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
$omnify_paged = (int) ($omnify_template_vars['omnify_paged'] ?? 1);
$omnify_total_pages = (int) ($omnify_template_vars['omnify_total_pages'] ?? 1);
$omnify_total_orders = (int) ($omnify_template_vars['omnify_total_orders'] ?? (is_countable($omnify_orders) ? count($omnify_orders) : 0));
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
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-export')); ?>" class="omnify-button omnify-button--secondary">
				<span class="dashicons dashicons-database-export" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
				<?php esc_html_e('Export Orders', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php
	$omnify_message = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
	if (! empty($omnify_message)) :
		$omnify_msg_text = match ($omnify_message) {
			'bulk_mark_completed'  => __('Selected orders marked as completed.', 'omnifywp-ecommerce'),
			'bulk_mark_shipped'    => __('Selected orders marked as shipped.', 'omnifywp-ecommerce'),
			'bulk_mark_processing' => __('Selected orders marked as processing.', 'omnifywp-ecommerce'),
			'bulk_mark_cancelled'  => __('Selected orders marked as cancelled.', 'omnifywp-ecommerce'),
			'bulk_fulfill'         => __('Selected orders marked as fulfilled.', 'omnifywp-ecommerce'),
			'bulk_trash'           => __('Selected orders moved to trash.', 'omnifywp-ecommerce'),
			'bulk_restore'         => __('Selected orders restored from trash.', 'omnifywp-ecommerce'),
			'no_selection'         => __('No orders were selected.', 'omnifywp-ecommerce'),
			default                => '',
		};
		if (! empty($omnify_msg_text)) :
	?>
		<div class="omnify-alert omnify-alert--success" style="margin-bottom: 16px; padding: 12px 16px; border-radius: 6px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
			<span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span>
			<span><?php echo esc_html($omnify_msg_text); ?></span>
		</div>
	<?php endif; endif; ?>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs-track">
		<div class="omnify-subtabs">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-subtab is-active">
				<span class="dashicons dashicons-cart" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: text-bottom; margin-right: 5px;"></span>
				<?php esc_html_e('Orders List', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-subtab">
				<span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: text-bottom; margin-right: 5px;"></span>
				<?php esc_html_e('Abandoned Carts', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<!-- Orders Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-orders" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group omnify-filter-group--search">
				<label for="search_order"><?php esc_html_e('Search Order', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_order" name="search" placeholder="<?php esc_attr_e('Search order number, customer or email...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Order Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Statuses', 'omnifywp-ecommerce'); ?></option>
					<?php foreach (['pending','processing','packed','ready_to_deliver','out_for_delivery','delivered','completed','shipped','refund_requested','refunded','cancelled','failed','trash'] as $omnify_st): ?>
						<option value="<?php echo esc_attr($omnify_st); ?>" <?php selected($omnify_filter_status, $omnify_st); ?>><?php echo esc_html(ucfirst(str_replace('_',' ',$omnify_st))); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="omnify-filter-group omnify-filter-group--date">
				<label for="date_from"><?php esc_html_e('From', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_filter_date_from); ?>" />
			</div>
			<div class="omnify-filter-group omnify-filter-group--date">
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
					<span class="dashicons dashicons-search"></span>
					<span class="omnify-btn-label"><?php esc_html_e('Filter', 'omnifywp-ecommerce'); ?></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<span class="omnify-btn-label"><?php esc_html_e('Reset', 'omnifywp-ecommerce'); ?></span>
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
	<div class="omnify-card omnify-orders-list-card">

		<?php if (empty($omnify_orders)) : ?>
			<div class="omnify-empty-state" style="padding: 48px 24px; text-align: center;">
				<div class="omnify-empty-state__icon" style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
					<span class="dashicons dashicons-cart" style="font-size: 32px; width: 32px; height: 32px; color: #94a3b8;"></span>
				</div>
				<h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px;"><?php esc_html_e('No orders yet', 'omnifywp-ecommerce'); ?></h3>
				<p style="color: #64748b; font-size: 14px; margin: 0;"><?php esc_html_e('Orders placed via the storefront checkout will show up here.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-bulk-orders-form">
				<?php wp_nonce_field('omnify_bulk_orders', 'omnify_bulk_nonce'); ?>
				<input type="hidden" name="action" value="omnify_bulk_orders" />

				<div class="omnify-table-toolbar">
					<div class="left">
						<input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" style="margin-right:8px;" />
						<span class="selected-count"><?php esc_html_e('0 selected', 'omnifywp-ecommerce'); ?></span>
						<div class="omnify-bulk-actions-wrapper" style="display: none; align-items: center; gap: 8px; margin-left: 16px;">
							<select name="bulk_action">
								<option value=""><?php esc_html_e('Bulk actions', 'omnifywp-ecommerce'); ?></option>
								<option value="trash"><?php esc_html_e('Move to Trash', 'omnifywp-ecommerce'); ?></option>
								<option value="restore"><?php esc_html_e('Restore from Trash', 'omnifywp-ecommerce'); ?></option>
								<option value="mark_completed"><?php esc_html_e('Mark as Completed', 'omnifywp-ecommerce'); ?></option>
								<option value="mark_shipped"><?php esc_html_e('Mark as Shipped', 'omnifywp-ecommerce'); ?></option>
								<option value="mark_processing"><?php esc_html_e('Mark as Processing', 'omnifywp-ecommerce'); ?></option>
								<option value="mark_cancelled"><?php esc_html_e('Mark as Cancelled', 'omnifywp-ecommerce'); ?></option>
								<option value="fulfill"><?php esc_html_e('Mark Fulfilled', 'omnifywp-ecommerce'); ?></option>
								<option value="packing_slips"><?php esc_html_e('Generate Packing Slips (open printable)', 'omnifywp-ecommerce'); ?></option>
								<?php if ('trash' === $omnify_status) : ?>
									<option value="delete"><?php esc_html_e('Delete Permanently', 'omnifywp-ecommerce'); ?></option>
								<?php endif; ?>
							</select>
							<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm"><?php esc_html_e('Apply', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
					<div class="right">
						<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_export_orders'), 'omnify_export_orders')); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="display:inline-flex; align-items:center; gap:6px;">
							<span class="dashicons dashicons-download" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
							<?php esc_html_e('Export', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>

				<div class="omnify-table-wrapper">
				<table class="omnify-table">
					<thead>
						<tr>
							<th style="width: 32px;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
							<th style="width: 140px; white-space: nowrap;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></th>
							<th style="min-width: 190px;"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
							<th style="min-width: 200px;"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
							<th style="width: 130px; white-space: nowrap;"><?php esc_html_e('Total Amount', 'omnifywp-ecommerce'); ?></th>
							<th style="width: 160px; white-space: nowrap;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
							<th style="width: 170px; white-space: nowrap;"><?php esc_html_e('Date & Time', 'omnifywp-ecommerce'); ?></th>
							<th style="width: 100px; text-align: right; white-space: nowrap;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($omnify_orders as $omnify_order) : ?>
							<tr>
								<td><input class="omnify-checkbox omnify-bulk-item" type="checkbox" name="order_ids[]" value="<?php echo esc_attr($omnify_order['id']); ?>" /></td>
								<td style="white-space: nowrap;">
									<div class="omnify-order-ident">
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" class="omnify-order-link">
											#<?php echo esc_html($omnify_order['order_number'] ?: $omnify_order['id']); ?>
										</a>
										<?php if (! empty($omnify_order['fraud_status']) && $omnify_order['fraud_status'] === 'flagged') : ?>
											<span class="omnify-fraud-badge omnify-fraud-badge--flagged"><span class="dashicons dashicons-warning"></span><?php esc_html_e('Flagged', 'omnifywp-ecommerce'); ?></span>
										<?php elseif (! empty($omnify_order['fraud_status']) && $omnify_order['fraud_status'] === 'high_risk') : ?>
											<span class="omnify-fraud-badge omnify-fraud-badge--high-risk"><span class="dashicons dashicons-shield"></span><?php esc_html_e('High Risk', 'omnifywp-ecommerce'); ?></span>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<?php 
									$omnify_cust_display = $omnify_order['customer_name'] ?: $omnify_order['customer_email'];
									if ($omnify_order['customer_id'] && $omnify_cust_display) : ?>
										<div class="omnify-order-cust-cell">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_order['customer_id'])); ?>" class="omnify-order-cust-name" title="<?php echo esc_attr($omnify_cust_display); ?>">
												<?php echo esc_html($omnify_cust_display); ?>
											</a>
											<?php if (! empty($omnify_order['customer_email']) && ! empty($omnify_order['customer_name'])) : ?>
												<span class="omnify-order-cust-email" title="<?php echo esc_attr($omnify_order['customer_email']); ?>"><?php echo esc_html($omnify_order['customer_email']); ?></span>
											<?php endif; ?>
										</div>
									<?php elseif ($omnify_cust_display) : ?>
										<div class="omnify-order-cust-cell">
											<span class="omnify-order-cust-name" title="<?php echo esc_attr($omnify_cust_display); ?>"><?php echo esc_html($omnify_cust_display); ?></span>
											<?php if (! empty($omnify_order['customer_email']) && ! empty($omnify_order['customer_name'])) : ?>
												<span class="omnify-order-cust-email" title="<?php echo esc_attr($omnify_order['customer_email']); ?>"><?php echo esc_html($omnify_order['customer_email']); ?></span>
											<?php endif; ?>
										</div>
									<?php else : ?>
										<span class="omnify-guest-badge">
											<span class="dashicons dashicons-admin-users"></span>
											<?php esc_html_e('Guest Customer', 'omnifywp-ecommerce'); ?>
										</span>
									<?php endif; ?>
								</td>
								<td>
									<?php
									$omnify_order_items = ! empty($omnify_order['items']) && is_array($omnify_order['items']) ? $omnify_order['items'] : [];
									$omnify_items_count = count($omnify_order_items);
									?>
									<?php if ($omnify_items_count === 0) : ?>
										<?php if ($omnify_order['product_id']) : ?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_order['product_id'])); ?>" class="omnify-order-prod-link">
												<?php echo esc_html($omnify_order['product_name'] ?: __('Unknown Product', 'omnifywp-ecommerce')); ?>
											</a>
										<?php else : ?>
											<span class="omnify-order-product-text"><?php echo esc_html($omnify_order['product_name'] ?: __('Unknown Product', 'omnifywp-ecommerce')); ?></span>
										<?php endif; ?>
									<?php elseif ($omnify_items_count === 1) : 
										$omnify_single_item = $omnify_order_items[0];
										$omnify_single_pid  = (int) ($omnify_single_item['product_id'] ?? $omnify_order['product_id']);
										$omnify_single_name = $omnify_single_item['product_name'] ?? $omnify_order['product_name'];
										$omnify_single_qty  = max(1, (int) ($omnify_single_item['quantity'] ?? 1));
									?>
										<div class="omnify-order-prod-single">
											<?php if ($omnify_single_pid) : ?>
												<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_single_pid)); ?>" class="omnify-order-prod-link" title="<?php echo esc_attr($omnify_single_name); ?>">
													<?php echo esc_html($omnify_single_name); ?>
												</a>
											<?php else : ?>
												<span class="omnify-order-prod-link omnify-order-prod-link--plain" title="<?php echo esc_attr($omnify_single_name); ?>"><?php echo esc_html($omnify_single_name); ?></span>
											<?php endif; ?>
											<?php if ($omnify_single_qty > 1) : ?>
												<span class="omnify-order-item-qty">×<?php echo esc_html($omnify_single_qty); ?></span>
											<?php endif; ?>
										</div>
									<?php else : 
										$omnify_first_item      = $omnify_order_items[0];
										$omnify_first_pid       = (int) ($omnify_first_item['product_id'] ?? $omnify_order['product_id']);
										$omnify_first_name      = $omnify_first_item['product_name'] ?? '';
										$omnify_first_qty       = max(1, (int) ($omnify_first_item['quantity'] ?? 1));
										$omnify_remaining_count = $omnify_items_count - 1;
										$omnify_total_units     = 0;
										foreach ($omnify_order_items as $omnify_it) {
											$omnify_total_units += max(1, (int) ($omnify_it['quantity'] ?? 1));
										}
									?>
										<div class="omnify-order-prod-multi">
											<div class="omnify-order-prod-primary">
												<?php if ($omnify_first_pid) : ?>
													<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_first_pid)); ?>" class="omnify-order-prod-link" title="<?php echo esc_attr($omnify_first_name); ?>">
														<?php echo esc_html($omnify_first_name); ?>
													</a>
												<?php else : ?>
													<span class="omnify-order-prod-link omnify-order-prod-link--plain" title="<?php echo esc_attr($omnify_first_name); ?>"><?php echo esc_html($omnify_first_name); ?></span>
												<?php endif; ?>
												<?php if ($omnify_first_qty > 1) : ?>
													<span class="omnify-order-item-qty">×<?php echo esc_html($omnify_first_qty); ?></span>
												<?php endif; ?>
											</div>
											<div class="omnify-order-items-popover-wrap">
												<button type="button" class="omnify-order-more-btn" tabindex="0" aria-label="<?php echo esc_attr(sprintf(__('+%d more items', 'omnifywp-ecommerce'), $omnify_remaining_count)); ?>">
													<span>+<?php echo esc_html($omnify_remaining_count); ?> <?php esc_html_e('more', 'omnifywp-ecommerce'); ?></span>
													<span class="dashicons dashicons-arrow-down-alt2"></span>
												</button>
												<div class="omnify-order-items-popover">
													<div class="omnify-order-items-popover__header">
														<strong><?php printf(esc_html__('Order Items (%d items, %d units)', 'omnifywp-ecommerce'), $omnify_items_count, $omnify_total_units); ?></strong>
													</div>
													<ul class="omnify-order-items-popover__list">
														<?php foreach ($omnify_order_items as $omnify_p_item) : 
															$omnify_pi_id    = (int) ($omnify_p_item['product_id'] ?? 0);
															$omnify_pi_qty   = max(1, (int) ($omnify_p_item['quantity'] ?? 1));
															$omnify_pi_price = (float) ($omnify_p_item['price'] ?? 0);
															$omnify_pi_name  = $omnify_p_item['product_name'] ?? __('Product', 'omnifywp-ecommerce');
														?>
															<li class="omnify-order-items-popover__item">
																<span class="omnify-order-items-popover__qty"><?php echo esc_html($omnify_pi_qty); ?>×</span>
																<span class="omnify-order-items-popover__title">
																	<?php if ($omnify_pi_id) : ?>
																		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_pi_id)); ?>">
																			<?php echo esc_html($omnify_pi_name); ?>
																		</a>
																	<?php else : ?>
																		<?php echo esc_html($omnify_pi_name); ?>
																	<?php endif; ?>
																</span>
																<?php if ($omnify_pi_price > 0) : ?>
																	<span class="omnify-order-items-popover__price">
																		<?php 
																		$omnify_item_total = $omnify_pi_price * $omnify_pi_qty;
																		$omnify_cur_sym = $omnify_currency_symbol($omnify_order['currency']);
																		echo esc_html($omnify_cur_sym . number_format($omnify_item_total, $omnify_dec, $omnify_decs, $omnify_tho));
																		?>
																	</span>
																<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ul>
													<div class="omnify-order-items-popover__footer">
														<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>">
															<?php esc_html_e('View Order Details →', 'omnifywp-ecommerce'); ?>
														</a>
													</div>
												</div>
											</div>
										</div>
									<?php endif; ?>
								</td>
								<td style="white-space: nowrap;">
									<div class="omnify-order-total-cell">
										<strong class="omnify-order-total-amount">
											<?php 
											$omnify_order_symbol = $omnify_currency_symbol($omnify_order['currency']);
											$omnify_dec = (int)($omnify_settings['price_decimals'] ?? 2);
											$omnify_tho = $omnify_settings['thousand_separator'] ?? ',';
											$omnify_decs = $omnify_settings['decimal_separator'] ?? '.';
											echo esc_html($omnify_order_symbol . number_format($omnify_order['total'], $omnify_dec, $omnify_decs, $omnify_tho)); 
											?>
										</strong>
									</div>
								</td>
								<td style="white-space: nowrap;">
									<div class="omnify-order-status-stack">
										<div class="omnify-quick-status-wrapper">
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
											<span class="omnify-fulfillment-badge omnify-fulfillment-badge--<?php echo esc_attr($omnify_order['fulfillment_status']); ?>">
												<?php echo esc_html(ucfirst($omnify_order['fulfillment_status'])); ?>
											</span>
										<?php endif; ?>
									</div>
								</td>
								<td style="white-space: nowrap;">
									<span class="omnify-order-date-text">
										<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_order['created_at']))); ?>
									</span>
								</td>
								<td style="text-align: right; white-space: nowrap;">
									<div class="omnify-row-actions">
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('View Order Details', 'omnifywp-ecommerce'); ?>">
											<span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
										</a>
										<?php $omnify_is_trashed = !empty($omnify_order['deleted_at']); ?>
										<?php if ($omnify_is_trashed): ?>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_order&order_id=' . $omnify_order['id']), 'omnify_restore_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Restore', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_order&order_id=' . $omnify_order['id']), 'omnify_delete_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Delete permanently', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php esc_attr_e('Permanently delete order?', 'omnifywp-ecommerce'); ?>');">
												<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
										<?php else: ?>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_order&order_id=' . $omnify_order['id']), 'omnify_trash_order_' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Move to trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php esc_attr_e('Move order to trash?', 'omnifywp-ecommerce'); ?>');">
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
			</div>
			</form>
			<?php if ($omnify_total_pages > 1) : ?>
				<div class="tablenav" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-top: 1px solid var(--omnify-gray-200, #e2e8f0);">
					<div class="tablenav-pages" style="display: flex; align-items: center; gap: 8px;">
						<span class="displaying-num" style="font-size: 13px; color: #64748b;">
							<?php
							/* translators: %s: total orders count */
							printf(esc_html(_n('%s order', '%s orders', $omnify_total_orders, 'omnifywp-ecommerce')), number_format_i18n($omnify_total_orders));
							?>
						</span>
						<span class="pagination-links" style="display: flex; gap: 4px; align-items: center;">
							<?php
							$omnify_current_url = remove_query_arg('paged');
							if ($omnify_paged > 1) : ?>
								<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('First page', 'omnifywp-ecommerce'); ?>">&laquo;</a>
								<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_paged - 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('Previous page', 'omnifywp-ecommerce'); ?>">&lsaquo;</a>
							<?php endif; ?>
							<span class="paging-input" style="font-size: 13px; margin: 0 4px;">
								<span class="current-page"><?php echo esc_html((string) $omnify_paged); ?></span>
								<span class="tablenav-paging-text"> <?php esc_html_e('of', 'omnifywp-ecommerce'); ?> <span class="total-pages"><?php echo esc_html((string) $omnify_total_pages); ?></span></span>
							</span>
							<?php if ($omnify_paged < $omnify_total_pages) : ?>
								<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_paged + 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('Next page', 'omnifywp-ecommerce'); ?>">&rsaquo;</a>
								<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_total_pages, $omnify_current_url)); ?>" title="<?php esc_attr_e('Last page', 'omnifywp-ecommerce'); ?>">&raquo;</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

