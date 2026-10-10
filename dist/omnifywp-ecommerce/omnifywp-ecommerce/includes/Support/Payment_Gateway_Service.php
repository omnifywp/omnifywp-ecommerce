<?php
/**
 * Payment gateway service for Stripe, PayPal, Razorpay, Alipay, WeChat Pay and SSLCommerz integrations (including refunds).
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Settings\Omnify_Settings_Repository;
use WP_Error;

class Omnify_Payment_Gateway_Service {
	private Omnify_Settings_Repository $omnify_settings;

	public function __construct(Omnify_Settings_Repository $omnify_settings) {
		$this->omnify_settings = $omnify_settings;
	}

	public function refund_stripe_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_secret_key = $this->stripe_secret_key($omnify_settings);
		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_stripe_key_missing', __('Stripe secret key is missing.', 'omnifywp-ecommerce'));
		}

		$omnify_body = [
			'charge' => $omnify_transaction_id,
			'amount' => (string) $this->stripe_amount($omnify_amount, $omnify_currency),
		];

		$omnify_response = $this->stripe_request('refunds', $omnify_body, $omnify_secret_key);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_paypal_payment(string $omnify_capture_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_access_token = $this->paypal_access_token($omnify_settings);
		if (is_wp_error($omnify_access_token)) {
			return $omnify_access_token;
		}

		$omnify_body = [
			'amount' => [
				'value' => $this->paypal_amount_value($omnify_amount, $omnify_currency),
				'currency_code' => strtoupper($omnify_currency),
			],
			'note_to_payer' => __('Refund for order.', 'omnifywp-ecommerce'),
		];

		$omnify_response = $this->paypal_request('POST', "v2/payments/captures/{$omnify_capture_id}/refund", $omnify_body, $omnify_access_token, $omnify_settings);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_razorpay_payment(string $omnify_payment_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_key_id = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_id"] ?? ''));
		$omnify_key_secret = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_secret"] ?? ''));

		if ('' === $omnify_key_id || '' === $omnify_key_secret) {
			return new WP_Error('omnify_razorpay_credentials_missing', __('Razorpay credentials are missing.', 'omnifywp-ecommerce'));
		}

		$omnify_amount_paise = (int) round($omnify_amount * 100);

		$omnify_body = [
			'amount' => $omnify_amount_paise,
		];

		$omnify_response = $this->razorpay_request('payments/' . $omnify_payment_id . '/refund', $omnify_body, $omnify_key_id, $omnify_key_secret);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_alipay_payment(string $omnify_out_trade_no, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['alipay_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_app_id = trim((string) ($omnify_settings['alipay_app_id'] ?? ''));
		$omnify_private_key = trim((string) ($omnify_settings['alipay_merchant_private_key'] ?? ''));

		if ('' === $omnify_app_id || '' === $omnify_private_key) {
			return new WP_Error('omnify_alipay_credentials_missing', __('Alipay credentials are missing.', 'omnifywp-ecommerce'));
		}

		// Automated API refunds require enterprise RSA2 signing certificates
		return new WP_Error('omnify_alipay_automated_refund_unsupported', __('Automated API refunds for Alipay require enterprise RSA2 certificates. Please issue this refund directly in your Alipay Merchant Dashboard.', 'omnifywp-ecommerce'));
	}

	public function refund_wechat_payment(string $omnify_out_trade_no, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['wechat_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_mchid = trim((string) ($omnify_settings['wechat_mchid'] ?? ''));
		$omnify_key = trim((string) ($omnify_settings['wechat_key'] ?? ''));

		if ('' === $omnify_mchid || '' === $omnify_key) {
			return new WP_Error('omnify_wechat_credentials_missing', __('WeChat Pay credentials are missing.', 'omnifywp-ecommerce'));
		}

		// Automated API refunds require client mTLS certificates (.pem)
		return new WP_Error('omnify_wechat_automated_refund_unsupported', __('Automated API refunds for WeChat Pay require client SSL/mTLS certificates. Please issue this refund directly in your WeChat Pay Merchant Platform.', 'omnifywp-ecommerce'));
	}

	public function refund_sslcommerz_payment(string $omnify_bank_tran_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_store_id = trim((string) ($omnify_settings['sslcommerz_store_id'] ?? ''));
		$omnify_store_passwd = trim((string) ($omnify_settings['sslcommerz_store_password'] ?? ''));

		if ('' === $omnify_store_id || '' === $omnify_store_passwd) {
			return new WP_Error('omnify_sslcommerz_credentials_missing', __('SSLCommerz credentials are missing.', 'omnifywp-ecommerce'));
		}

		$omnify_base = $omnify_mode === 'live' 
			? 'https://securepay.sslcommerz.com' 
			: 'https://sandbox.sslcommerz.com';

		$omnify_body = [
			'store_id' => $omnify_store_id,
			'store_passwd' => $omnify_store_passwd,
			'refund_amount' => number_format($omnify_amount, 2, '.', ''),
			'refund_remarks' => 'Refund for order',
			'bank_tran_id' => $omnify_bank_tran_id,
			'refe_id' => 'ref_' . uniqid(),
		];

		$omnify_response = wp_remote_post($omnify_base . '/validator/api/merchantTransIDvalidationAPI.php', [
			'timeout' => 30,
			'body' => $omnify_body,
		]);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_decoded = json_decode(wp_remote_retrieve_body($omnify_response), true);
		if (! empty($omnify_decoded['APIConnect']) && $omnify_decoded['APIConnect'] === 'DONE') {
			return (string) ($omnify_decoded['refund_ref_id'] ?? 'sslcommerz_refund_' . uniqid());
		}

		return new WP_Error('omnify_sslcommerz_refund_failed', $omnify_decoded['errorReason'] ?? 'SSLCommerz refund failed.');
	}

	public function refund_paystack_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));

		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_paystack_credentials_missing', __('Paystack secret key is missing.', 'omnifywp-ecommerce'));
		}

		$omnify_body = [
			'transaction' => $omnify_transaction_id,
			'amount'      => (int) round($omnify_amount * 100),
		];

		$omnify_response = wp_remote_post(
			'https://api.paystack.co/refund',
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_secret_key,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body' => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300 || empty($omnify_decoded['status'])) {
			return new WP_Error('omnify_paystack_refund_failed', $omnify_decoded['message'] ?? __('Paystack refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['data']['id'] ?? ('paystack_ref_' . uniqid()));
	}

	public function refund_tap_payment(string $omnify_charge_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));

		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_tap_credentials_missing', __('Tap secret key is missing.', 'omnifywp-ecommerce'));
		}

		$omnify_curr_upper = strtoupper($omnify_currency);
		$omnify_three_decimal = ['KWD', 'BHD', 'OMR'];
		$omnify_formatted_amount = in_array($omnify_curr_upper, $omnify_three_decimal, true)
			? (float) number_format($omnify_amount, 3, '.', '')
			: (float) number_format($omnify_amount, 2, '.', '');

		$omnify_body = [
			'charge_id' => $omnify_charge_id,
			'amount'    => $omnify_formatted_amount,
			'currency'  => $omnify_curr_upper,
			'reason'    => 'Order refund',
		];

		$omnify_response = wp_remote_post(
			'https://api.tap.company/v2/refunds',
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_secret_key,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body' => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			return new WP_Error('omnify_tap_refund_failed', $omnify_decoded['errors'][0]['description'] ?? __('Tap refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['id'] ?? ('tap_ref_' . uniqid()));
	}

	public function refund_mollie_payment(string $omnify_payment_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_api_key = trim((string) ($omnify_settings["mollie_{$omnify_mode}_api_key"] ?? ''));

		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_mollie_credentials_missing', __('Mollie API key is missing.', 'omnifywp-ecommerce'));
		}

		$omnify_body = [
			'amount' => [
				'currency' => strtoupper($omnify_currency),
				'value'    => number_format($omnify_amount, 2, '.', ''),
			],
			'description' => 'Refund for order',
		];

		$omnify_response = wp_remote_post(
			'https://api.mollie.com/v2/payments/' . rawurlencode($omnify_payment_id) . '/refunds',
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_api_key,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body' => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			return new WP_Error('omnify_mollie_refund_failed', $omnify_decoded['detail'] ?? __('Mollie refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['id'] ?? ('mollie_ref_' . uniqid()));
	}

	public function refund_khalti_payment(string $omnify_pidx, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_is_live  = 'live' === ($omnify_settings['khalti_mode'] ?? '');
		$omnify_secret_key = trim((string) ($omnify_is_live ? ($omnify_settings['khalti_live_secret_key'] ?? '') : ($omnify_settings['khalti_test_secret_key'] ?? '')));

		if ('' === $omnify_secret_key) {
			return new WP_Error('omnify_khalti_credentials_missing', __('Khalti secret key is missing.', 'omnifywp-ecommerce'));
		}

		// Khalti automated merchant refunds are not supported via the public API
		return new WP_Error('omnify_khalti_automated_refund_unsupported', __('Automated refunds are not supported via Khalti public API. Please process this refund in your Khalti Merchant Portal.', 'omnifywp-ecommerce'));
	}

	public function refund_esewa_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		// eSewa does not provide an automated merchant refund API
		return new WP_Error('omnify_esewa_automated_refund_unsupported', __('eSewa does not provide an automated merchant refund API. Please process this refund directly in the eSewa Merchant Portal.', 'omnifywp-ecommerce'));
	}

	// Helper methods copied from Rest_Server
	private function stripe_secret_key(array $omnify_settings): string {
		$omnify_mode = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';
		return trim((string) ($omnify_settings["stripe_{$omnify_mode}_secret_key"] ?? ''));
	}

	private function stripe_amount(float $omnify_amount, string $omnify_currency): int {
		$omnify_zero_decimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
		return in_array(strtoupper($omnify_currency), $omnify_zero_decimal, true)
			? (int) round($omnify_amount)
			: (int) round($omnify_amount * 100);
	}

	private function stripe_request(string $omnify_endpoint, array $omnify_body, string $omnify_secret_key): array|WP_Error {
		$omnify_response = wp_remote_post(
			'https://api.stripe.com/v1/' . ltrim($omnify_endpoint, '/'),
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_secret_key,
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => $omnify_body,
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['error']['message'] ?? __('Stripe request failed.', 'omnifywp-ecommerce')) : __('Stripe request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_stripe_request_failed', $omnify_message);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function paypal_base_url(array $omnify_settings): string {
		return 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox')
			? 'https://api-m.paypal.com/'
			: 'https://api-m.sandbox.paypal.com/';
	}

	private function paypal_credentials(array $omnify_settings): array {
		$omnify_mode = 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox';

		return [
			'client_id' => trim((string) ($omnify_settings["paypal_{$omnify_mode}_client_id"] ?? '')),
			'secret' => trim((string) ($omnify_settings["paypal_{$omnify_mode}_secret"] ?? '')),
		];
	}

	private function paypal_access_token(array $omnify_settings): string|WP_Error {
		$omnify_credentials = $this->paypal_credentials($omnify_settings);
		if ('' === $omnify_credentials['client_id'] || '' === $omnify_credentials['secret']) {
			return new WP_Error('omnify_paypal_credentials_missing', __('PayPal credentials are missing.', 'omnifywp-ecommerce'));
		}

		$omnify_response = wp_remote_post(
			$this->paypal_base_url($omnify_settings) . 'v1/oauth2/token',
			[
				'timeout' => 30,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($omnify_credentials['client_id'] . ':' . $omnify_credentials['secret']),
				],
				'body' => [
					'grant_type' => 'client_credentials',
				],
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300 || ! is_array($omnify_decoded) || empty($omnify_decoded['access_token'])) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['error_description'] ?? $omnify_decoded['error'] ?? __('PayPal authentication failed.', 'omnifywp-ecommerce')) : __('PayPal authentication failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_paypal_auth_failed', $omnify_message);
		}

		return (string) $omnify_decoded['access_token'];
	}

	private function paypal_request(string $omnify_method, string $omnify_endpoint, mixed $omnify_body, string $omnify_access_token, array $omnify_settings): array|WP_Error {
		$omnify_args = [
			'method' => strtoupper($omnify_method),
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_access_token,
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
		];

		if (null !== $omnify_body) {
			$omnify_args['body'] = wp_json_encode($omnify_body);
		}

		$omnify_response = wp_remote_request($this->paypal_base_url($omnify_settings) . ltrim($omnify_endpoint, '/'), $omnify_args);
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) ? (string) ($omnify_decoded['message'] ?? $omnify_decoded['name'] ?? __('PayPal request failed.', 'omnifywp-ecommerce')) : __('PayPal request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_paypal_request_failed', $omnify_message);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function paypal_amount_value(float $omnify_amount, string $omnify_currency): string {
		$omnify_zero_decimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

		return in_array(strtoupper($omnify_currency), $omnify_zero_decimal, true)
			? (string) (int) round($omnify_amount)
			: number_format($omnify_amount, 2, '.', '');
	}

	private function razorpay_request(string $omnify_endpoint, array $omnify_body, string $omnify_key_id, string $omnify_key_secret): array|WP_Error {
		$omnify_response = wp_remote_post(
			'https://api.razorpay.com/v1/' . ltrim($omnify_endpoint, '/'),
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($omnify_key_id . ':' . $omnify_key_secret),
					'Content-Type' => 'application/json',
				],
				'body' => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_message = is_array($omnify_decoded) && ! empty($omnify_decoded['error']['description']) 
				? $omnify_decoded['error']['description'] 
				: __('Razorpay refund request failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_razorpay_refund_failed', $omnify_message);
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}
}
