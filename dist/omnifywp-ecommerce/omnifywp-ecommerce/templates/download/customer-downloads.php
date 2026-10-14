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
<div class="omnify-customer-downloads">
	<?php if (! is_user_logged_in()) : ?>
		<p><?php esc_html_e('Please log in to view your downloads.', 'omnifywp-ecommerce'); ?></p>
	<?php elseif (! $omnify_customer) : ?>
		<p><?php esc_html_e('No customer profile is connected to this account.', 'omnifywp-ecommerce'); ?></p>
	<?php elseif (empty($omnify_files)) : ?>
		<p><?php esc_html_e('No downloads are available for your account.', 'omnifywp-ecommerce'); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ($omnify_files as $omnify_file) : ?>
				<li>
					<strong><?php echo esc_html((string) $omnify_file['product']['name']); ?></strong>
					<span><?php echo esc_html((string) $omnify_file['file_name']); ?></span>
					<a href="<?php echo esc_url((string) $omnify_file['download_url']); ?>"><?php esc_html_e('Download', 'omnifywp-ecommerce'); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
