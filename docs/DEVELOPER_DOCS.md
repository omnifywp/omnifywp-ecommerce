# OmnifyWP eCommerce — Complete Developer Documentation

> **Version:** 1.4.2  
> **Official Website:** [https://omnifywp.com](https://omnifywp.com)  
> **wp.org Directory:** [https://wordpress.org/plugins/omnifywp-ecommerce/](https://wordpress.org/plugins/omnifywp-ecommerce/)  
> **GitHub Repository:** [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)  
> **Minimum Requirements:** PHP 8.2+, WordPress 6.5+, MySQL 5.7+ / MariaDB 10.3+  
> **Interactive Documentation:** Open [`docs/index.html`](./index.html) or [`docs/user-guide.html`](./user-guide.html)  

---

## Table of Contents
1. [Introduction & Core Philosophy](#1-introduction--core-philosophy)
2. [Plugin Lifecycle & Container Architecture](#2-plugin-lifecycle--container-architecture)
3. [Database Engine & 18 Relational Tables](#3-database-engine--18-relational-tables)
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

## 3. Database Engine & 18 Relational Tables

### Architectural Schema Overview

All custom tables use the `$wpdb->prefix . 'omnify_'` naming convention and InnoDB engine with `utf8mb4_unicode_ci` or standard WordPress charset collation.

| Table Name | Primary Purpose | Key Indices |
| :--- | :--- | :--- |
| `wp_omnify_products` | Master product catalog (simple, variable, bundle, download) | `PRIMARY (id)`, `UNIQUE KEY (slug)`, `KEY status_type (status, type)` |
| `wp_omnify_product_variations` | Matrix variations (SKU, price, attributes) | `PRIMARY (id)`, `KEY (product_id)` |
| `wp_omnify_product_files` | Physical downloadable files & file metadata | `PRIMARY (id)`, `KEY (product_id)`, `KEY (expires_at)` |
| `wp_omnify_product_categories` | Product category taxonomy associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_product_brands` | Product brand associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_product_tags` | Product tag associations | `PRIMARY (id)`, `KEY (product_id)`, `KEY (slug)` |
| `wp_omnify_orders` | Financial transactions, tax, shipping, and customer references | `PRIMARY (id)`, `UNIQUE KEY (order_number)`, `KEY customer_status (customer_id, status)` |
| `wp_omnify_order_items` | Snapshot of purchased products, prices, and quantities | `PRIMARY (id)`, `KEY (order_id)`, `KEY (product_id)` |
| `wp_omnify_order_notes` | Staff comments & customer-facing order updates | `PRIMARY (id)`, `KEY (order_id)`, `KEY (customer_visible)` |
| `wp_omnify_customers` | Customer profiles, default addresses, and tax exemption status | `PRIMARY (id)`, `UNIQUE KEY (email)`, `KEY (user_id)`, `KEY (status)` |
| `wp_omnify_customer_notes` | Private admin CRM notes per customer | `PRIMARY (id)`, `KEY (customer_id)`, `KEY (customer_visible)` |
| `wp_omnify_customer_activity` | Event timeline (orders, logins, address changes) | `PRIMARY (id)`, `KEY (customer_id)`, `KEY (activity_type)` |
| `wp_omnify_customer_access` | Digital file entitlements granting access to files | `PRIMARY (id)`, `UNIQUE KEY customer_product (customer_id, product_id)` |
| `wp_omnify_downloads` | Download logs with IP, user-agent, and status tracking | `PRIMARY (id)`, `KEY (product_id)`, `KEY (file_id)`, `KEY (customer_id)` |
| `wp_omnify_coupons` | Promotion codes, discount rules, usage limits, and expiry | `PRIMARY (id)`, `UNIQUE KEY (code)` |
| `wp_omnify_reviews` | Ratings, moderation status, and customer reviews | `PRIMARY (id)`, `KEY (product_id)`, `KEY (status)` |
| `wp_omnify_abandoned_carts` | Incomplete checkout sessions, tokens, and recovery reminders | `PRIMARY (id)`, `UNIQUE KEY (token)`, `KEY email_status (email, status)` |
| `wp_omnify_wishlists` | Customer product favorites associations | `PRIMARY (id)`, `UNIQUE KEY user_product (user_id, product_id)` |
| `wp_omnify_activity_logs` | Admin action audits and security trace logs | `PRIMARY (id)`, `KEY (user_id)`, `KEY (action)` |

### Deep Column & Index Breakdown

#### 1. `wp_omnify_products` (Core Catalog)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Unique product ID.
- `title` (VARCHAR(255) NOT NULL): Product display title.
- `slug` (VARCHAR(255) NOT NULL UNIQUE): URL-safe permalink identifier.
- `type` (ENUM('simple', 'variable', 'download', 'bundle') DEFAULT 'simple'): Product architecture type.
- `status` (ENUM('published', 'draft', 'trash') DEFAULT 'published'): Catalog visibility status.
- `price` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Regular selling price.
- `sale_price` (DECIMAL(12,2) NULL): Discounted price if promotional pricing active.
- `sku` (VARCHAR(100) NULL): Stock Keeping Unit identifier.
- `stock_quantity` (INT NOT NULL DEFAULT 0): Available inventory count.
- `manage_stock` (TINYINT(1) NOT NULL DEFAULT 0): Inventory tracking toggle (`1` = active).
- `featured_image` (VARCHAR(500) NULL): URL or WordPress attachment ID of main photo.
- `gallery` (LONGTEXT NULL): JSON array of secondary gallery image URLs.
- `description` (LONGTEXT NULL): Full HTML product description.
- `short_description` (TEXT NULL): Brief excerpt displayed on product cards and list views.
- `total_sales` (INT UNSIGNED NOT NULL DEFAULT 0): Counter for popularity sorting.
- `rating_cache` (DECIMAL(3,2) NOT NULL DEFAULT 0.00): Cached average customer star rating.
- `rating_count` (INT UNSIGNED NOT NULL DEFAULT 0): Total count of approved reviews.
- `created_at`, `updated_at`, `deleted_at` (DATETIME NULL): Audit timestamps.

#### 2. `wp_omnify_orders` (Financial Transactions)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Internal order ID.
- `order_number` (VARCHAR(64) NOT NULL UNIQUE): Sequential or randomized customer-facing reference (e.g. `#1042`).
- `customer_id` (BIGINT UNSIGNED NULL): Foreign key referencing `wp_omnify_customers.id`.
- `status` (ENUM('pending_payment', 'processing', 'completed', 'on_hold', 'cancelled', 'refunded', 'failed') NOT NULL DEFAULT 'pending_payment'): Order state machine value.
- `currency` (VARCHAR(3) NOT NULL DEFAULT 'USD'): ISO 4217 currency code.
- `subtotal` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Sum of line items before tax and shipping.
- `discount_total` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Applied coupon discount amount.
- `tax_total` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Calculated sales tax amount.
- `shipping_total` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Shipping fee charged.
- `total` (DECIMAL(12,2) NOT NULL DEFAULT 0.00): Grand total charged to customer.
- `payment_method` (VARCHAR(50) NOT NULL): Gateway identifier (e.g., `stripe`, `paypal`, `razorpay`, `cod`, `bacs`).
- `transaction_id` (VARCHAR(255) NULL): Payment gateway reference token (e.g. `pi_3L714q...`).
- `billing_first_name`, `billing_last_name`, `billing_email`, `billing_phone` (VARCHAR(255) NULL): Customer details.
- `billing_address_1`, `billing_address_2`, `billing_city`, `billing_state`, `billing_postcode`, `billing_country`: Billing postal addresses.
- `shipping_first_name`, `shipping_last_name`, `shipping_address_1`, `shipping_city`, `shipping_country`: Physical delivery destination.
- `ip_address` (VARCHAR(45) NULL): IPv4 or IPv6 address for fraud auditing.
- `created_at`, `updated_at`, `deleted_at` (DATETIME NULL): Lifecycle timestamps.

#### 3. `wp_omnify_customer_access` (Digital Entitlements)
- `id` (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY): Access token ID.
- `customer_id` (BIGINT UNSIGNED NOT NULL): Customer identifier.
- `product_id` (BIGINT UNSIGNED NOT NULL): Purchased digital product.
- `order_id` (BIGINT UNSIGNED NOT NULL): Reference order granting the entitlement.
- `granted_at` (DATETIME NOT NULL): Timestamp when access was unlocked.
- `expires_at` (DATETIME NULL): Timestamp when access terminates (`NULL` = lifetime).
- `is_active` (TINYINT(1) NOT NULL DEFAULT 1): Hard kill switch (`0` upon refund).

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
| `[omnify_order_tracking]` | None | — | Public guest order lookup form requiring only Order ID and Billing Email without account login. |

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
User-Agent: OmnifyWP-Webhooks/1.4.2
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
5. Logs the event into `wp_omnify_activity_logs`.

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
| `/wishlist` | `GET` | Logged In | Retrieve customer favorites. |
| `/wishlist/toggle` | `POST` | Logged In | Toggle item favorite status. |
| `/products/{id}/reviews` | `GET` \| `POST` | Public | Read approved reviews or submit review. |

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
| `/activity-logs` | `GET` | Administrative security audit log feed. |
| `/delivery-zones` | `GET` \| `POST` | List or update shipping delivery zones. |
| `/tax-rules` | `GET` \| `POST` | List or update regional tax rates. |

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

### 16 Automated Email Notification Triggers

OmnifyWP includes 16 automated email notification triggers that can be subscribed to via action hooks or configured via **Omnify → Settings → Emails**:

1. `order_confirmation` — Dispatched to customer immediately upon successful payment capture.
2. `admin_new_order` — Dispatched to store admin email upon new order creation.
3. `payment_success` — Dispatched when payment authorization clears.
4. `payment_failed` — Dispatched when card authorization or gateway returns an error.
5. `order_status_changed` — Dispatched whenever order transitions between statuses.
6. `order_refunded` — Dispatched when full or partial refund is processed.
7. `order_cancelled` — Dispatched upon order cancellation.
8. `digital_download_ready` — Dispatched with cryptographic HMAC tokens for digital purchases.
9. `low_stock_admin` — Alert sent to admin when inventory drops below safety threshold.
10. `out_of_stock_admin` — Emergency alert sent when product stock reaches zero.
11. `customer_welcome` — Welcome email sent upon customer registration.
12. `password_reset` — Password recovery link with tokenized authentication.
13. `order_processing` — Dispatched when warehouse begins order packing.
14. `shipment_dispatched` — Delivery notice with carrier and tracking number.
15. `abandoned_cart_recovery` — Scheduled recovery email dispatched 1 hour post-abandonment.
16. `review_request` — Verified buyer review request dispatched after product delivery.

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
    │   ├── checkout-page.php   # 1-Page express checkout with 10+ gateways
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

---

*Documentation maintained by OmnifyWP Engineering.*
*GitHub Repository:* [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)
