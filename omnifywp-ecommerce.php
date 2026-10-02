<?php
/**
 * Plugin Name: OmnifyWP eCommerce
 * Description: Complete eCommerce engine for simple, variable, bundled, physical, and digital products on WordPress.
 * Version: 1.2.0
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

define('OMNIFY_VERSION', '1.2.0');
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


