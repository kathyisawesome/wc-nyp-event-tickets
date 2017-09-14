<?php
/**
 * Plugin Name: WooCommerce Name Your Price Event Tickets
 * Plugin URI:  http://github.com/helgatheviking/woocommerce-name-your-price-tickets
 * Description: Bridge plugin for adding NYP support to Modern Tribe&#39;s Tickets Plus
 * Version:     1.0.3
 * Author:      Kathy Darling
 * Author URI:  http://www.kathyisawesome.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc_nyp_tickets
 * Domain Path: /languages
 * Requires at least: 4.0.0
 * Tested up to: 4.8.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.0   
 */

/**
 * Copyright: © 2016 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * The Main WC_NYP_Tickets class
 **/
if ( ! class_exists( 'WC_NYP_Tickets' ) ) :

class WC_NYP_Tickets {

	const VERSION = '1.0.3';
	const PREFIX  = 'WC_NYP_Tickets';
	const REQUIRED_WC = '3.0.0';

	/**
	 * @var WC_NYP_Tickets - the single instance of the class
	 * @since 1.0.0
	 */
	protected static $instance = null;            

	/**
	 * Plugin Directory
	 *
	 * @since 1.0.0
	 * @var string $dir
	 */
	public static $dir = '';

	/**
	 * Plugin URL
	 *
	 * @since 1.0.0
	 * @var string $url
	 */
	public static $url = '';


	/**
	 * Main WC_NYP_Tickets Instance
	 *
	 * Ensures only one instance of WC_NYP_Tickets is loaded or can be loaded.
	 *
	 * @static
	 * @see WC_NYP_Tickets()
	 * @return WC_NYP_Tickets - Main instance
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_NYP_Tickets ) ) {

			self::$instance = new WC_NYP_Tickets();

			self::$dir = untrailingslashit( plugin_dir_path(__FILE__) );

			self::$url = untrailingslashit( plugin_dir_url(__FILE__) );

		}
		return self::$instance;
	}


	public function __construct(){

		// Load core files.
		add_action( 'plugins_loaded', array( $this, 'required_files' ), 20 );

		// Load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 20 );

	}


	/*-----------------------------------------------------------------------------------*/
	/* Required Files */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Load Classes
	 *
	 * @return      void
	 * @since       0.1.0
	 */
	public function required_files(){
		// include admin class to handle all backend functions
		if( is_admin() ){
			include_once( 'includes/class-wc-nyp-tickets-admin.php' );
		} else {
			include_once( 'includes/class-wc-nyp-tickets-display.php' );
			include_once( 'includes/class-wc-nyp-tickets-cart.php' );
			$this->display = new WC_NYP_Tickets_Display();
			$this->cart = new WC_NYP_Tickets_Cart();
		}
	}



	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/plugins/wc-nyp-tickets-LOCALE.mo
	 *      - WP_CONTENT_DIR/plugins/woocommerce-name-your-price-event-tickets/languages/wc-nyp-tickets-LOCALE.mo
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-nyp-tickets' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
	}


} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check


/**
 * Returns the main instance of WC_NYP_Tickets to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WC_NYP_Tickets
 */
function WC_NYP_Tickets() {
	return WC_NYP_Tickets::instance();
}

// Launch the whole plugin
add_action( 'wc_name_your_price_loaded', 'WC_NYP_Tickets' );