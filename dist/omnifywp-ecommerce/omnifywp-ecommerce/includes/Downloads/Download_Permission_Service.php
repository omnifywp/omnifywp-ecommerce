<?php
/**
 * Download permission checks.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Downloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;

class Omnify_Download_Permission_Service {
	public function __construct(private Omnify_Product_File_Repository $omnify_files, private Omnify_Product_Repository $omnify_products, private Omnify_Customer_Access_Repository $omnify_access) {}

	public function can_access(int $omnify_file_id, ?int $omnify_customer_id = null): bool {
		$omnify_file = $this->omnify_files->find($omnify_file_id);
		if (! $omnify_file || empty($omnify_file['file_path'])) {
			return false;
		}

		$omnify_is_url = (bool) wp_http_validate_url((string) $omnify_file['file_path']);
		if (! $omnify_is_url) {
			$omnify_safe_path = $this->get_safe_file_path((string) $omnify_file['file_path']);
			if (! $omnify_safe_path || ! is_readable($omnify_safe_path)) {
				return false;
			}
			$omnify_file['file_path'] = $omnify_safe_path;
		}

		if (! $this->omnify_files->is_downloadable($omnify_file)) {
			return false;
		}

		$omnify_product = $this->omnify_products->find((int) $omnify_file['product_id']);
		if (! $omnify_product) {
			return false;
		}

		if (current_user_can('manage_omnify') || current_user_can('manage_options')) {
			return true;
		}

		if (! $omnify_customer_id) {
			return false;
		}

		// Verify active purchase grant for the customer (allows downloading even if product is archived/discontinued)
		if (! $this->omnify_access->has_access($omnify_customer_id, (int) $omnify_file['product_id'])) {
			return false;
		}

		// Enforce store-configured maximum download attempts per file
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_download_limit = isset($omnify_settings['download_limit']) ? absint($omnify_settings['download_limit']) : 0;
		if ($omnify_download_limit > 0) {
			global $wpdb;
			$omnify_downloads_table = $wpdb->prefix . 'omnify_downloads';
			$omnify_delivered_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				"SELECT COUNT(*) FROM {$omnify_downloads_table} WHERE customer_id = %d AND file_id = %d AND status = 'delivered'",
				$omnify_customer_id,
				$omnify_file_id
			));
			if ($omnify_delivered_count >= $omnify_download_limit) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Verify file path is within allowed uploads directory.
	 */
	public function get_safe_file_path(string $omnify_path): ?string {
		if ('' === $omnify_path || ! is_readable($omnify_path)) {
			return null;
		}

		$omnify_real_path = realpath($omnify_path);
		if (! $omnify_real_path) {
			return null;
		}

		$omnify_upload_dir = wp_upload_dir();
		$omnify_basedir    = isset($omnify_upload_dir['basedir']) ? realpath($omnify_upload_dir['basedir']) : false;
		if (! $omnify_basedir) {
			return null;
		}

		$omnify_norm_path    = function_exists('wp_normalize_path') ? wp_normalize_path($omnify_real_path) : str_replace('\\', '/', $omnify_real_path);
		$omnify_norm_basedir = function_exists('wp_normalize_path') ? wp_normalize_path($omnify_basedir) : str_replace('\\', '/', $omnify_basedir);

		$omnify_allowed_dirs = (array) apply_filters('omnify_safe_download_directories', [$omnify_norm_basedir]);
		$omnify_is_inside = false;
		foreach ($omnify_allowed_dirs as $omnify_dir) {
			$omnify_norm_dir = function_exists('wp_normalize_path') ? wp_normalize_path((string) $omnify_dir) : str_replace('\\', '/', (string) $omnify_dir);
			if ('' !== $omnify_norm_dir && 0 === stripos($omnify_norm_path, $omnify_norm_dir)) {
				$omnify_is_inside = true;
				break;
			}
		}

		if (! $omnify_is_inside) {
			return null;
		}

		return $omnify_real_path;
	}
}
