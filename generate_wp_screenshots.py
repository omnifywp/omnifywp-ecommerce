import os
import subprocess
import time
from PIL import Image

CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
OUTPUT_DIR = os.path.abspath(".wordpress-org")
os.makedirs(OUTPUT_DIR, exist_ok=True)

def render_html_to_png(html_content, output_path, width=1440, height=900):
    temp_html = os.path.abspath(f"temp_render_{int(time.time() * 1000)}.html")
    with open(temp_html, "w", encoding="utf-8") as f:
        f.write(html_content)
    
    file_url = "file:///" + temp_html.replace("\\", "/")
    
    cmd = [
        CHROME_PATH,
        "--headless=new",
        "--disable-gpu",
        "--hide-scrollbars",
        "--force-device-scale-factor=1",
        f"--window-size={width},{height}",
        f"--screenshot={output_path}",
        file_url
    ]
    
    subprocess.run(cmd, check=True)
    if os.path.exists(temp_html):
        os.remove(temp_html)
    
    # Verify dimensions with PIL
    img = Image.open(output_path)
    if img.size != (width, height):
        img = img.resize((width, height), Image.Resampling.LANCZOS)
        img.save(output_path)
    print(f"Generated: {os.path.basename(output_path)} ({width}x{height}) - {os.path.getsize(output_path)} bytes")

# Shared base CSS for admin screens
ADMIN_BASE_CSS = """
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1440px;
    height: 900px;
    overflow: hidden;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
    -webkit-font-smoothing: antialiased;
  }
  
  .browser-header {
    height: 38px;
    background: #1e293b;
    display: flex;
    align-items: center;
    padding: 0 16px;
    gap: 14px;
    border-bottom: 1px solid #334155;
  }
  .mac-buttons { display: flex; gap: 8px; }
  .mac-btn { width: 11px; height: 11px; border-radius: 50%; }
  .btn-close { background: #ef4444; }
  .btn-min { background: #f59e0b; }
  .btn-max { background: #10b981; }
  
  .browser-url-bar {
    flex: 1;
    max-width: 680px;
    height: 24px;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 6px;
    display: flex;
    align-items: center;
    padding: 0 10px;
    gap: 8px;
    font-size: 11px;
    color: #94a3b8;
    margin: 0 auto;
    font-family: 'JetBrains Mono', monospace;
  }
  .ssl-lock { width: 11px; height: 11px; fill: #10b981; }
  
  .wp-admin-bar {
    height: 32px;
    background: #1d2327;
    color: #c3c4c7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    font-size: 12px;
  }
  .wp-admin-bar-left { display: flex; align-items: center; gap: 16px; }
  .wp-logo {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; background: #2c3338; border-radius: 50%;
    color: #fff; font-weight: 800; font-size: 11px;
  }
  .wp-site-name { font-weight: 600; color: #fff; display: flex; align-items: center; gap: 6px; }
  .wp-admin-bar-right { display: flex; align-items: center; gap: 10px; }
  .admin-avatar {
    width: 20px; height: 20px; border-radius: 50%; background: #0f766e;
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700;
  }
  
  .admin-body { display: flex; height: calc(900px - 70px); }
  .wp-sidebar {
    width: 175px; background: #1d2327; color: #8c8f94; padding: 8px 0;
    font-size: 12px; flex-shrink: 0; overflow-y: auto;
  }
  .wp-menu-item { display: flex; align-items: center; padding: 6px 14px; gap: 10px; color: #c3c4c7; }
  .wp-menu-item.active-parent {
    background: #042f2e; color: #fff; border-left: 4px solid #0f766e; font-weight: 700;
  }
  .wp-submenu { background: #101517; padding: 3px 0; }
  .wp-submenu-item {
    padding: 5px 14px 5px 32px; font-size: 11.5px; color: #a7aaad;
    display: flex; align-items: center; justify-content: space-between;
  }
  .wp-submenu-item.active { color: #5eead4; font-weight: 700; background: rgba(15, 118, 110, 0.2); }
  .badge-count {
    background: #0f766e; color: #fff; font-size: 9px; font-weight: 700;
    padding: 1px 6px; border-radius: 10px;
  }
  
  .admin-content {
    flex: 1; overflow: hidden; padding: 18px 24px; display: flex;
    flex-direction: column; gap: 13px; background: #f8fafc;
  }
  
  .admin-header { display: flex; align-items: center; justify-content: space-between; }
  .admin-title-group h1 {
    font-size: 19px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; letter-spacing: -0.01em;
  }
  .admin-title-group p { font-size: 11.5px; color: #64748b; margin-top: 1px; }
  .admin-actions { display: flex; align-items: center; gap: 8px; }
  
  .btn-primary {
    background: #0f766e; color: #ffffff; border: none; border-radius: 6px;
    padding: 7px 14px; font-size: 11.5px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 1px 2px rgba(15, 118, 110, 0.25);
  }
  .btn-secondary {
    background: #ffffff; color: #334155; border: 1px solid #cbd5e1;
    border-radius: 6px; padding: 7px 12px; font-size: 11.5px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
  }
  .btn-sm { padding: 4px 8px; font-size: 11px; border-radius: 4px; }
  
  .metric-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
  .metric-card {
    background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  .metric-header {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 5px;
  }
  .metric-value { font-size: 21px; font-weight: 800; color: #0f172a; line-height: 1.1; }
  .metric-change {
    margin-top: 5px; display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
  }
  .metric-change.up { color: #0d9488; }
  .metric-change.neutral { color: #64748b; }
  
  .data-table-card {
    background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;
    overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  .table-header-bar {
    padding: 10px 14px; border-bottom: 1px solid #f1f5f9; display: flex;
    align-items: center; justify-content: space-between;
  }
  .table-title { font-size: 12.5px; font-weight: 700; color: #0f172a; }
  table.data-table { width: 100%; border-collapse: collapse; font-size: 11.5px; text-align: left; }
  table.data-table th {
    background: #f8fafc; color: #475569; padding: 8px 12px; font-weight: 600;
    border-bottom: 1px solid #e2e8f0; font-size: 11px;
  }
  table.data-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
  table.data-table tr:last-child td { border-bottom: none; }
  
  .badge {
    display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px;
    border-radius: 10px; font-size: 10.5px; font-weight: 600;
  }
  .badge-success { background: #dcfce7; color: #15803d; }
  .badge-processing { background: #e0f2fe; color: #0369a1; }
  .badge-warning { background: #fef3c7; color: #b45309; }
  .badge-danger { background: #fee2e2; color: #b91c1c; }
  .badge-teal { background: #ccfbf1; color: #0f766e; }
  .badge-purple { background: #f3e8ff; color: #7e22ce; }
  .badge-gray { background: #f1f5f9; color: #64748b; }
"""

