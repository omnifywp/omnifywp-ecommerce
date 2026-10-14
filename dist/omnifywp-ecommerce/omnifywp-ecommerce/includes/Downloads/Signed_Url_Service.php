<?php
/**
 * Signed expiring download URLs.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Downloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Omnify_Signed_Url_Service {
	public function create(int $omnify_file_id, int $omnify_expires_in = 900, ?int $omnify_customer_id = null): string {
		$omnify_expires = time() + max(60, $omnify_expires_in);
		$omnify_payload = $this->payload($omnify_file_id, $omnify_expires, $omnify_customer_id);
		$omnify_signature = hash_hmac('sha256', $omnify_payload, wp_salt('auth'));

		return add_query_arg(
			[
				'omnify_download' => 1,
				'file_id'         => $omnify_file_id,
				'expires'         => $omnify_expires,
				'customer_id'     => $omnify_customer_id ?: 0,
				'signature'       => $omnify_signature,
			],
			home_url('/')
		);
	}

	public function verify(int $omnify_file_id, int $omnify_expires, string $omnify_signature, ?int $omnify_customer_id = null): bool {
		if ($omnify_expires < time()) {
			return false;
		}

		$omnify_expected = hash_hmac('sha256', $this->payload($omnify_file_id, $omnify_expires, $omnify_customer_id), wp_salt('auth'));

		return hash_equals($omnify_expected, $omnify_signature);
	}

	private function payload(int $omnify_file_id, int $omnify_expires, ?int $omnify_customer_id): string {
		return $omnify_file_id . '|' . $omnify_expires . '|' . (int) $omnify_customer_id;
	}
}
