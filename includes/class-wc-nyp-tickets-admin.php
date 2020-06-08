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
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'meta_box_script' ) );
		add_action( 'tribe_events_tickets_metabox_edit_main', array( __CLASS__, 'do_metabox_advanced_options' ), 5, 2 );
		add_action( 'event_tickets_after_save_ticket', array( __CLASS__, 'save_nyp_data' ), 10, 4 );
	}

    /*-----------------------------------------------------------------------------------*/
	/* Write Panel / metabox */
	/*-----------------------------------------------------------------------------------*/

	
	/**
	 * Add the extra options in the admin's new/edit ticket metabox
	 *
	 * @param int $event_id
	 * @param int $ticket_id
	 * @return void
	 * @since 1.0.0
	 */
	public static function do_metabox_advanced_options( $event_id, $ticket_id ) {

		$ticket_product = wc_get_product( $ticket_id );
		
		$is_nyp          = $ticket_product instanceOf WC_Product ? $ticket_product->get_meta( '_nyp', true, 'edit' ) : 'no';
		$suggested_price = $ticket_product instanceOf WC_Product ? $ticket_product->get_meta( '_suggested_price', true, 'edit' ) : '';
		$minimum_price   = $ticket_product instanceOf WC_Product ? $ticket_product->get_meta( '_min_price', true, 'edit' ) : '';

		?>
		
		<fieldset id="ticket_form_nyp" class="main">
			<div class="input_block ticket_is_nyp">
				<label class="ticket_form_label ticket_form_left" for="ticket_is_nyp"><?php esc_html_e( 'Name Your Price', 'wc-nyp-tickets' ); ?></label>
				<input type="checkbox" id="ticket_is_nyp" name="ticket_is_nyp" value="yes" class="ticket_field ticket_form_right" <?php checked( $is_nyp, 'yes' );?> />
				<span class="tribe_soft_note ticket_form_right"
					><?php esc_html_e( 'Customers are allowed to determine their own price.', 'wc-nyp-tickets' ); ?></span>
			</div>
		
			<div class="input_block suggested_ticket_price show_if_nyp hide tribe-dependent"
				data-depends="#ticket_is_nyp"
				data-condition-is-checked>
				<label class="ticket_form_label ticket_form_left" for="suggested_ticket_price"><?php _e( 'Suggested Price:', 'wc-nyp-tickets' );?></label>
				<input type="text" id="suggested_ticket_price" name="suggested_ticket_price" class="ticket_field ticket_form_right" size="7" value="<?php echo esc_attr( $suggested_price ); ?>">
				<span class="tribe_soft_note ticket_form_right"
					><?php esc_html_e( 'Price to pre-fill for customers.  Leave blank to not suggest a price.', 'wc-nyp-tickets' ); ?></span>
			</div>
			<div class="input_block min_ticket_price show_if_nyp hide tribe-dependent"
				data-depends="#ticket_is_nyp"
				data-condition-is-checked>
			
				<label class="ticket_form_label ticket_form_left" for="min_ticket_price"><?php _e( 'Minimum Price:', 'wc-nyp-tickets' );?></label>
				<input type="text" id="min_ticket_price" name="min_ticket_price" class="ticket_field ticket_form_right" size="7" value="<?php echo esc_attr( $minimum_price ); ?>">
				<span class="tribe_soft_note ticket_form_right"
					><?php esc_html_e( 'Lowest acceptable price for ticket. Leave blank to not enforce a minimum.', 'wc-nyp-tickets' ); ?></span>
			</div>
		</fieldset>
		<?php
	}


	/**
	 * Generic action fired after saving a ticket
	 *
	 * @param int                           Post ID of post the ticket is tied to
	 * @param Tribe__Tickets__Ticket_Object Ticket that was just saved
	 * @param array                         Ticket data
	 * @param string                        Commerce engine class
	 */
	public static function save_nyp_data( $post_id, $ticket, $raw_data, $commerce ){

		$product = wc_get_product( $ticket->ID );

		if ( $product instanceOf WC_Product ) {

		   	if ( isset( $raw_data['ticket_is_nyp'] ) ) {
				$product->update_meta_data( '_nyp', 'yes' );
				// removing the sale price removes NYP items from Sale shortcodes
				$product->set_sale_price( '' );
				$product->delete_meta_data( '_has_nyp' );
			} else {
				$product->update_meta_data( '_nyp', 'no' );
			}

			if ( isset( $raw_data['suggested_ticket_price'] ) ) {
				$suggested = ( trim( $raw_data['suggested_ticket_price'] ) === '' ) ? '' : wc_format_decimal( $raw_data['suggested_ticket_price'] );
				$product->update_meta_data( '_suggested_price', $suggested );
			}

			if ( isset( $raw_data['min_ticket_price'] ) ) {
				$minimum = ( trim( $raw_data['min_ticket_price'] ) === '' ) ? '' : wc_format_decimal( $raw_data['min_ticket_price'] );
				$product->update_meta_data( '_min_price', $minimum );
			}

			$product->save();

		}
		
	}
	
	/**
	 * Get ticket data for ajax loaded edit form
	 *
	 * @param array   $return
	 * @param array Ticket data
	 * @return  array	 * 
	 * @since 1.0.0
	 * @deprecated 1.1.0
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
	 * @since 1.0.0
	 */
	public static function meta_box_script( $hook ) {

		$screen       = get_current_screen();
		$screen_id    = is_a( $screen, 'WP_Screen' ) ? $screen->id : '';

		// Meta boxes
		if ( 'tribe_events' === $screen_id ) {

			$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WC_NYP_Tickets::VERSION;

			wp_enqueue_script( 'nyp-event-tickets-admin', WC_NYP_Tickets()->get_plugin_url() . '/assets/js/wc-nyp-tickets-admin' . $suffix . '.js', array( 'event-tickets-plus-meta-admin-js' ), $version, true );
		}

	}	

}
WC_NYP_Tickets_Admin::init();