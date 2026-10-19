# Changelog

All notable changes to OmnifyWP eCommerce are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.2] - 2026-10-18

### Added
- **Multi-Item Abandoned Cart Capture & 1-Click Recovery**: Complete shopping baskets with multiple items, variations, quantities, and prices are captured on checkout email input. Automated recovery reminders deliver encrypted 1-click tokenized checkout links that reconstitute the entire customer cart.
- **Automated Gateway Refund & Dispute Webhook Sync**: Native webhooks for Stripe (`charge.refunded`, `charge.dispute.created`) and PayPal (`PAYMENT.CAPTURE.REFUNDED`, `CUSTOMER.DISPUTE.CREATED`). Synchronizes external reversals directly into order records, audit notes, and access control.
- **Partially Refunded Order Workflow**: New core order status `partially_refunded`. Allows processing partial refunds via admin controls or incoming webhooks, calculating refunded balances while preserving file entitlements for non-refunded items.
- **N+1 Query Elimination in Product File Repositories**: New high-performance batch hydration method `Product_File_Repository::all_for_products()` replacing iterative per-product queries with a single indexed SQL query across product catalog loops and REST endpoints.
- **Customer Directory Pagination & Query Optimization**: High-performance SQL pagination utilizing optimized record counting and indexed sorting for enterprise stores with large customer datasets.
- **Timezone-Aware Coupon Expiration**: Coupon expiration validation now evaluates against the WordPress site timezone (`wp_timezone()`), eliminating premature or delayed coupon expiry caused by server UTC offsets.
- **Automated Database Maintenance & Cron Hygiene**: Scheduled recurring background maintenance cron `omnify_daily_database_cleanup` to prune expired abandoned carts past retention thresholds, clear stale session transients, and optimize database tables.

### Changed
- **Documentation Suite & Web Guides**: Updated Developer Documentation (`docs/DEVELOPER_DOCS.md`), User Manual (`docs/USER_DOCUMENTATION.md`), interactive Developer Portal (`docs/index.html`), and User Guide Web App (`docs/user-guide.html`) with 1.4.2 features, schemas, and API contracts.

## [1.4.1] - 2026-10-15

### Added
- **Live Database Diagnostics KPI Bar**: Real-time 4-card metric strip in Tools displaying total count for Products, Orders, Customers, and Customer Reviews.
- **Granular Database Cleanup Tools**: Specialized individual cleanup operations for Products, Orders, Customers, Reviews, Coupons, and Activity Logs with impact estimations.
- **Server Runtime & Specifications Panel**: System information panel inspecting PHP version, MySQL version, WordPress core version, memory limit, maximum execution time, and database table prefix.
- **Outbound Webhooks Manager**: Support for registering external webhooks with HMAC-SHA256 signature verification headers, delivery URLs, and event subscription toggles (`order.created`, `order.status_changed`, `order.completed`, `order.refunded`, `order.cancelled`, `customer.created`).
- **Payment Gateway Connectivity Diagnostics**: Live API verification cards for Stripe and PayPal testing authentication credentials, environment mode, and roundtrip latency.
- **Remote URL Health & Latency Checker**: Diagnostic utility testing connectivity, response codes, roundtrip latency rating (Fast, Good, Slow), and filtered HTTP response headers.
- **Dataset Export & Import Suite**: Export cards for Products, Orders, and Customers with UTF-8 encoding; CSV drag-and-drop import container with column override option and CSV schema reference table.

### Changed
- **Admin Tools & Seeding Redesign**: Modernized layout for System Tools, Import/Export, Activity Log, and API Keys sub-sections using unified design tokens.
- **Activity Log Audit Trail**: Replaced legacy styling with modern table components, circular actor avatars, timestamp formatting, and monospaced action tags.
- **Category Badge System**: Replaced hardcoded category styles with semantic badge styling across orders, products, coupons, settings, customers, and reviews.
- **Administrative Settings Modernization**: Visual design and UX polish across General, Localization, Currency, Payment, Tax, Shipping, Email, Customer Portal, and Advanced settings tabs.
- **Demo Data Seeding UI**: Upgraded generator card with structured checklist features, badge, and emerald animated progress bar.

### Fixed
- **Pagination Character Encodings**: Fixed garbled character entities in admin activity log pagination controls.
- **Nested Button Markup**: Resolved invalid nested button HTML tags inside payment gateway connectivity diagnostic cards.
- **Storefront Details Route**: Fixed URL query parameter handling between storefront catalog grid and single product details pages.
- **Zero-Emoji Compliance**: Replaced legacy character glyphs across administrative interfaces, scripts, and documentation with Dashicons and text labels.

---

## [1.4.0] - 2026-10-14

