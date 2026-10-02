<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_action = $omnify_template_vars['omnify_action'] ?? null;
$omnify_active_chips = $omnify_template_vars['omnify_active_chips'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_all_brands = $omnify_template_vars['omnify_all_brands'] ?? null;
$omnify_all_categories = $omnify_template_vars['omnify_all_categories'] ?? null;
$omnify_all_digital_products = $omnify_template_vars['omnify_all_digital_products'] ?? null;
$omnify_all_other_products = $omnify_template_vars['omnify_all_other_products'] ?? null;
$omnify_all_tags = $omnify_template_vars['omnify_all_tags'] ?? null;
$omnify_attr = $omnify_template_vars['omnify_attr'] ?? null;
$omnify_attr_desc = $omnify_template_vars['omnify_attr_desc'] ?? null;
$omnify_attr_option_meta = $omnify_template_vars['omnify_attr_option_meta'] ?? null;
$omnify_attr_type = $omnify_template_vars['omnify_attr_type'] ?? null;
$omnify_attrs = $omnify_template_vars['omnify_attrs'] ?? null;
$omnify_branch = $omnify_template_vars['omnify_branch'] ?? null;
$omnify_brand = $omnify_template_vars['omnify_brand'] ?? null;
$omnify_brands_table = $omnify_template_vars['omnify_brands_table'] ?? null;
$omnify_bundled_ids = $omnify_template_vars['omnify_bundled_ids'] ?? null;
$omnify_cat = $omnify_template_vars['omnify_cat'] ?? null;
$omnify_cat_parent = $omnify_template_vars['omnify_cat_parent'] ?? null;
$omnify_categories = $omnify_template_vars['omnify_categories'] ?? null;
$omnify_categories_table = $omnify_template_vars['omnify_categories_table'] ?? null;
$omnify_category = $omnify_template_vars['omnify_category'] ?? null;
$omnify_category_tree = $omnify_template_vars['omnify_category_tree'] ?? null;
$omnify_change_class = $omnify_template_vars['omnify_change_class'] ?? null;
$omnify_change_text = $omnify_template_vars['omnify_change_text'] ?? null;
$omnify_change_val = $omnify_template_vars['omnify_change_val'] ?? null;
$omnify_children = $omnify_template_vars['omnify_children'] ?? null;
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_content = $omnify_template_vars['omnify_content'] ?? null;
$omnify_cross_sell_ids = $omnify_template_vars['omnify_cross_sell_ids'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_date_label = $omnify_template_vars['omnify_date_label'] ?? null;
$omnify_default_trust_text = $omnify_template_vars['omnify_default_trust_text'] ?? null;
$omnify_default_trust_title = $omnify_template_vars['omnify_default_trust_title'] ?? null;
$omnify_depth = $omnify_template_vars['omnify_depth'] ?? null;
$omnify_dl_count = $omnify_template_vars['omnify_dl_count'] ?? null;
$omnify_downloads_table = $omnify_template_vars['omnify_downloads_table'] ?? null;
$omnify_dp = $omnify_template_vars['omnify_dp'] ?? null;
$omnify_exist_brand = $omnify_template_vars['omnify_exist_brand'] ?? null;
$omnify_exist_cat = $omnify_template_vars['omnify_exist_cat'] ?? null;
$omnify_exist_tag = $omnify_template_vars['omnify_exist_tag'] ?? null;
$omnify_existing_gallery = $omnify_template_vars['omnify_existing_gallery'] ?? null;
$omnify_existing_gallery_ids = $omnify_template_vars['omnify_existing_gallery_ids'] ?? null;
$omnify_file = $omnify_template_vars['omnify_file'] ?? null;
$omnify_file_count = $omnify_template_vars['omnify_file_count'] ?? null;
$omnify_files = $omnify_template_vars['omnify_files'] ?? null;
$omnify_files_table = $omnify_template_vars['omnify_files_table'] ?? null;
$omnify_filter_categories = $omnify_template_vars['omnify_filter_categories'] ?? null;
$omnify_g_attr = $omnify_template_vars['omnify_g_attr'] ?? null;
$omnify_g_brand = $omnify_template_vars['omnify_g_brand'] ?? null;
$omnify_g_tag = $omnify_template_vars['omnify_g_tag'] ?? null;
$omnify_gid = $omnify_template_vars['omnify_gid'] ?? null;
$omnify_gidx = $omnify_template_vars['omnify_gidx'] ?? null;
$omnify_global_attributes = $omnify_template_vars['omnify_global_attributes'] ?? null;
$omnify_global_brands = $omnify_template_vars['omnify_global_brands'] ?? null;
$omnify_global_categories = $omnify_template_vars['omnify_global_categories'] ?? null;
$omnify_global_tags = $omnify_template_vars['omnify_global_tags'] ?? null;
$omnify_gurl = $omnify_template_vars['omnify_gurl'] ?? null;
$omnify_hide_digital_sections = $omnify_template_vars['omnify_hide_digital_sections'] ?? null;
$omnify_index = $omnify_template_vars['omnify_index'] ?? null;
$omnify_inventory_logs = $omnify_template_vars['omnify_inventory_logs'] ?? null;
$omnify_is_checked = $omnify_template_vars['omnify_is_checked'] ?? null;
$omnify_is_trashed = $omnify_template_vars['omnify_is_trashed'] ?? null;
$omnify_item_desc = $omnify_template_vars['omnify_item_desc'] ?? null;
$omnify_k = $omnify_template_vars['omnify_k'] ?? null;
$omnify_log = $omnify_template_vars['omnify_log'] ?? null;
$omnify_logs_table = $omnify_template_vars['omnify_logs_table'] ?? null;
$omnify_node = $omnify_template_vars['omnify_node'] ?? null;
$omnify_op = $omnify_template_vars['omnify_op'] ?? null;
$omnify_opt = $omnify_template_vars['omnify_opt'] ?? null;
$omnify_order_items_table = $omnify_template_vars['omnify_order_items_table'] ?? null;
$omnify_orders_table = $omnify_template_vars['omnify_orders_table'] ?? null;
$omnify_parent_slug = $omnify_template_vars['omnify_parent_slug'] ?? null;
$omnify_preview_url = $omnify_template_vars['omnify_preview_url'] ?? null;
$omnify_prod = $omnify_template_vars['omnify_prod'] ?? null;
$omnify_prod_files = $omnify_template_vars['omnify_prod_files'] ?? null;
$omnify_prod_has_physical_meta = $omnify_template_vars['omnify_prod_has_physical_meta'] ?? null;
$omnify_prod_is_physical = $omnify_template_vars['omnify_prod_is_physical'] ?? null;
$omnify_prod_saved_kind = $omnify_template_vars['omnify_prod_saved_kind'] ?? null;
$omnify_prod_stats = $omnify_template_vars['omnify_prod_stats'] ?? null;
$omnify_prod_variation_settings = $omnify_template_vars['omnify_prod_variation_settings'] ?? null;
$omnify_product = $omnify_template_vars['omnify_product'] ?? null;
$omnify_product_files_repo = $omnify_template_vars['omnify_product_files_repo'] ?? null;
$omnify_product_has_physical_meta = $omnify_template_vars['omnify_product_has_physical_meta'] ?? null;
$omnify_product_id = $omnify_template_vars['omnify_product_id'] ?? null;
$omnify_product_kind = $omnify_template_vars['omnify_product_kind'] ?? null;
$omnify_product_kind_files = $omnify_template_vars['omnify_product_kind_files'] ?? null;
$omnify_product_repo = $omnify_template_vars['omnify_product_repo'] ?? null;
$omnify_product_structure = $omnify_template_vars['omnify_product_structure'] ?? null;
$omnify_product_type = $omnify_template_vars['omnify_product_type'] ?? null;
$omnify_products = $omnify_template_vars['omnify_products'] ?? ($omnify_template_vars['products'] ?? null);
$omnify_products_table = $omnify_template_vars['omnify_products_table'] ?? null;
$omnify_selected_categories = $omnify_template_vars['omnify_selected_categories'] ?? null;
$omnify_selected_val = $omnify_template_vars['omnify_selected_val'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? ($omnify_template_vars['settings'] ?? null);
$omnify_sidx = $omnify_template_vars['omnify_sidx'] ?? null;
$omnify_signed_preview_url = $omnify_template_vars['omnify_signed_preview_url'] ?? null;
$omnify_signed_urls = $omnify_template_vars['omnify_signed_urls'] ?? null;
$omnify_slugs = $omnify_template_vars['omnify_slugs'] ?? null;
$omnify_spec = $omnify_template_vars['omnify_spec'] ?? null;
$omnify_specs = $omnify_template_vars['omnify_specs'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_tag = $omnify_template_vars['omnify_tag'] ?? null;
$omnify_tags_table = $omnify_template_vars['omnify_tags_table'] ?? null;
$omnify_tree = $omnify_template_vars['omnify_tree'] ?? null;
$omnify_upsell_ids = $omnify_template_vars['omnify_upsell_ids'] ?? null;
$omnify_v = $omnify_template_vars['omnify_v'] ?? null;
$omnify_v_idx = $omnify_template_vars['omnify_v_idx'] ?? null;
$omnify_variation_settings = $omnify_template_vars['omnify_variation_settings'] ?? null;
$omnify_variations = $omnify_template_vars['omnify_variations'] ?? null;
$omnify_vars_table = $omnify_template_vars['omnify_vars_table'] ?? null;
$omnify_vf = $omnify_template_vars['omnify_vf'] ?? null;
$omnify_view_all_files = $omnify_template_vars['omnify_view_all_files'] ?? null;
$omnify_view_files = $omnify_template_vars['omnify_view_files'] ?? null;
$omnify_view_has_physical_meta = $omnify_template_vars['omnify_view_has_physical_meta'] ?? null;
$omnify_view_is_physical = $omnify_template_vars['omnify_view_is_physical'] ?? null;
$omnify_view_kind_label = $omnify_template_vars['omnify_view_kind_label'] ?? null;
$omnify_view_price = $omnify_template_vars['omnify_view_price'] ?? null;
$omnify_view_sale_price = $omnify_template_vars['omnify_view_sale_price'] ?? null;
$omnify_view_saved_kind = $omnify_template_vars['omnify_view_saved_kind'] ?? null;
$omnify_view_stock_labels = $omnify_template_vars['omnify_view_stock_labels'] ?? null;
$omnify_view_stock_status = $omnify_template_vars['omnify_view_stock_status'] ?? null;
$omnify_view_structure_label = $omnify_template_vars['omnify_view_structure_label'] ?? null;
$omnify_view_variation_settings = $omnify_template_vars['omnify_view_variation_settings'] ?? null;


/**
 * Admin Products Template
 *
 * @package Omnify
 */
$omnify_products = is_array($omnify_products) ? $omnify_products : [];
$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];

$omnify_currency_symbol = function($omnify_currency) {
	switch ($omnify_currency) {
		case 'EUR': return '€';
		case 'GBP': return 'Â£';
		case 'JPY': return 'Â¥';
		case 'CAD': return 'C$';
		case 'AUD': return 'A$';
		default: return '$';
	}
};
$omnify_symbol = $omnify_currency_symbol($omnify_settings['default_currency'] ?? 'USD');

$omnify_action     = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
$omnify_product_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;

$omnify_product = null;
if ($omnify_product_id && in_array($omnify_action, ['edit', 'update', 'view'], true)) {
	$omnify_product = $omnify_product_repo->find($omnify_product_id);
}

global $wpdb;
$omnify_categories_table = $wpdb->prefix . 'omnify_product_categories';
$omnify_tags_table       = $wpdb->prefix . 'omnify_product_tags';
$omnify_brands_table     = $wpdb->prefix . 'omnify_product_brands';
$omnify_products_table   = $wpdb->prefix . 'omnify_products';
$omnify_all_categories = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_categories_table));
$omnify_all_tags = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_tags_table));
$omnify_all_brands = \Omnify\eCommerce\Support\Omnify_DB::get_col($wpdb, $wpdb->prepare('SELECT DISTINCT name FROM %i ORDER BY name ASC', $omnify_brands_table));
$omnify_all_categories = is_array($omnify_all_categories) ? $omnify_all_categories : [];
$omnify_all_tags = is_array($omnify_all_tags) ? $omnify_all_tags : [];
$omnify_all_brands = is_array($omnify_all_brands) ? $omnify_all_brands : [];
$omnify_global_categories = get_option('omnify_global_categories', []);
$omnify_global_brands = get_option('omnify_global_brands', []);
$omnify_all_digital_products = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
	$wpdb->prepare(
		"SELECT id, name, status, sku FROM %i WHERE type = 'download' AND id != %d ORDER BY name ASC",
		$omnify_products_table,
		$omnify_product_id
	),
	ARRAY_A
);
$omnify_all_digital_products = is_array($omnify_all_digital_products) ? $omnify_all_digital_products : [];

$omnify_all_other_products = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
	$wpdb->prepare(
		'SELECT id, name, status, sku FROM %i WHERE id != %d ORDER BY name ASC',
		$omnify_products_table,
		$omnify_product_id
	),
	ARRAY_A
);
$omnify_all_other_products = is_array($omnify_all_other_products) ? $omnify_all_other_products : [];
$omnify_global_tags = get_option('omnify_global_tags', []);
$omnify_global_attributes = get_option('omnify_global_attributes', []);
$omnify_global_categories = is_array($omnify_global_categories) ? $omnify_global_categories : [];
$omnify_global_brands     = is_array($omnify_global_brands) ? $omnify_global_brands : [];
$omnify_global_tags       = is_array($omnify_global_tags) ? $omnify_global_tags : [];
$omnify_global_attributes = is_array($omnify_global_attributes) ? $omnify_global_attributes : [];
?>

