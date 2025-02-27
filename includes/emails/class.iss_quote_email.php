<?php
if ( !defined( 'ABSPATH' )) {
    exit; // Exit if accessed directly
}
/**
 * Implements features of YITH Woocommerce Request A Quote
 *
 * @class   ISS_RQAQ_Send_Email_Request_Quote
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */
if ( !class_exists( 'ISS_RQAQ_Send_Email_Request_Quote' ) ) {
    /**
     * ISS_RQAQ_Send_Email_Request_Quote
     *
     * @since 1.0.0
     */
    class ISS_RQAQ_Send_Email_Request_Quote extends WC_Email {
        /**
         * Constructor method, used to return object of the class to WC
         *
         * @return \ISS_RQAQ_Send_Email_Request_Quote
         * @since 1.0.0
         */
        public function __construct() {
            $this->id          = 'iss_raq_email';
            $this->title       = __( 'Email to request a quote', 'iss-request-a-quote' );
            $this->description = __( 'This email is sent when a user clicks on "Request a quote" button', 'iss-request-a-quote' );
            $this->template_base  =  ISS_RQAQ_PLUGIN_DIR . 'templates/';
            $this->template_html  = 'emails/request_quote.php';
            $this->template_plain = 'emails/plain/request_quote.php';
            // Call parent constructor
            parent::__construct();
            // Other settings
            $this->recipient = $this->get_option( 'recipient' );
            if ( !$this->recipient ) {
                $this->recipient = get_option( 'admin_email' );            }
            $this->enable_cc = $this->get_option( 'enable_cc' );
            $this->enable_cc = $this->enable_cc == 'no';
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
        public function trigger( $args ) {
            $this->raq = $args;
            $recipients = (array) $this->get_recipient();
            if( $this->enable_cc ){
                $recipients[] =  $this->raq['user_email'];
            }
            $this->subject =  'New Quote Request - #' . $args['order_id'] . ' - ' . $args['user_company'];
            $recipients = implode(',',$recipients);
            $return = $this->send( $recipients, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            if ( $return ) {      
                ISS_RQAQ_Request()->clear_raq_list(false);
                // iss_rqaq_add_notice( __('Your request has been sent successfully','iss-request-a-quote'), 'success' );
            }else {
                // iss_rqaq_add_notice( __( 'There was a problem in sending your request. Please try again.', 'iss-request-a-quote' ), 'error' );
            }
        }
        /**
         * get_headers function.
         *
         * @access public
         * @return string
         */
        function get_headers() {
            $headers = "Reply-to: " . $this->raq['user_email'] . "\r\n";
            if ( $this->enable_cc ) {
                $headers .= "Cc: " . $this->raq['user_email'] . "\r\n";
            }
            $headers .= "Content-Type: " . $this->get_content_type() . "\r\n";
            return apply_filters( 'woocommerce_email_headers', $headers, $this->id, $this->object );
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
                'raq_data'      => $this->raq,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
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
                'raq_data'      => $this->raq,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => false,               
            ) );
            return ob_get_clean();
        }
        /**
         * Get from name for email.
         *
         * @return string
         */
        // public function get_from_name() {
        //     $from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
        // return sanitize_email( $from_address );
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
                'recipient'  => array(
                    'title'       => __( 'Recipient(s)', 'iss-request-a-quote' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>', 'iss-request-a-quote' ), esc_attr( get_option( 'admin_email' ) ) ),
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
return new ISS_RQAQ_Send_Email_Request_Quote();