/**
 * Scripts for NYP Events
 * 
 * @since 2.0.0
 * @version 2.0.0
 */
    
jQuery( function( $ ) {

    /**
     * Use event tickets "submit" buttons
     */
     $( document ).on( 'wc-nyp-initializing', function( event, nypForm ) {

        const $ticket_form = nypForm.$el;

        let $add_to_cart = $ticket_form.find( tribe.tickets.block.selectors.submit );

        if ( ! $add_to_cart.length ) {
            $add_to_cart = $ticket_form.find( tribe.tickets.modal.selectors.submit );
        }

        if ( $add_to_cart.length ) {
            nypForm.$add_to_cart = $add_to_cart;
        }
    } );

    /**
     * Handle status of optional tickets.
     */
    $( document ).on( 'afterTicketsQuantityChange.tribeTicketsBlock', function( event, $input ) {

        const $nyp = $input.closest( tribe.tickets.block.selectors.item ).find( '.nyp' );

        if ( $nyp.length ) {
            const selected = $input.val() > 0;
            $nyp.data( 'optional_status', selected );
            $nyp.trigger( 'wc-nyp-update' );
        }

    } );

    /**
     * Initialize Modal form
     */
    $( tribe.dialogs.events ).on( 'tribe_dialog_show_ar_modal', function( e, dialogEl ) {

        const $modal = $( dialogEl );
        const $form = $modal.find( tribe.tickets.modal.selectors.form );
        const $modalCart = $modal.find( tribe.tickets.modal.selectors.cartForm );
        const $tribeTicket = $( document ).find( tribe.tickets.block.selectors.form ).filter( '[data-post-id="' + $form.data( 'postId' ) + '"]' );
        const $cartItems = $tribeTicket.find( tribe.tickets.block.selectors.item );
        const provider = tribe.tickets.block.getTicketsBlockProvider( $form );

        $cartItems.each(
            function() {
                const $blockCartItem = $( this );
                const id = $blockCartItem.data( 'ticket-id' );
                const $modalCartItem = $modalCart.find( '[data-ticket-id="' + id + '"]' );

                if ( 0 === $modalCartItem.length || 0 === $blockCartItem.length || ! $modalCartItem.hasClass( 'nyp-product' ) ) {
                    return;
                }

                // Sync current NYP block price to modal input.
                const ticketPrice = $blockCartItem.data( 'ticket-price' );
                $modalCartItem.data( 'ticket-price', ticketPrice ).find( '.nyp-input' ).val( woocommerce_nyp_format_price( ticketPrice ) ).attr( 'type', 'hidden' );
                $modalCartItem.find(  tribe.tickets.modal.selectors.itemPrice ).text( tribe.tickets.utils.numberFormat( ticketPrice, provider ) );
                tribe.tickets.modal.updateItem( id, $modalCartItem, $blockCartItem );
            }
        );

        // Launch NYP validation on modal form.
        $form.addClass( 'cart' ).wc_nyp_form();

    } );

      /**
     * Initialize Block form
     */
    $( tribe.tickets.block.selectors.form ).each( function() {

        const $form = $(this);

        if ( tribe.tickets.block.commerceSelector.woo !==  tribe.tickets.block.getTicketsBlockProvider( $form ) ) {
            return;
        }

        $form.addClass( 'cart' ).wc_nyp_form();

    } );

    /**
     * Update ticket totals when price is changed
     */
    $( document ).on( 'wc-nyp-updated', function( event, nypProduct ) { 
   
        const $form = nypProduct.$cart;
        const provider = tribe.tickets.block.getTicketsBlockProvider( $form );
		const $ticket = nypProduct.$el.closest( tribe.tickets.block.selectors.item );
        const ticketID = $ticket.data( 'ticket-id' );
        const ticketPrice = nypProduct.getPrice();

        if ( ! $ticket.length ) {
            return;
        }

        // Set the price on the block data attribute regardless if changed in block or modal.
        $( tribe.tickets.block.selectors.container ).find( tribe.tickets.block.selectors.item ).filter( '[data-ticket-id="' + ticketID + '"]' ).data( 'ticket-price', ticketPrice );
        $ticket.data( 'ticket-price', ticketPrice );
        $ticket.find( tribe.tickets.block.selectors.itemPrice ).text( tribe.tickets.utils.numberFormat( ticketPrice, provider ) );        

        const $qty_input = $ticket.find( tribe.tickets.block.selectors.itemQuantityInput );

        if ( $qty_input.length && parseInt( $qty_input.val(), 10 ) ) {
            
            tribe.tickets.block.updateFormTotals( $form );

            if ( $form.is( tribe.tickets.modal.selectors.form ) ) {
                tribe.tickets.modal.updateItem( ticketID, $ticket );
            }
        }

    } );

    /**
     * Validate modal form before allowing submit.
     */
    $( document ).on( 'isValidForm.eventTicketsModal', function( event, $form ) {

         const nypForm = $form.wc_nyp_get_script_object();
    
        if ( nypForm && ! nypForm.isValid( 'submit' ) ) { 
            return false;
        } else {
            return true;
        }
    
    } );

} );

