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
			[
				'id'          => 'tech-electronics-superstore',
				'title'       => __('Cyber Tech & Audio Superstore', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'High Performance',
				'description' => __('Ultra-fast tech catalog with dark mode hero, filterable 4-column product grid, and instant floating cart.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'desktop',
				'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"40px","bottom":"60px"}},"color":{"background":"#0D1B12","text":"#ffffff"}}} -->
<div class="wp-block-group alignfull has-text-color has-background" style="background-color:#0D1B12;color:#ffffff;padding-top:40px;padding-bottom:60px"><!-- wp:heading {"level":1,"textAlign":"center","style":{"typography":{"fontSize":"42px","fontWeight":"900"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:42px;font-weight:900">Next-Gen Audio &amp; Hardware</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"},"color":{"text":"#94a3b8"}}} -->
<p class="has-text-align-center has-text-color" style="color:#94a3b8;font-size:16px">Engineered for pure acoustics. 2-Year Warranty and Express Worldwide Shipping included.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","showBorder":false} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/product-grid {"columns":"4","perPage":8,"showSearch":"1","showFilters":"1","showSort":"1","showQuickView":true,"hoverEffect":"lift"} /-->

<!-- wp:omnify/mini-cart {"align":"right","showShippingMeter":true,"shippingThreshold":100} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'wellness-health-bundle',
				'title'       => __('Health & Organic Supplements Lander', 'omnifywp-ecommerce'),
				'category'    => 'funnels',
				'badge'       => 'Conversion Optimized',
				'description' => __('High-converting wellness landing page featuring doctor certification trust badges, customer star ratings, and bundle bumps.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'heart',
				'content'     => '<!-- wp:columns {"align":"wide","verticalAlignment":"center","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center" style="padding-top:48px;padding-bottom:48px"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:omnify/product-badge {"badgeType":"sale","customText":"100% ORGANIC &amp; CLINICALLY TESTED"} /-->

<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"38px","fontWeight":"800"}}} -->
<h1 class="wp-block-heading" style="font-size:38px;font-weight:800">Peak Daily Vitality Complex</h1>
<!-- /wp:heading -->

<!-- wp:omnify/product-rating {"starColor":"#f59e0b","showCount":true} /-->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.7"},"color":{"text":"#475569"}}} -->
<p class="has-text-color" style="color:#475569;font-size:16px;line-height:1.7">Bioavailable vitamins, adaptogenic mushrooms, and clean electrolytes crafted to sustain clean energy without crashes.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","showGuarantees":true,"showReturns":true} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:omnify/product-card {"cardLayout":"vertical","showBuyButton":true,"buttonAction":"checkout","buttonLabel":"Claim 30-Day Supply"} /-->

<!-- wp:omnify/order-bump {"title":"Add Nighttime Deep Sleep Formula (50% OFF)","discountVal":50,"badgeText":"VIP UPGRADE"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			],
			[
				'id'          => 'artisan-coffee-roasters',
				'title'       => __('Artisanal Coffee & Roasters Shop', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Artisanal',
				'description' => __('Warm, lifestyle storefront for craft roasters, apparel, or gourmet foods with free shipping goal and currency switcher.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'coffee',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"32px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:32px"><!-- wp:omnify/free-shipping-goal {"threshold":45,"showConfetti":true} /-->

<!-- wp:omnify/product-grid {"columns":"3","perPage":6,"showQuickFilters":"1","showSort":"1","layout":"grid"} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'accordion-multistep-checkout',
				'title'       => __('Accordion Multi-Step Checkout Funnel', 'omnifywp-ecommerce'),
				'category'    => 'checkout',
				'badge'       => 'Enterprise',
				'description' => __('Step-by-step accordion checkout flow that reduces buyer cognitive load and eliminates checkout abandonment.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'forms',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:48px"><!-- wp:omnify/checkout {"layout":"2-col","checkoutMode":"accordion-steps","showScarcityCountdown":true,"countdownMinutes":12,"showGuarantees":true,"showTrustBadges":"1"} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'dedicated-digital-vault',
				'title'       => __('Cryptographic Digital Downloads Vault', 'omnifywp-ecommerce'),
				'category'    => 'account',
				'badge'       => 'HMAC Secured',
				'description' => __('Dedicated file delivery locker for premium themes, plugins, ebooks, and video assets with signed links and download limits.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'lock',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/customer-downloads {"showSearch":true,"showVersion":true,"showFileSize":true,"showExpiryNotice":true,"showDownloadCount":true} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'minimalist-boutique-catalog',
				'title'       => __('Minimalist Luxury Boutique Catalog', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Editorial Design',
				'description' => __('Understated, minimalist fashion and interior design catalog with masonry columns, filter pills, and sleek currency switcher.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'art',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"40px","bottom":"60px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:40px;padding-bottom:60px"><!-- wp:heading {"level":1,"textAlign":"center","style":{"typography":{"fontSize":"36px","fontWeight":"300","letterSpacing":"1px"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:36px;font-weight:300;letter-spacing:1px">STUDIO COLLECTION 2026</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"},"color":{"text":"#64748b"}}} -->
<p class="has-text-align-center has-text-color" style="color:#64748b;font-size:14px">Thoughtfully designed garments and objects for everyday ritual.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/currency-switcher {"displayStyle":"pills","showSymbol":true,"align":"center"} /-->

<!-- wp:omnify/product-grid {"columns":"3","perPage":9,"layout":"masonry","showSearch":false,"showFilters":true,"showQuickFilters":true} /-->

<!-- wp:omnify/sticky-add-to-cart {"deviceVisibility":"all","showThumbnail":true,"showPrice":true} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'digital-course-creator',
				'title'       => __('Masterclass Course & Digital Academy Funnel', 'omnifywp-ecommerce'),
				'category'    => 'funnels',
				'badge'       => 'High AOV',
				'description' => __('High-ticket masterclass sales funnel with customer star rating proof, trust badges, and 1-click checkout order bump.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'welcome-learn-more',
				'content'     => '<!-- wp:columns {"align":"wide","verticalAlignment":"center","style":{"spacing":{"padding":{"top":"48px","bottom":"56px"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center" style="padding-top:48px;padding-bottom:56px"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:omnify/product-badge {"badgeType":"new","customText":"ENROLLMENT OPEN"} /-->

<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"40px","fontWeight":"800","lineHeight":"1.2"}}} -->
<h1 class="wp-block-heading" style="font-size:40px;font-weight:800;line-height:1.2">Full-Stack AI Engineering Masterclass</h1>
<!-- /wp:heading -->

<!-- wp:omnify/product-rating {"starColor":"#f59e0b","showCount":true,"rating":4.9,"reviewCount":312} /-->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px","lineHeight":"1.6"},"color":{"text":"#475569"}}} -->
<p class="has-text-color" style="color:#475569;font-size:17px;line-height:1.6">Master real-world autonomous agents, prompt architecture, and LLM inference. Includes lifetime access, certificate of completion, and private Discord community.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:omnify/product-card {"cardLayout":"vertical","showComparePrice":true,"showRating":true,"showBuyButton":true,"buttonAction":"checkout","buttonLabel":"Enroll Now (Instant Access)"} /-->

<!-- wp:omnify/order-bump {"title":"Add 1-on-1 Code Review & Mentorship Session","discountVal":40,"badgeText":"SAVE 40% TODAY"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			],
			[
				'id'          => 'beauty-cosmetics-glam',
				'title'       => __('Organic Beauty & Cosmetics Collection', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Trending',
				'description' => __('Glow-up skincare storefront with free shipping milestone meter, responsive 4-column product grid, and slide-out mini cart.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'smiley',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/free-shipping-goal {"threshold":60,"showIcon":true,"showConfetti":true} /-->

<!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontSize":"34px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:34px;font-weight:700">Clean Botanical Skincare</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px"},"color":{"text":"#64748b"}}} -->
<p class="has-text-align-center has-text-color" style="color:#64748b;font-size:15px">100% cruelty-free, vegan certified, and packaged in recycled amber glass.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/product-grid {"columns":"4","perPage":8,"showFilters":true,"showQuickFilters":true,"showSort":true} /-->

<!-- wp:omnify/mini-cart {"showShippingMeter":true,"shippingThreshold":60} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'single-product-hero-landing',
				'title'       => __('Single Product High-Impact Sales Page', 'omnifywp-ecommerce'),
				'category'    => 'funnels',
				'badge'       => 'Atomic InnerBlocks',
				'description' => __('Direct-to-consumer hero landing page assembled with Omnify atomic micro-blocks: title, price pill, star ratings, and button.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'star-filled',
				'content'     => '<!-- wp:columns {"align":"wide","verticalAlignment":"center","style":{"spacing":{"padding":{"top":"56px","bottom":"56px"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center" style="padding-top:56px;padding-bottom:56px"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:omnify/product-image {"aspectRatio":"1/1","showHoverFlip":true,"borderRadius":12} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:omnify/product-badge {"badgeType":"sale","customText":"BEST SELLER"} /-->

<!-- wp:omnify/product-title {"tag":"h1","fontSize":36,"linkToProduct":false} /-->

<!-- wp:omnify/product-rating {"starColor":"#f59e0b","showCount":true} /-->

<!-- wp:omnify/product-price {"showSavingsBadge":true,"fontSize":26} /-->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px","lineHeight":"1.7"},"color":{"text":"#64748b"}}} -->
<p class="has-text-color" style="color:#64748b;font-size:15px;line-height:1.7">Precision crafted with aircraft-grade aluminum, IP68 water resistance, and 30-day battery life. Designed to endure any journey.</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/product-button {"buttonAction":"checkout","buttonLabel":"Order Now — Free Shipping","showQuantity":true} /-->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","showGuarantees":true} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			],
			[
				'id'          => 'flash-sale-deal-zone',
				'title'       => __('Flash Sale Deal Zone with Urgency Bar', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Limited Time',
				'description' => __('High-urgency sales event template with countdown timer, free shipping milestone bar, and sticky add to cart bar.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'clock',
				'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}},"color":{"background":"#dc2626","text":"#ffffff"}}} -->
<div class="wp-block-group alignfull has-text-color has-background" style="background-color:#dc2626;color:#ffffff;padding-top:24px;padding-bottom:24px"><!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontSize":"26px","fontWeight":"900"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:26px;font-weight:900">⚡ 24-HOUR FLASH SALE — UP TO 60% OFF</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}}} -->
<p class="has-text-align-center" style="font-size:14px">Use coupon code <strong>FLASH60</strong> at checkout. Offer expires midnight sharp!</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"32px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:32px;padding-bottom:48px"><!-- wp:omnify/free-shipping-goal {"threshold":50,"showConfetti":true} /-->

<!-- wp:omnify/product-grid {"columns":"4","perPage":8,"showSort":true,"showQuickFilters":true} /-->

<!-- wp:omnify/sticky-add-to-cart {"deviceVisibility":"mobile"} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'agency-license-pricing-table',
				'title'       => __('Agency Developer Multi-Tier Licensing', 'omnifywp-ecommerce'),
				'category'    => 'funnels',
				'badge'       => 'Developer Tier',
				'description' => __('3-tier software and plugin licensing table with feature checklists, instant license generation, and digital file access.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'admin-network',
				'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:48px;padding-bottom:48px"><!-- wp:heading {"level":1,"textAlign":"center","style":{"typography":{"fontSize":"38px","fontWeight":"800"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:38px;font-weight:800">Choose the Plan That Fits Your Growth</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"},"color":{"text":"#64748b"}}} -->
<p class="has-text-align-center has-text-color" style="color:#64748b;font-size:16px">All plans include HMAC-signed file streaming, automated webhook sync, and 30-day money-back guarantee.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"32px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:32px"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showRating":true,"showBuyButton":true,"buttonLabel":"Get Starter"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showRating":true,"showBuyButton":true,"buttonLabel":"Get Professional"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showRating":true,"showBuyButton":true,"buttonLabel":"Get Agency Unlimited"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","showGuarantees":true} /--></div>
<!-- /wp:group -->',
			],
			[
				'id'          => 'luxury-watch-jewelry',
				'title'       => __('Haute Horlogerie & Luxury Timepieces', 'omnifywp-ecommerce'),
				'category'    => 'stores',
				'badge'       => 'Luxury Edition',
				'description' => __('Prestige watch and jewelry boutique with dark elegance theme harmony, multi-currency switcher, and verified courier tracking.', 'omnifywp-ecommerce'),
				'thumbnail'   => 'awards',
				'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"56px","bottom":"56px"}},"color":{"background":"#0D1B12","text":"#ffffff"}}} -->
<div class="wp-block-group alignfull has-text-color has-background" style="background-color:#0D1B12;color:#ffffff;padding-top:56px;padding-bottom:56px"><!-- wp:heading {"level":1,"textAlign":"center","style":{"typography":{"fontSize":"42px","fontWeight":"300","letterSpacing":"2px"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:42px;font-weight:300;letter-spacing:2px">CHRONOGRAPHE GENÈVE</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px","letterSpacing":"1px"},"color":{"text":"#94a3b8"}}} -->
<p class="has-text-align-center has-text-color" style="color:#94a3b8;font-size:14px;letter-spacing:1px">HAND-ASSEMBLED IN SWITZERLAND • LIMITED TO 500 PIECES WORLDWIDE</p>
<!-- /wp:paragraph -->

<!-- wp:omnify/currency-switcher {"displayStyle":"dropdown","showSymbol":true,"align":"center"} /-->

<!-- wp:omnify/product-grid {"columns":"3","perPage":6,"showFilters":false,"showSort":true} /-->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald"} /--></div>
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
