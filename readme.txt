=== OmnifyWP eCommerce — Fast Online Store, Digital Downloads & Global Payments ===
Contributors: omnifywp
Tags: ecommerce, digital-downloads, payment-gateway, online-store, checkout
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, high-converting eCommerce engine for WordPress. Sell digital downloads, physical goods, and variable products with global payment gateways.

== Description ==

**OmnifyWP eCommerce** is the modern, developer-first, and conversion-optimized eCommerce platform for WordPress. Designed for creators, merchants, agencies, and developers who are tired of bloated plugins and costly paid extensions, OmnifyWP gives you everything you need to build, launch, and scale a profitable online store in minutes.

Whether you're selling digital downloads (ebooks, software, audio, photography, courses, design assets), physical merchandise, product bundles, or variable items with multi-format pricing, OmnifyWP delivers a frictionless customer journey with instant storefront loading, frictionless 1-page checkout, and pre-integrated payment gateways for every continent.

> **🚀 Why Choose OmnifyWP eCommerce?**
> * **Zero Bloat & Blazing Fast**: Engineered with pure, optimized database queries and ultra-lightweight frontend assets (<35KB). Say goodbye to sluggish checkout experiences.
> * **Global & Regional Payments Included Free**: No expensive add-ons needed. Includes Stripe, PayPal, Paystack (Africa), Tap Payments (Middle East / GCC), Mollie (EU), Khalti & eSewa (Nepal), Razorpay (India), Alipay & WeChat Pay (China), SSLCommerz (Bangladesh), and manual methods out of the box.
> * **High-Converting 1-Page Checkout**: Designed to eliminate cart abandonment with built-in scarcity countdown timers, free-shipping threshold meters, country/state autocomplete, and guest checkout.
> * **Bulletproof Digital Locker**: Protect your digital assets with cryptographically signed HMAC-SHA256 download links, chunked streaming delivery, and a dedicated self-service Customer Portal.
> * **Developer-Friendly & Extensible**: 100% hook-driven architecture, customizable templates, and full REST API coverage for headless or custom frontends.

---

### 🌟 Perfect For Every Type of Online Business

* **Digital Creators & Artists**: Sell presets, digital art, music, 3D assets, fonts, and PDFs with secure expiring links and download attempt tracking.
* **Software Developers & SaaS**: Distribute apps, plugins, license files, and documentation with protected chunked file delivery.
* **Physical Stores & Boutiques**: Manage inventory, shipping zones, flat-rate shipping, local pickups, and country-specific tax rules with ease.
* **Global & Regional Merchants**: Accept credit cards, debit cards, mobile money, UPI, and local digital wallets across 100+ countries with zero additional gateway plugin fees.

---

== Features ==

### 🛍️ High-Converting Storefront & Cart Experience
* **Modern Storefront Catalog**: Clean, responsive product grid with instant search, category filtering, and customizable layout views.
* **Storefront AJAX Quick Add**: Let customers add items instantly to their cart with a single click, animated micro-feedback, and live header badge updates.
* **High-Impact Product Details**: Conversion-focused layout featuring responsive media galleries, trust badges, key feature highlights, and sticky purchase action panels.
* **Simple, Variable & Bundled Products**: Sell standard single items, multi-attribute variations (sizes, formats, licenses) with unique prices and SKUs, or bundle multiple digital products together into a single package.
* **Integrated Customer Wishlists**: Allow visitors to save favorite items for later, boosting return visits and customer lifetime value.
* **Cart Scarcity Countdown**: Urgency-driven countdown timer with session storage persistence that drives higher checkout completion rates.
* **Free Shipping Progress Meter**: Dynamic visual progress meter that motivates shoppers to add more items to unlock free delivery.

### ⚡ Frictionless 1-Page Checkout Engine
* **Single-Page Checkout**: Minimize cart drop-off with a streamlined checkout form requiring zero page reloads.
* **Country-First Dynamic Selectors**: Smart address selectors with automatic state/region filtering and saved address book autofill.
* **Flexible Account Creation**: Support guest checkouts, optional account creation, or automatic account provisioning.
* **Coupon & Discount Engine**: Create percentage-based or flat discount codes with usage limits, expiry dates, and minimum spend rules.
* **Automated Abandoned Cart Recovery**: Tracks abandoned carts and provides recovery links to win back lost sales automatically.

