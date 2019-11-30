( function( $ ) {
	
	// Hide show the NYP/price fields depending on NYP checkbox.
	$( document.getElementById( 'event_tickets' ) ).on( 'change', '#ticket_is_nyp', function( event ) {

	    var $ticket_price_div = $( document.getElementById( 'event_tickets' ) ).find( '#ticket_price' ).closest( '.input_block' );
	    
	    if( $(this).is( ':checked' ) ){
	        $ticket_price_div.hide();
	    } else {
	        $ticket_price_div.show();
	    }
	});

	// Hide price fields on "new" ticket. (not working yet)
	$( document.getElementById( 'event_tickets' ) ).find( '#ticket_is_nyp' ).change();

} ) ( jQuery );