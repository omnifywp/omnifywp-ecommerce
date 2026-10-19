<?php
define('DB_HOST', '127.0.0.1:10005');
$_SERVER['HTTP_HOST'] = 'wp.local';
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
$repo = omnify_plugin()->container()->get(\Omnify\eCommerce\Repositories\Omnify_Product_Repository::class);
$all = $repo->all();
echo "Total products: " . count($all) . "\n";
foreach ($all as $p) {
    echo "ID {$p['id']} ({$p['name']}): thumbnail_id = {$p['thumbnail_id']} | thumbnail_url = {$p['thumbnail_url']}\n";
}
