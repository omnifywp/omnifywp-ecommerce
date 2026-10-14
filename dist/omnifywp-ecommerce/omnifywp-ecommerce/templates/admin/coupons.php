<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_c = $omnify_template_vars['omnify_c'] ?? null;
$omnify_category_name = $omnify_template_vars['omnify_category_name'] ?? null;
$omnify_category_option = $omnify_template_vars['omnify_category_option'] ?? null;
$omnify_category_options = $omnify_template_vars['omnify_category_options'] ?? null;
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_coupon = $omnify_template_vars['omnify_coupon'] ?? null;
$omnify_coupon_csv_values = $omnify_template_vars['omnify_coupon_csv_values'] ?? null;
$omnify_coupons = $omnify_template_vars['omnify_coupons'] ?? null;
$omnify_cp_chips = $omnify_template_vars['omnify_cp_chips'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_date_label = $omnify_template_vars['omnify_date_label'] ?? null;
$omnify_global_categories = $omnify_template_vars['omnify_global_categories'] ?? null;
$omnify_global_category = $omnify_template_vars['omnify_global_category'] ?? null;
$omnify_is_trashed = $omnify_template_vars['omnify_is_trashed'] ?? null;
$omnify_item = $omnify_template_vars['omnify_item'] ?? null;
$omnify_known_product_ids = $omnify_template_vars['omnify_known_product_ids'] ?? null;
$omnify_limit = $omnify_template_vars['omnify_limit'] ?? null;
$omnify_missing_product_id = $omnify_template_vars['omnify_missing_product_id'] ?? null;
$omnify_product_id = $omnify_template_vars['omnify_product_id'] ?? null;
$omnify_product_name = $omnify_template_vars['omnify_product_name'] ?? null;
$omnify_product_option = $omnify_template_vars['omnify_product_option'] ?? null;
$omnify_products = $omnify_template_vars['omnify_products'] ?? null;
$omnify_selected_excluded_categories = $omnify_template_vars['omnify_selected_excluded_categories'] ?? null;
$omnify_selected_excluded_products = $omnify_template_vars['omnify_selected_excluded_products'] ?? null;
$omnify_selected_included_categories = $omnify_template_vars['omnify_selected_included_categories'] ?? null;
$omnify_selected_included_products = $omnify_template_vars['omnify_selected_included_products'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_type = $omnify_template_vars['omnify_type'] ?? null;
$omnify_valStr = $omnify_template_vars['omnify_valStr'] ?? null;
$omnify_value = $omnify_template_vars['omnify_value'] ?? null;


/**
 * Admin Coupons Template
 *
 * @package Omnify
 */
// Helper to format prices
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
$omnify_products = isset($omnify_products) && is_array($omnify_products) ? $omnify_products : [];
$omnify_global_categories = isset($omnify_global_categories) && is_array($omnify_global_categories) ? $omnify_global_categories : [];

// Make $coupon always an array to prevent "Trying to access array offset on value of type null"
$omnify_coupon = is_array($omnify_coupon ?? null) ? $omnify_coupon : [];

if (! isset($omnify_coupon_csv_values) || ! is_callable($omnify_coupon_csv_values)) {
	$omnify_coupon_csv_values = 'omnify_coupon_csv_values';
}

$omnify_selected_included_products   = array_map('strval', omnify_coupon_csv_values($omnify_coupon['included_product_ids'] ?? ''));
$omnify_selected_excluded_products   = array_map('strval', omnify_coupon_csv_values($omnify_coupon['excluded_product_ids'] ?? ''));
$omnify_selected_included_categories = omnify_coupon_csv_values($omnify_coupon['included_categories'] ?? '');
$omnify_selected_excluded_categories = omnify_coupon_csv_values($omnify_coupon['excluded_categories'] ?? '');
$omnify_category_options             = [];
$omnify_known_product_ids            = [];
foreach ($omnify_products as $omnify_product_option) {
	$omnify_product_id = (string) absint($omnify_product_option['id'] ?? 0);
	if ('0' !== $omnify_product_id) {
		$omnify_known_product_ids[] = $omnify_product_id;
	}
}
foreach ($omnify_global_categories as $omnify_global_category) {
	if (is_array($omnify_global_category) && ! empty($omnify_global_category['name'])) {
		$omnify_category_options[] = (string) $omnify_global_category['name'];
	}
}
foreach ($omnify_products as $omnify_product_option) {
	if (! empty($omnify_product_option['categories']) && is_array($omnify_product_option['categories'])) {
		foreach ($omnify_product_option['categories'] as $omnify_category_name) {
			$omnify_category_options[] = (string) $omnify_category_name;
		}
	}
}
$omnify_category_options = array_merge($omnify_category_options, $omnify_selected_included_categories, $omnify_selected_excluded_categories);
$omnify_category_options = array_values(array_unique(array_filter(array_map('trim', $omnify_category_options))));
sort($omnify_category_options, SORT_NATURAL | SORT_FLAG_CASE);
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'coupons'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Coupons', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Manage storefront discount codes, percentage cuts, and usage limits.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<?php if ($omnify_coupon) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-coupons')); ?>" class="omnify-button omnify-button--secondary">
					<?php esc_html_e('Back to Coupons', 'omnifywp-ecommerce'); ?>
				</a>
			<?php else : ?>
				<button type="button" id="omnify-create-coupon-btn" class="omnify-button omnify-button--primary">
					<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
					<?php esc_html_e('Create New Coupon', 'omnifywp-ecommerce'); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div id="omnify-coupons-list" style="display: <?php echo $omnify_coupon ? 'none' : 'block'; ?>;">
	<?php
	$omnify_filter_search    = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
	$omnify_filter_type      = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
	$omnify_filter_status    = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
	$omnify_filter_date_from = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
	$omnify_filter_date_to   = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
	$omnify_filter_sort      = isset($_GET['sort']) ? sanitize_key(wp_unslash($_GET['sort'])) : '';
	?>
	<!-- Coupons Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-coupons" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_coupon"><?php esc_html_e('Search', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_coupon" name="search" placeholder="<?php esc_attr_e('Search code...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="coupon_type"><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></label>
				<select id="coupon_type" name="type">
					<option value=""><?php esc_html_e('All Types', 'omnifywp-ecommerce'); ?></option>
					<option value="percent" <?php selected($omnify_filter_type, 'percent'); ?>><?php esc_html_e('Percent', 'omnifywp-ecommerce'); ?></option>
					<option value="fixed" <?php selected($omnify_filter_type, 'fixed'); ?>><?php esc_html_e('Fixed', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Statuses', 'omnifywp-ecommerce'); ?></option>
					<option value="active" <?php selected($omnify_filter_status, 'active'); ?>><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></option>
					<option value="trash" <?php selected($omnify_filter_status, 'trash'); ?>><?php esc_html_e('Trash', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="date_from"><?php esc_html_e('Created From', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_filter_date_from); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="date_to"><?php esc_html_e('Created To', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($omnify_filter_date_to); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="sort_by"><?php esc_html_e('Sort By', 'omnifywp-ecommerce'); ?></label>
				<select id="sort_by" name="sort">
					<option value=""><?php esc_html_e('Newest', 'omnifywp-ecommerce'); ?></option>
					<option value="code_asc" <?php selected($omnify_filter_sort, 'code_asc'); ?>><?php esc_html_e('Code A-Z', 'omnifywp-ecommerce'); ?></option>
					<option value="usage_desc" <?php selected($omnify_filter_sort, 'usage_desc'); ?>><?php esc_html_e('Most Used', 'omnifywp-ecommerce'); ?></option>
					<option value="expiry_asc" <?php selected($omnify_filter_sort, 'expiry_asc'); ?>><?php esc_html_e('Expiring Soon', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-coupons')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active filter chips -->
	<?php
	$omnify_cp_chips = [];
	if ('' !== $omnify_filter_search) {
		$omnify_cp_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . $omnify_filter_search,
			'url'   => remove_query_arg('search'),
		];
	}
	if ('' !== $omnify_filter_type) {
		$omnify_cp_chips['type'] = [
			'label' => __('Type', 'omnifywp-ecommerce') . ': ' . $omnify_filter_type,
			'url'   => remove_query_arg('type'),
		];
	}
	if ('' !== $omnify_filter_status) {
		$omnify_cp_chips['status'] = [
			'label' => __('Status', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_filter_status),
			'url'   => remove_query_arg('status'),
		];
	}
	if ('' !== $omnify_filter_date_from || '' !== $omnify_filter_date_to) {
		$omnify_date_label = trim($omnify_filter_date_from . ' - ' . $omnify_filter_date_to);
		$omnify_cp_chips['date'] = [
			'label' => __('Created', 'omnifywp-ecommerce') . ': ' . $omnify_date_label,
			'url'   => remove_query_arg(['date_from', 'date_to']),
		];
	}
	?>
	<?php if(!empty($omnify_cp_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500); margin-right:4px; font-weight:600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach($omnify_cp_chips as $omnify_chip): ?>
		<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
			<?php echo esc_html($omnify_chip['label']); ?>
			<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">&times;</a>
		</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="omnify-card omnify-coupons-list-card">
			<form id="omnify-bulk-coupons-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('omnify_bulk_coupons', 'omnify_bulk_nonce'); ?>
				<input type="hidden" name="action" value="omnify_bulk_coupons" />

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
						<a href="#" class="omnify-button omnify-button--secondary omnify-button--sm">Export</a>
					</div>
				</div>

				<?php if (empty($omnify_coupons)) : ?>
					<div class="omnify-empty-state">
						<div class="omnify-empty-state__icon"><span class="dashicons dashicons-tag" style="font-size: 48px; width: 48px; height: 48px; color: var(--omnify-gray-400);"></span></div>
						<h3><?php esc_html_e('No coupon codes created yet', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Click "Create New Coupon" to add your first discount code.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper">
						<table class="omnify-table">
							<thead>
									<tr>
										<th style="width:28px;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
										<th><?php esc_html_e('Code', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Discount Value', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Usages', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Expiry', 'omnifywp-ecommerce'); ?></th>
									<th style="width: 72px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_coupons as $omnify_c) : ?>
									<tr>
										<td><input type="checkbox" class="omnify-checkbox omnify-bulk-item" name="coupon_ids[]" value="<?php echo esc_attr($omnify_c['id']); ?>" /></td>
										<td>
											<strong class="omnify-code-pill">
												<?php echo esc_html($omnify_c['code']); ?>
											</strong>
										</td>
										<td>
											<span class="omnify-status <?php echo ! empty($omnify_c['is_active']) && ! $omnify_c['is_expired'] ? 'omnify-status--active' : 'omnify-status--revoked'; ?>">
												<?php echo ! empty($omnify_c['is_active']) && ! $omnify_c['is_expired'] ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Disabled', 'omnifywp-ecommerce'); ?>
											</span>
										</td>
											<td>
											<span class="omnify-pill">
												<?php echo esc_html($omnify_c['discount_type']); ?>
											</span>
										</td>
										<td>
											<strong>
												<?php 
												if ('percent' === $omnify_c['discount_type']) {
													echo esc_html(number_format($omnify_c['discount_value'], 1) . '%');
												} else {
													echo esc_html($omnify_symbol . number_format($omnify_c['discount_value'], 2));
												}
												?>
											</strong>
										</td>
										<td>
											<span style="font-size: 13px; font-weight: 500;">
												<?php 
												$omnify_limit = null === $omnify_c['usage_limit'] ? '∞' : (string) $omnify_c['usage_limit'];
												echo esc_html($omnify_c['usage_count'] . ' / ' . $omnify_limit); 
												?>
											</span>
										</td>
										<td>
											<?php if ($omnify_c['is_expired']) : ?>
												<span class="omnify-status omnify-status--revoked" style="font-size: 11px; padding: 2px 6px;">
													<?php esc_html_e('Expired', 'omnifywp-ecommerce'); ?>
												</span>
											<?php elseif ($omnify_c['expires_at']) : ?>
												<span style="font-size: 12px; color: var(--omnify-gray-800);">
													<?php echo esc_html(date_i18n(get_option('date_format'), strtotime($omnify_c['expires_at']))); ?>
												</span>
											<?php else : ?>
												<span style="font-size: 12px; color: var(--omnify-gray-600); font-style: italic;">
													<?php esc_html_e('No Expiry', 'omnifywp-ecommerce'); ?>
												</span>
											<?php endif; ?>
										</td>
										<td style="text-align: right; width: 72px;">
											<div class="omnify-row-actions">
												<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-coupons&action=edit&id=' . $omnify_c['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Edit', 'omnifywp-ecommerce'); ?>" aria-label="<?php esc_attr_e('Edit coupon', 'omnifywp-ecommerce'); ?>">
													<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
												</a>
												<?php $omnify_is_trashed = ! empty($omnify_c['deleted_at']); ?>
												<?php if ($omnify_is_trashed): ?>
													<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_coupon&id=' . $omnify_c['id']), 'omnify_restore_coupon_' . $omnify_c['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Restore', 'omnifywp-ecommerce'); ?>">
														<span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
													</a>
													<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_coupon&id=' . $omnify_c['id']), 'omnify_delete_coupon_' . $omnify_c['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Delete permanently', 'omnifywp-ecommerce'); ?>" onclick="return confirm('Permanently delete?');">
														<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
													</a>
												<?php else: ?>
													<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_coupon&id=' . $omnify_c['id']), 'omnify_trash_coupon_' . $omnify_c['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Move to Trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('Move to trash?');">
														<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
													</a>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
						</table>
					</div>
				</form>
				<?php endif; ?>
			</div>
		</div>

		<!-- Coupon Form (toggled) -->
		<div id="omnify-coupon-form-container" style="display: <?php echo $omnify_coupon ? 'block' : 'none'; ?>;">
			<div class="omnify-card omnify-coupon-form-card">
				<div class="form-header">
					<h2>
						<?php if ($omnify_coupon): ?>
							<span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; margin-right: 6px; vertical-align: middle;"></span>
							<?php esc_html_e('Edit Coupon', 'omnifywp-ecommerce'); ?>
						<?php else: ?>
							<span class="dashicons dashicons-plus" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; margin-right: 6px; vertical-align: middle;"></span>
							<?php esc_html_e('Create New Coupon', 'omnifywp-ecommerce'); ?>
						<?php endif; ?>
					</h2>
					<?php if (!empty($omnify_coupon['id'])) : ?>
						<span class="id-badge">ID: <?php echo esc_html($omnify_coupon['id']); ?></span>
					<?php endif; ?>
				</div>

				<!-- Quick Preview -->
				<div class="omnify-coupon-preview">
					<strong>Preview:</strong> 
					Customers using <strong class="code"><?php echo esc_html($omnify_coupon['code'] ?? 'NEWCODE'); ?></strong> 
					will get <strong><?php 
						$omnify_type  = $omnify_coupon['discount_type'] ?? 'fixed';
						$omnify_value = $omnify_coupon['discount_value'] ?? 0;
						$omnify_valStr = ($omnify_type === 'percent') ? number_format($omnify_value, 0) . '%' : $omnify_symbol . number_format($omnify_value, 2);
						echo esc_html($omnify_valStr); 
					?> off</strong>
					<?php if (!empty($omnify_coupon['min_order_amount'] ?? null)) : ?> on orders over <?php echo esc_html($omnify_symbol . $omnify_coupon['min_order_amount']); ?><?php endif; ?>.
				</div>

				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_save_coupon', 'omnify_coupon_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_coupon" />
					<input type="hidden" name="id" value="<?php echo esc_attr((string) ($omnify_coupon['id'] ?? 0)); ?>" />

					<!-- Status -->
					<div class="omnify-form-group" style="margin-bottom: 12px;">
						<label class="omnify-toggle">
							<input  type="checkbox" name="is_active" value="1" <?php checked(! empty($omnify_coupon['is_active']) || empty($omnify_coupon['id'])); ?> />
							<span class="omnify-toggle__label"><strong><?php esc_html_e('Coupon is active', 'omnifywp-ecommerce'); ?></strong></span>
						</label>
					</div>

					<!-- Basic Details -->
					<div class="omnify-form-section">
						<h3><?php esc_html_e('Basic Details', 'omnifywp-ecommerce'); ?></h3>
					</div>

					<div class="omnify-form-group">
						<label for="code"><?php esc_html_e('Coupon Code', 'omnifywp-ecommerce'); ?> *</label>
						<input type="text" id="code" name="code" value="<?php echo esc_attr($omnify_coupon['code'] ?? ''); ?>" required placeholder="SUMMER20" style="text-transform: uppercase; font-family: ui-monospace, monospace; font-size: 15px; letter-spacing: 1px;" />
						<span class="help-text"><?php esc_html_e('Unique uppercase code (letters + numbers) that customers enter at checkout.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="discount_type"><?php esc_html_e('Discount Type', 'omnifywp-ecommerce'); ?></label>
							<select id="discount_type" name="discount_type" style="font-weight: 600;">
								<option value="fixed" <?php selected($omnify_coupon['discount_type'] ?? 'fixed', 'fixed'); ?>><?php esc_html_e('Fixed Amount Off', 'omnifywp-ecommerce'); ?></option>
								<option value="percent" <?php selected($omnify_coupon['discount_type'] ?? 'fixed', 'percent'); ?>><?php esc_html_e('Percentage Off', 'omnifywp-ecommerce'); ?></option>
							</select>
						</div>
						<div class="omnify-form-group">
							<label for="discount_value"><?php esc_html_e('Discount Value', 'omnifywp-ecommerce'); ?> *</label>
							<input type="number" id="discount_value" name="discount_value" step="0.01" min="0" value="<?php echo esc_attr((string) ($omnify_coupon['discount_value'] ?? 0.0)); ?>" required placeholder="10" />
							<span class="help-text"><?php esc_html_e('10 = $10 off or 10% off', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>

					<!-- Usage & Limits -->
					<div class="omnify-form-section">
						<h3><?php esc_html_e('Usage Limits & Validity', 'omnifywp-ecommerce'); ?></h3>
					</div>

					<div class="omnify-form-group">
						<label for="usage_limit"><?php esc_html_e('Global Usage Limit', 'omnifywp-ecommerce'); ?></label>
						<input type="number" id="usage_limit" name="usage_limit" min="1" value="<?php echo esc_attr((string) ($omnify_coupon['usage_limit'] ?? '')); ?>" placeholder="Unlimited" />
						<span class="help-text"><?php esc_html_e('Maximum number of times this coupon can be used across all customers.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="usage_limit_per_customer"><?php esc_html_e('Per Customer Limit', 'omnifywp-ecommerce'); ?></label>
							<input type="number" id="usage_limit_per_customer" name="usage_limit_per_customer" min="1" value="<?php echo esc_attr((string) ($omnify_coupon['usage_limit_per_customer'] ?? '')); ?>" placeholder="Unlimited" />
							<span class="help-text"><?php esc_html_e('How many times one customer can use it.', 'omnifywp-ecommerce'); ?></span>
						</div>
						<div class="omnify-form-group">
							<label for="min_order_amount"><?php esc_html_e('Minimum Order Amount', 'omnifywp-ecommerce'); ?></label>
							<input type="number" id="min_order_amount" name="min_order_amount" step="0.01" min="0" value="<?php echo esc_attr((string) ($omnify_coupon['min_order_amount'] ?? '')); ?>" placeholder="0.00" />
							<span class="help-text"><?php esc_html_e('Only applies if order total meets this.', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="max_discount_amount"><?php esc_html_e('Maximum Discount Cap', 'omnifywp-ecommerce'); ?></label>
							<input type="number" id="max_discount_amount" name="max_discount_amount" step="0.01" min="0" value="<?php echo esc_attr((string) ($omnify_coupon['max_discount_amount'] ?? '')); ?>" placeholder="No cap" />
							<span class="help-text"><?php esc_html_e('Caps the discount for % coupons.', 'omnifywp-ecommerce'); ?></span>
						</div>
						<div class="omnify-form-group">
							<label for="expires_at"><?php esc_html_e('Expires On', 'omnifywp-ecommerce'); ?></label>
							<?php // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?>
							<input type="date" id="expires_at" name="expires_at" value="<?php echo esc_attr( !empty($omnify_coupon['expires_at'] ?? null) ? date('Y-m-d', strtotime($omnify_coupon['expires_at'])) : '' ); ?>" />
							<span class="help-text"><?php esc_html_e('Leave blank for no expiry.', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>

					<!-- Special Rules -->
					<div class="omnify-form-section">
						<h3><?php esc_html_e('Special Rules', 'omnifywp-ecommerce'); ?></h3>
					</div>

					<div class="omnify-form-group">
						<label class="omnify-toggle">
							<input  type="checkbox" name="free_shipping" value="1" <?php checked(! empty($omnify_coupon['free_shipping'])); ?> />
							<span class="omnify-toggle__label"><?php esc_html_e('Gives free shipping', 'omnifywp-ecommerce'); ?></span>
						</label>
					</div>

					<div class="omnify-form-group">
						<label class="omnify-toggle">
							<input  type="checkbox" name="first_order_only" value="1" <?php checked(! empty($omnify_coupon['first_order_only'])); ?> />
							<span class="omnify-toggle__label"><?php esc_html_e('Valid for first order only', 'omnifywp-ecommerce'); ?></span>
						</label>
					</div>

					<!-- Restrictions -->
					<div class="omnify-form-section">
						<h3><?php esc_html_e('Product & Category Restrictions', 'omnifywp-ecommerce'); ?></h3>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="included_product_ids"><?php esc_html_e('Only for these Products', 'omnifywp-ecommerce'); ?></label>
							<select id="included_product_ids" name="included_product_ids[]" class="omnify-searchable-select" multiple data-placeholder="<?php esc_attr_e('All products allowed', 'omnifywp-ecommerce'); ?>">
								<?php foreach ($omnify_products as $omnify_product_option) : ?>
									<?php
									$omnify_product_id = (string) absint($omnify_product_option['id'] ?? 0);
									if ('0' === $omnify_product_id) continue;
									// translators: %s: placeholder value.
									$omnify_product_name = $omnify_product_option['name'] ?? sprintf(__('Product #%s', 'omnifywp-ecommerce'), $omnify_product_id);
									?>
									<option value="<?php echo esc_attr($omnify_product_id); ?>" <?php selected(in_array($omnify_product_id, $omnify_selected_included_products, true)); ?>>
										<?php echo esc_html('#' . $omnify_product_id . ' — ' . $omnify_product_name); ?>
									</option>
								<?php endforeach; ?>
								<?php foreach (array_diff($omnify_selected_included_products, $omnify_known_product_ids) as $omnify_missing_product_id) : ?>
									<option value="<?php echo esc_attr($omnify_missing_product_id); ?>" selected>
										<?php 
										// translators: %s: placeholder value.
										echo esc_html(sprintf(__('Product #%s', 'omnifywp-ecommerce'), $omnify_missing_product_id)); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<span class="help-text"><?php esc_html_e('If empty, applies to all products.', 'omnifywp-ecommerce'); ?></span>
						</div>
						<div class="omnify-form-group">
							<label for="excluded_product_ids"><?php esc_html_e('Never for these Products', 'omnifywp-ecommerce'); ?></label>
							<select id="excluded_product_ids" name="excluded_product_ids[]" class="omnify-searchable-select" multiple data-placeholder="<?php esc_attr_e('No exclusions', 'omnifywp-ecommerce'); ?>">
								<?php foreach ($omnify_products as $omnify_product_option) : ?>
									<?php
									$omnify_product_id = (string) absint($omnify_product_option['id'] ?? 0);
									if ('0' === $omnify_product_id) continue;
									// translators: %s: placeholder value.
									$omnify_product_name = $omnify_product_option['name'] ?? sprintf(__('Product #%s', 'omnifywp-ecommerce'), $omnify_product_id);
									?>
									<option value="<?php echo esc_attr($omnify_product_id); ?>" <?php selected(in_array($omnify_product_id, $omnify_selected_excluded_products, true)); ?>>
										<?php echo esc_html('#' . $omnify_product_id . ' — ' . $omnify_product_name); ?>
									</option>
								<?php endforeach; ?>
								<?php foreach (array_diff($omnify_selected_excluded_products, $omnify_known_product_ids) as $omnify_missing_product_id) : ?>
									<option value="<?php echo esc_attr($omnify_missing_product_id); ?>" selected>
										<?php 
										// translators: %s: placeholder value.
										echo esc_html(sprintf(__('Product #%s', 'omnifywp-ecommerce'), $omnify_missing_product_id)); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="included_categories"><?php esc_html_e('Only for these Categories', 'omnifywp-ecommerce'); ?></label>
							<select id="included_categories" name="included_categories[]" class="omnify-searchable-select" multiple data-placeholder="<?php esc_attr_e('All categories', 'omnifywp-ecommerce'); ?>">
								<?php foreach ($omnify_category_options as $omnify_category_option) : ?>
									<option value="<?php echo esc_attr($omnify_category_option); ?>" <?php selected(in_array($omnify_category_option, $omnify_selected_included_categories, true)); ?>>
										<?php echo esc_html($omnify_category_option); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="omnify-form-group">
							<label for="excluded_categories"><?php esc_html_e('Never for these Categories', 'omnifywp-ecommerce'); ?></label>
							<select id="excluded_categories" name="excluded_categories[]" class="omnify-searchable-select" multiple data-placeholder="<?php esc_attr_e('No category exclusions', 'omnifywp-ecommerce'); ?>">
								<?php foreach ($omnify_category_options as $omnify_category_option) : ?>
									<option value="<?php echo esc_attr($omnify_category_option); ?>" <?php selected(in_array($omnify_category_option, $omnify_selected_excluded_categories, true)); ?>>
										<?php echo esc_html($omnify_category_option); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<!-- Actions -->
					<div class="omnify-button-group" style="margin-top: 20px; gap: 10px;">
						<button type="submit" class="omnify-button omnify-button--primary" style="flex: 1; min-height: 42px;">
							<span class="dashicons dashicons-yes" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php 
							if ($omnify_coupon) {
								esc_html_e('Save Coupon Changes', 'omnifywp-ecommerce'); 
							} else {
								esc_html_e('Create Coupon', 'omnifywp-ecommerce');
							}
							?>
						</button>
						<?php if ($omnify_coupon) : ?>
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-coupons')); ?>" class="omnify-button omnify-button--secondary" style="min-height: 42px;">
							<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
						</a>
						<?php else : ?>
						<button type="button" id="omnify-cancel-create-coupon" class="omnify-button omnify-button--secondary" style="min-height: 42px;">
							<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
						</button>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

