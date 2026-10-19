const fs = require('fs');

// order-detail.php line 554
let od = fs.readFileSync('templates/admin/order-detail.php', 'utf8');
od = od.replace(/<h2>\u{1F6E1}[\uFE00-\uFE0F]?\s*/u, '<h2><span class="dashicons dashicons-shield" style="font-size:18px; width:18px; height:18px; vertical-align:middle; margin-right:6px;"></span>');
fs.writeFileSync('templates/admin/order-detail.php', od, 'utf8');

// reviews.php line 216
let rev = fs.readFileSync('templates/admin/reviews.php', 'utf8');
rev = rev.replace(
  "<?php for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) : ?><?php echo $omnify_i <= $omnify_rating ? '★' : '☆'; ?><?php endfor; ?>",
  '<?php for ($omnify_i = 1; $omnify_i <= 5; $omnify_i++) : ?><span class="dashicons <?php echo $omnify_i <= $omnify_rating ? "dashicons-star-filled" : "dashicons-star-empty"; ?>" style="font-size:15px; width:15px; height:15px; color:#f59e0b;"></span><?php endfor; ?>'
);
fs.writeFileSync('templates/admin/reviews.php', rev, 'utf8');

// grid.php line 818
let grid = fs.readFileSync('templates/storefront/grid.php', 'utf8');
grid = grid.replace(
  '<span class="omnify-stars">★★★★★</span>',
  '<span class="omnify-stars"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></span>'
);
fs.writeFileSync('templates/storefront/grid.php', grid, 'utf8');

// product-single.php line 1287
let ps = fs.readFileSync('templates/storefront/product-single.php', 'utf8');
ps = ps.replace(
  /<span class="stars" style="color:#f59e0b; letter-spacing:1px;">[\s\S]*?<\/span>/,
  '<span class="stars" style="color:#f59e0b; display:inline-flex; align-items:center;"><?php for ($omnify_si = 1; $omnify_si <= 5; $omnify_si++) : ?><span class="dashicons <?php echo $omnify_si <= round($omnify_rp_avg_rating) ? "dashicons-star-filled" : "dashicons-star-empty"; ?>" style="font-size:13px; width:13px; height:13px;"></span><?php endfor; ?></span>'
);
fs.writeFileSync('templates/storefront/product-single.php', ps, 'utf8');

console.log('Done cleaning remaining star and shield symbols');