### 🔒 Enterprise-Grade Digital File Protection
* **Cryptographically Signed URLs**: Automatically generates HMAC-SHA256 authenticated links with customizable expiration windows and download attempt limits.
* **Secure Chunked Streaming**: Delivers files through memory-efficient streaming buffers that conceal your physical server paths and prevent hotlinking.
* **Self-Service Customer Portal**: Embed `[omnify_customer_downloads]` to give customers an organized dashboard to manage orders, download files, and update saved addresses.
* **Comprehensive Download Logs**: Audit IP addresses, user agents, download counts, and bandwidth usage directly in your admin dashboard.

### 💳 Built-In Global & Regional Payment Gateways
Accept payments globally without paying hundreds of dollars every year for third-party gateway addons:
* **Stripe**: Credit/debit cards, Apple Pay, Google Pay, and Stripe Checkout.
* **PayPal Commerce**: PayPal Express Checkout, credit/debit cards, and smart pay buttons.
* **Paystack (Africa)**: Market-leading payment infrastructure for Nigeria, Ghana, Kenya, and South Africa supporting cards, Mobile Money (M-Pesa, MTN, Airtel), EFT bank transfers, and USSD.
* **Tap Payments (Middle East & GCC)**: Full coverage across Saudi Arabia, UAE, Kuwait, Bahrain, Qatar, Oman, Egypt, and Jordan with mada, KNET, Benefit, NAPS, and cards.
* **Mollie (European Union)**: Seamless EU commerce supporting iDEAL, Bancontact, SEPA Direct Debit / Bank Transfer, Klarna, and cards.
* **Khalti (Nepal)**: Nepal's popular digital wallet and mobile banking via the official ePayment v2 API.
* **eSewa (Nepal)**: Nepal's pioneer digital payment wallet with HMAC-SHA256 signature verification.
* **Razorpay (India)**: Industry standard for India supporting UPI, QR codes, cards, netbanking, and mobile wallets.
* **Alipay & WeChat Pay**: Comprehensive Chinese cross-border mobile commerce with QR code checkout.
* **SSLCommerz (Bangladesh)**: Top gateway for Bangladesh supporting cards, mobile banking (bKash, Nagad, Rocket), and internet banking.
* **Offline & Manual Payments**: Configurable instructions for Direct Bank Transfer (BACS), Cheque Payments, Cash on Delivery (COD), and custom manual methods.
* **One-Click Refunds**: Issue full or partial refunds directly from the WordPress order management screen.

### 📊 Marketing, Taxes & Shipping
* **Country-Specific Tax Rules**: Multi-tier tax tables with inclusive/exclusive pricing toggles, shipping tax options, and reporting codes.
* **Custom Shipping & Delivery Zones**: Define country- and region-based shipping rates, flat fees, and free shipping thresholds.
* **Google Analytics 4 & Meta Pixel Tracking**: Built-in conversion tracking for purchase events, item revenue, order IDs, and cart behavior.
* **Automated HTML Email Notifications**: Professional order confirmations, payment receipts, signed download access emails, and admin alerts.

### 🔌 Developer-First Architecture & REST API
* **Public Headless REST Endpoints**: Access products, categories, reviews, and storefront configuration via `/wp-json/omnify/v1/`.
* **Clean Code & Extensibility**: Comprehensive action and filter hooks (`omnify_before_checkout_validation`, `omnify_settings_saved`, `omnify_format_price`) to customize every aspect of your store.
* **Native WordPress Standards**: Fully localized with strict capability checks, nonce security, and sanitized database queries.

== External Services ==

This plugin integrates with third-party payment gateways, analytics services, and external APIs to process transactions, handle webhooks, measure conversions, and manage customer avatars. These services are optional and only connect when configured by the site administrator or chosen by the customer at checkout.

