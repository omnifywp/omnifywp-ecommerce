<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$page_id = 44;
$post = get_post($page_id);
if (!$post) {
    die("Page 44 not found!\n");
}

// Clean Gutenberg markup for page 44
$content = <<<'EOD'
<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"2rem"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="margin-bottom:2rem">
  <!-- wp:paragraph {"style":{"typography":{"fontSize":"1.125rem","lineHeight":"1.7"}}} -->
  <p style="font-size:1.125rem;line-height:1.7">Stay up to date with the latest improvements, security updates, and performance optimizations engineered into OmnifyWP eCommerce.</p>
  <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#D4E8DC","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#D4E8DC;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.65rem;font-weight:800;margin:0">Version 1.4.1</h2>
    <p class="om-badge om-badge--neutral" style="font-size:0.8rem;font-weight:700">Latest Stable Release • October 15, 2026</p>
  </div>
  <p>Major administrative maintenance, diagnostic tools overhaul, outbound webhooks with cryptographic HMAC verification, and server runtime inspections.</p>
  <ul class="wp-block-list om-check-list">
    <li><strong>Live Database Diagnostics KPI Bar:</strong> Real-time 4-card metric strip in Tools displaying exact row counts for Products, Orders, Customers, and Reviews across normalized tables.</li>
    <li><strong>Granular Database Cleanup Tools:</strong> Specialized individual cleanup operations for Products, Orders, Customers, Reviews, Coupons, and Activity Logs.</li>
    <li><strong>Server Runtime &amp; Specifications Panel:</strong> System inspector checking PHP version (8.2+), MySQL/MariaDB version, memory allocation limits, and timeouts.</li>
    <li><strong>Outbound Webhooks Manager:</strong> Support for registering external webhooks with HMAC-SHA256 signature verification headers (<code>X-Omnify-Signature</code>).</li>
    <li><strong>Payment Gateway Connectivity Diagnostics:</strong> Live API verification cards for Stripe and PayPal testing authentication credentials, environment mode, and roundtrip network latency.</li>
    <li><strong>Dataset Export &amp; Import Suite:</strong> Full CSV export for Products, Orders, and Customers with UTF-8 encoding; CSV drag-and-drop import container.</li>
    <li><strong>Zero-Emoji Compliance:</strong> Replaced character glyphs across administrative interfaces, scripts, and documentation with clean SVG icons and text labels.</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#E2E8F0","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#E2E8F0;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.5rem;font-weight:800;margin:0">Version 1.4.0</h2>
    <p class="om-text-muted" style="font-size:0.85rem;margin:0">Storefront 2.0 &amp; Catalog • October 14, 2026</p>
  </div>
  <p>Complete storefront experience overhaul inspired by luxury commerce brands, featuring client-side 4-layout grid switching, quick filter pills, floating product actions, and flash sale tickers.</p>
  <ul class="wp-block-list om-check-list">
    <li><strong>4-Layout Catalog Switcher Toolbar:</strong> 1-click layout switching between 4-column dense grid, 3-column standard grid, 2-column large grid, and detailed list view with <code>localStorage</code> persistence.</li>
    <li><strong>Quick Filter Pills Strip:</strong> Dynamic horizontal filter bar for 1-click client-side filtering by Flash Deals, Bestsellers, New In, and top categories.</li>
    <li><strong>Floating Product Card Actions:</strong> Hover toolbar featuring Wishlist toggle, Quick View modal launcher, and Product Compare trigger.</li>
    <li><strong>Interactive Color Swatches:</strong> Selectable color dots on catalog product cards with smooth active indicator transitions.</li>
    <li><strong>Flash Sale Announcement Ticker:</strong> Persistent countdown timer with 1-click coupon code copy button (<code>FLASH30</code>).</li>
    <li><strong>Recently Viewed Products Grid:</strong> Cookie-persisted recently viewed products section with responsive 4-column display.</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#E2E8F0","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#E2E8F0;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.5rem;font-weight:800;margin:0">Version 1.3.0</h2>
    <p class="om-text-muted" style="font-size:0.85rem;margin:0">Lifecycle &amp; Database Scale • October 8, 2026</p>
  </div>
  <ul class="wp-block-list om-check-list">
    <li><strong>Order Lifecycle Service:</strong> Unified order automation in <code>Omnify_Order_Lifecycle_Service</code> managing digital access entitlement, refund revocation, and cancellation stock restoration.</li>
    <li><strong>Compound Indexes:</strong> Added composite indexes on <code>omnify_orders</code> (<code>transaction_id</code>, <code>status</code>, <code>created_at</code>) for sub-millisecond lookups under high concurrency.</li>
    <li><strong>Khalti &amp; Paystack Security:</strong> Fixed live production endpoint resolution and enforced strict webhook signature verification.</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#E2E8F0","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#E2E8F0;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.5rem;font-weight:800;margin:0">Version 1.2.0</h2>
    <p class="om-text-muted" style="font-size:0.85rem;margin:0">Tracking &amp; Streaming • September 30, 2026</p>
  </div>
  <ul class="wp-block-list om-check-list">
    <li><strong>Public Order Tracking:</strong> Added guest order tracking shortcode <code>&#91;omnify_order_tracking&#93;</code> for public status verification and invoice lookup without account login.</li>
    <li><strong>Automated Gateway Refunds:</strong> Integrated live API refund execution across Paystack, Tap Payments, Mollie, Khalti, and eSewa.</li>
    <li><strong>Chunked File Streaming:</strong> Engineered 8KB chunked streaming with memory buffer flushes, preventing PHP memory exhaustion on large file deliveries.</li>
    <li><strong>Path Normalization:</strong> Resolved digital download permission edge cases on Windows environments with <code>wp_normalize_path()</code>.</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#E2E8F0","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#E2E8F0;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.5rem;font-weight:800;margin:0">Version 1.1.0</h2>
    <p class="om-text-muted" style="font-size:0.85rem;margin:0">Global Payment Milestone • September 15, 2026</p>
  </div>
  <ul class="wp-block-list om-check-list">
    <li><strong>Paystack Integration:</strong> Market-leading African gateway supporting cards, Mobile Money, and bank transfers.</li>
    <li><strong>Tap Payments Integration:</strong> Regional rails for GCC / Middle East (Saudi Arabia, UAE, Kuwait, Bahrain, Qatar) supporting mada, KNET, and Benefit.</li>
    <li><strong>Mollie EU Integration:</strong> European Union checkout supporting iDEAL, Bancontact, SEPA, and Klarna.</li>
    <li><strong>Khalti &amp; eSewa:</strong> Digital wallet and ePayment v2 SDK support for Nepal in NPR currency.</li>
  </ul>
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"width":"1px","color":"#E2E8F0","radius":"16px"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"},"margin":{"bottom":"2.5rem"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-color:#E2E8F0;border-width:1px;border-radius:16px;padding:2rem;margin-bottom:2.5rem">
  <div class="wp-block-group" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <h2 class="wp-block-heading has-ink-color has-text-color" style="font-size:1.5rem;font-weight:800;margin:0">Version 1.0.0</h2>
    <p class="om-text-muted" style="font-size:0.85rem;margin:0">Initial Release • September 1, 2026</p>
  </div>
  <ul class="wp-block-list om-check-list">
    <li>Engineered custom relational SQL tables (<code>wp_omnify_*</code>) replacing legacy <code>wp_postmeta</code> overhead.</li>
    <li>Frictionless 1-page checkout with guest support and dynamic country/tax autocomplete.</li>
    <li>Cryptographic digital locker with SHA-256 HMAC protected links.</li>
    <li>Native Stripe, PayPal, Razorpay, Alipay, WeChat Pay, and SSLCommerz gateways included.</li>
  </ul>
</div>
<!-- /wp:group -->
EOD;

wp_update_post([
    'ID' => $page_id,
    'post_content' => $content,
]);

update_post_meta($page_id, '_wp_page_template', 'page-changelog');

echo "Successfully updated Page 44 (Changelog) content and set template to page-changelog!\n";
