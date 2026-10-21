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

$omnify_allowed_sections = ['general', 'pages', 'roles', 'payments', 'tax', 'delivery', 'checkout', 'fraud', 'marketing', 'design', 'email', 'tools'];
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
	'NPR' => 'NPR — Nepalese Rupee (रू)',
	'AED' => 'AED — UAE Dirham (د.إ)',
	'SAR' => 'SAR — Saudi Riyal (ر.س)',
	'SGD' => 'SGD — Singapore Dollar (S$)',
	'MYR' => 'MYR — Malaysia Ringgit (RM)',
	'NGN' => 'NGN — Nigerian Naira (₦)',
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

// Helper to render modern iOS switch toggle
function omnify_render_toggle($name, $value, $label = '') {
	$val = (int) $value;
	?>
	<div class="omnify-modern-toggle-wrapper">
		<label class="omnify-switch">
			<input type="hidden" name="<?php echo esc_attr($name); ?>" value="0" />
			<input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($val, 1); ?> />
			<span class="omnify-slider"></span>
		</label>
		<?php if ($label) : ?>
			<span class="help-text" style="display: block; margin-top: 6px; font-size: 12.5px; color: #64748b; line-height: 1.4;"><?php echo esc_html($label); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

// Helper to render modern compact color picker with hex input
function omnify_render_color_picker($name, $value, $default = '#0f766e') {
	$val = ! empty($value) ? $value : $default;
	?>
	<div class="omnify-color-picker-wrap">
		<input type="color" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($val); ?>" class="omnify-color-swatch" />
		<input type="text" class="omnify-color-hex-input" value="<?php echo esc_attr($val); ?>" maxlength="7" spellcheck="false" />
	</div>
	<?php
}

// Helper to render dynamic email template tokens
function omnify_render_token_pills($tokens) {
	?>
	<div class="omnify-token-pills-row">
		<span class="omnify-token-label"><?php esc_html_e('Available dynamic tokens (click to copy):', 'omnifywp-ecommerce'); ?></span>
		<div class="omnify-token-badges">
			<?php foreach ($tokens as $token) : ?>
				<button type="button" class="omnify-token-pill" data-token="<?php echo esc_attr($token); ?>" title="<?php esc_attr_e('Click to copy token', 'omnifywp-ecommerce'); ?>">
					<span><?php echo esc_html($token); ?></span>
					<span class="dashicons dashicons-admin-page" style="font-size: 11px; width: 11px; height: 11px; margin-left: 4px; opacity: 0.6;"></span>
				</button>
			<?php endforeach; ?>
		</div>
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

// Helper to render modern settings section divider
function omnify_render_section_divider($title, $subtext = '', $icon = '') {
	?>
	<div class="omnify-settings-section-divider">
		<div class="omnify-settings-section-divider__header">
			<?php if ($icon) : ?>
				<span class="omnify-settings-section-divider__icon"><span class="dashicons <?php echo esc_attr($icon); ?>"></span></span>
			<?php endif; ?>
			<div>
				<h4 class="omnify-settings-section-divider__title"><?php echo esc_html($title); ?></h4>
				<?php if ($subtext) : ?>
					<p class="omnify-settings-section-divider__subtext"><?php echo esc_html($subtext); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

// Helper to render modern SaaS toggle card
function omnify_render_toggle_card($name, $value, $title, $description = '', $badge = '', $badge_type = 'info') {
	$val = (int) $value;
	?>
	<div class="omnify-setting-toggle-card <?php echo $val ? 'is-active' : ''; ?>">
		<div class="omnify-setting-toggle-card__info">
			<div class="omnify-setting-toggle-card__title-row">
				<span class="omnify-setting-toggle-card__title"><?php echo esc_html($title); ?></span>
				<?php if ($badge) : ?>
					<span class="omnify-badge omnify-badge--<?php echo esc_attr($badge_type); ?>"><?php echo esc_html($badge); ?></span>
				<?php endif; ?>
			</div>
			<?php if ($description) : ?>
				<p class="omnify-setting-toggle-card__desc"><?php echo esc_html($description); ?></p>
			<?php endif; ?>
		</div>
		<div class="omnify-setting-toggle-card__control">
			<label class="omnify-switch">
				<input type="hidden" name="<?php echo esc_attr($name); ?>" value="0" />
				<input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($val, 1); ?> />
				<span class="omnify-slider"></span>
			</label>
		</div>
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
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Settings', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Manage your store configuration, payment gateways, delivery rules, and preferences.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<?php $omnify_storefront_url = function_exists('omnify_get_storefront_url') ? omnify_get_storefront_url() : home_url('/storefront/'); ?>
			<a href="<?php echo esc_url($omnify_storefront_url); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
				<?php esc_html_e('View Storefront', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url('https://omnifywp.com/doc/'); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<span class="dashicons dashicons-book-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
				<?php esc_html_e('Documentation', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<?php
	// Left Sidebar Category Navigation Items
	$omnify_sub_sections = [
		['id' => 'general', 'label' => __('General Settings', 'omnifywp-ecommerce'), 'icon' => 'dashicons-admin-settings', 'url' => omnify_settings_url('general'), 'actives' => ['general', 'pages', 'roles']],
		['id' => 'payments', 'label' => __('Payment Methods', 'omnifywp-ecommerce'), 'icon' => 'dashicons-money-alt', 'url' => omnify_settings_url('payments'), 'actives' => ['payments']],
		['id' => 'tax', 'label' => __('Tax Rules', 'omnifywp-ecommerce'), 'icon' => 'dashicons-calculator', 'url' => omnify_settings_url('tax'), 'actives' => ['tax']],
		['id' => 'delivery', 'label' => __('Delivery Zones', 'omnifywp-ecommerce'), 'icon' => 'dashicons-car', 'url' => omnify_settings_url('delivery'), 'actives' => ['delivery']],
		['id' => 'checkout', 'label' => __('Checkout Flow', 'omnifywp-ecommerce'), 'icon' => 'dashicons-cart', 'url' => omnify_settings_url('checkout'), 'actives' => ['checkout', 'fraud', 'marketing']],
		['id' => 'design', 'label' => __('Storefront Design', 'omnifywp-ecommerce'), 'icon' => 'dashicons-art', 'url' => omnify_settings_url('design'), 'actives' => ['design', 'email']]
	];
	?>

	<div class="omnify-modern-settings-layout">
		<!-- Left Sidebar Category Navigation -->
		<aside class="omnify-modern-sub-sidebar">
			<!-- Live Settings Search -->
			<div class="omnify-modern-settings-search" role="search">
				<span class="dashicons dashicons-search omnify-settings-search__icon" aria-hidden="true"></span>
				<input
					type="search"
					id="omnify-settings-search"
					class="omnify-settings-search__input"
					placeholder="<?php esc_attr_e('Search settings…', 'omnifywp-ecommerce'); ?>"
					aria-label="<?php esc_attr_e('Search settings on this page', 'omnifywp-ecommerce'); ?>"
					autocomplete="off"
					spellcheck="false"
				/>
				<span class="omnify-settings-search__count" aria-live="polite"></span>
				<button type="button" class="omnify-settings-search__clear" aria-label="<?php esc_attr_e('Clear search', 'omnifywp-ecommerce'); ?>" hidden>
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
				<div
					class="omnify-settings-search__results"
					id="omnify-settings-search-results"
					role="listbox"
					aria-label="<?php esc_attr_e('Matching settings', 'omnifywp-ecommerce'); ?>"
					hidden
				></div>
			</div>
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
					<span class="dashicons dashicons-phone" style="color: #059669;"></span>
					<?php esc_html_e('Need help?', 'omnifywp-ecommerce'); ?>
				</div>
				<p><?php esc_html_e('Learn more about store setup in our documentation.', 'omnifywp-ecommerce'); ?></p>
				<a href="https://omnifywp.com/doc/" target="_blank" rel="noopener">
					<?php esc_html_e('View Docs', 'omnifywp-ecommerce'); ?>
					<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px;"></span>
				</a>
			</div>
		</aside>

		<!-- Main Settings Form content -->
		<div class="omnify-modern-content-card">
			<div class="omnify-settings-search-empty" id="omnify-settings-search-empty" hidden>
				<span class="dashicons dashicons-search" aria-hidden="true"></span>
				<p><?php esc_html_e('No matching settings', 'omnifywp-ecommerce'); ?></p>
				<span class="omnify-settings-search-empty__hint"><?php esc_html_e('No settings on this page match. Pick a suggestion above to jump to another section.', 'omnifywp-ecommerce'); ?></span>
			</div>

			<?php if (in_array($omnify_active_section, ['general', 'pages', 'roles'], true)) : ?>
				<!-- GENERAL SETTINGS HUB -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('general')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'general' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-store"></span>
							<?php esc_html_e('Store Identity', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('pages')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'pages' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-admin-page"></span>
							<?php esc_html_e('Core Pages', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('roles')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'roles' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-admin-users"></span>
							<?php esc_html_e('Shop Roles', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>

				<?php if ($omnify_active_section === 'pages') : ?>
					<!-- NATIVE PAGES GENERATION PANEL -->
					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('Core Pages', 'omnifywp-ecommerce'), __('Omnify requires specific WordPress pages containing shortcodes. Generate them below or assign existing pages.', 'omnifywp-ecommerce')); ?>
						
						<div class="omnify-pages-grid">
							<?php
							$omnify_required_pages = [
								'storefront'      => ['label' => __('Storefront Page', 'omnifywp-ecommerce'),      'desc' => __('Main storefront catalog showcasing all available products.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_storefront]', 'icon' => 'dashicons-store'],
								'cart'            => ['label' => __('Cart Page', 'omnifywp-ecommerce'),            'desc' => __('Customer shopping bag with quantity and discount updates.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_cart]', 'icon' => 'dashicons-cart'],
								'checkout'        => ['label' => __('Checkout Page', 'omnifywp-ecommerce'),        'desc' => __('Seamless one-page payment and order completion.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_checkout]', 'icon' => 'dashicons-money-alt'],
								'customer_portal' => ['label' => __('Customer Portal', 'omnifywp-ecommerce'),      'desc' => __('Self-service portal for past orders and account details.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_customer_portal]', 'icon' => 'dashicons-admin-users'],
								'download_page'   => ['label' => __('Secure Download Page', 'omnifywp-ecommerce'), 'desc' => __('Protected link generator for purchased digital goods.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_download_page]', 'icon' => 'dashicons-download'],
								'order_tracking'  => ['label' => __('Order Tracking Page', 'omnifywp-ecommerce'),  'desc' => __('Live shipment tracking by order number and email.', 'omnifywp-ecommerce'), 'shortcode' => '[omnify_order_tracking]', 'icon' => 'dashicons-location-alt'],
							];
							foreach ($omnify_required_pages as $omnify_key => $omnify_page_info):
								$omnify_page_id = (int) ($omnify_settings['page_' . $omnify_key] ?? 0);
								if (! $omnify_page_id) {
									$omnify_found_pages = get_posts([
										'post_type'      => 'page',
										's'              => $omnify_page_info['shortcode'],
										'post_status'    => 'publish',
										'posts_per_page' => 1,
									]);
									if (! empty($omnify_found_pages) && isset($omnify_found_pages[0]->ID)) {
										$omnify_page_id = (int) $omnify_found_pages[0]->ID;
									} elseif ('storefront' === $omnify_key) {
										foreach (['storefront', 'shop', 'store'] as $omnify_try_slug) {
											$omnify_candidate = get_page_by_path($omnify_try_slug);
											if ($omnify_candidate instanceof \WP_Post && 'publish' === $omnify_candidate->post_status) {
												$omnify_page_id = (int) $omnify_candidate->ID;
												break;
											}
										}
									}
								}
								$omnify_page    = $omnify_page_id ? get_post($omnify_page_id) : null;
								$is_active      = ($omnify_page && $omnify_page->post_status === 'publish');
								$omnify_target_url = $is_active ? get_permalink($omnify_page->ID) : '';
							?>
							<div class="omnify-page-item-card <?php echo $is_active ? 'is-active' : 'is-unassigned'; ?>">
								<div class="omnify-page-item-card__icon">
									<span class="dashicons <?php echo esc_attr($omnify_page_info['icon']); ?>"></span>
								</div>
								<div class="omnify-page-item-card__main">
									<div class="omnify-page-item-card__header">
										<h4 class="omnify-page-item-card__title"><?php echo esc_html($omnify_page_info['label']); ?></h4>
										<?php if ($is_active): ?>
											<span class="omnify-badge omnify-badge--success">
												<span class="omnify-badge__dot"></span>
												<?php esc_html_e('Active', 'omnifywp-ecommerce'); ?>
											</span>
										<?php else: ?>
											<span class="omnify-badge omnify-badge--draft">
												<span class="omnify-badge__dot"></span>
												<?php esc_html_e('Not Set', 'omnifywp-ecommerce'); ?>
											</span>
										<?php endif; ?>
									</div>
									<p class="omnify-page-item-card__desc"><?php echo esc_html($omnify_page_info['desc']); ?></p>
									<div class="omnify-page-item-card__shortcode-row">
										<span class="omnify-page-item-card__shortcode-label"><?php esc_html_e('Shortcode:', 'omnifywp-ecommerce'); ?></span>
										<code class="omnify-code-pill"><?php echo esc_html($omnify_page_info['shortcode']); ?></code>
									</div>
									<?php if ($is_active && $omnify_target_url): ?>
										<div class="omnify-page-item-card__url-row" style="margin-top: 8px; font-size: 12px;">
											<span class="dashicons dashicons-admin-links" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle; color: #0d9488; margin-right: 2px;"></span>
											<a href="<?php echo esc_url($omnify_target_url); ?>" target="_blank" style="color: #0d9488; text-decoration: none; font-weight: 500; word-break: break-all;"><?php echo esc_html($omnify_target_url); ?></a>
										</div>
									<?php endif; ?>
								</div>
								<div class="omnify-page-item-card__actions">
									<?php if ($is_active): ?>
										<a href="<?php echo esc_url($omnify_target_url); ?>" target="_blank" class="omnify-button omnify-button--secondary omnify-button--sm">
											<span class="dashicons dashicons-external"></span>
											<?php esc_html_e('View', 'omnifywp-ecommerce'); ?>
										</a>
										<a href="<?php echo esc_url(get_edit_post_link($omnify_page->ID)); ?>" class="omnify-button omnify-button--secondary omnify-button--sm">
											<span class="dashicons dashicons-edit"></span>
											<?php esc_html_e('Edit', 'omnifywp-ecommerce'); ?>
										</a>
									<?php else: ?>
										<span class="omnify-page-item-card__missing-text"><?php esc_html_e('Will be auto-generated', 'omnifywp-ecommerce'); ?></span>
									<?php endif; ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>

						<div class="omnify-generate-pages-callout">
							<div class="omnify-generate-pages-callout__content">
								<div class="omnify-generate-pages-callout__icon">
									<span class="dashicons dashicons-admin-generic"></span>
								</div>
								<div>
									<h4><?php esc_html_e('Automated Page Setup', 'omnifywp-ecommerce'); ?></h4>
									<p><?php esc_html_e('Click below to automatically create any missing store pages and insert their required shortcodes.', 'omnifywp-ecommerce'); ?></p>
								</div>
							</div>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<?php wp_nonce_field('omnify_generate_pages', 'omnify_generate_pages_nonce'); ?>
								<input type="hidden" name="action" value="omnify_generate_pages" />
								<button type="submit" class="omnify-modern-btn omnify-modern-btn-primary">
									<span class="dashicons dashicons-plus-alt"></span>
									<?php esc_html_e('Generate All Required Pages', 'omnifywp-ecommerce'); ?>
								</button>
							</form>
						</div>
					</div>

				<?php else : ?>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
						<input type="hidden" name="action" value="omnify_save_settings" />
						<input type="hidden" name="section" value="<?php echo esc_attr($omnify_active_section); ?>" />

						<div class="omnify-modern-card-body">
							<?php if ($omnify_active_section === 'general') : ?>
								<?php omnify_render_section_title(__('Store Identity & Regional Market', 'omnifywp-ecommerce'), __('Configure core store identification, regional markets, and financial presentation parameters.', 'omnifywp-ecommerce')); ?>

								<!-- Section 1: Store Information -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Store Profile & Identity', 'omnifywp-ecommerce'), __('Basic identification details shown on customer receipts and store communications.', 'omnifywp-ecommerce'), 'dashicons-store'); ?>
									
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="store_name"><?php esc_html_e('Store Name', 'omnifywp-ecommerce'); ?> <span class="omnify-required-star" style="color: #ef4444; font-weight: 700;">*</span> <span class="dashicons dashicons-info" title="<?php esc_attr_e('Used in receipts, email notifications, and storefront header.', 'omnifywp-ecommerce'); ?>"></span></label>
											<input type="text" id="store_name" name="store_name" value="<?php echo esc_attr($omnify_settings['store_name']); ?>" required />
											<span class="help-text"><?php esc_html_e('Used in email receipts, notifications, and storefront.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="store_email"><?php esc_html_e('Store Email Address', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="<?php esc_attr_e('Sender reply address for customer order notifications.', 'omnifywp-ecommerce'); ?>"></span></label>
											<input type="email" id="store_email" name="store_email" value="<?php echo esc_attr($omnify_settings['store_email'] ?? get_option('admin_email')); ?>" />
											<span class="help-text"><?php esc_html_e('Reply-to address for outgoing order confirmations and customer receipts.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row" style="margin-top: 18px;">
										<div class="omnify-modern-form-group">
											<label for="store_url"><?php esc_html_e('Storefront URL', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="<?php esc_attr_e('Custom storefront catalog URL. Leave empty to use the auto-resolved Omnify Storefront page.', 'omnifywp-ecommerce'); ?>"></span></label>
											<input type="url" id="store_url" name="store_url" value="<?php echo esc_attr($omnify_settings['store_url'] ?? ''); ?>" placeholder="<?php echo esc_attr(function_exists('omnify_get_storefront_url') ? omnify_get_storefront_url() : home_url('/storefront/')); ?>" />
											<span class="help-text"><?php esc_html_e('Direct URL for your storefront catalog. If empty, Omnify dynamically links to your assigned storefront page.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row" style="margin-top: 18px;">
										<div class="omnify-modern-form-group">
											<label for="default_country"><?php esc_html_e('Base Country / Region', 'omnifywp-ecommerce'); ?></label>
											<select id="default_country" name="default_country" class="omnify-country-select omnify-searchable-select">
												<option value=""><?php esc_html_e('Select country...', 'omnifywp-ecommerce'); ?></option>
												<?php foreach ($omnify_countries as $code => $name) : ?>
													<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_settings['default_country'] ?? '', $code); ?>>
														<?php echo esc_html($name); ?>
													</option>
												<?php endforeach; ?>
											</select>
											<span class="help-text"><?php esc_html_e('The country where your business is based, used for default tax and delivery calculation.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="selling_locations"><?php esc_html_e('Selling Locations', 'omnifywp-ecommerce'); ?></label>
											<select id="selling_locations" name="selling_locations" class="omnify-searchable-select">
												<option value="all" <?php selected($omnify_settings['selling_locations'] ?? 'all', 'all'); ?>><?php esc_html_e('All Countries', 'omnifywp-ecommerce'); ?></option>
												<option value="specific" <?php selected($omnify_settings['selling_locations'] ?? 'all', 'specific'); ?>><?php esc_html_e('Specific Countries', 'omnifywp-ecommerce'); ?></option>
												<option value="all_except" <?php selected($omnify_settings['selling_locations'] ?? 'all', 'all_except'); ?>><?php esc_html_e('Exclude Specific Countries', 'omnifywp-ecommerce'); ?></option>
											</select>
											<span class="help-text"><?php esc_html_e('Choose which countries you accept customer orders and shipments from.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row" data-cond-field="selling_locations" data-cond-val="specific" style="grid-template-columns: 1fr !important; margin-top: 18px; <?php echo ($omnify_settings['selling_locations'] ?? 'all') === 'specific' ? '' : 'display: none !important;'; ?>">
										<div class="omnify-modern-form-group" style="grid-column: 1 / -1;">
											<label for="selling_countries"><?php esc_html_e('Specific Selling Countries', 'omnifywp-ecommerce'); ?></label>
											<select id="selling_countries" name="selling_countries[]" class="omnify-searchable-select" multiple placeholder="<?php esc_attr_e('Select countries...', 'omnifywp-ecommerce'); ?>">
												<?php foreach ($omnify_countries as $code => $name) : ?>
													<option value="<?php echo esc_attr($code); ?>" <?php echo in_array($code, (array) ($omnify_settings['selling_countries'] ?? []), true) ? 'selected' : ''; ?>>
														<?php echo esc_html($name); ?>
													</option>
												<?php endforeach; ?>
											</select>
											<span class="help-text"><?php esc_html_e('Select the specific countries you accept customer orders and shipments from.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row" data-cond-field="selling_locations" data-cond-val="all_except" style="grid-template-columns: 1fr !important; margin-top: 18px; <?php echo ($omnify_settings['selling_locations'] ?? 'all') === 'all_except' ? '' : 'display: none !important;'; ?>">
										<div class="omnify-modern-form-group" style="grid-column: 1 / -1;">
											<label for="selling_countries_exclude"><?php esc_html_e('Excluded Selling Countries', 'omnifywp-ecommerce'); ?></label>
											<select id="selling_countries_exclude" name="selling_countries_exclude[]" class="omnify-searchable-select" multiple placeholder="<?php esc_attr_e('Select countries to exclude...', 'omnifywp-ecommerce'); ?>">
												<?php foreach ($omnify_countries as $code => $name) : ?>
													<option value="<?php echo esc_attr($code); ?>" <?php echo in_array($code, (array) ($omnify_settings['selling_countries_exclude'] ?? []), true) ? 'selected' : ''; ?>>
														<?php echo esc_html($name); ?>
													</option>
												<?php endforeach; ?>
											</select>
											<span class="help-text"><?php esc_html_e('Select the countries you do not accept customer orders and shipments from.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>

								<!-- Section 2: Currency & Financial Formatting -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Currency & Price Presentation', 'omnifywp-ecommerce'), __('Configure default storefront currency and decimal formatting.', 'omnifywp-ecommerce'), 'dashicons-money-alt'); ?>

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
											<span class="help-text"><?php esc_html_e('The currency that prices are listed in and charged at checkout.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="currency_position"><?php esc_html_e('Currency Symbol Position', 'omnifywp-ecommerce'); ?></label>
											<select id="currency_position" name="currency_position">
												<option value="before" <?php selected($omnify_settings['currency_position'] ?? 'before', 'before'); ?>><?php esc_html_e('Before Amount ($99.99)', 'omnifywp-ecommerce'); ?></option>
												<option value="after" <?php selected($omnify_settings['currency_position'] ?? 'before', 'after'); ?>><?php esc_html_e('After Amount (99.99$)', 'omnifywp-ecommerce'); ?></option>
											</select>
											<span class="help-text"><?php esc_html_e('Placement of the currency symbol relative to numeric prices.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-form-grid-3" style="margin-top: 18px;">
										<div class="omnify-modern-form-group">
											<label for="price_decimals"><?php esc_html_e('Number of Decimals', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="price_decimals" name="price_decimals" min="0" max="4" value="<?php echo esc_attr((string) ($omnify_settings['price_decimals'] ?? 2)); ?>" />
											<span class="help-text"><?php esc_html_e('Decimal precision (e.g. 2 for $10.00).', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="thousand_separator"><?php esc_html_e('Thousand Separator', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="thousand_separator" name="thousand_separator" value="<?php echo esc_attr($omnify_settings['thousand_separator'] ?? ','); ?>" />
											<span class="help-text"><?php esc_html_e('Symbol separating thousands (e.g. ,).', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="decimal_separator"><?php esc_html_e('Decimal Separator', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="decimal_separator" name="decimal_separator" value="<?php echo esc_attr($omnify_settings['decimal_separator'] ?? '.'); ?>" />
											<span class="help-text"><?php esc_html_e('Symbol separating decimals (e.g. .).', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>

								<!-- Section 3: Sandbox / Test Mode -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Simulation & Testing Mode', 'omnifywp-ecommerce'), __('Manage sandbox processing for debugging and checkout validation.', 'omnifywp-ecommerce'), 'dashicons-admin-tools'); ?>
									
									<?php omnify_render_toggle_card(
										'test_mode',
										$omnify_settings['test_mode'] ?? 0,
										__('Enable Test Simulation Mode', 'omnifywp-ecommerce'),
										__('When test mode is active, customers can complete checkouts using mock payment gateways without real money transactions.', 'omnifywp-ecommerce'),
										($omnify_settings['test_mode'] ?? 0) ? __('Active Sandbox', 'omnifywp-ecommerce') : __('Live Store', 'omnifywp-ecommerce'),
										($omnify_settings['test_mode'] ?? 0) ? 'warning' : 'success'
									); ?>
								</div>

								<!-- Section 4: Navigation & Mini Cart -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Storefront Navigation & Mini Cart', 'omnifywp-ecommerce'), __('Configure the live shopping cart indicator in your website menu.', 'omnifywp-ecommerce'), 'dashicons-cart'); ?>

									<div class="omnify-settings-card-stack">
										<?php omnify_render_toggle_card(
											'mini_cart_menu_enabled',
											$omnify_settings['mini_cart_menu_enabled'] ?? 0,
											__('Attach Mini Cart to Navigation Menu', 'omnifywp-ecommerce'),
											__('Automatically inject a live shopping cart badge and item counter into your website navigation menu.', 'omnifywp-ecommerce')
										); ?>

										<div class="omnify-modern-form-row" data-cond-field="mini_cart_menu_enabled" data-cond-val="1" style="margin-top: 14px;">
											<div class="omnify-modern-form-group" style="grid-column: span 2;">
												<label for="mini_cart_menu_location"><?php esc_html_e('Theme Menu Location', 'omnifywp-ecommerce'); ?></label>
												<select id="mini_cart_menu_location" name="mini_cart_menu_location">
													<option value=""><?php esc_html_e('All / Any Menu Location', 'omnifywp-ecommerce'); ?></option>
													<?php
													$omnify_nav_menus = get_registered_nav_menus();
													foreach ($omnify_nav_menus as $omnify_loc => $omnify_loc_name) :
													?>
														<option value="<?php echo esc_attr($omnify_loc); ?>" <?php selected($omnify_settings['mini_cart_menu_location'] ?? '', $omnify_loc); ?>>
															<?php echo esc_html($omnify_loc_name . ' (' . $omnify_loc . ')'); ?>
														</option>
													<?php endforeach; ?>
												</select>
												<span class="help-text"><?php esc_html_e('Choose which registered navigation menu location displays the cart widget.', 'omnifywp-ecommerce'); ?></span>
											</div>
										</div>
									</div>
								</div>

								<!-- Section 5: Tax Calculation Options -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Tax Calculation Options', 'omnifywp-ecommerce'), __('Global tax settings applied to product catalog prices and delivery rates.', 'omnifywp-ecommerce'), 'dashicons-calculator'); ?>

									<div class="omnify-modern-form-row omnify-tax-defaults-inputs-grid">
										<div class="omnify-modern-form-group">
											<label for="gen_tax_rate"><?php esc_html_e('Default Store Tax Rate (%)', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix">
												<input type="number" step="0.0001" min="0" max="100" id="gen_tax_rate" name="tax_rate" value="<?php echo esc_attr((string) ($omnify_settings['tax_rate'] ?? 0)); ?>" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important; width: 100%;" />
												<span class="omnify-input-suffix">%</span>
											</div>
											<span class="help-text"><?php esc_html_e('Fallback tax rate applied when no destination-specific regional tax rule matches.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="gen_tax_label"><?php esc_html_e('Default Tax Label', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="gen_tax_label" name="tax_label" value="<?php echo esc_attr((string) ($omnify_settings['tax_label'] ?? 'Tax')); ?>" placeholder="<?php esc_attr_e('e.g. Sales Tax, VAT, GST', 'omnifywp-ecommerce'); ?>" style="width: 100%;" />
											<span class="help-text"><?php esc_html_e('The display name shown on cart, checkout, receipts, and invoices.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="tax_rounding"><?php esc_html_e('Tax Rounding Precision', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="<?php esc_attr_e('Choose how rounding is calculated.', 'omnifywp-ecommerce'); ?>"></span></label>
											<select id="tax_rounding" name="tax_rounding" style="width: 100%;">
												<option value="line" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'line'); ?>><?php esc_html_e('Round tax at line item level', 'omnifywp-ecommerce'); ?></option>
												<option value="subtotal" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'subtotal'); ?>><?php esc_html_e('Round tax at cart subtotal level', 'omnifywp-ecommerce'); ?></option>
											</select>
											<span class="help-text"><?php esc_html_e('Subtotal level rounding prevents 1-cent discrepancies when items have fractional tax rates.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-toggle-cards-grid omnify-tax-defaults-toggles-grid">
										<?php omnify_render_toggle_card(
											'prices_include_tax',
											$omnify_settings['prices_include_tax'] ?? 0,
											__('Prices Include Tax', 'omnifywp-ecommerce'),
											__('Catalog prices are treated as tax-inclusive across product displays and cart calculations.', 'omnifywp-ecommerce')
										); ?>

										<?php omnify_render_toggle_card(
											'tax_shipping',
											$omnify_settings['tax_shipping'] ?? 0,
											__('Charge Tax on Delivery', 'omnifywp-ecommerce'),
											__('When enabled, the matched delivery zone tax rate also applies to delivery shipping costs.', 'omnifywp-ecommerce')
										); ?>

										<?php omnify_render_toggle_card(
											'tax_reporting_enabled',
											$omnify_settings['tax_reporting_enabled'] ?? 0,
											__('Tax Reporting Codes', 'omnifywp-ecommerce'),
											__('Stores reporting codes on tax rules for export and accounting workflows.', 'omnifywp-ecommerce')
										); ?>
									</div>

									<div style="margin-top: 16px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
										<span style="font-size: 13px; color: #166534;"><?php esc_html_e('Looking to configure state or country-specific rates and VAT/GST rules?', 'omnifywp-ecommerce'); ?></span>
										<a href="<?php echo esc_url(omnify_settings_url('tax')); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="white-space: nowrap;">
											<?php esc_html_e('Manage Tax Rules &rarr;', 'omnifywp-ecommerce'); ?>
										</a>
									</div>
								</div>

							<?php elseif ($omnify_active_section === 'roles') : ?>
								<?php omnify_render_section_title(__('Shop Roles & Staff Permissions', 'omnifywp-ecommerce'), __('Manage WordPress capabilities, role-based dashboard access, and permissions matrix.', 'omnifywp-ecommerce')); ?>

								<!-- Minimum Access Capability Card -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Minimum Access Capability', 'omnifywp-ecommerce'), __('Choose which WordPress capability is required to access the Omnify admin area.', 'omnifywp-ecommerce'), 'dashicons-shield'); ?>

									<div class="omnify-capability-box">
										<div class="omnify-modern-form-group">
											<label for="admin_access_capability"><?php esc_html_e('Select Minimum Access Capability', 'omnifywp-ecommerce'); ?></label>
											<select id="admin_access_capability" name="admin_access_capability">
												<option value="manage_options" <?php selected($omnify_settings['admin_access_capability'] ?? 'manage_options', 'manage_options'); ?>><?php esc_html_e('Only Administrators (manage_options)', 'omnifywp-ecommerce'); ?></option>
												<option value="manage_omnify" <?php selected($omnify_settings['admin_access_capability'] ?? 'manage_options', 'manage_omnify'); ?>><?php esc_html_e('Administrators, Shop Managers & Clerks (manage_omnify)', 'omnifywp-ecommerce'); ?></option>
											</select>
											<span class="help-text"><?php esc_html_e('Choose who can view and edit settings, products, orders, and customer records.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-capability-info-note">
											<div class="omnify-capability-info-note__icon">
												<span class="dashicons dashicons-info"></span>
											</div>
											<div class="omnify-capability-info-note__text">
												<strong><?php esc_html_e('Security Notice', 'omnifywp-ecommerce'); ?>:</strong>
												<?php esc_html_e('Setting to "manage_omnify" permits non-administrators with the Shop Manager or Shop Clerk role to log in and manage the store according to the matrix below.', 'omnifywp-ecommerce'); ?>
											</div>
										</div>
									</div>
								</div>

								<!-- Feature Permissions Matrix -->
								<div class="omnify-settings-section-block">
									<?php omnify_render_section_divider(__('Feature Permissions Matrix', 'omnifywp-ecommerce'), __('Grant or revoke access to specific store features for each staff role.', 'omnifywp-ecommerce'), 'dashicons-lock'); ?>

									<div class="omnify-table-wrapper omnify-perm-matrix-wrapper">
										<table class="omnify-table omnify-perm-matrix-table">
											<thead>
												<tr>
													<th class="omnify-matrix-th-feature"><?php esc_html_e('Shop Feature / Module', 'omnifywp-ecommerce'); ?></th>
													<th class="omnify-matrix-th-role">
														<div class="omnify-role-header-chip omnify-role-header-chip--admin">
															<span class="dashicons dashicons-shield"></span>
															<span><?php esc_html_e('Administrator', 'omnifywp-ecommerce'); ?></span>
														</div>
													</th>
													<th class="omnify-matrix-th-role">
														<div class="omnify-role-header-chip omnify-role-header-chip--manager">
															<span class="dashicons dashicons-businessman"></span>
															<span><?php esc_html_e('Shop Manager', 'omnifywp-ecommerce'); ?></span>
														</div>
													</th>
													<th class="omnify-matrix-th-role">
														<div class="omnify-role-header-chip omnify-role-header-chip--clerk">
															<span class="dashicons dashicons-id"></span>
															<span><?php esc_html_e('Shop Clerk', 'omnifywp-ecommerce'); ?></span>
														</div>
													</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$omnify_features_map = [
													'dashboard'       => ['label' => __('Dashboard & KPIs', 'omnifywp-ecommerce'), 'desc' => __('Real-time revenue metrics, order velocity, and alerts', 'omnifywp-ecommerce'), 'icon' => 'dashicons-dashboard'],
													'products'        => ['label' => __('Products & Inventory', 'omnifywp-ecommerce'), 'desc' => __('Catalog items, stock inventory, categories, and attributes', 'omnifywp-ecommerce'), 'icon' => 'dashicons-products'],
													'orders'          => ['label' => __('Orders & Fulfillment', 'omnifywp-ecommerce'), 'desc' => __('Customer orders, shipments, refunds, and packing slips', 'omnifywp-ecommerce'), 'icon' => 'dashicons-cart'],
													'customers'       => ['label' => __('Customer Directory', 'omnifywp-ecommerce'), 'desc' => __('Customer accounts, purchase histories, and contact info', 'omnifywp-ecommerce'), 'icon' => 'dashicons-groups'],
													'coupons'         => ['label' => __('Coupons & Discounts', 'omnifywp-ecommerce'), 'desc' => __('Promotional coupon codes, usage limits, and discounts', 'omnifywp-ecommerce'), 'icon' => 'dashicons-tag'],
													'abandoned_carts' => ['label' => __('Abandoned Carts', 'omnifywp-ecommerce'), 'desc' => __('Recoverable checkout sessions and abandonment tracking', 'omnifywp-ecommerce'), 'icon' => 'dashicons-clock'],
													'reviews'         => ['label' => __('Reviews & Ratings', 'omnifywp-ecommerce'), 'desc' => __('Customer feedback moderation and public display', 'omnifywp-ecommerce'), 'icon' => 'dashicons-star-filled'],
													'analytics'       => ['label' => __('Reports & Analytics', 'omnifywp-ecommerce'), 'desc' => __('Sales performance, revenue trends, and conversion charts', 'omnifywp-ecommerce'), 'icon' => 'dashicons-chart-bar'],
													'settings'        => ['label' => __('Store Settings', 'omnifywp-ecommerce'), 'desc' => __('Global shop configurations, payment gateways, and taxes', 'omnifywp-ecommerce'), 'icon' => 'dashicons-admin-generic'],
													'tools'           => ['label' => __('Tools, API & Activity', 'omnifywp-ecommerce'), 'desc' => __('System diagnostics, webhooks, audit trails, and cache', 'omnifywp-ecommerce'), 'icon' => 'dashicons-admin-tools'],
												];

												$omnify_perms = $omnify_settings['role_permissions'] ?? [];
												foreach ($omnify_features_map as $omnify_f_key => $omnify_f_data) :
													$omnify_current_allowed = (array) ($omnify_perms[$omnify_f_key] ?? ['administrator']);
												?>
													<tr class="omnify-matrix-row">
														<td>
															<div class="omnify-perm-feature-row">
																<span class="omnify-perm-feature-icon"><span class="dashicons <?php echo esc_attr($omnify_f_data['icon']); ?>"></span></span>
																<div class="omnify-perm-feature-text">
																	<span class="omnify-perm-feature-title"><?php echo esc_html($omnify_f_data['label']); ?></span>
																	<span class="omnify-perm-feature-desc"><?php echo esc_html($omnify_f_data['desc']); ?></span>
																</div>
															</div>
														</td>
														<!-- Administrator Column -->
														<td class="omnify-matrix-cell-center">
															<span class="omnify-badge omnify-badge--success" title="<?php esc_attr_e('Administrators always possess full permissions', 'omnifywp-ecommerce'); ?>">
																<span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
																<?php esc_html_e('Full Access', 'omnifywp-ecommerce'); ?>
															</span>
															<input type="hidden" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="administrator" />
														</td>
														<!-- Shop Manager Column -->
														<td class="omnify-matrix-cell-center">
															<label class="omnify-matrix-checkbox-wrap">
																<input type="checkbox" class="omnify-matrix-checkbox" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="shop_manager" <?php checked(in_array('shop_manager', $omnify_current_allowed, true)); ?> />
															</label>
														</td>
														<!-- Shop Clerk Column -->
														<td class="omnify-matrix-cell-center">
															<label class="omnify-matrix-checkbox-wrap">
																<input type="checkbox" class="omnify-matrix-checkbox" name="role_permissions[<?php echo esc_attr($omnify_f_key); ?>][]" value="shop_clerk" <?php checked(in_array('shop_clerk', $omnify_current_allowed, true)); ?> />
															</label>
														</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<?php omnify_render_form_footer(); ?>
					</form>

					<!-- Shop User Management info and Directory details render below roles settings panel -->
					<?php if ($omnify_active_section === 'roles') : ?>
						<div class="omnify-modern-card-body" style="border-top: 1px solid #edf2f6; margin-top: 20px;">
							<div class="omnify-user-mgmt-header" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
								<div>
									<?php omnify_render_section_title(__('Shop Staff & User Directory', 'omnifywp-ecommerce'), __('Manage store staff, administrators, managers, and clerks.', 'omnifywp-ecommerce')); ?>
								</div>
								<a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="omnify-button omnify-button--primary">
									<span class="dashicons dashicons-plus-alt"></span>
									<?php esc_html_e('Add New Staff User', 'omnifywp-ecommerce'); ?>
								</a>
							</div>
							
							<div class="omnify-table-wrapper" style="margin-top: 16px;">
								<table class="omnify-table">
									<thead>
										<tr>
											<th><?php esc_html_e('Staff Member', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Assigned Role', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 130px; text-align: right;"><?php esc_html_e('Action', 'omnifywp-ecommerce'); ?></th>
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
												<td colspan="4" style="text-align: center; color: #94a3b8; padding: 32px 16px;">
													<?php esc_html_e('No shop users found.', 'omnifywp-ecommerce'); ?>
												</td>
											</tr>
										<?php else :
											foreach ($omnify_users as $omnify_usr) :
												$omnify_roles = (array) $omnify_usr->roles;
												$omnify_display_role = '';
												$omnify_badge_class = 'omnify-badge--info';
												if (in_array('administrator', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Administrator', 'omnifywp-ecommerce');
													$omnify_badge_class  = 'omnify-badge--purple';
												} elseif (in_array('shop_manager', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Shop Manager', 'omnifywp-ecommerce');
													$omnify_badge_class  = 'omnify-badge--success';
												} elseif (in_array('shop_clerk', $omnify_roles, true)) {
													$omnify_display_role = esc_html__('Shop Clerk', 'omnifywp-ecommerce');
													$omnify_badge_class  = 'omnify-badge--warning';
												} else {
													$omnify_display_role = esc_html(implode(', ', $omnify_roles));
												}
												$omnify_initial = mb_strtoupper(mb_substr($omnify_usr->display_name ?: $omnify_usr->user_login, 0, 1));
												?>
												<tr>
													<td>
														<div class="omnify-user-cell">
															<span class="omnify-user-avatar omnify-user-avatar--<?php echo in_array('administrator', $omnify_roles, true) ? 'admin' : (in_array('shop_manager', $omnify_roles, true) ? 'manager' : 'clerk'); ?>"><?php echo esc_html($omnify_initial); ?></span>
															<div>
																<strong style="color: #0f172a; display: block; font-size: 13.5px;"><?php echo esc_html($omnify_usr->display_name ?: $omnify_usr->user_login); ?></strong>
																<span style="font-size: 12px; color: #64748b;">@<?php echo esc_html($omnify_usr->user_login); ?></span>
															</div>
														</div>
													</td>
													<td>
														<span style="color: #475569; font-size: 13px; font-weight: 500;"><?php echo esc_html($omnify_usr->user_email); ?></span>
													</td>
													<td>
														<span class="omnify-badge <?php echo esc_attr($omnify_badge_class); ?>">
															<?php echo esc_html($omnify_display_role); ?>
														</span>
													</td>
													<td style="text-align: right;">
														<?php if (! in_array('administrator', $omnify_roles, true)) : ?>
															<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_remove_shop_user&user_id=' . $omnify_usr->ID), 'omnify_remove_shop_user_' . $omnify_usr->ID)); ?>" class="omnify-button omnify-button--danger omnify-button--sm" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to remove access for this user?', 'omnifywp-ecommerce')); ?>')">
																<span class="dashicons dashicons-trash"></span>
																<?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?>
															</a>
														<?php else: ?>
															<span class="omnify-badge omnify-badge--draft" title="<?php esc_attr_e('Default WordPress Administrator account cannot be removed from here', 'omnifywp-ecommerce'); ?>">
																<span class="dashicons dashicons-lock" style="font-size: 12px; width: 12px; height: 12px; line-height: 1;"></span>
																<?php esc_html_e('Protected', 'omnifywp-ecommerce'); ?>
															</span>
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

					<div class="omnify-modern-card-body" style="padding: 0; background: transparent; border: none; box-shadow: none;">
						<?php include __DIR__ . '/partials/payment-settings-directory.php'; ?>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif ($omnify_active_section === 'tax') : ?>
				<!-- ══ TAX RULES ═══════════════════════════════════════ -->
				<?php
				$omnify_tax_rules = is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [];
				?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-tax-rules-form">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="tax" />
					<input type="hidden" name="tax_rules_submitted" value="1" />

					<div class="omnify-modern-card-body">
						<!-- Section 1: Tax Calculation & Store Defaults -->
						<div class="omnify-settings-section-block" style="margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #e2e8f0;">
							<?php omnify_render_section_divider(__('Tax Calculation & Store Defaults', 'omnifywp-ecommerce'), __('Configure baseline store tax rate, price display modes, delivery taxation, and rounding precision.', 'omnifywp-ecommerce'), 'dashicons-calculator'); ?>

							<div class="omnify-modern-form-row omnify-tax-defaults-inputs-grid">
								<div class="omnify-modern-form-group">
									<label for="omnify_tax_rate"><?php esc_html_e('Default Store Tax Rate (%)', 'omnifywp-ecommerce'); ?></label>
									<div class="omnify-input-wrapper omnify-input-with-suffix">
										<input type="number" step="0.0001" min="0" max="100" id="omnify_tax_rate" name="tax_rate" value="<?php echo esc_attr((string) ($omnify_settings['tax_rate'] ?? 0)); ?>" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important; width: 100%;" />
										<span class="omnify-input-suffix">%</span>
									</div>
									<span class="help-text"><?php esc_html_e('Fallback tax rate applied when no destination-specific regional tax rule matches.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-modern-form-group">
									<label for="omnify_tax_label"><?php esc_html_e('Default Tax Label', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="omnify_tax_label" name="tax_label" value="<?php echo esc_attr((string) ($omnify_settings['tax_label'] ?? 'Tax')); ?>" placeholder="<?php esc_attr_e('e.g. Sales Tax, VAT, GST', 'omnifywp-ecommerce'); ?>" style="width: 100%;" />
									<span class="help-text"><?php esc_html_e('The display name shown on cart, checkout, receipts, and invoices.', 'omnifywp-ecommerce'); ?></span>
								</div>
								<div class="omnify-modern-form-group">
									<label for="omnify_tax_rounding"><?php esc_html_e('Tax Rounding Precision', 'omnifywp-ecommerce'); ?> <span class="dashicons dashicons-info" title="<?php esc_attr_e('Choose how rounding is calculated.', 'omnifywp-ecommerce'); ?>"></span></label>
									<select id="omnify_tax_rounding" name="tax_rounding" style="width: 100%;">
										<option value="line" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'line'); ?>><?php esc_html_e('Round tax at line item level', 'omnifywp-ecommerce'); ?></option>
										<option value="subtotal" <?php selected($omnify_settings['tax_rounding'] ?? 'line', 'subtotal'); ?>><?php esc_html_e('Round tax at cart subtotal level', 'omnifywp-ecommerce'); ?></option>
									</select>
									<span class="help-text"><?php esc_html_e('Subtotal level rounding prevents 1-cent discrepancies when items have fractional tax rates.', 'omnifywp-ecommerce'); ?></span>
								</div>
							</div>

							<div class="omnify-toggle-cards-grid omnify-tax-defaults-toggles-grid">
								<?php omnify_render_toggle_card(
									'prices_include_tax',
									$omnify_settings['prices_include_tax'] ?? 0,
									__('Prices Include Tax', 'omnifywp-ecommerce'),
									__('Catalog prices are treated as tax-inclusive across product displays and cart totals.', 'omnifywp-ecommerce')
								); ?>

								<?php omnify_render_toggle_card(
									'tax_shipping',
									$omnify_settings['tax_shipping'] ?? 0,
									__('Charge Tax on Delivery', 'omnifywp-ecommerce'),
									__('When enabled, the matched delivery zone tax rate also applies to delivery shipping costs.', 'omnifywp-ecommerce')
								); ?>

								<?php omnify_render_toggle_card(
									'tax_reporting_enabled',
									$omnify_settings['tax_reporting_enabled'] ?? 0,
									__('Tax Reporting Codes', 'omnifywp-ecommerce'),
									__('Stores reporting codes on tax rules for export and accounting workflows.', 'omnifywp-ecommerce')
								); ?>
							</div>
						</div>

						<!-- Section Header with Primary Add Button -->
						<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
							<div>
								<h2 style="font-size: 19px; font-weight: 700; color: #0f172a; margin: 0 0 5px; letter-spacing: -0.01em;"><?php esc_html_e('Regional Tax Rules & Rates', 'omnifywp-ecommerce'); ?></h2>
								<p style="font-size: 13px; color: #64748b; margin: 0; line-height: 1.4;"><?php esc_html_e('Define country and state tax rates applied to customer orders at checkout. Use * as a wildcard for all states or countries.', 'omnifywp-ecommerce'); ?></p>
							</div>
							<div style="display: flex; align-items: center; gap: 10px;">
								<button type="button" class="omnify-modern-btn omnify-modern-btn-primary omnify-trigger-add-tax-rule" style="background: #10b981; color: #fff; border-color: #10b981; display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; box-shadow: 0 1px 3px rgba(16, 185, 129, 0.25);">
									<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span>
									<span><?php esc_html_e('Add Tax Rule', 'omnifywp-ecommerce'); ?></span>
								</button>
							</div>
						</div>

						<!-- Regional Tax Guidance Banner -->
						<div class="omnify-zone-info-banner">
							<div class="omnify-zone-info-banner-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="2" y="4" width="20" height="16" rx="2"></rect>
									<line x1="2" y1="10" x2="22" y2="10"></line>
									<line x1="7" y1="15" x2="7.01" y2="15"></line>
									<line x1="12" y1="15" x2="12.01" y2="15"></line>
								</svg>
							</div>
							<div class="omnify-zone-info-banner-content">
								<h4><?php esc_html_e('Tax Evaluation Architecture', 'omnifywp-ecommerce'); ?></h4>
								<p><?php esc_html_e('Tax rates are applied based on customer destination address. Specific state rules match before national wildcards (*). When multiple rules match, priority determines compounding or evaluation order.', 'omnifywp-ecommerce'); ?></p>
							</div>
						</div>

						<!-- Quick Tax Presets Bar -->
						<div class="omnify-tax-presets-bar">
							<span class="omnify-tax-presets-label"><?php esc_html_e('Quick Presets:', 'omnifywp-ecommerce'); ?></span>
							<button type="button" class="omnify-tax-preset-btn" data-preset-label="State Sales Tax" data-preset-country="US" data-preset-state="*" data-preset-rate="0.00" data-preset-code="US-SALESTAX" data-preset-priority="10">
								<span>+</span> <?php esc_html_e('US State Sales Tax', 'omnifywp-ecommerce'); ?>
							</button>
							<button type="button" class="omnify-tax-preset-btn" data-preset-label="EU Standard VAT" data-preset-country="*" data-preset-state="*" data-preset-rate="20.00" data-preset-code="EU-VAT-20" data-preset-priority="10">
								<span>+</span> <?php esc_html_e('EU Standard VAT (20%)', 'omnifywp-ecommerce'); ?>
							</button>
							<button type="button" class="omnify-tax-preset-btn" data-preset-label="UK Standard VAT" data-preset-country="GB" data-preset-state="*" data-preset-rate="20.00" data-preset-code="UK-VAT-20" data-preset-priority="10">
								<span>+</span> <?php esc_html_e('UK VAT (20%)', 'omnifywp-ecommerce'); ?>
							</button>
							<button type="button" class="omnify-tax-preset-btn" data-preset-label="Canada Federal GST" data-preset-country="CA" data-preset-state="*" data-preset-rate="5.00" data-preset-code="CA-GST-5" data-preset-priority="10">
								<span>+</span> <?php esc_html_e('Canada GST (5%)', 'omnifywp-ecommerce'); ?>
							</button>
							<button type="button" class="omnify-tax-preset-btn" data-preset-label="Zero-Rated Export" data-preset-country="*" data-preset-state="*" data-preset-rate="0.00" data-preset-code="ZERO-RATE" data-preset-priority="10">
								<span>+</span> <?php esc_html_e('Zero-Rated (0%)', 'omnifywp-ecommerce'); ?>
							</button>
						</div>

						<!-- Modern Tax Table Wrapper -->
						<div id="omnify-tax-table-wrapper" class="omnify-tax-table-wrapper" style="<?php echo empty($omnify_tax_rules) ? 'display: none;' : ''; ?>">
							<table class="omnify-tax-table">
								<thead>
									<tr>
										<th style="width: 50px; text-align: center;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
										<th style="min-width: 140px;"><?php esc_html_e('Tax Label', 'omnifywp-ecommerce'); ?></th>
										<th style="min-width: 160px;"><?php esc_html_e('Country Scope', 'omnifywp-ecommerce'); ?></th>
										<th style="min-width: 150px;"><?php esc_html_e('State / Province', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 140px; min-width: 130px;"><?php esc_html_e('Rate %', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 130px;"><?php esc_html_e('Reporting Code', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 75px;"><?php esc_html_e('Priority', 'omnifywp-ecommerce'); ?></th>
										<th style="width: 50px; text-align: center;"></th>
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
											<td style="text-align: center; vertical-align: middle;">
												<label class="omnify-switch">
													<input type="checkbox" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][enabled]" value="1" <?php checked(! empty($omnify_rule['enabled'])); ?> />
													<span class="omnify-slider"></span>
												</label>
											</td>
											<td><input type="text" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][label]" value="<?php echo esc_attr((string) ($omnify_rule['label'] ?? '')); ?>" placeholder="<?php esc_attr_e('e.g. Sales Tax, VAT', 'omnifywp-ecommerce'); ?>" style="width: 100%;" required /></td>
											<td>
												<select name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][country]" class="omnify-country-select omnify-searchable-select" style="width: 100%;">
													<option value="*" <?php selected(empty($omnify_selected_country) || '*' === $omnify_selected_country); ?>><?php esc_html_e('All countries (*)', 'omnifywp-ecommerce'); ?></option>
													<?php foreach ($omnify_countries as $code => $name) : ?>
														<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_country, $code); ?>><?php echo esc_html($name); ?> (<?php echo esc_html($code); ?>)</option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<select name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][state]" class="omnify-state-select omnify-searchable-select" data-selected="<?php echo esc_attr($omnify_selected_state); ?>" style="width: 100%;">
													<option value="*" <?php selected(empty($omnify_selected_state) || '*' === $omnify_selected_state); ?>><?php esc_html_e('All states (*)', 'omnifywp-ecommerce'); ?></option>
													<?php foreach ($omnify_state_options as $code => $name) : ?>
														<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_state, $code); ?>><?php echo esc_html($name); ?></option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<div class="omnify-input-wrapper omnify-input-with-suffix">
													<input type="number" step="0.0001" min="0" max="100" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][rate]" value="<?php echo esc_attr((string) ($omnify_rule['rate'] ?? '')); ?>" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important; width: 100%;" required />
													<span class="omnify-input-suffix">%</span>
												</div>
											</td>
											<td><input type="text" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][reporting_code]" value="<?php echo esc_attr((string) ($omnify_rule['reporting_code'] ?? '')); ?>" placeholder="<?php esc_attr_e('e.g. TAX-STD', 'omnifywp-ecommerce'); ?>" style="width: 100%;" /></td>
											<td><input type="number" min="1" name="tax_rules[<?php echo esc_attr((string) $omnify_index); ?>][priority]" value="<?php echo esc_attr((string) ($omnify_rule['priority'] ?? 10)); ?>" style="width: 100%;" required /></td>
											<td style="text-align: center;">
												<button type="button" class="omnify-tax-remove-btn omnify-remove-row" title="<?php esc_attr_e('Delete Rule', 'omnifywp-ecommerce'); ?>" aria-label="<?php esc_attr_e('Delete Rule', 'omnifywp-ecommerce'); ?>">
													<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
														<polyline points="3 6 5 6 21 6"></polyline>
														<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
													</svg>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<!-- Empty State Container -->
						<div id="omnify-tax-rules-empty" class="omnify-zone-empty-state" style="<?php echo empty($omnify_tax_rules) ? '' : 'display: none;'; ?>">
							<div class="omnify-zone-empty-state-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="2" y="4" width="20" height="16" rx="2"></rect>
									<line x1="2" y1="10" x2="22" y2="10"></line>
								</svg>
							</div>
							<h3><?php esc_html_e('No Regional Tax Rules Configured', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Configure regional sales tax rates, VAT, or GST. If empty, the store fallback tax rate from General Settings will apply.', 'omnifywp-ecommerce'); ?></p>
							<button type="button" class="omnify-modern-btn omnify-modern-btn-primary omnify-trigger-add-tax-rule" style="background: #10b981; color: #fff; border-color: #10b981; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
								<span class="dashicons dashicons-plus-alt2"></span>
								<span><?php esc_html_e('Create Tax Rule', 'omnifywp-ecommerce'); ?></span>
							</button>
						</div>

						<!-- Add Rule Button at Bottom -->
						<button type="button" id="omnify-add-tax-rule" class="omnify-modern-btn omnify-modern-btn-secondary" style="margin-top: 14px;">
							<span class="dashicons dashicons-plus-alt2"></span>
							<span><?php esc_html_e('Add Another Tax Rule', 'omnifywp-ecommerce'); ?></span>
						</button>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif ($omnify_active_section === 'delivery') : ?>
				<!-- ══ DELIVERY ZONES ══════════════════════════════════ -->
				<?php
				$omnify_zones = is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [];
				$omnify_currency = $omnify_settings['default_currency'] ?? 'USD';
				$omnify_currency_symbols = [
					'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'CAD' => 'C$', 'AUD' => 'A$', 'CHF' => 'Fr',
					'CNY' => '¥', 'INR' => '₹', 'BDT' => '৳', 'BRL' => 'R$', 'MXN' => '$', 'NGN' => '₦', 'NZD' => 'NZ$',
					'PKR' => '₨', 'SAR' => 'ر.س', 'AED' => 'د.إ', 'SGD' => 'S$', 'MYR' => 'RM', 'ZAR' => 'R', 'NPR' => 'रू',
				];
				$omnify_symbol = $omnify_currency_symbols[$omnify_currency] ?? '$';
				?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-delivery-zones-form">
					<?php wp_nonce_field('omnify_save_settings', 'omnify_settings_nonce'); ?>
					<input type="hidden" name="action" value="omnify_save_settings" />
					<input type="hidden" name="section" value="delivery" />
					<input type="hidden" name="delivery_zones_submitted" value="1" />

					<div class="omnify-modern-card-body">
						<!-- Section Header with Primary Add Button -->
						<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
							<div>
								<h2 style="font-size: 19px; font-weight: 700; color: #0f172a; margin: 0 0 5px; letter-spacing: -0.01em;"><?php esc_html_e('Delivery & Shipping Zones', 'omnifywp-ecommerce'); ?></h2>
								<p style="font-size: 13px; color: #64748b; margin: 0; line-height: 1.4;"><?php esc_html_e('Manage regional delivery zones, shipping rates, and free delivery thresholds for physical orders.', 'omnifywp-ecommerce'); ?></p>
							</div>
							<div style="display: flex; align-items: center; gap: 10px;">
								<button type="button" class="omnify-modern-btn omnify-modern-btn-primary omnify-trigger-add-zone" style="background: #10b981; color: #fff; border-color: #10b981; display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; box-shadow: 0 1px 3px rgba(16, 185, 129, 0.25);">
									<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span>
									<span><?php esc_html_e('Add Shipping Zone', 'omnifywp-ecommerce'); ?></span>
								</button>
							</div>
						</div>

						<!-- Regional Matching Guidance Banner -->
						<div class="omnify-zone-info-banner">
							<div class="omnify-zone-info-banner-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10"></circle>
									<line x1="2" y1="12" x2="22" y2="12"></line>
									<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
								</svg>
							</div>
							<div class="omnify-zone-info-banner-content">
								<h4><?php esc_html_e('Regional Zone Matching Architecture', 'omnifywp-ecommerce'); ?></h4>
								<p><?php esc_html_e('Customer shipping addresses are evaluated from top to bottom. Specific country and state rules take precedence before falling back to global wildcards (*). If an order meets a zone\'s free shipping threshold, the shipping fee is automatically waived.', 'omnifywp-ecommerce'); ?></p>
							</div>
						</div>

						<!-- Delivery Zones List -->
						<div id="omnify-delivery-zones-container" style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
							<?php foreach ($omnify_zones as $omnify_index => $omnify_zone) : ?>
								<?php
								$omnify_selected_country = strtoupper((string) ($omnify_zone['countries'] ?? ''));
								$omnify_selected_state = strtoupper((string) ($omnify_zone['states'] ?? ''));
								$omnify_zone_name = (string) ($omnify_zone['name'] ?? '');
								$omnify_method_name = (string) ($omnify_zone['method_name'] ?? 'Standard Delivery');
								$omnify_method_type = (string) ($omnify_zone['method_type'] ?? 'flat_rate');
								$omnify_rate = (float) ($omnify_zone['cost'] ?? $omnify_zone['rate'] ?? 0);
								$omnify_free_min = (float) ($omnify_zone['free_min'] ?? 0);
								$omnify_priority = (int) ($omnify_zone['priority'] ?? (($omnify_index + 1) * 10));
								$omnify_is_enabled = ! empty($omnify_zone['enabled']);

								// Compute geographic preview label
								if (empty($omnify_selected_country) || '*' === $omnify_selected_country) {
									$omnify_geo_preview = __('Worldwide (*)', 'omnifywp-ecommerce');
								} else {
									$omnify_country_label = $omnify_countries[$omnify_selected_country] ?? $omnify_selected_country;
									$omnify_geo_preview = $omnify_country_label . (empty($omnify_selected_state) || '*' === $omnify_selected_state ? ' · ' . __('All states', 'omnifywp-ecommerce') : ' (' . $omnify_selected_state . ')');
								}
								?>
								<div class="omnify-delivery-zone-card <?php echo $omnify_is_enabled ? '' : 'is-zone-disabled'; ?>" data-zone-index="<?php echo esc_attr((string) $omnify_index); ?>">
									<!-- Zone Card Header -->
									<div class="omnify-zone-card-header">
										<div class="omnify-zone-card-header-left">
											<span class="omnify-zone-grip-icon" title="<?php esc_attr_e('Drag to reorder match priority', 'omnifywp-ecommerce'); ?>">
												<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
													<circle cx="4" cy="3" r="1.5"/><circle cx="12" cy="3" r="1.5"/>
													<circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
													<circle cx="4" cy="13" r="1.5"/><circle cx="12" cy="13" r="1.5"/>
												</svg>
											</span>
											<span class="omnify-zone-index-badge">Zone #<?php echo (int) ($omnify_index + 1); ?></span>
											<h4 class="omnify-zone-title-display"><?php echo esc_html('' !== $omnify_zone_name ? $omnify_zone_name : __('Untitled Zone', 'omnifywp-ecommerce')); ?></h4>
											<span class="omnify-zone-geo-pill" data-role="geo-preview"><?php echo esc_html($omnify_geo_preview); ?></span>
										</div>
										<div class="omnify-zone-card-header-right">
											<div class="omnify-zone-status-toggle">
												<span class="omnify-zone-status-label"><?php echo $omnify_is_enabled ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Disabled', 'omnifywp-ecommerce'); ?></span>
												<label class="omnify-switch">
													<input type="checkbox" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][enabled]" value="1" <?php checked($omnify_is_enabled); ?> class="omnify-zone-enable-toggle" />
													<span class="omnify-slider"></span>
												</label>
											</div>
											<button type="button" class="omnify-zone-delete-btn omnify-remove-zone" title="<?php esc_attr_e('Delete Zone', 'omnifywp-ecommerce'); ?>" aria-label="<?php esc_attr_e('Delete Zone', 'omnifywp-ecommerce'); ?>">
												<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
													<polyline points="3 6 5 6 21 6"></polyline>
													<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
													<line x1="10" y1="11" x2="10" y2="17"></line>
													<line x1="14" y1="11" x2="14" y2="17"></line>
												</svg>
											</button>
										</div>
									</div>

									<!-- Zone Card Body -->
									<div class="omnify-zone-card-body">
										<!-- Section 1: Region & Priority -->
										<div class="omnify-zone-field-grid omnify-grid-2">
											<div class="omnify-modern-form-group">
												<label class="omnify-field-label">
													<span><?php esc_html_e('Zone Name', 'omnifywp-ecommerce'); ?></span>
													<span class="omnify-required-asterisk">*</span>
												</label>
												<input type="text" class="omnify-zone-name-input" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][name]" value="<?php echo esc_attr($omnify_zone_name); ?>" placeholder="<?php esc_attr_e('e.g. Domestic (United States) or Europe Express', 'omnifywp-ecommerce'); ?>" required />
											</div>

											<div class="omnify-modern-form-group">
												<label class="omnify-field-label">
													<span><?php esc_html_e('Match Priority', 'omnifywp-ecommerce'); ?></span>
													<span class="omnify-field-hint"><?php esc_html_e('(Lower number = evaluated first)', 'omnifywp-ecommerce'); ?></span>
												</label>
												<input type="number" min="1" max="999" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][priority]" value="<?php echo esc_attr((string) $omnify_priority); ?>" />
											</div>
										</div>

										<div class="omnify-zone-field-grid omnify-grid-2" style="margin-top: 14px;">
											<div class="omnify-modern-form-group">
												<label class="omnify-field-label"><?php esc_html_e('Country / Region Scope', 'omnifywp-ecommerce'); ?></label>
												<select name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][countries]" class="omnify-country-select omnify-searchable-select" style="width: 100%;">
													<option value="*" <?php selected(empty($omnify_selected_country) || '*' === $omnify_selected_country); ?>><?php esc_html_e('All Countries & Regions (*)', 'omnifywp-ecommerce'); ?></option>
													<?php foreach ($omnify_countries as $code => $name) : ?>
														<option value="<?php echo esc_attr($code); ?>" <?php selected($omnify_selected_country, $code); ?>><?php echo esc_html($name); ?> (<?php echo esc_html($code); ?>)</option>
													<?php endforeach; ?>
												</select>
											</div>

											<div class="omnify-modern-form-group">
												<label class="omnify-field-label"><?php esc_html_e('State / Province Scope', 'omnifywp-ecommerce'); ?></label>
												<select name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][states]" class="omnify-state-select omnify-searchable-select" data-selected="<?php echo esc_attr($omnify_selected_state); ?>" style="width: 100%;">
													<option value="*" <?php selected(empty($omnify_selected_state) || '*' === $omnify_selected_state); ?>><?php esc_html_e('All States / Provinces (*)', 'omnifywp-ecommerce'); ?></option>
												</select>
											</div>
										</div>

										<!-- Section Divider -->
										<div class="omnify-zone-section-divider">
											<span><?php esc_html_e('Shipping Rates & Methods', 'omnifywp-ecommerce'); ?></span>
										</div>

										<!-- Section 2: Method, Rate & Threshold -->
										<div class="omnify-zone-field-grid omnify-grid-2">
											<div class="omnify-modern-form-group">
												<label class="omnify-field-label"><?php esc_html_e('Method Title', 'omnifywp-ecommerce'); ?></label>
												<input type="text" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][method_name]" value="<?php echo esc_attr($omnify_method_name); ?>" placeholder="<?php esc_attr_e('e.g. Standard Shipping', 'omnifywp-ecommerce'); ?>" />
												<span class="help-text"><?php esc_html_e('The shipping method title displayed to shoppers at checkout.', 'omnifywp-ecommerce'); ?></span>
											</div>

											<div class="omnify-modern-form-group">
												<label class="omnify-field-label"><?php esc_html_e('Method Type', 'omnifywp-ecommerce'); ?></label>
												<select name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][method_type]" class="omnify-method-type-select">
													<option value="flat_rate" <?php selected($omnify_method_type, 'flat_rate'); ?>><?php esc_html_e('Flat Rate Delivery', 'omnifywp-ecommerce'); ?></option>
													<option value="free_shipping" <?php selected($omnify_method_type, 'free_shipping'); ?>><?php esc_html_e('Free Shipping (Always $0)', 'omnifywp-ecommerce'); ?></option>
													<option value="local_pickup" <?php selected($omnify_method_type, 'local_pickup'); ?>><?php esc_html_e('Local Pickup / Store Collection', 'omnifywp-ecommerce'); ?></option>
												</select>
												<span class="help-text"><?php esc_html_e('Select charging model or collection arrangement for this rate.', 'omnifywp-ecommerce'); ?></span>
											</div>
										</div>

										<div class="omnify-zone-field-grid omnify-grid-2" style="margin-top: 14px;">
											<div class="omnify-modern-form-group omnify-zone-rate-group <?php echo 'free_shipping' === $omnify_method_type ? 'is-disabled-rate' : ''; ?>">
												<label class="omnify-field-label"><?php esc_html_e('Shipping Cost', 'omnifywp-ecommerce'); ?></label>
												<div class="omnify-input-wrapper omnify-input-with-prefix">
													<span class="omnify-input-prefix"><?php echo esc_html($omnify_symbol); ?></span>
													<input type="number" step="0.01" min="0" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][cost]" value="<?php echo esc_attr(number_format($omnify_rate, 2, '.', '')); ?>" placeholder="0.00" required style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												</div>
												<span class="help-text"><?php esc_html_e('Base delivery fee charged on orders in this zone.', 'omnifywp-ecommerce'); ?></span>
											</div>

											<div class="omnify-modern-form-group">
												<label class="omnify-field-label"><?php esc_html_e('Free Shipping Order Minimum', 'omnifywp-ecommerce'); ?></label>
												<div class="omnify-input-wrapper omnify-input-with-prefix">
													<span class="omnify-input-prefix"><?php echo esc_html($omnify_symbol); ?></span>
													<input type="number" step="0.01" min="0" name="delivery_zones[<?php echo esc_attr((string) $omnify_index); ?>][free_min]" value="<?php echo esc_attr(number_format($omnify_free_min, 2, '.', '')); ?>" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												</div>
												<span class="help-text"><?php esc_html_e('Orders at or above this amount unlock free shipping. Set to 0 to disable.', 'omnifywp-ecommerce'); ?></span>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Empty State Container (visible when 0 zones exist) -->
						<div id="omnify-delivery-zones-empty" class="omnify-zone-empty-state" style="<?php echo empty($omnify_zones) ? '' : 'display: none;'; ?>">
							<div class="omnify-zone-empty-state-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="1" y="3" width="15" height="13"></rect>
									<polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
									<circle cx="5.5" cy="18.5" r="2.5"></circle>
									<circle cx="18.5" cy="18.5" r="2.5"></circle>
								</svg>
							</div>
							<h3><?php esc_html_e('No Shipping Zones Configured', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Configure your first regional delivery zone to calculate shipping rates and unlock free delivery thresholds at checkout.', 'omnifywp-ecommerce'); ?></p>
							<button type="button" class="omnify-modern-btn omnify-modern-btn-primary omnify-trigger-add-zone" style="background: #10b981; color: #fff; border-color: #10b981; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
								<span class="dashicons dashicons-plus-alt2"></span>
								<span><?php esc_html_e('Create Shipping Zone', 'omnifywp-ecommerce'); ?></span>
							</button>
						</div>

						<!-- Add Zone Button at Bottom -->
						<button type="button" id="omnify-add-delivery-zone" class="omnify-modern-btn omnify-modern-btn-secondary" style="margin-top: 20px;">
							<span class="dashicons dashicons-plus-alt2"></span>
							<span><?php esc_html_e('Add Another Shipping Zone', 'omnifywp-ecommerce'); ?></span>
						</button>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif (in_array($omnify_active_section, ['checkout', 'fraud', 'marketing'], true)) : ?>
				<!-- CHECKOUT FLOW HUB -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('checkout')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'checkout' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-cart"></span>
							<?php esc_html_e('Checkout Flow', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('fraud')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'fraud' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-shield"></span>
							<?php esc_html_e('Fraud & Risk', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('marketing')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'marketing' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-chart-line"></span>
							<?php esc_html_e('Marketing & Tracking', 'omnifywp-ecommerce'); ?>
						</a>
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
							<?php
							$omnify_currency = $omnify_settings['default_currency'] ?? 'USD';
							$omnify_currency_symbols = [
								'USD' => '$',
								'EUR' => '€',
								'GBP' => '£',
								'CAD' => '$',
								'AUD' => '$',
								'JPY' => '¥',
							];
							$omnify_symbol = $omnify_currency_symbols[$omnify_currency] ?? '$';
							?>
							<?php omnify_render_section_title(__('Checkout Flow & Conversions', 'omnifywp-ecommerce'), __('Configure customer registration, order numbering, impulse bumps, and fulfillment options.', 'omnifywp-ecommerce')); ?>

							<!-- Checkout Guidance Banner -->
							<div class="omnify-zone-info-banner omnify-email-info-banner" style="grid-column: span 2; margin-bottom: 24px;">
								<div class="omnify-zone-info-banner-icon omnify-email-info-banner-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/><path d="m9 11 2 2 4-4"/></svg>
								</div>
								<div class="omnify-zone-info-banner-content omnify-email-info-banner-content">
									<h4><?php esc_html_e('Optimized Checkout Flow & Conversion Architecture', 'omnifywp-ecommerce'); ?></h4>
									<p><?php esc_html_e('Streamline the customer buying journey, boost average order value with one-click checkout bumps, automate abandoned cart recovery, and manage digital goods delivery.', 'omnifywp-ecommerce'); ?></p>
								</div>
							</div>

							<!-- 1. Customer Registration & Access Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--registration">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Customer Registration & Access', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Control guest purchases, account creation triggers, and automated customer login', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
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

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="account_creation_mode"><?php esc_html_e('Account Creation Mode', 'omnifywp-ecommerce'); ?></label>
											<select id="account_creation_mode" name="account_creation_mode">
												<option value="optional" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'optional'); ?>><?php esc_html_e('Optional checkbox during checkout', 'omnifywp-ecommerce'); ?></option>
												<option value="automatic" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'automatic'); ?>><?php esc_html_e('Automatically create account for buyers', 'omnifywp-ecommerce'); ?></option>
												<option value="required" <?php selected($omnify_settings['account_creation_mode'] ?? 'optional', 'required'); ?>><?php esc_html_e('Require account & password setup', 'omnifywp-ecommerce'); ?></option>
											</select>
											<span class="help-text"><?php esc_html_e('Automatic mode generates a WordPress account with a secure password after payment.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Sign In Customer After Creation', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('auto_login_created_accounts', $omnify_settings['auto_login_created_accounts'] ?? 1, __('Automatically log in buyers immediately after account creation.', 'omnifywp-ecommerce')); ?>
										</div>
									</div>
								</div>
							</div>

							<!-- 2. Order Processing & Numbering Format Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--behavior">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Order Processing & Sequential Numbering', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Configure default payment methods, inventory depletion, and branded order numbers', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Reduce Stock on Checkout', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('reduce_stock_on_checkout', $omnify_settings['reduce_stock_on_checkout'] ?? 1, __('Automatically deduct inventory counts when an order completes.', 'omnifywp-ecommerce')); ?>
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Require Phone Number', 'omnifywp-ecommerce'); ?></label>
											<?php omnify_render_toggle('checkout_require_phone', $omnify_settings['checkout_require_phone'] ?? 0, __('Make contact phone number a mandatory checkout field.', 'omnifywp-ecommerce')); ?>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="hold_stock_minutes"><?php esc_html_e('Hold Stock for Unpaid Orders (Minutes)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="hold_stock_minutes" name="hold_stock_minutes" min="0" value="<?php echo esc_attr($omnify_settings['hold_stock_minutes'] ?? 60); ?>" style="max-width: 140px;" />
											<span class="help-text"><?php esc_html_e('Automatically cancels pending/unpaid checkout orders after this many minutes and returns held stock back to inventory. Set to 0 to disable.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="default_payment_method"><?php esc_html_e('Default Payment Method', 'omnifywp-ecommerce'); ?></label>
											<select id="default_payment_method" name="default_payment_method">
												<option value="card" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'card'); ?>><?php esc_html_e('Credit / Debit Card (Stripe)', 'omnifywp-ecommerce'); ?></option>
												<option value="paypal" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'paypal'); ?>><?php esc_html_e('PayPal Express', 'omnifywp-ecommerce'); ?></option>
												<option value="bank_transfer" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'bank_transfer'); ?>><?php esc_html_e('Direct Bank Transfer (BACS)', 'omnifywp-ecommerce'); ?></option>
												<option value="cash_on_delivery" <?php selected($omnify_settings['default_payment_method'] ?? 'card', 'cash_on_delivery'); ?>><?php esc_html_e('Cash on Delivery (COD)', 'omnifywp-ecommerce'); ?></option>
											</select>
										</div>
										<div class="omnify-modern-form-group">
											<label for="low_stock_threshold"><?php esc_html_e('Low Stock Alert Threshold', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="<?php echo esc_attr($omnify_settings['low_stock_threshold'] ?? 5); ?>" style="max-width: 140px;" />
											<span class="help-text"><?php esc_html_e('Sends an admin email alert when inventory reaches this number.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<!-- Order Number Formatting Grid with Live Preview -->
									<div class="omnify-order-number-grid" style="margin-top: 5px;">
										<div class="omnify-modern-form-group">
											<label for="order_number_prefix"><?php esc_html_e('Prefix', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="order_number_prefix" name="order_number_prefix" value="<?php echo esc_attr($omnify_settings['order_number_prefix'] ?? 'OMN-'); ?>" placeholder="OMN-" />
										</div>
										<div class="omnify-modern-form-group">
											<label for="order_number_padding"><?php esc_html_e('Padding (Digits)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="order_number_padding" name="order_number_padding" min="1" max="10" value="<?php echo esc_attr($omnify_settings['order_number_padding'] ?? 4); ?>" />
										</div>
										<div class="omnify-modern-form-group">
											<label for="order_number_suffix"><?php esc_html_e('Suffix', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="order_number_suffix" name="order_number_suffix" value="<?php echo esc_attr($omnify_settings['order_number_suffix'] ?? ''); ?>" placeholder="-2026" />
										</div>
										<div class="omnify-order-number-sample-card">
											<span class="omnify-order-number-sample-label"><?php esc_html_e('Sample Order ID', 'omnifywp-ecommerce'); ?></span>
											<span class="omnify-order-number-sample-code" id="omnify-order-num-sample">
												<?php
												$omnify_pfx = $omnify_settings['order_number_prefix'] ?? 'OMN-';
												$omnify_pad = max(1, min(10, (int)($omnify_settings['order_number_padding'] ?? 4)));
												$omnify_sfx = $omnify_settings['order_number_suffix'] ?? '';
												echo esc_html($omnify_pfx . str_pad('42', $omnify_pad, '0', STR_PAD_LEFT) . $omnify_sfx);
												?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<!-- 3. Discounts & Checkout Impulse Bump Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--bump">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m20.59 13.41-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Discounts & Checkout Impulse Bump', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Enable coupon promo codes and high-converting impulse add-ons on the checkout page', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
									<div class="omnify-card-feature-strip">
										<div class="omnify-card-feature-strip-meta">
											<h5 class="omnify-card-feature-strip-title"><?php esc_html_e('Enable Coupons & Promo Codes', 'omnifywp-ecommerce'); ?></h5>
											<p class="omnify-card-feature-strip-desc"><?php esc_html_e('Allow buyers to redeem percentage, fixed, or free shipping discount codes.', 'omnifywp-ecommerce'); ?></p>
										</div>
										<?php omnify_render_toggle('enable_coupons', $omnify_settings['enable_coupons'] ?? 1); ?>
									</div>

									<div class="omnify-card-feature-strip" style="margin-top: 10px;">
										<div class="omnify-card-feature-strip-meta">
											<h5 class="omnify-card-feature-strip-title"><?php esc_html_e('Checkout Order Bump Offer', 'omnifywp-ecommerce'); ?></h5>
											<p class="omnify-card-feature-strip-desc"><?php esc_html_e('Display an eye-catching, 1-click impulse upsell directly on the checkout payment form.', 'omnifywp-ecommerce'); ?></p>
										</div>
										<?php omnify_render_toggle('order_bump_enabled', $omnify_settings['order_bump_enabled'] ?? 1); ?>
									</div>

									<div data-cond-field="order_bump_enabled" data-cond-val="1">
										<div class="omnify-modern-form-row" style="margin-top: 10px;">
											<div class="omnify-modern-form-group">
												<label for="order_bump_title"><?php esc_html_e('Order Bump Title', 'omnifywp-ecommerce'); ?></label>
												<input type="text" id="order_bump_title" name="order_bump_title" value="<?php echo esc_attr($omnify_settings['order_bump_title'] ?? 'VIP Priority Processing & Extended Warranty'); ?>" placeholder="VIP Priority Processing" />
											</div>
											<div class="omnify-modern-form-group">
												<label for="order_bump_price"><?php esc_html_e('Order Bump Price', 'omnifywp-ecommerce'); ?></label>
												<div class="omnify-input-wrapper omnify-input-with-prefix">
													<span class="omnify-input-prefix"><?php echo esc_html($omnify_symbol); ?></span>
													<input type="number" step="0.01" min="0" id="order_bump_price" name="order_bump_price" value="<?php echo esc_attr((string) ($omnify_settings['order_bump_price'] ?? 4.99)); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												</div>
											</div>
										</div>

										<div class="omnify-modern-form-group" style="margin-top: 15px;">
											<label for="order_bump_desc"><?php esc_html_e('Order Bump Description', 'omnifywp-ecommerce'); ?></label>
											<textarea id="order_bump_desc" name="order_bump_desc" rows="2" placeholder="Describe the benefit to encourage impulse additions..."><?php echo esc_textarea($omnify_settings['order_bump_desc'] ?? 'Skip the fulfillment queue with front-of-line priority dispatch plus 1-year accidental replacement guarantee.'); ?></textarea>
										</div>

										<!-- Live Order Bump Preview Box -->
										<div class="omnify-order-bump-preview-box">
											<div class="omnify-order-bump-preview-badge"><?php esc_html_e('Live Customer Checkout Preview', 'omnifywp-ecommerce'); ?></div>
											<div class="omnify-order-bump-preview-inner">
												<div class="omnify-order-bump-checkbox-mock"></div>
												<div class="omnify-order-bump-preview-content">
													<div class="omnify-order-bump-preview-header">
														<strong class="omnify-order-bump-preview-title" id="omnify-bump-title-preview"><?php echo esc_html($omnify_settings['order_bump_title'] ?? 'VIP Priority Processing & Extended Warranty'); ?></strong>
														<span class="omnify-order-bump-preview-price" id="omnify-bump-price-preview" data-symbol="<?php echo esc_attr($omnify_symbol); ?>"><?php echo esc_html($omnify_symbol . number_format((float)($omnify_settings['order_bump_price'] ?? 4.99), 2)); ?></span>
													</div>
													<p class="omnify-order-bump-preview-desc" id="omnify-bump-desc-preview"><?php echo esc_html($omnify_settings['order_bump_desc'] ?? 'Skip the fulfillment queue with front-of-line priority dispatch plus 1-year accidental replacement guarantee.'); ?></p>
												</div>
											</div>
										</div>
									</div>

									<div class="omnify-card-feature-strip" style="margin-top: 10px;">
										<div class="omnify-card-feature-strip-meta">
											<h5 class="omnify-card-feature-strip-title"><?php esc_html_e('Dynamic Safe & Secure Trust Badges', 'omnifywp-ecommerce'); ?></h5>
											<p class="omnify-card-feature-strip-desc"><?php esc_html_e('Automatically showcase verified trust badges for enabled payment gateways (Cards, PayPal, Razorpay, Manual Payment, SSL) in the checkout sidebar.', 'omnifywp-ecommerce'); ?></p>
										</div>
										<?php omnify_render_toggle('checkout_show_trust_box', $omnify_settings['checkout_show_trust_box'] ?? 1); ?>
									</div>

									<div data-cond-field="checkout_show_trust_box" data-cond-val="1">
										<div class="omnify-modern-form-group" style="margin-top: 10px;">
											<label for="checkout_trust_title"><?php esc_html_e('Trust Badges Heading', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="checkout_trust_title" name="checkout_trust_title" value="<?php echo esc_attr($omnify_settings['checkout_trust_title'] ?? 'Guaranteed Safe & Secure Checkout'); ?>" placeholder="<?php esc_attr_e('Guaranteed Safe & Secure Checkout', 'omnifywp-ecommerce'); ?>" />
										</div>
									</div>
								</div>
							</div>

							<!-- 4. Abandoned Cart Recovery Engine Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--recovery">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/><circle cx="12" cy="9" r="5"/><polyline points="12 7 12 9 14 10"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Abandoned Cart Recovery Engine', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Automatically capture checkout drop-offs and recover sales via scheduled reminder emails', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-checkout-card-header-right">
										<span style="font-size: 13px; font-weight: 500; color: #475569;"><?php esc_html_e('Enabled', 'omnifywp-ecommerce'); ?></span>
										<?php omnify_render_toggle('abandoned_cart_enabled', $omnify_settings['abandoned_cart_enabled'] ?? 1); ?>
									</div>
								</div>
								<div class="omnify-checkout-card-body" data-cond-field="abandoned_cart_enabled" data-cond-val="1">
									<div class="omnify-modern-form-row" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
										<div class="omnify-modern-form-group">
											<label for="abandoned_cart_delay_minutes"><?php esc_html_e('Send Reminder After', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix">
												<input type="number" id="abandoned_cart_delay_minutes" name="abandoned_cart_delay_minutes" min="5" value="<?php echo esc_attr($omnify_settings['abandoned_cart_delay_minutes'] ?? 60); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												<span class="omnify-input-suffix"><?php esc_html_e('mins', 'omnifywp-ecommerce'); ?></span>
											</div>
											<span class="help-text"><?php esc_html_e('Delay before sending the recovery message.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="abandoned_cart_expire_days"><?php esc_html_e('Recovery Link Expiry', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix">
												<input type="number" id="abandoned_cart_expire_days" name="abandoned_cart_expire_days" min="1" value="<?php echo esc_attr($omnify_settings['abandoned_cart_expire_days'] ?? 14); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												<span class="omnify-input-suffix"><?php esc_html_e('days', 'omnifywp-ecommerce'); ?></span>
											</div>
											<span class="help-text"><?php esc_html_e('Cart restore session expiration window.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="abandoned_cart_max_reminders"><?php esc_html_e('Maximum Reminders', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="abandoned_cart_max_reminders" name="abandoned_cart_max_reminders" min="1" max="5" value="<?php echo esc_attr($omnify_settings['abandoned_cart_max_reminders'] ?? 1); ?>" />
											<span class="help-text"><?php esc_html_e('Number of follow-up reminder emails.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="abandoned_cart_coupon_code"><?php esc_html_e('Incentive Coupon Code', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="abandoned_cart_coupon_code" name="abandoned_cart_coupon_code" placeholder="<?php esc_attr_e('e.g. SAVE10', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_settings['abandoned_cart_coupon_code'] ?? ''); ?>" style="text-transform: uppercase;" />
											<span class="help-text"><?php esc_html_e('Optional coupon auto-applied on the recovery link.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- 5. Digital Products & Download Limits Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--downloads">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Digital Products & Download Limits', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Manage secure signed download link expiration and maximum file access attempts', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="download_link_expiry"><?php esc_html_e('Download Link Expiry Window', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix">
												<input type="number" id="download_link_expiry" name="download_link_expiry" min="0" max="365" value="<?php echo esc_attr($omnify_settings['download_link_expiry'] ?? 7); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												<span class="omnify-input-suffix"><?php esc_html_e('days', 'omnifywp-ecommerce'); ?></span>
											</div>
											<span class="help-text"><?php esc_html_e('Active duration for signed file download URLs. Enter 0 for lifetime access.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="download_limit"><?php esc_html_e('Maximum Download Attempts per File', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix">
												<input type="number" id="download_limit" name="download_limit" min="0" value="<?php echo esc_attr($omnify_settings['download_limit'] ?? 0); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												<span class="omnify-input-suffix"><?php esc_html_e('times', 'omnifywp-ecommerce'); ?></span>
											</div>
											<span class="help-text"><?php esc_html_e('Maximum allowed download clicks per buyer. Enter 0 for unlimited downloads.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- 6. Store Policies & Customer Refunds Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--policies">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Store Policies & Refund Management', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Enforce Terms & Conditions acceptance and configure customer refund request windows', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
									<div class="omnify-card-feature-strip">
										<div class="omnify-card-feature-strip-meta">
											<h5 class="omnify-card-feature-strip-title"><?php esc_html_e('Require Terms & Conditions Agreement', 'omnifywp-ecommerce'); ?></h5>
											<p class="omnify-card-feature-strip-desc"><?php esc_html_e('Mandate that shoppers accept your legal terms and privacy policy before placing orders.', 'omnifywp-ecommerce'); ?></p>
										</div>
										<?php omnify_render_toggle('require_terms', $omnify_settings['require_terms'] ?? 0); ?>
									</div>

									<div data-cond-field="require_terms" data-cond-val="1">
										<div class="omnify-modern-form-group" style="margin-top: 10px;">
											<label for="terms_url"><?php esc_html_e('Terms & Conditions Page URL', 'omnifywp-ecommerce'); ?></label>
											<input type="url" id="terms_url" name="terms_url" value="<?php echo esc_attr($omnify_settings['terms_url'] ?? ''); ?>" placeholder="https://example.com/terms/" />
										</div>
									</div>

									<div class="omnify-card-feature-strip" style="margin-top: 10px;">
										<div class="omnify-card-feature-strip-meta">
											<h5 class="omnify-card-feature-strip-title"><?php esc_html_e('Enable Customer Refund Requests', 'omnifywp-ecommerce'); ?></h5>
											<p class="omnify-card-feature-strip-desc"><?php esc_html_e('Allow buyers to submit refund requests directly from their order history view.', 'omnifywp-ecommerce'); ?></p>
										</div>
										<?php omnify_render_toggle('enable_refunds', $omnify_settings['enable_refunds'] ?? 0); ?>
									</div>

									<div data-cond-field="enable_refunds" data-cond-val="1">
										<div class="omnify-modern-form-group" style="margin-top: 10px;">
											<label for="refund_duration"><?php esc_html_e('Eligible Refund Window', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-suffix" style="max-width: 220px;">
												<input type="number" id="refund_duration" name="refund_duration" min="1" max="120" value="<?php echo esc_attr((string) ($omnify_settings['refund_duration'] ?? 14)); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
												<span class="omnify-input-suffix"><?php esc_html_e('days', 'omnifywp-ecommerce'); ?></span>
											</div>
											<span class="help-text"><?php esc_html_e('Maximum days after purchase that a customer can submit a refund request.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

						<?php elseif ($omnify_active_section === 'fraud') : ?>
							<?php
							$omnify_currency = $omnify_settings['default_currency'] ?? 'USD';
							$omnify_currency_symbols = [
								'USD' => '$',
								'EUR' => '€',
								'GBP' => '£',
								'CAD' => '$',
								'AUD' => '$',
								'JPY' => '¥',
							];
							$omnify_symbol = $omnify_currency_symbols[$omnify_currency] ?? '$';
							?>
							<?php omnify_render_section_title(__('Fraud & Risk Prevention', 'omnifywp-ecommerce'), __('Configure automated risk flags, high-value order scrutiny, and velocity rate limits.', 'omnifywp-ecommerce')); ?>

							<!-- Fraud Guidance Banner -->
							<div class="omnify-zone-info-banner" style="background: #fef2f2; border-color: #fecaca; grid-column: span 2; margin-bottom: 24px;">
								<div class="omnify-zone-info-banner-icon" style="background: #fee2e2; color: #b91c1c;">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
								</div>
								<div class="omnify-zone-info-banner-content">
									<h4 style="color: #991b1b;"><?php esc_html_e('Automated Risk Scoring & Store Protection', 'omnifywp-ecommerce'); ?></h4>
									<p style="color: #b91c1c;"><?php esc_html_e('Protect your merchant account against fraudulent card testing, chargebacks, and throwaway disposable emails by configuring automated risk flags and rate limits.', 'omnifywp-ecommerce'); ?></p>
								</div>
							</div>

							<!-- Security Rules Card -->
							<div class="omnify-checkout-card">
								<div class="omnify-checkout-card-header">
									<div class="omnify-checkout-card-header-left">
										<div class="omnify-checkout-card-icon omnify-checkout-card-icon--fraud">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
										</div>
										<div class="omnify-checkout-card-meta">
											<h4 class="omnify-checkout-card-title"><?php esc_html_e('Risk Scoring & Threshold Rules', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-checkout-card-subtitle"><?php esc_html_e('Flag suspicious order patterns and enforce per-IP order rate limits', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
								</div>
								<div class="omnify-checkout-card-body">
									<div class="omnify-feature-triggers-grid">
										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('Flag Country Mismatch', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('fraud_country_mismatch_flag', $omnify_settings['fraud_country_mismatch_flag'] ?? 0); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Flag orders where the billing country does not match the delivery shipping country.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>

										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('Flag Disposable Emails', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('fraud_disposable_email_flag', $omnify_settings['fraud_disposable_email_flag'] ?? 0); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Detect and flag orders submitted using temporary or disposable mail inboxes.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>
									</div>

									<div class="omnify-modern-form-row" style="margin-top: 10px;">
										<div class="omnify-modern-form-group">
											<label for="fraud_max_value"><?php esc_html_e('High Value Order Threshold', 'omnifywp-ecommerce'); ?></label>
											<div class="omnify-input-wrapper omnify-input-with-prefix">
												<span class="omnify-input-prefix"><?php echo esc_html($omnify_symbol); ?></span>
												<input type="number" step="0.01" min="0" id="fraud_max_value" name="fraud_max_value" value="<?php echo esc_attr((string) ($omnify_settings['fraud_max_value'] ?? 500.00)); ?>" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
											</div>
											<span class="help-text"><?php esc_html_e('Flag orders exceeding this value for manual admin review before fulfillment.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="fraud_max_attempts_limit"><?php esc_html_e('IP Velocity Limit (Orders per 10 mins)', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="fraud_max_attempts_limit" name="fraud_max_attempts_limit" min="1" max="50" value="<?php echo esc_attr((string) ($omnify_settings['fraud_max_attempts_limit'] ?? 3)); ?>" />
											<span class="help-text"><?php esc_html_e('Flag multiple orders from the same IP address within a 10-minute window.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

						<?php elseif ($omnify_active_section === 'marketing') : ?>
							<?php omnify_render_section_title(__('Marketing & Tracking Integrations', 'omnifywp-ecommerce'), __('Connect analytics engines and conversion tracking pixels to measure storefront sales.', 'omnifywp-ecommerce')); ?>

							<div class="omnify-integrations-grid">
								<!-- Google Analytics 4 Card -->
								<div class="omnify-integration-card <?php echo !empty($omnify_settings['tracking_ga4_enabled']) ? 'is-enabled' : ''; ?>">
									<div class="omnify-integration-card__header">
										<div class="omnify-integration-card__brand">
											<div class="omnify-integration-icon omnify-integration-icon--ga4">
												<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M19.5 3h-15C3.12 3 2 4.12 2 5.5v13C2 19.88 3.12 21 4.5 21h15c1.38 0 2.5-1.12 2.5-2.5v-13C22 4.12 20.88 3 19.5 3z" fill="#F9AB00"/><path d="M7 17v-4.5M12 17V8M17 17v-7" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/></svg>
											</div>
											<div>
												<h4>Google Analytics 4</h4>
												<p><?php esc_html_e('Track visitors, page views, and eCommerce purchase funnels.', 'omnifywp-ecommerce'); ?></p>
											</div>
										</div>
										<div class="omnify-integration-card__toggle">
											<?php omnify_render_toggle('tracking_ga4_enabled', $omnify_settings['tracking_ga4_enabled'] ?? 0); ?>
										</div>
									</div>
									<div class="omnify-integration-card__body" data-cond-field="tracking_ga4_enabled" data-cond-val="1">
										<div class="omnify-modern-form-group">
											<label for="tracking_ga4_measurement_id"><?php esc_html_e('GA4 Measurement ID', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="tracking_ga4_measurement_id" name="tracking_ga4_measurement_id" value="<?php echo esc_attr($omnify_settings['tracking_ga4_measurement_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX" spellcheck="false" />
											<span class="help-text"><?php esc_html_e('Found in Google Analytics > Admin > Data Streams > Measurement ID.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>

								<!-- Meta Pixel Card -->
								<div class="omnify-integration-card <?php echo !empty($omnify_settings['tracking_meta_enabled']) ? 'is-enabled' : ''; ?>">
									<div class="omnify-integration-card__header">
										<div class="omnify-integration-card__brand">
											<div class="omnify-integration-icon omnify-integration-icon--meta">
												<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 0 0 8.44-9.9c0-5.53-4.5-10.02-10-10.02z" fill="#0081FB"/></svg>
											</div>
											<div>
												<h4>Meta Pixel</h4>
												<p><?php esc_html_e('Measure ad conversions and build remarketing custom audiences.', 'omnifywp-ecommerce'); ?></p>
											</div>
										</div>
										<div class="omnify-integration-card__toggle">
											<?php omnify_render_toggle('tracking_meta_enabled', $omnify_settings['tracking_meta_enabled'] ?? 0); ?>
										</div>
									</div>
									<div class="omnify-integration-card__body" data-cond-field="tracking_meta_enabled" data-cond-val="1">
										<div class="omnify-modern-form-group">
											<label for="tracking_meta_pixel_id"><?php esc_html_e('Meta Pixel ID', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="tracking_meta_pixel_id" name="tracking_meta_pixel_id" value="<?php echo esc_attr($omnify_settings['tracking_meta_pixel_id'] ?? ''); ?>" placeholder="XXXXXXXXXXXXXXX" spellcheck="false" />
											<span class="help-text"><?php esc_html_e('Numeric ID from Meta Events Manager > Data Sources.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<div class="omnify-modern-card-section" style="margin-top: 25px; border-top: 1px solid #edf2f6; padding-top: 20px;">
								<div class="omnify-modern-form-group" style="grid-column: span 2;">
									<label style="font-weight: 600; color: #0f172a; margin-bottom: 8px;"><?php esc_html_e('Standard Tracked Events', 'omnifywp-ecommerce'); ?></label>
									<div class="omnify-modern-events-badges" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php esc_html_e('View Item', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php esc_html_e('Add To Cart', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php esc_html_e('Begin Checkout', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-status-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php esc_html_e('Purchase', 'omnifywp-ecommerce'); ?></span>
									</div>
									<span class="help-text" style="font-size: 13px; color: #64748b; line-height: 1.4;"><?php esc_html_e('Purchase fires after local/manual checkout completes. Redirect providers also receive checkout-start events before the customer leaves the site.', 'omnifywp-ecommerce'); ?></span>
								</div>

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Console Debug Logging', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('tracking_debug_mode', $omnify_settings['tracking_debug_mode'] ?? 0, __('Log GA4 and Meta Pixel payload events in the browser console for live verification.', 'omnifywp-ecommerce')); ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php omnify_render_form_footer(); ?>
				</form>

			<?php elseif (in_array($omnify_active_section, ['design', 'email', 'tools'], true)) : ?>
				<!-- STOREFRONT DESIGN HUB -->
				<div class="omnify-modern-card-header-tabs">
					<div class="omnify-settings-subtabs">
						<a href="<?php echo esc_url(omnify_settings_url('design')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'design' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-art"></span>
							<?php esc_html_e('Storefront Design', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('email')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'email' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-email-alt"></span>
							<?php esc_html_e('Email Templates', 'omnifywp-ecommerce'); ?>
						</a>
						<a href="<?php echo esc_url(omnify_settings_url('tools')); ?>" class="omnify-settings-subtab <?php echo $omnify_active_section === 'tools' ? 'is-active' : ''; ?>">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e('Tools & Maintenance', 'omnifywp-ecommerce'); ?>
						</a>
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
										<?php omnify_render_color_picker('design_primary_color', $omnify_settings['design_primary_color'] ?? '#0f766e', '#0f766e'); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label for="design_hover_color"><?php esc_html_e('Primary Hover Color', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_color_picker('design_hover_color', $omnify_settings['design_hover_color'] ?? '#115e59', '#115e59'); ?>
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
											<option value="'Poppins', 'Montserrat', system-ui, -apple-system, sans-serif" <?php selected($omnify_settings['design_font_family'] ?? "'Poppins', 'Montserrat', system-ui, -apple-system, sans-serif", "'Poppins', 'Montserrat', system-ui, -apple-system, sans-serif"); ?>><?php esc_html_e('Poppins & Montserrat (Modern Geometric)', 'omnifywp-ecommerce'); ?></option>
											<option value="'Montserrat', sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', "'Montserrat', sans-serif"); ?>><?php esc_html_e('Montserrat (Bold / Modern)', 'omnifywp-ecommerce'); ?></option>
											<option value="'Poppins', sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', "'Poppins', sans-serif"); ?>><?php esc_html_e('Poppins (Clean Sans)', 'omnifywp-ecommerce'); ?></option>
											<option value="'Inter', system-ui, -apple-system, sans-serif" <?php selected($omnify_settings['design_font_family'] ?? '', "'Inter', system-ui, -apple-system, sans-serif"); ?>><?php esc_html_e('Inter (Clean)', 'omnifywp-ecommerce'); ?></option>
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
										<?php omnify_render_color_picker('storefront_hero_wave_color', $omnify_settings['storefront_hero_wave_color'] ?? '#e11d48', '#e11d48'); ?>
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
									<div class="omnify-feature-triggers-grid">
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
										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<span class="dashicons dashicons-marker" style="font-size: 14px; width: 14px; height: 14px; color: #0f766e;"></span>
													<strong><?php echo esc_html(sprintf(__('Feature #%d', 'omnifywp-ecommerce'), $i)); ?></strong>
												</div>
												<?php omnify_render_toggle($show_key, $f_show ? 1 : 0); ?>
											</div>
											<div class="omnify-feature-trigger-body">
												<div class="omnify-modern-form-group">
													<label for="<?php echo esc_attr($icon_key); ?>"><?php esc_html_e('Icon', 'omnifywp-ecommerce'); ?></label>
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
													<label for="<?php echo esc_attr($title_key); ?>"><?php esc_html_e('Title', 'omnifywp-ecommerce'); ?></label>
													<input type="text" id="<?php echo esc_attr($title_key); ?>" name="<?php echo esc_attr($title_key); ?>" value="<?php echo esc_attr($f_title); ?>" placeholder="<?php esc_attr_e('Title text', 'omnifywp-ecommerce'); ?>" />
												</div>
												<div class="omnify-modern-form-group">
													<label for="<?php echo esc_attr($desc_key); ?>"><?php esc_html_e('Description', 'omnifywp-ecommerce'); ?></label>
													<input type="text" id="<?php echo esc_attr($desc_key); ?>" name="<?php echo esc_attr($desc_key); ?>" value="<?php echo esc_attr($f_desc); ?>" placeholder="<?php esc_attr_e('Short description', 'omnifywp-ecommerce'); ?>" />
												</div>
											</div>
										</div>
									<?php endfor; ?>
									</div>
								</div>
							</div>

							<!-- Panel 2: Catalog & Filters -->
							<div class="omnify-modern-design-panel" id="omnify-design-panel-catalog" style="display: none;">
								<?php omnify_render_section_title(__('Catalog & Filters', 'omnifywp-ecommerce'), __('Choose the default product browsing layout, products count, and search/attribute filters.', 'omnifywp-ecommerce')); ?>

								<div class="omnify-modern-form-row">
									<div class="omnify-modern-form-group">
										<label for="storefront_catalog_layout"><?php esc_html_e('Default Product View', 'omnifywp-ecommerce'); ?></label>
										<select id="storefront_catalog_layout" name="storefront_catalog_layout">
											<option value="grid" <?php selected($omnify_catalog_layout, 'grid'); ?>><?php esc_html_e('Grid cards (Standard 3 Columns)', 'omnifywp-ecommerce'); ?></option>
											<option value="2" <?php selected($omnify_catalog_layout, '2'); ?>><?php esc_html_e('2 Columns (Large View)', 'omnifywp-ecommerce'); ?></option>
											<option value="4" <?php selected($omnify_catalog_layout, '4'); ?>><?php esc_html_e('4 Columns (Dense View)', 'omnifywp-ecommerce'); ?></option>
											<option value="list" <?php selected($omnify_catalog_layout, 'list'); ?>><?php esc_html_e('Detailed List rows', 'omnifywp-ecommerce'); ?></option>
											<option value="compact" <?php selected($omnify_catalog_layout, 'compact'); ?>><?php esc_html_e('Compact Rows', 'omnifywp-ecommerce'); ?></option>
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
											<option value="2" <?php selected($omnify_storefront_grid_columns, 2); ?>><?php esc_html_e('2 Columns (Large View)', 'omnifywp-ecommerce'); ?></option>
											<option value="3" <?php selected($omnify_storefront_grid_columns, 3); ?>><?php esc_html_e('3 Columns (Standard)', 'omnifywp-ecommerce'); ?></option>
											<option value="4" <?php selected($omnify_storefront_grid_columns, 4); ?>><?php esc_html_e('4 Columns (Dense View)', 'omnifywp-ecommerce'); ?></option>
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

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Infinite Scroll Loading', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_infinite_scroll', ! isset($omnify_settings['storefront_infinite_scroll']) || ! empty($omnify_settings['storefront_infinite_scroll']) ? 1 : 0, __('Continuously fetch more products as customer reaches end of page.', 'omnifywp-ecommerce')); ?>
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

								<div class="omnify-modern-form-row" style="margin-top: 15px;">
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Sale / Discount Badge', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_discount_badge', ! isset($omnify_settings['storefront_show_discount_badge']) || ! empty($omnify_settings['storefront_show_discount_badge']), __('Displays ribbon or percentage discount tag on cards.', 'omnifywp-ecommerce')); ?>
									</div>
									<div class="omnify-modern-form-group">
										<label><?php esc_html_e('Show Product Page Trust Badges', 'omnifywp-ecommerce'); ?></label>
										<?php omnify_render_toggle('storefront_show_reassurance', ! isset($omnify_settings['storefront_show_reassurance']) || ! empty($omnify_settings['storefront_show_reassurance']), __('Displays the 3-point security and support card on product pages.', 'omnifywp-ecommerce')); ?>
									</div>
								</div>

								<!-- Reassurance Checklist Customization -->
								<div data-cond-field="storefront_show_reassurance" data-cond-val="1">
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
							<?php
							$omnify_all_trigger_keys = [
								'email_receipt',
								'email_order_shipped_dispatch',
								'email_order_ready_pickup',
								'email_order_cancelled_notice',
								'email_customer_note',
								'email_customer_welcome',
								'email_payment_pending',
								'email_refund_processed',
								'email_status_changed',
								'email_abandoned_cart',
								'email_admin_new_order',
								'email_admin_failed_order',
								'email_admin_fraud_alert',
								'email_refund_requested',
								'email_low_stock',
								'email_out_of_stock',
							];
							$omnify_active_triggers_count = 0;
							foreach ($omnify_all_trigger_keys as $omnify_trig_key) {
								if (! empty($omnify_settings[$omnify_trig_key])) {
									$omnify_active_triggers_count++;
								}
							}
							$omnify_is_smtp_active = ! empty($omnify_settings['email_smtp_enabled']);
							?>

							<?php omnify_render_section_title(__('Email Notifications Hub', 'omnifywp-ecommerce'), __('Configure transactional outgoing SMTP configurations, customer notifications, and store administrator alerts.', 'omnifywp-ecommerce')); ?>

							<!-- Metrics Strip -->
							<div class="omnify-email-metrics-grid" style="grid-column: span 2;">
								<div class="omnify-email-metric-card">
									<div class="omnify-email-metric-header">
										<span class="omnify-email-metric-label"><?php esc_html_e('Active Email Triggers', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-email-metric-icon omnify-email-metric-icon--emerald">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
										</span>
									</div>
									<div class="omnify-email-metric-value" id="omnify-active-triggers-stat"><?php echo esc_html($omnify_active_triggers_count); ?> <?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></div>
									<div class="omnify-email-metric-sub"><?php echo esc_html(sprintf(__('Out of %d configured events', 'omnifywp-ecommerce'), count($omnify_all_trigger_keys))); ?></div>
								</div>

								<div class="omnify-email-metric-card">
									<div class="omnify-email-metric-header">
										<span class="omnify-email-metric-label"><?php esc_html_e('Customer Notifications', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-email-metric-icon omnify-email-metric-icon--blue">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
										</span>
									</div>
									<div class="omnify-email-metric-value">10 <?php esc_html_e('Triggers', 'omnifywp-ecommerce'); ?></div>
									<div class="omnify-email-metric-sub"><?php esc_html_e('Receipts, dispatch, tracking & notes', 'omnifywp-ecommerce'); ?></div>
								</div>

								<div class="omnify-email-metric-card">
									<div class="omnify-email-metric-header">
										<span class="omnify-email-metric-label"><?php esc_html_e('Store Admin Alerts', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-email-metric-icon omnify-email-metric-icon--amber">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
										</span>
									</div>
									<div class="omnify-email-metric-value">6 <?php esc_html_e('Alert Types', 'omnifywp-ecommerce'); ?></div>
									<div class="omnify-email-metric-sub"><?php esc_html_e('Orders, failures, stock & fraud radar', 'omnifywp-ecommerce'); ?></div>
								</div>

								<div class="omnify-email-metric-card">
									<div class="omnify-email-metric-header">
										<span class="omnify-email-metric-label"><?php esc_html_e('Dispatch Engine', 'omnifywp-ecommerce'); ?></span>
										<span class="omnify-email-metric-icon omnify-email-metric-icon--purple">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
										</span>
									</div>
									<div class="omnify-email-metric-value"><?php echo $omnify_is_smtp_active ? esc_html__('Custom SMTP', 'omnifywp-ecommerce') : esc_html__('Standard Mail', 'omnifywp-ecommerce'); ?></div>
									<div class="omnify-email-metric-sub"><?php echo $omnify_is_smtp_active ? esc_html($omnify_settings['email_smtp_host'] ?: 'Authenticated Host') : esc_html__('WordPress wp_mail() default', 'omnifywp-ecommerce'); ?></div>
								</div>
							</div>

							<!-- Category Filtering Toolbar -->
							<div class="omnify-email-toolbar" style="grid-column: span 2;">
								<div class="omnify-email-filter-tabs">
									<button type="button" class="omnify-email-filter-tab is-active" data-email-filter="all">
										<?php esc_html_e('All Triggers', 'omnifywp-ecommerce'); ?> <span class="omnify-filter-count">16</span>
									</button>
									<button type="button" class="omnify-email-filter-tab" data-email-filter="customer">
										<?php esc_html_e('Customer Notifications', 'omnifywp-ecommerce'); ?> <span class="omnify-filter-count">10</span>
									</button>
									<button type="button" class="omnify-email-filter-tab" data-email-filter="admin">
										<?php esc_html_e('Store Admin Alerts', 'omnifywp-ecommerce'); ?> <span class="omnify-filter-count">6</span>
									</button>
									<button type="button" class="omnify-email-filter-tab" data-email-filter="routing">
										<?php esc_html_e('Sender & Profile', 'omnifywp-ecommerce'); ?>
									</button>
									<button type="button" class="omnify-email-filter-tab" data-email-filter="smtp">
										<?php esc_html_e('SMTP Gateway', 'omnifywp-ecommerce'); ?>
									</button>
									<button type="button" class="omnify-email-filter-tab" data-email-filter="logs">
										<?php esc_html_e('Delivery Logs', 'omnifywp-ecommerce'); ?>
									</button>
								</div>
								<div class="omnify-email-toolbar-actions">
									<div class="omnify-email-toolbar-search omnify-email-search-wrap">
										<span class="omnify-email-search-icon">
											<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
										</span>
										<input type="text" id="omnify-email-search-input" placeholder="<?php esc_attr_e('Filter triggers...', 'omnifywp-ecommerce'); ?>" />
									</div>
									<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm" id="omnify-email-expand-all">
										<?php esc_html_e('Expand All', 'omnifywp-ecommerce'); ?>
									</button>
								</div>
							</div>

							<!-- Sender Details & Global Identity Card -->
							<div class="omnify-email-card is-collapsed" data-email-category="routing" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--smtp">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Sender & Global Profile', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Configure outgoing email identity and master dispatch controls', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Global Profile', 'omnifywp-ecommerce'); ?></span>
										<span style="font-size: 13px; font-weight: 500; color: #475569;"><?php esc_html_e('Master Dispatch', 'omnifywp-ecommerce'); ?></span>
										<?php omnify_render_toggle('email_notifications_enabled', ! array_key_exists('email_notifications_enabled', $omnify_settings) || ! empty($omnify_settings['email_notifications_enabled']) ? 1 : 0); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_from_name"><?php esc_html_e('From Name', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($omnify_settings['email_from_name'] ?? ''); ?>" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
											<span class="help-text"><?php esc_html_e('The sender name displayed in customer inboxes.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="email_from_address"><?php esc_html_e('From Email', 'omnifywp-ecommerce'); ?></label>
											<input type="email" id="email_from_address" name="email_from_address" value="<?php echo esc_attr($omnify_settings['email_from_address'] ?? ''); ?>" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>" />
											<span class="help-text"><?php esc_html_e('The outgoing address from which automated emails originate.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_reply_to"><?php esc_html_e('Reply-To Email', 'omnifywp-ecommerce'); ?></label>
											<input type="email" id="email_reply_to" name="email_reply_to" value="<?php echo esc_attr($omnify_settings['email_reply_to'] ?? ''); ?>" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>" />
											<span class="help-text"><?php esc_html_e('Destination inbox when customers reply to transactional emails.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="admin_notification_email"><?php esc_html_e('Admin Recipient Address', 'omnifywp-ecommerce'); ?></label>
											<input type="email" id="admin_notification_email" name="admin_notification_email" value="<?php echo esc_attr($omnify_settings['admin_notification_email'] ?? get_option('admin_email')); ?>" />
											<span class="help-text"><?php esc_html_e('Destination inbox for store order alerts and inventory notices.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Customer Transactional Notifications Heading -->
							<div class="omnify-email-section-heading" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-section-heading-left">
									<span class="omnify-email-section-heading-icon">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
									</span>
									<h4 class="omnify-email-section-heading-title"><?php esc_html_e('Customer Transactional Notifications', 'omnifywp-ecommerce'); ?></h4>
									<span class="omnify-email-section-count">10</span>
								</div>
							</div>

							<!-- 1. Order Receipt -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--receipt">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Order Receipt & Confirmation', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent immediately to customers upon successful checkout payment', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_receipt']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_receipt', $omnify_settings['email_receipt'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_receipt" data-cond-val="1">
									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_receipt_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_receipt_subject" name="email_receipt_subject" value="<?php echo esc_attr($omnify_settings['email_receipt_subject'] ?? '[{site_name}] Order Receipt {order_id}'); ?>" />
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Include Download Links', 'omnifywp-ecommerce'); ?></label>
											<div style="margin-top: 8px;">
												<?php omnify_render_toggle('email_download_links', $omnify_settings['email_download_links'] ?? 1); ?>
											</div>
											<span class="help-text"><?php esc_html_e('Automatically attach secure download links for digital products.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_receipt_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_receipt_intro" name="email_receipt_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_receipt_intro'] ?? 'Thank you for your purchase! Your order has been completed successfully.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{order_status}']); ?>
									</div>
								</div>
							</div>

							<!-- 2. Order Shipped & Out for Delivery -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--shipped">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Order Shipped & Out for Delivery', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when goods are dispatched with courier name, tracking number, and live tracking URL', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_order_shipped_dispatch']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_order_shipped_dispatch', $omnify_settings['email_order_shipped_dispatch'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_order_shipped_dispatch" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_order_shipped_dispatch_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_order_shipped_dispatch_subject" name="email_order_shipped_dispatch_subject" value="<?php echo esc_attr($omnify_settings['email_order_shipped_dispatch_subject'] ?? '[{site_name}] Your Order {order_id} Has Shipped!'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_order_shipped_dispatch_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_order_shipped_dispatch_intro" name="email_order_shipped_dispatch_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_order_shipped_dispatch_intro'] ?? 'Great news! Your package is on its way. Track your delivery progress using the carrier link below.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{tracking_carrier}', '{tracking_number}', '{tracking_url}']); ?>
									</div>
								</div>
							</div>

							<!-- 3. Order Ready for Store Pickup -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--pickup">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Order Ready for Store Pickup', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when a click-and-collect or local pickup order is packaged and awaiting customer pickup', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_order_ready_pickup']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_order_ready_pickup', $omnify_settings['email_order_ready_pickup'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_order_ready_pickup" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_order_ready_pickup_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_order_ready_pickup_subject" name="email_order_ready_pickup_subject" value="<?php echo esc_attr($omnify_settings['email_order_ready_pickup_subject'] ?? '[{site_name}] Your Order {order_id} is Ready for Pickup!'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_order_ready_pickup_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_order_ready_pickup_intro" name="email_order_ready_pickup_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_order_ready_pickup_intro'] ?? 'Your order is packaged and ready for pickup at our store counter. Please bring a valid photo ID and this confirmation.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{store_name}', '{store_address}', '{store_phone}']); ?>
									</div>
								</div>
							</div>

							<!-- 4. Order Cancelled / Voided Notice -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--cancelled">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Order Cancelled / Voided Notice', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when an order is cancelled, voided, or terminated by store management', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_order_cancelled_notice']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_order_cancelled_notice', $omnify_settings['email_order_cancelled_notice'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_order_cancelled_notice" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_order_cancelled_notice_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_order_cancelled_notice_subject" name="email_order_cancelled_notice_subject" value="<?php echo esc_attr($omnify_settings['email_order_cancelled_notice_subject'] ?? '[{site_name}] Order {order_id} Cancellation Notice'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_order_cancelled_notice_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_order_cancelled_notice_intro" name="email_order_cancelled_notice_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_order_cancelled_notice_intro'] ?? 'Your order has been cancelled. Any pre-authorized charges will be released according to your bank policies.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}']); ?>
									</div>
								</div>
							</div>

							<!-- 5. Customer Note / Message -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--note">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Customer Note / Direct Message', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when a store representative or fulfillment team appends a note to an existing order', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_customer_note']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_customer_note', $omnify_settings['email_customer_note'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_customer_note" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_customer_note_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_customer_note_subject" name="email_customer_note_subject" value="<?php echo esc_attr($omnify_settings['email_customer_note_subject'] ?? '[{site_name}] Note Added to Your Order {order_id}'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_customer_note_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_customer_note_intro" name="email_customer_note_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_customer_note_intro'] ?? 'A new note has been added to your order regarding delivery, customized details, or support.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_note}']); ?>
									</div>
								</div>
							</div>

							<!-- 6. Customer Welcome & Portal Access -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--welcome">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Customer Welcome & Portal Access', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent upon new account creation with login credentials and customer portal direct link', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_customer_welcome']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_customer_welcome', $omnify_settings['email_customer_welcome'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_customer_welcome" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_customer_welcome_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_customer_welcome_subject" name="email_customer_welcome_subject" value="<?php echo esc_attr($omnify_settings['email_customer_welcome_subject'] ?? 'Welcome to [{site_name}] — Your Customer Account Details'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_customer_welcome_intro"><?php esc_html_e('Message Intro Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_customer_welcome_intro" name="email_customer_welcome_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_customer_welcome_intro'] ?? 'Welcome! Your customer account has been created. You can log in at any time to review orders, manage subscriptions, and download purchased files.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{customer_name}', '{customer_email}', '{customer_portal_url}', '{login_url}']); ?>
									</div>
								</div>
							</div>

							<!-- 7. Manual & Offline Payment Pending -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--pending">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Manual & Offline Payment Instructions', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when an order is placed awaiting bank transfer, cheque, COD, or manual approval', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_payment_pending']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_payment_pending', $omnify_settings['email_payment_pending'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_payment_pending" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_payment_pending_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_payment_pending_subject" name="email_payment_pending_subject" value="<?php echo esc_attr($omnify_settings['email_payment_pending_subject'] ?? '[{site_name}] Order {order_id} — Awaiting Payment'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_payment_pending_intro"><?php esc_html_e('Message Template Text', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_payment_pending_intro" name="email_payment_pending_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_payment_pending_intro'] ?? 'Thank you for your order! It is awaiting payment.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{order_status}']); ?>
									</div>
								</div>
							</div>

							<!-- 8. Refund Processed -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--refund">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Refund Processed Notification', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent to customers once a full or partial refund is completed in store records', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_refund_processed']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_refund_processed', $omnify_settings['email_refund_processed'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_refund_processed" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_refund_processed_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_refund_processed_subject" name="email_refund_processed_subject" value="<?php echo esc_attr($omnify_settings['email_refund_processed_subject'] ?? '[{site_name}] Refund processed for Order {order_id}'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_refund_processed_body"><?php esc_html_e('Message Text', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_refund_processed_body" name="email_refund_processed_body" rows="3"><?php echo esc_textarea($omnify_settings['email_refund_processed_body'] ?? "Dear Customer,\n\nYour refund of {refund_amount} for Order {order_id} has been processed successfully.\n\nThank you."); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{refund_amount}']); ?>
									</div>
								</div>
							</div>

							<!-- 9. Order Status Changed -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--status">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Order Status Update Notice', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent when an order status transitions (e.g. processing, completed, or cancelled)', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_status_changed']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_status_changed', $omnify_settings['email_status_changed'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_status_changed" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_status_changed_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_status_changed_subject" name="email_status_changed_subject" value="<?php echo esc_attr($omnify_settings['email_status_changed_subject'] ?? '[{site_name}] Order {order_id} status updated'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_status_changed_body"><?php esc_html_e('Message Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_status_changed_body" name="email_status_changed_body" rows="3"><?php echo esc_textarea($omnify_settings['email_status_changed_body'] ?? 'Your order status changed from {old_status} to {new_status}.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{order_status}', '{old_status}', '{new_status}']); ?>
									</div>

									<div class="omnify-email-trigger-statuses">
										<span class="omnify-email-trigger-label"><?php esc_html_e('Active Status Triggers:', 'omnifywp-ecommerce'); ?></span>
										<div class="omnify-email-trigger-options">
											<label class="omnify-email-trigger-chip">
												<input type="checkbox" name="email_order_completed" value="1" <?php checked($omnify_settings['email_order_completed'] ?? true); ?> />
												<span><?php esc_html_e('Completed', 'omnifywp-ecommerce'); ?></span>
											</label>
											<label class="omnify-email-trigger-chip">
												<input type="checkbox" name="email_order_shipped" value="1" <?php checked($omnify_settings['email_order_shipped'] ?? true); ?> />
												<span><?php esc_html_e('Shipped', 'omnifywp-ecommerce'); ?></span>
											</label>
											<label class="omnify-email-trigger-chip">
												<input type="checkbox" name="email_order_processing" value="1" <?php checked($omnify_settings['email_order_processing'] ?? true); ?> />
												<span><?php esc_html_e('Processing', 'omnifywp-ecommerce'); ?></span>
											</label>
											<label class="omnify-email-trigger-chip">
												<input type="checkbox" name="email_order_cancelled" value="1" <?php checked($omnify_settings['email_order_cancelled'] ?? true); ?> />
												<span><?php esc_html_e('Cancelled', 'omnifywp-ecommerce'); ?></span>
											</label>
										</div>
									</div>

									<details class="omnify-email-accordion">
										<summary class="omnify-email-accordion-summary">
											<span><?php esc_html_e('Advanced: Specific Templates per Status Override', 'omnifywp-ecommerce'); ?></span>
											<svg class="omnify-accordion-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</summary>
										<div class="omnify-email-accordion-content">
											<p style="font-size: 12.5px; color: #64748b; margin: 0 0 10px;"><?php esc_html_e('Leave override fields blank to use the standard default template above:', 'omnifywp-ecommerce'); ?></p>
											<div class="omnify-status-override-grid">
												<?php foreach (['completed' => 'Completed', 'shipped' => 'Shipped', 'processing' => 'Processing', 'cancelled' => 'Cancelled'] as $omnify_st => $omnify_label): ?>
													<div class="omnify-status-override-card">
														<span class="omnify-status-pill-badge omnify-status-pill-badge--<?php echo esc_attr($omnify_st); ?>">
															<?php echo esc_html($omnify_label); ?>
														</span>
														<div class="omnify-modern-form-group">
															<label style="font-size: 12px;"><?php esc_html_e('Subject Override', 'omnifywp-ecommerce'); ?></label>
															<input type="text" name="email_order_<?php echo esc_attr($omnify_st); ?>_subject" value="<?php echo esc_attr($omnify_settings['email_order_' . $omnify_st . '_subject'] ?? ''); ?>" placeholder="<?php esc_attr_e('Default subject if empty', 'omnifywp-ecommerce'); ?>" />
														</div>
														<div class="omnify-modern-form-group">
															<label style="font-size: 12px;"><?php esc_html_e('Body Override Text', 'omnifywp-ecommerce'); ?></label>
															<textarea name="email_order_<?php echo esc_attr($omnify_st); ?>_body" rows="2" placeholder="<?php esc_attr_e('Default body if empty', 'omnifywp-ecommerce'); ?>"><?php echo esc_textarea($omnify_settings['email_order_' . $omnify_st . '_body'] ?? ''); ?></textarea>
														</div>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									</details>
								</div>
							</div>

							<!-- 10. Abandoned Cart Recovery -->
							<div class="omnify-email-card is-collapsed" data-email-category="customer" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--cart">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Abandoned Cart Recovery', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent automatically to customers who abandoned items in their checkout session', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--customer"><?php esc_html_e('Customer', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_abandoned_cart']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_abandoned_cart', $omnify_settings['email_abandoned_cart'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_abandoned_cart" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_abandoned_cart_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_abandoned_cart_subject" name="email_abandoned_cart_subject" value="<?php echo esc_attr($omnify_settings['email_abandoned_cart_subject'] ?? '[{site_name}] Complete your order'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_abandoned_cart_intro"><?php esc_html_e('Message Text', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_abandoned_cart_intro" name="email_abandoned_cart_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_abandoned_cart_intro'] ?? 'You left something in your cart. Use the button below to return to checkout and complete your order.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{customer_name}', '{customer_email}', '{product_name}', '{cart_total}', '{coupon_code}', '{recovery_url}']); ?>
									</div>
								</div>
							</div>

							<!-- Store Admin Alerts Heading -->
							<div class="omnify-email-section-heading" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-section-heading-left">
									<span class="omnify-email-section-heading-icon">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
									</span>
									<h4 class="omnify-email-section-heading-title"><?php esc_html_e('Store Admin Alerts & Operations', 'omnifywp-ecommerce'); ?></h4>
									<span class="omnify-email-section-count">6</span>
								</div>
							</div>

							<!-- Admin Alert 1: New Order Notification -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--admin">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('New Order Notification', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Notify store administration as soon as a new customer order is placed', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_admin_new_order']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_admin_new_order', $omnify_settings['email_admin_new_order'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_admin_new_order" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_admin_new_order_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_admin_new_order_subject" name="email_admin_new_order_subject" value="<?php echo esc_attr($omnify_settings['email_admin_new_order_subject'] ?? '[{site_name}] New Customer Order #{order_id}'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_admin_new_order_intro"><?php esc_html_e('Message Body Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_admin_new_order_intro" name="email_admin_new_order_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_admin_new_order_intro'] ?? 'A new order has been received on your store.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}']); ?>
									</div>
								</div>
							</div>

							<!-- Admin Alert 2: Failed Payment / Order Declined Alert -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--failed">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Failed Payment / Order Declined Alert', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent to store admin immediately when a checkout payment is rejected or gateway error occurs', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_admin_failed_order']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_admin_failed_order', $omnify_settings['email_admin_failed_order'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_admin_failed_order" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_admin_failed_order_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_admin_failed_order_subject" name="email_admin_failed_order_subject" value="<?php echo esc_attr($omnify_settings['email_admin_failed_order_subject'] ?? '[{site_name}] Payment Failed for Order #{order_id}'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_admin_failed_order_intro"><?php esc_html_e('Message Body Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_admin_failed_order_intro" name="email_admin_failed_order_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_admin_failed_order_intro'] ?? 'An order payment transaction has failed at checkout. Immediate review is recommended to assist the customer.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{failure_reason}']); ?>
									</div>
								</div>
							</div>

							<!-- Admin Alert 3: High-Risk / Fraud Suspicion Alert -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--fraud">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('High-Risk / Fraud Suspicion Alert', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Triggered when an order triggers suspicious risk signals or exceeds risk thresholds', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_admin_fraud_alert']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_admin_fraud_alert', $omnify_settings['email_admin_fraud_alert'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_admin_fraud_alert" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_admin_fraud_alert_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_admin_fraud_alert_subject" name="email_admin_fraud_alert_subject" value="<?php echo esc_attr($omnify_settings['email_admin_fraud_alert_subject'] ?? '[{site_name}] WARNING: High-Risk Order #{order_id} Flagged'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_admin_fraud_alert_intro"><?php esc_html_e('Message Body Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_admin_fraud_alert_intro" name="email_admin_fraud_alert_intro" rows="3"><?php echo esc_textarea($omnify_settings['email_admin_fraud_alert_intro'] ?? 'Attention: An order has been placed that matched automated fraud radar triggers. Review before fulfillment.'); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{order_total}', '{fraud_score}', '{fraud_reasons}']); ?>
									</div>
								</div>
							</div>

							<!-- Admin Alert 4: Refund Requested Alert -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--refund">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Refund Requested Alert', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Sent after a customer submits an official refund or dispute request', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_refund_requested']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_refund_requested', $omnify_settings['email_refund_requested'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_refund_requested" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_refund_requested_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_refund_requested_subject" name="email_refund_requested_subject" value="<?php echo esc_attr($omnify_settings['email_refund_requested_subject'] ?? '[{site_name}] Refund requested for Order {order_id}'); ?>" />
									</div>
									<div class="omnify-modern-form-group">
										<label for="email_refund_requested_body"><?php esc_html_e('Message Body Template', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_refund_requested_body" name="email_refund_requested_body" rows="3"><?php echo esc_textarea($omnify_settings['email_refund_requested_body'] ?? "Dear Customer,\n\nYour refund request for Order {order_id} has been received and is currently under review.\n\nReason: {refund_reason}\n\nWe will update you shortly."); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{order_id}', '{customer_name}', '{customer_email}', '{order_total}', '{refund_reason}']); ?>
									</div>
								</div>
							</div>

							<!-- Admin Alert 5: Low-Stock Inventory Alert -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--alert">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Low-Stock Inventory Alert', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Triggered whenever an item stock level falls below its low-stock threshold', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_low_stock']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_low_stock', $omnify_settings['email_low_stock'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_low_stock" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_low_stock_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_low_stock_subject" name="email_low_stock_subject" value="<?php echo esc_attr($omnify_settings['email_low_stock_subject'] ?? '[{site_name}] Low stock alert: {product_name}'); ?>" />
										<?php omnify_render_token_pills(['{site_name}', '{product_name}', '{variation}', '{sku}', '{stock_qty}', '{threshold}']); ?>
									</div>
								</div>
							</div>

							<!-- Admin Alert 6: Out of Stock Alert (Critical 0 Units) -->
							<div class="omnify-email-card is-collapsed" data-email-category="admin" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--outofstock">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/><path d="m17 13 5 5m0-5-5 5"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Out of Stock Alert (Critical 0 Units)', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Instant emergency notification triggered when a product reaches 0 inventory and becomes unavailable', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span class="omnify-email-card-type-badge omnify-email-card-type-badge--admin"><?php esc_html_e('Admin Alert', 'omnifywp-ecommerce'); ?></span>
										<?php $omnify_trig_active = ! empty($omnify_settings['email_out_of_stock']); ?>
										<span class="omnify-email-card-status-badge <?php echo $omnify_trig_active ? 'is-active' : 'is-paused'; ?>"><?php echo $omnify_trig_active ? esc_html__('Active', 'omnifywp-ecommerce') : esc_html__('Paused', 'omnifywp-ecommerce'); ?></span>
										<button type="button" class="omnify-email-preview-btn">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
										</button>
										<?php omnify_render_toggle('email_out_of_stock', $omnify_settings['email_out_of_stock'] ?? 1); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_out_of_stock" data-cond-val="1">
									<div class="omnify-modern-form-group">
										<label for="email_out_of_stock_subject"><?php esc_html_e('Subject Line', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="email_out_of_stock_subject" name="email_out_of_stock_subject" value="<?php echo esc_attr($omnify_settings['email_out_of_stock_subject'] ?? '[{site_name}] CRITICAL: Out of stock alert: {product_name}'); ?>" />
										<?php omnify_render_token_pills(['{site_name}', '{product_name}', '{variation}', '{sku}', '{stock_qty}']); ?>
									</div>
								</div>
							</div>

							<!-- Advanced Dispatch Options Heading & Card -->
							<div class="omnify-email-card is-collapsed" data-email-category="routing" style="grid-column: span 2; margin-top: 10px;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--smtp">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Dispatch & Routing Options', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Configure carbon-copy receipts, push notifications, and detailed status updates', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body">
									<div class="omnify-feature-triggers-grid">
										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('BCC Admin on Customer Emails', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('email_bcc_admin', $omnify_settings['email_bcc_admin'] ?? 0); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Sends a hidden copy of all customer emails to the admin recipient address.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>

										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('Send Admin Copy of Receipts', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('email_send_receipt_copy', ! array_key_exists('email_send_receipt_copy', $omnify_settings) || ! empty($omnify_settings['email_send_receipt_copy']) ? 1 : 0); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Deliver an identical copy of each order receipt to the store inbox.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>

										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('Include Order Summary in Status Updates', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('email_status_include_details', ! array_key_exists('email_status_include_details', $omnify_settings) || ! empty($omnify_settings['email_status_include_details']) ? 1 : 0); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Appends purchased line items and order totals below status change emails.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>

										<div class="omnify-feature-trigger-card">
											<div class="omnify-feature-trigger-header">
												<div class="omnify-feature-trigger-badge">
													<strong><?php esc_html_e('Browser Push Notifications', 'omnifywp-ecommerce'); ?></strong>
												</div>
												<?php omnify_render_toggle('push_notifications_enabled', $omnify_settings['push_notifications_enabled'] ?? 1); ?>
											</div>
											<p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.4;">
												<?php esc_html_e('Display instant desktop push alerts when new orders are placed.', 'omnifywp-ecommerce'); ?>
											</p>
										</div>
									</div>
								</div>
							</div>

							<!-- Custom SMTP Configuration Card -->
							<div class="omnify-email-card is-collapsed" data-email-category="smtp" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--smtp">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Custom SMTP Server Delivery', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Route outgoing mail through a dedicated authenticated SMTP provider', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<span style="font-size: 13px; font-weight: 500; color: #475569;"><?php esc_html_e('Enable SMTP', 'omnifywp-ecommerce'); ?></span>
										<?php omnify_render_toggle('email_smtp_enabled', $omnify_settings['email_smtp_enabled'] ?? 0); ?>
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body" data-cond-field="email_smtp_enabled" data-cond-val="1">
									<div class="omnify-smtp-notice">
										<div class="omnify-smtp-notice-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
										</div>
										<div>
											<strong><?php esc_html_e('Enhanced Delivery Guarantee', 'omnifywp-ecommerce'); ?>:</strong>
											<?php esc_html_e('Standard WordPress PHP mail frequently lands in spam. Using a dedicated external SMTP service (SendGrid, Mailgun, Amazon SES, Postmark, Google Workspace) guarantees high deliverability and inbox placement.', 'omnifywp-ecommerce'); ?>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_smtp_host"><?php esc_html_e('SMTP Host', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_smtp_host" name="email_smtp_host" value="<?php echo esc_attr($omnify_settings['email_smtp_host'] ?? ''); ?>" placeholder="smtp.example.com" />
											<span class="help-text"><?php esc_html_e('The outbound mail server address provided by your email service.', 'omnifywp-ecommerce'); ?></span>
										</div>
										<div class="omnify-modern-form-group">
											<label for="email_smtp_port"><?php esc_html_e('SMTP Port', 'omnifywp-ecommerce'); ?></label>
											<input type="number" id="email_smtp_port" name="email_smtp_port" value="<?php echo esc_attr((string) ($omnify_settings['email_smtp_port'] ?? 587)); ?>" placeholder="587" />
											<span class="help-text"><?php esc_html_e('Common ports: 587 (TLS), 465 (SSL), or 25.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_smtp_encryption"><?php esc_html_e('SMTP Encryption', 'omnifywp-ecommerce'); ?></label>
											<select id="email_smtp_encryption" name="email_smtp_encryption">
												<option value="" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), ''); ?>><?php esc_html_e('None (Unencrypted)', 'omnifywp-ecommerce'); ?></option>
												<option value="ssl" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), 'ssl'); ?>><?php esc_html_e('SSL (Port 465)', 'omnifywp-ecommerce'); ?></option>
												<option value="tls" <?php selected(($omnify_settings['email_smtp_encryption'] ?? 'tls'), 'tls'); ?>><?php esc_html_e('TLS (Recommended, Port 587)', 'omnifywp-ecommerce'); ?></option>
											</select>
										</div>
										<div class="omnify-modern-form-group">
											<label><?php esc_html_e('Use SMTP Authentication', 'omnifywp-ecommerce'); ?></label>
											<div style="margin-top: 8px;">
												<?php omnify_render_toggle('email_smtp_auth', ! isset($omnify_settings['email_smtp_auth']) || ! empty($omnify_settings['email_smtp_auth']) ? 1 : 0); ?>
											</div>
											<span class="help-text"><?php esc_html_e('Requires username and password to authenticate with the remote mail server.', 'omnifywp-ecommerce'); ?></span>
										</div>
									</div>

									<div class="omnify-modern-form-row">
										<div class="omnify-modern-form-group">
											<label for="email_smtp_username"><?php esc_html_e('SMTP Username', 'omnifywp-ecommerce'); ?></label>
											<input type="text" id="email_smtp_username" name="email_smtp_username" value="<?php echo esc_attr($omnify_settings['email_smtp_username'] ?? ''); ?>" placeholder="apikey or user@example.com" />
										</div>
										<div class="omnify-modern-form-group">
											<label for="email_smtp_password"><?php esc_html_e('SMTP Password', 'omnifywp-ecommerce'); ?></label>
											<input type="password" id="email_smtp_password" name="email_smtp_password" autocomplete="new-password" placeholder="<?php echo ! empty($omnify_settings['email_smtp_password']) ? esc_attr('••••••••') : ''; ?>" />
										</div>
									</div>
								</div>
							</div>

							<!-- Email Footer & Legal Branding Card -->
							<div class="omnify-email-card is-collapsed" data-email-category="routing" style="grid-column: span 2;">
								<div class="omnify-email-card-header">
									<div class="omnify-email-card-header-left">
										<div class="omnify-email-card-icon omnify-email-card-icon--smtp">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
										</div>
										<div class="omnify-email-card-meta">
											<h4 class="omnify-email-card-title"><?php esc_html_e('Email Footer & Legal Branding', 'omnifywp-ecommerce'); ?></h4>
											<p class="omnify-email-card-subtitle"><?php esc_html_e('Custom footer note, copyright text, and legal disclaimers appended to every outgoing email', 'omnifywp-ecommerce'); ?></p>
										</div>
									</div>
									<div class="omnify-email-card-header-right">
										<button type="button" class="omnify-email-card-collapse-btn" title="<?php esc_attr_e('Expand / Collapse', 'omnifywp-ecommerce'); ?>">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
										</button>
									</div>
								</div>
								<div class="omnify-email-card-body">
									<div class="omnify-modern-form-group">
										<label for="email_footer_text"><?php esc_html_e('Footer Text Content', 'omnifywp-ecommerce'); ?></label>
										<textarea id="email_footer_text" name="email_footer_text" rows="3" placeholder="<?php echo esc_attr(sprintf(__('Thank you for shopping with %s.', 'omnifywp-ecommerce'), get_bloginfo('name'))); ?>"><?php echo esc_textarea($omnify_settings['email_footer_text'] ?? ''); ?></textarea>
										<?php omnify_render_token_pills(['{site_name}', '{site_url}', '{year}']); ?>
									</div>
								</div>
							</div>

						</div>
						<?php omnify_render_form_footer(); ?>
					</form>

					<!-- Transactional Email Reliability / Delivery Log -->
					<div class="omnify-modern-card-body" id="omnify-email-logs-section" style="border-top: 1px solid #edf2f6; margin-top: 24px; padding-top: 24px;">
						<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
							<div>
								<h4 style="font-weight: 600; color: #0f172a; margin: 0; font-size: 16px; letter-spacing: -0.01em;"><?php esc_html_e('Transactional Email Reliability Log', 'omnifywp-ecommerce'); ?></h4>
								<p style="font-size: 13px; color: #64748b; margin-top: 4px; margin-bottom: 0;"><?php esc_html_e('Monitor every attempted transactional email, inspect message content, resend messages, and diagnose delivery errors.', 'omnifywp-ecommerce'); ?></p>
							</div>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omnify-test-email-bar">
								<?php wp_nonce_field('omnify_send_test_email', 'omnify_test_email_nonce'); ?>
								<input type="hidden" name="action" value="omnify_send_test_email" />
								<div class="omnify-test-email-input-wrap">
									<span class="omnify-test-email-icon">
										<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
									</span>
									<input type="email" name="test_email_to" value="<?php echo esc_attr(get_option('admin_email')); ?>" placeholder="admin@example.com" required />
								</div>
								<button type="submit" class="omnify-modern-btn omnify-modern-btn-primary" style="padding: 0 18px !important; height: 38px; border-radius: 8px; font-weight: 600;">
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
							<div class="omnify-table-wrapper" style="width: 100%;">
								<table class="omnify-table">
									<thead>
										<tr>
											<th style="width: 110px;"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Trigger', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Recipient', 'omnifywp-ecommerce'); ?></th>
											<th><?php esc_html_e('Subject / Failure', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 160px;"><?php esc_html_e('Date', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 160px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($omnify_email_logs as $omnify_log) : ?>
											<?php
											$omnify_status = sanitize_key((string) ($omnify_log['status'] ?? 'pending'));
											$omnify_badge_class = 'sent' === $omnify_status ? 'omnify-badge--success' : ('failed' === $omnify_status ? 'omnify-badge--danger' : 'omnify-badge--warning');
											$omnify_created = ! empty($omnify_log['created_at']) ? strtotime((string) $omnify_log['created_at']) : 0;
											$omnify_resend_url = wp_nonce_url(
												admin_url('admin-post.php?action=omnify_resend_email_log&log_id=' . absint($omnify_log['id'])),
												'omnify_resend_email_log_' . absint($omnify_log['id'])
											);
											?>
											<tr>
												<td>
													<span class="omnify-badge <?php echo esc_attr($omnify_badge_class); ?>">
														<span class="omnify-badge__dot"></span>
														<?php echo esc_html(ucfirst($omnify_status)); ?>
													</span>
												</td>
												<td>
													<strong style="color: #0f172a; font-size: 13.5px;"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $omnify_log['trigger_name']))); ?></strong>
													<?php if (! empty($omnify_log['order_id'])) : ?>
														<div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
															<?php echo esc_html(sprintf(__('Order #%d', 'omnifywp-ecommerce'), (int) $omnify_log['order_id'])); ?>
														</div>
													<?php endif; ?>
												</td>
												<td style="max-width: 180px; word-break: break-all; font-size: 13px; color: #475569;"><?php echo esc_html((string) $omnify_log['recipient']); ?></td>
												<td style="min-width: 200px;">
													<div style="font-weight: 600; color: #0f172a; font-size: 13px;"><?php echo esc_html((string) $omnify_log['subject']); ?></div>
													<?php if (! empty($omnify_log['error_message'])) : ?>
														<div style="margin-top: 6px; color: #ef4444; font-size: 11.5px; line-height: 1.4; background: #fef2f2; padding: 4px 8px; border-radius: 6px; border: 1px solid #fee2e2;">
															<?php echo esc_html((string) $omnify_log['error_message']); ?>
														</div>
													<?php endif; ?>
												</td>
												<td style="font-size: 12.5px; color: #64748b; white-space: nowrap;">
													<?php echo $omnify_created ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $omnify_created)) : esc_html__('Unknown', 'omnifywp-ecommerce'); ?>
												</td>
												<td style="text-align: right;">
													<div style="display: inline-flex; gap: 8px; align-items: center; justify-content: flex-end;">
														<a href="<?php echo esc_url($omnify_resend_url); ?>" class="omnify-button omnify-button--secondary omnify-button--sm">
															<span class="dashicons dashicons-controls-repeat" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
															<?php esc_html_e('Resend', 'omnifywp-ecommerce'); ?>
														</a>
														<details style="position: relative;">
															<summary class="omnify-button omnify-button--secondary omnify-button--sm" style="list-style: none; cursor: pointer;">
																<span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
																<?php esc_html_e('Preview', 'omnifywp-ecommerce'); ?>
															</summary>
															<div style="position: absolute; right: 0; top: 100%; margin-top: 6px; z-index: 1000; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); padding: 16px; width: 420px; max-height: 320px; overflow-y: auto; text-align: left;">
																<pre style="white-space: pre-wrap; font-family: monospace; font-size: 11.5px; margin: 0; line-height: 1.5; color: #1e293b;"><?php echo esc_html((string) $omnify_log['body']); ?></pre>
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

					<!-- Interactive Simulated Email Preview Modal -->
					<div id="omnify-email-preview-modal" class="omnify-email-modal-backdrop">
						<div class="omnify-email-modal-dialog">
							<div class="omnify-email-modal-header">
								<div>
									<span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #0f766e;"><?php esc_html_e('Simulated Dispatch Preview', 'omnifywp-ecommerce'); ?></span>
									<h3 id="omnify-modal-title" style="margin: 2px 0 0 0; font-size: 17px; font-weight: 700; color: #0f172a;"></h3>
								</div>
								<button type="button" id="omnify-email-modal-close-btn" class="omnify-email-modal-close" title="<?php esc_attr_e('Close Preview', 'omnifywp-ecommerce'); ?>">&times;</button>
							</div>
							<div class="omnify-email-preview-meta-bar">
								<div class="omnify-email-preview-meta-row">
									<span class="omnify-email-preview-meta-label"><?php esc_html_e('From:', 'omnifywp-ecommerce'); ?></span>
									<span id="omnify-modal-from" class="omnify-email-preview-meta-val"></span>
								</div>
								<div class="omnify-email-preview-meta-row">
									<span class="omnify-email-preview-meta-label"><?php esc_html_e('Subject:', 'omnifywp-ecommerce'); ?></span>
									<span id="omnify-modal-subject" class="omnify-email-preview-meta-val" style="font-weight: 600; color: #0f172a;"></span>
								</div>
							</div>
							<div class="omnify-email-preview-container">
								<div id="omnify-modal-content"></div>
							</div>
							<div class="omnify-email-modal-footer">
								<span style="font-size: 12px; color: #64748b;"><?php esc_html_e('Live preview with interpolated demo variables', 'omnifywp-ecommerce'); ?></span>
								<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm" onclick="document.getElementById('omnify-email-preview-modal').classList.remove('is-open');">
									<?php esc_html_e('Close', 'omnifywp-ecommerce'); ?>
								</button>
							</div>
						</div>
					</div>

				<?php elseif ($omnify_active_section === 'tools') : ?>
					<!-- ══ TOOLS & DATABASE MAINTENANCE (Item 35) ════════════════════════════════ -->
					<div class="omnify-modern-card-body">
						<?php omnify_render_section_title(__('System & Database Maintenance', 'omnifywp-ecommerce'), __('Optimize database tables, clean transient cache, purge expired abandoned carts, and re-sync product inventory states.', 'omnifywp-ecommerce')); ?>

						<?php if (isset($_GET['message']) && 'db_maintenance_completed' === $_GET['message']) : ?>
							<div class="notice notice-success is-dismissible" style="grid-column: span 2; margin: 0 0 16px;">
								<p><strong><?php esc_html_e('Database maintenance routine completed successfully.', 'omnifywp-ecommerce'); ?></strong></p>
							</div>
						<?php endif; ?>

						<div class="omnify-tools-grid" style="grid-column: span 2; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
							<!-- Transients & Cache Tool -->
							<div class="omnify-tool-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
								<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
									<span style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
										<span class="dashicons dashicons-trash" style="font-size: 18px; width: 18px; height: 18px; line-height: 18px;"></span>
									</span>
									<div>
										<h4 style="margin: 0; font-size: 15px; color: #0f172a;"><?php esc_html_e('Transients & Stale Sessions', 'omnifywp-ecommerce'); ?></h4>
										<span style="font-size: 12px; color: #64748b;"><?php esc_html_e('Clear expired cache and locks', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>
								<p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px; min-height: 40px;"><?php esc_html_e('Removes expired temporary checkout locks, rate limits, and transient data.', 'omnifywp-ecommerce'); ?></p>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<?php wp_nonce_field('omnify_run_db_maintenance_nonce'); ?>
									<input type="hidden" name="action" value="omnify_run_db_maintenance" />
									<input type="hidden" name="maintenance_type" value="transients" />
									<button type="submit" class="button button-secondary"><?php esc_html_e('Clear Transients', 'omnifywp-ecommerce'); ?></button>
								</form>
							</div>

							<!-- Abandoned Carts Tool -->
							<div class="omnify-tool-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
								<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
									<span style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
										<span class="dashicons dashicons-cart" style="font-size: 18px; width: 18px; height: 18px; line-height: 18px;"></span>
									</span>
									<div>
										<h4 style="margin: 0; font-size: 15px; color: #0f172a;"><?php esc_html_e('Stale Abandoned Carts', 'omnifywp-ecommerce'); ?></h4>
										<span style="font-size: 12px; color: #64748b;"><?php esc_html_e('Purge records > 60 days old', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>
								<p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px; min-height: 40px;"><?php esc_html_e('Deletes obsolete abandoned cart records and non-recovered guest sessions.', 'omnifywp-ecommerce'); ?></p>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<?php wp_nonce_field('omnify_run_db_maintenance_nonce'); ?>
									<input type="hidden" name="action" value="omnify_run_db_maintenance" />
									<input type="hidden" name="maintenance_type" value="carts" />
									<button type="submit" class="button button-secondary"><?php esc_html_e('Purge Stale Carts', 'omnifywp-ecommerce'); ?></button>
								</form>
							</div>

							<!-- Bundle Stock Synchronization Tool -->
							<div class="omnify-tool-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
								<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
									<span style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
										<span class="dashicons dashicons-update" style="font-size: 18px; width: 18px; height: 18px; line-height: 18px;"></span>
									</span>
									<div>
										<h4 style="margin: 0; font-size: 15px; color: #0f172a;"><?php esc_html_e('Re-sync Bundle Stocks', 'omnifywp-ecommerce'); ?></h4>
										<span style="font-size: 12px; color: #64748b;"><?php esc_html_e('Sync bundle status with children', 'omnifywp-ecommerce'); ?></span>
									</div>
								</div>
								<p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px; min-height: 40px;"><?php esc_html_e('Re-evaluates inventory of all components inside product bundles and sets stock statuses.', 'omnifywp-ecommerce'); ?></p>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<?php wp_nonce_field('omnify_run_db_maintenance_nonce'); ?>
									<input type="hidden" name="action" value="omnify_run_db_maintenance" />
									<input type="hidden" name="maintenance_type" value="bundles" />
									<button type="submit" class="button button-secondary"><?php esc_html_e('Re-sync Bundles', 'omnifywp-ecommerce'); ?></button>
								</form>
							</div>

							<!-- Execute All Maintenance Tasks -->
							<div class="omnify-tool-card" style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
								<div>
									<h4 style="margin: 0 0 6px; font-size: 15px; color: #0f172a;"><?php esc_html_e('Run Complete Optimization', 'omnifywp-ecommerce'); ?></h4>
									<p style="font-size: 13px; color: #64748b; margin: 0 0 16px; line-height: 1.5;"><?php esc_html_e('Executes all maintenance routines sequentially to keep your store fast and lean.', 'omnifywp-ecommerce'); ?></p>
								</div>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<?php wp_nonce_field('omnify_run_db_maintenance_nonce'); ?>
									<input type="hidden" name="action" value="omnify_run_db_maintenance" />
									<input type="hidden" name="maintenance_type" value="all" />
									<button type="submit" class="button button-primary" style="background: #0f766e; border-color: #0f766e;"><?php esc_html_e('Execute Complete Routine', 'omnifywp-ecommerce'); ?></button>
								</form>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		</div><!-- /.omnify-modern-content-card -->
	</div><!-- /.omnify-modern-settings-layout -->
</div><!-- /.omnify-admin-wrapper -->
