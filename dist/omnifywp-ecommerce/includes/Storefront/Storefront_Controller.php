<?php
/**
 * Frontend storefront and customer portal.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Storefront;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Download_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Wishlist_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;
use Omnify\eCommerce\Support\Omnify_Email_Service;
use Omnify\eCommerce\Support\Omnify_Locations;

class Omnify_Storefront_Controller {
	public function __construct(
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Customer_Access_Repository $omnify_access,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Download_Repository $omnify_downloads,
		private Omnify_Review_Repository $omnify_reviews,
		private Omnify_Email_Service $omnify_emails,
		private Omnify_Wishlist_Repository $omnify_wishlists,
		private ?Omnify_Abandoned_Cart_Repository $omnify_abandoned_carts = null,
		private ?Omnify_Settings_Repository $omnify_settings = null
	) {}

	public function register(): void {
		add_shortcode('omnify_storefront', [$this, 'render_storefront']);
		add_shortcode('omnify_cart', [$this, 'render_cart']);
		add_shortcode('omnify_mini_cart', [$this, 'render_mini_cart']);
		add_shortcode('omnify_customer_portal', [$this, 'render_customer_portal']);
		add_shortcode('omnify_checkout', [$this, 'render_checkout']);
		add_shortcode('omnify_order_tracking', [$this, 'render_order_tracking']);
		add_shortcode('omnify_currency_switcher', [$this, 'render_currency_switcher']);
		// Map legacy shortcode if not already registered by Download_Controller
		if (! shortcode_exists('omnify_customer_downloads')) {
			add_shortcode('omnify_customer_downloads', [$this, 'render_customer_portal']);
		}

		add_action('wp_head', [$this, 'maybe_disable_core_canonical'], 0);
		add_action('wp_head', [$this, 'render_product_seo_tags'], 3);
		add_filter('pre_get_document_title', [$this, 'filter_document_title']);
		add_filter('document_title_parts', [$this, 'filter_document_title_parts']);
		add_filter('wpseo_title', [$this, 'filter_seo_title']);
		add_filter('wpseo_metadesc', [$this, 'filter_seo_description']);
		add_filter('wpseo_canonical', [$this, 'filter_seo_canonical']);
		add_filter('wpseo_opengraph_title', [$this, 'filter_seo_title']);
		add_filter('wpseo_opengraph_desc', [$this, 'filter_seo_description']);
		add_filter('wpseo_opengraph_url', [$this, 'filter_seo_canonical']);
		add_filter('wpseo_opengraph_image', [$this, 'filter_seo_image']);
		add_filter('rank_math/frontend/title', [$this, 'filter_seo_title']);
		add_filter('rank_math/frontend/description', [$this, 'filter_seo_description']);
		add_filter('rank_math/frontend/canonical', [$this, 'filter_seo_canonical']);
		add_filter('rank_math/opengraph/facebook/title', [$this, 'filter_seo_title']);
		add_filter('rank_math/opengraph/facebook/description', [$this, 'filter_seo_description']);
		add_filter('rank_math/opengraph/facebook/url', [$this, 'filter_seo_canonical']);
		add_filter('rank_math/opengraph/facebook/image', [$this, 'filter_seo_image']);
		add_filter('body_class', [$this, 'add_storefront_body_classes']);
		add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_omnify_assets'], 99999);
		add_action('wp_head', [$this, 'output_theme_isolation_barrier_styles'], 999999);
		add_action('wp_footer', [$this, 'output_theme_isolation_barrier_styles'], 999999);
		add_filter('wp_nav_menu_items', [$this, 'append_mini_cart_to_menu'], 20, 2);
		$this->register_product_sitemap();

		// Handle review form submission (new review + edit)
		add_action('admin_post_omnify_submit_review', [$this, 'handle_submit_review']);
		add_action('admin_post_nopriv_omnify_submit_review', [$this, 'handle_submit_review']);

		// Sync guest wishlist items to customer account upon login
		add_action('wp_login', [$this, 'sync_guest_wishlist_on_login'], 10, 2);

		// Gutenberg FSE Blocks. register() already runs on `init`, so register the
		// blocks immediately — adding another `init` callback here would never fire.
		$this->register_blocks();
		Omnify_Block_Patterns::register();
		Omnify_FSE_Templates::register();
		Omnify_Template_Library::register();
		add_filter('block_categories_all', [$this, 'register_block_category'], 10, 2);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
	}

	public function register_block_category(array $categories, $post): array {
		return array_merge([
			[
				'slug'  => 'omnify',
				'title' => __('OmnifyWP eCommerce', 'omnifywp-ecommerce'),
				'icon'  => 'cart',
			],
		], $categories);
	}

	/**
	 * Canonical list of block names shipped by the plugin.
	 *
	 * @return array<int, string>
	 */
	public static function block_names(): array {
		return [
			'omnify/product-grid',
			'omnify/cart',
			'omnify/mini-cart',
			'omnify/checkout',
			'omnify/customer-portal',
			'omnify/customer-downloads',
			'omnify/order-tracking',
			'omnify/product-card',
			'omnify/currency-switcher',
			'omnify/order-bump',
			'omnify/sticky-add-to-cart',
			'omnify/free-shipping-goal',
			'omnify/trust-badge-bar',
			'omnify/product-image',
			'omnify/product-title',
			'omnify/product-price',
			'omnify/product-rating',
			'omnify/product-badge',
			'omnify/product-button',
			'omnify/template-cloud',
		];
	}

	public function register_blocks(): void {
		if (! function_exists('register_block_type')) {
			return;
		}

		$omnify_defaults = [
			'api_version' => 2,
			'category'    => 'omnify',
			'supports'    => ['html' => false],
		];

		$omnify_max_width_attr = ['type' => 'integer', 'default' => 0];
		$omnify_tristate_attr  = ['type' => 'string', 'default' => ''];

		// Shared per-block style controls (applied to every Omnify block).
		$omnify_style_attrs = [
			'blockBackground'   => ['type' => 'string', 'default' => ''],
			'blockTextColor'    => ['type' => 'string', 'default' => ''],
			'blockAccentColor'  => ['type' => 'string', 'default' => ''],
			'cardBackground'    => ['type' => 'string', 'default' => ''],
			'cardRadius'        => ['type' => 'integer', 'default' => -1],
			'blockFontSize'     => ['type' => 'integer', 'default' => 0],
			'blockPadding'      => ['type' => 'integer', 'default' => 0],
			'blockRadius'       => ['type' => 'integer', 'default' => -1],
			'blockBorderWidth'  => ['type' => 'integer', 'default' => 0],
			'blockBorderColor'  => ['type' => 'string', 'default' => ''],
			'blockShadow'       => ['type' => 'string', 'default' => ''],
			'blockMarginTop'    => ['type' => 'integer', 'default' => 0],
			'blockMarginBottom' => ['type' => 'integer', 'default' => 0],
			'blockAlign'        => ['type' => 'string', 'default' => ''],
		];

		$omnify_blocks = [
			'omnify/product-grid' => [
				'title'           => __('Omnify Product Grid', 'omnifywp-ecommerce'),
				'description'     => __('Display an interactive, responsive product grid with instant search and filters.', 'omnifywp-ecommerce'),
				'icon'            => 'grid-view',
				'keywords'        => ['shop', 'products', 'store', 'grid', 'ecommerce'],
				'attributes'      => [
					'maxWidth'          => $omnify_max_width_attr,
					'columns'           => ['type' => 'string', 'default' => ''],
					'perPage'           => ['type' => 'integer', 'default' => 0],
					'category'          => ['type' => 'string', 'default' => ''],
					'tag'               => ['type' => 'string', 'default' => ''],
					'brand'             => ['type' => 'string', 'default' => ''],
					'minPrice'          => ['type' => 'number', 'default' => -1],
					'maxPrice'          => ['type' => 'number', 'default' => -1],
					'stockStatus'       => ['type' => 'string', 'default' => ''],
					'gridRows'          => ['type' => 'integer', 'default' => -1],
					'layout'            => ['type' => 'string', 'default' => ''],
					'cardStyle'         => ['type' => 'string', 'default' => ''],
					'gap'               => ['type' => 'string', 'default' => ''],
					'filterPosition'    => ['type' => 'string', 'default' => ''],
					'paginationType'    => ['type' => 'string', 'default' => ''],
					'defaultSort'       => ['type' => 'string', 'default' => ''],
					'heroTitle'         => ['type' => 'string', 'default' => ''],
					'heroSubtitle'      => ['type' => 'string', 'default' => ''],
					'flashTickerText'   => ['type' => 'string', 'default' => ''],
					'flashCouponCode'   => ['type' => 'string', 'default' => ''],
					'showSearch'        => $omnify_tristate_attr,
					'showFilters'       => $omnify_tristate_attr,
					'showSort'          => $omnify_tristate_attr,
					'showHero'          => $omnify_tristate_attr,
					'showFlashTicker'   => $omnify_tristate_attr,
					'showTrustBar'      => $omnify_tristate_attr,
					'showQuickFilters'  => $omnify_tristate_attr,
					'showBreadcrumbs'   => $omnify_tristate_attr,
					'showReviews'       => $omnify_tristate_attr,
					'showWishlist'      => $omnify_tristate_attr,
					'showAddToCart'     => $omnify_tristate_attr,
					'showQuickView'     => $omnify_tristate_attr,
					'showSaleBadge'     => $omnify_tristate_attr,
					'showStockBadge'    => $omnify_tristate_attr,
					'showFeaturePill'   => $omnify_tristate_attr,
					'showRecentlyViewed'=> $omnify_tristate_attr,
					'featuredOnly'      => ['type' => 'boolean', 'default' => false],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-grid', $omnify_attributes);
				},
			],
			'omnify/cart' => [
				'title'           => __('Omnify Shopping Cart', 'omnifywp-ecommerce'),
				'description'     => __('Display the full shopping cart with dynamic quantity controls and coupon input.', 'omnifywp-ecommerce'),
				'icon'            => 'cart',
				'keywords'        => ['cart', 'shopping', 'basket', 'ecommerce'],
				'attributes'      => [
					'maxWidth'          => $omnify_max_width_attr,
					'showCoupon'        => $omnify_tristate_attr,
					'showSteps'         => ['type' => 'boolean', 'default' => true],
					'showNote'          => ['type' => 'boolean', 'default' => true],
					'showShippingMeter' => ['type' => 'boolean', 'default' => true],
					'shippingThreshold' => ['type' => 'number', 'default' => 0],
					'showTrustBadges'   => ['type' => 'boolean', 'default' => true],
					'showEmptyButton'   => ['type' => 'boolean', 'default' => true],
					'showCrossSells'    => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/cart', $omnify_attributes);
				},
			],
			'omnify/mini-cart' => [
				'title'           => __('Omnify Mini Cart', 'omnifywp-ecommerce'),
				'description'     => __('A floating or embedded mini cart button with slide-out drawer.', 'omnifywp-ecommerce'),
				'icon'            => 'cart',
				'keywords'        => ['cart', 'mini cart', 'drawer'],
				'attributes'      => [
					'maxWidth'          => $omnify_max_width_attr,
					'label'             => ['type' => 'string', 'default' => 'Cart'],
					'align'             => ['type' => 'string', 'default' => 'right'],
					'showShippingMeter' => ['type' => 'boolean', 'default' => true],
					'shippingThreshold' => ['type' => 'number', 'default' => 0],
					'showCount'         => ['type' => 'boolean', 'default' => true],
					'showSubtotal'      => ['type' => 'boolean', 'default' => true],
					'showCoupon'        => ['type' => 'boolean', 'default' => true],
					'showViewCartButton'=> ['type' => 'boolean', 'default' => true],
					'showCheckoutButton'=> ['type' => 'boolean', 'default' => true],
					'showEmptyState'    => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/mini-cart', $omnify_attributes);
				},
			],
			'omnify/checkout' => [
				'title'           => __('Omnify Checkout', 'omnifywp-ecommerce'),
				'description'     => __('High-converting one-page checkout experience with 14+ global payment gateways.', 'omnifywp-ecommerce'),
				'icon'            => 'money-alt',
				'keywords'        => ['checkout', 'payment', 'purchase', 'order', 'pay'],
				'attributes'      => [
					'maxWidth'              => $omnify_max_width_attr,
					'layout'                => ['type' => 'string', 'default' => ''],
					'showTrustBadges'       => $omnify_tristate_attr,
					'showOrderBump'         => $omnify_tristate_attr,
					'orderBumpTitle'        => ['type' => 'string', 'default' => ''],
					'showCoupon'            => $omnify_tristate_attr,
					'requireTerms'          => $omnify_tristate_attr,
					'showOrderNotes'        => ['type' => 'boolean', 'default' => true],
					'showScarcityCountdown' => ['type' => 'boolean', 'default' => true],
					'countdownMinutes'      => ['type' => 'integer', 'default' => 15],
					'showGuarantees'        => ['type' => 'boolean', 'default' => true],
					'expressCheckout'       => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/checkout', $omnify_attributes);
				},
			],
			'omnify/customer-portal' => [
				'title'           => __('Omnify Customer Portal', 'omnifywp-ecommerce'),
				'description'     => __('Self-service portal for customer order history, file downloads, and account details.', 'omnifywp-ecommerce'),
				'icon'            => 'id',
				'keywords'        => ['portal', 'account', 'customer', 'downloads', 'orders'],
				'attributes'      => [
					'maxWidth'       => $omnify_max_width_attr,
					'defaultTab'     => ['type' => 'string', 'default' => ''],
					'showStats'      => ['type' => 'boolean', 'default' => true],
					'showGreeting'   => ['type' => 'boolean', 'default' => true],
					'showLogout'     => ['type' => 'boolean', 'default' => true],
					'showInvoices'   => ['type' => 'boolean', 'default' => true],
					'showWishlistTab'=> ['type' => 'boolean', 'default' => true],
					'showSearch'     => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/customer-portal', $omnify_attributes);
				},
			],
			'omnify/customer-downloads' => [
				'title'           => __('Omnify Digital Downloads Locker', 'omnifywp-ecommerce'),
				'description'     => __('Dedicated customer digital file locker with HMAC signed URLs, expiration notices, and limits.', 'omnifywp-ecommerce'),
				'icon'            => 'download',
				'keywords'        => ['download', 'files', 'digital', 'license', 'software', 'locker'],
				'attributes'      => [
					'maxWidth'          => $omnify_max_width_attr,
					'title'             => ['type' => 'string', 'default' => ''],
					'subtitle'          => ['type' => 'string', 'default' => ''],
					'showSearch'        => ['type' => 'boolean', 'default' => true],
					'showVersion'       => ['type' => 'boolean', 'default' => true],
					'showFileSize'      => ['type' => 'boolean', 'default' => true],
					'showExpiryNotice'  => ['type' => 'boolean', 'default' => true],
					'showDownloadCount' => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/customer-downloads', $omnify_attributes);
				},
			],
			'omnify/order-tracking' => [
				'title'           => __('Omnify Order Tracking', 'omnifywp-ecommerce'),
				'description'     => __('Real-time visual order and shipment tracking timeline.', 'omnifywp-ecommerce'),
				'icon'            => 'location-alt',
				'keywords'        => ['track', 'tracking', 'order', 'shipment', 'delivery'],
				'attributes'      => [
					'maxWidth'          => $omnify_max_width_attr,
					'title'             => ['type' => 'string', 'default' => ''],
					'subtitle'          => ['type' => 'string', 'default' => ''],
					'showTimeline'      => ['type' => 'boolean', 'default' => true],
					'showItemsSummary'  => ['type' => 'boolean', 'default' => true],
					'showSupportButton' => ['type' => 'boolean', 'default' => true],
					'supportEmail'      => ['type' => 'string', 'default' => ''],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/order-tracking', $omnify_attributes);
				},
			],
			'omnify/product-card' => [
				'title'           => __('Omnify Product Card', 'omnifywp-ecommerce'),
				'description'     => __('Display an individual high-converting product card with 1-click buy.', 'omnifywp-ecommerce'),
				'icon'            => 'tag',
				'keywords'        => ['product', 'card', 'buy button'],
				'attributes'      => [
					'maxWidth'         => $omnify_max_width_attr,
					'productId'        => ['type' => 'integer', 'default' => 0],
					'cardLayout'       => ['type' => 'string', 'default' => 'vertical'],
					'showPrice'        => ['type' => 'boolean', 'default' => true],
					'showComparePrice' => ['type' => 'boolean', 'default' => true],
					'showRating'       => ['type' => 'boolean', 'default' => true],
					'showBuyButton'    => ['type' => 'boolean', 'default' => true],
					'buttonAction'     => ['type' => 'string', 'default' => 'cart'],
					'showSku'          => ['type' => 'boolean', 'default' => false],
					'showExcerpt'      => ['type' => 'boolean', 'default' => false],
					'showSaleBadge'    => ['type' => 'boolean', 'default' => true],
					'showStockStatus'  => ['type' => 'boolean', 'default' => false],
					'showCategory'     => ['type' => 'boolean', 'default' => false],
					'showFeaturePill'  => ['type' => 'boolean', 'default' => false],
					'imageRatio'       => ['type' => 'string', 'default' => 'square'],
					'buttonLabel'      => ['type' => 'string', 'default' => 'Add to Cart'],
					'badgeText'        => ['type' => 'string', 'default' => ''],
					'showGalleryFlip'  => ['type' => 'boolean', 'default' => true],
					'hoverEffect'      => ['type' => 'string', 'default' => 'lift'],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-card', $omnify_attributes);
				},
			],
			'omnify/currency-switcher' => [
				'title'           => __('Omnify Currency Switcher', 'omnifywp-ecommerce'),
				'description'     => __('Interactive currency switcher dropdown supporting 22 global currencies.', 'omnifywp-ecommerce'),
				'icon'            => 'translation',
				'keywords'        => ['currency', 'switcher', 'converter', 'money', 'multicurrency'],
				'attributes'      => [
					'maxWidth'     => $omnify_max_width_attr,
					'label'        => ['type' => 'string', 'default' => 'Currency:'],
					'showLabel'    => ['type' => 'boolean', 'default' => true],
					'showSymbol'   => ['type' => 'boolean', 'default' => true],
					'showFlag'     => ['type' => 'boolean', 'default' => false],
					'displayStyle' => ['type' => 'string', 'default' => 'dropdown'],
					'currencies'   => ['type' => 'string', 'default' => ''],
					'align'        => ['type' => 'string', 'default' => ''],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/currency-switcher', $omnify_attributes);
				},
			],
			'omnify/order-bump' => [
				'title'           => __('Omnify Order Bump', 'omnifywp-ecommerce'),
				'description'     => __('High-converting 1-click checkout upsell checkbox with instant AJAX cart addition.', 'omnifywp-ecommerce'),
				'icon'            => 'star-filled',
				'keywords'        => ['order bump', 'upsell', 'checkout', 'addon', 'cross-sell'],
				'attributes'      => [
					'maxWidth'       => $omnify_max_width_attr,
					'productId'      => ['type' => 'integer', 'default' => 0],
					'title'          => ['type' => 'string', 'default' => 'Yes! Add This Exclusive Offer to My Order'],
					'badgeText'      => ['type' => 'string', 'default' => 'ONE-TIME OFFER'],
					'description'    => ['type' => 'string', 'default' => 'Add priority support and extended warranty with one single click.'],
					'discountType'   => ['type' => 'string', 'default' => 'percentage'],
					'discountVal'    => ['type' => 'number', 'default' => 20],
					'highlightColor' => ['type' => 'string', 'default' => '#0f766e'],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/order-bump', $omnify_attributes);
				},
			],
			'omnify/sticky-add-to-cart' => [
				'title'           => __('Omnify Sticky Add to Cart', 'omnifywp-ecommerce'),
				'description'     => __('High-conversion floating bottom/top bar with product thumbnail, price, and instant checkout.', 'omnifywp-ecommerce'),
				'icon'            => 'arrow-down-alt2',
				'keywords'        => ['sticky', 'add to cart', 'floating', 'mobile', 'bar'],
				'attributes'      => [
					'maxWidth'         => $omnify_max_width_attr,
					'productId'        => ['type' => 'integer', 'default' => 0],
					'position'         => ['type' => 'string', 'default' => 'bottom'],
					'deviceVisibility' => ['type' => 'string', 'default' => 'all'],
					'showThumbnail'    => ['type' => 'boolean', 'default' => true],
					'showPrice'        => ['type' => 'boolean', 'default' => true],
					'showQuantity'     => ['type' => 'boolean', 'default' => true],
					'buttonAction'     => ['type' => 'string', 'default' => 'cart'],
					'buttonLabel'      => ['type' => 'string', 'default' => 'Add to Cart'],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/sticky-add-to-cart', $omnify_attributes);
				},
			],
			'omnify/free-shipping-goal' => [
				'title'           => __('Omnify Free Shipping Goal', 'omnifywp-ecommerce'),
				'description'     => __('Gamified dynamic progress bar displaying remaining amount for free shipping with live celebrations.', 'omnifywp-ecommerce'),
				'icon'            => 'car',
				'keywords'        => ['shipping', 'progress', 'free shipping', 'meter', 'goal'],
				'attributes'      => [
					'maxWidth'      => $omnify_max_width_attr,
					'threshold'     => ['type' => 'number', 'default' => 50],
					'initialMsg'    => ['type' => 'string', 'default' => 'Add %s more to unlock FREE Shipping!'],
					'successMsg'    => ['type' => 'string', 'default' => '🎉 Congratulations! You have unlocked FREE Shipping!'],
					'showIcon'      => ['type' => 'boolean', 'default' => true],
					'showConfetti'  => ['type' => 'boolean', 'default' => true],
					'barColor'      => ['type' => 'string', 'default' => '#0f766e'],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/free-shipping-goal', $omnify_attributes);
				},
			],
			'omnify/trust-badge-bar' => [
				'title'           => __('Omnify Trust Badges & Guarantee Strip', 'omnifywp-ecommerce'),
				'description'     => __('Showcase SSL security, payment providers, fast shipping, and money-back guarantees.', 'omnifywp-ecommerce'),
				'icon'            => 'shield-alt',
				'keywords'        => ['trust', 'badges', 'guarantee', 'ssl', 'security', 'payments'],
				'attributes'      => [
					'maxWidth'       => $omnify_max_width_attr,
					'layout'         => ['type' => 'string', 'default' => 'horizontal'],
					'badgeStyle'     => ['type' => 'string', 'default' => 'emerald'],
					'showBorder'     => ['type' => 'boolean', 'default' => true],
					'showGuarantees' => ['type' => 'boolean', 'default' => true],
					'showPayments'   => ['type' => 'boolean', 'default' => true],
					'showSupport'    => ['type' => 'boolean', 'default' => true],
					'showReturns'    => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/trust-badge-bar', $omnify_attributes);
				},
			],
			'omnify/product-image' => [
				'title'           => __('Omnify Product Image', 'omnifywp-ecommerce'),
				'description'     => __('Product thumbnail with custom aspect ratio, zoom, and secondary flip.', 'omnifywp-ecommerce'),
				'icon'            => 'format-image',
				'keywords'        => ['product', 'image', 'photo', 'picture'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'aspectRatio' => ['type' => 'string', 'default' => '1/1'],
					'zoomOnHover' => ['type' => 'boolean', 'default' => true],
					'borderRadius'=> ['type' => 'integer', 'default' => 10],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-image', $omnify_attributes);
				},
			],
			'omnify/product-title' => [
				'title'           => __('Omnify Product Title', 'omnifywp-ecommerce'),
				'description'     => __('Dynamic product title with customizable heading tag and link.', 'omnifywp-ecommerce'),
				'icon'            => 'heading',
				'keywords'        => ['product', 'title', 'heading', 'name'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'headingTag'  => ['type' => 'string', 'default' => 'h3'],
					'linkToPost'  => ['type' => 'boolean', 'default' => true],
					'fontSize'    => ['type' => 'integer', 'default' => 18],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-title', $omnify_attributes);
				},
			],
			'omnify/product-price' => [
				'title'           => __('Omnify Product Price', 'omnifywp-ecommerce'),
				'description'     => __('Dynamic product price with sale comparison and discount pill.', 'omnifywp-ecommerce'),
				'icon'            => 'money-alt',
				'keywords'        => ['product', 'price', 'sale', 'cost'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'showRegular' => ['type' => 'boolean', 'default' => true],
					'showSavings' => ['type' => 'boolean', 'default' => true],
					'fontSize'    => ['type' => 'integer', 'default' => 18],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-price', $omnify_attributes);
				},
			],
			'omnify/product-rating' => [
				'title'           => __('Omnify Product Rating', 'omnifywp-ecommerce'),
				'description'     => __('Product review star rating and review count breakdown.', 'omnifywp-ecommerce'),
				'icon'            => 'star-filled',
				'keywords'        => ['product', 'rating', 'stars', 'reviews'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'starColor'   => ['type' => 'string', 'default' => '#f59e0b'],
					'showCount'   => ['type' => 'boolean', 'default' => true],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-rating', $omnify_attributes);
				},
			],
			'omnify/product-badge' => [
				'title'           => __('Omnify Product Badge', 'omnifywp-ecommerce'),
				'description'     => __('Highlight tags like Sale, Out of Stock, or Verified Download.', 'omnifywp-ecommerce'),
				'icon'            => 'tag',
				'keywords'        => ['product', 'badge', 'sale', 'pill'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'badgeType'   => ['type' => 'string', 'default' => 'auto'],
					'customText'  => ['type' => 'string', 'default' => ''],
					'badgeColor'  => ['type' => 'string', 'default' => '#0f766e'],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-badge', $omnify_attributes);
				},
			],
			'omnify/product-button' => [
				'title'           => __('Omnify Product Button', 'omnifywp-ecommerce'),
				'description'     => __('Interactive Add to Cart or Buy Now button with quantity stepper.', 'omnifywp-ecommerce'),
				'icon'            => 'cart',
				'keywords'        => ['product', 'button', 'cart', 'buy now'],
				'attributes'      => [
					'productId'   => ['type' => 'integer', 'default' => 0],
					'action'      => ['type' => 'string', 'default' => 'cart'],
					'buttonText'  => ['type' => 'string', 'default' => 'Add to Cart'],
					'showQty'     => ['type' => 'boolean', 'default' => false],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return $this->render_omnify_block('omnify/product-button', $omnify_attributes);
				},
			],
			'omnify/template-cloud' => [
				'title'           => __('Omnify Template Cloud', 'omnifywp-ecommerce'),
				'description'     => __('Browse and insert pre-built, high-converting eCommerce starter layouts with 1-click.', 'omnifywp-ecommerce'),
				'icon'            => 'layout',
				'keywords'        => ['templates', 'starter', 'layout', 'cloud'],
				'attributes'      => [
					'selectedId'  => ['type' => 'string', 'default' => ''],
				],
				'render_callback' => function (array $omnify_attributes = []): string {
					return '';
				},
			],
		];

		foreach ($omnify_blocks as $omnify_block_name => $omnify_block_args) {
			$omnify_block_args['attributes'] = array_merge($omnify_style_attrs, (array) ($omnify_block_args['attributes'] ?? []));
			register_block_type($omnify_block_name, array_merge($omnify_defaults, $omnify_block_args));
		}
	}

	/**
	 * Render a dynamic block, applying its block-level setting overrides and width.
	 *
	 * @param string               $omnify_name       Block name.
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	private function render_omnify_block(string $omnify_name, array $omnify_attributes): string {
		$omnify_atts = is_array($omnify_attributes) ? $omnify_attributes : [];

		$omnify_renderers = [
			'omnify/product-grid'        => 'render_storefront',
			'omnify/cart'                => 'render_cart',
			'omnify/mini-cart'           => 'render_mini_cart',
			'omnify/checkout'            => 'render_checkout',
			'omnify/customer-portal'     => 'render_customer_portal',
			'omnify/customer-downloads'  => 'render_block_customer_downloads',
			'omnify/order-tracking'      => 'render_order_tracking',
			'omnify/product-card'        => 'render_block_product_card',
			'omnify/currency-switcher'   => 'render_currency_switcher',
			'omnify/order-bump'          => 'render_block_order_bump',
			'omnify/sticky-add-to-cart'  => 'render_block_sticky_add_to_cart',
			'omnify/free-shipping-goal'  => 'render_block_free_shipping_goal',
			'omnify/trust-badge-bar'     => 'render_block_trust_badge_bar',
			'omnify/product-image'       => 'render_atomic_product_image',
			'omnify/product-title'       => 'render_atomic_product_title',
			'omnify/product-price'       => 'render_atomic_product_price',
			'omnify/product-rating'      => 'render_atomic_product_rating',
			'omnify/product-badge'       => 'render_atomic_product_badge',
			'omnify/product-button'      => 'render_atomic_product_button',
		];

		$omnify_method = $omnify_renderers[$omnify_name] ?? '';
		if ('' === $omnify_method || ! method_exists($this, $omnify_method)) {
			return '';
		}

		$omnify_overrides = $this->block_setting_overrides($omnify_name, $omnify_atts);

		if (! empty($omnify_overrides)) {
			$omnify_base   = get_option('omnify_settings', []);
			$omnify_base   = is_array($omnify_base) ? $omnify_base : [];
			$omnify_filter = static function () use ($omnify_base, $omnify_overrides) {
				return array_merge($omnify_base, $omnify_overrides);
			};

			add_filter('pre_option_omnify_settings', $omnify_filter, PHP_INT_MAX);
			try {
				$omnify_html = (string) call_user_func([$this, $omnify_method], $omnify_atts);
			} finally {
				remove_filter('pre_option_omnify_settings', $omnify_filter, PHP_INT_MAX);
			}
		} else {
			$omnify_html = (string) call_user_func([$this, $omnify_method], $omnify_atts);
		}

		return $this->wrap_block_output($omnify_html, $omnify_atts);
	}

	/**
	 * Translate block attributes into store settings overrides for the render pass.
	 *
	 * @param string               $omnify_name Block name.
	 * @param array<string, mixed> $omnify_atts Block attributes.
	 * @return array<string, mixed>
	 */
	private function block_setting_overrides(string $omnify_name, array $omnify_atts): array {
		$omnify_overrides = [];
		$omnify_tristate  = static function ($omnify_value): ?int {
			if (null === $omnify_value || '' === $omnify_value) {
				return null;
			}
			return in_array((string) $omnify_value, ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
		};

		if ('omnify/product-grid' === $omnify_name) {
			if (! empty($omnify_atts['columns'])) {
				$omnify_overrides['storefront_grid_columns'] = absint($omnify_atts['columns']);
			}
			if (! empty($omnify_atts['perPage'])) {
				$omnify_overrides['storefront_products_per_page'] = absint($omnify_atts['perPage']);
			}
			if (! empty($omnify_atts['layout'])) {
				$omnify_overrides['storefront_catalog_layout'] = sanitize_key((string) $omnify_atts['layout']);
			}
			if (! empty($omnify_atts['cardStyle'])) {
				$omnify_overrides['storefront_card_style'] = sanitize_key((string) $omnify_atts['cardStyle']);
			}
			if (! empty($omnify_atts['filterPosition'])) {
				$omnify_overrides['storefront_filter_position'] = sanitize_key((string) $omnify_atts['filterPosition']);
			}
			if (! empty($omnify_atts['paginationType'])) {
				$omnify_overrides['storefront_pagination_type'] = sanitize_key((string) $omnify_atts['paginationType']);
			}
			if (! empty($omnify_atts['defaultSort'])) {
				$omnify_overrides['storefront_default_sort'] = sanitize_key((string) $omnify_atts['defaultSort']);
			}
			if (isset($omnify_atts['gridRows']) && (int) $omnify_atts['gridRows'] >= 0) {
				$omnify_overrides['storefront_grid_rows'] = (int) $omnify_atts['gridRows'];
			}
			if (! empty($omnify_atts['heroTitle'])) {
				$omnify_overrides['storefront_hero_title'] = sanitize_text_field((string) $omnify_atts['heroTitle']);
			}
			if (! empty($omnify_atts['heroSubtitle'])) {
				$omnify_overrides['storefront_hero_desc'] = sanitize_text_field((string) $omnify_atts['heroSubtitle']);
			}
			if (! empty($omnify_atts['flashTickerText'])) {
				$omnify_overrides['storefront_flash_message'] = sanitize_text_field((string) $omnify_atts['flashTickerText']);
			}
			if (! empty($omnify_atts['flashCouponCode'])) {
				$omnify_overrides['storefront_flash_coupon'] = sanitize_text_field((string) $omnify_atts['flashCouponCode']);
			}

			$omnify_tristate_map = [
				'showSearch'         => 'storefront_show_search',
				'showFilters'        => 'storefront_show_filters',
				'showSort'           => 'storefront_show_sort',
				'showHero'           => 'storefront_show_hero',
				'showFlashTicker'    => 'storefront_show_flash_sale',
				'showTrustBar'       => 'storefront_show_trust_bar',
				'showQuickFilters'   => 'storefront_show_quick_filters',
				'showBreadcrumbs'    => 'storefront_show_breadcrumbs',
				'showReviews'        => 'design_show_reviews',
				'showWishlist'       => 'storefront_show_wishlist',
				'showAddToCart'      => 'storefront_show_add_to_cart',
				'showQuickView'      => 'storefront_show_quick_view',
				'showSaleBadge'      => 'storefront_show_sale_badge',
				'showStockBadge'     => 'storefront_show_stock_badge',
				'showFeaturePill'    => 'storefront_show_feature_pill',
				'showRecentlyViewed' => 'storefront_show_recently_viewed',
			];
			foreach ($omnify_tristate_map as $omnify_attr_name => $omnify_setting_key) {
				$omnify_value = $omnify_tristate($omnify_atts[$omnify_attr_name] ?? '');
				if (null !== $omnify_value) {
					$omnify_overrides[$omnify_setting_key] = $omnify_value;
				}
			}
		} elseif ('omnify/cart' === $omnify_name) {
			$omnify_show_coupon = $omnify_tristate($omnify_atts['showCoupon'] ?? '');
			if (null !== $omnify_show_coupon) {
				$omnify_overrides['enable_coupons'] = $omnify_show_coupon;
			}
			if (isset($omnify_atts['showShippingMeter'])) {
				$omnify_overrides['cart_show_shipping_meter'] = ! empty($omnify_atts['showShippingMeter']) ? 1 : 0;
			}
			if (isset($omnify_atts['shippingThreshold']) && (float) $omnify_atts['shippingThreshold'] > 0) {
				$omnify_overrides['free_shipping_threshold'] = (float) $omnify_atts['shippingThreshold'];
			}
			if (isset($omnify_atts['showTrustBadges'])) {
				$omnify_overrides['cart_show_trust_badges'] = ! empty($omnify_atts['showTrustBadges']) ? 1 : 0;
			}
			if (isset($omnify_atts['showEmptyButton'])) {
				$omnify_overrides['cart_show_continue_shopping'] = ! empty($omnify_atts['showEmptyButton']) ? 1 : 0;
			}
			if (isset($omnify_atts['showCrossSells'])) {
				$omnify_overrides['cart_show_cross_sells'] = ! empty($omnify_atts['showCrossSells']) ? 1 : 0;
			}
		} elseif ('omnify/checkout' === $omnify_name) {
			if (! empty($omnify_atts['layout'])) {
				$omnify_overrides['checkout_layout'] = sanitize_key((string) $omnify_atts['layout']);
			}
			$omnify_show_trust = $omnify_tristate($omnify_atts['showTrustBadges'] ?? '');
			if (null !== $omnify_show_trust) {
				$omnify_overrides['checkout_show_trust_box'] = $omnify_show_trust;
			}
			$omnify_show_bump = $omnify_tristate($omnify_atts['showOrderBump'] ?? '');
			if (null !== $omnify_show_bump) {
				$omnify_overrides['order_bump_enabled'] = $omnify_show_bump;
			}
			if (! empty($omnify_atts['orderBumpTitle'])) {
				$omnify_overrides['order_bump_title'] = sanitize_text_field((string) $omnify_atts['orderBumpTitle']);
			}
			$omnify_show_coupon = $omnify_tristate($omnify_atts['showCoupon'] ?? '');
			if (null !== $omnify_show_coupon) {
				$omnify_overrides['enable_coupons'] = $omnify_show_coupon;
			}
			$omnify_require_terms = $omnify_tristate($omnify_atts['requireTerms'] ?? '');
			if (null !== $omnify_require_terms) {
				$omnify_overrides['require_terms'] = $omnify_require_terms;
			}
			if (isset($omnify_atts['showScarcityCountdown'])) {
				$omnify_overrides['checkout_show_timer'] = ! empty($omnify_atts['showScarcityCountdown']) ? 1 : 0;
			}
			if (isset($omnify_atts['countdownMinutes']) && absint($omnify_atts['countdownMinutes']) > 0) {
				$omnify_overrides['checkout_timer_minutes'] = absint($omnify_atts['countdownMinutes']);
			}
			if (isset($omnify_atts['showGuarantees'])) {
				$omnify_overrides['checkout_show_guarantees'] = ! empty($omnify_atts['showGuarantees']) ? 1 : 0;
			}
		} elseif ('omnify/customer-portal' === $omnify_name) {
			if (isset($omnify_atts['showStats'])) {
				$omnify_overrides['portal_show_stats'] = ! empty($omnify_atts['showStats']) ? 1 : 0;
			}
			if (isset($omnify_atts['showGreeting'])) {
				$omnify_overrides['portal_show_greeting'] = ! empty($omnify_atts['showGreeting']) ? 1 : 0;
			}
			if (isset($omnify_atts['showLogout'])) {
				$omnify_overrides['portal_show_logout'] = ! empty($omnify_atts['showLogout']) ? 1 : 0;
			}
			if (isset($omnify_atts['showInvoices'])) {
				$omnify_overrides['portal_show_invoices'] = ! empty($omnify_atts['showInvoices']) ? 1 : 0;
			}
			if (isset($omnify_atts['showWishlistTab'])) {
				$omnify_overrides['portal_show_wishlist'] = ! empty($omnify_atts['showWishlistTab']) ? 1 : 0;
			}
		}

		return $omnify_overrides;
	}

	/**
	 * Optionally constrain a block's rendered output to a maximum width.
	 *
	 * @param string               $omnify_html Rendered block HTML.
	 * @param array<string, mixed> $omnify_atts Block attributes.
	 */
	private function wrap_block_output(string $omnify_html, array $omnify_atts): string {
		if ('' === trim($omnify_html)) {
			return $omnify_html;
		}

		$omnify_styles = [];
		$omnify_vars   = [];

		$omnify_max = absint($omnify_atts['maxWidth'] ?? 0);
		if ($omnify_max > 0) {
			$omnify_styles[] = 'max-width:' . $omnify_max . 'px';
			$omnify_styles[] = 'width:100%';
			$omnify_styles[] = 'margin-left:auto';
			$omnify_styles[] = 'margin-right:auto';
		}

		$omnify_background = sanitize_hex_color((string) ($omnify_atts['blockBackground'] ?? ''));
		if ($omnify_background) {
			$omnify_styles[] = 'background-color:' . $omnify_background;
		}

		$omnify_text_color = sanitize_hex_color((string) ($omnify_atts['blockTextColor'] ?? ''));
		if ($omnify_text_color) {
			$omnify_styles[] = 'color:' . $omnify_text_color;
		}

		$omnify_accent = sanitize_hex_color((string) ($omnify_atts['blockAccentColor'] ?? ''));
		if ($omnify_accent) {
			$omnify_vars['--omnify-primary']       = $omnify_accent;
			$omnify_vars['--omnify-primary-hover'] = $omnify_accent;
			$omnify_vars['--omnify-primary-glow']  = $this->hex2rgba($omnify_accent, 0.15);
		}

		$omnify_card_bg = sanitize_hex_color((string) ($omnify_atts['cardBackground'] ?? ''));
		if ($omnify_card_bg) {
			$omnify_vars['--omnify-surface'] = $omnify_card_bg;
			$omnify_vars['--omnify-card-bg'] = $omnify_card_bg;
		}

		$omnify_card_radius = isset($omnify_atts['cardRadius']) ? (int) $omnify_atts['cardRadius'] : -1;
		if ($omnify_card_radius >= 0) {
			$omnify_vars['--omnify-card-radius'] = $omnify_card_radius . 'px';
		}

		$omnify_font_size = absint($omnify_atts['blockFontSize'] ?? 0);
		if ($omnify_font_size > 0) {
			$omnify_styles[] = 'font-size:' . $omnify_font_size . 'px';
		}

		$omnify_padding = absint($omnify_atts['blockPadding'] ?? 0);
		if ($omnify_padding > 0) {
			$omnify_styles[] = 'padding:' . $omnify_padding . 'px';
		}

		$omnify_radius = isset($omnify_atts['blockRadius']) ? (int) $omnify_atts['blockRadius'] : -1;
		if ($omnify_radius >= 0) {
			$omnify_vars['--omnify-radius'] = $omnify_radius . 'px';
			$omnify_styles[]                = 'border-radius:' . $omnify_radius . 'px';
		}

		$omnify_border_width = absint($omnify_atts['blockBorderWidth'] ?? 0);
		if ($omnify_border_width > 0) {
			$omnify_border_color = sanitize_hex_color((string) ($omnify_atts['blockBorderColor'] ?? '')) ?: '#e2e8f0';
			$omnify_styles[]     = 'border:' . $omnify_border_width . 'px solid ' . $omnify_border_color;
		}

		$omnify_shadow = sanitize_key((string) ($omnify_atts['blockShadow'] ?? ''));
		$omnify_shadow_map = [
			'none'   => 'none',
			'soft'   => '0 4px 12px rgba(15, 23, 42, 0.06)',
			'medium' => '0 12px 32px rgba(15, 23, 42, 0.12)',
			'strong' => '0 22px 60px rgba(15, 23, 42, 0.18)',
		];
		if (isset($omnify_shadow_map[$omnify_shadow])) {
			$omnify_styles[] = 'box-shadow:' . $omnify_shadow_map[$omnify_shadow];
		}

		$omnify_margin_top = absint($omnify_atts['blockMarginTop'] ?? 0);
		if ($omnify_margin_top > 0) {
			$omnify_styles[] = 'margin-top:' . $omnify_margin_top . 'px';
		}

		$omnify_margin_bottom = absint($omnify_atts['blockMarginBottom'] ?? 0);
		if ($omnify_margin_bottom > 0) {
			$omnify_styles[] = 'margin-bottom:' . $omnify_margin_bottom . 'px';
		}

		$omnify_align = sanitize_key((string) ($omnify_atts['blockAlign'] ?? ''));
		if (in_array($omnify_align, ['left', 'center', 'right'], true)) {
			$omnify_styles[] = 'text-align:' . $omnify_align;
		}

		if (empty($omnify_styles) && empty($omnify_vars)) {
			return $omnify_html;
		}

		$omnify_var_css = '';
		foreach ($omnify_vars as $omnify_var_name => $omnify_var_value) {
			$omnify_var_css .= $omnify_var_name . ':' . $omnify_var_value . ';';
		}

		$omnify_style_attr = $omnify_var_css . implode(';', $omnify_styles);
		if ('' === trim($omnify_style_attr)) {
			return $omnify_html;
		}

		// Give each block instance a unique id so we can also override the
		// storefront's scoped !important rules on the inner surfaces.
		$omnify_uid  = wp_unique_id('omnify-block-');
		$omnify_sel  = '#' . $omnify_uid;
		$omnify_rules = [];

		$omnify_surfaces = implode(
			',',
			[
				$omnify_sel,
				$omnify_sel . ' .omnify-storefront-wrapper',
				$omnify_sel . ' .omnify-checkout-wrapper',
				$omnify_sel . ' .omnify-cart-page',
				$omnify_sel . ' .omnify-portal-wrapper',
				$omnify_sel . ' .omnify-order-tracking-wrapper',
				$omnify_sel . ' .omnify-customer-downloads-block',
				$omnify_sel . ' .omnify-block-product-card',
				$omnify_sel . ' .omnify-currency-switcher-wrap',
				$omnify_sel . ' .omnify-mini-cart',
				$omnify_sel . ' .omnify-order-bump-block',
				$omnify_sel . ' .omnify-sticky-atc-bar',
				$omnify_sel . ' .omnify-free-shipping-goal-block',
				$omnify_sel . ' .omnify-trust-badge-bar-block',
			]
		);

		if ($omnify_background) {
			$omnify_rules[] = $omnify_surfaces . '{background-color:' . $omnify_background . ' !important;}';
		}

		if ($omnify_card_bg) {
			$omnify_rules[] = $omnify_sel . ' :where(.omnify-product-card,.omnify-portal-section-card,.omnify-checkout-summary-card,.omnify-cart-summary,.omnify-block-product-card){background-color:' . $omnify_card_bg . ' !important;}';
		}

		if ($omnify_card_radius >= 0) {
			$omnify_rules[] = $omnify_sel . ' :where(.omnify-product-card,.omnify-portal-section-card,.omnify-checkout-summary-card,.omnify-cart-summary,.omnify-block-product-card){border-radius:' . $omnify_card_radius . 'px !important;}';
		}

		if ($omnify_text_color) {
			$omnify_rules[] = $omnify_sel . '{color:' . $omnify_text_color . ' !important;}';
			$omnify_rules[] = $omnify_sel . ' :where(h1,h2,h3,h4,h5,h6,p,li,dt,dd,td,th,label,figcaption){color:' . $omnify_text_color . ' !important;}';
		}

		if ($omnify_font_size > 0) {
			$omnify_rules[] = $omnify_sel . '{font-size:' . $omnify_font_size . 'px !important;}';
			$omnify_rules[] = $omnify_sel . ' :where(p,li,dt,dd,td,th,label,figcaption){font-size:' . $omnify_font_size . 'px !important;}';
		}

		if ($omnify_radius >= 0) {
			$omnify_radius_css = $omnify_radius . 'px';
			$omnify_rules[]    = $omnify_sel . ' :where(.omnify-block-product-card,.omnify-product-card,.omnify-card,.omnify-portal-section-card,.omnify-portal-box,.omnify-order-box,.omnify-checkout-card){border-radius:' . $omnify_radius_css . ' !important;overflow:hidden;}';
			$omnify_rules[]    = $omnify_sel . ' :where(button,input,select,textarea,.omnify-btn){border-radius:' . $omnify_radius_css . ' !important;}';
		}

		if (in_array($omnify_align, ['left', 'center', 'right'], true)) {
			$omnify_rules[] = $omnify_sel . '{text-align:' . $omnify_align . ' !important;}';
		}

		$omnify_prefix = '';
		if (! empty($omnify_rules)) {
			$omnify_prefix = '<style id="' . esc_attr($omnify_uid) . '-css">' . implode('', $omnify_rules) . '</style>';
		}

		return $omnify_prefix . '<div id="' . esc_attr($omnify_uid) . '" class="omnify-block-container" style="' . esc_attr($omnify_style_attr) . '">' . $omnify_html . '</div>';
	}

	/**
	 * Enqueue the client-side editor UI for every block (plain JS, no build step).
	 */
	public function enqueue_block_editor_assets(): void {
		$omnify_editor_path = OMNIFY_PATH . 'assets/blocks/editor.js';
		$omnify_editor_ver  = file_exists($omnify_editor_path) ? (string) filemtime($omnify_editor_path) : OMNIFY_VERSION;

		wp_enqueue_script(
			'omnify-blocks-editor',
			OMNIFY_URL . 'assets/blocks/editor.js',
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-data', 'wp-api-fetch', 'wp-server-side-render'],
			$omnify_editor_ver,
			true
		);
		wp_set_script_translations('omnify-blocks-editor', 'omnifywp-ecommerce', OMNIFY_PATH . 'languages');

		$omnify_editor_css = OMNIFY_PATH . 'assets/blocks/editor.css';
		if (file_exists($omnify_editor_css)) {
			wp_enqueue_style(
				'omnify-blocks-editor',
				OMNIFY_URL . 'assets/blocks/editor.css',
				[],
				(string) filemtime($omnify_editor_css)
			);
		}

		// Load the (scoped) storefront stylesheet so block previews match the front end.
		$omnify_storefront_css = OMNIFY_PATH . 'assets/storefront/storefront.css';
		if (file_exists($omnify_storefront_css)) {
			wp_enqueue_style(
				'omnify-blocks-editor-storefront',
				OMNIFY_URL . 'assets/storefront/storefront.css',
				['omnify-blocks-editor'],
				(string) filemtime($omnify_storefront_css)
			);

			// Apply the store's saved design tokens (colors, radius, font) to previews.
			$omnify_editor_settings = get_option('omnify_settings', []);
			$omnify_editor_settings = is_array($omnify_editor_settings) ? $omnify_editor_settings : [];
			wp_add_inline_style('omnify-blocks-editor-storefront', $this->get_design_inline_css($omnify_editor_settings));
		}

		wp_localize_script('omnify-blocks-editor', 'omnifyBlocks', [
			'products'        => $this->get_block_product_options(),
			'categories'      => $this->get_block_category_options(),
			'templateLibrary' => Omnify_Template_Library::get_library_templates(),
		]);
	}

	/**
	 * Categories for the block editor.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	private function get_block_category_options(): array {
		$omnify_options = [
			['value' => '', 'label' => __('All Categories', 'omnifywp-ecommerce')],
		];
		$omnify_cats = $this->omnify_products->get_all_categories();
		foreach ($omnify_cats as $omnify_cat) {
			$omnify_options[] = [
				'value' => sanitize_title($omnify_cat),
				'label' => (string) $omnify_cat,
			];
		}
		return $omnify_options;
	}

	/**
	 * Published products for the Product Card block's inspector select.
	 *
	 * @return array<int, array{value: int, label: string}>
	 */
	private function get_block_product_options(): array {
		$omnify_options = [
			['value' => 0, 'label' => __('— Select a product —', 'omnifywp-ecommerce')],
		];

		$omnify_products = $this->omnify_products->all(['status' => 'published', 'per_page' => 200]);
		foreach ((array) $omnify_products as $omnify_product) {
			$omnify_id = (int) ($omnify_product['id'] ?? 0);
			if ($omnify_id <= 0) {
				continue;
			}
			$omnify_options[] = [
				'value' => $omnify_id,
				'label' => (string) ($omnify_product['name'] ?? ('#' . $omnify_id)),
			];
		}

		return $omnify_options;
	}

	/**
	 * Render the dedicated Customer Downloads Locker block.
	 *
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	public function render_block_customer_downloads(array $omnify_attributes = []): string {
		$this->enqueue_assets();
		$omnify_title = isset($omnify_attributes['title']) && '' !== trim((string) $omnify_attributes['title'])
			? sanitize_text_field((string) $omnify_attributes['title'])
			: __('My Digital Downloads', 'omnifywp-ecommerce');
		$omnify_subtitle = isset($omnify_attributes['subtitle']) && '' !== trim((string) $omnify_attributes['subtitle'])
			? sanitize_text_field((string) $omnify_attributes['subtitle'])
			: __('Access, manage and re-download your purchased digital files securely.', 'omnifywp-ecommerce');

		// Render customer downloads view
		$omnify_content = '';
		if (shortcode_exists('omnify_customer_downloads')) {
			$omnify_content = (string) do_shortcode('[omnify_customer_downloads]');
		} else {
			$omnify_content = (string) $this->render_customer_portal(['defaultTab' => 'downloads']);
		}

		if (empty(trim($omnify_content))) {
			$omnify_content = '<div class="omnify-portal-empty" style="text-align:center; padding:48px 24px; background:#fff; border:1px solid #e2e8f0; border-radius:12px;"><p style="font-size:15px; color:#64748b; margin:0;">' . esc_html__('No downloads found for your account.', 'omnifywp-ecommerce') . '</p></div>';
		}

		ob_start();
		?>
		<div class="omnify-storefront-wrapper omnify-customer-downloads-block" style="padding: 16px 0;">
			<div style="margin-bottom: 24px;">
				<h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;"><?php echo esc_html($omnify_title); ?></h2>
				<p style="font-size: 14px; color: #64748b; margin: 0;"><?php echo esc_html($omnify_subtitle); ?></p>
			</div>
			<?php echo $omnify_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function render_block_product_card(array $omnify_attributes = []): string {
		$omnify_product_id = absint($omnify_attributes['productId'] ?? 0);
		if (! $omnify_product_id) {
			return '';
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			return '';
		}

		$this->enqueue_assets();
		$omnify_settings      = get_option('omnify_settings', []);
		$omnify_card_layout   = sanitize_key((string) ($omnify_attributes['cardLayout'] ?? 'vertical'));
		$omnify_is_horizontal = ('horizontal' === $omnify_card_layout);
		$omnify_button_action = sanitize_key((string) ($omnify_attributes['buttonAction'] ?? 'cart'));
		$omnify_show_price    = ! isset($omnify_attributes['showPrice']) || ! empty($omnify_attributes['showPrice']);
		$omnify_show_compare  = ! isset($omnify_attributes['showComparePrice']) || ! empty($omnify_attributes['showComparePrice']);
		$omnify_show_rating   = ! isset($omnify_attributes['showRating']) || ! empty($omnify_attributes['showRating']);
		$omnify_show_buy      = ! isset($omnify_attributes['showBuyButton']) || ! empty($omnify_attributes['showBuyButton']);
		$omnify_show_sku      = ! empty($omnify_attributes['showSku']);
		$omnify_show_excerpt  = ! empty($omnify_attributes['showExcerpt']);
		$omnify_show_sale     = ! isset($omnify_attributes['showSaleBadge']) || ! empty($omnify_attributes['showSaleBadge']);
		$omnify_show_stock    = ! empty($omnify_attributes['showStockStatus']);
		$omnify_show_category = ! empty($omnify_attributes['showCategory']);
		$omnify_show_feature  = ! empty($omnify_attributes['showFeaturePill']);
		$omnify_ratio         = sanitize_key((string) ($omnify_attributes['imageRatio'] ?? 'square'));
		$omnify_ratio_css     = ['square' => '1 / 1', 'landscape' => '4 / 3', 'portrait' => '3 / 4'][$omnify_ratio] ?? '1 / 1';
		$omnify_max_width     = absint($omnify_attributes['maxWidth'] ?? 0);
		$omnify_card_width    = $omnify_max_width > 0 ? $omnify_max_width : ($omnify_is_horizontal ? 640 : 320);
		$omnify_button_label  = isset($omnify_attributes['buttonLabel']) && '' !== trim((string) $omnify_attributes['buttonLabel'])
			? sanitize_text_field((string) $omnify_attributes['buttonLabel'])
			: ('checkout' === $omnify_button_action ? __('Buy Now', 'omnifywp-ecommerce') : __('Add to Cart', 'omnifywp-ecommerce'));
		$omnify_sku     = trim((string) ($omnify_product['sku'] ?? ''));
		$omnify_excerpt = wp_trim_words(wp_strip_all_tags((string) ($omnify_product['short_description'] ?? '')), 18);

		$omnify_on_sale = (null !== ($omnify_product['sale_price'] ?? null))
			&& '' !== (string) $omnify_product['sale_price']
			&& (float) $omnify_product['sale_price'] < (float) $omnify_product['price'];

		$omnify_is_out_of_stock = ('outofstock' === ($omnify_product['stock_status'] ?? ''))
			|| (! empty($omnify_product['manage_stock']) && (int) ($omnify_product['stock_qty'] ?? 0) <= 0);
		$omnify_stock_label = $omnify_is_out_of_stock ? __('Out of stock', 'omnifywp-ecommerce') : __('In stock', 'omnifywp-ecommerce');

		$omnify_category = '';
		if ($omnify_show_category && ! empty($omnify_product['categories']) && is_array($omnify_product['categories'])) {
			$omnify_category = (string) reset($omnify_product['categories']);
		}

		$omnify_actual_price = (float) ($omnify_on_sale ? $omnify_product['sale_price'] : $omnify_product['price']);
		$omnify_price_html   = $this->format_store_price($omnify_actual_price, $omnify_settings);
		$omnify_thumb_url    = ! empty($omnify_product['thumbnail_id']) ? wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'medium') : '';
		$omnify_permalink    = home_url('/?omnify_product=' . rawurlencode($omnify_product['slug']));
		$omnify_checkout_url = home_url('/?omnify_checkout=1&add_to_cart=' . (int) $omnify_product['id']);

		$omnify_rating = ['rating' => 0.0, 'count' => 0];
		if ($omnify_show_rating) {
			$omnify_summaries = $this->omnify_reviews->get_rating_summaries([$omnify_product_id]);
			$omnify_rating    = $omnify_summaries[$omnify_product_id] ?? $omnify_rating;
		}

		ob_start();
		?>
		<div class="omnify-block-product-card <?php echo $omnify_is_horizontal ? 'omnify-block-product-card--horizontal' : ''; ?>" style="max-width: <?php echo esc_attr((string) $omnify_card_width); ?>px; border: 1px solid #e2e8f0; border-radius: var(--omnify-radius, 14px); overflow: hidden; background: #ffffff; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); font-family: var(--omnify-font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif); <?php echo $omnify_is_horizontal ? 'display: flex; flex-direction: row; align-items: stretch;' : ''; ?>">
			<?php 
			$omnify_custom_badge = trim((string) ($omnify_attributes['badgeText'] ?? ''));
			$omnify_gallery_flip = ! isset($omnify_attributes['showGalleryFlip']) || ! empty($omnify_attributes['showGalleryFlip']);
			$omnify_gallery_thumb= ($omnify_gallery_flip && ! empty($omnify_product['gallery_urls'][0])) ? (string) $omnify_product['gallery_urls'][0] : '';
			?>
			<?php if ($omnify_thumb_url) : ?>
				<a href="<?php echo esc_url($omnify_permalink); ?>" class="omnify-card-media <?php echo $omnify_gallery_thumb ? 'has-gallery-flip' : ''; ?>" style="display: block; position: relative; <?php echo $omnify_is_horizontal ? 'width: 40%; min-width: 180px; flex-shrink: 0;' : 'width: 100%; aspect-ratio: ' . esc_attr($omnify_ratio_css) . ';'; ?> overflow: hidden; background: #f8fafc;">
					<img src="<?php echo esc_url($omnify_thumb_url); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" class="omnify-primary-thumb" style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease, transform 0.3s ease;" />
					<?php if ($omnify_gallery_thumb) : ?>
						<img src="<?php echo esc_url($omnify_gallery_thumb); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" class="omnify-secondary-thumb" style="position: absolute; top:0; left:0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease;" />
					<?php endif; ?>
					<?php if ($omnify_custom_badge) : ?>
						<span style="position: absolute; top: 10px; left: 10px; z-index: 2; background: #0f172a; color: #ffffff; font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 999px; letter-spacing: 0.04em;"><?php echo esc_html($omnify_custom_badge); ?></span>
					<?php elseif ($omnify_show_sale && $omnify_on_sale) : ?>
						<span style="position: absolute; top: 10px; left: 10px; z-index: 2; background: var(--omnify-primary, #0f766e); color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px;"><?php esc_html_e('Sale', 'omnifywp-ecommerce'); ?></span>
					<?php endif; ?>
					<?php if ($omnify_show_feature) : ?>
						<span style="position: absolute; bottom: 10px; left: 10px; z-index: 2; background: rgba(15, 23, 42, 0.85); color: #ffffff; backdrop-filter: blur(4px); font-size: 10.5px; font-weight: 600; padding: 2px 7px; border-radius: 999px;"><?php echo ('download' === ($omnify_product['type'] ?? '')) ? 'HMAC Signed' : 'Verified'; ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
			<div style="padding: 18px; display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
				<div>
					<?php if ('' !== $omnify_category) : ?>
						<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; margin-bottom: 4px;"><?php echo esc_html($omnify_category); ?></div>
					<?php endif; ?>
					<h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; line-height: 1.3;">
						<a href="<?php echo esc_url($omnify_permalink); ?>" style="color: #0f172a; text-decoration: none;">
							<?php echo esc_html($omnify_product['name']); ?>
						</a>
					</h3>
					<?php if ($omnify_show_stock) : ?>
						<div style="font-size: 11.5px; font-weight: 600; margin-bottom: 8px; color: <?php echo $omnify_is_out_of_stock ? '#dc2626' : '#059669'; ?>;"><?php echo esc_html($omnify_stock_label); ?></div>
					<?php endif; ?>
					<?php if ($omnify_show_sku && '' !== $omnify_sku) : ?>
						<div style="font-size: 11px; color: #94a3b8; margin-bottom: 6px;"><?php printf(esc_html__('SKU: %s', 'omnifywp-ecommerce'), esc_html($omnify_sku)); ?></div>
					<?php endif; ?>
					<?php if ($omnify_show_excerpt && '' !== $omnify_excerpt) : ?>
						<p style="margin: 0 0 12px; font-size: 12.5px; line-height: 1.5; color: #64748b;"><?php echo esc_html($omnify_excerpt); ?></p>
					<?php endif; ?>
					<?php if ($omnify_show_rating && ! empty($omnify_rating['count'])) : ?>
						<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 12px; color: #64748b;">
							<span style="color: #f59e0b; font-weight: 700;">&#9733; <?php echo esc_html(number_format_i18n((float) $omnify_rating['rating'], 1)); ?></span>
							<span><?php
								printf(
									/* translators: %s: number of reviews */
									esc_html(_n('(%s review)', '(%s reviews)', (int) $omnify_rating['count'], 'omnifywp-ecommerce')),
									esc_html(number_format_i18n((int) $omnify_rating['count']))
								);
							?></span>
						</div>
					<?php endif; ?>
				</div>
				<div>
					<?php if ($omnify_show_price) : ?>
						<div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 14px;">
							<span style="font-size: 18px; font-weight: 800; color: #0f172a;"><?php echo wp_kses_post($omnify_price_html); ?></span>
							<?php if ($omnify_show_compare && $omnify_on_sale) : ?>
								<span style="font-size: 14px; color: #94a3b8; text-decoration: line-through;"><?php echo wp_kses_post($this->format_store_price((float) $omnify_product['price'], $omnify_settings)); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ($omnify_show_buy) : ?>
						<?php if ('checkout' === $omnify_button_action) : ?>
							<a href="<?php echo esc_url($omnify_checkout_url); ?>" class="omnify-btn omnify-btn--primary" style="display: block; text-align: center; text-decoration: none; width: 100%; box-sizing: border-box; padding: 10px 16px; background: var(--omnify-primary, #0f766e); color: #ffffff; border: none; border-radius: var(--omnify-radius, 8px); font-size: 13.5px; font-weight: 700; cursor: pointer; transition: background 0.2s;">
								<?php echo esc_html($omnify_button_label); ?>
							</a>
						<?php else : ?>
							<button type="button" class="omnify-btn-add-to-cart" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" style="width: 100%; padding: 10px 16px; background: var(--omnify-primary, #0f766e); color: #ffffff; border: none; border-radius: var(--omnify-radius, 8px); font-size: 13.5px; font-weight: 700; cursor: pointer; transition: background 0.2s;">
								<?php echo esc_html($omnify_button_label); ?>
							</button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render high-converting Order Bump checkout upsell checkbox block.
	 *
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	public function render_block_order_bump(array $omnify_attributes = []): string {
		$omnify_product_id = absint($omnify_attributes['productId'] ?? 0);
		if (! $omnify_product_id) {
			// Fallback to first published product if none explicitly selected in editor
			$omnify_candidates = $this->omnify_products->all(['status' => 'published', 'per_page' => 1]);
			if (! empty($omnify_candidates)) {
				$omnify_product_id = (int) $omnify_candidates[0]['id'];
			}
		}

		if (! $omnify_product_id) {
			return '';
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			return '';
		}

		$this->enqueue_assets();
		$omnify_settings   = get_option('omnify_settings', []);
		$omnify_title      = sanitize_text_field((string) ($omnify_attributes['title'] ?? __('Yes! Add This Exclusive Offer to My Order', 'omnifywp-ecommerce')));
		$omnify_badge      = sanitize_text_field((string) ($omnify_attributes['badgeText'] ?? __('ONE-TIME OFFER', 'omnifywp-ecommerce')));
		$omnify_desc       = sanitize_text_field((string) ($omnify_attributes['description'] ?? ''));
		$omnify_color      = sanitize_hex_color((string) ($omnify_attributes['highlightColor'] ?? '')) ?: '#0f766e';
		$omnify_disc_type  = sanitize_key((string) ($omnify_attributes['discountType'] ?? 'percentage'));
		$omnify_disc_val   = (float) ($omnify_attributes['discountVal'] ?? 20);

		$omnify_base_price = (float) ($omnify_product['price'] ?? 0);
		if ('percentage' === $omnify_disc_type && $omnify_disc_val > 0) {
			$omnify_bump_price = max(0.0, $omnify_base_price * (1 - ($omnify_disc_val / 100)));
		} elseif ('fixed' === $omnify_disc_type && $omnify_disc_val > 0) {
			$omnify_bump_price = max(0.0, $omnify_base_price - $omnify_disc_val);
		} else {
			$omnify_bump_price = $omnify_base_price;
		}

		$omnify_thumb_url = ! empty($omnify_product['thumbnail_id']) ? wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'thumbnail') : '';

		ob_start();
		?>
		<div class="omnify-order-bump-block" style="border: 2px dashed <?php echo esc_attr($omnify_color); ?>; border-radius: 12px; padding: 16px; background: #f0fdf4; margin: 16px 0; font-family: inherit; position: relative;">
			<div style="display: flex; align-items: flex-start; gap: 14px;">
				<div style="padding-top: 4px;">
					<input type="checkbox" id="omnify-bump-cb-<?php echo esc_attr((string) $omnify_product_id); ?>" class="omnify-order-bump-checkbox" data-product-id="<?php echo esc_attr((string) $omnify_product_id); ?>" data-price="<?php echo esc_attr((string) $omnify_bump_price); ?>" style="width: 20px; height: 20px; cursor: pointer; accent-color: <?php echo esc_attr($omnify_color); ?>;" />
				</div>
				<?php if ($omnify_thumb_url) : ?>
					<img src="<?php echo esc_url($omnify_thumb_url); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0;" />
				<?php endif; ?>
				<div style="flex-grow: 1;">
					<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
						<?php if ($omnify_badge) : ?>
							<span style="background: <?php echo esc_attr($omnify_color); ?>; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 999px; letter-spacing: 0.05em;"><?php echo esc_html($omnify_badge); ?></span>
						<?php endif; ?>
						<label for="omnify-bump-cb-<?php echo esc_attr((string) $omnify_product_id); ?>" style="font-size: 14.5px; font-weight: 700; color: #0f172a; cursor: pointer; margin: 0;">
							<?php echo esc_html($omnify_title); ?>
						</label>
					</div>
					<div style="margin-bottom: 6px;">
						<span style="font-size: 15px; font-weight: 800; color: <?php echo esc_attr($omnify_color); ?>;"><?php echo wp_kses_post($this->format_store_price($omnify_bump_price, $omnify_settings)); ?></span>
						<?php if ($omnify_bump_price < $omnify_base_price) : ?>
							<span style="font-size: 12.5px; color: #94a3b8; text-decoration: line-through; margin-left: 6px;"><?php echo wp_kses_post($this->format_store_price($omnify_base_price, $omnify_settings)); ?></span>
						<?php endif; ?>
					</div>
					<?php if ($omnify_desc) : ?>
						<p style="margin: 0; font-size: 12.5px; line-height: 1.5; color: #475569;"><?php echo esc_html($omnify_desc); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render Sticky Add-to-Cart mobile/desktop conversion bar block.
	 *
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	public function render_block_sticky_add_to_cart(array $omnify_attributes = []): string {
		$omnify_product_id = absint($omnify_attributes['productId'] ?? 0);
		if (! $omnify_product_id) {
			$omnify_candidates = $this->omnify_products->all(['status' => 'published', 'per_page' => 1]);
			if (! empty($omnify_candidates)) {
				$omnify_product_id = (int) $omnify_candidates[0]['id'];
			}
		}

		if (! $omnify_product_id) {
			return '';
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			return '';
		}

		$this->enqueue_assets();
		$omnify_settings   = get_option('omnify_settings', []);
		$omnify_position   = sanitize_key((string) ($omnify_attributes['position'] ?? 'bottom'));
		$omnify_visibility = sanitize_key((string) ($omnify_attributes['deviceVisibility'] ?? 'all'));
		$omnify_show_thumb = ! isset($omnify_attributes['showThumbnail']) || ! empty($omnify_attributes['showThumbnail']);
		$omnify_show_price = ! isset($omnify_attributes['showPrice']) || ! empty($omnify_attributes['showPrice']);
		$omnify_show_qty   = ! isset($omnify_attributes['showQuantity']) || ! empty($omnify_attributes['showQuantity']);
		$omnify_action     = sanitize_key((string) ($omnify_attributes['buttonAction'] ?? 'cart'));
		$omnify_label      = sanitize_text_field((string) ($omnify_attributes['buttonLabel'] ?? __('Add to Cart', 'omnifywp-ecommerce')));

		$omnify_thumb_url = ! empty($omnify_product['thumbnail_id']) ? wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'thumbnail') : '';
		$omnify_price     = (float) (! empty($omnify_product['sale_price']) && (float) $omnify_product['sale_price'] < (float) $omnify_product['price'] ? $omnify_product['sale_price'] : $omnify_product['price']);
		$omnify_price_fmt = $this->format_store_price($omnify_price, $omnify_settings);
		$omnify_checkout  = home_url('/?omnify_checkout=1&add_to_cart=' . (int) $omnify_product['id']);

		$omnify_classes = ['omnify-sticky-atc-bar', 'omnify-sticky-atc-bar--' . $omnify_position];
		if ('mobile' === $omnify_visibility) {
			$omnify_classes[] = 'omnify-show-mobile-only';
		} elseif ('desktop' === $omnify_visibility) {
			$omnify_classes[] = 'omnify-show-desktop-only';
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr(implode(' ', $omnify_classes)); ?>" data-position="<?php echo esc_attr($omnify_position); ?>" style="position: fixed; <?php echo 'top' === $omnify_position ? 'top: 0;' : 'bottom: 0;'; ?> left: 0; right: 0; z-index: 9999; background: #ffffff; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; font-family: inherit;">
			<div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
				<?php if ($omnify_show_thumb && $omnify_thumb_url) : ?>
					<img src="<?php echo esc_url($omnify_thumb_url); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0;" />
				<?php endif; ?>
				<div style="min-width: 0;">
					<div style="font-weight: 700; font-size: 14px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px;"><?php echo esc_html($omnify_product['name']); ?></div>
					<?php if ($omnify_show_price) : ?>
						<div style="font-weight: 800; font-size: 13.5px; color: var(--omnify-primary, #0f766e);"><?php echo wp_kses_post($omnify_price_fmt); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
				<?php if ($omnify_show_qty) : ?>
					<div style="display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #f8fafc;">
						<button type="button" class="omnify-qty-step" data-step="-1" style="border:none;background:none;padding:6px 10px;cursor:pointer;font-weight:bold;">-</button>
						<input type="number" class="omnify-sticky-qty-input" value="1" min="1" max="99" style="width:36px;text-align:center;border:none;background:none;font-size:13px;font-weight:bold;-moz-appearance:textfield;" />
						<button type="button" class="omnify-qty-step" data-step="1" style="border:none;background:none;padding:6px 10px;cursor:pointer;font-weight:bold;">+</button>
					</div>
				<?php endif; ?>
				<?php if ('checkout' === $omnify_action) : ?>
					<a href="<?php echo esc_url($omnify_checkout); ?>" class="omnify-btn" style="background: var(--omnify-primary, #0f766e); color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13.5px;"><?php echo esc_html($omnify_label); ?></a>
				<?php else : ?>
					<button type="button" class="omnify-btn-add-to-cart" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" style="background: var(--omnify-primary, #0f766e); color: #ffffff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13.5px; cursor: pointer;"><?php echo esc_html($omnify_label); ?></button>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render dynamic Gamified Free Shipping Progress Goal bar block.
	 *
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	public function render_block_free_shipping_goal(array $omnify_attributes = []): string {
		$this->enqueue_assets();
		$omnify_settings  = get_option('omnify_settings', []);
		$omnify_threshold = (float) ($omnify_attributes['threshold'] ?? 50);
		if ($omnify_threshold <= 0) {
			$omnify_threshold = (float) ($omnify_settings['free_shipping_threshold'] ?? 50);
		}
		$omnify_threshold = max(1.0, $omnify_threshold);

		$omnify_show_icon     = ! isset($omnify_attributes['showIcon']) || ! empty($omnify_attributes['showIcon']);
		$omnify_show_confetti = ! isset($omnify_attributes['showConfetti']) || ! empty($omnify_attributes['showConfetti']);
		$omnify_bar_color     = sanitize_hex_color((string) ($omnify_attributes['barColor'] ?? '')) ?: '#0f766e';
		$omnify_initial_msg   = sanitize_text_field((string) ($omnify_attributes['initialMsg'] ?? __('Add %s more to unlock FREE Shipping!', 'omnifywp-ecommerce')));
		$omnify_success_msg   = sanitize_text_field((string) ($omnify_attributes['successMsg'] ?? __('🎉 Congratulations! You have unlocked FREE Shipping!', 'omnifywp-ecommerce')));

		$omnify_cart_subtotal = 0.0;
		$omnify_progress_pct  = min(100.0, ($omnify_cart_subtotal / $omnify_threshold) * 100);
		$omnify_remaining     = max(0.0, $omnify_threshold - $omnify_cart_subtotal);
		$omnify_is_unlocked   = ($omnify_remaining <= 0);

		$omnify_formatted_remaining = $this->format_store_price($omnify_remaining, $omnify_settings);
		$omnify_active_msg = $omnify_is_unlocked ? $omnify_success_msg : sprintf($omnify_initial_msg, $omnify_formatted_remaining);

		ob_start();
		?>
		<div class="omnify-free-shipping-goal-block" data-threshold="<?php echo esc_attr((string) $omnify_threshold); ?>" data-confetti="<?php echo $omnify_show_confetti ? '1' : '0'; ?>" data-initial-template="<?php echo esc_attr($omnify_initial_msg); ?>" data-success-msg="<?php echo esc_attr($omnify_success_msg); ?>" style="padding: 14px 18px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; margin: 16px 0; font-family: inherit; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
			<div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
				<div class="omnify-goal-text" style="font-size: 13.5px; font-weight: 700; color: #1e293b;">
					<?php if ($omnify_show_icon) : ?>
						<span style="margin-right: 6px;">🚚</span>
					<?php endif; ?>
					<span class="omnify-goal-msg-target"><?php echo wp_kses_post($omnify_active_msg); ?></span>
				</div>
				<div class="omnify-goal-badge" style="font-size: 12px; font-weight: 800; color: <?php echo esc_attr($omnify_bar_color); ?>;">
					<span class="omnify-goal-pct-target"><?php echo esc_html((string) round($omnify_progress_pct)); ?></span>%
				</div>
			</div>
			<div style="width: 100%; height: 8px; border-radius: 999px; background: #e2e8f0; overflow: hidden; position: relative;">
				<div class="omnify-goal-fill" style="width: <?php echo esc_attr((string) $omnify_progress_pct); ?>%; height: 100%; background: <?php echo esc_attr($omnify_bar_color); ?>; border-radius: 999px; transition: width 0.4s ease-in-out;"></div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render Trust Badges & Guarantee strip block.
	 *
	 * @param array<string, mixed> $omnify_attributes Block attributes.
	 */
	public function render_block_trust_badge_bar(array $omnify_attributes = []): string {
		$omnify_layout     = sanitize_key((string) ($omnify_attributes['layout'] ?? 'horizontal'));
		$omnify_style      = sanitize_key((string) ($omnify_attributes['badgeStyle'] ?? 'emerald'));
		$omnify_border     = ! isset($omnify_attributes['showBorder']) || ! empty($omnify_attributes['showBorder']);
		$omnify_guarantees = ! isset($omnify_attributes['showGuarantees']) || ! empty($omnify_attributes['showGuarantees']);
		$omnify_payments   = ! isset($omnify_attributes['showPayments']) || ! empty($omnify_attributes['showPayments']);

		$omnify_is_grid  = ('grid-2x2' === $omnify_layout);
		$omnify_support  = ! isset($omnify_attributes['showSupport']) || ! empty($omnify_attributes['showSupport']);
		$omnify_returns  = ! isset($omnify_attributes['showReturns']) || ! empty($omnify_attributes['showReturns']);
		$omnify_badges   = [];

		if ($omnify_guarantees) {
			$omnify_badges[] = ['icon' => '🔒', 'title' => __('256-Bit SSL Encryption', 'omnifywp-ecommerce'), 'sub' => __('Guaranteed Safe Checkout', 'omnifywp-ecommerce')];
			$omnify_badges[] = ['icon' => '🛡️', 'title' => __('30-Day Money Back', 'omnifywp-ecommerce'), 'sub' => __('100% Risk-Free Guarantee', 'omnifywp-ecommerce')];
			$omnify_badges[] = ['icon' => '⚡', 'title' => __('Instant HMAC Dispatch', 'omnifywp-ecommerce'), 'sub' => __('Immediate Secure Access', 'omnifywp-ecommerce')];
		}
		if ($omnify_payments) {
			$omnify_badges[] = ['icon' => '💳', 'title' => __('Major Cards & Stripe', 'omnifywp-ecommerce'), 'sub' => __('Visa, MC, Amex, PayPal, Apple Pay', 'omnifywp-ecommerce')];
		}
		if ($omnify_support) {
			$omnify_badges[] = ['icon' => '🎧', 'title' => __('24/7 Priority Support', 'omnifywp-ecommerce'), 'sub' => __('Dedicated Expert Assistance', 'omnifywp-ecommerce')];
		}
		if ($omnify_returns) {
			$omnify_badges[] = ['icon' => '🔄', 'title' => __('Hassle-Free Returns', 'omnifywp-ecommerce'), 'sub' => __('Fast Automated Processing', 'omnifywp-ecommerce')];
		}

		ob_start();
		?>
		<div class="omnify-trust-badge-bar-block <?php echo 'omnify-trust-badge-bar--' . esc_attr($omnify_layout); ?>" style="<?php echo $omnify_border ? 'border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; padding: 16px 20px;' : 'padding: 8px 0;'; ?> margin: 16px 0; font-family: inherit;">
			<div style="display: grid; grid-template-columns: <?php echo $omnify_is_grid ? 'repeat(2, 1fr)' : 'repeat(auto-fit, minmax(200px, 1fr))'; ?>; gap: 14px;">
				<?php foreach ($omnify_badges as $omnify_b) : ?>
					<div style="display: flex; align-items: center; gap: 10px;">
						<div style="width: 36px; height: 36px; border-radius: 50%; background: #f0fdf4; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
							<?php echo esc_html($omnify_b['icon']); ?>
						</div>
						<div>
							<div style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.2;"><?php echo esc_html($omnify_b['title']); ?></div>
							<div style="font-size: 11px; color: #64748b;"><?php echo esc_html($omnify_b['sub']); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Helper to resolve product for atomic blocks (from attribute, post global, or published candidate).
	 */
	private function resolve_atomic_product(array $omnify_attributes): ?array {
		$omnify_product_id = absint($omnify_attributes['productId'] ?? 0);
		if (! $omnify_product_id) {
			global $post;
			if ($post && 'omnify_product' === get_post_type($post)) {
				$omnify_product_id = (int) $post->ID;
			}
		}
		if (! $omnify_product_id) {
			$omnify_candidates = $this->omnify_products->all(['status' => 'published', 'per_page' => 1]);
			if (! empty($omnify_candidates)) {
				$omnify_product_id = (int) $omnify_candidates[0]['id'];
			}
		}
		if (! $omnify_product_id) {
			return null;
		}
		return $this->omnify_products->find($omnify_product_id);
	}

	/**
	 * Render atomic Product Image.
	 */
	public function render_atomic_product_image(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$omnify_ratio   = sanitize_text_field((string) ($omnify_attributes['aspectRatio'] ?? '1/1'));
		$omnify_radius  = absint($omnify_attributes['borderRadius'] ?? 10);
		$omnify_zoom    = ! isset($omnify_attributes['zoomOnHover']) || ! empty($omnify_attributes['zoomOnHover']);
		$omnify_thumb   = ! empty($omnify_product['thumbnail_id']) ? wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'large') : '';
		$omnify_link    = home_url('/?omnify_product=' . rawurlencode($omnify_product['slug']));

		if (! $omnify_thumb) return '';

		ob_start();
		?>
		<div class="omnify-atomic-image-wrap <?php echo $omnify_zoom ? 'omnify-zoom-hover' : ''; ?>" style="border-radius: <?php echo esc_attr((string) $omnify_radius); ?>px; overflow: hidden; aspect-ratio: <?php echo esc_attr($omnify_ratio); ?>; background: #f8fafc; margin-bottom: 12px;">
			<a href="<?php echo esc_url($omnify_link); ?>" style="display: block; width: 100%; height: 100%;">
				<img src="<?php echo esc_url($omnify_thumb); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" />
			</a>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render atomic Product Title.
	 */
	public function render_atomic_product_title(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$omnify_tag     = sanitize_key((string) ($omnify_attributes['headingTag'] ?? 'h3'));
		if (! in_array($omnify_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'], true)) $omnify_tag = 'h3';
		$omnify_link    = ! isset($omnify_attributes['linkToPost']) || ! empty($omnify_attributes['linkToPost']);
		$omnify_size    = absint($omnify_attributes['fontSize'] ?? 18);
		$omnify_url     = home_url('/?omnify_product=' . rawurlencode($omnify_product['slug']));

		ob_start();
		?>
		<<?php echo esc_attr($omnify_tag); ?> class="omnify-atomic-title" style="margin: 0 0 8px; font-size: <?php echo esc_attr((string) $omnify_size); ?>px; font-weight: 700; line-height: 1.3;">
			<?php if ($omnify_link) : ?>
				<a href="<?php echo esc_url($omnify_url); ?>" style="color: inherit; text-decoration: none;">
					<?php echo esc_html($omnify_product['name']); ?>
				</a>
			<?php else : ?>
				<?php echo esc_html($omnify_product['name']); ?>
			<?php endif; ?>
		</<?php echo esc_attr($omnify_tag); ?>>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render atomic Product Price.
	 */
	public function render_atomic_product_price(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_show_reg = ! isset($omnify_attributes['showRegular']) || ! empty($omnify_attributes['showRegular']);
		$omnify_show_sav = ! isset($omnify_attributes['showSavings']) || ! empty($omnify_attributes['showSavings']);
		$omnify_size     = absint($omnify_attributes['fontSize'] ?? 18);

		$omnify_on_sale = (null !== ($omnify_product['sale_price'] ?? null))
			&& '' !== (string) $omnify_product['sale_price']
			&& (float) $omnify_product['sale_price'] < (float) $omnify_product['price'];

		$omnify_current = (float) ($omnify_on_sale ? $omnify_product['sale_price'] : $omnify_product['price']);
		$omnify_regular = (float) $omnify_product['price'];

		ob_start();
		?>
		<div class="omnify-atomic-price" style="display: flex; align-items: baseline; gap: 8px; margin: 4px 0 10px; font-size: <?php echo esc_attr((string) $omnify_size); ?>px;">
			<span style="font-weight: 800; color: #0f172a;"><?php echo wp_kses_post($this->format_store_price($omnify_current, $omnify_settings)); ?></span>
			<?php if ($omnify_show_reg && $omnify_on_sale) : ?>
				<span style="font-size: 0.8em; color: #94a3b8; text-decoration: line-through;"><?php echo wp_kses_post($this->format_store_price($omnify_regular, $omnify_settings)); ?></span>
			<?php endif; ?>
			<?php if ($omnify_show_sav && $omnify_on_sale && $omnify_regular > 0) : ?>
				<span style="font-size: 0.65em; font-weight: 800; background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 999px;">
					<?php printf(esc_html__('Save %s', 'omnifywp-ecommerce'), esc_html((string) round((($omnify_regular - $omnify_current) / $omnify_regular) * 100)) . '%'); ?>
				</span>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render atomic Product Rating.
	 */
	public function render_atomic_product_rating(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$omnify_pid     = (int) $omnify_product['id'];
		$omnify_color   = sanitize_hex_color((string) ($omnify_attributes['starColor'] ?? '')) ?: '#f59e0b';
		$omnify_show_cnt= ! isset($omnify_attributes['showCount']) || ! empty($omnify_attributes['showCount']);
		$omnify_summary = $this->omnify_reviews->get_rating_summaries([$omnify_pid])[$omnify_pid] ?? ['rating' => 0.0, 'count' => 0];

		ob_start();
		?>
		<div class="omnify-atomic-rating" style="display: flex; align-items: center; gap: 6px; margin: 4px 0 8px; font-size: 13px; color: #64748b;">
			<span style="color: <?php echo esc_attr($omnify_color); ?>; font-weight: 700;">★ <?php echo esc_html(number_format_i18n((float) $omnify_summary['rating'], 1)); ?></span>
			<?php if ($omnify_show_cnt) : ?>
				<span>(<?php echo esc_html(number_format_i18n((int) $omnify_summary['count'])); ?>)</span>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render atomic Product Badge.
	 */
	public function render_atomic_product_badge(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$omnify_color   = sanitize_hex_color((string) ($omnify_attributes['badgeColor'] ?? '')) ?: '#0f766e';
		$omnify_custom  = trim((string) ($omnify_attributes['customText'] ?? ''));

		$omnify_on_sale = (null !== ($omnify_product['sale_price'] ?? null))
			&& '' !== (string) $omnify_product['sale_price']
			&& (float) $omnify_product['sale_price'] < (float) $omnify_product['price'];

		$omnify_text = $omnify_custom ?: ($omnify_on_sale ? __('Sale', 'omnifywp-ecommerce') : __('New', 'omnifywp-ecommerce'));

		ob_start();
		?>
		<div class="omnify-atomic-badge" style="display: inline-block; margin-bottom: 6px;">
			<span style="background: <?php echo esc_attr($omnify_color); ?>; color: #ffffff; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.04em;">
				<?php echo esc_html($omnify_text); ?>
			</span>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render atomic Product Button.
	 */
	public function render_atomic_product_button(array $omnify_attributes = []): string {
		$omnify_product = $this->resolve_atomic_product($omnify_attributes);
		if (! $omnify_product) return '';

		$this->enqueue_assets();
		$omnify_action  = sanitize_key((string) ($omnify_attributes['action'] ?? 'cart'));
		$omnify_label   = sanitize_text_field((string) ($omnify_attributes['buttonText'] ?? __('Add to Cart', 'omnifywp-ecommerce')));
		$omnify_show_qty= ! empty($omnify_attributes['showQty']);
		$omnify_url     = home_url('/?omnify_checkout=1&add_to_cart=' . (int) $omnify_product['id']);

		ob_start();
		?>
		<div class="omnify-atomic-button-wrap" style="display: flex; align-items: center; gap: 8px; margin: 8px 0;">
			<?php if ($omnify_show_qty) : ?>
				<input type="number" class="omnify-atomic-qty" value="1" min="1" max="99" style="width: 50px; padding: 8px 4px; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; font-weight: bold;" />
			<?php endif; ?>
			<?php if ('checkout' === $omnify_action) : ?>
				<a href="<?php echo esc_url($omnify_url); ?>" class="omnify-btn" style="flex-grow: 1; text-align: center; text-decoration: none; padding: 10px 16px; background: var(--omnify-primary, #0f766e); color: #ffffff; border-radius: 8px; font-weight: 700; font-size: 13.5px;">
					<?php echo esc_html($omnify_label); ?>
				</a>
			<?php else : ?>
				<button type="button" class="omnify-btn-add-to-cart" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" style="flex-grow: 1; padding: 10px 16px; background: var(--omnify-primary, #0f766e); color: #ffffff; border: none; border-radius: 8px; font-weight: 700; font-size: 13.5px; cursor: pointer;">
					<?php echo esc_html($omnify_label); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Handle frontend review submission: create or update.
	 * Posted to admin-post.php with action=omnify_submit_review.
	 */
	public function handle_submit_review(): void {
		// Require logged-in user
		if (! is_user_logged_in()) {
			wp_die(esc_html__('You must be logged in to submit a review.', 'omnifywp-ecommerce'), 403);
		}

		$omnify_product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
		if (! $omnify_product_id) {
			wp_die(esc_html__('Invalid product.', 'omnifywp-ecommerce'), 400);
		}

		// Verify nonce
		$omnify_nonce = isset($_POST['omnify_review_nonce']) ? sanitize_text_field(wp_unslash($_POST['omnify_review_nonce'])) : '';
		if (! wp_verify_nonce($omnify_nonce, 'omnify_submit_review_' . $omnify_product_id)) {
			wp_die(esc_html__('Security check failed.', 'omnifywp-ecommerce'), 403);
		}

		$omnify_wp_user  = wp_get_current_user();
		$omnify_user_email = sanitize_email($omnify_wp_user->user_email);
		$omnify_rating   = isset($_POST['rating']) ? min(5, max(1, absint(wp_unslash($_POST['rating'])))) : 0;
		$omnify_content  = isset($_POST['content']) ? trim((string) sanitize_textarea_field(wp_unslash($_POST['content']))) : '';

		// Build redirect URL back to the product page
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_storefront_page_id = ! empty($omnify_settings['page_storefront']) ? absint($omnify_settings['page_storefront']) : 0;
		$omnify_storefront_url = $omnify_storefront_page_id ? get_permalink($omnify_storefront_page_id) : home_url('/');

		// Get product slug for redirect
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			wp_die(esc_html__('Product not found.', 'omnifywp-ecommerce'), 404);
		}
		$omnify_product_slug = $omnify_product['slug'] ?? '';
		$omnify_redirect_base = add_query_arg('omnify_product', $omnify_product_slug, $omnify_storefront_url);
		$omnify_redirect_base = $omnify_redirect_base . '#omnify-reviews-pane';

		if (! $omnify_rating) {
			wp_safe_redirect(add_query_arg('review', 'no_rating', $omnify_redirect_base));
			exit;
		}

		if ('' === $omnify_content) {
			wp_safe_redirect(add_query_arg('review', 'no_content', $omnify_redirect_base));
			exit;
		}

		// Handle image uploads
		$omnify_image_urls = [];
		if (isset($_FILES['review_images']) && is_array($_FILES['review_images']) && ! empty($_FILES['review_images']['name'][0])) {
			if (! function_exists('wp_handle_upload')) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_names     = (array) wp_unslash($_FILES['review_images']['name']);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_types     = (array) wp_unslash($_FILES['review_images']['type'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_tmp_names = (array) wp_unslash($_FILES['review_images']['tmp_name'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_errors    = (array) wp_unslash($_FILES['review_images']['error'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_sizes     = (array) wp_unslash($_FILES['review_images']['size'] ?? []);

			$omnify_allowed_mimes = [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
				'webp' => 'image/webp',
			];
			$omnify_upload_overrides = [
				'test_form' => false,
				'mimes'     => [
					'jpg|jpeg|jpe' => 'image/jpeg',
					'png'          => 'image/png',
					'webp'         => 'image/webp',
				],
			];

			foreach ($omnify_names as $omnify_idx => $omnify_fname) {
				if (empty($omnify_fname)) {
					continue;
				}
				$omnify_err = isset($omnify_errors[$omnify_idx]) ? (int) $omnify_errors[$omnify_idx] : UPLOAD_ERR_NO_FILE;
				$omnify_tmp = isset($omnify_tmp_names[$omnify_idx]) ? (string) $omnify_tmp_names[$omnify_idx] : '';
				$omnify_sz  = isset($omnify_sizes[$omnify_idx]) ? (int) $omnify_sizes[$omnify_idx] : 0;

				if (UPLOAD_ERR_OK !== $omnify_err || ! is_uploaded_file($omnify_tmp)) {
					continue;
				}

				// Enforce maximum 5MB per image file
				if ($omnify_sz <= 0 || $omnify_sz > 5 * 1024 * 1024) {
					continue;
				}

				$omnify_fname_sanitized = sanitize_file_name($omnify_fname);
				$omnify_file_check      = wp_check_filetype_and_ext($omnify_tmp, $omnify_fname_sanitized, $omnify_allowed_mimes);
				if (empty($omnify_file_check['ext']) || empty($omnify_file_check['type'])) {
					continue;
				}

				// Magic byte validation to ensure real image data (Item 30)
				$omnify_img_info = @getimagesize($omnify_tmp);
				if (false === $omnify_img_info || ! in_array($omnify_img_info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
					continue;
				}

				$omnify_file = [
					'name'     => $omnify_fname_sanitized,
					'type'     => $omnify_file_check['type'],
					'tmp_name' => $omnify_tmp,
					'error'    => $omnify_err,
					'size'     => $omnify_sz,
				];
				$omnify_upload = wp_handle_upload($omnify_file, $omnify_upload_overrides);
				if (! empty($omnify_upload['url'])) {
					$omnify_image_urls[] = esc_url_raw($omnify_upload['url']);
				}
				if (count($omnify_image_urls) >= 5) {
					break;
				}
			}
		}

		$omnify_review_id = isset($_POST['review_id']) ? absint(wp_unslash($_POST['review_id'])) : 0;

		if ($omnify_review_id > 0) {
			// --- EDIT existing review ---
			// Security: verify the review belongs to the current user
			$omnify_existing = $this->omnify_reviews->find($omnify_review_id);
			if (! $omnify_existing || sanitize_email($omnify_existing['customer_email']) !== $omnify_user_email) {
				wp_die(esc_html__('You do not have permission to edit this review.', 'omnifywp-ecommerce'), 403);
			}

			$omnify_update_data = [
				'rating'         => $omnify_rating,
				'review_content' => $omnify_content,
				'status'         => 'pending', // reset to pending after edit
			];

			if (! empty($omnify_image_urls)) {
				$omnify_update_data['media'] = $omnify_image_urls;
			} elseif (! empty($omnify_existing['media']) && is_array($omnify_existing['media'])) {
				$omnify_update_data['media'] = $omnify_existing['media'];
			}

			$this->omnify_reviews->update($omnify_review_id, $omnify_update_data);
			wp_safe_redirect(add_query_arg('review', 'updated', $omnify_redirect_base));
			exit;
		}

		// --- CREATE new review ---
		$omnify_name = '';
		$omnify_name = trim($omnify_wp_user->first_name . ' ' . $omnify_wp_user->last_name);
		if (empty($omnify_name)) {
			$omnify_name = $omnify_wp_user->display_name;
		}

		$omnify_review_data = [
			'product_id'     => $omnify_product_id,
			'customer_name'  => $omnify_name,
			'customer_email' => $omnify_user_email,
			'rating'         => $omnify_rating,
			'review_content' => $omnify_content,
			'status'         => 'pending',
		];

		if (! empty($omnify_image_urls)) {
			$omnify_review_data['media'] = $omnify_image_urls;
		}

		$this->omnify_reviews->create($omnify_review_data);
		wp_safe_redirect(add_query_arg('review', 'submitted', $omnify_redirect_base));
		exit;
	}

	public function is_omnify_frontend_request(): bool {
		if (is_admin()) {
			return false;
		}

		if (isset($_GET['omnify_product'])) {
			return true;
		}

		$post = get_post();
		if (! $post) {
			return false;
		}

		$content = (string) $post->post_content;
		if (
			has_shortcode($content, 'omnify_storefront') ||
			has_shortcode($content, 'omnify_cart') ||
			has_shortcode($content, 'omnify_mini_cart') ||
			has_shortcode($content, 'omnify_checkout') ||
			has_shortcode($content, 'omnify_customer_portal') ||
			has_shortcode($content, 'omnify_customer_downloads') ||
			has_shortcode($content, 'omnify_order_tracking') ||
			has_shortcode($content, 'omnify_currency_switcher')
		) {
			return true;
		}

		// Gutenberg blocks need the storefront assets too (render callbacks run after
		// wp_enqueue_scripts, so they must be detected here).
		foreach (self::block_names() as $omnify_block_name) {
			if (function_exists('has_block') && has_block($omnify_block_name, $post)) {
				return true;
			}
		}

		$settings = get_option('omnify_settings', []);
		$settings = is_array($settings) ? $settings : [];
		$current_id = (int) $post->ID;
		$configured_ids = array_filter([
			(int) ($settings['page_storefront'] ?? 0),
			(int) ($settings['cart_page_id'] ?? 0),
			(int) ($settings['checkout_page_id'] ?? 0),
			(int) ($settings['portal_page_id'] ?? 0),
		]);

		return in_array($current_id, $configured_ids, true);
	}

	public function maybe_enqueue_omnify_assets(): void {
		$settings = get_option('omnify_settings', []);
		$settings = is_array($settings) ? $settings : [];
		$mini_cart_enabled = ! empty($settings['mini_cart_menu_enabled']);

		if ($this->is_omnify_frontend_request() || $mini_cart_enabled) {
			$this->enqueue_assets();
		}
	}

	public function maybe_enqueue_menu_mini_cart_assets(): void {
		$this->maybe_enqueue_omnify_assets();
	}

	public function output_theme_isolation_barrier_styles(): void {
		static $omnify_isolation_printed = false;
		if ($omnify_isolation_printed) {
			return;
		}

		if (! $this->is_omnify_frontend_request()) {
			$settings = get_option('omnify_settings', []);
			$settings = is_array($settings) ? $settings : [];
			if (empty($settings['mini_cart_menu_enabled'])) {
				return;
			}
		}

		$omnify_isolation_printed = true;
		echo '<style id="omnify-theme-isolation-barrier">' . "\n";
		echo $this->get_theme_isolation_css();
		echo "\n" . '</style>' . "\n";
	}

	public function get_theme_isolation_css(): string {
		return "
			/* ==========================================================================
			   OMNIFY THEME ISOLATION & ANTI-OVERRIDE BARRIER
			   Guarantees plugin frontend is 100% immune to all WordPress theme CSS
			   ========================================================================== */

			/* 1. Universal Box-Sizing & Layout Protection */
			:is(html body, #page, #content, .site, .site-main, .entry-content, article) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart, .omnify-quickview-modal, .omnify-toast-container, .omnify-floating-cart-btn),
			:is(html body, #page, #content, .site, .site-main, .entry-content, article) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart, .omnify-quickview-modal, .omnify-toast-container, .omnify-floating-cart-btn) *,
			:is(html body, #page, #content, .site, .site-main, .entry-content, article) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart, .omnify-quickview-modal, .omnify-toast-container, .omnify-floating-cart-btn) *::before,
			:is(html body, #page, #content, .site, .site-main, .entry-content, article) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart, .omnify-quickview-modal, .omnify-toast-container, .omnify-floating-cart-btn) *::after {
				box-sizing: border-box !important;
				-webkit-font-smoothing: antialiased !important;
				-moz-osx-font-smoothing: grayscale !important;
			}

			/* 2. Neutralize Theme Button Poisoning */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) :where(button, input[type='button'], input[type='submit']) {
				letter-spacing: normal !important;
				text-transform: none !important;
				text-shadow: none !important;
				margin: 0 !important;
			}

			/* 3. Neutralize Theme Link Distortions */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) a:not(.omnify-link-underlined):not(#omnify-cart-clear) {
				text-decoration: none !important;
				border-bottom: none !important;
				box-shadow: none !important;
			}

			/* 4. Neutralize Theme List Bullets & Spacing */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) :is(ul, ol) {
				list-style: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) li {
				list-style: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			/* 5. Neutralize Theme Heading & Paragraph Distortions */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) :is(h1, h2, h3, h4, h5, h6) {
				font-family: 'Montserrat', 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
				text-transform: none !important;
				letter-spacing: normal !important;
			}

			/* 6. Neutralize Theme Table Striping & Heavy Borders */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper) :is(table, thead, tbody, tr, th, td) {
				border: none !important;
				background: transparent !important;
				box-shadow: none !important;
			}

			/* 7. Protect Stepper Inputs from Theme Padding & Squeezing */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page) input.omnify-cart-qty-input,
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page) input.omnify-checkout-qty-input {
				padding: 0 !important;
				margin: 0 !important;
				width: 32px !important;
				height: 26px !important;
				min-height: 26px !important;
				max-height: 26px !important;
				border: none !important;
				background: transparent !important;
				text-align: center !important;
				font-size: 13px !important;
				font-weight: 600 !important;
				color: #0F172A !important;
				box-shadow: none !important;
				outline: none !important;
				appearance: textfield !important;
				-moz-appearance: textfield !important;
			}

			/* 8. Protect Stepper Buttons from Theme Bloat */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page) :is(button.omnify-cart-qty-btn, button.omnify-checkout-qty-btn) {
				width: 26px !important;
				height: 26px !important;
				min-width: 26px !important;
				min-height: 26px !important;
				max-width: 26px !important;
				max-height: 26px !important;
				padding: 0 !important;
				margin: 0 !important;
				border: none !important;
				border-radius: 4px !important;
				background: transparent !important;
				color: #475569 !important;
				font-size: 14px !important;
				font-weight: 600 !important;
				line-height: 1 !important;
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				cursor: pointer !important;
				box-shadow: none !important;
			}

			/* 9. Protect SVG Icons from Theme Auto-Expansion */
			:is(html body, #page, .entry-content, .site-main) :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper, .omnify-mini-cart) svg {
				max-width: none !important;
				vertical-align: middle !important;
			}

			/* 10. Neutralize Theme Page Titles, Headers & Sidebars on Dedicated Omnify Pages */
			body:is(.omnify-checkout-active, .omnify-cart-active, .omnify-storefront-active, .omnify-portal-active, .omnify-checkout-page, .omnify-cart-page, .omnify-storefront-page, .omnify-portal-page) :is(.entry-title, .page-title, .wp-block-post-title, .tutor-page-title, .wp-block-post-featured-image) {
				display: none !important;
			}

			body:is(.omnify-checkout-active, .omnify-cart-active, .omnify-storefront-active, .omnify-portal-active, .omnify-checkout-page, .omnify-cart-page, .omnify-storefront-page, .omnify-portal-page) :is(#secondary, .sidebar, .widget-area, aside.widget-area) {
				display: none !important;
			}

			body:is(.omnify-checkout-active, .omnify-cart-active, .omnify-storefront-active, .omnify-portal-active, .omnify-checkout-page, .omnify-cart-page, .omnify-storefront-page, .omnify-portal-page) :is(#primary, .content-area, .site-main, .entry-content, main, article) {
				width: 100% !important;
				max-width: 100% !important;
				float: none !important;
				padding-left: 0 !important;
				padding-right: 0 !important;
				margin-left: 0 !important;
				margin-right: 0 !important;
			}

			/* 11. Eliminate Theme Container Margin Stacking & Provide Clean Top Gap */
			:is(.entry-content, .site-main, .content-area, .post-inner) > :is(.omnify-storefront-wrapper, .omnify-checkout-wrapper, .omnify-cart-page, .omnify-pdp-wrapper, .omnify-portal-wrapper) {
				margin-top: 36px !important;
				margin-bottom: 40px !important;
			}
		";
	}

	public function add_storefront_body_classes(array $omnify_classes): array {
		if (is_admin()) {
			return $omnify_classes;
		}

		$post = get_post();
		if (! $post) {
			return $omnify_classes;
		}

		$omnify_content = (string) $post->post_content;
		$omnify_has_any = false;
		$omnify_context = '';

		if (has_shortcode($omnify_content, 'omnify_storefront')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-storefront-page';
			$omnify_has_any = true;
			$omnify_context = 'storefront';
		}
		if (has_shortcode($omnify_content, 'omnify_cart')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-cart-page';
			$omnify_has_any = true;
			$omnify_context = 'cart';
		}
		if (has_shortcode($omnify_content, 'omnify_customer_portal') || has_shortcode($omnify_content, 'omnify_customer_downloads')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-portal-page';
			$omnify_has_any = true;
			$omnify_context = 'portal';
		}
		if (has_shortcode($omnify_content, 'omnify_checkout')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-checkout-page';
			$omnify_has_any = true;
			$omnify_context = 'checkout';
		}

		if ($omnify_has_any) {
			return apply_filters('omnify_storefront_body_classes', $omnify_classes, $omnify_context);
		}

		return $omnify_classes;
	}

	public function maybe_disable_core_canonical(): void {
		if ($this->current_seo_product()) {
			remove_action('wp_head', 'rel_canonical');
		}
	}

	public function filter_document_title(string $omnify_title): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['title'] : $omnify_title;
	}

	public function filter_document_title_parts(array $omnify_parts): array {
		$omnify_payload = $this->current_product_seo_payload();
		if (! $omnify_payload) {
			return $omnify_parts;
		}

		$omnify_parts['title'] = $omnify_payload['name'];

		return $omnify_parts;
	}

	public function filter_seo_title(string $omnify_title): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['title'] : $omnify_title;
	}

	public function filter_seo_description(string $omnify_description): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['description'] : $omnify_description;
	}

	public function filter_seo_canonical(string $omnify_canonical): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['url'] : $omnify_canonical;
	}

	public function filter_seo_image(string $omnify_image): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload && ! empty($omnify_payload['image']) ? $omnify_payload['image'] : $omnify_image;
	}

	public function render_product_seo_tags(): void {
		$omnify_payload = $this->current_product_seo_payload();
		if (! $omnify_payload) {
			return;
		}

		echo "\n" . '<!-- Omnify product SEO -->' . "\n";
		if (! $this->external_seo_plugin_active()) {
			printf('<link rel="canonical" href="%s" />' . "\n", esc_url($omnify_payload['url']));
			printf('<meta name="description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			printf('<meta property="og:type" content="product" />' . "\n");
			printf('<meta property="og:title" content="%s" />' . "\n", esc_attr($omnify_payload['title']));
			printf('<meta property="og:description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			printf('<meta property="og:url" content="%s" />' . "\n", esc_url($omnify_payload['url']));
			printf('<meta property="og:site_name" content="%s" />' . "\n", esc_attr(get_bloginfo('name')));
			printf('<meta property="product:price:amount" content="%s" />' . "\n", esc_attr(number_format((float) $omnify_payload['price'], 2, '.', '')));
			printf('<meta property="product:price:currency" content="%s" />' . "\n", esc_attr($omnify_payload['currency']));
			printf('<meta name="twitter:card" content="%s" />' . "\n", $omnify_payload['image'] ? 'summary_large_image' : 'summary');
			printf('<meta name="twitter:title" content="%s" />' . "\n", esc_attr($omnify_payload['title']));
			printf('<meta name="twitter:description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			if ($omnify_payload['image']) {
				printf('<meta property="og:image" content="%s" />' . "\n", esc_url($omnify_payload['image']));
			}
		}
		printf('<script type="application/ld+json">%s</script>' . "\n", wp_json_encode($omnify_payload['schema'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE));
		echo '<!-- /Omnify product SEO -->' . "\n";
	}

	public function register_product_sitemap(): void {
		if (! function_exists('wp_sitemaps_register_provider') || ! class_exists('\WP_Sitemaps_Provider')) {
			return;
		}

		$omnify_controller = $this;
		$omnify_provider = new class($omnify_controller) extends \WP_Sitemaps_Provider {
			public function __construct(private Omnify_Storefront_Controller $omnify_controller) {
				$this->name        = 'omnify-products';
				$this->object_type = 'omnify-products';
			}

			public function get_url_list($omnify_page_num, $omnify_object_subtype = '') {
				return $this->omnify_controller->product_sitemap_urls((int) $omnify_page_num);
			}

			public function get_max_num_pages($omnify_object_subtype = '') {
				return $this->omnify_controller->product_sitemap_page_count();
			}
		};

		wp_sitemaps_register_provider('omnify-products', $omnify_provider);
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	public function product_sitemap_urls(int $omnify_page_num): array {
		$omnify_page_num = max(1, $omnify_page_num);
		$omnify_products = $this->omnify_products->all([
			'status'   => 'published',
			'per_page' => 100,
			'offset'   => ($omnify_page_num - 1) * 100,
		]);

		$omnify_urls = [];
		foreach ($omnify_products as $omnify_product) {
			$omnify_urls[] = [
				'loc'     => $this->product_url($omnify_product),
				'lastmod' => ! empty($omnify_product['updated_at']) ? mysql2date('c', (string) $omnify_product['updated_at'], false) : current_time('c'),
			];
		}

		return $omnify_urls;
	}

	public function product_sitemap_page_count(): int {
		global $wpdb;

		$omnify_table = $wpdb->prefix . 'omnify_products';
		if (\Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) !== $omnify_table) {
			return 0;
		}

		$omnify_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SELECT COUNT(*) FROM %i WHERE status = %s', $omnify_table, 'published'));

		return max(1, (int) ceil($omnify_count / 100));
	}

	private function current_seo_product(): ?array {
		if (is_admin() || ! is_singular()) {
			return null;
		}

		$post = get_post();
		if (! $post || ! has_shortcode((string) $post->post_content, 'omnify_storefront')) {
			return null;
		}

		$omnify_slug = isset($_GET['omnify_product']) ? sanitize_title(wp_unslash((string) $_GET['omnify_product'])) : '';
		if ('' === $omnify_slug) {
			return null;
		}

		$omnify_product = $this->omnify_products->find_by_slug($omnify_slug);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '') || ! empty($omnify_product['deleted_at'])) {
			return null;
		}

		return $omnify_product;
	}

	private function current_product_seo_payload(): ?array {
		static $omnify_payload = null;
		static $omnify_resolved = false;

		if ($omnify_resolved) {
			return $omnify_payload;
		}
		$omnify_resolved = true;

		$omnify_product = $this->current_seo_product();
		if (! $omnify_product) {
			return null;
		}

		$omnify_payload = $this->seo_payload_for_product($omnify_product);

		return $omnify_payload;
	}

	private function seo_payload_for_product(array $omnify_product): array {
		$omnify_name        = wp_strip_all_tags((string) ($omnify_product['name'] ?? __('Product', 'omnifywp-ecommerce')));
		$omnify_settings    = get_option('omnify_settings', []);
		$omnify_store_name  = (string) ($omnify_settings['store_name'] ?? get_bloginfo('name'));
		$omnify_title       = sprintf('%s | %s', $omnify_name, $omnify_store_name ?: get_bloginfo('name'));
		$omnify_description = $this->product_seo_description($omnify_product);
		$omnify_url         = $this->product_url($omnify_product);
		$omnify_image       = $this->product_seo_image($omnify_product);
		$omnify_price       = $this->product_seo_price($omnify_product);
		$omnify_currency    = strtoupper((string) ($omnify_product['currency'] ?? $omnify_settings['default_currency'] ?? 'USD'));
		$omnify_review_count = $this->omnify_reviews->get_total_count((int) $omnify_product['id']);
		$omnify_rating       = $omnify_review_count > 0 ? round($this->omnify_reviews->get_average_rating((int) $omnify_product['id']), 1) : 0.0;

		$omnify_schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $omnify_name,
			'description' => $omnify_description,
			'url'         => $omnify_url,
			'sku'         => (string) ($omnify_product['sku'] ?? ''),
			'brand'       => [
				'@type' => 'Brand',
				'name'  => $omnify_store_name ?: get_bloginfo('name'),
			],
			'offers'      => [
				'@type'         => 'Offer',
				'url'           => $omnify_url,
				'priceCurrency' => $omnify_currency,
				'price'         => number_format($omnify_price, 2, '.', ''),
				'availability'  => $this->product_schema_availability($omnify_product),
				'itemCondition' => 'https://schema.org/NewCondition',
			],
		];

		if ($omnify_image) {
			$omnify_schema['image'] = [$omnify_image];
		}
		if (! empty($omnify_product['categories'])) {
			$omnify_schema['category'] = implode(', ', array_map('strval', (array) $omnify_product['categories']));
		}
		if ($omnify_review_count > 0 && $omnify_rating > 0) {
			$omnify_schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => $omnify_rating,
				'reviewCount' => $omnify_review_count,
			];
		}

		return [
			'name'        => $omnify_name,
			'title'       => $omnify_title,
			'description' => $omnify_description,
			'url'         => $omnify_url,
			'image'       => $omnify_image,
			'price'       => $omnify_price,
			'currency'    => $omnify_currency,
			'schema'      => array_filter($omnify_schema, static fn($omnify_value) => '' !== $omnify_value && null !== $omnify_value && [] !== $omnify_value),
		];
	}

	private function product_seo_description(array $omnify_product): string {
		$omnify_description = trim(wp_strip_all_tags((string) ($omnify_product['description'] ?? '')));
		if ('' === $omnify_description && ! empty($omnify_product['trust_badge_text'])) {
			$omnify_description = trim(wp_strip_all_tags((string) $omnify_product['trust_badge_text']));
		}
		if ('' === $omnify_description) {
			// translators: 1: product name, 2: site name
			$omnify_description = sprintf(__('Buy %1$s from %2$s.', 'omnifywp-ecommerce'), (string) ($omnify_product['name'] ?? __('this product', 'omnifywp-ecommerce')), get_bloginfo('name'));
		}

		return wp_html_excerpt($omnify_description, 155, '...');
	}

	private function product_seo_image(array $omnify_product): string {
		if (! empty($omnify_product['thumbnail_id'])) {
			$omnify_image = wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'full');
			if ($omnify_image) {
				return $omnify_image;
			}
		}
		if (! empty($omnify_product['gallery_urls']) && is_array($omnify_product['gallery_urls'])) {
			return (string) reset($omnify_product['gallery_urls']);
		}
		if (! empty($omnify_product['thumbnail_url'])) {
			return (string) $omnify_product['thumbnail_url'];
		}

		return '';
	}

	private function product_seo_price(array $omnify_product): float {
		$omnify_price = null !== ($omnify_product['sale_price'] ?? null) ? (float) $omnify_product['sale_price'] : (float) ($omnify_product['price'] ?? 0);
		if ('variable' === ($omnify_product['type'] ?? '') && ! empty($omnify_product['variations']) && is_array($omnify_product['variations'])) {
			foreach ($omnify_product['variations'] as $omnify_variation) {
				$omnify_variation_price = null !== ($omnify_variation['sale_price'] ?? null) ? (float) $omnify_variation['sale_price'] : (float) ($omnify_variation['price'] ?? 0);
				if ($omnify_variation_price > 0 && (0.0 === $omnify_price || $omnify_variation_price < $omnify_price)) {
					$omnify_price = $omnify_variation_price;
				}
			}
		}

		return max(0.0, $omnify_price);
	}

	private function product_schema_availability(array $omnify_product): string {
		if (! empty($omnify_product['preorder_enabled'])) {
			return 'https://schema.org/PreOrder';
		}
		if (! empty($omnify_product['allow_backorders']) || 'onbackorder' === ($omnify_product['stock_status'] ?? '')) {
			return 'https://schema.org/BackOrder';
		}
		if ('outofstock' === ($omnify_product['stock_status'] ?? '')) {
			return 'https://schema.org/OutOfStock';
		}
		if (! empty($omnify_product['manage_stock']) && null !== ($omnify_product['stock_qty'] ?? null) && (int) $omnify_product['stock_qty'] <= 0) {
			return 'https://schema.org/OutOfStock';
		}

		return 'https://schema.org/InStock';
	}

	private function external_seo_plugin_active(): bool {
		return defined('WPSEO_VERSION')
			|| defined('RANK_MATH_VERSION')
			|| class_exists('RankMath')
			|| class_exists('WPSEO_Frontend')
			|| class_exists('AIOSEO\Plugin\Common\Main\Main');
	}

	private function hex2rgba(string $omnify_hex, float $omnify_opacity): string {
		$omnify_hex = str_replace('#', '', $omnify_hex);
		if (strlen($omnify_hex) === 3) {
			$omnify_r = hexdec(substr($omnify_hex, 0, 1) . substr($omnify_hex, 0, 1));
			$omnify_g = hexdec(substr($omnify_hex, 1, 1) . substr($omnify_hex, 1, 1));
			$omnify_b = hexdec(substr($omnify_hex, 2, 1) . substr($omnify_hex, 2, 1));
		} else {
			$omnify_r = hexdec(substr($omnify_hex, 0, 2) ?: '0');
			$omnify_g = hexdec(substr($omnify_hex, 2, 2) ?: '0');
			$omnify_b = hexdec(substr($omnify_hex, 4, 2) ?: '0');
		}
		return "rgba({$omnify_r}, {$omnify_g}, {$omnify_b}, {$omnify_opacity})";
	}

	private function product_url(array $omnify_product): string {
		return add_query_arg('omnify_product', sanitize_title((string) ($omnify_product['slug'] ?? '')), $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/'));
	}

	/**
	 * Build the inline design-token CSS (colors, radius, font) for the active design settings.
	 * Used by both the storefront and the block editor previews so every block matches the
	 * store's default design.
	 *
	 * @param array<string, mixed> $omnify_settings Store settings.
	 */
	private function get_design_inline_css(array $omnify_settings): string {
		$omnify_saved_primary = $omnify_settings['design_primary_color'] ?? '';
		$omnify_primary_color = (! empty($omnify_saved_primary) && '#6366f1' !== $omnify_saved_primary)
			? (sanitize_hex_color($omnify_saved_primary) ?: '#0f766e')
			: '#0f766e';
		$omnify_saved_hover = $omnify_settings['design_hover_color'] ?? '';
		$omnify_hover_color = (! empty($omnify_saved_hover) && '#4f46e5' !== $omnify_saved_hover)
			? (sanitize_hex_color($omnify_saved_hover) ?: '#115e59')
			: '#115e59';
		$omnify_radius   = intval($omnify_settings['design_border_radius'] ?? 8) . 'px';
		$omnify_raw_font = (string) ($omnify_settings['design_font_family'] ?? '');
		if (empty($omnify_raw_font) || in_array($omnify_raw_font, ["'Inter', system-ui, -apple-system, sans-serif", 'Inter, system-ui, -apple-system, sans-serif'], true)) {
			$omnify_font = "'Poppins', 'Montserrat', system-ui, -apple-system, sans-serif";
		} else {
			$omnify_font = (string) preg_replace('/[^a-zA-Z0-9\s\'\",\-]/', '', $omnify_raw_font);
		}

		$omnify_css_vars = [
			'--omnify-primary'       => $omnify_primary_color,
			'--omnify-primary-hover' => $omnify_hover_color,
			'--omnify-primary-glow'  => $this->hex2rgba($omnify_primary_color, 0.15),
			'--omnify-radius'        => $omnify_radius,
			'--omnify-font-family'   => $omnify_font,
		];
		$omnify_css_vars = apply_filters('omnify_storefront_css_variables', $omnify_css_vars);

		$omnify_css_vars_str = '';
		foreach ($omnify_css_vars as $omnify_var_name => $omnify_var_val) {
			$omnify_css_vars_str .= "\t\t\t\t{$omnify_var_name}: {$omnify_var_val} !important;\n";
		}

		$omnify_wrappers = implode(
			",\n\t\t\t",
			[
				'.omnify-storefront-wrapper',
				'.omnify-checkout-wrapper',
				'.omnify-cart-page',
				'.omnify-portal-wrapper',
				'.omnify-order-tracking-wrapper',
				'.omnify-mini-cart',
				'.omnify-block-product-card',
				'.omnify-currency-switcher-wrap',
				'.omnify-block-container',
			]
		);

		$omnify_inline_css = "
			:root {
{$omnify_css_vars_str}			}
			{$omnify_wrappers} {
				font-family: {$omnify_font} !important;
			}
			{$omnify_wrappers} :where(h1, h2, h3, h4, h5, h6) {
				font-family: 'Montserrat', 'Poppins', system-ui, -apple-system, sans-serif !important;
			}
			{$omnify_wrappers} :where(input, select, textarea, button, .omnify-btn, .omnify-btn-add-to-cart) {
				font-family: {$omnify_font} !important;
				border-radius: {$omnify_radius} !important;
			}
			.omnify-block-product-card,
			.omnify-order-tracking-wrapper .omnify-portal-section-card,
			.omnify-product-card,
			.omnify-card,
			.omnify-portal-box,
			.omnify-order-box {
				border-radius: {$omnify_radius} !important;
			}
			.omnify-product-card__image-container,
			.omnify-product-card__image,
			.omnify-block-product-card > a {
				border-top-left-radius: {$omnify_radius} !important;
				border-top-right-radius: {$omnify_radius} !important;
				overflow: hidden;
			}
		";

		$omnify_inline_css = (string) apply_filters('omnify_storefront_inline_css', $omnify_inline_css);
		return wp_strip_all_tags($omnify_inline_css);
	}

	public function enqueue_assets(): void {
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];
		wp_enqueue_style('dashicons');

		// Enqueue Google Fonts
		wp_enqueue_style(
			'omnify-storefront-fonts',
			'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300..900;1,300..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700&display=swap',
			[],
			null
		);

		// Enqueue Stylesheet
		$omnify_css_ver = file_exists(OMNIFY_PATH . 'assets/storefront/storefront.css') ? (string) filemtime(OMNIFY_PATH . 'assets/storefront/storefront.css') : OMNIFY_VERSION;
		wp_enqueue_style(
			'omnify-storefront-css',
			OMNIFY_URL . 'assets/storefront/storefront.css',
			['omnify-storefront-fonts'],
			$omnify_css_ver
		);

		$omnify_inline_css = $this->get_design_inline_css($omnify_settings);
		wp_add_inline_style('omnify-storefront-css', $omnify_inline_css);

		// Enqueue Script
		$omnify_js_ver = file_exists(OMNIFY_PATH . 'assets/storefront/storefront.js') ? (string) filemtime(OMNIFY_PATH . 'assets/storefront/storefront.js') : OMNIFY_VERSION;
		wp_enqueue_script(
			'omnify-storefront-js',
			OMNIFY_URL . 'assets/storefront/storefront.js',
			[],
			$omnify_js_ver,
			true
		);

		$omnify_settings = get_option('omnify_settings', []);
		if (! empty($omnify_settings['razorpay_enabled']) || ! empty($omnify_settings['razorpay_checkout_enabled'])) {
			wp_enqueue_script(
				'omnify-razorpay-checkout',
				'https://checkout.razorpay.com/v1/checkout.js',
				[],
				OMNIFY_VERSION,
				true
			);
		}

		if (! empty($omnify_settings['paddle_enabled']) || ! empty($omnify_settings['paddle_checkout_enabled'])) {
			wp_enqueue_script(
				'omnify-paddle-checkout',
				'https://cdn.paddle.com/paddle/v2/paddle.js',
				[],
				OMNIFY_VERSION,
				true
			);
		}

		$omnify_tax_rate = (float) ($omnify_settings['tax_rate'] ?? 0.0);
		$omnify_currency = strtoupper((string) ($omnify_settings['default_currency'] ?? 'USD'));
		$omnify_stripe_mode = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';

		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_cart_url     = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');

		// Resolve free shipping minimum threshold across delivery zones or global settings
		$omnify_free_shipping_min = null;
		if (! empty($omnify_settings['delivery_zones']) && is_array($omnify_settings['delivery_zones'])) {
			foreach ($omnify_settings['delivery_zones'] as $omnify_zone) {
				if (! empty($omnify_zone['enabled']) && isset($omnify_zone['free_min']) && '' !== (string) $omnify_zone['free_min']) {
					$omnify_val = (float) $omnify_zone['free_min'];
					if ($omnify_val > 0 && (null === $omnify_free_shipping_min || $omnify_val < $omnify_free_shipping_min)) {
						$omnify_free_shipping_min = $omnify_val;
					}
				}
			}
		}
		if (null === $omnify_free_shipping_min && isset($omnify_settings['free_shipping_threshold']) && '' !== (string) $omnify_settings['free_shipping_threshold']) {
			$omnify_val = (float) $omnify_settings['free_shipping_threshold'];
			if ($omnify_val > 0) {
				$omnify_free_shipping_min = $omnify_val;
			}
		}

		// Localize Script
		wp_localize_script(
			'omnify-storefront-js',
			'omnifyStorefront',
			[
				'restUrl'     => untrailingslashit(esc_url_raw(rest_url('omnify/v1'))),
				'nonce'       => wp_create_nonce('wp_rest'),
				'taxRate'     => $omnify_tax_rate,
				'taxLabel'    => sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax')),
				'taxRules'    => is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [],
				'pricesIncludeTax' => ! empty($omnify_settings['prices_include_tax']),
				'taxShipping' => ! empty($omnify_settings['tax_shipping']),
				'taxRounding' => sanitize_key((string) ($omnify_settings['tax_rounding'] ?? 'line')),
				'taxReportingEnabled' => ! empty($omnify_settings['tax_reporting_enabled']),
				'deliveryZones' => is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [],
				'currency'        => $this->get_active_currency(),
				'baseCurrency'    => $omnify_currency,
				'currencySymbol'  => $this->currency_symbol($this->get_active_currency()),
				'currencyPosition' => $omnify_settings['currency_position'] ?? 'before',
				'priceDecimals'     => Omnify_Locations::currency_decimals($this->get_active_currency()),
				'thousandSeparator' => (string) ($omnify_settings['thousand_separator'] ?? ','),
				'decimalSeparator'  => (string) ($omnify_settings['decimal_separator'] ?? '.'),
				'freeShippingThreshold' => (float) ($omnify_free_shipping_min ?? 50.0),
				'countries'   => Omnify_Locations::allowed_countries($omnify_settings),
				'states'      => Omnify_Locations::states(),
				'cities'      => Omnify_Locations::cities(),
				'postcodes'   => Omnify_Locations::postcodes(),
				'checkoutUrl' => esc_url_raw($omnify_checkout_url),
				'cartUrl'     => esc_url_raw($omnify_cart_url),
				'isLoggedIn'  => is_user_logged_in(),
				'wishlistProductIds' => is_user_logged_in() 
					? $this->omnify_wishlists->product_ids_for_user(get_current_user_id()) 
					: array_values(array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash((string) ($_COOKIE['omnify_wishlist_ids'] ?? ''))))))),
				'stripe'      => [
					'enabled'               => (! empty($omnify_settings['stripe_enabled']) || ! empty($omnify_settings['stripe_checkout_enabled'])),
					'mode'                  => $omnify_stripe_mode,
					'publishableKey'        => sanitize_text_field((string) ($omnify_settings["stripe_{$omnify_stripe_mode}_publishable_key"] ?? '')),
					'checkoutEnabled'       => (! empty($omnify_settings['stripe_checkout_enabled']) || ! empty($omnify_settings['stripe_enabled'])),
					'paymentIntentsEnabled' => ! empty($omnify_settings['stripe_payment_intents_enabled']),
					'applePayEnabled'       => ! empty($omnify_settings['stripe_apple_pay_enabled']),
					'googlePayEnabled'      => ! empty($omnify_settings['stripe_google_pay_enabled']),
				],
				'paypal'      => [
					'enabled'         => (! empty($omnify_settings['paypal_enabled']) || ! empty($omnify_settings['paypal_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => (! empty($omnify_settings['paypal_checkout_enabled']) || ! empty($omnify_settings['paypal_enabled'])),
				],
				'razorpay'    => [
					'enabled'         => (! empty($omnify_settings['razorpay_enabled']) || ! empty($omnify_settings['razorpay_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test',
					'keyId'           => sanitize_text_field((string) ($omnify_settings[ 'razorpay_' . ('live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test') . '_key_id' ] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['razorpay_checkout_enabled']) || ! empty($omnify_settings['razorpay_enabled'])),
				],
				'alipay'      => [
					'enabled'         => (! empty($omnify_settings['alipay_enabled']) || ! empty($omnify_settings['alipay_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['alipay_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => (! empty($omnify_settings['alipay_checkout_enabled']) || ! empty($omnify_settings['alipay_enabled'])),
				],
				'wechat'      => [
					'enabled'         => (! empty($omnify_settings['wechat_enabled']) || ! empty($omnify_settings['wechat_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['wechat_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => (! empty($omnify_settings['wechat_checkout_enabled']) || ! empty($omnify_settings['wechat_enabled'])),
				],
				'sslcommerz'  => [
					'enabled'         => (! empty($omnify_settings['sslcommerz_enabled']) || ! empty($omnify_settings['sslcommerz_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => (! empty($omnify_settings['sslcommerz_checkout_enabled']) || ! empty($omnify_settings['sslcommerz_enabled'])),
				],
				'paystack'    => [
					'enabled'         => (! empty($omnify_settings['paystack_enabled']) || ! empty($omnify_settings['paystack_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['paystack_' . ('live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test') . '_public_key'] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['paystack_checkout_enabled']) || ! empty($omnify_settings['paystack_enabled'])),
				],
				'tap'         => [
					'enabled'         => (! empty($omnify_settings['tap_enabled']) || ! empty($omnify_settings['tap_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['tap_' . ('live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test') . '_publishable_key'] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['tap_checkout_enabled']) || ! empty($omnify_settings['tap_enabled'])),
				],
				'mollie'      => [
					'enabled'         => (! empty($omnify_settings['mollie_enabled']) || ! empty($omnify_settings['mollie_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test',
					'checkoutEnabled' => (! empty($omnify_settings['mollie_checkout_enabled']) || ! empty($omnify_settings['mollie_enabled'])),
				],
				'khalti'      => [
					'enabled'         => (! empty($omnify_settings['khalti_enabled']) || ! empty($omnify_settings['khalti_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['khalti_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['khalti_' . ('live' === ($omnify_settings['khalti_mode'] ?? 'test') ? 'live' : 'test') . '_public_key'] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['khalti_checkout_enabled']) || ! empty($omnify_settings['khalti_enabled'])),
				],
				'esewa'       => [
					'enabled'         => (! empty($omnify_settings['esewa_enabled']) || ! empty($omnify_settings['esewa_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test',
					'productCode'     => sanitize_text_field((string) ($omnify_settings['esewa_' . ('live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test') . '_product_code'] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['esewa_checkout_enabled']) || ! empty($omnify_settings['esewa_enabled'])),
				],
				'paddle'      => [
					'enabled'         => (! empty($omnify_settings['paddle_enabled']) || ! empty($omnify_settings['paddle_checkout_enabled'])),
					'mode'            => 'live' === ($omnify_settings['paddle_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'clientToken'     => sanitize_text_field((string) ($omnify_settings['paddle_' . ('live' === ($omnify_settings['paddle_mode'] ?? 'sandbox') ? 'live' : 'sandbox') . '_client_token'] ?? '')),
					'checkoutEnabled' => (! empty($omnify_settings['paddle_checkout_enabled']) || ! empty($omnify_settings['paddle_enabled'])),
				],
				'tracking'    => [
					'ga4Enabled'  => ! empty($omnify_settings['tracking_ga4_enabled']),
					'metaEnabled' => ! empty($omnify_settings['tracking_meta_enabled']),
					'currency'    => $omnify_currency,
				],
				'cart' => [
					'countdownEnabled'     => ! empty($omnify_settings['cart_countdown_enabled']),
					'countdownDuration'    => intval($omnify_settings['cart_countdown_duration'] ?? 7),
					'freeShippingEnabled'  => ! empty($omnify_settings['cart_free_shipping_enabled']),
					'freeShippingThreshold'=> floatval($omnify_settings['cart_free_shipping_threshold'] ?? 200.0),
				],
				'orderBump' => [
					'enabled' => ! array_key_exists('order_bump_enabled', $omnify_settings) || ! empty($omnify_settings['order_bump_enabled']),
					'title'   => sanitize_text_field((string) ($omnify_settings['order_bump_title'] ?? 'VIP Priority Processing & Extended Warranty')),
					'desc'    => sanitize_textarea_field((string) ($omnify_settings['order_bump_desc'] ?? 'Skip the fulfillment queue with front-of-line priority dispatch plus 1-year accidental replacement guarantee.')),
					'price'   => max(0.0, (float) ($omnify_settings['order_bump_price'] ?? 4.99)),
				],
				'orderBumpPrice' => max(0.0, (float) ($omnify_settings['order_bump_price'] ?? 4.99)),
				'minOrderAmount' => max(0.0, (float) ($omnify_settings['min_order_amount'] ?? 0.0)),
			]
		);
	}

	private function page_url_for_shortcode(string $omnify_shortcode, string $omnify_fallback_path): string {
		if ('[omnify_cart]' === $omnify_shortcode && function_exists('omnify_get_cart_url')) {
			return omnify_get_cart_url();
		}
		if ('[omnify_checkout]' === $omnify_shortcode && function_exists('omnify_get_checkout_url')) {
			return omnify_get_checkout_url();
		}
		if ('[omnify_storefront]' === $omnify_shortcode && function_exists('omnify_get_storefront_url')) {
			return omnify_get_storefront_url();
		}

		$omnify_pages = get_posts([
			'post_type'   => 'page',
			's'           => $omnify_shortcode,
			'post_status' => 'publish',
		]);

		if (! empty($omnify_pages)) {
			return get_permalink($omnify_pages[0]->ID);
		}

		return home_url($omnify_fallback_path);
	}

	public function supported_currencies(): array {
		return [
			'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR',
			'BDT', 'BRL', 'RUB', 'KRW', 'SGD', 'NZD', 'MXN', 'HKD', 'SEK',
			'NOK', 'TRY', 'ZAR', 'AED', 'SAR'
		];
	}

	public function get_active_currency(): string {
		$omnify_settings = $this->omnify_settings instanceof Omnify_Settings_Repository
			? $this->omnify_settings->all()
			: (array) get_option('omnify_settings', []);
		$omnify_default  = strtoupper((string) ($omnify_settings['default_currency'] ?? 'USD'));

		if (! empty($_GET['currency'])) {
			$omnify_candidate = strtoupper(sanitize_text_field(wp_unslash($_GET['currency'])));
			if (in_array($omnify_candidate, $this->supported_currencies(), true)) {
				return $omnify_candidate;
			}
		}

		if (! empty($_COOKIE['omnify_currency'])) {
			$omnify_candidate = strtoupper(sanitize_text_field(wp_unslash($_COOKIE['omnify_currency'])));
			if (in_array($omnify_candidate, $this->supported_currencies(), true)) {
				return $omnify_candidate;
			}
		}

		return $omnify_default;
	}

	public function render_currency_switcher(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();
		$omnify_active    = $this->get_active_currency();
		$omnify_supported = $this->supported_currencies();
		$omnify_atts      = is_array($omnify_attributes) ? $omnify_attributes : [];
		$omnify_label     = isset($omnify_atts['label']) && '' !== trim((string) $omnify_atts['label'])
			? sanitize_text_field((string) $omnify_atts['label'])
			: __('Currency:', 'omnifywp-ecommerce');
		$omnify_show_label  = ! isset($omnify_atts['showLabel']) || ! empty($omnify_atts['showLabel']);
		$omnify_show_symbol = ! isset($omnify_atts['showSymbol']) || ! empty($omnify_atts['showSymbol']);
		$omnify_align       = sanitize_key((string) ($omnify_atts['align'] ?? ''));
		$omnify_align_map   = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
		$omnify_wrap_style  = 'display:flex; align-items:center; gap:6px; font-size:13px;';
		if (isset($omnify_align_map[$omnify_align])) {
			$omnify_wrap_style .= 'justify-content:' . $omnify_align_map[$omnify_align] . ';';
		}
		if (! empty($omnify_atts['currencies'])) {
			$omnify_requested = array_filter(array_map(
				static fn($omnify_cur): string => strtoupper(trim((string) $omnify_cur)),
				explode(',', (string) $omnify_atts['currencies'])
			));
			$omnify_filtered = array_values(array_intersect($omnify_supported, $omnify_requested));
			if (! empty($omnify_filtered)) {
				$omnify_supported = $omnify_filtered;
			}
		}

		ob_start();
		?>
		<div class="omnify-currency-switcher-wrap" style="<?php echo esc_attr($omnify_wrap_style); ?>">
			<?php if ($omnify_show_label) : ?>
				<label for="omnify-currency-select" style="font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:4px;">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
					<?php echo esc_html($omnify_label); ?>
				</label>
			<?php endif; ?>
			<select id="omnify-currency-select" class="omnify-currency-select" aria-label="<?php echo esc_attr($omnify_label); ?>" onchange="document.cookie='omnify_currency='+this.value+';path=/;max-age=31536000'; window.location.reload();" style="padding:4px 8px; border-radius: var(--omnify-radius, 6px); border:1px solid #cbd5e1; background:#fff; font-size:13px; font-weight:500; cursor:pointer;">
				<?php foreach ($omnify_supported as $omnify_c) : ?>
					<option value="<?php echo esc_attr($omnify_c); ?>" <?php selected($omnify_active, $omnify_c); ?>>
						<?php echo esc_html($omnify_show_symbol ? ($omnify_c . ' (' . $this->currency_symbol($omnify_c) . ')') : $omnify_c); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Retrieve dynamically resolved trust badges based on active payment gateways and security settings.
	 *
	 * @param array<string, mixed> $omnify_settings Optional settings array override.
	 * @return array<int, array<string, string>>
	 */
	public function get_trust_badges(array $omnify_settings = []): array {
		if (empty($omnify_settings)) {
			$omnify_settings = $this->omnify_settings instanceof Omnify_Settings_Repository
				? $this->omnify_settings->all()
				: (array) get_option('omnify_settings', []);
		}

		$omnify_badges = [];
		$omnify_currency = strtoupper((string) ($omnify_settings['currency'] ?? ($omnify_settings['default_currency'] ?? 'USD')));

		// 1. Stripe / Cards
		$stripe_enabled = ! empty($omnify_settings['stripe_enabled']);
		if ($stripe_enabled) {
			$omnify_badges[] = [
				'id'     => 'card',
				'label'  => 'VISA',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#E2E8F0',
			];
			$omnify_badges[] = [
				'id'     => 'card',
				'label'  => 'Mastercard',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#E2E8F0',
			];
			$omnify_badges[] = [
				'id'     => 'card',
				'label'  => 'AMEX',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#E2E8F0',
			];
			if (! empty($omnify_settings['stripe_apple_pay_enabled'])) {
				$omnify_badges[] = [
					'id'     => 'card',
					'sub'    => 'apple_pay',
					'label'  => 'Apple Pay',
					'color'  => '#0F172A',
					'bg'     => '#F1F5F9',
					'border' => '#E2E8F0',
				];
			}
			if (! empty($omnify_settings['stripe_google_pay_enabled'])) {
				$omnify_badges[] = [
					'id'     => 'card',
					'sub'    => 'google_pay',
					'label'  => 'Google Pay',
					'color'  => '#0F172A',
					'bg'     => '#F1F5F9',
					'border' => '#E2E8F0',
				];
			}
		}

		// 2. PayPal
		if (! empty($omnify_settings['paypal_enabled'])) {
			$omnify_badges[] = [
				'id'     => 'paypal',
				'label'  => 'PayPal',
				'color'  => '#0284C7',
				'bg'     => '#F0F9FF',
				'border' => '#BAE6FD',
			];
		}

		// 3. Razorpay
		if (! empty($omnify_settings['razorpay_enabled'])) {
			$omnify_badges[] = [
				'id'     => 'razorpay',
				'label'  => 'Razorpay',
				'color'  => '#0C4A6E',
				'bg'     => '#E0F2FE',
				'border' => '#7DD3FC',
			];
			$omnify_badges[] = [
				'id'     => 'razorpay',
				'sub'    => 'upi',
				'label'  => 'UPI',
				'color'  => '#065F46',
				'bg'     => '#ECFDF5',
				'border' => '#A7F3D0',
			];
		}

		// 4. Paystack
		$paystack_compatible = in_array($omnify_currency, ['NGN', 'GHS', 'ZAR', 'KES', 'USD'], true);
		if (! empty($omnify_settings['paystack_enabled']) && $paystack_compatible) {
			$omnify_badges[] = [
				'id'     => 'paystack',
				'label'  => 'Paystack',
				'color'  => '#065F46',
				'bg'     => '#ECFDF5',
				'border' => '#A7F3D0',
			];
		}

		// 5. Tap Payments
		$tap_compatible = in_array($omnify_currency, ['AED', 'SAR', 'KWD', 'BHD', 'OMR', 'QAR', 'EGP', 'JOD', 'USD', 'EUR', 'GBP'], true);
		if (! empty($omnify_settings['tap_enabled']) && $tap_compatible) {
			$omnify_badges[] = [
				'id'     => 'tap',
				'label'  => 'Tap Payments',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#CBD5E1',
			];
		}

		// 6. Mollie
		$mollie_compatible = in_array($omnify_currency, ['EUR', 'GBP', 'CHF', 'PLN', 'SEK', 'NOK', 'DKK', 'USD'], true);
		if (! empty($omnify_settings['mollie_enabled']) && $mollie_compatible) {
			$omnify_badges[] = [
				'id'     => 'mollie',
				'label'  => 'Mollie / iDEAL',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#CBD5E1',
			];
		}

		// 7. Paddle
		if (! empty($omnify_settings['paddle_enabled'])) {
			$omnify_badges[] = [
				'id'     => 'paddle',
				'label'  => 'Paddle',
				'color'  => '#1E293B',
				'bg'     => '#F1F5F9',
				'border' => '#CBD5E1',
			];
		}

		// 8. Khalti
		if (! empty($omnify_settings['khalti_enabled']) && 'NPR' === $omnify_currency) {
			$omnify_badges[] = [
				'id'     => 'khalti',
				'label'  => 'Khalti',
				'color'  => '#581C87',
				'bg'     => '#F3E8FF',
				'border' => '#D8B4FE',
			];
		}

		// 9. eSewa
		if (! empty($omnify_settings['esewa_enabled']) && 'NPR' === $omnify_currency) {
			$omnify_badges[] = [
				'id'     => 'esewa',
				'label'  => 'eSewa',
				'color'  => '#166534',
				'bg'     => '#DCFCE7',
				'border' => '#86EFAC',
			];
		}

		// 10. SSLCommerz
		if (! empty($omnify_settings['sslcommerz_enabled']) && 'BDT' === $omnify_currency) {
			$omnify_badges[] = [
				'id'     => 'sslcommerz',
				'label'  => 'SSLCommerz',
				'color'  => '#991B1B',
				'bg'     => '#FEF2F2',
				'border' => '#FECACA',
			];
		}

		// 11. Alipay
		if (! empty($omnify_settings['alipay_enabled'])) {
			$omnify_badges[] = [
				'id'     => 'alipay',
				'label'  => 'Alipay',
				'color'  => '#0284C7',
				'bg'     => '#F0F9FF',
				'border' => '#BAE6FD',
			];
		}

		// 12. WeChat Pay
		if (! empty($omnify_settings['wechat_enabled'])) {
			$omnify_badges[] = [
				'id'     => 'wechat',
				'label'  => 'WeChat Pay',
				'color'  => '#166534',
				'bg'     => '#DCFCE7',
				'border' => '#86EFAC',
			];
		}

		// 13. Offline / Custom payment methods
		$methods = (array) ($omnify_settings['payment_methods'] ?? []);
		foreach ($methods as $m) {
			if (! empty($m['enabled'])) {
				$omnify_badges[] = [
					'id'     => (string) ($m['id'] ?? 'manual'),
					'label'  => (string) ($m['name'] ?? __('Manual Payment', 'omnifywp-ecommerce')),
					'color'  => '#334155',
					'bg'     => '#F8FAFC',
					'border' => '#CBD5E1',
				];
			}
		}

		// 14. SSL / Security badge
		$omnify_is_ssl = is_ssl() || (0 === strpos((string) home_url(), 'https://'));
		$omnify_badges[] = [
			'id'     => 'ssl',
			'label'  => $omnify_is_ssl ? '256-Bit SSL' : __('SSL Secured', 'omnifywp-ecommerce'),
			'color'  => '#15803D',
			'bg'     => '#F0FDF4',
			'border' => '#BBF7D0',
		];

		return (array) apply_filters('omnify_storefront_trust_badges', $omnify_badges, $omnify_settings);
	}

	private function currency_symbol(string $omnify_currency): string {
		return Omnify_Locations::currency_symbol($omnify_currency);
	}

	private function format_store_price(float $omnify_amount, array $omnify_settings): string {
		$omnify_base_currency = strtoupper((string) ($omnify_settings['default_currency'] ?? 'USD'));
		$omnify_currency      = $this->get_active_currency();

		if ($omnify_currency !== $omnify_base_currency) {
			$omnify_amount = Omnify_Locations::convert_currency($omnify_amount, $omnify_base_currency, $omnify_currency);
		}

		$omnify_symbol   = $this->currency_symbol($omnify_currency);
		$omnify_decimals = Omnify_Locations::currency_decimals($omnify_currency);
		if (2 === $omnify_decimals && isset($omnify_settings['price_decimals']) && '' !== (string) $omnify_settings['price_decimals']) {
			$omnify_decimals = max(0, min(4, (int) $omnify_settings['price_decimals']));
		}
		$omnify_thou     = $omnify_settings['thousand_separator'] ?? ',';
		$omnify_dec      = $omnify_settings['decimal_separator'] ?? '.';
		$omnify_price    = number_format($omnify_amount, $omnify_decimals, $omnify_dec, $omnify_thou);

		$omnify_position = strtolower((string) ($omnify_settings['currency_position'] ?? 'before'));
		switch ($omnify_position) {
			case 'after_no_space':
			case 'right':
				$omnify_formatted = $omnify_price . $omnify_symbol;
				break;
			case 'after':
			case 'after_space':
			case 'right_space':
				$omnify_formatted = $omnify_price . ' ' . $omnify_symbol;
				break;
			case 'before_space':
			case 'left_space':
				$omnify_formatted = $omnify_symbol . ' ' . $omnify_price;
				break;
			case 'before':
			case 'left':
			default:
				$omnify_formatted = $omnify_symbol . $omnify_price;
				break;
		}

		return apply_filters('omnify_format_price', $omnify_formatted, $omnify_amount, $omnify_settings);
	}

	public function render_storefront(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();

		$omnify_product_slug = isset($_GET['omnify_product']) ? sanitize_title(wp_unslash((string) $_GET['omnify_product'])) : '';
		$omnify_storefront_product_notice = '';
		if (! empty($omnify_product_slug)) {
			$omnify_product = $this->omnify_products->find_by_slug($omnify_product_slug);
			$omnify_can_preview = current_user_can('manage_options') || current_user_can('edit_posts');
			$omnify_is_viewable = $omnify_product && empty($omnify_product['deleted_at']) && (
				'published' === ($omnify_product['status'] ?? '') || $omnify_can_preview
			);

			if ($omnify_is_viewable) {
				$omnify_is_draft_preview = 'published' !== ($omnify_product['status'] ?? '');
				$omnify_prod_repo = $this->omnify_products;
				$omnify_reviews_repo = $this->omnify_reviews;
				$omnify_settings = get_option('omnify_settings', []);
				$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
				$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
				$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

				// Dynamic urgency from real data (Premium-like)
				$omnify_orders_repo = $this->omnify_orders;
				$omnify_abandoned_repo = $this->omnify_abandoned_carts ?: new \Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
				$omnify_sold_recent = $omnify_orders_repo->get_product_sales_count_last_hours( (int)$omnify_product['id'], 18 );
				$omnify_in_carts = $omnify_abandoned_repo->count_active_carts_for_product( (int)$omnify_product['id'] );
				$omnify_viewing = max(5, (int) ( $omnify_sold_recent * 3 + wp_rand(3,8) )); // realistic proxy + slight random for "live"

				$omnify_user_has_purchased = false;
				if (is_user_logged_in()) {
					$omnify_user_email = sanitize_email(wp_get_current_user()->user_email);
					$omnify_cust_record = $this->omnify_customers->find_by_email($omnify_user_email);
					if ($omnify_cust_record) {
						$omnify_cid = (int) $omnify_cust_record['id'];
						$omnify_pid = (int) $omnify_product['id'];
						$omnify_user_has_purchased = $this->omnify_access->has_access($omnify_cid, $omnify_pid)
							|| $this->omnify_orders->has_customer_purchased_product($omnify_cid, $omnify_pid);
					}
				}

				ob_start();
				include \omnify_locate_template('storefront/product-single.php');
				return (string) ob_get_clean();
			}

			$omnify_storefront_product_notice = sprintf(
				/* translators: %s: product slug */
				__('The requested product "%s" was not found or is currently unpublished.', 'omnifywp-ecommerce'),
				esc_html($omnify_product_slug)
			);
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_per_page = max(1, min(100, intval($omnify_settings['storefront_products_per_page'] ?? 12)));
		$omnify_offset = max(0, isset($_GET['offset']) ? absint(wp_unslash($_GET['offset'])) : 0);
		$omnify_filter_args = ['status' => 'published', 'per_page' => $omnify_per_page, 'offset' => $omnify_offset];

		$omnify_block_atts = is_array($omnify_attributes) ? $omnify_attributes : [];
		if (! empty($omnify_block_atts['category'])) {
			$omnify_filter_args['category'] = sanitize_title((string) $omnify_block_atts['category']);
		}
		if (! empty($omnify_block_atts['tag'])) {
			$omnify_filter_args['tag'] = sanitize_title((string) $omnify_block_atts['tag']);
		}
		if (! empty($omnify_block_atts['brand'])) {
			$omnify_filter_args['brand'] = sanitize_title((string) $omnify_block_atts['brand']);
		}
		if (isset($omnify_block_atts['minPrice']) && (float) $omnify_block_atts['minPrice'] >= 0) {
			$omnify_filter_args['price_min'] = (float) $omnify_block_atts['minPrice'];
		}
		if (isset($omnify_block_atts['maxPrice']) && (float) $omnify_block_atts['maxPrice'] >= 0) {
			$omnify_filter_args['price_max'] = (float) $omnify_block_atts['maxPrice'];
		}
		if (! empty($omnify_block_atts['stockStatus'])) {
			$omnify_filter_args['stock_status'] = sanitize_key((string) $omnify_block_atts['stockStatus']);
		}

		// Basic filters
		if (! empty($_GET['category'])) {
			$omnify_filter_args['category'] = sanitize_text_field(wp_unslash((string) $_GET['category']));
		}
		if (! empty($_GET['brand'])) {
			$omnify_filter_args['brand'] = sanitize_text_field(wp_unslash((string) $_GET['brand']));
		}
		if (! empty($_GET['filter_brand'])) {
			$omnify_filter_args['brand'] = sanitize_text_field(wp_unslash((string) $_GET['filter_brand']));
		}
		if (! empty($_GET['tag'])) {
			$omnify_filter_args['tag'] = sanitize_text_field(wp_unslash((string) $_GET['tag']));
		}
		if (! empty($_GET['type'])) {
			$omnify_filter_args['type'] = sanitize_key(wp_unslash((string) $_GET['type']));
		}
		if (! empty($_GET['search'])) {
			$omnify_filter_args['search'] = sanitize_text_field(wp_unslash((string) $_GET['search']));
		}
		if (isset($_GET['price_min'])) {
			$omnify_filter_args['price_min'] = max(0.0, (float) wp_unslash($_GET['price_min']));
		}
		if (isset($_GET['price_max'])) {
			$omnify_filter_args['price_max'] = max(0.0, (float) wp_unslash($_GET['price_max']));
		}
		if (! empty($_GET['stock_status'])) {
			$omnify_filter_args['stock_status'] = sanitize_key(wp_unslash((string) $_GET['stock_status']));
		}

		// Attribute filters e.g. ?filter_color=red&filter_brand=nike or attr_color=red
		$omnify_attr_filters = [];
		$omnify_raw_get = (array) wp_unslash($_GET);
		foreach ($omnify_raw_get as $omnify_key => $omnify_val) {
			$omnify_clean_key = sanitize_key($omnify_key);
			if (strpos($omnify_clean_key, 'filter_') === 0) {
				$omnify_attr = substr($omnify_clean_key, 7);
				$omnify_attr_filters[$omnify_attr] = is_array($omnify_val) ? map_deep($omnify_val, 'sanitize_text_field') : explode(',', sanitize_text_field((string) $omnify_val));
			} elseif (strpos($omnify_clean_key, 'attr_') === 0) {
				$omnify_attr = substr($omnify_clean_key, 5);
				$omnify_attr_filters[$omnify_attr] = is_array($omnify_val) ? map_deep($omnify_val, 'sanitize_text_field') : explode(',', sanitize_text_field((string) $omnify_val));
			}
		}
		if ($omnify_attr_filters) {
			$omnify_filter_args['attributes'] = $omnify_attr_filters;
		}

		$omnify_products = $this->omnify_products->all($omnify_filter_args);
		$omnify_product_ids = ! empty($omnify_products) ? array_map(static fn(array $omnify_p): int => (int) ($omnify_p['id'] ?? 0), $omnify_products) : [];
		$omnify_rating_summaries = ! empty($omnify_product_ids) ? $this->omnify_reviews->get_rating_summaries($omnify_product_ids) : [];

		$omnify_variable_ids = [];
		foreach ($omnify_products as $omnify_p) {
			if (($omnify_p['type'] ?? 'simple') === 'variable') {
				$omnify_variable_ids[] = (int) ($omnify_p['id'] ?? 0);
			}
		}
		$omnify_grid_variations = ! empty($omnify_variable_ids) ? $this->omnify_products->get_variations_for_products($omnify_variable_ids) : [];

		$omnify_prod_repo = $this->omnify_products;
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		ob_start();
		include \omnify_locate_template('storefront/grid.php');
		return (string) ob_get_clean();
	}

	public function render_cart(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');
		$omnify_atts       = is_array($omnify_attributes) ? $omnify_attributes : [];
		$omnify_show_steps = ! isset($omnify_atts['showSteps']) || ! empty($omnify_atts['showSteps']);
		$omnify_show_note  = ! isset($omnify_atts['showNote']) || ! empty($omnify_atts['showNote']);

		ob_start();
		include \omnify_locate_template('storefront/cart.php');
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	public function render_mini_cart(array|string $omnify_atts = []): string {
		$this->enqueue_assets();

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		$omnify_atts = shortcode_atts(
			[
				'label'              => __('Cart', 'omnifywp-ecommerce'),
				'align'              => 'right',
				'showShippingMeter'  => true,
				'shippingThreshold'  => 0,
				'showCount'          => true,
				'showSubtotal'       => true,
				'showViewCartButton' => true,
				'showCheckoutButton' => true,
				'showEmptyState'     => true,
			],
			is_array($omnify_atts) ? $omnify_atts : [],
			'omnify_mini_cart'
		);
		$omnify_show_shipping_meter  = ! empty($omnify_atts['showShippingMeter']);
		$omnify_show_count           = ! empty($omnify_atts['showCount']);
		$omnify_show_subtotal        = ! empty($omnify_atts['showSubtotal']);
		$omnify_show_view_cart       = ! empty($omnify_atts['showViewCartButton']);
		$omnify_show_checkout_btn    = ! empty($omnify_atts['showCheckoutButton']);
		$omnify_show_empty_state     = ! empty($omnify_atts['showEmptyState']);

		// Resolve free shipping minimum threshold across delivery zones or global settings
		$omnify_free_shipping_min = null;
		if (! empty($omnify_atts['shippingThreshold']) && (float) $omnify_atts['shippingThreshold'] > 0) {
			$omnify_free_shipping_min = (float) $omnify_atts['shippingThreshold'];
		}
		if (! empty($omnify_settings['delivery_zones']) && is_array($omnify_settings['delivery_zones'])) {
			foreach ($omnify_settings['delivery_zones'] as $omnify_zone) {
				if (! empty($omnify_zone['enabled']) && isset($omnify_zone['free_min']) && '' !== (string) $omnify_zone['free_min']) {
					$omnify_val = (float) $omnify_zone['free_min'];
					if ($omnify_val > 0 && (null === $omnify_free_shipping_min || $omnify_val < $omnify_free_shipping_min)) {
						$omnify_free_shipping_min = $omnify_val;
					}
				}
			}
		}
		if (null === $omnify_free_shipping_min && isset($omnify_settings['free_shipping_threshold']) && '' !== (string) $omnify_settings['free_shipping_threshold']) {
			$omnify_val = (float) $omnify_settings['free_shipping_threshold'];
			if ($omnify_val > 0) {
				$omnify_free_shipping_min = $omnify_val;
			}
		}
		$omnify_free_shipping_min = (float) ($omnify_free_shipping_min ?? 50.0);

		ob_start();
		include \omnify_locate_template('storefront/mini-cart.php');
		return (string) ob_get_clean();
	}

	public function append_mini_cart_to_menu(string $omnify_items, \stdClass $omnify_args): string {
		$omnify_settings = get_option('omnify_settings', []);
		if (empty($omnify_settings['mini_cart_menu_enabled'])) {
			return $omnify_items;
		}

		$omnify_configured_location = sanitize_key((string) ($omnify_settings['mini_cart_menu_location'] ?? ''));
		$omnify_current_location = sanitize_key((string) ($omnify_args->theme_location ?? ''));
		if ('' !== $omnify_configured_location && $omnify_configured_location !== $omnify_current_location) {
			return $omnify_items;
		}

		$omnify_mini_cart = $this->render_mini_cart(['label' => __('Cart', 'omnifywp-ecommerce')]);
		if ('' === trim($omnify_mini_cart)) {
			return $omnify_items;
		}

		return $omnify_items . '<li class="menu-item omnify-mini-cart-menu-item">' . $omnify_mini_cart . '</li>';
	}

	public function render_checkout(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();

		if (isset($_GET['omnify_stripe']) && 'cancelled' === sanitize_key(wp_unslash($_GET['omnify_stripe']))) {
			$omnify_order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
			$omnify_order_key = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : '';
			if ($omnify_order_id > 0) {
				$omnify_order = $this->omnify_orders->find($omnify_order_id);
				if ($omnify_order) {
					$omnify_expected_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);
					if (hash_equals($omnify_expected_key, $omnify_order_key)) {
						$this->cancel_order_payment_by_storefront($omnify_order_id, 'stripe', 'User cancelled session');
					}
				}
			}
		}

		if (isset($_GET['omnify_paypal']) && 'cancelled' === sanitize_key(wp_unslash($_GET['omnify_paypal']))) {
			$omnify_order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
			$omnify_order_key = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : '';
			if ($omnify_order_id > 0) {
				$omnify_order = $this->omnify_orders->find($omnify_order_id);
				if ($omnify_order) {
					$omnify_expected_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);
					if (hash_equals($omnify_expected_key, $omnify_order_key)) {
						$this->cancel_order_payment_by_storefront($omnify_order_id, 'paypal', 'User cancelled session');
					}
				}
			}
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_atts = is_array($omnify_attributes) ? $omnify_attributes : [];
		$omnify_show_order_notes = ! isset($omnify_atts['showOrderNotes']) || ! empty($omnify_atts['showOrderNotes']);
		$omnify_tax_rate = (float) ($omnify_settings['tax_rate'] ?? 0.0);
		$omnify_tax_label = sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax'));
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		$omnify_is_cart_checkout = isset($_GET['cart']) && '1' === sanitize_text_field(wp_unslash((string) $_GET['cart']));
		$omnify_cart_items = [];
		$omnify_is_physical_checkout = false;
		$omnify_line_total = 0.0;
		$omnify_product = null;
		$omnify_variation = null;
		$omnify_checkout_quantity = 1;
		$omnify_actual_price = 0.0;

		if ($omnify_is_cart_checkout) {
			$omnify_token = isset($_COOKIE['omnify_cart_token']) ? sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_cart_token'])) : '';
			$omnify_transient_key = 'omnify_cart_' . md5($omnify_token);
			$omnify_raw_items = get_transient($omnify_transient_key);
			$omnify_raw_items = is_array($omnify_raw_items) ? $omnify_raw_items : [];

			foreach ($omnify_raw_items as $omnify_item) {
				$omnify_prod = $this->omnify_products->find((int) $omnify_item['product_id']);
				if ($omnify_prod && 'published' === ($omnify_prod['status'] ?? '')) {
					$omnify_item_variation = null;
					$omnify_variation_id = absint($omnify_item['variation_id'] ?? 0);
					if ($omnify_variation_id && ! empty($omnify_prod['variations']) && is_array($omnify_prod['variations'])) {
						foreach ($omnify_prod['variations'] as $omnify_candidate) {
							if ((int) ($omnify_candidate['id'] ?? 0) === $omnify_variation_id) {
								$omnify_item_variation = $omnify_candidate;
								break;
							}
						}
					}
					$omnify_price = $omnify_item_variation
						? ((null !== ($omnify_item_variation['sale_price'] ?? null) && '' !== $omnify_item_variation['sale_price']) ? (float) $omnify_item_variation['sale_price'] : (float) $omnify_item_variation['price'])
						: ((null !== ($omnify_prod['sale_price'] ?? null) && '' !== $omnify_prod['sale_price']) ? (float) $omnify_prod['sale_price'] : (float) $omnify_prod['price']);
					
					$omnify_variation_settings = wp_parse_args($omnify_prod['variation_settings'] ?? [], ['product_kind' => 'digital']);
					$omnify_is_phys = ('physical' === ($omnify_prod['type'] ?? 'download')) || ('variable' === ($omnify_prod['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
					
					if ($omnify_is_phys) {
						$omnify_is_physical_checkout = true;
					}

					$omnify_qty = max(1, absint($omnify_item['quantity'] ?? 1));
					$omnify_line_total += $omnify_price * $omnify_qty;

					$omnify_cart_items[] = [
						'product' => $omnify_prod,
						'variation' => $omnify_item_variation,
						'quantity' => $omnify_qty,
						'price' => $omnify_price,
						'is_physical' => $omnify_is_phys,
					];
				}
			}
			$omnify_product = [
				'id' => 0,
				'name' => __('Cart order', 'omnifywp-ecommerce'),
				'price' => $omnify_line_total,
				'sale_price' => null,
				'currency' => $omnify_settings['default_currency'] ?? 'USD',
				'type' => $omnify_is_physical_checkout ? 'physical' : 'download',
				'thumbnail_url' => '',
				'variation_settings' => ['product_kind' => $omnify_is_physical_checkout ? 'physical' : 'digital'],
				'variations' => [],
				'max_purchase_qty' => 0,
			];
		} else {
			$omnify_product_id = isset($_GET['product_id']) ? absint(wp_unslash($_GET['product_id'])) : 0;
			$omnify_product = $omnify_product_id ? $this->omnify_products->find($omnify_product_id) : null;
			if ($omnify_product) {
				$omnify_variation_id = isset($_GET['variation_id']) ? absint(wp_unslash($_GET['variation_id'])) : 0;
				$omnify_checkout_quantity = max(1, isset($_GET['quantity']) ? absint(wp_unslash($_GET['quantity'])) : 1);
				$omnify_max_purchase_qty = isset($omnify_product['max_purchase_qty']) ? (int) $omnify_product['max_purchase_qty'] : 0;
				if ($omnify_max_purchase_qty > 0 && $omnify_checkout_quantity > $omnify_max_purchase_qty) {
					$omnify_checkout_quantity = $omnify_max_purchase_qty;
				}
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
				$omnify_actual_price = $omnify_variation
					? ((null !== ($omnify_variation['sale_price'] ?? null) && '' !== $omnify_variation['sale_price']) ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'])
					: ((null !== ($omnify_product['sale_price'] ?? null) && '' !== $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price']);
			}
		}

		$omnify_addresses = [];
		$omnify_default_address = null;
		$omnify_customer = null;
		if (is_user_logged_in()) {
			$omnify_uid = get_current_user_id();
			$omnify_customer = $this->omnify_customers->find_by_user_id($omnify_uid);
			if (! $omnify_customer) {
				$omnify_current_user = wp_get_current_user();
				if ($omnify_current_user && ! empty($omnify_current_user->user_email)) {
					$omnify_customer = $this->omnify_customers->find_by_email($omnify_current_user->user_email);
				}
			}

			$omnify_saved_addresses = get_user_meta($omnify_uid, '_omnify_shipping_addresses', true);
			if (is_array($omnify_saved_addresses)) {
				$omnify_addresses = $omnify_saved_addresses;
			}

			if ($omnify_customer) {
				if (! empty($omnify_customer['billing_address_1'])) {
					$omnify_exists = false;
					foreach ($omnify_addresses as $omnify_addr_check) {
						if (($omnify_addr_check['address_1'] ?? '') === $omnify_customer['billing_address_1']) {
							$omnify_exists = true;
							break;
						}
					}
					if (! $omnify_exists) {
						$omnify_addresses[] = [
							'id'         => 'customer_billing',
							'label'      => __('Billing Address', 'omnifywp-ecommerce'),
							'first_name' => $omnify_customer['billing_first_name'] ?? $omnify_customer['first_name'] ?? '',
							'last_name'  => $omnify_customer['billing_last_name'] ?? $omnify_customer['last_name'] ?? '',
							'company'    => $omnify_customer['billing_company'] ?? '',
							'address_1'  => $omnify_customer['billing_address_1'],
							'address_2'  => $omnify_customer['billing_address_2'] ?? '',
							'city'       => $omnify_customer['billing_city'] ?? '',
							'state'      => $omnify_customer['billing_state'] ?? '',
							'postcode'   => $omnify_customer['billing_postcode'] ?? '',
							'country'    => $omnify_customer['billing_country'] ?? '',
							'phone'      => $omnify_customer['billing_phone'] ?? $omnify_customer['phone'] ?? '',
							'is_default' => empty($omnify_addresses),
						];
					}
				}

				if (! empty($omnify_customer['shipping_address_1'])) {
					$omnify_exists = false;
					foreach ($omnify_addresses as $omnify_addr_check) {
						if (($omnify_addr_check['address_1'] ?? '') === $omnify_customer['shipping_address_1']) {
							$omnify_exists = true;
							break;
						}
					}
					if (! $omnify_exists) {
						$omnify_addresses[] = [
							'id'         => 'customer_shipping',
							'label'      => __('Shipping Address', 'omnifywp-ecommerce'),
							'first_name' => $omnify_customer['shipping_first_name'] ?? $omnify_customer['first_name'] ?? '',
							'last_name'  => $omnify_customer['shipping_last_name'] ?? $omnify_customer['last_name'] ?? '',
							'company'    => $omnify_customer['shipping_company'] ?? '',
							'address_1'  => $omnify_customer['shipping_address_1'],
							'address_2'  => $omnify_customer['shipping_address_2'] ?? '',
							'city'       => $omnify_customer['shipping_city'] ?? '',
							'state'      => $omnify_customer['shipping_state'] ?? '',
							'postcode'   => $omnify_customer['shipping_postcode'] ?? '',
							'country'    => $omnify_customer['shipping_country'] ?? '',
							'phone'      => $omnify_customer['shipping_phone'] ?? $omnify_customer['phone'] ?? '',
							'is_default' => empty($omnify_addresses),
						];
					}
				}
			}

			// Also check WP user meta as fallback
			$omnify_wp_b_addr = (string) get_user_meta($omnify_uid, 'billing_address_1', true);
			if (! empty($omnify_wp_b_addr)) {
				$omnify_exists = false;
				foreach ($omnify_addresses as $omnify_addr_check) {
					if (($omnify_addr_check['address_1'] ?? '') === $omnify_wp_b_addr) {
						$omnify_exists = true;
						break;
					}
				}
				if (! $omnify_exists) {
					$omnify_addresses[] = [
						'id'         => 'wp_billing',
						'label'      => __('Billing Address', 'omnifywp-ecommerce'),
						'first_name' => (string) (get_user_meta($omnify_uid, 'billing_first_name', true) ?: get_user_meta($omnify_uid, 'first_name', true)),
						'last_name'  => (string) (get_user_meta($omnify_uid, 'billing_last_name', true) ?: get_user_meta($omnify_uid, 'last_name', true)),
						'company'    => (string) get_user_meta($omnify_uid, 'billing_company', true),
						'address_1'  => $omnify_wp_b_addr,
						'address_2'  => (string) get_user_meta($omnify_uid, 'billing_address_2', true),
						'city'       => (string) get_user_meta($omnify_uid, 'billing_city', true),
						'state'      => (string) get_user_meta($omnify_uid, 'billing_state', true),
						'postcode'   => (string) get_user_meta($omnify_uid, 'billing_postcode', true),
						'country'    => (string) get_user_meta($omnify_uid, 'billing_country', true),
						'phone'      => (string) get_user_meta($omnify_uid, 'billing_phone', true),
						'is_default' => empty($omnify_addresses),
					];
				}
			}

			foreach ($omnify_addresses as $omnify_addr) {
				if (! empty($omnify_addr['is_default'])) {
					$omnify_default_address = $omnify_addr;
					break;
				}
			}
			if (! $omnify_default_address && ! empty($omnify_addresses)) {
				$omnify_default_address = $omnify_addresses[0];
			}
		}

		$omnify_is_redirect_success = (isset($_REQUEST['omnify_stripe']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_stripe'])))
			|| (isset($_REQUEST['omnify_paypal']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_paypal'])))
			|| (isset($_REQUEST['omnify_razorpay']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_razorpay'])))
			|| (isset($_REQUEST['omnify_alipay']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_alipay'])))
			|| (isset($_REQUEST['omnify_wechat']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_wechat'])))
			|| (isset($_REQUEST['omnify_sslcommerz']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_sslcommerz'])))
			|| (isset($_REQUEST['omnify_paystack']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_paystack'])))
			|| (isset($_REQUEST['omnify_tap']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_tap'])))
			|| (isset($_REQUEST['omnify_mollie']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_mollie'])))
			|| (isset($_REQUEST['omnify_khalti']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_khalti'])))
			|| (isset($_REQUEST['omnify_esewa']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_esewa'])))
			|| (isset($_REQUEST['omnify_paddle']) && 'success' === sanitize_key(wp_unslash($_REQUEST['omnify_paddle'])));

		$omnify_redirect_order_id = isset($_REQUEST['order_id']) ? absint(wp_unslash($_REQUEST['order_id'])) : (isset($_REQUEST['value_a']) ? absint(wp_unslash($_REQUEST['value_a'])) : 0);
		if (! $omnify_redirect_order_id) {
			foreach (['out_trade_no', 'tran_id', 'reference', 'trxref', 'transaction_id', 'pidx'] as $omnify_rk) {
				if (! empty($_REQUEST[$omnify_rk])) {
					$omnify_raw_ref = sanitize_text_field(wp_unslash((string) $_REQUEST[$omnify_rk]));
					if (preg_match('/(?:ssl|alipay|wechat|pstk|tap|esewa|khalti|txn|order)_(\d+)/i', $omnify_raw_ref, $omnify_m)) {
						$omnify_redirect_order_id = absint($omnify_m[1]);
						break;
					}
				}
			}
		}

		$omnify_success_order = $omnify_redirect_order_id ? $this->omnify_orders->find($omnify_redirect_order_id) : null;
		$omnify_success_files = [];
		if ($omnify_is_redirect_success && $omnify_success_order) {
			if ('completed' !== ($omnify_success_order['status'] ?? '')) {
				$omnify_gw_name = 'online';
				foreach (['stripe', 'paypal', 'razorpay', 'alipay', 'wechat', 'sslcommerz', 'paystack', 'tap', 'mollie', 'khalti', 'esewa', 'paddle'] as $gwn) {
					if (isset($_REQUEST['omnify_' . $gwn])) {
						$omnify_gw_name = $gwn;
						break;
					}
				}
				$omnify_txn = sanitize_text_field((string) (
					$_REQUEST['payment_id'] ??
					$_REQUEST['session_id'] ??
					$_REQUEST['charge_id'] ??
					$_REQUEST['tap_id'] ??
					$_REQUEST['pidx'] ??
					$_REQUEST['transaction_id'] ??
					$_REQUEST['reference'] ??
					$_REQUEST['trxref'] ??
					$_REQUEST['val_id'] ??
					$_REQUEST['tran_id'] ??
					$_REQUEST['token'] ??
					$_REQUEST['out_trade_no'] ??
					$_REQUEST['p_txn'] ??
					''
				));
				$this->complete_order_payment_by_storefront($omnify_redirect_order_id, $omnify_gw_name, $omnify_txn ?: null);
				$omnify_success_order = $this->omnify_orders->find($omnify_redirect_order_id);
			}

			if (isset($_COOKIE['omnify_cart_token'])) {
				$omnify_ctoken = sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_cart_token']));
				delete_transient('omnify_cart_' . md5($omnify_ctoken));
			}

			foreach ((array) ($omnify_success_order['items'] ?? []) as $omnify_ord_item) {
				$omnify_prod_ids = [(int) $omnify_ord_item['product_id']];
				$omnify_p = $this->omnify_products->find((int) $omnify_ord_item['product_id']);
				if ($omnify_p && ! empty($omnify_p['bundled_products']) && is_array($omnify_p['bundled_products'])) {
					foreach ($omnify_p['bundled_products'] as $bpid) {
						$omnify_prod_ids[] = (int) $bpid;
					}
				}
				$omnify_order_cust_id = (int) ($omnify_success_order['customer_id'] ?? 0);
				foreach ($omnify_prod_ids as $pid) {
					$omnify_item_files = $this->omnify_files->for_product($pid);
					foreach ($omnify_item_files as $omnify_f) {
						$omnify_signed_url = $this->omnify_signed_urls->create((int) $omnify_f['id'], 3600 * 24 * 7, $omnify_order_cust_id);
						$omnify_success_files[] = [
							'file_name'    => $omnify_f['file_name'],
							'file_size'    => size_format((int) $omnify_f['file_size']),
							'download_url' => $omnify_signed_url,
						];
					}
				}
			}
		}

		ob_start();
		include \omnify_locate_template('storefront/checkout-page.php');
		return (string) ob_get_clean();
	}

	public function render_customer_portal(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();

		$omnify_atts           = is_array($omnify_attributes) ? $omnify_attributes : [];
		$omnify_default_tab    = sanitize_key((string) ($omnify_atts['defaultTab'] ?? ''));
		$omnify_allowed_tabs   = ['products', 'downloads', 'orders', 'invoices', 'wishlist', 'history', 'settings'];
		if (! in_array($omnify_default_tab, $omnify_allowed_tabs, true)) {
			$omnify_default_tab = '';
		}
		$omnify_show_stats     = ! isset($omnify_atts['showStats']) || ! empty($omnify_atts['showStats']);
		$omnify_show_greeting  = ! isset($omnify_atts['showGreeting']) || ! empty($omnify_atts['showGreeting']);
		$omnify_show_logout    = ! isset($omnify_atts['showLogout']) || ! empty($omnify_atts['showLogout']);
		$omnify_show_invoices  = ! isset($omnify_atts['showInvoices']) || ! empty($omnify_atts['showInvoices']);
		$omnify_show_wishlist  = ! isset($omnify_atts['showWishlistTab']) || ! empty($omnify_atts['showWishlistTab']);

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		$omnify_profile_saved = isset($_GET['message']) && 'profile_saved' === sanitize_key(wp_unslash($_GET['message']));

		// Process multiple shipping address actions if logged in
		if (is_user_logged_in()) {
			$omnify_user_id = get_current_user_id();
			$omnify_action = isset($_POST['omnify_action']) ? sanitize_key(wp_unslash($_POST['omnify_action'])) : (isset($_GET['omnify_action']) ? sanitize_key(wp_unslash($_GET['omnify_action'])) : '');

			if ('omnify_add_address' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_address_nonce'] ?? '')), 'omnify_manage_addresses')) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					$omnify_addr_name = isset($_POST['shipping_address_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_name'])) : '';

					$omnify_new_address = [
						'id'           => uniqid('addr_'),
						'label'        => $omnify_addr_name,
						'address_name' => $omnify_addr_name,
						'first_name'   => isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '',
						'last_name'    => isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '',
						'phone'        => isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '',
						'company'      => isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '',
						'address_1'    => isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '',
						'address_2'    => isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '',
						'city'         => isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '',
						'state'        => isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '',
						'postcode'     => isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '',
						'country'      => strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2)),
						'is_default'   => ! empty($_POST['is_default']),
					];

					if ($omnify_new_address['is_default']) {
						foreach ($omnify_addresses as &$omnify_addr) {
							$omnify_addr['is_default'] = false;
						}
					} elseif (empty($omnify_addresses)) {
						$omnify_new_address['is_default'] = true;
					}

					$omnify_addresses[] = $omnify_new_address;
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'address_added'], remove_query_arg(['omnify_action', 'address_id', '_wpnonce'])));
					exit;
				}
			} elseif ('omnify_edit_address' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_address_nonce'] ?? '')), 'omnify_manage_addresses')) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];
					$omnify_addr_id = isset($_POST['address_id']) ? sanitize_key(wp_unslash($_POST['address_id'])) : '';

					foreach ($omnify_addresses as &$omnify_addr) {
						if ($omnify_addr['id'] === $omnify_addr_id) {
							$omnify_addr_name = isset($_POST['shipping_address_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_name'])) : '';
							$omnify_addr['label'] = $omnify_addr_name;
							$omnify_addr['address_name'] = $omnify_addr_name;
							$omnify_addr['first_name'] = isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '';
							$omnify_addr['last_name'] = isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '';
							$omnify_addr['phone'] = isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '';
							$omnify_addr['company'] = isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '';
							$omnify_addr['address_1'] = isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '';
							$omnify_addr['address_2'] = isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '';
							$omnify_addr['city'] = isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '';
							$omnify_addr['state'] = isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '';
							$omnify_addr['postcode'] = isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '';
							$omnify_addr['country'] = strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2));

							if (! empty($_POST['is_default'])) {
								$omnify_addr['is_default'] = true;
								foreach ($omnify_addresses as &$omnify_other_addr) {
									if ($omnify_other_addr['id'] !== $omnify_addr_id) {
										$omnify_other_addr['is_default'] = false;
									}
								}
							}
							break;
						}
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'address_updated'], remove_query_arg(['omnify_action', 'address_id', '_wpnonce'])));
					exit;
				}
			} elseif ('omnify_delete_address' === $omnify_action) {
				$omnify_addr_id = isset($_GET['address_id']) ? sanitize_key(wp_unslash($_GET['address_id'])) : '';
				if ($omnify_addr_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'omnify_delete_address_' . $omnify_addr_id)) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					$omnify_was_default = false;
					$omnify_filtered = [];
					foreach ($omnify_addresses as $omnify_addr) {
						if ($omnify_addr['id'] === $omnify_addr_id) {
							if (!empty($omnify_addr['is_default'])) {
								$omnify_was_default = true;
							}
							continue;
						}
						$omnify_filtered[] = $omnify_addr;
					}
					if ($omnify_was_default && ! empty($omnify_filtered)) {
						$omnify_filtered[0]['is_default'] = true;
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_filtered);

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'address_deleted'], remove_query_arg(['omnify_action', 'address_id', '_wpnonce'])));
					exit;
				}
			} elseif ('omnify_default_address' === $omnify_action) {
				$omnify_addr_id = isset($_GET['address_id']) ? sanitize_key(wp_unslash($_GET['address_id'])) : '';
				if ($omnify_addr_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'omnify_default_address_' . $omnify_addr_id)) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					foreach ($omnify_addresses as &$omnify_addr) {
						$omnify_addr['is_default'] = ($omnify_addr['id'] === $omnify_addr_id);
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);

					$omnify_cust_record = $this->omnify_customers->find_by_user_id($omnify_user_id);
					if ($omnify_cust_record) {
						foreach ($omnify_addresses as $omnify_check_addr) {
							if (! empty($omnify_check_addr['is_default'])) {
								$this->omnify_customers->update((int) $omnify_cust_record['id'], [
									'shipping_address_1' => (string) ($omnify_check_addr['address_1'] ?? ''),
									'shipping_address_2' => (string) ($omnify_check_addr['address_2'] ?? ''),
									'shipping_city'      => (string) ($omnify_check_addr['city'] ?? ''),
									'shipping_state'     => (string) ($omnify_check_addr['state'] ?? ''),
									'shipping_postcode'  => (string) ($omnify_check_addr['postcode'] ?? ''),
									'shipping_country'   => (string) ($omnify_check_addr['country'] ?? ''),
								]);
								break;
							}
						}
					}

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'default_updated'], remove_query_arg(['omnify_action', 'address_id', '_wpnonce'])));
					exit;
				}
			} elseif ('omnify_request_refund' === $omnify_action) {
				$omnify_order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
				if ($omnify_order_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_refund_nonce'] ?? '')), 'omnify_request_refund_' . $omnify_order_id)) {
					$omnify_customer_ids = [];
					$omnify_wp_user  = wp_get_current_user();
					$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

					$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
					$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;
					$omnify_db_customer       = $omnify_customer_by_email ?: $omnify_customer_by_uid;

					if ($omnify_db_customer) {
						$omnify_customer_ids[] = (int) $omnify_db_customer['id'];
					}

					global $wpdb;
					$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
					$omnify_cust_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
						"SELECT id FROM {$omnify_customers_table} WHERE user_id = %d OR email = %s",
						(int) $omnify_wp_user->ID,
						$omnify_wp_email
					), ARRAY_A);

					if (is_array($omnify_cust_rows)) {
						foreach ($omnify_cust_rows as $omnify_row) {
							$omnify_customer_ids[] = (int) $omnify_row['id'];
						}
					}
					$omnify_customer_ids = array_unique(array_filter($omnify_customer_ids));

					$omnify_order = $this->omnify_orders->find($omnify_order_id);
					if ($omnify_order && in_array((int) $omnify_order['customer_id'], $omnify_customer_ids, true)) {
						$omnify_is_eligible = $this->order_is_refund_eligible($omnify_order, $omnify_settings);

						if ($omnify_is_eligible) {
							$this->omnify_orders->update_status($omnify_order_id, 'refund_requested');

							$omnify_reason = isset($_POST['refund_reason']) ? sanitize_textarea_field(wp_unslash($_POST['refund_reason'])) : '';
							$omnify_note = sprintf(
								__('Customer requested a refund. Reason: %s', 'omnifywp-ecommerce'),
								$omnify_reason ?: __('No reason provided.', 'omnifywp-ecommerce')
							);
							$this->omnify_orders->add_note($omnify_order_id, $omnify_note);
							$this->omnify_emails->send_refund_requested($omnify_order_id, $omnify_reason);

							wp_safe_redirect(add_query_arg('message', 'refund_requested', get_permalink()));
							exit;
						}
					}
				}
			} elseif ('omnify_reorder' === $omnify_action) {
				$omnify_order_id = isset($_REQUEST['order_id']) ? absint(wp_unslash($_REQUEST['order_id'])) : 0;
				if ($omnify_order_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'omnify_reorder_' . $omnify_order_id)) {
					$omnify_customer_ids = [];
					$omnify_wp_user  = wp_get_current_user();
					$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

					$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
					$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;
					$omnify_db_customer       = $omnify_customer_by_email ?: $omnify_customer_by_uid;

					if ($omnify_db_customer) {
						$omnify_customer_ids[] = (int) $omnify_db_customer['id'];
					}

					global $wpdb;
					$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
					$omnify_cust_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
						"SELECT id FROM {$omnify_customers_table} WHERE user_id = %d OR email = %s",
						(int) $omnify_wp_user->ID,
						$omnify_wp_email
					), ARRAY_A);

					if (is_array($omnify_cust_rows)) {
						foreach ($omnify_cust_rows as $omnify_row) {
							$omnify_customer_ids[] = (int) $omnify_row['id'];
						}
					}
					$omnify_customer_ids = array_unique(array_filter($omnify_customer_ids));

					$omnify_order = $this->omnify_orders->find($omnify_order_id);
					if ($omnify_order && in_array((int) $omnify_order['customer_id'], $omnify_customer_ids, true)) {
						$omnify_cart_token = isset($_COOKIE['omnify_cart_token']) ? sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_cart_token'])) : '';
						if ('' === $omnify_cart_token) {
							$omnify_cart_token = wp_generate_uuid4();
							if (! headers_sent()) {
								setcookie('omnify_cart_token', $omnify_cart_token, [
									'expires'  => time() + (30 * DAY_IN_SECONDS),
									'path'     => COOKIEPATH ?: '/',
									'domain'   => COOKIE_DOMAIN ?: '',
									'secure'   => is_ssl(),
									'httponly' => false,
									'samesite' => 'Lax',
								]);
							}
						}

						$omnify_transient_key = 'omnify_cart_' . md5($omnify_cart_token);
						$omnify_session_cart = get_transient($omnify_transient_key);
						$omnify_session_cart = is_array($omnify_session_cart) ? $omnify_session_cart : [];

						$omnify_items_added = 0;
						foreach ((array) ($omnify_order['items'] ?? []) as $omnify_item) {
							$omnify_pid = (int) ($omnify_item['product_id'] ?? 0);
							$omnify_var_id = ! empty($omnify_item['variation_id']) ? (int) $omnify_item['variation_id'] : null;
							$omnify_qty = max(1, (int) ($omnify_item['quantity'] ?? 1));

							$omnify_prod = $this->omnify_products->find($omnify_pid);
							if ($omnify_prod && 'published' === ($omnify_prod['status'] ?? '') && empty($omnify_prod['deleted_at'])) {
								$omnify_cart_key = $omnify_pid . ':' . ($omnify_var_id ?: 0);
								$omnify_session_cart[$omnify_cart_key] = [
									'product_id'   => $omnify_pid,
									'variation_id' => $omnify_var_id,
									'quantity'     => $omnify_qty,
								];
								$omnify_items_added++;
							}
						}

						if ($omnify_items_added > 0) {
							set_transient($omnify_transient_key, $omnify_session_cart, 30 * DAY_IN_SECONDS);
							$omnify_checkout_url = ! empty($omnify_settings['checkout_page_id'])
								? get_permalink((int) $omnify_settings['checkout_page_id'])
								: home_url('/checkout');
							wp_safe_redirect(add_query_arg(['omnify_reordered' => 1], $omnify_checkout_url));
							exit;
						}
					}
				}
			} elseif ('omnify_change_password' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_password_nonce'] ?? '')), 'omnify_change_password')) {
					$omnify_wp_user = wp_get_current_user();
					$omnify_current_pass = isset($_POST['current_password']) ? (string) wp_unslash($_POST['current_password']) : '';
					$omnify_new_pass     = isset($_POST['new_password']) ? (string) wp_unslash($_POST['new_password']) : '';
					$omnify_confirm_pass = isset($_POST['confirm_password']) ? (string) wp_unslash($_POST['confirm_password']) : '';

					if (! wp_check_password($omnify_current_pass, $omnify_wp_user->user_pass, $omnify_wp_user->ID)) {
						wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'password_incorrect'], get_permalink()));
						exit;
					} elseif (strlen($omnify_new_pass) < 8) {
						wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'password_too_short'], get_permalink()));
						exit;
					} elseif ($omnify_new_pass !== $omnify_confirm_pass) {
						wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'password_mismatch'], get_permalink()));
						exit;
					} else {
						wp_set_password($omnify_new_pass, $omnify_wp_user->ID);
						wp_set_auth_cookie($omnify_wp_user->ID);
						wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'password_updated'], get_permalink()));
						exit;
					}
				}
			} elseif ('omnify_send_verification_email' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'omnify_verify_email_request')) {
					$omnify_wp_user = wp_get_current_user();
					$omnify_email = sanitize_email($omnify_wp_user->user_email);
					$omnify_exp = time() + (24 * HOUR_IN_SECONDS);
					$omnify_sig = hash_hmac('sha256', (string) $omnify_wp_user->ID . '|' . $omnify_email . '|' . $omnify_exp, wp_salt('auth'));
					$omnify_verify_link = add_query_arg([
						'omnify_action' => 'omnify_confirm_verify_email',
						'uid'           => (int) $omnify_wp_user->ID,
						'expires'       => $omnify_exp,
						'sig'           => $omnify_sig,
					], get_permalink());

					$omnify_subject = sprintf(__('[%s] Verify your email address', 'omnifywp-ecommerce'), get_bloginfo('name'));
					$omnify_body = sprintf(
						__("Hi %s,\n\nPlease verify your email address for your %s customer account by clicking the link below:\n\n%s\n\nThis verification link expires in 24 hours.\n\nThank you!", 'omnifywp-ecommerce'),
						$omnify_wp_user->display_name ?: $omnify_email,
						get_bloginfo('name'),
						$omnify_verify_link
					);
					wp_mail($omnify_email, $omnify_subject, $omnify_body);
					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'verification_sent'], get_permalink()));
					exit;
				}
			} elseif ('omnify_confirm_verify_email' === $omnify_action) {
				$omnify_uid = absint(wp_unslash($_GET['uid'] ?? 0));
				$omnify_exp = absint(wp_unslash($_GET['expires'] ?? 0));
				$omnify_sig = sanitize_text_field(wp_unslash($_GET['sig'] ?? ''));

				$omnify_target_user = get_userdata($omnify_uid);
				if ($omnify_target_user && $omnify_exp >= time()) {
					$omnify_expected_sig = hash_hmac('sha256', (string) $omnify_uid . '|' . sanitize_email($omnify_target_user->user_email) . '|' . $omnify_exp, wp_salt('auth'));
					if (hash_equals($omnify_expected_sig, $omnify_sig)) {
						update_user_meta($omnify_uid, '_omnify_email_verified', '1');
						update_user_meta($omnify_uid, '_omnify_email_verified_at', current_time('mysql'));
						wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'email_verified'], get_permalink()));
						exit;
					}
				}
				wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'email_verify_failed'], get_permalink()));
				exit;
			} elseif ('omnify_request_data_erasure' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_erasure_nonce'] ?? '')), 'omnify_request_data_erasure')) {
					$omnify_wp_user = wp_get_current_user();
					$omnify_email = sanitize_email($omnify_wp_user->user_email);

					if (function_exists('wp_create_user_request')) {
						wp_create_user_request($omnify_email, 'remove_personal_data');
					}
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_gdpr_erasure_requested', current_time('mysql'));

					$omnify_admin_email = get_option('admin_email');
					$omnify_subject = sprintf(__('[%s] GDPR Data Erasure Request: %s', 'omnifywp-ecommerce'), get_bloginfo('name'), $omnify_email);
					$omnify_message = sprintf(
						__("Customer %s (User ID #%d) has submitted a GDPR Data Erasure request from the customer portal.\n\nPlease review and process it under WordPress Tools > Erase Personal Data.", 'omnifywp-ecommerce'),
						$omnify_email,
						(int) $omnify_wp_user->ID
					);
					wp_mail($omnify_admin_email, $omnify_subject, $omnify_message);

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'erasure_requested'], get_permalink()));
					exit;
				}
			} elseif ('omnify_upload_tax_certificate' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_tax_cert_nonce'] ?? '')), 'omnify_tax_certificate')) {
					$omnify_wp_user = wp_get_current_user();
					$omnify_tax_id  = sanitize_text_field(wp_unslash($_POST['tax_exempt_id'] ?? ''));
					$omnify_org     = sanitize_text_field(wp_unslash($_POST['tax_exempt_org'] ?? ''));

					$omnify_cert_url = '';
					if (isset($_FILES['tax_certificate_file']) && ! empty($_FILES['tax_certificate_file']['name'])) {
						if (! function_exists('wp_handle_upload')) {
							require_once ABSPATH . 'wp-admin/includes/file.php';
						}
						$omnify_upload_file = (array) $_FILES['tax_certificate_file'];
						$omnify_allowed = [
							'pdf'  => 'application/pdf',
							'jpg'  => 'image/jpeg',
							'jpeg' => 'image/jpeg',
							'png'  => 'image/png',
						];
						$omnify_overrides = ['test_form' => false, 'mimes' => $omnify_allowed];
						$omnify_result = wp_handle_upload($omnify_upload_file, $omnify_overrides);
						if (! empty($omnify_result['url'])) {
							$omnify_cert_url = esc_url_raw($omnify_result['url']);
						}
					}

					update_user_meta((int) $omnify_wp_user->ID, '_omnify_tax_exempt_id', $omnify_tax_id);
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_tax_exempt_org', $omnify_org);
					if ($omnify_cert_url) {
						update_user_meta((int) $omnify_wp_user->ID, '_omnify_tax_exempt_cert_url', $omnify_cert_url);
					}
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_tax_exempt_status', 'pending');
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_tax_exempt_submitted_at', current_time('mysql'));

					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'tax_cert_submitted'], get_permalink()));
					exit;
				}
			}
		}

		// 1. Process customer settings form submit if present
		if (is_user_logged_in() && 'omnify_update_profile' === (isset($_POST['omnify_action']) ? sanitize_key(wp_unslash($_POST['omnify_action'])) : '')) {
			if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_profile_nonce'] ?? '')), 'omnify_update_profile')) {
				$omnify_wp_user  = wp_get_current_user();
				$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

				$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
				$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;
				$omnify_db_customer       = $omnify_customer_by_email ?: $omnify_customer_by_uid;

				$omnify_billing_data = [
					'billing_first_name' => isset($_POST['billing_first_name']) ? sanitize_text_field(wp_unslash($_POST['billing_first_name'])) : '',
					'billing_last_name'  => isset($_POST['billing_last_name']) ? sanitize_text_field(wp_unslash($_POST['billing_last_name'])) : '',
					'billing_phone'      => isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '',
					'billing_company'    => isset($_POST['billing_company']) ? sanitize_text_field(wp_unslash($_POST['billing_company'])) : '',
					'billing_address_1'  => isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '',
					'billing_address_2'  => isset($_POST['billing_address_2']) ? sanitize_text_field(wp_unslash($_POST['billing_address_2'])) : '',
					'billing_city'       => isset($_POST['billing_city']) ? sanitize_text_field(wp_unslash($_POST['billing_city'])) : '',
					'billing_state'      => isset($_POST['billing_state']) ? sanitize_text_field(wp_unslash($_POST['billing_state'])) : '',
					'billing_postcode'   => isset($_POST['billing_postcode']) ? sanitize_text_field(wp_unslash($_POST['billing_postcode'])) : '',
					'billing_country'    => strtoupper(substr(isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '', 0, 2)),
				];

				$omnify_shipping_same_as_billing = ! empty($_POST['shipping_same_as_billing']);
				if ($omnify_shipping_same_as_billing) {
					$omnify_shipping_data = [
						'shipping_first_name' => $omnify_billing_data['billing_first_name'],
						'shipping_last_name'  => $omnify_billing_data['billing_last_name'],
						'shipping_phone'      => $omnify_billing_data['billing_phone'],
						'shipping_company'    => $omnify_billing_data['billing_company'],
						'shipping_address_1'  => $omnify_billing_data['billing_address_1'],
						'shipping_address_2'  => $omnify_billing_data['billing_address_2'],
						'shipping_city'       => $omnify_billing_data['billing_city'],
						'shipping_state'      => $omnify_billing_data['billing_state'],
						'shipping_postcode'   => $omnify_billing_data['billing_postcode'],
						'shipping_country'    => $omnify_billing_data['billing_country'],
					];
				} else {
					$omnify_shipping_data = [
						'shipping_first_name' => isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '',
						'shipping_last_name'  => isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '',
						'shipping_phone'      => isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '',
						'shipping_company'    => isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '',
						'shipping_address_1'  => isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '',
						'shipping_address_2'  => isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '',
						'shipping_city'       => isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '',
						'shipping_state'      => isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '',
						'shipping_postcode'   => isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '',
						'shipping_country'    => strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2)),
					];
				}

				$omnify_profile_data = array_merge([
					'first_name' => isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '',
					'last_name'  => isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '',
					'phone'      => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
					'company'    => isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '',
					'country'    => strtoupper(substr(isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '', 0, 2)),
				], $omnify_shipping_data, $omnify_billing_data);

				if ($omnify_db_customer) {
					$omnify_updated = $this->omnify_customers->update((int) $omnify_db_customer['id'], $omnify_profile_data);
					if ($omnify_updated) {
						$omnify_profile_saved = true;
					}
				} else {
					$omnify_new_id = $this->omnify_customers->create(array_merge([
						'user_id' => (int) $omnify_wp_user->ID,
						'email'   => $omnify_wp_email,
						'status'  => 'active',
					], $omnify_profile_data));
					if ($omnify_new_id) {
						$omnify_profile_saved = true;
					}
				}

				if ($omnify_profile_saved) {
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_same_as_billing', $omnify_shipping_same_as_billing ? '1' : '0');
				}

				if ($omnify_profile_saved && $omnify_shipping_same_as_billing && (! empty($omnify_shipping_data['shipping_address_1']) || ! empty($omnify_shipping_data['shipping_city']))) {
					$omnify_addresses = get_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];
					$omnify_billing_shipping_address = [
						'id' => 'billing_default',
						'first_name' => $omnify_shipping_data['shipping_first_name'],
						'last_name' => $omnify_shipping_data['shipping_last_name'],
						'phone' => $omnify_shipping_data['shipping_phone'],
						'company' => $omnify_shipping_data['shipping_company'],
						'address_1' => $omnify_shipping_data['shipping_address_1'],
						'address_2' => $omnify_shipping_data['shipping_address_2'],
						'city' => $omnify_shipping_data['shipping_city'],
						'state' => $omnify_shipping_data['shipping_state'],
						'postcode' => $omnify_shipping_data['shipping_postcode'],
						'country' => $omnify_shipping_data['shipping_country'],
						'is_default' => true,
					];

					$omnify_updated_addresses = [];
					$omnify_found_billing_address = false;
					foreach ($omnify_addresses as $omnify_address) {
						$omnify_address['is_default'] = false;
						if (($omnify_address['id'] ?? '') === 'billing_default') {
							$omnify_updated_addresses[] = $omnify_billing_shipping_address;
							$omnify_found_billing_address = true;
						} else {
							$omnify_updated_addresses[] = $omnify_address;
						}
					}

					if (! $omnify_found_billing_address) {
						array_unshift($omnify_updated_addresses, $omnify_billing_shipping_address);
					}

					update_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_addresses', $omnify_updated_addresses);
				}

				if ($omnify_profile_saved) {
					wp_safe_redirect(add_query_arg(['omnify_tab' => 'settings', 'message' => 'profile_saved'], remove_query_arg(['omnify_action', '_wpnonce'])));
					exit;
				}
			}
		}

		$omnify_customer = null;
		if (is_user_logged_in()) {
			$omnify_wp_user  = wp_get_current_user();
			$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

			$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
			$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;

			if ($omnify_customer_by_email) {
				$omnify_customer = $omnify_customer_by_email;
				// Link WP user ID if not linked or if it points to a different user
				if ((int) $omnify_customer['user_id'] !== (int) $omnify_wp_user->ID) {
					$this->omnify_customers->update((int) $omnify_customer['id'], ['user_id' => (int) $omnify_wp_user->ID]);
					$omnify_customer['user_id'] = $omnify_wp_user->ID;
				}
				// If another customer record was linked to this user_id, de-associate it to maintain user_id uniqueness
				if ($omnify_customer_by_uid && (int) $omnify_customer_by_uid['id'] !== (int) $omnify_customer['id']) {
					$this->omnify_customers->update((int) $omnify_customer_by_uid['id'], ['user_id' => null]);
				}
			} elseif ($omnify_customer_by_uid) {
				$omnify_customer = $omnify_customer_by_uid;
				// If the customer record email doesn't match the WP email, update it
				if ($omnify_wp_email && strtolower((string) $omnify_customer['email']) !== strtolower($omnify_wp_email)) {
					$this->omnify_customers->update((int) $omnify_customer['id'], ['email' => $omnify_wp_email]);
					$omnify_customer['email'] = $omnify_wp_email;
				}
			} else {
				// Mock one from WP user info (no database insert yet)
				$omnify_customer = [
					'id'                  => 0,
					'user_id'             => $omnify_wp_user->ID,
					'email'               => $omnify_wp_email,
					'first_name'          => $omnify_wp_user->first_name ?: $omnify_wp_user->display_name,
					'last_name'           => $omnify_wp_user->last_name,
					'name'                => $omnify_wp_user->display_name,
					'company'             => '',
					'country'             => '',
					'phone'               => '',
					'status'              => 'active',
					'created_at'          => '',
					'shipping_first_name' => '',
					'shipping_last_name'  => '',
					'shipping_phone'      => '',
					'shipping_company'    => '',
					'shipping_address_1'  => '',
					'shipping_address_2'  => '',
					'shipping_city'       => '',
					'shipping_state'      => '',
					'shipping_postcode'   => '',
					'shipping_country'    => '',
					'billing_first_name'  => '',
					'billing_last_name'   => '',
					'billing_phone'       => '',
					'billing_company'     => '',
					'billing_address_1'   => '',
					'billing_address_2'   => '',
					'billing_city'        => '',
					'billing_state'       => '',
					'billing_postcode'    => '',
					'billing_country'     => '',
				];
			}
		}

		$omnify_products_accessed = [];
		$omnify_files             = [];
		$omnify_orders            = [];
		$omnify_download_history  = [];

		$omnify_customer_ids = [];
		if ($omnify_customer && $omnify_customer['id'] > 0) {
			$omnify_customer_ids[] = (int) $omnify_customer['id'];
		}
		if (is_user_logged_in()) {
			$omnify_wp_user  = wp_get_current_user();
			$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

			global $wpdb;
			$omnify_customers_table = $wpdb->prefix . 'omnify_customers';

			$omnify_cust_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id FROM {$omnify_customers_table} WHERE user_id = %d OR email = %s",
				(int) $omnify_wp_user->ID,
				$omnify_wp_email
			), ARRAY_A);

			if (is_array($omnify_cust_rows)) {
				foreach ($omnify_cust_rows as $omnify_row) {
					$omnify_customer_ids[] = (int) $omnify_row['id'];
				}
			}
		}
		$omnify_customer_ids = array_unique(array_filter($omnify_customer_ids));

		if (! empty($omnify_customer_ids)) {
			// Get orders history
			$omnify_orders = $this->omnify_orders->all(['customer_id' => $omnify_customer_ids, 'per_page' => 100]);

			// Collect all unique product IDs from access grants and orders for batched lookup
			$omnify_candidate_prod_ids = [];
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_access->for_customer($omnify_c_id) as $omnify_item) {
					if ('active' === $omnify_item['status'] && ! $omnify_item['is_expired']) {
						$omnify_candidate_prod_ids[] = (int) $omnify_item['product_id'];
					}
				}
			}
			foreach ($omnify_orders as $omnify_order) {
				if (! in_array($omnify_order['status'], ['cancelled', 'failed'], true)) {
					foreach ((array) ($omnify_order['items'] ?? []) as $omnify_item) {
						$omnify_candidate_prod_ids[] = (int) ($omnify_item['product_id'] ?? 0);
					}
				}
			}
			$omnify_candidate_prod_ids = array_values(array_unique(array_filter($omnify_candidate_prod_ids)));
			$omnify_loaded_products    = ! empty($omnify_candidate_prod_ids) ? $this->omnify_products->find_many($omnify_candidate_prod_ids) : [];

			// Build deduplicated active products accessed
			$omnify_unique_products = [];
			foreach ($omnify_candidate_prod_ids as $omnify_pid) {
				if (isset($omnify_loaded_products[$omnify_pid]) && 'trash' !== ($omnify_loaded_products[$omnify_pid]['status'] ?? '')) {
					$omnify_unique_products[$omnify_pid] = $omnify_loaded_products[$omnify_pid];
				}
			}
			$omnify_products_accessed = array_values($omnify_unique_products);

			// Get active downloads files
			$omnify_portal_ttl = (int) apply_filters('omnify_download_url_ttl', DAY_IN_SECONDS, 'portal');
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_access->files_for_customer($omnify_c_id) as $omnify_file) {
					$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], $omnify_portal_ttl, $omnify_c_id);
					$omnify_files[] = $omnify_file;
				}
			}
			// Deduplicate files by id
			$omnify_unique_files = [];
			foreach ($omnify_files as $omnify_f) {
				if ($omnify_f) {
					$omnify_unique_files[$omnify_f['id']] = $omnify_f;
				}
			}
			$omnify_files = array_values($omnify_unique_files);

			// Get downloads history logs
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_downloads->all_for_customer($omnify_c_id, 30) as $omnify_log) {
					$omnify_download_history[] = $omnify_log;
				}
			}
			// Sort download_history by downloaded_at DESC and slice to 30
			usort($omnify_download_history, fn($omnify_a, $omnify_b) => strcmp($omnify_b['downloaded_at'], $omnify_a['downloaded_at']));
			$omnify_download_history = array_slice($omnify_download_history, 0, 30);
		}

		$omnify_wishlist_products = [];
		$omnify_wishlist_ids = [];
		if (is_user_logged_in()) {
			$omnify_wishlist_ids = $this->omnify_wishlists->product_ids_for_user(get_current_user_id());
		} elseif (! empty($_COOKIE['omnify_wishlist_ids'])) {
			$omnify_wishlist_ids = array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_wishlist_ids'])))));
		}

		foreach ($omnify_wishlist_ids as $omnify_wishlist_product_id) {
			$omnify_wishlist_product = $this->omnify_products->find($omnify_wishlist_product_id);
			if ($omnify_wishlist_product && 'published' === ($omnify_wishlist_product['status'] ?? '')) {
				$omnify_wishlist_products[] = $omnify_wishlist_product;
			}
		}

		ob_start();
		include \omnify_locate_template('storefront/portal.php');
		return (string) ob_get_clean();
	}

	/**
	 * Sync guest cookie wishlist items to user account upon logging in.
	 */
	public function sync_guest_wishlist_on_login(string $user_login, \WP_User $user): void {
		if (empty($_COOKIE['omnify_wishlist_ids'])) {
			return;
		}

		$omnify_ids = array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_wishlist_ids'])))));
		foreach ($omnify_ids as $omnify_id) {
			$this->omnify_wishlists->add((int) $user->ID, $omnify_id);
		}

		if (! headers_sent()) {
			$omnify_cookie_options = [
				'expires'  => time() - 3600,
				'path'     => COOKIEPATH ?: '/',
				'domain'   => COOKIE_DOMAIN ?: '',
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			];
			setcookie('omnify_wishlist_ids', '', $omnify_cookie_options);
			if ((COOKIEPATH ?: '/') !== '/') {
				$omnify_cookie_options['path'] = '/';
				setcookie('omnify_wishlist_ids', '', $omnify_cookie_options);
			}
		}
	}

	/**
	 * @var array<int, array<string, mixed>|null>
	 */
	private static array $refund_product_cache = [];

	public function order_is_refund_eligible(array $omnify_order, array $omnify_settings): bool {
		if (empty($omnify_settings['enable_refunds']) || ! in_array($omnify_order['status'] ?? '', ['completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered'], true)) {
			return false;
		}

		$omnify_window_days = max(1, absint($omnify_settings['refund_duration'] ?? 14));
		foreach (($omnify_order['items'] ?? []) as $omnify_item) {
			$omnify_item_pid = absint($omnify_item['product_id'] ?? 0);
			if (! array_key_exists($omnify_item_pid, self::$refund_product_cache)) {
				self::$refund_product_cache[$omnify_item_pid] = $this->omnify_products->find($omnify_item_pid);
			}
			$omnify_product = self::$refund_product_cache[$omnify_item_pid];
			if (! $omnify_product || empty($omnify_product['refund_enabled'])) {
				return false;
			}
			if (! empty($omnify_product['refund_window_days'])) {
				$omnify_window_days = min($omnify_window_days, max(1, absint($omnify_product['refund_window_days'])));
			}
		}

		return time() <= strtotime((string) $omnify_order['created_at'] . ' UTC') + ($omnify_window_days * DAY_IN_SECONDS);
	}

	private function cancel_order_payment_by_storefront(int $omnify_order_id, string $omnify_gateway, string $omnify_reason): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order || ! in_array($omnify_order['status'], ['pending', 'pending_payment'], true)) {
			return;
		}

		$this->omnify_orders->update_status($omnify_order_id, 'cancelled');

		$this->omnify_orders->add_note(
			$omnify_order_id,
			// translators: 1: gateway name, 2: cancellation reason
			sprintf(__('Order cancelled via %1$s (%2$s). Stock and coupon usage restored.', 'omnifywp-ecommerce'), $omnify_gateway, $omnify_reason)
		);

		if (! empty($omnify_order['customer_id'])) {
			$this->omnify_customers->record_activity(
				(int) $omnify_order['customer_id'],
				'order_cancelled',
				sprintf(__('Order #%s cancelled via %s.', 'omnifywp-ecommerce'), (string) ($omnify_order['order_number'] ?: $omnify_order_id), $omnify_gateway),
				['order_id' => $omnify_order_id, 'reason' => $omnify_reason, 'gateway' => $omnify_gateway]
			);
		}
	}

	public function complete_order_payment_by_storefront(int $omnify_order_id, string $omnify_gateway, ?string $omnify_transaction_id = null): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order || 'completed' === $omnify_order['status']) {
			return;
		}

		$omnify_update_data = [
			'status' => 'completed',
		];
		if (! empty($omnify_transaction_id) && empty($omnify_order['transaction_id'])) {
			$omnify_update_data['transaction_id'] = sanitize_text_field($omnify_transaction_id);
		}
		$this->omnify_orders->update($omnify_order_id, $omnify_update_data);
		$this->omnify_orders->update_status($omnify_order_id, 'completed');

		$this->omnify_orders->add_note(
			$omnify_order_id,
			sprintf(
				/* translators: 1: gateway name, 2: transaction id */
				__('Payment completed via %1$s (Transaction: %2$s).', 'omnifywp-ecommerce'),
				esc_html($omnify_gateway),
				esc_html($omnify_transaction_id ?: 'N/A')
			)
		);

		if (! empty($omnify_order['customer_id'])) {
			$this->omnify_customers->record_activity(
				(int) $omnify_order['customer_id'],
				'order_paid',
				sprintf(__('Order #%s paid via %s.', 'omnifywp-ecommerce'), (string) ($omnify_order['order_number'] ?: $omnify_order_id), $omnify_gateway),
				['order_id' => $omnify_order_id, 'gateway' => $omnify_gateway, 'transaction_id' => $omnify_transaction_id]
			);

			foreach ((array) ($omnify_order['items'] ?? []) as $omnify_item) {
				$this->omnify_access->grant((int) $omnify_order['customer_id'], (int) $omnify_item['product_id'], $omnify_order_id);
				$omnify_p = $this->omnify_products->find((int) $omnify_item['product_id']);
				if ($omnify_p && ! empty($omnify_p['bundled_products']) && is_array($omnify_p['bundled_products'])) {
					foreach ($omnify_p['bundled_products'] as $omnify_bpid) {
						$this->omnify_access->grant((int) $omnify_order['customer_id'], (int) $omnify_bpid, $omnify_order_id);
					}
				}
			}

			if ($this->omnify_abandoned_carts && ! empty($omnify_order['customer_email'])) {
				$this->omnify_abandoned_carts->mark_recovered_by_email((string) $omnify_order['customer_email'], $omnify_order_id);
			}
		}

		do_action('omnify_order_completed', $omnify_order_id, $omnify_gateway, $omnify_transaction_id);
		if (isset($this->omnify_emails) && method_exists($this->omnify_emails, 'send_receipt')) {
			$this->omnify_emails->send_receipt($omnify_order_id);
		}
	}

	/**
	 * Render public guest order tracking and invoice lookup form & results.
	 */
	public function render_order_tracking(array|string $omnify_attributes = []): string {
		$this->enqueue_assets();
		$omnify_atts         = is_array($omnify_attributes) ? $omnify_attributes : [];
		$omnify_tracking_max = absint($omnify_atts['maxWidth'] ?? 0);
		if ($omnify_tracking_max <= 0) {
			$omnify_tracking_max = 680;
		}
		$omnify_tracking_title = isset($omnify_atts['title']) && '' !== trim((string) $omnify_atts['title'])
			? sanitize_text_field((string) $omnify_atts['title'])
			: __('Track Your Order', 'omnifywp-ecommerce');
		$omnify_tracking_subtitle = isset($omnify_atts['subtitle']) && '' !== trim((string) $omnify_atts['subtitle'])
			? sanitize_text_field((string) $omnify_atts['subtitle'])
			: __('Enter your order number and billing email to view current status and details.', 'omnifywp-ecommerce');
		$omnify_show_support = ! isset($omnify_atts['showSupportButton']) || ! empty($omnify_atts['showSupportButton']);
		$omnify_support_email = isset($omnify_atts['supportEmail']) && '' !== trim((string) $omnify_atts['supportEmail'])
			? sanitize_email((string) $omnify_atts['supportEmail'])
			: (get_option('admin_email') ?: '');

		$omnify_order_number = isset($_REQUEST['order_number']) ? sanitize_text_field(wp_unslash($_REQUEST['order_number'])) : '';
		$omnify_order_email  = isset($_REQUEST['order_email']) ? sanitize_email(wp_unslash($_REQUEST['order_email'])) : '';

		$omnify_found_order = null;
		$omnify_error_msg   = '';

		if ('' !== $omnify_order_number && '' !== $omnify_order_email) {
			global $wpdb;
			$omnify_table = $wpdb->prefix . 'omnify_orders';
			$omnify_num_clean = ltrim($omnify_order_number, '#');

			$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row(
				$wpdb,
				$wpdb->prepare(
					"SELECT id FROM {$omnify_table} WHERE (order_number = %s OR id = %d) LIMIT 1",
					$omnify_order_number,
					absint($omnify_num_clean)
				),
				ARRAY_A
			);

			if ($omnify_row) {
				$omnify_candidate = $this->omnify_orders->find((int) $omnify_row['id']);
				$omnify_stored_email = strtolower(trim((string) ($omnify_candidate['customer_email'] ?? $omnify_candidate['email'] ?? $omnify_candidate['billing_email'] ?? ($omnify_candidate['customer']['email'] ?? ''))));
				if ($omnify_candidate && $omnify_stored_email === strtolower(trim($omnify_order_email))) {
					$omnify_found_order = $omnify_candidate;
				} else {
					$omnify_error_msg = __('No matching order found for this email address.', 'omnifywp-ecommerce');
				}
			} else {
				$omnify_error_msg = __('Order not found. Please verify the order number.', 'omnifywp-ecommerce');
			}
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_currency = (string) ($omnify_settings['currency'] ?? 'USD');
		$omnify_format_price = function(float $omnify_val) use ($omnify_currency): string {
			return $omnify_currency . ' ' . number_format($omnify_val, 2);
		};

		ob_start();
		?>
		<div class="omnify-storefront-wrapper omnify-order-tracking-wrapper" style="max-width: <?php echo esc_attr((string) $omnify_tracking_max); ?>px; margin: 0 auto; padding: 24px;">
			<div class="omnify-portal-section-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
				<div style="margin-bottom: 20px;">
					<h3 style="margin:0 0 8px 0; font-size:20px; font-weight:700; color:#0f172a;"><?php echo esc_html($omnify_tracking_title); ?></h3>
					<p style="margin:0; font-size:14px; color:#64748b;"><?php echo esc_html($omnify_tracking_subtitle); ?></p>
				</div>

				<?php if ($omnify_error_msg) : ?>
					<div class="omnify-portal-notice omnify-portal-notice--error" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:14px;">
						<?php echo esc_html($omnify_error_msg); ?>
					</div>
				<?php endif; ?>

				<form method="get" class="omnify-tracking-form" style="display:flex; flex-direction:column; gap:14px;">
					<div class="omnify-portal-form-group" style="display:flex; flex-direction:column; gap:6px;">
						<label style="font-size:13px; font-weight:600; color:#334155;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></label>
						<input type="text" name="order_number" value="<?php echo esc_attr($omnify_order_number); ?>" placeholder="e.g. #1042 or ORD-2026-1042" required style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" />
					</div>
					<div class="omnify-portal-form-group" style="display:flex; flex-direction:column; gap:6px;">
						<label style="font-size:13px; font-weight:600; color:#334155;"><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?></label>
						<input type="email" name="order_email" value="<?php echo esc_attr($omnify_order_email); ?>" placeholder="e.g. you@example.com" required style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" />
					</div>
					<button type="submit" class="omnify-btn omnify-btn--primary omnify-track-submit-btn" style="background:#0f766e; color:#fff; border:none; min-height:44px; padding:0 28px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; margin-top:6px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
						<span class="dashicons dashicons-search" style="font-size:16px; width:16px; height:16px; line-height:1; vertical-align:middle;"></span>
						<span><?php esc_html_e('Track Order', 'omnifywp-ecommerce'); ?></span>
					</button>
				</form>
			</div>

			<?php if ($omnify_found_order) : ?>
				<?php
				$omnify_ord_num = (string) ($omnify_found_order['order_number'] ?: ('#' . $omnify_found_order['id']));
				$omnify_status = (string) ($omnify_found_order['status'] ?? 'pending');
				$omnify_fulfill = (string) ($omnify_found_order['fulfillment_status'] ?? 'none');
				$omnify_track_no = (string) ($omnify_found_order['tracking_number'] ?? '');
				$omnify_carrier = (string) ($omnify_found_order['tracking_carrier'] ?? '');
				?>
				<div class="omnify-portal-section-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-top:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
					<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:16px;">
						<div>
							<h4 style="margin:0 0 4px 0; font-size:18px; font-weight:700; color:#0f172a;">Order <?php echo esc_html($omnify_ord_num); ?></h4>
							<span style="font-size:13px; color:#64748b;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime((string) $omnify_found_order['created_at']))); ?></span>
						</div>
						<div style="display:flex; gap:8px;">
							<span class="omnify-status-pill" style="display:inline-block; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; text-transform:capitalize; background:#e0f2fe; color:#0369a1;">
								<?php echo esc_html(str_replace('_', ' ', $omnify_status)); ?>
							</span>
							<?php if ($omnify_fulfill && 'none' !== $omnify_fulfill) : ?>
								<span class="omnify-status-pill" style="display:inline-block; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; text-transform:capitalize; background:#dcfce7; color:#15803d;">
									<?php echo esc_html(str_replace('_', ' ', $omnify_fulfill)); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>

					<?php if ($omnify_track_no) : ?>
						<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#334155;">
							<strong><?php esc_html_e('Shipping Tracking:', 'omnifywp-ecommerce'); ?></strong>
							<?php if ($omnify_carrier) : ?><?php echo esc_html($omnify_carrier); ?> — <?php endif; ?>
							<code><?php echo esc_html($omnify_track_no); ?></code>
						</div>
					<?php endif; ?>

					<?php
					$omnify_can_download = 'completed' === $omnify_found_order['status'];
					$omnify_tracking_downloads = [];
					if ($omnify_can_download && ! empty($omnify_found_order['customer_id'])) {
						$omnify_cid = (int) $omnify_found_order['customer_id'];
						foreach (($omnify_found_order['items'] ?? []) as $omnify_item) {
							$omnify_pid = (int) ($omnify_item['product_id'] ?? 0);
							if ($omnify_pid > 0 && $this->omnify_access->has_access($omnify_cid, $omnify_pid)) {
								$omnify_product = $this->omnify_products->find($omnify_pid);
								$omnify_target_pids = [$omnify_pid];
								if ($omnify_product && 'bundle' === ($omnify_product['type'] ?? '')) {
									$omnify_bundled = ! empty($omnify_product['bundled_ids']) && is_array($omnify_product['bundled_ids']) ? $omnify_product['bundled_ids'] : [];
									if (! empty($omnify_bundled)) {
										$omnify_target_pids = array_merge($omnify_target_pids, array_map('absint', $omnify_bundled));
									}
								}
								foreach (array_unique($omnify_target_pids) as $omnify_t_pid) {
									$omnify_files = $this->omnify_files->active_for_product($omnify_t_pid);
									foreach ($omnify_files as $omnify_f) {
										$omnify_f['product_name'] = $omnify_item['product_name'];
										$omnify_f['download_url'] = $this->omnify_signed_urls->create((int) $omnify_f['id'], 3600 * 24 * 7, $omnify_cid);
										$omnify_tracking_downloads[] = $omnify_f;
									}
								}
							}
						}
					}
					if (! empty($omnify_tracking_downloads)) :
					?>
						<div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:14px 16px; margin-bottom:16px;">
							<strong style="display:block; margin-bottom:8px; font-size:14px; color:#0f172a;"><?php esc_html_e('Your Digital Downloads', 'omnifywp-ecommerce'); ?></strong>
							<div style="display:flex; flex-direction:column; gap:8px;">
								<?php foreach ($omnify_tracking_downloads as $omnify_td) : ?>
									<div style="display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px;">
										<div>
											<strong style="font-size:13px; color:#1e293b;"><?php echo esc_html($omnify_td['product_name']); ?></strong>
											<span style="font-size:12px; color:#64748b; margin-left:6px;"><?php echo esc_html($omnify_td['file_name']); ?> (v<?php echo esc_html($omnify_td['version']); ?>)</span>
										</div>
										<a href="<?php echo esc_url($omnify_td['download_url']); ?>" class="omnify-btn omnify-btn--primary omnify-btn--sm" style="padding:4px 12px; font-size:12px; border-radius:5px; text-decoration:none; background:var(--omnify-primary, #0f766e); color:#fff; font-weight:600;">
											<?php esc_html_e('Download', 'omnifywp-ecommerce'); ?>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<table style="width:100%; border-collapse:collapse; font-size:14px; margin-bottom:16px;">
						<thead>
							<tr style="border-bottom:1px solid #e2e8f0; color:#64748b; text-align:left;">
								<th style="padding:8px 0;"><?php esc_html_e('Item', 'omnifywp-ecommerce'); ?></th>
								<th style="padding:8px 0; text-align:center;"><?php esc_html_e('Qty', 'omnifywp-ecommerce'); ?></th>
								<th style="padding:8px 0; text-align:right;"><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (($omnify_found_order['items'] ?? []) as $omnify_item) : ?>
								<tr style="border-bottom:1px solid #f1f5f9;">
									<td style="padding:10px 0; font-weight:500; color:#1e293b;">
										<div><?php echo esc_html($omnify_item['product_name']); ?></div>
										<?php
										if (! empty($omnify_item['item_meta'])) :
											$omnify_item_meta = is_string($omnify_item['item_meta']) ? json_decode($omnify_item['item_meta'], true) : $omnify_item['item_meta'];
											if (is_array($omnify_item_meta) && ! empty($omnify_item_meta)) :
												$omnify_meta_parts = [];
												foreach ($omnify_item_meta as $omnify_mk => $omnify_mv) {
													if (is_scalar($omnify_mv) && '' !== (string) $omnify_mv) {
														$omnify_meta_parts[] = esc_html(ucfirst(str_replace(['_', '-'], ' ', (string) $omnify_mk))) . ': ' . esc_html((string) $omnify_mv);
													}
												}
												if (! empty($omnify_meta_parts)) : ?>
													<div style="font-size: 11px; font-weight: normal; color: #64748b; margin-top: 2px;">
														<?php echo implode(' &bull; ', $omnify_meta_parts); ?>
													</div>
												<?php endif;
											endif;
										endif; ?>
									</td>
									<td style="padding:10px 0; text-align:center; color:#64748b;"><?php echo esc_html($omnify_item['quantity']); ?></td>
									<td style="padding:10px 0; text-align:right; font-weight:600; color:#0f172a;"><?php echo esc_html($omnify_format_price((float) $omnify_item['price'] * (int) $omnify_item['quantity'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:16px; margin-top:8px;">
						<?php
						$omnify_ord_email = strtolower((string) ($omnify_found_order['customer_email'] ?? ''));
						$omnify_guest_token = hash_hmac('sha256', (int) $omnify_found_order['id'] . '|' . $omnify_ord_email, wp_salt('nonce'));
						$omnify_invoice_url = add_query_arg([
							'action'        => 'omnify_order_document',
							'order_id'      => (int) $omnify_found_order['id'],
							'document_type' => 'invoice',
							'guest_token'   => $omnify_guest_token,
						], admin_url('admin-post.php'));
						?>
						<a href="<?php echo esc_url($omnify_invoice_url); ?>" class="omnify-btn omnify-btn--secondary omnify-btn--sm" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600;" target="_blank">
							<?php esc_html_e('Download Invoice (PDF/HTML)', 'omnifywp-ecommerce'); ?>
						</a>
						<div style="font-size:16px; font-weight:700; color:#0f172a;">
							<span><?php esc_html_e('Total:', 'omnifywp-ecommerce'); ?>&nbsp;</span>
							<span><?php echo esc_html($omnify_format_price((float) ($omnify_found_order['total'] ?? 0))); ?></span>
						</div>
					</div>
					<?php if ($omnify_show_support && ! empty($omnify_support_email)) : ?>
						<div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b;">
							<span><?php esc_html_e('Have questions regarding this order?', 'omnifywp-ecommerce'); ?></span>
							<a href="mailto:<?php echo esc_attr($omnify_support_email); ?>?subject=<?php echo esc_attr(rawurlencode('Help with Order ' . $omnify_ord_num)); ?>" style="color: var(--omnify-primary, #0f766e); font-weight: 600; text-decoration: underline;">
								<?php esc_html_e('Contact Support', 'omnifywp-ecommerce'); ?> &rarr;
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
