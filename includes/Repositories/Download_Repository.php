<?php
/**
 * Download log persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Download_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	public function log(int $omnify_product_id, int $omnify_file_id, string $omnify_status = 'delivered', ?int $omnify_customer_id = null): int {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->omnify_schema->table('downloads'),
			[
				'product_id'    => $omnify_product_id,
				'file_id'       => $omnify_file_id,
				'customer_id'   => $omnify_customer_id,
				'status'        => sanitize_key($omnify_status),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'ip_address'    => sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? '')),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'user_agent'    => sanitize_text_field((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
				'downloaded_at' => current_time('mysql', true),
			]
		);

		$omnify_inserted_id = (int) $wpdb->insert_id;
		do_action('omnify_download_logged', $omnify_inserted_id, $omnify_product_id, $omnify_file_id, $omnify_customer_id, $omnify_status);
		return $omnify_inserted_id;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all_for_customer(int $omnify_customer_id, int $omnify_limit = 50): array {
		global $wpdb;

		$omnify_downloads_table = $this->omnify_schema->table('downloads');
		$omnify_products_table  = $this->omnify_schema->table('products');
		$omnify_files_table     = $this->omnify_schema->table('product_files');

		$omnify_sql = "
			SELECT
				d.*,
				p.name as product_name,
				f.file_name as file_name
			FROM {$omnify_downloads_table} d
			LEFT JOIN {$omnify_products_table} p ON d.product_id = p.id
			LEFT JOIN {$omnify_files_table} f ON d.file_id = f.id
			WHERE d.customer_id = %d
			ORDER BY d.downloaded_at DESC
			LIMIT %d
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, $omnify_customer_id, $omnify_limit), ARRAY_A);

		return is_array($omnify_rows) ? $omnify_rows : [];
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_downloads_table = $this->omnify_schema->table('downloads');
		$omnify_products_table  = $this->omnify_schema->table('products');
		$omnify_files_table     = $this->omnify_schema->table('product_files');

		$omnify_limit = min(200, max(1, absint($omnify_args['per_page'] ?? 20)));
		$omnify_offset = isset($omnify_args['offset'])
			? max(0, absint($omnify_args['offset']))
			: max(0, (max(1, absint($omnify_args['page'] ?? 1)) - 1) * $omnify_limit);

		$omnify_where = ['1=1'];
		$omnify_params = [];

		if (! empty($omnify_args['customer_id'])) {
			$omnify_where[] = 'd.customer_id = %d';
			$omnify_params[] = absint($omnify_args['customer_id']);
		}
		if (! empty($omnify_args['product_id'])) {
			$omnify_where[] = 'd.product_id = %d';
			$omnify_params[] = absint($omnify_args['product_id']);
		}
		if (! empty($omnify_args['status'])) {
			$omnify_where[] = 'd.status = %s';
			$omnify_params[] = sanitize_key($omnify_args['status']);
		}

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "
			SELECT
				d.*,
				p.name as product_name,
				f.file_name as file_name
			FROM {$omnify_downloads_table} d
			LEFT JOIN {$omnify_products_table} p ON d.product_id = p.id
			LEFT JOIN {$omnify_files_table} f ON d.file_id = f.id
			WHERE {$omnify_where_sql}
			ORDER BY d.downloaded_at DESC
			LIMIT %d OFFSET %d
		";

		$omnify_params[] = $omnify_limit;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);

		return is_array($omnify_rows) ? $omnify_rows : [];
	}

	/**
	 * Count downloads matching filters.
	 */
	public function count(array $omnify_args = []): int {
		global $wpdb;

		$omnify_downloads_table = $this->omnify_schema->table('downloads');
		$omnify_where = ['1=1'];
		$omnify_params = [];

		if (! empty($omnify_args['customer_id'])) {
			$omnify_where[] = 'customer_id = %d';
			$omnify_params[] = absint($omnify_args['customer_id']);
		}
		if (! empty($omnify_args['product_id'])) {
			$omnify_where[] = 'product_id = %d';
			$omnify_params[] = absint($omnify_args['product_id']);
		}
		if (! empty($omnify_args['status'])) {
			$omnify_where[] = 'status = %s';
			$omnify_params[] = sanitize_key($omnify_args['status']);
		}

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "SELECT COUNT(*) FROM {$omnify_downloads_table} WHERE {$omnify_where_sql}";

		if (! empty($omnify_params)) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params));
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $omnify_sql);
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->omnify_schema->table('downloads')} WHERE id = %d", $omnify_id), ARRAY_A);
		return is_array($omnify_row) ? $omnify_row : null;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;
		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->omnify_schema->table('downloads'), ['id' => $omnify_id]);
		if ($omnify_deleted) {
			do_action('omnify_download_deleted', $omnify_id);
		}
		return $omnify_deleted;
	}
}
