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

$omnify_active_section = isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : 'keys';
$omnify_allowed_sections = ['keys', 'payments', 'checker'];
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
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

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

	<div class="omnify-settings-layout" style="margin-top: 24px;">
		<!-- Sidebar navigation -->
		<nav class="omnify-settings-nav">
			<div style="font-size: 10px; font-weight: 500; letter-spacing: .5px; text-transform: uppercase; color: var(--omnify-gray-500); padding: 4px 14px 6px;">
				<?php esc_html_e('Sections', 'omnifywp-ecommerce'); ?>
			</div>
			<a href="<?php echo esc_url(omnify_api_page_url('keys')); ?>" class="<?php echo 'keys' === $omnify_active_section ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-admin-network" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('REST API Keys', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(omnify_api_page_url('payments')); ?>" class="<?php echo 'payments' === $omnify_active_section ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-money-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Payment Status', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(omnify_api_page_url('checker')); ?>" class="<?php echo 'checker' === $omnify_active_section ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-admin-site" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('URL Checker', 'omnifywp-ecommerce'); ?>
			</a>
		</nav>

		<!-- Main Page Content -->
		<div class="omnify-settings-content">

			<!-- ══ SECTION 1: API KEYS ═══════════════════════════════════════════════ -->
			<?php if ('keys' === $omnify_active_section) : ?>
				<div class="omnify-card">
					<div class="omnify-card__header">
						<h2><?php esc_html_e('REST API Keys', 'omnifywp-ecommerce'); ?></h2>
						<p><?php esc_html_e('API credentials allow external programs to access your store data. Treat keys with the same secrecy as passwords.', 'omnifywp-ecommerce'); ?></p>
					</div>

					<!-- Key Generator Form -->
					<div class="omnify-card__body" style="border-bottom: 1px solid var(--omnify-gray-200); padding-bottom: 24px; margin-bottom: 24px;">
						<h3 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Generate New Key', 'omnifywp-ecommerce'); ?></h3>
						<form id="omnify-create-key-form" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
							<div style="flex: 1 1 200px;">
								<label for="key-desc" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e('Description', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="key-desc" name="description" placeholder="<?php esc_attr_e('Mobile App, ERP Sync...', 'omnifywp-ecommerce'); ?>" required style="width: 100%;" />
							</div>

							<div style="flex: 1 1 150px;">
								<label for="key-user" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e('Owner User', 'omnifywp-ecommerce'); ?></label>
								<select id="key-user" name="user_id" style="width: 100%;">
									<?php foreach ($omnify_users as $omnify_user) : ?>
										<option value="<?php echo esc_attr($omnify_user->ID); ?>"><?php echo esc_html($omnify_user->display_name . ' (' . $omnify_user->user_login . ')'); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div style="flex: 1 1 150px;">
								<label for="key-perms" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e('Permissions', 'omnifywp-ecommerce'); ?></label>
								<select id="key-perms" name="permissions" style="width: 100%;">
									<option value="read"><?php esc_html_e('Read (GET)', 'omnifywp-ecommerce'); ?></option>
									<option value="write"><?php esc_html_e('Write (POST/PUT/DELETE)', 'omnifywp-ecommerce'); ?></option>
									<option value="read_write"><?php esc_html_e('Read / Write (Both)', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>

							<button type="submit" class="omnify-btn" id="omnify-generate-key-btn" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
								<span class="dashicons dashicons-key" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; display: inline-flex; align-items: center; justify-content: center;"></span>
								<span><?php esc_html_e('Generate Key', 'omnifywp-ecommerce'); ?></span>
							</button>
						</form>

						<!-- Dynamic Credentials Notification Modal -->
						<div id="omnify-credentials-overlay" style="display: none; background: rgba(var(--omnify-primary-rgb, 99, 102, 241), 0.05); border: 1px solid var(--omnify-primary); border-radius: var(--omnify-radius); padding: 20px; margin-top: 20px; backdrop-filter: blur(10px);">
							<h4 style="margin: 0 0 10px; color: var(--omnify-primary); font-size: 15px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-warning" style="font-size: 18px; width: 18px; height: 18px; color: #f59e0b; display: inline-flex; align-items: center; justify-content: center;"></span> <span><?php esc_html_e('API Credentials Generated', 'omnifywp-ecommerce'); ?></span></h4>
							<p style="margin: 0 0 16px; font-size: 13px; color: var(--omnify-gray-600);">
								<?php esc_html_e('Copy these keys now. For security reasons, the Client Secret cannot be retrieved or viewed again after closing this notice.', 'omnifywp-ecommerce'); ?>
							</p>

							<div class="omnify-form-row" style="margin-bottom: 12px;">
								<label style="font-weight: 600; font-size: 12px; color: var(--omnify-gray-700);"><?php esc_html_e('Client Key (Consumer Key)', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; gap: 8px; margin-top: 4px;">
									<input type="text" id="generated-client-key" readonly style="flex: 1; font-family: monospace; background: var(--omnify-gray-50);" />
									<button type="button" class="omnify-btn omnify-btn--secondary copy-credential-btn" data-target="generated-client-key"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
								</div>
							</div>

							<div class="omnify-form-row" style="margin-bottom: 16px;">
								<label style="font-weight: 600; font-size: 12px; color: var(--omnify-gray-700);"><?php esc_html_e('Client Secret (Consumer Secret)', 'omnifywp-ecommerce'); ?></label>
								<div style="display: flex; gap: 8px; margin-top: 4px;">
									<input type="text" id="generated-client-secret" readonly style="flex: 1; font-family: monospace; background: var(--omnify-gray-50);" />
									<button type="button" class="omnify-btn omnify-btn--secondary copy-credential-btn" data-target="generated-client-secret"><?php esc_html_e('Copy', 'omnifywp-ecommerce'); ?></button>
								</div>
							</div>

							<button type="button" class="omnify-btn" id="close-credentials-btn" style="background: var(--omnify-gray-700); color: white;"><?php esc_html_e('I have copied the keys', 'omnifywp-ecommerce'); ?></button>
						</div>
					</div>

					<!-- Existing Keys Listing -->
					<div class="omnify-card__body">
						<h3 style="margin-top: 0; font-size: 16px; margin-bottom: 16px;"><?php esc_html_e('Active API Credentials', 'omnifywp-ecommerce'); ?></h3>
						
						<?php if (empty($omnify_api_keys)) : ?>
							<div style="text-align: center; padding: 32px; background: var(--omnify-gray-50); border: 1px dashed var(--omnify-gray-200); border-radius: var(--omnify-radius);">
								<p style="margin: 0; color: var(--omnify-gray-500);"><?php esc_html_e('No API keys registered yet.', 'omnifywp-ecommerce'); ?></p>
							</div>
						<?php else : ?>
							<table class="widefat striped" style="border: 1px solid var(--omnify-gray-200); border-radius: 8px; overflow: hidden; width: 100%;">
								<thead>
									<tr>
										<th><?php esc_html_e('Description', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('User', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('API Key Identifier', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Permissions', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Created', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Last Used', 'omnifywp-ecommerce'); ?></th>
										<th style="text-align: right;"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($omnify_api_keys as $omnify_key) : 
										$omnify_owner = get_userdata($omnify_key['user_id']);
										$omnify_owner_name = $omnify_owner ? $omnify_owner->display_name : __('Unknown User', 'omnifywp-ecommerce');
									?>
										<tr id="api-key-row-<?php echo esc_attr($omnify_key['id']); ?>">
											<td style="font-weight: 600;"><?php echo esc_html($omnify_key['description']); ?></td>
											<td><?php echo esc_html($omnify_owner_name); ?></td>
											<td><code>ck_...<?php echo esc_html($omnify_key['consumer_key_last4']); ?></code></td>
											<td>
												<?php 
												if ('read_write' === $omnify_key['permissions']) {
													echo '<span class="omnify-badge" style="background: rgba(99, 102, 241, 0.1); color: var(--omnify-primary); font-size: 11px;">' . esc_html__('Read/Write', 'omnifywp-ecommerce') . '</span>';
												} elseif ('write' === $omnify_key['permissions']) {
													echo '<span class="omnify-badge" style="background: rgba(245, 158, 11, 0.1); color: var(--omnify-warning, #d97706); font-size: 11px;">' . esc_html__('Write-Only', 'omnifywp-ecommerce') . '</span>';
												} else {
													echo '<span class="omnify-badge" style="background: rgba(16, 185, 129, 0.1); color: var(--omnify-success); font-size: 11px;">' . esc_html__('Read-Only', 'omnifywp-ecommerce') . '</span>';
												}
												?>
											</td>
											<td><?php echo esc_html(mysql2date(get_option('date_format'), $omnify_key['created_at'])); ?></td>
											<td class="last-used-cell">
												<?php echo $omnify_key['last_used'] ? esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $omnify_key['last_used'])) : esc_html__('Never', 'omnifywp-ecommerce'); ?>
											</td>
											<td style="text-align: right;">
												<button type="button" class="omnify-btn omnify-btn--danger revoke-key-btn" data-key-id="<?php echo esc_attr($omnify_key['id']); ?>" style="padding: 4px 10px; font-size: 11px; height: 26px;">
													<?php esc_html_e('Revoke', 'omnifywp-ecommerce'); ?>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- ══ SECTION 2: PAYMENT STATUS ═════════════════════════════════════════ -->
			<?php if ('payments' === $omnify_active_section) : ?>
				<div class="omnify-card">
					<div class="omnify-card__header">
						<h2><?php esc_html_e('Payment Gateway Connectivity', 'omnifywp-ecommerce'); ?></h2>
						<p><?php esc_html_e('Validate authentication credentials and connection health for active payment systems.', 'omnifywp-ecommerce'); ?></p>
					</div>

					<div class="omnify-card__body">
						<div class="omnify-gateway-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
							
							<!-- Stripe Connectivity Card -->
							<div class="omnify-gateway-diagnostic-card" style="border: 1px solid var(--omnify-gray-200); border-radius: var(--omnify-radius-lg); padding: 24px; background: var(--omnify-white); position: relative; overflow: hidden; display: flex; flex-direction: column;">
								<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
									<div>
										<h3 style="margin: 0; font-size: 18px; color: var(--omnify-dark);"><?php esc_html_e('Stripe integration', 'omnifywp-ecommerce'); ?></h3>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 500; color: var(--omnify-gray-400);">
											<?php 
											$omnify_stripe_enabled = ! empty($omnify_settings['stripe_enabled']);
											echo $omnify_stripe_enabled ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Inactive', 'omnifywp-ecommerce'); 
											?>
										</span>
									</div>
									<div style="font-size: 32px; display: flex; align-items: center;"><span class="dashicons dashicons-money-alt" style="font-size: 32px; width: 32px; height: 32px; color: #6366f1;"></span></div>
								</div>
								
								<div style="font-size: 13px; color: var(--omnify-gray-600); margin-bottom: 24px; flex-grow: 1;">
									<p style="margin: 0 0 8px;"><strong><?php esc_html_e('Mode:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html(ucfirst($omnify_settings['stripe_mode'] ?? 'test')); ?></p>
									<p style="margin: 0;"><strong><?php esc_html_e('Webhook ID:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html($omnify_settings['stripe_webhook_secret'] ? 'Configured' : 'Not Set'); ?></p>
								</div>

								<!-- Diagnostics container -->
								<div id="stripe-test-results" style="display: none; padding: 12px; border-radius: var(--omnify-radius); margin-bottom: 16px; font-size: 12px; line-height: 1.5;"></div>

								<button type="button" class="omnify-btn" id="test-stripe-connection-btn" style="width: 100%;">
								<button type="button" class="omnify-btn omnify-btn--secondary omnify-test-gateway-btn" data-gateway="stripe" style="width: 100%; height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;"><span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span><span><?php esc_html_e('Check Stripe Status', 'omnifywp-ecommerce'); ?></span></button>
								</button>
							</div>

							<!-- PayPal Connectivity Card -->
							<div class="omnify-gateway-diagnostic-card" style="border: 1px solid var(--omnify-gray-200); border-radius: var(--omnify-radius-lg); padding: 24px; background: var(--omnify-white); position: relative; overflow: hidden; display: flex; flex-direction: column;">
								<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
									<div>
										<h3 style="margin: 0; font-size: 18px; color: var(--omnify-dark);"><?php esc_html_e('PayPal Integration', 'omnifywp-ecommerce'); ?></h3>
										<span style="font-size: 11px; text-transform: uppercase; font-weight: 500; color: var(--omnify-gray-400);">
											<?php 
											$omnify_paypal_enabled = ! empty($omnify_settings['paypal_enabled']);
											echo $omnify_paypal_enabled ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Inactive', 'omnifywp-ecommerce'); 
											?>
										</span>
									</div>
									<div style="font-size: 32px; display: flex; align-items: center;"><span class="dashicons dashicons-admin-site-alt3" style="font-size: 32px; width: 32px; height: 32px; color: #0070ba;"></span></div>
								</div>
								
								<div style="font-size: 13px; color: var(--omnify-gray-600); margin-bottom: 24px; flex-grow: 1;">
									<p style="margin: 0 0 8px;"><strong><?php esc_html_e('Mode:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html(ucfirst($omnify_settings['paypal_mode'] ?? 'sandbox')); ?></p>
									<p style="margin: 0;"><strong><?php esc_html_e('Client ID:', 'omnifywp-ecommerce'); ?></strong> 
										<?php 
										$omnify_paypal_mode = $omnify_settings['paypal_mode'] ?? 'sandbox';
										$omnify_client_id = ('live' === $omnify_paypal_mode) ? ($omnify_settings['paypal_live_client_id'] ?? '') : ($omnify_settings['paypal_sandbox_client_id'] ?? '');
										echo esc_html(empty($omnify_client_id) ? 'Not Set' : 'Configured (...'.substr($omnify_client_id, -8).')');
										?>
									</p>
								</div>

								<!-- Diagnostics container -->
								<div id="paypal-test-results" style="display: none; padding: 12px; border-radius: var(--omnify-radius); margin-bottom: 16px; font-size: 12px; line-height: 1.5;"></div>

								<button type="button" class="omnify-btn" id="test-paypal-connection-btn" style="width: 100%;">
								<button type="button" class="omnify-btn omnify-btn--secondary omnify-test-gateway-btn" data-gateway="paypal" style="width: 100%; height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;"><span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span><span><?php esc_html_e('Check PayPal Status', 'omnifywp-ecommerce'); ?></span></button>
								</button>
							</div>

						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- ══ SECTION 3: URL CHECKER ════════════════════════════════════════════ -->
			<?php if ('checker' === $omnify_active_section) : ?>
				<div class="omnify-card">
					<div class="omnify-card__header">
						<h2><?php esc_html_e('Arbitrary URL Connection Checker', 'omnifywp-ecommerce'); ?></h2>
						<p><?php esc_html_e('Test connectivity, HTTP status, and latency from this server to any remote URL endpoint.', 'omnifywp-ecommerce'); ?></p>
					</div>

					<div class="omnify-card__body">
						<form id="omnify-test-url-form" style="display: flex; gap: 12px; margin-bottom: 24px; align-items: center;">
							<input type="url" id="test-url-input" placeholder="https://api.github.com, https://httpbin.org/status/200" required style="flex: 1; height: 38px;" />
							<button type="submit" class="omnify-btn" id="omnify-test-url-submit" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;"><span class="dashicons dashicons-networking" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span> <span><?php esc_html_e('Send Ping', 'omnifywp-ecommerce'); ?>
							</button>
						</form>

						<!-- Connection Check Diagnostics Dashboard -->
						<div id="url-test-dashboard" style="display: none; border: 1px solid var(--omnify-gray-200); border-radius: var(--omnify-radius-lg); background: var(--omnify-gray-50); padding: 24px;">
							<h3 style="margin-top: 0; font-size: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
								<span><?php esc_html_e('Diagnostic Report', 'omnifywp-ecommerce'); ?></span>
								<span id="report-target-url" style="font-size: 12px; font-weight: normal; color: var(--omnify-gray-500); font-family: monospace;"></span>
							</h3>

							<div class="report-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
								
								<div class="stat-card" style="background: var(--omnify-white); padding: 16px; border-radius: var(--omnify-radius); border: 1px solid var(--omnify-gray-200); text-align: center;">
									<div style="font-size: 11px; text-transform: uppercase; font-weight: 500; color: var(--omnify-gray-400); margin-bottom: 4px;"><?php esc_html_e('Status Code', 'omnifywp-ecommerce'); ?></div>
									<div id="report-status-code" style="font-size: 28px; font-weight: 600; color: var(--omnify-dark);">--</div>
									<div id="report-status-message" style="font-size: 11px; color: var(--omnify-gray-500); margin-top: 4px;">--</div>
								</div>

								<div class="stat-card" style="background: var(--omnify-white); padding: 16px; border-radius: var(--omnify-radius); border: 1px solid var(--omnify-gray-200); text-align: center;">
									<div style="font-size: 11px; text-transform: uppercase; font-weight: 500; color: var(--omnify-gray-400); margin-bottom: 4px;"><?php esc_html_e('Latency (Ping)', 'omnifywp-ecommerce'); ?></div>
									<div id="report-latency" style="font-size: 28px; font-weight: 600; color: var(--omnify-dark);">--</div>
									<div id="report-latency-rating" style="font-size: 11px; margin-top: 4px;">--</div>
								</div>

								<div class="stat-card" style="background: var(--omnify-white); padding: 16px; border-radius: var(--omnify-radius); border: 1px solid var(--omnify-gray-200); text-align: center;">
									<div style="font-size: 11px; text-transform: uppercase; font-weight: 500; color: var(--omnify-gray-400); margin-bottom: 4px;"><?php esc_html_e('Health Status', 'omnifywp-ecommerce'); ?></div>
									<div id="report-health" style="font-size: 18px; font-weight: 600; height: 38px; display: flex; align-items: center; justify-content: center;">--</div>
								</div>

							</div>

							<div>
								<h4 style="margin: 0 0 8px; font-size: 13px; font-weight: 500; color: var(--omnify-gray-700);"><?php esc_html_e('Response Headers (Filtered)', 'omnifywp-ecommerce'); ?></h4>
								<pre id="report-headers" style="background: var(--omnify-dark); color: #a5b4fc; padding: 16px; border-radius: var(--omnify-radius); font-family: monospace; font-size: 12px; margin: 0; overflow-x: auto; max-height: 200px;"></pre>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
