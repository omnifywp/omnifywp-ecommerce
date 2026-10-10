<?php
/**
 * PSR-4 autoloader.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Autoloader {
	private const PREFIX = 'Omnify\\eCommerce\\';

	public static function register(): void {
		spl_autoload_register([self::class, 'load']);
	}

	public static function load(string $omnify_class): void {
		if (! str_starts_with($omnify_class, self::PREFIX)) {
			return;
		}

		$omnify_relative = substr($omnify_class, strlen(self::PREFIX));
		$omnify_file     = OMNIFY_PATH . 'includes/' . str_replace('\\', '/', $omnify_relative) . '.php';

		if (is_readable($omnify_file)) {
			require_once $omnify_file;
			return;
		}

		$omnify_parts    = explode('\\', $omnify_relative);
		$omnify_basename = array_pop($omnify_parts);
		if (str_starts_with($omnify_basename, 'Omnify_')) {
			$omnify_parts[] = substr($omnify_basename, strlen('Omnify_'));
			$omnify_file    = OMNIFY_PATH . 'includes/' . str_replace('\\', '/', implode('\\', $omnify_parts)) . '.php';

			if (is_readable($omnify_file)) {
				require_once $omnify_file;
			}
		}
	}
}
