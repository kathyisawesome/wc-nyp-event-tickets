( function( $ ) {
	
	// Hide show the NYP/price fields depending on NYP checkbox.
	$( '#tribetickets' ).on( 'change', '#ticket_is_nyp', function( event ) {

	    var $ticket_price_div = $( '#event_tickets' ).find( '#ticket_price' ).closest( '.input_block' );
	    
	    if( $(this).is( ':checked' ) ){
	        $ticket_price_div.hide();
	    } else {
	        $ticket_price_div.show();
	    }
	});

	$( '#tribetickets' )


	/* Trigger chance on edit ticket */
	$document.on( 'edit-ticket.tribe', function( event ) {}
		$( '#event_tickets' ).find( '#ticket_is_nyp' ).change();
	} );

} ) ( jQuery );