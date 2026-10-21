<?php
/**
 * Global Tags Admin Template
 */
if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_global_tags = $omnify_template_vars['omnify_global_tags'] ?? [];
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];
?>
<div class="omnify-admin-wrapper">
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Tags', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Assign reusable tags to products for metadata filtering.', 'omnifywp-ecommerce'); ?></p>
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
				if ('global_tag_added' === $omnify_message) {
					esc_html_e('Tag added successfully.', 'omnifywp-ecommerce');
				} elseif ('global_tag_deleted' === $omnify_message) {
					esc_html_e('Tag deleted successfully.', 'omnifywp-ecommerce');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="omnify-taxonomy-grid">
		<!-- Left Card: Add Tag Form -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--form">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-tag"></span>
				</div>
				<div>
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Add New Tag', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Create a product label for search & filtering', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-tax-card__body">
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_add_global_tag', 'omnify_add_global_tag_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_tag" />
					
					<div class="omnify-form-group" style="margin-bottom: 22px;">
						<label for="tag_name" class="omnify-field-label"><?php esc_html_e('Tag Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="tag_name" name="tag_name" class="omnify-input" placeholder="e.g. Hot, New, Promo, Summer" required />
						<span class="omnify-field-hint"><?php esc_html_e('Tags help customers filter and discover items quickly.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 40px; justify-content: center; font-size: 13.5px; font-weight: 600;">
						<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
						<?php esc_html_e('Add Tag', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- Right Card: Global Tags List -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--list">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-list-view"></span>
				</div>
				<div style="flex: 1;">
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Global Tags', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('All defined tags across your catalog', 'omnifywp-ecommerce'); ?></p>
				</div>
				<span class="omnify-tax-count-badge">
					<?php printf(esc_html(_n('%d Tag', '%d Tags', count($omnify_global_tags), 'omnifywp-ecommerce')), count($omnify_global_tags)); ?>
				</span>
			</div>
			<?php if (empty($omnify_global_tags)) : ?>
				<div class="omnify-tax-empty-state">
					<div class="omnify-tax-empty-icon">
						<span class="dashicons dashicons-tag"></span>
					</div>
					<h4><?php esc_html_e('No tags yet', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('Use the form on the left to add your first product tag.', 'omnifywp-ecommerce'); ?></p>
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
							<?php foreach ($omnify_global_tags as $omnify_idx => $omnify_t) : ?>
								<tr>
									<td>
										<div class="omnify-tax-name-cell">
											<span class="omnify-tax-icon-wrap omnify-tax-icon-wrap--tag">
												<span class="dashicons dashicons-tag"></span>
											</span>
											<span class="omnify-tax-name-text"><?php echo esc_html($omnify_t['name']); ?></span>
										</div>
									</td>
									<td>
										<code class="omnify-slug-pill">/<?php echo esc_html($omnify_t['slug']); ?></code>
									</td>
									<td style="text-align: right;">
										<div class="omnify-table-actions" style="justify-content: flex-end;">
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_tag&index=' . $omnify_idx), 'omnify_delete_global_tag_' . $omnify_idx)); ?>" class="omnify-btn-icon omnify-btn-icon--danger js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete tag "%s"?', 'omnifywp-ecommerce'), $omnify_t['name'])); ?>" title="<?php esc_attr_e('Delete Tag', 'omnifywp-ecommerce'); ?>">
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
