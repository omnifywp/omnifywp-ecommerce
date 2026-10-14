<?php
/**
 * Storefront Single Product Details Template – Elessi Redesign.
 *
 * Features:
 * - 3-Column Elessi-Inspired Layout:
 *   - Column 1: Brand, Title, Rating stars, Price, Gallery thumbnails.
 *   - Column 2: Large main gallery image with floating Wishlist/Compare actions.
 *   - Column 3: Sold count urgency badge, Short description, Swatches,
 *               Qty controls + Gold Add to Cart + Green Buy Now,
 *               Reassurance links, Trust & Refund policy.
 * - Dashicons for all icons.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
foreach ([
	'omnify_about_features',
	'omnify_all_images',
	'omnify_allows_oversell',
	'omnify_attr_config',
	'omnify_attr_name',
	'omnify_attr_option_meta',
	'omnify_attr_type',
	'omnify_attr_val',
	'omnify_attr_vals',
	'omnify_attribute_map',
	'omnify_attribute_meta_map',
	'omnify_availability_message',
	'omnify_avg_rating',
	'omnify_brand',
	'omnify_breakdown',
	'omnify_breakdown_class',
	'omnify_button_class',
	'omnify_buy_button_label',
	'omnify_buy_url',
	'omnify_cart_button_label',
	'omnify_cat',
	'omnify_checkout_pages',
	'omnify_checkout_url',
	'omnify_cnt',
	'omnify_color',
	'omnify_current_user',
	'omnify_current_user_review',
	'omnify_default_trust_text',
	'omnify_default_trust_title',
	'omnify_del',
	'omnify_delivery_zones',
	'omnify_display_price',
	'omnify_feat',
	'omnify_format_price',
	'omnify_formatted_display',
	'omnify_formatted_regular',
	'omnify_full_stars',
	'omnify_gallery_slice',
	'omnify_gallery_urls',
	'omnify_gidx',
	'omnify_gurl',
	'omnify_half_star',
	'omnify_has_delivery_details',
	'omnify_has_delivery_setup',
	'omnify_has_gallery',
	'omnify_has_review_images',
	'omnify_has_specs',
	'omnify_has_video',
	'omnify_i',
	'omnify_image_url',
	'omnify_img',
	'omnify_img_url',
	'omnify_is_editing_review',
	'omnify_is_even',
	'omnify_is_out_of_stock_simple',
	'omnify_is_physical',
	'omnify_is_variable',
	'omnify_is_variation_in_stock',
	'omnify_items_table_exists',
	'omnify_manage_stock',
	'omnify_max_purchase_qty',
	'omnify_open_reviews_tab',
	'omnify_option_is_in_stock',
	'omnify_option_meta',
	'omnify_option_on_sale',
	'omnify_orders_table_exists',
	'omnify_out_of_stock_behavior',
	'omnify_pct',
	'omnify_product',
	'omnify_product_attr',
	'omnify_product_compare_summary',
	'omnify_r',
	'omnify_r_img',
	'omnify_refund_policy_text',
	'omnify_refund_window_days',
	'omnify_refunds_enabled',
	'omnify_regular_price',
	'omnify_related_products',
	'omnify_ret',
	'omnify_rev',
	'omnify_review',
	'omnify_review_action_url',
	'omnify_review_count',
	'omnify_review_form_content',
	'omnify_review_form_email',
	'omnify_review_form_name',
	'omnify_review_form_rating',
	'omnify_review_images',
	'omnify_review_nonce',
	'omnify_review_notice',
	'omnify_reviews',
	'omnify_reviews_repo',
	'omnify_rp',
	'omnify_rp_formatted',
	'omnify_rp_url',
	'omnify_rt',
	'omnify_sale_price',
	'omnify_save_pct',
	'omnify_settings',
	'omnify_sold_recent',
	'omnify_spec',
	'omnify_specs',
	'omnify_star',
	'omnify_stock_qty',
	'omnify_stock_status',
	'omnify_storefront_page_id',
	'omnify_storefront_pages',
	'omnify_storefront_title',
	'omnify_storefront_url',
	'omnify_swatch_shape',
	'omnify_swatch_size',
	'omnify_table_items',
	'omnify_table_orders',
	'omnify_tag',
	'omnify_tidx',
	'omnify_total_reviews',
	'omnify_trust_badge_text',
	'omnify_trust_badge_title',
	'omnify_turl',
	'omnify_up',
	'omnify_up_formatted',
	'omnify_up_url',
	'omnify_upsell_products',
	'omnify_use_dropdown_variations',
	'omnify_v',
	'omnify_val',
	'omnify_vals',
	'omnify_variation',
	'omnify_variation_for_stock',
	'omnify_variation_selector_style',
	'omnify_variation_settings',
	'omnify_variations',
	'omnify_variations_json',
	'omnify_video_poster_url',
	'omnify_video_url',
] as $omnify_expected_var) {
	if (! isset($$omnify_expected_var)) {
		$$omnify_expected_var = $omnify_template_vars[$omnify_expected_var] ?? null;
	}
}

// ─── Resolve Storefront URLs ──────────────────────────────────────────────────
$omnify_storefront_url = home_url('/');
$omnify_storefront_pages = get_posts(['post_type' => 'page', 's' => '[omnify_storefront]', 'post_status' => 'publish']);
if (! empty($omnify_storefront_pages)) {
	$omnify_storefront_url = get_permalink($omnify_storefront_pages[0]->ID);
}

$omnify_settings = isset($omnify_settings) ? $omnify_settings : get_option('omnify_settings', []);
$omnify_storefront_page_id = ! empty($omnify_settings['page_storefront']) ? absint($omnify_settings['page_storefront']) : 0;
$omnify_show_discount_badge = ! array_key_exists('storefront_show_discount_badge', $omnify_settings) || ! empty($omnify_settings['storefront_show_discount_badge']);
$omnify_show_reassurance = ! array_key_exists('storefront_show_reassurance', $omnify_settings) || ! empty($omnify_settings['storefront_show_reassurance']);
$omnify_show_compare = ! array_key_exists('storefront_show_compare', $omnify_settings) || ! empty($omnify_settings['storefront_show_compare']);
$omnify_show_recently_viewed = ! array_key_exists('storefront_show_recently_viewed', $omnify_settings) || ! empty($omnify_settings['storefront_show_recently_viewed']);
$omnify_recently_viewed_count = max(1, min(20, absint($omnify_settings['storefront_recently_viewed_count'] ?? 5)));
$omnify_infinite_scroll = ! array_key_exists('storefront_infinite_scroll', $omnify_settings) || ! empty($omnify_settings['storefront_infinite_scroll']);
$omnify_storefront_card_style = $omnify_settings['storefront_card_style'] ?? 'standard';
$omnify_show_add_to_cart = ! array_key_exists('storefront_show_add_to_cart', $omnify_settings) || ! empty($omnify_settings['storefront_show_add_to_cart']);
$omnify_reassurance_1_title = $omnify_settings['storefront_reassurance_1_title'] ?? 'Secure Checkout';
$omnify_reassurance_1_desc = $omnify_settings['storefront_reassurance_1_desc'] ?? 'Your data is protected';
$omnify_reassurance_2_title = $omnify_settings['storefront_reassurance_2_title'] ?? 'Instant Download';
$omnify_reassurance_2_desc = $omnify_settings['storefront_reassurance_2_desc'] ?? 'Get access immediately';
$omnify_reassurance_3_title = $omnify_settings['storefront_reassurance_3_title'] ?? '24/7 Support';
$omnify_reassurance_3_desc = $omnify_settings['storefront_reassurance_3_desc'] ?? "We're here to help";
if (! $omnify_storefront_page_id && ! empty($omnify_storefront_pages)) {
	$omnify_storefront_page_id = $omnify_storefront_pages[0]->ID;
}
$omnify_storefront_title = $omnify_storefront_page_id ? get_the_title($omnify_storefront_page_id) : __('Shop', 'omnifywp-ecommerce');
if (empty($omnify_storefront_title)) {
	$omnify_storefront_title = __('Shop', 'omnifywp-ecommerce');
}

$omnify_checkout_url = home_url('/checkout/');
$omnify_checkout_pages = get_posts(['post_type' => 'page', 's' => '[omnify_checkout]', 'post_status' => 'publish']);
if (! empty($omnify_checkout_pages)) {
	$omnify_checkout_url = get_permalink($omnify_checkout_pages[0]->ID);
}

$omnify_buy_url = add_query_arg('product_id', $omnify_product['id'], $omnify_checkout_url);

$omnify_prod_repo = new \Omnify\eCommerce\Repositories\Omnify_Product_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
$omnify_related_products = $omnify_prod_repo->get_related_products((int) $omnify_product['id'], 4);

// ─── Product Basic Flags & Meta ───────────────────────────────────────────────
$omnify_is_variable = ! empty($omnify_product['type']) && 'variable' === $omnify_product['type'];
$omnify_variations = ! empty($omnify_product['variations']) && is_array($omnify_product['variations']) ? $omnify_product['variations'] : [];
if ($omnify_is_variable && empty($omnify_variations) && ! empty($omnify_product['id'])) {
	$omnify_variations = $omnify_prod_repo->get_variations((int) $omnify_product['id']);
}
$omnify_variations_json = wp_json_encode($omnify_variations);

$omnify_variation_settings = wp_parse_args(
	$omnify_product['variation_settings'] ?? [],
	[
		'product_kind'   => 'digital',
		'selector_style' => 'buttons',
		'swatch_shape'   => 'round',
		'swatch_size'    => 'medium',
		'out_of_stock'   => 'cross',
	]
);
$omnify_variation_selector_style = sanitize_key((string) ($omnify_variation_settings['selector_style'] ?? 'buttons'));
$omnify_use_dropdown_variations = 'dropdowns' === $omnify_variation_selector_style;
$omnify_swatch_shape = sanitize_key((string) ($omnify_variation_settings['swatch_shape'] ?? 'round'));
$omnify_swatch_size = sanitize_key((string) ($omnify_variation_settings['swatch_size'] ?? 'medium'));
$omnify_out_of_stock_behavior = sanitize_key((string) ($omnify_variation_settings['out_of_stock'] ?? 'cross'));

$omnify_is_physical = ($omnify_product['type'] === 'physical') || ($omnify_is_variable && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
$omnify_gallery_urls = $omnify_product['gallery_urls'] ?? [];
$omnify_video_url = $omnify_product['video_url'] ?? '';
$omnify_video_poster_url = $omnify_product['video_poster_url'] ?? '';
$omnify_has_video = ! empty($omnify_video_url);

// Build full image list (featured first, then gallery)
$omnify_all_images = [];
if (! empty($omnify_product['thumbnail_url'])) {
	$omnify_all_images[] = $omnify_product['thumbnail_url'];
}
foreach ($omnify_gallery_urls as $omnify_gurl) {
	if ($omnify_gurl !== ($omnify_product['thumbnail_url'] ?? '')) {
		$omnify_all_images[] = $omnify_gurl;
	}
}

// Fallback high-resolution mockup image if no image was uploaded
if (empty($omnify_all_images)) {
	$omnify_pname_lower = strtolower((string) ($omnify_product['name'] ?? ''));
	$omnify_assets_img = defined('OMNIFY_URL') ? OMNIFY_URL . 'assets/storefront/images/' : plugins_url('assets/storefront/images/', dirname(__DIR__, 1) . '/dummy.php');
	if (strpos($omnify_pname_lower, 'hoodie') !== false || strpos($omnify_pname_lower, 'shirt') !== false || strpos($omnify_pname_lower, 'merch') !== false || strpos($omnify_pname_lower, 't-shirt') !== false || strpos($omnify_pname_lower, 'cotton') !== false || strpos($omnify_pname_lower, 'apparel') !== false || 'physical' === strtolower((string) ($omnify_product['type'] ?? ''))) {
		$omnify_all_images[] = $omnify_assets_img . 'product-merch-hoodie.jpg';
	} elseif (strpos($omnify_pname_lower, 'bundle') !== false || strpos($omnify_pname_lower, 'kit') !== false || strpos($omnify_pname_lower, 'pack') !== false || 'bundle' === strtolower((string) ($omnify_product['type'] ?? ''))) {
		$omnify_all_images[] = $omnify_assets_img . 'product-bundle-kit.jpg';
	} else {
		$omnify_all_images[] = $omnify_assets_img . 'product-digital-suite.jpg';
	}
}
$omnify_has_gallery = ! empty($omnify_all_images);

// Reviews retrieval via Repository
$omnify_reviews_repo = isset($omnify_reviews_repo) ? $omnify_reviews_repo : null;
$omnify_reviews = ($omnify_reviews_repo && method_exists($omnify_reviews_repo, 'all_for_product')) ? $omnify_reviews_repo->all_for_product((int) $omnify_product['id']) : [];
$omnify_review_count = count($omnify_reviews);
$omnify_avg_rating = 0;
if ($omnify_review_count > 0) {
	$omnify_avg_rating = round(array_sum(array_column($omnify_reviews, 'rating')) / $omnify_review_count, 1);
}
$omnify_review_nonce = wp_create_nonce('omnify_submit_review');
$omnify_review_notice = isset($_GET['review']) ? sanitize_key(wp_unslash($_GET['review'])) : '';
$omnify_open_reviews_tab = in_array($omnify_review_notice, ['submitted', 'updated', 'no_rating'], true);
$omnify_review_action_url = admin_url('admin-post.php');

// Check purchase status and prefill details for reviews
$omnify_current_user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
$omnify_user_has_purchased = false;
$omnify_review_form_name = '';
$omnify_review_form_email = '';
if ($omnify_current_user_email) {
	$omnify_current_user = wp_get_current_user();
	$omnify_review_form_name = trim($omnify_current_user->first_name . ' ' . $omnify_current_user->last_name);
	if (empty($omnify_review_form_name)) {
		$omnify_review_form_name = $omnify_current_user->display_name;
	}
	$omnify_review_form_email = $omnify_current_user_email;

	$omnify_schema_instance = new \Omnify\eCommerce\Database\Omnify_Schema();
	$omnify_cust_repo = new \Omnify\eCommerce\Repositories\Omnify_Customer_Repository($omnify_schema_instance);
	$omnify_access_repo = new \Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository(
		$omnify_schema_instance,
		$omnify_cust_repo,
		new \Omnify\eCommerce\Repositories\Omnify_Product_Repository($omnify_schema_instance),
		new \Omnify\eCommerce\Repositories\Omnify_Product_File_Repository($omnify_schema_instance)
	);
	$omnify_cust_record = $omnify_cust_repo->find_by_email($omnify_current_user_email);
	if ($omnify_cust_record) {
		$omnify_user_has_purchased = $omnify_access_repo->has_access((int) $omnify_cust_record['id'], (int) $omnify_product['id']);
	}

	// Check if the user has already left a review to prefill form and decide gate visibility
	$omnify_user_has_reviewed = false;
	$omnify_my_review = null;
	$omnify_my_rating = 0;
	$omnify_my_review_id = 0;
	$omnify_review_form_content = '';

	if (! empty($omnify_reviews)) {
		foreach ($omnify_reviews as $omnify_rev) {
			if (sanitize_email($omnify_rev['customer_email'] ?? '') === sanitize_email($omnify_current_user_email)) {
				$omnify_user_has_reviewed = true;
				$omnify_my_review = $omnify_rev;
				$omnify_my_rating = intval($omnify_rev['rating'] ?? 0);
				$omnify_my_review_id = intval($omnify_rev['id'] ?? 0);
				$omnify_review_form_content = $omnify_rev['content'] ?? '';
				break;
			}
		}
	}
}

// Attribute Map building from canonical attributes and variations
$omnify_attribute_map = [];
if (! empty($omnify_product['attributes']) && is_array($omnify_product['attributes'])) {
	foreach ($omnify_product['attributes'] as $omnify_p_attr) {
		$omnify_attr_name = (string) ($omnify_p_attr['name'] ?? '');
		if ('' === $omnify_attr_name) continue;
		$omnify_attribute_map[$omnify_attr_name] = ! empty($omnify_p_attr['options']) && is_array($omnify_p_attr['options']) ? array_values(array_unique($omnify_p_attr['options'])) : [];
	}
}
foreach ($omnify_variations as $omnify_v) {
	foreach (($omnify_v['attributes'] ?? []) as $omnify_attr_name => $omnify_attr_val) {
		if (! isset($omnify_attribute_map[$omnify_attr_name])) {
			$omnify_attribute_map[$omnify_attr_name] = [];
		}
		if (! in_array($omnify_attr_val, $omnify_attribute_map[$omnify_attr_name], true)) {
			$omnify_attribute_map[$omnify_attr_name][] = $omnify_attr_val;
		}
	}
}
foreach ($omnify_attribute_map as &$omnify_vals) {
	$omnify_vals = array_values(array_unique($omnify_vals));
}
unset($omnify_vals);

// Swatch Configuration Meta mapping
$omnify_attribute_meta_map = [];
foreach (($omnify_product['attributes'] ?? []) as $omnify_product_attr) {
	$omnify_attr_name = (string) ($omnify_product_attr['name'] ?? '');
	if ('' === $omnify_attr_name) {
		continue;
	}
	$omnify_attr_type = sanitize_key($omnify_product_attr['type'] ?? '');
	if (! in_array($omnify_attr_type, ['button', 'dropdown', 'color', 'image'], true)) {
		$omnify_attr_type = 'dropdowns' === $omnify_variation_selector_style ? 'dropdown' : 'button';
	}
	$omnify_attribute_meta_map[$omnify_attr_name] = [
		'type'        => $omnify_attr_type,
		'option_meta' => is_array($omnify_product_attr['option_meta'] ?? null) ? $omnify_product_attr['option_meta'] : [],
	];
}

// Specifications table rows
$omnify_specs_rows = [];
if (! empty($omnify_product['specifications']) && is_array($omnify_product['specifications'])) {
	foreach ($omnify_product['specifications'] as $omnify_spec) {
		if (empty($omnify_spec['key'])) continue;
		$omnify_specs_rows[] = [
			'key'   => $omnify_spec['key'],
			'value' => $omnify_spec['value'] ?? '',
		];
	}
}
if (empty($omnify_specs_rows)) {
	if (! empty($omnify_product['sku'])) {
		$omnify_specs_rows[] = ['key' => __('SKU', 'omnifywp-ecommerce'), 'value' => (string) $omnify_product['sku']];
	}
	if (! empty($omnify_product['brands']) && is_array($omnify_product['brands'])) {
		$omnify_specs_rows[] = ['key' => __('Brands', 'omnifywp-ecommerce'), 'value' => implode(', ', $omnify_product['brands'])];
	}
	if (! empty($omnify_product['categories']) && is_array($omnify_product['categories'])) {
		$omnify_specs_rows[] = ['key' => __('Categories', 'omnifywp-ecommerce'), 'value' => implode(', ', $omnify_product['categories'])];
	}
}
$omnify_has_specs = ! empty($omnify_specs_rows);
$omnify_has_description = ! empty($omnify_product['description']);

$omnify_stock_status = $omnify_product['stock_status'] ?? 'instock';
$omnify_max_purchase_qty = intval($omnify_product['max_purchase_qty'] ?? -1);

$omnify_sale_price = $omnify_product['sale_price'] ?? null;
$omnify_regular_price = $omnify_product['price'] ?? 0.0;
$omnify_display_price = $omnify_sale_price !== null ? $omnify_sale_price : $omnify_regular_price;

$omnify_formatted_display = $omnify_format_price($omnify_display_price);
$omnify_formatted_regular = $omnify_format_price($omnify_regular_price);

$omnify_product_compare_summary = trim(wp_strip_all_tags((string) ($omnify_product['short_description'] ?? '')));

$omnify_default_trust_title = $omnify_is_physical ? __('Secure Delivery', 'omnifywp-ecommerce') : __('Instant Secure Access', 'omnifywp-ecommerce');
$omnify_default_trust_text  = $omnify_is_physical
	? __('Order confirmed. Shipped fast with tracking link sent to your email.', 'omnifywp-ecommerce')
	: __('Files available for download immediately after payment. Secure links emailed instantly.', 'omnifywp-ecommerce');
$omnify_trust_badge_title = trim((string) ($omnify_product['trust_badge_title'] ?? ''));
$omnify_trust_badge_text  = trim((string) ($omnify_product['trust_badge_text'] ?? ''));
$omnify_trust_badge_title = '' !== $omnify_trust_badge_title ? $omnify_trust_badge_title : $omnify_default_trust_title;
$omnify_trust_badge_text  = '' !== $omnify_trust_badge_text ? $omnify_trust_badge_text : $omnify_default_trust_text;

$omnify_refunds_enabled = ! empty($omnify_settings['enable_refunds']) && ! empty($omnify_product['refund_enabled']);
$omnify_refund_window_days = ! empty($omnify_product['refund_window_days']) ? absint($omnify_product['refund_window_days']) : max(1, absint($omnify_settings['refund_duration'] ?? 14));
$omnify_refund_policy_text = trim((string) ($omnify_product['refund_policy_text'] ?? ''));

$omnify_raw_delivery_info = trim((string) ($omnify_product['delivery_info'] ?? ''));
$omnify_raw_return_info   = trim((string) ($omnify_product['return_info'] ?? ''));
$omnify_delivery_info     = '' !== $omnify_raw_delivery_info ? $omnify_raw_delivery_info : ($omnify_settings['design_delivery_info'] ?? '');
$omnify_return_info       = '' !== $omnify_raw_return_info ? $omnify_raw_return_info : ($omnify_settings['design_return_info'] ?? '');

$omnify_delivery_display_text = trim((string) $omnify_delivery_info);
$omnify_return_display_text   = trim((string) $omnify_return_info);
$omnify_refund_display_text   = trim($omnify_refund_policy_text);
$omnify_trust_display_text    = trim($omnify_trust_badge_text);
$omnify_has_delivery_tab      = '' !== $omnify_delivery_display_text
	|| '' !== $omnify_return_display_text
	|| '' !== $omnify_refund_display_text
	|| $omnify_refunds_enabled
	|| '' !== $omnify_trust_display_text;

$omnify_active_tab = $omnify_open_reviews_tab ? 'reviews' : ($omnify_has_description ? 'description' : ($omnify_has_specs ? 'specifications' : ($omnify_has_delivery_tab ? 'delivery-return' : 'reviews')));
?>

<div class="omnify-storefront-wrapper">
	<div class="omnify-pdp-wrapper" data-is-variable="<?php echo $omnify_is_variable ? '1' : '0'; ?>" data-variations="<?php echo esc_attr($omnify_variations_json); ?>" data-product-id="<?php echo esc_attr($omnify_product['id']); ?>" data-infinite-scroll="<?php echo $omnify_infinite_scroll ? '1' : '0'; ?>">

		<!-- Breadcrumb -->
		<nav class="omnify-pdp-breadcrumb" aria-label="breadcrumb">
			<a href="<?php echo esc_url($omnify_storefront_url); ?>"><?php echo esc_html($omnify_storefront_title); ?></a>
			<span class="dashicons dashicons-arrow-right-alt2" style="font-size:12px; width:12px; height:12px; color:var(--omnify-gray-400);"></span>
			<?php if (! empty($omnify_product['categories'])) : ?>
				<a href="<?php echo esc_url(add_query_arg('category', $omnify_product['categories'][0], $omnify_storefront_url)); ?>"><?php echo esc_html($omnify_product['categories'][0]); ?></a>
				<span class="dashicons dashicons-arrow-right-alt2" style="font-size:12px; width:12px; height:12px; color:var(--omnify-gray-400);"></span>
			<?php endif; ?>
			<span class="omnify-breadcrumb-current"><?php echo esc_html($omnify_product['name']); ?></span>
		</nav>

		<!-- Product 2-Column Columns Grid (Slick Minimal Layout) -->
		<div class="omnify-pdp-grid">

			<!-- COLUMN 1: LEFT GALLERY -->
			<div class="omnify-gallery-container">
				
				<!-- Floating Wishlist & Compare buttons (Clean Minimal SVGs) -->
				<div class="omnify-pdp-floating-actions">
					<button type="button" class="omnify-wishlist-button" data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>" title="<?php esc_attr_e('Save to wishlist', 'omnifywp-ecommerce'); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
					</button>
					<?php if ($omnify_show_compare) : ?>
						<button type="button" class="omnify-compare-button"
							data-product-id="<?php echo esc_attr((string) $omnify_product['id']); ?>"
							data-name="<?php echo esc_attr((string) $omnify_product['name']); ?>"
							data-display-price="<?php echo esc_attr($omnify_formatted_display); ?>"
							data-type="<?php echo esc_attr((string) ($omnify_product['type'] ?? 'download')); ?>"
							data-image="<?php echo esc_attr((string) (! empty($omnify_product['thumbnail_url']) ? $omnify_product['thumbnail_url'] : ($omnify_all_images[0] ?? ''))); ?>"
							data-url="<?php echo esc_url(add_query_arg('omnify_product', $omnify_product['slug'], $omnify_storefront_url)); ?>"
							data-categories-label="<?php echo esc_attr(implode(', ', (array) ($omnify_product['categories'] ?? []))); ?>"
							data-rating="<?php echo esc_attr((string) round((float) $omnify_avg_rating, 1)); ?>"
							data-reviews="<?php echo esc_attr((string) $omnify_review_count); ?>"
							data-description="<?php echo esc_attr($omnify_product_compare_summary); ?>"
							title="<?php esc_attr_e('Compare', 'omnifywp-ecommerce'); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>
						</button>
					<?php endif; ?>
				</div>

				<!-- Main image viewer -->
				<div class="omnify-gallery-main" id="pdp-main-image-box" style="position: relative;">

					<?php if ($omnify_has_video && ! empty($omnify_video_url)) : ?>
						<button type="button" class="omnify-gallery-video-toggle" id="pdp-video-play-btn" data-video-url="<?php echo esc_url($omnify_video_url); ?>" aria-label="<?php esc_attr_e('Play Video', 'omnifywp-ecommerce'); ?>">
							<span class="dashicons dashicons-controls-play"></span>
						</button>
					<?php endif; ?>
					
					<?php if (! empty($omnify_all_images)) : ?>
						<img src="<?php echo esc_url($omnify_all_images[0]); ?>" id="pdp-main-img-el" alt="<?php echo esc_attr($omnify_product['name']); ?>" />
					<?php else : ?>
						<div class="omnify-gallery-placeholder">
							<span class="dashicons dashicons-format-image" style="font-size:48px; width:48px; height:48px; color:var(--omnify-gray-300);"></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($omnify_has_gallery && (count($omnify_all_images) > 1 || $omnify_has_video)) : ?>
					<div class="omnify-gallery-thumbs" aria-label="<?php esc_attr_e('Product gallery thumbnails', 'omnifywp-ecommerce'); ?>">
						<?php foreach ($omnify_all_images as $omnify_gidx => $omnify_gurl) : ?>
							<button type="button" class="omnify-gallery-thumb-item <?php echo 0 === $omnify_gidx ? 'active' : ''; ?>" data-index="<?php echo esc_attr((string) $omnify_gidx); ?>" data-full-url="<?php echo esc_url($omnify_gurl); ?>" aria-label="<?php echo esc_attr(sprintf(__('View gallery image %d', 'omnifywp-ecommerce'), $omnify_gidx + 1)); ?>">
								<img src="<?php echo esc_url($omnify_gurl); ?>" alt="<?php echo esc_attr(sprintf(__('Gallery image %d', 'omnifywp-ecommerce'), $omnify_gidx + 1)); ?>" />
							</button>
						<?php endforeach; ?>

						<?php if ($omnify_has_video && ! empty($omnify_video_url)) : ?>
							<button type="button" class="omnify-gallery-thumb-video" id="pdp-thumb-video-btn" data-video-url="<?php echo esc_url($omnify_video_url); ?>" aria-label="<?php esc_attr_e('Watch product video', 'omnifywp-ecommerce'); ?>">
								<?php if (! empty($omnify_video_poster_url)) : ?>
									<img src="<?php echo esc_url($omnify_video_poster_url); ?>" alt="<?php esc_attr_e('Product video preview', 'omnifywp-ecommerce'); ?>" />
									<span class="dashicons dashicons-controls-play"></span>
								<?php else : ?>
									<span class="dashicons dashicons-video-alt3"></span>
								<?php endif; ?>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- COLUMN 2: UNIFIED PRODUCT SUMMARY & PURCHASE FORM -->
			<div class="omnify-pdp-summary-column">
				
				<!-- Header Section (Brand, Title, Ratings) -->
				<div class="omnify-pdp-header">
					<?php if (! empty($omnify_product['brands'][0])) : ?>
						<span class="omnify-pdp-brand-tag"><?php echo esc_html($omnify_product['brands'][0]); ?></span>
					<?php endif; ?>
					
					<h1 class="omnify-pdp-title"><?php echo esc_html($omnify_product['name']); ?></h1>

					<!-- Ratings Summary Row -->
					<?php if ($omnify_review_count > 0) : ?>
						<div class="omnify-pdp-rating-row">
							<span class="omnify-stars">
								<?php
								$omnify_full_stars = floor($omnify_avg_rating);
								$omnify_half_star  = ($omnify_avg_rating - $omnify_full_stars) >= 0.5;
								for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) {
									if ($omnify_i <= $omnify_full_stars) {
										echo '<span class="dashicons dashicons-star-filled"></span>';
									} elseif ($omnify_i === $omnify_full_stars + 1 && $omnify_half_star) {
										echo '<span class="dashicons dashicons-star-half"></span>';
									} else {
										echo '<span class="dashicons dashicons-star-empty"></span>';
									}
								}
								?>
							</span>
							<a href="#omnify-reviews-pane" class="omnify-rating-val" id="pdp-scroll-to-reviews">(<?php echo esc_html($omnify_review_count); ?> <?php echo $omnify_review_count === 1 ? esc_html__('customer review', 'omnifywp-ecommerce') : esc_html__('customer reviews', 'omnifywp-ecommerce'); ?>)</a>
						</div>
					<?php endif; ?>
				</div>

				<!-- Price block -->
				<div class="omnify-pdp-price-block">
					<div class="omnify-pdp-price-row">
						<?php if ($omnify_sale_price !== null && (float)$omnify_sale_price < (float)$omnify_regular_price) : 
							$omnify_discount_pct = 0;
							if ((float)$omnify_regular_price > 0) {
								$omnify_discount_pct = round((((float)$omnify_regular_price - (float)$omnify_sale_price) / (float)$omnify_regular_price) * 100);
							}
						?>
							<span class="omnify-pdp-price-sale" id="pdp-price-display"><?php echo esc_html($omnify_formatted_display); ?></span>
							<span class="omnify-pdp-price-regular" style="text-decoration: line-through; margin-left: 8px;"><?php echo esc_html($omnify_formatted_regular); ?></span>
							<?php if ($omnify_discount_pct > 0 && $omnify_show_discount_badge) : ?>
								<span class="omnify-pdp-price-discount-badge"><?php echo (int) $omnify_discount_pct; ?><?php esc_html_e('% OFF', 'omnifywp-ecommerce'); ?></span>
							<?php endif; ?>
						<?php else : ?>
							<span class="omnify-pdp-price-sale" id="pdp-price-display"><?php echo esc_html($omnify_formatted_regular); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<!-- Short Description -->
				<?php
				$omnify_short_desc = trim((string) ($omnify_product['short_description'] ?? ''));
				$omnify_show_short_desc = '' !== $omnify_short_desc;
				if ($omnify_show_short_desc) :
				?>
					<div class="omnify-pdp-short-desc-wrapper">
						<div class="omnify-pdp-short-desc" id="pdp-short-description">
							<?php echo wp_kses_post($omnify_short_desc); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="omnify-pdp-divider"></div>

				<!-- Live Status Pill -->
				<div class="omnify-pdp-status-pill-wrap" id="pdp-status-pill-wrap">
					<?php if (! empty($omnify_product['type']) && 'download' === $omnify_product['type']) : ?>
						<span id="pdp-status-pill-badge" class="omnify-pdp-status-pill is-digital">
							<span class="pulse-dot"></span>
							<?php esc_html_e('Instant Download • 48-Hour Expiring Link', 'omnifywp-ecommerce'); ?>
						</span>
					<?php else : ?>
						<span id="pdp-status-pill-badge" class="omnify-pdp-status-pill is-physical">
							<span class="pulse-dot"></span>
							<?php esc_html_e('In Stock • Ships Worldwide', 'omnifywp-ecommerce'); ?>
						</span>
					<?php endif; ?>
				</div>

				<!-- Swatches & Attribute Selectors -->
				<?php if ($omnify_is_variable && ! empty($omnify_attribute_map)) : ?>
					<div class="omnify-pdp-variations">
						<?php foreach ($omnify_attribute_map as $omnify_attr_name => $omnify_attr_vals) :
							$omnify_attr_slug = sanitize_title($omnify_attr_name);
							$omnify_attr_type = $omnify_attribute_meta_map[$omnify_attr_name]['type'] ?? 'button';
						?>
						<div class="omnify-var-attr-row omnify-swatch-group" data-attr="<?php echo esc_attr($omnify_attr_name); ?>" data-attribute="<?php echo esc_attr($omnify_attr_name); ?>">
							<div class="omnify-var-attr-label">
								<span><?php echo esc_html($omnify_attr_name); ?>: <span class="omnify-pdp-chosen-val" id="pdp-attr-chosen-<?php echo esc_attr(strtolower(str_replace(' ', '-', $omnify_attr_name))); ?>"></span></span>
							</div>
							
							<div class="omnify-swatch-list-sidebar omnify-var-options--<?php echo esc_attr($omnify_attr_type); ?>">
								<?php foreach ($omnify_attr_vals as $omnify_val) :
									$omnify_option_meta = $omnify_attribute_meta_map[$omnify_attr_name]['option_meta'][$omnify_val] ?? [];
									$omnify_option_is_in_stock = true;
									$omnify_color = sanitize_hex_color($omnify_option_meta['color'] ?? '') ?: '';
									$omnify_image_url = esc_url($omnify_option_meta['image_url'] ?? '');

									$omnify_badge_text = '';
									$omnify_badge_color = '';
									if (strtolower($omnify_val) === 'silver') {
										$omnify_badge_text = 'HOT';
										$omnify_badge_color = '#ef4444';
									} elseif (strtolower($omnify_val) === '64gb') {
										$omnify_badge_text = 'TRENDY';
										$omnify_badge_color = '#2563eb';
									}
								?>
									<button type="button"
										class="omnify-swatch-btn <?php echo ('color' === $omnify_attr_type || 'image' === $omnify_attr_type) ? 'omnify-swatch-btn--circle' : 'omnify-swatch-btn--rect'; ?>"
										data-attr="<?php echo esc_attr($omnify_attr_name); ?>"
										data-val="<?php echo esc_attr($omnify_val); ?>"
										data-attribute-name="<?php echo esc_attr($omnify_attr_name); ?>"
										data-option-value="<?php echo esc_attr($omnify_val); ?>"
										title="<?php echo esc_attr($omnify_val); ?>"
										aria-label="<?php echo esc_attr(sprintf(__('%1$s: %2$s', 'omnifywp-ecommerce'), $omnify_attr_name, $omnify_val)); ?>">

										<?php if ($omnify_badge_text && 'color' !== $omnify_attr_type && 'image' !== $omnify_attr_type) : ?>
											<span class="omnify-swatch-badge" style="background: <?php echo esc_attr($omnify_badge_color); ?>;"><?php echo esc_html($omnify_badge_text); ?></span>
										<?php endif; ?>

										<?php if ('color' === $omnify_attr_type && $omnify_color) : ?>
											<span class="omnify-swatch-color-dot" style="background-color: <?php echo esc_attr($omnify_color); ?>;"></span>
										<?php endif; ?>

										<?php if ('image' === $omnify_attr_type && $omnify_image_url) : ?>
											<img src="<?php echo esc_url($omnify_image_url); ?>" alt="<?php echo esc_attr($omnify_val); ?>" class="omnify-swatch-img" />
										<?php endif; ?>

										<?php if ('color' !== $omnify_attr_type && 'image' !== $omnify_attr_type) : ?>
											<span class="omnify-swatch-text-label"><?php echo esc_html($omnify_val); ?></span>
										<?php endif; ?>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endforeach; ?>

						<!-- Clear variations link -->
						<div class="omnify-pdp-clear-var-row">
							<button type="button" class="omnify-clear-variations" id="pdp-clear-var-link">✕ <?php esc_html_e('Clear selection', 'omnifywp-ecommerce'); ?></button>
						</div>

						<!-- Real-Time Variation Status Banner -->
						<div class="omnify-var-status-banner" id="pdp-var-status-banner" style="display:none;" aria-live="polite"></div>

						<!-- Stock & Info Badges -->
						<div id="pdp-var-meta-row" style="display:none;" aria-live="polite">
							<span id="pdp-var-stock-badge" class="omnify-pdp-badge" style="display:none;"></span>
							<span id="pdp-var-sku" class="omnify-pdp-badge omnify-pdp-badge--sku" style="display:none;"></span>
						</div>
						<div class="omnify-var-unavailable" id="pdp-var-unavailable" style="display:none;">
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e('Combination unavailable.', 'omnifywp-ecommerce'); ?>
						</div>
						<div class="omnify-pdp-var-note" id="pdp-var-note"></div>
					</div>
				<?php endif; ?>

				<!-- Selected price for variable products -->
				<div class="omnify-pdp-variable-selected-price" id="pdp-var-selected-price-box" style="display: none;">
					<span id="pdp-var-price-display"></span>
				</div>

				<!-- Local Variable Hydration for Buttons and Stock -->
				<?php
				$omnify_is_out_of_stock_simple = (! $omnify_is_variable) && ($omnify_stock_status === 'outofstock') && empty($omnify_product['allow_backorders']) && empty($omnify_product['preorder_enabled']);
				$omnify_buy_button_label  = ! empty($omnify_product['preorder_enabled']) ? __('Preorder Now', 'omnifywp-ecommerce') : __('Buy Now', 'omnifywp-ecommerce');
				$omnify_cart_button_label = ! empty($omnify_product['preorder_enabled']) ? __('Add Preorder', 'omnifywp-ecommerce') : __('Add to Cart', 'omnifywp-ecommerce');
				?>

				<!-- CTA Block -->
				<?php if ($omnify_is_out_of_stock_simple) : ?>
					<button type="button" class="omnify-pdp-btn-buy is-outofstock" disabled>
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e('Out of Stock', 'omnifywp-ecommerce'); ?>
					</button>
				<?php else : ?>
					<div class="omnify-pdp-cta-block">
						<div class="omnify-pdp-cta-row">
							<!-- Modern Rounded Qty Control -->
							<div class="omnify-pdp-qty-control-horizontal">
								<button type="button" class="omnify-pdp-qty-btn-horizontal" id="pdp-qty-minus" aria-label="<?php esc_attr_e('Decrease quantity', 'omnifywp-ecommerce'); ?>" disabled>−</button>
								<input type="number" class="omnify-pdp-qty-input" id="pdp-qty-input" value="1" min="1" <?php echo $omnify_max_purchase_qty > 0 ? 'max="' . esc_attr($omnify_max_purchase_qty) . '"' : ''; ?> readonly />
								<button type="button" class="omnify-pdp-qty-btn-horizontal" id="pdp-qty-plus" aria-label="<?php esc_attr_e('Increase quantity', 'omnifywp-ecommerce'); ?>">+</button>
							</div>

							<!-- Primary Add to Cart -->
							<button
								type="button"
								class="omnify-pdp-btn-cart omnify-add-cart-button"
								id="pdp-add-cart-btn"
								<?php disabled($omnify_is_variable); ?>
								data-id="<?php echo esc_attr((string) $omnify_product['id']); ?>"
								data-name="<?php echo esc_attr((string) $omnify_product['name']); ?>"
								data-price="<?php echo esc_attr((string) ((float) $omnify_display_price)); ?>"
								data-display-price="<?php echo esc_attr($omnify_formatted_display); ?>"
								data-image="<?php echo esc_attr((string) (! empty($omnify_product['thumbnail_url']) ? $omnify_product['thumbnail_url'] : ($omnify_all_images[0] ?? ''))); ?>"
								data-url="<?php echo esc_url(add_query_arg('omnify_product', $omnify_product['slug'], $omnify_storefront_url)); ?>"
								data-type="<?php echo esc_attr((string) ($omnify_product['type'] ?? 'download')); ?>"
								data-max-qty="<?php echo esc_attr($omnify_max_purchase_qty > 0 ? $omnify_max_purchase_qty : ''); ?>">
								<span><?php echo esc_html($omnify_cart_button_label); ?></span>
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
							</button>
						</div>

						<!-- Row 2: Instant Buy Now -->
						<div class="omnify-pdp-buy-now-wrap">
							<a href="<?php echo esc_url($omnify_buy_url); ?>" class="omnify-pdp-btn-buy" id="pdp-buy-btn" data-base-url="<?php echo esc_url($omnify_buy_url); ?>" <?php if ($omnify_is_variable) echo 'style="opacity: 0.5; pointer-events: none;"'; ?>>
								<span><?php echo esc_html($omnify_buy_button_label); ?></span>
							</a>
						</div>
					</div>
				<?php endif; ?>

				<!-- Security & Confidence Trust Strip -->
				<div class="omnify-pdp-trust-strip">
					<div class="omnify-pdp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
						<div class="omnify-pdp-trust-text">
							<strong><?php esc_html_e('Secure Checkout', 'omnifywp-ecommerce'); ?></strong>
							<span><?php esc_html_e('256-bit SSL encrypted', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>
					<div class="omnify-pdp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
						<div class="omnify-pdp-trust-text">
							<strong><?php esc_html_e('Instant Access', 'omnifywp-ecommerce'); ?></strong>
							<span><?php esc_html_e('Automated delivery', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>
					<div class="omnify-pdp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4-4 4M12 16V9"/></svg>
						<div class="omnify-pdp-trust-text">
							<strong><?php esc_html_e('Satisfaction Guarantee', 'omnifywp-ecommerce'); ?></strong>
							<span><?php esc_html_e('Dedicated support', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>
				</div>

				<!-- Admin Edit Link -->
				<?php if (current_user_can('manage_options') && ! empty($omnify_product['id'])) : ?>
					<div class="omnify-pdp-admin-edit-wrap">
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product['id'])); ?>" class="omnify-pdp-admin-edit-link">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
							<span><?php esc_html_e('Edit Product', 'omnifywp-ecommerce'); ?></span>
						</a>
					</div>
				<?php endif; ?>

			</div><!-- /.omnify-pdp-summary-column -->

		</div><!-- /.omnify-pdp-grid -->

		<!-- TABS SECTION -->
		<div class="omnify-pdp-tabs-wrapper">
			<nav class="omnify-pdp-tab-nav" role="tablist">
				<?php if ($omnify_has_description) : ?>
					<button type="button" class="omnify-pdp-tab-btn <?php echo esc_attr('description' === $omnify_active_tab ? 'active' : ''); ?>" data-tab="description" role="tab" aria-selected="<?php echo esc_attr('description' === $omnify_active_tab ? 'true' : 'false'); ?>" id="pdp-tab-desc">
						<?php esc_html_e('Description', 'omnifywp-ecommerce'); ?>
					</button>
				<?php endif; ?>
				<?php if ($omnify_has_specs) : ?>
					<button type="button" class="omnify-pdp-tab-btn <?php echo esc_attr('specifications' === $omnify_active_tab ? 'active' : ''); ?>" data-tab="specifications" role="tab" aria-selected="<?php echo esc_attr('specifications' === $omnify_active_tab ? 'true' : 'false'); ?>" id="pdp-tab-specs">
						<?php esc_html_e('Specifications', 'omnifywp-ecommerce'); ?>
					</button>
				<?php endif; ?>
				<?php if ($omnify_has_delivery_tab) : ?>
					<button type="button" class="omnify-pdp-tab-btn <?php echo esc_attr('delivery-return' === $omnify_active_tab ? 'active' : ''); ?>" data-tab="delivery-return" role="tab" aria-selected="<?php echo esc_attr('delivery-return' === $omnify_active_tab ? 'true' : 'false'); ?>" id="pdp-tab-delivery-return">
						<?php esc_html_e('Delivery & Return', 'omnifywp-ecommerce'); ?>
					</button>
				<?php endif; ?>
				<button type="button" class="omnify-pdp-tab-btn <?php echo esc_attr('reviews' === $omnify_active_tab ? 'active' : ''); ?>" data-tab="reviews" role="tab" aria-selected="<?php echo esc_attr('reviews' === $omnify_active_tab ? 'true' : 'false'); ?>" id="pdp-tab-reviews">
					<?php esc_html_e('Reviews', 'omnifywp-ecommerce'); ?>
					<?php if ($omnify_review_count > 0) : ?>
						<span class="omnify-review-tab-badge"><?php echo esc_html($omnify_review_count); ?></span>
					<?php endif; ?>
				</button>
			</nav>

			<!-- Description Tab -->
			<?php if ($omnify_has_description) : ?>
				<div class="omnify-pdp-tab-pane <?php echo esc_attr('description' === $omnify_active_tab ? 'active' : ''); ?>" id="omnify-description-pane" role="tabpanel" aria-labelledby="pdp-tab-desc">
					<div class="omnify-desc" style="line-height: 1.75;">
						<?php echo wp_kses_post($omnify_product['description']); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($omnify_has_specs) : ?>
				<!-- Specifications Tab -->
				<div class="omnify-pdp-tab-pane <?php echo esc_attr('specifications' === $omnify_active_tab ? 'active' : ''); ?>" id="omnify-specifications-pane" role="tabpanel" aria-labelledby="pdp-tab-specs">
					<table class="omnify-pdp-specs-table">
						<tbody>
							<?php foreach ($omnify_specs_rows as $omnify_spec_row) : ?>
								<tr>
									<th><?php echo esc_html($omnify_spec_row['key']); ?></th>
									<td><?php echo esc_html($omnify_spec_row['value']); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ($omnify_has_delivery_tab) : ?>
				<!-- Delivery & Return Tab -->
				<div class="omnify-pdp-tab-pane <?php echo esc_attr('delivery-return' === $omnify_active_tab ? 'active' : ''); ?>" id="omnify-delivery-return-pane" role="tabpanel" aria-labelledby="pdp-tab-delivery-return">
					<div class="omnify-pdp-policy-grid">
						<?php if ('' !== $omnify_delivery_display_text) : ?>
							<section class="omnify-pdp-policy-card">
								<span class="omnify-pdp-policy-icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
								</span>
								<div>
									<h3><?php esc_html_e('Delivery Information', 'omnifywp-ecommerce'); ?></h3>
									<p><?php echo nl2br(esc_html($omnify_delivery_display_text)); ?></p>
								</div>
							</section>
						<?php endif; ?>

						<?php if ('' !== $omnify_return_display_text) : ?>
							<section class="omnify-pdp-policy-card">
								<span class="omnify-pdp-policy-icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
								</span>
								<div>
									<h3><?php esc_html_e('Return Information', 'omnifywp-ecommerce'); ?></h3>
									<p><?php echo nl2br(esc_html($omnify_return_display_text)); ?></p>
								</div>
							</section>
						<?php endif; ?>

						<?php if ('' !== $omnify_refund_display_text || $omnify_refunds_enabled) : ?>
							<section class="omnify-pdp-policy-card">
								<span class="omnify-pdp-policy-icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"></path><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"></path></svg>
								</span>
								<div>
									<h3><?php esc_html_e('Refund Policy', 'omnifywp-ecommerce'); ?></h3>
									<?php if ($omnify_refunds_enabled) : ?>
										<div class="omnify-pdp-policy-pill"><?php echo esc_html(sprintf(__('Refund window: %d days', 'omnifywp-ecommerce'), $omnify_refund_window_days)); ?></div>
									<?php endif; ?>
									<?php if ('' !== $omnify_refund_display_text) : ?>
										<p><?php echo nl2br(esc_html($omnify_refund_display_text)); ?></p>
									<?php endif; ?>
								</div>
							</section>
						<?php endif; ?>

						<?php if ('' !== $omnify_trust_display_text) : ?>
							<section class="omnify-pdp-policy-card">
								<span class="omnify-pdp-policy-icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
								</span>
								<div>
									<h3><?php echo esc_html($omnify_trust_badge_title ?: __('Trust & Security', 'omnifywp-ecommerce')); ?></h3>
									<p><?php echo nl2br(esc_html($omnify_trust_display_text)); ?></p>
								</div>
							</section>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Reviews Tab -->
			<div class="omnify-pdp-tab-pane <?php echo esc_attr('reviews' === $omnify_active_tab ? 'active' : ''); ?>" id="omnify-reviews-pane" role="tabpanel" aria-labelledby="pdp-tab-reviews">
				<?php if ('submitted' === $omnify_review_notice) : ?>
					<div class="omnify-review-notice"><?php esc_html_e('Thanks. Your review is waiting for approval.', 'omnifywp-ecommerce'); ?></div>
				<?php elseif ('updated' === $omnify_review_notice) : ?>
					<div class="omnify-review-notice"><?php esc_html_e('Your review was updated and is waiting for approval.', 'omnifywp-ecommerce'); ?></div>
				<?php elseif ('no_rating' === $omnify_review_notice) : ?>
					<div class="omnify-review-notice"><?php esc_html_e('Please choose a rating before submitting your review.', 'omnifywp-ecommerce'); ?></div>
				<?php endif; ?>

				<!-- Summary Breakdown -->
				<?php if ($omnify_review_count > 0) : ?>
					<div class="omnify-pdp-reviews-breakdown">
						<div class="omnify-pdp-reviews-breakdown-left">
							<div style="font-size: 42px; font-weight: 500; color: var(--omnify-dark); line-height: 1.1;"><?php echo esc_html(number_format($omnify_avg_rating, 1)); ?></div>
							<span class="omnify-stars" style="font-size: 16px; margin: 4px 0 2px; display: block;">
								<?php
								$omnify_full_stars = floor($omnify_avg_rating);
								$omnify_half_star  = ($omnify_avg_rating - $omnify_full_stars) >= 0.5;
								for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) {
									if ($omnify_i <= $omnify_full_stars) {
										echo '<span class="dashicons dashicons-star-filled"></span>';
									} elseif ($omnify_i === $omnify_full_stars + 1 && $omnify_half_star) {
										echo '<span class="dashicons dashicons-star-half"></span>';
									} else {
										echo '<span class="dashicons dashicons-star-empty"></span>';
									}
								}
								?>
							</span>
							<span style="font-size:12px; color:var(--omnify-gray-500);"><?php echo esc_html($omnify_review_count); ?> <?php echo $omnify_review_count === 1 ? esc_html__('customer review', 'omnifywp-ecommerce') : esc_html__('customer reviews', 'omnifywp-ecommerce'); ?></span>
						</div>
						
						<div class="omnify-pdp-reviews-breakdown-center">
							<?php 
							// Build star counts breakdown safely from reviews array
							$omnify_breakdown = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
							foreach ($omnify_reviews as $omnify_rev) {
								$omnify_star = intval($omnify_rev['rating'] ?? 5);
								if ($omnify_star >= 1 && $omnify_star <= 5) {
									$omnify_breakdown[$omnify_star]++;
								}
							}
							for ($omnify_i = 5; $omnify_i >= 1; $omnify_i--) : 
								$omnify_cnt = intval($omnify_breakdown[$omnify_i] ?? 0);
								$omnify_pct = $omnify_review_count > 0 ? ($omnify_cnt / $omnify_review_count) * 100 : 0;
							?>
								<div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--omnify-gray-600);">
									<span style="width: 45px; text-align: right;"><?php echo esc_html(sprintf(_n('%d star', '%d stars', $omnify_i, 'omnifywp-ecommerce'), $omnify_i)); ?></span>
									<div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; position: relative;">
										<div style="position: absolute; top: 0; left: 0; bottom: 0; background: #f59e0b; width: <?php echo esc_attr($omnify_pct); ?>%; border-radius: 3px;"></div>
									</div>
									<span style="width: 25px; text-align: right;"><?php echo esc_html($omnify_cnt); ?></span>
								</div>
							<?php endfor; ?>
						</div>

						<?php 
						$omnify_has_review_images = false;
						$omnify_review_images = [];
						foreach ($omnify_reviews as $omnify_rev) {
							if (! empty($omnify_rev['images']) && is_array($omnify_rev['images'])) {
								foreach ($omnify_rev['images'] as $omnify_r_img) {
									$omnify_review_images[] = esc_url($omnify_r_img);
									$omnify_has_review_images = true;
								}
							}
						}
						if ($omnify_has_review_images) : ?>
							<div class="omnify-pdp-reviews-breakdown-media" style="margin-left: 24px; padding-left: 24px; border-left: 1px solid var(--omnify-gray-200); display: flex; flex-direction: column; gap: 8px; max-width: 220px;">
								<span style="font-size: 12px; font-weight: 500; color: var(--omnify-dark); text-transform: uppercase;"><?php esc_html_e('Customer Images', 'omnifywp-ecommerce'); ?></span>
								<div style="display: flex; gap: 6px; flex-wrap: wrap;">
									<?php foreach (array_slice($omnify_review_images, 0, 6) as $omnify_r_img) : ?>
										<img src="<?php echo esc_url($omnify_r_img); ?>" alt="Customer upload" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="window.open(this.src, '_blank')" />
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<!-- Reviews Stream -->
			<div class="omnify-pdp-reviews">
				<?php if (empty($omnify_reviews)) : ?>
					<div class="omnify-reviews-empty">
						<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
						<p><?php esc_html_e('No reviews yet. Be the first to share your experience!', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<?php foreach ($omnify_reviews as $omnify_review) :
						$omnify_star = intval($omnify_review['rating'] ?? 0);
						$omnify_is_mine = $omnify_current_user_email && sanitize_email($omnify_review['customer_email']) === sanitize_email($omnify_current_user_email);
						$omnify_initials = strtoupper(substr($omnify_review['name'] ?? 'A', 0, 1));
					?>
					<div class="omnify-review-card" id="review-card-<?php echo esc_attr((string) ($omnify_review['id'] ?? '')); ?>">
						<div class="omnify-review-avatar"><?php echo esc_html($omnify_initials); ?></div>
						<div class="omnify-review-main">
							<div class="omnify-review-header">
								<div class="omnify-review-meta">
									<strong class="omnify-review-author"><?php echo esc_html($omnify_review['name']); ?></strong>
									<span class="omnify-review-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime((string) ($omnify_review['created_at'] ?? 'now')))); ?></span>
								</div>
								<div class="omnify-review-stars-row">
									<?php for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) : ?>
										<svg class="omnify-review-star <?php echo $omnify_i <= $omnify_star ? 'filled' : 'empty'; ?>" width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $omnify_i <= $omnify_star ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
									<?php endfor; ?>
									<?php if ($omnify_is_mine) : ?>
										<button type="button"
											class="omnify-pdp-review-edit-btn"
											data-review-id="<?php echo esc_attr((string) ($omnify_review['id'] ?? '')); ?>"
											data-review-rating="<?php echo esc_attr((string) $omnify_star); ?>"
											data-review-content="<?php echo esc_attr((string) ($omnify_review['content'] ?? '')); ?>"
											aria-label="<?php esc_attr_e('Edit review', 'omnifywp-ecommerce'); ?>">
											<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
											<?php esc_html_e('Edit', 'omnifywp-ecommerce'); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>
							<div class="omnify-review-body">
								<p><?php echo esc_html($omnify_review['content']); ?></p>
								<?php if (! empty($omnify_review['images']) && is_array($omnify_review['images'])) : ?>
									<div class="omnify-review-images">
										<?php foreach ($omnify_review['images'] as $omnify_img_url) : ?>
											<img src="<?php echo esc_url($omnify_img_url); ?>" alt="<?php esc_attr_e('Review image', 'omnifywp-ecommerce'); ?>" loading="lazy" onclick="window.open(this.src,'_blank')" />
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<!-- Review Submission Form -->
			<?php if (! is_user_logged_in()) : ?>
				<div class="omnify-review-gate">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<span><?php printf(
						wp_kses(
							// translators: %s: Login URL
							__('Please <a href="%s">log in</a> to write a review. Only verified buyers can leave a review.', 'omnifywp-ecommerce'),
							['a' => ['href' => []]]
						),
						esc_url(wp_login_url(get_permalink()))
					); ?></span>
				</div>
			<?php elseif (! $omnify_user_has_purchased && ! $omnify_user_has_reviewed) : ?>
				<div class="omnify-review-gate omnify-review-gate--warn">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
					<span><?php esc_html_e('Only verified buyers can leave a review.', 'omnifywp-ecommerce'); ?></span>
				</div>
			<?php else : ?>
				<div class="omnify-review-form">
					<div class="omnify-review-form-inner">
						<h3 id="omnify-review-form-title" class="omnify-review-form-heading" data-label-edit="<?php esc_attr_e('Edit Your Review', 'omnifywp-ecommerce'); ?>" data-label-add="<?php esc_attr_e('Write a Review', 'omnifywp-ecommerce'); ?>">
							<?php echo $omnify_user_has_reviewed ? esc_html__('Edit Your Review', 'omnifywp-ecommerce') : esc_html__('Write a Review', 'omnifywp-ecommerce'); ?>
						</h3>
						<form method="post" action="<?php echo esc_url($omnify_review_action_url); ?>" enctype="multipart/form-data" class="omnify-review-form-fields">
							<input type="hidden" name="action" value="omnify_submit_review" />
							<input type="hidden" name="product_id" value="<?php echo esc_attr((string) $omnify_product['id']); ?>" />
							<input type="hidden" name="review_id" id="pdp-review-id-input" value="<?php echo esc_attr($omnify_my_review_id > 0 ? (string)$omnify_my_review_id : ''); ?>" />
							<?php wp_nonce_field('omnify_submit_review_' . $omnify_product['id'], 'omnify_review_nonce'); ?>

							<!-- Star Rating Picker -->
							<div class="omnify-review-rating-group">
								<label><?php esc_html_e('Your Rating', 'omnifywp-ecommerce'); ?></label>
								<div class="omnify-pdp-review-stars-select" id="pdp-review-rating-stars" role="radiogroup" aria-label="<?php esc_attr_e('Select rating', 'omnifywp-ecommerce'); ?>">
									<?php for ($omnify_ri = 1; $omnify_ri <= 5; $omnify_ri++) : 
										$omnify_star_filled = ($omnify_my_rating >= $omnify_ri);
										$omnify_active_class = $omnify_star_filled ? ' is-active' : '';
									?>
										<button type="button" class="omnify-rating-star-btn<?php echo esc_attr($omnify_active_class); ?>" data-val="<?php echo esc_attr($omnify_ri); ?>" aria-label="<?php echo esc_attr(sprintf(__('%d stars', 'omnifywp-ecommerce'), $omnify_ri)); ?>">
											<svg width="28" height="28" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
										</button>
									<?php endfor; ?>
								</div>
								<input type="hidden" name="rating" id="pdp-rating-input" value="<?php echo esc_attr((string)$omnify_my_rating); ?>" />
							</div>

							<!-- Review Text -->
							<div class="omnify-review-field-group">
								<label for="omnify-review-content"><?php esc_html_e('Your Review', 'omnifywp-ecommerce'); ?></label>
								<textarea id="omnify-review-content" name="content" required rows="4" placeholder="<?php esc_attr_e('Share your experience with this product…', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_review_form_content ?? ''); ?></textarea>
							</div>

							<!-- Image Upload -->
							<div class="omnify-review-upload-group">
								<label class="omnify-review-upload-label">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
									<?php esc_html_e('Add Photos (optional)', 'omnifywp-ecommerce'); ?>
									<input type="file" name="review_images[]" id="omnify-review-upload-input" multiple accept="image/*" class="omnify-review-upload-input" />
								</label>
								<div id="omnify-review-file-list" class="omnify-review-file-list"></div>
							</div>

							<!-- Actions -->
							<div class="omnify-review-form-actions">
								<button type="submit" class="omnify-review-submit" data-label-submit="<?php esc_attr_e('Submit Review', 'omnifywp-ecommerce'); ?>" data-label-update="<?php esc_attr_e('Update Review', 'omnifywp-ecommerce'); ?>">
									<?php echo $omnify_user_has_reviewed ? esc_html__('Update Review', 'omnifywp-ecommerce') : esc_html__('Submit Review', 'omnifywp-ecommerce'); ?>
								</button>
								<?php if ($omnify_user_has_reviewed) : ?>
									<button type="button" id="pdp-review-cancel-btn" class="omnify-review-cancel" data-label-cancel="<?php esc_attr_e('Cancel', 'omnifywp-ecommerce'); ?>">
										<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
									</button>
								<?php endif; ?>
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>
			</div><!-- /#omnify-reviews-pane -->
		</div><!-- /.omnify-pdp-tabs-wrapper -->
	</div><!-- /.omnify-pdp-wrapper -->

	<?php
	/* ── Reusable card renderer for related/upsell/cross-sell/recently-viewed ── */
	$omnify_render_pdp_card = function($omnify_rp) use ($omnify_storefront_url, $omnify_reviews_repo, $omnify_format_price, $omnify_show_compare, $omnify_show_discount_badge, $omnify_storefront_card_style, $omnify_show_add_to_cart, $omnify_prod_repo) {
		$omnify_rp_url = add_query_arg('omnify_product', $omnify_rp['slug'], $omnify_storefront_url);
		$omnify_rp_avg_rating    = ($omnify_reviews_repo && method_exists($omnify_reviews_repo, 'get_average_rating')) ? $omnify_reviews_repo->get_average_rating((int) $omnify_rp['id']) : 0.0;
		$omnify_rp_review_count  = ($omnify_reviews_repo && method_exists($omnify_reviews_repo, 'get_total_count')) ? $omnify_reviews_repo->get_total_count((int) $omnify_rp['id']) : 0;
		$omnify_rp_actual_price = (null !== ($omnify_rp['sale_price'] ?? null) && '' !== (string) $omnify_rp['sale_price']) ? (float) $omnify_rp['sale_price'] : (float) $omnify_rp['price'];
		$omnify_rp_discount_pct = 0;
		if (! empty($omnify_rp['sale_price']) && (float) $omnify_rp['price'] > 0) {
			$omnify_rp_discount_pct = round((((float) $omnify_rp['price'] - (float) $omnify_rp['sale_price']) / (float) $omnify_rp['price']) * 100);
		}
		$omnify_rp_is_hot = in_array('hot', array_map('strtolower', (array) ($omnify_rp['tags'] ?? [])), true) || ($omnify_rp['id'] % 5 === 0);
		$omnify_rp_is_new = in_array('new', array_map('strtolower', (array) ($omnify_rp['tags'] ?? [])), true) || ($omnify_rp['id'] % 7 === 0);
 
		$omnify_rp_card_size_options = [];
		if (! empty($omnify_rp['attributes'])) {
			foreach ($omnify_rp['attributes'] as $omnify_attr) {
				$omnify_name_lower = strtolower((string) ($omnify_attr['name'] ?? ''));
				if ('size' === $omnify_name_lower) {
					$omnify_rp_card_size_options = (array) ($omnify_attr['options'] ?? []);
				}
			}
		}

		$omnify_rp_variations = [];
		if ('variable' === ($omnify_rp['type'] ?? '') && $omnify_prod_repo) {
			$omnify_rp_variations = $omnify_prod_repo->get_variations((int) $omnify_rp['id']);
		}

		$omnify_rp_thumb = ! empty($omnify_rp['thumbnail_url']) ? $omnify_rp['thumbnail_url'] : '';
		if (empty($omnify_rp_thumb)) {
			$omnify_rp_name_lower = strtolower((string) ($omnify_rp['name'] ?? ''));
			$omnify_assets_img = defined('OMNIFY_URL') ? OMNIFY_URL . 'assets/storefront/images/' : plugins_url('assets/storefront/images/', dirname(__DIR__, 1) . '/dummy.php');
			if (strpos($omnify_rp_name_lower, 'hoodie') !== false || strpos($omnify_rp_name_lower, 'shirt') !== false || strpos($omnify_rp_name_lower, 'merch') !== false || strpos($omnify_rp_name_lower, 't-shirt') !== false || strpos($omnify_rp_name_lower, 'cotton') !== false || strpos($omnify_rp_name_lower, 'apparel') !== false || 'physical' === strtolower((string) ($omnify_rp['type'] ?? ''))) {
				$omnify_rp_thumb = $omnify_assets_img . 'product-merch-hoodie.jpg';
			} elseif (strpos($omnify_rp_name_lower, 'bundle') !== false || strpos($omnify_rp_name_lower, 'kit') !== false || strpos($omnify_rp_name_lower, 'pack') !== false || 'bundle' === strtolower((string) ($omnify_rp['type'] ?? ''))) {
				$omnify_rp_thumb = $omnify_assets_img . 'product-bundle-kit.jpg';
			} else {
				$omnify_rp_thumb = $omnify_assets_img . 'product-digital-suite.jpg';
			}
		}
		?>
		<div class="omnify-product-card omnify-product-card--premium <?php echo 'omnify-product-card--' . esc_attr($omnify_storefront_card_style); ?>"
			onclick="window.location.href='<?php echo esc_url($omnify_rp_url); ?>'" style="cursor: pointer;"
			data-product-id="<?php echo esc_attr((string) $omnify_rp['id']); ?>"
			data-product-name="<?php echo esc_attr((string) $omnify_rp['name']); ?>"
			data-price="<?php echo esc_attr((string) $omnify_rp_actual_price); ?>"
			data-url="<?php echo esc_url($omnify_rp_url); ?>"
			data-type="<?php echo esc_attr((string) ($omnify_rp['type'] ?? 'download')); ?>"
			data-attributes="<?php echo esc_attr(wp_json_encode($omnify_rp['attributes'] ?? [])); ?>"
			data-variations="<?php echo esc_attr(wp_json_encode($omnify_rp_variations)); ?>">
				
				<div class="omnify-product-card__image-container">
					<div class="omnify-product-card__image-wrap">
						<?php if (! empty($omnify_rp_thumb)) : ?>
							<img src="<?php echo esc_url($omnify_rp_thumb); ?>" alt="<?php echo esc_attr($omnify_rp['name']); ?>" class="omnify-product-card__image omnify-product-card__image--primary" loading="lazy">
							<?php if (! empty($omnify_rp['gallery_urls']) && isset($omnify_rp['gallery_urls'][0])) : ?>
								<img src="<?php echo esc_url($omnify_rp['gallery_urls'][0]); ?>" alt="<?php echo esc_attr($omnify_rp['name']); ?>" class="omnify-product-card__image omnify-product-card__image--secondary" loading="lazy">
							<?php endif; ?>
						<?php else : ?>
							<div class="omnify-product-card__placeholder">
								<span class="dashicons dashicons-format-image" style="font-size:32px; width:32px; height:32px;"></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="omnify-card-badges">
						<?php if ($omnify_rp_discount_pct > 0 && $omnify_show_discount_badge) : ?>
							<span class="omnify-badge-bubble omnify-badge-bubble--sale">-<?php echo (int) $omnify_rp_discount_pct; ?>%</span>
						<?php endif; ?>
						<?php if ($omnify_rp_is_hot) : ?>
							<span class="omnify-badge-bubble omnify-badge-bubble--hot"><?php esc_html_e('HOT', 'omnifywp-ecommerce'); ?></span>
						<?php endif; ?>
						<?php if ($omnify_rp_is_new) : ?>
							<span class="omnify-badge-bubble omnify-badge-bubble--new"><?php esc_html_e('NEW', 'omnifywp-ecommerce'); ?></span>
						<?php endif; ?>
					</div>

					<div class="omnify-product-card__hover-tools">
						<button type="button" class="omnify-hover-tool-btn omnify-wishlist-button" data-product-id="<?php echo esc_attr((string) $omnify_rp['id']); ?>" onclick="event.stopPropagation();" aria-label="<?php esc_attr_e('Add to wishlist', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Wishlist', 'omnifywp-ecommerce'); ?>">
							<svg class="heart-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
						</button>
						<?php if ($omnify_show_compare) : ?>
							<button type="button" class="omnify-hover-tool-btn omnify-compare-button"
								data-product-id="<?php echo esc_attr((string) $omnify_rp['id']); ?>"
								data-name="<?php echo esc_attr((string) $omnify_rp['name']); ?>"
								data-display-price="<?php echo esc_attr($omnify_format_price($omnify_rp_actual_price)); ?>"
								data-type="<?php echo esc_attr((string) ($omnify_rp['type'] ?? 'download')); ?>"
								data-image="<?php echo esc_attr((string) (! empty($omnify_rp['thumbnail_url']) ? $omnify_rp['thumbnail_url'] : $omnify_rp_thumb)); ?>"
								data-url="<?php echo esc_url($omnify_rp_url); ?>"
								data-categories-label="<?php echo esc_attr(implode(', ', (array) ($omnify_rp['categories'] ?? []))); ?>"
								data-rating="<?php echo esc_attr((string) round((float) $omnify_rp_avg_rating, 1)); ?>"
								data-reviews="<?php echo esc_attr((string) $omnify_rp_review_count); ?>"
								data-description=""
								onclick="event.stopPropagation();"
								title="<?php esc_attr_e('Compare', 'omnifywp-ecommerce'); ?>">
								<svg class="compare-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4M7 4L3 8M7 4L11 8M17 8V20M17 20L13 16M17 20L21 16"/></svg>
							</button>
						<?php endif; ?>
						<a href="<?php echo esc_url($omnify_rp_url); ?>" onclick="event.stopPropagation();" class="omnify-hover-tool-btn omnify-quick-view-button" aria-label="<?php esc_attr_e('Quick view', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Quick view', 'omnifywp-ecommerce'); ?>">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.15" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 12s3.75-7 10.5-7 10.5 7 10.5 7-3.75 7-10.5 7-10.5-7z"/><circle cx="12" cy="12" r="3"/></svg>
						</a>
					</div>

					<?php if ($omnify_show_add_to_cart) : ?>
						<div class="omnify-product-card__quick-add-wrap">
							<?php if ('variable' === ($omnify_rp['type'] ?? '')) : ?>
								<a href="<?php echo esc_url($omnify_rp_url); ?>" onclick="event.stopPropagation();" class="omnify-product-card__quick-add omnify-select-options-btn" aria-label="<?php esc_attr_e('Select Options', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Select Options', 'omnifywp-ecommerce'); ?>">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
								</a>
							<?php else : ?>
								<button type="button" onclick="event.stopPropagation();" class="omnify-product-card__quick-add omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_rp['id']); ?>" data-name="<?php echo esc_attr($omnify_rp['name']); ?>" data-price="<?php echo esc_attr($omnify_rp_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_rp_actual_price)); ?>" data-image="<?php echo esc_attr(! empty($omnify_rp['thumbnail_url']) ? $omnify_rp['thumbnail_url'] : $omnify_rp_thumb); ?>" data-url="<?php echo esc_url($omnify_rp_url); ?>" aria-label="<?php esc_attr_e('Add to Cart', 'omnifywp-ecommerce'); ?>" title="<?php esc_attr_e('Add to Cart', 'omnifywp-ecommerce'); ?>">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
								</button>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if (! empty($omnify_rp_card_size_options)) : ?>
						<div class="omnify-product-card__hover-sizes" aria-label="<?php esc_attr_e('Available sizes', 'omnifywp-ecommerce'); ?>">
							<?php foreach (array_slice($omnify_rp_card_size_options, 0, 5) as $omnify_hover_size) : ?>
								<span class="omnify-product-card__hover-size"><?php echo esc_html($omnify_hover_size); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="omnify-product-card__slide-up-action">
						<?php if ('variable' === ($omnify_rp['type'] ?? '')) : ?>
							<a href="<?php echo esc_url($omnify_rp_url); ?>" onclick="event.stopPropagation();" class="omnify-btn-slide-cart omnify-select-options-btn">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
								<span><?php esc_html_e('Select Options', 'omnifywp-ecommerce'); ?></span>
							</a>
						<?php else : ?>
							<button type="button" onclick="event.stopPropagation();" class="omnify-btn-slide-cart omnify-add-cart-button" data-id="<?php echo esc_attr($omnify_rp['id']); ?>" data-name="<?php echo esc_attr($omnify_rp['name']); ?>" data-price="<?php echo esc_attr($omnify_rp_actual_price); ?>" data-display-price="<?php echo esc_attr($omnify_format_price($omnify_rp_actual_price)); ?>" data-image="<?php echo esc_attr(! empty($omnify_rp['thumbnail_url']) ? $omnify_rp['thumbnail_url'] : $omnify_rp_thumb); ?>" data-url="<?php echo esc_url($omnify_rp_url); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
								<span><?php esc_html_e('Add to Cart', 'omnifywp-ecommerce'); ?></span>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<div class="omnify-product-card__content">
					<?php if (! empty($omnify_rp['categories'])) : ?>
						<div class="omnify-product-card__category-label">
							<?php 
							$omnify_rp_cats = [];
							foreach (array_slice((array) $omnify_rp['categories'], 0, 2) as $omnify_rp_cat) {
								$omnify_cat_url = add_query_arg('category', $omnify_rp_cat, $omnify_storefront_url);
								$omnify_rp_cats[] = '<a href="' . esc_url($omnify_cat_url) . '" onclick="event.stopPropagation();" style="color: inherit; text-decoration: none;">' . esc_html(strtoupper($omnify_rp_cat)) . '</a>';
							}
							echo implode(', ', $omnify_rp_cats);
							?>
						</div>
					<?php endif; ?>

					<h3 class="omnify-product-card__title">
						<?php echo esc_html($omnify_rp['name']); ?>
					</h3>

					<?php if ($omnify_rp_review_count > 0) : ?>
						<div class="omnify-product-card__rating">
							<span class="stars"><?php echo esc_html(str_repeat('★', (int) round($omnify_rp_avg_rating)) . str_repeat('☆', 5 - (int) round($omnify_rp_avg_rating))); ?></span>
							<span class="count">(<?php echo esc_html($omnify_rp_review_count); ?>)</span>
						</div>
					<?php endif; ?>

					<div class="omnify-product-card__price">
						<?php if (! empty($omnify_rp['sale_price']) && (float) $omnify_rp['sale_price'] < (float) $omnify_rp['price']) : ?>
							<span class="omnify-price-old" style="text-decoration: line-through; color: var(--omnify-gray-400); margin-right: 6px;"><?php echo esc_html($omnify_format_price($omnify_rp['price'])); ?></span>
							<span class="omnify-price-sale" style="color: #ef4444; font-weight: 500;"><?php echo esc_html($omnify_format_price($omnify_rp['sale_price'])); ?></span>
						<?php else : ?>
							<span class="omnify-price-regular" style="font-weight: 500;"><?php echo esc_html($omnify_format_price($omnify_rp['price'])); ?></span>
						<?php endif; ?>
					</div>

					<!-- Color swatches from attributes -->
					<?php
					$omnify_rp_color_swatches = [];
					if (! empty($omnify_rp['attributes'])) {
						foreach ($omnify_rp['attributes'] as $omnify_rp_attr) {
							$omnify_rp_attr_name_lc = strtolower((string) ($omnify_rp_attr['name'] ?? ''));
							if ('color' === $omnify_rp_attr_name_lc || 'colour' === $omnify_rp_attr_name_lc) {
								$omnify_rp_color_swatches = array_slice((array) ($omnify_rp_attr['options'] ?? []), 0, 6);
							}
						}
					}
					if (! empty($omnify_rp_color_swatches)) :
					?>
						<div class="omnify-pdp-rp-swatches">
							<?php foreach ($omnify_rp_color_swatches as $omnify_rp_swatch_val) : ?>
								<span class="omnify-pdp-rp-swatch" title="<?php echo esc_attr($omnify_rp_swatch_val); ?>" style="background-color: <?php echo esc_attr(strtolower($omnify_rp_swatch_val)); ?>;"></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>


			</div>
		</div>
		<?php
	};
	?>

	<?php
	$omnify_upsells = $omnify_prod_repo->get_upsells($omnify_product);
	$omnify_cross_sells = $omnify_prod_repo->get_cross_sells($omnify_product);
	$omnify_recently_viewed_products = [];
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$omnify_recent_cookie = isset($_COOKIE['omnify_recently_viewed']) ? sanitize_text_field(wp_unslash($_COOKIE['omnify_recently_viewed'])) : '';

	if (! empty($omnify_recent_cookie)) {
		$omnify_recent_ids = array_filter(array_map('intval', explode(',', $omnify_recent_cookie)));
		$omnify_recent_ids = array_diff($omnify_recent_ids, [$omnify_product['id']]);
		foreach ($omnify_recent_ids as $omnify_rid) {
			$omnify_rprod = $omnify_prod_repo->find($omnify_rid);
			if ($omnify_rprod && 'published' === ($omnify_rprod['status'] ?? '')) {
				$omnify_recently_viewed_products[] = $omnify_rprod;
			}
		}
	}

	$omnify_has_after_sections = (! empty($omnify_upsells) && is_array($omnify_upsells))
		|| (! empty($omnify_cross_sells) && is_array($omnify_cross_sells))
		|| (! empty($omnify_related_products) && is_array($omnify_related_products))
		|| ! empty($omnify_recently_viewed_products);

	if ($omnify_has_after_sections) :
	?>
		<div class="omnify-pdp-after-sections">
			<!-- Upsells Section -->
			<?php if (! empty($omnify_upsells) && is_array($omnify_upsells)) : ?>
				<div class="omnify-pdp-related-section">
					<h2><?php esc_html_e('You May Also Like', 'omnifywp-ecommerce'); ?></h2>
					<div class="omnify-storefront-grid omnify-storefront-grid--cols-4 omnify-cols-4">
						<?php foreach ($omnify_upsells as $omnify_rp) :
							$omnify_render_pdp_card($omnify_rp);
						endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Cross-sells Section -->
			<?php if (! empty($omnify_cross_sells) && is_array($omnify_cross_sells)) : ?>
				<div class="omnify-pdp-related-section">
					<h2><?php esc_html_e('Frequently Bought Together', 'omnifywp-ecommerce'); ?></h2>
					<div class="omnify-storefront-grid omnify-storefront-grid--cols-4 omnify-cols-4">
						<?php foreach ($omnify_cross_sells as $omnify_rp) :
							$omnify_render_pdp_card($omnify_rp);
						endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Related & Recently Viewed Full-Width Sections -->
			<?php 
			$omnify_has_recent = $omnify_show_recently_viewed && ! empty($omnify_recently_viewed_products);
			?>

			<!-- Related Products (Full-Width Grid) -->
			<?php if (! empty($omnify_related_products) && is_array($omnify_related_products)) : ?>
				<section class="omnify-pdp-related-section">
					<div class="omnify-pdp-section-header">
						<h2><?php esc_html_e('Related Products', 'omnifywp-ecommerce'); ?></h2>
					</div>
					<div class="omnify-storefront-grid omnify-storefront-grid--cols-4 omnify-cols-4">
						<?php foreach ($omnify_related_products as $omnify_rp) :
							$omnify_render_pdp_card($omnify_rp);
						endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<!-- Recently Viewed (Full-Width Grid) -->
			<?php if ($omnify_has_recent && ! empty($omnify_recently_viewed_products)) : ?>
				<section class="omnify-pdp-recent-section">
					<div class="omnify-pdp-section-header">
						<h2><?php esc_html_e('Recently Viewed', 'omnifywp-ecommerce'); ?></h2>
					</div>
					<div class="omnify-storefront-grid omnify-storefront-grid--cols-4 omnify-cols-4">
						<?php foreach (array_slice($omnify_recently_viewed_products, 0, 4) as $omnify_rp) :
							$omnify_render_pdp_card($omnify_rp);
						endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div><!-- /.omnify-pdp-after-sections -->
	<?php endif; // $omnify_has_after_sections ?>
</div><!-- /.omnify-storefront-wrapper -->
