/**
 * OmnifyWP eCommerce — Native UI Mockups Generator
 * Generates realistic, responsive HTML/CSS UI mockups replacing static PNG screenshots.
 */

function wrapWindow(url, badge, contentHtml, caption) {
  return `
    <div class="ui-mockup-window">
      <div class="ui-mockup-header">
        <div class="ui-mockup-dots">
          <span class="dot dot-red"></span>
          <span class="dot dot-yellow"></span>
          <span class="dot dot-green"></span>
        </div>
        <div class="ui-mockup-address">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span>${url}</span>
        </div>
        <div class="ui-mockup-badge">${badge}</div>
      </div>
      <div class="ui-mockup-body">
        ${contentHtml}
      </div>
      ${caption ? `<div class="ui-mockup-caption">${caption}</div>` : ''}
    </div>
  `;
}

const mockups = {
  // Figure 2.1: Setup Wizard
  setup_wizard: wrapWindow(
    'wp-admin/admin.php?page=omnify-wizard',
    'Setup Wizard',
    `
      <div class="mockup-wizard">
        <div class="wizard-stepper">
          <div class="step completed">
            <span class="step-num">✓</span>
            <span class="step-title">Store Profile</span>
          </div>
          <div class="step-connector completed"></div>
          <div class="step active">
            <span class="step-num">2</span>
            <span class="step-title">Currency & Locale</span>
          </div>
          <div class="step-connector"></div>
          <div class="step">
            <span class="step-num">3</span>
            <span class="step-title">Payment Gateways</span>
          </div>
          <div class="step-connector"></div>
          <div class="step">
            <span class="step-num">4</span>
            <span class="step-title">Page Setup</span>
          </div>
        </div>

        <div class="wizard-card">
          <div class="wizard-card-header">
            <h4>Step 2: Configure Currency & Financial Formatting</h4>
            <p>Select your primary operational store currency and decimal display standards.</p>
          </div>
          <div class="mockup-form-grid">
            <div class="mockup-field">
              <label>Store Base Currency</label>
              <div class="mockup-input-select">United States Dollar (USD - $)</div>
            </div>
            <div class="mockup-field">
              <label>Currency Symbol Position</label>
              <div class="mockup-input-select">Left ($99.00)</div>
            </div>
            <div class="mockup-field">
              <label>Thousand Separator</label>
              <div class="mockup-input-text">,</div>
            </div>
            <div class="mockup-field">
              <label>Decimal Separator & Digits</label>
              <div class="mockup-input-text">. (2 Decimals)</div>
            </div>
          </div>
          <div class="wizard-actions">
            <span class="btn-mockup btn-outline">← Back</span>
            <span class="btn-mockup btn-primary">Continue to Payment Gateways →</span>
          </div>
        </div>
      </div>
    `,
    'Figure 2.1: OmnifyWP Setup Wizard guiding merchants through initial currency, payment, and page configuration.'
  ),

  // Figure 3.1: Dashboard Overview
  dashboard: wrapWindow(
    'wp-admin/admin.php?page=omnify-dashboard',
    'Store Dashboard',
    `
      <div class="mockup-dashboard">
        <div class="mockup-bar">
          <div>
            <h3 style="margin:0; font-size:1.15rem; font-weight:700;">Good morning, Administrator</h3>
            <span style="font-size:0.8rem; color:var(--text-dim);">Live Store Health: 18 SQL Tables Indexed • Last 30 Days</span>
          </div>
          <div class="mockup-pills">
            <span class="mockup-pill active">Last 30 Days</span>
            <span class="mockup-pill">This Quarter</span>
            <span class="mockup-pill">Year to Date</span>
          </div>
        </div>

        <div class="mockup-kpi-grid">
          <div class="kpi-box">
            <span class="kpi-label">Gross Revenue</span>
            <span class="kpi-val">$48,290.00</span>
            <span class="kpi-trend up">↑ +18.4% vs last mo</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Total Orders</span>
            <span class="kpi-val">1,248</span>
            <span class="kpi-trend up">↑ +12.1% completed</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Active Customers</span>
            <span class="kpi-val">892</span>
            <span class="kpi-trend up">↑ +9.5% repeat buyers</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Digital Downloads</span>
            <span class="kpi-val">3,410</span>
            <span class="kpi-trend up">↑ +24.0% verified streams</span>
          </div>
        </div>

        <div class="mockup-split-2">
          <div class="mockup-card-panel">
            <div class="panel-header">
              <strong>Recent Transactions</strong>
              <span class="panel-link">View All (1,248) →</span>
            </div>
            <div class="mockup-table-mini">
              <div class="table-mini-row header">
                <span>Order</span>
                <span>Customer</span>
                <span>Status</span>
                <span style="text-align:right;">Total</span>
              </div>
              <div class="table-mini-row">
                <span class="font-mono">#OMN-1042</span>
                <span>Sarah Connor</span>
                <span class="badge success">Completed</span>
                <span style="text-align:right; font-weight:600;">$59.00</span>
              </div>
              <div class="table-mini-row">
                <span class="font-mono">#OMN-1041</span>
                <span>John Doe</span>
                <span class="badge success">Completed</span>
                <span style="text-align:right; font-weight:600;">$129.00</span>
              </div>
              <div class="table-mini-row">
                <span class="font-mono">#OMN-1040</span>
                <span>Alex Vance</span>
                <span class="badge info">Processing</span>
                <span style="text-align:right; font-weight:600;">$79.00</span>
              </div>
            </div>
          </div>

          <div class="mockup-card-panel">
            <div class="panel-header">
              <strong>Quick Actions & Tools</strong>
            </div>
            <div class="quick-action-list">
              <div class="qa-item">
                <span class="qa-icon">📦</span>
                <div>
                  <div class="qa-title">+ Add New Product</div>
                  <div class="qa-desc">Publish digital, physical, or variable item</div>
                </div>
              </div>
              <div class="qa-item">
                <span class="qa-icon">🎟️</span>
                <div>
                  <div class="qa-title">Create Discount Coupon</div>
                  <div class="qa-desc">Percentage or fixed promotional code</div>
                </div>
              </div>
              <div class="qa-item">
                <span class="qa-icon">📊</span>
                <div>
                  <div class="qa-title">Export Accounting CSV</div>
                  <div class="qa-desc">Instant export for QuickBooks / Xero</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 3.1: Executive store dashboard displaying live metrics, trend comparisons, recent orders, and quick actions.'
  ),

  // Figure 4.1: Products Directory
  products_list: wrapWindow(
    'wp-admin/admin.php?page=omnify-products',
    'Catalog Directory',
    `
      <div class="mockup-content">
        <div class="mockup-toolbar">
          <div class="mockup-tabs-bar">
            <span class="m-tab active">All (148)</span>
            <span class="m-tab">Published (142)</span>
            <span class="m-tab">Digital (96)</span>
            <span class="m-tab">Physical (46)</span>
            <span class="m-tab">Drafts (6)</span>
          </div>
          <div class="mockup-btn-group">
            <span class="btn-mockup btn-outline">Export CSV</span>
            <span class="btn-mockup btn-primary">+ Add Product</span>
          </div>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="width:30px;">[ ]</div>
            <div style="flex:2;">Product Name & SKU</div>
            <div style="flex:1;">Type</div>
            <div style="flex:1;">Inventory</div>
            <div style="flex:1;">Price</div>
            <div style="flex:1;">Category</div>
            <div style="flex:1; text-align:right;">Actions</div>
          </div>
          <div class="table-row">
            <div style="width:30px;">[ ]</div>
            <div style="flex:2;">
              <div class="p-title">OmniStudio Pro Theme</div>
              <div class="p-sku">SKU: OMN-THM-01 • 342 sales</div>
            </div>
            <div style="flex:1;"><span class="badge info">Digital</span></div>
            <div style="flex:1;"><span class="badge success">In Stock (498)</span></div>
            <div style="flex:1;"><span style="font-weight:700;">$59.00</span> <s style="color:var(--text-dim); font-size:0.8rem;">$79.00</s></div>
            <div style="flex:1;">WordPress</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Edit</span> <span class="btn-xs danger">Trash</span></div>
          </div>
          <div class="table-row">
            <div style="width:30px;">[ ]</div>
            <div style="flex:2;">
              <div class="p-title">CloudSync SaaS Extension</div>
              <div class="p-sku">SKU: OMN-PLG-08 • 188 sales</div>
            </div>
            <div style="flex:1;"><span class="badge info">Digital</span></div>
            <div style="flex:1;"><span class="badge success">Unlimited (∞)</span></div>
            <div style="flex:1;"><span style="font-weight:700;">$129.00</span></div>
            <div style="flex:1;">Plugins</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Edit</span> <span class="btn-xs danger">Trash</span></div>
          </div>
          <div class="table-row">
            <div style="width:30px;">[ ]</div>
            <div style="flex:2;">
              <div class="p-title">Creator Minimal Desk Mat</div>
              <div class="p-sku">SKU: OMN-PHY-22 • 89 sales</div>
            </div>
            <div style="flex:1;"><span class="badge neutral">Physical</span></div>
            <div style="flex:1;"><span class="badge warning">Low Stock (8)</span></div>
            <div style="flex:1;"><span style="font-weight:700;">$34.00</span></div>
            <div style="flex:1;">Merch</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Edit</span> <span class="btn-xs danger">Trash</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.1: Product catalog listing with multi-parameter filtering, stock indicators, and quick action buttons.'
  ),

  // Figure 4.2: Comprehensive Product Editor
  product_editor: wrapWindow(
    'wp-admin/admin.php?page=omnify-products&action=edit&id=101',
    'Product Editor',
    `
      <div class="mockup-content">
        <div class="editor-header">
          <input type="text" class="editor-title-input" value="OmniStudio Pro Theme" readonly />
          <span class="btn-mockup btn-primary">Update Product</span>
        </div>

        <div class="editor-tabs">
          <span class="e-tab active">General</span>
          <span class="e-tab">Inventory</span>
          <span class="e-tab">Digital Locker (2 Files)</span>
          <span class="e-tab">Attributes & Variations</span>
          <span class="e-tab">SEO & Social</span>
        </div>

        <div class="editor-panel">
          <div class="mockup-split-2">
            <div>
              <div class="mockup-field">
                <label>Product Type</label>
                <div class="mockup-input-select">Digital / Downloadable Asset</div>
              </div>
              <div class="mockup-form-grid">
                <div class="mockup-field">
                  <label>Regular Price ($)</label>
                  <div class="mockup-input-text">79.00</div>
                </div>
                <div class="mockup-field">
                  <label>Sale Price ($)</label>
                  <div class="mockup-input-text">59.00</div>
                </div>
              </div>
              <div class="mockup-field">
                <label>SKU (Stock Keeping Unit)</label>
                <div class="mockup-input-text">OMN-THM-01</div>
              </div>
            </div>

            <div>
              <div class="mockup-field">
                <label>Attached Digital Files (HMAC-SHA256 Encrypted)</label>
                <div class="file-locker-item">
                  <span class="f-icon">📦</span>
                  <div style="flex:1;">
                    <div style="font-weight:600; font-size:0.85rem;">omnistudio-pro-v1.2.zip</div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">8.4 MB • SHA-256 Protected • 5 Download Limit</div>
                  </div>
                  <span class="badge success">Active</span>
                </div>
                <div class="file-locker-item">
                  <span class="f-icon">📄</span>
                  <div style="flex:1;">
                    <div style="font-weight:600; font-size:0.85rem;">quickstart-and-licensing.pdf</div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">1.2 MB • Token Expiration: 72 Hours</div>
                  </div>
                  <span class="badge success">Active</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.2: Comprehensive product editor with pricing, stock rules, file lockers, and gallery controls.'
  ),

  // Figure 4.3: Product Categories
  categories: wrapWindow(
    'wp-admin/admin.php?page=omnify-categories',
    'Taxonomies › Categories',
    `
      <div class="mockup-content">
        <div class="mockup-split-2">
          <div class="mockup-card-panel">
            <h5 style="margin-top:0;">Add New Category</h5>
            <div class="mockup-field">
              <label>Category Name</label>
              <div class="mockup-input-text" style="color:var(--text-dim);">e.g. SaaS Extensions</div>
            </div>
            <div class="mockup-field">
              <label>Slug</label>
              <div class="mockup-input-text" style="color:var(--text-dim);">saas-extensions</div>
            </div>
            <div class="mockup-field">
              <label>Parent Category</label>
              <div class="mockup-input-select">None (Top Level)</div>
            </div>
            <span class="btn-mockup btn-primary">+ Add New Category</span>
          </div>

          <div class="mockup-card-panel">
            <div class="mockup-table-mini">
              <div class="table-mini-row header">
                <span>Name</span>
                <span>Slug</span>
                <span style="text-align:right;">Count</span>
              </div>
              <div class="table-mini-row">
                <span style="font-weight:600;">WordPress Themes</span>
                <span class="font-mono">wp-themes</span>
                <span style="text-align:right;"><span class="badge info">48</span></span>
              </div>
              <div class="table-mini-row">
                <span style="font-weight:600;">SaaS Plugins</span>
                <span class="font-mono">saas-plugins</span>
                <span style="text-align:right;"><span class="badge info">32</span></span>
              </div>
              <div class="table-mini-row">
                <span style="font-weight:600;">Developer Toolkits</span>
                <span class="font-mono">dev-kits</span>
                <span style="text-align:right;"><span class="badge info">18</span></span>
              </div>
              <div class="table-mini-row">
                <span style="font-weight:600;">Creator Merch</span>
                <span class="font-mono">creator-merch</span>
                <span style="text-align:right;"><span class="badge info">24</span></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.3: Product Categories management screen for hierarchical catalog grouping.'
  ),

  // Figure 4.4: Product Tags
  tags: wrapWindow(
    'wp-admin/admin.php?page=omnify-tags',
    'Taxonomies › Tags',
    `
      <div class="mockup-content">
        <div style="margin-bottom:16px;">
          <strong style="font-size:0.95rem;">Catalog Tags Directory</strong>
          <p style="font-size:0.82rem; color:var(--text-dim); margin-top:2px;">Tags allow cross-cutting thematic product discovery and faceted search.</p>
        </div>
        <div class="tag-cloud-preview">
          <span class="tag-pill">#gutenberg <b>(42)</b></span>
          <span class="tag-pill">#block-theme <b>(38)</b></span>
          <span class="tag-pill">#headless-ready <b>(19)</b></span>
          <span class="tag-pill">#crypto-locker <b>(64)</b></span>
          <span class="tag-pill">#stripe-verified <b>(88)</b></span>
          <span class="tag-pill">#sub-millisecond <b>(31)</b></span>
          <span class="tag-pill">#minimal-design <b>(52)</b></span>
          <span class="tag-pill">#nextjs <b>(14)</b></span>
        </div>
      </div>
    `,
    'Figure 4.4: Granular product tags for cross-category search and thematic tagging.'
  ),

  // Figure 4.5: Product Brands
  brands: wrapWindow(
    'wp-admin/admin.php?page=omnify-brands',
    'Taxonomies › Brands',
    `
      <div class="mockup-content">
        <div class="mockup-kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
          <div class="kpi-box" style="text-align:center;">
            <div style="font-size:2rem; margin-bottom:8px;">⚡</div>
            <div style="font-weight:700;">OmnifyWP Core</div>
            <div style="font-size:0.75rem; color:var(--text-dim);">84 Products • Official</div>
          </div>
          <div class="kpi-box" style="text-align:center;">
            <div style="font-size:2rem; margin-bottom:8px;">☁️</div>
            <div style="font-weight:700;">CloudSync Labs</div>
            <div style="font-size:0.75rem; color:var(--text-dim);">26 Products • SaaS</div>
          </div>
          <div class="kpi-box" style="text-align:center;">
            <div style="font-size:2rem; margin-bottom:8px;">🎨</div>
            <div style="font-weight:700;">StudioPress Assets</div>
            <div style="font-size:0.75rem; color:var(--text-dim);">38 Products • Design</div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.5: Manufacturer and brand assignments with storefront brand logos.'
  ),

  // Figure 4.6: Global Attributes Manager
  attributes: wrapWindow(
    'wp-admin/admin.php?page=omnify-attributes',
    'Catalog › Attributes',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1;">Attribute Name</div>
            <div style="flex:1;">Slug</div>
            <div style="flex:2;">Configured Terms / Options</div>
            <div style="flex:1; text-align:right;">Variations</div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-weight:600;">License Type</div>
            <div style="flex:1;" class="font-mono">pa_license</div>
            <div style="flex:2;"><span class="badge info">Single Site</span> <span class="badge info">5 Sites</span> <span class="badge info">Unlimited Agency</span></div>
            <div style="flex:1; text-align:right;"><span class="badge success">Enabled</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-weight:600;">Platform Stack</div>
            <div style="flex:1;" class="font-mono">pa_platform</div>
            <div style="flex:2;"><span class="badge info">WordPress</span> <span class="badge info">Next.js 14</span> <span class="badge info">Nuxt 3</span></div>
            <div style="flex:1; text-align:right;"><span class="badge success">Enabled</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-weight:600;">Color Theme</div>
            <div style="flex:1;" class="font-mono">pa_color</div>
            <div style="flex:2;"><span class="badge info">Forest Green</span> <span class="badge info">Emerald</span> <span class="badge info">Obsidian</span></div>
            <div style="flex:1; text-align:right;"><span class="badge success">Enabled</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.6: Global Attributes manager for configuring variation axes (License, Platform, Color).'
  ),

  // Figure 5.1: Orders Management Directory
  orders_list: wrapWindow(
    'wp-admin/admin.php?page=omnify-orders',
    'Orders Center',
    `
      <div class="mockup-content">
        <div class="mockup-toolbar">
          <div class="mockup-tabs-bar">
            <span class="m-tab active">All (1,248)</span>
            <span class="m-tab">Completed (1,180)</span>
            <span class="m-tab">Processing (42)</span>
            <span class="m-tab">Pending (16)</span>
            <span class="m-tab">Refunded (10)</span>
          </div>
          <span class="btn-mockup btn-outline">Export Orders (CSV)</span>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1.2;">Order Number</div>
            <div style="flex:2;">Customer & Email</div>
            <div style="flex:1;">Status</div>
            <div style="flex:1;">Payment</div>
            <div style="flex:1;">Date</div>
            <div style="flex:1; text-align:right;">Total</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2;" class="font-mono"><b>#OMN-2026-1042</b></div>
            <div style="flex:2;">
              <div>Sarah Connor</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">sarah@cyberdyne.org</div>
            </div>
            <div style="flex:1;"><span class="badge success">Completed</span></div>
            <div style="flex:1;">Stripe (Card)</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">Oct 3, 2026</div>
            <div style="flex:1; text-align:right; font-weight:700;">$59.00</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2;" class="font-mono"><b>#OMN-2026-1041</b></div>
            <div style="flex:2;">
              <div>John Doe</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">john@example.com</div>
            </div>
            <div style="flex:1;"><span class="badge success">Completed</span></div>
            <div style="flex:1;">PayPal</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">Oct 2, 2026</div>
            <div style="flex:1; text-align:right; font-weight:700;">$129.00</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2;" class="font-mono"><b>#OMN-2026-1040</b></div>
            <div style="flex:2;">
              <div>Alex Vance</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">alex@blackmesa.gov</div>
            </div>
            <div style="flex:1;"><span class="badge info">Processing</span></div>
            <div style="flex:1;">Razorpay</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">Oct 2, 2026</div>
            <div style="flex:1; text-align:right; font-weight:700;">$79.00</div>
          </div>
        </div>
      </div>
    `,
    'Figure 5.1: Orders management table showing order numbers, customer names, status badges, and transaction totals.'
  ),

  // Figure 5.2: Order Detail View
  order_detail: wrapWindow(
    'wp-admin/admin.php?page=omnify-orders&action=view&id=1042',
    'Order #OMN-2026-1042',
    `
      <div class="mockup-content">
        <div class="mockup-bar" style="border-bottom:1px solid var(--border-subtle); padding-bottom:14px; margin-bottom:16px;">
          <div>
            <h4 style="margin:0;">Order #OMN-2026-1042</h4>
            <span style="font-size:0.8rem; color:var(--text-dim);">Placed on Oct 3, 2026 at 07:00 AM • Paid via Stripe (ch_3Nxy99Lkd891)</span>
          </div>
          <div style="display:flex; gap:8px;">
            <span class="btn-mockup btn-outline">Resend Receipt</span>
            <span class="btn-mockup btn-outline danger">Issue Refund</span>
          </div>
        </div>

        <div class="risk-badge-banner">
          <span style="font-size:1.2rem;">🛡️</span>
          <div>
            <strong>Automated Fraud & Risk Assessment: Low Risk (Score: 98/100)</strong>
            <div style="font-size:0.75rem; color:var(--text-dim);">Passed CVC match, 3D-Secure authentication, and matching GeoIP country coordinates.</div>
          </div>
        </div>

        <div class="mockup-split-2" style="margin-top:16px;">
          <div>
            <h5 style="margin-top:0;">Purchased Line Items</h5>
            <div class="mockup-table-mini">
              <div class="table-mini-row header">
                <span>Product</span>
                <span>Qty</span>
                <span style="text-align:right;">Subtotal</span>
              </div>
              <div class="table-mini-row">
                <div>
                  <div style="font-weight:600;">OmniStudio Pro Theme</div>
                  <div style="font-size:0.75rem; color:var(--text-dim);">Single Site License • 2 Files Granted</div>
                </div>
                <span>1</span>
                <span style="text-align:right; font-weight:600;">$59.00</span>
              </div>
            </div>
            <div style="text-align:right; margin-top:12px; font-size:0.9rem;">
              <div>Tax (0% Digital Export): <b>$0.00</b></div>
              <div style="font-size:1.1rem; font-weight:800; color:var(--brand-emerald); margin-top:4px;">Total Paid: $59.00</div>
            </div>
          </div>

          <div>
            <h5 style="margin-top:0;">Cryptographic File Grants</h5>
            <div class="file-locker-item">
              <span class="f-icon">🔒</span>
              <div style="flex:1;">
                <div style="font-weight:600; font-size:0.85rem;">omnistudio-pro-v1.2.zip</div>
                <div style="font-size:0.75rem; color:var(--text-dim);">Token active until Oct 10, 2026 (2/5 used)</div>
              </div>
              <span class="btn-xs">Regenerate</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 5.2: Order detail view with item breakdown, risk score analysis, refund manager, and receipt tools.'
  ),

  // Figure 6.1: Customer CRM Directory
  customers: wrapWindow(
    'wp-admin/admin.php?page=omnify-customers',
    'Customer Directory',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2;">Customer Profile</div>
            <div style="flex:1;">Tier</div>
            <div style="flex:1;">Orders</div>
            <div style="flex:1.2;">Lifetime Value (LTV)</div>
            <div style="flex:1;">Last Active</div>
            <div style="flex:1; text-align:right;">Actions</div>
          </div>
          <div class="table-row">
            <div style="flex:2;">
              <div style="font-weight:700;">Sarah Connor</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">sarah@cyberdyne.org</div>
            </div>
            <div style="flex:1;"><span class="badge success">VIP Buyer</span></div>
            <div style="flex:1;">4 orders</div>
            <div style="flex:1.2; font-weight:700; color:var(--brand-emerald);">$416.00</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">Today</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">View CRM</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2;">
              <div style="font-weight:700;">Elena Rostova</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">elena@neotech.io</div>
            </div>
            <div style="flex:1;"><span class="badge success">VIP Buyer</span></div>
            <div style="flex:1;">6 orders</div>
            <div style="flex:1.2; font-weight:700; color:var(--brand-emerald);">$720.00</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">2d ago</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">View CRM</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2;">
              <div style="font-weight:700;">Marcus Brody</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">marcus@marshall.edu</div>
            </div>
            <div style="flex:1;"><span class="badge info">Standard</span></div>
            <div style="flex:1;">2 orders</div>
            <div style="flex:1.2; font-weight:700;">$188.00</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">4d ago</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">View CRM</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 6.1: Customer relationship management directory with purchase history, LTV, and status indicators.'
  ),

  // Figure 7.1: Promotional Coupons Management
  coupons: wrapWindow(
    'wp-admin/admin.php?page=omnify-coupons',
    'Promotions & Coupons',
    `
      <div class="mockup-content">
        <div class="mockup-toolbar">
          <span style="font-weight:700;">Active Store Promotions</span>
          <span class="btn-mockup btn-primary">+ Create Coupon</span>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1.5;">Coupon Code</div>
            <div style="flex:1.2;">Discount Type</div>
            <div style="flex:1;">Min Spend</div>
            <div style="flex:1.5;">Usage Cap</div>
            <div style="flex:1;">Expires</div>
            <div style="flex:1; text-align:right;">Status</div>
          </div>
          <div class="table-row">
            <div style="flex:1.5;" class="font-mono"><b>SUMMER20</b></div>
            <div style="flex:1.2;"><span class="badge success">20% Off Cart</span></div>
            <div style="flex:1;">$50.00</div>
            <div style="flex:1.5;">142 / 500 used</div>
            <div style="flex:1; font-size:0.8rem;">Oct 31, 2026</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Active</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5;" class="font-mono"><b>LAUNCH10</b></div>
            <div style="flex:1.2;"><span class="badge info">$10.00 Fixed</span></div>
            <div style="flex:1;">$30.00</div>
            <div style="flex:1.5;">320 / 1,000 used</div>
            <div style="flex:1; font-size:0.8rem;">Never</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Active</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5;" class="font-mono"><b>VIP50</b></div>
            <div style="flex:1.2;"><span class="badge warning">50% Off VIP</span></div>
            <div style="flex:1;">$100.00</div>
            <div style="flex:1.5;">18 / 25 used</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">Sep 30, 2026</div>
            <div style="flex:1; text-align:right;"><span class="badge neutral">Expired</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 7.1: Promotional coupon management with usage counters, expiration dates, and discount rules.'
  ),

  // Figure 8.1: Real-time Analytics Dashboard
  analytics: wrapWindow(
    'wp-admin/admin.php?page=omnify-analytics',
    'Analytics & Reports',
    `
      <div class="mockup-content">
        <div class="mockup-kpi-grid">
          <div class="kpi-box">
            <span class="kpi-label">Gross Sales</span>
            <span class="kpi-val">$48,290.00</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Net Sales</span>
            <span class="kpi-val">$47,680.00</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Average Order (AOV)</span>
            <span class="kpi-val">$64.50</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Refund Rate</span>
            <span class="kpi-val" style="color:var(--brand-emerald);">1.2%</span>
          </div>
        </div>

        <div class="mockup-card-panel" style="margin-top:16px;">
          <div class="panel-header">
            <strong>Revenue Trajectory (30-Day Period)</strong>
            <span class="panel-link">Download Accounting CSV</span>
          </div>
          <div class="mockup-chart-bars">
            <div class="chart-col"><div class="bar-fill" style="height:40%;"></div><span>W1</span></div>
            <div class="chart-col"><div class="bar-fill" style="height:65%;"></div><span>W2</span></div>
            <div class="chart-col"><div class="bar-fill" style="height:85%;"></div><span>W3</span></div>
            <div class="chart-col"><div class="bar-fill" style="height:100%;"></div><span>W4</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 8.1: Real-time analytics dashboard with gross/net sales, AOV, tax breakdowns, and CSV export.'
  ),

  // Figure 9.1: Reviews Moderation Directory
  reviews: wrapWindow(
    'wp-admin/admin.php?page=omnify-reviews',
    'Reviews Moderation',
    `
      <div class="mockup-content">
        <div class="mockup-card-panel" style="margin-bottom:12px;">
          <div class="mockup-bar">
            <div>
              <span style="color:#FBBF24;">★★★★★</span>
              <strong style="margin-left:6px;">Sarah Connor</strong>
              <span class="badge success" style="margin-left:6px;">Verified Buyer</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-dim);">Oct 3, 2026</span>
          </div>
          <p style="margin:8px 0; font-size:0.88rem; color:var(--text-main);">"Unbelievable speed. Replaced a bloated WooCommerce setup and our page load time dropped to 0.4s. The cryptographic file locker alone is worth the switch."</p>
          <div class="mockup-bar" style="font-size:0.75rem; color:var(--text-dim);">
            <span>Product: <b>OmniStudio Pro Theme</b></span>
            <div><span class="btn-xs">Approved ✓</span> <span class="btn-xs danger">Trash</span></div>
          </div>
        </div>

        <div class="mockup-card-panel">
          <div class="mockup-bar">
            <div>
              <span style="color:#FBBF24;">★★★★★</span>
              <strong style="margin-left:6px;">David H.</strong>
              <span class="badge success" style="margin-left:6px;">Verified Buyer</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-dim);">Oct 2, 2026</span>
          </div>
          <p style="margin:8px 0; font-size:0.88rem; color:var(--text-main);">"The clean normalized database tables make running queries in our custom analytics script effortless. No messy wp_postmeta joins."</p>
          <div class="mockup-bar" style="font-size:0.75rem; color:var(--text-dim);">
            <span>Product: <b>CloudSync SaaS Extension</b></span>
            <div><span class="btn-xs">Approved ✓</span> <span class="btn-xs danger">Trash</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 9.1: Reviews moderation directory showing verified buyer badges, ratings, and status actions.'
  ),

  // Figure 10.1: Abandoned Cart Recovery
  abandoned_carts: wrapWindow(
    'wp-admin/admin.php?page=omnify-abandoned-carts',
    'Abandoned Cart Recovery',
    `
      <div class="mockup-content">
        <div class="mockup-kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:16px;">
          <div class="kpi-box">
            <span class="kpi-label">Dropped Sessions</span>
            <span class="kpi-val">48</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Recovered Orders</span>
            <span class="kpi-val" style="color:var(--brand-emerald);">18 (37.5%)</span>
          </div>
          <div class="kpi-box">
            <span class="kpi-label">Recovered Revenue</span>
            <span class="kpi-val" style="color:var(--brand-emerald);">$2,140.00</span>
          </div>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2;">Customer Email</div>
            <div style="flex:2;">Abandoned Items</div>
            <div style="flex:1;">Value</div>
            <div style="flex:1;">Time Ago</div>
            <div style="flex:1.2; text-align:right;">Status</div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">alex@venturecapital.com</div>
            <div style="flex:2; font-size:0.82rem;">OmniStudio Pro Theme (x1)</div>
            <div style="flex:1; font-weight:700;">$59.00</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">2h ago</div>
            <div style="flex:1.2; text-align:right;"><span class="badge info">Email 1 Sent</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">lisa@creativestudio.design</div>
            <div style="flex:2; font-size:0.82rem;">CloudSync SaaS + Desk Mat</div>
            <div style="flex:1; font-weight:700;">$163.00</div>
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">5h ago</div>
            <div style="flex:1.2; text-align:right;"><span class="badge success">Recovered ✓</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 10.1: Abandoned cart tracker displaying customer emails, abandoned items, subtotals, and recovery status.'
  ),

  // Figure 11.1: General Store Settings
  settings_general: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=general',
    'Settings › General',
    `
      <div class="mockup-content">
        <div class="mockup-form-grid">
          <div class="mockup-field">
            <label>Store Base Currency</label>
            <div class="mockup-input-select">United States Dollar (USD - $)</div>
          </div>
          <div class="mockup-field">
            <label>Currency Position</label>
            <div class="mockup-input-select">Left ($99.00)</div>
          </div>
          <div class="mockup-field">
            <label>Thousand Separator</label>
            <div class="mockup-input-text">,</div>
          </div>
          <div class="mockup-field">
            <label>Decimal Separator & Digits</label>
            <div class="mockup-input-text">. (2 Decimals)</div>
          </div>
          <div class="mockup-field">
            <label>Weight Unit</label>
            <div class="mockup-input-select">Kilogram (kg)</div>
          </div>
          <div class="mockup-field">
            <label>Dimension Unit</label>
            <div class="mockup-input-select">Centimeter (cm)</div>
          </div>
        </div>
        <div style="margin-top:16px;">
          <span class="btn-mockup btn-primary">Save Changes</span>
        </div>
      </div>
    `,
    'Figure 11.1: General store settings for currency, formatting, and measurement units.'
  ),

  // Figure 11.2: Payment Gateways Settings
  settings_payments: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=payments',
    'Settings › Payments',
    `
      <div class="mockup-content">
        <div class="gateway-row">
          <div style="display:flex; align-items:center; gap:12px;">
            <span class="g-icon">💳</span>
            <div>
              <div style="font-weight:700;">Stripe Elements (Credit/Debit, Apple Pay, Google Pay)</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">Live Mode Active • Webhooks Verified (200 OK)</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="badge success">Enabled</span>
            <span class="btn-xs">Manage</span>
          </div>
        </div>

        <div class="gateway-row">
          <div style="display:flex; align-items:center; gap:12px;">
            <span class="g-icon">🅿️</span>
            <div>
              <div style="font-weight:700;">PayPal Commerce Platform</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">Express Checkout & Pay in 4 Installments</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="badge success">Enabled</span>
            <span class="btn-xs">Manage</span>
          </div>
        </div>

        <div class="gateway-row">
          <div style="display:flex; align-items:center; gap:12px;">
            <span class="g-icon">⚡</span>
            <div>
              <div style="font-weight:700;">Razorpay (India)</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">UPI, NetBanking, RuPay Cards</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="badge info">Ready</span>
            <span class="btn-xs">Configure</span>
          </div>
        </div>

        <div class="gateway-row">
          <div style="display:flex; align-items:center; gap:12px;">
            <span class="g-icon">🇧🇩</span>
            <div>
              <div style="font-weight:700;">SSLCommerz (Bangladesh)</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">bKash, Nagad, Rocket & Cards</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="badge info">Ready</span>
            <span class="btn-xs">Configure</span>
          </div>
        </div>

        <div class="gateway-row">
          <div style="display:flex; align-items:center; gap:12px;">
            <span class="g-icon">💵</span>
            <div>
              <div style="font-weight:700;">Cash on Delivery (COD) / Bank Wire</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">Offline payment verification workflow</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="badge success">Enabled</span>
            <span class="btn-xs">Manage</span>
          </div>
        </div>
      </div>
    `,
    'Figure 11.2: Payment gateways screen supporting Stripe, PayPal, Razorpay, SSLCommerz, and manual payments.'
  ),

  // Figure 11.3: Delivery & Shipping Settings
  settings_delivery: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=delivery',
    'Settings › Delivery',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2;">Shipping Zone / Method</div>
            <div style="flex:1;">Type</div>
            <div style="flex:1;">Cost</div>
            <div style="flex:1.5;">Rule Conditions</div>
            <div style="flex:1; text-align:right;">Status</div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Domestic Standard (US)</div>
            <div style="flex:1;">Flat Rate</div>
            <div style="flex:1; font-weight:700;">$5.00</div>
            <div style="flex:1.5; font-size:0.8rem; color:var(--text-dim);">All US Orders</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Active</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Free Shipping Tier</div>
            <div style="flex:1;">Threshold</div>
            <div style="flex:1; font-weight:700; color:var(--brand-emerald);">$0.00</div>
            <div style="flex:1.5; font-size:0.8rem; color:var(--text-dim);">Orders over $100.00</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Active</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Worldwide Express</div>
            <div style="flex:1;">International</div>
            <div style="flex:1; font-weight:700;">$25.00</div>
            <div style="flex:1.5; font-size:0.8rem; color:var(--text-dim);">Non-US Destinations</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Active</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.3: Delivery settings for configuring Flat Rates, Free Shipping thresholds, and Local Pickup.'
  ),

  // Figure 11.4: Tax Settings
  settings_taxes: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=taxes',
    'Settings › Taxes',
    `
      <div class="mockup-content">
        <div class="mockup-split-2">
          <div>
            <div class="mockup-field">
              <label>Tax Calculation Engine</label>
              <div class="mockup-input-select">Enabled (Automated Tax Calculation)</div>
            </div>
            <div class="mockup-field">
              <label>Catalog Prices Entered</label>
              <div class="mockup-input-select">Exclusive of Tax</div>
            </div>
          </div>
          <div>
            <div class="mockup-field">
              <label>EU VAT Digital Goods</label>
              <div class="mockup-input-select">Automated MOSS Reverse-Charge Enabled</div>
            </div>
            <div class="mockup-field">
              <label>Display in Cart / Checkout</label>
              <div class="mockup-input-select">Itemized Tax Line Item</div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.4: Tax calculation options for inclusive/exclusive pricing and regional tax rates.'
  ),

  // Figure 11.5: Email Notifications Settings
  settings_emails: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=emails',
    'Settings › Emails',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2;">Notification Event</div>
            <div style="flex:1.2;">Recipient</div>
            <div style="flex:1;">Format</div>
            <div style="flex:1; text-align:right;">Actions</div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Customer Order Confirmation</div>
            <div style="flex:1.2;"><span class="badge info">Customer</span></div>
            <div style="flex:1;">HTML Email</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Customize</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Cryptographic File Delivery (Digital)</div>
            <div style="flex:1.2;"><span class="badge info">Customer</span></div>
            <div style="flex:1;">HTML Email</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Customize</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Admin New Order Notification</div>
            <div style="flex:1.2;"><span class="badge neutral">Store Admin</span></div>
            <div style="flex:1;">HTML Email</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs">Customize</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.5: Automated customer and admin email notification templates.'
  ),

  // Figure 11.6: Checkout Settings
  settings_checkout: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=checkout',
    'Settings › Checkout',
    `
      <div class="mockup-content">
        <div class="mockup-split-2">
          <div>
            <div class="mockup-field">
              <label>Guest Checkout</label>
              <div class="mockup-input-select">Allowed (No mandatory registration)</div>
            </div>
            <div class="mockup-field">
              <label>Checkout Layout</label>
              <div class="mockup-input-select">Express Single-Step Conversion Flow</div>
            </div>
          </div>
          <div>
            <div class="mockup-field">
              <label>Terms & Privacy Agreement</label>
              <div class="mockup-input-select">Required Checkbox with modal popup</div>
            </div>
            <div class="mockup-field">
              <label>Cart Scarcity Timer</label>
              <div class="mockup-input-select">15-Minute Reserved Cart Badge</div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.6: Checkout options for guest purchases, account creation, and terms acceptance.'
  ),

  // Figure 11.7: Store Pages Mapping
  settings_pages: wrapWindow(
    'wp-admin/admin.php?page=omnify-settings&tab=pages',
    'Settings › Core Pages',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1.5;">Role</div>
            <div style="flex:1.5;">WordPress Page</div>
            <div style="flex:2;">Shortcode Binding</div>
            <div style="flex:1; text-align:right;">Status</div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Storefront</div>
            <div style="flex:1.5;"><code>/storefront/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_storefront]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped ✓</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Cart</div>
            <div style="flex:1.5;"><code>/cart/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_cart]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped ✓</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Checkout</div>
            <div style="flex:1.5;"><code>/checkout/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_checkout]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped ✓</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Customer Portal</div>
            <div style="flex:1.5;"><code>/customer-portal/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_customer_portal]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped ✓</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.7: Core page mappings linking shortcodes to active WordPress pages.'
  ),

  // Figure 12.1: System Tools & Diagnostics
  tools: wrapWindow(
    'wp-admin/admin.php?page=omnify-tools',
    'System Tools & Maintenance',
    `
      <div class="mockup-content">
        <div class="risk-badge-banner" style="margin-bottom:16px;">
          <span style="font-size:1.2rem;">✨</span>
          <div>
            <strong>18 Normalized Relational Tables: 100% Operational & Verified</strong>
            <div style="font-size:0.75rem; color:var(--text-dim);">Indexes optimized, zero missing columns, schema migration v2026_09_24 up to date.</div>
          </div>
        </div>

        <div class="mockup-split-2">
          <div class="mockup-card-panel">
            <h5 style="margin-top:0;">Catalog & Demo Seeding</h5>
            <p style="font-size:0.82rem; color:var(--text-dim);">Populate catalog with realistic sample products, variations, and test orders.</p>
            <div style="display:flex; gap:8px;">
              <span class="btn-mockup btn-primary">Seed 50 Products</span>
              <span class="btn-mockup btn-outline danger">Clear Demo Data</span>
            </div>
          </div>

          <div class="mockup-card-panel">
            <h5 style="margin-top:0;">Performance & Caching</h5>
            <p style="font-size:0.82rem; color:var(--text-dim);">Purge product transients, cached pricing queries, and reload active gateways.</p>
            <div style="display:flex; gap:8px;">
              <span class="btn-mockup btn-outline">Flush Query Cache</span>
              <span class="btn-mockup btn-outline">Repair Schema</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 12.1: System diagnostics, demo data seeding/cleanup, and database optimization tools.'
  ),

  // Figure 13.1: Activity Audit Log
  activity_log: wrapWindow(
    'wp-admin/admin.php?page=omnify-activity',
    'Activity Audit Log',
    `
      <div class="mockup-content">
        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1;">Timestamp</div>
            <div style="flex:1.2;">Actor / User</div>
            <div style="flex:3;">Action Performed</div>
            <div style="flex:1; text-align:right;">Severity</div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">10:14:02 AM</div>
            <div style="flex:1.2; font-weight:600;">admin (John)</div>
            <div style="flex:3;">Updated regular price on product <code>OMN-THM-01</code> to $59.00</div>
            <div style="flex:1; text-align:right;"><span class="badge info">Info</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">09:42:15 AM</div>
            <div style="flex:1.2; font-weight:600;">webhook (Stripe)</div>
            <div style="flex:3;">Payment confirmed for Order #OMN-1042; granted digital locker token</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Success</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1; font-size:0.8rem; color:var(--text-dim);">08:30:44 AM</div>
            <div style="flex:1.2; font-weight:600;">sarah_manager</div>
            <div style="flex:3;">Created coupon code <code>SUMMER20</code> with 20% discount</div>
            <div style="flex:1; text-align:right;"><span class="badge info">Info</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 13.1: Chronological audit log showing user actions, setting changes, and timestamps.'
  ),

  // Figure 14.1: API Keys Management
  api_keys: wrapWindow(
    'wp-admin/admin.php?page=omnify-api-keys',
    'REST API Keys',
    `
      <div class="mockup-content">
        <div class="mockup-toolbar">
          <span style="font-weight:700;">Active Headless & Integrations Keys</span>
          <span class="btn-mockup btn-primary">+ Generate API Key</span>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2;">Key Description</div>
            <div style="flex:1.2;">User</div>
            <div style="flex:1;">Permissions</div>
            <div style="flex:2;">Consumer Key Prefix</div>
            <div style="flex:1; text-align:right;">Actions</div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">Next.js Headless Front</div>
            <div style="flex:1.2;">api_admin</div>
            <div style="flex:1;"><span class="badge success">Read/Write</span></div>
            <div style="flex:2;" class="font-mono">ck_live_99482f...</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs danger">Revoke</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2; font-weight:600;">iOS Mobile App Native</div>
            <div style="flex:1.2;">mobile_service</div>
            <div style="flex:1;"><span class="badge info">Read Only</span></div>
            <div style="flex:2;" class="font-mono">ck_live_831b11...</div>
            <div style="flex:1; text-align:right;"><span class="btn-xs danger">Revoke</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 14.1: REST API key management panel for headless commerce and external integrations.'
  ),

  // Figure 15.1: Storefront Catalog Grid
  storefront_catalog: wrapWindow(
    'https://omnifywp.com/storefront/',
    'Storefront Catalog',
    `
      <div class="mockup-storefront">
        <div class="storefront-grid-3">
          <div class="store-product-card">
            <div class="product-badge-sale">Sale -25%</div>
            <div class="product-card-img">📦</div>
            <div class="product-card-body">
              <div class="product-cat-tag">WordPress Themes</div>
              <h4 class="product-card-title">OmniStudio Pro Theme</h4>
              <div class="product-stars">★★★★★ <span style="font-size:0.75rem; color:var(--text-dim);">(42 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$59.00</span>
                <s class="price-old">$79.00</s>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Add to Cart 🛒</span>
            </div>
          </div>

          <div class="store-product-card">
            <div class="product-card-img">☁️</div>
            <div class="product-card-body">
              <div class="product-cat-tag">SaaS Plugins</div>
              <h4 class="product-card-title">CloudSync Extension</h4>
              <div class="product-stars">★★★★★ <span style="font-size:0.75rem; color:var(--text-dim);">(28 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$129.00</span>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Add to Cart 🛒</span>
            </div>
          </div>

          <div class="store-product-card">
            <div class="product-card-img">🎨</div>
            <div class="product-card-body">
              <div class="product-cat-tag">Creator Merch</div>
              <h4 class="product-card-title">Minimalist Desk Mat</h4>
              <div class="product-stars">★★★★★ <span style="font-size:0.75rem; color:var(--text-dim);">(19 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$34.00</span>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Add to Cart 🛒</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.1: Responsive catalog grid with sidebar category/brand filters and quick add-to-cart buttons.'
  ),

  // Figure 15.2: Product Details Page
  storefront_product: wrapWindow(
    'https://omnifywp.com/storefront/omnistudio-pro-theme/',
    'Product Details Page',
    `
      <div class="mockup-storefront">
        <div class="mockup-split-2">
          <div class="product-gallery-mock">
            <div class="main-gallery-preview">⚡ OmniStudio Pro</div>
            <div class="gallery-thumbs">
              <span class="thumb active">⚡</span>
              <span class="thumb">📱</span>
              <span class="thumb">📊</span>
            </div>
          </div>

          <div class="product-meta-mock">
            <span class="badge success">Digital Download • Instant Delivery</span>
            <h3 style="margin:8px 0 4px; font-size:1.35rem;">OmniStudio Pro Theme</h3>
            <div class="product-stars">★★★★★ (42 verified reviews) • 342 sold</div>
            <div class="product-price-row" style="margin:12px 0;">
              <span class="price-current" style="font-size:1.6rem;">$59.00</span>
              <s class="price-old" style="font-size:1.1rem;">$79.00</s>
              <span class="badge success">Save $20</span>
            </div>
            <div style="font-size:0.88rem; color:var(--text-muted); line-height:1.5; margin-bottom:14px;">
              Ultra-fast Full Site Editing theme built for eCommerce. 100% block-based, sub-millisecond query performance, and built-in conversion features.
            </div>

            <div style="margin-bottom:14px;">
              <label style="font-size:0.8rem; font-weight:700;">Select License Tier:</label>
              <div style="display:flex; gap:8px; margin-top:4px;">
                <span class="mockup-pill active">Single Site ($59)</span>
                <span class="mockup-pill">5 Sites ($129)</span>
                <span class="mockup-pill">Unlimited Agency ($249)</span>
              </div>
            </div>

            <div style="display:flex; gap:10px;">
              <span class="btn-mockup btn-primary" style="flex:1; text-align:center;">Add to Cart 🛒</span>
              <span class="btn-mockup btn-outline" style="flex:1; text-align:center;">Buy Now (Instant Checkout)</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.2: Product details layout featuring gallery zoom, format selection swatches, and verified reviews.'
  ),

  // Figure 15.3: Shopping Cart Page
  storefront_cart: wrapWindow(
    'https://omnifywp.com/cart/',
    'Shopping Cart',
    `
      <div class="mockup-storefront">
        <div class="mockup-split-2">
          <div>
            <div class="mockup-data-table">
              <div class="table-head">
                <div style="flex:3;">Product</div>
                <div style="flex:1;">Price</div>
                <div style="flex:1.2;">Quantity</div>
                <div style="flex:1; text-align:right;">Subtotal</div>
              </div>
              <div class="table-row">
                <div style="flex:3;">
                  <div style="font-weight:700;">OmniStudio Pro Theme</div>
                  <div style="font-size:0.75rem; color:var(--text-dim);">Single Site License</div>
                </div>
                <div style="flex:1;">$59.00</div>
                <div style="flex:1.2;"><span class="qty-stepper">[-] 1 [+]</span></div>
                <div style="flex:1; text-align:right; font-weight:700;">$59.00</div>
              </div>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
              <div class="mockup-input-text" style="flex:1;">SUMMER20</div>
              <span class="btn-mockup btn-outline">Apply Coupon</span>
            </div>
          </div>

          <div class="mockup-card-panel">
            <h5 style="margin-top:0;">Cart Totals</h5>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem;">
              <span>Subtotal:</span>
              <b>$59.00</b>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem; color:var(--brand-emerald);">
              <span>Coupon (SUMMER20):</span>
              <b>-$11.80</b>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem;">
              <span>Estimated Tax:</span>
              <b>$0.00</b>
            </div>
            <div style="display:flex; justify-content:space-between; border-top:1px solid var(--border-subtle); padding-top:10px; margin-top:8px; font-size:1.15rem; font-weight:800; color:var(--brand-emerald);">
              <span>Total:</span>
              <span>$47.20</span>
            </div>
            <span class="btn-mockup btn-primary" style="width:100%; text-align:center; margin-top:14px;">Proceed to Checkout →</span>
          </div>
        </div>
      </div>
    `,
    'Figure 15.3: Interactive cart page with quantity updates, coupon redemption box, and shipping preview.'
  ),

  // Figure 15.4: Conversion-Optimized Checkout
  storefront_checkout: wrapWindow(
    'https://omnifywp.com/checkout/',
    'Secure 1-Page Checkout',
    `
      <div class="mockup-storefront">
        <div class="mockup-split-2">
          <div>
            <h4 style="margin-top:0;">1. Contact & Customer Details</h4>
            <div class="mockup-field">
              <label>Email Address for Delivery</label>
              <div class="mockup-input-text">sarah@cyberdyne.org</div>
            </div>

            <h4 style="margin:20px 0 10px;">2. Payment Method</h4>
            <div style="border:1px solid var(--border-accent); border-radius:8px; padding:12px; margin-bottom:12px; background:var(--bg-subtle);">
              <div style="display:flex; align-items:center; justify-content:space-between;">
                <b>● Credit / Debit Card (Stripe)</b>
                <span>🔒 256-Bit SSL</span>
              </div>
              <div class="mockup-field" style="margin-top:10px;">
                <label>Card Number</label>
                <div class="mockup-input-text font-mono">•••• •••• •••• 4242</div>
              </div>
              <div style="display:flex; gap:8px;">
                <div class="mockup-field" style="flex:1;">
                  <label>Expiry</label>
                  <div class="mockup-input-text font-mono">12/28</div>
                </div>
                <div class="mockup-field" style="flex:1;">
                  <label>CVC</label>
                  <div class="mockup-input-text font-mono">•••</div>
                </div>
              </div>
            </div>
          </div>

          <div class="mockup-card-panel">
            <h5 style="margin-top:0;">Order Summary</h5>
            <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:6px;">
              <span>OmniStudio Pro Theme (x1)</span>
              <b>$59.00</b>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.88rem; color:var(--brand-emerald); margin-bottom:6px;">
              <span>Discount (SUMMER20):</span>
              <b>-$11.80</b>
            </div>
            <div style="display:flex; justify-content:space-between; border-top:1px solid var(--border-subtle); padding-top:10px; font-size:1.15rem; font-weight:800; color:var(--brand-emerald); margin-top:8px;">
              <span>Order Total:</span>
              <span>$47.20</span>
            </div>
            <span class="btn-mockup btn-primary" style="width:100%; text-align:center; margin-top:16px; font-size:1rem; padding:12px;">Complete Purchase ($47.20) 🔒</span>
          </div>
        </div>
      </div>
    `,
    'Figure 15.4: Modern multi-step checkout with live order summary, available promo codes, and payment inputs.'
  ),

  // Figure 15.5: Customer Portal & Download Locker
  customer_portal: wrapWindow(
    'https://omnifywp.com/customer-portal/',
    'Customer Portal',
    `
      <div class="mockup-storefront">
        <div class="mockup-bar" style="margin-bottom:14px;">
          <div>
            <h4 style="margin:0;">Welcome, Sarah Connor</h4>
            <span style="font-size:0.8rem; color:var(--text-dim);">Member since Oct 2026 • 4 Orders Placed</span>
          </div>
          <span class="btn-mockup btn-outline">Log Out</span>
        </div>

        <div class="mockup-tabs-bar" style="margin-bottom:16px;">
          <span class="m-tab">My Orders (4)</span>
          <span class="m-tab active">Digital Downloads Locker (2)</span>
          <span class="m-tab">Saved Addresses</span>
          <span class="m-tab">Account Security</span>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:2.5;">Downloadable File Asset</div>
            <div style="flex:1.2;">Associated Order</div>
            <div style="flex:1.5;">Download Access</div>
            <div style="flex:1.2; text-align:right;">Action</div>
          </div>
          <div class="table-row">
            <div style="flex:2.5;">
              <div style="font-weight:700;">omnistudio-pro-v1.2.zip</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">8.4 MB • Latest Version 1.2.0</div>
            </div>
            <div style="flex:1.2;" class="font-mono">#OMN-1042</div>
            <div style="flex:1.5;"><span class="badge success">3 Remaining (2/5 used)</span></div>
            <div style="flex:1.2; text-align:right;"><span class="btn-mockup btn-primary" style="padding:4px 10px; font-size:0.75rem;">Download 📥</span></div>
          </div>
          <div class="table-row">
            <div style="flex:2.5;">
              <div style="font-weight:700;">quickstart-and-licensing.pdf</div>
              <div style="font-size:0.75rem; color:var(--text-dim);">1.2 MB • User Handbook</div>
            </div>
            <div style="flex:1.2;" class="font-mono">#OMN-1042</div>
            <div style="flex:1.5;"><span class="badge success">Unlimited Access</span></div>
            <div style="flex:1.2; text-align:right;"><span class="btn-mockup btn-primary" style="padding:4px 10px; font-size:0.75rem;">Download 📥</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.5: Self-service Customer Portal for past orders, address management, and digital files.'
  ),

  // Figure 15.6: Public Order Tracking Portal
  order_tracking: wrapWindow(
    'https://omnifywp.com/order-tracking/',
    'Public Order Tracking',
    `
      <div class="mockup-storefront">
        <div class="mockup-split-2" style="margin-bottom:16px;">
          <div class="mockup-field">
            <label>Order Number / ID</label>
            <div class="mockup-input-text font-mono">OMN-2026-1042</div>
          </div>
          <div class="mockup-field">
            <label>Billing Email Address</label>
            <div class="mockup-input-text">sarah@cyberdyne.org</div>
          </div>
        </div>

        <div class="mockup-card-panel">
          <div class="mockup-bar" style="margin-bottom:16px;">
            <strong>Order Status: Completed & Dispatched</strong>
            <span class="badge success">Verified Delivery</span>
          </div>

          <div class="wizard-stepper">
            <div class="step completed">
              <span class="step-num">✓</span>
              <span class="step-title">Order Placed</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">✓</span>
              <span class="step-title">Payment Cleared</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">✓</span>
              <span class="step-title">Digital Locker Active</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">✓</span>
              <span class="step-title">Delivered</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.6: Guest order tracking lookup requiring only Order Number and Billing Email.'
  )
};

module.exports = {
  mockups
};
