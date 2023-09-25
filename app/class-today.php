<?php

namespace TheRacingAPI\App;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

class Today {

	public int $runners = 0;

	public function get_runners(): ?array {
		$data = ( new Connection() )->run( 'https://api.theracingapi.com/v1/racecards/standard?region_codes=gb&region_codes=ire' );

		$racecards = $data['racecards'];
		$horses    = array();

		if ( null === $data || ! isset( $data['racecards'] ) ) {
			return null;
		}

		if ( count( $racecards ) > 0 ) {
			foreach ( $racecards as $race ) {
				foreach ( $race['runners'] as $runner ) {
					$horses[] = array(
						'id'   => $runner['horse_id'],
						'name' => $runner['horse'],
					);
				}
			}
		}

		$this->runners = count( $horses );

		return $horses;
	}

	public function get_winners(): string {
		$runners = $this->get_runners();
		$details = array();

		foreach ( $runners as $key => $values ) {
			$items = ( new Connection() )->run(
				'https://api.theracingapi.com/v1/horses/' . $values['id'] . '/results?region=gb&region=ire'
			);

			if ( $items ) {
				$i = 0;

				foreach ( $items as $item ) {
					$details[ $i ]['race_name'] = $item['race_name'];
					$details[ $i ]['course_id'] = $item['course_id'];
					$details[ $i ]['date']      = $item['date'];
					$details[ $i ]['off']       = $item['off'];
					$details[ $i ]['type']      = $item['type'];
					$details[ $i ]['class']     = $item['class'];
					$details[ $i ]['distance']  = $item['distance'];
					$details[ $i ]['going']     = $item['going'];

					$participants = $item['runners'];

					foreach ( $participants as $participant ) {
						if ( $participants['position'] === '1' ) {
							$details[ $i ]['horse_id']   = $participant['horse_id'];
							$details[ $i ]['horse_name'] = $participants['horse'];
						}
					}

					$i++;
				}
			}
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