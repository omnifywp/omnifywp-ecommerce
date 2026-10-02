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
		$this->schedule();
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
