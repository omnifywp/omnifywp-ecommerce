<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_a = $omnify_template_vars['omnify_a'] ?? null;
$omnify_access = $omnify_template_vars['omnify_access'] ?? null;
$omnify_act = $omnify_template_vars['omnify_act'] ?? null;
$omnify_action = $omnify_template_vars['omnify_action'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_activities = $omnify_template_vars['omnify_activities'] ?? null;
$omnify_b = $omnify_template_vars['omnify_b'] ?? null;
$omnify_c_chips = $omnify_template_vars['omnify_c_chips'] ?? null;
$omnify_chip = $omnify_template_vars['omnify_chip'] ?? null;
$omnify_countries = $omnify_template_vars['omnify_countries'] ?? null;
$omnify_country_code = $omnify_template_vars['omnify_country_code'] ?? null;
$omnify_country_name = $omnify_template_vars['omnify_country_name'] ?? null;
$omnify_cust = $omnify_template_vars['omnify_cust'] ?? null;
$omnify_customer = $omnify_template_vars['omnify_customer'] ?? null;
$omnify_customer_access_repo = $omnify_template_vars['omnify_customer_access_repo'] ?? null;
$omnify_customer_id = $omnify_template_vars['omnify_customer_id'] ?? null;
$omnify_customer_repo = $omnify_template_vars['omnify_customer_repo'] ?? null;
$omnify_customers = $omnify_template_vars['omnify_customers'] ?? null;
$omnify_date_label = $omnify_template_vars['omnify_date_label'] ?? null;
$omnify_granted_products = $omnify_template_vars['omnify_granted_products'] ?? null;
$omnify_is_trashed = $omnify_template_vars['omnify_is_trashed'] ?? null;
$omnify_item = $omnify_template_vars['omnify_item'] ?? null;
$omnify_n = $omnify_template_vars['omnify_n'] ?? null;
$omnify_notes = $omnify_template_vars['omnify_notes'] ?? null;
$omnify_prod = $omnify_template_vars['omnify_prod'] ?? null;
$omnify_prod_details = $omnify_template_vars['omnify_prod_details'] ?? null;
$omnify_product_repo = $omnify_template_vars['omnify_product_repo'] ?? null;
$omnify_products = $omnify_template_vars['omnify_products'] ?? null;
$omnify_symbol = $omnify_template_vars['omnify_symbol'] ?? null;
$omnify_timeline = $omnify_template_vars['omnify_timeline'] ?? null;


/**
 * Admin Customers Template
 *
 * @package Omnify
 */
$omnify_action      = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
$omnify_customer_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;

$omnify_customer = null;
if ($omnify_customer_id && ($omnify_action === 'edit' || $omnify_action === 'update')) {
	$omnify_customer = $omnify_customer_repo->find($omnify_customer_id);
}
$omnify_countries = \Omnify\eCommerce\Support\Omnify_Locations::countries();
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'customers'; ?>
	<div class="omnify-header-row">
		<div class="omnify-header-row__title">
			<?php if ($omnify_action === 'new') : ?>
				<h1><?php esc_html_e('Add Customer', 'omnifywp-ecommerce'); ?></h1>
				<p><?php esc_html_e('Create a customer account manually.', 'omnifywp-ecommerce'); ?></p>
			<?php elseif ($omnify_action === 'edit' && $omnify_customer) : ?>
				<h1><?php
				// translators: %s: placeholder value. echo esc_html(sprintf(__('Customer Profile: %s', 'omnifywp-ecommerce'), $customer['name'] ?: $customer['email'])); ?></h1>
				<p><?php
				// translators: %s: placeholder value. echo esc_html(sprintf(__('Manage details, notes, and product access for %s.', 'omnifywp-ecommerce'), $customer['email'])); ?></p>
			<?php else : ?>
				<h1><?php esc_html_e('Customers', 'omnifywp-ecommerce'); ?></h1>
				<p><?php esc_html_e('Manage your store customers and their digital access keys.', 'omnifywp-ecommerce'); ?></p>
			<?php endif; ?>
		</div>
		<div class="omnify-header-row__actions">
			<?php if ($omnify_action === 'new' || ($omnify_action === 'edit' && $omnify_customer)) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers')); ?>" class="omnify-button omnify-button--secondary">
					<?php esc_html_e('Back to Customers', 'omnifywp-ecommerce'); ?>
				</a>
			<?php elseif (empty($omnify_action)) : ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=new')); ?>" class="omnify-button omnify-button--primary">
					<span class="dashicons dashicons-plus" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle; margin-right: 4px;"></span>
					<?php esc_html_e('Create Customer', 'omnifywp-ecommerce'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<?php if ($omnify_action === 'new') : ?>
		<!-- Create Form -->
		<div class="omnify-card">
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('omnify_save_customer', 'omnify_customer_nonce'); ?>
				<input type="hidden" name="action" value="omnify_save_customer" />

				<div class="omnify-form-grid" style="max-width: 100%;">
					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="first_name"><?php esc_html_e('First Name', 'omnifywp-ecommerce'); ?></label>
							<input type="text" id="first_name" name="first_name" placeholder="John" />
						</div>

						<div class="omnify-form-group">
							<label for="last_name"><?php esc_html_e('Last Name', 'omnifywp-ecommerce'); ?></label>
							<input type="text" id="last_name" name="last_name" placeholder="Doe" />
						</div>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="email"><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?> *</label>
							<input type="email" id="email" name="email" required placeholder="john.doe@example.com" />
						</div>

						<div class="omnify-form-group">
							<label for="status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
							<select id="status" name="status">
								<option value="active"><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></option>
								<option value="inactive"><?php esc_html_e('Inactive', 'omnifywp-ecommerce'); ?></option>
							</select>
						</div>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="phone"><?php esc_html_e('Phone Number', 'omnifywp-ecommerce'); ?></label>
							<input type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000" />
						</div>

						<div class="omnify-form-group">
							<label for="company"><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label>
							<input type="text" id="company" name="company" placeholder="Acme Corp" />
						</div>
					</div>

					<div class="omnify-form-group" style="max-width: 50%;">
						<label for="country"><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></label>
						<select id="country" name="country" class="omnify-searchable-select" data-placeholder="<?php esc_attr_e('Search country', 'omnifywp-ecommerce'); ?>">
							<option value=""><?php esc_html_e('Select country', 'omnifywp-ecommerce'); ?></option>
							<?php foreach ($omnify_countries as $omnify_country_code => $omnify_country_name) : ?>
								<option value="<?php echo esc_attr($omnify_country_code); ?>"><?php echo esc_html($omnify_country_name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="omnify-form-group">
						<label class="omnify-toggle">
							<input  type="checkbox" name="tax_exempt" value="1" />
							<span class="omnify-toggle__label"><?php esc_html_e('Customer is tax exempt', 'omnifywp-ecommerce'); ?></span>
						</label>
						<span class="help-text"><?php esc_html_e('Checkout will skip tax for this customer when they use this email/account.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-button-group" style="margin-top: 20px;">
						<button type="submit" class="omnify-button omnify-button--primary">
							<?php esc_html_e('Save Customer', 'omnifywp-ecommerce'); ?>
						</button>
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers')); ?>" class="omnify-button omnify-button--secondary">
							<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>
			</form>
		</div>

	<?php elseif ($omnify_action === 'edit' && $omnify_customer) : ?>
		<!-- Detail / Edit View with Layout Columns -->
		<div class="omnify-layout-grid">
			<!-- Left Column: Edit Form + Activity / Notes -->
			<div>
				<!-- Edit Form Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Customer Information', 'omnifywp-ecommerce'); ?></h2>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_save_customer', 'omnify_customer_nonce'); ?>
						<input type="hidden" name="action" value="omnify_save_customer" />
						<input type="hidden" name="id" value="<?php echo esc_attr($omnify_customer['id']); ?>" />

						<div class="omnify-form-grid" style="max-width: 100%;">
							<div class="omnify-form-row">
								<div class="omnify-form-group">
									<label for="first_name"><?php esc_html_e('First Name', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($omnify_customer['first_name']); ?>" />
								</div>

								<div class="omnify-form-group">
									<label for="last_name"><?php esc_html_e('Last Name', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($omnify_customer['last_name']); ?>" />
								</div>
							</div>

							<div class="omnify-form-row">
								<div class="omnify-form-group">
									<label for="email"><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?> *</label>
									<input type="email" id="email" name="email" value="<?php echo esc_attr($omnify_customer['email']); ?>" required />
								</div>

								<div class="omnify-form-group">
									<label for="status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
									<select id="status" name="status">
										<option value="active" <?php selected($omnify_customer['status'], 'active'); ?>><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></option>
										<option value="inactive" <?php selected($omnify_customer['status'], 'inactive'); ?>><?php esc_html_e('Inactive', 'omnifywp-ecommerce'); ?></option>
									</select>
								</div>
							</div>

							<div class="omnify-form-row">
								<div class="omnify-form-group">
									<label for="phone"><?php esc_html_e('Phone Number', 'omnifywp-ecommerce'); ?></label>
									<input type="tel" id="phone" name="phone" value="<?php echo esc_attr($omnify_customer['phone']); ?>" />
								</div>

								<div class="omnify-form-group">
									<label for="company"><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="company" name="company" value="<?php echo esc_attr($omnify_customer['company']); ?>" />
								</div>
							</div>

							<div class="omnify-form-group" style="max-width: 50%;">
								<label for="country"><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></label>
								<select id="country" name="country" class="omnify-searchable-select" data-placeholder="<?php esc_attr_e('Search country', 'omnifywp-ecommerce'); ?>">
									<option value=""><?php esc_html_e('Select country', 'omnifywp-ecommerce'); ?></option>
									<?php foreach ($omnify_countries as $omnify_country_code => $omnify_country_name) : ?>
										<option value="<?php echo esc_attr($omnify_country_code); ?>" <?php selected($omnify_customer['country'], $omnify_country_code); ?>><?php echo esc_html($omnify_country_name); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="omnify-form-group">
								<label class="omnify-toggle">
									<input  type="checkbox" name="tax_exempt" value="1" <?php checked(! empty($omnify_customer['tax_exempt'])); ?> />
									<span class="omnify-toggle__label"><?php esc_html_e('Customer is tax exempt', 'omnifywp-ecommerce'); ?></span>
								</label>
								<span class="help-text"><?php esc_html_e('Checkout will skip tax for this customer when they use this email/account.', 'omnifywp-ecommerce'); ?></span>
							</div>

							<div class="omnify-button-group" style="margin-top: 20px;">
								<button type="submit" class="omnify-button omnify-button--primary">
									<?php esc_html_e('Update Details', 'omnifywp-ecommerce'); ?>
								</button>
							</div>
						</div>
					</form>
				</div>

				<!-- Customer Timeline & Notes Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Timeline & Notes', 'omnifywp-ecommerce'); ?></h2>
					
					<!-- Add Note Form -->
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 24px;">
						<?php wp_nonce_field('omnify_add_customer_note', 'omnify_note_nonce'); ?>
						<input type="hidden" name="action" value="omnify_add_customer_note" />
						<input type="hidden" name="customer_id" value="<?php echo esc_attr($omnify_customer['id']); ?>" />

						<div class="omnify-form-group">
							<textarea name="note" placeholder="Write a note about this customer..." required style="width: 100%; min-height: 80px;"></textarea>
						</div>
						<div class="omnify-button-group">
							<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm">
								<?php esc_html_e('Add Note', 'omnifywp-ecommerce'); ?>
							</button>
						</div>
					</form>

					<!-- Combine Notes & Activities into one chronological timeline -->
					<input type="text" id="omnify-timeline-search" placeholder="<?php esc_attr_e('Search notes & activities...', 'omnifywp-ecommerce'); ?>" style="width:100%; margin-bottom:12px; padding:6px 8px; border:1px solid var(--omnify-gray-300); border-radius:4px; font-size:12px;" />
					<?php
					$omnify_notes = $omnify_customer_repo->notes((int) $omnify_customer['id']);
					$omnify_activities = $omnify_customer_repo->activity((int) $omnify_customer['id']);

					$omnify_timeline = [];
					foreach ($omnify_notes as $omnify_n) {
						$omnify_timeline[] = [
							'time'    => strtotime($omnify_n['created_at']),
							'date'    => $omnify_n['created_at'],
							'type'    => 'note',
							'content' => $omnify_n['note'],
							'id'      => $omnify_n['id'],
							'icon' => 'dashicons-location'
						];
					}
					foreach ($omnify_activities as $omnify_act) {
						$omnify_timeline[] = [
							'time'    => strtotime($omnify_act['created_at']),
							'date'    => $omnify_act['created_at'],
							'type'    => 'activity',
							'content' => $omnify_act['description'],
							'id'      => $omnify_act['id'],
							'icon' => '⚡'
						];
					}

					// Sort chronological descending
					usort($omnify_timeline, function($omnify_a, $omnify_b) {
						return $omnify_b['time'] <=> $omnify_a['time'];
					});
					?>

					<?php if (empty($omnify_timeline)) : ?>
						<p style="color: var(--omnify-gray-600); font-style: italic; font-size: 13px;"><?php esc_html_e('No timeline history found.', 'omnifywp-ecommerce'); ?></p>
					<?php else : ?>
						<div class="omnify-timeline">
							<?php foreach ($omnify_timeline as $omnify_item) : ?>
								<div class="omnify-timeline-item" style="border-bottom: 1px solid var(--omnify-gray-100); padding-bottom: 12px; margin-bottom: 4px;">
									<span class="omnify-timeline-item__bullet"><?php echo esc_html($omnify_item['icon']); ?></span>
									<div class="omnify-timeline-item__content">
										<p style="margin: 0; font-size: 13px; color: var(--omnify-dark);">
											<?php if ($omnify_item['type'] === 'note') : ?>
												<strong><?php esc_html_e('Note added:', 'omnifywp-ecommerce'); ?></strong> 
												<?php echo wp_kses_post($omnify_item['content']); ?>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_customer_note&customer_id=' . $omnify_customer['id'] . '&note_id=' . $omnify_item['id']), 'omnify_delete_customer_note_' . $omnify_item['id'])); ?>" class="js-confirm-delete" style="color: var(--omnify-danger); font-size: 11px; margin-left: 8px; text-decoration: none;">
													[<?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?>]
												</a>
											<?php else : ?>
												<?php echo esc_html($omnify_item['content']); ?>
											<?php endif; ?>
										</p>
										<span class="omnify-timeline-item__date">
											<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $omnify_item['time'])); ?>
										</span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Right Column: Granted Access Keys & Grant Access Form -->
			<div>
				<!-- Granted Access Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Granted Product Access', 'omnifywp-ecommerce'); ?></h2>
					<?php
					$omnify_granted_products = $omnify_customer_access_repo->for_customer((int) $omnify_customer['id']);
					?>
					<?php if (empty($omnify_granted_products)) : ?>
						<p style="color: var(--omnify-gray-600); font-size: 13px; font-style: italic; margin-bottom: 20px;">
							<?php esc_html_e('This customer does not have access to any products yet.', 'omnifywp-ecommerce'); ?>
						</p>
					<?php else : ?>
						<ul style="list-style: none; padding: 0; margin: 0 0 20px 0; display: flex; flex-direction: column; gap: 12px;">
							<?php foreach ($omnify_granted_products as $omnify_access) : 
								$omnify_prod_details = $omnify_product_repo->find((int) $omnify_access['product_id']);
								if (!$omnify_prod_details) continue;
							?>
								<li style="border-bottom: 1px solid var(--omnify-gray-200); padding-bottom: 8px; display: flex; justify-content: space-between; align-items: flex-start;">
									<div>
										<strong style="font-size: 13px; color: var(--omnify-dark);"><?php echo esc_html($omnify_prod_details['name']); ?></strong>
										<div style="font-size: 11px; color: var(--omnify-gray-600); margin-top: 2px;">
											<?php if ($omnify_access['status'] === 'revoked') : ?>
												<span class="omnify-status omnify-status--revoked" style="font-size: 9px; padding: 0px 4px;"><?php esc_html_e('Revoked', 'omnifywp-ecommerce'); ?></span>
											<?php elseif ($omnify_access['is_expired']) : ?>
												<span class="omnify-status omnify-status--inactive" style="font-size: 9px; padding: 0px 4px;"><?php esc_html_e('Expired', 'omnifywp-ecommerce'); ?></span>
											<?php else : ?>
												<span class="omnify-status omnify-status--active" style="font-size: 9px; padding: 0px 4px;"><?php esc_html_e('Active Access', 'omnifywp-ecommerce'); ?></span>
											<?php endif; ?>
											
											<?php if ($omnify_access['expires_at']) : ?>
												<br/>
												<span style="font-size: 10px;"><?php
												// translators: %s: placeholder value. echo esc_html(sprintf(__('Expires: %s', 'omnifywp-ecommerce'), date_i18n(get_option('date_format'), strtotime($access['expires_at'])))); ?></span>
											<?php endif; ?>
										</div>
									</div>

									<?php if ($omnify_access['status'] === 'active' && !$omnify_access['is_expired']) : ?>
										<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_revoke_access&customer_id=' . $omnify_customer['id'] . '&product_id=' . $omnify_access['product_id']), 'omnify_revoke_access_' . $omnify_customer['id'] . '_' . $omnify_access['product_id'])); ?>" class="omnify-button omnify-button--danger omnify-button--sm js-confirm-delete" data-message="<?php esc_attr_e('Are you sure you want to revoke access to this product?', 'omnifywp-ecommerce'); ?>" style="padding: 4px 8px; font-size: 11px;">
											<?php esc_html_e('Revoke', 'omnifywp-ecommerce'); ?>
										</a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<!-- Grant Manual Access Form -->
					<h3 style="border-top: 1px solid var(--omnify-gray-200); padding-top: 16px; margin-top: 16px;"><?php esc_html_e('Grant New Access', 'omnifywp-ecommerce'); ?></h3>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_grant_access', 'omnify_grant_nonce'); ?>
						<input type="hidden" name="action" value="omnify_grant_access" />
						<input type="hidden" name="customer_id" value="<?php echo esc_attr($omnify_customer['id']); ?>" />

						<div class="omnify-form-grid" style="max-width: 100%; gap: 12px;">
							<div class="omnify-form-group">
								<label for="grant_product_id"><?php esc_html_e('Select Product', 'omnifywp-ecommerce'); ?> *</label>
								<select id="grant_product_id" name="product_id" required style="width: 100%;">
									<option value=""><?php esc_html_e('-- Select Product --', 'omnifywp-ecommerce'); ?></option>
									<?php foreach ($omnify_products as $omnify_prod) : ?>
										<option value="<?php echo esc_attr($omnify_prod['id']); ?>">
											<?php echo esc_html($omnify_prod['name']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="omnify-form-group">
								<label for="expires_at"><?php esc_html_e('Expiration Date (Optional)', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="expires_at" name="expires_at" placeholder="YYYY-MM-DD" style="width: 100%;" />
								<span class="help-text"><?php esc_html_e('Leave blank for lifetime access.', 'omnifywp-ecommerce'); ?></span>
							</div>

							<div class="omnify-button-group">
								<button type="submit" class="omnify-button omnify-button--primary" style="width: 100%;">
									<?php esc_html_e('Grant Access', 'omnifywp-ecommerce'); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

	<?php else : ?>
		<!-- Customers List Table -->
		<?php
		$omnify_filter_search    = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
		$omnify_filter_date_from = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
		$omnify_filter_date_to   = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
		$omnify_filter_status    = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
		$omnify_filter_sort      = isset($_GET['sort']) ? sanitize_key(wp_unslash($_GET['sort'])) : 'created_desc';
		?>
	<!-- Customers Filter Bar -->
	<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="omnify-filter-card">
		<input type="hidden" name="page" value="omnify-customers" />

		<div class="omnify-filter-row">
			<div class="omnify-filter-group">
				<label for="search_customer"><?php esc_html_e('Search', 'omnifywp-ecommerce'); ?></label>
				<input type="text" id="search_customer" name="search" placeholder="<?php esc_attr_e('Search name or email...', 'omnifywp-ecommerce'); ?>" value="<?php echo esc_attr($omnify_filter_search); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="date_from"><?php esc_html_e('Joined From', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($omnify_filter_date_from); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="date_to"><?php esc_html_e('Joined To', 'omnifywp-ecommerce'); ?></label>
				<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($omnify_filter_date_to); ?>" />
			</div>
			<div class="omnify-filter-group">
				<label for="status"><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></label>
				<select id="status" name="status">
					<option value=""><?php esc_html_e('All Statuses', 'omnifywp-ecommerce'); ?></option>
					<option value="active" <?php selected($omnify_filter_status, 'active'); ?>><?php esc_html_e('Active', 'omnifywp-ecommerce'); ?></option>
					<option value="inactive" <?php selected($omnify_filter_status, 'inactive'); ?>><?php esc_html_e('Inactive', 'omnifywp-ecommerce'); ?></option>
					<option value="trash" <?php selected($omnify_filter_status, 'trash'); ?>><?php esc_html_e('Trash', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-group">
				<label for="sort_by"><?php esc_html_e('Sort By', 'omnifywp-ecommerce'); ?></label>
				<select id="sort_by" name="sort">
					<option value="created_desc" <?php selected($omnify_filter_sort, 'created_desc'); ?>><?php esc_html_e('Newest', 'omnifywp-ecommerce'); ?></option>
					<option value="created_asc" <?php selected($omnify_filter_sort, 'created_asc'); ?>><?php esc_html_e('Oldest', 'omnifywp-ecommerce'); ?></option>
				</select>
			</div>
			<div class="omnify-filter-actions">
				<button type="submit" class="omnify-btn-apply-filters" title="<?php esc_attr_e('Apply Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-search" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers')); ?>" class="omnify-btn-clear-filters" title="<?php esc_attr_e('Clear Filters', 'omnifywp-ecommerce'); ?>">
					<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
				</a>
			</div>
		</div>
	</form>

	<!-- Active filter chips -->
	<?php
	$omnify_c_chips = [];
	if ('' !== $omnify_filter_search) {
		$omnify_c_chips['search'] = [
			'label' => __('Search', 'omnifywp-ecommerce') . ': ' . $omnify_filter_search,
			'url'   => remove_query_arg('search'),
		];
	}
	if ('' !== $omnify_filter_status) {
		$omnify_c_chips['status'] = [
			'label' => __('Status', 'omnifywp-ecommerce') . ': ' . ucfirst($omnify_filter_status),
			'url'   => remove_query_arg('status'),
		];
	}
	if ('' !== $omnify_filter_date_from || '' !== $omnify_filter_date_to) {
		$omnify_date_label = trim($omnify_filter_date_from . ' - ' . $omnify_filter_date_to);
		$omnify_c_chips['date'] = [
			'label' => __('Joined', 'omnifywp-ecommerce') . ': ' . $omnify_date_label,
			'url'   => remove_query_arg(['date_from', 'date_to']),
		];
	}
	?>
	<?php if(!empty($omnify_c_chips)): ?>
	<div class="omnify-active-filters" style="margin: 4px 0 12px;">
		<span style="font-size:11px; color:var(--omnify-gray-500); margin-right:4px; font-weight:600; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e('Active Filters:', 'omnifywp-ecommerce'); ?></span>
		<?php foreach($omnify_c_chips as $omnify_chip): ?>
		<span class="omnify-filter-chip" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 4px 10px; font-size: 12px; color: #334155; margin-right: 6px; font-weight: 500;">
			<?php echo esc_html($omnify_chip['label']); ?>
			<a href="<?php echo esc_url($omnify_chip['url']); ?>" class="remove" style="color: #94a3b8; text-decoration: none; font-weight: 500; line-height: 1; font-size: 14px;">×</a>
		</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="omnify-card">

			<?php if (empty($omnify_customers)) : ?>
				<div class="omnify-empty-state">
					<div class="omnify-empty-state__icon"><span class="dashicons dashicons-groups" style="font-size: 48px; width: 48px; height: 48px; color: var(--omnify-gray-400);"></span></div>
					<h3><?php esc_html_e('Add your first customer', 'omnifywp-ecommerce'); ?></h3>
					<p><?php esc_html_e('Create customer profiles to manage access keys and view download logs.', 'omnifywp-ecommerce'); ?></p>
					<div style="margin-top: 20px;">
						<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=new')); ?>" class="omnify-button omnify-button--primary">
							<?php esc_html_e('Create Customer', 'omnifywp-ecommerce'); ?>
						</a>
					</div>
				</div>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-bulk-customers-form">
					<?php wp_nonce_field('omnify_bulk_customers', 'omnify_bulk_nonce'); ?>
					<input type="hidden" name="action" value="omnify_bulk_customers" />

					<div class="omnify-table-toolbar">
						<div class="left">
							<input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" style="margin-right:8px;" />
							<span class="selected-count">0 selected</span>
							<div class="omnify-bulk-actions-wrapper" style="display: none; align-items: center; gap: 8px; margin-left: 16px;">
								<select name="bulk_action">
									<option value="">Bulk actions</option>
									<option value="trash">Move to Trash</option>
									<option value="restore">Restore</option>
								</select>
								<button type="submit" class="omnify-button omnify-button--secondary omnify-button--sm">Apply</button>
							</div>
						</div>
						<div class="right">
							<a href="#" class="omnify-button omnify-button--secondary omnify-button--sm">Export</a>
						</div>
					</div>
				<div class="omnify-table-wrapper">
					<table class="omnify-table">
						<thead>
							<tr>
								<th style="width:28px;"><input type="checkbox" class="omnify-checkbox omnify-bulk-select-all" /></th>
								<th><?php esc_html_e('Name', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Status', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Orders', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Total Spent', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></th>
								<th><?php esc_html_e('Joined Date', 'omnifywp-ecommerce'); ?></th>
								<th style="text-align: right;"><?php esc_html_e('Actions', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_customers as $omnify_cust) : ?>
								<tr>
									<td><input type="checkbox" class="omnify-checkbox omnify-bulk-item" name="customer_ids[]" value="<?php echo esc_attr($omnify_cust['id']); ?>" /></td>
									<td>
										<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_cust['id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--omnify-dark);">
											<?php echo esc_html($omnify_cust['name'] ?: __('Guest Customer', 'omnifywp-ecommerce')); ?>
										</a>
									</td>
									<td><?php echo esc_html($omnify_cust['email']); ?></td>
									<td>
										<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_cust['status']); ?>">
											<?php echo esc_html($omnify_cust['status']); ?>
										</span>
									</td>
									<td><?php echo esc_html(number_format((int)($omnify_cust['order_count'] ?? 0))); ?></td>
									<td><strong><?php echo esc_html($omnify_symbol . number_format((float)($omnify_cust['total_spent'] ?? 0), 2)); ?></strong></td>
									<td><?php echo esc_html($omnify_cust['company'] ?: '-'); ?></td>
									<td><?php echo esc_html($omnify_cust['phone'] ?: '-'); ?></td>
									<td>
										<span style="text-transform: uppercase;">
											<?php echo esc_html($omnify_cust['country'] ?: '-'); ?>
										</span>
									</td>
									<td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($omnify_cust['created_at']))); ?></td>
									<td style="text-align: right;">
										<div class="omnify-row-actions">
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_cust['id'])); ?>" class="omnify-icon-btn" title="<?php esc_attr_e('Manage / Edit', 'omnifywp-ecommerce'); ?>">
												<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
											</a>
											<?php if ($omnify_is_trashed): ?>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_restore_customer&id=' . $omnify_cust['id']), 'omnify_restore_customer_' . $omnify_cust['id'])); ?>" class="omnify-icon-btn" title="Restore"><span class="dashicons dashicons-undo" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span></a>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_customer&id=' . $omnify_cust['id']), 'omnify_delete_customer_' . $omnify_cust['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Delete permanently" onclick="return confirm('Permanently?');"><span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span></a>
											<?php else: ?>
												<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_trash_customer&id=' . $omnify_cust['id']), 'omnify_trash_customer_' . $omnify_cust['id'])); ?>" class="omnify-icon-btn omnify-icon-btn--danger" title="Move to trash" onclick="return confirm('Trash?');"><span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span></a>
											<?php endif; ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				</form>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>


