<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_cust = $omnify_template_vars['omnify_cust'] ?? null;
$omnify_order = $omnify_template_vars['omnify_order'] ?? null;
$omnify_recent_customers = $omnify_template_vars['omnify_recent_customers'] ?? null;
$omnify_recent_orders = $omnify_template_vars['omnify_recent_orders'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_stats = $omnify_template_vars['omnify_stats'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_trends = $omnify_template_vars['omnify_trends'] ?? null;


/**
 * Admin Dashboard Template
 *
 * @package Omnify
 */
$omnify_settings = is_array($omnify_settings) ? $omnify_settings : [];
$omnify_stats = wp_parse_args(
	is_array($omnify_stats) ? $omnify_stats : [],
	[
		'revenue'         => 0,
		'orders'          => 0,
		'customers'       => 0,
		'downloads'       => 0,
		'products'        => 0,
		'revenue_trend'   => 0,
		'orders_trend'    => 0,
		'customers_trend' => 0,
		'downloads_trend' => 0,
	]
);

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
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'dashboard'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Overview', 'omnifywp-ecommerce'); ?></h1>
			<p><?php
			// translators: %s: placeholder value. echo esc_html(sprintf(__('Welcome to %s.', 'omnifywp-ecommerce'), $settings['store_name'] ?? '')); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
				<?php esc_html_e('View Storefront', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<!-- Stats Grid -->
	<div class="omnify-dashboard-stats" style="margin-bottom: 24px;">
		<?php
		$omnify_trends = [
			'revenue' => $omnify_stats['revenue_trend'] ?? 0,
			'orders' => $omnify_stats['orders_trend'] ?? 0,
			'customers' => $omnify_stats['customers_trend'] ?? 0,
			'downloads' => $omnify_stats['downloads_trend'] ?? 0,
		];
		?>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Total Revenue', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; gap: 8px; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value"><?php echo esc_html($omnify_symbol . number_format($omnify_stats['revenue'], 2)); ?></p>
				<span class="omnify-trend <?php echo esc_attr((($omnify_trends['revenue'] ?? 0) >= 0) ? 'positive' : 'negative'); ?>">
					<?php echo esc_html( ((($omnify_trends['revenue'] ?? 0) >= 0) ? '+' : '') . number_format($omnify_trends['revenue'] ?? 0, 1) . '%' ); ?>
				</span>
			</div>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Total Orders', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; gap: 8px; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value"><?php echo esc_html(number_format($omnify_stats['orders'])); ?></p>
				<span class="omnify-trend <?php echo esc_attr((($omnify_trends['orders'] ?? 0) >= 0) ? 'positive' : 'negative'); ?>">
					<?php echo esc_html( ((($omnify_trends['orders'] ?? 0) >= 0) ? '+' : '') . number_format($omnify_trends['orders'] ?? 0, 1) . '%' ); ?>
				</span>
			</div>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; gap: 8px; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value"><?php echo esc_html(number_format($omnify_stats['customers'])); ?></p>
				<span class="omnify-trend <?php echo esc_attr((($omnify_trends['customers'] ?? 0) >= 0) ? 'positive' : 'negative'); ?>">
					<?php echo esc_html( ((($omnify_trends['customers'] ?? 0) >= 0) ? '+' : '') . number_format($omnify_trends['customers'] ?? 0, 1) . '%' ); ?>
				</span>
			</div>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Total Downloads', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; gap: 8px; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value"><?php echo esc_html(number_format($omnify_stats['downloads'])); ?></p>
				<span class="omnify-trend <?php echo esc_attr((($omnify_trends['downloads'] ?? 0) >= 0) ? 'positive' : 'negative'); ?>">
					<?php echo esc_html( ((($omnify_trends['downloads'] ?? 0) >= 0) ? '+' : '') . number_format($omnify_trends['downloads'] ?? 0, 1) . '%' ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Store Status Summary -->
	<div class="omnify-card" style="margin-bottom: 24px;">
		<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
			<h2 style="margin:0; font-size:15px; font-weight:600; color:var(--omnify-dark);"><?php esc_html_e('Store at a Glance', 'omnifywp-ecommerce'); ?></h2>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-analytics')); ?>" style="font-size:12px; font-weight:600; color:var(--omnify-primary); text-decoration:none;">View Analytics &rarr;</a>
		</div>
		<div class="omnify-dashboard-summary-grid">
			<div class="omnify-summary-tile">
				<span class="icon"><span class="dashicons dashicons-archive" style="font-size: 20px; width: 20px; height: 20px;"></span></span>
				<div class="content">
					<div class="label"><?php esc_html_e('Published Products', 'omnifywp-ecommerce'); ?></div>
					<div class="value"><?php echo esc_html(number_format($omnify_stats['active_products'] ?? 0)); ?></div>
				</div>
			</div>
			<div class="omnify-summary-tile">
				<span class="icon"><span class="dashicons dashicons-tag" style="font-size: 20px; width: 20px; height: 20px;"></span></span>
				<div class="content">
					<div class="label"><?php esc_html_e('Active Coupons', 'omnifywp-ecommerce'); ?></div>
					<div class="value"><?php echo esc_html(number_format($omnify_stats['active_coupons'] ?? 0)); ?></div>
				</div>
			</div>
			<div class="omnify-summary-tile">
				<span class="icon"><span class="dashicons dashicons-admin-comments" style="font-size: 20px; width: 20px; height: 20px;"></span></span>
				<div class="content">
					<div class="label"><?php esc_html_e('Pending Reviews', 'omnifywp-ecommerce'); ?></div>
					<div class="value" style="color: <?php echo ($omnify_stats['pending_reviews'] ?? 0) > 0 ? 'var(--omnify-warning)' : 'var(--omnify-dark)'; ?>;"><?php echo esc_html(number_format($omnify_stats['pending_reviews'] ?? 0)); ?></div>
				</div>
				<?php if (($omnify_stats['pending_reviews'] ?? 0) > 0): ?>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-reviews')); ?>" class="omnify-icon-btn omnify-icon-btn--view" title="<?php esc_attr_e('Moderate reviews', 'omnifywp-ecommerce'); ?>" style="width:26px;height:26px;">
						<span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Columns Layout -->
	<div class="omnify-layout-grid">
		<!-- Left: Recent Orders -->
		<div class="omnify-card">
			<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
				<h2 style="margin:0; font-size:15px;"><?php esc_html_e('Recent Orders', 'omnifywp-ecommerce'); ?></h2>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" style="font-size:12px; color:var(--omnify-primary); font-weight:600; text-decoration:none;">View all &rarr;</a>
			</div>
			<?php if (empty($omnify_recent_orders)) : ?>
				<div class="omnify-empty-state">
					<div class="omnify-empty-state__icon"><span class="dashicons dashicons-cart" style="font-size: 48px; width: 48px; height: 48px; color: var(--omnify-gray-400);"></span></div>
					<h3><?php esc_html_e('No orders yet', 'omnifywp-ecommerce'); ?></h3>
					<p><?php esc_html_e('Orders will appear here once customers purchase.', 'omnifywp-ecommerce'); ?></p>
				</div>
			<?php else : ?>
				<div class="omnify-table-wrapper">
					<table class="omnify-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Order', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
								<th style="width:60px;"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_recent_orders as $omnify_order) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" style="font-weight:600; text-decoration:none; color:var(--omnify-primary);">
											<?php echo esc_html($omnify_order['order_number'] ?: '#' . $omnify_order['id']); ?>
										</a>
									</td>
									<td>
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_order['customer_id'])); ?>" style="font-weight:500;">
											<?php echo esc_html(($omnify_order['customer_name'] ?? '') ?: ($omnify_order['customer_email'] ?? '')); ?>
										</a>
									</td>
									<td><strong><?php echo esc_html($omnify_symbol . number_format($omnify_order['total'], 2)); ?></strong></td>
									<td>
										<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_order['status']); ?>">
											<?php echo esc_html(ucfirst($omnify_order['status'])); ?>
										</span>
									</td>
									<td style="text-align:right;">
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_order['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--view" title="<?php esc_attr_e('View order', 'omnifywp-ecommerce'); ?>">
											<span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

		<!-- Right: Quick Actions + Recent Customers + more options -->
		<div>
			<div class="omnify-card">
				<h2 style="margin-bottom:12px; font-size:15px;"><?php esc_html_e('Quick Actions', 'omnifywp-ecommerce'); ?></h2>
				<div class="omnify-quick-actions">
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=new')); ?>" class="omnify-button omnify-button--primary omnify-button--full">
						<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e('Add New Product', 'omnifywp-ecommerce'); ?>
					</a>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-coupons')); ?>" class="omnify-button omnify-button--secondary omnify-button--full">
						<span class="dashicons dashicons-tag" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e('Create Coupon', 'omnifywp-ecommerce'); ?>
					</a>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-button omnify-button--secondary omnify-button--full">
						<span class="dashicons dashicons-cart" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e('Manage Orders', 'omnifywp-ecommerce'); ?>
					</a>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-settings')); ?>" class="omnify-button omnify-button--secondary omnify-button--full">
						<span class="dashicons dashicons-admin-settings" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e('Settings & Integrations', 'omnifywp-ecommerce'); ?>
					</a>
				</div>
			</div>

			<div class="omnify-card">
				<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
					<h2 style="margin:0; font-size:15px;"><?php esc_html_e('Recent Customers', 'omnifywp-ecommerce'); ?></h2>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers')); ?>" style="font-size:12px; color:var(--omnify-primary); font-weight:600; text-decoration:none;">All &rarr;</a>
				</div>
				<?php if (empty($omnify_recent_customers)) : ?>
					<p style="color: var(--omnify-gray-600); font-size: 13px; margin:0;"><?php esc_html_e('No customers yet.', 'omnifywp-ecommerce'); ?></p>
				<?php else : ?>
					<div style="display:flex; flex-direction:column; gap:8px;">
						<?php foreach (array_slice($omnify_recent_customers, 0, 4) as $omnify_cust) : ?>
							<div style="display:flex; justify-content:space-between; align-items:center; font-size:13px;">
								<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_cust['id'])); ?>" style="font-weight:600; color:var(--omnify-dark); text-decoration:none;">
									<?php echo esc_html($omnify_cust['name'] ?: $omnify_cust['email']); ?>
								</a>
								<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_cust['status']); ?>" style="font-size:10px; padding:1px 5px;">
									<?php echo esc_html($omnify_cust['status']); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