<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'products'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<?php if ($omnify_action === 'new') : ?>
				<h1><?php esc_html_e('Add Product', 'omnifywp-ecommerce'); ?></h1>
				<p><?php esc_html_e('Create a new product and upload digital download files directly.', 'omnifywp-ecommerce'); ?></p>
			<?php elseif ($omnify_action === 'edit' && $omnify_product) : ?>
				<h1><?php
				// translators: %s: placeholder value. echo esc_html(sprintf(__('Edit: %s', 'omnifywp-ecommerce'), $product['name'])); ?></h1>
				<p><?php esc_html_e('Update product details and digital files.', 'omnifywp-ecommerce'); ?></p>
			<?php elseif ($omnify_action === 'view' && $omnify_product) : ?>
				<h1><?php echo esc_html($omnify_product['name']); ?></h1>
				<p><?php esc_html_e('Product overview, files, and performance.', 'omnifywp-ecommerce'); ?></p>
			<?php else : ?>
				<h1><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></h1>
				<p><?php esc_html_e('Manage your digital storefront products.', 'omnifywp-ecommerce'); ?></p>
			<?php endif; ?>
		</div>
		<div class="omnify-header-row__actions">
			<?php if ($omnify_action === 'view' && $omnify_product) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product['id'])); ?>" class="omnify-button omnify-button--secondary">
					<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
					<?php esc_html_e('Edit Product', 'omnifywp-ecommerce'); ?>
				</a>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products')); ?>" class="omnify-button omnify-button--secondary" style="display:inline-flex; align-items:center; gap:4px;">
					<span class="dashicons dashicons-arrow-left-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
					<?php esc_html_e('All Products', 'omnifywp-ecommerce'); ?>
				</a>
			<?php elseif ($omnify_action === 'new' || ($omnify_action === 'edit' && $omnify_product)) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products')); ?>" class="omnify-button omnify-button--secondary" style="display:inline-flex; align-items:center; gap:4px;">
					<span class="dashicons dashicons-arrow-left-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
					<?php esc_html_e('Back to Products', 'omnifywp-ecommerce'); ?>
				</a>
			<?php elseif (empty($omnify_action)) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=new')); ?>" class="omnify-button omnify-button--primary">
					<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
					<?php esc_html_e('Create Product', 'omnifywp-ecommerce'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>
	<?php if (empty($omnify_action)) { include __DIR__ . '/partials/product-subtabs.php'; } ?>
	<?php if ($omnify_action === 'view' && $omnify_product) : ?>
		<?php
		$omnify_view_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
		$omnify_view_all_files = $omnify_product_files_repo->all_for_product((int) $omnify_product['id']);
		$omnify_view_saved_kind = $omnify_product['product_kind'] ?? ($omnify_view_variation_settings['product_kind'] ?? 'digital');
		$omnify_view_has_physical_meta = ! empty($omnify_product['sku']) || ! empty($omnify_product['weight']) || ! empty($omnify_product['length']) || ! empty($omnify_product['width']) || ! empty($omnify_product['height']);
		$omnify_view_is_physical = ('physical' === ($omnify_product['type'] ?? 'download'))
			|| ('variable' === ($omnify_product['type'] ?? 'download') && 'physical' === $omnify_view_saved_kind)
			|| ('variable' === ($omnify_product['type'] ?? 'download') && empty($omnify_view_all_files) && $omnify_view_has_physical_meta);
		$omnify_view_files   = $omnify_view_is_physical ? [] : $omnify_view_all_files;
		$omnify_file_count   = count($omnify_view_files);
		$omnify_view_kind_label = $omnify_view_is_physical ? __('Physical', 'omnifywp-ecommerce') : __('Digital', 'omnifywp-ecommerce');
		$omnify_view_structure_label = 'variable' === ($omnify_product['type'] ?? 'download') ? __('Variable', 'omnifywp-ecommerce') : __('Simple', 'omnifywp-ecommerce');
		$omnify_view_sale_price = $omnify_product['sale_price'] ?? null;
		$omnify_view_price = null !== $omnify_view_sale_price ? (float) $omnify_view_sale_price : (float) $omnify_product['price'];
		$omnify_view_stock_status = $omnify_product['stock_status'] ?? 'instock';
		$omnify_view_stock_labels = [
			'instock'     => __('In stock', 'omnifywp-ecommerce'),
			'outofstock'  => __('Out of stock', 'omnifywp-ecommerce'),
			'onbackorder' => __('On backorder', 'omnifywp-ecommerce'),
		];
		?>
		<!-- ── Product Detail View ── -->
		<div class="omnify-product-detail-layout">
			<!-- Left column: details + files -->
			<div>
				<!-- Overview Card -->
				<div class="omnify-card omnify-product-hero-card">
					<div class="omnify-product-hero">
						<div class="omnify-product-hero__media">
							<?php if ($omnify_product['thumbnail_url']) : ?>
								<img src="<?php echo esc_url($omnify_product['thumbnail_url']); ?>" alt="<?php echo esc_attr($omnify_product['name']); ?>" />
							<?php else : ?>
								<div class="omnify-product-hero__placeholder"><?php echo esc_html(strtoupper(substr((string) $omnify_product['name'], 0, 1))); ?></div>
							<?php endif; ?>
						</div>
						<div class="omnify-product-hero__body">
							<div class="omnify-product-hero__badges">
								<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_product['status']); ?>"><?php echo esc_html(ucfirst((string) $omnify_product['status'])); ?></span>
								<span class="omnify-product-type-pill"><?php
								// translators: %1$s: placeholder value, %2$s: placeholder value. echo esc_html(sprintf(__('%1$s %2$s', 'omnifywp-ecommerce'), $view_kind_label, $view_structure_label)); ?></span>
							</div>
							<h2><?php echo esc_html($omnify_product['name']); ?></h2>
							<?php if (!empty($omnify_product['short_description'])): ?>
								<p style="margin:4px 0 12px; color:#475569; font-size:14px; line-height:1.4; max-width:620px;"><?php echo esc_html($omnify_product['short_description']); ?></p>
							<?php endif; ?>
							<div class="omnify-product-hero__price">
								<span><?php echo esc_html($omnify_symbol . number_format($omnify_view_price, 2)); ?></span>
								<?php if (null !== $omnify_view_sale_price) : ?>
									<del><?php echo esc_html($omnify_symbol . number_format((float) $omnify_product['price'], 2)); ?></del>
								<?php endif; ?>
							</div>
							<div class="omnify-product-hero__meta">
								<span><?php echo esc_html($omnify_view_stock_labels[$omnify_view_stock_status] ?? ucfirst((string) $omnify_view_stock_status)); ?></span>
								<?php if (! empty($omnify_product['sku'])) : ?>
									<span><?php
									// translators: %s: placeholder value. echo esc_html(sprintf(__('SKU: %s', 'omnifywp-ecommerce'), $product['sku'])); ?></span>
								<?php endif; ?>
								<?php if (! $omnify_view_is_physical) : ?>
									<span><?php
									// translators: %d: placeholder value. echo esc_html(sprintf(_n('%d file', '%d files', $file_count, 'omnifywp-ecommerce'), $file_count)); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="omnify-detail-tile-grid">
						<div class="omnify-detail-tile">
							<span><?php esc_html_e('Product Kind', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo esc_html($omnify_view_kind_label); ?></strong>
						</div>
						<div class="omnify-detail-tile">
							<span><?php esc_html_e('Sales Type', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo esc_html($omnify_view_structure_label); ?></strong>
						</div>
						<div class="omnify-detail-tile">
							<span><?php esc_html_e('Slug', 'omnifywp-ecommerce'); ?></span>
							<strong><code><?php echo esc_html($omnify_product['slug']); ?></code></strong>
						</div>
						<div class="omnify-detail-tile">
							<span><?php esc_html_e('Created', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo ! empty($omnify_product['created_at']) ? esc_html(date_i18n(get_option('date_format'), strtotime($omnify_product['created_at']))) : esc_html__('Not available', 'omnifywp-ecommerce'); ?></strong>
						</div>
					</div>

					<div class="omnify-product-taxonomy-list">
						<?php if (!empty($omnify_product['categories'])) : ?>
						<div>
							<span><?php esc_html_e('Categories', 'omnifywp-ecommerce'); ?></span>
							<div><?php foreach ($omnify_product['categories'] as $omnify_category) : ?><mark><?php echo esc_html($omnify_category); ?></mark><?php endforeach; ?></div>
						</div>
						<?php endif; ?>
						<?php if (!empty($omnify_product['tags'])) : ?>
						<div>
							<span><?php esc_html_e('Tags', 'omnifywp-ecommerce'); ?></span>
							<div><?php foreach ($omnify_product['tags'] as $omnify_tag) : ?><mark><?php echo esc_html($omnify_tag); ?></mark><?php endforeach; ?></div>
						</div>
						<?php endif; ?>
						<?php if (!empty($omnify_product['brands'])) : ?>
						<div>
							<span><?php esc_html_e('Brands', 'omnifywp-ecommerce'); ?></span>
							<div><?php foreach ($omnify_product['brands'] as $omnify_brand) : ?><mark><?php echo esc_html($omnify_brand); ?></mark><?php endforeach; ?></div>
						</div>
						<?php endif; ?>
					</div>

					<?php if (!empty($omnify_product['description'])) : ?>
						<div class="omnify-product-description">
							<h3><?php esc_html_e('Description', 'omnifywp-ecommerce'); ?></h3>
							<div>
								<?php echo wp_kses_post(wpautop($omnify_product['description'])); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<?php if (! $omnify_view_is_physical) : ?>
				<!-- Files Card -->
				<div class="omnify-card">
					<div class="omnify-card__header">
						<h2 style="margin-bottom:0;"><?php esc_html_e('Digital Files', 'omnifywp-ecommerce'); ?></h2>
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product['id'])); ?>" class="omnify-button omnify-button--secondary omnify-button--sm">
							<span class="dashicons dashicons-upload" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('Upload Files', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
					<?php if (empty($omnify_view_files)) : ?>
						<div class="omnify-empty-state" style="padding: 30px;">
							<div class="omnify-empty-state__icon"><span class="dashicons dashicons-portfolio" style="font-size: 36px; width: 36px; height: 36px; color: var(--omnify-gray-400);"></span></div>
							<p><?php esc_html_e('No files attached yet. Go to Edit Product to upload.', 'omnifywp-ecommerce'); ?></p>
						</div>
					<?php else : ?>
						<div class="omnify-table-wrapper">
							<table class="omnify-table">
								<thead>
									<tr>
										<th><?php esc_html_e('File Name', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Size', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Version', 'omnifywp-ecommerce'); ?></th>
										<th style="text-align:right;"><?php esc_html_e('Preview Link', 'omnifywp-ecommerce'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($omnify_view_files as $omnify_vf) :
										$omnify_preview_url = $omnify_signed_urls->create((int) $omnify_vf['id'], 86400);
									?>
										<tr>
											<td><strong><?php echo esc_html($omnify_vf['file_name']); ?></strong></td>
											<td><span style="font-size:11px; color: var(--omnify-gray-600);"><?php echo esc_html($omnify_vf['file_type']); ?></span></td>
											<td><?php echo esc_html($omnify_vf['size_label']); ?></td>
											<td><span class="omnify-status omnify-status--completed" style="font-size:10px;">v<?php echo esc_html($omnify_vf['version']); ?></span></td>
											<td style="text-align:right;">
												<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm js-copy-url" data-url="<?php echo esc_url($omnify_preview_url); ?>">
													<?php esc_html_e('Copy URL (24h)', 'omnifywp-ecommerce'); ?>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Right column: stats -->
			<div>
				<div class="omnify-card omnify-card--accent">
					<h2 style="margin-bottom: 16px;"><?php esc_html_e('Performance', 'omnifywp-ecommerce'); ?></h2>
					<?php
					// Fetch basic stats from orders for this product
					global $wpdb;
					$omnify_order_items_table = $wpdb->prefix . 'omnify_order_items';
					$omnify_orders_table      = $wpdb->prefix . 'omnify_orders';
					$omnify_prod_stats = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
						"SELECT COUNT(DISTINCT o.id) AS order_count,
							COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue,
							COALESCE(SUM(oi.quantity), 0) AS units_sold
						FROM %i oi
						JOIN %i o ON o.id = oi.order_id
						WHERE oi.product_id = %d AND o.status IN ('completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered', 'refund_requested')",
						$omnify_order_items_table,
						$omnify_orders_table,
						$omnify_product['id']
					), ARRAY_A);
					$omnify_prod_stats = $omnify_prod_stats ?: ['order_count' => 0, 'revenue' => 0, 'units_sold' => 0];

					$omnify_dl_count = 0;
					if (! $omnify_view_is_physical) {
						$omnify_downloads_table = $wpdb->prefix . 'omnify_download_logs';
						$omnify_files_table     = $wpdb->prefix . 'omnify_product_files';
						$omnify_dl_count = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
							"SELECT COUNT(*) FROM %i dl
							JOIN %i pf ON pf.id = dl.file_id
							WHERE pf.product_id = %d",
							$omnify_downloads_table,
							$omnify_files_table,
							$omnify_product['id']
						)) ?? 0;
					}
					?>
					<div class="omnify-performance-grid">
						<div class="omnify-performance-tile">
							<span><?php esc_html_e('Total Revenue', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo esc_html($omnify_symbol . number_format((float)$omnify_prod_stats['revenue'], 2)); ?></strong>
						</div>
						<div class="omnify-performance-tile">
							<span><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo esc_html(number_format((int)$omnify_prod_stats['order_count'])); ?></strong>
						</div>
						<div class="omnify-performance-tile">
							<span><?php esc_html_e('Units Sold', 'omnifywp-ecommerce'); ?></span>
							<strong><?php echo esc_html(number_format((int)$omnify_prod_stats['units_sold'])); ?></strong>
						</div>
						<?php if (! $omnify_view_is_physical) : ?>
							<div class="omnify-performance-tile">
								<span><?php esc_html_e('Total Downloads', 'omnifywp-ecommerce'); ?></span>
								<strong><?php echo esc_html(number_format((int)$omnify_dl_count)); ?></strong>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Quick actions -->
				<div class="omnify-card">
					<h2 style="margin-bottom: 14px;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></h2>
					<div style="display: flex; flex-direction: column; gap: 8px;">
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_product['id'])); ?>" class="omnify-button omnify-button--primary omnify-button--full">
							<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('Edit Product', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(add_query_arg(['omnify_product' => $omnify_product['slug']], home_url('/storefront/'))); ?>" target="_blank" class="omnify-button omnify-button--secondary omnify-button--full">
							<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('View on Storefront', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php
						// translators: %s: placeholder value. echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_product&id=' . $product['id']), 'omnify_delete_product_' . $product['id'])); ?>" class="omnify-button omnify-button--danger omnify-button--full js-confirm-delete" data-message="<?php
						// translators: %s: placeholder value. echo esc_attr(sprintf(__('Delete "%s"? All files and data will be permanently removed.', 'omnifywp-ecommerce'), $product['name'])); ?>">
							<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('Delete Product', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php elseif ($omnify_action === 'new' || ($omnify_action === 'edit' && $omnify_product)) : ?>
		<?php
		if (! function_exists('omnify_build_category_tree')) {
			function omnify_build_category_tree(array $omnify_categories, string $omnify_parent_slug = ''): array {
				$omnify_branch = [];
				foreach ($omnify_categories as $omnify_cat) {
					$omnify_cat_parent = $omnify_cat['parent'] ?? '';
					if ($omnify_cat_parent === $omnify_parent_slug) {
						$omnify_children = omnify_build_category_tree($omnify_categories, $omnify_cat['slug']);
						if (! empty($omnify_children)) {
							$omnify_cat['children'] = $omnify_children;
						}
						$omnify_branch[] = $omnify_cat;
					}
				}
				return $omnify_branch;
			}
		}

		if (! function_exists('omnify_get_category_tree')) {
			function omnify_get_category_tree(array $omnify_categories): array {
				$omnify_slugs = array_column($omnify_categories, 'slug');
				// If parent slug is not valid, reset parent to empty string
				foreach ($omnify_categories as &$omnify_cat) {
					if (! empty($omnify_cat['parent']) && ! in_array($omnify_cat['parent'], $omnify_slugs, true)) {
						$omnify_cat['parent'] = '';
					}
				}
				unset($omnify_cat);
				return omnify_build_category_tree($omnify_categories, '');
			}
		}

		if (! function_exists('omnify_render_categories_checkbox_tree')) {
			function omnify_render_categories_checkbox_tree(array $omnify_tree, array $omnify_selected_categories, int $omnify_depth = 0) {
				if (empty($omnify_tree)) return;
				
				echo '<ul style="list-style: none; margin: 0; padding-left: ' . ($omnify_depth > 0 ? '20px' : '0') . '; display: flex; flex-direction: column; gap: 6px;">';
				foreach ($omnify_tree as $omnify_node) {
					$omnify_is_checked = in_array($omnify_node['name'], $omnify_selected_categories, true);
					echo '<li>';
					echo '<label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none; margin-bottom: 2px;">';
					echo '<input type="checkbox" class="omnify-checkbox js-global-cat-checkbox" data-name="' . esc_attr($omnify_node['name']) . '" ' . checked($omnify_is_checked, true, false) . '  />';
					echo '<span>' . esc_html($omnify_node['name']) . '</span>';
					echo '</label>';
					if (! empty($omnify_node['children'])) {
						omnify_render_categories_checkbox_tree($omnify_node['children'], $omnify_selected_categories, $omnify_depth + 1);
					}
					echo '</li>';
				}
				echo '</ul>';
			}
		}

		$omnify_category_tree = omnify_get_category_tree($omnify_global_categories);
		$omnify_variation_settings = wp_parse_args(
			$omnify_product ? ($omnify_product['variation_settings'] ?? []) : [],
			[
				'product_kind'   => 'digital',
				'selector_style' => 'buttons',
				'swatch_shape'   => 'round',
				'swatch_size'    => 'medium',
				'out_of_stock'   => 'cross',
			]
		);
		$omnify_product_type = $omnify_product ? ($omnify_product['type'] ?? 'download') : 'download';
		$omnify_product_kind = $omnify_product ? ($omnify_product['product_kind'] ?? (('physical' === $omnify_product_type || ('variable' === $omnify_product_type && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'))) ? 'physical' : 'digital')) : 'digital';
		if ($omnify_product && 'variable' === $omnify_product_type && 'digital' === $omnify_product_kind) {
			$omnify_product_kind_files = $omnify_product_files_repo->all_for_product((int) $omnify_product['id']);
			$omnify_product_has_physical_meta = ! empty($omnify_product['sku']) || ! empty($omnify_product['weight']) || ! empty($omnify_product['length']) || ! empty($omnify_product['width']) || ! empty($omnify_product['height']);
			if (empty($omnify_product_kind_files) && $omnify_product_has_physical_meta) {
				$omnify_product_kind = 'physical';
			}
		}
		$omnify_product_structure = 'variable' === $omnify_product_type ? 'variable' : 'simple';
		$omnify_hide_digital_sections = 'physical' === $omnify_product_kind;
		$omnify_default_trust_title = 'physical' === $omnify_product_kind ? __('Secure Delivery', 'omnifywp-ecommerce') : __('Instant Secure Access', 'omnifywp-ecommerce');
		$omnify_default_trust_text  = 'physical' === $omnify_product_kind
			? __('Order confirmed. Shipped fast with tracking link sent to your email.', 'omnifywp-ecommerce')
			: __('Files available for download immediately after payment. Secure links emailed instantly.', 'omnifywp-ecommerce');
		?>

		<!-- Create / Edit Form - Multistep Modern Wizard -->
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="omnify-product-editor-form" id="omnify-product-wizard-form">
			<?php wp_nonce_field('omnify_save_product', 'omnify_product_nonce'); ?>
			<input type="hidden" name="action" value="omnify_save_product" />
			<?php if ($omnify_product) : ?>
				<input type="hidden" name="id" value="<?php echo esc_attr($omnify_product['id']); ?>" />
			<?php endif; ?>

			<div class="omnify-product-wizard">
				<div id="omnify-product-form-alert" class="omnify-product-form-alert" role="alert" hidden></div>
				<div class="omnify-wizard-header">
					<div>
						<strong style="font-size:15px;"><?php esc_html_e('Product Creation Wizard', 'omnifywp-ecommerce'); ?></strong>
						<span style="color:var(--omnify-gray-500); font-size:12px; margin-left:8px;"><?php esc_html_e('Step-by-step setup', 'omnifywp-ecommerce'); ?></span>
					</div>
				</div>

				<!-- Stepper -->
				<div class="omnify-wizard-steps" id="omnify-wizard-steps">
					<div class="omnify-wizard-step" data-step="1">
						<span class="step-num">1</span>
						<span><?php esc_html_e('Basics', 'omnifywp-ecommerce'); ?></span>
					</div>
					<div class="omnify-wizard-step" data-step="2">
						<span class="step-num">2</span>
						<span><?php esc_html_e('Pricing', 'omnifywp-ecommerce'); ?></span>
					</div>
					<div class="omnify-wizard-step" data-step="3">
						<span class="step-num">3</span>
						<span><?php esc_html_e('Content', 'omnifywp-ecommerce'); ?></span>
					</div>
					<div class="omnify-wizard-step" data-step="4">
						<span class="step-num">4</span>
						<span><?php esc_html_e('Options & Save', 'omnifywp-ecommerce'); ?></span>
					</div>
				</div>

				<!-- Progress bar -->
				<div class="omnify-wizard-progress" style="margin-bottom:20px;">
					<div class="omnify-wizard-progress-bar" id="omnify-wizard-progress" style="width: 25%;"></div>
				</div>

				<!-- Step Panels -->
				<div class="omnify-wizard-panels">

					<!-- STEP 1: Basics -->
					<div class="omnify-wizard-panel active" data-panel="1">
						<div class="omnify-card omnify-editor-section-card">
							<div class="omnify-editor-section-card__header">
								<div>
									<h2><?php esc_html_e('Product Basics', 'omnifywp-ecommerce'); ?></h2>
									<p><?php esc_html_e('Name, short description for listings, slug, type.', 'omnifywp-ecommerce'); ?></p>
								</div>
								<span class="omnify-editor-required-note"><?php esc_html_e('* Required', 'omnifywp-ecommerce'); ?></span>
							</div>
							<div class="omnify-form-grid" style="max-width: 100%;">
								<div class="omnify-form-group" style="margin-top: 0;">
									<label><?php esc_html_e('Product Kind', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></label>
									<div class="omnify-choice-grid">
										<label class="omnify-choice-card">
											<input type="radio" name="product_kind" value="digital" <?php checked($omnify_product_kind, 'digital'); ?> required />
											<span>
												<strong><?php esc_html_e('Digital', 'omnifywp-ecommerce'); ?></strong>
												<small><?php esc_html_e('Files, downloads, licenses.', 'omnifywp-ecommerce'); ?></small>
											</span>
										</label>
										<label class="omnify-choice-card">
											<input type="radio" name="product_kind" value="physical" <?php checked($omnify_product_kind, 'physical'); ?> required />
											<span>
												<strong><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></strong>
												<small><?php esc_html_e('Inventory + shipping.', 'omnifywp-ecommerce'); ?></small>
											</span>
										</label>
										<label class="omnify-choice-card">
											<input type="radio" name="product_kind" value="bundle" <?php checked($omnify_product_kind, 'bundle'); ?> required />
											<span>
												<strong><?php esc_html_e('Bundle', 'omnifywp-ecommerce'); ?></strong>
												<small><?php esc_html_e('Package of digital products.', 'omnifywp-ecommerce'); ?></small>
											</span>
										</label>
									</div>
								</div>

								<div class="omnify-form-group" style="margin-top: 4px;">
									<label><?php esc_html_e('Sales Type', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></label>
									<div class="omnify-choice-grid">
										<label class="omnify-choice-card">
											<input type="radio" name="product_structure" value="simple" <?php checked($omnify_product_structure, 'simple'); ?> required />
											<span>
												<strong><?php esc_html_e('Simple', 'omnifywp-ecommerce'); ?></strong>
												<small><?php esc_html_e('One price, one SKU.', 'omnifywp-ecommerce'); ?></small>
											</span>
										</label>
										<label class="omnify-choice-card">
											<input type="radio" name="product_structure" value="variable" <?php checked($omnify_product_structure, 'variable'); ?> required />
											<span>
												<strong><?php esc_html_e('Variable', 'omnifywp-ecommerce'); ?></strong>
												<small><?php esc_html_e('Sizes, colors, formats.', 'omnifywp-ecommerce'); ?></small>
											</span>
										</label>
									</div>
								</div>

								<div class="omnify-title-slug-row" style="border-top: 1px solid #edf2f7; padding-top: 20px; margin-top: 8px;">
									<div class="omnify-form-group" style="margin: 0;">
										<label for="name"><?php esc_html_e('Product Title', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></label>
										<input type="text" id="name" name="name" value="<?php echo esc_attr($omnify_product ? $omnify_product['name'] : ''); ?>" required placeholder="e.g. Ultimate Photoshop Presets Pack" />
									</div>

									<div class="omnify-form-group" style="margin: 0;">
										<label for="slug"><?php esc_html_e('Slug (URL Handle)', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="slug" name="slug" value="<?php echo esc_attr($omnify_product ? $omnify_product['slug'] : ''); ?>" placeholder="e.g. photoshop-presets" />
										<span class="help-text"><?php esc_html_e('Leave blank to auto-generate.', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>

								<div class="omnify-form-group">
									<label for="short_description"><?php esc_html_e('Short Description', 'omnifywp-ecommerce'); ?></label>
									<?php
									$omnify_short_desc_content = $omnify_product ? ($omnify_product['short_description'] ?? '') : '';
									wp_editor($omnify_short_desc_content, 'short_description', [
										'textarea_name' => 'short_description',
										'media_buttons' => false,
										'textarea_rows' => 4,
										'teeny'         => true,
										'quicktags'     => true,
									]);
									?>
								</div>

								<!-- Specifications (for the specs table in product page, like Premium) -->
								<div class="omnify-form-group">
									<label><?php esc_html_e('Specifications (key-value for product page table)', 'omnifywp-ecommerce'); ?></label>
									<div id="omnify-specs-container" style="display:flex; flex-direction:column; gap:6px;">
										<?php 
										$omnify_specs = $omnify_product && is_array($omnify_product['specifications'] ?? null) ? $omnify_product['specifications'] : [];
										if (empty($omnify_specs)) $omnify_specs = [['key'=>'', 'value'=>'']];
										foreach ($omnify_specs as $omnify_sidx => $omnify_spec) :
										?>
										<div class="omnify-spec-row" style="display:flex; gap:8px; align-items:center;">
											<input type="text" name="specifications[<?php echo esc_attr($omnify_sidx); ?>][key]" value="<?php echo esc_attr($omnify_spec['key'] ?? ''); ?>" placeholder="Key e.g. Brand, Weight" style="flex:1;" />
											<input type="text" name="specifications[<?php echo esc_attr($omnify_sidx); ?>][value]" value="<?php echo esc_attr($omnify_spec['value'] ?? ''); ?>" placeholder="Value" style="flex:2;" />
											<button type="button" class="omnify-spec-remove" style="background:#fee2e2; border:0; color:#b91c1c; padding:2px 6px; border-radius:4px; cursor:pointer;">&times;</button>
										</div>
										<?php endforeach; ?>
									</div>
									<button type="button" id="omnify-add-spec" class="omnify-button omnify-button--secondary omnify-button--sm" style="margin-top:6px;">+ Add Specification</button>
								</div>
							</div>
						</div>
					</div>

					<!-- STEP 2: Pricing -->
					<div class="omnify-wizard-panel" data-panel="2">
						<div class="omnify-card omnify-editor-section-card">
							<div class="omnify-editor-section-card__header">
								<div>
									<h2><?php esc_html_e('Pricing & Status', 'omnifywp-ecommerce'); ?></h2>
								</div>
							</div>
							<div class="omnify-form-grid" style="max-width:100%;">
								<div class="omnify-form-row">
									<div class="omnify-form-group">
										<label for="status"><?php esc_html_e('Storefront Status', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></label>
										<select id="status" name="status" required>
											<option value="draft" <?php selected($omnify_product ? $omnify_product['status'] : 'draft', 'draft'); ?>><?php esc_html_e('Draft (Hidden)', 'omnifywp-ecommerce'); ?></option>
											<option value="published" <?php selected($omnify_product ? $omnify_product['status'] : 'draft', 'published'); ?>><?php esc_html_e('Published (Visible)', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-form-group">
										<label for="sku"><?php esc_html_e('SKU', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="sku" name="sku" value="<?php echo esc_attr($omnify_product ? $omnify_product['sku'] : ''); ?>" placeholder="e.g. OMNI-PRD-001" />
									</div>
								</div>
								<div class="omnify-form-row">
									<div class="omnify-form-group">
										<label for="price"><?php
										/* translators: %s: currency symbol. */
										echo esc_html(sprintf(__('Regular Price (%s)', 'omnifywp-ecommerce'), $omnify_symbol));
										?> <span class="omnify-required">*</span></label>
										<input type="number" step="0.01" min="0" id="price" name="price" value="<?php echo esc_attr($omnify_product ? $omnify_product['price'] : '0.00'); ?>" required />
									</div>
									<div class="omnify-form-group">
										<label for="sale_price"><?php
										/* translators: %s: currency symbol. */
										echo esc_html(sprintf(__('Sale Price (%s)', 'omnifywp-ecommerce'), $omnify_symbol));
										?></label>
										<input type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['sale_price'] ? $omnify_product['sale_price'] : ''); ?>" />
									</div>
								</div>
							</div>
						</div>

						<!-- Inventory & Shipping (clean) -->
						<div class="omnify-card omnify-editor-section-card" id="omnify-inventory-card" style="<?php echo 'physical' === $omnify_product_kind ? '' : 'display: none;'; ?>">
							<div class="omnify-editor-section-card__header">
								<div>
									<h2><?php esc_html_e('Inventory & Shipping', 'omnifywp-ecommerce'); ?></h2>
								</div>
							</div>
							<div class="omnify-form-grid" style="max-width: 100%;">
								<div class="omnify-form-row" style="margin-bottom: 16px;">
									<div class="omnify-form-group" style="margin-bottom: 0;">
										<label for="stock_status"><?php esc_html_e('Stock Status', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></label>
										<select id="stock_status" name="stock_status" required style="width: 100%;">
											<option value="instock" <?php selected($omnify_product ? $omnify_product['stock_status'] : 'instock', 'instock'); ?>><?php esc_html_e('In Stock', 'omnifywp-ecommerce'); ?></option>
											<option value="outofstock" <?php selected($omnify_product ? $omnify_product['stock_status'] : 'instock', 'outofstock'); ?>><?php esc_html_e('Out of Stock', 'omnifywp-ecommerce'); ?></option>
											<option value="onbackorder" <?php selected($omnify_product ? $omnify_product['stock_status'] : 'instock', 'onbackorder'); ?>><?php esc_html_e('On Backorder', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>

									<div class="omnify-form-group" id="omnify-stock-qty-group" style="margin-bottom: 0; <?php echo ($omnify_product && $omnify_product['manage_stock']) ? '' : 'display: none;'; ?>">
										<label for="stock_qty"><?php esc_html_e('Stock Quantity', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="stock_qty" name="stock_qty" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['stock_qty'] ? $omnify_product['stock_qty'] : '0'); ?>" style="width: 100%;" />
									</div>
								</div>

								<div class="omnify-form-row" style="margin-bottom: 16px;">
									<div class="omnify-form-group" style="margin-bottom: 0;">
										<label class="omnify-toggle" style="display: flex; align-items: center; gap: 8px;">
											<input  type="checkbox" id="manage_stock" name="manage_stock" value="1" <?php checked($omnify_product ? $omnify_product['manage_stock'] : 0); ?> style="margin: 0;" />
											<span class="omnify-toggle__label" style="font-size: 13px; font-weight: 500;"><?php esc_html_e('Manage stock level', 'omnifywp-ecommerce'); ?></span>
										</label>
									</div>
									<div class="omnify-form-group" style="margin-bottom: 0;">
										<label class="omnify-toggle" style="display: flex; align-items: center; gap: 8px;">
											<input  type="checkbox" id="allow_backorders" name="allow_backorders" value="1" <?php checked($omnify_product ? ($omnify_product['allow_backorders'] ?? 0) : 0); ?> style="margin: 0;" />
											<span class="omnify-toggle__label" style="font-size: 13px; font-weight: 500;"><?php esc_html_e('Allow backorders when stock is low', 'omnifywp-ecommerce'); ?></span>
										</label>
									</div>
								</div>

								<div class="omnify-form-row" style="margin-bottom: 16px;">
									<div class="omnify-form-group" style="margin-bottom: 0;">
										<label class="omnify-toggle" style="display: flex; align-items: center; gap: 8px;">
											<input  type="checkbox" id="preorder_enabled" name="preorder_enabled" value="1" <?php checked($omnify_product ? ($omnify_product['preorder_enabled'] ?? 0) : 0); ?> style="margin: 0;" />
											<span class="omnify-toggle__label" style="font-size: 13px; font-weight: 500;"><?php esc_html_e('Sell as preorder', 'omnifywp-ecommerce'); ?></span>
										</label>
									</div>
									<div class="omnify-form-group" style="margin-bottom: 0;">
										<label for="max_purchase_qty"><?php esc_html_e('Max Purchase Quantity', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="max_purchase_qty" name="max_purchase_qty" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['max_purchase_qty'] ? $omnify_product['max_purchase_qty'] : ''); ?>" min="1" placeholder="<?php esc_attr_e('No limit', 'omnifywp-ecommerce'); ?>" style="width: 100%;" />
										<span class="help-text"><?php esc_html_e('Restrict max units per checkout.', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>

								<div id="omnify-preorder-fields" style="<?php echo ($omnify_product && ! empty($omnify_product['preorder_enabled'])) ? '' : 'display: none;'; ?>">
									<div class="omnify-form-row" style="margin-bottom: 16px;">
										<div class="omnify-form-group" style="margin-bottom: 0;">
											<label for="preorder_release_date"><?php esc_html_e('Expected Release Date', 'omnifywp-ecommerce'); ?></label>
											<input type="date" id="preorder_release_date" name="preorder_release_date" value="<?php echo esc_attr($omnify_product ? ($omnify_product['preorder_release_date'] ?? '') : ''); ?>" style="width: 100%;" />
										</div>
										<div class="omnify-form-group" style="margin-bottom: 0;">
											<label for="preorder_limit"><?php esc_html_e('Preorder Limit per Checkout', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="preorder_limit" name="preorder_limit" value="<?php echo esc_attr($omnify_product && null !== ($omnify_product['preorder_limit'] ?? null) ? $omnify_product['preorder_limit'] : ''); ?>" min="1" placeholder="<?php esc_attr_e('No limit', 'omnifywp-ecommerce'); ?>" style="width: 100%;" />
										</div>
									</div>
									<div class="omnify-form-group" style="margin-bottom: 16px;">
										<label for="preorder_message"><?php esc_html_e('Preorder / Backorder Message', 'omnifywp-ecommerce'); ?></label>
										<textarea id="preorder_message" name="preorder_message" rows="3" placeholder="<?php esc_attr_e('Ships when available. Estimated release date shown above.', 'omnifywp-ecommerce'); ?>" style="width: 100%; resize: vertical;"><?php echo esc_textarea($omnify_product ? ($omnify_product['preorder_message'] ?? '') : ''); ?></textarea>
									</div>
								</div>

								<!-- Dimensions/Weight (only for physical products) -->
								<div id="omnify-dimensions-row" style="border-top: 1px solid var(--omnify-gray-200); padding-top: 12px; margin-top: 8px; <?php echo 'physical' === $omnify_product_kind ? '' : 'display: none;'; ?>">
									<div class="omnify-form-row" style="margin-bottom: 12px;">
										<div class="omnify-form-group" style="margin-bottom: 0;">
											<label for="shipping_class"><?php esc_html_e('Shipping Class', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="shipping_class" name="shipping_class" value="<?php echo esc_attr($omnify_product ? ($omnify_product['shipping_class'] ?? '') : ''); ?>" placeholder="<?php esc_attr_e('fragile, heavy, oversized', 'omnifywp-ecommerce'); ?>" style="width: 100%;" />
											<span class="help-text"><?php esc_html_e('Used by delivery zones.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-form-group" style="margin-bottom: 0;">
											<label for="weight"><?php esc_html_e('Weight (kg)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" step="0.01" id="weight" name="weight" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['weight'] ? $omnify_product['weight'] : ''); ?>" placeholder="0.00" style="width: 100%;" />
										</div>
									</div>
									<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
										<div class="omnify-form-group">
											<label for="length" style="font-size: 11px; font-weight: 600;"><?php esc_html_e('L (cm)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" step="0.1" id="length" name="length" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['length'] ? $omnify_product['length'] : ''); ?>" placeholder="0" style="width: 100%; padding: 4px 8px !important; height: 32px !important;" />
										</div>
										<div class="omnify-form-group">
											<label for="width" style="font-size: 11px; font-weight: 600;"><?php esc_html_e('W (cm)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" step="0.1" id="width" name="width" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['width'] ? $omnify_product['width'] : ''); ?>" placeholder="0" style="width: 100%; padding: 4px 8px !important; height: 32px !important;" />
										</div>
										<div class="omnify-form-group">
											<label for="height" style="font-size: 11px; font-weight: 600;"><?php esc_html_e('H (cm)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" step="0.1" id="height" name="height" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['height'] ? $omnify_product['height'] : ''); ?>" placeholder="0" style="width: 100%; padding: 4px 8px !important; height: 32px !important;" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- STEP 3: Full Description -->
					<div class="omnify-wizard-panel" data-panel="3" id="omnify-content-panel">
						<div class="omnify-card omnify-editor-section-card">
							<div class="omnify-editor-section-card__header">
								<div>
									<h2><?php esc_html_e('Full Description', 'omnifywp-ecommerce'); ?> <span class="omnify-required">*</span></h2>
								</div>
							</div>
							<div class="omnify-form-group">
								<?php
								$omnify_content = $omnify_product ? $omnify_product['description'] : '';
								wp_editor($omnify_content, 'description', [
									'textarea_name' => 'description',
									'media_buttons' => true,
									'textarea_rows' => 12,
									'teeny'         => false,
									'quicktags'     => true,
								]);
								?>
							</div>
						</div>
						<div id="omnify-taxonomy-row" class="omnify-taxonomy-row"></div>
					</div>

					<!-- STEP 4: Advanced Options, Files, Variations -->
					<div class="omnify-wizard-panel" data-panel="4">
						<div style="display: flex; flex-direction: column; gap: 20px;">

					<!-- Bundle Products Card -->
					<div class="omnify-card omnify-editor-section-card" id="omnify-bundle-products-card" style="<?php echo esc_attr( ($omnify_product && $omnify_product['type'] === 'bundle') ? '' : 'display: none;' ); ?>">
						<h2><?php esc_html_e('Bundle Products', 'omnifywp-ecommerce'); ?></h2>
						<p><?php esc_html_e('Select the simple digital products included in this bundle.', 'omnifywp-ecommerce'); ?></p>
						<div class="omnify-form-group" style="margin-top: 12px;">
							<input type="text" id="omnify-bundle-search" placeholder="<?php esc_attr_e('Search products...', 'omnifywp-ecommerce'); ?>" style="width: 100%; margin-bottom: 12px; padding: 8px 12px; border: 1px solid var(--omnify-gray-200); border-radius: 6px;" />
							<div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 12px; background: #fff;">
								<?php
								$omnify_bundled_ids = $omnify_product && ! empty($omnify_product['bundled_ids']) && is_array($omnify_product['bundled_ids']) ? $omnify_product['bundled_ids'] : [];
								if (empty($omnify_all_digital_products)) :
									?>
									<p style="color: var(--omnify-gray-500); margin: 0; font-size: 13px;"><?php esc_html_e('No digital products found. Create some digital products first.', 'omnifywp-ecommerce'); ?></p>
								<?php else : ?>
									<div class="omnify-bundle-products-list">
										<?php foreach ($omnify_all_digital_products as $omnify_dp) : 
											$omnify_is_checked = in_array((int) $omnify_dp['id'], $omnify_bundled_ids, true);
											?>
											<label class="omnify-bundle-product-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--omnify-gray-800);">
												<input class="omnify-checkbox" type="checkbox" name="bundled_ids[]" value="<?php echo esc_attr((string) $omnify_dp['id']); ?>" <?php checked($omnify_is_checked); ?> style="margin: 0;" />
												<span>
													<?php echo esc_html($omnify_dp['name']); ?>
													<?php if (! empty($omnify_dp['sku'])) : ?>
														<span style="color: var(--omnify-gray-400); font-size: 11px;">(<?php echo esc_html($omnify_dp['sku']); ?>)</span>
													<?php endif; ?>
													<?php if ($omnify_dp['status'] === 'draft') : ?>
														<span style="color: #ef4444; font-size: 11px;">[<?php esc_html_e('Draft', 'omnifywp-ecommerce'); ?>]</span>
													<?php endif; ?>
												</span>
											</label>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- Existing Attached Files Card -->
					<?php if ($omnify_product) : ?>
						<div class="omnify-card omnify-editor-section-card omnify-digital-product-section" id="omnify-attached-files-card" style="<?php echo $omnify_hide_digital_sections ? 'display: none;' : ''; ?>">
							<div class="omnify-editor-section-card__header">
								<div>
									<h2><?php esc_html_e('Attached Digital Files', 'omnifywp-ecommerce'); ?></h2>
									<p><?php esc_html_e('Files customers can download after purchase.', 'omnifywp-ecommerce'); ?></p>
								</div>
							</div>
							<?php
							$omnify_files = $omnify_product_files_repo->all_for_product((int) $omnify_product['id']);
							if (empty($omnify_files)) :
							?>
								<p style="color: var(--shopify-gray-600); font-size: 13px; font-style: italic; margin: 0;">
									<?php esc_html_e('No digital files attached to this product yet. Customers will have nothing to download.', 'omnifywp-ecommerce'); ?>
								</p>
							<?php else : ?>
								<div class="omnify-table-wrapper">
									<table class="omnify-table">
										<thead>
											<tr>
												<th><?php esc_html_e('File Name', 'omnifywp-ecommerce'); ?></th>
												<th><?php esc_html_e('Size', 'omnifywp-ecommerce'); ?></th>
												<th><?php esc_html_e('Version', 'omnifywp-ecommerce'); ?></th>
												<th><?php esc_html_e('Signed URL', 'omnifywp-ecommerce'); ?></th>
												<th style="text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($omnify_files as $omnify_file) : 
												$omnify_signed_preview_url = $omnify_signed_urls->create((int) $omnify_file['id'], 86400);
											?>
												<tr>
													<td>
														<strong style="word-break: break-all;"><?php echo esc_html($omnify_file['file_name']); ?></strong>
														<div style="font-size: 11px; color: var(--shopify-gray-600);"><?php echo esc_html($omnify_file['file_type']); ?></div>
													</td>
													<td><?php echo esc_html($omnify_file['size_label']); ?></td>
													<td>
														<span class="omnify-status omnify-status--completed" style="padding: 1px 6px; font-size: 10px;">
															v<?php echo esc_html($omnify_file['version']); ?>
														</span>
													</td>
													<td>
														<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm js-copy-url" data-url="<?php echo esc_url($omnify_signed_preview_url); ?>">
															<?php esc_html_e('Copy URL (24h)', 'omnifywp-ecommerce'); ?>
														</button>
													</td>
													<td style="text-align: right;">
														<a href="<?php
														// translators: %s: placeholder value. echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_product_file&id=' . $file['id'] . '&product_id=' . $product['id']), 'omnify_delete_product_file_' . $file['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php
														// translators: %s: placeholder value. esc_attr_e('Delete file', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php
														// translators: %s: placeholder value. echo esc_js(sprintf(__('Delete file "%s"?', 'omnifywp-ecommerce'), $file['file_name'])); ?>');">
															<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
														</a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- Upload New Files Card -->
					<div class="omnify-card omnify-editor-section-card omnify-digital-product-section" id="omnify-upload-files-card" style="<?php echo $omnify_hide_digital_sections ? 'display: none;' : ''; ?>">
						<div class="omnify-editor-section-card__header">
							<div>
								<h2><?php esc_html_e('Upload Files', 'omnifywp-ecommerce'); ?></h2>
								<p><?php esc_html_e('Upload one or more downloadable files and set access duration after purchase.', 'omnifywp-ecommerce'); ?></p>
							</div>
						</div>
						<div class="omnify-form-grid" style="max-width: 100%;">
							<div class="omnify-form-row">
								<div class="omnify-form-group omnify-swatch-setting-field">
									<label for="product_files"><?php esc_html_e('Choose Files', 'omnifywp-ecommerce'); ?></label>
									<input type="file" id="product_files" name="product_files[]" multiple style="width: 100%; padding: 4px 0 !important; border: none !important;" />
									<span class="omnify-file-count" id="omnify-product-files-count"></span>
									<span class="help-text"><?php esc_html_e('Select multiple files at once by holding Shift or Command/Ctrl.', 'omnifywp-ecommerce'); ?></span>
								</div>

								<div class="omnify-form-group omnify-swatch-setting-field">
									<label for="file_version"><?php esc_html_e('Version Label', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="file_version" name="file_version" value="1.0.0" placeholder="e.g. 1.0.0" />
									<span class="help-text"><?php esc_html_e('Applies to all newly uploaded files in this session.', 'omnifywp-ecommerce'); ?></span>
								</div>

								<div class="omnify-form-group omnify-swatch-setting-field">
									<label for="download_expiry_days"><?php esc_html_e('Download Access Duration', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="download_expiry_days" name="download_expiry_days" min="0" step="1" value="<?php echo esc_attr($omnify_product ? ($omnify_product['download_expiry_days'] ?? 0) : 0); ?>" placeholder="0" />
									<span class="help-text"><?php esc_html_e('Number of days after purchase files remain downloadable. Use 0 for lifetime access.', 'omnifywp-ecommerce'); ?></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Attributes & Variations Card -->
					<div class="omnify-card omnify-editor-section-card" id="omnify-variations-card" style="<?php echo ($omnify_product && $omnify_product['type'] === 'variable') ? '' : 'display: none;'; ?>">
						<div class="omnify-editor-section-card__header">
							<div>
								<h2><?php esc_html_e('Product Attributes & Variations', 'omnifywp-ecommerce'); ?></h2>
								<p><?php esc_html_e('Define attributes such as size, color, license, or format, then generate purchasable variations.', 'omnifywp-ecommerce'); ?></p>
							</div>
						</div>

						<div class="omnify-variation-settings-panel">
							<div class="omnify-form-row">
								<div class="omnify-form-group">
									<label for="variation_selector_style"><?php esc_html_e('Storefront Selector Style', 'omnifywp-ecommerce'); ?></label>
									<select id="variation_selector_style" name="variation_selector_style">
										<option value="buttons" <?php selected($omnify_variation_settings['selector_style'], 'buttons'); ?>><?php esc_html_e('Option buttons', 'omnifywp-ecommerce'); ?></option>
										<option value="dropdowns" <?php selected($omnify_variation_settings['selector_style'], 'dropdowns'); ?>><?php esc_html_e('Dropdown lists', 'omnifywp-ecommerce'); ?></option>
										<option value="swatches" <?php selected($omnify_variation_settings['selector_style'], 'swatches'); ?>><?php esc_html_e('Swatch buttons', 'omnifywp-ecommerce'); ?></option>
									</select>
									<span class="help-text"><?php esc_html_e('Choose how shoppers select size, color, license, format, or other options.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-form-group">
									<label for="variation_swatch_shape"><?php esc_html_e('Swatch Shape', 'omnifywp-ecommerce'); ?></label>
									<select id="variation_swatch_shape" name="variation_swatch_shape">
										<option value="round" <?php selected($omnify_variation_settings['swatch_shape'], 'round'); ?>><?php esc_html_e('Rounded / Circle', 'omnifywp-ecommerce'); ?></option>
										<option value="square" <?php selected($omnify_variation_settings['swatch_shape'], 'square'); ?>><?php esc_html_e('Square', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>
								<div class="omnify-form-group">
									<label for="variation_swatch_size"><?php esc_html_e('Swatch Size', 'omnifywp-ecommerce'); ?></label>
									<select id="variation_swatch_size" name="variation_swatch_size">
										<option value="small" <?php selected($omnify_variation_settings['swatch_size'], 'small'); ?>><?php esc_html_e('Small', 'omnifywp-ecommerce'); ?></option>
										<option value="medium" <?php selected($omnify_variation_settings['swatch_size'], 'medium'); ?>><?php esc_html_e('Medium', 'omnifywp-ecommerce'); ?></option>
										<option value="large" <?php selected($omnify_variation_settings['swatch_size'], 'large'); ?>><?php esc_html_e('Large', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>
								<div class="omnify-form-group">
									<label for="variation_out_of_stock"><?php esc_html_e('Unavailable Swatches', 'omnifywp-ecommerce'); ?></label>
									<select id="variation_out_of_stock" name="variation_out_of_stock">
										<option value="cross" <?php selected($omnify_variation_settings['out_of_stock'], 'cross'); ?>><?php esc_html_e('Show crossed out', 'omnifywp-ecommerce'); ?></option>
										<option value="blur" <?php selected($omnify_variation_settings['out_of_stock'], 'blur'); ?>><?php esc_html_e('Blur and disable', 'omnifywp-ecommerce'); ?></option>
										<option value="hide" <?php selected($omnify_variation_settings['out_of_stock'], 'hide'); ?>><?php esc_html_e('Hide unavailable', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>

							</div>
						</div>

						<!-- Attributes Builder -->
						<div style="background: var(--omnify-gray-100); padding: 18px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--omnify-gray-200);">
							<h3 style="margin-top:0; margin-bottom:12px; font-size:14px; font-weight:600; color:var(--omnify-dark);"><?php esc_html_e('1. Attributes List', 'omnifywp-ecommerce'); ?></h3>
							<div id="omnify-attributes-container" style="display:flex; flex-direction:column; gap:12px; margin-bottom:12px;">
								<?php
								$omnify_attrs = $omnify_product && is_array($omnify_product['attributes'] ?? null) ? $omnify_product['attributes'] : [];
								foreach ($omnify_attrs as $omnify_index => $omnify_attr) :
									$omnify_attr_type = sanitize_key($omnify_attr['type'] ?? 'button');
									if (! in_array($omnify_attr_type, ['button', 'dropdown', 'color', 'image'], true)) {
										$omnify_attr_type = 'button';
									}
									$omnify_attr_option_meta = is_array($omnify_attr['option_meta'] ?? null) ? $omnify_attr['option_meta'] : [];
								?>
									<div class="omnify-attribute-row" data-index="<?php echo esc_attr( $omnify_index ); ?>" style="display:grid; grid-template-columns: 1fr 1fr 2fr auto; gap:12px; align-items:end; background:#ffffff; border:1px solid var(--omnify-gray-200); padding:10px; border-radius:6px;">
										<div class="omnify-form-group" style="margin-bottom:0;">
											<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Attribute Name', 'omnifywp-ecommerce'); ?></label>
											<input type="text" name="attributes[<?php echo esc_attr($omnify_index); ?>][name]" class="omnify-attr-name" value="<?php echo esc_attr($omnify_attr['name']); ?>" placeholder="e.g. Size" />
										</div>
										<div class="omnify-form-group" style="margin-bottom:0;">
											<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Variation Type', 'omnifywp-ecommerce'); ?></label>
											<select name="attributes[<?php echo esc_attr($omnify_index); ?>][type]" class="omnify-attr-type">
												<option value="button" <?php selected($omnify_attr_type, 'button'); ?>><?php esc_html_e('Buttons', 'omnifywp-ecommerce'); ?></option>
												<option value="dropdown" <?php selected($omnify_attr_type, 'dropdown'); ?>><?php esc_html_e('Dropdown', 'omnifywp-ecommerce'); ?></option>
												<option value="color" <?php selected($omnify_attr_type, 'color'); ?>><?php esc_html_e('Color', 'omnifywp-ecommerce'); ?></option>
												<option value="image" <?php selected($omnify_attr_type, 'image'); ?>><?php esc_html_e('Image', 'omnifywp-ecommerce'); ?></option>
											</select>
											<input type="hidden" name="attributes[<?php echo esc_attr($omnify_index); ?>][option_meta_json]" class="omnify-attr-option-meta" value="<?php echo esc_attr(wp_json_encode($omnify_attr_option_meta)); ?>" />
										</div>
										<div class="omnify-form-group" style="margin-bottom:0;">
											<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Options (comma-separated)', 'omnifywp-ecommerce'); ?></label>
											<input type="text" name="attributes[<?php echo esc_attr($omnify_index); ?>][options]" class="omnify-attr-options" value="<?php echo esc_attr(implode(', ', $omnify_attr['options'])); ?>" placeholder="e.g. S, M, L" />
										</div>
										<button type="button" class="omnify-button omnify-button--danger omnify-button--sm omnify-remove-attr" style="margin-bottom: 2px;">&times;</button>
									</div>
								<?php endforeach; ?>
							</div>
							<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
								<button type="button" id="omnify-add-attribute" class="omnify-button omnify-button--secondary omnify-button--sm" style="height:36px;">
									+ <?php esc_html_e('Add Attribute', 'omnifywp-ecommerce'); ?>
								</button>
								<div style="display:flex; gap:8px; align-items:center;">
									<select id="omnify-global-attribute-select" style="height:36px; padding:7px 12px; font-size:14px; border:1px solid var(--omnify-gray-300); border-radius:4px; max-width:240px; background:#fff;">
										<option value=""><?php esc_html_e('-- Choose Global Attribute --', 'omnifywp-ecommerce'); ?></option>
										<?php foreach ($omnify_global_attributes as $omnify_g_attr) : ?>
											<option value="<?php echo esc_attr(wp_json_encode($omnify_g_attr)); ?>"><?php echo esc_html($omnify_g_attr['name']); ?> (<?php echo esc_html(implode(', ', $omnify_g_attr['options'])); ?>)</option>
										<?php endforeach; ?>
									</select>
									<button type="button" id="omnify-add-global-attribute" class="omnify-button omnify-button--secondary omnify-button--sm" style="height:36px;">
										+ <?php esc_html_e('Add Global Attribute', 'omnifywp-ecommerce'); ?>
									</button>
								</div>
							</div>
						</div>

						<!-- Variations Configurator -->
						<div style="background: var(--omnify-gray-100); padding: 18px; border-radius: 8px; border: 1px solid var(--omnify-gray-200);">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
								<h3 style="margin:0; font-size:14px; font-weight:600; color:var(--omnify-dark);"><?php esc_html_e('2. Product Variations', 'omnifywp-ecommerce'); ?></h3>
								<div style="display:flex; gap:8px;">
									<button type="button" id="omnify-generate-variations" class="omnify-button omnify-button--secondary omnify-button--sm" style="display: inline-flex; align-items: center; gap: 4px;">
										<span class="dashicons dashicons-admin-generic" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
										<?php esc_html_e('Generate Variations', 'omnifywp-ecommerce'); ?>
									</button>
									<button type="button" id="omnify-add-variation" class="omnify-button omnify-button--secondary omnify-button--sm">
										+ <?php esc_html_e('Add Custom Variation', 'omnifywp-ecommerce'); ?>
									</button>
								</div>
							</div>

							<div id="omnify-variations-container" style="display:flex; flex-direction:column; gap:12px;">
								<?php
								$omnify_variations = $omnify_product && isset($omnify_product['variations']) ? $omnify_product['variations'] : [];
								foreach ($omnify_variations as $omnify_v_idx => $omnify_v) :
								?>
									<div class="omnify-variation-row" data-id="<?php echo esc_attr((string) $omnify_v['id']); ?>" style="background:#ffffff; border:1px solid var(--omnify-gray-200); border-radius:6px; padding:16px; position:relative;">
										<input type="hidden" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][id]" value="<?php echo esc_attr((string) $omnify_v['id']); ?>" />
										
										<div class="omnify-variation-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--omnify-gray-100);">
											<div class="omnify-variation-attributes" style="display:flex; gap:8px; flex-wrap:wrap; font-weight:600; font-size:12px;">
												<?php foreach ($omnify_attrs as $omnify_attr) : 
													$omnify_selected_val = $omnify_v['attributes'][$omnify_attr['name']] ?? '';
												?>
													<div class="omnify-variation-attribute-control" style="display:flex; align-items:center; gap:4px;">
														<span><?php echo esc_html($omnify_attr['name']); ?>:</span>
														<select name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][attributes][<?php echo esc_attr($omnify_attr['name']); ?>]" class="omnify-variation-attr-select" required>
															<option value=""><?php esc_html_e('Select...', 'omnifywp-ecommerce'); ?></option>
														<?php foreach ($omnify_attr['options'] as $omnify_opt) : ?>
																<option value="<?php echo esc_attr($omnify_opt); ?>" <?php selected($omnify_selected_val, $omnify_opt); ?>><?php echo esc_html($omnify_opt); ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												<?php endforeach; ?>
											</div>
											<button type="button" class="omnify-button omnify-button--danger omnify-button--sm omnify-remove-variation" style="padding: 2px 8px !important; height: 24px !important; line-height: 22px !important; display: inline-flex; align-items: center; justify-content: center;"><span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span></button>
										</div>

										<div class="omnify-form-row omnify-form-row--thirds omnify-variation-grid-pricing">
											<div class="omnify-form-group">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('SKU', 'omnifywp-ecommerce'); ?></label>
												<input type="text" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][sku]" value="<?php echo esc_attr($omnify_v['sku']); ?>" placeholder="e.g. SIZE-S" />
											</div>
											<div class="omnify-form-group">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php
												/* translators: %s: currency symbol. */
												echo esc_html(sprintf(__('Regular Price (%s)', 'omnifywp-ecommerce'), $omnify_symbol));
												?> *</label>
												<input type="number" step="0.01" min="0" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][price]" value="<?php echo esc_attr(number_format($omnify_v['price'], 2, '.', '')); ?>" required />
											</div>
											<div class="omnify-form-group">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php
												/* translators: %s: currency symbol. */
												echo esc_html(sprintf(__('Sale Price (%s)', 'omnifywp-ecommerce'), $omnify_symbol));
												?></label>
												<input type="number" step="0.01" min="0" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][sale_price]" value="<?php echo esc_attr($omnify_v['sale_price'] !== null ? number_format($omnify_v['sale_price'], 2, '.', '') : ''); ?>" />
											</div>
										</div>

										<div class="omnify-form-row omnify-variation-grid-stock" style="margin-top:10px; align-items: flex-end;">
											<div class="omnify-form-group" style="margin-bottom: 0;">
												<label class="omnify-toggle">
													<input  type="checkbox" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][manage_stock]" class="omnify-var-manage-stock" value="1" <?php checked($omnify_v['manage_stock']); ?> />
													<span class="omnify-toggle__label" style="font-size:11px;"><?php esc_html_e('Manage stock level', 'omnifywp-ecommerce'); ?></span>
												</label>
											</div>
											<div class="omnify-form-group omnify-var-stock-qty-group" style="margin-bottom: 0; <?php echo ! empty($omnify_v['manage_stock']) ? '' : 'display:none;'; ?>">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Stock Qty', 'omnifywp-ecommerce'); ?></label>
												<input type="number" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][stock_qty]" value="<?php echo esc_attr($omnify_v['stock_qty'] !== null ? $omnify_v['stock_qty'] : '0'); ?>" style="padding: 4px 8px !important; height: 28px !important;" />
											</div>
											<div class="omnify-form-group omnify-var-stock-status-group" style="margin-bottom: 0; <?php echo ! empty($omnify_v['manage_stock']) ? 'display:none;' : ''; ?>">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Stock Status', 'omnifywp-ecommerce'); ?></label>
												<select name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][stock_status]" style="padding: 4px 8px !important; height: 28px !important;">
													<option value="instock" <?php selected($omnify_v['stock_status'], 'instock'); ?>><?php esc_html_e('In Stock', 'omnifywp-ecommerce'); ?></option>
													<option value="outofstock" <?php selected($omnify_v['stock_status'], 'outofstock'); ?>><?php esc_html_e('Out of Stock', 'omnifywp-ecommerce'); ?></option>
													<option value="onbackorder" <?php selected($omnify_v['stock_status'], 'onbackorder'); ?>><?php esc_html_e('On Backorder', 'omnifywp-ecommerce'); ?></option>
												</select>
											</div>
										</div>

										<div class="omnify-form-row omnify-variation-grid-preorder" style="margin-top:10px; align-items:flex-end;">
											<div class="omnify-form-group" style="margin-bottom:0;">
												<label class="omnify-toggle">
													<input  type="checkbox" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][allow_backorders]" value="1" <?php checked(! empty($omnify_v['allow_backorders'])); ?> />
													<span class="omnify-toggle__label" style="font-size:11px;"><?php esc_html_e('Allow backorders', 'omnifywp-ecommerce'); ?></span>
												</label>
											</div>
											<div class="omnify-form-group" style="margin-bottom:0;">
												<label class="omnify-toggle">
													<input  type="checkbox" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][preorder_enabled]" class="omnify-var-preorder-toggle" value="1" <?php checked(! empty($omnify_v['preorder_enabled'])); ?> />
													<span class="omnify-toggle__label" style="font-size:11px;"><?php esc_html_e('Preorder', 'omnifywp-ecommerce'); ?></span>
												</label>
											</div>
											<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; <?php echo ! empty($omnify_v['preorder_enabled']) ? '' : 'display:none;'; ?>">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Release Date', 'omnifywp-ecommerce'); ?></label>
												<input type="date" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][preorder_release_date]" value="<?php echo esc_attr($omnify_v['preorder_release_date'] ?? ''); ?>" style="padding: 4px 8px !important; height: 28px !important;" />
											</div>
											<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; <?php echo ! empty($omnify_v['preorder_enabled']) ? '' : 'display:none;'; ?>">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Limit', 'omnifywp-ecommerce'); ?></label>
												<input type="number" min="1" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][preorder_limit]" value="<?php echo esc_attr($omnify_v['preorder_limit'] ?? ''); ?>" style="padding: 4px 8px !important; height: 28px !important;" />
											</div>
											<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; <?php echo ! empty($omnify_v['preorder_enabled']) ? '' : 'display:none;'; ?>">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Message', 'omnifywp-ecommerce'); ?></label>
												<input type="text" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][preorder_message]" value="<?php echo esc_attr($omnify_v['preorder_message'] ?? ''); ?>" placeholder="<?php esc_attr_e('Ships when available', 'omnifywp-ecommerce'); ?>" style="padding: 4px 8px !important; height: 28px !important;" />
											</div>
										</div>

										<!-- Variation Image + Description -->
										<div class="omnify-form-row omnify-variation-grid-media" style="margin-top:10px; gap:12px; align-items:flex-start;">
											<div class="omnify-form-group" style="margin-bottom:0; flex:0 0 auto; width:80px;">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Image', 'omnifywp-ecommerce'); ?></label>
												<input type="hidden" class="omnify-var-thumbnail-id-input" name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][thumbnail_id]" value="<?php echo esc_attr($omnify_v['thumbnail_id'] ?? ''); ?>" />
												<div class="omnify-var-thumbnail-preview" style="margin-bottom:4px;">
													<?php if (!empty($omnify_v['thumbnail_url'])) : ?>
														<img src="<?php echo esc_url($omnify_v['thumbnail_url']); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #d1d5db;" />
													<?php else : ?>
														<span style="font-size:11px;color:#9ca3af;">No image</span>
													<?php endif; ?>
												</div>
												<div style="display:flex;gap:4px;flex-direction:column;">
													<button type="button" class="omnify-button omnify-button--secondary omnify-var-pick-image-btn" style="padding:2px 6px !important;font-size:10px;height:auto!important;"><?php esc_html_e('Select', 'omnifywp-ecommerce'); ?></button>
													<button type="button" class="omnify-button omnify-button--danger omnify-var-remove-image-btn" style="padding:2px 6px !important;font-size:10px;height:auto!important;"><?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?></button>
												</div>
											</div>
											<div class="omnify-form-group" style="margin-bottom:0; flex:1;">
												<label style="font-size:11px; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Variation Note', 'omnifywp-ecommerce'); ?></label>
												<textarea name="variations[<?php echo esc_attr( $omnify_v_idx ); ?>][description]" class="omnify-var-description-textarea" rows="2" placeholder="e.g. Ships from Japan, limited edition" style="width:100%;font-size:12px;resize:vertical;min-height:54px;"><?php echo esc_textarea($omnify_v['description'] ?? ''); ?></textarea>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Advanced stacked cards for step 4 (clean single column) -->
				<div style="display: flex; flex-direction: column; gap: 20px;">
					<div class="omnify-refund-delivery-row">
						<!-- Refund Policy Card -->
						<?php if (! empty($omnify_settings['enable_refunds'])) : ?>
							<div class="omnify-card omnify-editor-section-card" style="margin-bottom: 0;">
								<h2><?php esc_html_e('Refund Policy', 'omnifywp-ecommerce'); ?></h2>
								<div class="omnify-form-group" style="margin-bottom: 16px;">
									<label class="omnify-toggle" style="display: flex; align-items: center; gap: 8px;">
										<input  type="checkbox" id="refund_enabled" name="refund_enabled" value="1" <?php checked($omnify_product ? $omnify_product['refund_enabled'] : 1); ?> style="margin: 0;" />
										<span class="omnify-toggle__label" style="font-size: 13px; font-weight: 500;"><?php esc_html_e('Enable refunds for this product', 'omnifywp-ecommerce'); ?></span>
									</label>
								</div>

								<div id="omnify-refund-policy-fields" style="<?php echo ($omnify_product ? $omnify_product['refund_enabled'] : 1) ? '' : 'display: none;'; ?>">
									<div class="omnify-form-group" style="margin-bottom: 16px;">
										<label for="refund_window_days"><?php esc_html_e('Refund Window (Days)', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="refund_window_days" name="refund_window_days" value="<?php echo esc_attr($omnify_product && null !== $omnify_product['refund_window_days'] ? $omnify_product['refund_window_days'] : '30'); ?>" min="0" style="width: 100%;" />
										<span class="help-text"><?php esc_html_e('Number of days after purchase refunds can be requested.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-form-group">
										<label for="refund_policy_text"><?php esc_html_e('Refund Policy/Instructions (shown on product page)', 'omnifywp-ecommerce'); ?></label>
										<textarea id="refund_policy_text" name="refund_policy_text" rows="3" style="width: 100%; resize: vertical;" placeholder="<?php esc_attr_e('Example: 30-day money-back guarantee. No questions asked.', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_product ? $omnify_product['refund_policy_text'] : ''); ?></textarea>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<!-- Delivery & Return for the rich storefront page (Premium-style) -->
						<div class="omnify-card omnify-editor-section-card" style="margin-bottom: 0;">
							<h2><?php esc_html_e('Delivery & Return Info', 'omnifywp-ecommerce'); ?></h2>
							<div class="omnify-form-group" style="margin-bottom: 16px;">
								<label for="delivery_info"><?php esc_html_e('Delivery Information (shown on product page)', 'omnifywp-ecommerce'); ?></label>
								<textarea id="delivery_info" name="delivery_info" rows="2" style="width:100%;" placeholder="Free shipping &bull; 1-3 days estimated."><?php echo esc_textarea($omnify_product ? ($omnify_product['delivery_info'] ?? '') : ''); ?></textarea>
							</div>
							<div class="omnify-form-group">
								<label for="return_info"><?php esc_html_e('Return Information (shown on product page)', 'omnifywp-ecommerce'); ?></label>
								<textarea id="return_info" name="return_info" rows="2" style="width:100%;" placeholder="30-day returns on unused items."><?php echo esc_textarea($omnify_product ? ($omnify_product['return_info'] ?? '') : ''); ?></textarea>
							</div>
						</div>
					</div>

					<!-- Linked Products Card -->
					<div class="omnify-card omnify-editor-section-card" id="omnify-linked-products-card">
						<h2><?php esc_html_e('Linked Products', 'omnifywp-ecommerce'); ?></h2>
						<p class="description" style="margin-bottom: 16px;">
							<?php esc_html_e('Link other products to recommend them on the storefront.', 'omnifywp-ecommerce'); ?>
						</p>

						<div class="omnify-form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; max-width: 100%;">
							<!-- Upsells -->
							<div class="omnify-form-group">
								<label style="font-weight: 600; margin-bottom: 6px; display: block;"><?php esc_html_e('Upsells (Product Page Recommendations)', 'omnifywp-ecommerce'); ?></label>
								<p class="description" style="margin-top: 0; margin-bottom: 8px; font-size: 11px;">
									<?php esc_html_e('Recommended on the single product page (e.g. upgrades).', 'omnifywp-ecommerce'); ?>
								</p>
								<input type="text" id="omnify-upsell-search" placeholder="<?php esc_attr_e('Search products...', 'omnifywp-ecommerce'); ?>" style="width: 100%; margin-bottom: 8px; padding: 6px 10px; border: 1px solid var(--omnify-gray-200); border-radius: 6px; font-size: 13px;" />
								<div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 10px; background: #fff;">
									<?php
									$omnify_upsell_ids = $omnify_product && ! empty($omnify_product['upsell_ids']) && is_array($omnify_product['upsell_ids']) ? $omnify_product['upsell_ids'] : [];
									if (empty($omnify_all_other_products)) :
										?>
										<p style="color: var(--omnify-gray-500); margin: 0; font-size: 12px; font-style: italic;"><?php esc_html_e('No other products found.', 'omnifywp-ecommerce'); ?></p>
									<?php else : ?>
										<div class="omnify-upsell-products-list">
											<?php foreach ($omnify_all_other_products as $omnify_op) : 
												$omnify_is_checked = in_array((int) $omnify_op['id'], $omnify_upsell_ids, true);
												?>
												<label class="omnify-upsell-product-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; cursor: pointer; font-size: 12px; font-weight: 500; color: var(--omnify-gray-800);">
													<input class="omnify-checkbox" type="checkbox" name="upsell_ids[]" value="<?php echo esc_attr((string) $omnify_op['id']); ?>" <?php checked($omnify_is_checked); ?> style="margin: 0; width: 14px; height: 14px;" />
													<span>
														<?php echo esc_html($omnify_op['name']); ?>
														<?php if (! empty($omnify_op['sku'])) : ?>
															<span style="color: var(--omnify-gray-400); font-size: 10px;">(<?php echo esc_html($omnify_op['sku']); ?>)</span>
														<?php endif; ?>
													</span>
												</label>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<!-- Cross-sells -->
							<div class="omnify-form-group">
								<label style="font-weight: 600; margin-bottom: 6px; display: block;"><?php esc_html_e('Cross-sells (Checkout Recommendations)', 'omnifywp-ecommerce'); ?></label>
								<p class="description" style="margin-top: 0; margin-bottom: 8px; font-size: 11px;">
									<?php esc_html_e('Recommended on the checkout page (e.g. complementary add-ons).', 'omnifywp-ecommerce'); ?>
								</p>
								<input type="text" id="omnify-crosssell-search" placeholder="<?php esc_attr_e('Search products...', 'omnifywp-ecommerce'); ?>" style="width: 100%; margin-bottom: 8px; padding: 6px 10px; border: 1px solid var(--omnify-gray-200); border-radius: 6px; font-size: 13px;" />
								<div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 10px; background: #fff;">
									<?php
									$omnify_cross_sell_ids = $omnify_product && ! empty($omnify_product['cross_sell_ids']) && is_array($omnify_product['cross_sell_ids']) ? $omnify_product['cross_sell_ids'] : [];
									if (empty($omnify_all_other_products)) :
										?>
										<p style="color: var(--omnify-gray-500); margin: 0; font-size: 12px; font-style: italic;"><?php esc_html_e('No other products found.', 'omnifywp-ecommerce'); ?></p>
									<?php else : ?>
										<div class="omnify-crosssell-products-list">
											<?php foreach ($omnify_all_other_products as $omnify_op) : 
												$omnify_is_checked = in_array((int) $omnify_op['id'], $omnify_cross_sell_ids, true);
												?>
												<label class="omnify-crosssell-product-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; cursor: pointer; font-size: 12px; font-weight: 500; color: var(--omnify-gray-800);">
													<input class="omnify-checkbox" type="checkbox" name="cross_sell_ids[]" value="<?php echo esc_attr((string) $omnify_op['id']); ?>" <?php checked($omnify_is_checked); ?> style="margin: 0; width: 14px; height: 14px;" />
													<span>
														<?php echo esc_html($omnify_op['name']); ?>
														<?php if (! empty($omnify_op['sku'])) : ?>
															<span style="color: var(--omnify-gray-400); font-size: 10px;">(<?php echo esc_html($omnify_op['sku']); ?>)</span>
														<?php endif; ?>
													</span>
												</label>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="omnify-card omnify-editor-section-card omnify-content-step-card" id="omnify-simple-trust-message-card">
						<h2><?php esc_html_e('Storefront Trust Message', 'omnifywp-ecommerce'); ?></h2>
						<p class="description" style="margin-bottom: 14px;">
							<?php esc_html_e('Customize the highlighted reassurance message shown below the product gallery.', 'omnifywp-ecommerce'); ?>
						</p>
						<div class="omnify-form-grid" style="max-width: 100%;">
							<div class="omnify-form-group" style="margin-bottom: 14px;">
								<label for="trust_badge_title_simple"><?php esc_html_e('Message Title', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="trust_badge_title_simple" name="trust_badge_title" value="<?php echo esc_attr($omnify_product ? ($omnify_product['trust_badge_title'] ?? '') : ''); ?>" placeholder="<?php echo esc_attr($omnify_default_trust_title); ?>" style="width: 100%;" />
								<span class="help-text"><?php esc_html_e('Leave blank to use the product type default.', 'omnifywp-ecommerce'); ?></span>
							</div>
							<div class="omnify-form-group">
								<label for="trust_badge_text_simple"><?php esc_html_e('Message Text', 'omnifywp-ecommerce'); ?></label>
								<textarea id="trust_badge_text_simple" name="trust_badge_text" rows="3" placeholder="<?php echo esc_attr($omnify_default_trust_text); ?>" style="width: 100%; resize: vertical;"><?php echo esc_textarea($omnify_product ? ($omnify_product['trust_badge_text'] ?? '') : ''); ?></textarea>
							</div>
						</div>
					</div>

					<!-- Media Card: Featured Image + Gallery + Video -->
					<div class="omnify-card omnify-editor-section-card omnify-content-step-card" id="omnify-product-media-card" style="padding: 0; overflow: hidden;">
						<div style="padding: 14px 18px; border-bottom: 1px solid var(--omnify-gray-200); background: var(--omnify-gray-100);">
							<h2 style="margin: 0; font-size: 13px; font-weight: 500; color: var(--omnify-dark); display: inline-flex; align-items: center; gap: 6px;"><span class="dashicons dashicons-format-image" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span><?php esc_html_e('Product Media', 'omnifywp-ecommerce'); ?></h2>
						</div>

						<div class="omnify-media-row-featured-video">
							<!-- Section 1: Featured Image -->
							<div style="padding: 14px 18px; border-right: 1px solid var(--omnify-gray-100);">
								<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;"><?php esc_html_e('Featured Image', 'omnifywp-ecommerce'); ?></div>
								<div style="display: flex; flex-direction: column; align-items: center; gap: 10px; border: 1px dashed var(--omnify-gray-300); padding: 14px; border-radius: 8px; background: var(--omnify-gray-50);">
									<div id="omnify-thumbnail-preview" style="width: 100%; display: flex; justify-content: center; min-height: 60px; align-items: center;">
										<?php if ($omnify_product && $omnify_product['thumbnail_url']) : ?>
											<img src="<?php echo esc_url($omnify_product['thumbnail_url']); ?>" style="max-width: 100%; max-height: 130px; object-fit: contain; border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 4px; background: #fff;" />
										<?php else : ?>
											<p class="description" style="margin: 0; color: var(--omnify-gray-500); font-size: 12px;"><?php esc_html_e('No featured image', 'omnifywp-ecommerce'); ?></p>
										<?php endif; ?>
									</div>
									<div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
										<input type="hidden" id="omnify-thumbnail-id" name="thumbnail_id" value="<?php echo esc_attr($omnify_product ? $omnify_product['thumbnail_id'] : ''); ?>" />
										<button type="button" id="omnify-select-thumbnail" class="omnify-button omnify-button--secondary omnify-button--sm">
											<?php esc_html_e('Set Featured Image', 'omnifywp-ecommerce'); ?>
										</button>
										<button type="button" id="omnify-remove-thumbnail" class="omnify-button omnify-button--danger omnify-button--sm" style="<?php echo ($omnify_product && $omnify_product['thumbnail_url']) ? '' : 'display: none;'; ?>">
											<?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?>
										</button>
									</div>
								</div>
							</div>

							<!-- Section 3: Product Video & Poster -->
							<div style="padding: 14px 18px;">
								<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;"><?php esc_html_e('Product Video & Poster', 'omnifywp-ecommerce'); ?></div>
								
								<label for="omnify-video-url" style="font-size: 11.5px; font-weight: 600; color: var(--omnify-gray-700); margin-bottom: 4px; display: block;"><?php esc_html_e('Video URL', 'omnifywp-ecommerce'); ?></label>
								<input type="url" id="omnify-video-url" name="video_url" value="<?php echo esc_attr($omnify_product ? ($omnify_product['video_url'] ?? '') : ''); ?>" placeholder="https://youtube.com/watch?v=... or Vimeo URL" style="width: 100%; margin-bottom: 12px;" />
								
								<label for="omnify-video-poster-url" style="font-size: 11.5px; font-weight: 600; color: var(--omnify-gray-700); margin-bottom: 4px; display: block;"><?php esc_html_e('Video Poster Image URL', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; gap: 8px; margin-bottom: 4px;">
									<input type="url" id="omnify-video-poster-url" name="video_poster_url" value="<?php echo esc_attr($omnify_product ? ($omnify_product['video_poster_url'] ?? '') : ''); ?>" placeholder="https://example.com/poster.jpg" style="flex: 1;" />
									<button type="button" id="omnify-select-video-poster" class="omnify-button omnify-button--secondary omnify-button--sm"><?php esc_html_e('Select', 'omnifywp-ecommerce'); ?></button>
								</div>
								<p style="font-size: 11px; color: var(--omnify-gray-500); margin: 6px 0 0 0;"><?php esc_html_e('Optional cover image displayed before video playback.', 'omnifywp-ecommerce'); ?></p>
								<div id="omnify-video-preview" style="display: none; margin-top: 8px; border-radius: 8px; overflow: hidden; aspect-ratio: 16/9; background: #000;"></div>
							</div>
						</div>

						<!-- Section 2: Image Gallery -->
						<div style="padding: 14px 18px; border-bottom: 1px solid var(--omnify-gray-100);">
							<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;"><?php esc_html_e('Image Gallery', 'omnifywp-ecommerce'); ?></div>
							<div id="omnify-gallery-preview-grid" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; min-height: 10px;">
								<?php
								$omnify_existing_gallery = $omnify_product ? ($omnify_product['gallery_urls'] ?? []) : [];
								$omnify_existing_gallery_ids = $omnify_product ? ($omnify_product['gallery_ids'] ?? []) : [];
								foreach ($omnify_existing_gallery as $omnify_gidx => $omnify_gurl) :
									$omnify_gid = $omnify_existing_gallery_ids[$omnify_gidx] ?? 0;
								?>
									<div class="omnify-gallery-tile" data-id="<?php echo esc_attr($omnify_gid); ?>" style="position: relative; width: 64px; height: 64px; border-radius: 6px; overflow: hidden; border: 1px solid var(--omnify-gray-300);">
										<img src="<?php echo esc_url($omnify_gurl); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
										<input type="hidden" name="gallery_ids[]" value="<?php echo esc_attr($omnify_gid); ?>" />
										<button type="button" class="omnify-gallery-remove-btn" style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,0.65);color:#fff;border:none;cursor:pointer;font-size:11px;line-height:1;display:flex;align-items:center;justify-content:center;padding:0;">&times;</button>
									</div>
								<?php endforeach; ?>
							</div>
							<button type="button" id="omnify-add-gallery-images" class="omnify-button omnify-button--secondary omnify-button--sm" style="width: 100%; justify-content: center;">
								+ <?php esc_html_e('Add Gallery Images', 'omnifywp-ecommerce'); ?>
							</button>
							<p style="font-size: 11px; color: var(--omnify-gray-500); margin: 6px 0 0 0;"><?php esc_html_e('Shown as a thumbnail strip on the product page.', 'omnifywp-ecommerce'); ?></p>
						</div>
					</div>

					<!-- Categories, Brands, and Tags Row -->
					<div class="omnify-categories-brands-tags-row">
						<!-- Categories Card -->
						<div class="omnify-card omnify-content-step-card" id="omnify-product-categories-card" style="margin-bottom: 0;">
							<h2><?php esc_html_e('Categories', 'omnifywp-ecommerce'); ?></h2>
							<div class="omnify-form-group" style="margin-bottom: 0;">
								<input type="text" id="categories" name="categories" value="<?php echo esc_attr($omnify_product ? implode(', ', $omnify_product['categories']) : ''); ?>" placeholder="e.g. Design, Software" style="width: 100%;" />
								<span class="help-text"><?php esc_html_e('Separate with commas.', 'omnifywp-ecommerce'); ?></span>
								
								<div class="omnify-quick-select" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
									<span style="font-size: 11px; color: var(--omnify-gray-600); align-self: center;"><?php esc_html_e('Quick Add:', 'omnifywp-ecommerce'); ?></span>
									<?php if (empty($omnify_all_categories)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('None', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<?php foreach ($omnify_all_categories as $omnify_exist_cat) : ?>
											<button type="button" class="omnify-badge omnify-badge--category js-quick-add-term" data-target="categories" data-val="<?php echo esc_attr($omnify_exist_cat); ?>" style="cursor: pointer; border: none; font-size: 10px; padding: 2px 8px; font-weight: 500; background: var(--omnify-gray-100); color: var(--omnify-gray-700); border-radius: 12px;">+ <?php echo esc_html($omnify_exist_cat); ?></button>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
								
								<div class="omnify-global-checkboxes-wrapper" style="margin-top: 14px; border-top: 1px solid var(--omnify-gray-200); padding-top: 12px;">
									<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); margin-bottom: 8px;"><?php esc_html_e('Global Categories:', 'omnifywp-ecommerce'); ?></div>
									<?php if (empty($omnify_global_categories)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('No global categories configured.', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<div style="border: 1px solid var(--omnify-gray-200); padding: 10px; border-radius: 6px; background: #fff; max-height: 200px; overflow-y: auto;">
											<?php omnify_render_categories_checkbox_tree($omnify_category_tree, $omnify_product ? $omnify_product['categories'] : []); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Brands Card (like categories) -->
						<div class="omnify-card omnify-content-step-card" id="omnify-product-brands-card" style="margin-bottom: 0;">
							<h2><?php esc_html_e('Brands', 'omnifywp-ecommerce'); ?></h2>
							<div class="omnify-form-group" style="margin-bottom: 0;">
								<input type="text" id="brands" name="brands" value="<?php echo esc_attr($omnify_product ? implode(', ', $omnify_product['brands'] ?? []) : ''); ?>" placeholder="e.g. Nike, Adidas" style="width: 100%;" />
								<span class="help-text"><?php esc_html_e('Separate with commas. Brands are stored like categories/tags and power storefront filters.', 'omnifywp-ecommerce'); ?></span>
								
								<div class="omnify-quick-select" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
									<span style="font-size: 11px; color: var(--omnify-gray-600); align-self: center;"><?php esc_html_e('Quick Add:', 'omnifywp-ecommerce'); ?></span>
									<?php if (empty($omnify_all_brands)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('None', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<?php foreach ($omnify_all_brands as $omnify_exist_brand) : ?>
											<button type="button" class="omnify-badge omnify-badge--category js-quick-add-term" data-target="brands" data-val="<?php echo esc_attr($omnify_exist_brand); ?>" style="cursor: pointer; border: none; font-size: 10px; padding: 2px 8px; font-weight: 500; background: var(--omnify-gray-100); color: var(--omnify-gray-700); border-radius: 12px;">+ <?php echo esc_html($omnify_exist_brand); ?></button>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
								
								<div class="omnify-global-checkboxes-wrapper" style="margin-top: 14px; border-top: 1px solid var(--omnify-gray-200); padding-top: 12px;">
									<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); margin-bottom: 8px;"><?php esc_html_e('Global Brands:', 'omnifywp-ecommerce'); ?></div>
									<?php if (empty($omnify_global_brands)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('No global brands configured. Add in Settings > Brands.', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<div style="border: 1px solid var(--omnify-gray-200); padding: 10px; border-radius: 6px; background: #fff; max-height: 150px; overflow-y: auto;">
											<?php foreach ($omnify_global_brands as $omnify_g_brand) : 
												$omnify_is_checked = $omnify_product && in_array($omnify_g_brand['name'], $omnify_product['brands'] ?? [], true);
											?>
												<label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none; margin-bottom: 4px;">
													<input type="checkbox" class="omnify-checkbox js-global-brand-checkbox" data-name="<?php echo esc_attr($omnify_g_brand['name']); ?>" <?php checked($omnify_is_checked); ?> style="margin: 0;" />
													<span><?php echo esc_html($omnify_g_brand['name']); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Tags Card -->
						<div class="omnify-card omnify-content-step-card" id="omnify-product-tags-card" style="margin-bottom: 0;">
							<h2><?php esc_html_e('Tags', 'omnifywp-ecommerce'); ?></h2>
							<div class="omnify-form-group" style="margin-bottom: 0;">
								<input type="text" id="tags" name="tags" value="<?php echo esc_attr($omnify_product ? implode(', ', $omnify_product['tags']) : ''); ?>" placeholder="e.g. photo, Lightroom" style="width: 100%;" />
								<span class="help-text"><?php esc_html_e('Separate with commas.', 'omnifywp-ecommerce'); ?></span>
								
								<div class="omnify-quick-select" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
									<span style="font-size: 11px; color: var(--omnify-gray-600); align-self: center;"><?php esc_html_e('Quick Add:', 'omnifywp-ecommerce'); ?></span>
									<?php if (empty($omnify_all_tags)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('None', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<?php foreach ($omnify_all_tags as $omnify_exist_tag) : ?>
											<button type="button" class="omnify-badge omnify-badge--category js-quick-add-term" data-target="tags" data-val="<?php echo esc_attr($omnify_exist_tag); ?>" style="cursor: pointer; border: none; font-size: 10px; padding: 2px 8px; font-weight: 500; background: var(--omnify-gray-100); color: var(--omnify-gray-700); border-radius: 12px;">+ <?php echo esc_html($omnify_exist_tag); ?></button>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
								
								<div class="omnify-global-checkboxes-wrapper" style="margin-top: 14px; border-top: 1px solid var(--omnify-gray-200); padding-top: 12px;">
									<div style="font-size: 11px; font-weight: 600; color: var(--omnify-gray-600); margin-bottom: 8px;"><?php esc_html_e('Global Tags:', 'omnifywp-ecommerce'); ?></div>
									<?php if (empty($omnify_global_tags)) : ?>
										<span style="font-size:11px; font-style:italic; color:var(--omnify-gray-500);"><?php esc_html_e('No global tags configured.', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<div style="display: flex; flex-wrap: wrap; gap: 8px; border: 1px solid var(--omnify-gray-200); padding: 10px; border-radius: 6px; background: #fff; max-height: 150px; overflow-y: auto;">
											<?php foreach ($omnify_global_tags as $omnify_g_tag) : 
												$omnify_is_checked = $omnify_product && in_array($omnify_g_tag['name'], $omnify_product['tags'], true);
											?>
												<label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;">
													<input type="checkbox" class="omnify-checkbox js-global-tag-checkbox" data-name="<?php echo esc_attr($omnify_g_tag['name']); ?>" <?php checked($omnify_is_checked); ?> style="margin: 0;" />
													<span><?php echo esc_html($omnify_g_tag['name']); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div> <!-- close step 4 stacked content -->

				<!-- Wizard Navigation -->
				<div class="omnify-wizard-nav">
					<div class="omnify-wizard-nav__left">
						<button type="button" class="omnify-button omnify-button--secondary" id="wizard-prev" disabled>
							<?php esc_html_e('Back', 'omnifywp-ecommerce'); ?>
						</button>
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products')); ?>" class="omnify-button omnify-button--secondary omnify-cancel-button" data-omnify-cancel>
							<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
						</a>
					</div>

					<div class="omnify-wizard-nav__progress">
						<div class="omnify-wizard-nav__progress-label"><?php esc_html_e('Progress', 'omnifywp-ecommerce'); ?></div>
						<div class="omnify-wizard-progress">
							<div class="omnify-wizard-progress-bar" id="omnify-wizard-progress-nav" style="width:25%; height:100%; background:linear-gradient(90deg, #6366f1, #4f46e5); transition:width 0.3s ease;"></div>
						</div>
						<div id="wizard-step-text" class="omnify-wizard-nav__step-text">1 / 4</div>
					</div>

					<div class="omnify-wizard-nav__right">
						<button type="button" class="omnify-button omnify-button--primary" id="wizard-next">
							<?php esc_html_e('Next', 'omnifywp-ecommerce'); ?>
						</button>
						<button type="submit" class="omnify-button omnify-button--primary" id="wizard-save" style="display: none !important;">
							<?php esc_html_e('Save Product', 'omnifywp-ecommerce'); ?>
						</button>
					</div>
				</div>
			</div>

			

			
		</form>

		<?php if ($omnify_action === 'edit' && $omnify_product) : 
			global $wpdb;
			$omnify_logs_table = $wpdb->prefix . 'omnify_inventory_logs';
			$omnify_vars_table = $wpdb->prefix . 'omnify_product_variations';
			$omnify_inventory_logs = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
				$wpdb->prepare(
					"SELECT l.*, v.sku as var_sku, v.attributes as var_attributes, u.display_name as user_name
					 FROM %i l
					 LEFT JOIN %i v ON l.variation_id = v.id
					 LEFT JOIN %i u ON l.user_id = u.ID
					 WHERE l.product_id = %d
					 ORDER BY l.created_at DESC",
					$omnify_logs_table,
					$omnify_vars_table,
					$wpdb->users,
					(int) $omnify_product['id']
				),
				ARRAY_A
			);
			$omnify_inventory_logs = is_array($omnify_inventory_logs) ? $omnify_inventory_logs : [];
		?>
			<div class="omnify-card" style="margin-top: 24px;">
				<div class="omnify-card__header">
					<h2><?php esc_html_e('Inventory Logs', 'omnifywp-ecommerce'); ?></h2>
				</div>
				<?php if (empty($omnify_inventory_logs)) : ?>
					<p style="color: var(--shopify-gray-600); font-size: 13px; padding: 20px 0;"><?php esc_html_e('No inventory changes recorded yet.', 'omnifywp-ecommerce'); ?></p>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto; margin-top: 16px;">
						<table class="omnify-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="border-bottom: 1px solid var(--omnify-gray-200); text-align: left;">
									<th style="width: 180px; padding: 10px 0;"><?php esc_html_e('Date & Time', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Item / Variation', 'omnifywp-ecommerce'); ?></th>
									<th style="width: 100px; text-align: center;"><?php esc_html_e('Adjustment', 'omnifywp-ecommerce'); ?></th>
									<th style="width: 100px; text-align: center;"><?php esc_html_e('New Qty', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Reason / Event', 'omnifywp-ecommerce'); ?></th>
									<th style="width: 140px;"><?php esc_html_e('Modified By', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_inventory_logs as $omnify_log) : 
									$omnify_change_val = (int) $omnify_log['change_qty'];
									$omnify_change_class = $omnify_change_val > 0 ? 'color: #059669; font-weight: 500;' : 'color: #dc2626; font-weight: 500;';
									$omnify_change_text = $omnify_change_val > 0 ? '+' . $omnify_change_val : (string) $omnify_change_val;
									
									$omnify_item_desc = __('Main Product', 'omnifywp-ecommerce');
									if (! empty($omnify_log['variation_id'])) {
										$omnify_attrs = ! empty($omnify_log['var_attributes']) ? json_decode($omnify_log['var_attributes'], true) : [];
										$omnify_attr_desc = [];
										if (is_array($omnify_attrs)) {
											foreach ($omnify_attrs as $omnify_k => $omnify_v) {
												$omnify_attr_desc[] = esc_html($omnify_k . ': ' . $omnify_v);
											}
										}
										$omnify_item_desc = ! empty($omnify_log['var_sku']) 
											// translators: %1$s: placeholder value, %2$s: placeholder value.
											? sprintf(__('Var: %1$s (%2$s)', 'omnifywp-ecommerce'), esc_html($omnify_log['var_sku']), implode(', ', $omnify_attr_desc))
											// translators: %s: placeholder value.
											: sprintf(__('Var (%s)', 'omnifywp-ecommerce'), implode(', ', $omnify_attr_desc));
									}
								?>
									<tr style="border-bottom: 1px solid var(--omnify-gray-100);">
										<td style="padding: 12px 0;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_log['created_at']))); ?></td>
										<td><strong><?php echo esc_html($omnify_item_desc); ?></strong></td>
										<td style="text-align: center; <?php echo esc_attr($omnify_change_class); ?>"><?php echo esc_html($omnify_change_text); ?></td>
										<td style="text-align: center; font-weight: 600;"><?php echo esc_html($omnify_log['new_qty']); ?></td>
										<td><?php echo esc_html($omnify_log['reason']); ?></td>
										<td><?php echo esc_html($omnify_log['user_name'] ?: __('System', 'omnifywp-ecommerce')); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<!-- Product List Table -->
		<?php
		$omnify_filter_search    = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
		$omnify_filter_category  = isset($_GET['category']) ? sanitize_text_field(wp_unslash($_GET['category'])) : '';
		$omnify_filter_type      = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
		$omnify_filter_status    = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
		$omnify_filter_date_from = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
		$omnify_filter_date_to   = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
		$omnify_filter_sort      = isset($_GET['sort']) ? sanitize_key(wp_unslash($_GET['sort'])) : '';
		?>
	<!-- Products Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-products" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_product"><?php esc_html_e('Search', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_product" name="search" placeholder="<?php esc_attr_e('Search product...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="category"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></label>
				<select id="category" name="category">
					<option value=""><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></option>
					<?php foreach ($omnify_filter_categories as $omnify_cat): ?>
						<option value="<?php echo esc_attr($omnify_cat); ?>" <?php selected($omnify_filter_category, $omnify_cat); ?>><?php echo esc_html($omnify_cat); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="product_type"><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></label>
				<select id="product_type" name="type">
					<option value=""><?php esc_html_e('All Types', 'omnifywp-ecommerce'); ?></option>
					<option value="download" <?php selected($omnify_filter_type, 'download'); ?>><?php esc_html_e('Digital', 'omnifywp-ecommerce'); ?></option>
					<option value="physical" <?php selected($omnify_filter_type, 'physical'); ?>><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></option>
					<option value="variable" <?php selected($omnify_filter_type, 'variable'); ?>><?php esc_html_e('Variable', 'omnifywp-ecommerce'); ?></option>
					<option value="bundle" <?php selected($omnify_filter_type, 'bundle'); ?>><?php esc_html_e('Bundle', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Product Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Statuses', 'omnifywp-ecommerce'); ?></option>
					<option value="published" <?php selected($omnify_filter_status, 'published'); ?>><?php esc_html_e('Published', 'omnifywp-ecommerce'); ?></option>
					<option value="draft" <?php selected($omnify_filter_status, 'draft'); ?>><?php esc_html_e('Draft', 'omnifywp-ecommerce'); ?></option>
					<option value="trash" <?php selected($omnify_filter_status, 'trash'); ?>><?php esc_html_e('Trash', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="date_from"><?php esc_html_e('From', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_filter_date_from); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="date_to"><?php esc_html_e('To', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($omnify_filter_date_to); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="sort_by"><?php esc_html_e('Sort By', 'omnifywp-ecommerce'); ?></label>
				<select id="sort_by" name="sort">
					<option value=""><?php esc_html_e('Newest', 'omnifywp-ecommerce'); ?></option>
					<option value="name_asc" <?php selected($omnify_filter_sort, 'name_asc'); ?>><?php esc_html_e('Name A-Z', 'omnifywp-ecommerce'); ?></option>
					<option value="name_desc" <?php selected($omnify_filter_sort, 'name_desc'); ?>><?php esc_html_e('Name Z-A', 'omnifywp-ecommerce'); ?></option>
					<option value="price_asc" <?php selected($omnify_filter_sort, 'price_asc'); ?>><?php echo esc_html(__('Price ↑', 'omnifywp-ecommerce')); ?></option>
					<option value="price_desc" <?php selected($omnify_filter_sort, 'price_desc'); ?>><?php echo esc_html(__('Price ↓', 'omnifywp-ecommerce')); ?></option>
				</select>
			</div>
			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active Filters Chips (modern style) -->
	<?php
	$omnify_active_chips = [];
	if ('' !== $omnify_filter_search) {
		$omnify_active_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . $omnify_filter_search,
			'url'   => remove_query_arg('search'),
		];
	}
	if ('' !== $omnify_filter_category) {
		$omnify_active_chips['category'] = [
			'label' => __('Category', 'omnifywp-ecommerce') . ': ' . $omnify_filter_category,
			'url'   => remove_query_arg('category'),
		];
	}
	if ('' !== $omnify_filter_type) {
		$omnify_active_chips['type'] = [
			'label' => __('Type', 'omnifywp-ecommerce') . ': ' . $omnify_filter_type,
			'url'   => remove_query_arg('type'),
		];
	}
	if ('' !== $omnify_filter_status) {
		$omnify_active_chips['status'] = [
			'label' => __('Status', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_filter_status),
			'url'   => remove_query_arg('status'),
		];
	}
	if ('' !== $omnify_filter_date_from || '' !== $omnify_filter_date_to) {
		$omnify_date_label = trim($omnify_filter_date_from . ' - ' . $omnify_filter_date_to);
		$omnify_active_chips['date'] = [
			'label' => __('Date', 'omnifywp-ecommerce') . ': ' . $omnify_date_label,
			'url'   => remove_query_arg(['date_from', 'date_to']),
		];
	}
	?>
	<?php if (!empty($omnify_active_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500); margin-right:6px; font-weight:600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach ($omnify_active_chips as $omnify_chip): ?>
			<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
				<?php echo esc_html($omnify_chip['label']); ?>
				<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">&times;</a>
			</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="omnify-card">

			<?php if (empty($omnify_products)) : ?>
				<div class="omnify-empty-state">
					<div class="omnify-empty-state__icon"><span class="dashicons dashicons-archive" style="font-size: 48px; width: 48px; height: 48px; color: var(--omnify-gray-400);"></span></div>
					<h3><?php esc_html_e('Add your first product', 'omnifywp-ecommerce'); ?></h3>
					<p><?php esc_html_e('You can sell templates, presets, software, ebooks, or any digital downloads.', 'omnifywp-ecommerce'); ?></p>
					<div style="margin-top: 20px;">
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=new')); ?>" class="omnify-button omnify-button--primary">
							<?php esc_html_e('Create Product', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>
			<?php else : ?>
				<form id="omnify-bulk-products-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_bulk_products', 'omnify_bulk_nonce'); ?>
					<input type="hidden" name="action" value="omnify_bulk_products" />

					<!-- Table Toolbar (modern, like reference design) -->
					<div class="omnify-table-toolbar">
						<div class="left">
							<input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" style="margin-right:8px;" />
							<span class="selected-count">0 selected</span>
							<div class="omnify-bulk-actions-wrapper" style="display: none; align-items: center; gap: 8px; margin-left: 16px;">
								<select name="bulk_action">
									<option value="">Bulk actions</option>
									<option value="trash">Move to Trash</option>
									<option value="restore">Restore</option>
								</select>
								<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm">Apply</button>
							</div>
						</div>
						<div class="right">
							<a href="<?php echo esc_url(add_query_arg('export_csv', '1')); ?>" class="omnify-button omnify-button--primary omnify-button--sm" style="display:inline-flex; align-items:center; gap:6px;">
								<span class="dashicons dashicons-download" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
								<?php esc_html_e('Export', 'omnifywp-ecommerce'); ?>
							</a>
						</div>
					</div>

				<div class="omnify-table-wrapper">
					<table class="omnify-table">
						<thead>
							<tr>
								<th style="width: 30px;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
								<th style="width: 60px;"><?php esc_html_e('Image', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Product Name', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Categories', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Tags', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Files', 'omnifywp-ecommerce'); ?></th>
								<th style="text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_products as $omnify_prod) : 
								$omnify_prod_files = $omnify_product_files_repo->all_for_product((int) $omnify_prod['id']);
								$omnify_file_count = count($omnify_prod_files);
								$omnify_prod_variation_settings = wp_parse_args($omnify_prod['variation_settings'] ?? [], ['product_kind' => 'digital']);
								$omnify_prod_saved_kind = $omnify_prod['product_kind'] ?? ($omnify_prod_variation_settings['product_kind'] ?? 'digital');
								$omnify_prod_has_physical_meta = ! empty($omnify_prod['sku']) || ! empty($omnify_prod['weight']) || ! empty($omnify_prod['length']) || ! empty($omnify_prod['width']) || ! empty($omnify_prod['height']);
								$omnify_prod_is_physical = (isset($omnify_prod['type']) && $omnify_prod['type'] === 'physical')
									|| (isset($omnify_prod['type']) && $omnify_prod['type'] === 'variable' && 'physical' === $omnify_prod_saved_kind)
									|| (isset($omnify_prod['type']) && $omnify_prod['type'] === 'variable' && empty($omnify_prod_files) && $omnify_prod_has_physical_meta);
							?>
								<tr>
									<td><input type="checkbox" class="omnify-checkbox omnify-bulk-item" name="product_ids[]" value="<?php echo esc_attr($omnify_prod['id']); ?>" /></td>
									<td>
										<?php if ($omnify_prod['thumbnail_url']) : ?>
											<img src="<?php echo esc_url($omnify_prod['thumbnail_url']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--omnify-gray-200);" />
										<?php else : ?>
											<div style="width: 40px; height: 40px; background: var(--omnify-gray-100); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--omnify-gray-400); font-size: 14px; font-weight: 600; border: 1px solid var(--omnify-gray-200);">
												<?php echo esc_html(strtoupper(substr($omnify_prod['name'], 0, 1))); ?>
											</div>
										<?php endif; ?>
									</td>
									<td>
										<div style="display: flex; flex-direction: column; gap: 1px;">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=view&id=' . $omnify_prod['id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--omnify-dark);">
												<?php echo esc_html($omnify_prod['name']); ?>
											</a>
											<div class="subtle">/storefront?product=<?php echo esc_html($omnify_prod['slug']); ?></div>
											<div style="margin-top:2px;">
												<?php if ($omnify_prod_is_physical) : ?>
													<span class="omnify-status omnify-status--completed" style="font-size: 9px; padding: 1px 5px; margin-right:2px; background: var(--omnify-gray-100); color: var(--omnify-gray-600);"><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></span>
												<?php elseif (isset($omnify_prod['type']) && $omnify_prod['type'] === 'variable') : ?>
													<span class="omnify-status omnify-status--completed" style="font-size: 9px; padding: 1px 5px; margin-right:2px; background: var(--omnify-primary-light); color: var(--omnify-primary);"><?php esc_html_e('Variable', 'omnifywp-ecommerce'); ?></span>
												<?php elseif (isset($omnify_prod['type']) && $omnify_prod['type'] === 'bundle') : ?>
													<span class="omnify-status omnify-status--completed" style="font-size: 9px; padding: 1px 5px; margin-right:2px; background: #faf5ff; color: #6b21a8;"><?php esc_html_e('Bundle', 'omnifywp-ecommerce'); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</td>
									<td><strong><?php echo esc_html($omnify_symbol . number_format($omnify_prod['price'], 2)); ?></strong></td>
									<td>
										<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_prod['status']); ?>">
											<?php echo esc_html(ucfirst($omnify_prod['status'])); ?>
										</span>
									</td>
									<td>
										<?php if (empty($omnify_prod['categories'])) : ?>
											<span style="color: var(--omnify-gray-400); font-style: italic; font-size: 12px;">&mdash;</span>
										<?php else : ?>
											<?php echo esc_html(implode(', ', array_slice($omnify_prod['categories'], 0, 2))); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php if (empty($omnify_prod['tags'])) : ?>
											<span style="color: var(--omnify-gray-400); font-style: italic; font-size: 12px;">&mdash;</span>
										<?php else : ?>
											<?php echo esc_html(implode(', ', array_slice($omnify_prod['tags'], 0, 2))); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($omnify_prod_is_physical) : ?>
											<span style="color: var(--omnify-gray-600); font-style: italic; font-size: 12px;"><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></span>
										<?php else : ?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_prod['id'])); ?>" style="color: var(--omnify-primary); font-weight: 600; text-decoration: none; font-size: 12px;">
												<?php 
												// translators: %d: placeholder value.
												echo esc_html(sprintf(_n('%d file', '%d files', $omnify_file_count, 'omnifywp-ecommerce'), $omnify_file_count)); ?>
											</a>
										<?php endif; ?>
									</td>
									<td style="text-align: right;">
										<div class="omnify-row-actions">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_prod['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Edit', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
											<a href="#" class="omnify-icon-btn omnify-quick-edit" data-id="<?php echo esc_attr($omnify_prod['id']); ?>" data-name="<?php echo esc_attr($omnify_prod['name']); ?>" data-price="<?php echo esc_attr($omnify_prod['price']); ?>" data-sale-price="<?php echo esc_attr($omnify_prod['sale_price'] ?? ''); ?>" data-status="<?php echo esc_attr($omnify_prod['status']); ?>" data-sku="<?php echo esc_attr($omnify_prod['sku'] ?? ''); ?>" title="<?php esc_attr_e('Quick Edit', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-welcome-write-blog" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
											<?php $omnify_is_trashed = !empty($omnify_prod['deleted_at']); ?>
											<?php if ($omnify_is_trashed): ?>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_product&id=' . $omnify_prod['id']), 'omnify_restore_product_' . $omnify_prod['id'])); ?>" class="omnify-icon-btn" title="Restore">
													<span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
												</a>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_product&id=' . $omnify_prod['id']), 'omnify_delete_product_' . $omnify_prod['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Delete permanently" onclick="return confirm('Permanently delete?');">
													<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
												</a>
											<?php else: ?>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_product&id=' . $omnify_prod['id']), 'omnify_trash_product_' . $omnify_prod['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Move to trash" onclick="return confirm('Move to trash?');">
													<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
												</a>
											<?php endif; ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				</form>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Quick Edit Modal -->
	<div id="omnify-quick-edit-modal" class="omnify-modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5);">
		<div class="omnify-modal-content" style="background:#fff; margin:5% auto; padding:20px; border-radius:8px; width:90%; max-width:500px; position:relative;">
			<span class="omnify-modal-close" style="position:absolute; top:10px; right:15px; font-size:24px; cursor:pointer;">&times;</span>
			<h3 style="margin-top:0;"><?php esc_html_e('Quick Edit Product', 'omnifywp-ecommerce'); ?></h3>
			<form id="omnify-quick-edit-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('omnify_quick_edit_product', 'omnify_quick_nonce'); ?>
				<input type="hidden" name="action" value="omnify_quick_edit_product" />
				<input type="hidden" name="id" id="qe-id" />

				<div class="omnify-form-group">
					<label for="qe-name"><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></label>
					<input type="text" id="qe-name" name="name" required />
				</div>

				<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
					<div class="omnify-form-group">
						<label for="qe-price"><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></label>
						<input type="number" step="0.01" id="qe-price" name="price" required />
					</div>
					<div class="omnify-form-group">
						<label for="qe-sale-price"><?php esc_html_e('Sale Price', 'omnifywp-ecommerce'); ?></label>
						<input type="number" step="0.01" id="qe-sale-price" name="sale_price" />
					</div>
				</div>

				<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
					<div class="omnify-form-group">
						<label for="qe-status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
						<select id="qe-status" name="status">
							<option value="draft"><?php esc_html_e('Draft', 'omnifywp-ecommerce'); ?></option>
							<option value="published"><?php esc_html_e('Published', 'omnifywp-ecommerce'); ?></option>
						</select>
					</div>
					<div class="omnify-form-group">
						<label for="qe-sku"><?php esc_html_e('SKU', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="qe-sku" name="sku" />
					</div>
				</div>

				<div class="omnify-button-group" style="margin-top:16px;">
					<button type="button" class="omnify-button omnify-button--secondary" id="qe-cancel"><?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?></button>
					<button type="submit" class="omnify-button omnify-button--primary"><?php esc_html_e('Save Changes', 'omnifywp-ecommerce'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>


