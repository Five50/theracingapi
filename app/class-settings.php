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

class Settings {
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
	 * Settings constructor.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Add Admin menu
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			esc_html__( 'The Racing API', 'theracing-api' ),
			esc_html__('The Racing API', 'theracing-api' ),
			'manage_options',
			'the_racing_api',
			array( $this, 'options_page' )
		);
	}

	/**
	 * Get Big racecards
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 * @throws \Exception
	 */
	public function get_big_racecards() {
		$url = $this->api_url . 'racecards/big-races';
		$url .= '?region_codes=gb';

		$racecards = ( new Connection )->run( $url );
		$racecards = json_decode( json_encode( $racecards['racecards'] ), true );

		$races = array();
		$items = array();

		foreach ( $racecards as $race ) {
			$races[] = array(
				'race_name' => $race['race_name'],
				'course'    => $race['course'],
				'off_time'  => $race['off_time'],
				'date'      => $race['date']
			);
		}

		foreach( $races as $race ) {

			$date = new \DateTime( $race['date'] );
			$date = $date->format( 'd/m/Y' );

			$items[] = sprintf(
				'<tr>%s %s %s %s %s</tr>',
				sprintf('<td>%s</td>', $race['race_name'] ),
				sprintf('<td>%s</td>', $race['course'], ),
				sprintf('<td>%s</td>', $race['off_time'] ),
				sprintf('<td>%s</td>', $date ),
				sprintf(
					'<td><code>[theracing-api-big-racecards course="%s" off_time="%s" start_date="%s" end_date="%s"]</code></td>',
					$race['course'],
                    $race['off_time'],
					$race['date'],
					$race['date']
				)
			);
		}

		printf(
			'<table class="wp-list-table widefat striped" style="max-width:90rem">
				<thead>
					<tr>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
					</tr>
				</thead>
				<tbody>%s</tbody>
			</table>',
			esc_html__( 'Race', 'theracing-api' ),
			esc_html__( 'Course', 'theracing-api' ),
			esc_html__( 'Off Time', 'theracing-api' ),
			esc_html__( 'Date', 'theracing-api' ),
			esc_html__( 'Shortcode', 'theracing-api' ),
			implode( '', $items )
		);
	}

	public function get_affiliate_vendors() {
		$list = $this->get_affiliate_list();

		$items = array();

		foreach( $list as $el ) {
			$items[] = sprintf(
				'<tr><td>%s</td></tr>',
				$el
			);
		}

		printf(
			'<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th>%s</th>
					</tr>
				</thead>
				<tbody>%s</tbody>
			</table>',
			esc_html__( 'Affiliate Name', 'theracing-api' ),
			implode( '', $items )
		);
	}

	/**
	 * Get courses
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $region
	 * @return void
	 */
    public function get_courses( $region ) {
	    $url = $this->api_url . 'courses';
	    $url .= '?region_codes=' . $region;

	    $data = ( new Connection )->run( $url );

        $items = array();

        foreach( $data['courses'] as $course ) {
            $items[] = sprintf(
                '<tr>%s %s</tr>',
                sprintf('<td>%s</td>', $course['course'] ),
                sprintf('<td>%s</td>', $course['id'] )
            );
        }

	    printf(
		    '<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th>%s</th>
						<th>%s</th>
					</tr>
				</thead>
				<tbody>%s</tbody>
			</table>',
		    esc_html__( 'Course', 'theracing-api' ),
		    esc_html__( 'ID', 'theracing-api' ),
		    implode( '', $items )
	    );
    }

	/**
	 * Options page
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 * @throws \Exception
	 */
	public function options_page() {
		?>
		<div class="theracing-api-admin">
			<form action='options.php' method='post'>
				<header class="theracing-api-admin__header">
					<h1><?php esc_html_e( 'The Racing API', 'theracing-api' ); ?></h1>
				</header>
				<nav class="theracing-api-admin__menu">
					<a href="#results"><?php esc_html_e( 'Results', 'theracing-api' ); ?></a>
					<a href="#racecards"><?php esc_html_e( 'Racecards', 'theracing-api' ); ?></a>
					<a href="#racecards-big-races"><?php esc_html_e( 'Racecards Big Races', 'theracing-api' ); ?></a>
				</nav>

				<div class="theracing-api-admin-content">
					<div class="theracing-api-admin-content__body">
						<div class="theracing-api-admin-section" id="intro">
							<h2><?php esc_html_e( 'Introduction', 'theracing-api' ); ?></h2>
							<p>
								<?php
									printf(
										'%s %s %s',
										esc_html__( 'This plugin uses the', 'theracing-api' ),
										sprintf(
											'<a href="%s" target="_blank">%s</a>',
											'https://www.theracingapi.com',
											esc_html__('theracingapi.com', 'theracing-api' )
										),
										esc_html__( 'A High performance API for horse racing statistical modelling, application development and web content. The API holds 10 years of global horse racing data.', 'theracing-api' )
									);
								?>
							</p>
							<p>
								<?php esc_html_e( 'Within this plugin you can display various data forms including racecards, results and big racecards. There are several options in relation to the formatting of these outputs including accordion and odds tables.', 'theracing-api' ); ?>
							</p>
						</div>

						<div class="theracing-api-admin-section" id="results">
							<h2><?php esc_html_e( 'Results', 'theracing-api' ); ?></h2>
							<p>
								<?php esc_html_e( 'In order to show result cards use the following shortcode.', 'theracing-api' ); ?>
							</p>
							<?php
							printf(
								'<code>[theracing-api-results]</code>',
							);
							?>
							<p>
								<?php esc_html_e( 'You can display historic results, up to 12 months in the past. However, by default the maximum number of races shown is 50. Therefore, In order to fetch the items you want. You will need to add parameters to shortcode in order to refine the output. The following parameters are available:', 'theracing-api' ); ?>
							</p>
							<ol>
								<li>course (default: null)</li>
								<li>course_id (default: null)</li>
								<li>start_date (default: null)</li>
								<li>end_date: (default: null)</li>
								<li>details (default: true)</li>
								<li>accordion (default: false)</li>
							</ol>

							<table class="wp-list-table widefat striped">
								<thead>
									<tr>
										<th>Name</th>
										<th>Parameter</th>
										<th>Notes</th>
										<th>Shortcode</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Course</td>
										<td><code>course</code></td>
										<td style="max-width:25rem">The course name should be defined in the same format as in the courses table. In order for this to work successfully the course needs to be available within the date ranges set within start_date and end_date. If your using the default format with no date ranges set it will default to 50 results and the course needs to be within those 50 results in order for the filter to work</td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results course="Aintree"]</code>',
											);
											?>
										</td>
									</tr>
									<tr>
										<td>Course ID</td>
										<td><code>course_id</code></td>
										<td>The course id should be defined in the same format as in the courses table. The id will find the first 50 results in the last year.</td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results course_id="crs_2184" start_date="2022-12-01" end_date="2022-12-31"]</code>',
											);
											?>
										</td>
									</tr>
									<tr>
										<td>Start Date</td>
										<td><code>start_date</code></td>
										<td>The start date should always be further in the past than the end date. Both dates need to be defined for the range to work correctly.<br/><br/><strong>Please Note:</strong> The format of the date should be YYYY-MM-DD. </td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results start_date="2022-12-01" end_date="2022-12-31"]</code>',
											);
											?>
										</td>
									</tr>
									<tr>
										<td>End Date</td>
										<td><code>end_date</code></td>
										<td>The end date should always be further in the future than the start date. Both dates need to be defined for the range to work correctly.<br/><br/><strong>Please Note:</strong> The format of the date should be YYYY-MM-DD. </td></td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results start_date="2022-12-01" end_date="2022-12-31"]</code>',
											);
											?>
										</td>
									</tr>
									<tr>
										<td>Details</td>
										<td><code>details</code></td>
										<td>The details option is either true or false. If set to true it will show details about the race including: runners, race class, race band and race distance</td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results details="false"]</code>',
											);
											?>
										</td>
									</tr>
									<tr>
										<td>Accordion</td>
										<td><code>accordion</code></td>
										<td>The accordion option hides results tables in order to show a large list. If set to true the accordion will display if false it will show the normal results card formatting.</td>
										<td>
											<?php
											printf(
												'<code>[theracing-api-results course_id="crs_2184" start_date="2022-12-01" end_date="2022-12-31" accordion="true"]</code>',
											);
											?>
										</td>
									</tr>
								</tbody>
							</table>
							<p>
								<strong> <?php esc_html_e('Please Note:', 'theracing-api'); ?></strong> <?php esc_html_e('When using start_date and end_date both values need to be within the shortcode for it to work correctly', 'theracing-api'); ?>
							</p>
						</div>

						<div class="theracing-api-admin-section" id="racecards">
							<h2><?php esc_html_e( 'Racecards', 'theracing-api' ); ?></h2>
							<p>
								<?php esc_html_e( 'The Racing API will return racecards for today by default.', 'theracing-api' ); ?>
							</p>
							<?php
							printf(
								'<code>[theracing-api-racecards]</code>',
							);
							?>
							<p>
								<?php esc_html_e( 'The following parameters are available:', 'theracing-api' ); ?>
							</p>
							<ol>
								<li>course (default: null)</li>
								<li>day (default: today)</li>
								<li>date (default: null)</li>
								<li>type (default: pro)</li>
								<li>details (default: true)</li>
								<li>odds (default: hide)</li>
							</ol>
							<table class="wp-list-table widefat striped">
								<thead>
								<tr>
									<th>Name</th>
									<th>Parameter</th>
									<th>Notes</th>
									<th>Shortcode</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>Course</td>
									<td><code>course</code></td>
									<td style="max-width:25rem">The course name should be defined in the same format as in the courses table. In order for this to work successfully the course needs to be available within the date ranges set within start_date and end_date. If your using the default format with no date ranges set it will default to 50 results and the course needs to be within those 50 results in order for the filter to work</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards course="Epsom"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Day</td>
									<td><code>day</code></td>
									<td>Parameter only takes two values today and tomorrow. If type is set to big-races, beta or pro, This parameter has no effect</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards day="today"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Type</td>
									<td><code>type</code></td>
									<td>The type parameter shows various forms of the racecard. Current options: free, standard, pro, big-races</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards type="%s"]</code>',
											'standard'
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Date</td>
									<td><code>date</code></td>
									<td>The date parameter is only available when type is set to pro or beta.<br/><br/><strong>Please Note:</strong> The format of the date should be YYYY-MM-DD.</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards date="%s"]</code>',
											date('Y-m-d', strtotime('+7 days'))
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Race Details</td>
									<td><code>race_details</code></td>
									<td>The race details option is either true or false. If enabled it will display a box of race information before tabular data</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards race_details="false"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Details</td>
									<td><code>details</code></td>
									<td>
										The details option is either true or false. If set to true it will show details about the race including: runners, race class, race band and race distance please see the <a href="#vendors">Vendors List</a> for more information.
									</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards details="false"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Affiliates</td>
									<td><code>affiliates</code></td>
									<td>Various affiliates are available within the racing api. To select various options use the name of the vendor. Each vendor should be separated by a pipe (|).</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards affiliates="Bet365|Sky Bet|Paddy Power|William Hill"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Odds</td>
									<td><code>odds</code></td>
									<td>The odds option shows racing odds if enabled. Show to enable and hide to disable</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-racecards odds="show"]</code>',
										);
										?>
									</td>
								</tr>
								</tbody>
							</table>
						</div>

						<div class="theracing-api-admin-section" id="racecards-big-races">
							<h2><?php esc_html_e( 'Racecards Big Races', 'theracing-api' ); ?></h2>
							<?php
							printf(
								'<code>[theracing-api-big-racecards]</code>',
							);
							?>
							<p>
								<?php esc_html_e( 'The Racing API will return all big racecards. By default the shortcode will show all racecards for big races. You can limit the values in several ways', 'theracing-api' ); ?>
							</p>
							<ol>
								<li><?php esc_html_e( 'course (default: null)', 'theracing-api' ); ?></li>
								<li><?php esc_html_e( 'start_date (default: null)', 'theracing-api' ); ?></li>
								<li><?php esc_html_e( 'end_date (default: null)', 'theracing-api' ); ?></li>
								<li><?php esc_html_e( 'off_time (default: null)', 'theracing-api' ); ?></li>
								<li><?php esc_html_e( 'details (default: true)', 'theracing-api' ); ?></li>
								<li><?php esc_html_e( 'odds (default: hide)', 'theracing-api' ); ?></li>
							</ol>
							<table class="wp-list-table widefat striped">
								<thead>
								<tr>
									<th>Name</th>
									<th>Parameter</th>
									<th>Notes</th>
									<th>Shortcode</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>Course</td>
									<td><code>course</code></td>
									<td style="max-width:25rem">The course name should be defined in the same format as in the courses table. In order for this to work successfully the course needs to be available within the date ranges set within start_date and end_date. If your using the default format with no date ranges set it will default to 50 results and the course needs to be within those 50 results in order for the filter to work</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards course="Epsom"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Start Date</td>
									<td><code>start_date</code></td>
									<td>The start date should always be further in the past than the end date. Both dates need to be defined for the range to work correctly.<br/><br/><strong>Please Note:</strong> The format of the date should be YYYY-MM-DD. </td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards start_date="%s" end_date="%s"]</code>',
											date('Y-m-d'),
											date('Y-m-d', strtotime('+6 months'))
										);
										?>
									</td>
								</tr>
								<tr>
									<td>End Date</td>
									<td><code>end_date</code></td>
									<td>The end date should always be further in the future than the start date. Both dates need to be defined for the range to work correctly.<br/><br/><strong>Please Note:</strong> The format of the date should be YYYY-MM-DD. </td></td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards start_date="%s" end_date="%s"]</code>',
											date('Y-m-d'),
											date('Y-m-d', strtotime('+6 months'))
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Off time</td>
									<td><code>off_time</code></td>
									<td>Off time should be in the format H:MM</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards off_time="3:15"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Details</td>
									<td><code>details</code></td>
									<td>The details option is either true or false. If set to true it will show details about the race including: runners, race class, race band and race distance</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards details="false"]</code>',
										);
										?>
									</td>
								</tr>
								<tr>
									<td>Odds</td>
									<td><code>odds</code></td>
									<td>The odds option shows racing odds if enabled. Show to enable and hide to disable</td>
									<td>
										<?php
										printf(
											'<code>[theracing-api-big-racecards odds="show"]</code>',
										);
										?>
									</td>
								</tr>
								</tbody>
							</table>
							<p>
								<?php esc_html_e( 'The following table shows the settings required to display big races within the page', 'theracing-api' ); ?>
							</p>
							<?php
							$this->get_big_racecards();
							?>
						</div>
					</div>
					<aside class="theracing-api-admin-content__side">

						<h2><?php esc_html_e( 'Courses', 'theracing-api' ); ?></h2>

						<h3><?php esc_html_e('United Kingdom', 'theracing-api' ); ?></h3>
						<?php $this->get_courses( 'gb' ); ?>

						<h3><?php esc_html_e('Ireland', 'theracing-api' ); ?></h3>
						<?php $this->get_courses( 'ire' ); ?>
						<h2><?php esc_html_e( 'Affiliates', 'theracing-api' ); ?></h2>

						<h3 id="vendors"><?php esc_html_e('Affiliates options', 'theracing-api' ); ?></h3>
						<?php $this->get_affiliate_vendors(); ?>
					</aside>
				</div>
			</form>
		</div>
		<?php
	}
}