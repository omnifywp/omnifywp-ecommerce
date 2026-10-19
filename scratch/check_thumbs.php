<?php
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
foreach ([93, 94, 95, 96, 97, 98] as $p) {
    $thumb_id = get_post_thumbnail_id($p);
    $url = get_the_post_thumbnail_url($p);
    $html = get_the_post_thumbnail($p, 'large');
    echo "Post $p: thumb_id=$thumb_id url=$url html=" . (empty($html) ? 'EMPTY' : substr($html, 0, 80)) . "\n";
}
