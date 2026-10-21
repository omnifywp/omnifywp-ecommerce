<?php
/**
 * Commercial Gutenberg Block Patterns for OmnifyWP eCommerce.
 *
 * Provides ready-to-use, high-converting eCommerce layouts for block themes,
 * FSE, and standard Gutenberg pages.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Storefront;

if (! defined('ABSPATH')) {
	exit;
}

final class Omnify_Block_Patterns {

	/**
	 * Register the Omnify block pattern category and patterns.
	 */
	public static function register(): void {
		if (! function_exists('register_block_pattern') || ! function_exists('register_block_pattern_category')) {
			return;
		}

		register_block_pattern_category('omnify', [
			'label' => __('OmnifyWP eCommerce', 'omnifywp-ecommerce'),
		]);

		self::register_patterns();
	}

	/**
	 * Register individual pre-designed commercial eCommerce patterns.
	 */
	private static function register_patterns(): void {
		// 1. Hero Storefront with Featured Product Card & 1-Click Buy
		register_block_pattern('omnify/hero-featured-product', [
			'title'       => __('Omnify Hero: Featured Product & 1-Click Buy', 'omnifywp-ecommerce'),
			'description' => __('Modern high-converting split hero section with bold headline, trust pillars, and featured product card.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify', 'featured', 'header'],
			'content'     => '<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:48px;padding-bottom:48px"><!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"42px","lineHeight":"1.15","fontWeight":"800"}}} -->
<h1 class="wp-block-heading" style="font-size:42px;font-weight:800;line-height:1.15">Next-Gen Digital Goods &amp; Hardware Store</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.6"},"color":{"text":"#64748b"}}} -->
<p class="has-text-color" style="color:#64748b;font-size:18px;line-height:1.6">Experience lightning-fast checkouts, cryptographically signed downloads, and seamless global multi-currency support.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/trust-badge-bar {"layout":"grid-2x2","badgeStyle":"emerald","maxWidth":480} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showComparePrice":true,"showRating":true,"showBuyButton":true,"buttonAction":"checkout","buttonLabel":"Buy Now (1-Click)","showFeaturePill":true,"maxWidth":380} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		]);

		// 2. High-Impact Flash Sale Banner with Free Shipping Goal
		register_block_pattern('omnify/flash-sale-scarcity-hero', [
			'title'       => __('Omnify Flash Sale & Scarcity Hub', 'omnifywp-ecommerce'),
			'description' => __('High-urgency promotional section with dynamic free shipping progress goal and trust badges.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify', 'featured'],
			'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}},"border":{"radius":"16px","width":"1px"}},"borderColor":"border-subtle","backgroundColor":"background"} -->
<div class="wp-block-group alignwide has-border-subtle-border-color has-background-background-color has-border-color" style="border-width:1px;border-radius:16px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px"><!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"28px","fontWeight":"800"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:28px;font-weight:800">🔥 Flash Sale: Save Up to 40% Today Only!</h2>
<!-- /wp:heading -->

<!-- wp:omnify/free-shipping-goal {"threshold":75,"showIcon":true,"showConfetti":true,"maxWidth":680} /-->

<!-- wp:omnify/product-grid {"perPage":3,"columns":3,"showFilters":false,"showSearch":false,"showFlashSale":true,"flashSaleText":"⚡ Flash Sale Ends Tonight • Code: FLASH40","flashSaleCoupon":"FLASH40"} /--></div>
<!-- /wp:group -->',
		]);

		// 3. Complete Frictionless 2-Column Checkout Page
		register_block_pattern('omnify/complete-checkout-funnel', [
			'title'       => __('Omnify Complete High-Conversion Checkout', 'omnifywp-ecommerce'),
			'description' => __('Two-column checkout flow featuring cart reservation countdown timer, order bump upsell, and security trust badges.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify'],
			'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:48px"><!-- wp:omnify/free-shipping-goal {"threshold":60,"showIcon":true,"maxWidth":800} /-->

<!-- wp:omnify/checkout {"layout":"2-col","showTrustBadges":"1","showOrderBump":"1","orderBumpTitle":"Exclusive One-Time Offer","showScarcityCountdown":true,"countdownMinutes":15,"showGuarantees":true} /-->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","maxWidth":800} /--></div>
<!-- /wp:group -->',
		]);

		// 4. Cart Page with Cross-Sells and Gamified Shipping Progress
		register_block_pattern('omnify/gamified-cart-showcase', [
			'title'       => __('Omnify Gamified Cart with Shipping Goal', 'omnifywp-ecommerce'),
			'description' => __('Conversion-boosted cart page with instant free shipping progress tracker and dynamic cross-sells.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify'],
			'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"36px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:36px"><!-- wp:omnify/free-shipping-goal {"threshold":50,"showIcon":true,"showConfetti":true,"maxWidth":720} /-->

<!-- wp:omnify/cart {"showShippingMeter":true,"shippingThreshold":50,"showTrustBadges":true,"showCrossSells":true,"showEmptyButton":true} /--></div>
<!-- /wp:group -->',
		]);

		// 5. Complete Customer Self-Service Portal & Digital Downloads Hub
		register_block_pattern('omnify/customer-account-hub', [
			'title'       => __('Omnify Customer Account & Downloads Hub', 'omnifywp-ecommerce'),
			'description' => __('Professional self-service dashboard with greeting, orders, licenses, HMAC downloads, and order tracking.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify'],
			'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/customer-portal {"showStats":true,"showGreeting":true,"showLogout":true,"showInvoices":true,"showWishlistTab":true} /-->

<!-- wp:spacer {"height":"32px"} -->
<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:omnify/order-tracking {"title":"Track an Order in Real-Time","subheading":"Enter your Order ID and billing email below for immediate delivery progress and courier dispatch details."} /--></div>
<!-- /wp:group -->',
		]);

		// 6. Sticky Mobile Add to Cart & Trust Strip
		register_block_pattern('omnify/product-conversion-booster', [
			'title'       => __('Omnify Product Conversion Booster Kit', 'omnifywp-ecommerce'),
			'description' => __('Sticky Add to Cart bar paired with a responsive Trust Badge Strip and 1-click Order Bump checkout trigger.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify'],
			'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"16px","bottom":"16px"}}}} -->
<div class="wp-block-group alignfull" style="padding-top:16px;padding-bottom:16px"><!-- wp:omnify/sticky-add-to-cart {"deviceVisibility":"mobile","showQuantity":true,"buttonLabel":"Quick Buy"} /-->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","showBorder":true} /--></div>
<!-- /wp:group -->',
		]);

		// 7. Catalog Landing Page with Quick Filters and Currency Switcher
		register_block_pattern('omnify/store-catalog-header', [
			'title'       => __('Omnify Storefront Catalog with Multicurrency', 'omnifywp-ecommerce'),
			'description' => __('Full storefront catalog featuring live search, instant category pills, and multi-currency dropdown.', 'omnifywp-ecommerce'),
			'categories'  => ['omnify'],
			'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:48px"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"width":"70%"} -->
<div class="wp-block-column" style="flex-basis:70%"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"28px","fontWeight":"800"}}} -->
<h2 class="wp-block-heading" style="font-size:28px;font-weight:800">Shop Catalog</h2>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"30%"} -->
<div class="wp-block-column" style="flex-basis:30%"><!-- wp:omnify/currency-switcher {"displayStyle":"dropdown","showSymbol":true,"align":"right"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:omnify/product-grid {"perPage":8,"columns":4,"showSearch":true,"showFilters":true,"showQuickFilters":true,"showSort":true} /--></div>
<!-- /wp:group -->',
		]);
	}
}
