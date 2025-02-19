<?php
/**
 * HTML Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */
$email_heading = 'New Quote Request';
do_action('woocommerce_email_header', $email_heading);
?>
<p><?php printf(__('We received a new quote request from %s. Details of the quote below:', 'iss-request-a-quote'), $raq_data['user_name']);?></p>


<h2><?php _e('Quote Request', 'iss-request-a-quote')?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <thead>
    <tr>
	<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Thumbnail', 'iss-request-a-quote');?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Product', 'iss-request-a-quote');?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Price', 'iss-request-a-quote');?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Qty', 'iss-request-a-quote');?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('SubTotal', 'iss-request-a-quote');?></th>
    </tr>
    </thead>
    <tbody>
    <?php
if (!empty($raq_data['raq_content'])):

    foreach ($raq_data['raq_content'] as $value => $item):
     
        if ($value !== 'fees') {
            if ($item['variation_id'] != 0) {
                $product = wc_get_product($item['variation_id']);
            } else {
                $product = wc_get_product($item['product_id']);
            }
            $product_id = $product->get_id();
            $title = $product->get_name();
            $sku = $product->get_sku();
            $accessoryAttributes = $product->get_attributes();
            $description = $product->get_short_description();
            $imageID = $product->get_image_id();
            $image = wp_get_attachment_image_src($imageID);
            $type = $product->get_type();
            echo " <tr class='rqaq_product' id='$product_id'>";?>
    
                                    <td class="accessoryImage">
                                        <img src="<?php echo $image[0] ?>" alt="<?php echo $image[0] ?>" style="max-height: 60px; width: auto; margin: 0 auto;">
                                    </td>
                                    <td class="accessoryInfo">
                                        <div class="accessory_att_information">
                                        <a class="accessoryLink" href="<?php echo get_permalink($product->get_id()); ?>" target="_blank">	<?php echo $title; ?></a>
                                </div>
                                    </td>
                                    <td class="price">
                                    <?php
            $price = $product->get_price();
            echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product)); // PHPCS: XSS ok.
            ?>
                                    </td>
                                    <td scope="col" style="text-align:left;"><?php echo $item['quantity'] ?></td>
                                    <td class="product-subtotal">
                                    <?php echo WC()->cart->get_product_subtotal($product, $item['quantity']); ?>
                            </td>
                            </tr><?php
      
        }          
      
    endforeach;
   
endif;
?>
    </tbody>
</table>
<?php 
 
    if( !empty($raq_data['raq_content']) ){
        foreach($raq_data['raq_content']['fees'] as $fee => $feeProperty){

                
            $name = $feeProperty['name'];
            $amount = number_format($feeProperty['amount'],2);
            
            
            
        
            $fee_tr_html = "<p class='rqaq_product fee'>$name: $amount</p></br>";
            
            echo $fee_tr_html;
            
            }
    }

 if (!empty($raq_data['user_message'])): ?>
<h2><?php _e('Customer message', 'iss-request-a-quote');?></h2>
    <p><?php echo $raq_data['user_message'] ?></p>
<?php endif?>
<h2><?php _e('Customer details', 'iss-request-a-quote');?></h2>
<p><strong><?php _e('Name:', 'iss-request-a-quote');?></strong> <?php echo $raq_data['user_name'] . ' ' . $raq_data['user_last_name']; ?><br>
<strong><?php _e('Address:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_address1']; ?> </strong><?php echo $raq_data['user_address2']; ?><br>
<strong><?php _e('City:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_city']; ?><br>
<strong><?php _e('State:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_state']; ?><br>
<strong><?php _e('Country:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_country']; ?><br>
<strong><?php _e('Postcode:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_postcode']; ?><br>
<strong><?php _e('Email:', 'iss-request-a-quote');?></strong><?php echo $raq_data['user_email']; ?><br>
<?php
if (!empty($raq_data['user_phone'])): ?>
<p><strong><?php _e('Phone:', 'iss-request-a-quote');?></strong> <?php echo $raq_data['user_phone']; ?></p>
<?php endif?>
<?php do_action('woocommerce_email_footer');?>