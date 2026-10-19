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
$omnify_customer      = $omnify_template_vars['omnify_customer'] ?? null;
$omnify_file          = $omnify_template_vars['omnify_file'] ?? null;
$omnify_files         = $omnify_template_vars['omnify_files'] ?? [];
$omnify_guest_error   = $omnify_template_vars['omnify_guest_error'] ?? '';
$omnify_guest_success = $omnify_template_vars['omnify_guest_success'] ?? '';
$omnify_guest_lookup  = ! empty($omnify_template_vars['omnify_guest_lookup']);

?>
<div class="omnify-customer-downloads" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 760px; margin: 0 auto;">
	<?php if (! empty($omnify_guest_error)) : ?>
		<div style="background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
			<?php echo esc_html($omnify_guest_error); ?>
		</div>
	<?php endif; ?>

	<?php if (! empty($omnify_guest_success)) : ?>
		<div style="background: #ECFDF5; border: 1px solid #6EE7B7; color: #065F46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
			<?php echo esc_html($omnify_guest_success); ?>
		</div>
	<?php endif; ?>

	<?php if (! is_user_logged_in() && empty($omnify_files)) : ?>
		<div style="display: grid; gap: 20px;">
			<div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 24px; text-align: center;">
				<h3 style="margin: 0 0 8px; font-size: 1.1rem; color: #0F172A; font-weight: 600;"><?php esc_html_e('Registered Customers', 'omnifywp-ecommerce'); ?></h3>
				<p style="font-size: 0.9rem; color: #475569; margin: 0 0 16px;"><?php esc_html_e('Sign in to access all current and past digital downloads tied to your account.', 'omnifywp-ecommerce'); ?></p>
				<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display: inline-flex; height: 38px; padding: 0 20px; border-radius: 9999px; background: #18794E; color: #FFFFFF; font-size: 0.85rem; font-weight: 600; text-decoration: none; align-items: center;">
					<?php esc_html_e('Sign In', 'omnifywp-ecommerce'); ?>
				</a>
			</div>

			<div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 24px;">
				<h3 style="margin: 0 0 8px; font-size: 1.1rem; color: #0F172A; font-weight: 600;"><?php esc_html_e('Guest Order Download Lookup', 'omnifywp-ecommerce'); ?></h3>
				<p style="font-size: 0.875rem; color: #64748B; margin: 0 0 18px;"><?php esc_html_e('Purchased without creating an account or link expired? Enter your order number and billing email to generate fresh download links.', 'omnifywp-ecommerce'); ?></p>
				<form method="post" action="<?php echo esc_url(get_permalink()); ?>" style="display: grid; gap: 14px;">
					<?php wp_nonce_field('omnify_guest_downloads', 'omnify_guest_nonce'); ?>
					<input type="hidden" name="omnify_guest_download_lookup" value="1">
					<div>
						<label for="omnify_order_number" style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 5px;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="omnify_order_number" name="omnify_order_number" required placeholder="<?php esc_attr_e('e.g. 1042 or #1042', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr(sanitize_text_field(wp_unslash((string) ($_POST['omnify_order_number'] ?? '')))); ?>" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box;">
					</div>
					<div>
						<label for="omnify_order_email" style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 5px;"><?php esc_html_e('Billing Email', 'omnifywp-ecommerce'); ?></label>
						<input type="email" id="omnify_order_email" name="omnify_order_email" required placeholder="<?php esc_attr_e('you@example.com', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr(sanitize_email(wp_unslash((string) ($_POST['omnify_order_email'] ?? '')))); ?>" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box;">
					</div>
					<div>
						<button type="submit" style="display: inline-flex; height: 40px; padding: 0 22px; border-radius: 8px; background: #0F172A; color: #FFFFFF; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; align-items: center; justify-content: center;">
							<?php esc_html_e('Retrieve Downloads', 'omnifywp-ecommerce'); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php elseif (empty($omnify_files)) : ?>
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
		<?php if (! is_user_logged_in() && ! empty($omnify_guest_lookup)) : ?>
			<div style="margin-top: 16px; text-align: right;">
				<a href="<?php echo esc_url(get_permalink()); ?>" style="font-size: 0.825rem; color: #64748B; text-decoration: underline;"><?php esc_html_e('Look up another order', 'omnifywp-ecommerce'); ?></a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
