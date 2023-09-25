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

class Results {
	use Trait_Tools;

	/**
	 * API End Point
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var string $api_url API URL
	 */
	public string $api_url = 'https://api.theracingapi.com/v1/results';

	public array $settings = array();

	/**
	 * Magic method to construct the class
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param array $settings
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
		$params         = array();

		if ( ! empty( $this->settings['limit'] ) ) {
			$params['limit'] = $this->settings['limit'];
		}

		if ( ! empty( $this->settings['course_id'] ) ) {
			$params['course'] = $this->settings['course_id'];
		}

		if ( ! empty( $this->settings['start_date'] && ! empty( $this->settings['end_date'] ) ) ) {
			$params['start_date'] = $this->settings['start_date'];
			$params['end_date']   = $this->settings['end_date'];
		}

		if ( ! empty( $params ) ) {
			$this->api_url .= '?' . http_build_query( $params );
		} else {
			$this->api_url .= esc_url( '?region=gb&region=ire' );
		}
	}

	/**
	 * Get results
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return mixed|void
	 */
	public function get() {
		$data = ( new Connection() )->run( $this->api_url );
		if ( isset( $data ) ) {
			return json_decode( json_encode( $data['results'] ), true );
		}
	}

	/**
	 * Set results
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param array $items Results
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public function set( array $items ) {
		if ( array( $items ) ) {
			$results = array();

			foreach ( $items as $race ) {

				$key = $race['date'] . '_' . $race['off'] . '_' . $race['course'];

				$off_time = $race['off'];

				if ( strpos($off_time, '11:') !== false) {
					$off_time = $off_time . ' am';
				} else {
					$off_time = $off_time . ' pm';
				}

				if ( $this->settings['accordion'] === 'true' ) {
					$results[ $key ][] = sprintf(
						'<div class="%s"><div class="%s">%s %s</div>%s</div>',
						'racecard-head racecard-head--accordion',
						'racecard-head-inner',
						sprintf(
							'<div class="%s">
								<span class="%s">%s %s</span>
								<span class="%s">%s</span>
							</div>',
							'racecard-head-race',
							'racecard-head-race__title',
							$race['off'],
							$race['course'],
							'racecard-head-race__name',
							$race['race_name'],
						),
						sprintf(
							'<div class="%s"><strong>%s</strong> %s<br/><strong>%s</strong> %s</div>',
							'racecard-head-race-date',
							esc_html__('Off Time:', 'theracing-api' ),
							$off_time,
							esc_html__( 'Date:', 'theracing-api' ),
							$this->set_date_format( $race['date'] )
						),
						sprintf(
							'<div class="%s"><svg xmlns="http://www.w3.org/2000/svg" height="%s" viewBox="0 96 960 960" width="%s"><path d="M480 712 240 472l43-43 197 197 197-197 43 43-240 240Z"/></svg></div>',
							'racecard-head-toggle',
							24,
							24
						)
					);
				} else {
					$results[ $key ][] = sprintf(
						'<div class="%s">%s %s</div>',
						'racecard-head',
						sprintf(
							'<div class="%s">
								<span class="%s">%s %s</span>
								<span class="%s">%s</span>
							</div>',
							'racecard-head-race',
							'racecard-head-race__title',
							$race['off'],
							$race['course'],
							'racecard-head-race__name',
							$race['race_name'],
						),
						sprintf(
							'<div class="%s"><strong>%s</strong> %s<br/><strong>%s</strong> %s</div>',
							'racecard-head-race-date',
							esc_html__('Off Time:', 'theracing-api' ),
							$race['off'],
							esc_html__( 'Date:', 'theracing-api' ),
							$this->set_date_format( $race['date'] )
						),
					);
				}

				if ( $this->settings['details'] !== "false" ) {

					$details = sprintf(
						'<li><strong>%s</strong> %s</li>',
						esc_html__( 'Runners:', 'theracing-api' ),
						count( $race['runners'] ) ?: '-'
					);

					$details .= sprintf(
						'<li><strong>%s</strong> %s</li>',
						esc_html__( 'Race Distance:', 'theracing-api' ),
						$race['dist'] ?: '-'
					);

					$details .= sprintf(
						'<li><strong>%s</strong> %s</li>',
						esc_html__( 'Rating Band:', 'theracing-api' ),
						$race['rating_band'] ?: '-'
					);

					$details .= sprintf(
						'<li><strong>%s</strong> %s</li>',
						esc_html__( 'Race Class:', 'theracing-api' ),
						$race['class'] ?: '-'
					);

					$results[ $key ][] = sprintf(
						'<div class="%s"><ul class="%s">%s</ul></div>',
						'racecard-details',
						'racecard-details-list',
						$details,
					);
				}

				$runners = '';

				$count = count( $race['runners'] );
				$i = 1;

				foreach ( $race['runners'] as $runner ) {

					$non = (int) $runner['position'] !== $i;

					if ( $non === true ) {
						$runners .= '<tr class="racecard-non-runner">';
					} else {
						$runners .= '<tr>';
					}

					$runners .= sprintf(
						'<td class="racecard-number-cell">
							<span class="%s">%s</span>
							<span class="%s">%s</span>
						</td>',
						'racecard-position racecard-position--' . $i,
						$runner['position'],
						'racecard-number racecard-number--result ' . ( $non === true ? 'racecard-number--nr' : '' ),
						$runner['number'] ?: ''
					);

					$runners .= sprintf(
						'<td>
							<div class="racecard-silk-horse-jockey">
								%s
								<div>
									<strong class="racecard-horse">%s</strong><br/>
									<small class="racecard-jockey">%s</small>
								</div>
							</div>
						</td>',
						! empty( $runner['silk_url'] ) ? sprintf( '<img class="racecard-silk" src="%s" alt="%s"/>', $runner['silk_url'], $runner['jockey'] ) : '',
						$runner['horse'],
						$runner['jockey']
					);

					$runners .= sprintf(
						'<td>
							<div class="racecard-trainer-owner">
								<strong class="racecard-horse">%s</strong>
								<small class="racecard-jockey">%s</small>
							</div>
						</td>',
						$runner['trainer'],
						$runner['owner']
					);

					$runners .= sprintf(
						'<td class="cell-center">%s</td>',
						$runner['prize']
					);
					$i++;
				}

				$head = array();

				$head[] = sprintf(
					'<th style="min-width:2.5rem" class="cell-center">%s</th>',
					esc_html__( '', 'theracing-api' )
				);

				$head[] = sprintf(
					'<th style="min-width:15rem">%s</th>',
					esc_html__( 'Horse/Jockey', 'theracing-api' )
				);

				$head[] = sprintf(
					'<th>%s</th>',
					esc_html__( 'Trainer/Owner', 'theracing-api' )
				);

				$head[] = sprintf(
					'<th class="cell-center">%s</th>',
					esc_html__( 'Prize', 'theracing-api' )
				);

				$results[ $key ][] .= sprintf(
					'<div class="racecard-table-overflow">
						<table class="racecard-table">
							<thead><tr>%s</tr></thead>
							<tbody>%s</tbody>
						</table>
					</div>',
					implode( ' ', $head ),
					$runners
				);
			}

			$output = '';

			if ( ! empty( $results ) ) {
				if ( ! empty( $this->settings['course'] ) ) {
					foreach ( $results as $key => $value ) {
						if ( strpos( $key, $this->settings['course'] ) !== false ) {
							$output .= sprintf(
								'<div class="%s" %s>%s</div>',
								'theracing-api' . ( $this->settings['accordion'] === 'true' ? ' theracing-api--accordion' : '' ),
								( $this->settings['accordion'] === 'true' ? 'aria-expanded="false"' : '' ),
								implode( '', $value )
							);
						}
					}

					if ( $output === '' ) {

						$output = 'Unfortunately there are no results for ';

						if ( ! empty( $this->settings['course'] ) ) {
							$output .= $this->settings['course'] . ' today (' . date( 'l jS F Y' ) . ').';
						}

						return sprintf(
							'<p class="theracing-api-container">%s</p>',
							$output
						);
					}
				} else {
					foreach ( $results as $key => $value ) {
						$output .= sprintf(
							'<div class="%s" %s>%s</div>',
							'theracing-api' . ( $this->settings['accordion'] === 'true' ? ' theracing-api--accordion' : '' ),
							( $this->settings['accordion'] === 'true' ? 'aria-expanded="false"' : '' ),
							implode( '', $value )
						);
					}
				}

				return sprintf(
					'<div class="theracing-api-container" %s>%s</div>',
					( $this->settings['accordion'] === 'true' ? 'data-component="accordion" data-accordion="single"' : '' ),
					$output
				);
			} else {
				if ( ! empty( $this->settings['course'] ) ) {
					$output .= $this->settings['course'];
				}

				if ( ! empty( $this->settings['start_date'] && ! empty( $this->settings['end_date'] ) ) ) {
					$start_date = date( 'l, jS F Y', strtotime( $this->settings['start_date'] ) );
					$end_date = date( 'l, jS F Y', strtotime( $this->settings['end_date'] ) );

					if ( $start_date !== $end_date ) {
						$output .= ' between ' . $start_date. ' and ' . $end_date;
					} else {
						$output .= ' on ' . $start_date;
					}
				} else {
					$output .= '  found';
				}

				return sprintf(
					'<div class="theracing-api-container">%s %s</div>',
					'Unfortunately there are no results for ',
					$output
				);
			}
		}
	}
}