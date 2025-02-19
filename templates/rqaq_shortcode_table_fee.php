<?php
$name = $value['name'];
$price = number_format($value['amount'], 2);
$total = number_format($value['total'], 2);
$html =  " <tr class='rqaq_product'>";
$html .='<td class="accessoryInfo">';
$html .= '<div class="accessory_att_information">';				
$html .= "<p>$name</p>";
$html .= "<p><strong>Amount: </strong>$price</p>";
$html .= "<p><strong>Total: </strong>$price</p>";
$html .= "</div></td></tr>";
return $html;