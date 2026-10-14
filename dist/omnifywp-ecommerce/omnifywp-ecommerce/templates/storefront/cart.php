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
<div class="omnify-storefront-wrapper">
	<div class="omnify-cart-page" data-checkout-url="<?php echo esc_url($omnify_checkout_url); ?>">
		
		<!-- Steps Nav -->
		<div class="omnify-cart-steps">
			<div class="omnify-cart-step active">
				<span class="step-num">01</span>
				<div class="step-info">
					<strong>Shopping Cart</strong>
					<span>Manage Your Items List</span>
				</div>
			</div>
			<div class="omnify-cart-step-separator"></div>
			<div class="omnify-cart-step">
				<span class="step-num">02</span>
				<div class="step-info">
					<strong>Checkout Details</strong>
					<span>Checkout Your Items List</span>
				</div>
			</div>
			<div class="omnify-cart-step-separator"></div>
			<div class="omnify-cart-step">
				<span class="step-num">03</span>
				<div class="step-info">
					<strong>Order Complete</strong>
					<span>Review Your Order</span>
				</div>
			</div>
		</div>

		<!-- Urgency countdown banner -->
		<?php if (! empty($omnify_settings['cart_countdown_enabled'])) : ?>
		<div class="omnify-cart-urgency-banner">
			<span>⚡ Hurry up! these products are limited, checkout within <strong id="omnify-cart-countdown"><?php echo esc_html(sprintf('%02dm00s', intval($omnify_settings['cart_countdown_duration'] ?? 7))); ?></strong></span>
		</div>
		<?php endif; ?>

		<!-- Free shipping progress bar -->
		<?php if (! empty($omnify_settings['cart_free_shipping_enabled'])) : ?>
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
			
			<!-- Left Column (Items Table + Coupon + Reviews) -->
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
				<div class="omnify-cart-table-header">
					<span style="grid-column: 1 / span 4;"><?php esc_html_e('PRODUCT', 'omnifywp-ecommerce'); ?></span>
					<span><?php esc_html_e('PRICE', 'omnifywp-ecommerce'); ?></span>
					<span style="text-align: center;"><?php esc_html_e('QUANTITY', 'omnifywp-ecommerce'); ?></span>
					<span style="text-align: right;"><?php esc_html_e('SUBTOTAL', 'omnifywp-ecommerce'); ?></span>
				</div>

				<!-- Rendered items -->
				<div id="omnify-cart-items" class="omnify-cart-items"></div>

				<!-- Coupon Input (Moved to bottom of items list) -->
				<?php if ($omnify_coupons_enabled) : ?>
				<div class="omnify-cart-coupon" id="omnify-cart-coupon-wrapper">
					<!-- Input Form -->
					<div class="omnify-cart-coupon__form" id="omnify-cart-coupon-form">
						<input type="text" id="omnify-cart-coupon-input" placeholder="<?php esc_attr_e('Coupon code', 'omnifywp-ecommerce'); ?>" autocomplete="off" />
						<button type="button" id="omnify-cart-coupon-apply">
							<?php esc_html_e('Apply Coupon', 'omnifywp-ecommerce'); ?>
						</button>
					</div>
					
					<!-- Applied State -->
					<div class="omnify-cart-coupon__applied" id="omnify-cart-coupon-applied" style="display: none;">
						<div>
							<span style="font-size:10px; font-weight:700; color:var(--cart-success); letter-spacing:0.05em; display:block;">APPLIED</span>
							<strong id="omnify-applied-coupon-code" style="color:var(--cart-dark); font-size:14px;"></strong>
						</div>
						<button type="button" id="omnify-cart-coupon-remove" class="omnify-cart-remove" style="font-size: 14px; font-weight:700;">✕ Remove</button>
					</div>
					
					<div id="omnify-cart-coupon-message" class="omnify-cart-coupon__message" style="display: none;"></div>
				</div>
				<?php endif; ?>

				<!-- Dynamic Customer Reviews (Loads actual database reviews, hides section if empty) -->
				<?php
				$omnify_reviews_repo = new \Omnify\eCommerce\Repositories\Omnify_Review_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
				$omnify_real_reviews = $omnify_reviews_repo->all(['status' => 'approved', 'per_page' => 3]);
				if (! empty($omnify_real_reviews)) : ?>
				<div class="omnify-cart-reviews">
					<h3><?php esc_html_e('Reviews From Customers', 'omnifywp-ecommerce'); ?></h3>
					<div class="omnify-cart-reviews-grid">
						<?php foreach ($omnify_real_reviews as $omnify_r) : ?>
							<div class="omnify-cart-review-card">
								<p>"<?php echo esc_html(wp_trim_words($omnify_r['review_content'] ?? '', 20)); ?>"</p>
								<div class="review-stars">
									<?php echo esc_html(str_repeat('★', (int)$omnify_r['rating']) . str_repeat('☆', 5 - (int)$omnify_r['rating'])); ?>
								</div>
								<strong><?php echo esc_html($omnify_r['customer_name'] ?? 'Anonymous'); ?></strong>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- Right Column: Cart Totals Box -->
			<aside class="omnify-cart-summary">
				<div class="omnify-cart-summary__header">
					<span><?php esc_html_e('Cart Totals', 'omnifywp-ecommerce'); ?></span>
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
				</div>

				<!-- Shipment -->
				<div class="omnify-cart-shipment-row">
					<span class="shipment-title"><?php esc_html_e('Shipment', 'omnifywp-ecommerce'); ?></span>
					<div class="shipment-details">
						<strong>Flat rate: <?php echo esc_html($omnify_format_price(19.00)); ?></strong>
						<span>Shipping to NY.</span>
						<a href="#" onclick="event.preventDefault(); document.getElementById('omnify-cart-checkout').click();"><?php esc_html_e('Change address', 'omnifywp-ecommerce'); ?></a>
					</div>
				</div>

				<!-- Total -->
				<div class="omnify-cart-totals-final">
					<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span>
					<strong id="omnify-cart-total-display"><?php echo esc_html($omnify_format_price(0)); ?></strong>
				</div>

				<!-- Checkout / Actions buttons -->
				<button type="button" id="omnify-cart-checkout">
					<?php esc_html_e('Proceed to Checkout', 'omnifywp-ecommerce'); ?>
				</button>
				
				<button type="button" id="omnify-cart-clear">
					<?php esc_html_e('Clear Cart', 'omnifywp-ecommerce'); ?>
				</button>
			</aside>

		</div>

		<!-- Cross Sells (You may be interested in...) -->
		<?php
		$omnify_products_repo = new \Omnify\eCommerce\Repositories\Omnify_Product_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
		$omnify_cross_sells = $omnify_products_repo->all(['limit' => 4, 'status' => 'publish']);
		if (! empty($omnify_cross_sells)) : ?>
		<div class="omnify-cart-cross-sells">
			<h3><?php esc_html_e('You may be interested in...', 'omnifywp-ecommerce'); ?></h3>
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
				?>
					<div class="omnify-cart-cross-sell-card">
						<a href="<?php echo esc_url($omnify_url_cs); ?>">
							<div class="card-image-wrap">
								<?php if ($omnify_on_sale) : ?>
									<span class="sale-badge">-<?php echo esc_html($omnify_save_pct); ?>%</span>
								<?php endif; ?>
								<span class="wishlist-icon">♡</span>
								<?php if (! empty($omnify_cs['thumbnail_url'])) : ?>
									<img src="<?php echo esc_url($omnify_cs['thumbnail_url']); ?>" alt="<?php echo esc_attr($omnify_cs['name']); ?>" />
								<?php else : ?>
									<div class="placeholder-icon">📦</div>
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
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div>
</div>
