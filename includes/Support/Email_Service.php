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
use Omnify\eCommerce\Repositories\Omnify_Stock_Subscriber_Repository;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;

class Omnify_Email_Service {
	public function __construct(
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Product_Repository $omnify_products,
		private ?Omnify_Stock_Subscriber_Repository $omnify_stock_subscribers = null
	) {
		$this->omnify_stock_subscribers = $omnify_stock_subscribers ?: new Omnify_Stock_Subscriber_Repository(new \Omnify\eCommerce\Database\Omnify_Schema());
		// Register SMTP configuration for custom mailer (checked at send time)
		add_action('phpmailer_init', [$this, 'maybe_configure_smtp']);

		// Automatically send customer email when order status changes (if enabled in settings)
		add_action('omnify_order_status_changed', [$this, 'maybe_send_status_email'], 10, 3);
		add_action('omnify_order_refunded', [$this, 'on_order_refunded'], 10, 2);
		add_action('omnify_order_fulfilled', [$this, 'on_order_fulfilled'], 10, 4);
		add_action('omnify_customer_note_added', [$this, 'on_customer_note_added'], 10, 3);
		add_action('omnify_order_fraud_flagged', [$this, 'on_order_fraud_flagged'], 10, 4);
		add_action('omnify_customer_created', [$this, 'on_customer_created'], 10, 2);
		add_action('omnify_product_restocked', [$this, 'send_back_in_stock_alerts'], 10, 2);

		// Automated background retry for failed transactional emails
		add_action(self::RETRY_EMAILS_HOOK, [$this, 'process_failed_email_retries']);
		if (! wp_next_scheduled(self::RETRY_EMAILS_HOOK)) {
			wp_schedule_event(time() + 300, 'hourly', self::RETRY_EMAILS_HOOK);
		}
	}

	private const RETRY_EMAILS_HOOK = 'omnify_retry_failed_emails';

	public function on_order_refunded(int $omnify_order_id, float $omnify_amount): void {
		$this->send_refund_processed($omnify_order_id, $omnify_amount);
	}

	public function on_order_fulfilled(int $omnify_order_id, ?string $omnify_carrier = null, ?string $omnify_tracking = null, string $omnify_status = 'fulfilled'): void {
		if ('fulfilled' === $omnify_status) {
			$this->send_order_shipped($omnify_order_id, (string) ($omnify_carrier ?? ''), (string) ($omnify_tracking ?? ''));
		}
	}

	public function on_customer_note_added(int $omnify_order_id, string $omnify_note, int $omnify_note_id = 0): void {
		$this->send_customer_note($omnify_order_id, $omnify_note);
	}

	public function on_order_fraud_flagged(int $omnify_order_id, string $omnify_status, int $omnify_score, ?array $omnify_flags = null): void {
		$this->send_admin_fraud_alert($omnify_order_id, $omnify_status, $omnify_score, $omnify_flags ?? []);
	}

	public function on_customer_created(int $omnify_customer_id, array $omnify_data = []): void {
		$this->send_customer_welcome($omnify_customer_id, $omnify_data);
	}

	private function currency_symbol(string $omnify_currency): string {
		return Omnify_Locations::currency_symbol($omnify_currency);
	}

	public function format_price(float $omnify_amount, string $omnify_currency = 'USD'): string {
		$omnify_symbol = $this->currency_symbol($omnify_currency);
		$omnify_settings = $this->omnify_settings();
		$omnify_pos = (string) ($omnify_settings['currency_position'] ?? 'left');
		$omnify_decimals = (int) ($omnify_settings['currency_decimals'] ?? 2);
		$omnify_dec_point = (string) ($omnify_settings['decimal_separator'] ?? '.');
		$omnify_thousands = (string) ($omnify_settings['thousand_separator'] ?? ',');
		$omnify_formatted = number_format($omnify_amount, $omnify_decimals, $omnify_dec_point, $omnify_thousands);

		switch ($omnify_pos) {
			case 'left_space':
				return $omnify_symbol . ' ' . $omnify_formatted;
			case 'right':
				return $omnify_formatted . $omnify_symbol;
			case 'right_space':
				return $omnify_formatted . ' ' . $omnify_symbol;
			case 'left':
			default:
				return $omnify_symbol . $omnify_formatted;
		}
	}

	private function omnify_settings(): array {
		$omnify_settings = get_option('omnify_settings', []);

		return is_array($omnify_settings) ? $omnify_settings : [];
	}

	private function emails_enabled(): bool {
		$omnify_settings = $this->omnify_settings();
		return ! array_key_exists('email_notifications_enabled', $omnify_settings) || ! empty($omnify_settings['email_notifications_enabled']);
	}

	public function portal_url(?array $omnify_settings = null): string {
		$omnify_settings = $omnify_settings ?? $this->omnify_settings();
		$omnify_page_id  = absint($omnify_settings['page_customer_portal'] ?? 0);
		if ($omnify_page_id > 0) {
			$omnify_link = get_permalink($omnify_page_id);
			if ($omnify_link) {
				return $omnify_link;
			}
		}

		return home_url('/customer-portal/');
	}

