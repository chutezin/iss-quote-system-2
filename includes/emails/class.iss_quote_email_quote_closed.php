<?php
if ( !defined( 'ABSPATH' )) {
    exit; // Exit if accessed directly
}
/**
 * Implements features of ISS Woocommerce Request A Quote
 *
 * @class   ISS_RQAQ_Send_Email_Quote_Closed
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */
if ( !class_exists( 'ISS_RQAQ_Send_Email_Quote_Closed' ) ) {

    /**
     * ISS_RQAQ_Send_Email_Quote_Closed
     *
     * @since 1.0.0
     */
    class ISS_RQAQ_Send_Email_Quote_Closed extends WC_Email {
        

        /**
         * Constructor method, used to return object of the class to WC
         *
         * @return \ISS_RQAQ_Send_Email_Quote_Closed
         * @since 1.0.0
         */
        public function __construct() {
            $this->id          = 'iss_raq_email_quote_closed';
            $this->title       = __( 'Quotation E-mail', 'iss-request-a-quote' );
            $this->description = __( 'This email will be sent for the customer when the request is closed by our team', 'iss-request-a-quote' );
            $this->customer_email = true;
            $this->heading = __( 'Thank you', 'iss-request-a-quote' );

            $this->template_base  =  ISS_RQAQ_PLUGIN_DIR . 'templates/';
            $this->template_html  = 'emails/quote_closed.php';
            $this->template_plain = 'emails/plain/quote_closed.php';



            // Call parent constructor
            parent::__construct();

            // Other settings
          
            $this->enable_cc = $this->get_option( 'enable_cc' );
            $this->enable_cc = $this->enable_cc == 'yes';
        }

        /**
         * Method triggered to send email
         *
         * @param int $args
         *
         * @return void
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */

	public function trigger( $order_id, $order = false ) {
            
            $this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
            }
            $CompanyName = null !== $order->get_billing_company() ? $order->get_billing_company() : '';
            
            $this->subject =  'New Quote - #' . $order->get_id() . ' - ' .  $CompanyName;

			if ( $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		
          
        }

        
        /**
         * Get HTML content for the mail
         *
         * @return string HTML content of the mail
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function get_content_html() {
            ob_start();
            wc_get_template( $this->template_html, array(
                'order'         => $this->object,
                'email_heading' =>  'Here is your quote!',
                'email'         => $this->object->get_billing_email(),
                'sent_to_admin' => false,
                'plain_text'    => false,              
            ) );
            return ob_get_clean();
        }
        /**
         * Get plain text content of the mail
         *
         * @return string Plain text content of the mail
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function get_content_plain() {
            ob_start();
            wc_get_template( $this->template_plain, array(
                'order'         => $this->object,
                'email'         => $this->object->get_billing_email(),
                'email_heading' =>  'Here is your quote!',
                'sent_to_admin' => true,
                'plain_text'    => false,               
            ) );
            return ob_get_clean();
        }

        // /**
        //  * Get from name for email.
        //  *
        //  * @return string
        //  */
        // public function get_from_name() {
        //     $email_from_name = ( isset($this->settings['email_from_name']) && $this->settings['email_from_name'] != '' ) ? $this->settings['email_from_name'] : '';

        //     return wp_specialchars_decode( esc_html( $email_from_name ), ENT_QUOTES );
        // }

        // /**
        //  * Get from email address.
        //  *
        //  * @return string
        //  */
        // public function get_from_address() {
        //     $email_from_email = ( isset($this->settings['email_from_email']) && $this->settings['email_from_email'] != '' ) ? $this->settings['email_from_email'] : '';
        //     return sanitize_email( $email_from_email );
        // }

        /**
         * Init form fields to display in WC admin pages
         *
         * @return void
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'         => __( 'Enable/Disable', 'iss-request-a-quote' ),
                    'type'          => 'checkbox',
                    'label'         => __( 'Enable this email notification', 'iss-request-a-quote' ),
                    'default'       => 'yes'
                ),
                'email_from_name'    => array(
                    'title'       => __( '"From" Name', 'iss-request-a-quote' ),
                    'type'        => 'text',
                    'description' => '',
                    'placeholder' => '',
                    'default'     => get_option( 'woocommerce_email_from_name' )
                ),
                'email_from_email'    => array(
                    'title'       => __( '"From" Email Address', 'iss-request-a-quote' ),
                    'type'        => 'text',
                    'description' => '',
                    'placeholder' => '',
                    'default'     => get_option( 'woocommerce_email_from_address' )
                ),
                'subject'    => array(
                    'title'       => __( 'Subject', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This field lets you modify the email subject line. Leave it blank to use default subject: <code>%s</code>.', 'iss-request-a-quote' ), $this->subject ),
                    'placeholder' => '',
                    'default'     => ''
                ),               
                'enable_cc'  => array(
                    'title'       => __( 'Send CC copy', 'iss-request-a-quote' ),
                    'type'        => 'checkbox',
                    'description' => __( 'Send a carbon copy to the user', 'iss-request-a-quote' ),
                    'default'     => 'no'
                ),
                'heading'    => array(
                    'title'       => __( 'Email Heading', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This field lets you modify the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'iss-request-a-quote' ), $this->heading ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Choose format for the email to be sent.', 'woocommerce' ),
                    'default'     => 'html',
                    'class'       => 'email_type',
                    'options'     => array(
                        'plain'     => __( 'Plain text', 'woocommerce' ),
                        'html'      => __( 'HTML', 'woocommerce' ),
                        'multipart' => __( 'Multipart', 'woocommerce' ),
                    )
                )
            );
        }
    }
}


// returns instance of the mail on file include
return new ISS_RQAQ_Send_Email_Quote_Closed();