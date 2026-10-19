<?php
define('DB_HOST', '127.0.0.1:10005');
$_SERVER['HTTP_HOST'] = 'wp.local';
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
global $wpdb;
$products = $wpdb->get_results("SELECT id, name, slug, thumbnail_id, gallery_ids, type FROM {$wpdb->prefix}omnify_products", ARRAY_A);
echo "PRODUCTS (" . count($products) . "):\n";
foreach ($products as $p) {
    $tid = $p['thumbnail_id'];
    $turl = $tid ? wp_get_attachment_image_url((int)$tid, 'full') : '';
    echo "ID: " . $p['id'] . " | Name: " . $p['name'] . " | thumbnail_id: " . var_export($tid, true) . " | url: " . var_export($turl, true) . " | gallery_ids: " . var_export($p['gallery_ids'], true) . "\n";
}
