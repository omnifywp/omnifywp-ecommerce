<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$p = get_post(44);
echo "TITLE: " . $p->post_title . "\n";
echo "STATUS: " . $p->post_status . "\n";
echo "CONTENT:\n" . $p->post_content . "\n";
