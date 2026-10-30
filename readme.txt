=== OmnifyWP eCommerce — Fast Online Store, Digital Downloads & Global Payments ===
Contributors: omnifywp
Tags: ecommerce, digital downloads, online store, payment gateway, checkout
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern, ultra-fast eCommerce for WordPress with built-in regional payment gateways, single-page checkout, and secure digital file protection.

== Description ==

**OmnifyWP eCommerce** is the modern, developer-first, and conversion-optimized eCommerce platform engineered for WordPress. Built specifically for creators, digital merchants, boutiques, and agencies who are tired of bloated plugins and expensive annual addon licenses, OmnifyWP delivers everything you need to build, launch, and scale a fast online store out of the box.

Whether you are selling **digital downloads** (ebooks, software, music, audio presets, design templates, photography, fonts, license keys), **physical merchandise** requiring granular shipping rules, or **bundled variable products**, OmnifyWP delivers a frictionless customer experience with sub-second page loads, a single-page checkout engine, and native payment gateways for every continent.

---

### Why Choose OmnifyWP eCommerce?

* **Save Over $800/Year in Extension Fees**: Stop paying $79–$199/year for separate gateway, abandoned cart, and digital download add-ons. OmnifyWP includes 12 native payment gateways, automated multi-item abandoned cart recovery, HMAC file encryption, customer wishlists, and multi-layout catalog switchers out of the box with zero subscription fees.
* **Ultra-Lightweight & Scalable SQL Architecture**: Engineered with a minimal frontend footprint (<35KB total scripts) and optimized database queries. Zero N+1 queries across catalog grids, single product cards, and customer portals. Features smart deferred order aggregations and indexed schema architecture for high-volume stores.
* **12 Global & Regional Payment Gateways Included Free**: Native support for Stripe, PayPal, Paddle (Merchant of Record / Global Tax & MoR), Paystack (Africa), Tap Payments (Middle East / GCC), Mollie (EU), Razorpay (India), Alipay & WeChat Pay (China), SSLCommerz (Bangladesh), Khalti & eSewa (Nepal), plus offline Direct Bank Transfer and COD.
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

#### 1. Product Types & Advanced Variation Builder
* **Simple Digital Products**: Instant access upon payment with file download limits, expiration dates, and HMAC link generation.
* **Simple Physical Products**: Inventory tracking, weight/dimensions, SKU, barcode (UPC/EAN), low-stock thresholds, and shipping calculations.
* **Variable Products**: Offer multiple sizes, colors, formats, or licensing tiers with unique SKUs, stock levels, and pricing.
* **Redesigned Product Variations Builder**:
    * **Unified Attribute Badge Controls**: Clean uppercase badge chips (SIZE, COLOR) paired with integrated borderless dropdowns and custom SVG chevron indicators.
    * **Laser-Aligned 3-Column Inputs**: Normalized 42px height, 8px radius inputs across SKU, Regular Price, and Sale Price with currency prefix indicators.
    * **Stock & Inventory Tracking**: Explicit "Inventory Tracking" toggle container perfectly aligned with Stock Qty and Stock Status fields.
    * **Availability & Preorders Card**: Structured fulfillment panel with "Allow backorders" and "Preorder" toggles, plus subgrid controls for Release Date, Preorder Limit, and Notice Message.
    * **Estimated Delivery Field**: Single-row full-width input with transport icon prefix and dedicated label spacing.
    * **Icon Action Tooltips**: Space-efficient 32x32px icon buttons (upload and remove) with accessible tooltips for image selection and deletion.
    * **Lightweight Variation Notes**: Fast, responsive native textareas replacing heavy TinyMCE toolbars.
* **Bundled Products**: Combine multiple catalog items into a single curated package with custom discount pricing.
* **Back-In-Stock Waitlist**: Dedicated customer opt-in on out-of-stock products/variations with automated email notifications when stock is replenished.
* **Product Upsells & Cross-Sells**: Configurable related product recommendations displayed strategically across product details and shopping cart pages.
* **Granular Product Taxonomies**: Built-in support for Hierarchical Categories, Searchable Tags, Brand Directories, and Global/Custom Variation Attributes.