* **PayPal Commerce & REST API**
  * What it is and what it is used for: Processes customer payments (Express Checkout, card payments, smart buttons, order capture, refunds) and verifies webhook/IPN notifications.
  * What data is sent and when: When a customer chooses PayPal during checkout or an admin issues a PayPal refund, order items, transaction amounts, currency, customer email, billing details, invoice references, and return/cancel URLs are sent to PayPal's REST API endpoints via `wp_remote_post()` and `wp_remote_request()`. Endpoints accessed include OAuth token authentication (`https://api-m.paypal.com/v1/oauth2/token` or sandbox), order creation and capture (`https://api-m.paypal.com/v2/checkout/orders`), refund processing (`https://api-m.paypal.com/v2/payments/captures/{id}/refund`), and webhook verification (`https://api-m.paypal.com/v1/notifications/verify-webhook-signature`).
  * Terms of Service: https://www.paypal.com/us/legalhub/useragreement-full
  * Privacy Policy: https://www.paypal.com/us/legalhub/privacy-full

* **Stripe**
  * What it is and what it is used for: Processes credit/debit cards, Apple Pay, Google Pay, Alipay, and Stripe Checkout sessions, as well as webhook event verifications and refunds.
  * What data is sent and when: When a customer enters payment information or selects Stripe at checkout, order totals, currency, customer name, email address, payment method tokens, and order line items are transmitted to Stripe's API (`api.stripe.com`).
  * Terms of Service: https://stripe.com/legal/consumer
  * Privacy Policy: https://stripe.com/privacy

* **Razorpay**
  * What it is and what it is used for: Processes payments via UPI, netbanking, cards, and wallets for India-based transactions, verifies payment signatures, and processes refunds.
  * What data is sent and when: When a customer selects Razorpay at checkout, order amounts, currency, receipt identifiers, customer name, email, and phone number are sent to Razorpay (`api.razorpay.com`).
  * Terms of Service: https://razorpay.com/terms/
  * Privacy Policy: https://razorpay.com/privacy/

* **Alipay**
  * What it is and what it is used for: Generates Alipay payment orders, verifies digital signatures, and processes customer transactions for Alipay users.
  * What data is sent and when: When a customer selects Alipay at checkout, order numbers, subject descriptions, currency, total amounts, and merchant parameters are sent to Alipay gateway endpoints (`openapi.alipay.com` or sandbox).
  * Terms of Service: https://render.alipay.com/p/f/agreementpages/alipayterms.html
  * Privacy Policy: https://render.alipay.com/p/f/agreementpages/alipayprivacy.html

* **WeChat Pay**
  * What it is and what it is used for: Creates WeChat Pay unified orders and QR codes, processes mobile payments, and handles payment notifications.
  * What data is sent and when: When a customer selects WeChat Pay at checkout, order IDs, total fees, product descriptions, customer IP address, and transaction metadata are sent to WeChat Pay API (`api.mch.weixin.qq.com`).
  * Terms of Service: https://www.wechat.com/en/service_terms.html
  * Privacy Policy: https://www.wechat.com/en/privacy_policy.html

* **SSLCommerz**
  * What it is and what it is used for: Processes cards, mobile banking, and internet banking for South Asian transactions, and validates IPN transaction sessions.
  * What data is sent and when: When a customer chooses SSLCommerz at checkout, customer name, email, phone, billing address, order ID, currency, and total amount are sent to SSLCommerz (`sslcommerz.com` or sandbox).
  * Terms of Service: https://sslcommerz.com/terms-and-conditions/
  * Privacy Policy: https://sslcommerz.com/privacy-policy/

* **Paystack**
  * What it is and what it is used for: Processes online payments (cards, bank transfer, mobile money, USSD) across African markets, verifies transactions, and handles webhooks and refunds.
  * What data is sent and when: When a customer selects Paystack at checkout or an admin issues a refund, customer email, order reference, amount, currency, and order metadata are sent to Paystack API (`api.paystack.co`).
  * Terms of Service: https://paystack.com/terms
  * Privacy Policy: https://paystack.com/privacy

* **Tap Payments**
  * What it is and what it is used for: Processes card payments and regional debit schemes (mada, KNET, Benefit, NAPS) in the Middle East and GCC region, verifies charges, and processes refunds.
  * What data is sent and when: When a customer selects Tap Payments at checkout, customer name, email, phone number, order total, currency, and redirect URLs are sent to Tap Payments API (`api.tap.company`).
  * Terms of Service: https://www.tap.company/terms
  * Privacy Policy: https://www.tap.company/privacy

