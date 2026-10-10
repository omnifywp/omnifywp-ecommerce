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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Order_Lifecycle_Service {
	public function __construct(
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Customer_Access_Repository $omnify_access
	) {}

	public function register(): void {
		add_action('omnify_order_status_changed', [$this, 'on_order_status_changed'], 10, 3);
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
		}

		// When transitioning from a paid/active state to cancelled, returned, or refunded
		$omnify_active_statuses = ['processing', 'completed', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered'];
		if (in_array($omnify_new_status, ['cancelled', 'refunded', 'returned'], true) && in_array($omnify_old_status, $omnify_active_statuses, true)) {
			// Revoke digital access
			$this->revoke_order_digital_access($omnify_order);

			// Automatically restock if not already restocked
			$this->restock_order_items($omnify_order, sprintf(__('Status changed to %s', 'omnifywp-ecommerce'), $omnify_new_status));
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
		$omnify_restocked_key = '_omnify_restocked_' . $omnify_order_id;
		if (get_transient($omnify_restocked_key)) {
			return;
		}
		set_transient($omnify_restocked_key, 1, 300);

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
				'restock',
				$omnify_qty,
				$omnify_new_qty,
				sprintf('%s (Order %s)', $omnify_reason, (string) ($omnify_order['order_number'] ?: '#' . $omnify_order['id']))
			);
		}
	}
}
