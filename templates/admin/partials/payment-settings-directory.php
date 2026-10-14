<?php
/**
 * Modern Payment Gateways & Methods Directory Partial.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_payment_methods = $omnify_settings['payment_methods'] ?? [];
$omnify_pm_map = [];
foreach ($omnify_payment_methods as $omnify_m) {
	if (! is_array($omnify_m) || empty($omnify_m['id'])) {
		continue;
	}
	$omnify_pm_map[$omnify_m['id']] = $omnify_m;
}

// Calculate active gateways count
$omnify_active_count = 0;
$omnify_gateway_flags = [
	'stripe_checkout_enabled',
	'paypal_checkout_enabled',
	'razorpay_checkout_enabled',
	'mollie_checkout_enabled',
	'paddle_checkout_enabled',
	'paystack_checkout_enabled',
	'tap_checkout_enabled',
	'sslcommerz_checkout_enabled',
	'alipay_checkout_enabled',
	'wechat_checkout_enabled',
	'khalti_checkout_enabled',
	'esewa_checkout_enabled',
];
foreach ($omnify_gateway_flags as $omnify_flag) {
	if (! empty($omnify_settings[$omnify_flag])) {
		$omnify_active_count++;
	}
}
if (! empty($omnify_pm_map['bank_transfer']['enabled'])) $omnify_active_count++;
if (! empty($omnify_pm_map['cheque']['enabled'])) $omnify_active_count++;
if (! empty($omnify_pm_map['cod']['enabled'])) $omnify_active_count++;

$omnify_webhook_base = home_url('/wp-json/omnify/v1/webhook/');

if (! function_exists('omnify_render_pm_header')) {
	function omnify_render_pm_header(array $args): void {
		$title       = $args['title'] ?? '';
		$desc        = $args['desc'] ?? '';
		$active      = ! empty($args['active']);
		$mode        = $args['mode'] ?? 'test';
		$is_offline  = ! empty($args['is_offline']);
		$toggle_name = $args['toggle_name'] ?? '';
		$logo_class  = $args['logo_class'] ?? '';
		$logo_svg    = $args['logo_svg'] ?? '';
		$is_live     = ('live' === $mode);
		?>
		<div class="omnify-pm-header">
			<div class="omnify-pm-brand">
				<div class="omnify-pm-logo <?php echo esc_attr($logo_class); ?>">
					<?php echo $logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="omnify-pm-meta">
					<div class="omnify-pm-title-row">
						<h4 class="omnify-pm-title"><?php echo esc_html($title); ?></h4>
						<span class="omnify-pm-status-badge <?php echo $active ? 'is-active' : 'is-disabled'; ?>">
							<?php echo $active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Disabled', 'omnifywp-ecommerce'); ?>
						</span>
						<?php if ($is_offline) : ?>
							<span class="omnify-pm-mode-badge is-offline"><?php esc_html_e('Offline', 'omnifywp-ecommerce'); ?></span>
						<?php else : ?>
							<span class="omnify-pm-mode-badge <?php echo $is_live ? 'is-live' : 'is-test'; ?>">
								<?php echo $is_live ? esc_html__('Live', 'omnifywp-ecommerce') : esc_html__('Sandbox', 'omnifywp-ecommerce'); ?>
							</span>
						<?php endif; ?>
					</div>
					<?php if ($desc) : ?>
						<p class="omnify-pm-desc"><?php echo esc_html($desc); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="omnify-pm-actions">
				<div class="omnify-pm-toggle-wrap">
					<?php omnify_render_toggle($toggle_name, $active ? 1 : 0); ?>
				</div>
				<button type="button" class="omnify-pm-configure-btn omnify-pm-collapse-btn" aria-expanded="false" aria-label="<?php echo esc_attr(sprintf(__('Configure %s', 'omnifywp-ecommerce'), $title)); ?>">
					<span><?php esc_html_e('Configure', 'omnifywp-ecommerce'); ?></span>
					<svg class="omnify-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
				</button>
			</div>
		</div>
		<?php
	}
}
?>

<div class="omnify-pm-directory">
	<!-- Hero Header Overview -->
	<div class="omnify-pm-hero">
		<div class="omnify-pm-hero-info omnify-pm-hero-content">
			<span class="omnify-pm-hero-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
			</span>
			<div>
				<h3 class="omnify-pm-hero-title"><?php esc_html_e('Payment Gateways & Checkout Methods', 'omnifywp-ecommerce'); ?></h3>
				<p class="omnify-pm-hero-desc omnify-pm-hero-subtitle"><?php esc_html_e('Configure credit cards, digital wallets, global billing systems, and offline payment options. By default all gateways are disabled for security until credentials are provided.', 'omnifywp-ecommerce'); ?></p>
			</div>
		</div>
		<div class="omnify-pm-stats-cluster omnify-pm-hero-stats">
			<div class="omnify-pm-stat-chip">
				<span class="omnify-pm-stat-count" id="omnify-active-pm-count"><?php echo (int) $omnify_active_count; ?></span>
				<span class="omnify-pm-stat-label"><?php esc_html_e('Active Gateways', 'omnifywp-ecommerce'); ?></span>
			</div>
			<div class="omnify-pm-stat-chip">
				<span class="omnify-pm-stat-count">15</span>
				<span class="omnify-pm-stat-label"><?php esc_html_e('Available Providers', 'omnifywp-ecommerce'); ?></span>
			</div>
		</div>
	</div>

	<!-- Directory Filter Toolbar -->
	<div class="omnify-pm-toolbar">
		<div class="omnify-pm-filter-tabs omnify-pm-tabs" role="tablist">
			<button type="button" class="omnify-pm-tab is-active" data-filter="all" data-group="all">
				<?php esc_html_e('All Gateways', 'omnifywp-ecommerce'); ?> <span class="omnify-pm-tab-count">15</span>
			</button>
			<button type="button" class="omnify-pm-tab" data-filter="global" data-group="global">
				<?php esc_html_e('Cards & Global', 'omnifywp-ecommerce'); ?> <span class="omnify-pm-tab-count">4</span>
			</button>
			<button type="button" class="omnify-pm-tab" data-filter="regional" data-group="regional">
				<?php esc_html_e('Regional & Wallets', 'omnifywp-ecommerce'); ?> <span class="omnify-pm-tab-count">8</span>
			</button>
			<button type="button" class="omnify-pm-tab" data-filter="manual" data-group="offline">
				<?php esc_html_e('Offline / Manual', 'omnifywp-ecommerce'); ?> <span class="omnify-pm-tab-count">3</span>
			</button>
		</div>

		<div class="omnify-pm-toolbar-actions">
			<div class="omnify-pm-search-box">
				<span class="omnify-pm-search-icon">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				</span>
				<input type="text" id="omnify-pm-search" name="omnify-pm-search" placeholder="<?php esc_attr_e('Search gateways...', 'omnifywp-ecommerce'); ?>" autocomplete="off" />
			</div>
			<button type="button" id="omnify-pm-expand-all" class="omnify-pm-toggle-all-btn omnify-pm-expand-toggle-btn">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>
				<span><?php esc_html_e('Expand All', 'omnifywp-ecommerce'); ?></span>
			</button>
		</div>
	</div>

	<!-- Gateway Cards Grid/Directory -->
	<div class="omnify-pm-list" id="omnify-pm-list">

		<!-- 1. Stripe -->
		<?php
		$omnify_stripe_active = ! empty($omnify_settings['stripe_checkout_enabled']);
		$omnify_stripe_mode = $omnify_settings['stripe_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_stripe_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="stripe" data-category="global" data-group="global">
			<?php
			omnify_render_pm_header([
				'title'       => __('Stripe Credit Cards & Wallets', 'omnifywp-ecommerce'),
				'desc'        => __('Accept Visa, Mastercard, Amex, Apple Pay & Google Pay globally.', 'omnifywp-ecommerce'),
				'active'      => $omnify_stripe_active,
				'mode'        => $omnify_stripe_mode,
				'toggle_name' => 'stripe_checkout_enabled',
				'logo_class'  => 'omnify-logo-stripe',
				'logo_svg'    => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="7" fill="#635BFF"/><path d="M15.4 12.2c0-1.2 1-1.7 2.6-1.7 2.3 0 5.2.7 7.5 2v-5c-2.5-1-5.1-1.4-7.5-1.4-6.1 0-10.2 3.2-10.2 8.6 0 8.4 11.5 7 11.5 10.6 0 1.4-1.2 1.9-2.9 1.9-2.6 0-5.9-1.1-8.5-2.5v5.1c2.8 1.2 5.7 1.7 8.5 1.7 6.3 0 10.5-3.1 10.5-8.6-.1-9.1-11.5-7.4-11.5-10.7z" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="stripe_mode"><?php esc_html_e('Environment Mode', 'omnifywp-ecommerce'); ?></label>
						<select id="stripe_mode" name="stripe_mode">
							<option value="test" <?php selected($omnify_settings['stripe_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Sandbox / Test Mode', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['stripe_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label><?php esc_html_e('Express Payment Options', 'omnifywp-ecommerce'); ?></label>
						<div style="display: flex; gap: 20px; align-items: center; margin-top: 6px;">
							<label style="display: inline-flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
								<input type="checkbox" name="stripe_apple_pay_enabled" value="1" <?php checked(! empty($omnify_settings['stripe_apple_pay_enabled'])); ?> />
								<span><?php esc_html_e('Apple Pay', 'omnifywp-ecommerce'); ?></span>
							</label>
							<label style="display: inline-flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
								<input type="checkbox" name="stripe_google_pay_enabled" value="1" <?php checked(! empty($omnify_settings['stripe_google_pay_enabled'])); ?> />
								<span><?php esc_html_e('Google Pay', 'omnifywp-ecommerce'); ?></span>
							</label>
							<label style="display: inline-flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
								<input type="checkbox" name="stripe_payment_intents_enabled" value="1" <?php checked(! empty($omnify_settings['stripe_payment_intents_enabled'])); ?> />
								<span><?php esc_html_e('Payment Intents API', 'omnifywp-ecommerce'); ?></span>
							</label>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="stripe_mode" data-cond-val="test">
					<div class="omnify-modern-form-group">
						<label for="stripe_test_publishable_key"><?php esc_html_e('Test Publishable Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="stripe_test_publishable_key" name="stripe_test_publishable_key" placeholder="pk_test_..." value="<?php echo esc_attr($omnify_settings['stripe_test_publishable_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="stripe_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="stripe_test_secret_key" name="stripe_test_secret_key" placeholder="sk_test_..." value="<?php echo esc_attr($omnify_settings['stripe_test_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn" aria-label="<?php esc_attr_e('Show secret', 'omnifywp-ecommerce'); ?>"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="stripe_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="stripe_live_publishable_key"><?php esc_html_e('Live Publishable Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="stripe_live_publishable_key" name="stripe_live_publishable_key" placeholder="pk_live_..." value="<?php echo esc_attr($omnify_settings['stripe_live_publishable_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="stripe_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="stripe_live_secret_key" name="stripe_live_secret_key" placeholder="sk_live_..." value="<?php echo esc_attr($omnify_settings['stripe_live_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn" aria-label="<?php esc_attr_e('Show secret', 'omnifywp-ecommerce'); ?>"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="stripe_webhook_secret"><?php esc_html_e('Stripe Webhook Signing Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="stripe_webhook_secret" name="stripe_webhook_secret" placeholder="whsec_..." value="<?php echo esc_attr($omnify_settings['stripe_webhook_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn" aria-label="<?php esc_attr_e('Show secret', 'omnifywp-ecommerce'); ?>"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
					<div class="omnify-modern-form-group">
						<label><?php esc_html_e('Webhook Endpoint URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'stripe'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'stripe'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 2. PayPal -->
		<?php
		$omnify_paypal_active = ! empty($omnify_settings['paypal_checkout_enabled']);
		$omnify_paypal_mode = $omnify_settings['paypal_mode'] ?? 'sandbox';
		?>
		<div class="omnify-pm-card <?php echo $omnify_paypal_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="paypal" data-category="global" data-group="global">
			<?php
			omnify_render_pm_header([
				'title'       => __('PayPal Express Checkout', 'omnifywp-ecommerce'),
				'desc'        => __('PayPal account balance, Pay in 4 installment financing, and credit cards.', 'omnifywp-ecommerce'),
				'active'      => $omnify_paypal_active,
				'mode'        => $omnify_paypal_mode,
				'toggle_name' => 'paypal_checkout_enabled',
				'logo_class'  => 'omnify-logo-paypal',
				'logo_svg'    => '<svg width="24" height="28" viewBox="0 0 84 100" fill="none"><path d="M68.5 28C66.9 39.8 58.4 47 46.5 47H34.7c-1.5 0-2.8 1.1-3.1 2.6L23.4 80.2c-.3 1.5.9 2.9 2.5 2.9h13c1.5 0 2.8-1.1 3.1-2.6l3-19.1c.3-1.5 1.5-2.6 3.1-2.6h5c15.8 0 27.1-9.1 29.2-25.1.8-5.8-.6-10.5-3.8-14-1.2 3.1-2.9 5.8-5 8.3z" fill="#0079C1"/><path d="M60.9 11C56.7 5.7 49 3.2 38.8 3.2H16c-2.1 0-3.9 1.5-4.1 3.6L.8 74.3c-.3 1.8 1.1 3.4 3 3.4h17.6l4.4-28.5c.3-2.1 2.1-3.6 4.1-3.6h11.8c15.8 0 27.1-9.1 29.2-25.1.9-6.7-.7-11.8-4.1-15.3l-5.9-4.2z" fill="#00457C"/><path d="M60.9 11C56.7 5.7 49 3.2 38.8 3.2H16c-2.1 0-3.9 1.5-4.1 3.6L.8 74.3c-.3 1.8 1.1 3.4 3 3.4h17.6l4.4-28.5c.3-2.1 2.1-3.6 4.1-3.6h11.8c11.9 0 20.4-7.1 21.9-18.9 2-15.3-9.1-23.4-13.2-23.4z" fill="#002952" opacity=".18"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="paypal_mode"><?php esc_html_e('PayPal Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="paypal_mode" name="paypal_mode">
							<option value="sandbox" <?php selected($omnify_settings['paypal_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox Testing', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['paypal_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="paypal_webhook_id"><?php esc_html_e('Webhook ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paypal_webhook_id" name="paypal_webhook_id" value="<?php echo esc_attr($omnify_settings['paypal_webhook_id'] ?? ''); ?>" />
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="paypal_mode" data-cond-val="sandbox">
					<div class="omnify-modern-form-group">
						<label for="paypal_sandbox_client_id"><?php esc_html_e('Sandbox Client ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paypal_sandbox_client_id" name="paypal_sandbox_client_id" value="<?php echo esc_attr($omnify_settings['paypal_sandbox_client_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paypal_sandbox_secret"><?php esc_html_e('Sandbox Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paypal_sandbox_secret" name="paypal_sandbox_secret" value="<?php echo esc_attr($omnify_settings['paypal_sandbox_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="paypal_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="paypal_live_client_id"><?php esc_html_e('Live Client ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paypal_live_client_id" name="paypal_live_client_id" value="<?php echo esc_attr($omnify_settings['paypal_live_client_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paypal_live_secret"><?php esc_html_e('Live Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paypal_live_secret" name="paypal_live_secret" value="<?php echo esc_attr($omnify_settings['paypal_live_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label><?php esc_html_e('PayPal IPN / Webhook Notification URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'paypal'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'paypal'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 3. Razorpay -->
		<?php
		$omnify_razorpay_active = ! empty($omnify_settings['razorpay_checkout_enabled']);
		$omnify_razorpay_mode = $omnify_settings['razorpay_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_razorpay_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="razorpay" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Razorpay (UPI & Netbanking)', 'omnifywp-ecommerce'),
				'desc'        => __('Complete payment suite for India & Southeast Asia (UPI, Cards, EMI, Wallets).', 'omnifywp-ecommerce'),
				'active'      => $omnify_razorpay_active,
				'mode'        => $omnify_razorpay_mode,
				'toggle_name' => 'razorpay_checkout_enabled',
				'logo_class'  => 'omnify-logo-razorpay',
				'logo_svg'    => '<svg width="24" height="28" viewBox="0 0 100 115" fill="none"><path d="M57.6 2.3c-2.4-.4-4.8.8-5.7 3.1L27.1 66.8c-.8 1.9-.3 4.2 1.3 5.5 1.5 1.4 3.7 1.6 5.5.7l19.5-10.4-10.2 47.9c-.4 2.1.6 4.1 2.5 4.9 1.9.9 4.2.4 5.5-1.1L82.1 76.5c1.4-1.6 1.7-3.9.7-5.8-1-1.9-3.1-2.9-5.2-2.6l-18.4 2.9 23.4-62.8c.8-2.2-.2-4.6-2.2-5.7-1-.6-2-.8-2.8-.2z" fill="#0C2340"/><path d="M57.6 2.3c-2.4-.4-4.8.8-5.7 3.1L27.1 66.8c-.8 1.9-.3 4.2 1.3 5.5 1.5 1.4 3.7 1.6 5.5.7l19.5-10.4-10.2 47.9c-.4 2.1.6 4.1 2.5 4.9 1.9.9 4.2.4 5.5-1.1L82.1 76.5l-26.6-4.5 17.6-64.8c.8-2.2-.2-4.6-2.2-5.7-1-.6-2-.8-2.8-.2z" fill="#0284C7"/><path d="M57.6 2.3l-22 59.3 17.8-9.5-7.6 35.8 28.5-27.1-14.8 2.3 17.6-55.9c.8-2.2-.2-4.6-2.2-5.7-1-.6-2-.8-2.8-.2z" fill="#38BDF8"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="razorpay_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="razorpay_mode" name="razorpay_mode">
							<option value="test" <?php selected($omnify_settings['razorpay_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test Sandbox', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['razorpay_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="razorpay_webhook_secret"><?php esc_html_e('Webhook Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="razorpay_webhook_secret" name="razorpay_webhook_secret" value="<?php echo esc_attr($omnify_settings['razorpay_webhook_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="razorpay_mode" data-cond-val="test">
					<div class="omnify-modern-form-group">
						<label for="razorpay_test_key_id"><?php esc_html_e('Test Key ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="razorpay_test_key_id" name="razorpay_test_key_id" value="<?php echo esc_attr($omnify_settings['razorpay_test_key_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="razorpay_test_key_secret"><?php esc_html_e('Test Key Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="razorpay_test_key_secret" name="razorpay_test_key_secret" value="<?php echo esc_attr($omnify_settings['razorpay_test_key_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row" data-cond-field="razorpay_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="razorpay_live_key_id"><?php esc_html_e('Live Key ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="razorpay_live_key_id" name="razorpay_live_key_id" value="<?php echo esc_attr($omnify_settings['razorpay_live_key_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="razorpay_live_key_secret"><?php esc_html_e('Live Key Secret', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="razorpay_live_key_secret" name="razorpay_live_key_secret" value="<?php echo esc_attr($omnify_settings['razorpay_live_key_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label><?php esc_html_e('Webhook Endpoint URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'razorpay'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'razorpay'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 4. Mollie -->
		<?php
		$omnify_mollie_active = ! empty($omnify_settings['mollie_checkout_enabled']);
		$omnify_mollie_mode = $omnify_settings['mollie_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_mollie_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="mollie" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Mollie Payments (Europe)', 'omnifywp-ecommerce'),
				'desc'        => __('iDEAL, Bancontact, Sofort, EPS, Cartes Bancaires & SEPA Direct Debit.', 'omnifywp-ecommerce'),
				'active'      => $omnify_mollie_active,
				'mode'        => $omnify_mollie_mode,
				'toggle_name' => 'mollie_checkout_enabled',
				'logo_class'  => 'omnify-logo-mollie',
				'logo_svg'    => '<svg width="32" height="20" viewBox="0 0 120 70" fill="none"><path d="M30 60C13.43 60 0 46.57 0 30S13.43 0 30 0c11.05 0 20.67 6 25.86 14.89l-11.83 6.94C40.64 16.54 35.65 14 30 14c-8.84 0-16 7.16-16 16s7.16 16 16 16c5.65 0 10.64-2.54 14.03-7.83l11.83 6.94C50.67 54 41.05 60 30 60z" fill="#000000"/><path d="M90 60c-16.57 0-30-13.43-30-30s13.43-30 30-30 30 13.43 30 30-13.43 30-30 30zm0-14c8.84 0 16-7.16 16-16s-7.16-16-16-16-16 7.16-16 16 7.16 16 16 16z" fill="#FF5C35"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="mollie_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="mollie_mode" name="mollie_mode">
							<option value="test" <?php selected($omnify_settings['mollie_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test API', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['mollie_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="mollie_profile_id"><?php esc_html_e('Mollie Profile ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="mollie_profile_id" name="mollie_profile_id" placeholder="pfl_..." value="<?php echo esc_attr($omnify_settings['mollie_profile_id'] ?? ''); ?>" />
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="mollie_mode" data-cond-val="test">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label for="mollie_test_api_key"><?php esc_html_e('Test API Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="mollie_test_api_key" name="mollie_test_api_key" placeholder="test_..." value="<?php echo esc_attr($omnify_settings['mollie_test_api_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="mollie_mode" data-cond-val="live">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label for="mollie_live_api_key"><?php esc_html_e('Live API Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="mollie_live_api_key" name="mollie_live_api_key" placeholder="live_..." value="<?php echo esc_attr($omnify_settings['mollie_live_api_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label><?php esc_html_e('Webhook Endpoint URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'mollie'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'mollie'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 5. Paddle -->
		<?php
		$omnify_paddle_active = ! empty($omnify_settings['paddle_checkout_enabled']);
		$omnify_paddle_mode = $omnify_settings['paddle_mode'] ?? 'sandbox';
		?>
		<div class="omnify-pm-card <?php echo $omnify_paddle_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="paddle" data-category="global" data-group="global">
			<?php
			omnify_render_pm_header([
				'title'       => __('Paddle (Merchant of Record)', 'omnifywp-ecommerce'),
				'desc'        => __('Global sales tax handling, SaaS billing, and fraud mitigation.', 'omnifywp-ecommerce'),
				'active'      => $omnify_paddle_active,
				'mode'        => $omnify_paddle_mode,
				'toggle_name' => 'paddle_checkout_enabled',
				'logo_class'  => 'omnify-logo-paddle',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="20" fill="#2D3142"/><path d="M50 20c-16.57 0-30 13.43-30 30 0 12.04 7.08 22.42 17.3 27.24L48 62.5c-4.14-2.12-7-6.44-7-11.41 0-7.18 5.82-13 13-13 5.3 0 9.87 3.16 11.95 7.69l12.78-7.38C73.34 27.42 62.53 20 50 20z" fill="#00FFB2"/><circle cx="54" cy="51" r="7" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="paddle_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="paddle_mode" name="paddle_mode">
							<option value="sandbox" <?php selected($omnify_settings['paddle_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox Testing', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['paddle_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="paddle_webhook_secret"><?php esc_html_e('Webhook Secret / Alert Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paddle_webhook_secret" name="paddle_webhook_secret" value="<?php echo esc_attr($omnify_settings['paddle_webhook_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="paddle_mode" data-cond-val="sandbox">
					<div class="omnify-modern-form-group">
						<label for="paddle_sandbox_vendor_id"><?php esc_html_e('Sandbox Vendor / Account ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paddle_sandbox_vendor_id" name="paddle_sandbox_vendor_id" value="<?php echo esc_attr($omnify_settings['paddle_sandbox_vendor_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paddle_sandbox_api_key"><?php esc_html_e('Sandbox API Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paddle_sandbox_api_key" name="paddle_sandbox_api_key" value="<?php echo esc_attr($omnify_settings['paddle_sandbox_api_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="paddle_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="paddle_live_vendor_id"><?php esc_html_e('Live Vendor / Account ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paddle_live_vendor_id" name="paddle_live_vendor_id" value="<?php echo esc_attr($omnify_settings['paddle_live_vendor_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paddle_live_api_key"><?php esc_html_e('Live API Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paddle_live_api_key" name="paddle_live_api_key" value="<?php echo esc_attr($omnify_settings['paddle_live_api_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label><?php esc_html_e('Paddle Webhook Destination URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'paddle'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'paddle'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 6. Paystack -->
		<?php
		$omnify_paystack_active = ! empty($omnify_settings['paystack_checkout_enabled']);
		$omnify_paystack_mode = $omnify_settings['paystack_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_paystack_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="paystack" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Paystack (Africa Payments)', 'omnifywp-ecommerce'),
				'desc'        => __('Nigeria, Ghana, South Africa & Kenya payments via Mobile Money, EFT & Cards.', 'omnifywp-ecommerce'),
				'active'      => $omnify_paystack_active,
				'mode'        => $omnify_paystack_mode,
				'toggle_name' => 'paystack_checkout_enabled',
				'logo_class'  => 'omnify-logo-paystack',
				'logo_svg'    => '<svg width="26" height="26" viewBox="0 0 100 100" fill="none"><rect x="10" y="16" width="80" height="14" rx="7" fill="#00C3F7"/><rect x="10" y="43" width="56" height="14" rx="7" fill="#00C3F7"/><rect x="10" y="70" width="80" height="14" rx="7" fill="#00C3F7"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="paystack_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="paystack_mode" name="paystack_mode">
							<option value="test" <?php selected($omnify_settings['paystack_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test Sandbox', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['paystack_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="paystack_webhook_secret"><?php esc_html_e('Webhook Secret Hash', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paystack_webhook_secret" name="paystack_webhook_secret" value="<?php echo esc_attr($omnify_settings['paystack_webhook_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="paystack_mode" data-cond-val="test">
					<div class="omnify-modern-form-group">
						<label for="paystack_test_public_key"><?php esc_html_e('Test Public Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paystack_test_public_key" name="paystack_test_public_key" placeholder="pk_test_..." value="<?php echo esc_attr($omnify_settings['paystack_test_public_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paystack_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paystack_test_secret_key" name="paystack_test_secret_key" placeholder="sk_test_..." value="<?php echo esc_attr($omnify_settings['paystack_test_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="paystack_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="paystack_live_public_key"><?php esc_html_e('Live Public Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="paystack_live_public_key" name="paystack_live_public_key" placeholder="pk_live_..." value="<?php echo esc_attr($omnify_settings['paystack_live_public_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="paystack_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="paystack_live_secret_key" name="paystack_live_secret_key" placeholder="sk_live_..." value="<?php echo esc_attr($omnify_settings['paystack_live_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label><?php esc_html_e('Paystack Webhook URL', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-copy-url-group">
							<input type="text" readonly value="<?php echo esc_url($omnify_webhook_base . 'paystack'); ?>" />
							<button type="button" class="omnify-copy-btn" data-copy="<?php echo esc_url($omnify_webhook_base . 'paystack'); ?>"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 7. Tap Payments -->
		<?php
		$omnify_tap_active = ! empty($omnify_settings['tap_checkout_enabled']);
		$omnify_tap_mode = $omnify_settings['tap_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_tap_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="tap" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Tap Payments (Middle East & GCC)', 'omnifywp-ecommerce'),
				'desc'        => __('Accept KNET, mada, Benefit, Fawry, Apple Pay across Kuwait, Saudi Arabia & UAE.', 'omnifywp-ecommerce'),
				'active'      => $omnify_tap_active,
				'mode'        => $omnify_tap_mode,
				'toggle_name' => 'tap_checkout_enabled',
				'logo_class'  => 'omnify-logo-tap',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="20" fill="#1E293B"/><circle cx="50" cy="50" r="30" stroke="#00D2D3" stroke-width="8" stroke-dasharray="140 50"/><circle cx="50" cy="50" r="10" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="tap_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="tap_mode" name="tap_mode">
							<option value="test" <?php selected($omnify_settings['tap_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test Sandbox', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['tap_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="tap_webhook_secret"><?php esc_html_e('Webhook Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="tap_webhook_secret" name="tap_webhook_secret" value="<?php echo esc_attr($omnify_settings['tap_webhook_secret'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="tap_mode" data-cond-val="test">
					<div class="omnify-modern-form-group">
						<label for="tap_test_publishable_key"><?php esc_html_e('Test Publishable Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="tap_test_publishable_key" name="tap_test_publishable_key" placeholder="pk_test_..." value="<?php echo esc_attr($omnify_settings['tap_test_publishable_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="tap_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="tap_test_secret_key" name="tap_test_secret_key" placeholder="sk_test_..." value="<?php echo esc_attr($omnify_settings['tap_test_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="tap_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="tap_live_publishable_key"><?php esc_html_e('Live Publishable Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="tap_live_publishable_key" name="tap_live_publishable_key" placeholder="pk_live_..." value="<?php echo esc_attr($omnify_settings['tap_live_publishable_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="tap_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="tap_live_secret_key" name="tap_live_secret_key" placeholder="sk_live_..." value="<?php echo esc_attr($omnify_settings['tap_live_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 8. SSLCommerz -->
		<?php
		$omnify_sslcommerz_active = ! empty($omnify_settings['sslcommerz_checkout_enabled']);
		$omnify_sslcommerz_mode = $omnify_settings['sslcommerz_mode'] ?? 'sandbox';
		?>
		<div class="omnify-pm-card <?php echo $omnify_sslcommerz_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="sslcommerz" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('SSLCommerz (Bangladesh Gateway)', 'omnifywp-ecommerce'),
				'desc'        => __('bKash, Nagad, Rocket, Upay, local cards & internet banking.', 'omnifywp-ecommerce'),
				'active'      => $omnify_sslcommerz_active,
				'mode'        => $omnify_sslcommerz_mode,
				'toggle_name' => 'sslcommerz_checkout_enabled',
				'logo_class'  => 'omnify-logo-sslcommerz',
				'logo_svg'    => '<svg width="32" height="20" viewBox="0 0 100 60" fill="none"><rect width="100" height="60" rx="8" fill="#E11D48"/><text x="50" y="40" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif" font-size="28" font-weight="900" fill="#FFFFFF" text-anchor="middle" letter-spacing="1">SSL</text></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="sslcommerz_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="sslcommerz_mode" name="sslcommerz_mode">
							<option value="sandbox" <?php selected($omnify_settings['sslcommerz_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['sslcommerz_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="sslcommerz_mode" data-cond-val="sandbox">
					<div class="omnify-modern-form-group">
						<label for="sslcommerz_test_store_id"><?php esc_html_e('Test Store ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="sslcommerz_test_store_id" name="sslcommerz_test_store_id" value="<?php echo esc_attr($omnify_settings['sslcommerz_test_store_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="sslcommerz_test_store_passwd"><?php esc_html_e('Test Store Password', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="sslcommerz_test_store_passwd" name="sslcommerz_test_store_passwd" value="<?php echo esc_attr($omnify_settings['sslcommerz_test_store_passwd'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="sslcommerz_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="sslcommerz_store_id"><?php esc_html_e('Live Store ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="sslcommerz_store_id" name="sslcommerz_store_id" value="<?php echo esc_attr($omnify_settings['sslcommerz_store_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="sslcommerz_store_passwd"><?php esc_html_e('Live Store Password', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="sslcommerz_store_passwd" name="sslcommerz_store_passwd" value="<?php echo esc_attr($omnify_settings['sslcommerz_store_passwd'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 9. Alipay -->
		<?php
		$omnify_alipay_active = ! empty($omnify_settings['alipay_checkout_enabled']);
		$omnify_alipay_mode = $omnify_settings['alipay_mode'] ?? 'sandbox';
		?>
		<div class="omnify-pm-card <?php echo $omnify_alipay_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="alipay" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Alipay (China & Cross-Border)', 'omnifywp-ecommerce'),
				'desc'        => __('Alipay mobile wallet QR code and cross-border web checkout.', 'omnifywp-ecommerce'),
				'active'      => $omnify_alipay_active,
				'mode'        => $omnify_alipay_mode,
				'toggle_name' => 'alipay_checkout_enabled',
				'logo_class'  => 'omnify-logo-alipay',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#1677FF"/><path d="M78 54.5c-4.2-1.8-11.4-5.2-17.6-8 4.2-8.4 7.6-17.8 9.4-23.5H53.5V17h-8v6H22.7v5.8h40.4c-1.6 4.3-4.4 11.2-7.8 17.5-12.8-4.5-22.3-3.6-26.6-2-5.5 2-8.7 6.6-8.7 12.3 0 7.8 6.5 13.9 16.5 13.9 10.6 0 20.3-6.6 27.6-17.3 7.8 4 17.6 8.5 25.1 11.2l8.8-13.9zm-50 11.7c-5.5 0-9.2-3.1-9.2-7.5 0-3.3 2.1-5.9 5.8-6.9 3.2-.8 9.5-.4 17.5 2.5-4.8 7.3-10.4 11.9-14.1 11.9z" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="alipay_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="alipay_mode" name="alipay_mode">
							<option value="sandbox" <?php selected($omnify_settings['alipay_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['alipay_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="alipay_app_id"><?php esc_html_e('Alipay App ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="alipay_app_id" name="alipay_app_id" value="<?php echo esc_attr($omnify_settings['alipay_app_id'] ?? ''); ?>" />
					</div>
				</div>
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="alipay_merchant_private_key"><?php esc_html_e('Merchant Private Key', 'omnifywp-ecommerce'); ?></label>
						<textarea id="alipay_merchant_private_key" name="alipay_merchant_private_key" rows="3"><?php echo esc_textarea($omnify_settings['alipay_merchant_private_key'] ?? ''); ?></textarea>
					</div>
					<div class="omnify-modern-form-group">
						<label for="alipay_alipay_public_key"><?php esc_html_e('Alipay Public Key', 'omnifywp-ecommerce'); ?></label>
						<textarea id="alipay_alipay_public_key" name="alipay_alipay_public_key" rows="3"><?php echo esc_textarea($omnify_settings['alipay_alipay_public_key'] ?? ''); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- 10. WeChat Pay -->
		<?php
		$omnify_wechat_active = ! empty($omnify_settings['wechat_checkout_enabled']);
		$omnify_wechat_mode = $omnify_settings['wechat_mode'] ?? 'sandbox';
		?>
		<div class="omnify-pm-card <?php echo $omnify_wechat_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="wechat" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('WeChat Pay (Native QR & H5)', 'omnifywp-ecommerce'),
				'desc'        => __('In-app payments, Mini Programs, and web QR code scanner checkout.', 'omnifywp-ecommerce'),
				'active'      => $omnify_wechat_active,
				'mode'        => $omnify_wechat_mode,
				'toggle_name' => 'wechat_checkout_enabled',
				'logo_class'  => 'omnify-logo-wechat',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#07C160"/><path d="M41 24C26.1 24 14 34.1 14 46.5c0 6.9 3.8 13.1 9.9 17.5-.5 2.9-1.8 8.1-2.1 9.3-.4 1.5.6 1.5 1.3 1 5.3-3.6 10.8-7.5 12.5-8.7 1.8.5 3.6.9 5.4.9 14.9 0 27-10.1 27-22.5S55.9 24 41 24z" fill="#FFFFFF"/><circle cx="31" cy="39" r="3.5" fill="#07C160"/><circle cx="51" cy="39" r="3.5" fill="#07C160"/><path d="M64 45c-11.6 0-21 7.8-21 17.5 0 5.4 3 10.3 7.8 13.7-.4 2.3-1.4 6.4-1.6 7.4-.3 1.2.5 1.2 1 0.8 4.1-2.9 8.4-5.9 9.8-6.9 1.4.4 2.8.7 4 .7 11.6 0 21-7.8 21-17.5S75.6 45 64 45z" fill="#FFFFFF"/><circle cx="57" cy="57" r="2.8" fill="#07C160"/><circle cx="71" cy="57" r="2.8" fill="#07C160"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="wechat_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="wechat_mode" name="wechat_mode">
							<option value="sandbox" <?php selected($omnify_settings['wechat_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox Testing', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['wechat_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="wechat_app_id"><?php esc_html_e('WeChat App ID', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="wechat_app_id" name="wechat_app_id" value="<?php echo esc_attr($omnify_settings['wechat_app_id'] ?? ''); ?>" />
					</div>
				</div>
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="wechat_mch_id"><?php esc_html_e('Merchant ID (Mch ID)', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="wechat_mch_id" name="wechat_mch_id" value="<?php echo esc_attr($omnify_settings['wechat_mch_id'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="wechat_api_key"><?php esc_html_e('API Key (v2 / v3 Key)', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="wechat_api_key" name="wechat_api_key" value="<?php echo esc_attr($omnify_settings['wechat_api_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 11. Khalti -->
		<?php
		$omnify_khalti_active = ! empty($omnify_settings['khalti_checkout_enabled']);
		$omnify_khalti_mode = $omnify_settings['khalti_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_khalti_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="khalti" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('Khalti Digital Wallet (Nepal)', 'omnifywp-ecommerce'),
				'desc'        => __('Nepal national payment gateway supporting Khalti wallet & mobile banking.', 'omnifywp-ecommerce'),
				'active'      => $omnify_khalti_active,
				'mode'        => $omnify_khalti_mode,
				'toggle_name' => 'khalti_checkout_enabled',
				'logo_class'  => 'omnify-logo-khalti',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#5C2D91"/><path d="M28 24h13v20.5L62.5 24H78L56 46.5 80 76H63.5L41 48.5V76H28V24z" fill="#FFFFFF"/><circle cx="69" cy="48" r="5" fill="#F59E0B"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="khalti_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="khalti_mode" name="khalti_mode">
							<option value="test" <?php selected($omnify_settings['khalti_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test Sandbox', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['khalti_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="khalti_mode" data-cond-val="test">
					<div class="omnify-modern-form-group">
						<label for="khalti_test_public_key"><?php esc_html_e('Test Public Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="khalti_test_public_key" name="khalti_test_public_key" value="<?php echo esc_attr($omnify_settings['khalti_test_public_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="khalti_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="khalti_test_secret_key" name="khalti_test_secret_key" value="<?php echo esc_attr($omnify_settings['khalti_test_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="khalti_mode" data-cond-val="live">
					<div class="omnify-modern-form-group">
						<label for="khalti_live_public_key"><?php esc_html_e('Live Public Key', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="khalti_live_public_key" name="khalti_live_public_key" value="<?php echo esc_attr($omnify_settings['khalti_live_public_key'] ?? ''); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="khalti_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="khalti_live_secret_key" name="khalti_live_secret_key" value="<?php echo esc_attr($omnify_settings['khalti_live_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 12. eSewa -->
		<?php
		$omnify_esewa_active = ! empty($omnify_settings['esewa_checkout_enabled']);
		$omnify_esewa_mode = $omnify_settings['esewa_mode'] ?? 'test';
		?>
		<div class="omnify-pm-card <?php echo $omnify_esewa_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="esewa" data-category="regional" data-group="regional">
			<?php
			omnify_render_pm_header([
				'title'       => __('eSewa Payment Gateway (Nepal)', 'omnifywp-ecommerce'),
				'desc'        => __('First and largest online payment gateway in Nepal.', 'omnifywp-ecommerce'),
				'active'      => $omnify_esewa_active,
				'mode'        => $omnify_esewa_mode,
				'toggle_name' => 'esewa_checkout_enabled',
				'logo_class'  => 'omnify-logo-esewa',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#60BB46"/><path d="M50 20C33.43 20 20 33.43 20 50s13.43 30 30 30 30-13.43 30-30-13.43-30-30-30zm0 10c11.05 0 20 8.95 20 20s-8.95 20-20 20c-5.8 0-11-2.5-14.7-6.5l8.7-8.7c1.7 1.9 4.2 3.2 7 3.2 5.2 0 9.5-4.3 9.5-9.5H35.5C36.2 38.6 42.4 30 50 30z" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="esewa_mode"><?php esc_html_e('Environment', 'omnifywp-ecommerce'); ?></label>
						<select id="esewa_mode" name="esewa_mode">
							<option value="test" <?php selected($omnify_settings['esewa_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test / UAT Sandbox', 'omnifywp-ecommerce'); ?></option>
							<option value="live" <?php selected($omnify_settings['esewa_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Production', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label for="esewa_secret_key"><?php esc_html_e('eSewa Secret Key / Hash Key', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-password-field-wrap">
							<input type="password" id="esewa_secret_key" name="esewa_secret_key" value="<?php echo esc_attr($omnify_settings['esewa_secret_key'] ?? ''); ?>" />
							<button type="button" class="omnify-toggle-pw-btn"><span class="dashicons dashicons-visibility"></span></button>
						</div>
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="esewa_mode" data-cond-val="test">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label for="esewa_test_merchant_id"><?php esc_html_e('Test Product / Merchant Code (e.g. EPAYTEST)', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="esewa_test_merchant_id" name="esewa_test_merchant_id" value="<?php echo esc_attr($omnify_settings['esewa_test_merchant_id'] ?? ''); ?>" />
					</div>
				</div>
				<div class="omnify-modern-form-row" data-cond-field="esewa_mode" data-cond-val="live">
					<div class="omnify-modern-form-group" style="grid-column: span 2;">
						<label for="esewa_live_merchant_id"><?php esc_html_e('Live Merchant Code', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="esewa_live_merchant_id" name="esewa_live_merchant_id" value="<?php echo esc_attr($omnify_settings['esewa_live_merchant_id'] ?? ''); ?>" />
					</div>
				</div>
			</div>
		</div>

		<!-- 13. Direct Bank Transfer (BACS) -->
		<?php
		$omnify_bank_data = $omnify_pm_map['bank_transfer'] ?? [];
		$omnify_bank_active = ! empty($omnify_bank_data['enabled']);
		$omnify_bank_instr = $omnify_bank_data['instructions'] ?? '';
		$omnify_bank_accounts = (array) ($omnify_bank_data['accounts'] ?? []);
		if (empty($omnify_bank_accounts)) {
			$omnify_bank_accounts[] = ['account_name' => '', 'account_number' => '', 'bank_name' => '', 'sort_code' => '', 'iban' => '', 'bic' => ''];
		}
		?>
		<div class="omnify-pm-card <?php echo $omnify_bank_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="bank_transfer" data-category="manual" data-group="offline">
			<?php
			omnify_render_pm_header([
				'title'       => __('Direct Bank Transfer (BACS / Wire)', 'omnifywp-ecommerce'),
				'desc'        => __('Shoppers transfer funds manually using account number, IBAN or routing code.', 'omnifywp-ecommerce'),
				'active'      => $omnify_bank_active,
				'is_offline'  => true,
				'toggle_name' => 'payment_methods[bank_transfer][enabled]',
				'logo_class'  => 'omnify-logo-bank',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#0F766E"/><path d="M50 18L18 34v6h64v-6L50 18zm-24 26v26h8V44h-8zm16 0v26h8V44h-8zm16 0v26h8V44h-8zm16 0v26h8V44h-8zM14 74v8h72v-8H14z" fill="#FFFFFF"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<input type="hidden" name="payment_methods[bank_transfer][id]" value="bank_transfer" />
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="pm_title_bank_transfer"><?php esc_html_e('Method Title at Checkout', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="pm_title_bank_transfer" name="payment_methods[bank_transfer][title]" value="<?php echo esc_attr($omnify_bank_data['title'] ?? __('Direct Bank Transfer', 'omnifywp-ecommerce')); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="pm_instr_bank_transfer"><?php esc_html_e('Transfer Instructions for Customers', 'omnifywp-ecommerce'); ?></label>
						<textarea id="pm_instr_bank_transfer" name="payment_methods[bank_transfer][instructions]" rows="3" placeholder="<?php esc_attr_e('Make your payment directly into our bank account...', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_bank_instr); ?></textarea>
					</div>
				</div>

				<div style="margin-top: 15px;">
					<h5 style="margin: 0 0 10px; font-size: 13px; color: #475569; font-weight: 600;"><?php esc_html_e('Receiving Bank Account Details', 'omnifywp-ecommerce'); ?></h5>
					<?php foreach ($omnify_bank_accounts as $omnify_idx => $omnify_acc) : ?>
						<div class="omnify-modern-form-row" style="margin-bottom: 12px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Account Name', 'omnifywp-ecommerce'); ?></label>
								<input type="text" name="payment_methods[bank_transfer][accounts][<?php echo (int) $omnify_idx; ?>][account_name]" value="<?php echo esc_attr($omnify_acc['account_name'] ?? ''); ?>" />
							</div>
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Account Number', 'omnifywp-ecommerce'); ?></label>
								<input type="text" name="payment_methods[bank_transfer][accounts][<?php echo (int) $omnify_idx; ?>][account_number]" value="<?php echo esc_attr($omnify_acc['account_number'] ?? ''); ?>" />
							</div>
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Bank Name', 'omnifywp-ecommerce'); ?></label>
								<input type="text" name="payment_methods[bank_transfer][accounts][<?php echo (int) $omnify_idx; ?>][bank_name]" value="<?php echo esc_attr($omnify_acc['bank_name'] ?? ''); ?>" />
							</div>
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('IBAN / Sort Code', 'omnifywp-ecommerce'); ?></label>
								<input type="text" name="payment_methods[bank_transfer][accounts][<?php echo (int) $omnify_idx; ?>][iban]" value="<?php echo esc_attr($omnify_acc['iban'] ?? ''); ?>" />
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- 14. Cheque Payment -->
		<?php
		$omnify_cheque_data = $omnify_pm_map['cheque'] ?? [];
		$omnify_cheque_active = ! empty($omnify_cheque_data['enabled']);
		$omnify_cheque_instr = $omnify_cheque_data['instructions'] ?? '';
		?>
		<div class="omnify-pm-card <?php echo $omnify_cheque_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="cheque" data-category="manual" data-group="offline">
			<?php
			omnify_render_pm_header([
				'title'       => __('Cheque / Check Payment', 'omnifywp-ecommerce'),
				'desc'        => __('Customer mails or delivers a physical bank cheque before order fulfillment.', 'omnifywp-ecommerce'),
				'active'      => $omnify_cheque_active,
				'is_offline'  => true,
				'toggle_name' => 'payment_methods[cheque][enabled]',
				'logo_class'  => 'omnify-logo-cheque',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#0284C7"/><rect x="14" y="26" width="72" height="48" rx="6" fill="#FFFFFF"/><circle cx="28" cy="42" r="5" fill="#0284C7"/><path d="M40 40h34M40 50h24M40 60h30" stroke="#0284C7" stroke-width="3" stroke-linecap="round"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<input type="hidden" name="payment_methods[cheque][id]" value="cheque" />
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="pm_title_cheque"><?php esc_html_e('Method Title at Checkout', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="pm_title_cheque" name="payment_methods[cheque][title]" value="<?php echo esc_attr($omnify_cheque_data['title'] ?? __('Cheque Payment', 'omnifywp-ecommerce')); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="pm_instr_cheque"><?php esc_html_e('Mailing Address & Cheque Payable Details', 'omnifywp-ecommerce'); ?></label>
						<textarea id="pm_instr_cheque" name="payment_methods[cheque][instructions]" rows="3" placeholder="<?php esc_attr_e('Please send a check to: Store Name, Street Address...', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_cheque_instr); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- 15. Cash on Delivery (COD) -->
		<?php
		$omnify_cod_data = $omnify_pm_map['cod'] ?? [];
		$omnify_cod_active = ! empty($omnify_cod_data['enabled']);
		$omnify_cod_instr = $omnify_cod_data['instructions'] ?? '';
		?>
		<div class="omnify-pm-card <?php echo $omnify_cod_active ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="cod" data-category="manual" data-group="offline">
			<?php
			omnify_render_pm_header([
				'title'       => __('Cash on Delivery (COD)', 'omnifywp-ecommerce'),
				'desc'        => __('Shoppers pay cash in hand when products are delivered at their doorstep.', 'omnifywp-ecommerce'),
				'active'      => $omnify_cod_active,
				'is_offline'  => true,
				'toggle_name' => 'payment_methods[cod][enabled]',
				'logo_class'  => 'omnify-logo-cod',
				'logo_svg'    => '<svg width="28" height="28" viewBox="0 0 100 100" fill="none"><rect width="100" height="100" rx="18" fill="#16A34A"/><rect x="14" y="28" width="72" height="44" rx="6" fill="#FFFFFF"/><circle cx="50" cy="50" r="12" fill="#16A34A"/><path d="M50 44v12M46 47h7a2 2 0 0 1 0 4h-6a2 2 0 0 0 0 4h7" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round"/><circle cx="24" cy="50" r="3" fill="#16A34A"/><circle cx="76" cy="50" r="3" fill="#16A34A"/></svg>',
			]);
			?>
			<div class="omnify-pm-body">
				<input type="hidden" name="payment_methods[cod][id]" value="cod" />
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label for="pm_title_cod"><?php esc_html_e('Method Title at Checkout', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="pm_title_cod" name="payment_methods[cod][title]" value="<?php echo esc_attr($omnify_cod_data['title'] ?? __('Cash on Delivery', 'omnifywp-ecommerce')); ?>" />
					</div>
					<div class="omnify-modern-form-group">
						<label for="pm_instr_cod"><?php esc_html_e('Instructions / Verification Note', 'omnifywp-ecommerce'); ?></label>
						<textarea id="pm_instr_cod" name="payment_methods[cod][instructions]" rows="3" placeholder="<?php esc_attr_e('Pay with cash upon delivery of your items.', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_cod_instr); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Custom Repeatable Payment Methods -->
		<div id="omnify-custom-payment-methods-container" class="omnify-custom-pm-list" style="width: 100%; display: flex; flex-direction: column; gap: 14px;">
			<?php
			$omnify_standard_pm_keys = ['bank_transfer', 'cheque', 'cod'];
			foreach ($omnify_payment_methods as $omnify_custom_m) :
				if (! is_array($omnify_custom_m) || empty($omnify_custom_m['id']) || in_array($omnify_custom_m['id'], $omnify_standard_pm_keys, true)) {
					continue;
				}
				$omnify_custom_id = sanitize_key($omnify_custom_m['id']);
				$omnify_custom_title = $omnify_custom_m['title'] ?? '';
				$omnify_custom_instr = $omnify_custom_m['instructions'] ?? '';
				$omnify_custom_enabled = ! empty($omnify_custom_m['enabled']);
			?>
				<div class="omnify-pm-card <?php echo $omnify_custom_enabled ? 'is-active-gateway is-enabled' : ''; ?>" data-gateway="<?php echo esc_attr($omnify_custom_id); ?>" data-category="manual" data-group="offline">
					<?php
					omnify_render_pm_header([
						'title'       => $omnify_custom_title ?: __('Custom Payment Method', 'omnifywp-ecommerce'),
						'desc'        => __('Custom offline or manual payment arrangement.', 'omnifywp-ecommerce'),
						'active'      => $omnify_custom_enabled,
						'is_offline'  => true,
						'toggle_name' => "payment_methods[{$omnify_custom_id}][enabled]",
						'logo_class'  => 'omnify-logo-custom',
						'logo_svg'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M12 8v8M8 12h8"/></svg>',
					]);
					?>
					<div class="omnify-pm-body">
						<input type="hidden" name="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][id]" value="<?php echo esc_attr($omnify_custom_id); ?>" />
						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Method Title', 'omnifywp-ecommerce'); ?></label>
								<input type="text" name="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][title]" value="<?php echo esc_attr($omnify_custom_title); ?>" />
							</div>
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Payment Instructions', 'omnifywp-ecommerce'); ?></label>
								<textarea name="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][instructions]" rows="3"><?php echo esc_textarea($omnify_custom_instr); ?></textarea>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div style="margin-top: 10px;">
			<button type="button" id="omnify-add-custom-pm" class="omnify-modern-btn omnify-modern-btn-secondary">
				<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
				<span><?php esc_html_e('Add Custom Offline Method', 'omnifywp-ecommerce'); ?></span>
			</button>
		</div>

	</div>
</div>
