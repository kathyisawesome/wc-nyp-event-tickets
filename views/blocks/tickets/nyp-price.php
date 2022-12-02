<?php
/**
 * Block: Tickets
 * Extra column, price
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/wc-nyp-event-tickets/blocks/tickets/nyp-price.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @version 2.0.0
 */

$classes = [ 'tribe-common-b2', 'tribe-common-b1--min-medium', 'tribe-tickets__item__extra__price' ];
/** @var Tribe__Tickets__Ticket_Object $ticket */
$ticket     = $this->get( 'ticket' );
$has_suffix = ! empty( $ticket->price_suffix );

/** @var Tribe__Tickets__Tickets $provider */
$provider = $this->get( 'provider' );
$provider_class = $provider->class_name;

?>
<div <?php tribe_classes( $classes ); ?>>

	<?php echo WC_Name_Your_Price()->display->display_suggested_price( $ticket->ID ); ?>

	<span class="tribe-common-b2">
		<?php echo WC_Name_Your_Price()->display->display_price_input( $ticket->ID, '-ticket-' . $ticket->ID ); ?>
	</span>

</div>
