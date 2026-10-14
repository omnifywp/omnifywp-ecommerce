<?php
/**
 * Admin activity log persistence.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Omnify\eCommerce\Database\Omnify_Schema;

class Omnify_Admin_Activity_Repository {
	public function __construct(private Omnify_Schema $omnify_schema) {}

	/**
	 * Log an admin activity.
	 */
	public function log(int $omnify_user_id, string $omnify_action, string $omnify_object_type, ?string $omnify_object_id, string $omnify_description): int {
		global $wpdb;

		$omnify_ip = '';
		if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (! empty($_SERVER['REMOTE_ADDR'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$omnify_ip = $_SERVER['REMOTE_ADDR'];
		}

		if (strpos($omnify_ip, ',') !== false) {
			$omnify_parts = explode(',', $omnify_ip);
			$omnify_ip = trim($omnify_parts[0]);
		}
		$omnify_ip_address = sanitize_text_field((string) $omnify_ip);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$omnify_user_agent = sanitize_text_field((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

		\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
			$this->omnify_schema->table('admin_activities'),
			[
				'user_id'     => $omnify_user_id,
				'action'      => sanitize_key($omnify_action),
				'object_type' => sanitize_key($omnify_object_type),
				'object_id'   => null !== $omnify_object_id ? sanitize_text_field($omnify_object_id) : null,
				'description' => sanitize_textarea_field($omnify_description),
				'ip_address'  => $omnify_ip_address,
				'user_agent'  => $omnify_user_agent,
				'created_at'  => current_time('mysql', true),
			]
		);

		$omnify_inserted_id = (int) $wpdb->insert_id;
		do_action('omnify_admin_activity_logged', $omnify_inserted_id, $omnify_user_id, $omnify_action, $omnify_object_type, $omnify_object_id, $omnify_description);
		return $omnify_inserted_id;
	}

	/**
	 * Get all activities with actor details.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all(array $omnify_args = []): array {
		global $wpdb;

		$omnify_limit  = min(100, max(1, absint($omnify_args['per_page'] ?? 20)));
		$omnify_offset = max(0, absint($omnify_args['offset'] ?? 0));

		$omnify_table = $this->omnify_schema->table('admin_activities');
		$omnify_users = $wpdb->users;

		$omnify_where = '1=1';
		$omnify_params = [];

		if (! empty($omnify_args['user_id'])) {
			$omnify_where .= ' AND a.user_id = %d';
			$omnify_params[] = absint($omnify_args['user_id']);
		}

		if (! empty($omnify_args['action'])) {
			$omnify_where .= ' AND a.action = %s';
			$omnify_params[] = sanitize_key($omnify_args['action']);
		}

		if (! empty($omnify_args['object_type'])) {
			$omnify_where .= ' AND a.object_type = %s';
			$omnify_params[] = sanitize_key($omnify_args['object_type']);
		}

		if (! empty($omnify_args['search'])) {
			$omnify_like = '%' . $wpdb->esc_like(sanitize_text_field($omnify_args['search'])) . '%';
			$omnify_where .= ' AND (a.description LIKE %s OR u.user_login LIKE %s OR u.display_name LIKE %s)';
			$omnify_params[] = $omnify_like;
			$omnify_params[] = $omnify_like;
			$omnify_params[] = $omnify_like;
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND a.created_at >= %s';
			$omnify_params[] = sanitize_text_field($omnify_args['date_from']) . ' 00:00:00';
		}

		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND a.created_at <= %s';
			$omnify_params[] = sanitize_text_field($omnify_args['date_to']) . ' 23:59:59';
		}

		$omnify_sql = "
			SELECT 
				a.*,
				u.user_login as actor_username,
				u.display_name as actor_name,
				u.user_email as actor_email
			FROM {$omnify_table} a
			LEFT JOIN {$omnify_users} u ON a.user_id = u.ID
			WHERE {$omnify_where}
			ORDER BY a.created_at DESC
			LIMIT %d OFFSET %d
		";

		$omnify_params[] = $omnify_limit;
		$omnify_params[] = $omnify_offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Activity log must reflect recent writes immediately in the admin audit screen.
		$omnify_rows = $wpdb->get_results($wpdb->prepare($omnify_sql, ...$omnify_params), ARRAY_A);

		if (! is_array($omnify_rows)) {
			return [];
		}

		return array_map([$this, 'hydrate'], $omnify_rows);
	}

	/**
	 * Count matching activities.
	 */
	public function count(array $omnify_args = []): int {
		global $wpdb;

		$omnify_table = $this->omnify_schema->table('admin_activities');
		$omnify_users = $wpdb->users;

		$omnify_where = '1=1';
		$omnify_params = [];

		if (! empty($omnify_args['user_id'])) {
			$omnify_where .= ' AND a.user_id = %d';
			$omnify_params[] = absint($omnify_args['user_id']);
		}

		if (! empty($omnify_args['action'])) {
			$omnify_where .= ' AND a.action = %s';
			$omnify_params[] = sanitize_key($omnify_args['action']);
		}

		if (! empty($omnify_args['object_type'])) {
			$omnify_where .= ' AND a.object_type = %s';
			$omnify_params[] = sanitize_key($omnify_args['object_type']);
		}

		if (! empty($omnify_args['search'])) {
			$omnify_like = '%' . $wpdb->esc_like(sanitize_text_field($omnify_args['search'])) . '%';
			$omnify_where .= ' AND (a.description LIKE %s OR u.user_login LIKE %s OR u.display_name LIKE %s)';
			$omnify_params[] = $omnify_like;
			$omnify_params[] = $omnify_like;
			$omnify_params[] = $omnify_like;
		}

		if (! empty($omnify_args['date_from'])) {
			$omnify_where .= ' AND a.created_at >= %s';
			$omnify_params[] = sanitize_text_field($omnify_args['date_from']) . ' 00:00:00';
		}

		if (! empty($omnify_args['date_to'])) {
			$omnify_where .= ' AND a.created_at <= %s';
			$omnify_params[] = sanitize_text_field($omnify_args['date_to']) . ' 23:59:59';
		}

		$omnify_sql = "
			SELECT COUNT(*)
			FROM {$omnify_table} a
			LEFT JOIN {$omnify_users} u ON a.user_id = u.ID
			WHERE {$omnify_where}
		";

		if (! empty($omnify_params)) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Activity counts must reflect recent writes immediately in the admin audit screen.
			return (int) $wpdb->get_var($wpdb->prepare($omnify_sql, ...$omnify_params));
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Activity counts must reflect recent writes immediately in the admin audit screen.
		return (int) $wpdb->get_var($omnify_sql);
	}

	/**
	 * Clear all logs.
	 */
	public function clear_all(): bool {
		global $wpdb;
		$omnify_cleared = \Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('TRUNCATE TABLE %i', $this->omnify_schema->table('admin_activities'))) !== false;
		if ($omnify_cleared) {
			do_action('omnify_admin_activities_cleared');
		}
		return $omnify_cleared;
	}

	private function hydrate(array $omnify_row): array {
		$omnify_row['id']      = (int) $omnify_row['id'];
		$omnify_row['user_id'] = (int) $omnify_row['user_id'];
		return $omnify_row;
	}
}