* **Mollie**
  * What it is and what it is used for: Processes online payment methods across the European Union (iDEAL, Bancontact, SEPA, Klarna, cards), verifies payment states, and handles refunds.
  * What data is sent and when: When a customer selects Mollie at checkout, order totals, currency, order descriptions, redirect and webhook URLs are sent to Mollie API (`api.mollie.com`).
  * Terms of Service: https://www.mollie.com/user-agreement
  * Privacy Policy: https://www.mollie.com/privacy

* **Khalti**
  * What it is and what it is used for: Processes digital wallet transactions, mobile banking, and ConnectIPS payments in Nepal, initiates payments, and performs payment verification lookups.
  * What data is sent and when: When a customer selects Khalti at checkout, customer name, email, phone, order amount, and purchase order identifiers are sent to Khalti API (`khalti.com` or sandbox).
  * Terms of Service: https://khalti.com/info/terms/
  * Privacy Policy: https://khalti.com/info/privacy/

* **eSewa**
  * What it is and what it is used for: Processes digital wallet transactions in Nepal through eSewa's ePay v2 service with cryptographic signature verification and status inquiries.
  * What data is sent and when: When a customer selects eSewa at checkout, total amount, transaction UUID, product code, and HMAC-SHA256 signature are sent to eSewa payment gateway (`epay.esewa.com.np` or test environment).
  * Terms of Service: https://esewa.com.np/common/termsAndConditions
  * Privacy Policy: https://esewa.com.np/common/privacyPolicy

* **Gravatar**
  * What it is and what it is used for: Displays customer and reviewer avatars in the admin dashboard and testimonials.
  * What data is sent and when: An MD5 hash of the customer's email address is sent to Gravatar (`secure.gravatar.com`) when displaying user avatars.
  * Terms of Service: https://automattic.com/tos/
  * Privacy Policy: https://automattic.com/privacy/

* **Google Analytics 4 / Google Tag Manager** (Optional conversion tracking)
  * What it is and what it is used for: Measures storefront traffic, page views, and eCommerce conversion events when the merchant enables GA4 tracking in Omnify settings and provides a Measurement ID.
  * What data is sent and when: When enabled by the admin, the visitor's browser loads Google Tag Manager scripts from `googletagmanager.com` and sends page views, purchase events, and browser/device metadata. No scripts are loaded if disabled.
  * Terms of Service: https://policies.google.com/terms
  * Privacy Policy: https://policies.google.com/privacy

* **Meta Pixel (Facebook)** (Optional conversion tracking)
  * What it is and what it is used for: Tracks page views and purchase conversion events for advertising and analytics when the merchant enables Meta Pixel tracking in Omnify settings and provides a Pixel ID.
  * What data is sent and when: When enabled by the admin, the visitor's browser loads Meta Pixel scripts from `connect.facebook.net` and sends page view and conversion signals to `facebook.com/tr`. No scripts are loaded if disabled.
  * Terms of Service: https://www.facebook.com/legal/terms
  * Privacy Policy: https://www.facebook.com/privacy/policy/

== Installation ==

### Automatic Installation
1. Log in to your WordPress Dashboard.
2. Navigate to **Plugins > Add New**.
3. Search for **OmnifyWP eCommerce** and click **Install Now**.
4. Activate the plugin and follow the interactive Setup Wizard to create your default pages (Storefront, Cart, Checkout, Customer Portal) in under 60 seconds.

### Manual Installation
1. Download the plugin ZIP file.
2. In your WordPress dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Activate the plugin and configure your payment credentials under **Omnify > Settings > Payments**.

== Frequently Asked Questions ==

= What makes OmnifyWP different from WooCommerce or Easy Digital Downloads? =
OmnifyWP is built from the ground up for speed, simplicity, and conversion. Unlike traditional eCommerce plugins that require 15–20 paid extensions just to accept local payments, offer 1-page checkout, or protect downloads, OmnifyWP includes all essential features out of the box with zero database bloat and blazing fast load times (<35KB frontend script footprint).

= Can I sell both digital downloads and physical products? =
Yes! OmnifyWP seamlessly supports digital files (e-books, music, software, presets, PDFs), physical goods requiring shipping addresses, variable products with format/size attributes, and bundled products.

