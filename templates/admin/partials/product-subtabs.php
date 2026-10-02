<?php
/**
 * Product-specific Subtabs Navigation
 */
if (! defined('ABSPATH')) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$omnify_current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
?>
<div class="omnify-subtabs" style="margin-bottom: 24px;">
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products')); ?>" class="omnify-subtab <?php echo 'omnify-products' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-archive" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Products Catalog', 'omnifywp-ecommerce'); ?>
	</a>
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-reviews')); ?>" class="omnify-subtab <?php echo 'omnify-reviews' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-admin-comments" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Customer Reviews', 'omnifywp-ecommerce'); ?>
	</a>
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-categories')); ?>" class="omnify-subtab <?php echo 'omnify-categories' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-category" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Categories', 'omnifywp-ecommerce'); ?>
	</a>
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-tags')); ?>" class="omnify-subtab <?php echo 'omnify-tags' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-tag" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Tags', 'omnifywp-ecommerce'); ?>
	</a>
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-brands')); ?>" class="omnify-subtab <?php echo 'omnify-brands' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-awards" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Brands', 'omnifywp-ecommerce'); ?>
	</a>
	<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-attributes')); ?>" class="omnify-subtab <?php echo 'omnify-attributes' === $omnify_current_page ? 'is-active' : ''; ?>">
		<span class="dashicons dashicons-admin-generic" style="font-size:17px; width:17px; height:17px; margin-right:5px; vertical-align:text-bottom;"></span>
		<?php esc_html_e('Attributes', 'omnifywp-ecommerce'); ?>
	</a>
</div>
