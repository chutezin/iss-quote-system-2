<?php	$product_id = $value['product_id'];
	if(isset($value['variation_id'])):
		$variation_id = $value['variation_id'];
	endif;
		$quantity = $value['quantity'];
		$quote_item_id = ( !isset($variation_id) )? $product_id : $variation_id;
		$product = wc_get_product( $quote_item_id );        
		$title = $product->get_name();
		$sku = $product->get_sku();
		$accessoryAttributes =  $product->get_attributes();	
		$description = $product ->get_short_description();
		$image = $product->get_image();
		$type = $product->get_type();

		
					echo" <tr class='rqaq_product' id='$key'>";?>
				
						<td class="accessoryImage"><?php  echo $image ?></td>
						<td class="name">
						<div class="accessory_att_information">
						<a class="accessoryLink" href="<?php echo get_permalink( $product->get_id() ); ?>" target="_blank">	<h4><?php echo $title; ?> </h4></a>
						</td>
						</div>
						<td class="price">
                        <?php 	
                        $price = $product->get_price();
						woocommerce_template_single_price();
                        ?>						
						</td>
						<td class="qty">
						<input type="number" step="1" title="qty" value="<?php echo $quantity; ?>" name="rqaq_quantity" class="iss_rqaq_quantity">
				
						</td>                        
                    
                        <td class="Actions">                   
						<?php
							echo apply_filters( 'iss_rqaq_update_link', sprintf( '<a href="#" data-update_item="%s"  data-wp_nonce="%s"  data-product_id="%d" class="rqaq_update_item update" title="%s">Update</a>', $key, wp_create_nonce( 'remove-request-quote-' . $product_id ), $product_id,  __( 'Remove this item', 'iss_request_a_quote' ) ), $key );
							echo apply_filters( 'iss_rqaq_remove_link', sprintf( '<a href="#"  data-remove-item="%s" data-wp_nonce="%s"  data-product_id="%d" class="rqaq_remove_item" title="%s">REmove</a>', $key, wp_create_nonce( 'remove-request-quote-' . $product_id ), $product_id,  __( 'Remove this item', 'iss_request_a_quote' ) ), $key );
							?>
                        </td>      
					<?php
						echo '</tr>';	