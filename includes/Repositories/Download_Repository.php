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

		$omnify_limit = isset($omnify_args['per_page']) ? absint($omnify_args['per_page']) : 20;
		$omnify_offset = isset($omnify_args['page']) ? (absint($omnify_args['page']) - 1) * $omnify_limit : 0;

		$omnify_sql = "
			SELECT
				d.*,
				p.name as product_name,
				f.file_name as file_name
			FROM {$omnify_downloads_table} d
			LEFT JOIN {$omnify_products_table} p ON d.product_id = p.id
			LEFT JOIN {$omnify_files_table} f ON d.file_id = f.id
			ORDER BY d.downloaded_at DESC
			LIMIT %d OFFSET %d
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, $omnify_limit, $omnify_offset), ARRAY_A);

		return is_array($omnify_rows) ? $omnify_rows : [];
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