	private function replace_tokens(string $omnify_content, array $omnify_order, array $omnify_extra = []): string {
		$omnify_first_name = trim((string) ($omnify_order['first_name'] ?? ($omnify_order['shipping_first_name'] ?? ($omnify_order['billing_first_name'] ?? ''))));
		$omnify_last_name  = trim((string) ($omnify_order['last_name'] ?? ($omnify_order['shipping_last_name'] ?? ($omnify_order['billing_last_name'] ?? ''))));
		$omnify_full_name  = trim($omnify_first_name . ' ' . $omnify_last_name);
		$omnify_order_num  = (string) ($omnify_order['order_number'] ?: ('#' . ($omnify_order['id'] ?? '')));

		$omnify_tokens = array_merge(
			[
				'{site_name}'           => get_bloginfo('name'),
				'{order_id}'            => $omnify_order_num,
				'{order_number}'        => $omnify_order_num,
				'{customer_name}'       => $omnify_full_name ?: __('Customer', 'omnifywp-ecommerce'),
				'{customer_first_name}' => $omnify_first_name ?: __('Customer', 'omnifywp-ecommerce'),
				'{first_name}'          => $omnify_first_name ?: __('Customer', 'omnifywp-ecommerce'),
				'{last_name}'           => $omnify_last_name,
				'{customer_email}'      => (string) ($omnify_order['customer_email'] ?? ''),
				'{order_total}'         => ($omnify_order['currency'] ?? '') . ' ' . number_format((float) ($omnify_order['total'] ?? 0), 2),
				'{order_status}'        => ucwords(str_replace('_', ' ', (string) ($omnify_order['status'] ?? ''))),
				'{customer_portal_url}' => $this->portal_url(),
				'{portal_url}'          => $this->portal_url(),
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

		if (! $this->email_logs_table_exists()) {
			return 0;
		}

		$omnify_table    = $wpdb->prefix . 'omnify_email_logs';
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

		$omnify_phpmailer_init = static function ($phpmailer): void {
			if (isset($phpmailer->Timeout) && $phpmailer->Timeout > 15) {
				$phpmailer->Timeout = 15;
			}
		};

		add_action('wp_mail_failed', $omnify_capture);
		add_action('phpmailer_init', $omnify_phpmailer_init);
		$omnify_sent = wp_mail($omnify_to, $omnify_subject, $omnify_body, $omnify_headers);
		remove_action('wp_mail_failed', $omnify_capture);
		remove_action('phpmailer_init', $omnify_phpmailer_init);

		$omnify_error_message = '';
		if (! $omnify_sent) {
			if ($omnify_failure && is_wp_error($omnify_failure)) {
				$omnify_error_message = $omnify_failure->get_error_message();
			}
			global $phpmailer;
			if (! $omnify_error_message && isset($phpmailer) && ! empty($phpmailer->ErrorInfo)) {
				$omnify_error_message = (string) $phpmailer->ErrorInfo;
			}
			if (! $omnify_error_message) {
				$omnify_error_message = __('WordPress mail returned false without a detailed error.', 'omnifywp-ecommerce');
			}
			set_transient('omnify_last_mail_error', $omnify_error_message, 60);
		} else {
			delete_transient('omnify_last_mail_error');
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

		if (! $this->email_logs_table_exists()) {
			return false;
		}

		$omnify_table = $wpdb->prefix . 'omnify_email_logs';
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

	/**
	 * Automatically process retries for transiently failed transactional emails.
	 */
	public function process_failed_email_retries(): void {
		if (! $this->email_logs_table_exists()) {
			return;
		}

		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_email_logs';
		$omnify_since = gmdate('Y-m-d H:i:s', time() - DAY_IN_SECONDS);

		// Find up to 10 failed transactional emails within the last 24 hours
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results(
			$wpdb,
			$wpdb->prepare(
				"SELECT * FROM {$omnify_table} WHERE status = 'failed' AND created_at >= %s ORDER BY id ASC LIMIT 10",
				$omnify_since
			),
			ARRAY_A
		);

		if (empty($omnify_rows) || ! is_array($omnify_rows)) {
			return;
		}

		foreach ($omnify_rows as $omnify_log) {
			$omnify_id = (int) ($omnify_log['id'] ?? 0);
			$omnify_context = json_decode((string) ($omnify_log['context'] ?? ''), true);
			if (! is_array($omnify_context)) {
				$omnify_context = [];
			}

			$omnify_retry_count = (int) ($omnify_context['retry_attempt'] ?? 0);
			if ($omnify_retry_count >= 3) {
				// Maximum retries reached; skip
				continue;
			}

			// Do not retry test emails
			if ('test_email' === ($omnify_log['trigger_name'] ?? '')) {
				continue;
			}

			$omnify_context['retry_attempt'] = $omnify_retry_count + 1;
			$omnify_headers = json_decode((string) ($omnify_log['headers'] ?? ''), true);
			if (! is_array($omnify_headers) || empty($omnify_headers)) {
				$omnify_headers = $this->default_headers();
			}

			$omnify_recipients = array_filter(array_map('sanitize_email', array_map('trim', explode(',', (string) $omnify_log['recipient']))));
			$omnify_to = count($omnify_recipients) > 1 ? array_values($omnify_recipients) : (string) $omnify_log['recipient'];

			$omnify_sent = $this->send_logged(
				(string) $omnify_log['trigger_name'],
				$omnify_to,
				(string) $omnify_log['subject'],
				(string) $omnify_log['body'],
				$omnify_headers,
				$omnify_context
			);

			if ($omnify_sent) {
				// Mark the original failed record as recovered
				\Omnify\eCommerce\Support\Omnify_DB::update(
					$wpdb,
					$omnify_table,
					[
						'status'        => 'recovered',
						'error_message' => sprintf(__('Recovered automatically on retry attempt %d', 'omnifywp-ecommerce'), $omnify_retry_count + 1),
					],
					['id' => $omnify_id]
				);
			} else {
				// Update context retry count on failed record
				\Omnify\eCommerce\Support\Omnify_DB::update(
					$wpdb,
					$omnify_table,
					[
						'context' => wp_json_encode($omnify_context),
					],
					['id' => $omnify_id]
				);
			}
		}
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
			$omnify_item_desc = esc_html($omnify_item['product_name']);
			if (! empty($omnify_item['item_meta'])) {
				$omnify_item_meta = is_string($omnify_item['item_meta']) ? json_decode($omnify_item['item_meta'], true) : $omnify_item['item_meta'];
				if (is_array($omnify_item_meta) && ! empty($omnify_item_meta)) {
					$omnify_meta_parts = [];
					foreach ($omnify_item_meta as $omnify_mk => $omnify_mv) {
						if (is_scalar($omnify_mv) && '' !== (string) $omnify_mv) {
							$omnify_meta_parts[] = esc_html(ucfirst(str_replace(['_', '-'], ' ', (string) $omnify_mk))) . ': ' . esc_html((string) $omnify_mv);
						}
					}
					if (! empty($omnify_meta_parts)) {
						$omnify_item_desc .= '<br><span style="font-size: 11px; color: #666;">' . implode(' &bull; ', $omnify_meta_parts) . '</span>';
					}
				}
			}

			$omnify_items_html .= sprintf(
				'<tr>
					<td style="padding: 10px; border-bottom: 1px solid #eee;">%s</td>
					<td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">%d</td>
					<td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">%s</td>
				</tr>',
				$omnify_item_desc,
				$omnify_item['quantity'],
				esc_html($omnify_symbol . number_format(($omnify_item['price'] * $omnify_item['quantity']) + (float) ($omnify_item['tax'] ?? 0), 2))
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
										<a href="%s" style="color: #0f766e; text-decoration: none; font-weight: bold;">Download File</a>
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
			<h2 style="color: #0f172a; border-bottom: 2px solid #0f766e; padding-bottom: 10px; margin-top: 0;">Order Receipt</h2>
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
				Access your account status anytime via the <a href="' . esc_url($this->portal_url()) . '" style="color: #0f766e;">Customer Portal</a>.
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
			$omnify_item_desc = esc_html($omnify_item['product_name']);
			if (! empty($omnify_item['item_meta'])) {
				$omnify_item_meta = is_string($omnify_item['item_meta']) ? json_decode($omnify_item['item_meta'], true) : $omnify_item['item_meta'];
				if (is_array($omnify_item_meta) && ! empty($omnify_item_meta)) {
					$omnify_meta_parts = [];
					foreach ($omnify_item_meta as $omnify_mk => $omnify_mv) {
						if (is_scalar($omnify_mv) && '' !== (string) $omnify_mv) {
							$omnify_meta_parts[] = esc_html(ucfirst(str_replace(['_', '-'], ' ', (string) $omnify_mk))) . ': ' . esc_html((string) $omnify_mv);
						}
					}
					if (! empty($omnify_meta_parts)) {
						$omnify_item_desc .= '<br><span style="font-size: 11px; color: #666;">' . implode(' &bull; ', $omnify_meta_parts) . '</span>';
					}
				}
			}

			$omnify_items_html .= sprintf(
				'<tr>
					<td style="padding: 8px 10px; border-bottom: 1px solid #eee;">%s</td>
					<td style="padding: 8px 10px; border-bottom: 1px solid #eee; text-align: right;">%s</td>
				</tr>',
				$omnify_item_desc,
				esc_html($omnify_symbol . number_format(($omnify_item['price'] * $omnify_item['quantity']) + (float) ($omnify_item['tax'] ?? 0), 2))
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
			<h2 style="color: #0f172a; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-top: 0;">Order Awaiting Payment</h2>
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
				Questions? Reply to this email or visit your <a href="' . esc_url($this->portal_url()) . '" style="color: #0f766e;">Customer Portal</a>.
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
		$omnify_product_name = (string) ($omnify_payload['product_name'] ?? '');
		if ('' === $omnify_product_name && ! empty($omnify_payload['items']) && is_array($omnify_payload['items'])) {
			$omnify_first_item = reset($omnify_payload['items']);
			$omnify_item_count = count($omnify_payload['items']);
			$omnify_first_name = (string) ($omnify_first_item['name'] ?? $omnify_first_item['product_name'] ?? __('Item', 'omnifywp-ecommerce'));
			$omnify_product_name = $omnify_item_count > 1
				// translators: 1: product name, 2: number of additional items.
				? sprintf(__('%1$s and %2$d other item(s)', 'omnifywp-ecommerce'), $omnify_first_name, $omnify_item_count - 1)
				: $omnify_first_name;
		}
		if ('' === $omnify_product_name) {
			$omnify_product_name = __('your selected products', 'omnifywp-ecommerce');
		}
		$omnify_customer     = trim((string) (($omnify_cart['first_name'] ?? '') . ' ' . ($omnify_cart['last_name'] ?? '')));
		$omnify_currency     = strtoupper((string) ($omnify_cart['currency'] ?? 'USD'));
		$omnify_total        = $omnify_currency . ' ' . number_format((float) ($omnify_cart['subtotal'] ?? 0), 2);
		$omnify_recovery_url = esc_url_raw((string) ($omnify_cart['recovery_url'] ?? $omnify_cart['checkout_url'] ?? home_url('/')));
		$omnify_coupon_code  = (string) ($omnify_cart['coupon_code'] ?? '');
		$omnify_incentive_code = trim((string) ($omnify_settings['abandoned_cart_coupon_code'] ?? ''));
		if (empty($omnify_coupon_code) && ! empty($omnify_incentive_code)) {
			$omnify_coupon_code = $omnify_incentive_code;
		}
		if (! empty($omnify_coupon_code)) {
			$omnify_recovery_url = add_query_arg(['coupon' => $omnify_coupon_code], $omnify_recovery_url);
		}
		$omnify_cart_token   = (string) ($omnify_cart['token'] ?? '');
		$omnify_reminder_count = (int) ($omnify_cart['reminder_count'] ?? 0);
		$omnify_stage          = $omnify_reminder_count + 1;

		$omnify_unsubscribe_url = esc_url_raw(
			add_query_arg(
				[
					'omnify_action' => 'unsubscribe_abandoned_cart',
					'token'         => $omnify_cart_token,
					'sig'           => hash_hmac('sha256', $omnify_cart_token, wp_salt('nonce')),
				],
				home_url('/')
			)
		);

		$omnify_tokens = [
			'{customer_name}'   => $omnify_customer ?: __('there', 'omnifywp-ecommerce'),
			'{customer_email}'  => $omnify_email,
			'{product_name}'    => $omnify_product_name,
			'{cart_total}'      => $omnify_total,
			'{recovery_url}'    => $omnify_recovery_url,
			'{coupon_code}'     => $omnify_coupon_code,
			'{unsubscribe_url}' => $omnify_unsubscribe_url,
			'{reminder_stage}'  => (string) $omnify_stage,
		];

		// Multi-stage abandoned cart copy customization (Item 26)
		$omnify_default_subject = '[{site_name}] Complete your order';
		$omnify_default_heading = __('Complete your order', 'omnifywp-ecommerce');
		if (2 === $omnify_stage) {
			$omnify_default_subject = '[{site_name}] Still thinking about it? Complete your order';
			$omnify_default_heading = __('We saved your items for you', 'omnifywp-ecommerce');
		} elseif ($omnify_stage >= 3) {
			$omnify_default_subject = '[{site_name}] Final reminder: Your cart is expiring soon';
			$omnify_default_heading = __('Final reminder: Don\'t miss out!', 'omnifywp-ecommerce');
		}

		$omnify_subject = strtr(
			(string) ($omnify_settings['email_abandoned_cart_subject'] ?? $omnify_default_subject),
			array_merge(['{site_name}' => get_bloginfo('name')], $omnify_tokens)
		);
		$omnify_intro = strtr(
			(string) ($omnify_settings['email_abandoned_cart_intro'] ?? 'You left something in your cart. Use the button below to return to checkout and complete your order.'),
			array_merge(['{site_name}' => get_bloginfo('name')], $omnify_tokens)
		);
		$omnify_footer = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 24px;">' . esc_html($omnify_default_heading) . '</h2>
			<p style="font-size: 15px; line-height: 1.7; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">' . esc_html__('Cart item', 'omnifywp-ecommerce') . '</p>
				<p style="margin: 0; font-size: 16px; font-weight: 500; color: #0f172a;">' . esc_html($omnify_product_name) . '</p>
				<p style="margin: 10px 0 0; font-size: 14px; color: #334155;">' . esc_html__('Estimated total:', 'omnifywp-ecommerce') . ' <strong>' . esc_html($omnify_total) . '</strong></p>
			</div>
			' . (! empty($omnify_coupon_code) ? '<div style="background: #ecfdf5; border: 1px dashed #10b981; border-radius: 8px; padding: 14px 16px; margin: 16px 0; text-align: center;">
				<p style="margin: 0 0 6px; font-size: 13px; color: #047857; font-weight: 600;">' . esc_html__('Special Discount Available for You', 'omnifywp-ecommerce') . '</p>
				<p style="margin: 0; font-size: 16px; color: #065f46; font-family: monospace; letter-spacing: 0.08em; font-weight: 700; background: #d1fae5; display: inline-block; padding: 4px 12px; border-radius: 4px;">' . esc_html($omnify_coupon_code) . '</p>
				<p style="margin: 6px 0 0; font-size: 12px; color: #059669;">' . esc_html__('Click the button below and this discount will be automatically applied at checkout.', 'omnifywp-ecommerce') . '</p>
			</div>' : '') . '
			<p style="margin: 24px 0;">
				<a href="' . esc_url($omnify_recovery_url) . '" style="display: inline-block; background: #008060; color: #ffffff; text-decoration: none; font-weight: 500; padding: 12px 18px; border-radius: 7px;">' . esc_html__('Resume checkout', 'omnifywp-ecommerce') . '</a>
			</p>
			<p style="font-size: 12px; color: #64748b; line-height: 1.6;">' . esc_html__('This link restores your checkout details where possible.', 'omnifywp-ecommerce') . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e5e7eb; padding-top: 14px; margin-top: 24px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
			<p style="font-size: 11px; color: #94a3b8; text-align: center; margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
				' . sprintf(
					/* translators: %s: Unsubscribe link HTML */
					esc_html__('No longer want to receive cart reminders? %s.', 'omnifywp-ecommerce'),
					'<a href="' . esc_url($omnify_unsubscribe_url) . '" style="color: #64748b; text-decoration: underline;">' . esc_html__('Unsubscribe here', 'omnifywp-ecommerce') . '</a>'
				) . '
			</p>
		</div>';

		// RFC 8058 compliant unsubscribe headers (Item 25)
		$omnify_headers = [
			'List-Unsubscribe: <' . $omnify_unsubscribe_url . '>',
			'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
		];

		return $this->send_logged('abandoned_cart', $omnify_email, $omnify_subject, $omnify_body, $omnify_headers, ['cart_id' => absint($omnify_cart['id'] ?? 0)]);
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

		$omnify_logged = $this->send_logged('low_stock', $omnify_to, $omnify_subject, $omnify_body, [], [
			'product_id' => absint($omnify_product['id'] ?? 0),
			'variation_id' => absint($omnify_variation['id'] ?? 0),
			'old_qty' => $omnify_old_qty,
			'new_qty' => $omnify_new_qty,
			'threshold' => $omnify_threshold,
		]);

		if ($omnify_new_qty <= 0 && ! empty($omnify_settings['email_out_of_stock'])) {
			$this->send_out_of_stock_alert($omnify_product, $omnify_variation, $omnify_old_qty);
		}

		return $omnify_logged;
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
		static $omnify_sent_refunds = [];
		$omnify_key = $omnify_order_id . '|' . number_format($omnify_amount, 2, '.', '');
		if (isset($omnify_sent_refunds[$omnify_key])) {
			return false;
		}
		$omnify_sent_refunds[$omnify_key] = true;

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

		$omnify_formatted_refund = ($omnify_order['currency'] ?? '') . ' ' . number_format($omnify_amount, 2);
		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_refund_processed_subject'] ?? '[{site_name}] Refund processed for Order {order_id}'), $omnify_order, ['{refund_amount}' => $omnify_formatted_refund]);
		$omnify_message = $this->replace_tokens((string) ($omnify_settings['email_refund_processed_body'] ?? "Dear Customer,\n\nYour refund of {refund_amount} for Order {order_id} has been processed successfully.\n\nThank you."), $omnify_order, ['{refund_amount}' => $omnify_formatted_refund]);
		$omnify_body = $this->simple_email_body(__('Refund Processed', 'omnifywp-ecommerce'), $omnify_message, $omnify_order);

		return $this->send_logged('refund_processed', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'amount' => $omnify_amount]);
	}

	public function send_status_changed(int $omnify_order_id, string $omnify_old_status, string $omnify_new_status): bool {
		static $omnify_sent_status = [];
		$omnify_key = $omnify_order_id . '|' . $omnify_new_status;
		if (isset($omnify_sent_status[$omnify_key])) {
			return false;
		}
		$omnify_sent_status[$omnify_key] = true;

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

		// If transitioning to refunded, send dedicated refund email when enabled
		if ('refunded' === $omnify_new_status && ! empty($omnify_settings['email_refund_processed'])) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order) {
				$omnify_refund_amount = (float) (($omnify_order['refunded_amount'] ?? 0) ?: ($omnify_order['total'] ?? 0));
				$omnify_refund_sent   = $this->send_refund_processed($omnify_order_id, $omnify_refund_amount);
				if ($omnify_refund_sent) {
					$omnify_sent_for[$omnify_key] = true;
					return;
				}
			}
		}

		// Dedicated triggers based on new status
		if ('shipped' === $omnify_new_status && ! empty($omnify_settings['email_order_shipped_dispatch'])) {
			$this->send_order_shipped($omnify_order_id);
		}
		if (in_array($omnify_new_status, ['ready_for_pickup', 'pickup_ready'], true) && ! empty($omnify_settings['email_order_ready_pickup'])) {
			$this->send_order_ready_pickup($omnify_order_id);
		}
		if ('cancelled' === $omnify_new_status && ! empty($omnify_settings['email_order_cancelled_notice'])) {
			$this->send_order_cancelled($omnify_order_id);
		}
		if ('failed' === $omnify_new_status && ! empty($omnify_settings['email_admin_failed_order'])) {
			$this->send_admin_failed_order($omnify_order_id);
		}

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

	/**
	 * Send an order shipped dispatch notification with tracking details.
	 */
	public function send_order_shipped(int $omnify_order_id, string $omnify_carrier = '', string $omnify_tracking = ''): bool {
		static $omnify_shipped_sent = [];
		if (isset($omnify_shipped_sent[$omnify_order_id])) {
			return false;
		}

		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_order_shipped_dispatch'])) {
			return false;
		}

		$omnify_carrier  = $omnify_carrier ?: (string) ($omnify_order['tracking_carrier'] ?? '');
		$omnify_tracking = $omnify_tracking ?: (string) ($omnify_order['tracking_number'] ?? '');
		$omnify_tracking_page = $this->portal_url($omnify_settings);
		if (! empty($omnify_settings['page_order_tracking'])) {
			$omnify_p = get_permalink((int) $omnify_settings['page_order_tracking']);
			if ($omnify_p) {
				$omnify_tracking_page = $omnify_p;
			}
		}
		$omnify_carrier_url = '';
		if ($omnify_tracking) {
			$omnify_carrier_url = \Omnify\eCommerce\Support\Tracking_Helper::get_tracking_url($omnify_carrier, $omnify_tracking);
		}

		$omnify_extra = [
			'{tracking_carrier}'     => $omnify_carrier ?: __('Standard Carrier', 'omnifywp-ecommerce'),
			'{tracking_number}'      => $omnify_tracking ?: __('Available on Request', 'omnifywp-ecommerce'),
			'{tracking_url}'         => esc_url($omnify_carrier_url ?: $omnify_track_url),
			'{carrier_tracking_url}' => esc_url($omnify_carrier_url),
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_order_shipped_dispatch_subject'] ?? '[{site_name}] Your Order {order_id} Has Been Shipped!'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_order_shipped_dispatch_intro'] ?? 'Great news! Your order is on its way. You can track your shipment using the tracking details below.'), $omnify_order, $omnify_extra);
		$omnify_footer  = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_carrier_label  = esc_html($omnify_carrier ?: __('Standard Carrier', 'omnifywp-ecommerce'));
		$omnify_tracking_label = esc_html($omnify_tracking ?: __('Track via Portal', 'omnifywp-ecommerce'));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #0284c7; padding-bottom: 10px;">' . esc_html__('Your Order Has Shipped', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>
			
			<div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
					<span style="font-size: 12px; color: #0369a1; font-weight: 700; text-transform: uppercase;">' . esc_html__('Shipping Carrier', 'omnifywp-ecommerce') . '</span>
					<span style="font-size: 14px; font-weight: 600; color: #0f172a;">' . $omnify_carrier_label . '</span>
				</div>
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<span style="font-size: 12px; color: #0369a1; font-weight: 700; text-transform: uppercase;">' . esc_html__('Tracking Number', 'omnifywp-ecommerce') . '</span>
					<code style="background: #e0f2fe; padding: 3px 8px; border-radius: 4px; font-size: 13px; color: #0284c7; font-weight: 600;">' . $omnify_tracking_label . '</code>
				</div>
			</div>

			<p style="margin: 22px 0; text-align: center;">
				<a href="' . esc_url($omnify_track_url) . '" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 7px; font-size: 14px;">' . esc_html__('Track Your Order', 'omnifywp-ecommerce') . '</a>
			</p>

			<p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 20px;">' . sprintf(esc_html__('Order #%s • Placed on %s', 'omnifywp-ecommerce'), esc_html($omnify_order['order_number'] ?: $omnify_order['id']), wp_date('Y-m-d', strtotime($omnify_order['created_at'] ?? 'now'))) . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		$omnify_sent = $this->send_logged('order_shipped', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], [
			'order_id'        => $omnify_order_id,
			'carrier'         => $omnify_carrier,
			'tracking_number' => $omnify_tracking,
		]);

		if ($omnify_sent) {
			$omnify_shipped_sent[$omnify_order_id] = true;
		}

		return $omnify_sent;
	}

	/**
	 * Send an order ready for store pickup notification.
	 */
	public function send_order_ready_pickup(int $omnify_order_id): bool {
		static $omnify_pickup_sent = [];
		if (isset($omnify_pickup_sent[$omnify_order_id])) {
			return false;
		}

		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_order_ready_pickup'])) {
			return false;
		}

		$omnify_store_name    = (string) ($omnify_settings['store_name'] ?? get_bloginfo('name'));
		$omnify_store_address = (string) ($omnify_settings['store_address'] ?? '');
		$omnify_store_phone   = (string) ($omnify_settings['store_phone'] ?? '');

		$omnify_extra = [
			'{store_name}'    => $omnify_store_name,
			'{store_address}' => $omnify_store_address ?: __('See store website for address', 'omnifywp-ecommerce'),
			'{store_phone}'   => $omnify_store_phone ?: __('See store contact page', 'omnifywp-ecommerce'),
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_order_ready_pickup_subject'] ?? '[{site_name}] Your Order {order_id} is Ready for Pickup!'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_order_ready_pickup_intro'] ?? 'Your order is packed and ready for collection at our store. Please bring your order confirmation when picking it up.'), $omnify_order, $omnify_extra);
		$omnify_footer  = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #0d9488; padding-bottom: 10px;">' . esc_html__('Ready for Store Pickup', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>

			<div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<h3 style="font-size: 14px; margin: 0 0 8px; color: #0f766e;">' . esc_html__('Pickup Location & Details', 'omnifywp-ecommerce') . '</h3>
				<p style="font-size: 13.5px; line-height: 1.6; margin: 0; color: #134e4a;">
					<strong>' . esc_html($omnify_store_name) . '</strong><br />
					' . ($omnify_store_address ? nl2br(esc_html($omnify_store_address)) . '<br />' : '') . '
					' . ($omnify_store_phone ? esc_html__('Phone:', 'omnifywp-ecommerce') . ' ' . esc_html($omnify_store_phone) : '') . '
				</p>
			</div>

			<p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 20px;">' . sprintf(esc_html__('Order #%s • Total: %s', 'omnifywp-ecommerce'), esc_html($omnify_order['order_number'] ?: $omnify_order['id']), esc_html(($omnify_order['currency'] ?? '') . ' ' . number_format((float) ($omnify_order['total'] ?? 0), 2))) . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		$omnify_sent = $this->send_logged('order_ready_pickup', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id]);

		if ($omnify_sent) {
			$omnify_pickup_sent[$omnify_order_id] = true;
		}

		return $omnify_sent;
	}

	/**
	 * Send an order cancelled notification.
	 */
	public function send_order_cancelled(int $omnify_order_id, string $omnify_reason = ''): bool {
		static $omnify_cancelled_sent = [];
		if (isset($omnify_cancelled_sent[$omnify_order_id])) {
			return false;
		}

		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_order_cancelled_notice'])) {
			return false;
		}

		$omnify_extra = [
			'{cancellation_reason}' => $omnify_reason ?: __('Order cancelled by customer or store administrator', 'omnifywp-ecommerce'),
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_order_cancelled_notice_subject'] ?? '[{site_name}] Order {order_id} Cancelled'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_order_cancelled_notice_intro'] ?? 'Your order {order_id} has been cancelled. If this was unexpected or if you need assistance, please contact our support team.'), $omnify_order, $omnify_extra);
		$omnify_footer  = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
			<h2 style="color: #991b1b; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #ef4444; padding-bottom: 10px;">' . esc_html__('Order Cancelled', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>

			' . ($omnify_reason ? '<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px; margin: 16px 0; font-size: 13.5px; color: #991b1b;"><strong>' . esc_html__('Reason:', 'omnifywp-ecommerce') . '</strong> ' . esc_html($omnify_reason) . '</div>' : '') . '

			<p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 20px;">' . sprintf(esc_html__('Order #%s • Total: %s', 'omnifywp-ecommerce'), esc_html($omnify_order['order_number'] ?: $omnify_order['id']), esc_html(($omnify_order['currency'] ?? '') . ' ' . number_format((float) ($omnify_order['total'] ?? 0), 2))) . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		$omnify_sent = $this->send_logged('order_cancelled', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'reason' => $omnify_reason]);

		if ($omnify_sent) {
			$omnify_cancelled_sent[$omnify_order_id] = true;
		}

		return $omnify_sent;
	}

	/**
	 * Send a customer note notification when merchant adds an order note.
	 */
	public function send_customer_note(int $omnify_order_id, string $omnify_note): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_customer_note'])) {
			return false;
		}

		$omnify_extra = [
			'{order_note}' => wp_kses_post($omnify_note),
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_customer_note_subject'] ?? '[{site_name}] Note Added to Order {order_id}'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_customer_note_intro'] ?? 'A new update or note has been added to your order by our support team.'), $omnify_order, $omnify_extra);
		$omnify_footer  = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #0f766e; padding-bottom: 10px;">' . esc_html__('Update Regarding Your Order', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>

			<div style="background: #f8fafc; border-left: 4px solid #0f766e; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #1e293b; line-height: 1.6;">
				' . nl2br(wp_kses_post($omnify_note)) . '
			</div>

			<p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 20px;">' . sprintf(esc_html__('Order #%s', 'omnifywp-ecommerce'), esc_html($omnify_order['order_number'] ?: $omnify_order['id'])) . '</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		return $this->send_logged('customer_note', $omnify_order['customer_email'], $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'note' => $omnify_note]);
	}

	/**
	 * Send a welcome email when a new customer account is created.
	 */
	public function send_customer_welcome(int $omnify_customer_id, array $omnify_data = []): bool {
		if (! $this->emails_enabled()) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_customer_welcome'])) {
			return false;
		}

		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		if (! is_email($omnify_email)) {
			return false;
		}

		$omnify_name = trim((string) (($omnify_data['first_name'] ?? '') . ' ' . ($omnify_data['last_name'] ?? '')));
		$omnify_portal_url = $this->portal_url($omnify_settings);
		$omnify_login_url  = wp_login_url($omnify_portal_url);

		$omnify_tokens = [
			'{site_name}'           => get_bloginfo('name'),
			'{customer_name}'       => $omnify_name ?: __('Customer', 'omnifywp-ecommerce'),
			'{customer_email}'      => $omnify_email,
			'{customer_portal_url}' => esc_url($omnify_portal_url),
			'{portal_url}'          => esc_url($omnify_portal_url),
			'{login_url}'           => esc_url($omnify_login_url),
		];

		$omnify_subject = strtr((string) ($omnify_settings['email_customer_welcome_subject'] ?? 'Welcome to {site_name}! Your account is ready'), $omnify_tokens);
		$omnify_intro   = strtr((string) ($omnify_settings['email_customer_welcome_intro'] ?? 'Welcome! Your customer account has been created. You can log in anytime to view your past orders, manage your profile, and download digital purchases.'), $omnify_tokens);
		$omnify_footer  = trim((string) ($omnify_settings['email_footer_text'] ?? ''));

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
			<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">' . esc_html__('Welcome to Our Store', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px;">' . nl2br(esc_html($omnify_intro)) . '</p>

			<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<h3 style="font-size: 14px; margin: 0 0 8px; color: #1e40af;">' . esc_html__('Account Details', 'omnifywp-ecommerce') . '</h3>
				<p style="font-size: 13.5px; line-height: 1.6; margin: 0; color: #1e3a8a;">
					<strong>' . esc_html__('Email:', 'omnifywp-ecommerce') . '</strong> ' . esc_html($omnify_email) . '
				</p>
			</div>

			<p style="margin: 22px 0; text-align: center;">
				<a href="' . esc_url($omnify_portal_url) . '" style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 7px; font-size: 14px;">' . esc_html__('Visit Customer Portal', 'omnifywp-ecommerce') . '</a>
			</p>
			' . ($omnify_footer ? '<p style="font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px;">' . nl2br(esc_html($omnify_footer)) . '</p>' : '') . '
		</div>';

		return $this->send_logged('customer_welcome', $omnify_email, $omnify_subject, $omnify_body, [], ['customer_id' => $omnify_customer_id]);
	}

	/**
	 * Send an admin notification when customer payment fails or is declined.
	 */
	public function send_admin_failed_order(int $omnify_order_id, string $omnify_reason = ''): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_admin_failed_order'])) {
			return false;
		}

		$omnify_extra = [
			'{failure_reason}' => $omnify_reason ?: __('Payment gateway declined or checkout error', 'omnifywp-ecommerce'),
			'{payment_method}' => ucwords(str_replace('_', ' ', (string) ($omnify_order['payment_method'] ?? 'unknown'))),
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_admin_failed_order_subject'] ?? '[{site_name}] Payment Failed for Order {order_id}'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_admin_failed_order_intro'] ?? 'A customer payment attempt failed or was declined at checkout. Review the details below to follow up.'), $omnify_order, $omnify_extra);

		$omnify_message = sprintf(
			"%s\n\nOrder: %s\nCustomer: %s (%s)\nTotal: %s\nPayment Method: %s\nReason: %s",
			$omnify_intro,
			$omnify_order['order_number'] ?: ('#' . ($omnify_order['id'] ?? '')),
			trim(($omnify_order['first_name'] ?? '') . ' ' . ($omnify_order['last_name'] ?? '')),
			$omnify_order['customer_email'] ?? '',
			($omnify_order['currency'] ?? '') . ' ' . number_format((float) ($omnify_order['total'] ?? 0), 2),
			ucwords(str_replace('_', ' ', (string) ($omnify_order['payment_method'] ?? 'unknown'))),
			$omnify_reason ?: __('Gateway declined transaction', 'omnifywp-ecommerce')
		);

		$omnify_body = $this->simple_email_body(__('Payment Failed Notification', 'omnifywp-ecommerce'), $omnify_message, $omnify_order);

		return $this->send_logged('admin_failed_order', $this->admin_notification_recipients($omnify_settings), $omnify_subject, $omnify_body, [], ['order_id' => $omnify_order_id, 'reason' => $omnify_reason]);
	}

	/**
	 * Send an urgent admin notification when stock reaches 0 units.
	 */
	public function send_out_of_stock_alert(array $omnify_product, ?array $omnify_variation = null, int $omnify_old_qty = 0): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_out_of_stock'])) {
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
			'{site_name}'    => get_bloginfo('name'),
			'{product_name}' => $omnify_product_name,
			'{variation}'    => $omnify_variation_label,
			'{sku}'          => $omnify_sku,
			'{restock_url}'  => ! empty($omnify_product['id']) ? admin_url('admin.php?page=omnify-products&action=edit&id=' . absint($omnify_product['id'])) : admin_url('admin.php?page=omnify-products'),
		];

		$omnify_subject = strtr((string) ($omnify_settings['email_out_of_stock_subject'] ?? '[{site_name}] CRITICAL: Out of Stock Alert for {product_name}'), $omnify_tokens);
		$omnify_to      = $this->admin_notification_recipients($omnify_settings);
		$omnify_edit_url = $omnify_tokens['restock_url'];

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #fecaca; border-radius: 10px;">
			<h2 style="color: #991b1b; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #ef4444; padding-bottom: 10px;">' . esc_html__('Out of Stock Alert (0 Units Remaining)', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px; color: #7f1d1d;"><strong>' . esc_html__('Critical Notice:', 'omnifywp-ecommerce') . '</strong> ' . esc_html__('The inventory for this item has been completely depleted and reached zero stock.', 'omnifywp-ecommerce') . '</p>
			<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin: 18px 0;">
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Product', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #0f172a;">' . esc_html($omnify_product_name) . '</td></tr>
				' . ($omnify_variation_label ? '<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Variation', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html($omnify_variation_label) . '</td></tr>' : '') . '
				' . ($omnify_sku ? '<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('SKU', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html($omnify_sku) . '</td></tr>' : '') . '
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Previous stock', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . esc_html((string) $omnify_old_qty) . '</td></tr>
				<tr><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #64748b;">' . esc_html__('Current stock', 'omnifywp-ecommerce') . '</td><td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: 700; color: #dc2626;">0 (' . esc_html__('Sold Out', 'omnifywp-ecommerce') . ')</td></tr>
			</table>
			<p style="margin: 24px 0 0;">
				<a href="' . esc_url($omnify_edit_url) . '" style="display: inline-block; background: #dc2626; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 20px; border-radius: 7px;">' . esc_html__('Restock Product', 'omnifywp-ecommerce') . '</a>
			</p>
		</div>';

		return $this->send_logged('out_of_stock', $omnify_to, $omnify_subject, $omnify_body, [], [
			'product_id'   => absint($omnify_product['id'] ?? 0),
			'variation_id' => absint($omnify_variation['id'] ?? 0),
			'old_qty'      => $omnify_old_qty,
			'new_qty'      => 0,
		]);
	}

	/**
	 * Send back-in-stock notification emails to subscribers when an item is restocked.
	 */
	public function send_back_in_stock_alerts(int $omnify_product_id, ?int $omnify_variation_id = null): int {
		if (! $this->emails_enabled()) {
			return 0;
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return 0;
		}

		$omnify_subscribers = $this->omnify_stock_subscribers->get_pending_subscribers($omnify_product_id, $omnify_variation_id);
		if (empty($omnify_subscribers)) {
			return 0;
		}

		$omnify_product_name = (string) ($omnify_product['name'] ?? __('Product', 'omnifywp-ecommerce'));
		$omnify_product_url  = ! empty($omnify_product['slug']) ? home_url('/?omnify_product=' . rawurlencode($omnify_product['slug'])) : home_url('/');
		$omnify_site_name    = get_bloginfo('name');
		$omnify_subject      = sprintf(__('[%1$s] Back in Stock: %2$s is now available!', 'omnifywp-ecommerce'), $omnify_site_name, $omnify_product_name);

		$omnify_sent_ids = [];
		foreach ($omnify_subscribers as $omnify_sub) {
			$omnify_to = sanitize_email((string) $omnify_sub['email']);
			if (! is_email($omnify_to)) {
				continue;
			}

			$omnify_body = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 32px 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
				<div style="margin-bottom: 20px;">
					<span style="display: inline-block; background: #ecfdf5; color: #047857; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase;">' . esc_html__('Back in Stock', 'omnifywp-ecommerce') . '</span>
				</div>
				<h2 style="color: #0f172a; margin: 0 0 12px; font-size: 22px; font-weight: 700;">' . sprintf(esc_html__('Good news! %s is back in stock.', 'omnifywp-ecommerce'), esc_html($omnify_product_name)) . '</h2>
				<p style="font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 24px;">' . esc_html__('You requested to be notified when this item was restocked. Inventory is now available, but supplies may be limited.', 'omnifywp-ecommerce') . '</p>
				<p style="margin: 0 0 28px;">
					<a href="' . esc_url($omnify_product_url) . '" style="display: inline-block; background: #008060; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 12px 24px; border-radius: 8px;">' . esc_html__('Order Now &rarr;', 'omnifywp-ecommerce') . '</a>
				</p>
				<hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;" />
				<p style="font-size: 12px; color: #94a3b8; margin: 0;">' . sprintf(esc_html__('You received this email because you subscribed to stock updates on %s.', 'omnifywp-ecommerce'), esc_html($omnify_site_name)) . '</p>
			</div>';

			$this->send_logged('customer_back_in_stock', $omnify_to, $omnify_subject, $omnify_body, [], ['product_id' => $omnify_product_id]);
			$omnify_sent_ids[] = (int) $omnify_sub['id'];
		}

		if (! empty($omnify_sent_ids)) {
			$this->omnify_stock_subscribers->mark_notified($omnify_sent_ids);
		}

		return count($omnify_sent_ids);
	}

	/**
	 * Send an admin security warning for high-risk / fraud flagged orders.
	 */
	public function send_admin_fraud_alert(int $omnify_order_id, string $omnify_status, int $omnify_score, array $omnify_flags = []): bool {
		if (! $this->emails_enabled()) {
			return false;
		}
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings();
		if (empty($omnify_settings['email_admin_fraud_alert'])) {
			return false;
		}

		$omnify_flags_text = ! empty($omnify_flags) ? implode(', ', array_map('esc_html', (array) $omnify_flags)) : __('Risk rules exceeded threshold', 'omnifywp-ecommerce');

		$omnify_extra = [
			'{fraud_score}'   => (string) $omnify_score,
			'{fraud_reasons}' => $omnify_flags_text,
		];

		$omnify_subject = $this->replace_tokens((string) ($omnify_settings['email_admin_fraud_alert_subject'] ?? '[{site_name}] High-Risk Order Detected: {order_id}'), $omnify_order, $omnify_extra);
		$omnify_intro   = $this->replace_tokens((string) ($omnify_settings['email_admin_fraud_alert_intro'] ?? 'Security Alert: Order {order_id} has triggered automated fraud flags or exceeded security risk thresholds.'), $omnify_order, $omnify_extra);

		$omnify_order_admin_url = admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id);

		$omnify_body = '<div style="font-family: Helvetica, Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #fed7aa; border-radius: 10px;">
			<h2 style="color: #9a3412; margin: 0 0 12px; font-size: 22px; border-bottom: 2px solid #f97316; padding-bottom: 10px;">' . esc_html__('High-Risk Order Warning', 'omnifywp-ecommerce') . '</h2>
			<p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px; color: #7c2d12;">' . nl2br(esc_html($omnify_intro)) . '</p>

			<div style="background: #fff7ed; border: 1px solid #ffedd5; border-radius: 8px; padding: 16px; margin: 18px 0;">
				<p style="margin: 0 0 8px; font-size: 13.5px;"><strong>' . esc_html__('Order Number:', 'omnifywp-ecommerce') . '</strong> ' . esc_html($omnify_order['order_number'] ?: $omnify_order['id']) . '</p>
				<p style="margin: 0 0 8px; font-size: 13.5px;"><strong>' . esc_html__('Customer:', 'omnifywp-ecommerce') . '</strong> ' . esc_html($omnify_order['customer_email']) . '</p>
				<p style="margin: 0 0 8px; font-size: 13.5px;"><strong>' . esc_html__('Risk Score:', 'omnifywp-ecommerce') . '</strong> <span style="color: #c2410c; font-weight: 700;">' . esc_html((string) $omnify_score) . '/100</span></p>
				<p style="margin: 0; font-size: 13.5px;"><strong>' . esc_html__('Flagged Triggers:', 'omnifywp-ecommerce') . '</strong> ' . esc_html($omnify_flags_text) . '</p>
			</div>

			<p style="margin: 22px 0;">
				<a href="' . esc_url($omnify_order_admin_url) . '" style="display: inline-block; background: #c2410c; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 22px; border-radius: 7px; font-size: 14px;">' . esc_html__('Review Order in Admin', 'omnifywp-ecommerce') . '</a>
			</p>
		</div>';

		return $this->send_logged('admin_fraud_alert', $this->admin_notification_recipients($omnify_settings), $omnify_subject, $omnify_body, [], [
			'order_id'    => $omnify_order_id,
			'fraud_score' => $omnify_score,
			'status'      => $omnify_status,
		]);
	}

	private function email_logs_table_exists(): bool {
		static $omnify_exists = null;
		if (null !== $omnify_exists) {
			return $omnify_exists;
		}

		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_email_logs';
		$omnify_cached = wp_cache_get('omnify_email_logs_table_exists', 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			$omnify_exists = (bool) $omnify_cached;
			return $omnify_exists;
		}

		$omnify_exists = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) === $omnify_table;
		wp_cache_set('omnify_email_logs_table_exists', $omnify_exists ? 1 : 0, 'omnifywp-ecommerce', DAY_IN_SECONDS);

		return $omnify_exists;
	}
}
