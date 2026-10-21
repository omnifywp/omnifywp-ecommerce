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
	private const CRON_PRUNE_HOOK = 'omnify_abandoned_cart_cleanup';

	public function __construct(
		private Omnify_Settings_Repository $omnify_settings,
		private Omnify_Abandoned_Cart_Repository $omnify_carts,
		private Omnify_Email_Service $omnify_emails
	) {}

	public function register(): void {
		add_action(self::CRON_HOOK, [$this, 'send_due_recoveries']);
		add_action(self::CRON_PRUNE_HOOK, [$this, 'prune_stale_carts']);
		add_action('template_redirect', [$this, 'maybe_handle_unsubscribe']);
		$this->schedule();
	}

	public function maybe_handle_unsubscribe(): void {
		if (isset($_GET['omnify_action']) && 'unsubscribe_abandoned_cart' === $_GET['omnify_action'] && ! empty($_GET['token'])) {
			$omnify_token = sanitize_text_field(wp_unslash((string) $_GET['token']));
			$omnify_sig   = sanitize_text_field(wp_unslash((string) ($_GET['sig'] ?? '')));

			// Require a valid cryptographic signature before acting on the request
			$omnify_expected_sig = hash_hmac('sha256', $omnify_token, wp_salt('nonce'));
			if (empty($omnify_sig) || ! hash_equals($omnify_expected_sig, $omnify_sig)) {
				wp_die(
					esc_html__('Invalid or forged unsubscribe verification signature.', 'omnifywp-ecommerce'),
					esc_html__('Security Verification Failed', 'omnifywp-ecommerce'),
					['response' => 403, 'back_link' => true]
				);
			}

			$this->omnify_carts->mark_unsubscribed($omnify_token);
			$omnify_store_name = get_bloginfo('name');
			$omnify_store_url  = home_url('/');
			status_header(200);
			nocache_headers();
			header('Content-Type: text/html; charset=utf-8');
			?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Unsubscribed', 'omnifywp-ecommerce'); ?> &ndash; <?php echo esc_html($omnify_store_name); ?></title>
	<style>
		body {
			margin: 0;
			padding: 0;
			background: #f8fafc;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			color: #334155;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			box-sizing: border-box;
		}
		.omnify-unsub-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			max-width: 480px;
			width: 90%;
			padding: 40px 32px;
			text-align: center;
			box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
		}
		.omnify-unsub-icon {
			width: 54px;
			height: 54px;
			background: #f1f5f9;
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 20px;
			color: #0f172a;
		}
		.omnify-unsub-card h1 {
			margin: 0 0 12px;
			font-size: 22px;
			font-weight: 700;
			color: #0f172a;
		}
		.omnify-unsub-card p {
			margin: 0 0 24px;
			font-size: 15px;
			line-height: 1.6;
			color: #64748b;
		}
		.omnify-unsub-btn {
			display: inline-block;
			background: #0f172a;
			color: #ffffff;
			text-decoration: none;
			padding: 12px 28px;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			transition: background 0.2s ease;
		}
		.omnify-unsub-btn:hover {
			background: #334155;
		}
		.omnify-unsub-footer {
			margin-top: 24px;
			font-size: 12px;
			color: #94a3b8;
		}
	</style>
</head>
<body>
	<div class="omnify-unsub-card">
		<div class="omnify-unsub-icon">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
		</div>
		<h1><?php esc_html_e('You have been unsubscribed', 'omnifywp-ecommerce'); ?></h1>
		<p><?php printf(esc_html__('You will no longer receive cart reminder emails from %s.', 'omnifywp-ecommerce'), esc_html($omnify_store_name)); ?></p>
		<a class="omnify-unsub-btn" href="<?php echo esc_url($omnify_store_url); ?>"><?php esc_html_e('Return to Store', 'omnifywp-ecommerce'); ?></a>
		<div class="omnify-unsub-footer"><?php echo esc_html($omnify_store_name); ?></div>
	</div>
</body>
</html>
			<?php
			exit;
		}
	}

	public function schedule(): void {
		if (false !== get_transient('omnify_cart_cron_checked')) {
			return;
		}

		if (! wp_next_scheduled(self::CRON_HOOK)) {
			wp_schedule_event(time() + (5 * MINUTE_IN_SECONDS), 'hourly', self::CRON_HOOK);
		}

		if (! wp_next_scheduled(self::CRON_PRUNE_HOOK)) {
			wp_schedule_event(time() + (10 * MINUTE_IN_SECONDS), 'daily', self::CRON_PRUNE_HOOK);
		}

		set_transient('omnify_cart_cron_checked', 1, DAY_IN_SECONDS);
	}

	public function unschedule(): void {
		delete_transient('omnify_cart_cron_checked');
		$omnify_timestamp = wp_next_scheduled(self::CRON_HOOK);
		if ($omnify_timestamp) {
			wp_unschedule_event($omnify_timestamp, self::CRON_HOOK);
		}
		$omnify_prune_timestamp = wp_next_scheduled(self::CRON_PRUNE_HOOK);
		if ($omnify_prune_timestamp) {
			wp_unschedule_event($omnify_prune_timestamp, self::CRON_PRUNE_HOOK);
		}
	}

	public function send_due_recoveries(): void {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['abandoned_cart_enabled']) || empty($omnify_settings['email_abandoned_cart'])) {
			return;
		}

		$omnify_delay_minutes = max(5, absint($omnify_settings['abandoned_cart_delay_minutes'] ?? 60));
		$omnify_max_reminders = max(1, absint($omnify_settings['abandoned_cart_max_reminders'] ?? 3));

		foreach ($this->omnify_carts->due_for_recovery($omnify_delay_minutes, $omnify_max_reminders) as $omnify_cart) {
			if (empty($omnify_cart['coupon_code']) && ! empty($omnify_settings['abandoned_cart_generate_coupon'])) {
				$omnify_pct = max(1, absint($omnify_settings['abandoned_cart_coupon_discount'] ?? 10));
				$omnify_exp_days = max(1, absint($omnify_settings['abandoned_cart_coupon_expiry_days'] ?? 3));
				$omnify_gen_coupon = $this->generate_recovery_coupon((string) $omnify_cart['email'], $omnify_pct, $omnify_exp_days);
				if ($omnify_gen_coupon) {
					$omnify_cart['coupon_code'] = $omnify_gen_coupon;
					// Persist immediately so a failed email send does not leak a new coupon on the next pass.
					$this->omnify_carts->update_coupon_code((int) ($omnify_cart['id'] ?? 0), $omnify_gen_coupon);
				}
			}
			if ($this->omnify_emails->send_abandoned_cart_recovery($omnify_cart)) {
				$this->omnify_carts->mark_sent((int) $omnify_cart['id']);
			}
		}
	}

	public function generate_recovery_coupon(string $omnify_email, int $omnify_discount_pct = 10, int $omnify_expiry_days = 3): ?string {
		if ($omnify_discount_pct <= 0) {
			return null;
		}
		$omnify_code = 'RECOVER-' . strtoupper(wp_generate_password(6, false, false));
		$omnify_expires = gmdate('Y-m-d H:i:s', time() + ($omnify_expiry_days * DAY_IN_SECONDS));

		try {
			$omnify_coupon_repo = \omnify_get_container()->get(\Omnify\eCommerce\Repositories\Omnify_Coupon_Repository::class);
			$omnify_id = $omnify_coupon_repo->create([
				'code'                     => $omnify_code,
				'is_active'                => 1,
				'discount_type'            => 'percent',
				'discount_value'           => (float) $omnify_discount_pct,
				'usage_limit'              => 1,
				'usage_limit_per_customer' => 1,
				'expires_at'               => $omnify_expires,
			]);

			return $omnify_id > 0 ? $omnify_code : null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function prune_stale_carts(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_retention_days = max(7, absint($omnify_settings['abandoned_cart_retention_days'] ?? 60));
		$this->omnify_carts->prune_stale($omnify_retention_days);
	}
}
