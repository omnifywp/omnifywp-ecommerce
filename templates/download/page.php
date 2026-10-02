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
<div class="omnify-download-page">
	<?php if (! $omnify_is_valid || ! $omnify_file) : ?>
		<p><?php esc_html_e('This download link is invalid, expired, or unavailable.', 'omnifywp-ecommerce'); ?></p>
	<?php else : ?>
		<h2><?php echo esc_html((string) $omnify_file['file_name']); ?></h2>
		<p><?php echo esc_html((string) $omnify_file['version']); ?> / <?php echo esc_html((string) $omnify_file['size_label']); ?></p>
		<p>
			<a class="button" href="<?php echo esc_url(add_query_arg(['omnify_download' => '1', 'file_id' => $omnify_file_id, 'expires' => $omnify_expires, 'customer_id' => $omnify_customer_id ?: 0, 'signature' => $omnify_signature], home_url('/'))); ?>">
				<?php esc_html_e('Download file', 'omnifywp-ecommerce'); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
