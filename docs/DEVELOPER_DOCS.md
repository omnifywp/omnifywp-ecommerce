# OmnifyWP eCommerce — Complete Developer Documentation

> **Version:** 1.4.0  
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
   - [Shortcodes Directory](#shortcodes-directory)
   - [Client-Side State Engine (`storefront.js`)](#client-side-state-engine)
7. [Payment Gateways & Webhooks Engine](#7-payment-gateways--webhooks-engine)
   - [Supported Gateways & Secure-by-Default Architecture](#supported-gateways)
   - [Complete Webhook Endpoints & Verification Engine](#webhook-endpoints)
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
```

---

## 6. Storefront & Checkout Internals

### Shortcodes Directory
- `[omnify_storefront]`: Renders product grid with search, filter sidebar, style switcher, quick add, and single product view.
- `[omnify_cart]`: Interactive cart table with quantity counters, coupon code form, and shipping zone selector.
- `[omnify_checkout]`: Single-page checkout with live tax calculator, payment gateway selectors, and saved address autofill.
- `[omnify_customer_portal]`: Account dashboard with order history, profile address book, and wishlist tab.
- `[omnify_customer_downloads]`: Customer digital locker displaying owned files and expiring download links.
- `[omnify_order_tracking]`: Public order lookup supporting order statuses and active digital download links for paid items.

### Client-Side State Engine (`window.omnifyStorefront`)
Injected onto all storefront pages via `wp_localize_script`:
```javascript
window.omnifyStorefront = {
  restUrl: "https://example.com/wp-json/omnify/v1", // Normalized with untrailingslashit
  nonce: "4a28c9b1e5",
  currency: "USD",
  currencySymbol: "$",
  currencyPosition: "before",
  taxRate: 0.08,
  taxRules: [],
  pricesIncludeTax: false,
  taxShipping: false,
  checkoutUrl: "https://example.com/checkout/",
  cartUrl: "https://example.com/cart/",
  isLoggedIn: true,
  wishlistProductIds: [15, 22],
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
| **Stripe** | `POST /wp-json/omnify/v1/stripe/webhook` | `HTTP_STRIPE_SIGNATURE` HMAC-SHA256 against `stripe_webhook_secret` | `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded` |
| **PayPal** | `POST /wp-json/omnify/v1/paypal/webhook` | Handshake via PayPal API `verify-webhook-signature` using `paypal_webhook_id` | `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.REFUNDED` |
| **Razorpay** | `POST /wp-json/omnify/v1/razorpay/webhook` | `HTTP_X_RAZORPAY_SIGNATURE` HMAC-SHA256 against `razorpay_webhook_secret` | `payment.captured`, `payment.failed`, `refund.processed` |
| **SSLCommerz** | `POST /wp-json/omnify/v1/sslcommerz/ipn` | Server-to-server transaction validation session inquiry with Store Password | Order validation, payment capture |
| **Paystack** | `POST /wp-json/omnify/v1/paystack/webhook` | `HTTP_X_PAYSTACK_SIGNATURE` HMAC-SHA512 against Secret Key | `charge.success`, `refund.processed` |
| **Tap Payments**| `POST /wp-json/omnify/v1/tap/webhook` | Header signature verification against Secret Key | `CHARGE.CAPTURED`, `CHARGE.FAILED` |
| **Mollie** | `POST /wp-json/omnify/v1/mollie/webhook` | Server-to-server API check fetching payment object by ID | `paid`, `canceled`, `expired`, `failed` |
| **Paddle** | `POST /wp-json/omnify/v1/paddle/webhook` | `Paddle-Signature` header verification against `paddle_webhook_secret` | `transaction.paid`, `transaction.billed`, `adjustment.updated` |
| **Alipay** | `POST /wp-json/omnify/v1/alipay/webhook` | RSA2 / MD5 signature verification | Payment notify trade success |
| **WeChat Pay**| `POST /wp-json/omnify/v1/wechat/webhook` | AES-256-GCM decrypt using APIv3 key | Resource trade success |

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

### Action Hooks
- `omnify_before_boot($plugin, $container)`: Fires before plugin controllers boot.
- `omnify_booted($plugin, $container)`: Fires after full plugin boot completion.
- `omnify_pre_create_product_data($data)`: Intercepts raw product data before DB insert.
- `omnify_product_created($product_id, $data)`: Fires after product creation.
- `omnify_product_updated($product_id, $data)`: Fires after product update.
- `omnify_product_trashed($product_id, $product)`: Fires when a product is soft-deleted.
- `omnify_product_restored($product_id, $product)`: Fires when a product is recovered from trash.
- `omnify_product_deleted($product_id)`: Fires after permanent product purge.
- `omnify_order_created($order_id, $data)`: Fires after order insertion.
- `omnify_order_status_changed($order_id, $new_status, $old_status)`: Fires on status transitions.
- `omnify_stock_reduced($product_id, $variation_id, $qty, $new_stock)`: Fires after stock reduction.
- `omnify_customer_access_granted($customer_id, $product_id, $expires_at, $insert_id)`: Fires on access grant.
- `omnify_customer_access_revoked($customer_id, $product_id)`: Fires on access revocation.
- `omnify_download_logged($insert_id, $product_id, $file_id, $customer_id, $status)`: Fires on file stream.

### Filter Hooks Directory & Reference

| Filter Hook | Parameters | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `omnify_format_price` | `$formatted, $amount, $settings` | `string` | Customize currency rendering, multi-currency conversion, or price suffixes (e.g. VAT tags, FREE badges). |
| `omnify_before_checkout_validation` | `$data, $settings, $items` | `array` | Clean, sanitize, or inject custom checkout form fields before validation starts (e.g. volume discounts). |
| `omnify_after_checkout_validation` | `$error, $data, $settings, $items` | `null\|WP_Error` | Inject custom business validation logic. Return a `WP_Error` to halt checkout and show error banner. |
| `omnify_get_products_args` | `$args` | `array` | Modify query parameters (filters, sorting, taxonomy) before catalog search queries run. |
| `omnify_get_orders_args` | `$args` | `array` | Modify criteria when querying orders in the repository or customer dashboard. |
| `omnify_signed_download_expires_in` | `$seconds, $file_id, $customer_id` | `int` | Adjust the expiration duration (default: 900s) for cryptographic digital download URLs. |
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
    │   ├── single-product.php  # Product details page layout
    │   ├── quick-view.php      # Quick view modal layout
    │   └── compare-drawer.php  # Product comparison drawer
    ├── cart/
    │   └── cart.php            # Shopping cart table & coupons
    ├── checkout/
    │   ├── checkout.php        # 1-Page and slide-over checkout form
    │   └── order-receipt.php   # Order confirmation & receipt
    └── portal/
        └── customer-portal.php # Customer downloads, licenses & orders
```

### CSS Design Tokens & Custom Properties
Override these tokens in your theme's `style.css` to re-theme the entire storefront seamlessly:

```css
:root {
  /* Brand Accents */
  --omnify-primary: #18794e;
  --omnify-primary-hover: #146340;
  --omnify-primary-light: #ecfdf5;

  /* Card Surfaces & Borders */
  --omnify-surface: #ffffff;
  --omnify-surface-alt: #f6f6f8;
  --omnify-border: #e5e7eb;
  --omnify-radius: 8px;
  --omnify-radius-sm: 4px;

  /* Typography Colors */
  --omnify-text-main: #111827;
  --omnify-text-muted: #6b7280;

  /* Badge Accents */
  --omnify-badge-hot: #ea3a3c;
  --omnify-badge-sale: #22c55e;
  --omnify-badge-limited: #f59e0b;
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
