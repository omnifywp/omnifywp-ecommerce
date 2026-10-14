<?php
/**
 * Product reviews persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Review_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * Retrieve all reviews, with optional filtering.
	 *
	 * @param array $args Optional: status, product_id, per_page, page
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_args = apply_filters('omnify_get_reviews_args', $omnify_args);

		$omnify_table = $this->table('reviews');

		$omnify_where  = ['deleted_at IS NULL'];
		$omnify_params = [];

		if (! empty($omnify_args['status'])) {
			if ($omnify_args['status'] === 'trash') {
				$omnify_where  = ['deleted_at IS NOT NULL'];
			} else {
				$omnify_where[]  = 'status = %s';
				$omnify_params[] = $omnify_args['status'];
			}
		}
		if (! empty($omnify_args['product_id'])) {
			$omnify_where[]  = 'product_id = %d';
			$omnify_params[] = (int) $omnify_args['product_id'];
		}

		$omnify_per_page = min(200, (int) ($omnify_args['per_page'] ?? 100));
		$omnify_page     = max(1, (int) ($omnify_args['page'] ?? 1));
		$omnify_offset   = ($omnify_page - 1) * $omnify_per_page;

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "SELECT * FROM {$omnify_table} WHERE {$omnify_where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$omnify_params[] = $omnify_per_page;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);
		$omnify_reviews = array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_reviews_results', $omnify_reviews, $omnify_args);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all_for_product(int $omnify_product_id): array {
		global $wpdb;

		$omnify_table = $this->table('reviews');

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$omnify_table} WHERE product_id = %d AND status = 'approved' AND deleted_at IS NULL ORDER BY created_at DESC",
				$omnify_product_id
			),
			ARRAY_A
		);

		return array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []);
	}

	public function create(array $omnify_data): int {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_create_review_data', $omnify_data);

		$omnify_now = $this->now();

		$omnify_insert_data = [
			'product_id'     => absint($omnify_data['product_id'] ?? 0),
			'customer_name'  => sanitize_text_field(trim((string) ($omnify_data['customer_name'] ?? ''))),
			'customer_email' => sanitize_email(trim((string) ($omnify_data['customer_email'] ?? ''))),
			'rating'         => min(5, max(1, absint($omnify_data['rating'] ?? 5))),
			'review_title'   => sanitize_text_field(trim((string) ($omnify_data['review_title'] ?? ''))),
			'review_content' => sanitize_textarea_field(trim((string) ($omnify_data['review_content'] ?? ''))),
			'status'         => sanitize_key((string) ($omnify_data['status'] ?? 'pending')),
			'created_at'     => $omnify_now,
		];

		// Support media (photos/video) as JSON
		if (!empty($omnify_data['media']) && is_array($omnify_data['media'])) {
			$omnify_insert_data['review_content'] .= "\n[media]" . wp_json_encode($omnify_data['media']) . "[/media]";
		}

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb,  $this->table('reviews'), $omnify_insert_data );

		$omnify_review_id = (int) $wpdb->insert_id;
		if ($omnify_review_id > 0) {
			do_action('omnify_review_created', $omnify_review_id, $omnify_data);
		}

		return $omnify_review_id;
	}

	public function find(int $omnify_id): ?array {
		global $wpdb;

		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table('reviews')} WHERE id = %d", $omnify_id),
			ARRAY_A
		);

		$omnify_review = is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
		return apply_filters('omnify_get_review', $omnify_review, $omnify_id);
	}

	public function find_for_product_by_email(int $omnify_product_id, string $omnify_email): ?array {
		global $wpdb;

		$omnify_email = sanitize_email($omnify_email);
		if ('' === $omnify_email) {
			return null;
		}

		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table('reviews')} WHERE product_id = %d AND customer_email = %s AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1",
				$omnify_product_id,
				$omnify_email
			),
			ARRAY_A
		);

		return is_array($omnify_row) ? $this->hydrate($omnify_row) : null;
	}

	public function update(int $omnify_id, array $omnify_data): bool {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_update_review_data', $omnify_data, $omnify_id);

		$omnify_update = [];
		if (array_key_exists('customer_name', $omnify_data)) {
			$omnify_update['customer_name'] = sanitize_text_field(trim((string) $omnify_data['customer_name']));
		}
		if (array_key_exists('customer_email', $omnify_data)) {
			$omnify_update['customer_email'] = sanitize_email(trim((string) $omnify_data['customer_email']));
		}
		if (array_key_exists('rating', $omnify_data)) {
			$omnify_update['rating'] = min(5, max(1, absint($omnify_data['rating'])));
		}
		if (array_key_exists('review_title', $omnify_data)) {
			$omnify_update['review_title'] = sanitize_text_field(trim((string) $omnify_data['review_title']));
		}
		if (array_key_exists('review_content', $omnify_data)) {
			$omnify_update['review_content'] = sanitize_textarea_field(trim((string) $omnify_data['review_content']));
		}
		if (array_key_exists('status', $omnify_data)) {
			$omnify_update['status'] = sanitize_key((string) $omnify_data['status']);
		}

		if (empty($omnify_update)) {
			return false;
		}

		$omnify_success = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('reviews'), $omnify_update, ['id' => $omnify_id]);
		if ($omnify_success) {
			do_action('omnify_review_updated', $omnify_id, $omnify_data);
		}

		return $omnify_success;
	}

	public function get_average_rating(int $omnify_product_id): float {
		global $wpdb;

		$omnify_table = $this->table('reviews');
		$omnify_avg = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT AVG(rating) FROM {$omnify_table} WHERE product_id = %d AND status = 'approved' AND deleted_at IS NULL",
				$omnify_product_id
			)
		);

		return $omnify_avg !== null ? round((float) $omnify_avg, 1) : 0.0;
	}

	public function get_total_count(int $omnify_product_id): int {
		global $wpdb;

		$omnify_table = $this->table('reviews');
		$omnify_count = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) FROM {$omnify_table} WHERE product_id = %d AND status = 'approved' AND deleted_at IS NULL",
				$omnify_product_id
			)
		);

		return (int) $omnify_count;
	}

	public function get_review_count(int $omnify_product_id): int {
		return $this->get_total_count($omnify_product_id);
	}

	public function get_rating_breakdown(int $omnify_product_id): array {
		global $wpdb;

		$omnify_table = $this->table('reviews');
		$omnify_results = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT rating, COUNT(*) as count FROM {$omnify_table} WHERE product_id = %d AND status = 'approved' AND deleted_at IS NULL GROUP BY rating",
				$omnify_product_id
			),
			ARRAY_A
		);

		$omnify_breakdown = [
			5 => ['count' => 0, 'percentage' => 0],
			4 => ['count' => 0, 'percentage' => 0],
			3 => ['count' => 0, 'percentage' => 0],
			2 => ['count' => 0, 'percentage' => 0],
			1 => ['count' => 0, 'percentage' => 0],
		];

		$omnify_total = 0;
		if (is_array($omnify_results)) {
			foreach ($omnify_results as $omnify_row) {
				$omnify_rating = (int) $omnify_row['rating'];
				$omnify_count  = (int) $omnify_row['count'];
				if (isset($omnify_breakdown[$omnify_rating])) {
					$omnify_breakdown[$omnify_rating]['count'] = $omnify_count;
					$omnify_total += $omnify_count;
				}
			}
		}

		if ($omnify_total > 0) {
			foreach ($omnify_breakdown as $omnify_rating => $omnify_data) {
				$omnify_breakdown[$omnify_rating]['percentage'] = round(($omnify_data['count'] / $omnify_total) * 100);
			}
		}

		return $omnify_breakdown;
	}

	public function update_status(int $omnify_id, string $omnify_status): bool {
		global $wpdb;
		$omnify_allowed = ['pending', 'approved', 'rejected'];
		if (! in_array($omnify_status, $omnify_allowed, true)) return false;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_old_status = (string) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SELECT status FROM {$this->table('reviews')} WHERE id = %d", $omnify_id));

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('reviews'),
			['status' => $omnify_status],
			['id' => $omnify_id]
		);

		$omnify_success = $omnify_result !== false;
		if ($omnify_success && $omnify_old_status !== $omnify_status) {
			do_action('omnify_review_status_changed', $omnify_id, $omnify_status, $omnify_old_status);
		}

		return $omnify_success;
	}

	public function count_by_status(): array {
		global $wpdb;
		$omnify_table = $this->table('reviews');
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_rows  = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, "SELECT status, COUNT(*) as cnt FROM {$omnify_table} WHERE deleted_at IS NULL GROUP BY status", ARRAY_A);
		$omnify_out   = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
		foreach (is_array($omnify_rows) ? $omnify_rows : [] as $omnify_row) {
			$omnify_s = $omnify_row['status'] ?? '';
			$omnify_c = (int) $omnify_row['cnt'];
			if (isset($omnify_out[$omnify_s])) $omnify_out[$omnify_s] = $omnify_c;
			$omnify_out['all'] += $omnify_c;
		}
		return $omnify_out;
	}

	public function trash(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('reviews'),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_review_trashed', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;
		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('reviews'),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			do_action('omnify_review_restored', $omnify_id);
		}
		return (bool) $omnify_updated;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		$omnify_result = \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, 
			$this->table('reviews'),
			['id' => $omnify_id]
		);

		$omnify_success = $omnify_result !== false;
		if ($omnify_success) {
			do_action('omnify_review_deleted', $omnify_id);
		}

		return $omnify_success;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']             = (int) $omnify_row['id'];
		$omnify_row['product_id']     = (int) $omnify_row['product_id'];
		$omnify_row['rating']         = (int) $omnify_row['rating'];
		// Alias fields for template compatibility
		$omnify_row['name']           = $omnify_row['customer_name']  ?? '';
		$omnify_row['reviewer_name']  = $omnify_row['customer_name']  ?? '';
		$omnify_row['reviewer_email'] = $omnify_row['customer_email'] ?? '';
		$omnify_content = $omnify_row['review_content'] ?? '';
		// Extract media if embedded
		if (preg_match('/\[media\](.*?)\[\/media\]/s', $omnify_content, $omnify_m)) {
			$omnify_row['media'] = json_decode($omnify_m[1], true) ?: [];
			$omnify_content = preg_replace('/\[media\].*?\[\/media\]/s', '', $omnify_content);
		}
		$omnify_row['content']        = trim($omnify_content);
		$omnify_row['title']          = $omnify_row['review_title']   ?? '';
		return $omnify_row;
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
