jQuery(document).ready(function ($) {
    $('.create_quote_cart').click(function (e) {
        ga('send', 'event', 'Quote System', 'Request Quote Button', 'Customer clicked on  request a quote button on cart');      
    });
    $('input.raq-send-request').click(function (e) { 
        ga('send', 'event', 'Quote System', 'Create a Quote', 'Customer clicked on create a quote button on request a quote page');      
    });
    $('a[title="Empty Cart"]').click(function (e) { 
        ga('send', 'event', 'Quote System', 'Continue Shopping', 'Customer clicked on continue shopping button on request a quote thank you page');      
    });
    });