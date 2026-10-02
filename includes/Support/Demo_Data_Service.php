<?php
/**
 * Demo data generator and cleanup service.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Admin_Activity_Repository;
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Demo_Data_Service {
	public function __construct(
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_product_files,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Review_Repository $omnify_reviews,
		private Omnify_Admin_Activity_Repository $omnify_activities,
		private Omnify_Schema $omnify_schema,
		private Omnify_Customer_Access_Repository $omnify_access
	) {}

	private function get_table_name(string $name): string {
		global $wpdb;
		return $wpdb->prefix . 'omnify_' . $name;
	}

	/**
	 * Generate diverse demo data.
	 *
	 * @return array{products: int, customers: int, orders: int, reviews: int}
	 */
	public function generate_demo_data(): array {
		global $wpdb;

		// 1. Create Demo Customers
		$customer_ids = [];
		$customer_data = [
			[
				'email'               => 'john.doe@example.com',
				'first_name'          => 'John',
				'last_name'           => 'Doe',
				'phone'               => '+15550199',
				'company'             => 'Doe Enterprises',
				'country'             => 'US',
				'shipping_first_name' => 'John',
				'shipping_last_name'  => 'Doe',
				'shipping_address_1'  => '123 Main St',
				'shipping_city'       => 'New York',
				'shipping_state'      => 'NY',
				'shipping_postcode'   => '10001',
				'shipping_country'    => 'US',
				'billing_first_name'  => 'John',
				'billing_last_name'   => 'Doe',
				'billing_address_1'   => '123 Main St',
				'billing_city'        => 'New York',
				'billing_state'       => 'NY',
				'billing_postcode'    => '10001',
				'billing_country'     => 'US',
			],
			[
				'email'               => 'jane.smith@example.net',
				'first_name'          => 'Jane',
				'last_name'           => 'Smith',
				'phone'               => '+442079460192',
				'company'             => '',
				'country'             => 'GB',
				'shipping_first_name' => 'Jane',
				'shipping_last_name'  => 'Smith',
				'shipping_address_1'  => '10 Downing St',
				'shipping_city'       => 'London',
				'shipping_postcode'   => 'SW1A 2AA',
				'shipping_country'    => 'GB',
				'billing_first_name'  => 'Jane',
				'billing_last_name'   => 'Smith',
				'billing_address_1'   => '10 Downing St',
				'billing_city'        => 'London',
				'billing_postcode'    => 'SW1A 2AA',
				'billing_country'     => 'GB',
			],
			[
				'email'               => 'marc.dupont@example.fr',
				'first_name'          => 'Marc',
				'last_name'           => 'Dupont',
				'phone'               => '+33142277889',
				'company'             => 'La Boutique',
				'country'             => 'FR',
				'shipping_first_name' => 'Marc',
				'shipping_last_name'  => 'Dupont',
				'shipping_address_1'  => '55 Rue du Faubourg Saint-Honoré',
				'shipping_city'       => 'Paris',
				'shipping_postcode'   => '75008',
				'shipping_country'    => 'FR',
				'billing_first_name'  => 'Marc',
				'billing_last_name'   => 'Dupont',
				'billing_address_1'   => '55 Rue du Faubourg Saint-Honoré',
				'billing_city'        => 'Paris',
				'billing_postcode'    => '75008',
				'billing_country'     => 'FR',
			]
		];

		foreach ($customer_data as $c_data) {
			$customer_ids[] = $this->omnify_customers->create($c_data);
		}

		// 2. Create Demo Products
		$product_ids = [];

		// A. Simple Physical Product
		$phys_product_data = [
			'status'       => 'publish',
			'type'         => 'physical',
			'name'         => 'Premium Leather Wallet',
			'description'  => '<p>Crafted from premium full-grain leather, this minimalist wallet offers durability and style. Features multiple card slots and a dedicated bill compartment.</p>',
			'short_description' => 'Sleek full-grain leather wallet for modern everyday carry.',
			'price'        => 49.00,
			'sku'          => 'WAL-LTH-01',
			'manage_stock' => 1,
			'stock_qty'    => 150,
			'weight'       => 0.15,
			'length'       => 11.5,
			'width'        => 8.5,
			'height'       => 1.5,
			'categories'   => ['Accessories', 'Leather Goods'],
			'tags'         => ['Minimalist', 'Wallet', 'Premium'],
			'brands'       => ['Omnify Heritage']
		];
		$product_ids['physical'] = $this->omnify_products->create($phys_product_data);

		// B. Simple Digital Product
		$dig_product_data = [
			'status'       => 'publish',
			'type'         => 'download',
			'name'         => 'Mastering WordPress & PHP E-Book',
			'description'  => '<p>The ultimate guide to building high-performance WordPress plugins and scaling PHP applications. Includes real-world examples and code snippets.</p>',
			'short_description' => 'Comprehensive digital e-book guide for WordPress plugin developers.',
			'price'        => 29.00,
			'sku'          => 'DIG-BK-WP01',
			'manage_stock' => 0,
			'download_expiry_days' => 365,
			'categories'   => ['Education', 'Digital Goods'],
			'tags'         => ['E-Book', 'WordPress', 'PHP', 'Development'],
			'brands'       => ['Omnify Academy']
		];
		$dig_id = $this->omnify_products->create($dig_product_data);
		$product_ids['digital'] = $dig_id;

		// Mock file attachment for digital product
		$omnify_upload_info = wp_upload_dir();
		$omnify_uploads_dir = $omnify_upload_info['basedir'] . '/omnifywp-ecommerce';
		$omnify_uploads_url = $omnify_upload_info['baseurl'] . '/omnifywp-ecommerce';

		$this->omnify_product_files->create_from_upload(
			$dig_id,
			[
				'name' => 'mastering-wp-and-php-guide.pdf',
				'file' => $omnify_uploads_dir . '/mastering-wp-and-php-guide.pdf',
				'url'  => $omnify_uploads_url . '/mastering-wp-and-php-guide.pdf',
				'type' => 'application/pdf',
			],
			'1.0'
		);

		// C. Variable Product
		$var_product_data = [
			'status'       => 'publish',
			'type'         => 'variable',
			'name'         => 'Classic Cotton T-Shirt',
			'description'  => '<p>Made from 100% organic cotton, this t-shirt provides all-day comfort. Available in multiple colors and sizes.</p>',
			'short_description' => 'Comfy organic cotton everyday casual t-shirt.',
			'price'        => 25.00,
			'sku'          => 'TSH-CL-01',
			'manage_stock' => 0,
			'categories'   => ['Apparel', 'Shirts'],
			'tags'         => ['Cotton', 'T-Shirt', 'Organic'],
			'brands'       => ['Omnify Basics'],
			'attributes'   => [
				[
					'name' => 'Size',
					'options' => ['S', 'M', 'L'],
					'is_variation' => 1
				],
				[
					'name' => 'Color',
					'options' => ['Blue', 'Black'],
					'is_variation' => 1
				]
			],
			'variation_settings' => [
				'attribute_keys' => ['Size', 'Color']
			]
		];
		$var_id = $this->omnify_products->create($var_product_data);
		$product_ids['variable'] = $var_id;

		// Save variations
		$variations = [
			[
				'sku' => 'TSH-CL-S-BLU',
				'price' => 25.00,
				'sale_price' => '',
				'manage_stock' => 1,
				'stock_qty' => 50,
				'stock_status' => 'instock',
				'attributes' => [
					'Size' => 'S',
					'Color' => 'Blue'
				]
			],
			[
				'sku' => 'TSH-CL-M-BLU',
				'price' => 25.00,
				'sale_price' => '',
				'manage_stock' => 1,
				'stock_qty' => 45,
				'stock_status' => 'instock',
				'attributes' => [
					'Size' => 'M',
					'Color' => 'Blue'
				]
			],
			[
				'sku' => 'TSH-CL-L-BLK',
				'price' => 27.00, // Large is slightly more expensive
				'sale_price' => 22.00, // On sale!
				'manage_stock' => 1,
				'stock_qty' => 12,
				'stock_status' => 'instock',
				'attributes' => [
					'Size' => 'L',
					'Color' => 'Black'
				]
			]
		];
		$this->omnify_products->save_variations($var_id, $variations);

		// D. Product Bundle
		$bundle_product_data = [
			'status'       => 'publish',
			'type'         => 'bundle',
			'name'         => 'Modern Developer Travel Kit',
			'description'  => '<p>An exclusive combination of our Premium Leather Wallet and the Mastering WordPress E-Book at a special bundled price. Perfect for developers on the go.</p>',
			'short_description' => 'Get our premium wallet and developer e-book together and save!',
			'price'        => 65.00, // Special discount price
			'sku'          => 'BND-DEV-01',
			'manage_stock' => 0,
			'categories'   => ['Bundles', 'Special Packs'],
			'tags'         => ['Bundle', 'Deal', 'Travel'],
			'bundled_ids'  => [$product_ids['physical'], $product_ids['digital']]
		];
		$product_ids['bundle'] = $this->omnify_products->create($bundle_product_data);

		// 3. Generate Orders
		$order_count = 0;
		$order_templates = [
			[
				'customer_id' => $customer_ids[0],
				'status'      => 'completed',
				'currency'    => 'USD',
				'payment_method' => 'Stripe Credit Card',
				'transaction_id' => 'ch_demo_123456',
				'items' => [
					[
						'product_id'   => $product_ids['physical'],
						'product_name' => 'Premium Leather Wallet',
						'price'        => 49.00,
						'tax'          => 3.92,
						'quantity'     => 1,
					]
				]
			],
			[
				'customer_id' => $customer_ids[1],
				'status'      => 'processing',
				'currency'    => 'USD',
				'payment_method' => 'PayPal',
				'transaction_id' => 'PAY-DEMO-998877',
				'items' => [
					[
						'product_id'   => $product_ids['digital'],
						'product_name' => 'Mastering WordPress & PHP E-Book',
						'price'        => 29.00,
						'tax'          => 0.00,
						'quantity'     => 2,
					]
				]
			],
			[
				'customer_id' => $customer_ids[2],
				'status'      => 'pending',
				'currency'    => 'USD',
				'payment_method' => 'Bank Transfer',
				'items' => [
					[
						'product_id'   => $product_ids['bundle'],
						'product_name' => 'Modern Developer Travel Kit',
						'price'         => 65.00,
						'tax'          => 5.20,
						'quantity'     => 1,
					]
				]
			],
			[
				'customer_id' => $customer_ids[0],
				'status'      => 'refunded',
				'currency'    => 'USD',
				'payment_method' => 'Stripe Credit Card',
				'transaction_id' => 'ch_demo_777888',
				'items' => [
					[
						'product_id'   => $product_ids['physical'],
						'product_name' => 'Premium Leather Wallet',
						'price'        => 49.00,
						'tax'          => 3.92,
						'quantity'     => 1,
					]
				]
			]
		];

		foreach ($order_templates as $o_tpl) {
			$this->omnify_orders->create($o_tpl);
			$order_count++;

			// Generate customer access rules for completed/processing digital items
			if (in_array($o_tpl['status'], ['completed', 'processing'], true)) {
				foreach ($o_tpl['items'] as $item) {
					$prod = $this->omnify_products->find((int) $item['product_id']);
					if ($prod) {
						$variation_settings = wp_parse_args($prod['variation_settings'] ?? [], ['product_kind' => 'digital']);
						$is_physical = ('physical' === ($prod['type'] ?? 'download'))
							|| ('variable' === ($prod['type'] ?? 'download') && 'physical' === ($variation_settings['product_kind'] ?? 'digital'));
						if (! $is_physical) {
							$this->omnify_access->grant((int) $o_tpl['customer_id'], (int) $item['product_id']);
						}
					}
				}
			}
		}

		// 4. Generate Reviews
		$review_count = 0;
		$reviews_data = [
			[
				'product_id'     => $product_ids['physical'],
				'customer_name'  => 'John Doe',
				'customer_email' => 'john.doe@example.com',
				'rating'         => 5,
				'review_title'   => 'Stunning quality!',
				'review_content' => 'Absolutely love this wallet. The leather feels incredibly premium, and it fits easily in my front pocket. Highly recommend!',
				'status'         => 'approved',
			],
			[
				'product_id'     => $product_ids['physical'],
				'customer_name'  => 'Jane Smith',
				'customer_email' => 'jane.smith@example.net',
				'rating'         => 4,
				'review_title'   => 'Great wallet, compact',
				'review_content' => 'The stitching is neat and it holds all my essential cards. Leather is a bit stiff initially but breaks in nicely after a few days.',
				'status'         => 'approved',
			],
			[
				'product_id'     => $product_ids['digital'],
				'customer_name'  => 'Alex Coder',
				'customer_email' => 'alex.coder@example.org',
				'rating'         => 5,
				'review_title'   => 'Best guide on WordPress plugins!',
				'review_content' => 'Every single chapter is filled with practical developer insights. The section on schema optimization alone was worth the purchase.',
				'status'         => 'approved',
			]
		];

		foreach ($reviews_data as $r_data) {
			$this->omnify_reviews->create($r_data);
			$review_count++;
		}

		// Log admin activity
		$current_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$current_user_id,
			'generate_demo_data',
			'system',
			'demo',
			sprintf(
				/* translators: 1: products count, 2: customers count, 3: orders count */
				__('Generated Omnify Demo Data: %1$d products, %2$d customers, %3$d orders.', 'omnifywp-ecommerce'),
				count($product_ids),
				count($customer_ids),
				$order_count
			)
		);

		return [
			'products'  => count($product_ids),
			'customers' => count($customer_ids),
			'orders'    => $order_count,
			'reviews'   => $review_count,
		];
	}

	/**
	 * Remove specific or all types of data.
	 *
	 * @param string $type The data category to remove: 'all', 'products', 'orders', 'customers', 'reviews'
	 * @return bool
	 */
	public function remove_data(string $type): bool {
		global $wpdb;

		$types_to_clean = [];
		if ('all' === $type) {
			$types_to_clean = ['products', 'orders', 'customers', 'reviews'];
		} else {
			$types_to_clean = [$type];
		}

		$current_user_id = get_current_user_id();

		foreach ($types_to_clean as $t) {
			switch ($t) {
				case 'products':
					// Delete products, variations, categories, tags, brands, files, inventory logs, metadata, customer access
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('products')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('product_categories')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('product_tags')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('product_brands')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('product_variations')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('product_files')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('inventory_logs')));
					$wpdb->query($wpdb->prepare(
						'DELETE FROM %i WHERE object_type IN (%s, %s)',
						$this->get_table_name('metadata'),
						'product',
						'variation'
					));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('customer_access')));
					
					$this->omnify_activities->log($current_user_id, 'clear_data_products', 'system', 'products', __('Cleared all products and linked variation/inventory/file data.', 'omnifywp-ecommerce'));
					break;

				case 'orders':
					// Delete orders, items, notes, abandoned carts
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('orders')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('order_items')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('order_notes')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('abandoned_carts')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('customer_access')));

					$this->omnify_activities->log($current_user_id, 'clear_data_orders', 'system', 'orders', __('Cleared all orders, items, notes, and abandoned carts.', 'omnifywp-ecommerce'));
					break;

				case 'customers':
					// Delete customers, wishlists, and null out customer_ids in orders
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('customers')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('wishlists')));
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('customer_access')));
					$wpdb->query($wpdb->prepare('UPDATE %i SET customer_id = NULL', $this->get_table_name('orders')));

					$this->omnify_activities->log($current_user_id, 'clear_data_customers', 'system', 'customers', __('Cleared all customers and customer access records.', 'omnifywp-ecommerce'));
					break;

				case 'reviews':
					// Delete reviews
					$wpdb->query($wpdb->prepare('TRUNCATE TABLE %i', $this->get_table_name('reviews')));

					$this->omnify_activities->log($current_user_id, 'clear_data_reviews', 'system', 'reviews', __('Cleared all product reviews.', 'omnifywp-ecommerce'));
					break;
			}
		}

		return true;
	}
}
