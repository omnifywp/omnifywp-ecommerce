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
		$omnify_mode = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = $this->stripe_secret_key($omnify_settings);

		// Handle simulated or zero-cost transactions
		if (str_starts_with($omnify_transaction_id, 'cs_test_') || str_starts_with($omnify_transaction_id, 'test_') || str_starts_with($omnify_transaction_id, 'free_')) {
			return 're_test_' . uniqid();
		}

		if ('' === $omnify_secret_key) {
			if ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
			return new WP_Error('omnify_stripe_key_missing', __('Stripe secret key is missing.', 'omnifywp-ecommerce'));
		}

		// If a checkout session ID was stored, retrieve the session to extract the payment intent
		if (str_starts_with($omnify_transaction_id, 'cs_')) {
			$omnify_session = $this->stripe_request('checkout/sessions/' . rawurlencode($omnify_transaction_id), [], $omnify_secret_key, 'GET');
			if (! is_wp_error($omnify_session) && ! empty($omnify_session['payment_intent'])) {
				$omnify_transaction_id = (string) $omnify_session['payment_intent'];
			} elseif ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
		}

		$omnify_target_key = str_starts_with($omnify_transaction_id, 'pi_') ? 'payment_intent' : 'charge';

		$omnify_body = [
			$omnify_target_key => $omnify_transaction_id,
			'amount'           => (string) $this->stripe_amount($omnify_amount, $omnify_currency),
		];

		$omnify_response = $this->stripe_request('refunds', $omnify_body, $omnify_secret_key);
		if (is_wp_error($omnify_response)) {
			if ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_paypal_payment(string $omnify_capture_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox';

		if (str_starts_with($omnify_capture_id, 'PAYID-TEST-') || str_starts_with($omnify_capture_id, 'test_') || str_starts_with($omnify_capture_id, 'free_')) {
			return 'refund_test_' . uniqid();
		}

		$omnify_access_token = $this->paypal_access_token($omnify_settings);
		if (is_wp_error($omnify_access_token)) {
			if ('sandbox' === $omnify_mode) {
				return 'refund_test_' . uniqid();
			}
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
			if ('sandbox' === $omnify_mode) {
				return 'refund_test_' . uniqid();
			}
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_razorpay_payment(string $omnify_payment_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_key_id = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_id"] ?? ''));
		$omnify_key_secret = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_secret"] ?? ''));

		if (str_starts_with($omnify_payment_id, 'order_test_') || str_starts_with($omnify_payment_id, 'pay_test_') || str_starts_with($omnify_payment_id, 'test_') || str_starts_with($omnify_payment_id, 'free_')) {
			return 'rfnd_test_' . uniqid();
		}

		if ('' === $omnify_key_id || '' === $omnify_key_secret) {
			if ('test' === $omnify_mode) {
				return 'rfnd_test_' . uniqid();
			}
			return new WP_Error('omnify_razorpay_credentials_missing', __('Razorpay credentials are missing.', 'omnifywp-ecommerce'));
		}

		$omnify_amount_paise = (int) round($omnify_amount * 100);

		$omnify_body = [
			'amount' => $omnify_amount_paise,
		];

		$omnify_response = $this->razorpay_request('payments/' . $omnify_payment_id . '/refund', $omnify_body, $omnify_key_id, $omnify_key_secret);
		if (is_wp_error($omnify_response)) {
			if ('test' === $omnify_mode) {
				return 'rfnd_test_' . uniqid();
			}
			return $omnify_response;
		}

		return (string) ($omnify_response['id'] ?? '');
	}

	public function refund_alipay_payment(string $omnify_out_trade_no, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['alipay_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_app_id = trim((string) ($omnify_settings['alipay_app_id'] ?? ''));
		$omnify_private_key = trim((string) ($omnify_settings['alipay_merchant_private_key'] ?? ''));

		if (str_starts_with($omnify_out_trade_no, 'alipay_') || str_starts_with($omnify_out_trade_no, 'test_') || str_starts_with($omnify_out_trade_no, 'free_')) {
			if ('sandbox' === $omnify_mode || '' === $omnify_app_id || '' === $omnify_private_key) {
				return 'alipay_ref_test_' . uniqid();
			}
		}

		if ('' === $omnify_app_id || '' === $omnify_private_key) {
			if ('sandbox' === $omnify_mode) {
				return 'alipay_ref_test_' . uniqid();
			}
			return new WP_Error('omnify_alipay_credentials_missing', __('Alipay credentials are missing.', 'omnifywp-ecommerce'));
		}

		if ($omnify_amount <= 0) {
			return new WP_Error('omnify_alipay_invalid_amount', __('Refund amount must be greater than zero.', 'omnifywp-ecommerce'));
		}

		$omnify_gateway_url = 'live' === $omnify_mode
			? 'https://openapi.alipay.com/gateway.do'
			: 'https://openapi-sandbox.dl.alipaydev.com/gateway.do';

		$omnify_params = [
			'app_id'      => $omnify_app_id,
			'method'      => 'alipay.trade.refund',
			'charset'     => 'utf-8',
			'sign_type'   => 'RSA2',
			'timestamp'   => gmdate('Y-m-d H:i:s'),
			'version'     => '1.0',
			'biz_content' => wp_json_encode([
				'out_trade_no'  => $omnify_out_trade_no,
				'refund_amount' => number_format($omnify_amount, 2, '.', ''),
				'refund_reason' => __('Order refund requested via Omnify e-Commerce', 'omnifywp-ecommerce'),
			]),
		];

		$omnify_sign = $this->sign_alipay_params($omnify_params, $omnify_private_key);
		if ('' === $omnify_sign) {
			if ('sandbox' === $omnify_mode) {
				return 'alipay_ref_test_' . uniqid();
			}
			return new WP_Error('omnify_alipay_sign_failed', __('Failed to sign the Alipay refund request. Please check the merchant private key.', 'omnifywp-ecommerce'));
		}
		$omnify_params['sign'] = $omnify_sign;

		$omnify_response = wp_remote_post($omnify_gateway_url, [
			'timeout' => 30,
			'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'],
			'body'    => $omnify_params,
		]);

		if (is_wp_error($omnify_response)) {
			if ('sandbox' === $omnify_mode) {
				return 'alipay_ref_test_' . uniqid();
			}
			return $omnify_response;
		}

		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		$omnify_result  = is_array($omnify_decoded) ? ($omnify_decoded['alipay_trade_refund_response'] ?? null) : null;

		if (! is_array($omnify_result) || '10000' !== (string) ($omnify_result['code'] ?? '')) {
			if ('sandbox' === $omnify_mode) {
				return 'alipay_ref_test_' . uniqid();
			}
			$omnify_msg = is_array($omnify_result)
				? (string) ($omnify_result['sub_msg'] ?? $omnify_result['msg'] ?? __('Alipay refund failed.', 'omnifywp-ecommerce'))
				: __('Alipay refund failed.', 'omnifywp-ecommerce');
			return new WP_Error('omnify_alipay_refund_failed', $omnify_msg);
		}

		return (string) ($omnify_result['trade_no'] ?? $omnify_result['out_trade_no'] ?? ('alipay_ref_' . uniqid()));
	}

	public function refund_wechat_payment(string $omnify_out_trade_no, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['wechat_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_mch_id = trim((string) ($omnify_settings['wechat_mch_id'] ?? ($omnify_settings['wechat_mchid'] ?? '')));
		$omnify_api_key = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));

		if (str_starts_with($omnify_out_trade_no, 'wechat_') || str_starts_with($omnify_out_trade_no, 'test_') || str_starts_with($omnify_out_trade_no, 'free_')) {
			if ('' === $omnify_mch_id || '' === $omnify_api_key) {
				return 'wechat_ref_test_' . uniqid();
			}
		}

		if ('' === $omnify_mch_id || '' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_credentials_missing', __('WeChat Pay credentials are missing.', 'omnifywp-ecommerce'));
		}

		if ($omnify_amount <= 0) {
			return new WP_Error('omnify_wechat_invalid_amount', __('Refund amount must be greater than zero.', 'omnifywp-ecommerce'));
		}

		// The v3 refund API requires the original transaction total, so look it up first.
		$omnify_txn = $this->wechat_v3_request(
			'GET',
			'/v3/pay/transactions/out-trade-no/' . rawurlencode($omnify_out_trade_no) . '?mchid=' . rawurlencode($omnify_mch_id),
			null,
			$omnify_settings
		);
		if (is_wp_error($omnify_txn)) {
			if ('live' === $omnify_mode) {
				return $omnify_txn;
			}
			return 'wechat_ref_test_' . uniqid();
		}

		$omnify_total_fen = (int) ($omnify_txn['amount']['total'] ?? 0);
		if ($omnify_total_fen <= 0) {
			if ('live' === $omnify_mode) {
				return new WP_Error('omnify_wechat_refund_lookup_failed', __('WeChat Pay could not resolve the original transaction total for this refund.', 'omnifywp-ecommerce'));
			}
			return 'wechat_ref_test_' . uniqid();
		}

		$omnify_refund_fen = (int) round($omnify_amount * 100);
		if ($omnify_refund_fen > $omnify_total_fen) {
			$omnify_refund_fen = $omnify_total_fen;
		}

		$omnify_amounts = [
			'refund' => $omnify_refund_fen,
			'total'  => $omnify_total_fen,
		];
		$omnify_currency_upper = strtoupper($omnify_currency);
		if ('' !== $omnify_currency_upper && 'CNY' !== $omnify_currency_upper) {
			$omnify_amounts['currency'] = $omnify_currency_upper;
		}

		$omnify_refund_no = 'omnify_' . preg_replace('/[^A-Za-z0-9]/', '', $omnify_out_trade_no) . '_' . time();
		$omnify_response = $this->wechat_v3_request('POST', '/v3/refund/domestic/refunds', [
			'out_trade_no'  => $omnify_out_trade_no,
			'out_refund_no' => $omnify_refund_no,
			'reason'        => __('Order refund requested via Omnify e-Commerce', 'omnifywp-ecommerce'),
			'amount'        => $omnify_amounts,
		], $omnify_settings);

		if (is_wp_error($omnify_response)) {
			if ('live' === $omnify_mode) {
				return $omnify_response;
			}
			return 'wechat_ref_test_' . uniqid();
		}

		return (string) ($omnify_response['refund_id'] ?? $omnify_refund_no);
	}

	public function refund_sslcommerz_payment(string $omnify_bank_tran_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_store_id = trim((string) ('live' === $omnify_mode
			? ($omnify_settings['sslcommerz_store_id'] ?? '')
			: ($omnify_settings['sslcommerz_test_store_id'] ?? ($omnify_settings['sslcommerz_store_id'] ?? ''))));
		$omnify_store_passwd = trim((string) ('live' === $omnify_mode
			? ($omnify_settings['sslcommerz_store_password'] ?? ($omnify_settings['sslcommerz_store_passwd'] ?? ''))
			: ($omnify_settings['sslcommerz_test_store_passwd'] ?? ($omnify_settings['sslcommerz_store_password'] ?? ($omnify_settings['sslcommerz_store_passwd'] ?? '')))));

		if (str_starts_with($omnify_bank_tran_id, 'ssl_') || str_starts_with($omnify_bank_tran_id, 'test_') || str_starts_with($omnify_bank_tran_id, 'free_')) {
			if ('sandbox' === $omnify_mode || '' === $omnify_store_id || '' === $omnify_store_passwd) {
				return 'ssl_ref_test_' . uniqid();
			}
		}

		if ('' === $omnify_store_id || '' === $omnify_store_passwd) {
			if ('sandbox' === $omnify_mode) {
				return 'ssl_ref_test_' . uniqid();
			}
			return new WP_Error('omnify_sslcommerz_credentials_missing', __('SSLCommerz credentials are missing.', 'omnifywp-ecommerce'));
		}

		$omnify_base = $omnify_mode === 'live' 
			? 'https://securepay.sslcommerz.com' 
			: 'https://sandbox.sslcommerz.com';

		$omnify_params = [
			'store_id'       => $omnify_store_id,
			'store_passwd'   => $omnify_store_passwd,
			'refund_amount'  => number_format($omnify_amount, 2, '.', ''),
			'refund_remarks' => 'Refund for order',
			'bank_tran_id'   => $omnify_bank_tran_id,
			'refe_id'        => 'ref_' . uniqid(),
			'format'         => 'json',
		];

		$omnify_response = wp_remote_post($omnify_base . '/validator/api/merchantTransIDvalidationAPI.php', [
			'timeout' => 30,
			'body'    => $omnify_params,
		]);

		if (is_wp_error($omnify_response)) {
			if ('sandbox' === $omnify_mode) {
				return 'ssl_ref_test_' . uniqid();
			}
			return $omnify_response;
		}

		$omnify_decoded = json_decode(wp_remote_retrieve_body($omnify_response), true);
		if (! empty($omnify_decoded['APIConnect']) && $omnify_decoded['APIConnect'] === 'DONE') {
			return (string) ($omnify_decoded['refund_ref_id'] ?? 'sslcommerz_refund_' . uniqid());
		}

		if ('sandbox' === $omnify_mode) {
			return 'ssl_ref_test_' . uniqid();
		}

		return new WP_Error('omnify_sslcommerz_refund_failed', $omnify_decoded['errorReason'] ?? 'SSLCommerz refund failed.');
	}

	public function refund_paystack_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));

		if (str_starts_with($omnify_transaction_id, 'pstk_') || str_starts_with($omnify_transaction_id, 'test_') || str_starts_with($omnify_transaction_id, 'free_')) {
			return 'pstk_ref_test_' . uniqid();
		}

		if ('' === $omnify_secret_key) {
			if ('test' === $omnify_mode) {
				return 'pstk_ref_test_' . uniqid();
			}
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
			if ('test' === $omnify_mode) {
				return 'pstk_ref_test_' . uniqid();
			}
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300 || empty($omnify_decoded['status'])) {
			if ('test' === $omnify_mode) {
				return 'pstk_ref_test_' . uniqid();
			}
			return new WP_Error('omnify_paystack_refund_failed', $omnify_decoded['message'] ?? __('Paystack refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['data']['id'] ?? ('paystack_ref_' . uniqid()));
	}

	public function refund_tap_payment(string $omnify_charge_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret_key = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));

		if (str_starts_with($omnify_charge_id, 'ch_test_') || str_starts_with($omnify_charge_id, 'test_') || str_starts_with($omnify_charge_id, 'free_')) {
			return 'tap_ref_test_' . uniqid();
		}

		if ('' === $omnify_secret_key) {
			if ('test' === $omnify_mode) {
				return 'tap_ref_test_' . uniqid();
			}
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
			if ('test' === $omnify_mode) {
				return 'tap_ref_test_' . uniqid();
			}
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			if ('test' === $omnify_mode) {
				return 'tap_ref_test_' . uniqid();
			}
			return new WP_Error('omnify_tap_refund_failed', $omnify_decoded['errors'][0]['description'] ?? __('Tap refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['id'] ?? ('tap_ref_' . uniqid()));
	}

	public function refund_mollie_payment(string $omnify_payment_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_api_key = trim((string) ($omnify_settings["mollie_{$omnify_mode}_api_key"] ?? ''));

		if (str_starts_with($omnify_payment_id, 'tr_test_') || str_starts_with($omnify_payment_id, 'test_') || str_starts_with($omnify_payment_id, 'free_')) {
			return 're_test_' . uniqid();
		}

		if ('' === $omnify_api_key) {
			if ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
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
			if ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			if ('test' === $omnify_mode) {
				return 're_test_' . uniqid();
			}
			return new WP_Error('omnify_mollie_refund_failed', $omnify_decoded['detail'] ?? __('Mollie refund failed.', 'omnifywp-ecommerce'));
		}

		return (string) ($omnify_decoded['id'] ?? ('mollie_ref_' . uniqid()));
	}

	public function refund_khalti_payment(string $omnify_pidx, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_is_live  = 'live' === ($omnify_settings['khalti_mode'] ?? '');
		$omnify_secret_key = trim((string) ($omnify_is_live ? ($omnify_settings['khalti_live_secret_key'] ?? '') : ($omnify_settings['khalti_test_secret_key'] ?? '')));

		if (str_starts_with($omnify_pidx, 'khalti_test_') || str_starts_with($omnify_pidx, 'test_') || str_starts_with($omnify_pidx, 'free_') || ! $omnify_is_live) {
			return 'khalti_ref_test_' . uniqid();
		}

		if ('' === $omnify_secret_key) {
			return 'khalti_ref_manual_' . uniqid();
		}

		// Khalti automated merchant refunds are not supported via the public API
		return new WP_Error('omnify_khalti_automated_refund_unsupported', __('Automated refunds are not supported via Khalti public API. Please process this refund in your Khalti Merchant Portal.', 'omnifywp-ecommerce'));
	}

	public function refund_esewa_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_is_live  = 'live' === ($omnify_settings['esewa_mode'] ?? '');

		if (str_starts_with($omnify_transaction_id, 'esewa_') || str_starts_with($omnify_transaction_id, 'test_') || str_starts_with($omnify_transaction_id, 'free_') || ! $omnify_is_live) {
			return 'esewa_ref_test_' . uniqid();
		}

		// eSewa does not provide an automated merchant refund API
		return new WP_Error('omnify_esewa_automated_refund_unsupported', __('eSewa does not provide an automated merchant refund API. Please process this refund directly in the eSewa Merchant Portal.', 'omnifywp-ecommerce'));
	}

	public function refund_paddle_payment(string $omnify_transaction_id, float $omnify_amount, string $omnify_currency): string|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_mode = 'live' === ($omnify_settings['paddle_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_api_key = trim((string) ($omnify_settings["paddle_{$omnify_mode}_api_key"] ?? ''));

		if (str_starts_with($omnify_transaction_id, 'txn_test_') || str_starts_with($omnify_transaction_id, 'test_') || str_starts_with($omnify_transaction_id, 'free_')) {
			return 'adj_test_' . uniqid();
		}

		if ('' === $omnify_api_key) {
			if ('sandbox' === $omnify_mode) {
				return 'adj_test_' . uniqid();
			}
			return new WP_Error('omnify_paddle_credentials_missing', __('Paddle API key is missing.', 'omnifywp-ecommerce'));
		}

		if ($omnify_amount <= 0) {
			return new WP_Error('omnify_paddle_invalid_amount', __('Refund amount must be greater than zero.', 'omnifywp-ecommerce'));
		}

		$omnify_base = 'live' === $omnify_mode ? 'https://api.paddle.com' : 'https://sandbox-api.paddle.com';

		// Check transaction details from Paddle to decide full vs partial adjustment
		$omnify_txn_res = wp_remote_get(
			$omnify_base . '/transactions/' . rawurlencode($omnify_transaction_id),
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_api_key,
					'Accept'        => 'application/json',
				],
			]
		);

		$omnify_is_partial = false;
		$omnify_adj_items  = [];

		if (! is_wp_error($omnify_txn_res) && 200 === (int) wp_remote_retrieve_response_code($omnify_txn_res)) {
			$omnify_txn_body = json_decode((string) wp_remote_retrieve_body($omnify_txn_res), true);
			$omnify_txn_data = is_array($omnify_txn_body) ? ($omnify_txn_body['data'] ?? []) : [];
			$omnify_txn_currency = strtoupper((string) ($omnify_txn_data['currency_code'] ?? $omnify_currency));
			$omnify_is_zero_decimal = in_array($omnify_txn_currency, ['JPY', 'KRW', 'VND', 'CLP', 'PYG', 'UGX', 'BIF', 'DJF', 'GNF', 'RWF'], true);
			$omnify_factor = $omnify_is_zero_decimal ? 1 : 100;

			$omnify_grand_total = isset($omnify_txn_data['details']['totals']['grand_total'])
				? (int) $omnify_txn_data['details']['totals']['grand_total']
				: (isset($omnify_txn_data['details']['totals']['total']) ? (int) $omnify_txn_data['details']['totals']['total'] : 0);

			$omnify_refund_minor = (int) round($omnify_amount * $omnify_factor);

			if ($omnify_grand_total > 0 && $omnify_refund_minor < $omnify_grand_total) {
				// Find line item to adjust
				$omnify_line_items = $omnify_txn_data['details']['line_items'] ?? ($omnify_txn_data['items'] ?? []);
				if (! empty($omnify_line_items) && is_array($omnify_line_items)) {
					$omnify_first_item = reset($omnify_line_items);
					$omnify_item_id    = (string) ($omnify_first_item['id'] ?? ($omnify_first_item['item_id'] ?? ''));
					if ('' !== $omnify_item_id) {
						$omnify_is_partial = true;
						$omnify_adj_items  = [
							[
								'item_id' => $omnify_item_id,
								'type'    => 'partial',
								'amount'  => (string) $omnify_refund_minor,
							],
						];
					}
				}
			}
		}

		$omnify_body = [
			'action'         => 'refund',
			'transaction_id' => $omnify_transaction_id,
			'reason'         => __('Order refund requested via Omnify e-Commerce', 'omnifywp-ecommerce'),
			'type'           => $omnify_is_partial ? 'partial' : 'full',
		];

		if ($omnify_is_partial && ! empty($omnify_adj_items)) {
			$omnify_body['items'] = $omnify_adj_items;
		}

		$omnify_response = wp_remote_post(
			$omnify_base . '/adjustments',
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $omnify_api_key,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body'    => wp_json_encode($omnify_body),
			]
		);

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			$omnify_msg = is_array($omnify_decoded) && ! empty($omnify_decoded['error']['detail'])
				? (string) $omnify_decoded['error']['detail']
				: (is_array($omnify_decoded) && ! empty($omnify_decoded['error']['message']) ? (string) $omnify_decoded['error']['message'] : __('Paddle adjustment failed.', 'omnifywp-ecommerce'));
			return new WP_Error('omnify_paddle_refund_failed', $omnify_msg);
		}

		return (string) ($omnify_decoded['data']['id'] ?? ('paddle_adj_' . uniqid()));
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

	private function stripe_request(string $omnify_endpoint, array $omnify_body = [], string $omnify_secret_key = '', string $omnify_method = 'POST'): array|WP_Error {
		$omnify_args = [
			'method'  => strtoupper($omnify_method),
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $omnify_secret_key,
			],
		];

		if ('POST' === strtoupper($omnify_method)) {
			$omnify_args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
			$omnify_args['body'] = $omnify_body;
		}

		$omnify_response = wp_remote_request(
			'https://api.stripe.com/v1/' . ltrim($omnify_endpoint, '/'),
			$omnify_args
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

	// ==================== WeChat Pay v3 (with mutual TLS) ====================

	/**
	 * Perform a WeChat Pay v3 API request, attaching the merchant client certificate when configured.
	 *
	 * @param string     $omnify_method HTTP method.
	 * @param string     $omnify_path   Request path beginning with /v3/...
	 * @param array|null $omnify_body   JSON-serialisable request body (null for GET).
	 * @param array      $omnify_settings
	 * @return array<string, mixed>|WP_Error
	 */
	public function wechat_v3_request(string $omnify_method, string $omnify_path, ?array $omnify_body, array $omnify_settings): array|WP_Error {
		$omnify_mch_id      = trim((string) ($omnify_settings['wechat_mch_id'] ?? ($omnify_settings['wechat_mchid'] ?? '')));
		$omnify_serial      = trim((string) ($omnify_settings['wechat_serial_no'] ?? ''));
		$omnify_private_key = trim((string) ($omnify_settings['wechat_merchant_private_key'] ?? ''));

		if ('' === $omnify_mch_id || '' === $omnify_serial || '' === $omnify_private_key) {
			return new WP_Error('omnify_wechat_unconfigured', __('WeChat Pay Merchant ID, certificate serial number and merchant private key are required.', 'omnifywp-ecommerce'));
		}

		$omnify_body_json = (null === $omnify_body) ? '' : (string) wp_json_encode($omnify_body);
		$omnify_auth      = $this->build_wechat_v3_auth_header($omnify_method, $omnify_path, $omnify_body_json, $omnify_mch_id, $omnify_serial, $omnify_private_key);
		if ('' === $omnify_auth) {
			return new WP_Error('omnify_wechat_sign_failed', __('Failed to sign the WeChat Pay request. Please check the merchant private key.', 'omnifywp-ecommerce'));
		}

		$omnify_args = [
			'method'  => strtoupper($omnify_method),
			'timeout' => 30,
			'headers' => [
				'Authorization' => $omnify_auth,
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'User-Agent'    => 'Omnify-eCommerce/' . OMNIFY_VERSION,
			],
		];
		if (null !== $omnify_body) {
			$omnify_args['body'] = $omnify_body_json;
		}

		$omnify_cleanup  = $this->attach_wechat_client_certificate($omnify_settings);
		$omnify_response = wp_remote_request('https://api.mch.weixin.qq.com' . $omnify_path, $omnify_args);

		if (is_callable($omnify_cleanup)) {
			$omnify_cleanup();
		}

		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code    = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);

		if ($omnify_code < 200 || $omnify_code >= 300) {
			return new WP_Error('omnify_wechat_request_failed', $this->gateway_api_error($omnify_decoded, __('WeChat Pay request failed.', 'omnifywp-ecommerce')));
		}

		return is_array($omnify_decoded) ? $omnify_decoded : [];
	}

	private function build_wechat_v3_auth_header(string $omnify_method, string $omnify_path, string $omnify_body, string $omnify_mch_id, string $omnify_serial, string $omnify_private_key): string {
		$omnify_nonce     = wp_generate_password(32, false);
		$omnify_timestamp = (string) time();
		$omnify_message   = $omnify_method . "\n" . $omnify_path . "\n" . $omnify_timestamp . "\n" . $omnify_nonce . "\n" . $omnify_body . "\n";

		$omnify_key = trim($omnify_private_key);
		if (! str_contains($omnify_key, '-----BEGIN RSA PRIVATE KEY-----') && ! str_contains($omnify_key, '-----BEGIN PRIVATE KEY-----')) {
			$omnify_key = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($omnify_key, 64, "\n") . "-----END PRIVATE KEY-----";
		}

		$omnify_pkey = openssl_pkey_get_private($omnify_key);
		if (! $omnify_pkey) {
			return '';
		}

		$omnify_raw_sig = '';
		if (! openssl_sign($omnify_message, $omnify_raw_sig, $omnify_pkey, OPENSSL_ALGO_SHA256)) {
			return '';
		}

		return sprintf(
			'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%s",serial_no="%s",signature="%s"',
			$omnify_mch_id,
			$omnify_nonce,
			$omnify_timestamp,
			$omnify_serial,
			base64_encode($omnify_raw_sig)
		);
	}

	/**
	 * Register a scoped curl hook that presents the merchant client certificate for WeChat Pay v3 (mTLS).
	 *
	 * @return callable|null Cleanup callback when a certificate was attached, null otherwise.
	 */
	private function attach_wechat_client_certificate(array $omnify_settings): ?callable {
		$omnify_cert = trim((string) ($omnify_settings['wechat_client_cert'] ?? ''));
		$omnify_key  = trim((string) ($omnify_settings['wechat_client_key'] ?? ''));
		if ('' === $omnify_cert || '' === $omnify_key) {
			return null;
		}

		if (! function_exists('wp_tempnam')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$omnify_cert_path = wp_tempnam('omnify-wechat-cert');
		$omnify_key_path  = wp_tempnam('omnify-wechat-key');
		file_put_contents($omnify_cert_path, $omnify_cert); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents($omnify_key_path, $omnify_key); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		$omnify_hook = static function ($omnify_handle) use ($omnify_cert_path, $omnify_key_path): void {
			curl_setopt($omnify_handle, CURLOPT_SSLCERT, $omnify_cert_path);
			curl_setopt($omnify_handle, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($omnify_handle, CURLOPT_SSLKEY, $omnify_key_path);
			curl_setopt($omnify_handle, CURLOPT_SSLKEYTYPE, 'PEM');
		};

		add_action('http_api_curl', $omnify_hook, 10, 1);

		return static function () use ($omnify_hook, $omnify_cert_path, $omnify_key_path): void {
			remove_action('http_api_curl', $omnify_hook, 10);
			if (file_exists($omnify_cert_path)) {
				unlink($omnify_cert_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
			if (file_exists($omnify_key_path)) {
				unlink($omnify_key_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		};
	}

	// ==================== Alipay signing ====================

	private function sign_alipay_params(array $omnify_params, string $omnify_private_key): string {
		unset($omnify_params['sign'], $omnify_params['sign_type']);
		ksort($omnify_params);

		$omnify_string_to_sign = '';
		foreach ($omnify_params as $omnify_key => $omnify_val) {
			if ('' !== $omnify_val && null !== $omnify_val) {
				$omnify_string_to_sign .= $omnify_key . '=' . $omnify_val . '&';
			}
		}
		$omnify_string_to_sign = rtrim($omnify_string_to_sign, '&');

		$omnify_key_str = trim($omnify_private_key);
		if (! str_contains($omnify_key_str, '-----BEGIN RSA PRIVATE KEY-----') && ! str_contains($omnify_key_str, '-----BEGIN PRIVATE KEY-----')) {
			$omnify_key_str = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($omnify_key_str, 64, "\n") . "-----END RSA PRIVATE KEY-----";
		}

		$omnify_res = openssl_pkey_get_private($omnify_key_str);
		if (! $omnify_res) {
			return '';
		}

		$omnify_signature = '';
		openssl_sign($omnify_string_to_sign, $omnify_signature, $omnify_res, OPENSSL_ALGO_SHA256);
		return base64_encode($omnify_signature);
	}

	private function private_key_is_parsable(string $omnify_private_key): bool {
		$omnify_key_str = trim($omnify_private_key);
		if ('' === $omnify_key_str) {
			return false;
		}
		if (! str_contains($omnify_key_str, '-----BEGIN RSA PRIVATE KEY-----') && ! str_contains($omnify_key_str, '-----BEGIN PRIVATE KEY-----')) {
			$omnify_key_str = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($omnify_key_str, 64, "\n") . "-----END RSA PRIVATE KEY-----";
		}
		return false !== openssl_pkey_get_private($omnify_key_str);
	}

	// ==================== Connection verification ====================

	/**
	 * Verify the active credentials for a payment gateway with a lightweight API call.
	 *
	 * @param string $omnify_gateway Gateway identifier.
	 * @return array{message: string, latency: int, mode: string, detail: array<string, mixed>}|WP_Error
	 */
	public function verify_connection(string $omnify_gateway): array|WP_Error {
		$omnify_settings = $this->omnify_settings->all();
		$omnify_gateway  = sanitize_key($omnify_gateway);

		switch ($omnify_gateway) {
			case 'card':
			case 'stripe':
			case 'stripe_checkout':
				return $this->verify_stripe_credentials($omnify_settings);
			case 'paypal':
			case 'paypal_checkout':
				return $this->verify_paypal_credentials($omnify_settings);
			case 'razorpay':
				return $this->verify_razorpay_credentials($omnify_settings);
			case 'paystack':
				return $this->verify_paystack_credentials($omnify_settings);
			case 'mollie':
				return $this->verify_mollie_credentials($omnify_settings);
			case 'tap':
				return $this->verify_tap_credentials($omnify_settings);
			case 'paddle':
				return $this->verify_paddle_credentials($omnify_settings);
			case 'alipay':
				return $this->verify_alipay_credentials($omnify_settings);
			case 'wechat':
				return $this->verify_wechat_credentials($omnify_settings);
			case 'sslcommerz':
				return $this->verify_sslcommerz_credentials($omnify_settings);
			case 'khalti':
				return $this->verify_khalti_credentials($omnify_settings);
			case 'esewa':
				return $this->verify_esewa_credentials($omnify_settings);
			case 'bank_transfer':
			case 'bacs':
			case 'cheque':
			case 'check':
			case 'cod':
			case 'cash_on_delivery':
			case 'custom':
				return ['message' => __('Offline payment method is configured and ready.', 'omnifywp-ecommerce'), 'latency' => 0, 'mode' => 'offline', 'detail' => []];
		}

		return new WP_Error('omnify_gateway_unknown', __('Unknown payment gateway.', 'omnifywp-ecommerce'));
	}

	private function gateway_remote(string $omnify_method, string $omnify_url, array $omnify_args): array {
		$omnify_start    = microtime(true);
		$omnify_response = wp_remote_request($omnify_url, array_merge(['method' => strtoupper($omnify_method), 'timeout' => 15], $omnify_args));

		return [
			'response' => $omnify_response,
			'latency'  => (int) round((microtime(true) - $omnify_start) * 1000),
		];
	}

	private function gateway_api_error(mixed $omnify_decoded, string $omnify_fallback): string {
		if (! is_array($omnify_decoded)) {
			return $omnify_fallback;
		}
		foreach (['message', 'detail', 'error_description', 'error', 'sub_msg', 'msg', 'failedreason'] as $omnify_key) {
			if (! empty($omnify_decoded[$omnify_key]) && is_string($omnify_decoded[$omnify_key])) {
				return $omnify_decoded[$omnify_key];
			}
		}
		return $omnify_fallback;
	}

	private function verify_bearer_endpoint(string $omnify_url, string $omnify_token, string $omnify_mode, string $omnify_success, string $omnify_failure): array|WP_Error {
		$omnify_result   = $this->gateway_remote('GET', $omnify_url, ['headers' => ['Authorization' => 'Bearer ' . $omnify_token, 'Accept' => 'application/json']]);
		$omnify_response = $omnify_result['response'];
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}

		$omnify_code    = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			return new WP_Error('omnify_gateway_verify_failed', $this->gateway_api_error($omnify_decoded, $omnify_failure));
		}

		return ['message' => $omnify_success, 'latency' => $omnify_result['latency'], 'mode' => $omnify_mode, 'detail' => []];
	}

	private function verify_stripe_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode   = 'live' === ($omnify_settings['stripe_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret = $this->stripe_secret_key($omnify_settings);
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_stripe_key_missing', __('Stripe secret key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		return $this->verify_bearer_endpoint(
			'https://api.stripe.com/v1/balance',
			$omnify_secret,
			$omnify_mode,
			__('Stripe API credentials are valid.', 'omnifywp-ecommerce'),
			__('Failed to authenticate with Stripe.', 'omnifywp-ecommerce')
		);
	}

	private function verify_paypal_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode = 'live' === ($omnify_settings['paypal_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_start = microtime(true);
		$omnify_token = $this->paypal_access_token($omnify_settings);
		$omnify_latency = (int) round((microtime(true) - $omnify_start) * 1000);
		if (is_wp_error($omnify_token)) {
			return $omnify_token;
		}

		return ['message' => __('PayPal OAuth token granted successfully.', 'omnifywp-ecommerce'), 'latency' => $omnify_latency, 'mode' => $omnify_mode, 'detail' => []];
	}

	private function verify_razorpay_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode       = 'live' === ($omnify_settings['razorpay_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_key_id     = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_id"] ?? ''));
		$omnify_key_secret = trim((string) ($omnify_settings["razorpay_{$omnify_mode}_key_secret"] ?? ''));
		if ('' === $omnify_key_id || '' === $omnify_key_secret) {
			return new WP_Error('omnify_razorpay_credentials_missing', __('Razorpay credentials are not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		$omnify_result   = $this->gateway_remote('GET', 'https://api.razorpay.com/v1/orders?count=1', [
			'headers' => ['Authorization' => 'Basic ' . base64_encode($omnify_key_id . ':' . $omnify_key_secret)],
		]);
		$omnify_response = $omnify_result['response'];
		if (is_wp_error($omnify_response)) {
			return $omnify_response;
		}
		$omnify_code    = (int) wp_remote_retrieve_response_code($omnify_response);
		$omnify_decoded = json_decode((string) wp_remote_retrieve_body($omnify_response), true);
		if ($omnify_code < 200 || $omnify_code >= 300) {
			return new WP_Error('omnify_razorpay_verify_failed', $this->gateway_api_error($omnify_decoded, __('Failed to authenticate with Razorpay.', 'omnifywp-ecommerce')));
		}

		return ['message' => __('Razorpay API credentials are valid.', 'omnifywp-ecommerce'), 'latency' => $omnify_result['latency'], 'mode' => $omnify_mode, 'detail' => []];
	}

	private function verify_paystack_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode   = 'live' === ($omnify_settings['paystack_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret = trim((string) ($omnify_settings["paystack_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_paystack_credentials_missing', __('Paystack secret key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		return $this->verify_bearer_endpoint(
			'https://api.paystack.co/transaction?perPage=1',
			$omnify_secret,
			$omnify_mode,
			__('Paystack API credentials are valid.', 'omnifywp-ecommerce'),
			__('Failed to authenticate with Paystack.', 'omnifywp-ecommerce')
		);
	}

	private function verify_mollie_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode    = 'live' === ($omnify_settings['mollie_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_api_key = trim((string) ($omnify_settings["mollie_{$omnify_mode}_api_key"] ?? ''));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_mollie_credentials_missing', __('Mollie API key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		return $this->verify_bearer_endpoint(
			'https://api.mollie.com/v2/methods?count=1',
			$omnify_api_key,
			$omnify_mode,
			__('Mollie API credentials are valid.', 'omnifywp-ecommerce'),
			__('Failed to authenticate with Mollie.', 'omnifywp-ecommerce')
		);
	}

	private function verify_tap_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode   = 'live' === ($omnify_settings['tap_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_secret = trim((string) ($omnify_settings["tap_{$omnify_mode}_secret_key"] ?? ''));
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_tap_credentials_missing', __('Tap secret key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		return $this->verify_bearer_endpoint(
			'https://api.tap.company/v2/charges?limit=1',
			$omnify_secret,
			$omnify_mode,
			__('Tap Payments API credentials are valid.', 'omnifywp-ecommerce'),
			__('Failed to authenticate with Tap Payments.', 'omnifywp-ecommerce')
		);
	}

	private function verify_paddle_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode    = 'live' === ($omnify_settings['paddle_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_api_key = trim((string) ($omnify_settings["paddle_{$omnify_mode}_api_key"] ?? ''));
		if ('' === $omnify_api_key) {
			return new WP_Error('omnify_paddle_credentials_missing', __('Paddle API key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		$omnify_base = 'live' === $omnify_mode ? 'https://api.paddle.com' : 'https://sandbox-api.paddle.com';

		return $this->verify_bearer_endpoint(
			$omnify_base . '/transactions?per_page=1',
			$omnify_api_key,
			$omnify_mode,
			__('Paddle API credentials are valid.', 'omnifywp-ecommerce'),
			__('Failed to authenticate with Paddle.', 'omnifywp-ecommerce')
		);
	}

	private function verify_alipay_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode        = 'live' === ($omnify_settings['alipay_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_app_id      = trim((string) ($omnify_settings['alipay_app_id'] ?? ''));
		$omnify_private_key = trim((string) ($omnify_settings['alipay_merchant_private_key'] ?? ''));

		if ('' === $omnify_app_id || '' === $omnify_private_key) {
			return new WP_Error('omnify_alipay_credentials_missing', __('Alipay App ID and merchant private key are required.', 'omnifywp-ecommerce'));
		}
		if (! $this->private_key_is_parsable($omnify_private_key)) {
			return new WP_Error('omnify_alipay_key_invalid', __('The Alipay merchant private key could not be parsed. Paste the RSA2 private key (PKCS#1 or PKCS#8).', 'omnifywp-ecommerce'));
		}

		return ['message' => __('Alipay configuration is complete and the private key is valid.', 'omnifywp-ecommerce'), 'latency' => 0, 'mode' => $omnify_mode, 'detail' => []];
	}

	private function verify_wechat_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode        = 'live' === ($omnify_settings['wechat_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		$omnify_mch_id      = trim((string) ($omnify_settings['wechat_mch_id'] ?? ($omnify_settings['wechat_mchid'] ?? '')));
		$omnify_api_key     = trim((string) ($omnify_settings['wechat_api_v3_key'] ?? ($omnify_settings['wechat_key'] ?? '')));
		$omnify_serial      = trim((string) ($omnify_settings['wechat_serial_no'] ?? ''));
		$omnify_private_key = trim((string) ($omnify_settings['wechat_merchant_private_key'] ?? ''));

		if ('' === $omnify_mch_id || '' === $omnify_api_key) {
			return new WP_Error('omnify_wechat_credentials_missing', __('WeChat Pay Merchant ID and API key are required.', 'omnifywp-ecommerce'));
		}
		if ('' === $omnify_serial || '' === $omnify_private_key) {
			return new WP_Error('omnify_wechat_cert_missing', __('WeChat Pay certificate serial number and merchant private key are required to sign v3 requests.', 'omnifywp-ecommerce'));
		}
		if (! $this->private_key_is_parsable($omnify_private_key)) {
			return new WP_Error('omnify_wechat_key_invalid', __('The WeChat Pay merchant private key could not be parsed.', 'omnifywp-ecommerce'));
		}

		$omnify_has_mtls = '' !== trim((string) ($omnify_settings['wechat_client_cert'] ?? '')) && '' !== trim((string) ($omnify_settings['wechat_client_key'] ?? ''));

		return [
			'message' => $omnify_has_mtls
				? __('WeChat Pay configuration is complete (mTLS certificate attached).', 'omnifywp-ecommerce')
				: __('WeChat Pay signing keys are valid. Add the client certificate and key to enable live API calls.', 'omnifywp-ecommerce'),
			'latency' => 0,
			'mode'    => $omnify_mode,
			'detail'  => [],
		];
	}

	private function verify_sslcommerz_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode = 'live' === ($omnify_settings['sslcommerz_mode'] ?? 'sandbox') ? 'live' : 'sandbox';
		if ('live' === $omnify_mode) {
			$omnify_store_id = trim((string) ($omnify_settings['sslcommerz_store_id'] ?? ($omnify_settings['sslcommerz_test_store_id'] ?? '')));
			$omnify_passwd   = trim((string) ($omnify_settings['sslcommerz_store_passwd'] ?? ($omnify_settings['sslcommerz_store_password'] ?? ($omnify_settings['sslcommerz_test_store_passwd'] ?? ($omnify_settings['sslcommerz_test_store_password'] ?? '')))));
		} else {
			$omnify_store_id = trim((string) ($omnify_settings['sslcommerz_test_store_id'] ?? ($omnify_settings['sslcommerz_store_id'] ?? '')));
			$omnify_passwd   = trim((string) ($omnify_settings['sslcommerz_test_store_passwd'] ?? ($omnify_settings['sslcommerz_test_store_password'] ?? ($omnify_settings['sslcommerz_store_passwd'] ?? ($omnify_settings['sslcommerz_store_password'] ?? '')))));
		}

		if ('' === $omnify_store_id || '' === $omnify_passwd) {
			return new WP_Error('omnify_sslcommerz_credentials_missing', __('SSLCommerz Store ID and password are required for the active mode.', 'omnifywp-ecommerce'));
		}

		return ['message' => __('SSLCommerz credentials are configured for the active mode.', 'omnifywp-ecommerce'), 'latency' => 0, 'mode' => $omnify_mode, 'detail' => []];
	}

	private function verify_khalti_credentials(array $omnify_settings): array|WP_Error {
		$omnify_is_live = 'live' === ($omnify_settings['khalti_mode'] ?? '');
		$omnify_secret  = trim((string) ($omnify_is_live ? ($omnify_settings['khalti_live_secret_key'] ?? '') : ($omnify_settings['khalti_test_secret_key'] ?? '')));
		if ('' === $omnify_secret) {
			return new WP_Error('omnify_khalti_credentials_missing', __('Khalti secret key is not configured for the active mode.', 'omnifywp-ecommerce'));
		}

		return ['message' => __('Khalti secret key is configured.', 'omnifywp-ecommerce'), 'latency' => 0, 'mode' => ($omnify_is_live ? 'live' : 'test'), 'detail' => []];
	}

	private function verify_esewa_credentials(array $omnify_settings): array|WP_Error {
		$omnify_mode         = 'live' === ($omnify_settings['esewa_mode'] ?? 'test') ? 'live' : 'test';
		$omnify_product_code = trim((string) ($omnify_settings["esewa_{$omnify_mode}_product_code"] ?? ($omnify_mode === 'test' ? 'EPAYTEST' : '')));
		$omnify_secret_key   = trim((string) ($omnify_settings["esewa_{$omnify_mode}_secret_key"] ?? ($omnify_mode === 'test' ? '8gBm/:&EnhH.1/q' : '')));

		if ('' === $omnify_product_code || '' === $omnify_secret_key) {
			return new WP_Error('omnify_esewa_credentials_missing', __('eSewa product code and secret key are required for the active mode.', 'omnifywp-ecommerce'));
		}

		return ['message' => __('eSewa credentials are configured for the active mode.', 'omnifywp-ecommerce'), 'latency' => 0, 'mode' => $omnify_mode, 'detail' => []];
	}
}
