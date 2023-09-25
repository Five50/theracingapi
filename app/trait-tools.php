<?php
/**
 * Connect to the Racing API
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

trait Trait_Tools {

	/**
	 * Plugin accepted values list.
	 *
	 * @since  0.1.0
	 *
	 * @access protected
	 * @static
	 * @var    array Values listed in plugin heading.
	 */
	protected array $accepted_values = array(
		'Name',
		'PluginURI',
		'Description',
		'Version',
		'Author',
		'AuthorURI',
		'TextDomain',
		'DomainPath',
		'Network',
	);

	/**
	 * Plugin data
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var    array Array of plugin elements.
	 */
	public array $constants;

	/**
	 * Plugin Database version.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $db_version = '1.0.0';

	/**
	 * Vendors directory.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $vendors = '\\vendors';

	/**
	 * Languages directory.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $languages = '\\languages';

	/**
	 * Retrieve plugin basename.
	 *
	 * @since   0.1.0
	 *
	 * @access  public
	 * @return  string plugin basename.
	 */
	public function get_plugin_basename(): string {
		return plugin_basename( $this->get_plugin_path() );
	}

	/**
	 * Retrieve path for sub directory.
	 *
	 * @since   0.1.0
	 *
	 * @access  public
	 * @return  string includes constant.
	 */
	public function get_sub_dir_path( string $dir_name ): string {
		return $this->get_plugin_path() . $dir_name;
	}

	/**
	 * Return the database version of the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Return plugin database version number.
	 */
	public function get_plugin_db_version(): string {
		return $this->db_version;
	}

	/**
	 * Retrieve plugin path.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Plugin path.
	 */
	public static function get_plugin_path(): string {
		return dirname( __DIR__ );
	}

	/**
	 * Retrieve plugin url.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Plugin url.
	 */
	public static function get_plugin_url(): string {
		return esc_url( plugin_dir_url( __DIR__ ) );
	}

	/**
	 * Get plugin information from main file.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param string $value Value to get from plugin file data.
	 * @return string       Return plugin information
	 */
	public function get_plugin_info( string $value ): string {

		$data = array(
			'Name'        => 'Plugin Name',
			'PluginURI'   => 'Plugin URI',
			'Description' => 'Description',
			'Version'     => 'Version',
			'Author'      => 'Author',
			'AuthorURI'   => 'Author URI',
			'TextDomain'  => 'Text Domain',
			'DomainPath'  => 'Domain Path',
			'Network'     => 'Network',
		);

		$plugin_info = get_file_data(
			trailingslashit( $this->get_plugin_path() ) . $this->get_plugin_basename() . '.php',
			$data,
			'plugin'
		);

		return $plugin_info[ $value ];
	}

	/**
	 * Returns the version number of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $request Plugin information value request.
	 * @return string          Return plugin version number.
	 */
	public function get_plugin_info_value( string $request ): string {
		if ( in_array( $request, $this->accepted_values, true ) ) {
			return $this->get_plugin_info( $request );
		}

		return '';
	}

	/**
	 * Get constant value
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param  string $request Return plugin value.
	 */
	public function get_constant( string $request ) {
		return $this->constants[ $request ];
	}

	/**
	 * Human time difference
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param int $age Time in seconds.
	 * @return string Human-readable time difference.
	 */
	public function human_time_diff( int $age ): string {
		if ( $age < 60 ) {
			return $age . ' seconds';
		} elseif ( $age < 3600 ) {
			return round( $age / 60 ) . ' minutes';
		} elseif ( $age < 86400 ) {
			return round( $age / 3600 ) . ' hours';
		} elseif ( $age < 604800 ) {
			return round( $age / 86400 ) . ' days';
		} elseif ( $age < 2592000 ) {
			return round( $age / 604800 ) . ' weeks';
		} elseif ( $age < 31536000 ) {
			return round( $age / 2592000 ) . ' months';
		} else {
			return round( $age / 31536000 ) . ' years';
		}
	}

	/**
	 * Convert pounds to stones and pounds,
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param string $lbs
	 * @return string
	 */
	public function set_lbs_to_stones( string $lbs ): string {
		$stones = floor( $lbs / 14 );
		$pounds = $lbs % 14;
		return $stones . 'st ' . $pounds . 'lbs';
	}

	/**
	 * Set date format.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $date
	 * @return string
	 * @throws \Exception
	 */
	public function set_date_format( $date ): string {
		$date = new \DateTime( $date );
		return $date->format( 'l, jS F Y' );
	}

	public function pounds_to_kilograms( string $pounds ): float {
		$pounds = (float) $pounds;
		return round( $pounds * 0.45359237, 2 );
	}

	/**
	 * @throws \Exception
	 */
	public function days_old( $date ): string {
		$given    = new \DateTime( $date );
		$current  = new \DateTime();
		$interval = $given->diff( $current );

		return number_format($interval->days ) . ' days';
	}

	public function get_affiliate_list(): array {
		return array(
			'Bet365', //have
			'Sky Bet', //have
			'Paddy Power', //have
			'William Hill', //have
			'888 Sport', //have
			'Betfair Sportsbook',
			'Bet Victor',
			'Coral',
			'Unibet',
			'Spread Ex',
			'Betfred', // Have
			'Boyle Sports',
			'10 Bet',
			'Star Sports',
			'Bet UK',
			'Sporting Index',
			'Livescore Bet',
			'Quinn Bet',
			'Betway',
			'Ladbrokes',
			'Pari Match',
			'V Bet',
			'Tote',
			'Betfair Exchange',
			'Matchbook'
		);
	}

	/**
	 * Clean Filename
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param string $filename Filename to be cleaned.
	 * @return string Clean filename.
	 */
	public function clean_filename( string $filename ): string {
		$filename = str_replace( ' ', '-', $filename );
		$filename = str_replace( '&amp;', '', $filename );
		$filename = str_replace( '/', '-', $filename );
		$filename = str_replace( ':', '-', $filename );
		$filename = str_replace( '(', '-', $filename );
		$filename = str_replace( ')', '-', $filename );
		$filename = str_replace( ',', '-', $filename );
		$filename = str_replace( '.', '-', $filename );
		$filename = str_replace( '---', '-', $filename );
		$filename = str_replace( '--', '-', $filename );
		$filename = str_replace( '@', '', $filename );
		$filename = str_replace( '#', '', $filename );

		return strtolower( $filename );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 * @return void
	 */
	private function set_constants(): void {

		$name = $this->get_plugin_info_value( 'Name' );
		$name = strtolower( $name );

		$this->constants = array(
			'name'        => str_replace( ' ', '-', $name ),
			'version'     => $this->get_plugin_info_value( 'Version' ),
			'basename'    => $this->get_plugin_basename(),
			'db-version'  => $this->get_plugin_db_version(),
			'dir'         => $this->get_plugin_path(),
			'url'         => $this->get_plugin_url(),
			'uri'         => $this->get_plugin_info_value( 'PluginURI' ),
			'author'      => $this->get_plugin_info_value( 'Author' ),
			'lang'        => $this->get_sub_dir_path( $this->languages ),
			'vendors'     => $this->get_sub_dir_path( $this->vendors ),
			'text_domain' => $this->get_plugin_info_value( 'TextDomain' ),
			'assets'      => trailingslashit( $this->get_plugin_url() . 'assets' ),
			'media'       => trailingslashit( $this->get_plugin_url() . 'media' ),
			'timeout'     => apply_filters( $name . '_timeout', 60 ),
		);
	}

	/**
	 * Litespeed purge
	 *
	 * @param string $url to purge.
	 *
	 * @return void
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function litespeed_purge( string $url ) {
		if ( strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGE");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-LiteSpeed-Purge: 1' ) );
			curl_exec( $ch );
			curl_close( $ch );
		}
	}

	/**
	 * Get refresh value.
	 *
	 * @param string $time Amount of time.
	 *
	 * @return float|int
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function get_refresh( string $time ) {

		$value = (int) substr( $time, 0, - 1 );

		switch ( substr( $time, - 1 ) ) {
			case 's':
				return $value;
			case 'm':
				return $value * 60;
			case 'h':
				return $value * 3600;
			case 'd':
				return $value * 86400;
			case 'w':
				return $value * 604800;
			case 'y':
				return $value * 31536000;
			default:
				return 0;
		}
	}
}