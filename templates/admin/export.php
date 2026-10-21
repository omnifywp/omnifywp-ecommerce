<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_error = $omnify_template_vars['omnify_error'] ?? null;
$omnify_export = $omnify_template_vars['omnify_export'] ?? null;
$omnify_exports = $omnify_template_vars['omnify_exports'] ?? null;
$omnify_headers = $omnify_template_vars['omnify_headers'] ?? null;
$omnify_import_result = $omnify_template_vars['omnify_import_result'] ?? null;
$omnify_import_types = $omnify_template_vars['omnify_import_types'] ?? null;
$omnify_label = $omnify_template_vars['omnify_label'] ?? null;
$omnify_result_type = $omnify_template_vars['omnify_result_type'] ?? null;
$omnify_value = $omnify_template_vars['omnify_value'] ?? null;


/**
 * Admin Import/Export Template
 *
 * @package Omnify
 */
$omnify_exports = [
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
		'theme' => 'products',
		'format' => 'CSV',
		'title' => __('Products (CSV)', 'omnifywp-ecommerce'),
		'description' => __('Catalog data including pricing, product type, stock, shipping class, categories, tags, and descriptions.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_products',
		'nonce' => 'omnify_export_products',
		'button' => __('Export Products (CSV)', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
		'theme' => 'products-xml',
		'format' => 'XML',
		'title' => __('Products (XML)', 'omnifywp-ecommerce'),
		'description' => __('Structured XML with nested variations, categories, files support for richer import/export.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_products_xml',
		'nonce' => 'omnify_export_products_xml',
		'button' => __('Export Products (XML)', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
		'theme' => 'customers',
		'format' => 'CSV',
		'title' => __('Customers Directory', 'omnifywp-ecommerce'),
		'description' => __('Customer profiles, registered emails, account status, company, country, and tax exemption status.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_customers',
		'nonce' => 'omnify_export_customers',
		'button' => __('Export Customers', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>',
		'theme' => 'orders',
		'format' => 'CSV',
		'title' => __('Store Orders', 'omnifywp-ecommerce'),
		'description' => __('Order totals, transaction status, customer email, taxes, shipping, payment methods, coupons, and dates.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_orders',
		'nonce' => 'omnify_export_orders',
		'button' => __('Export Orders', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m20.59 13.41-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/></svg>',
		'theme' => 'coupons',
		'format' => 'CSV',
		'title' => __('Coupons & Discounts', 'omnifywp-ecommerce'),
		'description' => __('Discount rules, usage limits, free shipping flags, product/category restrictions, and expiration dates.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_coupons',
		'nonce' => 'omnify_export_coupons',
		'button' => __('Export Coupons', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>',
		'theme' => 'tax',
		'format' => 'CSV',
		'title' => __('Regional Tax Rules', 'omnifywp-ecommerce'),
		'description' => __('Country/state tax rules with rates, reporting codes, evaluation priorities, and enabled state.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_tax_rules',
		'nonce' => 'omnify_export_tax_rules',
		'button' => __('Export Tax Rules', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
		'theme' => 'delivery',
		'format' => 'CSV',
		'title' => __('Delivery & Shipping Zones', 'omnifywp-ecommerce'),
		'description' => __('Shipping zones, country/state scopes, method type, base rates, free shipping order minimums, and class rates.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_delivery_zones',
		'nonce' => 'omnify_export_delivery_zones',
		'button' => __('Export Delivery Zones', 'omnifywp-ecommerce'),
	],
	[
		'icon_svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>',
		'theme' => 'downloads',
		'format' => 'AUDIT',
		'title' => __('Digital Download Logs', 'omnifywp-ecommerce'),
		'description' => __('Digital file download audit logs with product ID, file, customer, IP address, user agent, and timestamp.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_downloads',
		'nonce' => 'omnify_export_downloads',
		'button' => __('Export Download Logs', 'omnifywp-ecommerce'),
	],
];

$omnify_import_types = [
	'products' => __('Products', 'omnifywp-ecommerce'),
	'customers' => __('Customers', 'omnifywp-ecommerce'),
	'orders' => __('Orders', 'omnifywp-ecommerce'),
	'coupons' => __('Coupons', 'omnifywp-ecommerce'),
	'tax_rules' => __('Tax Rules', 'omnifywp-ecommerce'),
	'delivery_zones' => __('Delivery Zones', 'omnifywp-ecommerce'),
];

$omnify_headers = [
	'products' => 'name, slug, type, product_kind, sales_type, price, sale_price, currency, sku, status, stock_status, manage_stock, stock_qty, allow_backorders, preorder_enabled, preorder_release_date, preorder_limit, preorder_message, shipping_class, weight, length, width, height, download_expiry_days, categories, tags, description',
	'customers' => 'email, first_name, last_name, status, phone, company, country, tax_exempt',
	'orders' => 'order_number, customer_email, status, currency, subtotal, tax, shipping_total, total, payment_method, coupon_code, discount_amount',
	'coupons' => 'code, is_active, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, usage_limit_per_customer, free_shipping, first_order_only, included_product_ids, excluded_product_ids, included_categories, excluded_categories, expires_at',
	'tax_rules' => 'enabled, label, country, state, rate, priority, reporting_code',
	'delivery_zones' => 'enabled, name, countries, states, method_name, method_type, cost, free_min, class_costs, priority',
];

$omnify_schemas = [
	'products' => [
		'label' => __('Products', 'omnifywp-ecommerce'),
		'theme' => 'products',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
		'required' => ['name'],
		'headers' => $omnify_headers['products'],
	],
	'customers' => [
		'label' => __('Customers', 'omnifywp-ecommerce'),
		'theme' => 'customers',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
		'required' => ['email'],
		'headers' => $omnify_headers['customers'],
	],
	'orders' => [
		'label' => __('Orders', 'omnifywp-ecommerce'),
		'theme' => 'orders',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>',
		'required' => ['order_number', 'customer_email'],
		'headers' => $omnify_headers['orders'],
	],
	'coupons' => [
		'label' => __('Coupons', 'omnifywp-ecommerce'),
		'theme' => 'coupons',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m20.59 13.41-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/></svg>',
		'required' => ['code', 'discount_type', 'discount_value'],
		'headers' => $omnify_headers['coupons'],
	],
	'tax_rules' => [
		'label' => __('Tax Rules', 'omnifywp-ecommerce'),
		'theme' => 'tax',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>',
		'required' => ['label', 'country', 'state', 'rate'],
		'headers' => $omnify_headers['tax_rules'],
	],
	'delivery_zones' => [
		'label' => __('Delivery Zones', 'omnifywp-ecommerce'),
		'theme' => 'delivery',
		'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
		'required' => ['name', 'countries', 'states', 'method_name'],
		'headers' => $omnify_headers['delivery_zones'],
	],
];

$omnify_result_type = is_array($omnify_import_result ?? null) ? (string) ($omnify_import_result['type'] ?? '') : '';
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'export'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Import / Export', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Move store records in and out with schema-validated CSV files for products, customers, orders, coupons, tax tables, and delivery zones.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url('https://omnifywp.com/doc/'); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
				<?php esc_html_e('Documentation', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php $omnify_active_tab = 'export'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs-track">
		<div class="omnify-subtabs">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-export')); ?>" class="omnify-subtab is-active" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-database-export" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Import/Export', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-api')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-network" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('API Keys', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-tools')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-tools" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('System Tools & Seeding', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<!-- Top Guidance Banner -->
	<div class="omnify-export-info-banner">
		<div class="omnify-export-info-banner-icon">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/><path d="m14 2 4 4-4 4"/></svg>
		</div>
		<div class="omnify-export-info-banner-content">
			<h4><?php esc_html_e('Store Data Portability & Bulk Operations', 'omnifywp-ecommerce'); ?></h4>
			<p><?php esc_html_e('Export full product catalogs, customer directories, order history, coupons, tax rules, and delivery scopes in standardized CSV or XML formats. Ingest new records or perform bulk updates with schema validation.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<?php if (is_array($omnify_import_result ?? null)) : ?>
		<div class="omnify-card" style="border-left: 4px solid <?php echo empty($omnify_import_result['errors']) ? '#10b981' : '#f59e0b'; ?>; padding: 20px; background: #ffffff; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);">
			<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
				<span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: <?php echo empty($omnify_import_result['errors']) ? '#ecfdf5' : '#fffbeb'; ?>; color: <?php echo empty($omnify_import_result['errors']) ? '#059669' : '#d97706'; ?>;">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
				</span>
				<h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
					<?php
					printf(
						/* translators: %s: import type */
						esc_html__('Import Finished: %s', 'omnifywp-ecommerce'),
						esc_html($omnify_import_types[$omnify_result_type] ?? ucfirst(str_replace('_', ' ', $omnify_result_type)))
					);
					?>
				</h3>
			</div>
			<p style="margin: 0 0 12px; color: #475569; font-size: 13.5px;">
				<?php
				printf(
					/* translators: 1: created count, 2: updated count, 3: skipped count */
					esc_html__('%1$d created, %2$d updated, %3$d skipped.', 'omnifywp-ecommerce'),
					(int) ($omnify_import_result['created'] ?? 0),
					(int) ($omnify_import_result['updated'] ?? 0),
					(int) ($omnify_import_result['skipped'] ?? 0)
				);
				?>
			</p>
			<?php if (! empty($omnify_import_result['errors']) && is_array($omnify_import_result['errors'])) : ?>
				<div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-top: 10px;">
					<strong style="display: block; font-size: 12.5px; color: #b45309; margin-bottom: 6px;"><?php esc_html_e('Validation Notices / Skipped Rows:', 'omnifywp-ecommerce'); ?></strong>
					<ul style="margin: 0; padding-left: 18px; color: #92400e; font-size: 12.5px; line-height: 1.5;">
						<?php foreach (array_slice($omnify_import_result['errors'], 0, 12) as $omnify_error) : ?>
							<li><?php echo esc_html((string) $omnify_error); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Main Two-Column Layout -->
	<div class="omnify-io-main-layout">
		<!-- Left: Export Cards Grid Container -->
		<div class="omnify-card" style="padding: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div class="omnify-io-section-header">
				<h2><?php esc_html_e('Export Store Data', 'omnifywp-ecommerce'); ?></h2>
				<p><?php esc_html_e('Download current store records as CSV or structured XML with complete metadata.', 'omnifywp-ecommerce'); ?></p>
			</div>

			<div class="omnify-modern-export-grid">
				<?php foreach ($omnify_exports as $omnify_export) : ?>
					<div class="omnify-modern-export-card">
						<div class="omnify-modern-export-card-header">
							<div class="omnify-modern-export-icon omnify-export-theme--<?php echo esc_attr($omnify_export['theme']); ?>">
								<?php echo $omnify_export['icon_svg']; ?>
							</div>
							<span class="omnify-format-badge omnify-format-badge--<?php echo strtolower(esc_attr($omnify_export['format'])); ?>">
								<?php echo esc_html($omnify_export['format']); ?>
							</span>
						</div>
						<h3 class="omnify-modern-export-title"><?php echo esc_html($omnify_export['title']); ?></h3>
						<p class="omnify-modern-export-desc"><?php echo esc_html($omnify_export['description']); ?></p>
						<?php if ('orders' === $omnify_export['theme']) : ?>
							<form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: grid; gap: 8px; margin-top: auto;">
								<input type="hidden" name="action" value="<?php echo esc_attr($omnify_export['action']); ?>">
								<?php wp_nonce_field($omnify_export['nonce'], '_wpnonce', false); ?>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
									<input type="date" name="date_from" placeholder="<?php esc_attr_e('From', 'omnifywp-ecommerce'); ?>" style="font-size: 11px; padding: 4px 6px; border: 1px solid #CBD5E1; border-radius: 6px;" title="<?php esc_attr_e('Start Date', 'omnifywp-ecommerce'); ?>" />
									<input type="date" name="date_to" placeholder="<?php esc_attr_e('To', 'omnifywp-ecommerce'); ?>" style="font-size: 11px; padding: 4px 6px; border: 1px solid #CBD5E1; border-radius: 6px;" title="<?php esc_attr_e('End Date', 'omnifywp-ecommerce'); ?>" />
								</div>
								<select name="status" style="font-size: 11px; padding: 4px 6px; border: 1px solid #CBD5E1; border-radius: 6px; background: #FFF;">
									<option value=""><?php esc_html_e('All Order Statuses', 'omnifywp-ecommerce'); ?></option>
									<option value="completed"><?php esc_html_e('Completed', 'omnifywp-ecommerce'); ?></option>
									<option value="processing"><?php esc_html_e('Processing', 'omnifywp-ecommerce'); ?></option>
									<option value="pending"><?php esc_html_e('Pending Payment', 'omnifywp-ecommerce'); ?></option>
									<option value="shipped"><?php esc_html_e('Shipped', 'omnifywp-ecommerce'); ?></option>
									<option value="refunded"><?php esc_html_e('Refunded', 'omnifywp-ecommerce'); ?></option>
									<option value="cancelled"><?php esc_html_e('Cancelled', 'omnifywp-ecommerce'); ?></option>
								</select>
								<button type="submit" class="omnify-modern-export-btn" style="border: none; cursor: pointer; width: 100%;">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
									<span><?php echo esc_html($omnify_export['button']); ?></span>
								</button>
							</form>
						<?php else : ?>
							<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=' . $omnify_export['action']), $omnify_export['nonce'])); ?>" class="omnify-modern-export-btn">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
								<span><?php echo esc_html($omnify_export['button']); ?></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Right: Import Data Sidebar -->
		<div class="omnify-modern-import-card">
			<div class="omnify-io-import-header">
				<div class="omnify-import-header-badge">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
					<span><?php esc_html_e('Bulk Ingestion', 'omnifywp-ecommerce'); ?></span>
				</div>
				<h2><?php esc_html_e('Import Data', 'omnifywp-ecommerce'); ?></h2>
				<p><?php esc_html_e('Upload a valid CSV or XML dataset to create or synchronize store records.', 'omnifywp-ecommerce'); ?></p>
			</div>

			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
				<input type="hidden" name="action" value="omnify_import_csv">
				<?php wp_nonce_field('omnify_import_csv', 'omnify_import_nonce'); ?>

				<div class="omnify-form-group">
					<label for="omnify-import-type" style="display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px;"><?php esc_html_e('Target Entity Type', 'omnifywp-ecommerce'); ?></label>
					<select id="omnify-import-type" name="import_type" required style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 9px 12px; font-size: 13.5px; background: #ffffff;">
						<?php foreach ($omnify_import_types as $omnify_value => $omnify_label) : ?>
							<option value="<?php echo esc_attr($omnify_value); ?>"><?php echo esc_html($omnify_label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="omnify-form-group">
					<label for="omnify-import-mode" style="display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px;"><?php esc_html_e('Collision Handling Mode', 'omnifywp-ecommerce'); ?></label>
					<select id="omnify-import-mode" name="import_mode" required style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 9px 12px; font-size: 13.5px; background: #ffffff;">
						<option value="upsert"><?php esc_html_e('Update existing, create missing', 'omnifywp-ecommerce'); ?></option>
						<option value="create"><?php esc_html_e('Create only, skip existing matches', 'omnifywp-ecommerce'); ?></option>
						<option value="replace"><?php esc_html_e('Replace tax/delivery settings rows', 'omnifywp-ecommerce'); ?></option>
					</select>
				</div>

				<div class="omnify-form-group">
					<label style="display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px;"><?php esc_html_e('Upload CSV / XML Dataset', 'omnifywp-ecommerce'); ?></label>
					<div class="omnify-dropzone" id="omnify-io-dropzone">
						<input id="omnify-import-file" type="file" name="import_file" accept=".csv,.xml,text/csv,application/xml" required>
						<div class="omnify-dropzone-prompt" id="omnify-dropzone-prompt">
							<div class="omnify-dropzone-icon">
								<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
							</div>
							<strong class="omnify-dropzone-title"><?php esc_html_e('Choose file or drag & drop', 'omnifywp-ecommerce'); ?></strong>
							<span class="omnify-dropzone-subtitle"><?php esc_html_e('Supports CSV or XML in UTF-8 format', 'omnifywp-ecommerce'); ?></span>
						</div>
						<div class="omnify-dropzone-file-selected" id="omnify-dropzone-file-selected">
							<div class="omnify-dropzone-file-pill">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
								<span class="omnify-dropzone-file-name" id="omnify-chosen-filename">file.csv</span>
								<span class="omnify-dropzone-file-size" id="omnify-chosen-filesize">0 KB</span>
							</div>
							<span style="font-size: 11.5px; color: #0d9488; font-weight: 600; text-decoration: underline; margin-top: 4px;"><?php esc_html_e('Click to replace file', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>
					<p style="font-size: 12px; color: #64748b; margin: 6px 0 0; line-height: 1.4;"><?php esc_html_e('The first row must contain matching column header names.', 'omnifywp-ecommerce'); ?></p>
				</div>

				<button type="submit" class="omnify-button omnify-button--primary" style="justify-content: center; width: 100%; height: 42px; border-radius: 10px; font-weight: 700; font-size: 14px; gap: 8px;">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
					<span><?php esc_html_e('Start Import Process', 'omnifywp-ecommerce'); ?></span>
				</button>
			</form>
		</div>
	</div>

	<!-- CSV Schema Reference Card -->
	<div class="omnify-schema-ref-card">
		<div class="omnify-io-section-header">
			<h2><?php esc_html_e('CSV Schema & Header Reference', 'omnifywp-ecommerce'); ?></h2>
			<p><?php esc_html_e('Required fields are validated during import. Column order does not matter; extra columns are ignored safely.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-table-wrapper" style="overflow-x: auto;">
			<table class="omnify-schema-table">
				<thead>
					<tr>
						<th style="width: 190px;"><?php esc_html_e('Entity Type', 'omnifywp-ecommerce'); ?></th>
						<th style="width: 220px;"><?php esc_html_e('Required Header(s)', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Accepted Header Schema', 'omnifywp-ecommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($omnify_schemas as $schema_key => $schema) : 
						$headers_list = array_map('trim', explode(',', $schema['headers']));
					?>
						<tr>
							<td>
								<div class="omnify-schema-entity">
									<div class="omnify-schema-entity-icon omnify-export-theme--<?php echo esc_attr($schema['theme']); ?>">
										<?php echo $schema['icon_svg']; ?>
									</div>
									<div class="omnify-schema-entity-meta">
										<span class="omnify-schema-entity-name"><?php echo esc_html($schema['label']); ?></span>
										<code class="omnify-schema-entity-slug"><?php echo esc_html($schema_key); ?></code>
									</div>
								</div>
							</td>
							<td>
								<?php foreach ($schema['required'] as $req_field) : ?>
									<span class="omnify-required-pill">
										<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20M4.93 4.93l14.14 14.14M4.93 19.07l14.14-14.14"/></svg>
										<?php echo esc_html($req_field); ?>
									</span>
								<?php endforeach; ?>
							</td>
							<td>
								<div class="omnify-headers-wrap">
									<div class="omnify-headers-pills">
										<?php foreach ($headers_list as $h) : ?>
											<span class="omnify-header-pill"><?php echo esc_html($h); ?></span>
										<?php endforeach; ?>
									</div>
									<div class="omnify-header-actions">
										<button type="button" class="omnify-header-action-btn omnify-copy-headers-btn" data-headers="<?php echo esc_attr($schema['headers']); ?>" title="<?php esc_attr_e('Copy comma-separated headers to clipboard', 'omnifywp-ecommerce'); ?>">
											<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
											<span><?php esc_html_e('Copy Headers', 'omnifywp-ecommerce'); ?></span>
										</button>
										<button type="button" class="omnify-header-action-btn omnify-download-template-btn" data-entity="<?php echo esc_attr($schema_key); ?>" data-headers="<?php echo esc_attr($schema['headers']); ?>" title="<?php esc_attr_e('Download starter CSV template', 'omnifywp-ecommerce'); ?>">
											<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
											<span><?php esc_html_e('Starter CSV', 'omnifywp-ecommerce'); ?></span>
										</button>
									</div>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	// 1. Dropzone file upload preview
	const fileInput = document.getElementById('omnify-import-file');
	const dropzone = document.getElementById('omnify-io-dropzone');
	const promptEl = document.getElementById('omnify-dropzone-prompt');
	const selectedEl = document.getElementById('omnify-dropzone-file-selected');
	const filenameEl = document.getElementById('omnify-chosen-filename');
	const filesizeEl = document.getElementById('omnify-chosen-filesize');

	if (fileInput && dropzone && promptEl && selectedEl) {
		function formatSize(bytes) {
			if (bytes === 0) return '0 Bytes';
			const k = 1024;
			const sizes = ['Bytes', 'KB', 'MB', 'GB'];
			const i = Math.floor(Math.log(bytes) / Math.log(k));
			return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
		}

		fileInput.addEventListener('change', function () {
			if (fileInput.files && fileInput.files[0]) {
				const f = fileInput.files[0];
				if (filenameEl) filenameEl.textContent = f.name;
				if (filesizeEl) filesizeEl.textContent = formatSize(f.size);
				promptEl.style.display = 'none';
				selectedEl.classList.add('has-file');
			} else {
				promptEl.style.removeProperty('display');
				selectedEl.classList.remove('has-file');
			}
		});

		['dragenter', 'dragover'].forEach(eventName => {
			dropzone.addEventListener(eventName, function (e) {
				e.preventDefault();
				dropzone.classList.add('is-dragover');
			});
		});

		['dragleave', 'drop'].forEach(eventName => {
			dropzone.addEventListener(eventName, function (e) {
				e.preventDefault();
				dropzone.classList.remove('is-dragover');
			});
		});
	}

	// 2. Copy Headers button
	document.querySelectorAll('.omnify-copy-headers-btn').forEach(btn => {
		btn.addEventListener('click', function () {
			const headers = btn.getAttribute('data-headers') || '';
			if (!headers) return;

			function feedback() {
				const span = btn.querySelector('span');
				if (span) {
					const orig = span.textContent;
					btn.classList.add('is-copied');
					span.textContent = 'Copied!';
					setTimeout(() => {
						span.textContent = orig;
						btn.classList.remove('is-copied');
					}, 1300);
				}
			}

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(headers).then(feedback).catch(() => fallbackCopy(headers, feedback));
			} else {
				fallbackCopy(headers, feedback);
			}
		});
	});

	function fallbackCopy(text, cb) {
		const ta = document.createElement('textarea');
		ta.value = text;
		ta.style.position = 'fixed';
		ta.style.left = '-9999px';
		document.body.appendChild(ta);
		ta.select();
		try { document.execCommand('copy'); if (cb) cb(); } catch (e) {}
		document.body.removeChild(ta);
	}

	// 3. Download Starter CSV Template
	document.querySelectorAll('.omnify-download-template-btn').forEach(btn => {
		btn.addEventListener('click', function () {
			const entity = btn.getAttribute('data-entity') || 'template';
			const headers = btn.getAttribute('data-headers') || '';
			if (!headers) return;

			const csvContent = '\uFEFF' + headers + '\r\n';
			const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
			const link = document.createElement('a');
			link.href = URL.createObjectURL(blob);
			link.download = `omnify_${entity}_template.csv`;
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		});
	});
});
</script>
</div>