#### 2. Modern Storefront & Conversion Architecture
* **4-Layout Catalog Switcher**: Shoppers can instantly toggle between 4-column dense grid, 3-column standard grid, 2-column large split view, and 1-column detailed list mode with zero layout shift.
* **Responsive Full-Width Image Cards**: Engineered full-width card imagery (`aspect-ratio: auto`, `object-fit: cover`) ensuring consistent card alignment across all WordPress themes.
* **Balanced Typography Hierarchy**: Refined title, price, description, and badge sizing with 2-line clamps that gracefully prevent clipping on long product names.
* **Sleek Pill Action Buttons**: Streamlined `+ Quick Add` and `View Details` action buttons with balanced padding and font size, avoiding row wrapping or awkward overflows.
* **Card Hover Action Toolbar**: Floating product card toolbar with Wishlist toggle, Quick View modal trigger, and Product Compare launcher.
* **Interactive Color Swatches**: Selectable color dots on catalog product cards with smooth active indicator states.
* **Quick Filter Pills Strip**: Dynamic horizontal filter bar for 1-click client-side filtering by Flash Deals, Bestsellers, New In, and top categories with dynamic counter badges.
* **Flash Sale Announcement Ticker**: Persistent countdown timer with 1-click coupon code copy button (`FLASH30`) and clipboard feedback.
* **Multi-Currency Switcher**: Dynamic currency selector (`[omnify_currency_switcher]`) with real-time conversion rates, 1-year cookie persistence, and smart payment gateway compatibility filtering.
* **Floating Slide-Out Mini-Cart**: Instant AJAX drawer/dropdown widget (`[omnify_mini_cart]`) with live line item quantity adjustment, item removal, and subtotal calculation.
* **Recently Viewed Products Carousel**: High-performance batch-loaded carousel/grid showing recently browsed items with cookie persistence and configurable product display counts.
* **Storefront Features & Trust Bar**: Configurable value propositions strip (secure payments, instant access, money-back guarantee, dedicated support) boosting checkout conversion.
* **Faceted Sidebar Filtering**: Instant price range sliders, category pills, brand selectors, and in-stock / on-sale status filters.
* **Storefront AJAX Quick Add**: Single-click add-to-cart with smooth micro-animations and live header badge updates.
* **High-Impact Product Details (PDP)**: Conversion-focused product pages with media galleries, color swatches, trust badges, feature callouts, and sticky purchase panels.
* **Integrated Customer Wishlist**: Allow shoppers to save favorite products for later, driving repeat visits and higher customer lifetime value.
* **Cart Scarcity Countdown**: Urgency-driven countdown timer with session storage persistence that drives higher checkout completion.
* **Free Shipping Progress Meter**: Dynamic visual bar motivating shoppers to add more products to their cart to unlock free delivery.

#### 3. Conversion-Optimized 1-Page Checkout Engine
* **Single-Page Checkout**: Streamlined checkout layout requiring zero page reloads, drastically reducing checkout drop-off.
* **1-Click Checkout Order Bump**: Eye-catching promotional callout directly in checkout with special discount pricing and instant order total recalculation.
* **Intelligent Multi-Item Abandoned Cart Recovery**: Automatically tracks abandoned checkouts for both single-product purchases and multi-item shopping carts. Dispatches automated personalized recovery email sequences with dynamic item summaries, incentive coupons, and 1-click cart restoration.
* **Country-First Dynamic Selectors**: Smart address selectors with automatic state/region filtering and saved address book autofill.
* **Flexible Account Handling**: Seamless support for guest checkouts, optional account creation, or automatic account provisioning.
* **Timezone-Aware Coupons & Discount Rules**: Percentage or fixed discounts with usage caps, minimum spend rules, and local store timezone expiration accuracy (`wp_timezone()`).
* **Express Digital Wallets**: Native support for Apple Pay, Google Pay, and one-click express payment buttons.
* **Guest Order Tracking**: Dedicated shortcode `[omnify_order_tracking]` enabling buyers to check order status, delivery progress, carrier tracking, and PDF invoices without an account.
* **Custom Offline Payment Methods**: Create unlimited custom offline payment options with bespoke instructions, titles, and IDs alongside BACS, Cheque, and COD.

