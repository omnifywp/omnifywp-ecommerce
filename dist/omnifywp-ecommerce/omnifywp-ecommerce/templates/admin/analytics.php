<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_all_analytics_categories = $omnify_template_vars['omnify_all_analytics_categories'] ?? null;
$omnify_aov = $omnify_template_vars['omnify_aov'] ?? null;
$omnify_aov_trend = $omnify_template_vars['omnify_aov_trend'] ?? null;
$omnify_avg_items_per_order = $omnify_template_vars['omnify_avg_items_per_order'] ?? null;
$omnify_avg_items_trend = $omnify_template_vars['omnify_avg_items_trend'] ?? null;
$omnify_bg = $omnify_template_vars['omnify_bg'] ?? null;
$omnify_cat = $omnify_template_vars['omnify_cat'] ?? null;
$omnify_cat_labels = $omnify_template_vars['omnify_cat_labels'] ?? null;
$omnify_cat_revenue = $omnify_template_vars['omnify_cat_revenue'] ?? null;
$omnify_chart_labels = $omnify_template_vars['omnify_chart_labels'] ?? null;
$omnify_chart_orders = $omnify_template_vars['omnify_chart_orders'] ?? null;
$omnify_chart_revenue = $omnify_template_vars['omnify_chart_revenue'] ?? null;
$omnify_coupon_discounts = $omnify_template_vars['omnify_coupon_discounts'] ?? null;
$omnify_coupon_discounts_trend = $omnify_template_vars['omnify_coupon_discounts_trend'] ?? null;
$omnify_coupon_filter = $omnify_template_vars['omnify_coupon_filter'] ?? null;
$omnify_coupon_order_rate = $omnify_template_vars['omnify_coupon_order_rate'] ?? null;
$omnify_coupon_order_rate_trend = $omnify_template_vars['omnify_coupon_order_rate_trend'] ?? null;
$omnify_coupons_perf = $omnify_template_vars['omnify_coupons_perf'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_customers_trend = $omnify_template_vars['omnify_customers_trend'] ?? null;
$omnify_downloads_count = $omnify_template_vars['omnify_downloads_count'] ?? null;
$omnify_downloads_trend = $omnify_template_vars['omnify_downloads_trend'] ?? null;
$omnify_end_date_raw = $omnify_template_vars['omnify_end_date_raw'] ?? null;
$omnify_fg = $omnify_template_vars['omnify_fg'] ?? null;
$omnify_fmt_price = $omnify_template_vars['omnify_fmt_price'] ?? null;
$omnify_gross_sales = $omnify_template_vars['omnify_gross_sales'] ?? null;
$omnify_gross_trend = $omnify_template_vars['omnify_gross_trend'] ?? null;
$omnify_items_sold = $omnify_template_vars['omnify_items_sold'] ?? null;
$omnify_items_sold_trend = $omnify_template_vars['omnify_items_sold_trend'] ?? null;
$omnify_net_after_refunds = $omnify_template_vars['omnify_net_after_refunds'] ?? null;
$omnify_net_refunds_trend = $omnify_template_vars['omnify_net_refunds_trend'] ?? null;
$omnify_net_sales = $omnify_template_vars['omnify_net_sales'] ?? null;
$omnify_net_trend = $omnify_template_vars['omnify_net_trend'] ?? null;
$omnify_new_customers_count = $omnify_template_vars['omnify_new_customers_count'] ?? null;
$omnify_order_count = $omnify_template_vars['omnify_order_count'] ?? null;
$omnify_orders_trend = $omnify_template_vars['omnify_orders_trend'] ?? null;
$omnify_payment_breakdown = $omnify_template_vars['omnify_payment_breakdown'] ?? null;
$omnify_pm_labels = $omnify_template_vars['omnify_pm_labels'] ?? null;
$omnify_pm_orders = $omnify_template_vars['omnify_pm_orders'] ?? null;
$omnify_prod_symbol = $omnify_template_vars['omnify_prod_symbol'] ?? null;
$omnify_range = $omnify_template_vars['omnify_range'] ?? null;
$omnify_recent_orders = $omnify_template_vars['omnify_recent_orders'] ?? null;
$omnify_refund_rate = $omnify_template_vars['omnify_refund_rate'] ?? null;
$omnify_refund_rate_trend = $omnify_template_vars['omnify_refund_rate_trend'] ?? null;
$omnify_refunded_amount = $omnify_template_vars['omnify_refunded_amount'] ?? null;
$omnify_render_trend = isset($omnify_template_vars['omnify_render_trend']) && is_callable($omnify_template_vars['omnify_render_trend']) ? $omnify_template_vars['omnify_render_trend'] : 'omnify_render_trend';
$omnify_selected_category = $omnify_template_vars['omnify_selected_category'] ?? null;
$omnify_selected_method = $omnify_template_vars['omnify_selected_method'] ?? null;
$omnify_selected_status = $omnify_template_vars['omnify_selected_status'] ?? null;
$omnify_selected_type = $omnify_template_vars['omnify_selected_type'] ?? null;
$omnify_shipping_collected = $omnify_template_vars['omnify_shipping_collected'] ?? null;
$omnify_shipping_trend = $omnify_template_vars['omnify_shipping_trend'] ?? null;
$omnify_start_date_raw = $omnify_template_vars['omnify_start_date_raw'] ?? null;
$omnify_status_breakdown = $omnify_template_vars['omnify_status_breakdown'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_tax_breakdown = $omnify_template_vars['omnify_tax_breakdown'] ?? null;
$omnify_tax_collected = $omnify_template_vars['omnify_tax_collected'] ?? null;
$omnify_tax_trend = $omnify_template_vars['omnify_tax_trend'] ?? null;
$omnify_top_customers = $omnify_template_vars['omnify_top_customers'] ?? null;
$omnify_top_selling = $omnify_template_vars['omnify_top_selling'] ?? null;
$omnify_trend = $omnify_template_vars['omnify_trend'] ?? null;
$omnify_trend_sign = $omnify_template_vars['omnify_trend_sign'] ?? null;
$omnify_val = $omnify_template_vars['omnify_val'] ?? null;
$omnify_weekday_labels = $omnify_template_vars['omnify_weekday_labels'] ?? null;
$omnify_weekday_orders_cnt = $omnify_template_vars['omnify_weekday_orders_cnt'] ?? null;

$omnify_chart_data_js = [
	'labels'         => $omnify_chart_labels,
	'revenue'        => $omnify_chart_revenue,
	'orders'         => $omnify_chart_orders,
	'catLabels'      => $omnify_cat_labels,
	'catRevenue'     => $omnify_cat_revenue,
	'pmLabels'       => $omnify_pm_labels,
	'pmOrders'       => $omnify_pm_orders,
	'weekdayLabels'  => $omnify_weekday_labels,
	'weekdayRevenue' => $omnify_weekday_revenue,
	'weekdayOrders'  => $omnify_weekday_orders_cnt,
];
wp_add_inline_script('omnify-admin', 'window.omnifyChartData = ' . wp_json_encode($omnify_chart_data_js) . ';', 'before');



/**
 * Admin Analytics Template
 *
 * @package Omnify
 */
// Format helper
$omnify_fmt_price = function($omnify_val) use ($omnify_symbol) {
	return $omnify_symbol . number_format($omnify_val, 2);
};

// Safe defaults for new filters/metrics
$omnify_all_analytics_categories = $omnify_all_analytics_categories ?? [];
$omnify_selected_category = $omnify_selected_category ?? '';
$omnify_coupon_filter = $omnify_coupon_filter ?? '';
$omnify_refund_rate = $omnify_refund_rate ?? 0;
$omnify_avg_items_per_order = $omnify_avg_items_per_order ?? 0;
$omnify_refund_rate_trend = $omnify_refund_rate_trend ?? 0;
$omnify_avg_items_trend = $omnify_avg_items_trend ?? 0;
$omnify_weekday_labels = $omnify_weekday_labels ?? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$omnify_weekday_revenue = $omnify_weekday_revenue ?? [0,0,0,0,0,0,0];
$omnify_weekday_orders_cnt = $omnify_weekday_orders_cnt ?? [0,0,0,0,0,0,0];

// Trend rendering helper
if (! isset($omnify_render_trend) || ! is_callable($omnify_render_trend)) {
	$omnify_render_trend = 'omnify_render_trend';
}
?>
<div class="omnify-admin-wrapper omnify-analytics-page">
	<!-- Title and Export Header -->
	<div class="omnify-header-row" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
		<div class="omnify-header-row__title">
			<h1 style="margin: 0 0 4px; font-family: 'Outfit', sans-serif; font-weight: 500; color: var(--omnify-dark);"><?php esc_html_e('Analytics', 'omnifywp-ecommerce'); ?></h1>
			<p style="margin: 0; font-size: 13px; color: var(--omnify-gray-600);"><?php esc_html_e('Analyze your storefront performance, coupon conversions, and sales metrics.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div>
			<a href="<?php echo esc_url(add_query_arg('export_csv', '1')); ?>" class="omnify-button omnify-button--primary" style="display: inline-flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-download" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> <?php esc_html_e('Export Report (CSV)', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<!-- Navigation Tabs -->
	<?php $omnify_active_tab = 'analytics'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>

	<!-- Date & Multi-dimensional Filters Panel -->
	<!-- Date & Multi-dimensional Filters Panel -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-analytics" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="omnify-analytics-range"><?php esc_html_e('Date Range', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-range" name="range">
					<option value="today" <?php selected($omnify_range, 'today'); ?>><?php esc_html_e('Today', 'omnifywp-ecommerce'); ?></option>
					<option value="yesterday" <?php selected($omnify_range, 'yesterday'); ?>><?php esc_html_e('Yesterday', 'omnifywp-ecommerce'); ?></option>
					<option value="7days" <?php selected($omnify_range, '7days'); ?>><?php esc_html_e('Last 7 Days', 'omnifywp-ecommerce'); ?></option>
					<option value="30days" <?php selected($omnify_range, '30days'); ?>><?php esc_html_e('Last 30 Days', 'omnifywp-ecommerce'); ?></option>
					<option value="this_month" <?php selected($omnify_range, 'this_month'); ?>><?php esc_html_e('This Month', 'omnifywp-ecommerce'); ?></option>
					<option value="all_time" <?php selected($omnify_range, 'all_time'); ?>><?php esc_html_e('All Time', 'omnifywp-ecommerce'); ?></option>
					<option value="custom" <?php selected($omnify_range, 'custom'); ?>><?php esc_html_e('Custom Range', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="omnify-analytics-type"><?php esc_html_e('Product Type', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-type" name="product_type">
					<option value=""><?php esc_html_e('All Types', 'omnifywp-ecommerce'); ?></option>
					<option value="physical" <?php selected($omnify_selected_type, 'physical'); ?>><?php esc_html_e('Physical', 'omnifywp-ecommerce'); ?></option>
					<option value="digital" <?php selected($omnify_selected_type, 'digital'); ?>><?php esc_html_e('Digital', 'omnifywp-ecommerce'); ?></option>
					<option value="variable" <?php selected($omnify_selected_type, 'variable'); ?>><?php esc_html_e('Variable', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="omnify-analytics-method"><?php esc_html_e('Payment Method', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-method" name="payment_method">
					<option value=""><?php esc_html_e('All Methods', 'omnifywp-ecommerce'); ?></option>
					<option value="stripe" <?php selected($omnify_selected_method, 'stripe'); ?>><?php esc_html_e('Stripe', 'omnifywp-ecommerce'); ?></option>
					<option value="paypal" <?php selected($omnify_selected_method, 'paypal'); ?>><?php esc_html_e('PayPal', 'omnifywp-ecommerce'); ?></option>
					<option value="bank_transfer" <?php selected($omnify_selected_method, 'bank_transfer'); ?>><?php esc_html_e('Bank Transfer', 'omnifywp-ecommerce'); ?></option>
					<option value="cheque" <?php selected($omnify_selected_method, 'cheque'); ?>><?php esc_html_e('Cheque Payment', 'omnifywp-ecommerce'); ?></option>
					<option value="cash_on_delivery" <?php selected($omnify_selected_method, 'cash_on_delivery'); ?>><?php esc_html_e('Cash on Delivery', 'omnifywp-ecommerce'); ?></option>
					<option value="custom" <?php selected($omnify_selected_method, 'custom'); ?>><?php esc_html_e('Manual Payment', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="omnify-analytics-status"><?php esc_html_e('Order Status', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-status" name="order_status">
					<option value=""><?php esc_html_e('All Statuses (Active)', 'omnifywp-ecommerce'); ?></option>
					<option value="completed" <?php selected($omnify_selected_status, 'completed'); ?>><?php esc_html_e('Completed', 'omnifywp-ecommerce'); ?></option>
					<option value="processing" <?php selected($omnify_selected_status, 'processing'); ?>><?php esc_html_e('Processing', 'omnifywp-ecommerce'); ?></option>
					<option value="packed" <?php selected($omnify_selected_status, 'packed'); ?>><?php esc_html_e('Packed', 'omnifywp-ecommerce'); ?></option>
					<option value="ready_to_deliver" <?php selected($omnify_selected_status, 'ready_to_deliver'); ?>><?php esc_html_e('Ready to Deliver', 'omnifywp-ecommerce'); ?></option>
					<option value="shipped" <?php selected($omnify_selected_status, 'shipped'); ?>><?php esc_html_e('Shipped', 'omnifywp-ecommerce'); ?></option>
					<option value="out_for_delivery" <?php selected($omnify_selected_status, 'out_for_delivery'); ?>><?php esc_html_e('Out for Delivery', 'omnifywp-ecommerce'); ?></option>
					<option value="delivered" <?php selected($omnify_selected_status, 'delivered'); ?>><?php esc_html_e('Delivered', 'omnifywp-ecommerce'); ?></option>
					<option value="refund_requested" <?php selected($omnify_selected_status, 'refund_requested'); ?>><?php esc_html_e('Refund Requested', 'omnifywp-ecommerce'); ?></option>
					<option value="refunded" <?php selected($omnify_selected_status, 'refunded'); ?>><?php esc_html_e('Refunded', 'omnifywp-ecommerce'); ?></option>
					<option value="cancelled" <?php selected($omnify_selected_status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'omnifywp-ecommerce'); ?></option>
					<option value="failed" <?php selected($omnify_selected_status, 'failed'); ?>><?php esc_html_e('Failed', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="omnify-analytics-category"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-category" name="category">
					<option value=""><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></option>
					<?php foreach ($omnify_all_analytics_categories as $omnify_cat) : ?>
						<option value="<?php echo esc_attr($omnify_cat); ?>" <?php selected($omnify_selected_category, $omnify_cat); ?>><?php echo esc_html($omnify_cat); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="omnify-analytics-coupon"><?php esc_html_e('Coupon', 'omnifywp-ecommerce'); ?></label>
				<select id="omnify-analytics-coupon" name="coupon_filter">
					<option value="" <?php selected($omnify_coupon_filter, ''); ?>><?php esc_html_e('All Orders', 'omnifywp-ecommerce'); ?></option>
					<option value="with" <?php selected($omnify_coupon_filter, 'with'); ?>><?php esc_html_e('With Coupon', 'omnifywp-ecommerce'); ?></option>
					<option value="without" <?php selected($omnify_coupon_filter, 'without'); ?>><?php esc_html_e('No Coupon', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div id="omnify-analytics-custom-dates" style="display: <?php echo $omnify_range === 'custom' ? 'flex' : 'none'; ?>; gap: 14px;">
				<div class="omnify-filter-group">
					<label for="start_date"><?php esc_html_e('Start Date', 'omnifywp-ecommerce'); ?></label>
					<input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($omnify_start_date_raw); ?>" />
				</div>
				<div class="omnify-filter-group">
					<label for="end_date"><?php esc_html_e('End Date', 'omnifywp-ecommerce'); ?></label>
					<input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($omnify_end_date_raw); ?>" />
				</div>
			</div>

			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-analytics')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<div class="omnify-analytics-subtabs" role="tablist" aria-label="<?php esc_attr_e('Analytics sections', 'omnifywp-ecommerce'); ?>">
		<button type="button" class="omnify-analytics-subtab is-active" data-analytics-tab="overview" role="tab" aria-selected="true"><?php esc_html_e('Overview', 'omnifywp-ecommerce'); ?></button>
		<button type="button" class="omnify-analytics-subtab" data-analytics-tab="charts" role="tab" aria-selected="false"><?php esc_html_e('Charts', 'omnifywp-ecommerce'); ?></button>
		<button type="button" class="omnify-analytics-subtab" data-analytics-tab="products" role="tab" aria-selected="false"><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></button>
		<button type="button" class="omnify-analytics-subtab" data-analytics-tab="customers" role="tab" aria-selected="false"><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></button>
		<button type="button" class="omnify-analytics-subtab" data-analytics-tab="finance" role="tab" aria-selected="false"><?php esc_html_e('Finance', 'omnifywp-ecommerce'); ?></button>
	</div>

	<!-- KPIs Dashboard Summary Grid -->
	<div class="omnify-dashboard-stats" data-analytics-section="overview" style="margin-bottom: 24px;">
		<!-- 1. Gross Sales -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Gross Sales', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_gross_sales)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_gross_trend)); ?>
			</div>
		</div>

		<!-- 2. Net Sales -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Net Sales', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_net_sales)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_net_trend)); ?>
			</div>
		</div>

		<!-- 3. Net After Refunds -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Net After Refunds', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_net_after_refunds)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_net_refunds_trend)); ?>
			</div>
		</div>

		<!-- 4. Total Orders -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Total Orders', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_order_count)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_orders_trend)); ?>
			</div>
		</div>

		<!-- 5. Average Order Value -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Average Order Value', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_aov)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_aov_trend)); ?>
			</div>
		</div>

		<!-- 6. Items Sold -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Items Sold', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_items_sold)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_items_sold_trend)); ?>
			</div>
		</div>

		<!-- 7. Coupon Discounts -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Coupon Discounts', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2; color: var(--omnify-danger) !important;">-<?php echo esc_html($omnify_fmt_price($omnify_coupon_discounts)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_coupon_discounts_trend)); ?>
			</div>
		</div>

		<!-- 8. Coupon Order Share -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Coupon Order Share', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_coupon_order_rate, 1)); ?>%</p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_coupon_order_rate_trend)); ?>
			</div>
		</div>

		<!-- 9. Refunded -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Refunded', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2; color: var(--omnify-danger) !important;">-<?php echo esc_html($omnify_fmt_price($omnify_refunded_amount)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_refunded_trend)); ?>
			</div>
		</div>

		<!-- 10. Tax Collected -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Tax Collected', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_tax_collected)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_tax_trend)); ?>
			</div>
		</div>

		<!-- 11. Shipping Collected -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Shipping Collected', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_fmt_price($omnify_shipping_collected)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_shipping_trend)); ?>
			</div>
		</div>

		<!-- 12. Digital File Downloads -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Digital File Downloads', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_downloads_count)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_downloads_trend)); ?>
			</div>
		</div>

		<!-- 13. New Customers -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('New Customers', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_new_customers_count)); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_customers_trend)); ?>
			</div>
		</div>

		<!-- 14. Refund Rate -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Refund Rate', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html(number_format($omnify_refund_rate, 1)); ?>%</p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_refund_rate_trend)); ?>
			</div>
		</div>

		<!-- 15. Avg Items / Order -->
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Avg Items / Order', 'omnifywp-ecommerce'); ?></span>
			<div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-top: 8px;">
				<p class="omnify-dashboard-stat-card__value" style="margin: 0; line-height: 1.2;"><?php echo esc_html($omnify_avg_items_per_order); ?></p>
				<?php echo wp_kses_post(omnify_render_trend($omnify_avg_items_trend)); ?>
			</div>
		</div>
	</div>

	<!-- Charts Layout Grid -->
	<div class="omnify-charts-grid">
		<!-- Sales & Orders Chart Card -->
		<div class="omnify-card" data-analytics-section="overview" style="padding: 0 !important; overflow: hidden; display: flex; flex-direction: column;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Sales & Orders Performance', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
				<?php if (empty($omnify_chart_revenue) && empty($omnify_chart_orders)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 280px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-chart-line" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No performance data available for this range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div style="flex-grow: 1; position: relative; min-height: 280px; height: 280px;">
						<canvas id="omnify-sales-chart"></canvas>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Top Categories Share Card -->
		<div class="omnify-card" data-analytics-section="charts" style="padding: 0 !important; overflow: hidden; display: flex; flex-direction: column;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Top Categories Share', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
				<?php if (empty($omnify_cat_revenue)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 280px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-category" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No category data available.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div style="flex-grow: 1; position: relative; min-height: 280px; height: 280px; display: flex; align-items: center; justify-content: center;">
						<canvas id="omnify-categories-chart"></canvas>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Payment Methods Share Card -->
		<div class="omnify-card" data-analytics-section="charts" style="padding: 0 !important; overflow: hidden; display: flex; flex-direction: column;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Payment Methods Share', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
				<?php if (empty($omnify_pm_orders)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 280px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-cart" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No payment data available.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div style="flex-grow: 1; position: relative; min-height: 280px; height: 280px; display: flex; align-items: center; justify-content: center;">
						<canvas id="omnify-payments-chart"></canvas>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Weekday Performance -->
		<div class="omnify-card" data-analytics-section="charts" style="padding: 0 !important; overflow: hidden; display: flex; flex-direction: column;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Sales by Day of Week', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
				<?php if (empty($omnify_weekday_revenue) || ! array_filter($omnify_weekday_revenue)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 220px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-chart-bar" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No weekday sales data available.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div style="flex-grow: 1; position: relative; min-height: 220px; height: 220px;">
						<canvas id="omnify-weekday-chart"></canvas>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Flat Balanced CSS Grid for Bottom Reporting Cards -->
	<div class="omnify-bottom-cards-grid">
		<!-- 1. Top Selling Products -->
		<div class="omnify-card" data-analytics-section="products" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Top Selling Products', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_top_selling)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-archive" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No product sales completed in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto;">
						<table class="omnify-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="border-bottom: 1px solid var(--omnify-gray-200); text-align: left;">
									<th style="padding: 10px 0;"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Units Sold', 'omnifywp-ecommerce'); ?></th>
									<th style="text-align: right;"><?php esc_html_e('Revenue', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_top_selling as $omnify_row) : 
									$omnify_prod_symbol = $omnify_currency_symbol($omnify_row['currency']);
								?>
									<tr style="border-bottom: 1px solid var(--omnify-gray-100);">
										<td style="padding: 12px 0;">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_row['product_id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--omnify-dark);">
												<?php echo esc_html($omnify_row['product_name']); ?>
											</a>
										</td>
										<td>
											<span style="font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; background: <?php echo esc_attr(($omnify_row['product_type'] ?? '') === 'physical' ? 'var(--omnify-blue-light); color: var(--omnify-blue);' : 'var(--omnify-green-light); color: var(--omnify-green);'); ?>;">
												<?php echo esc_html(ucfirst((string) ($omnify_row['product_type'] ?? 'digital'))); ?>
											</span>
										</td>
										<td><strong><?php echo esc_html($omnify_row['units_sold']); ?></strong></td>
										<td style="text-align: right; font-weight: 600;"><?php echo esc_html($omnify_prod_symbol . number_format($omnify_row['gross_revenue'], 2)); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- 2. Payment Methods -->
		<div class="omnify-card" data-analytics-section="finance" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Payment Methods', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_payment_breakdown)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-cart" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No payment data available in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<?php foreach ($omnify_payment_breakdown as $omnify_row) : ?>
						<div style="display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--omnify-gray-100);">
							<span style="font-weight:600;"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $omnify_row['payment_method']))); ?></span>
							<span><?php echo esc_html(number_format((int) $omnify_row['total'])); ?> &middot; <?php echo esc_html($omnify_fmt_price((float) $omnify_row['revenue'])); ?></span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- 3. Coupons Conversion Performance -->
		<div class="omnify-card" data-analytics-section="products" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Coupons Conversion Performance', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_coupons_perf)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-tag" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No coupon usages recorded in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto;">
						<table class="omnify-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="border-bottom: 1px solid var(--omnify-gray-200); text-align: left;">
									<th style="padding: 10px 0;"><?php esc_html_e('Coupon Code', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Usages', 'omnifywp-ecommerce'); ?></th>
									<th style="text-align: right;"><?php esc_html_e('Total Discount Given', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_coupons_perf as $omnify_row) : ?>
									<tr style="border-bottom: 1px solid var(--omnify-gray-100);">
										<td style="padding: 12px 0;">
											<span style="font-family: monospace; font-size: 12px; font-weight: 500; padding: 2px 6px; border-radius: 4px; background: var(--omnify-gray-100); border: 1px solid var(--omnify-gray-300); color: var(--omnify-dark);">
												<?php echo esc_html($omnify_row['coupon_code']); ?>
											</span>
										</td>
										<td><strong><?php echo esc_html($omnify_row['usage_count']); ?></strong> usages</td>
										<td style="text-align: right; font-weight: 600; color: var(--omnify-danger);">-<?php echo esc_html($omnify_fmt_price($omnify_row['total_discount'])); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- 4. Order Statuses -->
		<div class="omnify-card" data-analytics-section="finance" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Order Statuses', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_status_breakdown)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-update" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No order status data available in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<?php foreach ($omnify_status_breakdown as $omnify_row) : ?>
						<div style="display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--omnify-gray-100);">
							<span style="font-weight:600;"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $omnify_row['status']))); ?></span>
							<span><?php echo esc_html(number_format((int) $omnify_row['total'])); ?> &middot; <?php echo esc_html($omnify_fmt_price((float) $omnify_row['revenue'])); ?></span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- 5. Top Customers -->
		<div class="omnify-card" data-analytics-section="customers" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Top Customers', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_top_customers)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-groups" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No customer spending data for this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto;">
						<table class="omnify-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="border-bottom: 1px solid var(--omnify-gray-200); text-align: left;">
									<th style="padding: 10px 0;"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></th>
									<th style="text-align: right;"><?php esc_html_e('Spent', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_top_customers as $omnify_row) : ?>
									<tr style="border-bottom: 1px solid var(--omnify-gray-100);">
										<td style="padding: 12px 0;">
											<strong><?php echo esc_html(trim(($omnify_row['first_name'] ?? '') . ' ' . ($omnify_row['last_name'] ?? '')) ?: __('Guest Customer', 'omnifywp-ecommerce')); ?></strong>
											<div style="font-size: 11px; color: var(--omnify-gray-600);"><?php echo esc_html($omnify_row['email'] ?? ''); ?></div>
										</td>
										<td><?php echo esc_html(number_format((int) $omnify_row['orders_count'])); ?></td>
										<td style="text-align: right; font-weight: 600;"><?php echo esc_html($omnify_fmt_price((float) $omnify_row['total_spent'])); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- 6. Tax Reporting -->
		<div class="omnify-card" data-analytics-section="finance" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Tax Reporting', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_tax_breakdown)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-portfolio" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No tax collected in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto;">
						<table class="omnify-table" style="width: 100%;">
							<thead>
								<tr>
									<th><?php esc_html_e('Code', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Rate', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Mode', 'omnifywp-ecommerce'); ?></th>
									<th style="text-align: right;"><?php esc_html_e('Tax', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_tax_breakdown as $omnify_row) : ?>
									<tr>
										<td>
											<strong><?php echo esc_html($omnify_row['reporting_code']); ?></strong>
											<div style="font-size: 11px; color: var(--omnify-gray-600);"><?php echo esc_html($omnify_row['tax_label']); ?> &middot; <?php echo esc_html(number_format((int) $omnify_row['order_count'])); ?> <?php esc_html_e('orders', 'omnifywp-ecommerce'); ?></div>
										</td>
										<td><?php echo esc_html(number_format((float) $omnify_row['tax_rate'], 4)); ?>%</td>
										<td>
											<span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; background: var(--omnify-gray-100); color: var(--omnify-gray-700); font-size: 11px; font-weight: 500;">
												<?php echo ! empty($omnify_row['tax_inclusive']) ? esc_html__('Inclusive', 'omnifywp-ecommerce') : esc_html__('Exclusive', 'omnifywp-ecommerce'); ?>
											</span>
											<?php if (! empty($omnify_row['tax_shipping'])) : ?>
												<div style="font-size: 11px; color: var(--omnify-gray-600); margin-top: 4px;"><?php esc_html_e('Includes shipping tax', 'omnifywp-ecommerce'); ?></div>
											<?php endif; ?>
											<?php if (! empty($omnify_row['customer_tax_exempt'])) : ?>
												<div style="font-size: 11px; color: var(--omnify-gray-600); margin-top: 4px;"><?php esc_html_e('Tax exempt orders', 'omnifywp-ecommerce'); ?></div>
											<?php endif; ?>
										</td>
										<td style="text-align: right; font-weight: 500;"><?php echo esc_html($omnify_fmt_price((float) $omnify_row['tax_total'])); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- 7. Recent Orders in Range -->
		<div class="omnify-card" data-analytics-section="overview" style="padding: 0 !important; overflow: hidden;">
			<div class="omnify-card__header" style="padding: 16px 20px; border-bottom: 1px solid var(--omnify-gray-200);">
				<h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 15px; color: var(--omnify-dark); font-weight: 600;"><?php esc_html_e('Recent Orders in Range', 'omnifywp-ecommerce'); ?></h3>
			</div>
			<div style="padding: 20px; height: 100%;">
				<?php if (empty($omnify_recent_orders)) : ?>
					<div class="omnify-empty-state" style="padding: 40px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; height: 100%;">
						<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 12px; line-height: 1; color: var(--omnify-gray-400);"><span class="dashicons dashicons-clipboard" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
						<p style="margin: 0; color: var(--omnify-gray-600); font-size: 13px; font-weight: 500;"><?php esc_html_e('No orders placed in this date range.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-table-wrapper" style="overflow-x: auto;">
						<table class="omnify-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="border-bottom: 1px solid var(--omnify-gray-200); text-align: left;">
									<th style="padding: 10px 0;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Payment', 'omnifywp-ecommerce'); ?></th>
									<th style="text-align: right;"><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_recent_orders as $omnify_row) : ?>
									<tr style="border-bottom: 1px solid var(--omnify-gray-100);">
										<td style="padding: 12px 0;">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . $omnify_row['id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--omnify-green);">
												<?php echo esc_html($omnify_row['order_number'] ?: '#' . $omnify_row['id']); ?>
											</a>
										</td>
										<td>
											<div style="font-weight: 600; color: var(--omnify-dark);"><?php echo esc_html($omnify_row['first_name'] . ' ' . $omnify_row['last_name']); ?></div>
											<div style="font-size: 11px; color: var(--omnify-gray-600);"><?php echo esc_html($omnify_row['email']); ?></div>
										</td>
										<td>
											<span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--omnify-gray-800);">
												<?php echo esc_html(str_replace('_', ' ', $omnify_row['payment_method'])); ?>
											</span>
										</td>
										<td style="text-align: right; font-weight: 600;"><?php echo esc_html($omnify_fmt_price($omnify_row['total'])); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
