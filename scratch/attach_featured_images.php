<?php
define('WP_USE_THEMES', false);
require_once 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$posts_banners = [
    93 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner1.png',
    94 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner2.png',
    95 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner3.png',
    96 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner4.png',
    97 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner5.png',
    98 => 'C:/Users/rashe/.gemini/antigravity/brain/2aa41e5f-5a0b-4839-8389-12aa50655bc5/banner6.png',
];

$wp_upload_dir = wp_upload_dir();

foreach ($posts_banners as $post_id => $source_file) {
    if (!file_exists($source_file)) {
        echo "File not found: $source_file\n";
        continue;
    }

    $filename = basename($source_file);
    $unique_name = 'blog-cover-post-' . $post_id . '.png';
    $target_file = $wp_upload_dir['path'] . '/' . $unique_name;

    copy($source_file, $target_file);

    $attachment = [
        'guid'           => $wp_upload_dir['url'] . '/' . $unique_name,
        'post_mime_type' => 'image/png',
        'post_title'     => preg_replace('/\.[^.]+$/', '', get_the_title($post_id)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $target_file, $post_id);

    // Generate metadata
    $attach_data = wp_generate_attachment_metadata($attach_id, $target_file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Set as featured image
    set_post_thumbnail($post_id, $attach_id);

    echo "Assigned featured image $attach_id to post $post_id (" . get_the_title($post_id) . ")\n";
}

echo "Done attaching featured images!\n";