= Do I need to buy expensive addons for payment gateways? =
No. OmnifyWP includes 11 global and regional payment gateways for free: Stripe, PayPal, Paystack (Africa), Tap Payments (Middle East), Mollie (EU), Khalti & eSewa (Nepal), Razorpay (India), Alipay & WeChat Pay (China), and SSLCommerz (Bangladesh), plus offline methods (Direct Bank Transfer, Cash on Delivery, Cheques).

= How does digital download protection work? =
Files uploaded to Omnify are delivered through cryptographically signed HMAC-SHA256 URLs that expire after your chosen time limit or download count. Files are streamed in chunked memory buffers without ever exposing the real server path. Customers can also access their files at any time through the secure self-service Customer Portal.

= Does OmnifyWP support coupons and abandoned cart recovery? =
Yes! You can create percentage or fixed discount coupons with usage limits and minimum spend rules. OmnifyWP also captures abandoned cart tokens to help you recover lost sales.

= Is OmnifyWP compatible with my WordPress theme and page builders? =
Yes. OmnifyWP outputs clean, self-contained semantic HTML and scoped CSS that automatically blends with popular WordPress themes (Astra, GeneratePress, Kadence, OceanWP, Hello Elementor, Divi) and Gutenberg Block Editor.

= Can I issue customer refunds? =
Yes. OmnifyWP provides one-click refund processing directly from the order details screen for supported gateways (Stripe, PayPal, Paystack, Tap Payments, Mollie, Khalti, eSewa, Razorpay, SSLCommerz).

== Screenshots ==

1. Modern storefront catalog grid with category filters, instant search, and AJAX Quick Add.
2. Product details layout featuring responsive media gallery, format swatches, and sticky purchase card.
3. High-converting 1-page checkout with cart countdown timer, free shipping progress meter, and global payment options.
4. Self-service Customer Portal with secure file access and address management.
5. Intuitive admin settings panel with regional payment gateway configuration and live/test mode toggles.
6. Order management dashboard with one-click refund handling and download audit logs.

== Changelog ==

= 1.2.0 =
* Critical Bug Fix: Fixed purchase verification blocking buyers of physical products from submitting reviews. Reviews now verify completed orders in addition to digital access entitlements.
* Critical Bug Fix: Resolved false 403 Forbidden errors on digital downloads in Windows server environments by normalizing file paths with wp_normalize_path().
* Performance & Stability: Replaced memory-buffering file streaming with 8KB chunked streaming and buffer flushes, preventing memory exhaustion when delivering large digital downloads.
* Database & Query Optimization: Added compound index on order_items (order_id, product_id) and migrated customer_visible flag on customer notes for fast customer portal lookups and data privacy.
* Feature: Added public guest order tracking shortcode [omnify_order_tracking] for customer order status lookup and invoice access without requiring account login.
* Gateways: Integrated live API automated refund execution for all supported regional gateways (Paystack, Tap Payments, Mollie, Khalti, and eSewa).
* Storefront Design: Seamlessly unified the Customer Portal design with the Admin UI system (matching KPI metrics, typography, tabs, buttons, forms, and cards).
* Security & Compliance: Streamlined checkout authentication to meet WordPress.org submission guidelines; replaced raw inline password form with secure WordPress login routing.
* Update: Bumped plugin release to version 1.2.0.

= 1.1.0 =
* Feature: Added Paystack gateway integration for African markets supporting cards, mobile money, bank transfer, and USSD.
* Feature: Added Tap Payments gateway integration for Middle East / GCC supporting mada, KNET, Benefit, NAPS, and cards.
* Feature: Added Mollie gateway integration for European Union supporting iDEAL, Bancontact, SEPA, and cards.
* Feature: Added Khalti Digital Wallet integration for Nepal supporting ePayment v2 API and status lookups.
* Feature: Added eSewa ePay v2 integration for Nepal with cryptographic HMAC-SHA256 signature verification.
* Feature: Added full admin settings panels for all 5 regional gateways with live/test mode switches and webhook URL displays.
* Feature: Added automated refund processing support for Paystack, Tap, Mollie, Khalti, and eSewa via Omnify Payment Gateway Service.
* Feature: Added storefront checkout payment selection, automatic redirection, and transaction verification handlers for all new regional gateways.
* Update: Updated plugin version to 1.1.0 and refreshed documentation.

= 1.0.0 =
* Initial release.