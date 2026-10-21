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
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Support\Omnify_Email_Service;

class Omnify_Download_Controller {
	public function __construct(
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Download_Repository $omnify_downloads,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Download_Permission_Service $omnify_permissions,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Customer_Access_Repository $omnify_access,
		private ?Omnify_Order_Repository $omnify_orders = null,
		private ?Omnify_Email_Service $omnify_emails = null
	) {}

	public function register(): void {
		add_shortcode('omnify_download_page', [$this, 'render_download_page']);
		add_shortcode('omnify_customer_downloads', [$this, 'render_customer_downloads']);
	}

	public function maybe_deliver(): void {
		if (empty($_GET['omnify_download'])) {
			return;
		}

		// Item 24: Throttle download requests per IP to prevent token brute force or bandwidth abuse
		$omnify_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
		// Normalize equivalent IPv6 notations (e.g. ::1 vs 0:0:0:0:0:0:0:1) so the limiter cannot be bypassed.
		$omnify_packed_ip = @inet_pton($omnify_ip);
		if (false !== $omnify_packed_ip) {
			$omnify_ip = inet_ntop($omnify_packed_ip);
		}
		$omnify_throttle_key = 'omnify_dl_throttle_' . md5($omnify_ip);
		$omnify_attempts = (int) get_transient($omnify_throttle_key);
		if ($omnify_attempts > 30) {
			wp_die(
				esc_html__('Too many download requests. Please wait a minute before trying again.', 'omnifywp-ecommerce'),
				esc_html__('Download Rate Limit Exceeded', 'omnifywp-ecommerce'),
				['response' => 429]
			);
		}
		set_transient($omnify_throttle_key, $omnify_attempts + 1, MINUTE_IN_SECONDS);

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

		// Only count download attempts that start from the beginning (prevent RFC 7233 chunk/resumed requests from exhausting download limits)
		$omnify_is_resume = false;
		if (isset($_SERVER['HTTP_RANGE'])) {
			$omnify_http_range = sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_RANGE']));
			if (preg_match('/bytes=\s*(\d+)/i', $omnify_http_range, $omnify_matches)) {
				if ((int) $omnify_matches[1] > 0) {
					$omnify_is_resume = true;
				}
			}
		}

		if (! $omnify_is_resume) {
			$this->omnify_downloads->log((int) $omnify_file['product_id'], (int) $omnify_file['id'], 'delivered', $omnify_customer_id);
		}

		// If the file path is an external cloud/CDN URL, redirect securely
		$omnify_file_path = (string) ($omnify_file['file_path'] ?? '');
		if (wp_http_validate_url($omnify_file_path)) {
			wp_redirect(esc_url_raw($omnify_file_path));
			exit;
		}

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
		$omnify_guest_error = '';
		$omnify_guest_success = '';
		$omnify_guest_lookup = false;

		// Handle guest lookup submission
		if (! is_user_logged_in() && ! empty($_POST['omnify_guest_download_lookup'])) {
			if (! isset($_POST['omnify_guest_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['omnify_guest_nonce'])), 'omnify_guest_downloads')) {
				$omnify_guest_error = __('Security check failed. Please refresh the page and try again.', 'omnifywp-ecommerce');
			} else {
				$omnify_raw_number = sanitize_text_field(wp_unslash((string) ($_POST['omnify_order_number'] ?? '')));
				$omnify_clean_email = sanitize_email(wp_unslash((string) ($_POST['omnify_order_email'] ?? '')));

				if ('' === $omnify_raw_number || '' === $omnify_clean_email) {
					$omnify_guest_error = __('Please enter both your order number and billing email.', 'omnifywp-ecommerce');
				} else {
					$omnify_order_id = $this->omnify_orders ? $this->omnify_orders->find_id_by_order_number($omnify_raw_number) : 0;
					if (! $omnify_order_id && $this->omnify_orders) {
						$omnify_trimmed_num = ltrim($omnify_raw_number, '#');
						$omnify_order_id = $this->omnify_orders->find_id_by_order_number($omnify_trimmed_num);
						if (! $omnify_order_id && is_numeric($omnify_trimmed_num)) {
							$omnify_order_id = absint($omnify_trimmed_num);
						}
					}

					$omnify_order = ($omnify_order_id && $this->omnify_orders) ? $this->omnify_orders->find($omnify_order_id) : null;
					$omnify_order_email = $omnify_order ? (string) ($omnify_order['customer_email'] ?? ($omnify_order['billing_email'] ?? '')) : '';

					if (! $omnify_order || 0 !== strcasecmp(trim($omnify_order_email), trim($omnify_clean_email))) {
						$omnify_guest_error = __('No matching order found for the provided details. Please check your order number and email address.', 'omnifywp-ecommerce');
					} elseif (! in_array((string) ($omnify_order['status'] ?? ''), ['completed', 'processing', 'delivered'], true)) {
						$omnify_guest_error = __('Digital downloads are not available because this order has not been completed or is cancelled.', 'omnifywp-ecommerce');
					} else {
						$omnify_guest_customer_id = absint($omnify_order['customer_id'] ?? 0);
						if (! $omnify_guest_customer_id) {
							$omnify_cust_row = $this->omnify_customers->find_by_email($omnify_clean_email);
							$omnify_guest_customer_id = $omnify_cust_row ? (int) $omnify_cust_row['id'] : 0;
						}

						if ($omnify_guest_customer_id > 0) {
							$omnify_ttl = (int) apply_filters('omnify_download_url_ttl', DAY_IN_SECONDS * 7, 'guest_lookup');
							$omnify_order_product_ids = [];
							if (! empty($omnify_order['items']) && is_array($omnify_order['items'])) {
								foreach ($omnify_order['items'] as $omnify_oi) {
									$omnify_order_product_ids[] = absint($omnify_oi['product_id'] ?? 0);
								}
							}

							foreach ($this->omnify_access->files_for_customer($omnify_guest_customer_id) as $omnify_file) {
								if (empty($omnify_order_product_ids) || in_array((int) $omnify_file['product_id'], $omnify_order_product_ids, true)) {
									$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], $omnify_ttl, $omnify_guest_customer_id);
									$omnify_files[] = $omnify_file;
								}
							}

							$omnify_guest_lookup = true;
							if (empty($omnify_files)) {
								$omnify_guest_error = __('This order does not contain downloadable digital files.', 'omnifywp-ecommerce');
							} else {
								// translators: %s: Order number.
								$omnify_guest_success = sprintf(__('Verified Order #%s. Fresh download links generated below.', 'omnifywp-ecommerce'), esc_html((string) ($omnify_order['order_number'] ?? $omnify_order_id)));
							}
						} else {
							$omnify_guest_error = __('Unable to locate customer download permissions for this order.', 'omnifywp-ecommerce');
						}
					}
				}
			}
		}

		if (is_user_logged_in() && $omnify_customer) {
			$omnify_ttl = (int) apply_filters('omnify_download_url_ttl', DAY_IN_SECONDS, 'shortcode');
			foreach ($this->omnify_access->files_for_customer((int) $omnify_customer['id']) as $omnify_file) {
				$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], $omnify_ttl, (int) $omnify_customer['id']);
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
		if ( false === $omnify_filesize || $omnify_filesize < 0 ) {
			wp_die( esc_html__( 'Unable to determine download file size.', 'omnifywp-ecommerce' ), esc_html__( 'Download unavailable', 'omnifywp-ecommerce' ), [ 'response' => 500 ] );
		}

		// Clean all active output buffers to prevent memory exhaustion on large files
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: ' . (string) ( $omnify_file['file_type'] ?: 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( (string) $omnify_file['file_name'] ) . '"' );
		header( 'Accept-Ranges: bytes' );
		header( 'X-Content-Type-Options: nosniff' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$omnify_handle = fopen( $omnify_path, 'rb' );
		if ( false === $omnify_handle ) {
			wp_die( esc_html__( 'Failed to open file for streaming.', 'omnifywp-ecommerce' ), esc_html__( 'Download error', 'omnifywp-ecommerce' ), [ 'response' => 500 ] );
		}

		// Support HTTP Range requests (resumable downloads RFC 7233)
		$omnify_start  = 0;
		$omnify_end    = $omnify_filesize - 1;
		$omnify_length = $omnify_filesize;

		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			$omnify_http_range = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_RANGE'] ) );
			if ( preg_match( '/bytes=\s*(\d*)\s*-\s*(\d*)/i', $omnify_http_range, $omnify_matches ) ) {
				$omnify_range_start = '' !== $omnify_matches[1] ? (float) $omnify_matches[1] : null;
				$omnify_range_end   = '' !== $omnify_matches[2] ? (float) $omnify_matches[2] : null;

				if ( null === $omnify_range_start ) {
					$omnify_start = max( 0.0, (float) ( $omnify_filesize - (float) $omnify_range_end ) );
				} else {
					$omnify_start = (float) $omnify_range_start;
					if ( null !== $omnify_range_end && $omnify_range_end >= $omnify_start ) {
						$omnify_end = min( (float) ( $omnify_filesize - 1 ), (float) $omnify_range_end );
					}
				}

				if ( $omnify_start > $omnify_end || $omnify_start >= $omnify_filesize ) {
					header( 'HTTP/1.1 416 Range Not Satisfiable' );
					header( "Content-Range: bytes */{$omnify_filesize}" );
					fclose( $omnify_handle );
					exit;
				}

				$omnify_length = ( $omnify_end - $omnify_start ) + 1;
				header( 'HTTP/1.1 206 Partial Content' );
				header( sprintf( 'Content-Range: bytes %.0f-%.0f/%.0f', $omnify_start, $omnify_end, $omnify_filesize ) );
			}
		}

		header( sprintf( 'Content-Length: %.0f', $omnify_length ) );

		if ( $omnify_start > 0 ) {
			fseek( $omnify_handle, (int) $omnify_start );
		}

		while ( ob_get_level() > 0 ) {
			@ob_end_clean();
		}

		// Stream requested range in 64KB chunks with abort detection
		$omnify_bytes_remaining = $omnify_length;
		$omnify_max_chunk       = 65536; // 64KB optimal chunk size
		while ( ! feof( $omnify_handle ) && $omnify_bytes_remaining > 0 ) {
			if ( connection_aborted() ) {
				break;
			}
			$omnify_chunk_size = (int) min( $omnify_max_chunk, $omnify_bytes_remaining );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			$omnify_buffer = fread( $omnify_handle, $omnify_chunk_size );
			if ( false === $omnify_buffer || '' === $omnify_buffer ) {
				break;
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $omnify_buffer;
			@ob_flush();
			flush();
			$omnify_bytes_remaining -= strlen( $omnify_buffer );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $omnify_handle );
		exit;
	}

	/**
	 * Return a verified safe absolute file path, or null if unsafe.
	 * Delegates to Omnify_Download_Permission_Service for centralized verification.
	 */
	private function get_safe_file_path( string $omnify_path ): ?string {
		return $this->omnify_permissions->get_safe_file_path( $omnify_path );
	}
}
