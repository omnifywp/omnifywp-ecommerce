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

	<div class="omnify-card">
		<div class="omnify-attribute-settings-grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 32px;">
			<!-- Left Side: Add Form -->
			<div>
				<h3 id="omnify-attribute-form-title" style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Add New Attribute', 'omnifywp-ecommerce'); ?></h3>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omnify-global-attribute-form">
					<?php wp_nonce_field('omnify_add_global_attribute', 'omnify_add_global_attr_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_attribute" />
					<input type="hidden" id="attribute_index" name="attribute_index" value="-1" />
					<input type="hidden" id="attribute_options" name="attribute_options" class="omnify-attribute-options-input" value="" />
					
					<div class="omnify-form-group" style="margin-bottom: 12px;">
						<label for="attribute_name"><?php esc_html_e('Attribute Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="attribute_name" name="attribute_name" placeholder="e.g. Size, Color" required style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px;" />
					</div>
					
					<div class="omnify-form-group" style="margin-bottom: 12px;">
						<label for="attribute_type"><?php esc_html_e('Variation Type', 'omnifywp-ecommerce'); ?></label>
						<select id="attribute_type" name="attribute_type" class="omnify-attribute-type-select" style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px; background: #fff; border: 1px solid var(--omnify-gray-300); border-radius: 6px;">
							<option value="button"><?php esc_html_e('Button / Pills', 'omnifywp-ecommerce'); ?></option>
							<option value="dropdown"><?php esc_html_e('Dropdown', 'omnifywp-ecommerce'); ?></option>
							<option value="color"><?php esc_html_e('Color Swatches', 'omnifywp-ecommerce'); ?></option>
							<option value="image"><?php esc_html_e('Image Swatches', 'omnifywp-ecommerce'); ?></option>
						</select>
						<span class="help-text"><?php esc_html_e('Controls how this attribute appears on product pages.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-attribute-builder">
						<div class="omnify-attribute-builder__header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
							<div>
								<h4 style="margin:0; font-size: 13px; font-weight:600;"><?php esc_html_e('Options Builder', 'omnifywp-ecommerce'); ?></h4>
							</div>
							<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm" id="omnify-add-attribute-option">
								+ <?php esc_html_e('Add Option', 'omnifywp-ecommerce'); ?>
							</button>
						</div>

						<div class="omnify-attribute-option-quickadd" style="display: flex; gap: 8px; margin-bottom: 12px;">
							<input type="text" id="omnify-attribute-option-label" placeholder="<?php esc_attr_e('e.g. Red, XL, Lifetime', 'omnifywp-ecommerce'); ?>" style="flex:1; height: 32px; padding: 4px 8px; font-size: 13px;" />
							<button type="button" class="omnify-button omnify-button--primary omnify-button--sm" id="omnify-add-attribute-option-from-input" style="height: 32px; padding: 0 12px;">
								<?php esc_html_e('Add', 'omnifywp-ecommerce'); ?>
							</button>
						</div>

						<div class="omnify-attribute-option-builder" id="omnify-attribute-option-builder" aria-live="polite"></div>

						<div class="omnify-attribute-builder__empty" id="omnify-attribute-builder-empty" style="text-align: center; padding: 24px; border: 1px dashed var(--omnify-gray-300); border-radius: 6px; background: var(--omnify-gray-50); margin-bottom: 16px;">
							<strong><?php esc_html_e('No options yet', 'omnifywp-ecommerce'); ?></strong>
							<span style="display: block; font-size: 12px; color: var(--omnify-gray-500); margin-top: 4px;"><?php esc_html_e('Add options like Red, Blue, Small, Medium, or upload image swatches.', 'omnifywp-ecommerce'); ?></span>
						</div>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 36px; justify-content: center; align-items: center;">
						<span id="omnify-attribute-submit-label"><?php esc_html_e('Add Attribute', 'omnifywp-ecommerce'); ?></span>
					</button>
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--full" id="omnify-cancel-attribute-edit" style="display:none; margin-top:8px; height: 34px; justify-content: center; align-items: center;">
						<?php esc_html_e('Cancel Edit', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
			<!-- Right Side: List -->
			<div>
				<h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Global Attributes List', 'omnifywp-ecommerce'); ?></h3>
				<?php if (empty($omnify_global_attrs)) : ?>
					<p style="font-size: 13px; color: var(--omnify-gray-600); font-style: italic; margin-top: 0;"><?php esc_html_e('No global attributes created yet.', 'omnifywp-ecommerce'); ?></p>
				<?php else : ?>
					<div class="omnify-table-wrapper">
						<table class="omnify-table">
							<thead>
								<tr>
									<th><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Type', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Options', 'omnifywp-ecommerce'); ?></th>
									<th style="width: 132px; text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_global_attrs as $omnify_idx => $omnify_attr) :
									$omnify_attr_type = sanitize_key($omnify_attr['type'] ?? 'button');
									$omnify_attr_options = is_array($omnify_attr['options'] ?? null) ? $omnify_attr['options'] : [];
									$omnify_attr_meta = is_array($omnify_attr['option_meta'] ?? null) ? $omnify_attr['option_meta'] : [];
								?>
									<tr>
										<td style="font-weight: 600;"><?php echo esc_html($omnify_attr['name']); ?></td>
										<td><span class="omnify-attribute-type-pill"><?php echo esc_html(ucfirst($omnify_attr_type)); ?></span></td>
										<td>
											<?php 
											foreach ($omnify_attr_options as $omnify_opt) {
												$omnify_meta = is_array($omnify_attr_meta[$omnify_opt] ?? null) ? $omnify_attr_meta[$omnify_opt] : [];
												echo '<span class="omnify-attribute-option-chip">';
												if ('color' === $omnify_attr_type && ! empty($omnify_meta['color'])) {
													echo '<span class="omnify-attribute-color-preview" style="background:' . esc_attr($omnify_meta['color']) . ';"></span>';
												}
												if ('image' === $omnify_attr_type && ! empty($omnify_meta['image_url'])) {
													echo '<img class="omnify-attribute-image-preview" src="' . esc_url($omnify_meta['image_url']) . '" alt="" />';
												}
												echo esc_html($omnify_opt) . '</span>';
											}
											?>
										</td>
										<td class="omnify-attribute-actions" style="text-align: right;">
											<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm omnify-edit-global-attribute" data-attribute="<?php echo esc_attr(wp_json_encode($omnify_attr)); ?>" data-index="<?php echo esc_attr($omnify_idx); ?>" style="padding: 4px 8px; font-size: 11px;">
												<?php esc_html_e('Edit', 'omnifywp-ecommerce'); ?>
											</button>
											<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_attribute&index=' . $omnify_idx), 'omnify_delete_global_attribute_' . $omnify_idx)); ?>" class="omnify-button omnify-button--danger omnify-button--sm js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete attribute "%s"?', 'omnifywp-ecommerce'), $omnify_attr['name'])); ?>" style="text-decoration: none; padding: 4px 8px; font-size: 11px;">
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
