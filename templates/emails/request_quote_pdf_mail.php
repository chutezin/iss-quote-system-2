<?php
/**
 * HTML Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( 'You received a quote request from %s. The request is the following:', 'iss-request-a-quote' ), $raq_data['user_name'] ); ?></p>


<h2><?php _e('Request Quote', 'iss-request-a-quote') ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <thead>
    <tr>
	<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Thumbnail', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'iss-request-a-quote' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Qty', 'iss-request-a-quote' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'SubTotal', 'iss-request-a-quote' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if( ! empty( $raq_data['raq_content'] ) ):
        foreach( $raq_data['raq_content'] as $item ):
            if( isset( $item['variation_id']) ){
                $product = wc_get_product( $item['variation_id'] );
            }else{
                $product = wc_get_product( $item['product_id'] );
            }
            
        $product_id = $product->get_id();
        $title = $product->get_name();
		$sku = $product->get_sku();
		$accessoryAttributes =  $product->get_attributes();	
		$description = $product ->get_short_description();
		$imageID = $product->get_image_id();
		$image = wp_get_attachment_image_src($imageID);
		$type = $product->get_type();	
					echo" <tr class='rqaq_product' id='$product_id'>";?>
				
						<td class="accessoryImage">	
							<img src="<?php echo $image[0] ?>" alt="<?php echo $image[0]?>" style="max-height: 60px; width: auto; margin: 0 auto;">
						</td>
						<td class="accessoryInfo">
							<div class="accessory_att_information">
							<a class="accessoryLink" href="<?php echo get_permalink( $product->get_id() ); ?>" target="_blank">	<?php echo $title; ?></a>
									<?php $varSKU = $product->get_sku(); if($varSKU){ ?>						
									<p>SKU: <strong><?php echo $varSKU; ?></strong></p>
									<?php }	
									
									if( $type != 'simple' ){						
                                 echo wc_get_formatted_variation(	$accessoryAttributes );
                                };
                                   
									
								?>
									<?php $brand = wp_get_post_terms( $product_id, 'pwb-brand', array('orderby'=>'name')); 
									if( !empty($brand)):?>
									<p>Brand: <strong><?php echo $brand[0]->name; ?></strong></p>
									<?php endif;?>
									<?php $varCOLOR = $product->get_attribute('pa_color'); if($varCOLOR){ ?>
									<p>Color: <strong><?php echo $varCOLOR; ?></strong></p>
									<?php } ?>
									<?php $varSIZE = $product->get_attribute('pa_size'); if($varSIZE){ ?>
									<p>Size: <strong><?php echo $varSIZE; ?></strong></p>
									<?php } ?>
									<?php $WC_Product = new WC_Product();
									$var = $WC_Product->is_in_stock(); if($var){ ?>
									<p><strong>In Stock </strong> <?php echo the_field('shipping_time', $product_id); ?></p>
									<?php } else{ ?>
									<p class="outOfStock"><strong>Out of stock</strong></p>
									<?php }?>						
					</div>
						</td>
						<td class="price">
                        <?php 	
                        $price = $product->get_price();
					
						echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $product )); // PHPCS: XSS ok.
				
                        ?>		
						
						</td>
						
						<td scope="col" style="text-align:left;"><?php echo $item['quantity'] ?></td>
				
						
						<td class="product-subtotal">
						<?php  echo WC()->cart->get_product_subtotal( $product, $item['quantity'] ); ?>
                </td><?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>


<?php if( ! empty( $raq_data['user_message']) ): ?>
<h2><?php _e( 'Customer message', 'iss-request-a-quote' ); ?></h2>
    <p><?php echo $raq_data['user_message'] ?></p>
<?php endif ?>
<h2><?php _e( 'Customer details', 'iss-request-a-quote' ); ?></h2>

<p><strong><?php _e( 'Name:', 'iss-request-a-quote' ); ?></strong> <?php echo $raq_data['user_name'] ?></p>
<p><strong><?php _e( 'Email:', 'iss-request-a-quote' ); ?></strong><?php echo $raq_data['user_email']; ?></p>

<?php 
var_dump($raq_data['user_phone']);

if( !empty( $raq_data['user_phone'])): ?>
<p><strong><?php _e( 'hone:', 'iss-request-a-quote' ); ?></strong> <?php echo $raq_data['user_phone']; ?></p>
<?php endif ?>
<?php do_action( 'woocommerce_email_footer' ); ?>