<?php
/**
 * Small service container.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

use Omnify\eCommerce\Admin\Omnify_Admin_Page;
use Omnify\eCommerce\Api\Omnify_Rest_Server;
use Omnify\eCommerce\Database\Omnify_Migrations;
use Omnify\eCommerce\Database\Omnify_Schema;
use Omnify\eCommerce\Downloads\Omnify_Download_Controller;
use Omnify\eCommerce\Downloads\Omnify_Download_Permission_Service;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;
use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Download_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Coupon_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Wishlist_Repository;
use Omnify\eCommerce\Repositories\Omnify_Admin_Activity_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;
use Omnify\eCommerce\Storefront\Omnify_Storefront_Controller;

class Omnify_Container {
	/** @var array<string, callable(self): object> */
	private array $omnify_factories = [];

	/** @var array<string, object> */
	private array $omnify_instances = [];

	public function __construct() {
		$this->set(Omnify_Schema::class, static fn (): Omnify_Schema => new Omnify_Schema());
		$this->set(Omnify_Migrations::class, static fn (self $omnify_c): Omnify_Migrations => new Omnify_Migrations($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Settings_Repository::class, static fn (): Omnify_Settings_Repository => new Omnify_Settings_Repository());
		$this->set(Omnify_Customer_Repository::class, static fn (self $omnify_c): Omnify_Customer_Repository => new Omnify_Customer_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Download_Repository::class, static fn (self $omnify_c): Omnify_Download_Repository => new Omnify_Download_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Abandoned_Cart_Repository::class, static fn (self $omnify_c): Omnify_Abandoned_Cart_Repository => new Omnify_Abandoned_Cart_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Product_Repository::class, static fn (self $omnify_c): Omnify_Product_Repository => new Omnify_Product_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Product_File_Repository::class, static fn (self $omnify_c): Omnify_Product_File_Repository => new Omnify_Product_File_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Order_Repository::class, static fn (self $omnify_c): Omnify_Order_Repository => new Omnify_Order_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Coupon_Repository::class, static fn (self $omnify_c): Omnify_Coupon_Repository => new Omnify_Coupon_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Review_Repository::class, static fn (self $omnify_c): Omnify_Review_Repository => new Omnify_Review_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Wishlist_Repository::class, static fn (self $omnify_c): Omnify_Wishlist_Repository => new Omnify_Wishlist_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Admin_Activity_Repository::class, static fn (self $omnify_c): Omnify_Admin_Activity_Repository => new Omnify_Admin_Activity_Repository($omnify_c->get(Omnify_Schema::class)));
		$this->set(Omnify_Customer_Access_Repository::class, static fn (self $omnify_c): Omnify_Customer_Access_Repository => new Omnify_Customer_Access_Repository($omnify_c->get(Omnify_Schema::class), $omnify_c->get(Omnify_Customer_Repository::class), $omnify_c->get(Omnify_Product_Repository::class), $omnify_c->get(Omnify_Product_File_Repository::class)));
		$this->set(Omnify_Signed_Url_Service::class, static fn (): Omnify_Signed_Url_Service => new Omnify_Signed_Url_Service());
		$this->set(Omnify_Email_Service::class, static fn (self $omnify_c): Omnify_Email_Service => new Omnify_Email_Service($omnify_c->get(Omnify_Order_Repository::class), $omnify_c->get(Omnify_Product_File_Repository::class), $omnify_c->get(Omnify_Signed_Url_Service::class), $omnify_c->get(Omnify_Product_Repository::class)));
		$this->set(Omnify_Abandoned_Cart_Service::class, static fn (self $omnify_c): Omnify_Abandoned_Cart_Service => new Omnify_Abandoned_Cart_Service($omnify_c->get(Omnify_Settings_Repository::class), $omnify_c->get(Omnify_Abandoned_Cart_Repository::class), $omnify_c->get(Omnify_Email_Service::class)));
		$this->set(Omnify_Download_Permission_Service::class, static fn (self $omnify_c): Omnify_Download_Permission_Service => new Omnify_Download_Permission_Service($omnify_c->get(Omnify_Product_File_Repository::class), $omnify_c->get(Omnify_Product_Repository::class), $omnify_c->get(Omnify_Customer_Access_Repository::class)));
		$this->set(Omnify_Download_Controller::class, static fn (self $omnify_c): Omnify_Download_Controller => new Omnify_Download_Controller($omnify_c->get(Omnify_Product_File_Repository::class), $omnify_c->get(Omnify_Download_Repository::class), $omnify_c->get(Omnify_Signed_Url_Service::class), $omnify_c->get(Omnify_Download_Permission_Service::class), $omnify_c->get(Omnify_Customer_Repository::class), $omnify_c->get(Omnify_Customer_Access_Repository::class)));
		$this->set(Omnify_Storefront_Controller::class, static fn (self $omnify_c): Omnify_Storefront_Controller => new Omnify_Storefront_Controller($omnify_c->get(Omnify_Product_Repository::class), $omnify_c->get(Omnify_Product_File_Repository::class), $omnify_c->get(Omnify_Customer_Repository::class), $omnify_c->get(Omnify_Customer_Access_Repository::class), $omnify_c->get(Omnify_Signed_Url_Service::class), $omnify_c->get(Omnify_Order_Repository::class), $omnify_c->get(Omnify_Download_Repository::class), $omnify_c->get(Omnify_Review_Repository::class), $omnify_c->get(Omnify_Email_Service::class), $omnify_c->get(Omnify_Wishlist_Repository::class)));
		$this->set(Omnify_Payment_Gateway_Service::class, static fn (self $omnify_c): Omnify_Payment_Gateway_Service => new Omnify_Payment_Gateway_Service($omnify_c->get(Omnify_Settings_Repository::class)));
		$this->set(Omnify_Demo_Data_Service::class, static fn (self $omnify_c): Omnify_Demo_Data_Service => new Omnify_Demo_Data_Service(
			$omnify_c->get(Omnify_Product_Repository::class),
			$omnify_c->get(Omnify_Product_File_Repository::class),
			$omnify_c->get(Omnify_Order_Repository::class),
			$omnify_c->get(Omnify_Customer_Repository::class),
			$omnify_c->get(Omnify_Review_Repository::class),
			$omnify_c->get(Omnify_Admin_Activity_Repository::class),
			$omnify_c->get(Omnify_Schema::class),
			$omnify_c->get(Omnify_Customer_Access_Repository::class)
		));
		$this->set(Omnify_Admin_Page::class, static fn (self $omnify_c): Omnify_Admin_Page => new Omnify_Admin_Page(
			$omnify_c->get(Omnify_Settings_Repository::class),
			$omnify_c->get(Omnify_Product_Repository::class),
			$omnify_c->get(Omnify_Product_File_Repository::class),
			$omnify_c->get(Omnify_Customer_Repository::class),
			$omnify_c->get(Omnify_Customer_Access_Repository::class),
			$omnify_c->get(Omnify_Order_Repository::class),
			$omnify_c->get(Omnify_Signed_Url_Service::class),
			$omnify_c->get(Omnify_Email_Service::class),
			$omnify_c->get(Omnify_Coupon_Repository::class),
			$omnify_c->get(Omnify_Review_Repository::class),
			$omnify_c->get(Omnify_Abandoned_Cart_Repository::class),
			$omnify_c->get(Omnify_Payment_Gateway_Service::class),
			$omnify_c->get(Omnify_Admin_Activity_Repository::class),
			$omnify_c->get(Omnify_Demo_Data_Service::class)
		));
		$this->set(Omnify_Rest_Server::class, static fn (self $omnify_c): Omnify_Rest_Server => new Omnify_Rest_Server(
			$omnify_c->get(Omnify_Settings_Repository::class),
			$omnify_c->get(Omnify_Product_Repository::class),
			$omnify_c->get(Omnify_Product_File_Repository::class),
			$omnify_c->get(Omnify_Customer_Repository::class),
			$omnify_c->get(Omnify_Signed_Url_Service::class),
			$omnify_c->get(Omnify_Download_Permission_Service::class),
			$omnify_c->get(Omnify_Customer_Access_Repository::class),
			$omnify_c->get(Omnify_Order_Repository::class),
			$omnify_c->get(Omnify_Email_Service::class),
			$omnify_c->get(Omnify_Coupon_Repository::class),
			$omnify_c->get(Omnify_Review_Repository::class),
			$omnify_c->get(Omnify_Abandoned_Cart_Repository::class),
			$omnify_c->get(Omnify_Wishlist_Repository::class),
			$omnify_c->get(Omnify_Payment_Gateway_Service::class),
			$omnify_c->get(Omnify_Admin_Activity_Repository::class),
			$omnify_c->get(Omnify_Download_Repository::class),
			$omnify_c->get(Omnify_Abandoned_Cart_Service::class)
		));
		$this->set(Omnify_Privacy_Service::class, static fn (self $omnify_c): Omnify_Privacy_Service => new Omnify_Privacy_Service(
			$omnify_c->get(Omnify_Schema::class),
			$omnify_c->get(Omnify_Customer_Repository::class),
			$omnify_c->get(Omnify_Order_Repository::class),
			$omnify_c->get(Omnify_Review_Repository::class),
			$omnify_c->get(Omnify_Abandoned_Cart_Repository::class)
		));
		$this->set(Omnify_Tracking_Service::class, static fn (self $omnify_c): Omnify_Tracking_Service => new Omnify_Tracking_Service($omnify_c->get(Omnify_Settings_Repository::class)));

		/**
		 * Fires after all default service factories are registered.
		 *
		 * Developers may call $container->set(Service::class, $factory) here to replace
		 * a repository, controller, or support service before it is resolved.
		 */
		do_action('omnify_container_registered', $this);
	}

	public function set(string $omnify_id, callable $omnify_factory): void {
		$this->omnify_factories[$omnify_id] = apply_filters('omnify_container_factory', $omnify_factory, $omnify_id, $this);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $id
	 * @return T
	 */
	public function get(string $omnify_id): object {
		if (! isset($this->omnify_instances[$omnify_id])) {
			if (! isset($this->omnify_factories[$omnify_id])) {
				throw new \InvalidArgumentException(sprintf('Service "%s" is not registered in the Omnify container.', esc_html($omnify_id)));
			}

			$omnify_instance = ($this->omnify_factories[$omnify_id])($this);
			$this->omnify_instances[$omnify_id] = apply_filters('omnify_container_instance', $omnify_instance, $omnify_id, $this);
		}

		return $this->omnify_instances[$omnify_id];
	}

	public function has(string $omnify_id): bool {
		return isset($this->omnify_factories[$omnify_id]) || isset($this->omnify_instances[$omnify_id]);
	}
}
