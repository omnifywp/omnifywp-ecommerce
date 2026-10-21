<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_act = $omnify_template_vars['omnify_act'] ?? null;
$omnify_act_chips = $omnify_template_vars['omnify_act_chips'] ?? null;
$omnify_action_type = $omnify_template_vars['omnify_action_type'] ?? null;
$omnify_active_style = $omnify_template_vars['omnify_active_style'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_activities = $omnify_template_vars['omnify_activities'] ?? null;
$omnify_actor_name = $omnify_template_vars['omnify_actor_name'] ?? null;
$omnify_cat_style = $omnify_template_vars['omnify_cat_style'] ?? null;
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_date_from = $omnify_template_vars['omnify_date_from'] ?? null;
$omnify_date_label = $omnify_template_vars['omnify_date_label'] ?? null;
$omnify_date_to = $omnify_template_vars['omnify_date_to'] ?? null;
$omnify_end_num = $omnify_template_vars['omnify_end_num'] ?? null;
$omnify_i = $omnify_template_vars['omnify_i'] ?? null;
$omnify_i_query = $omnify_template_vars['omnify_i_query'] ?? null;
$omnify_id = $omnify_template_vars['omnify_id'] ?? null;
$omnify_name = $omnify_template_vars['omnify_name'] ?? null;
$omnify_next_query = $omnify_template_vars['omnify_next_query'] ?? null;
$omnify_object_type = $omnify_template_vars['omnify_object_type'] ?? null;
$omnify_offset = $omnify_template_vars['omnify_offset'] ?? null;
$omnify_page = $omnify_template_vars['omnify_page'] ?? null;
$omnify_per_page = $omnify_template_vars['omnify_per_page'] ?? null;
$omnify_prev_query = $omnify_template_vars['omnify_prev_query'] ?? null;
$omnify_range = $omnify_template_vars['omnify_range'] ?? null;
$omnify_search = $omnify_template_vars['omnify_search'] ?? null;
$omnify_start_num = $omnify_template_vars['omnify_start_num'] ?? null;
$omnify_total_activities = $omnify_template_vars['omnify_total_activities'] ?? null;
$omnify_total_pages = $omnify_template_vars['omnify_total_pages'] ?? null;
$omnify_unique_actors = $omnify_template_vars['omnify_unique_actors'] ?? null;
$omnify_user_id = $omnify_template_vars['omnify_user_id'] ?? null;

/**
 * Admin Activity Log Template
 *
 * @package Omnify
 */
$omnify_unique_actors    = is_array($omnify_unique_actors) ? $omnify_unique_actors : [];
$omnify_activities       = is_array($omnify_activities) ? $omnify_activities : [];
$omnify_total_activities = absint($omnify_total_activities ?? 0);
$omnify_total_pages      = max(1, absint($omnify_total_pages ?? 1));
$omnify_page             = max(1, absint($omnify_page ?? 1));
$omnify_per_page         = max(1, absint($omnify_per_page ?? 20));
$omnify_offset           = max(0, absint($omnify_offset ?? 0));

// Query filters
$omnify_search      = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$omnify_user_id     = isset($_GET['user_id']) ? absint(wp_unslash($_GET['user_id'])) : 0;
$omnify_action_type = isset($_GET['action_type']) ? sanitize_key(wp_unslash($_GET['action_type'])) : '';
$omnify_object_type = isset($_GET['object_type']) ? sanitize_key(wp_unslash($_GET['object_type'])) : '';
$omnify_date_from   = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
$omnify_date_to     = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';

// Whitelist pagination parameters so tampered query args cannot leak into pagination links.
$omnify_query_base = array_intersect_key(
	$_GET,
	array_flip(['search', 'user_id', 'action_type', 'object_type', 'date_from', 'date_to', 'page', 'paged'])
);

global $wpdb;
$omnify_act_table = $wpdb->prefix . 'omnify_admin_activities';
$omnify_24h_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$omnify_act_table} WHERE created_at >= NOW() - INTERVAL 24 HOUR");
$omnify_settings_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$omnify_act_table} WHERE object_type = 'setting'");

