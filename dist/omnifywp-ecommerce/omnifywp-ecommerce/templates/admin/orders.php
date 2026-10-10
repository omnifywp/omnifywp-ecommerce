<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_date_label = $omnify_template_vars['omnify_date_label'] ?? null;
$omnify_dec = $omnify_template_vars['omnify_dec'] ?? null;
$omnify_decs = $omnify_template_vars['omnify_decs'] ?? null;
$omnify_is_trashed = $omnify_template_vars['omnify_is_trashed'] ?? null;
$omnify_o_chips = $omnify_template_vars['omnify_o_chips'] ?? null;
$omnify_order = $omnify_template_vars['omnify_order'] ?? null;
$omnify_order_symbol = $omnify_template_vars['omnify_order_symbol'] ?? null;
$omnify_orders = $omnify_template_vars['omnify_orders'] ?? null;
$omnify_pm = $omnify_template_vars['omnify_pm'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_st = $omnify_template_vars['omnify_st'] ?? null;
$omnify_statuses = $omnify_template_vars['omnify_statuses'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_tho = $omnify_template_vars['omnify_tho'] ?? null;


/**
 * Admin Orders Template
 *
 * @package Omnify
 */
// Helper to format prices
$omnify_currency_symbol = function($omnify_currency) {
	switch ($omnify_currency) {
		case 'EUR': return '€';
		case 'GBP': return '£';
		case 'JPY': return '¥';
		case 'CAD': return 'C$';
		case 'AUD': return 'A$';
		default: return '$';
	}
};
$omnify_symbol = $omnify_currency_symbol($omnify_settings['default_currency'] ?? 'USD');

$omnify_filter_search         = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$omnify_filter_status         = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
$omnify_filter_date_from      = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
$omnify_filter_date_to        = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
$omnify_filter_payment_method = isset($_GET['payment_method']) ? sanitize_key(wp_unslash($_GET['payment_method'])) : '';
$omnify_filter_product_type   = isset($_GET['product_type']) ? sanitize_key(wp_unslash($_GET['product_type'])) : '';
$omnify_filter_sort           = isset($_GET['sort']) ? sanitize_key(wp_unslash($_GET['sort'])) : 'created_desc';
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'orders'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Track storefront purchases and digital transactions.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs">
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-subtab is-active"><?php esc_html_e('🛒 Orders List', 'omnifywp-ecommerce'); ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-subtab"><?php esc_html_e('⏳ Abandoned Carts', 'omnifywp-ecommerce'); ?></a>
	</div>

	<!-- Orders Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-orders" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_order"><?php esc_html_e('Search Order', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_order" name="search" placeholder="<?php esc_attr_e('Search order number, customer or email...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Order Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Statuses', 'omnifywp-ecommerce'); ?></option>
					<?php foreach (['pending','processing','completed','shipped','refunded','cancelled','trash'] as $omnify_st): ?>
						<option value="<?php echo esc_attr($omnify_st); ?>" <?php selected($omnify_filter_status, $omnify_st); ?>><?php echo esc_html(ucfirst(str_replace('_',' ',$omnify_st))); ?></option>
					<?php endforeach; ?>
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
				<label for="payment_method"><?php esc_html_e('Payment Method', 'omnifywp-ecommerce'); ?></label>
				<select id="payment_method" name="payment_method">
					<option value=""><?php esc_html_e('All Methods', 'omnifywp-ecommerce'); ?></option>
					<option value="stripe" <?php selected($omnify_filter_payment_method, 'stripe'); ?>>Stripe</option>
					<option value="paypal" <?php selected($omnify_filter_payment_method, 'paypal'); ?>>PayPal</option>
					<option value="razorpay" <?php selected($omnify_filter_payment_method, 'razorpay'); ?>>Razorpay</option>
					<?php foreach (['bank_transfer','cheque','cash_on_delivery','custom'] as $omnify_pm): ?>
						<option value="<?php echo esc_attr($omnify_pm); ?>" <?php selected($omnify_filter_payment_method, $omnify_pm); ?>><?php echo esc_html(ucwords(str_replace('_',' ',$omnify_pm))); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="product_type"><?php esc_html_e('Product Type', 'omnifywp-ecommerce'); ?></label>
				<select id="product_type" name="product_type">
					<option value=""><?php esc_html_e('All Types', 'omnifywp-ecommerce'); ?></option>
					<option value="digital" <?php selected($omnify_filter_product_type, 'digital'); ?>><?php esc_html_e('Digital', 'omnifywp-ecommerce'); ?></option>
					<option value="physical" <?php selected($omnify_filter_product_type, 'physical'); ?>><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></option>
					<option value="variable" <?php selected($omnify_filter_product_type, 'variable'); ?>><?php esc_html_e('Variable', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="sort_by"><?php esc_html_e('Sort By', 'omnifywp-ecommerce'); ?></label>
				<select id="sort_by" name="sort">
					<option value="created_desc" <?php selected($omnify_filter_sort, 'created_desc'); ?>><?php esc_html_e('Newest', 'omnifywp-ecommerce'); ?></option>
					<option value="created_asc" <?php selected($omnify_filter_sort, 'created_asc'); ?>><?php esc_html_e('Oldest', 'omnifywp-ecommerce'); ?></option>
					<option value="total_desc" <?php selected($omnify_filter_sort, 'total_desc'); ?>><?php echo esc_html(__('Total High to Low', 'omnifywp-ecommerce')); ?></option>
					<option value="total_asc" <?php selected($omnify_filter_sort, 'total_asc'); ?>><?php echo esc_html(__('Total Low to High', 'omnifywp-ecommerce')); ?></option>
				</select>
			</div>
			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active filter chips -->
	<?php
	$omnify_o_chips = [];
	if ('' !== $omnify_filter_search) {
		$omnify_o_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . $omnify_filter_search,
			'url'   => remove_query_arg('search'),
		];
	}
	if ('' !== $omnify_filter_payment_method) {
		$omnify_o_chips['payment_method'] = [
			'label' => __('Payment Method', 'omnifywp-ecommerce') . ': ' . $omnify_filter_payment_method,
			'url'   => remove_query_arg('payment_method'),
		];
	}
	if ('' !== $omnify_filter_product_type) {
		$omnify_o_chips['product_type'] = [
			'label' => __('Product Type', 'omnifywp-ecommerce') . ': ' . $omnify_filter_product_type,
			'url'   => remove_query_arg('product_type'),
		];
	}
	if ('' !== $omnify_filter_status) {
		$omnify_o_chips['status'] = [
			'label' => __('Status', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_filter_status),
			'url'   => remove_query_arg('status'),
		];
	}
	if ('' !== $omnify_filter_date_from || '' !== $omnify_filter_date_to) {
		$omnify_date_label = trim($omnify_filter_date_from . ' - ' . $omnify_filter_date_to);
		$omnify_o_chips['date'] = [
			'label' => __('Date', 'omnifywp-ecommerce') . ': ' . $omnify_date_label,
			'url'   => remove_query_arg(['date_from', 'date_to']),
		];
	}
	?>
	<?php if(!empty($omnify_o_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500);margin-right:4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach($omnify_o_chips as $omnify_chip): ?>
		<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
			<?php echo esc_html($omnify_chip['label']); ?>
			<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">×</a>
		</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<!-- Orders List -->
	<div class="omnify-card">

		<?php if (empty($omnify_orders)) : ?>
			<div class="omnify-empty-state">
				<div class="omnify-empty-state__icon"><span class="dashicons dashicons-cart" style="font-size: 48px; width: 48px; height: 48px; color: var(--omnify-gray-400);"></span></div>
				<h3><?php esc_html_e('No orders yet', 'omnifywp-ecommerce'); ?></h3>
				<p><?php esc_html_e('Orders placed via the storefront checkout will show up here.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-bulk-orders-form">
				<?php wp_nonce_field('omnify_bulk_orders', 'omnify_bulk_nonce'); ?>
				<input type="hidden" name="action" value="omnify_bulk_orders" />

				<div class="omnify-table-toolbar">
					<div class="left">
						<input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" style="margin-right:8px;" />
						<span class="selected-count">0 selected</span>
						<div class="omnify-bulk-actions-wrapper" style="display: none; align-items: center; gap: 8px; margin-left: 16px;">
							<select name="bulk_action">
								<option value="">Bulk actions</option>
								<option value="trash">Move to Trash</option>
								<option value="restore">Restore from Trash</option>
								<option value="mark_completed">Mark as Completed</option>
								<option value="mark_shipped">Mark as Shipped</option>
								<option value="mark_processing">Mark as Processing</option>
								<option value="mark_cancelled">Mark as Cancelled</option>
								<option value="fulfill">Mark Fulfilled</option>
								<option value="packing_slips">Generate Packing Slips (open printable)</option>
							</select>
							<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm">Apply</button>
						</div>
					</div>
					<div class="right">
						<a href="#" class="omnify-button omnify-button--secondary omnify-button--sm">Export</a>
					</div>
				</div>

				<div class="omnify-table-wrapper">
				<table class="omnify-table">
					<thead>
						<tr>
							<th style="width:30px;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
							<th style="width: 80px;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></th>
							<th><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
							<th><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
							<th><?php esc_html_e('Total Amount', 'omnifywp-ecommerce'); ?></th>
							<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
							<th><?php esc_html_e('Date & Time', 'omnifywp-ecommerce'); ?></th>
							<th style="width: 150px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($omnify_orders as $omnify_order) : ?>
							<tr>
								<td><input class="omnify-checkbox" type="checkbox" name="order_ids[]" value="<?php echo esc_attr($omnify_order['id']); ?>" /></td>
								<td>
									<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--shopify-green);">
										<?php echo esc_html($omnify_order['order_number'] ?: '#' . $omnify_order['id']); ?>
									</a>
									<?php if (! empty($omnify_order['fraud_status']) && $omnify_order['fraud_status'] === 'flagged') : ?>
										<span class="omnify-status" style="background-color: #ffe0b2; color: #e65100; font-size: 11px; padding: 2px 6px; margin-left: 6px; font-weight: 600;">⚠️ <?php esc_html_e('Flagged', 'omnifywp-ecommerce'); ?></span>
									<?php elseif (! empty($omnify_order['fraud_status']) && $omnify_order['fraud_status'] === 'high_risk') : ?>
										<span class="omnify-status" style="background-color: #ffcdd2; color: #c62828; font-size: 11px; padding: 2px 6px; margin-left: 6px; font-weight: 600;">🚨 <?php esc_html_e('High Risk', 'omnifywp-ecommerce'); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ($omnify_order['customer_id']) : ?>
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_order['customer_id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--shopify-dark);">
											<?php echo esc_html($omnify_order['customer_name'] ?: $omnify_order['customer_email']); ?>
										</a>
									<?php else : ?>
										<strong><?php echo esc_html($omnify_order['customer_name'] ?: $omnify_order['customer_email']); ?></strong>
									<?php endif; ?>
									<div style="font-size: 11px; color: var(--shopify-gray-600);"><?php echo esc_html($omnify_order['customer_email']); ?></div>
								</td>
								<td>
									<?php if ($omnify_order['product_id']) : ?>
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_order['product_id'])); ?>" style="text-decoration: none; color: var(--shopify-green); font-weight: 500;">
											<?php echo esc_html($omnify_order['product_name']); ?>
										</a>
									<?php else : ?>
										<span style="color: var(--shopify-gray-600);"><?php echo esc_html($omnify_order['product_name'] ?: __('Unknown Product', 'omnifywp-ecommerce')); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<strong>
										<?php 
										$omnify_order_symbol = $omnify_currency_symbol($omnify_order['currency']);
										$omnify_dec = (int)($omnify_settings['price_decimals'] ?? 2);
										$omnify_tho = $omnify_settings['thousand_separator'] ?? ',';
										$omnify_decs = $omnify_settings['decimal_separator'] ?? '.';
										echo esc_html($omnify_order_symbol . number_format($omnify_order['total'], $omnify_dec, $omnify_decs, $omnify_tho)); 
										?>
									</strong>
								</td>
								<td>
									<div class="omnify-quick-status-wrapper" style="display:inline-block;">
										<select name="status" class="omnify-quick-status omnify-quick-status--<?php echo esc_attr($omnify_order['status']); ?>" data-order-id="<?php echo esc_attr($omnify_order['id']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('omnify_quick_order_status_' . $omnify_order['id'])); ?>">
											<?php 
											$omnify_statuses = ['pending','processing','completed','packed','ready_to_deliver','shipped','out_for_delivery','delivered','refund_requested','refunded','cancelled'];
											foreach ($omnify_statuses as $omnify_st) {
												printf('<option value="%s" %s>%s</option>', esc_attr($omnify_st), selected($omnify_order['status'], $omnify_st, false), esc_html(ucfirst(str_replace('_',' ',$omnify_st))));
											}
											?>
										</select>
									</div>
									<?php if (! empty($omnify_order['fulfillment_status']) && $omnify_order['fulfillment_status'] !== 'none') : ?>
										<div style="margin-top: 4px;">
											<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_order['fulfillment_status'] === 'fulfilled' ? 'completed' : 'pending'); ?>" style="font-size: 10px; padding: 2px 6px;">
												<?php echo esc_html(ucfirst($omnify_order['fulfillment_status'])); ?>
											</span>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_order['created_at']))); ?>
								</td>
								<td style="text-align: right;">
									<div class="omnify-row-actions">
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--view" title="<?php esc_attr_e('View', 'omnifywp-ecommerce'); ?>">
											<span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
										</a>
										<?php $omnify_is_trashed = !empty($omnify_order['deleted_at']); ?>
										<?php if ($omnify_is_trashed): ?>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_order&order_id=' . $omnify_order['id']), 'omnify_restore_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn" title="Restore">
												<span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_order&order_id=' . $omnify_order['id']), 'omnify_delete_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Delete permanently" onclick="return confirm('Permanently delete?');">
												<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
										<?php else: ?>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_order&order_id=' . $omnify_order['id']), 'omnify_trash_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Move to trash" onclick="return confirm('Trash order?');">
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
</div>

