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
 * Defaults class
 *
 * @since 0.1.0
 *
 * @package TheRacingAPI\App
 */
class Defaults {
	use Trait_Tools;

	/**
	 * Class construct method.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		$this->set_constants();
		$this->set_locale();

		add_action( 'wp_enqueue_scripts', array( $this, 'set_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'set_assets_admin' ) );
	}

	/**
	 * Set locale
	 *
	 * @since  0.1.0
	 *
	 * @access private
	 * @return void
	 */
	private function set_locale(): void {
		add_action(
			'plugins_loads',
			array( __NAMESPACE__ . $this->languages, 'load_plugin_text_domain' )
		);
	}

	/**
	 * Set assets
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_assets(): void {

		wp_enqueue_style(
			$this->constants['name'] . '-style',
			$this->get_plugin_url() . '/assets/css/' . $this->constants['name'] . '.css',
			array(),
			null,
			'all'
		);

		wp_enqueue_script(
			$this->constants['name'] . '-script',
			$this->get_plugin_url() . '/assets/js/' . $this->constants['name'] . '.js',
			array(),
			null,
			true
		);
	}

	/**
	 * Set admin assets
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_assets_admin(): void {
		wp_enqueue_style(
			$this->constants['name'] . '-admin',
			$this->get_plugin_url() . 'assets/css/' . $this->constants['name'] . '-admin.css',
			array(),
			null,
			'all'
		);
	}
}