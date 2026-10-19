<?php
/**
 * Centralized Order Lifecycle Service.
 *
 * Listens to order status changes to automate:
 * 1. Granting digital download access when an order transitions to 'completed'.
 * 2. Revoking digital download access when an order is cancelled or refunded.
 * 3. Restocking inventory when an order is cancelled, returned, or refunded.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Coupon_Repository;
use Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Order_Lifecycle_Service {
	/**
	 * @var array<int, bool>
	 */
	private static array $completed_orders_fired = [];

	public function __construct(
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Customer_Access_Repository $omnify_access,
		private ?Omnify_Coupon_Repository $omnify_coupons = null,
		private ?Omnify_Abandoned_Cart_Repository $omnify_abandoned_carts = null
	) {}

	private const EXPIRY_CRON_HOOK = 'omnify_expired_pending_orders_cleanup';

	public function register(): void {
		add_action('omnify_order_status_changed', [$this, 'on_order_status_changed'], 10, 3);
		add_action('omnify_order_created', [$this, 'on_order_created'], 10, 2);
		add_action(self::EXPIRY_CRON_HOOK, [$this, 'cancel_expired_pending_orders']);
		$this->schedule_expiry_cron();
	}

	public function on_order_created(int $omnify_order_id, array $omnify_data): void {
		$omnify_status = sanitize_key((string) ($omnify_data['status'] ?? 'pending'));
		if ('completed' === $omnify_status) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order) {
				$this->grant_order_digital_access($omnify_order);
				$this->reduce_order_items_stock($omnify_order);
				$this->mark_abandoned_cart_recovered_for_order($omnify_order);
			}
			$this->trigger_order_completed_once($omnify_order_id);
		} elseif ('processing' === $omnify_status) {
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if ($omnify_order) {
				$this->reduce_order_items_stock($omnify_order);
				$this->mark_abandoned_cart_recovered_for_order($omnify_order);
			}
		}
	}

	public function on_order_status_changed(int $omnify_order_id, string $omnify_new_status, string $omnify_old_status): void {
		if ($omnify_new_status === $omnify_old_status) {
			return;
		}

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order) {
			return;
		}

		// When entering 'completed', grant digital access if not already granted
		if ('completed' === $omnify_new_status) {
			$this->grant_order_digital_access($omnify_order);
			$this->reduce_order_items_stock($omnify_order);
			$this->mark_abandoned_cart_recovered_for_order($omnify_order);
			if ('completed' !== $omnify_old_status) {
				$this->trigger_order_completed_once($omnify_order_id);
			}
		} elseif ('processing' === $omnify_new_status) {
			$this->reduce_order_items_stock($omnify_order);
			$this->mark_abandoned_cart_recovered_for_order($omnify_order);
		}

		// When transitioning from a paid/active or pending state to cancelled, returned, or refunded
		$omnify_eligible_statuses = ['pending', 'pending_payment', 'processing', 'completed', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'partially_refunded'];
		if (in_array($omnify_new_status, ['cancelled', 'refunded', 'returned'], true) && in_array($omnify_old_status, $omnify_eligible_statuses, true)) {
			// Revoke digital access
			$this->revoke_order_digital_access($omnify_order);

			// Automatically restock if not already restocked
			$omnify_settings = get_option('omnify_settings', []);
			$omnify_was_reduced = ! empty($omnify_order['stock_reduced'])
				|| ! empty(get_option('_omnify_stock_reduced_' . $omnify_order_id)) 
				|| ! empty($omnify_settings['reduce_stock_on_checkout']) 
				|| in_array($omnify_old_status, ['processing', 'completed', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'partially_refunded'], true);

			if ($omnify_was_reduced) {
				$this->restock_order_items($omnify_order, sprintf(__('Status changed to %s', 'omnifywp-ecommerce'), $omnify_new_status));
			}

			// Restore coupon usage if coupon was used
			$this->restore_coupon_usage($omnify_order);
		}
	}

	public function grant_order_digital_access(array $omnify_order): void {
		$omnify_customer_id = (int) ($omnify_order['customer_id'] ?? 0);
		if ($omnify_customer_id <= 0 || empty($omnify_order['items']) || ! is_array($omnify_order['items'])) {
			return;
		}

		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			if ($omnify_product_id <= 0) {
				continue;
			}

			$omnify_product = $this->omnify_products->find($omnify_product_id);
			if (! $omnify_product) {
				continue;
			}

			$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
			$omnify_is_physical = 'physical' === ($omnify_product['type'] ?? 'download')
				|| ('variable' === ($omnify_product['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));

			if ($omnify_is_physical) {
				continue;
			}

			$omnify_expires_at = null;
			$omnify_days = absint($omnify_product['download_expiry_days'] ?? 0);
			if ($omnify_days > 0) {
				$omnify_expires_at = gmdate('Y-m-d H:i:s', time() + ($omnify_days * 86400));
			}

			$this->omnify_access->grant($omnify_customer_id, $omnify_product_id, $omnify_expires_at);
		}
	}

	public function revoke_order_digital_access(array $omnify_order): void {
		$omnify_customer_id = (int) ($omnify_order['customer_id'] ?? 0);
		if ($omnify_customer_id <= 0 || empty($omnify_order['items']) || ! is_array($omnify_order['items'])) {
			return;
		}

		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			if ($omnify_product_id > 0) {
				$this->omnify_access->revoke($omnify_customer_id, $omnify_product_id);
			}
		}
	}

	public function restock_order_items(array $omnify_order, string $omnify_reason): void {
		$omnify_order_id = absint($omnify_order['id'] ?? 0);
		if (! $omnify_order_id || empty($omnify_order['items']) || ! is_array($omnify_order['items'])) {
			return;
		}

		// Prevent duplicate restocking for the same order
		if (! empty($omnify_order['restocked']) || get_option('_omnify_restocked_' . $omnify_order_id)) {
			return;
		}

		// Only restock if stock was actually reduced for this order
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_was_reduced = ! empty($omnify_order['stock_reduced'])
			|| ! empty(get_option('_omnify_stock_reduced_' . $omnify_order_id))
			|| ! empty($omnify_settings['reduce_stock_on_checkout'])
			|| in_array((string) ($omnify_order['status'] ?? ''), ['processing', 'completed', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered'], true);

		if (! $omnify_was_reduced) {
			$this->omnify_orders->update($omnify_order_id, [
				'restocked'     => 1,
				'stock_reduced' => 0,
			]);
			return;
		}

		$this->omnify_orders->update($omnify_order_id, [
			'restocked'     => 1,
			'stock_reduced' => 0,
		]);
		delete_option('_omnify_stock_reduced_' . $omnify_order_id);
		update_option('_omnify_restocked_' . $omnify_order_id, '1', false);

		$omnify_items_restocked = 0;
		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			$omnify_qty = max(0, (int) ($omnify_item['quantity'] ?? 0) - (int) ($omnify_item['refunded_qty'] ?? 0));
			if ($omnify_product_id <= 0 || $omnify_qty <= 0) {
				continue;
			}

			$omnify_variation_id = ! empty($omnify_item['variation_id']) ? absint($omnify_item['variation_id']) : null;
			$omnify_product = $this->omnify_products->find($omnify_product_id);
			if (! $omnify_product) {
				continue;
			}

			$omnify_stock_row = null;
			if ($omnify_variation_id) {
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
				sprintf('%s (Order %s)', $omnify_reason, (string) ($omnify_order['order_number'] ?: '#' . $omnify_order['id'])),
				null
			);
			$omnify_items_restocked++;
		}

		if ($omnify_items_restocked > 0) {
			$this->omnify_orders->add_note(
				$omnify_order_id,
				sprintf(__('Restocked items back to inventory (%s).', 'omnifywp-ecommerce'), $omnify_reason)
			);
		}
	}

	public function reduce_order_items_stock(array $omnify_order): void {
		$omnify_order_id = absint($omnify_order['id'] ?? 0);
		if (! $omnify_order_id || empty($omnify_order['items']) || ! is_array($omnify_order['items'])) {
			return;
		}

		if (! empty($omnify_order['stock_reduced']) || get_option('_omnify_stock_reduced_' . $omnify_order_id)) {
			return;
		}

		$omnify_settings = get_option('omnify_settings', []);
		// If reduce_stock_on_checkout is enabled, stock was already deducted at checkout creation
		if (! empty($omnify_settings['reduce_stock_on_checkout'])) {
			$this->omnify_orders->update($omnify_order_id, [
				'stock_reduced' => 1,
				'restocked'     => 0,
			]);
			delete_option('_omnify_restocked_' . $omnify_order_id);
			return;
		}

		$this->omnify_orders->update($omnify_order_id, [
			'stock_reduced' => 1,
			'restocked'     => 0,
		]);
		delete_option('_omnify_restocked_' . $omnify_order_id);

		$omnify_items_reduced = 0;
		foreach ($omnify_order['items'] as $omnify_item) {
			$omnify_product_id = absint($omnify_item['product_id'] ?? 0);
			$omnify_qty = absint($omnify_item['quantity'] ?? 0);
			if ($omnify_product_id <= 0 || $omnify_qty <= 0) {
				continue;
			}

			$omnify_variation_id = ! empty($omnify_item['variation_id']) ? absint($omnify_item['variation_id']) : null;
			$omnify_product = $this->omnify_products->find($omnify_product_id);
			if (! $omnify_product) {
				continue;
			}

			$omnify_stock_row = null;
			if ($omnify_variation_id) {
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

			$omnify_updated = $this->omnify_products->reduce_stock($omnify_product_id, $omnify_variation_id, $omnify_qty);
			if ($omnify_updated) {
				$omnify_new_qty = $omnify_variation_id
					? $this->omnify_products->get_variation_stock_qty($omnify_variation_id)
					: $this->omnify_products->get_product_stock_qty($omnify_product_id);

				$this->omnify_products->log_stock_change(
					$omnify_product_id,
					$omnify_variation_id,
					-$omnify_qty,
					$omnify_new_qty,
					sprintf(__('Order %s paid', 'omnifywp-ecommerce'), (string) ($omnify_order['order_number'] ?: '#' . $omnify_order['id'])),
					null
				);
				$omnify_items_reduced++;
			}
		}

		if ($omnify_items_reduced > 0) {
			$this->omnify_orders->add_note(
				$omnify_order_id,
				__('Reduced items stock on order processing/completion.', 'omnifywp-ecommerce')
			);
		}
	}

	public function restore_coupon_usage(array $omnify_order): void {
		$omnify_order_id = absint($omnify_order['id'] ?? 0);
		if (! $omnify_order_id || empty($omnify_order['coupon_code']) || ! $this->omnify_coupons) {
			return;
		}

		$omnify_restored_key = '_omnify_coupon_restored_' . $omnify_order_id;
		if (get_option($omnify_restored_key)) {
			return;
		}
		update_option($omnify_restored_key, '1', false);

		$omnify_coupon = $this->omnify_coupons->find_by_code($omnify_order['coupon_code'], true);
		if ($omnify_coupon && ! empty($omnify_coupon['id'])) {
			$this->omnify_coupons->decrement_usage((int) $omnify_coupon['id']);
		}
	}

	private function trigger_order_completed_once(int $omnify_order_id): void {
		if ($omnify_order_id <= 0 || isset(self::$completed_orders_fired[$omnify_order_id])) {
			return;
		}
		self::$completed_orders_fired[$omnify_order_id] = true;
		do_action('omnify_order_completed', $omnify_order_id);
	}

	private function mark_abandoned_cart_recovered_for_order(array $omnify_order): void {
		if (! $this->omnify_abandoned_carts) {
			return;
		}

		$omnify_order_id = (int) ($omnify_order['id'] ?? 0);
		if ($omnify_order_id <= 0) {
			return;
		}

		$omnify_email = sanitize_email((string) ($omnify_order['customer_email'] ?? $omnify_order['email'] ?? ''));
		if ('' !== $omnify_email) {
			$this->omnify_abandoned_carts->mark_recovered_by_email($omnify_email, $omnify_order_id);
		}
	}

	public function schedule_expiry_cron(): void {
		if (false !== get_transient('omnify_order_expiry_cron_checked')) {
			return;
		}
		if (! wp_next_scheduled(self::EXPIRY_CRON_HOOK)) {
			wp_schedule_event(time() + (15 * MINUTE_IN_SECONDS), 'hourly', self::EXPIRY_CRON_HOOK);
		}
		set_transient('omnify_order_expiry_cron_checked', 1, DAY_IN_SECONDS);
	}

	public function cancel_expired_pending_orders(): void {
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_hold_minutes = isset($omnify_settings['hold_stock_minutes']) ? (int) $omnify_settings['hold_stock_minutes'] : 60;
		if ($omnify_hold_minutes <= 0) {
			return;
		}

		global $wpdb;
		$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
		$omnify_cutoff = gmdate('Y-m-d H:i:s', time() - ($omnify_hold_minutes * MINUTE_IN_SECONDS));

		$omnify_expired_ids = \Omnify\eCommerce\Support\Omnify_DB::get_col(
			$wpdb,
			$wpdb->prepare(
				"SELECT id FROM {$omnify_orders_table} 
				WHERE status IN ('pending', 'pending_payment') 
				AND created_at <= %s 
				ORDER BY id ASC 
				LIMIT 50",
				$omnify_cutoff
			)
		);

		if (empty($omnify_expired_ids)) {
			return;
		}

		foreach ($omnify_expired_ids as $omnify_order_id) {
			$omnify_order_id = absint($omnify_order_id);
			$omnify_order = $this->omnify_orders->find($omnify_order_id);
			if (! $omnify_order || ! in_array($omnify_order['status'], ['pending', 'pending_payment'], true)) {
				continue;
			}

			$omnify_old_status = (string) $omnify_order['status'];
			$this->omnify_orders->update_status($omnify_order_id, 'cancelled');

			$this->omnify_orders->add_note(
				$omnify_order_id,
				sprintf(
					__('Order cancelled automatically: payment was not received within %d minutes.', 'omnifywp-ecommerce'),
					$omnify_hold_minutes
				)
			);
		}

		if ($this->omnify_abandoned_carts) {
			$this->omnify_abandoned_carts->mark_expired();
			$this->omnify_abandoned_carts->cleanup_expired(90);
		}
	}
}

