<?php
/**
 * Abandoned cart recovery scheduler.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;

class Omnify_Abandoned_Cart_Service {
	private const CRON_HOOK = 'omnify_abandoned_cart_recovery';

	public function __construct(
		private Omnify_Settings_Repository $omnify_settings,
		private Omnify_Abandoned_Cart_Repository $omnify_carts,
		private Omnify_Email_Service $omnify_emails
	) {}

	public function register(): void {
		add_action(self::CRON_HOOK, [$this, 'send_due_recoveries']);
		add_action('template_redirect', [$this, 'maybe_handle_unsubscribe']);
		$this->schedule();
	}

	public function maybe_handle_unsubscribe(): void {
		if (isset($_GET['omnify_action']) && 'unsubscribe_abandoned_cart' === $_GET['omnify_action'] && ! empty($_GET['token'])) {
			$omnify_token = sanitize_text_field(wp_unslash((string) $_GET['token']));
			$this->omnify_carts->mark_unsubscribed($omnify_token);
			wp_die(
				'<div style="text-align:center; padding:40px 20px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif; max-width:480px; margin:40px auto; background:#fff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">' .
				'<h2 style="color:#0f172a; margin-bottom:12px;">' . esc_html__('Unsubscribed', 'omnifywp-ecommerce') . '</h2>' .
				'<p style="color:#475569; font-size:15px; line-height:1.6; margin-bottom:24px;">' . esc_html__('You have been successfully unsubscribed from abandoned cart reminder emails.', 'omnifywp-ecommerce') . '</p>' .
				'<p><a href="' . esc_url(home_url('/')) . '" style="display:inline-block; background:#008060; color:#fff; text-decoration:none; padding:10px 20px; border-radius:6px; font-size:14px; font-weight:500;">' . esc_html__('Return to store', 'omnifywp-ecommerce') . '</a></p>' .
				'</div>',
				esc_html__('Unsubscribed', 'omnifywp-ecommerce'),
				['response' => 200, 'back_link' => false]
			);
		}
	}

	public function schedule(): void {
		if (! wp_next_scheduled(self::CRON_HOOK)) {
			wp_schedule_event(time() + (5 * MINUTE_IN_SECONDS), 'hourly', self::CRON_HOOK);
		}
	}

	public function unschedule(): void {
		$omnify_timestamp = wp_next_scheduled(self::CRON_HOOK);
		if ($omnify_timestamp) {
			wp_unschedule_event($omnify_timestamp, self::CRON_HOOK);
		}
	}

	public function send_due_recoveries(): void {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['abandoned_cart_enabled']) || empty($omnify_settings['email_abandoned_cart'])) {
			return;
		}

		$omnify_delay_minutes = max(5, absint($omnify_settings['abandoned_cart_delay_minutes'] ?? 60));
		$omnify_max_reminders = max(1, absint($omnify_settings['abandoned_cart_max_reminders'] ?? 1));

		foreach ($this->omnify_carts->due_for_recovery($omnify_delay_minutes, $omnify_max_reminders) as $omnify_cart) {
			if ($this->omnify_emails->send_abandoned_cart_recovery($omnify_cart)) {
				$this->omnify_carts->mark_sent((int) $omnify_cart['id']);
			}
		}
	}
}
