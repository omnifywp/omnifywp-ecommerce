<?php
define('DB_HOST', '127.0.0.1:10005');
$_SERVER['HTTP_HOST'] = 'wp.local';
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
global $wpdb;
$attachments = $wpdb->get_results("SELECT ID, post_title, guid FROM {$wpdb->posts} WHERE post_type = 'attachment'", ARRAY_A);
echo "ATTACHMENTS (" . count($attachments) . "):\n";
foreach ($attachments as $a) {
    echo "ID: " . $a['ID'] . " | Title: " . $a['post_title'] . " | GUID: " . $a['guid'] . "\n";
}
