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

class Horses {
	use Trait_Tools;

	/**
	 * API End Point
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var string $api_url API URL
	 */
	private string $api_url = 'https://api.theracingapi.com/v1';

	private array $settings = array();

	public function __construct( array $settings, string $format = '' ) {
		$this->settings = $settings;

		if ( null === $this->settings['id'] ) {
			return null;
		}

		if ( $format === 'results' ) {
			$this->api_url  = $this->api_url . '/horses/' . $this->settings['id'] . '/' . $format;
		}

		if ( $format === '' ) {
			$this->api_url  = $this->api_url . '/results';
		}

		$params  = array();

		if ( '' !== $this->settings['start_date'] && '' !== $this->settings['end_date'] ) {
			$params['start_date'] = $this->settings['start_date'];
			$params['end_date']   = $this->settings['end_date'];
		}

		if ( '' !== $this->settings['course'] ) {
			$params['course'] = $this->settings['course'];
		}

		if ( '' !== $this->settings['type'] ) {
			$params['type'] = $this->settings['type'];
		}

		if ( '' !== $this->settings['going'] ) {
			$params['going'] = $this->settings['going'];
		}

		if ( '' !== $this->settings['race_class'] ) {
			$params['race_class'] = $this->settings['race_class'];
		}

		if ( '' !== $this->settings['limit'] ) {
			$params['limit'] = $this->settings['limit'];
		}

		if ( '' !== $this->settings['skip'] ) {
			$params['skip'] = $this->settings['skip'];
		}

		if ( ! empty( $params ) ) {
			$this->api_url .= esc_url( '?' . http_build_query( $params ) . '&region=gb&region=ire' );
		} else {
			$this->api_url .= esc_url( '?region=gb&region=ire' );
		}
	}

	public function get_current_runners(): ?array {
		$data = ( new Connection() )->run( esc_url( 'https://api.theracingapi.com/v1/racecards/standard?region_codes=gb&region_codes=ire' ) );

		if ( null === $data || ! isset( $data['racecards'] ) ) {
			return null;
		}

		$runners = array();

		if ( $data ) {
			foreach( $data['racecards'] as $racecard ) {
				foreach( $racecard['runners'] as $runner ) {
					$runners[] = $runner['horse'];
				}
			}
		}

		sort( $runners );

		return array_unique( $runners );
	}

	public function get_previous_winners( array $current_runners ): ?array {

		$winners = array();

		if ( empty( $current_runners ) ) {
			return null;
		}

		foreach( $current_runners as $runner ) {

			$data    = ( new Connection() )->run( esc_url( 'https://api.theracingapi.com/v1/horses/' . $runner . '/results?region=gb&region=ire' ) );
			$winners = array();

			foreach( $data['results'] as $item ) {
				$entrants = $item['runners'];

				foreach( $entrants as $entrant ) {
					if ( $entrant['position'] === '1' ) {
						$winners[] = $entrant['horse'];
					}
				}
			}
		}

		return $winners;
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

		if ( null === $data || ! isset( $data['results'] ) ) {
			return null;
		}

		return $data['results'];
	}

	public function set( array $items ): ?string {
		if ( empty( $items ) ) {
			return null;
		}

		$current_runners = $this->get_current_runners();

		//$previous_runners = $this->get_previous_winners( $current_runners );


		$details = array();
		$output = '';

		$i = 0;

		foreach( $items as $item ) {
			$details[ $i ]['race_name'] = $item['race_name'];
			$details[ $i ]['race_id']   = $item['race_id'];
			$details[ $i ]['course_id'] = $item['course_id'];
			$details[ $i ]['course']    = $item['course'];
			$details[ $i ]['date']      = $item['date'];
			$details[ $i ]['off']       = $item['off'];
			$details[ $i ]['type']      = $item['type'];
			$details[ $i ]['class']     = $item['class'];
			$details[ $i ]['distance']  = $item['distance'];
			$details[ $i ]['going']     = $item['going'];

			$entrants = $item['runners'];

			foreach( $entrants as $entrant ) {
				if ( $entrant['position'] === '1' ) {
					$details[ $i ]['horse_id'] = $entrant['horse_id'];
					$details[ $i ]['horse_name'] = $entrant['horse'];
					$details[ $i ]['draw'] = $entrant['draw'];
					$details[ $i ]['position'] = $entrant['position'];
					$details[ $i ]['prize'] = $entrant['prize'];
					$details[ $i ]['time'] = $entrant['time'];
				}
			}

			$i++;
		}


		if ( ! empty( $details ) ) {

			$rows = array();

			$headers = array(
				sprintf( '<th>%s</th>', 'Horse' ),
				sprintf( '<th class="cell-center">%s</th>', 'Draw' ),
				sprintf( '<th>%s</th>', 'Race' ),
				sprintf( '<th>%s</th>', 'Course' ),
				sprintf( '<th>%s</th>', 'Going' ),
			);

			foreach( $details as $key => $value ) {
				$rows[] = sprintf(
					'<tr>%s %s %s %s %s</tr>',

					sprintf(
						'<td>
							<div class="racecard-silk-horse-jockey">
								<div>
									<strong id="%s" class="racecard-horse">%s</strong>
								</div>
							</div>
						</td>',
						$value['horse_id'],
						$value['horse_name']
					),
					sprintf(
						'<td class="cell-center">%s</td>',
						$value['draw']
					),
					sprintf(
						'<td id="%s"><strong>%s</strong><br/><small>%s</small></td>',
						$value['race_id'],
						$value['race_name'] . ' (' . $value['off'] . ')',
						$this->set_date_format( $value['date'] )
					),
					sprintf(
						'<td id="%s">%s</td>',
						$value['course_id'],
						$value['course']
					),
					sprintf(
						'<td>%s</td>',
						$value['going']
					)
				);
			}
		}

		return sprintf(
			'<div class="racecard-table-overflow">
							<table class="racecard-table">
								<thead><tr>%s</tr></thead>
								<tbody>%s</tbody>
							</table>
							%s
						</div>',
			implode( '', $headers ),
			implode( '', $rows ),
			sprintf(
				'<p class="%s">%s <span><strong>%s</strong> %s</span></p>',
				'theracing-api-last-updated',
				'<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#02bb05"><path d="M480-144q-70 0-131.133-26.6-61.134-26.6-106.4-71.867-45.267-45.266-71.867-106.4Q144-410 144-480t26.6-131.133q26.6-61.134 71.867-106.4 45.266-45.267 106.4-71.867Q410-816 480-816q81 0 149.5 35T744-686v-130h72v240H576v-72h107q-35.907-44.8-88.453-70.4Q542-744 480-744q-109 0-186.5 77.5T216-480q0 109 77.5 186.5T480-216q109 0 186.5-77.5T744-480h72q0 140-98 238t-238 98Zm100-200L444-480v-192h72v162l115 115-51 51Z"/></svg>',
				esc_html__( 'Last Updated: ', 'theracing-api' ),
				date( 'l, jS F Y \a\t H:i' )
			)
		);
	}
}

