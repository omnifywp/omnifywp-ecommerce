<?php
/**
 * Country and state option helpers.
 *
 * @package Omnify
 */

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Omnify_Locations {
	/**
	 * @return array<string, string>
	 */
	public static function countries(): array {
		$omnify_countries = [
			'AF' => 'Afghanistan',
			'AX' => 'Åland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Sint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'CV' => 'Cabo Verde',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo (Republic)',
			'CD' => 'Congo (Democratic Republic)',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => "Côte d'Ivoire",
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curaçao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'SZ' => 'Eswatini',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'North Korea',
			'KR' => 'South Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MK' => 'North Macedonia',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestine',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Réunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthélemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French part)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten (Dutch part)',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (U.S.)',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		];

		asort($omnify_countries);

		return (array) apply_filters('omnify_countries', $omnify_countries);
	}

	/**
	 * Retrieve list of allowed countries based on store settings (selling_locations).
	 *
	 * @param array<string, mixed>|null $omnify_settings Optional plugin settings array. If null, fetched automatically.
	 * @return array<string, string>
	 */
	public static function allowed_countries(?array $omnify_settings = null): array {
		if ($omnify_settings === null) {
			$omnify_settings = (new \Omnify\eCommerce\Settings\Omnify_Settings_Repository())->all();
		}

		$omnify_all_countries = self::countries();
		$omnify_selling_locations = $omnify_settings['selling_locations'] ?? 'all';

		if ('specific' === $omnify_selling_locations) {
			$omnify_allowed = (array) ($omnify_settings['selling_countries'] ?? []);
			if (! empty($omnify_allowed)) {
				$omnify_allowed_map = array_fill_keys($omnify_allowed, true);
				return (array) apply_filters('omnify_allowed_countries', array_intersect_key($omnify_all_countries, $omnify_allowed_map), $omnify_settings);
			}
		} elseif ('all_except' === $omnify_selling_locations) {
			$omnify_excluded = (array) ($omnify_settings['selling_countries_exclude'] ?? []);
			if (! empty($omnify_excluded)) {
				$omnify_excluded_map = array_fill_keys($omnify_excluded, true);
				return (array) apply_filters('omnify_allowed_countries', array_diff_key($omnify_all_countries, $omnify_excluded_map), $omnify_settings);
			}
		}

		return (array) apply_filters('omnify_allowed_countries', $omnify_all_countries, $omnify_settings);
	}

	/**
	 * @return array<string, array<string, string>>
	 */
	public static function states(): array {
		$omnify_states = [
			'US' => [
				'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
				'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
				'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
				'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
				'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
				'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
				'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
				'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
				'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
				'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
				'DC' => 'District of Columbia',
			],
			'CA' => [
				'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick',
				'NL' => 'Newfoundland and Labrador', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories',
				'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec',
				'SK' => 'Saskatchewan', 'YT' => 'Yukon',
			],
			'AU' => [
				'ACT' => 'Australian Capital Territory', 'NSW' => 'New South Wales', 'NT' => 'Northern Territory',
				'QLD' => 'Queensland', 'SA' => 'South Australia', 'TAS' => 'Tasmania', 'VIC' => 'Victoria',
				'WA' => 'Western Australia',
			],
			'BD' => [
				'BAR' => 'Barishal', 'CTG' => 'Chattogram', 'DHA' => 'Dhaka', 'KHU' => 'Khulna',
				'MYM' => 'Mymensingh', 'RAJ' => 'Rajshahi', 'RAN' => 'Rangpur', 'SYL' => 'Sylhet',
			],
			'IN' => [
				'AP' => 'Andhra Pradesh', 'AR' => 'Arunachal Pradesh', 'AS' => 'Assam', 'BR' => 'Bihar',
				'CT' => 'Chhattisgarh', 'GA' => 'Goa', 'GJ' => 'Gujarat', 'HR' => 'Haryana',
				'HP' => 'Himachal Pradesh', 'JH' => 'Jharkhand', 'KA' => 'Karnataka', 'KL' => 'Kerala',
				'MP' => 'Madhya Pradesh', 'MH' => 'Maharashtra', 'MN' => 'Manipur', 'ML' => 'Meghalaya',
				'MZ' => 'Mizoram', 'NL' => 'Nagaland', 'OD' => 'Odisha', 'PB' => 'Punjab',
				'RJ' => 'Rajasthan', 'SK' => 'Sikkim', 'TN' => 'Tamil Nadu', 'TG' => 'Telangana',
				'TR' => 'Tripura', 'UP' => 'Uttar Pradesh', 'UT' => 'Uttarakhand', 'WB' => 'West Bengal',
				'DL' => 'Delhi',
			],
			'AE' => [
				'AZ' => 'Abu Dhabi', 'AJ' => 'Ajman', 'DU' => 'Dubai', 'FU' => 'Fujairah',
				'RK' => 'Ras al-Khaimah', 'SH' => 'Sharjah', 'UQ' => 'Umm al-Quwain',
			],
			'PT' => [
				'AV' => 'Aveiro', 'BE' => 'Beja', 'BR' => 'Braga', 'BA' => 'Bragança',
				'CB' => 'Castelo Branco', 'CO' => 'Coimbra', 'EV' => 'Évora', 'FA' => 'Faro',
				'GU' => 'Guarda', 'LE' => 'Leiria', 'LI' => 'Lisboa', 'PA' => 'Portalegre',
				'PO' => 'Porto', 'SA' => 'Santarém', 'SE' => 'Setúbal', 'VC' => 'Viana do Castelo',
				'VR' => 'Vila Real', 'VI' => 'Viseu', 'AC' => 'Açores', 'MA' => 'Madeira',
			],
			'GB' => [
				'ENG' => 'England', 'SCT' => 'Scotland', 'WLS' => 'Wales', 'NIR' => 'Northern Ireland',
			],
			'DE' => [
				'BW' => 'Baden-Württemberg', 'BY' => 'Bayern', 'BE' => 'Berlin', 'BB' => 'Brandenburg',
				'HB' => 'Bremen', 'HH' => 'Hamburg', 'HE' => 'Hessen', 'MV' => 'Mecklenburg-Vorpommern',
				'NI' => 'Niedersachsen', 'NW' => 'Nordrhein-Westfalen', 'RP' => 'Rheinland-Pfalz',
				'SL' => 'Saarland', 'SN' => 'Sachsen', 'ST' => 'Sachsen-Anhalt', 'SH' => 'Schleswig-Holstein',
				'TH' => 'Thüringen',
			],
			'FR' => [
				'IDF' => 'Île-de-France', 'ARA' => 'Auvergne-Rhône-Alpes', 'BFC' => 'Bourgogne-Franche-Comté',
				'BRE' => 'Bretagne', 'CVL' => 'Centre-Val de Loire', 'COR' => 'Corse',
				'GES' => 'Grand Est', 'HDF' => 'Hauts-de-France', 'NOR' => 'Normandie',
				'NAQ' => 'Nouvelle-Aquitaine', 'OCC' => 'Occitanie', 'PDL' => 'Pays de la Loire',
				'PAC' => "Provence-Alpes-Côte d'Azur",
			],
			'ES' => [
				'AN' => 'Andalucía', 'AR' => 'Aragón', 'AS' => 'Asturias', 'IB' => 'Islas Baleares',
				'CN' => 'Islas Canarias', 'CB' => 'Cantabria', 'CL' => 'Castilla y León',
				'CM' => 'Castilla-La Mancha', 'CT' => 'Cataluña', 'EX' => 'Extremadura',
				'GA' => 'Galicia', 'MD' => 'Madrid', 'MC' => 'Murcia', 'NC' => 'Navarra',
				'PV' => 'País Vasco', 'RI' => 'La Rioja', 'VC' => 'Valencia',
			],
			'IT' => [
				'ABR' => 'Abruzzo', 'BAS' => 'Basilicata', 'CAL' => 'Calabria', 'CAM' => 'Campania',
				'EMR' => 'Emilia-Romagna', 'FVG' => 'Friuli Venezia Giulia', 'LAZ' => 'Lazio',
				'LIG' => 'Liguria', 'LOM' => 'Lombardia', 'MAR' => 'Marche', 'MOL' => 'Molise',
				'PIE' => 'Piemonte', 'PUG' => 'Puglia', 'SAR' => 'Sardegna', 'SIC' => 'Sicilia',
				'TOS' => 'Toscana', 'TAA' => 'Trentino-Alto Adige', 'UMB' => 'Umbria',
				'VDA' => "Valle d'Aosta", 'VEN' => 'Veneto',
			],
			'NL' => [
				'DR' => 'Drenthe', 'FL' => 'Flevoland', 'FR' => 'Friesland', 'GE' => 'Gelderland',
				'GR' => 'Groningen', 'LI' => 'Limburg', 'NB' => 'Noord-Brabant', 'NH' => 'Noord-Holland',
				'OV' => 'Overijssel', 'UT' => 'Utrecht', 'ZE' => 'Zeeland', 'ZH' => 'Zuid-Holland',
			],
			'BR' => [
				'SP' => 'São Paulo', 'RJ' => 'Rio de Janeiro', 'MG' => 'Minas Gerais', 'RS' => 'Rio Grande do Sul',
				'PR' => 'Paraná', 'SC' => 'Santa Catarina', 'BA' => 'Bahia', 'PE' => 'Pernambuco',
				'CE' => 'Ceará', 'DF' => 'Distrito Federal',
			],
			'MX' => [
				'AG' => 'Aguascalientes', 'BC' => 'Baja California', 'BS' => 'Baja California Sur',
				'CM' => 'Ciudad de México', 'CO' => 'Coahuila', 'JA' => 'Jalisco', 'NL' => 'Nuevo León',
				'PUE' => 'Puebla', 'QR' => 'Quintana Roo', 'YUC' => 'Yucatán',
			],
			'PK' => [
				'PB' => 'Punjab', 'SD' => 'Sindh', 'KP' => 'Khyber Pakhtunkhwa', 'BA' => 'Balochistan',
				'IS' => 'Islamabad Capital Territory',
			],
		];

		return (array) apply_filters('omnify_states', $omnify_states);
	}

	/**
	 * Get display symbol for a given currency code.
	 */
	public static function currency_symbol(string $omnify_currency): string {
		$omnify_currency = strtoupper(trim($omnify_currency));
		$omnify_symbols = [
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CAD' => 'C$',
			'AUD' => 'A$',
			'BDT' => '৳',
			'INR' => '₹',
			'NPR' => 'रू',
			'PKR' => '₨',
			'BRL' => 'R$',
			'CNY' => '¥',
			'AED' => 'د.إ',
			'SAR' => 'ر.س',
			'SGD' => 'S$',
			'MYR' => 'RM',
			'NGN' => '₦',
			'NZD' => 'NZ$',
			'CHF' => 'Fr',
			'MXN' => '$',
			'ZAR' => 'R',
			'SEK' => 'kr',
			'NOK' => 'kr',
			'DKK' => 'kr',
			'PLN' => 'zł',
			'THB' => '฿',
			'IDR' => 'Rp',
			'PHP' => '₱',
			'TRY' => '₺',
			'ILS' => '₪',
			'EGP' => 'E£',
			'KWD' => 'KD',
			'QAR' => 'QR',
			'RUB' => '₽',
			'KRW' => '₩',
			'VND' => '₫',
		];

		$omnify_symbol = $omnify_symbols[$omnify_currency] ?? ($omnify_currency ?: '$');

		return (string) apply_filters('omnify_currency_symbol', $omnify_symbol, $omnify_currency);
	}

	/**
	 * Convert an amount from one currency to another using pegged USD exchange rates.
	 */
	public static function convert_currency(float $amount, string $from, string $to): float {
		$from = strtoupper(trim($from));
		$to   = strtoupper(trim($to));

		if ($from === $to || $amount <= 0.0) {
			return $amount;
		}

		$rates = [
			'USD' => 1.0,
			'EUR' => 0.92,
			'GBP' => 0.79,
			'JPY' => 155.0,
			'CAD' => 1.36,
			'AUD' => 1.52,
			'CHF' => 0.90,
			'CNY' => 7.23,
			'INR' => 83.5,
			'BDT' => 117.0,
			'BRL' => 5.15,
			'RUB' => 91.0,
			'KRW' => 1370.0,
			'SGD' => 1.35,
			'NZD' => 1.64,
			'MXN' => 16.8,
			'HKD' => 7.81,
			'SEK' => 10.7,
			'NOK' => 10.6,
			'TRY' => 32.2,
			'ZAR' => 18.4,
			'AED' => 3.67,
			'SAR' => 3.75,
		];

		$rates = (array) apply_filters('omnify_currency_exchange_rates', $rates);

		if (! isset($rates[$from]) || ! isset($rates[$to])) {
			return $amount;
		}

		$usd_amount = $amount / (float) $rates[$from];
		$converted  = $usd_amount * (float) $rates[$to];

		return (float) apply_filters('omnify_converted_currency_amount', round($converted, 4), $amount, $from, $to);
	}

	/**
	 * Get the standard decimal scale for a given currency code (ISO 4217).
	 *
	 * Zero-decimal currencies (e.g. JPY, KRW, VND, CLP, PYG, UGX, RWF, BIF, DJF, GNF, KMF, XOF, XAF, XPF) return 0.
	 * Three-decimal currencies (e.g. BHD, KWD, OMR, JOD, TND) return 3.
	 * Default standard is 2 decimals.
	 */
	public static function currency_decimals(string $currency): int {
		$currency = strtoupper(trim($currency));

		$zero_decimals = [
			'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW',
			'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
		];

		if (in_array($currency, $zero_decimals, true)) {
			return 0;
		}

		$three_decimals = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];
		if (in_array($currency, $three_decimals, true)) {
			return 3;
		}

		return (int) apply_filters('omnify_currency_decimals', 2, $currency);
	}

	/**
	 * @return array<string, array<string, array<string>>>
	 */
	public static function cities(): array {
		$omnify_cities = [
			'PT' => [
				'LI' => ['Lisboa', 'Sintra', 'Cascais', 'Loures', 'Amadora', 'Oeiras', 'Odivelas', 'Vila Franca de Xira', 'Mafra', 'Torres Vedras'],
				'PO' => ['Porto', 'Vila Nova de Gaia', 'Matosinhos', 'Gondomar', 'Maia', 'Valongo', 'Vila do Conde', 'Póvoa de Varzim', 'Santo Tirso'],
				'BR' => ['Braga', 'Guimarães', 'Vila Nova de Famalicão', 'Barcelos', 'Esposende', 'Fafe'],
				'CO' => ['Coimbra', 'Figueira da Foz', 'Cantanhede', 'Montemor-o-Velho', 'Lousã'],
				'FA' => ['Faro', 'Portimão', 'Albufeira', 'Loulé', 'Lagos', 'Olhão', 'Tavira', 'Silves'],
				'AV' => ['Aveiro', 'Santa Maria da Feira', 'Águeda', 'Ovar', 'Ílhavo', 'Espinho', 'Oliveira de Azeméis'],
				'SE' => ['Setúbal', 'Almada', 'Seixal', 'Barreiro', 'Palmela', 'Sesimbra', 'Montijo'],
				'LE' => ['Leiria', 'Caldas da Rainha', 'Alcobaça', 'Marinha Grande', 'Peniche', 'Pombal'],
				'EV' => ['Évora', 'Montemor-o-Novo', 'Vendas Novas', 'Estremoz'],
				'VI' => ['Viseu', 'Lamego', 'Mangualde', 'Tondela'],
				'VC' => ['Viana do Castelo', 'Ponte de Lima', 'Arcos de Valdevez', 'Caminha'],
				'VR' => ['Vila Real', 'Chaves', 'Peso da Régua'],
				'CB' => ['Castelo Branco', 'Covilhã', 'Fundão'],
				'GU' => ['Guarda', 'Seia', 'Pinhel'],
				'SA' => ['Santarém', 'Tomar', 'Torres Novas', 'Abrantes', 'Entroncamento', 'Cartaxo'],
				'BE' => ['Beja', 'Serpa', 'Moura'],
				'PA' => ['Portalegre', 'Elvas', 'Ponte de Sor'],
				'BA' => ['Bragança', 'Mirandela', 'Macedo de Cavaleiros'],
				'MA' => ['Funchal', 'Câmara de Lobos', 'Santa Cruz', 'Machico', 'Ribeira Brava'],
				'AC' => ['Ponta Delgada', 'Angra do Heroísmo', 'Ribeira Grande', 'Praia da Vitória', 'Horta'],
			],
			'US' => [
				'CA' => ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento', 'Fresno', 'Long Beach', 'Oakland', 'Bakersfield', 'Anaheim', 'Santa Ana', 'Riverside', 'Irvine'],
				'NY' => ['New York', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany', 'New Rochelle', 'Mount Vernon', 'Schenectady', 'Utica', 'White Plains'],
				'TX' => ['Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso', 'Arlington', 'Corpus Christi', 'Plano', 'Lubbock', 'Irving', 'Laredo'],
				'FL' => ['Miami', 'Orlando', 'Tampa', 'Jacksonville', 'St. Petersburg', 'Hialeah', 'Tallahassee', 'Fort Lauderdale', 'Port St. Lucie', 'Cape Coral', 'Pembroke Pines'],
				'IL' => ['Chicago', 'Aurora', 'Naperville', 'Joliet', 'Rockford', 'Springfield', 'Elgin', 'Peoria', 'Champaign', 'Waukegan'],
				'PA' => ['Philadelphia', 'Pittsburgh', 'Allentown', 'Reading', 'Erie', 'Upper Darby', 'Scranton', 'Bethlehem', 'Lancaster', 'Harrisburg'],
				'OH' => ['Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton', 'Parma', 'Canton', 'Youngstown', 'Lorain'],
				'GA' => ['Atlanta', 'Augusta', 'Columbus', 'Macon', 'Savannah', 'Athens', 'Sandy Springs', 'South Fulton', 'Roswell', 'Johns Creek'],
				'NC' => ['Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville', 'Cary', 'Wilmington', 'High Point', 'Concord'],
				'MI' => ['Detroit', 'Grand Rapids', 'Warren', 'Sterling Heights', 'Ann Arbor', 'Lansing', 'Dearborn', 'Livonia', 'Troy', 'Westland'],
				'NJ' => ['Newark', 'Jersey City', 'Paterson', 'Elizabeth', 'Lakewood', 'Edison', 'Woodbridge', 'Toms River', 'Hamilton', 'Trenton', 'Clifton'],
				'VA' => ['Virginia Beach', 'Chesapeake', 'Norfolk', 'Arlington', 'Richmond', 'Newport News', 'Alexandria', 'Hampton', 'Roanoke', 'Portsmouth'],
				'WA' => ['Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Kent', 'Everett', 'Renton', 'Spokane Valley', 'Federal Way'],
				'AZ' => ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Gilbert', 'Tempe', 'Peoria', 'Surprise', 'Yuma'],
				'MA' => ['Boston', 'Worcester', 'Springfield', 'Cambridge', 'Lowell', 'Brockton', 'Quincy', 'Lynn', 'New Bedford', 'Newton'],
				'CO' => ['Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood', 'Thornton', 'Arvada', 'Westminster', 'Pueblo', 'Boulder'],
			],
			'GB' => [
				'ENG' => ['London', 'Manchester', 'Birmingham', 'Leeds', 'Liverpool', 'Newcastle', 'Sheffield', 'Bristol', 'Nottingham', 'Leicester', 'Southampton', 'Brighton', 'Oxford', 'Cambridge'],
				'SCT' => ['Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee', 'Inverness', 'Stirling', 'Perth'],
				'WLS' => ['Cardiff', 'Swansea', 'Newport', 'Wrexham', 'Bangor', 'St Davids'],
				'NIR' => ['Belfast', 'Derry', 'Lisburn', 'Newry', 'Armagh', 'Bangor'],
			],
			'CA' => [
				'ON' => ['Toronto', 'Ottawa', 'Mississauga', 'Brampton', 'Hamilton', 'London', 'Markham', 'Vaughan', 'Kitchener', 'Windsor'],
				'BC' => ['Vancouver', 'Victoria', 'Surrey', 'Burnaby', 'Richmond', 'Abbotsford', 'Coquitlam', 'Kelowna', 'Langley'],
				'QC' => ['Montreal', 'Quebec City', 'Laval', 'Gatineau', 'Longueuil', 'Sherbrooke', 'Saguenay', 'Lévis', 'Trois-Rivières'],
				'AB' => ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge', 'St. Albert', 'Medicine Hat', 'Grande Prairie', 'Airdrie'],
			],
			'DE' => [
				'BY' => ['München', 'Nürnberg', 'Augsburg', 'Regensburg', 'Ingolstadt', 'Würzburg', 'Fürth', 'Erlangen', 'Bamberg'],
				'BE' => ['Berlin'],
				'NW' => ['Köln', 'Düsseldorf', 'Dortmund', 'Essen', 'Duisburg', 'Bochum', 'Wuppertal', 'Bielefeld', 'Bonn', 'Münster'],
				'BW' => ['Stuttgart', 'Mannheim', 'Karlsruhe', 'Freiburg', 'Heidelberg', 'Heilbronn', 'Ulm', 'Pforzheim'],
				'HE' => ['Frankfurt am Main', 'Wiesbaden', 'Kassel', 'Darmstadt', 'Offenbach am Main', 'Hanau', 'Giessen'],
				'HH' => ['Hamburg'],
				'SN' => ['Leipzig', 'Dresden', 'Chemnitz', 'Zwickau', 'Plauen'],
			],
			'FR' => [
				'IDF' => ['Paris', 'Boulogne-Billancourt', 'Saint-Denis', 'Argenteuil', 'Montreuil', 'Nanterre', 'Créteil', 'Versailles', 'Courbevoie'],
				'ARA' => ['Lyon', 'Saint-Étienne', 'Grenoble', 'Villeurbanne', 'Clermont-Ferrand', 'Annecy', 'Vénissieux'],
				'PAC' => ['Marseille', 'Nice', 'Toulon', 'Aix-en-Provence', 'Avignon', 'Cannes', 'Antibes'],
				'OCC' => ['Toulouse', 'Montpellier', 'Nîmes', 'Perpignan', 'Béziers', 'Montauban'],
				'NAQ' => ['Bordeaux', 'Limoges', 'Poitiers', 'Pau', 'La Rochelle', 'Mérignac'],
			],
			'ES' => [
				'MD' => ['Madrid', 'Móstoles', 'Alcalá de Henares', 'Fuenlabrada', 'Leganés', 'Getafe', 'Alcorcón', 'Torrejón de Ardoz'],
				'CT' => ['Barcelona', "L'Hospitalet de Llobregat", 'Badalona', 'Terrassa', 'Sabadell', 'Lleida', 'Tarragona', 'Mataró'],
				'AN' => ['Sevilla', 'Málaga', 'Córdoba', 'Granada', 'Jerez de la Frontera', 'Almería', 'Huelva', 'Marbella', 'Cádiz'],
				'VC' => ['Valencia', 'Alicante', 'Elche', 'Castellón de la Plana', 'Torrevieja', 'Gandia'],
				'GA' => ['Vigo', 'A Coruña', 'Ourense', 'Lugo', 'Santiago de Compostela', 'Pontevedra'],
			],
			'IT' => [
				'LAZ' => ['Roma', 'Latina', 'Guidonia Montecelio', 'Fiumicino', 'Aprilia', 'Viterbo', 'Pomezia', 'Tivoli'],
				'LOM' => ['Milano', 'Brescia', 'Monza', 'Bergamo', 'Busto Arsizio', 'Como', 'Sesto San Giovanni', 'Varese', 'Pavia'],
				'CAM' => ['Napoli', 'Salerno', 'Giugliano in Campania', 'Torre del Greco', 'Pozzuoli', 'Caserta'],
				'PIE' => ['Torino', 'Novara', 'Alessandria', 'Asti', 'Moncalieri', 'Cuneo'],
				'VEN' => ['Venezia', 'Verona', 'Padova', 'Vicenza', 'Treviso', 'Rovigo', 'Chioggia'],
				'EMR' => ['Bologna', 'Parma', 'Modena', 'Reggio Emilia', 'Ravenna', 'Rimini', 'Ferrara'],
			],
			'NL' => [
				'NH' => ['Amsterdam', 'Haarlem', 'Zaanstad', 'Haarlemmermeer', 'Alkmaar', 'Hilversum', 'Amstelveen'],
				'ZH' => ['Rotterdam', 'Den Haag', 'Leiden', 'Dordrecht', 'Zoetermeer', 'Delft', 'Alphen aan den Rijn'],
				'UT' => ['Utrecht', 'Amersfoort', 'Veenendaal', 'Zeist', 'Nieuwegein'],
				'NB' => ['Eindhoven', 'Tilburg', 'Breda', "'s-Hertogenbosch", 'Helmond', 'Oss'],
			],
			'AU' => [
				'NSW' => ['Sydney', 'Newcastle', 'Central Coast', 'Wollongong', 'Maitland', 'Tweed Heads', 'Wagga Wagga'],
				'VIC' => ['Melbourne', 'Geelong', 'Ballarat', 'Bendigo', 'Shepparton', 'Mildura', 'Warrnambool'],
				'QLD' => ['Brisbane', 'Gold Coast', 'Sunshine Coast', 'Townsville', 'Cairns', 'Toowoomba', 'Mackay'],
				'WA'  => ['Perth', 'Bunbury', 'Geraldton', 'Kalgoorlie', 'Albany', 'Busselton'],
				'SA'  => ['Adelaide', 'Mount Gambier', 'Whyalla', 'Murray Bridge', 'Port Augusta', 'Port Lincoln'],
			],
			'BD' => [
				'DHA' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Narsingdi', 'Manikganj', 'Munshiganj', 'Faridpur', 'Gopalganj'],
				'CTG' => ['Chattogram', "Cox's Bazar", 'Cumilla', 'Feni', 'Brahmanbaria', 'Noakhali', 'Chandpur'],
				'SYL' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'],
				'RAJ' => ['Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon', 'Natore', 'Chapainawabganj'],
				'KHU' => ['Khulna', 'Jashore', 'Kushtia', 'Jhenaidah', 'Satkhira', 'Bagerhat'],
				'BAR' => ['Barishal', 'Patuakhali', 'Bhola', 'Pirojpur', 'Barguna', 'Jhalokati'],
				'RAN' => ['Rangpur', 'Dinajpur', 'Gaibandha', 'Kurigram', 'Nilphamari', 'Thakurgaon'],
				'MYM' => ['Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur'],
			],
			'IN' => [
				'MH' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Navi Mumbai'],
				'DL' => ['New Delhi', 'North Delhi', 'South Delhi', 'West Delhi', 'East Delhi', 'Central Delhi'],
				'KA' => ['Bengaluru', 'Mysuru', 'Hubballi', 'Mangaluru', 'Belagavi', 'Kalaburagi', 'Davanagere'],
				'TN' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Erode', 'Vellore'],
				'WB' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Bardhaman', 'Kharagpur'],
				'TG' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Ramagundam', 'Khammam'],
				'GJ' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh'],
				'UP' => ['Lucknow', 'Kanpur', 'Varanasi', 'Agra', 'Prayagraj', 'Ghaziabad', 'Noida', 'Meerut'],
			],
			'PK' => [
				'PB' => ['Lahore', 'Faisalabad', 'Rawalpindi', 'Gujranwala', 'Multan', 'Sialkot', 'Bahawalpur', 'Sargodha'],
				'SD' => ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Nawabshah', 'Mirpur Khas'],
				'KP' => ['Peshawar', 'Mardan', 'Mingora', 'Abbottabad', 'Kohat', 'Dera Ismail Khan'],
				'IS' => ['Islamabad'],
			],
			'BR' => [
				'SP' => ['São Paulo', 'Guarulhos', 'Campinas', 'São Bernardo do Campo', 'Santo André', 'Osasco', 'Sorocaba', 'Ribeirão Preto'],
				'RJ' => ['Rio de Janeiro', 'São Gonçalo', 'Duque de Caxias', 'Nova Iguaçu', 'Niterói', 'Belford Roxo', 'Campos dos Goytacazes'],
				'MG' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora', 'Betim', 'Montes Claros'],
				'PR' => ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa', 'Cascavel'],
				'RS' => ['Porto Alegre', 'Caxias do Sul', 'Canoas', 'Pelotas', 'Santa Maria'],
			],
			'MX' => [
				'CM' => ['Ciudad de México', 'Iztapalapa', 'Gustavo A. Madero', 'Alvaro Obregón', 'Tlalpan'],
				'JA' => ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta'],
				'NL' => ['Monterrey', 'Guadalupe', 'San Nicolás de los Garza', 'Apodaca'],
			],
			'AE' => [
				'DU' => ['Dubai', 'Deira', 'Jumeirah', 'Downtown Dubai', 'Dubai Marina', 'Al Barsha'],
				'AZ' => ['Abu Dhabi', 'Al Ain', 'Al Dhafra'],
				'SH' => ['Sharjah', 'Khor Fakkan', 'Kalba'],
				'AJ' => ['Ajman'],
				'RK' => ['Ras al-Khaimah'],
				'FU' => ['Fujairah'],
				'UQ' => ['Umm al-Quwain'],
			],
		];

		return (array) apply_filters('omnify_cities', $omnify_cities);
	}

	/**
	 * @return array<string, array<string, array<string>>>
	 */
	public static function postcodes(): array {
		$omnify_postcodes = [
			'PT' => [
				'Lisboa'          => ['1000-001', '1050-001', '1100-001', '1150-001', '1200-001', '1250-001', '1300-001', '1400-001', '1500-001', '1600-001', '1700-001', '1800-001', '1900-001'],
				'Porto'           => ['4000-001', '4050-001', '4100-001', '4150-001', '4200-001', '4250-001', '4300-001', '4350-001'],
				'Sintra'          => ['2710-001', '2710-500', '2714-501', '2725-001', '2725-500', '2745-001'],
				'Cascais'         => ['2750-001', '2750-642', '2754-501', '2775-001', '2785-001'],
				'Braga'           => ['4700-001', '4705-001', '4710-001', '4715-001', '4720-001'],
				'Coimbra'         => ['3000-001', '3020-001', '3030-001', '3040-001', '3045-001'],
				'Faro'            => ['8000-001', '8005-001', '8009-001'],
				'Setúbal'         => ['2900-001', '2910-001', '2914-501', '2925-001'],
				'Aveiro'          => ['3800-001', '3810-001', '3814-501'],
				'Leiria'          => ['2400-001', '2410-001', '2415-001'],
				'Évora'           => ['7000-001', '7005-001'],
				'Funchal'         => ['9000-001', '9050-001', '9060-001'],
				'Ponta Delgada'   => ['9500-001', '9504-501'],
			],
			'US' => [
				'Los Angeles'     => ['90001', '90011', '90012', '90015', '90028', '90210', '90036', '90045', '90064', '90095'],
				'San Francisco'   => ['94102', '94103', '94107', '94109', '94110', '94114', '94117', '94121', '94122', '94133'],
				'San Diego'       => ['92101', '92103', '92109', '92115', '92120', '92126', '92130'],
				'San Jose'        => ['95110', '95112', '95120', '95125', '95128', '95134'],
				'Sacramento'      => ['95814', '95816', '95818', '95820', '95825'],
				'New York'        => ['10001', '10002', '10003', '10010', '10011', '10012', '10016', '10019', '10021', '10025', '10028', '10036'],
				'Buffalo'         => ['14201', '14202', '14209', '14213', '14221'],
				'Houston'         => ['77001', '77002', '77005', '77008', '77019', '77024', '77056', '77098'],
				'Austin'          => ['78701', '78702', '78703', '78704', '78705', '78745', '78759'],
				'Dallas'          => ['75201', '75202', '75204', '75206', '75214', '75219'],
				'San Antonio'     => ['78201', '78205', '78209', '78212', '78216'],
				'Miami'           => ['33101', '33125', '33130', '33131', '33139', '33145', '33156'],
				'Orlando'         => ['32801', '32803', '32805', '32819', '32830'],
				'Tampa'           => ['33602', '33606', '33609', '33611', '33629'],
				'Chicago'         => ['60601', '60602', '60605', '60611', '60614', '60618', '60622', '60647', '60657'],
				'Philadelphia'    => ['19102', '19103', '19104', '19106', '19107', '19123', '19147'],
				'Seattle'         => ['98101', '98102', '98103', '98104', '98109', '98115', '98122'],
				'Boston'          => ['02108', '02109', '02110', '02114', '02115', '02116', '02120', '02134'],
				'Atlanta'         => ['30303', '30305', '30308', '30309', '30318', '30324'],
				'Denver'          => ['80202', '80203', '80205', '80206', '80209', '80211'],
				'Phoenix'         => ['85003', '85004', '85008', '85012', '85016'],
			],
			'GB' => [
				'London'          => ['SW1A 1AA', 'EC1A 1BB', 'W1A 1AA', 'WC1A 1AA', 'E1 6AN', 'SE1 7PB', 'NW1 4NP', 'N1 9GU', 'W2 1NY'],
				'Manchester'      => ['M1 1AE', 'M2 1DH', 'M3 2WY', 'M4 1HQ', 'M15 6JA', 'M20 2RN'],
				'Birmingham'      => ['B1 1AA', 'B2 4QA', 'B3 2BJ', 'B4 7ET', 'B15 2TT'],
				'Leeds'           => ['LS1 1BA', 'LS2 7EA', 'LS6 1AN', 'LS11 5WD'],
				'Liverpool'       => ['L1 1DP', 'L2 2SP', 'L3 4FP', 'L8 7NH'],
				'Edinburgh'       => ['EH1 1YZ', 'EH2 2ER', 'EH3 9DR', 'EH8 9YL', 'EH12 5XQ'],
				'Glasgow'         => ['G1 1XQ', 'G2 1DY', 'G3 8AG', 'G12 8QQ'],
				'Cardiff'         => ['CF10 1EP', 'CF11 9BZ', 'CF14 3UZ', 'CF24 0AA'],
				'Belfast'         => ['BT1 1AA', 'BT2 7GE', 'BT7 1NN', 'BT9 6AW'],
			],
			'CA' => [
				'Toronto'         => ['M5V 2T6', 'M5H 2N2', 'M4B 1B3', 'M5J 2N8', 'M4Y 1H1', 'M6J 1V9'],
				'Ottawa'          => ['K1P 1J1', 'K1R 7S8', 'K2P 2G3', 'K1N 7B7'],
				'Vancouver'       => ['V6B 1A1', 'V5K 0A1', 'V6Z 2E6', 'V6E 1B2', 'V5T 1E1'],
				'Montreal'        => ['H2Y 1C6', 'H3A 0G4', 'H3B 1X9', 'H2X 1Y6', 'H4C 2Z6'],
				'Calgary'         => ['T2P 1J9', 'T2R 0E5', 'T2S 1M9', 'T2T 2L6'],
			],
			'DE' => [
				'München'         => ['80331', '80333', '80335', '80538', '80539', '80802'],
				'Berlin'          => ['10115', '10117', '10178', '10435', '10707', '10969'],
				'Hamburg'         => ['20095', '20354', '20457', '22765', '22767'],
				'Köln'            => ['50667', '50668', '50672', '50674', '50823'],
				'Frankfurt am Main'=> ['60311', '60313', '60322', '60325', '60329'],
				'Stuttgart'       => ['70173', '70174', '70178', '70182'],
			],
			'FR' => [
				'Paris'           => ['75001', '75002', '75004', '75008', '75009', '75011', '75015', '75016'],
				'Lyon'            => ['69001', '69002', '69003', '69006', '69007'],
				'Marseille'       => ['13001', '13002', '13006', '13008'],
				'Toulouse'        => ['31000', '31100', '31200', '31300', '31400'],
				'Bordeaux'        => ['33000', '33100', '33200', '33300', '33800'],
			],
			'ES' => [
				'Madrid'          => ['28001', '28004', '28012', '28013', '28014', '28028'],
				'Barcelona'       => ['08001', '08002', '08003', '08007', '08011', '08028'],
				'Sevilla'         => ['41001', '41002', '41004', '41010', '41013'],
				'Valencia'        => ['46001', '46002', '46003', '46004', '46005'],
			],
			'IT' => [
				'Roma'            => ['00118', '00184', '00185', '00186', '00187'],
				'Milano'          => ['20121', '20122', '20123', '20124', '20129'],
				'Napoli'          => ['80121', '80122', '80133', '80138', '80142'],
				'Torino'          => ['10121', '10122', '10123', '10125', '10128'],
			],
			'NL' => [
				'Amsterdam'       => ['1012 AB', '1015 CJ', '1017 BS', '1054 ES', '1071 DA'],
				'Rotterdam'       => ['3011 AA', '3012 CL', '3014 DA', '3016 CA'],
				'Utrecht'         => ['3511 AA', '3512 JE', '3514 BV'],
				'Den Haag'        => ['2511 AA', '2513 AA', '2518 AV'],
			],
			'BD' => [
				'Dhaka'           => ['1000', '1205', '1207', '1209', '1212', '1215', '1216', '1230'],
				'Gazipur'         => ['1700', '1701', '1702', '1703', '1704'],
				'Narayanganj'     => ['1400', '1410', '1420', '1430'],
				'Chattogram'      => ['4000', '4100', '4200', '4210', '4220'],
				'Cox\'s Bazar'    => ['4700', '4701', '4702'],
				'Cumilla'         => ['3500', '3501', '3502'],
				'Sylhet'          => ['3100', '3101', '3102', '3103'],
				'Rajshahi'        => ['6000', '6100', '6200', '6201'],
				'Khulna'          => ['9000', '9100', '9200', '9202'],
				'Barishal'        => ['8200', '8201', '8202'],
				'Rangpur'         => ['5400', '5401', '5402'],
				'Mymensingh'      => ['2200', '2201', '2202'],
			],
			'IN' => [
				'Mumbai'          => ['400001', '400004', '400020', '400050', '400053'],
				'New Delhi'       => ['110001', '110002', '110003', '110011', '110020'],
				'Bengaluru'       => ['560001', '560002', '560004', '560025', '560034'],
				'Chennai'         => ['600001', '600002', '600004', '600006', '600017'],
				'Kolkata'         => ['700001', '700007', '700016', '700020', '700026'],
				'Hyderabad'       => ['500001', '500003', '500016', '500034', '500081'],
				'Ahmedabad'       => ['380001', '380006', '380009', '380015'],
				'Pune'            => ['411001', '411004', '411016', '411038'],
			],
			'PK' => [
				'Lahore'          => ['54000', '54020', '54600', '54660', '54700'],
				'Karachi'         => ['74000', '74200', '75300', '75500', '75600'],
				'Islamabad'       => ['44000', '44010', '44080', '44100'],
				'Rawalpindi'      => ['46000', '46200', '46300'],
			],
		];

		return (array) apply_filters('omnify_postcodes', $omnify_postcodes);
	}
}
