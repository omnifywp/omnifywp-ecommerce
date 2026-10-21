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

		$omnify_status = ! empty($omnify_args['status']) ? sanitize_key((string) $omnify_args['status']) : '';
		if ('' !== $omnify_status && 'all' !== $omnify_status) {
			if ('trash' === $omnify_status) {
				$omnify_where  = ['deleted_at IS NOT NULL'];
			} else {
				$omnify_where[]  = 'status = %s';
				$omnify_params[] = $omnify_status;
			}
		}
		if (! empty($omnify_args['product_id'])) {
			$omnify_where[]  = 'product_id = %d';
			$omnify_params[] = (int) $omnify_args['product_id'];
		}

		if (! empty($omnify_args['rating'])) {
			$omnify_where[]  = 'rating = %d';
			$omnify_params[] = min(5, max(1, absint($omnify_args['rating'])));
		}
		if (! empty($omnify_args['search'])) {
			$omnify_like = '%' . $wpdb->esc_like(sanitize_text_field((string) $omnify_args['search'])) . '%';
			$omnify_where[]  = '(customer_name LIKE %s OR customer_email LIKE %s OR review_title LIKE %s OR review_content LIKE %s)';
			array_push($omnify_params, $omnify_like, $omnify_like, $omnify_like, $omnify_like);
		}
		if (! empty($omnify_args['date_from'])) {
			$omnify_where[]  = 'DATE(created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where[]  = 'DATE(created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_per_page = min(200, max(1, absint($omnify_args['per_page'] ?? 100)));
		$omnify_offset   = isset($omnify_args['offset'])
			? max(0, absint($omnify_args['offset']))
			: max(0, (max(1, (int) ($omnify_args['page'] ?? 1)) - 1) * $omnify_per_page);

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "SELECT * FROM {$omnify_table} WHERE {$omnify_where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$omnify_params[] = $omnify_per_page;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);
		$omnify_reviews = $this->hydrate_many(is_array($omnify_rows) ? $omnify_rows : []);
		return apply_filters('omnify_get_reviews_results', $omnify_reviews, $omnify_args);
	}

	/**
	 * Count reviews matching filters.
	 *
	 * @param array $args Optional: status, product_id, rating, search, date_from, date_to
	 */
	public function count(array $omnify_args = []): int {
		global $wpdb;

		$omnify_args = apply_filters('omnify_count_reviews_args', $omnify_args);
		$omnify_table = $this->table('reviews');

		$omnify_where  = ['deleted_at IS NULL'];
		$omnify_params = [];

		if (! empty($omnify_args['status']) && 'all' !== $omnify_args['status']) {
			if ($omnify_args['status'] === 'trash') {
				$omnify_where  = ['deleted_at IS NOT NULL'];
			} else {
				$omnify_where[]  = 'status = %s';
				$omnify_params[] = sanitize_key($omnify_args['status']);
			}
		}
		if (! empty($omnify_args['product_id'])) {
			$omnify_where[]  = 'product_id = %d';
			$omnify_params[] = (int) $omnify_args['product_id'];
		}
		if (! empty($omnify_args['rating'])) {
			$omnify_where[]  = 'rating = %d';
			$omnify_params[] = min(5, max(1, absint($omnify_args['rating'])));
		}
		if (! empty($omnify_args['search'])) {
			$omnify_like = '%' . $wpdb->esc_like(sanitize_text_field((string) $omnify_args['search'])) . '%';
			$omnify_where[]  = '(customer_name LIKE %s OR customer_email LIKE %s OR review_title LIKE %s OR review_content LIKE %s)';
			array_push($omnify_params, $omnify_like, $omnify_like, $omnify_like, $omnify_like);
		}
		if (! empty($omnify_args['date_from'])) {
			$omnify_where[]  = 'DATE(created_at) >= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_from']);
		}
		if (! empty($omnify_args['date_to'])) {
			$omnify_where[]  = 'DATE(created_at) <= %s';
			$omnify_params[] = sanitize_text_field((string) $omnify_args['date_to']);
		}

		$omnify_where_sql = implode(' AND ', $omnify_where);
		$omnify_sql = "SELECT COUNT(*) FROM {$omnify_table} WHERE {$omnify_where_sql}";

		if (! empty($omnify_params)) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare($omnify_sql, ...$omnify_params));
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $omnify_sql);
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

		return $this->hydrate_many(is_array($omnify_rows) ? $omnify_rows : []);
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

		$omnify_verified = ! empty($omnify_data['is_verified_buyer']);
		if (! $omnify_verified && ! empty($omnify_insert_data['customer_email']) && ! empty($omnify_insert_data['product_id'])) {
			$omnify_order_repo = new Omnify_Order_Repository($this->omnify_schema);
			$omnify_verified   = $omnify_order_repo->has_purchased_product($omnify_insert_data['customer_email'], $omnify_insert_data['product_id']);
		}
		$omnify_insert_data['is_verified_buyer'] = $omnify_verified ? 1 : 0;

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
		if (array_key_exists('media', $omnify_data)) {
			$omnify_media = is_array($omnify_data['media']) ? array_values($omnify_data['media']) : [];
			$omnify_base_content = '';
			if (array_key_exists('review_content', $omnify_update)) {
				$omnify_base_content = $omnify_update['review_content'];
			} else {
				$omnify_existing_row = $this->find($omnify_id);
				$omnify_base_content = $omnify_existing_row ? (string) ($omnify_existing_row['content'] ?? '') : '';
			}
			$omnify_base_content = trim(preg_replace('/\[media\].*?\[\/media\]/s', '', $omnify_base_content));
			if (! empty($omnify_media)) {
				$omnify_base_content .= "\n[media]" . wp_json_encode($omnify_media) . "[/media]";
			}
			$omnify_update['review_content'] = $omnify_base_content;
		} elseif (array_key_exists('review_content', $omnify_update)) {
			if (! preg_match('/\[media\].*?\[\/media\]/s', $omnify_update['review_content'])) {
				$omnify_existing_row = $this->find($omnify_id);
				if ($omnify_existing_row && ! empty($omnify_existing_row['media']) && is_array($omnify_existing_row['media'])) {
					$omnify_update['review_content'] .= "\n[media]" . wp_json_encode(array_values($omnify_existing_row['media'])) . "[/media]";
				}
			}
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

	/**
	 * Retrieve review rating summaries (average rating and total count) for multiple product IDs in a single query.
	 * Eliminates N+1 queries on catalog/grid displays.
	 *
	 * @param array<int> $omnify_product_ids
	 * @return array<int, array{rating: float, count: int}>
	 */
	public function get_rating_summaries(array $omnify_product_ids): array {
		$omnify_product_ids = array_values(array_filter(array_map('absint', $omnify_product_ids)));
		if (empty($omnify_product_ids)) {
			return [];
		}

		global $wpdb;
		$omnify_table = $this->table('reviews');
		$omnify_placeholders = implode(',', array_fill(0, count($omnify_product_ids), '%d'));

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_sql = "SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as review_count 
			FROM {$omnify_table} 
			WHERE product_id IN ({$omnify_placeholders}) AND status = 'approved' AND deleted_at IS NULL 
			GROUP BY product_id";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results(
			$wpdb,
			$wpdb->prepare($omnify_sql, ...$omnify_product_ids),
			ARRAY_A
		);

		$omnify_summaries = [];
		foreach ($omnify_product_ids as $omnify_pid) {
			$omnify_summaries[$omnify_pid] = [
				'rating' => 0.0,
				'count'  => 0,
			];
		}

		if (is_array($omnify_rows)) {
			foreach ($omnify_rows as $omnify_row) {
				$omnify_pid = (int) ($omnify_row['product_id'] ?? 0);
				if ($omnify_pid > 0) {
					$omnify_summaries[$omnify_pid] = [
						'rating' => round((float) ($omnify_row['avg_rating'] ?? 0), 1),
						'count'  => (int) ($omnify_row['review_count'] ?? 0),
					];
				}
			}
		}

		return $omnify_summaries;
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
		$omnify_out   = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'trash' => 0];
		foreach (is_array($omnify_rows) ? $omnify_rows : [] as $omnify_row) {
			$omnify_s = $omnify_row['status'] ?? '';
			$omnify_c = (int) $omnify_row['cnt'];
			if (isset($omnify_out[$omnify_s])) $omnify_out[$omnify_s] = $omnify_c;
			$omnify_out['all'] += $omnify_c;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_trash_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$omnify_table} WHERE deleted_at IS NOT NULL");
		$omnify_out['trash'] = $omnify_trash_count;

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

	/**
	 * Hydrate a batch of review rows, resolving verified-buyer status in a single query.
	 *
	 * @param array<int, array<string, mixed>> $omnify_rows
	 * @return array<int, array<string, mixed>>
	 */
	private function hydrate_many(array $omnify_rows): array {
		if (empty($omnify_rows)) {
			return [];
		}

		$omnify_pairs = [];
		foreach ($omnify_rows as $omnify_row) {
			// A null/absent column value would otherwise trigger a per-row lookup in hydrate().
			if (! isset($omnify_row['is_verified_buyer']) && ! empty($omnify_row['customer_email']) && ! empty($omnify_row['product_id'])) {
				$omnify_pairs[] = [(string) $omnify_row['customer_email'], (int) $omnify_row['product_id']];
			}
		}

		$omnify_verified = [];
		if (! empty($omnify_pairs)) {
			$omnify_order_repo = new Omnify_Order_Repository($this->omnify_schema);
			$omnify_verified   = $omnify_order_repo->verified_email_product_pairs($omnify_pairs);
		}

		$omnify_results = [];
		foreach ($omnify_rows as $omnify_row) {
			if (! isset($omnify_row['is_verified_buyer']) && ! empty($omnify_row['customer_email']) && ! empty($omnify_row['product_id'])) {
				$omnify_key = strtolower((string) $omnify_row['customer_email']) . '|' . (int) $omnify_row['product_id'];
				$omnify_row['is_verified_buyer'] = isset($omnify_verified[$omnify_key]);
			}
			$omnify_results[] = $this->hydrate($omnify_row);
		}

		return $omnify_results;
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
		if (isset($omnify_row['is_verified_buyer'])) {
			$omnify_row['is_verified_buyer'] = (bool) $omnify_row['is_verified_buyer'];
		} elseif (! empty($omnify_row['customer_email']) && ! empty($omnify_row['product_id'])) {
			$omnify_order_repo = new Omnify_Order_Repository($this->omnify_schema);
			$omnify_row['is_verified_buyer'] = $omnify_order_repo->has_purchased_product($omnify_row['customer_email'], (int) $omnify_row['product_id']);
		} else {
			$omnify_row['is_verified_buyer'] = false;
		}
		return $omnify_row;
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function now(): string {
		return current_time('mysql', true);
	}
}
