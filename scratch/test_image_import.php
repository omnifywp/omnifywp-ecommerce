<?php
define('DB_HOST', '127.0.0.1:10005');
$_SERVER['HTTP_HOST'] = 'wp.local';
require 'c:/Users/rashe/Local Sites/wp/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

function get_or_create_product_attachment(string $source_path, string $target_filename, string $title): int {
    global $wpdb;
    // Check if attachment with this title or filename already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_title = %s LIMIT 1",
        $title
    ));
    if ($existing) {
        return (int) $existing;
    }

    $upload_dir = wp_upload_dir();
    $target_path = $upload_dir['path'] . '/' . $target_filename;
    copy($source_path, $target_path);

    $filetype = wp_check_filetype($target_filename, null);
    $attachment = [
        'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
        'post_title'     => $title,
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $target_path);
    if (! is_wp_error($attach_id) && $attach_id > 0) {
        $attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        return (int) $attach_id;
    }
    return 0;
}

$base_dir = 'c:/Users/rashe/Local Sites/wp/app/public/wp-content/plugins/omnifywp-ecommerce/assets/storefront/images/';

$wallet_id = get_or_create_product_attachment($base_dir . 'product-merch-hoodie.jpg', 'demo-leather-wallet.jpg', 'Premium Leather Wallet');
$ebook_id  = get_or_create_product_attachment($base_dir . 'product-digital-suite.jpg', 'demo-wordpress-ebook.jpg', 'Mastering WordPress & PHP E-Book');
$shirt_id   = get_or_create_product_attachment($base_dir . 'product-merch-hoodie.jpg', 'demo-cotton-tshirt.jpg', 'Classic Cotton T-Shirt');
$bundle_id = get_or_create_product_attachment($base_dir . 'product-bundle-kit.jpg', 'demo-developer-kit.jpg', 'Modern Developer Travel Kit');

echo "ATTACHMENT IDs:\n";
echo "Wallet: $wallet_id (" . wp_get_attachment_image_url($wallet_id, 'full') . ")\n";
echo "EBook: $ebook_id (" . wp_get_attachment_image_url($ebook_id, 'full') . ")\n";
echo "T-Shirt: $shirt_id (" . wp_get_attachment_image_url($shirt_id, 'full') . ")\n";
echo "Bundle: $bundle_id (" . wp_get_attachment_image_url($bundle_id, 'full') . ")\n";
