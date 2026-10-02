<?php
/**
 * Mini cart template.
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
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
		</span>
		<span class="omnify-mini-cart__label"><?php echo esc_html($omnify_label); ?></span>
		<span class="omnify-mini-cart__count omnify-cart-count">0</span>
	</button>

	<div class="omnify-mini-cart__panel" aria-hidden="true">
		<div class="omnify-mini-cart__header">
			<strong><?php esc_html_e('Your cart', 'omnifywp-ecommerce'); ?></strong>
			<button type="button" class="omnify-mini-cart__close" aria-label="<?php esc_attr_e('Close mini cart', 'omnifywp-ecommerce'); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
			</button>
		</div>

		<div class="omnify-mini-cart__items"></div>

		<div class="omnify-mini-cart__empty">
			<p><?php esc_html_e('Your cart is empty.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url($omnify_storefront_url); ?>" class="omnify-btn omnify-btn--secondary omnify-btn--block">
				<?php esc_html_e('Browse Products', 'omnifywp-ecommerce'); ?>
			</a>
		</div>

		<div class="omnify-mini-cart__footer">
			<div class="omnify-mini-cart__subtotal">
				<span><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span>
				<strong class="omnify-mini-cart__subtotal-value"><?php echo esc_html($omnify_format_price(0)); ?></strong>
			</div>
			<a href="<?php echo esc_url($omnify_cart_url); ?>" class="omnify-btn omnify-btn--secondary omnify-btn--block">
				<?php esc_html_e('View Cart', 'omnifywp-ecommerce'); ?>
			</a>
			<button type="button" class="omnify-btn omnify-btn--primary omnify-btn--block omnify-mini-cart__checkout">
				<?php esc_html_e('Checkout', 'omnifywp-ecommerce'); ?>
			</button>
		</div>
	</div>
</div>
