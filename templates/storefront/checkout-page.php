<?php
/**
 * Storefront Checkout Page Template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_account_creation_mode = $omnify_template_vars['omnify_account_creation_mode'] ?? null;
$omnify_actual_price = $omnify_template_vars['omnify_actual_price'] ?? null;
$omnify_addr = $omnify_template_vars['omnify_addr'] ?? null;
$omnify_alipay_enabled = $omnify_template_vars['omnify_alipay_enabled'] ?? null;
$omnify_available_payment_ids = $omnify_template_vars['omnify_available_payment_ids'] ?? null;
$omnify_c = $omnify_template_vars['omnify_c'] ?? null;
$omnify_c_desc = $omnify_template_vars['omnify_c_desc'] ?? null;
$omnify_card_label = $omnify_template_vars['omnify_card_label'] ?? null;
$omnify_cart_items = $omnify_template_vars['omnify_cart_items'] ?? null;
$omnify_cart_url = $omnify_template_vars['omnify_cart_url'] ?? null;
$omnify_checkout_quantity = $omnify_template_vars['omnify_checkout_quantity'] ?? null;
$omnify_country_code = $omnify_template_vars['omnify_country_code'] ?? null;
$omnify_country_name = $omnify_template_vars['omnify_country_name'] ?? null;
$omnify_coupon_repo = $omnify_template_vars['omnify_coupon_repo'] ?? null;
$omnify_coupons = $omnify_template_vars['omnify_coupons'] ?? null;
$omnify_coupons_enabled = $omnify_template_vars['omnify_coupons_enabled'] ?? null;
$omnify_current_checkout_url = $omnify_template_vars['omnify_current_checkout_url'] ?? null;
$omnify_current_user = $omnify_template_vars['omnify_current_user'] ?? null;
$omnify_customer = $omnify_template_vars['omnify_customer'] ?? null;
$omnify_default_email = $omnify_template_vars['omnify_default_email'] ?? null;
$omnify_default_first = $omnify_template_vars['omnify_default_first'] ?? null;
$omnify_default_last = $omnify_template_vars['omnify_default_last'] ?? null;
$omnify_default_payment_method = $omnify_template_vars['omnify_default_payment_method'] ?? null;
$omnify_default_phone = $omnify_template_vars['omnify_default_phone'] ?? null;
$omnify_enabled_methods = $omnify_template_vars['omnify_enabled_methods'] ?? null;
$omnify_exp_label = $omnify_template_vars['omnify_exp_label'] ?? null;
$omnify_expiry = $omnify_template_vars['omnify_expiry'] ?? null;
$omnify_format_price = $omnify_template_vars['omnify_format_price'] ?? null;
$omnify_gravatar_url = $omnify_template_vars['omnify_gravatar_url'] ?? null;
$omnify_idx = $omnify_template_vars['omnify_idx'] ?? null;
$omnify_is_cart_checkout = $omnify_template_vars['omnify_is_cart_checkout'] ?? null;
$omnify_is_physical_checkout = $omnify_template_vars['omnify_is_physical_checkout'] ?? null;
$omnify_is_redirect_success = $omnify_template_vars['omnify_is_redirect_success'] ?? null;
$omnify_item = $omnify_template_vars['omnify_item'] ?? null;
$omnify_item_max_qty = $omnify_template_vars['omnify_item_max_qty'] ?? null;
$omnify_item_product = $omnify_template_vars['omnify_item_product'] ?? null;
$omnify_item_var_attrs = $omnify_template_vars['omnify_item_var_attrs'] ?? null;
$omnify_item_variation = $omnify_template_vars['omnify_item_variation'] ?? null;
$omnify_k = $omnify_template_vars['omnify_k'] ?? null;
$omnify_line_total = $omnify_template_vars['omnify_line_total'] ?? null;
$omnify_m = $omnify_template_vars['omnify_m'] ?? null;
$omnify_max_purchase_qty = $omnify_template_vars['omnify_max_purchase_qty'] ?? null;
$omnify_method = $omnify_template_vars['omnify_method'] ?? null;
$omnify_methods = $omnify_template_vars['omnify_methods'] ?? null;
$omnify_order_id = $omnify_template_vars['omnify_order_id'] ?? null;
$omnify_paypal_enabled = $omnify_template_vars['omnify_paypal_enabled'] ?? null;
$omnify_portal_url = $omnify_template_vars['omnify_portal_url'] ?? null;
$omnify_product = $omnify_template_vars['omnify_product'] ?? null;
$omnify_r = $omnify_template_vars['omnify_r'] ?? null;
$omnify_razorpay_enabled = $omnify_template_vars['omnify_razorpay_enabled'] ?? null;
$omnify_real_reviews = $omnify_template_vars['omnify_real_reviews'] ?? null;
$omnify_reviews_repo = $omnify_template_vars['omnify_reviews_repo'] ?? null;
$omnify_saved_addresses = $omnify_template_vars['omnify_saved_addresses'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_sslcommerz_enabled = $omnify_template_vars['omnify_sslcommerz_enabled'] ?? null;
$omnify_storefront_pages = $omnify_template_vars['omnify_storefront_pages'] ?? null;
$omnify_storefront_url = $omnify_template_vars['omnify_storefront_url'] ?? null;
$omnify_stripe_enabled = $omnify_template_vars['omnify_stripe_enabled'] ?? null;
$omnify_stripe_wallets = $omnify_template_vars['omnify_stripe_wallets'] ?? null;
$omnify_success_order = $omnify_template_vars['omnify_success_order'] ?? null;
$omnify_tax = $omnify_template_vars['omnify_tax'] ?? null;
$omnify_tax_label = $omnify_template_vars['omnify_tax_label'] ?? null;
$omnify_tax_rate = $omnify_template_vars['omnify_tax_rate'] ?? null;
$omnify_v = $omnify_template_vars['omnify_v'] ?? null;
$omnify_var_attrs = $omnify_template_vars['omnify_var_attrs'] ?? null;
$omnify_variation = $omnify_template_vars['omnify_variation'] ?? null;
$omnify_variation_id = $omnify_template_vars['omnify_variation_id'] ?? null;
$omnify_variation_settings = $omnify_template_vars['omnify_variation_settings'] ?? null;
$omnify_wechat_enabled = $omnify_template_vars['omnify_wechat_enabled'] ?? null;


// Find Storefront page URL for back link
$omnify_storefront_url = home_url('/');
$omnify_storefront_pages = get_posts([
	'post_type'   => 'page',
	's'           => '[omnify_storefront]',
	'post_status' => 'publish',
]);
if (! empty($omnify_storefront_pages)) {
	$omnify_storefront_url = get_permalink($omnify_storefront_pages[0]->ID);
}
$omnify_settings = get_option('omnify_settings', []);
$omnify_coupons_enabled = ! empty($omnify_settings['enable_coupons']);

$omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']) && ! empty($omnify_settings['stripe_checkout_enabled']);
$omnify_paypal_enabled = ! empty($omnify_settings['paypal_enabled']) && ! empty($omnify_settings['paypal_checkout_enabled']);
$omnify_razorpay_enabled = ! empty($omnify_settings['razorpay_enabled']) && ! empty($omnify_settings['razorpay_checkout_enabled']);
$omnify_alipay_enabled = ! empty($omnify_settings['alipay_enabled']) && ! empty($omnify_settings['alipay_checkout_enabled']);
$omnify_wechat_enabled = ! empty($omnify_settings['wechat_enabled']) && ! empty($omnify_settings['wechat_checkout_enabled']);
$omnify_sslcommerz_enabled = ! empty($omnify_settings['sslcommerz_enabled']) && ! empty($omnify_settings['sslcommerz_checkout_enabled']);
$omnify_paystack_enabled = ! empty($omnify_settings['paystack_enabled']) && ! empty($omnify_settings['paystack_checkout_enabled']);
$omnify_tap_enabled = ! empty($omnify_settings['tap_enabled']) && ! empty($omnify_settings['tap_checkout_enabled']);
$omnify_mollie_enabled = ! empty($omnify_settings['mollie_enabled']) && ! empty($omnify_settings['mollie_checkout_enabled']);
$omnify_khalti_enabled = ! empty($omnify_settings['khalti_enabled']) && ! empty($omnify_settings['khalti_checkout_enabled']);
$omnify_esewa_enabled = ! empty($omnify_settings['esewa_enabled']) && ! empty($omnify_settings['esewa_checkout_enabled']);
$omnify_paddle_enabled = ! empty($omnify_settings['paddle_enabled']) && ! empty($omnify_settings['paddle_checkout_enabled']);

$omnify_portal_url = ! empty($omnify_settings['page_customer_portal']) ? get_permalink((int) $omnify_settings['page_customer_portal']) : home_url('/customer-portal/');
if (! $omnify_portal_url) {
	$omnify_portal_url = home_url('/customer-portal/');
}
$omnify_current_checkout_url = home_url(sanitize_url(wp_unslash((string) ($_SERVER['REQUEST_URI'] ?? '/checkout/'))));
$omnify_is_cart_checkout = isset($_GET['cart']) && '1' === sanitize_text_field(wp_unslash((string) $_GET['cart']));
if ($omnify_is_cart_checkout && empty($omnify_product)) {
	$omnify_product = [
		'id' => 0,
		'name' => __('Cart order', 'omnifywp-ecommerce'),
		'price' => 0,
		'sale_price' => null,
		'currency' => $omnify_settings['default_currency'] ?? 'USD',
		'type' => 'physical',
		'thumbnail_url' => '',
		'variation_settings' => ['product_kind' => 'physical'],
		'variations' => [],
		'max_purchase_qty' => 0,
	];
}
$omnify_stripe_status    = isset($_GET['omnify_stripe']) ? sanitize_key(wp_unslash($_GET['omnify_stripe'])) : '';
$omnify_paypal_status    = isset($_GET['omnify_paypal']) ? sanitize_key(wp_unslash($_GET['omnify_paypal'])) : '';
$omnify_paypal_order_id  = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
$omnify_paypal_token     = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';
?>

<script>document.body.classList.add('omnify-checkout-active');</script>
<div class="omnify-storefront-wrapper">
	<div class="omnify-checkout-wrapper">
	<?php if (empty($omnify_product) && ! $omnify_is_cart_checkout) : ?>
		<div class="omnify-storefront-empty omnify-card" style="text-align: center; padding: 60px 20px;">
			<div style="font-size: 48px; margin-bottom: 20px;">🛒</div>
			<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 600; margin-bottom: 12px;"><?php esc_html_e('Your cart is empty', 'omnifywp-ecommerce'); ?></h2>
			<p style="color: var(--omnify-gray-600); margin-bottom: 24px; font-size: 14px;"><?php esc_html_e('Please select a product from the storefront catalog to proceed with checkout.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="omnify-btn omnify-btn--primary">
				<?php esc_html_e('Back to Shop', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	<?php else : ?>
		<?php if ('success' === $omnify_stripe_status) : ?>
			<div class="omnify-notice omnify-notice--success">
				<?php esc_html_e('Payment received. Your order is being finalized and a confirmation email will be sent shortly.', 'omnifywp-ecommerce'); ?>
			</div>
		<?php elseif ('cancelled' === $omnify_stripe_status) : ?>
			<div class="omnify-checkout-notice omnify-checkout-notice--warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; font-size: 14px;">
				<?php esc_html_e('Stripe Checkout was cancelled. You can review your details and try again.', 'omnifywp-ecommerce'); ?>
			</div>
		<?php elseif ('success' === $omnify_paypal_status) : ?>
			<div id="omnify-paypal-capture-notice" class="omnify-checkout-notice omnify-checkout-notice--success" data-order-id="<?php echo esc_attr($omnify_paypal_order_id); ?>" data-paypal-order-id="<?php echo esc_attr($omnify_paypal_token); ?>" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
				<?php esc_html_e('Confirming your PayPal payment...', 'omnifywp-ecommerce'); ?>
			</div>
		<?php elseif ('cancelled' === $omnify_paypal_status) : ?>
			<div class="omnify-checkout-notice omnify-checkout-notice--warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; font-size: 14px;">
				<?php esc_html_e('PayPal Checkout was cancelled. You can review your details and try again.', 'omnifywp-ecommerce'); ?>
			</div>
		<?php endif; ?>

		<?php
		$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
		$omnify_is_physical_checkout = (isset($omnify_product['type']) && $omnify_product['type'] === 'physical')
			|| (isset($omnify_product['type']) && $omnify_product['type'] === 'variable' && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
		?>

		<!-- Steps Nav (Minimal Modern) -->
		<div class="omnify-checkout-steps-container" style="display: flex; justify-content: center; width: 100%; margin: 0 auto 36px;">
			<div class="omnify-cart-steps-minimal" aria-label="<?php esc_attr_e('Checkout progress', 'omnifywp-ecommerce'); ?>">
				<?php 
				$omnify_cart_url = ! empty($omnify_cart_url) 
					? $omnify_cart_url 
					: (function_exists('omnify_get_cart_url') ? omnify_get_cart_url() : home_url('/cart/'));
				?>
				<a href="<?php echo esc_url($omnify_cart_url); ?>" class="omnify-step-item is-completed" data-omnify-cart-url="<?php echo esc_url($omnify_cart_url); ?>" style="text-decoration: none; cursor: pointer;">
					<span class="omnify-step-number" style="background: #18794E !important; color: #FFFFFF !important;">✓</span>
					<span class="omnify-step-title"><?php esc_html_e('Shopping Cart', 'omnifywp-ecommerce'); ?></span>
				</a>
				<span class="omnify-step-divider">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
				</span>
				<span class="omnify-step-item is-active">
					<span class="omnify-step-number">2</span>
					<span class="omnify-step-title"><?php echo $omnify_is_physical_checkout ? esc_html__('Billing & Shipping', 'omnifywp-ecommerce') : esc_html__('Billing & Payment', 'omnifywp-ecommerce'); ?></span>
				</span>
				<span class="omnify-step-divider">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
				</span>
				<span class="omnify-step-item">
					<span class="omnify-step-number">3</span>
					<span class="omnify-step-title"><?php esc_html_e('Order Complete', 'omnifywp-ecommerce'); ?></span>
				</span>
			</div>
		</div>

		<?php
		$omnify_current_user  = is_user_logged_in() ? wp_get_current_user() : null;
		$omnify_default_email = ! empty($omnify_customer['email']) ? $omnify_customer['email'] : ($omnify_current_user ? $omnify_current_user->user_email : '');
		$omnify_default_first = ! empty($omnify_customer['first_name']) ? $omnify_customer['first_name'] : ($omnify_current_user ? $omnify_current_user->first_name : '');
		$omnify_default_last  = ! empty($omnify_customer['last_name']) ? $omnify_customer['last_name'] : ($omnify_current_user ? $omnify_current_user->last_name : '');
		$omnify_default_phone = ! empty($omnify_customer['phone']) ? $omnify_customer['phone'] : '';
		$omnify_account_creation_mode = sanitize_key((string) ($omnify_settings['account_creation_mode'] ?? (! empty($omnify_settings['require_account_on_checkout']) ? 'required' : 'optional')));
		if (! in_array($omnify_account_creation_mode, ['optional', 'automatic', 'required'], true)) {
			$omnify_account_creation_mode = 'optional';
		}

		$omnify_variation_id = isset($_GET['variation_id']) ? absint(wp_unslash($_GET['variation_id'])) : 0;
		$omnify_checkout_quantity = max(1, isset($_GET['quantity']) ? absint(wp_unslash($_GET['quantity'])) : 1);
		$omnify_max_purchase_qty = isset($omnify_product['max_purchase_qty']) ? (int) $omnify_product['max_purchase_qty'] : 0;
		if ($omnify_max_purchase_qty > 0 && $omnify_checkout_quantity > $omnify_max_purchase_qty) {
			$omnify_checkout_quantity = $omnify_max_purchase_qty;
		}
		$omnify_variation = null;
		if ($omnify_variation_id && ! empty($omnify_product['variations'])) {
			foreach ($omnify_product['variations'] as $omnify_v) {
				if ($omnify_v['id'] === $omnify_variation_id) {
					$omnify_variation = $omnify_v;
					break;
				}
			}
		}

		$omnify_actual_price = (float) $omnify_product['price'];
		if (isset($omnify_product['type']) && $omnify_product['type'] === 'variable') {
			if ($omnify_variation) {
				$omnify_actual_price = (null !== $omnify_variation['sale_price'] && $omnify_variation['sale_price'] !== '') ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'];
			}
		} else {
			if (null !== $omnify_product['sale_price'] && $omnify_product['sale_price'] !== '') {
				$omnify_actual_price = (float) $omnify_product['sale_price'];
			}
		}
		$omnify_line_total = $omnify_actual_price * $omnify_checkout_quantity;
		?>

		<div id="omnify-checkout-page-grid" class="omnify-checkout-grid" style="<?php echo ! empty($omnify_is_redirect_success) ? 'display: none;' : ''; ?>">
			
			<!-- Left Column: Form -->
			<div>
				<!-- Returning Customer Notice -->
				<?php if (! is_user_logged_in()) : ?>
					<div class="omnify-checkout-login-toggle" style="background: #f8fafc; border: 1px solid var(--checkout-gray-200); border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13.5px; color: var(--checkout-gray-700);">
						👤 <?php esc_html_e('Returning customer?', 'omnifywp-ecommerce'); ?> <a href="<?php echo esc_url(wp_login_url($omnify_current_checkout_url)); ?>" style="color: var(--checkout-primary); font-weight: 600; text-decoration: none;"><?php esc_html_e('Log in here', 'omnifywp-ecommerce'); ?></a> <?php esc_html_e('to access saved addresses and orders.', 'omnifywp-ecommerce'); ?>
					</div>
				<?php endif; ?>

				<form id="omnify-checkout-page-form" class="omnify-checkout-form"
					data-cart-mode="<?php echo esc_attr($omnify_is_cart_checkout ? '1' : '0'); ?>"
					data-product-name="<?php echo esc_attr($omnify_product['name'] ?? ''); ?>"
					data-product-price="<?php echo esc_attr($omnify_actual_price); ?>"
					data-product-sku="<?php echo esc_attr($omnify_variation ? ($omnify_variation['sku'] ?: $omnify_product['sku'] ?: '') : ($omnify_product['sku'] ?? '')); ?>">
					<input type="hidden" id="omnify-checkout-product-id" name="product_id" value="<?php echo esc_attr((string) $omnify_product['id']); ?>">
					<input type="hidden" id="omnify-checkout-variation-id" name="variation_id" value="<?php echo esc_attr((string) $omnify_variation_id); ?>">
					<input type="hidden" id="omnify-checkout-coupon-code" name="coupon_code" value="">
					<input type="hidden" id="omnify-checkout-quantity" name="quantity" value="<?php echo esc_attr($omnify_checkout_quantity); ?>">

					<!-- Express Checkout Section (Conditional) -->
					<?php if ($omnify_stripe_enabled && (! empty($omnify_settings['stripe_apple_pay_enabled']) || ! empty($omnify_settings['stripe_google_pay_enabled']))) : ?>
					<div class="omnify-checkout-express-box" style="margin-bottom: 24px; padding: 16px 20px; background: #FFFFFF; border: 1px solid var(--checkout-gray-200); border-radius: 12px;">
						<div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;"><?php esc_html_e('Express Checkout', 'omnifywp-ecommerce'); ?></div>
						<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
							<?php if (! empty($omnify_settings['stripe_apple_pay_enabled'])) : ?>
								<button type="button" class="omnify-express-btn" id="omnify-express-apple-pay" onclick="document.getElementById('omnify-checkout-submit').click();" style="background: #000000; color: #FFFFFF; border: none; border-radius: 8px; height: 42px; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
									<span>Pay</span>
								</button>
							<?php endif; ?>
							<?php if (! empty($omnify_settings['stripe_google_pay_enabled'])) : ?>
								<button type="button" class="omnify-express-btn" id="omnify-express-google-pay" onclick="document.getElementById('omnify-checkout-submit').click();" style="background: #FFFFFF; color: #1E293B; border: 1px solid #CBD5E1; border-radius: 8px; height: 42px; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
									<span>GPay</span>
								</button>
							<?php endif; ?>
						</div>
						<div style="display: flex; align-items: center; gap: 12px; margin-top: 16px;">
							<span style="flex: 1; height: 1px; background: #E2E8F0;"></span>
							<span style="font-size: 0.72rem; color: #94A3B8; text-transform: uppercase; font-weight: 600;"><?php esc_html_e('or continue below', 'omnifywp-ecommerce'); ?></span>
							<span style="flex: 1; height: 1px; background: #E2E8F0;"></span>
						</div>
					</div>
					<?php endif; ?>

					<!-- Contact Information -->
					<h3 class="omnify-checkout-section-title"><?php esc_html_e('Contact information', 'omnifywp-ecommerce'); ?></h3>
					
					<div class="omnify-checkout-form-group">
						<input type="email" id="omnify-email" name="email" required placeholder="<?php esc_attr_e('Email address *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_email); ?>">
					</div>

					<!-- Guest Checkout Tracking Info -->
					<?php if (! is_user_logged_in()) : ?>
						<p style="font-size: 12.5px; color: var(--checkout-gray-500); margin: -8px 0 16px; line-height: 1.45;">
							<?php esc_html_e('Your order confirmation and download access will be sent securely to this email address.', 'omnifywp-ecommerce'); ?>
						</p>

						<?php if ('optional' === $omnify_account_creation_mode) : ?>
							<div class="omnify-create-account-wrapper" style="margin-bottom: 20px; padding: 12px 14px; background: #f8fafc; border: 1px solid var(--checkout-gray-200); border-radius: 8px;">
								<label class="omnify-checkbox-label" style="display: flex; align-items: center; gap: 9px; cursor: pointer; font-size: 13px; font-weight: 500; color: #334155; margin: 0;">
									<input type="checkbox" id="omnify-create-account" name="create_account" value="1" class="omnify-checkbox">
									<span><?php esc_html_e('Create an account for faster checkout and order tracking', 'omnifywp-ecommerce'); ?></span>
								</label>
								<div id="omnify-password-group" class="omnify-checkout-form-group" style="display: none; margin-top: 12px; margin-bottom: 0;">
									<label for="omnify-password" style="font-size: 12px; font-weight: 500; color: #475569; display: block; margin-bottom: 4px;"><?php esc_html_e('Create Password *', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="omnify-password" name="password" placeholder="<?php esc_attr_e('Choose a password (min. 6 characters)', 'omnifywp-ecommerce'); ?>">
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<!-- Shipping / Billing Address Section -->
					<h3 class="omnify-checkout-section-title"><?php echo $omnify_is_physical_checkout ? esc_html__('Shipping address', 'omnifywp-ecommerce') : esc_html__('Billing address', 'omnifywp-ecommerce'); ?></h3>

					<?php if ($omnify_is_physical_checkout) : ?>
						<?php
						$omnify_saved_addresses = [];
						if (is_user_logged_in()) {
							$omnify_saved_addresses = get_user_meta(get_current_user_id(), '_omnify_shipping_addresses', true);
							if (!is_array($omnify_saved_addresses)) $omnify_saved_addresses = [];
							
							// Auto-detect and merge default WordPress/WooCommerce shipping/billing address
							$omnify_uid = get_current_user_id();
							$omnify_wp_first = get_user_meta($omnify_uid, 'shipping_first_name', true) ?: get_user_meta($omnify_uid, 'first_name', true);
							$omnify_wp_last = get_user_meta($omnify_uid, 'shipping_last_name', true) ?: get_user_meta($omnify_uid, 'last_name', true);
							$omnify_wp_addr1 = get_user_meta($omnify_uid, 'shipping_address_1', true);
							$omnify_wp_addr2 = get_user_meta($omnify_uid, 'shipping_address_2', true);
							$omnify_wp_city = get_user_meta($omnify_uid, 'shipping_city', true);
							$omnify_wp_state = get_user_meta($omnify_uid, 'shipping_state', true);
							$omnify_wp_postcode = get_user_meta($omnify_uid, 'shipping_postcode', true);
							$omnify_wp_country = get_user_meta($omnify_uid, 'shipping_country', true);
							$omnify_wp_phone = get_user_meta($omnify_uid, 'shipping_phone', true);
							$omnify_wp_company = get_user_meta($omnify_uid, 'shipping_company', true);
							
							if (empty($omnify_wp_addr1)) {
								$omnify_wp_first = $omnify_wp_first ?: get_user_meta($omnify_uid, 'billing_first_name', true);
								$omnify_wp_last = $omnify_wp_last ?: get_user_meta($omnify_uid, 'billing_last_name', true);
								$omnify_wp_addr1 = get_user_meta($omnify_uid, 'billing_address_1', true);
								$omnify_wp_addr2 = get_user_meta($omnify_uid, 'billing_address_2', true);
								$omnify_wp_city = get_user_meta($omnify_uid, 'billing_city', true);
								$omnify_wp_state = get_user_meta($omnify_uid, 'billing_state', true);
								$omnify_wp_postcode = get_user_meta($omnify_uid, 'billing_postcode', true);
								$omnify_wp_country = get_user_meta($omnify_uid, 'billing_country', true);
								$omnify_wp_phone = get_user_meta($omnify_uid, 'billing_phone', true);
								$omnify_wp_company = get_user_meta($omnify_uid, 'billing_company', true);
							}
							
							if (! empty($omnify_wp_addr1)) {
								$omnify_dup = false;
								foreach ($omnify_saved_addresses as $omnify_saved_addr) {
									if (($omnify_saved_addr['address_1'] ?? '') === $omnify_wp_addr1) {
										$omnify_dup = true;
										break;
									}
								}
								if (! $omnify_dup) {
									$omnify_saved_addresses[] = [
										'id' => 'wp_default',
										'first_name' => $omnify_wp_first,
										'last_name' => $omnify_wp_last,
										'address_1' => $omnify_wp_addr1,
										'address_2' => $omnify_wp_addr2,
										'city' => $omnify_wp_city,
										'state' => $omnify_wp_state,
										'postcode' => $omnify_wp_postcode,
										'country' => $omnify_wp_country,
										'phone' => $omnify_wp_phone,
										'company' => $omnify_wp_company,
										'is_default' => true
									];
								}
							}
						}
						?>
						<?php if (!empty($omnify_saved_addresses)) : ?>
							<div class="omnify-checkout-form-group">
								<input type="hidden" id="omnify-saved-address-select" value="">
								<div class="omnify-saved-address-dropdown-container">
									<button type="button" class="omnify-saved-address-trigger">
										<span class="omnify-saved-address-selected-label">— <?php esc_html_e('Enter new address', 'omnifywp-ecommerce'); ?> —</span>
										<svg class="omnify-dropdown-arrow" viewBox="0 0 20 20" fill="currentColor">
											<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
										</svg>
									</button>
									<div class="omnify-saved-address-options">
										<div class="omnify-saved-address-option" data-value="">— <?php esc_html_e('Enter new address', 'omnifywp-ecommerce'); ?> —</div>
										<?php foreach ($omnify_saved_addresses as $omnify_idx => $omnify_addr) : ?>
											<div class="omnify-saved-address-option"
												data-value="<?php echo esc_attr($omnify_addr['id'] ?? $omnify_idx); ?>"
												data-firstname="<?php echo esc_attr($omnify_addr['first_name'] ?? ''); ?>"
												data-lastname="<?php echo esc_attr($omnify_addr['last_name'] ?? ''); ?>"
												data-address1="<?php echo esc_attr($omnify_addr['address_1'] ?? ''); ?>"
												data-address2="<?php echo esc_attr($omnify_addr['address_2'] ?? ''); ?>"
												data-city="<?php echo esc_attr($omnify_addr['city'] ?? ''); ?>"
												data-state="<?php echo esc_attr($omnify_addr['state'] ?? ''); ?>"
												data-postcode="<?php echo esc_attr($omnify_addr['postcode'] ?? ''); ?>"
												data-country="<?php echo esc_attr($omnify_addr['country'] ?? ''); ?>"
												data-phone="<?php echo esc_attr($omnify_addr['phone'] ?? ''); ?>"
												data-is-default="<?php echo !empty($omnify_addr['is_default']) ? 'true' : 'false'; ?>">
												<?php echo esc_html(($omnify_addr['first_name'] ?? '') . ' ' . ($omnify_addr['last_name'] ?? '') . ' — ' . ($omnify_addr['address_1'] ?? '') . ', ' . ($omnify_addr['city'] ?? '')); ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
								<small style="color: var(--checkout-gray-400); font-size: 11px; margin-top: 4px; display: block;">
									<a href="#" id="omnify-use-different-address" style="color: var(--checkout-primary); text-decoration: underline;"><?php esc_html_e('Use a different address', 'omnifywp-ecommerce'); ?></a>
								</small>
							</div>
						<?php endif; ?>

						<div id="omnify-manual-shipping-fields">
							
							<!-- First name / Last name -->
							<div class="omnify-checkout-form-row">
								<input type="text" id="omnify-shipping-firstname" name="shipping_first_name" required placeholder="<?php esc_attr_e('First name *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_first); ?>">
								<input type="text" id="omnify-shipping-lastname" name="shipping_last_name" required placeholder="<?php esc_attr_e('Last name *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_last); ?>">
							</div>

							<!-- Company name (Optional) -->
							<div class="omnify-company-wrap">
								<a href="#" id="omnify-toggle-company" class="optional-toggle-link">+ <?php esc_html_e('Company name (optional)', 'omnifywp-ecommerce'); ?></a>
								<div class="omnify-checkout-form-group" id="omnify-company-input-group" style="display: none;">
									<input type="text" id="omnify-company" name="billing_company" placeholder="<?php esc_attr_e('Company name', 'omnifywp-ecommerce'); ?>">
								</div>
							</div>

							<!-- Country selector -->
							<div class="omnify-checkout-form-group">
								<select id="omnify-shipping-country" name="shipping_country" required class="omnify-searchable-select">
									<option value=""><?php esc_html_e('Country / Region *', 'omnifywp-ecommerce'); ?></option>
									<?php foreach (\Omnify\eCommerce\Support\Omnify_Locations::countries() as $omnify_country_code => $omnify_country_name) : ?>
										<option value="<?php echo esc_attr($omnify_country_code); ?>" <?php selected($omnify_country_code, 'US'); ?>><?php echo esc_html($omnify_country_name); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Street address -->
							<div class="omnify-checkout-form-group">
								<input type="text" id="omnify-shipping-address-1" name="shipping_address_1" required placeholder="<?php esc_attr_e('House number and street name *', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-checkout-form-group">
								<input type="text" id="omnify-shipping-address-2" name="shipping_address_2" placeholder="<?php esc_attr_e('Apartment, suite, unit, etc. (optional)', 'omnifywp-ecommerce'); ?>">
							</div>

							<!-- Town / State / ZIP (3 columns inline) -->
							<div class="omnify-checkout-form-row--three">
								<input type="text" id="omnify-shipping-city" name="shipping_city" required placeholder="<?php esc_attr_e('Town / City *', 'omnifywp-ecommerce'); ?>">
								<select id="omnify-shipping-state" name="shipping_state" required class="omnify-searchable-select">
									<option value=""><?php esc_html_e('State / Province *', 'omnifywp-ecommerce'); ?></option>
								</select>
								<input type="text" id="omnify-shipping-postcode" name="shipping_postcode" required placeholder="<?php esc_attr_e('ZIP Code *', 'omnifywp-ecommerce'); ?>">
							</div>

							<!-- Phone -->
							<div class="omnify-checkout-form-group">
								<input type="tel" id="omnify-shipping-phone" name="shipping_phone" placeholder="<?php esc_attr_e('Phone *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_phone); ?>">
							</div>

						</div>

						<!-- Billing Address Same as Shipping Toggle -->
						<div class="omnify-billing-toggle-wrap" style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--checkout-gray-200);">
							<label class="omnify-checkbox-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--checkout-gray-700);">
								<input type="checkbox" id="omnify-billing-same" name="billing_same_as_shipping" value="1" checked style="accent-color: var(--checkout-primary); width: 16px; height: 16px;">
								<span><?php esc_html_e('Billing address is the same as shipping address', 'omnifywp-ecommerce'); ?></span>
							</label>
						</div>

						<!-- Collapsible Billing Address Fields -->
						<div id="omnify-billing-address-fields" style="display: none; margin-top: 16px;">
							<h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--checkout-gray-700); margin: 0 0 12px 0;"><?php esc_html_e('Billing Address', 'omnifywp-ecommerce'); ?></h4>
							<div class="omnify-checkout-form-row">
								<input type="text" id="omnify-billing-firstname" name="billing_first_name" placeholder="<?php esc_attr_e('Billing first name *', 'omnifywp-ecommerce'); ?>">
								<input type="text" id="omnify-billing-lastname" name="billing_last_name" placeholder="<?php esc_attr_e('Billing last name *', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-company-wrap" style="margin-bottom: 12px;">
								<input type="text" id="omnify-billing-company" name="billing_company" placeholder="<?php esc_attr_e('Billing company name (optional)', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-checkout-form-group">
								<select id="omnify-billing-country" name="billing_country" class="omnify-searchable-select">
									<option value=""><?php esc_html_e('Billing Country / Region *', 'omnifywp-ecommerce'); ?></option>
									<?php foreach (\Omnify\eCommerce\Support\Omnify_Locations::countries() as $omnify_country_code => $omnify_country_name) : ?>
										<option value="<?php echo esc_attr($omnify_country_code); ?>" <?php selected($omnify_country_code, 'US'); ?>><?php echo esc_html($omnify_country_name); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="omnify-checkout-form-group">
								<input type="text" id="omnify-billing-address-1" name="billing_address_1" placeholder="<?php esc_attr_e('Billing house number and street name *', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-checkout-form-group">
								<input type="text" id="omnify-billing-address-2" name="billing_address_2" placeholder="<?php esc_attr_e('Apartment, suite, unit, etc. (optional)', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-checkout-form-row--three">
								<input type="text" id="omnify-billing-city" name="billing_city" placeholder="<?php esc_attr_e('Billing town / city *', 'omnifywp-ecommerce'); ?>">
								<select id="omnify-billing-state" name="billing_state" class="omnify-searchable-select">
									<option value=""><?php esc_html_e('State / Province *', 'omnifywp-ecommerce'); ?></option>
								</select>
								<input type="text" id="omnify-billing-postcode" name="billing_postcode" placeholder="<?php esc_attr_e('ZIP Code *', 'omnifywp-ecommerce'); ?>">
							</div>
							<div class="omnify-checkout-form-group">
								<input type="tel" id="omnify-billing-phone" name="billing_phone" placeholder="<?php esc_attr_e('Billing phone (optional)', 'omnifywp-ecommerce'); ?>">
							</div>
						</div>

					<?php else : ?>
						<!-- Digital items billing fields -->
						<div class="omnify-checkout-form-row">
							<input type="text" id="omnify-billing-firstname" name="billing_first_name" required placeholder="<?php esc_attr_e('First name *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_first); ?>">
							<input type="text" id="omnify-billing-lastname" name="billing_last_name" required placeholder="<?php esc_attr_e('Last name *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_last); ?>">
						</div>
						<div class="omnify-checkout-form-group">
							<input type="text" id="omnify-billing-address-1" name="billing_address_1" required placeholder="<?php esc_attr_e('House number and street name *', 'omnifywp-ecommerce'); ?>">
						</div>
						<div class="omnify-checkout-form-row--three">
							<input type="text" id="omnify-billing-city" name="billing_city" required placeholder="<?php esc_attr_e('Town / City *', 'omnifywp-ecommerce'); ?>">
							<select id="omnify-billing-state" name="billing_state" required class="omnify-state-select">
								<option value=""><?php esc_html_e('State / Province *', 'omnifywp-ecommerce'); ?></option>
							</select>
							<input type="text" id="omnify-billing-postcode" name="billing_postcode" required placeholder="<?php esc_attr_e('ZIP Code *', 'omnifywp-ecommerce'); ?>">
						</div>
						<div class="omnify-checkout-form-group" style="display: none;">
							<select id="omnify-billing-country" name="billing_country" class="omnify-country-select">
								<option value="US" selected>United States</option>
							</select>
						</div>
						<input type="hidden" id="omnify-phone" name="phone" value="<?php echo esc_attr($omnify_default_phone); ?>">
					<?php endif; ?>

					<!-- Order Notes / Special Instructions -->
					<div class="omnify-checkout-notes-wrap" style="margin-top: 20px; margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--checkout-gray-200);">
						<label for="omnify-order-notes" style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--checkout-gray-700); margin-bottom: 8px;">
							<span>📝</span>
							<span><?php esc_html_e('Order notes (optional)', 'omnifywp-ecommerce'); ?></span>
						</label>
						<textarea id="omnify-order-notes" name="order_notes" rows="2" placeholder="<?php esc_attr_e('Notes about your order, e.g. special delivery instructions or gift requests...', 'omnifywp-ecommerce'); ?>" style="width: 100%; border: 1px solid var(--checkout-gray-200); border-radius: 8px; padding: 10px 12px; font-size: 13px; font-family: inherit; line-height: 1.4; resize: vertical; box-sizing: border-box; background: #fff;"></textarea>
					</div>

					<!-- Payment selection block -->
					<?php 
					$omnify_stripe_wallets = [];
					if (! empty($omnify_settings['stripe_apple_pay_enabled'])) {
						$omnify_stripe_wallets[] = __('Apple Pay', 'omnifywp-ecommerce');
					}
					if (! empty($omnify_settings['stripe_google_pay_enabled'])) {
						$omnify_stripe_wallets[] = __('Google Pay', 'omnifywp-ecommerce');
					}
					$omnify_card_label = $omnify_stripe_enabled
						// translators: %s: placeholder value.
						? (empty($omnify_stripe_wallets) ? __('Card via Stripe Checkout', 'omnifywp-ecommerce') : sprintf(__('Card, %s via Stripe Checkout', 'omnifywp-ecommerce'), implode(', ', $omnify_stripe_wallets)))
						: __('Credit/Debit Card (Test)', 'omnifywp-ecommerce');
					$omnify_default_payment_method = sanitize_key((string) ($omnify_settings['default_payment_method'] ?? 'card'));
					$omnify_methods  = $omnify_settings['payment_methods'] ?? [];
					$omnify_enabled_methods = [];
					foreach ($omnify_methods as $omnify_m) {
						if (! empty($omnify_m['enabled'])) {
							$omnify_enabled_methods[] = $omnify_m;
						}
					}
					$omnify_currency = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
					$omnify_khalti_compatible = ('NPR' === $omnify_currency);
					$omnify_esewa_compatible  = ('NPR' === $omnify_currency);
					$omnify_sslcommerz_compatible = ('BDT' === $omnify_currency);
					$omnify_paystack_compatible = in_array($omnify_currency, ['NGN', 'GHS', 'ZAR', 'KES', 'USD'], true);
					$omnify_tap_compatible = in_array($omnify_currency, ['AED', 'SAR', 'KWD', 'BHD', 'OMR', 'QAR', 'EGP', 'JOD', 'USD', 'EUR', 'GBP'], true);
					$omnify_mollie_compatible = in_array($omnify_currency, ['EUR', 'GBP', 'CHF', 'PLN', 'SEK', 'NOK', 'DKK', 'USD'], true);

					$omnify_available_payment_ids = array_merge(
						$omnify_stripe_enabled ? ['card'] : [],
						$omnify_paypal_enabled ? ['paypal'] : [],
						$omnify_razorpay_enabled ? ['razorpay'] : [],
						($omnify_paystack_enabled && $omnify_paystack_compatible) ? ['paystack'] : [],
						($omnify_tap_enabled && $omnify_tap_compatible) ? ['tap'] : [],
						($omnify_mollie_enabled && $omnify_mollie_compatible) ? ['mollie'] : [],
						$omnify_paddle_enabled ? ['paddle'] : [],
						($omnify_khalti_enabled && $omnify_khalti_compatible) ? ['khalti'] : [],
						($omnify_esewa_enabled && $omnify_esewa_compatible) ? ['esewa'] : [],
						($omnify_sslcommerz_enabled && $omnify_sslcommerz_compatible) ? ['sslcommerz'] : [],
						$omnify_alipay_enabled ? ['alipay'] : [],
						$omnify_wechat_enabled ? ['wechat'] : [],
						array_map(static fn($omnify_method) => (string) $omnify_method['id'], $omnify_enabled_methods)
					);
					if (! in_array($omnify_default_payment_method, $omnify_available_payment_ids, true)) {
						$omnify_default_payment_method = $omnify_available_payment_ids[0] ?? '';
					}
					?>
					<?php if (! empty($omnify_available_payment_ids)) : ?>
						<div class="omnify-checkout-payment-methods" style="margin-top: 24px; border-top: 1px solid var(--checkout-gray-200); padding-top: 24px;">
							<h3 class="omnify-checkout-section-title"><?php esc_html_e('Payment method', 'omnifywp-ecommerce'); ?></h3>
							
							<div style="display: grid; gap: 12px; margin-bottom: 20px;">
								<?php if ($omnify_stripe_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'card' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-card" name="payment_method" value="card" <?php checked($omnify_default_payment_method, 'card'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php echo esc_html($omnify_card_label); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_paypal_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'paypal' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-paypal" name="payment_method" value="paypal" <?php checked($omnify_default_payment_method, 'paypal'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('PayPal Checkout', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_razorpay_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'razorpay' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-razorpay" name="payment_method" value="razorpay" <?php checked($omnify_default_payment_method, 'razorpay'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Razorpay (UPI, Cards, Wallets)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_paystack_enabled && $omnify_paystack_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'paystack' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-paystack" name="payment_method" value="paystack" <?php checked($omnify_default_payment_method, 'paystack'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Paystack (Cards, Mobile Money, Bank Transfer)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_tap_enabled && $omnify_tap_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'tap' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-tap" name="payment_method" value="tap" <?php checked($omnify_default_payment_method, 'tap'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Tap Payments (mada, KNET, Benefit, Cards)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_mollie_enabled && $omnify_mollie_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'mollie' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-mollie" name="payment_method" value="mollie" <?php checked($omnify_default_payment_method, 'mollie'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Mollie (iDEAL, Bancontact, SEPA, Cards)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_paddle_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'paddle' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-paddle" name="payment_method" value="paddle" <?php checked($omnify_default_payment_method, 'paddle'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Paddle (Cards, PayPal, Apple Pay, Google Pay, iDEAL)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_khalti_enabled && $omnify_khalti_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'khalti' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-khalti" name="payment_method" value="khalti" <?php checked($omnify_default_payment_method, 'khalti'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Khalti Digital Wallet (Nepal)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_esewa_enabled && $omnify_esewa_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'esewa' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-esewa" name="payment_method" value="esewa" <?php checked($omnify_default_payment_method, 'esewa'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('eSewa ePay (Nepal)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_sslcommerz_enabled && $omnify_sslcommerz_compatible) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'sslcommerz' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-sslcommerz" name="payment_method" value="sslcommerz" <?php checked($omnify_default_payment_method, 'sslcommerz'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('SSLCommerz (Cards, Mobile Banking, Net Banking)', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_alipay_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'alipay' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-alipay" name="payment_method" value="alipay" <?php checked($omnify_default_payment_method, 'alipay'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('Alipay', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php if ($omnify_wechat_enabled) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo 'wechat' === $omnify_default_payment_method ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-wechat" name="payment_method" value="wechat" <?php checked($omnify_default_payment_method, 'wechat'); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php esc_html_e('WeChat Pay', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>

								<?php foreach ($omnify_enabled_methods as $omnify_method) : ?>
									<label class="omnify-payment-method-label" style="display: flex; align-items: center; gap: 12px; background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo $omnify_default_payment_method === $omnify_method['id'] ? 'var(--checkout-primary)' : 'var(--checkout-gray-200)'; ?>; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
										<input type="radio" id="payment-method-<?php echo esc_attr($omnify_method['id']); ?>" name="payment_method" value="<?php echo esc_attr($omnify_method['id']); ?>" <?php checked($omnify_default_payment_method, $omnify_method['id']); ?> style="margin: 0; width: 18px; height: 18px; accent-color: var(--checkout-primary);">
										<span style="font-size: 14px; font-weight: 600; color: var(--checkout-gray-800);"><?php echo esc_html($omnify_method['name']); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($omnify_stripe_enabled) : ?>
						<div class="omnify-checkout-stripe-note" style="margin-top: 15px; background: #f8fafc; border: 1px solid var(--checkout-gray-200); border-radius: 12px; padding: 16px; color: var(--checkout-gray-700); font-size: 13px; line-height: 1.5;">
							<strong style="color: var(--checkout-dark);"><?php esc_html_e('Secure Stripe checkout', 'omnifywp-ecommerce'); ?></strong><br>
							<?php esc_html_e('You will be redirected to Stripe to complete payment securely.', 'omnifywp-ecommerce'); ?>
						</div>
					<?php endif; ?>

					<?php if ($omnify_paypal_enabled) : ?>
						<div id="omnify-paypal-checkout-note" class="omnify-checkout-paypal-note" style="display: none; margin-top: 15px; background: #f8fafc; border: 1px solid var(--checkout-gray-200); border-radius: 12px; padding: 16px; color: var(--checkout-gray-700); font-size: 13px; line-height: 1.5;">
							<strong style="color: var(--checkout-dark);"><?php esc_html_e('Secure PayPal checkout', 'omnifywp-ecommerce'); ?></strong><br>
							<?php esc_html_e('You will be redirected to PayPal to approve and complete payment.', 'omnifywp-ecommerce'); ?>
						</div>
					<?php endif; ?>

					<!-- Pre-Purchase VIP Order Bump -->
					<?php
					$omnify_bump_enabled = ! array_key_exists('order_bump_enabled', $omnify_settings) || ! empty($omnify_settings['order_bump_enabled']);
					if ($omnify_bump_enabled) :
						$omnify_bump_title = ! empty($omnify_settings['order_bump_title']) ? $omnify_settings['order_bump_title'] : __('VIP Priority Processing & Extended Warranty', 'omnifywp-ecommerce');
						$omnify_bump_desc  = ! empty($omnify_settings['order_bump_desc']) ? $omnify_settings['order_bump_desc'] : __('Skip the fulfillment queue with front-of-line priority dispatch plus 1-year accidental replacement guarantee.', 'omnifywp-ecommerce');
						$omnify_bump_price = isset($omnify_settings['order_bump_price']) ? (float) $omnify_settings['order_bump_price'] : 4.99;
					?>
					<div class="omnify-order-bump-card" id="omnify-order-bump-card" style="border: 1.5px dashed #2563EB; background: #EFF6FF; border-radius: 12px; padding: 16px; margin: 24px 0 16px 0; transition: all 0.2s ease;">
						<label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0;">
							<input type="checkbox" id="omnify-order-bump-checkbox" name="order_bump" value="1" data-price="<?php echo esc_attr((string) $omnify_bump_price); ?>" style="margin-top: 3px; width: 18px; height: 18px; accent-color: #2563EB; cursor: pointer;">
							<div style="flex: 1;">
								<div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
									<span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 700; color: #1E40AF;">
										⚡ <?php echo esc_html($omnify_bump_title); ?>
									</span>
									<span style="background: #DBEAFE; color: #1D4ED8; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 6px; white-space: nowrap;">+<?php echo esc_html($omnify_format_price($omnify_bump_price)); ?></span>
								</div>
								<p style="margin: 4px 0 0 0; font-size: 12px; line-height: 1.45; color: #1E3A8A;">
									<?php echo esc_html($omnify_bump_desc); ?>
								</p>
							</div>
						</label>
					</div>
					<?php endif; ?>

					<!-- Terms Agreement -->
					<?php if (! empty($omnify_settings['require_terms'])) : ?>
						<div class="omnify-checkout-terms-wrapper" style="display: flex; align-items: flex-start; gap: 10px; margin-top: 20px; margin-bottom: 20px;">
							<input type="checkbox" id="omnify-agree-terms" name="agree_to_terms" value="1" required class="omnify-checkbox">
							<label for="omnify-agree-terms" style="font-size: 13px; line-height: 1.4; color: var(--checkout-gray-700); cursor: pointer;">
								<?php printf(
									wp_kses(
										// translators: %s: placeholder value.
										__('I agree to the <a href="%s" target="_blank" style="color: var(--checkout-primary); text-decoration: underline;">Terms & Conditions</a>', 'omnifywp-ecommerce'),
										['a' => ['href' => [], 'target' => [], 'style' => []]]
									),
									esc_url($omnify_settings['terms_url'] ?: '#')
								); ?> <span class="required" style="color: #dc2626;">*</span>
							</label>
						</div>
					<?php endif; ?>

					<div id="omnify-checkout-error" class="omnify-checkout-error" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: 12px; font-size: 13px; font-weight: 500; margin-bottom: 15px;"></div>

					<!-- Form Footer Actions -->
					<div class="omnify-checkout-actions">
						<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="return-to-cart-link">
							‹ <?php esc_html_e('RETURN TO CART', 'omnifywp-ecommerce'); ?>
						</a>
						<button type="submit" id="omnify-checkout-submit">
							<span class="btn-text"><?php echo $omnify_is_physical_checkout ? esc_html__('Complete Order & Pay', 'omnifywp-ecommerce') : esc_html__('Pay Now & Download', 'omnifywp-ecommerce'); ?> &rarr;</span>
							<span class="btn-spinner" style="display: none;"></span>
						</button>
					</div>
				</form>

			</div>

			<!-- Right Column: Sidebar Order Summary -->
			<aside class="omnify-checkout-sidebar">
				<h2 class="omnify-checkout-sidebar-title"><?php esc_html_e('Order Summary', 'omnifywp-ecommerce'); ?></h2>

				<!-- Single product checkout mode -->
				<?php if (! $omnify_is_cart_checkout) : 
					$omnify_p_thumb = ! empty($omnify_product['thumbnail_url']) ? $omnify_product['thumbnail_url'] : '';
					if (empty($omnify_p_thumb)) {
						$omnify_pn_lower = strtolower((string) ($omnify_product['name'] ?? ''));
						$omnify_assets_img = defined('OMNIFY_URL') ? OMNIFY_URL . 'assets/storefront/images/' : plugins_url('assets/storefront/images/', dirname(__DIR__, 1) . '/dummy.php');
						if (strpos($omnify_pn_lower, 'hoodie') !== false || strpos($omnify_pn_lower, 'shirt') !== false || strpos($omnify_pn_lower, 'merch') !== false || strpos($omnify_pn_lower, 't-shirt') !== false || strpos($omnify_pn_lower, 'cotton') !== false || strpos($omnify_pn_lower, 'apparel') !== false || 'physical' === strtolower((string) ($omnify_product['type'] ?? ''))) {
							$omnify_p_thumb = $omnify_assets_img . 'product-merch-hoodie.jpg';
						} elseif (strpos($omnify_pn_lower, 'bundle') !== false || strpos($omnify_pn_lower, 'kit') !== false || strpos($omnify_pn_lower, 'pack') !== false || 'bundle' === strtolower((string) ($omnify_product['type'] ?? ''))) {
							$omnify_p_thumb = $omnify_assets_img . 'product-bundle-kit.jpg';
						} else {
							$omnify_p_thumb = $omnify_assets_img . 'product-digital-suite.jpg';
						}
					}
				?>
					<div class="omnify-summary-item-row">
						<div class="omnify-summary-item-media">
							<span class="product-qty-badge" id="omnify-pdp-qty-badge-display"><?php echo esc_html($omnify_checkout_quantity); ?></span>
							<?php if (! empty($omnify_p_thumb)) : ?>
								<img src="<?php echo esc_url($omnify_p_thumb); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>">
							<?php else : ?>
								<div class="omnify-item-media-fallback">
									<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
								</div>
							<?php endif; ?>
						</div>
						<div class="omnify-summary-item-details">
							<h3><?php echo esc_html($omnify_product['name']); ?></h3>
							<?php if ($omnify_variation && ! empty($omnify_variation['attributes'])) : 
								$omnify_var_attrs = [];
								foreach ($omnify_variation['attributes'] as $omnify_k => $omnify_v) {
									$omnify_var_attrs[] = esc_html($omnify_k . ': ' . $omnify_v);
								}
								?>
								<span style="color: var(--checkout-gray-600); font-size: 12px; display: block; margin-bottom: 6px;">
									<?php echo esc_html(implode(', ', $omnify_var_attrs)); ?>
								</span>
							<?php endif; ?>

							<!-- Quantity Select inline -->
							<div class="omnify-checkout-qty-control-wrapper" style="display: flex; align-items: center; gap: 8px;">
								<div class="omnify-checkout-qty-selector" data-price="<?php echo esc_attr($omnify_actual_price); ?>" style="display: flex; align-items: center; border: 1px solid var(--checkout-gray-200); border-radius: 6px; overflow: hidden; background: #fff;">
									<button type="button" class="omnify-checkout-qty-btn checkout-qty-minus" style="width: 24px; height: 24px; border: none; background: #f8fafc; font-size: 14px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;">-</button>
									<input type="number" class="omnify-checkout-qty-input" value="<?php echo esc_attr($omnify_checkout_quantity); ?>" min="1" <?php echo $omnify_max_purchase_qty > 0 ? 'max="' . esc_attr($omnify_max_purchase_qty) . '"' : ''; ?> readonly style="width: 32px; height: 24px; border: none !important; border-left: 1px solid var(--checkout-gray-200) !important; border-right: 1px solid var(--checkout-gray-200) !important; text-align: center; font-size: 12px !important; font-weight: 600; padding: 0 !important; margin: 0 !important; background: #fff !important; min-height: auto !important; border-radius: 0 !important;" />
									<button type="button" class="omnify-checkout-qty-btn checkout-qty-plus" style="width: 24px; height: 24px; border: none; background: #f8fafc; font-size: 14px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;">+</button>
								</div>
							</div>
						</div>
						<span class="omnify-summary-item-price" id="omnify-pdp-line-total-display"><?php echo esc_html($omnify_format_price($omnify_line_total)); ?></span>
					</div>
					<!-- Cart mode list items -->
					<div class="omnify-summary-items-list">
						<?php foreach ($omnify_cart_items as $omnify_item) : 
							$omnify_item_product = $omnify_item['product'];
							$omnify_item_variation = $omnify_item['variation'];
							$omnify_it_thumb = ! empty($omnify_item_product['thumbnail_url']) ? $omnify_item_product['thumbnail_url'] : '';
							if (empty($omnify_it_thumb)) {
								$omnify_itn_lower = strtolower((string) ($omnify_item_product['name'] ?? ''));
								$omnify_assets_img = defined('OMNIFY_URL') ? OMNIFY_URL . 'assets/storefront/images/' : plugins_url('assets/storefront/images/', dirname(__DIR__, 1) . '/dummy.php');
								if (strpos($omnify_itn_lower, 'hoodie') !== false || strpos($omnify_itn_lower, 'shirt') !== false || strpos($omnify_itn_lower, 'merch') !== false || strpos($omnify_itn_lower, 't-shirt') !== false || strpos($omnify_itn_lower, 'cotton') !== false || strpos($omnify_itn_lower, 'apparel') !== false || 'physical' === strtolower((string) ($omnify_item_product['type'] ?? ''))) {
									$omnify_it_thumb = $omnify_assets_img . 'product-merch-hoodie.jpg';
								} elseif (strpos($omnify_itn_lower, 'bundle') !== false || strpos($omnify_itn_lower, 'kit') !== false || strpos($omnify_itn_lower, 'pack') !== false || 'bundle' === strtolower((string) ($omnify_item_product['type'] ?? ''))) {
									$omnify_it_thumb = $omnify_assets_img . 'product-bundle-kit.jpg';
								} else {
									$omnify_it_thumb = $omnify_assets_img . 'product-digital-suite.jpg';
								}
							}
							?>
							<div class="omnify-summary-item-row" style="margin-bottom:0; border-bottom:1px dashed var(--checkout-gray-200); padding-bottom:14px;">
								<div class="omnify-summary-item-media">
									<span class="product-qty-badge"><?php echo esc_html($omnify_item['quantity']); ?></span>
									<?php if (! empty($omnify_it_thumb)) : ?>
										<img src="<?php echo esc_url($omnify_it_thumb); ?>" alt="<?php echo esc_attr($omnify_item_product['name']); ?>">
									<?php else : ?>
										<div class="omnify-item-media-fallback">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
										</div>
									<?php endif; ?>
								</div>
								<div class="omnify-summary-item-details">
									<h3 class="omnify-summary-item-name"><?php echo esc_html($omnify_item_product['name']); ?></h3>
									<?php if ($omnify_item_variation && ! empty($omnify_item_variation['attributes'])) : 
										$omnify_item_var_attrs = [];
										foreach ($omnify_item_variation['attributes'] as $omnify_k => $omnify_v) {
											$omnify_item_var_attrs[] = esc_html($omnify_k . ': ' . $omnify_v);
										}
										?>
										<span class="omnify-summary-item-meta">
											<?php echo esc_html(implode(', ', $omnify_item_var_attrs)); ?>
										</span>
									<?php endif; ?>

									<!-- Quantity Selector -->
									<div class="omnify-checkout-qty-control-wrapper" style="display: flex; align-items: center; gap: 8px;">
										<div class="omnify-checkout-qty-selector" data-price="<?php echo esc_attr($omnify_item['price']); ?>" data-product-id="<?php echo esc_attr($omnify_item_product['id']); ?>" data-variation-id="<?php echo esc_attr($omnify_item_variation ? $omnify_item_variation['id'] : ''); ?>" style="display: flex; align-items: center; border: 1px solid var(--checkout-gray-200); border-radius: 6px; overflow: hidden; background: #fff;">
											<button type="button" class="omnify-checkout-qty-btn checkout-qty-minus" style="width: 24px; height: 24px; border: none; background: #f8fafc; font-size: 14px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;">-</button>
											<?php
											$omnify_item_max_qty = isset($omnify_item_product['max_purchase_qty']) ? (int) $omnify_item_product['max_purchase_qty'] : 0;
											?>
											<input type="number" class="omnify-checkout-qty-input" value="<?php echo esc_attr($omnify_item['quantity']); ?>" min="1" <?php echo $omnify_item_max_qty > 0 ? 'max="' . esc_attr($omnify_item_max_qty) . '"' : ''; ?> readonly style="width: 32px; height: 24px; border: none !important; border-left: 1px solid var(--checkout-gray-200) !important; border-right: 1px solid var(--checkout-gray-200) !important; text-align: center; font-size: 12px !important; font-weight: 600; padding: 0 !important; margin: 0 !important; background: #fff !important; min-height: auto !important; border-radius: 0 !important;" />
											<button type="button" class="omnify-checkout-qty-btn checkout-qty-plus" style="width: 24px; height: 24px; border: none; background: #f8fafc; font-size: 14px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;">+</button>
										</div>
									</div>
								</div>
								<span class="omnify-summary-item-price"><?php echo esc_html($omnify_format_price((float)($omnify_item['price'] * $omnify_item['quantity']))); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div id="omnify-checkout-cart-items" style="display: none;"></div>

				<!-- Toggleable Coupon Section -->
				<?php if (! empty($omnify_settings['enable_coupons'])) : ?>
					<div class="omnify-checkout-coupon-toggle">
						🏷️ <?php esc_html_e('Have a coupon code?', 'omnifywp-ecommerce'); ?> <a href="#" id="omnify-toggle-coupon-btn"><?php esc_html_e('Click here to enter', 'omnifywp-ecommerce'); ?></a>
					</div>
					
					<div id="omnify-coupon-input-wrapper" style="display: none; gap: 8px; margin-bottom: 16px;">
						<input type="text" id="omnify-coupon-input" placeholder="<?php esc_attr_e('Coupon code', 'omnifywp-ecommerce'); ?>" style="flex: 1; border: 1px solid var(--checkout-gray-200); border-radius: 6px; padding: 8px 12px; font-size: 13px; outline: none; text-transform: uppercase; font-weight: 500;">
						<button type="button" id="omnify-coupon-apply" style="background: var(--checkout-primary); color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer;"><?php esc_html_e('APPLY', 'omnifywp-ecommerce'); ?></button>
					</div>

					<!-- Applied Coupon state -->
					<div id="omnify-checkout-coupon-applied" style="display: none; align-items: center; justify-content: space-between; background: #ecfdf5; border: 1px dashed var(--checkout-success); border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px;">
						<span>✓ <strong id="omnify-checkout-applied-code" style="color: #065f46;"></strong></span>
						<button type="button" id="omnify-checkout-coupon-remove" style="background: transparent; border: 1px solid #065f46; color: #065f46; border-radius: 4px; padding: 2px 8px; font-size: 11px; cursor: pointer;">Remove</button>
					</div>
					<div id="omnify-coupon-message" style="display: none; font-size: 12px; margin-bottom: 12px; border-radius: 6px; padding: 8px 12px;"></div>

					<!-- Available Coupons Selector -->
					<?php
					$omnify_coupon_repo = new \Omnify\eCommerce\Repositories\Omnify_Coupon_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
					$omnify_coupons = $omnify_coupon_repo->all(['status' => 'active', 'per_page' => 10]);
					if ($omnify_coupons_enabled && ! empty($omnify_coupons)) : ?>
						<div class="omnify-available-coupons-section" style="margin-top: 16px; border-top: 1px solid var(--checkout-gray-200); padding-top: 16px; margin-bottom: 16px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
								<span style="font-size: 11px; font-weight: 500; color: var(--checkout-gray-700); text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Available Coupon', 'omnifywp-ecommerce'); ?></span>
								<div style="display: flex; gap: 8px;">
									<button type="button" class="coupon-nav-btn" id="coupon-prev" style="background: none; border: none; font-size: 14px; cursor: pointer; color: var(--checkout-gray-400);">&lt;</button>
									<button type="button" class="coupon-nav-btn" id="coupon-next" style="background: none; border: none; font-size: 14px; cursor: pointer; color: var(--checkout-gray-400);">&gt;</button>
								</div>
							</div>
							<div class="omnify-coupons-carousel-wrapper" id="omnify-coupons-carousel" style="overflow-x: auto; display: flex; gap: 12px; scroll-behavior: smooth; padding-bottom: 8px;">
								<?php foreach ($omnify_coupons as $omnify_c) : 
									$omnify_c_desc = '';
									if ($omnify_c['discount_type'] === 'percent') {
										// translators: %s: placeholder value.
										$omnify_c_desc = sprintf(__('%s%% Discount', 'omnifywp-ecommerce'), number_format($omnify_c['discount_value'], 0));
									} else {
										// translators: %s: placeholder value.
										$omnify_c_desc = sprintf(__('%s Discount', 'omnifywp-ecommerce'), $omnify_format_price($omnify_c['discount_value']));
									}
									// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
									$omnify_expiry = ! empty($omnify_c['expires_at']) ? date('m/d/Y', strtotime($omnify_c['expires_at'])) : __('Never expire', 'omnifywp-ecommerce');
									// translators: %s: placeholder value.
									$omnify_exp_label = ! empty($omnify_c['expires_at']) ? sprintf(__('Valid until %s', 'omnifywp-ecommerce'), $omnify_expiry) : __('Never expire', 'omnifywp-ecommerce');
								?>
									<label class="omnify-coupon-card">
										<input type="radio" name="select_avail_coupon" value="<?php echo esc_attr($omnify_c['code']); ?>" style="margin-top: 3px; accent-color: var(--checkout-primary);" />
										<div style="display: flex; flex-direction: column; gap: 4px;">
											<strong style="font-size: 12px; color: var(--checkout-dark); white-space: nowrap;"><?php echo esc_html($omnify_c_desc); ?></strong>
											<span style="font-size: 10px; font-weight: 500; color: var(--checkout-gray-700); background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px dashed #cbd5e1; display: inline-block; width: fit-content; text-transform: uppercase;"><?php echo esc_html($omnify_c['code']); ?></span>
											<span style="font-size: 10px; color: var(--checkout-gray-400); white-space: nowrap;"><?php echo esc_html($omnify_exp_label); ?></span>
										</div>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<!-- Summary Breakdown Totals -->
				<div id="omnify-checkout-summary-breakdown" data-is-physical="<?php echo esc_attr($omnify_is_physical_checkout ? '1' : '0'); ?>" style="display: grid; gap: 12px; font-size: 14px; color: var(--checkout-gray-600); border-top: 1px solid var(--checkout-gray-200); padding-top: 20px;">
					<div style="display: flex; justify-content: space-between;">
						<span><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span>
						<strong id="omnify-summary-subtotal" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_format_price((float) $omnify_line_total)); ?></strong>
					</div>

					<div id="omnify-summary-discount-row" style="display: none; justify-content: space-between; color: var(--checkout-success);">
						<span id="omnify-summary-discount-label"><?php esc_html_e('Discount', 'omnifywp-ecommerce'); ?></span>
						<strong id="omnify-summary-discount-amount" style="color: var(--checkout-success);">-<?php echo esc_html($omnify_format_price(0)); ?></strong>
					</div>
					
					<?php 
					$omnify_tax = 0.0;
					$omnify_tax_rate = isset($omnify_tax_rate) ? (float)$omnify_tax_rate : 0;
					$omnify_tax_label = isset($omnify_tax_label) ? $omnify_tax_label : __('Tax', 'omnifywp-ecommerce');
					if ($omnify_tax_rate > 0) {
						$omnify_tax = $omnify_line_total * ($omnify_tax_rate / 100);
					}
					?>
					<div id="omnify-summary-tax-row" style="display: flex; justify-content: space-between; <?php echo $omnify_tax_rate > 0 ? '' : 'display: none;'; ?>">
						<span id="omnify-summary-tax-label"><?php echo esc_html(sprintf('%s (%s%%)', $omnify_tax_label, number_format($omnify_tax_rate, 2))); ?></span>
						<strong id="omnify-summary-tax-amount" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_format_price((float) $omnify_tax)); ?></strong>
					</div>

					<div id="omnify-summary-shipping-row" style="display: <?php echo $omnify_is_physical_checkout ? 'flex' : 'none'; ?>; justify-content: space-between;">
						<span id="omnify-summary-shipping-label"><?php esc_html_e('Shipment', 'omnifywp-ecommerce'); ?></span>
						<strong id="omnify-summary-shipping-amount" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_format_price(0)); ?></strong>
					</div>

					<div id="omnify-shipping-methods" style="display: <?php echo $omnify_is_physical_checkout ? 'grid' : 'none'; ?>; gap: 8px; padding: 12px; border: 1px solid var(--checkout-gray-200); border-radius: 8px; background: #fff;">
						<input type="hidden" id="omnify-shipping-method-id" name="shipping_method_id" value="" />
						<div style="font-size: 11px; font-weight: 500; color: var(--checkout-gray-700); text-transform: uppercase;"><?php esc_html_e('Delivery Method', 'omnifywp-ecommerce'); ?></div>
						<div id="omnify-shipping-method-options" style="display: grid; gap: 8px;"></div>
						<div id="omnify-shipping-unavailable" style="display: none; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 8px 10px; font-size: 12px; font-weight: 600;">
							<?php esc_html_e('Delivery is not available for this address.', 'omnifywp-ecommerce'); ?>
						</div>
					</div>

					<!-- VIP Order Bump Summary Row -->
					<div id="omnify-summary-bump-row" style="display: none; justify-content: space-between; color: #2563EB; font-weight: 500;">
						<span id="omnify-summary-bump-label">⚡ <?php echo esc_html($omnify_bump_title ?? __('VIP Priority Processing', 'omnifywp-ecommerce')); ?></span>
						<strong id="omnify-summary-bump-amount" style="color: #2563EB;">+<?php echo esc_html($omnify_format_price($omnify_bump_price ?? 4.99)); ?></strong>
					</div>

					<div style="display: flex; justify-content: space-between; border-top: 1px solid var(--checkout-gray-200); padding-top: 16px; margin-top: 6px; font-size: 16px; color: var(--checkout-dark); font-weight: 600; text-transform: uppercase;">
						<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span>
						<span id="omnify-summary-total" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_format_price((float) ($omnify_line_total + $omnify_tax))); ?></span>
					</div>

					<div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #18794E; margin-top: 6px;">
						<span><?php esc_html_e('Platform Commission:', 'omnifywp-ecommerce'); ?></span>
						<strong>$0.00 (0% Free)</strong>
					</div>

					<!-- Instant Delivery Assurance Banner -->
					<div style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:12px;font-size:0.75rem;color:#065F46;line-height:1.45;margin-top:14px;">
						<strong>⚡ <?php echo $omnify_is_physical_checkout ? esc_html__('Fast Dispatch:', 'omnifywp-ecommerce') : esc_html__('Instant Delivery:', 'omnifywp-ecommerce'); ?></strong>
						<?php echo $omnify_is_physical_checkout ? esc_html__('Orders are dispatched directly from our verified warehouse with real-time tracking.', 'omnifywp-ecommerce') : esc_html__('Secure cryptographic HMAC download links appear immediately on screen upon payment completion.', 'omnifywp-ecommerce'); ?>
					</div>

					<!-- Trust Badges Strip -->
					<div class="omnify-checkout-trust-box" style="margin-top: 16px; padding-top: 14px; border-top: 1px dashed var(--checkout-gray-200); text-align: center;">
						<div style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--checkout-gray-600); margin-bottom: 10px;">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
							<span><?php esc_html_e('Guaranteed Safe & Secure Checkout', 'omnifywp-ecommerce'); ?></span>
						</div>
						<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;">
							<span style="font-size: 10.5px; font-weight: 600; color: #1E293B; background: #F1F5F9; padding: 3px 7px; border-radius: 4px; border: 1px solid #E2E8F0;">VISA</span>
							<span style="font-size: 10.5px; font-weight: 600; color: #1E293B; background: #F1F5F9; padding: 3px 7px; border-radius: 4px; border: 1px solid #E2E8F0;">Mastercard</span>
							<span style="font-size: 10.5px; font-weight: 600; color: #1E293B; background: #F1F5F9; padding: 3px 7px; border-radius: 4px; border: 1px solid #E2E8F0;">AMEX</span>
							<span style="font-size: 10.5px; font-weight: 600; color: #0284C7; background: #F0F9FF; padding: 3px 7px; border-radius: 4px; border: 1px solid #BAE6FD;">PayPal</span>
							<span style="font-size: 10.5px; font-weight: 600; color: #0F172A; background: #F1F5F9; padding: 3px 7px; border-radius: 4px; border: 1px solid #E2E8F0;">Apple Pay</span>
							<span style="font-size: 10.5px; font-weight: 600; color: #15803D; background: #F0FDF4; padding: 3px 7px; border-radius: 4px; border: 1px solid #BBF7D0;">256-Bit SSL</span>
						</div>
					</div>
				</div>
			</aside>
		</div>

		<!-- Success State (Toggled via JS or populated via gateway redirect) -->
		<?php
		$omnify_order_num_display = $omnify_success_order
			? ($omnify_success_order['order_number'] ?: '#' . $omnify_success_order['id'])
			: (isset($_GET['order_id']) ? '#' . absint($_GET['order_id']) : '#0');
		$omnify_is_physical_success = false;
		if ($omnify_success_order && ! empty($omnify_success_order['items'])) {
			foreach ($omnify_success_order['items'] as $omnify_soi) {
				$omnify_item_p = $this->omnify_products->find((int) $omnify_soi['product_id']);
				$omnify_item_vs = $omnify_item_p ? wp_parse_args($omnify_item_p['variation_settings'] ?? [], ['product_kind' => 'digital']) : [];
				if ($omnify_item_p && ('physical' === ($omnify_item_p['type'] ?? 'download') || ('variable' === ($omnify_item_p['type'] ?? 'download') && 'physical' === ($omnify_item_vs['product_kind'] ?? 'digital')))) {
					$omnify_is_physical_success = true;
					break;
				}
			}
		}
		?>
		<div id="omnify-checkout-page-success" style="<?php echo ! empty($omnify_is_redirect_success) ? 'display: block;' : 'display: none;'; ?> background: #ffffff; border: 1px solid var(--checkout-gray-200); border-radius: 16px; box-shadow: var(--omnify-card-shadow); padding: 40px; text-align: center; max-width: 600px; margin: 40px auto;">
			<div style="width: 60px; height: 60px; background: #ecfdf5; color: var(--checkout-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: bold; margin: 0 auto 20px;">✓</div>
			<h3 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 600; margin: 0 0 10px 0;">
				<?php echo $omnify_is_physical_success ? esc_html__('Order Placed Successfully!', 'omnifywp-ecommerce') : esc_html__('Purchase Successful!', 'omnifywp-ecommerce'); ?>
			</h3>
			<p style="color: var(--checkout-gray-600); font-size: 14px; margin-top: 0; margin-bottom: 24px; line-height: 1.5;">
				<?php
				if ($omnify_is_physical_success) {
					esc_html_e('Thank you for your order! We have received your purchase and a confirmation email has been sent to you. Your order will be prepared and shipped shortly.', 'omnifywp-ecommerce');
				} else {
					esc_html_e('Thank you for your purchase! A confirmation receipt has been sent to your email containing your access and download links.', 'omnifywp-ecommerce');
				}
				?>
			</p>
			
			<div style="background: var(--checkout-gray-100); border-radius: 8px; padding: 12px; font-size: 14px; margin-bottom: 30px; display: inline-block;">
				<span><?php esc_html_e('Order Number:', 'omnifywp-ecommerce'); ?> <strong id="omnify-page-success-order-id" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_order_num_display); ?></strong></span>
			</div>

			<?php if (! empty($omnify_success_files)) : ?>
				<div style="text-align: left; border-top: 1px solid var(--checkout-gray-200); padding-top: 24px; margin-top: 10px;">
					<h4 style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; margin-top: 0; margin-bottom: 16px; color: var(--checkout-dark);"><?php esc_html_e('Download your digital files:', 'omnifywp-ecommerce'); ?></h4>
					<div id="omnify-page-success-files-list" class="omnify-success-files-list" style="display: grid; gap: 12px;">
						<?php foreach ($omnify_success_files as $omnify_sf) : ?>
							<div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid var(--checkout-gray-200); border-radius: 8px; padding: 12px 16px;">
								<div>
									<div style="font-weight: 600; color: var(--checkout-dark); font-size: 14px;"><?php echo esc_html($omnify_sf['file_name']); ?></div>
									<?php if (! empty($omnify_sf['file_size'])) : ?>
										<div style="font-size: 12px; color: var(--checkout-gray-600);"><?php echo esc_html($omnify_sf['file_size']); ?></div>
									<?php endif; ?>
								</div>
								<a href="<?php echo esc_url($omnify_sf['download_url']); ?>" class="omnify-btn omnify-btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 13px; text-decoration: none; border-radius: 6px; background: var(--checkout-primary); color: #ffffff; font-weight: 500;" download>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
									<?php esc_html_e('Download', 'omnifywp-ecommerce'); ?>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else : ?>
				<div style="text-align: left; border-top: 1px solid var(--checkout-gray-200); padding-top: 24px; margin-top: 10px; <?php echo $omnify_is_physical_success ? 'display: none;' : ''; ?>">
					<h4 style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; margin-top: 0; margin-bottom: 16px; color: var(--checkout-dark);"><?php esc_html_e('Download your digital files:', 'omnifywp-ecommerce'); ?></h4>
					<div id="omnify-page-success-files-list" class="omnify-success-files-list" style="display: grid; gap: 12px;">
						<!-- Populated via JS -->
					</div>
				</div>
			<?php endif; ?>

			<p style="font-size: 12px; color: var(--checkout-gray-600); margin-top: 30px; line-height: 1.4;">
				<?php 
				// translators: %s: placeholder value.
				printf(esc_html__('You can also access your %1$s at any time via the %2$s.', 'omnifywp-ecommerce'), $omnify_is_physical_success ? esc_html__('orders', 'omnifywp-ecommerce') : esc_html__('downloads', 'omnifywp-ecommerce'), '<a href="' . esc_url($omnify_portal_url) . '" style="color: var(--checkout-primary); text-decoration: none; font-weight: 500;">' . esc_html__('Customer Portal', 'omnifywp-ecommerce') . '</a>'); ?>
			</p>
		</div>
	<?php endif; ?>
	</div>
</div>

<?php
$omnify_is_redirect_success = (isset($_GET['omnify_stripe']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_stripe'])))
	|| (isset($_GET['omnify_paypal']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_paypal'])))
	|| (isset($_GET['omnify_alipay']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_alipay'])))
	|| (isset($_GET['omnify_wechat']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_wechat'])))
	|| (isset($_GET['omnify_sslcommerz']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_sslcommerz'])))
	|| (isset($_GET['omnify_paystack']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_paystack'])))
	|| (isset($_GET['omnify_tap']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_tap'])))
	|| (isset($_GET['omnify_mollie']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_mollie'])))
	|| (isset($_GET['omnify_khalti']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_khalti'])))
	|| (isset($_GET['omnify_esewa']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_esewa'])))
	|| (isset($_GET['omnify_paddle']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_paddle'])));
if ($omnify_is_redirect_success && (! empty($omnify_settings['tracking_ga4_enabled']) || ! empty($omnify_settings['tracking_meta_enabled']))) :
	$omnify_order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
	$omnify_success_order = $omnify_order_id ? $this->omnify_orders->find($omnify_order_id) : null;
	if ($omnify_success_order) :
		$omnify_order_num = (string)$omnify_success_order['order_number'] ?: (string)$omnify_success_order['id'];
		$omnify_currency = $omnify_settings['default_currency'] ?? 'USD';
		$omnify_total = (float)$omnify_success_order['total'];
		$omnify_tax = (float)$omnify_success_order['tax'];
		$omnify_shipping = (float)$omnify_success_order['shipping_total'];
		
		$omnify_items_array = array_map(static fn($omnify_item) => [
			'item_id' => $omnify_item['variation_id'] ? (string)$omnify_item['variation_id'] : (string)$omnify_item['product_id'],
			'item_name' => $omnify_item['product_name'],
			'price' => (float)$omnify_item['price'],
			'quantity' => (int)$omnify_item['quantity']
		], $omnify_success_order['items']);
		
		$omnify_items_json = wp_json_encode($omnify_items_array);
		$omnify_order_num_json = wp_json_encode($omnify_order_num);
		$omnify_currency_json = wp_json_encode($omnify_currency);
		
		$omnify_tracking_js = "
		(function() {
			const orderId = {$omnify_order_num_json};
			const tracked = JSON.parse(window.sessionStorage.getItem('omnify_tracked_orders') || '[]');
			if (tracked.includes(orderId)) {
				return;
			}
			tracked.push(orderId);
			window.sessionStorage.setItem('omnify_tracked_orders', JSON.stringify(tracked));

			const currency = {$omnify_currency_json};
			const total = {$omnify_total};
			const items = {$omnify_items_json};
		";
		
		if (! empty($omnify_settings['tracking_ga4_enabled']) && ! empty($omnify_settings['tracking_ga4_measurement_id'])) {
			$omnify_tracking_js .= "
			if (typeof window.gtag === 'function') {
				window.gtag('event', 'purchase', {
					transaction_id: orderId,
					value: total,
					tax: {$omnify_tax},
					shipping: {$omnify_shipping},
					currency: currency,
					items: items
				});
			}
			";
		}
		
		if (! empty($omnify_settings['tracking_meta_enabled']) && ! empty($omnify_settings['tracking_meta_pixel_id'])) {
			$omnify_tracking_js .= "
			if (typeof window.fbq === 'function') {
				window.fbq('track', 'Purchase', {
					value: total,
					currency: currency,
					content_ids: items.map(i => i.item_id),
					content_type: 'product'
				});
			}
			";
		}
		
		$omnify_tracking_js .= "
		})();
		";
		
		wp_add_inline_script('omnify-storefront-js', $omnify_tracking_js);
	endif;
endif;
?>
