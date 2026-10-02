<?php
/**
 * Admin menu and page handlers.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Settings\Omnify_Settings_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Coupon_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;
use Omnify\eCommerce\Repositories\Omnify_Admin_Activity_Repository;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;
use Omnify\eCommerce\Support\Omnify_Email_Service;
use Omnify\eCommerce\Support\Omnify_Demo_Data_Service;

class Omnify_Admin_Page {
	public function __construct(
		private Omnify_Settings_Repository $omnify_settings,
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_product_files,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Customer_Access_Repository $omnify_customer_access,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Email_Service $omnify_emails,
		private Omnify_Coupon_Repository $omnify_coupons,
		private Omnify_Review_Repository $omnify_reviews_repo,
		private Omnify_Abandoned_Cart_Repository $omnify_abandoned_carts,
		private \Omnify\eCommerce\Support\Omnify_Payment_Gateway_Service $omnify_payment_gateways,
		private Omnify_Admin_Activity_Repository $omnify_activities,
		private Omnify_Demo_Data_Service $omnify_demo_data
	) {
		$this->register_post_actions();
		add_action('current_screen', [$this, 'maybe_clear_external_notices']);

		// API & Connection Management AJAX
		add_action('wp_ajax_omnify_create_api_key', [$this, 'ajax_create_api_key']);
		add_action('wp_ajax_omnify_revoke_api_key', [$this, 'ajax_revoke_api_key']);
		add_action('wp_ajax_omnify_test_connection', [$this, 'ajax_test_connection']);
		add_action('wp_ajax_omnify_test_stripe_connection', [$this, 'ajax_test_stripe_connection']);
		add_action('wp_ajax_omnify_test_paypal_connection', [$this, 'ajax_test_paypal_connection']);

		// Setup wizard & Demo Data AJAX
		add_action('wp_ajax_omnify_save_wizard', [$this, 'ajax_save_wizard']);
		add_action('wp_ajax_omnify_generate_demo_data', [$this, 'ajax_generate_demo_data']);
		add_action('wp_ajax_omnify_remove_demo_data', [$this, 'ajax_remove_demo_data']);
		add_action('admin_notices', [$this, 'admin_notices']);

		// Page States
		add_filter('display_post_states', [$this, 'add_omnify_post_states'], 10, 2);
	}

	private function register_post_actions(): void {
		$omnify_actions = [
			'omnify_clear_activity_log',
			'omnify_approve_order_risk',
			'omnify_save_product',
			'omnify_delete_product',
			'omnify_trash_product',
			'omnify_restore_product',
			'omnify_delete_product_file',
			'omnify_save_customer',
			'omnify_delete_customer',
			'omnify_trash_customer',
			'omnify_restore_customer',
			'omnify_add_customer_note',
			'omnify_delete_customer_note',
			'omnify_grant_access',
			'omnify_revoke_access',
			'omnify_save_settings',
			'omnify_export_products',
			'omnify_export_products_xml',
			'omnify_export_customers',
			'omnify_export_downloads',
			'omnify_export_orders',
			'omnify_export_coupons',
			'omnify_export_tax_rules',
			'omnify_export_delivery_zones',
			'omnify_import_csv',
			'omnify_refund_order',
			'omnify_add_order_note',
			'omnify_delete_order_note',
			'omnify_generate_pages',
			'omnify_resend_receipt',
			'omnify_save_coupon',
			'omnify_delete_coupon',
			'omnify_trash_coupon',
			'omnify_restore_coupon',
			'omnify_mark_order_paid',
			'omnify_fulfill_order',
			'omnify_update_order_status',
			'omnify_delete_order',
			'omnify_trash_order',
			'omnify_restore_order',
			'omnify_order_document',
			'omnify_cancel_order',
			'omnify_return_order',
			'omnify_add_global_category',
			'omnify_delete_global_category',
			'omnify_add_global_tag',
			'omnify_delete_global_tag',
			'omnify_add_global_brand',
			'omnify_delete_global_brand',
			'omnify_add_global_attribute',
			'omnify_delete_global_attribute',
			'omnify_approve_review',
			'omnify_reject_review',
			'omnify_delete_review',
			'omnify_trash_review',
			'omnify_restore_review',
			'omnify_submit_review',
			'omnify_delete_abandoned_cart',
			'omnify_trash_abandoned_cart',
			'omnify_restore_abandoned_cart',
			'omnify_send_abandoned_cart_email',
			'omnify_send_test_email',
			'omnify_resend_email_log',
			'omnify_bulk_orders',
			'omnify_bulk_customers',
			'omnify_bulk_products',
			'omnify_bulk_coupons',
			'omnify_quick_edit_product',
			'omnify_quick_update_order_status',
			'omnify_remove_shop_user',
		];

		foreach ($omnify_actions as $omnify_action) {
			add_action('admin_post_' . $omnify_action, [$this, 'handle_' . str_replace('omnify_', '', $omnify_action)]);
		}

		// Allow non-logged-in users to submit reviews
		add_action('admin_post_nopriv_omnify_submit_review', [$this, 'handle_submit_review']);
		add_action('admin_post_omnify_ask_question', [$this, 'handle_ask_question']);
		add_action('admin_post_nopriv_omnify_ask_question', [$this, 'handle_ask_question']);
		add_action('admin_post_nopriv_omnify_order_document', [$this, 'handle_order_document']);

		add_action('admin_notices', [$this, 'display_notices']);
	}

	private function admin_db_cache_key(string $omnify_context, string $omnify_sql): string {
		return 'admin_' . sanitize_key($omnify_context) . '_' . md5($omnify_sql);
	}

	private function cached_db_get_col(string $omnify_context, string $omnify_sql, int $omnify_ttl = MINUTE_IN_SECONDS): array {
		global $wpdb;

		$omnify_cache_key = $this->admin_db_cache_key($omnify_context, $omnify_sql);
		$omnify_cached = wp_cache_get($omnify_cache_key, 'omnify_admin');
		if (false !== $omnify_cached) {
			return is_array($omnify_cached) ? $omnify_cached : [];
		}

		$omnify_rows = call_user_func([$wpdb, 'get_col'], $omnify_sql);
		$omnify_rows = is_array($omnify_rows) ? $omnify_rows : [];
		wp_cache_set($omnify_cache_key, $omnify_rows, 'omnify_admin', $omnify_ttl);

		return $omnify_rows;
	}

	private function cached_db_get_row(string $omnify_context, string $omnify_sql, int $omnify_ttl = MINUTE_IN_SECONDS): array {
		global $wpdb;

		$omnify_cache_key = $this->admin_db_cache_key($omnify_context, $omnify_sql);
		$omnify_cached = wp_cache_get($omnify_cache_key, 'omnify_admin');
		if (false !== $omnify_cached) {
			return is_array($omnify_cached) ? $omnify_cached : [];
		}

		$omnify_row = call_user_func([$wpdb, 'get_row'], $omnify_sql, ARRAY_A);
		$omnify_row = is_array($omnify_row) ? $omnify_row : [];
		wp_cache_set($omnify_cache_key, $omnify_row, 'omnify_admin', $omnify_ttl);

		return $omnify_row;
	}

	private function cached_db_get_results(string $omnify_context, string $omnify_sql, int $omnify_ttl = MINUTE_IN_SECONDS): array {
		global $wpdb;

		$omnify_cache_key = $this->admin_db_cache_key($omnify_context, $omnify_sql);
		$omnify_cached = wp_cache_get($omnify_cache_key, 'omnify_admin');
		if (false !== $omnify_cached) {
			return is_array($omnify_cached) ? $omnify_cached : [];
		}

		$omnify_rows = call_user_func([$wpdb, 'get_results'], $omnify_sql, ARRAY_A);
		$omnify_rows = is_array($omnify_rows) ? $omnify_rows : [];
		wp_cache_set($omnify_cache_key, $omnify_rows, 'omnify_admin', $omnify_ttl);

		return $omnify_rows;
	}

	private function cached_db_get_var(string $omnify_context, string $omnify_sql, int $omnify_ttl = MINUTE_IN_SECONDS): mixed {
		global $wpdb;

		$omnify_cache_key = $this->admin_db_cache_key($omnify_context, $omnify_sql);
		$omnify_cached = wp_cache_get($omnify_cache_key, 'omnify_admin');
		if (false !== $omnify_cached) {
			return $omnify_cached;
		}

		$omnify_value = call_user_func([$wpdb, 'get_var'], $omnify_sql);
		wp_cache_set($omnify_cache_key, $omnify_value, 'omnify_admin', $omnify_ttl);

		return $omnify_value;
	}

	public function register_menu(): void {
		add_menu_page(
			__('Omnify', 'omnifywp-ecommerce'),
			__('Omnify', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnifywp-ecommerce',
			[$this, 'render_dashboard'],
			'dashicons-cart',
			56
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Dashboard', 'omnifywp-ecommerce'),
			__('Dashboard', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnifywp-ecommerce',
			[$this, 'render_dashboard']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Orders', 'omnifywp-ecommerce'),
			__('Orders', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-orders',
			[$this, 'render_orders']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Products', 'omnifywp-ecommerce'),
			__('Products', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-products',
			[$this, 'render_products']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Customers', 'omnifywp-ecommerce'),
			__('Customers', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-customers',
			[$this, 'render_customers']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Coupons', 'omnifywp-ecommerce'),
			__('Coupons', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-coupons',
			[$this, 'render_coupons']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Analytics', 'omnifywp-ecommerce'),
			__('Analytics', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-analytics',
			[$this, 'render_analytics']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Settings', 'omnifywp-ecommerce'),
			__('Settings', 'omnifywp-ecommerce'),
			'manage_options',
			'omnify-settings',
			[$this, 'render_settings']
		);

		add_submenu_page(
			'omnifywp-ecommerce',
			__('Tools & Seeding', 'omnifywp-ecommerce'),
			__('Tools & Seeding', 'omnifywp-ecommerce'),
			'manage_options',
			'omnify-tools',
			[$this, 'render_tools']
		);

		add_submenu_page(
			null,
			__('Categories', 'omnifywp-ecommerce'),
			__('Categories', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-categories',
			[$this, 'render_global_categories']
		);

		add_submenu_page(
			null,
			__('Tags', 'omnifywp-ecommerce'),
			__('Tags', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-tags',
			[$this, 'render_global_tags']
		);

		add_submenu_page(
			null,
			__('Brands', 'omnifywp-ecommerce'),
			__('Brands', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-brands',
			[$this, 'render_global_brands']
		);

		add_submenu_page(
			null,
			__('Attributes', 'omnifywp-ecommerce'),
			__('Attributes', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-attributes',
			[$this, 'render_global_attributes']
		);

		// Hidden utility/sub-navigation routes (parent set to null)
		add_submenu_page(
			null,
			__('Abandoned Carts', 'omnifywp-ecommerce'),
			__('Abandoned Carts', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-abandoned-carts',
			[$this, 'render_abandoned_carts']
		);

		add_submenu_page(
			null,
			__('Reviews', 'omnifywp-ecommerce'),
			__('Reviews', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-reviews',
			[$this, 'render_reviews']
		);

		add_submenu_page(
			null,
			__('Import/Export', 'omnifywp-ecommerce'),
			__('Import/Export', 'omnifywp-ecommerce'),
			'manage_omnify',
			'omnify-export',
			[$this, 'render_export']
		);

		add_submenu_page(
			null,
			__('API Keys', 'omnifywp-ecommerce'),
			__('API Keys', 'omnifywp-ecommerce'),
			'manage_options',
			'omnify-api',
			[$this, 'render_api']
		);

		add_submenu_page(
			null,
			__('Activity Log', 'omnifywp-ecommerce'),
			__('Activity Log', 'omnifywp-ecommerce'),
			'manage_options',
			'omnify-activity',
			[$this, 'render_activity']
		);

		add_submenu_page(
			null,
			__('Setup Wizard', 'omnifywp-ecommerce'),
			__('Setup Wizard', 'omnifywp-ecommerce'),
			'manage_options',
			'omnify-setup',
			[$this, 'render_setup_wizard']
		);
	}

	public function register_settings(): void {
		// Setup wizard redirect check
		if (get_transient('omnify_setup_redirect')) {
			delete_transient('omnify_setup_redirect');
			if (!get_option('omnify_setup_completed')) {
				wp_safe_redirect(admin_url('admin.php?page=omnify-setup'));
				exit;
			}
		}

		register_setting(
			'omnify_ecommerce',
			$this->omnify_settings->option_name(),
			[
				'type'              => 'array',
				'sanitize_callback' => [$this->omnify_settings, 'sanitize'],
				'default'           => $this->omnify_settings->defaults(),
			]
		);
	}

	/**
	 * Check if current admin view is an Omnify eCommerce page.
	 *
	 * @param \WP_Screen|null $omnify_screen
	 * @param string $omnify_hook
	 * @return bool
	 */
	public function is_omnify_screen(?\WP_Screen $omnify_screen = null, string $omnify_hook = ''): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		if ('' !== $omnify_page && (str_starts_with($omnify_page, 'omnify') || str_contains($omnify_page, 'omnify'))) {
			return true;
		}

		if ('' !== $omnify_hook && (str_starts_with($omnify_hook, 'omnify') || str_contains($omnify_hook, 'omnify'))) {
			return true;
		}

		if (! $omnify_screen && function_exists('get_current_screen')) {
			$omnify_screen = get_current_screen();
		}

		if ($omnify_screen && (str_starts_with($omnify_screen->id, 'omnify') || str_contains($omnify_screen->id, 'omnify'))) {
			return true;
		}

		return false;
	}

	public function enqueue_assets(string $omnify_hook): void {
		if (! $this->is_omnify_screen(null, $omnify_hook)) {
			return;
		}

		wp_enqueue_media();

		$admin_css_file = OMNIFY_PATH . 'assets/admin/admin.css';
		$admin_css_ver  = file_exists($admin_css_file) ? (string) filemtime($admin_css_file) : OMNIFY_VERSION;

		$admin_js_file = OMNIFY_PATH . 'assets/admin/admin.js';
		$admin_js_ver  = file_exists($admin_js_file) ? (string) filemtime($admin_js_file) : OMNIFY_VERSION;

		wp_enqueue_style(
			'omnify-admin',
			OMNIFY_URL . 'assets/admin/admin.css',
			[],
			$admin_css_ver
		);

		wp_enqueue_script(
			'omnify-admin',
			OMNIFY_URL . 'assets/admin/admin.js',
			['jquery'],
			$admin_js_ver,
			true
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (isset($_GET['page']) && $_GET['page'] === 'omnify-analytics') {
			wp_enqueue_script(
				'chartjs',
				OMNIFY_URL . 'assets/admin/chart.umd.js',
				[],
				OMNIFY_VERSION,
				true
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (isset($_GET['page']) && $_GET['page'] === 'omnify-products') {
			$editor_js_file = OMNIFY_PATH . 'assets/admin/product-editor.js';
			$editor_js_ver  = file_exists($editor_js_file) ? (string) filemtime($editor_js_file) : OMNIFY_VERSION;

			wp_enqueue_script(
				'omnify-product-editor',
				OMNIFY_URL . 'assets/admin/product-editor.js',
				['jquery'],
				$editor_js_ver,
				true
			);
			wp_localize_script('omnify-product-editor', 'omnifyProductL10n', [
				'defaultPhysicalTitle' => __('Secure Delivery', 'omnifywp-ecommerce'),
				'defaultPhysicalText'  => __('Order confirmed. Shipped fast with tracking link sent to your email.', 'omnifywp-ecommerce'),
				'defaultDigitalTitle'  => __('Instant Secure Access', 'omnifywp-ecommerce'),
				'defaultDigitalText'   => __('Files available for download immediately after payment. Secure links emailed instantly.', 'omnifywp-ecommerce'),
				'titleRequired'        => __('Product title is required.', 'omnifywp-ecommerce'),
				'descRequired'         => __('Product description is required.', 'omnifywp-ecommerce'),
				'confirmRemoveVar'     => __('Are you sure you want to remove this variation?', 'omnifywp-ecommerce'),
				'generateConfirm'      => __('Generate %d variations? This will clear any unsaved variations.', 'omnifywp-ecommerce'),
				'selectGlobalAttr'     => __('Please select a global attribute to add.', 'omnifywp-ecommerce'),
				'defineAttrFirst'      => __('Please define at least one attribute with options first.', 'omnifywp-ecommerce'),
			]);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'omnify-settings') {
			$settings_js_file = OMNIFY_PATH . 'assets/admin/settings-helper.js';
			$settings_js_ver  = file_exists($settings_js_file) ? (string) filemtime($settings_js_file) : OMNIFY_VERSION;

			wp_enqueue_script(
				'omnify-settings-helper',
				OMNIFY_URL . 'assets/admin/settings-helper.js',
				['jquery'],
				$settings_js_ver,
				true
			);
			wp_localize_script('omnify-settings-helper', 'omnifySettingsL10n', [
				'allStates'       => __('All states (*)', 'omnifywp-ecommerce'),
				'allCountries'    => __('All countries (*)', 'omnifywp-ecommerce'),
				'deleteZone'      => __('Delete Zone', 'omnifywp-ecommerce'),
				'zoneName'        => __('Zone Name', 'omnifywp-ecommerce'),
				'status'          => __('Status', 'omnifywp-ecommerce'),
				'yes'             => __('Yes', 'omnifywp-ecommerce'),
				'no'              => __('No', 'omnifywp-ecommerce'),
				'countryCode'     => __('Country Code', 'omnifywp-ecommerce'),
				'stateCode'       => __('State Code', 'omnifywp-ecommerce'),
				'rateCost'        => __('Rate Cost', 'omnifywp-ecommerce'),
				'customPayment'   => __('Custom / Manual Payment', 'omnifywp-ecommerce'),
				'paypalOffline'   => __('A flexible method for any other offline arrangement (e.g. PayPal invoice, wire transfer).', 'omnifywp-ecommerce'),
				'displayName'     => __('Display Name', 'omnifywp-ecommerce'),
				'manualPayment'   => __('Manual Payment', 'omnifywp-ecommerce'),
				'instructions'    => __('Payment Instructions', 'omnifywp-ecommerce'),
				'delete'          => __('Delete', 'omnifywp-ecommerce'),
			]);
			wp_localize_script('omnify-settings-helper', 'omnifyLocationData', [
				'countries' => \Omnify\eCommerce\Support\Omnify_Locations::countries(),
				'states'    => \Omnify\eCommerce\Support\Omnify_Locations::states(),
			]);
		}

		$omnify_settings = $this->omnify_settings->all();
		wp_localize_script(
			'omnify-admin',
			'omnifyAdmin',
			[
				'pushNotificationsEnabled' => ! empty($omnify_settings['push_notifications_enabled']),
				'notificationTitle' => __('Omnify', 'omnifywp-ecommerce'),
				'ajaxUrl'           => admin_url('admin-ajax.php'),
				'apiNonce'          => wp_create_nonce('omnify_api_nonce'),
				'adminNonce'        => wp_create_nonce('omnify_admin_nonce'),
			]
		);
	}

	public function render_dashboard(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_stats = $this->get_dashboard_stats();
		$omnify_recent_orders = $this->omnify_orders->all(['per_page' => 5]);
		$omnify_recent_customers = $this->omnify_customers->all(['per_page' => 5]);

		include OMNIFY_PATH . 'templates/admin/dashboard.php';
	}

	public function render_products(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_product_repo = $this->omnify_products;
		$omnify_product_files_repo = $this->omnify_product_files;
		$omnify_signed_urls = $this->omnify_signed_urls;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status = sanitize_key(wp_unslash($_GET['status'] ?? 'all'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_category = sanitize_text_field(wp_unslash($_GET['category'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_type = sanitize_key(wp_unslash($_GET['type'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_from = sanitize_text_field(wp_unslash($_GET['date_from'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_to = sanitize_text_field(wp_unslash($_GET['date_to'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_sort = sanitize_key(wp_unslash($_GET['sort'] ?? 'updated_desc'));

		$omnify_products = $this->omnify_products->all([
			'per_page' => 100,
			'status' => $omnify_status,
			'category' => $omnify_category,
			'type' => $omnify_type,
			'date_from' => $omnify_date_from,
			'date_to' => $omnify_date_to,
			'sort' => $omnify_sort,
		]);

		// Filter options
		global $wpdb;
		$omnify_cat_table = $wpdb->prefix . 'omnify_product_categories';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_filter_categories = $this->cached_db_get_col('product_filter_categories', "SELECT DISTINCT name FROM {$omnify_cat_table} ORDER BY name ASC");
		$omnify_filter_categories = is_array($omnify_filter_categories) ? $omnify_filter_categories : [];

		include OMNIFY_PATH . 'templates/admin/products.php';
	}

	public function render_orders(): void {
		$omnify_settings = $this->omnify_settings->all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_action = sanitize_key(wp_unslash($_GET['action'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_id     = absint(wp_unslash($_GET['id'] ?? 0));

		if ('view' === $omnify_action && $omnify_id) {
			$omnify_order = $this->omnify_orders->find($omnify_id);
			if ($omnify_order) {
				$omnify_product_files_repo = $this->omnify_product_files;
				$omnify_customer_access_repo = $this->omnify_customer_access;
				$omnify_signed_urls = $this->omnify_signed_urls;
				include OMNIFY_PATH . 'templates/admin/order-detail.php';
				return;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status = sanitize_key(wp_unslash($_GET['status'] ?? 'all'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_search = sanitize_text_field(wp_unslash($_GET['search'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_from = sanitize_text_field(wp_unslash($_GET['date_from'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_to = sanitize_text_field(wp_unslash($_GET['date_to'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_sort = sanitize_key(wp_unslash($_GET['sort'] ?? 'created_desc'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_payment_method = sanitize_text_field(wp_unslash($_GET['payment_method'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_product_type = sanitize_key(wp_unslash($_GET['product_type'] ?? ''));
		$omnify_orders = $this->omnify_orders->all([
			'per_page' => 100,
			'status' => $omnify_status,
			'search' => $omnify_search,
			'date_from' => $omnify_date_from,
			'date_to' => $omnify_date_to,
			'sort' => $omnify_sort,
			'payment_method' => $omnify_payment_method,
			'product_type' => $omnify_product_type,
		]);

		include OMNIFY_PATH . 'templates/admin/orders.php';
	}

	public function render_customers(): void {
		global $wpdb;
		$omnify_settings = $this->omnify_settings->all();
		$omnify_currency_symbol = function($omnify_currency) {
			switch ($omnify_currency) {
				case 'EUR': return '€';
				case 'GBP': return '£';
				case 'JPY': return '¥';
				case 'CAD': return 'C$';
				case 'AUD': return 'A$';
				default: return '$';
			}
		};
		$omnify_symbol = $omnify_currency_symbol($omnify_settings['default_currency'] ?? 'USD');
		$omnify_products = $this->omnify_products->all(['per_page' => 100]);
		$omnify_customer_repo = $this->omnify_customers;
		$omnify_customer_access_repo = $this->omnify_customer_access;
		$omnify_product_repo = $this->omnify_products;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status = sanitize_key(wp_unslash($_GET['status'] ?? 'all'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_search = sanitize_text_field(wp_unslash($_GET['search'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_from = sanitize_text_field(wp_unslash($_GET['date_from'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_to = sanitize_text_field(wp_unslash($_GET['date_to'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_sort = sanitize_key(wp_unslash($_GET['sort'] ?? 'created_desc'));
		$omnify_customers = $this->omnify_customers->all([
			'per_page' => 100,
			'status' => $omnify_status,
			'search' => $omnify_search,
			'date_from' => $omnify_date_from,
			'date_to' => $omnify_date_to,
			'sort' => $omnify_sort,
		]);

		// Attach order stats for list
		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		foreach ($omnify_customers as &$omnify_c) {
			$omnify_cid = (int) $omnify_c['id'];
			$omnify_stats = $this->cached_db_get_row('customer_order_stats', $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) as order_count, COALESCE(SUM(total), 0) as total_spent FROM {$omnify_orders_table} WHERE customer_id = %d AND status NOT IN ('cancelled','failed')",
				$omnify_cid
			));
			$omnify_c['order_count'] = (int) ($omnify_stats['order_count'] ?? 0);
			$omnify_c['total_spent'] = (float) ($omnify_stats['total_spent'] ?? 0);
		}
		unset($omnify_c);

		include OMNIFY_PATH . 'templates/admin/customers.php';
	}

	public function render_analytics(): void {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		global $wpdb;
		$omnify_settings = $this->omnify_settings->all();
		$omnify_currency_symbol = function($omnify_currency) {
			switch ($omnify_currency) {
				case 'EUR': return '€';
				case 'GBP': return '£';
				case 'JPY': return '¥';
				case 'CAD': return 'C$';
				case 'AUD': return 'A$';
				default: return '$';
			}
		};
		$omnify_symbol = $omnify_currency_symbol($omnify_settings['default_currency'] ?? 'USD');

		$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_all_analytics_categories = $this->cached_db_get_col('analytics_categories', "SELECT DISTINCT name FROM {$omnify_categories_table} ORDER BY name ASC");
		$omnify_all_analytics_categories = is_array($omnify_all_analytics_categories) ? $omnify_all_analytics_categories : [];

		// 1. Resolve Range & Dates
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_range = sanitize_key(wp_unslash($_GET['range'] ?? '7days'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_start_date_raw = sanitize_text_field(wp_unslash($_GET['start_date'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_end_date_raw = sanitize_text_field(wp_unslash($_GET['end_date'] ?? ''));

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_selected_type = sanitize_key(wp_unslash($_GET['product_type'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_selected_method = sanitize_text_field(wp_unslash($_GET['payment_method'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_selected_status = sanitize_key(wp_unslash($_GET['order_status'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_selected_category = sanitize_text_field(wp_unslash($_GET['category'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_coupon_filter = sanitize_key(wp_unslash($_GET['coupon_filter'] ?? '')); // all, with, without

		$omnify_now = current_time('timestamp');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$omnify_today_str = date('Y-m-d', $omnify_now);
		
		switch ($omnify_range) {
			case 'today':
				$omnify_start = $omnify_today_str . ' 00:00:00';
				$omnify_end   = $omnify_today_str . ' 23:59:59';
				break;
			case 'yesterday':
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$omnify_yesterday_str = date('Y-m-d', strtotime('-1 day', $omnify_now));
				$omnify_start = $omnify_yesterday_str . ' 00:00:00';
				$omnify_end   = $omnify_yesterday_str . ' 23:59:59';
				break;
			case '30days':
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$omnify_start = date('Y-m-d', strtotime('-29 days', $omnify_now)) . ' 00:00:00';
				$omnify_end   = $omnify_today_str . ' 23:59:59';
				break;
			case 'this_month':
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$omnify_start = date('Y-m-01', $omnify_now) . ' 00:00:00';
				$omnify_end   = $omnify_today_str . ' 23:59:59';
				break;
			case 'custom':
				$omnify_start = (!empty($omnify_start_date_raw) ? $omnify_start_date_raw : $omnify_today_str) . ' 00:00:00';
				$omnify_end   = (!empty($omnify_end_date_raw) ? $omnify_end_date_raw : $omnify_today_str) . ' 23:59:59';
				break;
			case 'all_time':
				$omnify_start = '2000-01-01 00:00:00';
				$omnify_end   = $omnify_today_str . ' 23:59:59';
				break;
			case '7days':
			default:
				$omnify_range = '7days';
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$omnify_start = date('Y-m-d', strtotime('-6 days', $omnify_now)) . ' 00:00:00';
				$omnify_end   = $omnify_today_str . ' 23:59:59';
				break;
		}

		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_order_items_table = $wpdb->prefix . 'omnify_order_items';
		$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
		$omnify_products_table = $wpdb->prefix . 'omnify_products';
		$omnify_downloads_table = $wpdb->prefix . 'omnify_downloads';

		// Dynamic where clauses with table prefixes
		$omnify_where_clauses = ["o.created_at >= %s", "o.created_at <= %s"];
		$omnify_where_args = [$omnify_start, $omnify_end];

		if (!empty($omnify_selected_status)) {
			$omnify_where_clauses[] = "o.status = %s";
			$omnify_where_args[] = $omnify_selected_status;
		} else {
			$omnify_where_clauses[] = "o.status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')";
		}

		if (!empty($omnify_selected_method)) {
			$omnify_where_clauses[] = "o.payment_method = %s";
			$omnify_where_args[] = $omnify_selected_method;
		}

		if (!empty($omnify_selected_type)) {
			$omnify_where_clauses[] = "o.id IN (SELECT oi_sub.order_id FROM {$omnify_order_items_table} oi_sub JOIN {$omnify_products_table} p_sub ON oi_sub.product_id = p_sub.id WHERE p_sub.type = %s)";
			$omnify_where_args[] = $omnify_selected_type;
		}

		if (!empty($omnify_selected_category)) {
			$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
			$omnify_where_clauses[] = "o.id IN (SELECT oi_sub.order_id FROM {$omnify_order_items_table} oi_sub JOIN {$omnify_categories_table} pc_sub ON oi_sub.product_id = pc_sub.product_id WHERE pc_sub.name = %s)";
			$omnify_where_args[] = $omnify_selected_category;
		}

		if ($omnify_coupon_filter === 'with') {
			$omnify_where_clauses[] = "o.coupon_code IS NOT NULL AND o.coupon_code != ''";
		} elseif ($omnify_coupon_filter === 'without') {
			$omnify_where_clauses[] = "(o.coupon_code IS NULL OR o.coupon_code = '')";
		}

		$omnify_where_str = implode(' AND ', $omnify_where_clauses);

		// Calculate previous equivalent period
		$omnify_start_ts = strtotime($omnify_start);
		$omnify_end_ts = strtotime($omnify_end);
		$omnify_duration = $omnify_end_ts - $omnify_start_ts;
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$omnify_prev_start = date('Y-m-d H:i:s', $omnify_start_ts - $omnify_duration - 1);
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$omnify_prev_end = date('Y-m-d H:i:s', $omnify_start_ts - 1);

		$omnify_prev_where_clauses = ["o.created_at >= %s", "o.created_at <= %s"];
		$omnify_prev_where_args = [$omnify_prev_start, $omnify_prev_end];

		if (!empty($omnify_selected_status)) {
			$omnify_prev_where_clauses[] = "o.status = %s";
			$omnify_prev_where_args[] = $omnify_selected_status;
		} else {
			$omnify_prev_where_clauses[] = "o.status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')";
		}

		if (!empty($omnify_selected_method)) {
			$omnify_prev_where_clauses[] = "o.payment_method = %s";
			$omnify_prev_where_args[] = $omnify_selected_method;
		}

		if (!empty($omnify_selected_type)) {
			$omnify_prev_where_clauses[] = "o.id IN (SELECT oi_sub.order_id FROM {$omnify_order_items_table} oi_sub JOIN {$omnify_products_table} p_sub ON oi_sub.product_id = p_sub.id WHERE p_sub.type = %s)";
			$omnify_prev_where_args[] = $omnify_selected_type;
		}

		if (!empty($omnify_selected_category)) {
			$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
			$omnify_prev_where_clauses[] = "o.id IN (SELECT oi_sub.order_id FROM {$omnify_order_items_table} oi_sub JOIN {$omnify_categories_table} pc_sub ON oi_sub.product_id = pc_sub.product_id WHERE pc_sub.name = %s)";
			$omnify_prev_where_args[] = $omnify_selected_category;
		}

		if ($omnify_coupon_filter === 'with') {
			$omnify_prev_where_clauses[] = "o.coupon_code IS NOT NULL AND o.coupon_code != ''";
		} elseif ($omnify_coupon_filter === 'without') {
			$omnify_prev_where_clauses[] = "(o.coupon_code IS NULL OR o.coupon_code = '')";
		}

		$omnify_prev_where_str = implode(' AND ', $omnify_prev_where_clauses);

		// 2. Fetch KPIs
		$omnify_sql_kpis = $wpdb->prepare(
			"SELECT 
				COALESCE(SUM(o.total), 0) as gross_sales,
				COALESCE(SUM(o.subtotal - o.discount_amount), 0) as net_sales,
				COALESCE(SUM(o.discount_amount), 0) as coupon_discounts,
				COALESCE(SUM(o.tax), 0) as tax_collected,
				COALESCE(SUM(o.shipping_total), 0) as shipping_collected,
				COUNT(DISTINCT o.id) as order_count
			FROM {$omnify_orders_table} o
			WHERE {$omnify_where_str}",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_kpis = $this->cached_db_get_row('analytics_kpis', $omnify_sql_kpis);

		$omnify_gross_sales = (float) $omnify_kpis['gross_sales'];
		$omnify_net_sales = (float) $omnify_kpis['net_sales'];
		$omnify_coupon_discounts = (float) $omnify_kpis['coupon_discounts'];
		$omnify_tax_collected = (float) $omnify_kpis['tax_collected'];
		$omnify_shipping_collected = (float) $omnify_kpis['shipping_collected'];
		$omnify_order_count = (int) $omnify_kpis['order_count'];
		$omnify_aov = $omnify_order_count > 0 ? ($omnify_net_sales / $omnify_order_count) : 0.0;

		$omnify_sql_items_sold = $wpdb->prepare(
			"SELECT COALESCE(SUM(oi.quantity), 0) as items_sold
			FROM {$omnify_order_items_table} oi
			JOIN {$omnify_orders_table} o ON oi.order_id = o.id
			WHERE {$omnify_where_str}",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_items_sold = (int) $this->cached_db_get_var('analytics_items_sold', $omnify_sql_items_sold);

		$omnify_sql_coupon_order_count = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT o.id) FROM {$omnify_orders_table} o WHERE o.coupon_code IS NOT NULL AND o.coupon_code != '' AND {$omnify_where_str}",
			...$omnify_where_args
		);
		$omnify_coupon_order_count = (int) $this->cached_db_get_var('analytics_coupon_order_count', $omnify_sql_coupon_order_count);
		$omnify_coupon_order_rate = $omnify_order_count > 0 ? ($omnify_coupon_order_count / $omnify_order_count) * 100 : 0.0;

		$omnify_sql_refunded_amount = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COALESCE(SUM(o.refunded_amount), 0) FROM {$omnify_orders_table} o WHERE {$omnify_where_str}",
			...$omnify_where_args
		);
		$omnify_refunded_amount = (float) $this->cached_db_get_var('analytics_refunded_amount', $omnify_sql_refunded_amount);
		$omnify_net_after_refunds = max(0.0, $omnify_net_sales - $omnify_refunded_amount);

		// Downloads in Range with filters
		$omnify_dl_where_clauses = ["d.downloaded_at >= %s", "d.downloaded_at <= %s"];
		$omnify_dl_where_args = [$omnify_start, $omnify_end];
		$omnify_dl_join = "";
		$omnify_has_dl_filter = !empty($omnify_selected_status) || !empty($omnify_selected_method) || !empty($omnify_selected_type) || !empty($omnify_selected_category) || ($omnify_coupon_filter === 'with' || $omnify_coupon_filter === 'without');
		if ($omnify_has_dl_filter) {
			$omnify_dl_join = "JOIN {$omnify_orders_table} o ON d.order_id = o.id";
			if (!empty($omnify_selected_status)) {
				$omnify_dl_where_clauses[] = "o.status = %s";
				$omnify_dl_where_args[] = $omnify_selected_status;
			}
			if (!empty($omnify_selected_method)) {
				$omnify_dl_where_clauses[] = "o.payment_method = %s";
				$omnify_dl_where_args[] = $omnify_selected_method;
			}
			if (!empty($omnify_selected_type)) {
				$omnify_dl_join .= " JOIN {$omnify_products_table} p ON d.product_id = p.id";
				$omnify_dl_where_clauses[] = "p.type = %s";
				$omnify_dl_where_args[] = $omnify_selected_type;
			}
			if (!empty($omnify_selected_category)) {
				$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
				$omnify_dl_join .= " JOIN {$omnify_order_items_table} oi_dl ON oi_dl.order_id = o.id JOIN {$omnify_categories_table} pc_dl ON oi_dl.product_id = pc_dl.product_id";
				$omnify_dl_where_clauses[] = "pc_dl.name = %s";
				$omnify_dl_where_args[] = $omnify_selected_category;
			}
			if ($omnify_coupon_filter === 'with') {
				$omnify_dl_where_clauses[] = "o.coupon_code IS NOT NULL AND o.coupon_code != ''";
			} elseif ($omnify_coupon_filter === 'without') {
				$omnify_dl_where_clauses[] = "(o.coupon_code IS NULL OR o.coupon_code = '')";
			}
		}
		$omnify_dl_where_str = implode(' AND ', $omnify_dl_where_clauses);
		$omnify_sql_downloads = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT d.id) FROM {$omnify_downloads_table} d {$omnify_dl_join} WHERE {$omnify_dl_where_str}",
			...$omnify_dl_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_downloads_count = (int) $this->cached_db_get_var('analytics_downloads', $omnify_sql_downloads);

		$omnify_sql_refunded_orders = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT o.id) FROM {$omnify_orders_table} o WHERE o.status IN ('refunded', 'refund_requested') AND {$omnify_where_str}",
			...$omnify_where_args
		);
		$omnify_refunded_orders = (int) $this->cached_db_get_var('analytics_refunded_orders', $omnify_sql_refunded_orders);
		$omnify_refund_rate = $omnify_order_count > 0 ? ($omnify_refunded_orders / $omnify_order_count * 100) : 0.0;

		$omnify_avg_items_per_order = $omnify_order_count > 0 ? round($omnify_items_sold / $omnify_order_count, 1) : 0.0;

		// New customers created in Range
		$omnify_cust_where_clauses = ["c.created_at >= %s", "c.created_at <= %s"];
		$omnify_cust_where_args = [$omnify_start, $omnify_end];
		$omnify_cust_join = "";
		if (!empty($omnify_selected_status) || !empty($omnify_selected_method) || !empty($omnify_selected_type)) {
			$omnify_cust_join = "JOIN {$omnify_orders_table} o ON o.customer_id = c.id";
			if (!empty($omnify_selected_status)) {
				$omnify_cust_where_clauses[] = "o.status = %s";
				$omnify_cust_where_args[] = $omnify_selected_status;
			}
			if (!empty($omnify_selected_method)) {
				$omnify_cust_where_clauses[] = "o.payment_method = %s";
				$omnify_cust_where_args[] = $omnify_selected_method;
			}
			if (!empty($omnify_selected_type)) {
				$omnify_cust_join .= " JOIN {$omnify_order_items_table} oi ON oi.order_id = o.id JOIN {$omnify_products_table} p ON oi.product_id = p.id";
				$omnify_cust_where_clauses[] = "p.type = %s";
				$omnify_cust_where_args[] = $omnify_selected_type;
			}
		}
		$omnify_cust_where_str = implode(' AND ', $omnify_cust_where_clauses);
		$omnify_sql_customers = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT c.id) FROM {$omnify_customers_table} c {$omnify_cust_join} WHERE {$omnify_cust_where_str}",
			...$omnify_cust_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_new_customers_count = (int) $this->cached_db_get_var('analytics_customers', $omnify_sql_customers);

		// Fetch previous KPIs
		$omnify_sql_prev_kpis = $wpdb->prepare(
			"SELECT 
				COALESCE(SUM(o.total), 0) as gross_sales,
				COALESCE(SUM(o.subtotal - o.discount_amount), 0) as net_sales,
				COALESCE(SUM(o.discount_amount), 0) as coupon_discounts,
				COALESCE(SUM(o.tax), 0) as tax_collected,
				COALESCE(SUM(o.shipping_total), 0) as shipping_collected,
				COUNT(DISTINCT o.id) as order_count
			FROM {$omnify_orders_table} o
			WHERE {$omnify_prev_where_str}",
			...$omnify_prev_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_prev_kpis = $this->cached_db_get_row('analytics_prev_kpis', $omnify_sql_prev_kpis);

		$omnify_prev_gross = (float) ($omnify_prev_kpis['gross_sales'] ?? 0.0);
		$omnify_prev_net = (float) ($omnify_prev_kpis['net_sales'] ?? 0.0);
		$omnify_prev_coupon_discounts = (float) ($omnify_prev_kpis['coupon_discounts'] ?? 0.0);
		$omnify_prev_tax = (float) ($omnify_prev_kpis['tax_collected'] ?? 0.0);
		$omnify_prev_shipping = (float) ($omnify_prev_kpis['shipping_collected'] ?? 0.0);
		$omnify_prev_orders = (int) ($omnify_prev_kpis['order_count'] ?? 0);
		$omnify_prev_aov = $omnify_prev_orders > 0 ? ($omnify_prev_net / $omnify_prev_orders) : 0.0;

		$omnify_sql_prev_refunded = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COALESCE(SUM(o.refunded_amount), 0) FROM {$omnify_orders_table} o WHERE {$omnify_prev_where_str}",
			...$omnify_prev_where_args
		);
		$omnify_prev_refunded = (float) $this->cached_db_get_var('analytics_prev_refunded', $omnify_sql_prev_refunded);
		$omnify_prev_net_after_refunds = max(0.0, $omnify_prev_net - $omnify_prev_refunded);

		$omnify_sql_prev_items_sold = $wpdb->prepare(
			"SELECT COALESCE(SUM(oi.quantity), 0) as items_sold
			FROM {$omnify_order_items_table} oi
			JOIN {$omnify_orders_table} o ON oi.order_id = o.id
			WHERE {$omnify_prev_where_str}",
			...$omnify_prev_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_prev_items_sold = (int) $this->cached_db_get_var('analytics_prev_items_sold', $omnify_sql_prev_items_sold);

		$omnify_sql_prev_coupon_order_count = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT o.id) FROM {$omnify_orders_table} o WHERE o.coupon_code IS NOT NULL AND o.coupon_code != '' AND {$omnify_prev_where_str}",
			...$omnify_prev_where_args
		);
		$omnify_prev_coupon_order_count = (int) $this->cached_db_get_var('analytics_prev_coupon_order_count', $omnify_sql_prev_coupon_order_count);
		$omnify_prev_coupon_order_rate = $omnify_prev_orders > 0 ? ($omnify_prev_coupon_order_count / $omnify_prev_orders) * 100 : 0.0;

		$omnify_prev_dl_where_clauses = ["d.downloaded_at >= %s", "d.downloaded_at <= %s"];
		$omnify_prev_dl_where_args = [$omnify_prev_start, $omnify_prev_end];
		$omnify_prev_dl_join = "";
		$omnify_has_prev_dl_filter = !empty($omnify_selected_status) || !empty($omnify_selected_method) || !empty($omnify_selected_type) || !empty($omnify_selected_category) || ($omnify_coupon_filter === 'with' || $omnify_coupon_filter === 'without');
		if ($omnify_has_prev_dl_filter) {
			$omnify_prev_dl_join = "JOIN {$omnify_orders_table} o ON d.order_id = o.id";
			if (!empty($omnify_selected_status)) {
				$omnify_prev_dl_where_clauses[] = "o.status = %s";
				$omnify_prev_dl_where_args[] = $omnify_selected_status;
			}
			if (!empty($omnify_selected_method)) {
				$omnify_prev_dl_where_clauses[] = "o.payment_method = %s";
				$omnify_prev_dl_where_args[] = $omnify_selected_method;
			}
			if (!empty($omnify_selected_type)) {
				$omnify_prev_dl_join .= " JOIN {$omnify_products_table} p ON d.product_id = p.id";
				$omnify_prev_dl_where_clauses[] = "p.type = %s";
				$omnify_prev_dl_where_args[] = $omnify_selected_type;
			}
			if (!empty($omnify_selected_category)) {
				$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
				$omnify_prev_dl_join .= " JOIN {$omnify_order_items_table} oi_dl ON oi_dl.order_id = o.id JOIN {$omnify_categories_table} pc_dl ON oi_dl.product_id = pc_dl.product_id";
				$omnify_prev_dl_where_clauses[] = "pc_dl.name = %s";
				$omnify_prev_dl_where_args[] = $omnify_selected_category;
			}
			if ($omnify_coupon_filter === 'with') {
				$omnify_prev_dl_where_clauses[] = "o.coupon_code IS NOT NULL AND o.coupon_code != ''";
			} elseif ($omnify_coupon_filter === 'without') {
				$omnify_prev_dl_where_clauses[] = "(o.coupon_code IS NULL OR o.coupon_code = '')";
			}
		}
		$omnify_prev_dl_where_str = implode(' AND ', $omnify_prev_dl_where_clauses);
		$omnify_sql_prev_downloads = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT d.id) FROM {$omnify_downloads_table} d {$omnify_prev_dl_join} WHERE {$omnify_prev_dl_where_str}",
			...$omnify_prev_dl_where_args
		);
		$omnify_prev_downloads = (int) $this->cached_db_get_var('analytics_prev_downloads', $omnify_sql_prev_downloads);

		$omnify_prev_cust_where_clauses = ["c.created_at >= %s", "c.created_at <= %s"];
		$omnify_prev_cust_where_args = [$omnify_prev_start, $omnify_prev_end];
		$omnify_prev_cust_join = "";
		if (!empty($omnify_selected_status) || !empty($omnify_selected_method) || !empty($omnify_selected_type)) {
			$omnify_prev_cust_join = "JOIN {$omnify_orders_table} o ON o.customer_id = c.id";
			if (!empty($omnify_selected_status)) {
				$omnify_prev_cust_where_clauses[] = "o.status = %s";
				$omnify_prev_cust_where_args[] = $omnify_selected_status;
			}
			if (!empty($omnify_selected_method)) {
				$omnify_prev_cust_where_clauses[] = "o.payment_method = %s";
				$omnify_prev_cust_where_args[] = $omnify_selected_method;
			}
			if (!empty($omnify_selected_type)) {
				$omnify_prev_cust_join .= " JOIN {$omnify_order_items_table} oi ON oi.order_id = o.id JOIN {$omnify_products_table} p ON oi.product_id = p.id";
				$omnify_prev_cust_where_clauses[] = "p.type = %s";
				$omnify_prev_cust_where_args[] = $omnify_selected_type;
			}
		}
		$omnify_prev_cust_where_str = implode(' AND ', $omnify_prev_cust_where_clauses);
		$omnify_sql_prev_customers = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT c.id) FROM {$omnify_customers_table} c {$omnify_prev_cust_join} WHERE {$omnify_prev_cust_where_str}",
			...$omnify_prev_cust_where_args
		);
		$omnify_prev_customers = (int) $this->cached_db_get_var('analytics_prev_customers', $omnify_sql_prev_customers);

		$omnify_sql_prev_refunded_orders = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COUNT(DISTINCT o.id) FROM {$omnify_orders_table} o WHERE o.status IN ('refunded', 'refund_requested') AND {$omnify_prev_where_str}",
			...$omnify_prev_where_args
		);
		$omnify_prev_refunded_orders = (int) $this->cached_db_get_var('analytics_prev_refunded_orders', $omnify_sql_prev_refunded_orders);
		$omnify_prev_refund_rate = $omnify_prev_orders > 0 ? ($omnify_prev_refunded_orders / $omnify_prev_orders * 100) : 0.0;

		$omnify_prev_avg_items_per_order = $omnify_prev_orders > 0 ? round($omnify_prev_items_sold / $omnify_prev_orders, 1) : 0;

		// Calculate trends
		$omnify_gross_trend = $this->calculate_trend($omnify_gross_sales, $omnify_prev_gross);
		$omnify_net_trend = $this->calculate_trend($omnify_net_sales, $omnify_prev_net);
		$omnify_net_refunds_trend = $this->calculate_trend($omnify_net_after_refunds, $omnify_prev_net_after_refunds);
		$omnify_orders_trend = $this->calculate_trend($omnify_order_count, $omnify_prev_orders);
		$omnify_aov_trend = $this->calculate_trend($omnify_aov, $omnify_prev_aov);

		$omnify_items_sold_trend = $this->calculate_trend($omnify_items_sold, $omnify_prev_items_sold);
		$omnify_coupon_discounts_trend = $this->calculate_trend($omnify_coupon_discounts, $omnify_prev_coupon_discounts);
		$omnify_coupon_order_rate_trend = $this->calculate_trend($omnify_coupon_order_rate, $omnify_prev_coupon_order_rate);
		$omnify_refunded_trend = $this->calculate_trend($omnify_refunded_amount, $omnify_prev_refunded);
		$omnify_tax_trend = $this->calculate_trend($omnify_tax_collected, $omnify_prev_tax);
		$omnify_shipping_trend = $this->calculate_trend($omnify_shipping_collected, $omnify_prev_shipping);
		$omnify_downloads_trend = $this->calculate_trend($omnify_downloads_count, $omnify_prev_downloads);
		$omnify_customers_trend = $this->calculate_trend($omnify_new_customers_count, $omnify_prev_customers);
		$omnify_refund_rate_trend = $this->calculate_trend($omnify_refund_rate, $omnify_prev_refund_rate);
		$omnify_avg_items_trend = $this->calculate_trend($omnify_avg_items_per_order, $omnify_prev_avg_items_per_order);

		// 3. Fetch Top Selling Products in Range
		$omnify_sql_top_selling = $wpdb->prepare(
			"SELECT 
				oi.product_id,
				oi.product_name,
				p.type as product_type,
				p.currency,
				SUM(oi.quantity) as units_sold,
				SUM(oi.price * oi.quantity) as gross_revenue
			FROM {$omnify_order_items_table} oi
			JOIN {$omnify_orders_table} o ON oi.order_id = o.id
			LEFT JOIN {$omnify_products_table} p ON oi.product_id = p.id
			WHERE {$omnify_where_str}
			GROUP BY oi.product_id, oi.product_name, p.type, p.currency
			ORDER BY units_sold DESC
			LIMIT 10",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_top_selling = $this->cached_db_get_results('analytics_top_selling', $omnify_sql_top_selling);
		if (!is_array($omnify_top_selling)) {
			$omnify_top_selling = [];
		}

		// 4. Fetch Coupon Performance in Range
		$omnify_sql_coupons_perf = $wpdb->prepare(
			"SELECT 
				o.coupon_code,
				COUNT(DISTINCT o.id) as usage_count,
				SUM(o.discount_amount) as total_discount
			FROM {$omnify_orders_table} o
			WHERE o.coupon_code IS NOT NULL AND o.coupon_code != '' AND {$omnify_where_str}
			GROUP BY o.coupon_code
			ORDER BY usage_count DESC",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_coupons_perf = $this->cached_db_get_results('analytics_coupons_perf', $omnify_sql_coupons_perf);
		if (!is_array($omnify_coupons_perf)) {
			$omnify_coupons_perf = [];
		}

		// 5. Fetch Recent Orders in Range
		$omnify_sql_recent_orders = $wpdb->prepare(
			"SELECT 
				o.id,
				o.order_number,
				o.total,
				o.payment_method,
				o.created_at,
				c.first_name,
				c.last_name,
				c.email
			FROM {$omnify_orders_table} o
			LEFT JOIN {$omnify_customers_table} c ON o.customer_id = c.id
			WHERE {$omnify_where_str}
			ORDER BY o.created_at DESC
			LIMIT 15",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_recent_orders = $this->cached_db_get_results('analytics_recent_orders', $omnify_sql_recent_orders);
		if (!is_array($omnify_recent_orders)) {
			$omnify_recent_orders = [];
		}

		$omnify_sql_status_breakdown = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT o.status, COUNT(DISTINCT o.id) as total, COALESCE(SUM(o.total), 0) as revenue FROM {$omnify_orders_table} o WHERE {$omnify_where_str} GROUP BY o.status ORDER BY total DESC",
			...$omnify_where_args
		);
		$omnify_status_breakdown = $this->cached_db_get_results('analytics_status_breakdown', $omnify_sql_status_breakdown);
		if (! is_array($omnify_status_breakdown)) {
			$omnify_status_breakdown = [];
		}

		$omnify_sql_payment_breakdown = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			"SELECT COALESCE(NULLIF(o.payment_method, ''), 'unknown') as payment_method, COUNT(DISTINCT o.id) as total, COALESCE(SUM(o.total), 0) as revenue FROM {$omnify_orders_table} o WHERE {$omnify_where_str} GROUP BY o.payment_method ORDER BY total DESC",
			...$omnify_where_args
		);
		$omnify_payment_breakdown = $this->cached_db_get_results('analytics_payment_breakdown', $omnify_sql_payment_breakdown);
		if (! is_array($omnify_payment_breakdown)) {
			$omnify_payment_breakdown = [];
		}

		$omnify_sql_tax_breakdown = $wpdb->prepare(
			"SELECT
				COALESCE(NULLIF(o.tax_reporting_code, ''), 'unassigned') as reporting_code,
				COALESCE(NULLIF(o.tax_label, ''), 'Tax') as tax_label,
				o.tax_rate,
				o.tax_inclusive,
				o.tax_shipping,
				o.customer_tax_exempt,
				COUNT(DISTINCT o.id) as order_count,
				COALESCE(SUM(o.tax), 0) as tax_total,
				COALESCE(SUM(o.shipping_total), 0) as shipping_total
			FROM {$omnify_orders_table} o
			WHERE {$omnify_where_str}
			GROUP BY reporting_code, tax_label, o.tax_rate, o.tax_inclusive, o.tax_shipping, o.customer_tax_exempt
			ORDER BY tax_total DESC",
			...$omnify_where_args
		);
		$omnify_tax_breakdown = $this->cached_db_get_results('analytics_tax_breakdown', $omnify_sql_tax_breakdown);
		if (! is_array($omnify_tax_breakdown)) {
			$omnify_tax_breakdown = [];
		}

		$omnify_sql_top_customers = $wpdb->prepare(
			"SELECT c.id, c.email, c.first_name, c.last_name, COUNT(DISTINCT o.id) as orders_count, COALESCE(SUM(o.total), 0) as total_spent
			FROM {$omnify_orders_table} o
			LEFT JOIN {$omnify_customers_table} c ON o.customer_id = c.id
			WHERE o.status NOT IN ('cancelled', 'failed') AND {$omnify_where_str}
			GROUP BY o.customer_id, c.id, c.email, c.first_name, c.last_name
			ORDER BY total_spent DESC
			LIMIT 10",
			...$omnify_where_args
		);
		$omnify_top_customers = $this->cached_db_get_results('analytics_top_customers', $omnify_sql_top_customers);
		if (! is_array($omnify_top_customers)) {
			$omnify_top_customers = [];
		}

		// 6. Query daily (or monthly if range > 90 days) revenue and orders
		$omnify_duration_days = (strtotime($omnify_end) - strtotime($omnify_start)) / DAY_IN_SECONDS;
		$omnify_chart_labels = [];
		$omnify_chart_revenue = [];
		$omnify_chart_orders = [];

		if ($omnify_duration_days > 90) {
			$omnify_sql_chart = $wpdb->prepare(
				"SELECT DATE_FORMAT(o.created_at, '%%Y-%%m') as period, COALESCE(SUM(o.total), 0) as revenue, COUNT(DISTINCT o.id) as orders
				FROM {$omnify_orders_table} o
				WHERE {$omnify_where_str}
				GROUP BY DATE_FORMAT(o.created_at, '%%Y-%%m')
				ORDER BY period ASC",
				...$omnify_where_args
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$omnify_chart_data = $this->cached_db_get_results('analytics_chart_monthly', $omnify_sql_chart);

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$omnify_start_month = new \DateTime(date('Y-m-01', strtotime($omnify_start)));
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$omnify_end_month = new \DateTime(date('Y-m-01', strtotime($omnify_end)));
			$omnify_interval_month = new \DateInterval('P1M');
			$omnify_month_range = new \DatePeriod($omnify_start_month, $omnify_interval_month, $omnify_end_month->modify('+1 month'));

			$omnify_mapped = [];
			if (is_array($omnify_chart_data)) {
				foreach ($omnify_chart_data as $omnify_row) {
					$omnify_mapped[$omnify_row['period']] = [
						'revenue' => (float) $omnify_row['revenue'],
						'orders'  => (int) $omnify_row['orders']
					];
				}
			}

			foreach ($omnify_month_range as $omnify_dt) {
				$omnify_period_str = $omnify_dt->format('Y-m');
				$omnify_chart_labels[] = $omnify_dt->format('M Y');
				$omnify_chart_revenue[] = $omnify_mapped[$omnify_period_str]['revenue'] ?? 0.0;
				$omnify_chart_orders[] = $omnify_mapped[$omnify_period_str]['orders'] ?? 0;
			}
		} else {
			$omnify_sql_chart = $wpdb->prepare(
				"SELECT DATE(o.created_at) as period, COALESCE(SUM(o.total), 0) as revenue, COUNT(DISTINCT o.id) as orders
				FROM {$omnify_orders_table} o
				WHERE {$omnify_where_str}
				GROUP BY DATE(o.created_at)
				ORDER BY period ASC",
				...$omnify_where_args
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$omnify_chart_data = $this->cached_db_get_results('analytics_chart_daily', $omnify_sql_chart);

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$omnify_start_date_obj = new \DateTime(date('Y-m-d', strtotime($omnify_start)));
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$omnify_end_date_obj = new \DateTime(date('Y-m-d', strtotime($omnify_end)));
			$omnify_interval = new \DateInterval('P1D');
			$omnify_date_range_period = new \DatePeriod($omnify_start_date_obj, $omnify_interval, $omnify_end_date_obj->modify('+1 day'));

			$omnify_mapped = [];
			if (is_array($omnify_chart_data)) {
				foreach ($omnify_chart_data as $omnify_row) {
					$omnify_mapped[$omnify_row['period']] = [
						'revenue' => (float) $omnify_row['revenue'],
						'orders'  => (int) $omnify_row['orders']
					];
				}
			}

			foreach ($omnify_date_range_period as $omnify_dt) {
				$omnify_period_str = $omnify_dt->format('Y-m-d');
				$omnify_chart_labels[] = $omnify_dt->format('M d');
				$omnify_chart_revenue[] = $omnify_mapped[$omnify_period_str]['revenue'] ?? 0.0;
				$omnify_chart_orders[] = $omnify_mapped[$omnify_period_str]['orders'] ?? 0;
			}
		}

		// Weekday performance (Sun=1 ... Sat=7)
		$omnify_sql_weekday = $wpdb->prepare(
			"SELECT DAYOFWEEK(o.created_at) as dow, COALESCE(SUM(o.total), 0) as revenue, COUNT(DISTINCT o.id) as ords
			FROM {$omnify_orders_table} o WHERE {$omnify_where_str}
			GROUP BY dow ORDER BY dow ASC",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_weekday_rows = $this->cached_db_get_results('analytics_weekday', $omnify_sql_weekday) ?: [];
		$omnify_weekday_labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		$omnify_weekday_revenue = [0,0,0,0,0,0,0];
		$omnify_weekday_orders_cnt = [0,0,0,0,0,0,0];
		foreach ($omnify_weekday_rows as $omnify_rw) {
			$omnify_idx = max(0, min(6, ((int)$omnify_rw['dow'] - 1) % 7 ));
			$omnify_weekday_revenue[$omnify_idx] = (float)$omnify_rw['revenue'];
			$omnify_weekday_orders_cnt[$omnify_idx] = (int)$omnify_rw['ords'];
		}

		// 7. Query category shares
		$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
		$omnify_sql_cat_shares = $wpdb->prepare(
			"SELECT pc.name as category_name, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue
			FROM {$omnify_order_items_table} oi
			JOIN {$omnify_orders_table} o ON oi.order_id = o.id
			JOIN {$omnify_categories_table} pc ON oi.product_id = pc.product_id
			WHERE {$omnify_where_str}
			GROUP BY pc.slug, pc.name
			ORDER BY revenue DESC
			LIMIT 8",
			...$omnify_where_args
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_cat_shares = $this->cached_db_get_results('analytics_cat_shares', $omnify_sql_cat_shares);

		$omnify_cat_labels = [];
		$omnify_cat_revenue = [];
		if (is_array($omnify_cat_shares)) {
			foreach ($omnify_cat_shares as $omnify_row) {
				$omnify_cat_labels[] = $omnify_row['category_name'];
				$omnify_cat_revenue[] = (float) $omnify_row['revenue'];
			}
		}

		// 8. Payment method shares
		$omnify_pm_labels = [];
		$omnify_pm_orders = [];
		if (is_array($omnify_payment_breakdown)) {
			foreach ($omnify_payment_breakdown as $omnify_row) {
				$omnify_pm_labels[] = ucwords(str_replace('_', ' ', $omnify_row['payment_method']));
				$omnify_pm_orders[] = (int) $omnify_row['total'];
			}
		}

		// 9. Handle CSV Export
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (!empty($_GET['export_csv'])) {
			$this->export_analytics_csv([
				'range' => $omnify_range,
				'start' => $omnify_start,
				'end' => $omnify_end,
				'currency_symbol' => $omnify_symbol,
				'filters' => [
					'Product Type' => $omnify_selected_type ?: 'All',
					'Payment' => $omnify_selected_method ?: 'All',
					'Status' => $omnify_selected_status ?: 'Active',
					'Category' => $omnify_selected_category ?: 'All',
					'Coupon' => $omnify_coupon_filter ?: 'All'
				],
				'kpis' => [
					'Gross Sales' => $omnify_gross_sales,
					'Net Sales' => $omnify_net_sales,
					'Net After Refunds' => $omnify_net_after_refunds,
					'Refunded Amount' => $omnify_refunded_amount,
					'Coupon Discounts' => $omnify_coupon_discounts,
					'Coupon Order Rate' => $omnify_coupon_order_rate,
					'Tax Collected' => $omnify_tax_collected,
					'Shipping Collected' => $omnify_shipping_collected,
					'Total Orders' => $omnify_order_count,
					'Items Sold' => $omnify_items_sold,
					'Average Order Value' => $omnify_aov,
					'Downloads Count' => $omnify_downloads_count,
					'New Customers' => $omnify_new_customers_count,
					'Refund Rate %' => $omnify_refund_rate,
					'Avg Items Per Order' => $omnify_avg_items_per_order
				],
				'top_selling' => $omnify_top_selling,
				'coupons_perf' => $omnify_coupons_perf,
				'recent_orders' => $omnify_recent_orders,
				'status_breakdown' => $omnify_status_breakdown,
				'payment_breakdown' => $omnify_payment_breakdown,
				'tax_breakdown' => $omnify_tax_breakdown,
				'top_customers' => $omnify_top_customers
			]);
			return;
		}

		$omnify_render_trend = [$this, 'render_trend'];
		include OMNIFY_PATH . 'templates/admin/analytics.php';
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	}

	private function export_analytics_csv(array $omnify_data): void {
		header('Content-Type: text/csv; charset=utf-8');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		header('Content-Disposition: attachment; filename="omnify-analytics-report-' . date('Y-m-d') . '.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$omnify_output = fopen('php://output', 'w');
		if (!$omnify_output) return;

		// UTF-8 BOM
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite($omnify_output, "\xEF\xBB\xBF");

		// Header block
		fputcsv($omnify_output, ['--- OMNIFY ANALYTICS REPORT ---']);
		fputcsv($omnify_output, ['Report Range', $omnify_data['start'] . ' to ' . $omnify_data['end']]);
		fputcsv($omnify_output, ['Range Preset', $omnify_data['range']]);
		if (!empty($omnify_data['filters'])) {
			fputcsv($omnify_output, ['Filters', implode(' | ', array_map(fn($omnify_k,$omnify_v)=>"$omnify_k:$omnify_v", array_keys($omnify_data['filters']), $omnify_data['filters'])) ]);
		}
		fputcsv($omnify_output, []);

		// KPIs Section
		fputcsv($omnify_output, ['--- FINANCIAL SUMMARY ---']);
		fputcsv($omnify_output, ['Metric', 'Value']);
		foreach ($omnify_data['kpis'] as $omnify_metric => $omnify_val) {
			if (in_array($omnify_metric, ['Gross Sales', 'Net Sales', 'Coupon Discounts', 'Tax Collected', 'Shipping Collected', 'Average Order Value'])) {
				fputcsv($omnify_output, [$omnify_metric, $omnify_data['currency_symbol'] . number_format($omnify_val, 2)]);
			} else {
				fputcsv($omnify_output, [$omnify_metric, number_format($omnify_val)]);
			}
		}
		fputcsv($omnify_output, []);

		// Top Selling Section
		fputcsv($omnify_output, ['--- TOP SELLING PRODUCTS ---']);
		fputcsv($omnify_output, ['Product ID', 'Product Title', 'Type', 'Units Sold', 'Revenue']);
		foreach ($omnify_data['top_selling'] as $omnify_row) {
			fputcsv($omnify_output, [
				$omnify_row['product_id'],
				$omnify_row['product_name'],
				ucfirst($omnify_row['product_type'] ?? 'digital'),
				$omnify_row['units_sold'],
				$omnify_data['currency_symbol'] . number_format($omnify_row['gross_revenue'], 2)
			]);
		}
		fputcsv($omnify_output, []);

		if (! empty($omnify_data['tax_breakdown'])) {
			fputcsv($omnify_output, ['--- TAX REPORTING BREAKDOWN ---']);
			fputcsv($omnify_output, ['Reporting Code', 'Tax Label', 'Rate', 'Orders', 'Tax Total', 'Shipping Total', 'Price Mode', 'Tax Shipping', 'Tax Exempt Orders']);
			foreach ($omnify_data['tax_breakdown'] as $omnify_row) {
				fputcsv($omnify_output, [
					$omnify_row['reporting_code'],
					$omnify_row['tax_label'],
					number_format((float) $omnify_row['tax_rate'], 4) . '%',
					$omnify_row['order_count'],
					$omnify_data['currency_symbol'] . number_format((float) $omnify_row['tax_total'], 2),
					$omnify_data['currency_symbol'] . number_format((float) $omnify_row['shipping_total'], 2),
					! empty($omnify_row['tax_inclusive']) ? 'Inclusive' : 'Exclusive',
					! empty($omnify_row['tax_shipping']) ? 'Yes' : 'No',
					! empty($omnify_row['customer_tax_exempt']) ? 'Yes' : 'No',
				]);
			}
			fputcsv($omnify_output, []);
		}

		// Coupons Performance Section
		fputcsv($omnify_output, ['--- COUPONS PERFORMANCE ---']);
		fputcsv($omnify_output, ['Coupon Code', 'Usage Count', 'Total Discount Given']);
		foreach ($omnify_data['coupons_perf'] as $omnify_row) {
			fputcsv($omnify_output, [
				$omnify_row['coupon_code'],
				$omnify_row['usage_count'],
				$omnify_data['currency_symbol'] . number_format($omnify_row['total_discount'], 2)
			]);
		}
		fputcsv($omnify_output, []);

		// Recent Orders Section
		fputcsv($omnify_output, ['--- RECENT ORDERS IN RANGE ---']);
		fputcsv($omnify_output, ['Order Number', 'Customer Name', 'Customer Email', 'Date', 'Payment Method', 'Total']);
		foreach ($omnify_data['recent_orders'] as $omnify_row) {
			fputcsv($omnify_output, [
				$omnify_row['order_number'] ?: '#' . $omnify_row['id'],
				$omnify_row['first_name'] . ' ' . $omnify_row['last_name'],
				$omnify_row['email'],
				$omnify_row['created_at'],
				$omnify_row['payment_method'],
				$omnify_data['currency_symbol'] . number_format($omnify_row['total'], 2)
			]);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function render_export(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_import_result = get_transient('omnify_import_result_' . get_current_user_id());
		if ($omnify_import_result) {
			delete_transient('omnify_import_result_' . get_current_user_id());
		}

		include OMNIFY_PATH . 'templates/admin/export.php';
	}

	public function render_settings(): void {
		$omnify_settings = $this->omnify_settings->all();

		include OMNIFY_PATH . 'templates/admin/settings.php';
	}

	public function render_global_categories(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_global_categories = get_option('omnify_global_categories', []);
		if (! is_array($omnify_global_categories)) {
			$omnify_global_categories = [];
		}
		include OMNIFY_PATH . 'templates/admin/categories.php';
	}

	public function render_global_tags(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_global_tags = get_option('omnify_global_tags', []);
		if (! is_array($omnify_global_tags)) {
			$omnify_global_tags = [];
		}
		include OMNIFY_PATH . 'templates/admin/tags.php';
	}

	public function render_global_brands(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_global_brands = get_option('omnify_global_brands', []);
		if (! is_array($omnify_global_brands)) {
			$omnify_global_brands = [];
		}
		include OMNIFY_PATH . 'templates/admin/brands.php';
	}

	public function render_global_attributes(): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_global_attributes = get_option('omnify_global_attributes', []);
		if (! is_array($omnify_global_attributes)) {
			$omnify_global_attributes = [];
		}
		include OMNIFY_PATH . 'templates/admin/attributes.php';
	}

	public function render_api(): void {
		$omnify_settings = $this->omnify_settings->all();

		include OMNIFY_PATH . 'templates/admin/api.php';
	}

	public function ajax_create_api_key(): void {
		check_ajax_referer('omnify_api_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('You do not have permission to manage Omnify.', 'omnifywp-ecommerce')]);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_description = sanitize_text_field(wp_unslash($_POST['description'] ?? ''));
		$omnify_user_id     = absint(wp_unslash($_POST['user_id'] ?? get_current_user_id()));
		$omnify_permissions = sanitize_key(wp_unslash($_POST['permissions'] ?? 'read'));

		if (empty($omnify_description)) {
			wp_send_json_error(['message' => __('Description is required.', 'omnifywp-ecommerce')]);
		}

		if (! in_array($omnify_permissions, ['read', 'write', 'read_write'], true)) {
			wp_send_json_error(['message' => __('Invalid permissions.', 'omnifywp-ecommerce')]);
		}

		try {
			$omnify_consumer_key    = 'ck_' . bin2hex(random_bytes(20));
			$omnify_consumer_secret = 'cs_' . bin2hex(random_bytes(20));
		} catch (\Exception $omnify_e) {
			wp_send_json_error(['message' => __('Failed to generate keys.', 'omnifywp-ecommerce')]);
		}

		$omnify_new_key = [
			'id'                   => uniqid('key_', true),
			'description'          => $omnify_description,
			'user_id'              => $omnify_user_id,
			'permissions'          => $omnify_permissions,
			'consumer_key_last4'   => substr($omnify_consumer_key, -4),
			'consumer_key_hash'    => hash('sha256', $omnify_consumer_key),
			'consumer_secret_hash' => hash('sha256', $omnify_consumer_secret),
			'created_at'           => current_time('mysql'),
			'last_used'            => null,
		];

		$omnify_keys   = get_option('omnify_api_keys', []);
		$omnify_keys[] = $omnify_new_key;
		update_option('omnify_api_keys', $omnify_keys);

		wp_send_json_success([
			'consumer_key'    => $omnify_consumer_key,
			'consumer_secret' => $omnify_consumer_secret,
			'key'             => $omnify_new_key,
		]);
	}

	public function ajax_revoke_api_key(): void {
		check_ajax_referer('omnify_api_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('You do not have permission to manage Omnify.', 'omnifywp-ecommerce')]);
		}

		$omnify_key_id = sanitize_key(wp_unslash($_POST['key_id'] ?? ''));
		if (empty($omnify_key_id)) {
			wp_send_json_error(['message' => __('Invalid key ID.', 'omnifywp-ecommerce')]);
		}

		$omnify_keys    = get_option('omnify_api_keys', []);
		$omnify_updated = [];
		$omnify_found   = false;

		foreach ($omnify_keys as $omnify_key) {
			if (isset($omnify_key['id']) && $omnify_key['id'] === $omnify_key_id) {
				$omnify_found = true;
				continue;
			}
			$omnify_updated[] = $omnify_key;
		}

		if ($omnify_found) {
			update_option('omnify_api_keys', $omnify_updated);
			wp_send_json_success(['message' => __('Key revoked successfully.', 'omnifywp-ecommerce')]);
		} else {
			wp_send_json_error(['message' => __('Key not found.', 'omnifywp-ecommerce')]);
		}
	}

	public function ajax_test_connection(): void {
		check_ajax_referer('omnify_api_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Forbidden.', 'omnifywp-ecommerce')]);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_url = esc_url_raw(wp_unslash($_POST['url'] ?? ''));
		if (empty($omnify_url) || ! wp_http_validate_url($omnify_url)) {
			wp_send_json_error(['message' => __('Invalid URL format.', 'omnifywp-ecommerce')]);
		}

		$omnify_start    = microtime(true);
		$omnify_response = wp_safe_remote_get($omnify_url, [
			'timeout'     => 10,
			'redirection' => 5,
		]);
		$omnify_latency  = round((microtime(true) - $omnify_start) * 1000);

		if (is_wp_error($omnify_response)) {
			wp_send_json_success([
				'status'  => 'error',
				'code'    => 0,
				'message' => $omnify_response->get_error_message(),
				'latency' => $omnify_latency,
				'headers' => [],
			]);
			return;
		}

		$omnify_code             = wp_remote_retrieve_response_code($omnify_response);
		$omnify_response_message = wp_remote_retrieve_response_message($omnify_response);
		$omnify_all_headers      = wp_remote_retrieve_headers($omnify_response);
		
		$omnify_headers = [];
		foreach (['content-type', 'server', 'date'] as $omnify_h) {
			if (isset($omnify_all_headers[$omnify_h])) {
				$omnify_headers[$omnify_h] = $omnify_all_headers[$omnify_h];
			}
		}

		$omnify_status = ($omnify_code >= 200 && $omnify_code < 400) ? 'success' : 'warning';

		wp_send_json_success([
			'status'  => $omnify_status,
			'code'    => $omnify_code,
			'message' => $omnify_response_message,
			'latency' => $omnify_latency,
			'headers' => $omnify_headers,
		]);
	}

	public function ajax_test_stripe_connection(): void {
		check_ajax_referer('omnify_api_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Forbidden.', 'omnifywp-ecommerce')]);
		}

		$omnify_settings   = $this->omnify_settings->all();
		$omnify_mode       = $omnify_settings['stripe_mode'] ?? 'test';
		$omnify_secret_key = ('live' === $omnify_mode) ? ($omnify_settings['stripe_live_secret_key'] ?? '') : ($omnify_settings['stripe_test_secret_key'] ?? '');

		if (empty($omnify_secret_key)) {
			wp_send_json_error(['message' => __('Stripe secret key is not configured.', 'omnifywp-ecommerce')]);
		}

		$omnify_start    = microtime(true);
		$omnify_response = wp_safe_remote_get('https://api.stripe.com/v1/accounts?limit=1', [
			'timeout' => 15,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
			],
		]);
		$omnify_latency  = round((microtime(true) - $omnify_start) * 1000);

		if (is_wp_error($omnify_response)) {
			wp_send_json_error([
				'message' => $omnify_response->get_error_message(),
				'latency' => $omnify_latency,
			]);
		}

		$omnify_code = wp_remote_retrieve_response_code($omnify_response);
		$omnify_body = wp_remote_retrieve_body($omnify_response);
		$omnify_data = json_decode($omnify_body, true);

		if (200 === $omnify_code) {
			$omnify_account_id = $omnify_data['data'][0]['id'] ?? 'Unknown Account';
			wp_send_json_success([
				'status'     => 'success',
				'account_id' => $omnify_account_id,
				'latency'    => $omnify_latency,
				'mode'       => $omnify_mode,
			]);
		} else {
			$omnify_error_msg = $omnify_data['error']['message'] ?? __('Failed to authenticate with Stripe.', 'omnifywp-ecommerce');
			wp_send_json_error([
				'message' => sprintf('Stripe API error: %s (HTTP %d)', $omnify_error_msg, $omnify_code),
				'latency' => $omnify_latency,
			]);
		}
	}

	public function ajax_test_paypal_connection(): void {
		check_ajax_referer('omnify_api_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Forbidden.', 'omnifywp-ecommerce')]);
		}

		$omnify_settings  = $this->omnify_settings->all();
		$omnify_mode      = $omnify_settings['paypal_mode'] ?? 'sandbox';
		$omnify_client_id = ('live' === $omnify_mode) ? ($omnify_settings['paypal_live_client_id'] ?? '') : ($omnify_settings['paypal_sandbox_client_id'] ?? '');
		$omnify_secret    = ('live' === $omnify_mode) ? ($omnify_settings['paypal_live_secret'] ?? '') : ($omnify_settings['paypal_sandbox_secret'] ?? '');

		if (empty($omnify_client_id) || empty($omnify_secret)) {
			wp_send_json_error(['message' => __('PayPal client ID or secret is not configured.', 'omnifywp-ecommerce')]);
		}

		$omnify_url = ('live' === $omnify_mode) 
			? 'https://api-m.paypal.com/v1/oauth2/token' 
			: 'https://api-m.sandbox.paypal.com/v1/oauth2/token';

		$omnify_start    = microtime(true);
		$omnify_response = wp_safe_remote_post($omnify_url, [
			'timeout' => 15,
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode($omnify_client_id . ':' . $omnify_secret),
				'Content-Type'  => 'application/x-www-form-urlencoded',
			],
			'body'    => [
				'grant_type' => 'client_credentials',
			],
		]);
		$omnify_latency  = round((microtime(true) - $omnify_start) * 1000);

		if (is_wp_error($omnify_response)) {
			wp_send_json_error([
				'message' => $omnify_response->get_error_message(),
				'latency' => $omnify_latency,
			]);
		}

		$omnify_code = wp_remote_retrieve_response_code($omnify_response);
		$omnify_body = wp_remote_retrieve_body($omnify_response);
		$omnify_data = json_decode($omnify_body, true);

		if (200 === $omnify_code && ! empty($omnify_data['access_token'])) {
			wp_send_json_success([
				'status'  => 'success',
				'latency' => $omnify_latency,
				'mode'    => $omnify_mode,
			]);
		} else {
			$omnify_error_msg = $omnify_data['error_description'] ?? $omnify_data['error'] ?? __('Failed to authenticate with PayPal.', 'omnifywp-ecommerce');
			wp_send_json_error([
				'message' => sprintf('PayPal API error: %s (HTTP %d)', $omnify_error_msg, $omnify_code),
				'latency' => $omnify_latency,
			]);
		}
	}

	public function render_activity(): void {
		$omnify_settings = $this->omnify_settings->all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_page     = max(1, absint(wp_unslash($_GET['paged'] ?? 1)));
		$omnify_per_page = 20;
		$omnify_offset   = ($omnify_page - 1) * $omnify_per_page;

		$omnify_filter_args = [
			'per_page' => $omnify_per_page,
			'offset'   => $omnify_offset,
		];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['user_id'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$omnify_filter_args['user_id'] = absint(wp_unslash($_GET['user_id']));
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['action_type'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$omnify_filter_args['action'] = sanitize_key(wp_unslash($_GET['action_type']));
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['object_type'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$omnify_filter_args['object_type'] = sanitize_key(wp_unslash($_GET['object_type']));
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['search'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$omnify_filter_args['search'] = sanitize_text_field(wp_unslash($_GET['search']));
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['date_from'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$omnify_filter_args['date_from'] = sanitize_text_field(wp_unslash($_GET['date_from']));
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (! empty($_GET['date_to'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$omnify_filter_args['date_to'] = sanitize_text_field(wp_unslash($_GET['date_to']));
		}

		$omnify_activities = $this->omnify_activities->all($omnify_filter_args);
		$omnify_total_activities = $this->omnify_activities->count($omnify_filter_args);
		$omnify_total_pages = ceil($omnify_total_activities / $omnify_per_page);

		global $wpdb;
		$omnify_recent_activities = $this->omnify_activities->all(['per_page' => 1000]);
		$omnify_unique_actors = [];
		foreach ($omnify_recent_activities as $omnify_act) {
			if (! empty($omnify_act['user_id']) && ! isset($omnify_unique_actors[$omnify_act['user_id']])) {
				$omnify_unique_actors[$omnify_act['user_id']] = $omnify_act['actor_name'] ?: $omnify_act['actor_username'] ?: '#' . $omnify_act['user_id'];
			}
		}

		include OMNIFY_PATH . 'templates/admin/activity.php';
	}

	public function render_reviews(): void {
		$omnify_settings    = $this->omnify_settings->all();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status      = sanitize_key(wp_unslash($_GET['status'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_product_id  = absint(wp_unslash($_GET['product_id'] ?? 0));
		$omnify_reviews_repo = $this->omnify_reviews_repo;

		$omnify_review_args = ['per_page' => 200];
		if ($omnify_status)     $omnify_review_args['status']     = $omnify_status;
		if ($omnify_product_id) $omnify_review_args['product_id'] = $omnify_product_id;

		$omnify_reviews      = $this->omnify_reviews_repo->all($omnify_review_args);
		$omnify_status_counts = $this->omnify_reviews_repo->count_by_status();
		$omnify_products     = $this->omnify_products->all(['per_page' => 200]);

		include OMNIFY_PATH . 'templates/admin/reviews.php';
	}

	public function render_coupons(): void {
		$omnify_settings = $this->omnify_settings->all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_action = sanitize_key(wp_unslash($_GET['action'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_id     = absint(wp_unslash($_GET['id'] ?? 0));

		$omnify_coupon = [];
		if ('edit' === $omnify_action && $omnify_id) {
			$omnify_coupon = $this->omnify_coupons->find($omnify_id) ?: [];
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status = sanitize_key(wp_unslash($_GET['status'] ?? 'all'));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_search = sanitize_text_field(wp_unslash($_GET['search'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_type = sanitize_key(wp_unslash($_GET['type'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_from = sanitize_text_field(wp_unslash($_GET['date_from'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_date_to = sanitize_text_field(wp_unslash($_GET['date_to'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_sort = sanitize_key(wp_unslash($_GET['sort'] ?? ''));
		$omnify_coupons = $this->omnify_coupons->all([
			'status' => $omnify_status,
			'search' => $omnify_search,
			'type' => $omnify_type,
			'date_from' => $omnify_date_from,
			'date_to' => $omnify_date_to,
			'sort' => $omnify_sort,
		]);
		$omnify_products          = $this->omnify_products->all(['per_page' => 500]);
		$omnify_global_categories = get_option('omnify_global_categories', []);
		if (! is_array($omnify_global_categories)) {
			$omnify_global_categories = [];
		}

		$omnify_coupon_csv_values = [$this, 'coupon_csv_values'];
		include OMNIFY_PATH . 'templates/admin/coupons.php';
	}

	public function render_abandoned_carts(): void {
		$omnify_settings = $this->omnify_settings->all();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_status   = sanitize_key(wp_unslash($_GET['status'] ?? ''));
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_search   = sanitize_text_field(wp_unslash($_GET['search'] ?? ''));
		$omnify_carts    = $this->omnify_abandoned_carts->all([
			'status'   => $omnify_status,
			'search'   => $omnify_search,
			'per_page' => 200,
		]);
		$omnify_stats    = $this->omnify_abandoned_carts->stats();

		include OMNIFY_PATH . 'templates/admin/abandoned-carts.php';
	}

	private function calculate_trend(float $omnify_current, float $omnify_previous): float {
		if ($omnify_previous <= 0.0) {
			return $omnify_current > 0.0 ? 100.0 : 0.0;
		}
		return (($omnify_current - $omnify_previous) / $omnify_previous) * 100.0;
	}

	private function get_dashboard_stats(): array {
		global $wpdb;

		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
		$omnify_products_table = $wpdb->prefix . 'omnify_products';
		$omnify_downloads_table = $wpdb->prefix . 'omnify_downloads';

		$omnify_coupons_table = $wpdb->prefix . 'omnify_coupons';
		$omnify_reviews_table = $wpdb->prefix . 'omnify_reviews';

		$omnify_revenue_sql   = $wpdb->prepare("SELECT SUM(total) FROM %i WHERE status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')", $omnify_orders_table);
		$omnify_orders_sql    = $wpdb->prepare('SELECT COUNT(*) FROM %i', $omnify_orders_table);
		$omnify_customers_sql = $wpdb->prepare('SELECT COUNT(*) FROM %i', $omnify_customers_table);
		$omnify_downloads_sql = $wpdb->prepare('SELECT COUNT(*) FROM %i', $omnify_downloads_table);
		$omnify_products_sql  = $wpdb->prepare('SELECT COUNT(*) FROM %i', $omnify_products_table);

		$omnify_revenue   = (float) $this->cached_db_get_var('dashboard_revenue', $omnify_revenue_sql);
		$omnify_orders    = (int) $this->cached_db_get_var('dashboard_orders', $omnify_orders_sql);
		$omnify_customers = (int) $this->cached_db_get_var('dashboard_customers', $omnify_customers_sql);
		$omnify_downloads = (int) $this->cached_db_get_var('dashboard_downloads', $omnify_downloads_sql);
		$omnify_products  = (int) $this->cached_db_get_var('dashboard_products', $omnify_products_sql);

		// Trends (Current 30 Days vs Previous 30 Days)
		$omnify_now_time = current_time('timestamp');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$omnify_t_current_start = date('Y-m-d H:i:s', strtotime('-30 days', $omnify_now_time));
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$omnify_t_previous_start = date('Y-m-d H:i:s', strtotime('-60 days', $omnify_now_time));

		// Revenue trends
		$omnify_rev_curr_sql = $wpdb->prepare(
			"SELECT SUM(total) FROM %i WHERE status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested') AND created_at >= %s",
			$omnify_orders_table,
			$omnify_t_current_start
		);
		$omnify_rev_curr = (float) $this->cached_db_get_var('dashboard_rev_curr', $omnify_rev_curr_sql);
		$omnify_rev_prev_sql = $wpdb->prepare(
			"SELECT SUM(total) FROM %i WHERE status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested') AND created_at >= %s AND created_at < %s",
			$omnify_orders_table,
			$omnify_t_previous_start,
			$omnify_t_current_start
		);
		$omnify_rev_prev = (float) $this->cached_db_get_var('dashboard_rev_prev', $omnify_rev_prev_sql);

		// Orders trends
		$omnify_ord_curr_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE created_at >= %s',
			$omnify_orders_table,
			$omnify_t_current_start
		);
		$omnify_ord_curr = (int) $this->cached_db_get_var('dashboard_ord_curr', $omnify_ord_curr_sql);
		$omnify_ord_prev_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE created_at >= %s AND created_at < %s',
			$omnify_orders_table,
			$omnify_t_previous_start,
			$omnify_t_current_start
		);
		$omnify_ord_prev = (int) $this->cached_db_get_var('dashboard_ord_prev', $omnify_ord_prev_sql);

		// Customers trends
		$omnify_cust_curr_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE created_at >= %s',
			$omnify_customers_table,
			$omnify_t_current_start
		);
		$omnify_cust_curr = (int) $this->cached_db_get_var('dashboard_cust_curr', $omnify_cust_curr_sql);
		$omnify_cust_prev_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE created_at >= %s AND created_at < %s',
			$omnify_customers_table,
			$omnify_t_previous_start,
			$omnify_t_current_start
		);
		$omnify_cust_prev = (int) $this->cached_db_get_var('dashboard_cust_prev', $omnify_cust_prev_sql);

		// Downloads trends
		$omnify_dl_curr_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE downloaded_at >= %s',
			$omnify_downloads_table,
			$omnify_t_current_start
		);
		$omnify_dl_curr = (int) $this->cached_db_get_var('dashboard_dl_curr', $omnify_dl_curr_sql);
		$omnify_dl_prev_sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE downloaded_at >= %s AND downloaded_at < %s',
			$omnify_downloads_table,
			$omnify_t_previous_start,
			$omnify_t_current_start
		);
		$omnify_dl_prev = (int) $this->cached_db_get_var('dashboard_dl_prev', $omnify_dl_prev_sql);

		// Store Status active summaries
		$omnify_active_products_sql = $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE status = 'published'", $omnify_products_table);
		$omnify_active_coupons_sql  = $wpdb->prepare('SELECT COUNT(*) FROM %i WHERE is_active = 1', $omnify_coupons_table);
		$omnify_pending_reviews_sql = $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE status = 'pending'", $omnify_reviews_table);
		$omnify_active_products = (int) $this->cached_db_get_var('dashboard_active_products', $omnify_active_products_sql);
		$omnify_active_coupons  = (int) $this->cached_db_get_var('dashboard_active_coupons', $omnify_active_coupons_sql);
		$omnify_pending_reviews = (int) $this->cached_db_get_var('dashboard_pending_reviews', $omnify_pending_reviews_sql);

		return [
			'revenue'          => $omnify_revenue,
			'orders'           => $omnify_orders,
			'customers'        => $omnify_customers,
			'downloads'        => $omnify_downloads,
			'products'         => $omnify_products,
			'revenue_trend'    => $this->calculate_trend($omnify_rev_curr, $omnify_rev_prev),
			'orders_trend'     => $this->calculate_trend($omnify_ord_curr, $omnify_ord_prev),
			'customers_trend'  => $this->calculate_trend($omnify_cust_curr, $omnify_cust_prev),
			'downloads_trend'  => $this->calculate_trend($omnify_dl_curr, $omnify_dl_prev),
			'active_products'  => $omnify_active_products,
			'active_coupons'   => $omnify_active_coupons,
			'pending_reviews'  => $omnify_pending_reviews,
		];
	}

	private function get_most_downloaded_products(int $omnify_limit = 10): array {
		global $wpdb;

		$omnify_downloads_table = $wpdb->prefix . 'omnify_downloads';
		$omnify_products_table = $wpdb->prefix . 'omnify_products';

		$omnify_sql = "
			SELECT
				p.id,
				p.name,
				p.price,
				p.currency,
				COUNT(d.id) as download_count
			FROM {$omnify_downloads_table} d
			JOIN {$omnify_products_table} p ON d.product_id = p.id
			GROUP BY p.id
			ORDER BY download_count DESC
			LIMIT %d
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_prepared_sql = $wpdb->prepare($omnify_sql, $omnify_limit);
		$omnify_rows = $this->cached_db_get_results('dashboard_most_downloaded_products', $omnify_prepared_sql);

		return is_array($omnify_rows) ? $omnify_rows : [];
	}

	public function maybe_clear_external_notices(): void {
		if ($this->is_omnify_screen()) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
			remove_all_actions('user_admin_notices');
			remove_all_actions('network_admin_notices');
			// Re-add our display notices and setup notice
			add_action('admin_notices', [$this, 'display_notices']);
			add_action('admin_notices', [$this, 'admin_notices']);
		}
	}

	public function display_notices(): void {
		if (! $this->is_omnify_screen()) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_message = sanitize_key(wp_unslash($_GET['message'] ?? ''));
		if (!$omnify_message) {
			return;
		}

		$omnify_class = 'notice notice-success is-dismissible omnify-notice';
		if (in_array($omnify_message, ['coupon_exists', 'refund_amount_zero', 'refund_failed', 'order_not_found', 'abandoned_cart_not_found', 'pages_generate_failed', 'save_failed', 'product_title_required', 'product_description_required', 'user_exists', 'create_failed', 'missing_fields', 'user_not_found', 'admin_protected'], true)) {
			$omnify_class = 'notice notice-error is-dismissible omnify-notice';
		}
		$omnify_text = '';

		switch ($omnify_message) {
			case 'saved':
				$omnify_text = __('Settings / Details saved successfully.', 'omnifywp-ecommerce');
				break;
			case 'save_failed':
				$omnify_text = __('Settings could not be saved. Please try again.', 'omnifywp-ecommerce');
				break;
			case 'product_title_required':
				$omnify_text = __('Product title is required. Please add a title before saving.', 'omnifywp-ecommerce');
				break;
			case 'product_description_required':
				$omnify_text = __('Product description is required. Please add the full product description before saving.', 'omnifywp-ecommerce');
				break;
			case 'activity_log_cleared':
				$omnify_text = __('Admin activity log cleared successfully.', 'omnifywp-ecommerce');
				break;
			case 'deleted':
				$omnify_text = __('Deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'note_added':
				$omnify_text = __('Note added successfully.', 'omnifywp-ecommerce');
				break;
			case 'note_deleted':
				$omnify_text = __('Note deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'access_granted':
				$omnify_text = __('Product access granted successfully.', 'omnifywp-ecommerce');
				break;
			case 'access_revoked':
				$omnify_text = __('Product access revoked successfully.', 'omnifywp-ecommerce');
				break;
			case 'receipt_resent':
				$omnify_text = __('Receipt email resent successfully.', 'omnifywp-ecommerce');
				break;
			case 'refund_processed':
				$omnify_text = __('Refund processed successfully.', 'omnifywp-ecommerce');
				break;
			case 'refund_failed':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$omnify_text = isset($_GET['err']) ? sanitize_text_field(wp_unslash($_GET['err'])) : __('Refund failed.', 'omnifywp-ecommerce');
				break;
			case 'refund_amount_zero':
				$omnify_text = __('Refund failed: Refund amount must be greater than zero.', 'omnifywp-ecommerce');
				break;
			case 'order_not_found':
				$omnify_text = __('Order not found.', 'omnifywp-ecommerce');
				break;
			case 'order_fulfilled':
				$omnify_text = __('Order fulfillment status updated successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_marked_paid':
				$omnify_text = __('Order marked as paid.', 'omnifywp-ecommerce');
				break;
			case 'order_approved_risk':
				$omnify_text = __('Order approved; fraud flags cleared.', 'omnifywp-ecommerce');
				break;
			case 'order_note_added':
				$omnify_text = __('Order note added successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_note_deleted':
				$omnify_text = __('Order note deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'pages_generated':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$omnify_count = absint(wp_unslash($_GET['count'] ?? 0));
				if ($omnify_count > 0) {
					// translators: %d: placeholder value.
					$omnify_text = sprintf(_n('%d page generated successfully.', '%d pages generated successfully.', $omnify_count, 'omnifywp-ecommerce'), $omnify_count);
				} else {
					$omnify_text = __('All required pages already exist.', 'omnifywp-ecommerce');
				}
				break;
			case 'pages_generate_failed':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$omnify_failed = absint(wp_unslash($_GET['failed'] ?? 0));
				if ($omnify_failed > 0) {
					// translators: %d: placeholder value.
					$omnify_text = sprintf(_n('%d required page could not be generated. Please try again or create it manually.', '%d required pages could not be generated. Please try again or create them manually.', $omnify_failed, 'omnifywp-ecommerce'), $omnify_failed);
				} else {
					$omnify_text = __('Required pages could not be generated. Please try again.', 'omnifywp-ecommerce');
				}
				break;
			case 'coupon_exists':
				$omnify_text = __('A coupon with this code already exists.', 'omnifywp-ecommerce');
				break;
			case 'bulk_trash':
			case 'bulk_restore':
			case 'bulk_mark_completed':
			case 'bulk_mark_cancelled':
				$omnify_text = __('Bulk action completed.', 'omnifywp-ecommerce');
				break;
			case 'no_selection':
				$omnify_text = __('No items selected.', 'omnifywp-ecommerce');
				$omnify_class = 'notice notice-error is-dismissible omnify-notice';
				break;
			case 'coupon_saved':
				$omnify_text = __('Coupon saved successfully.', 'omnifywp-ecommerce');
				break;
			case 'coupon_deleted':
				$omnify_text = __('Coupon deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_status_updated':
				$omnify_text = __('Order status updated successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_deleted':
				$omnify_text = __('Order deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_cancelled':
				$omnify_text = __('Order cancelled successfully.', 'omnifywp-ecommerce');
				break;
			case 'order_returned':
				$omnify_text = __('Return flow completed successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_cat_added':
				$omnify_text = __('Global category added successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_cat_deleted':
				$omnify_text = __('Global category deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_tag_added':
				$omnify_text = __('Global tag added successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_tag_deleted':
				$omnify_text = __('Global tag deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_attr_added':
				$omnify_text = __('Global attribute added successfully.', 'omnifywp-ecommerce');
				break;
			case 'global_attr_deleted':
				$omnify_text = __('Global attribute deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'review_approved':
				$omnify_text = __('Review approved and now visible on storefront.', 'omnifywp-ecommerce');
				break;
			case 'review_rejected':
				$omnify_text = __('Review rejected and hidden from storefront.', 'omnifywp-ecommerce');
				break;
			case 'review_deleted':
				$omnify_text = __('Review deleted permanently.', 'omnifywp-ecommerce');
				break;
			case 'abandoned_cart_deleted':
				$omnify_text = __('Abandoned cart deleted successfully.', 'omnifywp-ecommerce');
				break;
			case 'abandoned_cart_email_sent':
				$omnify_text = __('Recovery email sent successfully.', 'omnifywp-ecommerce');
				break;
			case 'abandoned_cart_not_found':
				$omnify_text = __('Abandoned cart not found.', 'omnifywp-ecommerce');
				break;
			case 'receipt_failed':
				$omnify_class = 'notice notice-error omnify-notice';
				$omnify_text = __('Receipt email could not be sent. Check the email log for delivery details.', 'omnifywp-ecommerce');
				break;
			case 'email_test_sent':
				$omnify_text = __('Test email sent successfully.', 'omnifywp-ecommerce');
				break;
			case 'email_test_failed':
				$omnify_class = 'notice notice-error omnify-notice';
				$omnify_text = __('Test email failed. Check the email log for the mailer error.', 'omnifywp-ecommerce');
				break;
			case 'email_resent':
				$omnify_text = __('Logged email resent successfully.', 'omnifywp-ecommerce');
				break;
			case 'email_resend_failed':
				$omnify_class = 'notice notice-error omnify-notice';
				$omnify_text = __('Logged email could not be resent. Check the latest email log entry for details.', 'omnifywp-ecommerce');
				break;
			case 'user_added':
				$omnify_text = __('Shop user created successfully.', 'omnifywp-ecommerce');
				break;
			case 'user_removed':
				$omnify_text = __('Shop user access removed successfully.', 'omnifywp-ecommerce');
				break;
			case 'user_exists':
				$omnify_text = __('A user with this username or email already exists.', 'omnifywp-ecommerce');
				break;
			case 'create_failed':
				$omnify_text = __('Failed to create shop user.', 'omnifywp-ecommerce');
				break;
			case 'missing_fields':
				$omnify_text = __('All fields are required to create a user.', 'omnifywp-ecommerce');
				break;
			case 'user_not_found':
				$omnify_text = __('Specified user not found.', 'omnifywp-ecommerce');
				break;
			case 'admin_protected':
				$omnify_text = __('Administrators cannot be modified or removed from this screen.', 'omnifywp-ecommerce');
				break;
		}

		if ($omnify_text) {
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($omnify_class), esc_html($omnify_text));
		}
	}

	public function handle_save_product(): void {
		check_admin_referer('omnify_save_product', 'omnify_product_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_id = absint(wp_unslash($_POST['id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_product_name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
		if ('' === $omnify_product_name) {
			$omnify_redirect_args = [
				'page'    => 'omnify-products',
				'action'  => $omnify_id ? 'edit' : 'new',
				'message' => 'product_title_required',
			];
			if ($omnify_id) {
				$omnify_redirect_args['id'] = $omnify_id;
			}
			wp_safe_redirect(add_query_arg($omnify_redirect_args, admin_url('admin.php')));
			exit;
		}
		$omnify_product_description = wp_kses_post(wp_unslash((string) wp_unslash($_POST['description'] ?? '')));
		if ('' === trim(wp_strip_all_tags($omnify_product_description))) {
			$omnify_redirect_args = [
				'page'        => 'omnify-products',
				'action'      => $omnify_id ? 'edit' : 'new',
				'message'     => 'product_description_required',
				'wizard_step' => 3,
			];
			if ($omnify_id) {
				$omnify_redirect_args['id'] = $omnify_id;
			}
			wp_safe_redirect(add_query_arg($omnify_redirect_args, admin_url('admin.php')));
			exit;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_short_description = wp_kses_post(wp_unslash((string) wp_unslash($_POST['short_description'] ?? '')));

		// Parse categories and tags
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_categories = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['categories'] ?? '')))));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_tags = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['tags'] ?? '')))));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_brands = array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['brands'] ?? '')))));

		// Parse attributes
		$omnify_posted_attributes = isset($_POST['attributes']) && is_array($_POST['attributes'])
			? wp_unslash($_POST['attributes']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: [];
		$omnify_attributes = [];
		foreach ($omnify_posted_attributes as $omnify_attr) {
			if (! is_array($omnify_attr)) {
				continue;
			}
			$omnify_name = sanitize_text_field($omnify_attr['name'] ?? '');
			$omnify_options = array_filter(array_map('trim', explode(',', sanitize_text_field($omnify_attr['options'] ?? ''))));
			if ('' !== $omnify_name && ! empty($omnify_options)) {
				$omnify_type = sanitize_key($omnify_attr['type'] ?? 'button');
				if (! in_array($omnify_type, ['button', 'dropdown', 'color', 'image'], true)) {
					$omnify_type = 'button';
				}
				$omnify_option_meta = [];
				$omnify_raw_option_meta = [];
				if (! empty($omnify_attr['option_meta_json'])) {
					$omnify_decoded_meta = json_decode((string) $omnify_attr['option_meta_json'], true);
					if (is_array($omnify_decoded_meta)) {
						$omnify_raw_option_meta = map_deep($omnify_decoded_meta, 'sanitize_text_field');
					}
				} elseif (isset($omnify_attr['option_meta']) && is_array($omnify_attr['option_meta'])) {
					$omnify_raw_option_meta = map_deep($omnify_attr['option_meta'], 'sanitize_text_field');
				}
				foreach ($omnify_options as $omnify_option_label) {
					$omnify_raw_meta = is_array($omnify_raw_option_meta[$omnify_option_label] ?? null) ? $omnify_raw_option_meta[$omnify_option_label] : [];
					$omnify_option_meta[$omnify_option_label] = [
						'color'     => sanitize_hex_color($omnify_raw_meta['color'] ?? '') ?: '',
						'image_id'  => absint($omnify_raw_meta['image_id'] ?? 0),
						'image_url' => esc_url_raw($omnify_raw_meta['image_url'] ?? ''),
					];
				}
				$omnify_attributes[] = [
					'name'        => $omnify_name,
					'type'        => $omnify_type,
					'options'     => array_values($omnify_options),
					'option_meta' => $omnify_option_meta,
				];
			}
		}

		// Parse variations
		$omnify_raw_variations = isset($_POST['variations']) && is_array($_POST['variations'])
			? map_deep(wp_unslash($_POST['variations']), 'sanitize_text_field')
			: [];
		$omnify_variations = [];
		foreach ($omnify_raw_variations as $omnify_var) {
			if (! is_array($omnify_var)) {
				continue;
			}
			$omnify_var_attrs = [];
			if (isset($omnify_var['attributes']) && is_array($omnify_var['attributes'])) {
				foreach ($omnify_var['attributes'] as $omnify_attr_name => $omnify_attr_val) {
					$omnify_var_attrs[sanitize_text_field((string) $omnify_attr_name)] = sanitize_text_field((string) $omnify_attr_val);
				}
			}
			$omnify_variations[] = [
				'id'           => isset($omnify_var['id']) ? absint($omnify_var['id']) : 0,
				'sku'          => sanitize_text_field($omnify_var['sku'] ?? ''),
				'price'        => (float) ($omnify_var['price'] ?? 0.0),
				'sale_price'   => isset($omnify_var['sale_price']) && '' !== trim((string)$omnify_var['sale_price']) ? (float) $omnify_var['sale_price'] : null,
				'attributes'   => $omnify_var_attrs,
				'description'  => sanitize_textarea_field($omnify_var['description'] ?? ''),
				'manage_stock' => ! empty($omnify_var['manage_stock']) ? 1 : 0,
				'stock_qty'    => isset($omnify_var['stock_qty']) && '' !== trim((string)$omnify_var['stock_qty']) ? (int) $omnify_var['stock_qty'] : null,
				'stock_status' => sanitize_key($omnify_var['stock_status'] ?? 'instock'),
				'allow_backorders' => ! empty($omnify_var['allow_backorders']) ? 1 : 0,
				'preorder_enabled' => ! empty($omnify_var['preorder_enabled']) ? 1 : 0,
				'preorder_release_date' => sanitize_text_field((string) ($omnify_var['preorder_release_date'] ?? '')),
				'preorder_limit' => isset($omnify_var['preorder_limit']) && '' !== trim((string) $omnify_var['preorder_limit']) ? absint($omnify_var['preorder_limit']) : null,
				'preorder_message' => sanitize_text_field((string) ($omnify_var['preorder_message'] ?? '')),
				'thumbnail_id' => isset($omnify_var['thumbnail_id']) ? absint($omnify_var['thumbnail_id']) : null,
			];
		}

		// Gallery IDs
		$omnify_raw_gallery = isset($_POST['gallery_ids']) && is_array($_POST['gallery_ids']) ? (array) wp_unslash($_POST['gallery_ids']) : [];
		$omnify_gallery_ids = array_values(array_filter(array_map('absint', $omnify_raw_gallery)));
		$omnify_variation_product_kind = sanitize_key(wp_unslash($_POST['variation_product_kind'] ?? 'digital'));
		if (! in_array($omnify_variation_product_kind, ['digital', 'physical', 'mixed'], true)) {
			$omnify_variation_product_kind = 'digital';
		}
		$omnify_variation_selector_style = sanitize_key(wp_unslash($_POST['variation_selector_style'] ?? 'buttons'));
		if (! in_array($omnify_variation_selector_style, ['buttons', 'dropdowns', 'swatches'], true)) {
			$omnify_variation_selector_style = 'buttons';
		}
		$omnify_variation_swatch_shape = sanitize_key(wp_unslash($_POST['variation_swatch_shape'] ?? 'round'));
		if (! in_array($omnify_variation_swatch_shape, ['round', 'square'], true)) {
			$omnify_variation_swatch_shape = 'round';
		}
		$omnify_variation_swatch_size = sanitize_key(wp_unslash($_POST['variation_swatch_size'] ?? 'medium'));
		if (! in_array($omnify_variation_swatch_size, ['small', 'medium', 'large'], true)) {
			$omnify_variation_swatch_size = 'medium';
		}
		$omnify_variation_out_of_stock = sanitize_key(wp_unslash($_POST['variation_out_of_stock'] ?? 'cross'));
		if (! in_array($omnify_variation_out_of_stock, ['cross', 'blur', 'hide'], true)) {
			$omnify_variation_out_of_stock = 'cross';
		}
		$omnify_product_kind = sanitize_key(wp_unslash($_POST['product_kind'] ?? $omnify_variation_product_kind));
		if (! in_array($omnify_product_kind, ['digital', 'physical', 'bundle'], true)) {
			$omnify_product_kind = 'digital';
		}
		$omnify_product_structure = sanitize_key(wp_unslash($_POST['product_structure'] ?? 'simple'));
		if (! in_array($omnify_product_structure, ['simple', 'variable'], true)) {
			$omnify_product_structure = 'simple';
		}
		$omnify_product_type = 'variable' === $omnify_product_structure ? 'variable' : ('bundle' === $omnify_product_kind ? 'bundle' : ('physical' === $omnify_product_kind ? 'physical' : 'download'));
		$omnify_variation_product_kind = $omnify_product_kind;

		$omnify_data = [
			'name'         => $omnify_product_name,
			'slug'              => sanitize_title(wp_unslash($_POST['slug'] ?? '')),
			'price'             => (float) wp_unslash($_POST['price'] ?? 0),
			'sale_price'        => isset($_POST['sale_price']) && '' !== trim((string) wp_unslash($_POST['sale_price'])) ? (float) wp_unslash($_POST['sale_price']) : null,
			'status'            => sanitize_key(wp_unslash($_POST['status'] ?? 'draft')),
			'type'              => $omnify_product_type,
			'description'       => $omnify_product_description,
			'short_description' => $omnify_short_description,
			'thumbnail_id'      => absint(wp_unslash($_POST['thumbnail_id'] ?? 0)),
			'categories'        => $omnify_categories,
			'tags'              => $omnify_tags,
			'sku'               => sanitize_text_field(wp_unslash($_POST['sku'] ?? '')),
			'manage_stock'      => ! empty($_POST['manage_stock']) ? 1 : 0,
			'stock_qty'         => isset($_POST['stock_qty']) && '' !== trim((string) wp_unslash($_POST['stock_qty'])) ? (int) wp_unslash($_POST['stock_qty']) : null,
			'stock_status'      => sanitize_key(wp_unslash($_POST['stock_status'] ?? 'instock')),
			'allow_backorders'  => ! empty($_POST['allow_backorders']) ? 1 : 0,
			'preorder_enabled'  => ! empty($_POST['preorder_enabled']) ? 1 : 0,
			'preorder_release_date' => sanitize_text_field((string) wp_unslash($_POST['preorder_release_date'] ?? '')),
			'preorder_limit'    => isset($_POST['preorder_limit']) && '' !== trim((string) wp_unslash($_POST['preorder_limit'])) ? absint(wp_unslash($_POST['preorder_limit'])) : null,
			'preorder_message'  => sanitize_text_field((string) wp_unslash($_POST['preorder_message'] ?? '')),
			'weight'            => isset($_POST['weight']) && '' !== trim((string) wp_unslash($_POST['weight'])) ? (float) wp_unslash($_POST['weight']) : null,
			'length'            => isset($_POST['length']) && '' !== trim((string) wp_unslash($_POST['length'])) ? (float) wp_unslash($_POST['length']) : null,
			'width'             => isset($_POST['width']) && '' !== trim((string) wp_unslash($_POST['width'])) ? (float) wp_unslash($_POST['width']) : null,
			'height'            => isset($_POST['height']) && '' !== trim((string) wp_unslash($_POST['height'])) ? (float) wp_unslash($_POST['height']) : null,
			'shipping_class'    => sanitize_title((string) wp_unslash($_POST['shipping_class'] ?? '')),
			'attributes'        => $omnify_attributes,
			'variation_settings' => [
				'product_kind'   => $omnify_variation_product_kind,
				'selector_style' => $omnify_variation_selector_style,
				'swatch_shape'   => $omnify_variation_swatch_shape,
				'swatch_size'    => $omnify_variation_swatch_size,
				'out_of_stock'   => $omnify_variation_out_of_stock,
			],
			'gallery_ids'       => $omnify_gallery_ids,
			'video_url'         => esc_url_raw(sanitize_text_field(wp_unslash($_POST['video_url'] ?? ''))),
			'trust_badge_title' => sanitize_text_field(wp_unslash($_POST['trust_badge_title'] ?? '')),
			'trust_badge_text'  => sanitize_textarea_field(wp_unslash($_POST['trust_badge_text'] ?? '')),
			'download_expiry_days' => absint(wp_unslash($_POST['download_expiry_days'] ?? 0)),
			'max_purchase_qty'  => isset($_POST['max_purchase_qty']) && '' !== trim((string) wp_unslash($_POST['max_purchase_qty'])) ? absint(wp_unslash($_POST['max_purchase_qty'])) : null,
			'bundled_ids'       => isset($_POST['bundled_ids']) && is_array($_POST['bundled_ids']) ? array_map('absint', (array) wp_unslash($_POST['bundled_ids'])) : [],
			'upsell_ids'        => isset($_POST['upsell_ids']) && is_array($_POST['upsell_ids']) ? array_map('absint', (array) wp_unslash($_POST['upsell_ids'])) : [],
			'cross_sell_ids'    => isset($_POST['cross_sell_ids']) && is_array($_POST['cross_sell_ids']) ? array_map('absint', (array) wp_unslash($_POST['cross_sell_ids'])) : [],
			'brands'            => $omnify_brands,
			'refund_enabled' => ! empty($_POST['refund_enabled']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'refund_window_days' => isset($_POST['refund_window_days']) && '' !== trim((string) wp_unslash($_POST['refund_window_days'])) ? absint(wp_unslash($_POST['refund_window_days'])) : null,
			'refund_policy_text' => sanitize_textarea_field(wp_unslash($_POST['refund_policy_text'] ?? '')),
			'delivery_info'      => sanitize_textarea_field(wp_unslash($_POST['delivery_info'] ?? '')),
			'return_info'        => sanitize_textarea_field(wp_unslash($_POST['return_info'] ?? '')),
			'specifications'     => isset($_POST['specifications']) && is_array($_POST['specifications'])
				? array_values(array_filter(array_map(static function(array $omnify_spec): array {
					return [
						'key'   => sanitize_text_field($omnify_spec['key'] ?? ''),
						'value' => sanitize_text_field($omnify_spec['value'] ?? ''),
					];
				}, (array) map_deep(wp_unslash($_POST['specifications']), 'sanitize_text_field')), static function(array $omnify_spec): bool {
					return '' !== $omnify_spec['key'] || '' !== $omnify_spec['value'];
				}))
				: [],
		];

		if ($omnify_id) {
			$this->omnify_products->update($omnify_id, $omnify_data);
			$omnify_product_id = $omnify_id;
		} else {
			$omnify_product_id = $this->omnify_products->create($omnify_data);
		}

		if ('variable' === $omnify_data['type']) {
			$this->omnify_products->save_variations($omnify_product_id, $omnify_variations);
		}

		// Process multiple file uploads if present
		$omnify_accepts_downloads = 'download' === $omnify_data['type'] || ('variable' === $omnify_data['type'] && 'physical' !== $omnify_variation_product_kind);
		if ($omnify_product_id && $omnify_accepts_downloads && ! empty($_FILES['product_files']) && is_array($_FILES['product_files'])) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$omnify_files = (array) wp_unslash($_FILES['product_files']);
			$omnify_version = isset($_POST['file_version']) ? sanitize_text_field(wp_unslash($_POST['file_version'])) : '1.0.0';

			if (! empty($omnify_files['name']) && is_array($omnify_files['name'])) {
				$omnify_total_files = count($omnify_files['name']);
				for ($omnify_i = 0; $omnify_i < $omnify_total_files; $omnify_i++) {
					$omnify_err = isset($omnify_files['error'][$omnify_i]) ? (int) $omnify_files['error'][$omnify_i] : UPLOAD_ERR_NO_FILE;
					$omnify_tmp = isset($omnify_files['tmp_name'][$omnify_i]) ? (string) $omnify_files['tmp_name'][$omnify_i] : '';
					if (UPLOAD_ERR_OK === $omnify_err && is_uploaded_file($omnify_tmp)) {
						$omnify_raw_name = (string) ($omnify_files['name'][$omnify_i] ?? '');
						if ('' === $omnify_raw_name) {
							continue;
						}
						$omnify_name = sanitize_file_name($omnify_raw_name);
						$omnify_type = sanitize_mime_type((string) ($omnify_files['type'][$omnify_i] ?? ''));
						$omnify_file_array = [
							'name'     => $omnify_name,
							'type'     => $omnify_type,
							'tmp_name' => $omnify_tmp,
							'error'    => $omnify_err,
							'size'     => isset($omnify_files['size'][$omnify_i]) ? (int) $omnify_files['size'][$omnify_i] : 0,
						];

						$omnify_upload = wp_handle_upload($omnify_file_array, ['test_form' => false]);
						if (empty($omnify_upload['error'])) {
							$omnify_upload['name'] = $omnify_name;
							$this->omnify_product_files->create_from_upload($omnify_product_id, $omnify_upload, $omnify_version);
						}
					}
				}
			}
		}

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			$omnify_id ? 'edit_product' : 'create_product',
			'product',
			(string) $omnify_product_id,
			// translators: %1$s: placeholder value, %2$d: placeholder value.
			// translators: %1$s: placeholder value, %2$d: placeholder value.
			$omnify_id ? sprintf(__('Updated product "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_product_name, $omnify_product_id) : sprintf(__('Created product "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_product_name, $omnify_product_id)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product_id . '&message=saved'));
		exit;
	}

	public function handle_delete_product(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_delete_product_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_product = $this->omnify_products->find($omnify_id);
		$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';

		$this->omnify_products->delete($omnify_id);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'delete_product',
			'product',
			(string) $omnify_id,
			// translators: %1$s: placeholder value, %2$d: placeholder value.
			sprintf(__('Deleted product "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_product_name ?: 'Unknown', $omnify_id)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=deleted'));
		exit;
	}

	public function handle_trash_product(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_product_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_product = $this->omnify_products->find($omnify_id);
		$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';

		do_action('omnify_before_admin_trash_product', $omnify_id, $omnify_product);
		$this->omnify_products->trash($omnify_id);
		do_action('omnify_after_admin_trash_product', $omnify_id, $omnify_product);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'trash_product',
			'product',
			(string) $omnify_id,
			// translators: %1$s: placeholder value, %2$d: placeholder value.
			sprintf(__('Moved product "%1$s" (ID: %2$d) to trash.', 'omnifywp-ecommerce'), $omnify_product_name ?: 'Unknown', $omnify_id)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=trashed'));
		exit;
	}

	public function handle_restore_product(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_product_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		do_action('omnify_before_admin_restore_product', $omnify_id);
		$this->omnify_products->restore($omnify_id);
		do_action('omnify_after_admin_restore_product', $omnify_id);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'restore_product',
			'product',
			(string) $omnify_id,
			// translators: %d: placeholder value.
			sprintf(__('Restored product ID: %d from trash.', 'omnifywp-ecommerce'), $omnify_id)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=restored'));
		exit;
	}

	public function handle_trash_customer(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_customer_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_customers->trash($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&message=trashed')); exit;
	}
	public function handle_restore_customer(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_customer_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_customers->restore($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&message=restored')); exit;
	}

	public function handle_quick_edit_product(): void {
		check_admin_referer('omnify_quick_edit_product', 'omnify_quick_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_id = absint(wp_unslash($_POST['id'] ?? 0));
		if (! $omnify_id) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=error'));
			exit;
		}

		$omnify_data = [
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'price' => (float) wp_unslash($_POST['price'] ?? 0),
			'sale_price' => isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float) wp_unslash($_POST['sale_price']): null,
			'status' => sanitize_key(wp_unslash($_POST['status'] ?? 'draft')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'sku' => sanitize_text_field(wp_unslash($_POST['sku'] ?? '')),
		];

		// Update stock if provided
		if (isset($_POST['stock_qty'])) {
			$omnify_data['stock_qty'] = isset($_POST['stock_qty']) && $_POST['stock_qty'] !== '' ? (int) wp_unslash($_POST['stock_qty']): null;
		}
		if (isset($_POST['manage_stock'])) {
			$omnify_data['manage_stock'] = ! empty($_POST['manage_stock']) ? 1 : 0;
		}

		do_action('omnify_before_admin_quick_edit_product', $omnify_id, $omnify_data);
		$this->omnify_products->update($omnify_id, $omnify_data);
		do_action('omnify_after_admin_quick_edit_product', $omnify_id, $omnify_data);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'quick_edit_product',
			'product',
			(string) $omnify_id,
			// translators: %d: placeholder value.
			sprintf(__('Quick edited product #%d.', 'omnifywp-ecommerce'), $omnify_id)
		);

		// If AJAX, return JSON
		if (wp_doing_ajax() || ! empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			wp_send_json_success(['message' => __('Product updated.', 'omnifywp-ecommerce')]);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=quick_saved'));
		exit;
	}

	public function handle_trash_order(): void {
		$omnify_id = absint(wp_unslash($_GET['order_id'] ?? $_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_order_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_orders->trash($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=trashed')); exit;
	}
	public function handle_restore_order(): void {
		$omnify_id = absint(wp_unslash($_GET['order_id'] ?? $_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_order_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_orders->restore($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=restored')); exit;
	}

	public function handle_bulk_orders(): void {
		check_admin_referer('omnify_bulk_orders', 'omnify_bulk_nonce');
		if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }

		$omnify_ids = array_map('absint', (array) wp_unslash($_POST['order_ids'] ?? []));
		$omnify_action = sanitize_key(wp_unslash($_POST['bulk_action'] ?? ''));
		if (empty($omnify_ids) || empty($omnify_action)) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=no_selection')); exit;
		}

		foreach ($omnify_ids as $omnify_id) {
			switch ($omnify_action) {
				case 'trash':
					$this->omnify_orders->trash($omnify_id);
					break;
				case 'restore':
					$this->omnify_orders->restore($omnify_id);
					break;
				case 'mark_completed':
					$this->omnify_orders->update_status($omnify_id, 'completed');
					$this->grant_order_download_access($omnify_id);
					do_action('omnify_order_completed', $omnify_id);
					break;
				case 'mark_shipped':
					$this->omnify_orders->update_status($omnify_id, 'shipped');
					$this->omnify_orders->update_fulfillment($omnify_id, 'fulfilled');
					break;
				case 'mark_processing':
					$this->omnify_orders->update_status($omnify_id, 'processing');
					break;
				case 'mark_cancelled':
					$this->omnify_orders->update_status($omnify_id, 'cancelled');
					break;
				case 'fulfill':
					$this->omnify_orders->update_fulfillment($omnify_id, 'fulfilled');
					break;
				case 'packing_slips':
					// For multiple, we'll redirect to first or use session, for now mark and note
					$this->omnify_orders->update_fulfillment($omnify_id, 'fulfilled');
					// To support bulk print, we can use a transient list or just open one by one; for deep, we'll handle by redirect with ids or just process.
					break;
			}
		}

		$omnify_msg = 'bulk_' . $omnify_action;
		if ($omnify_action === 'packing_slips' && !empty($omnify_ids)) {
			// Render combined packing slips for print/PDF
			nocache_headers();
			header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
			wp_enqueue_style('omnify-order-print', OMNIFY_URL . 'assets/admin/order-print.css', [], OMNIFY_VERSION);
			?>
			<!doctype html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="<?php bloginfo('charset'); ?>" />
				<title><?php esc_html_e('Packing Slips', 'omnifywp-ecommerce'); ?></title>
				<?php wp_print_styles(['omnify-order-print']); ?>
			</head>
			<body>
			<div class="toolbar"><button type="button" onclick="window.print()"><?php esc_html_e('Print / Save PDF', 'omnifywp-ecommerce'); ?></button></div>
			<?php
			foreach ($omnify_ids as $omnify_pid) {
				$omnify_o = $this->omnify_orders->find($omnify_pid);
				if ($omnify_o) {
					echo '<div class="slip">';
					echo '<h2>' . esc_html__('Packing Slip - Order #', 'omnifywp-ecommerce') . esc_html($omnify_o['order_number'] ?: $omnify_o['id']) . '</h2>';
					echo '<p>' . esc_html__('Ship to: ', 'omnifywp-ecommerce') . esc_html(trim(($omnify_o['shipping_first_name'] ?? '') . ' ' . ($omnify_o['shipping_last_name'] ?? ''))) . '</p>';
					echo '<ul>';
					foreach ((array)($omnify_o['items'] ?? []) as $omnify_it) {
						echo '<li>' . esc_html($omnify_it['product_name']) . ' x ' . (int)$omnify_it['quantity'] . '</li>';
					}
					echo '</ul>';
					echo '</div>';
				}
			}
			?>
			</body>
			</html>
			<?php
			exit;
		}
		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=' . $omnify_msg)); exit;
	}

	public function handle_quick_update_order_status(): void {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized', 'omnifywp-ecommerce'));
		}

		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		$omnify_new_status = sanitize_key(wp_unslash($_POST['status'] ?? ''));
		check_admin_referer('omnify_quick_order_status_' . $omnify_order_id, 'omnify_quick_nonce');

		if ($omnify_order_id && $omnify_new_status) {
			$this->omnify_orders->update_status($omnify_order_id, $omnify_new_status);
			if ($omnify_new_status === 'completed') {
				$this->grant_order_download_access($omnify_order_id);
				do_action('omnify_order_completed', $omnify_order_id);
			}
		}

		if (wp_doing_ajax()) {
			wp_send_json_success();
		}
		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=status_updated'));
		exit;
	}

	public function handle_bulk_customers(): void {
		check_admin_referer('omnify_bulk_customers', 'omnify_bulk_nonce');
		if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }

		$omnify_ids = array_map('absint', (array) wp_unslash($_POST['customer_ids'] ?? []));
		$omnify_action = sanitize_key(wp_unslash($_POST['bulk_action'] ?? ''));
		if (empty($omnify_ids) || empty($omnify_action)) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-customers&message=no_selection')); exit;
		}

		foreach ($omnify_ids as $omnify_id) {
			if ($omnify_action === 'trash') {
				$this->omnify_customers->trash($omnify_id);
			} elseif ($omnify_action === 'restore') {
				$this->omnify_customers->restore($omnify_id);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&message=bulk_' . $omnify_action)); exit;
	}

	public function handle_bulk_products(): void {
		check_admin_referer('omnify_bulk_products', 'omnify_bulk_nonce');
		if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }

		$omnify_ids = array_map('absint', (array) wp_unslash($_POST['product_ids'] ?? []));
		$omnify_action = sanitize_key(wp_unslash($_POST['bulk_action'] ?? ''));
		if (empty($omnify_ids) || empty($omnify_action)) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=no_selection')); exit;
		}

		foreach ($omnify_ids as $omnify_id) {
			if ($omnify_action === 'trash') {
				$this->omnify_products->trash($omnify_id);
			} elseif ($omnify_action === 'restore') {
				$this->omnify_products->restore($omnify_id);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&message=bulk_' . $omnify_action)); exit;
	}

	public function handle_bulk_coupons(): void {
		check_admin_referer('omnify_bulk_coupons', 'omnify_bulk_nonce');
		if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }

		$omnify_ids = array_map('absint', (array) wp_unslash($_POST['coupon_ids'] ?? []));
		$omnify_action = sanitize_key(wp_unslash($_POST['bulk_action'] ?? ''));
		if (empty($omnify_ids) || empty($omnify_action)) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=no_selection')); exit;
		}

		foreach ($omnify_ids as $omnify_id) {
			if ($omnify_action === 'trash') {
				$this->omnify_coupons->trash($omnify_id);
			} elseif ($omnify_action === 'restore') {
				$this->omnify_coupons->restore($omnify_id);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=bulk_' . $omnify_action)); exit;
	}

	public function handle_trash_review(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_review_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_reviews_repo->trash($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-reviews&message=trashed')); exit;
	}
	public function handle_restore_review(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_review_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_reviews_repo->restore($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-reviews&message=restored')); exit;
	}

	public function handle_trash_abandoned_cart(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_abandoned_cart_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_abandoned_carts->trash($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-abandoned-carts&message=trashed')); exit;
	}
	public function handle_restore_abandoned_cart(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_abandoned_cart_' . $omnify_id);
		if (current_user_can('manage_options')) $this->omnify_abandoned_carts->restore($omnify_id);
		wp_safe_redirect(admin_url('admin.php?page=omnify-abandoned-carts&message=restored')); exit;
	}

	public function handle_delete_product_file(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		$omnify_product_id = absint(wp_unslash($_GET['product_id'] ?? 0));
		check_admin_referer('omnify_delete_product_file_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_product_files->delete($omnify_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product_id . '&message=deleted'));
		exit;
	}

	public function handle_save_customer(): void {
		check_admin_referer('omnify_save_customer', 'omnify_customer_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_id = absint(wp_unslash($_POST['id'] ?? 0));

		$omnify_data = [
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'first_name' => sanitize_text_field(wp_unslash($_POST['first_name'] ?? '')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'last_name'  => sanitize_text_field(wp_unslash($_POST['last_name'] ?? '')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email'      => sanitize_email(wp_unslash($_POST['email'] ?? '')),
			'status'     => sanitize_key(wp_unslash($_POST['status'] ?? 'active')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'phone'      => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'company'    => sanitize_text_field(wp_unslash($_POST['company'] ?? '')),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'country'    => strtoupper(substr(sanitize_text_field(wp_unslash($_POST['country'] ?? '')), 0, 2)),
			'tax_exempt' => ! empty($_POST['tax_exempt']),
		];

		if ($omnify_id) {
			$this->omnify_customers->update($omnify_id, $omnify_data);
			wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_id . '&message=saved'));
		} else {
			$omnify_new_id = $this->omnify_customers->create($omnify_data);
			wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_new_id . '&message=saved'));
		}
		exit;
	}

	public function handle_delete_customer(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_delete_customer_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_customers->delete($omnify_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&message=deleted'));
		exit;
	}

	public function handle_add_customer_note(): void {
		check_admin_referer('omnify_add_customer_note', 'omnify_note_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_customer_id = absint(wp_unslash($_POST['customer_id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_note        = wp_kses_post(wp_unslash($_POST['note'] ?? ''));

		if ($omnify_customer_id && !empty($omnify_note)) {
			$this->omnify_customers->add_note($omnify_customer_id, $omnify_note);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_customer_id . '&message=note_added'));
		exit;
	}

	public function handle_delete_customer_note(): void {
		$omnify_customer_id = absint(wp_unslash($_GET['customer_id'] ?? 0));
		$omnify_note_id     = absint(wp_unslash($_GET['note_id'] ?? 0));
		check_admin_referer('omnify_delete_customer_note_' . $omnify_note_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_customers->delete_note($omnify_customer_id, $omnify_note_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_customer_id . '&message=note_deleted'));
		exit;
	}

	public function handle_grant_access(): void {
		check_admin_referer('omnify_grant_access', 'omnify_grant_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_customer_id = absint(wp_unslash($_POST['customer_id'] ?? 0));
		$omnify_product_id  = absint(wp_unslash($_POST['product_id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_expires_at  = sanitize_text_field(wp_unslash($_POST['expires_at'] ?? ''));

		if ($omnify_customer_id && $omnify_product_id) {
			$this->omnify_customer_access->grant($omnify_customer_id, $omnify_product_id, !empty($omnify_expires_at) ? $omnify_expires_at : null);

			$omnify_product = $this->omnify_products->find($omnify_product_id);
			$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';
			$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
			$omnify_customer_email = $omnify_customer ? ($omnify_customer['email'] ?? '') : '';

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'grant_access',
				'customer',
				(string) $omnify_customer_id,
				sprintf(
					// translators: %1$s: placeholder value, %2$d: placeholder value, %3$s: placeholder value.
					__('Granted manual product access for "%1$s" (ID: %2$d) to customer %3$s.', 'omnifywp-ecommerce'),
					$omnify_product_name ?: 'Unknown Product',
					$omnify_product_id,
					$omnify_customer_email ?: '#' . $omnify_customer_id
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_customer_id . '&message=access_granted'));
		exit;
	}

	public function handle_revoke_access(): void {
		$omnify_customer_id = absint(wp_unslash($_GET['customer_id'] ?? 0));
		$omnify_product_id  = absint(wp_unslash($_GET['product_id'] ?? 0));
		check_admin_referer('omnify_revoke_access_' . $omnify_customer_id . '_' . $omnify_product_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_customer_id && $omnify_product_id) {
			$this->omnify_customer_access->revoke($omnify_customer_id, $omnify_product_id);

			$omnify_product = $this->omnify_products->find($omnify_product_id);
			$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';
			$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
			$omnify_customer_email = $omnify_customer ? ($omnify_customer['email'] ?? '') : '';

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'revoke_access',
				'customer',
				(string) $omnify_customer_id,
				sprintf(
					// translators: %1$s: placeholder value, %2$d: placeholder value, %3$s: placeholder value.
					__('Revoked manual product access for "%1$s" (ID: %2$d) from customer %3$s.', 'omnifywp-ecommerce'),
					$omnify_product_name ?: 'Unknown Product',
					$omnify_product_id,
					$omnify_customer_email ?: '#' . $omnify_customer_id
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_customer_id . '&message=access_revoked'));
		exit;
	}

	public function handle_save_settings(): void {
		check_admin_referer('omnify_save_settings', 'omnify_settings_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		// Merge with existing settings so partial form posts don't wipe other sections
		$omnify_existing = $this->omnify_settings->all();
		$omnify_defaults = $this->omnify_settings->defaults();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_section  = sanitize_key((string) wp_unslash($_POST['section'] ?? 'general'));
		$omnify_allowed_sections = ['general', 'payments', 'tax', 'delivery', 'email', 'checkout', 'marketing', 'pages', 'categories', 'tags', 'brands', 'attributes', 'roles', 'fraud', 'design', 'filters'];
		if (! in_array($omnify_section, $omnify_allowed_sections, true)) {
			$omnify_section = 'general';
		}
		$omnify_is_storefront_section = in_array($omnify_section, ['design', 'filters'], true);
		$omnify_storefront_columns = max(1, min(3, absint(wp_unslash($_POST['storefront_grid_columns'] ?? ($_POST['design_grid_columns'] ?? ($omnify_existing['storefront_grid_columns'] ?? ($omnify_existing['design_grid_columns'] ?? 3)))))));

		// Build payment_methods array from POST
		$omnify_raw_payment_methods = $omnify_existing['payment_methods'] ?? $omnify_defaults['payment_methods'];
		if (isset($_POST['payment_methods']) && is_array($_POST['payment_methods'])) {
			$omnify_posted_methods = map_deep(wp_unslash($_POST['payment_methods']), 'sanitize_text_field');
			$omnify_raw_pm = [];
			$processed_ids = [];
			foreach ($omnify_defaults['payment_methods'] as $omnify_default_method) {
				$omnify_id  = $omnify_default_method['id'];
				$omnify_raw = $omnify_posted_methods[ $omnify_id ] ?? [];
				$omnify_raw_pm[] = [
					'id'           => $omnify_id,
					'name'         => sanitize_text_field((string) ($omnify_raw['name'] ?? $omnify_default_method['name'])),
					'enabled'      => ! empty($omnify_raw['enabled']),
					'instructions' => sanitize_textarea_field((string) ($omnify_raw['instructions'] ?? '')),
				];
				$processed_ids[] = $omnify_id;
			}
			foreach ($omnify_posted_methods as $omnify_id => $omnify_raw) {
				if (in_array($omnify_id, $processed_ids, true)) {
					continue;
				}
				$omnify_id = sanitize_key($omnify_id);
				if (empty($omnify_id) || ! is_array($omnify_raw)) {
					continue;
				}
				$omnify_raw_pm[] = [
					'id'           => $omnify_id,
					'name'         => sanitize_text_field((string) ($omnify_raw['name'] ?? 'Manual Payment')),
					'enabled'      => ! empty($omnify_raw['enabled']),
					'instructions' => sanitize_textarea_field((string) ($omnify_raw['instructions'] ?? '')),
				];
			}
			$omnify_raw_payment_methods = $omnify_raw_pm;
		}

		$omnify_posted = [
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'store_name'                  => sanitize_text_field(wp_unslash($_POST['store_name'] ?? $omnify_existing['store_name'])),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'store_email'                 => sanitize_email(wp_unslash($_POST['store_email'] ?? ($omnify_existing['store_email'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'store_url'                   => esc_url_raw(wp_unslash($_POST['store_url'] ?? ($omnify_existing['store_url'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'store_phone'                 => sanitize_text_field(wp_unslash($_POST['store_phone'] ?? ($omnify_existing['store_phone'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'store_address'               => sanitize_textarea_field(wp_unslash($_POST['store_address'] ?? ($omnify_existing['store_address'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'business_tax_id'             => sanitize_text_field(wp_unslash($_POST['business_tax_id'] ?? ($omnify_existing['business_tax_id'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'default_currency'            => sanitize_text_field(wp_unslash($_POST['default_currency'] ?? $omnify_existing['default_currency'])),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'default_country'             => strtoupper(substr(sanitize_text_field(wp_unslash($_POST['default_country'] ?? ($omnify_existing['default_country'] ?? 'US'))), 0, 2)),
			'selling_locations'           => 'general' === $omnify_section ? (in_array(wp_unslash($_POST['selling_locations'] ?? ''), ['all', 'specific'], true) ? sanitize_key(wp_unslash($_POST['selling_locations'])) : 'all') : ($omnify_existing['selling_locations'] ?? 'all'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'selling_countries'           => 'general' === $omnify_section ? (is_array($_POST['selling_countries'] ?? null) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['selling_countries'])) : []) : ($omnify_existing['selling_countries'] ?? []),
			'admin_access_capability'     => 'roles' === $omnify_section ? (in_array(wp_unslash($_POST['admin_access_capability'] ?? ''), ['manage_options', 'manage_omnify'], true) ? sanitize_key(wp_unslash($_POST['admin_access_capability'])) : 'manage_options') : ($omnify_existing['admin_access_capability'] ?? 'manage_options'),
			'role_permissions'            => 'roles' === $omnify_section ? (is_array($_POST['role_permissions'] ?? null) ? map_deep(wp_unslash($_POST['role_permissions']), 'sanitize_key') : []) : ($omnify_existing['role_permissions'] ?? []),
			'price_decimals'              => max(0, min(4, absint(wp_unslash($_POST['price_decimals'] ?? ($omnify_existing['price_decimals'] ?? 2))))),
			'thousand_separator'          => sanitize_text_field(wp_unslash($_POST['thousand_separator'] ?? ($omnify_existing['thousand_separator'] ?? ','))),
			'decimal_separator'           => sanitize_text_field(wp_unslash($_POST['decimal_separator'] ?? ($omnify_existing['decimal_separator'] ?? '.'))),
			'currency_position'           => in_array(wp_unslash($_POST['currency_position'] ?? ''), ['before', 'after'], true) ? sanitize_key(wp_unslash($_POST['currency_position'])) : ($omnify_existing['currency_position'] ?? 'before'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'tax_rate'                    => max(0.0, (float) wp_unslash($_POST['tax_rate'] ?? $omnify_existing['tax_rate'])),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tax_label'                   => sanitize_text_field(wp_unslash($_POST['tax_label'] ?? ($omnify_existing['tax_label'] ?? 'Tax'))),
			'prices_include_tax'          => 'general' === $omnify_section ? ! empty($_POST['prices_include_tax']) : ! empty($omnify_existing['prices_include_tax']),
			'tax_shipping'                => 'general' === $omnify_section ? ! empty($_POST['tax_shipping']) : ! empty($omnify_existing['tax_shipping']),
			'tax_rounding'                => 'general' === $omnify_section ? sanitize_key(wp_unslash($_POST['tax_rounding'] ?? 'line')) : ($omnify_existing['tax_rounding'] ?? 'line'),
			'tax_reporting_enabled'       => 'general' === $omnify_section ? ! empty($_POST['tax_reporting_enabled']) : ! empty($omnify_existing['tax_reporting_enabled']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'tax_rules'                   => 'tax' === $omnify_section ? $this->sanitize_tax_rules_array( (array) ( wp_unslash($_POST['tax_rules']) ?? [] ) ) : ( $omnify_existing['tax_rules'] ?? [] ),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'delivery_zones'              => 'delivery' === $omnify_section ? $this->sanitize_delivery_zones_array( (array) ( wp_unslash($_POST['delivery_zones']) ?? [] ) ) : ( $omnify_existing['delivery_zones'] ?? [] ),
			'test_mode'                   => 'general' === $omnify_section ? ! empty($_POST['test_mode']) : ! empty($omnify_existing['test_mode']),
			'stripe_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['stripe_checkout_enabled']) : ! empty($omnify_existing['stripe_enabled']),
			'stripe_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['stripe_mode'] ?? 'test')) : ($omnify_existing['stripe_mode'] ?? 'test'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'stripe_test_publishable_key' => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['stripe_test_publishable_key'] ?? '')) : ($omnify_existing['stripe_test_publishable_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'stripe_test_secret_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['stripe_test_secret_key'] ?? '')) : ($omnify_existing['stripe_test_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'stripe_live_publishable_key' => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['stripe_live_publishable_key'] ?? '')) : ($omnify_existing['stripe_live_publishable_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'stripe_live_secret_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['stripe_live_secret_key'] ?? '')) : ($omnify_existing['stripe_live_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'stripe_webhook_secret'       => ($omnify_existing['stripe_webhook_secret'] ?? ''),
			'stripe_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['stripe_checkout_enabled']) : ! empty($omnify_existing['stripe_checkout_enabled']),
			'stripe_payment_intents_enabled' => ! empty($omnify_existing['stripe_payment_intents_enabled']),
			'stripe_apple_pay_enabled'    => ! empty($omnify_existing['stripe_apple_pay_enabled']),
			'stripe_google_pay_enabled'   => ! empty($omnify_existing['stripe_google_pay_enabled']),
			'paypal_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['paypal_checkout_enabled']) : ! empty($omnify_existing['paypal_enabled']),
			'paypal_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['paypal_mode'] ?? 'sandbox')) : ($omnify_existing['paypal_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paypal_sandbox_client_id'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paypal_sandbox_client_id'] ?? '')) : ($omnify_existing['paypal_sandbox_client_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paypal_sandbox_secret'       => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paypal_sandbox_secret'] ?? '')) : ($omnify_existing['paypal_sandbox_secret'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paypal_live_client_id'       => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paypal_live_client_id'] ?? '')) : ($omnify_existing['paypal_live_client_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paypal_live_secret'          => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paypal_live_secret'] ?? '')) : ($omnify_existing['paypal_live_secret'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paypal_webhook_id'           => ($omnify_existing['paypal_webhook_id'] ?? ''),
			'paypal_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['paypal_checkout_enabled']) : ! empty($omnify_existing['paypal_checkout_enabled']),
			'razorpay_enabled'            => 'payments' === $omnify_section ? ! empty($_POST['razorpay_checkout_enabled']) : ! empty($omnify_existing['razorpay_enabled']),
			'razorpay_mode'               => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['razorpay_mode'] ?? 'test')) : ($omnify_existing['razorpay_mode'] ?? 'test'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'razorpay_test_key_id'        => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['razorpay_test_key_id'] ?? '')) : ($omnify_existing['razorpay_test_key_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'razorpay_test_key_secret'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['razorpay_test_key_secret'] ?? '')) : ($omnify_existing['razorpay_test_key_secret'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'razorpay_live_key_id'        => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['razorpay_live_key_id'] ?? '')) : ($omnify_existing['razorpay_live_key_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'razorpay_live_key_secret'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['razorpay_live_key_secret'] ?? '')) : ($omnify_existing['razorpay_live_key_secret'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'razorpay_webhook_secret'     => ($omnify_existing['razorpay_webhook_secret'] ?? ''),
			'razorpay_checkout_enabled'   => 'payments' === $omnify_section ? ! empty($_POST['razorpay_checkout_enabled']) : ! empty($omnify_existing['razorpay_checkout_enabled']),
			// Alipay
			'alipay_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['alipay_checkout_enabled']) : ! empty($omnify_existing['alipay_enabled']),
			'alipay_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['alipay_mode'] ?? 'sandbox')) : ($omnify_existing['alipay_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'alipay_app_id'               => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['alipay_app_id'] ?? '')) : ($omnify_existing['alipay_app_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'alipay_merchant_private_key' => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['alipay_merchant_private_key'] ?? '')) : ($omnify_existing['alipay_merchant_private_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'alipay_alipay_public_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['alipay_alipay_public_key'] ?? '')) : ($omnify_existing['alipay_alipay_public_key'] ?? ''),
			'alipay_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['alipay_checkout_enabled']) : ! empty($omnify_existing['alipay_checkout_enabled']),
			// WeChat Pay
			'wechat_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['wechat_checkout_enabled']) : ! empty($omnify_existing['wechat_enabled']),
			'wechat_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['wechat_mode'] ?? 'sandbox')) : ($omnify_existing['wechat_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'wechat_appid'                => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['wechat_appid'] ?? '')) : ($omnify_existing['wechat_appid'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'wechat_mchid'                => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['wechat_mchid'] ?? '')) : ($omnify_existing['wechat_mchid'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'wechat_key'                  => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['wechat_key'] ?? '')) : ($omnify_existing['wechat_key'] ?? ''),
			'wechat_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['wechat_checkout_enabled']) : ! empty($omnify_existing['wechat_checkout_enabled']),
			// SSLCommerz
			'sslcommerz_enabled'          => 'payments' === $omnify_section ? ! empty($_POST['sslcommerz_checkout_enabled']) : ! empty($omnify_existing['sslcommerz_enabled']),
			'sslcommerz_mode'             => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['sslcommerz_mode'] ?? 'sandbox')) : ($omnify_existing['sslcommerz_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'sslcommerz_store_id'         => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['sslcommerz_store_id'] ?? '')) : ($omnify_existing['sslcommerz_store_id'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'sslcommerz_store_password'   => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['sslcommerz_store_password'] ?? '')) : ($omnify_existing['sslcommerz_store_password'] ?? ''),
			'sslcommerz_checkout_enabled' => 'payments' === $omnify_section ? ! empty($_POST['sslcommerz_checkout_enabled']) : ! empty($omnify_existing['sslcommerz_checkout_enabled']),
			// Paystack (Africa)
			'paystack_enabled'            => 'payments' === $omnify_section ? ! empty($_POST['paystack_checkout_enabled']) : ! empty($omnify_existing['paystack_enabled']),
			'paystack_mode'               => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['paystack_mode'] ?? 'test')) : ($omnify_existing['paystack_mode'] ?? 'test'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paystack_test_public_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paystack_test_public_key'] ?? '')) : ($omnify_existing['paystack_test_public_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paystack_test_secret_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paystack_test_secret_key'] ?? '')) : ($omnify_existing['paystack_test_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paystack_live_public_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paystack_live_public_key'] ?? '')) : ($omnify_existing['paystack_live_public_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paystack_live_secret_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paystack_live_secret_key'] ?? '')) : ($omnify_existing['paystack_live_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'paystack_webhook_secret'     => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['paystack_webhook_secret'] ?? '')) : ($omnify_existing['paystack_webhook_secret'] ?? ''),
			'paystack_checkout_enabled'   => 'payments' === $omnify_section ? ! empty($_POST['paystack_checkout_enabled']) : ! empty($omnify_existing['paystack_checkout_enabled']),
			// Tap Payments (Middle East)
			'tap_enabled'                 => 'payments' === $omnify_section ? ! empty($_POST['tap_checkout_enabled']) : ! empty($omnify_existing['tap_enabled']),
			'tap_mode'                    => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['tap_mode'] ?? 'test')) : ($omnify_existing['tap_mode'] ?? 'test'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tap_test_publishable_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tap_test_publishable_key'] ?? '')) : ($omnify_existing['tap_test_publishable_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tap_test_secret_key'         => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tap_test_secret_key'] ?? '')) : ($omnify_existing['tap_test_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tap_live_publishable_key'    => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tap_live_publishable_key'] ?? '')) : ($omnify_existing['tap_live_publishable_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tap_live_secret_key'         => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tap_live_secret_key'] ?? '')) : ($omnify_existing['tap_live_secret_key'] ?? ''),
			'tap_checkout_enabled'        => 'payments' === $omnify_section ? ! empty($_POST['tap_checkout_enabled']) : ! empty($omnify_existing['tap_checkout_enabled']),
			// Mollie (European Union)
			'mollie_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['mollie_checkout_enabled']) : ! empty($omnify_existing['mollie_enabled']),
			'mollie_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['mollie_mode'] ?? 'test')) : ($omnify_existing['mollie_mode'] ?? 'test'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'mollie_test_api_key'         => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['mollie_test_api_key'] ?? '')) : ($omnify_existing['mollie_test_api_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'mollie_live_api_key'         => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['mollie_live_api_key'] ?? '')) : ($omnify_existing['mollie_live_api_key'] ?? ''),
			'mollie_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['mollie_checkout_enabled']) : ! empty($omnify_existing['mollie_checkout_enabled']),
			// Khalti (Nepal)
			'khalti_enabled'              => 'payments' === $omnify_section ? ! empty($_POST['khalti_checkout_enabled']) : ! empty($omnify_existing['khalti_enabled']),
			'khalti_mode'                 => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['khalti_mode'] ?? 'sandbox')) : ($omnify_existing['khalti_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'khalti_test_public_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['khalti_test_public_key'] ?? '')) : ($omnify_existing['khalti_test_public_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'khalti_test_secret_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['khalti_test_secret_key'] ?? '')) : ($omnify_existing['khalti_test_secret_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'khalti_live_public_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['khalti_live_public_key'] ?? '')) : ($omnify_existing['khalti_live_public_key'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'khalti_live_secret_key'      => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['khalti_live_secret_key'] ?? '')) : ($omnify_existing['khalti_live_secret_key'] ?? ''),
			'khalti_checkout_enabled'     => 'payments' === $omnify_section ? ! empty($_POST['khalti_checkout_enabled']) : ! empty($omnify_existing['khalti_checkout_enabled']),
			// eSewa (Nepal)
			'esewa_enabled'               => 'payments' === $omnify_section ? ! empty($_POST['esewa_checkout_enabled']) : ! empty($omnify_existing['esewa_enabled']),
			'esewa_mode'                  => 'payments' === $omnify_section ? sanitize_key(wp_unslash($_POST['esewa_mode'] ?? 'sandbox')) : ($omnify_existing['esewa_mode'] ?? 'sandbox'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'esewa_test_product_code'     => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['esewa_test_product_code'] ?? 'EPAYTEST')) : ($omnify_existing['esewa_test_product_code'] ?? 'EPAYTEST'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'esewa_test_secret_key'       => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['esewa_test_secret_key'] ?? '8gBm/:&EnhH.1/q')) : ($omnify_existing['esewa_test_secret_key'] ?? '8gBm/:&EnhH.1/q'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'esewa_live_product_code'     => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['esewa_live_product_code'] ?? '')) : ($omnify_existing['esewa_live_product_code'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'esewa_live_secret_key'       => 'payments' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['esewa_live_secret_key'] ?? '')) : ($omnify_existing['esewa_live_secret_key'] ?? ''),
			'esewa_checkout_enabled'      => 'payments' === $omnify_section ? ! empty($_POST['esewa_checkout_enabled']) : ! empty($omnify_existing['esewa_checkout_enabled']),
			'tracking_ga4_enabled'        => 'marketing' === $omnify_section ? ! empty($_POST['tracking_ga4_enabled']) : ! empty($omnify_existing['tracking_ga4_enabled']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tracking_ga4_measurement_id' => 'marketing' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tracking_ga4_measurement_id'] ?? '')) : ($omnify_existing['tracking_ga4_measurement_id'] ?? ''),
			'tracking_meta_enabled'       => 'marketing' === $omnify_section ? ! empty($_POST['tracking_meta_enabled']) : ! empty($omnify_existing['tracking_meta_enabled']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'tracking_meta_pixel_id'      => 'marketing' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['tracking_meta_pixel_id'] ?? '')) : ($omnify_existing['tracking_meta_pixel_id'] ?? ''),
			'tracking_debug_mode'         => 'marketing' === $omnify_section ? ! empty($_POST['tracking_debug_mode']) : ! empty($omnify_existing['tracking_debug_mode']),
			'mini_cart_menu_enabled'      => 'general' === $omnify_section ? ! empty($_POST['mini_cart_menu_enabled']) : ! empty($omnify_existing['mini_cart_menu_enabled']),
			'mini_cart_menu_location'     => 'general' === $omnify_section ? sanitize_key(wp_unslash($_POST['mini_cart_menu_location'] ?? '')) : ($omnify_existing['mini_cart_menu_location'] ?? ''),
			'payment_methods'             => $omnify_raw_payment_methods,
				'allow_guest_checkout'        => 'checkout' === $omnify_section ? ! empty($_POST['allow_guest_checkout']) : ! empty($omnify_existing['allow_guest_checkout']),
				'require_account_on_checkout' => 'checkout' === $omnify_section ? ! empty($_POST['require_account_on_checkout']) : ! empty($omnify_existing['require_account_on_checkout']),
				'account_creation_mode'       => 'checkout' === $omnify_section ? sanitize_key(wp_unslash($_POST['account_creation_mode'] ?? 'optional')) : ($omnify_existing['account_creation_mode'] ?? 'optional'),
				'auto_login_created_accounts' => 'checkout' === $omnify_section ? ! empty($_POST['auto_login_created_accounts']) : ! empty($omnify_existing['auto_login_created_accounts']),
				'checkout_require_phone'      => 'checkout' === $omnify_section ? ! empty($_POST['checkout_require_phone']) : ! empty($omnify_existing['checkout_require_phone']),
				'default_payment_method'      => 'checkout' === $omnify_section ? sanitize_key(wp_unslash($_POST['default_payment_method'] ?? 'card')) : ($omnify_existing['default_payment_method'] ?? 'card'),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'order_number_prefix'         => 'checkout' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['order_number_prefix'] ?? 'OMN-')) : ($omnify_existing['order_number_prefix'] ?? 'OMN-'),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'order_number_suffix'         => 'checkout' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['order_number_suffix'] ?? '')) : ($omnify_existing['order_number_suffix'] ?? ''),
				'order_number_padding'        => 'checkout' === $omnify_section ? max(1, min(10, absint(wp_unslash($_POST['order_number_padding'] ?? 4)))) : ($omnify_existing['order_number_padding'] ?? 4),
				'low_stock_threshold'         => 'checkout' === $omnify_section ? max(0, absint(wp_unslash($_POST['low_stock_threshold'] ?? 5))) : ($omnify_existing['low_stock_threshold'] ?? 5),
				'reduce_stock_on_checkout'    => 'checkout' === $omnify_section ? ! empty($_POST['reduce_stock_on_checkout']) : ! empty($omnify_existing['reduce_stock_on_checkout']),
				'enable_coupons'              => 'checkout' === $omnify_section ? ! empty($_POST['enable_coupons']) : ! empty($omnify_existing['enable_coupons']),
				'abandoned_cart_enabled'      => 'checkout' === $omnify_section ? ! empty($_POST['abandoned_cart_enabled']) : ! empty($omnify_existing['abandoned_cart_enabled']),
				'abandoned_cart_delay_minutes' => 'checkout' === $omnify_section ? max(5, absint(wp_unslash($_POST['abandoned_cart_delay_minutes'] ?? 60))) : ($omnify_existing['abandoned_cart_delay_minutes'] ?? 60),
				'abandoned_cart_expire_days'  => 'checkout' === $omnify_section ? max(1, absint(wp_unslash($_POST['abandoned_cart_expire_days'] ?? 14))) : ($omnify_existing['abandoned_cart_expire_days'] ?? 14),
				'abandoned_cart_max_reminders' => 'checkout' === $omnify_section ? max(1, absint(wp_unslash($_POST['abandoned_cart_max_reminders'] ?? 1))) : ($omnify_existing['abandoned_cart_max_reminders'] ?? 1),
			'require_terms'               => 'checkout' === $omnify_section ? ! empty($_POST['require_terms']) : ! empty($omnify_existing['require_terms']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'terms_url'                   => esc_url_raw(wp_unslash($_POST['terms_url'] ?? ($omnify_existing['terms_url'] ?? ''))),
			'download_link_expiry'        => max(0, absint(wp_unslash($_POST['download_link_expiry'] ?? ($omnify_existing['download_link_expiry'] ?? 7)))),
			'download_limit'              => max(0, absint(wp_unslash($_POST['download_limit'] ?? ($omnify_existing['download_limit'] ?? 0)))),
			'enable_refunds'              => 'checkout' === $omnify_section ? ! empty($_POST['enable_refunds']) : ! empty($omnify_existing['enable_refunds']),
			'refund_duration'             => 'checkout' === $omnify_section ? max(1, absint(wp_unslash($_POST['refund_duration'] ?? 14))) : ($omnify_existing['refund_duration'] ?? 14),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_from_name'             => sanitize_text_field(wp_unslash($_POST['email_from_name'] ?? ($omnify_existing['email_from_name'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_from_address'          => sanitize_email(wp_unslash($_POST['email_from_address'] ?? ($omnify_existing['email_from_address'] ?? ''))),
			'email_receipt'               => 'email' === $omnify_section ? ! empty($_POST['email_receipt']) : ! empty($omnify_existing['email_receipt']),
			'email_payment_pending'       => 'email' === $omnify_section ? ! empty($_POST['email_payment_pending']) : ! empty($omnify_existing['email_payment_pending']),
			'email_download_links'        => 'email' === $omnify_section ? ! empty($_POST['email_download_links']) : ! empty($omnify_existing['email_download_links']),
			'email_admin_new_order'       => 'email' === $omnify_section ? ! empty($_POST['email_admin_new_order']) : ! empty($omnify_existing['email_admin_new_order']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'admin_notification_email'    => sanitize_text_field(wp_unslash($_POST['admin_notification_email'] ?? ($omnify_existing['admin_notification_email'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_footer_text'           => sanitize_textarea_field(wp_unslash($_POST['email_footer_text'] ?? ($omnify_existing['email_footer_text'] ?? ''))),
			'push_notifications_enabled'  => 'email' === $omnify_section ? ! empty($_POST['push_notifications_enabled']) : ! empty($omnify_existing['push_notifications_enabled']),
			'email_refund_requested'      => 'email' === $omnify_section ? ! empty($_POST['email_refund_requested']) : ! empty($omnify_existing['email_refund_requested']),
			'email_refund_processed'      => 'email' === $omnify_section ? ! empty($_POST['email_refund_processed']) : ! empty($omnify_existing['email_refund_processed']),
			'email_status_changed'        => 'email' === $omnify_section ? ! empty($_POST['email_status_changed']) : ! empty($omnify_existing['email_status_changed']),
			'email_order_completed'       => 'email' === $omnify_section ? ! empty($_POST['email_order_completed']) : ! empty($omnify_existing['email_order_completed']),
			'email_order_shipped'         => 'email' === $omnify_section ? ! empty($_POST['email_order_shipped']) : ! empty($omnify_existing['email_order_shipped']),
			'email_order_processing'      => 'email' === $omnify_section ? ! empty($_POST['email_order_processing']) : ! empty($omnify_existing['email_order_processing']),
			'email_order_cancelled'       => 'email' === $omnify_section ? ! empty($_POST['email_order_cancelled']) : ! empty($omnify_existing['email_order_cancelled']),
			'email_abandoned_cart'        => 'email' === $omnify_section ? ! empty($_POST['email_abandoned_cart']) : ! empty($omnify_existing['email_abandoned_cart']),
			'email_low_stock'             => 'email' === $omnify_section ? ! empty($_POST['email_low_stock']) : ! empty($omnify_existing['email_low_stock']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_receipt_subject'       => sanitize_text_field(wp_unslash($_POST['email_receipt_subject'] ?? ($omnify_existing['email_receipt_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_receipt_intro'         => sanitize_textarea_field(wp_unslash($_POST['email_receipt_intro'] ?? ($omnify_existing['email_receipt_intro'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_payment_pending_subject' => sanitize_text_field(wp_unslash($_POST['email_payment_pending_subject'] ?? ($omnify_existing['email_payment_pending_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_payment_pending_intro' => sanitize_textarea_field(wp_unslash($_POST['email_payment_pending_intro'] ?? ($omnify_existing['email_payment_pending_intro'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_abandoned_cart_subject' => sanitize_text_field(wp_unslash($_POST['email_abandoned_cart_subject'] ?? ($omnify_existing['email_abandoned_cart_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_abandoned_cart_intro'  => sanitize_textarea_field(wp_unslash($_POST['email_abandoned_cart_intro'] ?? ($omnify_existing['email_abandoned_cart_intro'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_low_stock_subject'     => sanitize_text_field(wp_unslash($_POST['email_low_stock_subject'] ?? ($omnify_existing['email_low_stock_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_refund_requested_subject' => sanitize_text_field(wp_unslash($_POST['email_refund_requested_subject'] ?? ($omnify_existing['email_refund_requested_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_refund_requested_body' => sanitize_textarea_field(wp_unslash($_POST['email_refund_requested_body'] ?? ($omnify_existing['email_refund_requested_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_refund_processed_subject' => sanitize_text_field(wp_unslash($_POST['email_refund_processed_subject'] ?? ($omnify_existing['email_refund_processed_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_refund_processed_body' => sanitize_textarea_field(wp_unslash($_POST['email_refund_processed_body'] ?? ($omnify_existing['email_refund_processed_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_status_changed_subject' => sanitize_text_field(wp_unslash($_POST['email_status_changed_subject'] ?? ($omnify_existing['email_status_changed_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_status_changed_body'   => sanitize_textarea_field(wp_unslash($_POST['email_status_changed_body'] ?? ($omnify_existing['email_status_changed_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_completed_subject' => sanitize_text_field(wp_unslash($_POST['email_order_completed_subject'] ?? ($omnify_existing['email_order_completed_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_completed_body'   => sanitize_textarea_field(wp_unslash($_POST['email_order_completed_body'] ?? ($omnify_existing['email_order_completed_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_shipped_subject' => sanitize_text_field(wp_unslash($_POST['email_order_shipped_subject'] ?? ($omnify_existing['email_order_shipped_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_shipped_body'   => sanitize_textarea_field(wp_unslash($_POST['email_order_shipped_body'] ?? ($omnify_existing['email_order_shipped_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_processing_subject' => sanitize_text_field(wp_unslash($_POST['email_order_processing_subject'] ?? ($omnify_existing['email_order_processing_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_processing_body'   => sanitize_textarea_field(wp_unslash($_POST['email_order_processing_body'] ?? ($omnify_existing['email_order_processing_body'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_cancelled_subject' => sanitize_text_field(wp_unslash($_POST['email_order_cancelled_subject'] ?? ($omnify_existing['email_order_cancelled_subject'] ?? ''))),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_order_cancelled_body'   => sanitize_textarea_field(wp_unslash($_POST['email_order_cancelled_body'] ?? ($omnify_existing['email_order_cancelled_body'] ?? ''))),
			'email_notifications_enabled' => 'email' === $omnify_section ? ! array_key_exists('email_notifications_enabled', $_POST) || ! empty($_POST['email_notifications_enabled']) : (! array_key_exists('email_notifications_enabled', $omnify_existing) || ! empty($omnify_existing['email_notifications_enabled'])),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_reply_to'              => sanitize_email(wp_unslash($_POST['email_reply_to'] ?? ($omnify_existing['email_reply_to'] ?? ''))),
			'email_bcc_admin'             => 'email' === $omnify_section ? ! empty($_POST['email_bcc_admin']) : ! empty($omnify_existing['email_bcc_admin']),
			'email_send_receipt_copy'     => 'email' === $omnify_section ? ! array_key_exists('email_send_receipt_copy', $_POST) || ! empty($_POST['email_send_receipt_copy']) : (! array_key_exists('email_send_receipt_copy', $omnify_existing) || ! empty($omnify_existing['email_send_receipt_copy'])),
			'email_status_include_details'=> 'email' === $omnify_section ? ! array_key_exists('email_status_include_details', $_POST) || ! empty($_POST['email_status_include_details']) : (! array_key_exists('email_status_include_details', $omnify_existing) || ! empty($omnify_existing['email_status_include_details'])),
			// SMTP
			'email_smtp_enabled'          => 'email' === $omnify_section ? ! empty($_POST['email_smtp_enabled']) : ! empty($omnify_existing['email_smtp_enabled']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_smtp_host'             => 'email' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['email_smtp_host'] ?? ($omnify_existing['email_smtp_host'] ?? ''))) : ($omnify_existing['email_smtp_host'] ?? ''),
			'email_smtp_port'             => 'email' === $omnify_section ? max(1, min(65535, absint(wp_unslash($_POST['email_smtp_port'] ?? ($omnify_existing['email_smtp_port'] ?? 587))))) : ($omnify_existing['email_smtp_port'] ?? 587),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'email_smtp_encryption'       => 'email' === $omnify_section ? (in_array(wp_unslash($_POST['email_smtp_encryption'] ?? ''), ['', 'ssl', 'tls'], true) ? $_POST['email_smtp_encryption'] : 'tls') : ($omnify_existing['email_smtp_encryption'] ?? 'tls'),
			'email_smtp_auth'             => 'email' === $omnify_section ? ! empty($_POST['email_smtp_auth']) : ! empty($omnify_existing['email_smtp_auth']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'email_smtp_username'         => 'email' === $omnify_section ? sanitize_text_field(wp_unslash($_POST['email_smtp_username'] ?? ($omnify_existing['email_smtp_username'] ?? ''))) : ($omnify_existing['email_smtp_username'] ?? ''),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'email_smtp_password'         => 'email' === $omnify_section ? ( ! empty($_POST['email_smtp_password']) ? (string) wp_unslash($_POST['email_smtp_password']): ($omnify_existing['email_smtp_password'] ?? '') ) : ($omnify_existing['email_smtp_password'] ?? ''),
			'fraud_country_mismatch_flag' => 'fraud' === $omnify_section ? ! empty($_POST['fraud_country_mismatch_flag']) : ! empty($omnify_existing['fraud_country_mismatch_flag']),
			'fraud_disposable_email_flag' => 'fraud' === $omnify_section ? ! empty($_POST['fraud_disposable_email_flag']) : ! empty($omnify_existing['fraud_disposable_email_flag']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'fraud_max_value'             => 'fraud' === $omnify_section ? max(0.0, (float) wp_unslash($_POST['fraud_max_value'] ?? 500.00)) : ($omnify_existing['fraud_max_value'] ?? 500.00),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'fraud_max_attempts_limit'    => 'fraud' === $omnify_section ? max(1, (int) wp_unslash($_POST['fraud_max_attempts_limit'] ?? 3)) : ($omnify_existing['fraud_max_attempts_limit'] ?? 3),
			// Design Settings
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'design_primary_color'        => $omnify_is_storefront_section ? sanitize_hex_color(wp_unslash($_POST['design_primary_color'] ?? ($omnify_existing['design_primary_color'] ?? '#6366f1'))) : ($omnify_existing['design_primary_color'] ?? '#6366f1'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'design_hover_color'          => $omnify_is_storefront_section ? sanitize_hex_color(wp_unslash($_POST['design_hover_color'] ?? ($omnify_existing['design_hover_color'] ?? '#4f46e5'))) : ($omnify_existing['design_hover_color'] ?? '#4f46e5'),
			'design_border_radius'        => $omnify_is_storefront_section ? max(0, absint(wp_unslash($_POST['design_border_radius'] ?? ($omnify_existing['design_border_radius'] ?? 8)))) : ($omnify_existing['design_border_radius'] ?? 8),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'design_font_family'          => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['design_font_family'] ?? ($omnify_existing['design_font_family'] ?? ''))) : ($omnify_existing['design_font_family'] ?? "'Inter', system-ui, -apple-system, sans-serif"),
			'design_grid_columns'         => $omnify_is_storefront_section ? $omnify_storefront_columns : ($omnify_existing['design_grid_columns'] ?? 3),
			'design_show_reviews'         => $omnify_is_storefront_section ? ! empty($_POST['design_show_reviews']) : ! empty($omnify_existing['design_show_reviews']),
			// Filters Settings
			'storefront_show_filters'     => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_filters']) : (! array_key_exists('storefront_show_filters', $omnify_existing) || ! empty($omnify_existing['storefront_show_filters'])),
			'storefront_show_search'      => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_search']) : (! array_key_exists('storefront_show_search', $omnify_existing) || ! empty($omnify_existing['storefront_show_search'])),
			'storefront_show_sort'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_sort']) : (! array_key_exists('storefront_show_sort', $omnify_existing) || ! empty($omnify_existing['storefront_show_sort'])),
			'storefront_filter_position'  => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_filter_position'] ?? 'top')) : ($omnify_existing['storefront_filter_position'] ?? 'top'),
			'storefront_catalog_layout'   => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_catalog_layout'] ?? 'grid')) : ($omnify_existing['storefront_catalog_layout'] ?? 'grid'),
			'storefront_products_per_page' => $omnify_is_storefront_section ? max(1, min(100, absint(wp_unslash($_POST['storefront_products_per_page'] ?? 12)))) : ($omnify_existing['storefront_products_per_page'] ?? 12),
			'storefront_grid_rows'        => $omnify_is_storefront_section ? max(0, min(20, absint(wp_unslash($_POST['storefront_grid_rows'] ?? 0)))) : ($omnify_existing['storefront_grid_rows'] ?? 0),
			'storefront_grid_columns'     => $omnify_is_storefront_section ? $omnify_storefront_columns : ($omnify_existing['storefront_grid_columns'] ?? ($omnify_existing['design_grid_columns'] ?? 3)),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'storefront_filters'          => $omnify_is_storefront_section ? (is_array($_POST['storefront_filters'] ?? null) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['storefront_filters'])) : []) : ($omnify_existing['storefront_filters'] ?? ['category', 'type']),
			// Additional Storefront Experience options
			'storefront_show_wishlist'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_wishlist']) : ! empty($omnify_existing['storefront_show_wishlist']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'storefront_default_sort'         => $omnify_is_storefront_section ? (in_array(wp_unslash($_POST['storefront_default_sort'] ?? ''), ['latest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'], true) ? $_POST['storefront_default_sort'] : 'latest') : ($omnify_existing['storefront_default_sort'] ?? 'latest'),
			'storefront_show_sku'             => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_sku']) : ! empty($omnify_existing['storefront_show_sku']),
			'storefront_show_breadcrumbs'     => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_breadcrumbs']) : ! empty($omnify_existing['storefront_show_breadcrumbs']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'storefront_pagination_type'      => $omnify_is_storefront_section ? (in_array(wp_unslash($_POST['storefront_pagination_type'] ?? ''), ['classic', 'load_more'], true) ? $_POST['storefront_pagination_type'] : 'classic') : ($omnify_existing['storefront_pagination_type'] ?? 'classic'),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'storefront_card_style'           => $omnify_is_storefront_section ? (in_array(wp_unslash($_POST['storefront_card_style'] ?? ''), ['standard', 'compact', 'spacious'], true) ? $_POST['storefront_card_style'] : 'standard') : ($omnify_existing['storefront_card_style'] ?? 'standard'),
			'storefront_show_add_to_cart'     => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_add_to_cart']) : ! empty($omnify_existing['storefront_show_add_to_cart']),
			'storefront_related_count'        => $omnify_is_storefront_section ? max(0, min(12, absint(wp_unslash($_POST['storefront_related_count'] ?? 4)))) : max(0, min(12, absint($omnify_existing['storefront_related_count'] ?? 4))),
			'storefront_show_discount_badge'  => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_discount_badge']) : ! empty($omnify_existing['storefront_show_discount_badge']),
			'storefront_show_reassurance'     => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_reassurance']) : ! empty($omnify_existing['storefront_show_reassurance']),
			'storefront_show_compare'         => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_compare']) : ! empty($omnify_existing['storefront_show_compare']),
			'storefront_show_recently_viewed' => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_recently_viewed']) : ! empty($omnify_existing['storefront_show_recently_viewed']),
			'storefront_recently_viewed_count'=> $omnify_is_storefront_section ? max(1, min(20, absint(wp_unslash($_POST['storefront_recently_viewed_count'] ?? 5)))) : max(1, min(20, absint($omnify_existing['storefront_recently_viewed_count'] ?? 5))),
			'storefront_infinite_scroll'      => $omnify_is_storefront_section ? ! empty($_POST['storefront_infinite_scroll']) : ! empty($omnify_existing['storefront_infinite_scroll']),
			'storefront_reassurance_1_title'   => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_1_title'] ?? '')) : ($omnify_existing['storefront_reassurance_1_title'] ?? 'Secure Checkout'),
			'storefront_reassurance_1_desc'    => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_1_desc'] ?? '')) : ($omnify_existing['storefront_reassurance_1_desc'] ?? 'Your data is protected'),
			'storefront_reassurance_2_title'   => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_2_title'] ?? '')) : ($omnify_existing['storefront_reassurance_2_title'] ?? 'Instant Download'),
			'storefront_reassurance_2_desc'    => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_2_desc'] ?? '')) : ($omnify_existing['storefront_reassurance_2_desc'] ?? 'Get access immediately'),
			'storefront_reassurance_3_title'   => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_3_title'] ?? '')) : ($omnify_existing['storefront_reassurance_3_title'] ?? '24/7 Support'),
			'storefront_reassurance_3_desc'    => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_reassurance_3_desc'] ?? '')) : ($omnify_existing['storefront_reassurance_3_desc'] ?? "We're here to help"),
			'storefront_show_hero'             => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_hero']) : ! empty($omnify_existing['storefront_show_hero']),
			'storefront_hero_title'            => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_hero_title'] ?? '')) : ($omnify_existing['storefront_hero_title'] ?? 'Storefront'),
			'storefront_hero_desc'             => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_hero_desc'] ?? '')) : ($omnify_existing['storefront_hero_desc'] ?? ''),
			'storefront_hero_image'            => $omnify_is_storefront_section ? sanitize_url(wp_unslash($_POST['storefront_hero_image'] ?? '')) : ($omnify_existing['storefront_hero_image'] ?? ''),
			'storefront_hero_wave_color'       => $omnify_is_storefront_section ? sanitize_hex_color(wp_unslash($_POST['storefront_hero_wave_color'] ?? '')) : ($omnify_existing['storefront_hero_wave_color'] ?? '#e11d48'),
			'storefront_show_features_bar'     => $omnify_is_storefront_section ? ! empty($_POST['storefront_show_features_bar']) : ! empty($omnify_existing['storefront_show_features_bar']),
			'storefront_feature_1_show'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_feature_1_show']) : ! empty($omnify_existing['storefront_feature_1_show']),
			'storefront_feature_1_icon'        => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_feature_1_icon'] ?? '')) : ($omnify_existing['storefront_feature_1_icon'] ?? 'dollar'),
			'storefront_feature_1_title'       => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_1_title'] ?? '')) : ($omnify_existing['storefront_feature_1_title'] ?? 'High Quality'),
			'storefront_feature_1_desc'        => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_1_desc'] ?? '')) : ($omnify_existing['storefront_feature_1_desc'] ?? ''),
			'storefront_feature_2_show'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_feature_2_show']) : ! empty($omnify_existing['storefront_feature_2_show']),
			'storefront_feature_2_icon'        => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_feature_2_icon'] ?? '')) : ($omnify_existing['storefront_feature_2_icon'] ?? 'lock'),
			'storefront_feature_2_title'       => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_2_title'] ?? '')) : ($omnify_existing['storefront_feature_2_title'] ?? 'Secure Checkout'),
			'storefront_feature_2_desc'        => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_2_desc'] ?? '')) : ($omnify_existing['storefront_feature_2_desc'] ?? ''),
			'storefront_feature_3_show'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_feature_3_show']) : ! empty($omnify_existing['storefront_feature_3_show']),
			'storefront_feature_3_icon'        => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_feature_3_icon'] ?? '')) : ($omnify_existing['storefront_feature_3_icon'] ?? 'lightning'),
			'storefront_feature_3_title'       => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_3_title'] ?? '')) : ($omnify_existing['storefront_feature_3_title'] ?? 'Instant Access'),
			'storefront_feature_3_desc'        => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_3_desc'] ?? '')) : ($omnify_existing['storefront_feature_3_desc'] ?? ''),
			'storefront_feature_4_show'        => $omnify_is_storefront_section ? ! empty($_POST['storefront_feature_4_show']) : ! empty($omnify_existing['storefront_feature_4_show']),
			'storefront_feature_4_icon'        => $omnify_is_storefront_section ? sanitize_key(wp_unslash($_POST['storefront_feature_4_icon'] ?? '')) : ($omnify_existing['storefront_feature_4_icon'] ?? 'star'),
			'storefront_feature_4_title'       => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_4_title'] ?? '')) : ($omnify_existing['storefront_feature_4_title'] ?? 'Top Rated'),
			'storefront_feature_4_desc'        => $omnify_is_storefront_section ? sanitize_text_field(wp_unslash($_POST['storefront_feature_4_desc'] ?? '')) : ($omnify_existing['storefront_feature_4_desc'] ?? ''),
			'cart_countdown_enabled'           => $omnify_is_storefront_section ? ! empty($_POST['cart_countdown_enabled']) : ! empty($omnify_existing['cart_countdown_enabled']),
			'cart_countdown_duration'          => $omnify_is_storefront_section ? max(1, min(1440, absint(wp_unslash($_POST['cart_countdown_duration'] ?? 7)))) : ($omnify_existing['cart_countdown_duration'] ?? 7),
			'cart_free_shipping_enabled'       => $omnify_is_storefront_section ? ! empty($_POST['cart_free_shipping_enabled']) : ! empty($omnify_existing['cart_free_shipping_enabled']),
			'cart_free_shipping_threshold'     => $omnify_is_storefront_section ? max(0.0, (float) sanitize_text_field(wp_unslash($_POST['cart_free_shipping_threshold'] ?? 200.0))) : ($omnify_existing['cart_free_shipping_threshold'] ?? 200.0),
		];

		$omnify_settings_saved = $this->omnify_settings->update(array_merge($omnify_existing, $omnify_posted));

		if (! $omnify_settings_saved) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=' . $omnify_section . '&message=save_failed'));
			exit;
		}

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'save_settings',
			'setting',
			$omnify_section,
			sprintf(
				/* translators: %s: settings section name */
				__('Updated eCommerce settings section: %s.', 'omnifywp-ecommerce'),
				ucfirst($omnify_section)
			)
		);

		do_action('omnify_settings_saved', $omnify_section, $omnify_posted);

		wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=' . $omnify_section . '&message=saved'));
		exit;
	}

	public function handle_remove_shop_user(): void {
		$omnify_target_user_id = isset($_GET['user_id']) ? (int) wp_unslash($_GET['user_id']) : 0;
		check_admin_referer('omnify_remove_shop_user_' . $omnify_target_user_id);

		if (! current_user_can('manage_options') || ! current_user_can('edit_users')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_target_user_id === get_current_user_id()) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=roles&message=self_protected'));
			exit;
		}

		$omnify_target_user = get_userdata($omnify_target_user_id);
		if (! $omnify_target_user) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=roles&message=user_not_found'));
			exit;
		}

		$omnify_roles = (array) $omnify_target_user->roles;
		if (in_array('administrator', $omnify_roles, true)) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=roles&message=admin_protected'));
			exit;
		}

		// Revoke shop roles and custom shop capabilities
		$omnify_user = new \WP_User($omnify_target_user_id);
		$omnify_user->remove_role('shop_manager');
		$omnify_user->remove_role('shop_clerk');
		$omnify_user->remove_cap('manage_omnify');

		if (empty($omnify_user->roles)) {
			$omnify_user->set_role('subscriber');
		}

		$this->omnify_activities->log(
			get_current_user_id(),
			'remove_shop_role',
			'user',
			(string) $omnify_target_user_id,
			sprintf(
				/* translators: %s: username */
				__('Removed shop role and access for user: %s.', 'omnifywp-ecommerce'),
				$omnify_target_user->user_login
			)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=roles&message=user_removed'));
		exit;
	}

	public function handle_approve_order_risk(): void {
		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		check_admin_referer('omnify_approve_order_risk_' . $omnify_order_id, 'omnify_approve_order_risk_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_order_id) {
			$this->omnify_orders->update_fraud_info($omnify_order_id, 'safe', 0, null);

			$omnify_user = wp_get_current_user();
			$omnify_note = sprintf(
				/* translators: %s: administrator username */
				__('Order fraud flags cleared and approved by %s.', 'omnifywp-ecommerce'),
				$omnify_user->display_name ?: $omnify_user->user_login
			);
			$this->omnify_orders->add_note($omnify_order_id, $omnify_note, null, 'system');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'approve_order_risk',
				'order',
				(string) $omnify_order_id,
				// translators: %d: placeholder value.
				sprintf(__('Cleared fraud risk and approved Order #%d.', 'omnifywp-ecommerce'), $omnify_order_id)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_approved_risk'));
		exit;
	}

	public function handle_clear_activity_log(): void {
		check_admin_referer('omnify_clear_activity_log', 'omnify_clear_activity_log_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_activities->clear_all();

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'clear_activity_log',
			'activity_log',
			null,
			__('Cleared the administrative activity logs.', 'omnifywp-ecommerce')
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-activity&message=activity_log_cleared'));
		exit;
	}

	public function handle_mark_order_paid(): void {
		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		check_admin_referer('omnify_mark_order_paid_' . $omnify_order_id, 'omnify_mark_paid_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_order_id) {
			$this->omnify_orders->update_status($omnify_order_id, 'completed');
			$this->grant_order_download_access($omnify_order_id);
			do_action('omnify_order_completed', $omnify_order_id);
			$this->omnify_emails->send_receipt($omnify_order_id);
			$this->omnify_emails->send_admin_new_order($omnify_order_id);

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'mark_order_paid',
				'order',
				(string) $omnify_order_id,
				// translators: %d: placeholder value.
				sprintf(__('Marked Order #%d as paid.', 'omnifywp-ecommerce'), $omnify_order_id)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_marked_paid'));
		exit;
	}

	public function handle_update_order_status(): void {
		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		check_admin_referer('omnify_update_order_status_' . $omnify_order_id, 'omnify_update_status_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_status = sanitize_key(wp_unslash($_POST['order_status'] ?? ''));
		$omnify_valid_statuses = ['pending', 'pending_payment', 'completed', 'cancelled', 'processing', 'on_hold', 'refunded', 'failed', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested', 'returned'];

		if ($omnify_order_id && in_array($omnify_status, $omnify_valid_statuses, true)) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order && $omnify_order['status'] !== $omnify_status) {
				$omnify_old_status = $omnify_order['status'];
				$this->omnify_orders->update_status($omnify_order_id, $omnify_status);

				$omnify_note = sprintf(
					// translators: %1$s: placeholder value, %2$s: placeholder value.
					__('Order status changed from %1$s to %2$s.', 'omnifywp-ecommerce'),
					ucwords(str_replace('_', ' ', $omnify_old_status)),
					ucwords(str_replace('_', ' ', $omnify_status))
				);
				$this->omnify_orders->add_note($omnify_order_id, $omnify_note);

				if ($omnify_status === 'completed') {
					$this->grant_order_download_access($omnify_order_id);
					do_action('omnify_order_completed', $omnify_order_id);
				}

				$this->omnify_emails->send_status_changed($omnify_order_id, $omnify_old_status, $omnify_status);

				$omnify_user_id = get_current_user_id();
				$this->omnify_activities->log(
					$omnify_user_id,
					'update_order_status',
					'order',
					(string) $omnify_order_id,
					sprintf(
						// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value.
						__('Changed status of Order #%1$d from %2$s to %3$s.', 'omnifywp-ecommerce'),
						$omnify_order_id,
						ucwords(str_replace('_', ' ', $omnify_old_status)),
						ucwords(str_replace('_', ' ', $omnify_status))
					)
				);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_status_updated'));
		exit;
	}

	private function grant_order_download_access(int $omnify_order_id): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order || empty($omnify_order['customer_id']) || empty($omnify_order['items']) || ! is_array($omnify_order['items'])) {
			return;
		}

		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			$omnify_product = $omnify_product_id ? $this->omnify_products->find($omnify_product_id) : null;
			if (! $omnify_product) {
				continue;
			}

			$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
			$omnify_is_physical = 'physical' === ($omnify_product['type'] ?? 'download')
				|| ('variable' === ($omnify_product['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
			if ($omnify_is_physical) {
				continue;
			}

			$this->omnify_customer_access->grant((int) $omnify_order['customer_id'], $omnify_product_id, $this->download_access_expires_at($omnify_product));
		}
	}

	private function download_access_expires_at(array $omnify_product): ?string {
		$omnify_days = absint($omnify_product['download_expiry_days'] ?? 0);
		if ($omnify_days <= 0) {
			return null;
		}

		return gmdate('Y-m-d H:i:s', time() + ($omnify_days * 86400));
	}

	public function handle_delete_order(): void {
		$omnify_order_id = absint(wp_unslash($_GET['order_id'] ?? $_POST['order_id'] ?? 0));
		check_admin_referer('omnify_delete_order_' . $omnify_order_id);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_order_id) {
			$this->omnify_orders->delete($omnify_order_id);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=order_deleted'));
		exit;
	}

	public function handle_order_document(): void {
		$omnify_order_id = absint(wp_unslash($_GET['order_id'] ?? 0));
		$omnify_guest_token = sanitize_text_field(wp_unslash($_GET['guest_token'] ?? ''));

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			wp_die(esc_html__('Order not found.', 'omnifywp-ecommerce'));
		}

		$omnify_can_view = false;
		if ($omnify_guest_token && !empty($omnify_order['customer_email'])) {
			$omnify_expected_token = hash_hmac('sha256', $omnify_order_id . '|' . strtolower((string) $omnify_order['customer_email']), wp_salt('nonce'));
			if (hash_equals($omnify_expected_token, $omnify_guest_token)) {
				$omnify_can_view = true;
			}
		}

		if (! $omnify_can_view) {
			check_admin_referer('omnify_order_document_' . $omnify_order_id);
			if (current_user_can('manage_options')) {
				$omnify_can_view = true;
			} elseif (is_user_logged_in()) {
				$omnify_uid = get_current_user_id();
				$omnify_cust = method_exists($this->omnify_customers, 'find_by_user_id') ? $this->omnify_customers->find_by_user_id($omnify_uid) : null;
				if ($omnify_cust && (int)$omnify_cust['id'] === (int)$omnify_order['customer_id']) {
					$omnify_can_view = true;
				}
				$omnify_wp_user = get_userdata($omnify_uid);
				if ($omnify_wp_user && !empty($omnify_order['customer_email']) && strtolower($omnify_wp_user->user_email) === strtolower($omnify_order['customer_email'])) {
					$omnify_can_view = true;
				}
			}
		}

		if (! $omnify_can_view) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_type = sanitize_key((string) wp_unslash($_GET['document_type'] ?? 'order'));
		if (! in_array($omnify_type, ['order', 'invoice', 'packing_slip'], true)) {
			$omnify_type = 'order';
		}

		$omnify_format = sanitize_key((string) wp_unslash($_GET['format'] ?? 'html'));
		if ($omnify_format === 'pdf') {
			$this->output_order_pdf($omnify_order, $omnify_type);
			exit;
		}

		$this->render_order_document_html($omnify_order, $omnify_type);
		exit;
	}

	public function handle_cancel_order(): void {
		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		check_admin_referer('omnify_cancel_order_' . $omnify_order_id, 'omnify_cancel_order_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if ($omnify_order) {
			$omnify_old_status = (string) $omnify_order['status'];
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$omnify_reason = sanitize_text_field((string) wp_unslash($_POST['cancel_reason'] ?? ''));
			$omnify_restock = ! empty($_POST['restock_items']);
			$omnify_revoke = ! empty($_POST['revoke_access']);
			$omnify_notify = ! empty($_POST['add_customer_note']);

			if ($omnify_restock) {
				$this->restock_order_items($omnify_order, __('Cancellation restock', 'omnifywp-ecommerce'));
			}
			if ($omnify_revoke && ! empty($omnify_order['customer_id'])) {
				$this->revoke_order_access($omnify_order);
			}

			$this->omnify_orders->update_status($omnify_order_id, 'cancelled');
			$omnify_note = __('Order cancelled.', 'omnifywp-ecommerce');
			if ($omnify_reason) {
				// translators: %s: placeholder value.
				$omnify_note .= ' ' . sprintf(__('Reason: %s', 'omnifywp-ecommerce'), $omnify_reason);
			}
			if ($omnify_restock) {
				$omnify_note .= ' ' . __('Items were restocked.', 'omnifywp-ecommerce');
			}
			if ($omnify_revoke) {
				$omnify_note .= ' ' . __('Digital access was revoked.', 'omnifywp-ecommerce');
			}
			$this->omnify_orders->add_note($omnify_order_id, $omnify_note, null, 'system');

			if ($omnify_notify) {
				$this->omnify_orders->add_note($omnify_order_id, $omnify_reason ?: __('Your order has been cancelled.', 'omnifywp-ecommerce'), null, 'customer', true);
			}
			$this->omnify_emails->send_status_changed($omnify_order_id, $omnify_old_status, 'cancelled');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'cancel_order',
				'order',
				(string) $omnify_order_id,
				// translators: %1$d: placeholder value, %2$s: placeholder value.
				sprintf(__('Cancelled Order #%1$d. Reason: %2$s', 'omnifywp-ecommerce'), $omnify_order_id, $omnify_reason ?: __('None specified', 'omnifywp-ecommerce'))
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_cancelled'));
		exit;
	}

	public function handle_return_order(): void {
		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		check_admin_referer('omnify_return_order_' . $omnify_order_id, 'omnify_return_order_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if ($omnify_order) {
			$omnify_old_status = (string) $omnify_order['status'];
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$omnify_reason = sanitize_text_field((string) wp_unslash($_POST['return_reason'] ?? ''));
			$omnify_restock = ! empty($_POST['restock_items']);
			$omnify_revoke = ! empty($_POST['revoke_access']);
			$omnify_notify = ! empty($_POST['add_customer_note']);

			if ($omnify_restock) {
				$this->restock_order_items($omnify_order, __('Return restock', 'omnifywp-ecommerce'));
			}
			if ($omnify_revoke && ! empty($omnify_order['customer_id'])) {
				$this->revoke_order_access($omnify_order);
			}

			$this->omnify_orders->update_status($omnify_order_id, 'returned');
			$omnify_note = __('Order marked as returned.', 'omnifywp-ecommerce');
			if ($omnify_reason) {
				// translators: %s: placeholder value.
				$omnify_note .= ' ' . sprintf(__('Reason: %s', 'omnifywp-ecommerce'), $omnify_reason);
			}
			if ($omnify_restock) {
				$omnify_note .= ' ' . __('Returned items were restocked.', 'omnifywp-ecommerce');
			}
			if ($omnify_revoke) {
				$omnify_note .= ' ' . __('Digital access was revoked.', 'omnifywp-ecommerce');
			}
			$this->omnify_orders->add_note($omnify_order_id, $omnify_note, null, 'system');

			if ($omnify_notify) {
				$this->omnify_orders->add_note($omnify_order_id, $omnify_reason ?: __('Your return has been recorded.', 'omnifywp-ecommerce'), null, 'customer', true);
			}
			$this->omnify_emails->send_status_changed($omnify_order_id, $omnify_old_status, 'returned');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'return_order',
				'order',
				(string) $omnify_order_id,
				// translators: %1$d: placeholder value, %2$s: placeholder value.
				sprintf(__('Returned Order #%1$d. Reason: %2$s', 'omnifywp-ecommerce'), $omnify_order_id, $omnify_reason ?: __('None specified', 'omnifywp-ecommerce'))
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_returned'));
		exit;
	}

	private function restock_order_items(array $omnify_order, string $omnify_reason): void {
		global $wpdb;

		foreach ((array) ($omnify_order['items'] ?? []) as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			$omnify_qty = max(0, (int) ($omnify_item['quantity'] ?? 0) - (int) ($omnify_item['refunded_qty'] ?? 0));
			if ($omnify_product_id <= 0 || $omnify_qty <= 0) {
				continue;
			}

			$omnify_variation_id = ! empty($omnify_item['variation_id']) ? absint($omnify_item['variation_id']) : null;
			$omnify_product = $this->omnify_products->find($omnify_product_id);
			if ($omnify_variation_id) {
				$omnify_stock_row = null;
				foreach ((array) ($omnify_product['variations'] ?? []) as $omnify_variation) {
					if ((int) ($omnify_variation['id'] ?? 0) === $omnify_variation_id) {
						$omnify_stock_row = $omnify_variation;
						break;
					}
				}
			} else {
				$omnify_stock_row = $omnify_product;
			}
			if (! $omnify_stock_row || empty($omnify_stock_row['manage_stock'])) {
				continue;
			}

			$this->omnify_products->increment_stock($omnify_product_id, $omnify_variation_id, $omnify_qty);
			$omnify_new_qty = $omnify_variation_id
				? $this->omnify_products->get_variation_stock_qty($omnify_variation_id)
				: $this->omnify_products->get_product_stock_qty($omnify_product_id);
			$this->omnify_products->log_stock_change(
				$omnify_product_id,
				$omnify_variation_id,
				$omnify_qty,
				$omnify_new_qty,
				sprintf('%s (Order %s)', $omnify_reason, (string) ($omnify_order['order_number'] ?: '#' . $omnify_order['id']))
			);
		}
	}

	private function revoke_order_access(array $omnify_order): void {
		if (empty($omnify_order['customer_id'])) {
			return;
		}

		foreach ((array) ($omnify_order['items'] ?? []) as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			if ($omnify_product_id > 0) {
				$this->omnify_customer_access->revoke((int) $omnify_order['customer_id'], $omnify_product_id);
			}
		}
	}

	private function order_document_symbol(string $omnify_currency): string {
		return match (strtoupper($omnify_currency)) {
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CAD' => 'C$',
			'AUD' => 'A$',
			default => '$',
		};
	}

	private function render_order_document_html(array $omnify_order, string $omnify_type): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_store_name = (string) ($omnify_settings['store_name'] ?? get_bloginfo('name'));
		$omnify_store_email = (string) ($omnify_settings['store_email'] ?? get_option('admin_email'));
		$omnify_symbol = $this->order_document_symbol((string) $omnify_order['currency']);
		$omnify_title = match ($omnify_type) {
			'invoice' => __('Invoice / Receipt', 'omnifywp-ecommerce'),
			'packing_slip' => __('Packing Slip', 'omnifywp-ecommerce'),
			default => __('Order Printout', 'omnifywp-ecommerce'),
		};

		nocache_headers();
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		wp_enqueue_style('omnify-order-print', OMNIFY_URL . 'assets/admin/order-print.css', [], OMNIFY_VERSION);
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo('charset'); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<title><?php echo esc_html($omnify_title . ' - ' . ($omnify_order['order_number'] ?: '#' . $omnify_order['id'])); ?></title>
			<?php wp_print_styles(['omnify-order-print']); ?>
		</head>
		<body>
			<div class="toolbar">
				<button type="button" onclick="window.print()"><?php esc_html_e('Print / Save PDF', 'omnifywp-ecommerce'); ?></button>
			</div>
			<main class="document">
				<section class="header">
					<div>
						<h1><?php echo esc_html($omnify_title); ?></h1>
						<p><strong><?php echo esc_html($omnify_store_name); ?></strong></p>
						<p><?php echo esc_html($omnify_store_email); ?></p>
					</div>
					<div class="right">
						<p><strong><?php echo esc_html($omnify_order['order_number'] ?: '#' . $omnify_order['id']); ?></strong></p>
						<p><?php echo esc_html(date_i18n(get_option('date_format'), strtotime((string) $omnify_order['created_at']))); ?></p>
						<p><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $omnify_order['status']))); ?></p>
					</div>
				</section>

				<section>
					<h2><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></h2>
					<p><strong><?php echo esc_html($omnify_order['customer_name'] ?: $omnify_order['customer_email']); ?></strong></p>
					<p><?php echo esc_html($omnify_order['customer_email']); ?></p>
				</section>

				<?php if (! empty($omnify_order['shipping_address_1'])) : ?>
					<section>
						<h2><?php esc_html_e('Ship To', 'omnifywp-ecommerce'); ?></h2>
						<p><strong><?php echo esc_html(trim((string) $omnify_order['shipping_first_name'] . ' ' . (string) $omnify_order['shipping_last_name'])); ?></strong></p>
						<p><?php echo esc_html($omnify_order['shipping_address_1']); ?></p>
						<?php if (! empty($omnify_order['shipping_address_2'])) : ?><p><?php echo esc_html($omnify_order['shipping_address_2']); ?></p><?php endif; ?>
						<p><?php echo esc_html(trim((string) $omnify_order['shipping_city'] . ', ' . (string) $omnify_order['shipping_state'] . ' ' . (string) $omnify_order['shipping_postcode'])); ?></p>
						<p><?php echo esc_html($omnify_order['shipping_country']); ?></p>
					</section>
				<?php endif; ?>

				<section>
					<h2><?php echo 'packing_slip' === $omnify_type ? esc_html__('Items to Pack', 'omnifywp-ecommerce') : esc_html__('Order Items', 'omnifywp-ecommerce'); ?></h2>
					<table>
						<thead>
							<tr>
								<th><?php esc_html_e('Item', 'omnifywp-ecommerce'); ?></th>
								<th class="right"><?php esc_html_e('Qty', 'omnifywp-ecommerce'); ?></th>
								<?php if ('packing_slip' !== $omnify_type) : ?>
									<th class="right"><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></th>
									<th class="right"><?php esc_html_e('Tax', 'omnifywp-ecommerce'); ?></th>
									<th class="right"><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ((array) $omnify_order['items'] as $omnify_item) : ?>
								<tr>
									<td><?php echo esc_html((string) $omnify_item['product_name']); ?></td>
									<td class="right"><?php echo esc_html((string) $omnify_item['quantity']); ?></td>
									<?php if ('packing_slip' !== $omnify_type) : ?>
										<td class="right"><?php echo esc_html($omnify_symbol . number_format((float) $omnify_item['price'], 2)); ?></td>
										<td class="right"><?php echo esc_html($omnify_symbol . number_format((float) ($omnify_item['tax'] ?? 0), 2)); ?></td>
										<td class="right"><?php echo esc_html($omnify_symbol . number_format(((float) $omnify_item['price'] + (float) ($omnify_item['tax'] ?? 0)) * (int) $omnify_item['quantity'], 2)); ?></td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</section>

				<?php if ('packing_slip' !== $omnify_type) : ?>
					<section class="totals">
						<div><span><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?></span><span><?php echo esc_html($omnify_symbol . number_format((float) $omnify_order['subtotal'], 2)); ?></span></div>
						<?php if ((float) ($omnify_order['discount_amount'] ?? 0) > 0) : ?>
							<div><span><?php esc_html_e('Discount', 'omnifywp-ecommerce'); ?></span><span>-<?php echo esc_html($omnify_symbol . number_format((float) $omnify_order['discount_amount'], 2)); ?></span></div>
						<?php endif; ?>
						<div><span><?php echo esc_html((string) ($omnify_order['tax_label'] ?: __('Tax', 'omnifywp-ecommerce'))); ?></span><span><?php echo esc_html($omnify_symbol . number_format((float) $omnify_order['tax'], 2)); ?></span></div>
						<div><span><?php esc_html_e('Shipping', 'omnifywp-ecommerce'); ?></span><span><?php echo esc_html($omnify_symbol . number_format((float) ($omnify_order['shipping_total'] ?? 0), 2)); ?></span></div>
						<div class="total"><span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span><span><?php echo esc_html($omnify_symbol . number_format((float) $omnify_order['total'], 2)); ?></span></div>
					</section>
				<?php endif; ?>

				<?php
				// Billing address for invoice/receipt
				if ('packing_slip' !== $omnify_type && ! empty($omnify_order['billing_address_1'])) : ?>
					<section style="margin-top: 24px;">
						<h2><?php esc_html_e('Bill To', 'omnifywp-ecommerce'); ?></h2>
						<p><strong><?php echo esc_html(trim((string) ($omnify_order['billing_first_name'] ?? '') . ' ' . (string) ($omnify_order['billing_last_name'] ?? ''))); ?></strong></p>
						<?php if (! empty($omnify_order['billing_company'])) : ?><p><?php echo esc_html($omnify_order['billing_company']); ?></p><?php endif; ?>
						<p><?php echo esc_html($omnify_order['billing_address_1']); ?></p>
						<?php if (! empty($omnify_order['billing_address_2'])) : ?><p><?php echo esc_html($omnify_order['billing_address_2']); ?></p><?php endif; ?>
						<p><?php echo esc_html(trim((string) ($omnify_order['billing_city'] ?? '') . ', ' . (string) ($omnify_order['billing_state'] ?? '') . ' ' . (string) ($omnify_order['billing_postcode'] ?? ''))); ?></p>
						<p><?php echo esc_html($omnify_order['billing_country'] ?? ''); ?></p>
						<?php if (! empty($omnify_order['billing_phone'])) : ?><p><?php echo esc_html($omnify_order['billing_phone']); ?></p><?php endif; ?>
					</section>
				<?php endif; ?>

				<?php
				// Customer visible notes / order updates
				$omnify_visible_notes = array_filter((array) ($omnify_order['notes'] ?? []), fn($omnify_n) => ! empty($omnify_n['customer_visible']));
				if (! empty($omnify_visible_notes) && 'packing_slip' !== $omnify_type) : ?>
					<section style="margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 16px;">
						<h2><?php esc_html_e('Order Updates', 'omnifywp-ecommerce'); ?></h2>
						<?php foreach ($omnify_visible_notes as $omnify_note) : ?>
							<p style="font-size: 13px; margin: 4px 0; color: #374151;">
								<?php echo wp_kses_post((string) ($omnify_note['note'] ?? '')); ?>
								<?php if (! empty($omnify_note['created_at'])) : ?>
									<span style="color:#9ca3af; font-size:11px;"> — <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($omnify_note['created_at']))); ?></span>
								<?php endif; ?>
							</p>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>

				<?php if ('packing_slip' !== $omnify_type && ! empty($omnify_order['payment_method'])) : ?>
					<section style="margin-top: 20px; font-size: 13px; color: #4b5563;">
						<strong><?php esc_html_e('Payment Method:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $omnify_order['payment_method']))); ?>
						<?php if (! empty($omnify_order['transaction_id'])) : ?> (<?php echo esc_html(substr($omnify_order['transaction_id'], 0, 12)); ?>...)<?php endif; ?>
					</section>
				<?php endif; ?>

				<footer style="margin-top: 40px; font-size: 11px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 12px;">
					<?php echo esc_html($omnify_store_name); ?> • <?php echo esc_html($omnify_store_email); ?>
					<?php if (! empty($omnify_settings['store_url'])) : ?> • <?php echo esc_url($omnify_settings['store_url']); ?><?php endif; ?>
					<br><?php esc_html_e('Thank you for your business!', 'omnifywp-ecommerce'); ?>
				</footer>
			</main>
		</body>
		</html>
		<?php
	}

	private function output_order_pdf(array $omnify_order, string $omnify_type): void {
		$omnify_symbol = $this->order_document_symbol($omnify_order['currency'] ?? 'USD');
		$omnify_title = $omnify_type === 'invoice' ? 'Invoice' : ($omnify_type === 'packing_slip' ? 'Packing Slip' : 'Order');
		$omnify_order_num = $omnify_order['order_number'] ?: '#' . $omnify_order['id'];
		$omnify_date = date_i18n(get_option('date_format'), strtotime((string)$omnify_order['created_at']));

		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . sanitize_file_name($omnify_title . '-' . $omnify_order_num) . '.pdf"');

		// Richer pure PHP PDF layout using positioning, lines for "table"
		$omnify_esc = function($omnify_s) { return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $omnify_s); };

		$omnify_stream = "BT /F1 16 Tf 1 0 0 1 50 770 Tm (" . $omnify_esc($omnify_title . ' ' . $omnify_order_num) . ") Tj ET\n";
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 50 750 Tm (" . $omnify_esc('Date: ' . $omnify_date) . ") Tj ET\n";
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 50 735 Tm (" . $omnify_esc('Status: ' . ucwords(str_replace('_', ' ', $omnify_order['status'] ?? ''))) . ") Tj ET\n";

		// Customer / Bill To section
		$omnify_y = 710;
		$omnify_stream .= "BT /F1 11 Tf 1 0 0 1 50 {$omnify_y} Tm (Bill To:) Tj ET\n";
		$omnify_y -= 15;
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 50 {$omnify_y} Tm (" . $omnify_esc($omnify_order['customer_email'] ?? '') . ") Tj ET\n";
		if (!empty($omnify_order['billing_address_1'])) {
			$omnify_y -= 12;
			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 50 {$omnify_y} Tm (" . $omnify_esc(trim(($omnify_order['billing_first_name'] ?? '') . ' ' . ($omnify_order['billing_last_name'] ?? ''))) . ") Tj ET\n";
			$omnify_y -= 11;
			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 50 {$omnify_y} Tm (" . $omnify_esc($omnify_order['billing_address_1']) . ") Tj ET\n";
			if (!empty($omnify_order['billing_city'])) {
				$omnify_y -= 11;
				$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 50 {$omnify_y} Tm (" . $omnify_esc($omnify_order['billing_city'] . ', ' . ($omnify_order['billing_state'] ?? '') . ' ' . ($omnify_order['billing_postcode'] ?? '')) . ") Tj ET\n";
			}
		}

		// Items "table" header
		$omnify_y -= 30;
		$omnify_stream .= "q 0.5 w 50 {$omnify_y} m 560 {$omnify_y} l S Q\n"; // line
		$omnify_y -= 5;
		$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 50 {$omnify_y} Tm (Item) Tj ET\n";
		$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 380 {$omnify_y} Tm (Qty) Tj ET\n";
		$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 430 {$omnify_y} Tm (Price) Tj ET\n";
		$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 510 {$omnify_y} Tm (Total) Tj ET\n";
		$omnify_stream .= "q 0.5 w 50 " . ($omnify_y-2) . " m 560 " . ($omnify_y-2) . " l S Q\n";

		$omnify_y -= 15;
		$omnify_items_total = 0;
		foreach ((array)($omnify_order['items'] ?? []) as $omnify_item) {
			$omnify_name = substr($omnify_item['product_name'] ?? 'Item', 0, 45);
			$omnify_qty = (int)($omnify_item['quantity'] ?? 1);
			$omnify_price = (float)($omnify_item['price'] ?? 0);
			$omnify_line_total = $omnify_price * $omnify_qty;
			$omnify_items_total += $omnify_line_total;

			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 50 {$omnify_y} Tm (" . $omnify_esc($omnify_name) . ") Tj ET\n";
			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 380 {$omnify_y} Tm (" . $omnify_qty . ") Tj ET\n";
			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 430 {$omnify_y} Tm (" . $omnify_symbol . number_format($omnify_price, 2) . ") Tj ET\n";
			$omnify_stream .= "BT /F1 9 Tf 1 0 0 1 510 {$omnify_y} Tm (" . $omnify_symbol . number_format($omnify_line_total, 2) . ") Tj ET\n";
			$omnify_y -= 14;
			if ($omnify_y < 150) break; // prevent overflow
		}

		// Totals section
		$omnify_y -= 20;
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 380 {$omnify_y} Tm (Subtotal:) Tj ET BT /F1 10 Tf 1 0 0 1 510 {$omnify_y} Tm (" . $omnify_symbol . number_format((float)($omnify_order['subtotal'] ?? $omnify_items_total), 2) . ") Tj ET\n";
		$omnify_y -= 12;
		if ((float)($omnify_order['discount_amount'] ?? 0) > 0) {
			$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 380 {$omnify_y} Tm (Discount:) Tj ET BT /F1 10 Tf 1 0 0 1 510 {$omnify_y} Tm (-" . $omnify_symbol . number_format((float)$omnify_order['discount_amount'], 2) . ") Tj ET\n";
			$omnify_y -= 12;
		}
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 380 {$omnify_y} Tm (Tax:) Tj ET BT /F1 10 Tf 1 0 0 1 510 {$omnify_y} Tm (" . $omnify_symbol . number_format((float)($omnify_order['tax'] ?? 0), 2) . ") Tj ET\n";
		$omnify_y -= 12;
		$omnify_stream .= "BT /F1 10 Tf 1 0 0 1 380 {$omnify_y} Tm (Shipping:) Tj ET BT /F1 10 Tf 1 0 0 1 510 {$omnify_y} Tm (" . $omnify_symbol . number_format((float)($omnify_order['shipping_total'] ?? 0), 2) . ") Tj ET\n";
		$omnify_y -= 14;
		$omnify_stream .= "q 0.5 w 380 {$omnify_y} m 560 {$omnify_y} l S Q\n";
		$omnify_y -= 12;
		$omnify_stream .= "BT /F1 12 Tf 1 0 0 1 380 {$omnify_y} Tm (TOTAL:) Tj ET BT /F1 12 Tf 1 0 0 1 510 {$omnify_y} Tm (" . $omnify_symbol . number_format((float)$omnify_order['total'], 2) . ") Tj ET\n";

		// Footer
		$omnify_y = 60;
		$omnify_stream .= "BT /F1 8 Tf 1 0 0 1 50 {$omnify_y} Tm (Thank you for your business! Generated by Omnify) Tj ET\n";

		$omnify_len = strlen($omnify_stream);
		$omnify_pdf = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
		$omnify_pdf .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>\nendobj\n";
		$omnify_pdf .= "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>\nendobj\n";
		$omnify_pdf .= "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>\nendobj\n";
		$omnify_pdf .= "4 0 obj<</Length {$omnify_len}>>\nstream\n{$omnify_stream}endstream\nendobj\n";
		$omnify_pdf .= "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>\nendobj\n";
		$omnify_pdf .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000266 00000 n \n0000000" . sprintf('%04d', 300 + $omnify_len) . " 00000 n \ntrailer\n<</Size 6/Root 1 0 R>>\nstartxref\n" . (strlen($omnify_pdf) - 80) . "\n%%EOF";
		echo $omnify_pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputting raw PDF binary stream.
	}

	public function handle_ask_question(): void {
		check_admin_referer('omnify_ask_question', 'omnify_ask_nonce');
		$omnify_product_id = absint(wp_unslash($_POST['product_id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

		if ($omnify_product_id && $omnify_name && $omnify_email && $omnify_message) {
			// Log as customer activity or send email to admin
			if (isset($this->omnify_customers)) {
				$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
				if ($omnify_customer) {
					$this->omnify_customers->add_note((int)$omnify_customer['id'], "Question about product #$omnify_product_id: $omnify_message", true);
				}
			}
			// Simple admin email
			$omnify_admin_email = get_option('admin_email');
			wp_mail($omnify_admin_email, "New question for product #$omnify_product_id", "From: $omnify_name <$omnify_email>\n\n$omnify_message");
		}
		wp_safe_redirect(add_query_arg('ask', 'sent', wp_get_referer() ?: home_url()));
		exit;
	}

	public function handle_refund_order(): void {
		check_admin_referer('omnify_refund_order', 'omnify_refund_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-orders&message=order_not_found'));
			exit;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$omnify_refund_items = (array) wp_unslash($_POST['refund_items']) ?? [];
		$omnify_restock = ! empty($_POST['restock_items']);
		$omnify_revoke = ! empty($_POST['revoke_access']);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$omnify_shipping_refund = (float) wp_unslash($_POST['refund_shipping'] ?? 0.0);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$omnify_tax_refund = (float) wp_unslash($_POST['refund_tax'] ?? 0.0);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_reason = sanitize_text_field(wp_unslash($_POST['refund_reason'] ?? ''));

		// Calculate total refund amount from item inputs, shipping, and tax
		$omnify_total_refund = 0.0;
		$omnify_refunded_items_details = [];
		$omnify_prices_include_tax = ! empty($omnify_order['tax_inclusive']);

		if (! empty($omnify_refund_items) && is_array($omnify_refund_items)) {
			foreach ($omnify_order['items'] as $omnify_item) {
				$omnify_item_id = (int) $omnify_item['id'];
				if (isset($omnify_refund_items[$omnify_item_id]) && ! empty($omnify_refund_items[$omnify_item_id]['checked'])) {
					$omnify_qty = min((int) $omnify_item['quantity'] - (int) ($omnify_item['refunded_qty'] ?? 0), max(0, (int) $omnify_refund_items[$omnify_item_id]['qty']));
					if ($omnify_qty > 0) {
						$omnify_item_price = (float) $omnify_item['price'];
						$omnify_item_tax = (float) $omnify_item['tax'];
						$omnify_item_total_refund = ($omnify_prices_include_tax ? $omnify_item_price : ($omnify_item_price + $omnify_item_tax)) * $omnify_qty;
						$omnify_total_refund += $omnify_item_total_refund;

						$omnify_refunded_items_details[] = [
							'id' => $omnify_item_id,
							'product_id' => (int) $omnify_item['product_id'],
							'variation_id' => isset($omnify_item['variation_id']) ? (int) $omnify_item['variation_id'] : null,
							'product_name' => $omnify_item['product_name'],
							'qty' => $omnify_qty,
							'amount' => $omnify_item_total_refund
						];
					}
				}
			}
		}

		// Add shipping and tax refund
		$omnify_total_refund += $omnify_shipping_refund;
		$omnify_total_refund += $omnify_tax_refund;

		// Maximum refundable balance check
		$omnify_refundable_balance = max(0.0, (float) $omnify_order['total'] - (float) $omnify_order['refunded_amount']);
		if ($omnify_total_refund <= 0.0) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=refund_amount_zero'));
			exit;
		}

		if ($omnify_total_refund > $omnify_refundable_balance + 0.009) {
			$omnify_total_refund = $omnify_refundable_balance;
		}

		$omnify_gateway_refund_id = '';
		$omnify_payment_method = $omnify_order['payment_method'] ?? 'manual';
		$omnify_transaction_id = $omnify_order['transaction_id'] ?? '';

		$omnify_supported_gateways = ['card', 'paypal', 'razorpay', 'alipay', 'wechat', 'sslcommerz', 'paystack', 'tap', 'mollie', 'khalti', 'esewa'];
		if (in_array($omnify_payment_method, $omnify_supported_gateways, true)) {
			if (empty($omnify_transaction_id)) {
				wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=refund_failed&err=' . urlencode(__('Live refund failed: No transaction ID associated with this order.', 'omnifywp-ecommerce'))));
				exit;
			}

			$omnify_res = null;
			$omnify_error_prefix = '';

			switch ($omnify_payment_method) {
				case 'card':
					$omnify_res = $this->omnify_payment_gateways->refund_stripe_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'USD');
					$omnify_error_prefix = 'Stripe';
					break;
				case 'paypal':
					$omnify_res = $this->omnify_payment_gateways->refund_paypal_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'USD');
					$omnify_error_prefix = 'PayPal';
					break;
				case 'razorpay':
					$omnify_res = $this->omnify_payment_gateways->refund_razorpay_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'INR');
					$omnify_error_prefix = 'Razorpay';
					break;
				case 'alipay':
					$omnify_res = $this->omnify_payment_gateways->refund_alipay_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'CNY');
					$omnify_error_prefix = 'Alipay';
					break;
				case 'wechat':
					$omnify_res = $this->omnify_payment_gateways->refund_wechat_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'CNY');
					$omnify_error_prefix = 'WeChat';
					break;
				case 'sslcommerz':
					$omnify_res = $this->omnify_payment_gateways->refund_sslcommerz_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'BDT');
					$omnify_error_prefix = 'SSLCommerz';
					break;
				case 'paystack':
					$omnify_res = $this->omnify_payment_gateways->refund_paystack_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'NGN');
					$omnify_error_prefix = 'Paystack';
					break;
				case 'tap':
					$omnify_res = $this->omnify_payment_gateways->refund_tap_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'SAR');
					$omnify_error_prefix = 'Tap';
					break;
				case 'mollie':
					$omnify_res = $this->omnify_payment_gateways->refund_mollie_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'EUR');
					$omnify_error_prefix = 'Mollie';
					break;
				case 'khalti':
					$omnify_res = $this->omnify_payment_gateways->refund_khalti_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'NPR');
					$omnify_error_prefix = 'Khalti';
					break;
				case 'esewa':
					$omnify_res = $this->omnify_payment_gateways->refund_esewa_payment($omnify_transaction_id, $omnify_total_refund, $omnify_order['currency'] ?? 'NPR');
					$omnify_error_prefix = 'eSewa';
					break;
			}

			if (is_wp_error($omnify_res)) {
				// translators: %1$s: placeholder value, %2$s: placeholder value.
				wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=refund_failed&err=' . urlencode(sprintf(__('%1$s Refund Error: %2$s', 'omnifywp-ecommerce'), $omnify_error_prefix, $omnify_res->get_error_message()))));
				exit;
			}
			if ($omnify_res) {
				$omnify_gateway_refund_id = $omnify_res;
			}
		}

		// Perform database updates
		$this->omnify_orders->begin_transaction();

		// 1. Update order refunded amount and status
		$omnify_new_refunded = min((float) $omnify_order['total'], (float) $omnify_order['refunded_amount'] + $omnify_total_refund);
		$omnify_status = $omnify_order['status'];
		if ($omnify_new_refunded >= (float) $omnify_order['total'] - 0.009) {
			$omnify_status = 'refunded';
		}

		$this->omnify_orders->update_refund_summary($omnify_order_id, $omnify_new_refunded, $omnify_status);

		// 2. Update order items refunded quantities
		foreach ($omnify_refunded_items_details as $omnify_detail) {
			$this->omnify_orders->increment_item_refunded_qty((int) $omnify_detail['id'], (int) $omnify_detail['qty']);

			// 3. Restock inventory if requested
			if ($omnify_restock && $omnify_detail['product_id'] > 0) {
				$this->omnify_products->increment_stock($omnify_detail['product_id'], $omnify_detail['variation_id'], $omnify_detail['qty']);

				$omnify_new_qty = $omnify_detail['variation_id'] > 0
					? $this->omnify_products->get_variation_stock_qty((int) $omnify_detail['variation_id'])
					: $this->omnify_products->get_product_stock_qty((int) $omnify_detail['product_id']);

				$this->omnify_products->log_stock_change(
					$omnify_detail['product_id'],
					$omnify_detail['variation_id'],
					$omnify_detail['qty'],
					$omnify_new_qty,
					// translators: %d: placeholder value.
					sprintf(__('Refund restock (Order #%d)', 'omnifywp-ecommerce'), $omnify_order_id)
				);
			}

			// 4. Revoke customer digital access if requested
			if ($omnify_revoke && $omnify_order['customer_id'] && $omnify_detail['product_id'] > 0) {
				$this->omnify_customer_access->revoke((int) $omnify_order['customer_id'], $omnify_detail['product_id']);
			}
		}

		$this->omnify_orders->commit_transaction();

		// Build timeline note
		$omnify_note_parts = [];
		foreach ($omnify_refunded_items_details as $omnify_detail) {
			$omnify_note_parts[] = sprintf('%dx %s', $omnify_detail['qty'], $omnify_detail['product_name']);
		}
		
		$omnify_note_details = '';
		if (! empty($omnify_note_parts)) {
			$omnify_note_details = __('Refunded: ', 'omnifywp-ecommerce') . implode(', ', $omnify_note_parts) . '. ';
		}
		if ($omnify_shipping_refund > 0) {
			// translators: %s: placeholder value.
			$omnify_note_details .= sprintf(__('Shipping Refunded: %s. ', 'omnifywp-ecommerce'), $omnify_order['currency'] . ' ' . number_format($omnify_shipping_refund, 2));
		}
		if ($omnify_tax_refund > 0) {
			// translators: %s: placeholder value.
			$omnify_note_details .= sprintf(__('Tax Refunded: %s. ', 'omnifywp-ecommerce'), $omnify_order['currency'] . ' ' . number_format($omnify_tax_refund, 2));
		}
		if ($omnify_restock) {
			$omnify_note_details .= __('Restocked items. ', 'omnifywp-ecommerce');
		}
		if ($omnify_revoke) {
			$omnify_note_details .= __('Revoked customer access. ', 'omnifywp-ecommerce');
		}
		if ($omnify_reason) {
			// translators: %s: placeholder value.
			$omnify_note_details .= sprintf(__('Reason: %s. ', 'omnifywp-ecommerce'), $omnify_reason);
		}
		if ($omnify_gateway_refund_id) {
			// translators: %s: placeholder value.
			$omnify_note_details .= sprintf(__('Gateway Refund ID: %s. ', 'omnifywp-ecommerce'), $omnify_gateway_refund_id);
		}

		$this->omnify_orders->add_note(
			$omnify_order_id,
			sprintf(
				/* translators: 1: refund amount, 2: refund details */
				__('Refunded %1$s. %2$s', 'omnifywp-ecommerce'),
				$omnify_order['currency'] . ' ' . number_format($omnify_total_refund, 2),
				$omnify_note_details
			)
		);

		$this->omnify_emails->send_refund_processed($omnify_order_id, $omnify_total_refund);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'refund_order',
			'order',
			(string) $omnify_order_id,
			sprintf(
				// translators: %1$s: placeholder value, %2$d: placeholder value, %3$s: placeholder value.
				__('Refunded %1$s for Order #%2$d. Reason: %3$s', 'omnifywp-ecommerce'),
				$omnify_order['currency'] . ' ' . number_format($omnify_total_refund, 2),
				$omnify_order_id,
				$omnify_reason ?: __('None specified', 'omnifywp-ecommerce')
			)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=refund_processed'));
		exit;
	}

	public function handle_fulfill_order(): void {
		check_admin_referer('omnify_fulfill_order', 'omnify_fulfill_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_carrier  = sanitize_text_field(wp_unslash($_POST['tracking_carrier'] ?? ''));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_tracking = sanitize_text_field(wp_unslash($_POST['tracking_number'] ?? ''));
		$omnify_status   = sanitize_key(wp_unslash($_POST['fulfillment_status'] ?? 'fulfilled'));

		if ($omnify_order_id) {
			$this->omnify_orders->update_fulfillment($omnify_order_id, $omnify_status, $omnify_carrier, $omnify_tracking);

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'fulfill_order',
				'order',
				(string) $omnify_order_id,
				sprintf(
					// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value, %4$s: placeholder value.
					__('Updated fulfillment status for Order #%1$d to "%2$s" (Carrier: %3$s, Tracking: %4$s).', 'omnifywp-ecommerce'),
					$omnify_order_id,
					ucfirst($omnify_status),
					$omnify_carrier ?: __('N/A', 'omnifywp-ecommerce'),
					$omnify_tracking ?: __('N/A', 'omnifywp-ecommerce')
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_fulfilled'));
		exit;
	}

	public function handle_add_order_note(): void {
		check_admin_referer('omnify_add_order_note', 'omnify_note_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_note     = wp_kses_post(wp_unslash($_POST['note'] ?? ''));
		$omnify_note_type = sanitize_key((string) wp_unslash($_POST['note_type'] ?? 'private'));

		if ($omnify_order_id && !empty($omnify_note)) {
			$this->omnify_orders->add_note($omnify_order_id, $omnify_note, null, $omnify_note_type, 'customer' === $omnify_note_type);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_note_added'));
		exit;
	}

	public function handle_delete_order_note(): void {
		$omnify_order_id = absint(wp_unslash($_GET['order_id'] ?? 0));
		$omnify_note_id  = absint(wp_unslash($_GET['note_id'] ?? 0));
		check_admin_referer('omnify_delete_order_note_' . $omnify_note_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_orders->delete_note($omnify_order_id, $omnify_note_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=order_note_deleted'));
		exit;
	}

	public function handle_generate_pages(): void {
		check_admin_referer('omnify_generate_pages', 'omnify_generate_pages_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_pages = [
			'storefront' => [
				'title'   => __('Storefront', 'omnifywp-ecommerce'),
				'content' => '[omnify_storefront]',
				'slug'    => 'storefront',
			],
			'cart' => [
				'title'   => __('Cart', 'omnifywp-ecommerce'),
				'content' => '[omnify_cart]',
				'slug'    => 'cart',
			],
			'customer_portal' => [
				'title'   => __('Customer Portal', 'omnifywp-ecommerce'),
				'content' => '[omnify_customer_portal]',
				'slug'    => 'customer-portal',
			],
			'download_page' => [
				'title'   => __('Secure Download', 'omnifywp-ecommerce'),
				'content' => '[omnify_download_page]',
				'slug'    => 'secure-download',
			],
			'checkout' => [
				'title'   => __('Checkout', 'omnifywp-ecommerce'),
				'content' => '[omnify_checkout]',
				'slug'    => 'checkout',
			],
			'order_tracking' => [
				'title'   => __('Order Tracking', 'omnifywp-ecommerce'),
				'content' => '[omnify_order_tracking]',
				'slug'    => 'order-tracking',
			],
		];

		$omnify_generated = 0;
		$omnify_failed = 0;
		$omnify_existing_settings = $this->omnify_settings->all();
		$omnify_page_settings = [];
		foreach ($omnify_pages as $omnify_key => $omnify_page_info) {
			$omnify_existing = get_posts([
				'post_type'   => 'page',
				's'           => $omnify_page_info['content'],
				'post_status' => 'any',
			]);

			if (empty($omnify_existing)) {
				$omnify_post_id = wp_insert_post([
					'post_title'   => $omnify_page_info['title'],
					'post_content' => $omnify_page_info['content'],
					'post_name'    => $omnify_page_info['slug'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				]);
				if ($omnify_post_id && !is_wp_error($omnify_post_id)) {
					$omnify_generated++;
					$omnify_page_settings['page_' . $omnify_key] = (int) $omnify_post_id;
				} else {
					$omnify_failed++;
				}
			} else {
				$omnify_page_settings['page_' . $omnify_key] = (int) $omnify_existing[0]->ID;
			}
		}

		if (! empty($omnify_page_settings)) {
			$this->omnify_settings->update(array_merge($omnify_existing_settings, $omnify_page_settings));
		}

		$omnify_message = $omnify_failed > 0 ? 'pages_generate_failed' : 'pages_generated';
		wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=pages&message=' . $omnify_message . '&count=' . $omnify_generated . '&failed=' . $omnify_failed));
		exit;
	}

	public function handle_resend_receipt(): void {
		check_admin_referer('omnify_resend_receipt', 'omnify_resend_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_order_id = absint(wp_unslash($_POST['order_id'] ?? 0));

		if ($omnify_order_id) {
			$omnify_sent = $this->omnify_emails->send_receipt($omnify_order_id);

			// Log an internal order note about resending
			$this->omnify_orders->add_note(
				$omnify_order_id,
				$omnify_sent
					? __('Receipt email resent to customer.', 'omnifywp-ecommerce')
					: __('Receipt email resend failed. Check the email log for details.', 'omnifywp-ecommerce')
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order_id . '&message=' . (! empty($omnify_sent) ? 'receipt_resent' : 'receipt_failed')));
		exit;
	}

	public function handle_send_test_email(): void {
		check_admin_referer('omnify_send_test_email', 'omnify_test_email_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_to   = sanitize_email((string) wp_unslash($_POST['test_email_to'] ?? get_option('admin_email')));
		$omnify_sent = $this->omnify_emails->send_test_email($omnify_to ?: (string) get_option('admin_email'));

		wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=email&message=' . ($omnify_sent ? 'email_test_sent' : 'email_test_failed')));
		exit;
	}

	public function handle_resend_email_log(): void {
		$omnify_log_id = absint(wp_unslash($_GET['log_id'] ?? 0));
		check_admin_referer('omnify_resend_email_log_' . $omnify_log_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_sent = $omnify_log_id > 0 ? $this->omnify_emails->resend_logged_email($omnify_log_id) : false;

		wp_safe_redirect(admin_url('admin.php?page=omnify-settings&section=email&message=' . ($omnify_sent ? 'email_resent' : 'email_resend_failed')));
		exit;
	}

	public function handle_save_coupon(): void {
		check_admin_referer('omnify_save_coupon', 'omnify_coupon_nonce');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_id = absint(wp_unslash($_POST['id'] ?? 0));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$omnify_code = strtoupper(sanitize_text_field(trim((string) wp_unslash($_POST['code'] ?? ''))));

		$omnify_existing = $this->omnify_coupons->find_by_code($omnify_code);
		if ($omnify_existing && (int) $omnify_existing['id'] !== $omnify_id) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=coupon_exists'));
			exit;
		}

		$omnify_csv_from_post = static function (string $omnify_key, bool $omnify_numeric = false): string {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_value = isset($_POST[$omnify_key]) ? wp_unslash($_POST[$omnify_key]) : '';
			$omnify_items = is_array($omnify_value) ? $omnify_value : explode(',', (string) $omnify_value);
			$omnify_items = array_map(static function ($omnify_item) use ($omnify_numeric) {
				return $omnify_numeric ? (string) absint($omnify_item) : sanitize_text_field((string) $omnify_item);
			}, $omnify_items);
			$omnify_items = array_filter(array_map('trim', $omnify_items), static function ($omnify_item) {
				return '' !== $omnify_item && '0' !== $omnify_item;
			});
			return implode(',', array_values(array_unique($omnify_items)));
		};

			$omnify_data = [
				'code'           => $omnify_code,
				'is_active'      => ! empty($_POST['is_active']),
				'discount_type'  => sanitize_key(wp_unslash($_POST['discount_type'] ?? 'fixed')),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'discount_value' => (float) wp_unslash($_POST['discount_value'] ?? 0.0),
				'min_order_amount'    => isset($_POST['min_order_amount']) && '' !== trim((string) wp_unslash($_POST['min_order_amount'])) ? (float) wp_unslash($_POST['min_order_amount']) : null,
				'max_discount_amount' => isset($_POST['max_discount_amount']) && '' !== trim((string) wp_unslash($_POST['max_discount_amount'])) ? (float) wp_unslash($_POST['max_discount_amount']) : null,
				'usage_limit'    => !empty($_POST['usage_limit']) ? absint(wp_unslash($_POST['usage_limit'])) : null,
				'usage_limit_per_customer' => !empty($_POST['usage_limit_per_customer']) ? absint(wp_unslash($_POST['usage_limit_per_customer'])) : null,
				'free_shipping'  => ! empty($_POST['free_shipping']),
				'first_order_only' => ! empty($_POST['first_order_only']),
				'included_product_ids' => $omnify_csv_from_post('included_product_ids', true),
				'excluded_product_ids' => $omnify_csv_from_post('excluded_product_ids', true),
				'included_categories' => $omnify_csv_from_post('included_categories'),
				'excluded_categories' => $omnify_csv_from_post('excluded_categories'),
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'expires_at'     => !empty($_POST['expires_at']) ? sanitize_text_field(wp_unslash($_POST['expires_at'])) : null,
			];

		if ($omnify_id) {
			$this->omnify_coupons->update($omnify_id, $omnify_data);
		} else {
			$omnify_id = $this->omnify_coupons->create($omnify_data);
		}

		$omnify_user_id = get_current_user_id();
		$omnify_activity_action = $omnify_id ? 'edit_coupon' : 'create_coupon';
		if ($omnify_activity_action === 'edit_coupon') {
			/* translators: %1$s: coupon code, %2$d: coupon ID. */
			$omnify_activity_message = sprintf(__('Updated coupon "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_code, $omnify_id);
		} else {
			/* translators: %1$s: coupon code, %2$d: coupon ID. */
			$omnify_activity_message = sprintf(__('Created coupon "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_code, $omnify_id);
		}

		$this->omnify_activities->log(
			$omnify_user_id,
			$omnify_activity_action,
			'coupon',
			(string) $omnify_id,
			$omnify_activity_message
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=coupon_saved'));
		exit;
	}

	public function handle_delete_coupon(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_delete_coupon_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_coupon = $this->omnify_coupons->find($omnify_id);
		$omnify_code = $omnify_coupon ? ($omnify_coupon['code'] ?? '') : '';

		$this->omnify_coupons->delete($omnify_id);

		$omnify_user_id = get_current_user_id();
		$this->omnify_activities->log(
			$omnify_user_id,
			'delete_coupon',
			'coupon',
			(string) $omnify_id,
			// translators: %1$s: placeholder value, %2$d: placeholder value.
			sprintf(__('Deleted coupon "%1$s" (ID: %2$d).', 'omnifywp-ecommerce'), $omnify_code ?: 'Unknown', $omnify_id)
		);

		wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=coupon_deleted'));
		exit;
	}

	public function handle_trash_coupon(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_trash_coupon_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_coupons->trash($omnify_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=trashed'));
		exit;
	}

	public function handle_restore_coupon(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_restore_coupon_' . $omnify_id);

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->omnify_coupons->restore($omnify_id);

		wp_safe_redirect(admin_url('admin.php?page=omnify-coupons&message=restored'));
		exit;
	}

	public function handle_delete_abandoned_cart(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_delete_abandoned_cart_' . $omnify_id);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		if ($omnify_id) {
			$this->omnify_abandoned_carts->delete($omnify_id);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-abandoned-carts&message=abandoned_cart_deleted'));
		exit;
	}

	public function handle_send_abandoned_cart_email(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_send_abandoned_cart_email_' . $omnify_id);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_cart = $omnify_id ? $this->omnify_abandoned_carts->find($omnify_id) : null;
		if (! $omnify_cart) {
			wp_safe_redirect(admin_url('admin.php?page=omnify-abandoned-carts&message=abandoned_cart_not_found'));
			exit;
		}

		if ($this->omnify_emails->send_abandoned_cart_recovery($omnify_cart)) {
			$this->omnify_abandoned_carts->mark_sent($omnify_id);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-abandoned-carts&message=abandoned_cart_email_sent'));
		exit;
	}

	public function handle_export_products(): void {
		check_admin_referer('omnify_export_products');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->send_csv_headers('omnify-products');

		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['id', 'name', 'slug', 'type', 'product_kind', 'sales_type', 'price', 'sale_price', 'currency', 'sku', 'status', 'stock_status', 'manage_stock', 'stock_qty', 'allow_backorders', 'preorder_enabled', 'preorder_release_date', 'preorder_limit', 'preorder_message', 'shipping_class', 'weight', 'length', 'width', 'height', 'download_expiry_days', 'categories', 'tags', 'description', 'created_at']);

		$omnify_offset = 0;
		do {
			$omnify_products = $this->omnify_products->all(['per_page' => 100, 'offset' => $omnify_offset]);
			foreach ($omnify_products as $omnify_prod) {
				fputcsv($omnify_output, [
					$omnify_prod['id'],
					$omnify_prod['name'],
					$omnify_prod['slug'],
					$omnify_prod['type'],
					$omnify_prod['product_kind'] ?? '',
					$omnify_prod['product_structure'] ?? '',
					$omnify_prod['price'],
					$omnify_prod['sale_price'] ?? '',
					$omnify_prod['currency'],
					$omnify_prod['sku'] ?? '',
					$omnify_prod['status'],
					$omnify_prod['stock_status'] ?? '',
					! empty($omnify_prod['manage_stock']) ? 1 : 0,
					$omnify_prod['stock_qty'] ?? '',
					! empty($omnify_prod['allow_backorders']) ? 1 : 0,
					! empty($omnify_prod['preorder_enabled']) ? 1 : 0,
					$omnify_prod['preorder_release_date'] ?? '',
					$omnify_prod['preorder_limit'] ?? '',
					$omnify_prod['preorder_message'] ?? '',
					$omnify_prod['shipping_class'] ?? '',
					$omnify_prod['weight'] ?? '',
					$omnify_prod['length'] ?? '',
					$omnify_prod['width'] ?? '',
					$omnify_prod['height'] ?? '',
					$omnify_prod['download_expiry_days'] ?? 0,
					implode(', ', $omnify_prod['categories']),
					implode(', ', $omnify_prod['tags']),
					$omnify_prod['description'],
					$omnify_prod['created_at']
				]);
			}
			$omnify_offset += count($omnify_products);
		} while (100 === count($omnify_products));

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_products_xml(): void {
		check_admin_referer('omnify_export_products_xml');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		header('Content-Type: application/xml; charset=utf-8');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		header('Content-Disposition: attachment; filename=omnify-products-' . date('Y-m-d') . '.xml');

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<products>' . "\n";

		$omnify_offset = 0;
		do {
			$omnify_products = $this->omnify_products->all(['per_page' => 100, 'offset' => $omnify_offset]);
			foreach ($omnify_products as $omnify_prod) {
				echo '  <product>' . "\n";
				echo '    <id>' . (int)$omnify_prod['id'] . '</id>' . "\n";
				echo '    <name>' . esc_html($omnify_prod['name']) . '</name>' . "\n";
				echo '    <slug>' . esc_html($omnify_prod['slug']) . '</slug>' . "\n";
				echo '    <type>' . esc_html($omnify_prod['type']) . '</type>' . "\n";
				echo '    <price>' . (float)$omnify_prod['price'] . '</price>' . "\n";
				if (!empty($omnify_prod['sale_price'])) echo '    <sale_price>' . (float)$omnify_prod['sale_price'] . '</sale_price>' . "\n";
				echo '    <status>' . esc_html($omnify_prod['status']) . '</status>' . "\n";
				echo '    <sku>' . esc_html($omnify_prod['sku'] ?? '') . '</sku>' . "\n";
				// Richer nested structure
				if (!empty($omnify_prod['categories'])) {
					echo '    <categories>' . "\n";
					foreach ((array)$omnify_prod['categories'] as $omnify_cat) {
						echo '      <category>' . esc_html(is_array($omnify_cat) ? ($omnify_cat['name'] ?? $omnify_cat) : $omnify_cat) . '</category>' . "\n";
					}
					echo '    </categories>' . "\n";
				}
				if (!empty($omnify_prod['tags'])) {
					echo '    <tags>' . "\n";
					foreach ((array)$omnify_prod['tags'] as $omnify_tag) {
						echo '      <tag>' . esc_html(is_array($omnify_tag) ? ($omnify_tag['name'] ?? $omnify_tag) : $omnify_tag) . '</tag>' . "\n";
					}
					echo '    </tags>' . "\n";
				}
				// Variations nested
				$omnify_vars = $this->omnify_products->get_variations((int)$omnify_prod['id']);
				if (!empty($omnify_vars)) {
					echo '    <variations>' . "\n";
					foreach ($omnify_vars as $omnify_var) {
						echo '      <variation>' . "\n";
						echo '        <id>' . (int)$omnify_var['id'] . '</id>' . "\n";
						echo '        <sku>' . esc_html($omnify_var['sku'] ?? '') . '</sku>' . "\n";
						echo '        <price>' . (float)($omnify_var['price'] ?? 0) . '</price>' . "\n";
						if (!empty($omnify_var['attributes'])) {
							$omnify_attrs = is_string($omnify_var['attributes']) ? json_decode($omnify_var['attributes'], true) : $omnify_var['attributes'];
							echo '        <attributes>' . esc_html(wp_json_encode($omnify_attrs)) . '</attributes>' . "\n";
						}
						echo '      </variation>' . "\n";
					}
					echo '    </variations>' . "\n";
				}
				echo '    <description>' . esc_html($omnify_prod['description'] ?? '') . '</description>' . "\n";
				echo '    <created_at>' . esc_html($omnify_prod['created_at'] ?? '') . '</created_at>' . "\n";
				echo '  </product>' . "\n";
			}
			$omnify_offset += count($omnify_products);
		} while (100 === count($omnify_products));

		echo '</products>';
		exit;
	}

	public function handle_export_customers(): void {
		check_admin_referer('omnify_export_customers');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->send_csv_headers('omnify-customers');

		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['id', 'first_name', 'last_name', 'email', 'status', 'phone', 'company', 'country', 'tax_exempt', 'created_at']);

		$omnify_offset = 0;
		do {
			$omnify_customers = $this->omnify_customers->all(['per_page' => 100, 'offset' => $omnify_offset]);
			foreach ($omnify_customers as $omnify_cust) {
				fputcsv($omnify_output, [
					$omnify_cust['id'],
					$omnify_cust['first_name'],
					$omnify_cust['last_name'],
					$omnify_cust['email'],
					$omnify_cust['status'],
					$omnify_cust['phone'],
					$omnify_cust['company'],
					$omnify_cust['country'],
					! empty($omnify_cust['tax_exempt']) ? 1 : 0,
					$omnify_cust['created_at']
				]);
			}
			$omnify_offset += count($omnify_customers);
		} while (100 === count($omnify_customers));

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_downloads(): void {
		check_admin_referer('omnify_export_downloads');

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		global $wpdb;
		$omnify_downloads_table = $wpdb->prefix . 'omnify_downloads';
		$omnify_products_table = $wpdb->prefix . 'omnify_products';
		$omnify_files_table = $wpdb->prefix . 'omnify_product_files';
		$omnify_customers_table = $wpdb->prefix . 'omnify_customers';

		$omnify_sql = "
			SELECT
				d.id,
				p.name as product_name,
				f.file_name as file_name,
				c.email as customer_email,
				d.status,
				d.ip_address,
				d.user_agent,
				d.downloaded_at
			FROM {$omnify_downloads_table} d
			LEFT JOIN {$omnify_products_table} p ON d.product_id = p.id
			LEFT JOIN {$omnify_files_table} f ON d.file_id = f.id
			LEFT JOIN {$omnify_customers_table} c ON d.customer_id = c.id
			ORDER BY d.downloaded_at DESC
			LIMIT 10000
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = $this->cached_db_get_results('export_downloads', $omnify_sql, 30);

		// Set CSV headers
		header('Content-Type: text/csv; charset=utf-8');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		header('Content-Disposition: attachment; filename=omnify-downloads-log-' . date('Y-m-d') . '.csv');

		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['Log ID', 'Product Name', 'File Name', 'Customer Email', 'Download Status', 'IP Address', 'User Agent', 'Date & Time']);

		foreach (is_array($omnify_rows) ? $omnify_rows : [] as $omnify_row) {
			fputcsv($omnify_output, [
				$omnify_row['id'],
				$omnify_row['product_name'] ?: 'Unknown Product',
				$omnify_row['file_name'] ?: 'Unknown File',
				$omnify_row['customer_email'] ?: 'Guest/Unknown',
				$omnify_row['status'],
				$omnify_row['ip_address'],
				$omnify_row['user_agent'],
				$omnify_row['downloaded_at']
			]);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_orders(): void {
		check_admin_referer('omnify_export_orders');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->send_csv_headers('omnify-orders');
		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['order_number', 'customer_email', 'status', 'currency', 'subtotal', 'tax', 'shipping_total', 'total', 'payment_method', 'coupon_code', 'discount_amount', 'created_at']);

		$omnify_offset = 0;
		do {
			$omnify_orders = $this->omnify_orders->all(['per_page' => 100, 'offset' => $omnify_offset]);
			foreach ($omnify_orders as $omnify_order) {
				fputcsv($omnify_output, [
					$omnify_order['order_number'] ?: '#' . $omnify_order['id'],
					$omnify_order['customer_email'],
					$omnify_order['status'],
					$omnify_order['currency'],
					$omnify_order['subtotal'],
					$omnify_order['tax'],
					$omnify_order['shipping_total'] ?? 0,
					$omnify_order['total'],
					$omnify_order['payment_method'],
					$omnify_order['coupon_code'],
					$omnify_order['discount_amount'] ?? 0,
					$omnify_order['created_at'],
				]);
			}
			$omnify_offset += count($omnify_orders);
		} while (100 === count($omnify_orders));

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_coupons(): void {
		check_admin_referer('omnify_export_coupons');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$this->send_csv_headers('omnify-coupons');
		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['code', 'is_active', 'discount_type', 'discount_value', 'min_order_amount', 'max_discount_amount', 'usage_limit', 'usage_limit_per_customer', 'free_shipping', 'first_order_only', 'included_product_ids', 'excluded_product_ids', 'included_categories', 'excluded_categories', 'expires_at']);

		$omnify_offset = 0;
		do {
			$omnify_coupons = $this->omnify_coupons->all(['per_page' => 100, 'offset' => $omnify_offset]);
			foreach ($omnify_coupons as $omnify_coupon) {
				fputcsv($omnify_output, [
					$omnify_coupon['code'],
					! empty($omnify_coupon['is_active']) ? 1 : 0,
					$omnify_coupon['discount_type'],
					$omnify_coupon['discount_value'],
					$omnify_coupon['min_order_amount'],
					$omnify_coupon['max_discount_amount'],
					$omnify_coupon['usage_limit'],
					$omnify_coupon['usage_limit_per_customer'],
					! empty($omnify_coupon['free_shipping']) ? 1 : 0,
					! empty($omnify_coupon['first_order_only']) ? 1 : 0,
					$omnify_coupon['included_product_ids'],
					$omnify_coupon['excluded_product_ids'],
					$omnify_coupon['included_categories'],
					$omnify_coupon['excluded_categories'],
					$omnify_coupon['expires_at'] ?? '',
				]);
			}
			$omnify_offset += count($omnify_coupons);
		} while (100 === count($omnify_coupons));

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_tax_rules(): void {
		check_admin_referer('omnify_export_tax_rules');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_settings = $this->omnify_settings->all();
		$this->send_csv_headers('omnify-tax-rules');
		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['enabled', 'label', 'country', 'state', 'rate', 'priority', 'reporting_code']);

		foreach ((array) ($omnify_settings['tax_rules'] ?? []) as $omnify_rule) {
			fputcsv($omnify_output, [
				! empty($omnify_rule['enabled']) ? 1 : 0,
				$omnify_rule['label'] ?? 'Tax',
				$omnify_rule['country'] ?? '*',
				$omnify_rule['state'] ?? '*',
				$omnify_rule['rate'] ?? 0,
				$omnify_rule['priority'] ?? 10,
				$omnify_rule['reporting_code'] ?? '',
			]);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_export_delivery_zones(): void {
		check_admin_referer('omnify_export_delivery_zones');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_settings = $this->omnify_settings->all();
		$this->send_csv_headers('omnify-delivery-zones');
		$omnify_output = fopen('php://output', 'w');
		fputcsv($omnify_output, ['enabled', 'name', 'countries', 'states', 'method_name', 'method_type', 'cost', 'free_min', 'class_costs', 'priority']);

		foreach ((array) ($omnify_settings['delivery_zones'] ?? []) as $omnify_zone) {
			$omnify_class_costs = [];
			foreach ((array) ($omnify_zone['class_costs'] ?? []) as $omnify_class_name => $omnify_class_cost) {
				$omnify_class_costs[] = sanitize_title((string) $omnify_class_name) . ':' . (float) $omnify_class_cost;
			}
			fputcsv($omnify_output, [
				! empty($omnify_zone['enabled']) ? 1 : 0,
				$omnify_zone['name'] ?? '',
				$omnify_zone['countries'] ?? '*',
				$omnify_zone['states'] ?? '*',
				$omnify_zone['method_name'] ?? '',
				$omnify_zone['method_type'] ?? 'flat_rate',
				$omnify_zone['cost'] ?? 0,
				$omnify_zone['free_min'] ?? 0,
				implode("\n", $omnify_class_costs),
				$omnify_zone['priority'] ?? 10,
			]);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_output);
		exit;
	}

	public function handle_import_csv(): void {
		check_admin_referer('omnify_import_csv', 'omnify_import_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_type = sanitize_key((string) wp_unslash($_POST['import_type'] ?? ''));
		$omnify_mode = sanitize_key((string) wp_unslash($_POST['import_mode'] ?? 'upsert'));
		$omnify_result = ['type' => $omnify_type, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

		if (empty($_FILES['import_file']) || ! is_array($_FILES['import_file'])) {
			$omnify_result['errors'][] = __('No CSV file uploaded.', 'omnifywp-ecommerce');
			$this->redirect_import_result($omnify_result);
		}

		$omnify_raw_import = (array) wp_unslash($_FILES['import_file']);
		$omnify_tmp_name = isset($omnify_raw_import['tmp_name']) ? (string) $omnify_raw_import['tmp_name'] : '';
		$omnify_file_name = isset($omnify_raw_import['name']) ? sanitize_file_name((string) $omnify_raw_import['name']) : '';

		if (empty($omnify_tmp_name) || ! is_uploaded_file($omnify_tmp_name)) {
			$omnify_result['errors'][] = __('No CSV file uploaded.', 'omnifywp-ecommerce');
			$this->redirect_import_result($omnify_result);
		}

		$omnify_file_path = $omnify_tmp_name;
		$omnify_file_ext = strtolower(pathinfo($omnify_file_name, PATHINFO_EXTENSION));
		if ($omnify_file_ext === 'xml') {
			$omnify_rows = $this->read_uploaded_xml($omnify_file_path, $omnify_result);
		} else {
			$omnify_rows = $this->read_uploaded_csv($omnify_file_path, $omnify_result);
		}
		if (empty($omnify_rows)) {
			$omnify_result['errors'][] = __('No importable rows found in file.', 'omnifywp-ecommerce');
			$this->redirect_import_result($omnify_result);
		}

		match ($omnify_type) {
			'products' => $this->import_products_csv($omnify_rows, $omnify_mode, $omnify_result),
			'customers' => $this->import_customers_csv($omnify_rows, $omnify_mode, $omnify_result),
			'orders' => $this->import_orders_csv($omnify_rows, $omnify_mode, $omnify_result),
			'coupons' => $this->import_coupons_csv($omnify_rows, $omnify_mode, $omnify_result),
			'tax_rules' => $this->import_tax_rules_csv($omnify_rows, $omnify_mode, $omnify_result),
			'delivery_zones' => $this->import_delivery_zones_csv($omnify_rows, $omnify_mode, $omnify_result),
			default => $omnify_result['errors'][] = __('Invalid import type selected.', 'omnifywp-ecommerce'),
		};

		$this->redirect_import_result($omnify_result);
	}

	private function send_csv_headers(string $omnify_prefix): void {
		header('Content-Type: text/csv; charset=utf-8');
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		header('Content-Disposition: attachment; filename=' . sanitize_file_name($omnify_prefix . '-' . date('Y-m-d') . '.csv'));
		header('Pragma: no-cache');
		header('Expires: 0');
	}

	private function read_uploaded_csv(string $omnify_path, array &$omnify_result): array {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$omnify_handle = fopen($omnify_path, 'r');
		if (! $omnify_handle) {
			$omnify_result['errors'][] = __('Could not read uploaded CSV file.', 'omnifywp-ecommerce');
			return [];
		}

		$omnify_headers = fgetcsv($omnify_handle);
		if (! is_array($omnify_headers)) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose($omnify_handle);
			return [];
		}

		$omnify_headers = array_map([$this, 'normalize_csv_header'], $omnify_headers);
		$omnify_rows = [];
		$omnify_line = 1;
		while (($omnify_data = fgetcsv($omnify_handle)) !== false) {
			++$omnify_line;
			if (! array_filter($omnify_data, static fn($omnify_value): bool => '' !== trim((string) $omnify_value))) {
				continue;
			}
			$omnify_row = [];
			foreach ($omnify_headers as $omnify_index => $omnify_header) {
				if ('' !== $omnify_header) {
					$omnify_row[$omnify_header] = isset($omnify_data[$omnify_index]) ? trim((string) $omnify_data[$omnify_index]) : '';
				}
			}
			$omnify_row['_line'] = $omnify_line;
			$omnify_rows[] = $omnify_row;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($omnify_handle);

		return $omnify_rows;
	}

	private function read_uploaded_xml(string $omnify_path, array &$omnify_result): array {
		if (!file_exists($omnify_path)) {
			$omnify_result['errors'][] = __('Could not read uploaded XML file.', 'omnifywp-ecommerce');
			return [];
		}
		libxml_use_internal_errors(true);
		$omnify_xml = simplexml_load_file($omnify_path);
		if (!$omnify_xml) {
			$omnify_result['errors'][] = __('Invalid XML file.', 'omnifywp-ecommerce');
			return [];
		}
		$omnify_rows = [];
		$omnify_line = 1;
		// Support <products><product>...</product></products> or root level items
		$omnify_items = $omnify_xml->product ?? $omnify_xml->item ?? $omnify_xml->row ?? $omnify_xml->children();
		if ($omnify_items && $omnify_items->count() === 0 && count($omnify_xml->children()) > 0) {
			$omnify_items = $omnify_xml->children();
		}
		foreach ($omnify_items as $omnify_item) {
			$omnify_row = $this->xml_to_row($omnify_item);
			$omnify_row['_line'] = $omnify_line++;
			$omnify_rows[] = $omnify_row;
		}
		return $omnify_rows;
	}

	/**
	 * Recursively convert SimpleXML element to flat array row.
	 * Handles nested <variations>, <files>, <categories> etc. for richer structure.
	 */
	private function xml_to_row(\SimpleXMLElement $omnify_item): array {
		$omnify_row = [];
		foreach ($omnify_item->children() as $omnify_k => $omnify_child) {
			$omnify_key = (string)$omnify_k;
			if ($omnify_child->count() > 0) {
				// Nested structure: serialize for import compatibility
				if (in_array($omnify_key, ['variations', 'files', 'categories', 'tags', 'upsells', 'cross_sells'])) {
					$omnify_serialized = [];
					foreach ($omnify_child->children() as $omnify_sub) {
						$omnify_sub_row = [];
						foreach ($omnify_sub->children() as $omnify_sk => $omnify_sv) {
							$omnify_sub_row[(string)$omnify_sk] = (string)$omnify_sv;
						}
						if ($omnify_sub_row) {
							$omnify_serialized[] = $omnify_sub_row;
						} elseif ((string)$omnify_sub) {
							$omnify_serialized[] = (string)$omnify_sub;
						}
					}
					// For CSV-like import, convert to JSON or comma for common cases
					if ($omnify_key === 'categories' || $omnify_key === 'tags') {
						$omnify_row[$omnify_key] = implode(',', array_map(function($omnify_s){ return is_array($omnify_s) ? ($omnify_s['name'] ?? '') : $omnify_s; }, $omnify_serialized));
					} else {
						$omnify_row[$omnify_key] = wp_json_encode($omnify_serialized);
					}
				} else {
					// Generic nested as JSON
					$omnify_row[$omnify_key] = wp_json_encode($this->xml_to_row($omnify_child)); // recursive but flatten children
				}
			} else {
				$omnify_row[$omnify_key] = (string)$omnify_child;
			}
		}
		// Also capture attributes if any
		foreach ($omnify_item->attributes() as $omnify_ak => $omnify_av) {
			$omnify_row['attr_' . (string)$omnify_ak] = (string)$omnify_av;
		}
		return $omnify_row;
	}

	private function normalize_csv_header(string $omnify_header): string {
		$omnify_header = strtolower(trim($omnify_header));
		$omnify_header = preg_replace('/[^a-z0-9]+/', '_', $omnify_header) ?: '';

		return trim($omnify_header, '_');
	}

	private function truthy_csv(mixed $omnify_value): bool {
		return in_array(strtolower(trim((string) $omnify_value)), ['1', 'yes', 'true', 'on', 'enabled', 'active'], true);
	}

	private function csv_number(mixed $omnify_value, float $omnify_default = 0.0): float {
		$omnify_value = trim((string) $omnify_value);
		if ('' === $omnify_value || ! is_numeric($omnify_value)) {
			return $omnify_default;
		}

		return (float) $omnify_value;
	}

	private function csv_date_or_null(mixed $omnify_value): ?string {
		$omnify_value = trim((string) $omnify_value);
		if ('' === $omnify_value) {
			return null;
		}

		$omnify_time = strtotime($omnify_value);
		return $omnify_time ? gmdate('Y-m-d H:i:s', $omnify_time) : null;
	}

	private function csv_required(array $omnify_row, array $omnify_fields, array &$omnify_result): bool {
		foreach ($omnify_fields as $omnify_field) {
			if ('' === trim((string) ($omnify_row[$omnify_field] ?? ''))) {
				$omnify_result['skipped']++;
				// translators: %1$d: placeholder value, %2$s: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %1$d skipped: missing required field "%2$s".', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0), $omnify_field);
				return false;
			}
		}

		return true;
	}

	private function parse_csv_id_list(mixed $omnify_value): array {
		$omnify_val = trim((string) $omnify_value);
		if ('' === $omnify_val) {
			return [];
		}
		$omnify_parts = array_filter(array_map('intval', array_map('trim', explode(',', $omnify_val))));
		return array_values(array_unique($omnify_parts));
	}

	private function parse_csv_attributes(mixed $omnify_value): array {
		$omnify_val = trim((string) $omnify_value);
		if ('' === $omnify_val) {
			return [];
		}
		// Support "Color:Red,Blue|Size:Small,Medium" or JSON
		if (str_starts_with($omnify_val, '{') || str_starts_with($omnify_val, '[')) {
			$omnify_decoded = json_decode($omnify_val, true);
			return is_array($omnify_decoded) ? map_deep($omnify_decoded, 'sanitize_text_field') : [];
		}
		$omnify_attrs = [];
		foreach (explode('|', $omnify_val) as $omnify_pair) {
			$omnify_parts = array_map('trim', explode(':', $omnify_pair, 2));
			if (count($omnify_parts) === 2) {
				$omnify_name = sanitize_text_field($omnify_parts[0]);
				$omnify_options = array_filter(array_map('sanitize_text_field', explode(',', $omnify_parts[1])));
				if ($omnify_name && $omnify_options) {
					$omnify_attrs[$omnify_name] = $omnify_options;
				}
			}
		}
		return $omnify_attrs;
	}

	private function parse_csv_variation_settings(array $omnify_row): array {
		$omnify_settings = [];
		$omnify_kind = sanitize_key((string) ($omnify_row['product_kind'] ?? $omnify_row['sales_type'] ?? ''));
		if ($omnify_kind) {
			$omnify_settings['product_kind'] = $omnify_kind === 'physical' ? 'physical' : 'digital';
		}
		if (! empty($omnify_row['variation_selector_style'])) {
			$omnify_settings['selector_style'] = sanitize_key($omnify_row['variation_selector_style']);
		}
		return $omnify_settings;
	}

	private function redirect_import_result(array $omnify_result): void {
		set_transient('omnify_import_result_' . get_current_user_id(), $omnify_result, MINUTE_IN_SECONDS * 10);
		wp_safe_redirect(admin_url('admin.php?page=omnify-export'));
		exit;
	}

	private function import_products_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		// First pass: main products (no parent_slug)
		$omnify_main_rows = [];
		$omnify_variation_rows = [];
		foreach ($omnify_rows as $omnify_row) {
			$omnify_parent = trim((string) ($omnify_row['parent_slug'] ?? $omnify_row['parent_id'] ?? ''));
			if ($omnify_parent || !empty($omnify_row['is_variation'])) {
				$omnify_variation_rows[] = $omnify_row;
			} else {
				$omnify_main_rows[] = $omnify_row;
			}
		}

		$omnify_created_products = []; // slug => id

		foreach ($omnify_main_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['name'], $omnify_result)) {
				continue;
			}

			$omnify_name = sanitize_text_field((string) $omnify_row['name']);
			$omnify_slug = sanitize_title((string) ($omnify_row['slug'] ?? $omnify_name));
			$omnify_status = in_array(sanitize_key((string) ($omnify_row['status'] ?? 'draft')), ['draft', 'published'], true) ? sanitize_key((string) ($omnify_row['status'] ?? 'draft')) : 'draft';
			$omnify_type = sanitize_key((string) ($omnify_row['type'] ?? ''));
			if ('' === $omnify_type) {
				$omnify_product_kind = sanitize_key((string) ($omnify_row['product_kind'] ?? 'digital'));
				$omnify_sales_type = sanitize_key((string) ($omnify_row['sales_type'] ?? $omnify_row['product_structure'] ?? 'simple'));
				$omnify_type = 'variable' === $omnify_sales_type ? 'variable' : ('physical' === $omnify_product_kind ? 'physical' : 'download');
			}
			$omnify_type = in_array($omnify_type, ['download', 'physical', 'variable', 'bundle'], true) ? $omnify_type : 'download';
			$omnify_data = [
				'name' => $omnify_name,
				'slug' => $omnify_slug,
				'status' => $omnify_status,
				'type' => $omnify_type,
				'variation_settings' => [
					'product_kind' => 'physical' === sanitize_key((string) ($omnify_row['product_kind'] ?? '')) || 'physical' === $omnify_type ? 'physical' : 'digital',
				],
				'price' => $this->csv_number($omnify_row['price'] ?? 0),
				'sale_price' => '' !== ($omnify_row['sale_price'] ?? '') ? $this->csv_number($omnify_row['sale_price']) : null,
				'currency' => strtoupper(substr(sanitize_text_field((string) ($omnify_row['currency'] ?? 'USD')), 0, 3)),
				'sku' => sanitize_text_field((string) ($omnify_row['sku'] ?? '')),
				'description' => wp_kses_post((string) ($omnify_row['description'] ?? '')),
				'categories' => array_filter(array_map('trim', explode(',', (string) ($omnify_row['categories'] ?? '')))),
				'tags' => array_filter(array_map('trim', explode(',', (string) ($omnify_row['tags'] ?? '')))),
				'stock_status' => sanitize_key((string) ($omnify_row['stock_status'] ?? 'instock')),
				'manage_stock' => $this->truthy_csv($omnify_row['manage_stock'] ?? 0),
				'stock_qty' => '' !== ($omnify_row['stock_qty'] ?? '') ? (int) $omnify_row['stock_qty'] : null,
				'allow_backorders' => $this->truthy_csv($omnify_row['allow_backorders'] ?? 0),
				'preorder_enabled' => $this->truthy_csv($omnify_row['preorder_enabled'] ?? 0),
				'preorder_release_date' => sanitize_text_field((string) ($omnify_row['preorder_release_date'] ?? '')),
				'preorder_limit' => '' !== ($omnify_row['preorder_limit'] ?? '') ? absint($omnify_row['preorder_limit']) : null,
				'preorder_message' => sanitize_text_field((string) ($omnify_row['preorder_message'] ?? '')),
				'shipping_class' => sanitize_title((string) ($omnify_row['shipping_class'] ?? '')),
				'weight' => '' !== ($omnify_row['weight'] ?? '') ? $this->csv_number($omnify_row['weight']) : null,
				'length' => '' !== ($omnify_row['length'] ?? '') ? $this->csv_number($omnify_row['length']) : null,
				'width' => '' !== ($omnify_row['width'] ?? '') ? $this->csv_number($omnify_row['width']) : null,
				'height' => '' !== ($omnify_row['height'] ?? '') ? $this->csv_number($omnify_row['height']) : null,
				'download_expiry_days' => absint($omnify_row['download_expiry_days'] ?? 0),
				'max_purchase_qty' => '' !== ($omnify_row['max_purchase_qty'] ?? '') ? absint($omnify_row['max_purchase_qty']) : null,
				'bundled_ids' => $this->parse_csv_id_list($omnify_row['bundled_ids'] ?? ''),
				'upsell_ids' => $this->parse_csv_id_list($omnify_row['upsell_ids'] ?? ''),
				'cross_sell_ids' => $this->parse_csv_id_list($omnify_row['cross_sell_ids'] ?? ''),
				'attributes' => $this->parse_csv_attributes($omnify_row['attributes'] ?? $omnify_row['attribute_options'] ?? ''),
				'variation_settings' => $this->parse_csv_variation_settings($omnify_row),
			];

			// Handle files metadata if present (e.g. file_name, file_version, attachment_id)
			$omnify_existing = $omnify_slug ? $this->omnify_products->find_by_slug($omnify_slug) : null;
			$omnify_product_id = 0;
			if ($omnify_existing && 'create' !== $omnify_mode) {
				$this->omnify_products->update((int) $omnify_existing['id'], $omnify_data);
				$omnify_product_id = (int) $omnify_existing['id'];
				$omnify_result['updated']++;
			} elseif (! $omnify_existing) {
				$omnify_product_id = $this->omnify_products->create($omnify_data);
				$omnify_result['created']++;
			} else {
				$omnify_result['skipped']++;
				continue;
			}
			$omnify_created_products[$omnify_slug] = $omnify_product_id;

			// Files support (metadata, no binary upload in CSV)
			if (!empty($omnify_row['file_name']) || !empty($omnify_row['files'])) {
				$omnify_files_str = (string) ($omnify_row['files'] ?? $omnify_row['file_name'] ?? '');
				foreach (explode('|', $omnify_files_str) as $omnify_fpart) {
					$omnify_parts = array_map('trim', explode(':', $omnify_fpart));
					$omnify_fname = sanitize_text_field($omnify_parts[0] ?? ($omnify_row['file_name'] ?? 'imported-file'));
					$omnify_fver = sanitize_text_field($omnify_parts[1] ?? ($omnify_row['file_version'] ?? '1.0'));
					$omnify_att_id = !empty($omnify_row['file_attachment_id']) ? absint($omnify_row['file_attachment_id']) : null;
					// Add file record (path will be empty; admin can update via UI)
					$this->omnify_product_files->create_from_upload($omnify_product_id, [
						'file' => '',
						'url' => '',
						'type' => 'application/octet-stream',
					], $omnify_fver);
				}
			}
		}

		// Second pass: variations (multi-row support)
		foreach ($omnify_variation_rows as $omnify_row) {
			$omnify_parent_slug = sanitize_title((string) ($omnify_row['parent_slug'] ?? $omnify_row['parent_id'] ?? ''));
			if (!$omnify_parent_slug || !isset($omnify_created_products[$omnify_parent_slug])) {
				// Try find existing parent
				$omnify_parent = $this->omnify_products->find_by_slug($omnify_parent_slug);
				if (!$omnify_parent) {
					$omnify_result['skipped']++;
					continue;
				}
				$omnify_parent_id = (int)$omnify_parent['id'];
			} else {
				$omnify_parent_id = $omnify_created_products[$omnify_parent_slug];
			}

			$omnify_var_data = [
				'sku' => sanitize_text_field((string) ($omnify_row['sku'] ?? '')),
				'price' => $this->csv_number($omnify_row['price'] ?? 0),
				'sale_price' => '' !== ($omnify_row['sale_price'] ?? '') ? $this->csv_number($omnify_row['sale_price']) : null,
				'attributes' => $this->parse_csv_attributes($omnify_row['attributes'] ?? $omnify_row['variation_attributes'] ?? ''),
				'description' => sanitize_textarea_field((string) ($omnify_row['description'] ?? '')),
				'manage_stock' => $this->truthy_csv($omnify_row['manage_stock'] ?? 0),
				'stock_qty' => '' !== ($omnify_row['stock_qty'] ?? '') ? (int) $omnify_row['stock_qty'] : null,
				'stock_status' => sanitize_key((string) ($omnify_row['stock_status'] ?? 'instock')),
			];

			// Use save_variations for the parent (append)
			$omnify_current_vars = $this->omnify_products->get_variations($omnify_parent_id); // assume method or fetch
			if (!is_array($omnify_current_vars)) $omnify_current_vars = [];
			$omnify_current_vars[] = $omnify_var_data;
			$this->omnify_products->save_variations($omnify_parent_id, $omnify_current_vars);
			$omnify_result['created']++; // count as variant created
		}
	}

	private function import_customers_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		foreach ($omnify_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['email'], $omnify_result)) {
				continue;
			}
			$omnify_email = sanitize_email((string) $omnify_row['email']);
			if (! is_email($omnify_email)) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: invalid email address.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}

			$omnify_data = [
				'email' => $omnify_email,
				'first_name' => sanitize_text_field((string) ($omnify_row['first_name'] ?? '')),
				'last_name' => sanitize_text_field((string) ($omnify_row['last_name'] ?? '')),
				'status' => sanitize_key((string) ($omnify_row['status'] ?? 'active')),
				'phone' => sanitize_text_field((string) ($omnify_row['phone'] ?? '')),
				'company' => sanitize_text_field((string) ($omnify_row['company'] ?? '')),
				'country' => strtoupper(substr(sanitize_text_field((string) ($omnify_row['country'] ?? '')), 0, 2)),
				'tax_exempt' => $this->truthy_csv($omnify_row['tax_exempt'] ?? 0),
			];

			$omnify_existing = $this->omnify_customers->find_by_email($omnify_email);
			if ($omnify_existing && 'create' !== $omnify_mode) {
				$this->omnify_customers->update((int) $omnify_existing['id'], $omnify_data);
				$omnify_result['updated']++;
			} elseif (! $omnify_existing) {
				$this->omnify_customers->create($omnify_data);
				$omnify_result['created']++;
			} else {
				$omnify_result['skipped']++;
			}
		}
	}

	private function import_orders_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_orders';

		foreach ($omnify_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['order_number', 'customer_email'], $omnify_result)) {
				continue;
			}
			$omnify_email = sanitize_email((string) $omnify_row['customer_email']);
			if (! is_email($omnify_email)) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: invalid customer email.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}
			$omnify_customer = $this->omnify_customers->find_by_email($omnify_email);
			$omnify_customer_id = $omnify_customer ? (int) $omnify_customer['id'] : $this->omnify_customers->create(['email' => $omnify_email, 'status' => 'active']);
			$omnify_order_number = sanitize_text_field((string) $omnify_row['order_number']);
			$omnify_existing_id = $this->omnify_orders->find_id_by_order_number($omnify_order_number);
			$omnify_status = sanitize_key((string) ($omnify_row['status'] ?? 'pending'));
			if (! in_array($omnify_status, ['pending', 'pending_payment', 'processing', 'completed', 'cancelled', 'refunded', 'failed', 'on_hold', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested', 'returned'], true)) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: invalid order status.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}
			$omnify_data = [
				'customer_id' => $omnify_customer_id,
				'status' => $omnify_status,
				'currency' => strtoupper(substr(sanitize_text_field((string) ($omnify_row['currency'] ?? 'USD')), 0, 3)),
				'subtotal' => $this->csv_number($omnify_row['subtotal'] ?? 0),
				'tax' => $this->csv_number($omnify_row['tax'] ?? 0),
				'shipping_total' => $this->csv_number($omnify_row['shipping_total'] ?? 0),
				'total' => $this->csv_number($omnify_row['total'] ?? 0),
				'payment_method' => sanitize_key((string) ($omnify_row['payment_method'] ?? 'manual')),
				'coupon_code' => sanitize_text_field((string) ($omnify_row['coupon_code'] ?? '')),
				'discount_amount' => $this->csv_number($omnify_row['discount_amount'] ?? 0),
				'order_number' => $omnify_order_number,
			];
			if (0.0 === $omnify_data['total']) {
				$omnify_data['total'] = $omnify_data['subtotal'] + $omnify_data['tax'] + $omnify_data['shipping_total'] - $omnify_data['discount_amount'];
			}

			if ($omnify_existing_id && 'create' !== $omnify_mode) {
				$this->omnify_orders->update($omnify_existing_id, $omnify_data);
				$omnify_result['updated']++;
			} elseif (! $omnify_existing_id) {
				$this->omnify_orders->create($omnify_data);
				$omnify_result['created']++;
			} else {
				$omnify_result['skipped']++;
			}
		}
	}

	private function import_coupons_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		foreach ($omnify_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['code', 'discount_type', 'discount_value'], $omnify_result)) {
				continue;
			}
			$omnify_type = sanitize_key((string) $omnify_row['discount_type']);
			if (! in_array($omnify_type, ['fixed', 'percent'], true)) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: discount_type must be fixed or percent.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}
			$omnify_data = [
				'code' => strtoupper(sanitize_text_field((string) $omnify_row['code'])),
				'is_active' => $this->truthy_csv($omnify_row['is_active'] ?? 1),
				'discount_type' => $omnify_type,
				'discount_value' => $this->csv_number($omnify_row['discount_value'] ?? 0),
				'min_order_amount' => $omnify_row['min_order_amount'] ?? '',
				'max_discount_amount' => $omnify_row['max_discount_amount'] ?? '',
				'usage_limit' => $omnify_row['usage_limit'] ?? '',
				'usage_limit_per_customer' => $omnify_row['usage_limit_per_customer'] ?? '',
				'free_shipping' => $this->truthy_csv($omnify_row['free_shipping'] ?? 0),
				'first_order_only' => $this->truthy_csv($omnify_row['first_order_only'] ?? 0),
				'included_product_ids' => $omnify_row['included_product_ids'] ?? '',
				'excluded_product_ids' => $omnify_row['excluded_product_ids'] ?? '',
				'included_categories' => $omnify_row['included_categories'] ?? '',
				'excluded_categories' => $omnify_row['excluded_categories'] ?? '',
				'expires_at' => $this->csv_date_or_null($omnify_row['expires_at'] ?? ''),
			];

			$omnify_existing = $this->omnify_coupons->find_by_code((string) $omnify_data['code']);
			if ($omnify_existing && 'create' !== $omnify_mode) {
				$this->omnify_coupons->update((int) $omnify_existing['id'], $omnify_data);
				$omnify_result['updated']++;
			} elseif (! $omnify_existing) {
				$this->omnify_coupons->create($omnify_data);
				$omnify_result['created']++;
			} else {
				$omnify_result['skipped']++;
			}
		}
	}

	private function import_tax_rules_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_rules = 'replace' === $omnify_mode ? [] : (array) ($omnify_settings['tax_rules'] ?? []);
		foreach ($omnify_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['label', 'country', 'state', 'rate'], $omnify_result)) {
				continue;
			}
			$omnify_rate = $this->csv_number($omnify_row['rate'] ?? 0);
			if ($omnify_rate < 0 || $omnify_rate > 100) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: tax rate must be between 0 and 100.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}
			$omnify_rules[] = [
				'enabled' => $this->truthy_csv($omnify_row['enabled'] ?? 1),
				'label' => sanitize_text_field((string) $omnify_row['label']),
				'country' => strtoupper(sanitize_text_field((string) $omnify_row['country'])),
				'state' => strtoupper(sanitize_text_field((string) $omnify_row['state'])),
				'rate' => $omnify_rate,
				'priority' => absint($omnify_row['priority'] ?? 10),
				'reporting_code' => sanitize_text_field((string) ($omnify_row['reporting_code'] ?? '')),
			];
			$omnify_result['created']++;
		}
		$omnify_settings['tax_rules'] = $omnify_rules;
		$this->omnify_settings->update($omnify_settings);
	}

	private function import_delivery_zones_csv(array $omnify_rows, string $omnify_mode, array &$omnify_result): void {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_zones = 'replace' === $omnify_mode ? [] : (array) ($omnify_settings['delivery_zones'] ?? []);
		foreach ($omnify_rows as $omnify_row) {
			if (! $this->csv_required($omnify_row, ['name', 'countries', 'states', 'method_name'], $omnify_result)) {
				continue;
			}
			$omnify_method_type = sanitize_key((string) ($omnify_row['method_type'] ?? 'flat_rate'));
			if (! in_array($omnify_method_type, ['flat_rate', 'free_shipping', 'local_pickup'], true)) {
				$omnify_result['skipped']++;
				// translators: %d: placeholder value.
				$omnify_result['errors'][] = sprintf(__('Line %d skipped: invalid delivery method type.', 'omnifywp-ecommerce'), (int) ($omnify_row['_line'] ?? 0));
				continue;
			}
			$omnify_zones[] = [
				'enabled' => $this->truthy_csv($omnify_row['enabled'] ?? 1),
				'name' => sanitize_text_field((string) $omnify_row['name']),
				'countries' => strtoupper(sanitize_text_field((string) $omnify_row['countries'])),
				'states' => strtoupper(sanitize_text_field((string) $omnify_row['states'])),
				'method_name' => sanitize_text_field((string) $omnify_row['method_name']),
				'method_type' => $omnify_method_type,
				'cost' => max(0.0, $this->csv_number($omnify_row['cost'] ?? 0)),
				'free_min' => max(0.0, $this->csv_number($omnify_row['free_min'] ?? 0)),
				'class_costs' => sanitize_textarea_field((string) ($omnify_row['class_costs'] ?? '')),
				'priority' => absint($omnify_row['priority'] ?? 10),
			];
			$omnify_result['created']++;
		}
		$omnify_settings['delivery_zones'] = $omnify_zones;
		$this->omnify_settings->update($omnify_settings);
	}

	public function handle_add_global_category(): void {
		check_admin_referer('omnify_add_global_category', 'omnify_add_global_cat_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_name   = isset($_POST['category_name']) ? sanitize_text_field(trim(wp_unslash($_POST['category_name']))) : '';
		$omnify_parent = isset($_POST['category_parent']) ? sanitize_key(trim(wp_unslash($_POST['category_parent']))) : '';

		if ($omnify_name !== '') {
			$omnify_global_cats = get_option('omnify_global_categories', []);
			if (! is_array($omnify_global_cats)) {
				$omnify_global_cats = [];
			}

			$omnify_exists = false;
			foreach ($omnify_global_cats as $omnify_cat) {
				if (strtolower($omnify_cat['name']) === strtolower($omnify_name)) {
					$omnify_exists = true;
					break;
				}
			}

			if (! $omnify_exists) {
				$omnify_global_cats[] = [
					'name'   => $omnify_name,
					'slug'   => sanitize_title($omnify_name),
					'parent' => $omnify_parent,
				];
				update_option('omnify_global_categories', $omnify_global_cats);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-categories&message=global_cat_added'));
		exit;
	}

	public function handle_delete_global_category(): void {
		$omnify_index = isset($_GET['index']) ? (int) wp_unslash($_GET['index']): -1;
		check_admin_referer('omnify_delete_global_category_' . $omnify_index);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_global_cats = get_option('omnify_global_categories', []);
		if (is_array($omnify_global_cats) && isset($omnify_global_cats[$omnify_index])) {
			$omnify_deleted_slug = $omnify_global_cats[$omnify_index]['slug'] ?? '';
			unset($omnify_global_cats[$omnify_index]);

			// Clean up child categories' parents
			if ($omnify_deleted_slug !== '') {
				foreach ($omnify_global_cats as &$omnify_cat) {
					if (isset($omnify_cat['parent']) && $omnify_cat['parent'] === $omnify_deleted_slug) {
						$omnify_cat['parent'] = '';
					}
				}
				unset($omnify_cat);
			}

			update_option('omnify_global_categories', array_values($omnify_global_cats));
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-categories&message=global_cat_deleted'));
		exit;
	}

	public function handle_add_global_tag(): void {
		check_admin_referer('omnify_add_global_tag', 'omnify_add_global_tag_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_name = isset($_POST['tag_name']) ? sanitize_text_field(trim(wp_unslash($_POST['tag_name']))) : '';
		if ($omnify_name !== '') {
			$omnify_global_tags = get_option('omnify_global_tags', []);
			if (! is_array($omnify_global_tags)) {
				$omnify_global_tags = [];
			}

			$omnify_exists = false;
			foreach ($omnify_global_tags as $omnify_tag) {
				if (strtolower($omnify_tag['name']) === strtolower($omnify_name)) {
					$omnify_exists = true;
					break;
				}
			}

			if (! $omnify_exists) {
				$omnify_global_tags[] = [
					'name' => $omnify_name,
					'slug' => sanitize_title($omnify_name),
				];
				update_option('omnify_global_tags', $omnify_global_tags);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-tags&message=global_tag_added'));
		exit;
	}

	public function handle_delete_global_tag(): void {
		$omnify_index = isset($_GET['index']) ? (int) wp_unslash($_GET['index']): -1;
		check_admin_referer('omnify_delete_global_tag_' . $omnify_index);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_global_tags = get_option('omnify_global_tags', []);
		if (is_array($omnify_global_tags) && isset($omnify_global_tags[$omnify_index])) {
			unset($omnify_global_tags[$omnify_index]);
			update_option('omnify_global_tags', array_values($omnify_global_tags));
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-tags&message=global_tag_deleted'));
		exit;
	}

	public function handle_add_global_brand(): void {
		check_admin_referer('omnify_add_global_brand', 'omnify_add_global_brand_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_name = isset($_POST['brand_name']) ? sanitize_text_field(trim(wp_unslash($_POST['brand_name']))) : '';
		if ($omnify_name !== '') {
			$omnify_global_brands = get_option('omnify_global_brands', []);
			if (! is_array($omnify_global_brands)) {
				$omnify_global_brands = [];
			}

			$omnify_exists = false;
			foreach ($omnify_global_brands as $omnify_brand) {
				if (strtolower($omnify_brand['name']) === strtolower($omnify_name)) {
					$omnify_exists = true;
					break;
				}
			}

			if (! $omnify_exists) {
				$omnify_global_brands[] = [
					'name' => $omnify_name,
					'slug' => sanitize_title($omnify_name),
				];
				update_option('omnify_global_brands', $omnify_global_brands);
			}
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-brands&message=global_brand_added'));
		exit;
	}

	public function handle_delete_global_brand(): void {
		$omnify_index = isset($_GET['index']) ? (int) wp_unslash($_GET['index']): -1;
		check_admin_referer('omnify_delete_global_brand_' . $omnify_index);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_global_brands = get_option('omnify_global_brands', []);
		if (is_array($omnify_global_brands) && isset($omnify_global_brands[$omnify_index])) {
			unset($omnify_global_brands[$omnify_index]);
			update_option('omnify_global_brands', array_values($omnify_global_brands));
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-brands&message=global_brand_deleted'));
		exit;
	}

	public function handle_add_global_attribute(): void {
		check_admin_referer('omnify_add_global_attribute', 'omnify_add_global_attr_nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_name = isset($_POST['attribute_name']) ? sanitize_text_field(trim(wp_unslash($_POST['attribute_name']))) : '';
		$omnify_attribute_index = isset($_POST['attribute_index']) ? (int) wp_unslash($_POST['attribute_index']): -1;
		$omnify_type = sanitize_key(wp_unslash($_POST['attribute_type'] ?? 'button'));
		if (! in_array($omnify_type, ['button', 'dropdown', 'color', 'image'], true)) {
			$omnify_type = 'button';
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_raw_options = sanitize_text_field(wp_unslash($_POST['attribute_options'] ?? ''));
		$omnify_options = array_filter(array_map('trim', explode(',', $omnify_raw_options)));
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$omnify_raw_meta = isset($_POST['attribute_option_meta']) && is_array($_POST['attribute_option_meta']) ? $_POST['attribute_option_meta'] : [];
		$omnify_option_meta = [];
		foreach ($omnify_options as $omnify_option_label) {
			$omnify_option_raw_meta = is_array($omnify_raw_meta[$omnify_option_label] ?? null) ? $omnify_raw_meta[$omnify_option_label] : [];
			$omnify_option_meta[$omnify_option_label] = [
				'color'     => sanitize_hex_color($omnify_option_raw_meta['color'] ?? '') ?: '',
				'image_id'  => absint($omnify_option_raw_meta['image_id'] ?? 0),
				'image_url' => esc_url_raw($omnify_option_raw_meta['image_url'] ?? ''),
			];
		}

		if ($omnify_name !== '' && ! empty($omnify_options)) {
			$omnify_global_attrs = get_option('omnify_global_attributes', []);
			if (! is_array($omnify_global_attrs)) {
				$omnify_global_attrs = [];
			}

			$omnify_is_editing = $omnify_attribute_index >= 0 && isset($omnify_global_attrs[$omnify_attribute_index]);
			$omnify_exists = false;
			$omnify_exists_index = -1;
			foreach ($omnify_global_attrs as $omnify_idx => $omnify_attr) {
				if ($omnify_is_editing && $omnify_idx === $omnify_attribute_index) {
					continue;
				}
				if (strtolower($omnify_attr['name']) === strtolower($omnify_name)) {
					$omnify_exists = true;
					$omnify_exists_index = $omnify_idx;
					break;
				}
			}

			$omnify_attribute_payload = [
				'name'        => $omnify_name,
				'type'        => $omnify_type,
				'options'     => array_values($omnify_options),
				'option_meta' => $omnify_option_meta,
			];

			if ($omnify_is_editing) {
				$omnify_global_attrs[$omnify_attribute_index] = $omnify_attribute_payload;
			} elseif (! $omnify_exists) {
				$omnify_global_attrs[] = [
					'name'        => $omnify_name,
					'type'        => $omnify_type,
					'options'     => array_values($omnify_options),
					'option_meta' => $omnify_option_meta,
				];
			} else {
				$omnify_existing_options = is_array($omnify_global_attrs[$omnify_exists_index]['options'] ?? null) ? $omnify_global_attrs[$omnify_exists_index]['options'] : [];
				$omnify_existing_meta = is_array($omnify_global_attrs[$omnify_exists_index]['option_meta'] ?? null) ? $omnify_global_attrs[$omnify_exists_index]['option_meta'] : [];
				$omnify_global_attrs[$omnify_exists_index]['type'] = $omnify_type;
				$omnify_global_attrs[$omnify_exists_index]['options'] = array_values(array_unique(array_merge(
					$omnify_existing_options,
					$omnify_options
				)));
				$omnify_global_attrs[$omnify_exists_index]['option_meta'] = array_replace($omnify_existing_meta, $omnify_option_meta);
			}

			update_option('omnify_global_attributes', $omnify_global_attrs);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-attributes&message=global_attr_added'));
		exit;
	}

	public function handle_delete_global_attribute(): void {
		$omnify_index = isset($_GET['index']) ? (int) wp_unslash($_GET['index']): -1;
		check_admin_referer('omnify_delete_global_attribute_' . $omnify_index);

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		}

		$omnify_global_attrs = get_option('omnify_global_attributes', []);
		if (is_array($omnify_global_attrs) && isset($omnify_global_attrs[$omnify_index])) {
			unset($omnify_global_attrs[$omnify_index]);
			update_option('omnify_global_attributes', array_values($omnify_global_attrs));
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-attributes&message=global_attr_deleted'));
		exit;
	}

	public function handle_approve_review(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_approve_review_' . $omnify_id);
		if (! current_user_can('manage_options')) wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		
		$omnify_review = $this->omnify_reviews_repo->find($omnify_id);
		$this->omnify_reviews_repo->update_status($omnify_id, 'approved');

		if ($omnify_review) {
			$omnify_product = $this->omnify_products->find((int) $omnify_review['product_id']);
			$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';
			$omnify_reviewer = $omnify_review['reviewer_name'] ?: ($omnify_review['reviewer_email'] ?? 'Unknown');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'approve_review',
				'review',
				(string) $omnify_id,
				sprintf(
					// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value, %4$d: placeholder value.
					__('Approved review #%1$d by %2$s on product "%3$s" (Rating: %4$d/5).', 'omnifywp-ecommerce'),
					$omnify_id,
					$omnify_reviewer,
					$omnify_product_name ?: '#' . $omnify_review['product_id'],
					$omnify_review['rating']
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-reviews&message=review_approved'));
		exit;
	}

	public function handle_reject_review(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_reject_review_' . $omnify_id);
		if (! current_user_can('manage_options')) wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		
		$omnify_review = $this->omnify_reviews_repo->find($omnify_id);
		$this->omnify_reviews_repo->update_status($omnify_id, 'rejected');

		if ($omnify_review) {
			$omnify_product = $this->omnify_products->find((int) $omnify_review['product_id']);
			$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';
			$omnify_reviewer = $omnify_review['reviewer_name'] ?: ($omnify_review['reviewer_email'] ?? 'Unknown');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'reject_review',
				'review',
				(string) $omnify_id,
				sprintf(
					// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value, %4$d: placeholder value.
					__('Rejected review #%1$d by %2$s on product "%3$s" (Rating: %4$d/5).', 'omnifywp-ecommerce'),
					$omnify_id,
					$omnify_reviewer,
					$omnify_product_name ?: '#' . $omnify_review['product_id'],
					$omnify_review['rating']
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-reviews&message=review_rejected'));
		exit;
	}

	public function handle_delete_review(): void {
		$omnify_id = absint(wp_unslash($_GET['id'] ?? 0));
		check_admin_referer('omnify_delete_review_' . $omnify_id);
		if (! current_user_can('manage_options')) wp_die(esc_html__('Unauthorized access.', 'omnifywp-ecommerce'));
		
		$omnify_review = $this->omnify_reviews_repo->find($omnify_id);
		$this->omnify_reviews_repo->delete($omnify_id);

		if ($omnify_review) {
			$omnify_product = $this->omnify_products->find((int) $omnify_review['product_id']);
			$omnify_product_name = $omnify_product ? ($omnify_product['name'] ?? '') : '';
			$omnify_reviewer = $omnify_review['reviewer_name'] ?: ($omnify_review['reviewer_email'] ?? 'Unknown');

			$omnify_user_id = get_current_user_id();
			$this->omnify_activities->log(
				$omnify_user_id,
				'delete_review',
				'review',
				(string) $omnify_id,
				sprintf(
					// translators: %1$d: placeholder value, %2$s: placeholder value, %3$s: placeholder value, %4$d: placeholder value.
					__('Deleted review #%1$d by %2$s on product "%3$s" (Rating: %4$d/5).', 'omnifywp-ecommerce'),
					$omnify_id,
					$omnify_reviewer,
					$omnify_product_name ?: '#' . $omnify_review['product_id'],
					$omnify_review['rating']
				)
			);
		}

		wp_safe_redirect(admin_url('admin.php?page=omnify-reviews&message=review_deleted'));
		exit;
	}

	public function handle_submit_review(): void {
		$omnify_product_id = absint(wp_unslash($_POST['product_id'] ?? 0));
		check_admin_referer('omnify_submit_review_' . $omnify_product_id, 'omnify_review_nonce');

		if (! $omnify_product_id) {
			wp_die(esc_html__('Invalid product.', 'omnifywp-ecommerce'));
		}

		$omnify_rating = min(5, max(1, absint(wp_unslash($_POST['rating'] ?? 0))));
		if ($omnify_rating < 1) {
			wp_safe_redirect(add_query_arg('review', 'no_rating', wp_get_referer() ?: home_url()));
			exit;
		}

		$omnify_current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$omnify_current_email = $omnify_current_user ? sanitize_email((string) $omnify_current_user->user_email) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_posted_email = sanitize_email(wp_unslash($_POST['reviewer_email'] ?? ''));

		// Verify purchase permission
		$omnify_cust = $this->omnify_customers->find_by_email($omnify_posted_email);
		if (! $omnify_cust) {
			wp_die(esc_html__('Only customers who have purchased this product can leave a review.', 'omnifywp-ecommerce'));
		}
		$omnify_has_access = $this->omnify_customer_access->has_access((int) $omnify_cust['id'], $omnify_product_id);
		$omnify_has_purchased = $this->omnify_orders->has_customer_purchased_product((int) $omnify_cust['id'], $omnify_product_id);
		if (! $omnify_has_access && ! $omnify_has_purchased) {
			wp_die(esc_html__('Only customers who have purchased this product can leave a review.', 'omnifywp-ecommerce'));
		}
		$omnify_review_id = absint(wp_unslash($_POST['review_id'] ?? 0));
		$omnify_data = [
			'product_id'     => $omnify_product_id,
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'customer_name'  => sanitize_text_field(wp_unslash($_POST['reviewer_name'] ?? '')),
			'customer_email' => $omnify_posted_email,
			'rating'         => $omnify_rating,
			'review_title'   => '',
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'review_content' => sanitize_textarea_field(wp_unslash($_POST['content'] ?? '')),
			'status'         => 'pending',
			'media'          => $this->handle_review_media_uploads(),
		];

		if ($omnify_review_id && $omnify_current_email) {
			$omnify_existing = $this->omnify_reviews_repo->find($omnify_review_id);
			if (! $omnify_existing || (int) $omnify_existing['product_id'] !== $omnify_product_id || sanitize_email((string) $omnify_existing['reviewer_email']) !== $omnify_current_email) {
				wp_die(esc_html__('You can only edit your own review.', 'omnifywp-ecommerce'));
			}

			$omnify_data['customer_email'] = $omnify_current_email;
			$this->omnify_reviews_repo->update($omnify_review_id, $omnify_data);
			wp_safe_redirect(add_query_arg('review', 'updated', wp_get_referer() ?: home_url()));
			exit;
		}

		$this->omnify_reviews_repo->create($omnify_data);

		wp_safe_redirect(add_query_arg('review', 'submitted', wp_get_referer() ?: home_url()));
		exit;
	}

	private function handle_review_media_uploads(): array {
		$omnify_media = [];
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Images
		if (! empty($_FILES['review_images']) && is_array($_FILES['review_images'])) {
			$omnify_raw_files = (array) wp_unslash($_FILES['review_images']);
			if (! empty($omnify_raw_files['name']) && is_array($omnify_raw_files['name'])) {
				$omnify_count = min(3, count($omnify_raw_files['name']));
				$omnify_allowed_image_mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
				for ($omnify_i = 0; $omnify_i < $omnify_count; $omnify_i++) {
					$omnify_err = isset($omnify_raw_files['error'][$omnify_i]) ? (int) $omnify_raw_files['error'][$omnify_i] : UPLOAD_ERR_NO_FILE;
					$omnify_tmp = isset($omnify_raw_files['tmp_name'][$omnify_i]) ? (string) $omnify_raw_files['tmp_name'][$omnify_i] : '';
					if (UPLOAD_ERR_OK === $omnify_err && is_uploaded_file($omnify_tmp)) {
						$omnify_name = sanitize_file_name((string) ($omnify_raw_files['name'][$omnify_i] ?? ''));
						$omnify_type = sanitize_mime_type((string) ($omnify_raw_files['type'][$omnify_i] ?? ''));
						$omnify_file_check = wp_check_filetype_and_ext($omnify_tmp, $omnify_name, $omnify_allowed_image_mimes);
						if (empty($omnify_file_check['ext'])) {
							continue;
						}
						$omnify_file = [
							'name'     => $omnify_name,
							'type'     => $omnify_file_check['type'] ?: $omnify_type,
							'tmp_name' => $omnify_tmp,
							'error'    => $omnify_err,
							'size'     => isset($omnify_raw_files['size'][$omnify_i]) ? (int) $omnify_raw_files['size'][$omnify_i] : 0,
						];
						$omnify_uploaded = wp_handle_upload($omnify_file, ['test_form' => false, 'mimes' => $omnify_allowed_image_mimes]);
						if (! isset($omnify_uploaded['error']) && ! empty($omnify_uploaded['url'])) {
							$omnify_media['images'][] = esc_url_raw($omnify_uploaded['url']);
						}
					}
				}
			}
		}

		// Video
		if (! empty($_FILES['review_video']) && is_array($_FILES['review_video'])) {
			$omnify_raw_video = (array) wp_unslash($_FILES['review_video']);
			$omnify_v_err = isset($omnify_raw_video['error']) ? (int) $omnify_raw_video['error'] : UPLOAD_ERR_NO_FILE;
			$omnify_v_tmp = isset($omnify_raw_video['tmp_name']) ? (string) $omnify_raw_video['tmp_name'] : '';
			if (UPLOAD_ERR_OK === $omnify_v_err && is_uploaded_file($omnify_v_tmp)) {
				$omnify_v_name = sanitize_file_name((string) ($omnify_raw_video['name'] ?? ''));
				$omnify_v_type = sanitize_mime_type((string) ($omnify_raw_video['type'] ?? ''));
				$omnify_allowed_video_mimes = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'];
				$omnify_v_check = wp_check_filetype_and_ext($omnify_v_tmp, $omnify_v_name, $omnify_allowed_video_mimes);
				if (! empty($omnify_v_check['ext'])) {
					$omnify_v_file = [
						'name'     => $omnify_v_name,
						'type'     => $omnify_v_check['type'] ?: $omnify_v_type,
						'tmp_name' => $omnify_v_tmp,
						'error'    => $omnify_v_err,
						'size'     => isset($omnify_raw_video['size']) ? (int) $omnify_raw_video['size'] : 0,
					];
					$omnify_uploaded = wp_handle_upload($omnify_v_file, ['test_form' => false, 'mimes' => $omnify_allowed_video_mimes]);
					if (! isset($omnify_uploaded['error']) && ! empty($omnify_uploaded['url'])) {
						$omnify_media['video'] = esc_url_raw($omnify_uploaded['url']);
					}
				}
			}
		}

		return $omnify_media;
	}

	/**
	 * Sanitize tax rules array from POST for safe storage.
	 */
	private function sanitize_tax_rules_array( array $omnify_rules ): array {
		$omnify_clean = [];
		foreach ( $omnify_rules as $omnify_rule ) {
			if ( ! is_array( $omnify_rule ) ) {
				continue;
			}
			$omnify_clean[] = [
				'enabled'         => ! empty( $omnify_rule['enabled'] ),
				'label'           => sanitize_text_field( (string) ( $omnify_rule['label'] ?? '' ) ),
				'country'         => strtoupper( substr( sanitize_text_field( (string) ( $omnify_rule['country'] ?? '' ) ), 0, 2 ) ),
				'state'           => sanitize_text_field( (string) ( $omnify_rule['state'] ?? '' ) ),
				'rate'            => max( 0.0, (float) ( $omnify_rule['rate'] ?? 0 ) ),
				'priority'        => max( 0, absint( $omnify_rule['priority'] ?? 0 ) ),
				'reporting_code'  => sanitize_text_field( (string) ( $omnify_rule['reporting_code'] ?? '' ) ),
			];
		}
		return $omnify_clean;
	}

	/**
	 * Sanitize delivery zones array from POST for safe storage.
	 */
	private function sanitize_delivery_zones_array( array $omnify_zones ): array {
		$omnify_clean = [];
		foreach ( $omnify_zones as $omnify_zone ) {
			if ( ! is_array( $omnify_zone ) ) {
				continue;
			}
			$omnify_class_costs = [];
			if ( isset( $omnify_zone['class_costs'] ) && is_array( $omnify_zone['class_costs'] ) ) {
				foreach ( $omnify_zone['class_costs'] as $omnify_k => $omnify_v ) {
					$omnify_class_costs[ sanitize_key( (string) $omnify_k ) ] = max( 0.0, (float) $omnify_v );
				}
			}
			$omnify_clean[] = [
				'enabled'     => ! empty( $omnify_zone['enabled'] ),
				'name'        => sanitize_text_field( (string) ( $omnify_zone['name'] ?? '' ) ),
				'countries'   => array_map( 'strtoupper', array_map( 'sanitize_text_field', (array) ( $omnify_zone['countries'] ?? [] ) ) ),
				'states'      => array_map( 'sanitize_text_field', (array) ( $omnify_zone['states'] ?? [] ) ),
				'method_name' => sanitize_text_field( (string) ( $omnify_zone['method_name'] ?? '' ) ),
				'method_type' => in_array( $omnify_zone['method_type'] ?? '', [ 'flat_rate', 'free_shipping', 'local_pickup' ], true )
					? $omnify_zone['method_type'] : 'flat_rate',
				'cost'        => max( 0.0, (float) ( $omnify_zone['cost'] ?? 0 ) ),
				'free_min'    => max( 0.0, (float) ( $omnify_zone['free_min'] ?? 0 ) ),
				'class_costs' => $omnify_class_costs,
				'priority'    => max( 0, absint( $omnify_zone['priority'] ?? 0 ) ),
			];
		}
		return $omnify_clean;
	}

	public function render_setup_wizard(): void {
		$omnify_settings = $this->omnify_settings->all();
		include OMNIFY_PATH . 'templates/admin/setup-wizard.php';
	}

	public function render_tools(): void {
		include OMNIFY_PATH . 'templates/admin/tools.php';
	}

	public function admin_notices(): void {
		if (get_option('omnify_setup_completed')) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$omnify_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		if ('omnify-setup' === $omnify_page) {
			return;
		}

		$omnify_screen = get_current_screen();
		if ($omnify_screen && str_contains($omnify_screen->id, 'omnify-setup')) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php 
				printf(
					wp_kses(
						/* translators: %s: setup wizard URL */
						__('Thank you for installing OmnifyWP eCommerce! Please <a href="%s">run the Setup Wizard</a> to configure your store settings.', 'omnifywp-ecommerce'),
						['a' => ['href' => []]]
					),
					esc_url(admin_url('admin.php?page=omnify-setup'))
				); 
				?>
			</p>
		</div>
		<?php
	}

	public function add_omnify_post_states(array $omnify_post_states, \WP_Post $omnify_post): array {
		if ('page' !== $omnify_post->post_type) {
			return $omnify_post_states;
		}

		$omnify_settings = $this->omnify_settings->all();

		$omnify_page_mappings = [
			'page_storefront'      => __('Omnify Storefront', 'omnifywp-ecommerce'),
			'page_cart'            => __('Omnify Cart', 'omnifywp-ecommerce'),
			'page_checkout'        => __('Omnify Checkout', 'omnifywp-ecommerce'),
			'page_customer_portal' => __('Omnify Customer Portal', 'omnifywp-ecommerce'),
			'page_download_page'   => __('Omnify Secure Download', 'omnifywp-ecommerce'),
		];

		foreach ($omnify_page_mappings as $omnify_key => $omnify_label) {
			$omnify_mapped_id = absint($omnify_settings[$omnify_key] ?? 0);
			if ($omnify_mapped_id && $omnify_post->ID === $omnify_mapped_id) {
				$omnify_post_states[] = $omnify_label;
				break;
			}
		}

		return $omnify_post_states;
	}

	public function ajax_save_wizard(): void {
		check_ajax_referer('omnify_admin_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Permission denied.', 'omnifywp-ecommerce')]);
		}

		$omnify_store_name = sanitize_text_field(wp_unslash($_POST['store_name'] ?? ''));
		$omnify_store_email = sanitize_email(wp_unslash($_POST['store_email'] ?? ''));
		$omnify_currency = strtoupper(substr(sanitize_text_field(wp_unslash($_POST['currency'] ?? 'USD')), 0, 3));
		$omnify_country = strtoupper(substr(sanitize_text_field(wp_unslash($_POST['country'] ?? 'US')), 0, 2));

		$omnify_current_settings = $this->omnify_settings->all();
		$omnify_current_settings['store_name'] = $omnify_store_name;
		$omnify_current_settings['store_email'] = $omnify_store_email;
		$omnify_current_settings['default_currency'] = $omnify_currency;
		$omnify_current_settings['default_country'] = $omnify_country;

		// Gateways
		$omnify_current_settings['enable_cod'] = !empty($_POST['enable_cod']) ? 'yes' : 'no';
		$omnify_current_settings['enable_bacs'] = !empty($_POST['enable_bacs']) ? 'yes' : 'no';
		$omnify_current_settings['enable_stripe'] = !empty($_POST['enable_stripe']) ? 'yes' : 'no';
		$omnify_current_settings['enable_paypal'] = !empty($_POST['enable_paypal']) ? 'yes' : 'no';

		$omnify_success = $this->omnify_settings->update($omnify_current_settings);

		if ($omnify_success) {
			update_option('omnify_setup_completed', 1);
			wp_send_json_success(['message' => __('Setup settings saved successfully!', 'omnifywp-ecommerce')]);
		} else {
			wp_send_json_error(['message' => __('Failed to save settings. Please try again.', 'omnifywp-ecommerce')]);
		}
	}

	public function ajax_generate_demo_data(): void {
		check_ajax_referer('omnify_admin_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Permission denied.', 'omnifywp-ecommerce')]);
		}

		try {
			$omnify_stats = $this->omnify_demo_data->generate_demo_data();
			wp_send_json_success([
				'message' => __('Demo data generated successfully!', 'omnifywp-ecommerce'),
				'stats'   => $omnify_stats
			]);
		} catch (\Exception $e) {
			wp_send_json_error(['message' => $e->getMessage()]);
		}
	}

	public function ajax_remove_demo_data(): void {
		check_ajax_referer('omnify_admin_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Permission denied.', 'omnifywp-ecommerce')]);
		}

		$omnify_type = sanitize_key(wp_unslash($_POST['type'] ?? 'all'));

		try {
			$this->omnify_demo_data->remove_data($omnify_type);
			wp_send_json_success(['message' => __('Data removed successfully!', 'omnifywp-ecommerce')]);
		} catch (\Exception $e) {
			wp_send_json_error(['message' => $e->getMessage()]);
		}
	}

	/**
	 * Parse comma-separated CSV string into trimmed non-empty array of values.
	 *
	 * @param mixed $omnify_value
	 * @return array<int, string>
	 */
	public function coupon_csv_values(mixed $omnify_value): array {
		return omnify_coupon_csv_values($omnify_value);
	}

	/**
	 * Render HTML trend badge for analytics and KPIs.
	 *
	 * @param float|int|string $omnify_trend
	 * @return string
	 */
	public function render_trend(float|int|string $omnify_trend): string {
		return omnify_render_trend($omnify_trend);
	}
}

