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
		return [
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AU' => 'Australia',
			'BD' => 'Bangladesh',
			'IN' => 'India',
			'PK' => 'Pakistan',
			'AE' => 'United Arab Emirates',
			'SA' => 'Saudi Arabia',
			'DE' => 'Germany',
			'FR' => 'France',
			'IT' => 'Italy',
			'ES' => 'Spain',
			'NL' => 'Netherlands',
			'BE' => 'Belgium',
			'SE' => 'Sweden',
			'NO' => 'Norway',
			'DK' => 'Denmark',
			'FI' => 'Finland',
			'IE' => 'Ireland',
			'CH' => 'Switzerland',
			'AT' => 'Austria',
			'PT' => 'Portugal',
			'PL' => 'Poland',
			'CZ' => 'Czech Republic',
			'GR' => 'Greece',
			'TR' => 'Turkey',
			'ZA' => 'South Africa',
			'NG' => 'Nigeria',
			'KE' => 'Kenya',
			'EG' => 'Egypt',
			'MA' => 'Morocco',
			'CN' => 'China',
			'JP' => 'Japan',
			'KR' => 'South Korea',
			'SG' => 'Singapore',
			'MY' => 'Malaysia',
			'TH' => 'Thailand',
			'VN' => 'Vietnam',
			'PH' => 'Philippines',
			'ID' => 'Indonesia',
			'LK' => 'Sri Lanka',
			'NP' => 'Nepal',
			'NZ' => 'New Zealand',
			'BR' => 'Brazil',
			'MX' => 'Mexico',
			'AR' => 'Argentina',
			'CL' => 'Chile',
			'CO' => 'Colombia',
			'PE' => 'Peru',
			'IL' => 'Israel',
			'QA' => 'Qatar',
			'KW' => 'Kuwait',
			'OM' => 'Oman',
		];
	}

	/**
	 * @return array<string, array<string, string>>
	 */
	public static function states(): array {
		return [
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
		];
	}
}
