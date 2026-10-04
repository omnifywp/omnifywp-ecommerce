const fs = require('fs');
const path = require('path');
const { mockups } = require('./mockups.js');

console.log('Generating OmnifyWP eCommerce User Documentation Website...');

// Data structure for all chapters and subsections
const chapters = [
  {
    id: 'intro',
    title: '1. Introduction to OmnifyWP',
    badge: 'Overview & Philosophy',
    icon: '✨',
    summary: 'Discover how OmnifyWP eCommerce delivers ultra-high performance and a modern commerce experience on WordPress.',
    content: `
      <p class="lead-text">
        <strong>OmnifyWP eCommerce</strong> is an enterprise-grade, high-performance eCommerce solution engineered specifically for modern WordPress sites. By completely bypassing the legacy <code>wp_posts</code> and <code>wp_postmeta</code> bottleneck, OmnifyWP delivers sub-millisecond page loads, military-grade digital asset security, and high-converting checkouts.
      </p>

      <div class="grid-cards-3">
        <div class="feature-card">
          <div class="feature-card-icon">⚡</div>
          <h4>Zero-Postmeta Latency</h4>
          <p>Product, order, customer, and inventory records live in 18 dedicated normalized relational SQL tables with optimized multi-column indexes, achieving sub-millisecond queries even with catalogs of millions of items.</p>
        </div>
        <div class="feature-card">
          <div class="feature-card-icon">🔒</div>
          <h4>Cryptographic Digital Locker</h4>
          <p>Deliver software, digital downloads, courses, and media securely with SHA-256 HMAC-signed URLs, time-based expiration, download caps, and automated file revocation upon refund.</p>
        </div>
        <div class="feature-card">
          <div class="feature-card-icon">🛍️</div>
          <h4>Conversion-First Storefront</h4>
          <p>Modern responsive shop grid, sticky product cards, variant swatches, urgency badges, real-time cart scarcity timers, and frictionless single/multi-step checkouts designed to convert.</p>
        </div>
      </div>

      <h3>Architectural Comparison: OmnifyWP vs Legacy WooCommerce</h3>
      <table class="doc-table">
        <thead>
          <tr>
            <th>Architecture Dimension</th>
            <th>Legacy WooCommerce</th>
            <th>OmnifyWP eCommerce</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Database Storage</strong></td>
            <td>Overloaded <code>wp_posts</code> & <code>wp_postmeta</code> EAV table joins</td>
            <td><strong>18 Dedicated Normalized SQL Tables</strong> (Clean Separation)</td>
          </tr>
          <tr>
            <td><strong>Query Execution Time</strong></td>
            <td>45ms – 250ms+ under large catalogs</td>
            <td><strong>0.4ms – 2.5ms</strong> indexed direct SQL queries</td>
          </tr>
          <tr>
            <td><strong>Digital Asset Delivery</strong></td>
            <td>Basic PHP redirects or exposed download URLs</td>
            <td><strong>HMAC-SHA256 Signed Tokens</strong>, 8KB chunk streaming</td>
          </tr>
          <tr>
            <td><strong>Refund Asset Security</strong></td>
            <td>Manual access revocation required</td>
            <td><strong>Instant Automatic File Revocation</strong> on order refund</td>
          </tr>
          <tr>
            <td><strong>Block Editor Integration</strong></td>
            <td>Heavy legacy widgets & shortcode wrappers</td>
            <td><strong>100% Native Gutenberg Full Site Editing</strong> blocks</td>
          </tr>
        </tbody>
      </table>

      <div class="callout tip">
        <div class="callout-icon">💡</div>
        <div class="callout-body">
          <strong>Built for Scale:</strong> Your blog posts, pages, and custom post types remain completely isolated from high-volume catalog queries. Backups, queries, and caching remain lightning fast as your store scales to millions in revenue.
        </div>
      </div>
    `
  },
  {
    id: 'onboarding',
    title: '2. Installation & Setup Wizard',
    badge: 'Getting Started',
    icon: '🚀',
    summary: 'Step-by-step installation instructions, system prerequisites, and the 4-step store setup wizard.',
    content: `
      <p>Getting your store live with OmnifyWP eCommerce takes less than five minutes. The built-in setup wizard automatically configures core currency settings, payment gateways, and necessary storefront shortcodes.</p>

      <h3>System Requirements</h3>
      <table class="doc-table">
        <thead>
          <tr><th>Component</th><th>Minimum Required</th><th>Recommended</th></tr>
        </thead>
        <tbody>
          <tr><td>WordPress</td><td>6.5+</td><td>Latest Stable (Block Editor Enabled)</td></tr>
          <tr><td>PHP Version</td><td>8.2+</td><td>8.2 or 8.3 (with curl, mbstring, openssl, json)</td></tr>
          <tr><td>Database</td><td>MySQL 5.7+ / MariaDB 10.3+</td><td>MySQL 8.0+ / MariaDB 10.6+</td></tr>
          <tr><td>Web Server</td><td>Nginx or Apache with mod_rewrite</td><td>Nginx with HTTP/2 or HTTP/3</td></tr>
          <tr><td>SSL Certificate</td><td>HTTPS Required</td><td>Strict Transport Security (HSTS) Active</td></tr>
        </tbody>
      </table>

      <h3>Onboarding Setup Wizard</h3>
      <p>Upon plugin activation, navigate to <strong>Omnify → Setup Wizard</strong> to initialize your store in 4 simple guided steps:</p>
      <ul class="styled-list">
        <li><strong>Step 1: Store Profile:</strong> Business name, operational headquarters, physical address, and contact email.</li>
        <li><strong>Step 2: Currency & Locale:</strong> Operational base currency, thousand/decimal separator, symbol placement, and measurement units.</li>
        <li><strong>Step 3: Payment Gateways:</strong> One-click enablement for Stripe Elements, PayPal Commerce, Razorpay, SSLCommerz, and Cash on Delivery.</li>
        <li><strong>Step 4: Page Generation:</strong> Automatic creation and shortcode binding for Storefront, Cart, Checkout, Customer Portal, and Order Tracking.</li>
      </ul>

      ${mockups.setup_wizard}

      <h3>Generated Core Store Pages</h3>
      <p>OmnifyWP automatically generates dedicated pages with embedded shortcodes:</p>
      <ul class="styled-list">
        <li><strong>Storefront (<code>/storefront/</code>):</strong> Houses <code>[omnify_storefront]</code> to display the responsive product catalog grid.</li>
        <li><strong>Cart (<code>/cart/</code>):</strong> Houses <code>[omnify_cart]</code> for interactive line-item management and coupon applications.</li>
        <li><strong>Checkout (<code>/checkout/</code>):</strong> Houses <code>[omnify_checkout]</code> for high-converting single/multi-step checkout.</li>
        <li><strong>Customer Portal (<code>/customer-portal/</code>):</strong> Houses <code>[omnify_customer_portal]</code> for buyer account management and downloads.</li>
        <li><strong>Order Tracking (<code>/order-tracking/</code>):</strong> Houses <code>[omnify_order_tracking]</code> for guest order status lookups.</li>
        <li><strong>Secure Download (<code>/secure-download/</code>):</strong> Houses <code>[omnify_download_page]</code> for encrypted streaming delivery.</li>
      </ul>
    `
  },
  {
    id: 'dashboard',
    title: '3. Store Dashboard Overview',
    badge: 'Analytics & KPIs',
    icon: '📊',
    summary: 'Real-time financial KPI cards, store status badges, recent transactions feed, and one-click quick actions.',
    content: `
      <p>The <strong>Omnify Dashboard</strong> (located under <strong>Omnify → Dashboard</strong>) serves as the executive command center for store health, performance trends, and real-time transaction activity.</p>

      ${mockups.dashboard}

      <h3>Key Performance Indicators (KPI Cards)</h3>
      <div class="grid-cards-2">
        <div class="feature-card">
          <h4>Total Revenue</h4>
          <p>Cumulative gross revenues generated by paid orders, complete with percentage change indicators compared against earlier sales periods.</p>
        </div>
        <div class="feature-card">
          <h4>Total Orders</h4>
          <p>Total transaction volume across all order states, allowing you to gauge operational velocity and sales velocity.</p>
        </div>
        <div class="feature-card">
          <h4>Active Customers</h4>
          <p>Total verified customer accounts and repeat purchasing records preserved in the custom normalized CRM schema.</p>
        </div>
        <div class="feature-card">
          <h4>Digital Downloads</h4>
          <p>Count of authorized cryptographic digital downloads streamed safely to verified purchasers with zero memory leakage.</p>
        </div>
      </div>

      <h3>Quick Actions Panel</h3>
      <p>Use the right-hand action shortcuts to rapidly perform routine storekeeper operations:</p>
      <ul class="styled-list">
        <li><strong>+ Add New Product:</strong> Jump straight into the comprehensive product authoring suite.</li>
        <li><strong>Create Coupon:</strong> Launch a flash sale or custom discount code promotion.</li>
        <li><strong>Export Accounting CSV:</strong> Generate QuickBooks and Xero compatible financial exports.</li>
        <li><strong>Verify 18 SQL Tables:</strong> Run instant schema diagnostic checks across all custom tables.</li>
      </ul>
    `
  },
  {
    id: 'products',
    title: '4. Product Catalog Management',
    badge: 'Catalog & Stock',
    icon: '📦',
    summary: 'Create and edit Physical, Variable, Digital, and Bundled products, configure pricing, and manage inventory.',
    content: `
      <p>Navigate to <strong>Omnify → Products</strong> to view, search, and organize your store's inventory. Products can be filtered by taxonomy, type, status, and creation date, or exported to CSV.</p>

      ${mockups.products_list}

      <h3>Supported Product Types</h3>
      <ul class="styled-list">
        <li><strong>Digital / Downloadable:</strong> Software applications, WordPress plugins/themes, eBooks, audio/video lessons, presets, and digital graphics linked to secure cryptographic file delivery lockers.</li>
        <li><strong>Physical Products:</strong> Tangible items requiring weight, package dimensions, stock tracking, and shipping calculations.</li>
        <li><strong>Variable Products:</strong> Configurable products offering variation matrices based on attributes (e.g. License Tier: Single Site / 5 Sites / Agency, or Size / Color).</li>
        <li><strong>Product Bundles:</strong> Curated package deals grouping multiple digital or physical assets into a single discounted SKU.</li>
      </ul>

      <h3>Product Editor Interface</h3>
      <p>Click <strong>Create Product</strong> or edit an existing item to access the product management workspace.</p>

      ${mockups.product_editor}

      <h3>Product Taxonomies</h3>
      <p>Organize your catalog with structured taxonomies that drive storefront filtering, search, and navigation:</p>

      ${mockups.categories}

      ${mockups.tags}

      ${mockups.brands}

      ${mockups.attributes}
    `
  },
  {
    id: 'orders',
    title: '5. Order Fulfillment & Refunds',
    badge: 'Sales & Operations',
    icon: '🛒',
    summary: 'Manage incoming orders, review line items, inspect fraud risk scores, print packing slips, and issue refunds.',
    content: `
      <p>The orders fulfillment center under <strong>Omnify → Orders</strong> gives you complete control over transaction statuses, fulfillment workflows, and customer updates.</p>

      ${mockups.orders_list}

      <h3>Order Lifecycle Workflows</h3>
      <table class="doc-table">
        <thead>
          <tr><th>Status</th><th>Meaning</th><th>Digital Locker Action</th></tr>
        </thead>
        <tbody>
          <tr><td><span class="badge warning">Pending</span></td><td>Order created; awaiting customer payment clearance.</td><td>Access withheld</td></tr>
          <tr><td><span class="badge info">Processing</span></td><td>Payment confirmed; awaiting physical packing or delivery.</td><td>Access active</td></tr>
          <tr><td><span class="badge success">Completed</span></td><td>Order successfully fulfilled and dispatched to customer.</td><td>Access active</td></tr>
          <tr><td><span class="badge neutral">On Hold</span></td><td>Awaiting manual verification (e.g. Bank Transfer / Wire).</td><td>Access withheld</td></tr>
          <tr><td><span class="badge danger">Cancelled</span></td><td>Order terminated before payment processing.</td><td>Access withheld</td></tr>
          <tr><td><span class="badge danger">Refunded</span></td><td>Transaction reversed; funds returned to buyer.</td><td>Access automatically revoked</td></tr>
        </tbody>
      </table>

      <h3>Order Detail, Fraud Assessment & Refunds</h3>
      <p>Click on any order to view line items, customer addresses, transaction records, and the fraud risk assessment badge.</p>

      ${mockups.order_detail}

      <div class="callout warning">
        <div class="callout-icon">⚠️</div>
        <div class="callout-body">
          <strong>Automatic File Revocation:</strong> Processing a full refund automatically revokes customer access to digital files, immediately canceling existing download tokens to protect your intellectual property.
        </div>
      </div>
    `
  },
  {
    id: 'customers',
    title: '6. Customer Management (CRM)',
    badge: 'Profiles & LTV',
    icon: '👥',
    summary: 'Centralized directory of customer profiles, purchase histories, lifetime value calculations, and address books.',
    content: `
      <p>Under <strong>Omnify → Customers</strong>, store owners have a built-in CRM directory capturing valuable buyer intelligence without requiring third-party plugins.</p>

      ${mockups.customers}

      <h3>Customer Profile Capabilities</h3>
      <ul class="styled-list">
        <li><strong>Lifetime Value (LTV):</strong> Track cumulative gross revenues contributed by individual shoppers to identify high-value VIP buyers.</li>
        <li><strong>Order Histories:</strong> Direct chronological access to every transaction placed by the customer.</li>
        <li><strong>Saved Address Books:</strong> View and manage customer billing and shipping addresses for smooth reordering.</li>
        <li><strong>Digital Access Rights:</strong> Inspect which software licenses or downloadable products are currently assigned to the user.</li>
      </ul>
    `
  },
  {
    id: 'coupons',
    title: '7. Promotions & Coupons Engine',
    badge: 'Marketing & Discounts',
    icon: '🎟️',
    summary: 'Create percentage or fixed discount coupons with minimum spend thresholds, usage caps, and expiration limits.',
    content: `
      <p>Run targeted promotions, seasonal flash sales, and customer loyalty rewards via <strong>Omnify → Coupons</strong>.</p>

      ${mockups.coupons}

      <h3>Discount Types</h3>
      <ul class="styled-list">
        <li><strong>Percentage Discount (%):</strong> Deducts a percentage from the eligible cart items (e.g. 20% off entire cart).</li>
        <li><strong>Fixed Cart Discount ($):</strong> Deducts a fixed currency amount from the order total (e.g. $15 off orders over $100).</li>
        <li><strong>Fixed Product Discount ($):</strong> Applies a fixed currency discount per individual eligible product unit.</li>
      </ul>

      <h3>Usage Restrictions & Rules</h3>
      <p>Fine-tune coupon eligibility to prevent discount abuse:</p>
      <ul class="styled-list">
        <li><strong>Minimum & Maximum Spend:</strong> Establish subtotal spend boundaries required to activate the promo.</li>
        <li><strong>Individual Use Only:</strong> Prevent shoppers from stacking promotional codes.</li>
        <li><strong>Usage Limit per Coupon:</strong> Cap the total times a coupon can be redeemed store-wide.</li>
        <li><strong>Usage Limit per User:</strong> Limit redemptions per customer email address (e.g. one-time use per customer).</li>
        <li><strong>Expiration Timers:</strong> Set scheduled auto-expiry dates for seasonal marketing campaigns.</li>
      </ul>
    `
  },
  {
    id: 'analytics',
    title: '8. Store Analytics & Reports',
    badge: 'Business Intelligence',
    icon: '📈',
    summary: 'Monitor sales trends, net revenues, Average Order Value (AOV), refund rates, and export accounting CSVs.',
    content: `
      <p>Under <strong>Omnify → Analytics</strong>, gain instant visibility into store health through interactive charts and performance summaries.</p>

      ${mockups.analytics}

      <h3>Core Financial Metrics</h3>
      <div class="grid-cards-3">
        <div class="feature-card">
          <h4>Gross Sales</h4>
          <p>Total transaction values before deductions, discounts, or refunds.</p>
        </div>
        <div class="feature-card">
          <h4>Net Sales</h4>
          <p>Gross revenues minus promo discounts and completed refunds.</p>
        </div>
        <div class="feature-card">
          <h4>Average Order Value (AOV)</h4>
          <p>Average transaction size, helping evaluate upsell and cross-sell effectiveness.</p>
        </div>
      </div>

      <div class="callout tip">
        <div class="callout-icon">📊</div>
        <div class="callout-body">
          <strong>CSV Export for Accounting:</strong> Click <strong>Export Report (CSV)</strong> in the top-right corner to export comprehensive financial data formatted for QuickBooks, Xero, or custom spreadsheet analysis.
        </div>
      </div>
    `
  },
  {
    id: 'reviews',
    title: '9. Customer Reviews Moderation',
    badge: 'Social Proof',
    icon: '⭐',
    summary: 'Review moderation queue, verified buyer badges, star ratings management, and spam protection.',
    content: `
      <p>Customer testimonials build purchasing confidence. Navigate to <strong>Omnify → Reviews</strong> to moderate submitted buyer reviews and ratings.</p>

      ${mockups.reviews}

      <h3>Review Management Features</h3>
      <ul class="styled-list">
        <li><strong>Verified Buyer Badge:</strong> Automatically indicates whether the reviewer purchased the product from your store.</li>
        <li><strong>Moderation Statuses:</strong> Mark reviews as <code>Approved</code>, <code>Unapproved</code> (held for moderation), or move to <code>Trash</code>.</li>
        <li><strong>Star Ratings:</strong> Visual 1-5 star ratings displayed on storefront product cards and detail pages.</li>
      </ul>
    `
  },
  {
    id: 'abandoned-carts',
    title: '10. Abandoned Cart Recovery',
    badge: 'Revenue Recovery',
    icon: '🛒',
    summary: 'Automatically track dropped checkout sessions and recover lost sales with scheduled reminders.',
    content: `
      <p>Under <strong>Omnify → Abandoned Carts</strong>, OmnifyWP tracks incomplete checkout sessions and helps recover potential lost revenue automatically.</p>

      ${mockups.abandoned_carts}

      <h3>Recovery Workflow</h3>
      <ol class="styled-list">
        <li><strong>Email Capture:</strong> As soon as a customer enters their email address during checkout, their cart state is recorded.</li>
        <li><strong>Threshold Evaluation:</strong> If no completed order occurs within 60 minutes, the session is flagged as <code>Abandoned</code>.</li>
        <li><strong>Automated Reminder:</strong> Dispatches personalized recovery reminder emails containing direct one-click checkout restoration links.</li>
        <li><strong>Conversion Attribution:</strong> Once purchased, the status transitions to <code>Recovered</code>, updating your recovery metrics.</li>
      </ol>
    `
  },
  {
    id: 'settings',
    title: '11. Store Configuration & Settings',
    badge: 'Core Configuration',
    icon: '⚙️',
    summary: 'Configure store currencies, payment processors, delivery methods, tax rates, emails, and checkout rules.',
    content: `
      <p>Under <strong>Omnify → Settings</strong>, customize your store's business logic, regional formats, payment processors, and notification templates.</p>

      <h3>General Settings</h3>
      <p>Configure store currency, symbol placement, decimal formatting, and physical measurement units.</p>
      ${mockups.settings_general}

      <h3>Payment Gateways</h3>
      <p>Connect and manage integrated payment processors for your store:</p>
      ${mockups.settings_payments}

      <div class="grid-cards-2">
        <div class="feature-card">
          <h4>Stripe Elements</h4>
          <p>Credit/debit cards, Apple Pay, Google Pay, and localized EU payment options with automated webhook verification.</p>
        </div>
        <div class="feature-card">
          <h4>PayPal Commerce</h4>
          <p>Direct PayPal Express checkout, Pay in 4 installment financing, and credit card processing.</p>
        </div>
        <div class="feature-card">
          <h4>Regional Gateways</h4>
          <p>Pre-integrated support for Razorpay (India: UPI, NetBanking, RuPay) and SSLCommerz (Bangladesh: bKash, Nagad).</p>
        </div>
        <div class="feature-card">
          <h4>Manual Methods</h4>
          <p>Configurable workflows for Cash on Delivery (COD), Direct Bank Wire Transfer (BACS), and Cheque Payments.</p>
        </div>
      </div>

      <h3>Delivery & Shipping Options</h3>
      ${mockups.settings_delivery}

      <h3>Taxes & Calculations</h3>
      ${mockups.settings_taxes}

      <h3>Automated Email Notifications</h3>
      ${mockups.settings_emails}

      <h3>Checkout Experience</h3>
      ${mockups.settings_checkout}

      <h3>Store Page Mapping</h3>
      ${mockups.settings_pages}
    `
  },
  {
    id: 'tools',
    title: '12. System Tools & Maintenance',
    badge: 'Diagnostics & Seed',
    icon: '🛠️',
    summary: 'One-click demo data generation and cleanup, database table verification, and cache flushing.',
    content: `
      <p>Under <strong>Omnify → Tools & Seeding</strong>, manage maintenance operations and developer utilities.</p>

      ${mockups.tools}

      <h3>Available Tools</h3>
      <ul class="styled-list">
        <li><strong>Generate Demo Data:</strong> Seeds 50 sample products, orders, customers, and categories to preview themes.</li>
        <li><strong>Clear Demo Data:</strong> Safely removes generated demo records without affecting real customer purchases.</li>
        <li><strong>Flush Transients & Caches:</strong> Flushes cached queries and price calculations to reflect catalog changes immediately.</li>
        <li><strong>Verify Database Tables:</strong> Validates indexes and schemas across all 18 custom Omnify database tables.</li>
      </ul>
    `
  },
  {
    id: 'activity',
    title: '13. Administrative Activity Log',
    badge: 'Audit Trail',
    icon: '📝',
    summary: 'Maintain an immutable audit trail of administrator actions, price changes, and order modifications.',
    content: `
      <p>Under <strong>Omnify → Activity Log</strong>, view a complete chronological audit trail of administrative changes for accountability and security.</p>

      ${mockups.activity_log}

      <h3>Tracked Events Include</h3>
      <ul class="styled-list">
        <li>Product additions, price changes, and inventory updates.</li>
        <li>Order status transitions and processed refund amounts.</li>
        <li>Coupon creation, updates, and removals.</li>
        <li>Payment gateway credential modifications and system setting edits.</li>
      </ul>
    `
  },
  {
    id: 'api',
    title: '14. REST API Key Management',
    badge: 'Headless & API',
    icon: '🔑',
    summary: 'Generate secure Consumer Key and Consumer Secret pairs for headless frontends, mobile apps, and ERP sync.',
    content: `
      <p>Under <strong>Omnify → API Keys</strong>, generate and manage REST API credentials for external integrations.</p>

      ${mockups.api_keys}

      <h3>Generating API Credentials</h3>
      <ol class="styled-list">
        <li>Click <strong>+ Generate API Key</strong>.</li>
        <li>Provide a descriptive label (e.g. <em>Next.js Headless Front</em> or <em>iOS Mobile App</em>).</li>
        <li>Select permission scope: <code>Read</code>, <code>Write</code>, or <code>Read/Write</code>.</li>
        <li>Save to reveal the generated <strong>Consumer Key</strong> and <strong>Consumer Secret</strong>.</li>
      </ol>
    `
  },
  {
    id: 'storefront',
    title: '15. Customer Storefront Experience',
    badge: 'Shopper Journey',
    icon: '🌐',
    summary: 'Walkthrough of the customer experience: catalog grid, product pages, cart, checkout, and portal.',
    content: `
      <p>OmnifyWP delivers a fast, modern shopping experience with clean layouts and responsive controls.</p>

      <h3>Product Catalog Grid (<code>[omnify_storefront]</code>)</h3>
      ${mockups.storefront_catalog}

      <h3>Product Details Page</h3>
      ${mockups.storefront_product}

      <h3>Shopping Cart (<code>[omnify_cart]</code>)</h3>
      ${mockups.storefront_cart}

      <h3>Conversion-Optimized Checkout (<code>[omnify_checkout]</code>)</h3>
      ${mockups.storefront_checkout}

      <h3>Customer Portal & Public Order Tracking</h3>
      <p>Customers can manage their past orders, addresses, and digital downloads in the Customer Portal (<code>[omnify_customer_portal]</code>) or track purchases via the public Order Tracking page (<code>[omnify_order_tracking]</code>).</p>

      ${mockups.customer_portal}

      ${mockups.order_tracking}
    `
  },
  {
    id: 'faq',
    title: '16. FAQ & Troubleshooting',
    badge: 'Help & Support',
    icon: '❓',
    summary: 'Frequently asked questions, common troubleshooting scenarios, and performance tips.',
    content: `
      <div class="faq-item">
        <h4>Q: How does OmnifyWP achieve sub-millisecond database queries?</h4>
        <p>OmnifyWP stores products, variations, orders, and customer data in 18 dedicated normalized SQL tables (<code>wp_omnify_*</code>) with explicit multi-column indexes, bypassing WordPress's shared <code>wp_posts</code> and <code>wp_postmeta</code> tables completely.</p>
      </div>

      <div class="faq-item">
        <h4>Q: How do digital download links prevent unauthorized file sharing?</h4>
        <p>Download links use time-limited HMAC-SHA256 signatures salted with your WordPress secret keys. Actual server file paths are never exposed, and downloads are served via memory-safe 8KB chunk streaming with download attempt limits.</p>
      </div>

      <div class="faq-item">
        <h4>Q: How do I test Stripe payments without charging real credit cards?</h4>
        <p>Navigate to <strong>Omnify → Settings → Payments</strong>, toggle <strong>Test Mode</strong> on, enter your Stripe Test Publishable and Secret Keys, and use Stripe's standard test card numbers (e.g. <code>4242 4242 4242 4242</code>).</p>
      </div>

      <div class="faq-item">
        <h4>Q: What happens to customer download access when an order is refunded?</h4>
        <p>When an order is refunded in <strong>Omnify → Orders</strong>, OmnifyWP immediately revokes all associated download tokens, preventing future file access.</p>
      </div>

      <div class="faq-item">
        <h4>Q: Can OmnifyWP run alongside standard WordPress block themes?</h4>
        <p>Yes! OmnifyWP is 100% Full Site Editing (FSE) ready and includes native Gutenberg blocks, patterns, and templates. It works seamlessly with block themes, classic themes, and headless frontends (Next.js, Nuxt).</p>
      </div>

      <div class="faq-item">
        <h4>Q: Where can I get support or report an issue?</h4>
        <p>Visit the official website at <a href="https://omnifywp.com" target="_blank" rel="noopener">omnifywp.com</a> or our plugin page on <a href="https://wordpress.org/plugins/omnifywp-ecommerce/" target="_blank" rel="noopener">wp.org</a>.</p>
      </div>
    `
  }
];

