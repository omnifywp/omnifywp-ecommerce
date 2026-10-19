const fs = require('fs');

// 1. assets/storefront/storefront.js
let sjs = fs.readFileSync('assets/storefront/storefront.js', 'utf8');
sjs = sjs.replace("icon.textContent = wishlisted ? '♥' : '♡';", "icon.innerHTML = wishlisted ? '<span class=\"dashicons dashicons-heart\"></span>' : '<span class=\"dashicons dashicons-heart\" style=\"opacity:0.6;\"></span>';");
sjs = sjs.replace('<span class="omnify-toast__icon">✓</span>', '<span class="omnify-toast__icon"><span class="dashicons dashicons-yes"></span></span>');
sjs = sjs.replace('🎉 You have reached <strong>FREE SHIPPING!</strong>', 'You have reached <strong>FREE SHIPPING!</strong>');
sjs = sjs.replace("crossBtn.textContent = 'Added ✓';", "crossBtn.textContent = 'Added';");
sjs = sjs.replace('showCouponMsg(`✓ Coupon "', 'showCouponMsg(`Coupon "');
sjs = sjs.replace("couponApplyBtn.textContent = 'Applied ✓';", "couponApplyBtn.textContent = 'Applied';");
fs.writeFileSync('assets/storefront/storefront.js', sjs, 'utf8');

// 2. templates/admin/order-detail.php
let od = fs.readFileSync('templates/admin/order-detail.php', 'utf8');
od = od.replace('<span>🔄 ', '<span><span class="dashicons dashicons-update" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span>');
od = od.replace('<span>🚫 ', '<span><span class="dashicons dashicons-dismiss" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span>');
od = od.replace("✓ <?php esc_html_e('Mark as Paid', 'omnifywp-ecommerce'); ?>", '<span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Mark as Paid\', \'omnifywp-ecommerce\'); ?>');
od = od.replace('<div class="omnify-timeline-item__bullet">💬</div>', '<div class="omnify-timeline-item__bullet"><span class="dashicons dashicons-testimonial" style="font-size:12px; width:12px; height:12px;"></span></div>');
od = od.replace('<h2>🛡️  <?php esc_html_e(\'Fraud & Risk Assessment\', \'omnifywp-ecommerce\'); ?></h2>', '<h2><span class="dashicons dashicons-shield" style="font-size:18px; width:18px; height:18px; vertical-align:middle; margin-right:6px;"></span><?php esc_html_e(\'Fraud & Risk Assessment\', \'omnifywp-ecommerce\'); ?></h2>');
od = od.replace("✓ <?php esc_html_e('Clear Flags & Approve', 'omnifywp-ecommerce'); ?>", '<span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Clear Flags & Approve\', \'omnifywp-ecommerce\'); ?>');
od = od.replace("📦 <?php esc_html_e('Mark as Fulfilled', 'omnifywp-ecommerce'); ?>", '<span class="dashicons dashicons-archive" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Mark as Fulfilled\', \'omnifywp-ecommerce\'); ?>');
fs.writeFileSync('templates/admin/order-detail.php', od, 'utf8');

// 3. templates/admin/setup-wizard.php
let sw = fs.readFileSync('templates/admin/setup-wizard.php', 'utf8');
sw = sw.replace('<div class="panel-finish-icon">🎉</div>', '<div class="panel-finish-icon"><span class="dashicons dashicons-yes-alt" style="font-size:48px; width:48px; height:48px; color:var(--omnify-primary, #0f766e);"></span></div>');
fs.writeFileSync('templates/admin/setup-wizard.php', sw, 'utf8');

// 4. templates/storefront/cart.php
let cart = fs.readFileSync('templates/storefront/cart.php', 'utf8');
cart = cart.replace('<span class="omnify-urgency-icon">⚡</span>', '<span class="omnify-urgency-icon"><span class="dashicons dashicons-clock" style="font-size:14px; width:14px; height:14px; line-height:1; vertical-align:middle;"></span></span>');
cart = cart.replace('<span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #16A34A; color: #fff; font-size: 11px;">✓</span>', '<span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #16A34A; color: #fff; font-size: 11px;"><span class="dashicons dashicons-yes" style="font-size:12px; width:12px; height:12px; line-height:1;"></span></span>');
cart = cart.replace('✕ Remove', '&times; Remove');
cart = cart.replace('<span>🔒 256-Bit Encryption</span>', '<span><span class="dashicons dashicons-lock" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:4px;"></span>256-Bit Encryption</span>');
cart = cart.replace('<span>⚡ Instant Access</span>', '<span><span class="dashicons dashicons-download" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:4px;"></span>Instant Access</span>');
fs.writeFileSync('templates/storefront/cart.php', cart, 'utf8');

