(function( window, $, undefined ) {
	'use strict';
	
    var $tribe_tickets = $( '#tribetickets' );
    
    // fill fields with values on edit
	$tribe_tickets.on( 'edit-ticket.tribe', function( event, response ){ 
	    
	    if( typeof response != 'undefined' && response.hasOwnProperty( 'data' ) && response.data.hasOwnProperty( 'ticket_is_nyp' ) ){
	        
	        var $target = $( event.currentTarget );
	    
            // some nyp fields
	        var $ticket_is_nyp = $tribe_tickets.find( '#ticket_is_nyp' );
	        var $suggested_ticket_price = $tribe_tickets.find( '#suggested_ticket_price' );
	        var $min_ticket_price = $tribe_tickets.find( '#min_ticket_price' );
	        
            // maybe check the NYP checkbox
	        if( response.data.ticket_is_nyp == 'yes' ){
	            $ticket_is_nyp.prop( 'checked', true );
	        } else {
	            $ticket_is_nyp.prop( 'checked', false );
	        }
	        
	        $ticket_is_nyp.change();
	         
	        // suggested price input
	        if( response.data.hasOwnProperty( 'suggested_ticket_price' ) ){
	            $suggested_ticket_price.val( response.data.suggested_ticket_price );
	        }
	        
	        // min price input
	        if( response.data.hasOwnProperty( 'min_ticket_price' ) ){
	            $min_ticket_price.val( response.data.min_ticket_price );
	        }
	        
	    }
	    
	});
	
	// hide show the NYP/price fields depending on NYP checkbox
	$tribe_tickets.on( 'change', '#ticket_is_nyp', function( event ) {
	    var $ticket_price_tr = $tribe_tickets.find( '#ticket_price' ).closest('tr');
	    if( $(this).is(':checked') ){
	        $('.show_if_nyp').show();
	        $ticket_price_tr.hide();
	    } else {
	        $('.show_if_nyp').hide();
	        $ticket_price_tr.show();
	    }
	});

})( window, jQuery );