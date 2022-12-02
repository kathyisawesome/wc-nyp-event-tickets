( function( $ ) {
	
	// Hide show the NYP/price fields depending on NYP checkbox.
	$( '#tribetickets' ).on( 'change', '#ticket_is_nyp', function() {

	    var $ticket_price_div = $( '#event_tickets' ).find( '#ticket_price' ).closest( '.input_block' );
	    
	    if( $(this).is( ':checked' ) ){
	        $ticket_price_div.hide();
	    } else {
	        $ticket_price_div.show();
	    }
	});

	/* Trigger chance on edit ticket */
	$( '#event_tickets' ).on( 'event-tickets-plus-ticket-meta-initialized.tribe', function() {
		$( '#event_tickets' ).find( '#ticket_is_nyp' ).change();
	} );

} ) ( jQuery );