// Generate Search Index JSON
const searchIndex = [];
chapters.forEach(ch => {
  searchIndex.push({
    title: ch.title,
    snippet: ch.summary,
    url: '#' + ch.id
  });
});

// Build Sidebar Navigation HTML
const sidebarNavHtml = `
  <div class="sidebar-group">
    <div class="sidebar-heading">Getting Started</div>
    <ul class="sidebar-items">
      <li><a href="#intro" class="sidebar-link active"><span>✨ Introduction</span> <span class="sidebar-tag">Start</span></a></li>
      <li><a href="#onboarding" class="sidebar-link"><span>🚀 Setup Wizard</span> <span class="sidebar-tag">Setup</span></a></li>
      <li><a href="#dashboard" class="sidebar-link"><span>📊 Dashboard Overview</span> <span class="sidebar-tag">KPIs</span></a></li>
    </ul>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-heading">Catalog & Fulfillment</div>
    <ul class="sidebar-items">
      <li><a href="#products" class="sidebar-link"><span>📦 Products Catalog</span> <span class="sidebar-tag">Stock</span></a></li>
      <li><a href="#orders" class="sidebar-link"><span>🛒 Orders & Refunds</span> <span class="sidebar-tag">Sales</span></a></li>
      <li><a href="#customers" class="sidebar-link"><span>👥 Customers (CRM)</span> <span class="sidebar-tag">LTV</span></a></li>
      <li><a href="#coupons" class="sidebar-link"><span>🎟️ Coupons & Promos</span> <span class="sidebar-tag">Deals</span></a></li>
    </ul>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-heading">Analytics & Marketing</div>
    <ul class="sidebar-items">
      <li><a href="#analytics" class="sidebar-link"><span>📈 Store Analytics</span> <span class="sidebar-tag">Reports</span></a></li>
      <li><a href="#reviews" class="sidebar-link"><span>⭐ Reviews Moderation</span> <span class="sidebar-tag">Trust</span></a></li>
      <li><a href="#abandoned-carts" class="sidebar-link"><span>🛒 Abandoned Carts</span> <span class="sidebar-tag">Recovery</span></a></li>
    </ul>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-heading">Store Configuration</div>
    <ul class="sidebar-items">
      <li><a href="#settings" class="sidebar-link"><span>⚙️ Store Settings</span> <span class="sidebar-tag">Config</span></a></li>
      <li><a href="#tools" class="sidebar-link"><span>🛠️ Tools & Seeding</span> <span class="sidebar-tag">DB</span></a></li>
      <li><a href="#activity" class="sidebar-link"><span>📝 Activity Audit Log</span> <span class="sidebar-tag">Audit</span></a></li>
      <li><a href="#api" class="sidebar-link"><span>🔑 REST API Keys</span> <span class="sidebar-tag">REST</span></a></li>
    </ul>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-heading">Storefront & Support</div>
    <ul class="sidebar-items">
      <li><a href="#storefront" class="sidebar-link"><span>🌐 Customer Storefront</span> <span class="sidebar-tag">Shop</span></a></li>
      <li><a href="#faq" class="sidebar-link"><span>❓ FAQ & Troubleshooting</span> <span class="sidebar-tag">Help</span></a></li>
    </ul>
  </div>
`;

