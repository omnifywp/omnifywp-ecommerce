<?php
/**
 * Tools & Demo Data template.
 *
 * @package Omnify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'tools'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Tools & Seeding', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Generate mock records or perform bulk cleanups on database tables.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

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
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-activity')); ?>" class="omnify-subtab" style="display: inline-flex; align-items: center; gap: 6px;">
			<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
			<?php esc_html_e('Activity Log', 'omnifywp-ecommerce'); ?>
		</a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-tools')); ?>" class="omnify-subtab is-active" style="display: inline-flex; align-items: center; gap: 6px;">
			<span class="dashicons dashicons-admin-tools" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
			<?php esc_html_e('System Tools & Seeding', 'omnifywp-ecommerce'); ?>
		</a>
	</div>

	<div class="omnify-grid omnify-grid-2" style="gap:24px; align-items:start;">
		<!-- Card 1: Generate Demo Data -->
		<div class="omnify-card" style="padding:24px;">
			<div class="omnify-card-header" style="display:flex; align-items:center; gap:12px; margin-bottom:16px; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:16px;">
				<span class="dashicons dashicons-database-add" style="font-size:24px; width:24px; height:24px; color:var(--omnify-primary);"></span>
				<h2 style="margin:0; font-size:18px; font-weight:600; color:var(--omnify-gray-800);"><?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?></h2>
			</div>
			<p style="color:var(--omnify-gray-600); font-size:13px; line-height:1.5; margin-bottom:20px;">
				<?php esc_html_e('Seeding demo data will create 4 diverse products (Simple, Digital/Download, Variable, Bundled), 3 mock customers with complete billing/shipping profiles, 4 mock orders across various payment states, and 3 product reviews. Great for testing layouts instantly!', 'omnifywp-ecommerce'); ?>
			</p>

			<div style="margin-top:20px;">
				<button type="button" id="omnify-btn-tools-generate" class="omnify-button omnify-button--primary" style="display: inline-flex; align-items: center; gap: 6px;">
					<span class="dashicons dashicons-admin-generic" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
					<?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?>
				</button>
			</div>

			<!-- AJAX Progress Indicator -->
			<div id="omnify-tools-progress-container" style="display:none; margin-top:20px;">
				<div class="omnify-wizard-progress" style="height:8px; margin:8px 0; background:#e2e8f0; border-radius:999px; overflow:hidden;">
					<div id="omnify-tools-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg, #6366f1, #4f46e5); transition:width 0.3s ease;"></div>
				</div>
				<div id="omnify-tools-progress-status" style="font-size:12px; color:var(--omnify-gray-600); text-align:center; font-weight:500;">
					<?php esc_html_e('Initializing data seeder...', 'omnifywp-ecommerce'); ?>
				</div>
			</div>
		</div>

		<!-- Card 2: Clean/Reset Data -->
		<div class="omnify-card" style="padding:24px;">
			<div class="omnify-card-header" style="display:flex; align-items:center; gap:12px; margin-bottom:16px; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:16px;">
				<span class="dashicons dashicons-trash" style="font-size:24px; width:24px; height:24px; color:var(--omnify-danger);"></span>
				<h2 style="margin:0; font-size:18px; font-weight:600; color:var(--omnify-gray-800);"><?php esc_html_e('Clean Store Records', 'omnifywp-ecommerce'); ?></h2>
			</div>

			<div style="background:rgba(239, 68, 68, 0.08); border-left:4px solid var(--omnify-danger); padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:13px; color:#b91c1c; line-height:1.4; display: flex; align-items: center; gap: 8px;">
				<span class="dashicons dashicons-warning" style="font-size: 18px; width: 18px; height: 18px; line-height: 1; color: var(--omnify-danger);"></span>
				<div>
					<strong><?php esc_html_e('Caution:', 'omnifywp-ecommerce'); ?></strong> 
					<?php esc_html_e('Deleting database records is permanent. We strongly recommend making a database backup before performing cleanups.', 'omnifywp-ecommerce'); ?>
				</div>
			</div>

			<div style="display:flex; flex-direction:column; gap:12px;">
				<!-- Products Clean -->
				<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:12px;">
					<div>
						<h4 style="margin:0 0 2px; font-size:13px; font-weight:600; color:var(--omnify-gray-700);"><?php esc_html_e('Remove Products Only', 'omnifywp-ecommerce'); ?></h4>
						<p style="margin:0; font-size:11px; color:var(--omnify-gray-500);"><?php esc_html_e('Deletes products, variations, inventory logs, files, metadata.', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="products" style="border-color:var(--omnify-danger); color:var(--omnify-danger);">
						<?php esc_html_e('Clear Products', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Orders Clean -->
				<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:12px;">
					<div>
						<h4 style="margin:0 0 2px; font-size:13px; font-weight:600; color:var(--omnify-gray-700);"><?php esc_html_e('Remove Orders Only', 'omnifywp-ecommerce'); ?></h4>
						<p style="margin:0; font-size:11px; color:var(--omnify-gray-500);"><?php esc_html_e('Deletes order items, activity logs, abandoned carts.', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="orders" style="border-color:var(--omnify-danger); color:var(--omnify-danger);">
						<?php esc_html_e('Clear Orders', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Customers Clean -->
				<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:12px;">
					<div>
						<h4 style="margin:0 0 2px; font-size:13px; font-weight:600; color:var(--omnify-gray-700);"><?php esc_html_e('Remove Customers Only', 'omnifywp-ecommerce'); ?></h4>
						<p style="margin:0; font-size:11px; color:var(--omnify-gray-500);"><?php esc_html_e('Deletes customers, wishlists, and unlinks customer order links.', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="customers" style="border-color:var(--omnify-danger); color:var(--omnify-danger);">
						<?php esc_html_e('Clear Customers', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Reviews Clean -->
				<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--omnify-gray-100); padding-bottom:12px;">
					<div>
						<h4 style="margin:0 0 2px; font-size:13px; font-weight:600; color:var(--omnify-gray-700);"><?php esc_html_e('Remove Reviews Only', 'omnifywp-ecommerce'); ?></h4>
						<p style="margin:0; font-size:11px; color:var(--omnify-gray-500);"><?php esc_html_e('Deletes all review submissions and ratings.', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm btn-clean-data" data-type="reviews" style="border-color:var(--omnify-danger); color:var(--omnify-danger);">
						<?php esc_html_e('Clear Reviews', 'omnifywp-ecommerce'); ?>
					</button>
				</div>

				<!-- Clean All -->
				<div style="display:flex; justify-content:space-between; align-items:center; padding-top:4px;">
					<div>
						<h4 style="margin:0 0 2px; font-size:13px; font-weight:700; color:var(--omnify-danger);"><?php esc_html_e('Clean All Omnify Data', 'omnifywp-ecommerce'); ?></h4>
						<p style="margin:0; font-size:11px; color:var(--omnify-gray-500);"><?php esc_html_e('Truncates ALL database tables (products, orders, customers, reviews).', 'omnifywp-ecommerce'); ?></p>
					</div>
					<button type="button" class="omnify-button omnify-button--sm btn-clean-data" data-type="all" style="background:var(--omnify-danger); border-color:var(--omnify-danger); color:#fff; display: inline-flex; align-items: center; gap: 6px;">
						<span class="dashicons dashicons-warning" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
						<?php esc_html_e('Clean All Data', 'omnifywp-ecommerce'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
