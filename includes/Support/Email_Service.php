<?php
/**
 * Email notifications service.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;

class Omnify_Email_Service {
	public function __construct(
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Product_Repository $omnify_products
	) {
		// Register SMTP configuration for custom mailer (checked at send time)
		add_action('phpmailer_init', [$this, 'maybe_configure_smtp']);

		// Automatically send customer email when order status changes (if enabled in settings)
		add_action('omnify_order_status_changed', [$this, 'maybe_send_status_email'], 10, 3);
	}

	private function currency_symbol(string $omnify_currency): string {
		return match ($omnify_currency) {
			'EUR'   => '€',
			'GBP'   => '£',
			'JPY'   => '¥',
			'CAD'   => 'C$',
			'AUD'   => 'A$',
			default => '$',
		};
	}

	private function omnify_settings(): array {
		$omnify_settings = get_option('omnify_settings', []);

		return is_array($omnify_settings) ? $omnify_settings : [];
	}

	private function emails_enabled(): bool {
		$omnify_settings = $this->omnify_settings();
		return ! array_key_exists('email_notifications_enabled', $omnify_settings) || ! empty($omnify_settings['email_notifications_enabled']);
	}

	private function replace_tokens(string $omnify_content, array $omnify_order, array $omnify_extra = []): string {
		$omnify_tokens = array_merge(
			[
				'{site_name}' => get_bloginfo('name'),
				'{order_id}' => (string) ($omnify_order['order_number'] ?: ('#' . ($omnify_order['id'] ?? ''))),
				'{customer_email}' => (string) ($omnify_order['customer_email'] ?? ''),
				'{order_total}' => ($omnify_order['currency'] ?? '') . ' ' . number_format((float) ($omnify_order['total'] ?? 0), 2),
				'{order_status}' => ucwords(str_replace('_', ' ', (string) ($omnify_order['status'] ?? ''))),
			],
			$omnify_extra
		);

		return strtr($omnify_content, $omnify_tokens);
	}

	private function simple_email_body(string $omnify_heading, string $omnify_message, array $omnify_order): string {
		$omnify_settings = $this->omnify_settings();
		$omnify_footer = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		return '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
			<h2 style="color: #0f172a; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-top: 0;">' . esc_html($omnify_heading) . '</h2>
			<p style="font-size: 14px; line-height: 1.6;">' . nl2br(esc_html($omnify_message)) . '</p>
			<p style="font-size: 13px; color: #64748b;">Order ' . esc_html((string) ($omnify_order['order_number'] ?: ('#' . ($omnify_order['id'] ?? '')))) . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e5e7eb; padding-top: 14px; margin-top: 24px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
			</div>';
	}

	private function admin_notification_recipients(array $omnify_settings): array {
		$omnify_raw = (string) ($omnify_settings['admin_notification_email'] ?? get_option('admin_email'));
		$omnify_recipients = array_filter(array_map('sanitize_email', array_map('trim', explode(',', $omnify_raw))));

		return ! empty($omnify_recipients) ? array_values($omnify_recipients) : [get_option('admin_email')];
	}

	private function default_headers(): array {
		$omnify_settings = $this->omnify_settings();
		$omnify_headers  = ['Content-Type: text/html; charset=UTF-8'];
		$omnify_from     = sanitize_email((string) ($omnify_settings['email_from_address'] ?? ''));
		$omnify_name     = sanitize_text_field((string) ($omnify_settings['email_from_name'] ?? get_bloginfo('name')));

		if ($omnify_from && is_email($omnify_from)) {
			$omnify_headers[] = sprintf('From: %s <%s>', $omnify_name ?: get_bloginfo('name'), $omnify_from);
		}

		$omnify_reply_to = sanitize_email((string) ($omnify_settings['email_reply_to'] ?? ''));
		if ($omnify_reply_to && is_email($omnify_reply_to)) {
			$omnify_headers[] = 'Reply-To: ' . $omnify_reply_to;
		}

		if (! empty($omnify_settings['email_bcc_admin'])) {
			$omnify_bcc = $this->admin_notification_recipients($omnify_settings);
			if (! empty($omnify_bcc)) {
				$omnify_headers[] = 'Bcc: ' . implode(', ', $omnify_bcc);
			}
		}

		return $omnify_headers;
	}

	/**
	 * Configure PHPMailer with custom SMTP when enabled in settings.
	 * This runs on phpmailer_init for every wp_mail call.
	 */
	public function maybe_configure_smtp( $omnify_phpmailer ): void {
		$omnify_settings = $this->omnify_settings();

		if ( empty( $omnify_settings['email_smtp_enabled'] ) || empty( $omnify_settings['email_smtp_host'] ) ) {
			return;
		}

		$omnify_phpmailer->isSMTP();
		$omnify_phpmailer->Host       = sanitize_text_field( (string) $omnify_settings['email_smtp_host'] );
		$omnify_phpmailer->Port       = absint( $omnify_settings['email_smtp_port'] ?? 587 );

		$omnify_encryption = sanitize_text_field( (string) ( $omnify_settings['email_smtp_encryption'] ?? 'tls' ) );
		if ( $omnify_encryption === 'ssl' ) {
			$omnify_phpmailer->SMTPSecure = 'ssl';
		} elseif ( $omnify_encryption === 'tls' ) {
			$omnify_phpmailer->SMTPSecure = 'tls';
		} else {
			$omnify_phpmailer->SMTPSecure = '';
		}

		$omnify_phpmailer->SMTPAuth = ! empty( $omnify_settings['email_smtp_auth'] );
		if ( $omnify_phpmailer->SMTPAuth ) {
			$omnify_phpmailer->Username = sanitize_text_field( (string) ( $omnify_settings['email_smtp_username'] ?? '' ) );
			$omnify_phpmailer->Password = (string) ( $omnify_settings['email_smtp_password'] ?? '' );
		}

		// Use the plugin's From name/address as default if not already set
		if ( empty( $omnify_phpmailer->From ) ) {
			$omnify_from = sanitize_email( (string) ( $omnify_settings['email_from_address'] ?? get_option( 'admin_email' ) ) );
			if ( $omnify_from ) {
				$omnify_phpmailer->From = $omnify_from;
			}
		}
		if ( empty( $omnify_phpmailer->FromName ) ) {
			$omnify_phpmailer->FromName = sanitize_text_field( (string) ( $omnify_settings['email_from_name'] ?? get_bloginfo( 'name' ) ) );
		}
	}

	private function recipient_list($omnify_to): string {
		if (is_array($omnify_to)) {
			return implode(', ', array_map('sanitize_email', array_map('strval', $omnify_to)));
		}

		return sanitize_email((string) $omnify_to);
	}

	private function log_email(string $omnify_trigger, $omnify_to, string $omnify_subject, string $omnify_body, array $omnify_headers, string $omnify_status, string $omnify_error_message = '', array $omnify_context = []): int {
		global $wpdb;

		$omnify_table = $wpdb->prefix . 'omnify_email_logs';
		if (\Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) !== $omnify_table) {
			return 0;
		}

		$omnify_order_id = isset($omnify_context['order_id']) ? absint($omnify_context['order_id']) : null;
		$omnify_now      = current_time('mysql');

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$omnify_table,
			[
				'trigger_name'  => sanitize_key($omnify_trigger),
				'recipient'     => $this->recipient_list($omnify_to),
				'subject'       => $omnify_subject,
				'body'          => $omnify_body,
				'headers'       => wp_json_encode(array_values($omnify_headers)),
				'status'        => $omnify_status,
				'error_message' => $omnify_error_message,
				'order_id'      => $omnify_order_id ?: null,
				'context'       => wp_json_encode($omnify_context),
				'created_at'    => $omnify_now,
				'sent_at'       => 'sent' === $omnify_status ? $omnify_now : null,
			],
			['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
		);

		return (int) $wpdb->insert_id;
	}

	private function send_logged(string $omnify_trigger, $omnify_to, string $omnify_subject, string $omnify_body, array $omnify_headers = [], array $omnify_context = []): bool {
		$omnify_headers = $omnify_headers ?: $this->default_headers();

		$omnify_args = apply_filters('omnify_email_arguments', [
			'trigger' => $omnify_trigger,
			'to'      => $omnify_to,
			'subject' => $omnify_subject,
			'body'    => $omnify_body,
			'headers' => $omnify_headers,
			'context' => $omnify_context
		]);

		$omnify_trigger = (string) $omnify_args['trigger'];
		$omnify_to      = $omnify_args['to'];
		$omnify_subject = (string) $omnify_args['subject'];
		$omnify_body    = (string) $omnify_args['body'];
		$omnify_headers = (array) $omnify_args['headers'];
		$omnify_context = (array) $omnify_args['context'];

		$omnify_failure = null;
		$omnify_capture = static function ($omnify_error) use (&$omnify_failure): void {
			$omnify_failure = $omnify_error;
		};

		add_action('wp_mail_failed', $omnify_capture);
		$omnify_sent = wp_mail($omnify_to, $omnify_subject, $omnify_body, $omnify_headers);
		remove_action('wp_mail_failed', $omnify_capture);

		$omnify_error_message = '';
		if (! $omnify_sent) {
			if ($omnify_failure && is_wp_error($omnify_failure)) {
				$omnify_error_message = $omnify_failure->get_error_message();
			}
			if (! $omnify_error_message) {
				$omnify_error_message = __('WordPress mail returned false without a detailed error.', 'omnifywp-ecommerce');
			}
		}

		$this->log_email($omnify_trigger, $omnify_to, $omnify_subject, $omnify_body, $omnify_headers, $omnify_sent ? 'sent' : 'failed', $omnify_error_message, $omnify_context);

		do_action('omnify_email_sent', $omnify_sent, $omnify_trigger, $omnify_to, $omnify_subject, $omnify_body, $omnify_headers, $omnify_context, $omnify_error_message);

		return (bool) $omnify_sent;
	}

	public function send_test_email(string $omnify_to): bool {
		$omnify_to = sanitize_email($omnify_to);
		if (! is_email($omnify_to)) {
			return false;
		}

		$omnify_site_name = get_bloginfo('name');
		// translators: %s: site name.
		$omnify_subject   = sprintf(__('[%s] Test email from Omnify', 'omnifywp-ecommerce'), $omnify_site_name);
		
		// translators: %s: site name.
		$omnify_site_name_html = esc_html(sprintf(__('Site: %s', 'omnifywp-ecommerce'), $omnify_site_name));
		$omnify_body      = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 24px;">' . esc_html__('Email delivery test', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.7; margin: 0;">' . esc_html__('This test confirms that Omnify can hand transactional email to WordPress mail delivery.', 'omnifywp-ecommerce') . '</p>
			<p style="font-size: 13px; color: #64748b; margin: 18px 0 0;">' . $omnify_site_name_html . '</p>
		</div>';

		return $this->send_logged('test_email', $omnify_to, $omnify_subject, $omnify_body, [], ['source' => 'settings_test']);
	}

	public function resend_logged_email(int $omnify_log_id): bool {
		global $wpdb;

		$omnify_table = $wpdb->prefix . 'omnify_email_logs';
		if (\Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) !== $omnify_table) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_log = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$omnify_table} WHERE id = %d", $omnify_log_id), ARRAY_A);
		if (! $omnify_log || empty($omnify_log['recipient']) || empty($omnify_log['subject']) || empty($omnify_log['body'])) {
			return false;
		}

		$omnify_headers = json_decode((string) ($omnify_log['headers'] ?? ''), true);
		if (! is_array($omnify_headers) || empty($omnify_headers)) {
			$omnify_headers = $this->default_headers();
		}

		$omnify_context = json_decode((string) ($omnify_log['context'] ?? ''), true);
		if (! is_array($omnify_context)) {
			$omnify_context = [];
		}
		$omnify_context['resend_of_log_id'] = $omnify_log_id;

		$omnify_recipients = array_filter(array_map('sanitize_email', array_map('trim', explode(',', (string) $omnify_log['recipient']))));
		$omnify_to = count($omnify_recipients) > 1 ? array_values($omnify_recipients) : (string) $omnify_log['recipient'];

		return $this->send_logged('resend_' . sanitize_key((string) $omnify_log['trigger_name']), $omnify_to, (string) $omnify_log['subject'], (string) $omnify_log['body'], $omnify_headers, $omnify_context);
	}

	public function send_receipt(int $omnify_order_id): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_to      = $omnify_order['customer_email'];
		$omnify_settings = $this->omnify_settings();
		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_receipt_subject'] ?? '[{site_name}] Order Receipt {order_id}'), $omnify_order);
		$omnify_intro = $this->replace_tokens((string) ($omnify_settings['email_receipt_intro'] ?? 'Thank you for your purchase! Your order has been completed successfully.'), $omnify_order);
		$omnify_symbol  = $this->currency_symbol($omnify_order['currency']);

		// Build order items rows
		$omnify_items_html = '';
		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_items_html .= sprintf(
				'<tr>
					<td style="padding: 10px; border-bottom: 1px solid #eee;">%s</td>
					<td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">%d</td>
					<td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">%s</td>
				</tr>',
				esc_html($omnify_item['product_name']),
				$omnify_item['quantity'],
				esc_html($omnify_symbol . number_format(($omnify_item['price'] + $omnify_item['tax']) * $omnify_item['quantity'], 2))
			);
		}

		// Build download links html
		$omnify_downloads_html = '';
		$omnify_files_found    = false;
		if ($omnify_order['customer_id']) {
			foreach ($omnify_order['items'] as $omnify_item) {
				$omnify_product_id = (int) $omnify_item['product_id'];
				if ($omnify_product_id > 0) {
					$omnify_product = $this->omnify_products->find($omnify_product_id);
					$omnify_target_pids = [$omnify_product_id];
					if ($omnify_product && 'bundle' === $omnify_product['type']) {
						$omnify_target_pids = ! empty($omnify_product['bundled_ids']) && is_array($omnify_product['bundled_ids']) ? $omnify_product['bundled_ids'] : [];
					}

					foreach ($omnify_target_pids as $omnify_pid) {
						$omnify_product_files = $this->omnify_files->active_for_product((int) $omnify_pid);
						if (! empty($omnify_product_files)) {
							$omnify_files_found     = true;
							$omnify_child_name = $omnify_pid === $omnify_product_id ? $omnify_item['product_name'] : (($omnify_child = $this->omnify_products->find((int) $omnify_pid)) ? $omnify_child['name'] : $omnify_item['product_name']);
							$omnify_downloads_html .= sprintf(
								'<h3 style="margin-top: 20px; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px; color: #0f172a;">%s</h3>',
								esc_html($omnify_child_name)
							);
							foreach ($omnify_product_files as $omnify_file) {
								$omnify_url             = $this->omnify_signed_urls->create((int) $omnify_file['id'], 3600 * 24 * 7, (int) $omnify_order['customer_id']);
								$omnify_downloads_html .= sprintf(
									'<p style="margin: 8px 0; font-size: 13px;">
										<strong>%s</strong> (v%s) - 
										<a href="%s" style="color: #6366f1; text-decoration: none; font-weight: bold;">Download File</a>
									</p>',
									esc_html($omnify_file['file_name']),
									esc_html($omnify_file['version']),
									esc_url($omnify_url)
								);
							}
						}
					}
				}
			}
		}

		$omnify_shipping_html = '';
		if (! empty($omnify_order['shipping_address_1'])) {
			$omnify_shipping_html = sprintf(
				'<div style="background-color: #f1f5f9; border-radius: 6px; padding: 15px; margin-top: 20px;">
					<h3 style="font-size: 15px; margin-top: 0; color: #0f172a;">Shipping Address</h3>
					<p style="font-size: 13px; line-height: 1.5; margin: 0; color: #334155;">
						<strong>%1$s %2$s</strong><br />
						%3$s<br />
						%4$s
						%5$s, %6$s %7$s<br />
						%8$s<br />
						%9$s
					</p>
				</div>',
				esc_html($omnify_order['shipping_first_name']),
				esc_html($omnify_order['shipping_last_name']),
				esc_html($omnify_order['shipping_address_1']),
				! empty($omnify_order['shipping_address_2']) ? esc_html($omnify_order['shipping_address_2']) . '<br />' : '',
				esc_html($omnify_order['shipping_city']),
				esc_html($omnify_order['shipping_state']),
				esc_html($omnify_order['shipping_postcode']),
				esc_html($omnify_order['shipping_country']),
				! empty($omnify_order['shipping_phone']) ? 'Phone: ' . esc_html($omnify_order['shipping_phone']) : ''
			);
		}

		$omnify_downloads_section_html = '';
		if ($omnify_files_found) {
			$omnify_downloads_section_html = '
			<div style="background-color: #f1f5f9; border-radius: 6px; padding: 15px; margin-top: 20px;">
				<h2 style="font-size: 16px; margin-top: 0; color: #0f172a;">Your Digital Downloads</h2>
				<p style="font-size: 12px; color: #666; margin-top: -5px; margin-bottom: 15px;">These download links are active for 7 days.</p>
				' . $omnify_downloads_html . '
			</div>';
		}

		// Payment method line
		$omnify_payment_method_label = $omnify_order['payment_method'] ?? '';
		$omnify_payment_method_html  = '';
		if ($omnify_payment_method_label && $omnify_payment_method_label !== 'card') {
			$omnify_payment_method_html = '<div style="display: flex; justify-content: space-between; padding: 4px 0;">
				<span>Payment Method:</span>
				<span style="text-transform: capitalize;">' . esc_html(ucwords(str_replace('_', ' ', $omnify_payment_method_label))) . '</span>
			</div>';
		}

		// Discount line
		$omnify_discount_html = '';
		if (! empty($omnify_order['coupon_code']) && (float) ($omnify_order['discount_amount'] ?? 0) > 0) {
			$omnify_discount_html = '<div style="display: flex; justify-content: space-between; padding: 4px 0; color: #008060;">
				<span>Discount (' . esc_html(strtoupper($omnify_order['coupon_code'])) . '):</span>
				<span>-' . esc_html($omnify_symbol . number_format((float) $omnify_order['discount_amount'], 2)) . '</span>
			</div>';
		}

		$omnify_delivery_html = '';
		if ((float) ($omnify_order['shipping_total'] ?? 0) > 0 || ! empty($omnify_order['shipping_method'])) {
			$omnify_delivery_label = ! empty($omnify_order['shipping_method']) ? $omnify_order['shipping_method'] : 'Delivery';
			$omnify_delivery_html = '<div style="display: flex; justify-content: space-between; padding: 4px 0;">
				<span>' . esc_html($omnify_delivery_label) . ':</span>
				<span>' . esc_html($omnify_symbol . number_format((float) ($omnify_order['shipping_total'] ?? 0), 2)) . '</span>
			</div>';
		}

		$omnify_body = '
		<div style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e5e5; border-radius: 8px;">
			<h2 style="color: #0f172a; border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-top: 0;">Order Receipt</h2>
			<p>' . nl2br(esc_html($omnify_intro)) . '</p>

			<table style="width: 100%; margin: 20px 0; border-collapse: collapse; font-size: 14px;">
				<tr>
					<td style="padding: 5px 0;"><strong>Order Number:</strong> ' . esc_html($omnify_order['order_number'] ?: ($omnify_order['id'] ?? '')) . '</td>
					<td style="padding: 5px 0; text-align: right;"><strong>Date:</strong> ' . wp_date('Y-m-d H:i') . '</td>
				</tr>
			</table>

			<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
				<thead>
					<tr style="background-color: #f8fafc;">
						<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
						<th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Qty</th>
						<th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Total</th>
					</tr>
				</thead>
				<tbody>' . $omnify_items_html . '</tbody>
			</table>

			<div style="width: 260px; margin-left: auto; margin-bottom: 30px; font-size: 14px;">
				<div style="display: flex; justify-content: space-between; padding: 4px 0;">
					<span>Subtotal:</span><span>' . $omnify_symbol . number_format($omnify_order['subtotal'], 2) . '</span>
				</div>
				' . $omnify_discount_html . '
				<div style="display: flex; justify-content: space-between; padding: 4px 0;">
					<span>Tax:</span><span>' . $omnify_symbol . number_format($omnify_order['tax'], 2) . '</span>
				</div>
				' . $omnify_delivery_html . '
				' . $omnify_payment_method_html . '
				<div style="display: flex; justify-content: space-between; padding: 8px 0; font-weight: bold; border-top: 1px solid #ddd; font-size: 16px;">
					<span>Total:</span><span>' . $omnify_symbol . number_format($omnify_order['total'], 2) . '</span>
				</div>
			</div>

			' . $omnify_shipping_html . '
			' . $omnify_downloads_section_html . '

			<p style="font-size: 12px; color: #777; margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
				Access your account status anytime via the <a href="' . esc_url(home_url('/customer-portal/')) . '" style="color: #6366f1;">Customer Portal</a>.
			</p>
		</div>';

		$omnify_sent = $this->send_logged('order_receipt', $omnify_to, $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id]);

		// Send admin copy of receipt if enabled
		$omnify_settings = $this->omnify_settings();
		if (! empty($omnify_settings['email_send_receipt_copy'])) {
			$omnify_admin_recips = $this->admin_notification_recipients($omnify_settings);
			if (! empty($omnify_admin_recips)) {
				$omnify_admin_subject = $omnify_subject . ' (Admin Copy)';
				$this->send_logged('order_receipt_admin_copy', $omnify_admin_recips, $omnify_admin_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'admin_copy' => true]);
			}
		}

		return $omnify_sent;
	}

	/**
	 * Send a payment pending notification with payment instructions.
	 */
	public function send_payment_pending(int $omnify_order_id): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_to      = $omnify_order['customer_email'];
		$omnify_settings = $this->omnify_settings();
		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_payment_pending_subject'] ?? '[{site_name}] Order {order_id} — Awaiting Payment'), $omnify_order);
		$omnify_intro = $this->replace_tokens((string) ($omnify_settings['email_payment_pending_intro'] ?? 'Thank you for your order! It is awaiting payment.'), $omnify_order);
		$omnify_symbol  = $this->currency_symbol($omnify_order['currency']);

		$omnify_method_label      = ucwords(str_replace('_', ' ', $omnify_order['payment_method'] ?? 'manual'));
		$omnify_raw_instructions  = $omnify_order['payment_instructions'] ?? 'Please contact us to arrange payment.';
		$omnify_instructions_html = nl2br(esc_html($omnify_raw_instructions));

		$omnify_items_html = '';
		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_items_html .= sprintf(
				'<tr>
					<td style="padding: 8px 10px; border-bottom: 1px solid #eee;">%s</td>
					<td style="padding: 8px 10px; border-bottom: 1px solid #eee; text-align: right;">%s</td>
				</tr>',
				esc_html($omnify_item['product_name']),
				esc_html($omnify_symbol . number_format(($omnify_item['price'] + $omnify_item['tax']) * $omnify_item['quantity'], 2))
			);
		}

		$omnify_awaiting_message = '<p>Your downloads will be unlocked as soon as we confirm receipt of your payment.</p>';
		$omnify_shipping_html = '';
		if (! empty($omnify_order['shipping_address_1'])) {
			$omnify_awaiting_message = '<p>Your order will be processed and shipped as soon as we confirm receipt of your payment.</p>';
			$omnify_shipping_html = sprintf(
				'<div style="background-color: #f1f5f9; border-radius: 6px; padding: 15px; margin-top: 20px;">
					<h3 style="font-size: 15px; margin-top: 0; color: #0f172a;">Shipping Address</h3>
					<p style="font-size: 13px; line-height: 1.5; margin: 0; color: #334155;">
						<strong>%1$s %2$s</strong><br />
						%3$s<br />
						%4$s
						%5$s, %6$s %7$s<br />
						%8$s<br />
						%9$s
					</p>
				</div>',
				esc_html($omnify_order['shipping_first_name']),
				esc_html($omnify_order['shipping_last_name']),
				esc_html($omnify_order['shipping_address_1']),
				! empty($omnify_order['shipping_address_2']) ? esc_html($omnify_order['shipping_address_2']) . '<br />' : '',
				esc_html($omnify_order['shipping_city']),
				esc_html($omnify_order['shipping_state']),
				esc_html($omnify_order['shipping_postcode']),
				esc_html($omnify_order['shipping_country']),
				! empty($omnify_order['shipping_phone']) ? 'Phone: ' . esc_html($omnify_order['shipping_phone']) : ''
			);
		}

		$omnify_body = '
		<div style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e5e5; border-radius: 8px;">
			<h2 style="color: #0f172a; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-top: 0;">⏳ Order Awaiting Payment</h2>
			<p>' . nl2br(esc_html($omnify_intro)) . ' <strong>' . esc_html($omnify_method_label) . '</strong>.</p>
			' . $omnify_awaiting_message . '

			<table style="width: 100%; margin: 20px 0; border-collapse: collapse; font-size: 14px;">
				<tr>
					<td style="padding: 5px 0;"><strong>Order ID:</strong> ' . esc_html($omnify_order['order_number'] ?: ($omnify_order['id'] ?? '')) . '</td>
					<td style="padding: 5px 0; text-align: right;"><strong>Date:</strong> ' . wp_date('Y-m-d H:i') . '</td>
				</tr>
			</table>

			<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
				<thead>
					<tr style="background-color: #f8fafc;">
						<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
						<th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Total</th>
					</tr>
				</thead>
				<tbody>' . $omnify_items_html . '</tbody>
			</table>

			<div style="width: 220px; margin-left: auto; margin-bottom: 20px; font-size: 14px;">
				<div style="display: flex; justify-content: space-between; padding: 8px 0; font-weight: bold; border-top: 1px solid #ddd; font-size: 16px;">
					<span>Total Due:</span>
					<span>' . $omnify_symbol . number_format($omnify_order['total'], 2) . ' ' . esc_html($omnify_order['currency']) . '</span>
				</div>
			</div>

			<div style="background-color: #fffbeb; border: 1px solid #f59e0b; border-radius: 6px; padding: 16px; margin-top: 20px;">
				<h3 style="font-size: 15px; margin-top: 0; color: #92400e;">Payment Instructions — ' . esc_html($omnify_method_label) . '</h3>
				<p style="font-size: 13px; line-height: 1.7; margin: 0; color: #78350f;">' . $omnify_instructions_html . '</p>
			</div>

			' . $omnify_shipping_html . '

			<p style="font-size: 13px; color: #555; margin-top: 20px;">
				Please quote <strong>Order ' . esc_html($omnify_order['order_number'] ?: ($omnify_order['id'] ?? '')) . '</strong> as the payment reference.
			</p>

			<p style="font-size: 12px; color: #777; margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
				Questions? Reply to this email or visit your <a href="' . esc_url(home_url('/customer-portal/')) . '" style="color: #6366f1;">Customer Portal</a>.
			</p>
		</div>';

		return $this->send_logged('payment_pending', $omnify_to, $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id]);
	}

	public function send_abandoned_cart_recovery(array $omnify_cart): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_email = sanitize_email((string) ($omnify_cart['email'] ?? ''));
		if (! is_email($omnify_email)) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_abandoned_cart'])) {
			return false;
		}

		$omnify_payload      = is_array($omnify_cart['cart_payload'] ?? null) ? $omnify_cart['cart_payload'] : [];
		$omnify_product_name = (string) ($omnify_payload['product_name'] ?? __('your selected product', 'omnifywp-ecommerce'));
		$omnify_customer     = trim((string) (($omnify_cart['first_name'] ?? '') . ' ' . ($omnify_cart['last_name'] ?? '')));
		$omnify_currency     = strtoupper((string) ($omnify_cart['currency'] ?? 'USD'));
		$omnify_total        = $omnify_currency . ' ' . number_format((float) ($omnify_cart['subtotal'] ?? 0), 2);
		$omnify_recovery_url = esc_url_raw((string) ($omnify_cart['recovery_url'] ?? $omnify_cart['checkout_url'] ?? home_url('/')));
		$omnify_coupon_code  = (string) ($omnify_cart['coupon_code'] ?? '');

		$omnify_tokens = [
			'{customer_name}' => $omnify_customer ?: __('there', 'omnifywp-ecommerce'),
			'{customer_email}' => $omnify_email,
			'{product_name}' => $omnify_product_name,
			'{cart_total}' => $omnify_total,
			'{recovery_url}' => $omnify_recovery_url,
			'{coupon_code}' => $omnify_coupon_code,
		];

		$omnify_subject = strtr(
			(string) ($omnify_settings['email_abandoned_cart_subject'] ?? '[{site_name}] Complete your order'),
			array_merge(['{site_name}' => get_bloginfo('name')], $omnify_tokens)
		);
		$omnify_intro = strtr(
			(string) ($omnify_settings['email_abandoned_cart_intro'] ?? 'You left something in your cart. Use the button below to return to checkout and complete your order.'),
			array_merge(['{site_name}' => get_bloginfo('name')], $omnify_tokens)
		);
		$omnify_footer = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 24px;">' . esc_html__('Complete your order', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.7; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">' . esc_html__('Cart item', 'omnifywp-ecommerce') . '</p>
				<p style="margin: 0; font-size: 16px; font-weight: 500; color: #0f172a;">' . esc_html($omnify_product_name) . '</p>
				<p style="margin: 10px 0 0; font-size: 14px; color: #334155;">' . esc_html__('Estimated total:', 'omnifywp-ecommerce') . ' <strong>' . esc_html($omnify_total) . '</strong></p>
			</div>
			<p style="margin: 24px 0;">
				<a href="' . esc_url($omnify_recovery_url) . '" style="display: inline-block; background: #008060; color: #ffffff; text-decoration: none; font-weight: 500; padding: 12px 18px; border-radius: 7px;">' . esc_html__('Resume checkout', 'omnifywp-ecommerce') . '</a>
			</p>
			<p style="font-size: 12px; color: #64748b; line-height: 1.6;">' . esc_html__('This link restores your checkout details where possible.', 'omnifywp-ecommerce') . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e5e7eb; padding-top: 14px; margin-top: 24px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		return $this->send_logged('abandoned_cart', $omnify_email, $omnify_subject, $omnify_body, [], ['cart_id' => absint($omnify_cart['id'] ?? 0)]);
	}

	public function send_low_stock_alert(array $omnify_product, ?array $omnify_variation, int $omnify_old_qty, int $omnify_new_qty, int $omnify_threshold): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_low_stock'])) {
			return false;
		}

		$omnify_product_name = (string) ($omnify_product['name'] ?? __('Product', 'omnifywp-ecommerce'));
		$omnify_sku          = (string) ($omnify_variation['sku'] ?? $omnify_product['sku'] ?? '');
		$omnify_variation_label = '';
		if ($omnify_variation && ! empty($omnify_variation['attributes']) && is_array($omnify_variation['attributes'])) {
			$omnify_parts = [];
			foreach ($omnify_variation['attributes'] as $omnify_attribute_name => $omnify_attribute_value) {
				$omnify_parts[] = sprintf('%s: %s', (string) $omnify_attribute_name, (string) $omnify_attribute_value);
			}
			$omnify_variation_label = implode(', ', $omnify_parts);
		}

		$omnify_tokens = [
			'{site_name}' => get_bloginfo('name'),
			'{product_name}' => $omnify_product_name,
			'{variation}' => $omnify_variation_label,
			'{sku}' => $omnify_sku,
			'{stock_qty}' => (string) $omnify_new_qty,
			'{threshold}' => (string) $omnify_threshold,
		];

		$omnify_subject = strtr(
			(string) ($omnify_settings['email_low_stock_subject'] ?? '[{site_name}] Low stock alert: {product_name}'),
			$omnify_tokens
		);
		$omnify_to       = $this->admin_notification_recipients($omnify_settings);
		$omnify_edit_url = ! empty($omnify_product['id'])
			? admin_url('admin.php?page=omnify-products&action=edit&id=' . absint($omnify_product['id']))
			: admin_url('admin.php?page=omnify-products');

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 24px;">' . esc_html__('Low stock alert', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.7; margin: 0 0 18px;">' . esc_html__('A managed stock item has reached the configured low-stock threshold.', 'omnifywp-ecommerce') . '</p>
			<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin: 18px 0;">
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Product', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: 500; color: #0f172a;">' . esc_html($omnify_product_name) . '</td></tr>
				' . ($omnify_variation_label ? '<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Variation', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html($omnify_variation_label) . '</td></tr>' : '') . '
				' . ($omnify_sku ? '<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('SKU', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html($omnify_sku) . '</td></tr>' : '') . '
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Previous stock', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html((string) $omnify_old_qty) . '</td></tr>
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Current stock', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: 500; color: #b45309;">' . esc_html((string) $omnify_new_qty) . '</td></tr>
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Low-stock threshold', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html((string) $omnify_threshold) . '</td></tr>
			</table>
			<p style="margin: 24px 0 0;">
				<a href="' . esc_url($omnify_edit_url) . '" style="display: inline-block; background: #008060; color: #ffffff; text-decoration: none; font-weight: 500; padding: 12px 18px; border-radius: 7px;">' . esc_html__('Edit product', 'omnifywp-ecommerce') . '</a>
			</p>
		</div>';

		return $this->send_logged('low_stock', $omnify_to, $omnify_subject, $omnify_body, [], [
			'product_id' => absint($omnify_product['id'] ?? 0),
			'variation_id' => absint($omnify_variation['id'] ?? 0),
			'old_qty' => $omnify_old_qty,
			'new_qty' => $omnify_new_qty,
			'threshold' => $omnify_threshold,
		]);
	}

	public function send_refund_requested(int $omnify_order_id, string $omnify_reason = ''): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_refund_requested'])) {
			return false;
		}

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_refund_requested_subject'] ?? '[{site_name}] Refund requested for Order {order_id}'), $omnify_order);
		$omnify_message = $this->replace_tokens((string) ($omnify_settings['email_refund_requested_body'] ?? "Dear Customer,\n\nYour refund request for Order {order_id} has been received and is currently under review.\n\nReason: {refund_reason}\n\nWe will update you shortly."), $omnify_order, ['{refund_reason}' => $omnify_reason]);
		$omnify_body = $this->simple_email_body(__('Refund Request Under Review', 'omnifywp-ecommerce'), $omnify_message, $omnify_order);

		return $this->send_logged('refund_requested', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'reason' => $omnify_reason]);
	}

	public function send_refund_processed(int $omnify_order_id, float $omnify_amount): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_refund_processed'])) {
			return false;
		}

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_refund_processed_subject'] ?? '[{site_name}] Refund processed for Order {order_id}'), $omnify_order);
		$omnify_message = $this->replace_tokens((string) ($omnify_settings['email_refund_processed_body'] ?? "Dear Customer,\n\nYour refund of {refund_amount} for Order {order_id} has been processed successfully.\n\nThank you."), $omnify_order, ['{refund_amount}' => ($omnify_order['currency'] ?? '') . ' ' . number_format($omnify_amount, 2)]);
		$omnify_body = $this->simple_email_body(__('Refund Processed', 'omnifywp-ecommerce'), $omnify_message, $omnify_order);

		return $this->send_logged('refund_processed', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'amount' => $omnify_amount]);
	}

	public function send_status_changed(int $omnify_order_id, string $omnify_old_status, string $omnify_new_status): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_status_changed'])) {
			return false;
		}

		// Per-status toggles
		$omnify_status_key = 'email_order_' . sanitize_key($omnify_new_status);
		if (isset($omnify_settings[$omnify_status_key]) && empty($omnify_settings[$omnify_status_key])) {
			return false;
		}

		// Choose status-specific template if available
		$omnify_status_key = sanitize_key($omnify_new_status);
		$omnify_subj_key = 'email_order_' . $omnify_status_key . '_subject';
		$omnify_body_key = 'email_order_' . $omnify_status_key . '_body';
		$omnify_default_subj = $omnify_settings[$omnify_subj_key] ?? $omnify_settings['email_status_changed_subject'] ?? '[{site_name}] Order {order_id} status updated';
		$omnify_default_body = $omnify_settings[$omnify_body_key] ?? $omnify_settings['email_status_changed_body'] ?? 'Your order status changed from {old_status} to {new_status}.';

		$omnify_subject = $this->replace_tokens((string) $omnify_default_subj, $omnify_order, [
			'{old_status}' => ucwords(str_replace('_', ' ', $omnify_old_status)),
			'{new_status}' => ucwords(str_replace('_', ' ', $omnify_new_status)),
		]);
		$omnify_message = $this->replace_tokens(
			(string) $omnify_default_body,
			$omnify_order,
			[
				'{old_status}' => ucwords(str_replace('_', ' ', $omnify_old_status)),
				'{new_status}' => ucwords(str_replace('_', ' ', $omnify_new_status)),
			]
		);

		if (! empty($omnify_settings['email_status_include_details'])) {
			$omnify_symbol = $this->currency_symbol($omnify_order['currency'] ?? 'USD');
			$omnify_details = sprintf(
				"\n\nOrder #%s — Total: %s%s — Items: %d",
				$omnify_order['order_number'] ?: $omnify_order['id'],
				$omnify_symbol,
				number_format((float) ($omnify_order['total'] ?? 0), 2),
				count($omnify_order['items'] ?? [])
			);
			$omnify_message .= $omnify_details;
		}

		$omnify_customer_sent = $this->send_logged('order_status_changed', $omnify_order['customer_email'], $omnify_subject, $this->simple_email_body(__('Order Status Updated', 'omnifywp-ecommerce'), $omnify_message, $omnify_order), [], [
			'order_id' => $omnify_order_id,
			'old_status' => $omnify_old_status,
			'new_status' => $omnify_new_status,
		]);

		// Optionally notify admins too (re-use admin new order setting or a dedicated one)
		$omnify_settings = $this->omnify_settings();
		if (! empty($omnify_settings['email_admin_new_order']) || ! empty($omnify_settings['email_status_changed'])) {
			$omnify_admin_subject = $this->replace_tokens('[{site_name}] Order {order_id} status: {new_status}', $omnify_order, [
				'{old_status}' => ucwords(str_replace('_', ' ', $omnify_old_status)),
				'{new_status}' => ucwords(str_replace('_', ' ', $omnify_new_status)),
			]);
			$this->send_logged('order_status_admin', implode(',', $this->admin_notification_recipients($omnify_settings)), $omnify_admin_subject, $this->simple_email_body(__('Order Status Changed', 'omnifywp-ecommerce'), $omnify_message, $omnify_order), [], ['order_id' => $omnify_order_id]);
		}

		return $omnify_customer_sent;
	}

	/**
	 * Action handler: automatically trigger status email on order status changes.
	 * Signature of action: ($order_id, $new_status, $old_status)
	 */
	public function maybe_send_status_email(int $omnify_order_id, string $omnify_new_status, string $omnify_old_status = ''): void {
		// Guard against duplicate sends from admin handlers
		static $omnify_sent_for = [];
		$omnify_key = $omnify_order_id . '|' . $omnify_new_status;
		if (isset($omnify_sent_for[$omnify_key])) {
			return;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_status_changed'])) {
			return;
		}

		// Normalize old/new if caller passed differently
		if ($omnify_old_status === '' || $omnify_old_status === $omnify_new_status) {
			// Try to recover from order record
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order) {
				$omnify_old_status = (string) ($omnify_order['status'] ?? $omnify_new_status); // rough fallback
			}
		}

		$omnify_sent = $this->send_status_changed($omnify_order_id, $omnify_old_status ?: 'pending', $omnify_new_status);
		if ($omnify_sent) {
			$omnify_sent_for[$omnify_key] = true;
		}
	}

	/**
	 * Send a brief admin notification for a new order.
	 */
	public function send_admin_new_order(int $omnify_order_id): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_admin_new_order'])) {
			return false;
		}

		$omnify_to = $this->admin_notification_recipients($omnify_settings);
		$omnify_subject = $this->replace_tokens('[{site_name}] New Order {order_id}', $omnify_order);
		$omnify_symbol  = $this->currency_symbol($omnify_order['currency'] ?? 'USD');
		$omnify_message = sprintf(
			"New order received.\n\nOrder: %s\nTotal: %s%s\nCustomer: %s\nPayment: %s",
			$omnify_order['order_number'] ?: ('#' . ($omnify_order['id'] ?? '')),
			$omnify_symbol,
			number_format((float) ($omnify_order['total'] ?? 0), 2),
			$omnify_order['customer_email'] ?? '',
			ucwords(str_replace('_', ' ', (string) ($omnify_order['payment_method'] ?? 'unknown')))
		);

		$omnify_body = $this->simple_email_body(__('New Order Received', 'omnifywp-ecommerce'), $omnify_message, $omnify_order);

		return $this->send_logged('admin_new_order', $omnify_to, $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id]);
	}
}
