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

	<div class="omnify-card">
		<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 32px;">
			<!-- Left Side: Add Form -->
			<div>
				<h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Add New Tag', 'omnifywp-ecommerce'); ?></h3>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_add_global_tag', 'omnify_add_global_tag_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_tag" />
					
					<div class="omnify-form-group" style="margin-bottom: 16px;">
						<label for="tag_name"><?php esc_html_e('Tag Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="tag_name" name="tag_name" placeholder="e.g. Hot, New, Promo" required style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px;" />
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 36px; justify-content: center; align-items: center;">
						<?php esc_html_e('Add Tag', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
			<!-- Right Side: List -->
			<div>
				<h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Global Tags List', 'omnifywp-ecommerce'); ?></h3>
				<?php if (empty($omnify_global_tags)) : ?>
					<p style="font-size: 13px; color: var(--omnify-gray-600); font-style: italic; margin-top: 0;"><?php esc_html_e('No global tags created yet.', 'omnifywp-ecommerce'); ?></p>
				<?php else : ?>
					<div class="omnify-table-wrapper">
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
										<td style="font-weight: 600;"><?php echo esc_html($omnify_t['name']); ?></td>
										<td><?php echo esc_html($omnify_t['slug']); ?></td>
										<td style="text-align: right;">
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_tag&index=' . $omnify_idx), 'omnify_delete_global_tag_' . $omnify_idx)); ?>" class="omnify-button omnify-button--danger omnify-button--sm js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete tag "%s"?', 'omnifywp-ecommerce'), $omnify_t['name'])); ?>" style="text-decoration: none; padding: 4px 8px; font-size: 11px;">
												<?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?>
											</a>
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
</div>
