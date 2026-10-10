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
		'icon' => 'dashicons-products',
		'title' => __('Products', 'omnifywp-ecommerce'),
		'description' => __('Catalog data including pricing, product type, stock, shipping class, categories, tags, and descriptions.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_products',
		'nonce' => 'omnify_export_products',
		'button' => __('Export products (CSV)', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-media-code',
		'title' => __('Products (XML)', 'omnifywp-ecommerce'),
		'description' => __('Structured XML with nested variations, categories, files support for richer import/export.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_products_xml',
		'nonce' => 'omnify_export_products_xml',
		'button' => __('Export products (XML)', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-admin-users',
		'title' => __('Customers', 'omnifywp-ecommerce'),
		'description' => __('Customer profiles, emails, status, company, country, and tax exemption flag.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_customers',
		'nonce' => 'omnify_export_customers',
		'button' => __('Export customers', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-money-alt',
		'title' => __('Orders', 'omnifywp-ecommerce'),
		'description' => __('Order totals, status, customer email, tax, shipping, payment method, coupons, and dates.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_orders',
		'nonce' => 'omnify_export_orders',
		'button' => __('Export orders', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-tag',
		'title' => __('Coupons', 'omnifywp-ecommerce'),
		'description' => __('Discount rules, usage limits, free shipping flags, product/category restrictions, and expiry dates.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_coupons',
		'nonce' => 'omnify_export_coupons',
		'button' => __('Export coupons', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-calculator',
		'title' => __('Tax Rules', 'omnifywp-ecommerce'),
		'description' => __('Country/state tax rules with rates, reporting codes, priorities, and enabled state.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_tax_rules',
		'nonce' => 'omnify_export_tax_rules',
		'button' => __('Export tax rules', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-location-alt',
		'title' => __('Delivery Zones', 'omnifywp-ecommerce'),
		'description' => __('Shipping zones, country/state scopes, method type, costs, free shipping threshold, and class costs.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_delivery_zones',
		'nonce' => 'omnify_export_delivery_zones',
		'button' => __('Export delivery zones', 'omnifywp-ecommerce'),
	],
	[
		'icon' => 'dashicons-download',
		'title' => __('Download Logs', 'omnifywp-ecommerce'),
		'description' => __('Digital file download audit log with product, file, customer, IP, user agent, and timestamp.', 'omnifywp-ecommerce'),
		'action' => 'omnify_export_downloads',
		'nonce' => 'omnify_export_downloads',
		'button' => __('Export download logs', 'omnifywp-ecommerce'),
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

$omnify_result_type = is_array($omnify_import_result ?? null) ? (string) ($omnify_import_result['type'] ?? '') : '';
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'export'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Import/Export', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Move store data in and out with validated CSV files for products, customers, orders, coupons, tax rules, and delivery zones.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<?php $omnify_active_tab = 'export'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>

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

	<?php if (is_array($omnify_import_result ?? null)) : ?>
		<div class="omnify-card" style="border-left: 4px solid <?php echo empty($omnify_import_result['errors']) ? 'var(--omnify-green)' : 'var(--omnify-warning)'; ?>; margin-bottom: 18px;">
			<h2 style="margin: 0 0 8px; font-size: 16px;">
				<?php
				printf(
					/* translators: %s: import type */
					esc_html__('Import finished: %s', 'omnifywp-ecommerce'),
					esc_html($omnify_import_types[$omnify_result_type] ?? ucfirst(str_replace('_', ' ', $omnify_result_type)))
				);
				?>
			</h2>
			<p style="margin: 0 0 12px; color: var(--shopify-gray-700);">
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
				<ul style="margin: 0; padding-left: 18px; color: #8a4b00;">
					<?php foreach (array_slice($omnify_import_result['errors'], 0, 12) as $omnify_error) : ?>
						<li><?php echo esc_html((string) $omnify_error); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div style="display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 20px; align-items: start;">
		<div class="omnify-card">
			<div class="omnify-card__header">
				<div>
					<h2><?php esc_html_e('Export', 'omnifywp-ecommerce'); ?></h2>
					<p style="margin: 4px 0 0; color: var(--shopify-gray-600);"><?php esc_html_e('Download current store data. Exported files use importer-friendly headers.', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-dashboard-stats omnify-export-grid" style="gap: 20px; margin-top: 20px;">
				<?php foreach ($omnify_exports as $omnify_export) : ?>
					<div class="omnify-card omnify-card--accent omnify-export-card" style="display: flex; flex-direction: column; margin: 0; min-height: 220px; padding: 24px; border-radius: 16px; border: 1px solid #eaeef4; background: #ffffff; transition: all 0.25s ease;">
						<div class="omnify-export-icon-wrap" style="width: 48px; height: 48px; border-radius: 50%; background: rgba(13, 148, 136, 0.08); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--omnify-page-accent, #0d9488);">
							<span class="dashicons <?php echo esc_attr($omnify_export['icon']); ?>" style="font-size: 22px; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;"></span>
						</div>
						<h3 style="font-size: 15px; font-weight: 500; color: #0f172a; margin: 0 0 8px;"><?php echo esc_html($omnify_export['title']); ?></h3>
						<p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 20px; flex: 1;"><?php echo esc_html($omnify_export['description']); ?></p>
						<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=' . $omnify_export['action']), $omnify_export['nonce'])); ?>" class="omnify-button omnify-button--secondary" style="width: 100%; justify-content: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
							<?php echo esc_html($omnify_export['button']); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="omnify-card" style="position: sticky; top: 44px;">
			<div class="omnify-card__header">
				<div>
					<h2><?php esc_html_e('Import CSV', 'omnifywp-ecommerce'); ?></h2>
					<p style="margin: 4px 0 0; color: var(--shopify-gray-600);"><?php esc_html_e('Upload a CSV and the importer will validate required fields before saving.', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display: grid; gap: 14px; margin-top: 14px;">
				<input type="hidden" name="action" value="omnify_import_csv">
				<?php wp_nonce_field('omnify_import_csv', 'omnify_import_nonce'); ?>

				<div class="omnify-form-group">
					<label for="omnify-import-type"><?php esc_html_e('Data Type', 'omnifywp-ecommerce'); ?></label>
					<select id="omnify-import-type" name="import_type" required>
						<?php foreach ($omnify_import_types as $omnify_value => $omnify_label) : ?>
							<option value="<?php echo esc_attr($omnify_value); ?>"><?php echo esc_html($omnify_label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="omnify-form-group">
					<label for="omnify-import-mode"><?php esc_html_e('Import Mode', 'omnifywp-ecommerce'); ?></label>
					<select id="omnify-import-mode" name="import_mode" required>
						<option value="upsert"><?php esc_html_e('Update existing, create missing', 'omnifywp-ecommerce'); ?></option>
						<option value="create"><?php esc_html_e('Create only, skip existing', 'omnifywp-ecommerce'); ?></option>
						<option value="replace"><?php esc_html_e('Replace tax/delivery settings rows', 'omnifywp-ecommerce'); ?></option>
					</select>
				</div>

				<div class="omnify-form-group">
					<label for="omnify-import-file"><?php esc_html_e('CSV File', 'omnifywp-ecommerce'); ?></label>
					<input id="omnify-import-file" type="file" name="import_file" accept=".csv,.xml,text/csv,application/xml" required>
					<p class="description"><?php esc_html_e('Use UTF-8 CSV. The first row must contain column headers.', 'omnifywp-ecommerce'); ?></p>
				</div>

				<button type="submit" class="omnify-button omnify-button--primary" style="justify-content: center;">
					<?php esc_html_e('Import CSV', 'omnifywp-ecommerce'); ?>
				</button>
			</form>
		</div>
	</div>

	<div class="omnify-card" style="margin-top: 20px;">
		<div class="omnify-card__header">
			<div>
				<h2><?php esc_html_e('CSV Header Reference', 'omnifywp-ecommerce'); ?></h2>
				<p style="margin: 4px 0 0; color: var(--shopify-gray-600);"><?php esc_html_e('Required fields are validated during import. Extra columns are ignored safely.', 'omnifywp-ecommerce'); ?></p>
			</div>
		</div>
		<div class="omnify-table-wrapper" style="margin-top: 12px;">
			<table class="omnify-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Required Fields', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Accepted Headers', 'omnifywp-ecommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></td>
						<td><code>name</code></td>
						<td><code><?php echo esc_html($omnify_headers['products']); ?></code><br><small>(+ bundled_ids, upsell_ids, cross_sell_ids, max_purchase_qty supported)</small></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></td>
						<td><code>email</code></td>
						<td><code><?php echo esc_html($omnify_headers['customers']); ?></code></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></td>
						<td><code>order_number, customer_email</code></td>
						<td><code><?php echo esc_html($omnify_headers['orders']); ?></code></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Coupons', 'omnifywp-ecommerce'); ?></td>
						<td><code>code, discount_type, discount_value</code></td>
						<td><code><?php echo esc_html($omnify_headers['coupons']); ?></code></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Tax Rules', 'omnifywp-ecommerce'); ?></td>
						<td><code>label, country, state, rate</code></td>
						<td><code><?php echo esc_html($omnify_headers['tax_rules']); ?></code></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Delivery Zones', 'omnifywp-ecommerce'); ?></td>
						<td><code>name, countries, states, method_name</code></td>
						<td><code><?php echo esc_html($omnify_headers['delivery_zones']); ?></code></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
