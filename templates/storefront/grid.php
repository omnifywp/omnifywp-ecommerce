<?php
/**
 * Storefront grid template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_active_filters = $omnify_template_vars['omnify_active_filters'] ?? null;
$omnify_actual_price = $omnify_template_vars['omnify_actual_price'] ?? null;
$omnify_all_categories = $omnify_template_vars['omnify_all_categories'] ?? null;
$omnify_all_tags = $omnify_template_vars['omnify_all_tags'] ?? null;
$omnify_attr = $omnify_template_vars['omnify_attr'] ?? null;
$omnify_attr_map = $omnify_template_vars['omnify_attr_map'] ?? null;
$omnify_avg_rating = $omnify_template_vars['omnify_avg_rating'] ?? null;
$omnify_b_url = $omnify_template_vars['omnify_b_url'] ?? null;
$omnify_banner_bg = $omnify_template_vars['omnify_banner_bg'] ?? null;
$omnify_banner_slug = $omnify_template_vars['omnify_banner_slug'] ?? null;
$omnify_base_url = $omnify_template_vars['omnify_base_url'] ?? null;
$omnify_brand = $omnify_template_vars['omnify_brand'] ?? null;
$omnify_brand_name = $omnify_template_vars['omnify_brand_name'] ?? null;
$omnify_brand_options = $omnify_template_vars['omnify_brand_options'] ?? null;
$omnify_brand_slugs = $omnify_template_vars['omnify_brand_slugs'] ?? null;
$omnify_breadcrumb_tail = $omnify_template_vars['omnify_breadcrumb_tail'] ?? null;
$omnify_bval = $omnify_template_vars['omnify_bval'] ?? null;
$omnify_cart_url = $omnify_template_vars['omnify_cart_url'] ?? null;
$omnify_cat = $omnify_template_vars['omnify_cat'] ?? null;
$omnify_cat_name = $omnify_template_vars['omnify_cat_name'] ?? null;
$omnify_cat_slug = $omnify_template_vars['omnify_cat_slug'] ?? null;
$omnify_cat_url = $omnify_template_vars['omnify_cat_url'] ?? null;
$omnify_catalog_layout = $omnify_template_vars['omnify_catalog_layout'] ?? null;
$omnify_category_slugs = $omnify_template_vars['omnify_category_slugs'] ?? null;
$omnify_color = $omnify_template_vars['omnify_color'] ?? null;
$omnify_color_options = $omnify_template_vars['omnify_color_options'] ?? null;
$omnify_current_brand = $omnify_template_vars['omnify_current_brand'] ?? null;
$omnify_current_cat = $omnify_template_vars['omnify_current_cat'] ?? null;
$omnify_current_filters = $omnify_template_vars['omnify_current_filters'] ?? null;
$omnify_current_search = $omnify_template_vars['omnify_current_search'] ?? null;
$omnify_default_sort = $omnify_template_vars['omnify_default_sort'] ?? null;
$omnify_discount_pct = $omnify_template_vars['omnify_discount_pct'] ?? null;
$omnify_excerpt = $omnify_template_vars['omnify_excerpt'] ?? null;
$omnify_filter_position = $omnify_template_vars['omnify_filter_position'] ?? null;
$omnify_format_price = $omnify_template_vars['omnify_format_price'] ?? null;
$omnify_global_attrs = $omnify_template_vars['omnify_global_attrs'] ?? null;
$omnify_grid_columns = $omnify_template_vars['omnify_grid_columns'] ?? null;
$omnify_grid_rows = $omnify_template_vars['omnify_grid_rows'] ?? null;
$omnify_header_title = $omnify_template_vars['omnify_header_title'] ?? null;
$omnify_initial_count = $omnify_template_vars['omnify_initial_count'] ?? null;
$omnify_is_active = $omnify_template_vars['omnify_is_active'] ?? null;
$omnify_is_hot = $omnify_template_vars['omnify_is_hot'] ?? null;
$omnify_is_new = $omnify_template_vars['omnify_is_new'] ?? null;
$omnify_k = $omnify_template_vars['omnify_k'] ?? null;
$omnify_n = $omnify_template_vars['omnify_n'] ?? null;
$omnify_name_lower = $omnify_template_vars['omnify_name_lower'] ?? null;
$omnify_opts = $omnify_template_vars['omnify_opts'] ?? null;
$omnify_p = $omnify_template_vars['omnify_p'] ?? null;
$omnify_pa = $omnify_template_vars['omnify_pa'] ?? null;
$omnify_page_id = $omnify_template_vars['omnify_page_id'] ?? null;
$omnify_page_title = $omnify_template_vars['omnify_page_title'] ?? null;
$omnify_pagination_type = $omnify_template_vars['omnify_pagination_type'] ?? null;
$omnify_product = $omnify_template_vars['omnify_product'] ?? null;
$omnify_product_compare_summary = $omnify_template_vars['omnify_product_compare_summary'] ?? null;
$omnify_product_url = $omnify_template_vars['omnify_product_url'] ?? null;
$omnify_products = $omnify_template_vars['omnify_products'] ?? null;
$omnify_products_limit = $omnify_template_vars['omnify_products_limit'] ?? null;
$omnify_review_count = $omnify_template_vars['omnify_review_count'] ?? null;
$omnify_search_text = $omnify_template_vars['omnify_search_text'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_show_breadcrumbs = $omnify_template_vars['omnify_show_breadcrumbs'] ?? null;
$omnify_show_filters = $omnify_template_vars['omnify_show_filters'] ?? null;
$omnify_show_reviews_setting = $omnify_template_vars['omnify_show_reviews_setting'] ?? null;
$omnify_show_search = $omnify_template_vars['omnify_show_search'] ?? null;
$omnify_show_sku = $omnify_template_vars['omnify_show_sku'] ?? null;
$omnify_show_sort = $omnify_template_vars['omnify_show_sort'] ?? null;
$omnify_show_wishlist = $omnify_template_vars['omnify_show_wishlist'] ?? null;
$omnify_size = $omnify_template_vars['omnify_size'] ?? null;
$omnify_size_options = $omnify_template_vars['omnify_size_options'] ?? null;
$omnify_slug_page = $omnify_template_vars['omnify_slug_page'] ?? null;
$omnify_stock_qty = $omnify_template_vars['omnify_stock_qty'] ?? null;
$omnify_storefront_card_style = $omnify_template_vars['omnify_storefront_card_style'] ?? null;
$omnify_tag_slugs = $omnify_template_vars['omnify_tag_slugs'] ?? null;
$omnify_v = $omnify_template_vars['omnify_v'] ?? null;
$omnify_vals = $omnify_template_vars['omnify_vals'] ?? null;
$omnify_variations = $omnify_template_vars['omnify_variations'] ?? null;


$omnify_settings = get_option('omnify_settings', []);
$omnify_active_filters = $omnify_settings['storefront_filters'] ?? ['category', 'type'];
$omnify_show_filters = ! array_key_exists('storefront_show_filters', $omnify_settings) || ! empty($omnify_settings['storefront_show_filters']);
$omnify_show_search = ! array_key_exists('storefront_show_search', $omnify_settings) || ! empty($omnify_settings['storefront_show_search']);
$omnify_show_sort = ! array_key_exists('storefront_show_sort', $omnify_settings) || ! empty($omnify_settings['storefront_show_sort']);
$omnify_filter_position = in_array($omnify_settings['storefront_filter_position'] ?? '', ['top', 'left', 'right'], true) ? $omnify_settings['storefront_filter_position'] : 'top';
$omnify_catalog_layout = $omnify_settings['storefront_catalog_layout'] ?? 'grid';
$omnify_grid_columns = (int) ($omnify_settings['storefront_grid_columns'] ?? ($omnify_settings['design_grid_columns'] ?? 3));
$omnify_grid_rows = max(0, intval($omnify_settings['storefront_grid_rows'] ?? 0));
$omnify_products_limit = max(1, min(100, intval($omnify_settings['storefront_products_per_page'] ?? 12)));
if ('grid' === $omnify_catalog_layout && $omnify_grid_rows > 0) {
	$omnify_products_limit = min($omnify_products_limit, max(1, $omnify_grid_rows * $omnify_grid_columns));
}
$omnify_initial_count = min(count($omnify_products), $omnify_products_limit);
$omnify_show_reviews_setting = ! isset($omnify_settings['design_show_reviews']) || ! empty($omnify_settings['design_show_reviews']);
$omnify_show_wishlist = ! array_key_exists('storefront_show_wishlist', $omnify_settings) || ! empty($omnify_settings['storefront_show_wishlist']);
$omnify_show_sku = ! empty($omnify_settings['storefront_show_sku']);
$omnify_show_breadcrumbs = ! array_key_exists('storefront_show_breadcrumbs', $omnify_settings) || ! empty($omnify_settings['storefront_show_breadcrumbs']);
$omnify_default_sort = $omnify_settings['storefront_default_sort'] ?? 'latest';
$omnify_pagination_type = in_array($omnify_settings['storefront_pagination_type'] ?? '', ['classic', 'load_more'], true) ? $omnify_settings['storefront_pagination_type'] : 'classic';
// Respect Premium-style param
$omnify_paging_style = isset($_GET['paging-style']) ? sanitize_key(wp_unslash($_GET['paging-style'])) : '';
if ('load-more' === $omnify_paging_style) {
	$omnify_pagination_type = 'load_more';
}
$omnify_storefront_card_style = $omnify_settings['storefront_card_style'] ?? 'standard';
$omnify_show_add_to_cart = ! array_key_exists('storefront_show_add_to_cart', $omnify_settings) || ! empty($omnify_settings['storefront_show_add_to_cart']);

// Current filter state for active classes and link building (server-side faceted)
$omnify_current_filters = [
	'category' => isset($_GET['category']) ? sanitize_text_field(wp_unslash($_GET['category'])) : '',
	'search'   => isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '',
];
$omnify_raw_get = (array) wp_unslash($_GET);
foreach ($omnify_raw_get as $omnify_k => $omnify_v) {
	$omnify_clean_k = sanitize_key($omnify_k);
	if (strpos($omnify_clean_k, 'filter_') === 0 || strpos($omnify_clean_k, 'attr_') === 0) {
		$omnify_current_filters[$omnify_clean_k] = is_array($omnify_v) ? map_deep($omnify_v, 'sanitize_text_field') : sanitize_text_field((string) $omnify_v);
	}
}
$omnify_base_url = add_query_arg([]); // current page with existing query, add params on top

// Dynamic data collection for filters (Premium style)
$omnify_all_categories = [];
$omnify_all_tags = [];
foreach ($omnify_products as $omnify_p) {
	$omnify_all_categories = array_merge($omnify_all_categories, (array) ($omnify_p['categories'] ?? []));
	$omnify_all_tags = array_merge($omnify_all_tags, (array) ($omnify_p['tags'] ?? []));
}
$omnify_all_categories = array_values(array_unique(array_filter($omnify_all_categories)));
$omnify_all_tags = array_values(array_unique(array_filter($omnify_all_tags)));
?>
<div class="omnify-storefront-wrapper omnify-storefront-wrapper--<?php echo esc_attr($omnify_catalog_layout); ?>">


	<?php
	// Get storefront page ID with fallback chain
	$omnify_page_id = get_the_ID();
	if ( ! $omnify_page_id ) {
		$omnify_page_id = get_queried_object_id();
	}
	if ( ! $omnify_page_id && ! empty( $omnify_settings['page_storefront'] ) ) {
		$omnify_page_id = absint( $omnify_settings['page_storefront'] );
	}

	$omnify_page_title = $omnify_page_id ? get_the_title($omnify_page_id) : __('Shop', 'omnifywp-ecommerce');
	if (empty($omnify_page_title)) {
		$omnify_page_title = __('Shop', 'omnifywp-ecommerce');
	}

	// Capture active filters
	$omnify_current_cat    = isset($_GET['category']) ? sanitize_text_field(wp_unslash($_GET['category'])) : '';
	$omnify_current_brand  = isset($_GET['filter_brand']) ? sanitize_text_field(wp_unslash($_GET['filter_brand'])) : (isset($_GET['brand']) ? sanitize_text_field(wp_unslash($_GET['brand'])) : '');
	$omnify_current_search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';

	// Determine dynamic title and breadcrumb text
	$omnify_header_title = $omnify_page_title;
	$omnify_breadcrumb_tail = '';

	global $wpdb;
	if ($omnify_current_cat) {
		$omnify_cat_name = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
			"SELECT DISTINCT name FROM {$wpdb->prefix}omnify_product_categories WHERE slug = %s LIMIT 1",
			$omnify_current_cat
		));
		$omnify_header_title = $omnify_cat_name ? $omnify_cat_name : ucfirst(str_replace('-', ' ', $omnify_current_cat));
		$omnify_breadcrumb_tail = $omnify_header_title;
	} elseif ($omnify_current_brand) {
		$omnify_brand_name = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
			"SELECT DISTINCT name FROM {$wpdb->prefix}omnify_product_brands WHERE slug = %s LIMIT 1",
			$omnify_current_brand
		));
		$omnify_header_title = $omnify_brand_name ? $omnify_brand_name : ucfirst(str_replace('-', ' ', $omnify_current_brand));
		$omnify_breadcrumb_tail = $omnify_header_title;
	} elseif ($omnify_current_search) {
		// translators: %s: placeholder value.
		$omnify_header_title = sprintf(__('Search results for "%s"', 'omnifywp-ecommerce'), $omnify_current_search);
		// translators: %s: placeholder value.
		$omnify_breadcrumb_tail = sprintf(__('Search: %s', 'omnifywp-ecommerce'), $omnify_current_search);
	}

	// Get dynamic background image (category/brand page featured image -> storefront page featured image -> fallback banner)
	$omnify_banner_bg = '';
	$omnify_banner_slug = $omnify_current_cat ? $omnify_current_cat : ($omnify_current_brand ? $omnify_current_brand : '');
	if ($omnify_banner_slug) {
		$omnify_slug_page = get_page_by_path($omnify_banner_slug, OBJECT, 'page');
		if ($omnify_slug_page) {
			$omnify_banner_bg = get_the_post_thumbnail_url($omnify_slug_page->ID, 'full');
		}
	}
	if (empty($omnify_banner_bg) && $omnify_page_id) {
		$omnify_banner_bg = get_the_post_thumbnail_url($omnify_page_id, 'full');
	}
	if (empty($omnify_banner_bg)) {
		$omnify_banner_bg = OMNIFY_URL . 'assets/storefront/images/shop-banner.png';
	}

	// Dynamic category counts
	$omnify_cat_counts = [];
	foreach ($omnify_products as $omnify_p) {
		if (!empty($omnify_p['categories']) && is_array($omnify_p['categories'])) {
			foreach ($omnify_p['categories'] as $omnify_cat) {
				$omnify_cat_slug = sanitize_title($omnify_cat);
				$omnify_cat_counts[$omnify_cat_slug] = ($omnify_cat_counts[$omnify_cat_slug] ?? 0) + 1;
			}
		}
	}

	// Dynamic label counts and max price
	$omnify_hot_count = 0;
	$omnify_new_count = 0;
	$omnify_sale_count = 0;
	$omnify_max_price = 0;
	foreach ($omnify_products as $omnify_p) {
		$omnify_is_hot = in_array('hot', array_map('strtolower', (array) ($omnify_p['tags'] ?? [])), true) || ($omnify_p['id'] % 5 === 0);
		$omnify_is_new = in_array('new', array_map('strtolower', (array) ($omnify_p['tags'] ?? [])), true) || ($omnify_p['id'] % 7 === 0);
		$omnify_is_sale = ! empty($omnify_p['sale_price']) && (float) $omnify_p['sale_price'] < (float) $omnify_p['price'];
		if ($omnify_is_hot) $omnify_hot_count++;
		if ($omnify_is_new) $omnify_new_count++;
		if ($omnify_is_sale) $omnify_sale_count++;

		$omnify_p_price = (float) $omnify_p['price'];
		if (! empty($omnify_p['sale_price'])) {
			$omnify_p_price = (float) $omnify_p['sale_price'];
		}
		if ($omnify_p_price > $omnify_max_price) {
			$omnify_max_price = $omnify_p_price;
		}
	}
	$omnify_max_price = ceil($omnify_max_price / 100) * 100;
	if ($omnify_max_price <= 0) {
		$omnify_max_price = 1000;
	}

	if (empty($omnify_brand_options)) {
		$omnify_brand_options = [];
		foreach ($omnify_products as $omnify_p) {
			if (! empty($omnify_p['brands']) && is_array($omnify_p['brands'])) {
				foreach ($omnify_p['brands'] as $omnify_brand) {
					$omnify_brand_options[] = $omnify_brand;
				}
			}
			if (! empty($omnify_p['attributes']) && is_array($omnify_p['attributes'])) {
				foreach ($omnify_p['attributes'] as $omnify_attr) {
					$omnify_attr_name = strtolower((string) ($omnify_attr['name'] ?? ''));
					if ('brand' === $omnify_attr_name || 'brands' === $omnify_attr_name) {
						if (! empty($omnify_attr['options']) && is_array($omnify_attr['options'])) {
							foreach ($omnify_attr['options'] as $omnify_opt) {
								$omnify_brand_options[] = $omnify_opt;
							}
						}
					}
				}
			}
		}
		$omnify_brand_options = array_unique($omnify_brand_options);
	}

	if (empty($omnify_banner_bg)) {
		$omnify_banner_bg = OMNIFY_URL . 'assets/storefront/images/shop-banner.png';
	}
	?>
	<?php
	$omnify_settings = get_option('omnify_settings', []);
	$omnify_show_hero = ! array_key_exists('storefront_show_hero', $omnify_settings) || ! empty($omnify_settings['storefront_show_hero']);
	$omnify_hero_title = $omnify_settings['storefront_hero_title'] ?? 'Storefront';
	if (empty($omnify_hero_title)) {
		$omnify_hero_title = $omnify_header_title;
	}
	$omnify_hero_desc = $omnify_settings['storefront_hero_desc'] ?? 'High quality products to build, grow and succeed.';
	$omnify_hero_image = $omnify_settings['storefront_hero_image'] ?? '';
	if (empty($omnify_hero_image)) {
		$omnify_hero_image = OMNIFY_URL . 'assets/storefront/images/catalog-hero.jpg';
	}
	$omnify_hero_wave_color = $omnify_settings['storefront_hero_wave_color'] ?? '#e11d48';
	$omnify_show_features_bar = ! array_key_exists('storefront_show_features_bar', $omnify_settings) || ! empty($omnify_settings['storefront_show_features_bar']);

	$omnify_features = [];
	for ($i = 1; $i <= 4; $i++) {
		$show = ! array_key_exists("storefront_feature_{$i}_show", $omnify_settings) || ! empty($omnify_settings["storefront_feature_{$i}_show"]);
		if ($show) {
			$omnify_features[] = [
				'icon'  => $omnify_settings["storefront_feature_{$i}_icon"] ?? 'dollar',
				'title' => $omnify_settings["storefront_feature_{$i}_title"] ?? '',
				'desc'  => $omnify_settings["storefront_feature_{$i}_desc"] ?? '',
			];
		}
	}

	if (! function_exists('omnify_render_feature_svg')) {
		function omnify_render_feature_svg($icon) {
			switch ($icon) {
				case 'dollar':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
				case 'lock':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
				case 'lightning':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>';
				case 'star':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
				case 'shield':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
				case 'check':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
				case 'headphones':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>';
				case 'heart':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
				case 'cart':
					return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"><circle cx="20" cy="21" r="1"><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
				default:
					return '';
			}
		}
	}
	?>

	<?php if ($omnify_show_hero) : ?>
	<div class="omnify-catalog-hero">
		<div class="omnify-catalog-hero-inner">
			<div class="omnify-catalog-hero-left">
				<h1 class="omnify-catalog-hero-title"><?php echo esc_html($omnify_hero_title); ?></h1>
				<div class="omnify-catalog-hero-wave">
					<svg width="34" height="5" viewBox="0 0 34 5" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 3.5C5.5 3.5 8 1.5 12 1.5C16 1.5 18.5 3.5 22.5 3.5C26.5 3.5 29 1.5 33 1.5" stroke="<?php echo esc_attr($omnify_hero_wave_color); ?>" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				<?php if (! empty($omnify_hero_desc)) : ?>
					<p class="omnify-catalog-hero-desc"><?php echo esc_html($omnify_hero_desc); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Reassurance Row -->
		<?php if ($omnify_show_features_bar && ! empty($omnify_features)) : ?>
			<div class="omnify-catalog-hero-features">
				<?php foreach ($omnify_features as $omnify_feat) : ?>
					<div class="omnify-catalog-hero-feature-item">
						<div class="omnify-feature-icon-wrap">
							<?php echo omnify_render_feature_svg($omnify_feat['icon']); ?>
						</div>
						<div class="omnify-feature-text">
							<strong><?php echo esc_html($omnify_feat['title']); ?></strong>
							<?php if (! empty($omnify_feat['desc'])) : ?>
								<span><?php echo esc_html($omnify_feat['desc']); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php
	// Dynamic filter options from global attributes + current products (user manageable)
	$omnify_color_options = [];
	$omnify_size_options = [];
	$omnify_brand_options = [];
	$omnify_global_attrs = get_option('omnify_global_attributes', []);
	$omnify_attr_map = [];
	if (is_array($omnify_global_attrs)) {
		foreach ($omnify_global_attrs as $omnify_attr) {
			$omnify_name_lower = strtolower($omnify_attr['name'] ?? '');
			$omnify_opts = (array) ($omnify_attr['options'] ?? []);
			if ($omnify_name_lower === 'color' || $omnify_name_lower === 'colour') {
				$omnify_color_options = $omnify_opts;
			} elseif ($omnify_name_lower === 'size') {
				$omnify_size_options = $omnify_opts;
			} elseif ($omnify_name_lower === 'brand' || $omnify_name_lower === 'brands') {
				$omnify_brand_options = $omnify_opts;
			}
			$omnify_attr_map[$omnify_name_lower] = $omnify_opts;
		}
	}

	// Collect unique values from current products for dynamic facets
	foreach ($omnify_products as $omnify_p) {
		if (!empty($omnify_p['attributes']) && is_array($omnify_p['attributes'])) {
			foreach ($omnify_p['attributes'] as $omnify_pa) {
				$omnify_n = strtolower($omnify_pa['name'] ?? '');
				$omnify_vals = (array) ($omnify_pa['options'] ?? $omnify_pa['values'] ?? []);
				if (!isset($omnify_attr_map[$omnify_n])) $omnify_attr_map[$omnify_n] = [];
				$omnify_attr_map[$omnify_n] = array_unique(array_merge($omnify_attr_map[$omnify_n], $omnify_vals));
			}
		}
	}
	if (empty($omnify_color_options) && isset($omnify_attr_map['color'])) $omnify_color_options = $omnify_attr_map['color'];
	if (empty($omnify_size_options) && isset($omnify_attr_map['size'])) $omnify_size_options = $omnify_attr_map['size'];
	if (empty($omnify_brand_options) && isset($omnify_attr_map['brand'])) $omnify_brand_options = $omnify_attr_map['brand'];

	// Fallbacks only if still empty (for empty catalogs)
	if (empty($omnify_color_options)) $omnify_color_options = ['Black', 'White', 'Red', 'Blue'];
	if (empty($omnify_size_options)) $omnify_size_options = ['S', 'M', 'L', 'XL'];
	if (empty($omnify_brand_options)) $omnify_brand_options = [];
	?>
	<div class="omnify-storefront-shell">
		<div class="omnify-storefront-layout-grid">
			<!-- Left Column: Sidebar Filters -->
			<aside class="omnify-storefront-sidebar">
				<!-- Categories Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Categories', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-category-list">
							<label class="omnify-sidebar-category-item">
								<input type="checkbox" class="omnify-sidebar-category-check" value="" checked>
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></span>
							</label>
							<?php foreach ($omnify_all_categories as $omnify_cat) :
								$omnify_cat_slug = sanitize_title($omnify_cat);
								$omnify_count = $omnify_cat_counts[$omnify_cat_slug] ?? 0;
							?>
								<label class="omnify-sidebar-category-item">
									<input type="checkbox" class="omnify-sidebar-category-check" value="<?php echo esc_attr($omnify_cat_slug); ?>">
									<span class="checkbox-box"></span>
									<span class="label-name"><?php echo esc_html($omnify_cat); ?></span>
									<span class="count">(<?php echo (int) $omnify_count; ?>)</span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Color Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Color', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-color-swatches">
							<?php foreach ($omnify_color_options as $omnify_color) : ?>
								<span class="omnify-filter-swatch color" data-attribute="color" data-value="<?php echo esc_attr(strtolower(trim($omnify_color))); ?>" style="background-color: <?php echo esc_attr(strtolower(trim($omnify_color))); ?>;" title="<?php echo esc_attr($omnify_color); ?>"></span>
							<?php endforeach; ?>
							<span class="swatch-count-more">+2</span>
						</div>
					</div>
				</div>

				<!-- Price Range Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Price Range', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-price-range">
							<input type="range" id="omnify-sidebar-price-slider" min="0" max="<?php echo (int) $omnify_max_price; ?>" value="<?php echo (int) $omnify_max_price; ?>" class="omnify-price-slider">
							<div class="omnify-sidebar-price-inputs">
								<div class="price-input-wrap">
									<span class="currency">৳</span>
									<input type="number" id="omnify-sidebar-price-min" value="0">
								</div>
								<div class="price-input-wrap">
									<span class="currency">৳</span>
									<input type="number" id="omnify-sidebar-price-max" value="<?php echo (int) $omnify_max_price; ?>">
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Size Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Size', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-size-swatches">
							<?php foreach ($omnify_size_options as $omnify_size) : ?>
								<span class="omnify-filter-swatch size" data-attribute="size" data-value="<?php echo esc_attr(strtolower(trim($omnify_size))); ?>" title="<?php echo esc_attr($omnify_size); ?>"><?php echo esc_html($omnify_size); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Labels Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Labels', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-labels-list">
							<label class="omnify-sidebar-label-item">
								<input type="checkbox" class="omnify-sidebar-label-check" value="hot">
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('Hot', 'omnifywp-ecommerce'); ?></span>
								<span class="count"><?php echo (int) $omnify_hot_count; ?></span>
							</label>
							<label class="omnify-sidebar-label-item">
								<input type="checkbox" class="omnify-sidebar-label-check" value="new">
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('New', 'omnifywp-ecommerce'); ?></span>
								<span class="count"><?php echo (int) $omnify_new_count; ?></span>
							</label>
							<label class="omnify-sidebar-label-item">
								<input type="checkbox" class="omnify-sidebar-label-check" value="sale">
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('Sale', 'omnifywp-ecommerce'); ?></span>
								<span class="count"><?php echo (int) $omnify_sale_count; ?></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Status Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-status-list">
							<label class="omnify-sidebar-status-item">
								<input type="checkbox" class="omnify-sidebar-status-check omnify-filter-status-check" value="instock">
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('In Stock', 'omnifywp-ecommerce'); ?></span>
							</label>
							<label class="omnify-sidebar-status-item">
								<input type="checkbox" class="omnify-sidebar-status-check omnify-filter-status-check" value="onsale">
								<span class="checkbox-box"></span>
								<span class="label-name"><?php esc_html_e('On Sale', 'omnifywp-ecommerce'); ?></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Brands Accordion -->
				<div class="omnify-sidebar-widget active">
					<h3 class="omnify-sidebar-widget-title">
						<span><?php esc_html_e('Brands', 'omnifywp-ecommerce'); ?></span>
						<span class="arrow"></span>
					</h3>
					<div class="omnify-sidebar-widget-content">
						<div class="omnify-sidebar-brands-list">
							<?php foreach ($omnify_brand_options as $omnify_brand) :
								$omnify_bval = strtolower(trim($omnify_brand));
							?>
								<label class="omnify-sidebar-brand-item">
									<input type="checkbox" class="omnify-sidebar-brand-check omnify-filter-brand-check" value="<?php echo esc_attr($omnify_bval); ?>">
									<span class="checkbox-box"></span>
									<span class="label-name"><?php echo esc_html($omnify_brand); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Clear All Button -->
				<a href="#" class="omnify-sidebar-clear-all">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
					<span><?php esc_html_e('Clear All', 'omnifywp-ecommerce'); ?></span>
				</a>

				<!-- Help Box Widget -->
				<div class="omnify-sidebar-help-card" style="margin-top: 24px; padding: 20px; background: #ffffff; border: 1px solid var(--omnify-gray-200, #e2e8f0); border-radius: 12px; display: flex; flex-direction: column; gap: 12px;">
					<div style="display: flex; align-items: center; gap: 12px;">
						<span style="font-size: 24px; color: var(--omnify-primary); line-height: 1;">🎧</span>
						<h4 style="margin: 0; font-size: 14px; font-weight: 500; color: #0f172a;"><?php esc_html_e('Need Help?', 'omnifywp-ecommerce'); ?></h4>
					</div>
					<p style="margin: 0; font-size: 11px; line-height: 1.4; color: #64748b;"><?php esc_html_e("We're here to help you find the right product.", 'omnifywp-ecommerce'); ?></p>
					<a href="#" class="omnify-support-btn" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 16px; border: 1.2px solid var(--omnify-primary); border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--omnify-primary); text-decoration: none; transition: all 0.2s ease; background: transparent; text-align: center; cursor: pointer;"><?php esc_html_e('Contact Support →', 'omnifywp-ecommerce'); ?></a>
				</div>
			</aside>

			<!-- Right Column: Products Content Area -->
			<div class="omnify-storefront-content">
		<?php if ($omnify_show_filters) : ?>
			<!-- Storefront catalog toolbar -->
			<div class="omnify-horizontal-filter-bar">
				<div class="omnify-filter-left">
					<button type="button" class="omnify-sidebar-toggle-btn">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
						<span><?php esc_html_e('Filters', 'omnifywp-ecommerce'); ?></span>
					</button>



					<span id="omnify-storefront-results" class="omnify-filter-results-count">
						<?php 
						// translators: %d: placeholder value.
						printf(esc_html__('%d results', 'omnifywp-ecommerce'), count($omnify_products)); ?>
					</span>
				</div>

				<div class="omnify-filter-right">
					<?php if ($omnify_show_search) : ?>
						<form method="get" class="omnify-filter-search-box">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
							<input type="search" id="omnify-product-search" name="search" value="<?php echo esc_attr($omnify_current_filters['search'] ?? ''); ?>" placeholder="<?php esc_attr_e('Search product...', 'omnifywp-ecommerce'); ?>" />
						</form>
					<?php endif; ?>

					<!-- Sort Dropdown -->
					<?php if ($omnify_show_sort) : ?>
						<div class="omnify-sort-dropdown">
							<button type="button" class="omnify-sort-toggle" id="omnify-sort-toggle" aria-label="<?php esc_attr_e('Sort products', 'omnifywp-ecommerce'); ?>">
								<svg class="omnify-sort-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h12"/><path d="M3 12h9"/><path d="M3 17h6"/><path d="M18 6v12"/><path d="m15 15 3 3 3-3"/></svg>
								<span><?php esc_html_e('Latest', 'omnifywp-ecommerce'); ?></span>
								<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
							</button>
							<div class="omnify-sort-options" id="omnify-sort-options" style="display: none;">
								<a href="#" data-sort="latest" class="is-active"><?php esc_html_e('Latest', 'omnifywp-ecommerce'); ?></a>
								<a href="#" data-sort="price_asc"><?php esc_html_e('Price: low to high', 'omnifywp-ecommerce'); ?></a>
								<a href="#" data-sort="price_desc"><?php esc_html_e('Price: high to low', 'omnifywp-ecommerce'); ?></a>
								<a href="#" data-sort="name_asc"><?php esc_html_e('Name: A to Z', 'omnifywp-ecommerce'); ?></a>
							</div>
							<!-- Hidden select for form compatibility -->
							<select id="omnify-sort-filter" style="display: none;">
								<option value="latest" <?php selected($omnify_default_sort, 'latest'); ?>>Latest</option>
								<option value="price_asc" <?php selected($omnify_default_sort, 'price_asc'); ?>>Price: low to high</option>
								<option value="price_desc" <?php selected($omnify_default_sort, 'price_desc'); ?>>Price: high to low</option>
								<option value="name_asc" <?php selected($omnify_default_sort, 'name_asc'); ?>>Name: A to Z</option>
							</select>
						</div>
					<?php endif; ?>

					<!-- Columns Grid Switcher Icons -->
					<div class="omnify-layout-switches">
						<button type="button" class="omnify-layout-btn <?php echo ('grid' === $omnify_catalog_layout && 2 === $omnify_grid_columns) ? 'is-active' : ''; ?>" data-cols="2" aria-label="<?php esc_attr_e('2 columns', 'omnifywp-ecommerce'); ?>">
							<svg width="18" height="14" viewBox="0 0 18 14" fill="currentColor">
								<rect x="0" y="0" width="8.2" height="5.5" />
								<rect x="9.8" y="0" width="8.2" height="5.5" />
								<rect x="0" y="7.5" width="8.2" height="5.5" />
								<rect x="9.8" y="7.5" width="8.2" height="5.5" />
							</svg>
						</button>
						<button type="button" class="omnify-layout-btn <?php echo ('grid' === $omnify_catalog_layout && 3 === $omnify_grid_columns) ? 'is-active' : ''; ?>" data-cols="3" aria-label="<?php esc_attr_e('3 columns', 'omnifywp-ecommerce'); ?>">
							<svg width="18" height="14" viewBox="0 0 18 14" fill="currentColor">
								<rect x="0" y="0" width="5.2" height="5.5" />
								<rect x="6.4" y="0" width="5.2" height="5.5" />
								<rect x="12.8" y="0" width="5.2" height="5.5" />
								<rect x="0" y="7.5" width="5.2" height="5.5" />
								<rect x="6.4" y="7.5" width="5.2" height="5.5" />
								<rect x="12.8" y="7.5" width="5.2" height="5.5" />
							</svg>
						</button>


						<button type="button" class="omnify-layout-btn <?php echo ('list' === $omnify_catalog_layout && 2 === $omnify_grid_columns) ? 'is-active' : ''; ?>" data-cols="list-2" aria-label="<?php esc_attr_e('Two column list', 'omnifywp-ecommerce'); ?>">
							<svg width="18" height="14" viewBox="0 0 18 14" fill="currentColor">
								<rect x="0" y="0" width="7" height="3" />
								<rect x="9" y="0" width="7" height="3" />
								<rect x="0" y="5.5" width="7" height="3" />
								<rect x="9" y="5.5" width="7" height="3" />
								<rect x="0" y="11" width="7" height="3" />
								<rect x="9" y="11" width="7" height="3" />
							</svg>
						</button>
						<button type="button" class="omnify-layout-btn <?php echo ('list' === $omnify_catalog_layout && 2 !== $omnify_grid_columns) ? 'is-active' : ''; ?>" data-cols="list" aria-label="<?php esc_attr_e('List view', 'omnifywp-ecommerce'); ?>">
							<svg width="18" height="14" viewBox="0 0 18 14" fill="currentColor">
								<rect x="0" y="0" width="5" height="3" />
								<rect x="7" y="0" width="11" height="3" />
								<rect x="0" y="5.5" width="5" height="3" />
								<rect x="7" y="5.5" width="11" height="3" />
								<rect x="0" y="11" width="5" height="3" />
								<rect x="7" y="11" width="11" height="3" />
							</svg>
						</button>
					</div>

					<!-- Cart Link -->
					<div class="omnify-filter-cart-wrap">
						<a href="<?php echo esc_url($omnify_cart_url ?? '#'); ?>" class="omnify-cart-link" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; color: var(--omnify-dark); font-size: 13.5px; text-decoration: none; padding: 4px 6px;">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
							<span>Cart</span>
							<span id="omnify-cart-count" style="background: var(--omnify-primary); color: white; padding: 1px 6px; border-radius: 999px; font-size: 11px; font-weight: 500; margin-left: 2px;">0</span>
						</a>
					</div>
				</div>
			</div>

			<!-- Slide Down Dropdown Drawer for Options -->
			<div class="omnify-filter-drawer" id="omnify-filter-drawer" style="max-height: 0; opacity: 0; overflow: hidden; transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
				<div class="omnify-filter-drawer-content">
					<!-- Hidden select for compatibility -->
					<select id="omnify-category-filter" style="display: none;">
						<option value="" <?php selected($omnify_current_filters['category'] ?? '', ''); ?>>All Categories</option>
						<?php foreach ($omnify_all_categories as $omnify_cat) : ?>
							<?php $omnify_cat_option_slug = sanitize_title($omnify_cat); ?>
							<option value="<?php echo esc_attr($omnify_cat_option_slug); ?>" <?php selected($omnify_current_filters['category'] ?? '', $omnify_cat_option_slug); ?>><?php echo esc_html($omnify_cat); ?></option>
						<?php endforeach; ?>
					</select>

					<!-- Category Group -->
					<div class="omnify-filter-group" data-group="category" style="display: none;">
						<h4><?php esc_html_e('Filter by Category', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-options-grid categories">
							<a href="#" class="omnify-filter-link is-active" data-value=""><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></a>
							<?php foreach ($omnify_all_categories as $omnify_cat) : ?>
								<?php $omnify_cat_option_slug = sanitize_title($omnify_cat); ?>
								<a href="#" class="omnify-filter-link" data-value="<?php echo esc_attr($omnify_cat_option_slug); ?>"><?php echo esc_html($omnify_cat); ?></a>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Color Group -->
					<div class="omnify-filter-group" data-group="color" style="display: none;">
						<h4><?php esc_html_e('Filter by Color', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-options-swatches color">
							<?php foreach ($omnify_color_options as $omnify_color) : ?>
								<span class="omnify-filter-swatch color" data-attribute="color" data-value="<?php echo esc_attr(strtolower(trim($omnify_color))); ?>" style="background-color: <?php echo esc_attr(strtolower(trim($omnify_color))); ?>;" title="<?php echo esc_attr($omnify_color); ?>"></span>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Price Group -->
					<div class="omnify-filter-group" data-group="price" style="display: none;">
						<h4><?php esc_html_e('Filter by Price', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-price-slider-wrap" style="padding: 10px 0;">
							<input type="range" class="omnify-filter-slider" id="omnify-top-price-slider" min="0" max="1000" value="1000" style="width: 100%; accent-color: var(--omnify-primary);">
							<div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 13px; color: #475569;">
								<span>$0</span>
								<span id="omnify-top-price-display">$1000</span>
							</div>
						</div>
					</div>

					<!-- Size Blocks Group -->
					<div class="omnify-filter-group" data-group="size" style="display: none;">
						<h4><?php esc_html_e('Filter by Size', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-options-swatches size">
							<?php foreach ($omnify_size_options as $omnify_size) : ?>
								<span class="omnify-filter-swatch size" data-attribute="size" data-value="<?php echo esc_attr(strtolower(trim($omnify_size))); ?>"><?php echo esc_html($omnify_size); ?></span>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Status Checks Group -->
					<div class="omnify-filter-group" data-group="status" style="display: none;">
						<h4><?php esc_html_e('Filter by Status', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-options-list">
							<label class="omnify-filter-checkbox-label">
								<input type="checkbox" class="omnify-checkbox omnify-filter-status-check" value="instock">
								<span><?php esc_html_e('In stock', 'omnifywp-ecommerce'); ?></span>
							</label>
							<label class="omnify-filter-checkbox-label">
								<input type="checkbox" class="omnify-checkbox omnify-filter-status-check" value="onsale">
								<span><?php esc_html_e('On sale', 'omnifywp-ecommerce'); ?></span>
							</label>
						</div>
					</div>

					<!-- Brands Checkboxes Group -->
					<div class="omnify-filter-group" data-group="brand" style="display: none;">
						<h4><?php esc_html_e('Filter by Brand', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-filter-options-grid brands">
							<?php $omnify_has_active_brand = ! empty($omnify_current_filters['filter_brand']) || ! empty($omnify_current_filters['attr_brand']); ?>
							<a href="#" class="omnify-filter-link <?php echo $omnify_has_active_brand ? '' : 'is-active'; ?>" data-value=""><?php esc_html_e('All Brands', 'omnifywp-ecommerce'); ?></a>
							<?php foreach ($omnify_brand_options as $omnify_brand) : 
								$omnify_bval = strtolower(trim($omnify_brand));
								$omnify_b_url = add_query_arg('filter_brand', $omnify_bval, $omnify_base_url);
								$omnify_is_active = (isset($omnify_current_filters['filter_brand']) && $omnify_current_filters['filter_brand'] === $omnify_bval) || (isset($omnify_current_filters['attr_brand']) && $omnify_current_filters['attr_brand'] === $omnify_bval);
							?>
								<a href="<?php echo esc_url($omnify_b_url); ?>" class="omnify-filter-link <?php echo $omnify_is_active ? 'is-active' : ''; ?>" data-value="<?php echo esc_attr($omnify_bval); ?>"><?php echo esc_html($omnify_brand); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<section class="omnify-storefront-products">

	<?php
	$omnify_grid_layout_class = 'omnify-storefront-grid--' . $omnify_catalog_layout;
	if ('list' === $omnify_catalog_layout && 2 === $omnify_grid_columns) {
		$omnify_grid_layout_class = 'omnify-storefront-grid--list-2';
	}
	?>
	<div id="omnify-storefront-grid" class="omnify-storefront-grid <?php echo esc_attr($omnify_grid_layout_class); ?> omnify-cols-<?php echo esc_attr((string) $omnify_grid_columns); ?>" 
		data-products-limit="<?php echo esc_attr((string) $omnify_products_limit); ?>" 
		data-pagination-type="<?php echo esc_attr($omnify_pagination_type); ?>" 
		data-default-sort="<?php echo esc_attr($omnify_default_sort); ?>">
		<?php if (empty($omnify_products)) : ?>
			<div class="omnify-storefront-empty">
				<p><?php esc_html_e('No products found in the catalog.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<?php foreach ($omnify_products as $omnify_product) : 
				$omnify_avg_rating = $this->omnify_reviews->get_average_rating((int) $omnify_product['id']);
				$omnify_review_count = $this->omnify_reviews->get_total_count((int) $omnify_product['id']);
				$omnify_actual_price = (null !== ($omnify_product['sale_price'] ?? null) && '' !== (string) $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price'];
				$omnify_category_slugs = implode(' ', array_map('sanitize_title', (array) ($omnify_product['categories'] ?? [])));
				$omnify_product_brand_values = (array) ($omnify_product['brands'] ?? []);
				if (! empty($omnify_product['attributes']) && is_array($omnify_product['attributes'])) {
					foreach ($omnify_product['attributes'] as $omnify_brand_attr) {
						$omnify_brand_attr_name = strtolower((string) ($omnify_brand_attr['name'] ?? ''));
						if ('brand' === $omnify_brand_attr_name || 'brands' === $omnify_brand_attr_name) {
							$omnify_product_brand_values = array_merge($omnify_product_brand_values, (array) ($omnify_brand_attr['options'] ?? []));
						}
					}
				}
				$omnify_brand_slugs = implode(' ', array_unique(array_map('sanitize_title', $omnify_product_brand_values)));
				$omnify_tag_slugs = implode(' ', array_map('sanitize_title', (array) ($omnify_product['tags'] ?? [])));
				$omnify_search_text = strtolower(trim((string) $omnify_product['name'] . ' ' . wp_strip_all_tags((string) ($omnify_product['description'] ?? '')) . ' ' . implode(' ', (array) ($omnify_product['categories'] ?? [])) . ' ' . implode(' ', (array) ($omnify_product['tags'] ?? []))));
				$omnify_product_url = add_query_arg('omnify_product', $omnify_product['slug'], get_permalink());
				$omnify_product_compare_summary = wp_trim_words(wp_strip_all_tags((string) ($omnify_product['description'] ?? '')), 18);
			?>
				<?php
				$omnify_variations = [];
				if ('variable' === ($omnify_product['type'] ?? '')) {
					$omnify_variations = $this->omnify_products->get_variations((int) $omnify_product['id']);
				}
				$omnify_discount_pct = 0;
				if (! empty($omnify_product['sale_price']) && (float) $omnify_product['price'] > 0) {
					$omnify_discount_pct = round((((float) $omnify_product['price'] - (float) $omnify_product['sale_price']) / (float) $omnify_product['price']) * 100);
				}
				$omnify_is_hot = in_array('hot', array_map('strtolower', (array) ($omnify_product['tags'] ?? [])), true) || ($omnify_product['id'] % 5 === 0);
				$omnify_is_new = in_array('new', array_map('strtolower', (array) ($omnify_product['tags'] ?? [])), true) || ($omnify_product['id'] % 7 === 0);
				$omnify_card_color_options = [];
				$omnify_card_size_options = [];
				if (! empty($omnify_product['attributes'])) {
					foreach ($omnify_product['attributes'] as $omnify_attr) {
						$omnify_name_lower = strtolower((string) ($omnify_attr['name'] ?? ''));
						if ('color' === $omnify_name_lower || 'colour' === $omnify_name_lower) {
							$omnify_card_color_options = (array) ($omnify_attr['options'] ?? []);
						} elseif ('size' === $omnify_name_lower) {
							$omnify_card_size_options = (array) ($omnify_attr['options'] ?? []);
						}
					}
				}
				?>
				<div class="omnify-product-card omnify-product-card--premium <?php echo 'omnify-product-card--' . esc_attr($omnify_storefront_card_style); ?>" 
					data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>"
					data-product-name="<?php echo esc_attr((string) $omnify_product['name']); ?>"
					data-price="<?php echo esc_attr((string) $omnify_actual_price); ?>"
					data-onsale="<?php echo esc_attr(! empty($omnify_product['sale_price']) && (float) $omnify_product['sale_price'] < (float) $omnify_product['price'] ? '1' : '0'); ?>"
					data-stock-status="<?php echo esc_attr((string) ($omnify_product['stock_status'] ?? '')); ?>"
					data-url="<?php echo esc_url($omnify_product_url); ?>"
					data-type="<?php echo esc_attr((string) ($omnify_product['type'] ?? 'download')); ?>"
					data-search="<?php echo esc_attr($omnify_search_text); ?>"
					data-categories="<?php echo esc_attr($omnify_category_slugs); ?>"
					data-brands="<?php echo esc_attr($omnify_brand_slugs); ?>"
					data-attributes="<?php echo esc_attr(wp_json_encode($omnify_product['attributes'] ?? [])); ?>"
					data-variations="<?php echo esc_attr(wp_json_encode($omnify_variations)); ?>"
					data-is-hot="<?php echo esc_attr($omnify_is_hot ? '1' : '0'); ?>"
					data-is-new="<?php echo esc_attr($omnify_is_new ? '1' : '0'); ?>">
					
					<div class="omnify-product-card__image-container">
						<div class="omnify-product-card__image-wrap" onclick="window.location='<?php echo esc_url($omnify_product_url); ?>'" style="cursor: pointer;">
							<?php if (! empty($omnify_product['thumbnail_url'])) : ?>
								<img src="<?php echo esc_url($omnify_product['thumbnail_url']); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" class="omnify-product-card__image omnify-product-card__image--primary">
								<?php if (! empty($omnify_product['gallery_urls']) && isset($omnify_product['gallery_urls'][0])) : ?>
									<img src="<?php echo esc_url($omnify_product['gallery_urls'][0]); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" class="omnify-product-card__image omnify-product-card__image--secondary">
								<?php endif; ?>
							<?php else : ?>
								<div class="omnify-product-card__placeholder">
									<span><?php echo esc_html(substr((string) $omnify_product['name'], 0, 1)); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<div class="omnify-card-badges">
							<?php if ($omnify_discount_pct > 0) : ?>
								<span class="omnify-badge-bubble omnify-badge-bubble--sale">-<?php echo (int) $omnify_discount_pct; ?>%</span>
							<?php endif; ?>
							<?php if ($omnify_is_hot && $omnify_discount_pct == 0) : ?>
								<span class="omnify-badge-bubble omnify-badge-bubble--hot"><?php esc_html_e('HOT', 'omnifywp-ecommerce'); ?></span>
							<?php endif; ?>
							<?php if ($omnify_is_new) : ?>
								<span class="omnify-badge-bubble omnify-badge-bubble--new"><?php esc_html_e('NEW', 'omnifywp-ecommerce'); ?></span>
							<?php endif; ?>
							<?php if (!empty($omnify_product['stock_qty']) && $omnify_product['stock_qty'] < 10) : ?>
								<span class="omnify-badge-bubble" style="background:#fef3c7;color:#92400e;">Limited</span>
							<?php endif; ?>
						</div>

						<div class="omnify-product-card__hover-tools">
							<?php if ($omnify_show_wishlist) : ?>
								<button type="button" class="omnify-hover-tool-btn omnify-wishlist-button" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" aria-label="<?php esc_attr_e('Add to wishlist', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Wishlist', 'omnifywp-ecommerce'); ?>">
									<svg class="heart-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
								</button>
							<?php endif; ?>
							<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-hover-tool-btn omnify-quick-view-button" aria-label="<?php esc_attr_e('Quick view', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Quick view', 'omnifywp-ecommerce'); ?>">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 12s3.75-7 10.5-7 10.5 7 10.5 7-3.75 7-10.5 7-10.5-7-10.5-7z"/><circle cx="12" cy="12" r="3"/></svg>
							</a>
							<button type="button" class="omnify-hover-tool-btn omnify-compare-button" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" data-name="<?php echo esc_attr((string) $omnify_product['name']); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>" data-type="<?php echo esc_attr((string) ($omnify_product['type'] ?? 'download')); ?>" data-categories-label="<?php echo esc_attr(implode(', ', (array) ($omnify_product['categories'] ?? []))); ?>" data-rating="<?php echo esc_attr($omnify_avg_rating); ?>" data-reviews="<?php echo esc_attr($omnify_review_count); ?>" data-description="<?php echo esc_attr($omnify_product_compare_summary); ?>" aria-label="<?php esc_attr_e('Compare product', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Compare', 'omnifywp-ecommerce'); ?>">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4M7 4L3 8M7 4L11 8M17 8V20M17 20L13 16M17 20L21 16"/></svg>
							</button>
						</div>

						<?php if ($omnify_show_add_to_cart) : ?>
							<div class="omnify-product-card__quick-add-wrap">
								<?php if ('variable' === ($omnify_product['type'] ?? '')) : ?>
									<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-product-card__quick-add omnify-select-options-btn" aria-label="<?php esc_attr_e('Select Options', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Select Options', 'omnifywp-ecommerce'); ?>">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
									</a>
								<?php else : ?>
									<button type="button" class="omnify-product-card__quick-add omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_product['id']); ?>" data-name="<?php echo esc_attr($omnify_product['name']); ?>" data-price="<?php echo esc_attr($omnify_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>" aria-label="<?php esc_attr_e('Add to Cart', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Add to Cart', 'omnifywp-ecommerce'); ?>">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
									</button>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if (! empty($omnify_card_size_options)) : ?>
							<div class="omnify-product-card__hover-sizes" aria-label="<?php esc_attr_e('Available sizes', 'omnifywp-ecommerce'); ?>">
								<?php foreach (array_slice($omnify_card_size_options, 0, 5) as $omnify_hover_size) : ?>
									<span class="omnify-product-card__hover-size"><?php echo esc_html($omnify_hover_size); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="omnify-product-card__slide-up-action">
							<?php if ('variable' === ($omnify_product['type'] ?? '')) : ?>
								<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-btn-slide-cart omnify-select-options-btn">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
									<span><?php esc_html_e('Select Options', 'omnifywp-ecommerce'); ?></span>
								</a>
							<?php else : ?>
								<button type="button" class="omnify-btn-slide-cart omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_product['id']); ?>" data-name="<?php echo esc_attr($omnify_product['name']); ?>" data-price="<?php echo esc_attr($omnify_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
									<span><?php esc_html_e('Add to Cart', 'omnifywp-ecommerce'); ?></span>
								</button>
							<?php endif; ?>
						</div>
					</div>

					<div class="omnify-product-card__content">
						<?php if (! empty($omnify_product['categories'])) : ?>
							<div class="omnify-product-card__category-label">
								<?php echo esc_html(strtoupper(implode(', ', array_slice($omnify_product['categories'], 0, 2)))); ?>
							</div>
						<?php endif; ?>

						<h3 class="omnify-product-card__title" onclick="window.location='<?php echo esc_url($omnify_product_url); ?>'" style="cursor: pointer;">
							<?php echo esc_html($omnify_product['name']); ?>
						</h3>

						<?php if ($omnify_show_reviews_setting && $omnify_review_count > 0) : ?>
							<div class="omnify-product-card__rating">
								<span class="stars"><?php echo esc_html(str_repeat('★', (int) round($omnify_avg_rating)) . str_repeat('☆', 5 - (int) round($omnify_avg_rating))); ?></span>
								<span class="count">(<?php echo esc_html($omnify_review_count); ?>)</span>
							</div>
						<?php endif; ?>

						<div class="omnify-product-card__price">
							<?php if (! empty($omnify_product['sale_price']) && (float) $omnify_product['sale_price'] < (float) $omnify_product['price']) : ?>
								<span class="current-price sale"><?php echo esc_html($omnify_format_price((float) $omnify_product['sale_price'])); ?></span>
								<span class="original-price"><?php echo esc_html($omnify_format_price((float) $omnify_product['price'])); ?></span>
							<?php else : ?>
								<span class="current-price"><?php echo esc_html($omnify_format_price((float) $omnify_product['price'])); ?></span>
							<?php endif; ?>
						</div>

						<?php 
						// Premium-like stock and excerpt
						$omnify_stock_qty = isset($omnify_product['stock_qty']) ? (int)$omnify_product['stock_qty'] : 0;
						if ($omnify_stock_qty > 0 || ($omnify_product['stock_status'] ?? '') === 'instock') {
							echo '<div class="omnify-product-card__stock" style="font-size:11px; color:#15803d; margin-top:2px;">' . ($omnify_stock_qty ? esc_html($omnify_stock_qty) . ' in stock' : 'In stock') . '</div>';
						}
						if (!empty($omnify_product['description'])) {
							$omnify_excerpt = wp_trim_words(wp_strip_all_tags($omnify_product['description']), 10);
							echo '<div class="omnify-product-card__excerpt" style="font-size:11px; color:#64748b; margin-top:4px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">' . esc_html($omnify_excerpt) . '</div>';
						}
						?>

						<?php
						$omnify_color_options = $omnify_card_color_options;
						$omnify_size_options = $omnify_card_size_options;
						if (! empty($omnify_color_options) || ! empty($omnify_size_options)) : ?>
							<div class="omnify-product-card__swatches">
								<?php if (! empty($omnify_color_options)) : ?>
									<div class="omnify-swatch-list omnify-swatch-list--color">
										<?php foreach ($omnify_color_options as $omnify_color) : ?>
											<span class="omnify-swatch-item omnify-swatch-item--color" data-attribute="color" data-value="<?php echo esc_attr(strtolower(trim($omnify_color))); ?>" title="<?php echo esc_attr($omnify_color); ?>"></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								<?php if (! empty($omnify_size_options)) : ?>
									<div class="omnify-swatch-list omnify-swatch-list--size">
										<?php foreach ($omnify_size_options as $omnify_size) : ?>
											<span class="omnify-swatch-item omnify-swatch-item--size" data-attribute="size" data-value="<?php echo esc_attr(strtolower(trim($omnify_size))); ?>"><?php echo esc_html($omnify_size); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="omnify-product-card__footer-action">
							<?php if ('variable' === ($omnify_product['type'] ?? '')) : ?>
								<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-card-action-btn omnify-select-options-btn">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
									<span><?php esc_html_e('Add to Cart', 'omnifywp-ecommerce'); ?></span>
								</a>
							<?php else : ?>
								<button type="button" class="omnify-card-action-btn omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_product['id']); ?>" data-name="<?php echo esc_attr($omnify_product['name']); ?>" data-price="<?php echo esc_attr($omnify_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
									<span><?php esc_html_e('Add to Cart', 'omnifywp-ecommerce'); ?></span>
								</button>
							<?php endif; ?>
						</div>
					</div>

					<div class="omnify-product-card__list-panel">
						<div class="omnify-product-card__list-price">
							<?php if (! empty($omnify_product['sale_price']) && (float) $omnify_product['sale_price'] < (float) $omnify_product['price']) : ?>
								<span class="current-price sale"><?php echo esc_html($omnify_format_price((float) $omnify_product['sale_price'])); ?></span>
								<span class="original-price"><?php echo esc_html($omnify_format_price((float) $omnify_product['price'])); ?></span>
							<?php else : ?>
								<span class="current-price"><?php echo esc_html($omnify_format_price((float) $omnify_product['price'])); ?></span>
							<?php endif; ?>
						</div>
						<?php if ($omnify_stock_qty > 0 || ($omnify_product['stock_status'] ?? '') === 'instock') : ?>
							<div class="omnify-product-card__list-stock"><?php echo $omnify_stock_qty ? esc_html($omnify_stock_qty) . ' ' . esc_html__('in stock', 'omnifywp-ecommerce') : esc_html__('In stock', 'omnifywp-ecommerce'); ?></div>
						<?php endif; ?>
						<?php if ('variable' === ($omnify_product['type'] ?? '')) : ?>
							<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-product-card__list-button" aria-label="<?php esc_attr_e('Select options', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Select options', 'omnifywp-ecommerce'); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
							</a>
						<?php else : ?>
							<button type="button" class="omnify-product-card__list-button omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_product['id']); ?>" data-name="<?php echo esc_attr($omnify_product['name']); ?>" data-price="<?php echo esc_attr($omnify_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>" aria-label="<?php esc_attr_e('Add to cart', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Add to cart', 'omnifywp-ecommerce'); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
							</button>
						<?php endif; ?>
						<div class="omnify-product-card__list-links">
							<?php if ($omnify_show_wishlist) : ?>
								<button type="button" class="omnify-list-link omnify-wishlist-button" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>">
									<span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></span>
									<?php esc_html_e('Wishlist', 'omnifywp-ecommerce'); ?>
								</button>
							<?php endif; ?>
							<a href="<?php echo esc_url($omnify_product_url); ?>" class="omnify-list-link omnify-quick-view-button">
								<span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></span>
								<?php esc_html_e('Quick View', 'omnifywp-ecommerce'); ?>
							</a>
							<button type="button" class="omnify-list-link omnify-compare-button" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" data-name="<?php echo esc_attr((string) $omnify_product['name']); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_actual_price)); ?>" data-image="<?php echo esc_attr($omnify_product['thumbnail_url'] ?? ''); ?>" data-url="<?php echo esc_url($omnify_product_url); ?>" data-type="<?php echo esc_attr((string) ($omnify_product['type'] ?? 'download')); ?>" data-categories-label="<?php echo esc_attr(implode(', ', (array) ($omnify_product['categories'] ?? []))); ?>" data-rating="<?php echo esc_attr($omnify_avg_rating); ?>" data-reviews="<?php echo esc_attr($omnify_review_count); ?>" data-description="<?php echo esc_attr($omnify_product_compare_summary); ?>">
								<span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M7 16V4M7 4L3 8M7 4L11 8M17 8V20M17 20L13 16M17 20L21 16"/></svg></span>
								<?php esc_html_e('Compare', 'omnifywp-ecommerce'); ?>
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php if (count($omnify_products) > $omnify_initial_count) : ?>
		<div class="omnify-load-more-wrapper" style="text-align: center; margin-top: 32px;">
			<button id="omnify-load-more-btn" class="omnify-btn omnify-btn--secondary" style="min-width: 180px; padding: 11px 28px; font-size: 13px; font-weight: 600; letter-spacing: .5px; border: 1px solid #cbd5e1; background: #fff; color: #475569;">
				LOAD MORE ...
			</button>
		</div>
	<?php endif; ?>

	<div id="omnify-storefront-empty-filter" class="omnify-storefront-empty" style="display: none;">
		<p><?php esc_html_e('No products match your current search or filters.', 'omnifywp-ecommerce'); ?></p>
	</div>
		</section>
			</div> <!-- .omnify-storefront-content -->
		</div> <!-- .omnify-storefront-layout-grid -->

		<!-- Footer Reassurance Row -->
		<div class="omnify-catalog-footer-reassurance">
			<div class="omnify-reassurance-item">
				<div class="omnify-reassurance-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				</div>
				<div class="omnify-reassurance-info">
					<h4><?php esc_html_e('Secure Payments', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('100% secure & trusted payments', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-reassurance-item">
				<div class="omnify-reassurance-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
				</div>
				<div class="omnify-reassurance-info">
					<h4><?php esc_html_e('Instant Access', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('Get your products instantly', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-reassurance-item">
				<div class="omnify-reassurance-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				</div>
				<div class="omnify-reassurance-info">
					<h4><?php esc_html_e('Money Back Guarantee', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('7-day refund policy', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-reassurance-item">
				<div class="omnify-reassurance-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
				</div>
				<div class="omnify-reassurance-info">
					<h4><?php esc_html_e('Top Rated Support', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('We\'re here to help', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
		</div>

		<!-- Newsletter block -->
		<div class="omnify-catalog-newsletter">
			<div class="omnify-newsletter-left">
				<div class="omnify-newsletter-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
				</div>
				<div class="omnify-newsletter-text">
					<h3><?php esc_html_e('Stay updated with our latest products and offers', 'omnifywp-ecommerce'); ?></h3>
					<p><?php esc_html_e('Subscribe to our newsletter and never miss a deal.', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-newsletter-right">
				<form class="omnify-newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed!');">
					<input type="email" placeholder="<?php esc_attr_e('Enter your email address', 'omnifywp-ecommerce'); ?>" required>
					<button type="submit"><?php esc_html_e('Subscribe', 'omnifywp-ecommerce'); ?></button>
				</form>
			</div>
		</div>
	</div>

	<!-- Load More and Swatches are handled via storefront.js -->

	<!-- Slide-over Checkout Modal -->
	<div id="omnify-checkout-overlay" class="omnify-checkout-overlay" style="display: none;">
		<div class="omnify-checkout-pane">
			<div class="omnify-checkout-pane__header">
				<h2><?php esc_html_e('Checkout', 'omnifywp-ecommerce'); ?></h2>
				<button id="omnify-checkout-close" class="omnify-checkout-pane__close" aria-label="<?php esc_attr_e('Close panel', 'omnifywp-ecommerce'); ?>">&times;</button>
			</div>

			<div class="omnify-checkout-pane__body">
				<!-- Product Summary -->
				<div class="omnify-checkout-summary" style="display: flex; flex-direction: column; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid var(--omnify-gray-200); margin-bottom: 16px;">
					<div class="omnify-checkout-summary__details">
						<span class="omnify-checkout-summary__label" style="font-size: 11px; text-transform: uppercase; color: var(--omnify-gray-400); font-weight: 600;"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></span>
						<h4 id="omnify-checkout-product-name" class="omnify-checkout-summary__name" style="margin: 4px 0 0 0; font-size: 16px; font-weight: 600; color: var(--omnify-dark);">Product Title</h4>
					</div>
					<div class="omnify-checkout-summary__breakdown" style="display: grid; gap: 6px; font-size: 13px; color: var(--omnify-gray-700);">
						<div style="display: flex; justify-content: space-between;">
							<span><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></span>
							<strong id="omnify-checkout-price-raw">USD 0.00</strong>
						</div>
						<div id="omnify-checkout-tax-row" style="display: flex; justify-content: space-between; display: none;">
							<span><?php esc_html_e('Tax', 'omnifywp-ecommerce'); ?></span>
							<strong id="omnify-checkout-tax-amount">USD 0.00</strong>
						</div>
						<div style="display: flex; justify-content: space-between; border-top: 1px solid var(--omnify-gray-200); padding-top: 8px; margin-top: 4px; font-size: 15px; color: var(--omnify-dark); font-weight: 500;">
							<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></span>
							<span id="omnify-checkout-total-price">USD 0.00</span>
						</div>
					</div>
				</div>

				<!-- Form Checkout -->
				<form id="omnify-checkout-form" class="omnify-checkout-form">
					<input type="hidden" id="omnify-checkout-product-id" name="product_id" value="">

					<div class="omnify-form-group">
						<label for="omnify-email"><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?> <span class="required">*</span></label>
						<input type="email" id="omnify-email" name="email" required placeholder="name@example.com">
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="omnify-firstname"><?php esc_html_e('First Name', 'omnifywp-ecommerce'); ?> <span class="required">*</span></label>
							<input type="text" id="omnify-firstname" name="first_name" required placeholder="John">
						</div>
						<div class="omnify-form-group">
							<label for="omnify-lastname"><?php esc_html_e('Last Name', 'omnifywp-ecommerce'); ?> <span class="required">*</span></label>
							<input type="text" id="omnify-lastname" name="last_name" required placeholder="Doe">
						</div>
					</div>

					<div class="omnify-form-group">
						<label for="omnify-phone"><?php esc_html_e('Phone Number', 'omnifywp-ecommerce'); ?></label>
						<input type="tel" id="omnify-phone" name="phone" placeholder="+1 (555) 000-0000">
					</div>

					<!-- Mock Card Inputs -->
					<div class="omnify-checkout-card-section">
						<h4><?php esc_html_e('Payment Details', 'omnifywp-ecommerce'); ?></h4>
						<div class="omnify-form-group">
							<label for="omnify-card-number"><?php esc_html_e('Card Number', 'omnifywp-ecommerce'); ?></label>
							<input type="text" id="omnify-card-number" required placeholder="4111 2222 3333 4444" max-length="19">
						</div>
						<div class="omnify-form-row">
							<div class="omnify-form-group">
								<label for="omnify-card-expiry"><?php esc_html_e('Expiry', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="omnify-card-expiry" required placeholder="MM/YY" max-length="5">
							</div>
							<div class="omnify-form-group">
								<label for="omnify-card-cvc"><?php esc_html_e('CVC', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="omnify-card-cvc" required placeholder="123" max-length="3">
							</div>
						</div>
					</div>

					<div id="omnify-checkout-error" class="omnify-checkout-error" style="display: none;"></div>

					<button type="submit" id="omnify-checkout-submit" class="omnify-btn omnify-btn--primary omnify-btn--block">
						<span class="btn-text"><?php esc_html_e('Complete Purchase', 'omnifywp-ecommerce'); ?></span>
						<span class="btn-spinner" style="display: none;"></span>
					</button>
				</form>

				<!-- Success State -->
				<div id="omnify-checkout-success" class="omnify-checkout-success" style="display: none;">
					<div class="omnify-checkout-success__icon">✓</div>
					<h3><?php esc_html_e('Purchase Successful!', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-checkout-success__msg"><?php esc_html_e('Thank you! Your order has been processed successfully.', 'omnifywp-ecommerce'); ?></p>
					
					<div class="omnify-order-box">
						<span class="omnify-order-box__number"><?php esc_html_e('Order ID:', 'omnifywp-ecommerce'); ?> <strong id="omnify-success-order-id">#0</strong></span>
					</div>

					<div class="omnify-success-downloads">
						<h4><?php esc_html_e('Your Files:', 'omnifywp-ecommerce'); ?></h4>
						<div id="omnify-success-files-list" class="omnify-success-files-list">
							<!-- Populated via JS -->
						</div>
					</div>

					<p class="omnify-portal-redirect-hint">
						<?php esc_html_e('You can view your full download history inside the customer portal.', 'omnifywp-ecommerce'); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
