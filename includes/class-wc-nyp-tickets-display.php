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

		include_once 'class-tribe-tickets-nyp-template.php';
		tribe_singleton( 'tickets-plus.nyp.template', 'Tribe__Tickets__NYP__Template' );

		add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
		add_filter( 'tribe_get_cost', array( $this, 'nyp_event_cost' ), 10, 3 );
		add_filter( 'wc_nyp_data_attributes', array( $this, 'optional_nyp_attributes' ), 10, 2 );
		
		add_filter( 'tribe_template_html:tickets/blocks/tickets/extra-price', array( $this, 'ticket_nyp_html' ), 10, 4 );

		add_action( 'wp_head', array( $this, 'ticket_nyp_css' ) );

	}

	/*-----------------------------------------------------------------------------------*/
	/* Single Event Display Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Add a post class to Events with NYP tickets.
	 *
	 * @since  2.0.0
	 *
	 * @param string[] $classes An array of post class names.
	 * @param string[] $class Additional class names.
	 * @param int	$post_id The event ID.
	 * @return array
	 */
	public function post_class( $classes, $class, $post_id ) {

		if ( WC_NYP_Tickets()->event_has_nyp( $post_id ) ) {
			$classes[] = 'has-nyp-tickets';
		}
				
		return $classes;
	}

	/**
	 * Hide the Event cost if any tickets are NYP
	 *
	 * @since  1.0.0
	 *
	 * @param str $cost
	 * @param id	$post_id // the event ID
	 * @param bool	$with_currency_symbol
	 * @return str
	 */
	public function nyp_event_cost( $cost, $post_id, $with_currency_symbol ) {

		if ( WC_NYP_Tickets()->event_has_nyp( $post_id ) ) {
			$cost = apply_filters( 'wc_nyp_events_has_nyp_tickets_cost_string', __( 'See below for pricing', 'wc-nyp-tickets' ), $post_id );
		}
				
		return $cost;
	}

	/**
	 * Load NYP scripts for front-end validation
	 *
	 * @since  1.1.0
	 */
	public function load_nyp_scripts() {
					
			WC_Name_Your_Price()->display->nyp_scripts();

			wp_add_inline_script( 'woocommerce-nyp', '
				jQuery( function( $ ) {
					$( ".tribe-tickets" ).each(	function() {

						var $ticket_form = $(this);

						if ( "woo" !== $ticket_form.data( "provider-id" ) ) {
							return;
						}

						$ticket_form.addClass( "cart" );

						// Update totals when price is changed.
						$( ".nyp" ).on( "wc-nyp-updated", function( event, nypProduct ) {
							var $ticket = $( this ).closest( ".nyp-product" );
							$ticket.find( ".tribe-tickets__sale_price .tribe-amount" ).html( nypProduct.getPrice() );
							$ticket.find( "input.tribe-tickets-quantity" ).trigger( "change" );
						} );

						// Handle status of optional tickets.
						$ticket_form.find( "input.tribe-tickets-quantity" ).on( "change", function( event) {
							var $nyp = $( this ).closest( ".nyp-product" ).find( ".nyp" );

							if ( $nyp.length ) {
								var selected = $( this ).val() > 0;
								$nyp.data( "optional_status", selected );
							}

						} );

						// Run validation on submit, by switching the add to cart button element.
						$ticket_form.on( "wc-nyp-initializing", function( event, nypForm ) {
							nypForm.$add_to_cart = $ticket_form.find( "#tribe-tickets__buy" );
						} );

						$( this ).wc_nyp_form();
					} );

				} );

			' );
	}


	/**
	 * Mark products as optional.
	 *
	 * @since 2.0.0
	 * 
	 * @param array      $attributes - The data attributes on the NYP div.
	 * @param  WC_Product $product
	 * @return array
	 */
	public function optional_nyp_attributes( $attributes, $product ) {

		if ( tribe_events_product_is_ticket( $product->get_id() ) ) {
			$attributes['optional'] = 'yes';
		}
		return $attributes;
	}


	/**
	 * Mark products as optional.
	 *
	 * @since 2.0.0
	 * @see https://docs.theeventscalendar.com/reference/hooks/tribe_template_htmlhook_name/
	 * 
	 * @param string $html      The final HTML
	 * @param string $file      Complete path to include the PHP File
	 * @param array  $name      Template name
	 * @param self   $template  Current instance of the Tribe__Template
	 * @return string
	 */
	public function ticket_nyp_html( $html, $file, $name, $template ) {
		
		/** @var Tribe__Tickets__Tickets $provider */
		$provider = $template->get( 'provider' );

		if ( 'woo' === $provider->orm_provider ) {

			/** @var Tribe__Tickets__Ticket_Object $ticket */
			$ticket = $template->get( 'ticket' );

			/** @var Tribe__Tickets__NYP__Template $template */
	  		$template = tribe( 'tickets-plus.nyp.template' );

			if ( WC_Name_Your_Price_Helpers::is_nyp( $ticket->ID ) ) {

				$this->load_nyp_scripts();

				$html = $template->template( 'blocks/tickets/nyp-price', [ 'ticket' => $ticket, 'provider' => $provider ], false );
			}

		}

		return $html;
	}


	/**
	 * Mark products as optional.
	 *
	 * @since 2.0.0
	 */
	public function ticket_nyp_css() { ?>
		
		<style>
			@media (min-width: 768px) {
				.has-nyp-tickets .tribe-common .tribe-tickets__item, .has-nyp-tickets.entry .entry-content .tribe-common .tribe-tickets__item {
					-ms-grid-columns: 6.6fr 4fr 2fr;
					grid-template-columns: 6.5fr 3fr 2fr;
					-ms-grid-rows: 1fr 1.5fr 1fr;
				}
			}
			.has-nyp-tickets .tribe-common.tribe-tickets {
				max-width: initial;
			}
			
		</style>

		<?php
	}

	/*-----------------------------------------------------------------------------------*/
	/* Deprecated */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Adds cart_group class to cart form, causes NYP to look for nested cart classes
	 *
	 * @since  1.0.0
	 * @deprecated 2.0.0 Events Tickets no longer has this filter.
	 * 
	 * @param array $class
	 * @return array
	 */
	public function add_form_class( $classes ) {
		$classes[] = 'cart_group';
		return $classes;
	}

	/**
	 * Adds cart class to input wrapper <tr>
	 *
	 * @since  1.0.0
	 * @deprecated 2.0.0 Events Tickets no longer has this filter.
	 * 
	 * @param array $classes
	 * @return array
	 */
	public function add_row_class( $classes ) {
		$classes[] = 'cart';
		return $classes;
	}

	/**
	 * Change the display price of paid tickets OR display price inputs
	 *
	 * @since  1.0.3
	 * @deprecated 2.0.0 Use a custom template.
	 *
	 * @param obj $ticket
	 * @param obj $product
	 * @return void
	 */
	public function nyp_ticket_price( $price_html, $product, $attendee ) {
		if( tribe_is_event() && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			if( isset( $attendee['order_id'] ) && isset( $attendee['order_item_id'] ) ) {
				$order = wc_get_order( $attendee['order_id'] );
				$order_items = $order->get_items();
				$order_item_id = $attendee['order_item_id'];
				if( isset( $order_items[$order_item_id] ) ) {
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

} // End class.

return new WC_NYP_Tickets_Display();
