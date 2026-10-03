# ⚡ Omnify

[![WordPress Version](https://img.shields.io/badge/WordPress-5.8+-21759B.svg?style=flat-square&logo=wordpress)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg?style=flat-square)](https://gnu.org/licenses/gpl.html)
[![Documentation](https://img.shields.io/badge/Docs-omnifywp.com%2Fdoc-blueviolet.svg?style=flat-square)](https://omnifywp.com/doc/)
[![Developer Friendly](https://img.shields.io/badge/Extensible-Filters%20%26%20Actions-brightgreen.svg?style=flat-square)](#-developer-extensibility)

**Omnify** is a high-performance, developer-first eCommerce engine built for WordPress. Highly optimized, extremely secure, and headless-ready, Omnify gives you the ultimate storefront, checkouts, payment integrations, and secure digital downloads experience without the bloat of traditional eCommerce plugins.

> 📖 **Official Developer Documentation:** [https://omnifywp.com/doc/](https://omnifywp.com/doc/)  
> 💻 **GitHub Repository:** [https://github.com/omnifywp/omnifywp-ecommerce](https://github.com/omnifywp/omnifywp-ecommerce)

---

## 🌟 Highly Sellable Core Features

### 🛍️ Storefront & Conversion-Optimization
* **Premium Storefront Grid Layout**: High-end storefront layout displaying catalog items with responsive grid adjustments, visual layout style toggles, integrated cart buttons in filter panels, and brand/category title headers.
* **Storefront Quick Add**: Hovering over product images reveals a Quick Add icon that instantly adds items to the cart via AJAX, showing a beautiful checkmark animation and updating the cart badge count instantly.
* **Premium Product Details Layout**: 3-column details layout with a media gallery, trust badges, features checklist, and a sticky purchase card housing format swatches and quantity inputs.
* **Smart Checkout Engine**: Frictionless single-page checkout form featuring country-first selectors, region/state handles, and a saved address autocomplete autofill dropdown.
* **Variable & Simple Products**: Switch variations dynamically with format-specific pricing, descriptions, SKUs, and variation file attachments.
* **Product Bundles**: Group multiple digital products together so a single purchase grants access to all child files.
* **Wishlist Integration**: Built-in wishlist mechanism to let users add products to their favorites list.
* **Cart Scarcity & Progress Tracker**: Real-time Cart Scarcity Countdown Timer (with sessionStorage persistence) and a Free Shipping Progress Tracker to optimize user conversions.
* **Segmented Settings Toggle Switches**: Clean, modern Yes/No segmented controls for managing setting options.

### 💳 Comprehensive Payment Gateways (Multi-Currency & Global Support)
Omnify comes pre-integrated with the world's most popular payment processors, featuring automatic refunds and instant digital file access revocation upon refund processing:
* **Stripe Integration**: Supports credit cards, Apple Pay, Google Pay, Alipay, and Stripe Checkout.
* **PayPal Commerce**: Secure Express Checkout, credit/debit cards, and smart payment buttons.
* **Razorpay**: Best-in-class checkout for India, supporting UPI, cards, netbanking, and wallets.
* **Alipay**: Trusted payment service for Chinese buyers.
* **WeChat Pay**: Integrated WeChat mobile payment QR code flow.
* **SSLCommerz**: Highly trusted payment gateway for Bangladesh, supporting cards, mobile banking, and netbanking.
* **Manual Payment Methods**: Fully configurable custom instruction flows for **Direct Bank Transfer**, **Cheque Payments**, **Cash on Delivery**, and custom **Manual Payments**.

### 🔒 Secure Digital Locker & Downloads
* **Cryptographically Signed Links**: Dynamically generated secure download links signed via SHA-256 HMAC (salted with WP auth salts) with user-defined expiry windows.
* **Direct Protected File Delivery**: Prevents resource exposure by streaming files securely using chunked read buffers without revealing actual server paths.
* **Customer Downloads Portal**: Shortcode (`[omnify_customer_downloads]`) mounting a tabbed account dashboard for file access and shipping/billing address book management.
* **Bandwidth & Attempt Logs**: Tracks all download transactions, IP addresses, download states, and server bandwidth consumption.

### ⚙️ Modern Admin Dashboard & Data Management
* **Unified & Modern Listing UI**: Clean lists across Products, Orders, Customers, Coupons, Abandoned Carts, and Reviews with shared layouts.
* **Trash / Soft Delete**: Safely trash records across all main entities. Restore or permanently delete them via dedicated Trash status tabs.
* **Advanced Filter Panel**: Persistent filtering (via URL query params) with status pills, search, categories, dates, and sorting options.
* **Coupons Engine Rules**: Promotional code management supporting percentage/fixed discounts, usage limits, expiration dates, and free shipping overrides.
* **Reviews Moderation System**: Moderation portal to approve, unapprove, edit, or trash customer reviews and ratings.
* **Abandoned Cart Recovery**: Captures incomplete checkouts in the dashboard with tools to dispatch manual recovery emails containing checkout restore links.
* **Customer CRM Timelines**: Individual customer detail timelines auditing actions like `checkout_started`, `wishlist_added`, and support notes.
* **Inventory Logs & Stock Control**: Stock validation comparison on checkout, maximum purchase limits per checkout session, and automated restocking on returns/cancellation.
* **Gateway API Refunds & Access Revocations**: Automated API refund calls which trigger automatic revocation of corresponding digital file entitlements.
---

## 📚 Complete Developer Documentation

Explore interactive guides, repository SDK references, database schemas, and step-by-step developer recipes on our official documentation portal:

👉 **[https://omnifywp.com/doc/](https://omnifywp.com/doc/)**

* **Interactive REST API Sandbox**: Test public and protected endpoints with live mock responses.
* **18 SQL Tables & Relational Schema**: Normalized schema reference avoiding `wp_postmeta` latency.
* **Repositories SDK**: Clean object-oriented queries for products, orders, customers, and downloads.
* **Filter Hooks & Developer Recipes**: Ready-to-use production recipes for custom checkout fields, Discord/Slack webhooks, Next.js 14 headless catalogs, and software licensing.

---

## 📡 REST API Reference

The plugin exposes routes under the `/wp-json/omnify/v1` namespace.

### Storefront Public Endpoints (Headless Ready)

| Route | Method | Description |
| :--- | :---: | :--- |
| `/status` | `GET` | Get plugin status and database migration details. |
| `/checkout` | `POST` | Submits checkout details. |
| `/checkout/validate-coupon` | `POST` | Validates coupon discount. |
| `/checkout/payment-methods` | `GET` | Get configured payment methods. |
| `/cart` | `GET` \| `POST` \| `DELETE` | Read, save, or clear customer cart. |
| `/products/public` | `GET` | Search and retrieve published storefront products. |
| `/products/public/slug/{slug}` | `GET` | Retrieve a published product details by URL slug. |
| `/products/public/sku/{sku}` | `GET` | Retrieve a published product details by SKU. |

### Administrative Protected Endpoints

| Route | Method | Description |
| :--- | :---: | :--- |
| `/settings` | `GET` \| `POST` | Retrieve or save global settings. |
| `/activity-logs` | `GET` \| `DELETE` | Retrieve or clear activity logs. |
| `/products` | `GET` \| `POST` | List all catalog items or create a product. |
| `/products/{id}` | `GET` \| `PUT` \| `DELETE` | Retrieve, update, or delete a product. |
| `/products/{id}/trash` \| `/restore` | `POST` | Soft-delete or recover a product. |
| `/orders` | `GET` | List orders (supports status, search, and sort filters). |
| `/orders/{id}` | `GET` | Retrieve details of a single order. |
| `/orders/{id}/history` | `GET` | Retrieve the notes and status history audit trail. |
| `/orders/{id}/trash` \| `/restore` | `POST` | Soft-delete or recover an order. |
| `/customers` | `GET` \| `POST` | List or register customers. |
| `/customers/{id}/notes` | `GET` \| `POST` \| `DELETE` | Manage notes attached to a customer. |
| `/customers/{id}/activity` | `GET` | Fetch customer timeline events. |
| `/access` \| `/access/revoke` | `GET` \| `POST` | Grant or revoke customer product access. |

---

## 🔌 Developer Extensibility

Omnify was built from the ground up for developers. You can extend its queries, filter data structures, hook into events, and resolve classes from its internal container.

### Core Extensibility Hooks
* **`omnify_settings_saved`**: Action fired after settings section update. Passes `$section` (string) and `$posted` (array).
* **`omnify_before_checkout_validation`**: Action fired before checkout validation starts. Passes `$data` (array), `$settings` (array), `$items` (array).
* **`omnify_after_checkout_validation`**: Filter to inject custom validation logic. Passes `null|WP_Error $error`, `$data`, `$settings`, `$items`. Returning a `WP_Error` halts checkout.
* **`omnify_format_price`**: Filter to intercept price strings. Passes `$formatted_price` (string), `$amount` (float), `$settings` (array).

### Resolving Dependencies
Fetch any repository or service class directly from the main plugin container:
```php
$container = omnify()->container();
$product_repo = $container->get(\Omnify\eCommerce\Repositories\Product_Repository::class);
$products = $product_repo->all();
```

---

## 🌐 External Services

Omnify integrates with third-party payment gateways, analytics services, and external APIs to process transactions, handle webhooks, measure conversions, and display customer avatars. These services are optional and only connect when configured by the site administrator or chosen by the customer at checkout:

* **PayPal Commerce & REST API**
  * **What it is and what it is used for**: Processes customer payments (Express Checkout, card payments, smart buttons, order capture, refunds) and verifies webhook/IPN notifications.
  * **What data is sent and when**: When a customer chooses PayPal during checkout or an admin issues a PayPal refund, order items, transaction amounts, currency, customer email, billing details, invoice references, and return/cancel URLs are sent to PayPal's REST API endpoints via `wp_remote_post()` and `wp_remote_request()`. Endpoints accessed include OAuth token authentication (`https://api-m.paypal.com/v1/oauth2/token` or sandbox), order creation and capture (`https://api-m.paypal.com/v2/checkout/orders`), refund processing (`https://api-m.paypal.com/v2/payments/captures/{id}/refund`), and webhook verification (`https://api-m.paypal.com/v1/notifications/verify-webhook-signature`).
  * **Terms of Service**: [PayPal User Agreement](https://www.paypal.com/us/legalhub/useragreement-full)
  * **Privacy Policy**: [PayPal Privacy Statement](https://www.paypal.com/us/legalhub/privacy-full)

* **Stripe**
  * **What it is and what it is used for**: Processes credit/debit cards, Apple Pay, Google Pay, Alipay, and Stripe Checkout sessions, as well as webhook event verifications and refunds.
  * **What data is sent and when**: When a customer enters payment information or selects Stripe at checkout, order totals, currency, customer name, email address, payment method tokens, and order line items are transmitted to Stripe's API (`api.stripe.com`).
  * [Terms of Service](https://stripe.com/legal/consumer) | [Privacy Policy](https://stripe.com/privacy)

* **Razorpay**
  * **What it is and what it is used for**: Processes payments via UPI, netbanking, cards, and wallets for India-based transactions, verifies payment signatures, and processes refunds.
  * **What data is sent and when**: When a customer selects Razorpay at checkout, order amounts, currency, receipt identifiers, customer name, email, and phone number are sent to Razorpay (`api.razorpay.com`).
  * [Terms of Service](https://razorpay.com/terms/) | [Privacy Policy](https://razorpay.com/privacy/)

* **Alipay**
  * **What it is and what it is used for**: Generates Alipay payment orders, verifies digital signatures, and processes customer transactions for Alipay users.
  * **What data is sent and when**: When a customer selects Alipay at checkout, order numbers, subject descriptions, currency, total amounts, and merchant parameters are sent to Alipay gateway endpoints (`openapi.alipay.com` or sandbox).
  * [Terms of Service](https://render.alipay.com/p/f/agreementpages/alipayterms.html) | [Privacy Policy](https://render.alipay.com/p/f/agreementpages/alipayprivacy.html)

* **WeChat Pay**
  * **What it is and what it is used for**: Creates WeChat Pay unified orders and QR codes, processes mobile payments, and handles payment notifications.
  * **What data is sent and when**: When a customer selects WeChat Pay at checkout, order IDs, total fees, product descriptions, customer IP address, and transaction metadata are sent to WeChat Pay API (`api.mch.weixin.qq.com`).
  * [Terms of Service](https://www.wechat.com/en/service_terms.html) | [Privacy Policy](https://www.wechat.com/en/privacy_policy.html)

* **SSLCommerz**
  * **What it is and what it is used for**: Processes cards, mobile banking, and internet banking for South Asian transactions, and validates IPN transaction sessions.
  * **What data is sent and when**: When a customer chooses SSLCommerz at checkout, customer name, email, phone, billing address, order ID, currency, and total amount are sent to SSLCommerz (`sslcommerz.com` or sandbox).
  * [Terms of Service](https://sslcommerz.com/terms-and-conditions/) | [Privacy Policy](https://sslcommerz.com/privacy-policy/)

* **Gravatar**
  * **What it is and what it is used for**: Displays customer and reviewer avatars in the admin dashboard and testimonials.
  * **What data is sent and when**: An MD5 hash of the customer's email address is sent to Gravatar (`secure.gravatar.com`) when displaying user avatars.
  * [Terms of Service](https://automattic.com/tos/) | [Privacy Policy](https://automattic.com/privacy/)

* **Google Analytics 4 / Google Tag Manager** (Optional conversion tracking)
  * **What it is and what it is used for**: Measures storefront traffic, page views, and eCommerce conversion events when the merchant enables GA4 tracking in Omnify settings and provides a Measurement ID.
  * **What data is sent and when**: When enabled by the admin, the visitor's browser loads Google Tag Manager scripts from `googletagmanager.com` and sends page views, purchase events, and browser/device metadata. No scripts are loaded if disabled.
  * [Terms of Service](https://policies.google.com/terms) | [Privacy Policy](https://policies.google.com/privacy)

* **Meta Pixel (Facebook)** (Optional conversion tracking)
  * **What it is and what it is used for**: Tracks page views and purchase conversion events for advertising and analytics when the merchant enables Meta Pixel tracking in Omnify settings and provides a Pixel ID.
  * **What data is sent and when**: When enabled by the admin, the visitor's browser loads Meta Pixel scripts from `connect.facebook.net` and sends page view and conversion signals to `facebook.com/tr`. No scripts are loaded if disabled.
  * [Terms of Service](https://www.facebook.com/legal/terms) | [Privacy Policy](https://www.facebook.com/privacy/policy/)

---

## 📄 License
Omnify is open-source software licensed under the GPL-2.0-or-later.