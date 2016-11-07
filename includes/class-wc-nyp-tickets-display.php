<?php
/**
 * Functions related to front-end display
 *
 * @class 	WC_NYP_Tickets_Display
 * @version 0.1.0
 * @since   0.1.0
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
		add_filter( 'tribe_events_tickets_woo_cart_column_class', array( $this, 'add_column_class' ) );
		add_action( 'wootickets_tickets_after_ticket_price', array( $this, 'add_nyp_inputs' ), 10, 2 );	
		add_filter( 'wootickets_ticket_price_html', array( $this, 'nyp_ticket_price' ), 10, 3 );

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
	 *
	 * @return str
	 */
	public function nyp_event_cost( $cost, $post_id, $with_currency_symbol ){
		if( class_exists( 'Tribe__Tickets_Plus__Commerce__WooCommerce__Main' ) ){
			// get instance of Woo Tickets class
			$wootickets = $wootickets = Tribe__Tickets_Plus__Commerce__WooCommerce__Main::get_instance();
			
			// get all tickets for event
			$tickets = $wootickets->get_tickets_ids( $post_id );
			
			$all_nyp = true;
			$has_nyp = false;
			$min_price = null;
			
			// if any NYP tickets, create new price strings
			if( $tickets ){
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
				
				// Find min price
				if ( is_null( $min_price ) || $ticket_price < $min_price ) {
					$min_price    = $ticket_price;
				}
				
			}
			
			if( $all_nyp ){
				if ( $with_currency_symbol ) {
					$cost_utils = Tribe__Events__Cost_Utils::instance();
					
					if ( '0' === (string) $min_price ) {
						$cost = $cost_utils->maybe_replace_cost_with_free( $min_price );
					} elseif ( $with_currency_symbol ) {
						$cost = $cost_utils->maybe_format_with_currency( $min_price );
						
					}
		
					$cost = esc_html( $cost );
			
				}
				$cost = sprintf( __( 'From: %s' ), $cost );
			} elseif ( $has_nyp ) {
				$cost = __( 'See tickets below for pricing', 'wc-nyp-tickets' );
			}
		}
		
		return $cost;
	}

	/**
	 * Adds cart_group class to cart form, causes NYP to look for nested cart classes
	 *
	 * @param array $class
	 *
	 * @return array
	 */
	public function add_form_class( $classes ) {
		$classes[] = 'cart_group';
		return $classes;
	}

	/**
	 * Adds cart class to input wrapper <td>
	 *
	 * @param array $class
	 *
	 * @return array
	 */
	public function add_column_class( $classes ) {
		$classes[] = 'cart';
		return $classes;
	}

	/**
	 * Adds the NYP input
	 *
	 * @param obj $ticket
	 * @param obj $product
	 *
	 * @return void
	 */
	public function add_nyp_inputs( $ticket, $product ){
		if( WC_Name_Your_Price_Helpers::is_nyp( $product ) ){
			WC_Name_Your_Price()->display->display_price_input( $product, '-ticket-' . $product->id );
		}
	}


	/**
	 * Change the display price of paid tickets
	 *
	 * @param obj $ticket
	 * @param obj $product
	 *
	 * @return void
	 */
	public function nyp_ticket_price( $price_html, $product, $attendee ){
		if( tribe_is_event() && isset( $attendee['order_id'] ) && isset( $attendee['order_item_id'] ) ){
			$order = wc_get_order( $attendee['order_id'] );
			$order_items = $order->get_items();
			$order_item_id = $attendee['order_item_id'];
			if( isset( $order_items[$order_item_id] ) ){
				$line_item = $order_items[$order_item_id];
				$price_html = $order->get_formatted_line_subtotal( $line_item );
			}
		}
		return $price_html;
	}

} //end class