$omnify_webhooks_table = $wpdb->prefix . 'omnify_webhook_events';
$omnify_recent_webhooks = [];
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $omnify_webhooks_table)) === $omnify_webhooks_table) {
	$omnify_recent_webhooks = $wpdb->get_results("SELECT * FROM {$omnify_webhooks_table} ORDER BY id DESC LIMIT 25", ARRAY_A);
}
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'activity'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Audit trail of administrative actions performed on this store.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions" style="display: flex; align-items: center; gap: 8px;">
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline-flex; align-items: center; gap: 6px;">
				<?php wp_nonce_field('omnify_prune_activity_logs', 'omnify_prune_nonce'); ?>
				<input type="hidden" name="action" value="omnify_prune_activity_logs" />
				<select name="days" style="font-size: 12px; height: 32px; padding: 0 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
					<option value="30"><?php esc_html_e('Older than 30 days', 'omnifywp-ecommerce'); ?></option>
					<option value="60"><?php esc_html_e('Older than 60 days', 'omnifywp-ecommerce'); ?></option>
					<option value="90"><?php esc_html_e('Older than 90 days', 'omnifywp-ecommerce'); ?></option>
				</select>
				<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm" onclick="return confirm('<?php esc_attr_e('Are you sure you want to permanently prune old activity logs?', 'omnifywp-ecommerce'); ?>');">
					<span class="dashicons dashicons-trash" style="font-size: 13px; width: 13px; height: 13px; line-height: 1; vertical-align: middle;"></span>
					<?php esc_html_e('Prune Logs', 'omnifywp-ecommerce'); ?>
				</button>
			</form>
			<a href="<?php echo esc_url('https://omnifywp.com/doc/'); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
				<?php esc_html_e('Documentation', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php if (isset($_GET['message']) && 'pruned' === $_GET['message']) : ?>
		<div class="omnify-alert omnify-alert--success" style="margin-bottom: 16px; padding: 12px 16px; border-radius: 6px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
			<span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span>
			<span><?php printf(esc_html__('Successfully pruned %d old activity log entries.', 'omnifywp-ecommerce'), absint($_GET['count'] ?? 0)); ?></span>
		</div>
	<?php endif; ?>

	<!-- Navigation Tabs -->
	<?php $omnify_active_tab = 'activity'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs-track">
		<div class="omnify-subtabs">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-export')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-database-export" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Import/Export', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-api')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-network" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('API Keys', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-subtab is-active" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-tools')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-tools" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('System Tools & Seeding', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<!-- Top Guidance Hero Banner -->
	<div class="omnify-activity-hero-banner">
		<div class="omnify-activity-hero-icon">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
		</div>
		<div class="omnify-activity-hero-content">
			<h4><?php esc_html_e('Store Audit Trail & Administrative Activity Ledger', 'omnifywp-ecommerce'); ?></h4>
			<p><?php esc_html_e('Immutable security and operational log tracking catalog edits, order modifications, settings changes, and automated system jobs across all administrative sessions.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<!-- KPI Summary Stats Grid -->
	<div class="omnify-activity-stats-grid">
		<div class="omnify-activity-stat-card">
			<div class="omnify-activity-stat-icon omnify-activity-stat-icon--emerald">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
			</div>
			<div class="omnify-activity-stat-info">
				<div class="omnify-activity-stat-value"><?php echo number_format_i18n($omnify_total_activities); ?></div>
				<div class="omnify-activity-stat-label"><?php esc_html_e('Total Logged Events', 'omnifywp-ecommerce'); ?></div>
			</div>
		</div>

		<div class="omnify-activity-stat-card">
			<div class="omnify-activity-stat-icon omnify-activity-stat-icon--blue">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			</div>
			<div class="omnify-activity-stat-info">
				<div class="omnify-activity-stat-value"><?php echo number_format_i18n($omnify_24h_count); ?></div>
				<div class="omnify-activity-stat-label"><?php esc_html_e('Past 24 Hours', 'omnifywp-ecommerce'); ?></div>
			</div>
		</div>

		<div class="omnify-activity-stat-card">
			<div class="omnify-activity-stat-icon omnify-activity-stat-icon--purple">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
			</div>
			<div class="omnify-activity-stat-info">
				<div class="omnify-activity-stat-value"><?php echo number_format_i18n(count($omnify_unique_actors)); ?></div>
				<div class="omnify-activity-stat-label"><?php esc_html_e('Recorded Actors', 'omnifywp-ecommerce'); ?></div>
			</div>
		</div>

		<div class="omnify-activity-stat-card">
			<div class="omnify-activity-stat-icon omnify-activity-stat-icon--amber">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
			</div>
			<div class="omnify-activity-stat-info">
				<div class="omnify-activity-stat-value"><?php echo number_format_i18n($omnify_settings_count); ?></div>
				<div class="omnify-activity-stat-label"><?php esc_html_e('Settings Mutations', 'omnifywp-ecommerce'); ?></div>
			</div>
		</div>
	</div>

	<!-- Active Filter Chips Computation -->
	<?php
	$omnify_act_chips = [];
	if (!empty($omnify_search)) {
		$omnify_act_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . sanitize_text_field($omnify_search),
			'url'   => remove_query_arg('search')
		];
	}
	if (!empty($omnify_user_id)) {
		$omnify_actor_name = $omnify_unique_actors[$omnify_user_id] ?? ('#' . $omnify_user_id);
		$omnify_act_chips['user_id'] = [
			'label' => __('Actor', 'omnifywp-ecommerce') . ': ' . esc_html($omnify_actor_name),
			'url'   => remove_query_arg('user_id')
		];
	}
	if (!empty($omnify_object_type)) {
		$omnify_act_chips['object_type'] = [
			'label' => __('Category', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_object_type),
			'url'   => remove_query_arg('object_type')
		];
	}
	if (!empty($omnify_date_from) || !empty($omnify_date_to)) {
		$omnify_date_label = trim(($omnify_date_from ?? '') . ' - ' . ($omnify_date_to ?? ''));
		$omnify_act_chips['date'] = [
			'label' => __('Date', 'omnifywp-ecommerce') . ': ' . $omnify_date_label,
			'url'   => remove_query_arg(['date_from', 'date_to'])
		];
	}
	?>

	<!-- Activity Card with Unified Toolbar & Table -->
	<div class="omnify-card omnify-activity-card" style="padding: 0; overflow: hidden; border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
		<!-- Modern Filters Toolbar (Unified Inside Card) -->
		<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-activity-toolbar" style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; background: #ffffff; margin: 0;">
			<input type="hidden" name="page" value="omnify-activity" />

			<div class="omnify-filter-row" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
				<div class="omnify-filter-group" style="flex: 2 1 240px;">
					<label for="search_activity" style="font-weight: 600; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; display: block;"><?php esc_html_e('Search Details / Keywords', 'omnifywp-ecommerce'); ?></label>
					<input type="text" id="search_activity" name="search" value="<?php echo esc_attr($omnify_search); ?>" placeholder="<?php esc_attr_e('Search logs by keyword, ID, or description...', 'omnifywp-ecommerce'); ?>" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px; width: 100%;" />
				</div>

				<div class="omnify-filter-group" style="flex: 1 1 180px;">
					<label for="user_id" style="font-weight: 600; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; display: block;"><?php esc_html_e('Actor', 'omnifywp-ecommerce'); ?></label>
					<select id="user_id" name="user_id" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px; width: 100%;">
						<option value=""><?php esc_html_e('All Actors', 'omnifywp-ecommerce'); ?></option>
						<?php foreach ($omnify_unique_actors as $omnify_id => $omnify_name) : ?>
							<option value="<?php echo esc_attr($omnify_id); ?>" <?php selected($omnify_user_id, $omnify_id); ?>><?php echo esc_html($omnify_name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="omnify-filter-group" style="flex: 1 1 160px;">
					<label for="object_type" style="font-weight: 600; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; display: block;"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></label>
					<select id="object_type" name="object_type" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px; width: 100%;">
						<option value=""><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></option>
						<option value="setting" <?php selected($omnify_object_type, 'setting'); ?>><?php esc_html_e('Settings', 'omnifywp-ecommerce'); ?></option>
						<option value="product" <?php selected($omnify_object_type, 'product'); ?>><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></option>
						<option value="coupon" <?php selected($omnify_object_type, 'coupon'); ?>><?php esc_html_e('Coupons', 'omnifywp-ecommerce'); ?></option>
						<option value="order" <?php selected($omnify_object_type, 'order'); ?>><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></option>
						<option value="customer" <?php selected($omnify_object_type, 'customer'); ?>><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></option>
						<option value="system" <?php selected($omnify_object_type, 'system'); ?>><?php esc_html_e('System', 'omnifywp-ecommerce'); ?></option>
						<option value="review" <?php selected($omnify_object_type, 'review'); ?>><?php esc_html_e('Reviews', 'omnifywp-ecommerce'); ?></option>
					</select>
				</div>

				<div class="omnify-filter-group" style="flex: 1 1 140px;">
					<label for="date_from" style="font-weight: 600; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; display: block;"><?php esc_html_e('Date From', 'omnifywp-ecommerce'); ?></label>
					<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_date_from); ?>" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px; width: 100%;" />
				</div>

				<div class="omnify-filter-group" style="flex: 1 1 140px;">
					<label for="date_to" style="font-weight: 600; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; display: block;"><?php esc_html_e('Date To', 'omnifywp-ecommerce'); ?></label>
					<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($omnify_date_to); ?>" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px; width: 100%;" />
				</div>

				<div class="omnify-filter-actions" style="display: flex; gap: 8px;">
					<button type="submit" class="omnify-btn-apply-filters" style="height: 38px; padding: 0 16px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
						<span><?php esc_html_e('Filter', 'omnifywp-ecommerce'); ?></span>
					</button>
					<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-btn-clear-filters" style="height: 38px; padding: 0 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
						<span><?php esc_html_e('Reset', 'omnifywp-ecommerce'); ?></span>
					</a>
				</div>
			</div>

			<!-- Quick Presets Row -->
			<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
				<span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Quick Scope:', 'omnifywp-ecommerce'); ?></span>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; <?php echo (empty($omnify_object_type) && empty($omnify_date_from)) ? 'background: #059669; color: #ffffff;' : 'background: #f1f5f9; color: #475569;'; ?>">
					<?php esc_html_e('All Events', 'omnifywp-ecommerce'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg(['object_type' => 'setting'], admin_url('admin.php?page=omnify-activity'))); ?>" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; <?php echo ('setting' === $omnify_object_type) ? 'background: #059669; color: #ffffff;' : 'background: #f1f5f9; color: #475569;'; ?>">
					<?php esc_html_e('Settings Only', 'omnifywp-ecommerce'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg(['object_type' => 'product'], admin_url('admin.php?page=omnify-activity'))); ?>" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; <?php echo ('product' === $omnify_object_type) ? 'background: #059669; color: #ffffff;' : 'background: #f1f5f9; color: #475569;'; ?>">
					<?php esc_html_e('Products Only', 'omnifywp-ecommerce'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg(['object_type' => 'order'], admin_url('admin.php?page=omnify-activity'))); ?>" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; <?php echo ('order' === $omnify_object_type) ? 'background: #059669; color: #ffffff;' : 'background: #f1f5f9; color: #475569;'; ?>">
					<?php esc_html_e('Orders Only', 'omnifywp-ecommerce'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg(['date_from' => gmdate('Y-m-d')], admin_url('admin.php?page=omnify-activity'))); ?>" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; <?php echo ($omnify_date_from === gmdate('Y-m-d')) ? 'background: #059669; color: #ffffff;' : 'background: #f1f5f9; color: #475569;'; ?>">
					<?php esc_html_e('Today', 'omnifywp-ecommerce'); ?>
				</a>
			</div>

			<!-- Active Filter Chips (if any) -->
			<?php if(!empty($omnify_act_chips)): ?>
			<div class="omnify-active-filters" style="margin: 12px 0 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 12px; border-top: 1px solid #f1f5f9;">
				<span style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
				<?php foreach($omnify_act_chips as $omnify_chip): ?>
				<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #1e293b; font-weight: 600; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);">
					<?php echo esc_html($omnify_chip['label']); ?>
					<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 700; font-size: 14px; line-height: 1;">&times;</a>
				</span>
				<?php endforeach; ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" style="font-size: 12px; color: #dc2626; text-decoration: none; font-weight: 600; margin-left: 4px;"><?php esc_html_e('Clear All', 'omnifywp-ecommerce'); ?></a>
			</div>
			<?php endif; ?>
		</form>

		<?php if (empty($omnify_activities)) : ?>
			<div class="omnify-empty-state" style="padding: 64px 20px; text-align: center;">
				<div style="width: 52px; height: 52px; border-radius: 14px; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px;">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
				</div>
				<h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 6px;"><?php esc_html_e('No Activities Recorded', 'omnifywp-ecommerce'); ?></h3>
				<p style="color: #64748b; margin: 0; font-size: 13px; max-width: 420px; margin-inline: auto;"><?php esc_html_e('No administrative actions match your current search filters or date boundaries.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<div class="omnify-table-wrapper" style="overflow-x: auto; border: 0 !important; border-radius: 0 !important; box-shadow: none !important;">
				<table class="omnify-table" style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; border: 0 !important; border-radius: 0 !important; box-shadow: none !important;">
					<thead>
						<tr style="background: #f8fafc;">
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; width: 180px; border-bottom: 1px solid #e2e8f0;"><?php esc_html_e('Date / Timestamp', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; width: 170px;"><?php esc_html_e('Actor', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; width: 130px;"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; width: 150px;"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Event Details', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 14px 18px; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; width: 120px;"><?php esc_html_e('Origin IP', 'omnifywp-ecommerce'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($omnify_activities as $omnify_act) :
							$omnify_cat_class = 'omnify-act-cat-badge--setting';
							$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>';
							
							switch ($omnify_act['object_type']) {
								case 'order':
									$omnify_cat_class = 'omnify-act-cat-badge--order';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>';
									break;
								case 'product':
									$omnify_cat_class = 'omnify-act-cat-badge--product';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>';
									break;
								case 'coupon':
									$omnify_cat_class = 'omnify-act-cat-badge--coupon';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m20.59 13.41-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/></svg>';
									break;
								case 'customer':
									$omnify_cat_class = 'omnify-act-cat-badge--customer';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>';
									break;
								case 'system':
									$omnify_cat_class = 'omnify-act-cat-badge--system';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>';
									break;
								case 'review':
									$omnify_cat_class = 'omnify-act-cat-badge--review';
									$omnify_cat_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
									break;
							}

							$omnify_time_ts = strtotime($omnify_act['created_at']);
							$omnify_is_system = empty($omnify_act['user_id']) || 'System' === ($omnify_act['actor_name'] ?? '');
							?>
							<tr style="border-bottom: 1px solid #f1f5f9;">
								<td style="padding: 14px 18px; white-space: nowrap;">
									<div style="font-weight: 600; color: #0f172a; font-size: 13px;">
										<?php echo esc_html(date_i18n(get_option('date_format'), $omnify_time_ts)); ?>
									</div>
									<div style="font-size: 11.5px; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; margin-top: 2px;">
										<?php echo esc_html(date_i18n(get_option('time_format'), $omnify_time_ts)); ?>
									</div>
								</td>
								<td style="padding: 14px 18px;">
									<?php if ($omnify_is_system) : ?>
										<div style="display: inline-flex; align-items: center; gap: 8px;">
											<span style="width: 26px; height: 26px; border-radius: 6px; background: #f3e8ff; color: #7c3aed; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d8b4fe;">
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
											</span>
											<div>
												<strong style="color: #6b21a8; font-size: 13px; display: block; font-weight: 700;"><?php esc_html_e('System Worker', 'omnifywp-ecommerce'); ?></strong>
												<span style="font-size: 11px; color: #94a3b8;"><?php esc_html_e('automated', 'omnifywp-ecommerce'); ?></span>
											</div>
										</div>
									<?php else : ?>
										<div style="display: flex; align-items: center; gap: 10px;">
											<?php if (! empty($omnify_act['actor_email'])) : ?>
												<?php echo get_avatar($omnify_act['actor_email'], 28, '', '', ['extra_attr' => 'style="border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.06); flex-shrink: 0;"']); ?>
											<?php else : ?>
												<span style="width: 28px; height: 28px; border-radius: 50%; background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #a7f3d0; flex-shrink: 0;">
													<?php echo esc_html(strtoupper(substr($omnify_act['actor_name'] ?: 'U', 0, 1))); ?>
												</span>
											<?php endif; ?>
											<div>
												<strong style="color: #0f172a; font-size: 13px; display: block;"><?php echo esc_html($omnify_act['actor_name'] ?: $omnify_act['actor_username'] ?: __('Administrator', 'omnifywp-ecommerce')); ?></strong>
												<?php if (! empty($omnify_act['actor_username'])) : ?>
													<div style="font-size: 11px; color: #94a3b8;">@<?php echo esc_html($omnify_act['actor_username']); ?></div>
												<?php endif; ?>
											</div>
										</div>
									<?php endif; ?>
								</td>
								<td style="padding: 14px 18px;">
									<span class="omnify-act-cat-badge <?php echo esc_attr($omnify_cat_class); ?>">
										<?php echo $omnify_cat_icon; ?>
										<span><?php echo esc_html($omnify_act['object_type']); ?></span>
									</span>
								</td>
								<td style="padding: 14px 18px;">
									<code style="background: #f8fafc; color: #0f172a; padding: 4px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #e2e8f0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 600;">
										<?php echo esc_html($omnify_act['action']); ?>
									</code>
								</td>
								<td style="padding: 14px 18px; color: #334155; font-size: 13px; max-width: 340px; word-break: break-word; line-height: 1.5;">
									<?php echo esc_html($omnify_act['description']); ?>
								</td>
								<td style="padding: 14px 18px; font-size: 12px; color: #64748b; white-space: nowrap;">
									<span title="<?php echo esc_attr($omnify_act['user_agent'] ?? ''); ?>" style="display: inline-flex; align-items: center; gap: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 7px; border-radius: 4px; cursor: help;">
										<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8;"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
										<span><?php echo esc_html($omnify_act['ip_address'] ?: '—'); ?></span>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Pagination Bar -->
			<?php if ($omnify_total_pages > 1) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff; flex-wrap: wrap; gap: 12px;">
					<div style="font-size: 13px; color: #64748b; font-weight: 500;">
						<?php
						$omnify_start_num = $omnify_offset + 1;
						$omnify_end_num   = min($omnify_offset + $omnify_per_page, $omnify_total_activities);
						// translators: %1$d: placeholder value, %2$d: placeholder value, %3$d: placeholder value.
						echo esc_html(sprintf(__('Showing %1$d to %2$d of %3$d activities', 'omnifywp-ecommerce'), $omnify_start_num, $omnify_end_num, $omnify_total_activities));
						?>
					</div>
					<div style="display: flex; gap: 6px; align-items: center;">
						<?php if ($omnify_page > 1) :
							$omnify_prev_query = array_merge($omnify_query_base, ['paged' => $omnify_page - 1]);
							?>
							<a href="<?php echo esc_url(add_query_arg($omnify_prev_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px;">
								<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								<?php esc_html_e('Prev', 'omnifywp-ecommerce'); ?>
							</a>
						<?php endif; ?>

						<?php
						$omnify_range = 2;
						for ($omnify_i = 1; $omnify_i <= $omnify_total_pages; $omnify_i++) {
							if ($omnify_i == 1 || $omnify_i == $omnify_total_pages || ($omnify_i >= $omnify_page - $omnify_range && $omnify_i <= $omnify_page + $omnify_range)) {
								$omnify_i_query = array_merge($omnify_query_base, ['paged' => $omnify_i]);
								$omnify_active_style = ($omnify_i === $omnify_page) ? 'background: #059669; color: #ffffff; border-color: #059669; font-weight: 700;' : 'background: #ffffff; color: #334155; border-color: #cbd5e1;';
								?>
								<a href="<?php echo esc_url(add_query_arg($omnify_i_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px; min-width: 32px; justify-content: center; border-radius: 6px; <?php echo esc_attr($omnify_active_style); ?>"><?php echo esc_html($omnify_i); ?></a>
								<?php
							} elseif ($omnify_i == 2 || $omnify_i == $omnify_total_pages - 1) {
								echo '<span style="padding: 6px; color: #94a3b8;">...</span>';
							}
						}
						?>

						<?php if ($omnify_page < $omnify_total_pages) :
							$omnify_next_query = array_merge($omnify_query_base, ['paged' => $omnify_page + 1]);
							?>
							<a href="<?php echo esc_url(add_query_arg($omnify_next_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px;">
								<?php esc_html_e('Next', 'omnifywp-ecommerce'); ?>
								<span class="dashicons dashicons-arrow-right-alt2" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Payment Gateway Webhook Diagnostics & Event Replay -->
	<div class="omnify-card" style="margin-top: 32px; padding: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
		<div class="omnify-card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
			<div style="display: flex; align-items: center; gap: 12px;">
				<div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #dbeafe;">
					<span class="dashicons dashicons-rest-api" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
				</div>
				<div>
					<h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Payment Gateway Webhook Diagnostics & Event Replay', 'omnifywp-ecommerce'); ?></h2>
					<span style="font-size: 12.5px; color: #64748b;"><?php esc_html_e('Inspect incoming gateway callbacks and replay missed or failed payment events.', 'omnifywp-ecommerce'); ?></span>
				</div>
			</div>
			<span class="omnify-badge omnify-badge--info">
				<?php printf(esc_html__('%d Recorded Events', 'omnifywp-ecommerce'), count($omnify_recent_webhooks)); ?>
			</span>
		</div>

		<?php if (empty($omnify_recent_webhooks)) : ?>
			<div style="text-align: center; padding: 32px 20px; color: #64748b; font-size: 13.5px;">
				<span class="dashicons dashicons-yes-alt" style="font-size: 32px; width: 32px; height: 32px; color: #10b981; display: block; margin: 0 auto 10px;"></span>
				<?php esc_html_e('No gateway webhook events have been recorded yet. Live payment callbacks will appear here automatically.', 'omnifywp-ecommerce'); ?>
			</div>
		<?php else : ?>
			<div style="overflow-x: auto;">
				<table class="omnify-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
					<thead>
						<tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
							<th style="padding: 10px 14px; font-weight: 600; color: #475569;"><?php esc_html_e('Gateway', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 10px 14px; font-weight: 600; color: #475569;"><?php esc_html_e('Event ID / Reference', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 10px 14px; font-weight: 600; color: #475569;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 10px 14px; font-weight: 600; color: #475569;"><?php esc_html_e('Timestamp', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 10px 14px; font-weight: 600; color: #475569; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($omnify_recent_webhooks as $omnify_wh) : 
							$omnify_status_col = ('failed' === ($omnify_wh['status'] ?? '')) ? 'danger' : (('replayed' === ($omnify_wh['status'] ?? '')) ? 'info' : 'success');
						?>
							<tr style="border-bottom: 1px solid #f1f5f9;">
								<td style="padding: 10px 14px; font-weight: 600;">
									<span class="omnify-badge" style="text-transform: uppercase; font-size: 11px;">
										<?php echo esc_html($omnify_wh['gateway'] ?? 'unknown'); ?>
									</span>
								</td>
								<td style="padding: 10px 14px; font-family: monospace; color: #0f172a; word-break: break-all;">
									<?php echo esc_html($omnify_wh['event_id'] ?? ''); ?>
								</td>
								<td id="webhook-status-<?php echo esc_attr($omnify_wh['event_id']); ?>" style="padding: 10px 14px;">
									<span class="omnify-badge omnify-badge--<?php echo esc_attr($omnify_status_col); ?>">
										<?php echo esc_html($omnify_wh['status'] ?? 'processed'); ?>
									</span>
								</td>
								<td style="padding: 10px 14px; color: #64748b; font-size: 12px;">
									<?php echo esc_html(mysql2date('Y-m-d H:i:s', $omnify_wh['created_at'] ?? '')); ?>
								</td>
								<td style="padding: 10px 14px; text-align: right;">
									<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-replay-webhook" data-event-id="<?php echo esc_attr($omnify_wh['event_id']); ?>" data-gateway="<?php echo esc_attr($omnify_wh['gateway']); ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px;">
										<span class="dashicons dashicons-controls-repeat" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
										<?php esc_html_e('Replay Event', 'omnifywp-ecommerce'); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>

	<!-- Danger Zone / Clear Log Card -->
	<div class="omnify-card" style="margin-top: 32px; border: 1px solid #fecaca; background: #fff5f5; border-radius: 14px; padding: 24px;">
		<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
			<div style="flex: 1; min-width: 280px;">
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
					<h2 style="color: #991b1b; margin: 0; font-size: 16px; font-weight: 700;">
						<?php esc_html_e('Danger Zone: Purge Activity Logs', 'omnifywp-ecommerce'); ?>
					</h2>
				</div>
				<p style="color: #7f1d1d; font-size: 13px; margin: 0; line-height: 1.5;">
					<?php esc_html_e('Clearing activity logs will permanently remove all audit trail records from the database. This action is irreversible.', 'omnifywp-ecommerce'); ?>
				</p>
			</div>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php esc_attr_e('Are you absolutely sure you want to delete all activity log records? This cannot be undone.', 'omnifywp-ecommerce'); ?>');">
				<input type="hidden" name="action" value="omnify_clear_activity_log" />
				<?php wp_nonce_field('omnify_clear_activity_log', 'omnify_clear_activity_log_nonce'); ?>
				<button type="submit" class="omnify-activity-clear-btn">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
					<span><?php esc_html_e('Clear Activity Logs', 'omnifywp-ecommerce'); ?></span>
				</button>
			</form>
		</div>
	</div>
</div>
