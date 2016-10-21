<?php
/**
 * WooCommerce Name Your Price Event Tickets order functions and filters.
 *
 * @class 	WC_NYP_Tickets_Order
 * @version 0.1.0
 * @since   0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_NYP_Tickets_Order {

	/**
	 * Setup order class
	 */
	public function __construct() {

		// Filter price output shown in cart, review-order & order-details templates
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Modify order items to include bundle meta
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

	}


	/**
	 * Find the parent of a bundled item in an order.
	 *
	 * @param  	array    $item
	 * @param  	WC_Order $order
	 * @return 	array $item
	 */
	public function get_bundled_order_item_container( $item, $order ) {

		// find container item
		foreach ( $order->get_items() as $order_item ) {

			$is_parent = isset( $item[ 'mnm_container' ] ) && isset( $order_item[ 'mnm_cart_key' ] ) && $item[ 'mnm_container' ] === $order_item[ 'mnm_cart_key' ];

			if ( $is_parent ) {

				$parent_item = $order_item;

				return $parent_item;
			}
		}

		return false;
	}


	/**
	 * Modify the subtotal of order-items (order-details.php)
	 *
	 * @param  string   $subtotal   the item subtotal
	 * @param  array    $item       the items
	 * @param  WC_Order $order      the order
	 * @return string               modified subtotal string.
	 */
	public function order_item_subtotal( $subtotal, $item, $order ) {
		return sprintf( __( 'Sample subtotal: %s', 'woocommerce-name-your-price-event-tickets' ), $subtotal );
	}


	/**
	 * Add bundle info meta to order items.
	 *
	 * @param  int      $item_id      order item id
	 * @param  array    $cart_item_values   cart item data
	 * @return void
	 */
	public function add_order_item_meta( $item_id, $cart_item_values, $cart_item_key ) {

		// add data to the product
		if ( isset( $cart_item_values[ 'wc-boilerplate-extension-number' ] ) ) {
			wc_add_order_item_meta( $item_id, '_wc-boilerplate-extension-number', $cart_item_values[ 'wc-boilerplate-extension-number' ] );
		}

		if ( isset( $cart_item_values[ 'wc-boilerplate-extension-textbox' ] ) ) {
			wc_add_order_item_meta( $item_id, '_wc-boilerplate-extension-textbox', $cart_item_values[ 'wc-boilerplate-extension-textbox' ] );
		}

	}

}
