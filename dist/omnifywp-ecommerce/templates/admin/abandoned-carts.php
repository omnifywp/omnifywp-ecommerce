<?php
/**
 * Abandoned carts admin screen.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_ac_chips = $omnify_template_vars['omnify_ac_chips'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_cart = $omnify_template_vars['omnify_cart'] ?? null;
$omnify_carts = $omnify_template_vars['omnify_carts'] ?? null;
$omnify_paged = (int) ($omnify_template_vars['omnify_paged'] ?? 1);
$omnify_total_pages = (int) ($omnify_template_vars['omnify_total_pages'] ?? 1);
$omnify_total_carts = (int) ($omnify_template_vars['omnify_total_carts'] ?? (is_countable($omnify_carts) ? count($omnify_carts) : 0));
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_delete_url = $omnify_template_vars['omnify_delete_url'] ?? null;
$omnify_payload = $omnify_template_vars['omnify_payload'] ?? null;
$omnify_product_name = $omnify_template_vars['omnify_product_name'] ?? null;
$omnify_send_url = $omnify_template_vars['omnify_send_url'] ?? null;
$omnify_stats = $omnify_template_vars['omnify_stats'] ?? null;
$omnify_status = $omnify_template_vars['omnify_status'] ?? null;


$omnify_status = $omnify_status ?? '';
$omnify_stats  = $omnify_stats ?? [];
$omnify_carts  = $omnify_carts ?? [];
$omnify_settings = get_option('omnify_settings', []);
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
?>

<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'abandoned-carts'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Abandoned Carts', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Recover checkout sessions, send reminders, and see which carts became orders.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a class="omnify-button omnify-button--secondary" href="<?php echo esc_url(admin_url('admin.php?page=omnify-settings&section=checkout')); ?>">
				<span class="dashicons dashicons-admin-generic" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
				<?php esc_html_e('Recovery Settings', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<?php
	$omnify_message = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
	if (! empty($omnify_message)) :
		$omnify_msg_text = match ($omnify_message) {
			'bulk_trash'                => __('Selected carts moved to trash.', 'omnifywp-ecommerce'),
			'bulk_restore'              => __('Selected carts restored.', 'omnifywp-ecommerce'),
			'bulk_delete'               => __('Selected carts deleted permanently.', 'omnifywp-ecommerce'),
			'trashed'                   => __('Cart moved to trash.', 'omnifywp-ecommerce'),
			'restored'                  => __('Cart restored.', 'omnifywp-ecommerce'),
			'abandoned_cart_deleted'    => __('Cart deleted permanently.', 'omnifywp-ecommerce'),
			'abandoned_cart_email_sent' => __('Recovery email sent successfully.', 'omnifywp-ecommerce'),
			'abandoned_cart_not_found'  => __('Abandoned cart not found.', 'omnifywp-ecommerce'),
			'no_selection'              => __('No carts were selected.', 'omnifywp-ecommerce'),
			default                     => '',
		};
		if (! empty($omnify_msg_text)) :
	?>
		<div class="omnify-alert omnify-alert--success" style="margin-bottom: 16px; padding: 12px 16px; border-radius: 6px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
			<span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span>
			<span><?php echo esc_html($omnify_msg_text); ?></span>
		</div>
	<?php endif; endif; ?>

	<div class="omnify-subtabs-track">
		<div class="omnify-subtabs">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-subtab">
				<span class="dashicons dashicons-cart" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: text-bottom; margin-right: 5px;"></span>
				<?php esc_html_e('Orders List', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-subtab is-active">
				<span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: text-bottom; margin-right: 5px;"></span>
				<?php esc_html_e('Abandoned Carts', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<div class="omnify-dashboard-stats omnify-ac-stats-grid">
		<div class="omnify-dashboard-stat-card">
			<div class="omnify-stat-header">
				<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Captured', 'omnifywp-ecommerce'); ?></span>
				<span class="omnify-stat-icon omnify-stat-icon--captured"><span class="dashicons dashicons-cart"></span></span>
			</div>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['total'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<div class="omnify-stat-header">
				<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></span>
				<span class="omnify-stat-icon omnify-stat-icon--active"><span class="dashicons dashicons-clock"></span></span>
			</div>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['active'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<div class="omnify-stat-header">
				<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Emailed', 'omnifywp-ecommerce'); ?></span>
				<span class="omnify-stat-icon omnify-stat-icon--emailed"><span class="dashicons dashicons-email-alt"></span></span>
			</div>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['emailed'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<div class="omnify-stat-header">
				<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Recovered', 'omnifywp-ecommerce'); ?></span>
				<span class="omnify-stat-icon omnify-stat-icon--recovered"><span class="dashicons dashicons-yes-alt"></span></span>
			</div>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['recovered'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<div class="omnify-stat-header">
				<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Open Value', 'omnifywp-ecommerce'); ?></span>
				<span class="omnify-stat-icon omnify-stat-icon--value"><span class="dashicons dashicons-money-alt"></span></span>
			</div>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html($omnify_symbol . number_format((float) ($omnify_stats['value'] ?? 0), 2)); ?></p>
		</div>
	</div>

<?php
$omnify_filter_search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$omnify_filter_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
?>
	<!-- Abandoned Carts Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-abandoned-carts" />

		<!-- Row 1: Primary Filter Row -->
		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_cart"><?php esc_html_e('Search', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_cart" name="search" placeholder="<?php esc_attr_e('Search email or name...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Carts', 'omnifywp-ecommerce'); ?></option>
					<option value="active" <?php selected($omnify_filter_status, 'active'); ?>><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></option>
					<option value="recovered" <?php selected($omnify_filter_status, 'recovered'); ?>><?php esc_html_e('Recovered', 'omnifywp-ecommerce'); ?></option>
					<option value="trash" <?php selected($omnify_filter_status, 'trash'); ?>><?php esc_html_e('Trash', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search"></span>
					<span class="omnify-btn-label"><?php esc_html_e('Filter', 'omnifywp-ecommerce'); ?></span>
				</button>

				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<span class="omnify-btn-label"><?php esc_html_e('Reset', 'omnifywp-ecommerce'); ?></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active filter chips -->
	<?php
	$omnify_ac_chips = [];
	if ('' !== $omnify_filter_search) {
		$omnify_ac_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . $omnify_filter_search,
			'url'   => remove_query_arg('search'),
		];
	}
	if ('' !== $omnify_filter_status) {
		$omnify_ac_chips['status'] = [
			'label' => __('Status', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_filter_status),
			'url'   => remove_query_arg('status'),
		];
	}
	?>
	<?php if(!empty($omnify_ac_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500); margin-right:4px; font-weight:600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach($omnify_ac_chips as $omnify_chip): ?>
		<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
			<?php echo esc_html($omnify_chip['label']); ?>
			<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">×</a>
		</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="omnify-card omnify-abandoned-carts-card">
		<?php if (empty($omnify_carts)) : ?>
			<div class="omnify-empty-state" style="text-align: center; padding: 48px 24px;">
				<div class="omnify-empty-state__icon" style="width: 52px; height: 52px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
					<span class="dashicons dashicons-cart" style="font-size: 26px; width: 26px; height: 26px; color: #94a3b8;"></span>
				</div>
				<p style="color: #64748b; font-size: 14px; margin: 0;"><?php esc_html_e('No abandoned carts found.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-bulk-abandoned-carts-form">
				<?php wp_nonce_field('omnify_bulk_abandoned_carts', 'omnify_bulk_nonce'); ?>
				<input type="hidden" name="action" value="omnify_bulk_abandoned_carts" />

				<div class="omnify-table-toolbar">
					<div class="left">
						<input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" style="margin-right:8px;" />
						<span class="selected-count"><?php esc_html_e('0 selected', 'omnifywp-ecommerce'); ?></span>
						<div class="omnify-bulk-actions-wrapper" style="display: none; align-items: center; gap: 8px; margin-left: 16px;">
							<select name="bulk_action">
								<option value=""><?php esc_html_e('Bulk actions', 'omnifywp-ecommerce'); ?></option>
								<option value="trash"><?php esc_html_e('Move to Trash', 'omnifywp-ecommerce'); ?></option>
								<option value="restore"><?php esc_html_e('Restore', 'omnifywp-ecommerce'); ?></option>
								<option value="delete"><?php esc_html_e('Delete Permanently', 'omnifywp-ecommerce'); ?></option>
							</select>
							<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm"><?php esc_html_e('Apply', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>
				</div>

				<div class="omnify-table-wrapper">
					<table class="omnify-table">
						<thead>
							<tr>
								<th style="width: 38px; text-align: center;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
								<th style="min-width: 190px;"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
								<th style="min-width: 220px;"><?php esc_html_e('Cart', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 130px; white-space: nowrap;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 160px; white-space: nowrap;"><?php esc_html_e('Reminder', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 170px; white-space: nowrap;"><?php esc_html_e('Last Activity', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 160px; text-align: right; white-space: nowrap;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_carts as $omnify_cart) : ?>
								<?php
								$omnify_payload      = is_array($omnify_cart['cart_payload'] ?? null) ? $omnify_cart['cart_payload'] : [];
								$omnify_product_name = (string) ($omnify_payload['product_name'] ?? __('Unknown product', 'omnifywp-ecommerce'));
								$omnify_send_url     = wp_nonce_url(admin_url('admin-post.php?action=omnify_send_abandoned_cart_email&id=' . (int) $omnify_cart['id']), 'omnify_send_abandoned_cart_email_' . (int) $omnify_cart['id']);
								$omnify_delete_url   = wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_abandoned_cart&id=' . (int) $omnify_cart['id']), 'omnify_trash_abandoned_cart_' . (int) $omnify_cart['id']);
								$omnify_cust_name    = trim((string) (($omnify_cart['first_name'] ?? '') . ' ' . ($omnify_cart['last_name'] ?? '')));
								$omnify_cust_email   = (string) ($omnify_cart['email'] ?? '');
								$omnify_cart_status  = (string) ($omnify_cart['status'] ?? 'active');
								?>
								<tr>
									<td style="text-align: center;">
										<input type="checkbox" name="cart_ids[]" value="<?php echo (int) $omnify_cart['id']; ?>" class="omnify-checkbox" />
									</td>
									<td>
										<div class="omnify-ac-cust-cell">
											<strong class="omnify-ac-cust-name"><?php echo esc_html($omnify_cust_name ?: __('Guest Customer', 'omnifywp-ecommerce')); ?></strong>
											<?php if (! empty($omnify_cust_email)) : ?>
												<span class="omnify-ac-cust-email"><?php echo esc_html($omnify_cust_email); ?></span>
											<?php endif; ?>
										</div>
									</td>
									<td>
										<div class="omnify-ac-cart-cell">
											<strong class="omnify-ac-prod-name"><?php echo esc_html($omnify_product_name); ?></strong>
											<div class="omnify-ac-cart-meta">
												<span class="omnify-ac-cart-price"><?php echo esc_html(sprintf('%1$s %2$s', (string) ($omnify_cart['currency'] ?? 'USD'), number_format((float) ($omnify_cart['subtotal'] ?? 0), 2))); ?></span>
												<span class="omnify-ac-cart-qty">× <?php echo (int) ($omnify_cart['quantity'] ?? 1); ?></span>
											</div>
											<?php if (! empty($omnify_cart['coupon_code'])) : ?>
												<div class="omnify-cart-coupon-pill">
													<span class="dashicons dashicons-tag" style="font-size: 11px; width: 11px; height: 11px; line-height: 1;"></span>
													<?php
													/* translators: %s: Coupon code */
													echo esc_html(sprintf(__('Coupon: %s', 'omnifywp-ecommerce'), strtoupper((string) $omnify_cart['coupon_code']))); ?>
												</div>
											<?php endif; ?>
										</div>
									</td>
									<td style="white-space: nowrap;">
										<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_cart_status === 'recovered' ? 'completed' : ($omnify_cart_status === 'active' ? 'processing' : 'draft')); ?>">
											<?php echo esc_html(ucwords(str_replace('_', ' ', $omnify_cart_status))); ?>
										</span>
										<?php if (! empty($omnify_cart['recovered_order_id'])) : ?>
											<div style="margin-top: 4px;">
												<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . (int) $omnify_cart['recovered_order_id'])); ?>" class="omnify-recovered-order-link">
													<span class="dashicons dashicons-yes" style="font-size: 12px; width: 12px; height: 12px; vertical-align: middle;"></span>
													<?php
													/* translators: %d: Order ID */
													echo esc_html(sprintf(__('Order #%d', 'omnifywp-ecommerce'), (int) $omnify_cart['recovered_order_id'])); ?>
												</a>
											</div>
										<?php endif; ?>
									</td>
									<td style="white-space: nowrap;">
										<div class="omnify-reminder-cell">
											<span class="omnify-reminder-count">
												<span class="dashicons dashicons-email-alt" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle; margin-right: 3px;"></span>
												<?php 
												/* translators: %d: Email count */
												echo esc_html(sprintf(_n('%d email', '%d emails', (int) ($omnify_cart['reminder_count'] ?? 0), 'omnifywp-ecommerce'), (int) ($omnify_cart['reminder_count'] ?? 0))); ?>
											</span>
											<span class="omnify-reminder-time"><?php echo ! empty($omnify_cart['last_reminder_at']) ? esc_html(get_date_from_gmt((string) $omnify_cart['last_reminder_at'])) : esc_html__('Not sent', 'omnifywp-ecommerce'); ?></span>
										</div>
									</td>
									<td style="white-space: nowrap;">
										<span class="omnify-ac-date"><?php echo esc_html(get_date_from_gmt((string) ($omnify_cart['updated_at'] ?? ''))); ?></span>
									</td>
									<td style="text-align: right; white-space: nowrap;">
										<div class="omnify-row-actions" style="justify-content: flex-end;">
											<?php if (! empty($omnify_cart['recovery_url'])) : ?>
												<a class="omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url((string) $omnify_cart['recovery_url']); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Open Cart Link', 'omnifywp-ecommerce'); ?>" style="font-size: 11.5px; height: 32px; padding: 0 10px;">
													<span class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; line-height: 1; vertical-align: middle;"></span>
													<?php esc_html_e('Link', 'omnifywp-ecommerce'); ?>
												</a>
											<?php endif; ?>
											<a class="omnify-button omnify-button--primary omnify-button--sm" href="<?php echo esc_url($omnify_send_url); ?>" title="<?php esc_attr_e('Send recovery reminder email', 'omnifywp-ecommerce'); ?>" style="font-size: 11.5px; height: 32px; padding: 0 12px;">
												<span class="dashicons dashicons-email-alt" style="font-size: 12px; width: 12px; height: 12px; line-height: 1; vertical-align: middle;"></span>
												<?php esc_html_e('Send Email', 'omnifywp-ecommerce'); ?>
											</a>
											<a class="omnify-icon-btn omnify-icon-btn--danger" href="<?php echo esc_url($omnify_delete_url); ?>" title="<?php esc_attr_e('Move to trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Move to trash?', 'omnifywp-ecommerce')); ?>');">
												<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
											</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</form>
		<?php endif; ?>
		<?php if ($omnify_total_pages > 1) : ?>
			<div class="tablenav" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-top: 1px solid var(--omnify-gray-200, #e2e8f0);">
				<div class="tablenav-pages" style="display: flex; align-items: center; gap: 8px;">
					<span class="displaying-num" style="font-size: 13px; color: #64748b;">
						<?php
						/* translators: %s: total carts count */
						printf(esc_html(_n('%s cart', '%s carts', $omnify_total_carts, 'omnifywp-ecommerce')), number_format_i18n($omnify_total_carts));
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
	</div>
</div>


