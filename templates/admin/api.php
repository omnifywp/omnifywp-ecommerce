<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_active_section = $omnify_template_vars['omnify_active_section'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_allowed_sections = $omnify_template_vars['omnify_allowed_sections'] ?? null;
$omnify_api_keys = $omnify_template_vars['omnify_api_keys'] ?? null;
$omnify_client_id = $omnify_template_vars['omnify_client_id'] ?? null;
$omnify_key = $omnify_template_vars['omnify_key'] ?? null;
$omnify_owner = $omnify_template_vars['omnify_owner'] ?? null;
$omnify_owner_name = $omnify_template_vars['omnify_owner_name'] ?? null;
$omnify_paypal_enabled = $omnify_template_vars['omnify_paypal_enabled'] ?? null;
$omnify_paypal_mode = $omnify_template_vars['omnify_paypal_mode'] ?? null;
$omnify_section = $omnify_template_vars['omnify_section'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_stripe_enabled = $omnify_template_vars['omnify_stripe_enabled'] ?? null;
$omnify_user = $omnify_template_vars['omnify_user'] ?? null;
$omnify_users = $omnify_template_vars['omnify_users'] ?? null;

/**
 * Admin API & Connection Diagnostics Template
 *
 * @package Omnify
 */
$omnify_api_keys = get_option('omnify_api_keys', []);
if (! is_array($omnify_settings)) {
	$omnify_settings = get_option('omnify_settings', []);
}

// Retrieve administrative users for key ownership selection
$omnify_users = get_users([
	'role__in' => ['administrator'],
	'fields'   => ['ID', 'user_login', 'display_name'],
]);
if (empty($omnify_users)) {
	$omnify_users = get_users([
		'fields' => ['ID', 'user_login', 'display_name'],
		'number' => 20,
	]);
}

global $wpdb;
$omnify_webhooks_list = \Omnify\eCommerce\Support\Omnify_DB::get_results(
	$wpdb,
	"SELECT * FROM {$wpdb->prefix}omnify_webhooks ORDER BY id DESC",
	ARRAY_A
) ?: [];

$omnify_active_section = isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : 'keys';
$omnify_allowed_sections = ['keys', 'webhooks', 'payments', 'checker'];
if (! in_array($omnify_active_section, $omnify_allowed_sections, true)) {
	$omnify_active_section = 'keys';
}

function omnify_api_page_url(string $omnify_section): string {
	return esc_url(admin_url('admin.php?page=omnify-api&section=' . $omnify_section));
}
?>
<div class="omnify-admin-wrapper omnify-api-page">
	<?php $omnify_active_tab = 'api'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('API & Diagnostics', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Manage REST API access keys, test third-party integrations, and monitor latency.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url('https://omnifywp.com/doc/'); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
				<?php esc_html_e('Documentation', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<div class="omnify-subtabs-track">
		<div class="omnify-subtabs">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-export')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-database-export" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Import/Export', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-api')); ?>" class="omnify-subtab is-active" style="display: inline-flex; align-items: center; gap: 6px;">
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
	</div>

	<!-- Top Guidance Hero Banner -->
	<div class="omnify-api-hero-banner">
		<div class="omnify-api-hero-icon">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17l6-6-6-6"/><path d="M12 19h8"/></svg>
		</div>
		<div class="omnify-api-hero-content">
			<h4><?php esc_html_e('API & Developer Diagnostics Console', 'omnifywp-ecommerce'); ?></h4>
			<p><?php esc_html_e('Provision secure authentication keys, configure outbound webhook subscribers, verify real-time payment gateway health, and inspect server network latency.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<div class="omnify-api-layout">
		<!-- Left: Sidebar Navigation -->
		<nav class="omnify-api-nav-card">
			<div class="omnify-api-nav-header"><?php esc_html_e('Sections', 'omnifywp-ecommerce'); ?></div>
			
			<a href="<?php echo esc_url(omnify_api_page_url('keys')); ?>" class="omnify-api-nav-item <?php echo 'keys' === $omnify_active_section ? 'is-active' : ''; ?>">
				<span class="omnify-api-nav-item-left">
					<span class="omnify-api-nav-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-6 6 7.5-7.5a2.12 2.12 0 0 1 3 3L16 8.5m-5.5 5.5-4 4a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l4-4m2.5-2.5L12 12m-6 6-2 2"/></svg>
					</span>
					<span><?php esc_html_e('REST API Keys', 'omnifywp-ecommerce'); ?></span>
				</span>
				<span class="omnify-api-nav-badge"><?php echo count($omnify_api_keys); ?></span>
			</a>

			<a href="<?php echo esc_url(omnify_api_page_url('webhooks')); ?>" class="omnify-api-nav-item <?php echo 'webhooks' === $omnify_active_section ? 'is-active' : ''; ?>">
				<span class="omnify-api-nav-item-left">
					<span class="omnify-api-nav-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8c0-2.2-1.8-4-4-4-1.8 0-3.3 1.2-3.8 2.8M6 12c0 2.2 1.8 4 4 4 1.8 0 3.3-1.2 3.8-2.8"/><circle cx="18" cy="8" r="2"/><circle cx="6" cy="12" r="2"/></svg>
					</span>
					<span><?php esc_html_e('Outbound Webhooks', 'omnifywp-ecommerce'); ?></span>
				</span>
				<span class="omnify-api-nav-badge"><?php echo count($omnify_webhooks_list); ?></span>
			</a>

			<a href="<?php echo esc_url(omnify_api_page_url('payments')); ?>" class="omnify-api-nav-item <?php echo 'payments' === $omnify_active_section ? 'is-active' : ''; ?>">
				<span class="omnify-api-nav-item-left">
					<span class="omnify-api-nav-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
					</span>
					<span><?php esc_html_e('Payment Status', 'omnifywp-ecommerce'); ?></span>
				</span>
				<span class="omnify-api-nav-badge">2</span>
			</a>

			<a href="<?php echo esc_url(omnify_api_page_url('checker')); ?>" class="omnify-api-nav-item <?php echo 'checker' === $omnify_active_section ? 'is-active' : ''; ?>">
				<span class="omnify-api-nav-item-left">
					<span class="omnify-api-nav-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
					</span>
					<span><?php esc_html_e('URL Checker', 'omnifywp-ecommerce'); ?></span>
				</span>
			</a>
		</nav>

		<!-- Right: Main Content Panel -->
		<div class="omnify-api-content">

			<!-- ══ SECTION 1: API KEYS ═══════════════════════════════════════════════ -->
			<?php if ('keys' === $omnify_active_section) : ?>
				<div class="omnify-api-card">
					<div class="omnify-api-section-header">
						<div class="omnify-api-section-header-left">
							<h2><?php esc_html_e('REST API Keys', 'omnifywp-ecommerce'); ?></h2>
							<p><?php esc_html_e('Generate authenticated credentials for external client applications, ERP synchronizers, and headless mobile storefronts.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<span class="omnify-api-section-chip"><?php esc_html_e('REST v1 • HMAC', 'omnifywp-ecommerce'); ?></span>
					</div>

					<!-- Key Generator Subcard -->
					<div class="omnify-api-subform-card">
						<h3 class="omnify-api-subform-title">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><path d="m21 2-2 2m-6 6 7.5-7.5a2.12 2.12 0 0 1 3 3L16 8.5m-5.5 5.5-4 4a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l4-4m2.5-2.5L12 12m-6 6-2 2"/></svg>
							<span><?php esc_html_e('Generate New API Key', 'omnifywp-ecommerce'); ?></span>
						</h3>
						<form id="omnify-create-key-form">
							<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
								<div>
									<label for="key-desc" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Description / App Name', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="key-desc" name="description" placeholder="<?php esc_attr_e('e.g. Mobile POS, ERP Sync, Analytics', 'omnifywp-ecommerce'); ?>" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;" />
								</div>

								<div>
									<label for="key-user" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Authorized User', 'omnifywp-ecommerce'); ?></label>
									<select id="key-user" name="user_id" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;">
										<?php foreach ($omnify_users as $omnify_user) : ?>
											<option value="<?php echo esc_attr($omnify_user->ID); ?>"><?php echo esc_html($omnify_user->display_name . ' (' . $omnify_user->user_login . ')'); ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div>
									<label for="key-perms" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Permissions Scope', 'omnifywp-ecommerce'); ?></label>
									<select id="key-perms" name="permissions" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;">
										<option value="read"><?php esc_html_e('Read-Only (GET)', 'omnifywp-ecommerce'); ?></option>
										<option value="write"><?php esc_html_e('Write-Only (POST/PUT/DELETE)', 'omnifywp-ecommerce'); ?></option>
										<option value="read_write"><?php esc_html_e('Read & Write (Full Access)', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>
							</div>

							<div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
								<button type="submit" class="omnify-button omnify-button--primary" id="omnify-generate-key-btn" style="height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 20px; font-weight: 700; border-radius: 8px;">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-6 6 7.5-7.5a2.12 2.12 0 0 1 3 3L16 8.5m-5.5 5.5-4 4a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l4-4m2.5-2.5L12 12m-6 6-2 2"/></svg>
									<span><?php esc_html_e('Generate API Key', 'omnifywp-ecommerce'); ?></span>
								</button>
							</div>
						</form>

						<!-- Dynamic Credentials Notification Modal -->
						<div id="omnify-credentials-overlay" style="display: none; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px; margin-top: 20px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
								<span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background: #d1fae5; color: #059669;">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
								</span>
								<h4 style="margin: 0; color: #065f46; font-size: 15px; font-weight: 700;">
									<?php esc_html_e('API Credentials Generated Successfully', 'omnifywp-ecommerce'); ?>
								</h4>
							</div>
							<p style="margin: 0 0 16px; font-size: 13px; color: #047857; line-height: 1.5;">
								<?php esc_html_e('Copy these keys now. For security purposes, the Consumer Secret cannot be viewed again once you leave or refresh this page.', 'omnifywp-ecommerce'); ?>
							</p>

							<div class="omnify-form-row" style="margin-bottom: 12px;">
								<label style="font-weight: 600; font-size: 12px; color: #065f46; display: block; margin-bottom: 4px;"><?php esc_html_e('Consumer Key', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; gap: 8px;">
									<input type="text" id="generated-client-key" readonly style="flex: 1; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #ffffff; border: 1px solid #a7f3d0; border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #0f172a;" />
									<button type="button" class="omnify-button omnify-button--secondary copy-credential-btn" data-target="generated-client-key"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
								</div>
							</div>

							<div class="omnify-form-row" style="margin-bottom: 18px;">
								<label style="font-weight: 600; font-size: 12px; color: #065f46; display: block; margin-bottom: 4px;"><?php esc_html_e('Consumer Secret', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; gap: 8px;">
									<input type="text" id="generated-client-secret" readonly style="flex: 1; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #ffffff; border: 1px solid #a7f3d0; border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #0f172a;" />
									<button type="button" class="omnify-button omnify-button--secondary copy-credential-btn" data-target="generated-client-secret"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
								</div>
							</div>

							<button type="button" class="omnify-button omnify-button--primary" id="close-credentials-btn" style="border-radius: 8px; font-weight: 600;">
								<?php esc_html_e('I have securely stored these keys', 'omnifywp-ecommerce'); ?>
							</button>
						</div>
					</div>

					<!-- Existing Keys Listing -->
					<div>
						<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
							<h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Active API Credentials', 'omnifywp-ecommerce'); ?></h3>
							<span style="font-size: 12px; color: #64748b;"><?php printf(esc_html__('%d key(s) provisioned', 'omnifywp-ecommerce'), count($omnify_api_keys)); ?></span>
						</div>
						
						<?php if (empty($omnify_api_keys)) : ?>
							<div class="omnify-empty-state" style="text-align: center; padding: 48px 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
								<div style="width: 44px; height: 44px; border-radius: 12px; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
									<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-6 6 7.5-7.5a2.12 2.12 0 0 1 3 3L16 8.5m-5.5 5.5-4 4a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l4-4m2.5-2.5L12 12m-6 6-2 2"/></svg>
								</div>
								<h4 style="margin: 0 0 4px; font-size: 14.5px; font-weight: 700; color: #0f172a;"><?php esc_html_e('No API Keys Configured Yet', 'omnifywp-ecommerce'); ?></h4>
								<p style="margin: 0; color: #64748b; font-size: 13px;"><?php esc_html_e('Generate your first key above to connect external services or third-party platforms.', 'omnifywp-ecommerce'); ?></p>
							</div>
						<?php else : ?>
							<div class="omnify-table-wrapper" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
								<table class="omnify-table" style="width: 100%; border-collapse: collapse; text-align: left;">
									<thead>
										<tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Description', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Authorized User', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Consumer Key', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Scope', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Created', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Last Used', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; text-align: right;"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($omnify_api_keys as $omnify_key) : 
											$omnify_owner = get_userdata($omnify_key['user_id']);
											$omnify_owner_name = $omnify_owner ? $omnify_owner->display_name : __('Unknown User', 'omnifywp-ecommerce');
										?>
											<tr id="api-key-row-<?php echo esc_attr($omnify_key['id']); ?>" style="border-bottom: 1px solid #f1f5f9;">
												<td style="padding: 14px 16px; font-weight: 600; color: #0f172a; font-size: 13.5px;">
													<?php echo esc_html($omnify_key['description']); ?>
												</td>
												<td style="padding: 14px 16px; color: #334155; font-size: 13px;">
													<span style="display: inline-flex; align-items: center; gap: 6px;">
														<span style="width: 22px; height: 22px; border-radius: 50%; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">
															<?php echo esc_html(strtoupper(substr($omnify_owner_name, 0, 1))); ?>
														</span>
														<span><?php echo esc_html($omnify_owner_name); ?></span>
													</span>
												</td>
												<td style="padding: 14px 16px;">
													<code style="background: #f1f5f9; padding: 3px 8px; border-radius: 6px; font-size: 12px; color: #0f172a; border: 1px solid #e2e8f0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">ck_...<?php echo esc_html($omnify_key['consumer_key_last4']); ?></code>
												</td>
												<td style="padding: 14px 16px;">
													<?php 
													if ('read_write' === $omnify_key['permissions']) {
														echo '<span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11.5px; padding: 3px 9px; border-radius: 999px; font-weight: 600;">' . esc_html__('Read & Write', 'omnifywp-ecommerce') . '</span>';
													} elseif ('write' === $omnify_key['permissions']) {
														echo '<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11.5px; padding: 3px 9px; border-radius: 999px; font-weight: 600;">' . esc_html__('Write-Only', 'omnifywp-ecommerce') . '</span>';
													} else {
														echo '<span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11.5px; padding: 3px 9px; border-radius: 999px; font-weight: 600;">' . esc_html__('Read-Only', 'omnifywp-ecommerce') . '</span>';
													}
													?>
												</td>
												<td style="padding: 14px 16px; font-size: 13px; color: #64748b;"><?php echo esc_html(mysql2date(get_option('date_format'), $omnify_key['created_at'])); ?></td>
												<td class="last-used-cell" style="padding: 14px 16px; font-size: 13px; color: #64748b;">
													<?php echo $omnify_key['last_used'] ? esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $omnify_key['last_used'])) : '<span style="color: #94a3b8;">' . esc_html__('Never', 'omnifywp-ecommerce') . '</span>'; ?>
												</td>
												<td style="padding: 14px 16px; text-align: right;">
													<button type="button" class="omnify-button omnify-button--danger revoke-key-btn" data-key-id="<?php echo esc_attr($omnify_key['id']); ?>" style="padding: 5px 12px; font-size: 12px; height: 30px; border-radius: 6px;">
														<?php esc_html_e('Revoke', 'omnifywp-ecommerce'); ?>
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- ══ SECTION 2: OUTBOUND WEBHOOKS ═════════════════════════════════════════ -->
			<?php if ('webhooks' === $omnify_active_section) : ?>
				<div class="omnify-api-card">
					<div class="omnify-api-section-header">
						<div class="omnify-api-section-header-left">
							<h2><?php esc_html_e('Outbound Webhooks', 'omnifywp-ecommerce'); ?></h2>
							<p><?php esc_html_e('Dispatch automated real-time JSON payloads to external endpoints when store activities occur. Deliveries include HMAC-SHA256 signature headers for payload verification.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<span class="omnify-api-section-chip"><?php esc_html_e('HTTP POST • JSON', 'omnifywp-ecommerce'); ?></span>
					</div>

					<!-- Webhook Creation Subcard -->
					<div class="omnify-api-subform-card">
						<h3 class="omnify-api-subform-title">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><path d="M12 5v14M5 12h14"/></svg>
							<span><?php esc_html_e('Register Webhook Subscriber', 'omnifywp-ecommerce'); ?></span>
						</h3>
						<form id="omnify-create-webhook-form">
							<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
								<div>
									<label for="webhook-name" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Subscriber Name', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="webhook-name" name="name" placeholder="<?php esc_attr_e('e.g. ERP Sync, Zapier, Slack Bot', 'omnifywp-ecommerce'); ?>" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;" />
								</div>
								<div>
									<label for="webhook-url" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Payload Delivery URL', 'omnifywp-ecommerce'); ?></label>
									<input type="url" id="webhook-url" name="url" placeholder="https://api.yourdomain.com/webhook" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;" />
								</div>
								<div>
									<label for="webhook-secret" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Secret Key (Optional)', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="webhook-secret" name="secret" placeholder="<?php esc_attr_e('Leave blank to auto-generate', 'omnifywp-ecommerce'); ?>" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; background: #ffffff;" />
								</div>
							</div>

							<div style="margin-bottom: 22px;">
								<label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 13px; color: #1e293b;"><?php esc_html_e('Trigger Topics (Events to Stream)', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; flex-wrap: wrap; gap: 10px;">
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="order.created" checked />
										<code>order.created</code>
									</label>
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="order.status_changed" checked />
										<code>order.status_changed</code>
									</label>
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="order.completed" checked />
										<code>order.completed</code>
									</label>
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="order.refunded" />
										<code>order.refunded</code>
									</label>
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="order.cancelled" />
										<code>order.cancelled</code>
									</label>
									<label class="omnify-event-pill">
										<input type="checkbox" name="webhook_events[]" value="customer.created" />
										<code>customer.created</code>
									</label>
								</div>
							</div>

							<div style="display: flex; align-items: center; justify-content: flex-end;">
								<button type="submit" class="omnify-button omnify-button--primary" id="omnify-create-webhook-btn" style="height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 20px; font-weight: 700; border-radius: 8px;">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
									<span><?php esc_html_e('Register Webhook', 'omnifywp-ecommerce'); ?></span>
								</button>
							</div>
						</form>
					</div>

					<!-- Existing Webhooks List -->
					<div>
						<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
							<h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Active Subscriptions', 'omnifywp-ecommerce'); ?></h3>
							<span style="font-size: 12px; color: #64748b;"><?php printf(esc_html__('%d webhook(s) registered', 'omnifywp-ecommerce'), count($omnify_webhooks_list)); ?></span>
						</div>

						<?php if (empty($omnify_webhooks_list)) : ?>
							<div class="omnify-empty-state" style="text-align: center; padding: 48px 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
								<div style="width: 44px; height: 44px; border-radius: 12px; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
									<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8c0-2.2-1.8-4-4-4-1.8 0-3.3 1.2-3.8 2.8M6 12c0 2.2 1.8 4 4 4 1.8 0 3.3-1.2 3.8-2.8"/><circle cx="18" cy="8" r="2"/><circle cx="6" cy="12" r="2"/></svg>
								</div>
								<h4 style="margin: 0 0 4px; font-size: 14.5px; font-weight: 700; color: #0f172a;"><?php esc_html_e('No Outbound Webhooks Registered', 'omnifywp-ecommerce'); ?></h4>
								<p style="color: #64748b; margin: 0; font-size: 13px;"><?php esc_html_e('Register your first webhook above to begin streaming real-time store events to external listeners.', 'omnifywp-ecommerce'); ?></p>
							</div>
						<?php else : ?>
							<div class="omnify-table-wrapper" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
								<table class="omnify-table" style="width: 100%; border-collapse: collapse; text-align: left;">
									<thead>
										<tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Subscriber Name', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Endpoint URL', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Subscribed Topics', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
											<th style="padding: 12px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($omnify_webhooks_list as $omnify_hook) : ?>
											<tr id="webhook-row-<?php echo esc_attr($omnify_hook['id']); ?>" style="border-bottom: 1px solid #f1f5f9;">
												<td style="padding: 14px 16px; font-weight: 600; color: #0f172a; font-size: 13.5px;">
													<?php echo esc_html($omnify_hook['name']); ?>
												</td>
												<td style="padding: 14px 16px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; color: #475569; word-break: break-all; max-width: 280px;">
													<?php echo esc_html($omnify_hook['url']); ?>
												</td>
												<td style="padding: 14px 16px;">
													<?php
													$omnify_hook_events = array_map('trim', explode(',', (string) ($omnify_hook['events'] ?? '*')));
													foreach ($omnify_hook_events as $omnify_ev) :
													?>
														<span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 7px; font-size: 11px; margin-right: 4px; display: inline-block; margin-bottom: 3px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
															<?php echo esc_html($omnify_ev); ?>
														</span>
													<?php endforeach; ?>
												</td>
												<td style="padding: 14px 16px;">
													<span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 999px; padding: 3px 9px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;">
														<?php echo esc_html(ucfirst((string) ($omnify_hook['status'] ?? 'active'))); ?>
													</span>
												</td>
												<td style="padding: 14px 16px; text-align: right; white-space: nowrap;">
													<span id="webhook-test-result-<?php echo esc_attr($omnify_hook['id']); ?>" style="display: none; font-size: 11.5px; font-weight: 600; margin-right: 8px;"></span>
													<button type="button" class="omnify-button omnify-button--secondary test-webhook-btn" data-webhook-id="<?php echo esc_attr($omnify_hook['id']); ?>" style="height: 30px; font-size: 12px; padding: 0 12px; margin-right: 6px; border-radius: 6px;">
														<?php esc_html_e('Send Test Ping', 'omnifywp-ecommerce'); ?>
													</button>
													<button type="button" class="omnify-button omnify-button--danger delete-webhook-btn" data-webhook-id="<?php echo esc_attr($omnify_hook['id']); ?>" style="height: 30px; font-size: 12px; padding: 0 12px; border-radius: 6px;">
														<?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?>
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- ══ SECTION 3: PAYMENT STATUS ═════════════════════════════════════════ -->
			<?php if ('payments' === $omnify_active_section) : ?>
				<div class="omnify-api-card">
					<div class="omnify-api-section-header">
						<div class="omnify-api-section-header-left">
							<h2><?php esc_html_e('Payment Gateway Connectivity', 'omnifywp-ecommerce'); ?></h2>
							<p><?php esc_html_e('Validate real-time authentication credentials, API endpoints, and connection latency for enabled payment systems.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<span class="omnify-api-section-chip"><?php esc_html_e('Live Diagnostics', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-gateway-cards-grid">
						<!-- Stripe Connectivity Card -->
						<?php $omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']); ?>
						<div class="omnify-gateway-card">
							<div class="omnify-gateway-card-header">
								<div>
									<h3 style="margin: 0 0 6px; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Stripe Gateway', 'omnifywp-ecommerce'); ?></h3>
									<?php if ($omnify_stripe_enabled) : ?>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 3px 9px; border-radius: 999px;">
											<?php esc_html_e('Active', 'omnifywp-ecommerce'); ?>
										</span>
									<?php else : ?>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 3px 9px; border-radius: 999px;">
											<?php esc_html_e('Inactive', 'omnifywp-ecommerce'); ?>
										</span>
									<?php endif; ?>
								</div>
								<div class="omnify-gateway-brand-badge omnify-gateway-brand-badge--stripe">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
								</div>
							</div>
							
							<div class="omnify-gateway-meta-list">
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('Environment Mode', 'omnifywp-ecommerce'); ?></span>
									<strong style="color: #0f172a;"><?php echo esc_html(strtoupper($omnify_settings['stripe_mode'] ?? 'test')); ?></strong>
								</div>
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('Webhook Secret', 'omnifywp-ecommerce'); ?></span>
									<?php if (! empty($omnify_settings['stripe_webhook_secret'])) : ?>
										<span style="color: #059669; font-weight: 600;"><?php esc_html_e('Configured', 'omnifywp-ecommerce'); ?></span>
									<?php else : ?>
										<span style="color: #94a3b8;"><?php esc_html_e('Not Set', 'omnifywp-ecommerce'); ?></span>
									<?php endif; ?>
								</div>
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('API Target', 'omnifywp-ecommerce'); ?></span>
									<code style="font-size: 11.5px; color: #475569;">api.stripe.com/v1</code>
								</div>
							</div>

							<!-- Diagnostics container -->
							<div id="stripe-test-results" style="display: none; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 12.5px; line-height: 1.5;"></div>

							<button type="button" class="omnify-button omnify-button--secondary omnify-test-gateway-btn" id="test-stripe-connection-btn" data-gateway="stripe" style="width: 100%; height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border-radius: 8px;">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
								<span><?php esc_html_e('Check Stripe Status', 'omnifywp-ecommerce'); ?></span>
							</button>
						</div>

						<!-- PayPal Connectivity Card -->
						<?php 
						$omnify_paypal_enabled = ! empty($omnify_settings['paypal_enabled']); 
						$omnify_paypal_mode = $omnify_settings['paypal_mode'] ?? 'sandbox';
						$omnify_client_id = ('live' === $omnify_paypal_mode) ? ($omnify_settings['paypal_live_client_id'] ?? '') : ($omnify_settings['paypal_sandbox_client_id'] ?? '');
						?>
						<div class="omnify-gateway-card">
							<div class="omnify-gateway-card-header">
								<div>
									<h3 style="margin: 0 0 6px; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('PayPal Gateway', 'omnifywp-ecommerce'); ?></h3>
									<?php if ($omnify_paypal_enabled) : ?>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 3px 9px; border-radius: 999px;">
											<?php esc_html_e('Active', 'omnifywp-ecommerce'); ?>
										</span>
									<?php else : ?>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 3px 9px; border-radius: 999px;">
											<?php esc_html_e('Inactive', 'omnifywp-ecommerce'); ?>
										</span>
									<?php endif; ?>
								</div>
								<div class="omnify-gateway-brand-badge omnify-gateway-brand-badge--paypal">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
								</div>
							</div>
							
							<div class="omnify-gateway-meta-list">
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('Environment Mode', 'omnifywp-ecommerce'); ?></span>
									<strong style="color: #0f172a;"><?php echo esc_html(strtoupper($omnify_paypal_mode)); ?></strong>
								</div>
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('Client ID Scope', 'omnifywp-ecommerce'); ?></span>
									<?php if (! empty($omnify_client_id)) : ?>
										<code style="font-size: 11.5px; color: #047857;"><?php echo esc_html('...'.substr($omnify_client_id, -8)); ?></code>
									<?php else : ?>
										<span style="color: #94a3b8;"><?php esc_html_e('Not Set', 'omnifywp-ecommerce'); ?></span>
									<?php endif; ?>
								</div>
								<div class="omnify-gateway-meta-item">
									<span style="color: #64748b;"><?php esc_html_e('API Target', 'omnifywp-ecommerce'); ?></span>
									<code style="font-size: 11.5px; color: #475569;">api-m.sandbox.paypal.com</code>
								</div>
							</div>

							<!-- Diagnostics container -->
							<div id="paypal-test-results" style="display: none; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 12.5px; line-height: 1.5;"></div>

							<button type="button" class="omnify-button omnify-button--secondary omnify-test-gateway-btn" id="test-paypal-connection-btn" data-gateway="paypal" style="width: 100%; height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border-radius: 8px;">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
								<span><?php esc_html_e('Check PayPal Status', 'omnifywp-ecommerce'); ?></span>
							</button>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- ══ SECTION 4: URL CHECKER ════════════════════════════════════════════ -->
			<?php if ('checker' === $omnify_active_section) : ?>
				<div class="omnify-api-card">
					<div class="omnify-api-section-header">
						<div class="omnify-api-section-header-left">
							<h2><?php esc_html_e('Arbitrary URL Connection Checker', 'omnifywp-ecommerce'); ?></h2>
							<p><?php esc_html_e('Inspect server-to-server HTTP connectivity, status codes, response headers, and roundtrip ping latency to external endpoints.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<span class="omnify-api-section-chip"><?php esc_html_e('HTTP GET • Latency Gauge', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-api-subform-card" style="margin-bottom: 24px;">
						<div class="omnify-ping-presets-row">
							<span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;"><?php esc_html_e('Quick Presets:', 'omnifywp-ecommerce'); ?></span>
							<button type="button" class="omnify-ping-preset-btn" onclick="document.getElementById('test-url-input').value='https://api.wordpress.org';">
								<span>WordPress.org API</span>
							</button>
							<button type="button" class="omnify-ping-preset-btn" onclick="document.getElementById('test-url-input').value='https://api.stripe.com/v1';">
								<span>Stripe API</span>
							</button>
							<button type="button" class="omnify-ping-preset-btn" onclick="document.getElementById('test-url-input').value='https://api-m.sandbox.paypal.com';">
								<span>PayPal Sandbox</span>
							</button>
							<button type="button" class="omnify-ping-preset-btn" onclick="document.getElementById('test-url-input').value='https://httpbin.org/status/200';">
								<span>HTTPBin 200</span>
							</button>
						</div>

						<form id="omnify-test-url-form" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
							<div class="omnify-ping-input-group" style="flex: 1; min-width: 280px;">
								<span class="omnify-ping-method-badge">GET</span>
								<input type="url" id="test-url-input" placeholder="https://api.wordpress.org or https://httpbin.org/status/200" required />
							</div>
							<button type="submit" class="omnify-button omnify-button--primary" id="omnify-test-url-submit" style="height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 22px; font-weight: 700; border-radius: 8px;">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
								<span><?php esc_html_e('Send Ping', 'omnifywp-ecommerce'); ?></span>
							</button>
						</form>
					</div>

					<!-- Connection Check Diagnostics Dashboard -->
					<div id="url-test-dashboard" style="display: none; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; padding: 24px;">
						<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: #ecfdf5; color: #059669;">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
								</span>
								<h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Diagnostic Report', 'omnifywp-ecommerce'); ?></h3>
							</div>
							<span id="report-target-url" style="font-size: 12px; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 10px;"></span>
						</div>

						<div class="report-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
							<div class="stat-card" style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);">
								<div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #64748b; margin-bottom: 6px;"><?php esc_html_e('HTTP Status Code', 'omnifywp-ecommerce'); ?></div>
								<div id="report-status-code" style="font-size: 28px; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">--</div>
								<div id="report-status-message" style="font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 500;">--</div>
							</div>

							<div class="stat-card" style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);">
								<div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #64748b; margin-bottom: 6px;"><?php esc_html_e('Roundtrip Latency', 'omnifywp-ecommerce'); ?></div>
								<div id="report-latency" style="font-size: 28px; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">--</div>
								<div id="report-latency-rating" style="font-size: 12px; font-weight: 700; margin-top: 4px;">--</div>
							</div>

							<div class="stat-card" style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);">
								<div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #64748b; margin-bottom: 6px;"><?php esc_html_e('Health Assessment', 'omnifywp-ecommerce'); ?></div>
								<div id="report-health" style="font-size: 16px; font-weight: 700; height: 38px; display: flex; align-items: center; justify-content: center;">--</div>
							</div>
						</div>

						<div>
							<h4 style="margin: 0 0 10px; font-size: 13px; font-weight: 700; color: #334155;"><?php esc_html_e('Response Headers (Filtered)', 'omnifywp-ecommerce'); ?></h4>
							<pre id="report-headers" style="background: #0f172a; color: #93c5fd; padding: 16px; border-radius: 10px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; margin: 0; overflow-x: auto; max-height: 240px; line-height: 1.6;"></pre>
						</div>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
