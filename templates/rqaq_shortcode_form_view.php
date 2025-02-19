<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $woocommerce;
global $current_user;
$first_name = get_user_meta($current_user->ID, 'billing_first_name', true);
$last_name = get_user_meta($current_user->ID, 'billing_last_name', true);
$company = get_user_meta($current_user->ID, 'billing_company', true);
$address_1 = get_user_meta($current_user->ID, 'billing_address_1', true);
$address_2 = get_user_meta($current_user->ID, 'billing_address_2', true);
$city = get_user_meta($current_user->ID, 'billing_city', true);
$selectedCountry = get_user_meta($current_user->ID, 'billing_country', true);
$countries = WC()->countries->countries;
$state = get_user_meta($current_user->ID, 'billing_state', true);
$email = get_user_meta($current_user->ID, 'billing_email', true);
$phone = get_user_meta($current_user->ID, 'billing_phone', true);
$postcode = get_user_meta($current_user->ID, 'billing_postcode', true);
$formUrl = esc_url(ISS_RQAQ_Request()->get_raq_page_url());
if (is_user_logged_in()):
    if (isset($first_name)):
    endif;
endif;
function input_text_generator($name, $copy, $value = '', $required = false, $type = 'text', $placeholder = '')
{
    if ($value !== null) {
        $value = $value;
    }
    $label = "<label for='$name' class=''>";
    $label .= $copy;
    if ($required === true):
        $label .= "<abbr class='required' title='required'>*</abbr>";
    endif;
    $label .= " </label>";
    $label .= "<input type='$type' class='input-text ' name='$name' id='$name' placeholder='$placeholder' value='";
    $label .= $value;
    if ($required === true):
        $label .= "'required>";
    else:
        $label .= "'>";
    endif;
    return $label;
}
$form_html = "<form action='$formUrl'name='rqaq_form' id='iss_rqaq_request_form' method='post'>";
$form_html .= "<input type='hidden' name='action' value='send_message'>";
$form_html .= "<div class='rqaq_form_trigger' id='rqaq_form_login_trigger'>";
$form_html .= "<h4> login or proceed as guest</h4> <span class='triggerButton closed'>-</span></div>";
$form_html .= "<div class='rqaq_form_data' id='rqaq_form_login_data'>";
if (is_user_logged_in()):
    $form_html .= "<div class='login logged'>";
    $form_html .= "<h4>Hello ";
    if ($first_name): $form_html .= $first_name;
    else:
        $form_html .= 'There';
    endif;
    $form_html .= "</h4>";
    $form_html .= "<p>Please Confirm Your Address Below</p>";
    $form_html .= "<a href='#' class='continueForm'>Create quote</a>";
    $form_html .= "</div>"; // close .logged
else:
    $form_html .= "<div class='guest'><h4>New Customers and Guests</h4>";
    $form_html .= "<p>To make future quotes faster, we will create an account for you at the end of this checkout.</p>";
    $form_html .= "<a href='#' class='continueForm'>create quote</a>";
    $form_html .= "</div>"; // close .guest
    $form_html .= "<div class='login'>";
    $form_html .= "<h4>Returning Customers</h4>";
    $form_html .= "<p>If you have an account, click to login enter your e-mail and password to pre-fill your contact information.</p>";
    $form_html .= "<a href='#' id='loginUP'>Log in</a>";
    $form_html .= "</div>"; // close .login
endif;
$form_html .= "</div>"; // close .rqaq_form_data
$form_html .= "<div class='rqaq_form_trigger' id='rqaq_form_ShippingAdress_trigger'>";
$form_html .= "<h4>";
if (is_user_logged_in()):
    $form_html .= "Confirm your";
endif;
$form_html .= "Contact Info </h4>";
$form_html .= "<span class='triggerButton closed'> &plus;</span>";
$form_html .= "</div>"; // #rqaq_form_ShippingAdress_trigger
$form_html .= "<div class='rqaq_form_data' id='rqaq_form_ShippingAdress_data' style='display:none;'>";
//// labels init
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_name', 'First Name', $first_name, true);
$form_html .= input_text_generator('rqa_last_name', 'Last Name', $last_name, true);
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_company', 'Company', $company, true);
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide address-field update_totals_on_change validate-required' id='billing_country_field' data-priority='40'>";
$form_html .= "<label for='rqa_country' class=''>Country&nbsp;<abbr class='required' title='required'>*</abbr></label>";
$form_html .= "<span class='woocommerce-input-wrapper'>";
$form_html .= "<select name='rqa_country' id='rqa_country' class='country_to_state country_select'autocomplete='country' required>";
$form_html .= "<option value=''>Select a country&hellip;</option>";
foreach ($countries as $countryCode => $countryName) {
    if (is_user_logged_in()) {
        if (isset($selectedCountry)) {
            if ($countryCode == $selectedCountry) {
                $form_html .= "<option value='$countryCode' selected>$countryName</option>";
            } else {
                $form_html .= "<option value='$countryCode'>$countryName</option>";
            }
        } else {
            $form_html .= "<option value='$countryCode'>$countryName</option>";
        }
    } else {
        $form_html .= "<option value='$countryCode'>$countryName</option>";
    }
}
$form_html .= "</select> </span>";
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_address1', 'Street Address', $address_1, true,'text','House Number and street name');
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_address2', ' ', $address_2, false,'text','Apartment, suit, unit, etc ... (optional)');
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_city', 'Town / City', $city, true);
$form_html .= "</p>";
//state
$form_state = "<p class='form-row form-row-wide validate-required'>";
$form_state .= "<label for='rqa_country' class=''>State / County<abbr class='required' title='required'>*</abbr></label>";
$form_state .= "<div id='renderState'>";
$form_state .= "<input type='text' name='rqa_state'  value='";
 if( is_user_logged_in(  )):
     if(isset($state)): 
      $form_state .= $state;
     endif; 
    endif; 
$form_state .= "' id='rqa_state'>";
$form_state .= "</div>";
$form_state .= "</p>";
//calling state
$form_html .= $form_state;
$form_html .= "<p class='form-row form-row-wide validate-required'>";
$form_html .= input_text_generator('rqa_postcode', 'Postcode / Zip', $postcode, true);
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide'>";
$form_html .= input_text_generator('rqa_phone', 'Phone', $phone, true, 'tel');
$form_html .= "</p>";
$form_html .= "<p class='form-row form-row-wide'>";
$form_html .= input_text_generator('rqa_email', 'Email', $email, true, 'email');
$form_html .= "</p>";
$form_html .= "<p class='form-row' id='rqa_message_row'>";
$form_html .= "<label for='rqa_message' class=''>";
$form_html .= "<p>If you have any instructions, questions or requests regarding your quote, please detail them here (max 500 characters): </p>";
$form_html .= "</label>";
$form_html .= "<textarea name='rqa_message' class='input-text ' id='rqa_message' placeholder='";
$form_html .= 'Notes on your request...';
$form_html .= "'rows='5' cols='5'></textarea></p>";
$form_html .= "<input type='hidden' id='raq-mail-wpnonce' name='raq_mail_wpnonce' value='";
$form_html .= wp_create_nonce('send-request-quote');
$form_html .= "'>";
$form_html .= "<input class='raq-send-request' type='submit' value='";
$form_html .= 'Create Quote';
$form_html .= "'>";
$form_html .= "</div></form>";
return $form_html;