def wrap_admin(active_slug, title, subtitle, actions_html, content_html, url_page):
    submenus = [
        ("Dashboard", "dashboard", "omnify-dashboard", ""),
        ("Products", "products", "omnify-products", ""),
        ("Orders", "orders", "omnify-orders", "18"),
        ("Customers", "customers", "omnify-customers", ""),
        ("Coupons", "coupons", "omnify-coupons", ""),
        ("Gateways", "gateways", "omnify-settings&tab=payments", ""),
        ("Emails", "emails", "omnify-settings&tab=emails", ""),
        ("Settings", "settings", "omnify-settings", ""),
        ("Tools", "tools", "omnify-tools", "")
    ]
    submenu_html = ""
    for item in submenus:
        is_active = (item[1] == active_slug)
        active_cls = "active" if is_active else ""
        badge_html = f"<span class='badge-count'>{item[3]}</span>" if item[3] else ""
        submenu_html += f"<div class='wp-submenu-item {active_cls}'><span>{item[0]}</span>{badge_html}</div>"
        
    return f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>{ADMIN_BASE_CSS}</style>
</head>
<body>
  <div class="browser-header">
    <div class="mac-buttons">
      <div class="mac-btn btn-close"></div>
      <div class="mac-btn btn-min"></div>
      <div class="mac-btn btn-max"></div>
    </div>
    <div class="browser-url-bar">
      <svg class="ssl-lock" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
      https://mystore.com/wp-admin/admin.php?page={url_page}
    </div>
  </div>
  
  <div class="wp-admin-bar">
    <div class="wp-admin-bar-left">
      <span class="wp-logo">W</span>
      <span class="wp-site-name">Omnify Demo Store</span>
      <span style="color: #8c8f94;">+ New</span>
    </div>
    <div class="wp-admin-bar-right">
      <span>Howdy, Store Admin</span>
      <div class="admin-avatar">A</div>
    </div>
  </div>
  
  <div class="admin-body">
    <div class="wp-sidebar">
      <div class="wp-menu-item">Dashboard</div>
      <div class="wp-menu-item">Posts</div>
      <div class="wp-menu-item">Media</div>
      <div class="wp-menu-item">Pages</div>
      <div class="wp-menu-item active-parent">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Omnify Store
      </div>
      <div class="wp-submenu">
        {submenu_html}
      </div>
      <div class="wp-menu-item">Plugins</div>
      <div class="wp-menu-item">Users</div>
      <div class="wp-menu-item">Tools</div>
      <div class="wp-menu-item">Settings</div>
    </div>
    
    <div class="admin-content">
      <div class="admin-header">
        <div class="admin-title-group">
          <h1>{title}</h1>
          <p>{subtitle}</p>
        </div>
        <div class="admin-actions">
          {actions_html}
        </div>
      </div>
      {content_html}
    </div>
  </div>
</body>
</html>"""

print("Base setup ready.")
