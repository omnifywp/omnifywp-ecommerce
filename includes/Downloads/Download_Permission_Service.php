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

		$omnify_safe_path = $this->get_safe_file_path((string) $omnify_file['file_path']);
		if (! $omnify_safe_path) {
			return false;
		}

		// Re-check readability on the validated path
		if (! is_readable($omnify_safe_path)) {
			return false;
		}

		// Temporarily normalize for downstream checks that expect file_path
		$omnify_file['file_path'] = $omnify_safe_path;

		if (! $this->omnify_files->is_downloadable($omnify_file)) {
			return false;
		}

		$omnify_product = $this->omnify_products->find((int) $omnify_file['product_id']);
		if (! $omnify_product) {
			return false;
		}

		if (current_user_can('manage_options')) {
			return true;
		}

		if ('published' !== $omnify_product['status'] || ! $omnify_customer_id) {
			return false;
		}

		return $this->omnify_access->has_access($omnify_customer_id, (int) $omnify_file['product_id']);
	}

	/**
	 * Verify file path is within allowed uploads directory.
	 */
	private function get_safe_file_path(string $omnify_path): ?string {
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

		$omnify_norm_path    = wp_normalize_path($omnify_real_path);
		$omnify_norm_basedir = wp_normalize_path($omnify_basedir);

		if (0 !== stripos($omnify_norm_path, $omnify_norm_basedir)) {
			return null;
		}

		return $omnify_real_path;
	}
}
