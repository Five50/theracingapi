<?php
/**
 * Defaults for Racing API Plugin
 *
 * @author     Karl Adams <karladams@getmediawise.com>
 * @copyright  Copyright (c) 2023, GetMediaWise Ltd
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://www.getmediawise.com
 * @package    TheRacingAPI
 * @since      0.1.0
 */

namespace TheRacingAPI\App;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

/**
 * Class ShortCodes
 *
 * @since 0.1.0
 *
 * @package TheRacingAPI\App
 * @subpackage ShortCodes
 * @category Class
 */
class ShortCodes {
	use Trait_Tools;

	/**
	 * API URL.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var string $api_url The Racing API URL.
	 */
	public string $api_url = 'https://api.theracingapi.com/v1/';

	/**
	 * Class construct method.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_shortcode( 'theracing-api-racecards', array( $this, 'get_theracing_api_racecards_shortcode' ) );
		add_shortcode( 'theracing-api-big-racecards', array( $this, 'get_theracing_api_big_racecards_shortcode' ) );
		add_shortcode( 'theracing-api-results', array( $this, 'get_theracing_api_results_shortcode' ) );
		add_shortcode( 'theracing-api-horses', array( $this, 'get_theracing_api_horses_shortcode' ) );
	}

	public function get_theracing_api_horses_shortcode( $atts ): string {
		$args = shortcode_atts(
			array(
				'id'         => '',
				'start_date' => '',
				'end_date'   => '',
				'region'     => '',
				'course'     => '', // course_id
				// chase, flat, hurdle, nh_flat
				'type'       => '',
				// fast, firm, good, good_to_firm, good_to_soft, good_to_yielding, hard, heavy, holding, muddy, sloppy, slow, soft, soft_to_heavy, standard, standard_to_fast, standard_to_slow, very_soft, yielding, yielding_to_soft
				'going'      => '',
				// class_1, class_2, class_3, class_4, class_5, class_6, class_7
				'race_class' => '',
				'limit'      => 50,
				'skip'       => '',
			),
			$atts
		);

		$output = '';

		$horses = new Horses( $args );
		$items  = $horses->get();

		if ( ! is_array( $items ) || empty( $items ) ) {
			return $output;
		}

		$output = $horses->set( $items );

		return $output;
	}

	/**
	 * Get racecards from API shortcode
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_theracing_api_racecards_shortcode( $atts ): string {

		$args = shortcode_atts(
			array(
				'course'       => '',
				'day'          => '',
				'date'         => '',
				'off_time'     => '',
 				'regions'      => 'gb,ire',
				'type'         => 'pro',
				'details'      => 'true',
				'race_details' => 'false',
				'odds'         => 'show',
				'affiliates'   => ''
			),
			$atts
		);

		$transient  = 'theracing-api_racecards';
		$transient .= '_' . strtolower( str_replace( ' ', '-', get_the_title() ) );
		$transient .= empty( $args['day'] ) ? '' : '_' . $args['day'];
		$transient .= empty( $args['course'] ) ? '' : '_' . $args['course'];
		$output = '';

		//if ( false === ( $output = get_transient( $transient ) ) ) {
			$racecards = new Racecards( $args );
			$items = $racecards->get();

			if ( ! is_array( $items ) || empty( $items ) ) {
				return $output;
			}

			$output= $racecards->set( $items );
			set_transient( $transient, $output, $this->get_refresh( '24h' ) );
		//}

		return $output;
	}

	/**
	 * Get racecards from API shortcode
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_theracing_api_big_racecards_shortcode( $atts ): string {

		$args = shortcode_atts(
			array(
				'off_time'     => '',
				'course'       => '',
				'start_date'   => '',
				'end_date'     => '',
				'type'         => 'big-races',
				'race_details' => 'false',
				'details'      => 'true',
				'odds'         => 'hide',
				'affiliates'   => ''
			),
			$atts
		);

		$transient  = 'theracing-api_big_racecards';
		$transient .= '_' . strtolower( str_replace( ' ', '-', get_the_title() ) );
		$transient .= empty( $args['day'] ) ? '' : '_' . $args['day'];
		$transient .= empty( $args['course'] ) ? '' : '_' . $args['course'];
		$output = '';

		if ( false === ( $output = get_transient( $transient ) ) ) {
			$racecards = new Racecards( $args );
			$items = $racecards->get();

			if ( ! is_array( $items ) || empty( $items ) ) {
				return $output;
			}

			$output= $racecards->set( $items );
			set_transient( $transient, $output, $this->get_refresh( '24h' ) );
		}

		return $output;
	}

	/**
	 * Get the racing api results shortcode.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $atts array shortcode attributes.
	 * @return string
	 * @throws \Exception
	 */
	public function get_theracing_api_results_shortcode( $atts ): string {
		static $count = 0;

		$args = shortcode_atts(
			array(
				'course'     => '',
				'course_id'  => '',
				'regions'    => 'gb,ire',
				'limit'      => '50',
				'start_date' => '',
				'end_date'   => '',
				'details'    => 'false',
				'accordion'  => 'true'
			),
			$atts
		);

		$transient  = 'theracing-api_results';
		$transient .= '_' . strtolower( str_replace( ' ', '-', get_the_title() ) );
		$transient .= empty( $args['course'] ) ? '' : '_' . strtolower( $args['course'] );
		$transient .= empty( $args['course_id'] ) ? '' : '_' . $args['course_id'];
		$transient .= empty( $args['start_date'] ) && empty( $args['end_date'] ) ? '' : '_' . $args['start_date'] . '_' . $args['end_date'];
		$transient .= '_' . $count++;
		$output = '';

		if ( false === ( $output = get_transient( $transient ) ) ) {
			$results = new Results( $args );
			$items   = $results->get();

			if ( ! is_array( $items ) || empty( $items ) ) {
				return $output;
			}

			$output = $results->set( $items );
			set_transient( $transient, $output, $this->get_refresh( '24h' ) );
		}

		return $output;
	}
}