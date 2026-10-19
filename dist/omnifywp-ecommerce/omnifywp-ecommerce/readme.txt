=== OmnifyWP eCommerce — Fast Online Store, Digital Downloads & Global Payments ===
Contributors: omnifywp
Tags: ecommerce, digital downloads, online store, payment gateway, checkout
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern, ultra-fast eCommerce for WordPress with built-in regional payment gateways, single-page checkout, and secure digital file protection.

== Description ==

**OmnifyWP eCommerce** is the modern, developer-first, and conversion-optimized eCommerce platform engineered for WordPress. Built specifically for creators, digital merchants, boutiques, and agencies who are tired of bloated plugins and expensive annual addon licenses, OmnifyWP delivers everything you need to build, launch, and scale a fast online store out of the box.

Whether you are selling **digital downloads** (ebooks, software, music, audio presets, design templates, photography, fonts, license keys), **physical merchandise** requiring granular shipping rules, or **bundled variable products**, OmnifyWP delivers a frictionless customer experience with sub-second page loads, a single-page checkout engine, and native payment gateways for every continent.

---

### Why Choose OmnifyWP eCommerce?

* **Save Over $800/Year in Extension Fees**: Stop paying $79–$199/year for separate gateway, abandoned cart, and digital download add-ons. OmnifyWP includes 11 native payment gateways, automated multi-item abandoned cart recovery, HMAC file encryption, customer wishlists, and multi-layout catalog switchers out of the box with zero subscription fees.
* **Ultra-Lightweight & Scalable SQL Architecture**: Engineered with a minimal frontend footprint (<35KB total scripts) and optimized database queries. Zero N+1 queries across catalog grids, single product cards, and customer portals. Features smart deferred order aggregations and indexed schema architecture for high-volume stores.
* **11 Global & Regional Payment Gateways Included Free**: Native support for Stripe, PayPal, Paystack (Africa), Tap Payments (Middle East / GCC), Mollie (EU), Razorpay (India), Alipay & WeChat Pay (China), SSLCommerz (Bangladesh), Khalti & eSewa (Nepal), plus offline Direct Bank Transfer and COD.
* **Automated Webhook Synchronization**: Real-time two-way synchronization for Stripe and PayPal dashboard refunds and disputes. External refunds immediately update order records, restock inventory, and revoke digital download permissions automatically.
* **Intelligent Multi-Item Cart Abandonment Recovery**: Automatically tracks abandoned checkouts for both single-product purchases and multi-item shopping carts. Dispatches automated personalized recovery email sequences with dynamic item summaries, incentive coupons, and 1-click cart restoration.
* **Frictionless 1-Page Checkout Engine**: Designed specifically to slash cart abandonment with dynamic scarcity countdown timers, free-shipping threshold meters, country/state autocomplete, and seamless guest checkout.
* **Bulletproof Digital File Protection**: Protect your digital assets with cryptographically signed HMAC-SHA256 expiring download URLs, chunked 8KB memory streaming buffers (never revealing real server paths), and a self-service Customer Portal.
* **Timezone-Aware Promotions & Coupon Engine**: Flexible percentage or fixed discounts with store-local expiration windows, usage limits, minimum order thresholds, and live checkout validation.
* **Automated Database Hygiene**: Built-in scheduled cron maintenance routines automatically prune expired abandoned carts and purge stale checkout sessions to ensure top database performance over years of store operation.
* **Developer-First & Headless Ready**: Built with a 100% hook-driven architecture, customizable template overrides, and clean public REST API endpoints (`/wp-json/omnify/v1/`).

---

### Perfect For Every Type of Business

* **Digital Creators & Artists**: Sell audio samples, Notion templates, Lightroom presets, 3D models, fonts, PDFs, and courses with download limits, expiring links, and audit logs.
* **Software Developers & SaaS**: Distribute software installers, plugins, and license documentation using protected 8KB chunked streaming delivery.
* **Boutiques & Physical Stores**: Manage inventory, multi-tier tax tables, shipping zones, flat rates, and local pickups with zero hassle.
* **Global & Emerging-Market Merchants**: Collect payments via credit cards, debit cards, mobile money (M-Pesa, MTN, Airtel), UPI, Netbanking, and digital wallets without high third-party extension costs.

---

### Key Features

