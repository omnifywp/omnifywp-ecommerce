<?php
/**
 * Wishlist persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Wishlist_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all_for_user(int $omnify_user_id): array {
		global $wpdb;

		if ($omnify_user_id <= 0) {
			return [];
		}

		$omnify_table = $this->table();
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_rows  = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT w.* FROM {$omnify_table} w 
				 INNER JOIN {$omnify_products_table} p ON w.product_id = p.id 
				 WHERE w.user_id = %d AND p.deleted_at IS NULL 
				 ORDER BY w.created_at DESC", 
				$omnify_user_id
			),
			ARRAY_A
		);

		$omnify_results = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_wishlist_results', $omnify_results, $omnify_user_id);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_all_wishlists_args', $omnify_args);
		$omnify_limit  = min(200, max(1, absint($omnify_args['per_page'] ?? 100)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_table = $this->table();
		$omnify_rows  = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$omnify_table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $omnify_limit, $omnify_offset),
			ARRAY_A
		);

		$omnify_results = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_all_wishlists_results', $omnify_results, $omnify_args);
	}

	/**
	 * @return array<int, int>
	 */
	public function product_ids_for_user(int $omnify_user_id): array {
		return array_values(array_map(static fn(array $omnify_row): int => (int) $omnify_row['product_id'], $this->all_for_user($omnify_user_id)));
	}

	public function exists(int $omnify_user_id, int $omnify_product_id): bool {
		global $wpdb;

		if ($omnify_user_id <= 0 || $omnify_product_id <= 0) {
			return false;
		}

		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT COUNT(*) FROM {$this->table()} WHERE user_id = %d AND product_id = %d", $omnify_user_id, $omnify_product_id)
		) > 0;
	}

	public function add(int $omnify_user_id, int $omnify_product_id): bool {
		global $wpdb;

		if ($omnify_user_id <= 0 || $omnify_product_id <= 0 || $this->exists($omnify_user_id, $omnify_product_id)) {
			return false;
		}

		$omnify_now = current_time('mysql', true);
		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table(),
			[
				'user_id'    => $omnify_user_id,
				'product_id' => $omnify_product_id,
				'created_at' => $omnify_now,
				'updated_at' => $omnify_now,
			]
		);

		$omnify_success = false !== $omnify_result;
		if ($omnify_success) {
			do_action('omnify_wishlist_added', $omnify_user_id, $omnify_product_id);
		}
		return $omnify_success;
	}

	public function remove(int $omnify_user_id, int $omnify_product_id): bool {
		global $wpdb;

		if ($omnify_user_id <= 0 || $omnify_product_id <= 0) {
			return false;
		}

		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, 
			$this->table(),
			[
				'user_id'    => $omnify_user_id,
				'product_id' => $omnify_product_id,
			]
		);

		if ($omnify_deleted) {
			do_action('omnify_wishlist_removed', $omnify_user_id, $omnify_product_id);
		}
		return $omnify_deleted;
	}

	/**
	 * @return array{wishlisted: bool}
	 */
	public function toggle(int $omnify_user_id, int $omnify_product_id): array {
		if ($this->exists($omnify_user_id, $omnify_product_id)) {
			$this->remove($omnify_user_id, $omnify_product_id);

			return ['wishlisted' => false];
		}

		$this->add($omnify_user_id, $omnify_product_id);

		return ['wishlisted' => true];
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']         = (int) ($omnify_row['id'] ?? 0);
		$omnify_row['user_id']    = (int) ($omnify_row['user_id'] ?? 0);
		$omnify_row['product_id'] = (int) ($omnify_row['product_id'] ?? 0);

		return $omnify_row;
	}

	private function table(): string {
		return $this->omnify_schema->table('wishlists');
	}
}
