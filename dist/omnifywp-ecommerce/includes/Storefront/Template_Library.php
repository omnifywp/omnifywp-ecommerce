<?php
/**
 * Modern In-Editor Cloud Template Library & Starter Kits for OmnifyWP eCommerce.
 *
 * Provides ready-to-insert complete niche store starter pages, sales funnels,
 * and storefront sections directly inside the Gutenberg block editor.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Storefront;

if (! defined('ABSPATH')) {
	exit;
}

final class Omnify_Template_Library {

	/**
	 * Register REST endpoint or localization data for the template library.
	 */
	public static function register(): void {
		add_filter('omnify_block_editor_data', [self::class, 'inject_templates_data']);
	}

	/**
	 * Return the library of pre-designed niche eCommerce templates.
	 *
	 * @return array<int, array{id: string, title: string, category: string, badge: string, description: string, thumbnail: string, content: string}>
	 */
	public static function get_library_templates(): array {
		return [
			[
				'id'          => 'digital-saas-hero',
				'title'       => __('Digital Software & License Key Funnel', 'omnifywp-ecommerce'),
				'category'    => 'funnels',
				'badge'       => 'High Converting',
				'description' => __('Split-screen digital software landing page with HMAC signed downloads, pricing comparison, and 1-click checkout.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'laptop',
				'content'     => '<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:48px;padding-bottom:48px"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"44px","fontWeight":"800","lineHeight":"1.15"}}} -->
<h1 class="wp-block-heading" style="font-size:44px;font-weight:800;line-height:1.15">Enterprise SaaS &amp; Digital Downloads Engine</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.6"},"color":{"text":"#64748b"}}} -->
<p class="has-text-color" style="color:#64748b;font-size:18px;line-height:1.6">Deliver cryptographically signed downloads, instant license keys, and recurring memberships with zero platform transaction fees.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","showBorder":true} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showComparePrice":true,"showRating":true,"showBuyButton":true,"buttonAction":"checkout","buttonLabel":"Instant Access (1-Click)","showFeaturePill":true,"maxWidth":400} /-->

<!-- wp:omnify/order-bump {"title":"Add Lifetime Priority Support & Updates","badgeText":"SAVE 50%","discountVal":50,"discountType":"percentage"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			],
			[
				'id'          => 'fashion-apparel-grid',
				'title'       => __('Luxury Fashion & Apparel Showcase', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Best Seller',
				'description' => __('Full-width luxury fashion store catalog with live free shipping goal meter, filter pills, and sticky conversion cart.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'tag',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/free-shipping-goal {"threshold":75,"showIcon":true,"showConfetti":true,"maxWidth":840} /-->

<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"width":"70%"} -->
<div class="wp-block-column" style="flex-basis:70%"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"32px","fontWeight":"800"}}} -->
<h2 class="wp-block-heading" style="font-size:32px;font-weight:800">Summer Collection</h2>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"30%"} -->
<div class="wp-block-column" style="flex-basis:30%"><!-- wp:omnify/currency-switcher {"displayStyle":"dropdown","showSymbol":true,"align":"right"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:omnify/product-grid {"perPage":8,"columns":4,"showSearch":true,"showFilters":true,"showQuickFilters":true,"showSort":true} /-->

<!-- wp:omnify/sticky-add-to-cart {"deviceVisibility":"mobile","showThumbnail":true,"showPrice":true,"showQuantity":true} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'high-converting-checkout-v3',
				'title'       => __('Frictionless 2-Column Split Checkout', 'omnifywp-ecommerce'),
				'category'    => 'checkout',
				'badge'       => '3x Conversions',
				'description' => __('Modern split checkout with cart reservation timer, order bump upsell, and security guarantee seal.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'cart',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:48px"><!-- wp:omnify/free-shipping-goal {"threshold":50,"showIcon":true,"maxWidth":880} /-->

<!-- wp:omnify/checkout {"layout":"2-col","showTrustBadges":"1","showOrderBump":"1","orderBumpTitle":"Exclusive VIP Membership Offer","showScarcityCountdown":true,"countdownMinutes":15,"showGuarantees":true} /-->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","maxWidth":880} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'self-service-account-portal',
				'title'       => __('Customer Downloads & License Dashboard', 'omnifywp-ecommerce'),
				'category'    => 'account',
				'badge'       => 'Self Service',
				'description' => __('Complete customer self-service hub with greeting, invoice downloads, wishlist tab, and live courier tracking.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'admin-users',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/customer-portal {"showStats":true,"showGreeting":true,"showLogout":true,"showInvoices":true,"showWishlistTab":true} /-->

<!-- wp:spacer {"height":"32px"} -->
<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:omnify/order-tracking {"title":"Track an Order in Real-Time","subheading":"Enter your Order ID and billing email below for real-time delivery and dispatch tracking."} /--></div>
<!-- /wp:group -->',
			],
		];
	}

	/**
	 * Inject templates into the localized omnifyBlocks JS payload.
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function inject_templates_data(array $data): array {
		$data['templateLibrary'] = self::get_library_templates();
		return $data;
	}
}
