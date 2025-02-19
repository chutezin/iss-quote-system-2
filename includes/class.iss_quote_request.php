<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * creating the class adminstrator for ISS request a quote
 *
 * @class ISS_RQAQ_Request
 * @package ISS Quote System
 * @since   1.0.0
 * @author  chutes
 */

if (!class_exists('ISS_RQAQ_Request')) {
    class ISS_RQAQ_Request
    {

        /**
         * Single instance of the class
         *
         * @var \RQAQ
         *
         * Returns single instance of the class
         *
         * @return \ISS_RQAQ_Request
         * @since 1.0.0
         */

        protected static $instance;

        /**
         * Session object
         */
        public $session_class;

        /**
         * Content of session
         */
        public $raq_content = array();

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
            add_action('init', array($this, 'start_session'));
            //  calling the button scripts
            add_action('wp_enqueue_scripts', array($this, 'iss_rqaq_buttons_scripts'));
            //  calling the button scripts
            add_action('wp_enqueue_scripts', array($this, 'iss_rqaq_form_scripts'));

            //  adding the button to the single product page
            add_action('woocommerce_after_cart_totals', array($this, 'iss_rqaq_button'), 1);
            // adding the custom status
            add_filter('woocommerce_register_shop_order_post_statuses', array($this, 'iss_rqaq_order_statuses'));
            add_filter('wc_order_statuses', array($this, 'iss_rqaq_list_order_statuses'));
            add_filter('wc_order_is_editable', array($this, 'iss_quote_status_is_editable'), 10, 2);
            add_filter('woocommerce_valid_order_statuses_for_payment', array($this, 'filter_woocommerce_valid_order_statuses_for_payment'), 10, 2);

            add_action('admin_head', array($this, 'iss_rqaq_styling_admin_order_list'));

            // Adding action for 'quote-closed'
            add_action('woocommerce_order_status_wc-quote-closed', array(WC(), 'send_transactional_email'), 10, 1);

            // Sending an email notification when order get 'quote-closed' status
            add_action('woocommerce_order_status_quote-closed', array($this, 'backorder_status_custom_notification'), 20, 2);

            add_action('wp_loaded', array($this, 'init'));
            add_action('wp_loaded', array($this, 'iss_rqaq_time_validation_schedule'));
            add_action('wp', array($this, 'maybe_set_raq_cookies'), 99); // Set cookies
            add_action('shutdown', array($this, 'maybe_set_raq_cookies'), 0); // Set cookies before shutdown and ob flushing

            add_action('wp_loaded', array($this, 'woocommerce_empty_cart_action'), 20);

            add_action('iss_rqaq_clean_cron', array($this, 'clean_session'));

            add_filter('woocommerce_email_classes', array($this, 'add_woocommerce_emails'));
            add_action('woocommerce_init', array($this, 'load_wc_mailer'));

            /* general actions */
            add_filter('woocommerce_locate_core_template', array($this, 'filter_woocommerce_template'), 10, 3);
            add_filter('woocommerce_locate_template', array($this, 'filter_woocommerce_template'), 10, 3);
            add_action('init', array($this, 'send_message'));

            // //add quote from query string
            // add_action( 'wp_loaded', array( $this, 'add_to_quote_action' ), 30);
            /* ajax action */
            add_action('wp_ajax_ajax_button_action', array($this, 'ajax'));
            add_action('wp_ajax_nopriv_ajax_button_action', array($this, 'ajax'));
            /* ajax action */

            // fill form action
            add_action('wp_ajax_fill_state', array($this, 'ajax_fill_state'));
            add_action('wp_ajax_nopriv_fill_state', array($this, 'ajax_fill_state'));
            //cart button action

            add_action('admin_post_add_items_to_rqaq', array($this, 'add_fee_to_raq_content'));
            add_action('admin_post_nopriv_add_items_to_rqaq', array($this, 'add_fee_to_raq_content'));
        }
        /**
         * calling the scripts to handle with the ajax
         */
        public function iss_rqaq_buttons_scripts()
        {
            wp_register_script('ajaxButtonScripts', ISS_RQAQ_PLUGIN_URL . 'assets/js/ajaxButton.js', array('jquery'), '1.0', true);
            $localize_script_args = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            );
            wp_localize_script('ajaxButtonScripts', 'ajaxButton', $localize_script_args);
            wp_enqueue_script('ajaxButtonScripts');
            wp_enqueue_style('button_style', ISS_RQAQ_PLUGIN_URL . 'assets/css/rqaqStyles.css');
        }

        public function iss_rqaq_order_statuses($order_statuses)
        {
            // Status must start with "wc-"
            $order_statuses['wc-quote-open'] = array(
                'label' => _x('Quote Request', 'Order status', 'woocommerce'),
                'public' => true,
                'exclude_from_search' => false,
                'editable' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Quote Open <span class="count">(%s)</span>', 'Quotes Open<span class="count">(%s)</span>', 'woocommerce'),
            );
            $order_statuses['wc-quote-closed'] = array(
                'label' => _x('Send Quote', 'Order status', 'woocommerce'),
                'public' => true,
                'exclude_from_search' => false,
                'editable' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Send Quote <span class="count">(%s)</span>', 'Quotes Closed<span class="count">(%s)</span>', 'woocommerce'),
            );
            return $order_statuses;
        }

        public function iss_quote_status_is_editable($editable, $order)
        {
            if ($order->get_status() == 'quote-open') {
                $editable = true;
            }
            return $editable;
        }

        public function filter_woocommerce_valid_order_statuses_for_payment($statuses, $order)
        {
            $statuses[] = 'quote-closed';
            return $statuses;

        }

        public function iss_rqaq_list_order_statuses($order_statuses)
        {
            $order_statuses['wc-quote-open'] = _x('Quote Request', 'Order status', 'woocommerce');
            $order_statuses['wc-quote-closed'] = _x('Send Quote', 'Order status', 'woocommerce');
            return $order_statuses;
        }

        /**
         * calling the scripts to handle with the ajax
         */
        public function iss_rqaq_form_scripts()
        {
            global $post;
            if ($post && has_shortcode($post->post_content, 'iss_rqaq_shortcode')) {
                wp_register_script('ajaxFormScripts', ISS_RQAQ_PLUGIN_URL . 'assets/js/formValidators.js', array('jquery'), '1.0', true);
                $localize_script_args = array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                );
                wp_localize_script('ajaxFormScripts', 'ajaxForm', $localize_script_args);
                wp_enqueue_script('ajaxFormScripts');
            }
        }
        /**
         * Clean the session
         */
        public function clean_session()
        {
            global $wpdb;
            $query = $wpdb->query("DELETE FROM " . $wpdb->prefix . "options  WHERE option_name LIKE '_ISS_RQAQ_Session_%'");
        }

        /**
         * Get all errors in HTML mode or simple string.
         *
         * @param bool $html
         * @return string
         * @since 1.0.0
         */
        public function get_errors($errors, $html = true)
        {
            return implode(($html ? '<br />' : ', '), $errors);
        }
        /**
         * Locate default templates of woocommerce in plugin, if exists
         *
         * @param $core_file     string
         * @param $template      string
         * @param $template_base string
         *
         * @return string
         * @since  1.0.0
         */
        public function filter_woocommerce_template($core_file, $template, $template_base)
        {
            $located = iss_rqaq_locate_template($template);
            if ($located) {
                return $located;
            } else {
                return $core_file;
            }
        }

        public function ajax_fill_state()
        {
            global $woocommerce;
            $countrycode = $_POST['rqa_country'];
            $allStates = WC()->countries->states;

            if (array_key_exists($countrycode, $allStates)) {
                $states = WC()->countries->states[$countrycode];

            } else {
                $states = array(
                    'error' => 'not listed',
                );

            }

            wp_send_json_success($states);

        }

        /**
         * Clear the list
         */
        public function clear_raq_list(bool $cleanAll = true)
        {

            if ($cleanAll === true) {
                $this->raq_content = array();
            } else {
                if (isset($this->raq_content['fees'])):
                $fees = $this->raq_content['fees'];
                $this->raq_content = array('fees' => $fees);
                else:                 
               
                    $this->raq_content = array();
                endif;

            }

            $this->set_session($this->raq_content, true);
        }

        /**
         * Get all errors in HTML mode or simple string.
         *
         * @return void
         * @since 1.0.0
         */
        public function send_message()
        {
            global $woocommerce;
            $errors = array();
            if (!isset($_POST['rqa_name'])) {
                return;
            } else {

                if (empty($_POST['rqa_name'])) {
                    $errors[] = '<p>' . __('Please enter a name', 'iss-request-a-quote') . '</p>';
                }

                if (!isset($_POST['rqa_email']) || empty($_POST['rqa_email']) || !is_email($_POST['rqa_email'])) {
                    $errors[] = '<p>' . __('Please enter a valid email', 'iss-request-a-quote') . '</p>';
                }

                if (ISS_RQAQ_Request()->is_empty()) {
                    $errors[] = '<p>' . __('Your list is empty, add products to the list to send a request', 'iss-request-a-quote') . '</p>';
                }

                if (empty($errors)) {

                    $args = array(
                        'user_name' => $_POST['rqa_name'],
                        'user_last_name' => $_POST['rqa_last_name'],
                        'user_email' => $_POST['rqa_email'],
                        'user_message' => nl2br($_POST['rqa_message']),
                        'user_phone' => $_POST['rqa_phone'],
                        'user_company' => $_POST['rqa_company'],
                        'user_address1' => $_POST['rqa_address1'],
                        'user_address2' => $_POST['rqa_address2'],
                        'user_city' => $_POST['rqa_city'],
                        'user_state' => $_POST['rqa_state'],
                        'user_postcode' => $_POST['rqa_postcode'],
                        'user_country' => $_POST['rqa_country'],
                        'raq_content' => $this->get_raq_return(),
                        'order_id' => '',
                        '',
                    );

                    $theInfo = $this->create_quote_order($args);                   
                    $args['order_id'] = $theInfo['order-object']->get_order_number();
                    $mailer = WC()->mailer()->get_emails();
                    $mailer['ISS_RQAQ_Send_Email_Request_Quote']->trigger($args);

                    do_action('issrqaq_process', $args);
                    do_action('send_raq_mail', $args);
                    if (!isset($theInfo['password'])) {
                        $response = add_query_arg(array(
                            'email' => $theInfo['user_email'],
                            'response' => 'true',
                            'order_id' => $theInfo['order-object']->get_order_number(),
                        ), ISS_RQAQ_Request()->get_raq_page_url());
                    } else {
                        $response = add_query_arg(array(
                            'email' => $theInfo['user_email'],
                            'response' => 'true',
                            'order_id' => $theInfo['order-object']->get_order_number(),
                            'password' => urlencode($theInfo['password']),

                        ), ISS_RQAQ_Request()->get_raq_page_url());

                    }

                    wp_redirect($response, 301);
                    exit();
                } else {
               
                    wp_redirect(ISS_RQAQ_Request()->get_raq_page_url() . '?response="error"', 301);
                }

            }

            // iss_rqaq_add_notice($this->get_errors($errors), 'error');

        }

        /**
         * Return the url of request quote page
         *
         * @return string
         * @since 1.0.0
         */
        public function get_raq_page_url()
        {
            $option_value = get_option('iss_page_id');

            $base_url = get_the_permalink($option_value);

            return apply_filters('iss_request_page_url', $base_url);
        }

        /**
         * Filters woocommerce available mails, to add wishlist related ones
         *
         * @param $emails array
         *
         * @return array
         * @since 1.0
         */
        public function add_woocommerce_emails($emails)
        {
            $emails['ISS_RQAQ_Send_Email_Request_Quote'] = include ISS_RQAQ_PLUGIN_INC . 'emails/class.iss_quote_email.php';
            $emails['ISS_RQAQ_Send_Email_Quote_Closed'] = include ISS_RQAQ_PLUGIN_INC . 'emails/class.iss_quote_email_quote_closed.php';
            return $emails;
        }

        function add_credit_card_fee( $order, $feePercent, $orderTotal){
               // Get a new instance of the WC_Order_Item_Fee Object
               $item_fee = new WC_Order_Item_Fee();
               $feeAmount = ($feePercent  * $orderTotal ) / 100;
               $item_fee->set_name( "Credit Card Fee" ); // Generic fee name
               $item_fee->set_amount( $feeAmount ); // Fee amount
               $item_fee->set_tax_class( '' ); // default for ''
               $item_fee->set_tax_status( 'none' ); // or 'none'
               $item_fee->set_total( $feeAmount ); // Fee amount
   
               // Calculating Fee taxes
          
   
               // Add Fee item to the order
                 $order->add_item( $item_fee );

            
        }

        /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since 1.0
         */
        public function load_wc_mailer()
        {
            add_action('send_raq_mail', array('WC_Emails', 'send_transactional_email'), 10, 10);

        }

        public function backorder_status_custom_notification($order_id, $order)
        {

            // Getting all WC_emails objects
            $mailer = WC()->mailer()->get_emails();

            $note = 'The quote e-mail was sent to the customer ';
            $order->add_order_note($note);

            $mailer['ISS_RQAQ_Send_Email_Quote_Closed']->trigger($order_id);
            $order->update_status('pending');

        }

        public function iss_rqaq_time_validation_schedule()
        {

            if (!wp_next_scheduled('iss_rqaq_time_validation')) {
                $ve = get_option('gmt_offset') > 0 ? '+' : '-';
                wp_schedule_event(strtotime('00:00 tomorrow ' . $ve . get_option('gmt_offset') . ' HOURS'), 'daily', 'iss_rqaq_time_validation');
            }

            if (!wp_next_scheduled('iss_rqaq_clean_cron')) {
                wp_schedule_event(time(), 'daily', 'iss_rqaq_clean_cron');
            }
        }

        public function iss_rqaq_styling_admin_order_list()
        {
            if (is_admin()) {
                global $pagenow, $post;

                if ($pagenow != 'edit.php') {
                    return;
                }
                // Exit
                if ($post && get_post_type($post->ID) != 'shop_order') {
                    return;
                }
                // Exit

                // HERE below set your custom status
                $quote_open = 'quote-open'; // <==== HERE
                $quote_closed = 'quote-closed';
                ?>
    <style>
        .order-status.status-<?php echo sanitize_title($quote_open); ?> {
            background: #ff9b54;
            color: #94660c;
        }
        .order-status.status-<?php echo sanitize_title($quote_closed); ?> {
            background: #abdb84;
            color: #6b8754;
        }
    </style>
    <?php
}
        }

        public function woocommerce_empty_cart_action()
        {
            if (isset($_GET['empty_cart']) && 'yes' === esc_html($_GET['empty_cart'])) {
                WC()->cart->empty_cart();

                $shop_page_url = get_permalink(wc_get_page_id('shop'));
                wp_safe_redirect($shop_page_url);
            }
        }
        /**
         * initializing the session
         */
        public function start_session()
        {
            $this->session_class = new ISS_RQAQ_Session();
            $this->set_session();
        }

        public function create_quote_order($raq)
        {
            global $woocommerce;
            $address = array(
                'first_name' => $raq['user_name'],
                'last_name' => $raq['user_last_name'],
                'company' => $raq['user_company'],
                'email' => $raq['user_email'],
                'phone' => $raq['user_phone'],
                'address_1' => $raq['user_address1'],
                'address_2' => $raq['user_address2'],
                'city' => $raq['user_city'],
                'state' => $raq['user_state'],
                'postcode' => $raq['user_postcode'],
                'country' => $raq['user_country'],
            );

            $email = $raq['user_email'];
            $exists = email_exists($email);

            if (!empty($exists)):
                $userID = $exists;
            else:
                $default_password = wp_generate_password();
                $user = wp_create_user($email, $default_password, $email);
                $userID = $user;
                $this->send_new_user_email($userID);
            endif;

            // Now we create the order
            $order = wc_create_order(array(
                'customer_id' => $userID,
            )); 

            $cc_fee = false; 

            foreach ($raq['raq_content'] as $index => $item) {
                if ($index !== 'fees'):
                    if ($item['variation_id'] !== 0) {
                        $id_p = $item['variation_id'];
                    } else {
                        $id_p = $item['product_id'];
                    }
                    $order->add_product(wc_get_product($id_p), $item['quantity']);
                else: 
               
                    foreach( $item as $fee => $fee_data){                     
                        if( $fee_data['name'] === 'Credit Card Fee'){
                            $cc_fee = true;
                        }
                            $fee = new WC_Order_Item_Fee();
                            $fee->set_name( $fee_data['name'] ); // Generic fee name
                            $fee->set_amount( $fee_data['amount'] ); // Fee amount
                            $fee->set_tax_class( '' ); // default for ''
                            $fee->set_tax_status( 'taxable' ); // or 'none'
                            $fee->set_total( $fee_data['total'] ); // Fee amount                       
                            // Add Fee item to the order
                            $order->add_item( $fee );                         
                    }
                   
                endif;        
                         
         
           

            };
            error_log(print_r($cc_fee, true ));
            $order->calculate_totals();
            if( $cc_fee === false){
                $orderTotal = $order->get_total();
                $feePercent = 3.5;
                $this->add_credit_card_fee( $order, $feePercent, $orderTotal);
            }
        

            //  add note if exists
            if (!empty($raq['user_message'])):
                $note = 'Customer Quote Note: ' . $raq['user_message'];
                $order->add_order_note($note);
            endif;

            update_user_meta($userID, 'billing_first_name', $raq['user_name']);
            update_user_meta($userID, 'billing_last_name', $raq['user_last_name']);
            update_user_meta($userID, 'billing_email', $raq['user_email']);
            update_user_meta($userID, 'billing_address_1', $raq['user_address1']);
            update_user_meta($userID, 'billing_city', $raq['user_city']);
            update_user_meta($userID, 'billing_state', $raq['user_state']);
            update_user_meta($userID, 'billing_country', $raq['user_country']);
            update_user_meta($userID, 'billing_postcode', $raq['user_postcode']);
            if (isset($raq['user_phone'])) {
                update_user_meta($userID, 'billing_phone', $raq['user_phone']);
            }

            if (isset($raq['user_address2'])) {
                update_user_meta($userID, 'billing_address_2', $raq['user_address2']);
            }

            if (isset($raq['user_company'])) {
                update_user_meta($userID, 'billing_company', $raq['user_company']);
            }

            $order->set_address($address, 'billing');
            $order->set_address($address, 'shipping');
            // Get the customer country code

            // Get a new instance of the WC_Order_Item_Shipping Object
         /*    $shipping = new WC_Order_Item_Shipping();
            $country_code = $order->get_shipping_country();

            $calculate_tax_for = array(
                'country' => $country_code,
                'state' => '', // Can be set (optional)
                'postcode' => '', // Can be set (optional)
                'city' => '', // Can be set (optional)
            );
            

         
            $shipping->set_method_title('Flat');
            $shipping->set_method_id("flat_rate:1"); // set an existing Shipping method rate ID

            $state_Code = $order->get_shipping_state();

            $numberProducts = $order->get_item_count();
            $totalShipping = $numberProducts * 49;

            $shipping->set_total($totalShipping);

            $shipping->calculate_taxes($calculate_tax_for); */
            $this->calculateAndAddShipping($order);
            $order->calculate_totals();

            $country_code = $order->get_shipping_country();
           
           

            $order->update_status('wc-quote-open');

            $order->calculate_totals();

            if (!isset($default_password)):
                $response = array('order-object' => $order, 'user_email' => $email);
            else:
                $response = array('order-object' => $order, 'password' => $default_password, 'user_email' => $email);
            endif;

            $this->backorder_status_custom_notification($order->id, $order);
            return $response;

            exit();
        }

        public function send_new_user_email($userID)
        {
            $wcMail = new WC_Emails();
            $wcMail->customer_new_account($userID);
        }

        public function init()
        {
            $this->get_raq_for_session();
            $this->session_class->set_customer_session_cookie(true);
        }
        public function get_raq_for_session()
        {
            $this->raq_content = $this->session_class->get('raq', array());
            return $this->raq_content;
        }

        /**
         *
         */

        public function set_fee_raq_content($feeJSON)
        {

            $feeJSON = str_replace('\"', '"', $feeJSON);
            $fees = json_decode($feeJSON);

            $this->raq_content['fees'] = array();

            foreach ($fees as $feeItem) {
                if (isset($feeItem->name)):
                    $this->raq_content['fees'][] = array(
                        'name' => $feeItem->name,
                        'id' => $feeItem->id,
                        'amount' => $feeItem->amount,
                        'total' => $feeItem->total,

                    );

                endif;
            }

            $this->set_session($this->raq_content, true);

        }

        function calculateAndAddShipping($order){
      
            $country = $order->get_shipping_country();
            $state = $order->get_shipping_state();
            $postcode = $order->get_shipping_postcode();
            $city = $order->get_shipping_city();
           
            $calculate_tax_for = array(
                'country' => $order->get_shipping_country(),
                'state' =>       $order->get_shipping_state(),
                'postcode' =>   $order->get_shipping_postcode(),
                'city' =>  $order->get_shipping_city(),
            );
            
            
            WC()->shipping()->reset_shipping();
        
          
            // Remove all current items from cart
              if ( sizeof( WC()->cart->get_cart() ) > 0 ) { 
                  $cart_contents = WC()->cart->get_cart_contents();
                  WC()->cart->empty_cart();
        
        
              }
              $order_items    = $order->get_items();
              // Add all items to cart
              foreach ($order_items as $order_item) {
                  WC()->cart->add_to_cart($order_item['product_id'], $order_item['qty']);
              }
        
              // Calculate shipping
              $packages = WC()->cart->get_shipping_packages();           
        
        
            $shipping = WC()->shipping->calculate_shipping($packages);
        
           
            $available_methods = WC()->shipping->get_packages();
            
           
                
            foreach ($available_methods[0]['rates'] as $rateName => $rate) {
                $item = new WC_Order_Item_Shipping();
                    $item->set_props(array('method_title' => $rate->label, 'method_id' => $rate->id, 'total' => wc_format_decimal($rate->cost), 'taxes' => $rate->taxes));
                    $order->add_item($item);
                
            }
           
            // empty and add the old items to the cart
            if(isset($cart_contents)){
             
                WC()->cart->empty_cart();
                WC()->cart->set_cart_contents($cart_contents);
        
               
            } else {
              
                WC()->cart->empty_cart();
            }
           
            
          }
        
        /**
         *
         * set raq CONTENNT FROM CART
         */
        public function set_raq_content()
        {

            global $woocommerce;

            $cart = WC()->cart->get_cart_contents();
            foreach ($cart as $raqItem) {

                $this->raq_content[] = array(
                    'product_id' => $raqItem['product_id'],
                    'variation_id' => $raqItem['variation_id'],
                    'quantity' => $raqItem['quantity'],

                );

            }
            $this->set_session($this->raq_content, true);

        }
        /**
         *
         * get raq return()
         */
        public function get_raq_return()
        {
            return $this->raq_content;
        }

        /**
         * Sets the php session data for the request a quote
         *
         * @since  1.0.0
         * @return void
         * @author Emanuela Castorina
         */
        public function set_session($raq_session = array(), $can_be_empty = false)
        {
            if (empty($raq_session) && !$can_be_empty) {
                $raq_session = $this->get_raq_for_session();
            }

            // Set raq  session data
            $this->session_class->set('raq', $raq_session);

        }

        public function get_raq_item_number()
        {
            return count($this->raq_content);
        }

        public function is_empty()
        {
            return empty($this->raq_content);
        }

        /**
         * Set Request a quote cookie
         *
         * @since  1.0.0
         * @return void
         * @author Emanuela Castorina
         */
        public function maybe_set_raq_cookies()
        {
            $set = true;

            if (!headers_sent()) {
                if (sizeof($this->raq_content) > 0) {
                    $this->set_rqa_cookies(true);
                    $set = true;
                } elseif (isset($_COOKIE['iss_rqaq_items_in_raq'])) {
                    $this->set_rqa_cookies(false);
                    $set = false;
                }
            }

            do_action('iss_rqaq_set_raq_cookies', $set);
        }

        /**
         * Set hash cookie and items in raq.
         *
         * @since  1.0.0
         * @access private
         * @return voidd
         * @author Emanuela Castorina
         */
        private function set_rqa_cookies($set = true)
        {
            if ($set) {
                wc_setcookie('iss_rqaq_items_in_raq', 1);
                wc_setcookie('iss_rqaq_hash', md5(json_encode($this->raq_content)));
            } elseif (isset($_COOKIE['iss_rqaq_items_in_raq'])) {
                wc_setcookie('iss_rqaq_items_in_raq', 0, time() - HOUR_IN_SECONDS);
                wc_setcookie('iss_rqaq_hash', '', time() - HOUR_IN_SECONDS);
            }
            do_action('iss_rqaq_set_cookies', $set);
        }

        /**
         * Check if the product is in the list
         */
        public function exists($product_id, $variation_id = false)
        {

            if ($variation_id) {
                //variation product
                $key_to_find = md5($product_id . $variation_id);
            } else {
                $key_to_find = md5($product_id);
            }

            if (array_key_exists($key_to_find, $this->raq_content)) {
                $this->errors[] = __('Product already in the list.', 'iss-request-a-quote');
                return true;
            }

            return false;
        }

        /**
         * Add an item to request quote list
         */
        public function add_item($product_raq)
        {

            if (empty($product_raq['product_id']) || !is_numeric($product_raq['product_id'])) {
                return;
            }

            $product_raq['quantity'] = (isset($product_raq['quantity'])) ? (int) $product_raq['quantity'] : 1;

            $return = '';
            if (!isset($product_raq['variation_id'])) {
                //single product
                if (!$this->exists($product_raq['product_id'])) {
                    $raq = array(
                        'product_id' => $product_raq['product_id'],
                        'quantity' => $product_raq['quantity'],
                    );

                    $this->raq_content[md5($product_raq['product_id'])] = $raq;

                } else {
                    $key = md5($product_raq['product_id']);
                    $raq = $this->raq_content[$key];

                    if ($raq['quantity'] == $product_raq['quantity']) {
                        $return = 'exists';
                    } elseif ($raq['quantity'] != 0) {
                        ISS_RQAQ_Request()->update_item($key, 'quantity', $product_raq['quantity']);
                        $return = 'updated';
                    }
                }
            } else {
                //variable product
                if (!$this->exists($product_raq['product_id'], $product_raq['variation_id'])) {

                    $raq = array(
                        'product_id' => $product_raq['product_id'],
                        'variation_id' => $product_raq['variation_id'],
                        'quantity' => $product_raq['quantity'],
                    );

                    $this->raq_content[md5($product_raq['product_id'] . $product_raq['variation_id'])] = $raq;

                } else {
                    $raq_content = $this->raq_content;
                    $key = md5($product_raq['product_id'] . $product_raq['variation_id']);
                    $raq = $raq_content[$key];
                    if ($product_raq['quantity'] == $raq['quantity']) {
                        $return = 'exists';
                    } elseif ($product_raq['quantity'] != 0) {
                        ISS_RQAQ_Request()->update_item($key, 'quantity', $product_raq['quantity']);
                        $return = 'updated';
                    } else {
                        ISS_RQAQ_Request()->remove_item($key);
                        $return = 'removed';
                    }

                }
            }

            if ($return != 'exists' && $return != 'updated' && $return != 'removed') {

                $this->set_session($this->raq_content);

                $return = 'true';
                $this->set_rqa_cookies(sizeof($this->raq_content) > 0);
            }
            return $return;
        }
        public function ajax_update_item()
        {
            $product = $_POST;
            $key = $product['key'];
            $return = 'false';
            if ($product['qty'] != 0) {
                ISS_RQAQ_Request()->update_item($key, 'quantity', $product['qty']);
                $return = 'updated';
            } else {
                ISS_RQAQ_Request()->remove_item($key);
                $return = 'removed';
            }

            if ($return == 'updated') {
                $message = 'Your list Was Updated';
            } elseif ($return == 'removed') {
                $message = 'Product Removed from your List';
            } else {
                $message = 'Nothing was done';
            }
            wp_send_json_success($message);

        }

        public function update_item($key, $field = false, $value)
        {

            if ($field && isset($this->raq_content[$key][$field])) {
                $this->raq_content[$key][$field] = $value;
                $this->set_session($this->raq_content);

            } elseif (isset($this->raq_content[$key])) {
                $this->raq_content[$key] = $value;
                $this->set_session($this->raq_content);
            } else {
                return false;
            }

            $this->set_session($this->raq_content);
            return true;
        }

        public function ajax()
        {
            if (isset($_POST['task'])) {
                if (method_exists($this, 'ajax_' . $_POST['task'])) {
                    $s = 'ajax_' . $_POST['task'];
                    $this->$s();
                }
            }
        }

        /**
         * Creating the button request a quote on single product pages
         */

        public function iss_rqaq_button()
        {
            $postUrl = esc_url(admin_url('admin-post.php'));
            $action = 'add_items_to_rqaq';
            $url = $postUrl . '?action=' . $action;
            $fees = json_encode(WC()->cart->get_fees());

            $html = '<form  action="' . $postUrl . '" method="post">';
            $html .= '<input type="hidden" name="action" value="' . $action . '">';
            if (!empty($fees)):
            $html .= "<input type='hidden' name='fees' value='$fees'>";
            endif;
            $html .= '<input type="submit" value="Request a Quote" class="create_quote_cart">';
            $html .= '</form>';

            echo $html;

            return;

        }

        /**
         * Add an item in the list from query string
         * for example ?add-to-quote=%product_id%=%quantity=%quantity%
         */
        public function add_fee_to_raq_content()
        {
            $this->clear_raq_list();
            $fessFromCart = array();

            $fees = $_POST['fees'];

            $addingFessToRaq = $this->set_fee_raq_content($fees);

            $this->quote_page_redirect();
            return;

        }

        public function quote_page_redirect()
        {
            $url = $this->get_raq_page_url();
            wp_redirect($url);
        }
        public function ajax_add_item_to_cart($itemInCartRaq)
        {

            $return = 'false';
            $message = '';
            $errors = array();

            $product_id = (isset($itemInCartRaq['product_id']) && is_numeric($itemInCartRaq['product_id'])) ? (int) $itemInCartRaq['product_id'] : false;
            $is_valid_variation = isset($itemInCartRaq['variation_id']) ? !((empty($itemInCartRaq['variation_id']) || !is_numeric($itemInCartRaq['variation_id']))) : true;
            $product_raq['quantity'] = (isset($itemInCartRaq['quantity'])) ? (int) $itemInCartRaq['quantity'] : 1;
            $is_valid = $is_valid_variation;

            if (!$is_valid) {
                $errors[] = __('Error occurred while adding product to Request a Quote list.', 'iss-request-a-quote');
            } else {
                return $this->add_item($itemInCartRaq);
            }

            if ($return == 'true') {
                $message = apply_filters('iss_rqaqproduct_added_to_list_message', __('Product added to your quote list!', 'iss-request-a-quote'));
            } elseif ($return == 'exists') {
                $message = apply_filters('iss_rqaqproduct_already_in_list_message', __('Product already in the list.', 'iss-request-a-quote'));
            } elseif ($return == 'removed') {
                $message = apply_filters('iss_rqaqproduct_remove_from_list_message', __('Product removed from your list.', 'iss-request-a-quote'));
            } elseif ($return == 'updated') {
                $message = apply_filters('iss_rqaqproduct_Updated_in_list_message', __('Your list was updated.', 'iss-request-a-quote'));
            } elseif (count($errors) > 0) {
                $message = apply_filters('iss_rqaqerror_adding_to_list_message', $this->get_errors($errors));
            }
            wp_send_json_success($message);

        }

        public function remove_item($key)
        {

            if (isset($this->raq_content[$key])) {
                unset($this->raq_content[$key]);
                $this->set_session($this->raq_content, true);

                if (isset($_COOKIE['iss_rqaq_items_in_raq'])) {
                    $message = apply_filters('iss_rqaqproduct_remove_in_list_message', __('Product Removed.', 'iss-request-a-quote'));
                }
                wp_send_json_success($message);
                return true;
            } else {
                return false;
            }
        }

        public function ajax_remove_item()
        {
            $product_id = (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) ? (int) $_POST['product_id'] : false;
            $is_valid = $product_id && isset($_POST['key']);

            if ($is_valid) {
                echo $this->remove_item($_POST['key']);
            } else {
                echo false;
            }
            die();
        }

    }
}

/**
 *  unique acccess to instance of the ISS_RQAQ_Request class
 *
 * @return \ISS_RQAQ_Request
 *
 */

function ISS_RQAQ_Request()
{
    return ISS_RQAQ_Request::get_instance();
}

ISS_RQAQ_Request();