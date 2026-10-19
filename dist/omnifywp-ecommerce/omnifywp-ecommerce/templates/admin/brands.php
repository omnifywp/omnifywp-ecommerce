<?php
/**
 * Global Brands Admin Template
 */
if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_global_brands = $omnify_template_vars['omnify_global_brands'] ?? [];
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];
?>
<div class="omnify-admin-wrapper">
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Brands', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Create and manage product brands to power storefront filters and catalog badges.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=new')); ?>" class="omnify-button omnify-button--primary">
				<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
				<?php esc_html_e('Create Product', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>
	<?php include __DIR__ . '/partials/product-subtabs.php'; ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$omnify_message = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
	if (! empty($omnify_message)) :
	?>
		<div class="notice notice-success is-dismissible" style="margin: 0 0 20px 0;">
			<p>
				<?php
				if ('global_brand_added' === $omnify_message) {
					esc_html_e('Brand added successfully.', 'omnifywp-ecommerce');
				} elseif ('global_brand_deleted' === $omnify_message) {
					esc_html_e('Brand deleted successfully.', 'omnifywp-ecommerce');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="omnify-taxonomy-grid">
		<!-- Left Card: Add Brand Form -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--form">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-awards"></span>
				</div>
				<div>
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Add New Brand', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Register a manufacturer or brand line', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-tax-card__body">
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_add_global_brand', 'omnify_add_global_brand_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_brand" />
					
					<div class="omnify-form-group" style="margin-bottom: 22px;">
						<label for="brand_name" class="omnify-field-label"><?php esc_html_e('Brand Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="brand_name" name="brand_name" class="omnify-input" placeholder="e.g. Apple, Nike, Sony, Adidas" required />
						<span class="omnify-field-hint"><?php esc_html_e('Brands power storefront filter facet panels and product tags.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 40px; justify-content: center; font-size: 13.5px; font-weight: 600;">
						<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
						<?php esc_html_e('Add Brand', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- Right Card: Global Brands List -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--list">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-list-view"></span>
				</div>
				<div style="flex: 1;">
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Global Brands', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('All defined brands across your catalog', 'omnifywp-ecommerce'); ?></p>
				</div>
				<span class="omnify-tax-count-badge">
					<?php printf(esc_html(_n('%d Brand', '%d Brands', count($omnify_global_brands), 'omnifywp-ecommerce')), count($omnify_global_brands)); ?>
				</span>
			</div>
			<?php if (empty($omnify_global_brands)) : ?>
				<div class="omnify-tax-empty-state">
					<div class="omnify-tax-empty-icon">
						<span class="dashicons dashicons-awards"></span>
					</div>
					<h4><?php esc_html_e('No brands yet', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('Use the form on the left to add your first product brand.', 'omnifywp-ecommerce'); ?></p>
				</div>
			<?php else : ?>
				<div class="omnify-table-wrapper" style="margin: 0; border: 0; border-radius: 0; box-shadow: none;">
					<table class="omnify-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Slug', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 80px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_global_brands as $omnify_idx => $omnify_b) :
								$omnify_initial = strtoupper(substr((string) $omnify_b['name'], 0, 1));
							?>
								<tr>
									<td>
										<div class="omnify-tax-name-cell">
											<span class="omnify-tax-avatar-wrap">
												<?php echo esc_html($omnify_initial); ?>
											</span>
											<span class="omnify-tax-name-text"><?php echo esc_html($omnify_b['name']); ?></span>
										</div>
									</td>
									<td>
										<code class="omnify-slug-pill">/<?php echo esc_html($omnify_b['slug']); ?></code>
									</td>
									<td style="text-align: right;">
										<div class="omnify-table-actions" style="justify-content: flex-end;">
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_brand&index=' . $omnify_idx), 'omnify_delete_global_brand_' . $omnify_idx)); ?>" class="omnify-btn-icon omnify-btn-icon--danger js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete brand "%s"?', 'omnifywp-ecommerce'), $omnify_b['name'])); ?>" title="<?php esc_attr_e('Delete Brand', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-trash"></span>
											</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
