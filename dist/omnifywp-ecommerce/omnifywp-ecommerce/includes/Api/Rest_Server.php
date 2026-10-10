<?php
/**
 * REST API framework.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Downloads\Omnify_Download_Permission_Service;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Coupon_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;
use Omnify\eCommerce\Repositories\Omnify_Wishlist_Repository;
use Omnify\eCommerce\Repositories\Omnify_Admin_Activity_Repository;
use Omnify\eCommerce\Repositories\Omnify_Download_Repository;
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;
use Omnify\eCommerce\Support\Omnify_Email_Service;
use Omnify\eCommerce\Support\Omnify_Abandoned_Cart_Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class Omnify_Rest_Server {
	private const NAMESPACE = 'omnify/v1';

	public function __construct(
		private Omnify_Settings_Repository $omnify_settings,
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_product_files,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Download_Permission_Service $omnify_download_permissions,
		private Omnify_Customer_Access_Repository $omnify_access,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Email_Service $omnify_emails,
		private Omnify_Coupon_Repository $omnify_coupons,
		private Omnify_Review_Repository $omnify_reviews,
		private Omnify_Abandoned_Cart_Repository $omnify_abandoned_carts,
		private Omnify_Wishlist_Repository $omnify_wishlists,
		private \Omnify\eCommerce\Support\Omnify_Payment_Gateway_Service $omnify_payment_gateways,
		private Omnify_Admin_Activity_Repository $omnify_activities,
		private Omnify_Download_Repository $omnify_downloads,
		private Omnify_Abandoned_Cart_Service $omnify_abandoned_cart_service
	) {}

	public function register_routes(): void {
		/**
		 * Explanatory Note on Public Routes:
		 * The storefront checkout, cart management, coupon validation, wishlist toggles, and payment
		 * webhook callbacks (Stripe, PayPal, Razorpay, SSLCommerz) are intentionally registered with
		 * 'permission_callback' => '__return_true' because they must be accessible by anonymous (guest)
		 * visitors on the frontend, or by external gateway APIs sending asynchronous payment notifications.
		 * Internal REST endpoints (managing settings, listing customers, modifying products) are fully
		 * restricted using custom permission callbacks.
		 */
		register_rest_route(
			self::NAMESPACE,
			'/status',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'status'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkout/validate-coupon',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'validate_coupon_endpoint'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkout/abandoned-cart',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'capture_abandoned_cart'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkout/abandoned-cart/unsubscribe',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'unsubscribe_abandoned_cart'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkout/payment-methods',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_payment_methods'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/cart',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_cart'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'save_cart'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'clear_cart'],
					'permission_callback' => '__return_true',
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/wishlist',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_wishlist'],
				'permission_callback' => [$this, 'is_logged_in'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/wishlist/toggle',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'toggle_wishlist'],
				'permission_callback' => [$this, 'is_logged_in'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/mark-paid',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'mark_order_paid'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_settings'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'update_settings'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/tax-rules',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_tax_rules'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_tax_rule'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/tax-rules/(?P<index>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_tax_rule'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_tax_rule'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_tax_rule'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/delivery-zones',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_delivery_zones'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_delivery_zone'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/delivery-zones/(?P<index>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_delivery_zone'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_delivery_zone'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_delivery_zone'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_products'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_product'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/public',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_products_public'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/public/slug/(?P<slug>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_product_public_by_slug'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/public/sku/(?P<sku>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_product_public_by_sku'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/history',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_order_history'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_product'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_product'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_product'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore (soft delete support)
		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_product'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_product'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/files',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_product_files'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'upload_product_file'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/files/(?P<file_id>\d+)',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'replace_product_file'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_product_file'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/files/(?P<file_id>\d+)/download-url',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_download_url'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_customers'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_customer'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_customer'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_customer'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_customer'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore support
		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_customer'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_customer'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/notes',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_customer_notes'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_customer_note'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/notes/(?P<note_id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [$this, 'delete_customer_note'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/activity',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_customer_activity'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/access',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'grant_access'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/access/bulk',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'bulk_grant_access'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/access/revoke',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'revoke_access'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/access',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_customer_access'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkout',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'checkout'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/stripe/checkout-session',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_stripe_checkout_session'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/stripe/payment-intent',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_stripe_payment_intent'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/stripe/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'stripe_webhook'],
				'permission_callback' => [$this, 'can_process_stripe_webhook'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/paypal/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_paypal_order'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/paypal/capture',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'capture_paypal_order'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/paypal/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'paypal_webhook'],
				'permission_callback' => [$this, 'can_process_paypal_webhook'],
			]
		);

		// Razorpay
		register_rest_route(
			self::NAMESPACE,
			'/razorpay/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_razorpay_order'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/razorpay/verify',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'verify_razorpay_payment'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/razorpay/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'razorpay_webhook'],
				'permission_callback' => [$this, 'can_process_razorpay_webhook'],
			]
		);

		// Alipay
		register_rest_route(
			self::NAMESPACE,
			'/alipay/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_alipay_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/alipay/verify',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'verify_alipay_payment'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/alipay/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'alipay_webhook'],
				'permission_callback' => [$this, 'can_process_alipay_webhook'],
			]
		);

		// WeChat Pay
		register_rest_route(
			self::NAMESPACE,
			'/wechat/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_wechat_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/wechat/verify',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'verify_wechat_payment'],
				'permission_callback' => [$this, 'can_process_wechat_verify'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/wechat/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'wechat_webhook'],
				'permission_callback' => [$this, 'can_process_wechat_webhook'],
			]
		);

		// SSLCommerz
		register_rest_route(
			self::NAMESPACE,
			'/sslcommerz/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_sslcommerz_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/sslcommerz/verify',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'verify_sslcommerz_payment'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/sslcommerz/ipn',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'sslcommerz_ipn'],
				'permission_callback' => [$this, 'can_process_sslcommerz_ipn'],
			]
		);

		// Paystack
		register_rest_route(
			self::NAMESPACE,
			'/paystack/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_paystack_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/paystack/verify',
			[
				'methods'             => ['GET', 'POST'],
				'callback'            => [$this, 'verify_paystack_payment'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/paystack/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'paystack_webhook'],
				'permission_callback' => [$this, 'can_process_paystack_webhook'],
			]
		);

		// Tap Payments
		register_rest_route(
			self::NAMESPACE,
			'/tap/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_tap_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/tap/verify',
			[
				'methods'             => ['GET', 'POST'],
				'callback'            => [$this, 'verify_tap_payment'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/tap/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'tap_webhook'],
				'permission_callback' => [$this, 'can_process_tap_webhook'],
			]
		);

		// Mollie
		register_rest_route(
			self::NAMESPACE,
			'/mollie/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_mollie_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/mollie/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'mollie_webhook'],
				'permission_callback' => [$this, 'can_process_mollie_webhook'],
			]
		);

		// Khalti
		register_rest_route(
			self::NAMESPACE,
			'/khalti/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_khalti_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/khalti/verify',
			[
				'methods'             => ['GET', 'POST'],
				'callback'            => [$this, 'verify_khalti_payment'],
				'permission_callback' => '__return_true',
			]
		);

		// eSewa
		register_rest_route(
			self::NAMESPACE,
			'/esewa/order',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'create_esewa_order'],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/esewa/verify',
			[
				'methods'             => ['GET', 'POST'],
				'callback'            => [$this, 'verify_esewa_payment'],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_orders'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_order'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_order'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_order'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore
		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_order'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_order'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/coupons',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_coupons'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'create_coupon'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/coupons/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_coupon'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_coupon'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_coupon'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore
		register_rest_route(
			self::NAMESPACE,
			'/coupons/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_coupon'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/coupons/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_coupon'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/reviews',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_reviews'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/reviews/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_review'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'update_review'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_review'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore
		register_rest_route(
			self::NAMESPACE,
			'/reviews/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_review'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/reviews/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_review'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/categories',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_product_categories'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'link_product_category'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'unlink_product_category'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/variations',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_product_variations'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'save_product_variations'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/activity-logs',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'list_activity_logs'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'clear_activity_logs'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/downloads',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_downloads'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/downloads/(?P<id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [$this, 'revoke_download'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/abandoned-carts',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_abandoned_carts'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/abandoned-carts/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_abandoned_cart'],
					'permission_callback' => [$this, 'can_manage'],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'delete_abandoned_cart'],
					'permission_callback' => [$this, 'can_manage'],
				],
			]
		);

		// Trash & restore
		register_rest_route(
			self::NAMESPACE,
			'/abandoned-carts/(?P<id>\d+)/trash',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'trash_abandoned_cart'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/abandoned-carts/(?P<id>\d+)/restore',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'restore_abandoned_cart'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/abandoned-carts/(?P<id>\d+)/recover',
			[
				'methods'             => 'POST',
				'callback'            => [$this, 'manual_recover_abandoned_cart'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/wishlists',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_all_wishlists'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/customers/(?P<id>\d+)/wishlist',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_customer_wishlist'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/files',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_all_product_files'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/files/(?P<id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_product_file'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/access',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'list_all_access'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/access/(?P<id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_access_detail'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/stats',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'stats'],
				'permission_callback' => [$this, 'can_manage'],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<product_id>\d+)/reviews',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'get_product_reviews'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'submit_product_review'],
					'permission_callback' => [$this, 'can_submit_review'],
				],
			]
		);

		do_action('omnify_rest_routes_registered', self::NAMESPACE, $this);

		// Additional extensibility for new endpoints
		do_action('omnify_rest_trash_endpoints_registered', self::NAMESPACE, $this);
	}

	public function status(): WP_REST_Response {
		return rest_ensure_response(
			[
				'name'    => 'Omnify',
				'version' => OMNIFY_VERSION,
				'rest'    => self::NAMESPACE,
			]
		);
	}

	public function get_settings(): WP_REST_Response {
		return rest_ensure_response($this->omnify_settings->all());
	}

	public function update_settings(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_settings = (array) $omnify_request->get_json_params();
		$this->omnify_settings->update($omnify_settings);

		return rest_ensure_response($this->omnify_settings->all());
	}

	public function list_tax_rules(): WP_REST_Response {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];

		return rest_ensure_response(apply_filters('omnify_rest_tax_rules_response', $omnify_rules));
	}

	public function get_tax_rule(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_rules = $this->settings_resource_items('tax_rules');
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_rules)) {
			return new WP_Error('omnify_tax_rule_not_found', __('Tax rule not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response(apply_filters('omnify_rest_tax_rule_response', $omnify_rules[$omnify_index], $omnify_index));
	}

	public function create_tax_rule(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];
		$omnify_rule = apply_filters('omnify_rest_pre_create_tax_rule', (array) $omnify_request->get_json_params(), $omnify_request);
		$omnify_rules[] = $omnify_rule;
		$omnify_settings['tax_rules'] = $omnify_rules;
		$this->omnify_settings->update($omnify_settings);
		$omnify_rules = $this->settings_resource_items('tax_rules');
		$omnify_index = max(0, count($omnify_rules) - 1);

		do_action('omnify_rest_tax_rule_created', $omnify_index, $omnify_rules[$omnify_index] ?? [], $omnify_request);

		return rest_ensure_response(['index' => $omnify_index, 'rule' => $omnify_rules[$omnify_index] ?? []]);
	}

	public function update_tax_rule(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_rules)) {
			return new WP_Error('omnify_tax_rule_not_found', __('Tax rule not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_rules[$omnify_index] = apply_filters('omnify_rest_pre_update_tax_rule', array_merge((array) $omnify_rules[$omnify_index], (array) $omnify_request->get_json_params()), $omnify_index, $omnify_request);
		$omnify_settings['tax_rules'] = $omnify_rules;
		$this->omnify_settings->update($omnify_settings);
		$omnify_rules = $this->settings_resource_items('tax_rules');

		do_action('omnify_rest_tax_rule_updated', $omnify_index, $omnify_rules[$omnify_index] ?? [], $omnify_request);

		return rest_ensure_response(['index' => $omnify_index, 'rule' => $omnify_rules[$omnify_index] ?? []]);
	}

	public function delete_tax_rule(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_rules)) {
			return new WP_Error('omnify_tax_rule_not_found', __('Tax rule not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_deleted = $omnify_rules[$omnify_index];
		array_splice($omnify_rules, $omnify_index, 1);
		$omnify_settings['tax_rules'] = array_values($omnify_rules);
		$this->omnify_settings->update($omnify_settings);

		do_action('omnify_rest_tax_rule_deleted', $omnify_index, $omnify_deleted, $omnify_request);

		return rest_ensure_response(['deleted' => true, 'index' => $omnify_index]);
	}

	public function list_delivery_zones(): WP_REST_Response {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];

		return rest_ensure_response(apply_filters('omnify_rest_delivery_zones_response', $omnify_zones));
	}

	public function get_delivery_zone(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_zones = $this->settings_resource_items('delivery_zones');
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_zones)) {
			return new WP_Error('omnify_delivery_zone_not_found', __('Delivery zone not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response(apply_filters('omnify_rest_delivery_zone_response', $omnify_zones[$omnify_index], $omnify_index));
	}

	public function create_delivery_zone(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];
		$omnify_zone = apply_filters('omnify_rest_pre_create_delivery_zone', (array) $omnify_request->get_json_params(), $omnify_request);
		$omnify_zones[] = $omnify_zone;
		$omnify_settings['delivery_zones'] = $omnify_zones;
		$this->omnify_settings->update($omnify_settings);
		$omnify_zones = $this->settings_resource_items('delivery_zones');
		$omnify_index = max(0, count($omnify_zones) - 1);

		do_action('omnify_rest_delivery_zone_created', $omnify_index, $omnify_zones[$omnify_index] ?? [], $omnify_request);

		return rest_ensure_response(['index' => $omnify_index, 'zone' => $omnify_zones[$omnify_index] ?? []]);
	}

	public function update_delivery_zone(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_zones)) {
			return new WP_Error('omnify_delivery_zone_not_found', __('Delivery zone not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_zones[$omnify_index] = apply_filters('omnify_rest_pre_update_delivery_zone', array_merge((array) $omnify_zones[$omnify_index], (array) $omnify_request->get_json_params()), $omnify_index, $omnify_request);
		$omnify_settings['delivery_zones'] = $omnify_zones;
		$this->omnify_settings->update($omnify_settings);
		$omnify_zones = $this->settings_resource_items('delivery_zones');

		do_action('omnify_rest_delivery_zone_updated', $omnify_index, $omnify_zones[$omnify_index] ?? [], $omnify_request);

		return rest_ensure_response(['index' => $omnify_index, 'zone' => $omnify_zones[$omnify_index] ?? []]);
	}

	public function delete_delivery_zone(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];
		$omnify_index = absint($omnify_request['index']);

		if (! array_key_exists($omnify_index, $omnify_zones)) {
			return new WP_Error('omnify_delivery_zone_not_found', __('Delivery zone not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_deleted = $omnify_zones[$omnify_index];
		array_splice($omnify_zones, $omnify_index, 1);
		$omnify_settings['delivery_zones'] = array_values($omnify_zones);
		$this->omnify_settings->update($omnify_settings);

		do_action('omnify_rest_delivery_zone_deleted', $omnify_index, $omnify_deleted, $omnify_request);

		return rest_ensure_response(['deleted' => true, 'index' => $omnify_index]);
	}

	public function list_products_public(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_params = $omnify_request->get_params();
		$omnify_params['status'] = 'published';

		$omnify_params = apply_filters('omnify_rest_public_products_query_args', $omnify_params, $omnify_request);
		$omnify_results = $this->omnify_products->all($omnify_params);
		$omnify_results = apply_filters('omnify_rest_public_products_response', $omnify_results, $omnify_params, $omnify_request);

		return rest_ensure_response($omnify_results);
	}

	public function get_product_public_by_slug(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_slug = sanitize_title($omnify_request['slug']);
		$omnify_product = $this->omnify_products->find_by_slug($omnify_slug);

		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '') || ! empty($omnify_product['deleted_at'])) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_product = apply_filters('omnify_rest_public_product_by_slug_response', $omnify_product, $omnify_request);
		return rest_ensure_response($omnify_product);
	}

	public function get_product_public_by_sku(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_sku = sanitize_text_field($omnify_request['sku']);
		$omnify_product = $this->omnify_products->find_by_sku($omnify_sku);

		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '') || ! empty($omnify_product['deleted_at'])) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_product = apply_filters('omnify_rest_public_product_by_sku_response', $omnify_product, $omnify_request);
		return rest_ensure_response($omnify_product);
	}

	public function get_order_history(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_order_id = absint($omnify_request['id']);
		$omnify_order = $this->omnify_orders->find($omnify_order_id);

		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_history = $omnify_order['notes'] ?? [];
		$omnify_history = apply_filters('omnify_rest_order_history_response', $omnify_history, $omnify_order_id, $omnify_request);

		return rest_ensure_response($omnify_history);
	}

	public function list_products(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_params = $omnify_request->get_params();
		$omnify_params = apply_filters('omnify_rest_products_query_args', $omnify_params, $omnify_request);

		$omnify_results = $this->omnify_products->all($omnify_params);
		$omnify_results = apply_filters('omnify_rest_products_response', $omnify_results, $omnify_params, $omnify_request);

		do_action('omnify_rest_products_listed', count($omnify_results), $omnify_params, $omnify_request);

		return rest_ensure_response($omnify_results);
	}

	public function create_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();

		if (empty($omnify_data['name'])) {
			return new WP_Error('omnify_product_name_required', __('Product name is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_id = $this->omnify_products->create($omnify_data);

		return rest_ensure_response($this->omnify_products->find($omnify_id));
	}

	public function get_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product = $this->omnify_products->find(absint($omnify_request['id']));

		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response($omnify_product);
	}

	public function update_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_products->find($omnify_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$this->omnify_products->update($omnify_id, (array) $omnify_request->get_json_params());

		return rest_ensure_response($this->omnify_products->find($omnify_id));
	}

	public function delete_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_products->find($omnify_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		// DELETE is permanent. Use /trash and /restore for soft delete.
		$omnify_result = $this->omnify_products->delete($omnify_id);
		do_action('omnify_rest_product_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_products->find($omnify_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_products->trash($omnify_id);
		do_action('omnify_rest_product_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_product(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		$omnify_product = $this->omnify_products->find($omnify_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_products->restore($omnify_id);
		do_action('omnify_rest_product_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'product' => $this->omnify_products->find($omnify_id)]);
	}

	public function list_product_files(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);

		if (! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response($this->omnify_product_files->all_for_product($omnify_product_id));
	}

	public function upload_product_file(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);

		if (! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_upload = $this->handle_upload($omnify_request);
		if (is_wp_error($omnify_upload)) {
			return $omnify_upload;
		}

		$omnify_file_id = $this->omnify_product_files->create_from_upload($omnify_product_id, $omnify_upload, $this->file_version($omnify_request));

		return rest_ensure_response($this->omnify_product_files->find($omnify_file_id));
	}

	public function replace_product_file(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_file_id    = absint($omnify_request['file_id']);
		$omnify_file       = $this->omnify_product_files->find($omnify_file_id);

		if (! $this->omnify_products->find($omnify_product_id) || ! $omnify_file || (int) $omnify_file['product_id'] !== $omnify_product_id) {
			return new WP_Error('omnify_file_not_found', __('Product file not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_upload = $this->handle_upload($omnify_request);
		if (is_wp_error($omnify_upload)) {
			return $omnify_upload;
		}

		$this->omnify_product_files->replace_from_upload($omnify_file_id, $omnify_upload, $this->file_version($omnify_request));

		return rest_ensure_response($this->omnify_product_files->find($omnify_file_id));
	}

	public function delete_product_file(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_file_id    = absint($omnify_request['file_id']);
		$omnify_file       = $this->omnify_product_files->find($omnify_file_id);

		if (! $this->omnify_products->find($omnify_product_id) || ! $omnify_file || (int) $omnify_file['product_id'] !== $omnify_product_id) {
			return new WP_Error('omnify_file_not_found', __('Product file not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response(['deleted' => $this->omnify_product_files->delete($omnify_file_id)]);
	}

	public function create_download_url(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_file_id = absint($omnify_request['file_id']);
		$omnify_file = $this->omnify_product_files->find($omnify_file_id);

		if (! $this->omnify_products->find($omnify_product_id) || ! $omnify_file || (int) $omnify_file['product_id'] !== $omnify_product_id) {
			return new WP_Error('omnify_file_not_found', __('Product file not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_customer_id = absint($omnify_request->get_param('customer_id')) ?: null;
		if (! $this->omnify_download_permissions->can_access($omnify_file_id, $omnify_customer_id)) {
			return new WP_Error('omnify_download_unavailable', __('This file is not available for download.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_expires_in = max(60, absint($omnify_request->get_param('expires_in') ?: 900));

		return rest_ensure_response(
			[
				'url'        => $this->omnify_signed_urls->create($omnify_file_id, $omnify_expires_in, $omnify_customer_id),
				'expiresIn'  => $omnify_expires_in,
				'fileId'     => $omnify_file_id,
				'customerId' => $omnify_customer_id,
			]
		);
	}

	public function list_customers(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_params = $omnify_request->get_params();
		$omnify_params = apply_filters('omnify_rest_customers_query_args', $omnify_params, $omnify_request);

		$omnify_results = $this->omnify_customers->all($omnify_params);
		$omnify_results = apply_filters('omnify_rest_customers_response', $omnify_results, $omnify_params, $omnify_request);

		do_action('omnify_rest_customers_listed', count($omnify_results), $omnify_params, $omnify_request);

		return rest_ensure_response($omnify_results);
	}

	public function create_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));

		if (! is_email($omnify_email)) {
			return new WP_Error('omnify_customer_email_required', __('A valid customer email is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}
		if ($this->omnify_customers->find_by_email($omnify_email)) {
			return new WP_Error('omnify_customer_email_exists', __('A customer with this email already exists.', 'omnifywp-ecommerce'), ['status' => 409]);
		}

		$omnify_data['email'] = $omnify_email;
		$omnify_id = $this->omnify_customers->create($omnify_data);

		return rest_ensure_response($this->omnify_customers->find($omnify_id));
	}

	public function get_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_customer = $this->omnify_customers->find(absint($omnify_request['id']));

		if (! $omnify_customer) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_customer['notes'] = $this->omnify_customers->notes((int) $omnify_customer['id']);
		$omnify_customer['activity'] = $this->omnify_customers->activity((int) $omnify_customer['id']);

		return rest_ensure_response($omnify_customer);
	}

	public function update_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		if (array_key_exists('email', $omnify_data) && ! is_email((string) $omnify_data['email'])) {
			return new WP_Error('omnify_customer_email_required', __('A valid customer email is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}
		if (array_key_exists('email', $omnify_data)) {
			$omnify_existing = $this->omnify_customers->find_by_email((string) $omnify_data['email']);
			if ($omnify_existing && (int) $omnify_existing['id'] !== $omnify_id) {
				return new WP_Error('omnify_customer_email_exists', __('A customer with this email already exists.', 'omnifywp-ecommerce'), ['status' => 409]);
			}
		}

		$this->omnify_customers->update($omnify_id, $omnify_data);

		return rest_ensure_response($this->omnify_customers->find($omnify_id));
	}

	public function delete_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_customers->delete($omnify_id);
		do_action('omnify_rest_customer_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_customers->trash($omnify_id);
		do_action('omnify_rest_customer_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_customer(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_customers->restore($omnify_id);
		do_action('omnify_rest_customer_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'customer' => $this->omnify_customers->find($omnify_id)]);
	}

	public function list_customer_notes(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_customer_only = ! empty($omnify_request->get_param('customer_only'));
		return rest_ensure_response($this->omnify_customers->notes($omnify_id, $omnify_customer_only));
	}

	public function create_customer_note(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_note = (string) ($omnify_data['note'] ?? '');
		$omnify_visible = ! empty($omnify_data['customer_visible']);

		if ('' === trim($omnify_note)) {
			return new WP_Error('omnify_customer_note_required', __('A note is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$this->omnify_customers->add_note($omnify_id, $omnify_note, $omnify_visible);

		return rest_ensure_response($this->omnify_customers->notes($omnify_id));
	}

	public function delete_customer_note(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response(['deleted' => $this->omnify_customers->delete_note($omnify_id, absint($omnify_request['note_id']))]);
	}

	public function list_customer_activity(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response($this->omnify_customers->activity($omnify_id));
	}

	public function grant_access(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_customer_id = absint($omnify_data['customer_id'] ?? 0);
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);

		if (! $this->omnify_customers->find($omnify_customer_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		if (! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$this->omnify_access->grant($omnify_customer_id, $omnify_product_id, empty($omnify_data['expires_at']) ? null : (string) $omnify_data['expires_at']);

		return rest_ensure_response($this->omnify_access->for_customer($omnify_customer_id));
	}

	public function bulk_grant_access(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);

		if (! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_granted = $this->omnify_access->bulk_grant((array) ($omnify_data['customer_ids'] ?? []), $omnify_product_id, empty($omnify_data['expires_at']) ? null : (string) $omnify_data['expires_at']);

		return rest_ensure_response(['granted' => count($omnify_granted)]);
	}

	public function revoke_access(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_customer_id = absint($omnify_data['customer_id'] ?? 0);
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);

		if (! $this->omnify_customers->find($omnify_customer_id) || ! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_access_not_found', __('Customer or product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response(['revoked' => $this->omnify_access->revoke($omnify_customer_id, $omnify_product_id)]);
	}

	public function list_customer_access(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_customer_id = absint($omnify_request['id']);

		if (! $this->omnify_customers->find($omnify_customer_id)) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return rest_ensure_response($this->omnify_access->for_customer($omnify_customer_id));
	}

	/**
	 * @return array<int, mixed>
	 */
	private function settings_resource_items(string $omnify_key): array {
		$omnify_settings = $this->omnify_settings->all();
		return is_array($omnify_settings[$omnify_key] ?? null) ? array_values($omnify_settings[$omnify_key]) : [];
	}

	public function can_manage(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_permission = apply_filters('omnify_rest_can_manage', null, $omnify_request, $this);
		if (null !== $omnify_permission) {
			return $omnify_permission;
		}

		$omnify_nonce = sanitize_text_field(wp_unslash((string) $omnify_request->get_header('X-WP-Nonce')));

		if (! wp_verify_nonce($omnify_nonce, 'wp_rest')) {
			return new WP_Error('omnify_bad_nonce', __('Invalid REST nonce.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		if (! current_user_can('manage_options')) {
			return new WP_Error('omnify_forbidden', __('You do not have permission to manage Omnify.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function is_logged_in(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_permission = apply_filters('omnify_rest_is_logged_in', null, $omnify_request, $this);
		if (null !== $omnify_permission) {
			return $omnify_permission;
		}

		$omnify_nonce = sanitize_text_field(wp_unslash((string) $omnify_request->get_header('X-WP-Nonce')));
		if (! wp_verify_nonce($omnify_nonce, 'wp_rest')) {
			return new WP_Error('omnify_bad_nonce', __('Invalid REST nonce.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		if (! is_user_logged_in()) {
			return new WP_Error('omnify_login_required', __('Please log in to save wishlist items.', 'omnifywp-ecommerce'), ['status' => 401]);
		}

		return true;
	}

	public function can_submit_review(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id'] ?? 0);
		if (! $omnify_product_id || ! $this->omnify_products->find($omnify_product_id)) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_params = $omnify_request->get_json_params() ?: $omnify_request->get_params();
		$omnify_nonce = sanitize_text_field(wp_unslash((string) ($omnify_params['omnify_review_nonce'] ?? $omnify_request->get_header('X-WP-Nonce') ?? '')));

		if (! wp_verify_nonce($omnify_nonce, 'omnify_submit_review_' . $omnify_product_id) && ! wp_verify_nonce($omnify_nonce, 'wp_rest')) {
			return new WP_Error('omnify_bad_nonce', __('Invalid security token.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_razorpay_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['razorpay_enabled'])) {
			return new WP_Error('omnify_razorpay_disabled', __('Razorpay is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_secret = sanitize_text_field((string) ($omnify_settings['razorpay_webhook_secret'] ?? ''));
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_razorpay_secret_missing', __('Razorpay webhook secret is not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_signature = sanitize_text_field(wp_unslash((string) ($omnify_request->get_header('x-razorpay-signature') ?? '')));
		if ('' === $omnify_signature) {
			return new WP_Error('omnify_missing_signature', __('Missing signature header.', 'omnifywp-ecommerce'), ['status' => 401]);
		}

		$omnify_expected = hash_hmac('sha256', $omnify_request->get_body(), $omnify_secret);
		if (! hash_equals($omnify_expected, $omnify_signature)) {
			return new WP_Error('omnify_invalid_signature', __('Invalid Razorpay webhook signature.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_stripe_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['stripe_enabled'])) {
			return new WP_Error('omnify_stripe_disabled', __('Stripe is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_secret = sanitize_text_field((string) ($omnify_settings['stripe_webhook_secret'] ?? ''));
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_stripe_secret_missing', __('Stripe webhook secret is not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_sig_header = sanitize_text_field(wp_unslash((string) ($omnify_request->get_header('stripe-signature') ?? '')));
		if ('' === $omnify_sig_header) {
			return new WP_Error('omnify_missing_signature', __('Missing Stripe signature header.', 'omnifywp-ecommerce'), ['status' => 401]);
		}

		return true;
	}

	public function can_process_paypal_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['paypal_enabled'])) {
			return new WP_Error('omnify_paypal_disabled', __('PayPal is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_alipay_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['alipay_enabled'])) {
			return new WP_Error('omnify_alipay_disabled', __('Alipay is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_wechat_verify(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['wechat_enabled'])) {
			return new WP_Error('omnify_wechat_disabled', __('WeChat Pay is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_unconfigured', __('WeChat Pay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_wechat_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['wechat_enabled'])) {
			return new WP_Error('omnify_wechat_disabled', __('WeChat Pay is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_unconfigured', __('WeChat Pay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_signature = sanitize_text_field(wp_unslash((string) ($omnify_request->get_header('wechatpay-signature') ?? ($omnify_request->get_header('wechat-signature') ?? ''))));
		if ('' === $omnify_signature) {
			$omnify_params = (array) ($omnify_request->get_json_params() ?: $omnify_request->get_body_params());
			if (empty($omnify_params['sign'])) {
				return new WP_Error('omnify_missing_signature', __('Missing WeChat signature header or sign parameter.', 'omnifywp-ecommerce'), ['status' => 401]);
			}
		}

		return true;
	}

	public function can_process_sslcommerz_ipn(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['sslcommerz_enabled'])) {
			return new WP_Error('omnify_sslcommerz_disabled', __('SSLCommerz is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_store_id     = trim((string) ($omnify_settings['sslcommerz_store_id'] ?? ''));
		$omnify_store_passwd = trim((string) ($omnify_settings['sslcommerz_store_password'] ?? ''));
		if ('' === $omnify_store_id || '' === $omnify_store_passwd) {
			return new WP_Error('omnify_sslcommerz_not_configured', __('SSLCommerz credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_data   = (array) ($omnify_request->get_json_params() ?: $omnify_request->get_body_params());
		$omnify_val_id = sanitize_text_field((string) ($omnify_data['val_id'] ?? ''));
		if ('' === $omnify_val_id) {
			return new WP_Error('omnify_missing_val_id', __('Missing SSLCommerz validation ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_order_id = absint($omnify_data['value_a'] ?? 0);
		$omnify_tran_id  = sanitize_text_field((string) ($omnify_data['tran_id'] ?? ''));
		if (! $omnify_order_id && '' !== $omnify_tran_id) {
			$omnify_order_id = absint(str_replace('ssl_', '', $omnify_tran_id));
		}

		if ($omnify_order_id && ! $this->omnify_orders->find($omnify_order_id)) {
			return new WP_Error('omnify_order_not_found', __('Order not found for SSLCommerz IPN.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		return true;
	}

	public function can_process_paystack_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['paystack_enabled'])) {
			return new WP_Error('omnify_paystack_disabled', __('Paystack is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_paystack_secret_missing', __('Paystack secret key is not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_tap_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['tap_enabled'])) {
			return new WP_Error('omnify_tap_disabled', __('Tap Payments is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function can_process_mollie_webhook(WP_REST_Request $omnify_request): bool|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['mollie_enabled'])) {
			return new WP_Error('omnify_mollie_disabled', __('Mollie is not enabled.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		return true;
	}

	public function get_wishlist(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_product_ids = $this->omnify_wishlists->product_ids_for_user(get_current_user_id());
		$omnify_products    = [];

		foreach ($omnify_product_ids as $omnify_product_id) {
			$omnify_product = $this->omnify_products->find($omnify_product_id);
			if ($omnify_product && 'published' === ($omnify_product['status'] ?? '')) {
				$omnify_products[] = [
					'id'            => (int) $omnify_product['id'],
					'name'          => (string) $omnify_product['name'],
					'slug'          => (string) $omnify_product['slug'],
					'price'         => (float) $omnify_product['price'],
					'sale_price'    => null === ($omnify_product['sale_price'] ?? null) ? null : (float) $omnify_product['sale_price'],
					'thumbnail_url' => (string) ($omnify_product['thumbnail_url'] ?? ''),
				];
			}
		}

		return rest_ensure_response([
			'product_ids' => $omnify_product_ids,
			'products'    => $omnify_products,
		]);
	}

	public function toggle_wishlist(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data       = (array) $omnify_request->get_json_params();
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);
		$omnify_product    = $this->omnify_products->find($omnify_product_id);

		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			return new WP_Error('omnify_wishlist_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_wishlists->toggle(get_current_user_id(), $omnify_product_id);

		return rest_ensure_response([
			'success'    => true,
			'product_id' => $omnify_product_id,
			'wishlisted' => $omnify_result['wishlisted'],
			'product_ids' => $this->omnify_wishlists->product_ids_for_user(get_current_user_id()),
		]);
	}

	private function handle_upload(WP_REST_Request $omnify_request): array|WP_Error {
		$omnify_files = $omnify_request->get_file_params();

		if (empty($omnify_files['file'])) {
			return new WP_Error('omnify_file_required', __('A file is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$omnify_upload = wp_handle_upload($omnify_files['file'], ['test_form' => false]);

		if (! empty($omnify_upload['error'])) {
			return new WP_Error('omnify_upload_failed', (string) $omnify_upload['error'], ['status' => 422]);
		}

		$omnify_upload['name'] = sanitize_file_name((string) ($omnify_files['file']['name'] ?? basename((string) $omnify_upload['file'])));

		return $omnify_upload;
	}

	private function file_version(WP_REST_Request $omnify_request): string {
		$omnify_version = sanitize_text_field((string) $omnify_request->get_param('version'));

		return '' === $omnify_version ? '1.0.0' : $omnify_version;
	}

	private function download_access_expires_at(array $omnify_product): ?string {
		$omnify_days = absint($omnify_product['download_expiry_days'] ?? 0);
		if ($omnify_days <= 0) {
			return null;
		}

		return gmdate('Y-m-d H:i:s', time() + ($omnify_days * 86400));
	}

	/**
	 * @return array{rate: float, label: string}
	 */
	private function resolve_tax_rule(array $omnify_settings, string $omnify_country, string $omnify_state): array {
		$omnify_country = $this->normalize_region_token($omnify_country);
		$omnify_state   = $this->normalize_region_token($omnify_state);
		$omnify_rules   = is_array($omnify_settings['tax_rules'] ?? null) ? $omnify_settings['tax_rules'] : [];
		$omnify_matches = [];

		foreach ($omnify_rules as $omnify_index => $omnify_rule) {
			if (empty($omnify_rule['enabled'])) {
				continue;
			}

			$omnify_rule_country = $this->normalize_region_token((string) ($omnify_rule['country'] ?? '*'));
			$omnify_rule_state   = $this->normalize_region_token((string) ($omnify_rule['state'] ?? '*'));
			$omnify_country_ok   = '*' === $omnify_rule_country || $omnify_rule_country === $omnify_country;
			$omnify_state_ok     = '*' === $omnify_rule_state || $omnify_rule_state === $omnify_state;

			if (! $omnify_country_ok || ! $omnify_state_ok) {
				continue;
			}

			$omnify_specificity = ('*' !== $omnify_rule_country ? 10 : 0) + ('*' !== $omnify_rule_state ? 20 : 0);
			$omnify_matches[] = [
				'specificity' => $omnify_specificity,
				'priority'    => absint($omnify_rule['priority'] ?? 10),
				'index'       => $omnify_index,
				'rate'        => max(0.0, (float) ($omnify_rule['rate'] ?? 0.0)),
				'label'       => sanitize_text_field((string) ($omnify_rule['label'] ?? ($omnify_settings['tax_label'] ?? 'Tax'))),
				'reporting_code' => sanitize_text_field((string) ($omnify_rule['reporting_code'] ?? '')),
			];
		}

		if (! empty($omnify_matches)) {
			usort($omnify_matches, static function (array $omnify_a, array $omnify_b): int {
				if ($omnify_a['specificity'] === $omnify_b['specificity']) {
					return $omnify_a['priority'] <=> $omnify_b['priority'] ?: $omnify_a['index'] <=> $omnify_b['index'];
				}
				return $omnify_b['specificity'] <=> $omnify_a['specificity'];
			});

			return [
				'rate'  => (float) $omnify_matches[0]['rate'],
				'label' => '' !== $omnify_matches[0]['label'] ? $omnify_matches[0]['label'] : 'Tax',
				'reporting_code' => (string) ($omnify_matches[0]['reporting_code'] ?? ''),
			];
		}

		return [
			'rate'  => max(0.0, (float) ($omnify_settings['tax_rate'] ?? 0.0)),
			'label' => sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax')),
			'reporting_code' => '',
		];
	}

	private function round_tax(float $omnify_amount, array $omnify_settings): float {
		return round($omnify_amount, 2);
	}

	private function customer_is_tax_exempt(?int $omnify_customer_id): bool {
		if (! $omnify_customer_id) {
			return false;
		}

		$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
		return ! empty($omnify_customer['tax_exempt']);
	}

	private function calculate_tax_breakdown(float $omnify_net_subtotal, float $omnify_shipping_total, float $omnify_tax_rate, array $omnify_settings, bool $omnify_tax_exempt): array {
		$omnify_tax_shipping = ! empty($omnify_settings['tax_shipping']);
		$omnify_prices_include_tax = ! empty($omnify_settings['prices_include_tax']);
		$omnify_shipping_tax = 0.0;
		$omnify_product_tax = 0.0;
		$omnify_taxable_shipping = $omnify_tax_shipping ? $omnify_shipping_total : 0.0;

		if ($omnify_tax_rate <= 0) {
			return ['tax' => 0.0, 'product_tax' => 0.0, 'shipping_tax' => 0.0, 'total' => $omnify_net_subtotal + $omnify_shipping_total, 'taxable_subtotal' => $omnify_net_subtotal, 'taxable_shipping' => $omnify_taxable_shipping];
		}

		if ($omnify_prices_include_tax) {
			$omnify_tax_divisor = 1 + ($omnify_tax_rate / 100);
			$omnify_product_tax = $omnify_tax_exempt ? 0.0 : ($omnify_net_subtotal - ($omnify_net_subtotal / $omnify_tax_divisor));
			$omnify_taxable_subtotal = $omnify_tax_exempt ? ($omnify_net_subtotal / $omnify_tax_divisor) : $omnify_net_subtotal;
			$omnify_shipping_tax = (! $omnify_tax_exempt && $omnify_tax_shipping) ? ($omnify_shipping_total * ($omnify_tax_rate / 100)) : 0.0;
			$omnify_tax = $this->round_tax($omnify_product_tax + $omnify_shipping_tax, $omnify_settings);

			return [
				'tax' => $omnify_tax,
				'product_tax' => $this->round_tax($omnify_product_tax, $omnify_settings),
				'shipping_tax' => $this->round_tax($omnify_shipping_tax, $omnify_settings),
				'total' => $omnify_taxable_subtotal + $omnify_shipping_total + $omnify_shipping_tax,
				'taxable_subtotal' => $omnify_taxable_subtotal,
				'taxable_shipping' => $omnify_taxable_shipping,
			];
		}

		$omnify_taxable_subtotal = $omnify_net_subtotal;
		$omnify_taxable_amount = $omnify_tax_exempt ? 0.0 : ($omnify_net_subtotal + $omnify_taxable_shipping);
		$omnify_tax = $this->round_tax($omnify_taxable_amount * ($omnify_tax_rate / 100), $omnify_settings);

		return [
			'tax' => $omnify_tax,
			'product_tax' => $this->round_tax($omnify_tax_exempt ? 0.0 : ($omnify_net_subtotal * ($omnify_tax_rate / 100)), $omnify_settings),
			'shipping_tax' => $this->round_tax($omnify_tax_exempt ? 0.0 : ($omnify_taxable_shipping * ($omnify_tax_rate / 100)), $omnify_settings),
			'total' => $omnify_net_subtotal + $omnify_shipping_total + $omnify_tax,
			'taxable_subtotal' => $omnify_taxable_subtotal,
			'taxable_shipping' => $omnify_taxable_shipping,
		];
	}

	private function collect_shipping_classes(array $omnify_items): array {
		$omnify_classes = [];
		foreach ($omnify_items as $omnify_item) {
			$omnify_product = $omnify_item['product'] ?? $omnify_item;
			$omnify_class = sanitize_title((string) ($omnify_product['shipping_class'] ?? ''));
			if ('' === $omnify_class) {
				continue;
			}
			$omnify_classes[$omnify_class] = ($omnify_classes[$omnify_class] ?? 0) + max(1, (int) ($omnify_item['quantity'] ?? 1));
		}
		return $omnify_classes;
	}

	private function shipping_class_surcharge(array $omnify_class_costs, array $omnify_shipping_classes): float {
		$omnify_total = 0.0;
		foreach ($omnify_shipping_classes as $omnify_class => $omnify_quantity) {
			$omnify_total += max(0.0, (float) ($omnify_class_costs[$omnify_class] ?? 0.0)) * max(1, (int) $omnify_quantity);
		}
		return $omnify_total;
	}

	/**
	 * @return array<int, array{id: string, amount: float, method: string, type: string}>
	 */
	private function resolve_delivery_rates(array $omnify_settings, string $omnify_country, string $omnify_state, float $omnify_net_subtotal, bool $omnify_is_physical, array $omnify_shipping_classes = []): array|WP_Error {
		if (! $omnify_is_physical) {
			return [];
		}

		$omnify_country = $this->normalize_region_token($omnify_country);
		$omnify_state   = $this->normalize_region_token($omnify_state);
		$omnify_zones   = is_array($omnify_settings['delivery_zones'] ?? null) ? $omnify_settings['delivery_zones'] : [];
		$omnify_matches = [];

		foreach ($omnify_zones as $omnify_index => $omnify_zone) {
			if (empty($omnify_zone['enabled'])) {
				continue;
			}

			$omnify_country_ok = $this->region_list_matches((string) ($omnify_zone['countries'] ?? '*'), $omnify_country);
			$omnify_state_ok   = $this->region_list_matches((string) ($omnify_zone['states'] ?? '*'), $omnify_state);
			if (! $omnify_country_ok || ! $omnify_state_ok) {
				continue;
			}

			$omnify_specificity = ($this->region_list_is_wildcard((string) ($omnify_zone['countries'] ?? '*')) ? 0 : 10)
				+ ($this->region_list_is_wildcard((string) ($omnify_zone['states'] ?? '*')) ? 0 : 20);

			$omnify_cost     = max(0.0, (float) ($omnify_zone['cost'] ?? 0.0));
			$omnify_free_min = max(0.0, (float) ($omnify_zone['free_min'] ?? 0.0));
			$omnify_method_type = sanitize_key((string) ($omnify_zone['method_type'] ?? 'flat_rate'));
			if (! in_array($omnify_method_type, ['flat_rate', 'free_shipping', 'local_pickup'], true)) {
				$omnify_method_type = 'flat_rate';
			}
			if ('free_shipping' === $omnify_method_type || ($omnify_free_min > 0 && $omnify_net_subtotal >= $omnify_free_min)) {
				$omnify_cost = 0.0;
			} elseif ('flat_rate' === $omnify_method_type) {
				$omnify_cost += $this->shipping_class_surcharge(is_array($omnify_zone['class_costs'] ?? null) ? $omnify_zone['class_costs'] : [], $omnify_shipping_classes);
			}

			$omnify_matches[] = [
				'specificity' => $omnify_specificity,
				'priority'    => absint($omnify_zone['priority'] ?? 10),
				'index'       => $omnify_index,
				'amount'      => $omnify_cost,
				'id'          => 'zone-' . $omnify_index . '-' . $omnify_method_type,
				'method'      => sanitize_text_field((string) ($omnify_zone['method_name'] ?? 'Standard delivery')),
				'type'        => $omnify_method_type,
			];
		}

		if (empty($omnify_matches)) {
			return new WP_Error('omnify_delivery_unavailable', __('Delivery is not available for this shipping address.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		usort($omnify_matches, static function (array $omnify_a, array $omnify_b): int {
			if ($omnify_a['specificity'] === $omnify_b['specificity']) {
				return $omnify_a['priority'] <=> $omnify_b['priority'] ?: $omnify_a['index'] <=> $omnify_b['index'];
			}
			return $omnify_b['specificity'] <=> $omnify_a['specificity'];
		});

		return array_map(static function (array $omnify_rate): array {
			return [
				'id'     => (string) $omnify_rate['id'],
				'amount' => (float) $omnify_rate['amount'],
				'method' => '' !== $omnify_rate['method'] ? (string) $omnify_rate['method'] : __('Standard delivery', 'omnifywp-ecommerce'),
				'type'   => (string) $omnify_rate['type'],
			];
		}, $omnify_matches);
	}

	private function resolve_delivery_zone(array $omnify_settings, string $omnify_country, string $omnify_state, float $omnify_net_subtotal, bool $omnify_is_physical, array $omnify_shipping_classes = [], string $omnify_selected_rate = ''): array|WP_Error {
		$omnify_rates = $this->resolve_delivery_rates($omnify_settings, $omnify_country, $omnify_state, $omnify_net_subtotal, $omnify_is_physical, $omnify_shipping_classes);
		if (is_wp_error($omnify_rates)) {
			return $omnify_rates;
		}
		if (empty($omnify_rates)) {
			return ['amount' => 0.0, 'method' => '', 'id' => '', 'rates' => []];
		}

		$omnify_selected = sanitize_text_field($omnify_selected_rate);
		foreach ($omnify_rates as $omnify_rate) {
			if ('' !== $omnify_selected && $omnify_rate['id'] === $omnify_selected) {
				return $omnify_rate + ['rates' => $omnify_rates];
			}
		}

		if ('' !== $omnify_selected) {
			return new WP_Error('omnify_delivery_method_invalid', __('Please select an available delivery method.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		return $omnify_rates[0] + ['rates' => $omnify_rates];
	}

	private function normalize_region_token(string $omnify_value): string {
		$omnify_value = trim($omnify_value);
		if ('' === $omnify_value || '*' === $omnify_value) {
			return '*';
		}

		$omnify_normalized = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $omnify_value) ?? $omnify_value);
		$omnify_aliases = [
			'UNITEDSTATES' => 'US',
			'USA'          => 'US',
			'UNITEDKINGDOM' => 'GB',
			'UK'           => 'GB',
			'GREATBRITAIN' => 'GB',
			'CANADA'       => 'CA',
			'AUSTRALIA'    => 'AU',
			'BANGLADESH'   => 'BD',
			'INDIA'        => 'IN',
			'GERMANY'      => 'DE',
			'FRANCE'       => 'FR',
			'ITALY'        => 'IT',
			'SPAIN'        => 'ES',
		];

		return $omnify_aliases[$omnify_normalized] ?? $omnify_normalized;
	}

	private function region_list_matches(string $omnify_list, string $omnify_target): bool {
		foreach (explode(',', $omnify_list) as $omnify_token) {
			$omnify_normalized = $this->normalize_region_token($omnify_token);
			if ('*' === $omnify_normalized || $omnify_normalized === $omnify_target) {
				return true;
			}
		}

		return false;
	}

	private function region_list_is_wildcard(string $omnify_list): bool {
		foreach (explode(',', $omnify_list) as $omnify_token) {
			if ('*' !== $this->normalize_region_token($omnify_token)) {
				return false;
			}
		}

		return true;
	}

	public function capture_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['abandoned_cart_enabled'])) {
			return rest_ensure_response(['success' => false, 'disabled' => true]);
		}

		$omnify_data       = (array) $omnify_request->get_json_params();
		$omnify_email      = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);
		$omnify_quantity   = max(1, absint($omnify_data['quantity'] ?? 1));

		if (! is_email($omnify_email)) {
			return new WP_Error('omnify_abandoned_cart_email_required', __('A valid email is required before a cart can be recovered.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			return new WP_Error('omnify_abandoned_cart_product_not_found', __('The selected product is not available.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_variation_id = absint($omnify_data['variation_id'] ?? 0);
		$omnify_variation    = null;
		if ($omnify_variation_id && ! empty($omnify_product['variations']) && is_array($omnify_product['variations'])) {
			foreach ($omnify_product['variations'] as $omnify_candidate) {
				if ((int) ($omnify_candidate['id'] ?? 0) === $omnify_variation_id) {
					$omnify_variation = $omnify_candidate;
					break;
				}
			}
		}

		$omnify_price = $omnify_variation
			? ((null !== ($omnify_variation['sale_price'] ?? null) && '' !== $omnify_variation['sale_price']) ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'])
			: ((null !== ($omnify_product['sale_price'] ?? null) && '' !== $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price']);

		$omnify_token        = sanitize_text_field((string) ($omnify_data['token'] ?? ''));
		if ('' === $omnify_token) {
			$omnify_token = wp_generate_uuid4();
		}
		$omnify_checkout_url = esc_url_raw((string) ($omnify_data['checkout_url'] ?? ''));
		if ('' === $omnify_checkout_url) {
			$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
			$omnify_checkout_url     = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/');
		}

		$omnify_recovery_url = add_query_arg(
			array_filter([
				'product_id'          => $omnify_product_id,
				'variation_id'        => $omnify_variation_id ?: null,
				'quantity'            => $omnify_quantity,
				'omnify_recover_cart' => $omnify_token ?: null,
			]),
			$omnify_checkout_url
		);

		$omnify_expire_days = max(1, absint($omnify_settings['abandoned_cart_expire_days'] ?? 14));
		$omnify_cart = $this->omnify_abandoned_carts->upsert([
			'token'        => $omnify_token,
			'email'        => $omnify_email,
			'first_name'   => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
			'last_name'    => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
			'phone'        => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
			'product_id'   => $omnify_product_id,
			'variation_id' => $omnify_variation_id ?: null,
			'quantity'     => $omnify_quantity,
			'subtotal'     => $omnify_price * $omnify_quantity,
			'currency'     => (string) ($omnify_product['currency'] ?? 'USD'),
			'coupon_code'  => sanitize_text_field((string) ($omnify_data['coupon_code'] ?? '')),
			'checkout_url' => $omnify_checkout_url,
			'recovery_url' => $omnify_recovery_url,
			'expires_at'   => gmdate('Y-m-d H:i:s', time() + ($omnify_expire_days * DAY_IN_SECONDS)),
			'cart_payload' => [
				'product_name' => (string) ($omnify_product['name'] ?? ''),
				'product_slug' => (string) ($omnify_product['slug'] ?? ''),
				'variation'    => $omnify_variation,
			],
		]);

		return rest_ensure_response([
			'success'      => true,
			'token'        => $omnify_cart['token'] ?? $omnify_token,
			'recovery_url' => $omnify_cart['recovery_url'] ?? $omnify_recovery_url,
		]);
	}

	public function unsubscribe_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data  = (array) $omnify_request->get_json_params();
		$omnify_token = sanitize_text_field((string) ($omnify_data['token'] ?? $omnify_request->get_param('token') ?? ''));
		if ('' === $omnify_token) {
			return new WP_Error('omnify_invalid_token', __('A valid token is required to unsubscribe.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_unsubscribed = $this->omnify_abandoned_carts->mark_unsubscribed($omnify_token);
		return rest_ensure_response(['success' => (bool) $omnify_unsubscribed]);
	}

	public function get_cart(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_token = $this->cart_session_token($omnify_request);

		return rest_ensure_response([
			'token' => $omnify_token,
			'items' => $this->cart_session_items($omnify_token),
		]);
	}

	public function save_cart(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_data  = (array) $omnify_request->get_json_params();
		$omnify_items = $this->sanitize_cart_session_items($omnify_data['items'] ?? []);
		$omnify_token = $this->cart_session_token($omnify_request);

		set_transient($this->cart_transient_key($omnify_token), $omnify_items, 30 * DAY_IN_SECONDS);

		return rest_ensure_response([
			'success' => true,
			'token'   => $omnify_token,
			'items'   => $omnify_items,
		]);
	}

	public function clear_cart(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_token = $this->cart_session_token($omnify_request);
		delete_transient($this->cart_transient_key($omnify_token));

		return rest_ensure_response([
			'success' => true,
			'token'   => $omnify_token,
			'items'   => [],
		]);
	}

	public function checkout(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		if (! empty($omnify_data['items']) && is_array($omnify_data['items'])) {
			return $this->checkout_cart($omnify_data);
		}
		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);
		$omnify_quantity = max(1, absint($omnify_data['quantity'] ?? 1));
		$omnify_settings = $this->omnify_settings->all();

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== $omnify_product['status']) {
			return new WP_Error('omnify_checkout_product_not_found', __('The product is not available for purchase.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_variation_id = absint($omnify_data['variation_id'] ?? 0);
		$omnify_variation = null;
		if ($omnify_variation_id) {
			global $wpdb;
			$omnify_variation = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}omnify_product_variations WHERE id = %d AND product_id = %d",
				$omnify_variation_id,
				$omnify_product_id
			), ARRAY_A);
			if (! $omnify_variation) {
				return new WP_Error('omnify_checkout_variation_not_found', __('The selected product variation is not available.', 'omnifywp-ecommerce'), ['status' => 404]);
			}
			$omnify_variation['attributes'] = empty($omnify_variation['attributes']) ? [] : json_decode($omnify_variation['attributes'], true);
		}

		$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
		$omnify_is_physical = (isset($omnify_product['type']) && $omnify_product['type'] === 'physical')
			|| (isset($omnify_product['type']) && $omnify_product['type'] === 'variable' && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));

		$omnify_items = [
			[
				'product' => $omnify_product,
				'variation' => $omnify_variation,
				'quantity' => $omnify_quantity
			]
		];

		$omnify_val_err = $this->validate_checkout_payload($omnify_data, $omnify_settings, $omnify_items, $omnify_is_physical, 'manual');
		if (is_wp_error($omnify_val_err)) {
			return $omnify_val_err;
		}

			$omnify_account_mode = in_array($omnify_settings['account_creation_mode'] ?? '', ['optional', 'automatic', 'required'], true)
				? (string) $omnify_settings['account_creation_mode']
				: (! empty($omnify_settings['require_account_on_checkout']) ? 'required' : 'optional');
		$omnify_wp_user_id = get_current_user_id() ?: (email_exists($omnify_email) ?: null);

		// Find or create customer
		$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
		if (! $omnify_customer) {
			$omnify_customer_id = $this->omnify_customers->create([
				'email'      => $omnify_email,
				'user_id'    => $omnify_wp_user_id,
				'first_name' => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
				'last_name'  => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
				'phone'      => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
				'status'     => 'active',
			]);
			$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
		} else {
			// Update customer name and user_id if provided
			$omnify_update_data = [];
			if ($omnify_wp_user_id && empty($omnify_customer['user_id'])) {
				$omnify_update_data['user_id'] = $omnify_wp_user_id;
			}
			if (! empty($omnify_data['first_name']) && empty($omnify_customer['first_name'])) {
				$omnify_update_data['first_name'] = sanitize_text_field($omnify_data['first_name']);
			}
			if (! empty($omnify_data['last_name']) && empty($omnify_customer['last_name'])) {
				$omnify_update_data['last_name'] = sanitize_text_field($omnify_data['last_name']);
			}
			if (! empty($omnify_data['phone']) && empty($omnify_customer['phone'])) {
				$omnify_update_data['phone'] = sanitize_text_field($omnify_data['phone']);
			}
			if (! empty($omnify_update_data)) {
				$this->omnify_customers->update((int) $omnify_customer['id'], $omnify_update_data);
				$omnify_customer = $this->omnify_customers->find((int) $omnify_customer['id']);
			}
		}

		// Calculate base price dynamically based on variation or sale price
		if ($omnify_variation) {
			$omnify_price = (null !== $omnify_variation['sale_price'] && $omnify_variation['sale_price'] !== '') ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'];
		} else {
			$omnify_price = (null !== $omnify_product['sale_price'] && $omnify_product['sale_price'] !== '') ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price'];
		}
		$omnify_subtotal = $omnify_price * $omnify_quantity;

			// Calculate coupon discount if provided
			$omnify_coupon_code = sanitize_text_field($omnify_data['coupon_code'] ?? '');
			$omnify_discount_amount = 0.0;
			$omnify_coupon_id = null;
			$omnify_coupon_free_shipping = false;

			if (! empty($omnify_coupon_code)) {
				if (empty($omnify_settings['enable_coupons'])) {
					return new WP_Error('omnify_coupons_disabled', __('Coupons are not enabled for this checkout.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				$omnify_coupon_result = $this->evaluate_coupon($omnify_coupon_code, $omnify_product, $omnify_subtotal, $omnify_customer ? (int) $omnify_customer['id'] : null);
				if (is_wp_error($omnify_coupon_result)) {
					return $omnify_coupon_result;
				}
				$omnify_coupon_id = (int) $omnify_coupon_result['coupon']['id'];
				$omnify_discount_amount = (float) $omnify_coupon_result['discount'];
				$omnify_coupon_free_shipping = ! empty($omnify_coupon_result['free_shipping']);
			}

		// Determine payment method
		$omnify_payment_method_id = sanitize_text_field($omnify_data['payment_method'] ?? 'card');
		$omnify_is_manual   = false;
		$omnify_payment_instructions = '';

		if ($omnify_payment_method_id === 'card') {
			$omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']) && ! empty($omnify_settings['stripe_checkout_enabled']);
			if (! $omnify_stripe_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Card payments are not configured.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'paypal') {
			$omnify_paypal_enabled = ! empty($omnify_settings['paypal_enabled']) && ! empty($omnify_settings['paypal_checkout_enabled']);
			if (! $omnify_paypal_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('PayPal is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'razorpay') {
			$omnify_razorpay_enabled = ! empty($omnify_settings['razorpay_enabled']) && ! empty($omnify_settings['razorpay_checkout_enabled']);
			if (! $omnify_razorpay_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Razorpay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'alipay') {
			$omnify_alipay_enabled = ! empty($omnify_settings['alipay_enabled']) && ! empty($omnify_settings['alipay_checkout_enabled']);
			if (! $omnify_alipay_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Alipay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'wechat') {
			$omnify_wechat_enabled = ! empty($omnify_settings['wechat_enabled']) && ! empty($omnify_settings['wechat_checkout_enabled']);
			if (! $omnify_wechat_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('WeChat Pay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'sslcommerz') {
			$omnify_sslcommerz_enabled = ! empty($omnify_settings['sslcommerz_enabled']) && ! empty($omnify_settings['sslcommerz_checkout_enabled']);
			if (! $omnify_sslcommerz_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('SSLCommerz is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'paystack') {
			$omnify_paystack_enabled = ! empty($omnify_settings['paystack_enabled']) && ! empty($omnify_settings['paystack_checkout_enabled']);
			if (! $omnify_paystack_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Paystack is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'tap') {
			$omnify_tap_enabled = ! empty($omnify_settings['tap_enabled']) && ! empty($omnify_settings['tap_checkout_enabled']);
			if (! $omnify_tap_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Tap Payments is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'mollie') {
			$omnify_mollie_enabled = ! empty($omnify_settings['mollie_enabled']) && ! empty($omnify_settings['mollie_checkout_enabled']);
			if (! $omnify_mollie_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Mollie is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'khalti') {
			$omnify_khalti_enabled = ! empty($omnify_settings['khalti_enabled']) && ! empty($omnify_settings['khalti_checkout_enabled']);
			if (! $omnify_khalti_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Khalti is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_curr = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
			if ('NPR' !== $omnify_curr) {
				return new WP_Error('omnify_currency_unsupported', __('Khalti only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ($omnify_payment_method_id === 'esewa') {
			$omnify_esewa_enabled = ! empty($omnify_settings['esewa_enabled']) && ! empty($omnify_settings['esewa_checkout_enabled']);
			if (! $omnify_esewa_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('eSewa is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_curr = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
			if ('NPR' !== $omnify_curr) {
				return new WP_Error('omnify_currency_unsupported', __('eSewa only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} else {
			$omnify_methods = $omnify_settings['payment_methods'] ?? [];
			foreach ($omnify_methods as $omnify_method) {
				if ($omnify_method['id'] === $omnify_payment_method_id && ! empty($omnify_method['enabled'])) {
					$omnify_is_manual            = true;
					$omnify_payment_instructions = $omnify_method['instructions'] ?? '';
					break;
				}
			}

			if (! $omnify_is_manual) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Please select a valid payment method.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		}

		// Calculate tax and subtotal
		$omnify_subtotal     = $omnify_price * $omnify_quantity;
		$omnify_net_subtotal = max(0.0, $omnify_subtotal - $omnify_discount_amount);
		$omnify_shipping_country = $omnify_is_physical ? sanitize_text_field((string) ($omnify_data['shipping_country'] ?? '')) : '';
		$omnify_shipping_state   = $omnify_is_physical ? sanitize_text_field((string) ($omnify_data['shipping_state'] ?? '')) : '';
		$omnify_tax_rule     = $this->resolve_tax_rule($omnify_settings, $omnify_shipping_country, $omnify_shipping_state);
		$omnify_tax_rate     = (float) $omnify_tax_rule['rate'];
			$omnify_shipping_classes = $omnify_is_physical ? $this->collect_shipping_classes([['product' => $omnify_product, 'quantity' => $omnify_quantity]]) : [];
			$omnify_delivery     = $this->resolve_delivery_zone($omnify_settings, $omnify_shipping_country, $omnify_shipping_state, $omnify_net_subtotal, $omnify_is_physical, $omnify_shipping_classes, sanitize_text_field((string) ($omnify_data['shipping_method_id'] ?? '')));
			if (is_wp_error($omnify_delivery)) {
				return $omnify_delivery;
			}
			if ($omnify_coupon_free_shipping) {
				$omnify_delivery['amount'] = 0.0;
				$omnify_delivery['method'] = $omnify_delivery['method'] ?: __('Free shipping', 'omnifywp-ecommerce');
			}
			$omnify_shipping_total = (float) $omnify_delivery['amount'];
		$omnify_tax_breakdown = $this->calculate_tax_breakdown($omnify_net_subtotal, $omnify_shipping_total, $omnify_tax_rate, $omnify_settings, $this->customer_is_tax_exempt($omnify_customer ? (int) $omnify_customer['id'] : null));
		$omnify_tax          = (float) $omnify_tax_breakdown['tax'];
		$omnify_total        = (float) $omnify_tax_breakdown['total'];
		$omnify_customer_tax_exempt = $this->customer_is_tax_exempt($omnify_customer ? (int) $omnify_customer['id'] : null);

		// Order status: completed only if free ($0), otherwise pending_payment until verified by payment gateway/webhook
		$omnify_order_status = ($omnify_total <= 0.0) ? 'completed' : 'pending_payment';

		// Construct order item name containing variation details and SKU if applicable
		$omnify_item_name = $omnify_product['name'];
		if ($omnify_variation) {
			$omnify_attrs_str = '';
			if (! empty($omnify_variation['attributes'])) {
				$omnify_var_attrs = [];
				foreach ($omnify_variation['attributes'] as $omnify_k => $omnify_v) {
					$omnify_var_attrs[] = "$omnify_k: $omnify_v";
				}
				$omnify_attrs_str = implode(', ', $omnify_var_attrs);
			}
			$omnify_item_name .= ($omnify_attrs_str ? ' - ' . $omnify_attrs_str : '') . ($omnify_variation['sku'] ? ' (' . $omnify_variation['sku'] . ')' : '');
		} else {
			if (! empty($omnify_product['sku'])) {
				$omnify_item_name .= ' (' . $omnify_product['sku'] . ')';
			}
		}

		// Create order
		$omnify_order_id = $this->omnify_orders->create([
			'customer_id'          => $omnify_customer['id'],
			'status'               => $omnify_order_status,
			'currency'             => $omnify_product['currency'],
			'subtotal'             => $omnify_subtotal,
			'tax'                  => $omnify_tax,
			'tax_label'            => $omnify_tax_rule['label'],
			'tax_rate'             => $omnify_tax_rate,
			'tax_reporting_code'   => $omnify_tax_rule['reporting_code'] ?? '',
			'tax_inclusive'        => ! empty($omnify_settings['prices_include_tax']),
			'tax_shipping'         => ! empty($omnify_settings['tax_shipping']),
			'customer_tax_exempt'  => $omnify_customer_tax_exempt,
			'shipping_total'       => $omnify_shipping_total,
			'shipping_method'      => $omnify_is_physical ? $omnify_delivery['method'] : null,
			'total'                => $omnify_total,
			'coupon_code'          => $omnify_coupon_code ?: null,
			'discount_amount'      => $omnify_discount_amount,
			'payment_method'       => $omnify_payment_method_id,
			'payment_instructions' => $omnify_is_manual ? $omnify_payment_instructions : null,
			'shipping_first_name' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_first_name']) : null,
			'shipping_last_name'  => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_last_name']) : null,
			'shipping_address_1'  => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_address_1']) : null,
			'shipping_address_2'  => ($omnify_is_physical && ! empty($omnify_data['shipping_address_2'])) ? sanitize_text_field($omnify_data['shipping_address_2']) : null,
			'shipping_city'       => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_city']) : null,
			'shipping_state'      => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_state']) : null,
			'shipping_postcode'   => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_postcode']) : null,
			'shipping_country'    => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_country']) : null,
			'shipping_phone'      => ($omnify_is_physical && ! empty($omnify_data['shipping_phone'])) ? sanitize_text_field($omnify_data['shipping_phone']) : null,
			'billing_first_name'   => ! empty($omnify_data['billing_first_name']) ? sanitize_text_field($omnify_data['billing_first_name']) : null,
			'billing_last_name'    => ! empty($omnify_data['billing_last_name']) ? sanitize_text_field($omnify_data['billing_last_name']) : null,
			'billing_company'      => ! empty($omnify_data['billing_company']) ? sanitize_text_field($omnify_data['billing_company']) : null,
			'billing_phone'        => ! empty($omnify_data['billing_phone']) ? sanitize_text_field($omnify_data['billing_phone']) : null,
			'billing_address_1'    => ! empty($omnify_data['billing_address_1']) ? sanitize_text_field($omnify_data['billing_address_1']) : null,
			'billing_address_2'    => ! empty($omnify_data['billing_address_2']) ? sanitize_text_field($omnify_data['billing_address_2']) : null,
			'billing_city'         => ! empty($omnify_data['billing_city']) ? sanitize_text_field($omnify_data['billing_city']) : null,
			'billing_state'        => ! empty($omnify_data['billing_state']) ? sanitize_text_field($omnify_data['billing_state']) : null,
			'billing_postcode'     => ! empty($omnify_data['billing_postcode']) ? sanitize_text_field($omnify_data['billing_postcode']) : null,
			'billing_country'      => ! empty($omnify_data['billing_country']) ? sanitize_text_field($omnify_data['billing_country']) : null,
			'billing_same_as_shipping' => isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1,
			'fulfillment_status'  => $omnify_is_physical ? 'unfulfilled' : 'none',
			'items'                => [
				[
					'product_id'   => $omnify_product['id'],
					'variation_id' => $omnify_variation ? (int) $omnify_variation['id'] : null,
					'product_name' => $omnify_item_name,
					'price'        => $omnify_price,
					'tax'          => (float) ($omnify_tax_breakdown['product_tax'] ?? $omnify_tax),
					'quantity'     => $omnify_quantity,
				],
			],
		]);

		if ($omnify_order_id) {
			$this->reduce_managed_stock_for_checkout($omnify_product, $omnify_variation, $omnify_quantity, $omnify_settings, (int) $omnify_order_id);
		}

		if ($omnify_coupon_id) {
			$this->omnify_coupons->increment_usage($omnify_coupon_id);
		}

		$this->mark_abandoned_cart_recovered_from_payload($omnify_data, (int) $omnify_order_id);

		$omnify_files = [];

		if ('completed' === $omnify_order_status) {
			if (! $omnify_is_physical) {
				// Free digital order: grant access immediately, send receipt
				$this->omnify_access->grant((int) $omnify_customer['id'], (int) $omnify_product['id'], $this->download_access_expires_at($omnify_product));
			}

			// Record activity
			$this->omnify_customers->record_activity(
				(int) $omnify_customer['id'],
				'purchased',
				// translators: %1$s: placeholder value, %2$d: placeholder value.
				sprintf(__('Purchased product: %1$s (Order #%2$d)', 'omnifywp-ecommerce'), $omnify_product['name'], $omnify_order_id),
				['order_id' => $omnify_order_id, 'product_id' => $omnify_product['id']]
			);

			// Send receipt email
			$this->omnify_emails->send_receipt($omnify_order_id);
			$this->omnify_emails->send_admin_new_order($omnify_order_id);

			if (! $omnify_is_physical) {
				// Get download links
				$omnify_target_pids = [(int) $omnify_product['id']];
				if ('bundle' === $omnify_product['type']) {
					$omnify_target_pids = ! empty($omnify_product['bundled_ids']) && is_array($omnify_product['bundled_ids']) ? $omnify_product['bundled_ids'] : [];
				}
				foreach ($omnify_target_pids as $omnify_pid) {
					foreach ($this->omnify_product_files->active_for_product((int) $omnify_pid) as $omnify_file) {
						$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], 3600 * 24, (int) $omnify_customer['id']);
						$omnify_files[] = $omnify_file;
					}
				}
			}
		} elseif ($omnify_is_manual) {
			// Manual payment: send pending email, do NOT grant access yet
			$this->omnify_emails->send_payment_pending($omnify_order_id);
		}

		$omnify_created_order = $this->omnify_orders->find($omnify_order_id);
		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'order_number' => $omnify_created_order ? $omnify_created_order['order_number'] : '#' . $omnify_order_id,
			'status'       => $omnify_order_status,
			'payment_method' => $omnify_payment_method_id,
			'is_manual'      => $omnify_is_manual,
			'is_physical' => $omnify_is_physical,
			'totals'         => [
				'subtotal'        => $omnify_subtotal,
				'discount'        => $omnify_discount_amount,
				'tax'             => $omnify_tax,
				'tax_label'       => $omnify_tax_rule['label'],
				'tax_rate'        => $omnify_tax_rate,
				'shipping_total'  => $omnify_shipping_total,
				'shipping_method' => $omnify_is_physical ? $omnify_delivery['method'] : '',
				'total'           => $omnify_total,
			],
			'customer'       => $omnify_customer,
			'product'        => $omnify_product,
			'files'          => ('completed' === $omnify_order_status) ? $omnify_files : [],
		]);
	}

	private function checkout_cart(array $omnify_data): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();

		$omnify_cart_items = $this->resolve_checkout_items($omnify_data, $omnify_settings);
		if (is_wp_error($omnify_cart_items)) {
			return $omnify_cart_items;
		}

		$omnify_has_physical = ! empty(array_filter($omnify_cart_items, static fn(array $omnify_item): bool => ! empty($omnify_item['is_physical'])));

		$omnify_val_err = $this->validate_checkout_payload($omnify_data, $omnify_settings, $omnify_cart_items, $omnify_has_physical, 'manual');
		if (is_wp_error($omnify_val_err)) {
			return $omnify_val_err;
		}

		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_customer = $this->resolve_checkout_customer($omnify_data, $omnify_settings, $omnify_email);
		if (is_wp_error($omnify_customer)) {
			return $omnify_customer;
		}

		$omnify_coupon_code = sanitize_text_field((string) ($omnify_data['coupon_code'] ?? ''));
		$omnify_totals = $this->calculate_checkout_totals($omnify_cart_items, $omnify_settings, $omnify_coupon_code, $omnify_customer ? (int) $omnify_customer['id'] : null, $omnify_has_physical, $omnify_data);
		if (is_wp_error($omnify_totals)) {
			return $omnify_totals;
		}

		$omnify_payment_method_id    = sanitize_text_field((string) ($omnify_data['payment_method'] ?? 'card'));
		$omnify_is_manual           = false;
		$omnify_payment_instructions = '';
		if ('card' === $omnify_payment_method_id) {
			$omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']) && ! empty($omnify_settings['stripe_checkout_enabled']);
			if (! $omnify_stripe_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Card payments are not configured.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('paypal' === $omnify_payment_method_id) {
			$omnify_paypal_enabled = ! empty($omnify_settings['paypal_enabled']) && ! empty($omnify_settings['paypal_checkout_enabled']);
			if (! $omnify_paypal_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('PayPal is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('razorpay' === $omnify_payment_method_id) {
			$omnify_razorpay_enabled = ! empty($omnify_settings['razorpay_enabled']) && ! empty($omnify_settings['razorpay_checkout_enabled']);
			if (! $omnify_razorpay_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Razorpay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('alipay' === $omnify_payment_method_id) {
			$omnify_alipay_enabled = ! empty($omnify_settings['alipay_enabled']) && ! empty($omnify_settings['alipay_checkout_enabled']);
			if (! $omnify_alipay_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Alipay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('wechat' === $omnify_payment_method_id) {
			$omnify_wechat_enabled = ! empty($omnify_settings['wechat_enabled']) && ! empty($omnify_settings['wechat_checkout_enabled']);
			if (! $omnify_wechat_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('WeChat Pay is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('sslcommerz' === $omnify_payment_method_id) {
			$omnify_sslcommerz_enabled = ! empty($omnify_settings['sslcommerz_enabled']) && ! empty($omnify_settings['sslcommerz_checkout_enabled']);
			if (! $omnify_sslcommerz_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('SSLCommerz is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('paystack' === $omnify_payment_method_id) {
			$omnify_paystack_enabled = ! empty($omnify_settings['paystack_enabled']) && ! empty($omnify_settings['paystack_checkout_enabled']);
			if (! $omnify_paystack_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Paystack is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('tap' === $omnify_payment_method_id) {
			$omnify_tap_enabled = ! empty($omnify_settings['tap_enabled']) && ! empty($omnify_settings['tap_checkout_enabled']);
			if (! $omnify_tap_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Tap Payments is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('mollie' === $omnify_payment_method_id) {
			$omnify_mollie_enabled = ! empty($omnify_settings['mollie_enabled']) && ! empty($omnify_settings['mollie_checkout_enabled']);
			if (! $omnify_mollie_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Mollie is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('khalti' === $omnify_payment_method_id) {
			$omnify_khalti_enabled = ! empty($omnify_settings['khalti_enabled']) && ! empty($omnify_settings['khalti_checkout_enabled']);
			if (! $omnify_khalti_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Khalti is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_curr = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
			if ('NPR' !== $omnify_curr) {
				return new WP_Error('omnify_currency_unsupported', __('Khalti only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('esewa' === $omnify_payment_method_id) {
			$omnify_esewa_enabled = ! empty($omnify_settings['esewa_enabled']) && ! empty($omnify_settings['esewa_checkout_enabled']);
			if (! $omnify_esewa_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('eSewa is not enabled.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_curr = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
			if ('NPR' !== $omnify_curr) {
				return new WP_Error('omnify_currency_unsupported', __('eSewa only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} else {
			foreach (($omnify_settings['payment_methods'] ?? []) as $omnify_method) {
				if (($omnify_method['id'] ?? '') === $omnify_payment_method_id && ! empty($omnify_method['enabled'])) {
					$omnify_is_manual            = true;
					$omnify_payment_instructions = (string) ($omnify_method['instructions'] ?? '');
					break;
				}
			}
			if (! $omnify_is_manual) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Please select a valid payment method.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		}

		$omnify_order_status = ((float) $omnify_totals['total'] <= 0.0) ? 'completed' : 'pending_payment';

		$omnify_order_id = $this->omnify_orders->create([
			'customer_id'          => $omnify_customer['id'],
			'status'               => $omnify_order_status,
			'currency'             => $omnify_totals['currency'],
			'subtotal'             => $omnify_totals['subtotal'],
			'tax'                  => $omnify_totals['tax'],
			'tax_label'            => $omnify_totals['tax_label'],
			'tax_rate'             => $omnify_totals['tax_rate'],
			'tax_reporting_code'   => $omnify_totals['tax_reporting_code'],
			'tax_inclusive'        => $omnify_totals['prices_include_tax'],
			'tax_shipping'         => $omnify_totals['tax_shipping'],
			'customer_tax_exempt'  => $omnify_totals['tax_exempt'],
			'shipping_total'       => $omnify_totals['shipping_total'],
			'shipping_method'      => $omnify_has_physical ? $omnify_totals['shipping_method'] : null,
			'total'                => $omnify_totals['total'],
			'coupon_code'          => $omnify_coupon_code ?: null,
			'discount_amount'      => $omnify_totals['discount'],
			'payment_method'       => $omnify_payment_method_id,
			'payment_instructions' => $omnify_is_manual ? $omnify_payment_instructions : null,
			'shipping_first_name'  => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_first_name']) : null,
			'shipping_last_name'   => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_last_name']) : null,
			'shipping_address_1'   => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_address_1']) : null,
			'shipping_address_2'   => ($omnify_has_physical && ! empty($omnify_data['shipping_address_2'])) ? sanitize_text_field((string) $omnify_data['shipping_address_2']) : null,
			'shipping_city'        => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_city']) : null,
			'shipping_state'       => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_state']) : null,
			'shipping_postcode'    => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_postcode']) : null,
			'shipping_country'     => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_country']) : null,
			'shipping_phone'       => ($omnify_has_physical && ! empty($omnify_data['shipping_phone'])) ? sanitize_text_field((string) $omnify_data['shipping_phone']) : null,
			'billing_first_name'   => ! empty($omnify_data['billing_first_name']) ? sanitize_text_field($omnify_data['billing_first_name']) : null,
			'billing_last_name'    => ! empty($omnify_data['billing_last_name']) ? sanitize_text_field($omnify_data['billing_last_name']) : null,
			'billing_company'      => ! empty($omnify_data['billing_company']) ? sanitize_text_field($omnify_data['billing_company']) : null,
			'billing_phone'        => ! empty($omnify_data['billing_phone']) ? sanitize_text_field($omnify_data['billing_phone']) : null,
			'billing_address_1'    => ! empty($omnify_data['billing_address_1']) ? sanitize_text_field($omnify_data['billing_address_1']) : null,
			'billing_address_2'    => ! empty($omnify_data['billing_address_2']) ? sanitize_text_field($omnify_data['billing_address_2']) : null,
			'billing_city'         => ! empty($omnify_data['billing_city']) ? sanitize_text_field($omnify_data['billing_city']) : null,
			'billing_state'        => ! empty($omnify_data['billing_state']) ? sanitize_text_field($omnify_data['billing_state']) : null,
			'billing_postcode'     => ! empty($omnify_data['billing_postcode']) ? sanitize_text_field($omnify_data['billing_postcode']) : null,
			'billing_country'      => ! empty($omnify_data['billing_country']) ? sanitize_text_field($omnify_data['billing_country']) : null,
			'billing_same_as_shipping' => isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1,
			'fulfillment_status'   => $omnify_has_physical ? 'unfulfilled' : 'none',
			'items'                => array_map(
				static fn(array $omnify_item): array => [
					'product_id'   => (int) $omnify_item['product']['id'],
					'variation_id' => $omnify_item['variation'] ? (int) $omnify_item['variation']['id'] : null,
					'product_name' => $omnify_item['item_name'],
					'price'        => $omnify_item['price'],
					'tax'          => $omnify_item['line_tax'],
					'quantity'     => $omnify_item['quantity'],
				],
				$omnify_totals['items']
			),
		]);

		if ($omnify_order_id) {
			foreach ($omnify_cart_items as $omnify_item) {
				$this->reduce_managed_stock_for_checkout($omnify_item['product'], $omnify_item['variation'], $omnify_item['quantity'], $omnify_settings, (int) $omnify_order_id);
			}
		}
		if (! empty($omnify_totals['coupon_id'])) {
			$this->omnify_coupons->increment_usage((int) $omnify_totals['coupon_id']);
		}

		$this->mark_abandoned_cart_recovered_from_payload($omnify_data, (int) $omnify_order_id);

		$omnify_files = [];
		if ('completed' === $omnify_order_status) {
			foreach ($omnify_cart_items as $omnify_item) {
				if (empty($omnify_item['is_physical'])) {
					$this->omnify_access->grant((int) $omnify_customer['id'], (int) $omnify_item['product']['id'], $this->download_access_expires_at($omnify_item['product']));
					$omnify_target_pids = [(int) $omnify_item['product']['id']];
					if ('bundle' === $omnify_item['product']['type']) {
						$omnify_target_pids = ! empty($omnify_item['product']['bundled_ids']) && is_array($omnify_item['product']['bundled_ids']) ? $omnify_item['product']['bundled_ids'] : [];
					}
					foreach ($omnify_target_pids as $omnify_pid) {
						foreach ($this->omnify_product_files->active_for_product((int) $omnify_pid) as $omnify_file) {
							$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], 3600 * 24, (int) $omnify_customer['id']);
							$omnify_files[] = $omnify_file;
						}
					}
				}
			}
			// translators: %1$d: placeholder value, %2$d: placeholder value.
			$this->omnify_customers->record_activity((int) $omnify_customer['id'], 'purchased', sprintf(__('Purchased %1$d item(s) (Order #%2$d)', 'omnifywp-ecommerce'), count($omnify_cart_items), $omnify_order_id), ['order_id' => $omnify_order_id]);
			$this->omnify_emails->send_receipt($omnify_order_id);
			$this->omnify_emails->send_admin_new_order($omnify_order_id);
		} elseif ($omnify_is_manual) {
			$this->omnify_emails->send_payment_pending($omnify_order_id);
		}

		$omnify_created_order = $this->omnify_orders->find($omnify_order_id);
		return rest_ensure_response([
			'success'        => true,
			'order_id'       => $omnify_order_id,
			'order_number'   => $omnify_created_order ? $omnify_created_order['order_number'] : '#' . $omnify_order_id,
			'status'         => $omnify_order_status,
			'payment_method' => $omnify_payment_method_id,
			'is_manual'      => $omnify_is_manual,
			'is_physical'    => $omnify_has_physical,
			'totals'         => [
				'subtotal'        => $omnify_totals['subtotal'],
				'discount'        => $omnify_totals['discount'],
				'tax'             => $omnify_totals['tax'],
				'tax_label'       => $omnify_totals['tax_label'],
				'tax_rate'        => $omnify_totals['tax_rate'],
				'shipping_total'  => $omnify_totals['shipping_total'],
				'shipping_method' => $omnify_totals['shipping_method'],
				'total'           => $omnify_totals['total'],
			],
			'customer'       => $omnify_customer,
			'items'          => array_map(static fn(array $omnify_item): array => [
				'product_id' => (int) $omnify_item['product']['id'],
				'variation_id' => $omnify_item['variation'] ? (int) $omnify_item['variation']['id'] : null,
				'product_name' => $omnify_item['item_name'],
				'quantity' => $omnify_item['quantity'],
			], $omnify_cart_items),
			'files'          => ('completed' === $omnify_order_status) ? $omnify_files : [],
		]);
	}

	private function cart_session_token(WP_REST_Request $omnify_request): string {
		$omnify_cookie_token = isset($_COOKIE['omnify_cart_token']) ? sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_cart_token'])) : '';
		$omnify_token = sanitize_text_field((string) ($omnify_request->get_header('x-omnify-cart-token') ?: $omnify_cookie_token));
		if ('' === $omnify_token) {
			$omnify_token = wp_generate_uuid4();
		}

		if (! headers_sent()) {
			setcookie('omnify_cart_token', $omnify_token, [
				'expires'  => time() + (30 * DAY_IN_SECONDS),
				'path'     => COOKIEPATH ?: '/',
				'domain'   => COOKIE_DOMAIN ?: '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			]);
		}

		return $omnify_token;
	}

	private function cart_transient_key(string $omnify_token): string {
		return 'omnify_cart_' . md5($omnify_token);
	}

	private function cart_session_items(string $omnify_token): array {
		$omnify_items = get_transient($this->cart_transient_key($omnify_token));

		return is_array($omnify_items) ? $omnify_items : [];
	}

	private function sanitize_cart_session_items(mixed $omnify_items): array {
		if (! is_array($omnify_items)) {
			return [];
		}

		$omnify_clean = [];
		foreach ($omnify_items as $omnify_item) {
			if (! is_array($omnify_item)) {
				continue;
			}
			$omnify_product_id = absint($omnify_item['product_id'] ?? $omnify_item['id'] ?? 0);
			if (! $omnify_product_id) {
				continue;
			}
			$omnify_key = $omnify_product_id . ':' . absint($omnify_item['variation_id'] ?? $omnify_item['variationId'] ?? 0);
			$omnify_clean[$omnify_key] = [
				'product_id'   => $omnify_product_id,
				'variation_id' => absint($omnify_item['variation_id'] ?? $omnify_item['variationId'] ?? 0) ?: null,
				'quantity'     => max(1, absint($omnify_item['quantity'] ?? 1)),
			];
		}

		return array_values($omnify_clean);
	}

	private function payload_checkout_items(array $omnify_data): array {
		if (! empty($omnify_data['items']) && is_array($omnify_data['items'])) {
			return $omnify_data['items'];
		}

		if (! empty($omnify_data['cart_token'])) {
			$omnify_items = $this->cart_session_items(sanitize_text_field((string) $omnify_data['cart_token']));
			if (! empty($omnify_items)) {
				return $omnify_items;
			}
		}

		return [
			[
				'product_id'   => absint($omnify_data['product_id'] ?? 0),
				'variation_id' => absint($omnify_data['variation_id'] ?? 0) ?: null,
				'quantity'     => max(1, absint($omnify_data['quantity'] ?? 1)),
			],
		];
	}

	private function resolve_checkout_items(array $omnify_data, array $omnify_settings): array|WP_Error {
		$omnify_items = $this->sanitize_cart_session_items($this->payload_checkout_items($omnify_data));
		if (empty($omnify_items)) {
			return new WP_Error('omnify_checkout_cart_empty', __('Your cart is empty.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_resolved = [];
		$omnify_currency = '';
		foreach ($omnify_items as $omnify_item) {
			$omnify_product = $this->omnify_products->find((int) $omnify_item['product_id']);
			if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
				return new WP_Error('omnify_checkout_product_not_found', __('One or more products are not available for purchase.', 'omnifywp-ecommerce'), ['status' => 404]);
			}
			if ('' === $omnify_currency) {
				$omnify_currency = (string) ($omnify_product['currency'] ?? ($omnify_settings['default_currency'] ?? 'USD'));
			} elseif ($omnify_currency !== (string) ($omnify_product['currency'] ?? $omnify_currency)) {
				return new WP_Error('omnify_checkout_currency_mismatch', __('All cart items must use the same currency.', 'omnifywp-ecommerce'), ['status' => 400]);
			}

			$omnify_variation = null;
			$omnify_variation_id = absint($omnify_item['variation_id'] ?? 0);
			if ('variable' === ($omnify_product['type'] ?? '') && ! $omnify_variation_id) {
				return new WP_Error('omnify_checkout_variation_required', __('Please select a product variation for each variable product.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			if ($omnify_variation_id && ! empty($omnify_product['variations']) && is_array($omnify_product['variations'])) {
				foreach ($omnify_product['variations'] as $omnify_candidate) {
					if ((int) ($omnify_candidate['id'] ?? 0) === $omnify_variation_id) {
						$omnify_variation = $omnify_candidate;
						break;
					}
				}
			}
			if ($omnify_variation_id && ! $omnify_variation) {
				return new WP_Error('omnify_checkout_variation_not_found', __('One or more selected variations are not available.', 'omnifywp-ecommerce'), ['status' => 422]);
			}

			$omnify_quantity = max(1, absint($omnify_item['quantity'] ?? 1));
			if ($omnify_variation) {
				$omnify_variation_allows_oversell = ! empty($omnify_variation['allow_backorders']) || ! empty($omnify_variation['preorder_enabled']);
				if ('outofstock' === ($omnify_variation['stock_status'] ?? '') && ! $omnify_variation_allows_oversell) {
					return new WP_Error('omnify_checkout_out_of_stock', __('A selected variation is out of stock.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				if (! empty($omnify_variation['manage_stock']) && (int) ($omnify_variation['stock_qty'] ?? 0) < $omnify_quantity && ! $omnify_variation_allows_oversell) {
					return new WP_Error('omnify_checkout_out_of_stock', __('A selected variation does not have enough stock.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				$omnify_preorder_limit = (int) ($omnify_variation['preorder_limit'] ?? 0);
				if (! empty($omnify_variation['preorder_enabled']) && $omnify_preorder_limit > 0 && $omnify_quantity > $omnify_preorder_limit) {
					// translators: %1$s: placeholder value, %2$d: placeholder value.
					return new WP_Error('omnify_checkout_preorder_limit_exceeded', sprintf(__('Preorder quantity for %1$s is limited to %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_preorder_limit), ['status' => 400]);
				}
				$omnify_price = ((null !== ($omnify_variation['sale_price'] ?? null) && '' !== $omnify_variation['sale_price']) ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price']);
			} else {
				$omnify_product_allows_oversell = ! empty($omnify_product['allow_backorders']) || ! empty($omnify_product['preorder_enabled']);
				if ('outofstock' === ($omnify_product['stock_status'] ?? '') && ! $omnify_product_allows_oversell) {
					return new WP_Error('omnify_checkout_out_of_stock', __('A product in your cart is out of stock.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				if (! empty($omnify_product['manage_stock']) && (int) ($omnify_product['stock_qty'] ?? 0) < $omnify_quantity && ! $omnify_product_allows_oversell) {
					return new WP_Error('omnify_checkout_out_of_stock', __('A product in your cart does not have enough stock.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				$omnify_preorder_limit = (int) ($omnify_product['preorder_limit'] ?? 0);
				if (! empty($omnify_product['preorder_enabled']) && $omnify_preorder_limit > 0 && $omnify_quantity > $omnify_preorder_limit) {
					// translators: %1$s: placeholder value, %2$d: placeholder value.
					return new WP_Error('omnify_checkout_preorder_limit_exceeded', sprintf(__('Preorder quantity for %1$s is limited to %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_preorder_limit), ['status' => 400]);
				}
				$omnify_price = ((null !== ($omnify_product['sale_price'] ?? null) && '' !== $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price']);
			}
			$omnify_max_qty = isset($omnify_product['max_purchase_qty']) ? (int) $omnify_product['max_purchase_qty'] : 0;
			if ($omnify_max_qty > 0 && $omnify_quantity > $omnify_max_qty) {
				// translators: %1$s: placeholder value, %2$d: placeholder value.
				return new WP_Error('omnify_checkout_max_qty_exceeded', sprintf(__('Maximum purchase quantity for %1$s is %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_max_qty), ['status' => 400]);
			}

			$omnify_item_name = (string) $omnify_product['name'];
			if ($omnify_variation) {
				$omnify_variation_bits = [];
				foreach (($omnify_variation['attributes'] ?? []) as $omnify_attribute_name => $omnify_attribute_value) {
					$omnify_variation_bits[] = $omnify_attribute_name . ': ' . $omnify_attribute_value;
				}
				$omnify_item_name .= empty($omnify_variation_bits) ? '' : ' - ' . implode(', ', $omnify_variation_bits);
				$omnify_item_name .= empty($omnify_variation['sku']) ? '' : ' (' . $omnify_variation['sku'] . ')';
			} elseif (! empty($omnify_product['sku'])) {
				$omnify_item_name .= ' (' . $omnify_product['sku'] . ')';
			}

			$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
			$omnify_is_physical = ('physical' === ($omnify_product['type'] ?? 'download')) || ('variable' === ($omnify_product['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
			$omnify_resolved[] = [
				'product'     => $omnify_product,
				'variation'   => $omnify_variation,
				'quantity'    => $omnify_quantity,
				'price'       => $omnify_price,
				'line_total'  => $omnify_price * $omnify_quantity,
				'line_tax'    => 0.0,
				'item_name'   => $omnify_item_name,
				'is_physical' => $omnify_is_physical,
			];
		}

		return $omnify_resolved;
	}

	private function resolve_checkout_customer(array $omnify_data, array $omnify_settings, string $omnify_email): array|WP_Error {
		$omnify_account_mode = in_array($omnify_settings['account_creation_mode'] ?? '', ['optional', 'automatic', 'required'], true)
			? (string) $omnify_settings['account_creation_mode']
			: (! empty($omnify_settings['require_account_on_checkout']) ? 'required' : 'optional');
		$omnify_wp_user_id = get_current_user_id() ?: (email_exists($omnify_email) ?: null);

		$omnify_should_create_account = ! empty($omnify_data['create_account']) || in_array($omnify_account_mode, ['automatic', 'required'], true);
		if ($omnify_should_create_account && ! $omnify_wp_user_id && is_email($omnify_email)) {
			$omnify_existing_uid = email_exists($omnify_email);
			if ($omnify_existing_uid) {
				$omnify_wp_user_id = (int) $omnify_existing_uid;
			} else {
				$omnify_password = ! empty($omnify_data['password']) ? (string) $omnify_data['password'] : wp_generate_password(14, true);
				$omnify_username_base = sanitize_user(current(explode('@', $omnify_email)), true);
				$omnify_username = $omnify_username_base ?: 'customer';
				$omnify_i = 1;
				while (username_exists($omnify_username)) {
					$omnify_username = $omnify_username_base . $omnify_i;
					$omnify_i++;
				}
				$omnify_first = sanitize_text_field((string) ($omnify_data['first_name'] ?? ''));
				$omnify_last  = sanitize_text_field((string) ($omnify_data['last_name'] ?? ''));
				$omnify_new_uid = wp_insert_user([
					'user_login'   => $omnify_username,
					'user_email'   => $omnify_email,
					'user_pass'    => $omnify_password,
					'first_name'   => $omnify_first,
					'last_name'    => $omnify_last,
					'display_name' => trim("$omnify_first $omnify_last") ?: $omnify_username,
					'role'         => 'subscriber',
				]);
				if (! is_wp_error($omnify_new_uid)) {
					$omnify_wp_user_id = (int) $omnify_new_uid;
					if (! is_user_logged_in() && function_exists('wp_set_current_user') && function_exists('wp_set_auth_cookie')) {
						wp_set_current_user($omnify_wp_user_id);
						wp_set_auth_cookie($omnify_wp_user_id);
					}
				}
			}
		}

		$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
		$omnify_addr_fields = [
			'shipping_first_name', 'shipping_last_name', 'shipping_phone', 'shipping_company', 
			'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country',
			'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_company', 
			'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country'
		];

		if (! $omnify_customer) {
			$omnify_customer_args = [
				'email' => $omnify_email,
				'user_id' => $omnify_wp_user_id,
				'first_name' => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
				'last_name' => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
				'phone' => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
				'status' => 'active',
			];
			foreach ($omnify_addr_fields as $omnify_f) {
				if (isset($omnify_data[$omnify_f])) {
					$omnify_customer_args[$omnify_f] = sanitize_text_field((string) $omnify_data[$omnify_f]);
				}
			}
			$omnify_customer_id = $this->omnify_customers->create($omnify_customer_args);
			return $this->omnify_customers->find($omnify_customer_id) ?: [];
		}

		$omnify_update_data = [];
		if ($omnify_wp_user_id && empty($omnify_customer['user_id'])) {
			$omnify_update_data['user_id'] = $omnify_wp_user_id;
		}
		foreach (['first_name' => 'first_name', 'last_name' => 'last_name', 'phone' => 'phone'] as $omnify_input => $omnify_column) {
			if (! empty($omnify_data[$omnify_input]) && empty($omnify_customer[$omnify_column])) {
				$omnify_update_data[$omnify_column] = sanitize_text_field((string) $omnify_data[$omnify_input]);
			}
		}
		foreach ($omnify_addr_fields as $omnify_f) {
			if (! empty($omnify_data[$omnify_f]) && empty($omnify_customer[$omnify_f])) {
				$omnify_update_data[$omnify_f] = sanitize_text_field((string) $omnify_data[$omnify_f]);
			}
		}
		if (! empty($omnify_update_data)) {
			$this->omnify_customers->update((int) $omnify_customer['id'], $omnify_update_data);
			$omnify_customer = $this->omnify_customers->find((int) $omnify_customer['id']) ?: $omnify_customer;
		}

		return $omnify_customer;
	}

	private function calculate_checkout_totals(array $omnify_items, array $omnify_settings, string $omnify_coupon_code, ?int $omnify_customer_id, bool $omnify_has_physical, array $omnify_data): array|WP_Error {
		$omnify_subtotal = array_reduce($omnify_items, static fn(float $omnify_sum, array $omnify_item): float => $omnify_sum + (float) $omnify_item['line_total'], 0.0);
		$omnify_discount = 0.0;
		$omnify_coupon_id = null;
		$omnify_coupon_free_shipping = false;

		if ('' !== $omnify_coupon_code) {
			if (empty($omnify_settings['enable_coupons'])) {
				return new WP_Error('omnify_coupons_disabled', __('Coupons are not enabled for this checkout.', 'omnifywp-ecommerce'), ['status' => 400]);
			}
			$omnify_coupon_result = $this->evaluate_coupon_for_items($omnify_coupon_code, $omnify_items, $omnify_subtotal, $omnify_customer_id);
			if (is_wp_error($omnify_coupon_result)) {
				return $omnify_coupon_result;
			}
			$omnify_coupon_id = (int) $omnify_coupon_result['coupon']['id'];
			$omnify_discount = (float) $omnify_coupon_result['discount'];
			$omnify_coupon_free_shipping = ! empty($omnify_coupon_result['free_shipping']);
		}

		$omnify_net_subtotal = max(0.0, $omnify_subtotal - $omnify_discount);
		$omnify_shipping_country = $omnify_has_physical ? sanitize_text_field((string) ($omnify_data['shipping_country'] ?? '')) : '';
		$omnify_shipping_state   = $omnify_has_physical ? sanitize_text_field((string) ($omnify_data['shipping_state'] ?? '')) : '';
		$omnify_tax_rule = $this->resolve_tax_rule($omnify_settings, $omnify_shipping_country, $omnify_shipping_state);
		$omnify_tax_rate = (float) $omnify_tax_rule['rate'];
		$omnify_delivery = $this->resolve_delivery_zone($omnify_settings, $omnify_shipping_country, $omnify_shipping_state, $omnify_net_subtotal, $omnify_has_physical, $this->collect_shipping_classes($omnify_items), sanitize_text_field((string) ($omnify_data['shipping_method_id'] ?? '')));
		if (is_wp_error($omnify_delivery)) {
			return $omnify_delivery;
		}
		if ($omnify_coupon_free_shipping) {
			$omnify_delivery['amount'] = 0.0;
			$omnify_delivery['method'] = $omnify_delivery['method'] ?: __('Free shipping', 'omnifywp-ecommerce');
		}
		$omnify_shipping_total = (float) $omnify_delivery['amount'];
		$omnify_tax_exempt = $this->customer_is_tax_exempt($omnify_customer_id);
		$omnify_tax_breakdown = $this->calculate_tax_breakdown($omnify_net_subtotal, $omnify_shipping_total, $omnify_tax_rate, $omnify_settings, $omnify_tax_exempt);
		$omnify_tax = (float) $omnify_tax_breakdown['tax'];
		$omnify_product_tax_total = (float) ($omnify_tax_breakdown['product_tax'] ?? $omnify_tax);
		$omnify_shipping_tax_total = (float) ($omnify_tax_breakdown['shipping_tax'] ?? 0.0);

		foreach ($omnify_items as $omnify_index => $omnify_item) {
			$omnify_share = $omnify_subtotal > 0 ? ((float) $omnify_item['line_total'] / $omnify_subtotal) : 0;
			$omnify_line_tax = $omnify_product_tax_total * $omnify_share;
			$omnify_items[$omnify_index]['line_tax'] = ('line' === ($omnify_settings['tax_rounding'] ?? 'line')) ? $this->round_tax($omnify_line_tax, $omnify_settings) : $omnify_line_tax;
		}
		if ('line' === ($omnify_settings['tax_rounding'] ?? 'line')) {
			$omnify_product_tax_total = array_reduce($omnify_items, static fn(float $omnify_sum, array $omnify_item): float => $omnify_sum + (float) $omnify_item['line_tax'], 0.0);
			$omnify_tax = $omnify_product_tax_total + $omnify_shipping_tax_total;
			$omnify_tax_breakdown['tax'] = $omnify_tax;
			$omnify_tax_breakdown['product_tax'] = $omnify_product_tax_total;
			$omnify_tax_breakdown['total'] = ! empty($omnify_settings['prices_include_tax'])
				? ((float) $omnify_tax_breakdown['taxable_subtotal'] + $omnify_shipping_total + $omnify_shipping_tax_total)
				: ($omnify_net_subtotal + $omnify_shipping_total + $omnify_tax);
		}

		return [
			'items' => $omnify_items,
			'currency' => (string) ($omnify_items[0]['product']['currency'] ?? ($omnify_settings['default_currency'] ?? 'USD')),
			'subtotal' => $omnify_subtotal,
			'discount' => $omnify_discount,
			'tax' => $omnify_tax,
			'tax_label' => $omnify_tax_rule['label'],
			'tax_rate' => $omnify_tax_rate,
			'tax_reporting_code' => (string) ($omnify_tax_rule['reporting_code'] ?? ''),
			'tax_exempt' => $omnify_tax_exempt,
			'prices_include_tax' => ! empty($omnify_settings['prices_include_tax']),
			'tax_shipping' => ! empty($omnify_settings['tax_shipping']),
			'shipping_total' => $omnify_shipping_total,
			'shipping_method' => (string) $omnify_delivery['method'],
			'total' => (float) $omnify_tax_breakdown['total'],
			'coupon_id' => $omnify_coupon_id,
		];
	}

	public function get_payment_methods(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_methods  = $omnify_settings['payment_methods'] ?? [];
		$omnify_enabled  = array_values(array_filter($omnify_methods, fn($omnify_m) => ! empty($omnify_m['enabled'])));

		return rest_ensure_response($omnify_enabled);
	}

	public function create_stripe_checkout_session(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['stripe_enabled']) || empty($omnify_settings['stripe_checkout_enabled'])) {
			return new WP_Error('omnify_stripe_disabled', __('Stripe Checkout is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_secret_key = $this->stripe_secret_key($omnify_settings);
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_stripe_key_missing', __('Stripe secret key is missing.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_context = $this->prepare_stripe_order_context((array) $omnify_request->get_json_params(), 'stripe_checkout');
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order = $this->omnify_orders->find($omnify_context['order_id']);
		$omnify_order_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);

		$omnify_success_url = add_query_arg(
			['omnify_stripe' => 'success', 'order_id' => $omnify_context['order_id'], 'order_key' => $omnify_order_key, 'session_id' => '{CHECKOUT_SESSION_ID}'],
			(string) (wp_get_referer() ?: home_url('/'))
		);
		$omnify_cancel_url = add_query_arg(['omnify_stripe' => 'cancelled', 'order_id' => $omnify_context['order_id'], 'order_key' => $omnify_order_key], (string) (wp_get_referer() ?: home_url('/')));

		$omnify_session_args = [
			'mode' => 'payment',
			'success_url' => $omnify_success_url,
			'cancel_url' => $omnify_cancel_url,
			'customer_email' => $omnify_context['email'],
			'client_reference_id' => (string) $omnify_context['order_id'],
			'payment_method_types[0]' => 'card',
			'line_items[0][quantity]' => '1',
			'line_items[0][price_data][currency]' => strtolower($omnify_context['currency']),
			'line_items[0][price_data][unit_amount]' => (string) $this->stripe_amount($omnify_context['total'], $omnify_context['currency']),
			'line_items[0][price_data][product_data][name]' => $omnify_context['item_name'],
			'metadata[order_id]' => (string) $omnify_context['order_id'],
			'payment_intent_data[metadata][order_id]' => (string) $omnify_context['order_id'],
		];

		$omnify_response = $this->stripe_request('checkout/sessions', $omnify_session_args, $omnify_secret_key);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return rest_ensure_response([
			'success' => true,
			'order_id' => $omnify_context['order_id'],
			'session_id' => $omnify_response['id'] ?? '',
			'url' => $omnify_response['url'] ?? '',
		]);
	}

	public function create_stripe_payment_intent(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['stripe_enabled']) || empty($omnify_settings['stripe_payment_intents_enabled'])) {
			return new WP_Error('omnify_stripe_payment_intents_disabled', __('Stripe Payment Intents are not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_secret_key = $this->stripe_secret_key($omnify_settings);
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_stripe_key_missing', __('Stripe secret key is missing.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_context = $this->prepare_stripe_order_context((array) $omnify_request->get_json_params(), 'stripe_payment_intent');
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_args = [
			'amount' => (string) $this->stripe_amount($omnify_context['total'], $omnify_context['currency']),
			'currency' => strtolower($omnify_context['currency']),
			'receipt_email' => $omnify_context['email'],
			'description' => $omnify_context['item_name'],
			'automatic_payment_methods[enabled]' => 'true',
			'metadata[order_id]' => (string) $omnify_context['order_id'],
		];

		$omnify_response = $this->stripe_request('payment_intents', $omnify_args, $omnify_secret_key);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return rest_ensure_response([
			'success' => true,
			'order_id' => $omnify_context['order_id'],
			'payment_intent_id' => $omnify_response['id'] ?? '',
			'client_secret' => $omnify_response['client_secret'] ?? '',
		]);
	}

	public function stripe_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_payload = $omnify_request->get_body();
		$omnify_signature = (string) ($omnify_request->get_header('stripe-signature') ?? '');
		$omnify_webhook_secret = (string) ($omnify_settings['stripe_webhook_secret'] ?? '');

		if ('' === $omnify_webhook_secret) {
			return new WP_Error('omnify_stripe_webhook_unconfigured', __('Stripe webhook secret is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		if (! $this->verify_stripe_signature($omnify_payload, $omnify_signature, $omnify_webhook_secret)) {
			return new WP_Error('omnify_stripe_webhook_signature_invalid', __('Invalid Stripe webhook signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_event = json_decode($omnify_payload, true);
		if (! is_array($omnify_event)) {
			return new WP_Error('omnify_stripe_webhook_invalid', __('Invalid Stripe webhook payload.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_type = (string) ($omnify_event['type'] ?? '');
		$omnify_object = $omnify_event['data']['object'] ?? [];
		$omnify_order_id = absint($omnify_object['metadata']['order_id'] ?? $omnify_object['client_reference_id'] ?? 0);

		if ($omnify_order_id && in_array($omnify_type, ['checkout.session.completed', 'payment_intent.succeeded'], true)) {
			$omnify_event_id = (string) ($omnify_event['id'] ?? '');
			if ('' !== $omnify_event_id) {
				if ($this->is_webhook_event_processed($omnify_event_id, 'stripe')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
				if (! $this->record_webhook_event($omnify_event_id, 'stripe')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
			}

			$omnify_transaction_id = null;
			if ($omnify_type === 'checkout.session.completed') {
				$omnify_transaction_id = $omnify_object['payment_intent'] ?? null;
			} elseif ($omnify_type === 'payment_intent.succeeded') {
				$omnify_transaction_id = $omnify_object['id'] ?? null;
			}

			$this->complete_order_payment($omnify_order_id, 'stripe', $omnify_transaction_id);
		} elseif ($omnify_order_id && in_array($omnify_type, ['checkout.session.expired', 'payment_intent.payment_failed'], true)) {
			$omnify_event_id = (string) ($omnify_event['id'] ?? '');
			if ('' !== $omnify_event_id) {
				if ($this->is_webhook_event_processed($omnify_event_id, 'stripe')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
				if (! $this->record_webhook_event($omnify_event_id, 'stripe')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
			}

			// translators: %s: placeholder value.
			$this->cancel_order_payment($omnify_order_id, 'stripe', sprintf(__('Stripe event: %s', 'omnifywp-ecommerce'), $omnify_type));
		}

		return rest_ensure_response(['received' => true]);
	}

	public function create_paypal_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['paypal_enabled']) || empty($omnify_settings['paypal_checkout_enabled'])) {
			return new WP_Error('omnify_paypal_disabled', __('PayPal Checkout is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_access_token = $this->paypal_access_token($omnify_settings);
		if (is_wp_error($omnify_access_token)) {
			return $omnify_access_token;
		}

		$omnify_context = $this->prepare_stripe_order_context((array) $omnify_request->get_json_params(), 'paypal_checkout');
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order = $this->omnify_orders->find($omnify_context['order_id']);
		$omnify_order_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);

		$omnify_return_url = add_query_arg(
			['omnify_paypal' => 'success', 'order_id' => $omnify_context['order_id'], 'order_key' => $omnify_order_key],
			(string) (wp_get_referer() ?: home_url('/'))
		);
		$omnify_cancel_url = add_query_arg(
			['omnify_paypal' => 'cancelled', 'order_id' => $omnify_context['order_id'], 'order_key' => $omnify_order_key],
			(string) (wp_get_referer() ?: home_url('/'))
		);

		$omnify_body = [
			'intent' => 'CAPTURE',
			'purchase_units' => [
				[
					'reference_id' => (string) $omnify_context['order_id'],
					'custom_id' => (string) $omnify_context['order_id'],
					'description' => $omnify_context['item_name'],
					'amount' => [
						'currency_code' => strtoupper((string) $omnify_context['currency']),
						'value' => $this->paypal_amount_value((float) $omnify_context['total'], (string) $omnify_context['currency']),
					],
				],
			],
			'application_context' => [
				'brand_name' => get_bloginfo('name'),
				'shipping_preference' => 'NO_SHIPPING',
				'user_action' => 'PAY_NOW',
				'return_url' => $omnify_return_url,
				'cancel_url' => $omnify_cancel_url,
			],
		];

		$omnify_response = $this->paypal_request('POST', 'v2/checkout/orders', $omnify_body, $omnify_access_token, $omnify_settings);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_approve_url = '';
		foreach (($omnify_response['links'] ?? []) as $omnify_link) {
			if (in_array($omnify_link['rel'] ?? '', ['approve', 'payer-action'], true)) {
				$omnify_approve_url = (string) ($omnify_link['href'] ?? '');
				break;
			}
		}

		return rest_ensure_response([
			'success' => true,
			'order_id' => $omnify_context['order_id'],
			'paypal_order_id' => $omnify_response['id'] ?? '',
			'url' => $omnify_approve_url,
		]);
	}

	public function capture_paypal_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['paypal_enabled']) || empty($omnify_settings['paypal_checkout_enabled'])) {
			return new WP_Error('omnify_paypal_disabled', __('PayPal Checkout is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_order_id = absint($omnify_data['order_id'] ?? 0);
		$omnify_paypal_order_id = sanitize_text_field((string) ($omnify_data['paypal_order_id'] ?? ''));

		if (! $omnify_order_id || '' === $omnify_paypal_order_id) {
			return new WP_Error('omnify_paypal_capture_missing_data', __('PayPal order data is missing.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_access_token = $this->paypal_access_token($omnify_settings);
		if (is_wp_error($omnify_access_token)) {
			return $omnify_access_token;
		}

		$omnify_response = $this->paypal_request('POST', 'v2/checkout/orders/' . rawurlencode($omnify_paypal_order_id) . '/capture', new \stdClass(), $omnify_access_token, $omnify_settings);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		if ('COMPLETED' !== ($omnify_response['status'] ?? '')) {
			return new WP_Error('omnify_paypal_capture_incomplete', __('PayPal payment was not completed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_response_order_id = $this->paypal_order_id_from_event(['resource' => $omnify_response]);
		if ($omnify_response_order_id && $omnify_response_order_id !== $omnify_order_id) {
			return new WP_Error('omnify_paypal_order_mismatch', __('PayPal order does not match the local order.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_capture_id = null;
		if (! empty($omnify_response['purchase_units'])) {
			foreach ($omnify_response['purchase_units'] as $omnify_unit) {
				if (! empty($omnify_unit['payments']['captures'])) {
					foreach ($omnify_unit['payments']['captures'] as $omnify_capture) {
						if ('COMPLETED' === ($omnify_capture['status'] ?? '')) {
							$omnify_capture_id = (string) ($omnify_capture['id'] ?? '');
							break 2;
						}
					}
				}
			}
		}

		$this->complete_order_payment($omnify_order_id, 'paypal', $omnify_capture_id);

		return rest_ensure_response([
			'success' => true,
			'order_id' => $omnify_order_id,
			'paypal_order_id' => $omnify_paypal_order_id,
		]);
	}

	public function paypal_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_payload = $omnify_request->get_body();
		$omnify_event = json_decode($omnify_payload, true);

		if (! is_array($omnify_event)) {
			return new WP_Error('omnify_paypal_webhook_invalid', __('Invalid PayPal webhook payload.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		if (empty($omnify_settings['paypal_webhook_id'])) {
			return new WP_Error('omnify_paypal_webhook_unconfigured', __('PayPal webhook ID is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_verified = $this->verify_paypal_webhook($omnify_request, $omnify_event, $omnify_settings);
		if (is_wp_error($omnify_verified)) {
			return $omnify_verified;
		}
		if (! $omnify_verified) {
			return new WP_Error('omnify_paypal_webhook_signature_invalid', __('Invalid PayPal webhook signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_type = (string) ($omnify_event['event_type'] ?? '');
		if (in_array($omnify_type, ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.COMPLETED'], true)) {
			$omnify_order_id = $this->paypal_order_id_from_event($omnify_event);
			if ($omnify_order_id) {
				$omnify_event_id = (string) ($omnify_event['id'] ?? '');
				if ('' !== $omnify_event_id) {
					if ($this->is_webhook_event_processed($omnify_event_id, 'paypal')) {
						return rest_ensure_response(['received' => true, 'duplicate' => true]);
					}
					if (! $this->record_webhook_event($omnify_event_id, 'paypal')) {
						return rest_ensure_response(['received' => true, 'duplicate' => true]);
					}
				}

				$omnify_capture_id = null;
				$omnify_resource = $omnify_event['resource'] ?? [];
				if ($omnify_type === 'PAYMENT.CAPTURE.COMPLETED') {
					$omnify_capture_id = (string) ($omnify_resource['id'] ?? '');
				} elseif ($omnify_type === 'CHECKOUT.ORDER.COMPLETED') {
					if (! empty($omnify_resource['purchase_units'])) {
						foreach ($omnify_resource['purchase_units'] as $omnify_unit) {
							if (! empty($omnify_unit['payments']['captures'])) {
								foreach ($omnify_unit['payments']['captures'] as $omnify_capture) {
									if ('COMPLETED' === ($omnify_capture['status'] ?? '')) {
										$omnify_capture_id = (string) ($omnify_capture['id'] ?? '');
										break 2;
									}
								}
							}
						}
					}
				}

				$this->complete_order_payment($omnify_order_id, 'paypal', $omnify_capture_id);
			}
		} elseif (in_array($omnify_type, ['PAYMENT.CAPTURE.DENIED', 'CHECKOUT.ORDER.VOIDED'], true)) {
			$omnify_order_id = $this->paypal_order_id_from_event($omnify_event);
			if ($omnify_order_id) {
				$omnify_event_id = (string) ($omnify_event['id'] ?? '');
				if ('' !== $omnify_event_id) {
					if ($this->is_webhook_event_processed($omnify_event_id, 'paypal')) {
						return rest_ensure_response(['received' => true, 'duplicate' => true]);
					}
					if (! $this->record_webhook_event($omnify_event_id, 'paypal')) {
						return rest_ensure_response(['received' => true, 'duplicate' => true]);
					}
				}

				// translators: %s: placeholder value.
				$this->cancel_order_payment($omnify_order_id, 'paypal', sprintf(__('PayPal event: %s', 'omnifywp-ecommerce'), $omnify_type));
			}
		}

		return rest_ensure_response(['received' => true]);
	}

	/**
	 * Create Razorpay Order (for frontend checkout)
	 */
	public function create_razorpay_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['razorpay_enabled']) || empty($omnify_settings['razorpay_checkout_enabled'])) {
			return new WP_Error('omnify_razorpay_disabled', __('Razorpay is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_mode = 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_key_id = sanitize_text_field((string) ($omnify_settings["razorpay_{$omnify_mode}_key_id"] ?? ''));
		$omnify_key_secret = sanitize_text_field((string) ($omnify_settings["razorpay_{$omnify_mode}_key_secret"] ?? ''));

		if ('' === $omnify_key_id || '' === $omnify_key_secret) {
			return new WP_Error('omnify_razorpay_keys_missing', __('Razorpay keys are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();

		// Create Omnify order as pending_payment first
		$omnify_order_creation = $this->create_omnify_order_for_gateway($omnify_data, 'razorpay', $omnify_settings);
		if (is_wp_error($omnify_order_creation)) {
			return $omnify_order_creation;
		}
		$omnify_context = $omnify_order_creation;

		$omnify_razorpay_order_id = (string) ($omnify_context['transaction_id'] ?? '');

		// If we already have a razorpay order id stored, reuse it (idempotent)
		if ($omnify_razorpay_order_id) {
			return rest_ensure_response([
				'success'           => true,
				'order_id'          => (int) $omnify_context['order_id'],
				'razorpay_order_id' => $omnify_razorpay_order_id,
				'key'               => $omnify_key_id,
				'amount'            => (int) $this->razorpay_amount($omnify_context['total'], $omnify_context['currency']),
				'currency'          => strtoupper((string) $omnify_context['currency']),
				'name'              => sanitize_text_field((string) ($omnify_settings['store_name'] ?? get_bloginfo('name'))),
				'description'       => (string) ($omnify_context['item_name'] ?? 'Order'),
				'prefill'           => [
					'email' => (string) ($omnify_context['email'] ?? ''),
				],
			]);
		}

		// Create Razorpay Order
		$omnify_amount = $this->razorpay_amount((float) $omnify_context['total'], (string) $omnify_context['currency']);
		$omnify_receipt = 'omnify_' . $omnify_context['order_id'];

		$omnify_body = [
			'amount'   => $omnify_amount,
			'currency' => strtoupper((string) $omnify_context['currency']),
			'receipt'  => $omnify_receipt,
			'notes'    => [
				'omnify_order_id' => (string) $omnify_context['order_id'],
				'email'           => (string) ($omnify_context['email'] ?? ''),
			],
		];

		$omnify_response = $this->razorpay_request('orders', $omnify_body, $omnify_key_id, $omnify_key_secret);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_rzp_order_id = (string) ($omnify_response['id'] ?? '');
		if ('' === $omnify_rzp_order_id) {
			return new WP_Error('omnify_razorpay_order_failed', __('Failed to create Razorpay order.', 'omnifywp-ecommerce'), ['status' => 500]);
		}

		// Store razorpay order id on our order
		$this->omnify_orders->update((int) $omnify_context['order_id'], [
			'transaction_id' => $omnify_rzp_order_id,
		]);

		return rest_ensure_response([
			'success'           => true,
			'order_id'          => (int) $omnify_context['order_id'],
			'razorpay_order_id' => $omnify_rzp_order_id,
			'key'               => $omnify_key_id,
			'amount'            => $omnify_amount,
			'currency'          => strtoupper((string) $omnify_context['currency']),
			'name'              => sanitize_text_field((string) ($omnify_settings['store_name'] ?? get_bloginfo('name'))),
			'description'       => (string) ($omnify_context['item_name'] ?? 'Order'),
			'prefill'           => [
				'email' => (string) ($omnify_context['email'] ?? ''),
			],
		]);
	}

	public function verify_razorpay_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_data = (array) $omnify_request->get_json_params();

		$omnify_order_id     = absint($omnify_data['order_id'] ?? 0);
		$omnify_rzp_order_id = sanitize_text_field((string) ($omnify_data['razorpay_order_id'] ?? ''));
		$omnify_rzp_payment_id = sanitize_text_field((string) ($omnify_data['razorpay_payment_id'] ?? ''));
		$omnify_rzp_signature  = sanitize_text_field((string) ($omnify_data['razorpay_signature'] ?? ''));

		if (! $omnify_order_id || '' === $omnify_rzp_order_id || '' === $omnify_rzp_payment_id || '' === $omnify_rzp_signature) {
			return new WP_Error('omnify_razorpay_verify_missing', __('Missing payment verification data.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_mode = 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_key_secret = sanitize_text_field((string) ($omnify_settings["razorpay_{$omnify_mode}_key_secret"] ?? ''));

		// Verify signature
		$omnify_expected_signature = hash_hmac('sha256', $omnify_rzp_order_id . '|' . $omnify_rzp_payment_id, $omnify_key_secret);
		if (! hash_equals($omnify_expected_signature, $omnify_rzp_signature)) {
			return new WP_Error('omnify_razorpay_signature_invalid', __('Payment signature verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		// Mark order paid and complete
		$this->complete_order_payment($omnify_order_id, 'razorpay', $omnify_rzp_payment_id);

		$omnify_created_order = $this->omnify_orders->find($omnify_order_id);

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'order_number' => $omnify_created_order ? $omnify_created_order['order_number'] : '#' . $omnify_order_id,
		]);
	}

	public function razorpay_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_payload = $omnify_request->get_body();
		$omnify_signature = (string) ($omnify_request->get_header('x-razorpay-signature') ?? '');

		if ('' === $omnify_webhook_secret) {
			return new WP_Error('omnify_razorpay_webhook_unconfigured', __('Razorpay webhook secret is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_expected = hash_hmac('sha256', $omnify_payload, $omnify_webhook_secret);
		if (! hash_equals($omnify_expected, $omnify_signature)) {
			return new WP_Error('omnify_razorpay_webhook_signature_invalid', __('Invalid Razorpay webhook signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_event = json_decode($omnify_payload, true);
		if (! is_array($omnify_event)) {
			return new WP_Error('omnify_razorpay_webhook_invalid', __('Invalid Razorpay webhook payload.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_event_type = (string) ($omnify_event['event'] ?? '');
		$omnify_payment    = $omnify_event['payload']['payment']['entity'] ?? [];
		$omnify_order_id   = absint($omnify_payment['notes']['omnify_order_id'] ?? $omnify_payment['notes']['order_id'] ?? 0);

		if (! $omnify_order_id) {
			// try from receipt or other
			$omnify_rzp_order = $omnify_payment['order_id'] ?? '';
			if ($omnify_rzp_order) {
				// Could look up by transaction_id
				global $wpdb;
				$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}omnify_orders WHERE transaction_id = %s",
					$omnify_rzp_order
				));
			}
		}

		if ($omnify_order_id && in_array($omnify_event_type, ['payment.captured', 'payment.authorized'], true)) {
			$omnify_event_id = (string) ($omnify_event['id'] ?? '');
			if ('' !== $omnify_event_id) {
				if ($this->is_webhook_event_processed($omnify_event_id, 'razorpay')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
				if (! $this->record_webhook_event($omnify_event_id, 'razorpay')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
			}

			$this->complete_order_payment($omnify_order_id, 'razorpay', (string) ($omnify_payment['id'] ?? ''));
		} elseif ($omnify_order_id && 'payment.failed' === $omnify_event_type) {
			$this->cancel_order_payment($omnify_order_id, 'razorpay', 'Razorpay payment failed');
		}

		return rest_ensure_response(['received' => true]);
	}

	// ==================== Alipay ====================
	public function create_alipay_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['alipay_enabled']) || empty($omnify_settings['alipay_checkout_enabled'])) {
			return new WP_Error('omnify_alipay_disabled', __('Alipay is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'alipay', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		// TODO: Call real Alipay API here to get payment URL
		// For now, return a verify redirect (developer should replace with real integration)
		$omnify_verify_url = add_query_arg([
			'omnify_alipay' => 'success',
			'order_id'      => $omnify_context['order_id'],
			'out_trade_no'  => 'alipay_' . $omnify_context['order_id'],
		], home_url('/'));

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => (int) $omnify_context['order_id'],
			'redirect_url' => $omnify_verify_url,
		]);
	}

	public function verify_alipay_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_public_key = (string) ($omnify_settings['alipay_alipay_public_key'] ?? '');
		if ('' === $omnify_public_key) {
			return new WP_Error('omnify_alipay_unconfigured', __('Alipay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_params = $omnify_request->get_params();
		if (! $this->verify_alipay_signature($omnify_params, $omnify_public_key)) {
			return new WP_Error('omnify_alipay_signature_invalid', __('Invalid Alipay signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_order_id = absint($omnify_request->get_param('order_id'));
		$omnify_refund_ref = sanitize_text_field($omnify_request->get_param('out_trade_no') ?? '');
		if ($omnify_order_id) {
			$this->complete_order_payment($omnify_order_id, 'alipay', $omnify_refund_ref ?: null);
		}
		wp_safe_redirect(add_query_arg('omnify_alipay', 'success', home_url()));
		exit;
	}

	public function alipay_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_public_key = (string) ($omnify_settings['alipay_alipay_public_key'] ?? '');
		if ('' === $omnify_public_key) {
			return new WP_Error('omnify_alipay_unconfigured', __('Alipay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_params = $omnify_request->get_params();
		if (! $this->verify_alipay_signature($omnify_params, $omnify_public_key)) {
			return new WP_Error('omnify_alipay_signature_invalid', __('Invalid Alipay signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_out_trade_no = sanitize_text_field($omnify_params['out_trade_no'] ?? '');
		if ($omnify_out_trade_no) {
			$omnify_order_id = absint(str_replace('alipay_', '', $omnify_out_trade_no));
			if ($omnify_order_id) {
				$this->complete_order_payment($omnify_order_id, 'alipay', $omnify_params['trade_no'] ?? null);
			}
		}
		return rest_ensure_response(['success' => true]);
	}

	// ==================== WeChat Pay ====================
	public function create_wechat_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['wechat_enabled']) || empty($omnify_settings['wechat_checkout_enabled'])) {
			return new WP_Error('omnify_wechat_disabled', __('WeChat Pay is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'wechat', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		// TODO: Real WeChat Pay unified order API call
		$omnify_verify_url = add_query_arg([
			'omnify_wechat' => 'success',
			'order_id'      => $omnify_context['order_id'],
		], home_url('/'));

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => (int) $omnify_context['order_id'],
			'redirect_url' => $omnify_verify_url,
		]);
	}

	public function verify_wechat_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_unconfigured', __('WeChat Pay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_order_id = absint($omnify_request->get_param('order_id'));
		$omnify_payment_id = sanitize_text_field(wp_unslash((string) ($omnify_request->get_param('transaction_id') ?? '')));
		$omnify_out_trade_no = sanitize_text_field(wp_unslash((string) ($omnify_request->get_param('out_trade_no') ?? ('wechat_' . $omnify_order_id))));

		$omnify_is_valid = false;
		if ($this->verify_wechat_signature($omnify_request, $omnify_api_key)) {
			$omnify_is_valid = true;
		} elseif ($this->query_wechat_order_status($omnify_payment_id, $omnify_out_trade_no, $omnify_settings)) {
			$omnify_is_valid = true;
		}

		if (! $omnify_is_valid) {
			return new WP_Error('omnify_wechat_verification_failed', __('WeChat Pay transaction verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		if ($omnify_order_id) {
			$this->complete_order_payment($omnify_order_id, 'wechat', $omnify_payment_id ?: null);
		}
		wp_safe_redirect(add_query_arg('omnify_wechat', 'success', home_url()));
		exit;
	}

	public function wechat_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_unconfigured', __('WeChat Pay credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		if (! $this->verify_wechat_signature($omnify_request, $omnify_api_key)) {
			return new WP_Error('omnify_wechat_signature_invalid', __('Invalid WeChat Pay webhook signature.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_data = (array) ($omnify_request->get_json_params() ?: $omnify_request->get_body_params());
		$omnify_attach = sanitize_text_field(wp_unslash((string) ($omnify_data['attach'] ?? ($omnify_data['out_trade_no'] ?? ''))));
		$omnify_order_id = absint(str_replace('wechat_', '', $omnify_attach));
		$omnify_transaction_id = sanitize_text_field(wp_unslash((string) ($omnify_data['transaction_id'] ?? '')));

		if ($omnify_order_id) {
			$this->complete_order_payment($omnify_order_id, 'wechat', $omnify_transaction_id ?: null);
		}

		return rest_ensure_response(['code' => 'SUCCESS', 'message' => 'OK']);
	}

	// ==================== SSLCommerz ====================
	public function create_sslcommerz_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['sslcommerz_enabled']) || empty($omnify_settings['sslcommerz_checkout_enabled'])) {
			return new WP_Error('omnify_sslcommerz_disabled', __('SSLCommerz is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'sslcommerz', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_mode = 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_base = $omnify_mode === 'live' ? 'https://securepay.sslcommerz.com' : 'https://sandbox.sslcommerz.com';

		// Build minimal payload for SSLCommerz (real implementation would POST to /gwprocess/v4/api.php)
		$omnify_redirect_url = add_query_arg([
			'omnify_sslcommerz' => 'success',
			'order_id'          => $omnify_context['order_id'],
			'tran_id'           => 'ssl_' . $omnify_context['order_id'],
		], home_url('/'));

		// For demo, return redirect. In real: call API and return their redirect URL.
		return rest_ensure_response([
			'success'      => true,
			'order_id'     => (int) $omnify_context['order_id'],
			'redirect_url' => $omnify_redirect_url,
		]);
	}

	public function verify_sslcommerz_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_order_id = absint($omnify_request->get_param('order_id'));
		$omnify_val_id   = sanitize_text_field((string) ($omnify_request->get_param('val_id') ?? ''));
		$omnify_tran_id  = sanitize_text_field((string) ($omnify_request->get_param('tran_id') ?? ''));

		if ('' === $omnify_val_id) {
			return new WP_Error('omnify_missing_val_id', __('Missing SSLCommerz validation ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_validated = $this->verify_sslcommerz_transaction($omnify_val_id, $omnify_settings);
		if (! $omnify_validated) {
			return new WP_Error('omnify_sslcommerz_invalid', __('SSLCommerz payment validation failed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_bound = $this->bind_and_complete_sslcommerz_order($omnify_order_id, $omnify_validated, $omnify_tran_id);
		if (is_wp_error($omnify_bound)) {
			return $omnify_bound;
		}

		wp_safe_redirect(add_query_arg('omnify_sslcommerz', 'success', home_url()));
		exit;
	}

	public function sslcommerz_ipn(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_data     = (array) ($omnify_request->get_json_params() ?: $omnify_request->get_body_params());
		$omnify_val_id   = sanitize_text_field((string) ($omnify_data['val_id'] ?? ''));
		$omnify_order_id = absint($omnify_data['value_a'] ?? 0);
		$omnify_tran_id  = sanitize_text_field((string) ($omnify_data['tran_id'] ?? ''));

		if (! $omnify_order_id && '' !== $omnify_tran_id) {
			$omnify_order_id = absint(str_replace('ssl_', '', $omnify_tran_id));
		}

		if ('' === $omnify_val_id) {
			return new WP_Error('omnify_missing_val_id', __('Missing SSLCommerz validation ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_validated = $this->verify_sslcommerz_transaction($omnify_val_id, $omnify_settings);
		if (! $omnify_validated) {
			return new WP_Error('omnify_sslcommerz_invalid', __('SSLCommerz payment validation failed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_bound = $this->bind_and_complete_sslcommerz_order($omnify_order_id, $omnify_validated, $omnify_tran_id);
		if (is_wp_error($omnify_bound)) {
			return $omnify_bound;
		}

		return rest_ensure_response(['status' => 'success']);
	}

	// ==================== Paystack ====================
	public function create_paystack_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['paystack_enabled']) || empty($omnify_settings['paystack_checkout_enabled'])) {
			return new WP_Error('omnify_paystack_disabled', __('Paystack is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_paystack_unconfigured', __('Paystack secret key is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'paystack', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order_id = (int) $omnify_context['order_id'];
		$omnify_currency = strtoupper((string) $omnify_context['currency']);
		$omnify_amount = (int) round((float) $omnify_context['total'] * 100);
		$omnify_reference = 'pstk_' . $omnify_order_id . '_' . time() . '_' . wp_rand(100, 999);

		$omnify_callback_url = add_query_arg([
			'order_id' => $omnify_order_id,
		], rest_url(self::NAMESPACE . '/paystack/verify'));

		$omnify_response = wp_remote_post('https://api.paystack.co/transaction/initialize', [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode([
				'email'        => (string) $omnify_context['email'],
				'amount'       => $omnify_amount,
				'currency'     => $omnify_currency,
				'reference'    => $omnify_reference,
				'callback_url' => $omnify_callback_url,
				'metadata'     => [
					'order_id' => $omnify_order_id,
				],
			]),
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (! is_array($omnify_body) || empty($omnify_body['status']) || empty($omnify_body['data']['authorization_url'])) {
			$omnify_msg = is_array($omnify_body) && ! empty($omnify_body['message']) ? (string) $omnify_body['message'] : __('Failed to initialize Paystack payment.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_paystack_init_failed', $omnify_msg, ['status' => 400]);
		}

		$this->omnify_orders->update($omnify_order_id, [
			'transaction_id' => $omnify_reference,
		]);

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'redirect_url' => (string) $omnify_body['data']['authorization_url'],
			'reference'    => $omnify_reference,
		]);
	}

	public function verify_paystack_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));
		$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
		$omnify_checkout_url = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/checkout/');

		$omnify_reference = sanitize_text_field((string) ($omnify_request->get_param('reference') ?: ($omnify_request->get_param('trxref') ?: '')));
		$omnify_order_id  = absint($omnify_request->get_param('order_id'));

		if ('' === $omnify_reference && $omnify_order_id) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order && ! empty($omnify_order['transaction_id'])) {
				$omnify_reference = (string) $omnify_order['transaction_id'];
			}
		}

		if ('' === $omnify_reference) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_paystack' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return new WP_Error('omnify_paystack_missing_ref', __('Missing Paystack transaction reference.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_response = wp_remote_get('https://api.paystack.co/transaction/verify/' . rawurlencode($omnify_reference), [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
			],
		]);

		if (is_wp_error($omnify_response)) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_paystack' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (is_array($omnify_body) && ! empty($omnify_body['status']) && 'success' === ($omnify_body['data']['status'] ?? '')) {
			if (! $omnify_order_id && ! empty($omnify_body['data']['metadata']['order_id'])) {
				$omnify_order_id = absint($omnify_body['data']['metadata']['order_id']);
			}
			if ($omnify_order_id) {
				$this->complete_order_payment($omnify_order_id, 'paystack', $omnify_reference);
			}

			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_paystack' => 'success', 'order_id' => $omnify_order_id], $omnify_checkout_url));
				exit;
			}

			return rest_ensure_response([
				'success'  => true,
				'order_id' => $omnify_order_id,
			]);
		}

		if ('GET' === $omnify_request->get_method()) {
			wp_safe_redirect(add_query_arg(['omnify_paystack' => 'failed', 'order_id' => $omnify_order_id], $omnify_checkout_url));
			exit;
		}

		return new WP_Error('omnify_paystack_verify_failed', __('Paystack payment verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
	}

	public function paystack_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));
		$omnify_payload = (string) $omnify_request->get_body();
		$omnify_signature = (string) ($omnify_request->get_header('x-paystack-signature') ?? '');

		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_paystack_webhook_unconfigured', __('Paystack secret key is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_expected = hash_hmac('sha512', $omnify_payload, $omnify_secret_key);
		if (! hash_equals($omnify_expected, $omnify_signature)) {
			return new WP_Error('omnify_paystack_webhook_invalid', __('Invalid Paystack webhook signature.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_event = json_decode($omnify_payload, true);
		if (! is_array($omnify_event)) {
			return new WP_Error('omnify_paystack_webhook_invalid', __('Invalid Paystack webhook payload.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_event_type = (string) ($omnify_event['event'] ?? '');
		if ('charge.success' === $omnify_event_type) {
			$omnify_data = $omnify_event['data'] ?? [];
			$omnify_order_id = absint($omnify_data['metadata']['order_id'] ?? 0);
			$omnify_ref = sanitize_text_field((string) ($omnify_data['reference'] ?? ''));

			if (! $omnify_order_id && '' !== $omnify_ref) {
				global $wpdb;
				$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}omnify_orders WHERE transaction_id = %s",
					$omnify_ref
				));
			}

			if ($omnify_order_id) {
				$omnify_event_id = sanitize_text_field((string) ($omnify_data['id'] ?? $omnify_ref));
				if ('' !== $omnify_event_id && ! $this->record_webhook_event($omnify_event_id, 'paystack')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
				$this->complete_order_payment($omnify_order_id, 'paystack', $omnify_ref);
			}
		}

		return rest_ensure_response(['received' => true]);
	}

	// ==================== Tap Payments ====================
	public function create_tap_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['tap_enabled']) || empty($omnify_settings['tap_checkout_enabled'])) {
			return new WP_Error('omnify_tap_disabled', __('Tap Payments is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_mode = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_tap_unconfigured', __('Tap Payments secret API key is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'tap', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order_id = (int) $omnify_context['order_id'];
		$omnify_currency = strtoupper((string) $omnify_context['currency']);
		$omnify_callback_url = add_query_arg([
			'order_id' => $omnify_order_id,
		], rest_url(self::NAMESPACE . '/tap/verify'));

		$omnify_response = wp_remote_post('https://api.tap.company/v2/charges', [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode([
				'amount'   => (float) $omnify_context['total'],
				'currency' => $omnify_currency,
				'customer' => [
					'first_name' => sanitize_text_field((string) ($omnify_data['first_name'] ?? 'Customer')),
					'last_name'  => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
					'email'      => (string) $omnify_context['email'],
				],
				'source'   => ['id' => 'src_all'],
				'redirect' => [
					'url' => $omnify_callback_url,
				],
				'post'     => [
					'url' => rest_url(self::NAMESPACE . '/tap/webhook'),
				],
				'metadata' => [
					'order_id' => (string) $omnify_order_id,
				],
			]),
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		$omnify_redirect_url = (string) ($omnify_body['transaction']['url'] ?? '');
		$omnify_charge_id    = (string) ($omnify_body['id'] ?? '');

		if (! is_array($omnify_body) || '' === $omnify_redirect_url) {
			$omnify_msg = is_array($omnify_body) && ! empty($omnify_body['errors'][0]['description']) ? (string) $omnify_body['errors'][0]['description'] : __('Failed to initiate Tap charge.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_tap_init_failed', $omnify_msg, ['status' => 400]);
		}

		if ('' !== $omnify_charge_id) {
			$this->omnify_orders->update($omnify_order_id, [
				'transaction_id' => $omnify_charge_id,
			]);
		}

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'redirect_url' => $omnify_redirect_url,
			'charge_id'    => $omnify_charge_id,
		]);
	}

	public function verify_tap_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));
		$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
		$omnify_checkout_url = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/checkout/');

		$omnify_charge_id = sanitize_text_field((string) ($omnify_request->get_param('tap_id') ?: ($omnify_request->get_param('charge_id') ?: '')));
		$omnify_order_id  = absint($omnify_request->get_param('order_id'));

		if ('' === $omnify_charge_id && $omnify_order_id) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order && ! empty($omnify_order['transaction_id'])) {
				$omnify_charge_id = (string) $omnify_order['transaction_id'];
			}
		}

		if ('' === $omnify_charge_id) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_tap' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return new WP_Error('omnify_tap_missing_id', __('Missing Tap charge ID.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_response = wp_remote_get('https://api.tap.company/v2/charges/' . rawurlencode($omnify_charge_id), [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
			],
		]);

		if (is_wp_error($omnify_response)) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_tap' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (is_array($omnify_body) && 'CAPTURED' === ($omnify_body['status'] ?? '')) {
			if (! $omnify_order_id && ! empty($omnify_body['metadata']['order_id'])) {
				$omnify_order_id = absint($omnify_body['metadata']['order_id']);
			}
			if ($omnify_order_id) {
				$this->complete_order_payment($omnify_order_id, 'tap', $omnify_charge_id);
			}

			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_tap' => 'success', 'order_id' => $omnify_order_id], $omnify_checkout_url));
				exit;
			}

			return rest_ensure_response([
				'success'  => true,
				'order_id' => $omnify_order_id,
			]);
		}

		if ('GET' === $omnify_request->get_method()) {
			wp_safe_redirect(add_query_arg(['omnify_tap' => 'failed', 'order_id' => $omnify_order_id], $omnify_checkout_url));
			exit;
		}

		return new WP_Error('omnify_tap_verify_failed', __('Tap payment verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
	}

	public function tap_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_tap_unconfigured', __('Tap secret key is not configured.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_payload = (string) $omnify_request->get_body();
		$omnify_event = json_decode($omnify_payload, true);
		if (! is_array($omnify_event)) {
			return new WP_Error('omnify_tap_webhook_invalid', __('Invalid Tap webhook payload.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_charge_id = sanitize_text_field((string) ($omnify_event['id'] ?? ''));
		if ('' === $omnify_charge_id) {
			return new WP_Error('omnify_tap_missing_id', __('Missing Tap charge ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		// Verify charge directly against Tap API to prevent webhook forgery
		$omnify_response = wp_remote_get('https://api.tap.company/v2/charges/' . rawurlencode($omnify_charge_id), [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
			],
		]);

		if (is_wp_error($omnify_response) || 200 !== (int) wp_remote_retrieve_response_code($omnify_response)) {
			return new WP_Error('omnify_tap_verification_failed', __('Failed to verify charge with Tap API.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_verified_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (! is_array($omnify_verified_body) || 'CAPTURED' !== ($omnify_verified_body['status'] ?? '')) {
			return rest_ensure_response(['received' => true, 'status' => $omnify_verified_body['status'] ?? 'pending']);
		}

		$omnify_order_id = absint($omnify_verified_body['metadata']['order_id'] ?? 0);
		if (! $omnify_order_id && '' !== $omnify_charge_id) {
			global $wpdb;
			$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}omnify_orders WHERE transaction_id = %s",
				$omnify_charge_id
			));
		}

		if ($omnify_order_id && '' !== $omnify_charge_id) {
			if (! $this->record_webhook_event($omnify_charge_id, 'tap')) {
				return rest_ensure_response(['received' => true, 'duplicate' => true]);
			}
			$this->complete_order_payment($omnify_order_id, 'tap', $omnify_charge_id);
		}

		return rest_ensure_response(['received' => true]);
	}

	// ==================== Mollie ====================
	public function create_mollie_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['mollie_enabled']) || empty($omnify_settings['mollie_checkout_enabled'])) {
			return new WP_Error('omnify_mollie_disabled', __('Mollie is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_mode = 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_api_key = trim((string) ($omnify_settings["mollie_{$omnify_mode}_api_key"] ?? ''));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_mollie_unconfigured', __('Mollie API key is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'mollie', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order_id = (int) $omnify_context['order_id'];
		$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
		$omnify_checkout_url = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/checkout/');

		$omnify_redirect_url = add_query_arg([
			'omnify_mollie' => 'success',
			'order_id'      => $omnify_order_id,
		], $omnify_checkout_url);

		$omnify_response = wp_remote_post('https://api.mollie.com/v2/payments', [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_api_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode([
				'amount'      => [
					'currency' => strtoupper((string) $omnify_context['currency']),
					'value'    => number_format((float) $omnify_context['total'], 2, '.', ''),
				],
				'description' => 'Order #' . $omnify_order_id,
				'redirectUrl' => $omnify_redirect_url,
				'webhookUrl'  => rest_url(self::NAMESPACE . '/mollie/webhook'),
				'metadata'    => [
					'order_id' => $omnify_order_id,
				],
			]),
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		$omnify_checkout_href = (string) ($omnify_body['_links']['checkout']['href'] ?? '');
		$omnify_payment_id    = (string) ($omnify_body['id'] ?? '');

		if (! is_array($omnify_body) || '' === $omnify_checkout_href) {
			$omnify_msg = is_array($omnify_body) && ! empty($omnify_body['detail']) ? (string) $omnify_body['detail'] : __('Failed to create Mollie payment.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_mollie_init_failed', $omnify_msg, ['status' => 400]);
		}

		if ('' !== $omnify_payment_id) {
			$this->omnify_orders->update($omnify_order_id, [
				'transaction_id' => $omnify_payment_id,
			]);
		}

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'redirect_url' => $omnify_checkout_href,
			'payment_id'   => $omnify_payment_id,
		]);
	}

	public function mollie_webhook(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_api_key = trim((string) ($omnify_settings["mollie_{$omnify_mode}_api_key"] ?? ''));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_mollie_unconfigured', __('Mollie credentials are not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_payment_id = sanitize_text_field((string) ($omnify_request->get_param('id') ?? ''));
		if ('' === $omnify_payment_id) {
			return new WP_Error('omnify_mollie_missing_id', __('Missing payment ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_response = wp_remote_get('https://api.mollie.com/v2/payments/' . rawurlencode($omnify_payment_id), [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_api_key,
			],
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_payment = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (is_array($omnify_payment) && 'paid' === ($omnify_payment['status'] ?? '')) {
			$omnify_order_id = absint($omnify_payment['metadata']['order_id'] ?? 0);
			if (! $omnify_order_id) {
				global $wpdb;
				$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}omnify_orders WHERE transaction_id = %s",
					$omnify_payment_id
				));
			}

			if ($omnify_order_id) {
				if (! $this->record_webhook_event($omnify_payment_id, 'mollie')) {
					return rest_ensure_response(['received' => true, 'duplicate' => true]);
				}
				$this->complete_order_payment($omnify_order_id, 'mollie', $omnify_payment_id);
			}
		}

		return rest_ensure_response(['received' => true]);
	}

	// ==================== Khalti ====================
	public function create_khalti_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['khalti_enabled']) || empty($omnify_settings['khalti_checkout_enabled'])) {
			return new WP_Error('omnify_khalti_disabled', __('Khalti is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_currency = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
		if ('NPR' !== $omnify_currency) {
			return new WP_Error('omnify_khalti_currency_unsupported', __('Khalti only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_is_live  = 'live' === ($omnify_settings['khalti_mode'] ?? '');
		$omnify_secret_key = trim((string) ($omnify_is_live ? ($omnify_settings['khalti_live_secret_key'] ?? '') : ($omnify_settings['khalti_test_secret_key'] ?? '')));
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_khalti_unconfigured', __('Khalti secret key is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'khalti', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order_id = (int) $omnify_context['order_id'];
		$omnify_amount_paisa = (int) round((float) $omnify_context['total'] * 100);
		$omnify_return_url = add_query_arg([
			'order_id' => $omnify_order_id,
		], rest_url(self::NAMESPACE . '/khalti/verify'));

		$omnify_base = $omnify_is_live ? 'https://khalti.com/api/v2' : 'https://dev.khalti.com/api/v2';

		$omnify_response = wp_remote_post("{$omnify_base}/epayment/initiate/", [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Key ' . $omnify_secret_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode([
				'return_url'          => $omnify_return_url,
				'website_url'         => home_url('/'),
				'amount'              => $omnify_amount_paisa,
				'purchase_order_id'   => (string) $omnify_order_id,
				'purchase_order_name' => 'Order #' . $omnify_order_id,
				'customer_info'       => [
					'name'  => sanitize_text_field((string) ($omnify_data['first_name'] ?? 'Customer')),
					'email' => (string) $omnify_context['email'],
				],
			]),
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		$omnify_payment_url = (string) ($omnify_body['payment_url'] ?? '');
		$omnify_pidx        = (string) ($omnify_body['pidx'] ?? '');

		if (! is_array($omnify_body) || '' === $omnify_payment_url) {
			$omnify_msg = is_array($omnify_body) && ! empty($omnify_body['detail']) ? (string) $omnify_body['detail'] : __('Failed to initiate Khalti payment.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_khalti_init_failed', $omnify_msg, ['status' => 400]);
		}

		if ('' !== $omnify_pidx) {
			$this->omnify_orders->update($omnify_order_id, [
				'transaction_id' => $omnify_pidx,
			]);
		}

		return rest_ensure_response([
			'success'      => true,
			'order_id'     => $omnify_order_id,
			'redirect_url' => $omnify_payment_url,
			'pidx'         => $omnify_pidx,
		]);
	}

	public function verify_khalti_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_is_live  = 'live' === ($omnify_settings['khalti_mode'] ?? '');
		$omnify_secret_key = trim((string) ($omnify_is_live ? ($omnify_settings['khalti_live_secret_key'] ?? '') : ($omnify_settings['khalti_test_secret_key'] ?? '')));
		$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
		$omnify_checkout_url = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/checkout/');

		$omnify_pidx     = sanitize_text_field((string) ($omnify_request->get_param('pidx') ?? ''));
		$omnify_order_id = absint($omnify_request->get_param('purchase_order_id') ?: $omnify_request->get_param('order_id'));

		if ('' === $omnify_pidx && $omnify_order_id) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order && ! empty($omnify_order['transaction_id'])) {
				$omnify_pidx = (string) $omnify_order['transaction_id'];
			}
		}

		if ('' === $omnify_pidx) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_khalti' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return new WP_Error('omnify_khalti_missing_pidx', __('Missing Khalti payment PIDX.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_base = $omnify_is_live ? 'https://khalti.com/api/v2' : 'https://dev.khalti.com/api/v2';

		$omnify_response = wp_remote_post("{$omnify_base}/epayment/lookup/", [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Key ' . $omnify_secret_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode([
				'pidx' => $omnify_pidx,
			]),
		]);

		if (is_wp_error($omnify_response)) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_khalti' => 'failed'], $omnify_checkout_url));
				exit;
			}
			return $omnify_response;
		}

		$omnify_body = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if (is_array($omnify_body) && 'Completed' === ($omnify_body['status'] ?? '')) {
			if (! $omnify_order_id && ! empty($omnify_body['purchase_order_id'])) {
				$omnify_order_id = absint($omnify_body['purchase_order_id']);
			}
			if ($omnify_order_id) {
				$this->complete_order_payment($omnify_order_id, 'khalti', $omnify_pidx);
			}

			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_khalti' => 'success', 'order_id' => $omnify_order_id], $omnify_checkout_url));
				exit;
			}

			return rest_ensure_response([
				'success'  => true,
				'order_id' => $omnify_order_id,
			]);
		}

		if ('GET' === $omnify_request->get_method()) {
			wp_safe_redirect(add_query_arg(['omnify_khalti' => 'failed', 'order_id' => $omnify_order_id], $omnify_checkout_url));
			exit;
		}

		return new WP_Error('omnify_khalti_verify_failed', __('Khalti payment verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
	}

	// ==================== eSewa ====================
	public function create_esewa_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['esewa_enabled']) || empty($omnify_settings['esewa_checkout_enabled'])) {
			return new WP_Error('omnify_esewa_disabled', __('eSewa is not enabled.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_currency = strtoupper((string) ($omnify_settings['currency'] ?? 'USD'));
		if ('NPR' !== $omnify_currency) {
			return new WP_Error('omnify_esewa_currency_unsupported', __('eSewa only supports Nepalese Rupees (NPR).', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_mode = 'live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_product_code = trim((string) ($omnify_settings["esewa_{$omnify_mode}_product_code"] ?? ($omnify_mode === 'test' ? 'EPAYTEST' : '')));
		$omnify_secret_key   = trim((string) ($omnify_settings["esewa_{$omnify_mode}_secret_key"] ?? ($omnify_mode === 'test' ? '8gBm/:&EnhH.1/q' : '')));

		if ('' === $omnify_product_code) {
			return new WP_Error('omnify_esewa_unconfigured', __('eSewa product code is not configured.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_context = $this->create_omnify_order_for_gateway($omnify_data, 'esewa', $omnify_settings);
		if (is_wp_error($omnify_context)) {
			return $omnify_context;
		}

		$omnify_order_id = (int) $omnify_context['order_id'];
		$omnify_amount = number_format((float) $omnify_context['total'], 2, '.', '');
		$omnify_uuid   = 'esewa_' . $omnify_order_id . '_' . time();

		$omnify_message = "total_amount={$omnify_amount},transaction_uuid={$omnify_uuid},product_code={$omnify_product_code}";
		$omnify_signature = base64_encode(hash_hmac('sha256', $omnify_message, $omnify_secret_key, true));

		$omnify_base_form = $omnify_mode === 'live'
			? 'https://epay.esewa.com.np/api/epay/main/v2/form'
			: 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';

		$this->omnify_orders->update($omnify_order_id, [
			'transaction_id' => $omnify_uuid,
		]);

		return rest_ensure_response([
			'success'  => true,
			'order_id' => $omnify_order_id,
			'form_url' => $omnify_base_form,
			'params'   => [
				'amount'                  => $omnify_amount,
				'tax_amount'              => '0',
				'total_amount'            => $omnify_amount,
				'transaction_uuid'        => $omnify_uuid,
				'product_code'            => $omnify_product_code,
				'product_service_charge'  => '0',
				'product_delivery_charge' => '0',
				'success_url'             => add_query_arg(['order_id' => $omnify_order_id], rest_url(self::NAMESPACE . '/esewa/verify')),
				'failure_url'             => add_query_arg(['order_id' => $omnify_order_id, 'failed' => 1], rest_url(self::NAMESPACE . '/esewa/verify')),
				'signed_field_names'      => 'total_amount,transaction_uuid,product_code',
				'signature'               => $omnify_signature,
			],
		]);
	}

	public function verify_esewa_payment(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_checkout_page_id = absint($omnify_settings['page_checkout'] ?? 0);
		$omnify_checkout_url = $omnify_checkout_page_id ? (string) get_permalink($omnify_checkout_page_id) : home_url('/checkout/');
		$omnify_order_id = absint($omnify_request->get_param('order_id'));

		if ($omnify_request->get_param('failed')) {
			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_esewa' => 'failed', 'order_id' => $omnify_order_id], $omnify_checkout_url));
				exit;
			}
			return new WP_Error('omnify_esewa_failed', __('eSewa payment was not completed.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_data_param = (string) ($omnify_request->get_param('data') ?? '');
		$omnify_txn_code   = '';
		$omnify_uuid       = '';
		$omnify_status     = '';
		$omnify_signature  = '';
		$omnify_signed_fields = '';
		$omnify_decoded    = null;

		if ('' !== $omnify_data_param) {
			$omnify_decoded = json_decode((string) base64_decode($omnify_data_param), true);
			if (is_array($omnify_decoded)) {
				$omnify_status        = (string) ($omnify_decoded['status'] ?? '');
				$omnify_txn_code      = sanitize_text_field((string) ($omnify_decoded['transaction_code'] ?? ''));
				$omnify_uuid          = sanitize_text_field((string) ($omnify_decoded['transaction_uuid'] ?? ''));
				$omnify_signature     = (string) ($omnify_decoded['signature'] ?? '');
				$omnify_signed_fields = (string) ($omnify_decoded['signed_field_names'] ?? '');
			}
		}

		if (! $omnify_order_id && '' !== $omnify_uuid) {
			global $wpdb;
			$omnify_order_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}omnify_orders WHERE transaction_id = %s",
				$omnify_uuid
			));
		}

		// Verify cryptographic signature from eSewa v2
		$omnify_mode = 'live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["esewa_{$omnify_mode}_secret_key"] ?? ($omnify_mode === 'test' ? '8gBm/:&EnhH.1/q' : '')));

		$omnify_signature_valid = false;
		if ('' !== $omnify_secret_key && '' !== $omnify_signature && '' !== $omnify_signed_fields && is_array($omnify_decoded)) {
			$field_names = explode(',', $omnify_signed_fields);
			$message_parts = [];
			foreach ($field_names as $fname) {
				$fname = trim($fname);
				if (isset($omnify_decoded[$fname])) {
					$message_parts[] = $fname . '=' . $omnify_decoded[$fname];
				}
			}
			$calculated_message = implode(',', $message_parts);
			$calculated_sig = base64_encode(hash_hmac('sha256', $calculated_message, $omnify_secret_key, true));
			$omnify_signature_valid = hash_equals($calculated_sig, $omnify_signature);
		}

		if ('COMPLETE' === $omnify_status && $omnify_order_id && $omnify_signature_valid) {
			$this->complete_order_payment($omnify_order_id, 'esewa', $omnify_txn_code ?: $omnify_uuid);

			if ('GET' === $omnify_request->get_method()) {
				wp_safe_redirect(add_query_arg(['omnify_esewa' => 'success', 'order_id' => $omnify_order_id], $omnify_checkout_url));
				exit;
			}

			return rest_ensure_response([
				'success'  => true,
				'order_id' => $omnify_order_id,
			]);
		}

		if ('GET' === $omnify_request->get_method()) {
			wp_safe_redirect(add_query_arg(['omnify_esewa' => 'failed', 'order_id' => $omnify_order_id], $omnify_checkout_url));
			exit;
		}

		return new WP_Error('omnify_esewa_verify_failed', __('eSewa payment verification failed.', 'omnifywp-ecommerce'), ['status' => 400]);
	}

	private function razorpay_amount(float $omnify_amount, string $omnify_currency): int {
		// Most currencies use 100, some like JPY use 1. Razorpay docs recommend *100 for supported.
		$omnify_zero_decimal = ['JPY', 'INR' /* wait no, INR is 100 paise */ ];
		// For safety, Razorpay always multiplies to smallest unit. For INR it's *100.
		// We assume all use *100 unless zero decimal.
		$omnify_zero_decimal_currencies = ['JPY', 'KRW', 'VND'];
		if (in_array(strtoupper($omnify_currency), $omnify_zero_decimal_currencies, true)) {
			return (int) round($omnify_amount);
		}
		return (int) round($omnify_amount * 100);
	}

	private function create_omnify_order_for_gateway(array $omnify_data, string $omnify_payment_method, array $omnify_settings): array|WP_Error {
		// Reuse existing validation + totals + customer + order creation logic but force pending_payment
		$omnify_cart_items = $this->resolve_checkout_items($omnify_data, $omnify_settings);
		if (is_wp_error($omnify_cart_items)) {
			return $omnify_cart_items;
		}

		$omnify_has_physical = ! empty(array_filter($omnify_cart_items, static fn(array $omnify_item): bool => ! empty($omnify_item['is_physical'])));

		$omnify_val_err = $this->validate_checkout_payload($omnify_data, $omnify_settings, $omnify_cart_items, $omnify_has_physical, $omnify_payment_method);
		if (is_wp_error($omnify_val_err)) {
			return $omnify_val_err;
		}

		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_customer = $this->resolve_checkout_customer($omnify_data, $omnify_settings, $omnify_email);
		if (is_wp_error($omnify_customer)) {
			return $omnify_customer;
		}

		$omnify_coupon_code = sanitize_text_field((string) ($omnify_data['coupon_code'] ?? ''));
		$omnify_totals = $this->calculate_checkout_totals($omnify_cart_items, $omnify_settings, $omnify_coupon_code, $omnify_customer ? (int) $omnify_customer['id'] : null, $omnify_has_physical, $omnify_data);
		if (is_wp_error($omnify_totals)) {
			return $omnify_totals;
		}

		$omnify_order_id = $this->omnify_orders->create([
			'customer_id'          => $omnify_customer['id'],
			'status'               => 'pending_payment',
			'currency'             => $omnify_totals['currency'],
			'subtotal'             => $omnify_totals['subtotal'],
			'tax'                  => $omnify_totals['tax'],
			'tax_label'            => $omnify_totals['tax_label'],
			'tax_rate'             => $omnify_totals['tax_rate'],
			'tax_reporting_code'   => $omnify_totals['tax_reporting_code'],
			'tax_inclusive'        => $omnify_totals['prices_include_tax'],
			'tax_shipping'         => $omnify_totals['tax_shipping'],
			'customer_tax_exempt'  => $omnify_totals['tax_exempt'],
			'shipping_total'       => $omnify_totals['shipping_total'],
			'shipping_method'      => $omnify_has_physical ? $omnify_totals['shipping_method'] : null,
			'total'                => $omnify_totals['total'],
			'coupon_code'          => $omnify_coupon_code ?: null,
			'discount_amount'      => $omnify_totals['discount'],
			'payment_method'       => $omnify_payment_method,
			'payment_instructions' => null,
			// shipping/billing abbreviated for brevity (full in real checkout)
			'fulfillment_status'   => $omnify_has_physical ? 'unfulfilled' : 'none',
			'items'                => array_map(
				static fn(array $omnify_item): array => [
					'product_id'   => (int) $omnify_item['product']['id'],
					'variation_id' => $omnify_item['variation'] ? (int) $omnify_item['variation']['id'] : null,
					'product_name' => $omnify_item['item_name'],
					'price'        => $omnify_item['price'],
					'tax'          => $omnify_item['line_tax'],
					'quantity'     => $omnify_item['quantity'],
				],
				$omnify_totals['items']
			),
		]);

		if (! $omnify_order_id) {
			return new WP_Error('omnify_order_create_failed', __('Could not create order.', 'omnifywp-ecommerce'), ['status' => 500]);
		}

		// Reduce stock etc.
		foreach ($omnify_cart_items as $omnify_item) {
			$this->reduce_managed_stock_for_checkout($omnify_item['product'], $omnify_item['variation'], $omnify_item['quantity'], $omnify_settings, (int) $omnify_order_id);
		}

		if (! empty($omnify_totals['coupon_id'])) {
			$this->omnify_coupons->increment_usage((int) $omnify_totals['coupon_id']);
		}

		$this->mark_abandoned_cart_recovered_from_payload($omnify_data, (int) $omnify_order_id);

		return [
			'order_id'     => $omnify_order_id,
			'total'        => $omnify_totals['total'],
			'currency'     => $omnify_totals['currency'],
			'email'        => $omnify_email,
			'item_name'    => count($omnify_cart_items) > 1 ? 'Multiple items' : ($omnify_cart_items[0]['item_name'] ?? 'Order'),
			'transaction_id' => '',
		];
	}

	private function razorpay_request(string $omnify_endpoint, array $omnify_body, string $omnify_key_id, string $omnify_key_secret): array|WP_Error {
		$omnify_response = wp_remote_post(
			'https://api.razorpay.com/v1/' . ltrim($omnify_endpoint, '/'),
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($omnify_key_id . ':' . $omnify_key_secret),
					'Content-Type'  => 'application/json',
				],
				'body'    => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) && ! empty($omnify_decoded['error']['description']) ? (string) $omnify_decoded['error']['description'] : __('Razorpay request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_razorpay_request_failed', $omnify_message, ['status' => $omnify_code]);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function stripe_secret_key(array $omnify_settings): string {
		$omnify_mode = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';
		return trim((string) ($omnify_settings["stripe_{$omnify_mode}_secret_key"] ?? ''));
	}

	private function paypal_base_url(array $omnify_settings): string {
		return 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox')
			? 'https://api-m.paypal.com/'
			: 'https://api-m.sandbox.paypal.com/';
	}

	private function paypal_credentials(array $omnify_settings): array {
		$omnify_mode = 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox';

		return [
			'client_id' => trim((string) ($omnify_settings["paypal_{$omnify_mode}_client_id"] ?? '')),
			'secret' => trim((string) ($omnify_settings["paypal_{$omnify_mode}_secret"] ?? '')),
		];
	}

	private function paypal_access_token(array $omnify_settings): string|WP_Error {
		$omnify_credentials = $this->paypal_credentials($omnify_settings);
		if ('' === $omnify_credentials['client_id'] || '' === $omnify_credentials['secret']) {
			return new WP_Error('omnify_paypal_credentials_missing', __('PayPal credentials are missing.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_response = wp_remote_post(
			$this->paypal_base_url($omnify_settings) . 'v1/oauth2/token',
			[
				'timeout' => 30,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($omnify_credentials['client_id'] . ':' . $omnify_credentials['secret']),
				],
				'body' => [
					'grant_type' => 'client_credentials',
				],
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300 || ! is_array($omnify_decoded) || empty($omnify_decoded['access_token'])) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['error_description'] ?? $omnify_decoded['error'] ?? __('PayPal authentication failed.', 'omnifywp-ecommerce')) : __('PayPal authentication failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_paypal_auth_failed', $omnify_message, ['status' => 400]);
		}

		return (string) $omnify_decoded['access_token'];
	}

	private function paypal_request(string $omnify_method, string $omnify_endpoint, mixed $omnify_body, string $omnify_access_token, array $omnify_settings): array|WP_Error {
		$omnify_args = [
			'method' => strtoupper($omnify_method),
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_access_token,
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
		];

		if (null !== $omnify_body) {
			$omnify_args['body'] = wp_json_encode($omnify_body);
		}

		$omnify_response = wp_remote_request($this->paypal_base_url($omnify_settings) . ltrim($omnify_endpoint, '/'), $omnify_args);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['message'] ?? $omnify_decoded['name'] ?? __('PayPal request failed.', 'omnifywp-ecommerce')) : __('PayPal request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_paypal_request_failed', $omnify_message, ['status' => 400]);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function paypal_amount_value(float $omnify_amount, string $omnify_currency): string {
		$omnify_zero_decimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

		return in_array(strtoupper($omnify_currency), $omnify_zero_decimal, true)
			? (string) (int) round($omnify_amount)
			: number_format($omnify_amount, 2, '.', '');
	}

	private function verify_paypal_webhook(WP_REST_Request $omnify_request, array $omnify_event, array $omnify_settings): bool|WP_Error {
		$omnify_access_token = $this->paypal_access_token($omnify_settings);
		if (is_wp_error($omnify_access_token)) {
			return $omnify_access_token;
		}

		$omnify_response = $this->paypal_request(
			'POST',
			'v1/notifications/verify-webhook-signature',
			[
				'auth_algo' => (string) $omnify_request->get_header('paypal-auth-algo'),
				'cert_url' => (string) $omnify_request->get_header('paypal-cert-url'),
				'transmission_id' => (string) $omnify_request->get_header('paypal-transmission-id'),
				'transmission_sig' => (string) $omnify_request->get_header('paypal-transmission-sig'),
				'transmission_time' => (string) $omnify_request->get_header('paypal-transmission-time'),
				'webhook_id' => (string) ($omnify_settings['paypal_webhook_id'] ?? ''),
				'webhook_event' => $omnify_event,
			],
			$omnify_access_token,
			$omnify_settings
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return 'SUCCESS' === ($omnify_response['verification_status'] ?? '');
	}

	private function paypal_order_id_from_event(array $omnify_event): int {
		$omnify_resource = $omnify_event['resource'] ?? [];
		if (! is_array($omnify_resource)) {
			return 0;
		}

		$omnify_candidates = [
			$omnify_resource['custom_id'] ?? null,
			$omnify_resource['invoice_id'] ?? null,
			$omnify_resource['purchase_units'][0]['custom_id'] ?? null,
			$omnify_resource['purchase_units'][0]['reference_id'] ?? null,
			$omnify_resource['purchase_units'][0]['payments']['captures'][0]['custom_id'] ?? null,
			$omnify_resource['purchase_units'][0]['payments']['captures'][0]['invoice_id'] ?? null,
		];

		foreach ($omnify_candidates as $omnify_candidate) {
			$omnify_order_id = absint($omnify_candidate);
			if ($omnify_order_id) {
				return $omnify_order_id;
			}
		}

		return 0;
	}

	private function stripe_amount(float $omnify_amount, string $omnify_currency): int {
		$omnify_zero_decimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
		return in_array(strtoupper($omnify_currency), $omnify_zero_decimal, true)
			? (int) round($omnify_amount)
			: (int) round($omnify_amount * 100);
	}

	private function stripe_request(string $omnify_endpoint, array $omnify_body, string $omnify_secret_key): array|WP_Error {
		$omnify_response = wp_remote_post(
			'https://api.stripe.com/v1/' . ltrim($omnify_endpoint, '/'),
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_secret_key,
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => $omnify_body,
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['error']['message'] ?? __('Stripe request failed.', 'omnifywp-ecommerce')) : __('Stripe request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_stripe_request_failed', $omnify_message, ['status' => 400]);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function verify_stripe_signature(string $omnify_payload, string $omnify_signature, string $omnify_secret): bool {
		$omnify_timestamp = '';
		$omnify_signatures = [];
		foreach (explode(',', $omnify_signature) as $omnify_part) {
			[$omnify_key, $omnify_value] = array_pad(explode('=', trim($omnify_part), 2), 2, '');
			if ('t' === $omnify_key) {
				$omnify_timestamp = $omnify_value;
			}
			if ('v1' === $omnify_key) {
				$omnify_signatures[] = $omnify_value;
			}
		}

		if ('' === $omnify_timestamp || empty($omnify_signatures)) {
			return false;
		}

		$omnify_expected = hash_hmac('sha256', $omnify_timestamp . '.' . $omnify_payload, $omnify_secret);
		foreach ($omnify_signatures as $omnify_sig) {
			if (hash_equals($omnify_expected, $omnify_sig)) {
				return true;
			}
		}

		return false;
	}

	private function verify_alipay_signature(array $omnify_params, string $omnify_public_key): bool {
		$omnify_sign = $omnify_params['sign'] ?? '';
		if ('' === $omnify_sign) {
			return false;
		}

		unset($omnify_params['sign'], $omnify_params['sign_type']);
		ksort($omnify_params);

		$omnify_string_to_sign = '';
		foreach ($omnify_params as $omnify_key => $omnify_val) {
			if ('' !== $omnify_val && null !== $omnify_val) {
				$omnify_string_to_sign .= $omnify_key . '=' . $omnify_val . '&';
			}
		}
		$omnify_string_to_sign = rtrim($omnify_string_to_sign, '&');

		$omnify_pub_key_str = trim($omnify_public_key);
		if (! str_contains($omnify_pub_key_str, '-----BEGIN PUBLIC KEY-----')) {
			$omnify_pub_key_str = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($omnify_pub_key_str, 64, "\n") . "-----END PUBLIC KEY-----";
		}

		$omnify_pub_key_res = openssl_get_publickey($omnify_pub_key_str);
		if (! $omnify_pub_key_res) {
			return false;
		}

		$omnify_result = openssl_verify($omnify_string_to_sign, base64_decode($omnify_sign), $omnify_pub_key_res, OPENSSL_ALGO_SHA256);

		return 1 === $omnify_result;
	}

	private function verify_wechat_signature(WP_REST_Request $omnify_request, string $omnify_api_key): bool {
		$omnify_sig = (string) ($omnify_request->get_header('wechatpay-signature') ?? ($omnify_request->get_header('wechat-signature') ?? ''));
		$omnify_timestamp = (string) ($omnify_request->get_header('wechatpay-timestamp') ?? ($omnify_request->get_header('wechat-timestamp') ?? ''));
		$omnify_nonce = (string) ($omnify_request->get_header('wechatpay-nonce') ?? ($omnify_request->get_header('wechat-nonce') ?? ''));
		$omnify_body = (string) $omnify_request->get_body();

		if ('' !== $omnify_sig && '' !== $omnify_timestamp && '' !== $omnify_nonce) {
			$omnify_payload = $omnify_timestamp . "\n" . $omnify_nonce . "\n" . $omnify_body . "\n";
			$omnify_expected = base64_encode(hash_hmac('sha256', $omnify_payload, $omnify_api_key, true));
			if (hash_equals($omnify_expected, $omnify_sig)) {
				return true;
			}
			$omnify_hex_expected = hash_hmac('sha256', $omnify_payload, $omnify_api_key);
			if (hash_equals($omnify_hex_expected, $omnify_sig)) {
				return true;
			}
		}

		$omnify_params = (array) ($omnify_request->get_json_params() ?: $omnify_request->get_body_params());
		if (! empty($omnify_params['sign'])) {
			$omnify_sign = (string) $omnify_params['sign'];
			unset($omnify_params['sign']);
			ksort($omnify_params);
			$omnify_string = '';
			foreach ($omnify_params as $omnify_k => $omnify_v) {
				if ('' !== (string) $omnify_v && null !== $omnify_v) {
					$omnify_string .= $omnify_k . '=' . $omnify_v . '&';
				}
			}
			$omnify_string .= 'key=' . $omnify_api_key;
			$omnify_expected_md5 = strtoupper(md5($omnify_string));
			$omnify_expected_hmac = strtoupper(hash_hmac('sha256', $omnify_string, $omnify_api_key));
			if (hash_equals($omnify_expected_md5, $omnify_sign) || hash_equals($omnify_expected_hmac, $omnify_sign)) {
				return true;
			}
		}

		return false;
	}

	private function query_wechat_order_status(string $omnify_transaction_id, string $omnify_out_trade_no, array $omnify_settings): bool {
		$omnify_mch_id = trim((string) ($omnify_settings['wechat_mch_id'] ?? ''));
		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		if ('' === $omnify_mch_id || '' === $omnify_api_key) {
			return false;
		}

		$omnify_endpoint = $omnify_transaction_id
			? 'https://api.mch.weixin.qq.com/v3/pay/transactions/id/' . rawurlencode($omnify_transaction_id) . '?mchid=' . rawurlencode($omnify_mch_id)
			: 'https://api.mch.weixin.qq.com/v3/pay/transactions/out-trade-no/' . rawurlencode($omnify_out_trade_no) . '?mchid=' . rawurlencode($omnify_mch_id);

		$omnify_response = wp_remote_get($omnify_endpoint, [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/json',
				'User-Agent' => 'Omnify-eCommerce/' . OMNIFY_VERSION,
			],
		]);

		if (is_wp_error($omnify_response)) {
			return false;
		}

		$omnify_body = wp_remote_retrieve_body($omnify_response);
		$omnify_data = json_decode($omnify_body, true);
		if (! is_array($omnify_data)) {
			return false;
		}

		$omnify_trade_state = strtoupper((string) ($omnify_data['trade_state'] ?? ''));
		return 'SUCCESS' === $omnify_trade_state;
	}

	/**
	 * Validates SSLCommerz payment with the validation server and returns the validated transaction payload.
	 *
	 * @param string $omnify_val_id
	 * @param array $omnify_settings
	 * @return array<string, mixed>|null
	 */
	private function verify_sslcommerz_transaction(string $omnify_val_id, array $omnify_settings): ?array {
		$omnify_store_id     = trim((string) ($omnify_settings['sslcommerz_store_id'] ?? ''));
		$omnify_store_passwd = trim((string) ($omnify_settings['sslcommerz_store_password'] ?? ''));
		if ('' === $omnify_store_id || '' === $omnify_store_passwd) {
			return null;
		}

		$omnify_mode          = 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_validator_url = $omnify_mode === 'live'
			? 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php'
			: 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php';

		$omnify_validator_url = add_query_arg([
			'val_id'       => $omnify_val_id,
			'store_id'     => $omnify_store_id,
			'store_passwd' => $omnify_store_passwd,
			'format'       => 'json',
		], $omnify_validator_url);

		$omnify_response = wp_safe_remote_get($omnify_validator_url);
		if (is_wp_error($omnify_response)) {
			return null;
		}

		$omnify_body = wp_remote_retrieve_body($omnify_response);
		$omnify_data = json_decode($omnify_body, true);

		if (! is_array($omnify_data)) {
			return null;
		}

		$omnify_status = strtoupper((string) ($omnify_data['status'] ?? ''));
		if ('VALID' !== $omnify_status && 'VALIDATED' !== $omnify_status) {
			return null;
		}

		return $omnify_data;
	}

	private function verify_sslcommerz_ipn(string $omnify_val_id, array $omnify_settings): bool {
		return null !== $this->verify_sslcommerz_transaction($omnify_val_id, $omnify_settings);
	}

	/**
	 * Binds the gateway-validated SSLCommerz transaction to the order and completes payment.
	 *
	 * @param int $omnify_order_id
	 * @param array $omnify_validated
	 * @param string $omnify_supplied_tran_id
	 * @return true|WP_Error
	 */
	private function bind_and_complete_sslcommerz_order(int $omnify_order_id, array $omnify_validated, string $omnify_supplied_tran_id = ''): true|WP_Error {
		$omnify_gateway_tran_id  = sanitize_text_field((string) ($omnify_validated['tran_id'] ?? ''));
		$omnify_gateway_order_id = absint($omnify_validated['value_a'] ?? 0);
		$omnify_gateway_val_id   = sanitize_text_field((string) ($omnify_validated['val_id'] ?? ''));
		$omnify_gateway_amount   = (float) ($omnify_validated['amount'] ?? 0.0);

		$omnify_target_id = $omnify_order_id ?: $omnify_gateway_order_id;
		if (! $omnify_target_id && '' !== $omnify_gateway_tran_id) {
			$omnify_target_id = absint(str_replace('ssl_', '', $omnify_gateway_tran_id));
		}

		if (! $omnify_target_id) {
			return new WP_Error('omnify_missing_order_id', __('Could not determine order ID from validated transaction.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_order = $this->omnify_orders->find($omnify_target_id);
		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		// Bind the gateway transaction ID or order ID to the order
		$omnify_expected_tran_id = 'ssl_' . $omnify_order['id'];
		$omnify_order_tran_id    = (string) ($omnify_order['transaction_id'] ?? '');

		$omnify_matches = ($omnify_gateway_tran_id === $omnify_expected_tran_id)
			|| ($omnify_gateway_tran_id === (string) $omnify_order['id'])
			|| ('' !== $omnify_order_tran_id && $omnify_gateway_tran_id === $omnify_order_tran_id)
			|| ($omnify_gateway_order_id === (int) $omnify_order['id'])
			|| ('' !== $omnify_supplied_tran_id && $omnify_gateway_tran_id === $omnify_supplied_tran_id);

		if (! $omnify_matches) {
			return new WP_Error('omnify_transaction_mismatch', __('Validated gateway transaction does not match this order ID or transaction ID.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		// Verify amount matches within acceptable tolerance
		if ($omnify_gateway_amount > 0 && abs((float) $omnify_order['total'] - $omnify_gateway_amount) > 0.10) {
			return new WP_Error('omnify_amount_mismatch', __('Paid amount does not match order total.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_txn_id = $omnify_gateway_tran_id ?: ($omnify_supplied_tran_id ?: $omnify_gateway_val_id);
		$this->complete_order_payment((int) $omnify_order['id'], 'sslcommerz', $omnify_txn_id ?: null);

		return true;
	}

	private function prepare_stripe_order_context(array $omnify_data, string $omnify_payment_method): array|WP_Error {
		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);
		$omnify_quantity = max(1, absint($omnify_data['quantity'] ?? 1));
		$omnify_settings = $this->omnify_settings->all();
		if (! empty($omnify_data['items']) && is_array($omnify_data['items'])) {
			return $this->prepare_cart_order_context($omnify_data, $omnify_payment_method, $omnify_settings);
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== $omnify_product['status']) {
			return new WP_Error('omnify_checkout_product_not_found', __('The product is not available for purchase.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_variation = null;
		$omnify_variation_id = absint($omnify_data['variation_id'] ?? 0);
		if ('variable' === ($omnify_product['type'] ?? '') && ! $omnify_variation_id) {
			return new WP_Error('omnify_checkout_variation_required', __('Please select a product variation.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		if ($omnify_variation_id && ! empty($omnify_product['variations']) && is_array($omnify_product['variations'])) {
			foreach ($omnify_product['variations'] as $omnify_candidate) {
				if ((int) ($omnify_candidate['id'] ?? 0) === $omnify_variation_id) {
					$omnify_variation = $omnify_candidate;
					break;
				}
			}
		}

		if ($omnify_variation_id && ! $omnify_variation) {
			return new WP_Error('omnify_checkout_variation_not_found', __('The selected product variation is not available.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
		$omnify_is_physical = ('physical' === ($omnify_product['type'] ?? 'download')) || ('variable' === ($omnify_product['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));

		$omnify_items = [
			[
				'product' => $omnify_product,
				'variation' => $omnify_variation,
				'quantity' => $omnify_quantity
			]
		];

		$omnify_val_err = $this->validate_checkout_payload($omnify_data, $omnify_settings, $omnify_items, $omnify_is_physical, $omnify_payment_method);
		if (is_wp_error($omnify_val_err)) {
			return $omnify_val_err;
		}

		$omnify_price = $omnify_variation
			? ((null !== ($omnify_variation['sale_price'] ?? null) && '' !== $omnify_variation['sale_price']) ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'])
			: ((null !== ($omnify_product['sale_price'] ?? null) && '' !== $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price']);
		$omnify_item_name = (string) $omnify_product['name'];
		if ($omnify_variation) {
			$omnify_variation_bits = [];
			foreach (($omnify_variation['attributes'] ?? []) as $omnify_attribute_name => $omnify_attribute_value) {
				$omnify_variation_bits[] = $omnify_attribute_name . ': ' . $omnify_attribute_value;
			}
			$omnify_item_name .= empty($omnify_variation_bits) ? '' : ' - ' . implode(', ', $omnify_variation_bits);
			$omnify_item_name .= empty($omnify_variation['sku']) ? '' : ' (' . $omnify_variation['sku'] . ')';
		} elseif (! empty($omnify_product['sku'])) {
			$omnify_item_name .= ' (' . $omnify_product['sku'] . ')';
		}
			$omnify_subtotal = $omnify_price * $omnify_quantity;
			$omnify_discount_amount = 0.0;
			$omnify_coupon_code = sanitize_text_field($omnify_data['coupon_code'] ?? '');
			$omnify_coupon_id = null;
			$omnify_coupon_free_shipping = false;
				$omnify_wp_user_id = get_current_user_id() ?: (email_exists($omnify_email) ?: null);

				$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
				if (! $omnify_customer) {
					$omnify_customer_id = $this->omnify_customers->create([
						'email' => $omnify_email,
						'user_id' => $omnify_wp_user_id,
						'first_name' => sanitize_text_field((string) ($omnify_data['first_name'] ?? '')),
						'last_name' => sanitize_text_field((string) ($omnify_data['last_name'] ?? '')),
						'phone' => sanitize_text_field((string) ($omnify_data['phone'] ?? '')),
						'status' => 'active',
					]);
					$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
				}

			if ('' !== $omnify_coupon_code) {
				if (empty($omnify_settings['enable_coupons'])) {
					return new WP_Error('omnify_coupons_disabled', __('Coupons are not enabled for this checkout.', 'omnifywp-ecommerce'), ['status' => 400]);
				}
				$omnify_coupon_result = $this->evaluate_coupon($omnify_coupon_code, $omnify_product, $omnify_subtotal, $omnify_customer ? (int) $omnify_customer['id'] : null);
				if (is_wp_error($omnify_coupon_result)) {
					return $omnify_coupon_result;
				}
				$omnify_coupon_id = (int) $omnify_coupon_result['coupon']['id'];
				$omnify_discount_amount = (float) $omnify_coupon_result['discount'];
				$omnify_coupon_free_shipping = ! empty($omnify_coupon_result['free_shipping']);
			}

			$omnify_net_subtotal = max(0.0, $omnify_subtotal - $omnify_discount_amount);
			$omnify_shipping_country = $omnify_is_physical ? sanitize_text_field((string) ($omnify_data['shipping_country'] ?? '')) : '';
			$omnify_shipping_state = $omnify_is_physical ? sanitize_text_field((string) ($omnify_data['shipping_state'] ?? '')) : '';
			$omnify_tax_rule = $this->resolve_tax_rule($omnify_settings, $omnify_shipping_country, $omnify_shipping_state);
			$omnify_shipping_classes = $omnify_is_physical ? $this->collect_shipping_classes([['product' => $omnify_product, 'quantity' => $omnify_quantity]]) : [];
			$omnify_delivery = $this->resolve_delivery_zone($omnify_settings, $omnify_shipping_country, $omnify_shipping_state, $omnify_net_subtotal, $omnify_is_physical, $omnify_shipping_classes, sanitize_text_field((string) ($omnify_data['shipping_method_id'] ?? '')));
			if (is_wp_error($omnify_delivery)) {
				return $omnify_delivery;
			}
			if ($omnify_coupon_free_shipping) {
				$omnify_delivery['amount'] = 0.0;
				$omnify_delivery['method'] = $omnify_delivery['method'] ?: __('Free shipping', 'omnifywp-ecommerce');
			}
			$omnify_tax_breakdown = $this->calculate_tax_breakdown($omnify_net_subtotal, (float) $omnify_delivery['amount'], (float) $omnify_tax_rule['rate'], $omnify_settings, $this->customer_is_tax_exempt($omnify_customer ? (int) $omnify_customer['id'] : null));
			$omnify_tax = (float) $omnify_tax_breakdown['tax'];
			$omnify_total = (float) $omnify_tax_breakdown['total'];

		$omnify_order_id = $this->omnify_orders->create([
			'customer_id' => $omnify_customer['id'],
			'status' => 'pending_payment',
			'currency' => $omnify_product['currency'],
			'subtotal' => $omnify_subtotal,
			'tax' => $omnify_tax,
			'tax_label' => $omnify_tax_rule['label'],
			'tax_rate' => (float) $omnify_tax_rule['rate'],
			'tax_reporting_code' => $omnify_tax_rule['reporting_code'] ?? '',
			'tax_inclusive' => ! empty($omnify_settings['prices_include_tax']),
			'tax_shipping' => ! empty($omnify_settings['tax_shipping']),
			'customer_tax_exempt' => $this->customer_is_tax_exempt($omnify_customer ? (int) $omnify_customer['id'] : null),
			'shipping_total' => (float) $omnify_delivery['amount'],
			'shipping_method' => $omnify_is_physical ? $omnify_delivery['method'] : null,
			'total' => $omnify_total,
			'coupon_code' => $omnify_coupon_code ?: null,
			'discount_amount' => $omnify_discount_amount,
			'payment_method' => $omnify_payment_method,
			'payment_instructions' => null,
			'shipping_first_name' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_first_name']) : null,
			'shipping_last_name' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_last_name']) : null,
			'shipping_address_1' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_address_1']) : null,
			'shipping_address_2' => ($omnify_is_physical && ! empty($omnify_data['shipping_address_2'])) ? sanitize_text_field($omnify_data['shipping_address_2']) : null,
			'shipping_city' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_city']) : null,
			'shipping_state' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_state']) : null,
			'shipping_postcode' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_postcode']) : null,
			'shipping_country' => $omnify_is_physical ? sanitize_text_field($omnify_data['shipping_country']) : null,
			'shipping_phone' => ($omnify_is_physical && ! empty($omnify_data['shipping_phone'])) ? sanitize_text_field($omnify_data['shipping_phone']) : null,
			'billing_first_name'   => ! empty($omnify_data['billing_first_name']) ? sanitize_text_field($omnify_data['billing_first_name']) : null,
			'billing_last_name'    => ! empty($omnify_data['billing_last_name']) ? sanitize_text_field($omnify_data['billing_last_name']) : null,
			'billing_company'      => ! empty($omnify_data['billing_company']) ? sanitize_text_field($omnify_data['billing_company']) : null,
			'billing_phone'        => ! empty($omnify_data['billing_phone']) ? sanitize_text_field($omnify_data['billing_phone']) : null,
			'billing_address_1'    => ! empty($omnify_data['billing_address_1']) ? sanitize_text_field($omnify_data['billing_address_1']) : null,
			'billing_address_2'    => ! empty($omnify_data['billing_address_2']) ? sanitize_text_field($omnify_data['billing_address_2']) : null,
			'billing_city'         => ! empty($omnify_data['billing_city']) ? sanitize_text_field($omnify_data['billing_city']) : null,
			'billing_state'        => ! empty($omnify_data['billing_state']) ? sanitize_text_field($omnify_data['billing_state']) : null,
			'billing_postcode'     => ! empty($omnify_data['billing_postcode']) ? sanitize_text_field($omnify_data['billing_postcode']) : null,
			'billing_country'      => ! empty($omnify_data['billing_country']) ? sanitize_text_field($omnify_data['billing_country']) : null,
			'billing_same_as_shipping' => isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1,
			'fulfillment_status' => $omnify_is_physical ? 'unfulfilled' : 'none',
			'items' => [[
				'product_id' => $omnify_product['id'],
				'variation_id' => $omnify_variation ? (int) $omnify_variation['id'] : null,
				'product_name' => $omnify_item_name,
				'price' => $omnify_price,
				'tax' => (float) ($omnify_tax_breakdown['product_tax'] ?? $omnify_tax),
				'quantity' => $omnify_quantity,
			]],
			]);

			if ($omnify_coupon_id) {
				$this->omnify_coupons->increment_usage($omnify_coupon_id);
			}

			if ($omnify_order_id) {
				$this->reduce_managed_stock_for_checkout($omnify_product, $omnify_variation, $omnify_quantity, $omnify_settings, (int) $omnify_order_id);
			}

			$this->mark_abandoned_cart_recovered_from_payload($omnify_data, (int) $omnify_order_id);

		return [
			'order_id' => $omnify_order_id,
			'email' => $omnify_email,
			'currency' => (string) $omnify_product['currency'],
			'total' => $omnify_total,
			'item_name' => $omnify_item_name,
		];
	}

	private function reduce_managed_stock_for_checkout(array $omnify_product, ?array $omnify_variation, int $omnify_quantity, array $omnify_settings, int $omnify_order_id): void {
		if (empty($omnify_settings['reduce_stock_on_checkout'])) {
			return;
		}

		global $wpdb;

		$omnify_threshold = max(0, absint($omnify_settings['low_stock_threshold'] ?? 5));

		if ($omnify_variation) {
			if (empty($omnify_variation['manage_stock'])) {
				return;
			}

			$omnify_old_qty = (int) ($omnify_variation['stock_qty'] ?? 0);
			$omnify_updated = $this->omnify_products->reduce_stock((int) $omnify_product['id'], (int) $omnify_variation['id'], $omnify_quantity);

			if ($omnify_updated) {
				$omnify_new_qty = $this->omnify_products->get_variation_stock_qty((int) $omnify_variation['id']);
				$this->omnify_products->log_stock_change(
					(int) $omnify_product['id'],
					(int) $omnify_variation['id'],
					-$omnify_quantity,
					$omnify_new_qty,
					// translators: %d: placeholder value.
					sprintf(__('Checkout Order #%d', 'omnifywp-ecommerce'), $omnify_order_id)
				);
				if ($this->should_send_low_stock_alert($omnify_old_qty, $omnify_new_qty, $omnify_threshold)) {
					$this->omnify_emails->send_low_stock_alert($omnify_product, $omnify_variation, $omnify_old_qty, $omnify_new_qty, $omnify_threshold);
				}
			}

			return;
		}

		if (empty($omnify_product['manage_stock'])) {
			return;
		}

		$omnify_old_qty = (int) ($omnify_product['stock_qty'] ?? 0);
		$omnify_updated = $this->omnify_products->reduce_stock((int) $omnify_product['id'], null, $omnify_quantity);

		if ($omnify_updated) {
			$omnify_new_qty = $this->omnify_products->get_product_stock_qty((int) $omnify_product['id']);
			$this->omnify_products->log_stock_change(
				(int) $omnify_product['id'],
				null,
				-$omnify_quantity,
				$omnify_new_qty,
				// translators: %d: placeholder value.
				sprintf(__('Checkout Order #%d', 'omnifywp-ecommerce'), $omnify_order_id)
			);
			if ($this->should_send_low_stock_alert($omnify_old_qty, $omnify_new_qty, $omnify_threshold)) {
				$this->omnify_emails->send_low_stock_alert($omnify_product, null, $omnify_old_qty, $omnify_new_qty, $omnify_threshold);
			}
		}
	}

	private function should_send_low_stock_alert(int $omnify_old_qty, int $omnify_new_qty, int $omnify_threshold): bool {
		return $omnify_threshold > 0 && $omnify_old_qty > $omnify_threshold && $omnify_new_qty <= $omnify_threshold;
	}

	private function mark_abandoned_cart_recovered_from_payload(array $omnify_data, int $omnify_order_id): void {
		$omnify_token = sanitize_text_field((string) ($omnify_data['abandoned_cart_token'] ?? $omnify_data['omnify_recover_cart'] ?? ''));
		if ($omnify_order_id > 0 && '' !== $omnify_token) {
			$this->omnify_abandoned_carts->mark_recovered($omnify_token, $omnify_order_id);
		}
	}

	private function prepare_cart_order_context(array $omnify_data, string $omnify_payment_method, array $omnify_settings): array|WP_Error {
		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));

		$omnify_cart_items = $this->resolve_checkout_items($omnify_data, $omnify_settings);
		if (is_wp_error($omnify_cart_items)) {
			return $omnify_cart_items;
		}
		$omnify_has_physical = ! empty(array_filter($omnify_cart_items, static fn(array $omnify_item): bool => ! empty($omnify_item['is_physical'])));

		$omnify_val_err = $this->validate_checkout_payload($omnify_data, $omnify_settings, $omnify_cart_items, $omnify_has_physical, $omnify_payment_method);
		if (is_wp_error($omnify_val_err)) {
			return $omnify_val_err;
		}

		$omnify_customer = $this->resolve_checkout_customer($omnify_data, $omnify_settings, $omnify_email);
		if (is_wp_error($omnify_customer)) {
			return $omnify_customer;
		}

		$omnify_coupon_code = sanitize_text_field((string) ($omnify_data['coupon_code'] ?? ''));
		$omnify_totals = $this->calculate_checkout_totals($omnify_cart_items, $omnify_settings, $omnify_coupon_code, $omnify_customer ? (int) $omnify_customer['id'] : null, $omnify_has_physical, $omnify_data);
		if (is_wp_error($omnify_totals)) {
			return $omnify_totals;
		}

		$omnify_order_id = $this->omnify_orders->create([
			'customer_id'         => $omnify_customer['id'],
			'status'              => 'pending_payment',
			'currency'            => $omnify_totals['currency'],
			'subtotal'            => $omnify_totals['subtotal'],
			'tax'                 => $omnify_totals['tax'],
			'tax_label'           => $omnify_totals['tax_label'],
			'tax_rate'            => $omnify_totals['tax_rate'],
			'tax_reporting_code'  => $omnify_totals['tax_reporting_code'],
			'tax_inclusive'       => $omnify_totals['prices_include_tax'],
			'tax_shipping'        => $omnify_totals['tax_shipping'],
			'customer_tax_exempt' => $omnify_totals['tax_exempt'],
			'shipping_total'      => $omnify_totals['shipping_total'],
			'shipping_method'     => $omnify_has_physical ? $omnify_totals['shipping_method'] : null,
			'total'               => $omnify_totals['total'],
			'coupon_code'         => $omnify_coupon_code ?: null,
			'discount_amount'     => $omnify_totals['discount'],
			'payment_method'      => $omnify_payment_method,
			'shipping_first_name' => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_first_name']) : null,
			'shipping_last_name'  => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_last_name']) : null,
			'shipping_address_1'  => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_address_1']) : null,
			'shipping_address_2'  => ($omnify_has_physical && ! empty($omnify_data['shipping_address_2'])) ? sanitize_text_field((string) $omnify_data['shipping_address_2']) : null,
			'shipping_city'       => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_city']) : null,
			'shipping_state'      => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_state']) : null,
			'shipping_postcode'   => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_postcode']) : null,
			'shipping_country'    => $omnify_has_physical ? sanitize_text_field((string) $omnify_data['shipping_country']) : null,
			'shipping_phone'      => ($omnify_has_physical && ! empty($omnify_data['shipping_phone'])) ? sanitize_text_field((string) $omnify_data['shipping_phone']) : null,
			'billing_first_name'   => ! empty($omnify_data['billing_first_name']) ? sanitize_text_field($omnify_data['billing_first_name']) : null,
			'billing_last_name'    => ! empty($omnify_data['billing_last_name']) ? sanitize_text_field($omnify_data['billing_last_name']) : null,
			'billing_company'      => ! empty($omnify_data['billing_company']) ? sanitize_text_field($omnify_data['billing_company']) : null,
			'billing_phone'        => ! empty($omnify_data['billing_phone']) ? sanitize_text_field($omnify_data['billing_phone']) : null,
			'billing_address_1'    => ! empty($omnify_data['billing_address_1']) ? sanitize_text_field($omnify_data['billing_address_1']) : null,
			'billing_address_2'    => ! empty($omnify_data['billing_address_2']) ? sanitize_text_field($omnify_data['billing_address_2']) : null,
			'billing_city'         => ! empty($omnify_data['billing_city']) ? sanitize_text_field($omnify_data['billing_city']) : null,
			'billing_state'        => ! empty($omnify_data['billing_state']) ? sanitize_text_field($omnify_data['billing_state']) : null,
			'billing_postcode'     => ! empty($omnify_data['billing_postcode']) ? sanitize_text_field($omnify_data['billing_postcode']) : null,
			'billing_country'      => ! empty($omnify_data['billing_country']) ? sanitize_text_field($omnify_data['billing_country']) : null,
			'billing_same_as_shipping' => isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1,
			'fulfillment_status'  => $omnify_has_physical ? 'unfulfilled' : 'none',
			'items'               => array_map(
				static fn(array $omnify_item): array => [
					'product_id'   => (int) $omnify_item['product']['id'],
					'variation_id' => $omnify_item['variation'] ? (int) $omnify_item['variation']['id'] : null,
					'product_name' => $omnify_item['item_name'],
					'price'        => $omnify_item['price'],
					'tax'          => $omnify_item['line_tax'],
					'quantity'     => $omnify_item['quantity'],
				],
				$omnify_totals['items']
			),
		]);

		if (! empty($omnify_totals['coupon_id'])) {
			$this->omnify_coupons->increment_usage((int) $omnify_totals['coupon_id']);
		}
		if ($omnify_order_id) {
			foreach ($omnify_cart_items as $omnify_item) {
				$this->reduce_managed_stock_for_checkout($omnify_item['product'], $omnify_item['variation'], $omnify_item['quantity'], $omnify_settings, (int) $omnify_order_id);
			}
		}
		$this->mark_abandoned_cart_recovered_from_payload($omnify_data, (int) $omnify_order_id);

		return [
			'order_id' => $omnify_order_id,
			'email' => $omnify_email,
			'currency' => (string) $omnify_totals['currency'],
			'total' => (float) $omnify_totals['total'],
			// translators: %d: placeholder value.
			'item_name' => count($omnify_cart_items) === 1 ? (string) $omnify_cart_items[0]['item_name'] : sprintf(__('%d-item order', 'omnifywp-ecommerce'), count($omnify_cart_items)),
		];
	}

	private function complete_order_payment(int $omnify_order_id, string $omnify_source, ?string $omnify_transaction_id = null): bool {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'START TRANSACTION');

		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_order_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT status FROM {$omnify_orders_table} WHERE id = %d FOR UPDATE",
			$omnify_order_id
		), ARRAY_A);

		if (! $omnify_order_row || 'completed' === ($omnify_order_row['status'] ?? '')) {
			\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'COMMIT');
			return false;
		}

		\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$omnify_orders_table,
			[
				'status'     => 'completed',
				'updated_at' => current_time('mysql', true),
			],
			['id' => $omnify_order_id]
		);

		if ($omnify_transaction_id) {
			\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
				$omnify_orders_table,
				['transaction_id' => $omnify_transaction_id],
				['id' => $omnify_order_id]
			);
		}

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'COMMIT');

		// Now fetch full order object for post-payment hooks/emails
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_prod = $this->omnify_products->find((int) $omnify_item['product_id']);
			$omnify_prod_variation_settings = $omnify_prod ? wp_parse_args($omnify_prod['variation_settings'] ?? [], ['product_kind' => 'digital']) : [];
			$omnify_prod_is_physical = $omnify_prod && ('physical' === ($omnify_prod['type'] ?? 'download') || ('variable' === ($omnify_prod['type'] ?? 'download') && 'physical' === ($omnify_prod_variation_settings['product_kind'] ?? 'digital')));
			if ($omnify_prod && ! $omnify_prod_is_physical && ! empty($omnify_order['customer_id'])) {
				$this->omnify_access->grant((int) $omnify_order['customer_id'], (int) $omnify_item['product_id'], $this->download_access_expires_at($omnify_prod));
			}
		}

		if ($omnify_order['customer_id']) {
			$this->omnify_customers->record_activity(
				(int) $omnify_order['customer_id'],
				'payment_received',
				// translators: %1$d: placeholder value, %2$s: placeholder value.
				sprintf(__('Payment received for Order #%1$d via %2$s.', 'omnifywp-ecommerce'), $omnify_order_id, $omnify_source),
				['order_id' => $omnify_order_id]
			);
		}

		$this->omnify_orders->add_note(
			$omnify_order_id,
			// translators: %s: placeholder value.
			sprintf(__('Payment confirmed via %s. Order status changed to Completed.', 'omnifywp-ecommerce'), $omnify_source)
		);

		$this->omnify_emails->send_receipt($omnify_order_id);
		$this->omnify_emails->send_admin_new_order($omnify_order_id);

		return true;
	}

	private function cancel_order_payment(int $omnify_order_id, string $omnify_source, string $omnify_reason): bool {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'START TRANSACTION');

		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_order_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT status FROM {$omnify_orders_table} WHERE id = %d FOR UPDATE",
			$omnify_order_id
		), ARRAY_A);

		if (! $omnify_order_row) {
			\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'ROLLBACK');
			return false;
		}

		$omnify_current_status = $omnify_order_row['status'] ?? 'pending';
		if (! in_array($omnify_current_status, ['pending', 'pending_payment'], true)) {
			\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'ROLLBACK');
			return false;
		}

		\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$omnify_orders_table,
			[
				'status'     => 'cancelled',
				'updated_at' => current_time('mysql', true),
			],
			['id' => $omnify_order_id]
		);

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, 'COMMIT');

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return false;
		}

		$omnify_settings = $this->omnify_settings->all();
		if (! empty($omnify_settings['reduce_stock_on_checkout'])) {
			foreach ($omnify_order['items'] as $omnify_item) {
				$omnify_product_id   = (int) $omnify_item['product_id'];
				$omnify_variation_id = ! empty($omnify_item['variation_id']) ? (int) $omnify_item['variation_id'] : null;
				$omnify_quantity     = (int) $omnify_item['quantity'];

				if ($omnify_quantity > 0) {
					$this->omnify_products->increment_stock($omnify_product_id, $omnify_variation_id, $omnify_quantity);

					$omnify_new_qty = 0;
					if ($omnify_variation_id > 0) {
						$omnify_var = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
							"SELECT stock_qty FROM {$wpdb->prefix}omnify_product_variations WHERE id = %d",
							$omnify_variation_id
						), ARRAY_A);
						$omnify_new_qty = (int) ($omnify_var['stock_qty'] ?? 0);
					} else {
						$omnify_prod = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
							"SELECT stock_qty FROM {$wpdb->prefix}omnify_products WHERE id = %d",
							$omnify_product_id
						), ARRAY_A);
						$omnify_new_qty = (int) ($omnify_prod['stock_qty'] ?? 0);
					}

					$this->omnify_products->log_stock_change(
						$omnify_product_id,
						$omnify_variation_id,
						$omnify_quantity,
						$omnify_new_qty,
						// translators: %d: placeholder value.
						sprintf(__('Restored from cancelled Order #%d', 'omnifywp-ecommerce'), $omnify_order_id)
					);
				}
			}
		}

		if ($omnify_order['customer_id']) {
			$this->omnify_customers->record_activity(
				(int) $omnify_order['customer_id'],
				'order_cancelled',
				// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value.
				sprintf(__('Order #%1$d was cancelled (%2$s: %3$s). Stock was restored.', 'omnifywp-ecommerce'), $omnify_order_id, $omnify_source, $omnify_reason),
				['order_id' => $omnify_order_id]
			);
		}

		$this->omnify_orders->add_note(
			$omnify_order_id,
			// translators: %1$s: placeholder value, %2$s: placeholder value.
			sprintf(__('Order cancelled via %1$s (Reason: %2$s). Stock restored.', 'omnifywp-ecommerce'), $omnify_source, $omnify_reason)
		);

		return true;
	}

	private function is_webhook_event_processed(string $omnify_event_id, string $omnify_gateway): bool {
		if (empty($omnify_event_id)) {
			return false;
		}
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_webhook_events';
		$omnify_exists = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT 1 FROM {$omnify_table} WHERE event_id = %s AND gateway = %s LIMIT 1",
			$omnify_event_id,
			$omnify_gateway
		));
		return ! empty($omnify_exists);
	}

	private function record_webhook_event(string $omnify_event_id, string $omnify_gateway): bool {
		if (empty($omnify_event_id)) {
			return false;
		}
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_webhook_events';
		$omnify_inserted = \Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$omnify_table,
			[
				'event_id'   => $omnify_event_id,
				'gateway'    => $omnify_gateway,
				'created_at' => current_time('mysql', true),
			]
		);
		return $omnify_inserted !== false;
	}

	public function mark_order_paid(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_order_id = absint($omnify_request->get_param('id'));
		$omnify_order    = $this->omnify_orders->find($omnify_order_id);

		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		if ($omnify_order['status'] === 'completed') {
			return new WP_Error('omnify_order_already_paid', __('This order is already marked as paid.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$this->complete_order_payment($omnify_order_id, $omnify_order['payment_method'] ?? 'manual');

		return rest_ensure_response(['success' => true, 'order_id' => $omnify_order_id]);
	}

	public function validate_coupon_endpoint(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		$omnify_code = sanitize_text_field($omnify_data['code'] ?? '');
		$omnify_product_id = absint($omnify_data['product_id'] ?? 0);

		if (empty($omnify_code)) {
			return new WP_Error('omnify_coupon_code_required', __('Coupon code is required.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (empty($this->omnify_settings->all()['enable_coupons'])) {
			return new WP_Error('omnify_coupons_disabled', __('Coupons are not enabled for this checkout.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		if (! empty($omnify_data['items']) && is_array($omnify_data['items'])) {
			$omnify_settings = $this->omnify_settings->all();
			$omnify_items = $this->resolve_checkout_items($omnify_data, $omnify_settings);
			if (is_wp_error($omnify_items)) {
				return $omnify_items;
			}
			$omnify_subtotal = array_reduce($omnify_items, static fn(float $omnify_sum, array $omnify_item): float => $omnify_sum + (float) $omnify_item['line_total'], 0.0);
			$omnify_customer = null;
			$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
			if (is_email($omnify_email)) {
				$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
			} elseif (is_user_logged_in()) {
				$omnify_wp_user = wp_get_current_user();
				$omnify_customer = $this->omnify_customers->find_by_user_id($omnify_wp_user->ID) ?: $this->omnify_customers->find_by_email($omnify_wp_user->user_email);
			}
			$omnify_result = $this->evaluate_coupon_for_items($omnify_code, $omnify_items, $omnify_subtotal, $omnify_customer ? (int) $omnify_customer['id'] : null);
			if (is_wp_error($omnify_result)) {
				return $omnify_result;
			}
			$omnify_coupon = $omnify_result['coupon'];

			return rest_ensure_response([
				'valid'          => true,
				'code'           => $omnify_coupon['code'],
				'discount'       => (float) $omnify_result['discount'],
				'discount_type'  => $omnify_coupon['discount_type'],
				'discount_value' => $omnify_coupon['discount_value'],
				'free_shipping'  => ! empty($omnify_coupon['free_shipping']),
			]);
		}

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_coupon_product_not_found', __('Invalid product.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_variation_id = absint($omnify_data['variation_id'] ?? 0);
		$omnify_price = (float) $omnify_product['price'];
		if ($omnify_variation_id) {
			global $wpdb;
			$omnify_variation = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}omnify_product_variations WHERE id = %d AND product_id = %d",
				$omnify_variation_id,
				$omnify_product_id
			), ARRAY_A);
			if ($omnify_variation) {
				$omnify_price = (null !== $omnify_variation['sale_price'] && $omnify_variation['sale_price'] !== '') ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'];
			}
		} else {
			if (null !== $omnify_product['sale_price'] && $omnify_product['sale_price'] !== '') {
				$omnify_price = (float) $omnify_product['sale_price'];
			}
		}

		$omnify_customer = null;
		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));
		if (is_email($omnify_email)) {
			$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
		} elseif (is_user_logged_in()) {
			$omnify_wp_user = wp_get_current_user();
			$omnify_customer = $this->omnify_customers->find_by_user_id($omnify_wp_user->ID) ?: $this->omnify_customers->find_by_email($omnify_wp_user->user_email);
		}

		$omnify_result = $this->evaluate_coupon($omnify_code, $omnify_product, $omnify_price, $omnify_customer ? (int) $omnify_customer['id'] : null);
		if (is_wp_error($omnify_result)) {
			return $omnify_result;
		}
		$omnify_coupon = $omnify_result['coupon'];

		return rest_ensure_response([
			'valid'          => true,
			'code'           => $omnify_coupon['code'],
			'discount'       => (float) $omnify_result['discount'],
			'discount_type'  => $omnify_coupon['discount_type'],
			'discount_value' => $omnify_coupon['discount_value'],
			'free_shipping'  => ! empty($omnify_coupon['free_shipping']),
		]);
	}

	private function evaluate_coupon(string $omnify_code, array $omnify_product, float $omnify_subtotal, ?int $omnify_customer_id = null): array|WP_Error {
		$omnify_coupon = $this->omnify_coupons->find_by_code($omnify_code);
		if (! $omnify_coupon) {
			return new WP_Error('omnify_coupon_invalid', __('This discount code is invalid.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		if (empty($omnify_coupon['is_active'])) {
			return new WP_Error('omnify_coupon_inactive', __('This discount code is not active.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if ($omnify_coupon['is_expired']) {
			return new WP_Error('omnify_coupon_expired', __('This discount code has expired.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (null !== $omnify_coupon['usage_limit'] && $omnify_coupon['usage_count'] >= $omnify_coupon['usage_limit']) {
			return new WP_Error('omnify_coupon_limit_reached', __('This discount code has reached its usage limit.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (null !== $omnify_coupon['min_order_amount'] && $omnify_subtotal < (float) $omnify_coupon['min_order_amount']) {
			// translators: %s: placeholder value.
			return new WP_Error('omnify_coupon_minimum_not_met', sprintf(__('This coupon requires a minimum order amount of %s.', 'omnifywp-ecommerce'), number_format((float) $omnify_coupon['min_order_amount'], 2)), ['status' => 400]);
		}
		if ($omnify_customer_id && null !== $omnify_coupon['usage_limit_per_customer'] && $this->customer_coupon_usage((string) $omnify_coupon['code'], $omnify_customer_id) >= (int) $omnify_coupon['usage_limit_per_customer']) {
			return new WP_Error('omnify_coupon_customer_limit_reached', __('You have already used this discount code the maximum number of times.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (! empty($omnify_coupon['first_order_only']) && $omnify_customer_id && $this->customer_order_count($omnify_customer_id) > 0) {
			return new WP_Error('omnify_coupon_first_order_only', __('This discount code is only available for first orders.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (! $this->coupon_product_matches($omnify_coupon, $omnify_product)) {
			return new WP_Error('omnify_coupon_product_restricted', __('This discount code cannot be used for this product.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_discount = 'percent' === $omnify_coupon['discount_type']
			? $omnify_subtotal * ((float) $omnify_coupon['discount_value'] / 100)
			: (float) $omnify_coupon['discount_value'];
		if (null !== $omnify_coupon['max_discount_amount']) {
			$omnify_discount = min($omnify_discount, (float) $omnify_coupon['max_discount_amount']);
		}

		return [
			'coupon' => $omnify_coupon,
			'discount' => min($omnify_subtotal, max(0.0, $omnify_discount)),
			'free_shipping' => ! empty($omnify_coupon['free_shipping']),
		];
	}

	private function evaluate_coupon_for_items(string $omnify_code, array $omnify_items, float $omnify_subtotal, ?int $omnify_customer_id = null): array|WP_Error {
		$omnify_coupon = $this->omnify_coupons->find_by_code($omnify_code);
		if (! $omnify_coupon) {
			return new WP_Error('omnify_coupon_invalid', __('This discount code is invalid.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		if (empty($omnify_coupon['is_active'])) {
			return new WP_Error('omnify_coupon_inactive', __('This discount code is not active.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if ($omnify_coupon['is_expired']) {
			return new WP_Error('omnify_coupon_expired', __('This discount code has expired.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (null !== $omnify_coupon['usage_limit'] && $omnify_coupon['usage_count'] >= $omnify_coupon['usage_limit']) {
			return new WP_Error('omnify_coupon_limit_reached', __('This discount code has reached its usage limit.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (null !== $omnify_coupon['min_order_amount'] && $omnify_subtotal < (float) $omnify_coupon['min_order_amount']) {
			// translators: %s: placeholder value.
			return new WP_Error('omnify_coupon_minimum_not_met', sprintf(__('This coupon requires a minimum order amount of %s.', 'omnifywp-ecommerce'), number_format((float) $omnify_coupon['min_order_amount'], 2)), ['status' => 400]);
		}
		if ($omnify_customer_id && null !== $omnify_coupon['usage_limit_per_customer'] && $this->customer_coupon_usage((string) $omnify_coupon['code'], $omnify_customer_id) >= (int) $omnify_coupon['usage_limit_per_customer']) {
			return new WP_Error('omnify_coupon_customer_limit_reached', __('You have already used this discount code the maximum number of times.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (! empty($omnify_coupon['first_order_only']) && $omnify_customer_id && $this->customer_order_count($omnify_customer_id) > 0) {
			return new WP_Error('omnify_coupon_first_order_only', __('This discount code is only available for first orders.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_eligible_subtotal = 0.0;
		foreach ($omnify_items as $omnify_item) {
			if ($this->coupon_product_matches($omnify_coupon, $omnify_item['product'])) {
				$omnify_eligible_subtotal += (float) $omnify_item['line_total'];
			}
		}
		if ($omnify_eligible_subtotal <= 0) {
			return new WP_Error('omnify_coupon_product_restricted', __('This discount code cannot be used for the products in your cart.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_discount = 'percent' === $omnify_coupon['discount_type']
			? $omnify_eligible_subtotal * ((float) $omnify_coupon['discount_value'] / 100)
			: (float) $omnify_coupon['discount_value'];
		if (null !== $omnify_coupon['max_discount_amount']) {
			$omnify_discount = min($omnify_discount, (float) $omnify_coupon['max_discount_amount']);
		}

		return [
			'coupon' => $omnify_coupon,
			'discount' => min($omnify_eligible_subtotal, max(0.0, $omnify_discount)),
			'free_shipping' => ! empty($omnify_coupon['free_shipping']),
		];
	}

	private function coupon_product_matches(array $omnify_coupon, array $omnify_product): bool {
		$omnify_product_id = (string) ($omnify_product['id'] ?? '');
		$omnify_categories = array_map(static fn($omnify_value): string => strtolower(trim((string) $omnify_value)), (array) ($omnify_product['categories'] ?? []));
		$omnify_csv = static fn(string $omnify_value): array => array_filter(array_map(static fn($omnify_item): string => strtolower(trim($omnify_item)), explode(',', $omnify_value)));

		if (in_array(strtolower($omnify_product_id), $omnify_csv((string) $omnify_coupon['excluded_product_ids']), true)) {
			return false;
		}
		if (array_intersect($omnify_categories, $omnify_csv((string) $omnify_coupon['excluded_categories']))) {
			return false;
		}
		$omnify_included_products = $omnify_csv((string) $omnify_coupon['included_product_ids']);
		if (! empty($omnify_included_products) && ! in_array(strtolower($omnify_product_id), $omnify_included_products, true)) {
			return false;
		}
		$omnify_included_categories = $omnify_csv((string) $omnify_coupon['included_categories']);
		if (! empty($omnify_included_categories) && ! array_intersect($omnify_categories, $omnify_included_categories)) {
			return false;
		}

		return true;
	}

	private function customer_coupon_usage(string $omnify_coupon_code, int $omnify_customer_id): int {
		global $wpdb;
		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}omnify_orders WHERE customer_id = %d AND UPPER(coupon_code) = UPPER(%s) AND status NOT IN ('cancelled', 'failed')",
			$omnify_customer_id,
			$omnify_coupon_code
		));
	}

	private function customer_order_count(int $omnify_customer_id): int {
		global $wpdb;
		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}omnify_orders WHERE customer_id = %d AND status NOT IN ('cancelled', 'failed')",
			$omnify_customer_id
		));
	}

	public function list_orders(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_params = $omnify_request->get_params();
		$omnify_params = apply_filters('omnify_rest_orders_query_args', $omnify_params, $omnify_request);

		$omnify_results = $this->omnify_orders->all($omnify_params);
		$omnify_results = apply_filters('omnify_rest_orders_response', $omnify_results, $omnify_params, $omnify_request);

		do_action('omnify_rest_orders_listed', count($omnify_results), $omnify_params, $omnify_request);

		return rest_ensure_response($omnify_results);
	}

	public function stats(WP_REST_Request $omnify_request): WP_REST_Response {
		global $wpdb;

		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
		$omnify_products_table = $wpdb->prefix . 'omnify_products';

		$omnify_revenue = (float) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SELECT SUM(total) FROM %i WHERE status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')", $omnify_orders_table));
		$omnify_orders = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')", $omnify_orders_table));
		$omnify_customers = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE status = 'active'", $omnify_customers_table));
		$omnify_products = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE status = 'published'", $omnify_products_table));

		return rest_ensure_response([
			'revenue'   => $omnify_revenue,
			'orders'    => $omnify_orders,
			'customers' => $omnify_customers,
			'products'  => $omnify_products,
		]);
	}

	public function get_product_reviews(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_product_id = absint($omnify_request['product_id']);

		$omnify_reviews   = $this->omnify_reviews->all_for_product($omnify_product_id);
		$omnify_average   = $this->omnify_reviews->get_average_rating($omnify_product_id);
		$omnify_count     = $this->omnify_reviews->get_total_count($omnify_product_id);
		$omnify_breakdown = $this->omnify_reviews->get_rating_breakdown($omnify_product_id);

		global $wpdb;
		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_items_table  = $wpdb->prefix . 'omnify_order_items';
		$omnify_customers_table = $wpdb->prefix . 'omnify_customers';

		foreach ($omnify_reviews as &$omnify_review) {
			$omnify_email = $omnify_review['customer_email'];
			$omnify_has_ordered = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, 
				$wpdb->prepare(
					"SELECT COUNT(*) FROM %i o
					JOIN %i i ON o.id = i.order_id
					WHERE o.customer_id IN (SELECT id FROM %i WHERE email = %s)
					AND i.product_id = %d
					AND o.status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')",
					$omnify_orders_table,
					$omnify_items_table,
					$omnify_customers_table,
					$omnify_email,
					$omnify_product_id
				)
			);
			$omnify_review['verified_buyer'] = ((int) $omnify_has_ordered) > 0;
			$omnify_review['formatted_date'] = date_i18n(get_option('date_format'), strtotime($omnify_review['created_at']));
		}

		return rest_ensure_response([
			'reviews'        => $omnify_reviews,
			'average_rating' => $omnify_average,
			'total_count'    => $omnify_count,
			'breakdown'      => $omnify_breakdown,
		]);
	}

	public function submit_product_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_params     = $omnify_request->get_json_params() ?: $omnify_request->get_params();

		// Enforce nonce verification
		$omnify_nonce = sanitize_text_field(wp_unslash((string) ($omnify_params['omnify_review_nonce'] ?? $omnify_request->get_header('X-WP-Nonce'))));
		if (! wp_verify_nonce($omnify_nonce, 'omnify_submit_review_' . $omnify_product_id) && ! wp_verify_nonce($omnify_nonce, 'wp_rest')) {
			return new WP_Error('omnify_bad_nonce', __('Invalid nonce.', 'omnifywp-ecommerce'), ['status' => 403]);
		}

		$omnify_rating         = absint($omnify_params['rating'] ?? 0);
		$omnify_customer_name  = sanitize_text_field(trim((string) ($omnify_params['customer_name'] ?? '')));
		$omnify_customer_email = sanitize_email(trim((string) ($omnify_params['customer_email'] ?? '')));
		$omnify_review_title   = sanitize_text_field(trim((string) ($omnify_params['review_title'] ?? '')));
		$omnify_review_content = sanitize_textarea_field(trim((string) ($omnify_params['review_content'] ?? '')));

		if ($omnify_rating < 1 || $omnify_rating > 5) {
			return new WP_Error('omnify_invalid_rating', __('Please select a rating between 1 and 5 stars.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (empty($omnify_customer_name)) {
			return new WP_Error('omnify_name_required', __('Please enter your name.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (empty($omnify_customer_email) || ! is_email($omnify_customer_email)) {
			return new WP_Error('omnify_email_required', __('Please enter a valid email address.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (empty($omnify_review_title)) {
			return new WP_Error('omnify_title_required', __('Please enter a review title.', 'omnifywp-ecommerce'), ['status' => 400]);
		}
		if (empty($omnify_review_content)) {
			return new WP_Error('omnify_content_required', __('Please enter your review text.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_review_id = $this->omnify_reviews->create([
			'product_id'     => $omnify_product_id,
			'customer_name'  => $omnify_customer_name,
			'customer_email' => $omnify_customer_email,
			'rating'         => $omnify_rating,
			'review_title'   => $omnify_review_title,
			'review_content' => $omnify_review_content,
			'status'         => 'pending', // Default status to pending for moderation
		]);

		if (! $omnify_review_id) {
			return new WP_Error('omnify_db_error', __('Could not save your review. Please try again.', 'omnifywp-ecommerce'), ['status' => 500]);
		}

		return rest_ensure_response([
			'success'   => true,
			'review_id' => $omnify_review_id,
			'message'   => __('Thank you! Your review has been submitted and posted.', 'omnifywp-ecommerce'),
		]);
	}

	/**
	 * Centralized validation helper for checkouts.
	 *
	 * @param array $data Input payload data.
	 * @param array $settings Plugin settings.
	 * @param array $items Unified order items.
	 * @param bool $has_physical Whether the order has physical items.
	 * @param string $route_payment_method Payment context flow.
	 * @return WP_Error|null
	 */
	private function validate_checkout_payload(array &$omnify_data, array $omnify_settings, array $omnify_items, bool $omnify_has_physical, string $omnify_route_payment_method): ?WP_Error {
		do_action('omnify_before_checkout_validation', $omnify_data, $omnify_settings, $omnify_items);

		$omnify_email = sanitize_email((string) ($omnify_data['email'] ?? ''));

		// 1. Email check
		if (! is_email($omnify_email)) {
			return new WP_Error('omnify_checkout_email_required', __('A valid email is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		// 2. Phone check
		if (! empty($omnify_settings['checkout_require_phone']) && '' === trim((string) ($omnify_data['phone'] ?? ''))) {
			return new WP_Error('omnify_checkout_phone_required', __('Phone number is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		// 3. Terms check
		if (! empty($omnify_settings['require_terms']) && empty($omnify_data['agree_to_terms'])) {
			return new WP_Error('omnify_checkout_terms_required', __('You must agree to the Terms & Conditions to complete your purchase.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		// 4. Shipping fields check
		if ($omnify_has_physical) {
			$omnify_required_shipping = [
				'shipping_first_name' => __('Shipping first name is required.', 'omnifywp-ecommerce'),
				'shipping_last_name'  => __('Shipping last name is required.', 'omnifywp-ecommerce'),
				'shipping_address_1'  => __('Shipping address line 1 is required.', 'omnifywp-ecommerce'),
				'shipping_city'       => __('Shipping city is required.', 'omnifywp-ecommerce'),
				'shipping_state'      => __('Shipping state is required.', 'omnifywp-ecommerce'),
				'shipping_postcode'   => __('Shipping postcode is required.', 'omnifywp-ecommerce'),
				'shipping_country'    => __('Shipping country is required.', 'omnifywp-ecommerce'),
			];
			foreach ($omnify_required_shipping as $omnify_field => $omnify_error_msg) {
				if (empty($omnify_data[$omnify_field])) {
					return new WP_Error('omnify_checkout_shipping_field_required', $omnify_error_msg, ['status' => 422]);
				}
			}

			// Validate shipping country & state
			$omnify_country = sanitize_text_field((string) $omnify_data['shipping_country']);
			$omnify_countries = \Omnify\eCommerce\Support\Omnify_Locations::countries();
			if (! isset($omnify_countries[$omnify_country])) {
				return new WP_Error('omnify_checkout_shipping_country_invalid', __('Please select a valid shipping country.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_state = sanitize_text_field((string) $omnify_data['shipping_state']);
			$omnify_states = \Omnify\eCommerce\Support\Omnify_Locations::states();
			if (isset($omnify_states[$omnify_country]) && ! isset($omnify_states[$omnify_country][$omnify_state])) {
				return new WP_Error('omnify_checkout_shipping_state_invalid', __('Please select a valid shipping state/province.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		}

		// 5. Billing fields check
		$omnify_billing_same = isset($omnify_data['billing_same_as_shipping']) ? (int) $omnify_data['billing_same_as_shipping'] : 1;
		if ($omnify_has_physical && $omnify_billing_same) {
			// Copy shipping fields if they are the same
			$omnify_data['billing_first_name'] = $omnify_data['shipping_first_name'] ?? '';
			$omnify_data['billing_last_name']  = $omnify_data['shipping_last_name'] ?? '';
			$omnify_data['billing_address_1']  = $omnify_data['shipping_address_1'] ?? '';
			$omnify_data['billing_address_2']  = $omnify_data['shipping_address_2'] ?? '';
			$omnify_data['billing_city']       = $omnify_data['shipping_city'] ?? '';
			$omnify_data['billing_state']      = $omnify_data['shipping_state'] ?? '';
			$omnify_data['billing_postcode']   = $omnify_data['shipping_postcode'] ?? '';
			$omnify_data['billing_country']    = $omnify_data['shipping_country'] ?? '';
			$omnify_data['billing_phone']      = $omnify_data['shipping_phone'] ?? '';
		} else {
			$omnify_required_billing = [
				'billing_first_name' => __('Billing first name is required.', 'omnifywp-ecommerce'),
				'billing_last_name'  => __('Billing last name is required.', 'omnifywp-ecommerce'),
				'billing_address_1'  => __('Billing address line 1 is required.', 'omnifywp-ecommerce'),
				'billing_city'       => __('Billing city is required.', 'omnifywp-ecommerce'),
				'billing_state'      => __('Billing state is required.', 'omnifywp-ecommerce'),
				'billing_postcode'   => __('Billing postcode is required.', 'omnifywp-ecommerce'),
				'billing_country'    => __('Billing country is required.', 'omnifywp-ecommerce'),
			];
			foreach ($omnify_required_billing as $omnify_field => $omnify_error_msg) {
				if (empty($omnify_data[$omnify_field])) {
					return new WP_Error('omnify_checkout_billing_field_required', $omnify_error_msg, ['status' => 422]);
				}
			}

			// Validate billing country & state
			$omnify_country = sanitize_text_field((string) $omnify_data['billing_country']);
			$omnify_countries = \Omnify\eCommerce\Support\Omnify_Locations::countries();
			if (! isset($omnify_countries[$omnify_country])) {
				return new WP_Error('omnify_checkout_billing_country_invalid', __('Please select a valid billing country.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_state = sanitize_text_field((string) $omnify_data['billing_state']);
			$omnify_states = \Omnify\eCommerce\Support\Omnify_Locations::states();
			if (isset($omnify_states[$omnify_country]) && ! isset($omnify_states[$omnify_country][$omnify_state])) {
				return new WP_Error('omnify_checkout_billing_state_invalid', __('Please select a valid billing state/province.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		}

		// 5.5 Selling locations check
		$omnify_sell_locations = $omnify_settings['selling_locations'] ?? 'all';
		if ('specific' === $omnify_sell_locations) {
			$omnify_allowed_countries = (array) ($omnify_settings['selling_countries'] ?? []);
			if ($omnify_has_physical) {
				$omnify_ship_country = strtoupper(sanitize_text_field((string) ($omnify_data['shipping_country'] ?? '')));
				if (! in_array($omnify_ship_country, $omnify_allowed_countries, true)) {
					return new WP_Error('omnify_checkout_shipping_country_not_allowed', __('We do not sell or ship to your selected shipping country.', 'omnifywp-ecommerce'), ['status' => 422]);
				}
			}
			$omnify_bill_country = strtoupper(sanitize_text_field((string) ($omnify_data['billing_country'] ?? '')));
			if ($omnify_bill_country && ! in_array($omnify_bill_country, $omnify_allowed_countries, true)) {
				return new WP_Error('omnify_checkout_billing_country_not_allowed', __('We do not sell to your selected billing country.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		}

		// 6. Max quantity check & Stock check
		foreach ($omnify_items as $omnify_item) {
			$omnify_product = $omnify_item['product'];
			$omnify_variation = $omnify_item['variation'] ?? null;
			$omnify_quantity = (int) $omnify_item['quantity'];

			if ($omnify_quantity <= 0) {
				return new WP_Error('omnify_checkout_quantity_invalid', __('Invalid product quantity.', 'omnifywp-ecommerce'), ['status' => 400]);
			}

			// Validate stock
			if ($omnify_variation) {
				$omnify_variation_allows_oversell = ! empty($omnify_variation['allow_backorders']) || ! empty($omnify_variation['preorder_enabled']);
				if ('outofstock' === ($omnify_variation['stock_status'] ?? '') && ! $omnify_variation_allows_oversell) {
					// translators: %s: placeholder value.
					return new WP_Error('omnify_checkout_out_of_stock', sprintf(__('The variation for %s is out of stock.', 'omnifywp-ecommerce'), (string) $omnify_product['name']), ['status' => 400]);
				}
				if ($omnify_variation['manage_stock'] && (int) $omnify_variation['stock_qty'] < $omnify_quantity && ! $omnify_variation_allows_oversell) {
					// translators: %s: placeholder value.
					return new WP_Error('omnify_checkout_out_of_stock', sprintf(__('The variation for %s is out of stock.', 'omnifywp-ecommerce'), (string) $omnify_product['name']), ['status' => 400]);
				}
				$omnify_preorder_limit = (int) ($omnify_variation['preorder_limit'] ?? 0);
				if (! empty($omnify_variation['preorder_enabled']) && $omnify_preorder_limit > 0 && $omnify_quantity > $omnify_preorder_limit) {
					// translators: %1$s: placeholder value, %2$d: placeholder value.
					return new WP_Error('omnify_checkout_preorder_limit_exceeded', sprintf(__('Preorder quantity for %1$s is limited to %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_preorder_limit), ['status' => 400]);
				}
			} else {
				$omnify_product_allows_oversell = ! empty($omnify_product['allow_backorders']) || ! empty($omnify_product['preorder_enabled']);
				if ('outofstock' === ($omnify_product['stock_status'] ?? '') && ! $omnify_product_allows_oversell) {
					// translators: %s: placeholder value.
					return new WP_Error('omnify_checkout_out_of_stock', sprintf(__('The product %s is out of stock.', 'omnifywp-ecommerce'), (string) $omnify_product['name']), ['status' => 400]);
				}
				if (isset($omnify_product['manage_stock']) && $omnify_product['manage_stock'] && (int) $omnify_product['stock_qty'] < $omnify_quantity && ! $omnify_product_allows_oversell) {
					// translators: %s: placeholder value.
					return new WP_Error('omnify_checkout_out_of_stock', sprintf(__('The product %s is out of stock.', 'omnifywp-ecommerce'), (string) $omnify_product['name']), ['status' => 400]);
				}
				$omnify_preorder_limit = (int) ($omnify_product['preorder_limit'] ?? 0);
				if (! empty($omnify_product['preorder_enabled']) && $omnify_preorder_limit > 0 && $omnify_quantity > $omnify_preorder_limit) {
					// translators: %1$s: placeholder value, %2$d: placeholder value.
					return new WP_Error('omnify_checkout_preorder_limit_exceeded', sprintf(__('Preorder quantity for %1$s is limited to %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_preorder_limit), ['status' => 400]);
				}
			}

			// Validate max purchase qty
			$omnify_max_qty = isset($omnify_product['max_purchase_qty']) ? (int) $omnify_product['max_purchase_qty'] : 0;
			if ($omnify_max_qty > 0 && $omnify_quantity > $omnify_max_qty) {
				// translators: %1$s: placeholder value, %2$d: placeholder value.
				return new WP_Error('omnify_checkout_max_qty_exceeded', sprintf(__('Maximum purchase quantity for %1$s is %2$d.', 'omnifywp-ecommerce'), (string) $omnify_product['name'], $omnify_max_qty), ['status' => 400]);
			}
		}

		// 7. Payment method availability
		$omnify_payment_method_id = sanitize_text_field((string) ($omnify_data['payment_method'] ?? ''));
		if ('' === $omnify_payment_method_id) {
			return new WP_Error('omnify_checkout_payment_method_required', __('Please select a payment method.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		if ('stripe_checkout' === $omnify_route_payment_method || 'stripe_payment_intent' === $omnify_route_payment_method) {
			if ('card' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Stripe Checkout.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
			$omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']) && ! empty($omnify_settings['stripe_checkout_enabled']);
			if (! $omnify_stripe_enabled) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Card payments are not configured.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('paypal_checkout' === $omnify_route_payment_method) {
			if ('paypal' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for PayPal Checkout.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('razorpay' === $omnify_route_payment_method) {
			if ('razorpay' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Razorpay.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('alipay' === $omnify_route_payment_method) {
			if ('alipay' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Alipay.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('wechat' === $omnify_route_payment_method) {
			if ('wechat' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for WeChat Pay.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('sslcommerz' === $omnify_route_payment_method) {
			if ('sslcommerz' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for SSLCommerz.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('paystack' === $omnify_route_payment_method) {
			if ('paystack' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Paystack.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('tap' === $omnify_route_payment_method) {
			if ('tap' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Tap Payments.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('mollie' === $omnify_route_payment_method) {
			if ('mollie' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Mollie.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('khalti' === $omnify_route_payment_method) {
			if ('khalti' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for Khalti.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('esewa' === $omnify_route_payment_method) {
			if ('esewa' !== $omnify_payment_method_id) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method for eSewa.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} elseif ('manual' === $omnify_route_payment_method) {
			$omnify_is_manual = false;
			foreach (($omnify_settings['payment_methods'] ?? []) as $omnify_method) {
				if (($omnify_method['id'] ?? '') === $omnify_payment_method_id && ! empty($omnify_method['enabled'])) {
					$omnify_is_manual = true;
					break;
				}
			}
			if (! $omnify_is_manual) {
				return new WP_Error('omnify_checkout_payment_method_invalid', __('Please select a valid payment method.', 'omnifywp-ecommerce'), ['status' => 422]);
			}
		} else {
			return new WP_Error('omnify_checkout_payment_method_invalid', __('Invalid payment method configuration.', 'omnifywp-ecommerce'), ['status' => 422]);
		}

		$omnify_after_val_error = apply_filters('omnify_after_checkout_validation', null, $omnify_data, $omnify_settings, $omnify_items);
		if (is_wp_error($omnify_after_val_error)) {
			return $omnify_after_val_error;
		}

		return null;
	}

	public function get_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_order = $this->omnify_orders->find(absint($omnify_request['id']));
		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_order);
	}

	public function update_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		$omnify_order = $this->omnify_orders->find($omnify_id);
		if (! $omnify_order) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$omnify_data = (array) $omnify_request->get_json_params();

		if (isset($omnify_data['status']) && $omnify_data['status'] !== $omnify_order['status']) {
			$this->omnify_orders->update_status($omnify_id, sanitize_key($omnify_data['status']));
			unset($omnify_data['status']);
		}

		if (! empty($omnify_data)) {
			$this->omnify_orders->update($omnify_id, $omnify_data);
		}

		return rest_ensure_response($this->omnify_orders->find($omnify_id));
	}

	public function delete_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_orders->find($omnify_id)) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_orders->delete($omnify_id);
		do_action('omnify_rest_order_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_orders->find($omnify_id)) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_orders->trash($omnify_id);
		do_action('omnify_rest_order_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_order(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_orders->find($omnify_id)) {
			return new WP_Error('omnify_order_not_found', __('Order not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_orders->restore($omnify_id);
		do_action('omnify_rest_order_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'order' => $this->omnify_orders->find($omnify_id)]);
	}

	public function list_coupons(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_coupons->all($omnify_request->get_params()));
	}

	public function create_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_data = (array) $omnify_request->get_json_params();
		if (empty($omnify_data['code'])) {
			return new WP_Error('omnify_coupon_code_required', __('Coupon code is required.', 'omnifywp-ecommerce'), ['status' => 422]);
		}
		$omnify_id = $this->omnify_coupons->create($omnify_data);
		return rest_ensure_response($this->omnify_coupons->find($omnify_id));
	}

	public function get_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_coupon = $this->omnify_coupons->find(absint($omnify_request['id']));
		if (! $omnify_coupon) {
			return new WP_Error('omnify_coupon_not_found', __('Coupon not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_coupon);
	}

	public function update_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_coupons->find($omnify_id)) {
			return new WP_Error('omnify_coupon_not_found', __('Coupon not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$this->omnify_coupons->update($omnify_id, (array) $omnify_request->get_json_params());
		return rest_ensure_response($this->omnify_coupons->find($omnify_id));
	}

	public function delete_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_coupons->find($omnify_id)) {
			return new WP_Error('omnify_coupon_not_found', __('Coupon not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_coupons->delete($omnify_id);
		do_action('omnify_rest_coupon_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_coupons->find($omnify_id)) {
			return new WP_Error('omnify_coupon_not_found', __('Coupon not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_coupons->trash($omnify_id);
		do_action('omnify_rest_coupon_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_coupon(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_coupons->find($omnify_id)) {
			return new WP_Error('omnify_coupon_not_found', __('Coupon not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_coupons->restore($omnify_id);
		do_action('omnify_rest_coupon_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'coupon' => $this->omnify_coupons->find($omnify_id)]);
	}

	public function list_reviews(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_reviews->all($omnify_request->get_params()));
	}

	public function get_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_review = $this->omnify_reviews->find(absint($omnify_request['id']));
		if (! $omnify_review) {
			return new WP_Error('omnify_review_not_found', __('Review not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_review);
	}

	public function update_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_reviews->find($omnify_id)) {
			return new WP_Error('omnify_review_not_found', __('Review not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$this->omnify_reviews->update($omnify_id, (array) $omnify_request->get_json_params());
		return rest_ensure_response($this->omnify_reviews->find($omnify_id));
	}

	public function delete_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_reviews->find($omnify_id)) {
			return new WP_Error('omnify_review_not_found', __('Review not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_reviews->delete($omnify_id);
		do_action('omnify_rest_review_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_reviews->find($omnify_id)) {
			return new WP_Error('omnify_review_not_found', __('Review not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_reviews->trash($omnify_id);
		do_action('omnify_rest_review_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_review(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_reviews->find($omnify_id)) {
			return new WP_Error('omnify_review_not_found', __('Review not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_reviews->restore($omnify_id);
		do_action('omnify_rest_review_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'review' => $this->omnify_reviews->find($omnify_id)]);
	}

	public function list_product_categories(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_product['categories'] ?? []);
	}

	public function link_product_category(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$omnify_params = $omnify_request->get_json_params() ?: $omnify_request->get_params();
		$omnify_category = sanitize_text_field(trim((string) ($omnify_params['category'] ?? '')));
		if (empty($omnify_category)) {
			return new WP_Error('omnify_category_required', __('Category name is required.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_current_categories = $omnify_product['categories'] ?? [];
		if (! in_array($omnify_category, $omnify_current_categories, true)) {
			$omnify_current_categories[] = $omnify_category;
			$this->omnify_products->update($omnify_product_id, ['categories' => $omnify_current_categories]);
		}

		$omnify_updated_product = $this->omnify_products->find($omnify_product_id);
		return rest_ensure_response($omnify_updated_product['categories'] ?? []);
	}

	public function unlink_product_category(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$omnify_params = $omnify_request->get_json_params() ?: $omnify_request->get_params();
		$omnify_category = sanitize_text_field(trim((string) ($omnify_params['category'] ?? '')));
		if (empty($omnify_category)) {
			return new WP_Error('omnify_category_required', __('Category name is required.', 'omnifywp-ecommerce'), ['status' => 400]);
		}

		$omnify_current_categories = $omnify_product['categories'] ?? [];
		$omnify_new_categories = array_values(array_diff($omnify_current_categories, [$omnify_category]));
		if (count($omnify_current_categories) !== count($omnify_new_categories)) {
			$this->omnify_products->update($omnify_product_id, ['categories' => $omnify_new_categories]);
		}

		$omnify_updated_product = $this->omnify_products->find($omnify_product_id);
		return rest_ensure_response($omnify_updated_product['categories'] ?? []);
	}

	public function list_product_variations(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($this->omnify_products->get_variations($omnify_product_id));
	}

	public function save_product_variations(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_product_id = absint($omnify_request['product_id']);
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product) {
			return new WP_Error('omnify_product_not_found', __('Product not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		$omnify_variations = (array) $omnify_request->get_json_params();
		$this->omnify_products->save_variations($omnify_product_id, $omnify_variations);

		return rest_ensure_response($this->omnify_products->get_variations($omnify_product_id));
	}

	public function list_activity_logs(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_activities->all($omnify_request->get_params()));
	}

	public function clear_activity_logs(WP_REST_Request $omnify_request): WP_REST_Response {
		$this->omnify_activities->clear_all();

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'clear_activity_log',
			'activity_log',
			null,
			__('Cleared the administrative activity logs via REST API.', 'omnifywp-ecommerce')
		);

		return rest_ensure_response(['success' => true]);
	}

	public function list_downloads(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_downloads->all($omnify_request->get_params()));
	}

	public function revoke_download(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		$omnify_download = $this->omnify_downloads->find($omnify_id);
		if (! $omnify_download) {
			return new WP_Error('omnify_download_log_not_found', __('Download log not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_customer_id = isset($omnify_download['customer_id']) ? (int) $omnify_download['customer_id'] : 0;
		$omnify_product_id = isset($omnify_download['product_id']) ? (int) $omnify_download['product_id'] : 0;

		if ($omnify_customer_id > 0 && $omnify_product_id > 0) {
			$this->omnify_access->revoke($omnify_customer_id, $omnify_product_id);
		}

		$this->omnify_downloads->delete($omnify_id);

		return rest_ensure_response(['success' => true]);
	}

	public function list_abandoned_carts(WP_REST_Request $omnify_request): WP_REST_Response {
		$omnify_params = $omnify_request->get_params();
		$omnify_params = apply_filters('omnify_rest_abandoned_carts_query_args', $omnify_params, $omnify_request);

		$omnify_results = $this->omnify_abandoned_carts->all($omnify_params);
		$omnify_results = apply_filters('omnify_rest_abandoned_carts_response', $omnify_results, $omnify_params, $omnify_request);

		do_action('omnify_rest_abandoned_carts_listed', count($omnify_results), $omnify_params, $omnify_request);

		return rest_ensure_response($omnify_results);
	}

	public function get_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_cart = $this->omnify_abandoned_carts->find(absint($omnify_request['id']));
		if (! $omnify_cart) {
			return new WP_Error('omnify_abandoned_cart_not_found', __('Abandoned cart not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_cart);
	}

	public function delete_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_abandoned_carts->find($omnify_id)) {
			return new WP_Error('omnify_abandoned_cart_not_found', __('Abandoned cart not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_abandoned_carts->delete($omnify_id);
		do_action('omnify_rest_abandoned_cart_permanently_deleted', $omnify_id, $omnify_request);
		return rest_ensure_response(['deleted' => $omnify_result, 'permanent' => true]);
	}

	public function trash_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_abandoned_carts->find($omnify_id)) {
			return new WP_Error('omnify_abandoned_cart_not_found', __('Abandoned cart not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_abandoned_carts->trash($omnify_id);
		do_action('omnify_rest_abandoned_cart_trashed', $omnify_id, $omnify_request);

		return rest_ensure_response(['trashed' => $omnify_result]);
	}

	public function restore_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		if (! $this->omnify_abandoned_carts->find($omnify_id)) {
			return new WP_Error('omnify_abandoned_cart_not_found', __('Abandoned cart not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_result = $this->omnify_abandoned_carts->restore($omnify_id);
		do_action('omnify_rest_abandoned_cart_restored', $omnify_id, $omnify_request);

		return rest_ensure_response(['restored' => $omnify_result, 'cart' => $this->omnify_abandoned_carts->find($omnify_id)]);
	}

	public function manual_recover_abandoned_cart(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_id = absint($omnify_request['id']);
		$omnify_cart = $this->omnify_abandoned_carts->find($omnify_id);
		if (! $omnify_cart) {
			return new WP_Error('omnify_abandoned_cart_not_found', __('Abandoned cart not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		if ($this->omnify_emails->send_abandoned_cart_recovery($omnify_cart)) {
			$this->omnify_abandoned_carts->mark_sent($omnify_id);
			return rest_ensure_response(['success' => true, 'message' => __('Recovery email sent successfully.', 'omnifywp-ecommerce')]);
		}

		return new WP_Error('omnify_recovery_email_failed', __('Failed to send recovery email.', 'omnifywp-ecommerce'), ['status' => 500]);
	}

	public function list_all_wishlists(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_wishlists->all($omnify_request->get_params()));
	}

	public function get_customer_wishlist(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_customer_id = absint($omnify_request['id']);
		$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
		if (! $omnify_customer) {
			return new WP_Error('omnify_customer_not_found', __('Customer not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_user_id = isset($omnify_customer['user_id']) ? (int) $omnify_customer['user_id'] : 0;
		if ($omnify_user_id <= 0) {
			return rest_ensure_response([]);
		}

		return rest_ensure_response($this->omnify_wishlists->all_for_user($omnify_user_id));
	}

	public function list_all_product_files(WP_REST_Request $omnify_request): WP_REST_Response {
		return rest_ensure_response($this->omnify_product_files->all($omnify_request->get_params()));
	}

	public function get_product_file(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		$omnify_file = $this->omnify_product_files->find(absint($omnify_request['id']));
		if (! $omnify_file) {
			return new WP_Error('omnify_file_not_found', __('Product file not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}
		return rest_ensure_response($omnify_file);
	}

	public function list_all_access(WP_REST_Request $omnify_request): WP_REST_Response {
		global $wpdb;
		$omnify_limit  = min(200, max(1, absint($omnify_request['per_page'] ?? 100)));
		$omnify_offset = max(0, absint($omnify_request['offset'] ?? 0));

		$omnify_table = $wpdb->prefix . 'omnify_customer_access';
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare('SELECT * FROM %i ORDER BY updated_at DESC LIMIT %d OFFSET %d', $omnify_table, $omnify_limit, $omnify_offset),
			ARRAY_A
		);

		$omnify_results = [];
		if (is_array($omnify_rows)) {
			foreach ($omnify_rows as $omnify_row) {
				$omnify_row['id'] = (int) $omnify_row['id'];
				$omnify_row['customer_id'] = (int) $omnify_row['customer_id'];
				$omnify_row['product_id'] = (int) $omnify_row['product_id'];
				$omnify_row['granted_by'] = null === $omnify_row['granted_by'] ? null : (int) $omnify_row['granted_by'];
				$omnify_row['is_expired'] = ! empty($omnify_row['expires_at']) && strtotime((string) $omnify_row['expires_at']) < time();
				$omnify_results[] = $omnify_row;
			}
		}

		return rest_ensure_response($omnify_results);
	}

	public function get_access_detail(WP_REST_Request $omnify_request): WP_REST_Response|WP_Error {
		global $wpdb;
		$omnify_id = absint($omnify_request['id']);
		$omnify_table = $wpdb->prefix . 'omnify_customer_access';
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare('SELECT * FROM %i WHERE id = %d', $omnify_table, $omnify_id), ARRAY_A);
		if (! $omnify_row) {
			return new WP_Error('omnify_access_not_found', __('Access record not found.', 'omnifywp-ecommerce'), ['status' => 404]);
		}

		$omnify_row['id'] = (int) $omnify_row['id'];
		$omnify_row['customer_id'] = (int) $omnify_row['customer_id'];
		$omnify_row['product_id'] = (int) $omnify_row['product_id'];
		$omnify_row['granted_by'] = null === $omnify_row['granted_by'] ? null : (int) $omnify_row['granted_by'];
		$omnify_row['is_expired'] = ! empty($omnify_row['expires_at']) && strtotime((string) $omnify_row['expires_at']) < time();

		return rest_ensure_response($omnify_row);
	}
}
