<?php
/**
 * Order persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Order_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_orders_args', $omnify_args);

		$omnify_limit  = min(100, max(1, absint($omnify_args['per_page'] ?? 20)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_orders_table    = $this->table('orders');
		$omnify_customers_table = $this->table('customers');

		$omnify_status = sanitize_key((string) ($omnify_args['status'] ?? 'all'));
		$omnify_where  = 'trash' === $omnify_status ? 'o.deleted_at IS NOT NULL' : 'o.deleted_at IS NULL';
		$omnify_params = [];

		if ($omnify_status && 'all' !== $omnify_status && 'trash' !== $omnify_status) {
			$omnify_where .= ' AND o.status = %s';
			$omnify_params[] = $omnify_status;
		}

		if (! empty($omnify_args['customer_id'])) {
			if (is_array($omnify_args['customer_id'])) {
				$omnify_ids = array_map('intval', $omnify_args['customer_id']);
				if (! empty($omnify_ids)) {
					$omnify_placeholders = implode( ',', array_fill( 0, count( $omnify_ids ), '%d' ) );
					$omnify_where .= " AND o.customer_id IN ($omnify_placeholders)";
					$omnify_params = array_merge( $omnify_params, $omnify_ids );
				}
			} else {
				$omnify_where    .= ' AND o.customer_id = %d';
				$omnify_params[] = absint($omnify_args['customer_id']);
			}
		}

		if (! empty($omnify_args['search'])) {
			$omnify_search = '%' . $wpdb->esc_like(sanitize_text_field($omnify_args['search'])) . '%';
			$omnify_where .= " AND (o.order_number LIKE %s OR c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s)";
			$omnify_params = array_merge($omnify_params, [$omnify_search, $omnify_search, $omnify_search, $omnify_search]);
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= " AND DATE(o.created_at) >= %s";
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= " AND DATE(o.created_at) <= %s";
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		if (! empty($omnify_args['payment_method'])) {
			$omnify_where .= " AND o.payment_method = %s";
			$omnify_params[] = sanitize_text_field((string) $omnify_args['payment_method']);
		}

		if (! empty($omnify_args['product_type'])) {
			$omnify_product_type = sanitize_key((string) $omnify_args['product_type']);
			if ('digital' === $omnify_product_type) {
				$omnify_product_type = 'download';
			}
			$omnify_items_table = $this->table('order_items');
			$omnify_products_table = $this->table('products');
			$omnify_where .= " AND o.id IN (SELECT oi_sub.order_id FROM {$omnify_items_table} oi_sub JOIN {$omnify_products_table} p_sub ON oi_sub.product_id = p_sub.id WHERE p_sub.type = %s)";
			$omnify_params[] = $omnify_product_type;
		}

		$omnify_order_by = 'o.created_at DESC';
		$omnify_sort = sanitize_key((string) ($omnify_args['sort'] ?? 'created_desc'));
		switch ($omnify_sort) {
			case 'created_asc': $omnify_order_by = 'o.created_at ASC'; break;
			case 'total_desc': $omnify_order_by = 'o.total DESC'; break;
			case 'total_asc': $omnify_order_by = 'o.total ASC'; break;
		}

		$omnify_sql = "
			SELECT 
				o.*,
				c.email as customer_email,
				c.first_name as customer_first_name,
				c.last_name as customer_last_name
			FROM {$omnify_orders_table} o
			LEFT JOIN {$omnify_customers_table} c ON o.customer_id = c.id
			WHERE {$omnify_where}
			ORDER BY {$omnify_order_by} 
			LIMIT %d OFFSET %d
		";

		$omnify_params[] = $omnify_limit;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);

		if (empty($omnify_rows) || ! is_array($omnify_rows)) {
			return [];
		}

		$omnify_order_ids = array_map( 'intval', array_column( $omnify_rows, 'id' ) );

		// Fetch items and notes for these orders in batch (eliminates N+1 queries)
		$omnify_items_rows = [];
		$omnify_notes_rows = [];
		if ( ! empty( $omnify_order_ids ) ) {
			$omnify_order_items_table = $this->table('order_items');
			$omnify_order_notes_table = $this->table('order_notes');
			$omnify_placeholders = implode( ',', array_fill( 0, count( $omnify_order_ids ), '%d' ) );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$omnify_items = \Omnify\eCommerce\Support\Omnify_DB::get_results(
				$wpdb,
				$wpdb->prepare( "SELECT * FROM {$omnify_order_items_table} WHERE order_id IN ({$omnify_placeholders})", ...$omnify_order_ids ),
				ARRAY_A
			);
			if ( is_array( $omnify_items ) ) {
				$omnify_items_rows = $omnify_items;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$omnify_notes = \Omnify\eCommerce\Support\Omnify_DB::get_results(
				$wpdb,
				$wpdb->prepare( "SELECT * FROM {$omnify_order_notes_table} WHERE order_id IN ({$omnify_placeholders}) ORDER BY created_at ASC", ...$omnify_order_ids ),
				ARRAY_A
			);
			if ( is_array( $omnify_notes ) ) {
				$omnify_notes_rows = $omnify_notes;
			}
		}

		$omnify_items_by_order = [];
		foreach ( is_array( $omnify_items_rows ) ? $omnify_items_rows : [] as $omnify_item ) {
			$omnify_items_by_order[ (int) $omnify_item['order_id'] ][] = $omnify_item;
		}

		$omnify_notes_by_order = [];
		foreach ( is_array( $omnify_notes_rows ) ? $omnify_notes_rows : [] as $omnify_note ) {
			$omnify_notes_by_order[ (int) $omnify_note['order_id'] ][] = $omnify_note;
		}

		$omnify_results = [];
		foreach ($omnify_rows as $omnify_row) {
			$omnify_id        = (int) $omnify_row['id'];
			$omnify_results[] = $this->hydrate(
				$omnify_row,
				$omnify_items_by_order[$omnify_id] ?? [],
				$omnify_notes_by_order[$omnify_id] ?? []
			);
		}

		$omnify_results = apply_filters('omnify_get_orders_results', $omnify_results, $omnify_args);
		return $omnify_results;
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		$omnify_orders_table    = $this->table('orders');
		$omnify_customers_table = $this->table('customers');

		$omnify_sql = "
			SELECT 
				o.*,
				c.email as customer_email,
				c.first_name as customer_first_name,
				c.last_name as customer_last_name
			FROM {$omnify_orders_table} o
			LEFT JOIN {$omnify_customers_table} c ON o.customer_id = c.id
			WHERE o.id = %d
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare($omnify_sql, $omnify_id), ARRAY_A);

		if (! is_array($omnify_row)) {
			return null;
		}

		$omnify_order_items_table = $this->table('order_items');
		$omnify_order_notes_table = $this->table('order_notes');

		$omnify_items = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare('SELECT * FROM %i WHERE order_id = %d', $omnify_order_items_table, $omnify_id),
			ARRAY_A
		);

		$omnify_notes = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare('SELECT * FROM %i WHERE order_id = %d ORDER BY created_at ASC', $omnify_order_notes_table, $omnify_id),
			ARRAY_A
		);

		$omnify_order = $this->hydrate($omnify_row, is_array($omnify_items) ? $omnify_items : [], is_array($omnify_notes) ? $omnify_notes : []);
		return apply_filters('omnify_get_order', $omnify_order, $omnify_id);
	}

	public function find_id_by_order_number(string $omnify_order_number): int {
		global $wpdb;

		$omnify_order_number = sanitize_text_field($omnify_order_number);
		if ('' === $omnify_order_number) {
			return 0;
		}

		$omnify_cache_key = 'omnify_order_id_by_number_' . md5($omnify_order_number);
		$omnify_cached = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return (int) $omnify_cached;
		}

		$omnify_table = $this->table('orders');
		$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SELECT id FROM %i WHERE order_number = %s', $omnify_table, $omnify_order_number));
		wp_cache_set($omnify_cache_key, $omnify_order_id, 'omnifywp-ecommerce', HOUR_IN_SECONDS);

		return $omnify_order_id;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function for_customer(int $omnify_customer_id): array {
		return $this->all(['customer_id' => $omnify_customer_id, 'per_page' => 100]);
	}

	/**
	 * Check whether a customer has purchased a specific product in a completed or processing order.
	 */
	public function has_customer_purchased_product(int $omnify_customer_id, int $omnify_product_id): bool {
		global $wpdb;
		$omnify_items_table = $this->table('order_items');
		$omnify_orders_table = $this->table('orders');

		$omnify_found = \Omnify\eCommerce\Support\Omnify_DB::get_var(
			$wpdb,
			$wpdb->prepare(
				"SELECT 1 FROM %i oi
				 JOIN %i o ON oi.order_id = o.id
				 WHERE o.customer_id = %d
				   AND oi.product_id = %d
				   AND o.status IN ('completed', 'processing', 'shipped')
				 LIMIT 1",
				$omnify_items_table,
				$omnify_orders_table,
				$omnify_customer_id,
				$omnify_product_id
			)
		);

		return ! empty($omnify_found);
	}

	/**
	 * Get total quantity sold for a product in the last N hours (for urgency).
	 */
	public function get_product_sales_count_last_hours(int $omnify_product_id, int $omnify_hours = 18): int {
		global $wpdb;
		$omnify_items_table = $this->table('order_items');
		$omnify_orders_table = $this->table('orders');
		$omnify_since = gmdate('Y-m-d H:i:s', time() - ($omnify_hours * 3600));
		$omnify_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb,  $wpdb->prepare(
			"SELECT SUM(oi.quantity) 
			 FROM %i oi 
			 JOIN %i o ON oi.order_id = o.id 
			 WHERE oi.product_id = %d 
			   AND o.status IN ('completed','processing','shipped') 
			   AND o.created_at >= %s
			   AND o.deleted_at IS NULL",
			$omnify_items_table,
			$omnify_orders_table,
			$omnify_product_id,
			$omnify_since
		));
		return max(0, $omnify_count);
	}

	public function create(array $omnify_data): int {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_create_order_data', $omnify_data);

		$omnify_now   = $this->now();
		$omnify_items = $omnify_data['items'] ?? [];

		$omnify_subtotal = isset($omnify_data['subtotal']) ? (float) $omnify_data['subtotal'] : 0.0;
		$omnify_tax      = isset($omnify_data['tax']) ? (float) $omnify_data['tax'] : 0.0;
		$omnify_shipping = isset($omnify_data['shipping_total']) ? (float) $omnify_data['shipping_total'] : 0.0;
		$omnify_total    = isset($omnify_data['total']) ? (float) $omnify_data['total'] : 0.0;

		// Calculate total if not supplied but items are present
		if (empty($omnify_subtotal) && empty($omnify_total) && ! empty($omnify_items)) {
			foreach ($omnify_items as $omnify_item) {
				$omnify_qty        = max(1, intval($omnify_item['quantity'] ?? 1));
				$omnify_item_price = (float) ($omnify_item['price'] ?? 0);
				$omnify_item_tax   = (float) ($omnify_item['tax'] ?? 0);

				$omnify_subtotal += $omnify_item_price * $omnify_qty;
				$omnify_tax      += $omnify_item_tax * $omnify_qty;
			}
			$omnify_total = $omnify_subtotal + $omnify_tax + $omnify_shipping;
		}

		// First product ID for backwards compatibility
		$omnify_first_product_id = ! empty($omnify_items) ? absint($omnify_items[0]['product_id'] ?? 0) : absint($omnify_data['product_id'] ?? 0);

		if (empty($omnify_items) && ! empty($omnify_first_product_id)) {
			$omnify_products_table = $this->table('products');
			$omnify_product_name   = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT name FROM %i WHERE id = %d',
				$omnify_products_table,
				$omnify_first_product_id
			)) ?: 'Product';

			$omnify_items = [
				[
					'product_id'   => $omnify_first_product_id,
					'product_name' => $omnify_product_name,
					'price'        => $omnify_total,
					'tax'          => $omnify_tax,
					'quantity'     => 1,
				],
			];
			$omnify_subtotal = $omnify_total - $omnify_tax;
		}

		// --- Fraud & Risk Engine ---
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_fraud_country_mismatch_flag = ! isset($omnify_settings['fraud_country_mismatch_flag']) ? true : ! empty($omnify_settings['fraud_country_mismatch_flag']);
		$omnify_fraud_disposable_email_flag = ! isset($omnify_settings['fraud_disposable_email_flag']) ? true : ! empty($omnify_settings['fraud_disposable_email_flag']);
		$omnify_fraud_max_value             = isset($omnify_settings['fraud_max_value']) ? (float) $omnify_settings['fraud_max_value'] : 500.00;
		$omnify_fraud_max_attempts_limit    = isset($omnify_settings['fraud_max_attempts_limit']) ? (int) $omnify_settings['fraud_max_attempts_limit'] : 3;

		$omnify_ip_address = $this->get_visitor_ip();
		$omnify_email      = '';
		$omnify_customer_id = absint($omnify_data['customer_id'] ?? 0);
		if ($omnify_customer_id > 0) {
			$omnify_customers_table = $this->table('customers');
			$omnify_email = (string) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT email FROM %i WHERE id = %d',
				$omnify_customers_table,
				$omnify_customer_id
			));
		}

		$omnify_fraud_score = 0;
		$omnify_fraud_flags = [];

		// 1. Country mismatch
		$omnify_billing_country  = ! empty($omnify_data['billing_country']) ? trim(strtoupper($omnify_data['billing_country'])) : '';
		$omnify_shipping_country = ! empty($omnify_data['shipping_country']) ? trim(strtoupper($omnify_data['shipping_country'])) : '';
		if ($omnify_fraud_country_mismatch_flag && ! empty($omnify_billing_country) && ! empty($omnify_shipping_country) && $omnify_billing_country !== $omnify_shipping_country) {
			$omnify_fraud_flags[] = sprintf(
				/* translators: 1: billing country, 2: shipping country */
				__('Billing & shipping country mismatch (Billing: %1$s, Shipping: %2$s)', 'omnifywp-ecommerce'),
				$omnify_billing_country,
				$omnify_shipping_country
			);
			$omnify_fraud_score += 25;
		}

		// 2. Disposable Email
		if ($omnify_fraud_disposable_email_flag && ! empty($omnify_email)) {
			$omnify_domain = strtolower(substr((string) strrchr($omnify_email, '@'), 1));
			$omnify_disposable_domains = [
				'yopmail.com', 'mailinator.com', 'tempmail.com', 'temp-mail.org',
				'sharklasers.com', 'guerrillamail.com', 'dispostable.com',
				'getairmail.com', '10minutemail.com', 'tempmailaddress.com',
				'throwawaymail.com', 'maildrop.cc'
			];
			if (in_array($omnify_domain, $omnify_disposable_domains, true)) {
				$omnify_fraud_flags[] = sprintf(
					/* translators: %s: disposable email domain */
					__('Disposable email address domain detected: %s', 'omnifywp-ecommerce'),
					$omnify_domain
				);
				$omnify_fraud_score += 25;
			}
		}

		// 3. High Value Order
		if ($omnify_total >= $omnify_fraud_max_value) {
			$omnify_fraud_flags[] = sprintf(
				/* translators: 1: order total, 2: threshold */
				__('High value order (%1$.2f) exceeds threshold of %2$.2f', 'omnifywp-ecommerce'),
				$omnify_total,
				$omnify_fraud_max_value
			);
			$omnify_fraud_score += 35;
		}

		// 4. Velocity check
		if (! empty($omnify_ip_address)) {
			$omnify_ten_minutes_ago = gmdate('Y-m-d H:i:s', time() - 600);
			$omnify_orders_table = $this->table('orders');
			$omnify_recent_orders_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE ip_address = %s AND created_at >= %s',
				$omnify_orders_table,
				$omnify_ip_address,
				$omnify_ten_minutes_ago
			));
			if ($omnify_recent_orders_count >= $omnify_fraud_max_attempts_limit) {
				$omnify_fraud_flags[] = sprintf(
					/* translators: 1: count, 2: limit */
					__('Velocity limit exceeded: %1$d orders placed from IP %2$s in last 10 minutes (limit: %3$d)', 'omnifywp-ecommerce'),
					$omnify_recent_orders_count,
					$omnify_ip_address,
					$omnify_fraud_max_attempts_limit
				);
				$omnify_fraud_score += 40;
			}
		}

		$omnify_fraud_status = 'safe';
		if ($omnify_fraud_score >= 40) {
			$omnify_fraud_status = 'high_risk';
		} elseif ($omnify_fraud_score > 0) {
			$omnify_fraud_status = 'flagged';
		}
		// --- End Fraud & Risk Engine ---

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('orders'),
			[
				'customer_id'          => absint($omnify_data['customer_id'] ?? 0) ?: null,
				'product_id'           => $omnify_first_product_id ?: null,
				'status'               => sanitize_key((string) ($omnify_data['status'] ?? 'pending')),
				'currency'             => strtoupper(substr(sanitize_text_field((string) ($omnify_data['currency'] ?? 'USD')), 0, 3)),
				'subtotal'             => $omnify_subtotal,
				'tax'                  => $omnify_tax,
				'tax_label'            => ! empty($omnify_data['tax_label']) ? sanitize_text_field((string) $omnify_data['tax_label']) : null,
				'tax_rate'             => (float) ($omnify_data['tax_rate'] ?? 0.0),
				'tax_reporting_code'   => ! empty($omnify_data['tax_reporting_code']) ? sanitize_text_field((string) $omnify_data['tax_reporting_code']) : null,
				'tax_inclusive'        => ! empty($omnify_data['tax_inclusive']) ? 1 : 0,
				'tax_shipping'         => ! empty($omnify_data['tax_shipping']) ? 1 : 0,
				'customer_tax_exempt'  => ! empty($omnify_data['customer_tax_exempt']) ? 1 : 0,
				'shipping_total'       => $omnify_shipping,
				'shipping_method'      => ! empty($omnify_data['shipping_method']) ? sanitize_text_field($omnify_data['shipping_method']) : null,
				'total'                => $omnify_total,
				'refunded_amount'      => 0.0,
				'coupon_code'          => ! empty($omnify_data['coupon_code']) ? sanitize_text_field($omnify_data['coupon_code']) : null,
				'discount_amount'      => (float) ($omnify_data['discount_amount'] ?? 0.0),
				'payment_method'       => ! empty($omnify_data['payment_method']) ? sanitize_text_field($omnify_data['payment_method']) : null,
				'payment_instructions' => ! empty($omnify_data['payment_instructions']) ? sanitize_textarea_field($omnify_data['payment_instructions']) : null,
				'shipping_first_name'  => ! empty($omnify_data['shipping_first_name']) ? sanitize_text_field($omnify_data['shipping_first_name']) : null,
				'shipping_last_name'   => ! empty($omnify_data['shipping_last_name']) ? sanitize_text_field($omnify_data['shipping_last_name']) : null,
				'shipping_address_1'   => ! empty($omnify_data['shipping_address_1']) ? sanitize_text_field($omnify_data['shipping_address_1']) : null,
				'shipping_address_2'   => ! empty($omnify_data['shipping_address_2']) ? sanitize_text_field($omnify_data['shipping_address_2']) : null,
				'shipping_city'        => ! empty($omnify_data['shipping_city']) ? sanitize_text_field($omnify_data['shipping_city']) : null,
				'shipping_state'       => ! empty($omnify_data['shipping_state']) ? sanitize_text_field($omnify_data['shipping_state']) : null,
				'shipping_postcode'    => ! empty($omnify_data['shipping_postcode']) ? sanitize_text_field($omnify_data['shipping_postcode']) : null,
				'shipping_country'     => ! empty($omnify_data['shipping_country']) ? sanitize_text_field($omnify_data['shipping_country']) : null,
				'shipping_phone'       => ! empty($omnify_data['shipping_phone']) ? sanitize_text_field($omnify_data['shipping_phone']) : null,
				'billing_first_name'   => ! empty($omnify_data['billing_first_name']) ? sanitize_text_field($omnify_data['billing_first_name']) : null,
				'billing_last_name'    => ! empty($omnify_data['billing_last_name']) ? sanitize_text_field($omnify_data['billing_last_name']) : null,
				'billing_company'      => ! empty($omnify_data['billing_company']) ? sanitize_text_field($omnify_data['billing_company']) : null,
				'billing_phone'        => ! empty($omnify_data['billing_phone']) ? sanitize_text_field($omnify_data['billing_phone']) : null,
				'billing_address_1'    => ! empty($omnify_data['billing_address_1']) ? sanitize_text_field($omnify_data['billing_address_1']) : null,
				'billing_address_2'    => ! empty($omnify_data['billing_address_2']) ? sanitize_text_field($omnify_data['billing_address_2']) : null,
				'billing_city'         => ! empty($omnify_data['billing_city']) ? sanitize_text_field($omnify_data['billing_city']) : null,
				'billing_state'        => ! empty($omnify_data['billing_state']) ? sanitize_text_field($omnify_data['billing_state']) : null,
				'billing_postcode'     => ! empty($omnify_data['billing_postcode']) ? sanitize_text_field($omnify_data['billing_postcode']) : null,
				'billing_country'      => ! empty($omnify_data['billing_country']) ? sanitize_text_field($omnify_data['billing_country']) : null,
				'billing_same_as_shipping' => isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1,
				'transaction_id'       => ! empty($omnify_data['transaction_id']) ? sanitize_text_field($omnify_data['transaction_id']) : null,
				'order_number'         => ! empty($omnify_data['order_number']) ? sanitize_text_field($omnify_data['order_number']) : null,
				'fulfillment_status'   => ! empty($omnify_data['fulfillment_status']) ? sanitize_key($omnify_data['fulfillment_status']) : 'none',
				'tracking_number'      => ! empty($omnify_data['tracking_number']) ? sanitize_text_field($omnify_data['tracking_number']) : null,
				'tracking_carrier'     => ! empty($omnify_data['tracking_carrier']) ? sanitize_text_field($omnify_data['tracking_carrier']) : null,
				'fraud_status'         => $omnify_fraud_status,
				'fraud_score'          => $omnify_fraud_score,
				'fraud_flags'          => ! empty($omnify_fraud_flags) ? wp_json_encode($omnify_fraud_flags) : null,
				'ip_address'           => ! empty($omnify_ip_address) ? $omnify_ip_address : null,
				'created_at'           => $omnify_now,
				'updated_at'           => $omnify_now,
			]
		);

		$omnify_order_id = (int) $wpdb->insert_id;

		if ($omnify_order_id > 0 && empty($omnify_data['order_number'])) {
			$omnify_settings = get_option('omnify_settings', []);
			$omnify_prefix   = sanitize_text_field((string) ($omnify_settings['order_number_prefix'] ?? 'OMN-'));
			$omnify_suffix   = sanitize_text_field((string) ($omnify_settings['order_number_suffix'] ?? ''));
			$omnify_padding  = max(1, min(10, absint($omnify_settings['order_number_padding'] ?? 4)));
			$omnify_order_number = $omnify_prefix . str_pad((string) $omnify_order_id, $omnify_padding, '0', STR_PAD_LEFT) . $omnify_suffix;

			\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
				$this->table('orders'),
				['order_number' => $omnify_order_number],
				['id' => $omnify_order_id]
			);
		}

		// Insert order items
		if ($omnify_order_id > 0 && ! empty($omnify_items)) {
			foreach ($omnify_items as $omnify_item) {
				\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
					$this->table('order_items'),
					[
						'order_id'     => $omnify_order_id,
						'product_id'   => absint($omnify_item['product_id'] ?? 0),
						'variation_id' => ! empty($omnify_item['variation_id']) ? absint($omnify_item['variation_id']) : null,
						'product_name' => sanitize_text_field((string) ($omnify_item['product_name'] ?? 'Product')),
						'price'        => (float) ($omnify_item['price'] ?? 0),
						'tax'          => (float) ($omnify_item['tax'] ?? 0),
						'quantity'     => max(1, intval($omnify_item['quantity'] ?? 1)),
						'created_at'   => $omnify_now,
					]
				);
			}
		}

		if ($omnify_order_id > 0) {
			do_action('omnify_order_created', $omnify_order_id, $omnify_data);
		}

		return $omnify_order_id;
	}

	/**
	 * Update order status.
	 */
	public function update_status(int $omnify_order_id, string $omnify_status): bool {
		global $wpdb;

		$omnify_orders_table = $this->table('orders');
		$omnify_old_status = (string) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SELECT status FROM %i WHERE id = %d', $omnify_orders_table, $omnify_order_id));
		$omnify_status = sanitize_key($omnify_status);

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			[
				'status'     => $omnify_status,
				'updated_at' => $this->now(),
			],
			['id' => $omnify_order_id]
		);

		$omnify_success = $omnify_updated !== false;
		if ($omnify_success && $omnify_old_status !== $omnify_status) {
			do_action('omnify_order_status_changed', $omnify_order_id, $omnify_status, $omnify_old_status);
		}

		return $omnify_success;
	}

	/**
	 * Update an order's general data.
	 */
	public function update(int $omnify_id, array $omnify_data): bool {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_update_order_data', $omnify_data, $omnify_id);

		$omnify_row = ['updated_at' => $this->now()];

		$omnify_fields = [
			'customer_id'         => 'intval',
			'status'             => 'sanitize_key',
			'currency'           => 'sanitize_text_field',
			'payment_method'     => 'sanitize_text_field',
			'transaction_id'     => 'sanitize_text_field',
			'tracking_carrier'   => 'sanitize_text_field',
			'tracking_number'    => 'sanitize_text_field',
			'fulfillment_status' => 'sanitize_key',
			'fraud_status'       => 'sanitize_key',
			'fraud_score'        => 'intval',
			'ip_address'         => 'sanitize_text_field',
			'coupon_code'        => 'sanitize_text_field',
			'subtotal'           => 'floatval',
			'tax'                => 'floatval',
			'shipping_total'     => 'floatval',
			'discount_amount'    => 'floatval',
			'total'              => 'floatval',
		];

		foreach ($omnify_fields as $omnify_field => $omnify_sanitize_fn) {
			if (array_key_exists($omnify_field, $omnify_data)) {
				if ('intval' === $omnify_sanitize_fn) {
					$omnify_row[$omnify_field] = intval($omnify_data[$omnify_field]);
				} elseif ('floatval' === $omnify_sanitize_fn) {
					$omnify_row[$omnify_field] = floatval($omnify_data[$omnify_field]);
				} else {
					$omnify_row[$omnify_field] = $omnify_sanitize_fn((string) $omnify_data[$omnify_field]);
				}
			}
		}

		if (array_key_exists('fraud_flags', $omnify_data)) {
			$omnify_row['fraud_flags'] = is_array($omnify_data['fraud_flags']) ? wp_json_encode($omnify_data['fraud_flags']) : null;
		}

		$omnify_old_order = $this->find($omnify_id);
		$omnify_updated = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('orders'), $omnify_row, ['id' => $omnify_id]);

		if ($omnify_updated) {
			do_action('omnify_order_updated', $omnify_id, $omnify_data);
			if (isset($omnify_row['status']) && $omnify_old_order && $omnify_old_order['status'] !== $omnify_row['status']) {
				do_action('omnify_order_status_changed', $omnify_id, $omnify_row['status'], $omnify_old_order['status']);
			}
		}

		return $omnify_updated;
	}

	public function begin_transaction(): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'START TRANSACTION');
	}

	public function commit_transaction(): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'COMMIT');
	}

	public function update_refund_summary(int $omnify_id, float $omnify_refunded_amount, string $omnify_status): bool {
		global $wpdb;

		return false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			[
				'refunded_amount' => $omnify_refunded_amount,
				'status'          => sanitize_key($omnify_status),
				'updated_at'      => $this->now(),
			],
			['id' => $omnify_id]
		);
	}

	public function increment_item_refunded_qty(int $omnify_item_id, int $omnify_qty): void {
		global $wpdb;

		$omnify_table = $this->table('order_items');
		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
			'UPDATE %i SET refunded_qty = refunded_qty + %d WHERE id = %d',
			$omnify_table,
			$omnify_qty,
			$omnify_item_id
		));
	}

	/**
	 * Update transaction ID.
	 */
	public function update_transaction_id(int $omnify_order_id, string $omnify_transaction_id): bool {
		global $wpdb;

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			[
				'transaction_id' => sanitize_text_field($omnify_transaction_id),
				'updated_at'     => $this->now(),
			],
			['id' => $omnify_order_id]
		);

		return $omnify_updated !== false;
	}

	/**
	 * Update fulfillment status and tracking.
	 */
	public function update_fulfillment(int $omnify_order_id, string $omnify_status, ?string $omnify_carrier = null, ?string $omnify_tracking = null): bool {
		global $wpdb;

		$omnify_data = [
			'fulfillment_status' => sanitize_key($omnify_status),
			'updated_at'         => $this->now(),
		];

		if (null !== $omnify_carrier) {
			$omnify_data['tracking_carrier'] = sanitize_text_field($omnify_carrier);
		}
		if (null !== $omnify_tracking) {
			$omnify_data['tracking_number'] = sanitize_text_field($omnify_tracking);
		}

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			$omnify_data,
			['id' => $omnify_order_id]
		);

		if ($omnify_updated !== false) {
			if ($omnify_status === 'fulfilled') {
				$omnify_note = __('Order marked as fulfilled.', 'omnifywp-ecommerce');
				if (! empty($omnify_carrier) || ! empty($omnify_tracking)) {
					$omnify_note .= sprintf(
						// translators: %1$s: placeholder value, %2$s: placeholder value.
						' ' . __('Carrier: %1$s, Tracking Number: %2$s.', 'omnifywp-ecommerce'),
						$omnify_carrier ?: 'N/A',
						$omnify_tracking ?: 'N/A'
					);
				}
			} else {
				$omnify_note = sprintf(
					/* translators: %s: fulfillment status */
					__('Fulfillment status updated to: %s.', 'omnifywp-ecommerce'),
					$omnify_status
				);
			}
			$this->add_note($omnify_order_id, $omnify_note);
			return true;
		}

		return false;
	}

	/**
	 * Update fraud and risk flags info.
	 */
	public function update_fraud_info(int $omnify_order_id, string $omnify_status, int $omnify_score, ?array $omnify_flags): bool {
		global $wpdb;

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			[
				'fraud_status' => sanitize_key($omnify_status),
				'fraud_score'  => max(0, min(100, $omnify_score)),
				'fraud_flags'  => ! empty($omnify_flags) ? wp_json_encode($omnify_flags) : null,
				'updated_at'   => $this->now(),
			],
			['id' => $omnify_order_id]
		);

		return $omnify_updated !== false;
	}

	/**
	 * Record a refund.
	 */
	public function refund(int $omnify_order_id, float $omnify_amount): bool {
		global $wpdb;

		$omnify_order = $this->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_new_refunded = (float) $omnify_order['refunded_amount'] + $omnify_amount;
		if ($omnify_new_refunded > (float) $omnify_order['total']) {
			$omnify_new_refunded = (float) $omnify_order['total'];
		}

		$omnify_status = $omnify_order['status'];
		if ($omnify_new_refunded >= (float) $omnify_order['total']) {
			$omnify_status = 'refunded';
		}

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			[
				'refunded_amount' => $omnify_new_refunded,
				'status'          => $omnify_status,
				'updated_at'      => $this->now(),
			],
			['id' => $omnify_order_id]
		);

		if ($omnify_updated !== false) {
			// Add order note about the refund
			$this->add_note(
				$omnify_order_id,
				sprintf(
					/* translators: %s: refund amount */
					__('Refunded %s.', 'omnifywp-ecommerce'),
					$omnify_order['currency'] . ' ' . number_format($omnify_amount, 2)
				)
			);
			return true;
		}

		return false;
	}

	/**
	 * Trash (soft delete) an order.
	 */
	public function trash(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_order_trashed', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('orders'),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_order_restored', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	/**
	 * Delete an order, its items, and its notes (permanent).
	 */
	public function delete(int $omnify_id): bool {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('order_notes'), ['order_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('order_items'), ['order_id' => $omnify_id]);
		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('orders'), ['id' => $omnify_id]);

		if ($omnify_deleted) {
			do_action('omnify_order_deleted', $omnify_id);
		}

		return $omnify_deleted;
	}

	/**
	 * Add order note.
	 */
	public function add_note(int $omnify_order_id, string $omnify_note, ?int $omnify_author_id = null, string $omnify_note_type = 'private', bool $omnify_customer_visible = false): int {
		global $wpdb;

		if (null === $omnify_author_id && is_user_logged_in()) {
			$omnify_author_id = get_current_user_id();
		}

		$omnify_note_type = sanitize_key($omnify_note_type);
		if (! in_array($omnify_note_type, ['private', 'customer', 'system'], true)) {
			$omnify_note_type = 'private';
		}

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('order_notes'),
			[
				'order_id'          => $omnify_order_id,
				'author_id'         => $omnify_author_id,
				'note_type'         => $omnify_note_type,
				'customer_visible'  => $omnify_customer_visible || 'customer' === $omnify_note_type ? 1 : 0,
				'note'              => wp_kses_post($omnify_note),
				'created_at'        => $this->now(),
			]
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Delete order note.
	 */
	public function delete_note(int $omnify_order_id, int $omnify_note_id): bool {
		global $wpdb;

		$omnify_deleted = \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, 
			$this->table('order_notes'),
			[
				'id'       => $omnify_note_id,
				'order_id' => $omnify_order_id,
			]
		);

		return $omnify_deleted !== false;
	}

	private function hydrate(array $omnify_row, array $omnify_items = [], array $omnify_notes = []): array {
		$omnify_row['id']                   = (int) $omnify_row['id'];
		$omnify_row['customer_id']          = null === $omnify_row['customer_id'] ? null : (int) $omnify_row['customer_id'];
		$omnify_row['subtotal']             = (float) ($omnify_row['subtotal'] ?? 0);
		$omnify_row['tax']                  = (float) ($omnify_row['tax'] ?? 0);
		$omnify_row['tax_label']            = $omnify_row['tax_label'] ?? null;
		$omnify_row['tax_rate']             = (float) ($omnify_row['tax_rate'] ?? 0);
		$omnify_row['tax_reporting_code']   = $omnify_row['tax_reporting_code'] ?? null;
		$omnify_row['tax_inclusive']        = ! empty($omnify_row['tax_inclusive']);
		$omnify_row['tax_shipping']         = ! empty($omnify_row['tax_shipping']);
		$omnify_row['customer_tax_exempt']  = ! empty($omnify_row['customer_tax_exempt']);
		$omnify_row['shipping_total']       = (float) ($omnify_row['shipping_total'] ?? 0);
		$omnify_row['shipping_method']      = ! empty($omnify_row['shipping_method']) ? (string) $omnify_row['shipping_method'] : null;
		$omnify_row['total']                = (float) ($omnify_row['total'] ?? 0);
		$omnify_row['refunded_amount']      = (float) ($omnify_row['refunded_amount'] ?? 0);
		$omnify_row['coupon_code']          = null === $omnify_row['coupon_code'] ? null : (string) $omnify_row['coupon_code'];
		$omnify_row['discount_amount']      = (float) ($omnify_row['discount_amount'] ?? 0.0);
		$omnify_row['payment_method']       = ! empty($omnify_row['payment_method']) ? (string) $omnify_row['payment_method'] : null;
		$omnify_row['payment_instructions'] = ! empty($omnify_row['payment_instructions']) ? (string) $omnify_row['payment_instructions'] : null;
		$omnify_row['shipping_first_name']  = $omnify_row['shipping_first_name'] ?? null;
		$omnify_row['shipping_last_name']   = $omnify_row['shipping_last_name'] ?? null;
		$omnify_row['shipping_address_1']   = $omnify_row['shipping_address_1'] ?? null;
		$omnify_row['shipping_address_2']   = $omnify_row['shipping_address_2'] ?? null;
		$omnify_row['shipping_city']        = $omnify_row['shipping_city'] ?? null;
		$omnify_row['shipping_state']       = $omnify_row['shipping_state'] ?? null;
		$omnify_row['shipping_postcode']    = $omnify_row['shipping_postcode'] ?? null;
		$omnify_row['shipping_country']     = $omnify_row['shipping_country'] ?? null;
		$omnify_row['shipping_phone']       = $omnify_row['shipping_phone'] ?? null;
		$omnify_row['billing_first_name']   = $omnify_row['billing_first_name'] ?? null;
		$omnify_row['billing_last_name']    = $omnify_row['billing_last_name'] ?? null;
		$omnify_row['billing_company']      = $omnify_row['billing_company'] ?? null;
		$omnify_row['billing_phone']        = $omnify_row['billing_phone'] ?? null;
		$omnify_row['billing_address_1']    = $omnify_row['billing_address_1'] ?? null;
		$omnify_row['billing_address_2']    = $omnify_row['billing_address_2'] ?? null;
		$omnify_row['billing_city']         = $omnify_row['billing_city'] ?? null;
		$omnify_row['billing_state']        = $omnify_row['billing_state'] ?? null;
		$omnify_row['billing_postcode']     = $omnify_row['billing_postcode'] ?? null;
		$omnify_row['billing_country']      = $omnify_row['billing_country'] ?? null;
		$omnify_row['billing_same_as_shipping'] = isset($omnify_row['billing_same_as_shipping']) ? (int) $omnify_row['billing_same_as_shipping'] : 1;
		$omnify_row['transaction_id']       = $omnify_row['transaction_id'] ?? null;
		$omnify_row['order_number']         = $omnify_row['order_number'] ?? null;
		$omnify_row['fulfillment_status']   = $omnify_row['fulfillment_status'] ?? 'none';
		$omnify_row['tracking_number']      = $omnify_row['tracking_number'] ?? null;
		$omnify_row['tracking_carrier']     = $omnify_row['tracking_carrier'] ?? null;
		$omnify_row['fraud_status']         = $omnify_row['fraud_status'] ?? 'safe';
		$omnify_row['fraud_score']          = (int) ($omnify_row['fraud_score'] ?? 0);
		$omnify_row['fraud_flags']          = ! empty($omnify_row['fraud_flags']) ? json_decode($omnify_row['fraud_flags'], true) : [];
		$omnify_row['ip_address']           = $omnify_row['ip_address'] ?? null;

		$omnify_row['items'] = $omnify_items;
		$omnify_row['notes'] = array_map(
			static function(array $omnify_note): array {
				$omnify_note['id'] = (int) ($omnify_note['id'] ?? 0);
				$omnify_note['order_id'] = (int) ($omnify_note['order_id'] ?? 0);
				$omnify_note['author_id'] = null === ($omnify_note['author_id'] ?? null) ? null : (int) $omnify_note['author_id'];
				$omnify_note['note_type'] = sanitize_key((string) ($omnify_note['note_type'] ?? 'private')) ?: 'private';
				$omnify_note['customer_visible'] = ! empty($omnify_note['customer_visible']) ? 1 : 0;

				return $omnify_note;
			},
			$omnify_notes
		);

		if (empty($omnify_row['items'])) {
			$omnify_row['product_id']   = null === $omnify_row['product_id'] ? null : (int) $omnify_row['product_id'];
			$omnify_row['product_name'] = $omnify_row['product_name'] ?? '';
		} else {
			$omnify_row['product_id']   = (int) $omnify_row['items'][0]['product_id'];
			$omnify_names               = array_column($omnify_row['items'], 'product_name');
			$omnify_row['product_name'] = implode(', ', $omnify_names);
		}

		if (array_key_exists('customer_first_name', $omnify_row) || array_key_exists('customer_last_name', $omnify_row)) {
			$omnify_row['customer_name'] = trim((string) ($omnify_row['customer_first_name'] ?? '') . ' ' . (string) ($omnify_row['customer_last_name'] ?? ''));
		}

		return $omnify_row;
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function now(): string {
		return current_time('mysql', true);
	}

	/**
	 * Get visitor IP address.
	 */
	public function get_visitor_ip(): string {
		$omnify_ip = '';
		if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (! empty($_SERVER['REMOTE_ADDR'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['REMOTE_ADDR'];
		}

		if (strpos($omnify_ip, ',') !== false) {
			$omnify_parts = explode(',', $omnify_ip);
			$omnify_ip = trim($omnify_parts[0]);
		}

		return sanitize_text_field((string) $omnify_ip);
	}

	/**
	 * Check if a customer has purchased a product and the order status is completed or processing.
	 */
	public function has_purchased_product(string $omnify_email, int $omnify_product_id): bool {
		global $wpdb;
		$omnify_email = sanitize_email($omnify_email);
		if (empty($omnify_email)) {
			return false;
		}

		$omnify_orders_table = $this->table('orders');
		$omnify_customers_table = $this->table('customers');
		$omnify_order_items_table = $this->table('order_items');

		$omnify_sql = "
			SELECT COUNT(*) 
			FROM {$omnify_orders_table} o
			JOIN {$omnify_customers_table} c ON o.customer_id = c.id
			JOIN {$omnify_order_items_table} oi ON oi.order_id = o.id
			WHERE c.email = %s 
			  AND oi.product_id = %d 
			  AND o.status IN ('completed', 'processing')
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_count = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare($omnify_sql, $omnify_email, $omnify_product_id));
		return ((int)$omnify_count) > 0;
	}
}
