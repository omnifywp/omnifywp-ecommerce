<?php
/**
 * Plugin Name: OmnifyWP eCommerce
 * Description: Complete eCommerce engine for simple, variable, bundled, physical, and digital products on WordPress.
 * Version: 1.4.2
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Author: OmnifyWP
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: omnifywp-ecommerce
 * Domain Path: /languages
 *
 * @package Omnify
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('OMNIFY_VERSION', '1.4.2');
define('OMNIFY_FILE', __FILE__);
define('OMNIFY_PATH', plugin_dir_path(__FILE__));
define('OMNIFY_URL', plugin_dir_url(__FILE__));

require_once OMNIFY_PATH . 'includes/Support/Autoloader.php';

\Omnify\eCommerce\Support\Omnify_Autoloader::register();

register_activation_hook(__FILE__, static function (): void {
	\Omnify\eCommerce\Omnify_Plugin::activate();
});

register_deactivation_hook(__FILE__, static function (): void {
	\Omnify\eCommerce\Omnify_Plugin::deactivate();
});

add_action('plugins_loaded', static function (): void {
	\Omnify\eCommerce\Omnify_Plugin::instance()->boot();
});

/**
 * Locate a template file in the theme or fallback to the plugin.
 *
 * @param string $template_name Relative template path.
 * @return string Full path to the template file.
 */
function omnify_locate_template(string $omnify_template_name): string {
	$omnify_located = locate_template('omnify/' . $omnify_template_name);
	if (! $omnify_located) {
		$omnify_located = OMNIFY_PATH . 'templates/' . $omnify_template_name;
	}
	return apply_filters('omnify_locate_template', $omnify_located, $omnify_template_name);
}

/**
 * Main plugin instance accessor.
 *
 * @return \Omnify\eCommerce\Omnify_Plugin
 */
function omnify_plugin() {
	return \Omnify\eCommerce\Omnify_Plugin::instance();
}

/**
 * Parse a comma-separated list into a trimmed array of non-empty string values.
 *
 * @param mixed $omnify_value
 * @return array<int, string>
 */
function omnify_coupon_csv_values(mixed $omnify_value): array {
	return array_values(array_filter(array_map('trim', explode(',', (string) $omnify_value)), static function ($omnify_item) {
		return '' !== $omnify_item;
	}));
}

/**
 * Render an HTML trend badge for dashboard and analytics metrics.
 *
 * @param float|int|string $omnify_trend
 * @return string
 */
function omnify_render_trend(float|int|string $omnify_trend): string {
	$omnify_trend_val  = (float) $omnify_trend;
	$omnify_trend_sign = $omnify_trend_val > 0 ? '+' : '';
	$omnify_class      = $omnify_trend_val > 0 ? 'omnify-trend--up' : ($omnify_trend_val < 0 ? 'omnify-trend--down' : 'omnify-trend--neutral');
	$omnify_bg         = $omnify_trend_val > 0 ? '#ecfdf5' : ($omnify_trend_val < 0 ? '#fef2f2' : '#f1f5f9');
	$omnify_fg         = $omnify_trend_val > 0 ? '#059669' : ($omnify_trend_val < 0 ? '#dc2626' : '#64748b');
	$omnify_border     = $omnify_trend_val > 0 ? '#a7f3d0' : ($omnify_trend_val < 0 ? '#fecaca' : '#e2e8f0');
	return '<span class="omnify-trend ' . $omnify_class . '" style="border: 1px solid ' . $omnify_border . '; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 9999px; background: ' . $omnify_bg . '; color: ' . $omnify_fg . '; display: inline-flex; align-items: center; gap: 2px;">' . $omnify_trend_sign . number_format($omnify_trend_val, 1) . '%</span>';
}

/**
 * Dynamically resolve the storefront Cart URL.
 *
 * Checks in priority order:
 * 1. Filter: 'omnify_cart_url'
 * 2. Settings: 'page_cart' or 'cart_page_id'
 * 3. Published page containing '[omnify_cart]' shortcode
 * 4. Published page with slug 'cart' or 'shopping-cart'
 * 5. Fallback: WooCommerce cart if available, or site home_url('/cart/')
 *
 * @return string
 */
