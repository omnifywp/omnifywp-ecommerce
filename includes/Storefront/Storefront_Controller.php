<?php
/**
 * Frontend storefront and customer portal.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Storefront;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Repositories\Omnify_Customer_Access_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;
use Omnify\eCommerce\Repositories\Omnify_Download_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_File_Repository;
use Omnify\eCommerce\Repositories\Omnify_Product_Repository;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Review_Repository;
use Omnify\eCommerce\Repositories\Omnify_Wishlist_Repository;
use Omnify\eCommerce\Downloads\Omnify_Signed_Url_Service;
use Omnify\eCommerce\Support\Omnify_Email_Service;
use Omnify\eCommerce\Support\Omnify_Locations;

class Omnify_Storefront_Controller {
	public function __construct(
		private Omnify_Product_Repository $omnify_products,
		private Omnify_Product_File_Repository $omnify_files,
		private Omnify_Customer_Repository $omnify_customers,
		private Omnify_Customer_Access_Repository $omnify_access,
		private Omnify_Signed_Url_Service $omnify_signed_urls,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Download_Repository $omnify_downloads,
		private Omnify_Review_Repository $omnify_reviews,
		private Omnify_Email_Service $omnify_emails,
		private Omnify_Wishlist_Repository $omnify_wishlists
	) {}

	public function register(): void {
		add_shortcode('omnify_storefront', [$this, 'render_storefront']);
		add_shortcode('omnify_cart', [$this, 'render_cart']);
		add_shortcode('omnify_mini_cart', [$this, 'render_mini_cart']);
		add_shortcode('omnify_customer_portal', [$this, 'render_customer_portal']);
		add_shortcode('omnify_checkout', [$this, 'render_checkout']);
		add_shortcode('omnify_order_tracking', [$this, 'render_order_tracking']);
		// Map legacy shortcode
		add_shortcode('omnify_customer_downloads', [$this, 'render_customer_portal']);

		add_action('wp_head', [$this, 'maybe_disable_core_canonical'], 0);
		add_action('wp_head', [$this, 'render_product_seo_tags'], 3);
		add_filter('pre_get_document_title', [$this, 'filter_document_title']);
		add_filter('document_title_parts', [$this, 'filter_document_title_parts']);
		add_filter('wpseo_title', [$this, 'filter_seo_title']);
		add_filter('wpseo_metadesc', [$this, 'filter_seo_description']);
		add_filter('wpseo_canonical', [$this, 'filter_seo_canonical']);
		add_filter('wpseo_opengraph_title', [$this, 'filter_seo_title']);
		add_filter('wpseo_opengraph_desc', [$this, 'filter_seo_description']);
		add_filter('wpseo_opengraph_url', [$this, 'filter_seo_canonical']);
		add_filter('wpseo_opengraph_image', [$this, 'filter_seo_image']);
		add_filter('rank_math/frontend/title', [$this, 'filter_seo_title']);
		add_filter('rank_math/frontend/description', [$this, 'filter_seo_description']);
		add_filter('rank_math/frontend/canonical', [$this, 'filter_seo_canonical']);
		add_filter('rank_math/opengraph/facebook/title', [$this, 'filter_seo_title']);
		add_filter('rank_math/opengraph/facebook/description', [$this, 'filter_seo_description']);
		add_filter('rank_math/opengraph/facebook/url', [$this, 'filter_seo_canonical']);
		add_filter('rank_math/opengraph/facebook/image', [$this, 'filter_seo_image']);
		add_filter('body_class', [$this, 'add_storefront_body_classes']);
		add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_menu_mini_cart_assets']);
		add_filter('wp_nav_menu_items', [$this, 'append_mini_cart_to_menu'], 20, 2);
		$this->register_product_sitemap();

		// Handle review form submission (new review + edit)
		add_action('admin_post_omnify_submit_review', [$this, 'handle_submit_review']);
		add_action('admin_post_nopriv_omnify_submit_review', [$this, 'handle_submit_review']);
	}

	/**
	 * Handle frontend review submission: create or update.
	 * Posted to admin-post.php with action=omnify_submit_review.
	 */
	public function handle_submit_review(): void {
		// Require logged-in user
		if (! is_user_logged_in()) {
			wp_die(esc_html__('You must be logged in to submit a review.', 'omnifywp-ecommerce'), 403);
		}

		$omnify_product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
		if (! $omnify_product_id) {
			wp_die(esc_html__('Invalid product.', 'omnifywp-ecommerce'), 400);
		}

		// Verify nonce
		$omnify_nonce = isset($_POST['omnify_review_nonce']) ? sanitize_text_field(wp_unslash($_POST['omnify_review_nonce'])) : '';
		if (! wp_verify_nonce($omnify_nonce, 'omnify_submit_review_' . $omnify_product_id)) {
			wp_die(esc_html__('Security check failed.', 'omnifywp-ecommerce'), 403);
		}

		$omnify_wp_user  = wp_get_current_user();
		$omnify_user_email = sanitize_email($omnify_wp_user->user_email);
		$omnify_rating   = isset($_POST['rating']) ? min(5, max(1, absint(wp_unslash($_POST['rating'])))) : 0;
		$omnify_content  = isset($_POST['content']) ? trim((string) sanitize_textarea_field(wp_unslash($_POST['content']))) : '';

		// Build redirect URL back to the product page
		$omnify_settings = get_option('omnify_settings', []);
		$omnify_storefront_page_id = ! empty($omnify_settings['page_storefront']) ? absint($omnify_settings['page_storefront']) : 0;
		$omnify_storefront_url = $omnify_storefront_page_id ? get_permalink($omnify_storefront_page_id) : home_url('/');

		// Get product slug for redirect
		$omnify_product = $this->omnify_products->find($omnify_product_id);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '')) {
			wp_die(esc_html__('Product not found.', 'omnifywp-ecommerce'), 404);
		}
		$omnify_product_slug = $omnify_product['slug'] ?? '';
		$omnify_redirect_base = add_query_arg('omnify_product', $omnify_product_slug, $omnify_storefront_url);
		$omnify_redirect_base = $omnify_redirect_base . '#omnify-reviews-pane';

		if (! $omnify_rating) {
			wp_safe_redirect(add_query_arg('review', 'no_rating', $omnify_redirect_base));
			exit;
		}

		if ('' === $omnify_content) {
			wp_safe_redirect(add_query_arg('review', 'no_content', $omnify_redirect_base));
			exit;
		}

		// Handle image uploads
		$omnify_image_urls = [];
		if (isset($_FILES['review_images']) && is_array($_FILES['review_images']) && ! empty($_FILES['review_images']['name'][0])) {
			if (! function_exists('wp_handle_upload')) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_names     = (array) wp_unslash($_FILES['review_images']['name']);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_types     = (array) wp_unslash($_FILES['review_images']['type'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_tmp_names = (array) wp_unslash($_FILES['review_images']['tmp_name'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_errors    = (array) wp_unslash($_FILES['review_images']['error'] ?? []);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_sizes     = (array) wp_unslash($_FILES['review_images']['size'] ?? []);

			foreach ($omnify_names as $omnify_idx => $omnify_fname) {
				if (empty($omnify_fname)) {
					continue;
				}
				$omnify_fname_sanitized = sanitize_file_name($omnify_fname);
				$omnify_type            = sanitize_text_field($omnify_types[$omnify_idx] ?? '');
				$omnify_tmp             = sanitize_text_field($omnify_tmp_names[$omnify_idx] ?? '');
				$omnify_err             = isset($omnify_errors[$omnify_idx]) ? (int) $omnify_errors[$omnify_idx] : 0;
				$omnify_sz              = isset($omnify_sizes[$omnify_idx]) ? (int) $omnify_sizes[$omnify_idx] : 0;

				$omnify_file = [
					'name'     => $omnify_fname_sanitized,
					'type'     => $omnify_type,
					'tmp_name' => $omnify_tmp,
					'error'    => $omnify_err,
					'size'     => $omnify_sz,
				];
				$omnify_upload = wp_handle_upload($omnify_file, ['test_form' => false]);
				if (! empty($omnify_upload['url'])) {
					$omnify_image_urls[] = $omnify_upload['url'];
				}
				if (count($omnify_image_urls) >= 5) {
					break;
				}
			}
		}

		$omnify_review_id = isset($_POST['review_id']) ? absint(wp_unslash($_POST['review_id'])) : 0;

		if ($omnify_review_id > 0) {
			// --- EDIT existing review ---
			// Security: verify the review belongs to the current user
			$omnify_existing = $this->omnify_reviews->find($omnify_review_id);
			if (! $omnify_existing || sanitize_email($omnify_existing['customer_email']) !== $omnify_user_email) {
				wp_die(esc_html__('You do not have permission to edit this review.', 'omnifywp-ecommerce'), 403);
			}

			$omnify_update_data = [
				'rating'         => $omnify_rating,
				'review_content' => $omnify_content,
				'status'         => 'pending', // reset to pending after edit
			];

			if (! empty($omnify_image_urls)) {
				$omnify_update_data['review_content'] .= "\n[media]" . wp_json_encode($omnify_image_urls) . '[/media]';
			}

			$this->omnify_reviews->update($omnify_review_id, $omnify_update_data);
			wp_safe_redirect(add_query_arg('review', 'updated', $omnify_redirect_base));
			exit;
		}

		// --- CREATE new review ---
		$omnify_name = '';
		$omnify_name = trim($omnify_wp_user->first_name . ' ' . $omnify_wp_user->last_name);
		if (empty($omnify_name)) {
			$omnify_name = $omnify_wp_user->display_name;
		}

		$omnify_review_data = [
			'product_id'     => $omnify_product_id,
			'customer_name'  => $omnify_name,
			'customer_email' => $omnify_user_email,
			'rating'         => $omnify_rating,
			'review_content' => $omnify_content,
			'status'         => 'pending',
		];

		if (! empty($omnify_image_urls)) {
			$omnify_review_data['media'] = $omnify_image_urls;
		}

		$this->omnify_reviews->create($omnify_review_data);
		wp_safe_redirect(add_query_arg('review', 'submitted', $omnify_redirect_base));
		exit;
	}

	public function maybe_enqueue_menu_mini_cart_assets(): void {
		$omnify_settings = get_option('omnify_settings', []);
		if (! empty($omnify_settings['mini_cart_menu_enabled'])) {
			$this->enqueue_assets();
		}
	}

	public function add_storefront_body_classes(array $omnify_classes): array {
		if (is_admin()) {
			return $omnify_classes;
		}

		$post = get_post();
		if (! $post) {
			return $omnify_classes;
		}

		$omnify_content = (string) $post->post_content;
		$omnify_has_any = false;
		$omnify_context = '';

		if (has_shortcode($omnify_content, 'omnify_storefront')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-storefront-page';
			$omnify_has_any = true;
			$omnify_context = 'storefront';
		}
		if (has_shortcode($omnify_content, 'omnify_cart')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-cart-page';
			$omnify_has_any = true;
			$omnify_context = 'cart';
		}
		if (has_shortcode($omnify_content, 'omnify_customer_portal') || has_shortcode($omnify_content, 'omnify_customer_downloads')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-portal-page';
			$omnify_has_any = true;
			$omnify_context = 'portal';
		}
		if (has_shortcode($omnify_content, 'omnify_checkout')) {
			$omnify_classes[] = 'omnify-page';
			$omnify_classes[] = 'omnify-checkout-page';
			$omnify_has_any = true;
			$omnify_context = 'checkout';
		}

		if ($omnify_has_any) {
			return apply_filters('omnify_storefront_body_classes', $omnify_classes, $omnify_context);
		}

		return $omnify_classes;
	}

	public function maybe_disable_core_canonical(): void {
		if ($this->current_seo_product()) {
			remove_action('wp_head', 'rel_canonical');
		}
	}

	public function filter_document_title(string $omnify_title): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['title'] : $omnify_title;
	}

	public function filter_document_title_parts(array $omnify_parts): array {
		$omnify_payload = $this->current_product_seo_payload();
		if (! $omnify_payload) {
			return $omnify_parts;
		}

		$omnify_parts['title'] = $omnify_payload['name'];

		return $omnify_parts;
	}

	public function filter_seo_title(string $omnify_title): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['title'] : $omnify_title;
	}

	public function filter_seo_description(string $omnify_description): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['description'] : $omnify_description;
	}

	public function filter_seo_canonical(string $omnify_canonical): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload ? $omnify_payload['url'] : $omnify_canonical;
	}

	public function filter_seo_image(string $omnify_image): string {
		$omnify_payload = $this->current_product_seo_payload();

		return $omnify_payload && ! empty($omnify_payload['image']) ? $omnify_payload['image'] : $omnify_image;
	}

	public function render_product_seo_tags(): void {
		$omnify_payload = $this->current_product_seo_payload();
		if (! $omnify_payload) {
			return;
		}

		echo "\n" . '<!-- Omnify product SEO -->' . "\n";
		if (! $this->external_seo_plugin_active()) {
			printf('<link rel="canonical" href="%s" />' . "\n", esc_url($omnify_payload['url']));
			printf('<meta name="description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			printf('<meta property="og:type" content="product" />' . "\n");
			printf('<meta property="og:title" content="%s" />' . "\n", esc_attr($omnify_payload['title']));
			printf('<meta property="og:description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			printf('<meta property="og:url" content="%s" />' . "\n", esc_url($omnify_payload['url']));
			printf('<meta property="og:site_name" content="%s" />' . "\n", esc_attr(get_bloginfo('name')));
			printf('<meta property="product:price:amount" content="%s" />' . "\n", esc_attr(number_format((float) $omnify_payload['price'], 2, '.', '')));
			printf('<meta property="product:price:currency" content="%s" />' . "\n", esc_attr($omnify_payload['currency']));
			printf('<meta name="twitter:card" content="%s" />' . "\n", $omnify_payload['image'] ? 'summary_large_image' : 'summary');
			printf('<meta name="twitter:title" content="%s" />' . "\n", esc_attr($omnify_payload['title']));
			printf('<meta name="twitter:description" content="%s" />' . "\n", esc_attr($omnify_payload['description']));
			if ($omnify_payload['image']) {
				printf('<meta property="og:image" content="%s" />' . "\n", esc_url($omnify_payload['image']));
			}
		}
		printf('<script type="application/ld+json">%s</script>' . "\n", wp_json_encode($omnify_payload['schema'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE));
		echo '<!-- /Omnify product SEO -->' . "\n";
	}

	public function register_product_sitemap(): void {
		if (! function_exists('wp_sitemaps_register_provider') || ! class_exists('\WP_Sitemaps_Provider')) {
			return;
		}

		$omnify_controller = $this;
		$omnify_provider = new class($omnify_controller) extends \WP_Sitemaps_Provider {
			public function __construct(private Omnify_Storefront_Controller $omnify_controller) {
				$this->name        = 'omnify-products';
				$this->object_type = 'omnify-products';
			}

			public function get_url_list($omnify_page_num, $omnify_object_subtype = '') {
				return $this->omnify_controller->product_sitemap_urls((int) $omnify_page_num);
			}

			public function get_max_num_pages($omnify_object_subtype = '') {
				return $this->omnify_controller->product_sitemap_page_count();
			}
		};

		wp_sitemaps_register_provider('omnify-products', $omnify_provider);
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	public function product_sitemap_urls(int $omnify_page_num): array {
		$omnify_page_num = max(1, $omnify_page_num);
		$omnify_products = $this->omnify_products->all([
			'status'   => 'published',
			'per_page' => 100,
			'offset'   => ($omnify_page_num - 1) * 100,
		]);

		$omnify_urls = [];
		foreach ($omnify_products as $omnify_product) {
			$omnify_urls[] = [
				'loc'     => $this->product_url($omnify_product),
				'lastmod' => ! empty($omnify_product['updated_at']) ? mysql2date('c', (string) $omnify_product['updated_at'], false) : current_time('c'),
			];
		}

		return $omnify_urls;
	}

	public function product_sitemap_page_count(): int {
		global $wpdb;

		$omnify_table = $wpdb->prefix . 'omnify_products';
		if (\Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) !== $omnify_table) {
			return 0;
		}

		$omnify_count = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SELECT COUNT(*) FROM %i WHERE status = %s', $omnify_table, 'published'));

		return max(1, (int) ceil($omnify_count / 100));
	}

	private function current_seo_product(): ?array {
		if (is_admin() || ! is_singular()) {
			return null;
		}

		$post = get_post();
		if (! $post || ! has_shortcode((string) $post->post_content, 'omnify_storefront')) {
			return null;
		}

		$omnify_slug = isset($_GET['omnify_product']) ? sanitize_title(wp_unslash((string) $_GET['omnify_product'])) : '';
		if ('' === $omnify_slug) {
			return null;
		}

		$omnify_product = $this->omnify_products->find_by_slug($omnify_slug);
		if (! $omnify_product || 'published' !== ($omnify_product['status'] ?? '') || ! empty($omnify_product['deleted_at'])) {
			return null;
		}

		return $omnify_product;
	}

	private function current_product_seo_payload(): ?array {
		static $omnify_payload = null;
		static $omnify_resolved = false;

		if ($omnify_resolved) {
			return $omnify_payload;
		}
		$omnify_resolved = true;

		$omnify_product = $this->current_seo_product();
		if (! $omnify_product) {
			return null;
		}

		$omnify_payload = $this->seo_payload_for_product($omnify_product);

		return $omnify_payload;
	}

	private function seo_payload_for_product(array $omnify_product): array {
		$omnify_name        = wp_strip_all_tags((string) ($omnify_product['name'] ?? __('Product', 'omnifywp-ecommerce')));
		$omnify_settings    = get_option('omnify_settings', []);
		$omnify_store_name  = (string) ($omnify_settings['store_name'] ?? get_bloginfo('name'));
		$omnify_title       = sprintf('%s | %s', $omnify_name, $omnify_store_name ?: get_bloginfo('name'));
		$omnify_description = $this->product_seo_description($omnify_product);
		$omnify_url         = $this->product_url($omnify_product);
		$omnify_image       = $this->product_seo_image($omnify_product);
		$omnify_price       = $this->product_seo_price($omnify_product);
		$omnify_currency    = strtoupper((string) ($omnify_product['currency'] ?? $omnify_settings['default_currency'] ?? 'USD'));
		$omnify_review_count = $this->omnify_reviews->get_total_count((int) $omnify_product['id']);
		$omnify_rating       = $omnify_review_count > 0 ? round($this->omnify_reviews->get_average_rating((int) $omnify_product['id']), 1) : 0.0;

		$omnify_schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $omnify_name,
			'description' => $omnify_description,
			'url'         => $omnify_url,
			'sku'         => (string) ($omnify_product['sku'] ?? ''),
			'brand'       => [
				'@type' => 'Brand',
				'name'  => $omnify_store_name ?: get_bloginfo('name'),
			],
			'offers'      => [
				'@type'         => 'Offer',
				'url'           => $omnify_url,
				'priceCurrency' => $omnify_currency,
				'price'         => number_format($omnify_price, 2, '.', ''),
				'availability'  => $this->product_schema_availability($omnify_product),
				'itemCondition' => 'https://schema.org/NewCondition',
			],
		];

		if ($omnify_image) {
			$omnify_schema['image'] = [$omnify_image];
		}
		if (! empty($omnify_product['categories'])) {
			$omnify_schema['category'] = implode(', ', array_map('strval', (array) $omnify_product['categories']));
		}
		if ($omnify_review_count > 0 && $omnify_rating > 0) {
			$omnify_schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => $omnify_rating,
				'reviewCount' => $omnify_review_count,
			];
		}

		return [
			'name'        => $omnify_name,
			'title'       => $omnify_title,
			'description' => $omnify_description,
			'url'         => $omnify_url,
			'image'       => $omnify_image,
			'price'       => $omnify_price,
			'currency'    => $omnify_currency,
			'schema'      => array_filter($omnify_schema, static fn($omnify_value) => '' !== $omnify_value && null !== $omnify_value && [] !== $omnify_value),
		];
	}

	private function product_seo_description(array $omnify_product): string {
		$omnify_description = trim(wp_strip_all_tags((string) ($omnify_product['description'] ?? '')));
		if ('' === $omnify_description && ! empty($omnify_product['trust_badge_text'])) {
			$omnify_description = trim(wp_strip_all_tags((string) $omnify_product['trust_badge_text']));
		}
		if ('' === $omnify_description) {
			// translators: 1: product name, 2: site name
			$omnify_description = sprintf(__('Buy %1$s from %2$s.', 'omnifywp-ecommerce'), (string) ($omnify_product['name'] ?? __('this product', 'omnifywp-ecommerce')), get_bloginfo('name'));
		}

		return wp_html_excerpt($omnify_description, 155, '...');
	}

	private function product_seo_image(array $omnify_product): string {
		if (! empty($omnify_product['thumbnail_id'])) {
			$omnify_image = wp_get_attachment_image_url((int) $omnify_product['thumbnail_id'], 'full');
			if ($omnify_image) {
				return $omnify_image;
			}
		}
		if (! empty($omnify_product['gallery_urls']) && is_array($omnify_product['gallery_urls'])) {
			return (string) reset($omnify_product['gallery_urls']);
		}
		if (! empty($omnify_product['thumbnail_url'])) {
			return (string) $omnify_product['thumbnail_url'];
		}

		return '';
	}

	private function product_seo_price(array $omnify_product): float {
		$omnify_price = null !== ($omnify_product['sale_price'] ?? null) ? (float) $omnify_product['sale_price'] : (float) ($omnify_product['price'] ?? 0);
		if ('variable' === ($omnify_product['type'] ?? '') && ! empty($omnify_product['variations']) && is_array($omnify_product['variations'])) {
			foreach ($omnify_product['variations'] as $omnify_variation) {
				$omnify_variation_price = null !== ($omnify_variation['sale_price'] ?? null) ? (float) $omnify_variation['sale_price'] : (float) ($omnify_variation['price'] ?? 0);
				if ($omnify_variation_price > 0 && (0.0 === $omnify_price || $omnify_variation_price < $omnify_price)) {
					$omnify_price = $omnify_variation_price;
				}
			}
		}

		return max(0.0, $omnify_price);
	}

	private function product_schema_availability(array $omnify_product): string {
		if (! empty($omnify_product['preorder_enabled'])) {
			return 'https://schema.org/PreOrder';
		}
		if (! empty($omnify_product['allow_backorders']) || 'onbackorder' === ($omnify_product['stock_status'] ?? '')) {
			return 'https://schema.org/BackOrder';
		}
		if ('outofstock' === ($omnify_product['stock_status'] ?? '')) {
			return 'https://schema.org/OutOfStock';
		}
		if (! empty($omnify_product['manage_stock']) && null !== ($omnify_product['stock_qty'] ?? null) && (int) $omnify_product['stock_qty'] <= 0) {
			return 'https://schema.org/OutOfStock';
		}

		return 'https://schema.org/InStock';
	}

	private function external_seo_plugin_active(): bool {
		return defined('WPSEO_VERSION')
			|| defined('RANK_MATH_VERSION')
			|| class_exists('RankMath')
			|| class_exists('WPSEO_Frontend')
			|| class_exists('AIOSEO\Plugin\Common\Main\Main');
	}

	private function hex2rgba(string $omnify_hex, float $omnify_opacity): string {
		$omnify_hex = str_replace('#', '', $omnify_hex);
		if (strlen($omnify_hex) === 3) {
			$omnify_r = hexdec(substr($omnify_hex, 0, 1) . substr($omnify_hex, 0, 1));
			$omnify_g = hexdec(substr($omnify_hex, 1, 1) . substr($omnify_hex, 1, 1));
			$omnify_b = hexdec(substr($omnify_hex, 2, 1) . substr($omnify_hex, 2, 1));
		} else {
			$omnify_r = hexdec(substr($omnify_hex, 0, 2) ?: '0');
			$omnify_g = hexdec(substr($omnify_hex, 2, 2) ?: '0');
			$omnify_b = hexdec(substr($omnify_hex, 4, 2) ?: '0');
		}
		return "rgba({$omnify_r}, {$omnify_g}, {$omnify_b}, {$omnify_opacity})";
	}

	private function product_url(array $omnify_product): string {
		return add_query_arg('omnify_product', sanitize_title((string) ($omnify_product['slug'] ?? '')), $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/'));
	}

	public function enqueue_assets(): void {
		wp_enqueue_style('dashicons');

		// Enqueue Stylesheet
		wp_enqueue_style(
			'omnify-storefront-css',
			OMNIFY_URL . 'assets/storefront/storefront.css',
			[],
			OMNIFY_VERSION
		);

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_primary_color = sanitize_hex_color($omnify_settings['design_primary_color'] ?? '#6366f1') ?: '#6366f1';
		$omnify_hover_color   = sanitize_hex_color($omnify_settings['design_hover_color'] ?? '#4f46e5') ?: '#4f46e5';
		$omnify_radius        = intval($omnify_settings['design_border_radius'] ?? 8) . 'px';
		$omnify_font          = preg_replace('/[^a-zA-Z0-9\s\'\",\-]/', '', $omnify_settings['design_font_family'] ?? "'Inter', system-ui, -apple-system, sans-serif");

		$omnify_css_vars = [
			'--omnify-primary'       => $omnify_primary_color,
			'--omnify-primary-hover' => $omnify_hover_color,
			'--omnify-primary-glow'  => $this->hex2rgba($omnify_primary_color, 0.15),
		];
		$omnify_css_vars = apply_filters('omnify_storefront_css_variables', $omnify_css_vars);

		$omnify_css_vars_str = '';
		foreach ($omnify_css_vars as $omnify_var_name => $omnify_var_val) {
			$omnify_css_vars_str .= "\t\t\t\t{$omnify_var_name}: {$omnify_var_val} !important;\n";
		}

		$omnify_inline_css = "
			:root {
{$omnify_css_vars_str}			}
			.omnify-storefront-wrapper,
			.omnify-mini-cart,
			.omnify-portal-wrapper {
				font-family: {$omnify_font} !important;
			}
			.omnify-storefront-wrapper :where(input, select, textarea, button, .omnify-btn),
			.omnify-mini-cart :where(input, select, textarea, button, .omnify-btn),
			.omnify-portal-wrapper :where(input, select, textarea, button, .omnify-btn) {
				font-family: {$omnify_font} !important;
				border-radius: {$omnify_radius} !important;
			}
			.omnify-product-card,
			.omnify-card,
			.omnify-portal-box,
			.omnify-order-box {
				border-radius: {$omnify_radius} !important;
			}
			.omnify-product-card__image-container,
			.omnify-product-card__image {
				border-top-left-radius: {$omnify_radius} !important;
				border-top-right-radius: {$omnify_radius} !important;
			}
		";
		$omnify_inline_css = (string) apply_filters('omnify_storefront_inline_css', $omnify_inline_css);
		$omnify_inline_css = wp_strip_all_tags($omnify_inline_css);
		wp_add_inline_style('omnify-storefront-css', $omnify_inline_css);

		// Enqueue Script
		wp_enqueue_script(
			'omnify-storefront-js',
			OMNIFY_URL . 'assets/storefront/storefront.js',
			[],
			OMNIFY_VERSION,
			true
		);

		$omnify_settings = get_option('omnify_settings', []);
		if (! empty($omnify_settings['razorpay_enabled']) && ! empty($omnify_settings['razorpay_checkout_enabled'])) {
			wp_enqueue_script(
				'omnify-razorpay-checkout',
				'https://checkout.razorpay.com/v1/checkout.js',
				[],
				OMNIFY_VERSION,
				true
			);
		}

		$omnify_tax_rate = (float) ($omnify_settings['tax_rate'] ?? 0.0);
		$omnify_currency = strtoupper((string) ($omnify_settings['default_currency'] ?? 'USD'));
		$omnify_stripe_mode = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';

		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_cart_url     = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');

		// Localize Script
		wp_localize_script(
			'omnify-storefront-js',
			'omnifyStorefront',
			[
				'restUrl'     => untrailingslashit(esc_url_raw(rest_url('omnify/v1'))),
				'nonce'       => wp_create_nonce('wp_rest'),
				'taxRate'     => $omnify_tax_rate,
				'taxLabel'    => sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax')),
				'taxRules'    => is_array($omnify_settings['tax_rules'] ?? null) ? array_values($omnify_settings['tax_rules']) : [],
				'pricesIncludeTax' => ! empty($omnify_settings['prices_include_tax']),
				'taxShipping' => ! empty($omnify_settings['tax_shipping']),
				'taxRounding' => sanitize_key((string) ($omnify_settings['tax_rounding'] ?? 'line')),
				'taxReportingEnabled' => ! empty($omnify_settings['tax_reporting_enabled']),
				'deliveryZones' => is_array($omnify_settings['delivery_zones'] ?? null) ? array_values($omnify_settings['delivery_zones']) : [],
				'currency'    => $omnify_currency,
				'currencySymbol' => $this->currency_symbol($omnify_currency),
				'currencyPosition' => $omnify_settings['currency_position'] ?? 'before',
				'countries'   => Omnify_Locations::countries(),
				'states'      => Omnify_Locations::states(),
				'checkoutUrl' => esc_url_raw($omnify_checkout_url),
				'cartUrl'     => esc_url_raw($omnify_cart_url),
				'isLoggedIn'  => is_user_logged_in(),
				'wishlistProductIds' => is_user_logged_in() ? $this->omnify_wishlists->product_ids_for_user(get_current_user_id()) : [],
				'stripe'      => [
					'enabled'               => ! empty($omnify_settings['stripe_enabled']),
					'mode'                  => $omnify_stripe_mode,
					'publishableKey'        => sanitize_text_field((string) ($omnify_settings["stripe_{$omnify_stripe_mode}_publishable_key"] ?? '')),
					'checkoutEnabled'       => ! empty($omnify_settings['stripe_checkout_enabled']),
					'paymentIntentsEnabled' => ! empty($omnify_settings['stripe_payment_intents_enabled']),
					'applePayEnabled'       => ! empty($omnify_settings['stripe_apple_pay_enabled']),
					'googlePayEnabled'      => ! empty($omnify_settings['stripe_google_pay_enabled']),
				],
				'paypal'      => [
					'enabled'         => ! empty($omnify_settings['paypal_enabled']),
					'mode'            => 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => ! empty($omnify_settings['paypal_checkout_enabled']),
				],
				'razorpay'    => [
					'enabled'         => ! empty($omnify_settings['razorpay_enabled']),
					'mode'            => 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test',
					'keyId'           => sanitize_text_field((string) ($omnify_settings[ 'razorpay_' . ('live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test') . '_key_id' ] ?? '')),
					'checkoutEnabled' => ! empty($omnify_settings['razorpay_checkout_enabled']),
				],
				'alipay'      => [
					'enabled'         => ! empty($omnify_settings['alipay_enabled']),
					'mode'            => 'live' === ($omnify_settings['alipay_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => ! empty($omnify_settings['alipay_checkout_enabled']),
				],
				'wechat'      => [
					'enabled'         => ! empty($omnify_settings['wechat_enabled']),
					'mode'            => 'live' === ($omnify_settings['wechat_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => ! empty($omnify_settings['wechat_checkout_enabled']),
				],
				'sslcommerz'  => [
					'enabled'         => ! empty($omnify_settings['sslcommerz_enabled']),
					'mode'            => 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox',
					'checkoutEnabled' => ! empty($omnify_settings['sslcommerz_checkout_enabled']),
				],
				'paystack'    => [
					'enabled'         => ! empty($omnify_settings['paystack_enabled']),
					'mode'            => 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['paystack_' . ('live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test') . '_public_key'] ?? '')),
					'checkoutEnabled' => ! empty($omnify_settings['paystack_checkout_enabled']),
				],
				'tap'         => [
					'enabled'         => ! empty($omnify_settings['tap_enabled']),
					'mode'            => 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['tap_' . ('live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test') . '_publishable_key'] ?? '')),
					'checkoutEnabled' => ! empty($omnify_settings['tap_checkout_enabled']),
				],
				'mollie'      => [
					'enabled'         => ! empty($omnify_settings['mollie_enabled']),
					'mode'            => 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test',
					'checkoutEnabled' => ! empty($omnify_settings['mollie_checkout_enabled']),
				],
				'khalti'      => [
					'enabled'         => ! empty($omnify_settings['khalti_enabled']),
					'mode'            => 'live' === ($omnify_settings['khalti_mode'] ?? 'test') ? 'live' : 'test',
					'publicKey'       => sanitize_text_field((string) ($omnify_settings['khalti_' . ('live' === ($omnify_settings['khalti_mode'] ?? 'test') ? 'live' : 'test') . '_public_key'] ?? '')),
					'checkoutEnabled' => ! empty($omnify_settings['khalti_checkout_enabled']),
				],
				'esewa'       => [
					'enabled'         => ! empty($omnify_settings['esewa_enabled']),
					'mode'            => 'live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test',
					'productCode'     => sanitize_text_field((string) ($omnify_settings['esewa_' . ('live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test') . '_product_code'] ?? '')),
					'checkoutEnabled' => ! empty($omnify_settings['esewa_checkout_enabled']),
				],
				'tracking'    => [
					'ga4Enabled'  => ! empty($omnify_settings['tracking_ga4_enabled']),
					'metaEnabled' => ! empty($omnify_settings['tracking_meta_enabled']),
					'currency'    => $omnify_currency,
				],
				'cart' => [
					'countdownEnabled'     => ! empty($omnify_settings['cart_countdown_enabled']),
					'countdownDuration'    => intval($omnify_settings['cart_countdown_duration'] ?? 7),
					'freeShippingEnabled'  => ! empty($omnify_settings['cart_free_shipping_enabled']),
					'freeShippingThreshold'=> floatval($omnify_settings['cart_free_shipping_threshold'] ?? 200.0),
				],
			]
		);
	}

	private function page_url_for_shortcode(string $omnify_shortcode, string $omnify_fallback_path): string {
		$omnify_pages = get_posts([
			'post_type'   => 'page',
			's'           => $omnify_shortcode,
			'post_status' => 'publish',
		]);

		if (! empty($omnify_pages)) {
			return get_permalink($omnify_pages[0]->ID);
		}

		return home_url($omnify_fallback_path);
	}

	private function currency_symbol(string $omnify_currency): string {
		$omnify_symbols = [
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CAD' => 'C$',
			'AUD' => 'A$',
			'BDT' => '৳',
			'INR' => '₹',
			'BRL' => 'R$',
			'CNY' => '¥',
		];

		return $omnify_symbols[strtoupper($omnify_currency)] ?? strtoupper($omnify_currency);
	}

	private function format_store_price(float $omnify_amount, array $omnify_settings): string {
		$omnify_currency = strtoupper((string) ($omnify_settings['default_currency'] ?? 'USD'));
		$omnify_symbol   = $this->currency_symbol($omnify_currency);
		$omnify_decimals = max(0, min(4, (int) ($omnify_settings['price_decimals'] ?? 2)));
		$omnify_thou     = $omnify_settings['thousand_separator'] ?? ',';
		$omnify_dec      = $omnify_settings['decimal_separator'] ?? '.';
		$omnify_price    = number_format($omnify_amount, $omnify_decimals, $omnify_dec, $omnify_thou);

		$omnify_formatted = 'after' === ($omnify_settings['currency_position'] ?? 'before')
			? $omnify_price . ' ' . $omnify_symbol
			: $omnify_symbol . $omnify_price;

		return apply_filters('omnify_format_price', $omnify_formatted, $omnify_amount, $omnify_settings);
	}

	public function render_storefront(): string {
		$this->enqueue_assets();

		$omnify_product_slug = isset($_GET['omnify_product']) ? sanitize_title(wp_unslash((string) $_GET['omnify_product'])) : '';
		if (! empty($omnify_product_slug)) {
			$omnify_product = $this->omnify_products->find_by_slug($omnify_product_slug);
			if ($omnify_product && 'published' === $omnify_product['status'] && empty($omnify_product['deleted_at'])) {
				$omnify_reviews_repo = $this->omnify_reviews;
				$omnify_settings = get_option('omnify_settings', []);
				$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
				$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
				$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

				// Dynamic urgency from real data (Premium-like)
				$omnify_orders_repo = new \Omnify\eCommerce\Repositories\Omnify_Order_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
				$omnify_abandoned_repo = new \Omnify\eCommerce\Repositories\Omnify_Abandoned_Cart_Repository( new \Omnify\eCommerce\Database\Omnify_Schema() );
				$omnify_sold_recent = $omnify_orders_repo->get_product_sales_count_last_hours( (int)$omnify_product['id'], 18 );
				$omnify_in_carts = $omnify_abandoned_repo->count_active_carts_for_product( (int)$omnify_product['id'] );
				$omnify_viewing = max(5, (int) ( $omnify_sold_recent * 3 + wp_rand(3,8) )); // realistic proxy + slight random for "live"

				ob_start();
				include \omnify_locate_template('storefront/product-single.php');
				return (string) ob_get_clean();
			}
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_per_page = max(1, min(100, intval($omnify_settings['storefront_products_per_page'] ?? 12)));
		$omnify_offset = max(0, isset($_GET['offset']) ? absint(wp_unslash($_GET['offset'])) : 0);
		$omnify_filter_args = ['status' => 'published', 'per_page' => $omnify_per_page, 'offset' => $omnify_offset];

		// Basic filters
		if (! empty($_GET['category'])) {
			$omnify_filter_args['category'] = sanitize_text_field(wp_unslash((string) $_GET['category']));
		}
		if (! empty($_GET['brand'])) {
			$omnify_filter_args['brand'] = sanitize_text_field(wp_unslash((string) $_GET['brand']));
		}
		if (! empty($_GET['filter_brand'])) {
			$omnify_filter_args['brand'] = sanitize_text_field(wp_unslash((string) $_GET['filter_brand']));
		}
		if (! empty($_GET['tag'])) {
			$omnify_filter_args['tag'] = sanitize_text_field(wp_unslash((string) $_GET['tag']));
		}
		if (! empty($_GET['type'])) {
			$omnify_filter_args['type'] = sanitize_key(wp_unslash((string) $_GET['type']));
		}
		if (! empty($_GET['search'])) {
			$omnify_filter_args['search'] = sanitize_text_field(wp_unslash((string) $_GET['search']));
		}
		if (isset($_GET['price_min'])) {
			$omnify_filter_args['price_min'] = max(0.0, (float) wp_unslash($_GET['price_min']));
		}
		if (isset($_GET['price_max'])) {
			$omnify_filter_args['price_max'] = max(0.0, (float) wp_unslash($_GET['price_max']));
		}
		if (! empty($_GET['stock_status'])) {
			$omnify_filter_args['stock_status'] = sanitize_key(wp_unslash((string) $_GET['stock_status']));
		}

		// Attribute filters e.g. ?filter_color=red&filter_brand=nike or attr_color=red
		$omnify_attr_filters = [];
		$omnify_raw_get = (array) wp_unslash($_GET);
		foreach ($omnify_raw_get as $omnify_key => $omnify_val) {
			$omnify_clean_key = sanitize_key($omnify_key);
			if (strpos($omnify_clean_key, 'filter_') === 0) {
				$omnify_attr = substr($omnify_clean_key, 7);
				$omnify_attr_filters[$omnify_attr] = is_array($omnify_val) ? map_deep($omnify_val, 'sanitize_text_field') : explode(',', sanitize_text_field((string) $omnify_val));
			} elseif (strpos($omnify_clean_key, 'attr_') === 0) {
				$omnify_attr = substr($omnify_clean_key, 5);
				$omnify_attr_filters[$omnify_attr] = is_array($omnify_val) ? map_deep($omnify_val, 'sanitize_text_field') : explode(',', sanitize_text_field((string) $omnify_val));
			}
		}
		if ($omnify_attr_filters) {
			$omnify_filter_args['attributes'] = $omnify_attr_filters;
		}

		$omnify_products = $this->omnify_products->all($omnify_filter_args);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		ob_start();
		include \omnify_locate_template('storefront/grid.php');
		return (string) ob_get_clean();
	}

	public function render_cart(): string {
		$this->enqueue_assets();

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		ob_start();
		include \omnify_locate_template('storefront/cart.php');
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	public function render_mini_cart(array|string $omnify_atts = []): string {
		$this->enqueue_assets();

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_checkout_url = $this->page_url_for_shortcode('[omnify_checkout]', '/checkout/');
		$omnify_cart_url = $this->page_url_for_shortcode('[omnify_cart]', '/cart/');
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		$omnify_atts = shortcode_atts(
			[
				'label' => __('Cart', 'omnifywp-ecommerce'),
				'align' => 'right',
			],
			is_array($omnify_atts) ? $omnify_atts : [],
			'omnify_mini_cart'
		);

		ob_start();
		include \omnify_locate_template('storefront/mini-cart.php');
		return (string) ob_get_clean();
	}

	public function append_mini_cart_to_menu(string $omnify_items, \stdClass $omnify_args): string {
		$omnify_settings = get_option('omnify_settings', []);
		if (empty($omnify_settings['mini_cart_menu_enabled'])) {
			return $omnify_items;
		}

		$omnify_configured_location = sanitize_key((string) ($omnify_settings['mini_cart_menu_location'] ?? ''));
		$omnify_current_location = sanitize_key((string) ($omnify_args->theme_location ?? ''));
		if ('' !== $omnify_configured_location && $omnify_configured_location !== $omnify_current_location) {
			return $omnify_items;
		}

		$omnify_mini_cart = $this->render_mini_cart(['label' => __('Cart', 'omnifywp-ecommerce')]);
		if ('' === trim($omnify_mini_cart)) {
			return $omnify_items;
		}

		return $omnify_items . '<li class="menu-item omnify-mini-cart-menu-item">' . $omnify_mini_cart . '</li>';
	}

	public function render_checkout(): string {
		$this->enqueue_assets();

		if (isset($_GET['omnify_stripe']) && 'cancelled' === sanitize_key(wp_unslash($_GET['omnify_stripe']))) {
			$omnify_order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
			$omnify_order_key = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : '';
			if ($omnify_order_id > 0) {
				$omnify_order = $this->omnify_orders->find($omnify_order_id);
				if ($omnify_order) {
					$omnify_expected_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);
					if (hash_equals($omnify_expected_key, $omnify_order_key)) {
						$this->cancel_order_payment_by_storefront($omnify_order_id, 'stripe', 'User cancelled session');
					}
				}
			}
		}

		if (isset($_GET['omnify_paypal']) && 'cancelled' === sanitize_key(wp_unslash($_GET['omnify_paypal']))) {
			$omnify_order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
			$omnify_order_key = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : '';
			if ($omnify_order_id > 0) {
				$omnify_order = $this->omnify_orders->find($omnify_order_id);
				if ($omnify_order) {
					$omnify_expected_key = wp_hash($omnify_order['id'] . '|' . $omnify_order['created_at'] . '|' . $omnify_order['total']);
					if (hash_equals($omnify_expected_key, $omnify_order_key)) {
						$this->cancel_order_payment_by_storefront($omnify_order_id, 'paypal', 'User cancelled session');
					}
				}
			}
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_tax_rate = (float) ($omnify_settings['tax_rate'] ?? 0.0);
		$omnify_tax_label = sanitize_text_field((string) ($omnify_settings['tax_label'] ?? 'Tax'));
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);

		$omnify_is_cart_checkout = isset($_GET['cart']) && '1' === sanitize_text_field(wp_unslash((string) $_GET['cart']));
		$omnify_cart_items = [];
		$omnify_is_physical_checkout = false;
		$omnify_line_total = 0.0;
		$omnify_product = null;
		$omnify_variation = null;
		$omnify_checkout_quantity = 1;
		$omnify_actual_price = 0.0;

		if ($omnify_is_cart_checkout) {
			$omnify_token = isset($_COOKIE['omnify_cart_token']) ? sanitize_text_field(wp_unslash((string) $_COOKIE['omnify_cart_token'])) : '';
			$omnify_transient_key = 'omnify_cart_' . md5($omnify_token);
			$omnify_raw_items = get_transient($omnify_transient_key);
			$omnify_raw_items = is_array($omnify_raw_items) ? $omnify_raw_items : [];

			foreach ($omnify_raw_items as $omnify_item) {
				$omnify_prod = $this->omnify_products->find((int) $omnify_item['product_id']);
				if ($omnify_prod && 'published' === ($omnify_prod['status'] ?? '')) {
					$omnify_item_variation = null;
					$omnify_variation_id = absint($omnify_item['variation_id'] ?? 0);
					if ($omnify_variation_id && ! empty($omnify_prod['variations']) && is_array($omnify_prod['variations'])) {
						foreach ($omnify_prod['variations'] as $omnify_candidate) {
							if ((int) ($omnify_candidate['id'] ?? 0) === $omnify_variation_id) {
								$omnify_item_variation = $omnify_candidate;
								break;
							}
						}
					}
					$omnify_price = $omnify_item_variation
						? ((null !== ($omnify_item_variation['sale_price'] ?? null) && '' !== $omnify_item_variation['sale_price']) ? (float) $omnify_item_variation['sale_price'] : (float) $omnify_item_variation['price'])
						: ((null !== ($omnify_prod['sale_price'] ?? null) && '' !== $omnify_prod['sale_price']) ? (float) $omnify_prod['sale_price'] : (float) $omnify_prod['price']);
					
					$omnify_variation_settings = wp_parse_args($omnify_prod['variation_settings'] ?? [], ['product_kind' => 'digital']);
					$omnify_is_phys = ('physical' === ($omnify_prod['type'] ?? 'download')) || ('variable' === ($omnify_prod['type'] ?? 'download') && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
					
					if ($omnify_is_phys) {
						$omnify_is_physical_checkout = true;
					}

					$omnify_qty = max(1, absint($omnify_item['quantity'] ?? 1));
					$omnify_line_total += $omnify_price * $omnify_qty;

					$omnify_cart_items[] = [
						'product' => $omnify_prod,
						'variation' => $omnify_item_variation,
						'quantity' => $omnify_qty,
						'price' => $omnify_price,
						'is_physical' => $omnify_is_phys,
					];
				}
			}
			$omnify_product = [
				'id' => 0,
				'name' => __('Cart order', 'omnifywp-ecommerce'),
				'price' => $omnify_line_total,
				'sale_price' => null,
				'currency' => $omnify_settings['default_currency'] ?? 'USD',
				'type' => $omnify_is_physical_checkout ? 'physical' : 'download',
				'thumbnail_url' => '',
				'variation_settings' => ['product_kind' => $omnify_is_physical_checkout ? 'physical' : 'digital'],
				'variations' => [],
				'max_purchase_qty' => 0,
			];
		} else {
			$omnify_product_id = isset($_GET['product_id']) ? absint(wp_unslash($_GET['product_id'])) : 0;
			$omnify_product = $omnify_product_id ? $this->omnify_products->find($omnify_product_id) : null;
			if ($omnify_product) {
				$omnify_variation_id = isset($_GET['variation_id']) ? absint(wp_unslash($_GET['variation_id'])) : 0;
				$omnify_checkout_quantity = max(1, isset($_GET['quantity']) ? absint(wp_unslash($_GET['quantity'])) : 1);
				$omnify_max_purchase_qty = isset($omnify_product['max_purchase_qty']) ? (int) $omnify_product['max_purchase_qty'] : 0;
				if ($omnify_max_purchase_qty > 0 && $omnify_checkout_quantity > $omnify_max_purchase_qty) {
					$omnify_checkout_quantity = $omnify_max_purchase_qty;
				}
				$omnify_variation_settings = wp_parse_args($omnify_product['variation_settings'] ?? [], ['product_kind' => 'digital']);
				$omnify_is_physical_checkout = (isset($omnify_product['type']) && $omnify_product['type'] === 'physical')
					|| (isset($omnify_product['type']) && $omnify_product['type'] === 'variable' && 'physical' === ($omnify_variation_settings['product_kind'] ?? 'digital'));
				if ($omnify_variation_id && ! empty($omnify_product['variations'])) {
					foreach ($omnify_product['variations'] as $omnify_v) {
						if ($omnify_v['id'] === $omnify_variation_id) {
							$omnify_variation = $omnify_v;
							break;
						}
					}
				}
				$omnify_actual_price = $omnify_variation
					? ((null !== ($omnify_variation['sale_price'] ?? null) && '' !== $omnify_variation['sale_price']) ? (float) $omnify_variation['sale_price'] : (float) $omnify_variation['price'])
					: ((null !== ($omnify_product['sale_price'] ?? null) && '' !== $omnify_product['sale_price']) ? (float) $omnify_product['sale_price'] : (float) $omnify_product['price']);
			}
		}

		$omnify_addresses = [];
		$omnify_default_address = null;
		if (is_user_logged_in()) {
			$omnify_saved_addresses = get_user_meta(get_current_user_id(), '_omnify_shipping_addresses', true);
			if (is_array($omnify_saved_addresses)) {
				$omnify_addresses = $omnify_saved_addresses;
				foreach ($omnify_addresses as $omnify_addr) {
					if (! empty($omnify_addr['is_default'])) {
						$omnify_default_address = $omnify_addr;
						break;
					}
				}
				if (! $omnify_default_address && ! empty($omnify_addresses)) {
					$omnify_default_address = $omnify_addresses[0];
				}
			}
		}

		ob_start();
		include \omnify_locate_template('storefront/checkout-page.php');
		return (string) ob_get_clean();
	}

	public function render_customer_portal(): string {
		$this->enqueue_assets();

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_format_price = fn(float $omnify_amount): string => $this->format_store_price($omnify_amount, $omnify_settings);
		$omnify_storefront_url = $this->page_url_for_shortcode('[omnify_storefront]', '/storefront/');

		$omnify_profile_saved = false;

		// Process multiple shipping address actions if logged in
		if (is_user_logged_in()) {
			$omnify_user_id = get_current_user_id();
			$omnify_action = isset($_POST['omnify_action']) ? sanitize_key(wp_unslash($_POST['omnify_action'])) : (isset($_GET['omnify_action']) ? sanitize_key(wp_unslash($_GET['omnify_action'])) : '');

			if ('omnify_add_address' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_address_nonce'] ?? '')), 'omnify_manage_addresses')) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					$omnify_new_address = [
						'id' => uniqid('addr_'),
						'first_name' => isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '',
						'last_name'  => isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '',
						'phone'      => isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '',
						'company'    => isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '',
						'address_1'  => isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '',
						'address_2'  => isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '',
						'city'       => isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '',
						'state'      => isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '',
						'postcode'   => isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '',
						'country'    => strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2)),
						'is_default' => ! empty($_POST['is_default']),
					];

					if ($omnify_new_address['is_default']) {
						foreach ($omnify_addresses as &$omnify_addr) {
							$omnify_addr['is_default'] = false;
						}
					} elseif (empty($omnify_addresses)) {
						$omnify_new_address['is_default'] = true;
					}

					$omnify_addresses[] = $omnify_new_address;
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);
				}
			} elseif ('omnify_edit_address' === $omnify_action) {
				if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_address_nonce'] ?? '')), 'omnify_manage_addresses')) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];
					$omnify_addr_id = isset($_POST['address_id']) ? sanitize_key(wp_unslash($_POST['address_id'])) : '';

					foreach ($omnify_addresses as &$omnify_addr) {
						if ($omnify_addr['id'] === $omnify_addr_id) {
							$omnify_addr['first_name'] = isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '';
							$omnify_addr['last_name'] = isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '';
							$omnify_addr['phone'] = isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '';
							$omnify_addr['company'] = isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '';
							$omnify_addr['address_1'] = isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '';
							$omnify_addr['address_2'] = isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '';
							$omnify_addr['city'] = isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '';
							$omnify_addr['state'] = isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '';
							$omnify_addr['postcode'] = isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '';
							$omnify_addr['country'] = strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2));

							if (! empty($_POST['is_default'])) {
								$omnify_addr['is_default'] = true;
								foreach ($omnify_addresses as &$omnify_other_addr) {
									if ($omnify_other_addr['id'] !== $omnify_addr_id) {
										$omnify_other_addr['is_default'] = false;
									}
								}
							}
							break;
						}
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);
				}
			} elseif ('omnify_delete_address' === $omnify_action) {
				$omnify_addr_id = isset($_GET['address_id']) ? sanitize_key(wp_unslash($_GET['address_id'])) : '';
				if ($omnify_addr_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'omnify_delete_address_' . $omnify_addr_id)) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					$omnify_was_default = false;
					$omnify_filtered = [];
					foreach ($omnify_addresses as $omnify_addr) {
						if ($omnify_addr['id'] === $omnify_addr_id) {
							if (!empty($omnify_addr['is_default'])) {
								$omnify_was_default = true;
							}
							continue;
						}
						$omnify_filtered[] = $omnify_addr;
					}
					if ($omnify_was_default && ! empty($omnify_filtered)) {
						$omnify_filtered[0]['is_default'] = true;
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_filtered);
				}
			} elseif ('omnify_default_address' === $omnify_action) {
				$omnify_addr_id = isset($_GET['address_id']) ? sanitize_key(wp_unslash($_GET['address_id'])) : '';
				if ($omnify_addr_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'omnify_default_address_' . $omnify_addr_id)) {
					$omnify_addresses = get_user_meta($omnify_user_id, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];

					foreach ($omnify_addresses as &$omnify_addr) {
						$omnify_addr['is_default'] = ($omnify_addr['id'] === $omnify_addr_id);
					}
					update_user_meta($omnify_user_id, '_omnify_shipping_addresses', $omnify_addresses);
				}
			} elseif ('omnify_request_refund' === $omnify_action) {
				$omnify_order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
				if ($omnify_order_id && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_refund_nonce'] ?? '')), 'omnify_request_refund_' . $omnify_order_id)) {
					$omnify_customer_ids = [];
					$omnify_wp_user  = wp_get_current_user();
					$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

					$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
					$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;
					$omnify_db_customer       = $omnify_customer_by_email ?: $omnify_customer_by_uid;

					if ($omnify_db_customer) {
						$omnify_customer_ids[] = (int) $omnify_db_customer['id'];
					}

					global $wpdb;
					$omnify_customers_table = $wpdb->prefix . 'omnify_customers';
					$omnify_cust_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
						"SELECT id FROM {$omnify_customers_table} WHERE user_id = %d OR email = %s",
						(int) $omnify_wp_user->ID,
						$omnify_wp_email
					), ARRAY_A);

					if (is_array($omnify_cust_rows)) {
						foreach ($omnify_cust_rows as $omnify_row) {
							$omnify_customer_ids[] = (int) $omnify_row['id'];
						}
					}
					$omnify_customer_ids = array_unique(array_filter($omnify_customer_ids));

					$omnify_order = $this->omnify_orders->find($omnify_order_id);
					if ($omnify_order && in_array((int) $omnify_order['customer_id'], $omnify_customer_ids, true)) {
						$omnify_is_eligible = $this->order_is_refund_eligible($omnify_order, $omnify_settings);

						if ($omnify_is_eligible) {
							$this->omnify_orders->update_status($omnify_order_id, 'refund_requested');

							$omnify_reason = isset($_POST['refund_reason']) ? sanitize_textarea_field(wp_unslash($_POST['refund_reason'])) : '';
							$omnify_note = sprintf(
								__('Customer requested a refund. Reason: %s', 'omnifywp-ecommerce'),
								$omnify_reason ?: __('No reason provided.', 'omnifywp-ecommerce')
							);
							$this->omnify_orders->add_note($omnify_order_id, $omnify_note);
							$this->omnify_emails->send_refund_requested($omnify_order_id, $omnify_reason);

							wp_safe_redirect(add_query_arg('message', 'refund_requested', get_permalink()));
							exit;
						}
					}
				}
			}
		}

		// 1. Process customer settings form submit if present
		if (is_user_logged_in() && 'omnify_update_profile' === (isset($_POST['omnify_action']) ? sanitize_key(wp_unslash($_POST['omnify_action'])) : '')) {
			if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['omnify_profile_nonce'] ?? '')), 'omnify_update_profile')) {
				$omnify_wp_user  = wp_get_current_user();
				$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

				$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
				$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;
				$omnify_db_customer       = $omnify_customer_by_email ?: $omnify_customer_by_uid;

				$omnify_billing_data = [
					'billing_first_name' => isset($_POST['billing_first_name']) ? sanitize_text_field(wp_unslash($_POST['billing_first_name'])) : '',
					'billing_last_name'  => isset($_POST['billing_last_name']) ? sanitize_text_field(wp_unslash($_POST['billing_last_name'])) : '',
					'billing_phone'      => isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '',
					'billing_company'    => isset($_POST['billing_company']) ? sanitize_text_field(wp_unslash($_POST['billing_company'])) : '',
					'billing_address_1'  => isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '',
					'billing_address_2'  => isset($_POST['billing_address_2']) ? sanitize_text_field(wp_unslash($_POST['billing_address_2'])) : '',
					'billing_city'       => isset($_POST['billing_city']) ? sanitize_text_field(wp_unslash($_POST['billing_city'])) : '',
					'billing_state'      => isset($_POST['billing_state']) ? sanitize_text_field(wp_unslash($_POST['billing_state'])) : '',
					'billing_postcode'   => isset($_POST['billing_postcode']) ? sanitize_text_field(wp_unslash($_POST['billing_postcode'])) : '',
					'billing_country'    => strtoupper(substr(isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '', 0, 2)),
				];

				$omnify_shipping_same_as_billing = ! empty($_POST['shipping_same_as_billing']);
				if ($omnify_shipping_same_as_billing) {
					$omnify_shipping_data = [
						'shipping_first_name' => $omnify_billing_data['billing_first_name'],
						'shipping_last_name'  => $omnify_billing_data['billing_last_name'],
						'shipping_phone'      => $omnify_billing_data['billing_phone'],
						'shipping_company'    => $omnify_billing_data['billing_company'],
						'shipping_address_1'  => $omnify_billing_data['billing_address_1'],
						'shipping_address_2'  => $omnify_billing_data['billing_address_2'],
						'shipping_city'       => $omnify_billing_data['billing_city'],
						'shipping_state'      => $omnify_billing_data['billing_state'],
						'shipping_postcode'   => $omnify_billing_data['billing_postcode'],
						'shipping_country'    => $omnify_billing_data['billing_country'],
					];
				} else {
					$omnify_shipping_data = [
						'shipping_first_name' => isset($_POST['shipping_first_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_first_name'])) : '',
						'shipping_last_name'  => isset($_POST['shipping_last_name']) ? sanitize_text_field(wp_unslash($_POST['shipping_last_name'])) : '',
						'shipping_phone'      => isset($_POST['shipping_phone']) ? sanitize_text_field(wp_unslash($_POST['shipping_phone'])) : '',
						'shipping_company'    => isset($_POST['shipping_company']) ? sanitize_text_field(wp_unslash($_POST['shipping_company'])) : '',
						'shipping_address_1'  => isset($_POST['shipping_address_1']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_1'])) : '',
						'shipping_address_2'  => isset($_POST['shipping_address_2']) ? sanitize_text_field(wp_unslash($_POST['shipping_address_2'])) : '',
						'shipping_city'       => isset($_POST['shipping_city']) ? sanitize_text_field(wp_unslash($_POST['shipping_city'])) : '',
						'shipping_state'      => isset($_POST['shipping_state']) ? sanitize_text_field(wp_unslash($_POST['shipping_state'])) : '',
						'shipping_postcode'   => isset($_POST['shipping_postcode']) ? sanitize_text_field(wp_unslash($_POST['shipping_postcode'])) : '',
						'shipping_country'    => strtoupper(substr(isset($_POST['shipping_country']) ? sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '', 0, 2)),
					];
				}

				$omnify_profile_data = array_merge([
					'first_name' => isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '',
					'last_name'  => isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '',
					'phone'      => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
					'company'    => isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '',
					'country'    => strtoupper(substr(isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '', 0, 2)),
				], $omnify_shipping_data, $omnify_billing_data);

				if ($omnify_db_customer) {
					$omnify_updated = $this->omnify_customers->update((int) $omnify_db_customer['id'], $omnify_profile_data);
					if ($omnify_updated) {
						$omnify_profile_saved = true;
					}
				} else {
					$omnify_new_id = $this->omnify_customers->create(array_merge([
						'user_id' => (int) $omnify_wp_user->ID,
						'email'   => $omnify_wp_email,
						'status'  => 'active',
					], $omnify_profile_data));
					if ($omnify_new_id) {
						$omnify_profile_saved = true;
					}
				}

				if ($omnify_profile_saved) {
					update_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_same_as_billing', $omnify_shipping_same_as_billing ? '1' : '0');
				}

				if ($omnify_profile_saved && $omnify_shipping_same_as_billing && (! empty($omnify_shipping_data['shipping_address_1']) || ! empty($omnify_shipping_data['shipping_city']))) {
					$omnify_addresses = get_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_addresses', true);
					$omnify_addresses = is_array($omnify_addresses) ? $omnify_addresses : [];
					$omnify_billing_shipping_address = [
						'id' => 'billing_default',
						'first_name' => $omnify_shipping_data['shipping_first_name'],
						'last_name' => $omnify_shipping_data['shipping_last_name'],
						'phone' => $omnify_shipping_data['shipping_phone'],
						'company' => $omnify_shipping_data['shipping_company'],
						'address_1' => $omnify_shipping_data['shipping_address_1'],
						'address_2' => $omnify_shipping_data['shipping_address_2'],
						'city' => $omnify_shipping_data['shipping_city'],
						'state' => $omnify_shipping_data['shipping_state'],
						'postcode' => $omnify_shipping_data['shipping_postcode'],
						'country' => $omnify_shipping_data['shipping_country'],
						'is_default' => true,
					];

					$omnify_updated_addresses = [];
					$omnify_found_billing_address = false;
					foreach ($omnify_addresses as $omnify_address) {
						$omnify_address['is_default'] = false;
						if (($omnify_address['id'] ?? '') === 'billing_default') {
							$omnify_updated_addresses[] = $omnify_billing_shipping_address;
							$omnify_found_billing_address = true;
						} else {
							$omnify_updated_addresses[] = $omnify_address;
						}
					}

					if (! $omnify_found_billing_address) {
						array_unshift($omnify_updated_addresses, $omnify_billing_shipping_address);
					}

					update_user_meta((int) $omnify_wp_user->ID, '_omnify_shipping_addresses', $omnify_updated_addresses);
				}
			}
		}

		$omnify_customer = null;
		if (is_user_logged_in()) {
			$omnify_wp_user  = wp_get_current_user();
			$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

			$omnify_customer_by_uid   = $this->omnify_customers->find_by_user_id((int) $omnify_wp_user->ID);
			$omnify_customer_by_email = $omnify_wp_email ? $this->omnify_customers->find_by_email($omnify_wp_email) : null;

			if ($omnify_customer_by_email) {
				$omnify_customer = $omnify_customer_by_email;
				// Link WP user ID if not linked or if it points to a different user
				if ((int) $omnify_customer['user_id'] !== (int) $omnify_wp_user->ID) {
					$this->omnify_customers->update((int) $omnify_customer['id'], ['user_id' => (int) $omnify_wp_user->ID]);
					$omnify_customer['user_id'] = $omnify_wp_user->ID;
				}
				// If another customer record was linked to this user_id, de-associate it to maintain user_id uniqueness
				if ($omnify_customer_by_uid && (int) $omnify_customer_by_uid['id'] !== (int) $omnify_customer['id']) {
					$this->omnify_customers->update((int) $omnify_customer_by_uid['id'], ['user_id' => null]);
				}
			} elseif ($omnify_customer_by_uid) {
				$omnify_customer = $omnify_customer_by_uid;
				// If the customer record email doesn't match the WP email, update it
				if ($omnify_wp_email && strtolower((string) $omnify_customer['email']) !== strtolower($omnify_wp_email)) {
					$this->omnify_customers->update((int) $omnify_customer['id'], ['email' => $omnify_wp_email]);
					$omnify_customer['email'] = $omnify_wp_email;
				}
			} else {
				// Mock one from WP user info (no database insert yet)
				$omnify_customer = [
					'id'                  => 0,
					'user_id'             => $omnify_wp_user->ID,
					'email'               => $omnify_wp_email,
					'first_name'          => $omnify_wp_user->first_name ?: $omnify_wp_user->display_name,
					'last_name'           => $omnify_wp_user->last_name,
					'name'                => $omnify_wp_user->display_name,
					'company'             => '',
					'country'             => '',
					'phone'               => '',
					'status'              => 'active',
					'created_at'          => '',
					'shipping_first_name' => '',
					'shipping_last_name'  => '',
					'shipping_phone'      => '',
					'shipping_company'    => '',
					'shipping_address_1'  => '',
					'shipping_address_2'  => '',
					'shipping_city'       => '',
					'shipping_state'      => '',
					'shipping_postcode'   => '',
					'shipping_country'    => '',
					'billing_first_name'  => '',
					'billing_last_name'   => '',
					'billing_phone'       => '',
					'billing_company'     => '',
					'billing_address_1'   => '',
					'billing_address_2'   => '',
					'billing_city'        => '',
					'billing_state'       => '',
					'billing_postcode'    => '',
					'billing_country'     => '',
				];
			}
		}

		$omnify_products_accessed = [];
		$omnify_files             = [];
		$omnify_orders            = [];
		$omnify_download_history  = [];

		$omnify_customer_ids = [];
		if ($omnify_customer && $omnify_customer['id'] > 0) {
			$omnify_customer_ids[] = (int) $omnify_customer['id'];
		}
		if (is_user_logged_in()) {
			$omnify_wp_user  = wp_get_current_user();
			$omnify_wp_email = sanitize_email($omnify_wp_user->user_email);

			global $wpdb;
			$omnify_customers_table = $wpdb->prefix . 'omnify_customers';

			$omnify_cust_rows = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id FROM {$omnify_customers_table} WHERE user_id = %d OR email = %s",
				(int) $omnify_wp_user->ID,
				$omnify_wp_email
			), ARRAY_A);

			if (is_array($omnify_cust_rows)) {
				foreach ($omnify_cust_rows as $omnify_row) {
					$omnify_customer_ids[] = (int) $omnify_row['id'];
				}
			}
		}
		$omnify_customer_ids = array_unique(array_filter($omnify_customer_ids));

		if (! empty($omnify_customer_ids)) {
			// Get unique products accessed
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_access->for_customer($omnify_c_id) as $omnify_item) {
					if ('active' === $omnify_item['status'] && ! $omnify_item['is_expired']) {
						$omnify_prod = $this->omnify_products->find((int) $omnify_item['product_id']);
						if ($omnify_prod && 'published' === $omnify_prod['status']) {
							$omnify_products_accessed[] = $omnify_prod;
						}
					}
				}
			}
			// Deduplicate products_accessed
			$omnify_unique_products = [];
			foreach ($omnify_products_accessed as $omnify_p) {
				if ($omnify_p) {
					$omnify_unique_products[$omnify_p['id']] = $omnify_p;
				}
			}
			$omnify_products_accessed = array_values($omnify_unique_products);

			// Get active downloads files
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_access->files_for_customer($omnify_c_id) as $omnify_file) {
					$omnify_file['download_url'] = $this->omnify_signed_urls->create((int) $omnify_file['id'], 3600, $omnify_c_id);
					$omnify_files[] = $omnify_file;
				}
			}
			// Deduplicate files by id
			$omnify_unique_files = [];
			foreach ($omnify_files as $omnify_f) {
				if ($omnify_f) {
					$omnify_unique_files[$omnify_f['id']] = $omnify_f;
				}
			}
			$omnify_files = array_values($omnify_unique_files);

			// Get orders history
			$omnify_orders = $this->omnify_orders->all(['customer_id' => $omnify_customer_ids, 'per_page' => 100]);

			// Also add unique products from active orders to products_accessed
			$omnify_accessed_prod_ids = array_column($omnify_products_accessed, 'id');
			foreach ($omnify_orders as $omnify_order) {
				if (! in_array($omnify_order['status'], ['cancelled', 'failed'], true)) {
					foreach ($omnify_order['items'] as $omnify_item) {
						$omnify_prod_id = (int) $omnify_item['product_id'];
						if (! in_array($omnify_prod_id, $omnify_accessed_prod_ids, true)) {
							$omnify_prod = $this->omnify_products->find($omnify_prod_id);
							if ($omnify_prod && 'published' === $omnify_prod['status']) {
								$omnify_products_accessed[] = $omnify_prod;
								$omnify_accessed_prod_ids[] = $omnify_prod_id;
							}
						}
					}
				}
			}

			// Get downloads history logs
			foreach ($omnify_customer_ids as $omnify_c_id) {
				foreach ($this->omnify_downloads->all_for_customer($omnify_c_id, 30) as $omnify_log) {
					$omnify_download_history[] = $omnify_log;
				}
			}
			// Sort download_history by downloaded_at DESC and slice to 30
			usort($omnify_download_history, fn($omnify_a, $omnify_b) => strcmp($omnify_b['downloaded_at'], $omnify_a['downloaded_at']));
			$omnify_download_history = array_slice($omnify_download_history, 0, 30);
		}

		$omnify_wishlist_products = [];
		if (is_user_logged_in()) {
			foreach ($this->omnify_wishlists->product_ids_for_user(get_current_user_id()) as $omnify_wishlist_product_id) {
				$omnify_wishlist_product = $this->omnify_products->find($omnify_wishlist_product_id);
				if ($omnify_wishlist_product && 'published' === ($omnify_wishlist_product['status'] ?? '')) {
					$omnify_wishlist_products[] = $omnify_wishlist_product;
				}
			}
		}

		ob_start();
		include \omnify_locate_template('storefront/portal.php');
		return (string) ob_get_clean();
	}

	public function order_is_refund_eligible(array $omnify_order, array $omnify_settings): bool {
		if (empty($omnify_settings['enable_refunds']) || ! in_array($omnify_order['status'] ?? '', ['completed', 'processing', 'packed', 'ready_to_deliver', 'shipped', 'out_for_delivery', 'delivered'], true)) {
			return false;
		}

		$omnify_window_days = max(1, absint($omnify_settings['refund_duration'] ?? 14));
		foreach (($omnify_order['items'] ?? []) as $omnify_item) {
			$omnify_product = $this->omnify_products->find(absint($omnify_item['product_id'] ?? 0));
			if (! $omnify_product || empty($omnify_product['refund_enabled'])) {
				return false;
			}
			if (! empty($omnify_product['refund_window_days'])) {
				$omnify_window_days = min($omnify_window_days, max(1, absint($omnify_product['refund_window_days'])));
			}
		}

		return time() <= strtotime((string) $omnify_order['created_at']) + ($omnify_window_days * DAY_IN_SECONDS);
	}

	private function cancel_order_payment_by_storefront(int $omnify_order_id, string $omnify_gateway, string $omnify_reason): void {
		global $wpdb;

		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		if (! $omnify_order || ! in_array($omnify_order['status'], ['pending', 'pending_payment'], true)) {
			return;
		}

		\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
			$wpdb->prefix . 'omnify_orders',
			[
				'status'     => 'cancelled',
				'updated_at' => current_time('mysql', true),
			],
			['id' => $omnify_order_id]
		);

		$omnify_settings = get_option('omnify_settings', []);
		if (! empty($omnify_settings['reduce_stock_on_checkout'])) {
			foreach ($omnify_order['items'] as $omnify_item) {
				$omnify_product_id   = (int) $omnify_item['product_id'];
				$omnify_variation_id = ! empty($omnify_item['variation_id']) ? (int) $omnify_item['variation_id'] : null;
				$omnify_quantity     = (int) $omnify_item['quantity'];

				if ($omnify_quantity > 0) {
					$this->omnify_products->increment_stock($omnify_product_id, $omnify_variation_id, $omnify_quantity);

					$omnify_new_qty = 0;
					if ($omnify_variation_id > 0) {
						$omnify_var = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
							"SELECT stock_qty FROM {$wpdb->prefix}omnify_product_variations WHERE id = %d",
							$omnify_variation_id
						), ARRAY_A);
						$omnify_new_qty = (int) ($omnify_var['stock_qty'] ?? 0);
					} else {
						$omnify_prod = \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare(
							"SELECT stock_qty FROM {$wpdb->prefix}omnify_products WHERE id = %d",
							$omnify_product_id
						), ARRAY_A);
						$omnify_new_qty = (int) ($omnify_prod['stock_qty'] ?? 0);
					}

					$this->omnify_products->log_stock_change(
						$omnify_product_id,
						$omnify_variation_id,
						$omnify_quantity,
						$omnify_new_qty,
						// translators: %d: order ID
						sprintf(__('Restored from cancelled Order #%d', 'omnifywp-ecommerce'), $omnify_order_id)
					);
				}
			}
		}

		$this->omnify_orders->add_note(
			$omnify_order_id,
			// translators: 1: gateway name, 2: cancellation reason
			sprintf(__('Order cancelled via %1$s (%2$s). Stock restored.', 'omnifywp-ecommerce'), $omnify_gateway, $omnify_reason)
		);
	}

	/**
	 * Render public guest order tracking and invoice lookup form & results.
	 */
	public function render_order_tracking(): string {
		$omnify_order_number = isset($_REQUEST['order_number']) ? sanitize_text_field(wp_unslash($_REQUEST['order_number'])) : '';
		$omnify_order_email  = isset($_REQUEST['order_email']) ? sanitize_email(wp_unslash($_REQUEST['order_email'])) : '';

		$omnify_found_order = null;
		$omnify_error_msg   = '';

		if ('' !== $omnify_order_number && '' !== $omnify_order_email) {
			global $wpdb;
			$omnify_table = $wpdb->prefix . 'omnify_orders';
			$omnify_num_clean = ltrim($omnify_order_number, '#');

			$omnify_row = \Omnify\eCommerce\Support\Omnify_DB::get_row(
				$wpdb,
				$wpdb->prepare(
					"SELECT id FROM {$omnify_table} WHERE (order_number = %s OR id = %d) LIMIT 1",
					$omnify_order_number,
					absint($omnify_num_clean)
				),
				ARRAY_A
			);

			if ($omnify_row) {
				$omnify_candidate = $this->omnify_orders->find((int) $omnify_row['id']);
				if ($omnify_candidate && strtolower(trim((string) ($omnify_candidate['customer_email'] ?? ''))) === strtolower(trim($omnify_order_email))) {
					$omnify_found_order = $omnify_candidate;
				} else {
					$omnify_error_msg = __('No matching order found for this email address.', 'omnifywp-ecommerce');
				}
			} else {
				$omnify_error_msg = __('Order not found. Please verify the order number.', 'omnifywp-ecommerce');
			}
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_currency = (string) ($omnify_settings['currency'] ?? 'USD');
		$omnify_format_price = function(float $omnify_val) use ($omnify_currency): string {
			return $omnify_currency . ' ' . number_format($omnify_val, 2);
		};

		ob_start();
		?>
		<div class="omnify-storefront-wrapper omnify-order-tracking-wrapper" style="max-width: 680px; margin: 0 auto; padding: 24px;">
			<div class="omnify-portal-section-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
				<div style="margin-bottom: 20px;">
					<h3 style="margin:0 0 8px 0; font-size:20px; font-weight:700; color:#0f172a;"><?php esc_html_e('Track Your Order', 'omnifywp-ecommerce'); ?></h3>
					<p style="margin:0; font-size:14px; color:#64748b;"><?php esc_html_e('Enter your order number and billing email to view current status and details.', 'omnifywp-ecommerce'); ?></p>
				</div>

				<?php if ($omnify_error_msg) : ?>
					<div class="omnify-portal-notice omnify-portal-notice--error" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:14px;">
						<?php echo esc_html($omnify_error_msg); ?>
					</div>
				<?php endif; ?>

				<form method="get" class="omnify-tracking-form" style="display:flex; flex-direction:column; gap:14px;">
					<div class="omnify-portal-form-group" style="display:flex; flex-direction:column; gap:6px;">
						<label style="font-size:13px; font-weight:600; color:#334155;"><?php esc_html_e('Order Number', 'omnifywp-ecommerce'); ?></label>
						<input type="text" name="order_number" value="<?php echo esc_attr($omnify_order_number); ?>" placeholder="e.g. #1042 or ORD-2026-1042" required style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" />
					</div>
					<div class="omnify-portal-form-group" style="display:flex; flex-direction:column; gap:6px;">
						<label style="font-size:13px; font-weight:600; color:#334155;"><?php esc_html_e('Email Address', 'omnifywp-ecommerce'); ?></label>
						<input type="email" name="order_email" value="<?php echo esc_attr($omnify_order_email); ?>" placeholder="e.g. you@example.com" required style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" />
					</div>
					<button type="submit" class="omnify-btn omnify-btn--primary" style="background:#0f172a; color:#fff; border:none; padding:12px 20px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; margin-top:6px;">
						<?php esc_html_e('Track Order', 'omnifywp-ecommerce'); ?>
					</button>
				</form>
			</div>

			<?php if ($omnify_found_order) : ?>
				<?php
				$omnify_ord_num = (string) ($omnify_found_order['order_number'] ?: ('#' . $omnify_found_order['id']));
				$omnify_status = (string) ($omnify_found_order['status'] ?? 'pending');
				$omnify_fulfill = (string) ($omnify_found_order['fulfillment_status'] ?? 'none');
				$omnify_track_no = (string) ($omnify_found_order['tracking_number'] ?? '');
				$omnify_carrier = (string) ($omnify_found_order['tracking_carrier'] ?? '');
				?>
				<div class="omnify-portal-section-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-top:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
					<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:16px;">
						<div>
							<h4 style="margin:0 0 4px 0; font-size:18px; font-weight:700; color:#0f172a;">Order <?php echo esc_html($omnify_ord_num); ?></h4>
							<span style="font-size:13px; color:#64748b;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime((string) $omnify_found_order['created_at']))); ?></span>
						</div>
						<div style="display:flex; gap:8px;">
							<span class="omnify-status-pill" style="display:inline-block; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; text-transform:capitalize; background:#e0f2fe; color:#0369a1;">
								<?php echo esc_html(str_replace('_', ' ', $omnify_status)); ?>
							</span>
							<?php if ($omnify_fulfill && 'none' !== $omnify_fulfill) : ?>
								<span class="omnify-status-pill" style="display:inline-block; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; text-transform:capitalize; background:#dcfce7; color:#15803d;">
									<?php echo esc_html(str_replace('_', ' ', $omnify_fulfill)); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>

					<?php if ($omnify_track_no) : ?>
						<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#334155;">
							<strong><?php esc_html_e('Shipping Tracking:', 'omnifywp-ecommerce'); ?></strong>
							<?php if ($omnify_carrier) : ?><?php echo esc_html($omnify_carrier); ?> — <?php endif; ?>
							<code><?php echo esc_html($omnify_track_no); ?></code>
						</div>
					<?php endif; ?>

					<?php
					$omnify_can_download = in_array($omnify_found_order['status'], ['completed', 'processing', 'shipped'], true);
					$omnify_tracking_downloads = [];
					if ($omnify_can_download && ! empty($omnify_found_order['customer_id'])) {
						$omnify_cid = (int) $omnify_found_order['customer_id'];
						foreach (($omnify_found_order['items'] ?? []) as $omnify_item) {
							$omnify_pid = (int) ($omnify_item['product_id'] ?? 0);
							if ($omnify_pid > 0 && $this->omnify_access->has_access($omnify_cid, $omnify_pid)) {
								$omnify_files = $this->omnify_files->active_for_product($omnify_pid);
								foreach ($omnify_files as $omnify_f) {
									$omnify_f['product_name'] = $omnify_item['product_name'];
									$omnify_f['download_url'] = $this->omnify_signed_urls->create((int) $omnify_f['id'], 3600 * 24 * 7, $omnify_cid);
									$omnify_tracking_downloads[] = $omnify_f;
								}
							}
						}
					}
					if (! empty($omnify_tracking_downloads)) :
					?>
						<div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:14px 16px; margin-bottom:16px;">
							<strong style="display:block; margin-bottom:8px; font-size:14px; color:#0f172a;"><?php esc_html_e('Your Digital Downloads', 'omnifywp-ecommerce'); ?></strong>
							<div style="display:flex; flex-direction:column; gap:8px;">
								<?php foreach ($omnify_tracking_downloads as $omnify_td) : ?>
									<div style="display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px;">
										<div>
											<strong style="font-size:13px; color:#1e293b;"><?php echo esc_html($omnify_td['product_name']); ?></strong>
											<span style="font-size:12px; color:#64748b; margin-left:6px;"><?php echo esc_html($omnify_td['file_name']); ?> (v<?php echo esc_html($omnify_td['version']); ?>)</span>
										</div>
										<a href="<?php echo esc_url($omnify_td['download_url']); ?>" class="omnify-btn omnify-btn--primary omnify-btn--sm" style="padding:4px 12px; font-size:12px; border-radius:5px; text-decoration:none; background:#6366f1; color:#fff; font-weight:600;">
											<?php esc_html_e('Download', 'omnifywp-ecommerce'); ?>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<table style="width:100%; border-collapse:collapse; font-size:14px; margin-bottom:16px;">
						<thead>
							<tr style="border-bottom:1px solid #e2e8f0; color:#64748b; text-align:left;">
								<th style="padding:8px 0;"><?php esc_html_e('Item', 'omnifywp-ecommerce'); ?></th>
								<th style="padding:8px 0; text-align:center;"><?php esc_html_e('Qty', 'omnifywp-ecommerce'); ?></th>
								<th style="padding:8px 0; text-align:right;"><?php esc_html_e('Total', 'omnifywp-ecommerce'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (($omnify_found_order['items'] ?? []) as $omnify_item) : ?>
								<tr style="border-bottom:1px solid #f1f5f9;">
									<td style="padding:10px 0; font-weight:500; color:#1e293b;"><?php echo esc_html($omnify_item['product_name']); ?></td>
									<td style="padding:10px 0; text-align:center; color:#64748b;"><?php echo esc_html($omnify_item['quantity']); ?></td>
									<td style="padding:10px 0; text-align:right; font-weight:600; color:#0f172a;"><?php echo esc_html($omnify_format_price((float) $omnify_item['price'] * (int) $omnify_item['quantity'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:16px; margin-top:8px;">
						<?php
						$omnify_ord_email = strtolower((string) ($omnify_found_order['customer_email'] ?? ''));
						$omnify_guest_token = hash_hmac('sha256', (int) $omnify_found_order['id'] . '|' . $omnify_ord_email, wp_salt('nonce'));
						$omnify_invoice_url = add_query_arg([
							'action'        => 'omnify_order_document',
							'order_id'      => (int) $omnify_found_order['id'],
							'document_type' => 'invoice',
							'guest_token'   => $omnify_guest_token,
						], admin_url('admin-post.php'));
						?>
						<a href="<?php echo esc_url($omnify_invoice_url); ?>" class="omnify-btn omnify-btn--secondary omnify-btn--sm" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600;" target="_blank">
							<?php esc_html_e('Download Invoice (PDF/HTML)', 'omnifywp-ecommerce'); ?>
						</a>
						<div style="font-size:16px; font-weight:700; color:#0f172a;">
							<span><?php esc_html_e('Total:', 'omnifywp-ecommerce'); ?>&nbsp;</span>
							<span><?php echo esc_html($omnify_format_price((float) ($omnify_found_order['total'] ?? 0))); ?></span>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
