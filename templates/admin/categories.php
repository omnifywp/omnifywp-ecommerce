<?php
/**
 * Global Categories Admin Template
 */
if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_global_categories = $omnify_template_vars['omnify_global_categories'] ?? [];
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];

// Helper functions (defined locally or globally)
if (! function_exists('omnify_build_category_tree')) {
	function omnify_build_category_tree(array $omnify_categories, string $omnify_parent_slug = ''): array {
		$omnify_branch = [];
		foreach ($omnify_categories as $omnify_cat) {
			$omnify_cat_parent = $omnify_cat['parent'] ?? '';
			if ($omnify_cat_parent === $omnify_parent_slug) {
				$omnify_children = omnify_build_category_tree($omnify_categories, $omnify_cat['slug']);
				if (! empty($omnify_children)) {
					$omnify_cat['children'] = $omnify_children;
				}
				$omnify_branch[] = $omnify_cat;
			}
		}
		return $omnify_branch;
	}
}

if (! function_exists('omnify_get_category_tree')) {
	function omnify_get_category_tree(array $omnify_categories): array {
		$omnify_slugs = array_column($omnify_categories, 'slug');
		foreach ($omnify_categories as $omnify_idx => &$omnify_cat) {
			$omnify_cat['global_idx'] = $omnify_idx;
			if (! empty($omnify_cat['parent']) && ! in_array($omnify_cat['parent'], $omnify_slugs, true)) {
				$omnify_cat['parent'] = '';
			}
		}
		unset($omnify_cat);
		return omnify_build_category_tree($omnify_categories, '');
	}
}

if (! function_exists('omnify_render_category_table_rows')) {
	function omnify_render_category_table_rows(array $omnify_tree, int $omnify_depth = 0) {
		foreach ($omnify_tree as $omnify_node) {
			$omnify_global_idx = $omnify_node['global_idx'];
			?>
			<tr>
				<td>
					<div class="omnify-tax-name-cell" style="<?php echo $omnify_depth > 0 ? 'padding-left: ' . (int) ($omnify_depth * 22) . 'px;' : ''; ?>">
						<?php if ($omnify_depth > 0) : ?>
							<span class="omnify-tree-branch">└─</span>
							<span class="omnify-tax-icon-wrap omnify-tax-icon-wrap--subcat">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</span>
						<?php else : ?>
							<span class="omnify-tax-icon-wrap omnify-tax-icon-wrap--cat">
								<span class="dashicons dashicons-category"></span>
							</span>
						<?php endif; ?>
						<span class="omnify-tax-name-text"><?php echo esc_html($omnify_node['name']); ?></span>
					</div>
				</td>
				<td>
					<code class="omnify-slug-pill">/<?php echo esc_html($omnify_node['slug']); ?></code>
				</td>
				<td style="text-align: right;">
					<div class="omnify-table-actions" style="justify-content: flex-end;">
						<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_category&index=' . $omnify_global_idx), 'omnify_delete_global_category_' . $omnify_global_idx)); ?>" class="omnify-btn-icon omnify-btn-icon--danger js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete category "%s"? Any subcategories will be unparented.', 'omnifywp-ecommerce'), $omnify_node['name'])); ?>" title="<?php esc_attr_e('Delete Category', 'omnifywp-ecommerce'); ?>">
							<span class="dashicons dashicons-trash"></span>
						</a>
					</div>
				</td>
			</tr>
			<?php
			if (! empty($omnify_node['children'])) {
				omnify_render_category_table_rows($omnify_node['children'], $omnify_depth + 1);
			}
		}
	}
}

$omnify_category_tree = omnify_get_category_tree($omnify_global_categories);
?>
<div class="omnify-admin-wrapper">
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Categories', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Organize your catalog products into customizable categories.', 'omnifywp-ecommerce'); ?></p>
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
				if ('global_cat_added' === $omnify_message) {
					esc_html_e('Category added successfully.', 'omnifywp-ecommerce');
				} elseif ('global_cat_deleted' === $omnify_message) {
					esc_html_e('Category deleted successfully.', 'omnifywp-ecommerce');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="omnify-taxonomy-grid">
		<!-- Left Card: Add Category Form -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--form">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-category"></span>
				</div>
				<div>
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Add New Category', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Create a top-level or sub-category', 'omnifywp-ecommerce'); ?></p>
				</div>
			</div>
			<div class="omnify-tax-card__body">
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_add_global_category', 'omnify_add_global_cat_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_category" />
					
					<div class="omnify-form-group" style="margin-bottom: 18px;">
						<label for="category_name" class="omnify-field-label"><?php esc_html_e('Category Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="category_name" name="category_name" class="omnify-input" placeholder="e.g. Software, Design, Audio" required />
						<span class="omnify-field-hint"><?php esc_html_e('The name is how it appears on your site.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-form-group" style="margin-bottom: 22px;">
						<label for="category_parent" class="omnify-field-label"><?php esc_html_e('Parent Category', 'omnifywp-ecommerce'); ?></label>
						<div class="omnify-select-wrapper">
							<select id="category_parent" name="category_parent" class="omnify-select">
								<option value=""><?php esc_html_e('-- None (Top-level) --', 'omnifywp-ecommerce'); ?></option>
								<?php foreach ($omnify_global_categories as $omnify_c) : ?>
									<option value="<?php echo esc_attr($omnify_c['slug']); ?>"><?php echo esc_html($omnify_c['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<span class="omnify-field-hint"><?php esc_html_e('Assign a parent to build hierarchical categories.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 40px; justify-content: center; font-size: 13.5px; font-weight: 600;">
						<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; margin-right: 4px;"></span>
						<?php esc_html_e('Add Category', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- Right Card: Global Categories List -->
		<div class="omnify-card omnify-tax-card omnify-tax-card--list">
			<div class="omnify-tax-card__header">
				<div class="omnify-tax-card__header-icon">
					<span class="dashicons dashicons-list-view"></span>
				</div>
				<div style="flex: 1;">
					<h3 class="omnify-tax-card__title"><?php esc_html_e('Global Categories', 'omnifywp-ecommerce'); ?></h3>
					<p class="omnify-tax-card__subtitle"><?php esc_html_e('Manage your product catalog taxonomy', 'omnifywp-ecommerce'); ?></p>
				</div>
				<span class="omnify-tax-count-badge">
					<?php printf(esc_html(_n('%d Category', '%d Categories', count($omnify_global_categories), 'omnifywp-ecommerce')), count($omnify_global_categories)); ?>
				</span>
			</div>
			<?php if (empty($omnify_global_categories)) : ?>
				<div class="omnify-tax-empty-state">
					<div class="omnify-tax-empty-icon">
						<span class="dashicons dashicons-category"></span>
					</div>
					<h4><?php esc_html_e('No categories yet', 'omnifywp-ecommerce'); ?></h4>
					<p><?php esc_html_e('Use the form on the left to add your first product category.', 'omnifywp-ecommerce'); ?></p>
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
							<?php omnify_render_category_table_rows($omnify_category_tree); ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