#### Modern Storefront & Catalog Experience
* **4-Layout Catalog Switcher**: Shoppers can instantly toggle between 4-column dense grid, 3-column standard grid, 2-column large split view, and 1-column detailed list mode with zero layout shift.
* **Responsive Full-Width Image Cards**: Engineered full-width card imagery (`aspect-ratio: auto`, `object-fit: cover`) ensuring consistent card alignment across all WordPress themes.
* **Balanced Typography Hierarchy**: Refined title, price, description, and badge sizing with 2-line clamps that gracefully prevent clipping on long product names.
* **Sleek Pill Action Buttons**: Streamlined `+ Quick Add` and `View Details` action buttons with balanced padding and font size, avoiding row wrapping or awkward overflows.
* **Recently Viewed Products Carousel**: High-performance batch-loaded carousel/grid showing recently browsed items with cookie persistence and configurable product display counts.
* **Storefront Features & Trust Bar**: Configurable value propositions strip (secure payments, instant access, money-back guarantee, dedicated support) boosting checkout conversion.
* **Faceted Sidebar Filtering**: Instant price range sliders, category pills, brand selectors, and in-stock / on-sale status filters.
* **Storefront AJAX Quick Add**: Single-click add-to-cart with smooth micro-animations and live header badge updates.
* **High-Impact Product Details**: Conversion-focused product pages with media galleries, color swatches, trust badges, feature callouts, and sticky purchase panels.
* **Simple, Variable & Bundled Products**: Sell standard standalone products, multi-attribute variants (sizes, formats, licenses) with unique SKUs and pricing, or bundle multiple items together.
* **Integrated Customer Wishlist**: Allow shoppers to save favorite products for later, driving repeat visits and higher customer lifetime value.
* **Cart Scarcity Countdown**: Urgency-driven countdown timer with session storage persistence that drives higher checkout completion.
* **Free Shipping Progress Meter**: Dynamic visual bar motivating shoppers to add more products to their cart to unlock free delivery.

#### Conversion-Optimized 1-Page Checkout Engine
* **Single-Page Checkout**: Streamlined checkout layout requiring zero page reloads, drastically reducing checkout drop-off.
* **Intelligent Multi-Item Abandoned Cart Recovery**: Tracks abandoned carts from both single products and multi-item shopping carts, dispatching automated recovery emails with dynamic item summaries and 1-click recovery URLs.
* **Country-First Dynamic Selectors**: Smart address selectors with automatic state/region filtering and saved address book autofill.
* **Flexible Account Handling**: Seamless support for guest checkouts, optional account creation, or automatic account provisioning.
* **Timezone-Aware Coupons & Discount Rules**: Percentage or fixed discounts with usage caps, minimum spend rules, and local store timezone expiration accuracy.
* **Express Digital Wallets**: Native support for Apple Pay, Google Pay, and one-click express payment buttons.

#### Enterprise-Grade Digital File Delivery
* **Cryptographically Signed URLs**: Generates HMAC-SHA256 authenticated links with customizable expiration windows and download attempt limits.
* **Secure Chunked Streaming**: Delivers files through 8KB memory-efficient streaming buffers that conceal your physical server paths and prevent hotlinking or memory exhaustion.
* **Fast Self-Service Customer Portal**: Embed `[omnify_customer_downloads]` to give buyers an instant, batch-loaded dashboard to access purchased files, view order histories, and manage billing details with sub-millisecond response times.
* **Comprehensive Download Logs**: Audit IP addresses, user agents, download counts, and bandwidth directly in your WordPress admin.