function omnify_get_cart_url(): string {
	$omnify_settings = get_option('omnify_settings', []);
	$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];

	// 1. Check settings page_cart or cart_page_id
	$omnify_page_id = (int) ($omnify_settings['page_cart'] ?? $omnify_settings['cart_page_id'] ?? 0);
	if ($omnify_page_id > 0) {
		$omnify_permalink = get_permalink($omnify_page_id);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_cart_url', $omnify_permalink);
		}
	}

	// 2. Search for published page with [omnify_cart]
	$omnify_pages = get_posts([
		'post_type'        => 'page',
		's'                => '[omnify_cart]',
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'suppress_filters' => false,
	]);
	if (! empty($omnify_pages) && isset($omnify_pages[0]->ID)) {
		$omnify_permalink = get_permalink($omnify_pages[0]->ID);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_cart_url', $omnify_permalink);
		}
	}

	// 3. Search for published page with slug 'cart' or 'shopping-cart'
	foreach (['cart', 'shopping-cart'] as $omnify_slug) {
		$omnify_page = get_page_by_path($omnify_slug);
		if ($omnify_page instanceof \WP_Post && 'publish' === $omnify_page->post_status) {
			$omnify_permalink = get_permalink($omnify_page->ID);
			if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
				return (string) apply_filters('omnify_cart_url', $omnify_permalink);
			}
		}
	}

	// 4. WooCommerce fallback if WooCommerce is active
	if (function_exists('wc_get_cart_url')) {
		$omnify_wc_url = wc_get_cart_url();
		if (! empty($omnify_wc_url)) {
			return (string) apply_filters('omnify_cart_url', $omnify_wc_url);
		}
	}

	// 5. Default fallback to home_url('/cart/')
	return (string) apply_filters('omnify_cart_url', home_url('/cart/'));
}

/**
 * Dynamically resolve the storefront Checkout URL.
 *
 * @return string
 */
function omnify_get_checkout_url(): string {
	$omnify_settings = get_option('omnify_settings', []);
	$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];

	$omnify_page_id = (int) ($omnify_settings['page_checkout'] ?? $omnify_settings['checkout_page_id'] ?? 0);
	if ($omnify_page_id > 0) {
		$omnify_permalink = get_permalink($omnify_page_id);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_checkout_url', $omnify_permalink);
		}
	}

	$omnify_pages = get_posts([
		'post_type'        => 'page',
		's'                => '[omnify_checkout]',
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'suppress_filters' => false,
	]);
	if (! empty($omnify_pages) && isset($omnify_pages[0]->ID)) {
		$omnify_permalink = get_permalink($omnify_pages[0]->ID);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_checkout_url', $omnify_permalink);
		}
	}

	$omnify_page = get_page_by_path('checkout');
	if ($omnify_page instanceof \WP_Post && 'publish' === $omnify_page->post_status) {
		$omnify_permalink = get_permalink($omnify_page->ID);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_checkout_url', $omnify_permalink);
		}
	}

	if (function_exists('wc_get_checkout_url')) {
		$omnify_wc_url = wc_get_checkout_url();
		if (! empty($omnify_wc_url)) {
			return (string) apply_filters('omnify_checkout_url', $omnify_wc_url);
		}
	}

	return (string) apply_filters('omnify_checkout_url', home_url('/checkout/'));
}

/**
 * Dynamically resolve the storefront Shop URL.
 *
 * @return string
 */
function omnify_get_storefront_url(): string {
	$omnify_settings = get_option('omnify_settings', []);
	$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];

	$omnify_page_id = (int) ($omnify_settings['page_storefront'] ?? $omnify_settings['storefront_page_id'] ?? 0);
	if ($omnify_page_id > 0) {
		$omnify_permalink = get_permalink($omnify_page_id);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_storefront_url', $omnify_permalink);
		}
	}

	$omnify_pages = get_posts([
		'post_type'        => 'page',
		's'                => '[omnify_storefront]',
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'suppress_filters' => false,
	]);
	if (! empty($omnify_pages) && isset($omnify_pages[0]->ID)) {
		$omnify_permalink = get_permalink($omnify_pages[0]->ID);
		if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
			return (string) apply_filters('omnify_storefront_url', $omnify_permalink);
		}
	}

	foreach (['storefront', 'shop', 'store'] as $omnify_slug) {
		$omnify_page = get_page_by_path($omnify_slug);
		if ($omnify_page instanceof \WP_Post && 'publish' === $omnify_page->post_status) {
			$omnify_permalink = get_permalink($omnify_page->ID);
			if ($omnify_permalink && ! is_wp_error($omnify_permalink)) {
				return (string) apply_filters('omnify_storefront_url', $omnify_permalink);
			}
		}
	}

	return (string) apply_filters('omnify_storefront_url', home_url('/storefront/'));
}

