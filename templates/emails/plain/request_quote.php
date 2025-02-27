<?php
/**
 * HTML Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @version 1.0.0
 * @since   1.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo $email_heading . "\n\n";

echo sprintf( __( 'You have received a quote request from %s. The request is the following:', 'iss-request-a-quote' ), $raq_data['user_name'] ) . "\n\n";

echo "****************************************************\n\n";


echo "\n";

if( ! empty( $raq_data['raq_content'] ) ):
    foreach( $raq_data['raq_content'] as $item ):

        if( isset( $item['variation_id']) ){
            $product = wc_get_product( $item['variation_id'] );
        }else{
            $product = wc_get_product( $item['product_id'] );
        }

        echo $product->get_name() . ' '. iss_rqaq_get_product_meta($item, false) . ' | ';
        echo $item['quantity'];
        echo ' '.WC()->cart->get_product_subtotal( $product, $item['quantity'] );
        echo "\n";
    endforeach;
endif;

echo "\n****************************************************\n\n";



if( ! empty( $raq_data['user_message']) ){

    echo __( 'Customer message', 'iss-request-a-quote' ) . "\n";

    echo $raq_data['user_message']. "\n\n";
}

echo __( 'Customer details', 'iss-request-a-quote' ) . "\n";

echo __( 'Name:', 'iss-request-a-quote' ); echo $raq_data['user_name'] . "\n";
echo __( 'Email:', 'iss-request-a-quote' ); echo $raq_data['user_email'] . "\n";

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );