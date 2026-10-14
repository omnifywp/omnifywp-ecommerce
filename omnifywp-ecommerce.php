<?php
/**
 * Plugin Name: OmnifyWP eCommerce
 * Description: Complete eCommerce engine for simple, variable, bundled, physical, and digital products on WordPress.
 * Version: 1.4.0
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

define('OMNIFY_VERSION', '1.4.0');
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
	$omnify_trend_sign = $omnify_trend_val >= 0 ? '+' : '';
	$omnify_bg         = $omnify_trend_val >= 0 ? '#d1fae5' : '#fee2e2';
	$omnify_fg         = $omnify_trend_val >= 0 ? '#065f46' : '#991b1b';
	return '<span class="omnify-trend" style="font-size: 11px; font-weight: 500; padding: 2px 6px; border-radius: 4px; background: ' . $omnify_bg . '; color: ' . $omnify_fg . '; display: inline-block; margin-left: 8px;">' . $omnify_trend_sign . number_format($omnify_trend_val, 1) . '%</span>';
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


