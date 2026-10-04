<?php
/**
 * Settings Page Template (Native Premium SaaS Layout Match Rebuild)
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$omnify_active_section = isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : 'general';

$omnify_allowed_sections = ['general', 'pages', 'roles', 'payments', 'tax', 'delivery', 'checkout', 'fraud', 'marketing', 'design', 'email'];
if (! in_array($omnify_active_section, $omnify_allowed_sections, true)) {
	$omnify_active_section = 'general';
}

// Data lists
$omnify_countries = $omnify_template_vars['omnify_countries'] ?? \Omnify\eCommerce\Support\Omnify_Locations::countries();
$omnify_states = \Omnify\eCommerce\Support\Omnify_Locations::states();
$omnify_email_logs = $omnify_template_vars['omnify_email_logs'] ?? [];

$omnify_currencies = [
	'USD' => 'USD — US Dollar ($)',
	'EUR' => 'EUR — Euro (€)',
	'GBP' => 'GBP — British Pound (£)',
	'JPY' => 'JPY — Japanese Yen (¥)',
	'CAD' => 'CAD — Canadian Dollar (C$)',
	'AUD' => 'AUD — Australian Dollar (A$)',
	'CHF' => 'CHF — Swiss Franc (Fr)',
	'CNY' => 'CNY — Chinese Yuan (¥)',
	'INR' => 'INR — Indian Rupee (₹)',
	'AED' => 'AED — UAE Dirham (د.إ)',
	'SAR' => 'SAR — Saudi Riyal (ر.س)',
	'SGD' => 'SGD — Singapore Dollar (S$)',
	'MYR' => 'MYR — Malaysia Ringgit (RM)',
	'NZD' => 'NZD — New Zealand Dollar (NZ$)',
	'BRL' => 'BRL — Brazil Real (R$)',
	'MXN' => 'MXN — Mexico Peso ($)',
	'ZAR' => 'ZAR — South Africa Rand (R)',
	'BDT' => 'BDT — Bangladesh Taka (৳)',
	'PKR' => 'PKR — Pakistan Rupee (₨)'
];

// Helper to generate settings URL
if (! function_exists('omnify_settings_url')) {
	function omnify_settings_url($section) {
		return admin_url('admin.php?page=omnify-settings&section=' . sanitize_key($section));
	}
}

// Helper to render Yes/No radio toggle pills matching mockup exactly
function omnify_render_toggle($name, $value, $label = '') {
	$val = (int) $value;
	?>
	<div class="omnify-modern-toggle-wrapper">
		<div class="omnify-yes-no-group">
			<label class="omnify-yes-no-btn">
				<input type="radio" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($val, 1); ?> />
				<span><?php esc_html_e('Yes', 'omnifywp-ecommerce'); ?></span>
			</label>
			<label class="omnify-yes-no-btn">
				<input type="radio" name="<?php echo esc_attr($name); ?>" value="0" <?php checked($val, 0); ?> />
				<span><?php esc_html_e('No', 'omnifywp-ecommerce'); ?></span>
			</label>
		</div>
		<?php if ($label) : ?>
			<span class="help-text" style="display: block; margin-top: 6px;"><?php echo esc_html($label); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

// Helper to render section title block natively
function omnify_render_section_title($title, $subtext) {
	?>
	<div class="omnify-modern-card-title-block">
		<h3><?php echo esc_html($title); ?></h3>
		<p><?php echo esc_html($subtext); ?></p>
	</div>
	<?php
}

// Helper to render form action footer natively
function omnify_render_form_footer() {
	?>
	<div class="omnify-modern-footer">
		<button type="submit" class="omnify-modern-btn omnify-modern-btn-primary">
			<span class="dashicons dashicons-saved"></span>
			<?php esc_html_e('Save Settings', 'omnifywp-ecommerce'); ?>
		</button>
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-settings')); ?>" class="omnify-modern-btn omnify-modern-btn-secondary">
			<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
		</a>
	</div>
	<?php
}
?>

<div class="omnify-admin-wrapper omnify-settings-page">
	<?php $omnify_active_tab = 'settings'; ?>
	<?php include __DIR__ . '/partials/nav.php'; ?>
	
	<!-- Rebuilt Settings Header -->
	<div class="omnify-modern-settings-header">
		<div class="omnify-modern-header-logo" style="background: transparent !important; border-radius: 0 !important; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
			<img src="<?php echo esc_url(OMNIFY_URL . 'assets/icon-256x256.png'); ?>" alt="Omnify" style="width: 48px; height: 48px; border-radius: 12px; display: block; box-shadow: 0 4px 12px rgba(11, 81, 53, 0.2);" />
		</div>
		<div class="omnify-modern-header-text">
			<h2><?php esc_html_e('Store Settings', 'omnifywp-ecommerce'); ?></h2>
			<p><?php esc_html_e('Manage your store configuration and preferences.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<?php
	// Left Sidebar Category Navigation Items
	$omnify_sub_sections = [
		['id' => 'general', 'label' => __('General Settings', 'omnifywp-ecommerce'), 'icon' => 'dashicons-admin-users', 'url' => omnify_settings_url('general'), 'actives' => ['general', 'pages', 'roles']],
		['id' => 'payments', 'label' => __('Payment Methods', 'omnifywp-ecommerce'), 'icon' => 'dashicons-money-alt', 'url' => omnify_settings_url('payments'), 'actives' => ['payments']],
		['id' => 'tax', 'label' => __('Tax Rules', 'omnifywp-ecommerce'), 'icon' => 'dashicons-calculator', 'url' => omnify_settings_url('tax'), 'actives' => ['tax']],
		['id' => 'delivery', 'label' => __('Delivery Zones', 'omnifywp-ecommerce'), 'icon' => 'dashicons-location', 'url' => omnify_settings_url('delivery'), 'actives' => ['delivery']],
		['id' => 'checkout', 'label' => __('Checkout Flow', 'omnifywp-ecommerce'), 'icon' => 'dashicons-cart', 'url' => omnify_settings_url('checkout'), 'actives' => ['checkout', 'fraud', 'marketing']],
		['id' => 'design', 'label' => __('Storefront Design', 'omnifywp-ecommerce'), 'icon' => 'dashicons-art', 'url' => omnify_settings_url('design'), 'actives' => ['design', 'email']]
	];
	?>

	<div class="omnify-modern-settings-layout">
		<!-- Left Sidebar Category Navigation -->
		<aside class="omnify-modern-sub-sidebar">
			<nav class="omnify-modern-sub-nav">
				<?php foreach ($omnify_sub_sections as $sub_sec) : 
					$omnify_sec_active = in_array($omnify_active_section, $sub_sec['actives'], true);
				?>
					<a href="<?php echo esc_url($sub_sec['url']); ?>" class="omnify-modern-sub-tab <?php echo $omnify_sec_active ? 'is-active' : ''; ?>">
						<span class="dashicons <?php echo esc_attr($sub_sec['icon']); ?>"></span>
						<?php echo esc_html($sub_sec['label']); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<!-- Need Help Card -->
			<div class="omnify-modern-help-card">
				<div class="omnify-modern-help-header">
					<span class="dashicons dashicons-phone" style="color: #136c5f;"></span>
					<?php esc_html_e('Need help?', 'omnifywp-ecommerce'); ?>
				</div>
				<p><?php esc_html_e('Learn more about store setup in our documentation.', 'omnifywp-ecommerce'); ?></p>
				<a href="https://docs.omnify.com" target="_blank" rel="noopener">
					<?php esc_html_e('View Docs', 'omnifywp-ecommerce'); ?>
					<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px;"></span>
				</a>
			</div>
		</aside>

		<!-- Main Settings Form content -->
		<div class="omnify-modern-content-card">

			<?php if (in_array($omnify_active_section, ['general', 'pages', 'roles'], true)) : ?>
				<!-- ══ GENERAL SETTINGS HUB ════════════════════════════ -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('general')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'general' ? 'is-active' : ''; ?>"><?php esc_html_e('Store Identity', 'omnifywp-ecommerce'); ?></a>
						<a href="<?php echo esc_url(omnify_settings_url('pages')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'pages' ? 'is-active' : ''; ?>"><?php esc_html_e('Core Pages', 'omnifywp-ecommerce'); ?></a>
						<a href="<?php echo esc_url(omnify_settings_url('roles')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'roles' ? 'is-active' : ''; ?>"><?php esc_html_e('Shop Roles', 'omnifywp-ecommerce'); ?></a>
					</div>
				</div>

				<?php if ($omnify_active_section === 'pages') : ?>
					<!-- NATIVE PAGES GENERATION PANEL -->
					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('Core Pages', 'omnifywp-ecommerce'), __('Omnify requires specific WordPress pages containing shortcodes. Generate them below or assign existing pages.', 'omnifywp-ecommerce')); ?>
						
						<?php
						$omnify_required_pages = [
							'storefront'      => ['label' => __('Storefront Page', 'omnifywp-ecommerce'),      'shortcode' => '[omnify_storefront]'],
							'cart'            => ['label' => __('Cart Page', 'omnifywp-ecommerce'),            'shortcode' => '[omnify_cart]'],
							'checkout'        => ['label' => __('Checkout Page', 'omnifywp-ecommerce'),       'shortcode' => '[omnify_checkout]'],
							'customer_portal' => ['label' => __('Customer Portal', 'omnifywp-ecommerce'),      'shortcode' => '[omnify_customer_portal]'],
							'download_page'   => ['label' => __('Secure Download Page', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_download_page]'],
							'order_tracking'  => ['label' => __('Order Tracking Page', 'omnifywp-ecommerce'),  'shortcode' => '[omnify_order_tracking]'],
						];
						foreach ($omnify_required_pages as $omnify_key => $omnify_page_info):
							$omnify_page_id = $omnify_settings['page_' . $omnify_key] ?? 0;
							$omnify_page    = $omnify_page_id ? get_post($omnify_page_id) : null;
						?>
						<div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #edf2f6; gap: 16px; flex-wrap: wrap;">
							<div>
								<strong style="font-size: 14px; color: #0f172a;"><?php echo esc_html($omnify_page_info['label']); ?></strong>
								<div style="font-size: 12px; color: #64748b; margin-top: 4px;">
									<?php esc_html_e('Shortcode:', 'omnifywp-ecommerce'); ?>
									<code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #0f172a; font-family: monospace;"><?php echo esc_html($omnify_page_info['shortcode']); ?></code>
								</div>
							</div>
							<div style="display: flex; align-items: center; gap: 10px;">
								<?php if ($omnify_page && $omnify_page->post_status === 'publish'): ?>
									<span style="display: flex; align-items: center; gap: 4px; color: #136c5f; font-size: 13px; font-weight: 600; margin-right: 8px;">
										<span class="dashicons dashicons-yes" style="color: #136c5f; font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
										<?php esc_html_e('Active', 'omnifywp-ecommerce'); ?>
									</span>
									<a href="<?php echo esc_url(get_permalink($omnify_page->ID)); ?>" target="_blank" class="omnify-modern-btn omnify-modern-btn-secondary" style="padding: 6px 12px !important; font-size: 12px !important;">
										<?php esc_html_e('View', 'omnifywp-ecommerce'); ?>
									</a>
									<a href="<?php echo esc_url(get_edit_post_link($omnify_page->ID)); ?>" class="omnify-modern-btn omnify-modern-btn-secondary" style="padding: 6px 12px !important; font-size: 12px !important;">
										<?php esc_html_e('Edit', 'omnifywp-ecommerce'); ?>
									</a>
								<?php else: ?>
									<span style="color: #94a3b8; font-size: 13px;"><?php esc_html_e('Not set', 'omnifywp-ecommerce'); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>

						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 30px;">
							<?php wp_nonce_field('omnify_generate_pages', 'omnify_generate_pages_nonce'); ?>
							<input type="hidden" name="action" value="omnify_generate_pages" />
							<button type="submit" class="omnify-modern-btn omnify-modern-btn-primary">
								<span class="dashicons dashicons-plus-alt"></span>
								<?php esc_html_e('Generate All Required Pages', 'omnifywp-ecommerce'); ?>
							</button>
						</form>
					</div>

				<?php else : ?>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
						<input type="hidden" name="action" value="omnify_save_settings" />
						<input type="hidden" name="section" value="<?php echo esc_attr($omnify_active_section); ?>" />

						<div class="omnify-modern-card-body">
							<?php if ($omnify_active_section === 'general') : ?>
								<?php omnify_render_section_title(__('Store Identity', 'omnifywp-ecommerce'), __('Configure details about your online shop identity.', 'omnifywp-ecommerce')); ?>
								
								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="store_name"><?php esc_html_e('Store Name', 'omnifywp-ecommerce'); ?> * <span class="dashicons dashicons-info" title="Used in receipts and shop header."></span></label>
										<input type="text" id="store_name" name="store_name" value="<?php echo esc_attr($omnify_settings['store_name']); ?>" required />
										<span class="help-text"><?php esc_html_e('Used in email receipts, notifications, and storefront.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label for="store_email"><?php esc_html_e('Store Email', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="Sender reply address."></span></label>
										<input type="email" id="store_email" name="store_email" value="<?php echo esc_attr($omnify_settings['store_email'] ?? get_option('admin_email')); ?>" />
										<span class="help-text"><?php esc_html_e('Reply-to address for outgoing emails.', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="default_currency"><?php esc_html_e('Store Currency', 'omnifywp-ecommerce'); ?></label>
										<select id="default_currency" name="default_currency" class="omnify-searchable-select">
											<?php foreach ($omnify_currencies as $code => $label) : ?>
												<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_settings['default_currency'], $code); ?>>
													<?php echo esc_html($label); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label for="currency_position"><?php esc_html_e('Currency Position', 'omnifywp-ecommerce'); ?></label>
										<select id="currency_position" name="currency_position">
											<option value="before" <?php selected($omnify_settings['currency_position'] ?? 'before', 'before'); ?>><?php esc_html_e('Before ($99.99)', 'omnifywp-ecommerce'); ?></option>
											<option value="after" <?php selected($omnify_settings['currency_position'] ?? 'before', 'after'); ?>><?php esc_html_e('After (99.99$)', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="price_decimals"><?php esc_html_e('Number of Decimals', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="price_decimals" name="price_decimals" min="0" max="4" value="<?php echo esc_attr((string) ($omnify_settings['price_decimals'] ?? 2)); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="thousand_separator"><?php esc_html_e('Thousand Separator', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="thousand_separator" name="thousand_separator" value="<?php echo esc_attr($omnify_settings['thousand_separator'] ?? ','); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="decimal_separator"><?php esc_html_e('Decimal Separator', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="decimal_separator" name="decimal_separator" value="<?php echo esc_attr($omnify_settings['decimal_separator'] ?? '.'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="default_country"><?php esc_html_e('Base Country / Region', 'omnifywp-ecommerce'); ?></label>
										<select id="default_country" name="default_country" class="omnify-country-select omnify-searchable-select">
											<?php foreach ($omnify_countries as $code => $name) : ?>
												<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_settings['default_country'] ?? '', $code); ?>>
													<?php echo esc_html($name); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="selling_countries"><?php esc_html_e('Selling Locations', 'omnifywp-ecommerce'); ?></label>
										<select id="selling_countries" name="selling_countries[]" class="omnify-searchable-select" multiple>
											<?php foreach ($omnify_countries as $code => $name) : ?>
												<option value="<?php echo esc_attr($code); ?>" <?php echo in_array($code, (array) ($omnify_settings['selling_countries'] ?? []), true) ? 'selected' : ''; ?>>
													<?php echo esc_html($name); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Enable Test Mode', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('test_mode', $omnify_settings['test_mode'] ?? 0, __('Checkouts bypass payment gateway verification.', 'omnifywp-ecommerce')); ?>
									</div>
								</div>

								<!-- Global Tax Parameters embedded under General section so they save perfectly -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Tax Calculation Options', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Prices Include Tax', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('prices_include_tax', $omnify_settings['prices_include_tax'] ?? 0, __('If enabled, listed prices are treated as tax-inclusive.', 'omnifywp-ecommerce')); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Charge Tax on Delivery', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('tax_shipping', $omnify_settings['tax_shipping'] ?? 0, __('When enabled, the matched tax rate also applies to delivery costs.', 'omnifywp-ecommerce')); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Enable Tax Reporting Fields', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('tax_reporting_enabled', $omnify_settings['tax_reporting_enabled'] ?? 0, __('Stores reporting codes on tax rules for accounting workflows.', 'omnifywp-ecommerce')); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label for="tax_rounding"><?php esc_html_e('Tax Rounding', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="Rounding method."></span></label>
										<select id="tax_rounding" name="tax_rounding">
											<option value="line" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'line'); ?>><?php esc_html_e('Round at line level', 'omnifywp-ecommerce'); ?></option>
											<option value="subtotal" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'subtotal'); ?>><?php esc_html_e('Round at subtotal level', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
								</div>
								
								<!-- Carry over tax rate parameter -->
								<input type="hidden" name="tax_rate" value="<?php echo esc_attr($omnify_settings['tax_rate'] ?? 0); ?>" />

							<?php elseif ($omnify_active_section === 'roles') : ?>
								<?php omnify_render_section_title(__('Shop Roles & Permissions', 'omnifywp-ecommerce'), __('Configure dashboard access permissions for administrative roles.', 'omnifywp-ecommerce')); ?>
								
								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group" style="grid-column: span 2;">
										<label for="admin_access_capability"><?php esc_html_e('Minimum Access Capability', 'omnifywp-ecommerce'); ?></label>
										<select id="admin_access_capability" name="admin_access_capability">
											<option value="manage_options" <?php selected($omnify_settings['admin_access_capability'] ?? 'manage_options', 'manage_options'); ?>><?php esc_html_e('Only Administrators (manage_options)', 'omnifywp-ecommerce'); ?></option>
											<option value="manage_omnify" <?php selected($omnify_settings['admin_access_capability'] ?? 'manage_options', 'manage_omnify'); ?>><?php esc_html_e('Administrators, Shop Managers & Clerks (manage_omnify)', 'omnifywp-ecommerce'); ?></option>
										</select>
										<span class="help-text"><?php esc_html_e('Choose who can view and edit settings, products, orders, and review directories.', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>

								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Feature Permissions Matrix', 'omnifywp-ecommerce'); ?></h4>
									<p style="font-size: 12.5px; color: #64748b; margin-top: 4px;"><?php esc_html_e('Select which roles are permitted to access and manage specific shop sections.', 'omnifywp-ecommerce'); ?></p>
								</div>

								<div style="grid-column: span 2; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 10px;">
									<table class="widefat striped" style="margin: 0; border: none; box-shadow: none;">
										<thead>
											<tr>
												<th><?php esc_html_e('Shop Feature / Section', 'omnifywp-ecommerce'); ?></th>
												<th style="text-align: center; width: 140px;"><?php esc_html_e('Administrator', 'omnifywp-ecommerce'); ?></th>
												<th style="text-align: center; width: 140px;"><?php esc_html_e('Shop Manager', 'omnifywp-ecommerce'); ?></th>
												<th style="text-align: center; width: 140px;"><?php esc_html_e('Shop Clerk', 'omnifywp-ecommerce'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$omnify_features_map = [
												'dashboard'       => __('📊 Dashboard & Analytics Overview', 'omnifywp-ecommerce'),
												'products'        => __('📦 Products & Inventory Management', 'omnifywp-ecommerce'),
												'orders'          => __('🛒 Orders & Fulfillment', 'omnifywp-ecommerce'),
												'customers'       => __('👥 Customer Database & Access Control', 'omnifywp-ecommerce'),
												'coupons'         => __('🏷️ Coupon Codes & Discounts', 'omnifywp-ecommerce'),
												'abandoned_carts' => __('⌛ Abandoned Cart Recovery Logs', 'omnifywp-ecommerce'),
												'reviews'         => __('⭐ Product Reviews & Moderation', 'omnifywp-ecommerce'),
												'analytics'       => __('📈 Sales Analytics & Reports', 'omnifywp-ecommerce'),
												'settings'        => __('⚙️ Store Settings & Configurations', 'omnifywp-ecommerce'),
												'tools'           => __('🛡️ System Tools, API Keys & Logs', 'omnifywp-ecommerce'),
											];

											$omnify_perms = $omnify_settings['role_permissions'] ?? [];
											foreach ($omnify_features_map as $omnify_f_key => $omnify_f_label) :
												$omnify_current_allowed = (array) ($omnify_perms[$omnify_f_key] ?? ['administrator']);
											?>
												<tr>
													<td><strong><?php echo esc_html($omnify_f_label); ?></strong></td>
													<!-- Administrator Column -->
													<td style="text-align: center;">
														<input type="checkbox" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="administrator" checked disabled style="cursor: not-allowed; opacity: 0.7;" />
														<input type="hidden" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="administrator" />
													</td>
													<!-- Shop Manager Column -->
													<td style="text-align: center;">
														<input type="checkbox" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="shop_manager" <?php checked(in_array('shop_manager', $omnify_current_allowed, true)); ?> />
													</td>
													<!-- Shop Clerk Column -->
													<td style="text-align: center;">
														<input type="checkbox" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="shop_clerk" <?php checked(in_array('shop_clerk', $omnify_current_allowed, true)); ?> />
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
						</div>
						<?php omnify_render_form_footer(); ?>
					</form>

					<!-- Shop User Management info and Directory details render below roles settings panel -->
					<?php if ($omnify_active_section === 'roles') : ?>
						<div class="omnify-modern-card-body" style="border-top: 1px solid #edf2f6; margin-top: 20px;">
							<?php omnify_render_section_title(__('Shop User Management', 'omnifywp-ecommerce'), __('Manage shop managers and clerks using native WordPress user management.', 'omnifywp-ecommerce')); ?>
							<p style="margin: 12px 0 16px; color: var(--omnify-gray-600); font-size: 13px; line-height: 1.6;">
								<?php esc_html_e('To add new store staff, create users through standard WordPress user management and assign them the Shop Manager or Shop Clerk role.', 'omnifywp-ecommerce'); ?>
							</p>
							<a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="omnify-modern-btn omnify-modern-btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-admin-users"></span>
								<?php esc_html_e('Add New User in WordPress', 'omnifywp-ecommerce'); ?>
							</a>
						</div>

						<div class="omnify-modern-card-body" style="border-top: 1px solid #edf2f6; margin-top: 20px;">
							<?php omnify_render_section_title(__('Access Directory / Users', 'omnifywp-ecommerce'), __('Current users holding shop-specific roles or administrative access.', 'omnifywp-ecommerce')); ?>
							
							<div style="overflow-x: auto; width: 100%; margin-top: 10px;">
								<table class="widefat striped" style="box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px;">
									<thead>
										<tr>
											<th><?php esc_html_e('Username', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Email', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Role', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 100px;"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$omnify_users = get_users([
											'role__in' => ['administrator', 'shop_manager', 'shop_clerk'],
											'orderby'  => 'user_login',
										]);
										if (empty($omnify_users)) : ?>
											<tr>
												<td colspan="5" style="text-align: center; color: #94a3b8;">
													<?php esc_html_e('No shop users found.', 'omnifywp-ecommerce'); ?>
												</td>
											</tr>
										<?php else :
											foreach ($omnify_users as $omnify_usr) :
												$omnify_roles = (array) $omnify_usr->roles;
												$omnify_display_role = '';
												if (in_array('administrator', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Administrator', 'omnifywp-ecommerce');
												} elseif (in_array('shop_manager', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Shop Manager', 'omnifywp-ecommerce');
												} elseif (in_array('shop_clerk', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Shop Clerk', 'omnifywp-ecommerce');
												} else {
													$omnify_display_role = esc_html(implode(', ', $omnify_roles));
												}
												?>
												<tr>
													<td><strong><?php echo esc_html($omnify_usr->user_login); ?></strong></td>
													<td><?php echo esc_html($omnify_usr->display_name); ?></td>
													<td><?php echo esc_html($omnify_usr->user_email); ?></td>
													<td>
														<span class="omnify-modern-sub-tab <?php echo in_array('administrator', $omnify_roles, true) ? 'is-active' : ''; ?>" style="padding: 2px 8px !important; font-size: 11px !important; border-radius: 6px !important; display: inline-block !important; font-weight: 500 !important; cursor: default !important;">
															<?php echo esc_html($omnify_display_role); ?>
														</span>
													</td>
													<td>
														<?php if (! in_array('administrator', $omnify_roles, true)) : ?>
															<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_remove_shop_user&user_id=' . $omnify_usr->ID), 'omnify_remove_shop_user_' . $omnify_usr->ID)); ?>" style="color: #ef4444; font-weight: 600; text-decoration: none;" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to remove access for this user?', 'omnifywp-ecommerce')); ?>')">
																<?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?>
															</a>
														<?php else: ?>
															<span style="color: #cbd5e1; font-weight: 600; cursor: not-allowed;"><?php esc_html_e('Locked', 'omnifywp-ecommerce'); ?></span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach;
										endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endif; ?>

				<?php endif; ?>

			<?php elseif ($omnify_active_section === 'payments') : ?>
				<!-- ══ PAYMENT METHODS ═════════════════════════════════ -->
				<?php
				$omnify_payment_methods = $omnify_settings['payment_methods'] ?? [];
				$omnify_pm_map = [];
				foreach ($omnify_payment_methods as $omnify_m) {
					if (! is_array($omnify_m) || empty($omnify_m['id'])) {
						continue;
					}
					$omnify_pm_map[$omnify_m['id']] = $omnify_m;
				}
				?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="payments" />

					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('Payment Methods', 'omnifywp-ecommerce'), __('Configure active credit cards, digital wallets, and manual payment arrangements.', 'omnifywp-ecommerce')); ?>

						<!-- Stripe Settings -->
						<div style="grid-column: span 2; margin-top: 10px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Stripe Credit Card Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Stripe Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('stripe_checkout_enabled', $omnify_settings['stripe_checkout_enabled'] ?? 0, __('Shoppers can pay via Credit Cards powered by Stripe.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="stripe_checkout_enabled" data-cond-val="1">
								<label for="stripe_mode"><?php esc_html_e('Stripe Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="stripe_mode" name="stripe_mode">
									<option value="test" <?php selected($omnify_settings['stripe_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['stripe_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Credit Cards', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="stripe_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="stripe-test-keys-row" data-cond-field="stripe_mode" data-cond-val="test">
								<div class="omnify-modern-form-group">
									<label for="stripe_test_publishable_key"><?php esc_html_e('Stripe Test Publishable Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="stripe_test_publishable_key" name="stripe_test_publishable_key" value="<?php echo esc_attr($omnify_settings['stripe_test_publishable_key'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="stripe_test_secret_key"><?php esc_html_e('Stripe Test Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="stripe_test_secret_key" name="stripe_test_secret_key" value="<?php echo esc_attr($omnify_settings['stripe_test_secret_key'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="stripe-live-keys-row" data-cond-field="stripe_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="stripe_live_publishable_key"><?php esc_html_e('Stripe Live Publishable Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="stripe_live_publishable_key" name="stripe_live_publishable_key" value="<?php echo esc_attr($omnify_settings['stripe_live_publishable_key'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="stripe_live_secret_key"><?php esc_html_e('Stripe Live Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="stripe_live_secret_key" name="stripe_live_secret_key" value="<?php echo esc_attr($omnify_settings['stripe_live_secret_key'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- PayPal Settings -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('PayPal Gateway Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable PayPal Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('paypal_checkout_enabled', $omnify_settings['paypal_checkout_enabled'] ?? 0, __('Shoppers can pay via PayPal.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="paypal_checkout_enabled" data-cond-val="1">
								<label for="paypal_mode"><?php esc_html_e('PayPal Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="paypal_mode" name="paypal_mode">
									<option value="sandbox" <?php selected($omnify_settings['paypal_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['paypal_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live PayPal Accounts', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="paypal_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="paypal-sandbox-keys-row" data-cond-field="paypal_mode" data-cond-val="sandbox">
								<div class="omnify-modern-form-group">
									<label for="paypal_sandbox_client_id"><?php esc_html_e('PayPal Sandbox Client ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="paypal_sandbox_client_id" name="paypal_sandbox_client_id" value="<?php echo esc_attr($omnify_settings['paypal_sandbox_client_id'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="paypal_sandbox_secret"><?php esc_html_e('PayPal Sandbox Secret', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="paypal_sandbox_secret" name="paypal_sandbox_secret" value="<?php echo esc_attr($omnify_settings['paypal_sandbox_secret'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="paypal-live-keys-row" data-cond-field="paypal_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="paypal_live_client_id"><?php esc_html_e('PayPal Live Client ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="paypal_live_client_id" name="paypal_live_client_id" value="<?php echo esc_attr($omnify_settings['paypal_live_client_id'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="paypal_live_secret"><?php esc_html_e('PayPal Live Secret', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="paypal_live_secret" name="paypal_live_secret" value="<?php echo esc_attr($omnify_settings['paypal_live_secret'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- Razorpay Settings -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Razorpay Gateway Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Razorpay Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('razorpay_checkout_enabled', $omnify_settings['razorpay_checkout_enabled'] ?? 0, __('Shoppers can pay via Razorpay (India).', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="razorpay_checkout_enabled" data-cond-val="1">
								<label for="razorpay_mode"><?php esc_html_e('Razorpay Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="razorpay_mode" name="razorpay_mode">
									<option value="test" <?php selected($omnify_settings['razorpay_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test / Sandbox', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['razorpay_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Razorpay Accounts', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="razorpay_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="razorpay-test-keys-row" data-cond-field="razorpay_mode" data-cond-val="test">
								<div class="omnify-modern-form-group">
									<label for="razorpay_test_key_id"><?php esc_html_e('Razorpay Test Key ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="razorpay_test_key_id" name="razorpay_test_key_id" value="<?php echo esc_attr($omnify_settings['razorpay_test_key_id'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="razorpay_test_key_secret"><?php esc_html_e('Razorpay Test Key Secret', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="razorpay_test_key_secret" name="razorpay_test_key_secret" value="<?php echo esc_attr($omnify_settings['razorpay_test_key_secret'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="razorpay-live-keys-row" data-cond-field="razorpay_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="razorpay_live_key_id"><?php esc_html_e('Razorpay Live Key ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="razorpay_live_key_id" name="razorpay_live_key_id" value="<?php echo esc_attr($omnify_settings['razorpay_live_key_id'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="razorpay_live_key_secret"><?php esc_html_e('Razorpay Live Key Secret', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="razorpay_live_key_secret" name="razorpay_live_key_secret" value="<?php echo esc_attr($omnify_settings['razorpay_live_key_secret'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- Alipay Settings -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Alipay Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Alipay Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('alipay_checkout_enabled', $omnify_settings['alipay_checkout_enabled'] ?? 0, __('Shoppers can pay via Alipay.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="alipay_checkout_enabled" data-cond-val="1">
								<label for="alipay_mode"><?php esc_html_e('Alipay Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="alipay_mode" name="alipay_mode">
									<option value="sandbox" <?php selected($omnify_settings['alipay_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['alipay_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Alipay Accounts', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="alipay_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label for="alipay_app_id"><?php esc_html_e('Alipay App ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="alipay_app_id" name="alipay_app_id" value="<?php echo esc_attr($omnify_settings['alipay_app_id'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="alipay_merchant_private_key"><?php esc_html_e('Merchant Private Key', 'omnifywp-ecommerce'); ?></label>
									<textarea id="alipay_merchant_private_key" name="alipay_merchant_private_key" rows="3"><?php echo esc_textarea($omnify_settings['alipay_merchant_private_key'] ?? ''); ?></textarea>
								</div>
								<div class="omnify-modern-form-group">
									<label for="alipay_alipay_public_key"><?php esc_html_e('Alipay Public Key', 'omnifywp-ecommerce'); ?></label>
									<textarea id="alipay_alipay_public_key" name="alipay_alipay_public_key" rows="3"><?php echo esc_textarea($omnify_settings['alipay_alipay_public_key'] ?? ''); ?></textarea>
								</div>
							</div>
						</div>

						<!-- WeChat Pay Settings -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('WeChat Pay Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable WeChat Pay Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('wechat_checkout_enabled', $omnify_settings['wechat_checkout_enabled'] ?? 0, __('Shoppers can pay via WeChat Pay.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="wechat_checkout_enabled" data-cond-val="1">
								<label for="wechat_mode"><?php esc_html_e('WeChat Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="wechat_mode" name="wechat_mode">
									<option value="sandbox" <?php selected($omnify_settings['wechat_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['wechat_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live WeChat Pay', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="wechat_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="wechat_appid"><?php esc_html_e('WeChat App ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="wechat_appid" name="wechat_appid" value="<?php echo esc_attr($omnify_settings['wechat_appid'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="wechat_mchid"><?php esc_html_e('WeChat Merchant ID (MCH ID)', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="wechat_mchid" name="wechat_mchid" value="<?php echo esc_attr($omnify_settings['wechat_mchid'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label for="wechat_key"><?php esc_html_e('WeChat API Key (v2)', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="wechat_key" name="wechat_key" value="<?php echo esc_attr($omnify_settings['wechat_key'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- SSLCommerz Settings -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('SSLCommerz Settings', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable SSLCommerz Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('sslcommerz_checkout_enabled', $omnify_settings['sslcommerz_checkout_enabled'] ?? 0, __('Shoppers can pay via SSLCommerz (Bangladesh).', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="sslcommerz_checkout_enabled" data-cond-val="1">
								<label for="sslcommerz_mode"><?php esc_html_e('SSLCommerz Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="sslcommerz_mode" name="sslcommerz_mode">
									<option value="sandbox" <?php selected($omnify_settings['sslcommerz_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['sslcommerz_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live SSLCommerz', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="sslcommerz_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="sslcommerz_store_id"><?php esc_html_e('Store ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="sslcommerz_store_id" name="sslcommerz_store_id" value="<?php echo esc_attr($omnify_settings['sslcommerz_store_id'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="sslcommerz_store_password"><?php esc_html_e('Store Password', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="sslcommerz_store_password" name="sslcommerz_store_password" value="<?php echo esc_attr($omnify_settings['sslcommerz_store_password'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- Paystack Settings (Africa) -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Paystack Gateway Settings (Africa)', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Paystack Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('paystack_checkout_enabled', $omnify_settings['paystack_checkout_enabled'] ?? 0, __('Shoppers can pay via Cards, Mobile Money (M-Pesa, MTN, Airtel), and Bank Transfer across Africa.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="paystack_checkout_enabled" data-cond-val="1">
								<label for="paystack_mode"><?php esc_html_e('Paystack Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="paystack_mode" name="paystack_mode">
									<option value="test" <?php selected($omnify_settings['paystack_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['paystack_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Paystack Accounts', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="paystack_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="paystack-test-keys-row" data-cond-field="paystack_mode" data-cond-val="test">
								<div class="omnify-modern-form-group">
									<label for="paystack_test_public_key"><?php esc_html_e('Test Public Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="paystack_test_public_key" name="paystack_test_public_key" value="<?php echo esc_attr($omnify_settings['paystack_test_public_key'] ?? ''); ?>" placeholder="pk_test_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="paystack_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="paystack_test_secret_key" name="paystack_test_secret_key" value="<?php echo esc_attr($omnify_settings['paystack_test_secret_key'] ?? ''); ?>" placeholder="sk_test_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="paystack-live-keys-row" data-cond-field="paystack_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="paystack_live_public_key"><?php esc_html_e('Live Public Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="paystack_live_public_key" name="paystack_live_public_key" value="<?php echo esc_attr($omnify_settings['paystack_live_public_key'] ?? ''); ?>" placeholder="pk_live_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="paystack_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="paystack_live_secret_key" name="paystack_live_secret_key" value="<?php echo esc_attr($omnify_settings['paystack_live_secret_key'] ?? ''); ?>" placeholder="sk_live_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="paystack_webhook_secret"><?php esc_html_e('Paystack Webhook Secret (Optional)', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="paystack_webhook_secret" name="paystack_webhook_secret" value="<?php echo esc_attr($omnify_settings['paystack_webhook_secret'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Paystack Webhook URL', 'omnifywp-ecommerce'); ?></label>
									<input type="text" readonly value="<?php echo esc_url(rest_url('omnify/v1/paystack/webhook')); ?>" style="background: #f8fafc; color: #64748b;" />
								</div>
							</div>
						</div>

						<!-- Tap Payments Settings (Middle East / GCC) -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Tap Payments Settings (Middle East / GCC)', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Tap Payments Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('tap_checkout_enabled', $omnify_settings['tap_checkout_enabled'] ?? 0, __('Shoppers can pay via mada, KNET, Benefit, NAPS, Apple Pay, and Cards across the Middle East.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="tap_checkout_enabled" data-cond-val="1">
								<label for="tap_mode"><?php esc_html_e('Tap Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="tap_mode" name="tap_mode">
									<option value="test" <?php selected($omnify_settings['tap_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['tap_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Tap Accounts', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="tap_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="tap-test-keys-row" data-cond-field="tap_mode" data-cond-val="test">
								<div class="omnify-modern-form-group">
									<label for="tap_test_publishable_key"><?php esc_html_e('Test Publishable Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="tap_test_publishable_key" name="tap_test_publishable_key" value="<?php echo esc_attr($omnify_settings['tap_test_publishable_key'] ?? ''); ?>" placeholder="pk_test_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="tap_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="tap_test_secret_key" name="tap_test_secret_key" value="<?php echo esc_attr($omnify_settings['tap_test_secret_key'] ?? ''); ?>" placeholder="sk_test_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="tap-live-keys-row" data-cond-field="tap_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="tap_live_publishable_key"><?php esc_html_e('Live Publishable Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="tap_live_publishable_key" name="tap_live_publishable_key" value="<?php echo esc_attr($omnify_settings['tap_live_publishable_key'] ?? ''); ?>" placeholder="pk_live_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="tap_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="tap_live_secret_key" name="tap_live_secret_key" value="<?php echo esc_attr($omnify_settings['tap_live_secret_key'] ?? ''); ?>" placeholder="sk_live_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label><?php esc_html_e('Tap Webhook URL', 'omnifywp-ecommerce'); ?></label>
									<input type="text" readonly value="<?php echo esc_url(rest_url('omnify/v1/tap/webhook')); ?>" style="background: #f8fafc; color: #64748b;" />
								</div>
							</div>
						</div>

						<!-- Mollie Settings (European Union) -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Mollie Gateway Settings (European Union)', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Mollie Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('mollie_checkout_enabled', $omnify_settings['mollie_checkout_enabled'] ?? 0, __('Shoppers can pay via iDEAL, Bancontact, SEPA Direct Debit, EPS, Przelewy24, Klarna, and Cards across Europe.', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="mollie_checkout_enabled" data-cond-val="1">
								<label for="mollie_mode"><?php esc_html_e('Mollie Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="mollie_mode" name="mollie_mode">
									<option value="test" <?php selected($omnify_settings['mollie_mode'] ?? 'test', 'test'); ?>><?php esc_html_e('Test Mode', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['mollie_mode'] ?? 'test', 'live'); ?>><?php esc_html_e('Live Mollie Account', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="mollie_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="mollie-test-key-row" data-cond-field="mollie_mode" data-cond-val="test">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label for="mollie_test_api_key"><?php esc_html_e('Test API Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="mollie_test_api_key" name="mollie_test_api_key" value="<?php echo esc_attr($omnify_settings['mollie_test_api_key'] ?? ''); ?>" placeholder="test_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="mollie-live-key-row" data-cond-field="mollie_mode" data-cond-val="live">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label for="mollie_live_api_key"><?php esc_html_e('Live API Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="mollie_live_api_key" name="mollie_live_api_key" value="<?php echo esc_attr($omnify_settings['mollie_live_api_key'] ?? ''); ?>" placeholder="live_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label><?php esc_html_e('Mollie Webhook URL', 'omnifywp-ecommerce'); ?></label>
									<input type="text" readonly value="<?php echo esc_url(rest_url('omnify/v1/mollie/webhook')); ?>" style="background: #f8fafc; color: #64748b;" />
								</div>
							</div>
						</div>

						<!-- Khalti Settings (Nepal) -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Khalti Gateway Settings (Nepal)', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable Khalti Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('khalti_checkout_enabled', $omnify_settings['khalti_checkout_enabled'] ?? 0, __('Shoppers can pay via Khalti Digital Wallet, Mobile Banking, and ConnectIPS (Nepal).', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="khalti_checkout_enabled" data-cond-val="1">
								<label for="khalti_mode"><?php esc_html_e('Khalti Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="khalti_mode" name="khalti_mode">
									<option value="sandbox" <?php selected($omnify_settings['khalti_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['khalti_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live Khalti Merchant', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="khalti_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="khalti-sandbox-keys-row" data-cond-field="khalti_mode" data-cond-val="sandbox">
								<div class="omnify-modern-form-group">
									<label for="khalti_test_public_key"><?php esc_html_e('Khalti Test Public Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="khalti_test_public_key" name="khalti_test_public_key" value="<?php echo esc_attr($omnify_settings['khalti_test_public_key'] ?? ''); ?>" placeholder="test_public_key_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="khalti_test_secret_key"><?php esc_html_e('Khalti Test Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="khalti_test_secret_key" name="khalti_test_secret_key" value="<?php echo esc_attr($omnify_settings['khalti_test_secret_key'] ?? ''); ?>" placeholder="test_secret_key_..." />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="khalti-live-keys-row" data-cond-field="khalti_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="khalti_live_public_key"><?php esc_html_e('Khalti Live Public Key', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="khalti_live_public_key" name="khalti_live_public_key" value="<?php echo esc_attr($omnify_settings['khalti_live_public_key'] ?? ''); ?>" placeholder="live_public_key_..." />
								</div>
								<div class="omnify-modern-form-group">
									<label for="khalti_live_secret_key"><?php esc_html_e('Khalti Live Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="khalti_live_secret_key" name="khalti_live_secret_key" value="<?php echo esc_attr($omnify_settings['khalti_live_secret_key'] ?? ''); ?>" placeholder="live_secret_key_..." />
								</div>
							</div>
						</div>

						<!-- eSewa Settings (Nepal) -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('eSewa Gateway Settings (Nepal)', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<div class="omnify-modern-form-row">
							<div class="omnify-modern-form-group">
								<label><?php esc_html_e('Enable eSewa Checkout', 'omnifywp-ecommerce'); ?></label>
								<?php omnify_render_toggle('esewa_checkout_enabled', $omnify_settings['esewa_checkout_enabled'] ?? 0, __('Shoppers can pay via eSewa Digital Wallet and SCT cards (Nepal).', 'omnifywp-ecommerce')); ?>
							</div>
							<div class="omnify-modern-form-group" data-cond-field="esewa_checkout_enabled" data-cond-val="1">
								<label for="esewa_mode"><?php esc_html_e('eSewa Mode', 'omnifywp-ecommerce'); ?></label>
								<select id="esewa_mode" name="esewa_mode">
									<option value="sandbox" <?php selected($omnify_settings['esewa_mode'] ?? 'sandbox', 'sandbox'); ?>><?php esc_html_e('Sandbox / Test', 'omnifywp-ecommerce'); ?></option>
									<option value="live" <?php selected($omnify_settings['esewa_mode'] ?? 'sandbox', 'live'); ?>><?php esc_html_e('Live eSewa Merchant', 'omnifywp-ecommerce'); ?></option>
								</select>
							</div>
						</div>

						<div data-cond-field="esewa_checkout_enabled" data-cond-val="1">
							<div class="omnify-modern-form-row" id="esewa-sandbox-keys-row" data-cond-field="esewa_mode" data-cond-val="sandbox">
								<div class="omnify-modern-form-group">
									<label for="esewa_test_product_code"><?php esc_html_e('Test Product Code (Merchant ID)', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="esewa_test_product_code" name="esewa_test_product_code" value="<?php echo esc_attr($omnify_settings['esewa_test_product_code'] ?? 'EPAYTEST'); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="esewa_test_secret_key"><?php esc_html_e('Test Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="esewa_test_secret_key" name="esewa_test_secret_key" value="<?php echo esc_attr($omnify_settings['esewa_test_secret_key'] ?? '8gBm/:&EnhH.1/q'); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" id="esewa-live-keys-row" data-cond-field="esewa_mode" data-cond-val="live">
								<div class="omnify-modern-form-group">
									<label for="esewa_live_product_code"><?php esc_html_e('Live Product Code (Merchant ID)', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="esewa_live_product_code" name="esewa_live_product_code" value="<?php echo esc_attr($omnify_settings['esewa_live_product_code'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="esewa_live_secret_key"><?php esc_html_e('Live Secret Key', 'omnifywp-ecommerce'); ?></label>
									<input type="password" id="esewa_live_secret_key" name="esewa_live_secret_key" value="<?php echo esc_attr($omnify_settings['esewa_live_secret_key'] ?? ''); ?>" />
								</div>
							</div>
						</div>

						<!-- Manual Payment Methods Settings Loop -->
						<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
							<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Manual / Offline Payment Methods', 'omnifywp-ecommerce'); ?></h4>
						</div>

						<?php
						$omnify_method_defs = [
							[
								'id'   => 'bank_transfer',
								'icon' => '🏦',
								'name' => __('Bank Transfer', 'omnifywp-ecommerce'),
								'desc' => __('Customers transfer the payment directly to your bank account. Order stays pending until you mark it paid.', 'omnifywp-ecommerce'),
							],
							[
								'id'   => 'cheque',
								'icon' => '✉️',
								'name' => __('Cheque / Money Order', 'omnifywp-ecommerce'),
								'desc' => __('Customers mail you a cheque. Order stays pending until you receive and mark it paid.', 'omnifywp-ecommerce'),
							],
							[
								'id'   => 'cash_on_delivery',
								'icon' => '💵',
								'name' => __('Cash on Delivery', 'omnifywp-ecommerce'),
								'desc' => __('Customers pay when they receive the product or meet you in person.', 'omnifywp-ecommerce'),
							],
						];
						foreach ($omnify_method_defs as $omnify_def):
							$omnify_saved    = $omnify_pm_map[$omnify_def['id']] ?? [];
							$omnify_enabled  = ! empty($omnify_saved['enabled']);
							$omnify_name_val = $omnify_saved['name'] ?? $omnify_def['name'];
							$omnify_instr    = $omnify_saved['instructions'] ?? '';
						?>
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; padding: 24px; border-radius: 12px; margin-bottom: 10px; background: #ffffff;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<label style="font-size: 15px; font-weight: 500; color: #0f172a;"><?php echo esc_html($omnify_def['icon'] . ' ' . $omnify_def['name']); ?></label>
										<p style="font-size: 12.5px; color: #64748b; margin: 4px 0 20px 0; line-height: 1.5;"><?php echo esc_html($omnify_def['desc']); ?></p>
										
										<div data-cond-field="payment_methods[<?php echo esc_attr($omnify_def['id']); ?>][enabled]" data-cond-val="1">
											<label for="pm_name_<?php echo esc_attr($omnify_def['id']); ?>"><?php esc_html_e('Display Name', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="pm_name_<?php echo esc_attr($omnify_def['id']); ?>" name="payment_methods[<?php echo esc_attr($omnify_def['id']); ?>][name]" value="<?php echo esc_attr($omnify_name_val); ?>" required />
										</div>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle("payment_methods[{$omnify_def['id']}][enabled]", $omnify_enabled ? 1 : 0); ?>
										
										<div data-cond-field="payment_methods[<?php echo esc_attr($omnify_def['id']); ?>][enabled]" data-cond-val="1" style="margin-top: 20px; width: 100%;">
											<label for="pm_instr_<?php echo esc_attr($omnify_def['id']); ?>"><?php esc_html_e('Payment Instructions', 'omnifywp-ecommerce'); ?></label>
											<textarea id="pm_instr_<?php echo esc_attr($omnify_def['id']); ?>" name="payment_methods[<?php echo esc_attr($omnify_def['id']); ?>][instructions]" rows="3"><?php echo esc_textarea($omnify_instr); ?></textarea>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>

						<!-- Repeatable Custom payment methods list -->
						<?php
						$omnify_custom_methods = [];
						foreach ($omnify_pm_map as $omnify_id => $omnify_saved) {
							if (in_array($omnify_id, ['bank_transfer', 'cheque', 'cash_on_delivery'], true)) {
								continue;
							}
							$omnify_custom_methods[] = $omnify_saved;
						}
						?>

						<div id="omnify-custom-payment-methods-container" style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
							<?php foreach ($omnify_custom_methods as $omnify_custom_pm) :
								$omnify_custom_id = $omnify_custom_pm['id'];
								$omnify_custom_enabled = ! empty($omnify_custom_pm['enabled']);
								$omnify_custom_name = $omnify_custom_pm['name'] ?? __('Manual Payment', 'omnifywp-ecommerce');
								$omnify_custom_instr = $omnify_custom_pm['instructions'] ?? '';
							?>
								<div class="omnify-delivery-zone-card omnify-custom-pm-card" data-pm-id="<?php echo esc_attr($omnify_custom_id); ?>" style="grid-column: span 2; border: 1px solid rgba(226, 232, 240, 0.8); padding: 24px; border-radius: 12px; margin-bottom: 16px; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01); transition: all 0.2s ease;">
									<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
										<div class="omnify-modern-form-group">
											<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
												<div style="display: flex; align-items: center; gap: 10px;">
													<span class="omnify-pm-icon-wrap" style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: rgba(13, 148, 136, 0.08); color: #0d9488;">
														<span class="dashicons dashicons-admin-generic" style="font-size: 15px; width: 15px; height: 15px;"></span>
													</span>
													<label style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0;"><?php esc_html_e('Custom / Manual Payment', 'omnifywp-ecommerce'); ?></label>
												</div>
												<button type="button" class="omnify-remove-pm-icon-btn omnify-remove-pm" title="<?php esc_attr_e('Delete', 'omnifywp-ecommerce'); ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); color: #ef4444; cursor: pointer; transition: all 0.2s ease; padding: 0;">
													<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
												</button>
											</div>
											<p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0; line-height: 1.5;"><?php esc_html_e('A flexible method for any other offline arrangement (e.g. PayPal invoice, wire transfer).', 'omnifywp-ecommerce'); ?></p>
											
											<div data-cond-field="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][enabled]" data-cond-val="1">
												<label for="pm_name_<?php echo esc_attr($omnify_custom_id); ?>"><?php esc_html_e('Display Name', 'omnifywp-ecommerce'); ?></label>
												<input type="text" id="pm_name_<?php echo esc_attr($omnify_custom_id); ?>" name="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][name]" value="<?php echo esc_attr($omnify_custom_name); ?>" required />
											</div>
										</div>
										
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle("payment_methods[{$omnify_custom_id}][enabled]", $omnify_custom_enabled ? 1 : 0); ?>
											
											<div data-cond-field="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][enabled]" data-cond-val="1" style="margin-top: 20px; width: 100%;">
												<label for="pm_instr_<?php echo esc_attr($omnify_custom_id); ?>"><?php esc_html_e('Payment Instructions', 'omnifywp-ecommerce'); ?></label>
												<textarea id="pm_instr_<?php echo esc_attr($omnify_custom_id); ?>" name="payment_methods[<?php echo esc_attr($omnify_custom_id); ?>][instructions]" rows="3"><?php echo esc_textarea($omnify_custom_instr); ?></textarea>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<div style="grid-column: span 2; margin-top: 10px; margin-bottom: 20px;">
							<button type="button" id="omnify-add-custom-pm" class="omnify-modern-btn omnify-modern-btn-secondary">
								<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
								<span><?php esc_html_e('Add Custom Payment Method', 'omnifywp-ecommerce'); ?></span>
							</button>
						</div>

					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif ($omnify_active_section === 'tax') : ?>
				<!-- ══ TAX RULES ═══════════════════════════════════════ -->
				<?php
				$omnify_tax_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];
				if (empty($omnify_tax_rules)) {
					$omnify_tax_rules[] = ['enabled' => false, 'label' => '', 'country' => '', 'state' => '', 'rate' => '', 'priority' => 10, 'reporting_code' => ''];
				}
				?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="tax" />
					<input type="hidden" name="tax_rules_submitted" value="1" />

					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('Tax Rules', 'omnifywp-ecommerce'), __('Create regional country and state tax rates. Use * as a wildcard for all states or countries.', 'omnifywp-ecommerce')); ?>

						<div class="omnify-table-wrapper" style="margin: 16px 0;">
							<table class="omnify-table">
								<thead>
									<tr>
										<th style="width: 40px; text-align: center;"><?php esc_html_e('On', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Label', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('State', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Rate %', 'omnifywp-ecommerce'); ?></th>
										<th><?php esc_html_e('Reporting Code', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 80px;"><?php esc_html_e('Priority', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 50px;"></th>
									</tr>
								</thead>
								<tbody id="omnify-tax-rules-body">
									<?php foreach ($omnify_tax_rules as $omnify_index => $omnify_rule) : ?>
										<?php
										$omnify_selected_country = strtoupper((string) ($omnify_rule['country'] ?? ''));
										$omnify_selected_state = strtoupper((string) ($omnify_rule['state'] ?? ''));
										$omnify_state_options = $omnify_states[$omnify_selected_country] ?? [];
										?>
										<tr>
											<td style="text-align: center; vertical-align: middle;"><input type="checkbox" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][enabled]" value="1" <?php checked(! empty($omnify_rule['enabled'])); ?> /></td>
											<td><input type="text" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][label]" value="<?php echo esc_attr((string) ($omnify_rule['label'] ?? '')); ?>" style="width: 100%;" required /></td>
											<td>
												<select name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][country]" class="omnify-country-select" style="width: 100%;">
													<option value="*"><?php esc_html_e('All countries (*)', 'omnifywp-ecommerce'); ?></option>
													<?php foreach ($omnify_countries as $code => $name) : ?>
														<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_country, $code); ?>><?php echo esc_html($name); ?></option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<select name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][state]" class="omnify-state-select" data-selected="<?php echo esc_attr($omnify_selected_state); ?>" style="width: 100%;">
													<option value="*"><?php esc_html_e('All states (*)', 'omnifywp-ecommerce'); ?></option>
													<?php foreach ($omnify_state_options as $code => $name) : ?>
														<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_state, $code); ?>><?php echo esc_html($name); ?></option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<div class="omnify-input-wrapper">
													<input type="number" step="0.0001" min="0" max="100" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][rate]" value="<?php echo esc_attr((string) ($omnify_rule['rate'] ?? '')); ?>" style="width: 100%;" required />
													<span class="omnify-input-suffix">%</span>
												</div>
											</td>
											<td><input type="text" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][reporting_code]" value="<?php echo esc_attr((string) ($omnify_rule['reporting_code'] ?? '')); ?>" style="width: 100%;" /></td>
											<td><input type="number" min="1" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][priority]" value="<?php echo esc_attr((string) ($omnify_rule['priority'] ?? 10)); ?>" style="width: 100%;" required /></td>
											<td style="text-align: center;"><button type="button" class="omnify-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.2s;"><span class="dashicons dashicons-trash"></span></button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<button type="button" id="omnify-add-tax-rule" class="omnify-modern-btn omnify-modern-btn-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
							<span><?php esc_html_e('Add Tax Rule Row', 'omnifywp-ecommerce'); ?></span>
						</button>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif ($omnify_active_section === 'delivery') : ?>
				<!-- ══ DELIVERY ZONES ══════════════════════════════════ -->
				<?php
				$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];
				?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="delivery" />
					<input type="hidden" name="delivery_zones_submitted" value="1" />

					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('Delivery & Shipping', 'omnifywp-ecommerce'), __('Manage shipping costs and regional delivery zones for physical orders.', 'omnifywp-ecommerce')); ?>

						<div id="omnify-delivery-zones-container" style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
							<?php foreach ($omnify_zones as $omnify_index => $omnify_zone) : ?>
								<?php
								$omnify_selected_country = strtoupper((string) ($omnify_zone['countries'] ?? ''));
								$omnify_selected_state = strtoupper((string) ($omnify_zone['states'] ?? ''));
								?>
								<div class="omnify-delivery-zone-card" data-zone-index="<?php echo esc_attr((string) $omnify_index); ?>">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Zone Name', 'omnifywp-ecommerce'); ?></label>
											<input type="text" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][name]" value="<?php echo esc_attr((string) ($omnify_zone['name'] ?? '')); ?>" required />
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle("delivery_zones[{$omnify_index}][enabled]", $omnify_zone['enabled'] ?? 0); ?>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Country Code', 'omnifywp-ecommerce'); ?></label>
											<select name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][countries]" class="omnify-country-select" style="width: 100%;">
												<option value="*"><?php esc_html_e('All countries (*)', 'omnifywp-ecommerce'); ?></option>
												<?php foreach ($omnify_countries as $code => $name) : ?>
													<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_country, $code); ?>><?php echo esc_html($name); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('State Code', 'omnifywp-ecommerce'); ?></label>
											<select name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][states]" class="omnify-state-select" data-selected="<?php echo esc_attr($omnify_selected_state); ?>" style="width: 100%;">
												<option value="*"><?php esc_html_e('All states (*)', 'omnifywp-ecommerce'); ?></option>
											</select>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Rate Cost', 'omnifywp-ecommerce'); ?></label>
											<input type="number" step="0.01" min="0" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][rate]" value="<?php echo esc_attr((string) ($omnify_zone['rate'] ?? '')); ?>" required />
										</div>
										<div class="omnify-modern-form-group" style="justify-content: flex-end; align-items: flex-end;">
											<button type="button" class="button omnify-remove-zone" style="display: inline-flex; align-items: center; gap: 6px; color: #ef4444; border-color: #fca5a5; background: #fff;"><span class="dashicons dashicons-trash" style="font-size: 15px; width: 15px; height: 15px; line-height: 15px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span><span><?php esc_html_e('Delete Zone', 'omnifywp-ecommerce'); ?></span></button>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<button type="button" id="omnify-add-delivery-zone" class="omnify-modern-btn omnify-modern-btn-secondary" style="margin-top: 20px;">
							<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
							<span><?php esc_html_e('Add Delivery Zone Card', 'omnifywp-ecommerce'); ?></span>
						</button>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif (in_array($omnify_active_section, ['checkout', 'fraud', 'marketing'], true)) : ?>
				<!-- ══ CHECKOUT FLOW HUB ═══════════════════════════════ -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('checkout')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'checkout' ? 'is-active' : ''; ?>"><?php esc_html_e('Checkout Flow', 'omnifywp-ecommerce'); ?></a>
						<a href="<?php echo esc_url(omnify_settings_url('fraud')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'fraud' ? 'is-active' : ''; ?>"><?php esc_html_e('Fraud & Risk', 'omnifywp-ecommerce'); ?></a>
						<a href="<?php echo esc_url(omnify_settings_url('marketing')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'marketing' ? 'is-active' : ''; ?>"><?php esc_html_e('Marketing & Tracking', 'omnifywp-ecommerce'); ?></a>
					</div>
				</div>

				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="<?php echo esc_attr($omnify_active_section); ?>" />

					<!-- Carry over settings parameters -->
					<input type="hidden" name="store_name" value="<?php echo esc_attr($omnify_settings['store_name'] ?? ''); ?>" />
					<input type="hidden" name="default_currency" value="<?php echo esc_attr($omnify_settings['default_currency'] ?? ''); ?>" />
					<input type="hidden" name="tax_rate" value="<?php echo esc_attr($omnify_settings['tax_rate'] ?? 0); ?>" />
					<?php if (! empty($omnify_settings['test_mode'])) : ?><input type="hidden" name="test_mode" value="1"><?php endif; ?>

					<div class="omnify-modern-card-body">
						<?php if ($omnify_active_section === 'checkout') : ?>
							<?php omnify_render_section_title(__('Checkout Flow', 'omnifywp-ecommerce'), __('Configure shopping basket, discounts, and order processing options.', 'omnifywp-ecommerce')); ?>

							<!-- Customer Registration -->
							<div style="grid-column: span 2; margin-top: 10px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Customer Registration', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Allow Guest Checkout', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('allow_guest_checkout', $omnify_settings['allow_guest_checkout'] ?? 1, __('Customers can purchase without creating an account.', 'omnifywp-ecommerce')); ?>
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Require Account', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('require_account_on_checkout', $omnify_settings['require_account_on_checkout'] ?? 0, __('Forces customers to register or log in before completing checkout.', 'omnifywp-ecommerce')); ?>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label for="account_creation_mode"><?php esc_html_e('Account Creation During Checkout', 'omnifywp-ecommerce'); ?></label>
									<select id="account_creation_mode" name="account_creation_mode">
										<option value="optional" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'optional'); ?>><?php esc_html_e('Optional checkbox', 'omnifywp-ecommerce'); ?></option>
										<option value="automatic" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'automatic'); ?>><?php esc_html_e('Automatically create account', 'omnifywp-ecommerce'); ?></option>
										<option value="required" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'required'); ?>><?php esc_html_e('Require account and password', 'omnifywp-ecommerce'); ?></option>
									</select>
									<span class="help-text"><?php esc_html_e('Automatic mode creates a WordPress account with a generated password after checkout.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Sign In Customer', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('auto_login_created_accounts', $omnify_settings['auto_login_created_accounts'] ?? 1, __('Sign in customers after account creation', 'omnifywp-ecommerce')); ?>
								</div>
							</div>

							<!-- Checkout Behavior -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Checkout Behavior', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Require Phone Number', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('checkout_require_phone', $omnify_settings['checkout_require_phone'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Reduce Stock', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('reduce_stock_on_checkout', $omnify_settings['reduce_stock_on_checkout'] ?? 1); ?>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label for="default_payment_method"><?php esc_html_e('Default Payment Method', 'omnifywp-ecommerce'); ?></label>
									<select id="default_payment_method" name="default_payment_method">
										<option value="card" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'card'); ?>><?php esc_html_e('Card / Stripe', 'omnifywp-ecommerce'); ?></option>
										<option value="paypal" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'paypal'); ?>><?php esc_html_e('PayPal', 'omnifywp-ecommerce'); ?></option>
										<option value="bank_transfer" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'bank_transfer'); ?>><?php esc_html_e('Bank Transfer', 'omnifywp-ecommerce'); ?></option>
										<option value="cash_on_delivery" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'cash_on_delivery'); ?>><?php esc_html_e('Cash on Delivery', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>
								<div class="omnify-modern-form-group">
									<label for="order_number_prefix"><?php esc_html_e('Order Number Prefix', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="order_number_prefix" name="order_number_prefix" value="<?php echo esc_attr($omnify_settings['order_number_prefix'] ?? 'OMN-'); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label for="order_number_suffix"><?php esc_html_e('Order Number Suffix', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="order_number_suffix" name="order_number_suffix" value="<?php echo esc_attr($omnify_settings['order_number_suffix'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="order_number_padding"><?php esc_html_e('Order Number Padding (digits)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="order_number_padding" name="order_number_padding" min="1" max="10" value="<?php echo esc_attr($omnify_settings['order_number_padding'] ?? 4); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="low_stock_threshold"><?php esc_html_e('Low Stock Threshold', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="<?php echo esc_attr($omnify_settings['low_stock_threshold'] ?? 5); ?>" style="max-width: 120px;" />
								</div>
							</div>

							<!-- Coupons & Discounts -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Coupons & Discounts', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Enable Coupons', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('enable_coupons', $omnify_settings['enable_coupons'] ?? 1); ?>
								</div>
							</div>

							<!-- Abandoned Cart Recovery -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Abandoned Cart Recovery', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Abandoned Cart Recovery', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('abandoned_cart_enabled', $omnify_settings['abandoned_cart_enabled'] ?? 1, __('Capture checkout sessions for recovery emails', 'omnifywp-ecommerce')); ?>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;" data-cond-field="abandoned_cart_enabled" data-cond-val="1">
								<div class="omnify-modern-form-group">
									<label for="abandoned_cart_delay_minutes"><?php esc_html_e('Send Reminder After (minutes)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="abandoned_cart_delay_minutes" name="abandoned_cart_delay_minutes" min="5" value="<?php echo esc_attr($omnify_settings['abandoned_cart_delay_minutes'] ?? 60); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="abandoned_cart_expire_days"><?php esc_html_e('Recovery Link Expiry (Days)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="abandoned_cart_expire_days" name="abandoned_cart_expire_days" min="1" value="<?php echo esc_attr($omnify_settings['abandoned_cart_expire_days'] ?? 14); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="abandoned_cart_max_reminders"><?php esc_html_e('Maximum Reminders', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="abandoned_cart_max_reminders" name="abandoned_cart_max_reminders" min="1" max="5" value="<?php echo esc_attr($omnify_settings['abandoned_cart_max_reminders'] ?? 1); ?>" />
								</div>
							</div>

							<!-- Terms & Conditions -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Terms & Conditions', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Require Terms Agreement', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('require_terms', $omnify_settings['require_terms'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group" data-cond-field="require_terms" data-cond-val="1">
									<label for="terms_url"><?php esc_html_e('Terms & Conditions URL', 'omnifywp-ecommerce'); ?></label>
									<input type="url" id="terms_url" name="terms_url" value="<?php echo esc_attr($omnify_settings['terms_url'] ?? ''); ?>" placeholder="https://..." />
								</div>
							</div>

							<!-- Downloads -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Downloads Settings', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="download_link_expiry"><?php esc_html_e('Download Link Expiry (days)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="download_link_expiry" name="download_link_expiry" min="0" max="365" value="<?php echo esc_attr($omnify_settings['download_link_expiry'] ?? 7); ?>" />
									<span class="help-text"><?php esc_html_e('How many days signed download links remain active. Set 0 for no expiry.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-modern-form-group">
									<label for="download_limit"><?php esc_html_e('Maximum Download Attempts per File', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="download_limit" name="download_limit" min="0" value="<?php echo esc_attr($omnify_settings['download_limit'] ?? 0); ?>" />
									<span class="help-text"><?php esc_html_e('0 = unlimited. Limits how many times each download link can be used.', 'omnifywp-ecommerce'); ?></span>
								</div>
							</div>

							<!-- Refunds -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Refunds Management', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Enable Refund Requests', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('enable_refunds', $omnify_settings['enable_refunds'] ?? 0, __('Allow customers to request refunds for completed orders.', 'omnifywp-ecommerce')); ?>
								</div>
								<div class="omnify-modern-form-group" data-cond-field="enable_refunds" data-cond-val="1">
									<label for="refund_duration"><?php esc_html_e('Refund Window (Days)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="refund_duration" name="refund_duration" min="1" max="120" value="<?php echo esc_attr((string) ($omnify_settings['refund_duration'] ?? 14)); ?>" />
								</div>
							</div>

						<?php elseif ($omnify_active_section === 'fraud') : ?>
							<?php omnify_render_section_title(__('Fraud & Risk', 'omnifywp-ecommerce'), __('Configure security rules and order blocking options.', 'omnifywp-ecommerce')); ?>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Flag Billing/Shipping Country Mismatch', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('fraud_country_mismatch_flag', $omnify_settings['fraud_country_mismatch_flag'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Flag Disposable/Temporary Emails', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('fraud_disposable_email_flag', $omnify_settings['fraud_disposable_email_flag'] ?? 0); ?>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label for="fraud_max_value"><?php esc_html_e('High Value Order Threshold', 'omnifywp-ecommerce'); ?></label>
									<input type="number" step="0.01" min="0" id="fraud_max_value" name="fraud_max_value" value="<?php echo esc_attr((string) ($omnify_settings['fraud_max_value'] ?? 500.00)); ?>" />
									<span class="help-text"><?php esc_html_e('Flag orders with a total greater than or equal to this amount.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-modern-form-group">
									<label for="fraud_max_attempts_limit"><?php esc_html_e('Velocity Limit (Orders per 10 mins)', 'omnifywp-ecommerce'); ?></label>
									<input type="number" id="fraud_max_attempts_limit" name="fraud_max_attempts_limit" min="1" max="50" value="<?php echo esc_attr((string) ($omnify_settings['fraud_max_attempts_limit'] ?? 3)); ?>" />
									<span class="help-text"><?php esc_html_e('Flag orders if more than this number of orders are placed from the same IP address within a 10-minute window.', 'omnifywp-ecommerce'); ?></span>
								</div>
							</div>

						<?php elseif ($omnify_active_section === 'marketing') : ?>
							<?php omnify_render_section_title(__('Marketing & Tracking', 'omnifywp-ecommerce'), __('Configure analytic logs and conversion pixel keys.', 'omnifywp-ecommerce')); ?>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Enable Google Analytics 4', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('tracking_ga4_enabled', $omnify_settings['tracking_ga4_enabled'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group" data-cond-field="tracking_ga4_enabled" data-cond-val="1">
									<label for="tracking_ga4_measurement_id"><?php esc_html_e('GA4 Measurement ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="tracking_ga4_measurement_id" name="tracking_ga4_measurement_id" value="<?php echo esc_attr($omnify_settings['tracking_ga4_measurement_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX" />
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Enable Meta Pixel Tracking', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('tracking_meta_enabled', $omnify_settings['tracking_meta_enabled'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group" data-cond-field="tracking_meta_enabled" data-cond-val="1">
									<label for="tracking_meta_pixel_id"><?php esc_html_e('Meta Pixel ID', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="tracking_meta_pixel_id" name="tracking_meta_pixel_id" value="<?php echo esc_attr($omnify_settings['tracking_meta_pixel_id'] ?? ''); ?>" placeholder="XXXXXXXXXXXXXXX" />
								</div>
							<div class="omnify-modern-form-row" style="margin-top: 25px; border-top: 1px solid #edf2f6; padding-top: 25px;">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label style="font-weight: 500; color: #0f172a; margin-bottom: 8px;"><?php esc_html_e('Tracked Events', 'omnifywp-ecommerce'); ?></label>
									<div class="omnify-modern-events-badges" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?php esc_html_e('View Item', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?php esc_html_e('Add To Cart', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?php esc_html_e('Begin Checkout', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?php esc_html_e('Purchase', 'omnifywp-ecommerce'); ?></span>
									</div>
									<span class="help-text" style="font-size: 13px; color: var(--omnify-gray-600); line-height: 1.4;"><?php esc_html_e('Purchase fires after local/manual checkout completes. Redirect providers also receive checkout-start events before the customer leaves the site.', 'omnifywp-ecommerce'); ?></span>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Log tracking events in the browser console', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('tracking_debug_mode', $omnify_settings['tracking_debug_mode'] ?? 0, __('Enable this to log GA4 and Meta Pixel events in the browser console for debugging purposes.', 'omnifywp-ecommerce')); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif (in_array($omnify_active_section, ['design', 'email'], true)) : ?>
				<!-- ══ STOREFRONT DESIGN HUB ═══════════════════════════ -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('design')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'design' ? 'is-active' : ''; ?>"><?php esc_html_e('Storefront Design', 'omnifywp-ecommerce'); ?></a>
						<a href="<?php echo esc_url(omnify_settings_url('email')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'email' ? 'is-active' : ''; ?>"><?php esc_html_e('Email Templates', 'omnifywp-ecommerce'); ?></a>
					</div>
				</div>

				<?php if ($omnify_active_section === 'design') : ?>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
						<input type="hidden" name="action" value="omnify_save_settings" />
						<input type="hidden" name="section" value="design" />

						<?php
						$omnify_active_filters = $omnify_settings['storefront_filters'] ?? ['category', 'type'];
						$omnify_filter_position = $omnify_settings['storefront_filter_position'] ?? 'top';
						$omnify_catalog_layout = $omnify_settings['storefront_catalog_layout'] ?? 'grid';
						$omnify_storefront_grid_columns = (int) ($omnify_settings['storefront_grid_columns'] ?? ($omnify_settings['design_grid_columns'] ?? 3));
						$omnify_storefront_card_style = $omnify_settings['storefront_card_style'] ?? 'standard';
						?>

						<!-- Visual sub-tabs inside Design Card -->
						<div class="omnify-settings-section-tabs">
							<button type="button" class="omnify-settings-section-tab is-active" data-tab="theme"><?php esc_html_e('Theme & Hero Banner', 'omnifywp-ecommerce'); ?></button>
							<button type="button" class="omnify-settings-section-tab" data-tab="catalog"><?php esc_html_e('Catalog & Filters', 'omnifywp-ecommerce'); ?></button>
							<button type="button" class="omnify-settings-section-tab" data-tab="product"><?php esc_html_e('Product Display & Urgency', 'omnifywp-ecommerce'); ?></button>
						</div>

						<div class="omnify-modern-card-body" style="padding-top: 15px;">
							
							<!-- Panel 1: Theme & Hero Banner -->
							<div class="omnify-modern-design-panel is-active" id="omnify-design-panel-theme">
								<?php omnify_render_section_title(__('Brand & Theme', 'omnifywp-ecommerce'), __('Set the visual foundation used by storefront buttons, links, cards, filters, and inputs.', 'omnifywp-ecommerce')); ?>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="design_primary_color"><?php esc_html_e('Primary Theme Color', 'omnifywp-ecommerce'); ?></label>
										<input type="color" id="design_primary_color" name="design_primary_color" value="<?php echo esc_attr($omnify_settings['design_primary_color'] ?? '#6366f1'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="design_hover_color"><?php esc_html_e('Primary Hover Color', 'omnifywp-ecommerce'); ?></label>
										<input type="color" id="design_hover_color" name="design_hover_color" value="<?php echo esc_attr($omnify_settings['design_hover_color'] ?? '#4f46e5'); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="design_border_radius"><?php esc_html_e('Border Radius (px)', 'omnifywp-ecommerce'); ?></label>
										<input type="number" min="0" max="40" id="design_border_radius" name="design_border_radius" value="<?php echo esc_attr($omnify_settings['design_border_radius'] ?? 8); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="design_font_family"><?php esc_html_e('Font Family', 'omnifywp-ecommerce'); ?></label>
										<select id="design_font_family" name="design_font_family">
											<option value="'Inter', system-ui, -apple-system, sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', "'Inter', system-ui, -apple-system, sans-serif"); ?>><?php esc_html_e('Inter (Modern)', 'omnifywp-ecommerce'); ?></option>
											<option value="'Outfit', sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', "'Outfit', sans-serif"); ?>><?php esc_html_e('Outfit (Premium)', 'omnifywp-ecommerce'); ?></option>
											<option value="system-ui, -apple-system, sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', 'system-ui, -apple-system, sans-serif'); ?>><?php esc_html_e('System', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
								</div>

								<!-- Hero Welcome Banner -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Storefront Hero Welcome Section', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Hero Banner', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_hero', ! isset($omnify_settings['storefront_show_hero']) || ! empty($omnify_settings['storefront_show_hero']) ? 1 : 0); ?>
									</div>
									<div class="omnify-modern-form-group" data-cond-field="storefront_show_hero" data-cond-val="1">
										<label for="storefront_hero_wave_color"><?php esc_html_e('Wave Highlight Color', 'omnifywp-ecommerce'); ?></label>
										<input type="color" id="storefront_hero_wave_color" name="storefront_hero_wave_color" value="<?php echo esc_attr($omnify_settings['storefront_hero_wave_color'] ?? '#e11d48'); ?>" />
									</div>
								</div>

								<div data-cond-field="storefront_show_hero" data-cond-val="1">
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="storefront_hero_title"><?php esc_html_e('Hero Title', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="storefront_hero_title" name="storefront_hero_title" value="<?php echo esc_attr($omnify_settings['storefront_hero_title'] ?? 'Storefront'); ?>" />
										</div>
									</div>

									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="storefront_hero_desc"><?php esc_html_e('Hero Subtitle / Description', 'omnifywp-ecommerce'); ?></label>
											<textarea id="storefront_hero_desc" name="storefront_hero_desc" rows="2"><?php echo esc_textarea($omnify_settings['storefront_hero_desc'] ?? 'High quality products to build, grow and succeed.'); ?></textarea>
										</div>
									</div>
								</div>

								<!-- Bottom Trust Features Bar -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Bottom Features Bar', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group" style="grid-column: span 2;">
										<label><?php esc_html_e('Show Bottom Features Bar', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_features_bar', ! isset($omnify_settings['storefront_show_features_bar']) || ! empty($omnify_settings['storefront_show_features_bar']) ? 1 : 0); ?>
									</div>
								</div>

								<div data-cond-field="storefront_show_features_bar" data-cond-val="1" style="margin-top: 15px;">
									<?php for ($i = 1; $i <= 4; $i++) : 
										$icon_key = "storefront_feature_{$i}_icon";
										$title_key = "storefront_feature_{$i}_title";
										$desc_key = "storefront_feature_{$i}_desc";
										$show_key = "storefront_feature_{$i}_show";
										
										$f_icon = $omnify_settings[$icon_key] ?? 'shield';
										$f_title = $omnify_settings[$title_key] ?? '';
										$f_desc = $omnify_settings[$desc_key] ?? '';
										$f_show = ! isset($omnify_settings[$show_key]) || ! empty($omnify_settings[$show_key]);
									?>
										<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #fafafa; margin-bottom: 8px;">
											<div class="omnify-modern-form-row">
												<div class="omnify-modern-form-group">
													<label style="font-weight: 500; color: #0f172a;"><?php echo esc_html(sprintf(__('Feature Trigger #%d', 'omnifywp-ecommerce'), $i)); ?></label>
												</div>
												<div class="omnify-modern-form-group">
													<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
													<?php omnify_render_toggle($show_key, $f_show ? 1 : 0); ?>
												</div>
											</div>
											<div class="omnify-modern-form-row" style="margin-top: 10px;">
												<div class="omnify-modern-form-group">
													<label for="<?php echo esc_attr($icon_key); ?>"><?php esc_html_e('Feature Icon', 'omnifywp-ecommerce'); ?></label>
													<select id="<?php echo esc_attr($icon_key); ?>" name="<?php echo esc_attr($icon_key); ?>">
														<option value="shield" <?php selected($f_icon, 'shield'); ?>><?php esc_html_e('Shield (Security)', 'omnifywp-ecommerce'); ?></option>
														<option value="cloud" <?php selected($f_icon, 'cloud'); ?>><?php esc_html_e('Cloud (Downloads)', 'omnifywp-ecommerce'); ?></option>
														<option value="star" <?php selected($f_icon, 'star'); ?>><?php esc_html_e('Star (Rating)', 'omnifywp-ecommerce'); ?></option>
														<option value="truck" <?php selected($f_icon, 'truck'); ?>><?php esc_html_e('Truck (Delivery)', 'omnifywp-ecommerce'); ?></option>
														<option value="heart" <?php selected($f_icon, 'heart'); ?>><?php esc_html_e('Heart (Favorite)', 'omnifywp-ecommerce'); ?></option>
														<option value="cart" <?php selected($f_icon, 'cart'); ?>><?php esc_html_e('Shopping Cart', 'omnifywp-ecommerce'); ?></option>
													</select>
												</div>
												<div class="omnify-modern-form-group">
													<label for="<?php echo esc_attr($title_key); ?>"><?php esc_html_e('Item Title', 'omnifywp-ecommerce'); ?></label>
													<input type="text" id="<?php echo esc_attr($title_key); ?>" name="<?php echo esc_attr($title_key); ?>" value="<?php echo esc_attr($f_title); ?>" />
												</div>
												<div class="omnify-modern-form-group">
													<label for="<?php echo esc_attr($desc_key); ?>"><?php esc_html_e('Item Description', 'omnifywp-ecommerce'); ?></label>
													<input type="text" id="<?php echo esc_attr($desc_key); ?>" name="<?php echo esc_attr($desc_key); ?>" value="<?php echo esc_attr($f_desc); ?>" />
												</div>
											</div>
										</div>
									<?php endfor; ?>
								</div>
							</div>

							<!-- Panel 2: Catalog & Filters -->
							<div class="omnify-modern-design-panel" id="omnify-design-panel-catalog" style="display: none;">
								<?php omnify_render_section_title(__('Catalog & Filters', 'omnifywp-ecommerce'), __('Choose the default product browsing layout, products count, and search/attribute filters.', 'omnifywp-ecommerce')); ?>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="storefront_catalog_layout"><?php esc_html_e('Default Product View', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_catalog_layout" name="storefront_catalog_layout">
											<option value="grid" <?php selected($omnify_catalog_layout, 'grid'); ?>><?php esc_html_e('Grid cards', 'omnifywp-ecommerce'); ?></option>
											<option value="list" <?php selected($omnify_catalog_layout, 'list'); ?>><?php esc_html_e('List rows', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_products_per_page"><?php esc_html_e('Products Per Page', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="storefront_products_per_page" name="storefront_products_per_page" min="1" max="100" value="<?php echo esc_attr((string) ($omnify_settings['storefront_products_per_page'] ?? 12)); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="storefront_grid_columns"><?php esc_html_e('Grid Columns', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_grid_columns" name="storefront_grid_columns">
											<option value="2" <?php selected($omnify_storefront_grid_columns, 2); ?>><?php esc_html_e('2 Columns', 'omnifywp-ecommerce'); ?></option>
											<option value="3" <?php selected($omnify_storefront_grid_columns, 3); ?>><?php esc_html_e('3 Columns', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_grid_rows"><?php esc_html_e('Grid Rows (visible)', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="storefront_grid_rows" name="storefront_grid_rows" min="0" max="20" value="<?php echo esc_attr((string) ($omnify_settings['storefront_grid_rows'] ?? 0)); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="storefront_pagination_type"><?php esc_html_e('Pagination Style', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_pagination_type" name="storefront_pagination_type">
											<option value="classic" <?php selected($omnify_settings['storefront_pagination_type'] ?? 'classic', 'classic'); ?>><?php esc_html_e('Classic pagination', 'omnifywp-ecommerce'); ?></option>
											<option value="load_more" <?php selected($omnify_settings['storefront_pagination_type'] ?? '', 'load_more'); ?>><?php esc_html_e('Load More button', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_default_sort"><?php esc_html_e('Default Product Sorting', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_default_sort" name="storefront_default_sort">
											<option value="latest" <?php selected($omnify_settings['storefront_default_sort'] ?? 'latest', 'latest'); ?>><?php esc_html_e('Sort by latest', 'omnifywp-ecommerce'); ?></option>
											<option value="price_asc" <?php selected($omnify_settings['storefront_default_sort'] ?? 'latest', 'price_asc'); ?>><?php esc_html_e('Price: low to high', 'omnifywp-ecommerce'); ?></option>
											<option value="price_desc" <?php selected($omnify_settings['storefront_default_sort'] ?? 'latest', 'price_desc'); ?>><?php esc_html_e('Price: high to low', 'omnifywp-ecommerce'); ?></option>
											<option value="name_asc" <?php selected($omnify_settings['storefront_default_sort'] ?? 'latest', 'name_asc'); ?>><?php esc_html_e('Name: A to Z', 'omnifywp-ecommerce'); ?></option>
											<option value="name_desc" <?php selected($omnify_settings['storefront_default_sort'] ?? 'latest', 'name_desc'); ?>><?php esc_html_e('Name: Z to A', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
								</div>

								<!-- Filter Panel Settings -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Filter Panel Settings', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Filter Panel', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_filters', ! array_key_exists('storefront_show_filters', $omnify_settings) || ! empty($omnify_settings['storefront_show_filters']) ? 1 : 0); ?>
									</div>
									<div class="omnify-modern-form-group" data-cond-field="storefront_show_filters" data-cond-val="1">
										<label for="storefront_filter_position"><?php esc_html_e('Filter Position', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_filter_position" name="storefront_filter_position">
											<option value="top" <?php selected($omnify_filter_position, 'top'); ?>><?php esc_html_e('Top toolbar', 'omnifywp-ecommerce'); ?></option>
											<option value="left" <?php selected($omnify_filter_position, 'left'); ?>><?php esc_html_e('Left sidebar', 'omnifywp-ecommerce'); ?></option>
											<option value="right" <?php selected($omnify_filter_position, 'right'); ?>><?php esc_html_e('Right sidebar', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
								</div>

								<div data-cond-field="storefront_show_filters" data-cond-val="1">
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Search Field', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('storefront_show_search', ! array_key_exists('storefront_show_search', $omnify_settings) || ! empty($omnify_settings['storefront_show_search']) ? 1 : 0); ?>
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Sort Dropdown', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('storefront_show_sort', ! array_key_exists('storefront_show_sort', $omnify_settings) || ! empty($omnify_settings['storefront_show_sort']) ? 1 : 0); ?>
										</div>
									</div>

									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group">
											<label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: #0f172a; margin-top: 5px;">
												<input type="checkbox" class="omnify-checkbox" name="storefront_filters[]" value="category" <?php checked(in_array('category', $omnify_active_filters, true)); ?> />
												<span><?php esc_html_e('Category Filter', 'omnifywp-ecommerce'); ?></span>
											</label>
										</div>
										<div class="omnify-modern-form-group">
											<label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: #0f172a; margin-top: 5px;">
												<input type="checkbox" class="omnify-checkbox" name="storefront_filters[]" value="type" <?php checked(in_array('type', $omnify_active_filters, true)); ?> />
												<span><?php esc_html_e('Product Type Filter', 'omnifywp-ecommerce'); ?></span>
											</label>
										</div>
									</div>

									<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
										<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Attribute Filters Settings', 'omnifywp-ecommerce'); ?></h4>
									</div>

									<?php
									$omnify_global_attrs = get_option('omnify_global_attributes', []);
									if (empty($omnify_global_attrs)) :
									?>
										<div style="grid-column: span 2; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 8px; text-align: center; color: #64748b;">
											<?php echo wp_kses_post(__('Create attributes first in the Attributes tab to use them as storefront filters.', 'omnifywp-ecommerce')); ?>
										</div>
									<?php else : ?>
										<?php foreach ($omnify_global_attrs as $omnify_attr) :
											$omnify_attr_slug = sanitize_title($omnify_attr['name']);
											$omnify_attr_label = $omnify_attr['name'];
											$attr_active = in_array('attr_' . $omnify_attr_slug, $omnify_active_filters, true);
										?>
											<div class="omnify-modern-form-row" style="margin-bottom: 10px;">
												<div class="omnify-modern-form-group">
													<strong style="color: #0f172a;"><?php echo esc_html($omnify_attr_label); ?></strong>
													<span class="help-text"><?php echo esc_html(sprintf(__('Options: %s', 'omnifywp-ecommerce'), implode(', ', (array) ($omnify_attr['options'] ?? [])))); ?></span>
												</div>
												<div class="omnify-modern-form-group" style="align-items: flex-start; justify-content: center;">
													<label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: #0f172a; margin-top: 5px;">
														<input type="checkbox" class="omnify-checkbox" name="storefront_filters[]" value="<?php echo esc_attr('attr_' . $omnify_attr_slug); ?>" <?php checked($attr_active); ?> />
														<span><?php esc_html_e('Enable as Filter', 'omnifywp-ecommerce'); ?></span>
													</label>
												</div>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div>

							<!-- Panel 3: Product Display & Urgency -->
							<div class="omnify-modern-design-panel" id="omnify-design-panel-product" style="display: none;">
								<?php omnify_render_section_title(__('Product Display & Reassurances', 'omnifywp-ecommerce'), __('Customize product card styling, quick cart interactions, and single product page reassurances.', 'omnifywp-ecommerce')); ?>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="storefront_card_style"><?php esc_html_e('Product Card Style', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_card_style" name="storefront_card_style">
											<option value="standard" <?php selected($omnify_storefront_card_style, 'standard'); ?>><?php esc_html_e('Standard', 'omnifywp-ecommerce'); ?></option>
											<option value="compact" <?php selected($omnify_storefront_card_style, 'compact'); ?>><?php esc_html_e('Compact', 'omnifywp-ecommerce'); ?></option>
											<option value="spacious" <?php selected($omnify_storefront_card_style, 'spacious'); ?>><?php esc_html_e('Spacious', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Ratings & Reviews', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('design_show_reviews', $omnify_settings['design_show_reviews'] ?? 1); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Add to Cart on Cards', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_add_to_cart', ! isset($omnify_settings['storefront_show_add_to_cart']) || ! empty($omnify_settings['storefront_show_add_to_cart']) ? 1 : 0); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Wishlist Option', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_wishlist', ! isset($omnify_settings['storefront_show_wishlist']) || ! empty($omnify_settings['storefront_show_wishlist']) ? 1 : 0); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Product SKU', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_sku', ! empty($omnify_settings['storefront_show_sku']) ? 1 : 0); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Breadcrumbs Navigation', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_breadcrumbs', ! isset($omnify_settings['storefront_show_breadcrumbs']) || ! empty($omnify_settings['storefront_show_breadcrumbs']) ? 1 : 0); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Compare Option', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_compare', ! isset($omnify_settings['storefront_show_compare']) || ! empty($omnify_settings['storefront_show_compare']) ? 1 : 0); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Recently Viewed Products', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_recently_viewed', ! isset($omnify_settings['storefront_show_recently_viewed']) || ! empty($omnify_settings['storefront_show_recently_viewed']) ? 1 : 0); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group" data-cond-field="storefront_show_recently_viewed" data-cond-val="1">
										<label for="storefront_recently_viewed_count"><?php esc_html_e('Recently Viewed Count', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="storefront_recently_viewed_count" name="storefront_recently_viewed_count" min="1" max="20" value="<?php echo esc_attr((string) ($omnify_settings['storefront_recently_viewed_count'] ?? 5)); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_related_count"><?php esc_html_e('Related Products Display Count', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="storefront_related_count" name="storefront_related_count" min="0" max="12" value="<?php echo esc_attr((string) ($omnify_settings['storefront_related_count'] ?? 4)); ?>" />
									</div>
								</div>

								<!-- Reassurance Checklist Customization -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Reassurance Checklist Customization', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_1_title"><?php esc_html_e('Secure Checkout: Title', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_1_title" name="storefront_reassurance_1_title" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_1_title'] ?? 'Secure Checkout'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_1_desc"><?php esc_html_e('Secure Checkout: Description', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_1_desc" name="storefront_reassurance_1_desc" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_1_desc'] ?? 'SSL encryption'); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_2_title"><?php esc_html_e('Instant Download: Title', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_2_title" name="storefront_reassurance_2_title" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_2_title'] ?? 'Instant Download'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_2_desc"><?php esc_html_e('Instant Download: Description', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_2_desc" name="storefront_reassurance_2_desc" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_2_desc'] ?? 'Get your files immediately'); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_3_title"><?php esc_html_e('24/7 Support: Title', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_3_title" name="storefront_reassurance_3_title" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_3_title'] ?? '24/7 Support'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="storefront_reassurance_3_desc"><?php esc_html_e('24/7 Support: Description', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="storefront_reassurance_3_desc" name="storefront_reassurance_3_desc" value="<?php echo esc_attr($omnify_settings['storefront_reassurance_3_desc'] ?? "We're here to help"); ?>" />
									</div>
								</div>

								<!-- Cart Urgency Urgency Scarcity & Free Shipping -->
								<div style="grid-column: span 2; margin-top: 30px; border-top: 1px solid #edf2f6; padding-top: 25px; margin-bottom: 10px;">
									<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Cart Scarcity & Free Shipping', 'omnifywp-ecommerce'); ?></h4>
								</div>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Enable Scarcity Countdown Timer', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('cart_countdown_enabled', $omnify_settings['cart_countdown_enabled'] ?? 0); ?>
									</div>
									<div class="omnify-modern-form-group" data-cond-field="cart_countdown_enabled" data-cond-val="1">
										<label for="cart_countdown_duration"><?php esc_html_e('Countdown Duration (minutes)', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="cart_countdown_duration" name="cart_countdown_duration" min="1" max="1440" value="<?php echo esc_attr((string) ($omnify_settings['cart_countdown_duration'] ?? 7)); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Enable Free Shipping Progress Bar', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('cart_free_shipping_enabled', $omnify_settings['cart_free_shipping_enabled'] ?? 0); ?>
									</div>
									<div class="omnify-modern-form-group" data-cond-field="cart_free_shipping_enabled" data-cond-val="1">
										<label for="cart_free_shipping_threshold"><?php esc_html_e('Free Shipping Threshold', 'omnifywp-ecommerce'); ?></label>
										<input type="number" step="0.01" min="0" id="cart_free_shipping_threshold" name="cart_free_shipping_threshold" value="<?php echo esc_attr((string) ($omnify_settings['cart_free_shipping_threshold'] ?? 200)); ?>" />
									</div>
								</div>
							</div>

						</div>
						<?php omnify_render_form_footer(); ?>
					</form>

				<?php elseif ($omnify_active_section === 'email') : ?>
					<!-- ══ EMAIL TEMPLATES ════════════════════════════════ -->
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
						<input type="hidden" name="action" value="omnify_save_settings" />
						<input type="hidden" name="section" value="email" />

						<!-- Carry over settings parameters -->
						<input type="hidden" name="store_name" value="<?php echo esc_attr($omnify_settings['store_name'] ?? ''); ?>" />
						<input type="hidden" name="default_currency" value="<?php echo esc_attr($omnify_settings['default_currency'] ?? ''); ?>" />
						<input type="hidden" name="tax_rate" value="<?php echo esc_attr($omnify_settings['tax_rate'] ?? 0); ?>" />
						<?php if (! empty($omnify_settings['test_mode'])) : ?><input type="hidden" name="test_mode" value="1"><?php endif; ?>

						<div class="omnify-modern-card-body">
							<?php omnify_render_section_title(__('Email Templates', 'omnifywp-ecommerce'), __('Configure transactional outgoing SMTP configurations and templates.', 'omnifywp-ecommerce')); ?>

							<!-- Sender Details -->
							<div style="grid-column: span 2; margin-top: 10px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Sender Details', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="email_from_name"><?php esc_html_e('From Name', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($omnify_settings['email_from_name'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label for="email_from_address"><?php esc_html_e('From Email', 'omnifywp-ecommerce'); ?></label>
									<input type="email" id="email_from_address" name="email_from_address" value="<?php echo esc_attr($omnify_settings['email_from_address'] ?? ''); ?>" />
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group">
									<label for="email_reply_to"><?php esc_html_e('Reply-To Email', 'omnifywp-ecommerce'); ?></label>
									<input type="email" id="email_reply_to" name="email_reply_to" value="<?php echo esc_attr($omnify_settings['email_reply_to'] ?? ''); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Enable all email notifications', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('email_notifications_enabled', ! array_key_exists('email_notifications_enabled', $omnify_settings) || ! empty($omnify_settings['email_notifications_enabled']) ? 1 : 0); ?>
								</div>
							</div>

							<!-- Customer transactional emails trigger editor list -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Customer Transactional Emails', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<!-- Order Receipt -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Order receipt after successful payment', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent to customers after a paid order is completed.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_receipt', $omnify_settings['email_receipt'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_receipt" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_receipt_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_receipt_subject" name="email_receipt_subject" value="<?php echo esc_attr($omnify_settings['email_receipt_subject'] ?? '[{site_name}] Order Receipt {order_id}'); ?>" />
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Include download links', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('email_download_links', $omnify_settings['email_download_links'] ?? 1); ?>
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_receipt_intro"><?php esc_html_e('Message intro template', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_receipt_intro" name="email_receipt_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_receipt_intro'] ?? 'Thank you for your purchase! Your order has been completed successfully.'); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {order_id}, {customer_email}, {order_total}, {order_status}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Manual payment pending -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Manual payment instructions', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent when an order is waiting for bank transfer, cheque, COD, or manual payment.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_payment_pending', $omnify_settings['email_payment_pending'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_payment_pending" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_payment_pending_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_payment_pending_subject" name="email_payment_pending_subject" value="<?php echo esc_attr($omnify_settings['email_payment_pending_subject'] ?? '[{site_name}] Order {order_id} — Awaiting Payment'); ?>" />
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_payment_pending_intro"><?php esc_html_e('Message template text', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_payment_pending_intro" name="email_payment_pending_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_payment_pending_intro'] ?? 'Thank you for your order! It is awaiting payment.'); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {order_id}, {customer_email}, {order_total}, {order_status}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Refund processed -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Refund processed', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent to customers after a refund is marked as processed.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_refund_processed', $omnify_settings['email_refund_processed'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_refund_processed" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_refund_processed_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_refund_processed_subject" name="email_refund_processed_subject" value="<?php echo esc_attr($omnify_settings['email_refund_processed_subject'] ?? '[{site_name}] Refund processed for Order {order_id}'); ?>" />
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_refund_processed_body"><?php esc_html_e('Message text', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_refund_processed_body" name="email_refund_processed_body" rows="3"><?php echo esc_textarea($omnify_settings['email_refund_processed_body'] ?? "Dear Customer,\n\nYour refund of {refund_amount} for Order {order_id} has been processed successfully.\n\nThank you."); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {order_id}, {customer_email}, {order_total}, {refund_amount}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Order status changed triggers -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Order status changed', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent to customers when an order status changes.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_status_changed', $omnify_settings['email_status_changed'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_status_changed" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_status_changed_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_status_changed_subject" name="email_status_changed_subject" value="<?php echo esc_attr($omnify_settings['email_status_changed_subject'] ?? '[{site_name}] Order {order_id} status updated'); ?>" />
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_status_changed_body"><?php esc_html_e('Message text', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_status_changed_body" name="email_status_changed_body" rows="3"><?php echo esc_textarea($omnify_settings['email_status_changed_body'] ?? 'Your order status changed from {old_status} to {new_status}.'); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {order_id}, {customer_email}, {order_total}, {order_status}, {old_status}, {new_status}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
									<div style="margin-top: 15px; display: flex; gap: 16px; flex-wrap: wrap;">
										<label style="display: flex; align-items: center; gap: 6px;"><input type="checkbox" name="email_order_completed" value="1" <?php checked($omnify_settings['email_order_completed'] ?? true); ?> /> Completed</label>
										<label style="display: flex; align-items: center; gap: 6px;"><input type="checkbox" name="email_order_shipped" value="1" <?php checked($omnify_settings['email_order_shipped'] ?? true); ?> /> Shipped</label>
										<label style="display: flex; align-items: center; gap: 6px;"><input type="checkbox" name="email_order_processing" value="1" <?php checked($omnify_settings['email_order_processing'] ?? true); ?> /> Processing</label>
										<label style="display: flex; align-items: center; gap: 6px;"><input type="checkbox" name="email_order_cancelled" value="1" <?php checked($omnify_settings['email_order_cancelled'] ?? true); ?> /> Cancelled</label>
									</div>
									<div style="margin-top: 15px; border-top: 1px solid #edf2f6; padding-top: 15px;">
										<details>
											<summary style="cursor: pointer; font-weight: 600; color: #136c5f;"><?php esc_html_e('Advanced: Specific templates per status', 'omnifywp-ecommerce'); ?></summary>
											<div style="margin-top: 15px; display: flex; flex-direction: column; gap: 12px;">
												<?php foreach (['completed' => 'Completed', 'shipped' => 'Shipped', 'processing' => 'Processing', 'cancelled' => 'Cancelled'] as $omnify_st => $omnify_label): ?>
													<div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #fafafa;">
														<strong><?php echo esc_html($omnify_label); ?>:</strong>
														<input type="text" name="email_order_<?php echo esc_attr($omnify_st); ?>_subject" value="<?php echo esc_attr($omnify_settings['email_order_' . $omnify_st . '_subject'] ?? ''); ?>" style="width: 100%; margin: 8px 0;" placeholder="Subject override" />
														<textarea name="email_order_<?php echo esc_attr($omnify_st); ?>_body" rows="2" style="width: 100%;" placeholder="Body override text"><?php echo esc_textarea($omnify_settings['email_order_' . $omnify_st . '_body'] ?? ''); ?></textarea>
													</div>
												<?php endforeach; ?>
											</div>
										</details>
									</div>
								</div>
							</div>

							<!-- Abandoned cart recovery trigger -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Abandoned cart recovery email', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent when a captured checkout session qualifies for recovery.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_abandoned_cart', $omnify_settings['email_abandoned_cart'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_abandoned_cart" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_abandoned_cart_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_abandoned_cart_subject" name="email_abandoned_cart_subject" value="<?php echo esc_attr($omnify_settings['email_abandoned_cart_subject'] ?? '[{site_name}] Complete your order'); ?>" />
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_abandoned_cart_intro"><?php esc_html_e('Message text', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_abandoned_cart_intro" name="email_abandoned_cart_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_abandoned_cart_intro'] ?? 'You left something in your cart. Use the button below to return to checkout and complete your order.'); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {customer_name}, {customer_email}, {product_name}, {cart_total}, {coupon_code}, {recovery_url}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Admin Alerts -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Admin Emails & Alerts', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label for="admin_notification_email"><?php esc_html_e('Admin Recipient Address', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="admin_notification_email" name="admin_notification_email" value="<?php echo esc_attr($omnify_settings['admin_notification_email'] ?? get_option('admin_email')); ?>" />
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Browser Push Notifications', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('push_notifications_enabled', $omnify_settings['push_notifications_enabled'] ?? 1); ?>
								</div>
							</div>

							<!-- Admin trigger alerts edit -->
							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('New order notification', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Notify store admins when a new order is placed.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_admin_new_order', $omnify_settings['email_admin_new_order'] ?? 1); ?>
									</div>
								</div>
							</div>

							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Refund requested alert', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sent after a customer submits a refund request.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_refund_requested', $omnify_settings['email_refund_requested'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_refund_requested" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_refund_requested_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_refund_requested_subject" name="email_refund_requested_subject" value="<?php echo esc_attr($omnify_settings['email_refund_requested_subject'] ?? '[{site_name}] Refund requested for Order {order_id}'); ?>" />
										</div>
									</div>
									<div class="omnify-modern-form-row" style="margin-top: 15px;">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_refund_requested_body"><?php esc_html_e('Message body template', 'omnifywp-ecommerce'); ?></label>
											<textarea id="email_refund_requested_body" name="email_refund_requested_body" rows="3"><?php echo esc_textarea($omnify_settings['email_refund_requested_body'] ?? "Dear Customer,\n\nYour refund request for Order {order_id} has been received and is currently under review.\n\nReason: {refund_reason}\n\nWe will update you shortly."); ?></textarea>
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {order_id}, {customer_email}, {order_total}, {refund_reason}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<div style="grid-column: span 2; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #fff; margin-bottom: 15px;">
								<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
									<div class="omnify-modern-form-group">
										<strong style="font-size: 15px; color: #0f172a;"><?php esc_html_e('Low-stock alert', 'omnifywp-ecommerce'); ?></strong>
										<span class="help-text" style="margin-top: 4px; display: block;"><?php esc_html_e('Sends when stock falls below the low-stock checkout threshold.', 'omnifywp-ecommerce'); ?></span>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_low_stock', $omnify_settings['email_low_stock'] ?? 1); ?>
									</div>
								</div>
								<div style="margin-top: 15px;" data-cond-field="email_low_stock" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group" style="grid-column: span 2;">
											<label for="email_low_stock_subject"><?php esc_html_e('Subject', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_low_stock_subject" name="email_low_stock_subject" value="<?php echo esc_attr($omnify_settings['email_low_stock_subject'] ?? '[{site_name}] Low stock alert: {product_name}'); ?>" />
											<span class="help-text"><?php esc_html_e('Tokens: {site_name}, {product_name}, {variation}, {sku}, {stock_qty}, {threshold}', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Advanced Email Options -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Advanced Email Options', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('BCC Admin on Customer Emails', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('email_bcc_admin', $omnify_settings['email_bcc_admin'] ?? 0); ?>
								</div>
								<div class="omnify-modern-form-group">
									<label><?php esc_html_e('Send Admin Copy of Receipts', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('email_send_receipt_copy', ! array_key_exists('email_send_receipt_copy', $omnify_settings) || ! empty($omnify_settings['email_send_receipt_copy']) ? 1 : 0); ?>
								</div>
							</div>

							<div class="omnify-modern-form-row" style="margin-top: 15px;">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label><?php esc_html_e('Include Order Summary in Status Update Emails', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('email_status_include_details', ! array_key_exists('email_status_include_details', $omnify_settings) || ! empty($omnify_settings['email_status_include_details']) ? 1 : 0); ?>
								</div>
							</div>

							<!-- SMTP Configuration Details -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('SMTP Configuration', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label><?php esc_html_e('Enable Custom SMTP', 'omnifywp-ecommerce'); ?></label>
									<?php omnify_render_toggle('email_smtp_enabled', $omnify_settings['email_smtp_enabled'] ?? 0); ?>
								</div>
							</div>

							<div class="omnify-smtp-fields" data-cond-field="email_smtp_enabled" data-cond-val="1">
								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="email_smtp_host"><?php esc_html_e('SMTP Host', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_smtp_host" name="email_smtp_host" value="<?php echo esc_attr($omnify_settings['email_smtp_host'] ?? ''); ?>" placeholder="smtp.example.com" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_smtp_port"><?php esc_html_e('SMTP Port', 'omnifywp-ecommerce'); ?></label>
										<input type="number" id="email_smtp_port" name="email_smtp_port" value="<?php echo esc_attr((string) ($omnify_settings['email_smtp_port'] ?? 587)); ?>" />
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="email_smtp_encryption"><?php esc_html_e('SMTP Encryption', 'omnifywp-ecommerce'); ?></label>
										<select id="email_smtp_encryption" name="email_smtp_encryption">
											<option value="" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), ''); ?>><?php esc_html_e('None', 'omnifywp-ecommerce'); ?></option>
											<option value="ssl" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), 'ssl'); ?>><?php esc_html_e('SSL', 'omnifywp-ecommerce'); ?></option>
											<option value="tls" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), 'tls'); ?>><?php esc_html_e('TLS (recommended)', 'omnifywp-ecommerce'); ?></option>
										</select>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Use SMTP Authentication', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('email_smtp_auth', ! isset($omnify_settings['email_smtp_auth']) || ! empty($omnify_settings['email_smtp_auth']) ? 1 : 0); ?>
									</div>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label for="email_smtp_username"><?php esc_html_e('SMTP Username', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_smtp_username" name="email_smtp_username" value="<?php echo esc_attr($omnify_settings['email_smtp_username'] ?? ''); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_smtp_password"><?php esc_html_e('SMTP Password', 'omnifywp-ecommerce'); ?></label>
										<input type="password" id="email_smtp_password" name="email_smtp_password" autocomplete="new-password" placeholder="<?php echo ! empty($omnify_settings['email_smtp_password']) ? esc_attr('••••••••') : ''; ?>" />
									</div>
								</div>
							</div>

							<!-- Email Footer Text Area -->
							<div style="grid-column: span 2; margin-top: 30px; border-bottom: 1px solid #edf2f6; padding-bottom: 12px; margin-bottom: 10px;">
								<h4 style="font-weight: 500; color: #0f172a; margin: 0;"><?php esc_html_e('Email Footer Text', 'omnifywp-ecommerce'); ?></h4>
							</div>

							<div class="omnify-modern-form-row">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label for="email_footer_text"><?php esc_html_e('Footer Text', 'omnifywp-ecommerce'); ?></label>
									<textarea id="email_footer_text" name="email_footer_text" rows="3"><?php echo esc_textarea($omnify_settings['email_footer_text'] ?? ''); ?></textarea>
								</div>
							</div>

						</div>
						<?php omnify_render_form_footer(); ?>
					</form>

					<!-- Transactional Email Reliability / Log Logs Render below Email settings -->
					<div class="omnify-modern-card-body" style="border-top: 1px solid #edf2f6; margin-top: 20px; padding-top: 20px;">
						<div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
							<div>
								<h4 style="font-weight: 500; color: #0f172a; margin: 0; font-size: 16px;"><?php esc_html_e('Transactional Email Reliability Log', 'omnifywp-ecommerce'); ?></h4>
								<p style="font-size: 12.5px; color: #64748b; margin-top: 4px;"><?php esc_html_e('Monitor every attempted transactional email, preview content, resend messages, and catch delivery failures.', 'omnifywp-ecommerce'); ?></p>
							</div>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
								<?php wp_nonce_field('omnify_send_test_email', 'omnify_test_email_nonce'); ?>
								<input type="hidden" name="action" value="omnify_send_test_email" />
								<div style="min-width: 200px;">
									<input type="email" name="test_email_to" value="<?php echo esc_attr(get_option('admin_email')); ?>" placeholder="test@email.com" required style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; height: 35px;" />
								</div>
								<button type="submit" class="omnify-modern-btn omnify-modern-btn-primary" style="padding: 6px 16px !important; font-size: 13px !important; height: 35px; border-radius: 6px;">
									<?php esc_html_e('Send Test Email', 'omnifywp-ecommerce'); ?>
								</button>
							</form>
						</div>

						<?php if (empty($omnify_email_logs)) : ?>
							<div style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 35px; text-align: center; color: #64748b;">
								<strong style="display: block; color: #0f172a; margin-bottom: 6px; font-size: 14px;"><?php esc_html_e('No email activity yet', 'omnifywp-ecommerce'); ?></strong>
								<?php esc_html_e('Send a test email or wait for the next order event to create the first log entry.', 'omnifywp-ecommerce'); ?>
							</div>
						<?php else : ?>
							<div style="overflow-x: auto; width: 100%;">
								<table class="widefat striped" style="box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px;">
									<thead>
										<tr>
											<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Trigger', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Recipient', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Subject / Failure', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Date', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 150px;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($omnify_email_logs as $omnify_log) : ?>
											<?php
											$omnify_status = sanitize_key((string) ($omnify_log['status'] ?? 'pending'));
											$omnify_status_class = 'sent' === $omnify_status ? 'active' : ('failed' === $omnify_status ? 'cancelled' : 'pending');
											$omnify_created = ! empty($omnify_log['created_at']) ? strtotime((string) $omnify_log['created_at']) : 0;
											$omnify_resend_url = wp_nonce_url(
												admin_url('admin-post.php?action=omnify_resend_email_log&log_id=' . absint($omnify_log['id'])),
												'omnify_resend_email_log_' . absint($omnify_log['id'])
											);
											?>
											<tr>
												<td>
													<span class="omnify-modern-sub-tab <?php echo $omnify_status === 'sent' ? 'is-active' : ''; ?>" style="padding: 2px 8px !important; font-size: 11px !important; border-radius: 6px !important; display: inline-block !important; font-weight: 500 !important; cursor: default !important;">
														<?php echo esc_html(ucfirst($omnify_status)); ?>
													</span>
												</td>
												<td>
													<strong><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $omnify_log['trigger_name']))); ?></strong>
													<?php if (! empty($omnify_log['order_id'])) : ?>
														<div style="font-size: 11px; color: #64748b; margin-top: 3px;">
															<?php echo esc_html(sprintf(__('Order #%d', 'omnifywp-ecommerce'), (int) $omnify_log['order_id'])); ?>
														</div>
													<?php endif; ?>
												</td>
												<td style="max-width: 180px; word-break: break-all;"><?php echo esc_html((string) $omnify_log['recipient']); ?></td>
												<td style="min-width: 200px;">
													<div style="font-weight: 600; color: #0f172a;"><?php echo esc_html((string) $omnify_log['subject']); ?></div>
													<?php if (! empty($omnify_log['error_message'])) : ?>
														<div style="margin-top: 6px; color: #ef4444; font-size: 11.5px; line-height: 1.4;">
															<?php echo esc_html((string) $omnify_log['error_message']); ?>
														</div>
													<?php endif; ?>
												</td>
												<td>
													<?php echo $omnify_created ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $omnify_created)) : esc_html__('Unknown', 'omnifywp-ecommerce'); ?>
												</td>
												<td>
													<div style="display: flex; gap: 8px; align-items: center;">
														<a href="<?php echo esc_url($omnify_resend_url); ?>" class="omnify-modern-btn omnify-modern-btn-secondary" style="padding: 4px 10px !important; font-size: 11px !important; border-radius: 6px;">
															<?php esc_html_e('Resend', 'omnifywp-ecommerce'); ?>
														</a>
														<details style="position: relative;">
															<summary class="omnify-modern-btn omnify-modern-btn-secondary" style="list-style: none; cursor: pointer; padding: 4px 10px !important; font-size: 11px !important; border-radius: 6px;">
																<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
															</summary>
															<div style="position: absolute; right: 0; top: 100%; z-index: 1000; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); padding: 16px; width: 400px; max-height: 300px; overflow-y: auto;">
																<pre style="white-space: pre-wrap; font-family: monospace; font-size: 11px; margin: 0;"><?php echo esc_html((string) $omnify_log['body']); ?></pre>
															</div>
														</details>
													</div>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>

		</div><!-- /.omnify-modern-content-card -->
	</div><!-- /.omnify-modern-settings-layout -->
</div><!-- /.omnify-admin-wrapper -->
