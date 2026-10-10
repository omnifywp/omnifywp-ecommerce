<?php
/**
 * Plugin settings.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Omnify_Settings_Repository {
	private const OPTION = 'omnify_settings';

	/**
	 * @return array<string, mixed>
	 */
	public function all(): array {
		$omnify_settings = get_option(self::OPTION, []);

		$omnify_parsed = wp_parse_args(
			is_array($omnify_settings) ? $omnify_settings : [],
			$this->defaults()
		);

		// Dynamic cleanup of legacy default disabled 'custom' payment method
		if (isset($omnify_parsed['payment_methods']) && is_array($omnify_parsed['payment_methods'])) {
			foreach ($omnify_parsed['payment_methods'] as $omnify_k => $omnify_method) {
				if (isset($omnify_method['id']) && 'custom' === $omnify_method['id'] && empty($omnify_method['enabled'])) {
					unset($omnify_parsed['payment_methods'][$omnify_k]);
				}
			}
			$omnify_parsed['payment_methods'] = array_values($omnify_parsed['payment_methods']);
		}

		return apply_filters('omnify_settings_all', $omnify_parsed);
	}

	/**
	 * @param array<string, mixed> $settings
	 */
	public function update(array $omnify_settings): bool {
		$omnify_settings = apply_filters('omnify_pre_update_settings', $omnify_settings);
		$omnify_sanitized_settings = $this->sanitize($omnify_settings);
		$omnify_updated            = update_option(self::OPTION, $omnify_sanitized_settings, false);

		if ($omnify_updated) {
			wp_cache_delete(self::OPTION, 'options');
			return true;
		}

		$omnify_stored_settings = get_option(self::OPTION, []);

		return is_array($omnify_stored_settings) && $omnify_stored_settings == $omnify_sanitized_settings;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function defaults(): array {
		$omnify_defaults = [
			'store_name'                  => get_bloginfo('name'),
			'store_email'                 => get_option('admin_email'),
			'store_url'                   => home_url(),
			'store_phone'                 => '',
			'store_address'               => '',
			'business_tax_id'             => '',
			'default_currency'            => 'USD',
			'default_country'             => 'US',
			'selling_locations'           => 'all',
			'selling_countries'           => [],
			'admin_access_capability'     => 'manage_options',
			'role_permissions'            => [
				'dashboard'       => ['administrator', 'shop_manager', 'shop_clerk'],
				'products'        => ['administrator', 'shop_manager'],
				'orders'          => ['administrator', 'shop_manager', 'shop_clerk'],
				'customers'       => ['administrator', 'shop_manager'],
				'coupons'         => ['administrator', 'shop_manager'],
				'abandoned_carts' => ['administrator', 'shop_manager'],
				'reviews'         => ['administrator', 'shop_manager', 'shop_clerk'],
				'analytics'       => ['administrator', 'shop_manager'],
				'settings'        => ['administrator'],
				'tools'           => ['administrator'],
			],
			'currency_position'           => 'before',
			'price_decimals'              => 2,
			'thousand_separator'          => ',',
			'decimal_separator'           => '.',
			'test_mode'                   => true,
			'tax_rate'                    => 0.0,
			'tax_label'                   => 'Tax',
			'prices_include_tax'          => false,
			'tax_shipping'                => false,
			'tax_rounding'                => 'line',
			'tax_reporting_enabled'       => true,
			'tax_rules'                   => [],
			'delivery_zones'              => [],
			'stripe_enabled'              => false,
			'stripe_mode'                 => 'test',
			'stripe_test_publishable_key' => '',
			'stripe_test_secret_key'      => '',
			'stripe_live_publishable_key' => '',
			'stripe_live_secret_key'      => '',
			'stripe_webhook_secret'       => '',
			'stripe_checkout_enabled'     => true,
			'stripe_payment_intents_enabled' => true,
			'stripe_apple_pay_enabled'    => true,
			'stripe_google_pay_enabled'   => true,
			'paypal_enabled'              => false,
			'paypal_mode'                 => 'sandbox',
			'paypal_sandbox_client_id'    => '',
			'paypal_sandbox_secret'       => '',
			'paypal_live_client_id'       => '',
			'paypal_live_secret'          => '',
			'paypal_webhook_id'           => '',
			'paypal_checkout_enabled'     => true,
			'razorpay_enabled'            => false,
			'razorpay_mode'               => 'test',
			'razorpay_test_key_id'        => '',
			'razorpay_test_key_secret'    => '',
			'razorpay_live_key_id'        => '',
			'razorpay_live_key_secret'    => '',
			'razorpay_webhook_secret'     => '',
			'razorpay_checkout_enabled'   => true,
			// Alipay
			'alipay_enabled'              => false,
			'alipay_mode'                 => 'sandbox',
			'alipay_app_id'               => '',
			'alipay_merchant_private_key' => '',
			'alipay_alipay_public_key'    => '',
			'alipay_checkout_enabled'     => true,
			// WeChat Pay
			'wechat_enabled'              => false,
			'wechat_mode'                 => 'sandbox',
			'wechat_appid'                => '',
			'wechat_mchid'                => '',
			'wechat_key'                  => '',
			'wechat_checkout_enabled'     => true,
			// SSLCommerz
			'sslcommerz_enabled'          => false,
			'sslcommerz_mode'             => 'sandbox',
			'sslcommerz_store_id'         => '',
			'sslcommerz_store_password'   => '',
			'sslcommerz_checkout_enabled' => true,
			// Paystack (Africa)
			'paystack_enabled'            => false,
			'paystack_mode'               => 'test',
			'paystack_test_public_key'    => '',
			'paystack_test_secret_key'    => '',
			'paystack_live_public_key'    => '',
			'paystack_live_secret_key'    => '',
			'paystack_webhook_secret'     => '',
			'paystack_checkout_enabled'   => true,
			// Tap Payments (Middle East)
			'tap_enabled'                 => false,
			'tap_mode'                    => 'test',
			'tap_test_publishable_key'    => '',
			'tap_test_secret_key'         => '',
			'tap_live_publishable_key'    => '',
			'tap_live_secret_key'         => '',
			'tap_checkout_enabled'        => true,
			// Mollie (European Union)
			'mollie_enabled'              => false,
			'mollie_mode'                 => 'test',
			'mollie_test_api_key'         => '',
			'mollie_live_api_key'         => '',
			'mollie_checkout_enabled'     => true,
			// Khalti (Nepal)
			'khalti_enabled'              => false,
			'khalti_mode'                 => 'sandbox',
			'khalti_test_public_key'      => '',
			'khalti_test_secret_key'      => '',
			'khalti_live_public_key'      => '',
			'khalti_live_secret_key'      => '',
			'khalti_checkout_enabled'     => true,
			// eSewa (Nepal)
			'esewa_enabled'               => false,
			'esewa_mode'                  => 'sandbox',
			'esewa_test_product_code'     => 'EPAYTEST',
			'esewa_test_secret_key'       => '8gBm/:&EnhH.1/q',
			'esewa_live_product_code'     => '',
			'esewa_live_secret_key'       => '',
			'esewa_checkout_enabled'      => true,
			'tracking_ga4_enabled'        => false,
			'tracking_ga4_measurement_id' => '',
			'tracking_meta_enabled'       => false,
			'tracking_meta_pixel_id'      => '',
			'tracking_debug_mode'         => false,
			'mini_cart_menu_enabled'      => false,
			'mini_cart_menu_location'     => '',
			'fraud_country_mismatch_flag' => true,
			'fraud_disposable_email_flag' => true,
			'fraud_max_value'             => 500.00,
			'fraud_max_attempts_limit'    => 3,
			'payment_methods'             => [
				[
					'id'           => 'bank_transfer',
					'name'         => 'Bank Transfer',
					'enabled'      => false,
					'instructions' => "Please transfer the total amount to the following bank account:\n\nBank: Example Bank\nAccount Name: Your Store Name\nAccount Number: 0000-0000\nRouting/Sort Code: 00-00-00\n\nInclude your Order ID as the payment reference.",
				],
				[
					'id'           => 'cheque',
					'name'         => 'Cheque Payment',
					'enabled'      => false,
					'instructions' => "Please make your cheque payable to \"Your Store Name\" and mail it to:\n\nYour Store Name\n123 Main Street\nYour City, State, ZIP\n\nInclude your Order ID on the memo line.",
				],
				[
					'id'           => 'cash_on_delivery',
					'name'         => 'Cash on Delivery',
					'enabled'      => false,
					'instructions' => 'Please have the exact amount ready when your order is delivered.',
				],
			],
			// Checkout
				'allow_guest_checkout'        => true,
				'require_account_on_checkout' => false,
				'account_creation_mode'       => 'optional',
				'auto_login_created_accounts' => true,
				'checkout_require_phone'      => false,
				'default_payment_method'      => 'card',
				'order_number_prefix'         => '',
				'low_stock_threshold'         => 5,
				'reduce_stock_on_checkout'    => true,
				'enable_coupons'              => true,
				'abandoned_cart_enabled'      => true,
				'abandoned_cart_delay_minutes' => 60,
				'abandoned_cart_expire_days'  => 14,
				'abandoned_cart_max_reminders' => 1,
			'require_terms'               => false,
			'terms_url'                   => '',
			'download_link_expiry'        => 7,
			'download_limit'              => 0,
			// Email
			'email_from_name'             => get_bloginfo('name'),
			'email_from_address'          => get_option('admin_email'),
			'email_receipt'               => true,
			'email_payment_pending'       => true,
			'email_download_links'        => true,
			'email_admin_new_order'       => true,
			'admin_notification_email'    => get_option('admin_email'),
			'email_footer_text'           => '',
			'push_notifications_enabled'  => true,
			'email_refund_requested'      => true,
			'email_refund_processed'      => true,
			'email_status_changed'        => true,
			'email_order_completed'       => true,
			'email_order_shipped'         => true,
			'email_order_processing'      => true,
			'email_order_cancelled'       => true,
			'email_abandoned_cart'        => true,
			'email_low_stock'             => true,
			'email_receipt_subject'       => '[{site_name}] Order Receipt {order_id}',
			'email_receipt_intro'         => 'Thank you for your purchase! Your order has been completed successfully.',
			'email_payment_pending_subject' => '[{site_name}] Order {order_id} — Awaiting Payment',
			'email_payment_pending_intro' => 'Thank you for your order! It is awaiting payment.',
			'email_abandoned_cart_subject' => '[{site_name}] Complete your order',
			'email_abandoned_cart_intro'  => 'You left something in your cart. Use the button below to return to checkout and complete your order.',
			'email_low_stock_subject'     => '[{site_name}] Low stock alert: {product_name}',
			'email_refund_requested_subject' => '[{site_name}] Refund requested for Order {order_id}',
			'email_refund_requested_body' => "Dear Customer,\n\nYour refund request for Order {order_id} has been received and is currently under review.\n\nReason: {refund_reason}\n\nWe will update you shortly.",
			'email_refund_processed_subject' => '[{site_name}] Refund processed for Order {order_id}',
			'email_refund_processed_body' => "Dear Customer,\n\nYour refund of {refund_amount} for Order {order_id} has been processed successfully.\n\nThank you.",
			'email_status_changed_subject' => '[{site_name}] Order {order_id} status updated',
			'email_status_changed_body'   => 'Your order status changed from {old_status} to {new_status}.',
			'email_order_completed_subject' => '[{site_name}] Order {order_id} Completed',
			'email_order_completed_body'   => 'Great news! Your order {order_id} has been completed. Thank you for your purchase.',
			'email_order_shipped_subject' => '[{site_name}] Order {order_id} Has Shipped',
			'email_order_shipped_body'   => 'Your order {order_id} has been shipped. Track it in your portal.',
			'email_order_processing_subject' => '[{site_name}] Order {order_id} is Processing',
			'email_order_processing_body'   => 'We are now processing your order {order_id}.',
			'email_order_cancelled_subject' => '[{site_name}] Order {order_id} Cancelled',
			'email_order_cancelled_body'   => 'Your order {order_id} has been cancelled.',
			'email_notifications_enabled' => true,
			'email_reply_to'              => '',
			'email_bcc_admin'             => false,
			'email_send_receipt_copy'     => true,
			'email_status_include_details'=> true,
			// Custom SMTP
			'email_smtp_enabled'          => false,
			'email_smtp_host'             => '',
			'email_smtp_port'             => 587,
			'email_smtp_encryption'       => 'tls',
			'email_smtp_auth'             => true,
			'email_smtp_username'         => '',
			'email_smtp_password'         => '',
			'enable_refunds'              => false,
			'refund_duration'             => 14,
			// Pages
			'page_storefront'             => 0,
			'page_cart'                   => 0,
			'page_checkout'               => 0,
			'page_customer_portal'        => 0,
			'page_download_page'          => 0,
			'page_download_hub'           => 0,
			'page_order_tracking'         => 0,
			// Design Settings
			'design_primary_color'        => '#6366f1',
			'design_hover_color'          => '#4f46e5',
			'design_border_radius'        => 8,
			'design_font_family'          => "'Inter', system-ui, -apple-system, sans-serif",
			'design_grid_columns'         => 3,
			'design_show_reviews'         => true,
			// Filters Settings
			'storefront_show_filters'     => true,
			'storefront_show_search'      => true,
			'storefront_show_sort'        => true,
			'storefront_filter_position'  => 'top',
			// Scarcity and Shipping Experience
			'cart_countdown_enabled'      => true,
			'cart_countdown_duration'     => 7,
			'cart_free_shipping_enabled'  => true,
			'cart_free_shipping_threshold'=> 200,
			'storefront_catalog_layout'   => 'grid',
			'storefront_products_per_page' => 12,
			'storefront_grid_rows'        => 0,
			'storefront_grid_columns'     => 3,
			'storefront_filters'          => ['category', 'type'],
			// Additional Storefront Experience options
			'storefront_show_wishlist'         => true,
			'storefront_default_sort'          => 'latest',
			'storefront_show_sku'              => false,
			'storefront_show_breadcrumbs'      => true,
			'storefront_pagination_type'       => 'classic',
			'storefront_show_discount_badge'   => true,
			'storefront_show_reassurance'      => true,
			'storefront_show_compare'          => true,
			'storefront_show_recently_viewed'  => true,
			'storefront_recently_viewed_count' => 5,
			'storefront_infinite_scroll'       => true,
			'storefront_reassurance_1_title'   => 'Secure Checkout',
			'storefront_reassurance_1_desc'    => 'Your data is protected',
			'storefront_reassurance_2_title'   => 'Instant Download',
			'storefront_reassurance_2_desc'    => 'Get access immediately',
			'storefront_reassurance_3_title'   => '24/7 Support',
			'storefront_reassurance_3_desc'    => "We're here to help",
			'storefront_show_hero'             => true,
			'storefront_hero_title'            => 'Storefront',
			'storefront_hero_desc'             => 'High quality products to build, grow and succeed.',
			'storefront_hero_image'            => '',
			'storefront_hero_wave_color'       => '#e11d48',
			'storefront_show_features_bar'     => true,
			'storefront_feature_1_show'        => true,
			'storefront_feature_1_icon'        => 'dollar',
			'storefront_feature_1_title'       => 'High Quality',
			'storefront_feature_1_desc'        => 'Premium products',
			'storefront_feature_2_show'        => true,
			'storefront_feature_2_icon'        => 'lock',
			'storefront_feature_2_title'       => 'Secure Checkout',
			'storefront_feature_2_desc'        => 'Safe & trusted',
			'storefront_feature_3_show'        => true,
			'storefront_feature_3_icon'        => 'lightning',
			'storefront_feature_3_title'       => 'Instant Access',
			'storefront_feature_3_desc'        => 'Download instantly',
			'storefront_feature_4_show'        => true,
			'storefront_feature_4_icon'        => 'star',
			'storefront_feature_4_title'       => 'Top Rated',
			'storefront_feature_4_desc'        => 'Loved by customers',
		];

		return apply_filters('omnify_settings_defaults', $omnify_defaults);
	}

	/**
	 * @param array<string, mixed> $settings
	 * @return array<string, mixed>
	 */
	public function sanitize(array $omnify_settings): array {
		$omnify_defaults = $this->defaults();

		// payment_methods may arrive as indexed array (from storage) or keyed by id (from POST)
		$omnify_raw_methods   = $omnify_settings['payment_methods'] ?? $omnify_defaults['payment_methods'];
		$omnify_methods_by_id = [];
		foreach ($omnify_raw_methods as $omnify_m) {
			if (isset($omnify_m['id'])) {
				$omnify_methods_by_id[$omnify_m['id']] = $omnify_m;
			}
		}
		$omnify_methods = [];
		$processed_ids = [];
		foreach ($omnify_defaults['payment_methods'] as $omnify_default_method) {
			$omnify_id        = $omnify_default_method['id'];
			$omnify_raw       = $omnify_methods_by_id[$omnify_id] ?? $omnify_default_method;
			$omnify_methods[] = [
				'id'           => $omnify_id,
				'name'         => sanitize_text_field((string) ($omnify_raw['name'] ?? $omnify_default_method['name'])),
				'enabled'      => ! empty($omnify_raw['enabled']),
				'instructions' => sanitize_textarea_field((string) ($omnify_raw['instructions'] ?? $omnify_default_method['instructions'])),
			];
			$processed_ids[] = $omnify_id;
		}
		foreach ($omnify_methods_by_id as $omnify_id => $omnify_raw) {
			if (in_array($omnify_id, $processed_ids, true)) {
				continue;
			}
			$omnify_id = sanitize_key($omnify_id);
			if (empty($omnify_id)) {
				continue;
			}
			$omnify_methods[] = [
				'id'           => $omnify_id,
				'name'         => sanitize_text_field((string) ($omnify_raw['name'] ?? 'Manual Payment')),
				'enabled'      => ! empty($omnify_raw['enabled']),
				'instructions' => sanitize_textarea_field((string) ($omnify_raw['instructions'] ?? '')),
			];
		}

		return [
			// General
			'store_name'                  => sanitize_text_field((string) ($omnify_settings['store_name'] ?? '')),
			'store_email'                 => sanitize_email((string) ($omnify_settings['store_email'] ?? '')),
			'store_url'                   => esc_url_raw((string) ($omnify_settings['store_url'] ?? '')),
			'store_phone'                 => sanitize_text_field((string) ($omnify_settings['store_phone'] ?? '')),
			'store_address'               => sanitize_textarea_field((string) ($omnify_settings['store_address'] ?? '')),
			'business_tax_id'             => sanitize_text_field((string) ($omnify_settings['business_tax_id'] ?? '')),
			'default_currency'            => strtoupper(substr(sanitize_text_field((string) ($omnify_settings['default_currency'] ?? 'USD')), 0, 3)),
			'default_country'             => strtoupper(substr(sanitize_text_field((string) ($omnify_settings['default_country'] ?? 'US')), 0, 2)),
			'selling_locations'           => in_array($omnify_settings['selling_locations'] ?? '', ['all', 'specific'], true) ? $omnify_settings['selling_locations'] : 'all',
			'selling_countries'           => is_array($omnify_settings['selling_countries'] ?? null) ? array_map('sanitize_text_field', $omnify_settings['selling_countries']) : [],
			'admin_access_capability'     => in_array($omnify_settings['admin_access_capability'] ?? '', ['manage_options', 'manage_omnify'], true) ? $omnify_settings['admin_access_capability'] : 'manage_options',
			'role_permissions'            => $this->sanitize_role_permissions($omnify_settings['role_permissions'] ?? []),
			'price_decimals'              => max(0, min(4, absint($omnify_settings['price_decimals'] ?? 2))),
			'thousand_separator'          => sanitize_text_field((string) ($omnify_settings['thousand_separator'] ?? ',')),
			'decimal_separator'           => sanitize_text_field((string) ($omnify_settings['decimal_separator'] ?? '.')),
			'currency_position'           => in_array($omnify_settings['currency_position'] ?? '', ['before', 'after'], true) ? $omnify_settings['currency_position'] : 'before',
			'test_mode'                   => ! empty($omnify_settings['test_mode']),
			'tax_rate'                    => max(0.0, (float) ($omnify_settings['tax_rate'] ?? 0.0)),
			'tax_label'                   => sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax')),
			'prices_include_tax'          => ! empty($omnify_settings['prices_include_tax']),
			'tax_shipping'                => ! empty($omnify_settings['tax_shipping']),
			'tax_rounding'                => in_array($omnify_settings['tax_rounding'] ?? '', ['line', 'subtotal'], true) ? $omnify_settings['tax_rounding'] : 'line',
			'tax_reporting_enabled'       => ! empty($omnify_settings['tax_reporting_enabled']),
			'tax_rules'                   => $this->sanitize_tax_rules($omnify_settings['tax_rules'] ?? []),
			'delivery_zones'              => $this->sanitize_delivery_zones($omnify_settings['delivery_zones'] ?? []),
			'stripe_enabled'              => ! empty($omnify_settings['stripe_enabled']),
			'stripe_mode'                 => in_array($omnify_settings['stripe_mode'] ?? '', ['test', 'live'], true) ? $omnify_settings['stripe_mode'] : 'test',
			'stripe_test_publishable_key' => sanitize_text_field((string) ($omnify_settings['stripe_test_publishable_key'] ?? '')),
			'stripe_test_secret_key'      => sanitize_text_field((string) ($omnify_settings['stripe_test_secret_key'] ?? '')),
			'stripe_live_publishable_key' => sanitize_text_field((string) ($omnify_settings['stripe_live_publishable_key'] ?? '')),
			'stripe_live_secret_key'      => sanitize_text_field((string) ($omnify_settings['stripe_live_secret_key'] ?? '')),
			'stripe_webhook_secret'       => sanitize_text_field((string) ($omnify_settings['stripe_webhook_secret'] ?? '')),
			'stripe_checkout_enabled'     => ! empty($omnify_settings['stripe_checkout_enabled']),
			'stripe_payment_intents_enabled' => ! empty($omnify_settings['stripe_payment_intents_enabled']),
			'stripe_apple_pay_enabled'    => ! empty($omnify_settings['stripe_apple_pay_enabled']),
			'stripe_google_pay_enabled'   => ! empty($omnify_settings['stripe_google_pay_enabled']),
			'paypal_enabled'              => ! empty($omnify_settings['paypal_enabled']),
			'paypal_mode'                 => in_array($omnify_settings['paypal_mode'] ?? '', ['sandbox', 'live'], true) ? $omnify_settings['paypal_mode'] : 'sandbox',
			'paypal_sandbox_client_id'    => sanitize_text_field((string) ($omnify_settings['paypal_sandbox_client_id'] ?? '')),
			'paypal_sandbox_secret'       => sanitize_text_field((string) ($omnify_settings['paypal_sandbox_secret'] ?? '')),
			'paypal_live_client_id'       => sanitize_text_field((string) ($omnify_settings['paypal_live_client_id'] ?? '')),
			'paypal_live_secret'          => sanitize_text_field((string) ($omnify_settings['paypal_live_secret'] ?? '')),
			'paypal_webhook_id'           => sanitize_text_field((string) ($omnify_settings['paypal_webhook_id'] ?? '')),
			'paypal_checkout_enabled'     => ! empty($omnify_settings['paypal_checkout_enabled']),
			'razorpay_enabled'            => ! empty($omnify_settings['razorpay_enabled']),
			'razorpay_mode'               => in_array($omnify_settings['razorpay_mode'] ?? '', ['test', 'live'], true) ? $omnify_settings['razorpay_mode'] : 'test',
			'razorpay_test_key_id'        => sanitize_text_field((string) ($omnify_settings['razorpay_test_key_id'] ?? '')),
			'razorpay_test_key_secret'    => sanitize_text_field((string) ($omnify_settings['razorpay_test_key_secret'] ?? '')),
			'razorpay_live_key_id'        => sanitize_text_field((string) ($omnify_settings['razorpay_live_key_id'] ?? '')),
			'razorpay_live_key_secret'    => sanitize_text_field((string) ($omnify_settings['razorpay_live_key_secret'] ?? '')),
			'razorpay_webhook_secret'     => sanitize_text_field((string) ($omnify_settings['razorpay_webhook_secret'] ?? '')),
			'razorpay_checkout_enabled'   => ! empty($omnify_settings['razorpay_checkout_enabled']),
			// Alipay
			'alipay_enabled'              => ! empty($omnify_settings['alipay_enabled']),
			'alipay_mode'                 => in_array($omnify_settings['alipay_mode'] ?? '', ['sandbox', 'live'], true) ? $omnify_settings['alipay_mode'] : 'sandbox',
			'alipay_app_id'               => sanitize_text_field((string) ($omnify_settings['alipay_app_id'] ?? '')),
			'alipay_merchant_private_key' => sanitize_text_field((string) ($omnify_settings['alipay_merchant_private_key'] ?? '')),
			'alipay_alipay_public_key'    => sanitize_text_field((string) ($omnify_settings['alipay_alipay_public_key'] ?? '')),
			'alipay_checkout_enabled'     => ! empty($omnify_settings['alipay_checkout_enabled']),
			// WeChat Pay
			'wechat_enabled'              => ! empty($omnify_settings['wechat_enabled']),
			'wechat_mode'                 => in_array($omnify_settings['wechat_mode'] ?? '', ['sandbox', 'live'], true) ? $omnify_settings['wechat_mode'] : 'sandbox',
			'wechat_appid'                => sanitize_text_field((string) ($omnify_settings['wechat_appid'] ?? '')),
			'wechat_mchid'                => sanitize_text_field((string) ($omnify_settings['wechat_mchid'] ?? '')),
			'wechat_key'                  => sanitize_text_field((string) ($omnify_settings['wechat_key'] ?? '')),
			'wechat_checkout_enabled'     => ! empty($omnify_settings['wechat_checkout_enabled']),
			// SSLCommerz
			'sslcommerz_enabled'          => ! empty($omnify_settings['sslcommerz_enabled']),
			'sslcommerz_mode'             => in_array($omnify_settings['sslcommerz_mode'] ?? '', ['sandbox', 'live'], true) ? $omnify_settings['sslcommerz_mode'] : 'sandbox',
			'sslcommerz_store_id'         => sanitize_text_field((string) ($omnify_settings['sslcommerz_store_id'] ?? '')),
			'sslcommerz_store_password'   => sanitize_text_field((string) ($omnify_settings['sslcommerz_store_password'] ?? '')),
			'sslcommerz_checkout_enabled' => ! empty($omnify_settings['sslcommerz_checkout_enabled']),
			'tracking_ga4_enabled'        => ! empty($omnify_settings['tracking_ga4_enabled']),
			'tracking_ga4_measurement_id' => strtoupper(sanitize_text_field((string) ($omnify_settings['tracking_ga4_measurement_id'] ?? ''))),
			'tracking_meta_enabled'       => ! empty($omnify_settings['tracking_meta_enabled']),
			'tracking_meta_pixel_id'      => preg_replace('/[^0-9]/', '', (string) ($omnify_settings['tracking_meta_pixel_id'] ?? '')),
			'tracking_debug_mode'         => ! empty($omnify_settings['tracking_debug_mode']),
			'mini_cart_menu_enabled'      => ! empty($omnify_settings['mini_cart_menu_enabled']),
			'mini_cart_menu_location'     => sanitize_key((string) ($omnify_settings['mini_cart_menu_location'] ?? '')),
			'fraud_country_mismatch_flag' => ! empty($omnify_settings['fraud_country_mismatch_flag']),
			'fraud_disposable_email_flag' => ! empty($omnify_settings['fraud_disposable_email_flag']),
			'fraud_max_value'             => max(0.0, (float) ($omnify_settings['fraud_max_value'] ?? 500.00)),
			'fraud_max_attempts_limit'    => max(1, (int) ($omnify_settings['fraud_max_attempts_limit'] ?? 3)),
			// Payments
			'payment_methods'             => $omnify_methods,
			// Checkout
				'allow_guest_checkout'        => ! empty($omnify_settings['allow_guest_checkout']),
				'require_account_on_checkout' => ! empty($omnify_settings['require_account_on_checkout']),
				'account_creation_mode'       => in_array($omnify_settings['account_creation_mode'] ?? '', ['optional', 'automatic', 'required'], true) ? $omnify_settings['account_creation_mode'] : (! empty($omnify_settings['require_account_on_checkout']) ? 'required' : 'optional'),
				'auto_login_created_accounts' => ! empty($omnify_settings['auto_login_created_accounts']),
				'checkout_require_phone'      => ! empty($omnify_settings['checkout_require_phone']),
				'default_payment_method'      => sanitize_key((string) ($omnify_settings['default_payment_method'] ?? 'card')),
				'order_number_prefix'         => sanitize_text_field((string) ($omnify_settings['order_number_prefix'] ?? '')),
				'low_stock_threshold'         => max(0, absint($omnify_settings['low_stock_threshold'] ?? 5)),
				'reduce_stock_on_checkout'    => ! empty($omnify_settings['reduce_stock_on_checkout']),
				'enable_coupons'              => ! empty($omnify_settings['enable_coupons']),
				'abandoned_cart_enabled'      => ! empty($omnify_settings['abandoned_cart_enabled']),
				'abandoned_cart_delay_minutes' => max(5, absint($omnify_settings['abandoned_cart_delay_minutes'] ?? 60)),
				'abandoned_cart_expire_days'  => max(1, absint($omnify_settings['abandoned_cart_expire_days'] ?? 14)),
				'abandoned_cart_max_reminders' => max(1, absint($omnify_settings['abandoned_cart_max_reminders'] ?? 1)),
			'require_terms'               => ! empty($omnify_settings['require_terms']),
			'terms_url'                   => esc_url_raw((string) ($omnify_settings['terms_url'] ?? '')),
			'download_link_expiry'        => max(0, absint($omnify_settings['download_link_expiry'] ?? 7)),
			'download_limit'              => max(0, absint($omnify_settings['download_limit'] ?? 0)),
			// Email
			'email_from_name'             => sanitize_text_field((string) ($omnify_settings['email_from_name'] ?? '')),
			'email_from_address'          => sanitize_email((string) ($omnify_settings['email_from_address'] ?? '')),
			'email_receipt'               => ! empty($omnify_settings['email_receipt']),
			'email_payment_pending'       => ! empty($omnify_settings['email_payment_pending']),
			'email_download_links'        => ! empty($omnify_settings['email_download_links']),
			'email_admin_new_order'       => ! empty($omnify_settings['email_admin_new_order']),
			'admin_notification_email'    => sanitize_text_field((string) ($omnify_settings['admin_notification_email'] ?? '')),
			'email_footer_text'           => sanitize_textarea_field((string) ($omnify_settings['email_footer_text'] ?? '')),
			'push_notifications_enabled'  => ! empty($omnify_settings['push_notifications_enabled']),
			'email_refund_requested'      => ! empty($omnify_settings['email_refund_requested']),
			'email_refund_processed'      => ! empty($omnify_settings['email_refund_processed']),
			'email_status_changed'        => ! empty($omnify_settings['email_status_changed']),
			'email_order_completed'       => ! empty($omnify_settings['email_order_completed']),
			'email_order_shipped'         => ! empty($omnify_settings['email_order_shipped']),
			'email_order_processing'      => ! empty($omnify_settings['email_order_processing']),
			'email_order_cancelled'       => ! empty($omnify_settings['email_order_cancelled']),
			'email_abandoned_cart'        => ! empty($omnify_settings['email_abandoned_cart']),
			'email_low_stock'             => ! empty($omnify_settings['email_low_stock']),
			'email_receipt_subject'       => sanitize_text_field((string) ($omnify_settings['email_receipt_subject'] ?? $omnify_defaults['email_receipt_subject'])),
			'email_receipt_intro'         => sanitize_textarea_field((string) ($omnify_settings['email_receipt_intro'] ?? $omnify_defaults['email_receipt_intro'])),
			'email_payment_pending_subject' => sanitize_text_field((string) ($omnify_settings['email_payment_pending_subject'] ?? $omnify_defaults['email_payment_pending_subject'])),
			'email_payment_pending_intro' => sanitize_textarea_field((string) ($omnify_settings['email_payment_pending_intro'] ?? $omnify_defaults['email_payment_pending_intro'])),
			'email_abandoned_cart_subject' => sanitize_text_field((string) ($omnify_settings['email_abandoned_cart_subject'] ?? $omnify_defaults['email_abandoned_cart_subject'])),
			'email_abandoned_cart_intro'  => sanitize_textarea_field((string) ($omnify_settings['email_abandoned_cart_intro'] ?? $omnify_defaults['email_abandoned_cart_intro'])),
			'email_low_stock_subject'     => sanitize_text_field((string) ($omnify_settings['email_low_stock_subject'] ?? $omnify_defaults['email_low_stock_subject'])),
			'email_refund_requested_subject' => sanitize_text_field((string) ($omnify_settings['email_refund_requested_subject'] ?? $omnify_defaults['email_refund_requested_subject'])),
			'email_refund_requested_body' => sanitize_textarea_field((string) ($omnify_settings['email_refund_requested_body'] ?? $omnify_defaults['email_refund_requested_body'])),
			'email_refund_processed_subject' => sanitize_text_field((string) ($omnify_settings['email_refund_processed_subject'] ?? $omnify_defaults['email_refund_processed_subject'])),
			'email_refund_processed_body' => sanitize_textarea_field((string) ($omnify_settings['email_refund_processed_body'] ?? $omnify_defaults['email_refund_processed_body'])),
			'email_status_changed_subject' => sanitize_text_field((string) ($omnify_settings['email_status_changed_subject'] ?? $omnify_defaults['email_status_changed_subject'])),
			'email_status_changed_body'   => sanitize_textarea_field((string) ($omnify_settings['email_status_changed_body'] ?? $omnify_defaults['email_status_changed_body'])),
			'email_order_completed_subject' => sanitize_text_field((string) ($omnify_settings['email_order_completed_subject'] ?? $omnify_defaults['email_order_completed_subject'])),
			'email_order_completed_body'   => sanitize_textarea_field((string) ($omnify_settings['email_order_completed_body'] ?? $omnify_defaults['email_order_completed_body'])),
			'email_order_shipped_subject' => sanitize_text_field((string) ($omnify_settings['email_order_shipped_subject'] ?? $omnify_defaults['email_order_shipped_subject'])),
			'email_order_shipped_body'   => sanitize_textarea_field((string) ($omnify_settings['email_order_shipped_body'] ?? $omnify_defaults['email_order_shipped_body'])),
			'email_order_processing_subject' => sanitize_text_field((string) ($omnify_settings['email_order_processing_subject'] ?? $omnify_defaults['email_order_processing_subject'])),
			'email_order_processing_body'   => sanitize_textarea_field((string) ($omnify_settings['email_order_processing_body'] ?? $omnify_defaults['email_order_processing_body'])),
			'email_order_cancelled_subject' => sanitize_text_field((string) ($omnify_settings['email_order_cancelled_subject'] ?? $omnify_defaults['email_order_cancelled_subject'])),
			'email_order_cancelled_body'   => sanitize_textarea_field((string) ($omnify_settings['email_order_cancelled_body'] ?? $omnify_defaults['email_order_cancelled_body'])),
			'email_notifications_enabled' => ! array_key_exists('email_notifications_enabled', $omnify_settings) || ! empty($omnify_settings['email_notifications_enabled']),
			'email_reply_to'              => sanitize_email((string) ($omnify_settings['email_reply_to'] ?? '')),
			'email_bcc_admin'             => ! empty($omnify_settings['email_bcc_admin']),
			'email_send_receipt_copy'     => ! array_key_exists('email_send_receipt_copy', $omnify_settings) || ! empty($omnify_settings['email_send_receipt_copy']),
			'email_status_include_details'=> ! array_key_exists('email_status_include_details', $omnify_settings) || ! empty($omnify_settings['email_status_include_details']),
			// SMTP
			'email_smtp_enabled'          => ! empty($omnify_settings['email_smtp_enabled']),
			'email_smtp_host'             => sanitize_text_field((string) ($omnify_settings['email_smtp_host'] ?? '')),
			'email_smtp_port'             => max(1, min(65535, absint($omnify_settings['email_smtp_port'] ?? 587))),
			'email_smtp_encryption'       => in_array($omnify_settings['email_smtp_encryption'] ?? '', ['', 'ssl', 'tls'], true) ? $omnify_settings['email_smtp_encryption'] : 'tls',
			'email_smtp_auth'             => ! empty($omnify_settings['email_smtp_auth']),
			'email_smtp_username'         => sanitize_text_field((string) ($omnify_settings['email_smtp_username'] ?? '')),
			'email_smtp_password'         => (string) ($omnify_settings['email_smtp_password'] ?? ''), // passwords not sanitized aggressively
			'enable_refunds'              => ! empty($omnify_settings['enable_refunds']),
			'refund_duration'             => max(1, absint($omnify_settings['refund_duration'] ?? 14)),
			// Pages
			'page_storefront'             => absint($omnify_settings['page_storefront'] ?? 0),
			'page_cart'                   => absint($omnify_settings['page_cart'] ?? 0),
			'page_checkout'               => absint($omnify_settings['page_checkout'] ?? 0),
			'page_customer_portal'        => absint($omnify_settings['page_customer_portal'] ?? 0),
			'page_download_page'          => absint($omnify_settings['page_download_page'] ?? ($omnify_settings['page_download_hub'] ?? 0)),
			'page_download_hub'           => absint($omnify_settings['page_download_hub'] ?? 0),
			'page_order_tracking'         => absint($omnify_settings['page_order_tracking'] ?? 0),
			// Design Settings
			'design_primary_color'        => sanitize_hex_color($omnify_settings['design_primary_color'] ?? '#6366f1'),
			'design_hover_color'          => sanitize_hex_color($omnify_settings['design_hover_color'] ?? '#4f46e5'),
			'design_border_radius'        => max(0, absint($omnify_settings['design_border_radius'] ?? 8)),
			'design_font_family'          => sanitize_text_field((string) ($omnify_settings['design_font_family'] ?? "'Inter', system-ui, -apple-system, sans-serif")),
			'design_grid_columns'         => max(1, min(3, absint($omnify_settings['design_grid_columns'] ?? ($omnify_settings['storefront_grid_columns'] ?? 3)))),
			'design_show_reviews'         => ! empty($omnify_settings['design_show_reviews']),
			// Filters Settings
			'storefront_show_filters'     => ! array_key_exists('storefront_show_filters', $omnify_settings) || ! empty($omnify_settings['storefront_show_filters']),
			'storefront_show_search'      => ! array_key_exists('storefront_show_search', $omnify_settings) || ! empty($omnify_settings['storefront_show_search']),
			'storefront_show_sort'        => ! array_key_exists('storefront_show_sort', $omnify_settings) || ! empty($omnify_settings['storefront_show_sort']),
			'storefront_filter_position'  => in_array($omnify_settings['storefront_filter_position'] ?? '', ['top', 'left', 'right'], true) ? $omnify_settings['storefront_filter_position'] : 'top',
			'storefront_catalog_layout'   => in_array($omnify_settings['storefront_catalog_layout'] ?? '', ['grid', 'list'], true) ? $omnify_settings['storefront_catalog_layout'] : 'grid',
			'storefront_products_per_page' => max(1, min(100, absint($omnify_settings['storefront_products_per_page'] ?? 12))),
			'storefront_grid_rows'        => max(0, min(20, absint($omnify_settings['storefront_grid_rows'] ?? 0))),
			'storefront_grid_columns'     => max(1, min(3, absint($omnify_settings['storefront_grid_columns'] ?? ($omnify_settings['design_grid_columns'] ?? 3)))),
			'storefront_filters'          => is_array($omnify_settings['storefront_filters'] ?? null) ? array_map('sanitize_text_field', $omnify_settings['storefront_filters']) : ['category', 'type'],
			// Additional Storefront Experience options
			'storefront_show_wishlist'         => ! empty($omnify_settings['storefront_show_wishlist']),
			'storefront_default_sort'          => in_array($omnify_settings['storefront_default_sort'] ?? '', ['latest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'], true) ? $omnify_settings['storefront_default_sort'] : 'latest',
			'storefront_show_sku'              => ! empty($omnify_settings['storefront_show_sku']),
			'storefront_show_breadcrumbs'      => ! empty($omnify_settings['storefront_show_breadcrumbs']),
			'storefront_pagination_type'       => in_array($omnify_settings['storefront_pagination_type'] ?? '', ['classic', 'load_more'], true) ? $omnify_settings['storefront_pagination_type'] : 'classic',
			'storefront_card_style'            => in_array($omnify_settings['storefront_card_style'] ?? '', ['standard', 'compact', 'spacious'], true) ? $omnify_settings['storefront_card_style'] : 'standard',
			'storefront_show_add_to_cart'      => ! empty($omnify_settings['storefront_show_add_to_cart']),
			'storefront_related_count'         => max(0, min(12, absint($omnify_settings['storefront_related_count'] ?? 4))),
			'storefront_show_discount_badge'   => ! empty($omnify_settings['storefront_show_discount_badge']),
			'storefront_show_reassurance'      => ! empty($omnify_settings['storefront_show_reassurance']),
			'storefront_show_compare'          => ! empty($omnify_settings['storefront_show_compare']),
			'storefront_show_recently_viewed'  => ! empty($omnify_settings['storefront_show_recently_viewed']),
			'storefront_recently_viewed_count' => max(1, min(20, absint($omnify_settings['storefront_recently_viewed_count'] ?? 5))),
			'storefront_infinite_scroll'       => ! empty($omnify_settings['storefront_infinite_scroll']),
			'storefront_reassurance_1_title'   => sanitize_text_field($omnify_settings['storefront_reassurance_1_title'] ?? 'Secure Checkout'),
			'storefront_reassurance_1_desc'    => sanitize_text_field($omnify_settings['storefront_reassurance_1_desc'] ?? 'Your data is protected'),
			'storefront_reassurance_2_title'   => sanitize_text_field($omnify_settings['storefront_reassurance_2_title'] ?? 'Instant Download'),
			'storefront_reassurance_2_desc'    => sanitize_text_field($omnify_settings['storefront_reassurance_2_desc'] ?? 'Get access immediately'),
			'storefront_reassurance_3_title'   => sanitize_text_field($omnify_settings['storefront_reassurance_3_title'] ?? '24/7 Support'),
			'storefront_reassurance_3_desc'    => sanitize_text_field($omnify_settings['storefront_reassurance_3_desc'] ?? "We're here to help"),
			'storefront_show_hero'             => ! empty($omnify_settings['storefront_show_hero']),
			'storefront_hero_title'            => sanitize_text_field($omnify_settings['storefront_hero_title'] ?? 'Storefront'),
			'storefront_hero_desc'             => sanitize_text_field($omnify_settings['storefront_hero_desc'] ?? 'High quality products to build, grow and succeed.'),
			'storefront_hero_image'            => sanitize_url($omnify_settings['storefront_hero_image'] ?? ''),
			'storefront_hero_wave_color'       => sanitize_hex_color($omnify_settings['storefront_hero_wave_color'] ?? '#e11d48'),
			'storefront_show_features_bar'     => ! empty($omnify_settings['storefront_show_features_bar']),
			'storefront_feature_1_show'        => ! empty($omnify_settings['storefront_feature_1_show']),
			'storefront_feature_1_icon'        => sanitize_key($omnify_settings['storefront_feature_1_icon'] ?? 'dollar'),
			'storefront_feature_1_title'       => sanitize_text_field($omnify_settings['storefront_feature_1_title'] ?? 'High Quality'),
			'storefront_feature_1_desc'        => sanitize_text_field($omnify_settings['storefront_feature_1_desc'] ?? 'Premium products'),
			'storefront_feature_2_show'        => ! empty($omnify_settings['storefront_feature_2_show']),
			'storefront_feature_2_icon'        => sanitize_key($omnify_settings['storefront_feature_2_icon'] ?? 'lock'),
			'storefront_feature_2_title'       => sanitize_text_field($omnify_settings['storefront_feature_2_title'] ?? 'Secure Checkout'),
			'storefront_feature_2_desc'        => sanitize_text_field($omnify_settings['storefront_feature_2_desc'] ?? 'Safe & trusted'),
			'storefront_feature_3_show'        => ! empty($omnify_settings['storefront_feature_3_show']),
			'storefront_feature_3_icon'        => sanitize_key($omnify_settings['storefront_feature_3_icon'] ?? 'lightning'),
			'storefront_feature_3_title'       => sanitize_text_field($omnify_settings['storefront_feature_3_title'] ?? 'Instant Access'),
			'storefront_feature_3_desc'        => sanitize_text_field($omnify_settings['storefront_feature_3_desc'] ?? 'Download instantly'),
			'storefront_feature_4_show'        => ! empty($omnify_settings['storefront_feature_4_show']),
			'storefront_feature_4_icon'        => sanitize_key($omnify_settings['storefront_feature_4_icon'] ?? 'star'),
			'storefront_feature_4_title'       => sanitize_text_field($omnify_settings['storefront_feature_4_title'] ?? 'Top Rated'),
			'storefront_feature_4_desc'        => sanitize_text_field($omnify_settings['storefront_feature_4_desc'] ?? 'Loved by customers'),
			'cart_countdown_enabled'           => ! empty($omnify_settings['cart_countdown_enabled']),
			'cart_countdown_duration'          => max(1, min(1440, absint($omnify_settings['cart_countdown_duration'] ?? 7))),
			'cart_free_shipping_enabled'       => ! empty($omnify_settings['cart_free_shipping_enabled']),
			'cart_free_shipping_threshold'     => max(0.0, (float) ($omnify_settings['cart_free_shipping_threshold'] ?? 200.0)),
		];
	}

	public function option_name(): string {
		return self::OPTION;
	}

	/**
	 * @param mixed $rules
	 * @return array<int, array<string, mixed>>
	 */
	private function sanitize_tax_rules(mixed $omnify_rules): array {
		if (! is_array($omnify_rules)) {
			return [];
		}

		$omnify_clean = [];
		foreach ($omnify_rules as $omnify_rule) {
			if (! is_array($omnify_rule)) {
				continue;
			}

			$omnify_raw_label   = trim((string) ($omnify_rule['label'] ?? ''));
			$omnify_raw_country = trim((string) ($omnify_rule['country'] ?? ''));
			$omnify_raw_state   = trim((string) ($omnify_rule['state'] ?? ''));
			$omnify_raw_rate    = trim((string) ($omnify_rule['rate'] ?? ''));
			$omnify_raw_reporting_code = trim((string) ($omnify_rule['reporting_code'] ?? ''));
			$omnify_is_default_country = '' === $omnify_raw_country || '*' === $omnify_raw_country;
			$omnify_is_default_state   = '' === $omnify_raw_state || '*' === $omnify_raw_state;
			$omnify_is_default_label   = '' === $omnify_raw_label || in_array(strtolower($omnify_raw_label), ['tax', 'vat / gst'], true);
			$omnify_is_default_rate    = '' === $omnify_raw_rate || 0.0 === (float) $omnify_raw_rate;

			if (
				empty($omnify_rule['enabled'])
				&& $omnify_is_default_label
				&& $omnify_is_default_country
				&& $omnify_is_default_state
				&& $omnify_is_default_rate
				&& ('' === $omnify_raw_reporting_code || 'US-NY-SALES' === strtoupper($omnify_raw_reporting_code))
			) {
				continue;
			}

			$omnify_label   = sanitize_text_field('' !== $omnify_raw_label ? $omnify_raw_label : 'Tax');
			$omnify_country = strtoupper(sanitize_text_field('' !== $omnify_raw_country ? $omnify_raw_country : '*'));
			$omnify_state   = strtoupper(sanitize_text_field('' !== $omnify_raw_state ? $omnify_raw_state : '*'));
			$omnify_rate    = max(0.0, (float) ($omnify_rule['rate'] ?? 0.0));

			$omnify_clean[] = [
				'enabled'  => ! empty($omnify_rule['enabled']),
				'label'    => '' !== $omnify_label ? $omnify_label : 'Tax',
				'country'  => '' !== $omnify_country ? $omnify_country : '*',
				'state'    => '' !== $omnify_state ? $omnify_state : '*',
				'rate'     => min(100.0, $omnify_rate),
				'priority' => absint($omnify_rule['priority'] ?? 10),
				'reporting_code' => sanitize_text_field((string) ($omnify_rule['reporting_code'] ?? '')),
			];
		}

		return array_values($omnify_clean);
	}

	/**
	 * @param mixed $zones
	 * @return array<int, array<string, mixed>>
	 */
	private function sanitize_delivery_zones(mixed $omnify_zones): array {
		if (! is_array($omnify_zones)) {
			return [];
		}

		$omnify_clean = [];
		foreach ($omnify_zones as $omnify_zone) {
			if (! is_array($omnify_zone)) {
				continue;
			}

			$omnify_raw_name        = trim((string) ($omnify_zone['name'] ?? ''));
			$omnify_raw_countries   = trim((string) ($omnify_zone['countries'] ?? ''));
			$omnify_raw_states      = trim((string) ($omnify_zone['states'] ?? ''));
			$omnify_raw_method_name = trim((string) ($omnify_zone['method_name'] ?? ''));
			$omnify_raw_method_type = trim((string) ($omnify_zone['method_type'] ?? 'flat_rate'));
			$omnify_raw_cost        = trim((string) ($omnify_zone['cost'] ?? ''));
			$omnify_raw_free_min    = trim((string) ($omnify_zone['free_min'] ?? ''));
			$omnify_raw_class_costs = is_array($omnify_zone['class_costs'] ?? null) ? $omnify_zone['class_costs'] : trim((string) ($omnify_zone['class_costs'] ?? ''));
			$omnify_is_default_name = '' === $omnify_raw_name || 'Delivery Zone' === $omnify_raw_name;
			$omnify_is_default_countries = '' === $omnify_raw_countries || '*' === $omnify_raw_countries;
			$omnify_is_default_states = '' === $omnify_raw_states || '*' === $omnify_raw_states;
			$omnify_is_default_method = '' === $omnify_raw_method_name || 'Standard delivery' === $omnify_raw_method_name;
			$omnify_is_default_method_type = '' === $omnify_raw_method_type || 'flat_rate' === $omnify_raw_method_type;
			$omnify_is_default_cost = '' === $omnify_raw_cost || 0.0 === (float) $omnify_raw_cost;
			$omnify_is_default_free_min = '' === $omnify_raw_free_min || 0.0 === (float) $omnify_raw_free_min;

			if (
				empty($omnify_zone['enabled'])
				&& $omnify_is_default_name
				&& $omnify_is_default_countries
				&& $omnify_is_default_states
				&& $omnify_is_default_method
				&& $omnify_is_default_method_type
				&& $omnify_is_default_cost
				&& $omnify_is_default_free_min
				&& empty($omnify_raw_class_costs)
			) {
				continue;
			}

			$omnify_name        = sanitize_text_field('' !== $omnify_raw_name ? $omnify_raw_name : 'Delivery Zone');
			$omnify_countries   = strtoupper(sanitize_text_field('' !== $omnify_raw_countries ? $omnify_raw_countries : '*'));
			$omnify_states      = strtoupper(sanitize_text_field('' !== $omnify_raw_states ? $omnify_raw_states : '*'));
			$omnify_method_name = sanitize_text_field('' !== $omnify_raw_method_name ? $omnify_raw_method_name : 'Standard delivery');
			$omnify_method_type = in_array($omnify_raw_method_type, ['flat_rate', 'free_shipping', 'local_pickup'], true) ? $omnify_raw_method_type : 'flat_rate';
			$omnify_cost        = max(0.0, (float) ($omnify_zone['cost'] ?? 0.0));
			$omnify_free_min    = max(0.0, (float) ($omnify_zone['free_min'] ?? 0.0));
			$omnify_class_costs = [];
			if (is_array($omnify_raw_class_costs)) {
				foreach ($omnify_raw_class_costs as $omnify_class_name => $omnify_class_cost) {
					if ('' === (string) $omnify_class_name) {
						continue;
					}
					$omnify_class_costs[sanitize_title((string) $omnify_class_name)] = max(0.0, (float) $omnify_class_cost);
				}
			} else {
				foreach (preg_split('/\r\n|\r|\n/', $omnify_raw_class_costs) ?: [] as $omnify_line) {
					$omnify_parts = array_map('trim', explode(':', (string) $omnify_line, 2));
					if (2 !== count($omnify_parts) || '' === $omnify_parts[0]) {
						continue;
					}
					$omnify_class_costs[sanitize_title($omnify_parts[0])] = max(0.0, (float) $omnify_parts[1]);
				}
			}

			$omnify_clean[] = [
				'enabled'     => ! empty($omnify_zone['enabled']),
				'name'        => '' !== $omnify_name ? $omnify_name : 'Delivery Zone',
				'countries'   => '' !== $omnify_countries ? $omnify_countries : '*',
				'states'      => '' !== $omnify_states ? $omnify_states : '*',
				'method_name' => '' !== $omnify_method_name ? $omnify_method_name : 'Standard delivery',
				'method_type' => $omnify_method_type,
				'cost'        => $omnify_cost,
				'free_min'    => $omnify_free_min,
				'class_costs' => $omnify_class_costs,
				'priority'    => absint($omnify_zone['priority'] ?? 10),
			];
		}

		return array_values($omnify_clean);
	}

	/**
	 * @param mixed $omnify_perms
	 * @return array<string, array<int, string>>
	 */
	private function sanitize_role_permissions(mixed $omnify_perms): array {
		$omnify_clean = [];
		$omnify_features = ['dashboard', 'products', 'orders', 'customers', 'coupons', 'abandoned_carts', 'reviews', 'analytics', 'settings', 'tools'];
		$omnify_roles = ['administrator', 'shop_manager', 'shop_clerk'];

		foreach ($omnify_features as $omnify_feat) {
			$omnify_clean[$omnify_feat] = ['administrator']; // Administrator always has access
			if (isset($omnify_perms[$omnify_feat]) && is_array($omnify_perms[$omnify_feat])) {
				foreach ($omnify_perms[$omnify_feat] as $omnify_role) {
					$omnify_role = sanitize_key((string) $omnify_role);
					if (in_array($omnify_role, $omnify_roles, true) && ! in_array($omnify_role, $omnify_clean[$omnify_feat], true)) {
						$omnify_clean[$omnify_feat][] = $omnify_role;
					}
				}
			}
		}

		return $omnify_clean;
	}
}
