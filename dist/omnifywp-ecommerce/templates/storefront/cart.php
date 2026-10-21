<?php
/**
 * Storefront cart template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_checkout_url = $omnify_template_vars['omnify_checkout_url'] ?? null;
$omnify_coupons_enabled = $omnify_template_vars['omnify_coupons_enabled'] ?? null;
$omnify_cross_sells = $omnify_template_vars['omnify_cross_sells'] ?? null;
$omnify_cs = $omnify_template_vars['omnify_cs'] ?? null;
$omnify_cs_display = $omnify_template_vars['omnify_cs_display'] ?? null;
$omnify_cs_formatted = $omnify_template_vars['omnify_cs_formatted'] ?? null;
$omnify_cs_formatted_reg = $omnify_template_vars['omnify_cs_formatted_reg'] ?? null;
$omnify_cs_regular = $omnify_template_vars['omnify_cs_regular'] ?? null;
$omnify_cs_sale = $omnify_template_vars['omnify_cs_sale'] ?? null;
$omnify_format_price = $omnify_template_vars['omnify_format_price'] ?? null;
$omnify_on_sale = $omnify_template_vars['omnify_on_sale'] ?? null;
$omnify_products_repo = $omnify_template_vars['omnify_products_repo'] ?? null;
$omnify_r = $omnify_template_vars['omnify_r'] ?? null;
$omnify_real_reviews = $omnify_template_vars['omnify_real_reviews'] ?? null;
$omnify_reviews_repo = $omnify_template_vars['omnify_reviews_repo'] ?? null;
$omnify_save_pct = $omnify_template_vars['omnify_save_pct'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_storefront_url = $omnify_template_vars['omnify_storefront_url'] ?? null;
$omnify_url_cs = $omnify_template_vars['omnify_url_cs'] ?? null;


$omnify_settings        = get_option('omnify_settings', []);
$omnify_settings        = is_array($omnify_settings) ? $omnify_settings : [];
$omnify_coupons_enabled = ! array_key_exists('enable_coupons', $omnify_settings) || ! empty($omnify_settings['enable_coupons']);
?>
<script>document.body.classList.add('omnify-cart-active');</script>
<div class="omnify-storefront-wrapper">
	<div class="omnify-cart-page" data-checkout-url="<?php echo esc_url($omnify_checkout_url); ?>">
		<?php do_action('omnify_before_cart'); ?>
		
		<!-- Steps Nav (Minimal Modern) -->
		<div class="omnify-cart-steps-minimal" aria-label="<?php esc_attr_e('Checkout progress', 'omnifywp-ecommerce'); ?>" style="<?php echo ( ! isset( $omnify_show_steps ) || $omnify_show_steps ) ? '' : 'display:none;'; ?>">
			<span class="omnify-step-item is-active">
				<span class="omnify-step-number">1</span>
				<span class="omnify-step-title"><?php esc_html_e('Shopping Cart', 'omnifywp-ecommerce'); ?></span>
			</span>
			<span class="omnify-step-divider">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
			</span>
			<span class="omnify-step-item">
				<span class="omnify-step-number">2</span>
				<span class="omnify-step-title"><?php esc_html_e('Checkout', 'omnifywp-ecommerce'); ?></span>
			</span>
			<span class="omnify-step-divider">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
			</span>
			<span class="omnify-step-item">
				<span class="omnify-step-number">3</span>
				<span class="omnify-step-title"><?php esc_html_e('Order Complete', 'omnifywp-ecommerce'); ?></span>
			</span>
		</div>

		<!-- Urgency countdown banner -->
		<?php if (! empty($omnify_settings['cart_countdown_enabled'])) : ?>
		<div class="omnify-cart-urgency-banner">
			<span class="omnify-urgency-text"><span class="omnify-urgency-icon"><span class="dashicons dashicons-clock" style="font-size:14px; width:14px; height:14px; line-height:1; vertical-align:middle;"></span></span> <?php esc_html_e('Hurry up! These products are limited, checkout within', 'omnifywp-ecommerce'); ?> <strong id="omnify-cart-countdown"><?php echo esc_html(sprintf('%02dm00s', intval($omnify_settings['cart_countdown_duration'] ?? 7))); ?></strong></span>
		</div>
		<?php endif; ?>

		<!-- Free shipping progress bar -->
		<?php
		$omnify_show_ship_meter = ! array_key_exists('cart_show_shipping_meter', $omnify_settings) || ! empty($omnify_settings['cart_show_shipping_meter']);
		if ($omnify_show_ship_meter && ! empty($omnify_settings['cart_free_shipping_enabled'])) :
		?>
		<div class="omnify-cart-shipping-progress" id="omnify-shipping-progress-wrapper">
			<div class="progress-bar-container">
				<div class="progress-bar-fill" id="omnify-shipping-progress-fill"></div>
			</div>
			<p class="progress-text" id="omnify-shipping-progress-text"></p>
		</div>
		<?php endif; ?>

		<!-- Empty Cart -->
		<div id="omnify-cart-empty" class="omnify-storefront-empty" style="display: none;">
			<p><?php esc_html_e('Your cart is empty.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="omnify-btn omnify-btn--primary">
				<?php esc_html_e('Browse Products', 'omnifywp-ecommerce'); ?>
			</a>
		</div>

		<!-- Cart Content Layout -->
		<div id="omnify-cart-content" class="omnify-cart-grid" style="display: none;">
			
			<!-- Left Column (Items Table + Coupon) -->
			<div class="omnify-cart-left-col">
				
				<!-- Selection Bar (Select All) -->
				<div class="omnify-cart-selection-bar">
					<label class="omnify-cart-select-all">
						<input type="checkbox" id="omnify-cart-select-all" class="omnify-checkbox" checked />
						<span><?php esc_html_e('Select all for checkout', 'omnifywp-ecommerce'); ?></span>
					</label>
					<span id="omnify-cart-selected-count" class="omnify-cart-selected-count"></span>
				</div>

				<!-- Column Titles -->
				<?php do_action('omnify_before_cart_table'); ?>
				<div class="omnify-cart-table-header">
					<span class="omnify-header-product"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></span>
					<span class="omnify-header-price"><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></span>
					<span class="omnify-header-qty"><?php esc_html_e('Quantity', 'omnifywp-ecommerce'); ?></span>
					<span class="omnify-header-subtotal"><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span>
					<span class="omnify-header-remove"></span>
				</div>

				<!-- Rendered items -->
				<div id="omnify-cart-items" class="omnify-cart-items"></div>
				<?php do_action('omnify_after_cart_table'); ?>

				<!-- Coupon Input (Moved to bottom of items list) -->
				<?php if ($omnify_coupons_enabled) : ?>
				<div class="omnify-cart-coupon" id="omnify-cart-coupon-wrapper">
					<!-- Input Form -->
					<div class="omnify-cart-coupon__form" id="omnify-cart-coupon-form">
						<input type="text" id="omnify-cart-coupon-input" placeholder="<?php esc_attr_e('Coupon code', 'omnifywp-ecommerce'); ?>" autocomplete="off" />
						<button type="button" id="omnify-cart-coupon-apply" class="omnify-btn-apply-coupon">
							<?php esc_html_e('Apply Coupon', 'omnifywp-ecommerce'); ?>
						</button>
					</div>
					
					<!-- Applied State -->
					<div class="omnify-cart-coupon__applied" id="omnify-cart-coupon-applied" style="display: none !important;">
						<div style="display: flex; align-items: center; gap: 8px;">
							<span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #16A34A; color: #fff; font-size: 11px;"><span class="dashicons dashicons-yes" style="font-size:12px; width:12px; height:12px; line-height:1;"></span></span>
							<span style="font-size: 11px; font-weight: 700; color: #16A34A; letter-spacing: 0.04em; text-transform: uppercase;"><?php esc_html_e('Applied', 'omnifywp-ecommerce'); ?>:</span>
							<strong id="omnify-applied-coupon-code" style="color: #0F172A; font-size: 13.5px; font-weight: 600; text-transform: uppercase;"></strong>
						</div>
						<button type="button" id="omnify-cart-coupon-remove" class="omnify-coupon-remove-btn">&times; <?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?></button>
					</div>
					
					<div id="omnify-cart-coupon-message" class="omnify-cart-coupon__message" style="display: none;"></div>
				</div>
				<?php endif; ?>

			</div>

			<!-- Right Column: Cart Totals Box -->
			<aside class="omnify-cart-summary">
				<?php do_action('omnify_before_cart_totals'); ?>
				<div class="omnify-cart-summary__header">
					<span><?php esc_html_e('Order Summary', 'omnifywp-ecommerce'); ?></span>
					<strong id="omnify-cart-total"><?php echo esc_html($omnify_format_price(0)); ?></strong>
				</div>

				<div class="omnify-cart-summary__rows">
					<!-- Subtotal -->
					<div class="omnify-cart-summary__row">
						<span><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span>
						<strong id="omnify-cart-subtotal"><?php echo esc_html($omnify_format_price(0)); ?></strong>
					</div>
					
					<!-- Coupon discount -->
					<div id="omnify-cart-discount-row" class="omnify-cart-summary__row omnify-cart-summary__row--discount" style="display: none;">
						<span id="omnify-cart-discount-label"><?php esc_html_e('Discount', 'omnifywp-ecommerce'); ?></span>
						<strong id="omnify-cart-discount">-<?php echo esc_html($omnify_format_price(0)); ?></strong>
					</div>

					<!-- Shipping Notice -->
					<div class="omnify-cart-summary__row omnify-cart-summary__row--shipping">
						<span class="shipment-title"><?php esc_html_e('Shipping', 'omnifywp-ecommerce'); ?></span>
						<span style="font-size: 13px; color: #94A3B8; font-weight: 500;"><?php esc_html_e('Calculated at checkout', 'omnifywp-ecommerce'); ?></span>
					</div>
					<!-- Shipping & Tax Estimator -->
					<div class="omnify-cart-estimator-box" id="omnify-cart-estimator-box">
						<button type="button" class="omnify-cart-accordion-trigger" id="omnify-estimator-toggle">
							<span style="display:flex; align-items:center; gap:8px;">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
								<span><?php esc_html_e('Estimate Shipping & Tax', 'omnifywp-ecommerce'); ?></span>
							</span>
							<svg class="chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
						</button>
						<div class="omnify-cart-accordion-body" id="omnify-estimator-content" style="display: none;">
							<div class="omnify-estimator-fields">
								<select id="omnify-estimate-country" class="omnify-select-field">
									<option value=""><?php esc_html_e('Select Country', 'omnifywp-ecommerce'); ?></option>
									<?php foreach (\Omnify\eCommerce\Support\Omnify_Locations::allowed_countries() as $omnify_c_code => $omnify_c_name) : ?>
										<option value="<?php echo esc_attr($omnify_c_code); ?>" <?php selected($omnify_c_code, 'US'); ?>><?php echo esc_html($omnify_c_name); ?></option>
									<?php endforeach; ?>
								</select>
								<div style="display: flex; gap: 8px;">
									<input type="text" id="omnify-estimate-state" class="omnify-input-field" placeholder="<?php esc_attr_e('State / Province', 'omnifywp-ecommerce'); ?>" style="flex: 1;" />
									<input type="text" id="omnify-estimate-zip" class="omnify-input-field" placeholder="<?php esc_attr_e('ZIP Code', 'omnifywp-ecommerce'); ?>" style="width: 100px;" />
								</div>
								<button type="button" id="omnify-estimate-btn" class="omnify-btn-estimate">
									<?php esc_html_e('Calculate Rates', 'omnifywp-ecommerce'); ?>
								</button>
								<div id="omnify-estimate-result" class="omnify-estimate-result" style="display: none;"></div>
							</div>
						</div>
					</div>

					<!-- Order Note -->
					<div class="omnify-cart-note-box" id="omnify-cart-note-box" style="<?php echo ( ! isset( $omnify_show_note ) || $omnify_show_note ) ? '' : 'display:none;'; ?>">
						<button type="button" class="omnify-cart-accordion-trigger" id="omnify-note-toggle">
							<span style="display:flex; align-items:center; gap:8px;">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
								<span><?php esc_html_e('Add order note or instructions', 'omnifywp-ecommerce'); ?></span>
							</span>
							<svg class="chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
						</button>
						<div class="omnify-cart-accordion-body" id="omnify-note-content" style="display: none;">
							<textarea id="omnify-cart-note-input" rows="3" placeholder="<?php esc_attr_e('Special delivery notes, gate code, or packaging requests...', 'omnifywp-ecommerce'); ?>"></textarea>
						</div>
					</div>
				</div>

				<!-- Total -->
				<div class="omnify-cart-totals-final">
					<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span>
					<strong id="omnify-cart-total-display"><?php echo esc_html($omnify_format_price(0)); ?></strong>
				</div>

				<!-- Checkout / Actions buttons (Minimal Modern) -->
				<button type="button" id="omnify-cart-checkout" class="omnify-btn omnify-cart-checkout-btn">
					<span><?php esc_html_e('Proceed to Checkout', 'omnifywp-ecommerce'); ?></span>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</button>
				
				<!-- Trust & Security Badges -->
				<?php
				$omnify_show_cart_trust = ! array_key_exists('cart_show_trust_badges', $omnify_settings) || ! empty($omnify_settings['cart_show_trust_badges']);
				if ($omnify_show_cart_trust) :
				?>
				<div class="omnify-cart-trust-badges">
					<div class="trust-badge-heading">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#18794E" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
						<span><?php esc_html_e('Guaranteed Safe & Secure Checkout', 'omnifywp-ecommerce'); ?></span>
					</div>
					<?php
					$omnify_cart_badges = method_exists($this, 'get_trust_badges') ? $this->get_trust_badges($omnify_settings) : [];
					?>
					<div class="trust-badge-icons">
						<?php foreach ($omnify_cart_badges as $omnify_cb) : 
							if ('ssl' === $omnify_cb['id']) {
								continue;
							}
						?>
							<span class="trust-pill" style="color: <?php echo esc_attr($omnify_cb['color']); ?>; background: <?php echo esc_attr($omnify_cb['bg']); ?>; border: 1px solid <?php echo esc_attr($omnify_cb['border']); ?>;"><?php echo esc_html($omnify_cb['label']); ?></span>
						<?php endforeach; ?>
					</div>
					<div class="trust-badge-features">
						<span><span class="dashicons dashicons-lock" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:4px;"></span>256-Bit Encryption</span>
						<span><span class="dashicons dashicons-download" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:4px;"></span>Instant Access</span>
					</div>
				</div>
				<?php endif; ?>

				<?php
				$omnify_show_continue = ! array_key_exists('cart_show_continue_shopping', $omnify_settings) || ! empty($omnify_settings['cart_show_continue_shopping']);
				if ($omnify_show_continue) :
				?>
				<div class="omnify-cart-bottom-actions">
					<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="omnify-continue-shopping-link">
						‹ <?php esc_html_e('Continue Shopping', 'omnifywp-ecommerce'); ?>
					</a>
					<button type="button" id="omnify-cart-clear" class="omnify-cart-clear-link">
						<?php esc_html_e('Clear cart', 'omnifywp-ecommerce'); ?>
					</button>
				</div>
				<?php endif; ?>
				<?php do_action('omnify_after_cart_totals'); ?>
			</aside>

		</div>

		<!-- Clear Cart Confirmation Modal -->
		<div id="omnify-cart-clear-modal" class="omnify-modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px;">
			<div class="omnify-modal-dialog" style="background:#fff; border-radius:12px; max-width:400px; width:100%; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); text-align:center;">
				<div style="width:48px; height:48px; border-radius:50%; background:#fef2f2; color:#dc2626; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px;">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
				</div>
				<h3 style="font-size:18px; font-weight:700; color:#0f172a; margin:0 0 8px;"><?php esc_html_e('Clear entire cart?', 'omnifywp-ecommerce'); ?></h3>
				<p style="font-size:14px; color:#64748b; margin:0 0 20px; line-height:1.5;"><?php esc_html_e('Are you sure you want to remove all items from your cart? This action cannot be undone.', 'omnifywp-ecommerce'); ?></p>
				<div style="display:flex; gap:10px; justify-content:center;">
					<button type="button" id="omnify-cart-clear-cancel" class="omnify-btn omnify-btn--secondary" style="flex:1;"><?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?></button>
					<button type="button" id="omnify-cart-clear-confirm" class="omnify-btn omnify-btn--danger" style="flex:1; background:#dc2626; color:#fff; border-color:#dc2626;"><?php esc_html_e('Yes, Clear Cart', 'omnifywp-ecommerce'); ?></button>
				</div>
			</div>
		</div>

		<!-- Cross Sells (You may be interested in...) -->
		<?php
		$omnify_show_cross_sells = ! array_key_exists('cart_show_cross_sells', $omnify_settings) || ! empty($omnify_settings['cart_show_cross_sells']);
		$omnify_products_repo = new \Omnify\eCommerce\Repositories\Omnify_Product_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
		$omnify_cross_sells = $omnify_products_repo->all(['limit' => 4, 'status' => 'publish']);
		if ($omnify_show_cross_sells && ! empty($omnify_cross_sells)) : ?>
		<div class="omnify-cart-cross-sells">
			<h3 class="omnify-cross-sells-title"><?php esc_html_e('You may also like', 'omnifywp-ecommerce'); ?></h3>
			<div class="omnify-cart-cross-sells-grid">
				<?php foreach ($omnify_cross_sells as $omnify_cs) : 
					$omnify_cs_display = $omnify_cs['sale_price'] ?? $omnify_cs['price'];
					$omnify_cs_regular = $omnify_cs['price'];
					$omnify_cs_sale = $omnify_cs['sale_price'] ?? null;
					$omnify_cs_formatted = isset($omnify_format_price) ? $omnify_format_price((float) $omnify_cs_display) : number_format((float) $omnify_cs_display, 2);
					$omnify_cs_formatted_reg = isset($omnify_format_price) ? $omnify_format_price((float) $omnify_cs_regular) : number_format((float) $omnify_cs_regular, 2);
					
					$omnify_url_cs = add_query_arg('omnify_product', $omnify_cs['slug'], $omnify_storefront_url);
					$omnify_on_sale = ! empty($omnify_cs_sale) && (float)$omnify_cs_sale < (float)$omnify_cs_regular;
					$omnify_save_pct = 0;
					if ($omnify_on_sale) {
						$omnify_save_pct = round((((float)$omnify_cs_regular - (float)$omnify_cs_sale) / (float)$omnify_cs_regular) * 100);
					}
					$omnify_cs_thumb = ! empty($omnify_cs['thumbnail_url']) ? $omnify_cs['thumbnail_url'] : '';
				?>
					<div class="omnify-cart-cross-sell-card">
						<a href="<?php echo esc_url($omnify_url_cs); ?>">
							<div class="card-image-wrap">
								<?php if ($omnify_on_sale) : ?>
									<span class="sale-badge">-<?php echo esc_html($omnify_save_pct); ?>%</span>
								<?php endif; ?>
								<span class="wishlist-icon" aria-label="<?php esc_attr_e('Wishlist', 'omnifywp-ecommerce'); ?>">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
								</span>
								<?php if (! empty($omnify_cs_thumb)) : ?>
									<img src="<?php echo esc_url($omnify_cs_thumb); ?>" alt="<?php echo esc_attr($omnify_cs['name']); ?>" />
								<?php else : ?>
									<div class="placeholder-icon">
										<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
									</div>
								<?php endif; ?>
							</div>
							<div class="card-info">
								<h4><?php echo esc_html($omnify_cs['name']); ?></h4>
								<div class="card-price">
									<?php if ($omnify_on_sale) : ?>
										<span class="regular-price"><?php echo esc_html($omnify_cs_formatted_reg); ?></span>
									<?php endif; ?>
									<span class="current-price"><?php echo esc_html($omnify_cs_formatted); ?></span>
								</div>
							</div>
						</a>
						<button type="button" class="omnify-cross-sell-add-btn omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_cs['id']); ?>" data-name="<?php echo esc_attr($omnify_cs['name']); ?>" data-price="<?php echo esc_attr($omnify_cs_display); ?>" data-display-price="<?php echo esc_attr($omnify_cs_formatted); ?>" data-image="<?php echo esc_attr(! empty($omnify_cs['thumbnail_url']) ? $omnify_cs['thumbnail_url'] : $omnify_cs_thumb); ?>" data-url="<?php echo esc_url($omnify_url_cs); ?>">
							+ <?php esc_html_e('Add', 'omnifywp-ecommerce'); ?>
						</button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php do_action('omnify_after_cart'); ?>
	</div>
</div>
