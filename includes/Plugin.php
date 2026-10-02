<?php
/**
 * Plugin lifecycle and runtime.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce;

use Omnify\eCommerce\Admin\Omnify_Admin_Page;
use Omnify\eCommerce\Api\Omnify_Rest_Server;
use Omnify\eCommerce\Database\Omnify_Migrations;
use Omnify\eCommerce\Downloads\Omnify_Download_Controller;
use Omnify\eCommerce\Storefront\Omnify_Storefront_Controller;
use Omnify\eCommerce\Support\Omnify_Abandoned_Cart_Service;
use Omnify\eCommerce\Support\Omnify_Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Plugin {
	private static ?self $omnify_instance = null;

	private Omnify_Container $omnify_container;

	private bool $omnify_booted = false;

	private function __construct() {
		$this->omnify_container = new Omnify_Container();
	}

	public static function instance(): self {
		if (null === self::$omnify_instance) {
			self::$omnify_instance = new self();
		}

		return self::$omnify_instance;
	}

	public function container(): Omnify_Container {
		return $this->omnify_container;
	}

	public static function activate(): void {
		$omnify_container = new Omnify_Container();
		$omnify_container->get(Omnify_Migrations::class)->migrate();
		$omnify_container->get(Omnify_Abandoned_Cart_Service::class)->schedule();

		// Generate required pages automatically on activation
		$omnify_settings_repo = $omnify_container->get(\Omnify\eCommerce\Settings\Omnify_Settings_Repository::class);
		$omnify_existing_settings = $omnify_settings_repo->all();

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
					$omnify_page_settings['page_' . $omnify_key] = (int) $omnify_post_id;
				}
			} else {
				$omnify_page_settings['page_' . $omnify_key] = (int) $omnify_existing[0]->ID;
			}
		}

		if (! empty($omnify_page_settings)) {
			$omnify_settings_repo->update(array_merge($omnify_existing_settings, $omnify_page_settings));
		}

		flush_rewrite_rules();
		update_option('omnify_version', OMNIFY_VERSION);
		set_transient('omnify_setup_redirect', true, 60);
	}

	public static function deactivate(): void {
		(new Omnify_Container())->get(Omnify_Abandoned_Cart_Service::class)->unschedule();
		flush_rewrite_rules();
		do_action('omnify_deactivated');
	}

	public function boot(): void {
		if ($this->omnify_booted) {
			return;
		}

		do_action('omnify_before_boot', $this, $this->omnify_container);



		$omnify_migrations = $this->omnify_container->get(Omnify_Migrations::class);
		if (get_option('omnify_schema_version') !== $omnify_migrations->version()) {
			$omnify_migrations->migrate();
		}

		add_action('admin_menu', [$this->omnify_container->get(Omnify_Admin_Page::class), 'register_menu']);
		add_action('admin_init', [$this->omnify_container->get(Omnify_Admin_Page::class), 'register_settings']);
		add_action('admin_enqueue_scripts', [$this->omnify_container->get(Omnify_Admin_Page::class), 'enqueue_assets']);
		add_action('rest_api_init', [$this->omnify_container->get(Omnify_Rest_Server::class), 'register_routes']);

		// API Key Authentication
		add_filter('determine_current_user', [\Omnify\eCommerce\Api\Omnify_Auth::class, 'determine_current_user'], 20);
		add_filter('omnify_rest_can_manage', [\Omnify\eCommerce\Api\Omnify_Auth::class, 'rest_can_manage'], 10, 3);
		add_filter('omnify_rest_is_logged_in', [\Omnify\eCommerce\Api\Omnify_Auth::class, 'rest_is_logged_in'], 10, 3);
		add_action('init', [$this->omnify_container->get(Omnify_Download_Controller::class), 'register']);
		add_action('init', [$this->omnify_container->get(Omnify_Storefront_Controller::class), 'register']);
		add_action('template_redirect', [$this->omnify_container->get(Omnify_Download_Controller::class), 'maybe_deliver']);
		$this->omnify_container->get(Omnify_Abandoned_Cart_Service::class)->register();

		$omnify_privacy = $this->omnify_container->get(\Omnify\eCommerce\Support\Omnify_Privacy_Service::class);
		add_filter('wp_privacy_personal_data_exporters', [$omnify_privacy, 'register_exporters']);
		add_filter('wp_privacy_personal_data_erasers', [$omnify_privacy, 'register_erasers']);

		$this->omnify_container->get(\Omnify\eCommerce\Support\Omnify_Tracking_Service::class)->register();

		// Additional extensibility points
		add_action('admin_init', function() {
			do_action('omnify_admin_init', $this->omnify_container);
		});

		$this->initialize_roles_and_caps();

		$this->omnify_booted = true;

		do_action('omnify_booted', $this, $this->omnify_container);
	}

	private function initialize_roles_and_caps(): void {
		// Grant to Administrator if not already granted
		$omnify_admin = get_role('administrator');
		if ($omnify_admin && ! $omnify_admin->has_cap('manage_omnify')) {
			$omnify_admin->add_cap('manage_omnify');
		}

		// Register shop_manager if not already registered
		if (! get_role('shop_manager')) {
			add_role(
				'shop_manager',
				__('Shop Manager', 'omnifywp-ecommerce'),
				[
					'read'          => true,
					'edit_posts'    => true,
					'manage_omnify' => true,
				]
			);
		}

		// Register shop_clerk if not already registered
		if (! get_role('shop_clerk')) {
			add_role(
				'shop_clerk',
				__('Shop Clerk', 'omnifywp-ecommerce'),
				[
					'read'          => true,
					'edit_posts'    => true,
					'manage_omnify' => true,
				]
			);
		}

		// Map Omnify permissions cleanly via user_has_cap without granting manage_options
		add_filter('user_has_cap', static function(array $omnify_allcaps, array $omnify_caps, array $omnify_args) {
			// Administrators inherently have full capabilities
			if (! empty($omnify_allcaps['administrator'])) {
				$omnify_allcaps['manage_omnify'] = true;
				return $omnify_allcaps;
			}

			// Check shop manager and clerk roles
			$omnify_user_roles = [];
			if (! empty($omnify_allcaps['shop_manager'])) {
				$omnify_user_roles[] = 'shop_manager';
			}
			if (! empty($omnify_allcaps['shop_clerk'])) {
				$omnify_user_roles[] = 'shop_clerk';
			}

			if (empty($omnify_user_roles)) {
				return $omnify_allcaps;
			}

			$omnify_settings = get_option('omnify_settings', []);
			$omnify_perms    = (array) ($omnify_settings['role_permissions'] ?? []);

			$omnify_has_any_permission = false;
			$omnify_features = ['dashboard', 'orders', 'products', 'customers', 'coupons', 'abandoned_carts', 'reviews', 'analytics'];
			foreach ($omnify_features as $omnify_feature) {
				$omnify_allowed = (array) ($omnify_perms[$omnify_feature] ?? ['administrator', 'shop_manager']);
				if (! empty(array_intersect($omnify_user_roles, $omnify_allowed))) {
					$omnify_allcaps['manage_omnify_' . $omnify_feature] = true;
					$omnify_has_any_permission = true;
				}
			}

			if ($omnify_has_any_permission) {
				$omnify_allcaps['manage_omnify'] = true;
			}

			return $omnify_allcaps;
		}, 10, 3);
	}
}

