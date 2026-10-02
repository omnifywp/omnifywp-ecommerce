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
				<?php esc_html_e('Recovery Settings', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs">
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" class="omnify-subtab"><?php esc_html_e('🛒 Orders List', 'omnifywp-ecommerce'); ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-subtab is-active"><?php esc_html_e('⏳ Abandoned Carts', 'omnifywp-ecommerce'); ?></a>
	</div>

	<div class="omnify-dashboard-stats" style="grid-template-columns: repeat(5, minmax(140px, 1fr)); margin-bottom: 16px;">
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Captured', 'omnifywp-ecommerce'); ?></span>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['total'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></span>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['active'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Emailed', 'omnifywp-ecommerce'); ?></span>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['emailed'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Recovered', 'omnifywp-ecommerce'); ?></span>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html((string) ($omnify_stats['recovered'] ?? 0)); ?></p>
		</div>
		<div class="omnify-dashboard-stat-card">
			<span class="omnify-dashboard-stat-card__label"><?php esc_html_e('Open Value', 'omnifywp-ecommerce'); ?></span>
			<p class="omnify-dashboard-stat-card__value"><?php echo esc_html(number_format((float) ($omnify_stats['value'] ?? 0), 2)); ?></p>
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
				<button type="submit" class="omnify-btn-apply-filters">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>

				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-abandoned-carts')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
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

	<div class="omnify-card">

		<div class="omnify-table-wrapper">
			<table class="omnify-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Cart', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Reminder', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Last Activity', 'omnifywp-ecommerce'); ?></th>
						<th><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($omnify_carts)) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e('No abandoned carts found yet.', 'omnifywp-ecommerce'); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ($omnify_carts as $omnify_cart) : ?>
							<?php
							$omnify_payload      = is_array($omnify_cart['cart_payload'] ?? null) ? $omnify_cart['cart_payload'] : [];
							$omnify_product_name = (string) ($omnify_payload['product_name'] ?? __('Unknown product', 'omnifywp-ecommerce'));
							$omnify_send_url     = wp_nonce_url(admin_url('admin-post.php?action=omnify_send_abandoned_cart_email&id=' . (int) $omnify_cart['id']), 'omnify_send_abandoned_cart_email_' . (int) $omnify_cart['id']);
							$omnify_delete_url   = wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_abandoned_cart&id=' . (int) $omnify_cart['id']), 'omnify_trash_abandoned_cart_' . (int) $omnify_cart['id']);
							?>
							<tr>
								<td>
									<strong><?php echo esc_html(trim((string) (($omnify_cart['first_name'] ?? '') . ' ' . ($omnify_cart['last_name'] ?? ''))) ?: __('Guest customer', 'omnifywp-ecommerce')); ?></strong><br />
									<span><?php echo esc_html((string) ($omnify_cart['email'] ?? '')); ?></span>
								</td>
								<td>
									<strong><?php echo esc_html($omnify_product_name); ?></strong><br />
									<span><?php echo esc_html(sprintf('%1$s %2$s x %3$d', (string) ($omnify_cart['currency'] ?? 'USD'), number_format((float) ($omnify_cart['subtotal'] ?? 0), 2), (int) ($omnify_cart['quantity'] ?? 1))); ?></span>
									<?php if (! empty($omnify_cart['coupon_code'])) : ?>
										<br /><span><?php
										// translators: %s: placeholder value. echo esc_html(sprintf(__('Coupon: %s', 'omnifywp-ecommerce'), strtoupper((string) $cart['coupon_code']))); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<span class="omnify-badge omnify-badge--<?php echo esc_attr((string) ($omnify_cart['status'] ?? 'active')); ?>">
										<?php echo esc_html(ucwords(str_replace('_', ' ', (string) ($omnify_cart['status'] ?? 'active')))); ?>
									</span>
									<?php if (! empty($omnify_cart['recovered_order_id'])) : ?>
										<br /><a href="<?php
										// translators: %d: placeholder value. echo esc_url(admin_url('admin.php?page=omnify-orders&action=view&id=' . (int) $cart['recovered_order_id'])); ?>"><?php
										// translators: %d: placeholder value. echo esc_html(sprintf(__('Order #%d', 'omnifywp-ecommerce'), (int) $cart['recovered_order_id'])); ?></a>
									<?php endif; ?>
								</td>
								<td>
									<?php 
									// translators: %d: placeholder value.
									echo esc_html(sprintf(_n('%d email', '%d emails', (int) ($omnify_cart['reminder_count'] ?? 0), 'omnifywp-ecommerce'), (int) ($omnify_cart['reminder_count'] ?? 0))); ?><br />
									<span><?php echo ! empty($omnify_cart['last_reminder_at']) ? esc_html(get_date_from_gmt((string) $omnify_cart['last_reminder_at'])) : esc_html__('Not sent', 'omnifywp-ecommerce'); ?></span>
								</td>
								<td>
									<?php echo esc_html(get_date_from_gmt((string) ($omnify_cart['updated_at'] ?? ''))); ?>
								</td>
								<td>
									<div class="omnify-action-stack">
										<?php if (! empty($omnify_cart['recovery_url'])) : ?>
											<a class="omnify-button omnify-button--secondary" href="<?php echo esc_url((string) $omnify_cart['recovery_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Link', 'omnifywp-ecommerce'); ?></a>
										<?php endif; ?>
										<a class="omnify-button" href="<?php echo esc_url($omnify_send_url); ?>"><?php esc_html_e('Send Email', 'omnifywp-ecommerce'); ?></a>
										<a class="omnify-icon-btn omnify-icon-btn--danger" href="<?php echo esc_url($omnify_delete_url); ?>" title="<?php esc_attr_e('Move to trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php echo esc_js(__('Move to trash?', 'omnifywp-ecommerce')); ?>');">
											<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
										</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>


