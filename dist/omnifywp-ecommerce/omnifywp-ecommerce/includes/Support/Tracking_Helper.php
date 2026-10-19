<?php
/**
 * Shipping Carrier Tracking Helper.
 *
 * Resolves courier carrier names and tracking numbers into deep tracking links.
 *
 * @package Omnify
 */

declare(strict_types=1);

namespace Omnify\eCommerce\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tracking_Helper {

	/**
	 * Build a direct track-and-trace URL based on carrier name and tracking number.
	 *
	 * @param string $carrier Carrier name (e.g. FedEx, DHL, UPS, USPS).
	 * @param string $tracking_number Consignment / tracking number.
	 * @return string Direct tracking URL or universal tracking fallback.
	 */
	public static function get_tracking_url(string $carrier, string $tracking_number): string {
		$carrier = trim($carrier);
		$tracking_number = trim($tracking_number);

		if ('' === $tracking_number) {
			return '';
		}

		$normalized = strtolower(preg_replace('/[^a-z0-9]/', '', $carrier));
		$encoded_number = rawurlencode($tracking_number);
		$url = '';

		switch ($normalized) {
			case 'fedex':
			case 'federalexpress':
				$url = 'https://www.fedex.com/fedextrack/?trknbr=' . $encoded_number;
				break;

			case 'ups':
			case 'unitedparcelservice':
				$url = 'https://www.ups.com/track?tracknum=' . $encoded_number;
				break;

			case 'dhl':
			case 'dhlexpress':
				$url = 'https://www.dhl.com/en/express/tracking.html?AWB=' . $encoded_number;
				break;

			case 'usps':
			case 'unitedstatespostalservice':
				$url = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $encoded_number;
				break;

			case 'royalmail':
				$url = 'https://www.royalmail.com/portal/rm/track?trackNumber=' . $encoded_number;
				break;

			case 'australiapost':
			case 'auspost':
				$url = 'https://auspost.com.au/mypost/track/#/details/' . $encoded_number;
				break;

			case 'canadapost':
				$url = 'https://www.canadapost-postescanada.ca/track-reperage/en#/search?searchFor=' . $encoded_number;
				break;

			case 'dpd':
				$url = 'https://tracking.dpd.de/status/en_US/parcel/' . $encoded_number;
				break;

			case 'evri':
			case 'hermes':
				$url = 'https://www.evri.com/track/parcel/' . $encoded_number . '/details';
				break;

			case 'tnt':
				$url = 'https://www.tnt.com/express/en_gc/site/shipping-tools/tracking.html?searchType=con&cons=' . $encoded_number;
				break;

			case 'aramex':
				$url = 'https://www.aramex.com/track/results?shipmentNumber=' . $encoded_number;
				break;

			case 'gls':
				$url = 'https://gls-group.eu/track/' . $encoded_number;
				break;

			case 'purolator':
				$url = 'https://www.purolator.com/en/shipping/tracker?pins=' . $encoded_number;
				break;

			case 'redx':
				$url = 'https://redx.com.bd/track/' . $encoded_number;
				break;

			case 'steadfast':
				$url = 'https://steadfast.com.bd/tracking/' . $encoded_number;
				break;

			case 'pathao':
				$url = 'https://merchant.pathao.com/tracking?consignment_id=' . $encoded_number;
				break;

			default:
				// Fallback to universal package tracker
				$url = 'https://parcelsapp.com/en/tracking/' . $encoded_number;
				break;
		}

		/**
		 * Filter the generated carrier tracking URL.
		 *
		 * @param string $url The generated tracking URL.
		 * @param string $carrier Carrier name.
		 * @param string $tracking_number Tracking number.
		 */
		return (string) apply_filters('omnify_carrier_tracking_url', $url, $carrier, $tracking_number);
	}
}
