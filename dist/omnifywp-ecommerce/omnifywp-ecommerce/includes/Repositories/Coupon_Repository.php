<?php
/**
 * Coupon persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Coupon_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_coupons_args', $omnify_args);

		$omnify_limit  = min(100, max(1, absint($omnify_args['per_page'] ?? 100)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));
		$omnify_table  = $this->table('coupons');

		$omnify_where = "WHERE deleted_at IS NULL";
		$omnify_params = [];

		if (! empty($omnify_args['status']) && $omnify_args['status'] !== 'all') {
			if ($omnify_args['status'] === 'active') {
				$omnify_where .= " AND is_active = 1";
			} elseif ($omnify_args['status'] === 'trash') {
				$omnify_where = "WHERE deleted_at IS NOT NULL";
			}
		}

		if (! empty($omnify_args['search'])) {
			$omnify_search = '%' . $wpdb->esc_like($omnify_args['search']) . '%';
			$omnify_where .= " AND code LIKE %s";
			$omnify_params[] = $omnify_search;
		}

		if (! empty($omnify_args['type'])) {
			$omnify_where .= " AND discount_type = %s";
			$omnify_params[] = $omnify_args['type'];
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND DATE(created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND DATE(created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_order_by = 'created_at DESC';
		if (! empty($omnify_args['sort'])) {
			switch ($omnify_args['sort']) {
				case 'code_asc': $omnify_order_by = 'code ASC'; break;
				case 'usage_desc': $omnify_order_by = 'usage_count DESC'; break;
				case 'expiry_asc': $omnify_order_by = 'expires_at ASC'; break;
			}
		}

		$omnify_sql = "SELECT * FROM {$omnify_table} {$omnify_where} ORDER BY {$omnify_order_by} LIMIT %d OFFSET %d";
		$omnify_params[] = $omnify_limit;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);

		$omnify_coupons = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_coupons_results', $omnify_coupons, $omnify_args);
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		$omnify_table = $this->table('coupons');
		$omnify_row   = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$omnify_table} WHERE id = %d", $omnify_id),
			ARRAY_A
		);

		$omnify_coupon = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_coupon', $omnify_coupon, $omnify_id);
	}

	public function find_by_code(string $omnify_code, bool $omnify_include_trashed = false): ?array {
		global $wpdb;

		$omnify_table = $this->table('coupons');
		$omnify_where = $omnify_include_trashed ? '' : ' AND deleted_at IS NULL';
		$omnify_row   = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$omnify_table} WHERE UPPER(code) = UPPER(%s){$omnify_where}", sanitize_text_field(trim($omnify_code))),
			ARRAY_A
		);

		return is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
	}

	public function create(array $omnify_data): int {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_create_coupon_data', $omnify_data);

		$omnify_now        = $this->now();
		$omnify_expires_at = ! empty($omnify_data['expires_at']) ? sanitize_text_field($omnify_data['expires_at']) : null;

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('coupons'),
				[
					'code'           => strtoupper(sanitize_text_field(trim((string) ($omnify_data['code'] ?? '')))),
					'is_active'      => ! empty($omnify_data['is_active']) ? 1 : 0,
					'discount_type'  => sanitize_key((string) ($omnify_data['discount_type'] ?? 'fixed')),
					'discount_value' => (float) ($omnify_data['discount_value'] ?? 0.0),
					'min_order_amount' => isset($omnify_data['min_order_amount']) && '' !== trim((string) $omnify_data['min_order_amount']) ? (float) $omnify_data['min_order_amount'] : null,
					'max_discount_amount' => isset($omnify_data['max_discount_amount']) && '' !== trim((string) $omnify_data['max_discount_amount']) ? (float) $omnify_data['max_discount_amount'] : null,
					'usage_limit'    => ! empty($omnify_data['usage_limit']) ? absint($omnify_data['usage_limit']) : null,
					'usage_limit_per_customer' => ! empty($omnify_data['usage_limit_per_customer']) ? absint($omnify_data['usage_limit_per_customer']) : null,
					'usage_count'    => 0,
					'free_shipping'  => ! empty($omnify_data['free_shipping']) ? 1 : 0,
					'first_order_only' => ! empty($omnify_data['first_order_only']) ? 1 : 0,
					'included_product_ids' => $this->sanitize_csv($omnify_data['included_product_ids'] ?? ''),
					'excluded_product_ids' => $this->sanitize_csv($omnify_data['excluded_product_ids'] ?? ''),
					'included_categories' => $this->sanitize_csv($omnify_data['included_categories'] ?? ''),
					'excluded_categories' => $this->sanitize_csv($omnify_data['excluded_categories'] ?? ''),
					'expires_at'     => $omnify_expires_at,
					'created_at'     => $omnify_now,
					'updated_at'     => $omnify_now,
			]
		);

		$omnify_coupon_id = (int) $wpdb->insert_id;
		if ($omnify_coupon_id > 0) {
			do_action('omnify_coupon_created', $omnify_coupon_id, $omnify_data);
		}

		return $omnify_coupon_id;
	}

	public function update(int $omnify_id, array $omnify_data): bool {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_update_coupon_data', $omnify_data, $omnify_id);

		$omnify_expires_at = isset($omnify_data['expires_at']) ? (! empty($omnify_data['expires_at']) ? sanitize_text_field($omnify_data['expires_at']) : null) : null;
		$omnify_update     = [];

			if (isset($omnify_data['code'])) {
				$omnify_update['code'] = strtoupper(sanitize_text_field(trim((string) $omnify_data['code'])));
			}
			if (array_key_exists('is_active', $omnify_data)) {
				$omnify_update['is_active'] = ! empty($omnify_data['is_active']) ? 1 : 0;
			}
			if (isset($omnify_data['discount_type'])) {
				$omnify_update['discount_type'] = sanitize_key((string) $omnify_data['discount_type']);
			}
			if (isset($omnify_data['discount_value'])) {
				$omnify_update['discount_value'] = (float) $omnify_data['discount_value'];
			}
			if (array_key_exists('min_order_amount', $omnify_data)) {
				$omnify_update['min_order_amount'] = isset($omnify_data['min_order_amount']) && '' !== trim((string) $omnify_data['min_order_amount']) ? (float) $omnify_data['min_order_amount'] : null;
			}
			if (array_key_exists('max_discount_amount', $omnify_data)) {
				$omnify_update['max_discount_amount'] = isset($omnify_data['max_discount_amount']) && '' !== trim((string) $omnify_data['max_discount_amount']) ? (float) $omnify_data['max_discount_amount'] : null;
			}
			if (array_key_exists('usage_limit', $omnify_data)) {
				$omnify_update['usage_limit'] = ! empty($omnify_data['usage_limit']) ? absint($omnify_data['usage_limit']) : null;
			}
			if (array_key_exists('usage_limit_per_customer', $omnify_data)) {
				$omnify_update['usage_limit_per_customer'] = ! empty($omnify_data['usage_limit_per_customer']) ? absint($omnify_data['usage_limit_per_customer']) : null;
			}
			if (array_key_exists('free_shipping', $omnify_data)) {
				$omnify_update['free_shipping'] = ! empty($omnify_data['free_shipping']) ? 1 : 0;
			}
			if (array_key_exists('first_order_only', $omnify_data)) {
				$omnify_update['first_order_only'] = ! empty($omnify_data['first_order_only']) ? 1 : 0;
			}
			foreach (['included_product_ids', 'excluded_product_ids', 'included_categories', 'excluded_categories'] as $omnify_csv_field) {
				if (array_key_exists($omnify_csv_field, $omnify_data)) {
					$omnify_update[$omnify_csv_field] = $this->sanitize_csv($omnify_data[$omnify_csv_field]);
				}
			}
			if (array_key_exists('expires_at', $omnify_data)) {
				$omnify_update['expires_at'] = $omnify_expires_at;
			}

		if (empty($omnify_update)) {
			return false;
		}

		$omnify_update['updated_at'] = $this->now();

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('coupons'),
			$omnify_update,
			['id' => $omnify_id]
		);

		$omnify_success = $omnify_result !== false;
		if ($omnify_success) {
			do_action('omnify_coupon_updated', $omnify_id, $omnify_data);
		}

		return $omnify_success;
	}

	public function trash(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('coupons'),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_coupon_trashed', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('coupons'),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_coupon_restored', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, 
			$this->table('coupons'),
			['id' => $omnify_id]
		);

		$omnify_success = $omnify_result !== false;
		if ($omnify_success) {
			do_action('omnify_coupon_deleted', $omnify_id);
		}

		return $omnify_success;
	}

	public function increment_usage(int $omnify_id): bool {
		global $wpdb;

		$omnify_table = $this->table('coupons');
		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"UPDATE {$omnify_table} SET usage_count = usage_count + 1, updated_at = %s WHERE id = %d",
				$this->now(),
				$omnify_id
			)
		);

		return $omnify_result !== false;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']             = (int) $omnify_row['id'];
		$omnify_row['is_active']      = (int) ($omnify_row['is_active'] ?? 1);
		$omnify_row['discount_value'] = (float) $omnify_row['discount_value'];
		$omnify_row['min_order_amount'] = null === ($omnify_row['min_order_amount'] ?? null) ? null : (float) $omnify_row['min_order_amount'];
		$omnify_row['max_discount_amount'] = null === ($omnify_row['max_discount_amount'] ?? null) ? null : (float) $omnify_row['max_discount_amount'];
		$omnify_row['usage_limit']    = null === $omnify_row['usage_limit'] ? null : (int) $omnify_row['usage_limit'];
		$omnify_row['usage_limit_per_customer'] = null === ($omnify_row['usage_limit_per_customer'] ?? null) ? null : (int) $omnify_row['usage_limit_per_customer'];
		$omnify_row['usage_count']    = (int) $omnify_row['usage_count'];
		$omnify_row['free_shipping']  = (int) ($omnify_row['free_shipping'] ?? 0);
		$omnify_row['first_order_only'] = (int) ($omnify_row['first_order_only'] ?? 0);
		$omnify_row['included_product_ids'] = (string) ($omnify_row['included_product_ids'] ?? '');
		$omnify_row['excluded_product_ids'] = (string) ($omnify_row['excluded_product_ids'] ?? '');
		$omnify_row['included_categories'] = (string) ($omnify_row['included_categories'] ?? '');
		$omnify_row['excluded_categories'] = (string) ($omnify_row['excluded_categories'] ?? '');
		$omnify_row['is_expired']     = false;

		if (! empty($omnify_row['expires_at'])) {
			$omnify_expires_str = trim((string) $omnify_row['expires_at']);
			// If format is date-only (YYYY-MM-DD), expire at end of day (23:59:59)
			if (1 === preg_match('/^\d{4}-\d{2}-\d{2}$/', $omnify_expires_str)) {
				$omnify_expires_str .= ' 23:59:59';
			}
			$omnify_row['is_expired'] = strtotime($omnify_expires_str) < time();
		}

		return $omnify_row;
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function sanitize_csv(mixed $omnify_value): string {
		$omnify_items = array_filter(array_map('trim', explode(',', (string) $omnify_value)), static fn(string $omnify_item): bool => '' !== $omnify_item);
		$omnify_items = array_map(static fn(string $omnify_item): string => sanitize_text_field($omnify_item), $omnify_items);

		return implode(',', array_unique($omnify_items));
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
