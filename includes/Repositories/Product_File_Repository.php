<?php
/**
 * Product file persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Product_File_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all_for_product(int $omnify_product_id): array {
		global $wpdb;

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table()} WHERE product_id = %d ORDER BY updated_at DESC, id DESC", $omnify_product_id),
			ARRAY_A
		);

		$omnify_results = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_product_files_results', $omnify_results, $omnify_product_id);
	}

	/**
	 * Retrieve all files for multiple product IDs in a single query.
	 *
	 * @param array<int> $omnify_product_ids
	 * @return array<int, array<int, array<string, mixed>>>
	 */
	public function all_for_products(array $omnify_product_ids): array {
		$omnify_product_ids = array_values(array_filter(array_map('absint', $omnify_product_ids)));
		if (empty($omnify_product_ids)) {
			return [];
		}

		global $wpdb;
		$omnify_placeholders = implode(',', array_fill(0, count($omnify_product_ids), '%d'));

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results(
			$wpdb,
			$wpdb->prepare("SELECT * FROM {$this->table()} WHERE product_id IN ({$omnify_placeholders}) ORDER BY updated_at DESC, id DESC", ...$omnify_product_ids),
			ARRAY_A
		);

		$omnify_grouped = [];
		foreach ($omnify_product_ids as $omnify_pid) {
			$omnify_grouped[$omnify_pid] = [];
		}

		if (is_array($omnify_rows)) {
			foreach ($omnify_rows as $omnify_row) {
				$omnify_hydrated = $this->hydrate($omnify_row);
				$omnify_grouped[(int) $omnify_row['product_id']][] = $omnify_hydrated;
			}
		}

		return $omnify_grouped;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_product_files_args', $omnify_args);
		$omnify_limit  = min(200, max(1, absint($omnify_args['per_page'] ?? 100)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table()} ORDER BY updated_at DESC, id DESC LIMIT %d OFFSET %d", $omnify_limit, $omnify_offset),
			ARRAY_A
		);

		$omnify_results = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_all_product_files_results', $omnify_results, $omnify_args);
	}

	/**
	 * Count total product files matching filters.
	 */
	public function count(array $omnify_args = []): int {
		global $wpdb;

		$omnify_table = $this->table();
		$omnify_where = ['1=1'];
		$omnify_params = [];

		if (! empty($omnify_args['product_id'])) {
			$omnify_where[] = 'product_id = %d';
			$omnify_params[] = absint($omnify_args['product_id']);
		}

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "SELECT COUNT(*) FROM {$omnify_table} WHERE {$omnify_where_sql}";

		if (! empty($omnify_params)) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params));
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $omnify_sql);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function active_for_product(int $omnify_product_id): array {
		$omnify_files = $this->all_for_product($omnify_product_id);

		return array_values(array_filter($omnify_files, [$this, 'is_downloadable']));
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table()} WHERE id = %d", $omnify_id), ARRAY_A);

		$omnify_result = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_product_file', $omnify_result, $omnify_id);
	}

	public function create_from_upload(int $omnify_product_id, array $omnify_upload, string $omnify_version, ?string $omnify_expires_at = null): int {
		global $wpdb;

		$omnify_upload = apply_filters('omnify_pre_create_product_file_data', $omnify_upload, $omnify_product_id, $omnify_version, $omnify_expires_at);
		$omnify_now = $this->now();
		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table(),
			[
				'product_id'    => $omnify_product_id,
				'attachment_id' => null,
				'version'       => sanitize_text_field($omnify_version),
				'file_name'     => sanitize_file_name((string) ($omnify_upload['name'] ?? basename((string) ($omnify_upload['file'] ?? 'file')))),
				'file_path'     => sanitize_text_field( (string) ( realpath( (string) ($omnify_upload['file'] ?? '') ) ?: ($omnify_upload['file'] ?? '') ) ),
				'file_url'      => esc_url_raw((string) ($omnify_upload['url'] ?? '')),
				'file_size'     => is_readable((string) ($omnify_upload['file'] ?? '')) ? (int) filesize((string) $omnify_upload['file']) : 0,
				'file_type'     => sanitize_text_field((string) ($omnify_upload['type'] ?? 'application/octet-stream')),
				'expires_at'    => $this->date_or_null($omnify_expires_at),
				'created_at'    => $omnify_now,
				'updated_at'    => $omnify_now,
			]
		);

		$omnify_inserted_id = (int) $wpdb->insert_id;
		do_action('omnify_product_file_created', $omnify_inserted_id, $omnify_product_id, $omnify_upload, $omnify_version);
		return $omnify_inserted_id;
	}

	public function replace_from_upload(int $omnify_id, array $omnify_upload, string $omnify_version, ?string $omnify_expires_at = null): bool {
		global $wpdb;

		$omnify_upload = apply_filters('omnify_pre_replace_product_file_data', $omnify_upload, $omnify_id, $omnify_version, $omnify_expires_at);
		$omnify_success = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			[
				'version'    => sanitize_text_field($omnify_version),
				'file_name'  => sanitize_file_name((string) ($omnify_upload['name'] ?? basename((string) ($omnify_upload['file'] ?? 'file')))),
				'file_path'  => sanitize_text_field( (string) ( realpath( (string) ($omnify_upload['file'] ?? '') ) ?: ($omnify_upload['file'] ?? '') ) ),
				'file_url'   => esc_url_raw((string) ($omnify_upload['url'] ?? '')),
				'file_size'  => is_readable((string) ($omnify_upload['file'] ?? '')) ? (int) filesize((string) $omnify_upload['file']) : 0,
				'file_type'  => sanitize_text_field((string) ($omnify_upload['type'] ?? 'application/octet-stream')),
				'expires_at' => $this->date_or_null($omnify_expires_at),
				'updated_at' => $this->now(),
			],
			['id' => $omnify_id]
		);

		if ($omnify_success) {
			do_action('omnify_product_file_replaced', $omnify_id, $omnify_upload, $omnify_version);
		}
		return $omnify_success;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		$omnify_file = $this->find($omnify_id);
		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table(), ['id' => $omnify_id]);
		if ($omnify_deleted) {
			if ($omnify_file && empty($omnify_file['attachment_id']) && ! empty($omnify_file['file_path'])) {
				$omnify_file_path = (string) $omnify_file['file_path'];
				$omnify_uploads   = wp_upload_dir();
				$omnify_basedir   = realpath((string) ($omnify_uploads['basedir'] ?? ''));
				$omnify_real_file = realpath($omnify_file_path);

				$omnify_norm_file = ($omnify_real_file && function_exists('wp_normalize_path')) ? wp_normalize_path($omnify_real_file) : ($omnify_real_file ? str_replace('\\', '/', $omnify_real_file) : '');
				$omnify_norm_base = ($omnify_basedir && function_exists('wp_normalize_path')) ? wp_normalize_path($omnify_basedir) : ($omnify_basedir ? str_replace('\\', '/', $omnify_basedir) : '');

				// Safety checks: ensure it is a local file, exists within the WordPress uploads directory,
				// and is not referenced by any other product file record.
				if ($omnify_basedir && $omnify_real_file && '' !== $omnify_norm_base && 0 === stripos($omnify_norm_file, $omnify_norm_base) && file_exists($omnify_real_file) && is_file($omnify_real_file)) {
					$omnify_still_referenced = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var(
						$wpdb,
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$wpdb->prepare("SELECT COUNT(*) FROM {$this->table()} WHERE file_path = %s AND id != %d", $omnify_file_path, $omnify_id)
					);

					if (0 === $omnify_still_referenced) {
						if (function_exists('wp_delete_file')) {
							wp_delete_file($omnify_real_file);
						} else {
							@unlink($omnify_real_file);
						}
					}
				}
			}

			do_action('omnify_product_file_deleted', $omnify_id);
		}
		return $omnify_deleted;
	}

	public function is_downloadable(array $omnify_file): bool {
		if (! empty($omnify_file['expires_at'])) {
			$omnify_expires_timestamp = strtotime((string) $omnify_file['expires_at'] . ' UTC');
			if ($omnify_expires_timestamp && $omnify_expires_timestamp < time()) {
				return false;
			}
		}

		return true;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']            = (int) $omnify_row['id'];
		$omnify_row['product_id']    = (int) $omnify_row['product_id'];
		$omnify_row['attachment_id'] = null === $omnify_row['attachment_id'] ? null : (int) $omnify_row['attachment_id'];
		$omnify_row['file_size']     = (int) $omnify_row['file_size'];
		$omnify_row['size_label']    = size_format((int) $omnify_row['file_size']);
		$omnify_row['is_expired']    = false;

		if (! empty($omnify_row['expires_at'])) {
			$omnify_expires_timestamp = strtotime((string) $omnify_row['expires_at'] . ' UTC');
			$omnify_row['is_expired'] = $omnify_expires_timestamp ? $omnify_expires_timestamp < time() : false;
		}

		return $omnify_row;
	}

	private function date_or_null(?string $omnify_date): ?string {
		if (empty($omnify_date)) {
			return null;
		}

		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $omnify_date)) {
			$omnify_date .= ' 23:59:59';
		}

		$omnify_timestamp = strtotime($omnify_date);

		return $omnify_timestamp ? gmdate('Y-m-d H:i:s', $omnify_timestamp) : null;
	}

	private function table(): string {
		return $this->omnify_schema->table('product_files');
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
