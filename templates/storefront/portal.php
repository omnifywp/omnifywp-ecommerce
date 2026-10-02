<?php
/**
 * Customer portal template.
 *
 * @package Omnify
 */

if (! defined('ABSPATH')) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_addr = $omnify_template_vars['omnify_addr'] ?? null;
$omnify_addresses = $omnify_template_vars['omnify_addresses'] ?? null;
$omnify_base_username = $omnify_template_vars['omnify_base_username'] ?? null;
$omnify_code = $omnify_template_vars['omnify_code'] ?? null;
$omnify_confirm = $omnify_template_vars['omnify_confirm'] ?? null;
$omnify_countries = $omnify_template_vars['omnify_countries'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_current = $omnify_template_vars['omnify_current'] ?? null;
$omnify_customer = $omnify_template_vars['omnify_customer'] ?? null;
$omnify_customer_notes = $omnify_template_vars['omnify_customer_notes'] ?? null;
$omnify_dl_count_total = $omnify_template_vars['omnify_dl_count_total'] ?? null;
$omnify_download_history = $omnify_template_vars['omnify_download_history'] ?? null;
$omnify_email = $omnify_template_vars['omnify_email'] ?? null;
$omnify_email_parts = $omnify_template_vars['omnify_email_parts'] ?? null;
$omnify_ext = $omnify_template_vars['omnify_ext'] ?? null;
$omnify_f = $omnify_template_vars['omnify_f'] ?? null;
$omnify_file = $omnify_template_vars['omnify_file'] ?? null;
$omnify_files = $omnify_template_vars['omnify_files'] ?? null;
$omnify_first_name = $omnify_template_vars['omnify_first_name'] ?? null;
$omnify_format_price = $omnify_template_vars['omnify_format_price'] ?? null;
$omnify_fulfillment = $omnify_template_vars['omnify_fulfillment'] ?? null;
$omnify_icons = $omnify_template_vars['omnify_icons'] ?? null;
$omnify_is_eligible_for_refund = $omnify_template_vars['omnify_is_eligible_for_refund'] ?? null;
$omnify_item = $omnify_template_vars['omnify_item'] ?? null;
$omnify_item_ids = $omnify_template_vars['omnify_item_ids'] ?? null;
$omnify_last_name = $omnify_template_vars['omnify_last_name'] ?? null;
$omnify_latest_order = $omnify_template_vars['omnify_latest_order'] ?? null;
$omnify_log = $omnify_template_vars['omnify_log'] ?? null;
$omnify_map = $omnify_template_vars['omnify_map'] ?? null;
$omnify_member_since = $omnify_template_vars['omnify_member_since'] ?? null;
$omnify_name = $omnify_template_vars['omnify_name'] ?? null;
$omnify_new_pw = $omnify_template_vars['omnify_new_pw'] ?? null;
$omnify_note = $omnify_template_vars['omnify_note'] ?? null;
$omnify_o = $omnify_template_vars['omnify_o'] ?? null;
$omnify_order = $omnify_template_vars['omnify_order'] ?? null;
$omnify_order_items = $omnify_template_vars['omnify_order_items'] ?? null;
$omnify_order_sym = $omnify_template_vars['omnify_order_sym'] ?? null;
$omnify_orders = $omnify_template_vars['omnify_orders'] ?? null;
$omnify_password = $omnify_template_vars['omnify_password'] ?? null;
$omnify_password_error = $omnify_template_vars['omnify_password_error'] ?? null;
$omnify_password_success = $omnify_template_vars['omnify_password_success'] ?? null;
$omnify_pf = $omnify_template_vars['omnify_pf'] ?? null;
$omnify_portal_action = $omnify_template_vars['omnify_portal_action'] ?? null;
$omnify_portal_auth_error = $omnify_template_vars['omnify_portal_auth_error'] ?? null;
$omnify_portal_auth_mode = $omnify_template_vars['omnify_portal_auth_mode'] ?? null;
$omnify_portal_auth_success = $omnify_template_vars['omnify_portal_auth_success'] ?? null;
$omnify_portal_redirect_to = $omnify_template_vars['omnify_portal_redirect_to'] ?? null;
$omnify_prod = $omnify_template_vars['omnify_prod'] ?? null;
$omnify_prod_files = $omnify_template_vars['omnify_prod_files'] ?? null;
$omnify_prod_is_physical = $omnify_template_vars['omnify_prod_is_physical'] ?? null;
$omnify_prod_orders = $omnify_template_vars['omnify_prod_orders'] ?? null;
$omnify_prod_variation_settings = $omnify_template_vars['omnify_prod_variation_settings'] ?? null;
$omnify_products_accessed = $omnify_template_vars['omnify_products_accessed'] ?? null;
$omnify_profile_saved = $omnify_template_vars['omnify_profile_saved'] ?? null;
$omnify_receipt_url = $omnify_template_vars['omnify_receipt_url'] ?? null;
$omnify_remember = $omnify_template_vars['omnify_remember'] ?? null;
$omnify_result = $omnify_template_vars['omnify_result'] ?? null;
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? null;
$omnify_signon = $omnify_template_vars['omnify_signon'] ?? null;
$omnify_status = $omnify_template_vars['omnify_status'] ?? null;
$omnify_storefront_url = $omnify_template_vars['omnify_storefront_url'] ?? null;
$omnify_suffix = $omnify_template_vars['omnify_suffix'] ?? null;
$omnify_sym = $omnify_template_vars['omnify_sym'] ?? null;
$omnify_total_files = $omnify_template_vars['omnify_total_files'] ?? null;
$omnify_total_orders = $omnify_template_vars['omnify_total_orders'] ?? null;
$omnify_total_spent = $omnify_template_vars['omnify_total_spent'] ?? null;
$omnify_user_id = $omnify_template_vars['omnify_user_id'] ?? null;
$omnify_user_login = $omnify_template_vars['omnify_user_login'] ?? null;
$omnify_username = $omnify_template_vars['omnify_username'] ?? null;
$omnify_wish_price = $omnify_template_vars['omnify_wish_price'] ?? null;
$omnify_wish_product = $omnify_template_vars['omnify_wish_product'] ?? null;
$omnify_wish_url = $omnify_template_vars['omnify_wish_url'] ?? null;
$omnify_wishlist_products = $omnify_template_vars['omnify_wishlist_products'] ?? null;
$omnify_wp_user = $omnify_template_vars['omnify_wp_user'] ?? null;


/* Helpers. */
$omnify_sym = function(string $omnify_currency): string {
	$omnify_map = [
		'USD' => '$',  'EUR' => 'EUR',  'GBP' => 'GBP',  'JPY' => 'JPY',
		'CAD' => 'C$', 'AUD' => 'A$', 'CHF' => 'Fr',  'CNY' => 'CNY',
		'HKD' => 'HK$','SGD' => 'S$', 'INR' => 'INR',  'BRL' => 'R$',
		'MXN' => '$',  'KRW' => 'KRW',  'TRY' => 'TRY',  'RUB' => 'RUB',
		'ZAR' => 'R',  'NGN' => 'NGN',  'AED' => 'AED', 'SAR' => 'SAR',
		'TWD' => 'NT$','THB' => 'THB',  'MYR' => 'RM',  'IDR' => 'Rp',
		'PHP' => 'PHP',  'VND' => 'VND',  'PKR' => 'PKR',  'BDT' => 'BDT',
	];
	return $omnify_map[$omnify_currency] ?? $omnify_currency;
};

/* Password-change handler. */
$omnify_password_error   = '';
$omnify_portal_redirect_to = ! empty($_REQUEST['redirect_to'])
	? wp_validate_redirect(rawurldecode(sanitize_text_field(wp_unslash((string) $_REQUEST['redirect_to']))), get_permalink())
	: get_permalink();

$omnify_countries = \Omnify\eCommerce\Support\Omnify_Locations::countries();
?>

<div class="omnify-storefront-wrapper">
<div class="omnify-portal-wrapper">
<?php if (! is_user_logged_in()) : ?>
	<div class="omnify-auth-shell">
		<div class="omnify-auth-intro">
			<span class="omnify-auth-eyebrow"><?php esc_html_e('Customer account', 'omnifywp-ecommerce'); ?></span>
			<h2><?php esc_html_e('Everything you bought, saved, and downloaded in one place.', 'omnifywp-ecommerce'); ?></h2>
			<p><?php esc_html_e('A dedicated customer hub for downloadable files, order history, invoices, wishlist items, saved addresses, and account security.', 'omnifywp-ecommerce'); ?></p>
			<div class="omnify-auth-benefits">
				<span><?php esc_html_e('Downloads', 'omnifywp-ecommerce'); ?></span>
				<span><?php esc_html_e('Invoices', 'omnifywp-ecommerce'); ?></span>
				<span><?php esc_html_e('Orders and refunds', 'omnifywp-ecommerce'); ?></span>
				<span><?php esc_html_e('Wishlist and profile', 'omnifywp-ecommerce'); ?></span>
			</div>
		</div>

		<div class="omnify-portal-login-card">
			<div class="omnify-portal-login-card__header">
				<div class="omnify-lock-icon"><span><?php esc_html_e('Account', 'omnifywp-ecommerce'); ?></span></div>
				<h3><?php esc_html_e('Sign in to your account', 'omnifywp-ecommerce'); ?></h3>
				<p><?php esc_html_e('Sign in to view downloads, invoices, orders, and saved profile details.', 'omnifywp-ecommerce'); ?></p>
			</div>

			<div class="omnify-portal-login-card__form">
				<?php
				wp_login_form([
					'redirect'       => $omnify_portal_redirect_to,
					'label_username' => __('Username or Email Address', 'omnifywp-ecommerce'),
					'label_password' => __('Password', 'omnifywp-ecommerce'),
					'label_remember' => __('Remember Me', 'omnifywp-ecommerce'),
					'label_log_in'   => __('Sign In', 'omnifywp-ecommerce'),
					'remember'       => true,
				]);
				?>
				<p class="omnify-auth-switch" style="margin-top: 16px; font-size: 13px;">
					<a href="<?php echo esc_url(wp_lostpassword_url($omnify_portal_redirect_to)); ?>"><?php esc_html_e('Forgot password?', 'omnifywp-ecommerce'); ?></a>
					<?php if (get_option('users_can_register')) : ?>
						<span>&middot;</span>
						<a href="<?php echo esc_url(wp_registration_url()); ?>"><?php esc_html_e('Create account', 'omnifywp-ecommerce'); ?></a>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</div>
<?php else : ?>
	<?php
	$omnify_customer            = is_array($omnify_customer) ? $omnify_customer : [];
	$omnify_products_accessed   = is_array($omnify_products_accessed) ? $omnify_products_accessed : [];
	$omnify_files               = is_array($omnify_files) ? $omnify_files : [];
	$omnify_orders              = is_array($omnify_orders) ? $omnify_orders : [];
	$omnify_wishlist_products   = is_array($omnify_wishlist_products) ? $omnify_wishlist_products : [];
	$omnify_download_history    = is_array($omnify_download_history) ? $omnify_download_history : [];
	$omnify_portal_wp_user      = wp_get_current_user();
	$omnify_portal_name         = trim((string) (($omnify_customer['first_name'] ?? '') . ' ' . ($omnify_customer['last_name'] ?? '')));
	$omnify_portal_name         = '' !== $omnify_portal_name ? $omnify_portal_name : (string) ($omnify_customer['name'] ?? $omnify_portal_wp_user->display_name);
	$omnify_portal_email        = (string) ($omnify_customer['email'] ?? $omnify_portal_wp_user->user_email);
	$omnify_portal_initial      = strtoupper(substr($omnify_portal_name ?: $omnify_portal_email, 0, 1));
	$omnify_portal_price        = is_callable($omnify_format_price) ? $omnify_format_price : static fn($omnify_amount) => number_format_i18n((float) $omnify_amount, 2);
	$omnify_portal_addresses    = get_user_meta(get_current_user_id(), '_omnify_shipping_addresses', true);
	$omnify_portal_addresses    = is_array($omnify_portal_addresses) ? $omnify_portal_addresses : [];
	$omnify_portal_total_spent  = 0.0;
	foreach ($omnify_orders as $omnify_portal_order_total_row) {
		$omnify_portal_total_spent += (float) ($omnify_portal_order_total_row['total'] ?? 0);
	}
	$omnify_product_files_for = static function($omnify_product_id) use ($omnify_files) {
		$omnify_product_files = [];
		foreach ($omnify_files as $omnify_file_row) {
			if ((int) ($omnify_file_row['product_id'] ?? 0) === (int) $omnify_product_id) {
				$omnify_product_files[] = $omnify_file_row;
			}
		}
		return $omnify_product_files;
	};
	$omnify_render_country_options = static function($omnify_selected_country) use ($omnify_countries) {
		foreach ((array) $omnify_countries as $omnify_country_code => $omnify_country_name) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr($omnify_country_code),
				selected((string) $omnify_selected_country, (string) $omnify_country_code, false),
				esc_html($omnify_country_name)
			);
		}
	};
	?>

	<div class="omnify-portal-header">
		<div class="omnify-portal-profile">
			<div class="omnify-portal-profile__avatar"><span><?php echo esc_html($omnify_portal_initial); ?></span></div>
			<div class="omnify-portal-profile__details">
				<h3><?php echo esc_html($omnify_portal_name); ?></h3>
				<span class="omnify-portal-profile__meta"><?php echo esc_html($omnify_portal_email); ?></span>
			</div>
		</div>
		<a class="omnify-btn omnify-btn--secondary" href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>"><?php esc_html_e('Sign out', 'omnifywp-ecommerce'); ?></a>
	</div>

	<div class="omnify-portal-stats">
		<div class="omnify-portal-stat"><span class="omnify-portal-stat__label"><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></span><strong class="omnify-portal-stat__value"><?php echo esc_html((string) count($omnify_orders)); ?></strong></div>
		<div class="omnify-portal-stat"><span class="omnify-portal-stat__label"><?php esc_html_e('Downloads', 'omnifywp-ecommerce'); ?></span><strong class="omnify-portal-stat__value"><?php echo esc_html((string) count($omnify_files)); ?></strong></div>
		<div class="omnify-portal-stat"><span class="omnify-portal-stat__label"><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></span><strong class="omnify-portal-stat__value"><?php echo esc_html((string) count($omnify_products_accessed)); ?></strong></div>
		<div class="omnify-portal-stat"><span class="omnify-portal-stat__label"><?php esc_html_e('Spent', 'omnifywp-ecommerce'); ?></span><strong class="omnify-portal-stat__value"><?php echo esc_html($omnify_portal_price($omnify_portal_total_spent)); ?></strong></div>
	</div>

	<?php if ($omnify_profile_saved) : ?>
		<div class="omnify-portal-notice omnify-portal-notice--success"><?php esc_html_e('Profile settings updated successfully.', 'omnifywp-ecommerce'); ?></div>
	<?php endif; ?>
	<?php if ($omnify_password_success) : ?>
		<div class="omnify-portal-notice omnify-portal-notice--success"><?php esc_html_e('Password changed successfully.', 'omnifywp-ecommerce'); ?></div>
	<?php endif; ?>
	<?php if ($omnify_password_error) : ?>
		<div class="omnify-portal-notice omnify-portal-notice--error"><?php echo esc_html($omnify_password_error); ?></div>
	<?php endif; ?>
	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$omnify_message = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
	if ('refund_requested' === $omnify_message) :
	?>
		<div class="omnify-portal-notice omnify-portal-notice--success"><?php esc_html_e('Your refund request has been submitted.', 'omnifywp-ecommerce'); ?></div>
	<?php endif; ?>

	<div class="omnify-portal-overview">
		<div class="omnify-portal-overview-card">
			<span class="omnify-portal-overview-card__label"><?php esc_html_e('Latest order', 'omnifywp-ecommerce'); ?></span>
			<strong><?php echo ! empty($omnify_orders[0]['order_number']) ? esc_html((string) $omnify_orders[0]['order_number']) : esc_html__('No orders yet', 'omnifywp-ecommerce'); ?></strong>
			<p><?php esc_html_e('Review purchases and receipts from Order History.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-portal-overview-card">
			<span class="omnify-portal-overview-card__label"><?php esc_html_e('Saved addresses', 'omnifywp-ecommerce'); ?></span>
			<strong><?php echo esc_html((string) count($omnify_portal_addresses)); ?></strong>
			<p><?php esc_html_e('Keep checkout faster with reusable shipping details.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-portal-overview-card">
			<span class="omnify-portal-overview-card__label"><?php esc_html_e('Invoices', 'omnifywp-ecommerce'); ?></span>
			<strong><?php echo esc_html((string) count($omnify_orders)); ?></strong>
			<p><?php esc_html_e('Download invoice documents for every order from the Invoices tab.', 'omnifywp-ecommerce'); ?></p>
		</div>
		<div class="omnify-portal-overview-card">
			<span class="omnify-portal-overview-card__label"><?php esc_html_e('Wishlist', 'omnifywp-ecommerce'); ?></span>
			<strong><?php echo esc_html((string) count($omnify_wishlist_products)); ?></strong>
			<p><?php esc_html_e('Return to products you saved for later.', 'omnifywp-ecommerce'); ?></p>
		</div>
	</div>

	<div class="omnify-portal-tabs">
		<div class="omnify-portal-tabs__nav">
			<button class="omnify-tab-btn active" type="button" data-tab="products"><?php esc_html_e('My Products', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="downloads"><?php esc_html_e('Downloads', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="orders"><?php esc_html_e('Order History', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="invoices"><?php esc_html_e('Invoices', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="wishlist"><?php esc_html_e('Wishlist', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="history"><?php esc_html_e('Download Log', 'omnifywp-ecommerce'); ?></button>
			<button class="omnify-tab-btn" type="button" data-tab="settings"><?php esc_html_e('My Account', 'omnifywp-ecommerce'); ?></button>
		</div>

		<div class="omnify-portal-tabs__content">
			<div class="omnify-tab-pane active" id="omnify-pane-products">
				<?php if (empty($omnify_products_accessed)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Products', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('No products yet', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Products you purchase or receive access to will appear here.', 'omnifywp-ecommerce'); ?></p>
						<a class="omnify-btn omnify-btn--primary" href="<?php echo esc_url($omnify_storefront_url); ?>"><?php esc_html_e('Browse store', 'omnifywp-ecommerce'); ?></a>
					</div>
				<?php else : ?>
					<div class="omnify-portal-products-grid">
						<?php foreach ($omnify_products_accessed as $omnify_portal_product) : ?>
							<?php
							$omnify_portal_product_id    = (int) ($omnify_portal_product['id'] ?? 0);
							$omnify_portal_product_files = $omnify_product_files_for($omnify_portal_product_id);
							$omnify_portal_product_url   = add_query_arg('product_id', $omnify_portal_product_id, $omnify_storefront_url);
							?>
							<div class="omnify-product-card">
								<div class="omnify-product-card__content">
									<span class="omnify-product-card__type"><?php echo esc_html(ucfirst((string) ($omnify_portal_product['type'] ?? 'product'))); ?></span>
									<h3><?php echo esc_html((string) ($omnify_portal_product['name'] ?? __('Product', 'omnifywp-ecommerce'))); ?></h3>
									<?php if (! empty($omnify_portal_product['short_description'])) : ?>
										<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) $omnify_portal_product['short_description']), 22)); ?></p>
									<?php endif; ?>
									<div class="omnify-product-card__actions">
										<a class="omnify-btn omnify-btn--secondary omnify-btn--sm" href="<?php echo esc_url($omnify_portal_product_url); ?>"><?php esc_html_e('View product', 'omnifywp-ecommerce'); ?></a>
										<?php if (! empty($omnify_portal_product_files)) : ?>
											<button class="omnify-btn omnify-btn--ghost omnify-btn--sm" type="button" data-omnify-open-tab="downloads"><?php esc_html_e('Files', 'omnifywp-ecommerce'); ?></button>
										<?php endif; ?>
									</div>
								</div>
								<?php if (! empty($omnify_portal_product_files)) : ?>
									<div class="omnify-portal-product-files">
										<?php foreach (array_slice($omnify_portal_product_files, 0, 3) as $omnify_portal_product_file) : ?>
											<?php $omnify_portal_file_ext = strtoupper(pathinfo((string) ($omnify_portal_product_file['file_name'] ?? 'FILE'), PATHINFO_EXTENSION) ?: 'FILE'); ?>
											<div class="omnify-portal-file-row">
												<span class="omnify-portal-file-row__icon"><?php echo esc_html($omnify_portal_file_ext); ?></span>
												<div class="omnify-portal-file-row__info">
													<strong><?php echo esc_html((string) ($omnify_portal_product_file['file_name'] ?? __('Download file', 'omnifywp-ecommerce'))); ?></strong>
													<span><?php echo esc_html((string) ($omnify_portal_product_file['version'] ?? '')); ?></span>
												</div>
												<a class="omnify-btn omnify-btn--primary omnify-btn--sm" href="<?php echo esc_url((string) ($omnify_portal_product_file['download_url'] ?? '#')); ?>" download><?php esc_html_e('Download', 'omnifywp-ecommerce'); ?></a>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-downloads" style="display:none;">
				<?php if (empty($omnify_files)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Files', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('No downloads available', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Downloadable files from your purchases will show here.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-portal-downloads-grid">
						<?php foreach ($omnify_files as $omnify_portal_file) : ?>
							<?php $omnify_portal_file_ext = strtoupper(pathinfo((string) ($omnify_portal_file['file_name'] ?? 'FILE'), PATHINFO_EXTENSION) ?: 'FILE'); ?>
							<div class="omnify-portal-download-card">
								<div class="omnify-portal-download-card__icon"><?php echo esc_html($omnify_portal_file_ext); ?></div>
								<div class="omnify-portal-download-card__details">
									<span class="omnify-portal-download-card__product"><?php echo esc_html((string) ($omnify_portal_file['product_name'] ?? __('Product file', 'omnifywp-ecommerce'))); ?></span>
									<strong class="omnify-portal-download-card__name"><?php echo esc_html((string) ($omnify_portal_file['file_name'] ?? __('Download file', 'omnifywp-ecommerce'))); ?></strong>
									<span class="omnify-portal-download-card__meta"><?php echo esc_html((string) ($omnify_portal_file['version'] ?? '')); ?></span>
								</div>
								<a class="omnify-btn omnify-btn--primary omnify-btn--sm" href="<?php echo esc_url((string) ($omnify_portal_file['download_url'] ?? '#')); ?>" download><?php esc_html_e('Download', 'omnifywp-ecommerce'); ?></a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-orders" style="display:none;">
				<?php if (empty($omnify_orders)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('No orders yet', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Your completed and pending orders will appear here.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-portal-orders-list">
						<?php foreach ($omnify_orders as $omnify_portal_order) : ?>
							<?php
							$omnify_portal_order_id       = (int) ($omnify_portal_order['id'] ?? 0);
							$omnify_portal_order_status   = (string) ($omnify_portal_order['status'] ?? 'pending');
							$omnify_portal_order_items    = is_array($omnify_portal_order['items'] ?? null) ? $omnify_portal_order['items'] : [];
							$omnify_portal_invoice_url    = wp_nonce_url(admin_url('admin-post.php?action=omnify_order_document&order_id=' . $omnify_portal_order_id . '&document_type=invoice'), 'omnify_order_document_' . $omnify_portal_order_id);
							$omnify_portal_refund_allowed = is_object($this) && method_exists($this, 'order_is_refund_eligible') ? $this->order_is_refund_eligible($omnify_portal_order, (array) $omnify_settings) : false;
							?>
							<div class="omnify-portal-order-card">
								<div class="omnify-portal-order-card__header">
									<div>
										<strong class="omnify-portal-order-card__id"><?php echo esc_html(sprintf(__('Order %s', 'omnifywp-ecommerce'), (string) ($omnify_portal_order['order_number'] ?? ('#' . $omnify_portal_order_id)))); ?></strong>
										<span class="omnify-portal-order-card__date"><?php echo ! empty($omnify_portal_order['created_at']) ? esc_html(date_i18n(get_option('date_format'), strtotime((string) $omnify_portal_order['created_at']))) : ''; ?></span>
									</div>
									<div class="omnify-portal-order-card__actions">
										<span class="omnify-status-pill"><?php echo esc_html(ucwords(str_replace('_', ' ', $omnify_portal_order_status))); ?></span>
										<a class="omnify-btn omnify-btn--secondary omnify-btn--sm" href="<?php echo esc_url($omnify_portal_invoice_url); ?>" download><?php esc_html_e('Download invoice', 'omnifywp-ecommerce'); ?></a>
										<button class="omnify-btn omnify-btn--ghost omnify-btn--sm omnify-print-receipt-btn" type="button" data-order-id="<?php echo esc_attr((string) $omnify_portal_order_id); ?>"><?php esc_html_e('Receipt', 'omnifywp-ecommerce'); ?></button>
									</div>
								</div>
								<div class="omnify-portal-order-card__items">
									<?php foreach ($omnify_portal_order_items as $omnify_portal_order_item) : ?>
										<div class="omnify-portal-order-item">
											<span class="omnify-portal-order-item__name"><?php echo esc_html((string) ($omnify_portal_order_item['product_name'] ?? __('Product', 'omnifywp-ecommerce'))); ?></span>
											<span class="omnify-portal-order-item__qty"><?php echo esc_html('x' . (int) ($omnify_portal_order_item['quantity'] ?? 1)); ?></span>
											<span class="omnify-portal-order-item__price"><?php echo esc_html($omnify_portal_price((float) ($omnify_portal_order_item['line_total'] ?? $omnify_portal_order_item['total'] ?? 0))); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
								<div class="omnify-portal-order-card__footer">
									<strong><?php echo esc_html($omnify_portal_price((float) ($omnify_portal_order['total'] ?? 0))); ?></strong>
									<span><?php echo esc_html((string) ($omnify_portal_order['payment_method'] ?? '')); ?></span>
								</div>
								<?php if ($omnify_portal_refund_allowed) : ?>
									<form class="omnify-refund-request-form" method="post">
										<?php wp_nonce_field('omnify_request_refund_' . $omnify_portal_order_id, 'omnify_refund_nonce'); ?>
										<input type="hidden" name="omnify_action" value="omnify_request_refund" />
										<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_portal_order_id); ?>" />
										<textarea name="refund_reason" rows="2" placeholder="<?php esc_attr_e('Reason for refund request', 'omnifywp-ecommerce'); ?>"></textarea>
										<button class="omnify-btn omnify-btn--secondary omnify-btn--sm" type="submit"><?php esc_html_e('Request refund', 'omnifywp-ecommerce'); ?></button>
									</form>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-invoices" style="display:none;">
				<?php if (empty($omnify_orders)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Invoices', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('No invoices yet', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Invoice downloads will appear here after your first order.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-portal-invoices-list">
						<?php foreach ($omnify_orders as $omnify_portal_invoice_order) : ?>
							<?php
							$omnify_portal_invoice_order_id     = (int) ($omnify_portal_invoice_order['id'] ?? 0);
							$omnify_portal_invoice_order_number = (string) ($omnify_portal_invoice_order['order_number'] ?? ('#' . $omnify_portal_invoice_order_id));
							$omnify_portal_invoice_url          = wp_nonce_url(admin_url('admin-post.php?action=omnify_order_document&order_id=' . $omnify_portal_invoice_order_id . '&document_type=invoice'), 'omnify_order_document_' . $omnify_portal_invoice_order_id);
							?>
							<div class="omnify-portal-invoice-card">
								<div class="omnify-portal-invoice-card__badge"><?php esc_html_e('PDF', 'omnifywp-ecommerce'); ?></div>
								<div class="omnify-portal-invoice-card__body">
									<strong><?php echo esc_html(sprintf(__('Invoice for order %s', 'omnifywp-ecommerce'), $omnify_portal_invoice_order_number)); ?></strong>
									<span>
										<?php
										echo ! empty($omnify_portal_invoice_order['created_at'])
											? esc_html(date_i18n(get_option('date_format'), strtotime((string) $omnify_portal_invoice_order['created_at'])))
											: esc_html__('Order document', 'omnifywp-ecommerce');
										?>
									</span>
								</div>
								<div class="omnify-portal-invoice-card__meta">
									<span class="omnify-status-pill"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) ($omnify_portal_invoice_order['status'] ?? 'pending')))); ?></span>
									<strong><?php echo esc_html($omnify_portal_price((float) ($omnify_portal_invoice_order['total'] ?? 0))); ?></strong>
								</div>
								<a class="omnify-btn omnify-btn--primary omnify-btn--sm" href="<?php echo esc_url($omnify_portal_invoice_url); ?>" download><?php esc_html_e('Download invoice', 'omnifywp-ecommerce'); ?></a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-wishlist" style="display:none;">
				<?php if (empty($omnify_wishlist_products)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Saved', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('Your wishlist is empty', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Saved products will appear here for quick access later.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-portal-products-grid omnify-wishlist-grid">
						<?php foreach ($omnify_wishlist_products as $omnify_portal_wish_product) : ?>
							<?php
							$omnify_portal_wish_id  = (int) ($omnify_portal_wish_product['id'] ?? 0);
							$omnify_portal_wish_url = add_query_arg('product_id', $omnify_portal_wish_id, $omnify_storefront_url);
							?>
							<div class="omnify-product-card" data-product-card-id="<?php echo esc_attr((string) $omnify_portal_wish_id); ?>">
								<div class="omnify-product-card__content">
									<span class="omnify-product-card__type"><?php echo esc_html(ucfirst((string) ($omnify_portal_wish_product['type'] ?? 'product'))); ?></span>
									<h3><?php echo esc_html((string) ($omnify_portal_wish_product['name'] ?? __('Product', 'omnifywp-ecommerce'))); ?></h3>
									<div class="omnify-product-card__actions">
										<a class="omnify-btn omnify-btn--secondary omnify-btn--sm" href="<?php echo esc_url($omnify_portal_wish_url); ?>"><?php esc_html_e('View product', 'omnifywp-ecommerce'); ?></a>
										<button class="omnify-wishlist-button omnify-btn omnify-btn--ghost omnify-btn--sm is-wishlisted" type="button" data-product-id="<?php echo esc_attr((string) $omnify_portal_wish_id); ?>" aria-pressed="true">
											<span class="omnify-wishlist-button__text"><?php esc_html_e('Remove', 'omnifywp-ecommerce'); ?></span>
										</button>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-history" style="display:none;">
				<?php if (empty($omnify_download_history)) : ?>
					<div class="omnify-tab-empty">
						<div class="omnify-tab-empty__icon"><?php esc_html_e('Log', 'omnifywp-ecommerce'); ?></div>
						<h3><?php esc_html_e('No download activity yet', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Your recent file download history will appear here.', 'omnifywp-ecommerce'); ?></p>
					</div>
				<?php else : ?>
					<div class="omnify-portal-history-table">
						<table>
							<thead>
								<tr>
									<th><?php esc_html_e('File', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
									<th><?php esc_html_e('Date', 'omnifywp-ecommerce'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($omnify_download_history as $omnify_portal_log) : ?>
									<tr>
										<td><?php echo esc_html((string) ($omnify_portal_log['file_name'] ?? __('File', 'omnifywp-ecommerce'))); ?></td>
										<td><?php echo esc_html((string) ($omnify_portal_log['product_name'] ?? '')); ?></td>
										<td><?php echo ! empty($omnify_portal_log['downloaded_at']) ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime((string) $omnify_portal_log['downloaded_at']))) : ''; ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>

			<div class="omnify-tab-pane" id="omnify-pane-settings" style="display:none;">
				<div class="omnify-account-hero">
					<div>
						<span class="omnify-account-hero__eyebrow"><?php esc_html_e('Account settings', 'omnifywp-ecommerce'); ?></span>
						<h3><?php esc_html_e('Manage profile, security, and addresses', 'omnifywp-ecommerce'); ?></h3>
						<p><?php esc_html_e('Keep your contact details, billing information, shipping addresses, and password up to date.', 'omnifywp-ecommerce'); ?></p>
					</div>
					<div class="omnify-account-hero__meta">
						<span><?php esc_html_e('Signed in as', 'omnifywp-ecommerce'); ?></span>
						<strong><?php echo esc_html($omnify_portal_email); ?></strong>
					</div>
				</div>

				<div class="omnify-portal-account-grid">
					<div class="omnify-portal-section-card omnify-account-card--profile">
						<div class="omnify-account-card__header">
							<div>
								<span><?php esc_html_e('Profile', 'omnifywp-ecommerce'); ?></span>
								<h3><?php esc_html_e('Contact and addresses', 'omnifywp-ecommerce'); ?></h3>
							</div>
							<p><?php esc_html_e('Used on invoices, receipts, and checkout forms.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<form method="post">
							<?php wp_nonce_field('omnify_update_profile', 'omnify_profile_nonce'); ?>
							<input type="hidden" name="omnify_action" value="omnify_update_profile" />
							<div class="omnify-account-form-section">
								<div class="omnify-account-form-section__title"><?php esc_html_e('Personal details', 'omnifywp-ecommerce'); ?></div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('First name', 'omnifywp-ecommerce'); ?></label><input name="first_name" value="<?php echo esc_attr((string) ($omnify_customer['first_name'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Last name', 'omnifywp-ecommerce'); ?></label><input name="last_name" value="<?php echo esc_attr((string) ($omnify_customer['last_name'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></label><input name="phone" value="<?php echo esc_attr((string) ($omnify_customer['phone'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label><input name="company" value="<?php echo esc_attr((string) ($omnify_customer['company'] ?? '')); ?>" /></div>
							</div>
							</div>

							<div class="omnify-account-form-section">
							<h4><?php esc_html_e('Billing address', 'omnifywp-ecommerce'); ?></h4>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('First name', 'omnifywp-ecommerce'); ?></label><input name="billing_first_name" value="<?php echo esc_attr((string) ($omnify_customer['billing_first_name'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Last name', 'omnifywp-ecommerce'); ?></label><input name="billing_last_name" value="<?php echo esc_attr((string) ($omnify_customer['billing_last_name'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></label><input name="billing_phone" value="<?php echo esc_attr((string) ($omnify_customer['billing_phone'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label><input name="billing_company" value="<?php echo esc_attr((string) ($omnify_customer['billing_company'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 1', 'omnifywp-ecommerce'); ?></label><input name="billing_address_1" value="<?php echo esc_attr((string) ($omnify_customer['billing_address_1'] ?? '')); ?>" /></div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 2', 'omnifywp-ecommerce'); ?></label><input name="billing_address_2" value="<?php echo esc_attr((string) ($omnify_customer['billing_address_2'] ?? '')); ?>" /></div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('City', 'omnifywp-ecommerce'); ?></label><input name="billing_city" value="<?php echo esc_attr((string) ($omnify_customer['billing_city'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('State', 'omnifywp-ecommerce'); ?></label><input name="billing_state" value="<?php echo esc_attr((string) ($omnify_customer['billing_state'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Postcode', 'omnifywp-ecommerce'); ?></label><input name="billing_postcode" value="<?php echo esc_attr((string) ($omnify_customer['billing_postcode'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></label><select class="omnify-searchable-select" name="billing_country"><option value=""><?php esc_html_e('Select country', 'omnifywp-ecommerce'); ?></option><?php $omnify_render_country_options((string) ($omnify_customer['billing_country'] ?? '')); ?></select></div>
							</div>
							</div>

							<?php
							$omnify_same_as_billing = get_user_meta(get_current_user_id(), '_omnify_shipping_same_as_billing', true);
							$omnify_same_checked = ($omnify_same_as_billing === '1' || $omnify_same_as_billing === '');
							?>
							<label class="omnify-auth-remember" style="margin: 16px 0; display: flex; align-items: center; gap: 8px;">
								<input class="omnify-checkbox" type="checkbox" name="shipping_same_as_billing" value="1" <?php checked($omnify_same_checked); ?> />
								<span><?php esc_html_e('Use billing address as shipping address', 'omnifywp-ecommerce'); ?></span>
							</label>

							<div class="omnify-account-form-section" id="omnify-portal-shipping-section">
							<h4><?php esc_html_e('Shipping address', 'omnifywp-ecommerce'); ?></h4>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('First name', 'omnifywp-ecommerce'); ?></label><input name="shipping_first_name" value="<?php echo esc_attr((string) ($omnify_customer['shipping_first_name'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Last name', 'omnifywp-ecommerce'); ?></label><input name="shipping_last_name" value="<?php echo esc_attr((string) ($omnify_customer['shipping_last_name'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></label><input name="shipping_phone" value="<?php echo esc_attr((string) ($omnify_customer['shipping_phone'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label><input name="shipping_company" value="<?php echo esc_attr((string) ($omnify_customer['shipping_company'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 1', 'omnifywp-ecommerce'); ?></label><input name="shipping_address_1" value="<?php echo esc_attr((string) ($omnify_customer['shipping_address_1'] ?? '')); ?>" /></div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 2', 'omnifywp-ecommerce'); ?></label><input name="shipping_address_2" value="<?php echo esc_attr((string) ($omnify_customer['shipping_address_2'] ?? '')); ?>" /></div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('City', 'omnifywp-ecommerce'); ?></label><input name="shipping_city" value="<?php echo esc_attr((string) ($omnify_customer['shipping_city'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('State', 'omnifywp-ecommerce'); ?></label><input name="shipping_state" value="<?php echo esc_attr((string) ($omnify_customer['shipping_state'] ?? '')); ?>" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Postcode', 'omnifywp-ecommerce'); ?></label><input name="shipping_postcode" value="<?php echo esc_attr((string) ($omnify_customer['shipping_postcode'] ?? '')); ?>" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></label><select class="omnify-searchable-select" name="shipping_country"><option value=""><?php esc_html_e('Select country', 'omnifywp-ecommerce'); ?></option><?php $omnify_render_country_options((string) ($omnify_customer['shipping_country'] ?? '')); ?></select></div>
							</div>
							</div>
							<div class="omnify-portal-form-actions"><button class="omnify-btn omnify-btn--primary" type="submit"><?php esc_html_e('Save profile', 'omnifywp-ecommerce'); ?></button></div>
						</form>
					</div>

					<div class="omnify-portal-section-card omnify-account-card--security">
						<div class="omnify-account-card__header">
							<div>
								<span><?php esc_html_e('Security', 'omnifywp-ecommerce'); ?></span>
								<h3><?php esc_html_e('Password', 'omnifywp-ecommerce'); ?></h3>
							</div>
							<p><?php esc_html_e('Use a strong password to protect downloads and invoices.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<form method="post">
							<?php wp_nonce_field('omnify_change_password', 'omnify_cpw_nonce'); ?>
							<input type="hidden" name="omnify_action" value="omnify_change_password" />
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Current password', 'omnifywp-ecommerce'); ?></label><input type="password" name="current_password" required /></div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('New password', 'omnifywp-ecommerce'); ?></label><input type="password" name="new_password" required minlength="8" /></div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Confirm password', 'omnifywp-ecommerce'); ?></label><input type="password" name="confirm_password" required minlength="8" /></div>
							<div class="omnify-portal-form-actions"><button class="omnify-btn omnify-btn--primary" type="submit"><?php esc_html_e('Change password', 'omnifywp-ecommerce'); ?></button></div>
						</form>
					</div>
				</div>

				<div class="omnify-portal-section-card omnify-account-card--addresses">
					<div class="omnify-address-form-header">
						<div>
							<h3><?php esc_html_e('Shipping Addresses', 'omnifywp-ecommerce'); ?></h3>
							<span><?php esc_html_e('Save addresses for faster checkout.', 'omnifywp-ecommerce'); ?></span>
						</div>
						<button class="omnify-btn omnify-btn--secondary" type="button" id="omnify-add-new-address-btn"><?php esc_html_e('Add address', 'omnifywp-ecommerce'); ?></button>
					</div>

					<?php if (empty($omnify_portal_addresses)) : ?>
						<p class="omnify-portal-form-hint"><?php esc_html_e('No saved shipping addresses yet.', 'omnifywp-ecommerce'); ?></p>
					<?php else : ?>
						<div class="omnify-portal-address-list">
							<?php foreach ($omnify_portal_addresses as $omnify_portal_address) : ?>
								<?php
								$omnify_portal_address_id = (string) ($omnify_portal_address['id'] ?? '');
								$omnify_portal_address_json = wp_json_encode($omnify_portal_address);
								?>
								<div class="omnify-address-card">
									<div>
										<strong><?php echo esc_html(trim((string) (($omnify_portal_address['first_name'] ?? '') . ' ' . ($omnify_portal_address['last_name'] ?? ''))) ?: __('Shipping address', 'omnifywp-ecommerce')); ?></strong>
										<p>
											<?php echo esc_html((string) ($omnify_portal_address['address_1'] ?? '')); ?><br />
											<?php echo esc_html(trim((string) (($omnify_portal_address['city'] ?? '') . ' ' . ($omnify_portal_address['state'] ?? '') . ' ' . ($omnify_portal_address['postcode'] ?? '')))); ?><br />
											<?php echo esc_html((string) ($omnify_portal_address['country'] ?? '')); ?>
										</p>
										<?php if (! empty($omnify_portal_address['is_default'])) : ?><span class="omnify-status-pill"><?php esc_html_e('Default', 'omnifywp-ecommerce'); ?></span><?php endif; ?>
									</div>
									<div class="omnify-address-card__actions">
										<button class="omnify-btn omnify-btn--ghost omnify-btn--sm omnify-edit-address-btn" type="button" data-address="<?php echo esc_attr((string) $omnify_portal_address_json); ?>"><?php esc_html_e('Edit', 'omnifywp-ecommerce'); ?></button>
										<?php if (empty($omnify_portal_address['is_default'])) : ?>
											<a class="omnify-btn omnify-btn--ghost omnify-btn--sm" href="<?php echo esc_url(wp_nonce_url(add_query_arg(['omnify_action' => 'omnify_default_address', 'address_id' => $omnify_portal_address_id], get_permalink()), 'omnify_default_address_' . $omnify_portal_address_id)); ?>"><?php esc_html_e('Set default', 'omnifywp-ecommerce'); ?></a>
										<?php endif; ?>
										<a class="omnify-btn omnify-btn--ghost omnify-btn--sm" href="<?php echo esc_url(wp_nonce_url(add_query_arg(['omnify_action' => 'omnify_delete_address', 'address_id' => $omnify_portal_address_id], get_permalink()), 'omnify_delete_address_' . $omnify_portal_address_id)); ?>"><?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?></a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="omnify-address-form-panel" id="omnify-address-form-wrapper" style="display:none;">
						<div class="omnify-address-form-header">
							<h4 id="omnify-address-form-title" data-label-edit="<?php esc_attr_e('Edit shipping address', 'omnifywp-ecommerce'); ?>" data-label-add="<?php esc_attr_e('Add shipping address', 'omnifywp-ecommerce'); ?>"><?php esc_html_e('Add shipping address', 'omnifywp-ecommerce'); ?></h4>
							<button class="omnify-btn omnify-btn--ghost omnify-btn--sm" type="button" id="omnify-cancel-address-btn"><?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?></button>
						</div>
						<form method="post" id="omnify-address-form">
							<?php wp_nonce_field('omnify_manage_addresses', 'omnify_address_nonce'); ?>
							<input type="hidden" name="omnify_action" id="omnify-address-action-input" value="omnify_add_address" />
							<input type="hidden" name="address_id" id="omnify-address-id-input" value="" />
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('First name', 'omnifywp-ecommerce'); ?></label><input name="shipping_first_name" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Last name', 'omnifywp-ecommerce'); ?></label><input name="shipping_last_name" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></label><input name="shipping_phone" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label><input name="shipping_company" /></div>
							</div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 1', 'omnifywp-ecommerce'); ?></label><input name="shipping_address_1" /></div>
							<div class="omnify-portal-form-group"><label><?php esc_html_e('Address line 2', 'omnifywp-ecommerce'); ?></label><input name="shipping_address_2" /></div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('City', 'omnifywp-ecommerce'); ?></label><input name="shipping_city" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('State', 'omnifywp-ecommerce'); ?></label><input name="shipping_state" /></div>
							</div>
							<div class="omnify-portal-form-row">
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Postcode', 'omnifywp-ecommerce'); ?></label><input name="shipping_postcode" /></div>
								<div class="omnify-portal-form-group"><label><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></label><select class="omnify-searchable-select" name="shipping_country"><option value=""><?php esc_html_e('Select country', 'omnifywp-ecommerce'); ?></option><?php $omnify_render_country_options(''); ?></select></div>
							</div>
							<label class="omnify-auth-remember omnify-address-default-row">
								<input class="omnify-checkbox" type="checkbox" name="is_default" value="1" />
								<span><?php esc_html_e('Make this the default shipping address', 'omnifywp-ecommerce'); ?></span>
							</label>
							<div class="omnify-address-form-actions">
								<button class="omnify-btn omnify-btn--primary" type="submit"><?php esc_html_e('Save address', 'omnifywp-ecommerce'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
	</div><!-- .omnify-portal-wrapper -->
</div><!-- .omnify-storefront-wrapper -->
