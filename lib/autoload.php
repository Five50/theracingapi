<?php
/**
 * Automatically locates and loads files based on their namespaces and file names.
 * Instantiates the Autoloader, and registers it with the standard PHP library.
 *
 * @author     Karl Adams <karladams@getmediawise.com>
 * @copyright  Copyright (c) 2023, GetMediaWise Ltd
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://www.getmediawise.com
 * @package    TheRacingAPI
 * @since      0.1.0
 */

spl_autoload_register( static function ( $file ) {

	$file_path = explode( '\\', $file );

	$file_name = '';

	if ( isset( $file_path[ count( $file_path ) - 1 ] ) ) {

		$file_name       = strtolower( $file_path[ count( $file_path ) - 1 ] );
		$file_name       = str_ireplace( '_', '-', $file_name );
		$file_name_parts = explode( '-', $file_name );
		$index           = $file_name_parts[0];

		if ( 'interface' === $index || 'trait' === $index ) {

			// Remove the 'interface' part.
			unset( $file_name_parts[ $index ] );

			// Rebuild the file name.
			$file_name = implode( '-', $file_name_parts );
			$file_name = $file_name . '.php';

		} else {
			$file_name = "class-$file_name.php";
		}
	}

	$fully_qualified_path = trailingslashit( dirname( __FILE__, 2 ) );

	for ( $i = 1; $i < count( $file_path ) - 1; $i ++ ) {
		$dir                  = strtolower( $file_path[ $i ] );
		$fully_qualified_path .= trailingslashit( $dir );
	}

	$fully_qualified_path .= $file_name;

	if ( stream_resolve_include_path( $fully_qualified_path ) ) {
		include_once $fully_qualified_path;
	}
} );