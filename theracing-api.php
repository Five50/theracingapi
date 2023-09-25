<?php
/**
 * The Racing API Bootstrap file.
 *
 * This file is used to generate all plugin information. Including all the
 * dependencies used by the plugin, registers the activation and deactivation
 * functions, and defines a function that starts the plugin.
 *
 * @author     Karl Adams <karladams@getmediawise.com>
 * @copyright  Copyright (c) 2023, GetMediaWise Ltd
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://www.getmediawise.com
 * @package    TheRacing API
 * @since      1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       TheRacing API
 * Plugin URI:        https://www.getmediawise.com
 * Description:       WordPress plugin for The Racing API. A top-performing API designed for statistical modeling,
 *                    application development, and web content creation in horse racing.
 * Version:           0.1.0
 * Requires at Least: 6.0
 * Requires PHP:      7.4
 * Author:            Karl Adams <karladams@getmediawise.com>
 * Author URI:        https://www.getmediawise.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain        theracing-api
 */

namespace TheRacingAPI;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

require_once 'lib/autoload.php';

add_action(
	'plugins_loaded',
	function () {
		$defaults = new App\Defaults();

		( new App\Languages() )->run(
			$defaults->get_constant( 'text_domain' )
		);

		new App\Shortcodes();
		new App\Settings();
	}
);