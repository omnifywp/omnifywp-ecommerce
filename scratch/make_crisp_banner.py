import base64

def b64(path):
    with open(path, 'rb') as f:
        return 'data:image/png;base64,' + base64.b64encode(f.read()).decode('utf-8')

sneaker_b64 = b64('scratch/parts/sneaker_teal_trans.png')
backpack_b64 = b64('scratch/parts/backpack.png')
bottle_b64 = b64('scratch/parts/bottle_teal.png')
headphones_b64 = b64('scratch/parts/headphones.png')
hoodie_b64 = b64('scratch/parts/hoodie.png')
icon_b64 = b64('assets/icon-256x256.png')

html = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OmnifyWP Crisp Banner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;850;900&display=swap" rel="stylesheet">
<style>
  * {{ box-sizing: border-box; margin: 0; padding: 0; }}
  body {{
    width: 1544px;
    height: 500px;
    overflow: hidden;
    background: #FFFFFF;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    position: relative;
  }}

  /* Ambient glow */
  .ambient-1 {{
    position: absolute;
    width: 600px;
    height: 600px;
    left: -100px;
    top: -200px;
    background: radial-gradient(circle, rgba(15, 118, 110, 0.04) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
  }}
  .ambient-2 {{
    position: absolute;
    width: 750px;
    height: 750px;
    right: 40px;
    top: -160px;
    background: radial-gradient(circle, rgba(15, 118, 110, 0.05) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
  }}

  /* Left Side */
  .left-col {{
    position: absolute;
    left: 56px;
    top: 50px;
    bottom: 46px;
    width: 660px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 20;
  }}

  .brand-row {{
    display: flex;
    align-items: center;
    gap: 14px;
  }}
  .brand-logo-box {{
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #093725 0%, #031c12 100%);
    border: 1.2px solid rgba(52, 211, 153, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(9, 55, 37, 0.28);
    flex-shrink: 0;
  }}
  .brand-logo-box img {{
    width: 28px;
    height: 28px;
    border-radius: 6px;
  }}
  .brand-title-wrap {{
    display: flex;
    flex-direction: column;
  }}
  .brand-title {{
    font-size: 24.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.025em;
  }}
  .brand-tagline {{
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    margin-top: 2px;
    letter-spacing: -0.01em;
  }}

  .headline-wrap {{
    margin: 8px 0;
  }}
  .headline-dark {{
    font-size: 64px;
    font-weight: 850;
    line-height: 1.02;
    letter-spacing: -0.04em;
    color: #0f172a;
  }}
  .headline-teal {{
    font-size: 64px;
    font-weight: 850;
    line-height: 1.02;
    letter-spacing: -0.04em;
    color: #0f766e;
  }}

  .subhead {{
    font-size: 19px;
    font-weight: 450;
    color: #475569;
    line-height: 1.45;
    max-width: 610px;
    letter-spacing: -0.015em;
  }}

  .badges-strip {{
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 4px;
  }}
  .badge-item {{
    display: flex;
    align-items: center;
    gap: 12px;
  }}
  .badge-icon {{
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #0f766e;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);
    flex-shrink: 0;
  }}
  .badge-icon svg {{
    width: 22px;
    height: 22px;
    fill: #FFFFFF;
  }}
  .badge-text {{
    display: flex;
    flex-direction: column;
  }}
  .badge-text-top {{
    font-size: 16.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    letter-spacing: -0.02em;
    white-space: nowrap;
  }}
  .badge-text-sub {{
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    line-height: 1.2;
    letter-spacing: -0.01em;
    white-space: nowrap;
  }}
  .badge-sep {{
    width: 1px;
    height: 38px;
    background: #e2e8f0;
    flex-shrink: 0;
  }}

  /* Right Side Mockup Scene */
  .scene-wrap {{
    position: absolute;
    right: 28px;
    top: 36px;
    width: 775px;
    height: 436px;
    z-index: 10;
  }}

  /* Browser Mockup */
  .browser-card {{
    position: absolute;
    left: 45px;
    top: 8px;
    width: 535px;
    height: 412px;
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 48px -10px rgba(15, 23, 42, 0.11), 0 0 0 1px rgba(226, 232, 240, 0.7);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }}
  .browser-top {{
    height: 34px;
    background: #FFFFFF;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    padding: 0 14px;
    gap: 6px;
  }}
  .dot {{
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }}
  .dot-red {{ background: #ef4444; }}
  .dot-yellow {{ background: #f59e0b; }}
  .dot-green {{ background: #10b981; }}

  .store-nav {{
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    border-bottom: 1px solid #f8fafc;
  }}
  .store-logo {{
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
  }}
  .store-links {{
    display: flex;
    align-items: center;
    gap: 15px;
  }}
  .store-link {{
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
    text-decoration: none;
  }}
  .store-actions {{
    display: flex;
    align-items: center;
    gap: 12px;
  }}
  .store-actions svg {{
    width: 15px;
    height: 15px;
    fill: #334155;
  }}
  .cart-badge {{
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #0f766e;
    color: #ffffff;
    font-size: 8.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: -6px;
    margin-top: -8px;
  }}

  /* Hero Banner */
  .store-hero {{
    margin: 12px 18px 0;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #edf2f7;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    height: 175px;
    overflow: hidden;
  }}
  .hero-left {{
    z-index: 2;
    max-width: 220px;
  }}
  .hero-pill {{
    display: inline-block;
    font-size: 8px;
    font-weight: 750;
    letter-spacing: 0.04em;
    color: #0f766e;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 9999px;
    padding: 2.5px 7px;
    margin-bottom: 6px;
  }}
  .hero-title {{
    font-size: 23px;
    font-weight: 850;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.03em;
    margin-bottom: 5px;
  }}
  .hero-desc {{
    font-size: 9.5px;
    font-weight: 450;
    color: #64748b;
    line-height: 1.35;
    margin-bottom: 10px;
  }}
  .hero-btn {{
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #0f766e;
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(15, 118, 110, 0.22);
  }}
  .hero-right {{
    position: absolute;
    right: 14px;
    top: 10px;
    bottom: 10px;
    width: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
  }}
  .hero-right img {{
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.14));
  }}

  /* Shelf Items */
  .store-shelf {{
    margin: 14px 18px 0;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
  }}
  .shelf-card {{
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }}
  .shelf-card-img {{
    width: 44px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
  }}
  .shelf-card-img img {{
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }}
  .shelf-bar {{
    width: 32px;
    height: 5px;
    background: #e2e8f0;
    border-radius: 9999px;
  }}

  /* Floating Sales Card */
  .sales-card {{
    position: absolute;
    left: -8px;
    top: 232px;
    width: 168px;
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.13), 0 0 0 1px rgba(226, 232, 240, 0.6);
    padding: 13px 15px;
    z-index: 30;
  }}
  .sales-lbl {{
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
  }}
  .sales-val {{
    font-size: 23px;
    font-weight: 850;
    color: #0f172a;
    letter-spacing: -0.03em;
    margin: 3px 0 6px;
  }}
  .sales-growth {{
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 11.5px;
    font-weight: 700;
    color: #059669;
  }}
  .sales-chart {{
    width: 100%;
    height: 34px;
    margin-top: 4px;
  }}

  /* Floating Checkout Card */
  .checkout-card {{
    position: absolute;
    right: 0px;
    top: 50px;
    width: 242px;
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 24px 55px -12px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(226, 232, 240, 0.8);
    padding: 16px 16px 18px;
    z-index: 40;
  }}
  .checkout-hdr {{
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
  }}
  .checkout-hdr-left {{
    display: flex;
    align-items: center;
    gap: 8px;
  }}
  .checkout-lock-box {{
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
  }}
  .checkout-lock-box svg {{
    width: 13px;
    height: 13px;
    fill: #059669;
  }}
  .checkout-hdr-title {{
    font-size: 13.5px;
    font-weight: 750;
    color: #0f172a;
    letter-spacing: -0.015em;
  }}
  .checkout-dots {{
    font-size: 15px;
    color: #94a3b8;
    line-height: 1;
    letter-spacing: 2px;
  }}

  /* Input Skeleton line */
  .checkout-step {{
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
  }}
  .checkout-step-bar {{
    flex: 1;
    height: 7px;
    background: #f1f5f9;
    border-radius: 9999px;
  }}
  .checkout-step-chk {{
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #0f766e;
    display: flex;
    align-items: center;
    justify-content: center;
  }}
  .checkout-step-chk svg {{
    width: 9px;
    height: 9px;
    fill: #FFFFFF;
  }}

  /* Gateways 4x3 Grid */
  .gateways-grid {{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 14px;
  }}
  .gw-badge {{
    height: 29px;
    border-radius: 6px;
    border: 1px solid #edf2f7;
    background: #fcfdfe;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
  }}
  .gw-badge svg, .gw-badge img {{
    max-height: 16px;
    max-width: 90%;
  }}

  .btn-pay {{
    width: 100%;
    height: 36px;
    background: #0f766e;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.28);
    letter-spacing: -0.01em;
  }}
</style>
</head>
<body>
  <div class="ambient-1"></div>
  <div class="ambient-2"></div>

  <!-- Left Container -->
  <div class="left-col">
    <!-- Brand Header -->
    <div class="brand-row">
      <div class="brand-logo-box">
        <img src="{icon_b64}" alt="Omnify">
      </div>
      <div class="brand-title-wrap">
        <div class="brand-title">OmnifyWP</div>
        <div class="brand-tagline">The High-Speed Autonomous eCommerce Engine</div>
      </div>
    </div>

    <!-- Main Headline -->
    <div class="headline-wrap">
      <div class="headline-dark">Modern eCommerce</div>
      <div class="headline-teal">Without the Bloat.</div>
    </div>

    <!-- Subtitle -->
    <div class="subhead">
      Fast online stores, digital downloads and global payments &mdash; everything you need, out of the box.
    </div>

    <!-- Feature Badges -->
    <div class="badges-strip">
      <div class="badge-item">
        <div class="badge-icon">
          <svg viewBox="0 0 24 24"><path d="M13 2L3 14h7v8l10-12h-7z"/></svg>
        </div>
        <div class="badge-text">
          <span class="badge-text-top">11 Built-in</span>
          <span class="badge-text-sub">Payment Gateways</span>
        </div>
      </div>
      <div class="badge-sep"></div>
      <div class="badge-item">
        <div class="badge-icon">
          <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </div>
        <div class="badge-text">
          <span class="badge-text-top">1-Page</span>
          <span class="badge-text-sub">Checkout</span>
        </div>
      </div>
      <div class="badge-sep"></div>
      <div class="badge-item">
        <div class="badge-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        </div>
        <div class="badge-text">
          <span class="badge-text-top">Secure</span>
          <span class="badge-text-sub">Digital Downloads</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Side Mockup Scene -->
  <div class="scene-wrap">
    <!-- Browser Window -->
    <div class="browser-card">
      <div class="browser-top">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
      </div>
      <div class="store-nav">
        <span class="store-logo">Your Store</span>
        <div class="store-links">
          <span class="store-link">Home</span>
          <span class="store-link">Shop</span>
          <span class="store-link">About</span>
          <span class="store-link">Contact</span>
        </div>
        <div class="store-actions">
          <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
          <div style="position: relative; display: flex; align-items: center;">
            <svg viewBox="0 0 24 24"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12z"/></svg>
            <span class="cart-badge">2</span>
          </div>
        </div>
      </div>

      <!-- Hero Card inside Browser -->
      <div class="store-hero">
        <div class="hero-left">
          <span class="hero-pill">NEW COLLECTION</span>
          <div class="hero-title">Premium<br>Sneakers</div>
          <div class="hero-desc">Modern design. Everyday comfort.<br>Built for what's next.</div>
          <span class="hero-btn">Shop Now &rarr;</span>
        </div>
        <div class="hero-right">
          <img src="{sneaker_b64}" alt="Sneakers">
        </div>
      </div>

      <!-- Shelf Items -->
      <div class="store-shelf">
        <div class="shelf-card">
          <div class="shelf-card-img"><img src="{backpack_b64}" alt="Backpack"></div>
          <div class="shelf-bar"></div>
        </div>
        <div class="shelf-card">
          <div class="shelf-card-img"><img src="{bottle_b64}" alt="Bottle"></div>
          <div class="shelf-bar"></div>
        </div>
        <div class="shelf-card">
          <div class="shelf-card-img"><img src="{headphones_b64}" alt="Headphones"></div>
          <div class="shelf-bar"></div>
        </div>
        <div class="shelf-card">
          <div class="shelf-card-img"><img src="{hoodie_b64}" alt="Hoodie"></div>
          <div class="shelf-bar"></div>
        </div>
      </div>
    </div>

    <!-- Floating Total Sales Card -->
    <div class="sales-card">
      <div class="sales-lbl">Total Sales</div>
      <div class="sales-val">$12,458</div>
      <div class="sales-growth">
        <span>&uarr; 24%</span>
      </div>
      <svg class="sales-chart" viewBox="0 0 135 34" fill="none">
        <defs>
          <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#10b981" stop-opacity="0.28"/>
            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
          </linearGradient>
        </defs>
        <path d="M2 28 C 30 26, 45 31, 65 24 C 85 17, 95 24, 110 14 C 122 6, 128 4, 133 3" stroke="#059669" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M2 28 C 30 26, 45 31, 65 24 C 85 17, 95 24, 110 14 C 122 6, 128 4, 133 3 L 133 34 L 2 34 Z" fill="url(#salesGrad)"/>
      </svg>
    </div>

    <!-- Floating Secure Checkout Card -->
    <div class="checkout-card">
      <div class="checkout-hdr">
        <div class="checkout-hdr-left">
          <div class="checkout-lock-box">
            <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
          </div>
          <span class="checkout-hdr-title">Secure Checkout</span>
        </div>
        <span class="checkout-dots">&bull;&bull;&bull;</span>
      </div>

      <div class="checkout-step">
        <div class="checkout-step-bar"></div>
        <div class="checkout-step-chk">
          <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        </div>
      </div>

      <!-- 12 Gateways Grid -->
      <div class="gateways-grid">
        <!-- 1. VISA -->
        <div class="gw-badge">
          <span style="font-size: 13px; font-weight: 900; font-style: italic; color: #1434cb; letter-spacing: -0.05em;">VISA</span>
        </div>
        <!-- 2. Mastercard -->
        <div class="gw-badge">
          <svg width="28" height="16" viewBox="0 0 36 22">
            <circle cx="13" cy="11" r="10" fill="#eb001b"/>
            <circle cx="23" cy="11" r="10" fill="#f79e1b" fill-opacity="0.88"/>
          </svg>
        </div>
        <!-- 3. PayPal -->
        <div class="gw-badge">
          <span style="font-size: 11.5px; font-weight: 800; font-style: italic;"><span style="color: #003087;">Pay</span><span style="color: #0079c1;">Pal</span></span>
        </div>
        <!-- 4. Stripe -->
        <div class="gw-badge">
          <span style="font-size: 13px; font-weight: 800; color: #635bff; letter-spacing: -0.03em;">stripe</span>
        </div>
        <!-- 5. Razorpay -->
        <div class="gw-badge">
          <span style="font-size: 10px; font-weight: 800; color: #0c2340;"><span style="color: #0284c7;">&#x25B6;</span>Razorpay</span>
        </div>
        <!-- 6. Paystack -->
        <div class="gw-badge">
          <span style="font-size: 10px; font-weight: 800; color: #00c3f7;">&equiv;<span style="color: #001f3f;">paystack</span></span>
        </div>
        <!-- 7. Mollie -->
        <div class="gw-badge">
          <span style="font-size: 11.5px; font-weight: 850; color: #000000; letter-spacing: -0.04em;">mollie</span>
        </div>
        <!-- 8. Tap -->
        <div class="gw-badge">
          <span style="font-size: 12px; font-weight: 800; color: #1e293b;">tap</span>
        </div>
        <!-- 9. SSLCommerz -->
        <div class="gw-badge">
          <span style="font-size: 8px; font-weight: 850; color: #ffffff; background: #0072ce; padding: 2px 4px; border-radius: 3px;">SSLCOMMERZ</span>
        </div>
        <!-- 10. eSewa -->
        <div class="gw-badge">
          <span style="font-size: 10px; font-weight: 800; color: #60bb46;"><span style="background:#60bb46;color:#fff;border-radius:50%;padding:0 3px;font-size:8px;margin-right:2px;">e</span>Sewa</span>
        </div>
        <!-- 11. Khalti -->
        <div class="gw-badge">
          <span style="font-size: 9.5px; font-weight: 800; color: #5c2d91;">&#x2B24; Khalti</span>
        </div>
        <!-- 12. WeChat -->
        <div class="gw-badge">
          <svg width="20" height="16" viewBox="0 0 24 24" fill="#07c160"><path d="M9.5 4C5.36 4 2 6.91 2 10.5c0 2.03 1.07 3.86 2.76 5.09L4 18.5l3.24-1.62c.71.21 1.47.32 2.26.32.22 0 .43-.01.64-.03-.39-.92-.64-1.94-.64-3.02 0-4.04 3.7-7.32 8.28-7.32.24 0 .47.01.7.03C17.43 5.37 13.78 4 9.5 4zm-2 3.5a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5zm5 0a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5zm5.28 4.65c-3.69 0-6.68 2.62-6.68 5.85 0 1.78.9 3.37 2.34 4.45L13 20.5l2.67-1.34c.66.21 1.37.34 2.11.34 3.69 0 6.68-2.62 6.68-5.85s-2.99-5.85-6.68-5.85zm-2.28 3.1a1 1 0 110 2 1 1 0 010-2zm4.5 0a1 1 0 110 2 1 1 0 010-2z"/></svg>
        </div>
      </div>

      <div class="btn-pay">
        <span>Pay Now &rarr;</span>
      </div>
    </div>
  </div>
</body>
</html>"""

with open('scratch/render_banner_crisp.html', 'w', encoding='utf-8') as f:
    f.write(html)

print('Updated scratch/render_banner_crisp.html successfully!')