#### 4. Enterprise-Grade Digital File Delivery
* **Cryptographically Signed URLs**: Generates HMAC-SHA256 authenticated links with customizable expiration windows and download attempt limits.
* **Secure Chunked Streaming**: Delivers files through 8KB memory-efficient streaming buffers that conceal your physical server paths and prevent hotlinking or memory exhaustion.
* **Fast Self-Service Customer Portal**: Embed `[omnify_customer_downloads]` or `[omnify_customer_portal]` to give buyers an instant, batch-loaded dashboard to access purchased files, view order histories, and manage billing details with sub-millisecond response times.
* **Comprehensive Download Logs**: Audit IP addresses, user agents, download counts, and bandwidth directly in your WordPress admin.

#### 5. Marketing, CRM, Analytics & Taxes
* **Executive Financial Dashboard**: Real-time KPI cards (Gross Sales, Net Sales, Orders, Average Order Value), 30-day health monitor, and revenue charts.
* **Customer Relationship Management (CRM)**: Track customer lifetime value (LTV), order frequency, saved addresses, and customer activity logs.
* **Automated Fraud Risk Scoring**: Calculates fraud risk levels (low, medium, high) based on IP geolocation, proxy indicators, billing/shipping mismatches, and transaction velocity.
* **Review Moderation System**: Dedicated workflow to approve, edit, filter, or trash user reviews, star ratings, and verified buyer badges.
* **17 Automated HTML Email Notifications**: Pre-designed responsive templates for order receipts, digital access, shipping tracking, pickup readiness, refunds, notes, back-in-stock alerts, admin alerts, and failed orders.
* **Native Custom SMTP Relay**: Built-in SMTP settings (host, port, encryption, authentication) ensuring 100% reliable inbox delivery without third-party mailer plugins.
* **Analytics & Conversion Tracking**: Built-in integration for Google Analytics 4 (GA4) and Meta Pixel (Facebook Pixel) with e-commerce purchase events.
* **Country-Specific Tax Rules**: Multi-tier tax tables with inclusive/exclusive pricing toggles, compound rates, shipping tax options, and digital tax exemptions.
* **Shipping Zones & Rules**: Define country- and region-based shipping rates, flat fees, and free shipping thresholds.
* **GDPR & Privacy Compliance**: Integrated with WordPress core privacy tools, including privacy policy templates and 1-click Personal Data Export & Erasure.
* **Automated Database Maintenance**: Scheduled hourly cron cleanup for expired carts, stale checkout sessions, and automated pending order cancellation.

#### 6. Admin Diagnostics, Seeding & Tools
* **Live Database Diagnostics Overview**: Real-time 4-KPI metric strip in Tools reporting live counts for Products, Orders, Customers, and Customer Reviews.
* **Demo Data Seeding & Granular Cleanup**: One-click demo data generation with animated progress bar; granular cleanup utilities for individual data types and elevated Danger Zone wiping.
* **Server Runtime Specifications Panel**: Diagnostic inspection of PHP version, MySQL version, WordPress core version, memory limits, and database table prefix.
* **Dataset Export & Import Suite**: One-click CSV exports for Products, Orders, and Customers with UTF-8 encoding; CSV drag-and-drop import container with column override option and CSV schema reference table.
* **Administrative Activity Audit Trail**: Filterable chronological log with Actor, Category, Search, and Date filters, active filter chips, category badge treatments, and irreversible log clearing.
* **Outbound Webhooks Engine**: Register unlimited endpoints with HMAC-SHA256 signature verification (`X-Omnify-Signature`), event subscriptions, retry queues with exponential backoff, and delivery logs.
* **REST API Keys Management**: Generate secure Consumer Key and Consumer Secret pairs with permission scopes (read, write, read/write) and one-click copy modal.
* **Payment Gateway & URL Diagnostics**: Live API verification cards for Stripe, PayPal, and Paddle testing authentication credentials, environment mode, and roundtrip latency; arbitrary URL connection checker with latency rating and filtered response headers.

