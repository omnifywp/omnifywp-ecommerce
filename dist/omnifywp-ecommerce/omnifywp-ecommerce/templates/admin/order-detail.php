<?php

defined('ABSPATH') || exit;

$omnify_template_vars = get_defined_vars();
$omnify_access_row = $omnify_template_vars['omnify_access_row'] ?? null;
$omnify_access_status = $omnify_template_vars['omnify_access_status'] ?? null;
$omnify_active_tab = $omnify_template_vars['omnify_active_tab'] ?? null;
$omnify_author_name = $omnify_template_vars['omnify_author_name'] ?? null;
$omnify_card_bg = $omnify_template_vars['omnify_card_bg'] ?? null;
$omnify_card_border = $omnify_template_vars['omnify_card_border'] ?? null;
$omnify_currency = $omnify_template_vars['omnify_currency'] ?? null;
$omnify_currency_symbol = $omnify_template_vars['omnify_currency_symbol'] ?? null;
$omnify_cust_row = $omnify_template_vars['omnify_cust_row'] ?? null;
$omnify_customer_access_repo = $omnify_template_vars['omnify_customer_access_repo'] ?? null;
$omnify_download_url = $omnify_template_vars['omnify_download_url'] ?? null;
$omnify_file = $omnify_template_vars['omnify_file'] ?? null;
$omnify_files = $omnify_template_vars['omnify_files'] ?? null;
$omnify_files_found = $omnify_template_vars['omnify_files_found'] ?? null;
$omnify_flag = $omnify_template_vars['omnify_flag'] ?? null;
$omnify_fraud_flags = $omnify_template_vars['omnify_fraud_flags'] ?? null;
$omnify_fraud_score = $omnify_template_vars['omnify_fraud_score'] ?? null;
$omnify_fraud_status = $omnify_template_vars['omnify_fraud_status'] ?? null;
$omnify_has_digital_items = $omnify_template_vars['omnify_has_digital_items'] ?? null;
$omnify_ip_address = $omnify_template_vars['omnify_ip_address'] ?? null;
$omnify_item = $omnify_template_vars['omnify_item'] ?? null;
$omnify_item_price = $omnify_template_vars['omnify_item_price'] ?? null;
$omnify_item_tax = $omnify_template_vars['omnify_item_tax'] ?? null;
$omnify_item_unit_cost = $omnify_template_vars['omnify_item_unit_cost'] ?? null;
$omnify_item_unit_display = $omnify_template_vars['omnify_item_unit_display'] ?? null;
$omnify_label = $omnify_template_vars['omnify_label'] ?? null;
$omnify_max_item_refund = $omnify_template_vars['omnify_max_item_refund'] ?? null;
$omnify_note = $omnify_template_vars['omnify_note'] ?? null;
$omnify_note_badge = $omnify_template_vars['omnify_note_badge'] ?? null;
$omnify_note_color = $omnify_template_vars['omnify_note_color'] ?? null;
$omnify_note_type = $omnify_template_vars['omnify_note_type'] ?? null;
$omnify_order = $omnify_template_vars['omnify_order'] ?? null;
$omnify_order_document_url = $omnify_template_vars['omnify_order_document_url'] ?? null;
$omnify_order_symbol = $omnify_template_vars['omnify_order_symbol'] ?? null;
$omnify_p = $omnify_template_vars['omnify_p'] ?? null;
$omnify_p_id = $omnify_template_vars['omnify_p_id'] ?? null;
$omnify_prices_include_tax = $omnify_template_vars['omnify_prices_include_tax'] ?? null;
$omnify_prod = $omnify_template_vars['omnify_prod'] ?? null;
$omnify_product_files_repo = $omnify_template_vars['omnify_product_files_repo'] ?? null;
$omnify_product_id = $omnify_template_vars['omnify_product_id'] ?? null;
$omnify_refundable_balance = $omnify_template_vars['omnify_refundable_balance'] ?? null;
$omnify_signed_urls = $omnify_template_vars['omnify_signed_urls'] ?? null;
$omnify_status_color = $omnify_template_vars['omnify_status_color'] ?? null;
$omnify_status_text = $omnify_template_vars['omnify_status_text'] ?? null;
$omnify_statuses = $omnify_template_vars['omnify_statuses'] ?? null;
$omnify_tax_label = $omnify_template_vars['omnify_tax_label'] ?? null;
$omnify_tax_rate = $omnify_template_vars['omnify_tax_rate'] ?? null;
$omnify_tax_reporting_code = $omnify_template_vars['omnify_tax_reporting_code'] ?? null;
$omnify_type = $omnify_template_vars['omnify_type'] ?? null;
$omnify_user = $omnify_template_vars['omnify_user'] ?? null;
$omnify_val = $omnify_template_vars['omnify_val'] ?? null;


/**
 * Admin Order Detail Template
 *
 * @package Omnify
 */
