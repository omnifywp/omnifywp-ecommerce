<?php
/**
 * REST API Authentication and Key Management
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Api;

use WP_REST_Request;
use WP_Error;

if (! defined('ABSPATH')) {
	exit;
}

class Omnify_Auth {
	/**
	 * The currently authenticated API key details.
	 *
	 * @var array|null
	 */
	private static ?array $omnify_authenticated_key = null;

	/**
	 * Hooks into determine_current_user to authenticate requests via API keys.
	 *
	 * @param int|false $user_id The current user ID.
	 * @return int|false User ID if authenticated, else original value.
	 */
	public static function determine_current_user($omnify_user_id) {
		if (! empty($omnify_user_id)) {
			return $omnify_user_id;
		}

		// Ensure this is a REST API request
		if (! defined('REST_REQUEST') || ! REST_REQUEST) {
			return $omnify_user_id;
		}

		// Ensure we are hitting an Omnify endpoint
		$omnify_request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		if (! str_contains($omnify_request_uri, '/wp-json/omnify/')) {
			return $omnify_user_id;
		}

		$omnify_credentials = self::get_credentials();
		if (! $omnify_credentials) {
			return $omnify_user_id;
		}

		[$omnify_consumer_key, $omnify_consumer_secret] = $omnify_credentials;

		$omnify_key_hash = hash('sha256', $omnify_consumer_key);
		$omnify_keys     = get_option('omnify_api_keys', []);

		if (! is_array($omnify_keys)) {
			return $omnify_user_id;
		}

		foreach ($omnify_keys as &$omnify_key) {
			if (isset($omnify_key['consumer_key_hash']) && hash_equals($omnify_key['consumer_key_hash'], $omnify_key_hash)) {
				// Verify the secret hash
				if (hash_equals($omnify_key['consumer_secret_hash'], hash('sha256', $omnify_consumer_secret))) {
					// Update last used timestamp
					$omnify_key['last_used'] = current_time('mysql');
					update_option('omnify_api_keys', $omnify_keys);

					$omnify_user = get_userdata($omnify_key['user_id']);
					if ($omnify_user) {
						self::$omnify_authenticated_key = $omnify_key;
						return $omnify_user->ID;
					}
				}
				break;
			}
		}

		return $omnify_user_id;
	}

	/**
	 * Get credentials from the request.
	 *
	 * @return array|null [consumer_key, consumer_secret] or null if not found.
	 */
	private static function get_credentials(): ?array {
		$omnify_consumer_key    = '';
		$omnify_consumer_secret = '';

		// Check PHP Basic Auth credentials
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$omnify_consumer_key    = sanitize_text_field(wp_unslash($_SERVER['PHP_AUTH_USER']));
			$omnify_consumer_secret = sanitize_text_field(wp_unslash($_SERVER['PHP_AUTH_PW']));
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$omnify_auth_header = sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZATION']));
			if (str_starts_with(strtolower($omnify_auth_header), 'basic ')) {
				$omnify_decoded = base64_decode(substr($omnify_auth_header, 6));
				if ($omnify_decoded && str_contains($omnify_decoded, ':')) {
					[$omnify_consumer_key, $omnify_consumer_secret] = explode(':', $omnify_decoded, 2);
				}
			}
		}

		// Fallback to query variables if empty
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (empty($omnify_consumer_key) && isset($_GET['consumer_key']) && isset($_GET['consumer_secret'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$omnify_consumer_key    = sanitize_text_field(wp_unslash($_GET['consumer_key']));
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$omnify_consumer_secret = sanitize_text_field(wp_unslash($_GET['consumer_secret']));
		}

		if (empty($omnify_consumer_key) || empty($omnify_consumer_secret)) {
			return null;
		}

		return [$omnify_consumer_key, $omnify_consumer_secret];
	}

	/**
	 * Override can_manage capabilities if authenticated via API keys.
	 *
	 * @param bool|WP_Error   $permission Original permission callback result.
	 * @param WP_REST_Request $request    REST request object.
	 * @param mixed           $server     REST server instance.
	 * @return bool|WP_Error Modified permission or WP_Error.
	 */
	public static function rest_can_manage($omnify_permission, WP_REST_Request $omnify_request, $omnify_server) {
		if (self::$omnify_authenticated_key) {
			$omnify_permissions = self::$omnify_authenticated_key['permissions'] ?? 'read';
			$omnify_method      = $omnify_request->get_method();

			if ('read' === $omnify_permissions && 'GET' !== $omnify_method) {
				return new WP_Error(
					'omnify_rest_read_only',
					__('The API key provided is read-only.', 'omnifywp-ecommerce'),
					['status' => 403]
				);
			}

			if ('write' === $omnify_permissions && 'GET' === $omnify_method) {
				return new WP_Error(
					'omnify_rest_write_only',
					__('The API key provided is write-only.', 'omnifywp-ecommerce'),
					['status' => 403]
				);
			}

			return true;
		}

		return $omnify_permission;
	}

	/**
	 * Override is_logged_in checks if authenticated via API keys.
	 *
	 * @param bool|WP_Error   $permission Original permission callback result.
	 * @param WP_REST_Request $request    REST request.
	 * @param mixed           $server     REST server instance.
	 * @return bool|WP_Error Modified permission.
	 */
	public static function rest_is_logged_in($omnify_permission, WP_REST_Request $omnify_request, $omnify_server) {
		if (self::$omnify_authenticated_key) {
			return true;
		}

		return $omnify_permission;
	}
}
