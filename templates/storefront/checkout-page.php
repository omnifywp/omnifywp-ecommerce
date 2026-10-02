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

		<!-- Steps Nav -->
		<div class="omnify-cart-steps" style="margin-bottom: 40px;">
			<div class="omnify-cart-step">
				<span class="step-num">01</span>
				<div class="step-info">
					<strong>Shopping Cart</strong>
					<span>Manage Your Items List</span>
				</div>
			</div>
			<div class="omnify-cart-step-separator"></div>
			<div class="omnify-cart-step active">
				<span class="step-num">02</span>
				<div class="step-info">
					<strong>Checkout Details</strong>
					<span>Billing & Shipping Info</span>
				</div>
			</div>
			<div class="omnify-cart-step-separator"></div>
			<div class="omnify-cart-step">
				<span class="step-num">03</span>
				<div class="step-info">
					<strong>Order Complete</strong>
					<span>Receipt & Downloads Access</span>
				</div>
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
		$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
		$omnify_is_physical_checkout = (isset($omnify_product['type']) && $omnify_product['type'] === 'physical')
			|| (isset($omnify_product['type']) && $omnify_product['type'] === 'variable' && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
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

		<div id="omnify-checkout-page-grid" class="omnify-checkout-grid">
			
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

					<!-- Contact Information -->
					<h3 class="omnify-checkout-section-title"><?php esc_html_e('Contact information', 'omnifywp-ecommerce'); ?></h3>
					
					<div class="omnify-checkout-form-group">
						<input type="email" id="omnify-email" name="email" required placeholder="<?php esc_attr_e('Email address *', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_default_email); ?>">
					</div>

					<!-- Guest Checkout Tracking Info -->
					<?php if (! is_user_logged_in()) : ?>
						<p style="font-size: 12.5px; color: var(--checkout-gray-500); margin: -8px 0 20px; line-height: 1.45;">
							<?php esc_html_e('Your order confirmation and download access will be sent securely to this email address.', 'omnifywp-ecommerce'); ?>
						</p>
					<?php endif; ?>

					<!-- Shipping / Billing Address Section -->
					<h3 class="omnify-checkout-section-title"><?php esc_html_e('Shipping address', 'omnifywp-ecommerce'); ?></h3>

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
							<span class="btn-text"><?php esc_html_e('CONTINUE TO SHIPPING', 'omnifywp-ecommerce'); ?></span>
							<span class="btn-spinner" style="display: none;"></span>
						</button>
					</div>
				</form>

				<!-- Testimonials Section -->
				<?php
				$omnify_reviews_repo = new \Omnify\eCommerce\Repositories\Omnify_Review_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
				$omnify_real_reviews = $omnify_reviews_repo->all(['status' => 'approved', 'per_page' => 1]);
				if (! empty($omnify_real_reviews)) : 
					$omnify_r = $omnify_real_reviews[0];
					$omnify_gravatar_url = get_avatar_url($omnify_r['customer_email'], ['size' => 96, 'default' => 'mm']);
				?>
					<div class="omnify-checkout-testimonials">
						<h3 style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 500; margin-top: 0; margin-bottom: 20px; color: var(--checkout-dark);"><?php esc_html_e('What they are saying', 'omnifywp-ecommerce'); ?></h3>
						<div style="display: flex; gap: 16px; align-items: center; background: #fff; border: 1px solid var(--checkout-gray-200); border-radius: 12px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,.01);">
							<img src="<?php echo esc_url($omnify_gravatar_url); ?>" alt="<?php echo esc_attr($omnify_r['customer_name']); ?>" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover;" />
							<div style="display: flex; flex-direction: column; gap: 4px;">
								<strong style="font-size: 14px; color: var(--checkout-dark);"><?php echo esc_html($omnify_r['customer_name']); ?></strong>
								<div style="color: #fbbf24; font-size: 12px;"><?php echo esc_html(str_repeat('★', (int)$omnify_r['rating']) . str_repeat('☆', 5 - (int)$omnify_r['rating'])); ?></div>
								<p style="font-size: 12.5px; line-height: 1.6; color: var(--checkout-gray-600); margin: 0; font-style: italic;">
									"<?php echo esc_html(wp_trim_words($omnify_r['review_content'], 30)); ?>"
								</p>
							</div>
						</div>
					</div>
				<?php endif; ?>

			</div>

			<!-- Right Column: Sidebar Order Summary -->
			<aside class="omnify-checkout-sidebar">
				<h2 class="omnify-checkout-sidebar-title"><?php esc_html_e('Order Summary', 'omnifywp-ecommerce'); ?></h2>

				<!-- Single product checkout mode -->
				<?php if (! $omnify_is_cart_checkout) : ?>
					<div class="omnify-summary-item-row">
						<div class="omnify-summary-item-media">
							<span class="product-qty-badge" id="omnify-pdp-qty-badge-display"><?php echo esc_html($omnify_checkout_quantity); ?></span>
							<?php if (! empty($omnify_product['thumbnail_url'])) : ?>
								<img src="<?php echo esc_url($omnify_product['thumbnail_url']); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>">
							<?php else : ?>
								<div style="font-size: 24px;">📦</div>
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
				<?php else : ?>
					<!-- Cart mode list items -->
					<div style="display: grid; gap: 16px; margin-bottom: 24px; max-height: 280px; overflow-y: auto;">
						<?php foreach ($omnify_cart_items as $omnify_item) : 
							$omnify_item_product = $omnify_item['product'];
							$omnify_item_variation = $omnify_item['variation'];
							?>
							<div class="omnify-summary-item-row" style="margin-bottom:0; border-bottom:1px dashed var(--checkout-gray-200); padding-bottom:12px;">
								<div class="omnify-summary-item-media">
									<span class="product-qty-badge"><?php echo esc_html($omnify_item['quantity']); ?></span>
									<?php if (! empty($omnify_item_product['thumbnail_url'])) : ?>
										<img src="<?php echo esc_url($omnify_item_product['thumbnail_url']); ?>" alt="<?php echo esc_attr($omnify_item_product['name']); ?>">
									<?php else : ?>
										<div style="font-size: 20px;">📦</div>
									<?php endif; ?>
								</div>
								<div class="omnify-summary-item-details">
									<h3 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;"><?php echo esc_html($omnify_item_product['name']); ?></h3>
									<?php if ($omnify_item_variation && ! empty($omnify_item_variation['attributes'])) : 
										$omnify_item_var_attrs = [];
										foreach ($omnify_item_variation['attributes'] as $omnify_k => $omnify_v) {
											$omnify_item_var_attrs[] = esc_html($omnify_k . ': ' . $omnify_v);
										}
										?>
										<span style="color: var(--checkout-gray-600); font-size: 11px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;">
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

					<div style="display: flex; justify-content: space-between; border-top: 1px solid var(--checkout-gray-200); padding-top: 16px; margin-top: 6px; font-size: 16px; color: var(--checkout-dark); font-weight: 600; text-transform: uppercase;">
						<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span>
						<span id="omnify-summary-total" style="color: var(--checkout-dark);"><?php echo esc_html($omnify_format_price((float) ($omnify_line_total + $omnify_tax))); ?></span>
					</div>
				</div>
			</aside>
		</div>

		<!-- Success State (Toggled via JS) -->
		<div id="omnify-checkout-page-success" style="display: none; background: #ffffff; border: 1px solid var(--checkout-gray-200); border-radius: 16px; box-shadow: var(--omnify-card-shadow); padding: 40px; text-align: center; max-width: 600px; margin: 40px auto;">
			<div style="width: 60px; height: 60px; background: #ecfdf5; color: var(--checkout-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: bold; margin: 0 auto 20px;">✓</div>
			<h3 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 600; margin: 0 0 10px 0;"><?php esc_html_e('Purchase Successful!', 'omnifywp-ecommerce'); ?></h3>
			<p style="color: var(--checkout-gray-600); font-size: 14px; margin-top: 0; margin-bottom: 24px; line-height: 1.5;">
				<?php esc_html_e('Thank you for your purchase! A confirmation receipt has been sent to your email containing signed download links.', 'omnifywp-ecommerce'); ?>
			</p>
			
			<div style="background: var(--checkout-gray-100); border-radius: 8px; padding: 12px; font-size: 14px; margin-bottom: 30px; display: inline-block;">
				<span><?php esc_html_e('Order Number:', 'omnifywp-ecommerce'); ?> <strong id="omnify-page-success-order-id" style="color: var(--checkout-dark);">#0</strong></span>
			</div>

			<div style="text-align: left; border-top: 1px solid var(--checkout-gray-200); padding-top: 24px; margin-top: 10px;">
				<h4 style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; margin-top: 0; margin-bottom: 16px; color: var(--checkout-dark);"><?php esc_html_e('Download your digital files:', 'omnifywp-ecommerce'); ?></h4>
				<div id="omnify-page-success-files-list" class="omnify-success-files-list" style="display: grid; gap: 12px;">
					<!-- Populated via JS -->
				</div>
							<p style="font-size: 12px; color: var(--checkout-gray-600); margin-top: 30px; line-height: 1.4;">
				<?php 
				// translators: %s: placeholder value.
				printf(esc_html__('You can also access your downloads at any time via the %s.', 'omnifywp-ecommerce'), '<a href="' . esc_url(home_url('/customer-portal/')) . '" style="color: var(--checkout-primary); text-decoration: none; font-weight: 500;">' . esc_html__('Customer Portal', 'omnifywp-ecommerce') . '</a>'); ?>
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
	|| (isset($_GET['omnify_esewa']) && 'success' === sanitize_key(wp_unslash($_GET['omnify_esewa'])));
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
