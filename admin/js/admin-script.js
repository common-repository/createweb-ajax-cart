jQuery(document).ready( function($) {
    $( 'body' ).on( 'click', '#change_status', function( e ) { 
        select = $(this).siblings( 'select' );
        status = select.val();
        id = $(this).data( 'id' );

        $.ajax({
            type: "POST",
            url:  ajaxurl,
            data: { 
                action: 'cwac_change_status', 
                id: id,
                status: status
            }, 
            success: function( data ){
                $( '#cart_table_wrap' ).html( data );
            }
        });
        
        e.preventDefault();
    })
})