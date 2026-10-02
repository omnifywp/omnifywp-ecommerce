<?php
/**
 * Conversion Tracking Service.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;

class Omnify_Tracking_Service {
	/**
	 * Constructor.
	 *
	 * @param Settings_Repository $settings Settings repository instance.
	 */
	public function __construct(private Omnify_Settings_Repository $omnify_settings) {}

	/**
	 * Register actions and hooks.
	 */
	public function register(): void {
		add_action('wp_enqueue_scripts', [$this, 'enqueue_tracking_codes']);
		add_action('wp_body_open', [$this, 'output_meta_pixel_noscript']);
	}

	/**
	 * Enqueue GA4 and Meta Pixel base tags if enabled.
	 */
	public function enqueue_tracking_codes(): void {
		$omnify_settings = $this->omnify_settings->all();

		if (! empty($omnify_settings['tracking_ga4_enabled']) && ! empty($omnify_settings['tracking_ga4_measurement_id'])) {
			$omnify_measurement_id = sanitize_text_field((string) $omnify_settings['tracking_ga4_measurement_id']);
			$omnify_debug_mode = ! empty($omnify_settings['tracking_debug_mode']);

			wp_enqueue_script(
				'omnify-google-analytics',
				add_query_arg('id', rawurlencode($omnify_measurement_id), 'https://www.googletagmanager.com/gtag/js'),
				[],
				OMNIFY_VERSION,
				[
					'strategy'  => 'async',
					'in_footer' => false,
				]
			);
			wp_add_inline_script(
				'omnify-google-analytics',
				'window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag("js", new Date()); gtag("config", ' . wp_json_encode($omnify_measurement_id) . ', { "debug_mode": ' . wp_json_encode($omnify_debug_mode) . ' });',
				'after'
			);
		}

		if (! empty($omnify_settings['tracking_meta_enabled']) && ! empty($omnify_settings['tracking_meta_pixel_id'])) {
			$omnify_pixel_id = sanitize_text_field((string) $omnify_settings['tracking_meta_pixel_id']);

			wp_enqueue_script(
				'omnify-meta-pixel',
				'https://connect.facebook.net/en_US/fbevents.js',
				[],
				OMNIFY_VERSION,
				[
					'strategy'  => 'async',
					'in_footer' => false,
				]
			);
			wp_add_inline_script(
				'omnify-meta-pixel',
				'!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];}(window, document, "script");',
				'before'
			);
			wp_add_inline_script(
				'omnify-meta-pixel',
				'fbq("init", ' . wp_json_encode($omnify_pixel_id) . '); fbq("track", "PageView");',
				'after'
			);
		}
	}

	/**
	 * Output Meta Pixel noscript fallback when the pixel is enabled.
	 */
	public function output_meta_pixel_noscript(): void {
		$omnify_settings = $this->omnify_settings->all();
		if (empty($omnify_settings['tracking_meta_enabled']) || empty($omnify_settings['tracking_meta_pixel_id'])) {
			return;
		}

		$omnify_pixel_id = sanitize_text_field((string) $omnify_settings['tracking_meta_pixel_id']);
		$omnify_url = add_query_arg(
			[
				'id'       => $omnify_pixel_id,
				'ev'       => 'PageView',
				'noscript' => '1',
			],
			'https://www.facebook.com/tr'
		);
		?>
		<noscript><img height="1" width="1" style="display:none" src="<?php echo esc_url($omnify_url); ?>" alt="" /></noscript>
		<?php
	}
}
