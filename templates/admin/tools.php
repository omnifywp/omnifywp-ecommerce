<?php
/**
 * Tools & Demo Data template.
 *
 * @package Omnify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Safely gather live database metrics
$omnify_total_products = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s", 'omnify_product', 'trash'));

$omnify_total_orders = 0;
$omnify_orders_table = $wpdb->prefix . 'omnify_orders';
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $omnify_orders_table)) === $omnify_orders_table) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$omnify_total_orders = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$omnify_orders_table}");
}

$omnify_total_customers = 0;
$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $omnify_customers_table)) === $omnify_customers_table) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$omnify_total_customers = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$omnify_customers_table}");
}

$omnify_total_reviews = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = %s AND comment_approved != %s", 'omnify_review', 'trash'));

$omnify_wc_products_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s", 'product', 'trash'));

// WooCommerce HPOS stores orders in {$wpdb->prefix}wc_orders when the feature is enabled.
$omnify_wc_orders_count = 0;
$omnify_wc_orders_table = $wpdb->prefix . 'wc_orders';
if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $omnify_wc_orders_table)) === $omnify_wc_orders_table) {
	$omnify_wc_orders_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$omnify_wc_orders_table} WHERE type = %s AND status != %s", 'shop_order', 'trash'));
}
if ($omnify_wc_orders_count === 0) {
	$omnify_wc_orders_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s", 'shop_order', 'trash'));
}

$omnify_wc_customers_count = (int) $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'billing_email' AND meta_value != ''");
if ($omnify_wc_customers_count === 0) {
	$omnify_wc_customers_count = count(get_users(['role' => 'customer', 'fields' => 'ID']));
}

$omnify_wc_coupons_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s", 'shop_coupon', 'publish'));

$omnify_wc_reviews_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = %s AND comment_approved = %s", 'review', '1'));

$omnify_total_wc_records = $omnify_wc_products_count + $omnify_wc_orders_count + $omnify_wc_customers_count + $omnify_wc_coupons_count + $omnify_wc_reviews_count;
?>
<div class="omnify-admin-wrapper omnify-tools-page">
	<?php $omnify_active_tab = 'tools'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('System Tools & Database Seeding', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Generate sample store records, execute maintenance cleanups, and inspect runtime health.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url('https://omnifywp.com/doc/'); ?>" target="_blank" class="omnify-button omnify-button--secondary">
				<span class="dashicons dashicons-book-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
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
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-api')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-network" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('API Keys', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-tools')); ?>" class="omnify-subtab is-active" style="display: inline-flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-tools" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('System Tools & Seeding', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<!-- Live Database Health & Metrics Overview -->
	<div class="omnify-tools-metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
		<div class="omnify-metric-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div style="width: 42px; height: 42px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<span class="dashicons dashicons-products" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
			</div>
			<div>
				<span style="font-size: 12px; font-weight: 500; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.04em;"><?php esc_html_e('Total Products', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.2;"><?php echo esc_html(number_format_i18n($omnify_total_products)); ?></strong>
			</div>
		</div>

		<div class="omnify-metric-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div style="width: 42px; height: 42px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<span class="dashicons dashicons-cart" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
			</div>
			<div>
				<span style="font-size: 12px; font-weight: 500; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.04em;"><?php esc_html_e('Total Orders', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.2;"><?php echo esc_html(number_format_i18n($omnify_total_orders)); ?></strong>
			</div>
		</div>

		<div class="omnify-metric-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div style="width: 42px; height: 42px; border-radius: 10px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<span class="dashicons dashicons-groups" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
			</div>
			<div>
				<span style="font-size: 12px; font-weight: 500; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.04em;"><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.2;"><?php echo esc_html(number_format_i18n($omnify_total_customers)); ?></strong>
			</div>
		</div>

		<div class="omnify-metric-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div style="width: 42px; height: 42px; border-radius: 10px; background: #fefce8; color: #ca8a04; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<span class="dashicons dashicons-star-filled" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
			</div>
			<div>
				<span style="font-size: 12px; font-weight: 500; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.04em;"><?php esc_html_e('Reviews', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.2;"><?php echo esc_html(number_format_i18n($omnify_total_reviews)); ?></strong>
			</div>
		</div>
	</div>

	<!-- Main 2-Column Tools Operations -->
	<div class="omnify-grid omnify-grid-2" style="gap: 24px; align-items: start;">
		<!-- Card 1: Generate Demo Data -->
		<div class="omnify-card omnify-tool-card" style="padding: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div class="omnify-card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px;">
				<div style="display: flex; align-items: center; gap: 12px;">
					<div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
						<span class="dashicons dashicons-database-add" style="font-size: 22px; width: 22px; height: 22px; line-height: 1;"></span>
					</div>
					<div>
						<h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?></h2>
						<span style="font-size: 12.5px; color: #64748b;"><?php esc_html_e('Quickly seed realistic shop data for testing.', 'omnifywp-ecommerce'); ?></span>
					</div>
				</div>
				<span class="omnify-badge omnify-badge--success"><?php esc_html_e('Ready', 'omnifywp-ecommerce'); ?></span>
			</div>

			<p style="color: #475569; font-size: 13.5px; line-height: 1.55; margin: 0 0 18px;">
				<?php esc_html_e('Seeding demo data generates a balanced sample catalog, complete customer accounts, and diverse orders. Ideal for previews, testing checkout gateways, and verifying storefront styling.', 'omnifywp-ecommerce'); ?>
			</p>

			<!-- Demo Data Content Items -->
			<div class="omnify-seeder-features" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; margin-bottom: 22px; display: grid; gap: 10px;">
				<div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155;">
					<span class="dashicons dashicons-yes-alt" style="color: #059669; font-size: 18px; width: 18px; height: 18px; line-height: 1; flex-shrink: 0;"></span>
					<span><strong>4 Products:</strong> Simple, Digital Download, Variable, and Product Bundle.</span>
				</div>
				<div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155;">
					<span class="dashicons dashicons-yes-alt" style="color: #059669; font-size: 18px; width: 18px; height: 18px; line-height: 1; flex-shrink: 0;"></span>
					<span><strong>3 Customer Accounts:</strong> Complete billing and shipping postal addresses.</span>
				</div>
				<div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155;">
					<span class="dashicons dashicons-yes-alt" style="color: #059669; font-size: 18px; width: 18px; height: 18px; line-height: 1; flex-shrink: 0;"></span>
					<span><strong>4 Orders:</strong> Completed, Processing, Pending Payment, and Refunded.</span>
				</div>
				<div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155;">
					<span class="dashicons dashicons-yes-alt" style="color: #059669; font-size: 18px; width: 18px; height: 18px; line-height: 1; flex-shrink: 0;"></span>
					<span><strong>3 Product Reviews:</strong> Verified customer feedback and 5-star ratings.</span>
				</div>
			</div>

			<div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
				<button type="button" id="omnify-btn-tools-generate" class="omnify-button omnify-button--primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; font-size: 13.5px; font-weight: 700; border-radius: 10px;">
					<span class="dashicons dashicons-database-add" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
					<?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?>
				</button>
			</div>

			<!-- AJAX Progress Indicator -->
			<div id="omnify-tools-progress-container" style="display:none; margin-top:22px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
				<div class="omnify-wizard-progress" style="height: 8px; margin: 8px 0; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
					<div id="omnify-tools-progress-bar" style="width:0%; height:100%; background: linear-gradient(90deg, #059669, #10b981); transition: width 0.3s ease;"></div>
				</div>
				<div id="omnify-tools-progress-status" style="font-size: 12.5px; color: #475569; text-align: center; font-weight: 600; margin-top: 6px;">
					<?php esc_html_e('Initializing data seeder...', 'omnifywp-ecommerce'); ?>
				</div>
			</div>
		</div>

		<!-- Card 2: Clean/Reset Data -->
		<div class="omnify-card omnify-tool-card" style="padding: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
			<div class="omnify-card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px;">
				<div style="display: flex; align-items: center; gap: 12px;">
					<div style="width: 40px; height: 40px; border-radius: 10px; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
						<span class="dashicons dashicons-trash" style="font-size: 22px; width: 22px; height: 22px; line-height: 1;"></span>
					</div>
					<div>
						<h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Clean Store Records', 'omnifywp-ecommerce'); ?></h2>
						<span style="font-size: 12.5px; color: #64748b;"><?php esc_html_e('Granular database maintenance and table resets.', 'omnifywp-ecommerce'); ?></span>
					</div>
				</div>
				<span class="omnify-badge omnify-badge--danger"><?php esc_html_e('Permanent', 'omnifywp-ecommerce'); ?></span>
			</div>

			<div style="background: #fef2f2; border: 1px solid #fee2e2; border-left: 4px solid #ef4444; padding: 14px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; color: #991b1b; line-height: 1.5; display: flex; align-items: flex-start; gap: 10px;">
				<span class="dashicons dashicons-warning" style="font-size: 18px; width: 18px; height: 18px; line-height: 1; color: #ef4444; margin-top: 1px; flex-shrink: 0;"></span>
				<div>
					<strong style="color: #7f1d1d;"><?php esc_html_e('Caution: Permanent Operation', 'omnifywp-ecommerce'); ?></strong><br />
					<?php esc_html_e('Deleting database records cannot be undone. We strongly advise taking a full WordPress database backup before running cleanups.', 'omnifywp-ecommerce'); ?>
				</div>
			</div>

			<div style="display: flex; flex-direction: column; gap: 10px;">
				<!-- Products Clean -->
				<div class="omnify-clean-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: border-color 0.2s;">
					<div style="display: flex; align-items: center; gap: 12px;">
						<div style="width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
							<span class="dashicons dashicons-products" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						</div>
						<div>
							<h4 style="margin: 0 0 2px; font-size: 13.5px; font-weight: 600; color: #0f172a;"><?php esc_html_e('Products Only', 'omnifywp-ecommerce'); ?></h4>
							<p style="margin: 0; font-size: 11.5px; color: #64748b;"><?php esc_html_e('Deletes products, variations, inventory logs, files, metadata.', 'omnifywp-ecommerce'); ?></p>
						</div>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="products" style="border-color: #fca5a5; color: #dc2626; flex-shrink: 0;">
						<?php esc_html_e('Clear Products', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Orders Clean -->
				<div class="omnify-clean-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: border-color 0.2s;">
					<div style="display: flex; align-items: center; gap: 12px;">
						<div style="width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
							<span class="dashicons dashicons-cart" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						</div>
						<div>
							<h4 style="margin: 0 0 2px; font-size: 13.5px; font-weight: 600; color: #0f172a;"><?php esc_html_e('Orders Only', 'omnifywp-ecommerce'); ?></h4>
							<p style="margin: 0; font-size: 11.5px; color: #64748b;"><?php esc_html_e('Deletes order items, activity logs, abandoned carts.', 'omnifywp-ecommerce'); ?></p>
						</div>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="orders" style="border-color: #fca5a5; color: #dc2626; flex-shrink: 0;">
						<?php esc_html_e('Clear Orders', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Customers Clean -->
				<div class="omnify-clean-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: border-color 0.2s;">
					<div style="display: flex; align-items: center; gap: 12px;">
						<div style="width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
							<span class="dashicons dashicons-groups" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						</div>
						<div>
							<h4 style="margin: 0 0 2px; font-size: 13.5px; font-weight: 600; color: #0f172a;"><?php esc_html_e('Customers Only', 'omnifywp-ecommerce'); ?></h4>
							<p style="margin: 0; font-size: 11.5px; color: #64748b;"><?php esc_html_e('Deletes customers, wishlists, and unlinks customer accounts.', 'omnifywp-ecommerce'); ?></p>
						</div>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="customers" style="border-color: #fca5a5; color: #dc2626; flex-shrink: 0;">
						<?php esc_html_e('Clear Customers', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Reviews Clean -->
				<div class="omnify-clean-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: border-color 0.2s;">
					<div style="display: flex; align-items: center; gap: 12px;">
						<div style="width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
							<span class="dashicons dashicons-star-filled" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						</div>
						<div>
							<h4 style="margin: 0 0 2px; font-size: 13.5px; font-weight: 600; color: #0f172a;"><?php esc_html_e('Reviews Only', 'omnifywp-ecommerce'); ?></h4>
							<p style="margin: 0; font-size: 11.5px; color: #64748b;"><?php esc_html_e('Deletes all review submissions and ratings.', 'omnifywp-ecommerce'); ?></p>
						</div>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="reviews" style="border-color: #fca5a5; color: #dc2626; flex-shrink: 0;">
						<?php esc_html_e('Clear Reviews', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Clean All / Danger Zone -->
				<div class="omnify-clean-danger-box" style="margin-top: 8px; padding: 16px; background: #fff5f5; border: 1px dashed #fca5a5; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
					<div>
						<div style="display: flex; align-items: center; gap: 6px;">
							<span class="dashicons dashicons-warning" style="color: #dc2626; font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
							<h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #dc2626;"><?php esc_html_e('Clean All Omnify Store Data', 'omnifywp-ecommerce'); ?></h4>
						</div>
						<p style="margin: 4px 0 0; font-size: 12px; color: #7f1d1d;"><?php esc_html_e('Truncates all database tables (products, orders, customers, reviews).', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--danger btn-clean-data" data-type="all" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; padding: 8px 18px;">
						<span class="dashicons dashicons-trash" style="font-size: 15px; width: 15px; height: 15px; line-height: 1;"></span>
						<?php esc_html_e('Clean All Data', 'omnifywp-ecommerce'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Card 3: 1-Click WooCommerce Migrator -->
	<div class="omnify-card omnify-tool-card" style="margin-top: 24px; padding: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
		<div class="omnify-card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px;">
			<div style="display: flex; align-items: center; gap: 12px;">
				<div style="width: 40px; height: 40px; border-radius: 10px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
					<span class="dashicons dashicons-migrate" style="font-size: 22px; width: 22px; height: 22px; line-height: 1;"></span>
				</div>
				<div>
					<h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('1-Click WooCommerce Migrator', 'omnifywp-ecommerce'); ?></h2>
					<span style="font-size: 12.5px; color: #64748b;"><?php esc_html_e('Seamlessly import your catalog, variations, categories, tags, images, and orders.', 'omnifywp-ecommerce'); ?></span>
				</div>
			</div>
			<?php if ($omnify_total_wc_records > 0) : ?>
				<span class="omnify-badge omnify-badge--info" style="background: #faf5ff; color: #7e22ce; border-color: #f3e8ff;">
					<?php printf(esc_html__('%d WC Records Detected', 'omnifywp-ecommerce'), $omnify_total_wc_records); ?>
				</span>
			<?php else : ?>
				<span class="omnify-badge"><?php esc_html_e('No WC Data Found', 'omnifywp-ecommerce'); ?></span>
			<?php endif; ?>
		</div>

		<p style="color: #475569; font-size: 13.5px; line-height: 1.55; margin: 0 0 18px;">
			<?php esc_html_e('Migrate your entire store from WooCommerce into OmnifyWP with a single click. Products, variations, download files, categories, tags, gallery photos, stock quantities, historical orders with itemized products, customers, coupons, and verified product reviews are cleanly imported without overwriting existing Omnify records.', 'omnifywp-ecommerce'); ?>
		</p>

		<div style="display: flex; gap: 16px 24px; margin-bottom: 20px; flex-wrap: wrap;">
			<label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer;">
				<input type="checkbox" id="omnify-mig-products" checked style="border-radius: 4px;" />
				<span><strong><?php esc_html_e('Products & Variations', 'omnifywp-ecommerce'); ?></strong> (<?php echo esc_html(number_format_i18n($omnify_wc_products_count)); ?>)</span>
			</label>
			<label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer;">
				<input type="checkbox" id="omnify-mig-orders" checked style="border-radius: 4px;" />
				<span><strong><?php esc_html_e('Historical Orders & Line Items', 'omnifywp-ecommerce'); ?></strong> (<?php echo esc_html(number_format_i18n($omnify_wc_orders_count)); ?>)</span>
			</label>
			<label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer;">
				<input type="checkbox" id="omnify-mig-customers" checked style="border-radius: 4px;" />
				<span><strong><?php esc_html_e('Customer Accounts & Addresses', 'omnifywp-ecommerce'); ?></strong> (<?php echo esc_html(number_format_i18n($omnify_wc_customers_count)); ?>)</span>
			</label>
			<label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer;">
				<input type="checkbox" id="omnify-mig-coupons" checked style="border-radius: 4px;" />
				<span><strong><?php esc_html_e('Coupons & Discounts', 'omnifywp-ecommerce'); ?></strong> (<?php echo esc_html(number_format_i18n($omnify_wc_coupons_count)); ?>)</span>
			</label>
			<label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer;">
				<input type="checkbox" id="omnify-mig-reviews" checked style="border-radius: 4px;" />
				<span><strong><?php esc_html_e('Product Reviews & Ratings', 'omnifywp-ecommerce'); ?></strong> (<?php echo esc_html(number_format_i18n($omnify_wc_reviews_count)); ?>)</span>
			</label>
		</div>

		<div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
			<button type="button" id="omnify-btn-migrate-woocommerce" class="omnify-button omnify-button--primary" style="background: #7e22ce; border-color: #7e22ce; display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; font-size: 13.5px; font-weight: 700; border-radius: 10px;">
				<span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				<?php esc_html_e('Start 1-Click Migration', 'omnifywp-ecommerce'); ?>
			</button>
		</div>

		<!-- Migration Progress -->
		<div id="omnify-mig-progress-container" style="display:none; margin-top:22px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
			<div class="omnify-wizard-progress" style="height: 8px; margin: 8px 0; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
				<div id="omnify-mig-progress-bar" style="width:0%; height:100%; background: linear-gradient(90deg, #9333ea, #a855f7); transition: width 0.3s ease;"></div>
			</div>
			<div id="omnify-mig-progress-status" style="font-size: 12.5px; color: #475569; text-align: center; font-weight: 600; margin-top: 6px;">
				<?php esc_html_e('Scanning WooCommerce records...', 'omnifywp-ecommerce'); ?>
			</div>
		</div>
	</div>

	<!-- System Environment & Specifications Panel -->
	<div class="omnify-card" style="margin-top: 24px; padding: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);">
		<div class="omnify-card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
			<div style="display: flex; align-items: center; gap: 12px;">
				<div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e2e8f0;">
					<span class="dashicons dashicons-admin-generic" style="font-size: 20px; width: 20px; height: 20px; line-height: 1;"></span>
				</div>
				<div>
					<h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Server Runtime & System Specifications', 'omnifywp-ecommerce'); ?></h2>
					<span style="font-size: 12.5px; color: #64748b;"><?php esc_html_e('Key environment parameters for diagnostics and troubleshooting.', 'omnifywp-ecommerce'); ?></span>
				</div>
			</div>
			<span class="omnify-badge omnify-badge--info">
				<?php echo esc_html(defined('OMNIFY_VERSION') ? 'Omnify v' . OMNIFY_VERSION : 'v1.4.1'); ?>
			</span>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('PHP Version', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a; font-family: monospace;"><?php echo esc_html(PHP_VERSION); ?></strong>
			</div>

			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('WordPress Version', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a;"><?php echo esc_html(get_bloginfo('version')); ?></strong>
			</div>

			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('MySQL Server', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a; font-family: monospace;"><?php echo esc_html($wpdb->db_version()); ?></strong>
			</div>

			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('PHP Memory Limit', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a; font-family: monospace;"><?php echo esc_html(ini_get('memory_limit')); ?></strong>
			</div>

			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('Max Execution Time', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a; font-family: monospace;"><?php echo esc_html(ini_get('max_execution_time') . 's'); ?></strong>
			</div>

			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
				<span style="font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px;"><?php esc_html_e('Table Prefix', 'omnifywp-ecommerce'); ?></span>
				<strong style="font-size: 15px; color: #0f172a; font-family: monospace;"><?php echo esc_html($wpdb->prefix . 'omnify_*'); ?></strong>
			</div>
		</div>
	</div>
</div>
