<?php
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
$posts_order = [93, 98, 97, 96, 95, 94];
$now = current_time('timestamp');
foreach ($posts_order as $idx => $id) {
    $d = date('Y-m-d H:i:s', $now - ($idx * 300));
    $g = gmdate('Y-m-d H:i:s', time() - ($idx * 300));
    wp_update_post([
        'ID' => $id,
        'post_status' => 'publish',
        'post_date' => $d,
        'post_date_gmt' => $g
    ]);
    echo "Post $id updated to $d (status: " . get_post_status($id) . ")\n";
}
