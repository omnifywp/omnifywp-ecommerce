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
			$omnify_indent = str_repeat('- ', $omnify_depth);
			?>
			<tr>
				<td style="font-weight: 600;"><?php echo esc_html($omnify_indent . $omnify_node['name']); ?></td>
				<td><?php echo esc_html($omnify_node['slug']); ?></td>
				<td style="text-align: right;">
					<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_global_category&index=' . $omnify_global_idx), 'omnify_delete_global_category_' . $omnify_global_idx)); ?>" class="omnify-button omnify-button--danger omnify-button--sm js-confirm-delete" data-message="<?php echo esc_attr(sprintf(__('Are you sure you want to delete category "%s"? Any subcategories will be unparented.', 'omnifywp-ecommerce'), $omnify_node['name'])); ?>" style="text-decoration: none; padding: 4px 8px; font-size: 11px;">
						<?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?>
					</a>
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

	<div class="omnify-card">
		<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 32px;">
			<!-- Left Side: Add Form -->
			<div>
				<h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Add New Category', 'omnifywp-ecommerce'); ?></h3>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<?php wp_nonce_field('omnify_add_global_category', 'omnify_add_global_cat_nonce'); ?>
					<input type="hidden" name="action" value="omnify_add_global_category" />
					
					<div class="omnify-form-group" style="margin-bottom: 16px;">
						<label for="category_name"><?php esc_html_e('Category Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="category_name" name="category_name" placeholder="e.g. Software, Design" required style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px;" />
					</div>

					<div class="omnify-form-group" style="margin-bottom: 16px;">
						<label for="category_parent"><?php esc_html_e('Parent Category', 'omnifywp-ecommerce'); ?></label>
						<select id="category_parent" name="category_parent" style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px; background: #fff; border: 1px solid var(--omnify-gray-300); border-radius: 6px;">
							<option value=""><?php esc_html_e('-- None --', 'omnifywp-ecommerce'); ?></option>
							<?php foreach ($omnify_global_categories as $omnify_c) : ?>
								<option value="<?php echo esc_attr($omnify_c['slug']); ?>"><?php echo esc_html($omnify_c['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 36px; justify-content: center; align-items: center;">
						<?php esc_html_e('Add Category', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>
			<!-- Right Side: List -->
			<div>
				<h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; font-weight: 600;"><?php esc_html_e('Global Categories List', 'omnifywp-ecommerce'); ?></h3>
				<?php if (empty($omnify_global_categories)) : ?>
					<p style="font-size: 13px; color: var(--omnify-gray-600); font-style: italic; margin-top: 0;"><?php esc_html_e('No global categories created yet.', 'omnifywp-ecommerce'); ?></p>
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
								<?php omnify_render_category_table_rows($omnify_category_tree); ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
