<?php
/**
 * Plugin Name: WooCommerce Name Your Price - Event Tickets
 * Plugin URI:  https://github.com/kathyisawesome/wc-nyp-event-tickets
 * Description: Bridge plugin for adding NYP support to Modern Tribe&#39;s Tickets Plus
 * Version: 2.0.2
 * Author:      Kathy Darling
 * Author URI:  http://www.kathyisawesome.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc-nyp-event-tickets
 * Domain Path: /languages
 * Requires at least: 5.0.0
 * Tested up to: 5.8.0
 * WC requires at least: 5.7.0
 * WC tested up to: 7.4.0   
 *
 * GitHub Plugin URI: https://github.com/kathyisawesome/wc-nyp-event-tickets
 * Release Asset: true
 * Primary Branch: trunk
 *
 * Copyright: © 2016 - 2023 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * The Main WC_NYP_Tickets class
 **/
if ( ! class_exists( 'WC_NYP_Tickets' ) ) :

class WC_NYP_Tickets {

	const VERSION = '2.0.2';
	const PREFIX  = 'WC_NYP_Tickets';
	const REQUIRED_WC = '5.0.0';
	const REQUIRED_NYP = '3.0.0';
	const REQUIRED_TICKETS = '5.2.10';

	/**
	 * @var WC_NYP_Tickets - the single instance of the class
	 * @since 1.0.0
	 */
	protected static $instance = null;            

	/**
	 * Directory of the plugin
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin Directory Path
	 *
	 * @since 1.0.0
	 * @var string $plugin_path
	 */
	public $plugin_path = '';

	/**
	 * Plugin URL
	 *
	 * @since 1.0.0
	 * @var string $plugin_url
	 */
	public $plugin_url = '';

	/**
	 * Where in the themes we will look for templates.
	 *
	 * @since 2.0.1
	 *
	 * @var string
	 */
	public $template_namespace = 'wc-nyp-event-tickets';

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


	public function __construct() {

		// Properties needed for Tribe_Template.
		$this->plugin_path = trailingslashit( dirname( __FILE__ ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url() . '/' . $this->plugin_dir;

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', [ __CLASS__, 'declare_hpos_compatibility' ] );

		// Load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 20 );

		// Sanity checks.
		if( ! $this->has_min_environment() ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			return false;
		}

		// Load core files.
		self::required_files();

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
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sWooCommerce plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate WooCommerce.', 'wc-nyp-event-tickets' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', self::REQUIRED_WC );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		// Name Your Price version sanity check.
		if ( ! function_exists( 'WC_Name_Your_Price' ) || version_compare( WC_Name_Your_Price()->version, self::REQUIRED_NYP, '<' ) ) {
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sWooCommerce Name Your Price plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate WooCommerce Name Your Price.', 'wc-nyp-event-tickets' ), '<a href="http://woocommerce.com/products/name-your-price/">', '</a>', self::REQUIRED_NYP );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		// Event Tickets version sanity check.
		if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) || version_compare( Tribe__Tickets_Plus__Main::VERSION, self::REQUIRED_TICKETS, '<' ) ) {
			$notice = sprintf( __( '<strong>Name Your Price Tickets is inactive.</strong> The %sEvents Ticket Plus plugin%s must be active and at least version %s for Name Your Price Tickets to function. Please upgrade or activate Events Tickets Plus.', 'wc-nyp-event-tickets' ), '<a href="https://theeventscalendar.com/product/wordpress-event-tickets-plus/">', '</a>', self::REQUIRED_TICKETS );

			$notices[] = $notice;
			$has_min_environment = false;
		}

		if( ! empty( $notices ) ) {
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
	/* Core Compat */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Declare HPOS (Custom Order tables) compatibility.
	 */
	public static function declare_hpos_compatibility() {

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
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
	public function required_files() {
		// include admin class to handle all backend functions
		if( is_admin() ){
			include_once 'includes/class-wc-nyp-event-tickets-admin.php';
		}
		
		$this->display = include_once 'includes/class-wc-nyp-event-tickets-display.php';
		$this->cart    = include_once 'includes/class-wc-nyp-event-tickets-cart.php';
		
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
		load_plugin_textdomain( 'wc-nyp-event-tickets' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
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
		return $this->plugin_path;
	}

	/**
	 * Get the plugin url path
	 *
	 * @return str
	 * @since  1.1.0
	 */
	public function get_plugin_url() {
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

	/*-----------------------------------------------------------------------------------*/
	/* Helpers */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Helper function to see if Event has any NYP tickets.
	 *
	 * @param id	$event_id The event ID.
	 * @param bool	$force_check Bypass the event meta, force check all the tickets.
	 * @return bool
	 * @since  2.0.0
	 */
	public function event_has_nyp( $event_id, $force_check = false ) {

		$has_nyp = false;

		if ( $force_check ) {

			/** @var \Tribe__Tickets_Plus__Commerce__WooCommerce__Main $woo */
			$woo = tribe( 'tickets-plus.commerce.woo' );
			
			// Get all tickets for event.
			$tickets = $woo->get_tickets_ids( $event_id );
			
			if( $tickets ) {
			
				foreach ( $tickets as $ticket_id ) {
					if ( WC_Name_Your_Price_Helpers::is_nyp( $ticket_id ) ) {
						$has_nyp = true;
						break;
					}
				}

			}

		} else {

			$has_nyp = 'yes' === get_post_meta( $event_id, '_has_nyp', true );

		}

		return $has_nyp;
	}

} // End class: do not remove or there will be no more guacamole for you.

endif; // End class_exists check.


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
add_action( 'plugins_loaded', 'WC_NYP_Tickets', 99 );