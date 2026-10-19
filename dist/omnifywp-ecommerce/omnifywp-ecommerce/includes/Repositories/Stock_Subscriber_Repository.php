<?php
/**
 * Back-in-Stock email subscription repository.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Repositories;

use Omnify\eCommerce\Database\Omnify_Schema;
use Omnify\eCommerce\Support\Omnify_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Stock_Subscriber_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	private function table(): string {
		return $this->omnify_schema->table('stock_subscribers');
	}

	/**
	 * Subscribe an email address to a product / variation stock alert.
	 */
	public function subscribe(int $product_id, ?int $variation_id, string $email): int|false {
		global $wpdb;

		$product_id = absint($product_id);
		$variation_id = $variation_id ? absint($variation_id) : null;
		$email = sanitize_email($email);

		if (! is_email($email) || $product_id <= 0) {
			return false;
		}

		if ($this->is_subscribed($product_id, $variation_id, $email)) {
			return 0; // Already subscribed
		}

		$table = $this->table();
		$now = current_time('mysql');

		$inserted = Omnify_DB::insert(
			$wpdb,
			$table,
			[
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'email'        => $email,
				'status'       => 'pending',
				'created_at'   => $now,
			],
			['%d', '%d', '%s', '%s', '%s']
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Check if an email is already subscribed and waiting for restock.
	 */
	public function is_subscribed(int $product_id, ?int $variation_id, string $email): bool {
		global $wpdb;

		$table = $this->table();
		$email = sanitize_email($email);

		if (! empty($variation_id)) {
			$sql = $wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND variation_id = %d AND email = %s AND status = 'pending' LIMIT 1",
				$product_id,
				$variation_id,
				$email
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND (variation_id IS NULL OR variation_id = 0) AND email = %s AND status = 'pending' LIMIT 1",
				$product_id,
				$email
			);
		}

		return (bool) Omnify_DB::get_var($wpdb, $sql);
	}

	/**
	 * Retrieve all pending subscribers for a product / variation.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_pending_subscribers(int $product_id, ?int $variation_id = null): array {
		global $wpdb;

		$table = $this->table();
		if ($variation_id) {
			$sql = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE product_id = %d AND variation_id = %d AND status = 'pending' ORDER BY id ASC LIMIT 200",
				$product_id,
				$variation_id
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE product_id = %d AND (variation_id IS NULL OR variation_id = 0) AND status = 'pending' ORDER BY id ASC LIMIT 200",
				$product_id
			);
		}

		$rows = Omnify_DB::get_results($wpdb, $sql, ARRAY_A);
		return is_array($rows) ? $rows : [];
	}

	/**
	 * Mark subscribers as notified.
	 *
	 * @param array<int> $ids
	 */
	public function mark_notified(array $ids): void {
		global $wpdb;

		$ids = array_filter(array_map('absint', $ids));
		if (empty($ids)) {
			return;
		}

		$table = $this->table();
		$placeholders = implode(',', array_fill(0, count($ids), '%d'));
		$now = current_time('mysql');

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		Omnify_DB::query(
			$wpdb,
			$wpdb->prepare(
				"UPDATE {$table} SET status = 'notified', notified_at = %s WHERE id IN ({$placeholders})",
				array_merge([$now], $ids)
			)
		);
	}
}