#### 7. Built-In Payment Gateways & Integrations (Zero Addon Fees)
* **Stripe**: Credit/debit cards, Apple Pay, Google Pay, Stripe Checkout, plus automated two-way webhook sync for dashboard refunds (`charge.refunded`) and disputes (`charge.dispute.created`).
* **PayPal Commerce**: PayPal Express Checkout, Pay in 4, credit/debit cards, smart pay buttons, and automated refund webhook synchronization (`PAYMENT.CAPTURE.REFUNDED`).
* **Paddle (Merchant of Record)**: Complete global sales tax and VAT compliance across 200+ countries, SaaS billing, cards, PayPal, Apple Pay, Google Pay, iDEAL, wire transfers, and automated refund/adjustment synchronization via Paddle API.
* **Paystack (Africa)**: Market-leading payment infrastructure for Nigeria, Ghana, Kenya, and South Africa supporting cards, Mobile Money (M-Pesa, MTN, Airtel), EFT bank transfers, and USSD.
* **Tap Payments (Middle East & GCC)**: Full coverage across Saudi Arabia, UAE, Kuwait, Bahrain, Qatar, Oman, Egypt, and Jordan with mada, KNET, Benefit, NAPS, and cards.
* **Mollie (European Union)**: Seamless EU commerce supporting iDEAL, Bancontact, SEPA Direct Debit / Bank Transfer, Klarna, and cards.
* **Razorpay (India)**: Industry standard for India supporting UPI (Google Pay, PhonePe, Paytm), QR codes, cards, netbanking, and mobile wallets.
* **Khalti (Nepal)**: Nepal's popular digital wallet and mobile banking via the official ePayment v2 API with automated verification.
* **eSewa (Nepal)**: Nepal's pioneer digital payment wallet with HMAC-SHA256 signature verification.
* **Alipay & WeChat Pay**: Comprehensive Chinese cross-border mobile commerce with dynamic QR code checkout and automated callback verification.
* **SSLCommerz (Bangladesh)**: Top gateway for Bangladesh supporting cards, mobile banking (bKash, Nagad, Rocket), and internet banking with IPN validation.
* **Offline & Manual Payments**: Configurable instructions for Direct Bank Transfer (BACS), Cheque Payments, and Cash on Delivery (COD).
* **One-Click & Partial Refunds**: Issue full or partial refunds directly from the WordPress order screen, with dedicated `Partially Refunded` status tracking and automated inventory restocking.
* **Analytics Integrations**: Native Google Analytics 4 (GA4) and Meta Pixel (Facebook Pixel) tracking standard e-commerce purchase events.

