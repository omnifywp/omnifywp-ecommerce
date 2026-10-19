<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$posts = get_posts([
    'post_type'   => 'post',
    'numberposts' => -1,
    'post_status' => 'any',
]);

echo "Found " . count($posts) . " posts:\n";
foreach ($posts as $p) {
    echo "ID: {$p->ID} | Title: {$p->post_title} | Slug: {$p->post_name} | Status: {$p->post_status}\n";
}