// 5. templates/storefront/checkout-page.php
let co = fs.readFileSync('templates/storefront/checkout-page.php', 'utf8');
co = co.replace('<div style="font-size: 48px; margin-bottom: 20px;">🛒</div>', '<div style="font-size: 48px; margin-bottom: 20px;"><span class="dashicons dashicons-cart" style="font-size:48px; width:48px; height:48px; color:var(--checkout-primary, #0f766e);"></span></div>');
co = co.replace('<span class="omnify-step-number" style="background: #18794E !important; color: #FFFFFF !important;">✓</span>', '<span class="omnify-step-number" style="background: #18794E !important; color: #FFFFFF !important;"><span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; line-height:1;"></span></span>');
co = co.replace("👤 <?php esc_html_e('Returning customer?', 'omnifywp-ecommerce'); ?>", '<span class="dashicons dashicons-admin-users" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Returning customer?\', \'omnifywp-ecommerce\'); ?>');
co = co.replace('<span>📝</span>', '<span><span class="dashicons dashicons-edit" style="font-size:14px; width:14px; height:14px; vertical-align:middle;"></span></span>');
co = co.replace('⚡ <?php echo esc_html($omnify_bump_title); ?>', '<span class="dashicons dashicons-star-filled" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px; color:#f59e0b;"></span><?php echo esc_html($omnify_bump_title); ?>');
co = co.replace("🏷️ <?php esc_html_e('Have a coupon code?', 'omnifywp-ecommerce'); ?>", '<span class="dashicons dashicons-tag" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Have a coupon code?\', \'omnifywp-ecommerce\'); ?>');
co = co.replace('<span>✓ <strong id="omnify-checkout-applied-code"', '<span><span class="dashicons dashicons-yes" style="font-size:12px; width:12px; height:12px; vertical-align:middle;"></span> <strong id="omnify-checkout-applied-code"');
co = co.replace('<span id="omnify-summary-bump-label">⚡ ', '<span id="omnify-summary-bump-label\"><span class="dashicons dashicons-star-filled" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:3px; color:#f59e0b;"></span>');
co = co.replace('<strong>⚡ <?php echo $omnify_is_physical_checkout', '<strong><span class="dashicons dashicons-clock" style="font-size:12px; width:12px; height:12px; vertical-align:middle; margin-right:3px;"></span><?php echo $omnify_is_physical_checkout');
co = co.replace('>✓</div>', '><span class="dashicons dashicons-yes" style="font-size:32px; width:32px; height:32px; line-height:1;"></span></div>');
fs.writeFileSync('templates/storefront/checkout-page.php', co, 'utf8');

// 6. templates/storefront/portal.php
let port = fs.readFileSync('templates/storefront/portal.php', 'utf8');
port = port.replace("<span>🔄 <?php esc_html_e('Request a refund for this order', 'omnifywp-ecommerce'); ?></span>", '<span><span class="dashicons dashicons-undo" style="font-size:14px; width:14px; height:14px; vertical-align:middle; margin-right:4px;"></span><?php esc_html_e(\'Request a refund for this order\', \'omnifywp-ecommerce\'); ?></span>');
fs.writeFileSync('templates/storefront/portal.php', port, 'utf8');

// 7. templates/storefront/product-single.php
let ps = fs.readFileSync('templates/storefront/product-single.php', 'utf8');
ps = ps.replace("id=\"pdp-clear-var-link\">✕ <?php esc_html_e('Clear selection', 'omnifywp-ecommerce'); ?>", 'id="pdp-clear-var-link">&times; <?php esc_html_e(\'Clear selection\', \'omnifywp-ecommerce\'); ?>');
fs.writeFileSync('templates/storefront/product-single.php', ps, 'utf8');

console.log('Clean emojis executed successfully');
