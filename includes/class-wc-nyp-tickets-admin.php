<?php
/**
 * WooCommerce Name Your Price Event Tickets Admin Main Class
 *
 * Adds a setting tab and product meta.
 *
 * @class 		WC_NYP_Tickets_Admin
 * @package		WooCommerce Name Your Price Event Tickets
 * @author		Kathy Darling
 * @since		1.0.0
 */
class WC_NYP_Tickets_Admin {

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 0.1.0
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'meta_box_script' ) );
		add_action( 'tribe_events_tickets_metabox_advanced', array( __CLASS__, 'do_metabox_advanced_options' ), 10, 2 );
		add_action( 'wootickets_after_save_ticket', array( __CLASS__, 'save_nyp_data' ), 10, 3 );
		add_filter( 'tribe_events_tickets_ajax_ticket_edit', array( __CLASS__, 'edit_nyp_data' ), 10, 2 );
	}

    /*-----------------------------------------------------------------------------------*/
	/* Write Panel / metabox */
	/*-----------------------------------------------------------------------------------*/

	
	/**
	 * Add the extra options in the admin's new/edit ticket metabox
	 *
	 * @param $event_id
	 * @param $ticket_id
	 *
	 * @return void
	 */
	public static function do_metabox_advanced_options( $event_id, $ticket_id ) {
		
		wp_enqueue_script( 'nyp-event-tickets-admin' ); ?>

		<tr class="ticket_advanced ticket_advanced_Tribe__Tickets_Plus__Commerce__WooCommerce__Main ticket_is_nyp" style="display: table-row;">
			<td>
				<label for="ticket_is_nyp"><?php _e( 'Name Your Price', 'wc-nyp-tickets' );?></label>
			</td>
			<td>
				<label><input type="checkbox" id="ticket_is_nyp" name="ticket_is_nyp" value="yes" class="ticket_field">
				<?php _e( 'Customers are allowed to determine their own price.', 'wc-nyp-tickets');?></label>
			</td>
		</tr>
		<tr class="ticket_advanced ticket_advanced_meta ticket_advanced_Tribe__Tickets_Plus__Commerce__WooCommerce__Main suggested_ticket_price show_if_nyp hide">
			<td>
				<label for="suggested_ticket_price"><?php _e( 'Suggested Price:', 'wc-nyp-tickets' );?></label>
			</td>
			<td>
				<input type="text" id="suggested_ticket_price" name="suggested_ticket_price" class="ticket_field" size="7" value="">
				<p class="description"><?php _e( 'Price to pre-fill for customers.  Leave blank to not suggest a price.', 'wc-nyp-tickets' );?></p>
			</td>
		</tr>
		<tr class="ticket_advanced ticket_advanced_meta ticket_advanced_Tribe__Tickets_Plus__Commerce__WooCommerce__Main min_ticket_price show_if_nyp hide">
			<td>
				<label for="min_ticket_price"><?php _e( 'Minimum Price:', 'wc-nyp-tickets' );?></label>
			</td>
			<td>
				<input type="text" id="min_ticket_price" name="min_ticket_price" class="ticket_field" size="7" value="">
				<p class="description"><?php _e( 'Lowest acceptable price for ticket. Leave blank to not enforce a minimum.', 'wc-nyp-tickets' );?></p>
			</td>
		</tr>
		<?php
	}


	/**
	 * Commerce-specific action fired after saving a ticket
	 *
	 * @param int   Ticket ID, aka the product ID
	 * @param int   Event Post ID of post the ticket is tied to
	 * @param array Ticket data
	 */
	public static function save_nyp_data( $ticket_id, $event_id, $raw_data ){

	   	if ( isset( $raw_data['ticket_is_nyp'] ) ) {
			update_post_meta( $ticket_id, '_nyp', 'yes' );
			// removing the sale price removes NYP items from Sale shortcodes
			update_post_meta( $ticket_id, '_sale_price', '' );
			delete_post_meta( $ticket_id, '_has_nyp' );
		} else {
			update_post_meta( $ticket_id, '_nyp', 'no' );
		}

		if ( isset( $raw_data['suggested_ticket_price'] ) ) {
			$suggested = ( trim( $raw_data['suggested_ticket_price'] ) === '' ) ? '' : wc_format_decimal( $raw_data['suggested_ticket_price'] );
			update_post_meta( $ticket_id, '_suggested_price', $suggested );
		}

		if ( isset( $raw_data['min_ticket_price'] ) ) {
			$minimum = ( trim( $raw_data['min_ticket_price'] ) === '' ) ? '' : wc_format_decimal( $raw_data['min_ticket_price'] );
			update_post_meta( $ticket_id, '_min_price', $minimum );
		}
		
	}
	
	/**
	 * Get ticket data for ajax loaded edit form
	 *
	 * @param array   $return
	 * @param array Ticket data
	 */
	public static function edit_nyp_data( $return, $ticket_class ){
		$product_id = isset( $return['ID'] ) ? $return['ID'] : 0;
		
		if( $product_id ){
			$return['ticket_is_nyp'] = WC_Name_Your_Price_Helpers::is_nyp( $product_id ) ? 'yes' : 'no';
			$return['suggested_ticket_price'] = WC_Name_Your_Price_Helpers::get_suggested_price( $product_id );
			$return['min_ticket_price'] = WC_Name_Your_Price_Helpers::get_minimum_price( $product_id );
		}
		return $return;
	}


	/**
	 * Enqueue the tickets metabox JS and CSS
	 * @param string $hook
	 * @return void
	 * @since 0.1.0
	 *
	 * @param $hook
	 */
	public static function meta_box_script( $hook ) {
		wp_register_script( 'nyp-event-tickets-admin', WC_NYP_Tickets::$url . '/assets/js/wc-nyp-tickets-admin.js', array( 'event-tickets' ), WC_NYP_Tickets::VERSION , true );
	}	

}
WC_NYP_Tickets_Admin::init();