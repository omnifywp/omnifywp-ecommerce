<?php
/**
 * Database access helpers.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_DB {
	private const CACHE_GROUP = 'omnify_db';
	private const CACHE_TTL   = MINUTE_IN_SECONDS;

	public static function get_results( \wpdb $omnify_wpdb, string $omnify_query, string $omnify_output = OBJECT ): array|object|null {
		return self::cached_read( 'get_results', $omnify_wpdb, [ $omnify_query, $omnify_output ] );
	}

	public static function get_row( \wpdb $omnify_wpdb, string $omnify_query, string $omnify_output = OBJECT, int $omnify_y = 0 ): array|object|null {
		return self::cached_read( 'get_row', $omnify_wpdb, [ $omnify_query, $omnify_output, $omnify_y ] );
	}

	public static function get_col( \wpdb $omnify_wpdb, string $omnify_query, int $omnify_x = 0 ): array {
		$omnify_rows = self::cached_read( 'get_col', $omnify_wpdb, [ $omnify_query, $omnify_x ] );
		return is_array( $omnify_rows ) ? $omnify_rows : [];
	}

	public static function get_var( \wpdb $omnify_wpdb, string $omnify_query, int $omnify_x = 0, int $omnify_y = 0 ): mixed {
		return self::cached_read( 'get_var', $omnify_wpdb, [ $omnify_query, $omnify_x, $omnify_y ] );
	}

	public static function query( \wpdb $omnify_wpdb, string $omnify_query ): int|bool {
		$omnify_result = call_user_func( [ $omnify_wpdb, 'query' ], $omnify_query );

		// Do not invalidate cache for transaction boundary operations (START TRANSACTION, COMMIT, ROLLBACK)
		$omnify_trimmed = strtoupper( trim( $omnify_query ) );
		if ( ! preg_match( '/^(START\s+TRANSACTION|COMMIT|ROLLBACK|BEGIN|SAVEPOINT)/i', $omnify_trimmed ) ) {
			self::bump_last_changed();
		}

		return $omnify_result;
	}

	public static function insert( \wpdb $omnify_wpdb, string $omnify_table, array $omnify_data, array|string|null $omnify_format = null ): int|false {
		$omnify_result = call_user_func( [ $omnify_wpdb, 'insert' ], $omnify_table, $omnify_data, $omnify_format );
		self::bump_last_changed();
		return $omnify_result;
	}

	public static function replace( \wpdb $omnify_wpdb, string $omnify_table, array $omnify_data, array|string|null $omnify_format = null ): int|false {
		$omnify_result = call_user_func( [ $omnify_wpdb, 'replace' ], $omnify_table, $omnify_data, $omnify_format );
		self::bump_last_changed();
		return $omnify_result;
	}

	public static function update( \wpdb $omnify_wpdb, string $omnify_table, array $omnify_data, array $omnify_where, array|string|null $omnify_format = null, array|string|null $omnify_where_format = null ): int|false {
		$omnify_result = call_user_func( [ $omnify_wpdb, 'update' ], $omnify_table, $omnify_data, $omnify_where, $omnify_format, $omnify_where_format );
		self::bump_last_changed();
		return $omnify_result;
	}

	public static function delete( \wpdb $omnify_wpdb, string $omnify_table, array $omnify_where, array|string|null $omnify_where_format = null ): int|false {
		$omnify_result = call_user_func( [ $omnify_wpdb, 'delete' ], $omnify_table, $omnify_where, $omnify_where_format );
		self::bump_last_changed();
		return $omnify_result;
	}

	private static function cached_read( string $omnify_method, \wpdb $omnify_wpdb, array $omnify_args ): mixed {
		$omnify_query = isset( $omnify_args[0] ) && is_string( $omnify_args[0] ) ? $omnify_args[0] : '';

		// Automatically bypass cache for pessimistic locking queries so DB handles row locks
		if ( '' !== $omnify_query && preg_match( '/\b(FOR\s+UPDATE|LOCK\s+IN\s+SHARE\s+MODE)\b/i', $omnify_query ) ) {
			return call_user_func_array( [ $omnify_wpdb, $omnify_method ], $omnify_args );
		}

		$omnify_cache_key = self::cache_key( $omnify_method, $omnify_args );
		$omnify_cached    = wp_cache_get( $omnify_cache_key, self::CACHE_GROUP );

		if ( false !== $omnify_cached ) {
			return $omnify_cached;
		}

		$omnify_result = call_user_func_array( [ $omnify_wpdb, $omnify_method ], $omnify_args );
		wp_cache_set( $omnify_cache_key, $omnify_result, self::CACHE_GROUP, self::CACHE_TTL );

		return $omnify_result;
	}

	private static function cache_key( string $omnify_method, array $omnify_args ): string {
		return $omnify_method . '_' . md5( self::last_changed() . '|' . wp_json_encode( $omnify_args ) );
	}

	private static function last_changed(): string {
		$omnify_last_changed = wp_cache_get( 'last_changed', self::CACHE_GROUP );
		if ( false === $omnify_last_changed ) {
			$omnify_last_changed = microtime();
			wp_cache_set( 'last_changed', $omnify_last_changed, self::CACHE_GROUP );
		}

		return (string) $omnify_last_changed;
	}

	private static function bump_last_changed(): void {
		wp_cache_set( 'last_changed', microtime(), self::CACHE_GROUP );
	}
}
