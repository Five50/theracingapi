<?php
/**
 * Connection for Racing API Plugin
 *
 * @author     Karl Adams <karladams@getmediawise.com>
 * @copyright  Copyright (c) 2023, GetMediaWise Ltd
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://www.getmediawise.com
 * @package    TheRacingAPI
 * @since      0.1.0
 */

namespace TheRacingAPI\App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

/**
 * Connection class
 *
 * @since 0.1.0
 *
 * @package TheRacingAPI\App
 */
class Connection {

	private array $settings = array(
		'headers' => array(
			'Authorization' => 'Basic M2M5Qnl5dllBTXljNldLeWFDeEtUM2xWOkN0bWo5WkVoUWpPeEdIMU9sRzd6MjYxbQ=='
		),
	);

	public function run( string $url ) {

		$client = new Client(
			array(
				'base_uri'        => $url,
				'timeout'         => 30.0,
				'allow_redirects' => array( 'max' => 10 ),
				'version'         => 2.0,
			)
		);

		try {
			$response = $client->request( 'GET', '', $this->settings );
			$body     = $response->getBody();

			if ( $response->getStatusCode() === 200 ) {
				return json_decode( $body->getContents(), true );
			} else {
				return $response->getStatusCode();
			}
		} catch ( GuzzleException $e ) {
			return error_log( 'TheRacing API - error getting API data: ' . $e->getMessage() );
		}
	}
}