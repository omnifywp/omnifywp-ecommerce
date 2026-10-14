<?php
/**
 * Customer downloads template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_customer = $omnify_template_vars['omnify_customer'] ?? null;
$omnify_file = $omnify_template_vars['omnify_file'] ?? null;
$omnify_files = $omnify_template_vars['omnify_files'] ?? null;


?>
<div class="omnify-customer-downloads" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
	<?php if (! is_user_logged_in()) : ?>
		<div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 32px 20px; text-align: center;">
			<p style="font-size: 0.95rem; color: #475569; margin: 0 0 16px;"><?php esc_html_e('Please sign in to access your digital downloads.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display: inline-flex; height: 38px; padding: 0 20px; border-radius: 9999px; background: #18794E; color: #FFFFFF; font-size: 0.85rem; font-weight: 600; text-decoration: none; align-items: center;">
				<?php esc_html_e('Sign In', 'omnifywp-ecommerce'); ?>
			</a>
		</div>
	<?php elseif (! $omnify_customer || empty($omnify_files)) : ?>
		<div style="background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 12px; padding: 40px 20px; text-align: center;">
			<div style="width: 48px; height: 48px; border-radius: 50%; background: #EFF8F2; color: #18794E; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
			</div>
			<p style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin: 0 0 6px;"><?php esc_html_e('No downloads available yet', 'omnifywp-ecommerce'); ?></p>
			<p style="font-size: 0.825rem; color: #64748B; margin: 0;"><?php esc_html_e('Files from your digital purchases will appear here.', 'omnifywp-ecommerce'); ?></p>
		</div>
	<?php else : ?>
		<div class="omnify-downloads-grid" style="display: grid; gap: 12px;">
			<?php foreach ($omnify_files as $omnify_file) : ?>
				<div class="omnify-download-row-card" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px 20px; transition: border-color 0.15s ease;">
					<div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
						<div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
						</div>
						<div style="min-width: 0;">
							<strong style="display: block; font-size: 0.9rem; font-weight: 700; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html((string) $omnify_file['file_name']); ?></strong>
							<span style="font-size: 0.78rem; color: #64748B;"><?php echo esc_html((string) ($omnify_file['product']['name'] ?? 'Product asset')); ?></span>
						</div>
					</div>
					<a href="<?php echo esc_url((string) $omnify_file['download_url']); ?>" style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 9999px; background: #18794E; color: #FFFFFF; font-size: 0.8rem; font-weight: 600; text-decoration: none; flex-shrink: 0; box-shadow: 0 2px 6px rgba(24,121,78,0.2);">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
						<span><?php esc_html_e('Download', 'omnifywp-ecommerce'); ?></span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
