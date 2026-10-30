<?php
/**
 * Full Site Editing (FSE) block templates and template parts integration.
 *
 * Automatically registers native block templates for Omnify Single Product,
 * Product Archive, Category Archive, and Header/Footer eCommerce Template Parts
 * so block themes (Twenty Twenty-Four, Frost, etc.) can customize them visually.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Storefront;

use WP_Block_Template;

if (! defined('ABSPATH')) {
	exit;
}

final class Omnify_FSE_Templates {

	/**
	 * Register hooks for block template resolution.
	 */
	public static function register(): void {
		add_filter('pre_get_block_file_template', [self::class, 'resolve_block_template'], 10, 3);
		add_filter('get_block_templates', [self::class, 'inject_block_templates'], 10, 3);
		add_filter('default_template_types', [self::class, 'register_template_types'], 10, 1);
	}

	/**
	 * Register default template types so WordPress recognizes Omnify templates.
	 *
	 * @param array<string, array{title: string, description: string}> $template_types
	 * @return array<string, array{title: string, description: string}>
	 */
	public static function register_template_types(array $template_types): array {
		$registry = self::get_templates_registry();
		foreach ($registry as $slug => $def) {
			if ($def['type'] === 'wp_template' && ! isset($template_types[$slug])) {
				$template_types[$slug] = [
					'title'       => $def['title'],
					'description' => $def['description'],
				];
			}
		}
		return $template_types;
	}

	/**
	 * List of registered Omnify block templates.
	 *
	 * @return array<string, array{title: string, description: string, content: string, type: string}>
	 */
	public static function get_templates_registry(): array {
		return [
			'single-omnify_product' => [
				'title'       => __('Omnify Single Product', 'omnifywp-ecommerce'),
				'description' => __('Template used to display an individual Omnify product with gallery, details, reviews, and checkout.', 'omnifywp-ecommerce'),
				'type'        => 'wp_template',
				'content'     => '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"32px","bottom":"64px"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:32px;padding-bottom:64px"><!-- wp:omnify/free-shipping-goal {"threshold":50,"showIcon":true,"maxWidth":960} /-->

<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"40px","left":"48px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:post-featured-image {"aspectRatio":"1","style":{"border":{"radius":"14px"}}} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"32px","fontWeight":"800"}}} /-->

<!-- wp:post-excerpt {"style":{"typography":{"fontSize":"16px","lineHeight":"1.6"},"color":{"text":"#64748b"}}} /-->

<!-- wp:omnify/product-card {"cardLayout":"vertical","showPrice":true,"showComparePrice":true,"showRating":true,"showBuyButton":true,"buttonAction":"cart","buttonLabel":"Add to Cart","maxWidth":420} /-->

<!-- wp:spacer {"height":"16px"} -->
<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:omnify/trust-badge-bar {"layout":"horizontal","badgeStyle":"emerald","showBorder":true} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:post-content {"layout":{"type":"constrained"}} /-->

<!-- wp:omnify/sticky-add-to-cart {"deviceVisibility":"mobile","showThumbnail":true,"showPrice":true,"showQuantity":true} /--></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->',
			],
			'archive-omnify_product' => [
				'title'       => __('Omnify Product Catalog Archive', 'omnifywp-ecommerce'),
				'description' => __('Catalog shop template displaying product listings with filter pills and search.', 'omnifywp-ecommerce'),
				'type'        => 'wp_template',
				'content'     => '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"32px","bottom":"64px"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:32px;padding-bottom:64px"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"36px","fontWeight":"800"}}} -->
<h1 class="wp-block-heading" style="font-size:36px;font-weight:800">Shop Catalog</h1>
<!-- /wp:heading -->

<!-- wp:omnify/free-shipping-goal {"threshold":60,"showIcon":true,"maxWidth":800} /-->

<!-- wp:omnify/product-grid {"perPage":12,"columns":4,"showSearch":true,"showFilters":true,"showQuickFilters":true,"showSort":true} /--></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->',
			],
			'omnify-header-cart' => [
				'title'       => __('Omnify Header Cart & Currency Bar', 'omnifywp-ecommerce'),
				'description' => __('Template part combining Mini-Cart trigger and Currency Switcher for website headers.', 'omnifywp-ecommerce'),
				'type'        => 'wp_template_part',
				'content'     => '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:omnify/currency-switcher {"displayStyle":"dropdown","showSymbol":true} /-->

<!-- wp:omnify/mini-cart {"showEmptyState":true,"showCheckoutBtn":true,"showViewCartBtn":true} /--></div>
<!-- /wp:group -->',
			],
		];
	}

	/**
	 * Intercept template resolution when WordPress looks for block templates.
	 *
	 * @param WP_Block_Template|null $template
	 * @param string                 $id
	 * @param string                 $template_type
	 * @return WP_Block_Template|null
	 */
	public static function resolve_block_template($template, string $id, string $template_type) {
		if ($template instanceof WP_Block_Template) {
			return $template;
		}

		$parts = explode('//', $id);
		$slug  = end($parts);

		$registry = self::get_templates_registry();
		if (! isset($registry[$slug])) {
			return $template;
		}

		$def = $registry[$slug];
		if ($def['type'] !== $template_type) {
			return $template;
		}

		$block_template                 = new WP_Block_Template();
		$block_template->id             = get_stylesheet() . '//' . $slug;
		$block_template->theme          = get_stylesheet();
		$block_template->content        = $def['content'];
		$block_template->slug           = $slug;
		$block_template->source         = 'plugin';
		$block_template->type           = $def['type'];
		$block_template->title          = $def['title'];
		$block_template->description    = $def['description'];
		$block_template->status         = 'publish';
		$block_template->has_theme_file = false;
		$block_template->is_custom      = false;
		$block_template->post_types     = ['omnify_product'];

		return $block_template;
	}

	/**
	 * Inject templates into the Site Editor template list.
	 *
	 * @param WP_Block_Template[] $query_result
	 * @param array               $query
	 * @param string              $template_type
	 * @return WP_Block_Template[]
	 */
	public static function inject_block_templates(array $query_result, array $query, string $template_type): array {
		$registry = self::get_templates_registry();

		$existing_slugs = [];
		foreach ($query_result as $item) {
			if ($item instanceof WP_Block_Template) {
				$existing_slugs[] = $item->slug;
			}
		}

		$has_slug_filter      = ! empty($query['slug__in']) && is_array($query['slug__in']);
		$has_post_type_filter = ! empty($query['post_type']);
		$matches_post_type    = $has_post_type_filter && in_array('omnify_product', (array) $query['post_type'], true);

		$theme = get_stylesheet();
		foreach ($registry as $slug => $def) {
			if ($def['type'] !== $template_type || in_array($slug, $existing_slugs, true)) {
				continue;
			}

			// If query specifies slug__in, only include templates that are explicitly requested
			if ($has_slug_filter && ! in_array($slug, $query['slug__in'], true)) {
				continue;
			}

			// If query specifies post_type, only include if omnify_product is targeted
			if ($has_post_type_filter && ! $matches_post_type) {
				continue;
			}

			$block_template                 = new WP_Block_Template();
			$block_template->id             = $theme . '//' . $slug;
			$block_template->theme          = $theme;
			$block_template->content        = $def['content'];
			$block_template->slug           = $slug;
			$block_template->source         = 'plugin';
			$block_template->type           = $def['type'];
			$block_template->title          = $def['title'];
			$block_template->description    = $def['description'];
			$block_template->status         = 'publish';
			$block_template->has_theme_file = false;
			$block_template->is_custom      = false;
			$block_template->post_types     = ['omnify_product'];

			$query_result[] = $block_template;
		}

		return $query_result;
	}
}
