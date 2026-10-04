<?php
/**
 * Shared Admin Navigation Tabs (seamless across all pages)
 *
 * Set $active_tab before including, e.g. $active_tab = 'dashboard';
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_admin_nav_tabs = $omnify_template_vars['omnify_admin_nav_tabs'] ?? null;
$omnify_classes = $omnify_template_vars['omnify_classes'] ?? null;
$omnify_is_active = $omnify_template_vars['omnify_is_active'] ?? null;
$omnify_key = $omnify_template_vars['omnify_key'] ?? null;
$omnify_tab = $omnify_template_vars['omnify_tab'] ?? null;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$omnify_current_page_nav = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

if (in_array($omnify_current_page_nav, ['omnify-abandoned-carts', 'omnify-orders'], true)) {
	$omnify_active_tab = 'orders';
} elseif (in_array($omnify_current_page_nav, ['omnify-reviews', 'omnify-products', 'omnify-categories', 'omnify-tags', 'omnify-brands', 'omnify-attributes'], true)) {
	$omnify_active_tab = 'products';
} elseif (in_array($omnify_current_page_nav, ['omnify-export', 'omnify-api', 'omnify-activity', 'omnify-tools'], true)) {
	$omnify_active_tab = 'tools';
} else {
	$omnify_active_tab = $omnify_active_tab ?? 'dashboard';
}

$omnify_admin_nav_tabs = [
	'dashboard' => ['url' => admin_url('admin.php?page=omnifywp-ecommerce'), 'icon_class' => 'dashicons-chart-bar', 'label' => __('Dashboard', 'omnifywp-ecommerce')],
	'orders'    => ['url' => admin_url('admin.php?page=omnify-orders'), 'icon_class' => 'dashicons-cart', 'label' => __('Orders', 'omnifywp-ecommerce')],
	'products'  => ['url' => admin_url('admin.php?page=omnify-products'), 'icon_class' => 'dashicons-archive', 'label' => __('Products', 'omnifywp-ecommerce')],
	'customers' => ['url' => admin_url('admin.php?page=omnify-customers'), 'icon_class' => 'dashicons-groups', 'label' => __('Customers', 'omnifywp-ecommerce')],
	'coupons'   => ['url' => admin_url('admin.php?page=omnify-coupons'), 'icon_class' => 'dashicons-tag', 'label' => __('Coupons', 'omnifywp-ecommerce')],
	'analytics' => ['url' => admin_url('admin.php?page=omnify-analytics'), 'icon_class' => 'dashicons-chart-line', 'label' => __('Analytics', 'omnifywp-ecommerce')],
	'settings'  => ['url' => admin_url('admin.php?page=omnify-settings'), 'icon_class' => 'dashicons-admin-settings', 'label' => __('Settings', 'omnifywp-ecommerce')],
	'tools'     => ['url' => admin_url('admin.php?page=omnify-tools'), 'icon_class' => 'dashicons-admin-tools', 'label' => __('Tools', 'omnifywp-ecommerce')],
];
?>

<div class="omnify-tabs-wrapper">
	<div class="omnify-nav-brand" style="display: inline-flex; align-items: center; padding: 4px 14px 4px 4px; margin-right: 6px; border-right: 1px solid var(--omnify-gray-200, #e2e8f0);">
		<img src="<?php echo esc_url(OMNIFY_URL . 'assets/icon-256x256.png'); ?>" alt="Omnify" style="width: 24px; height: 24px; border-radius: 6px; margin-right: 8px; box-shadow: 0 2px 6px rgba(11, 81, 53, 0.15);" />
		<span style="font-weight: 800; font-size: 13.5px; letter-spacing: -0.02em; color: #0B5135;">Omnify<span style="color: #22A06B;">WP</span></span>
	</div>
	<?php foreach ($omnify_admin_nav_tabs as $omnify_key => $omnify_tab): 
		$omnify_is_active = ($omnify_active_tab === $omnify_key);
		$omnify_classes = 'omnify-tab' . ($omnify_is_active ? ' is-active' : '');
	?>
		<a href="<?php echo esc_url($omnify_tab['url']); ?>" class="<?php echo esc_attr($omnify_classes); ?>">
			<span class="dashicons <?php echo esc_attr($omnify_tab['icon_class']); ?>" style="font-size: 18px; width: 18px; height: 18px; margin-right: 6px; display: inline-flex; align-items: center; justify-content: center; vertical-align: text-bottom;"></span>
			<?php echo esc_html($omnify_tab['label']); ?>
		</a>
	<?php endforeach; ?>
</div>
