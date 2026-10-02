<?php
/**
 * Admin Reviews Management Page.
 *
 * @package Omnify
 * Variables available: $reviews, $status_counts, $products, $settings
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_base_url = $omnify_template_vars['omnify_base_url'] ?? null;
$omnify_content = $omnify_template_vars['omnify_content'] ?? null;
$omnify_current_product = $omnify_template_vars['omnify_current_product'] ?? null;
$omnify_current_status = $omnify_template_vars['omnify_current_status'] ?? null;
$omnify_date = $omnify_template_vars['omnify_date'] ?? null;
$omnify_excerpt = $omnify_template_vars['omnify_excerpt'] ?? null;
$omnify_i = $omnify_template_vars['omnify_i'] ?? null;
$omnify_initial = $omnify_template_vars['omnify_initial'] ?? null;
$omnify_is_active = $omnify_template_vars['omnify_is_active'] ?? null;
$omnify_is_trashed = $omnify_template_vars['omnify_is_trashed'] ?? null;
$omnify_key = $omnify_template_vars['omnify_key'] ?? null;
$omnify_p = $omnify_template_vars['omnify_p'] ?? null;
$omnify_product_map = $omnify_template_vars['omnify_product_map'] ?? null;
$omnify_product_name = $omnify_template_vars['omnify_product_name'] ?? null;
$omnify_products = $omnify_template_vars['omnify_products'] ?? ($omnify_template_vars['products'] ?? null);
$omnify_rating = $omnify_template_vars['omnify_rating'] ?? null;
$omnify_review = $omnify_template_vars['omnify_review'] ?? null;
$omnify_reviewer = $omnify_template_vars['omnify_reviewer'] ?? null;
$omnify_reviews = $omnify_template_vars['omnify_reviews'] ?? ($omnify_template_vars['reviews'] ?? null);
$omnify_stat = $omnify_template_vars['omnify_stat'] ?? null;
$omnify_stats = $omnify_template_vars['omnify_stats'] ?? null;
$omnify_status = $omnify_template_vars['omnify_status'] ?? null;
$omnify_status_counts = $omnify_template_vars['omnify_status_counts'] ?? ($omnify_template_vars['status_counts'] ?? null);
$omnify_status_labels = $omnify_template_vars['omnify_status_labels'] ?? null;
$omnify_tab = $omnify_template_vars['omnify_tab'] ?? null;
$omnify_tab_url = $omnify_template_vars['omnify_tab_url'] ?? null;
$omnify_tabs = $omnify_template_vars['omnify_tabs'] ?? null;
$omnify_title = $omnify_template_vars['omnify_title'] ?? null;


$omnify_current_status  = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
$omnify_current_product = isset($_GET['product_id']) ? absint(wp_unslash($_GET['product_id'])) : 0;
$omnify_base_url        = admin_url('admin.php?page=omnify-reviews');
$omnify_products        = is_array($omnify_products) ? $omnify_products : [];
$omnify_reviews         = is_array($omnify_reviews) ? $omnify_reviews : [];
$omnify_status_counts   = is_array($omnify_status_counts) ? $omnify_status_counts : [];

$omnify_product_map = [];
foreach ($omnify_products as $omnify_p) {
	$omnify_product_map[(int) $omnify_p['id']] = $omnify_p['name'];
}

$omnify_tabs = [
	''         => ['label' => __('All', 'omnifywp-ecommerce'), 'count' => $omnify_status_counts['all'] ?? 0],
	'pending'  => ['label' => __('Pending', 'omnifywp-ecommerce'), 'count' => $omnify_status_counts['pending'] ?? 0],
	'approved' => ['label' => __('Approved', 'omnifywp-ecommerce'), 'count' => $omnify_status_counts['approved'] ?? 0],
	'rejected' => ['label' => __('Rejected', 'omnifywp-ecommerce'), 'count' => $omnify_status_counts['rejected'] ?? 0],
];

$omnify_stats = [
	[
		'value' => $omnify_status_counts['all'] ?? 0,
		'label' => __('Total Reviews', 'omnifywp-ecommerce'),
		'tone'  => 'all',
	],
	[
		'value' => $omnify_status_counts['pending'] ?? 0,
		'label' => __('Needs Review', 'omnifywp-ecommerce'),
		'tone'  => 'pending',
	],
	[
		'value' => $omnify_status_counts['approved'] ?? 0,
		'label' => __('Published', 'omnifywp-ecommerce'),
		'tone'  => 'approved',
	],
	[
		'value' => $omnify_status_counts['rejected'] ?? 0,
		'label' => __('Rejected', 'omnifywp-ecommerce'),
		'tone'  => 'rejected',
	],
];

$omnify_status_labels = [
	'approved' => __('Approved', 'omnifywp-ecommerce'),
	'pending'  => __('Pending', 'omnifywp-ecommerce'),
	'rejected' => __('Rejected', 'omnifywp-ecommerce'),
];
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'reviews'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Reviews', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Review customer feedback, publish useful testimonials, and keep rejected or pending comments organized.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>
	<?php include __DIR__ . '/partials/product-subtabs.php'; ?>
	<div class="omnify-reviews-stats">
		<?php foreach ($omnify_stats as $omnify_stat) : ?>
			<div class="omnify-reviews-stat-card <?php echo esc_attr($omnify_stat['tone']); ?>">
				<div class="omnify-reviews-stat-value"><?php echo esc_html(number_format_i18n((int) $omnify_stat['value'])); ?></div>
				<div class="omnify-reviews-stat-label"><?php echo esc_html($omnify_stat['label']); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="omnify-reviews-toolbar">
		<div class="omnify-reviews-tabs">
			<?php foreach ($omnify_tabs as $omnify_key => $omnify_tab) :
				$omnify_tab_url = add_query_arg(
					[
						'status'     => $omnify_key ? $omnify_key : '',
						'product_id' => $omnify_current_product ?: '',
					],
					$omnify_base_url
				);
				$omnify_is_active = ($omnify_current_status === $omnify_key);
			?>
				<a href="<?php echo esc_url($omnify_tab_url); ?>" class="omnify-reviews-tab <?php echo esc_attr( $omnify_is_active ? 'active' : '' ); ?>">
					<?php echo esc_html($omnify_tab['label']); ?>
					<span class="omnify-tab-badge"><?php echo esc_html(number_format_i18n((int) ($omnify_tab['count'] ?? 0))); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<form method="get" class="omnify-reviews-filter-form">
			<input type="hidden" name="page" value="omnify-reviews" />
			<input type="hidden" name="status" value="<?php echo esc_attr($omnify_current_status); ?>" />
			<label for="omnify-review-product-filter"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></label>
			<select id="omnify-review-product-filter" name="product_id" onchange="this.form.submit()">
				<option value=""><?php esc_html_e('All products', 'omnifywp-ecommerce'); ?></option>
				<?php foreach ($omnify_products as $omnify_p) : ?>
					<option value="<?php echo esc_attr($omnify_p['id']); ?>" <?php selected($omnify_current_product, $omnify_p['id']); ?>>
						<?php echo esc_html($omnify_p['name']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</form>
	</div>

	<?php if (empty($omnify_reviews)) : ?>
		<div class="omnify-reviews-empty">
			<div>
				<h2><?php esc_html_e('No reviews found', 'omnifywp-ecommerce'); ?></h2>
				<p><?php esc_html_e('Try another status or product filter.', 'omnifywp-ecommerce'); ?></p>
			</div>
		</div>
	<?php else : ?>
		<div class="omnify-reviews-list">
			<?php foreach ($omnify_reviews as $omnify_review) :
				$omnify_product_name = $omnify_product_map[(int) $omnify_review['product_id']] ?? __('Unknown product', 'omnifywp-ecommerce');
				$omnify_status       = sanitize_key($omnify_review['status'] ?? 'pending');
				$omnify_rating       = min(5, max(0, (int) ($omnify_review['rating'] ?? 0)));
				$omnify_content      = wp_strip_all_tags((string) ($omnify_review['content'] ?? ''));
				$omnify_excerpt      = wp_trim_words($omnify_content, 32, '...');
				$omnify_date         = date_i18n(get_option('date_format'), strtotime($omnify_review['created_at'] ?? 'now'));
				$omnify_reviewer     = (string) ($omnify_review['reviewer_name'] ?: __('Anonymous', 'omnifywp-ecommerce'));
				$omnify_initial      = strtoupper(substr($omnify_reviewer, 0, 1));
				$omnify_title        = trim((string) ($omnify_review['title'] ?? ''));
			?>
				<div class="omnify-review-card">
					<div>
						<div class="omnify-reviewer">
							<div class="omnify-reviewer-avatar"><?php echo esc_html($omnify_initial); ?></div>
							<div>
								<div class="omnify-reviewer-name"><?php echo esc_html($omnify_reviewer); ?></div>
								<div class="omnify-reviewer-email"><?php echo esc_html($omnify_review['reviewer_email'] ?? ''); ?></div>
							</div>
						</div>
						<div class="omnify-review-product"><?php echo esc_html($omnify_product_name); ?></div>
					</div>

					<div class="omnify-review-copy">
						<?php if ('' !== $omnify_title) : ?>
							<h3 class="omnify-review-title"><?php echo esc_html($omnify_title); ?></h3>
						<?php endif; ?>
						<p class="omnify-review-excerpt"><?php echo esc_html($omnify_excerpt); ?></p>
					</div>

					<div class="omnify-review-rating">
						<div class="omnify-reviews-stars" aria-label="<?php
						// translators: %d: placeholder value. echo esc_attr(sprintf(__('%d out of 5 stars', 'omnifywp-ecommerce'), $rating)); ?>">
							<?php for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) : ?><?php echo $omnify_i <= $omnify_rating ? '★' : '☆'; ?><?php endfor; ?>
						</div>
						<div class="omnify-review-rating-value"><?php
						// translators: %d: placeholder value. echo esc_html(sprintf(__('%d/5 rating', 'omnifywp-ecommerce'), $rating)); ?></div>
						<span class="omnify-status-badge <?php echo esc_attr($omnify_status); ?>">
							<?php echo esc_html($omnify_status_labels[$omnify_status] ?? ucfirst($omnify_status)); ?>
						</span>
					</div>

					<div class="omnify-review-meta-actions">
						<div class="omnify-review-date"><?php echo esc_html($omnify_date); ?></div>
						<div class="omnify-reviews-actions" style="display:inline-flex; gap:4px;">
							<?php if ('approved' !== $omnify_status) : ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_approve_review&id=' . $omnify_review['id']), 'omnify_approve_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Approve', 'omnifywp-ecommerce'); ?>" style="background:var(--omnify-success);color:#fff;border-color:var(--omnify-success);">
									<span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
							<?php if ('rejected' !== $omnify_status) : ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_reject_review&id=' . $omnify_review['id']), 'omnify_reject_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Reject', 'omnifywp-ecommerce'); ?>">
									<span class="dashicons dashicons-no-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
							<?php $omnify_is_trashed = !empty($omnify_review['deleted_at']); ?>
							<?php if ($omnify_is_trashed): ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_review&id=' . $omnify_review['id']), 'omnify_restore_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn" title="Restore"><span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span></a>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_review&id=' . $omnify_review['id']), 'omnify_delete_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Delete permanently" onclick="return confirm('Permanently delete?');">
									<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php else: ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_review&id=' . $omnify_review['id']), 'omnify_trash_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Move to trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('Trash?');">
									<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