// Helper to format prices
$omnify_currency_symbol = function($omnify_currency) {
	switch ($omnify_currency) {
		case 'EUR': return '€';
		case 'GBP': return 'Â£';
		case 'JPY': return 'Â¥';
		case 'CAD': return 'C$';
		case 'AUD': return 'A$';
		default: return '$';
	}
};
$omnify_order_symbol = $omnify_currency_symbol($omnify_order['currency']);
$omnify_refundable_balance = max(0.0, (float) $omnify_order['total'] - (float) $omnify_order['refunded_amount']);
$omnify_tax_label = ! empty($omnify_order['tax_label']) ? (string) $omnify_order['tax_label'] : __('Tax', 'omnifywp-ecommerce');
$omnify_tax_rate = isset($omnify_order['tax_rate']) ? (float) $omnify_order['tax_rate'] : 0.0;
$omnify_tax_reporting_code = ! empty($omnify_order['tax_reporting_code']) ? (string) $omnify_order['tax_reporting_code'] : '';
$omnify_prices_include_tax = ! empty($omnify_order['tax_inclusive']);
$omnify_order_document_url = static function(string $omnify_type) use ($omnify_order): string {
	return wp_nonce_url(
		admin_url('admin-post.php?action=omnify_order_document&order_id=' . (int) $omnify_order['id'] . '&document_type=' . sanitize_key($omnify_type)),
		'omnify_order_document_' . (int) $omnify_order['id']
	);
};
?>
<div class="omnify-admin-wrapper">
	<?php $omnify_active_tab = 'orders'; ?>
	<div class="omnify-header-row" style="margin-bottom: 16px;">
		<div class="omnify-header-row__title">
			<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-orders')); ?>" style="text-decoration: none; color: var(--omnify-gray-800); font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
				<span class="dashicons dashicons-arrow-left-alt" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
				<?php esc_html_e('Back to Orders', 'omnifywp-ecommerce'); ?>
			</a>
			<h1 style="display: flex; align-items: center; gap: 12px; margin: 0;">
				<?php 
				// translators: %s: placeholder value.
				echo esc_html(sprintf(__('Order %s', 'omnifywp-ecommerce'), $omnify_order['order_number'] ?: '#' . $omnify_order['id'])); ?>
				<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_order['status']); ?>" style="font-size: 13px;">
					<?php echo esc_html($omnify_order['status']); ?>
				</span>
			</h1>
			<p style="margin-top: 6px;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_order['created_at']))); ?></p>
		</div>
		<div class="omnify-header-row__actions">
			<a href="<?php echo esc_url($omnify_order_document_url('order')); ?>" target="_blank" class="omnify-button omnify-button--secondary" style="text-decoration: none;">
				<?php esc_html_e('Print Order', 'omnifywp-ecommerce'); ?>
			</a>
			<a href="<?php echo esc_url($omnify_order_document_url('invoice')); ?>" target="_blank" class="omnify-button omnify-button--secondary" style="text-decoration: none;">
				<?php esc_html_e('Invoice / PDF', 'omnifywp-ecommerce'); ?>
			</a>
			<?php if (! empty($omnify_order['shipping_address_1'])) : ?>
				<a href="<?php echo esc_url($omnify_order_document_url('packing_slip')); ?>" target="_blank" class="omnify-button omnify-button--secondary" style="text-decoration: none;">
					<?php esc_html_e('Packing Slip', 'omnifywp-ecommerce'); ?>
				</a>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('omnify_resend_receipt', 'omnify_resend_nonce'); ?>
				<input type="hidden" name="action" value="omnify_resend_receipt" />
				<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
				<button type="submit" class="omnify-button omnify-button--secondary">
						<span class="dashicons dashicons-email-alt" style="margin-right: 4px; vertical-align: middle;"></span><?php esc_html_e('Resend Receipt', 'omnifywp-ecommerce'); ?>
				</button>
			</form>
		</div>
	</div>

	<?php include __DIR__ . '/partials/nav.php'; ?>

	<?php if ($omnify_order['status'] === 'refund_requested') : ?>
		<div class="omnify-alert omnify-alert--warning" style="background: var(--omnify-warning-bg); border: 1px solid var(--omnify-warning); color: var(--omnify-warning); padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
			<div style="display: flex; align-items: center; gap: 10px;">
							<span class="dashicons dashicons-warning" style="font-size: 20px; width: 20px; height: 20px; color: #f59e0b; vertical-align: middle;"></span>
				<div>
					<strong style="display: block; font-size: 14px; margin-bottom: 2px; color: var(--omnify-warning);"><?php esc_html_e('Refund Request Pending', 'omnifywp-ecommerce'); ?></strong>
					<span style="font-size: 12px; color: var(--omnify-gray-800);"><?php esc_html_e('The customer has requested a refund for this order. Please review the details below to process the request.', 'omnifywp-ecommerce'); ?></span>
				</div>
			</div>
			<div>
				<a href="#omnify-refund-card" class="omnify-button omnify-button--sm omnify-button--primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 32px; background: var(--omnify-warning); border-color: var(--omnify-warning); color: #fff;">
					<?php esc_html_e('Review Request', 'omnifywp-ecommerce'); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<div class="omnify-layout-grid">
		<!-- Left Column -->
		<div>
			<!-- Order Items Card -->
			<div class="omnify-card">
				<h2><?php esc_html_e('Order Items', 'omnifywp-ecommerce'); ?></h2>
				<div class="omnify-table-wrapper">
					<table class="omnify-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 100px; text-align: right;"><?php esc_html_e('Price', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 80px; text-align: center;"><?php esc_html_e('Qty', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 100px; text-align: right;"><?php esc_html_e('Tax', 'omnifywp-ecommerce'); ?></th>
								<th style="width: 100px; text-align: right;"><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($omnify_order['items'] as $omnify_item) : ?>
								<tr>
									<td>
										<?php if ($omnify_item['product_id']) : ?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-products&action=edit&id=' . $omnify_item['product_id'])); ?>" style="text-decoration: none; color: var(--omnify-green); font-weight: 600;">
												<?php echo esc_html($omnify_item['product_name']); ?>
											</a>
										<?php else : ?>
											<strong><?php echo esc_html($omnify_item['product_name']); ?></strong>
										<?php endif; ?>

										<?php 
										$omnify_prod = $omnify_item['product_id'] ? $this->omnify_products->find((int) $omnify_item['product_id']) : null;
										if ($omnify_prod && ! empty($omnify_prod['sku']) && strpos($omnify_item['product_name'], $omnify_prod['sku']) === false) : ?>
											<div style="font-size: 11px; color: var(--omnify-gray-500); margin-top: 4px;">
												<strong><?php esc_html_e('SKU:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html($omnify_prod['sku']); ?>
											</div>
										<?php endif; ?>
									</td>
									<td style="text-align: right;"><?php echo esc_html($omnify_order_symbol . number_format($omnify_item['price'], 2)); ?></td>
									<td style="text-align: center;"><?php echo esc_html($omnify_item['quantity']); ?></td>
									<td style="text-align: right;"><?php echo esc_html($omnify_order_symbol . number_format($omnify_item['tax'], 2)); ?></td>
									<td style="text-align: right; font-weight: 600;">
										<?php
										$omnify_item_unit_display = $omnify_prices_include_tax ? (float) $omnify_item['price'] : ((float) $omnify_item['price'] + (float) $omnify_item['tax']);
										echo esc_html($omnify_order_symbol . number_format($omnify_item_unit_display * (int) $omnify_item['quantity'], 2));
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Financial Summary Card -->
			<div class="omnify-card">
				<h2><?php esc_html_e('Financial Summary', 'omnifywp-ecommerce'); ?></h2>
				<div style="display: grid; gap: 8px; max-width: 400px; margin-bottom: 20px;">
					<div style="display: flex; justify-content: space-between; font-size: 13px;">
						<span><?php esc_html_e('Subtotal', 'omnifywp-ecommerce'); ?>:</span>
						<span><?php echo esc_html($omnify_order_symbol . number_format($omnify_order['subtotal'], 2)); ?></span>
					</div>
					<?php if (! empty($omnify_order['coupon_code']) && (float) ($omnify_order['discount_amount'] ?? 0) > 0) : ?>
						<div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--omnify-green);">
							<span>
								<?php
								printf(
									/* translators: %s: coupon code */
									esc_html__('Discount (Coupon: %s)', 'omnifywp-ecommerce'),
									'<strong style="letter-spacing: 0.05em; background: rgba(0,128,96,0.08); padding: 1px 6px; border-radius: 3px;">' . esc_html(strtoupper($omnify_order['coupon_code'])) . '</strong>'
								);
								?>
							</span>
							<span style="font-weight: 600;">-<?php echo esc_html($omnify_order_symbol . number_format((float) $omnify_order['discount_amount'], 2)); ?></span>
						</div>
					<?php endif; ?>
					<div style="display: flex; justify-content: space-between; font-size: 13px;">
						<span><?php echo esc_html($omnify_tax_label); ?><?php echo $omnify_tax_rate > 0 ? esc_html(sprintf(' (%s%%)', number_format($omnify_tax_rate, 4))) : ''; ?>:</span>
						<span><?php echo esc_html($omnify_order_symbol . number_format($omnify_order['tax'], 2)); ?></span>
					</div>
					<?php if (! empty($omnify_tax_reporting_code) || ! empty($omnify_order['tax_inclusive']) || ! empty($omnify_order['tax_shipping']) || ! empty($omnify_order['customer_tax_exempt'])) : ?>
						<div style="display: grid; gap: 6px; padding: 10px 12px; background: var(--omnify-gray-50); border: 1px solid var(--omnify-gray-200); border-radius: 6px; font-size: 12px; color: var(--omnify-gray-700);">
							<?php if (! empty($omnify_tax_reporting_code)) : ?>
								<div style="display: flex; justify-content: space-between; gap: 12px;">
									<span><?php esc_html_e('Reporting Code', 'omnifywp-ecommerce'); ?></span>
									<strong><?php echo esc_html($omnify_tax_reporting_code); ?></strong>
								</div>
							<?php endif; ?>
							<div style="display: flex; justify-content: space-between; gap: 12px;">
								<span><?php esc_html_e('Price Mode', 'omnifywp-ecommerce'); ?></span>
								<strong><?php echo ! empty($omnify_order['tax_inclusive']) ? esc_html__('Tax inclusive', 'omnifywp-ecommerce') : esc_html__('Tax exclusive', 'omnifywp-ecommerce'); ?></strong>
							</div>
							<div style="display: flex; justify-content: space-between; gap: 12px;">
								<span><?php esc_html_e('Tax on Shipping', 'omnifywp-ecommerce'); ?></span>
								<strong><?php echo ! empty($omnify_order['tax_shipping']) ? esc_html__('Yes', 'omnifywp-ecommerce') : esc_html__('No', 'omnifywp-ecommerce'); ?></strong>
							</div>
							<?php if (! empty($omnify_order['customer_tax_exempt'])) : ?>
								<div style="display: flex; justify-content: space-between; gap: 12px;">
									<span><?php esc_html_e('Customer Tax Status', 'omnifywp-ecommerce'); ?></span>
									<strong><?php esc_html_e('Tax exempt', 'omnifywp-ecommerce'); ?></strong>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ((float) ($omnify_order['shipping_total'] ?? 0) > 0 || ! empty($omnify_order['shipping_method'])) : ?>
						<div style="display: flex; justify-content: space-between; font-size: 13px;">
							<span><?php echo esc_html(! empty($omnify_order['shipping_method']) ? $omnify_order['shipping_method'] : __('Delivery', 'omnifywp-ecommerce')); ?>:</span>
							<span><?php echo esc_html($omnify_order_symbol . number_format((float) ($omnify_order['shipping_total'] ?? 0), 2)); ?></span>
						</div>
					<?php endif; ?>
					<?php if (! empty($omnify_order['payment_method']) && $omnify_order['payment_method'] !== 'card') : ?>
					<div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--omnify-gray-600);">
						<span><?php esc_html_e('Payment Method', 'omnifywp-ecommerce'); ?>:</span>
						<span style="text-transform: capitalize;"><?php echo esc_html(ucwords(str_replace('_', ' ', $omnify_order['payment_method']))); ?></span>
					</div>
					<?php endif; ?>
					<div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 500; border-top: 1px solid var(--omnify-gray-200); padding-top: 8px;">
						<span><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?>:</span>
						<span><?php echo esc_html($omnify_order_symbol . number_format($omnify_order['total'], 2)); ?></span>
					</div>
					<?php if ($omnify_order['refunded_amount'] > 0) : ?>
						<div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--omnify-danger); font-weight: 600;">
							<span><?php esc_html_e('Refunded', 'omnifywp-ecommerce'); ?>:</span>
							<span>-<?php echo esc_html($omnify_order_symbol . number_format($omnify_order['refunded_amount'], 2)); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($omnify_refundable_balance > 0) : ?>
					<div id="omnify-refund-card" style="border-top: 1px solid var(--omnify-gray-200); padding-top: 20px; margin-top: 20px;">
						<h3 style="font-size: 16px; font-weight: 500; margin-bottom: 12px; color: var(--omnify-dark);"><?php esc_html_e('Process Refund', 'omnifywp-ecommerce'); ?></h3>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="omnify-refund-form" data-max-refundable="<?php echo esc_attr((string) $omnify_refundable_balance); ?>" data-currency-symbol="<?php echo esc_attr((string) $omnify_order_symbol); ?>" data-confirm-msg="<?php esc_attr_e('Are you sure you want to process this refund? If this order was paid via Stripe or PayPal, this action will issue a real refund to the customer.', 'omnifywp-ecommerce'); ?>">
							<?php wp_nonce_field('omnify_refund_order', 'omnify_refund_nonce'); ?>
							<input type="hidden" name="action" value="omnify_refund_order" />
							<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
							
							<div class="omnify-table-wrapper" style="margin-bottom: 16px; border: 1px solid var(--omnify-gray-200); border-radius: 6px; overflow: hidden;">
								<table class="omnify-table" style="margin: 0; background: #fafafa;">
									<thead>
										<tr style="background: var(--omnify-gray-50);">
											<th style="width: 40px; padding: 10px; text-align: center;"></th>
											<th><?php esc_html_e('Product', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 110px; text-align: center;"><?php esc_html_e('Qty to Refund', 'omnifywp-ecommerce'); ?></th>
											<th style="width: 120px; text-align: right;"><?php esc_html_e('Refund Subtotal', 'omnifywp-ecommerce'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($omnify_order['items'] as $omnify_item) : 
											$omnify_max_item_refund = (int) $omnify_item['quantity'] - (int) ($omnify_item['refunded_qty'] ?? 0);
											$omnify_item_price = (float) $omnify_item['price'];
											$omnify_item_tax = (float) $omnify_item['tax'];
											$omnify_item_unit_cost = $omnify_prices_include_tax ? $omnify_item_price : ($omnify_item_price + $omnify_item_tax);
										?>
											<tr>
												<td style="padding: 10px; text-align: center;">
													<?php if ($omnify_max_item_refund > 0) : ?>
														<input type="checkbox" class="omnify-checkbox omnify-refund-item-checkbox" data-item-id="<?php echo esc_attr((string) $omnify_item['id']); ?>" name="refund_items[<?php echo esc_attr((string) $omnify_item['id']); ?>][checked]" value="1" />
													<?php else : ?>
														<span style="color: var(--omnify-danger); font-size: 11px; font-weight: 600;"><?php esc_html_e('Refunded', 'omnifywp-ecommerce'); ?></span>
													<?php endif; ?>
												</td>
												<td>
													<strong><?php echo esc_html($omnify_item['product_name']); ?></strong>
													<div style="font-size: 11px; color: var(--omnify-gray-500); margin-top: 2px;">
														<?php 
														// translators: %1$s: placeholder value, %2$s: placeholder value, %3$d: placeholder value, %4$d: placeholder value.
														printf(esc_html__('Price: %1$s &middot; Tax: %2$s &middot; Ordered: %3$d &middot; Refunded: %4$d', 'omnifywp-ecommerce'), esc_html($omnify_order_symbol . number_format($omnify_item_price, 2)), esc_html($omnify_order_symbol . number_format($omnify_item_tax, 2)), (int) $omnify_item['quantity'], (int) ($omnify_item['refunded_qty'] ?? 0)); ?>
													</div>
												</td>
												<td style="text-align: center; padding: 6px;">
													<?php if ($omnify_max_item_refund > 0) : ?>
														<input type="number" class="omnify-refund-item-qty" name="refund_items[<?php echo esc_attr((string) $omnify_item['id']); ?>][qty]" value="0" min="0" max="<?php echo esc_attr((string) $omnify_max_item_refund); ?>" data-unit-cost="<?php echo esc_attr((string) $omnify_item_unit_cost); ?>" style="width: 70px; text-align: center; padding: 5px; border: 1px solid var(--omnify-gray-300); border-radius: 4px;" disabled />
													<?php else : ?>
														<input type="hidden" name="refund_items[<?php echo esc_attr((string) $omnify_item['id']); ?>][qty]" value="0" />
														—
													<?php endif; ?>
												</td>
												<td style="text-align: right; font-weight: 600;" class="omnify-refund-item-total" data-item-id="<?php echo esc_attr((string) $omnify_item['id']); ?>">
													<?php echo esc_html($omnify_order_symbol . '0.00'); ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>

							<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
								<?php if ((float) ($omnify_order['shipping_total'] ?? 0.0) > 0.0) : ?>
									<div class="omnify-form-group">
										<label for="refund_shipping" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--omnify-gray-700);"><?php esc_html_e('Refund Shipping', 'omnifywp-ecommerce'); ?> (<?php echo esc_html($omnify_order_symbol . number_format((float) $omnify_order['shipping_total'], 2)); ?> max)</label>
										<input type="number" id="refund_shipping" name="refund_shipping" step="0.01" min="0" max="<?php echo esc_attr((string) $omnify_order['shipping_total']); ?>" value="0.00" style="width: 100%; height: 36px; padding: 7px 12px;" />
									</div>
								<?php endif; ?>

								<div class="omnify-form-group">
									<label for="refund_tax" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--omnify-gray-700);"><?php esc_html_e('Refund Additional Tax', 'omnifywp-ecommerce'); ?> (<?php echo esc_html($omnify_order_symbol . number_format(max(0.0, (float) $omnify_order['tax']), 2)); ?> max)</label>
									<input type="number" id="refund_tax" name="refund_tax" step="0.01" min="0" max="<?php echo esc_attr((string) max(0.0, (float) $omnify_order['tax'])); ?>" value="0.00" style="width: 100%; height: 36px; padding: 7px 12px;" />
								</div>

								<div class="omnify-form-group" style="grid-column: span 2;">
									<label for="refund_reason" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--omnify-gray-700);"><?php esc_html_e('Refund Reason (Optional)', 'omnifywp-ecommerce'); ?></label>
									<input type="text" id="refund_reason" name="refund_reason" placeholder="<?php esc_attr_e('e.g. Customer returned items, duplicate order', 'omnifywp-ecommerce'); ?>" style="width: 100%; height: 36px; padding: 7px 12px;" />
								</div>
							</div>

							<div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; background: var(--omnify-gray-50); border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 14px;">
								<label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; cursor: pointer; margin: 0;">
									<input class="omnify-checkbox" type="checkbox" name="restock_items" value="1" checked />
									<span>🔄 <?php esc_html_e('Restock refunded items in product catalog', 'omnifywp-ecommerce'); ?></span>
								</label>

								<?php 
								$omnify_has_digital_items = false;
								foreach ($omnify_order['items'] as $omnify_item) {
									$omnify_p_id = (int) $omnify_item['product_id'];
									if ($omnify_p_id > 0) {
										$omnify_p = $this->omnify_products->find($omnify_p_id);
										if ($omnify_p && ($omnify_p['type'] === 'download' || $omnify_p['type'] === 'variable')) {
											$omnify_has_digital_items = true;
											break;
										}
									}
								}
								if ($omnify_has_digital_items) : ?>
									<label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; cursor: pointer; margin: 0;">
										<input class="omnify-checkbox" type="checkbox" name="revoke_access" value="1" checked />
										<span>🚫 <?php esc_html_e('Revoke download access for refunded digital products', 'omnifywp-ecommerce'); ?></span>
									</label>
								<?php endif; ?>
							</div>

							<div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; border-top: 1px solid var(--omnify-gray-200); padding-top: 16px;">
								<div>
									<span class="help-text" style="display: block; margin-bottom: 4px; font-size: 11px; color: var(--omnify-gray-500);"><?php
									// translators: %s: placeholder value. printf(esc_html__('Maximum refundable amount is %s', 'omnifywp-ecommerce'), esc_html($order_symbol . number_format($refundable_balance, 2))); ?></span>
									<div style="font-size: 15px; font-weight: 500; color: var(--omnify-dark);">
										<?php esc_html_e('Total Refund: ', 'omnifywp-ecommerce'); ?>
										<span id="omnify-refund-total-display"><?php echo esc_html($omnify_order_symbol . '0.00'); ?></span>
									</div>
								</div>
								<button type="submit" class="omnify-button omnify-button--danger" id="omnify-refund-submit-btn" disabled style="height: 36px; padding: 7px 16px; font-size: 13px; font-weight: 500;">
									<?php esc_html_e('Issue Refund', 'omnifywp-ecommerce'); ?>
								</button>
							</div>
						</form>
					</div>
					
				<?php endif; ?>

				<?php if ($omnify_order['status'] === 'pending_payment') : ?>
					<div style="border-top: 1px solid var(--omnify-gray-200); padding-top: 20px; margin-top: 20px; background: var(--omnify-amber-light); border-radius: 6px; padding: 16px;">
						<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
							<div>
									<strong style="font-size: 13px; color: #92400e;">⏳ <?php esc_html_e('Awaiting Payment', 'omnifywp-ecommerce'); ?></strong>
								<p style="margin: 4px 0 0; font-size: 12px; color: #78350f;">
									<?php esc_html_e('This order is waiting for manual payment confirmation. Click below once payment is received.', 'omnifywp-ecommerce'); ?>
								</p>
							</div>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<?php wp_nonce_field('omnify_mark_order_paid_' . $omnify_order['id'], 'omnify_mark_paid_nonce'); ?>
								<input type="hidden" name="action" value="omnify_mark_order_paid" />
								<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
								<button type="submit" class="omnify-button omnify-button--primary">
									✓ <?php esc_html_e('Mark as Paid', 'omnifywp-ecommerce'); ?>
								</button>
							</form>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Order Notes Card -->
			<div class="omnify-card">
				<h2><?php esc_html_e('Timeline & Order Notes', 'omnifywp-ecommerce'); ?></h2>
				
				<!-- Notes log list -->
				<?php if (empty($omnify_order['notes'])) : ?>
					<p style="font-size: 13px; color: var(--omnify-gray-600); margin-bottom: 24px;"><?php esc_html_e('No notes on this order yet.', 'omnifywp-ecommerce'); ?></p>
				<?php else : ?>
					<div class="omnify-timeline" style="margin-bottom: 24px;">
						<?php foreach ($omnify_order['notes'] as $omnify_note) : ?>
							<?php
							$omnify_note_type = sanitize_key((string) ($omnify_note['note_type'] ?? 'private'));
							$omnify_note_badge = match ($omnify_note_type) {
								'customer' => __('Customer note', 'omnifywp-ecommerce'),
								'system'   => __('System event', 'omnifywp-ecommerce'),
								default    => __('Private note', 'omnifywp-ecommerce'),
							};
							$omnify_note_color = match ($omnify_note_type) {
								'customer' => '#0369a1',
								'system'   => '#6b7280',
								default    => '#7c2d12',
							};
							?>
							<div class="omnify-timeline-item" style="border-bottom: 1px solid var(--omnify-gray-100); padding-bottom: 12px; margin-bottom: 12px;">
								<div class="omnify-timeline-item__bullet">💬</div>
								<div class="omnify-timeline-item__content">
									<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
										<span style="display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 999px; background: color-mix(in srgb, <?php echo esc_attr($omnify_note_color); ?> 12%, white); color: <?php echo esc_attr($omnify_note_color); ?>; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .04em;">
											<?php echo esc_html($omnify_note_badge); ?>
										</span>
										<?php if (! empty($omnify_note['customer_visible'])) : ?>
											<span style="font-size: 11px; color: var(--omnify-gray-600);"><?php esc_html_e('Visible to customer', 'omnifywp-ecommerce'); ?></span>
										<?php endif; ?>
									</div>
									<div style="font-size: 13px; line-height: 1.4; color: var(--omnify-dark);">
										<?php echo wp_kses_post($omnify_note['note']); ?>
									</div>
									<span class="omnify-timeline-item__date">
										<?php 
										$omnify_author_name = __('System', 'omnifywp-ecommerce');
										if ($omnify_note['author_id']) {
											$omnify_user = get_userdata($omnify_note['author_id']);
											if ($omnify_user) {
												$omnify_author_name = $omnify_user->display_name;
											}
										}
										printf(
											/* translators: 1: author name, 2: formatted date */
											esc_html__('Added by %1$s on %2$s', 'omnifywp-ecommerce'),
											'<strong>' . esc_html($omnify_author_name) . '</strong>',
											esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_note['created_at'])))
										);
										?>
										&middot; 
										<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_order_note&order_id=' . $omnify_order['id'] . '&note_id=' . $omnify_note['id']), 'omnify_delete_order_note_' . $omnify_note['id'])); ?>" style="color: var(--omnify-danger); text-decoration: none;" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this note?', 'omnifywp-ecommerce'); ?>');">
											<?php esc_html_e('Delete', 'omnifywp-ecommerce'); ?>
										</a>
									</span>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Add note form -->
				<div style="border-top: 1px solid var(--omnify-gray-200); padding-top: 20px; margin-top: 20px;">
					<h3 style="font-size: 14px; margin-bottom: 10px;"><?php esc_html_e('Add Note', 'omnifywp-ecommerce'); ?></h3>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('omnify_add_order_note', 'omnify_note_nonce'); ?>
						<input type="hidden" name="action" value="omnify_add_order_note" />
						<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
						
						<div class="omnify-form-group" style="margin-bottom: 12px;">
							<textarea name="note" placeholder="<?php esc_attr_e('Add notes or internal comments about this order...', 'omnifywp-ecommerce'); ?>" required style="width: 100%; min-height: 80px;"></textarea>
						</div>
						<div class="omnify-form-group" style="margin-bottom: 12px;">
							<label for="omnify_order_note_type"><?php esc_html_e('Note visibility', 'omnifywp-ecommerce'); ?></label>
							<select id="omnify_order_note_type" name="note_type" style="width: 100%; height: 36px;">
								<option value="private"><?php esc_html_e('Private staff note', 'omnifywp-ecommerce'); ?></option>
								<option value="customer"><?php esc_html_e('Customer-visible note', 'omnifywp-ecommerce'); ?></option>
							</select>
							<span class="help-text"><?php esc_html_e('Customer-visible notes are saved to the order timeline for customer service and future portal display.', 'omnifywp-ecommerce'); ?></span>
						</div>
						<button type="submit" class="omnify-button omnify-button--primary">
							<?php esc_html_e('Save Note', 'omnifywp-ecommerce'); ?>
						</button>
					</form>
				</div>
			</div>
		</div>

		<!-- Right Column -->
		<div>
			<!-- Fraud & Risk Assessment Card -->
			<?php 
			$omnify_fraud_status = $omnify_order['fraud_status'] ?? 'safe';
			$omnify_fraud_score  = (int) ($omnify_order['fraud_score'] ?? 0);
			$omnify_fraud_flags  = is_array($omnify_order['fraud_flags'] ?? null) ? $omnify_order['fraud_flags'] : [];
			$omnify_ip_address   = $omnify_order['ip_address'] ?? '';
			
			$omnify_card_border = 'var(--omnify-gray-200)';
			$omnify_card_bg = '#fff';
			$omnify_status_text = __('Safe', 'omnifywp-ecommerce');
			$omnify_status_color = '#2e7d32';
			
			if ('high_risk' === $omnify_fraud_status) {
				$omnify_card_border = '#e57373';
				$omnify_card_bg = '#ffebee';
				$omnify_status_text = __('High Risk', 'omnifywp-ecommerce');
				$omnify_status_color = '#c62828';
			} elseif ('flagged' === $omnify_fraud_status) {
				$omnify_card_border = '#ffb74d';
				$omnify_card_bg = '#fff8e1';
				$omnify_status_text = __('Flagged', 'omnifywp-ecommerce');
				$omnify_status_color = '#e65100';
			}
			?>
			<div class="omnify-card" style="border: 1px solid <?php echo esc_attr($omnify_card_border); ?>; background-color: <?php echo esc_attr($omnify_card_bg); ?>;">
				<h2>🛡️ <?php esc_html_e('Fraud & Risk Assessment', 'omnifywp-ecommerce'); ?></h2>
				<div style="margin-top: 12px; font-size: 13px;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
						<strong><?php esc_html_e('Status:', 'omnifywp-ecommerce'); ?></strong>
						<span style="font-weight: 500; color: <?php echo esc_attr($omnify_status_color); ?>; font-size: 14px;">
							<?php echo esc_html($omnify_status_text); ?>
						</span>
					</div>
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
						<strong><?php esc_html_e('Risk Score:', 'omnifywp-ecommerce'); ?></strong>
						<span style="font-weight: 500; font-size: 14px;">
							<?php echo esc_html($omnify_fraud_score); ?>/100
						</span>
					</div>
					<?php if (! empty($omnify_ip_address)) : ?>
						<div style="margin-bottom: 12px;">
							<strong><?php esc_html_e('Customer IP Address:', 'omnifywp-ecommerce'); ?></strong>
							<code style="display: block; margin-top: 4px; background: rgba(0,0,0,0.03); padding: 4px 8px; border-radius: 4px;"><?php echo esc_html($omnify_ip_address); ?></code>
						</div>
					<?php endif; ?>

					<?php if (! empty($omnify_fraud_flags)) : ?>
						<div style="margin-top: 12px; margin-bottom: 16px;">
							<strong style="color: <?php echo esc_attr($omnify_status_color); ?>; display: block; margin-bottom: 8px;"><?php esc_html_e('Triggered Warnings:', 'omnifywp-ecommerce'); ?></strong>
							<ul style="margin: 0; padding-left: 20px; list-style-type: disc; display: grid; gap: 6px; line-height: 1.4;">
								<?php foreach ($omnify_fraud_flags as $omnify_flag) : ?>
									<li><?php echo esc_html($omnify_flag); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ('safe' !== $omnify_fraud_status) : ?>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 16px;">
							<?php wp_nonce_field('omnify_approve_order_risk_' . $omnify_order['id'], 'omnify_approve_order_risk_nonce'); ?>
							<input type="hidden" name="action" value="omnify_approve_order_risk" />
							<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
							<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="justify-content: center; height: 36px; background-color: #2e7d32; border-color: #2e7d32; color: #fff;">
								✓ <?php esc_html_e('Clear Flags & Approve', 'omnifywp-ecommerce'); ?>
							</button>
						</form>
					<?php endif; ?>
				</div>
			</div>

			<!-- Manage Order Card -->
			<div class="omnify-card">
				<h2><?php esc_html_e('Manage Order', 'omnifywp-ecommerce'); ?></h2>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 16px;">
					<?php wp_nonce_field('omnify_update_order_status_' . $omnify_order['id'], 'omnify_update_status_nonce'); ?>
					<input type="hidden" name="action" value="omnify_update_order_status" />
					<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
					
					<div class="omnify-form-group" style="margin-bottom: 12px;">
						<label for="order_status"><?php esc_html_e('Order Status', 'omnifywp-ecommerce'); ?></label>
						<select id="order_status" name="order_status" style="width: 100%; height: 36px; padding: 7px 12px; font-size: 14px;">
							<?php
							$omnify_statuses = [
								'pending'           => __('Pending', 'omnifywp-ecommerce'),
								'pending_payment'   => __('Pending Payment', 'omnifywp-ecommerce'),
								'completed'         => __('Completed', 'omnifywp-ecommerce'),
								'cancelled'         => __('Cancelled', 'omnifywp-ecommerce'),
								'processing'        => __('Processing', 'omnifywp-ecommerce'),
								'on_hold'           => __('On Hold', 'omnifywp-ecommerce'),
								'refunded'          => __('Refunded', 'omnifywp-ecommerce'),
								'failed'            => __('Failed', 'omnifywp-ecommerce'),
								'packed'            => __('Packed', 'omnifywp-ecommerce'),
								'ready_to_deliver'  => __('Ready to Deliver', 'omnifywp-ecommerce'),
								'shipped'           => __('Shipped', 'omnifywp-ecommerce'),
								'out_for_delivery'  => __('Out for Delivery', 'omnifywp-ecommerce'),
								'delivered'         => __('Delivered', 'omnifywp-ecommerce'),
								'refund_requested'  => __('Refund Requested', 'omnifywp-ecommerce'),
								'returned'          => __('Returned', 'omnifywp-ecommerce'),
							];
							foreach ($omnify_statuses as $omnify_val => $omnify_label) {
								echo '<option value="' . esc_attr($omnify_val) . '" ' . selected($omnify_order['status'], $omnify_val, false) . '>' . esc_html($omnify_label) . '</option>';
							}
							?>
						</select>
					</div>
					<button type="submit" class="omnify-button omnify-button--primary omnify-button--full" style="height: 36px; justify-content: center; align-items: center;">
						<?php esc_html_e('Update Status', 'omnifywp-ecommerce'); ?>
					</button>
				</form>

				<div style="border-top: 1px solid var(--omnify-gray-200); padding-top: 16px; display: grid; gap: 14px;">
					<details style="border: 1px solid var(--omnify-gray-200); border-radius: 8px; padding: 12px; background: #fff;">
						<summary style="cursor: pointer; font-weight: 500; color: var(--omnify-danger);">
							<?php esc_html_e('Cancel Order', 'omnifywp-ecommerce'); ?>
						</summary>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 12px; display: grid; gap: 10px;">
							<?php wp_nonce_field('omnify_cancel_order_' . $omnify_order['id'], 'omnify_cancel_order_nonce'); ?>
							<input type="hidden" name="action" value="omnify_cancel_order" />
							<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
							<div class="omnify-form-group" style="margin: 0;">
								<label for="omnify_cancel_reason"><?php esc_html_e('Cancellation reason', 'omnifywp-ecommerce'); ?></label>
								<textarea id="omnify_cancel_reason" name="cancel_reason" rows="3" placeholder="<?php esc_attr_e('Reason shown in the internal timeline, and optionally to the customer.', 'omnifywp-ecommerce'); ?>"></textarea>
							</div>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="restock_items" value="1" checked /> <?php esc_html_e('Restock unrefunded items', 'omnifywp-ecommerce'); ?></label>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="revoke_access" value="1" checked /> <?php esc_html_e('Revoke digital access', 'omnifywp-ecommerce'); ?></label>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="add_customer_note" value="1" /> <?php esc_html_e('Save reason as customer-visible note', 'omnifywp-ecommerce'); ?></label>
							<button type="submit" class="omnify-button omnify-button--danger omnify-button--full js-confirm-delete" data-message="<?php esc_attr_e('Cancel this order? This will update the order timeline and can restock/revoke access based on your selections.', 'omnifywp-ecommerce'); ?>" style="gap:6px;">
								<span class="dashicons dashicons-no" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
								<?php esc_html_e('Confirm Cancellation', 'omnifywp-ecommerce'); ?>
							</button>
						</form>
					</details>

					<details style="border: 1px solid var(--omnify-gray-200); border-radius: 8px; padding: 12px; background: #fff;">
						<summary style="cursor: pointer; font-weight: 500;">
							<?php esc_html_e('Return Flow', 'omnifywp-ecommerce'); ?>
						</summary>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 12px; display: grid; gap: 10px;">
							<?php wp_nonce_field('omnify_return_order_' . $omnify_order['id'], 'omnify_return_order_nonce'); ?>
							<input type="hidden" name="action" value="omnify_return_order" />
							<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
							<div class="omnify-form-group" style="margin: 0;">
								<label for="omnify_return_reason"><?php esc_html_e('Return reason', 'omnifywp-ecommerce'); ?></label>
								<textarea id="omnify_return_reason" name="return_reason" rows="3" placeholder="<?php esc_attr_e('Describe condition, RMA number, or return context.', 'omnifywp-ecommerce'); ?>"></textarea>
							</div>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="restock_items" value="1" checked /> <?php esc_html_e('Restock returned items', 'omnifywp-ecommerce'); ?></label>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="revoke_access" value="1" /> <?php esc_html_e('Revoke digital access', 'omnifywp-ecommerce'); ?></label>
							<label style="display: flex; gap: 8px; align-items: center; font-size: 12px;"><input class="omnify-checkbox" type="checkbox" name="add_customer_note" value="1" /> <?php esc_html_e('Save reason as customer-visible note', 'omnifywp-ecommerce'); ?></label>
							<button type="submit" class="omnify-button omnify-button--secondary omnify-button--full">
								<?php esc_html_e('Mark Returned', 'omnifywp-ecommerce'); ?>
							</button>
						</form>
					</details>
				</div>
				
				<div style="border-top: 1px solid var(--omnify-gray-200); padding-top: 16px;">
					<a href="<?php
					// translators: %s: placeholder value. echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=omnify_delete_order&order_id=' . $order['id']), 'omnify_delete_order_' . $order['id'])); ?>" class="omnify-button omnify-button--danger omnify-button--full js-confirm-delete" data-message="<?php
					// translators: %s: placeholder value. echo esc_attr(sprintf(__('Are you sure you want to delete Order %s? This action cannot be undone and will permanently remove all related items and notes.', 'omnifywp-ecommerce'), $order['order_number'] ?: '#' . $order['id'])); ?>" style="height: 36px; justify-content: center; align-items: center; text-decoration: none; gap: 6px;">
						<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
						<?php esc_html_e('Delete Order', 'omnifywp-ecommerce'); ?>
					</a>
				</div>
			</div>

			<!-- Customer Details Card -->
			<div class="omnify-card">
				<h2><?php esc_html_e('Customer Details', 'omnifywp-ecommerce'); ?></h2>
				<?php if ($omnify_order['customer_id']) : ?>
					<div style="display: grid; gap: 12px; font-size: 13px;">
						<div>
							<strong style="display: block; color: var(--omnify-gray-600);"><?php esc_html_e('Contact Information', 'omnifywp-ecommerce'); ?></strong>
							<a href="<?php echo esc_url(admin_url('admin.php?page=omnify-customers&action=edit&id=' . $omnify_order['customer_id'])); ?>" style="font-weight: 600; text-decoration: none; color: var(--omnify-green); font-size: 14px; display: block; margin-top: 4px;">
								<?php echo esc_html($omnify_order['customer_name'] ?: $omnify_order['customer_email']); ?>
							</a>
							<span style="color: var(--omnify-gray-600); display: block; margin-top: 2px;"><?php echo esc_html($omnify_order['customer_email']); ?></span>
						</div>

						<?php 
						global $wpdb;
						$omnify_cust_row = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "omnify_customers WHERE id = %d", $omnify_order['customer_id']), ARRAY_A);
						if ($omnify_cust_row) :
						?>
							<?php if (!empty($omnify_cust_row['phone'])) : ?>
								<div>
									<strong style="display: block; color: var(--omnify-gray-600);"><?php esc_html_e('Phone', 'omnifywp-ecommerce'); ?></strong>
									<span><?php echo esc_html($omnify_cust_row['phone']); ?></span>
								</div>
							<?php endif; ?>
							<?php if (!empty($omnify_cust_row['company'])) : ?>
								<div>
									<strong style="display: block; color: var(--omnify-gray-600);"><?php esc_html_e('Company', 'omnifywp-ecommerce'); ?></strong>
									<span><?php echo esc_html($omnify_cust_row['company']); ?></span>
								</div>
							<?php endif; ?>
							<?php if (!empty($omnify_cust_row['country'])) : ?>
								<div>
									<strong style="display: block; color: var(--omnify-gray-600);"><?php esc_html_e('Country', 'omnifywp-ecommerce'); ?></strong>
									<span><?php echo esc_html($omnify_cust_row['country']); ?></span>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<p style="font-size: 13px; color: var(--omnify-gray-600);"><?php esc_html_e('Guest checkout / Customer not registered.', 'omnifywp-ecommerce'); ?></p>
					<span style="font-weight: 600; font-size: 14px;"><?php echo esc_html($omnify_order['customer_email']); ?></span>
				<?php endif; ?>
			</div>

			<?php if (! empty($omnify_order['shipping_address_1'])) : ?>
				<!-- Shipping Address Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Shipping Address', 'omnifywp-ecommerce'); ?></h2>
					<div style="font-size: 13px; line-height: 1.6; color: var(--omnify-dark);">
						<strong><?php echo esc_html($omnify_order['shipping_first_name'] . ' ' . $omnify_order['shipping_last_name']); ?></strong><br />
						<?php echo esc_html($omnify_order['shipping_address_1']); ?><br />
						<?php if (! empty($omnify_order['shipping_address_2'])) : ?>
							<?php echo esc_html($omnify_order['shipping_address_2']); ?><br />
						<?php endif; ?>
						<?php echo esc_html($omnify_order['shipping_city'] . ', ' . $omnify_order['shipping_state'] . ' ' . $omnify_order['shipping_postcode']); ?><br />
						<?php echo esc_html($omnify_order['shipping_country']); ?><br />
						<?php if (! empty($omnify_order['shipping_phone'])) : ?>
							<span style="color: var(--omnify-gray-600);"><?php echo esc_html($omnify_order['shipping_phone']); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<!-- Fulfillment Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Fulfillment', 'omnifywp-ecommerce'); ?></h2>
					
					<?php if (empty($omnify_order['fulfillment_status']) || $omnify_order['fulfillment_status'] === 'unfulfilled') : ?>
						<p style="font-size: 12px; color: var(--omnify-gray-600); margin-bottom: 16px;"><?php esc_html_e('This order is unfulfilled. Enter tracking details to ship.', 'omnifywp-ecommerce'); ?></p>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<?php wp_nonce_field('omnify_fulfill_order', 'omnify_fulfill_nonce'); ?>
							<input type="hidden" name="action" value="omnify_fulfill_order" />
							<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
							<input type="hidden" name="fulfillment_status" value="fulfilled" />

							<div class="omnify-form-group" style="margin-bottom: 12px;">
								<label for="tracking_carrier"><?php esc_html_e('Shipping Carrier', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="tracking_carrier" name="tracking_carrier" placeholder="e.g. DHL, FedEx, USPS" required style="width: 100%;" />
							</div>

							<div class="omnify-form-group" style="margin-bottom: 16px;">
								<label for="tracking_number"><?php esc_html_e('Tracking Number', 'omnifywp-ecommerce'); ?></label>
								<input type="text" id="tracking_number" name="tracking_number" placeholder="e.g. 1Z999AA10123456784" required style="width: 100%;" />
							</div>

							<button type="submit" class="omnify-button omnify-button--primary omnify-button--full">
								📦 <?php esc_html_e('Mark as Fulfilled', 'omnifywp-ecommerce'); ?>
							</button>
						</form>
					<?php else : ?>
						<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
							<span class="omnify-status omnify-status--completed" style="font-size: 11px; padding: 2px 6px;">
								<?php esc_html_e('Fulfilled', 'omnifywp-ecommerce'); ?>
							</span>
							<span style="font-size: 12px; color: var(--omnify-gray-600);">
								<?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($omnify_order['updated_at']))); ?>
							</span>
						</div>

						<div style="font-size: 13px; line-height: 1.6; color: var(--omnify-dark); background: var(--omnify-gray-100); border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 12px;">
							<div><strong><?php esc_html_e('Carrier:', 'omnifywp-ecommerce'); ?></strong> <?php echo esc_html($omnify_order['tracking_carrier'] ?: 'N/A'); ?></div>
							<div style="margin-top: 4px;">
								<strong><?php esc_html_e('Tracking Number:', 'omnifywp-ecommerce'); ?></strong><br />
								<code style="display: inline-block; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-top: 4px;"><?php echo esc_html($omnify_order['tracking_number'] ?: 'N/A'); ?></code>
							</div>
						</div>

						<!-- Option to edit / update fulfillment -->
						<div style="border-top: 1px solid var(--omnify-gray-200); padding-top: 16px; margin-top: 16px;">
							<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm omnify-button--full" id="omnify-edit-fulfillment-btn" style="gap:6px;">
								<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
								<?php esc_html_e('Update Tracking Details', 'omnifywp-ecommerce'); ?>
							</button>

							<div id="omnify-edit-fulfillment-form" style="display: none; margin-top: 12px;">
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<?php wp_nonce_field('omnify_fulfill_order', 'omnify_fulfill_nonce'); ?>
									<input type="hidden" name="action" value="omnify_fulfill_order" />
									<input type="hidden" name="order_id" value="<?php echo esc_attr((string) $omnify_order['id']); ?>" />
									<input type="hidden" name="fulfillment_status" value="fulfilled" />

									<div class="omnify-form-group" style="margin-bottom: 12px;">
										<label for="edit_tracking_carrier"><?php esc_html_e('Shipping Carrier', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="edit_tracking_carrier" name="tracking_carrier" value="<?php echo esc_attr($omnify_order['tracking_carrier']); ?>" required style="width: 100%;" />
									</div>

									<div class="omnify-form-group" style="margin-bottom: 16px;">
										<label for="edit_tracking_number"><?php esc_html_e('Tracking Number', 'omnifywp-ecommerce'); ?></label>
										<input type="text" id="edit_tracking_number" name="tracking_number" value="<?php echo esc_attr($omnify_order['tracking_number']); ?>" required style="width: 100%;" />
									</div>

									<div style="display: flex; gap: 8px;">
										<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm" id="omnify-cancel-fulfillment-btn" style="flex: 1;">
											<?php esc_html_e('Cancel', 'omnifywp-ecommerce'); ?>
										</button>
										<button type="submit" class="omnify-button omnify-button--primary omnify-button--sm" style="flex: 1;">
											<?php esc_html_e('Save Changes', 'omnifywp-ecommerce'); ?>
										</button>
									</div>
								</form>
							</div>
						</div>

					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (empty($omnify_order['shipping_address_1'])) : ?>
				<!-- Digital Downloads Access Card -->
				<div class="omnify-card">
					<h2><?php esc_html_e('Digital Files Access', 'omnifywp-ecommerce'); ?></h2>
					<p style="font-size: 12px; color: var(--omnify-gray-600); margin-bottom: 16px;"><?php esc_html_e('The files below are granted to the customer via this order.', 'omnifywp-ecommerce'); ?></p>
					
					<?php 
					$omnify_files_found = false;
					if ($omnify_order['customer_id']) :
						foreach ($omnify_order['items'] as $omnify_item) :
							$omnify_product_id = (int) $omnify_item['product_id'];
							if ($omnify_product_id > 0) :
								$omnify_files = $omnify_product_files_repo->all_for_product($omnify_product_id);
								if (!empty($omnify_files)) :
									$omnify_files_found = true;
									?>
									<div style="margin-bottom: 16px;">
										<h4 style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--omnify-gray-800); margin-bottom: 8px;"><?php echo esc_html($omnify_item['product_name']); ?></h4>
										<div style="display: grid; gap: 8px;">
											<?php foreach ($omnify_files as $omnify_file) : 
												$omnify_download_url = $omnify_signed_urls->create((int) $omnify_file['id'], 3600 * 24, (int) $omnify_order['customer_id']);
												$omnify_access_row = $omnify_customer_access_repo->find_by_customer_and_product((int) $omnify_order['customer_id'], $omnify_product_id);
												$omnify_access_status = $omnify_access_row ? $omnify_access_row['status'] : 'inactive';
											?>
												<div style="background: var(--omnify-gray-100); border: 1px solid var(--omnify-gray-200); border-radius: 6px; padding: 10px; font-size: 12px; display: flex; flex-direction: column; gap: 6px;">
													<div style="display: flex; justify-content: space-between; align-items: center;">
														<strong style="color: var(--omnify-dark); text-overflow: ellipsis; white-space: nowrap; overflow: hidden; max-width: 160px;"><?php echo esc_html($omnify_file['file_name']); ?></strong>
														<span class="omnify-status omnify-status--<?php echo esc_attr($omnify_access_status); ?>" style="font-size: 9px; padding: 2px 6px;">
															<?php echo esc_html($omnify_access_status); ?>
														</span>
													</div>
													<div style="color: var(--omnify-gray-600); font-size: 10px;">
														v<?php echo esc_html($omnify_file['version']); ?> &middot; <?php echo esc_html($omnify_file['size_label']); ?>
													</div>
													<div style="display: flex; gap: 6px; margin-top: 4px;">
														<a href="<?php echo esc_url($omnify_download_url); ?>" class="omnify-button omnify-button--secondary omnify-button--sm" style="padding: 4px 8px; font-size: 11px;" target="_blank">
															<?php esc_html_e('Signed URL', 'omnifywp-ecommerce'); ?>
														</a>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
									<?php
								endif;
							endif;
						endforeach;
					endif;

					if (!$omnify_files_found) :
					?>
						<p style="font-size: 13px; color: var(--omnify-gray-600);"><?php esc_html_e('No digital files associated with products in this order.', 'omnifywp-ecommerce'); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
