<?php
/**
 * GDPR Privacy and personal data handler.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;

class Omnify_Privacy_Service {

	/**
	 * Constructor.
	 *
	 * @param Schema                    $schema                   Database schema wrapper.
	 * @param Customer_Repository       $customer_repo            Customer repository.
	 * @param Order_Repository          $order_repo               Order repository.
	 * @param Review_Repository         $review_repo              Review repository.
	 * @param Abandoned_Cart_Repository $abandoned_cart_repo      Abandoned cart repository.
	 */
	public function __construct(
		private Omnify_Schema $omnify_schema,
		private Omnify_Customer_Repository $omnify_customer_repo,
		private Omnify_Order_Repository $omnify_order_repo,
		private Omnify_Review_Repository $omnify_review_repo,
		private Omnify_Abandoned_Cart_Repository $omnify_abandoned_cart_repo
	) {}

	/**
	 * Register personal data exporters.
	 *
	 * @param array $exporters Array of registered exporters.
	 * @return array Updated array of exporters.
	 */
	public function register_exporters( array $omnify_exporters ): array {
		$omnify_exporters['omnify'] = [
			'exporter_friendly_name' => __( 'Omnify', 'omnifywp-ecommerce'),
			'callback'               => [ $this, 'export_personal_data' ],
		];
		return $omnify_exporters;
	}

	/**
	 * Register personal data erasers.
	 *
	 * @param array $erasers Array of registered erasers.
	 * @return array Updated array of erasers.
	 */
	public function register_erasers( array $omnify_erasers ): array {
		$omnify_erasers['omnify'] = [
			'eraser_friendly_name' => __( 'Omnify', 'omnifywp-ecommerce'),
			'callback'             => [ $this, 'erase_personal_data' ],
		];
		return $omnify_erasers;
	}

	/**
	 * Export personal data associated with an email address.
	 *
	 * @param string $email_address The email address to export data for.
	 * @param int    $page          The page parameter for pagination.
	 * @return array Structured export response.
	 */
	public function export_personal_data( string $omnify_email_address, int $omnify_page = 1 ): array {
		global $wpdb;
		$omnify_email_address = sanitize_email( $omnify_email_address );
		$omnify_data_to_export = [];

		$omnify_customers_table = $this->omnify_schema->table( 'customers' );
		$omnify_orders_table    = $this->omnify_schema->table( 'orders' );
		$omnify_reviews_table   = $this->omnify_schema->table( 'reviews' );
		$omnify_products_table  = $this->omnify_schema->table( 'products' );

		// 1. Export Customer Profile
		$omnify_customer = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb,  $wpdb->prepare(
			'SELECT * FROM %i WHERE email = %s',
			$omnify_customers_table,
			$omnify_email_address
		), ARRAY_A );

		if ( $omnify_customer ) {
			$omnify_customer_data = [
				[ 'name' => __( 'Customer ID', 'omnifywp-ecommerce'), 'value' => $omnify_customer['id'] ],
				[ 'name' => __( 'First Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['first_name'] ],
				[ 'name' => __( 'Last Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['last_name'] ],
				[ 'name' => __( 'Email', 'omnifywp-ecommerce'), 'value' => $omnify_customer['email'] ],
				[ 'name' => __( 'Phone', 'omnifywp-ecommerce'), 'value' => $omnify_customer['phone'] ],
				[ 'name' => __( 'Company', 'omnifywp-ecommerce'), 'value' => $omnify_customer['company'] ],
				[ 'name' => __( 'Country', 'omnifywp-ecommerce'), 'value' => $omnify_customer['country'] ],
				[ 'name' => __( 'Status', 'omnifywp-ecommerce'), 'value' => $omnify_customer['status'] ],
				[ 'name' => __( 'Created At', 'omnifywp-ecommerce'), 'value' => $omnify_customer['created_at'] ],
			];

			if ( ! empty( $omnify_customer['shipping_first_name'] ) || ! empty( $omnify_customer['shipping_address_1'] ) ) {
				$omnify_customer_data[] = [ 'name' => __( 'Shipping First Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_first_name'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Last Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_last_name'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Phone', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_phone'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Company', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_company'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Address 1', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_address_1'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Address 2', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_address_2'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping City', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_city'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping State', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_state'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Postcode', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_postcode'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Shipping Country', 'omnifywp-ecommerce'), 'value' => $omnify_customer['shipping_country'] ];
			}

			if ( ! empty( $omnify_customer['billing_first_name'] ) || ! empty( $omnify_customer['billing_address_1'] ) ) {
				$omnify_customer_data[] = [ 'name' => __( 'Billing First Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_first_name'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Last Name', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_last_name'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Phone', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_phone'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Company', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_company'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Address 1', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_address_1'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Address 2', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_address_2'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing City', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_city'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing State', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_state'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Postcode', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_postcode'] ];
				$omnify_customer_data[] = [ 'name' => __( 'Billing Country', 'omnifywp-ecommerce'), 'value' => $omnify_customer['billing_country'] ];
			}

			$omnify_data_to_export[] = [
				'group_id'    => 'omnify-customer-profile',
				'group_label' => __( 'Omnify Customer Profile', 'omnifywp-ecommerce'),
				'item_id'     => 'customer-' . $omnify_customer['id'],
				'data'        => $omnify_customer_data,
			];

			// 2. Export Orders linked to Customer ID
			$omnify_orders = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb,  $wpdb->prepare(
				'SELECT * FROM %i WHERE customer_id = %d ORDER BY created_at DESC',
				$omnify_orders_table,
				(int) $omnify_customer['id']
			), ARRAY_A );

			foreach ( $omnify_orders as $omnify_order ) {
				$omnify_order_data = [
					[ 'name' => __( 'Order ID', 'omnifywp-ecommerce'), 'value' => $omnify_order['id'] ],
					[ 'name' => __( 'Order Number', 'omnifywp-ecommerce'), 'value' => $omnify_order['order_number'] ?: $omnify_order['id'] ],
					[ 'name' => __( 'Date', 'omnifywp-ecommerce'), 'value' => $omnify_order['created_at'] ],
					[ 'name' => __( 'Status', 'omnifywp-ecommerce'), 'value' => $omnify_order['status'] ],
					[ 'name' => __( 'Total', 'omnifywp-ecommerce'), 'value' => $omnify_order['currency'] . ' ' . number_format( (float) $omnify_order['total'], 2 ) ],
					[ 'name' => __( 'Subtotal', 'omnifywp-ecommerce'), 'value' => $omnify_order['currency'] . ' ' . number_format( (float) $omnify_order['subtotal'], 2 ) ],
					[ 'name' => __( 'Tax', 'omnifywp-ecommerce'), 'value' => $omnify_order['currency'] . ' ' . number_format( (float) $omnify_order['tax'], 2 ) ],
					[ 'name' => __( 'Payment Method', 'omnifywp-ecommerce'), 'value' => $omnify_order['payment_method'] ],
				];

				if ( ! empty( $omnify_order['shipping_first_name'] ) || ! empty( $omnify_order['shipping_address_1'] ) ) {
					$omnify_shipping_addr = sprintf(
						"%s %s\n%s %s\n%s, %s %s\n%s\nPhone: %s",
						$omnify_order['shipping_first_name'] ?? '',
						$omnify_order['shipping_last_name'] ?? '',
						$omnify_order['shipping_address_1'] ?? '',
						$omnify_order['shipping_address_2'] ?? '',
						$omnify_order['shipping_city'] ?? '',
						$omnify_order['shipping_state'] ?? '',
						$omnify_order['shipping_postcode'] ?? '',
						$omnify_order['shipping_country'] ?? '',
						$omnify_order['shipping_phone'] ?? ''
					);
					$omnify_order_data[] = [ 'name' => __( 'Shipping Address', 'omnifywp-ecommerce'), 'value' => $omnify_shipping_addr ];
				}

				if ( ! empty( $omnify_order['billing_first_name'] ) || ! empty( $omnify_order['billing_address_1'] ) ) {
					$omnify_billing_addr = sprintf(
						"%s %s\n%s %s\n%s, %s %s\n%s\nPhone: %s",
						$omnify_order['billing_first_name'] ?? '',
						$omnify_order['billing_last_name'] ?? '',
						$omnify_order['billing_address_1'] ?? '',
						$omnify_order['billing_address_2'] ?? '',
						$omnify_order['billing_city'] ?? '',
						$omnify_order['billing_state'] ?? '',
						$omnify_order['billing_postcode'] ?? '',
						$omnify_order['billing_country'] ?? '',
						$omnify_order['billing_phone'] ?? ''
					);
					$omnify_order_data[] = [ 'name' => __( 'Billing Address', 'omnifywp-ecommerce'), 'value' => $omnify_billing_addr ];
				}

				$omnify_data_to_export[] = [
					'group_id'    => 'omnify-customer-orders',
					'group_label' => __( 'Omnify Customer Orders', 'omnifywp-ecommerce'),
					'item_id'     => 'order-' . $omnify_order['id'],
					'data'        => $omnify_order_data,
				];
			}
		}

		// 3. Export Reviews matching email
		$omnify_reviews = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb,  $wpdb->prepare(
			"SELECT r.*, p.name as product_name FROM %i r 
			 LEFT JOIN %i p ON r.product_id = p.id
			 WHERE r.customer_email = %s 
			 ORDER BY r.created_at DESC",
			$omnify_reviews_table,
			$omnify_products_table,
			$omnify_email_address
		), ARRAY_A );

		foreach ( $omnify_reviews as $omnify_review ) {
			$omnify_data_to_export[] = [
				'group_id'    => 'omnify-customer-reviews',
				'group_label' => __( 'Omnify Customer Reviews', 'omnifywp-ecommerce'),
				'item_id'     => 'review-' . $omnify_review['id'],
				'data'        => [
					// translators: %d: placeholder value.
					[ 'name' => __( 'Product', 'omnifywp-ecommerce'), 'value' => $omnify_review['product_name'] ?: sprintf( __( 'Product #%d', 'omnifywp-ecommerce'), $omnify_review['product_id'] ) ],
					[ 'name' => __( 'Rating', 'omnifywp-ecommerce'), 'value' => $omnify_review['rating'] ],
					[ 'name' => __( 'Title', 'omnifywp-ecommerce'), 'value' => $omnify_review['review_title'] ],
					[ 'name' => __( 'Content', 'omnifywp-ecommerce'), 'value' => $omnify_review['review_content'] ],
					[ 'name' => __( 'Status', 'omnifywp-ecommerce'), 'value' => $omnify_review['status'] ],
					[ 'name' => __( 'Created At', 'omnifywp-ecommerce'), 'value' => $omnify_review['created_at'] ],
				],
			];
		}

		return [
			'data' => $omnify_data_to_export,
			'done' => true,
		];
	}

	/**
	 * Erase/Anonymize personal data associated with an email address.
	 *
	 * @param string $email_address The email address to erase data for.
	 * @param int    $page          The page parameter for pagination.
	 * @return array Structured erasure response.
	 */
	public function erase_personal_data( string $omnify_email_address, int $omnify_page = 1 ): array {
		global $wpdb;
		$omnify_email_address = sanitize_email( $omnify_email_address );

		$omnify_customers_table = $this->omnify_schema->table( 'customers' );
		$omnify_orders_table    = $this->omnify_schema->table( 'orders' );
		$omnify_reviews_table   = $this->omnify_schema->table( 'reviews' );
		$omnify_abandoned_table = $this->omnify_schema->table( 'abandoned_carts' );

		$omnify_customer = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb,  $wpdb->prepare(
			'SELECT * FROM %i WHERE email = %s',
			$omnify_customers_table,
			$omnify_email_address
		), ARRAY_A );

		$omnify_items_removed  = false;
		$omnify_items_retained = false;
		$omnify_messages       = [];

		if ( $omnify_customer ) {
			$omnify_customer_id = (int) $omnify_customer['id'];

			// Delete timeline notes
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb,  $this->omnify_schema->table( 'customer_notes' ), [ 'customer_id' => $omnify_customer_id ] );

			// Delete timeline activities
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb,  $this->omnify_schema->table( 'customer_activity' ), [ 'customer_id' => $omnify_customer_id ] );

			// Delete product access grants
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb,  $this->omnify_schema->table( 'customer_access' ), [ 'customer_id' => $omnify_customer_id ] );

			// Anonymize/Redact Orders
			$omnify_orders = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb,  $wpdb->prepare(
				'SELECT id FROM %i WHERE customer_id = %d',
				$omnify_orders_table,
				$omnify_customer_id
			) );

			if ( ! empty( $omnify_orders ) ) {
				$omnify_anonymized_data = [
					'customer_id'         => null,
					'shipping_first_name' => __( 'Anonymized', 'omnifywp-ecommerce'),
					'shipping_last_name'  => __( 'Customer', 'omnifywp-ecommerce'),
					'shipping_phone'      => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_address_1'  => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_address_2'  => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_city'       => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_state'      => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_postcode'   => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'shipping_country'    => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_first_name'  => __( 'Anonymized', 'omnifywp-ecommerce'),
					'billing_last_name'   => __( 'Customer', 'omnifywp-ecommerce'),
					'billing_phone'       => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_address_1'   => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_address_2'   => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_city'        => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_state'       => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_postcode'    => __( '[REDACTED]', 'omnifywp-ecommerce'),
					'billing_country'     => __( '[REDACTED]', 'omnifywp-ecommerce'),
				];

				foreach ( $omnify_orders as $omnify_order_id ) {
					\Omnify\eCommerce\Support\Omnify_DB::update($wpdb,  $omnify_orders_table, $omnify_anonymized_data, [ 'id' => $omnify_order_id ] );
				}
				// translators: %d: placeholder value.
				$omnify_messages[]     = sprintf( __( 'Anonymized personal details for %d orders.', 'omnifywp-ecommerce'), count( $omnify_orders ) );
				$omnify_items_removed = true;
			}

			// Delete Customer profile
			\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb,  $omnify_customers_table, [ 'id' => $omnify_customer_id ] );
			$omnify_messages[]     = __( 'Deleted customer profile, notes, activity logs, and product access keys.', 'omnifywp-ecommerce');
			$omnify_items_removed = true;
		}

		// Anonymize Product Reviews
		$omnify_reviews = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb,  $wpdb->prepare(
			'SELECT id FROM %i WHERE customer_email = %s',
			$omnify_reviews_table,
			$omnify_email_address
		) );

		if ( ! empty( $omnify_reviews ) ) {
			foreach ( $omnify_reviews as $omnify_review_id ) {
				\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
					$omnify_reviews_table,
					[
						'customer_name'  => __( 'Anonymous', 'omnifywp-ecommerce'),
						'customer_email' => 'deleted@example.com',
					],
					[ 'id' => $omnify_review_id ]
				);
			}
			// translators: %d: placeholder value.
			$omnify_messages[]     = sprintf( __( 'Anonymized %d reviews.', 'omnifywp-ecommerce'), count( $omnify_reviews ) );
			$omnify_items_removed = true;
		}

		// Delete Abandoned Carts
		$omnify_deleted_carts = \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb,  $omnify_abandoned_table, [ 'email' => $omnify_email_address ] );
		if ( $omnify_deleted_carts ) {
			// translators: %d: placeholder value.
			$omnify_messages[]     = sprintf( __( 'Deleted %d abandoned carts.', 'omnifywp-ecommerce'), (int) $omnify_deleted_carts );
			$omnify_items_removed = true;
		}

		if ( ! $omnify_items_removed ) {
			$omnify_messages[] = __( 'No personal data found for this email address.', 'omnifywp-ecommerce');
		}

		return [
			'items_removed'  => $omnify_items_removed,
			'items_retained' => $omnify_items_retained,
			'messages'       => $omnify_messages,
			'done'           => true,
		];
	}
}
