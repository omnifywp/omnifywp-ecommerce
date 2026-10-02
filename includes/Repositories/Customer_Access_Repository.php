<?php
/**
 * Manual customer access persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Customer_Access_Repository {
	public function __construct(
		private Omnify_Schema $omnify_schema,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_files
	) {}

	public function grant(int $omnify_customer_id, int $omnify_product_id, ?string $omnify_expires_at = null): int {
		global $wpdb;

		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if ($omnify_product && 'bundle' === $omnify_product['type']) {
			$omnify_bundled_ids = ! empty($omnify_product['bundled_ids']) && is_array($omnify_product['bundled_ids']) ? $omnify_product['bundled_ids'] : [];
			foreach ($omnify_bundled_ids as $omnify_child_id) {
				$omnify_child_product = $this->omnify_products->find((int) $omnify_child_id);
				if ($omnify_child_product) {
					$omnify_child_expires = $omnify_expires_at;
					if (null === $omnify_child_expires) {
						$omnify_days = absint($omnify_child_product['download_expiry_days'] ?? 0);
						if ($omnify_days > 0) {
							$omnify_child_expires = gmdate('Y-m-d H:i:s', time() + ($omnify_days * 86400));
						}
					}
					$this->grant($omnify_customer_id, (int) $omnify_child_id, $omnify_child_expires);
				}
			}
		}

		$omnify_now = $this->now();
		\Omnify\eCommerce\Support\Omnify_DB::replace(
			$wpdb,
			$this->table(),
			[
				'customer_id' => $omnify_customer_id,
				'product_id'  => $omnify_product_id,
				'status'      => 'active',
				'expires_at'  => $this->date_or_null($omnify_expires_at),
				'granted_by'  => get_current_user_id() ?: null,
				'created_at'  => $omnify_now,
				'updated_at'  => $omnify_now,
			],
			['%d', '%d', '%s', '%s', '%d', '%s', '%s']
		);

		$omnify_insert_id = (int) $wpdb->insert_id;

		$this->omnify_customers->record_activity($omnify_customer_id, 'access_granted', __('Product access granted.', 'omnifywp-ecommerce'), ['product_id' => $omnify_product_id]);

		do_action('omnify_customer_access_granted', $omnify_customer_id, $omnify_product_id, $omnify_expires_at, $omnify_insert_id);

		return $omnify_insert_id;
	}

	/**
	 * @param array<int, int> $customer_ids
	 * @return array<int, int>
	 */
	public function bulk_grant(array $omnify_customer_ids, int $omnify_product_id, ?string $omnify_expires_at = null): array {
		$omnify_granted = [];

		foreach (array_unique(array_map('absint', $omnify_customer_ids)) as $omnify_customer_id) {
			if ($omnify_customer_id > 0 && $this->omnify_customers->find($omnify_customer_id)) {
				$omnify_granted[] = $this->grant($omnify_customer_id, $omnify_product_id, $omnify_expires_at);
			}
		}

		return $omnify_granted;
	}

	public function revoke(int $omnify_customer_id, int $omnify_product_id): bool {
		global $wpdb;

		$omnify_updated = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table(),
			[
				'status'     => 'revoked',
				'updated_at' => $this->now(),
			],
			[
				'customer_id' => $omnify_customer_id,
				'product_id'  => $omnify_product_id,
			]
		);

		if ($omnify_updated) {
			$this->omnify_customers->record_activity($omnify_customer_id, 'access_revoked', __('Product access revoked.', 'omnifywp-ecommerce'), ['product_id' => $omnify_product_id]);
			do_action('omnify_customer_access_revoked', $omnify_customer_id, $omnify_product_id);
		}

		return $omnify_updated;
	}

	public function has_access(int $omnify_customer_id, int $omnify_product_id): bool {
		global $wpdb;

		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table()} WHERE customer_id = %d AND product_id = %d AND status = 'active'", $omnify_customer_id, $omnify_product_id),
			ARRAY_A
		);

		if (! is_array($omnify_row)) {
			return false;
		}

		return empty($omnify_row['expires_at']) || strtotime((string) $omnify_row['expires_at']) >= time();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function for_customer(int $omnify_customer_id): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare("SELECT * FROM {$this->table()} WHERE customer_id = %d ORDER BY updated_at DESC", $omnify_customer_id), ARRAY_A);

		return array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function files_for_customer(int $omnify_customer_id): array {
		$omnify_items = [];

		foreach ($this->for_customer($omnify_customer_id) as $omnify_access) {
			if ('active' !== $omnify_access['status'] || ! $this->has_access($omnify_customer_id, (int) $omnify_access['product_id'])) {
				continue;
			}

			$omnify_product = $this->omnify_products->find((int) $omnify_access['product_id']);
			if (! $omnify_product || 'published' !== $omnify_product['status']) {
				continue;
			}

			foreach ($this->omnify_files->active_for_product((int) $omnify_access['product_id']) as $omnify_file) {
				$omnify_file['product'] = $omnify_product;
				$omnify_file['access'] = $omnify_access;
				$omnify_items[] = $omnify_file;
			}
		}

		return $omnify_items;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id'] = (int) $omnify_row['id'];
		$omnify_row['customer_id'] = (int) $omnify_row['customer_id'];
		$omnify_row['product_id'] = (int) $omnify_row['product_id'];
		$omnify_row['granted_by'] = null === $omnify_row['granted_by'] ? null : (int) $omnify_row['granted_by'];
		$omnify_row['is_expired'] = ! empty($omnify_row['expires_at']) && strtotime((string) $omnify_row['expires_at']) < time();

		return $omnify_row;
	}

	private function date_or_null(?string $omnify_date): ?string {
		if (empty($omnify_date)) {
			return null;
		}

		$omnify_timestamp = strtotime($omnify_date);

		return $omnify_timestamp ? gmdate('Y-m-d H:i:s', $omnify_timestamp) : null;
	}

	private function table(): string {
		return $this->omnify_schema->table('customer_access');
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
