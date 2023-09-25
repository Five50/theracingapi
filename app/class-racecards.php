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
 * Racecards class
 *
 * @since 0.1.0
 * @package TheRacingAPI\App
 */
class Racecards {
	use Trait_Tools;

	/**
	 * API End Point
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var string $api_url API URL
	 */
	public string $api_url = 'https://api.theracingapi.com/v1/racecards';

	/**
	 * Settings
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var array
	 */
	public array $settings = array();

	/**
	 * Affiliates
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var array|string[]
	 */
	public array $affiliates = array(
		'Bet365',
		'Sky Bet',
		'Paddy Power',
		'William Hill',
		'Bet Victor',
		'Coral',
		'Unibet',
		'Betfred',
		'Bet UK',
		'Ladbrokes',
	);

	/**
	 * Magic method to construct the class
	 *
	 * @param array $settings
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
		$params         = array();

		switch ( $this->settings['type'] ) {
			case 'pro':
				if ( 'today' === $this->settings['date'] ) {
					$params['date'] = date( 'Y-m-d' );
				}

				if ( 'tomorrow' === $this->settings['date'] ) {
					$params['date'] = date( 'Y-m-d', strtotime( '+1 day' ) );


				}
				if ( ! empty( $this->settings['date'] ) ) {
					$params['date'] = $this->settings['date'];
				}
				break;
			case 'big-races':
				if ( ! empty( $this->settings['start_date'] ) && ! empty( $this->settings['end_date'] ) ) {
					$params['start_date'] = $this->settings['start_date'];
					$params['end_date']   = $this->settings['end_date'];
				}
				break;
			case 'summaries':
				if ( ! empty( $this->settings['date'] ) ) {
					$params['date'] = $this->settings['date'];
				}
				break;
			case 'free':
			case 'standard':
			default:
				$params['day'] = ! empty( $this->settings['day'] ) ? $this->settings['day'] : 'today';
				break;
		}

		if ( ! empty( $params ) && ! empty( $this->settings['type'] ) ) {
			$this->api_url .= esc_url( '/' . $this->settings['type'] . '?' . http_build_query( $params ) . '&region_codes=gb&region_codes=ire' );
		} elseif ( ! empty( $params ) && empty( $this->settings['type'] ) ) {
			$this->api_url .= esc_url( '?' . http_build_query( $params ) . '&region_codes=gb&region_codes=ire' );
		} elseif ( empty( $params ) && ! empty( $this->settings['type'] ) ) {
			$this->api_url .= esc_url( '/' . $this->settings['type'] . '?region_codes=gb&region_codes=ire' );
		} else {
			$this->api_url .= esc_url( '?region_codes=gb&region_codes=ire' );
		}
	}

	/**
	 * Init
	 *
	 * @return mixed
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function get() {
		$data = ( new Connection() )->run( $this->api_url );

		if ( null === $data || ! isset( $data['racecards'] ) ) {
			return null;
		}

		return $data['racecards'];
	}

	/**
	 * Get affiliates
	 *
	 * @return array|string[]
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function get_affiliates(): array {
		return empty( $this->settings['affiliates'] ) ?
			$this->affiliates : explode( '|', $this->settings['affiliates'] );
	}

	/**
	 * Set Racecards
	 *
	 * @param array $items
	 *
	 * @return string
	 * @throws \Exception
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function set( array $items ): string {
		if ( array( $items ) ) {

			$racecard = array();

			foreach ( $items as $race ) {

				$key = $race['off_time'] . '_' . $race['course'];

				$racecard[ $key ][] = sprintf(
					'<div class="%s">%s %s</div>',
					'racecard-head',
					sprintf(
						'<div class="%s">
								<span class="%s">%s %s</span>
								<span class="%s">%s</span>
							</div>',
						'racecard-head-race',
						'racecard-head-race__title',
						$race['off_time'],
						$race['course'] . ' (' . $race['region'] . ')',
						'racecard-head-race__name',
						$race['race_name'],
					),
					sprintf(
						'<span class="%s">%s %s</span>',
						'racecard-head-race__date',
						'<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"><path d="M576.225-240Q536-240 508-267.775q-28-27.774-28-68Q480-376 507.775-404q27.774-28 68-28Q616-432 644-404.225q28 27.774 28 68Q672-296 644.225-268q-27.774 28-68 28ZM216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Zm0-432h528v-96H216v96Zm0 0v-96 96Z"/></svg>',
						$this->set_date_format( $race['date'] )
					)
				);

				if ( 'true' === $this->settings['race_details'] ) {

					$details = '';

					$race_details = array(
						'Runners'       => count( $race['runners'] ),
						'Race Distance' => $race['distance'],
						'Race Class'    => $race['race_class'],
						'Surface'       => $race['surface'],
						'Prize Money'   => $race['prize'],
						'Rating'        => $race['rating_band'],
						'Age'           => $race['age_band'],
						'Type'          => $race['type']
					);

					foreach ( $race_details as $label => $value ) {
						if ( 'pro' === $this->settings['type'] || $label !== 'Race Distance' ) {
							$details .= sprintf(
								'<li><strong>%s</strong><span>%s</span></li>',
								esc_html__( $label, 'theracing-api' ),
								$value ?: '-'
							);
						}
					}

					$racecard[ $key ][] = sprintf(
						'<div class="%s"><strong>%s</strong><ul class="%s">%s</ul></div>',
						'racecard-details',
						esc_html__( 'Race Details', 'theracing-api' ),
						'racecard-details-list',
						$details,
					);
				}

				$runners = '';

				$count      = count( $race['runners'] );
				$i          = 1;
				$empty_odds = false;

				foreach ( $race['runners'] as $runner ) {
					$non     = (int) $runner['number'] !== $i;
					$runners .= true === $non ? '<tr class="racecard-non-runner">' : '<tr>';
					$runners .= sprintf( '<td class="racecard-number-cell"><span class="%s">%s</span></td>', 'racecard-number ' . ( $non === true ? 'racecard-number--nr' : '' ), $runner['number'] );

					if ( $this->settings['type'] !== 'basic' ) {
						$runners .= sprintf(
							'<td>
								<div class="racecard-silk-horse-jockey">
									%s
									<div>
										<strong class="racecard-horse" id="%s">%s (%s)</strong><br/>
										<small class="racecard-jockey">%s %s</small>
									</div>
								</div>
							</td>',
							( ! empty( $runner['silk_url'] ) ? sprintf(
								'<img class="racecard-silk" src="%s" alt="%s"/>',
								$runner['silk_url'],
								$runner['jockey'] ) : ''
							),
							$runner['horse_id'],
							$runner['horse'],
							$runner['region'],
							$runner['jockey'],
							( $this->settings['odds'] !== 'show' ) ? '' : $runner['form']
						);
					} else {
						$runners .= sprintf(
							'<td>
							<div class="racecard-silk-horse-jockey">
								<div>
									<strong class="racecard-horse">%s (%s)</strong><br/>
									<small class="racecard-jockey">%s</small>
								</div>
							</div>
						</td>',
							$runner['horse'],
							$runner['region'],
							$runner['jockey']
						);
					}

					if ( isset( $runner['odds'] ) && $this->settings['odds'] === 'show' && count( $runner['odds'] ) > 0 ) {
						foreach ( $runner['odds'] as $odds ) {
							if ( ! in_array( $odds['bookmaker'], $this->get_affiliates() ) ) {
								continue;
							}

							$token  = strtolower( str_replace( ' ', '-', $odds['bookmaker'] ) );
							$token  = $token === 'bet-uk' ? 'betuk' : ( $token === 'bet-victor' ? 'betvictor' : $token );
							$format = '<td data-affiliate="%s" class="racecard-odds-cell">%s</td>';
							$link   = $non ? '<span class="racecard-affiliate-link">%s</span>' : '<a class="racecard-affiliate-link" href="%s">%s</a>';

							$runners .= sprintf( $format, $token, sprintf( $link, esc_url( site_url() . '/out/' . $token . '-horse-racing' ), $odds['fractional'] ) );
						}
					}

					$empty_odds = count( $runner['odds'] ) === 0;

					if ( $empty_odds === true || $this->settings['odds'] !== 'show' ) {
						$runners .= sprintf(
							'<td class="cell-center"><div class="racecard-age">%s <small>(%s)</small></div></td><td>%s • %s</td><td class="cell-center"><div class="racecard-weights"><span>%s</span> <small>%s</small></div></td><td><div class="racecard-trainer-owner"><strong class="racecard-horse">%s</strong><small class="racecard-jockey">%s</small></div></td>',
							$runner['age'],
							$this->days_old( $runner['dob'] ),
							$runner['form'],
							strtoupper( $runner['sex'] ),
							( ! empty( $runner['lbs'] ) ) ? $runner['lbs'] . ' lbs' : '0 lbs',
							'(' . $this->pounds_to_kilograms( $runner['lbs'] ) . 'kg' . ')',
							$runner['trainer'],
							$runner['owner']
						);
					}

					$runners .= '</tr>';
					$i ++;
				}

				$head = array(
					sprintf( '<th class="racecard-table-head-cell--position cell-center">%s</th>', esc_html__( '#', 'theracing-api' ) ),
					sprintf( '<th class="racecard-table-head-cell--horse-jockey">%s</th>', esc_html__( 'Horse/Jockey', 'theracing-api' ) )
				);

				if ( 'show' !== $this->settings['odds'] || $empty_odds === true ) {
					$head = array_merge(
						$head,
						array(
							sprintf( '<th class="cell-center">%s</th>', esc_html__( 'Age', 'theracing-api' ) ),
							sprintf( '<th>%s</th>', esc_html__( 'Form', 'theracing-api' ) ),
							sprintf( '<th class="cell-center">%s</th>', esc_html__( 'Weight', 'theracing-api' ) ),
							sprintf( '<th>%s</th>', esc_html__( 'Trainer/Owner', 'theracing-api' ) ),
						)
					);
				}

				if ( $empty_odds === false && $this->settings['odds'] === 'show' ) {
					foreach ( $this->get_affiliates() as $affiliate ) {
						$lower = strtolower( str_replace( ' ', '-', $affiliate ) );

						$head[] = sprintf(
							'<th class="racecard-table-affiliate racecard-table-affiliate--%s">%s</th>',
							$lower,
							sprintf(
								'<img src="%s" alt="%s">',
								$this->get_plugin_url() . 'assets/media/' . $lower . '-logo.webp',
								$affiliate . ' ' . esc_html__( 'Logo', 'theracing-api' ),
							)
						);
					}
				}

				$racecard[ $key ][] .= sprintf(
					'<div class="racecard-table-overflow">
							<table class="racecard-table">
								<thead><tr>%s</tr></thead>
								<tbody>%s</tbody>
							</table>
							%s
						</div>',
					implode( ' ', $head ),
					$runners,
					sprintf(
						'<p class="%s">%s <span><strong>%s</strong> %s %s</span></p>',
						'theracing-api-last-updated',
						'<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#02bb05"><path d="M480-144q-70 0-131.133-26.6-61.134-26.6-106.4-71.867-45.267-45.266-71.867-106.4Q144-410 144-480t26.6-131.133q26.6-61.134 71.867-106.4 45.266-45.267 106.4-71.867Q410-816 480-816q81 0 149.5 35T744-686v-130h72v240H576v-72h107q-35.907-44.8-88.453-70.4Q542-744 480-744q-109 0-186.5 77.5T216-480q0 109 77.5 186.5T480-216q109 0 186.5-77.5T744-480h72q0 140-98 238t-238 98Zm100-200L444-480v-192h72v162l115 115-51 51Z"/></svg>',
						esc_html__( 'Last Updated: ', 'theracing-api' ),
						date( 'l, jS F Y \a\t H:i' ),
						$empty_odds === true ? ' ' . esc_html__( '(Odds not available)', 'theracing-api' ) : ''
					)
				);
			}

			if ( $this->settings['type'] !== 'big-races' ) {
				ksort( $racecard );
			}

			$output = '';

			if ( ! empty( $racecard ) ) {
				if ( ! empty( $this->settings['course'] ) && ! empty( $this->settings['off_time'] ) ) {
					foreach ( $racecard as $key => $value ) {
						if ( strpos( $key, $this->settings['course'] ) !== false && strpos( $key, $this->settings['off_time'] ) !== false ) {
							$output .= sprintf( '<div class="%s">%s</div>', 'theracing-api', implode( '', $value ) );
						}
					}
				} elseif ( ! empty( $this->settings['course'] ) ) {
					foreach ( $racecard as $key => $value ) {
						if ( strpos( $key, $this->settings['course'] ) !== false ) {
							$output .= sprintf( '<div class="%s">%s</div>', 'theracing-api', implode( '', $value ) );
						}
					}
				} elseif ( ! empty( $this->settings['off_time'] ) ) {
					foreach ( $racecard as $key => $value ) {
						if ( strpos( $key, $this->settings['off_time'] ) !== false ) {
							$output .= sprintf( '<div class="%s">%s</div>', 'theracing-api', implode( '', $value ) );
						}
					}
				} else {
					foreach ( $racecard as $key => $value ) {
						$output .= sprintf( '<div class="%s">%s</div>', 'theracing-api', implode( '', $value ) );
					}
				}

				return $output;
			} else {

				if ( ! empty( $this->settings['course'] ) ) {
					$output .= ' for ' . $this->settings['course'];
				}

				if ( 'pro' === $this->settings['type'] && ! empty( $this->settings['date'] ) ) {
					$output .= ' on ' . date( 'l, jS F Y', strtotime( $this->settings['date'] ) );
				}

				if ( 'big-races' === $this->settings['type'] && ! empty( $this->settings['start_date'] ) && ! empty( $this->settings['end_date'] ) ) {
					$output .= ( date( 'l, jS F Y', strtotime( $this->settings['start_date'] ) ) !== date( 'l, jS F Y', strtotime( $this->settings['end_date'] ) ) ) ?
						' between ' . date( 'l, jS F Y', strtotime( $this->settings['start_date'] ) ) . ' and ' . date( 'l, jS F Y', strtotime( $this->settings['end_date'] ) ) :
						' on ' . date( 'l, jS F Y', strtotime( $this->settings['start_date'] ) );
				}

				return sprintf(
					'<div class="theracing-api-container">%s %s</div>',
					esc_html__( 'Unfortunately no racecards have been found', 'theracing-api' ),
					$output . '.'
				);
			}
		}
	}
}