#### 8. Shortcodes & 20 Native Gutenberg Blocks
OmnifyWP features an agency-grade suite of 20 Gutenberg blocks with live debounced previews, theme.json Global Styles harmony, and 4 design style variations:
* **`[omnify_storefront]` / `omnify/product-grid`**: Dynamic catalog with layout switcher, category filter pills, flash sale countdown, and faceted filters.
* **`[omnify_cart]` / `omnify/cart`**: Full shopping cart with free-shipping meter, trust badges, and cross-sell recommendations.
* **`[omnify_mini_cart]` / `omnify/mini-cart`**: Floating or embedded slide-out drawer with live item counter pill and checkout button.
* **`[omnify_checkout]` / `omnify/checkout`**: 1-Page checkout engine with 1-col/2-col layouts, cart reservation timer, and guarantee seals.
* **`omnify/order-bump`**: High-converting 1-click checkout upsell checkbox with instant AJAX cart addition without page reload.
* **`omnify/sticky-add-to-cart`**: Floating mobile/desktop conversion bar with thumbnail, price, quantity stepper, and quick buy.
* **`omnify/free-shipping-goal`**: Gamified progress bar calculating remaining cart amount in real-time with confetti celebrations.
* **`omnify/trust-badge-bar`**: Security & guarantee strip (SSL 256-Bit, 30-Day Guarantee, instant HMAC dispatch, payment badges).
* **`[omnify_customer_portal]` / `omnify/customer-portal`**: Customer account dashboard (orders with 1-click re-order, download locker, invoices, wishlist).
* **`[omnify_customer_downloads]` / `omnify/customer-downloads`**: Dedicated customer digital file locker with HMAC signed URLs and expiry notices.
* **`[omnify_order_tracking]` / `omnify/order-tracking`**: Guest order tracking form with visual delivery timeline stepper and direct support links.
* **`[omnify_currency_switcher]` / `omnify/currency-switcher`**: Multi-currency dropdown or pill strip supporting 22 global currencies with live exchange rates.
* **Atomic Micro-Blocks Suite**: `omnify/product-image`, `omnify/product-title`, `omnify/product-price`, `omnify/product-rating`, `omnify/product-badge`, `omnify/product-button`.
* **`omnify/template-cloud`**: In-editor modal offering 1-click insertion of 20+ pre-designed niche store layouts and sales funnels.
* **`core/query` (*Omnify Products* Variation)**: Core WordPress Query Loop variation with atomic block scaffolding.
* **Full Site Editing (FSE)**: Native `single-omnify_product`, `archive-omnify_product`, and `omnify-header-cart` block templates for block themes.
* **`[omnify_download_page]`**: Dedicated secure download endpoint.

---

### Live Interactive Demo & Resources

