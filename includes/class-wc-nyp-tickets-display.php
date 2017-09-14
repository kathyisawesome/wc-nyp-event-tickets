<?php
/**
 * Functions related to front-end display
 *
 * @class 		WC_NYP_Tickets_Display
 * @package		WooCommerce Name Your Price Event Tickets
 * @author		Kathy Darling
 * @since		1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_NYP_Tickets_Display {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'tribe_get_cost', array( $this, 'nyp_event_cost' ), 10, 3 );
		add_filter( 'tribe_events_tickets_woo_cart_class', array( $this, 'add_form_class' ) );
		add_filter( 'tribe_events_tickets_row_class', array( $this, 'add_row_class' ) );
		add_filter( 'tribe_events_wootickets_ticket_price_html', array( $this, 'nyp_ticket_price' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_nyp_scripts' ), 20 );

	}

	/*-----------------------------------------------------------------------------------*/
	/* Single Event Display Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Hide the Event cost if any tickets are NYP
	 *
	 * @param str $cost
	 * @param id	$post_id // the event ID
	 * @param bool	$with_currency_symbol
	 * @return str
	 * @since  1.0.0
	 */
	public function nyp_event_cost( $cost, $post_id, $with_currency_symbol ){
		if( class_exists( 'Tribe__Tickets_Plus__Commerce__WooCommerce__Main' ) ){
			// get instance of Woo Tickets class
			$wootickets = Tribe__Tickets_Plus__Commerce__WooCommerce__Main::get_instance();
			
			// get all tickets for event
			$tickets = $wootickets->get_tickets_ids( $post_id );
			
			// if any NYP tickets, create new price strings
			if( $tickets ){
				
				$all_nyp = true;
				$has_nyp = false;
				$min_price = null;
			
				foreach ( $tickets as $ticket ){
					if ( WC_Name_Your_Price_Helpers::is_nyp( $ticket ) ){
						$has_nyp = true;
						$ticket_price = WC_Name_Your_Price_Helpers::get_minimum_price( $ticket );
					} else {
						$all_nyp = false;
						$product = wc_get_product( $ticket );
						$ticket_price = $product->get_price();
					}
				}
			
				if( $all_nyp ){
					$cost = apply_filters( 'wc_nyp_events_all_nyp_tickets_cost_string', '', $post_id );
				} elseif ( $has_nyp ) {
					$cost = apply_filters( 'wc_nyp_events_has_nyp_tickets_cost_string', __( 'See tickets below for pricing', 'wc-nyp-tickets' ), $post_id );
				}
			}
		}
		
		return $cost;
	}

	/**
	 * Adds cart_group class to cart form, causes NYP to look for nested cart classes
	 *
	 * @param array $class
	 * @return array
	 * @since  1.0.0
	 */
	public function add_form_class( $classes ) {
		$classes[] = 'cart_group';
		return $classes;
	}

	/**
	 * Adds cart class to input wrapper <tr>
	 *
	 * @param array $classes
	 * @return array
	 * @since  1.1.0
	 */
	public function add_row_class( $classes ) {
		$classes[] = 'cart';
		return $classes;
	}

	/**
	 * Change the display price of paid tickets OR display price inputs
	 *
	 * @param obj $ticket
	 * @param obj $product
	 * @return void
	 * @since  1.0.3
	 */
	public function nyp_ticket_price( $price_html, $product, $attendee ){
		if( tribe_is_event() && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			if( isset( $attendee['order_id'] ) && isset( $attendee['order_item_id'] ) ){
				$order = wc_get_order( $attendee['order_id'] );
				$order_items = $order->get_items();
				$order_item_id = $attendee['order_item_id'];
				if( isset( $order_items[$order_item_id] ) ){
					$line_item = $order_items[$order_item_id];
					$price_html = $order->get_formatted_line_subtotal( $line_item );
				}
			} else {
				ob_start();
				WC_Name_Your_Price()->display->display_price_input( $product, '-ticket-' . $product->get_id() );
				$input_html = ob_get_contents();
				ob_get_clean();
				$price_html .= $input_html;
			}
		}
		return $price_html;
	}

	/**
	 * Load NYP scripts for front-end validation
	 *
	 * @return void
	 * @since  1.1.0
	 */
	public function load_nyp_scripts(){
		if( function_exists( 'tribe_is_event' ) && tribe_is_event() ) {
			WC_Name_Your_Price()->display->nyp_scripts();
		}
	}

} //end class