/**
 * Retrieve a product by ID.
 *
 * @param int $product_id Product ID.
 * @return array<string, mixed>|null Product data array or null if not found.
 */
function omnify_get_product(int $product_id): ?array {
	if ($product_id <= 0) {
		return null;
	}
	$schema = new \Omnify\eCommerce\Database\Omnify_Schema();
	$repo   = new \Omnify\eCommerce\Repositories\Omnify_Product_Repository($schema);
	$product = $repo->find($product_id);
	return is_array($product) ? apply_filters('omnify_get_product', $product, $product_id) : null;
}

/**
 * Retrieve an order by ID.
 *
 * @param int $order_id Order ID.
 * @return array<string, mixed>|null Order data array or null if not found.
 */
function omnify_get_order(int $order_id): ?array {
	if ($order_id <= 0) {
		return null;
	}
	$schema = new \Omnify\eCommerce\Database\Omnify_Schema();
	$repo   = new \Omnify\eCommerce\Repositories\Omnify_Order_Repository($schema);
	$order  = $repo->find($order_id);
	return is_array($order) ? apply_filters('omnify_get_order', $order, $order_id) : null;
}

/**
 * Format a numeric amount according to store currency and design settings.
 *
 * @param float  $amount   Numeric price amount.
 * @param string $currency Optional currency code. If empty, store default is used.
 * @return string Formatted price string.
 */
function omnify_format_price(float $amount, string $currency = ''): string {
	$settings = get_option('omnify_settings', []);
	$settings = is_array($settings) ? $settings : [];

	$currency = ! empty($currency) ? strtoupper($currency) : strtoupper((string) ($settings['default_currency'] ?? 'USD'));
	$symbol   = class_exists('\Omnify\eCommerce\Support\Omnify_Locations') ? \Omnify\eCommerce\Support\Omnify_Locations::currency_symbol($currency) : '$';
	$decimals = max(0, min(4, (int) ($settings['price_decimals'] ?? 2)));
	$thou     = (string) ($settings['thousand_separator'] ?? ',');
	$dec      = (string) ($settings['decimal_separator'] ?? '.');
	$price    = number_format($amount, $decimals, $dec, $thou);

	$formatted = 'after' === ($settings['currency_position'] ?? 'before')
		? $price . ' ' . $symbol
		: $symbol . $price;

	return (string) apply_filters('omnify_format_price', $formatted, $amount, $settings);
}

/**
 * Dynamically resolve the Customer Portal URL.
 *
 * @return string
 */
function omnify_get_customer_portal_url(): string {
	$settings = get_option('omnify_settings', []);
	$settings = is_array($settings) ? $settings : [];

	$page_id = (int) ($settings['page_customer_portal'] ?? $settings['customer_portal_page_id'] ?? 0);
	if ($page_id > 0) {
		$permalink = get_permalink($page_id);
		if ($permalink && ! is_wp_error($permalink)) {
			return (string) apply_filters('omnify_customer_portal_url', $permalink);
		}
	}

	$pages = get_posts([
		'post_type'        => 'page',
		's'                => '[omnify_portal]',
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'suppress_filters' => false,
	]);
	if (! empty($pages) && isset($pages[0]->ID)) {
		$permalink = get_permalink($pages[0]->ID);
		if ($permalink && ! is_wp_error($permalink)) {
			return (string) apply_filters('omnify_customer_portal_url', $permalink);
		}
	}

	foreach (['portal', 'customer-portal', 'my-account'] as $slug) {
		$page = get_page_by_path($slug);
		if ($page instanceof \WP_Post && 'publish' === $page->post_status) {
			$permalink = get_permalink($page->ID);
			if ($permalink && ! is_wp_error($permalink)) {
				return (string) apply_filters('omnify_customer_portal_url', $permalink);
			}
		}
	}

	return (string) apply_filters('omnify_customer_portal_url', home_url('/portal/'));
}