#### Built-In Payment Gateways (Zero Addon Fees)
* **Stripe**: Credit/debit cards, Apple Pay, Google Pay, Stripe Checkout, plus automated two-way webhook sync for dashboard refunds and disputes.
* **PayPal Commerce**: PayPal Express Checkout, credit/debit cards, smart pay buttons, and automated refund webhook synchronization.
* **Paystack (Africa)**: Market-leading payment infrastructure for Nigeria, Ghana, Kenya, and South Africa supporting cards, Mobile Money (M-Pesa, MTN, Airtel), EFT bank transfers, and USSD.
* **Tap Payments (Middle East & GCC)**: Full coverage across Saudi Arabia, UAE, Kuwait, Bahrain, Qatar, Oman, Egypt, and Jordan with mada, KNET, Benefit, NAPS, and cards.
* **Mollie (European Union)**: Seamless EU commerce supporting iDEAL, Bancontact, SEPA Direct Debit / Bank Transfer, Klarna, and cards.
* **Razorpay (India)**: Industry standard for India supporting UPI, QR codes, cards, netbanking, and mobile wallets.
* **Khalti (Nepal)**: Nepal's popular digital wallet and mobile banking via the official ePayment v2 API.
* **eSewa (Nepal)**: Nepal's pioneer digital payment wallet with HMAC-SHA256 signature verification.
* **Alipay & WeChat Pay**: Comprehensive Chinese cross-border mobile commerce with QR code checkout.
* **SSLCommerz (Bangladesh)**: Top gateway for Bangladesh supporting cards, mobile banking (bKash, Nagad, Rocket), and internet banking.
* **Offline & Manual Payments**: Configurable instructions for Direct Bank Transfer (BACS), Cheque Payments, and Cash on Delivery (COD).
* **One-Click & Partial Refunds**: Issue full or partial refunds directly from the WordPress order screen, with dedicated `Partially Refunded` status tracking and automated inventory restocking.

#### Marketing, Analytics & Taxes
* **Google Analytics 4 & Meta Pixel**: Built-in conversion tracking for purchase events, item revenue, order IDs, and cart behavior.
* **Country-Specific Tax Rules**: Multi-tier tax tables with inclusive/exclusive pricing toggles and shipping tax options.
* **Shipping Zones & Rules**: Define country- and region-based shipping rates, flat fees, and free shipping thresholds.
* **Automated HTML Email Notifications**: Professional order confirmations, payment receipts, signed download access emails, abandoned cart recovery sequences, and admin alerts.
* **Automated Database Maintenance**: Scheduled hourly cron cleanup for expired carts, stale checkout sessions, and automated pending order cancellation.

---

### Live Interactive Demo

Want to test OmnifyWP before installing? Try the full interactive sandbox on WordPress Playground with zero installation:

