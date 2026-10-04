<?php
/**
 * Setup Wizard template.
 *
 * @package Omnify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$omnify_template_vars = get_defined_vars();
$omnify_settings = $omnify_template_vars['omnify_settings'] ?? [];

$omnify_store_name = $omnify_settings['store_name'] ?? get_bloginfo('name');
$omnify_store_email = $omnify_settings['store_email'] ?? get_option('admin_email');
$omnify_currency = $omnify_settings['default_currency'] ?? 'USD';
$omnify_country = $omnify_settings['default_country'] ?? 'US';

$omnify_cod_enabled = ($omnify_settings['enable_cod'] ?? 'no') === 'yes';
$omnify_bacs_enabled = ($omnify_settings['enable_bacs'] ?? 'no') === 'yes';
$omnify_stripe_enabled = ($omnify_settings['enable_stripe'] ?? 'no') === 'yes';
$omnify_paypal_enabled = ($omnify_settings['enable_paypal'] ?? 'no') === 'yes';

$omnify_countries = \Omnify\eCommerce\Support\Omnify_Locations::countries();

$omnify_all_currencies = [
	'USD' => 'USD — US Dollar ($)',
	'EUR' => 'EUR — Euro (€)',
	'GBP' => 'GBP — British Pound (£)',
	'JPY' => 'JPY — Japanese Yen (¥)',
	'CAD' => 'CAD — Canadian Dollar (C$)',
	'AUD' => 'AUD — Australian Dollar (A$)',
	'CHF' => 'CHF — Swiss Franc (Fr)',
	'CNY' => 'CNY — Chinese Yuan Renminbi (¥)',
	'HKD' => 'HKD — Hong Kong Dollar (HK$)',
	'SGD' => 'SGD — Singapore Dollar (S$)',
	'ARS' => 'ARS — Argentine Peso ($)',
	'BOB' => 'BOB — Bolivian Boliviano (Bs.)',
	'BRL' => 'BRL — Brazilian Real (R$)',
	'BZD' => 'BZD — Belize Dollar (BZ$)',
	'CLP' => 'CLP — Chilean Peso ($)',
	'COP' => 'COP — Colombian Peso ($)',
	'CRC' => 'CRC — Costa Rican Colón (₡)',
	'CUP' => 'CUP — Cuban Peso ($)',
	'DOP' => 'DOP — Dominican Peso (RD$)',
	'GTQ' => 'GTQ — Guatemalan Quetzal (Q)',
	'GYD' => 'GYD — Guyanese Dollar (GY$)',
	'HNL' => 'HNL — Honduran Lempira (L)',
	'HTG' => 'HTG — Haitian Gourde (G)',
	'JMD' => 'JMD — Jamaican Dollar (J$)',
	'MXN' => 'MXN — Mexican Peso ($)',
	'NIO' => 'NIO — Nicaraguan Córdoba (C$)',
	'PAB' => 'PAB — Panamanian Balboa (B/)',
	'PEN' => 'PEN — Peruvian Sol (S/)',
	'PYG' => 'PYG — Paraguayan Guaraní (₲)',
	'SRD' => 'SRD — Surinamese Dollar (SR$)',
	'TTD' => 'TTD — Trinidad & Tobago Dollar (TT$)',
	'UYU' => 'UYU — Uruguayan Peso ($U)',
	'VES' => 'VES — Venezuelan Bolívar (Bs.)',
	'ALL' => 'ALL — Albanian Lek (L)',
	'BAM' => 'BAM — Bosnia-Herzegovina Mark (KM)',
	'BGN' => 'BGN — Bulgarian Lev (лв)',
	'BYN' => 'BYN — Belarusian Ruble (Br)',
	'CZK' => 'CZK — Czech Koruna (Kč)',
	'DKK' => 'DKK — Danish Krone (kr)',
	'GEL' => 'GEL — Georgian Lari (₾)',
	'HRK' => 'HRK — Croatian Kuna (kn)',
	'HUF' => 'HUF — Hungarian Forint (Ft)',
	'ISK' => 'ISK — Icelandic Króna (kr)',
	'MDL' => 'MDL — Moldovan Leu (lei)',
	'MKD' => 'MKD — Macedonian Denar (ден)',
	'NOK' => 'NOK — Norwegian Krone (kr)',
	'PLN' => 'PLN — Polish Złoty (zł)',
	'RON' => 'RON — Romanian Leu (lei)',
	'RSD' => 'RSD — Serbian Dinar (din)',
	'RUB' => 'RUB — Russian Ruble (₽)',
	'SEK' => 'SEK — Swedish Krone (kr)',
	'TRY' => 'TRY — Turkish Lira (₺)',
	'UAH' => 'UAH — Ukrainian Hryvnia (₴)',
	'AED' => 'AED — UAE Dirham (د.إ)',
	'BHD' => 'BHD — Bahraini Dinar (.د.ب)',
	'DZD' => 'DZD — Algerian Dinar (دج)',
	'EGP' => 'EGP — Egyptian Pound (£)',
	'ILS' => 'ILS — Israeli New Shekel (₪)',
	'IQD' => 'IQD — Iraqi Dinar (ع.د)',
	'IRR' => 'IRR — Iranian Rial (﷼)',
	'JOD' => 'JOD — Jordanian Dinar (JD)',
	'KWD' => 'KWD — Kuwaiti Dinar (KD)',
	'LBP' => 'LBP — Lebanese Pound (£)',
	'LYD' => 'LYD — Libyan Dinar (LD)',
	'MAD' => 'MAD — Moroccan Dirham (MAD)',
	'OMR' => 'OMR — Omani Rial (﷼)',
	'QAR' => 'QAR — Qatari Riyal (﷼)',
	'SAR' => 'SAR — Saudi Riyal (﷼)',
	'SDG' => 'SDG — Sudanese Pound (ج.س.)',
	'SYP' => 'SYP — Syrian Pound (£)',
	'TND' => 'TND — Tunisian Dinar (د.ت)',
	'YER' => 'YER — Yemeni Rial (﷼)',
	'AOA' => 'AOA — Angolan Kwanza (Kz)',
	'BIF' => 'BIF — Burundian Franc (Fr)',
	'BWP' => 'BWP — Botswana Pula (P)',
	'CDF' => 'CDF — Congolese Franc (FC)',
	'CVE' => 'CVE — Cape Verdean Escudo (Esc)',
	'DJF' => 'DJF — Djiboutian Franc (Fr)',
	'ERN' => 'ERN — Eritrean Nakfa (Nfk)',
	'ETB' => 'ETB — Ethiopian Birr (Br)',
	'GHS' => 'GHS — Ghanaian Cedi (₵)',
	'GMD' => 'GMD — Gambian Dalasi (D)',
	'GNF' => 'GNF — Guinean Franc (Fr)',
	'KES' => 'KES — Kenyan Shilling (KSh)',
	'KMF' => 'KMF — Comorian Franc (Fr)',
	'LRD' => 'LRD — Liberian Dollar (L$)',
	'LSL' => 'LSL — Lesotho Loti (M)',
	'MGA' => 'MGA — Malagasy Ariary (Ar)',
	'MRU' => 'MRU — Mauritanian Ouguiya (UM)',
	'MUR' => 'MUR — Mauritian Rupee (Rs)',
	'MWK' => 'MWK — Malawian Kwacha (MK)',
	'MZN' => 'MZN — Mozambican Metical (MT)',
	'NAD' => 'NAD — Namibian Dollar (N$)',
	'NGN' => 'NGN — Nigerian Naira (₦)',
	'RWF' => 'RWF — Rwandan Franc (Fr)',
	'SCR' => 'SCR — Seychellois Rupee (Rs)',
	'SLL' => 'SLL — Sierra Leonean Leone (Le)',
	'SOS' => 'SOS — Somali Shilling (Sh)',
	'SSP' => 'SSP — South Sudanese Pound (£)',
	'STN' => 'STN — São Tomé & Príncipe Dobra (Db)',
	'SZL' => 'SZL — Swazi Lilangeni (L)',
	'TZS' => 'TZS — Tanzanian Shilling (Sh)',
	'UGX' => 'UGX — Ugandan Shilling (Sh)',
	'XAF' => 'XAF — Central African CFA Franc (Fr)',
	'XOF' => 'XOF — West African CFA Franc (Fr)',
	'ZAR' => 'ZAR — South African Rand (R)',
	'ZMW' => 'ZMW — Zambian Kwacha (ZK)',
	'ZWL' => 'ZWL — Zimbabwean Dollar (Z$)',
	'AFN' => 'AFN — Afghan Afghani (؋)',
	'BDT' => 'BDT — Bangladeshi Taka (৳)',
	'BTN' => 'BTN — Bhutanese Ngultrum (Nu)',
	'INR' => 'INR — Indian Rupee (₹)',
	'LKR' => 'LKR — Sri Lankan Rupee (Rs)',
	'MVR' => 'MVR — Maldivian Rufiyaa (Rf)',
	'NPR' => 'NPR — Nepalese Rupee (Rs)',
	'PKR' => 'PKR — Pakistani Rupee (₨)',
	'UZS' => 'UZS — Uzbekistani Som (лв)',
	'KZT' => 'KZT — Kazakhstani Tenge (₸)',
	'KGS' => 'KGS — Kyrgyzstani Som (лв)',
	'TJS' => 'TJS — Tajikistani Somoni (SM)',
	'TMT' => 'TMT — Turkmenistani Manat (T)',
	'IDR' => 'IDR — Indonesian Rupiah (Rp)',
	'KHR' => 'KHR — Cambodian Riel (៛)',
	'KPW' => 'KPW — North Korean Won (₩)',
	'KRW' => 'KRW — South Korean Won (₩)',
	'LAK' => 'LAK — Laotian Kip (₭)',
	'MMK' => 'MMK — Myanmar Kyat (K)',
	'MNT' => 'MNT — Mongolian Tögrög (₮)',
	'MOP' => 'MOP — Macanese Pataca (P)',
	'MYR' => 'MYR — Malaysian Ringgit (RM)',
	'PHP' => 'PHP — Philippine Peso (₱)',
	'THB' => 'THB — Thai Baht (฿)',
	'TWD' => 'TWD — New Taiwan Dollar (NT$)',
	'VND' => 'VND — Vietnamese Dong (₫)',
	'FJD' => 'FJD — Fijian Dollar (FJ$)',
	'NZD' => 'NZD — New Zealand Dollar (NZ$)',
	'PGK' => 'PGK — Papua New Guinean Kina (K)',
	'SBD' => 'SBD — Solomon Islands Dollar (SI$)',
	'TOP' => 'TOP — Tongan Paʻanga (T$)',
	'VUV' => 'VUV — Vanuatu Vatu (VT)',
	'WST' => 'WST — Samoan Tālā (WS$)',
];
ksort($omnify_all_currencies);
?>
<div class="wrap omnify-admin-wrapper omnify-setup-wizard-wrap">
	<div class="omnify-setup-wizard-container">
		<!-- Wizard Header -->
		<div class="omnify-setup-wizard-header">
			<div class="omnify-setup-wizard-logo" style="display: flex; align-items: center; justify-content: center; gap: 14px;">
				<img src="<?php echo esc_url(OMNIFY_URL . 'assets/icon-256x256.png'); ?>" alt="Omnify" style="width: 44px; height: 44px; border-radius: 11px; display: block; box-shadow: 0 4px 12px rgba(11, 81, 53, 0.2);" />
				<h1 style="margin: 0;"><?php esc_html_e('Omnify Setup Wizard', 'omnifywp-ecommerce'); ?></h1>
			</div>
			<p class="omnify-setup-wizard-subtitle"><?php esc_html_e('Configure your eCommerce store in a few quick steps.', 'omnifywp-ecommerce'); ?></p>

			<!-- Step Progress Tracker -->
			<div class="omnify-setup-steps">
				<div class="omnify-setup-step active" data-step="1">
					<div class="step-number">1</div>
					<div class="step-label"><?php esc_html_e('Store Profile', 'omnifywp-ecommerce'); ?></div>
				</div>
				<div class="step-connector"></div>
				<div class="omnify-setup-step" data-step="2">
					<div class="step-number">2</div>
					<div class="step-label"><?php esc_html_e('Payments', 'omnifywp-ecommerce'); ?></div>
				</div>
				<div class="step-connector"></div>
				<div class="omnify-setup-step" data-step="3">
					<div class="step-number">3</div>
					<div class="step-label"><?php esc_html_e('Finish & Demo', 'omnifywp-ecommerce'); ?></div>
				</div>
			</div>
		</div>

		<!-- Wizard Form -->
		<form id="omnify-setup-wizard-form" method="post" action="">
			<!-- STEP 1: Store Profile -->
			<div class="omnify-setup-panel active" data-panel="1">
				<h2 class="panel-title"><?php esc_html_e('Tell us about your store', 'omnifywp-ecommerce'); ?></h2>
				<p class="panel-desc"><?php esc_html_e('Provide basic details to configure your localized shop settings.', 'omnifywp-ecommerce'); ?></p>

				<div class="omnify-form-grid" style="margin-bottom: 24px;">
					<div class="omnify-form-group">
						<label for="store_name"><?php esc_html_e('Store Name', 'omnifywp-ecommerce'); ?></label>
						<input type="text" id="store_name" name="store_name" value="<?php echo esc_attr($omnify_store_name); ?>" required placeholder="<?php esc_attr_e('My Online Shop', 'omnifywp-ecommerce'); ?>" />
					</div>

					<div class="omnify-form-group">
						<label for="store_email"><?php esc_html_e('Store Email Address', 'omnifywp-ecommerce'); ?></label>
						<input type="email" id="store_email" name="store_email" value="<?php echo esc_attr($omnify_store_email); ?>" required placeholder="shop@example.com" />
						<span class="help-text" style="margin-top: 2px; display: block;"><?php esc_html_e('Used for order notifications and customer communications.', 'omnifywp-ecommerce'); ?></span>
					</div>

					<div class="omnify-form-row">
						<div class="omnify-form-group">
							<label for="currency"><?php esc_html_e('Store Currency', 'omnifywp-ecommerce'); ?></label>
							<select id="currency" name="currency" class="omnify-searchable-select">
								<?php foreach ($omnify_all_currencies as $omnify_code => $omnify_label) : ?>
									<option value="<?php echo esc_attr($omnify_code); ?>" <?php selected($omnify_currency, $omnify_code); ?>>
										<?php echo esc_html($omnify_label); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="omnify-form-group">
							<label for="country"><?php esc_html_e('Store Country', 'omnifywp-ecommerce'); ?></label>
							<select id="country" name="country" class="omnify-searchable-select">
								<?php foreach ($omnify_countries as $omnify_code => $omnify_name) : ?>
									<option value="<?php echo esc_attr($omnify_code); ?>" <?php selected($omnify_country, $omnify_code); ?>>
										<?php echo esc_html($omnify_name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<div class="omnify-setup-actions">
					<button type="button" class="omnify-button omnify-button--primary btn-next-step" data-next="2">
						<?php esc_html_e('Continue', 'omnifywp-ecommerce'); ?> &rarr;
					</button>
				</div>
			</div>

			<!-- STEP 2: Payments -->
			<div class="omnify-setup-panel" data-panel="2">
				<h2 class="panel-title"><?php esc_html_e('Enable payment gateways', 'omnifywp-ecommerce'); ?></h2>
				<p class="panel-desc"><?php esc_html_e('Choose how you want to accept payments from your customers.', 'omnifywp-ecommerce'); ?></p>

				<div class="omnify-payment-grid">
					<!-- Stripe Card -->
					<div class="omnify-payment-card <?php echo $omnify_stripe_enabled ? 'active' : ''; ?>">
						<div class="payment-card-icon">💳</div>
						<div class="payment-card-info">
							<h3><?php esc_html_e('Stripe Credit Cards', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Accept major credit/debit cards globally.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<div class="payment-card-action">
							<label class="omnify-toggle">
								<input type="checkbox" name="enable_stripe" value="yes" <?php checked($omnify_stripe_enabled); ?> />
								<span class="omnify-toggle__label"></span>
							</label>
						</div>
					</div>

					<!-- PayPal Card -->
					<div class="omnify-payment-card <?php echo $omnify_paypal_enabled ? 'active' : ''; ?>">
						<div class="payment-card-icon">🅿️</div>
						<div class="payment-card-info">
							<h3><?php esc_html_e('PayPal Express', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Accept payments via PayPal accounts.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<div class="payment-card-action">
							<label class="omnify-toggle">
								<input type="checkbox" name="enable_paypal" value="yes" <?php checked($omnify_paypal_enabled); ?> />
								<span class="omnify-toggle__label"></span>
							</label>
						</div>
					</div>

					<!-- Bank Transfer -->
					<div class="omnify-payment-card <?php echo $omnify_bacs_enabled ? 'active' : ''; ?>">
						<div class="payment-card-icon">🏦</div>
						<div class="payment-card-info">
							<h3><?php esc_html_e('Direct Bank Transfer', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Accept wire transfers or manual bank deposits.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<div class="payment-card-action">
							<label class="omnify-toggle">
								<input type="checkbox" name="enable_bacs" value="yes" <?php checked($omnify_bacs_enabled); ?> />
								<span class="omnify-toggle__label"></span>
							</label>
						</div>
					</div>

					<!-- Cash on Delivery -->
					<div class="omnify-payment-card <?php echo $omnify_cod_enabled ? 'active' : ''; ?>">
						<div class="payment-card-icon">📦</div>
						<div class="payment-card-info">
							<h3><?php esc_html_e('Cash on Delivery (COD)', 'omnifywp-ecommerce'); ?></h3>
							<p><?php esc_html_e('Let customers pay on delivery or collection.', 'omnifywp-ecommerce'); ?></p>
						</div>
						<div class="payment-card-action">
							<label class="omnify-toggle">
								<input type="checkbox" name="enable_cod" value="yes" <?php checked($omnify_cod_enabled); ?> />
								<span class="omnify-toggle__label"></span>
							</label>
						</div>
					</div>
				</div>

				<div class="omnify-setup-actions">
					<button type="button" class="omnify-button omnify-button--secondary btn-prev-step" data-prev="1">
						&larr; <?php esc_html_e('Back', 'omnifywp-ecommerce'); ?>
					</button>
					<button type="button" class="omnify-button omnify-button--primary btn-next-step" data-next="3">
						<?php esc_html_e('Continue', 'omnifywp-ecommerce'); ?> &rarr;
					</button>
				</div>
			</div>

			<!-- STEP 3: Demo Data & Finish -->
			<div class="omnify-setup-panel" data-panel="3">
				<div class="panel-finish-icon">🎉</div>
				<h2 class="panel-title"><?php esc_html_e('Almost finished!', 'omnifywp-ecommerce'); ?></h2>
				<p class="panel-desc"><?php esc_html_e('Your store configuration is complete. You can optionally generate mock store data to preview layout functions.', 'omnifywp-ecommerce'); ?></p>

				<!-- Demo Data Generation Section -->
				<div class="omnify-setup-demo-data-card">
					<div class="demo-data-header">
						<span class="dashicons dashicons-database-add"></span>
						<h3><?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?></h3>
					</div>
					<p><?php esc_html_e('This will create dummy products (physical, digital, variations, bundles), customers, reviews, and orders to populate your dashboard immediately.', 'omnifywp-ecommerce'); ?></p>
					
					<div class="demo-data-action-wrapper">
						<button type="button" id="omnify-btn-generate-demo" class="omnify-button omnify-button--secondary" style="display:inline-flex; align-items:center; gap:4px;">
							<span class="dashicons dashicons-admin-generic" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
							<?php esc_html_e('Generate Demo Data', 'omnifywp-ecommerce'); ?>
						</button>
					</div>

					<!-- AJAX Progress Indicator -->
					<div id="omnify-demo-progress-container" style="display:none; margin-top: 16px;">
						<div class="omnify-wizard-progress" style="height:8px; margin: 8px 0; background:#e2e8f0; border-radius:999px; overflow:hidden;">
							<div id="omnify-demo-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg, #10b981, #059669); transition:width 0.3s ease;"></div>
						</div>
						<div id="omnify-demo-progress-status" style="font-size:12px; color:var(--omnify-gray-600); text-align:center; font-weight:500;">
							<?php esc_html_e('Initializing data seeder...', 'omnifywp-ecommerce'); ?>
						</div>
					</div>
				</div>

				<div class="omnify-setup-actions" style="margin-top: 32px;">
					<button type="button" class="omnify-button omnify-button--secondary btn-prev-step" data-prev="2">
						&larr; <?php esc_html_e('Back', 'omnifywp-ecommerce'); ?>
					</button>
					<button type="submit" class="omnify-button omnify-button--primary btn-finish-setup" style="display:inline-flex; align-items:center; gap:4px;">
						<?php esc_html_e('Save & Go to Dashboard', 'omnifywp-ecommerce'); ?>
						<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 1; vertical-align: middle;"></span>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
