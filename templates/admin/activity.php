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
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'activity'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Audit trail of administrative actions performed on this store.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<!-- Navigation Tabs -->
	<?php $omnify_active_tab = 'activity'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>

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

	<!-- Filters Card -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-activity" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_activity"><?php esc_html_e('Search Details', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_activity" name="search" value="<?php echo esc_attr($omnify_search); ?>" placeholder="<?php esc_attr_e('Search logs...', 'omnifywp-ecommerce'); ?>" />
			</div>

			<div class="omnify-filter-group">
				<label for="user_id"><?php esc_html_e('Actor', 'omnifywp-ecommerce'); ?></label>
				<select id="user_id" name="user_id">
					<option value=""><?php esc_html_e('All Actors', 'omnifywp-ecommerce'); ?></option>
					<?php foreach ($omnify_unique_actors as $omnify_id => $omnify_name) : ?>
						<option value="<?php echo esc_attr($omnify_id); ?>" <?php selected($omnify_user_id, $omnify_id); ?>><?php echo esc_html($omnify_name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="object_type"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></label>
				<select id="object_type" name="object_type">
					<option value=""><?php esc_html_e('All Categories', 'omnifywp-ecommerce'); ?></option>
					<option value="setting" <?php selected($omnify_object_type, 'setting'); ?>><?php esc_html_e('Settings', 'omnifywp-ecommerce'); ?></option>
					<option value="product" <?php selected($omnify_object_type, 'product'); ?>><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></option>
					<option value="coupon" <?php selected($omnify_object_type, 'coupon'); ?>><?php esc_html_e('Coupons', 'omnifywp-ecommerce'); ?></option>
					<option value="order" <?php selected($omnify_object_type, 'order'); ?>><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></option>
					<option value="customer" <?php selected($omnify_object_type, 'customer'); ?>><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></option>
					<option value="review" <?php selected($omnify_object_type, 'review'); ?>><?php esc_html_e('Reviews', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>

			<div class="omnify-filter-group">
				<label for="date_from"><?php esc_html_e('Date From', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_date_from); ?>" />
			</div>

			<div class="omnify-filter-group">
				<label for="date_to"><?php esc_html_e('Date To', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($omnify_date_to); ?>" />
			</div>

			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active filter chips -->
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
	<?php if(!empty($omnify_act_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500);margin-right:4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach($omnify_act_chips as $omnify_chip): ?>
		<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
			<?php echo esc_html($omnify_chip['label']); ?>
			<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">&times;</a>
		</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<!-- Activity Table Card -->
	<div class="omnify-card">
		<?php if (empty($omnify_activities)) : ?>
			<div class="omnify-empty-state" style="padding: 60px 20px; text-align: center;">
				<div class="omnify-empty-state__icon" style="font-size: 48px; margin-bottom: 16px; color: var(--omnify-gray-400);"><span class="dashicons dashicons-list-view" style="font-size: 48px; width: 48px; height: 48px;"></span></div>
				<h3 style="font-size: 18px; font-weight: 600; color: var(--shopify-gray-800); margin: 0 0 8px;"><?php esc_html_e('No activities logged', 'omnifywp-ecommerce'); ?></h3>
				<p style="color: var(--shopify-gray-600); margin: 0;"><?php esc_html_e('No administrative activities match the current filters.', 'omnifywp-ecommerce'); ?></p>
			</div>
		<?php else : ?>
			<div class="omnify-table-wrapper" style="overflow-x: auto;">
				<table class="omnify-table" style="width: 100%; border-collapse: collapse; text-align: left;">
					<thead>
						<tr style="border-bottom: 1px solid var(--shopify-gray-200);">
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('Date/Time', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('Actor', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('Category', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('Details', 'omnifywp-ecommerce'); ?></th>
							<th style="padding: 12px 16px; font-weight: 600; color: var(--shopify-gray-700);"><?php esc_html_e('IP Address', 'omnifywp-ecommerce'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($omnify_activities as $omnify_act) :
							// Category styles
							$omnify_cat_style = 'background: #f4f6f8; color: #454f5b;';
							switch ($omnify_act['object_type']) {
								case 'order':
									$omnify_cat_style = 'background: #e3f2fd; color: #0d47a1;'; // Teal/blue
									break;
								case 'product':
									$omnify_cat_style = 'background: #fff3e0; color: #e65100;'; // Orange
									break;
								case 'coupon':
									$omnify_cat_style = 'background: #efebe9; color: #4e342e;'; // Brown
									break;
								case 'setting':
									$omnify_cat_style = 'background: #ede7f6; color: #4a148c;'; // Purple
									break;
								case 'customer':
									$omnify_cat_style = 'background: #e8f5e9; color: #1b5e20;'; // Green
									break;
								case 'review':
									$omnify_cat_style = 'background: #fce4ec; color: #880e4f;'; // Pink
									break;
							}
							?>
							<tr style="border-bottom: 1px solid var(--shopify-gray-100);">
								<td style="padding: 12px 16px; color: var(--shopify-gray-700); font-size: 13px;">
									<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_act['created_at']))); ?>
								</td>
								<td style="padding: 12px 16px;">
									<div style="display: flex; align-items: center; gap: 8px;">
										<?php if (! empty($omnify_act['actor_email'])) : ?>
											<?php echo get_avatar($omnify_act['actor_email'], 24, '', '', ['extra_attr' => 'style="border-radius: 50%;"']); ?>
										<?php endif; ?>
										<div>
											<strong style="color: var(--shopify-gray-800); font-size: 13px;"><?php echo esc_html($omnify_act['actor_name'] ?: $omnify_act['actor_username'] ?: __('System', 'omnifywp-ecommerce')); ?></strong>
											<?php if (! empty($omnify_act['actor_username'])) : ?>
												<div style="font-size: 11px; color: var(--shopify-gray-500);">@<?php echo esc_html($omnify_act['actor_username']); ?></div>
											<?php endif; ?>
										</div>
									</div>
								</td>
								<td style="padding: 12px 16px;">
									<span style="display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; <?php echo esc_attr($omnify_cat_style); ?>">
										<?php echo esc_html($omnify_act['object_type']); ?>
									</span>
								</td>
								<td style="padding: 12px 16px; font-size: 13px; font-weight: 500; color: var(--shopify-gray-800);">
									<code><?php echo esc_html($omnify_act['action']); ?></code>
								</td>
								<td style="padding: 12px 16px; color: var(--shopify-gray-700); font-size: 13px; max-width: 320px; word-break: break-word;">
									<?php echo esc_html($omnify_act['description']); ?>
								</td>
								<td style="padding: 12px 16px; font-size: 12px; color: var(--shopify-gray-600);">
									<span title="<?php echo esc_attr($omnify_act['user_agent'] ?? ''); ?>" style="border-bottom: 1px dotted var(--shopify-gray-400); cursor: help;">
										<?php echo esc_html($omnify_act['ip_address'] ?: '—'); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<?php if ($omnify_total_pages > 1) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-top: 1px solid var(--shopify-gray-200); flex-wrap: wrap; gap: 12px;">
					<div style="font-size: 13px; color: var(--shopify-gray-600);">
						<?php
						$omnify_start_num = $omnify_offset + 1;
						$omnify_end_num   = min($omnify_offset + $omnify_per_page, $omnify_total_activities);
						// translators: %1$d: placeholder value, %2$d: placeholder value, %3$d: placeholder value.
						echo esc_html(sprintf(__('Showing %1$d to %2$d of %3$d activities', 'omnifywp-ecommerce'), $omnify_start_num, $omnify_end_num, $omnify_total_activities));
						?>
					</div>
					<div style="display: flex; gap: 6px;">
						<?php if ($omnify_page > 1) :
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$omnify_prev_query = array_merge($_GET, ['paged' => $omnify_page - 1]);
							?>
							<a href="<?php echo esc_url(add_query_arg($omnify_prev_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px;">Â« <?php esc_html_e('Prev', 'omnifywp-ecommerce'); ?></a>
						<?php endif; ?>

						<?php
						$omnify_range = 2;
						for ($omnify_i = 1; $omnify_i <= $omnify_total_pages; $omnify_i++) {
							if ($omnify_i == 1 || $omnify_i == $omnify_total_pages || ($omnify_i >= $omnify_page - $omnify_range && $omnify_i <= $omnify_page + $omnify_range)) {
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended
								$omnify_i_query = array_merge($_GET, ['paged' => $omnify_i]);
								$omnify_active_style = ($omnify_i === $omnify_page) ? 'background: var(--omnify-green); color: #fff; border-color: var(--omnify-green);' : '';
								?>
								<a href="<?php echo esc_url(add_query_arg($omnify_i_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px; min-width: 32px; justify-content: center; <?php echo esc_attr($omnify_active_style); ?>"><?php echo esc_html($omnify_i); ?></a>
								<?php
							} elseif ($omnify_i == 2 || $omnify_i == $omnify_total_pages - 1) {
								echo '<span style="padding: 6px; color: var(--shopify-gray-500);">...</span>';
							}
						}
						?>

						<?php if ($omnify_page < $omnify_total_pages) :
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$omnify_next_query = array_merge($_GET, ['paged' => $omnify_page + 1]);
							?>
							<a href="<?php echo esc_url(add_query_arg($omnify_next_query, admin_url('admin.php'))); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="text-decoration: none; padding: 6px 12px;"><?php esc_html_e('Next', 'omnifywp-ecommerce'); ?> Â»</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Danger Zone / Clear Log Card -->
	<div class="omnify-card" style="margin-top: 30px; border: 1px solid #ffcdd2; background: #fff8f8;">
		<h2 style="color: #c62828; margin-top: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
					<span class="dashicons dashicons-warning" style="color: #ef4444; margin-right: 4px;"></span><?php esc_html_e('Danger Zone: Clear Activity Logs', 'omnifywp-ecommerce'); ?>
		</h2>
		<p style="color: #5d4037; font-size: 13px; margin: 0 0 16px;">
			<?php esc_html_e('Clearing activity logs will permanently remove all audit trail records from the database. This action is irreversible.', 'omnifywp-ecommerce'); ?>
		</p>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php esc_attr_e('Are you absolutely sure you want to delete all activity log records? This cannot be undone.', 'omnifywp-ecommerce'); ?>');">
			<input type="hidden" name="action" value="omnify_clear_activity_log" />
			<?php wp_nonce_field('omnify_clear_activity_log', 'omnify_clear_activity_log_nonce'); ?>
			<button type="submit" class="omnify-button omnify-button--danger" style="background: #ef4444; border-color: #ef4444; color: #fff;">
				<?php esc_html_e('Clear Log', 'omnifywp-ecommerce'); ?>
			</button>
		</form>
	</div>
</div>
