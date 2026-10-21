<?php
/**
 * Global Attributes Admin Template
 */
if (! defined('ABSPATH')) {
	exit;
}

// Guarantee WordPress media uploader scripts are loaded
wp_enqueue_media();

$omnify_template_vars = get_defined_vars();
$omnify_global_attrs = $omnify_template_vars['omnify_global_attributes'] ?? [];
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];
?>
<div class="omnify-admin-wrapper">
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Attributes (Global Variations)', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Define global variation attributes (e.g. Size, Color) to map different options and pricing for variable products.', 'omnifywp-ecommerce'); ?></p>
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
				if ('global_attr_added' === $omnify_message) {
					esc_html_e('Attribute added/saved successfully.', 'omnifywp-ecommerce');
				} elseif ('global_attr_deleted' === $omnify_message) {
					esc_html_e('Attribute deleted successfully.', 'omnifywp-ecommerce');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="omnify-taxonomy-grid">
		<!-- Left Card: Add Attribute Form -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--form">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-admin-generic"></span>
				</div>
				<div>
					<h3 id="omnify-attribute-form-title" class="omnify-tax-card__title"><?php esc_html_e('Add New Attribute', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Global variation options for variable products', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-tax-card__body">
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omnify-global-attribute-form">
					<?php wp_nonce_field('omnify_add_global_attribute', 'omnify_add_global_attr_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_attribute" />
					<input type="hidden" id="attribute_index" name="attribute_index" value="-1" />
					<input type="hidden" id="attribute_options" name="attribute_options" class="omnify-attribute-options-input" value="" />
					
					<div class="omnify-form-group" style="margin-bottom: 16px;">
						<label for="attribute_name" class="omnify-field-label"><?php esc_html_e('Attribute Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="attribute_name" name="attribute_name" class="omnify-input" placeholder="e.g. Size, Color, Storage" required />
						<span class="omnify-field-hint"><?php esc_html_e('Name of the attribute shown in options filters.', 'omnifywp-ecommerce'); ?></span>
					</div>
					
					<div class="omnify-form-group" style="margin-bottom: 18px;">
						<label for="attribute_type" class="omnify-field-label"><?php esc_html_e('Variation Type', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-select-wrapper">
							<select id="attribute_type" name="attribute_type" class="omnify-select omnify-attribute-type-select">
								<option value="button"><?php esc_html_e('Button / Pills', 'omnifywp-ecommerce'); ?></option>
								<option value="dropdown"><?php esc_html_e('Dropdown', 'omnifywp-ecommerce'); ?></option>
								<option value="color"><?php esc_html_e('Color Swatches', 'omnifywp-ecommerce'); ?></option>
								<option value="image"><?php esc_html_e('Image Swatches', 'omnifywp-ecommerce'); ?></option>
							</select>
						</div>
						<span class="omnify-field-hint"><?php esc_html_e('Controls how this variation appears on the storefront.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-attribute-builder" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
						<div class="omnify-attribute-builder__header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
							<div>
								<h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Options Builder', 'omnifywp-ecommerce'); ?></h4>
							</div>
							<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm" id="omnify-add-attribute-option" style="height: 30px; font-size: 12px; font-weight: 600;">
								<span class="dashicons dashicons-plus" style="font-size: 12px; width: 12px; height: 12px; line-height: 1; margin-right: 3px;"></span>
								<?php esc_html_e('Add Option', 'omnifywp-ecommerce'); ?>
							</button>
						</div>

						<div class="omnify-attribute-option-quickadd" style="display: flex; gap: 8px; margin-bottom: 12px;">
							<input type="text" id="omnify-attribute-option-label" class="omnify-input" placeholder="<?php esc_attr_e('e.g. Red, XL, 256GB', 'omnifywp-ecommerce'); ?>" style="flex: 1; height: 34px; font-size: 13px;" />
							<button type="button" class="omnify-button omnify-button--primary omnify-button--sm" id="omnify-add-attribute-option-from-input" style="height: 34px; padding: 0 14px; font-size: 12.5px; font-weight: 600;">
								<?php esc_html_e('Add', 'omnifywp-ecommerce'); ?>
							</button>
						</div>

						<div class="omnify-attribute-option-builder" id="omnify-attribute-option-builder" aria-live="polite"></div>

						<div class="omnify-attribute-builder__empty" id="omnify-attribute-builder-empty" style="text-align: center; padding: 20px 16px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #ffffff;">
							<strong style="color: #475569; font-size: 13px;"><?php esc_html_e('No options added yet', 'omnifywp-ecommerce'); ?></strong>
							<span style="display: block; font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php esc_html_e('Add option values like Red, Blue, Small, or Swatches.', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 40px; justify-content: center; font-size: 13.5px; font-weight: 600;">
						<span id="omnify-attribute-submit-label"><?php esc_html_e('Add Attribute', 'omnifywp-ecommerce'); ?></span>
					</button>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--full" id="omnify-cancel-attribute-edit" style="display: none; margin-top: 8px; height: 36px; justify-content: center; font-size: 13px;">
						<?php esc_html_e('Cancel Edit', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- Right Card: Global Attributes List -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--list">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-list-view"></span>
				</div>
				<div style="flex: 1;">
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Global Attributes', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Available variation presets in your store', 'omnifywp-ecommerce'); ?></p>
				</div>
				<span class="omnify-tax-count-badge">
					<?php printf(esc_html(_n('%d Attribute', '%d Attributes', count($omnify_global_attrs), 'omnifywp-ecommerce')), count($omnify_global_attrs)); ?>
				</span>
			</div>
			<?php if (empty($omnify_global_attrs)) : ?>
				<div class="omnify-tax-empty-state">
					<div class="omnify-tax-empty-icon">
						<span class="dashicons dashicons-admin-generic"></span>
					</div>
					<h4><?php esc_html_e('No attributes yet', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('Use the form on the left to add your first global variation attribute.', 'omnifywp-ecommerce'); ?></p>
				</div>
			<?php else : ?>
				<div class="omnify-table-wrapper" style="margin: 0; border: 0; border-radius: 0; box-shadow: none;">
					<table class="omnify-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Options', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 100px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_global_attrs as $omnify_idx => $omnify_attr) :
								$omnify_attr_type = sanitize_key($omnify_attr['type'] ?? 'button');
								$omnify_attr_options = is_array($omnify_attr['options'] ?? null) ? $omnify_attr['options'] : [];
								$omnify_attr_meta = is_array($omnify_attr['option_meta'] ?? null) ? $omnify_attr['option_meta'] : [];
							?>
								<tr>
									<td>
										<div class="omnify-tax-name-cell">
											<span class="omnify-tax-icon-wrap omnify-tax-icon-wrap--attr">
												<span class="dashicons dashicons-admin-generic"></span>
											</span>
											<span class="omnify-tax-name-text"><?php echo esc_html($omnify_attr['name']); ?></span>
										</div>
									</td>
									<td>
										<span class="omnify-attr-type-badge omnify-attr-type-badge--<?php echo esc_attr($omnify_attr_type); ?>">
											<?php echo esc_html(ucfirst($omnify_attr_type)); ?>
										</span>
									</td>
									<td>
										<div class="omnify-attr-chips-wrap">
											<?php 
											foreach ($omnify_attr_options as $omnify_opt) {
												$omnify_meta = is_array($omnify_attr_meta[$omnify_opt] ?? null) ? $omnify_attr_meta[$omnify_opt] : [];
												echo '<span class="omnify-attr-chip">';
												if ('color' === $omnify_attr_type && ! empty($omnify_meta['color'])) {
													echo '<span class="omnify-attr-color-dot" style="background:' . esc_attr($omnify_meta['color']) . ';"></span>';
												}
												if ('image' === $omnify_attr_type && ! empty($omnify_meta['image_url'])) {
													echo '<img class="omnify-attr-image-dot" src="' . esc_url($omnify_meta['image_url']) . '" alt="" />';
												}
												echo '<span>' . esc_html($omnify_opt) . '</span></span>';
											}
											?>
										</div>
									</td>
									<td style="text-align: right;">
										<div class="omnify-table-actions" style="justify-content: flex-end;">
											<button type="button" class="omnify-btn-icon omnify-edit-global-attribute" data-attribute="<?php echo esc_attr(wp_json_encode($omnify_attr)); ?>" data-index="<?php echo esc_attr($omnify_idx); ?>" title="<?php esc_attr_e('Edit Attribute', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-edit"></span>
											</button>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_attribute&index=' . $omnify_idx), 'omnify_delete_global_attribute_' . $omnify_idx)); ?>" class="omnify-btn-icon omnify-btn-icon--danger js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete attribute "%s"?', 'omnifywp-ecommerce'), $omnify_attr['name'])); ?>" title="<?php esc_attr_e('Delete Attribute', 'omnifywp-ecommerce'); ?>">
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
