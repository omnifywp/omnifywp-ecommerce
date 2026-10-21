# OmnifyWP eCommerce — Complete Developer Documentation

> **Version:** 1.4.3  
> **Official Website:** [https://omnifywp.com](https://omnifywp.com)  
> **wp.org Directory:** [https://wordpress.org/plugins/omnifywp-ecommerce/](https://wordpress.org/plugins/omnifywp-ecommerce/)  
> **GitHub Repository:** [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)  
> **Minimum Requirements:** PHP 8.2+, WordPress 6.5+, MySQL 5.7+ / MariaDB 10.3+  
> **Interactive Documentation:** Open [`docs/index.html`](./index.html) or [`docs/user-guide.html`](./user-guide.html)  

---

## Table of Contents
1. [Introduction & Core Philosophy](#1-introduction--core-philosophy)
2. [Plugin Lifecycle & Container Architecture](#2-plugin-lifecycle--container-architecture)
3. [Database Engine & 27 Relational Tables](#3-database-engine--27-relational-tables)
   - [Architectural Schema Overview](#architectural-schema-overview)
   - [Deep Column & Index Breakdown](#deep-column--index-breakdown)
4. [Soft Deletions & Cache Invalidation System](#4-soft-deletions--cache-invalidation-system)
5. [Repositories SDK & Query APIs](#5-repositories-sdk--query-apis)
   - [Product Repository (`Omnify_Product_Repository`)](#product-repository)
   - [Order Repository (`Omnify_Order_Repository`)](#order-repository)
   - [Customer Repository (`Omnify_Customer_Repository`)](#customer-repository)
   - [Coupon Repository (`Omnify_Coupon_Repository`)](#coupon-repository)
   - [Review Repository (`Omnify_Review_Repository`)](#review-repository)
   - [Abandoned Cart Repository (`Omnify_Abandoned_Cart_Repository`)](#abandoned-cart-repository)
   - [Digital Download & Access Repositories](#digital-download--access-repositories)
   - [Wishlist Repository (`Omnify_Wishlist_Repository`)](#wishlist-repository)
6. [Storefront & Checkout Internals](#6-storefront--checkout-internals)
   - [Shortcodes Directory & Parameters Reference](#shortcodes-directory)
   - [Client-Side State Engine (`window.omnifyStorefront`)](#client-side-state-engine)
   - [Flash Sale Ticker & Live Countdown Architecture (`initFlashSaleCountdown`)](#flash-sale-ticker-architecture)
   - [1-Click Coupon Code Copy API (`initCouponPillCopy`)](#coupon-code-copy-api)
   - [Interactive Quick Filter Pills Engine (`initQuickFilterPills`)](#quick-filter-pills-engine)
   - [Product Card Hover Tools & Action Triggers (`.omnify-card-hover-tools`)](#product-card-hover-tools)
   - [Interactive Color Swatches Engine (`initCardMockSwatches`)](#interactive-color-swatches-engine)
   - [Recently Viewed Products Engine & Cookie Storage (`initStorefrontRecentlyViewed`)](#recently-viewed-products-engine)
   - [Free Shipping Progress Bar & Dynamic Calculation](#free-shipping-progress-bar)
   - [Product Card Data Contract & Attribute Reference](#product-card-data-contract)
   - [Third-Party Theme Compatibility & Specificity Shielding](#theme-compatibility-and-specificity)
7. [Payment Gateways & Webhooks Engine](#7-payment-gateways--webhooks-engine)
   - [Supported Gateways & Secure-by-Default Architecture](#supported-gateways)
   - [Complete Webhook Endpoints & Verification Engine](#webhook-endpoints)
   - [Outbound Webhooks Engine & Signature Verification](#outbound-webhooks-engine)
   - [Live Diagnostics & Connectivity Testing APIs](#live-diagnostics-and-testing-apis)
   - [Building Custom Payment Gateways (`Payment_Gateway_Interface`)](#building-custom-payment-gateways)
   - [Automatic File Revocation on Refund](#automatic-file-revocation-on-refund)
8. [Cryptographic Digital Locker Engine](#8-cryptographic-digital-locker-engine)
   - [HMAC-SHA256 Tokenization](#hmac-sha256-tokenization)
   - [Protected 8KB Chunked File Streaming](#protected-8kb-chunked-file-streaming)
   - [Server Hardening Rules (Apache & Nginx)](#server-hardening-rules)
9. [REST API Complete Reference](#9-rest-api-complete-reference)
   - [Authentication Methods](#authentication-methods)
   - [Storefront Public Endpoints](#storefront-public-endpoints)
   - [Administrative Protected Endpoints](#administrative-protected-endpoints)
   - [Request & Response Payload Schemas](#request--response-payload-schemas)
10. [Action & Filter Hooks Directory](#10-action--filter-hooks-directory)
11. [Storefront Template Overriding & Theme Integration](#11-storefront-template-overriding--theme-integration)
12. [Developer Recipes & Code Examples](#12-developer-recipes--code-examples)
13. [Roles, Capabilities & Staff Permissions](#13-roles-capabilities--staff-permissions)
14. [Analytics & Conversion Tracking (GA4 + Meta Pixel)](#14-analytics--conversion-tracking-ga4--meta-pixel)
15. [Privacy, GDPR & Data Lifecycle](#15-privacy-gdpr--data-lifecycle)
16. [Shipping Zones, Tax Rules & Multi-Currency](#16-shipping-zones-tax-rules--multi-currency)
17. [Product Variations, Availability & Preorders](#17-product-variations-availability--preorders)
18. [Background Jobs, Cron & Maintenance](#18-background-jobs-cron--maintenance)
19. [Admin Pages & Settings Reference](#19-admin-pages--settings-reference)
20. [Email Template Merge Tags](#20-email-template-merge-tags)
21. [CSV & XML Import/Export Schema](#21-csv--xml-importexport-schema)
22. [Performance, Caching & Scaling](#22-performance-caching--scaling)
23. [Security Model & Hardening](#23-security-model--hardening)
24. [Glossary](#24-glossary)

---

> **How to use this documentation**
> - **New to Omnify?** Read §1 Introduction → §2 Lifecycle & Container → §3 Database Engine.
> - **Building an integration?** Jump to §9 REST API Reference → §10 Hooks Directory → §12 Developer Recipes.
> - **Shipping a production store?** Review §7 Payment Gateways, §13–§18 platform features, and §23 Security & Hardening.
> - **Looking something up?** The HTML portal (`docs/index.html`) has full-text search (Ctrl/Cmd-K) and a live Hooks explorer.

---

## 1. Introduction & Core Philosophy

**OmnifyWP eCommerce** is a high-performance, developer-first eCommerce engine designed to overcome the performance bottlenecks of legacy WordPress commerce plugins:

- **Decoupled Architecture**: Product, order, customer, and inventory records live in dedicated normalized SQL tables instead of `wp_posts` and `wp_postmeta`.
- **Zero-Postmeta Latency**: Horizontal schema with indexed columns enables sub-millisecond query execution even with millions of rows.
- **Headless-Ready**: Clean REST APIs under `/wp-json/omnify/v1` with API key authentication, JSON responses, and decoupled checkout endpoints.
- **Enterprise Digital Locker**: Built from the ground up for software, audio, video, courses, and digital assets with HMAC-SHA256 link signing, chunked memory-safe streaming, and automated license revocation upon refund.

---

## 2. Plugin Lifecycle & Container Architecture

Omnify uses a centralized dependency injection container (`Omnify\eCommerce\Support\Omnify_Container`) managed by the singleton `Omnify\eCommerce\Omnify_Plugin`.

### Resolving Dependencies
```php
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;

// Fetch container instance
$container = omnify()->container();

// Resolve auto-wired repositories or services
$product_repo = $container->get(Omnify_Product_Repository::class);
$order_repo   = $container->get(Omnify_Order_Repository::class);
```

### Bootstrap Phases
1. **Activation Hook (`Omnify_Plugin::activate`)**:
   - Executes `Omnify_Migrations::migrate()` to create custom tables.
   - Registers scheduled cron jobs for abandoned cart recovery (`Omnify_Abandoned_Cart_Service`).
   - Automatically generates essential store pages: Storefront (`[omnify_storefront]`), Cart (`[omnify_cart]`), Checkout (`[omnify_checkout]`), Customer Portal (`[omnify_customer_portal]`), Secure Download (`[omnify_download_page]`), and Order Tracking (`[omnify_order_tracking]`).
2. **Runtime Boot (`Omnify_Plugin::boot`)**:
   - Compares database version against code migration version.
   - Binds REST API routes (`rest_api_init`).
   - Registers WP admin pages, settings tabs, and assets (`admin_menu`, `admin_enqueue_scripts`).
   - Hooks download interceptor on `template_redirect`.
   - Initializes user roles: `shop_manager` and `shop_clerk` with `manage_omnify` capability.

---

## 3. Database Engine & 27 Relational Tables

### Architectural Schema Overview

All custom tables use the `$wpdb->prefix . 'omnify_'` naming convention and InnoDB engine with `utf8mb4_unicode_ci` or standard WordPress charset collation.

| Table Name | Primary Purpose | Key Indices |
| :--- | :--- | :--- |
| `wp_omnify_products` | Master product catalog (simple, variable, bundle, download) | `PRIMARY (id)`, `UNIQUE KEY (slug)`, `KEY status_type (status, type)` |
| `wp_omnify_product_variations` | Matrix variations (SKU, price, attributes) | `PRIMARY (id)`, `KEY (product_id)` |
| `wp_omnify_product_files` | Digital downloadable files & file metadata | `PRIMARY (id)`, `KEY (product_id)`, `KEY (expires_at)` |
| `wp_omnify_product_categories` | Product category taxonomy associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_product_brands` | Product brand associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_product_tags` | Product tag associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_customers` | Customer profiles, default addresses, and tax exemption status | `PRIMARY (id)`, `UNIQUE KEY (email)`, `KEY (user_id)`, `KEY (status)` |
| `wp_omnify_customer_notes` | Private admin CRM notes per customer | `PRIMARY (id)`, `KEY (customer_id)`, `KEY (customer_visible)` |
| `wp_omnify_customer_activity` | Event timeline (orders, logins, address changes) | `PRIMARY (id)`, `KEY (customer_id)`, `KEY (activity_type)` |
| `wp_omnify_customer_access` | Digital file entitlements granting access to files | `PRIMARY (id)`, `UNIQUE KEY customer_product (customer_id, product_id)` |
| `wp_omnify_downloads` | Download logs with IP, user-agent, and status tracking | `PRIMARY (id)`, `KEY (product_id)`, `KEY (file_id)`, `KEY (customer_id)` |
| `wp_omnify_email_logs` | Outbound transactional email dispatch log & retry queue | `PRIMARY (id)`, `KEY (trigger_name)`, `KEY (status)` |
| `wp_omnify_orders` | Financial transactions, tax, shipping, and customer references | `PRIMARY (id)`, `UNIQUE KEY (order_number)`, `KEY customer_status (customer_id, status)` |
| `wp_omnify_order_items` | Snapshot of purchased products, prices, and quantities | `PRIMARY (id)`, `KEY (order_id)`, `KEY (product_id)` |
| `wp_omnify_order_notes` | Staff comments & customer-facing order updates | `PRIMARY (id)`, `KEY (order_id)`, `KEY (customer_visible)` |
| `wp_omnify_abandoned_carts` | Incomplete checkout sessions, tokens, and recovery reminders | `PRIMARY (id)`, `UNIQUE KEY (token)`, `KEY email_status (email, status)` |
| `wp_omnify_wishlists` | Customer product favorites associations | `PRIMARY (id)`, `UNIQUE KEY user_product (user_id, product_id)` |
| `wp_omnify_coupons` | Promotion codes, discount rules, usage limits, and expiry | `PRIMARY (id)`, `UNIQUE KEY (code)` |
| `wp_omnify_reviews` | Ratings, moderation status, and customer reviews | `PRIMARY (id)`, `KEY (product_id)`, `KEY (status)` |
| `wp_omnify_metadata` | Extensible key/value metadata for products, orders, and customers | `PRIMARY (id)`, `KEY object_type (object_type, object_id)` |
| `wp_omnify_webhook_events` | Inbound gateway webhook event ledger (dedupe & audit) | `PRIMARY (id)`, `KEY gateway (gateway)`, `KEY event_id (event_id)` |
| `wp_omnify_webhooks` | Registered outbound webhook endpoints & topic subscriptions | `PRIMARY (id)`, `KEY (status)` |
| `wp_omnify_webhook_delivery_logs` | Outbound webhook delivery attempts, HTTP status, and latency | `PRIMARY (id)`, `KEY (webhook_id)`, `KEY (status)` |
| `wp_omnify_inventory_logs` | Stock movement audit trail (reductions, restocks, adjustments) | `PRIMARY (id)`, `KEY (product_id)`, `KEY (variation_id)` |
| `wp_omnify_stock_subscribers` | Back-in-stock waitlist opt-ins per product/variation | `PRIMARY (id)`, `KEY (product_id)`, `KEY (variation_id)` |
| `wp_omnify_admin_activities` | Admin action audits and security trace logs | `PRIMARY (id)`, `KEY (user_id)`, `KEY (action)` |
| `wp_omnify_api_keys` | REST API consumer key/secret pairs and permission scopes | `PRIMARY (id)`, `UNIQUE KEY (consumer_key)`, `KEY (user_id)` |

### Deep Column & Index Breakdown

#### 1. `wp_omnify_products` (Core Catalog)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Unique product ID.
- `name` (VARCHAR(190) NOT NULL): Product display name.
- `slug` (VARCHAR(190) NOT NULL UNIQUE): URL-safe permalink identifier.
- `type` (VARCHAR(32) NOT NULL DEFAULT `download`): Product architecture type (`simple`, `variable`, `download`, `bundle`).
- `status` (VARCHAR(32) NOT NULL DEFAULT `draft`): Catalog visibility status (`published`, `draft`, `trash`).
- `price` (DECIMAL(18,6) NOT NULL DEFAULT 0): Regular selling price.
- `sale_price` (DECIMAL(18,6) NULL): Discounted price if promotional pricing is active.
- `currency` (CHAR(3) NOT NULL DEFAULT 'USD'): ISO 4217 currency code for the price columns.
- `sku` (VARCHAR(100) NULL): Stock Keeping Unit identifier.
- `manage_stock` (TINYINT NOT NULL DEFAULT 0): Inventory tracking toggle (`1` = active).
- `stock_qty` (INT NULL DEFAULT 0): Available inventory count.
- `stock_status` (VARCHAR(32) NOT NULL DEFAULT `instock`): Stock status string.
- `allow_backorders` (TINYINT NOT NULL DEFAULT 0), `preorder_enabled` (TINYINT NOT NULL DEFAULT 0): Availability & preorder flags.
- `preorder_release_date` (DATE NULL), `preorder_limit` (INT UNSIGNED NULL), `preorder_message` (VARCHAR(255) NULL): Preorder release controls.
- `thumbnail_id` (BIGINT UNSIGNED NULL): WordPress attachment ID of the primary image.
- `gallery_ids` (LONGTEXT NULL): JSON array of secondary gallery attachment IDs.
- `description` (LONGTEXT NULL) / `short_description` (TEXT NULL): Full and excerpt product copy.
- `attributes`, `variation_settings` (LONGTEXT NULL): Serialized attribute axes and variation configuration.
- `weight`, `length`, `width`, `height` (DECIMAL(10,2) NULL): Physical shipping dimensions.
- `upsell_ids`, `cross_sell_ids`, `bundled_ids` (TEXT NULL): Related-product and bundle associations.
- `download_expiry_days` (INT UNSIGNED NULL), `refund_enabled` (TINYINT NOT NULL DEFAULT 1): Digital delivery and refund policy.
- `created_at`, `updated_at`, `deleted_at` (DATETIME NULL): Audit timestamps.

#### 2. `wp_omnify_orders` (Financial Transactions)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Internal order ID.
- `order_number` (VARCHAR(100) NULL, `UNIQUE KEY`): Sequential or randomized customer-facing reference (e.g. `#1042`).
- `customer_id` (BIGINT UNSIGNED NULL): Foreign key referencing `wp_omnify_customers.id`.
- `status` (VARCHAR(32) NOT NULL DEFAULT `pending`): Order state machine value (`pending`, `pending_payment`, `processing`, `completed`, `on_hold`, `cancelled`, `partially_refunded`, `refunded`, `failed`).
- `currency` (CHAR(3) NOT NULL DEFAULT 'USD'): ISO 4217 currency code.
- `subtotal` (DECIMAL(18,6) NOT NULL DEFAULT 0): Sum of line items before tax and shipping.
- `discount_amount` (DECIMAL(18,6) NOT NULL DEFAULT 0): Applied coupon discount amount.
- `tax` (DECIMAL(18,6) NOT NULL DEFAULT 0): Calculated sales tax amount (with `tax_label`, `tax_rate`, `tax_reporting_code`, `tax_inclusive`, `tax_shipping` companions).
- `shipping_total` (DECIMAL(18,6) NOT NULL DEFAULT 0): Shipping fee charged.
- `total` (DECIMAL(18,6) NOT NULL DEFAULT 0): Grand total charged to customer.
- `refunded_amount` (DECIMAL(18,6) NOT NULL DEFAULT 0): Cumulative refunded amount for full and partial refunds.
- `payment_method` (VARCHAR(80) NULL): Gateway identifier (e.g., `stripe`, `paypal`, `razorpay`, `cod`, `bacs`).
- `transaction_id` (VARCHAR(190) NULL): Payment gateway reference token (e.g. `pi_3L714q...`).
- `fulfillment_status` (VARCHAR(32) NOT NULL DEFAULT `none`), `tracking_number` (VARCHAR(100) NULL), `tracking_carrier` (VARCHAR(100) NULL): Fulfillment & shipment tracking.
- `fraud_status` (VARCHAR(32) NOT NULL DEFAULT `safe`), `fraud_score` (INT NOT NULL DEFAULT 0), `fraud_flags` (TEXT NULL): Fraud risk assessment.
- `billing_first_name`, `billing_last_name`, `billing_phone`, `billing_address_1`, `billing_address_2`, `billing_city`, `billing_state`, `billing_postcode`, `billing_country`: Billing details.
- `shipping_first_name`, `shipping_last_name`, `shipping_address_1`, `shipping_city`, `shipping_country`: Physical delivery destination.
- `ip_address` (VARCHAR(45) NULL): IPv4 or IPv6 address for fraud auditing.
- `created_at`, `updated_at`, `deleted_at` (DATETIME NULL): Lifecycle timestamps.

#### 3. `wp_omnify_customer_access` (Digital Entitlements)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Access entitlement ID.
- `customer_id` (BIGINT UNSIGNED NOT NULL): Customer identifier.
- `product_id` (BIGINT UNSIGNED NOT NULL): Purchased digital product.
- `status` (VARCHAR(32) NOT NULL DEFAULT `active`): Entitlement state (`active` / revoked on refund).
- `expires_at` (DATETIME NULL): Timestamp when access terminates (`NULL` = lifetime).
- `granted_by` (BIGINT UNSIGNED NULL): User ID that granted the entitlement (NULL for automated grants).
- `created_at`, `updated_at` (DATETIME NOT NULL): Audit timestamps.
- `UNIQUE KEY customer_product (customer_id, product_id)`: One entitlement per customer/product pair.

---

## 4. Soft Deletions & Cache Invalidation System

### Soft Deletes (`deleted_at`)
Products, Orders, Customers, Coupons, Reviews, and Abandoned Carts utilize a nullable `deleted_at DATETIME` column:
- **Trash Action**: Sets `deleted_at = current_time('mysql', 1)`.
- **Restore Action**: Sets `deleted_at = null`.
- **Storefront & Public APIs**: Automatically exclude records where `deleted_at IS NOT NULL`.
- **Admin Trash Filter**: Admin listing queries pass `'status' => 'trash'` to display soft-deleted items.
- **Permanent Purge**: `Product_Repository::delete($id)` cascades removal across `product_categories`, `product_tags`, `product_brands`, `product_files`, `product_variations`, `wishlists`, `reviews`, `customer_access`, and `metadata`.

### Cache Invalidation Groups
Omnify caches queries using the WordPress Object Cache API (`wp_cache_*`) under the `'omnifywp-ecommerce'` group:
```php
// Product caches:
wp_cache_delete('omnify_prod_' . $product_id, 'omnifywp-ecommerce');
wp_cache_delete('omnify_prod_slug_' . $slug . '_' . $version, 'omnifywp-ecommerce');
wp_cache_delete('omnify_prod_sku_' . $sku . '_' . $version, 'omnifywp-ecommerce');
wp_cache_delete('omnify_recent_sales_' . $product_id . '_' . $hours, 'omnifywp-ecommerce');

// Customer caches:
wp_cache_delete('omnify_cust_ids_' . $user_id . '_' . md5($email), 'omnifywp-ecommerce');
```

---

## 5. Repositories SDK & Query APIs

### Product Repository
`Omnify\eCommerce\Repositories\Omnify_Product_Repository`

```php
$products = omnify()->container()->get(Omnify_Product_Repository::class);

// Filter products with faceted query criteria
$list = $products->all([
    'status'       => 'published',
    'category'     => 'Digital Goods',
    'type'         => 'download',
    'price_min'    => 10.00,
    'price_max'    => 100.00,
    'stock_status' => 'instock',
    'sort'         => 'rating_desc', // 'price_asc', 'price_desc', 'rating_desc', 'popular_desc'
    'per_page'     => 20,
    'offset'       => 0,
]);

// Atomic stock reduction on checkout
$success = $products->reduce_stock(
    product_id: 15,
    variation_id: null,
    quantity: 1
);

// Create product programmatically
$id = $products->create([
    'name'         => 'TypeScript Design Patterns',
    'type'         => 'download',
    'status'       => 'published',
    'price'        => 39.99,
    'sale_price'   => 29.99,
    'manage_stock' => 1,
    'stock_qty'    => 250,
    'categories'   => ['Programming', 'Books'],
]);
```

### Order Repository
`Omnify\eCommerce\Repositories\Omnify_Order_Repository`

```php
$orders = omnify()->container()->get(Omnify_Order_Repository::class);

// Retrieve orders (Batch hydrated without N+1 queries)
$recent_orders = $orders->all([
    'status'    => 'processing',
    'date_from' => '2026-01-01',
    'per_page'  => 50,
]);

// Create complete order
$order_id = $orders->create([
    'customer_id'     => 12,
    'status'          => 'processing',
    'currency'        => 'USD',
    'subtotal'        => 59.98,
    'tax'             => 4.80,
    'shipping_total'  => 0.00,
    'total'           => 64.78,
    'payment_method'  => 'stripe',
    'transaction_id'  => 'pi_3L714q2eZvKYlo2C0pY2e',
    'billing_first_name' => 'Alex',
    'billing_last_name'  => 'Smith',
    'items'           => [
        [
            'product_id'   => 15,
            'product_name' => 'TypeScript Design Patterns',
            'price'        => 29.99,
            'quantity'     => 2,
            'tax'          => 4.80,
        ]
    ]
]);

// Add order audit note
$orders->add_note($order_id, 'Payment authorized via Stripe Payment Intent', customer_visible: false);

// Process full or partial refund
// If refunded_amount >= total, status transitions to 'refunded' and files are revoked
// If 0 < refunded_amount < total, status transitions to 'partially_refunded' and active files remain accessible
$orders->record_refund($order_id, amount: 15.00, reason: 'Customer requested partial discount adjustment');
```

### Customer Repository
`Omnify\eCommerce\Repositories\Omnify_Customer_Repository`

```php
$customers = omnify()->container()->get(Omnify_Customer_Repository::class);

// Paginated query with search, sort, and cache optimization
$customer_list = $customers->all([
    'search'   => 'alex',
    'status'   => 'active',
    'sort'     => 'total_spent',
    'order'    => 'DESC',
    'per_page' => 25,
    'offset'   => 0,
]);

// Query total customer count for pagination calculation
$total_count = $customers->count([
    'search' => 'alex',
    'status' => 'active',
]);

// Retrieve customer lifetime metrics
$stats = $customers->get_stats($customer_id);
// Returns: ['total_orders' => 14, 'total_spent' => 842.50, 'average_order_value' => 60.18, 'last_order_date' => '2026-10-14 10:22:15']
```

### Coupon Repository
`Omnify\eCommerce\Repositories\Omnify_Coupon_Repository`

```php
$coupons = omnify()->container()->get(Omnify_Coupon_Repository::class);

// Validate coupon with timezone-aware expiry check (using wp_timezone())
$result = $coupons->validate(
    code: 'FALL2026',
    subtotal: 120.00,
    customer_email: 'alex@example.com',
    product_ids: [15, 24]
);

if ($result['valid']) {
    $discount_amount = $result['discount']; // e.g. 24.00
}
```

### Abandoned Cart Repository
`Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository`

```php
$abandoned = omnify()->container()->get(Omnify_Abandoned_Cart_Repository::class);

// Multi-Item abandoned checkout capture
$cart_record = $abandoned->upsert([
    'email'          => 'shopper@example.com',
    'cart_token'     => $token,
    'user_id'        => get_current_user_id() ?: null,
    'cart_contents'  => wp_json_encode([
        ['product_id' => 15, 'variation_id' => 0, 'quantity' => 2, 'price' => 29.99, 'name' => 'TypeScript Design Patterns'],
        ['product_id' => 24, 'variation_id' => 102, 'quantity' => 1, 'price' => 45.00, 'name' => 'Silk Bomber Jacket - Large']
    ]),
    'subtotal'       => 104.98,
    'currency'       => 'USD',
    'ip_address'     => '192.168.1.100',
    'recovery_token' => wp_generate_password(32, false),
]);

// Find carts qualified for automated recovery notifications
$pending_reminders = $abandoned->due_for_recovery(
    delay_minutes: 60,
    max_reminders: 3,
    limit: 50
);

// Mark cart restored/recovered when checkout completes
$abandoned->mark_recovered(token: $cart_token, order_id: $order_id);
```

### Product File Repository (N+1 Query Batching)
`Omnify\eCommerce\Repositories\Product_File_Repository`

```php
$file_repo = omnify()->container()->get(Product_File_Repository::class);

// High-performance batch hydration: retrieves all downloadable files for multiple products in a single SQL query
$product_ids = [12, 15, 24, 88, 102];
$files_by_product = $file_repo->all_for_products($product_ids);

// Structure returned:
// [
//   15 => [ [ 'id' => 101, 'name' => 'patterns-v1.4.2.pdf', 'file_size' => 4194304, ... ] ],
//   24 => [ [ 'id' => 205, 'name' => 'source-code.zip',     'file_size' => 12582912, ... ] ],
// ]
```

---

## 6. Storefront & Checkout Internals

### Shortcodes Directory & Parameters Reference

| Shortcode | Supported Parameters | Default | Description |
| :--- | :--- | :---: | :--- |
| `[omnify_storefront]` | `columns` (`2`, `3`, `4`, `list`)<br>`per_page` (int, 1-100)<br>`category` (slug)<br>`tag` (slug)<br>`brand` (slug)<br>`orderby` (`price`, `date`, `rating`, `title`)<br>`order` (`ASC`, `DESC`) | `columns="4"`<br>`per_page="12"`<br>`orderby="date"`<br>`order="DESC"` | Renders the complete storefront catalog with faceted sidebar, flash ticker, quick filter pills, layout switcher, and dynamic product cards. |
| `[omnify_cart]` | `show_shipping` (`true`, `false`) | `true` | Interactive shopping cart table with real-time quantity steppers, shipping fee calculator, coupon validation, and dynamic free shipping progress meter. |
| `[omnify_checkout]` | `layout` (`single`, `steps`) | `single` | Single-page or multi-step checkout engine featuring live tax estimation, address autocomplete, guest checkout, and integrated payment gateways. |
| `[omnify_customer_portal]` | `tab` (`orders`, `downloads`, `addresses`, `security`) | `orders` | Self-service customer dashboard with KPI analytics cards, order details, download access tokens, and saved address books. |
| `[omnify_customer_downloads]`| None | — | Focused customer digital locker rendering owned files, license keys, and HMAC expiring download links. |
| `[omnify_mini_cart]` | None | — | Floating slide-out AJAX mini-cart drawer or dropdown widget with live line item quantity adjustments, item removal, and subtotal calculation. |
| `[omnify_currency_switcher]` | `label` (string) | `"Select Currency"` | Customer-facing multi-currency selector dropdown with real-time conversion rates and 1-year cookie persistence. |
| `[omnify_order_tracking]` | None | — | Public guest order lookup form requiring only Order ID and Billing Email without account login. |
| `[omnify_download_page]` | None | — | Dedicated template handler for secure, chunked file downloads. |

### Native Gutenberg Blocks
Every shortcode is also registered as a first-party Gutenberg block (registered in `Omnify_Storefront_Controller::register_blocks()`):

| Block | Replaces Shortcode | Description |
| :--- | :--- | :--- |
| `omnify/product-grid` | `[omnify_storefront]` | Responsive catalog grid with configurable columns, category filtering, and sorting. |
| `omnify/cart` | `[omnify_cart]` | Standalone cart block with live quantity adjustments and shipping calculation. |
| `omnify/mini-cart` | `[omnify_mini_cart]` | Header/navigation mini-cart block with item counter pill. |
| `omnify/checkout` | `[omnify_checkout]` | 1-Page checkout block with integrated payment gateway accordion. |
| `omnify/customer-portal` | `[omnify_customer_portal]` | Customer account hub block (orders, downloads, addresses, profile). |
| `omnify/order-tracking` | `[omnify_order_tracking]` | Guest order tracking and invoice lookup block. |
| `omnify/product-card` | — | Individual product card showcase block. |
| `omnify/currency-switcher` | `[omnify_currency_switcher]` | Header/sidebar multi-currency dropdown block. |

---

### Client-Side State Engine (`window.omnifyStorefront`)
Injected onto all frontend pages via `wp_localize_script('omnify-storefront-script')`:
```javascript
window.omnifyStorefront = {
  restUrl: "https://example.com/wp-json/omnify/v1", // Normalized without trailing slash
  nonce: "4a28c9b1e5",
  currency: "USD",
  currencySymbol: "$",
  currencyPosition: "before",                       // "before" ($10) or "after" (10$)
  taxRate: 0.08,
  taxRules: [],
  pricesIncludeTax: false,
  taxShipping: false,
  checkoutUrl: "https://example.com/checkout/",
  cartUrl: "https://example.com/cart/",
  isLoggedIn: true,
  wishlistProductIds: [15, 22],
  cart: {
    freeShippingThreshold: 200                      // Numeric dollar threshold for free shipping
  },
  stripe: {
    enabled: true,
    mode: "live",
    publishableKey: "pk_live_...",
    applePayEnabled: true,
    googlePayEnabled: true
  },
  paypal: {
    enabled: true,
    mode: "live"
  }
};
```

---

### Flash Sale Ticker & Live Countdown Architecture (`initFlashSaleCountdown`)
The promotional flash sale ticker (`#omnify-flash-ticker`) creates urgency using real-time countdown units:
* **DOM Targets**: `#omnify-ticker-hours`, `#omnify-ticker-mins`, `#omnify-ticker-secs`.
* **State Persistence**: The countdown target timestamp is cached in browser `sessionStorage` under the key `omnify_flash_sale_end`.
* **Timer Lifecycle**:
  ```javascript
  // Pseudocode implementation overview
  function initFlashSaleCountdown() {
    let target = sessionStorage.getItem('omnify_flash_sale_end');
    if (!target) {
      target = Date.now() + (7 * 3600 + 42 * 60 + 18) * 1000; // 7h 42m 18s default window
      sessionStorage.setItem('omnify_flash_sale_end', target);
    }
    setInterval(() => {
      const remaining = Math.max(0, target - Date.now());
      const hours = Math.floor(remaining / 3600000);
      const mins  = Math.floor((remaining % 3600000) / 60000);
      const secs  = Math.floor((remaining % 60000) / 1000);
      hoursEl.textContent = String(hours).padStart(2, '0');
      minsEl.textContent  = String(mins).padStart(2, '0');
      secsEl.textContent  = String(secs).padStart(2, '0');
    }, 1000);
  }
  ```
* **Status Dot**: Features an animated green glowing pulse dot (`.ticker-pulse`) indicating active live sale pricing.

---

### 1-Click Coupon Code Copy API (`initCouponPillCopy`)
Allows shoppers to copy promotional discount codes directly into their clipboard with instant visual feedback:
* **Trigger Element**: `.omnify-coupon-code-pill[data-code="CODE"]`.
* **Clipboard Execution**:
  * Calls `navigator.clipboard.writeText(code)` with `document.execCommand('copy')` fallback for legacy browsers.
  * Adds `.is-copied` class to the pill and replaces the label with `"Copied!"` for 2,000ms.
* **Auto-Application**: On checkout and cart pages, clicking the pill automatically populates and submits the coupon form input.

---

### Interactive Quick Filter Pills Engine (`initQuickFilterPills`)
The horizontal pill strip (`.omnify-quick-filter-strip`) provides instant 1-click client-side catalog filtering:
* **Pill Data Contract**:
  * `data-filter-type="all"`: Shows all catalog cards.
  * `data-filter-type="sale"`: Filters by `data-onsale="1"` or presence of `.current-price.sale`.
  * `data-filter-type="hot"`: Filters by `data-is-hot="1"` or bestseller tag.
  * `data-filter-type="new"`: Filters by `data-is-new="1"` or new tag.
  * `data-filter-type="category"` with `data-filter-value="slug"`: Matches against space-separated slugs in `data-categories`.
* **Dynamic Results Recalculation**:
  Recalculates visible cards count and updates `#omnify-storefront-results`:
  ```javascript
  const resCount = document.getElementById('omnify-storefront-results');
  if (resCount) {
    resCount.textContent = `${matchCount} products`;
  }
  ```
* **Sidebar Synchronization**: Automatically updates `#omnify-category-filter` select value and checks/unchecks matching `.omnify-sidebar-category-check` checkboxes without page reloads.

---

### Product Card Hover Tools & Action Triggers (`.omnify-card-hover-tools`)
Frosted-glass floating action toolbar positioned at the top-right of product card thumbnails:
* **Wishlist Heart Button** (`.omnify-wishlist-button`):
  * Reads `data-product-id` from the button or parent `.omnify-product-card`.
  * Toggles `.is-wishlisted` class and persists across sessions via REST API (`POST /wp-json/omnify/v1/wishlist/toggle`) for authenticated users, and localStorage for guest sessions.
  * Dispatches custom event: `document.dispatchEvent(new CustomEvent('omnify:wishlist:toggled', { detail: { productId, added } }))`.
* **Quick View Eye Button** (`.omnify-quick-view-button`):
  * Invokes `openQuickView(button)` to fetch product data from card attributes or REST API.
  * Injects product images, variations, price, and instant checkout form into `#omnify-quick-view-modal`.
  * Dispatches custom event: `document.dispatchEvent(new CustomEvent('omnify:quickview:opened', { detail: { productId } }))`.
* **Compare Button** (`.omnify-compare-button`):
  * Reads `data-product-id`, `data-name`, `data-display-price`, `data-image`, and `data-url`.
  * Appends item to comparison tray (`#omnify-compare-drawer`) and triggers side-by-side spec modal.

---

### Interactive Color Swatches Engine (`initCardMockSwatches`)
* **DOM Target**: `.omnify-card-mock-swatches` with child `.omnify-swatch-dot`.
* **Event Handling**: Stops click propagation (`e.stopPropagation()`) to prevent accidental navigation to the single product page when clicking a swatch.
* **State**: Clears `.is-active` from siblings and applies to the selected swatch dot, displaying a distinct focus ring.

---

### Recently Viewed Products Engine & Cookie Storage (`initStorefrontRecentlyViewed`)
* **Tracking Pipeline**:
  When a user views a single product page (`/product/{slug}`), `storefront.js` captures `productId` and updates the `omnify_recently_viewed` cookie:
  ```javascript
  // JSON array stored in cookie (e.g. [14, 28, 5, 92])
  Cookies.set('omnify_recently_viewed', JSON.stringify(ids), { expires: 30, path: '/' });
  ```
* **Catalog Showcase Injection**:
  On catalog pages, `#omnify-recently-viewed-grid` inspects the cookie:
  * If the cookie is empty or has fewer than 1 item, the container sets `style.display = 'none'`.
  * If items exist, it unhides the container and renders product cards matching the primary catalog 4-column design with ratings, badges, and quick-add buttons.

---

### Free Shipping Progress Bar & Dynamic Calculation
* **DOM Components**:
  * Container: `#omnify-shipping-progress-wrapper`
  * Fill Bar: `#omnify-shipping-progress-fill`
  * Message: `#omnify-shipping-progress-text`
* **Calculation Engine**:
  ```javascript
  const threshold = window.omnifyStorefront?.cart?.freeShippingThreshold || 200;
  const subtotal = selectedItems.reduce((acc, item) => acc + (parseFloat(item.price) * item.quantity), 0);
  const diff = threshold - subtotal;
  if (diff > 0) {
    const pct = Math.min(100, Math.max(0, (subtotal / threshold) * 100));
    progressFill.style.width = pct + '%';
    progressText.innerHTML = `Spend <strong>${formatStoreMoney(diff)}</strong> more to reach <strong>FREE SHIPPING!</strong>`;
  } else {
    progressFill.style.width = '100%';
    progressText.innerHTML = `You have reached <strong>FREE SHIPPING!</strong>`;
  }
  ```

---

### Product Card Data Contract & Attribute Reference
Every `.omnify-product-card` rendered in `templates/storefront/grid.php` exposes the following standardized data contract:

| HTML Attribute | Value Type | Description |
| :--- | :--- | :--- |
| `data-product-id` | `integer` | Primary key identifier in `wp_omnify_products`. |
| `data-product-name` | `string` | Escaped product title. |
| `data-price` | `float` | Active selling price (sale price if active, regular price otherwise). |
| `data-onsale` | `"1"` \| `"0"` | Indicates whether the product has an active discount. |
| `data-stock-status` | `"instock"` \| `"outofstock"` | Inventory status string. |
| `data-url` | `URL` | Canonical permalink to product details page. |
| `data-type` | `"download"` \| `"physical"` \| `"bundle"` \| `"variable"` | Product type specification. |
| `data-search` | `string` | Pre-indexed lowercase search string (title + excerpt + tags + categories). |
| `data-categories` | `string` | Space-delimited category taxonomy slugs. |
| `data-brands` | `string` | Space-delimited brand slugs. |
| `data-attributes` | `JSON string` | Serialized array of product attributes (sizes, colors, licenses). |
| `data-variations` | `JSON string` | Serialized array of variation objects (SKU, price, stock, attributes). |
| `data-is-hot` | `"1"` \| `"0"` | Bestseller flag based on product tags. |
| `data-is-new` | `"1"` \| `"0"` | New release flag based on product tags. |

---

### Third-Party Theme Compatibility & Specificity Shielding
To prevent popular WordPress themes (such as Astra, Kadence, GeneratePress, and block themes) from overriding card dimensions, button padding, or image aspect ratios, OmnifyWP uses **Authoritative Specificity Shielding** in `storefront.css`:

```css
/* Specificity Shield: Prevents theme container overrides */
body .omnify-storefront-wrapper .omnify-product-card .omnify-product-card__image-container,
.omnify-storefront-wrapper .omnify-product-card .omnify-product-card__image-container {
  width: 100% !important;
  max-width: 100% !important;
  aspect-ratio: auto !important;
  display: block !important;
}

body .omnify-storefront-wrapper .omnify-product-card .omnify-product-card__image,
.omnify-storefront-wrapper .omnify-product-card .omnify-product-card__image {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  object-position: center !important;
}

/* Dense 4-column description suppression */
body .omnify-storefront-wrapper .omnify-storefront-grid--cols-4 .omnify-product-card .omnify-product-card__desc,
body .omnify-storefront-wrapper .omnify-storefront-grid.omnify-cols-4 .omnify-product-card .omnify-product-card__desc {
  display: none !important;
}
```

---

## 7. Payment Gateways & Webhooks Engine

### Supported Gateways & Secure-by-Default Architecture
OmnifyWP features a **Secure-by-Default Architecture**: all gateways default to disabled (`0`) upon initial database seeding, preventing accidental or unverified checkouts.

1. **Stripe**: Payment Intents (custom elements, Apple Pay, Google Pay) + Hosted Checkout Sessions.
2. **PayPal Commerce**: REST API v2 Orders & Captures with smart buttons.
3. **Razorpay**: Standard Checkout flow supporting UPI, Indian debit/credit cards, and netbanking.
4. **Alipay & WeChat Pay**: Chinese digital wallet checkout and QR codes.
5. **SSLCommerz**: South Asian gateway supporting cards and mobile banking (bKash, Nagad).
6. **Paystack**: Top African payment gateway supporting cards, bank transfers, mobile money, and USSD.
7. **Tap Payments**: Middle East & GCC gateway supporting mada, KNET, Benefit, NAPS, and cards.
8. **Mollie**: European Union checkout covering iDEAL, Bancontact, SEPA, and cards.
9. **Khalti & eSewa**: Leading digital wallets and mobile banking in Nepal.
10. **Paddle Billing**: Merchant-of-Record infrastructure with automated tax calculations and webhooks.
11. **Offline / Manual**: Cash on Delivery (COD), Direct Bank Transfer (BACS), Cheque Payments.

### Complete Webhook Endpoints & Verification Engine

| Gateway | Webhook Route | Verification Signature / Handshake | Active Events Handled |
| :--- | :--- | :--- | :--- |
| **Stripe** | `POST /wp-json/omnify/v1/stripe/webhook` | `HTTP_STRIPE_SIGNATURE` HMAC-SHA256 against `stripe_webhook_secret` | `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`, `charge.dispute.created` |
| **PayPal** | `POST /wp-json/omnify/v1/paypal/webhook` | Handshake via PayPal API `verify-webhook-signature` using `paypal_webhook_id` | `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.REFUNDED`, `CUSTOMER.DISPUTE.CREATED` |
| **Razorpay** | `POST /wp-json/omnify/v1/razorpay/webhook` | `HTTP_X_RAZORPAY_SIGNATURE` HMAC-SHA256 against `razorpay_webhook_secret` | `payment.captured`, `payment.failed`, `refund.processed` |
| **SSLCommerz** | `POST /wp-json/omnify/v1/sslcommerz/ipn` | Server-to-server transaction validation session inquiry with Store Password | Order validation, payment capture |
| **Paystack** | `POST /wp-json/omnify/v1/paystack/webhook` | `HTTP_X_PAYSTACK_SIGNATURE` HMAC-SHA512 against Secret Key | `charge.success`, `refund.processed` |
| **Tap Payments**| `POST /wp-json/omnify/v1/tap/webhook` | Header signature verification against Secret Key | `CHARGE.CAPTURED`, `CHARGE.FAILED` |
| **Mollie** | `POST /wp-json/omnify/v1/mollie/webhook` | Server-to-server API check fetching payment object by ID | `paid`, `canceled`, `expired`, `failed` |
| **Paddle** | `POST /wp-json/omnify/v1/paddle/webhook` | `Paddle-Signature` header verification against `paddle_webhook_secret` | `transaction.paid`, `transaction.billed`, `adjustment.updated` |
| **Alipay** | `POST /wp-json/omnify/v1/alipay/webhook` | RSA2 / MD5 signature verification | Payment notify trade success |
| **WeChat Pay**| `POST /wp-json/omnify/v1/wechat/webhook` | AES-256-GCM decrypt using APIv3 key | Resource trade success |

#### Inbound Refund & Dispute Synchronization Logic
* **Full Refunds**: When a gateway webhook reports a total refund (`charge.refunded` with `amount_refunded >= total` or `PAYMENT.CAPTURE.REFUNDED`), the order status transitions to `refunded`, order audit notes record the reversal, and digital access entitlements in `wp_omnify_customer_access` are instantly revoked.
* **Partial Refunds**: When an external partial refund is issued through the gateway dashboard (`amount_refunded < total`), the order status transitions to `partially_refunded`. The refunded amount is accrued in `refunded_amount`, audit notes log the gateway reference, and digital file access remains active for non-refunded items.
* **Disputes & Chargebacks**: When `charge.dispute.created` or `CUSTOMER.DISPUTE.CREATED` arrives, the order transitions to `on-hold` with dispute metadata logged, temporarily freezing digital download access until evidence submission resolves.

### Outbound Webhooks Engine & Signature Verification

OmnifyWP eCommerce features an outbound event publishing pipeline (`Omnify\eCommerce\Support\Webhook_Service`) that dispatches asynchronous HTTP POST notifications when state mutations occur within the store.

#### Supported Outbound Events
- `order.created`: Triggered immediately when a new checkout order is inserted.
- `order.paid`: Dispatched when an order payment is successfully authorized or captured.
- `order.refunded`: Fired when a full or partial refund is processed.
- `order.status_changed`: Emitted whenever an order status transitions (e.g. `pending` to `processing`).
- `product.created`: Triggered when a new product is published.
- `product.updated`: Dispatched when product attributes, prices, or inventory levels are updated.
- `product.deleted`: Fired when a product record is permanently deleted.
- `customer.created`: Emitted when a new customer profile is registered.

#### Outbound Dispatch Payload Contract
```json
{
  "event": "order.paid",
  "timestamp": "2026-10-15 12:45:00",
  "site_url": "https://example.com/",
  "data": {
    "order_id": 1042,
    "order_number": "OMN-1042",
    "status": "processing",
    "payment_status": "paid",
    "total": 129.00,
    "currency": "USD",
    "customer": {
      "id": 88,
      "email": "customer@example.com",
      "first_name": "Elena",
      "last_name": "Rostova"
    },
    "items": [
      {
        "product_id": 24,
        "name": "Minimalist Silk Bomber",
        "quantity": 1,
        "price": 129.00
      }
    ]
  }
}
```

#### HTTP Headers & Signature Verification
Every outbound HTTP POST request is signed using HMAC-SHA256 with the secret key configured for the respective webhook endpoint:

```http
POST /your-receiver-endpoint HTTP/1.1
Host: api.yourapp.com
Content-Type: application/json; charset=utf-8
X-Omnify-Event: order.paid
X-Omnify-Signature: 7f83b1657ff1fc53b92dc18148a1d65dfc2d4b1fa3d677284addd200126d9069
User-Agent: OmnifyWP-Webhooks/1.4.3
```

#### Node.js / Express Signature Verification Example
```javascript
const crypto = require('crypto');

function verifyOmnifyWebhook(req, secret) {
  const signature = req.headers['x-omnify-signature'];
  const event = req.headers['x-omnify-event'];
  const rawBody = req.rawBody; // Buffer or raw utf-8 string before JSON.parse

  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(rawBody)
    .digest('hex');

  return crypto.timingSafeEqual(
    Buffer.from(signature, 'utf8'),
    Buffer.from(expectedSignature, 'utf8')
  );
}
```

#### PHP Signature Verification Example
```php
$secret = 'whsec_your_configured_secret';
$raw_payload = file_get_contents('php://input');
$received_signature = $_SERVER['HTTP_X_OMNIFY_SIGNATURE'] ?? '';

$computed_signature = hash_hmac('sha256', $raw_payload, $secret);

if (! hash_equals($computed_signature, $received_signature)) {
    http_response_code(401);
    exit('Unauthorized: Signature mismatch');
}

// Process webhook payload safely
$data = json_decode($raw_payload, true);
```

### Live Diagnostics & Connectivity Testing APIs

OmnifyWP provides administrative AJAX endpoints for real-time gateway credential validation and network reachability testing:

| Action Hook | Capability | Description |
| :--- | :--- | :--- |
| `wp_ajax_omnify_test_stripe_connection` | `manage_omnify` | Queries Stripe API (`/v1/balance`) using configured secret key to verify live credentials, account balance, and operating currency. |
| `wp_ajax_omnify_test_paypal_connection` | `manage_omnify` | Authenticates against PayPal OAuth2 endpoint (`/v1/oauth2/token`) using Client ID & Secret to ensure valid token grant. |
| `wp_ajax_omnify_test_connection` | `manage_omnify` | Performs an authenticated HTTPS probe to arbitrary target URLs, measuring network latency, HTTP status codes, and SSL certificate validity. |
| `wp_ajax_omnify_test_webhook` | `manage_omnify` | Dispatches an asynchronous ping event (`event: ping`) to a specified webhook endpoint ID and records HTTP latency and response code in `wp_omnify_webhook_delivery_logs`. |

### Building Custom Payment Gateways

You can register custom payment gateways by implementing the gateway interface and hooking into `omnify_payment_gateways`:

```php
namespace MyPlugin\Gateways;

class Custom_Cryptocurrency_Gateway {
    public function get_id(): string {
        return 'crypto_pay';
    }

    public function get_title(): string {
        return __('Cryptocurrency (USDC / Bitcoin)', 'my-plugin');
    }

    public function is_enabled(): bool {
        $settings = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Settings_Repository::class);
        return (bool) $settings->get('custom_crypto_enabled', false);
    }

    public function process_payment(int $order_id, array $posted_data): array {
        $orders = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Order_Repository::class);
        $order  = $orders->find($order_id);

        // Generate hosted crypto payment invoice URL
        $invoice_url = "https://crypto-gateway.com/pay/{$order['order_number']}";

        return [
            'result'   => 'success',
            'redirect' => $invoice_url,
            'order_id' => $order_id,
        ];
    }

    public function process_refund(int $order_id, float $amount, string $reason = ''): bool {
        // Execute API refund call...
        return true;
    }
}

// Register gateway into Omnify
add_filter('omnify_payment_gateways', function(array $gateways) {
    $gateways['crypto_pay'] = new Custom_Cryptocurrency_Gateway();
    return $gateways;
});
```

### Automatic File Revocation on Refund
When a refund is processed via `Omnify_Payment_Gateway_Service`:
1. Communicates with gateway API to execute the refund.
2. Updates order record to `refunded` (or increments `refunded_amount`).
3. Automatically deletes corresponding customer access entitlements from `wp_omnify_customer_access`.
4. Fires action hook `omnify_customer_access_revoked($customer_id, $product_id)`.
5. Logs the event into `wp_omnify_admin_activities`.

---

## 8. Cryptographic Digital Locker Engine

### HMAC-SHA256 Tokenization
`Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service`
```php
$payload   = $file_id . '|' . $expires_timestamp . '|' . (int) $customer_id;
$signature = hash_hmac('sha256', $payload, wp_salt('auth'));

// Resulting Expiring Link:
// https://example.com/?omnify_download=1&file_id=15&expires=1728000000&customer_id=42&signature=...
```

### Protected 8KB Chunked File Streaming
`Omnify\eCommerce\Downloads\Omnify_Download_Controller::stream`
- **Path Traversal Shield**: Verifies `realpath($file_path)` is strictly contained within the WordPress uploads directory via `wp_normalize_path()`.
- **Output Buffer Flush**: Runs `while (ob_get_level() > 0) ob_end_clean();` to prevent memory exhaustion on large file deliveries.
### Server Hardening Rules (Apache & Nginx)
To guarantee that customers cannot bypass Omnify's PHP token validator and download protected assets directly via static URLs, place your files inside `wp-content/uploads/omnify-protected/` and apply the following server rules:

#### Apache (`.htaccess` in `wp-content/uploads/omnify-protected/.htaccess`)
```apache
# Deny all direct HTTP requests to protected files
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
```

#### Nginx (`nginx.conf`)
```nginx
# Block direct static file downloads from the protected folder
location ^~ /wp-content/uploads/omnify-protected/ {
    internal; # Only accessible via PHP X-Accel-Redirect or chunked stream
    return 403;
}
```

---

## 9. REST API Complete Reference

Namespace: `/wp-json/omnify/v1`

### Authentication Methods
- **WordPress Nonce**: Send `X-WP-Nonce` header.
- **Basic Auth**: Send `Authorization: Basic base64(consumer_key:consumer_secret)`.
- **Custom Headers**: Send `X-Consumer-Key` and `X-Consumer-Secret`.
- **Query Parameters**: Append `?consumer_key=...&consumer_secret=...`.

### Storefront Endpoints
| Route | Method | Access | Description |
| :--- | :---: | :---: | :--- |
| `/status` | `GET` | Public | Returns plugin status and version. |
| `/products/public` | `GET` | Public | List and search published products. |
| `/products/public/slug/{slug}` | `GET` | Public | Get product details by URL slug. |
| `/products/public/sku/{sku}` | `GET` | Public | Get product details by SKU. |
| `/cart` | `GET` \| `POST` \| `DELETE` | Public | Retrieve, persist, or clear customer cart. |
| `/checkout` | `POST` | Public | Submit checkout order and process payment. |
| `/checkout/validate-coupon` | `POST` | Public | Validate promotional coupon. |
| `/checkout/abandoned-cart` | `POST` | Public | Save partial checkout inputs for recovery. |
| `/checkout/payment-methods` | `GET` | Public | Retrieve active checkout payment gateways. |
| `/checkout/validate-vat` | `POST` | Public | Validate a VAT / tax registration number. |
| `/checkout/check-email` | `POST` | Public | Check whether an email already has an account. |
| `/checkout/abandoned-cart/unsubscribe` | `POST` | Public | Opt a shopper out of recovery emails. |
| `/products/subscribe-stock` | `POST` | Public | Join the back-in-stock waitlist for a product/variation. |
| `/wishlist` | `GET` | Logged In | Retrieve customer favorites. |
| `/wishlist/toggle` | `POST` | Logged In | Toggle item favorite status. |
| `/products/files` | `GET` | Public | List product file metadata across the catalog. |
| `/products/{product_id}/categories` | `GET` | Public | List categories assigned to a product. |
| `/products/{product_id}/variations` | `GET` | Public | List the variation matrix for a product. |
| `/products/{product_id}/reviews` | `GET` \| `POST` | Public | Read approved reviews or submit review. |

### Protected Management Endpoints
Requires `manage_omnify` capability or valid Consumer API key.

| Route | Method | Description |
| :--- | :---: | :--- |
| `/products` | `GET` \| `POST` | Paginated product list or create product. |
| `/products/{id}` | `GET` \| `PUT` \| `DELETE` | Read, update, or permanently delete product. |
| `/products/{id}/trash` | `POST` | Soft-delete product to trash. |
| `/products/{id}/restore` | `POST` | Restore product from trash. |
| `/products/{id}/files` | `GET` \| `POST` | Manage downloadable files for product. |
| `/orders` | `GET` | List orders (status, date, search, customer filters). |
| `/orders/{id}` | `GET` \| `PUT` \| `DELETE` | Read, update, or delete order. |
| `/orders/{id}/mark-paid` | `POST` | Mark order paid and grant customer file access. |
| `/orders/{id}/trash` \| `/restore` | `POST` | Soft-delete or restore an order. |
| `/customers` | `GET` \| `POST` | List or register customers. |
| `/customers/{id}/activity` | `GET` | Retrieve customer CRM audit timeline. |
| `/customers/{id}/notes` | `GET` \| `POST` \| `DELETE` | Manage internal customer notes. |
| `/access` | `POST` | Manually grant customer product access. |
| `/access/revoke` | `POST` | Revoke product access from customer. |
| `/coupons` | `GET` \| `POST` | List or create promotional coupons. |
| `/reviews` | `GET` | List reviews with moderation status filters. |
| `/reviews/{id}` | `PUT` \| `DELETE` | Approve, reject, edit, or delete review. |
| `/abandoned-carts` | `GET` | View captured abandoned checkout sessions. |
| `/stats` | `GET` | Dashboard sales, AOV, refunds, and analytics. |
| `/settings` | `GET` \| `POST` | Read or update store configuration. |
| `/activity-logs` | `GET` | Administrative security audit log feed. |
| `/delivery-zones` | `GET` \| `POST` | List or update shipping delivery zones (`/{index}` for a single zone). |
| `/tax-rules` | `GET` \| `POST` | List or update regional tax rates (`/{index}` for a single rule). |
| `/customers/{id}` | `GET` | Fetch a single customer profile. |
| `/customers/{id}/trash` \| `/restore` | `POST` | Soft-delete or restore a customer. |
| `/customers/{id}/access` | `GET` | List digital entitlements for a customer. |
| `/customers/{id}/wishlist` | `GET` | List wishlist items for a customer. |
| `/access` | `GET` \| `POST` | List or grant customer product access. |
| `/access/bulk` | `POST` | Grant access to multiple customers/products at once. |
| `/access/{id}` | `GET` | Read a single access entitlement. |
| `/access/revoke` | `POST` | Revoke product access from a customer. |
| `/orders/{id}/history` | `GET` | Retrieve the status/activity history for an order. |
| `/downloads` | `GET` | List download audit records. |
| `/downloads/{id}` | `DELETE` | Delete a download log record. |
| `/wishlists` | `GET` | List all wishlist rows (admin). |
| `/products/files/{id}` | `GET` | Read a single product file record. |
| `/products/{product_id}/files/{file_id}/download-url` | `POST` | Mint an expiring HMAC download URL. |
| `/coupons/{id}` | `GET` | Read a single coupon. |
| `/reviews/{id}` | `GET` | Read a single review. |

### Checkout, Gateway & Webhook Endpoints
These endpoints power the client-side gateway flows and inbound webhook verification:

| Route | Method | Access | Description |
| :--- | :---: | :---: | :--- |
| `/checkout` | `POST` | Public | Create an order and initiate payment. |
| `/stripe/checkout-session` \| `/stripe/payment-intent` | `POST` | Public | Create a Stripe Checkout Session or Payment Intent. |
| `/stripe/webhook` | `POST` | Webhook | Verify `Stripe-Signature` and sync payment/refund/dispute events. |
| `/paypal/order` \| `/paypal/capture` | `POST` | Public | Create and capture a PayPal order. |
| `/paypal/webhook` | `POST` | Webhook | Verify PayPal webhook signature. |
| `/razorpay/order` \| `/razorpay/verify` | `POST` | Public | Create/verify a Razorpay payment. |
| `/razorpay/webhook` | `POST` | Webhook | Verify `X-Razorpay-Signature`. |
| `/mollie/order` | `POST` | Public | Create a Mollie payment. |
| `/mollie/webhook` | `POST` | Webhook | Server-side payment status check. |
| `/paddle/order` \| `/paddle/verify` | `POST` | Public | Create/verify a Paddle transaction. |
| `/paddle/webhook` | `POST` | Webhook | Verify `Paddle-Signature`. |
| `/paystack/order` \| `/paystack/verify` | `POST` | Public | Create/verify a Paystack transaction. |
| `/paystack/webhook` | `POST` | Webhook | Verify `X-Paystack-Signature` (HMAC-SHA512). |
| `/tap/order` \| `/tap/verify` | `POST` | Public | Create/verify a Tap charge. |
| `/tap/webhook` | `POST` | Webhook | Verify Tap signature. |
| `/alipay/order` \| `/alipay/verify` \| `/alipay/webhook` | `POST` | Mixed | Alipay trade creation, verification, and RSA2 notify. |
| `/wechat/order` \| `/wechat/verify` \| `/wechat/webhook` | `POST` | Mixed | WeChat Pay unified order, verification, and v3 notify. |
| `/sslcommerz/order` \| `/sslcommerz/verify` \| `/sslcommerz/ipn` | `POST` | Mixed | SSLCommerz session, validation, and IPN. |
| `/khalti/order` \| `/khalti/verify` | `POST` | Public | Khalti ePayment v2 initiation and lookup verification. |
| `/esewa/order` \| `/esewa/verify` | `POST` | Public | eSewa ePay v2 initiation and HMAC verification. |

### Request & Response Payload Schemas

#### Example 1: Submit Checkout (`POST /wp-json/omnify/v1/checkout`)
**Request Headers**:
```http
POST /wp-json/omnify/v1/checkout HTTP/1.1
Host: example.com
Content-Type: application/json
X-WP-Nonce: 4a28c9b1e5
```
**Request Body**:
```json
{
  "billing_first_name": "Jane",
  "billing_last_name": "Doe",
  "billing_email": "jane@example.com",
  "billing_phone": "+15551234567",
  "billing_address_1": "742 Evergreen Terrace",
  "billing_city": "Springfield",
  "billing_state": "IL",
  "billing_postcode": "62701",
  "billing_country": "US",
  "payment_method": "stripe",
  "payment_token": "pm_card_visa",
  "coupon_code": "WELCOME10",
  "items": [
    {
      "product_id": 15,
      "variation_id": null,
      "quantity": 1
    }
  ]
}
```
**Response (200 OK)**:
```json
{
  "success": true,
  "order_id": 1042,
  "order_number": "OMN-1042",
  "status": "processing",
  "total": 26.99,
  "currency": "USD",
  "redirect_url": "https://example.com/checkout/order-received/1042/?token=sec_abc123"
}
```

#### Example 2: Public Products Query (`GET /wp-json/omnify/v1/products/public`)
**cURL Command**:
```bash
curl -X GET "https://example.com/wp-json/omnify/v1/products/public?category=Apparel&per_page=12&sort=price_asc" \
  -H "Accept: application/json"
```

---
## 10. Action & Filter Hooks Directory

OmnifyWP provides a rich, enterprise-grade WordPress hooks system allowing developers to customize product editing workflows, cart calculations, single product architecture, checkout processing, and email dispatches without editing core files.

### Public Developer Helper APIs

The following global helper functions are available anywhere in your themes or custom plugins:

| Function | Parameters | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `omnify_get_product( $product_id )` | `int $product_id` | `array\|null` | Retrieves the full normalized product record from `omnify_products` repository. Filterable via `omnify_get_product`. |
| `omnify_get_order( $order_id )` | `int $order_id` | `array\|null` | Retrieves the complete order record from `omnify_orders` repository. Filterable via `omnify_get_order`. |
| `omnify_format_price( $amount, $currency = '' )` | `float $amount, string $currency` | `string` | Formats numeric price according to store currency formatting settings. Filterable via `omnify_format_price`. |
| `omnify_get_customer_portal_url()` | None | `string` | Dynamically resolves the URL to the Customer Account Portal. Filterable via `omnify_customer_portal_url`. |
| `omnify_get_cart_url()` | None | `string` | Dynamically resolves the active Shopping Cart page URL. Filterable via `omnify_cart_url`. |
| `omnify_get_checkout_url()` | None | `string` | Dynamically resolves the active Checkout page URL. Filterable via `omnify_checkout_url`. |
| `omnify_get_storefront_url()` | None | `string` | Dynamically resolves the primary storefront shop URL. Filterable via `omnify_storefront_url`. |

---

### Action Hooks Reference

#### 1. Product Editor 4-Step Wizard Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_before_product_editor_wizard` | None | Fires before the 4-step wizard container is rendered in the admin. |
| `omnify_after_product_editor_wizard_step_1` | `$product` | Fires immediately after Step 1 (General & Pricing) inputs render. |
| `omnify_after_product_editor_wizard_step_2` | `$product` | Fires immediately after Step 2 (Inventory & Shipping) inputs render. |
| `omnify_after_product_editor_wizard_step_3` | `$product` | Fires immediately after Step 3 (Digital Files & Media) inputs render. |
| `omnify_after_product_editor_wizard_step_4` | `$product` | Fires immediately after Step 4 (Variations & SEO) inputs render. |
| `omnify_after_product_editor_wizard` | None | Fires after all 4 wizard steps finish rendering. |

#### 2. Product Save & Lifecycle Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_product_before_save` | `$product_data, $product_id` | Fires before inserting or updating a product record in `omnify_products`. |
| `omnify_product_saved` | `$product_id, $product_data` | Fires immediately after a product is successfully saved. |
| `omnify_product_before_delete` | `$product_id` | Fires prior to soft-deleting or purging a product. |
| `omnify_product_deleted` | `$product_id` | Fires after permanent product deletion. |
| `omnify_product_trashed` | `$product_id, $product` | Fires when a product is moved to trash. |
| `omnify_product_restored` | `$product_id, $product` | Fires when a product is restored from trash. |

#### 3. Single Product Page (PDP) Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_before_single_product` | `$product` | Fires before the 3-column single product container opens. |
| `omnify_single_product_gallery_start` | `$product` | Fires at the beginning of the gallery thumbnail rail column. |
| `omnify_single_product_gallery_end` | `$product` | Fires at the end of the gallery column. |
| `omnify_single_product_summary_start` | `$product` | Fires before product title, ratings, and price callout. |
| `omnify_single_product_summary_end` | `$product` | Fires after quantity stepper, Add to Cart, and Buy Now buttons. |
| `omnify_single_product_meta_start` | `$product` | Fires at the beginning of the SKU/category metadata section. |
| `omnify_single_product_meta_end` | `$product` | Fires at the end of the SKU/category metadata section. |
| `omnify_after_single_product` | `$product` | Fires after the entire 3-column layout and bottom tab panels. |

#### 4. Shopping Cart Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_before_cart` | None | Fires before the cart wrapper and urgency countdown timer. |
| `omnify_cart_contents` | None | Fires inside the cart table body for custom line items or add-ons. |
| `omnify_after_cart_contents` | None | Fires immediately after the cart table closes. |
| `omnify_cart_totals_before_shipping` | None | Fires inside the order summary card prior to shipping calculation. |
| `omnify_cart_totals_after_shipping` | None | Fires inside the order summary card after shipping calculation. |
| `omnify_after_cart` | None | Fires after the entire cart layout container closes. |

#### 5. Checkout Form Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_before_checkout_form` | None | Fires before the checkout wrapper and Express Pay buttons. |
| `omnify_checkout_customer_details` | None | Fires inside customer contact and address inputs. |
| `omnify_checkout_billing` | None | Fires inside the billing address container. |
| `omnify_checkout_shipping` | None | Fires inside the shipping method selection block. |
| `omnify_checkout_payment_gateways` | None | Fires before payment gateway cards render. |
| `omnify_after_checkout_payment_gateways` | None | Fires after payment gateway selection options. |
| `omnify_after_checkout_form` | None | Fires after the checkout form container. |

#### 6. Admin Table Row Action Hooks
| Action Hook | Parameters | Description |
| :--- | :--- | :--- |
| `omnify_product_row_actions` | `$actions, $item` | Filter or append action links for items in the admin product table. |
| `omnify_order_row_actions` | `$actions, $item` | Filter or append action links for orders in the admin orders table. |
| `omnify_customer_row_actions` | `$actions, $item` | Filter or append action links for customer CRM rows. |
| `omnify_coupon_row_actions` | `$actions, $item` | Filter or append action links for coupons directory rows. |

---

### Filter Hooks Directory & Reference

| Filter Hook | Parameters | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `omnify_product_editor_wizard_steps` | `$steps` | `array` | Add, remove, rename, or reorder steps in the 4-step Product Editor Wizard. |
| `omnify_product_data_before_save` | `$data, $product_id` | `array` | Mutate or sanitize product payload before persisting to database. |
| `omnify_single_product_tabs` | `$tabs, $product` | `array` | Add, modify, or reorder single product information tabs (Description, Specs, Reviews, Shipping). |
| `omnify_cart_item_name` | `$name, $cart_item, $cart_item_key` | `string` | Filter the item title rendered inside shopping cart tables and checkout summaries. |
| `omnify_cart_shipping_free_threshold` | `$threshold` | `float` | Dynamically override the Free Shipping dollar threshold for the cart meter. |
| `omnify_available_payment_gateways` | `$gateways` | `array` | Filter the list of registered payment gateway instances available during checkout. |
| `omnify_format_price` | `$formatted, $amount, $settings` | `string` | Customize currency rendering, multi-currency conversion, or price suffixes (e.g. VAT tags, FREE badges). |
| `omnify_before_checkout_validation` | `$data, $settings, $items` | `array` | Clean, sanitize, or inject custom checkout form fields before validation starts. |
| `omnify_after_checkout_validation` | `$error, $data, $settings, $items` | `null\|WP_Error` | Inject custom business validation logic. Return a `WP_Error` to halt checkout and show error banner. |
| `omnify_get_products_args` | `$args` | `array` | Modify query parameters (filters, sorting, taxonomy) before catalog search queries run. |
| `omnify_get_orders_args` | `$args` | `array` | Modify criteria when querying orders in the repository or customer dashboard. |
| `omnify_signed_download_expires_in` | `$seconds, $file_id, $customer_id` | `int` | Adjust the expiration duration (default: 900s) for cryptographic digital download URLs. |
| `omnify_customer_portal_url` | `$url` | `string` | Override the dynamically resolved Customer Portal URL. |
| `omnify_cart_url` | `$url` | `string` | Override the dynamically resolved Cart page URL. |
| `omnify_checkout_url` | `$url` | `string` | Override the dynamically resolved Checkout page URL. |
| `omnify_storefront_url` | `$url` | `string` | Override the dynamically resolved Storefront Shop page URL. |

---

### 17 Automated Email Notification Triggers

OmnifyWP includes 17 automated email notification triggers that can be subscribed to via action hooks or configured via **Omnify → Settings → Emails**. Each trigger logs its dispatch to `wp_omnify_email_logs` and can be intercepted via the `omnify_email_arguments` filter:

1. `order_receipt` — Order confirmation dispatched to the customer upon successful payment capture (with optional admin copy).
2. `payment_pending` — Sent to the customer when an order is awaiting offline or delayed payment.
3. `abandoned_cart` — Scheduled 1-click recovery email dispatched post-abandonment.
4. `low_stock` — Alert sent to admin when inventory drops below the safety threshold.
5. `out_of_stock` — Emergency alert sent when product stock reaches zero.
6. `refund_requested` — Acknowledgement sent to the customer when a refund request is submitted.
7. `refund_processed` — Confirmation of full or partial refund with the credited amount.
8. `order_status_changed` — Dispatched whenever an order transitions between statuses.
9. `admin_new_order` — Dispatched to store admin recipients upon new order creation.
10. `order_shipped` — Shipment dispatch notice with carrier and tracking number.
11. `order_ready_pickup` — Local-pickup collection notice.
12. `order_cancelled` — Cancellation notice sent to the customer.
13. `customer_note` — Customer-visible support note added to an order.
14. `customer_welcome` — Welcome email sent upon customer registration.
15. `admin_failed_order` — Payment failure alert dispatched to store admins.
16. `customer_back_in_stock` — Back-in-stock alert sent to waitlisted shoppers when stock is replenished.
17. `admin_fraud_alert` — High-risk order security alert dispatched to store admins.

### Extended Hooks Inventory (`omnify_*`)
Beyond the grouped hooks above, the following action and filter hooks are dispatched across the codebase. Every hook is prefixed `omnify_`.

| Hook | Type | Arguments | Purpose |
| :--- | :--- | :--- | :--- |
| `omnify_container_registered` | action | `$container` | Fires after a service is registered in the DI container. |
| `omnify_container_factory` / `omnify_container_instance` | filter | `$factory\|$instance, $id, $container` | Override how a service is built or resolved. |
| `omnify_before_boot` / `omnify_booted` | action | `$plugin, $container` | Fires around the runtime bootstrap. |
| `omnify_admin_init` | action | `$container` | Fires on admin init after registration. |
| `omnify_deactivated` | action | — | Fires on plugin deactivation. |
| `omnify_product_created` / `omnify_product_updated` | action | `$product_id, $data` | Product record created/updated in the repository. |
| `omnify_product_trashed` / `omnify_after_trash_product` | action | `$product_id, $product` | Product moved to trash. |
| `omnify_product_restored` / `omnify_after_restore_product` | action | `$product_id, $product` | Product restored from trash. |
| `omnify_pre_create_product_data` / `omnify_pre_update_product_data` | filter | `$data[, $id]` | Sanitize product payloads before persistence. |
| `omnify_get_product` / `omnify_get_product_by_slug` / `omnify_get_product_by_sku` | filter | `$product, $key` | Filter a hydrated product record. |
| `omnify_get_products_args` / `omnify_get_products_results` | filter | `$args` / `$products, $args` | Filter catalog queries and results. |
| `omnify_product_fallback_thumbnail_url` | filter | `$url, $product` | Provide a default product image. |
| `omnify_product_restocked` | action | `$product_id, $variation_id` | Inventory replenished. |
| `omnify_product_reviews_updated` | action | `$product_id` | Cached rating aggregates recalculated. |
| `omnify_order_created` / `omnify_order_updated` | action | `$order_id, $data` | Order record created / updated. |
| `omnify_order_status_changed` | action | `$order_id, $new_status, $old_status` | Order state transition. |
| `omnify_order_completed` | action | `$order_id[, $gateway, $txn_id]` | Order marked completed / digital access granted. |
| `omnify_order_fulfilled` | action | `$order_id, $carrier, $tracking, $status` | Fulfillment + tracking recorded. |
| `omnify_order_fraud_flagged` | action | `$order_id, $status, $score, $flags` | Fraud scoring flagged an order. |
| `omnify_order_refunded` | action | `$order_id, $amount, $new_refunded, $reason, $restock` | Full or partial refund recorded. |
| `omnify_order_trashed` / `omnify_order_restored` / `omnify_order_deleted` | action | `$order_id` | Order lifecycle (soft-delete/restore/purge). |
| `omnify_customer_note_added` | action | `$order_id, $note, $note_id` | Customer-visible order note added. |
| `omnify_pre_create_order_data` / `omnify_pre_update_order_data` | filter | `$data[, $id]` | Filter order payloads before save. |
| `omnify_get_orders_args` / `omnify_get_orders_results` | filter | `$args` / `$results, $args` | Filter order queries/results. |
| `omnify_customer_created` / `omnify_customer_updated` | action | `$customer_id, $data` | Customer profile created/updated. |
| `omnify_customer_trashed` / `omnify_customer_restored` / `omnify_customer_deleted` | action | `$customer_id` | Customer lifecycle. |
| `omnify_pre_create_customer_data` / `omnify_pre_update_customer_data` | filter | `$data[, $id]` | Filter customer payloads. |
| `omnify_get_customers_args` / `omnify_get_customers_results` / `omnify_get_customer` | filter | `$args` / `$results, $args` / `$customer, $id` | Filter customer queries. |
| `omnify_coupon_created` / `omnify_coupon_updated` | action | `$coupon_id, $data` | Coupon created/updated. |
| `omnify_coupon_trashed` / `omnify_coupon_restored` / `omnify_coupon_deleted` | action | `$coupon_id` | Coupon lifecycle. |
| `omnify_pre_create_coupon_data` / `omnify_pre_update_coupon_data` | filter | `$data[, $id]` | Filter coupon payloads. |
| `omnify_review_created` / `omnify_review_updated` | action | `$review_id, $data` | Review created/updated. |
| `omnify_review_status_changed` | action | `$review_id, $status, $old_status` | Moderation status change. |
| `omnify_review_trashed` / `omnify_review_restored` / `omnify_review_deleted` | action | `$review_id` | Review lifecycle. |
| `omnify_get_reviews_args` / `omnify_get_reviews_results` | filter | `$args` / `$reviews, $args` | Filter review queries. |
| `omnify_customer_access_granted` | action | `$customer_id, $product_id, $expires_at, $id` | Digital entitlement granted. |
| `omnify_customer_access_revoked` | action | `$customer_id, $product_id` | Digital entitlement revoked. |
| `omnify_download_logged` / `omnify_download_deleted` | action | `$log_id, ...` / `$id` | Download audit log events. |
| `omnify_product_file_created` / `omnify_product_file_replaced` / `omnify_product_file_deleted` | action | `$file_id, ...` | Digital file locker events. |
| `omnify_get_product_files_results` / `omnify_get_all_product_files_results` | filter | `$results, $args` | Batch file hydration results. |
| `omnify_abandoned_cart_saved` / `omnify_abandoned_cart_sent` | action | `$id, $data, $created` / `$id` | Cart captured / reminder sent. |
| `omnify_abandoned_cart_recovered` / `omnify_abandoned_cart_recovered_by_email` | action | `$token, $order_id` / `$email, $order_id` | Cart recovered. |
| `omnify_abandoned_cart_unsubscribed` / `omnify_abandoned_cart_trashed` / `omnify_abandoned_cart_restored` / `omnify_abandoned_cart_deleted` | action | `$token` / `$id` | Cart lifecycle. |
| `omnify_abandoned_cart_followup_interval_hours` | filter | `$hours` | Follow-up reminder cadence. |
| `omnify_wishlist_added` / `omnify_wishlist_removed` | action | `$user_id, $product_id` | Wishlist toggled. |
| `omnify_admin_activity_logged` / `omnify_admin_activities_cleared` | action | `$id, ...` / — | Audit trail written / purged. |
| `omnify_settings_saved` / `omnify_pre_update_settings` / `omnify_settings_all` / `omnify_settings_defaults` | action/filter | `$section, $posted` etc. | Settings save pipeline. |
| `omnify_email_arguments` / `omnify_email_sent` | filter/action | `$args` / `$sent, $trigger, ...` | Transactional email dispatch. |
| `omnify_webhook_payload` / `omnify_webhook_request_args` | filter | `$payload, $event, $hook` | Outbound webhook payload/request. |
| `omnify_webhook_blocking_dispatch` / `omnify_webhook_dispatch_timeout` | filter | `$bool/$int, $event, $hook` | Outbound delivery behavior. |
| `omnify_webhook_event_replayed` | action | `$event_id, $gateway, $row` | Inbound webhook manually replayed. |
| `omnify_rest_routes_registered` / `omnify_rest_trash_endpoints_registered` | action | `$namespace, $server` | Fires after REST routes register. |
| `omnify_rest_can_manage` / `omnify_rest_is_logged_in` | filter | `$permission, $request, $server` | REST authorization. |
| `omnify_rest_products_listed` / `omnify_rest_orders_listed` / `omnify_rest_customers_listed` | action | `$count, $params, $request` | REST list endpoints. |
| `omnify_storefront_css_variables` / `omnify_storefront_inline_css` / `omnify_storefront_body_classes` | filter | `$vars/css/classes` | Storefront theming. |
| `omnify_storefront_trust_badges` | filter | `$badges, $settings` | Trust-bar slots. |
| `omnify_locate_template` | filter | `$path, $template_name` | Template override resolution. |
| `omnify_signed_url_ttl` / `omnify_download_url_ttl` | filter | `$seconds, $file_id, $customer_id` | HMAC download URL lifetime. |
| `omnify_safe_download_directories` | filter | `$dirs` | Allowed directories for streamed files. |
| `omnify_carrier_tracking_url` | filter | `$url, $carrier, $tracking_number` | Carrier tracking link builder. |
| `omnify_currency_exchange_rates` / `omnify_currency_exchange_rate` | filter | `$rates` / `null, $from, $to, $amount` | Currency conversion. |
| `omnify_currency_symbol` / `omnify_currency_decimals` / `omnify_converted_currency_amount` | filter | mixed | Currency formatting. |
| `omnify_countries` / `omnify_allowed_countries` / `omnify_states` / `omnify_cities` / `omnify_postcodes` | filter | mixed | Selling & address geography. |
| `omnify_cart_url` / `omnify_checkout_url` / `omnify_storefront_url` / `omnify_customer_portal_url` | filter | `$url` | Override resolved page URLs. |
| `omnify_payment_gateways` | filter | `$gateways` | Register custom payment gateways. |

---

## 11. Storefront Template Overriding & Theme Integration

OmnifyWP allows themes and child themes to override any storefront template file without modifying core plugin code.

### Template Hierarchy
Templates are located inside `omnifywp-ecommerce/templates/`. To override a template, create a matching file path inside your active WordPress theme directory:

```
your-active-theme/
└── omnify/
    ├── storefront/
    │   ├── grid.php            # Storefront catalog grid layout
    │   ├── product-single.php  # Elessi 3-column product details page
    │   ├── quick-view.php      # Quick view modal layout
    │   └── compare-drawer.php  # Product comparison drawer
    ├── cart/
    │   └── cart.php            # Scarcity countdown cart & shipping meter
    ├── checkout/
    │   ├── checkout-page.php   # 1-Page express checkout with 12 gateways
    │   └── order-receipt.php   # Order confirmation & receipt
    └── portal/
        └── customer-portal.php # Customer downloads, licenses & orders
```

### CSS Design Tokens & Unified Primary Accent
All storefront templates, buttons, badges, and interactive controls strictly utilize the unified primary brand accent `#0f766e` (Teal). Override these tokens in your theme's `style.css` to re-theme the entire storefront seamlessly:

```css
:root {
  /* Brand Accents (Unified Teal) */
  --omnify-primary: #0f766e;
  --omnify-primary-hover: #115e59;
  --omnify-primary-light: #f0fdfa;

  /* Card Surfaces & Borders */
  --omnify-surface: #ffffff;
  --omnify-surface-alt: #f8fafc;
  --omnify-border: #e2e8f0;
  --omnify-radius: 8px;
  --omnify-radius-sm: 4px;

  /* Typography Colors */
  --omnify-text-main: #0f172a;
  --omnify-text-muted: #64748b;

  /* Badge Accents */
  --omnify-badge-hot: #ef4444;
  --omnify-badge-sale: #0f766e;
  --omnify-badge-limited: #d97706;
}
```

### JavaScript Custom DOM Events
The frontend storefront dispatches custom browser events on `document` that your theme scripts can listen to:

```javascript
// Cart updated event
document.addEventListener('omnify:cart:updated', function(event) {
    console.log('Cart count:', event.detail.count);
    console.log('Cart subtotal:', event.detail.subtotal);
});

// Quick View opened event
document.addEventListener('omnify:quickview:opened', function(event) {
    console.log('Product ID opened:', event.detail.productId);
});

// Wishlist toggled event
document.addEventListener('omnify:wishlist:toggled', function(event) {
    console.log('Product ID:', event.detail.productId, 'Added:', event.detail.added);
});
```

---

## 12. Developer Recipes & Code Examples

### Recipe 1: Custom Checkout Fields (VAT / Tax ID & Delivery Instructions)
Enforce and validate custom checkout fields submitted by customers, save them into order records, and display them in the admin dashboard:

```php
/**
 * Step 1: Validate custom VAT registration number during checkout.
 */
add_filter('omnify_after_checkout_validation', function($error, $data) {
    $vat = trim($data['vat_number'] ?? '');
    
    // If customer provided a VAT ID, ensure it has at least 8 alphanumeric characters
    if (! empty($vat) && strlen($vat) < 8) {
        return new \WP_Error('invalid_vat', __('Please provide a valid VAT / Tax registration ID.', 'my-theme'));
    }
    
    return $error;
}, 10, 2);

/**
 * Step 2: Save custom VAT and delivery instructions into Order Notes upon creation.
 */
add_action('omnify_order_created', function($order_id, $order_data) {
    $orders = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Order_Repository::class);
    
    if (! empty($_POST['vat_number'])) {
        $vat = sanitize_text_field(wp_unslash($_POST['vat_number']));
        $orders->add_note($order_id, "Customer VAT / Tax ID: {$vat}", customer_visible: true);
    }

    if (! empty($_POST['delivery_notes'])) {
        $notes = sanitize_textarea_field(wp_unslash($_POST['delivery_notes']));
        $orders->add_note($order_id, "Delivery Instructions: {$notes}", customer_visible: true);
    }
}, 10, 2);
```

### Recipe 2: Real-Time Discord or Slack Order Alerts
Automatically dispatch an instant chat notification to a Discord or Slack channel whenever an order is marked completed:

```php
add_action('omnify_order_status_changed', function($order_id, $new_status) {
    // Trigger only when order reaches 'completed' or 'processing'
    if (! in_array($new_status, ['completed', 'processing'], true)) {
        return;
    }

    $order = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Order_Repository::class)->find($order_id);
    if (! $order) {
        return;
    }

    $webhook_url = 'https://discord.com/api/webhooks/YOUR_WEBHOOK_URL_HERE';

    $payload = [
        'embeds' => [
            [
                'title'       => "[Order] New Order #{$order_id} Received!",
                'color'       => 3921912, // Emerald green
                'description' => "**Customer:** {$order['billing_first_name']} {$order['billing_last_name']} ({$order['email']})\n"
                               . "**Total:** {$order['currency']} \${$order['total']}\n"
                               . "**Gateway:** {$order['payment_method']}\n"
                               . "**Status:** {$new_status}",
                'timestamp'   => date('c')
            ]
        ]
    ];

    wp_remote_post($webhook_url, [
        'headers'  => ['Content-Type' => 'application/json'],
        'body'     => wp_json_encode($payload),
        'blocking' => false, // Non-blocking async dispatch for maximum performance
        'timeout'  => 5,
    ]);
}, 10, 2);
```

### Recipe 3: Headless Next.js 14 / React Catalog Integration
Build a high-performance headless frontend by querying Omnify's public catalog REST endpoints with Incremental Static Regeneration (ISR):

```tsx
// app/products/page.tsx (Next.js 14 App Router Server Component)
interface Product {
  id: number;
  name: string;
  slug: string;
  price: number;
  sale_price: number | null;
  stock_status: string;
}

export default async function ProductsPage() {
  const res = await fetch('https://your-site.com/wp-json/omnify/v1/products/public?per_page=12', {
    next: { revalidate: 60 } // Revalidate every 60 seconds (ISR)
  });
  const products: Product[] = await res.json();

  return (
    <div className="max-w-6xl mx-auto py-12 px-4">
      <h1 className="text-3xl font-extrabold mb-8">Product Catalog</h1>
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        {products.map((p) => (
          <div key={p.id} className="border border-slate-800 rounded-xl p-5 bg-slate-900">
            <h3 className="font-bold text-lg text-white">{p.name}</h3>
            <p className="text-sky-400 font-semibold text-xl mt-2">
              ${p.sale_price ?? p.price}
            </p>
            <a href={`/products/${p.slug}`} className="block mt-4 text-center bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold py-2 rounded-lg">
              View Details
            </a>
          </div>
        ))}
      </div>
    </div>
  );
}
```

### Recipe 4: Auto-Generating & Emailing Software License Keys
Listen for digital access grants and automatically generate a cryptographically random software license key formatted as `XXXX-XXXX-XXXX-XXXX`:

```php
add_action('omnify_customer_access_granted', function($customer_id, $product_id) {
    // Format: XXXX-XXXX-XXXX-XXXX
    $raw_bytes   = random_bytes(8);
    $hex_string  = strtoupper(bin2hex($raw_bytes));
    $license_key = implode('-', str_split($hex_string, 4));

    // Retrieve customer details
    $customer = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Customer_Repository::class)->find($customer_id);
    if (! $customer) {
        return;
    }

    // Store license key in customer options
    update_user_meta($customer['user_id'] ?? 0, "omnify_license_{$product_id}", $license_key);

    // Dispatch email to customer
    $subject = 'Your Software License Activation Key';
    $message = "Hello {$customer['first_name']},\n\n"
             . "Thank you for your order! Here is your official activation license key:\n\n"
             . "  {$license_key}\n\n"
             . "You can download your product files anytime from your customer account portal.\n\n"
             . "Best regards,\nThe Team";

    wp_mail($customer['email'], $subject, $message);
}, 10, 2);
```

### Recipe 5: Tiered Bulk Volume Discount Engine
Automatically apply discounts during checkout based on the total quantity of items: 10% off for 3+ items, and 20% off for 5+ items:

```php
add_filter('omnify_before_checkout_validation', function($data, $settings, $items) {
    $total_qty = 0;
    foreach ($items as $item) {
        $total_qty += (int) ($item['quantity'] ?? 1);
    }

    // Apply 20% discount for 5+ items, or 10% for 3+ items
    $discount_rate = 0.0;
    if ($total_qty >= 5) {
        $discount_rate = 0.20;
    } elseif ($total_qty >= 3) {
        $discount_rate = 0.10;
    }

    if ($discount_rate > 0) {
        $data['volume_discount_rate'] = $discount_rate;
    }

    return $data;
}, 10, 3);
```

### Recipe 6: Custom Wire Transfer / Invoice Payment Method
Add an offline "Direct Invoice / Wire Transfer" payment option that places orders into `on-hold` status until manual wire confirmation:

```php
add_action('omnify_order_created', function($order_id, $order_data) {
    if (($order_data['payment_method'] ?? '') === 'wire_transfer') {
        $orders = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Order_Repository::class);
        
        // Set status to 'on-hold' until bank wire settles
        $orders->update($order_id, ['status' => 'on-hold']);
        
        // Add wire instructions for customer view
        $orders->add_note(
            $order_id,
            'Bank: Chase Bank | Account: #987654321 | Routing: #123456789. Please include Order #' . $order_id . ' in the wire memo.',
            customer_visible: true
        );
    }
}, 10, 2);
```

### Recipe 7: Dynamic Multi-Currency Pricing with Live Geo-Location
Show European customers prices in EUR (€) and UK customers prices in GBP (£) dynamically based on visitor IP address:

```php
add_filter('omnify_format_price', function($formatted, $amount, $settings) {
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'US';

    if (in_array($country, ['FR', 'DE', 'IT', 'ES', 'NL'], true)) {
        // EUR conversion rate (e.g. 1 USD = 0.92 EUR)
        $eur_price = number_format($amount * 0.92, 2);
        return "€{$eur_price} EUR";
    }

    if ($country === 'GB') {
        // GBP conversion rate (e.g. 1 USD = 0.79 GBP)
        $gbp_price = number_format($amount * 0.79, 2);
        return "£{$gbp_price} GBP";
    }

    return $formatted;
}, 10, 3);
```

### Recipe 8: Restricting Digital Downloads by Maximum Download Count
Prevent customers from sharing digital download links by capping file access to a maximum of 5 downloads per customer:

```php
add_filter('omnify_signed_download_expires_in', function($seconds, $file_id, $customer_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'omnify_download_logs';

    // Count successful past downloads for this customer and file
    $download_count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE customer_id = %d AND file_id = %d AND status = 'success'",
        $customer_id,
        $file_id
    ));

    if ($download_count >= 5) {
        // Return 0 or halt execution to prevent piracy
        wp_die(__('You have reached the maximum allowed downloads (5/5) for this file. Please contact support.', 'my-store'), 403);
    }

    return $seconds;
}, 10, 3);
```

### Recipe 9: Conditionally Hide Cash on Delivery for Digital-Only Carts
Prevent shoppers from selecting COD when the cart contains only downloadable products:

```php
add_filter('omnify_available_payment_gateways', function($gateways) {
    $has_physical = false;
    foreach (WC()->cart->get_cart() as $item) { /* replace with your cart source */ }

    // If every cart line is digital, strip the COD gateway
    if (! $has_physical && isset($gateways['cod'])) {
        unset($gateways['cod']);
    }
    return $gateways;
});
```

### Recipe 10: Sync Live FX Rates into the Multi-Currency Engine
Pull rates from an external API on a schedule and feed them to the storefront currency switcher:

```php
add_action('init', function() {
    if (! wp_next_scheduled('my_store_sync_fx')) {
        wp_schedule_event(time(), 'hourly', 'my_store_sync_fx');
    }
});

add_action('my_store_sync_fx', function() {
    $response = wp_remote_get('https://api.exchangerate.host/latest?base=USD');
    if (is_wp_error($response)) {
        return;
    }
    $data  = json_decode(wp_remote_retrieve_body($response), true);
    $rates = is_array($data['rates'] ?? null) ? $data['rates'] : [];
    set_transient('my_fx_rates', $rates, HOUR_IN_SECONDS);
});

// Consume the cached rates inside Omnify's conversion layer
add_filter('omnify_currency_exchange_rates', function($rates) {
    $live = get_transient('my_fx_rates');
    return is_array($live) && $live ? $live : $rates;
});
```

### Recipe 11: Headless Checkout via REST (Stripe Payment Intent)
Create an order and a Stripe Payment Intent from any decoupled frontend:

```javascript
// 1. Create the order + capture the gateway client secret
const res = await fetch('https://your-site.com/wp-json/omnify/v1/checkout', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.omnifyStorefront.nonce },
  body: JSON.stringify({
    billing_first_name: 'Jane',
    billing_last_name:  'Doe',
    billing_email:      'jane@example.com',
    billing_country:    'US',
    payment_method:     'stripe',
    items: [{ product_id: 15, variation_id: null, quantity: 1 }],
  }),
});
const order = await res.json();

// 2. Confirm the Payment Intent client-side, then verify server-side on redirect
console.log('Order', order.order_number, 'created');
```

### Recipe 12: Register a Custom Payment Gateway
Implement the gateway contract and register it through the `omnify_payment_gateways` filter:

```php
add_filter('omnify_payment_gateways', function(array $gateways) {
    $gateways['crypto_pay'] = new class {
        public function get_id(): string { return 'crypto_pay'; }
        public function get_title(): string { return __('Crypto (USDC / BTC)', 'my-store'); }
        public function is_enabled(): bool {
            return (bool) omnify()->container()
                ->get(\Omnify\eCommerce\Settings\Omnify_Settings_Repository::class)
                ->get('crypto_enabled', false);
        }
        public function process_payment(int $order_id, array $data): array {
            $order = omnify_get_order($order_id);
            return [
                'result'   => 'success',
                'redirect' => "https://pay.example.com/{$order['order_number']}",
            ];
        }
        public function process_refund(int $order_id, float $amount, string $reason = ''): bool {
            return true;
        }
    };
    return $gateways;
});
```

---

## 13. Roles, Capabilities & Staff Permissions

OmnifyWP ships a granular, per-feature permission model so store owners can safely delegate day-to-day operations to staff without granting full WordPress administrator rights.

### Capabilities
| Capability | Granted To | Meaning |
| :--- | :--- | :--- |
| `manage_omnify` | Administrators + mapped roles | Master capability gating the entire Omnify admin. |
| `manage_omnify_{feature}` | Roles mapped per feature | Scoped access to a single feature area. |

Supported feature scopes: `dashboard`, `orders`, `products`, `customers`, `coupons`, `abandoned_carts`, `reviews`, `analytics`.

### Built-in Roles
On first boot (`Omnify_Plugin::initialize_roles_and_caps()`), Omnify creates two roles when missing:

| Role | Capabilities | Typical Use |
| :--- | :--- | :--- |
| `shop_manager` | `read`, `edit_posts`, `manage_omnify` | Full operational store management (no `manage_options`). |
| `shop_clerk` | `read`, `edit_posts`, `manage_omnify` | Fulfillment, order processing, customer support. |

### Mapping Roles to Features
Permissions are stored in the `role_permissions` setting (`Settings → Roles`) and resolved at runtime through the `user_has_cap` filter, so a `shop_clerk` can be allowed to manage Orders but not Products or Settings:

```php
// Programmatically grant the shop_clerk role access to Orders and Reviews
$settings = get_option('omnify_settings', []);
$settings['role_permissions'] = array_merge((array) ($settings['role_permissions'] ?? []), [
    'orders'  => ['administrator', 'shop_manager', 'shop_clerk'],
    'reviews' => ['administrator', 'shop_manager', 'shop_clerk'],
]);
update_option('omnify_settings', $settings);
```

> [!NOTE]
> `manage_omnify_{feature}` capabilities are computed dynamically — they are never persisted to the role, so revocation is instant when the setting changes.

---

## 14. Analytics & Conversion Tracking (GA4 + Meta Pixel)

The `Omnify\eCommerce\Support\Omnify_Tracking_Service` injects first-party Google Analytics 4 and Meta Pixel tags on the storefront, driven by **Settings → Marketing**.

### Settings Keys
| Setting Key | Type | Description |
| :--- | :--- | :--- |
| `tracking_ga4_enabled` | bool | Enables the GA4 (`gtag.js`) snippet. |
| `tracking_ga4_measurement_id` | string | GA4 Measurement ID (e.g. `G-XXXXXXX`), upper-cased & sanitized on save. |
| `tracking_meta_enabled` | bool | Enables the Meta (Facebook) Pixel (`fbq`) snippet. |
| `tracking_meta_pixel_id` | string | Numeric Meta Pixel ID (digits only). |
| `tracking_debug_mode` | bool | Adds GA4 `debug_mode: true` for Tag Assistant verification. |

### Runtime Hooks
- `wp_enqueue_scripts` → `enqueue_tracking_codes()` prints the `gtag`/`fbq` bootstrap.
- `wp_body_open` → `output_meta_pixel_noscript()` prints the `<noscript><img></noscript>` fallback.
- Values are surfaced to the storefront JS through `window.omnifyStorefront.analytics` (`ga4Enabled`, `metaEnabled`).

### Tracked E-commerce Events
| Provider | Events |
| :--- | :--- |
| **GA4** | `view_item_list`, `view_item`, `add_to_cart`, `begin_checkout`, `purchase` (with `transaction_id`, `value`, `currency`, `items`). |
| **Meta Pixel** | `PageView`, `ViewContent`, `AddToCart`, `InitiateCheckout`, `Purchase`. |

```php
// Suppress Meta Pixel on a specific page
add_action('template_redirect', function() {
    if (is_page('thank-you')) {
        remove_action('wp_body_open', [omnify()->container()->get(\Omnify\eCommerce\Support\Omnify_Tracking_Service::class), 'output_meta_pixel_noscript']);
    }
});
```

---

## 15. Privacy, GDPR & Data Lifecycle

Omnify integrates with the WordPress core privacy framework (`Omnify\eCommerce\Support\Omnify_Privacy_Service`).

### Registered Tools
| WordPress Tool | Hook | Coverage |
| :--- | :--- | :--- |
| Personal Data Exporter | `wp_privacy_personal_data_exporters` | Customer profile, orders, order items, downloads, wishlist, abandoned carts. |
| Personal Data Eraser | `wp_privacy_personal_data_erasers` | Anonymizes/removes the same customer-owned records on request. |

The exporter/eraser are registered under the friendly name **Omnify**.

### Data Lifecycle & Retention
- **Soft deletes**: records use a nullable `deleted_at` and are excluded from storefront and public queries.
- **Automated maintenance**: the recurring cron `omnify_daily_database_cleanup` prunes expired abandoned carts past the retention window, clears stale session transients, and optimizes tables.
- **Email logs**: `wp_omnify_email_logs` retains dispatch history and retry state; failed sends are retried via the `omnify_retry_failed_emails` scheduled event.
- **Download audit**: `wp_omnify_downloads` logs IP, user agent, and status per delivery for GDPR access/erasure requests.

```php
// Extend the personal data exporter with a custom field
add_filter('wp_privacy_personal_data_exporters', function($exporters) {
    // Wrap the existing Omnify exporter to append loyalty points
    return $exporters;
});
```

---

## 16. Shipping Zones, Tax Rules & Multi-Currency

### Delivery Zones
Shipping rates are stored in the `delivery_zones` setting (`Settings → Delivery`) and exposed over REST at `GET|POST /wp-json/omnify/v1/delivery-zones`. Each zone carries a name, matched countries/regions, a rate type (flat/free/threshold), and optional local-pickup + per-item fees.

```php
// Register an additional delivery zone via the settings repository
$repo = omnify()->container()->get(\Omnify\eCommerce\Settings\Omnify_Settings_Repository::class);
$zones = (array) $repo->get('delivery_zones', []);
$zones['eu_standard'] = [
    'title'      => 'EU Standard (5-7 days)',
    'countries'  => ['DE', 'FR', 'NL', 'ES', 'IT'],
    'rate'       => 9.90,
    'free_over'  => 120.00,
];
$repo->update(['delivery_zones' => $zones]);
```

### Tax Rules
Tax tables live in the tax-rules setting (`Settings → Tax`) and are exposed at `GET|POST /wp-json/omnify/v1/tax-rules`. Relevant settings keys: `prices_include_tax`, `tax_shipping`, `tax_rounding` (`line`/`total`), `tax_reporting_enabled`, and per-order `tax_reporting_code`.

### Multi-Currency Engine
`Omnify\eCommerce\Support\Omnify_Locations` powers the `[omnify_currency_switcher]` shortcode and all price conversion. Rates and symbols are filterable:

| Filter | Purpose |
| :--- | :--- |
| `omnify_currency_exchange_rates` | Override the full rate table (e.g. sync from an FX API). |
| `omnify_currency_exchange_rate` | Override a single from→to rate. |
| `omnify_converted_currency_amount` | Post-process a converted amount before formatting. |
| `omnify_currency_symbol` | Override a currency symbol. |
| `omnify_currency_decimals` | Override decimal precision per currency. |
| `omnify_allowed_countries` / `omnify_countries` / `omnify_states` | Control the storefront selling/address geography. |

```php
// Fetch live FX rates from a transient-backed external API
add_filter('omnify_currency_exchange_rates', function($rates) {
    $live = get_transient('my_fx_rates');
    return is_array($live) ? $live : $rates;
});
```

---

## 17. Product Variations, Availability & Preorders

Variable products are modelled across `wp_omnify_products` (parent) and `wp_omnify_product_variations` (matrix rows). Availability logic is driven by dedicated columns rather than a single stock field.

### Availability Columns
| Column | Table | Purpose |
| :--- | :--- | :--- |
| `manage_stock` | products + variations | Enables numeric inventory tracking. |
| `stock_qty` | products + variations | Tracked quantity. |
| `stock_status` | products + variations | `instock` / `outofstock` / `onbackorder`. |
| `allow_backorders` | products + variations | Permit purchases when out of stock. |
| `preorder_enabled` | products + variations | Marks the item as a preorder. |
| `preorder_release_date` | products + variations | Scheduled release date. |
| `preorder_limit` | products + variations | Maximum preorder quantity. |
| `preorder_message` | products + variations | Shopper-facing preorder notice. |
| `estimated_delivery` | variations | Per-variation dispatch estimate. |

### Stock & Access Hooks
`omnify_stock_reduced`, `omnify_product_restocked`, `omnify_order_fraud_flagged`, `omnify_order_fulfilled`, `omnify_customer_access_granted` and `omnify_customer_access_revoked` let you mirror availability changes into external systems.

```php
add_action('omnify_product_restocked', function($product_id, $variation_id) {
    // Notify a back-in-stock Slack channel
}, 10, 2);
```

---

## 18. Background Jobs, Cron & Maintenance

| Scheduled Hook | Frequency | Responsibility |
| :--- | :--- | :--- |
| `omnify_abandoned_cart_recovery` | Hourly | Scans `wp_omnify_abandoned_carts` and dispatches recovery emails. |
| `omnify_daily_database_cleanup` | Daily | Prunes expired carts, stale checkouts, and optimizes indexes. |
| `omnify_retry_failed_emails` | Hourly | Re-dispatches failed sends logged in `wp_omnify_email_logs`. |
| `omnify_prune_webhook_logs` | Daily | Trims old `wp_omnify_webhook_delivery_logs` rows. |

Deactivation unschedules the recovery and webhook-prune events (`Omnify_Plugin::deactivate()`).

```php
// Run custom maintenance alongside the daily cleanup
add_action('omnify_daily_database_cleanup', function() {
    // Rebuild a custom reporting table
});
```

---

## 19. Admin Pages & Settings Reference

### Admin Menu Directory
All Omnify screens are registered under the top-level **Omnify** menu (`Omnify_Admin_Page::register_menu()`, capability `manage_omnify`):

| Slug | Screen | Purpose |
| :--- | :--- | :--- |
| `omnify-dashboard` | Dashboard | Financial KPIs, health monitor, recent orders/customers. |
| `omnify-orders` | Orders | Order fulfillment, refunds, notes, packing slips. |
| `omnify-products` | Products | Catalog table + 4-step product editor. |
| `omnify-customers` | Customers | CRM directory, LTV, activity, notes. |
| `omnify-coupons` | Coupons | Promotion code management. |
| `omnify-analytics` | Analytics | Sales/AOV/refund reporting + CSV export. |
| `omnify-categories` / `omnify-tags` / `omnify-brands` / `omnify-attributes` | Taxonomies | Catalog taxonomy managers. |
| `omnify-abandoned-carts` | Abandoned Carts | Captured sessions + recovery. |
| `omnify-reviews` | Reviews | Moderation queue. |
| `omnify-export` | Import / Export | CSV/XML export, import, schema reference. |
| `omnify-api` | API & Webhooks | Keys, outbound webhooks, gateway diagnostics. |
| `omnify-activity` | Activity Log | Administrative audit trail. |
| `omnify-tools` | Tools & Seeding | Diagnostics, demo data, cleanup. |
| `omnify-settings` | Settings | Multi-tab configuration hub. |
| `omnify-setup` | Setup Wizard | 4-step onboarding. |

### Settings Sections
The Settings hub is segmented into the following tabs (see `Omnify_Settings_Search_Index::sections()`), and the same slug list gates which keys a save request may modify:

| Slug | Label | Notable Settings |
| :--- | :--- | :--- |
| `general` | General Settings | Store name, base country, selling locations, currency, price formatting, tax defaults, test mode. |
| `pages` | Core Pages | Storefront/Cart/Checkout/Portal/Download/Tracking page mapping + auto page setup. |
| `roles` | Shop Roles | Minimum capability, feature permissions matrix, staff directory. |
| `payments` | Payment Methods | 12 gateways + offline methods, modes, keys, webhook URLs. |
| `tax` | Tax Rules | Tax defaults + regional tax rule table. |
| `delivery` | Delivery Zones | Zones, method type, cost, free-shipping minimum. |
| `checkout` | Checkout Flow | Guest/account handling, stock hold, order number format, coupons, order bump, downloads, terms, refunds. |
| `fraud` | Fraud & Risk | Country mismatch, disposable email, high-value threshold, IP velocity. |
| `marketing` | Marketing & Tracking | GA4 + Meta Pixel and debug logging. |
| `design` | Storefront Design | Brand colors, typography, hero, catalog layout, filters, product card. |
| `email` | Email Templates | 17 triggers, SMTP relay, routing, footer. |
| `tools` | Tools & Maintenance | Optimize tables, clear transients, purge carts, inventory resync. |

> [!NOTE]
> Category/tag/brand/attribute management also runs through the settings saver (`categories`, `tags`, `brands`, `attributes`) alongside the Settings tabs.

---

## 20. Email Template Merge Tags

Every email subject and body supports dynamic merge tags, resolved by `Omnify_Email_Service::replace_tokens()`.

### Order Tags
| Tag | Resolves To |
| :--- | :--- |
| `{site_name}` | Store name. |
| `{order_id}` / `{order_number}` | Customer-facing order number. |
| `{order_total}` | Currency + formatted order total. |
| `{order_status}` | Humanized order status. |
| `{old_status}` / `{new_status}` | Previous and new status (status-change emails). |
| `{customer_name}` / `{customer_first_name}` / `{first_name}` / `{last_name}` | Customer name parts. |
| `{customer_email}` | Customer email address. |
| `{customer_portal_url}` / `{portal_url}` | Customer portal URL. |
| `{tracking_carrier}` / `{tracking_number}` | Shipment carrier and tracking number. |
| `{refund_reason}` / `{refund_amount}` | Refund reason and credited amount. |

### Inventory Tags
`{product_name}`, `{variation}`, `{sku}`, `{stock_qty}`, `{threshold}`.

### Cart Recovery Tags
`{cart_total}`, `{recovery_url}`, `{coupon_code}`, `{unsubscribe_url}`, `{reminder_stage}`.

---

## 21. CSV & XML Import/Export Schema

Under **Omnify → Import / Export**, records move in and out as schema-validated CSV (or XML for products). Column order does not matter; unknown columns are ignored, and required fields are validated on import.

| Entity | Required Fields | Full Header Set |
| :--- | :--- | :--- |
| **Products** | `name` | `name, slug, type, product_kind, sales_type, price, sale_price, currency, sku, status, stock_status, manage_stock, stock_qty, allow_backorders, preorder_enabled, preorder_release_date, preorder_limit, preorder_message, shipping_class, weight, length, width, height, download_expiry_days, categories, tags, description` |
| **Customers** | `email` | `email, first_name, last_name, status, phone, company, country, tax_exempt` |
| **Orders** | `order_number`, `customer_email` | `order_number, customer_email, status, currency, subtotal, tax, shipping_total, total, payment_method, coupon_code, discount_amount` |
| **Coupons** | `code`, `discount_type`, `discount_value` | `code, is_active, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, usage_limit_per_customer, free_shipping, first_order_only, included_product_ids, excluded_product_ids, included_categories, excluded_categories, expires_at` |
| **Tax Rules** | `label`, `country`, `state`, `rate` | `enabled, label, country, state, rate, priority, reporting_code` |
| **Delivery Zones** | `name`, `countries`, `states`, `method_name` | `enabled, name, countries, states, method_name, method_type, cost, free_min, class_costs, priority` |

Export handlers are exposed as admin-post actions: `omnify_export_products`, `omnify_export_products_xml`, `omnify_export_customers`, `omnify_export_orders`, `omnify_export_coupons`, `omnify_export_tax_rules`, `omnify_export_delivery_zones`, `omnify_export_downloads`. Imports post to `omnify_import_csv`.

---

## 22. Performance, Caching & Scaling

| Technique | Where | Benefit |
| :--- | :--- | :--- |
| Normalized indexed tables | `wp_omnify_*` | Avoids `wp_postmeta` EAV joins; single-query lookups. |
| Batch hydration | `Product_File_Repository::all_for_products()` | Eliminates N+1 file queries across catalog loops. |
| Object cache group | `omnifywp-ecommerce` | Caches product/slug/sku/customer lookups via `wp_cache_*`. |
| Compound indexes | `omnify_orders` (`status, created_at`, `customer_id, status, created_at`) | Fast filtered order listings. |
| Atomic stock ops | `Product_Repository::reduce_stock()` | Prevents oversell under concurrency. |
| Chunked downloads | `Download_Controller::stream()` (8KB buffers) | Large files without memory exhaustion. |
| Lightweight frontend | `<35KB` script footprint | Fast storefront boot. |
| Deferred cron | `omnify_daily_database_cleanup` et al. | Moves maintenance off the request path. |

```php
// Warm the product cache after a bulk import
$repo = omnify()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Product_Repository::class);
$repo->all(['status' => 'published', 'per_page' => 100]);
```

---

## 23. Security Model & Hardening

- **Capability gating**: every admin route requires `manage_omnify` (or a scoped `manage_omnify_{feature}`); REST management routes require the same or a valid API key.
- **REST authentication**: WordPress nonce (`X-WP-Nonce`), HTTP Basic (`consumer_key:consumer_secret`), custom `X-Consumer-Key`/`X-Consumer-Secret` headers, or query params — resolved by `Omnify_Auth::determine_current_user()`.
- **Secure-by-default gateways**: all gateways start disabled; none appear at checkout until credentials are saved and the toggle enabled.
- **Webhook verification**: Stripe `Stripe-Signature` (with 300s tolerance), PayPal `/verify-webhook-signature`, Razorpay/Paystack HMAC (`X-Razorpay-Signature` / `X-Paystack-Signature`), Paddle `Paddle-Signature`, Alipay RSA2, WeChat AES-256-GCM.
- **Download protection**: HMAC-SHA256 tokens salted with `wp_salt('auth')`, `realpath()` containment checks, and `wp_normalize_path()` guards.
- **Output escaping**: admin/storefront templates escape with `esc_html`/`esc_attr`/`esc_url`; nonces on all mutating actions.
- **Privacy**: core exporter/eraser integration and soft deletes.

### Server Hardening
Protect the protected-files directory so static URLs cannot bypass the PHP token validator:

```apache
# wp-content/uploads/omnify-protected/.htaccess
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
```

```nginx
location ^~ /wp-content/uploads/omnify-protected/ {
    internal;
    return 403;
}
```

---

## 24. Glossary

| Term | Meaning |
| :--- | :--- |
| **Bundle** | A curated product composed of multiple catalog items sold as one discounted SKU. |
| **Customer Access** | A row in `wp_omnify_customer_access` granting a customer entitlement to a digital product. |
| **Delivery Zone** | A shipping rule scoped by country/region with a rate type. |
| **HMAC Token** | A signed, expiring download URL that conceals the physical file path. |
| **Order Bump** | A one-click upsell offered directly on the checkout form. |
| **Preorder** | A product/variation sold before its release date, with optional limits and notices. |
| **Soft Delete** | A nullable `deleted_at` flag that hides a record without purging it. |
| **Trigger** | A named automated email dispatched at a lifecycle event. |
| **Webhook Event** | An inbound gateway callback recorded in `wp_omnify_webhook_events`. |

---

*Documentation maintained by OmnifyWP Engineering.*
*GitHub Repository:* [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)
