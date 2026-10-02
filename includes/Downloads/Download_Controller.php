<?php
/**
 * Secure download delivery and shortcode page.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Downloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Repositories\Omnify_Download_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;

class Omnify_Download_Controller {
	public function __construct(
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Download_Repository $omnify_downloads,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Download_Permission_Service $omnify_permissions,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Customer_Access_Repository $omnify_access
	) {}

	public function register(): void {
		add_shortcode('omnify_download_page', [$this, 'render_download_page']);
		add_shortcode('omnify_customer_downloads', [$this, 'render_customer_downloads']);
	}

	public function maybe_deliver(): void {
		if (empty($_GET['omnify_download'])) {
			return;
		}

		$omnify_file_id     = isset($_GET['file_id']) ? absint(wp_unslash($_GET['file_id'])) : 0;
		$omnify_expires     = isset($_GET['expires']) ? absint(wp_unslash($_GET['expires'])) : 0;
		$omnify_customer_id = isset($_GET['customer_id']) ? (absint(wp_unslash($_GET['customer_id'])) ?: null) : null;
		$omnify_signature   = isset($_GET['signature']) ? sanitize_text_field(wp_unslash((string) $_GET['signature'])) : '';

		if (! $this->omnify_signed_urls->verify($omnify_file_id, $omnify_expires, $omnify_signature, $omnify_customer_id)) {
			wp_die(esc_html__('This download link is invalid or has expired.', 'omnifywp-ecommerce'), esc_html__('Download unavailable', 'omnifywp-ecommerce'), ['response' => 403]);
		}

		if (! $this->omnify_permissions->can_access($omnify_file_id, $omnify_customer_id)) {
			wp_die(esc_html__('You do not have permission to download this file.', 'omnifywp-ecommerce'), esc_html__('Download unavailable', 'omnifywp-ecommerce'), ['response' => 403]);
		}

		$omnify_file = $this->omnify_files->find($omnify_file_id);
		if (! $omnify_file) {
			wp_die(esc_html__('File not found.', 'omnifywp-ecommerce'), esc_html__('Download unavailable', 'omnifywp-ecommerce'), ['response' => 404]);
		}

		$this->omnify_downloads->log((int) $omnify_file['product_id'], (int) $omnify_file['id'], 'delivered', $omnify_customer_id);
		$this->stream($omnify_file);
	}

	public function render_download_page(): string {
		$omnify_file_id     = isset($_GET['file_id']) ? absint(wp_unslash($_GET['file_id'])) : 0;
		$omnify_expires     = isset($_GET['expires']) ? absint(wp_unslash($_GET['expires'])) : 0;
		$omnify_customer_id = isset($_GET['customer_id']) ? (absint(wp_unslash($_GET['customer_id'])) ?: null) : null;
		$omnify_signature   = isset($_GET['signature']) ? sanitize_text_field(wp_unslash((string) $_GET['signature'])) : '';
		$omnify_is_valid    = $omnify_file_id > 0 && $this->omnify_signed_urls->verify($omnify_file_id, $omnify_expires, $omnify_signature, $omnify_customer_id) && $this->omnify_permissions->can_access($omnify_file_id, $omnify_customer_id);
		$omnify_file        = $omnify_is_valid ? $this->omnify_files->find($omnify_file_id) : null;

		ob_start();
		include \omnify_locate_template('download/page.php');
		return (string) ob_get_clean();
	}

	public function render_customer_downloads(): string {
		$omnify_customer = is_user_logged_in() ? $this->omnify_customers->find_by_user_id(get_current_user_id()) : null;
		$omnify_files = [];

		if ($omnify_customer) {
			foreach ($this->omnify_access->files_for_customer((int) $omnify_customer['id']) as $omnify_file) {
				$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], 900, (int) $omnify_customer['id']);
				$omnify_files[] = $omnify_file;
			}
		}

		ob_start();
		include \omnify_locate_template('download/customer-downloads.php');
		return (string) ob_get_clean();
	}

	private function stream(array $omnify_file): void {
		$omnify_path = $this->get_safe_file_path( (string) ($omnify_file['file_path'] ?? '') );
		if ( ! $omnify_path ) {
			wp_die( esc_html__( 'Download file is invalid or inaccessible.', 'omnifywp-ecommerce'), esc_html__( 'Download unavailable', 'omnifywp-ecommerce'), [ 'response' => 403 ] );
		}

		$omnify_filesize = filesize( $omnify_path );

		// Clean all active output buffers to prevent memory exhaustion on large files
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: ' . (string) ( $omnify_file['file_type'] ?: 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( (string) $omnify_file['file_name'] ) . '"' );
		if ( false !== $omnify_filesize ) {
			header( 'Content-Length: ' . (string) $omnify_filesize );
		}
		header( 'X-Content-Type-Options: nosniff' );

		// Stream file in 8KB chunks to handle large files efficiently
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$omnify_handle = fopen( $omnify_path, 'rb' );
		if ( false !== $omnify_handle ) {
			while ( ! feof( $omnify_handle ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
				$omnify_buffer = fread( $omnify_handle, 8192 );
				if ( false === $omnify_buffer ) {
					break;
				}
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $omnify_buffer;
				flush();
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $omnify_handle );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
			readfile( $omnify_path );
		}
		exit;
	}

	/**
	 * Return a verified safe absolute file path, or null if unsafe.
	 * Ensures path is inside the WordPress uploads directory (prevents path traversal).
	 */
	private function get_safe_file_path( string $omnify_path ): ?string {
		if ( '' === $omnify_path || ! is_readable( $omnify_path ) ) {
			return null;
		}

		$omnify_real_path = realpath( $omnify_path );
		if ( ! $omnify_real_path ) {
			return null;
		}

		$omnify_upload_dir = wp_upload_dir();
		$omnify_basedir    = isset( $omnify_upload_dir['basedir'] ) ? realpath( $omnify_upload_dir['basedir'] ) : false;
		if ( ! $omnify_basedir ) {
			return null;
		}

		// Normalize both paths (forward slashes and case) to prevent false 403 on Windows environments
		$omnify_norm_path    = function_exists( 'wp_normalize_path' ) ? wp_normalize_path( $omnify_real_path ) : str_replace( '\\', '/', $omnify_real_path );
		$omnify_norm_basedir = function_exists( 'wp_normalize_path' ) ? wp_normalize_path( $omnify_basedir ) : str_replace( '\\', '/', $omnify_basedir );

		// Ensure the resolved path is inside the uploads base dir
		if ( 0 !== stripos( $omnify_norm_path, $omnify_norm_basedir ) ) {
			return null;
		}

		return $omnify_real_path;
	}
}