// Build Table of Contents for Right Rail
const tocHtml = chapters.map(ch => `
  <li><a href="#${ch.id}" class="toc-link">${ch.title.split('. ')[1] || ch.title}</a></li>
`).join('');

// Build Main Content Sections
const mainContentHtml = chapters.map(ch => `
  <section id="${ch.id}" class="doc-section">
    <div class="section-header">
      <div class="section-meta">
        <span class="section-badge">${ch.badge}</span>
      </div>
      <h2 class="section-title"><span class="section-icon">${ch.icon}</span> ${ch.title}</h2>
      <p class="section-summary">${ch.summary}</p>
    </div>
    <div class="section-content">
      ${ch.content}
    </div>
  </section>
`).join('');

// Complete HTML Template
const fullHtml = `<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OmnifyWP eCommerce — Complete User Manual & Store Owner Guide</title>
  <meta name="description" content="Complete user documentation for OmnifyWP eCommerce: catalog management, orders, customer CRM, coupons, analytics, payment gateways, and storefront guide.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <script>
    // System color scheme auto-detection on initial load
    (function() {
      var saved = localStorage.getItem('omnify_theme_pref');
      if (saved) {
        document.documentElement.setAttribute('data-theme', saved);
      } else {
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
      }
    })();
  </script>

  <style>
    /* Website Palette (Forest Green, Emerald, Mint & Obsidian) */
    :root {
      --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --font-mono: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    /* Light Mode (Omnify Website Color Palette) */
    :root[data-theme="light"] {
      --bg-body: #F7FCF9;
      --bg-surface: #FFFFFF;
      --bg-card: #FFFFFF;
      --bg-card-hover: #EFF8F2;
      --bg-subtle: #EFF8F2;
      --bg-glass: rgba(255, 255, 255, 0.92);
      --bg-code: #0D1B12;
      --border-subtle: #D4E8DC;
      --border-highlight: #BCE0CB;
      --border-accent: #22A06B;

      --text-main: #0D1B12;
      --text-muted: #3D5147;
      --text-dim: #6B7F74;

      --brand-forest-dark: #063d26;
      --brand-forest: #0B5135;
      --brand-forest-mid: #126343;
      --brand-forest-light: #18794E;
      --brand-emerald: #22A06B;
      --brand-emerald-vivid: #34D399;
      --brand-mint-light: #A8DFBF;
      --brand-mint-pale: #EFF8F2;
      --brand-amber: #D97706;
      --brand-rose: #DC2626;

      --brand-gradient: linear-gradient(135deg, #0B5135 0%, #18794E 45%, #22A06B 100%);
      --brand-button-gradient: linear-gradient(135deg, #18794E 0%, #22A06B 100%);
      --hero-gradient: linear-gradient(160deg, #0B5135 0%, #18794E 40%, #22A06B 100%);
      --glow-emerald: 0 0 35px -5px rgba(34, 160, 107, 0.25);
      --grid-pattern: radial-gradient(rgba(11, 81, 53, 0.08) 1px, transparent 1px);
    }

    /* Dark Mode (Forest Obsidian & Vivid Emerald) */
    :root[data-theme="dark"] {
      --bg-body: #08100B;
      --bg-surface: #0E1A13;
      --bg-card: #13241B;
      --bg-card-hover: #193024;
      --bg-subtle: #162B20;
      --bg-glass: rgba(14, 26, 19, 0.90);
      --bg-code: #050B07;
      --border-subtle: rgba(212, 232, 220, 0.12);
      --border-highlight: rgba(52, 211, 153, 0.28);
      --border-accent: #22A06B;

      --text-main: #F4FAF6;
      --text-muted: #A3BFB0;
      --text-dim: #718F7F;

      --brand-forest-dark: #063d26;
      --brand-forest: #126343;
      --brand-forest-mid: #18794E;
      --brand-forest-light: #22A06B;
      --brand-emerald: #34D399;
      --brand-emerald-vivid: #6EE7B7;
      --brand-mint-light: #A8DFBF;
      --brand-mint-pale: #1A3326;
      --brand-amber: #FBBF24;
      --brand-rose: #F87171;

      --brand-gradient: linear-gradient(135deg, #18794E 0%, #22A06B 50%, #34D399 100%);
      --brand-button-gradient: linear-gradient(135deg, #18794E 0%, #22A06B 100%);
      --hero-gradient: linear-gradient(160deg, #063d26 0%, #0B5135 40%, #18794E 100%);
      --glow-emerald: 0 0 35px -5px rgba(52, 211, 153, 0.2);
      --grid-pattern: radial-gradient(rgba(52, 211, 153, 0.08) 1px, transparent 1px);
    }

    html {
      scroll-behavior: smooth;
    }

    section[id], [id] {
      scroll-margin-top: 100px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-sans);
      background-color: var(--bg-body);
      color: var(--text-main);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
      background-image: var(--grid-pattern);
      background-size: 28px 28px;
      background-position: top center;
    }

    /* Ambient Background Glow */
    .ambient-glow-top {
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 1100px;
      height: 480px;
      background: radial-gradient(50% 50% at 50% 0%, rgba(34, 160, 107, 0.12) 0%, rgba(11, 81, 53, 0.06) 50%, transparent 100%);
      pointer-events: none;
      z-index: 0;
    }

    /* Sticky Top Header */
    .top-header {
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      background: var(--bg-glass);
      border-bottom: 1px solid var(--border-subtle);
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
    }

    .brand-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-main);
    }

    .brand-icon {
      width: 34px;
      height: 34px;
      border-radius: 9px;
      background: var(--brand-button-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(34, 160, 107, 0.3);
      color: #fff;
      font-weight: 800;
      font-size: 1.1rem;
    }

    .brand-text {
      font-weight: 800;
      font-size: 1.15rem;
      letter-spacing: -0.03em;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .brand-version-pill {
      font-family: var(--font-mono);
      font-size: 0.7rem;
      font-weight: 600;
      padding: 2px 7px;
      border-radius: 9999px;
      background: rgba(34, 160, 107, 0.12);
      color: var(--brand-emerald);
      border: 1px solid rgba(34, 160, 107, 0.25);
    }

    /* Doc Mode Switcher Segmented Control */
    .doc-switcher {
      display: flex;
      align-items: center;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 3px;
      gap: 2px;
    }

    .switcher-btn {
      padding: 6px 14px;
      font-size: 0.82rem;
      font-weight: 600;
      text-decoration: none;
      color: var(--text-muted);
      border-radius: 7px;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .switcher-btn:hover {
      color: var(--text-main);
    }

    .switcher-btn.active {
      background: var(--bg-card);
      color: var(--brand-emerald);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border: 1px solid var(--border-subtle);
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .header-nav-link {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.84rem;
      font-weight: 600;
      color: var(--text-muted);
      text-decoration: none;
      padding: 7px 12px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .header-nav-link:hover {
      color: var(--brand-emerald);
      background: var(--bg-card-hover);
      border-color: var(--border-subtle);
    }

    .search-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 9px;
      padding: 7px 14px;
      color: var(--text-muted);
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s;
      font-family: inherit;
    }

    .search-btn:hover {
      border-color: var(--border-highlight);
      color: var(--text-main);
      background: var(--bg-card-hover);
    }

    .kbd-shortcut {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 5px;
      padding: 1px 6px;
      font-size: 0.7rem;
      font-family: var(--font-mono);
      color: var(--text-dim);
    }

    .icon-btn {
      background: transparent;
      border: 1px solid var(--border-subtle);
      border-radius: 9px;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.2s;
    }

    .icon-btn:hover {
      color: var(--text-main);
      border-color: var(--border-highlight);
      background: var(--bg-card);
    }

    .github-link {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-subtle);
      border-radius: 9px;
      padding: 7px 14px;
      color: var(--text-main);
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .github-link:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--border-highlight);
      transform: translateY(-1px);
    }

    /* Layout Structure */
    .docs-layout {
      position: relative;
      display: flex;
      max-width: 1560px;
      margin: 0 auto;
      z-index: 1;
    }

    /* Left Sidebar */
    .docs-sidebar {
      width: 290px;
      flex-shrink: 0;
      position: sticky;
      top: 68px;
      height: calc(100vh - 68px);
      overflow-y: auto;
      border-right: 1px solid var(--border-subtle);
      padding: 24px 16px 40px;
      background: transparent;
      scrollbar-width: thin;
      scrollbar-color: var(--border-subtle) transparent;
    }

    .sidebar-group {
      margin-bottom: 24px;
    }

    .sidebar-heading {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-dim);
      padding: 6px 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .sidebar-items {
      list-style: none;
      margin-top: 4px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 7px 12px;
      border-radius: 7px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.86rem;
      font-weight: 500;
      transition: all 0.15s ease;
      line-height: 1.4;
    }

    .sidebar-link:hover {
      color: var(--text-main);
      background: var(--bg-card);
    }

    .sidebar-link.active {
      color: var(--brand-emerald);
      background: rgba(34, 160, 107, 0.1);
      font-weight: 600;
    }

    .sidebar-tag {
      font-size: 0.68rem;
      font-family: var(--font-mono);
      padding: 1px 5px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-dim);
    }

    /* Main Content */
    .docs-main {
      flex: 1;
      min-width: 0;
      padding: 40px 56px 100px;
    }

    @media (max-width: 1180px) {
      .docs-main { padding: 32px 24px 80px; }
    }

    /* Hero Header */
    .doc-hero {
      margin-bottom: 56px;
      padding-bottom: 40px;
      border-bottom: 1px solid var(--border-subtle);
    }

    .hero-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 4px 14px;
      border-radius: 9999px;
      background: rgba(34, 160, 107, 0.12);
      border: 1px solid rgba(34, 160, 107, 0.28);
      color: var(--brand-emerald);
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 16px;
    }

    .hero-title {
      font-size: 2.75rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      line-height: 1.15;
      margin-bottom: 16px;
      color: var(--text-main);
    }

    .hero-description {
      font-size: 1.15rem;
      color: var(--text-muted);
      max-width: 820px;
      line-height: 1.6;
    }

    .hero-meta-bar {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    .hero-ext-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 9px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .hero-ext-btn.primary {
      background: var(--brand-button-gradient);
      color: #fff;
      box-shadow: 0 4px 14px rgba(34, 160, 107, 0.3);
    }

    .hero-ext-btn.primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(34, 160, 107, 0.4);
    }

    .hero-ext-btn.secondary {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      color: var(--text-main);
    }

    .hero-ext-btn.secondary:hover {
      border-color: var(--border-highlight);
      background: var(--bg-card-hover);
    }

    /* Section Styling */
    .doc-section {
      margin-bottom: 72px;
      padding-bottom: 48px;
      border-bottom: 1px solid var(--border-subtle);
    }

    .section-header {
      margin-bottom: 28px;
    }

    .section-meta {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }

    .section-badge {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 2px 8px;
      border-radius: 6px;
      background: rgba(34, 160, 107, 0.12);
      color: var(--brand-emerald);
      border: 1px solid rgba(34, 160, 107, 0.25);
    }

    .section-title {
      font-size: 1.95rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-summary {
      font-size: 1.05rem;
      color: var(--text-muted);
    }

    .section-content h3 {
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      margin: 32px 0 14px;
      color: var(--text-main);
    }

    .section-content p {
      margin-bottom: 16px;
      color: var(--text-muted);
      line-height: 1.7;
    }

    .lead-text {
      font-size: 1.1rem;
      line-height: 1.75;
      color: var(--text-main);
      margin-bottom: 24px;
    }

    /* Grid Cards */
    .grid-cards-3 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 18px;
      margin: 24px 0 32px;
    }

    .grid-cards-2 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 18px;
      margin: 24px 0 32px;
    }

    .feature-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 22px;
      transition: all 0.2s ease;
    }

    .feature-card:hover {
      border-color: var(--border-highlight);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .feature-card-icon {
      font-size: 1.75rem;
      margin-bottom: 12px;
    }

    .feature-card h4 {
      font-size: 1.05rem;
      font-weight: 700;
      margin-bottom: 8px;
      color: var(--text-main);
    }

    .feature-card p {
      font-size: 0.9rem;
      margin-bottom: 0;
      color: var(--text-muted);
      line-height: 1.55;
    }

    /* UI Mockup Window Styles */
    .ui-mockup-window {
      margin: 32px 0;
      border: 1px solid var(--border-subtle);
      border-radius: 14px;
      overflow: hidden;
      background: var(--bg-card);
      box-shadow: 0 12px 36px rgba(11, 81, 53, 0.08);
      transition: all 0.2s ease;
    }

    .ui-mockup-window:hover {
      border-color: var(--border-highlight);
      box-shadow: 0 16px 48px rgba(11, 81, 53, 0.12);
    }

    .ui-mockup-header {
      background: var(--bg-surface);
      border-bottom: 1px solid var(--border-subtle);
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .ui-mockup-dots {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
    }

    .dot-red { background: #EF4444; }
    .dot-yellow { background: #F59E0B; }
    .dot-green { background: #10B981; }

    .ui-mockup-address {
      background: var(--bg-subtle);
      border: 1px solid var(--border-subtle);
      border-radius: 6px;
      padding: 4px 14px;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      color: var(--text-dim);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .ui-mockup-badge {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 9999px;
      background: rgba(34, 160, 107, 0.12);
      color: var(--brand-emerald);
      border: 1px solid rgba(34, 160, 107, 0.25);
    }

    .ui-mockup-body {
      padding: 24px;
      background: var(--bg-card);
    }

    .ui-mockup-caption {
      padding: 12px 20px;
      background: var(--bg-surface);
      border-top: 1px solid var(--border-subtle);
      font-size: 0.85rem;
      color: var(--text-dim);
      font-style: italic;
    }

    /* Sub-components for UI Mockups */
    .mockup-wizard {
      max-width: 720px;
      margin: 0 auto;
    }

    .wizard-stepper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .step {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.82rem;
      color: var(--text-dim);
      font-weight: 600;
    }

    .step.active {
      color: var(--brand-emerald);
    }

    .step.completed {
      color: var(--brand-forest);
    }

    .step-num {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
    }

    .step.active .step-num {
      background: var(--brand-emerald);
      color: #fff;
      border-color: var(--brand-emerald);
    }

    .step.completed .step-num {
      background: var(--brand-forest);
      color: #fff;
      border-color: var(--brand-forest);
    }

    .step-connector {
      flex: 1;
      height: 2px;
      background: var(--border-subtle);
      margin: 0 10px;
    }

    .step-connector.completed {
      background: var(--brand-forest);
    }

    .wizard-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 24px;
    }

    .wizard-card-header h4 {
      margin: 0 0 4px;
      font-size: 1.1rem;
      font-weight: 700;
    }

    .wizard-card-header p {
      font-size: 0.85rem;
      color: var(--text-dim);
      margin-bottom: 20px;
    }

    .mockup-form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 20px;
    }

    .mockup-field label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
      margin-bottom: 6px;
    }

    .mockup-input-text, .mockup-input-select {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 7px;
      padding: 8px 12px;
      font-size: 0.85rem;
      color: var(--text-main);
    }

    .wizard-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid var(--border-subtle);
      padding-top: 16px;
      margin-top: 20px;
    }

    .btn-mockup {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 16px;
      border-radius: 7px;
      font-size: 0.84rem;
      font-weight: 600;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;
    }

    .btn-mockup.btn-primary {
      background: var(--brand-button-gradient);
      color: #fff;
    }

    .btn-mockup.btn-outline {
      background: transparent;
      border: 1px solid var(--border-subtle);
      color: var(--text-main);
    }

    .btn-mockup.btn-outline.danger {
      color: var(--brand-rose);
      border-color: rgba(220, 38, 38, 0.3);
    }

    .btn-xs {
      display: inline-block;
      padding: 3px 8px;
      font-size: 0.72rem;
      font-weight: 600;
      border-radius: 5px;
      background: var(--bg-subtle);
      border: 1px solid var(--border-subtle);
      color: var(--text-muted);
      cursor: pointer;
    }

    .btn-xs.danger {
      color: var(--brand-rose);
    }

    .mockup-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 18px;
    }

    .mockup-pills {
      display: flex;
      gap: 6px;
    }

    .mockup-pill {
      font-size: 0.75rem;
      padding: 3px 10px;
      border-radius: 9999px;
      background: var(--bg-subtle);
      border: 1px solid var(--border-subtle);
      color: var(--text-dim);
      font-weight: 600;
    }

    .mockup-pill.active {
      background: var(--brand-forest);
      color: #fff;
      border-color: var(--brand-forest);
    }

    .mockup-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 14px;
      margin-bottom: 20px;
    }

    .kpi-box {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 16px;
    }

    .kpi-label {
      display: block;
      font-size: 0.78rem;
      color: var(--text-dim);
      font-weight: 600;
      margin-bottom: 4px;
    }

    .kpi-val {
      display: block;
      font-size: 1.45rem;
      font-weight: 800;
      color: var(--text-main);
      margin-bottom: 4px;
    }

    .kpi-trend {
      font-size: 0.72rem;
      font-weight: 600;
    }

    .kpi-trend.up {
      color: var(--brand-emerald);
    }

    .mockup-split-2 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
    }

    .mockup-card-panel {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 18px;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
      font-size: 0.88rem;
    }

    .panel-link {
      font-size: 0.75rem;
      color: var(--brand-emerald);
      font-weight: 600;
      cursor: pointer;
    }

    .mockup-table-mini {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .table-mini-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      border-bottom: 1px solid var(--border-subtle);
      font-size: 0.82rem;
    }

    .table-mini-row.header {
      font-weight: 700;
      color: var(--text-dim);
      font-size: 0.72rem;
      text-transform: uppercase;
      border-bottom: 2px solid var(--border-subtle);
    }

    .quick-action-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .qa-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 12px;
      border-radius: 8px;
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
    }

    .qa-icon {
      font-size: 1.25rem;
    }

    .qa-title {
      font-size: 0.84rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .qa-desc {
      font-size: 0.72rem;
      color: var(--text-dim);
    }

    .mockup-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .mockup-tabs-bar {
      display: flex;
      gap: 6px;
    }

    .m-tab {
      font-size: 0.78rem;
      padding: 5px 12px;
      border-radius: 6px;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      color: var(--text-muted);
      font-weight: 600;
    }

    .m-tab.active {
      background: var(--brand-forest);
      color: #fff;
      border-color: var(--brand-forest);
    }

    .mockup-data-table {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      overflow: hidden;
      font-size: 0.82rem;
    }

    .table-head {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      background: var(--bg-subtle);
      border-bottom: 1px solid var(--border-subtle);
      font-weight: 700;
      color: var(--text-dim);
      font-size: 0.72rem;
      text-transform: uppercase;
    }

    .table-row {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border-subtle);
      color: var(--text-main);
    }

    .table-row:last-child {
      border-bottom: none;
    }

    .p-title {
      font-weight: 700;
      font-size: 0.88rem;
    }

    .p-sku {
      font-size: 0.72rem;
      color: var(--text-dim);
    }

    .editor-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 16px;
    }

    .editor-title-input {
      flex: 1;
      font-size: 1.15rem;
      font-weight: 700;
      padding: 8px 14px;
      border-radius: 8px;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      color: var(--text-main);
    }

    .editor-tabs {
      display: flex;
      gap: 4px;
      border-bottom: 1px solid var(--border-subtle);
      margin-bottom: 16px;
    }

    .e-tab {
      padding: 8px 14px;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-dim);
      border-bottom: 2px solid transparent;
    }

    .e-tab.active {
      color: var(--brand-emerald);
      border-bottom-color: var(--brand-emerald);
    }

    .file-locker-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 8px;
      background: var(--bg-subtle);
      border: 1px solid var(--border-subtle);
      margin-bottom: 8px;
    }

    .tag-cloud-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .tag-pill {
      font-size: 0.78rem;
      padding: 5px 12px;
      border-radius: 9999px;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      color: var(--brand-forest-light);
    }

    .risk-badge-banner {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: 8px;
      background: rgba(34, 160, 107, 0.12);
      border: 1px solid rgba(34, 160, 107, 0.3);
      color: var(--text-main);
      font-size: 0.85rem;
    }

    .gateway-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
      border-bottom: 1px solid var(--border-subtle);
      background: var(--bg-surface);
    }

    .gateway-row:last-child {
      border-bottom: none;
    }

    .g-icon {
      font-size: 1.5rem;
    }

    .mockup-chart-bars {
      display: flex;
      align-items: flex-end;
      gap: 16px;
      height: 120px;
      padding-top: 20px;
    }

    .chart-col {
      flex: 1;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      gap: 6px;
      font-size: 0.72rem;
      color: var(--text-dim);
    }

    .bar-fill {
      width: 100%;
      background: var(--brand-button-gradient);
      border-radius: 4px 4px 0 0;
      min-height: 8px;
    }

    .storefront-grid-3 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }

    .store-product-card {
      position: relative;
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .product-badge-sale {
      position: absolute;
      top: 8px;
      left: 8px;
      font-size: 0.68rem;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 4px;
      background: var(--brand-rose);
      color: #fff;
    }

    .product-card-img {
      height: 120px;
      background: var(--bg-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
    }

    .product-card-body {
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .product-cat-tag {
      font-size: 0.7rem;
      color: var(--text-dim);
      font-weight: 600;
    }

    .product-card-title {
      font-size: 0.92rem;
      font-weight: 700;
      margin: 0;
    }

    .product-stars {
      color: #FBBF24;
      font-size: 0.75rem;
    }

    .product-price-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 4px 0 8px;
    }

    .price-current {
      font-weight: 800;
      font-size: 1.1rem;
      color: var(--text-main);
    }

    .price-old {
      font-size: 0.85rem;
      color: var(--text-dim);
    }

    .product-gallery-mock {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 16px;
      text-align: center;
    }

    .main-gallery-preview {
      height: 180px;
      background: var(--bg-subtle);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .gallery-thumbs {
      display: flex;
      justify-content: center;
      gap: 8px;
    }

    .thumb {
      width: 40px;
      height: 40px;
      border-radius: 6px;
      border: 1px solid var(--border-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      background: var(--bg-card);
    }

    .thumb.active {
      border-color: var(--brand-emerald);
      box-shadow: 0 0 0 1px var(--brand-emerald);
    }

    .product-meta-mock {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .qty-stepper {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      padding: 3px 8px;
      border-radius: 5px;
      background: var(--bg-subtle);
      border: 1px solid var(--border-subtle);
    }

    /* Tables */
    .doc-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin: 24px 0 32px;
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      overflow: hidden;
      font-size: 0.9rem;
    }

    .doc-table th, .doc-table td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border-subtle);
    }

    .doc-table th {
      background: var(--bg-surface);
      color: var(--text-main);
      font-weight: 600;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .doc-table tr:last-child td {
      border-bottom: none;
    }

    .doc-table td {
      color: var(--text-muted);
    }

    /* Callouts */
    .callout {
      display: flex;
      gap: 16px;
      padding: 18px 20px;
      border-radius: 12px;
      margin: 24px 0;
      font-size: 0.92rem;
      line-height: 1.6;
    }

    .callout-icon {
      font-size: 1.35rem;
      flex-shrink: 0;
    }

    .callout.tip {
      background: rgba(34, 160, 107, 0.08);
      border: 1px solid rgba(34, 160, 107, 0.25);
      color: var(--text-main);
    }

    .callout.warning {
      background: rgba(217, 119, 6, 0.08);
      border: 1px solid rgba(217, 119, 6, 0.25);
      color: var(--text-main);
    }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      font-family: var(--font-mono);
    }

    .badge.success { background: rgba(34, 160, 107, 0.15); color: var(--brand-emerald); }
    .badge.info { background: rgba(18, 99, 67, 0.15); color: var(--brand-forest-light); }
    .badge.warning { background: rgba(217, 119, 6, 0.15); color: var(--brand-amber); }
    .badge.danger { background: rgba(220, 38, 38, 0.15); color: var(--brand-rose); }
    .badge.neutral { background: rgba(255, 255, 255, 0.08); color: var(--text-dim); }

    /* Lists */
    .styled-list {
      margin: 16px 0 24px 20px;
      color: var(--text-muted);
    }

    .styled-list li {
      margin-bottom: 8px;
      line-height: 1.6;
    }

    code {
      font-family: var(--font-mono);
      font-size: 0.85em;
      padding: 2px 6px;
      border-radius: 5px;
      background: rgba(34, 160, 107, 0.08);
      color: var(--brand-emerald);
      border: 1px solid rgba(34, 160, 107, 0.2);
    }

    /* Right Rail TOC */
    .docs-toc {
      width: 240px;
      flex-shrink: 0;
      position: sticky;
      top: 68px;
      height: calc(100vh - 68px);
      overflow-y: auto;
      padding: 32px 16px 40px;
      border-left: 1px solid var(--border-subtle);
    }

    .toc-title {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-dim);
      margin-bottom: 12px;
      padding-left: 10px;
    }

    .toc-list {
      list-style: none;
    }

    .toc-link {
      display: block;
      padding: 6px 10px;
      font-size: 0.8rem;
      color: var(--text-muted);
      text-decoration: none;
      border-radius: 6px;
      line-height: 1.4;
      transition: all 0.15s ease;
    }

    .toc-link:hover {
      color: var(--text-main);
      background: var(--bg-card);
    }

    .toc-link.active {
      color: var(--brand-emerald);
      font-weight: 600;
      background: rgba(34, 160, 107, 0.08);
    }

    /* FAQ Item */
    .faq-item {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
    }

    .faq-item h4 {
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 8px;
    }

    .faq-item p {
      margin-bottom: 0;
      font-size: 0.92rem;
      color: var(--text-muted);
    }

    /* Search Modal */
    .search-modal {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      z-index: 100;
      display: none;
      align-items: flex-start;
      justify-content: center;
      padding-top: 15vh;
    }

    .search-modal.active {
      display: flex;
    }

    .search-dialog {
      background: var(--bg-surface);
      border: 1px solid var(--border-highlight);
      border-radius: 14px;
      width: 90%;
      max-width: 620px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .search-input-wrap {
      display: flex;
      align-items: center;
      padding: 16px 20px;
      border-bottom: 1px solid var(--border-subtle);
      gap: 12px;
    }

    .search-input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      font-size: 1.05rem;
      color: var(--text-main);
      font-family: inherit;
    }

    .search-results {
      max-height: 420px;
      overflow-y: auto;
      padding: 12px;
    }

    .search-result-item {
      display: block;
      padding: 12px 16px;
      border-radius: 8px;
      text-decoration: none;
      transition: background 0.15s ease;
      color: inherit;
    }

    .search-result-item:hover {
      background: var(--bg-card);
    }

    .search-result-title {
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--brand-emerald);
      margin-bottom: 4px;
    }

    .search-result-snippet {
      font-size: 0.82rem;
      color: var(--text-dim);
      line-height: 1.4;
    }

    @media (max-width: 980px) {
      .docs-toc { display: none; }
      .docs-sidebar { display: none; }
      .docs-main { padding: 24px 16px 60px; }
      .hero-title { font-size: 2.1rem; }
    }
  </style>
</head>
<body>
  <div class="ambient-glow-top"></div>

  <!-- Top Sticky Header -->
  <header class="top-header">
    <div style="display: flex; align-items: center; gap: 20px;">
      <a href="user-guide.html" class="brand-wrap">
        <div class="brand-icon">⚡</div>
        <div class="brand-text">
          OmnifyWP
          <span class="brand-version-pill">v1.2.0</span>
        </div>
      </a>

      <!-- Doc Switcher -->
      <nav class="doc-switcher">
        <a href="user-guide.html" class="switcher-btn active">
          <span>📘 User Guide</span>
        </a>
        <a href="index.html" class="switcher-btn">
          <span>⚡ Developer Docs</span>
        </a>
      </nav>
    </div>

    <div class="header-right">
      <a href="https://omnifywp.com" target="_blank" rel="noopener" class="header-nav-link" title="Official Website">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <span>omnifywp.com</span>
      </a>

      <a href="https://wordpress.org/plugins/omnifywp-ecommerce/" target="_blank" rel="noopener" class="header-nav-link" title="wp.org Plugin Directory">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.73-1.5l3.89-10.66 3.99 10.94a8.16 8.16 0 0 1-3.15 1.22zm6.26-4.63l-2.4-6.57a3.44 3.44 0 0 0-1.89-1.89l.86-.04a6.6 6.6 0 0 1 4.22 3.63 8.17 8.17 0 0 1-.79 4.87zM5.74 15.57A8.17 8.17 0 0 1 3.8 12a8.16 8.16 0 0 1 2.37-5.74l3.15 8.63zm8.38-11.45a8.2 8.2 0 0 1 3.41 1.76l-1.39 3.8a4 4 0 0 0-2.02-.56zm-4.24.47A8.15 8.15 0 0 1 12 3.8a8.23 8.23 0 0 1 2.06.26l-1.77 5.12z"/></svg>
        <span>wp.org</span>
      </a>

      <button class="search-btn" id="search-trigger">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <span>Search User Guide...</span>
        <span class="kbd-shortcut">Ctrl K</span>
      </button>

      <button class="icon-btn" id="theme-toggle" title="Toggle Dark/Light Mode">
        <svg id="theme-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        <svg id="theme-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>

      <a href="https://github.com/omnifywp/omnifywp-ecommerce" target="_blank" rel="noopener" class="github-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg>
        <span>GitHub</span>
      </a>
    </div>
  </header>

  <!-- Layout Container -->
  <div class="docs-layout">
    <!-- Left Sticky Sidebar -->
    <aside class="docs-sidebar">
      ${sidebarNavHtml}
    </aside>

    <!-- Main Content -->
    <main class="docs-main">
      <div class="doc-hero">
        <div class="hero-badge-pill">
          <span>📚 Store Owner Manual</span>
          <span>•</span>
          <span>Version 1.2.0</span>
        </div>
        <h1 class="hero-title">OmnifyWP eCommerce<br>Complete User Manual</h1>
        <p class="hero-description">
          The comprehensive operating handbook for store managers, operators, and merchants. Learn how to configure your catalog, fulfill orders, manage customer CRM profiles, execute promotional campaigns, and run a high-converting storefront.
        </p>

        <div class="hero-meta-bar">
          <a href="https://omnifywp.com" target="_blank" rel="noopener" class="hero-ext-btn primary">
            <span>Visit Website (omnifywp.com)</span>
            <span>↗</span>
          </a>
          <a href="https://wordpress.org/plugins/omnifywp-ecommerce/" target="_blank" rel="noopener" class="hero-ext-btn secondary">
            <span>wp.org Directory</span>
            <span>↗</span>
          </a>
          <a href="index.html" class="hero-ext-btn secondary">
            <span>Developer Documentation & REST API</span>
            <span>→</span>
          </a>
        </div>
      </div>

      ${mainContentHtml}
    </main>

    <!-- Right Table of Contents -->
    <aside class="docs-toc">
      <div class="toc-title">On This Page</div>
      <ul class="toc-list">
        ${tocHtml}
      </ul>
    </aside>
  </div>

  <!-- Search Modal -->
  <div class="search-modal" id="search-modal">
    <div class="search-dialog">
      <div class="search-input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" class="search-input" id="search-input" placeholder="Search user guide (e.g. products, refunds, stripe, coupons)...">
      </div>
      <div class="search-results" id="search-results"></div>
    </div>
  </div>

  <script>
    const searchIndex = ${JSON.stringify(searchIndex, null, 2)};

    // System Color Scheme Auto-Detection & Persistent Manual Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const themeSun = document.getElementById('theme-sun');
    const themeMoon = document.getElementById('theme-moon');

    function updateThemeIcons(theme) {
      if (theme === 'light') {
        themeSun.style.display = 'none';
        themeMoon.style.display = 'block';
      } else {
        themeSun.style.display = 'block';
        themeMoon.style.display = 'none';
      }
    }

    function applyTheme(theme, save = true) {
      document.documentElement.setAttribute('data-theme', theme);
      if (save) {
        localStorage.setItem('omnify_theme_pref', theme);
      }
      updateThemeIcons(theme);
    }

    function getInitialTheme() {
      const saved = localStorage.getItem('omnify_theme_pref');
      if (saved) return saved;
      return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    const currentTheme = getInitialTheme();
    applyTheme(currentTheme, false);

    // Toggle theme manually
    themeToggle.addEventListener('click', () => {
      const active = document.documentElement.getAttribute('data-theme') || 'dark';
      applyTheme(active === 'dark' ? 'light' : 'dark', true);
    });

    // React to system color scheme changes if user hasn't explicitly overridden
    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('omnify_theme_pref')) {
          applyTheme(e.matches ? 'dark' : 'light', false);
        }
      });
    }

    // Search Trigger and Modal
    const searchTrigger = document.getElementById('search-trigger');
    const searchModal = document.getElementById('search-modal');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');

    function openSearch() {
      searchModal.classList.add('active');
      searchInput.value = '';
      renderSearchResults('');
      setTimeout(() => searchInput.focus(), 50);
    }

    function closeSearch() {
      searchModal.classList.remove('active');
    }

    searchTrigger.addEventListener('click', openSearch);
    searchModal.addEventListener('click', (e) => {
      if (e.target === searchModal) closeSearch();
    });

    window.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openSearch();
      }
      if (e.key === 'Escape') {
        closeSearch();
      }
    });

    function renderSearchResults(query) {
      const q = query.toLowerCase().trim();
      const filtered = q === '' 
        ? searchIndex.slice(0, 6)
        : searchIndex.filter(item => item.title.toLowerCase().includes(q) || item.snippet.toLowerCase().includes(q));

      if (filtered.length === 0) {
        searchResults.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--text-dim);">No matching user guide chapters found.</div>';
        return;
      }

      searchResults.innerHTML = filtered.map(item => \`
        <a href="\${item.url}" class="search-result-item" onclick="closeSearch()">
          <div class="search-result-title">\${item.title}</div>
          <div class="search-result-snippet">\${item.snippet}</div>
        </a>
      \`).join('');
    }

    searchInput.addEventListener('input', (e) => {
      renderSearchResults(e.target.value);
    });

    // Universal Smooth Anchor Scroll with Fixed Header Offset
    function scrollToAnchor(targetId, updateHistory = true) {
      if (!targetId || !targetId.startsWith('#')) return;
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        const header = document.querySelector('.top-header');
        const headerHeight = header ? header.offsetHeight : 68;
        const extraOffset = 20;
        const elementPosition = targetEl.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - (headerHeight + extraOffset);

        window.scrollTo({
          top: Math.max(0, offsetPosition),
          behavior: 'smooth'
        });

        if (updateHistory) {
          history.pushState(null, '', targetId);
        }
      }
    }

    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;
      const href = link.getAttribute('href');
      if (href && href.length > 1 && !href.startsWith('#/')) {
        const targetEl = document.querySelector(href);
        if (targetEl) {
          e.preventDefault();
          scrollToAnchor(href);
        }
      }
    });

    // Scrollspy for sidebar & table of contents
    const trackedSections = document.querySelectorAll('section[id]');
    const tocLinks = document.querySelectorAll('.toc-link');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    window.addEventListener('scroll', () => {
      let currentId = '';
      const header = document.querySelector('.top-header');
      const headerOffset = header ? header.offsetHeight + 30 : 98;
      const scrollPos = window.pageYOffset + headerOffset;

      trackedSections.forEach(sec => {
        const top = sec.getBoundingClientRect().top + window.pageYOffset;
        if (top <= scrollPos) {
          currentId = sec.getAttribute('id');
        }
      });

      if (currentId) {
        tocLinks.forEach(link => {
          link.classList.toggle('active', link.getAttribute('href') === '#' + currentId);
        });
        sidebarLinks.forEach(link => {
          link.classList.toggle('active', link.getAttribute('href') === '#' + currentId);
        });
      }
    }, { passive: true });
  </script>
</body>
</html>
`;

const outputPath = path.join(__dirname, 'user-guide.html');
fs.writeFileSync(outputPath, fullHtml, 'utf8');
console.log('Successfully generated:', outputPath);
