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

		$omnify_where = 'deleted_at IS NULL';
		$omnify_params = [];

		if ('trash' === $omnify_status) {
			$omnify_where = 'deleted_at IS NOT NULL';
		} elseif ('all' !== $omnify_status) {
			$omnify_where .= ' AND status = %s';
			$omnify_params[] = $this->status($omnify_status);
		}

		if ('' !== $omnify_search) {
			$omnify_where .= ' AND (email LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR company LIKE %s)';
			$omnify_like = '%' . $wpdb->esc_like($omnify_search) . '%';
			array_push($omnify_params, $omnify_like, $omnify_like, $omnify_like, $omnify_like);
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND DATE(created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND DATE(created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_order_by = 'updated_at DESC';
		$omnify_sort = sanitize_key((string) ($omnify_args['sort'] ?? 'created_desc'));
		switch ($omnify_sort) {
			case 'created_asc': $omnify_order_by = 'created_at ASC'; break;
			case 'name_asc': $omnify_order_by = 'first_name ASC, last_name ASC'; break;
			case 'name_desc': $omnify_order_by = 'first_name DESC, last_name DESC'; break;
		}

		$omnify_params[] = $omnify_limit;
		$omnify_params[] = $omnify_offset;

		$omnify_sql = "SELECT * FROM {$this->table('customers')} WHERE {$omnify_where} ORDER BY {$omnify_order_by} LIMIT %d OFFSET %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, $omnify_params), ARRAY_A);

		$omnify_customers = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_customers_results', $omnify_customers, $omnify_args);
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE id = %d", $omnify_id), ARRAY_A);

		$omnify_customer = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_customer', $omnify_customer, $omnify_id);
	}

	public function find_by_email(string $omnify_email): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE email = %s", sanitize_email($omnify_email)), ARRAY_A);

		return is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
	}

	public function find_by_user_id(int $omnify_user_id): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('customers')} WHERE user_id = %d", $omnify_user_id), ARRAY_A);

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

		$omnify_updated = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('customers'), $omnify_row, ['id' => $omnify_id]);

		if ($omnify_updated) {
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

		$this->clean_customer_caches_by_id($omnify_id);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_notes'), ['customer_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_activity'), ['customer_id' => $omnify_id]);

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
	}

	/**
	 * Find customer IDs matching a user ID or email.
	 */
	public function find_ids_by_user_id_or_email(int $omnify_user_id, string $omnify_email): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_cust_ids_' . $omnify_user_id . '_' . md5($omnify_email);
		$omnify_ids = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_ids) {
			$omnify_customers_table = $this->table('customers');
			$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
				'SELECT id FROM %i WHERE (user_id = %d OR email = %s) AND deleted_at IS NULL',
				$omnify_customers_table,
				$omnify_user_id,
				$omnify_email
			), ARRAY_A);
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
