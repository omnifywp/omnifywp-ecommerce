<?php
/**
 * Global settings search index.
 *
 * A flat, searchable catalogue of settings across every settings screen. Each
 * settings hub renders as a separate admin page, so this index is what powers
 * the cross-section search suggestions in the admin settings search bar.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Settings_Search_Index {

	/**
	 * Settings section slug => human label.
	 *
	 * @return array<string, string>
	 */
	public static function sections(): array {
		return [
			'general'   => __('General Settings', 'omnifywp-ecommerce'),
			'pages'     => __('Core Pages', 'omnifywp-ecommerce'),
			'roles'     => __('Shop Roles', 'omnifywp-ecommerce'),
			'payments'  => __('Payment Methods', 'omnifywp-ecommerce'),
			'tax'       => __('Tax Rules', 'omnifywp-ecommerce'),
			'delivery'  => __('Delivery Zones', 'omnifywp-ecommerce'),
			'checkout'  => __('Checkout Flow', 'omnifywp-ecommerce'),
			'fraud'     => __('Fraud & Risk', 'omnifywp-ecommerce'),
			'marketing' => __('Marketing & Tracking', 'omnifywp-ecommerce'),
			'design'    => __('Storefront Design', 'omnifywp-ecommerce'),
			'email'     => __('Email Templates', 'omnifywp-ecommerce'),
			'tools'     => __('Tools & Maintenance', 'omnifywp-ecommerce'),
		];
	}

	/**
	 * Flat list of searchable settings.
	 *
	 * Each entry is [ label, section slug, extra keywords ].
	 *
	 * @return array<int, array{0: string, 1: string, 2: string}>
	 */
	private static function entries(): array {
		return [
			// ── General ──────────────────────────────────────────────────
			[__('Store Name', 'omnifywp-ecommerce'), 'general', 'identity business shop title'],
			[__('Store Email Address', 'omnifywp-ecommerce'), 'general', 'sender reply to receipt email'],
			[__('Storefront URL', 'omnifywp-ecommerce'), 'general', 'shop catalog link url'],
			[__('Base Country / Region', 'omnifywp-ecommerce'), 'general', 'default country location region'],
			[__('Selling Locations', 'omnifywp-ecommerce'), 'general', 'countries regions sell'],
			[__('Specific Selling Countries', 'omnifywp-ecommerce'), 'general', 'allow countries'],
			[__('Excluded Selling Countries', 'omnifywp-ecommerce'), 'general', 'block exclude countries'],
			[__('Store Currency', 'omnifywp-ecommerce'), 'general', 'money default currency'],
			[__('Currency Symbol Position', 'omnifywp-ecommerce'), 'general', 'price format symbol'],
			[__('Number of Decimals', 'omnifywp-ecommerce'), 'general', 'price precision decimals'],
			[__('Thousand Separator', 'omnifywp-ecommerce'), 'general', 'price format'],
			[__('Decimal Separator', 'omnifywp-ecommerce'), 'general', 'price format'],
			[__('Enable Test Simulation Mode', 'omnifywp-ecommerce'), 'general', 'sandbox test debug mock'],
			[__('Attach Mini Cart to Navigation Menu', 'omnifywp-ecommerce'), 'general', 'cart menu header badge'],
			[__('Theme Menu Location', 'omnifywp-ecommerce'), 'general', 'menu location nav'],
			[__('Default Store Tax Rate', 'omnifywp-ecommerce'), 'general', 'vat gst tax percent'],
			[__('Default Tax Label', 'omnifywp-ecommerce'), 'general', 'tax name vat gst'],
			[__('Tax Rounding Precision', 'omnifywp-ecommerce'), 'general', 'rounding tax line subtotal'],
			[__('Prices Include Tax', 'omnifywp-ecommerce'), 'general', 'inclusive vat'],
			[__('Charge Tax on Delivery', 'omnifywp-ecommerce'), 'general', 'shipping tax delivery'],
			[__('Tax Reporting Codes', 'omnifywp-ecommerce'), 'general', 'reporting code accounting'],

			// ── Core Pages ───────────────────────────────────────────────
			[__('Storefront Page', 'omnifywp-ecommerce'), 'pages', 'catalog shop shortcode page'],
			[__('Cart Page', 'omnifywp-ecommerce'), 'pages', 'shopping cart shortcode page'],
			[__('Checkout Page', 'omnifywp-ecommerce'), 'pages', 'payment shortcode page'],
			[__('Customer Portal', 'omnifywp-ecommerce'), 'pages', 'account my account shortcode'],
			[__('Secure Download Page', 'omnifywp-ecommerce'), 'pages', 'downloads shortcode page'],
			[__('Order Tracking Page', 'omnifywp-ecommerce'), 'pages', 'tracking shortcode page'],
			[__('Automated Page Setup', 'omnifywp-ecommerce'), 'pages', 'generate pages shortcodes'],

			// ── Shop Roles ───────────────────────────────────────────────
			[__('Minimum Access Capability', 'omnifywp-ecommerce'), 'roles', 'permission admin capability role'],
			[__('Feature Permissions Matrix', 'omnifywp-ecommerce'), 'roles', 'role permissions manager clerk access'],
			[__('Shop Staff & User Directory', 'omnifywp-ecommerce'), 'roles', 'staff users add user directory'],

			// ── Payment Methods ──────────────────────────────────────────
			[__('Payment Gateways & Checkout Methods', 'omnifywp-ecommerce'), 'payments', 'gateways providers payment methods'],
			[__('Stripe', 'omnifywp-ecommerce'), 'payments', 'card credit card stripe'],
			[__('PayPal', 'omnifywp-ecommerce'), 'payments', 'paypal wallet express'],
			[__('Razorpay', 'omnifywp-ecommerce'), 'payments', 'razorpay india upi'],
			[__('Mollie', 'omnifywp-ecommerce'), 'payments', 'mollie ideal'],
			[__('Paddle', 'omnifywp-ecommerce'), 'payments', 'paddle'],
			[__('Paystack', 'omnifywp-ecommerce'), 'payments', 'paystack africa'],
			[__('Tap Payments', 'omnifywp-ecommerce'), 'payments', 'tap'],
			[__('Alipay', 'omnifywp-ecommerce'), 'payments', 'alipay china'],
			[__('WeChat Pay', 'omnifywp-ecommerce'), 'payments', 'wechat china'],
			[__('SSLCommerz', 'omnifywp-ecommerce'), 'payments', 'sslcommerz bangladesh'],
			[__('Khalti', 'omnifywp-ecommerce'), 'payments', 'khalti nepal'],
			[__('eSewa', 'omnifywp-ecommerce'), 'payments', 'esewa nepal'],
			[__('Bank Transfer (BACS)', 'omnifywp-ecommerce'), 'payments', 'bank transfer offline bacs'],
			[__('Cheque Payment', 'omnifywp-ecommerce'), 'payments', 'cheque check offline'],
			[__('Cash on Delivery', 'omnifywp-ecommerce'), 'payments', 'cod cash offline'],
			[__('Custom / Manual Payment', 'omnifywp-ecommerce'), 'payments', 'manual custom offline'],
			[__('Payment Mode (Live vs Sandbox)', 'omnifywp-ecommerce'), 'payments', 'live test sandbox mode'],
			[__('API Key / Secret Key', 'omnifywp-ecommerce'), 'payments', 'credentials api key secret token'],
			[__('Webhook URL', 'omnifywp-ecommerce'), 'payments', 'webhook callback endpoint'],

			// ── Tax Rules ────────────────────────────────────────────────
			[__('Tax Calculation & Store Defaults', 'omnifywp-ecommerce'), 'tax', 'tax defaults rate'],
			[__('Regional Tax Rules & Rates', 'omnifywp-ecommerce'), 'tax', 'country state tax rules regional'],
			[__('Add Tax Rule', 'omnifywp-ecommerce'), 'tax', 'new tax rule'],
			[__('Tax Rule Country Scope', 'omnifywp-ecommerce'), 'tax', 'tax country scope'],
			[__('Tax Rule State / Province', 'omnifywp-ecommerce'), 'tax', 'tax state province'],
			[__('Tax Rule Rate %', 'omnifywp-ecommerce'), 'tax', 'tax rate percent'],
			[__('Tax Rule Reporting Code', 'omnifywp-ecommerce'), 'tax', 'tax reporting code'],
			[__('Tax Rule Priority', 'omnifywp-ecommerce'), 'tax', 'tax priority order'],

			// ── Delivery Zones ───────────────────────────────────────────
			[__('Delivery & Shipping Zones', 'omnifywp-ecommerce'), 'delivery', 'shipping zones delivery rates'],
			[__('Add Shipping Zone', 'omnifywp-ecommerce'), 'delivery', 'new shipping zone'],
			[__('Zone Name', 'omnifywp-ecommerce'), 'delivery', 'shipping zone name'],
			[__('Match Priority', 'omnifywp-ecommerce'), 'delivery', 'zone priority order'],
			[__('Country / Region Scope', 'omnifywp-ecommerce'), 'delivery', 'zone country region'],
			[__('State / Province Scope', 'omnifywp-ecommerce'), 'delivery', 'zone state province'],
			[__('Method Title', 'omnifywp-ecommerce'), 'delivery', 'shipping method title'],
			[__('Method Type', 'omnifywp-ecommerce'), 'delivery', 'flat rate free shipping local pickup'],
			[__('Shipping Cost', 'omnifywp-ecommerce'), 'delivery', 'shipping rate cost fee'],
			[__('Free Shipping Order Minimum', 'omnifywp-ecommerce'), 'delivery', 'free shipping threshold minimum'],

			// ── Checkout Flow ────────────────────────────────────────────
			[__('Allow Guest Checkout', 'omnifywp-ecommerce'), 'checkout', 'guest checkout account'],
			[__('Require Account', 'omnifywp-ecommerce'), 'checkout', 'account required login'],
			[__('Account Creation Mode', 'omnifywp-ecommerce'), 'checkout', 'register account creation'],
			[__('Sign In Customer After Creation', 'omnifywp-ecommerce'), 'checkout', 'auto login account'],
			[__('Reduce Stock on Checkout', 'omnifywp-ecommerce'), 'checkout', 'inventory stock reduce'],
			[__('Require Phone Number', 'omnifywp-ecommerce'), 'checkout', 'phone required field'],
			[__('Hold Stock for Unpaid Orders', 'omnifywp-ecommerce'), 'checkout', 'hold stock minutes pending expire'],
			[__('Default Payment Method', 'omnifywp-ecommerce'), 'checkout', 'payment default method'],
			[__('Low Stock Alert Threshold', 'omnifywp-ecommerce'), 'checkout', 'low stock alert threshold'],
			[__('Order Number Prefix', 'omnifywp-ecommerce'), 'checkout', 'order number prefix'],
			[__('Order Number Padding', 'omnifywp-ecommerce'), 'checkout', 'order number digits padding'],
			[__('Order Number Suffix', 'omnifywp-ecommerce'), 'checkout', 'order number suffix'],
			[__('Enable Coupons & Promo Codes', 'omnifywp-ecommerce'), 'checkout', 'coupon discount promo code'],
			[__('Checkout Order Bump Offer', 'omnifywp-ecommerce'), 'checkout', 'order bump upsell impulse'],
			[__('Order Bump Title', 'omnifywp-ecommerce'), 'checkout', 'order bump title'],
			[__('Order Bump Price', 'omnifywp-ecommerce'), 'checkout', 'order bump price'],
			[__('Order Bump Description', 'omnifywp-ecommerce'), 'checkout', 'order bump description'],
			[__('Trust Badges Heading', 'omnifywp-ecommerce'), 'checkout', 'trust badges secure checkout'],
			[__('Abandoned Cart Recovery', 'omnifywp-ecommerce'), 'checkout', 'abandoned cart recovery email'],
			[__('Send Reminder After', 'omnifywp-ecommerce'), 'checkout', 'abandoned cart delay minutes'],
			[__('Recovery Link Expiry', 'omnifywp-ecommerce'), 'checkout', 'abandoned cart expiry days'],
			[__('Maximum Reminders', 'omnifywp-ecommerce'), 'checkout', 'abandoned cart reminders count'],
			[__('Incentive Coupon Code', 'omnifywp-ecommerce'), 'checkout', 'abandoned cart coupon incentive'],
			[__('Download Link Expiry Window', 'omnifywp-ecommerce'), 'checkout', 'download link expiry days'],
			[__('Maximum Download Attempts per File', 'omnifywp-ecommerce'), 'checkout', 'download limit attempts'],
			[__('Require Terms & Conditions Agreement', 'omnifywp-ecommerce'), 'checkout', 'terms conditions agreement'],
			[__('Terms & Conditions Page URL', 'omnifywp-ecommerce'), 'checkout', 'terms url legal'],
			[__('Enable Customer Refund Requests', 'omnifywp-ecommerce'), 'checkout', 'refunds refund requests'],
			[__('Eligible Refund Window', 'omnifywp-ecommerce'), 'checkout', 'refund duration days window'],

			// ── Fraud & Risk ─────────────────────────────────────────────
			[__('Flag Country Mismatch', 'omnifywp-ecommerce'), 'fraud', 'fraud country mismatch billing shipping'],
			[__('Flag Disposable Emails', 'omnifywp-ecommerce'), 'fraud', 'fraud disposable email'],
			[__('High Value Order Threshold', 'omnifywp-ecommerce'), 'fraud', 'fraud high value threshold'],
			[__('IP Velocity Limit', 'omnifywp-ecommerce'), 'fraud', 'fraud rate limit ip velocity'],

			// ── Marketing & Tracking ─────────────────────────────────────
			[__('Google Analytics 4 (GA4)', 'omnifywp-ecommerce'), 'marketing', 'analytics tracking ga4'],
			[__('GA4 Measurement ID', 'omnifywp-ecommerce'), 'marketing', 'ga4 measurement id'],
			[__('Meta Pixel', 'omnifywp-ecommerce'), 'marketing', 'facebook meta pixel'],
			[__('Meta Pixel ID', 'omnifywp-ecommerce'), 'marketing', 'pixel id facebook'],
			[__('Console Debug Logging', 'omnifywp-ecommerce'), 'marketing', 'debug tracking console'],

			// ── Storefront Design ────────────────────────────────────────
			[__('Brand & Theme', 'omnifywp-ecommerce'), 'design', 'colors theme design'],
			[__('Primary Theme Color', 'omnifywp-ecommerce'), 'design', 'brand color primary'],
			[__('Primary Hover Color', 'omnifywp-ecommerce'), 'design', 'hover color accent'],
			[__('Border Radius (px)', 'omnifywp-ecommerce'), 'design', 'radius corners rounded'],
			[__('Font Family', 'omnifywp-ecommerce'), 'design', 'typography font'],
			[__('Show Hero Banner', 'omnifywp-ecommerce'), 'design', 'hero banner storefront'],
			[__('Hero Title', 'omnifywp-ecommerce'), 'design', 'hero heading title'],
			[__('Hero Subtitle / Description', 'omnifywp-ecommerce'), 'design', 'hero subtitle description'],
			[__('Show Bottom Features Bar', 'omnifywp-ecommerce'), 'design', 'features bar trust'],
			[__('Catalog & Filters', 'omnifywp-ecommerce'), 'design', 'catalog filters layout'],
			[__('Default Product View', 'omnifywp-ecommerce'), 'design', 'catalog layout grid list'],
			[__('Products Per Page', 'omnifywp-ecommerce'), 'design', 'per page pagination count'],
			[__('Grid Columns', 'omnifywp-ecommerce'), 'design', 'columns grid layout'],
			[__('Grid Rows (visible)', 'omnifywp-ecommerce'), 'design', 'rows grid visible'],
			[__('Pagination Style', 'omnifywp-ecommerce'), 'design', 'pagination load more classic'],
			[__('Default Product Sorting', 'omnifywp-ecommerce'), 'design', 'sort default order'],
			[__('Infinite Scroll Loading', 'omnifywp-ecommerce'), 'design', 'infinite scroll load'],
			[__('Show Filter Panel', 'omnifywp-ecommerce'), 'design', 'filters panel show'],
			[__('Filter Position', 'omnifywp-ecommerce'), 'design', 'filter position sidebar top'],
			[__('Search Field', 'omnifywp-ecommerce'), 'design', 'search filter'],
			[__('Sort Dropdown', 'omnifywp-ecommerce'), 'design', 'sort dropdown filter'],
			[__('Product Display & Reassurances', 'omnifywp-ecommerce'), 'design', 'product card display'],
			[__('Product Card Style', 'omnifywp-ecommerce'), 'design', 'card style compact spacious'],
			[__('Show Ratings & Reviews', 'omnifywp-ecommerce'), 'design', 'reviews stars ratings'],
			[__('Show Add to Cart on Cards', 'omnifywp-ecommerce'), 'design', 'add to cart card'],
			[__('Show Wishlist Option', 'omnifywp-ecommerce'), 'design', 'wishlist favourite'],
			[__('Show Product SKU', 'omnifywp-ecommerce'), 'design', 'sku product code'],

			// ── Email Templates ──────────────────────────────────────────
			[__('Email Notifications Hub', 'omnifywp-ecommerce'), 'email', 'emails notifications hub'],
			[__('Sender Name', 'omnifywp-ecommerce'), 'email', 'from name sender'],
			[__('Sender Email Address', 'omnifywp-ecommerce'), 'email', 'from email sender address'],
			[__('Admin Recipient Address', 'omnifywp-ecommerce'), 'email', 'admin notification address'],
			[__('Order Receipt & Confirmation', 'omnifywp-ecommerce'), 'email', 'receipt confirmation email'],
			[__('Order Shipped & Out for Delivery', 'omnifywp-ecommerce'), 'email', 'shipped dispatch tracking email'],
			[__('Order Ready for Store Pickup', 'omnifywp-ecommerce'), 'email', 'pickup collect email'],
			[__('Order Cancelled / Voided Notice', 'omnifywp-ecommerce'), 'email', 'cancelled voided email'],
			[__('Customer Note / Direct Message', 'omnifywp-ecommerce'), 'email', 'note message email'],
			[__('Customer Welcome & Portal Access', 'omnifywp-ecommerce'), 'email', 'welcome account email'],
			[__('Manual & Offline Payment Instructions', 'omnifywp-ecommerce'), 'email', 'pending manual payment email'],
			[__('Refund Processed Notification', 'omnifywp-ecommerce'), 'email', 'refund processed email'],
			[__('Order Status Update Notice', 'omnifywp-ecommerce'), 'email', 'status update email'],
			[__('Abandoned Cart Recovery Email', 'omnifywp-ecommerce'), 'email', 'abandoned cart email'],
			[__('New Order Notification', 'omnifywp-ecommerce'), 'email', 'admin new order alert'],
			[__('Failed Payment / Order Declined Alert', 'omnifywp-ecommerce'), 'email', 'failed payment declined admin'],
			[__('High-Risk / Fraud Suspicion Alert', 'omnifywp-ecommerce'), 'email', 'fraud risk alert admin'],
			[__('Refund Requested Alert', 'omnifywp-ecommerce'), 'email', 'refund requested admin alert'],
			[__('Low-Stock Inventory Alert', 'omnifywp-ecommerce'), 'email', 'low stock alert admin'],
			[__('Out of Stock Alert', 'omnifywp-ecommerce'), 'email', 'out of stock alert admin'],
			[__('Dispatch & Routing Options', 'omnifywp-ecommerce'), 'email', 'routing cc bcc dispatch'],
			[__('Custom SMTP Server Delivery', 'omnifywp-ecommerce'), 'email', 'smtp host port username password encryption'],
			[__('Email Footer & Legal Branding', 'omnifywp-ecommerce'), 'email', 'footer legal copyright branding'],
			[__('Email Logs', 'omnifywp-ecommerce'), 'email', 'email log history'],

			// ── Tools & Maintenance ──────────────────────────────────────
			[__('System & Database Maintenance', 'omnifywp-ecommerce'), 'tools', 'system database maintenance'],
			[__('Optimize Database Tables', 'omnifywp-ecommerce'), 'tools', 'optimize database tables'],
			[__('Clear Transient Cache', 'omnifywp-ecommerce'), 'tools', 'cache clear transient'],
			[__('Purge Expired Abandoned Carts', 'omnifywp-ecommerce'), 'tools', 'purge carts cleanup'],
			[__('Re-sync Product Inventory', 'omnifywp-ecommerce'), 'tools', 'inventory sync stock resync'],
		];
	}

	/**
	 * Full payload consumed by the client-side global search.
	 *
	 * @return array{sections: array<string, string>, items: array<int, array{label: string, section: string, group: string, keywords: string}>}
	 */
	public static function all(): array {
		$omnify_sections = self::sections();
		$omnify_items    = [];

		foreach (self::entries() as $omnify_entry) {
			$omnify_section = (string) $omnify_entry[1];
			$omnify_items[] = [
				'label'    => (string) $omnify_entry[0],
				'section'  => $omnify_section,
				'group'    => (string) ($omnify_sections[$omnify_section] ?? $omnify_section),
				'keywords' => (string) ($omnify_entry[2] ?? ''),
			];
		}

		return [
			'sections' => $omnify_sections,
			'items'    => $omnify_items,
		];
	}
}
