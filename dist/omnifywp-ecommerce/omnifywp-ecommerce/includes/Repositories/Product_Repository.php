<?php
/**
 * Product management persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Product_Repository {
	private const STATUSES = ['draft', 'published'];

	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		$omnify_cache_version = $this->get_cache_version();
		$omnify_cache_key     = 'omnify_prod_all_' . md5(serialize($omnify_args)) . '_' . $omnify_cache_version;
		$omnify_cached        = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return $omnify_cached;
		}

		global $wpdb;

		$omnify_args = apply_filters('omnify_get_products_args', $omnify_args);

		$omnify_status = sanitize_key((string) ($omnify_args['status'] ?? 'all'));
		$omnify_category = sanitize_text_field((string) ($omnify_args['category'] ?? ''));
		$omnify_type = sanitize_key((string) ($omnify_args['type'] ?? ''));
		$omnify_search = sanitize_text_field((string) ($omnify_args['search'] ?? ''));
		$omnify_price_min = isset($omnify_args['price_min']) ? (float) $omnify_args['price_min'] : null;
		$omnify_price_max = isset($omnify_args['price_max']) ? (float) $omnify_args['price_max'] : null;
		$omnify_stock_status = sanitize_key((string) ($omnify_args['stock_status'] ?? ''));
		$omnify_date_from = sanitize_text_field((string) ($omnify_args['date_from'] ?? ''));
		$omnify_date_to = sanitize_text_field((string) ($omnify_args['date_to'] ?? ''));
		$omnify_sort = sanitize_key((string) ($omnify_args['sort'] ?? 'updated_desc'));
		$omnify_limit  = min(100, max(1, absint($omnify_args['per_page'] ?? 20)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		// Advanced attribute filters e.g. $args['attributes'] = ['brand' => ['nike'], 'color' => ['red','blue']]
		$omnify_attribute_filters = (array) ($omnify_args['attributes'] ?? []);

		$omnify_where = "WHERE 1=1";
		if ('trash' === $omnify_status) {
			$omnify_where .= " AND deleted_at IS NOT NULL";
		} elseif ('all' !== $omnify_status) {
			$omnify_where .= $wpdb->prepare(" AND status = %s AND deleted_at IS NULL", $omnify_status);
		} else {
			$omnify_where .= " AND deleted_at IS NULL";
		}

		if ($omnify_category) {
			$omnify_cat_table = $this->table('product_categories');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_where .= $wpdb->prepare(" AND id IN (SELECT product_id FROM {$omnify_cat_table} WHERE name = %s)", $omnify_category);
		}

		$omnify_brand = sanitize_text_field((string) ($omnify_args['brand'] ?? ''));
		if ($omnify_brand) {
			$omnify_brand_table = $this->table('product_brands');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_where .= $wpdb->prepare(" AND id IN (SELECT product_id FROM {$omnify_brand_table} WHERE name = %s)", $omnify_brand);
		}

		$omnify_tag = sanitize_text_field((string) ($omnify_args['tag'] ?? ''));
		if ($omnify_tag) {
			$omnify_tag_table = $this->table('product_tags');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_where .= $wpdb->prepare(" AND id IN (SELECT product_id FROM {$omnify_tag_table} WHERE name = %s)", $omnify_tag);
		}

		if ($omnify_type) {
			$omnify_where .= $wpdb->prepare(" AND type = %s", $omnify_type);
		}

		if ($omnify_search) {
			$omnify_like = '%' . $wpdb->esc_like($omnify_search) . '%';
			$omnify_where .= $wpdb->prepare(" AND (name LIKE %s OR description LIKE %s OR sku LIKE %s)", $omnify_like, $omnify_like, $omnify_like);
		}

		if ($omnify_price_min !== null) {
			$omnify_where .= $wpdb->prepare(" AND COALESCE(sale_price, price) >= %f", $omnify_price_min);
		}
		if ($omnify_price_max !== null) {
			$omnify_where .= $wpdb->prepare(" AND COALESCE(sale_price, price) <= %f", $omnify_price_max);
		}

		if ($omnify_stock_status === 'instock') {
			$omnify_where .= " AND stock_status = 'instock' AND (manage_stock = 0 OR stock_qty > 0)";
		} elseif ($omnify_stock_status === 'onsale') {
			$omnify_where .= " AND sale_price IS NOT NULL AND sale_price < price";
		}

		// Server-side attribute filters (faceted)
		if (!empty($omnify_attribute_filters)) {
			foreach ($omnify_attribute_filters as $omnify_attr_name => $omnify_values) {
				if (empty($omnify_values)) continue;
				$omnify_attr_name = sanitize_text_field($omnify_attr_name);
				$omnify_values = (array) $omnify_values;

				$omnify_attr_placeholders = implode(',', array_fill(0, count($omnify_values), '%s'));
				$omnify_attr_table = $this->table('product_attributes');

				$omnify_attr_query = $wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT product_id FROM {$omnify_attr_table} WHERE name = %s AND value IN ($omnify_attr_placeholders)",
					array_merge([$omnify_attr_name], $omnify_values)
				);

				$omnify_where .= " AND id IN ($omnify_attr_query)";
			}
		}

		if ($omnify_date_from) {
			$omnify_where .= $wpdb->prepare(" AND created_at >= %s", $omnify_date_from);
		}
		if ($omnify_date_to) {
			$omnify_where .= $wpdb->prepare(" AND created_at <= %s", $omnify_date_to);
		}

		$omnify_order_by = "ORDER BY updated_at DESC";
		switch ($omnify_sort) {
			case 'price_asc':
				$omnify_order_by = "ORDER BY COALESCE(sale_price, price) ASC";
				break;
			case 'price_desc':
				$omnify_order_by = "ORDER BY COALESCE(sale_price, price) DESC";
				break;
			case 'name_asc':
				$omnify_order_by = "ORDER BY name ASC";
				break;
			case 'name_desc':
				$omnify_order_by = "ORDER BY name DESC";
				break;
			case 'created_asc':
				$omnify_order_by = "ORDER BY created_at ASC";
				break;
			case 'created_desc':
				$omnify_order_by = "ORDER BY created_at DESC";
				break;
			case 'sales_desc':
				$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
				$omnify_order_items_table = $wpdb->prefix . 'omnify_order_items';
				$omnify_order_by = "ORDER BY (
					SELECT COALESCE(SUM(oi.quantity), 0)
					FROM {$omnify_order_items_table} oi
					JOIN {$omnify_orders_table} o ON o.id = oi.order_id
					WHERE oi.product_id = id AND o.status = 'completed'
				) DESC";
				break;
			case 'rating_desc':
				$omnify_reviews_table = $wpdb->prefix . 'omnify_reviews';
				$omnify_order_by = "ORDER BY (
					SELECT COALESCE(AVG(r.rating), 0)
					FROM {$omnify_reviews_table} r
					WHERE r.product_id = id AND r.status = 'approved' AND r.deleted_at IS NULL
				) DESC";
				break;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results(
			$wpdb,
			$wpdb->prepare(
				"SELECT * FROM {$this->table('products')} {$omnify_where} {$omnify_order_by} LIMIT %d OFFSET %d",
				$omnify_limit,
				$omnify_offset
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$omnify_products = array_map([$this, 'with_terms'], array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []));
		$omnify_result = apply_filters('omnify_get_products_results', $omnify_products, $omnify_args);
		wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		return $omnify_result;
	}

	public function find(int $omnify_id): ?array {
		$omnify_cache_version = $this->get_cache_version();
		$omnify_cache_key     = 'omnify_prod_find_' . $omnify_id . '_' . $omnify_cache_version;
		$omnify_cached        = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return '__null' === $omnify_cached ? null : $omnify_cached;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('products')} WHERE id = %d", $omnify_id), ARRAY_A);

		if (! is_array($omnify_row)) {
			wp_cache_set($omnify_cache_key, '__null', 'omnifywp-ecommerce', HOUR_IN_SECONDS);
			return null;
		}

		$omnify_product = $this->with_terms($this->hydrate($omnify_row));
		if ('variable' === $omnify_product['type']) {
			$omnify_product['variations'] = $this->get_variations($omnify_id);
		}

		$omnify_result = apply_filters('omnify_get_product', $omnify_product, $omnify_id);
		wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		return $omnify_result;
	}

	public function find_by_slug(string $omnify_slug): ?array {
		$omnify_cache_version = $this->get_cache_version();
		$omnify_cache_key     = 'omnify_prod_slug_' . sanitize_title($omnify_slug) . '_' . $omnify_cache_version;
		$omnify_cached        = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return '__null' === $omnify_cached ? null : $omnify_cached;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('products')} WHERE slug = %s", sanitize_title($omnify_slug)), ARRAY_A);

		if (! is_array($omnify_row)) {
			wp_cache_set($omnify_cache_key, '__null', 'omnifywp-ecommerce', HOUR_IN_SECONDS);
			return null;
		}

		$omnify_product = $this->with_terms($this->hydrate($omnify_row));
		if ('variable' === $omnify_product['type']) {
			$omnify_product['variations'] = $this->get_variations((int) $omnify_product['id']);
		}

		$omnify_result = apply_filters('omnify_get_product_by_slug', $omnify_product, $omnify_slug);
		wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		return $omnify_result;
	}

	public function find_by_sku(string $omnify_sku): ?array {
		$omnify_cache_version = $this->get_cache_version();
		$omnify_cache_key     = 'omnify_prod_sku_' . sanitize_title($omnify_sku) . '_' . $omnify_cache_version;
		$omnify_cached        = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return '__null' === $omnify_cached ? null : $omnify_cached;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$this->table('products')} WHERE sku = %s", sanitize_text_field($omnify_sku)), ARRAY_A);

		if (! is_array($omnify_row)) {
			wp_cache_set($omnify_cache_key, '__null', 'omnifywp-ecommerce', HOUR_IN_SECONDS);
			return null;
		}

		$omnify_product = $this->with_terms($this->hydrate($omnify_row));
		if ('variable' === $omnify_product['type']) {
			$omnify_product['variations'] = $this->get_variations((int) $omnify_product['id']);
		}

		$omnify_result = apply_filters('omnify_get_product_by_sku', $omnify_product, $omnify_sku);
		wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		return $omnify_result;
	}

	public function create(array $omnify_data): int {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_create_product_data', $omnify_data);

		$omnify_now = $this->now();
		$omnify_name = sanitize_text_field((string) ($omnify_data['name'] ?? ''));
		$omnify_slug = $this->unique_slug(sanitize_title((string) ($omnify_data['slug'] ?? $omnify_name)));

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('products'),
			[
				'status'       => $this->status((string) ($omnify_data['status'] ?? 'draft')),
				'type'         => sanitize_key($omnify_data['type'] ?? 'download'),
				'name'         => $omnify_name,
				'slug'         => $omnify_slug,
				'description'  => wp_kses_post((string) ($omnify_data['description'] ?? '')),
				'short_description' => sanitize_textarea_field((string) ($omnify_data['short_description'] ?? '')),
				'thumbnail_id' => absint($omnify_data['thumbnail_id'] ?? 0) ?: null,
				'price'        => (float) ($omnify_data['price'] ?? 0),
				'sale_price'   => isset($omnify_data['sale_price']) && '' !== $omnify_data['sale_price'] ? (float) $omnify_data['sale_price'] : null,
				'currency'     => strtoupper(substr(sanitize_text_field((string) ($omnify_data['currency'] ?? 'USD')), 0, 3)),
				'sku'          => sanitize_text_field($omnify_data['sku'] ?? ''),
				'manage_stock' => ! empty($omnify_data['manage_stock']) ? 1 : 0,
				'stock_qty'    => isset($omnify_data['stock_qty']) && '' !== $omnify_data['stock_qty'] ? (int) $omnify_data['stock_qty'] : null,
				'stock_status' => sanitize_key($omnify_data['stock_status'] ?? 'instock'),
				'allow_backorders' => ! empty($omnify_data['allow_backorders']) ? 1 : 0,
				'preorder_enabled' => ! empty($omnify_data['preorder_enabled']) ? 1 : 0,
				'preorder_release_date' => $this->date_or_null($omnify_data['preorder_release_date'] ?? null),
				'preorder_limit' => isset($omnify_data['preorder_limit']) && '' !== trim((string) $omnify_data['preorder_limit']) ? absint($omnify_data['preorder_limit']) : null,
				'preorder_message' => sanitize_text_field($omnify_data['preorder_message'] ?? ''),
				'weight'       => isset($omnify_data['weight']) && '' !== $omnify_data['weight'] ? (float) $omnify_data['weight'] : null,
				'length'       => isset($omnify_data['length']) && '' !== $omnify_data['length'] ? (float) $omnify_data['length'] : null,
				'width'        => isset($omnify_data['width']) && '' !== $omnify_data['width'] ? (float) $omnify_data['width'] : null,
				'height'       => isset($omnify_data['height']) && '' !== $omnify_data['height'] ? (float) $omnify_data['height'] : null,
				'shipping_class' => sanitize_title((string) ($omnify_data['shipping_class'] ?? '')),
				'attributes'   => is_array($omnify_data['attributes'] ?? null) ? wp_json_encode($omnify_data['attributes']) : null,
				'variation_settings' => is_array($omnify_data['variation_settings'] ?? null) ? wp_json_encode($omnify_data['variation_settings']) : null,
				'gallery_ids'  => ! empty($omnify_data['gallery_ids']) && is_array($omnify_data['gallery_ids']) ? wp_json_encode(array_map('absint', $omnify_data['gallery_ids'])) : null,
				'video_url'    => ! empty($omnify_data['video_url']) ? esc_url_raw(sanitize_text_field($omnify_data['video_url'])) : null,
				'video_poster_url' => ! empty($omnify_data['video_poster_url']) ? esc_url_raw(sanitize_text_field($omnify_data['video_poster_url'])) : null,
				'trust_badge_title' => sanitize_text_field($omnify_data['trust_badge_title'] ?? ''),
				'trust_badge_text'  => sanitize_textarea_field($omnify_data['trust_badge_text'] ?? ''),
				'download_expiry_days' => absint($omnify_data['download_expiry_days'] ?? 0),
				'max_purchase_qty' => isset($omnify_data['max_purchase_qty']) && '' !== trim((string)$omnify_data['max_purchase_qty']) ? absint($omnify_data['max_purchase_qty']) : null,
				'bundled_ids'  => ! empty($omnify_data['bundled_ids']) && is_array($omnify_data['bundled_ids']) ? wp_json_encode(array_map('absint', $omnify_data['bundled_ids'])) : null,
				'upsell_ids'   => ! empty($omnify_data['upsell_ids']) && is_array($omnify_data['upsell_ids']) ? wp_json_encode(array_map('absint', $omnify_data['upsell_ids'])) : null,
				'cross_sell_ids' => ! empty($omnify_data['cross_sell_ids']) && is_array($omnify_data['cross_sell_ids']) ? wp_json_encode(array_map('absint', $omnify_data['cross_sell_ids'])) : null,
				'refund_enabled' => ! empty($omnify_data['refund_enabled']) ? 1 : 0,
				'refund_window_days' => isset($omnify_data['refund_window_days']) && '' !== trim((string)$omnify_data['refund_window_days']) ? absint($omnify_data['refund_window_days']) : null,
				'refund_policy_text' => sanitize_textarea_field($omnify_data['refund_policy_text'] ?? ''),
				'specifications' => ! empty($omnify_data['specifications']) && is_array($omnify_data['specifications']) ? wp_json_encode($omnify_data['specifications']) : null,
				'delivery_info' => sanitize_textarea_field($omnify_data['delivery_info'] ?? ''),
				'return_info' => sanitize_textarea_field($omnify_data['return_info'] ?? ''),
				'created_at'   => $omnify_now,
				'updated_at'   => $omnify_now,
			]
		);

		$omnify_product_id = (int) $wpdb->insert_id;
		$this->replace_terms($omnify_product_id, 'product_categories', (array) ($omnify_data['categories'] ?? []));
		$this->replace_terms($omnify_product_id, 'product_tags', (array) ($omnify_data['tags'] ?? []));
		$this->replace_terms($omnify_product_id, 'product_brands', (array) ($omnify_data['brands'] ?? []));

		if ($omnify_product_id > 0 && ! empty($omnify_data['manage_stock']) && isset($omnify_data['stock_qty']) && '' !== $omnify_data['stock_qty']) {
			$omnify_qty = (int) $omnify_data['stock_qty'];
			$this->log_stock_change(
				$omnify_product_id,
				null,
				$omnify_qty,
				$omnify_qty,
				__('Product creation initial stock', 'omnifywp-ecommerce')
			);
		}

		if ($omnify_product_id > 0) {
			$this->clean_product_caches($omnify_product_id);
			do_action('omnify_product_created', $omnify_product_id, $omnify_data);
		}

		return $omnify_product_id;
	}

	public function update(int $omnify_id, array $omnify_data): bool {
		global $wpdb;

		$omnify_data = apply_filters('omnify_pre_update_product_data', $omnify_data, $omnify_id);

		$omnify_row = ['updated_at' => $this->now()];

		if (array_key_exists('status', $omnify_data)) {
			$omnify_row['status'] = $this->status((string) $omnify_data['status']);
		}
		if (array_key_exists('type', $omnify_data)) {
			$omnify_row['type'] = sanitize_key($omnify_data['type']);
		}
		if (array_key_exists('name', $omnify_data)) {
			$omnify_row['name'] = sanitize_text_field((string) $omnify_data['name']);
		}
		if (array_key_exists('slug', $omnify_data)) {
			$omnify_row['slug'] = $this->unique_slug(sanitize_title((string) $omnify_data['slug']), $omnify_id);
		}
		if (array_key_exists('description', $omnify_data)) {
			$omnify_row['description'] = wp_kses_post((string) $omnify_data['description']);
		}
		if (array_key_exists('short_description', $omnify_data)) {
			$omnify_row['short_description'] = sanitize_textarea_field((string) $omnify_data['short_description']);
		}
		if (array_key_exists('thumbnail_id', $omnify_data)) {
			$omnify_row['thumbnail_id'] = absint($omnify_data['thumbnail_id']) ?: null;
		}
		if (array_key_exists('price', $omnify_data)) {
			$omnify_row['price'] = (float) $omnify_data['price'];
		}
		if (array_key_exists('sale_price', $omnify_data)) {
			$omnify_row['sale_price'] = isset($omnify_data['sale_price']) && '' !== $omnify_data['sale_price'] ? (float) $omnify_data['sale_price'] : null;
		}
		if (array_key_exists('currency', $omnify_data)) {
			$omnify_row['currency'] = strtoupper(substr(sanitize_text_field((string) $omnify_data['currency']), 0, 3));
		}
		if (array_key_exists('sku', $omnify_data)) {
			$omnify_row['sku'] = sanitize_text_field($omnify_data['sku']);
		}
		if (array_key_exists('manage_stock', $omnify_data)) {
			$omnify_row['manage_stock'] = ! empty($omnify_data['manage_stock']) ? 1 : 0;
		}
		if (array_key_exists('stock_qty', $omnify_data)) {
			$omnify_row['stock_qty'] = isset($omnify_data['stock_qty']) && '' !== $omnify_data['stock_qty'] ? (int) $omnify_data['stock_qty'] : null;
		}
		if (array_key_exists('stock_status', $omnify_data)) {
			$omnify_row['stock_status'] = sanitize_key($omnify_data['stock_status']);
		}
		if (array_key_exists('allow_backorders', $omnify_data)) {
			$omnify_row['allow_backorders'] = ! empty($omnify_data['allow_backorders']) ? 1 : 0;
		}
		if (array_key_exists('preorder_enabled', $omnify_data)) {
			$omnify_row['preorder_enabled'] = ! empty($omnify_data['preorder_enabled']) ? 1 : 0;
		}
		if (array_key_exists('preorder_release_date', $omnify_data)) {
			$omnify_row['preorder_release_date'] = $this->date_or_null($omnify_data['preorder_release_date']);
		}
		if (array_key_exists('preorder_limit', $omnify_data)) {
			$omnify_row['preorder_limit'] = isset($omnify_data['preorder_limit']) && '' !== trim((string) $omnify_data['preorder_limit']) ? absint($omnify_data['preorder_limit']) : null;
		}
		if (array_key_exists('preorder_message', $omnify_data)) {
			$omnify_row['preorder_message'] = sanitize_text_field((string) $omnify_data['preorder_message']);
		}
		if (array_key_exists('weight', $omnify_data)) {
			$omnify_row['weight'] = isset($omnify_data['weight']) && '' !== $omnify_data['weight'] ? (float) $omnify_data['weight'] : null;
		}
		if (array_key_exists('length', $omnify_data)) {
			$omnify_row['length'] = isset($omnify_data['length']) && '' !== $omnify_data['length'] ? (float) $omnify_data['length'] : null;
		}
		if (array_key_exists('width', $omnify_data)) {
			$omnify_row['width'] = isset($omnify_data['width']) && '' !== $omnify_data['width'] ? (float) $omnify_data['width'] : null;
		}
		if (array_key_exists('height', $omnify_data)) {
			$omnify_row['height'] = isset($omnify_data['height']) && '' !== $omnify_data['height'] ? (float) $omnify_data['height'] : null;
		}
		if (array_key_exists('shipping_class', $omnify_data)) {
			$omnify_row['shipping_class'] = sanitize_title((string) $omnify_data['shipping_class']);
		}
		if (array_key_exists('attributes', $omnify_data)) {
			$omnify_row['attributes'] = is_array($omnify_data['attributes']) ? wp_json_encode($omnify_data['attributes']) : null;
		}
		if (array_key_exists('variation_settings', $omnify_data)) {
			$omnify_row['variation_settings'] = is_array($omnify_data['variation_settings']) ? wp_json_encode($omnify_data['variation_settings']) : null;
		}
		if (array_key_exists('gallery_ids', $omnify_data)) {
			$omnify_row['gallery_ids'] = ! empty($omnify_data['gallery_ids']) && is_array($omnify_data['gallery_ids'])
				? wp_json_encode(array_map('absint', $omnify_data['gallery_ids']))
				: null;
		}
		if (array_key_exists('video_url', $omnify_data)) {
			$omnify_row['video_url'] = ! empty($omnify_data['video_url'])
				? esc_url_raw(sanitize_text_field($omnify_data['video_url']))
				: null;
		}
		if (array_key_exists('video_poster_url', $omnify_data)) {
			$omnify_row['video_poster_url'] = ! empty($omnify_data['video_poster_url'])
				? esc_url_raw(sanitize_text_field($omnify_data['video_poster_url']))
				: null;
		}
		if (array_key_exists('trust_badge_title', $omnify_data)) {
			$omnify_row['trust_badge_title'] = sanitize_text_field((string) $omnify_data['trust_badge_title']);
		}
		if (array_key_exists('trust_badge_text', $omnify_data)) {
			$omnify_row['trust_badge_text'] = sanitize_textarea_field((string) $omnify_data['trust_badge_text']);
		}
		if (array_key_exists('download_expiry_days', $omnify_data)) {
			$omnify_row['download_expiry_days'] = absint($omnify_data['download_expiry_days']);
		}
		if (array_key_exists('max_purchase_qty', $omnify_data)) {
			$omnify_row['max_purchase_qty'] = isset($omnify_data['max_purchase_qty']) && '' !== trim((string)$omnify_data['max_purchase_qty']) ? absint($omnify_data['max_purchase_qty']) : null;
		}
		if (array_key_exists('bundled_ids', $omnify_data)) {
			$omnify_row['bundled_ids'] = ! empty($omnify_data['bundled_ids']) && is_array($omnify_data['bundled_ids'])
				? wp_json_encode(array_map('absint', $omnify_data['bundled_ids']))
				: null;
		}
		if (array_key_exists('upsell_ids', $omnify_data)) {
			$omnify_row['upsell_ids'] = ! empty($omnify_data['upsell_ids']) && is_array($omnify_data['upsell_ids'])
				? wp_json_encode(array_map('absint', $omnify_data['upsell_ids']))
				: null;
		}
		if (array_key_exists('cross_sell_ids', $omnify_data)) {
			$omnify_row['cross_sell_ids'] = ! empty($omnify_data['cross_sell_ids']) && is_array($omnify_data['cross_sell_ids'])
				? wp_json_encode(array_map('absint', $omnify_data['cross_sell_ids']))
				: null;
		}
		if (array_key_exists('refund_enabled', $omnify_data)) {
			$omnify_row['refund_enabled'] = ! empty($omnify_data['refund_enabled']) ? 1 : 0;
		}
		if (array_key_exists('refund_window_days', $omnify_data)) {
			$omnify_row['refund_window_days'] = isset($omnify_data['refund_window_days']) && '' !== trim((string)$omnify_data['refund_window_days']) ? absint($omnify_data['refund_window_days']) : null;
		}
		if (array_key_exists('refund_policy_text', $omnify_data)) {
			$omnify_row['refund_policy_text'] = sanitize_textarea_field((string) $omnify_data['refund_policy_text']);
		}
		if (array_key_exists('specifications', $omnify_data)) {
			$omnify_row['specifications'] = ! empty($omnify_data['specifications']) && is_array($omnify_data['specifications']) ? wp_json_encode($omnify_data['specifications']) : null;
		}
		if (array_key_exists('delivery_info', $omnify_data)) {
			$omnify_row['delivery_info'] = sanitize_textarea_field((string) $omnify_data['delivery_info']);
		}
		if (array_key_exists('return_info', $omnify_data)) {
			$omnify_row['return_info'] = sanitize_textarea_field((string) $omnify_data['return_info']);
		}

		$omnify_old_stock = null;
		if (array_key_exists('stock_qty', $omnify_data) || array_key_exists('manage_stock', $omnify_data)) {
			$omnify_prod = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT manage_stock, stock_qty FROM {$this->table('products')} WHERE id = %d",
				$omnify_id
			), ARRAY_A);
			if ($omnify_prod) {
				$omnify_old_stock = null !== $omnify_prod['stock_qty'] ? (int) $omnify_prod['stock_qty'] : null;
			}
		}

		$omnify_updated = false !== \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('products'), $omnify_row, ['id' => $omnify_id]);

		if ($omnify_updated && array_key_exists('stock_qty', $omnify_data)) {
			$omnify_new_stock = isset($omnify_data['stock_qty']) && '' !== $omnify_data['stock_qty'] ? (int) $omnify_data['stock_qty'] : null;
			if (null !== $omnify_new_stock) {
				$omnify_old_stock_val = null !== $omnify_old_stock ? $omnify_old_stock : 0;
				if ($omnify_new_stock !== $omnify_old_stock) {
					$this->log_stock_change(
						$omnify_id,
						null,
						$omnify_new_stock - $omnify_old_stock_val,
						$omnify_new_stock,
						__('Admin manual update', 'omnifywp-ecommerce')
					);
				}
			}
		}

		if (array_key_exists('categories', $omnify_data)) {
			$this->replace_terms($omnify_id, 'product_categories', (array) $omnify_data['categories']);
		}
		if (array_key_exists('tags', $omnify_data)) {
			$this->replace_terms($omnify_id, 'product_tags', (array) $omnify_data['tags']);
		}
		if (array_key_exists('brands', $omnify_data)) {
			$this->replace_terms($omnify_id, 'product_brands', (array) $omnify_data['brands']);
		}

		if ($omnify_updated) {
			$this->clean_product_caches($omnify_id);
			do_action('omnify_product_updated', $omnify_id, $omnify_data);
		}

		return $omnify_updated;
	}

	public function trash(int $omnify_id): bool {
		global $wpdb;

		$omnify_product = $this->find($omnify_id);
		$omnify_product = apply_filters('omnify_pre_trash_product', $omnify_product, $omnify_id);

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('products'),
			['deleted_at' => current_time('mysql', 1)],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			$this->clean_product_caches($omnify_id);
			do_action('omnify_product_trashed', $omnify_id, $omnify_product);
			do_action('omnify_after_trash_product', $omnify_id, $omnify_product);
		}
		return (bool) $omnify_updated;
	}

	public function restore(int $omnify_id): bool {
		global $wpdb;

		$omnify_product = $this->find($omnify_id);
		$omnify_product = apply_filters('omnify_pre_restore_product', $omnify_product, $omnify_id);

		$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$this->table('products'),
			['deleted_at' => null],
			['id' => $omnify_id]
		);
		if ($omnify_updated) {
			$this->clean_product_caches($omnify_id);
			do_action('omnify_product_restored', $omnify_id, $omnify_product);
			do_action('omnify_after_restore_product', $omnify_id, $omnify_product);
		}
		return (bool) $omnify_updated;
	}

	public function delete(int $omnify_id): bool {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('product_categories'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('product_tags'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('product_brands'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('product_files'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('product_variations'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('wishlists'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('reviews'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('customer_access'), ['product_id' => $omnify_id]);
		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('metadata'), ['object_type' => 'product', 'object_id' => $omnify_id]);

		$omnify_deleted = false !== \Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table('products'), ['id' => $omnify_id]);
		if ($omnify_deleted) {
			$this->clean_product_caches($omnify_id);
			do_action('omnify_product_deleted', $omnify_id);
		}

		return $omnify_deleted;
	}

	private function replace_terms(int $omnify_product_id, string $omnify_table, array $omnify_terms): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::delete($wpdb, $this->table($omnify_table), ['product_id' => $omnify_product_id]);

		foreach ($this->normalize_terms($omnify_terms) as $omnify_term) {
			\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
				$this->table($omnify_table),
				[
					'product_id' => $omnify_product_id,
					'name'       => $omnify_term,
					'slug'       => sanitize_title($omnify_term),
					'created_at' => $this->now(),
				]
			);
		}
	}

	/**
	 * @return array<int, string>
	 */
	private function terms(int $omnify_product_id, string $omnify_table): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_terms = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare("SELECT name FROM {$this->table($omnify_table)} WHERE product_id = %d ORDER BY name ASC", $omnify_product_id));

		return array_values(array_map('strval', is_array($omnify_terms) ? $omnify_terms : []));
	}

	/**
	 * @return array<int, string>
	 */
	private function normalize_terms(array $omnify_terms): array {
		$omnify_normalized = [];

		foreach ($omnify_terms as $omnify_term) {
			$omnify_term = sanitize_text_field((string) $omnify_term);
			if ('' !== $omnify_term) {
				$omnify_normalized[] = $omnify_term;
			}
		}

		return array_values(array_unique($omnify_normalized));
	}

	private function with_terms(array $omnify_product): array {
		$omnify_product['categories'] = $this->terms((int) $omnify_product['id'], 'product_categories');
		$omnify_product['tags']       = $this->terms((int) $omnify_product['id'], 'product_tags');
		$omnify_product['brands']     = $this->terms((int) $omnify_product['id'], 'product_brands');

		return $omnify_product;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']            = (int) $omnify_row['id'];
		$omnify_row['thumbnail_id']  = null === $omnify_row['thumbnail_id'] ? null : (int) $omnify_row['thumbnail_id'];
		$omnify_row['thumbnail_url'] = $omnify_row['thumbnail_id'] ? wp_get_attachment_image_url((int) $omnify_row['thumbnail_id'], 'full') : '';
		$omnify_row['price']         = (float) $omnify_row['price'];
		$omnify_row['sale_price']    = null === $omnify_row['sale_price'] ? null : (float) $omnify_row['sale_price'];
		$omnify_row['sku']           = $omnify_row['sku'] ?? '';
		$omnify_row['manage_stock']  = (int) ($omnify_row['manage_stock'] ?? 0);
		$omnify_row['stock_qty']     = null === $omnify_row['stock_qty'] ? null : (int) $omnify_row['stock_qty'];
		$omnify_row['stock_status']  = $omnify_row['stock_status'] ?? 'instock';
		$omnify_row['allow_backorders'] = (int) ($omnify_row['allow_backorders'] ?? 0);
		$omnify_row['preorder_enabled'] = (int) ($omnify_row['preorder_enabled'] ?? 0);
		$omnify_row['preorder_release_date'] = $omnify_row['preorder_release_date'] ?? null;
		$omnify_row['preorder_limit'] = null === ($omnify_row['preorder_limit'] ?? null) ? null : (int) $omnify_row['preorder_limit'];
		$omnify_row['preorder_message'] = $omnify_row['preorder_message'] ?? '';
		$omnify_row['weight']        = null === $omnify_row['weight'] ? null : (float) $omnify_row['weight'];
		$omnify_row['length']        = null === $omnify_row['length'] ? null : (float) $omnify_row['length'];
		$omnify_row['width']         = null === $omnify_row['width'] ? null : (float) $omnify_row['width'];
		$omnify_row['height']        = null === $omnify_row['height'] ? null : (float) $omnify_row['height'];
		$omnify_row['shipping_class'] = $omnify_row['shipping_class'] ?? '';
		$omnify_row['attributes']    = empty($omnify_row['attributes']) ? [] : json_decode($omnify_row['attributes'], true);
		$omnify_variation_settings   = empty($omnify_row['variation_settings'] ?? '') ? [] : json_decode((string) $omnify_row['variation_settings'], true);
		$omnify_row['variation_settings'] = wp_parse_args(
			is_array($omnify_variation_settings) ? $omnify_variation_settings : [],
			[
				'product_kind'   => 'digital',
				'selector_style' => 'buttons',
				'swatch_shape'   => 'round',
				'swatch_size'    => 'medium',
				'out_of_stock'   => 'cross',
			]
		);
		if ('physical' === ($omnify_row['type'] ?? 'download')) {
			$omnify_row['variation_settings']['product_kind'] = 'physical';
		}
		if ('variable' === ($omnify_row['type'] ?? 'download') && ! empty($omnify_row['variation_settings']['product_kind']) && ! in_array($omnify_row['variation_settings']['product_kind'], ['digital', 'physical'], true)) {
			$omnify_row['variation_settings']['product_kind'] = 'digital';
		}
		if ('bundle' === ($omnify_row['type'] ?? 'download')) {
			$omnify_row['product_kind'] = 'bundle';
		} else {
			$omnify_row['product_kind'] = ('physical' === ($omnify_row['type'] ?? 'download') || ('variable' === ($omnify_row['type'] ?? 'download') && 'physical' === ($omnify_row['variation_settings']['product_kind'] ?? 'digital')))
				? 'physical'
				: 'digital';
		}
		$omnify_row['product_structure'] = 'variable' === ($omnify_row['type'] ?? 'download') ? 'variable' : 'simple';

		// Gallery
		$omnify_raw_ids = empty($omnify_row['gallery_ids']) ? [] : json_decode($omnify_row['gallery_ids'], true);
		$omnify_row['gallery_ids']   = is_array($omnify_raw_ids) ? array_map('intval', $omnify_raw_ids) : [];
		$omnify_row['gallery_urls']  = array_values(array_filter(array_map(function($omnify_id) {
			return wp_get_attachment_image_url($omnify_id, 'full') ?: '';
		}, $omnify_row['gallery_ids'])));

		$omnify_row['video_url']     = $omnify_row['video_url'] ?? null;
		$omnify_row['video_poster_url'] = $omnify_row['video_poster_url'] ?? null;
		$omnify_row['trust_badge_title'] = $omnify_row['trust_badge_title'] ?? '';
		$omnify_row['trust_badge_text']  = $omnify_row['trust_badge_text'] ?? '';
		$omnify_row['download_expiry_days'] = absint($omnify_row['download_expiry_days'] ?? 0);
		$omnify_row['max_purchase_qty'] = null === ($omnify_row['max_purchase_qty'] ?? null) ? null : (int) $omnify_row['max_purchase_qty'];
		$omnify_raw_bundled_ids = empty($omnify_row['bundled_ids']) ? [] : json_decode($omnify_row['bundled_ids'], true);
		$omnify_row['bundled_ids'] = is_array($omnify_raw_bundled_ids) ? array_map('intval', $omnify_raw_bundled_ids) : [];
		$omnify_raw_upsell_ids = empty($omnify_row['upsell_ids']) ? [] : json_decode($omnify_row['upsell_ids'], true);
		$omnify_row['upsell_ids'] = is_array($omnify_raw_upsell_ids) ? array_map('intval', $omnify_raw_upsell_ids) : [];
		$omnify_raw_cross_sell_ids = empty($omnify_row['cross_sell_ids']) ? [] : json_decode($omnify_row['cross_sell_ids'], true);
		$omnify_row['cross_sell_ids'] = is_array($omnify_raw_cross_sell_ids) ? array_map('intval', $omnify_raw_cross_sell_ids) : [];
		$omnify_row['refund_enabled'] = (int) ($omnify_row['refund_enabled'] ?? 1);
		$omnify_row['refund_window_days'] = null === ($omnify_row['refund_window_days'] ?? null) ? null : (int) $omnify_row['refund_window_days'];
		$omnify_row['refund_policy_text'] = $omnify_row['refund_policy_text'] ?? '';
		$omnify_row['specifications'] = empty($omnify_row['specifications']) ? [] : json_decode($omnify_row['specifications'], true);
		if (! is_array($omnify_row['specifications'])) $omnify_row['specifications'] = [];
		$omnify_row['delivery_info'] = $omnify_row['delivery_info'] ?? '';
		$omnify_row['return_info'] = $omnify_row['return_info'] ?? '';
		$omnify_row['short_description'] = $omnify_row['short_description'] ?? '';

		return $omnify_row;
	}

	private function status(string $omnify_status): string {
		$omnify_status = sanitize_key($omnify_status);

		return in_array($omnify_status, self::STATUSES, true) ? $omnify_status : 'draft';
	}

	private function unique_slug(string $omnify_slug, int $omnify_ignore_id = 0): string {
		global $wpdb;

		$omnify_slug = '' === $omnify_slug ? 'product' : $omnify_slug;
		$omnify_base = $omnify_slug;
		$omnify_i = 2;

		while ($this->slug_exists($omnify_slug, $omnify_ignore_id)) {
			$omnify_slug = $omnify_base . '-' . $omnify_i;
			++$omnify_i;
		}

		return $omnify_slug;
	}

	private function slug_exists(string $omnify_slug, int $omnify_ignore_id): bool {
		global $wpdb;

		$omnify_id = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT id FROM {$this->table('products')} WHERE slug = %s AND id != %d LIMIT 1", $omnify_slug, $omnify_ignore_id)
		);

		return $omnify_id > 0;
	}

	private function table(string $omnify_name): string {
		return $this->omnify_schema->table($omnify_name);
	}

	private function now(): string {
		return current_time('mysql', true);
	}

	private function date_or_null(mixed $omnify_value): ?string {
		$omnify_value = trim((string) $omnify_value);
		if ('' === $omnify_value) {
			return null;
		}

		$omnify_time = strtotime($omnify_value);
		return $omnify_time ? gmdate('Y-m-d', $omnify_time) : null;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_variations(int $omnify_product_id): array {
		global $wpdb;

		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare("SELECT * FROM {$this->table('product_variations')} WHERE product_id = %d ORDER BY id ASC", $omnify_product_id),
			ARRAY_A
		);

		if (! is_array($omnify_rows)) {
			return [];
		}

		return array_map(function($omnify_row) {
			$omnify_row['id']           = (int) $omnify_row['id'];
			$omnify_row['product_id']   = (int) $omnify_row['product_id'];
			$omnify_row['price']        = (float) $omnify_row['price'];
			$omnify_row['sale_price']   = null === $omnify_row['sale_price'] ? null : (float) $omnify_row['sale_price'];
			$omnify_row['attributes']   = empty($omnify_row['attributes']) ? [] : json_decode($omnify_row['attributes'], true);
			$omnify_row['description']  = $omnify_row['description'] ?? '';
			$omnify_row['manage_stock'] = (int) $omnify_row['manage_stock'];
			$omnify_row['stock_qty']    = null === $omnify_row['stock_qty'] ? null : (int) $omnify_row['stock_qty'];
			$omnify_row['stock_status'] = $omnify_row['stock_status'] ?? 'instock';
			$omnify_row['allow_backorders'] = (int) ($omnify_row['allow_backorders'] ?? 0);
			$omnify_row['preorder_enabled'] = (int) ($omnify_row['preorder_enabled'] ?? 0);
			$omnify_row['preorder_release_date'] = $omnify_row['preorder_release_date'] ?? null;
			$omnify_row['preorder_limit'] = null === ($omnify_row['preorder_limit'] ?? null) ? null : (int) $omnify_row['preorder_limit'];
			$omnify_row['preorder_message'] = $omnify_row['preorder_message'] ?? '';
			$omnify_row['thumbnail_id'] = null === $omnify_row['thumbnail_id'] ? null : (int) $omnify_row['thumbnail_id'];
			$omnify_row['thumbnail_url'] = $omnify_row['thumbnail_id'] ? wp_get_attachment_image_url((int) $omnify_row['thumbnail_id'], 'full') : '';
			return $omnify_row;
		}, $omnify_rows);
	}

	public function save_variations(int $omnify_product_id, array $omnify_variations): void {
		global $wpdb;

		// Fetch existing variations to see what needs to be deleted
		$omnify_existing_ids = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT id FROM {$this->table('product_variations')} WHERE product_id = %d",
			$omnify_product_id
		));
		$omnify_existing_ids = array_map('intval', $omnify_existing_ids ?: []);

		$omnify_kept_ids = [];
		$omnify_now = $this->now();

		foreach ($omnify_variations as $omnify_var) {
			$omnify_var_id = absint($omnify_var['id'] ?? 0);
			$omnify_attrs  = is_array($omnify_var['attributes'] ?? null) ? $omnify_var['attributes'] : [];

			$omnify_data = [
				'sku'          => sanitize_text_field($omnify_var['sku'] ?? ''),
				'price'        => (float) ($omnify_var['price'] ?? 0.0),
				'sale_price'   => isset($omnify_var['sale_price']) && '' !== $omnify_var['sale_price'] && null !== $omnify_var['sale_price'] ? (float) $omnify_var['sale_price'] : null,
				'attributes'   => wp_json_encode($omnify_attrs),
				'description'  => sanitize_textarea_field($omnify_var['description'] ?? ''),
				'manage_stock' => ! empty($omnify_var['manage_stock']) ? 1 : 0,
				'stock_qty'    => isset($omnify_var['stock_qty']) && '' !== $omnify_var['stock_qty'] && null !== $omnify_var['stock_qty'] ? (int) $omnify_var['stock_qty'] : null,
				'stock_status' => sanitize_key($omnify_var['stock_status'] ?? 'instock'),
				'allow_backorders' => ! empty($omnify_var['allow_backorders']) ? 1 : 0,
				'preorder_enabled' => ! empty($omnify_var['preorder_enabled']) ? 1 : 0,
				'preorder_release_date' => $this->date_or_null($omnify_var['preorder_release_date'] ?? null),
				'preorder_limit' => isset($omnify_var['preorder_limit']) && '' !== trim((string) $omnify_var['preorder_limit']) ? absint($omnify_var['preorder_limit']) : null,
				'preorder_message' => sanitize_text_field($omnify_var['preorder_message'] ?? ''),
				'thumbnail_id' => absint($omnify_var['thumbnail_id'] ?? 0) ?: null,
				'updated_at'   => $omnify_now,
			];

			if ($omnify_var_id && in_array($omnify_var_id, $omnify_existing_ids, true)) {
				$omnify_old_var = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT stock_qty FROM {$this->table('product_variations')} WHERE id = %d",
					$omnify_var_id
				), ARRAY_A);
				$omnify_old_stock = $omnify_old_var && null !== $omnify_old_var['stock_qty'] ? (int) $omnify_old_var['stock_qty'] : null;

				\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, $this->table('product_variations'), $omnify_data, ['id' => $omnify_var_id]);
				$omnify_kept_ids[] = $omnify_var_id;

				if (isset($omnify_var['stock_qty']) && '' !== $omnify_var['stock_qty'] && null !== $omnify_var['stock_qty']) {
					$omnify_new_stock = (int) $omnify_var['stock_qty'];
					$omnify_old_stock_val = null !== $omnify_old_stock ? $omnify_old_stock : 0;
					if ($omnify_new_stock !== $omnify_old_stock) {
						$this->log_stock_change(
							$omnify_product_id,
							$omnify_var_id,
							$omnify_new_stock - $omnify_old_stock_val,
							$omnify_new_stock,
							__('Admin manual update (Variation)', 'omnifywp-ecommerce')
						);
					}
				}
			} else {
				$omnify_data['product_id'] = $omnify_product_id;
				$omnify_data['created_at'] = $omnify_now;
				\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, $this->table('product_variations'), $omnify_data);
				$omnify_new_var_id = (int) $wpdb->insert_id;
				$omnify_kept_ids[] = $omnify_new_var_id;

				if (! empty($omnify_var['manage_stock']) && isset($omnify_var['stock_qty']) && '' !== $omnify_var['stock_qty'] && null !== $omnify_var['stock_qty']) {
					$omnify_qty = (int) $omnify_var['stock_qty'];
					$this->log_stock_change(
						$omnify_product_id,
						$omnify_new_var_id,
						$omnify_qty,
						$omnify_qty,
						__('Variation creation initial stock', 'omnifywp-ecommerce')
					);
				}
			}
		}

		// Delete any variation that was removed in the editor
		$omnify_to_delete = array_diff($omnify_existing_ids, $omnify_kept_ids);
		if (! empty($omnify_to_delete)) {
			$omnify_ids_placeholders = implode(',', array_fill(0, count($omnify_to_delete), '%d'));
			\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"DELETE FROM {$this->table('product_variations')} WHERE id IN ($omnify_ids_placeholders)",
				...$omnify_to_delete
			));
			foreach ($omnify_to_delete as $omnify_del_var_id) {
				$this->clean_variation_caches((int) $omnify_del_var_id);
			}
		}

		foreach ($omnify_kept_ids as $omnify_k_var_id) {
			$this->clean_variation_caches((int) $omnify_k_var_id);
		}
		$this->clean_product_caches($omnify_product_id);
	}

	/**
	 * Increment product or variation stock by a specified quantity atomically.
	 */
	public function increment_stock(int $omnify_product_id, ?int $omnify_variation_id, int $omnify_qty): void {
		global $wpdb;

		$omnify_qty = max(1, absint($omnify_qty));

		if ($omnify_variation_id > 0) {
			$omnify_table = $this->table('product_variations');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
				"UPDATE {$omnify_table} 
				SET stock_qty = stock_qty + %d,
					stock_status = CASE 
						WHEN (stock_qty + %d) > 0 THEN 'instock'
						WHEN (allow_backorders = 1 OR preorder_enabled = 1) THEN 'onbackorder'
						ELSE 'outofstock'
					END,
					updated_at = %s
				WHERE id = %d AND manage_stock = 1",
				$omnify_qty,
				$omnify_qty,
				$this->now(),
				$omnify_variation_id
			));
			if (false !== $omnify_updated) {
				$this->clean_variation_caches($omnify_variation_id);
			}
		} else {
			$omnify_table = $this->table('products');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
				"UPDATE {$omnify_table} 
				SET stock_qty = stock_qty + %d,
					stock_status = CASE 
						WHEN (stock_qty + %d) > 0 THEN 'instock'
						WHEN (allow_backorders = 1 OR preorder_enabled = 1) THEN 'onbackorder'
						ELSE 'outofstock'
					END,
					updated_at = %s
				WHERE id = %d AND manage_stock = 1",
				$omnify_qty,
				$omnify_qty,
				$this->now(),
				$omnify_product_id
			));
		}
		$this->clean_product_caches($omnify_product_id);
	}

	/**
	 * Decrement product or variation stock by a specified quantity atomically and invalidate caches.
	 */
	public function reduce_stock(int $omnify_product_id, ?int $omnify_variation_id, int $omnify_qty): bool {
		global $wpdb;

		$omnify_qty = max(1, absint($omnify_qty));

		if ($omnify_variation_id > 0) {
			$omnify_table = $this->table('product_variations');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
				"UPDATE {$omnify_table} 
				SET stock_qty = CASE 
					WHEN (allow_backorders = 1 OR preorder_enabled = 1) THEN (stock_qty - %d)
					ELSE GREATEST(0, stock_qty - %d)
				END,
				stock_status = CASE 
					WHEN (stock_qty - %d) <= 0 AND (allow_backorders = 1 OR preorder_enabled = 1) THEN 'onbackorder'
					WHEN (stock_qty - %d) <= 0 THEN 'outofstock'
					ELSE 'instock'
				END,
				updated_at = %s
				WHERE id = %d AND manage_stock = 1",
				$omnify_qty,
				$omnify_qty,
				$omnify_qty,
				$omnify_qty,
				$this->now(),
				$omnify_variation_id
			));

			if (false !== $omnify_updated) {
				$this->clean_variation_caches($omnify_variation_id);
				$this->clean_product_caches($omnify_product_id);
				return true;
			}
		} else {
			$omnify_table = $this->table('products');
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$omnify_updated = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare(
				"UPDATE {$omnify_table} 
				SET stock_qty = CASE 
					WHEN (allow_backorders = 1 OR preorder_enabled = 1) THEN (stock_qty - %d)
					ELSE GREATEST(0, stock_qty - %d)
				END,
				stock_status = CASE 
					WHEN (stock_qty - %d) <= 0 AND (allow_backorders = 1 OR preorder_enabled = 1) THEN 'onbackorder'
					WHEN (stock_qty - %d) <= 0 THEN 'outofstock'
					ELSE 'instock'
				END,
				updated_at = %s
				WHERE id = %d AND manage_stock = 1",
				$omnify_qty,
				$omnify_qty,
				$omnify_qty,
				$omnify_qty,
				$this->now(),
				$omnify_product_id
			));

			if (false !== $omnify_updated) {
				$this->clean_product_caches($omnify_product_id);
				return true;
			}
		}

		return false;
	}

	/**
	 * Record a change in stock log.
	 */
	public function log_stock_change(int $omnify_product_id, ?int $omnify_variation_id, int $omnify_change_qty, int $omnify_new_qty, string $omnify_reason, ?int $omnify_user_id = null): void {
		global $wpdb;
		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->table('inventory_logs'),
			[
				'product_id'   => $omnify_product_id,
				'variation_id' => $omnify_variation_id ?: null,
				'change_qty'   => $omnify_change_qty,
				'new_qty'      => $omnify_new_qty,
				'reason'       => sanitize_text_field($omnify_reason),
				'user_id'      => $omnify_user_id ?: get_current_user_id() ?: null,
				'created_at'   => $this->now(),
			]
		);
	}

	/**
	 * Get related products based on categories and tags.
	 *
	 * @param int $product_id Product ID to find relations for.
	 * @param int $limit Maximum number of related products to return.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_related_products(int $omnify_product_id, int $omnify_limit = 4): array {
		$omnify_cache_version = $this->get_cache_version();
		$omnify_cache_key     = 'omnify_prod_related_' . $omnify_product_id . '_' . $omnify_limit . '_' . $omnify_cache_version;
		$omnify_cached        = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false !== $omnify_cached) {
			return $omnify_cached;
		}

		global $wpdb;

		// 1. Get categories and tags of this product
		$omnify_categories = $this->terms($omnify_product_id, 'product_categories');
		$omnify_tags       = $this->terms($omnify_product_id, 'product_tags');

		if (empty($omnify_categories) && empty($omnify_tags)) {
			// Fallback: get recent published products excluding current
			$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM {$this->table('products')} WHERE id != %d AND status = 'published' ORDER BY created_at DESC LIMIT %d",
					$omnify_product_id,
					$omnify_limit
				),
				ARRAY_A
			);
			$omnify_result = array_map([$this, 'with_terms'], array_map([$this, 'hydrate'], is_array($omnify_rows) ? $omnify_rows : []));
			wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
			return $omnify_result;
		}

		$omnify_clauses = [];
		$omnify_params  = [];

		if (! empty($omnify_categories)) {
			$omnify_placeholders = implode(',', array_fill(0, count($omnify_categories), '%s'));
			$omnify_clauses[] = "id IN (SELECT product_id FROM {$this->table('product_categories')} WHERE name IN ($omnify_placeholders))";
			$omnify_params = array_merge($omnify_params, $omnify_categories);
		}

		if (! empty($omnify_tags)) {
			$omnify_placeholders = implode(',', array_fill(0, count($omnify_tags), '%s'));
			$omnify_clauses[] = "id IN (SELECT product_id FROM {$this->table('product_tags')} WHERE name IN ($omnify_placeholders))";
			$omnify_params = array_merge($omnify_params, $omnify_tags);
		}

		$omnify_where_clause = implode(' OR ', $omnify_clauses);
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$omnify_query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$this->table('products')} WHERE id != %d AND status = 'published' AND ($omnify_where_clause) ORDER BY RAND() LIMIT %d",
			array_merge([$omnify_product_id], $omnify_params, [$omnify_limit])
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $omnify_query, ARRAY_A);

		// If we don't have enough related products, backfill with recent products
		if (! is_array($omnify_rows)) {
			$omnify_rows = [];
		}

		if (count($omnify_rows) < $omnify_limit) {
			$omnify_exclude_ids = array_merge([$omnify_product_id], array_column($omnify_rows, 'id'));
			$omnify_needed = $omnify_limit - count($omnify_rows);
			$omnify_placeholders = implode(',', array_fill(0, count($omnify_exclude_ids), '%d'));
			$omnify_backfill_query = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table('products')} WHERE id NOT IN ($omnify_placeholders) AND status = 'published' ORDER BY created_at DESC LIMIT %d",
				array_merge($omnify_exclude_ids, [$omnify_needed])
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$omnify_backfill_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $omnify_backfill_query, ARRAY_A);
			if (is_array($omnify_backfill_rows)) {
				$omnify_rows = array_merge($omnify_rows, $omnify_backfill_rows);
			}
		}

		$omnify_result = array_map([$this, 'with_terms'], array_map([$this, 'hydrate'], $omnify_rows));
		wp_cache_set($omnify_cache_key, $omnify_result, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		return $omnify_result;
	}

	/**
	 * Get manually selected upsell products for a product.
	 */
	public function get_upsells(array $omnify_product): array {
		global $wpdb;
		$omnify_ids = ! empty($omnify_product['upsell_ids']) && is_array($omnify_product['upsell_ids']) ? $omnify_product['upsell_ids'] : [];
		if (empty($omnify_ids)) {
			return [];
		}

		$omnify_placeholders = implode(',', array_fill(0, count($omnify_ids), '%d'));
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT * FROM {$this->table('products')} WHERE id IN ($omnify_placeholders) AND status = 'published'",
				...$omnify_ids
			),
			ARRAY_A
		);

		if (! is_array($omnify_rows)) {
			return [];
		}

		return array_map([$this, 'with_terms'], array_map([$this, 'hydrate'], $omnify_rows));
	}

	/**
	 * Get manually selected cross-sell products for a product.
	 */
	public function get_cross_sells(array $omnify_product): array {
		global $wpdb;
		$omnify_ids = ! empty($omnify_product['cross_sell_ids']) && is_array($omnify_product['cross_sell_ids']) ? $omnify_product['cross_sell_ids'] : [];
		if (empty($omnify_ids)) {
			return [];
		}

		$omnify_placeholders = implode(',', array_fill(0, count($omnify_ids), '%d'));
		$omnify_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT * FROM {$this->table('products')} WHERE id IN ($omnify_placeholders) AND status = 'published'",
				...$omnify_ids
			),
			ARRAY_A
		);

		if (! is_array($omnify_rows)) {
			return [];
		}

		return array_map([$this, 'with_terms'], array_map([$this, 'hydrate'], $omnify_rows));
	}

	/**
	 * Clean all caches related to a product.
	 */
	private function clean_product_caches(int $omnify_product_id): void {
		$this->increment_cache_version();
		wp_cache_delete('omnify_recent_sales_' . $omnify_product_id . '_24', 'omnifywp-ecommerce');
		wp_cache_delete('omnify_prod_stats_' . $omnify_product_id, 'omnifywp-ecommerce');
		wp_cache_delete('omnify_dl_count_' . $omnify_product_id, 'omnifywp-ecommerce');
		wp_cache_delete('omnify_inventory_logs_' . $omnify_product_id, 'omnifywp-ecommerce');
		wp_cache_delete('omnify_prod_stock_' . $omnify_product_id, 'omnifywp-ecommerce');
		wp_cache_delete('omnify_all_category_names', 'omnifywp-ecommerce');
		wp_cache_delete('omnify_all_tag_names', 'omnifywp-ecommerce');
		wp_cache_delete('omnify_all_brand_names', 'omnifywp-ecommerce');
		wp_cache_delete('omnify_sitemap_page_count', 'omnifywp-ecommerce');
	}

	/**
	 * Clean variation cache.
	 */
	private function clean_variation_caches(int $omnify_variation_id): void {
		$this->increment_cache_version();
		wp_cache_delete('omnify_var_stock_' . $omnify_variation_id, 'omnifywp-ecommerce');
	}

	/**
	 * Get category name by slug.
	 */
	public function get_category_name_by_slug(string $omnify_slug): ?string {
		global $wpdb;
		$omnify_cache_key = 'omnify_cat_name_' . md5($omnify_slug);
		$omnify_cat_name = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_cat_name) {
			$omnify_table = $this->table('product_categories');
			$omnify_cat_name = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT DISTINCT name FROM %i WHERE slug = %s LIMIT 1',
				$omnify_table,
				$omnify_slug
			));
			wp_cache_set($omnify_cache_key, $omnify_cat_name, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return $omnify_cat_name ?: null;
	}

	/**
	 * Get brand name by slug.
	 */
	public function get_brand_name_by_slug(string $omnify_slug): ?string {
		global $wpdb;
		$omnify_cache_key = 'omnify_brand_name_' . md5($omnify_slug);
		$omnify_brand_name = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_brand_name) {
			$omnify_table = $this->table('product_brands');
			$omnify_brand_name = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT DISTINCT name FROM %i WHERE slug = %s LIMIT 1',
				$omnify_table,
				$omnify_slug
			));
			wp_cache_set($omnify_cache_key, $omnify_brand_name, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return $omnify_brand_name ?: null;
	}

	/**
	 * Get recent sales count for a product.
	 */
	public function get_recent_sales_count(int $omnify_product_id, int $omnify_hours = 24): int {
		global $wpdb;
		$omnify_cache_key = 'omnify_recent_sales_' . $omnify_product_id . '_' . $omnify_hours;
		$omnify_sold_recent = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_sold_recent) {
			$omnify_table_items = $this->table('order_items');
			$omnify_table_orders = $wpdb->prefix . 'omnify_orders';
			$omnify_sold_recent = 0;

			$omnify_items_exists = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SHOW TABLES LIKE %s", $omnify_table_items)) === $omnify_table_items;
			$omnify_orders_exists = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare("SHOW TABLES LIKE %s", $omnify_table_orders)) === $omnify_table_orders;

			if ($omnify_items_exists && $omnify_orders_exists) {
				$omnify_sold_recent = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
					"SELECT SUM(item.quantity) 
					 FROM %i AS item
					 INNER JOIN %i AS ord ON item.order_id = ord.id
					 WHERE item.product_id = %d 
					   AND ord.created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR)
					   AND ord.status IN ('completed', 'processing', 'paid')
					   AND ord.deleted_at IS NULL",
					$omnify_table_items,
					$omnify_table_orders,
					$omnify_product_id,
					$omnify_hours
				));
			}
			wp_cache_set($omnify_cache_key, $omnify_sold_recent, 'omnifywp-ecommerce', 5 * MINUTE_IN_SECONDS);
		}
		return (int) $omnify_sold_recent;
	}

	/**
	 * Get all category names.
	 */
	public function get_all_category_names(): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_all_category_names';
		$omnify_categories = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_categories) {
			$omnify_table = $this->table('product_categories');
			$omnify_categories = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_table));
			wp_cache_set($omnify_cache_key, $omnify_categories, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_categories) ? $omnify_categories : [];
	}

	/**
	 * Get all tag names.
	 */
	public function get_all_tag_names(): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_all_tag_names';
		$omnify_tags = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_tags) {
			$omnify_table = $this->table('product_tags');
			$omnify_tags = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_table));
			wp_cache_set($omnify_cache_key, $omnify_tags, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_tags) ? $omnify_tags : [];
	}

	/**
	 * Get all brand names.
	 */
	public function get_all_brand_names(): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_all_brand_names';
		$omnify_brands = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_brands) {
			$omnify_table = $this->table('product_brands');
			$omnify_brands = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_table));
			wp_cache_set($omnify_cache_key, $omnify_brands, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_brands) ? $omnify_brands : [];
	}

	/**
	 * Get digital products excluding a specific product.
	 */
	public function get_digital_products(int $omnify_exclude_id): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_digital_products_' . $omnify_exclude_id;
		$omnify_products = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_products) {
			$omnify_table = $this->table('products');
			$omnify_products = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
				$wpdb->prepare(
					"SELECT id, name, status, sku FROM %i WHERE type = 'download' AND id != %d ORDER BY name ASC",
					$omnify_table,
					$omnify_exclude_id
				),
				ARRAY_A
			);
			wp_cache_set($omnify_cache_key, $omnify_products, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_products) ? $omnify_products : [];
	}

	/**
	 * Get other products excluding a specific product.
	 */
	public function get_other_products(int $omnify_exclude_id): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_other_products_' . $omnify_exclude_id;
		$omnify_products = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_products) {
			$omnify_table = $this->table('products');
			$omnify_products = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
				$wpdb->prepare(
					'SELECT id, name, status, sku FROM %i WHERE id != %d ORDER BY name ASC',
					$omnify_table,
					$omnify_exclude_id
				),
				ARRAY_A
			);
			wp_cache_set($omnify_cache_key, $omnify_products, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return is_array($omnify_products) ? $omnify_products : [];
	}

	/**
	 * Get product stats from orders/items.
	 */
	public function get_product_stats(int $omnify_product_id): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_prod_stats_' . $omnify_product_id;
		$omnify_stats = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_stats) {
			$omnify_table_items = $this->table('order_items');
			$omnify_table_orders = $wpdb->prefix . 'omnify_orders';
			$omnify_stats = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
				"SELECT COUNT(DISTINCT o.id) AS order_count,
					COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue,
					COALESCE(SUM(oi.quantity), 0) AS units_sold
				FROM %i oi
				JOIN %i o ON o.id = oi.order_id
				WHERE oi.product_id = %d AND o.status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested') AND o.deleted_at IS NULL",
				$omnify_table_items,
				$omnify_table_orders,
				$omnify_product_id
			), ARRAY_A);
			$omnify_stats = $omnify_stats ?: ['order_count' => 0, 'revenue' => 0, 'units_sold' => 0];
			wp_cache_set($omnify_cache_key, $omnify_stats, 'omnifywp-ecommerce', 5 * MINUTE_IN_SECONDS);
		}
		return $omnify_stats;
	}

	/**
	 * Get file download count for a product.
	 */
	public function get_file_download_count(int $omnify_product_id): int {
		global $wpdb;
		$omnify_cache_key = 'omnify_dl_count_' . $omnify_product_id;
		$omnify_count = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_count) {
			$omnify_downloads_table = $this->table('downloads');
			$omnify_files_table     = $this->table('product_files');
			$omnify_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				"SELECT COUNT(*) FROM %i dl
				JOIN %i pf ON pf.id = dl.file_id
				WHERE pf.product_id = %d",
				$omnify_downloads_table,
				$omnify_files_table,
				$omnify_product_id
			));
			wp_cache_set($omnify_cache_key, $omnify_count, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return $omnify_count;
	}

	/**
	 * Get inventory logs for a product.
	 */
	public function get_inventory_logs(int $omnify_product_id): array {
		global $wpdb;
		$omnify_cache_key = 'omnify_inventory_logs_' . $omnify_product_id;
		$omnify_logs = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_logs) {
			$omnify_logs_table = $this->table('inventory_logs');
			$omnify_vars_table = $this->table('product_variations');
			$omnify_logs = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
				"SELECT l.*, v.sku as var_sku, v.attributes as var_attributes, u.display_name as user_name
				 FROM %i l
				 LEFT JOIN %i v ON l.variation_id = v.id
				 LEFT JOIN %i u ON l.user_id = u.ID
				 WHERE l.product_id = %d
				 ORDER BY l.created_at DESC",
				$omnify_logs_table,
				$omnify_vars_table,
				$wpdb->users,
				$omnify_product_id
			), ARRAY_A);
			$omnify_logs = is_array($omnify_logs) ? $omnify_logs : [];
			wp_cache_set($omnify_cache_key, $omnify_logs, 'omnifywp-ecommerce', 5 * MINUTE_IN_SECONDS);
		}
		return $omnify_logs;
	}

	/**
	 * Get variation stock qty.
	 */
	public function get_variation_stock_qty(int $omnify_variation_id): int {
		global $wpdb;
		$omnify_cache_key = 'omnify_var_stock_' . $omnify_variation_id;
		$omnify_stock = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_stock) {
			$omnify_table = $this->table('product_variations');
			$omnify_stock = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT stock_qty FROM %i WHERE id = %d',
				$omnify_table,
				$omnify_variation_id
			));
			$omnify_stock = null === $omnify_stock ? 0 : (int) $omnify_stock;
			wp_cache_set($omnify_cache_key, $omnify_stock, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return (int) $omnify_stock;
	}

	/**
	 * Get product stock qty.
	 */
	public function get_product_stock_qty(int $omnify_product_id): int {
		global $wpdb;
		$omnify_cache_key = 'omnify_prod_stock_' . $omnify_product_id;
		$omnify_stock = wp_cache_get($omnify_cache_key, 'omnifywp-ecommerce');
		if (false === $omnify_stock) {
			$omnify_table = $this->table('products');
			$omnify_stock = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT stock_qty FROM %i WHERE id = %d',
				$omnify_table,
				$omnify_product_id
			));
			$omnify_stock = null === $omnify_stock ? 0 : (int) $omnify_stock;
			wp_cache_set($omnify_cache_key, $omnify_stock, 'omnifywp-ecommerce', HOUR_IN_SECONDS);
		}
		return (int) $omnify_stock;
	}

	private function get_cache_version(): string {
		$omnify_version = wp_cache_get('omnify_products_cache_version', 'omnifywp-ecommerce');
		if (false === $omnify_version) {
			$omnify_version = (string) get_option('omnify_products_cache_version', '1');
			wp_cache_set('omnify_products_cache_version', $omnify_version, 'omnifywp-ecommerce');
		}
		return $omnify_version;
	}

	private function increment_cache_version(): void {
		$omnify_new_version = (string) microtime(true);
		update_option('omnify_products_cache_version', $omnify_new_version);
		wp_cache_set('omnify_products_cache_version', $omnify_new_version, 'omnifywp-ecommerce');
	}
}

