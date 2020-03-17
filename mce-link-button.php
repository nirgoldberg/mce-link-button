<?php
/**
* Plugin Name: MCE Link Button
* Plugin URI: http://www.htmline.com/
* Description: Extends WordPress TinyMCE with a new link button generator
* Version: 1.0.0
* Author: Nir Goldberg
* Author URI: http://www.htmline.com/
* License: GPLv3
* Text Domain: mcelb
* Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MCELB' ) ) :

class MCELB {

	/**
	 * Plugin version
	 *
	 * @var (string)
	 */
	private $version;

	/**
	 * Shortcode tag
	 *
	 * @var (string)
	 */
	private $shortcode_tag;

	/**
	 * __construct
	 *
	 * A dummy constructor to ensure MCELB is only initialized once
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function __construct() {

		$this->version			= '1.0.0';
		$this->shortcode_tag	= 'mcelb';

		/* Do nothing here */

	}

	/**
	 * initialize
	 *
	 * The real constructor to initialize MCELB
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function initialize() {

		// vars
		$basename	= plugin_basename( __FILE__ );
		$path		= plugin_dir_path( __FILE__ );
		$url		= plugin_dir_url( __FILE__ );
		$slug		= dirname( $basename );

		// settings
		$this->settings = array(

			// basic
			'name'				=> __( 'MCE Link Button', 'mcelb' ),
			'version'			=> $this->version,

			// urls
			'basename'			=> $basename,
			'path'				=> $path,		// with trailing slash
			'url'				=> $url,		// with trailing slash
			'slug'				=> $slug,

			// options
			'show_admin'		=> true,
			'capability'		=> 'manage_options',
			'debug'				=> false,

		);

		// constants
		$this->define( 'MCELB',			true );
		$this->define( 'MCELB_VERSION',	$this->version );
		$this->define( 'MCELB_PATH',	$path );

		// helpers
		include_once( MCELB_PATH . 'includes/api/api-helpers.php' );

		// actions
		add_action( 'init', array( $this, 'init' ), 99 );
		add_action( 'init',	array( $this, 'enqueue_scripts' ), 99 );

		// shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_handler' ) );

		// admin
		if ( is_admin() ) {

			// actions
			add_action( 'admin_head',		array( $this, 'admin_head' ) );

		}

		// plugin activation / deactivation
		register_activation_hook	( __FILE__,	array( $this, 'mcelb_activate' ) );
		register_deactivation_hook	( __FILE__,	array( $this, 'mcelb_deactivate' ) );

	}

	/**
	 * init
	 *
	 * This function will run after all plugins and theme functions have been included
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function init() {

		// exit if called too early
		if ( ! did_action( 'plugins_loaded' ) )
			return;

		// exit if already init
		if ( mcelb_get_setting( 'init' ) )
			return;

		// only run once
		mcelb_update_setting( 'init', true );

		// update url - allow another plugin to modify dir
		mcelb_update_setting( 'url', plugin_dir_url( __FILE__ ) );

		// set textdomain
		$this->load_plugin_textdomain();

		// action for 3rd party
		do_action( 'mcelb/init' );

	}

	/**
	 * define
	 *
	 * This function will safely define a constant
	 *
	 * @since		1.0.0
	 * @param		$name (string)
	 * @param		$value (string)
	 * @return		N/A
	 */
	public function define( $name, $value = true ) {

		if ( ! defined( $name ) ) {
			define( $name, $value );
		}

	}

	/**
	 * load_plugin_textdomain
	 *
	 * This function will load the textdomain file
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	private function load_plugin_textdomain() {

		// vars
		$domain = 'mcelb';
		$locale = apply_filters( 'plugin_locale', mcelb_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';

		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );

		// load from plugin lang folder
		load_textdomain( $domain, mcelb_get_path( 'lang/' . $mofile ) );

	}

	/**
	 * enqueue_scripts
	 *
	 * This function will enqueue custom styles
	 *
	 * @param		N/A
	 * @return		N/A
	 */
	function enqueue_scripts() {

		wp_enqueue_style( 'mce-link-button', plugins_url( 'assets/css/mce-link-button.css' , __FILE__ ) );

	}

	/**
	 * shortcode_handler
	 *
	 * This function will load the textdomain file
	 *
	 * @since		1.0.0
	 * @param		$atts (array) Shortcode attributes
	 * @param		$content (string) Shortcode content
	 * @return		(string)
	 */
	function shortcode_handler( $atts, $content = null ) {

		// attributes
		extract( shortcode_atts(
			array(
				'text'		=> 'Download',
				'link'		=> 'https://',
				'target'	=> 'self',
			), $atts)
		);

		$output = '';

		// validate text
		if ( ! $text )
			return $output;

		// validate link target, if not valid revert to default
		$target_list	= array( 'self', 'blank' );
		$target			= in_array( $target, $target_list ) ? $target : 'self';

		// button markup
		$output .= '<p><a class="mcelb button size-small" href="' . $link . '" target="_' . $target . '">' . $text . '</a></p>';

		// return
		return $output;

	}

	/**
	 * admin_head
	 *
	 * This function will register plugin MCE button
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function admin_head() {

		// check user permissions
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
			return;

		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {

			// filters
			add_filter( 'mce_external_plugins',	array( $this ,'mce_external_plugins' ) );
			add_filter( 'mce_buttons',			array( $this, 'mce_buttons' ) );

		}

	}

	/**
	 * mce_external_plugins
	 *
	 * This function will add MCELB tinymce plugin
	 *
	 * @param		$plugin_array (array)
	 * @return		(array)
	 */
	function mce_external_plugins( $plugin_array ) {

		$plugin_array[ $this->shortcode_tag ] = plugins_url( 'assets/js/min/mce-link-button.min.js', __FILE__ );

		// return
		return $plugin_array;

	}

	/**
	 * mce_buttons
	 *
	 * This function will add MCELB tinymce button
	 *
	 * @param		$buttons (array)
	 * @return		(array)
	 */
	function mce_buttons( $buttons ) {

		array_push( $buttons, $this->shortcode_tag );

		// return
		return $buttons;

	}

	/**
	* has_setting
	*
	* This function will return true if has setting
	*
	* @since		1.0.0
	* @param		$name (string)
	* @return		(boolean)
	*/
	public function has_setting( $name ) {

		// return
		return isset( $this->settings[ $name ] );

	}

	/**
	* get_setting
	*
	* This function will return a setting value
	*
	* @since		1.0.0
	* @param		$name (string)
	* @return		(mixed)
	*/
	public function get_setting( $name ) {

		// return
		return isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : null;

	}

	/**
	* update_setting
	*
	* This function will update a setting value
	*
	* @since		1.0.0
	* @param		$name (string)
	* @param		$value (mixed)
	* @return		N/A
	*/
	public function update_setting( $name, $value ) {

		$this->settings[ $name ] = $value;

		// return
		return true;

	}

	/**
	* mcelb_activate
	*
	* Actions perform on activation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function mcelb_activate() {}

	/**
	* mcelb_deactivate
	*
	* Actions perform on deactivation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function mcelb_deactivate() {}

}

/**
* mcelb
*
* The main function responsible for returning the one true mcelb instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mcelb() {

	// globals
	global $mcelb;

	// initialize
	if( ! isset( $mcelb ) ) {

		$mcelb = new MCELB();
		$mcelb->initialize();

	}

	// return
	return $mcelb;

}

// initialize
mcelb();

endif; // class_exists check