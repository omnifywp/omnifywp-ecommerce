<?php
/**
 * Migration runner.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnify_Migrations {
	private const VERSION = '2026_10_08_000089_orders_lookup_indexes';

	public function __construct(private Omnify_Schema $omnify_schema) {}

	public function version(): string {
		return self::VERSION;
	}

	public function migrate(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ($this->omnify_schema->statements() as $omnify_statement) {
			dbDelta($omnify_statement);
		}

		$this->migrate_legacy_orders();
		$this->migrate_add_payment_columns();
		$this->migrate_add_physical_columns();
		$this->migrate_add_variation_columns();
		$this->migrate_drop_order_key();
		$this->migrate_add_gallery_video();
		$this->migrate_add_variation_description();
		$this->migrate_add_variation_settings();
		$this->migrate_add_product_file_expiry();
		$this->migrate_add_product_trust_badge();
		$this->migrate_add_order_delivery_columns();
		$this->migrate_reviews_table_status();
		$this->migrate_add_customer_address_columns();
		$this->migrate_add_product_max_qty();
		$this->migrate_order_items_refund_columns();
		$this->migrate_add_product_refund_policy();
		$this->migrate_extend_coupon_columns();
		$this->migrate_order_billing_and_transaction_columns();
		$this->migrate_webhook_events_table();
		$this->migrate_inventory_logs_table();
		$this->migrate_populate_missing_order_numbers();
		$this->migrate_add_customer_tax_exempt();
		$this->migrate_add_order_tax_context();
		$this->migrate_add_product_shipping_class();
		$this->migrate_add_order_note_visibility();
		$this->migrate_add_backorder_preorder_columns();
		$this->migrate_add_product_bundled_ids();
		$this->migrate_add_related_products_columns();
		$this->migrate_add_order_fraud_columns();
		$this->migrate_admin_activities_table();
		$this->migrate_add_deleted_at_columns();
		$this->migrate_add_short_description();
		$this->migrate_add_product_specifications_delivery_return();
		$this->migrate_add_product_video_poster();
		$this->migrate_add_customer_note_visibility();
		$this->migrate_add_order_items_compound_index();
		$this->migrate_add_orders_indexes();

		$this->record(self::VERSION);
		update_option('omnify_schema_version', self::VERSION);
	}

	private function table_exists(string $omnify_table): bool {
		global $wpdb;

		return \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare('SHOW TABLES LIKE %s', $omnify_table)) === $omnify_table;
	}

	private function table_columns(string $omnify_table): array {
		global $wpdb;

		$omnify_columns = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare('SHOW COLUMNS FROM %i', $omnify_table), ARRAY_A);

		return is_array($omnify_columns) ? $omnify_columns : [];
	}

	private function table_indexes(string $omnify_table, string $omnify_key_name = ''): array {
		global $wpdb;

		if ('' !== $omnify_key_name) {
			$omnify_indexes = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare('SHOW INDEX FROM %i WHERE Key_name = %s', $omnify_table, $omnify_key_name), ARRAY_A);
		} else {
			$omnify_indexes = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare('SHOW INDEX FROM %i', $omnify_table), ARRAY_A);
		}

		return is_array($omnify_indexes) ? $omnify_indexes : [];
	}

	private function column_exists(string $omnify_table, string $omnify_column): bool {
		global $wpdb;

		return null !== \Omnify\eCommerce\Support\Omnify_DB::get_row($wpdb, $wpdb->prepare('SHOW COLUMNS FROM %i LIKE %s', $omnify_table, $omnify_column));
	}

	private function add_column(string $omnify_table, string $omnify_column, string $omnify_definition): void {
		global $wpdb;

		if (! preg_match('/^[A-Za-z0-9_]+$/', $omnify_column)) {
			return;
		}

		switch ($omnify_definition) {
			case 'BIGINT UNSIGNED NULL DEFAULT NULL AFTER product_id':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i BIGINT UNSIGNED NULL DEFAULT NULL AFTER product_id', $omnify_table, $omnify_column));
				break;
			case 'CHAR(2) NULL AFTER billing_postcode':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i CHAR(2) NULL AFTER billing_postcode', $omnify_table, $omnify_column));
				break;
			case 'CHAR(2) NULL AFTER shipping_postcode':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i CHAR(2) NULL AFTER shipping_postcode', $omnify_table, $omnify_column));
				break;
			case 'DATE NULL AFTER preorder_enabled':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATE NULL AFTER preorder_enabled', $omnify_table, $omnify_column));
				break;
			case 'DATETIME NULL AFTER file_type':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATETIME NULL AFTER file_type', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER tax_label':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER tax_label', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(10,2) NULL AFTER height':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(10,2) NULL AFTER height', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(10,2) NULL AFTER length':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(10,2) NULL AFTER length', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(10,2) NULL AFTER stock_status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(10,2) NULL AFTER stock_status', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(10,2) NULL AFTER weight':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(10,2) NULL AFTER weight', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(18,6) NULL AFTER price':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(18,6) NULL AFTER price', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(18,6) NULL DEFAULT NULL AFTER discount_value':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(18,6) NULL DEFAULT NULL AFTER discount_value', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(18,6) NULL DEFAULT NULL AFTER min_order_amount':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(18,6) NULL DEFAULT NULL AFTER min_order_amount', $omnify_table, $omnify_column));
				break;
			case 'DECIMAL(18,6) NOT NULL DEFAULT 0 AFTER tax':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DECIMAL(18,6) NOT NULL DEFAULT 0 AFTER tax', $omnify_table, $omnify_column));
				break;
			case 'INT NOT NULL DEFAULT 0 AFTER fraud_status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT NOT NULL DEFAULT 0 AFTER fraud_status', $omnify_table, $omnify_column));
				break;
			case 'INT NOT NULL DEFAULT 0 AFTER quantity':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT NOT NULL DEFAULT 0 AFTER quantity', $omnify_table, $omnify_column));
				break;
			case 'INT NULL AFTER usage_limit':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT NULL AFTER usage_limit', $omnify_table, $omnify_column));
				break;
			case 'INT NULL DEFAULT 0 AFTER manage_stock':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT NULL DEFAULT 0 AFTER manage_stock', $omnify_table, $omnify_column));
				break;
			case 'INT UNSIGNED NULL DEFAULT NULL AFTER preorder_release_date':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT UNSIGNED NULL DEFAULT NULL AFTER preorder_release_date', $omnify_table, $omnify_column));
				break;
			case 'INT UNSIGNED NULL DEFAULT NULL AFTER refund_enabled':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT UNSIGNED NULL DEFAULT NULL AFTER refund_enabled', $omnify_table, $omnify_column));
				break;
			case 'INT UNSIGNED NULL DEFAULT 0 AFTER trust_badge_text':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT UNSIGNED NULL DEFAULT 0 AFTER trust_badge_text', $omnify_table, $omnify_column));
				break;
			case 'LONGTEXT NULL AFTER attributes':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i LONGTEXT NULL AFTER attributes', $omnify_table, $omnify_column));
				break;
			case 'LONGTEXT NULL AFTER height':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i LONGTEXT NULL AFTER height', $omnify_table, $omnify_column));
				break;
			case 'LONGTEXT NULL AFTER variation_settings':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i LONGTEXT NULL AFTER variation_settings', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER delivery_info':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER delivery_info', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER description':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER description', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER excluded_product_ids':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER excluded_product_ids', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER first_order_only':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER first_order_only', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER fraud_score':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER fraud_score', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER included_categories':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER included_categories', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER included_product_ids':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER included_product_ids', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER payment_method':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER payment_method', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER refund_window_days':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER refund_window_days', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER specifications':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER specifications', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER trust_badge_title':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER trust_badge_title', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER billing_country':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER billing_country', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER free_shipping':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER free_shipping', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER manage_stock':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER manage_stock', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER note_type':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER note_type', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER preorder_enabled':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER preorder_enabled', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER sku':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER sku', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER tax_inclusive':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER tax_inclusive', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER tax_reporting_code':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER tax_reporting_code', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER usage_count':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER usage_count', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 1 AFTER billing_country':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 1 AFTER billing_country', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 1 AFTER code':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 1 AFTER code', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 1 AFTER max_purchase_qty':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 1 AFTER max_purchase_qty', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(20) NULL AFTER billing_state':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(20) NULL AFTER billing_state', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(20) NULL AFTER shipping_state':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(20) NULL AFTER shipping_state', $omnify_table, $omnify_column));
				break;
			case "VARCHAR(32) NOT NULL DEFAULT 'instock' AFTER stock_qty":
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare("ALTER TABLE %i ADD COLUMN %i VARCHAR(32) NOT NULL DEFAULT 'instock' AFTER stock_qty", $omnify_table, $omnify_column));
				break;
			case "VARCHAR(32) NOT NULL DEFAULT 'none' AFTER shipping_phone":
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare("ALTER TABLE %i ADD COLUMN %i VARCHAR(32) NOT NULL DEFAULT 'none' AFTER shipping_phone", $omnify_table, $omnify_column));
				break;
			case "VARCHAR(32) NOT NULL DEFAULT 'pending' AFTER review_content":
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare("ALTER TABLE %i ADD COLUMN %i VARCHAR(32) NOT NULL DEFAULT 'pending' AFTER review_content", $omnify_table, $omnify_column));
				break;
			case "VARCHAR(32) NOT NULL DEFAULT 'private' AFTER author_id":
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare("ALTER TABLE %i ADD COLUMN %i VARCHAR(32) NOT NULL DEFAULT 'private' AFTER author_id", $omnify_table, $omnify_column));
				break;
			case "VARCHAR(32) NOT NULL DEFAULT 'safe' AFTER tracking_carrier":
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare("ALTER TABLE %i ADD COLUMN %i VARCHAR(32) NOT NULL DEFAULT 'safe' AFTER tracking_carrier", $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(45) NULL AFTER fraud_flags':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(45) NULL AFTER fraud_flags', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(60) NULL AFTER billing_company':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(60) NULL AFTER billing_company', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(60) NULL AFTER shipping_country':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(60) NULL AFTER shipping_country', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(60) NULL AFTER shipping_last_name':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(60) NULL AFTER shipping_last_name', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(80) NULL AFTER discount_amount':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(80) NULL AFTER discount_amount', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(80) NULL AFTER tax':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(80) NULL AFTER tax', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER billing_first_name':
			case 'VARCHAR(100) NULL AFTER billing_state':
			case 'VARCHAR(100) NULL AFTER currency':
			case 'VARCHAR(100) NULL AFTER fulfillment_status':
			case 'VARCHAR(100) NULL AFTER payment_instructions':
			case 'VARCHAR(100) NULL AFTER shipping_address_2':
			case 'VARCHAR(100) NULL AFTER shipping_city':
			case 'VARCHAR(100) NULL AFTER shipping_first_name':
			case 'VARCHAR(100) NULL AFTER shipping_phone':
			case 'VARCHAR(100) NULL AFTER shipping_postcode':
			case 'VARCHAR(100) NULL AFTER tracking_number':
				$this->add_common_varchar_100_column($omnify_table, $omnify_column, $omnify_definition);
				break;
			case 'VARCHAR(120) NULL AFTER height':
			case 'VARCHAR(120) NULL AFTER tax_rate':
				$this->add_common_varchar_120_column($omnify_table, $omnify_column, $omnify_definition);
				break;
			case 'VARCHAR(160) NULL AFTER billing_last_name':
			case 'VARCHAR(160) NULL AFTER shipping_phone':
			case 'VARCHAR(160) NULL AFTER video_url':
				$this->add_common_varchar_160_column($omnify_table, $omnify_column, $omnify_definition);
				break;
			case 'VARCHAR(190) NULL AFTER billing_address_1':
			case 'VARCHAR(190) NULL AFTER billing_company':
			case 'VARCHAR(190) NULL AFTER billing_phone':
			case 'VARCHAR(190) NULL AFTER billing_same_as_shipping':
			case 'VARCHAR(190) NULL AFTER shipping_address_1':
			case 'VARCHAR(190) NULL AFTER shipping_company':
			case 'VARCHAR(190) NULL AFTER shipping_last_name':
				$this->add_common_varchar_190_column($omnify_table, $omnify_column, $omnify_definition);
				break;
			case 'VARCHAR(255) NULL AFTER preorder_limit':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(255) NULL AFTER preorder_limit', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(500) NULL AFTER gallery_ids':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(500) NULL AFTER gallery_ids', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(500) NULL AFTER video_url':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(500) NULL AFTER video_url', $omnify_table, $omnify_column));
				break;
		}
	}

	private function add_column_after(string $omnify_table, string $omnify_column, string $omnify_definition, string $omnify_after): void {
		global $wpdb;

		if (! preg_match('/^[A-Za-z0-9_]+$/', $omnify_column) || ! preg_match('/^[A-Za-z0-9_]+$/', $omnify_after)) {
			return;
		}

		$omnify_key = $omnify_definition . ' AFTER ' . $omnify_after;

		switch ($omnify_key) {
			case 'DATETIME NULL AFTER expires_at':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATETIME NULL AFTER expires_at', $omnify_table, $omnify_column));
				break;
			case 'DATETIME NULL AFTER ip_address':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATETIME NULL AFTER ip_address', $omnify_table, $omnify_column));
				break;
			case 'DATETIME NULL AFTER status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATETIME NULL AFTER status', $omnify_table, $omnify_column));
				break;
			case 'DATETIME NULL AFTER stock_status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i DATETIME NULL AFTER stock_status', $omnify_table, $omnify_column));
				break;
			case 'INT UNSIGNED NULL DEFAULT NULL AFTER download_expiry_days':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i INT UNSIGNED NULL DEFAULT NULL AFTER download_expiry_days', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER bundled_ids':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER bundled_ids', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER max_purchase_qty':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER max_purchase_qty', $omnify_table, $omnify_column));
				break;
			case 'TEXT NULL AFTER upsell_ids':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TEXT NULL AFTER upsell_ids', $omnify_table, $omnify_column));
				break;
			case 'TINYINT NOT NULL DEFAULT 0 AFTER stock_status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i TINYINT NOT NULL DEFAULT 0 AFTER stock_status', $omnify_table, $omnify_column));
				break;
		}
	}

	private function add_common_varchar_100_column(string $omnify_table, string $omnify_column, string $omnify_definition): void {
		global $wpdb;

		switch ($omnify_definition) {
			case 'VARCHAR(100) NULL AFTER billing_first_name':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER billing_first_name', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER billing_state':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER billing_state', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER currency':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER currency', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER fulfillment_status':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER fulfillment_status', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER payment_instructions':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER payment_instructions', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER shipping_address_2':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER shipping_address_2', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER shipping_city':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER shipping_city', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER shipping_first_name':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER shipping_first_name', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER shipping_phone':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER shipping_phone', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER shipping_postcode':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER shipping_postcode', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(100) NULL AFTER tracking_number':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(100) NULL AFTER tracking_number', $omnify_table, $omnify_column));
				break;
		}
	}

	private function add_common_varchar_120_column(string $omnify_table, string $omnify_column, string $omnify_definition): void {
		global $wpdb;

		switch ($omnify_definition) {
			case 'VARCHAR(120) NULL AFTER height':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(120) NULL AFTER height', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(120) NULL AFTER tax_rate':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(120) NULL AFTER tax_rate', $omnify_table, $omnify_column));
				break;
		}
	}

	private function add_common_varchar_160_column(string $omnify_table, string $omnify_column, string $omnify_definition): void {
		global $wpdb;

		switch ($omnify_definition) {
			case 'VARCHAR(160) NULL AFTER billing_last_name':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(160) NULL AFTER billing_last_name', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(160) NULL AFTER shipping_phone':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(160) NULL AFTER shipping_phone', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(160) NULL AFTER video_url':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(160) NULL AFTER video_url', $omnify_table, $omnify_column));
				break;
		}
	}

	private function add_common_varchar_190_column(string $omnify_table, string $omnify_column, string $omnify_definition): void {
		global $wpdb;

		switch ($omnify_definition) {
			case 'VARCHAR(190) NULL AFTER billing_address_1':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER billing_address_1', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER billing_company':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER billing_company', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER billing_phone':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER billing_phone', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER billing_same_as_shipping':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER billing_same_as_shipping', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER shipping_address_1':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER shipping_address_1', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER shipping_company':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER shipping_company', $omnify_table, $omnify_column));
				break;
			case 'VARCHAR(190) NULL AFTER shipping_last_name':
				\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD COLUMN %i VARCHAR(190) NULL AFTER shipping_last_name', $omnify_table, $omnify_column));
				break;
		}
	}

	private function drop_column(string $omnify_table, string $omnify_column): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i DROP COLUMN %i', $omnify_table, $omnify_column));
	}

	private function add_key(string $omnify_table, string $omnify_key, string $omnify_column, bool $omnify_unique = false): void {
		global $wpdb;

		if ($omnify_unique) {
			\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD UNIQUE KEY %i (%i)', $omnify_table, $omnify_key, $omnify_column));
			return;
		}

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i ADD KEY %i (%i)', $omnify_table, $omnify_key, $omnify_column));
	}

	private function drop_key(string $omnify_table, string $omnify_key): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::query($wpdb, $wpdb->prepare('ALTER TABLE %i DROP KEY %i', $omnify_table, $omnify_key));
	}

	private function migrate_legacy_orders(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_items_table  = $this->omnify_schema->table('order_items');
		$omnify_products_table = $this->omnify_schema->table('products');

		// Check if orders table exists and has rows
		if (! $this->table_exists($omnify_orders_table)) {
			return;
		}

		$omnify_orders = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, $wpdb->prepare('SELECT * FROM %i', $omnify_orders_table), ARRAY_A);
		if (empty($omnify_orders)) {
			return;
		}

		foreach ($omnify_orders as $omnify_order) {
			$omnify_order_id = (int) $omnify_order['id'];

			// Check if this order already has items
			$omnify_has_items = (int) \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE order_id = %d',
				$omnify_items_table,
				$omnify_order_id
			));

			if ($omnify_has_items === 0) {
				$omnify_product_id = isset($omnify_order['product_id']) ? (int) $omnify_order['product_id'] : 0;
				$omnify_product_name = '';

				if ($omnify_product_id > 0) {
					$omnify_product_name = \Omnify\eCommerce\Support\Omnify_DB::get_var($wpdb, $wpdb->prepare(
						'SELECT name FROM %i WHERE id = %d',
						$omnify_products_table,
						$omnify_product_id
					));
				}

				if (empty($omnify_product_name)) {
					// translators: %d is the legacy product ID.
					$omnify_product_name = sprintf(__('Legacy Product #%d', 'omnifywp-ecommerce'), $omnify_product_id);
				}

				\Omnify\eCommerce\Support\Omnify_DB::insert($wpdb, 
					$omnify_items_table,
					[
						'order_id'     => $omnify_order_id,
						'product_id'   => $omnify_product_id,
						'product_name' => $omnify_product_name,
						'price'        => (float) $omnify_order['total'],
						'tax'          => 0.0,
						'quantity'     => 1,
						'created_at'   => $omnify_order['created_at'],
					]
				);
			}

			// Initialize new order fields if they are zero or null
			$omnify_subtotal        = isset($omnify_order['subtotal']) ? (float) $omnify_order['subtotal'] : 0.0;
			$omnify_tax             = isset($omnify_order['tax']) ? (float) $omnify_order['tax'] : 0.0;
			$omnify_refunded_amount = isset($omnify_order['refunded_amount']) ? (float) $omnify_order['refunded_amount'] : 0.0;

			if ($omnify_subtotal === 0.0 && (float) $omnify_order['total'] > 0.0) {
				\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
					$omnify_orders_table,
					[
						'subtotal'        => (float) $omnify_order['total'],
						'tax'             => 0.0,
						'refunded_amount' => 0.0,
					],
					['id' => $omnify_order_id]
				);
			}
		}
	}

	private function migrate_add_customer_tax_exempt(): void {
		global $wpdb;
		$omnify_customers_table = $this->omnify_schema->table('customers');
		$omnify_existing = $this->table_columns($omnify_customers_table);
		if (empty($omnify_existing)) {
			return;
		}

		if (! in_array('tax_exempt', array_column($omnify_existing, 'Field'), true)) {
			$this->add_column($omnify_customers_table, 'tax_exempt', 'TINYINT NOT NULL DEFAULT 0 AFTER billing_country');
		}
	}

	private function migrate_add_order_tax_context(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');
		$omnify_columns = [
			'tax_label' => 'VARCHAR(80) NULL AFTER tax',
			'tax_rate' => 'DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER tax_label',
			'tax_reporting_code' => 'VARCHAR(120) NULL AFTER tax_rate',
			'tax_inclusive' => 'TINYINT NOT NULL DEFAULT 0 AFTER tax_reporting_code',
			'tax_shipping' => 'TINYINT NOT NULL DEFAULT 0 AFTER tax_inclusive',
			'customer_tax_exempt' => 'TINYINT NOT NULL DEFAULT 0 AFTER tax_shipping',
		];

		foreach ($omnify_columns as $omnify_column => $omnify_definition) {
			if (! in_array($omnify_column, $omnify_existing_names, true)) {
				$this->add_column($omnify_orders_table, $omnify_column, $omnify_definition);
			}
		}
	}

	private function migrate_add_product_shipping_class(): void {
		global $wpdb;
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_products_table);
		if (empty($omnify_existing)) {
			return;
		}

		if (! in_array('shipping_class', array_column($omnify_existing, 'Field'), true)) {
			$this->add_column($omnify_products_table, 'shipping_class', 'VARCHAR(120) NULL AFTER height');
		}
	}

	private function migrate_add_payment_columns(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('payment_method', $omnify_existing_names, true)) {
			$this->add_column($omnify_orders_table, 'payment_method', 'VARCHAR(80) NULL AFTER discount_amount');
		}
		if (! in_array('payment_instructions', $omnify_existing_names, true)) {
			$this->add_column($omnify_orders_table, 'payment_instructions', 'TEXT NULL AFTER payment_method');
		}
	}

	private function migrate_add_physical_columns(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		$omnify_columns_to_add = [
			'shipping_first_name' => 'VARCHAR(100) NULL AFTER payment_instructions',
			'shipping_last_name'  => 'VARCHAR(100) NULL AFTER shipping_first_name',
			'shipping_address_1'  => 'VARCHAR(190) NULL AFTER shipping_last_name',
			'shipping_address_2'  => 'VARCHAR(190) NULL AFTER shipping_address_1',
			'shipping_city'       => 'VARCHAR(100) NULL AFTER shipping_address_2',
			'shipping_state'      => 'VARCHAR(100) NULL AFTER shipping_city',
			'shipping_postcode'   => 'VARCHAR(20) NULL AFTER shipping_state',
			'shipping_country'    => 'VARCHAR(100) NULL AFTER shipping_postcode',
			'shipping_phone'      => 'VARCHAR(60) NULL AFTER shipping_country',
			'fulfillment_status'  => "VARCHAR(32) NOT NULL DEFAULT 'none' AFTER shipping_phone",
			'tracking_number'     => 'VARCHAR(100) NULL AFTER fulfillment_status',
			'tracking_carrier'    => 'VARCHAR(100) NULL AFTER tracking_number',
		];

		foreach ($omnify_columns_to_add as $omnify_col => $omnify_definition) {
			if (! in_array($omnify_col, $omnify_existing_names, true)) {
				$this->add_column($omnify_orders_table, $omnify_col, $omnify_definition);
			}
		}
	}

	private function migrate_add_variation_columns(): void {
		global $wpdb;
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_products_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		$omnify_columns_to_add = [
			'sale_price'   => 'DECIMAL(18,6) NULL AFTER price',
			'sku'          => 'VARCHAR(100) NULL AFTER currency',
			'manage_stock' => 'TINYINT NOT NULL DEFAULT 0 AFTER sku',
			'stock_qty'    => 'INT NULL DEFAULT 0 AFTER manage_stock',
			'stock_status' => "VARCHAR(32) NOT NULL DEFAULT 'instock' AFTER stock_qty",
			'weight'       => 'DECIMAL(10,2) NULL AFTER stock_status',
			'length'       => 'DECIMAL(10,2) NULL AFTER weight',
			'width'        => 'DECIMAL(10,2) NULL AFTER length',
			'height'       => 'DECIMAL(10,2) NULL AFTER width',
			'attributes'   => 'LONGTEXT NULL AFTER height',
		];

		foreach ($omnify_columns_to_add as $omnify_col => $omnify_definition) {
			if (! in_array($omnify_col, $omnify_existing_names, true)) {
				$this->add_column($omnify_products_table, $omnify_col, $omnify_definition);
			}
		}
	}

	private function migrate_drop_order_key(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');

		// Check if order_key column exists
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (in_array('order_key', $omnify_existing_names, true)) {
			// Drop unique key index first if it exists
			$omnify_indexes = $this->table_indexes($omnify_orders_table, 'order_key');
			if (! empty($omnify_indexes)) {
				$this->drop_key($omnify_orders_table, 'order_key');
			}
			// Drop the column
			$this->drop_column($omnify_orders_table, 'order_key');
		}
	}

	private function migrate_add_gallery_video(): void {
		global $wpdb;
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_products_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('gallery_ids', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'gallery_ids', 'LONGTEXT NULL AFTER attributes');
		}
		if (! in_array('video_url', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'video_url', 'VARCHAR(500) NULL AFTER gallery_ids');
		}
	}

	private function migrate_add_variation_description(): void {
		global $wpdb;
		$omnify_variations_table = $this->omnify_schema->table('product_variations');
		$omnify_existing = $this->table_columns($omnify_variations_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('description', $omnify_existing_names, true)) {
			$this->add_column($omnify_variations_table, 'description', 'TEXT NULL AFTER attributes');
		}
	}

	private function migrate_add_variation_settings(): void {
		global $wpdb;
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_products_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('variation_settings', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'variation_settings', 'LONGTEXT NULL AFTER attributes');
		}
	}

	private function migrate_add_product_file_expiry(): void {
		global $wpdb;
		$omnify_files_table = $this->omnify_schema->table('product_files');
		$omnify_existing = $this->table_columns($omnify_files_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('expires_at', $omnify_existing_names, true)) {
			$this->add_column($omnify_files_table, 'expires_at', 'DATETIME NULL AFTER file_type');
			$this->add_key($omnify_files_table, 'expires_at', 'expires_at');
		}
	}

	private function migrate_add_product_trust_badge(): void {
		global $wpdb;
		$omnify_products_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_products_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('trust_badge_title', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'trust_badge_title', 'VARCHAR(160) NULL AFTER video_url');
		}
		if (! in_array('trust_badge_text', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'trust_badge_text', 'TEXT NULL AFTER trust_badge_title');
		}
		if (! in_array('download_expiry_days', $omnify_existing_names, true)) {
			$this->add_column($omnify_products_table, 'download_expiry_days', 'INT UNSIGNED NULL DEFAULT 0 AFTER trust_badge_text');
		}
	}

	private function migrate_add_order_delivery_columns(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('shipping_total', $omnify_existing_names, true)) {
			$this->add_column($omnify_orders_table, 'shipping_total', 'DECIMAL(18,6) NOT NULL DEFAULT 0 AFTER tax');
		}
		if (! in_array('shipping_method', $omnify_existing_names, true)) {
			$this->add_column($omnify_orders_table, 'shipping_method', 'VARCHAR(160) NULL AFTER shipping_total');
		}
	}

	private function migrate_reviews_table_status(): void {
		global $wpdb;
		$omnify_reviews_table = $this->omnify_schema->table('reviews');

		// Check if reviews table exists
		if (! $this->table_exists($omnify_reviews_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_reviews_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('status', $omnify_existing_names, true)) {
			$this->add_column($omnify_reviews_table, 'status', "VARCHAR(32) NOT NULL DEFAULT 'pending' AFTER review_content");
			$this->add_key($omnify_reviews_table, 'status', 'status');
		}
	}

	private function migrate_add_customer_address_columns(): void {
		global $wpdb;
		$omnify_customers_table = $this->omnify_schema->table('customers');

		// Check if customers table exists
		if (! $this->table_exists($omnify_customers_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_customers_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		$omnify_columns_to_add = [
			'shipping_first_name' => 'VARCHAR(100) NULL AFTER country',
			'shipping_last_name'  => 'VARCHAR(100) NULL AFTER shipping_first_name',
			'shipping_phone'      => 'VARCHAR(60) NULL AFTER shipping_last_name',
			'shipping_company'    => 'VARCHAR(160) NULL AFTER shipping_phone',
			'shipping_address_1'  => 'VARCHAR(190) NULL AFTER shipping_company',
			'shipping_address_2'  => 'VARCHAR(190) NULL AFTER shipping_address_1',
			'shipping_city'       => 'VARCHAR(100) NULL AFTER shipping_address_2',
			'shipping_state'      => 'VARCHAR(100) NULL AFTER shipping_city',
			'shipping_postcode'   => 'VARCHAR(20) NULL AFTER shipping_state',
			'shipping_country'    => 'CHAR(2) NULL AFTER shipping_postcode',
			'billing_first_name'  => 'VARCHAR(100) NULL AFTER shipping_country',
			'billing_last_name'   => 'VARCHAR(100) NULL AFTER billing_first_name',
			'billing_phone'       => 'VARCHAR(60) NULL AFTER billing_last_name',
			'billing_company'     => 'VARCHAR(160) NULL AFTER billing_phone',
			'billing_address_1'   => 'VARCHAR(190) NULL AFTER billing_company',
			'billing_address_2'   => 'VARCHAR(190) NULL AFTER billing_address_1',
			'billing_city'        => 'VARCHAR(100) NULL AFTER billing_address_2',
			'billing_state'       => 'VARCHAR(100) NULL AFTER billing_city',
			'billing_postcode'    => 'VARCHAR(20) NULL AFTER billing_state',
			'billing_country'     => 'CHAR(2) NULL AFTER billing_postcode',
		];

		foreach ($omnify_columns_to_add as $omnify_col => $omnify_definition) {
			if (! in_array($omnify_col, $omnify_existing_names, true)) {
				$this->add_column($omnify_customers_table, $omnify_col, $omnify_definition);
			}
		}
	}

	private function migrate_add_product_max_qty(): void {
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_products';
		$omnify_column = 'max_purchase_qty';

		if (! $this->column_exists($omnify_table, $omnify_column)) {
			$this->add_column_after($omnify_table, $omnify_column, 'INT UNSIGNED NULL DEFAULT NULL', 'download_expiry_days');
		}
	}

	private function migrate_order_items_refund_columns(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('order_items');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('variation_id', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'variation_id', 'BIGINT UNSIGNED NULL DEFAULT NULL AFTER product_id');
		}

		if (! in_array('refunded_qty', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'refunded_qty', 'INT NOT NULL DEFAULT 0 AFTER quantity');
		}
	}

	private function migrate_add_product_refund_policy(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('products');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('refund_enabled', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'refund_enabled', 'TINYINT NOT NULL DEFAULT 1 AFTER max_purchase_qty');
		}
		if (! in_array('refund_window_days', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'refund_window_days', 'INT UNSIGNED NULL DEFAULT NULL AFTER refund_enabled');
		}
		if (! in_array('refund_policy_text', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'refund_policy_text', 'TEXT NULL AFTER refund_window_days');
		}
	}

	private function migrate_extend_coupon_columns(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('coupons');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');
		$omnify_columns = [
			'is_active'                => "TINYINT NOT NULL DEFAULT 1 AFTER code",
			'min_order_amount'         => "DECIMAL(18,6) NULL DEFAULT NULL AFTER discount_value",
			'max_discount_amount'      => "DECIMAL(18,6) NULL DEFAULT NULL AFTER min_order_amount",
			'usage_limit_per_customer' => "INT NULL AFTER usage_limit",
			'free_shipping'            => "TINYINT NOT NULL DEFAULT 0 AFTER usage_count",
			'first_order_only'         => "TINYINT NOT NULL DEFAULT 0 AFTER free_shipping",
			'included_product_ids'     => "TEXT NULL AFTER first_order_only",
			'excluded_product_ids'     => "TEXT NULL AFTER included_product_ids",
			'included_categories'      => "TEXT NULL AFTER excluded_product_ids",
			'excluded_categories'      => "TEXT NULL AFTER included_categories",
		];

		foreach ($omnify_columns as $omnify_column => $omnify_definition) {
			if (! in_array($omnify_column, $omnify_existing_names, true)) {
				$this->add_column($omnify_table, $omnify_column, $omnify_definition);
			}
		}
	}

	private function migrate_order_billing_and_transaction_columns(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		if (! $this->table_exists($omnify_orders_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		$omnify_columns_to_add = [
			'billing_first_name'       => 'VARCHAR(100) NULL AFTER shipping_phone',
			'billing_last_name'        => 'VARCHAR(100) NULL AFTER billing_first_name',
			'billing_company'          => 'VARCHAR(160) NULL AFTER billing_last_name',
			'billing_phone'            => 'VARCHAR(60) NULL AFTER billing_company',
			'billing_address_1'        => 'VARCHAR(190) NULL AFTER billing_phone',
			'billing_address_2'        => 'VARCHAR(190) NULL AFTER billing_address_1',
			'billing_city'             => 'VARCHAR(100) NULL AFTER billing_address_2',
			'billing_state'            => 'VARCHAR(100) NULL AFTER billing_city',
			'billing_postcode'         => 'VARCHAR(20) NULL AFTER billing_state',
			'billing_country'          => 'VARCHAR(100) NULL AFTER billing_postcode',
			'billing_same_as_shipping' => 'TINYINT NOT NULL DEFAULT 1 AFTER billing_country',
			'transaction_id'           => 'VARCHAR(190) NULL AFTER billing_same_as_shipping',
			'order_number'             => 'VARCHAR(100) NULL AFTER transaction_id',
		];

		foreach ($omnify_columns_to_add as $omnify_col => $omnify_definition) {
			if (! in_array($omnify_col, $omnify_existing_names, true)) {
				$this->add_column($omnify_orders_table, $omnify_col, $omnify_definition);
			}
		}
		$omnify_indexes = $this->table_indexes($omnify_orders_table, 'order_number');
		if (empty($omnify_indexes)) {
			$this->add_key($omnify_orders_table, 'order_number', 'order_number', true);
		}
	}

	private function migrate_webhook_events_table(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('webhook_events');
		if (! $this->table_exists($omnify_table)) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$omnify_statements = $this->omnify_schema->statements();
			dbDelta($omnify_statements['webhook_events']);
		}
	}

	private function migrate_inventory_logs_table(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('inventory_logs');
		if (! $this->table_exists($omnify_table)) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$omnify_statements = $this->omnify_schema->statements();
			dbDelta($omnify_statements['inventory_logs']);
		}
	}

	private function migrate_populate_missing_order_numbers(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		if (! $this->table_exists($omnify_orders_table)) {
			return;
		}

		$omnify_orders = \Omnify\eCommerce\Support\Omnify_DB::get_results($wpdb, 
			$wpdb->prepare(
				"SELECT id FROM %i WHERE order_number IS NULL OR order_number = ''",
				$omnify_orders_table
			),
			ARRAY_A
		);

		if (empty($omnify_orders) || ! is_array($omnify_orders)) {
			return;
		}

		$omnify_settings = get_option('omnify_settings', []);
		$omnify_prefix   = sanitize_text_field((string) ($omnify_settings['order_number_prefix'] ?? 'OMN-'));
		$omnify_suffix   = sanitize_text_field((string) ($omnify_settings['order_number_suffix'] ?? ''));
		$omnify_padding  = max(1, min(10, absint($omnify_settings['order_number_padding'] ?? 4)));

		foreach ($omnify_orders as $omnify_order) {
			$omnify_order_id = (int) $omnify_order['id'];
			$omnify_order_number = $omnify_prefix . str_pad((string) $omnify_order_id, $omnify_padding, '0', STR_PAD_LEFT) . $omnify_suffix;

			\Omnify\eCommerce\Support\Omnify_DB::update($wpdb, 
				$omnify_orders_table,
				['order_number' => $omnify_order_number],
				['id' => $omnify_order_id]
			);
		}
	}

	private function migrate_add_order_note_visibility(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('order_notes');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_columns = $this->table_columns($omnify_table);
		$omnify_existing = array_column(is_array($omnify_columns) ? $omnify_columns : [], 'Field');

		if (! in_array('note_type', $omnify_existing, true)) {
			$this->add_column($omnify_table, 'note_type', "VARCHAR(32) NOT NULL DEFAULT 'private' AFTER author_id");
		}
		if (! in_array('customer_visible', $omnify_existing, true)) {
			$this->add_column($omnify_table, 'customer_visible', 'TINYINT NOT NULL DEFAULT 0 AFTER note_type');
		}
		$omnify_indexes = $this->table_indexes($omnify_table);
		$omnify_index_names = array_unique(array_column(is_array($omnify_indexes) ? $omnify_indexes : [], 'Key_name'));
		if (! in_array('customer_visible', $omnify_index_names, true)) {
			$this->add_key($omnify_table, 'customer_visible', 'customer_visible');
		}
		if (! in_array('note_type', $omnify_index_names, true)) {
			$this->add_key($omnify_table, 'note_type', 'note_type');
		}
	}

	private function migrate_add_backorder_preorder_columns(): void {
		global $wpdb;

		$omnify_tables = [
			$this->omnify_schema->table('products') => 'stock_status',
			$this->omnify_schema->table('product_variations') => 'stock_status',
		];

		foreach ($omnify_tables as $omnify_table => $omnify_after) {
			if (! $this->table_exists($omnify_table)) {
				continue;
			}
			$omnify_columns = $this->table_columns($omnify_table);
			$omnify_existing = array_column(is_array($omnify_columns) ? $omnify_columns : [], 'Field');

			if (! in_array('allow_backorders', $omnify_existing, true)) {
				$this->add_column_after($omnify_table, 'allow_backorders', 'TINYINT NOT NULL DEFAULT 0', $omnify_after);
				$omnify_existing[] = 'allow_backorders';
			}

			$omnify_definitions = [
				'preorder_enabled' => 'TINYINT NOT NULL DEFAULT 0 AFTER allow_backorders',
				'preorder_release_date' => 'DATE NULL AFTER preorder_enabled',
				'preorder_limit' => 'INT UNSIGNED NULL DEFAULT NULL AFTER preorder_release_date',
				'preorder_message' => 'VARCHAR(255) NULL AFTER preorder_limit',
			];

			foreach ($omnify_definitions as $omnify_column => $omnify_definition) {
				if (! in_array($omnify_column, $omnify_existing, true)) {
					$this->add_column($omnify_table, $omnify_column, $omnify_definition);
				}
			}
		}
	}

	private function migrate_add_product_bundled_ids(): void {
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_products';
		$omnify_column = 'bundled_ids';

		if (! $this->column_exists($omnify_table, $omnify_column)) {
			$this->add_column_after($omnify_table, $omnify_column, 'TEXT NULL', 'max_purchase_qty');
		}
	}

	private function migrate_add_related_products_columns(): void {
		global $wpdb;
		$omnify_table = $wpdb->prefix . 'omnify_products';

		if (! $this->column_exists($omnify_table, 'upsell_ids')) {
			$this->add_column_after($omnify_table, 'upsell_ids', 'TEXT NULL', 'bundled_ids');
		}

		if (! $this->column_exists($omnify_table, 'cross_sell_ids')) {
			$this->add_column_after($omnify_table, 'cross_sell_ids', 'TEXT NULL', 'upsell_ids');
		}
	}

	private function migrate_add_short_description(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('short_description', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'short_description', 'TEXT NULL AFTER description');
		}
	}

	private function migrate_add_order_fraud_columns(): void {
		global $wpdb;
		$omnify_orders_table = $this->omnify_schema->table('orders');
		$omnify_existing = $this->table_columns($omnify_orders_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		$omnify_columns_to_add = [
			'fraud_status' => "VARCHAR(32) NOT NULL DEFAULT 'safe' AFTER tracking_carrier",
			'fraud_score'  => 'INT NOT NULL DEFAULT 0 AFTER fraud_status',
			'fraud_flags'  => 'TEXT NULL AFTER fraud_score',
			'ip_address'   => 'VARCHAR(45) NULL AFTER fraud_flags',
		];

		foreach ($omnify_columns_to_add as $omnify_col => $omnify_definition) {
			if (! in_array($omnify_col, $omnify_existing_names, true)) {
				$this->add_column($omnify_orders_table, $omnify_col, $omnify_definition);
			}
		}
	}

	private function migrate_admin_activities_table(): void {
		// Table creation is handled dynamically by the Schema statements loop in migrate()
	}

	private function migrate_add_deleted_at_columns(): void {
		global $wpdb;

		$omnify_tables = [
			'products'         => 'stock_status',
			'customers'        => 'status',
			'orders'           => 'ip_address',
			'coupons'          => 'expires_at',
			'reviews'          => 'status',
			'abandoned_carts'  => 'status',
		];

		foreach ($omnify_tables as $omnify_table_key => $omnify_after_col) {
			$omnify_table = $this->omnify_schema->table($omnify_table_key);
			if (! $this->table_exists($omnify_table)) {
				continue;
			}
			$omnify_columns = $this->table_columns($omnify_table);
			if (empty($omnify_columns)) continue;

			$omnify_existing = array_column($omnify_columns, 'Field');

			if (! in_array('deleted_at', $omnify_existing, true)) {
				$this->add_column_after($omnify_table, 'deleted_at', 'DATETIME NULL', $omnify_after_col);
				$this->add_key($omnify_table, 'deleted_at', 'deleted_at');
			}
		}
	}

	private function migrate_add_product_specifications_delivery_return(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('specifications', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'specifications', 'LONGTEXT NULL AFTER variation_settings');
		}
		if (! in_array('delivery_info', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'delivery_info', 'TEXT NULL AFTER specifications');
		}
		if (! in_array('return_info', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'return_info', 'TEXT NULL AFTER delivery_info');
		}
	}

	private function migrate_add_product_video_poster(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('products');
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('video_poster_url', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'video_poster_url', 'VARCHAR(500) NULL AFTER video_url');
		}
	}

	private function migrate_add_customer_note_visibility(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('customer_notes');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_existing = $this->table_columns($omnify_table);
		if (empty($omnify_existing)) {
			return;
		}
		$omnify_existing_names = array_column($omnify_existing, 'Field');

		if (! in_array('customer_visible', $omnify_existing_names, true)) {
			$this->add_column($omnify_table, 'customer_visible', 'TINYINT NOT NULL DEFAULT 0 AFTER author_id');
		}

		$omnify_indexes = $this->table_indexes($omnify_table, 'customer_visible');
		if (empty($omnify_indexes)) {
			$this->add_key($omnify_table, 'customer_visible', 'customer_visible');
		}
	}

	private function migrate_add_order_items_compound_index(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('order_items');
		if (! $this->table_exists($omnify_table)) {
			return;
		}
		$omnify_indexes = $this->table_indexes($omnify_table, 'order_product');
		if (empty($omnify_indexes)) {
			$this->add_key($omnify_table, 'order_product', 'order_id, product_id');
		}
	}

	private function migrate_add_orders_indexes(): void {
		global $wpdb;
		$omnify_table = $this->omnify_schema->table('orders');
		if (! $this->table_exists($omnify_table)) {
			return;
		}

		if (empty($this->table_indexes($omnify_table, 'transaction_id'))) {
			$this->add_key($omnify_table, 'transaction_id', 'transaction_id');
		}

		if (empty($this->table_indexes($omnify_table, 'status'))) {
			$this->add_key($omnify_table, 'status', 'status');
		}

		if (empty($this->table_indexes($omnify_table, 'created_at'))) {
			$this->add_key($omnify_table, 'created_at', 'created_at');
		}
	}

	private function record(string $omnify_migration): void {
		global $wpdb;

		\Omnify\eCommerce\Support\Omnify_DB::replace(
			$wpdb,
			$this->omnify_schema->table('migrations'),
			[
				'migration' => $omnify_migration,
				'ran_at'    => current_time('mysql', true),
			],
			['%s', '%s']
		);
	}
}
