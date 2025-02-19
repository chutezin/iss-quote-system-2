<?php
$product_id = $value['product_id'];
if( isset($value['variation_id'])){
	$variation_id = $value['variation_id'];
}
$quantity = $value['quantity'];
$quote_item_id = ( !isset($variation_id) || $variation_id === 0) ? $product_id : $variation_id;
$product = wc_get_product($quote_item_id);
$title = $product->get_name();
$sku = $product->get_sku();
$html = "<tr class='rqaq_product' id='$key'>";
$html .= "<td class='accessoryInfo'>";
$html .= "<div class='accessory_att_information'>";
$html .= "<a class='accessoryLink' href='" .  get_permalink($product->get_id()) ."' target='_blank'>";
$html .=  $title . "</a>";
$html .=  "<p><strong>x </strong>$quantity<strong>each:</strong>";
$html .= apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product)) . "</p>"; // PHPCS: XSS ok.
$html .= "<p> <strong>Total:</strong>";
$html .= apply_filters('yith_ywraq_hide_price_template', WC()->cart->get_product_subtotal($product, $quantity), $product_id) . "</p>";
$html .= "</div></td></tr>";
return $html; 				