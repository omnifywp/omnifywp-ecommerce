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
$omnify_current_rating  = isset($_GET['rating']) ? absint(wp_unslash($_GET['rating'])) : 0;
$omnify_search          = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$omnify_paged           = absint($omnify_template_vars['omnify_paged'] ?? (isset($_GET['paged']) ? absint($_GET['paged']) : 1));
$omnify_total_reviews   = absint($omnify_template_vars['omnify_total_reviews'] ?? count($omnify_reviews ?? []));
$omnify_total_pages     = max(1, absint($omnify_template_vars['omnify_total_pages'] ?? 1));
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
	'trash'    => ['label' => __('Trash', 'omnifywp-ecommerce'), 'count' => $omnify_status_counts['trash'] ?? 0],
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
	'trash'    => __('Trash', 'omnifywp-ecommerce'),
];
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'reviews'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<h1><?php esc_html_e('Product Reviews', 'omnifywp-ecommerce'); ?></h1>
			<p><?php esc_html_e('Review customer feedback, publish useful testimonials, and keep rejected or pending comments organized.', 'omnifywp-ecommerce'); ?></p>
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
	<div class="omnify-reviews-stats">
		<?php 
		$omnify_stat_icons = [
			'all'      => 'dashicons-format-chat',
			'pending'  => 'dashicons-clock',
			'approved' => 'dashicons-yes-alt',
			'rejected' => 'dashicons-dismiss',
		];
		foreach ($omnify_stats as $omnify_stat) : 
			$omnify_s_tone = $omnify_stat['tone'];
			$omnify_s_icon = $omnify_stat_icons[$omnify_s_tone] ?? 'dashicons-star-filled';
		?>
			<div class="omnify-reviews-stat-card omnify-reviews-stat-card--<?php echo esc_attr($omnify_s_tone); ?>">
				<div class="omnify-rev-stat-top">
					<div class="omnify-reviews-stat-label"><?php echo esc_html($omnify_stat['label']); ?></div>
					<span class="omnify-rev-stat-icon omnify-rev-stat-icon--<?php echo esc_attr($omnify_s_tone); ?>">
						<span class="dashicons <?php echo esc_attr($omnify_s_icon); ?>"></span>
					</span>
				</div>
				<div class="omnify-reviews-stat-value"><?php echo esc_html(number_format_i18n((int) $omnify_stat['value'])); ?></div>
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

		<form method="get" class="omnify-reviews-filter-form" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
			<input type="hidden" name="page" value="omnify-reviews" />
			<input type="hidden" name="status" value="<?php echo esc_attr($omnify_current_status); ?>" />
			<label for="omnify-review-product-filter" class="screen-reader-text"><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></label>
			<select id="omnify-review-product-filter" name="product_id" onchange="this.form.submit()">
				<option value=""><?php esc_html_e('All products', 'omnifywp-ecommerce'); ?></option>
				<?php foreach ($omnify_products as $omnify_p) : ?>
					<option value="<?php echo esc_attr($omnify_p['id']); ?>" <?php selected($omnify_current_product, $omnify_p['id']); ?>>
						<?php echo esc_html($omnify_p['name']); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="omnify-review-rating-filter" class="screen-reader-text"><?php esc_html_e('Rating', 'omnifywp-ecommerce'); ?></label>
			<select id="omnify-review-rating-filter" name="rating" onchange="this.form.submit()">
				<option value=""><?php esc_html_e('All ratings', 'omnifywp-ecommerce'); ?></option>
				<?php for ($omnify_r = 5; $omnify_r >= 1; $omnify_r--) : ?>
					<option value="<?php echo esc_attr((string) $omnify_r); ?>" <?php selected($omnify_current_rating, $omnify_r); ?>>
						<?php /* translators: %d: rating score */ printf(esc_html__('%d Stars', 'omnifywp-ecommerce'), $omnify_r); ?>
					</option>
				<?php endfor; ?>
			</select>
			<div style="display: flex; gap: 4px;">
				<input type="search" name="s" value="<?php echo esc_attr($omnify_search); ?>" placeholder="<?php esc_attr_e('Search reviews...', 'omnifywp-ecommerce'); ?>" style="padding: 4px 8px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px;" />
				<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm"><?php esc_html_e('Search', 'omnifywp-ecommerce'); ?></button>
			</div>
		</form>
	</div>

	<?php if (empty($omnify_reviews)) : ?>
		<div class="omnify-reviews-empty">
			<div>
				<div class="omnify-empty-state__icon" style="width: 52px; height: 52px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
					<span class="dashicons dashicons-star-filled" style="font-size: 26px; width: 26px; height: 26px; color: #94a3b8;"></span>
				</div>
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
					<div class="omnify-review-left-col">
						<div class="omnify-reviewer">
							<div class="omnify-reviewer-avatar"><?php echo esc_html($omnify_initial); ?></div>
							<div class="omnify-reviewer-info">
								<div class="omnify-reviewer-name"><?php echo esc_html($omnify_reviewer); ?></div>
								<div class="omnify-reviewer-email"><?php echo esc_html($omnify_review['reviewer_email'] ?? ''); ?></div>
							</div>
						</div>
						<div class="omnify-review-product-wrap">
							<span class="omnify-review-product"><span class="dashicons dashicons-tag" style="font-size:11px; width:11px; height:11px; margin-right:3px; vertical-align:middle;"></span><?php echo esc_html($omnify_product_name); ?></span>
						</div>
					</div>

					<div class="omnify-review-copy">
						<?php if ('' !== $omnify_title) : ?>
							<h3 class="omnify-review-title"><?php echo esc_html($omnify_title); ?></h3>
						<?php endif; ?>
						<p class="omnify-review-excerpt"><?php echo esc_html($omnify_excerpt); ?></p>
					</div>

					<div class="omnify-review-rating">
						<div class="omnify-reviews-stars" aria-label="<?php
						/* translators: %d: Rating */
						echo esc_attr(sprintf(__('%d out of 5 stars', 'omnifywp-ecommerce'), $omnify_rating)); ?>">
							<?php for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) : ?><span class="dashicons <?php echo $omnify_i <= $omnify_rating ? "dashicons-star-filled" : "dashicons-star-empty"; ?>" style="font-size:15px; width:15px; height:15px; color:#f59e0b;"></span><?php endfor; ?>
						</div>
						<div class="omnify-review-rating-value">
							<?php
							/* translators: %d: Rating */
							echo esc_html(sprintf(__('%d/5 rating', 'omnifywp-ecommerce'), $omnify_rating)); ?>
						</div>
						<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_status === 'approved' ? 'completed' : ($omnify_status === 'pending' ? 'draft' : 'failed')); ?>">
							<?php echo esc_html($omnify_status_labels[$omnify_status] ?? ucfirst($omnify_status)); ?>
						</span>
					</div>

					<div class="omnify-review-meta-actions">
						<div class="omnify-review-date"><?php echo esc_html($omnify_date); ?></div>
						<div class="omnify-reviews-actions">
							<?php if ('approved' !== $omnify_status) : ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_approve_review&id=' . $omnify_review['id']), 'omnify_approve_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--approve" title="<?php esc_attr_e('Approve Review', 'omnifywp-ecommerce'); ?>">
									<span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
							<?php if ('rejected' !== $omnify_status) : ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_reject_review&id=' . $omnify_review['id']), 'omnify_reject_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--reject" title="<?php esc_attr_e('Reject Review', 'omnifywp-ecommerce'); ?>">
									<span class="dashicons dashicons-no-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
							<?php $omnify_is_trashed = !empty($omnify_review['deleted_at']); ?>
							<?php if ($omnify_is_trashed): ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_review&id=' . $omnify_review['id']), 'omnify_restore_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Restore', 'omnifywp-ecommerce'); ?>"><span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span></a>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_review&id=' . $omnify_review['id']), 'omnify_delete_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Delete permanently', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php esc_attr_e('Permanently delete review?', 'omnifywp-ecommerce'); ?>');">
									<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php else: ?>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_review&id=' . $omnify_review['id']), 'omnify_trash_review_' . $omnify_review['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="<?php esc_attr_e('Move to trash', 'omnifywp-ecommerce'); ?>" onclick="return confirm('<?php esc_attr_e('Trash review?', 'omnifywp-ecommerce'); ?>');">
									<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ($omnify_total_pages > 1) : ?>
			<div class="tablenav" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-top: 1px solid var(--omnify-gray-200, #e2e8f0); margin-top: 16px; background: #fff; border-radius: 8px;">
				<div class="tablenav-pages" style="display: flex; align-items: center; gap: 8px;">
					<span class="displaying-num" style="font-size: 13px; color: #64748b;">
						<?php
						/* translators: %s: total reviews count */
						printf(esc_html(_n('%s review', '%s reviews', $omnify_total_reviews, 'omnifywp-ecommerce')), number_format_i18n($omnify_total_reviews));
						?>
					</span>
					<span class="pagination-links" style="display: flex; gap: 4px; align-items: center;">
						<?php
						$omnify_current_url = remove_query_arg('paged');
						if ($omnify_paged > 1) : ?>
							<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('First page', 'omnifywp-ecommerce'); ?>">&laquo;</a>
							<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_paged - 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('Previous page', 'omnifywp-ecommerce'); ?>">&lsaquo;</a>
						<?php endif; ?>
						<span class="paging-input" style="font-size: 13px; margin: 0 4px;">
							<span class="current-page"><?php echo esc_html((string) $omnify_paged); ?></span>
							<span class="tablenav-paging-text"> <?php esc_html_e('of', 'omnifywp-ecommerce'); ?> <span class="total-pages"><?php echo esc_html((string) $omnify_total_pages); ?></span></span>
						</span>
						<?php if ($omnify_paged < $omnify_total_pages) : ?>
							<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_paged + 1, $omnify_current_url)); ?>" title="<?php esc_attr_e('Next page', 'omnifywp-ecommerce'); ?>">&rsaquo;</a>
							<a class="button omnify-button omnify-button--secondary omnify-button--sm" href="<?php echo esc_url(add_query_arg('paged', $omnify_total_pages, $omnify_current_url)); ?>" title="<?php esc_attr_e('Last page', 'omnifywp-ecommerce'); ?>">&raquo;</a>
						<?php endif; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
