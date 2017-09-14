jQuery( document ).ready( function($) {

	$.fn.wc_nyp_tickets_admin = function() {

		var $tribe_tickets = $(this);
    
	    // Fill fields with values on edit.
		$tribe_tickets.on( 'edit-ticket.tribe', function( event, response ){ console.log('this trigger works');
		    
		    if( typeof response != 'undefined' && response.hasOwnProperty( 'data' ) && response.data.hasOwnProperty( 'ticket_is_nyp' ) ){
		        
		        var $target = $( event.currentTarget );
		    
	            // Some nyp fields
		        var $ticket_is_nyp = $tribe_tickets.find( '#ticket_is_nyp' );
		        var $suggested_ticket_price = $tribe_tickets.find( '#suggested_ticket_price' );
		        var $min_ticket_price = $tribe_tickets.find( '#min_ticket_price' );
		        
	            // Maybe check the NYP checkbox
		        if( response.data.ticket_is_nyp == 'yes' ){
		            $ticket_is_nyp.prop( 'checked', true );
		        } else {
		            $ticket_is_nyp.prop( 'checked', false );
		        }
		        
		        $ticket_is_nyp.change();
		         
		        // Suggested price input.
		        if( response.data.hasOwnProperty( 'suggested_ticket_price' ) ){
		            $suggested_ticket_price.val( response.data.suggested_ticket_price );
		        }
		        
		        // Min price input.
		        if( response.data.hasOwnProperty( 'min_ticket_price' ) ){
		            $min_ticket_price.val( response.data.min_ticket_price );
		        }
		        
		    }
		    
		});
	
		// Hide show the NYP/price fields depending on NYP checkbox.
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

		// Hide suggested and min price fields on "new" ticket.
		$('body').on( 'set-advanced-fields.tribe', function( event ){ 
			$(this).find('#ticket_is_nyp').change();
		});

	} // End wc_nyp_tickets_admin().

	// Launch it.
	$( '#tribetickets' ).wc_nyp_tickets_admin();


});