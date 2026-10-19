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
            <span class="step-num">Done</span>
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
                <span class="qa-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></span>
                <div>
                  <div class="qa-title">+ Add New Product</div>
                  <div class="qa-desc">Publish digital, physical, or variable item</div>
                </div>
              </div>
              <div class="qa-item">
                <span class="qa-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg></span>
                <div>
                  <div class="qa-title">Create Discount Coupon</div>
                  <div class="qa-desc">Percentage or fixed promotional code</div>
                </div>
              </div>
              <div class="qa-item">
                <span class="qa-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
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

  // Figure 4.2: Comprehensive 4-Step Product Wizard & Live Preview
  product_editor: wrapWindow(
    'wp-admin/admin.php?page=omnify-products&action=edit&id=101',
    'Product Editor Wizard',
    `
      <div class="mockup-content">
        <div class="editor-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
          <input type="text" class="editor-title-input" value="Omnify Heavyweight French Terry Hoodie" readonly style="flex:1; font-size:1.1rem; font-weight:700; padding:8px 12px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--bg-surface); color:var(--text-main);" />
          <div style="display:flex; gap:8px;">
            <button type="button" class="btn-mockup btn-outline" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; font-weight:600; font-size:0.82rem; border-color:#0f766e; color:#0f766e;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              Preview Product
            </button>
            <span class="btn-mockup btn-primary" style="background:#0f766e; color:#fff; padding:8px 16px; font-weight:600; font-size:0.82rem; border-radius:8px;">Update Product</span>
          </div>
        </div>

        <!-- 4-Step Wizard Navigation Stepper -->
        <div class="wizard-stepper" style="margin-bottom:20px; background:var(--bg-subtle); padding:12px 18px; border-radius:10px; border:1px solid var(--border-subtle); display:flex; align-items:center; justify-content:space-between;">
          <div class="step completed" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <span class="step-num" style="background:#0f766e; color:#fff; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700;">1</span>
            <div>
              <div style="font-size:0.8rem; font-weight:700; color:#0f766e;">Step 1</div>
              <div style="font-size:0.72rem; color:var(--text-dim);">General &amp; Pricing</div>
            </div>
          </div>
          <div class="step-connector completed" style="flex:1; height:2px; background:#0f766e; margin:0 10px;"></div>
          <div class="step active" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <span class="step-num" style="background:#0f766e; color:#fff; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; box-shadow:0 0 0 3px rgba(15,118,110,0.2);">2</span>
            <div>
              <div style="font-size:0.8rem; font-weight:700; color:#0f766e;">Step 2</div>
              <div style="font-size:0.72rem; color:var(--text-main); font-weight:600;">Inventory &amp; Stock</div>
            </div>
          </div>
          <div class="step-connector" style="flex:1; height:2px; background:var(--border-subtle); margin:0 10px;"></div>
          <div class="step" style="display:flex; align-items:center; gap:8px; opacity:0.7;">
            <span class="step-num" style="background:var(--border-subtle); color:var(--text-dim); width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700;">3</span>
            <div>
              <div style="font-size:0.8rem; font-weight:600; color:var(--text-dim);">Step 3</div>
              <div style="font-size:0.72rem; color:var(--text-dim);">Digital &amp; Media</div>
            </div>
          </div>
          <div class="step-connector" style="flex:1; height:2px; background:var(--border-subtle); margin:0 10px;"></div>
          <div class="step" style="display:flex; align-items:center; gap:8px; opacity:0.7;">
            <span class="step-num" style="background:var(--border-subtle); color:var(--text-dim); width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700;">4</span>
            <div>
              <div style="font-size:0.8rem; font-weight:600; color:var(--text-dim);">Step 4</div>
              <div style="font-size:0.72rem; color:var(--text-dim);">Variations &amp; SEO</div>
            </div>
          </div>
        </div>

        <div class="editor-panel" style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:10px; padding:20px;">
          <div class="mockup-split-2">
            <div>
              <div class="mockup-field">
                <label>Product Type</label>
                <div class="mockup-input-select">Variable Physical Merch (Apparel)</div>
              </div>
              <div class="mockup-form-grid">
                <div class="mockup-field">
                  <label>Regular Price ($)</label>
                  <div class="mockup-input-text">85.00</div>
                </div>
                <div class="mockup-field">
                  <label>Sale Price ($)</label>
                  <div class="mockup-input-text">68.00</div>
                </div>
              </div>
              <div class="mockup-field">
                <label>SKU (Stock Keeping Unit)</label>
                <div class="mockup-input-text">OMN-HD-001</div>
              </div>
              <div class="mockup-field">
                <label>Stock Status</label>
                <div class="mockup-input-select">In Stock (145 units available across variations)</div>
              </div>
            </div>

            <div>
              <div class="mockup-field">
                <label>Developer Extensibility Hooks</label>
                <div style="background:var(--bg-subtle); border:1px dashed #0f766e; border-radius:8px; padding:12px; font-size:0.75rem; font-family:var(--font-mono); color:#0f766e;">
                  <div>// Wizard Step Action Hook</div>
                  <div style="color:var(--text-main); font-weight:600; margin-top:2px;">do_action('omnify_after_product_editor_wizard_step_2', $product);</div>
                  <div style="margin-top:8px;">// Filter Steps Programmatically</div>
                  <div style="color:var(--text-main); font-weight:600; margin-top:2px;">apply_filters('omnify_product_editor_wizard_steps', $steps);</div>
                </div>
              </div>

              <div class="mockup-field" style="margin-top:14px;">
                <label>Live Product Preview Modal Available</label>
                <div style="background:#f0fdfa; border:1px solid #99f6e4; border-radius:8px; padding:12px; font-size:0.8rem; color:#0f766e; display:flex; align-items:center; gap:10px;">
                  <svg width="20" height="20" fill="none" stroke="#0f766e" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  <span>Instant sandbox preview renders full storefront layout without saving draft to public visitors.</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 4.2: Comprehensive 4-step product wizard with preview modal and granular developer action hooks.'
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
            <div style="font-size:1.5rem; margin-bottom:8px;"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
            <div style="font-weight:700;">OmnifyWP Core</div>
            <div style="font-size:0.75rem; color:var(--text-dim);">84 Products • Official</div>
          </div>
          <div class="kpi-box" style="text-align:center;">
            <div style="font-size:1.5rem; margin-bottom:8px;"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg></div>
            <div style="font-weight:700;">CloudSync Labs</div>
            <div style="font-size:0.75rem; color:var(--text-dim);">26 Products • SaaS</div>
          </div>
          <div class="kpi-box" style="text-align:center;">
            <div style="font-size:1.5rem; margin-bottom:8px;"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 10 10 0 0 0 9-6"/></svg></div>
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
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
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
              <span class="f-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
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
              <span style="color:#FBBF24;">5.0 / 5.0 Stars</span>
              <strong style="margin-left:6px;">Sarah Connor</strong>
              <span class="badge success" style="margin-left:6px;">Verified Buyer</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-dim);">Oct 3, 2026</span>
          </div>
          <p style="margin:8px 0; font-size:0.88rem; color:var(--text-main);">"Unbelievable speed. Replaced a bloated WooCommerce setup and our page load time dropped to 0.4s. The cryptographic file locker alone is worth the switch."</p>
          <div class="mockup-bar" style="font-size:0.75rem; color:var(--text-dim);">
            <span>Product: <b>OmniStudio Pro Theme</b></span>
            <div><span class="btn-xs">Approved Done</span> <span class="btn-xs danger">Trash</span></div>
          </div>
        </div>

        <div class="mockup-card-panel">
          <div class="mockup-bar">
            <div>
              <span style="color:#FBBF24;">5.0 / 5.0 Stars</span>
              <strong style="margin-left:6px;">David H.</strong>
              <span class="badge success" style="margin-left:6px;">Verified Buyer</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-dim);">Oct 2, 2026</span>
          </div>
          <p style="margin:8px 0; font-size:0.88rem; color:var(--text-main);">"The clean normalized database tables make running queries in our custom analytics script effortless. No messy wp_postmeta joins."</p>
          <div class="mockup-bar" style="font-size:0.75rem; color:var(--text-dim);">
            <span>Product: <b>CloudSync SaaS Extension</b></span>
            <div><span class="btn-xs">Approved Done</span> <span class="btn-xs danger">Trash</span></div>
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
            <div style="flex:1.2; text-align:right;"><span class="badge success">Recovered Done</span></div>
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
            <span class="g-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
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
            <span class="g-icon"><b>P</b></span>
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
            <span class="g-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></span>
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
            <span class="g-icon"><b>SSL</b></span>
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
            <span class="g-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg></span>
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
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped Done</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Cart</div>
            <div style="flex:1.5;"><code>/cart/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_cart]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped Done</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Checkout</div>
            <div style="flex:1.5;"><code>/checkout/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_checkout]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped Done</span></div>
          </div>
          <div class="table-row">
            <div style="flex:1.5; font-weight:600;">Customer Portal</div>
            <div style="flex:1.5;"><code>/customer-portal/</code></div>
            <div style="flex:2;" class="font-mono">[omnify_customer_portal]</div>
            <div style="flex:1; text-align:right;"><span class="badge success">Mapped Done</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 11.7: Core page mappings linking shortcodes to active WordPress pages.'
  ),

  // Figure 12.1: System Tools & Diagnostics
  tools: wrapWindow(
    'wp-admin/admin.php?page=omnify-tools',
    'System Tools & Seeding',
    `
      <div class="mockup-content">
        <!-- Live 4-KPI Diagnostics Strip -->
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px;">
          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; text-align:center;">
            <div style="font-size:0.7rem; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.05em;">Total Products</div>
            <div style="font-size:1.5rem; font-weight:700; color:#0f172a; margin-top:2px;">48</div>
            <div style="font-size:0.7rem; color:#0f766e; font-weight:600;">Active in Catalog</div>
          </div>
          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; text-align:center;">
            <div style="font-size:0.7rem; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.05em;">Total Orders</div>
            <div style="font-size:1.5rem; font-weight:700; color:#0f172a; margin-top:2px;">124</div>
            <div style="font-size:0.7rem; color:#0f766e; font-weight:600;">Store Transactions</div>
          </div>
          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; text-align:center;">
            <div style="font-size:0.7rem; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.05em;">Total Customers</div>
            <div style="font-size:1.5rem; font-weight:700; color:#0f172a; margin-top:2px;">89</div>
            <div style="font-size:0.7rem; color:#0f766e; font-weight:600;">CRM Profiles</div>
          </div>
          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; text-align:center;">
            <div style="font-size:0.7rem; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.05em;">Total Reviews</div>
            <div style="font-size:1.5rem; font-weight:700; color:#0f172a; margin-top:2px;">36</div>
            <div style="font-size:0.7rem; color:#0f766e; font-weight:600;">Verified Ratings</div>
          </div>
        </div>

        <div class="mockup-split-2">
          <!-- Demo Data Generator Card -->
          <div class="mockup-card-panel" style="border:1px solid #e2e8f0; border-radius:12px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
              <div>
                <h5 style="margin:0; font-size:1rem; color:#0f172a;">Demo Data Generator</h5>
                <p style="font-size:0.8rem; color:#64748b; margin:4px 0 0;">Populate your catalog with 50 products, orders, and reviews.</p>
              </div>
              <span class="badge success" style="font-size:0.7rem;">Safe to Use</span>
            </div>
            <div style="background:#f1f5f9; border-radius:999px; height:8px; overflow:hidden; margin:12px 0 6px;">
              <div style="width:75%; height:100%; background:#0f766e; border-radius:999px;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b; margin-bottom:14px;">
              <span>Populating orders and customers...</span>
              <strong style="color:#0f766e;">75%</strong>
            </div>
            <span class="btn-mockup btn-primary" style="width:100%; justify-content:center;">Generate Demo Catalog</span>
          </div>

          <!-- Granular Cleanup Utilities -->
          <div class="mockup-card-panel" style="border:1px solid #e2e8f0; border-radius:12px; padding:18px;">
            <h5 style="margin:0 0 10px; font-size:1rem; color:#0f172a;">Granular Store Cleanup</h5>
            <div style="display:flex; flex-direction:column; gap:8px;">
              <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                <span style="font-size:0.8rem; font-weight:600; color:#334155;">Clean Product Catalog</span>
                <span class="btn-xs" style="background:#ffffff; border:1px solid #cbd5e1; color:#334155;">Clean (48 items)</span>
              </div>
              <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                <span style="font-size:0.8rem; font-weight:600; color:#334155;">Clean Orders & Invoices</span>
                <span class="btn-xs" style="background:#ffffff; border:1px solid #cbd5e1; color:#334155;">Clean (124 items)</span>
              </div>
              <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 10px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px;">
                <span style="font-size:0.8rem; font-weight:600; color:#991b1b;">Wipe All Store Data</span>
                <span class="btn-xs danger">Reset Store</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 12.1: Real-time database diagnostics strip, animated demo data seeding, and granular cleanup utilities.'
  ),

  // Figure 13.1: Activity Audit Log
  activity_log: wrapWindow(
    'wp-admin/admin.php?page=omnify-activity',
    'Activity Audit Log',
    `
      <div class="mockup-content">
        <!-- Filter Toolbar -->
        <div style="display:flex; gap:10px; flex-wrap:wrap; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:14px; align-items:center;">
          <input type="text" placeholder="Search logs..." value="" style="padding:6px 10px; font-size:0.8rem; border:1px solid #cbd5e1; border-radius:6px; flex:1;" readonly />
          <span style="font-size:0.8rem; padding:6px 10px; background:#ffffff; border:1px solid #cbd5e1; border-radius:6px; color:#334155;">All Actors</span>
          <span style="font-size:0.8rem; padding:6px 10px; background:#ffffff; border:1px solid #cbd5e1; border-radius:6px; color:#334155;">Orders</span>
          <span class="btn-xs btn-primary">Filter</span>
          <span class="btn-xs" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b;">Reset</span>
        </div>

        <div class="mockup-data-table">
          <div class="table-head">
            <div style="flex:1.2;">Date / Time</div>
            <div style="flex:1.2;">Actor</div>
            <div style="flex:0.9;">Category</div>
            <div style="flex:1.2;">Action</div>
            <div style="flex:2.2;">Details</div>
            <div style="flex:1; text-align:right;">IP Address</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2; font-size:0.75rem; color:#64748b;">Oct 15, 2026 14:22</div>
            <div style="flex:1.2; font-weight:600; color:#0f172a;">admin (@john)</div>
            <div style="flex:0.9;"><span style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:0.7rem; padding:2px 7px; border-radius:999px; font-weight:600;">ORDER</span></div>
            <div style="flex:1.2;"><code style="background:#f1f5f9; padding:2px 5px; border-radius:4px; font-size:0.75rem;">order.completed</code></div>
            <div style="flex:2.2; font-size:0.8rem; color:#334155;">Marked Order #1042 as completed and issued access.</div>
            <div style="flex:1; text-align:right; font-size:0.75rem; font-family:monospace; color:#64748b;">192.168.1.1</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2; font-size:0.75rem; color:#64748b;">Oct 15, 2026 13:05</div>
            <div style="flex:1.2; font-weight:600; color:#0f172a;">editor (@sarah)</div>
            <div style="flex:0.9;"><span style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; font-size:0.7rem; padding:2px 7px; border-radius:999px; font-weight:600;">PRODUCT</span></div>
            <div style="flex:1.2;"><code style="background:#f1f5f9; padding:2px 5px; border-radius:4px; font-size:0.75rem;">product.update</code></div>
            <div style="flex:2.2; font-size:0.8rem; color:#334155;">Updated price on Mastering WordPress to $49.00.</div>
            <div style="flex:1; text-align:right; font-size:0.75rem; font-family:monospace; color:#64748b;">192.168.1.45</div>
          </div>
          <div class="table-row">
            <div style="flex:1.2; font-size:0.75rem; color:#64748b;">Oct 15, 2026 11:40</div>
            <div style="flex:1.2; font-weight:600; color:#0f172a;">admin (@john)</div>
            <div style="flex:0.9;"><span style="background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; font-size:0.7rem; padding:2px 7px; border-radius:999px; font-weight:600;">COUPON</span></div>
            <div style="flex:1.2;"><code style="background:#f1f5f9; padding:2px 5px; border-radius:4px; font-size:0.75rem;">coupon.created</code></div>
            <div style="flex:2.2; font-size:0.8rem; color:#334155;">Created discount coupon FLASH30 for 30% off.</div>
            <div style="flex:1; text-align:right; font-size:0.75rem; font-family:monospace; color:#64748b;">192.168.1.1</div>
          </div>
        </div>
      </div>
    `,
    'Figure 13.1: Chronological audit log showing user actions, category badges, and IP metadata.'
  ),

  // Figure 14.1: API Keys & Diagnostics Management
  api_keys: wrapWindow(
    'wp-admin/admin.php?page=omnify-api',
    'REST API Keys & Diagnostics',
    `
      <div class="mockup-content">
        <!-- Subtabs Track -->
        <div style="display:flex; gap:8px; border-bottom:1px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; font-size:0.8rem;">
          <span style="color:#64748b;">Import/Export</span>
          <span style="font-weight:700; color:#0f766e; border-bottom:2px solid #0f766e; padding-bottom:8px;">API Keys</span>
          <span style="color:#64748b;">Activity Log</span>
          <span style="color:#64748b;">System Tools & Seeding</span>
        </div>

        <div style="display:flex; gap:16px;">
          <!-- Sidebar Nav -->
          <div style="width:180px; flex-shrink:0; display:flex; flex-direction:column; gap:4px; font-size:0.8rem;">
            <div style="padding:6px 10px; background:#ecfdf5; color:#047857; font-weight:600; border-radius:6px;">REST API Keys</div>
            <div style="padding:6px 10px; color:#64748b;">Outbound Webhooks</div>
            <div style="padding:6px 10px; color:#64748b;">Payment Status</div>
            <div style="padding:6px 10px; color:#64748b;">URL Checker</div>
          </div>

          <!-- Main API Key List -->
          <div style="flex:1;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <span style="font-weight:700; font-size:0.9rem; color:#0f172a;">Active API Credentials</span>
              <span class="btn-xs btn-primary">+ Generate Key</span>
            </div>

            <div class="mockup-data-table">
              <div class="table-head">
                <div style="flex:1.5;">Description</div>
                <div style="flex:1;">User</div>
                <div style="flex:1.2;">Key Identifier</div>
                <div style="flex:1;">Permissions</div>
                <div style="flex:0.8; text-align:right;">Action</div>
              </div>
              <div class="table-row">
                <div style="flex:1.5; font-weight:600; color:#0f172a;">ERP Sync Service</div>
                <div style="flex:1; color:#334155;">admin</div>
                <div style="flex:1.2;"><code style="font-size:0.75rem;">ck_...49b2</code></div>
                <div style="flex:1;"><span style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:0.7rem; padding:2px 6px; border-radius:999px; font-weight:600;">Read/Write</span></div>
                <div style="flex:0.8; text-align:right;"><span class="btn-xs danger">Revoke</span></div>
              </div>
              <div class="table-row">
                <div style="flex:1.5; font-weight:600; color:#0f172a;">Mobile Store App</div>
                <div style="flex:1; color:#334155;">mobile_svc</div>
                <div style="flex:1.2;"><code style="font-size:0.75rem;">ck_...88ac</code></div>
                <div style="flex:1;"><span style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; font-size:0.7rem; padding:2px 6px; border-radius:999px; font-weight:600;">Read-Only</span></div>
                <div style="flex:0.8; text-align:right;"><span class="btn-xs danger">Revoke</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 14.1: REST API key manager, outbound webhooks, payment status diagnostics, and URL checker.'
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
            <div class="product-card-img">
              <img src="../assets/storefront/images/product-digital-suite.jpg" alt="OmniStudio Creator Suite" loading="lazy">
            </div>
            <div class="product-card-body">
              <div class="product-cat-tag">Digital Software</div>
              <h4 class="product-card-title">OmniStudio Creator Suite</h4>
              <div class="product-stars">5.0 / 5.0 Stars <span style="font-size:0.75rem; color:var(--text-dim);">(42 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$59.00</span>
                <s class="price-old">$79.00</s>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Add to Cart</span>
            </div>
          </div>

          <div class="store-product-card">
            <div class="product-badge-sale" style="background:#0F172A;">Bestseller</div>
            <div class="product-card-img">
              <img src="../assets/storefront/images/product-merch-hoodie.jpg" alt="Omnify Heavyweight Hoodie" loading="lazy">
            </div>
            <div class="product-card-body">
              <div class="product-cat-tag">Apparel & Merch</div>
              <h4 class="product-card-title">Omnify Heavyweight Hoodie</h4>
              <div class="product-stars">5.0 / 5.0 Stars <span style="font-size:0.75rem; color:var(--text-dim);">(28 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$68.00</span>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Select Options</span>
            </div>
          </div>

          <div class="store-product-card">
            <div class="product-card-img">
              <img src="../assets/storefront/images/product-bundle-kit.jpg" alt="All-Access Hardware Bundle" loading="lazy">
            </div>
            <div class="product-card-body">
              <div class="product-cat-tag">Product Bundles</div>
              <h4 class="product-card-title">All-Access Hardware Bundle</h4>
              <div class="product-stars">5.0 / 5.0 Stars <span style="font-size:0.75rem; color:var(--text-dim);">(19 reviews)</span></div>
              <div class="product-price-row">
                <span class="price-current">$199.00</span>
              </div>
              <span class="btn-mockup btn-primary" style="width:100%; text-align:center;">Add to Cart</span>
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.1: Modern catalog grid with high-resolution imagery, verified reviews, sale pills, and quick add-to-cart buttons.'
  ),

  // Figure 15.2: Elessi-Style 3-Column Single Product Page
  storefront_product: wrapWindow(
    'https://omnifywp.com/storefront/omnify-heavyweight-hoodie/',
    'Elessi-Style 3-Column Single Product Page',
    `
      <div class="mockup-storefront" style="padding:16px;">
        <div style="display:grid; grid-template-columns: 1.1fr 1.3fr 0.9fr; gap:20px; align-items:start;">
          <!-- Column 1: Vertical Gallery & Thumbnails -->
          <div class="product-gallery-mock" style="display:flex; gap:12px;">
            <div class="gallery-thumbs" style="display:flex; flex-direction:column; gap:8px; width:64px;">
              <span class="thumb active" style="width:64px; height:64px; border:2px solid #0f766e; border-radius:6px; overflow:hidden;"><img src="../assets/storefront/images/product-merch-hoodie.jpg" alt="Black" style="width:100%;height:100%;object-fit:cover;"></span>
              <span class="thumb" style="width:64px; height:64px; border:1px solid var(--border-subtle); border-radius:6px; overflow:hidden;"><img src="../assets/storefront/images/product-digital-suite.jpg" alt="Preview 2" style="width:100%;height:100%;object-fit:cover;"></span>
              <span class="thumb" style="width:64px; height:64px; border:1px solid var(--border-subtle); border-radius:6px; overflow:hidden;"><img src="../assets/storefront/images/product-bundle-kit.jpg" alt="Preview 3" style="width:100%;height:100%;object-fit:cover;"></span>
            </div>
            <div class="main-gallery-preview" style="flex:1; height:320px; border-radius:10px; overflow:hidden; position:relative; background:#f1f5f9;">
              <img src="../assets/storefront/images/product-merch-hoodie.jpg" alt="Omnify Heavyweight Hoodie" style="width:100%;height:100%;object-fit:cover;">
              <span style="position:absolute; top:10px; left:10px; background:#0f766e; color:#fff; font-size:0.7rem; font-weight:700; padding:3px 8px; border-radius:4px; text-transform:uppercase;">Sale -20%</span>
            </div>
          </div>

          <!-- Column 2: Product Core Details, Swatches & CTA -->
          <div class="product-meta-mock">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
              <span class="badge success" style="background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4; font-size:0.75rem; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                In Stock &bull; Dispatches in 24h
              </span>
              <span style="font-size:0.75rem; color:var(--text-dim);">SKU: <strong>OMN-HD-BLK-M</strong></span>
            </div>

            <h3 style="margin:4px 0 8px; font-size:1.35rem; font-weight:800; color:#0f172a; line-height:1.3;">Omnify Heavyweight French Terry Hoodie</h3>
            
            <div class="product-stars" style="display:flex; align-items:center; gap:6px; font-size:0.82rem; margin-bottom:12px;">
              <span style="color:#d97706; font-weight:700;">&#9733; 5.0 / 5.0</span>
              <span style="color:var(--text-dim);">(42 verified reviews) &bull; 342 sold</span>
            </div>

            <div class="product-price-row" style="display:flex; align-items:baseline; gap:10px; margin-bottom:14px;">
              <span class="price-current" style="font-size:1.75rem; font-weight:800; color:#0f766e;">$68.00</span>
              <s class="price-old" style="font-size:1.05rem; color:#94a3b8;">$85.00</s>
              <span class="badge success" style="background:#ecfdf5; color:#0f766e; font-size:0.75rem; padding:2px 8px; border-radius:6px; font-weight:700;">Save $17.00</span>
            </div>

            <!-- Color Swatches -->
            <div style="margin-bottom:14px;">
              <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:6px;">
                <span style="font-weight:700; color:#334155;">Color: <strong style="color:#0f766e;">Onyx Black</strong></span>
                <span style="color:var(--text-dim);">3 Options</span>
              </div>
              <div style="display:flex; gap:8px;">
                <span class="swatch-btn active" style="width:28px; height:28px; border-radius:50%; background:#1e293b; border:2px solid #fff; outline:2px solid #0f766e; cursor:pointer;" title="Onyx Black"></span>
                <span class="swatch-btn" style="width:28px; height:28px; border-radius:50%; background:#64748b; border:2px solid #fff; outline:1px solid #cbd5e1; cursor:pointer;" title="Heather Gray"></span>
                <span class="swatch-btn" style="width:28px; height:28px; border-radius:50%; background:#0f766e; border:2px solid #fff; outline:1px solid #cbd5e1; cursor:pointer;" title="Teal Green"></span>
              </div>
            </div>

            <!-- Size Pills -->
            <div style="margin-bottom:18px;">
              <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:6px;">
                <span style="font-weight:700; color:#334155;">Size: <strong style="color:#0f766e;">M</strong></span>
                <a href="#" style="color:#0f766e; font-size:0.75rem; text-decoration:none;">Size Guide</a>
              </div>
              <div style="display:flex; gap:8px;">
                <span class="mockup-pill" style="padding:6px 14px; border:1px solid var(--border-subtle); border-radius:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">S</span>
                <span class="mockup-pill active" style="padding:6px 14px; border:2px solid #0f766e; background:#f0fdfa; color:#0f766e; border-radius:6px; font-size:0.8rem; font-weight:700; cursor:pointer;">M</span>
                <span class="mockup-pill" style="padding:6px 14px; border:1px solid var(--border-subtle); border-radius:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">L</span>
                <span class="mockup-pill" style="padding:6px 14px; border:1px solid var(--border-subtle); border-radius:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">XL</span>
              </div>
            </div>

            <!-- Quantity & Actions -->
            <div style="display:flex; gap:10px; margin-bottom:14px;">
              <div style="display:flex; align-items:center; border:1px solid var(--border-subtle); border-radius:8px; overflow:hidden; background:var(--bg-surface);">
                <button type="button" style="padding:8px 12px; border:none; background:transparent; font-weight:700; cursor:pointer;">-</button>
                <span style="padding:8px 12px; font-weight:700; font-size:0.9rem;">1</span>
                <button type="button" style="padding:8px 12px; border:none; background:transparent; font-weight:700; cursor:pointer;">+</button>
              </div>
              <span class="btn-mockup btn-primary" style="flex:2; text-align:center; background:#0f766e; color:#fff; font-weight:700; padding:10px 16px; border-radius:8px; cursor:pointer; font-size:0.9rem;">Add to Cart</span>
              <span class="btn-mockup btn-outline" style="flex:2; text-align:center; border:2px solid #0f766e; color:#0f766e; font-weight:700; padding:10px 16px; border-radius:8px; cursor:pointer; font-size:0.9rem;">Instant Buy Now</span>
            </div>
          </div>

          <!-- Column 3: Elessi Trust Box & Order Bump Sidebar -->
          <div class="product-sidebar-mock" style="background:var(--bg-subtle); border:1px solid var(--border-subtle); border-radius:10px; padding:16px;">
            <h5 style="margin:0 0 12px; font-size:0.88rem; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:0.04em;">Customer Guarantee</h5>
            <div style="display:flex; flex-direction:column; gap:10px; font-size:0.8rem; color:var(--text-main);">
              <div style="display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span><strong>Free Expedited Shipping</strong> over $50</span>
              </div>
              <div style="display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span><strong>30-Day Hassle-Free Returns</strong></span>
              </div>
              <div style="display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span><strong>256-Bit Encrypted Checkout</strong></span>
              </div>
            </div>

            <!-- VIP Add-On Bump Box -->
            <div style="margin-top:16px; padding:12px; background:#fff; border:1px dashed #0f766e; border-radius:8px;">
              <div style="display:flex; align-items:flex-start; gap:8px;">
                <input type="checkbox" checked style="accent-color:#0f766e; margin-top:3px;" />
                <div>
                  <div style="font-weight:700; font-size:0.8rem; color:#0f172a;">Add Heavyweight Beanie +$15</div>
                  <div style="font-size:0.72rem; color:var(--text-dim); margin-top:2px;">Special matching bundled pricing ($10 savings).</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Single Product Tabs -->
        <div style="margin-top:20px; border-top:1px solid var(--border-subtle); padding-top:16px;">
          <div style="display:flex; gap:12px; border-bottom:1px solid var(--border-subtle); padding-bottom:8px; font-size:0.85rem; font-weight:700;">
            <span style="color:#0f766e; border-bottom:2px solid #0f766e; padding-bottom:6px; cursor:pointer;">Description</span>
            <span style="color:var(--text-dim); cursor:pointer;">Technical Specifications</span>
            <span style="color:var(--text-dim); cursor:pointer;">Customer Reviews (42)</span>
            <span style="color:var(--text-dim); cursor:pointer;">Shipping &amp; Care</span>
          </div>
          <p style="margin-top:12px; font-size:0.85rem; color:var(--text-muted); line-height:1.6;">
            Engineered with 450 GSM French terry cotton, double-lined hood, and reinforced flatlock stitching. The Omnify Heavyweight Hoodie sets a new standard for luxury streetwear essentials, offering thermal regulation and shape retention across hundreds of wash cycles.
          </p>
        </div>
      </div>
    `,
    'Figure 15.2: Elessi-style 3-column product details layout with vertical gallery, variation swatches, trust badges, and tab panels.'
  ),

  // Figure 15.3: Shopping Cart Page
  // Figure 15.3: Enhanced Shopping Cart Page with Urgency & Shipping Meter
  storefront_cart: wrapWindow(
    'https://omnifywp.com/cart/',
    'Interactive Shopping Cart',
    `
      <div class="mockup-storefront" style="padding:16px;">
        <!-- Scarcity Countdown Banner -->
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; font-size:0.85rem; color:#92400e;">
          <div style="display:flex; align-items:center; gap:8px; font-weight:600;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Items reserved! Special bundle discount held for:</span>
          </div>
          <span style="font-family:var(--font-mono); font-weight:800; background:#fef3c7; padding:2px 8px; border-radius:6px; color:#b45309;">14:48</span>
        </div>

        <!-- Free Shipping Progress Meter -->
        <div style="background:#f0fdfa; border:1px solid #99f6e4; border-radius:8px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
          <div style="display:flex; align-items:center; gap:8px; color:#0f766e; font-weight:700;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span>Congratulations! You've unlocked <strong>Free Expedited Shipping</strong>!</span>
          </div>
          <div style="width:140px; height:8px; background:#ccfbf1; border-radius:9999px; overflow:hidden;">
            <div style="width:100%; height:100%; background:#0f766e; border-radius:9999px;"></div>
          </div>
        </div>

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
                <div style="flex:3; display:flex; gap:10px; align-items:center;">
                  <img src="../assets/storefront/images/product-merch-hoodie.jpg" alt="Hoodie" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--border-subtle);">
                  <div>
                    <div style="font-weight:700; color:#0f172a;">Omnify Heavyweight Hoodie</div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">Color: Onyx Black &bull; Size: M</div>
                  </div>
                </div>
                <div style="flex:1; font-weight:600;">$68.00</div>
                <div style="flex:1.2;"><span class="qty-stepper" style="border:1px solid var(--border-subtle); padding:4px 10px; border-radius:6px; font-weight:700; cursor:pointer;">- 1 +</span></div>
                <div style="flex:1; text-align:right; font-weight:800; color:#0f766e;">$68.00</div>
              </div>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
              <div class="mockup-input-text" style="flex:1; font-weight:600; color:#0f766e;">SUMMER20</div>
              <span class="btn-mockup btn-outline" style="border-color:#0f766e; color:#0f766e; font-weight:700;">Apply Coupon</span>
            </div>
          </div>

          <div class="mockup-card-panel" style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:10px; padding:18px;">
            <h5 style="margin-top:0; font-size:1rem; font-weight:800; color:#0f172a;">Order Summary</h5>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem;">
              <span>Subtotal:</span>
              <b>$68.00</b>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem; color:#0f766e; font-weight:600;">
              <span>Coupon (SUMMER20):</span>
              <b>-$13.60</b>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.88rem;">
              <span>Shipping:</span>
              <span style="color:#0f766e; font-weight:700;">Free</span>
            </div>
            <div style="display:flex; justify-content:space-between; border-top:1px solid var(--border-subtle); padding-top:12px; margin-top:8px; font-size:1.2rem; font-weight:800; color:#0f766e;">
              <span>Total:</span>
              <span>$54.40</span>
            </div>
            <span class="btn-mockup btn-primary" style="width:100%; text-align:center; margin-top:16px; background:#0f766e; color:#fff; font-weight:700; padding:12px; border-radius:8px; font-size:0.95rem;">Proceed to Checkout &rarr;</span>
          </div>
        </div>
      </div>
    `,
    'Figure 15.3: Interactive cart page featuring real-time scarcity countdown timer, shipping threshold meter, and instant coupon calculation.'
  ),

  // Figure 15.4: Conversion-Optimized Checkout with Express Pay & 10+ Gateways
  storefront_checkout: wrapWindow(
    'https://omnifywp.com/checkout/',
    'Conversion-Optimized Checkout',
    `
      <div class="mockup-storefront" style="padding:16px;">
        <!-- Express Checkout Buttons -->
        <div style="background:var(--bg-subtle); border:1px solid var(--border-subtle); border-radius:10px; padding:14px; margin-bottom:16px; text-align:center;">
          <div style="font-size:0.8rem; font-weight:700; color:var(--text-dim); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.04em;">Express 1-Click Checkout</div>
          <div style="display:flex; gap:10px; justify-content:center;">
            <button type="button" style="background:#000; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:700; font-size:0.88rem; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
              Apple Pay
            </button>
            <button type="button" style="background:#fff; color:#3c4043; border:1px solid #dadce0; padding:10px 24px; border-radius:8px; font-weight:700; font-size:0.88rem; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
              Google Pay
            </button>
          </div>
          <div style="position:relative; margin:16px 0 6px; text-align:center;">
            <div style="height:1px; background:var(--border-subtle);"></div>
            <span style="position:absolute; top:-9px; left:50%; transform:translateX(-50%); background:var(--bg-subtle); padding:0 12px; font-size:0.75rem; color:var(--text-dim); text-transform:uppercase;">Or pay with traditional methods</span>
          </div>
        </div>

        <div class="mockup-split-2">
          <div>
            <h4 style="margin-top:0; font-size:1rem; color:#0f172a; display:flex; align-items:center; gap:8px;">
              <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:#0f766e; color:#fff; font-size:0.75rem; font-weight:700;">1</span>
              Contact &amp; Shipping Address
            </h4>
            <div class="mockup-field">
              <label>Email Address</label>
              <div class="mockup-input-text">sarah@cyberdyne.org</div>
            </div>
            <div class="mockup-field">
              <label>Shipping Destination</label>
              <div class="mockup-input-text">742 Evergreen Terrace, Springfield, OR 97477</div>
            </div>

            <h4 style="margin:20px 0 10px; font-size:1rem; color:#0f172a; display:flex; align-items:center; gap:8px;">
              <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:#0f766e; color:#fff; font-size:0.75rem; font-weight:700;">2</span>
              Payment Method (10+ Gateways Integrated)
            </h4>
            
            <!-- Gateway Cards Selector -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px;">
              <div style="border:2px solid #0f766e; background:#f0fdfa; border-radius:8px; padding:10px; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="radio" name="payment_method" checked style="accent-color:#0f766e;" />
                <span style="font-weight:700; font-size:0.85rem; color:#0f766e;">Credit Card (Stripe)</span>
              </div>
              <div style="border:1px solid var(--border-subtle); background:var(--bg-surface); border-radius:8px; padding:10px; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="radio" name="payment_method" style="accent-color:#0f766e;" />
                <span style="font-weight:600; font-size:0.85rem;">PayPal Express</span>
              </div>
            </div>

            <div style="border:1px solid #99f6e4; border-radius:8px; padding:14px; margin-bottom:12px; background:#f0fdfa;">
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <b style="font-size:0.9rem; color:#0f766e;">Card Details (256-Bit SSL Encrypted)</b>
                <span style="font-size:0.75rem; color:#0f766e; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  PCI-DSS Level 1 Compliant
                </span>
              </div>
              <div class="mockup-field">
                <label>Card Number</label>
                <div class="mockup-input-text font-mono">•••• •••• •••• 4242</div>
              </div>
              <div style="display:flex; gap:8px;">
                <div class="mockup-field" style="flex:1;">
                  <label>Expiry Date</label>
                  <div class="mockup-input-text font-mono">12/28</div>
                </div>
                <div class="mockup-field" style="flex:1;">
                  <label>Security Code (CVC)</label>
                  <div class="mockup-input-text font-mono">•••</div>
                </div>
              </div>
            </div>
          </div>

          <div class="mockup-card-panel" style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:10px; padding:18px;">
            <h5 style="margin-top:0; font-size:1rem; font-weight:800; color:#0f172a;">Order Summary</h5>
            <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:6px;">
              <span>Omnify Heavyweight Hoodie (x1)</span>
              <b>$68.00</b>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.88rem; color:#0f766e; margin-bottom:6px; font-weight:600;">
              <span>Discount (SUMMER20):</span>
              <b>-$13.60</b>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:6px;">
              <span>Shipping:</span>
              <span style="color:#0f766e; font-weight:700;">Free Expedited</span>
            </div>

            <!-- VIP Priority Order Bump -->
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px; margin:12px 0;">
              <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                <input type="checkbox" checked style="accent-color:#0f766e; margin-top:2px;" />
                <div>
                  <div style="font-weight:700; font-size:0.8rem; color:#92400e;">VIP Priority Order Processing (+$4.99)</div>
                  <div style="font-size:0.72rem; color:#b45309;">Jump to the front of warehouse fulfillment queue.</div>
                </div>
              </label>
            </div>

            <div style="display:flex; justify-content:space-between; border-top:1px solid var(--border-subtle); padding-top:12px; font-size:1.2rem; font-weight:800; color:#0f766e; margin-top:8px;">
              <span>Order Total:</span>
              <span>$59.39</span>
            </div>
            <span class="btn-mockup btn-primary" style="width:100%; text-align:center; margin-top:16px; font-size:0.95rem; padding:12px; font-weight:700; background:#0f766e; color:#fff; border-radius:8px;">Complete Order ($59.39)</span>
            <div style="text-align:center; font-size:0.75rem; color:#64748b; margin-top:10px; display:flex; align-items:center; justify-content:center; gap:4px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Guaranteed 256-Bit SSL Safe &amp; Secure Checkout
            </div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.4: Conversion-optimized checkout with Express 1-Click Pay, 10+ payment gateways, and VIP priority order bump.'
  ),

  // Figure 15.5: Customer Portal & Download Locker
  // Figure 15.5: Customer Portal 2.0 & Download Locker
  customer_portal: wrapWindow(
    'https://omnifywp.com/customer-portal/',
    'Customer Portal 2.0 (SupportHub Modern Theme)',
    `
      <div class="mockup-storefront" style="background:#f8fafc;padding:18px;border-radius:12px;">
        <div class="mockup-bar" style="margin-bottom:16px;background:#ffffff;padding:16px 20px;border-radius:12px;border:1px solid var(--border-subtle);box-shadow:0 1px 3px rgba(0,0,0,0.02);">
          <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
              <h4 style="margin:0;font-size:1.1rem;color:#0f172a;">Welcome back, Sarah Connor</h4>
              <span class="badge success" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:0.72rem;padding:2px 10px;border-radius:9999px;font-weight:700;">Active Account</span>
            </div>
            <span style="font-size:0.8rem; color:var(--text-dim);">Member since Oct 2026 &bull; Customer ID #OMN-CUST-8821</span>
          </div>
          <span class="btn-mockup btn-outline" style="border-radius:8px;font-size:0.8rem;padding:6px 14px;border-color:#e2e8f0;color:#64748b;">Sign Out</span>
        </div>

        <!-- 4-Column KPI Stats Grid -->
        <div class="portal-kpi-grid">
          <div class="portal-kpi-card">
            <div class="portal-kpi-label">Lifetime Spent</div>
            <div class="portal-kpi-value">$3,840.50</div>
            <div class="portal-kpi-sub">4 completed orders</div>
          </div>
          <div class="portal-kpi-card">
            <div class="portal-kpi-label">Total Orders</div>
            <div class="portal-kpi-value">4</div>
            <div class="portal-kpi-sub">0 pending dispatch</div>
          </div>
          <div class="portal-kpi-card">
            <div class="portal-kpi-label">Digital Assets</div>
            <div class="portal-kpi-value">2</div>
            <div class="portal-kpi-sub">Instant secure access</div>
          </div>
          <div class="portal-kpi-card">
            <div class="portal-kpi-label">Saved Addresses</div>
            <div class="portal-kpi-value">2</div>
            <div class="portal-kpi-sub">1 default shipping</div>
          </div>
        </div>

        <!-- Live Tab Navigation Bar -->
        <div class="mockup-tabs-bar" style="margin-bottom:16px;">
          <span class="m-tab" style="border-radius:9999px;padding:6px 16px;">Orders <span class="m-pill">4</span></span>
          <span class="m-tab active" style="background:#0f766e;color:#fff;border-color:#0f766e;border-radius:9999px;padding:6px 16px;">Digital Downloads <span class="m-pill" style="background:rgba(255,255,255,0.25);color:#fff;">2</span></span>
          <span class="m-tab" style="border-radius:9999px;padding:6px 16px;">Saved Addresses</span>
          <span class="m-tab" style="border-radius:9999px;padding:6px 16px;">Account Settings</span>
        </div>

        <!-- Downloads Locker Data Table -->
        <div class="mockup-data-table" style="background:#ffffff;border-radius:12px;border:1px solid var(--border-subtle);overflow:hidden;">
          <div class="table-head" style="background:#f8fafc;padding:12px 18px;">
            <div style="flex:2.5;">Downloadable File Asset</div>
            <div style="flex:1.2;">Associated Order</div>
            <div style="flex:1.5;">Download Access</div>
            <div style="flex:1.2; text-align:right;">Action</div>
          </div>
          <div class="table-row" style="padding:14px 18px;">
            <div style="flex:2.5;">
              <div style="font-weight:700;color:#0f172a;">omnistudio-pro-v1.4.2.zip</div>
              <div style="font-size:0.75rem; color:var(--text-dim);margin-top:2px;">8.4 MB &bull; SHA-256 HMAC Protected &bull; Version 1.4.2</div>
            </div>
            <div style="flex:1.2;" class="font-mono" style="color:#0f766e;font-weight:600;">#OMN-1042</div>
            <div style="flex:1.5;"><span class="badge success" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">3 Remaining (2/5 used)</span></div>
            <div style="flex:1.2; text-align:right;"><span class="btn-mockup btn-primary" style="background:#0f766e;padding:6px 14px; font-size:0.78rem; border-radius:8px;font-weight:600;">Download</span></div>
          </div>
          <div class="table-row" style="padding:14px 18px;">
            <div style="flex:2.5;">
              <div style="font-weight:700;color:#0f172a;">quickstart-and-licensing.pdf</div>
              <div style="font-size:0.75rem; color:var(--text-dim);margin-top:2px;">1.2 MB &bull; Comprehensive Storefront &amp; API Guide</div>
            </div>
            <div style="flex:1.2;" class="font-mono" style="color:#0f766e;font-weight:600;">#OMN-1042</div>
            <div style="flex:1.5;"><span class="badge success" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">Unlimited Access</span></div>
            <div style="flex:1.2; text-align:right;"><span class="btn-mockup btn-primary" style="background:#0f766e;padding:6px 14px; font-size:0.78rem; border-radius:8px;font-weight:600;">Download</span></div>
          </div>
        </div>
      </div>
    `,
    'Figure 15.5: Customer Portal 2.0 self-service hub with 4-column KPI analytics, live tab pills, and cryptographic download locker.'
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
              <span class="step-num">Done</span>
              <span class="step-title">Order Placed</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">Done</span>
              <span class="step-title">Payment Cleared</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">Done</span>
              <span class="step-title">Digital Locker Active</span>
            </div>
            <div class="step-connector completed"></div>
            <div class="step completed">
              <span class="step-num">Done</span>
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
