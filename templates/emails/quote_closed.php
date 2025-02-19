<?php
/**
 * Customer quote email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-invoice.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executes the e-mail header.
 *
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p style="margin: 0px;"><?php printf( esc_html__( 'Hi %s, here is the quote that you just requested, with a link to make payment when you’re ready. ', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?>
</p><p style="margin: 0px;">If you have any questions for our hazardous area experts please contact us at  <a style="text-decoration: none;  display:block; color: #ff5c00; " href="tel:+1 832 699 6726">832 699 6726:</a></p>
	<p style='margin: 0px;'><?php
	printf(
		wp_kses(
			/* translators: %1$s Site title, %2$s Order pay link */
			__( '%1$s', 'woocommerce' ),
			array(
				'a' => array(
					'href' => array(),
				),
			)
		),
		'<a style="text-align: center; background:  #ff5c00; displa:block; width: 30%; font-size: .8em; margin: 10px 0px; float:right; text-decoration:none; padding: 5px 10px; text-transform: uppercase; color: white; border-radius: 1px;" href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay For These Items', 'woocommerce' ) . '</a>'
	);
	?>
	</p>
<?php

$orderNumber = $order->get_id();
$orderDate = $order->get_date_created();
?>
<h2 style="display:block; float:left;">Quote #<?php echo $orderNumber; ?></h2>
<table cellspacing="0" cellpadding="6" style="width: 100%;">
    <thead>
    <tr>
	<th scope="col" style="text-transform:uppercase; font-size: 0.7em; text-align:left; border: 1px solid #eee; border-right: 0px;"><?php _e( 'Item', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-transform:uppercase; font-size: 0.7em; text-align:left; border: 1px solid #eee; border-left: 0px;"><?php _e( '', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-transform:uppercase; font-size: 0.7em; text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-transform:uppercase; font-size: 0.7em; text-align:left; border: 1px solid #eee;"><?php _e( 'Qty', 'iss-request-a-quote' ); ?></th>
		<th scope="col" style="text-transform:uppercase; font-size: 0.7em; text-align:left; border: 1px solid #eee;"><?php _e( 'SubTotal', 'iss-request-a-quote' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php

    if( ! empty( $order->get_items() ) ):
        foreach( $order->get_items() as $item_id => $item_data ):
           
		$product = $item_data->get_product();		
		$product_id = $product->get_id();
        $title = $product->get_name();
		$sku = $product->get_sku();
		$accessoryAttributes =  $product->get_attributes();	
		$description = $product ->get_short_description();
		$imageID = $product->get_image_id();
		$image = wp_get_attachment_image_src($imageID);
		$type = $product->get_type();	
		?>
				 <tr class='rqaq_product'>
						<td class="accessoryImage" style="border: 1px solid #eee;">	
							<img src="<?php echo $image[0] ?>" alt="<?php echo $image[0]?>" style="max-height: 60px; width: auto; margin: 0 auto;">
						</td>
						<td class="accessoryInfo" style="border: 1px solid #eee;">
							<div class="accessory_att_information">
							<a style="display:block; width: 100%;" class="accessoryLink" href="<?php echo get_permalink( $product->get_id() ); ?>" target="_blank">	<?php echo $title; ?></a>
									<?php $varSKU = $product->get_sku(); if($varSKU){ ?>						
									<p style="line-height: 100%; margin: 0">SKU: <strong><?php echo $varSKU; ?></strong></p>
									<?php }	
									
									if( $type != 'simple' ){						
                                 echo wc_get_formatted_variation(	 $accessoryAttributes, true );
                                };
                                   
									
								?>
								        <div class="cart-sku-custom">
   
            <?php $brand = wp_get_post_terms( $product_id, 'product_brand', array('orderby'=>'name')); 
            if( !empty($brand)):?>
            <p style="line-height: 100%; margin:0;">Brand: <strong><?php echo $brand[0]->name; ?></strong></p>
            <?php endif;?>
            <?php $varCOLOR = $product->get_attribute('pa_color'); if($varCOLOR){ ?>
            <p style="line-height: 100%; margin:0;">Color: <strong><?php echo $varCOLOR; ?></strong></p>
            <?php } ?>
            <?php $varSIZE = $product->get_attribute('pa_size'); if($varSIZE){ ?>
            <p style="line-height: 100%; margin:0;">Size: <strong><?php echo $varSIZE; ?></strong></p>
            <?php } ?>
            <?php $WC_Product = new WC_Product();
			$leadtime =  get_field('shipping_time', $product_id); 
			?>
            <p style="line-height: 100%; margin:0;"><?php if( $leadtime ) : echo 'Lead time: '. $leadtime; endif;?></p>
           
          </div>
						</td>
						<td class="price" style="border: 1px solid #eee;">
						<?php 	
						global $woocommerce;
                        $price = $product->get_price_html();
					
						echo $price; // PHPCS: XSS ok.
				
                        ?>		
						
			</td>
						
						<td scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->get_quantity();  ?></td>
				
						
						<td class="product-subtotal" style=" border: 1px solid #eee;">
						<?php 
						
						$item_total = $item_data->get_total(); 
						echo '$' .number_format( $item_total, 2  );?>
                </td><?php
        endforeach;
    endif;
    ?>
	</tbody>
</table>
<div style="display:block; margin">

<?php foreach($order->get_fees() as $fee => $fee_data):?>
<?php $feeTotal = $fee_data['total']; ?>
	<p style=" font-size: 0.9em; line-height: 100%; margin: 0; padding: 5px 0;"><?php print_r($fee_data['name']);?>: $<?php echo number_format( $feeTotal, 2 )  ?> </p>

<?php	endforeach;?>
<?php foreach($order->get_taxes() as $fee => $fee_data):?>
<?php 


$feeTotal = $fee_data['tax_total']; ?>
	<p style=" font-size: 0.9em; line-height: 100%; margin: 0; padding: 5px 0;"><?php print_r($fee_data['name']);?>: $<?php echo number_format( $feeTotal, 2 )  ?></p>

<?php	endforeach;?>


	<p style=" font-size: 0.9em; line-height: 100%; margin: 0; padding: 5px 0;">Shipping: <?php echo '$' . number_format($order->get_shipping_total(), 2 );?></p>


	<?php $Totals =	$order->get_total();
	$subtotalDisplay = 	$order->get_subtotal_to_display();
	 ?>
	<p style=" font-size: 0.9em; line-height: 100%; margin: 0; padding: 5px 0;">Sub-Totals: <?php echo $subtotalDisplay?></p>

<p style=" font-size: 0.9em; line-height: 100%; margin: 0; padding: 5px 0;">Total: <?php echo '$' . number_format($Totals, 2 );?> USD</p>
</div>
<div class="pay" style="display:block;">
	<a style="text-align: center; background:  #ff5c00; display:block; width: 180px; margin: 20px 0px; float:right; text-decoration:none; padding: 10px 30px; text-transform: uppercase; color: white; border-radius: 1px;" href="<?php echo esc_url( $order->get_checkout_payment_url() ) ?>"><?php echo esc_html__( 'Pay For These Items', 'woocommerce' ) ?></a>
	</div>
<?php

/**
 * Hook for the woocommerce_email_order_meta.
 *
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * Hook for woocommerce_email_customer_details.
 *
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

?>
<p style="text-align: center;
display: block; text-transform: uppercase; f">Talk to our Hazardous Area Experts - <a style="text-decoration: none; color: #ff5c00; " href="tel:+1 832 699 6726">832 699 6726</a></p>
<?php

/**
 * Executes the email footer.
 *
 * @hooked WC_Emails::email_footer() Output the email footer
 */

do_action( 'woocommerce_email_footer', $email );