<?php
/**
 * Helper functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mcelb_has_setting
 *
 * Alias of mcelb()->has_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(boolean)
 */
function mcelb_has_setting( $name = '' ) {

	// return
	return mcelb()->has_setting( $name );

}

/**
 * mcelb_get_setting
 *
 * This function will return a value from the settings array found in the mcelb object
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(mixed)
 */
function mcelb_get_setting( $name, $default = null ) {

	// vars
	$settings = mcelb()->settings;

	// find setting
	$setting = mcelb_maybe_get( $settings, $name, $default );

	// filter for 3rd party
	$setting = apply_filters( "mcelb/settings/{$name}", $setting );

	// return
	return $setting;

}

/**
 * mcelb_update_setting
 *
 * Alias of mcelb()->update_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @param		$value (mixed)
 * @return		N/A
 */
function mcelb_update_setting( $name, $value ) {

	// return
	return mcelb()->update_setting( $name, $value );

}

/**
 * mcelb_get_path
 *
 * This function will return the path to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) The relative path from the root of the plugin folder
 * @return		(string)
 */
function mcelb_get_path( $path = '' ) {

	// return
	return MCELB_PATH . $path;

}

/**
 * mcelb_get_url
 *
 * This function will return the url to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) The relative path from the root of the plugin folder
 * @return		(string)
 */
function mcelb_get_url( $path = '' ) {

	// define mcelb_URL to optimize performance
	mcelb()->define( 'MCELB_URL', mcelb_get_setting( 'url' ) );

	// return
	return MCELB_URL . $path;

}

/**
 * mcelb_include
 *
 * This function will include a file
 *
 * @since		1.0.0
 * @param		$file (string) The file name to be included
 * @return		N/A
 */
function mcelb_include( $file ) {

	$path = mcelb_get_path( $file );

	if ( file_exists( $path ) ) {
		include_once( $path );
	}

}

/**
 * mcelb_maybe_get
 *
 * This function will return a variable if it exists in an array
 *
 * @since		1.0.0
 * @param		$array (array) The array to look within
 * @param		$key (key) The array key to look for
 * @param		$default (mixed) The value returned if not found
 * @return		(mixed)
 */
function mcelb_maybe_get( $array = array(), $key = 0, $default = null ) {

	// return
	return isset( $array[ $key ] ) ? $array[ $key ] : $default;

}

/**
 * mcelb_get_locale
 *
 * This function is a wrapper for the get_locale() function
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(string)
 */
function mcelb_get_locale() {

	// return
	return is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

}