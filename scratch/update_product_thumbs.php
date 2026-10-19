<?php
define('DB_HOST', '127.0.0.1:10005');
$_SERVER['HTTP_HOST'] = 'wp.local';
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
global $wpdb;

$map = [
    'wallet' => 117,
    'e-book' => 118,
    't-shirt' => 119,
    'travel kit' => 120,
    'bundle' => 120,
];

$products = $wpdb->get_results("SELECT id, name, thumbnail_id FROM {$wpdb->prefix}omnify_products", ARRAY_A);
foreach ($products as $p) {
    $pname_lc = strtolower($p['name']);
    $target_tid = 118; // default ebook
    if (strpos($pname_lc, 'wallet') !== false) {
        $target_tid = 117;
    } elseif (strpos($pname_lc, 't-shirt') !== false || strpos($pname_lc, 'shirt') !== false) {
        $target_tid = 119;
    } elseif (strpos($pname_lc, 'travel kit') !== false || strpos($pname_lc, 'kit') !== false || strpos($pname_lc, 'bundle') !== false) {
        $target_tid = 120;
    }
    $wpdb->update(
        "{$wpdb->prefix}omnify_products",
        ['thumbnail_id' => $target_tid],
        ['id' => $p['id']],
        ['%d'],
        ['%d']
    );
    echo "Updated Product {$p['id']} ({$p['name']}) -> thumbnail_id: $target_tid\n";
}