* Official Website: [https://omnifywp.com](https://omnifywp.com)
* Live Demo Sandbox: [https://demo.omnifywp.com](https://demo.omnifywp.com)
* WordPress Playground Sandbox (Zero Setup): [Launch Playground Demo](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2Fomnifywp%2Fomnifywp-ecommerce%2Fmain%2Fblueprint.json)
* GitHub Repository: [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)
* Official Documentation: [https://omnifywp.com/doc/](https://omnifywp.com/doc/)

== Blocks ==

This plugin provides 20 blocks.

= Product Grid =
Display an interactive, responsive product grid with instant search and filters.

= Shopping Cart =
Display the full shopping cart with dynamic quantity controls and coupon input.

= Mini Cart =
A floating or embedded mini cart button with slide-out drawer.

= Checkout =
High-converting one-page checkout experience with 14+ global payment gateways.

= Customer Portal =
Self-service portal for customer order history, file downloads, and account details.

= Digital Downloads Locker =
Dedicated customer digital file locker with HMAC signed URLs, expiration notices, and limits.

= Order Tracking =
Real-time visual order and shipment tracking timeline.

= Product Card =
Display an individual high-converting product card with 1-click buy.

= Currency Switcher =
Interactive currency switcher dropdown supporting 22 global currencies.

= Order Bump =
High-converting 1-click checkout upsell checkbox with instant AJAX cart addition.

= Sticky Add to Cart =
High-conversion floating bottom/top bar with product thumbnail, price, and instant checkout.

= Free Shipping Goal =
Gamified dynamic progress bar displaying remaining amount for free shipping with live celebrations.

= Trust Badges & Guarantee Strip =
Showcase SSL security, payment providers, fast shipping, and money-back guarantees.

= Product Image =
Product thumbnail with custom aspect ratio, zoom, and secondary flip.

= Product Title =
Dynamic product title with customizable heading tag and link.

= Product Price =
Dynamic product price with sale comparison and discount pill.

= Product Rating =
Product review star rating and review count breakdown.

= Product Badge =
Highlight tags like Sale, Out of Stock, or Verified Download.

= Product Button =
Interactive Add to Cart or Buy Now button with quantity stepper.

= Template Cloud =
Browse and insert pre-built, high-converting eCommerce starter layouts with 1-click.

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
Yes! 100% free. Gateways like Paddle, Paystack, Tap Payments, Mollie, Razorpay, SSLCommerz, Khalti, eSewa, Stripe, and PayPal are pre-integrated into the core plugin. You only pay standard transaction processing fees directly to the payment processor—no plugin licensing fees or subscription upsells.

= Can I sell both digital downloads and physical products? =
Yes. OmnifyWP seamlessly supports digital files (e-books, audio, presets, software, PDFs), physical goods requiring shipping addresses, variable products with format/size attributes, and bundled packages.

= How does digital download protection work? =
Files uploaded to OmnifyWP are delivered through cryptographically signed HMAC-SHA256 URLs that expire after your chosen time limit or download count. Files are streamed in memory-efficient 8KB chunks without ever exposing the real server path. Customers can also access their files at any time through the secure self-service Customer Portal.

= Is OmnifyWP compatible with my WordPress theme and page builders? =
Yes. OmnifyWP outputs clean, self-contained semantic HTML and scoped CSS that automatically blends with popular WordPress themes (Astra, GeneratePress, Kadence, OceanWP, Hello Elementor, Divi) and the Gutenberg Block Editor.

= Can I issue customer refunds? =
Yes. OmnifyWP provides one-click refund processing directly from the order details screen for supported gateways (Stripe, PayPal, Paddle, Paystack, Tap Payments, Mollie, Khalti, eSewa, Razorpay, SSLCommerz).

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
7. Payment gateway center supporting 12 built-in global and regional gateways (Stripe, PayPal, Paddle, Razorpay, Mollie, Paystack, Tap Payments, etc.) with zero platform fees.
8. Automated store email notifications with 17 automated triggers, visual HTML template preview modal, and dynamic merge tags.
9. Modern Elessi-style single product page with 3-column conversion layout, vertical gallery, color swatches, trust badges, and instant checkout.
10. Conversion-optimized cart & one-page checkout with urgency countdown timer, free shipping progress bar, express digital wallets, and 1-click order bump.

== Changelog ==

= 1.4.3 =
* Feature: Unified Attribute Badge Controls — Redesigned variation attribute selectors into modern badge chips combining clear uppercase labels with streamlined native select dropdowns and custom SVG chevron indicators.
* Feature: Availability & Preorder Management — Integrated structured availability panels with "Allow backorders" and "Preorder" toggles, plus subgrid controls for release date, limits, and notice messages.
* UI/UX: Space-Efficient Action Tooltips — Converted variation image action buttons into clean 32×32px icon buttons (upload and remove) with accessible tooltips, eliminating text truncation in narrow columns.
* Design: Laser-Aligned Product Form Grids — Unified field heights (42px) and border radii (8px) across SKU, Regular Price, Sale Price, Inventory Tracking, Stock Qty, and Stock Status.
* Design: Stripped Inner Input Borders — Resolved nested double borders and inner rounded corners inherited from global input/select styling within prefixed containers and attribute pills.
* Performance & UX: Lightweight Variation Notes — Replaced heavy TinyMCE WYSIWYG editor instances with responsive native textareas to eliminate editor toolbar bloat and improve render performance.
* Design: Clean Variation Card Flow — Zeroed unwanted card gaps and normalized outer row padding for seamless card header and body alignment.

For the complete historical changelog and previous release notes, visit [https://omnifywp.com/changelog/](https://omnifywp.com/changelog/).

== Upgrade Notice ==

= 1.4.3 =
Version 1.4.3 delivers major design, alignment, and UX polish to the Product Editor and Variations builder, including unified attribute badge controls, icon-with-tooltip media actions, eliminated nested borders, and laser-aligned pricing inputs. Recommended for all stores.
