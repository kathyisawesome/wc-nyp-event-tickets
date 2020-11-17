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

		// NYP needs a suffix to handle multiple inputs on the same page.
		add_filter( 'wc_nyp_field_suffix', array( $this, 'nyp_cart_suffix_for_tickets' ), 9, 2 );

	}


	/**
	 * get the ticket's nyp suffix
	 *
	 * @param str $suffix
	 * @param int	$product_id
	 * @return str
	 * @since  1.0.0
	 */
	public function nyp_cart_suffix_for_tickets( $suffix, $product_id ) {
		if( tribe_events_product_is_ticket( $product_id ) ) {
			$suffix = '-ticket-' . $product_id;
		}
		return $suffix;
	}

}

return new WC_NYP_Tickets_Cart();
