<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * creating the class adminstrator for ISS request a quote
 *
 * @class ISS_RQAQ_Shortcodes
 * @package ISS Quote System
 * @since   1.0.0
 * @author  chutes
 */

if (!class_exists('ISS_RQAQ_Shortcodes')) {
    class ISS_RQAQ_Shortcodes
    {

        /**
         * Single instance of the class
         *
         * @var \RQAQ
         */
        protected static $instance;
        /**
         * Returns single instance of the class
         *
         * @return \ISS_RQAQ_Shortcodes
         * @since 1.0.0
         */

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        /**
         * Constructor
         *
         * Initialize the plugin and register some actions
         *
         * @since 1.0.0
         * @author  chutes
         *
         */
        public function __construct()
        {
            add_shortcode('iss_rqaq_shortcode', array($this, 'request_quote_list'));
        }

        public function request_quote_list($atts)        
        {
            $shop_page_url = get_permalink(wc_get_page_id('shop'));
            if (isset($_GET["response"]) && trim($_GET["response"]) == 'true') {
                if (isset($_GET["order_id"])) {
                    $numberquote = $_GET['order_id'];
                }

                if (isset($_GET["email"])) {
                    $email = $_GET['email'];
                }

                $ty_tittle = '<h2 class="title-quote-page thanks">Thank you for your request</h2>';

               
                $ty_message_html = "<div class='thank-you-message'>";

                if ($numberquote) {
                    $ty_message_html .= "<p>Your quote number is <strong>#$numberquote</strong></p>";
                }

                if ($email) {
                    $ty_message_html .= "<p>We will contact you on <strong>$email</strong></p>";
                }

                $ty_message_html .= '<p>You will receive your quote via email shortly. </p>';
                $ty_message_html .= '<a href="' . $shop_page_url . '?empty_cart=yes" class="button" title="' . esc_attr('Empty Cart', 'woocommerce') . '">' . esc_html('Continue Shopping', 'woocommerce') . '</a>';
                $ty_message_html .= "</div>";
                echo $ty_tittle;
                echo $ty_message_html; 
                if (!is_user_logged_in()) {
                    if (isset($_GET["email"])) {
                        $email = $_GET['email'];
                    }

                    if (isset($_GET["password"])):
                        $password = $_GET['password'];
                        $ty_new_user_html = "<div class='thank-you-not-logged'>";
                        $ty_new_user_html .= '<h4>Now you have an account</h4>';
                        $ty_new_user_html .= '<p>You can follow your quote status in your brand new account, next time just log in with your username and password to autofill the quote form.</p>';
                        if ($email) {
                            $ty_new_user_html .= "<p>Your username is <strong>$email</strong></p>";
                        }

                        if ($password) {
                            $ty_new_user_html .= "<p>Your password is  <span style='font-weight:800' id='pw'>$password</span></p><p>Please store in a safe place before close this window</p>";
                        }

                        $ty_new_user_html .= '<a class="button" href="#" id="loginUP">Log in</a>';
                        $ty_new_user_html .= "</div>";
                        
                        echo $ty_new_user_html;
                    else:
                        $ty_email_exists_html = "<div class='thank-you-not-logged'>";
                        $ty_email_exists_html .= '<h4>You already have an account!</h4>';
                        if ($email) {
                            $ty_email_exists_html .= "<p>Your username is <strong>$email</strong></p>";
                        }

                        $actual_link = get_home_url() . '/my-account/lost-password';
                        $ty_email_exists_html .= '<p>Your account was updated with your new information!</p>';
                        $ty_email_exists_html .= "<a class='button btn' target='_blank' href='$actual_link'>lost your password ?</a>";
                        $ty_email_exists_html .= "</div>";

                        echo $ty_email_exists_html;
                    endif;
                }

            } elseif (basename($_SERVER['REQUEST_URI']) == '?response=error') {
                $error = "<div class='thank-you-message'>";
                $error .= '<h4>Something is wrong :/</h4>';
                $error .= '<p> We did not receive your quote, or something is wrong with that, please contact our team to more information, or try again </p>';
                $error .= "</div>";
                echo $error;
            } else {

                if (WC()->cart != null) {
                    $cart = WC()->cart;
                    $false = false;
                    ISS_RQAQ_Request()->clear_raq_list($false);
                    ISS_RQAQ_Request()->set_raq_content();
                    $raq_content = ISS_RQAQ_Request()->get_raq_return();
                    $cart_url = wc_get_cart_url();
                  
                    $totals = $cart->get_total();                    //
                    $title_quote = '<h2 class="title-quote-page">Quote Request</h2>';
                    $quoteEntryContent = '<div id="quote-cart">';
                    if (count($raq_content) >= 1) {
                        $quote_cart_html = "<div id='rqaq_cart_content_wrapper'>";
                        $quote_cart_html .= '<table id="rqaq_cart_contents">';
                        $quote_cart_html .= '<h6>Quote Summary</h6>';
                        $quote_cart_html .= '<a href="#" id="showCartDetails">Show cart details</a>';
                        foreach ($raq_content as $key => $value) {
                            if ($key !== 'fees') {
                                $quote_cart_html .= include ISS_RQAQ_PLUGIN_DIR . 'templates/rqaq_shortcode_table.php';
                            }
                        }

                        if ( isset($raq_content['fees'])) {
                            foreach ($raq_content['fees'] as $key => $value) {
                                if (!empty($value)) {
                                    $quote_cart_html .= include ISS_RQAQ_PLUGIN_DIR . 'templates/rqaq_shortcode_table_fee.php';
                                }
                            }
                        }

                        $quote_cart_html .= "<tr><td class='total'>Sub Total: $totals</td></tr>";
                        $quote_cart_html .= "<tr><td class='action'><a href='$cart_url'>Edit Cart</a></td></tr>";
                        $quote_cart_html .= '</table></div>';

                        $quote_form_html = '<div class="form-view">';
                        $quote_form_html .= include ISS_RQAQ_PLUGIN_DIR . 'templates/rqaq_shortcode_form_view.php';
                        $quote_form_html .= '</div>';

                        $quoteEntryContent.= $title_quote;
                        $quoteEntryContent.= $quote_cart_html;
                        $quoteEntryContent .= $quote_form_html;

                    } else {
                        $empty ='<div class="cart-empty"><h4>No products on quote cart</h4></div>';
                        $empty .= "<a href='$shop_page_url' class='cntShop'>Continue Shopping</a>";
                        echo $empty;
                    }

                    $quoteEntryContent.= '</div>';
                    echo $quoteEntryContent;
                } else {
                    return;
                }
            }

        }
    }
}

/**
 *  unique acccess to instance of the ISS_RQAQ_Shortcodes class
 *
 * @return \ISS_RQAQ_Shortcodes
 *
 */

function ISS_RQAQ_Shortcodes()
{
    return ISS_RQAQ_Shortcodes::get_instance();
}

ISS_RQAQ_Shortcodes();
