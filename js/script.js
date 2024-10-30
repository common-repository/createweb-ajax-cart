(function($){

$.cw_ajax_cart = function() {
    
    //Update minicart
    
    $.cw_ajax_cart.minicart_update = function() {
        $.ajax({
            type: "POST",
            url: cart_ajax.ajax_url,
            data: { 
                action: 'cwac_cart', 
                nonce: cart_ajax.nonce
            }, 
            success: function( data ) {
                $( '.cw-minicart-info' ).text( data );
            }
        })     
    };

    //Changing product quantity on single page
    
    $( '.single-cart-quantity .plus' ).on( 'click', function() {
        var input = $(this).siblings( '.product-qty' );
        input.val( parseInt( input.val() ) + 1 );
    });
    
    
    $( '.single-cart-quantity .minus' ).on( 'click', function() {
        var input = $(this).siblings( '.product-qty' );
        
        if ( input.val() > 1 ) {
            input.val( parseInt( input.val() ) - 1 );
        }
    });	
    
    
    $( '.single-cart-quantity .product-qty' ).blur( function() {
        if ( $(this).val() < 1 ) {
            $(this).val( 1 );
        }
    });
    
    $(document.body).on( 'added_to_cart', function() {} );
    
    $.cw_ajax_cart.added_to_cart = function( parent ) {
        var msg_type = cw_cart_params.added_msg_type;
        
        if ( msg_type === 'text' ) {
            if ( ! parent.hasClass( 'added' ) ) {
                parent.addClass( 'added' );
                $( '<div class="cart-added-msg">' + cw_cart_params.added_msg + '</div>' ).appendTo( parent ); 
                setTimeout( function() { parent.find( '.cart-added-msg' ).fadeOut( 500 ) }, 2000 );
            } else {
                parent.find( '.cart-added-msg' ).show();
                setTimeout( function() { parent.find( '.cart-added-msg' ).fadeOut( 500 ) }, 2000 );
            }  
        } else {
            if ( ! parent.hasClass( 'added' ) ) {
                parent.addClass( 'added' );
            }
            
            $.fancybox({ href: '#popup_cart_added' });
        }
        
        $(document.body).trigger( 'added_to_cart' );
    };
    
    //Adding product to cart
    
    $( '.add-to-cart-button' ).on( 'click', function() { 
        var prod_id = $(this).attr( 'data-id' );
        var prod_count = $( '.product-qty' ).val();
        var cart_cookie = $.cookie( cart_ajax.cart_cookie );
        var cookie_val = [];
        var parent = $(this).parents( '.cart-add-box' );
   
        if ( prod_count === undefined ) {
            prod_count = 1;
        }
        
        var obj = { id: prod_id, count: prod_count };
        
        if ( cart_cookie === null ) {
            cookie_val.push( obj );
            var cookie_upd = JSON.stringify( cookie_val ); //from array to string
            $.cookie( cart_ajax.cart_cookie, cookie_upd, { path: '/' } );
            
            $.cw_ajax_cart.added_to_cart( parent );            
            $.cw_ajax_cart.minicart_update();
        } else {
            var get_cookie = $.cookie( cart_ajax.cart_cookie );
            var new_cookie = JSON.parse( get_cookie ); //from string to array 
            var search_id = get_cookie.search( prod_id );
            
            //If current ID not in cookie, add it
            if ( search_id == -1 ) {
                new_cookie.push( obj );
                var updated_cookie = JSON.stringify( new_cookie );
                $.cookie( cart_ajax.cart_cookie, updated_cookie, { path: '/' } );
                
                $.cw_ajax_cart.added_to_cart( parent );               
                $.cw_ajax_cart.minicart_update();
            } else {
                for ( var i = 0; i < new_cookie.length; i++ ) {
                    if ( new_cookie[i]['id'] == prod_id ) {
                        new_cookie[i]['count'] = parseInt( new_cookie[i]['count'] ) + parseInt( prod_count );                       
                        var updated_cookie = JSON.stringify( new_cookie );
                        $.cookie( cart_ajax.cart_cookie, updated_cookie, { path: '/' } );
                    }
                }  
                
                $.cw_ajax_cart.added_to_cart( parent ); 
                $.cw_ajax_cart.minicart_update();             
            }
        }
    
        return false;
    });
    
    //Total sum on cart page
    
    $.cw_ajax_cart.totalSum = function() {
        var sum_total = 0;
        var coupon_cookie = $.cookie( cart_ajax.coupon_cookie );
        
        $( '.cw-product-item' ).each( function() {
            var s = $(this).find( '.cw-product-subtotal' ).find( '.amount' ).text();
            if ( s ) {
                sum = s;
            } else {
                sum = 0;
            }
            
            var sum = parseFloat( sum );
            sum_total = sum_total + sum;
        })
        
        var total = sum_total;
        
        if ( total ) {
            if ( coupon_cookie ) {
                var coupon = JSON.parse( coupon_cookie ); 
                
                if ( coupon['type'] == 'percent' ) {
                    total = sum_total - ( sum_total / 100 ) * coupon['discount'];
                } else if ( coupon['type'] == 'fix' ) {
                    total = sum_total - coupon['discount'];
                }
            }
            
            $( '.cw-subtotal-sum .amount' ).attr( 'data-total', sum_total );
            $( '.cw-subtotal-sum .amount' ).text( sum_total );
            $( '.cw-total-sum .amount' ).text( total );
        } else {
            $('.cw-total-row').remove();
        }
    };
    
    //Apply coupon
    
    $.cw_ajax_cart.apply_coupon = function() {
        var coupon_val = $( 'input#coupon' ).val();
        var coupon_cookie = $.cookie( cart_ajax.coupon_cookie );

        var coupon = '';
        
        if ( coupon_val != '' ) {
            coupon = coupon_val;
        } else {
            if ( coupon_cookie ) {
                cookie = JSON.parse( coupon_cookie );
                coupon = cookie['coupon'];
            }
        }
        
        if ( coupon != '' ) {
            $.ajax({
                type: "POST",
                url:  cart_ajax.ajax_url,
                data: { 
                    action: 'cwac_cart_coupon', 
                    nonce: cart_ajax.nonce,
                    coupon: coupon
                }, 
                success: function( data ){
                    if ( data ) {
                        $( '#coupon-msg' ).text( '' );
                        sub = $( '.cw-subtotal-sum' );
                        console.log(sub);
                        
                        if ( sub.length ) {
                            sum = parseInt( $( '.cw-subtotal-sum .amount' ).attr( 'data-total' ) );
                            
                            if ( data['type'] == 'percent' ) {
                                total = sum - ( sum / 100 ) * data['discount'];
                                $( '.cw-total-sum .amount' ).text( total );
                            } else if ( data['type'] == 'fix' ) {
                                total = sum - data['discount'];
                                $( '.cw-total-sum .amount' ).text( total );
                            }
                        }
                        
                        $( '#coupon-msg' ).text( cw_cart_params.coupon_apply_msg );
                        
                        $.cookie( cart_ajax.coupon_cookie, JSON.stringify( data ), { path: '/' });
                    } else {
                        $( '#coupon' ).val( '' ).addClass( 'coupon-false' );
                        $( '#coupon-msg' ).text( cw_cart_params.coupon_not_valid );
                        $( '.cw-total-sum .amount' ).text( $( '.cw-subtotal-sum .amount' ).attr( 'data-total' ));
                        $.cookie( cart_ajax.coupon_cookie, null, { path:'/' });
                    }                    
                }
            })    
        }
    }  
    
    $( '#cart_coupon_form #apply_coupon' ).on( 'click', function() {
        $.cw_ajax_cart.apply_coupon();
    })
    
    $( '#remove_coupon' ).on( 'click', function() {
        $( '#coupon' ).val( '' );
        $( '.cw-total-sum .amount' ).text( $( '.cw-subtotal-sum .amount' ).attr( 'data-total' ));
        $.cookie( cart_ajax.coupon_cookie, null, { path:'/' });
    })
    
    // Recount coupon after loading if it was changed
    
    $.cw_ajax_cart.check_coupon = function() {
        var coupon_cookie = $.cookie( cart_ajax.coupon_cookie );
        var coupon;
        
        if ( coupon_cookie ) {
            cookie = JSON.parse( coupon_cookie );
            coupon = cookie['coupon'];
            
            $.ajax({
                type: "POST",
                url:  cart_ajax.ajax_url,
                data: { 
                    action: 'cwac_cart_coupon', 
                    nonce: cart_ajax.nonce,
                    coupon: coupon
                },  
                success: function( data ) {
                    if ( data ) {
                        sum = parseInt( $( '.cw-subtotal-sum .amount' ).attr( 'data-total' ) );
                        
                        if ( data['type'] == 'percent' ) {
                            total = sum - ( sum / 100 ) * data['discount'];
                            $( '.cw-total-sum .amount' ).text( total );
                        } else if ( data['type'] == 'fix' ) {
                            total = sum - data['discount'];
                            $( '.cw-total-sum .amount' ).text( total );
                        }
                        
                        $.cookie( cart_ajax.coupon_cookie, JSON.stringify( data ), { path: '/' });
                    }
                }
            })
        }
    }
    
    $.cw_ajax_cart.check_coupon();
    
    //Delete item from cart
       
    $( 'span.cart-remove-item' ).on( 'click', function() {
        var post_id = $(this).attr( 'data-id' );       
        var cookie = $.cookie( cart_ajax.cart_cookie );
        var arr_cookie = JSON.parse( cookie ); //from string to array 
        
        if ( arr_cookie.length > 1 ) {            
            for ( var i = 0; i < arr_cookie.length; i++ ) {
                if ( arr_cookie[i]['id'] == post_id ) {
                    var index = arr_cookie.indexOf( arr_cookie[i] );
                    arr_cookie.splice( index, 1 );
                }
            }
            
            var str_cookie = JSON.stringify( arr_cookie ); //from array to string
            $.cookie( cart_ajax.cart_cookie, str_cookie, { path: '/' } );
            $(this).parents( '.cw-product-item' ).fadeOut( 400 );
            $(this).parents( '.cw-product-item' ).queue( function () {
                $(this).remove();
                $.cw_ajax_cart.totalSum();
            })
        } else {
            $( '#cw-cart-page-wrap' ).html('');
            $( '<div class="cart-empty">' + cw_cart_params.cart_empty + '</div>' ).appendTo( '#cw-cart-page-wrap' );
            $.cookie( cart_ajax.cart_cookie, null, { path:'/' } );
        }   
        
        $.cw_ajax_cart.minicart_update();
    });
    
    // Changing product quantity on cart page
    
    $.cw_ajax_cart.change_count = function( input, price, prod_id, prod_count ) {
        var cart_cookie = $.cookie( cart_ajax.cart_cookie );
        var get_cookie = $.cookie( cart_ajax.cart_cookie );
        var new_cookie = JSON.parse( get_cookie ); //from string to array 

        for ( var i = 0; i < new_cookie.length; i++ ) {
            if ( new_cookie[i]['id'] == prod_id ) {
                new_cookie[i]['count'] = prod_count;                       
                var updated_cookie = JSON.stringify( new_cookie ); //from array to string
                $.cookie( cart_ajax.cart_cookie, updated_cookie, { path: '/' } );
            }
        }   
    };
    
    $( '.page-cart-quantity .plus' ).on( 'click', function() {
        var input = $(this).siblings( '.product-qty' );
        var price = $(this).parents( '.cw-product-item' ).attr( 'data-price' );
        var prod_id = $(this).parents( '.cw-product-item' ).attr( 'data-id' );
        
        input.val( parseInt( input.val() ) + 1 ); 
        var prod_count = input.val();      
        
        $(this).parents( '.cw-product-item' ).find( '.cw-product-subtotal' )
            .find( '.amount' ).text( price * input.val() );
            
        $.cw_ajax_cart.change_count( input, price, prod_id, prod_count );
        $.cw_ajax_cart.totalSum();
    });
    
    
    $( '.page-cart-quantity .minus' ).on( 'click', function() {
        var input = $(this).siblings( '.product-qty' );
        var price = $(this).parents( '.cw-product-item' ).attr( 'data-price' );
        var prod_id = $(this).parents( '.cw-product-item' ).attr( 'data-id' );
        
        if ( input.val() > 1 ) {
            input.val( parseInt( input.val() ) - 1 );
        }
        
        var prod_count = input.val();
        
        $(this).parents( '.cw-product-item' ).find( '.cw-product-subtotal' )
            .find( '.amount' ).text( price * input.val() );
        
        if ( input.val() >= 1 ) {
            $.cw_ajax_cart.change_count( input, price, prod_id, prod_count );
        }
        
        $.cw_ajax_cart.totalSum();
    });
    
    
    $( '.page-cart-quantity .product-qty' ).blur( function() {
        if ( $(this).val() < 1 ) {
            $(this).val( 1 );    
        }
         
        var input = $(this);
        var price = $(this).parents( '.cw-product-item' ).attr( 'data-price' );
        var prod_id = $(this).parents( '.cw-product-item' ).attr( 'data-id' );
        var prod_count = parseInt( input.val() );
        
        $(this).parents( '.cw-product-item' ).find( '.cw-product-subtotal' )
            .find( '.amount' ).text( price * input.val() );
        
        if ( input.val() >= 1 ) {
            $.cw_ajax_cart.change_count( input, price, prod_id, prod_count );
        }
        
        $.cw_ajax_cart.totalSum();
    });

} //function $.cw_ajax_cart()

$.ajax({
    type: "POST",
    url: cart_ajax.ajax_url,
    data: { 
        action: 'cwac_add_popup', 
    }, 
    success: function( data ) {
        $('body').append( data );
    }
})

$.cw_ajax_cart();

$( '.cw-cart-totals button' ).on( 'click', function() {
    $.fancybox({ href: '#cw_popup_wrap' });
});

//Sending order from cart page
$.cw_form_validation = function() {
    var name = $( '#cw_popup_form input[name=name]' ).val();
    var phone = $( '#cw_popup_form input[name=phone]' ).val();
    var email = $( '#cw_popup_form input[name=email]' ).val();
    var reg_phone = /^(\+?\d+)(\(?\d+\)?)(\d+)$/;
    var reg_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    
    function valid_class( input, val, regex ) {
        if ( regex ) {            
            valid = val.search( regex );
            if ( valid == -1 ) {
                input.addClass( 'cw-input-not-valid' );       
            } else {
                input.removeClass( 'cw-input-not-valid' );
            }
        } else {
            if ( val == '' ) {
                input.addClass( 'cw-input-not-valid' );
            } else {
                input.removeClass( 'cw-input-not-valid' );
            }
        }
    };
    
    $( '#cw_popup_form .cw-form-input' ).each( function() {
        var input = $(this);
        var name = $(this).attr( 'name' );
        var val = $.trim( $(this).val() );
        
        switch ( name ) {
            case 'phone':
                valid_class( input, val, reg_phone );
                break;
            case 'email':
                valid_class( input, val, reg_email );
                break;
            case 'name':               
                valid_class( input, val, false );
                break;
        }
    });

    if ( ! $( 'input' ).is( '.cw-input-not-valid' ) ) {
        form_data = $( '#cw_popup_form' ).serialize();
        
        $.ajax({
            type: "POST",
            url:  cart_ajax.ajax_url,
            beforeSend: function(){
            	$('#cw-form-preloader').show();  
            },
            data: { 
                action: 'cwac_add_new_order', 
                nonce: cart_ajax.nonce,
                fields: form_data
            }, 
            success: function( data ) {
                $('#cw-form-preloader').hide(); 
                if ( typeof data === 'string' ) {
                    $( '#cw_popup form' ).trigger( 'reset');
                    $( '#cw_popup' ).html( '<div class="cw-success-send">' + data + '</div>' );
                    console.log(data);
                } else {
                    $.cookie( cart_ajax.cart_cookie, null, { path:'/' } );
                    $.cookie( cart_ajax.coupon_cookie, null, { path:'/' } );
                    
                    if ( data.type == 'text' ) {
                        $( '#cw_popup form' ).trigger( 'reset' );
                        $( '#cw_popup' ).html( '<div class="cw-success-send">' + data.msg + '</div>' );
                        $( '#cw-cart-page-wrap' ).html('');
                        $( '<div class="cart-empty">' + cw_cart_params.cart_empty + '</div>' )
                            .appendTo( '#cw-cart-page-wrap' );
                        $.cw_ajax_cart.minicart_update();
                    } else {
                        location.href = data.page;
                    }
                }
            }
        })
    };
};

$( '#cw_popup_form' ).on( 'submit', function( event ) {  
    event.preventDefault();
    $.cw_form_validation();
});

})(jQuery);