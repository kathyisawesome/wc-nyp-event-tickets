<?php
/**
 * Plugin Name: WooCommerce Name Your Price Event Tickets
 * Plugin URI:  http://github.com/helgatheviking/woocommerce-name-your-price-tickets
 * Description: Bridge plugin for adding NYP support to Modern Tribe&#39;s Tickets Plus
 * Version: 1.1.0
 * Author:      Kathy Darling
 * Author URI:  http://www.kathyisawesome.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc-nyp-tickets
 * Domain Path: /languages
 * Requires at least: 5.0.0
 * Tested up to: 5.3.0
 * WC requires at least: 3.6.0
 * WC tested up to: 3.8.0   
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

	const VERSION = '1.1.0';
	const PREFIX  = 'WC_NYP_Tickets';
	const REQUIRED_WC = '3.6.0';
	const REQUIRED_NYP = '2.10.0';
	const REQUIRED_TICKETS = '4.10.0';

	/**
	 * @var WC_NYP_Tickets - the single instance of the class
	 * @since 1.0.0
	 */
	protected static $instance = null;            

	/**
	 * Plugin Directory Path
	 *
	 * @since 1.0.0
	 * @var string $plugin_path
	 */
	private $plugin_path = '';

	/**
	 * Plugin URL
	 *
	 * @since 1.0.0
	 * @var string $plugin_url
	 */
	private $plugin_url = '';

	/**
	 * Plugin Display Class
	 *
	 * @since 1.0.0
	 * @var string $display
	 */
	private $display = '';

	/**
	 * Plugin Cart Class
	 *
	 * @since 1.0.0
	 * @var string $cart
	 */
	private $cart = '';


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
		}
		return self::$instance;
	}


	public function __construct(){

		// Load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 20 );

		// Sanity checks.
		if( ! $this->has_min_environment() ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			return false;
		}

		// Load core files.
		add_action( 'plugins_loaded', array( $this, 'required_files' ), 20 );

	}


	/**
	 * Test environement meets min requirements.
	 *
	 * @since  1.1.0
	 */
	public function has_min_environment() {

		$has_min_environment = true;
		$notices = array();

		// WC version sanity check.
		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::REQUIRED_WC, '<' ) ) {
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sWooCommerce plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate WooCommerce.', 'wc-nyp-tickets' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', self::REQUIRED_WC );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		// Name Your Price version sanity check.
		if ( ! function_exists( 'WC_Name_Your_Price' ) || version_compare( WC_Name_Your_Price()->version, self::REQUIRED_NYP, '<' ) ) {
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sWooCommerce Name Your Price plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate WooCommerce Name Your Price.', 'wc-nyp-tickets' ), '<a href="http://woocommerce.com/products/name-your-price/">', '</a>', self::REQUIRED_NYP );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		// Event Tickets version sanity check.
		if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) || version_compare( Tribe__Tickets_Plus__Main::VERSION, self::REQUIRED_TICKETS, '<' ) ) {
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sEvents Tickets Plus plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate WooCommerce.', 'wc-nyp-tickets' ), '<a href="https://theeventscalendar.com/product/wordpress-event-tickets-plus/">', '</a>', self::REQUIRED_TICKETS );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		if( ! empty( $notices ) ) { error_log(json_encode( $notices));
			update_option( 'wc_nyp_tickets_notices', $notices );
		}
		return $has_min_environment;

	}


	/**
	 * Displays a warning message if version check fails.
	 *
	 * @return string
	 */
	public function admin_notices() {

		$notices = get_option( 'wc_nyp_tickets_notices', array() );

		if( ! empty( $notices ) ) {
			foreach( $notices as $notice ) {
				 echo '<div class="error"><p>' . $notice . '</p></div>';
			}
			delete_option( 'wc_nyp_tickets_notices' );
		}
	   
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
			$this->display = include_once( 'includes/class-wc-nyp-tickets-display.php' );
			$this->cart = include_once( 'includes/class-wc-nyp-tickets-cart.php' );
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


	/*-----------------------------------------------------------------------------------*/
	/* Getters */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Get the plugin directory path
	 *
	 * @return str
	 * @since  1.1.0
	 */
	public function get_plugin_path() {
		if( ! $this->plugin_path ) {
			$this->plugin_path = untrailingslashit( plugin_dir_path(__FILE__) );
		}
		return $this->plugin_path;
	}

	/**
	 * Get the plugin url path
	 *
	 * @return str
	 * @since  1.1.0
	 */
	public function get_plugin_url() {
		if( ! $this->plugin_url ) {
			$this->plugin_url = untrailingslashit( plugin_dir_url(__FILE__) );
		}
		return $this->plugin_url;
	}

	/**
	 * Get the display class
	 *
	 * @return mixed obj|null string
	 * @since  1.1.0
	 */
	public function get_display_class() {
		return $this->display;
	}

	/**
	 * Get the cart class
	 *
	 * @return mixed obj|null string
	 * @since  1.1.0
	 */
	public function get_cart_class() {
		return $this->cart;
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
add_action( 'plugins_loaded', 'WC_NYP_Tickets' );