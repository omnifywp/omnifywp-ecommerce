<?php
/**
 * Abandoned cart persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Abandoned_Cart_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_abandoned_carts_args', $omnify_args);
		$omnify_table  = $this->table();
		$omnify_status = sanitize_key((string) ($omnify_args['status'] ?? ''));
		$omnify_search = sanitize_text_field((string) ($omnify_args['search'] ?? ''));
		$omnify_limit  = min(200, max(1, absint($omnify_args['per_page'] ?? 100)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_where = "WHERE deleted_at IS NULL";
		if ($omnify_status === 'trash') {
			$omnify_where = "WHERE deleted_at IS NOT NULL";
		} elseif ($omnify_status) {
			$omnify_where .= $wpdb->prepare(" AND status = %s", $omnify_status);
		}
		if ($omnify_search !== '') {
			$omnify_like = '%' . $wpdb->esc_like($omnify_search) . '%';
			$omnify_where .= $wpdb->prepare(
				" AND (email LIKE %s OR first_name LIKE %s OR last_name LIKE %s)",
				$omnify_like, $omnify_like, $omnify_like
			);
		}

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$omnify_table} {$omnify_where} ORDER BY updated_at DESC LIMIT %d OFFSET %d", $omnify_limit, $omnify_offset),
			ARRAY_A
		);

		$omnify_results = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_abandoned_carts_results', $omnify_results, $omnify_args);
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table()} WHERE id = %d", $omnify_id),
			ARRAY_A
		);

		$omnify_result = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_abandoned_cart', $omnify_result, $omnify_id);
	}

	public function find_by_token(string $omnify_token): ?array {
		global $wpdb;

		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table()} WHERE token = %s", sanitize_text_field($omnify_token)),
			ARRAY_A
		);

		$omnify_result = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_abandoned_cart_by_token', $omnify_result, $omnify_token);
	}

	public function upsert(array $omnify_data): array {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_upsert_abandoned_cart_data', $omnify_data);
		$omnify_now   = $this->now();
		$omnify_token = sanitize_text_field((string) ($omnify_data['token'] ?? ''));
		if ('' === $omnify_token) {
			$omnify_token = wp_generate_uuid4();
		}

		$omnify_payload = $omnify_data['cart_payload'] ?? [];
		$omnify_record  = [
			'token'              => $omnify_token,
			'email'              => sanitize_email((string) ($omnify_data['email'] ?? '')),
			'first_name'         => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
			'last_name'          => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
			'phone'              => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
			'product_id'         => ! empty($omnify_data['product_id']) ? absint($omnify_data['product_id']) : null,
			'variation_id'       => ! empty($omnify_data['variation_id']) ? absint($omnify_data['variation_id']) : null,
			'quantity'           => max(1, absint($omnify_data['quantity'] ?? 1)),
			'cart_payload'       => wp_json_encode(is_array($omnify_payload) ? $omnify_payload : []),
			'subtotal'           => (float) ($omnify_data['subtotal'] ?? 0),
			'currency'           => strtoupper(substr(sanitize_text_field((string) ($omnify_data['currency'] ?? 'USD')), 0, 3)),
			'coupon_code'        => sanitize_text_field((string) ($omnify_data['coupon_code'] ?? '')),
			'checkout_url'       => esc_url_raw((string) ($omnify_data['checkout_url'] ?? '')),
			'recovery_url'       => esc_url_raw((string) ($omnify_data['recovery_url'] ?? '')),
			'status'             => sanitize_key((string) ($omnify_data['status'] ?? 'active')),
			'expires_at'         => ! empty($omnify_data['expires_at']) ? sanitize_text_field((string) $omnify_data['expires_at']) : null,
			'updated_at'         => $omnify_now,
		];

		$omnify_existing = $this->find_by_token($omnify_token);
		if ($omnify_existing) {
			if ('recovered' === ($omnify_existing['status'] ?? '')) {
				$omnify_record['status'] = 'recovered';
			}
			\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table(), $omnify_record, ['id' => (int) $omnify_existing['id']]);

			$omnify_updated = $this->find((int) $omnify_existing['id']) ?: $omnify_existing;
			do_action('omnify_abandoned_cart_saved', (int) $omnify_existing['id'], $omnify_updated, false);
			return $omnify_updated;
		}

		$omnify_record['reminder_count'] = 0;
		$omnify_record['created_at']     = $omnify_now;
		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, $this->table(), $omnify_record);

		$omnify_inserted_id = (int) $wpdb->insert_id;
		$omnify_created = $this->find($omnify_inserted_id) ?: $omnify_record;
		do_action('omnify_abandoned_cart_saved', $omnify_inserted_id, $omnify_created, true);
		return $omnify_created;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function due_for_recovery(int $omnify_delay_minutes, int $omnify_max_reminders, int $omnify_limit = 20): array {
		global $wpdb;

		$omnify_delay_minutes = max(5, $omnify_delay_minutes);
		$omnify_max_reminders = max(1, $omnify_max_reminders);
		$omnify_threshold     = gmdate('Y-m-d H:i:s', time() - ($omnify_delay_minutes * MINUTE_IN_SECONDS));
		$omnify_now           = gmdate('Y-m-d H:i:s');
		$omnify_table         = $this->table();

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$omnify_table}
				WHERE status = 'active'
					AND deleted_at IS NULL
					AND email <> ''
					AND reminder_count < %d
					AND updated_at <= %s
					AND (expires_at IS NULL OR expires_at > %s)
				ORDER BY updated_at ASC
				LIMIT %d",
				$omnify_max_reminders,
				$omnify_threshold,
				$omnify_now,
				min(100, max(1, $omnify_limit))
			),
			ARRAY_A
		);

		return array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
	}

	public function mark_sent(int $omnify_id): bool {
		global $wpdb;

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"UPDATE {$this->table()} SET reminder_count = reminder_count + 1, last_reminder_at = %s, updated_at = %s WHERE id = %d",
				$this->now(),
				$this->now(),
				$omnify_id
			)
		);

		if (false !== $omnify_result) {
			do_action('omnify_abandoned_cart_sent', $omnify_id);
			return true;
		}
		return false;
	}

	public function mark_recovered(string $omnify_token, int $omnify_order_id): bool {
		global $wpdb;

		$omnify_token = sanitize_text_field($omnify_token);
		if ('' === $omnify_token) {
			return false;
		}

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			[
				'status'             => 'recovered',
				'recovered_order_id' => $omnify_order_id,
				'recovered_at'       => $this->now(),
				'updated_at'         => $this->now(),
			],
			['token' => $omnify_token]
		);

		if (false !== $omnify_result) {
			do_action('omnify_abandoned_cart_recovered', $omnify_token, $omnify_order_id);
			return true;
		}
		return false;
	}

	public function mark_unsubscribed(string $omnify_token): bool {
		global $wpdb;

		$omnify_token = sanitize_text_field($omnify_token);
		if ('' === $omnify_token) {
			return false;
		}

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			[
				'status'     => 'unsubscribed',
				'updated_at' => $this->now(),
			],
			['token' => $omnify_token]
		);

		if (false !== $omnify_result) {
			do_action('omnify_abandoned_cart_unsubscribed', $omnify_token);
			return true;
		}
		return false;
	}

	public function trash(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_abandoned_cart_trashed', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_abandoned_cart_restored', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table(), ['id' => $omnify_id]);
		if ($omnify_deleted) {
			do_action('omnify_abandoned_cart_deleted', $omnify_id);
		}
		return $omnify_deleted;
	}

	/**
	 * @return array<string, int|float>
	 */
	public function stats(): array {
		global $wpdb;

		$omnify_table = $this->table();

		return [
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'total'     => (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, "SELECT COUNT(*) FROM {$omnify_table}"),
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'active'    => (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, "SELECT COUNT(*) FROM {$omnify_table} WHERE status = 'active'"),
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'emailed'   => (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, "SELECT COUNT(*) FROM {$omnify_table} WHERE reminder_count > 0"),
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'recovered' => (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, "SELECT COUNT(*) FROM {$omnify_table} WHERE status = 'recovered'"),
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'value'     => (float) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, "SELECT SUM(subtotal) FROM {$omnify_table} WHERE status = 'active'"),
		];
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']                 = (int) ($omnify_row['id'] ?? 0);
		$omnify_row['product_id']         = empty($omnify_row['product_id']) ? null : (int) $omnify_row['product_id'];
		$omnify_row['variation_id']       = empty($omnify_row['variation_id']) ? null : (int) $omnify_row['variation_id'];
		$omnify_row['quantity']           = max(1, (int) ($omnify_row['quantity'] ?? 1));
		$omnify_row['subtotal']           = (float) ($omnify_row['subtotal'] ?? 0);
		$omnify_row['reminder_count']     = (int) ($omnify_row['reminder_count'] ?? 0);
		$omnify_row['recovered_order_id'] = empty($omnify_row['recovered_order_id']) ? null : (int) $omnify_row['recovered_order_id'];
		$omnify_row['cart_payload']       = ! empty($omnify_row['cart_payload']) ? json_decode((string) $omnify_row['cart_payload'], true) : [];
		if (! is_array($omnify_row['cart_payload'])) {
			$omnify_row['cart_payload'] = [];
		}

		return $omnify_row;
	}

	/**
	 * Count active abandoned carts containing a specific product (for urgency "in carts").
	 */
	public function count_active_carts_for_product(int $omnify_product_id): int {
		global $wpdb;
		$omnify_table = $this->table();
		$omnify_json_id = wp_json_encode($omnify_product_id);
		$omnify_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb,  $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT COUNT(*) FROM {$omnify_table} 
			 WHERE deleted_at IS NULL AND status = 'active' 
			   AND (product_id = %d OR cart_payload LIKE %s)",
			$omnify_product_id, '%' . $wpdb->esc_like($omnify_json_id) . '%'
		));
		return max(0, $omnify_count);
	}

	private function table(): string {
		return $this->omnify_schema->table('abandoned_carts');
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