### Added
- **4-Layout Catalog Switcher Toolbar**: 1-click layout switching between 4-column dense grid, 3-column standard grid, 2-column large grid, and detailed list view with `localStorage` persistence.
- **Quick Filter Pills Strip**: Dynamic horizontal filter bar for 1-click client-side filtering by Flash Deals, Bestsellers, New In, and top categories with dynamic counter badges.
- **Floating Product Card Actions**: Card hover toolbar with Wishlist toggle, Quick View modal launcher, and Product Compare trigger.
- **Interactive Color Swatches**: Selectable color dots on catalog product cards with smooth active indicator states.
- **Flash Sale Announcement Ticker**: Persistent countdown timer with 1-click coupon code copy button (`FLASH30`) and clipboard feedback.
- **Recently Viewed Products Grid**: Cookie-persisted recently viewed products section with responsive 4-column display matching the primary catalog.
- **Storefront Value Propositions Bar**: 4 highlight slots (Security, Speed, Guarantee, Support).

### Changed
- **Product Card Visual Architecture**: Overhauled product cards with full-width responsive image containers preventing distortion across third-party WordPress themes.
- **Card Padding & Spacing**: Unified density-specific card padding (18px for 2-col, 15px for 3-col, 12px for 4-col).
- **Typography & Clamping**: 2-line title clamps preventing text clipping on long product titles; streamlined description display.
- **Action Buttons**: Compact pill buttons (`+ Quick Add` / `View Details`) with aligned heights.

### Fixed
- **Mobile Filter Triggers**: Ensured reliable category and brand filter synchronization on mobile devices.
- **Theme Specificity**: Enhanced CSS override specificity for Astra, GeneratePress, and Kadence themes.

---

## [1.3.0] - 2026-10-08

### Added
- **Order Lifecycle Service**: Centralized order lifecycle automation in `Omnify_Order_Lifecycle_Service` for instant digital access entitlement, refund revocation, and cancellation stock restoration.
- **Database Index Optimization**: Compound indexes on `omnify_orders` (`transaction_id`, `status`, `created_at`) for high-concurrency lookup efficiency.

### Fixed
- **Khalti Endpoint Resolution**: Fixed Khalti live endpoint URL resolution in `verify_khalti_payment()` ensuring production payments target `khalti.com`.
- **Webhook Secret Validation**: Enforced strict signature validation on Razorpay and Paystack webhook endpoints.
- **Coupon Expiration Logic**: Evaluated coupon expiry to cover the full end of the expiration day (23:59:59).
- **Customer Portal Filtering**: Ensured paying customers retain access to purchased downloads even if a product is subsequently drafted or archived.

---

## [1.2.0] - 2026-09-30

### Added
- **Guest Order Tracking**: Shortcode `[omnify_order_tracking]` for customer invoice lookup without requiring an account.
- **Chunked File Streaming**: Replaced memory buffering with 8KB chunked streaming to support large digital downloads without server timeouts.
- **Automated Regional Refunds**: Integrated live API refund processing for Paystack, Tap Payments, Mollie, Khalti, and eSewa.
- **Customer Portal 2.0**: Unified portal UI with admin design tokens, KPI cards, download history, and address book.

### Fixed
- **Review Purchase Verification**: Fixed verification logic allowing physical product buyers to submit reviews.
- **Windows File Normalization**: Resolved false 403 Forbidden errors on digital downloads in Windows server environments via `wp_normalize_path()`.

---

## [1.1.0] - 2026-09-15

### Added
- **Paystack Payment Gateway**: Native integration for African markets supporting cards, mobile money, and USSD.
- **Tap Payments Gateway**: Native integration for Middle East & GCC supporting mada, KNET, Benefit, NAPS, and cards.
- **Mollie Payment Gateway**: Native integration for European Union supporting iDEAL, Bancontact, SEPA, and cards.
- **Khalti Digital Wallet**: Native integration for Nepal supporting ePayment v2 API.
- **eSewa ePay v2**: Native integration for Nepal with cryptographic HMAC-SHA256 signature verification.

---

## [1.0.0] - 2026-09-01

### Added
- **Core Engine**: Initial release of OmnifyWP eCommerce.
- **Relational SQL Architecture**: 18 dedicated normalized database tables bypassing `wp_postmeta`.
- **Digital File Protection**: Expiring HMAC-SHA256 tokens and protected download streaming.
- **Single-Page Checkout**: Streamlined checkout engine with country/state autocomplete and guest checkout.
- **Global Gateways**: Native support for Stripe, PayPal, COD, and Direct Bank Transfer.
- **Storefront Catalog**: Responsive product catalog with faceted sidebar filters and AJAX Quick Add.