[Test Drive OmnifyWP Live Demo](https://wordpress.org/plugins/omnifywp-ecommerce/)

== Installation ==

### Automatic Installation (Recommended)
1. Log in to your WordPress Admin dashboard.
2. Go to **Plugins > Add New Plugin**.
3. Search for `OmnifyWP eCommerce`.
4. Click **Install Now**, then click **Activate**.
5. Follow the interactive **Quick Setup Wizard** to automatically generate your default store pages (Storefront, Cart, Checkout, and Customer Portal) in under 60 seconds.

### Manual Installation
1. Download the `omnifywp-ecommerce.zip` package from WordPress.org.
2. In your WordPress Admin, go to **Plugins > Add New Plugin > Upload Plugin**.
3. Select the downloaded ZIP file and click **Install Now**.
4. Activate the plugin and navigate to **Omnify > Settings > Payments** to configure your credentials.

== Frequently Asked Questions ==

= What makes OmnifyWP different from WooCommerce or Easy Digital Downloads? =
OmnifyWP is built from the ground up for speed, simplicity, and conversions. Unlike traditional eCommerce plugins that require 15–20 paid extensions just to accept local payments, offer 1-page checkout, or protect downloads, OmnifyWP includes all essential features out of the box with zero database bloat and blazing fast load times (<35KB frontend script footprint).

= Are the regional payment gateways really free? =
Yes! 100% free. Gateways like Paystack, Tap Payments, Mollie, Razorpay, SSLCommerz, Khalti, eSewa, Stripe, and PayPal are pre-integrated into the core plugin. You only pay standard transaction processing fees directly to the payment processor—no plugin licensing fees or subscription upsells.

= Can I sell both digital downloads and physical products? =
Yes. OmnifyWP seamlessly supports digital files (e-books, audio, presets, software, PDFs), physical goods requiring shipping addresses, variable products with format/size attributes, and bundled packages.

= How does digital download protection work? =
Files uploaded to OmnifyWP are delivered through cryptographically signed HMAC-SHA256 URLs that expire after your chosen time limit or download count. Files are streamed in memory-efficient 8KB chunks without ever exposing the real server path. Customers can also access their files at any time through the secure self-service Customer Portal.

= Is OmnifyWP compatible with my WordPress theme and page builders? =
Yes. OmnifyWP outputs clean, self-contained semantic HTML and scoped CSS that automatically blends with popular WordPress themes (Astra, GeneratePress, Kadence, OceanWP, Hello Elementor, Divi) and the Gutenberg Block Editor.

= Can I issue customer refunds? =
Yes. OmnifyWP provides one-click refund processing directly from the order details screen for supported gateways (Stripe, PayPal, Paystack, Tap Payments, Mollie, Khalti, eSewa, Razorpay, SSLCommerz).

= Does it support automated abandoned cart recovery? =
Yes. OmnifyWP captures abandoned cart sessions and tokens, allowing merchants to trigger recovery notifications and win back lost checkouts.

= Is there a REST API for headless eCommerce? =
Yes. OmnifyWP provides clean, public endpoints under `/wp-json/omnify/v1/` to access products, categories, reviews, and store configuration for custom frontends, React/Next.js apps, or mobile integrations.

== Screenshots ==

1. Executive store dashboard with real-time financial KPIs ($48,290.00 Gross Sales, 1,248 Orders), 30-day health monitor, and revenue charts.
2. Product catalog management table with multi-attribute filtering, instant search, stock level badges, SKU tracking, and bulk actions.
3. 4-step product creation wizard with pricing tiers, cryptographic HMAC digital file locker, and live "Preview Product" modal.
4. Order fulfillment & lifecycle directory with status workflows, automated fraud risk scoring, PDF invoices, and one-click gateway refunds.
5. Customer Relationship Management (CRM) directory tracking customer lifetime value (LTV), order histories, and saved addresses.
6. Promotions & coupons engine with percentage and fixed discount rules, usage counters, expiration countdowns, and coupon analytics.
7. Payment gateway center supporting 10+ built-in global and regional gateways (Stripe, PayPal, Razorpay, Mollie, Square, etc.) with zero platform fees.
8. Automated store email notifications with 16 automated triggers, visual HTML template preview modal, and dynamic merge tags.
9. Modern Elessi-style single product page with 3-column conversion layout, vertical gallery, color swatches, trust badges, and instant checkout.
10. Conversion-optimized cart & one-page checkout with urgency countdown timer, free shipping progress bar, express digital wallets, and 1-click order bump.

== Changelog ==

= 1.4.2 =
* Feature: Multi-Item Abandoned Cart Capture & Recovery — Extended cart abandonment tracking to capture multi-item shopping carts as well as single-product checkouts, ensuring recovery emails and dynamic item summary merge tags trigger reliably.
* Feature: Gateway Dashboard Webhook Synchronization — Added native webhook event handlers for Stripe (`charge.refunded`, `charge.dispute.created`) and PayPal (`PAYMENT.CAPTURE.REFUNDED`), automatically syncing external dashboard refunds with order status, inventory restoration, and customer file revocation.
* Feature: Partially Refunded Order Workflow & Audit Trails — Introduced dedicated `'partially_refunded'` status to distinguish partially refunded orders from fully paid orders across admin order listings, customer activity logs, and order lifecycle automation hooks.
* Performance: N+1 Review & Rating Query Elimination — Implemented batch rating summarization across single product page (PDP) related, upsell, cross-sell, and recently viewed cards, reducing template database queries from 30+ down to a single batch query.
* Performance: High-Volume Customer Pagination Optimization — Redesigned customer listing queries to defer order aggregations during standard pagination, switching to lightweight indexed batch queries for page records and eliminating full-table order grouping scans on large databases.
* Performance: Customer Downloads Tab Acceleration — Added batch file resolution in `Product_File_Repository::all_for_products()` and streamlined `Customer_Access_Repository` to pre-fetch all products and digital files in 3 queries instead of 30+ sequential lookups.
* Performance: Customer Portal Refund Eligibility Memoization — Implemented runtime static caching for product refund policy lookups across order line items, eliminating redundant database queries on portal order histories.
* Performance: PDP Recently Viewed Products Batch Loading — Replaced sequential single-item lookups with `Product_Repository::find_many()` to load all recently viewed products in a single SQL query.
* Fix: Coupon Expiration Timezone Shift — Resolved timezone discrepancy in coupon expiration normalization by anchoring date-only strings to the WordPress site's local timezone via `get_gmt_from_date()` and `wp_timezone()`.
* Maintenance: Automated Abandoned Cart Database Hygiene — Added automated maintenance routines (`mark_expired()` and `cleanup_expired()`) hooked into the hourly cleanup cron to prevent indefinite database growth.
* Reliability: Safe Download Normalization & Windows Path Resolution — Hardened path verification across local files and upload directories with cross-platform normalization.

= 1.4.1 =
* Admin Tools & Diagnostics Modernization: Complete design and UX overhaul across all Tools pages (System Tools & Seeding, Import/Export, Activity Log, and API Keys / Connection Diagnostics).
* Database Diagnostics Overview: Real-time 4-card status strip reporting live counts for Products, Orders, Customers, and Reviews.
* System Tools & Seeding: Modern animated progress bar for demo data generation, structured granular database cleanup actions, server runtime specifications dashboard, and elevated danger zone.
* Import & Export: Redesigned export cards with emerald accent bubbles and format tags, streamlined CSV upload dropzone, and modernized CSV header specification table.
* Activity Audit Trail: Replaced legacy styling with modern table components, circular user avatars, category-specific badge treatments, and fixed pagination character encoding issues.
* REST API & Diagnostics: Modernized API key generation form, clean active credentials table, outbound webhooks manager with trigger topic pills, and polished remote URL connectivity checker.
* Payment Gateway Connectivity: Fixed nested button markup on diagnostic cards and improved Stripe and PayPal connection test feedback.
* Storefront Navigation: Fixed broken URL parameter handling between catalog listings and single product details pages.
* Admin Settings Redesign: Comprehensive visual polish across General, Localization, Currency, Payment, Tax, Shipping, Email, Customer Portal, and Advanced settings tabs.
* Standards & Compliance: Standardized all administrative interfaces, scripts, and buttons with Dashicons and clean text labels.

= 1.4.0 =
* Storefront Interactivity: Added dynamic Flash Sale announcement ticker with live session-persisted countdown timer and 1-click coupon code copy button (`FLASH30`) with instant clipboard feedback.
* Storefront Interactivity: Added interactive Quick Filter Pills strip for instant 1-click client-side filtering by Flash Deals, Bestsellers, New In, and top categories with dynamic counter badges.
* Storefront Mockup: Added floating card action toolbar on hover with Wishlist toggle, Quick View modal trigger, and Product Compare launcher.
* Storefront Mockup: Added interactive color swatch selector dots on product cards with smooth active indicator states.
* Storefront Layout: Streamlined home page and catalog by removing redundant bottom features bar, removing hidden duplicate filter drawers, and cleaning up sidebar filter widgets.
* Storefront Design: Complete product card visual and layout overhaul across all catalog grid views (4-column dense, 3-column standard, 2-column large, and detailed list view).
* Storefront UI: Implemented full-width responsive product image containers (`width: 100%`, `max-width: 100%`, `aspect-ratio: auto`, `object-fit: cover`) preventing image squishing, unexpected gaps, and aspect ratio distortion across third-party themes.
* Storefront Layout: Unified card padding tailored per column density: 18px (2-column), 15px (3-column), and 12px (4-column).
* Storefront Typography: Balanced title, price, description, and badge sizing with 2-line title clamps to prevent awkward text truncation on long product titles. Cleaned description display in 4-column mode.
* Storefront UI: Streamlined pill action buttons (`+ Quick Add` / `View Details`) with compact padding and font sizing (`min-height: 24px-28px`) ensuring clean alignment without wrapping in dense layouts.
* Storefront Feature: Enhanced Recently Viewed Products grid (`#omnify-recently-viewed-grid`) with cookie persistence, auto-hiding when empty, and responsive 4-column layout matching the primary catalog styling.
* Storefront Feature: Added 4-layout catalog switcher toolbar allowing buyers to switch between 2-column, 3-column, 4-column, and detailed list layouts on the fly.
* Storefront Feature: Added Storefront Features & Value Propositions bar with 4 configurable highlight slots (Security, Speed, Guarantee, Support).
* Storefront Feature: Added faceted sidebar filtering with instant search, category pills, brand toggles, and price range sliders.
* Storefront UX: Fixed category and brand filtering triggers to ensure responsive updates on mobile devices.
* Storefront Design: Refined product badge positions (Discount, Bestseller, In Stock), hover micro-interactions, and wishlist heart action animations.
* Reviews: Enhanced review average calculation and star rating breakdown indicators on storefront cards.
* Cart: Added slide-out cart drawer integration and dynamic cart badge counts.
* Checkout: Enhanced address field autocomplete and country dropdown state synchronization.
* Compatibility: Strengthened CSS override specificity for popular themes including Astra, GeneratePress, and Kadence.

= 1.3.0 =
* Critical Bug Fix: Fixed Khalti live endpoint URL resolution in `verify_khalti_payment()` ensuring production payments target `khalti.com` rather than sandbox test environment.
* Security Hardening: Enforced strict webhook secret validation on Razorpay and Paystack webhook endpoints, preventing unsigned/forged payload processing when secrets are unconfigured.
* Architecture: Centralized order lifecycle automation into `Omnify_Order_Lifecycle_Service` for instant digital access entitlement on order completion, automatic access revocation on refunds, and stock restoration on cancellations.
* Business Logic: Enhanced coupon expiry evaluation to cover the full end of the expiration day (23:59:59) for date-only settings.
* Customer Portal: Fixed digital access filtering so paying customers retain access and downloads for purchased items even if a product is later archived or drafted by the store.
* Performance: Added dedicated database lookup indexes on `omnify_orders` (`transaction_id`, `status`, `created_at`) via migration for high-traffic stores.
* Storefront UX: Added clear pending-payment notice on the checkout receipt screen for manual orders (Bank Transfer/COD) explaining that downloads unlock once payment is confirmed.

= 1.2.0 =
* Documentation & Demo: Added complete user documentation website, interactive screenshot suite, and 1-click WordPress Playground live demo blueprint.
* Critical Bug Fix: Fixed purchase verification blocking buyers of physical products from submitting reviews. Reviews now verify completed orders in addition to digital access entitlements.
* Critical Bug Fix: Resolved false 403 Forbidden errors on digital downloads in Windows server environments by normalizing file paths with `wp_normalize_path()`.
* Performance & Stability: Replaced memory-buffering file streaming with 8KB chunked streaming and buffer flushes, preventing memory exhaustion when delivering large digital downloads.
* Database & Query Optimization: Added compound index on `order_items` (`order_id`, `product_id`) and migrated `customer_visible` flag on customer notes for fast customer portal lookups and data privacy.
* Feature: Added public guest order tracking shortcode `[omnify_order_tracking]` for customer order status lookup and invoice access without requiring account login.
* Gateways: Integrated live API automated refund execution for all supported regional gateways (Paystack, Tap Payments, Mollie, Khalti, and eSewa).
* Storefront Design: Seamlessly unified the Customer Portal design with the Admin UI system (matching KPI metrics, typography, tabs, buttons, forms, and cards).
* Security & Compliance: Streamlined checkout authentication to meet WordPress.org submission guidelines; replaced raw inline password form with secure WordPress login routing.

= 1.1.0 =
* Feature: Added Paystack gateway integration for African markets supporting cards, mobile money, bank transfer, and USSD.
* Feature: Added Tap Payments gateway integration for Middle East & GCC supporting mada, KNET, Benefit, NAPS, and cards.
* Feature: Added Mollie gateway integration for European Union supporting iDEAL, Bancontact, SEPA, and cards.
* Feature: Added Khalti Digital Wallet integration for Nepal supporting ePayment v2 API and status lookups.
* Feature: Added eSewa ePay v2 integration for Nepal with cryptographic HMAC-SHA256 signature verification.
* Feature: Added full admin settings panels for all 5 regional gateways with live/test mode switches and webhook URL displays.
* Feature: Added automated refund processing support for Paystack, Tap, Mollie, Khalti, and eSewa via Omnify Payment Gateway Service.
* Feature: Added storefront checkout payment selection, automatic redirection, and transaction verification handlers for all new regional gateways.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.4.2 =
Version 1.4.2 introduces multi-item abandoned cart capture, two-way Stripe and PayPal refund webhook synchronization, partially refunded order status tracking, major database query optimizations across customer and product listings, coupon timezone accuracy, and automated database hygiene. Recommended for all stores.

= 1.4.1 =
Version 1.4.1 brings a complete redesign of the administrative Tools and Diagnostics pages, real-time database health metrics, streamlined CSV import/export, modernized REST API key and webhook managers, settings tabs polish, and bug fixes for storefront navigation. Recommended for all stores.

= 1.4.0 =
Version 1.4.0 introduces a major storefront product card redesign, 4-layout catalog switcher, full-width responsive image layouts, balanced typography, compact pill buttons, and unified Recently Viewed Products grid. Recommended for all stores.
