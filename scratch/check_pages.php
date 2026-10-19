<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$pages = get_posts([
    'post_type'   => 'page',
    'numberposts' => -1,
    'post_status' => 'any',
]);

foreach ($pages as $p) {
    $tmpl = get_post_meta($p->ID, '_wp_page_template', true);
    echo "ID: {$p->ID} | Title: {$p->post_title} | Slug: {$p->post_name} | Template: {$tmpl}\n";
}
