<?php
/**
 * Download page template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_customer_id = $omnify_template_vars['omnify_customer_id'] ?? null;
$omnify_expires = $omnify_template_vars['omnify_expires'] ?? null;
$omnify_file = $omnify_template_vars['omnify_file'] ?? null;
$omnify_file_id = $omnify_template_vars['omnify_file_id'] ?? null;
$omnify_is_valid = $omnify_template_vars['omnify_is_valid'] ?? null;
$omnify_signature = $omnify_template_vars['omnify_signature'] ?? null;


?>
<div class="omnify-download-shell" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
	<div class="omnify-download-card" style="max-width: 480px; width: 100%; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; box-shadow: 0 12px 36px rgba(15, 23, 42, 0.06); padding: 36px 30px; text-align: center;">
		<?php if (! $omnify_is_valid || ! $omnify_file) : ?>
			<div style="width: 56px; height: 56px; border-radius: 50%; background: #FEF2F2; color: #DC2626; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
			</div>
			<h2 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0 0 8px;"><?php esc_html_e('Link Expired or Invalid', 'omnifywp-ecommerce'); ?></h2>
			<p style="font-size: 0.9rem; color: #64748B; line-height: 1.5; margin: 0 0 24px;"><?php esc_html_e('This secure download link is no longer valid or has expired. You can sign in to your customer portal to generate a new link.', 'omnifywp-ecommerce'); ?></p>
			<a href="<?php echo esc_url(home_url('/')); ?>" style="display: inline-flex; height: 42px; padding: 0 22px; border-radius: 9999px; background: #18794E; color: #FFFFFF; text-decoration: none; align-items: center; font-size: 0.875rem; font-weight: 600; box-shadow: 0 4px 12px rgba(24,121,78,0.2);">
				<?php esc_html_e('Return to Store', 'omnifywp-ecommerce'); ?>
			</a>
		<?php else : ?>
			<div style="width: 58px; height: 58px; border-radius: 50%; background: #ECFDF5; color: #059669; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
			</div>
			<h2 style="font-size: 1.3rem; font-weight: 800; color: #0F172A; margin: 0 0 8px;"><?php echo esc_html((string) $omnify_file['file_name']); ?></h2>
			
			<div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 24px;">
				<?php if (! empty($omnify_file['version'])) : ?>
					<span style="font-size: 0.75rem; font-weight: 600; padding: 2px 10px; border-radius: 9999px; background: #F1F5F9; color: #475569;">v<?php echo esc_html((string) $omnify_file['version']); ?></span>
				<?php endif; ?>
				<?php if (! empty($omnify_file['size_label'])) : ?>
					<span style="font-size: 0.75rem; font-weight: 600; padding: 2px 10px; border-radius: 9999px; background: #F1F5F9; color: #475569;"><?php echo esc_html((string) $omnify_file['size_label']); ?></span>
				<?php endif; ?>
			</div>

			<a class="omnify-download-btn" href="<?php echo esc_url(add_query_arg(['omnify_download' => '1', 'file_id' => $omnify_file_id, 'expires' => $omnify_expires, 'customer_id' => $omnify_customer_id ?: 0, 'signature' => $omnify_signature], home_url('/'))); ?>" style="display: flex; width: 100%; height: 48px; border-radius: 9999px; background: #18794E; color: #FFFFFF; text-decoration: none; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; font-weight: 700; box-shadow: 0 4px 14px rgba(24,121,78,0.25); transition: background 0.2s;">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
				<span><?php esc_html_e('Download File', 'omnifywp-ecommerce'); ?></span>
			</a>

			<p style="font-size: 0.78rem; color: #94A3B8; margin-top: 18px; margin-bottom: 0;">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<?php esc_html_e('Authenticated, direct cryptographic streaming link.', 'omnifywp-ecommerce'); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
