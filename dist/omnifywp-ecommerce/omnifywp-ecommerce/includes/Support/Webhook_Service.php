<?php
/**
 * Outbound webhook dispatcher service.
 *
 * Dispatches eCommerce events (orders, customers, status changes) to external HTTP endpoints.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Omnify\eCommerce\Database\Omnify_Schema;
use Omnify\eCommerce\Repositories\Omnify_Order_Repository;
use Omnify\eCommerce\Repositories\Omnify_Customer_Repository;

class Omnify_Webhook_Service {
	public function __construct(
		private Omnify_Schema $omnify_schema,
		private Omnify_Order_Repository $omnify_orders,
		private Omnify_Customer_Repository $omnify_customers
	) {}

	private const PRUNE_CRON_HOOK = 'omnify_webhook_logs_prune';
	private const RETRY_CRON_HOOK = 'omnify_webhook_retry_queue';

	public function register(): void {
		add_action('omnify_order_created', [$this, 'on_order_created'], 20, 2);
		add_action('omnify_order_status_changed', [$this, 'on_order_status_changed'], 20, 3);
		add_action('omnify_order_completed', [$this, 'on_order_completed'], 20, 1);
		add_action('omnify_order_refunded', [$this, 'on_order_refunded'], 20, 3);
		add_action('omnify_customer_created', [$this, 'on_customer_created'], 20, 2);
		add_action(self::PRUNE_CRON_HOOK, [$this, 'prune_logs']);
		add_action(self::RETRY_CRON_HOOK, [$this, 'process_retry_queue']);
		$this->schedule_prune();
		$this->schedule_retry_queue();
	}

	public function schedule_prune(): void {
		if (false !== get_transient('omnify_webhook_prune_checked')) {
			return;
		}

		if (! wp_next_scheduled(self::PRUNE_CRON_HOOK)) {
			wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', self::PRUNE_CRON_HOOK);
		}

		set_transient('omnify_webhook_prune_checked', 1, DAY_IN_SECONDS);
	}

	public function on_order_created(int $omnify_order_id, array $omnify_data): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		$this->dispatch('order.created', [
			'event'     => 'order.created',
			'timestamp' => current_time('mysql', true),
			'order'     => $omnify_order ?: array_merge(['id' => $omnify_order_id], $omnify_data),
		]);
	}

	public function on_order_status_changed(int $omnify_order_id, string $omnify_new_status, string $omnify_old_status): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		$omnify_payload = [
			'event'        => 'order.status_changed',
			'timestamp'    => current_time('mysql', true),
			'order_id'     => $omnify_order_id,
			'new_status'   => $omnify_new_status,
			'old_status'   => $omnify_old_status,
			'order_number' => $omnify_order['order_number'] ?? ('#' . $omnify_order_id),
			'order'        => $omnify_order,
		];
		$this->dispatch('order.status_changed', $omnify_payload);

		if ('cancelled' === $omnify_new_status) {
			$omnify_cancel_payload          = $omnify_payload;
			$omnify_cancel_payload['event'] = 'order.cancelled';
			$this->dispatch('order.cancelled', $omnify_cancel_payload);
		}
	}

	public function on_order_completed(int $omnify_order_id): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		$this->dispatch('order.completed', [
			'event'        => 'order.completed',
			'timestamp'    => current_time('mysql', true),
			'order_id'     => $omnify_order_id,
			'order_number' => $omnify_order['order_number'] ?? ('#' . $omnify_order_id),
			'order'        => $omnify_order,
		]);
	}

	public function on_order_refunded(int $omnify_order_id, float $omnify_refund_amount, float $omnify_total_refunded): void {
		$omnify_order = $this->omnify_orders->find($omnify_order_id);
		$this->dispatch('order.refunded', [
			'event'          => 'order.refunded',
			'timestamp'      => current_time('mysql', true),
			'order_id'       => $omnify_order_id,
			'refund_amount'  => $omnify_refund_amount,
			'total_refunded' => $omnify_total_refunded,
			'order_number'   => $omnify_order['order_number'] ?? ('#' . $omnify_order_id),
			'order'          => $omnify_order,
		]);
	}

	public function on_customer_created(int $omnify_customer_id, array $omnify_data): void {
		$omnify_customer = $this->omnify_customers->find($omnify_customer_id);
		$this->dispatch('customer.created', [
			'event'       => 'customer.created',
			'timestamp'   => current_time('mysql', true),
			'customer_id' => $omnify_customer_id,
			'customer'    => $omnify_customer ?: array_merge(['id' => $omnify_customer_id], $omnify_data),
		]);
	}

	/**
	 * Dispatch event to all registered and active webhooks subscribed to it.
	 */
	public function dispatch(string $omnify_event, array $omnify_payload): void {
		global $wpdb;

		$omnify_table = $this->omnify_schema->table('webhooks');
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$omnify_webhooks = Omnify_DB::get_results($wpdb, "SELECT * FROM {$omnify_table} WHERE status = 'active'", ARRAY_A);
		if (empty($omnify_webhooks) || ! is_array($omnify_webhooks)) {
			return;
		}

		foreach ($omnify_webhooks as $omnify_hook) {
			$omnify_subscribed_events = array_map('trim', explode(',', (string) ($omnify_hook['events'] ?? '')));
			if (! in_array('*', $omnify_subscribed_events, true) && ! in_array($omnify_event, $omnify_subscribed_events, true)) {
				continue;
			}

			$omnify_url = (string) ($omnify_hook['url'] ?? '');
			if ('' === $omnify_url || ! wp_http_validate_url($omnify_url)) {
				continue;
			}

			$omnify_hook_payload = apply_filters('omnify_webhook_payload', $omnify_payload, $omnify_event, $omnify_hook);
			$omnify_hook_json    = wp_json_encode($omnify_hook_payload);
			if (false === $omnify_hook_json) {
				continue;
			}

			$omnify_secret    = (string) ($omnify_hook['secret'] ?? '');
			$omnify_signature = hash_hmac('sha256', $omnify_hook_json, $omnify_secret);

			$omnify_blocking = (bool) apply_filters('omnify_webhook_blocking_dispatch', true, $omnify_event, $omnify_hook);
			$omnify_timeout  = (int) apply_filters('omnify_webhook_dispatch_timeout', 3, $omnify_event, $omnify_hook);

			$omnify_request_args = apply_filters('omnify_webhook_request_args', [
				'blocking' => $omnify_blocking,
				'timeout'  => max(1, min(10, $omnify_timeout)),
				'headers'  => [
					'Content-Type'       => 'application/json; charset=utf-8',
					'X-Omnify-Event'     => $omnify_event,
					'X-Omnify-Signature' => $omnify_signature,
					'User-Agent'         => 'OmnifyWP-Webhooks/' . OMNIFY_VERSION,
				],
				'body'     => $omnify_hook_json,
			], $omnify_event, $omnify_hook);

			$omnify_start_time = microtime(true);
			$omnify_response   = wp_safe_remote_post($omnify_url, $omnify_request_args);
			$omnify_duration   = round(microtime(true) - $omnify_start_time, 3);

			$omnify_logs_table = $this->omnify_schema->table('webhook_delivery_logs');
			if (is_wp_error($omnify_response)) {
				$omnify_code = 0;
				$omnify_body = $omnify_response->get_error_message();
			} elseif (! empty($omnify_request_args['blocking'])) {
				$omnify_code = (int) wp_remote_retrieve_response_code($omnify_response);
				$omnify_body = wp_remote_retrieve_body($omnify_response);
				if (strlen($omnify_body) > 2000) {
					$omnify_body = substr($omnify_body, 0, 2000) . '... [truncated]';
				}
			} else {
				$omnify_code = 202;
				$omnify_body = 'Dispatched asynchronously';
			}

			Omnify_DB::insert($wpdb, $omnify_logs_table, [
				'webhook_id'      => (int) ($omnify_hook['id'] ?? 0),
				'event'           => $omnify_event,
				'request_payload' => $omnify_hook_json,
				'response_code'   => $omnify_code,
				'response_body'   => $omnify_body,
				'duration'        => $omnify_duration,
				'created_at'      => current_time('mysql', true),
			]);
		}
	}

	/**
	 * Test a webhook endpoint with a ping payload.
	 *
	 * @return array<string, mixed>
	 */
	public function test_webhook(int $omnify_webhook_id): array {
		global $wpdb;

		$omnify_table = $this->omnify_schema->table('webhooks');
		$omnify_hook  = Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$omnify_table} WHERE id = %d", $omnify_webhook_id), ARRAY_A);
		if (! $omnify_hook) {
			return ['success' => false, 'message' => __('Webhook not found.', 'omnifywp-ecommerce')];
		}

		$omnify_url = (string) ($omnify_hook['url'] ?? '');
		if ('' === $omnify_url || ! wp_http_validate_url($omnify_url)) {
			return ['success' => false, 'message' => __('Invalid webhook URL.', 'omnifywp-ecommerce')];
		}

		$omnify_payload = [
			'event'     => 'ping',
			'timestamp' => current_time('mysql', true),
			'message'   => __('OmnifyWP eCommerce Webhook Test Ping', 'omnifywp-ecommerce'),
			'site_url'  => home_url('/'),
		];
		$omnify_json = (string) wp_json_encode($omnify_payload);
		$omnify_secret = (string) ($omnify_hook['secret'] ?? '');
		$omnify_signature = hash_hmac('sha256', $omnify_json, $omnify_secret);

		$omnify_start = microtime(true);
		$omnify_response = wp_safe_remote_post($omnify_url, [
			'timeout' => 15,
			'headers' => [
				'Content-Type'       => 'application/json; charset=utf-8',
				'X-Omnify-Event'     => 'ping',
				'X-Omnify-Signature' => $omnify_signature,
				'User-Agent'         => 'OmnifyWP-Webhooks/' . OMNIFY_VERSION,
			],
			'body'    => $omnify_json,
		]);
		$omnify_duration = round(microtime(true) - $omnify_start, 3);

		$omnify_code = is_wp_error($omnify_response) ? 0 : wp_remote_retrieve_response_code($omnify_response);
		$omnify_body = is_wp_error($omnify_response) ? $omnify_response->get_error_message() : substr(wp_remote_retrieve_body($omnify_response), 0, 1000);

		// Log delivery attempt
		$omnify_logs_table = $this->omnify_schema->table('webhook_delivery_logs');
		Omnify_DB::insert($wpdb, $omnify_logs_table, [
			'webhook_id'      => $omnify_webhook_id,
			'event'           => 'ping',
			'request_payload' => $omnify_json,
			'response_code'   => $omnify_code,
			'response_body'   => $omnify_body,
			'duration'        => $omnify_duration,
			'created_at'      => current_time('mysql', true),
		]);

		$omnify_is_success = $omnify_code >= 200 && $omnify_code < 300;

		return [
			'success'       => $omnify_is_success,
			'response_code' => $omnify_code,
			'duration'      => $omnify_duration,
			'message'       => $omnify_is_success ? sprintf(__('Ping delivered successfully (HTTP %d, %.3fs).', 'omnifywp-ecommerce'), $omnify_code, $omnify_duration) : sprintf(__('Ping failed (HTTP %d): %s', 'omnifywp-ecommerce'), $omnify_code, $omnify_body),
		];
	}

	/**
	 * Retrieve all registered webhooks.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all(): array {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhooks');
		$omnify_rows = Omnify_DB::get_results($wpdb, "SELECT * FROM {$omnify_table} ORDER BY id DESC", ARRAY_A);
		return is_array($omnify_rows) ? $omnify_rows : [];
	}

	/**
	 * Find a webhook by ID.
	 *
	 * @return array<string, mixed>|null
	 */
	public function find(int $omnify_id): ?array {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhooks');
		$omnify_row = Omnify_DB::get_row($wpdb, $wpdb->prepare("SELECT * FROM {$omnify_table} WHERE id = %d", $omnify_id), ARRAY_A);
		return is_array($omnify_row) ? $omnify_row : null;
	}

	/**
	 * Create a new webhook.
	 *
	 * @param array<string, mixed> $omnify_data
	 */
	public function create(array $omnify_data): int {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhooks');
		$omnify_now = current_time('mysql', true);
		$omnify_inserted = Omnify_DB::insert($wpdb, $omnify_table, [
			'name'       => sanitize_text_field($omnify_data['name'] ?? 'Webhook'),
			'url'        => esc_url_raw($omnify_data['url'] ?? ''),
			'secret'     => sanitize_text_field($omnify_data['secret'] ?? wp_generate_password(32, false)),
			'events'     => sanitize_text_field($omnify_data['events'] ?? '*'),
			'status'     => in_array($omnify_data['status'] ?? 'active', ['active', 'paused'], true) ? $omnify_data['status'] : 'active',
			'created_at' => $omnify_now,
			'updated_at' => $omnify_now,
		]);
		return $omnify_inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Delete a webhook by ID.
	 */
	public function delete(int $omnify_id): bool {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhooks');
		$omnify_deleted = Omnify_DB::delete($wpdb, $omnify_table, ['id' => $omnify_id]);
		// Also clean up associated logs
		$omnify_logs_table = $this->omnify_schema->table('webhook_delivery_logs');
		Omnify_DB::delete($wpdb, $omnify_logs_table, ['webhook_id' => $omnify_id]);
		return false !== $omnify_deleted;
	}

	/**
	 * Get recent delivery logs for a webhook.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_delivery_logs(int $omnify_webhook_id, int $omnify_limit = 20): array {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhook_delivery_logs');
		$omnify_rows = Omnify_DB::get_results($wpdb, $wpdb->prepare(
			"SELECT * FROM {$omnify_table} WHERE webhook_id = %d ORDER BY id DESC LIMIT %d",
			$omnify_webhook_id,
			$omnify_limit
		), ARRAY_A);
		return is_array($omnify_rows) ? $omnify_rows : [];
	}

	/**
	 * Prune delivery logs older than specified number of days (default 30 days).
	 *
	 * @param int $omnify_days Number of days to retain logs.
	 * @return int Number of deleted log rows.
	 */
	public function prune_logs(int $omnify_days = 30): int {
		global $wpdb;

		$omnify_table = $this->omnify_schema->table('webhook_delivery_logs');
		$omnify_days = max(1, $omnify_days);
		$omnify_cutoff = gmdate('Y-m-d H:i:s', time() - ($omnify_days * DAY_IN_SECONDS));

		$omnify_deleted = Omnify_DB::query(
			$wpdb,
			$wpdb->prepare("DELETE FROM {$omnify_table} WHERE created_at < %s", $omnify_cutoff)
		);

		return false !== $omnify_deleted ? (int) $omnify_deleted : 0;
	}

	public function schedule_retry_queue(): void {
		if (false !== get_transient('omnify_webhook_retry_cron_checked')) {
			return;
		}

		if (! wp_next_scheduled(self::RETRY_CRON_HOOK)) {
			wp_schedule_event(time() + (10 * MINUTE_IN_SECONDS), 'hourly', self::RETRY_CRON_HOOK);
		}

		set_transient('omnify_webhook_retry_cron_checked', 1, DAY_IN_SECONDS);
	}

	public function process_retry_queue(): int {
		global $wpdb;

		$omnify_logs_table = $this->omnify_schema->table('webhook_delivery_logs');
		$omnify_webhooks_table = $this->omnify_schema->table('webhooks');
		$omnify_cutoff = gmdate('Y-m-d H:i:s', time() - DAY_IN_SECONDS);

		$omnify_failed_logs = Omnify_DB::get_results(
			$wpdb,
			$wpdb->prepare(
				"SELECT l.*, w.url, w.secret 
				FROM {$omnify_logs_table} l 
				JOIN {$omnify_webhooks_table} w ON l.webhook_id = w.id 
				WHERE (l.response_code = 0 OR (l.response_code >= 400 AND l.response_code != -1)) 
				AND (l.response_body IS NULL OR l.response_body NOT LIKE '%%[Auto-Retry #3%%')
				AND w.status = 'active' 
				AND l.created_at >= %s 
				ORDER BY l.id ASC 
				LIMIT 20",
				$omnify_cutoff
			),
			ARRAY_A
		);

		if (empty($omnify_failed_logs) || ! is_array($omnify_failed_logs)) {
			return 0;
		}

		$omnify_retried_count = 0;
		foreach ($omnify_failed_logs as $omnify_log) {
			$omnify_url = (string) ($omnify_log['url'] ?? '');
			if (! wp_http_validate_url($omnify_url)) {
				continue;
			}

			// Extract current retry attempt count from response_body
			$omnify_current_body = (string) ($omnify_log['response_body'] ?? '');
			$omnify_retry_attempt = 1;
			if (preg_match('/\[Auto-Retry #(\d+)/', $omnify_current_body, $omnify_matches)) {
				$omnify_retry_attempt = ((int) $omnify_matches[1]) + 1;
			}

			if ($omnify_retry_attempt > 3) {
				// Mark as permanently failed to avoid blocking the queue
				Omnify_DB::update(
					$wpdb,
					$omnify_logs_table,
					[
						'response_code' => -1,
						'response_body' => sprintf('[Auto-Retry #3 Failed Permanently %s] Max retry attempts exceeded. %s', current_time('mysql'), $omnify_current_body),
					],
					['id' => (int) $omnify_log['id']],
					['%d', '%s'],
					['%d']
				);
				continue;
			}

			$omnify_payload_json = (string) ($omnify_log['request_payload'] ?? '');
			$omnify_secret       = (string) ($omnify_log['secret'] ?? '');
			$omnify_signature    = hash_hmac('sha256', $omnify_payload_json, $omnify_secret);
			$omnify_event        = (string) ($omnify_log['event'] ?? 'retry');

			$omnify_start    = microtime(true);
			$omnify_response = wp_safe_remote_post($omnify_url, [
				'timeout' => 10,
				'headers' => [
					'Content-Type'       => 'application/json; charset=utf-8',
					'X-Omnify-Event'     => $omnify_event,
					'X-Omnify-Signature' => $omnify_signature,
					'X-Omnify-Retry'     => (string) $omnify_retry_attempt,
					'User-Agent'         => 'OmnifyWP-Webhooks/' . OMNIFY_VERSION,
				],
				'body'    => $omnify_payload_json,
			]);
			$omnify_duration = round(microtime(true) - $omnify_start, 3);

			$omnify_code = is_wp_error($omnify_response) ? 0 : wp_remote_retrieve_response_code($omnify_response);
			$omnify_body = is_wp_error($omnify_response) ? $omnify_response->get_error_message() : substr(wp_remote_retrieve_body($omnify_response), 0, 1000);

			$omnify_is_success = $omnify_code >= 200 && $omnify_code < 300;
			$omnify_formatted_body = $omnify_is_success
				? sprintf('[Auto-Retry #%d Succeeded %s] %s', $omnify_retry_attempt, current_time('mysql'), $omnify_body)
				: ($omnify_retry_attempt >= 3
					? sprintf('[Auto-Retry #3 Failed Permanently %s] %s', current_time('mysql'), $omnify_body)
					: sprintf('[Auto-Retry #%d %s] %s', $omnify_retry_attempt, current_time('mysql'), $omnify_body));

			Omnify_DB::update(
				$wpdb,
				$omnify_logs_table,
				[
					'response_code' => (! $omnify_is_success && $omnify_retry_attempt >= 3) ? -1 : $omnify_code,
					'response_body' => $omnify_formatted_body,
					'duration'      => $omnify_duration,
				],
				['id' => (int) $omnify_log['id']],
				['%d', '%s', '%f'],
				['%d']
			);

			$omnify_retried_count++;
		}

		return $omnify_retried_count;
	}

	public function unschedule_prune(): void {
		delete_transient('omnify_webhook_prune_checked');
		delete_transient('omnify_webhook_retry_cron_checked');
		$omnify_timestamp = wp_next_scheduled(self::PRUNE_CRON_HOOK);
		if ($omnify_timestamp) {
			wp_unschedule_event($omnify_timestamp, self::PRUNE_CRON_HOOK);
		}
		$omnify_retry_timestamp = wp_next_scheduled(self::RETRY_CRON_HOOK);
		if ($omnify_retry_timestamp) {
			wp_unschedule_event($omnify_retry_timestamp, self::RETRY_CRON_HOOK);
		}
	}
}
