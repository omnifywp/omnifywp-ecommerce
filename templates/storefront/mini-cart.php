<?php
/**
 * Mini cart template (Slide-Out Cart Drawer).
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_align = $omnify_template_vars['omnify_align'] ?? null;
$omnify_atts = $omnify_template_vars['omnify_atts'] ?? null;
$omnify_cart_url = $omnify_template_vars['omnify_cart_url'] ?? null;
$omnify_checkout_url = $omnify_template_vars['omnify_checkout_url'] ?? null;
$omnify_format_price = $omnify_template_vars['omnify_format_price'] ?? null;
$omnify_label = $omnify_template_vars['omnify_label'] ?? null;
$omnify_storefront_url = $omnify_template_vars['omnify_storefront_url'] ?? null;

$omnify_label = isset($omnify_atts['label']) ? sanitize_text_field((string) $omnify_atts['label']) : __('Cart', 'omnifywp-ecommerce');
$omnify_align = isset($omnify_atts['align']) && in_array((string) $omnify_atts['align'], ['left', 'right'], true) ? (string) $omnify_atts['align'] : 'right';
?>
<div class="omnify-mini-cart omnify-mini-cart--<?php echo esc_attr($omnify_align); ?>" data-checkout-url="<?php echo esc_url($omnify_checkout_url); ?>" data-cart-url="<?php echo esc_url($omnify_cart_url); ?>">
	<button type="button" class="omnify-mini-cart__toggle" aria-expanded="false">
		<span class="omnify-mini-cart__icon" aria-hidden="true">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
		</span>
		<span class="omnify-mini-cart__label"><?php echo esc_html($omnify_label); ?></span>
		<span class="omnify-mini-cart__count omnify-cart-count">0</span>
	</button>

	<div class="omnify-mini-cart__panel" aria-hidden="true">
		
		<!-- Cart Header -->
		<div class="omnify-mini-cart__header">
			<div class="omnify-mini-cart__header-title">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
				<strong><?php esc_html_e('Your Bag', 'omnifywp-ecommerce'); ?> (<span class="omnify-cart-count">0</span>)</strong>
			</div>
			<button type="button" class="omnify-mini-cart__close" aria-label="<?php esc_attr_e('Close cart drawer', 'omnifywp-ecommerce'); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
			</button>
		</div>

		<!-- Free Shipping Progress Bar -->
		<div class="omnify-mini-cart-shipping-meter" id="omnify-mini-cart-shipping-meter">
			<div class="omnify-mini-cart-shipping-text">
				<span class="omnify-mini-shipping-msg"><?php esc_html_e('Free shipping unlocked on orders over $50', 'omnifywp-ecommerce'); ?></span>
			</div>
			<div class="omnify-mini-cart-shipping-bar">
				<div class="omnify-mini-cart-shipping-fill" style="width: 100%;"></div>
			</div>
		</div>

		<!-- Items Container -->
		<div class="omnify-mini-cart__items"></div>

		<!-- Empty Cart State -->
		<div class="omnify-mini-cart__empty">
			<div class="omnify-mini-cart__empty-icon">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			</div>
			<p class="omnify-mini-cart__empty-title"><?php esc_html_e('Your bag is empty', 'omnifywp-ecommerce'); ?></p>
			<p class="omnify-mini-cart__empty-desc"><?php esc_html_e('Browse the collection and add your favorite items.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="omnify-mini-cart__explore-btn">
				<?php esc_html_e('Explore Products', 'omnifywp-ecommerce'); ?> &rarr;
			</a>
		</div>

		<!-- Drawer Footer & Action Buttons -->
		<div class="omnify-mini-cart__footer">
			<div class="omnify-mini-cart__subtotal">
				<span class="omnify-mini-cart__subtotal-label"><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span>
				<strong class="omnify-mini-cart__subtotal-value"><?php echo esc_html($omnify_format_price(0)); ?></strong>
			</div>
			<div class="omnify-mini-cart__tax-notice">
				<?php esc_html_e('Taxes and shipping calculated at checkout.', 'omnifywp-ecommerce'); ?>
			</div>

			<!-- 1-Page Checkout Primary Button -->
			<button type="button" class="omnify-btn omnify-mini-cart__checkout">
				<span><?php esc_html_e('Proceed to Checkout', 'omnifywp-ecommerce'); ?></span>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
			</button>

			<div class="omnify-mini-cart__view-cart-wrap">
				<a href="<?php echo esc_url($omnify_cart_url); ?>" class="omnify-mini-cart__cart-link">
					<?php esc_html_e('View Shopping Cart', 'omnifywp-ecommerce'); ?>
				</a>
			</div>
		</div>

	</div>
</div>
