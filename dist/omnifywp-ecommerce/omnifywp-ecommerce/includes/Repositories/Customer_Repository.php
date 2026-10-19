<?php
/**
 * Customer persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Customer_Repository {
	private const STATUSES = ['active', 'inactive'];

	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_customers_args', $omnify_args);

		$omnify_status = sanitize_key((string) ($omnify_args['status'] ?? 'all'));
		$omnify_search = sanitize_text_field((string) ($omnify_args['search'] ?? ''));
		$omnify_limit  = min(100, max(1, absint($omnify_args['per_page'] ?? 20)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_where = 'c.deleted_at IS NULL';
		$omnify_params = [];

		if ('trash' === $omnify_status) {
			$omnify_where = 'c.deleted_at IS NOT NULL';
		} elseif ('all' !== $omnify_status) {
			$omnify_where .= ' AND c.status = %s';
			$omnify_params[] = $this->status($omnify_status);
		}

		if ('' !== $omnify_search) {
			$omnify_where .= ' AND (c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s OR c.company LIKE %s OR c.phone LIKE %s OR c.billing_phone LIKE %s OR c.shipping_phone LIKE %s)';
			$omnify_like = '%' . $wpdb->esc_like($omnify_search) . '%';
			array_push($omnify_params, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like);
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND DATE(c.created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND DATE(c.created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_order_by = 'c.updated_at DESC';
		$omnify_sort = sanitize_key((string) ($omnify_args['sort'] ?? 'created_desc'));
		switch ($omnify_sort) {
			case 'created_asc': $omnify_order_by = 'c.created_at ASC'; break;
			case 'created_desc': $omnify_order_by = 'c.created_at DESC'; break;
			case 'name_asc': $omnify_order_by = 'c.first_name ASC, c.last_name ASC'; break;
			case 'name_desc': $omnify_order_by = 'c.first_name DESC, c.last_name DESC'; break;
			case 'spent_desc': $omnify_order_by = 'total_spent DESC, c.id DESC'; break;
			case 'spent_asc': $omnify_order_by = 'total_spent ASC, c.id ASC'; break;
			case 'orders_desc': $omnify_order_by = 'order_count DESC, c.id DESC'; break;
			case 'orders_asc': $omnify_order_by = 'order_count ASC, c.id ASC'; break;
		}

		$omnify_cust_table = $this->table('customers');
		$omnify_orders_table = $this->table('orders');
		$omnify_needs_stats_sort = in_array($omnify_sort, ['spent_desc', 'spent_asc', 'orders_desc', 'orders_asc'], true);

		if ($omnify_needs_stats_sort) {
			$omnify_params[] = $omnify_limit;
			$omnify_params[] = $omnify_offset;

			$omnify_sql = "SELECT c.*,
				COALESCE(s.order_count, 0) AS order_count,
				COALESCE(s.total_spent, 0.0) AS total_spent
			FROM {$omnify_cust_table} c
			LEFT JOIN (
				SELECT customer_id,
					COUNT(*) AS order_count,
					COALESCE(SUM(total - refunded_amount), 0) AS total_spent
				FROM {$omnify_orders_table}
				WHERE status NOT IN ('cancelled', 'failed', 'refunded', 'returned')
					AND deleted_at IS NULL
				GROUP BY customer_id
			) s ON s.customer_id = c.id
			WHERE {$omnify_where}
			ORDER BY {$omnify_order_by}
			LIMIT %d OFFSET %d";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, $omnify_params), ARRAY_A);
		} else {
			$omnify_params[] = $omnify_limit;
			$omnify_params[] = $omnify_offset;

			$omnify_sql = "SELECT c.*
			FROM {$omnify_cust_table} c
			WHERE {$omnify_where}
			ORDER BY {$omnify_order_by}
			LIMIT %d OFFSET %d";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, $omnify_params), ARRAY_A);

			if (is_array($omnify_rows) && ! empty($omnify_rows)) {
				$omnify_page_cids = array_map('intval', array_column($omnify_rows, 'id'));
				$omnify_placeholders = implode(',', array_fill(0, count($omnify_page_cids), '%d'));

				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$omnify_stats_sql = "SELECT customer_id,
					COUNT(*) AS order_count,
					COALESCE(SUM(total - refunded_amount), 0) AS total_spent
				FROM {$omnify_orders_table}
				WHERE customer_id IN ({$omnify_placeholders})
					AND status NOT IN ('cancelled', 'failed', 'refunded', 'returned')
					AND deleted_at IS NULL
				GROUP BY customer_id";

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$omnify_stats_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_stats_sql, ...$omnify_page_cids), ARRAY_A);
				$omnify_stats_map = [];
				if (is_array($omnify_stats_rows)) {
					foreach ($omnify_stats_rows as $omnify_s) {
						$omnify_stats_map[(int) $omnify_s['customer_id']] = [
							'order_count' => (int) $omnify_s['order_count'],
							'total_spent' => (float) $omnify_s['total_spent'],
						];
					}
				}

				foreach ($omnify_rows as &$omnify_row) {
					$omnify_cid = (int) $omnify_row['id'];
					$omnify_row['order_count'] = $omnify_stats_map[$omnify_cid]['order_count'] ?? 0;
					$omnify_row['total_spent'] = $omnify_stats_map[$omnify_cid]['total_spent'] ?? 0.0;
				}
				unset($omnify_row);
			}
		}

		$omnify_customers = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_customers_results', $omnify_customers, $omnify_args);
	}

	/**
	 * Count total customers matching criteria for pagination.
	 */
	public function count(array $omnify_args = []): int {
		global $wpdb;

		$omnify_status = sanitize_key((string) ($omnify_args['status'] ?? 'all'));
		$omnify_search = sanitize_text_field((string) ($omnify_args['search'] ?? ''));

		$omnify_where = 'c.deleted_at IS NULL';
		$omnify_params = [];

		if ('trash' === $omnify_status) {
			$omnify_where = 'c.deleted_at IS NOT NULL';
		} elseif ('all' !== $omnify_status) {
			$omnify_where .= ' AND c.status = %s';
			$omnify_params[] = $this->status($omnify_status);
		}

		if ('' !== $omnify_search) {
			$omnify_where .= ' AND (c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s OR c.company LIKE %s OR c.phone LIKE %s OR c.billing_phone LIKE %s OR c.shipping_phone LIKE %s)';
			$omnify_like = '%' . $wpdb->esc_like($omnify_search) . '%';
			array_push($omnify_params, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like, $omnify_like);
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND DATE(c.created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND DATE(c.created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_cust_table = $this->table('customers');
		$omnify_sql = "SELECT COUNT(*) FROM {$omnify_cust_table} c WHERE {$omnify_where}";

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
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE id = %d", $omnify_id), ARRAY_A);

		if (! is_array($omnify_row)) {
			return null;
		}

		$omnify_stats = $this->get_stats($omnify_id);
		$omnify_row['order_count'] = $omnify_stats['order_count'];
		$omnify_row['total_spent'] = $omnify_stats['total_spent'];

		$omnify_customer = $this->hydrate($omnify_row);
		return apply_filters('omnify_get_customer', $omnify_customer, $omnify_id);
	}

	public function find_by_email(string $omnify_email): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE email = %s AND deleted_at IS NULL", sanitize_email($omnify_email)), ARRAY_A);

		if (! is_array($omnify_row)) {
			return null;
		}

		$omnify_stats = $this->get_stats((int) $omnify_row['id']);
		$omnify_row['order_count'] = $omnify_stats['order_count'];
		$omnify_row['total_spent'] = $omnify_stats['total_spent'];

		return $this->hydrate($omnify_row);
	}

	public function find_by_user_id(int $omnify_user_id): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE user_id = %d AND deleted_at IS NULL", $omnify_user_id), ARRAY_A);

		if (! $omnify_row && $omnify_user_id > 0) {
			$omnify_wp_user = get_userdata($omnify_user_id);
			if ($omnify_wp_user && ! empty($omnify_wp_user->user_email)) {
				$omnify_by_email = $this->find_by_email($omnify_wp_user->user_email);
				if ($omnify_by_email && empty($omnify_by_email['user_id'])) {
					$this->update((int) $omnify_by_email['id'], ['user_id' => $omnify_user_id]);
					$omnify_by_email['user_id'] = $omnify_user_id;
					return $omnify_by_email;
				}
			}
		}

		return is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
	}

	public function create(array $omnify_data): int {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_create_customer_data', $omnify_data);

		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_now = $this->now();

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('customers'),
			[
				'user_id'             => absint($omnify_data['user_id'] ?? 0) ?: null,
				'email'               => $omnify_email,
				'first_name'          => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
				'last_name'           => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
				'phone'               => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
				'company'             => sanitize_text_field((string) ($omnify_data['company'] ?? '')),
				'country'             => strtoupper(substr(sanitize_text_field((string) ($omnify_data['country'] ?? '')), 0, 2)),
				'shipping_first_name' => sanitize_text_field((string) ($omnify_data['shipping_first_name'] ?? '')),
				'shipping_last_name'  => sanitize_text_field((string) ($omnify_data['shipping_last_name'] ?? '')),
				'shipping_phone'      => sanitize_text_field((string) ($omnify_data['shipping_phone'] ?? '')),
				'shipping_company'    => sanitize_text_field((string) ($omnify_data['shipping_company'] ?? '')),
				'shipping_address_1'  => sanitize_text_field((string) ($omnify_data['shipping_address_1'] ?? '')),
				'shipping_address_2'  => sanitize_text_field((string) ($omnify_data['shipping_address_2'] ?? '')),
				'shipping_city'       => sanitize_text_field((string) ($omnify_data['shipping_city'] ?? '')),
				'shipping_state'      => sanitize_text_field((string) ($omnify_data['shipping_state'] ?? '')),
				'shipping_postcode'   => sanitize_text_field((string) ($omnify_data['shipping_postcode'] ?? '')),
				'shipping_country'    => strtoupper(substr(sanitize_text_field((string) ($omnify_data['shipping_country'] ?? '')), 0, 2)),
				'billing_first_name'  => sanitize_text_field((string) ($omnify_data['billing_first_name'] ?? '')),
				'billing_last_name'   => sanitize_text_field((string) ($omnify_data['billing_last_name'] ?? '')),
				'billing_phone'       => sanitize_text_field((string) ($omnify_data['billing_phone'] ?? '')),
				'billing_company'     => sanitize_text_field((string) ($omnify_data['billing_company'] ?? '')),
				'billing_address_1'   => sanitize_text_field((string) ($omnify_data['billing_address_1'] ?? '')),
				'billing_address_2'   => sanitize_text_field((string) ($omnify_data['billing_address_2'] ?? '')),
				'billing_city'        => sanitize_text_field((string) ($omnify_data['billing_city'] ?? '')),
				'billing_state'       => sanitize_text_field((string) ($omnify_data['billing_state'] ?? '')),
				'billing_postcode'    => sanitize_text_field((string) ($omnify_data['billing_postcode'] ?? '')),
				'billing_country'     => strtoupper(substr(sanitize_text_field((string) ($omnify_data['billing_country'] ?? '')), 0, 2)),
				'tax_exempt'          => ! empty($omnify_data['tax_exempt']) ? 1 : 0,
				'status'              => $this->status((string) ($omnify_data['status'] ?? 'active')),
				'created_at'          => $omnify_now,
				'updated_at'          => $omnify_now,
			]
		);

		$omnify_customer_id = (int) $wpdb->insert_id;
		if ($omnify_customer_id > 0) {
			$this->clean_customer_caches_by_id($omnify_customer_id);
			do_action('omnify_customer_created', $omnify_customer_id, $omnify_data);
		}

		return $omnify_customer_id;
	}

	public function update(int $omnify_id, array $omnify_data): bool {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_update_customer_data', $omnify_data, $omnify_id);

		$omnify_row = ['updated_at' => $this->now()];

		if (array_key_exists('user_id', $omnify_data)) {
			$omnify_row['user_id'] = absint($omnify_data['user_id']) ?: null;
		}
		if (array_key_exists('email', $omnify_data)) {
			$omnify_row['email'] = sanitize_email((string) $omnify_data['email']);
		}
		if (array_key_exists('first_name', $omnify_data)) {
			$omnify_row['first_name'] = sanitize_text_field((string) $omnify_data['first_name']);
		}
		if (array_key_exists('last_name', $omnify_data)) {
			$omnify_row['last_name'] = sanitize_text_field((string) $omnify_data['last_name']);
		}
		if (array_key_exists('phone', $omnify_data)) {
			$omnify_row['phone'] = sanitize_text_field((string) $omnify_data['phone']);
		}
		if (array_key_exists('company', $omnify_data)) {
			$omnify_row['company'] = sanitize_text_field((string) $omnify_data['company']);
		}
		if (array_key_exists('country', $omnify_data)) {
			$omnify_row['country'] = strtoupper(substr(sanitize_text_field((string) $omnify_data['country']), 0, 2));
		}
		// Shipping fields
		$omnify_shipping_fields = ['shipping_first_name', 'shipping_last_name', 'shipping_phone', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode'];
		foreach ($omnify_shipping_fields as $omnify_field) {
			if (array_key_exists($omnify_field, $omnify_data)) {
				$omnify_row[$omnify_field] = sanitize_text_field((string) $omnify_data[$omnify_field]);
			}
		}
		if (array_key_exists('shipping_country', $omnify_data)) {
			$omnify_row['shipping_country'] = strtoupper(substr(sanitize_text_field((string) $omnify_data['shipping_country']), 0, 2));
		}
		// Billing fields
		$omnify_billing_fields = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode'];
		foreach ($omnify_billing_fields as $omnify_field) {
			if (array_key_exists($omnify_field, $omnify_data)) {
				$omnify_row[$omnify_field] = sanitize_text_field((string) $omnify_data[$omnify_field]);
			}
		}
		if (array_key_exists('billing_country', $omnify_data)) {
			$omnify_row['billing_country'] = strtoupper(substr(sanitize_text_field((string) $omnify_data['billing_country']), 0, 2));
		}
		if (array_key_exists('tax_exempt', $omnify_data)) {
			$omnify_row['tax_exempt'] = ! empty($omnify_data['tax_exempt']) ? 1 : 0;
		}
		if (array_key_exists('status', $omnify_data)) {
			$omnify_row['status'] = $this->status((string) $omnify_data['status']);
		}

		$omnify_old_customer = $this->find($omnify_id);

		$omnify_updated = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('customers'), $omnify_row, ['id' => $omnify_id]);

		if ($omnify_updated) {
			if ($omnify_old_customer) {
				$omnify_old_user_id = isset($omnify_old_customer['user_id']) ? (int) $omnify_old_customer['user_id'] : 0;
				$omnify_old_email   = isset($omnify_old_customer['email']) ? $omnify_old_customer['email'] : '';
				wp_cache_delete('omnify_cust_ids_' . $omnify_old_user_id . '_' . md5($omnify_old_email), 'omnifywp-ecommerce');
			}
			$this->clean_customer_caches_by_id($omnify_id);
			$this->record_activity($omnify_id, 'updated', __('Customer profile updated.', 'omnifywp-ecommerce'));
			do_action('omnify_customer_updated', $omnify_id, $omnify_data);
		}

		return $omnify_updated;
	}

	public function trash(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('customers'),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			$this->clean_customer_caches_by_id($omnify_id);
			do_action('omnify_customer_trashed', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('customers'),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			$this->clean_customer_caches_by_id($omnify_id);
			do_action('omnify_customer_restored', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		$omnify_customer = $this->find($omnify_id);

		$this->clean_customer_caches_by_id($omnify_id);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_notes'), ['customer_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_activity'), ['customer_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_access'), ['customer_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('orders'), ['customer_id' => null], ['customer_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('downloads'), ['customer_id' => null], ['customer_id' => $omnify_id]);

		if ($omnify_customer && ! empty($omnify_customer['email'])) {
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('abandoned_carts'), ['email' => $omnify_customer['email']]);
		}
		if ($omnify_customer && ! empty($omnify_customer['user_id'])) {
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('wishlists'), ['user_id' => (int) $omnify_customer['user_id']]);
		}

		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customers'), ['id' => $omnify_id]);
		if ($omnify_deleted) {
			do_action('omnify_customer_deleted', $omnify_id);
		}

		return $omnify_deleted;
	}

	public function add_note(int $omnify_customer_id, string $omnify_note, bool $omnify_customer_visible = false): int {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('customer_notes'),
			[
				'customer_id'      => $omnify_customer_id,
				'author_id'        => get_current_user_id() ?: null,
				'customer_visible' => $omnify_customer_visible ? 1 : 0,
				'note'             => wp_kses_post($omnify_note),
				'created_at'       => $this->now(),
			]
		);

		$omnify_note_id = (int) $wpdb->insert_id;
		$this->record_activity($omnify_customer_id, 'note_added', __('Customer note added.', 'omnifywp-ecommerce'), ['note_id' => $omnify_note_id]);

		return $omnify_note_id;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function notes(int $omnify_customer_id, bool $omnify_customer_only = false): array {
		global $wpdb;

		$omnify_sql = "SELECT * FROM {$this->table('customer_notes')} WHERE customer_id = %d";
		if ($omnify_customer_only) {
			$omnify_sql .= ' AND customer_visible = 1';
		}
		$omnify_sql .= ' ORDER BY created_at DESC, id DESC';

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare($omnify_sql, $omnify_customer_id),
			ARRAY_A
		);

		return array_map([$this, 'hydrate_note'], is_array($omnify_rows) ? $omnify_rows : []);
	}

	public function delete_note(int $omnify_customer_id, int $omnify_note_id): bool {
		global $wpdb;

		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_notes'), ['id' => $omnify_note_id, 'customer_id' => $omnify_customer_id]);

		if ($omnify_deleted) {
			$this->record_activity($omnify_customer_id, 'note_deleted', __('Customer note deleted.', 'omnifywp-ecommerce'), ['note_id' => $omnify_note_id]);
		}

		return $omnify_deleted;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function activity(int $omnify_customer_id): array {
		global $wpdb;

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table('customer_activity')} WHERE customer_id = %d ORDER BY created_at DESC, id DESC", $omnify_customer_id),
			ARRAY_A
		);

		return array_map([$this, 'hydrate_activity'], is_array($omnify_rows) ? $omnify_rows : []);
	}

	public function record_activity(int $omnify_customer_id, string $omnify_type, string $omnify_description, array $omnify_metadata = []): int {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('customer_activity'),
			[
				'customer_id'    => $omnify_customer_id,
				'actor_id'       => get_current_user_id() ?: null,
				'activity_type'  => sanitize_key($omnify_type),
				'description'    => sanitize_text_field($omnify_description),
				'metadata'       => wp_json_encode($omnify_metadata),
				'created_at'     => $this->now(),
			]
		);

		return (int) $wpdb->insert_id;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id'] = (int) $omnify_row['id'];
		$omnify_row['user_id'] = null === $omnify_row['user_id'] ? null : (int) $omnify_row['user_id'];
		$omnify_row['tax_exempt'] = ! empty($omnify_row['tax_exempt']);
		$omnify_row['order_count'] = isset($omnify_row['order_count']) ? (int) $omnify_row['order_count'] : 0;
		$omnify_row['total_spent'] = isset($omnify_row['total_spent']) ? (float) $omnify_row['total_spent'] : 0.0;
		$omnify_row['name'] = trim((string) $omnify_row['first_name'] . ' ' . (string) $omnify_row['last_name']);

		$omnify_address_fields = [
			'shipping_first_name', 'shipping_last_name', 'shipping_phone', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country',
			'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country'
		];
		foreach ($omnify_address_fields as $omnify_field) {
			$omnify_row[$omnify_field] = (string) ($omnify_row[$omnify_field] ?? '');
		}

		return $omnify_row;
	}

	private function hydrate_note(array $omnify_row): array {
		$omnify_row['id'] = (int) $omnify_row['id'];
		$omnify_row['customer_id'] = (int) $omnify_row['customer_id'];
		$omnify_row['author_id'] = null === $omnify_row['author_id'] ? null : (int) $omnify_row['author_id'];
		$omnify_row['customer_visible'] = ! empty($omnify_row['customer_visible']);

		return $omnify_row;
	}

	private function hydrate_activity(array $omnify_row): array {
		$omnify_row['id'] = (int) $omnify_row['id'];
		$omnify_row['customer_id'] = (int) $omnify_row['customer_id'];
		$omnify_row['actor_id'] = null === $omnify_row['actor_id'] ? null : (int) $omnify_row['actor_id'];
		$omnify_row['metadata'] = json_decode((string) $omnify_row['metadata'], true) ?: [];

		return $omnify_row;
	}

	private function status(string $omnify_status): string {
		$omnify_status = sanitize_key($omnify_status);

		return in_array($omnify_status, self::STATUSES, true) ? $omnify_status : 'active';
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function clean_customer_caches_by_id(int $omnify_id): void {
		$omnify_customer = $this->find($omnify_id);
		if ($omnify_customer) {
			$omnify_user_id = isset($omnify_customer['user_id']) ? (int) $omnify_customer['user_id'] : 0;
			$omnify_email = isset($omnify_customer['email']) ? $omnify_customer['email'] : '';
			wp_cache_delete('omnify_cust_ids_' . $omnify_user_id . '_' . md5($omnify_email), 'omnifywp-ecommerce');
		}
		$this->clear_stats_cache($omnify_id);
	}

	/**
	 * Get calculated spend and order count stats for a customer.
	 *
	 * Excludes cancelled, failed, refunded, and returned orders as well as trashed orders.
	 * Deducts any refunded amount from total spent.
	 *
	 * @param int $omnify_customer_id
	 * @return array{order_count: int, total_spent: float}
	 */
	public function get_stats(int $omnify_customer_id): array {
		if ($omnify_customer_id <= 0) {
			return ['order_count' => 0, 'total_spent' => 0.0];
		}

		global $wpdb;
		$omnify_cache_key = 'omnify_customer_stats_' . $omnify_customer_id;
		$omnify_stats     = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');

		if (false === $omnify_stats) {
			$omnify_orders_table = $this->table('orders');
			$omnify_row          = \Omnify\eCommerce\Support\Omnify_DB::get_row(
				$wpdb,
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT COUNT(*) as order_count, COALESCE(SUM(total - refunded_amount), 0) as total_spent FROM {$omnify_orders_table} WHERE customer_id = %d AND status NOT IN ('cancelled', 'failed', 'refunded', 'returned') AND deleted_at IS NULL",
					$omnify_customer_id
				),
				ARRAY_A
			);

			$omnify_stats = [
				'order_count' => (int) ($omnify_row['order_count'] ?? 0),
				'total_spent' => max(0.0, (float) ($omnify_row['total_spent'] ?? 0)),
			];
			wp_cache_set($omnify_cache_key, $omnify_stats, 'omnifywp-ecommerce', 10 * MINUTE_IN_SECONDS);
		}

		return is_array($omnify_stats) ? $omnify_stats : ['order_count' => 0, 'total_spent' => 0.0];
	}

	/**
	 * Invalidate customer stats cache.
	 *
	 * @param int $omnify_customer_id
	 */
	public function clear_stats_cache(int $omnify_customer_id): void {
		if ($omnify_customer_id > 0) {
			wp_cache_delete('omnify_customer_stats_' . $omnify_customer_id, 'omnifywp-ecommerce');
		}
	}

	/**
	 * Find customer IDs matching a user ID or email.
	 */
	public function find_ids_by_user_id_or_email(int $omnify_user_id, string $omnify_email): array {
		global $wpdb;

		$omnify_user_id = max(0, $omnify_user_id);
		$omnify_email   = sanitize_email($omnify_email);

		if ($omnify_user_id <= 0 && '' === $omnify_email) {
			return [];
		}

		$omnify_cache_key = 'omnify_cust_ids_' . $omnify_user_id . '_' . md5($omnify_email);
		$omnify_ids = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_ids) {
			$omnify_customers_table = $this->table('customers');
			if ($omnify_user_id > 0 && '' !== $omnify_email) {
				$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
					'SELECT id FROM %i WHERE (user_id = %d OR email = %s) AND deleted_at IS NULL',
					$omnify_customers_table,
					$omnify_user_id,
					$omnify_email
				), ARRAY_A);
			} elseif ($omnify_user_id > 0) {
				$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
					'SELECT id FROM %i WHERE user_id = %d AND deleted_at IS NULL',
					$omnify_customers_table,
					$omnify_user_id
				), ARRAY_A);
			} else {
				$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
					'SELECT id FROM %i WHERE email = %s AND deleted_at IS NULL',
					$omnify_customers_table,
					$omnify_email
				), ARRAY_A);
			}

			$omnify_ids = [];
			if (is_array($omnify_rows)) {
				foreach ($omnify_rows as $omnify_row) {
					$omnify_ids[] = (int) $omnify_row['id'];
				}
			}
			wp_cache_set($omnify_cache_key, $omnify_ids, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_ids) ? $omnify_ids : [];
	}

	private function now(): string {
		return current_time('mysql');
	}
}
