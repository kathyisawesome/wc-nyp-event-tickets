<?php
/**
 * WooCommerce Name Your Price Event Tickets cart functions and filters.
 *
 * @class 	WC_NYP_Tickets_Cart
 * @package		WooCommerce Name Your Price Event Tickets
 * @author		Kathy Darling
 * @since		1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_NYP_Tickets_Cart {

	/**
	 * Setup order class
	 */
	public function __construct() {

		// NYP needs a prefix to handle multiple inputs on the same page
		add_filter( 'nyp_field_prefix', array( $this, 'nyp_cart_prefix_for_tickets' ), 9, 2 );

	}


	/**
	 * get the ticket's nyp prefix
	 *
	 * @param str $prefix
	 * @param int	$product_id
	 *
	 * @return str
	 */
	public function nyp_cart_prefix_for_tickets( $prefix, $product_id ) {
		if( isset( $_POST['wootickets_process'] ) && $_POST['wootickets_process'] == 1 ){
			$prefix = '-ticket-' . $product_id;
		}
		return $prefix;
	}

}
