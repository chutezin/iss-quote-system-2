<?php 
if( !defined( 'ABSPATH') ){
    exit; // Exit if accessed directly
}
/**
 * creating the class adminstrator for ISS request a quote
 *
 * @class ISS_RQAQ_Admin
 * @package ISS Quote System
 * @since   1.0.0
 * @author  chutes 
 */

if ( ! class_exists( 'ISS_RQAQ_Admin') ) {
    class ISS_RQAQ_Admin{

        /**
         * Single instance of the class
         * 
         * @var \RQAQ
         */
        protected static $instance;
        /**
         * Returns single instance of the class
         *
         * @return \ISS_RQAQ_Admin
         * @since 1.0.0
         */
     

        public static function get_instance(){
            if( is_null( self::$instance ) ){
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
       

        public function __construct(){           
           
            
            //  adds the quote page
            add_action( 'init', array( $this, 'add_quote_page' ) );
            // add_action( 'add_meta_boxes_shop_order', array( $this, 'add_meta_boxes' ) );

            // add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_email_pdf' ) );
            // add_action( 'woocommerce_init', array( $this, 'load_wc_mailer_pdf' ) );
         
            // enqueue scripts 
            // add_action( 'admin_enqueue_scripts', array( $this,  'button_pdf_scripts') );

            // add_action( 'wp_ajax_ajax_pdf_generator', array( $this, 'ajax_pdf_generator' ) );
            // add_action( 'wp_ajax_nopriv_ajax_pdf_generator', array( $this, 'ajax_pdf_generator' ) );
            
        }
        function button_pdf_scripts() {
            // wp_register_script( 'jsPdfMin', ISS_RQAQ_PLUGIN_URL . 'assets/js/jspdf.min.js', , '1.0', true );
            // wp_register_script( 'jsPdfDebug', ISS_RQAQ_PLUGIN_URL . 'assets/js/jspdf.debug.js', array() , '1.0', true );
            wp_register_script( 'ajaxPdfScripts', ISS_RQAQ_PLUGIN_URL . 'assets/js/ajaxButtonPdf.js', array('jquery'), '1.0', true );
                    $localize_script_args = array(
                        'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                    );
         wp_localize_script( 'ajaxPdfScripts', 'ajaxPdf', $localize_script_args );
            wp_enqueue_script( 'ajaxPdfScripts' );
            // wp_enqueue_script( 'jsPdfDebug' );  
         
          }
       
        

        public function ajax_pdf_generator(){
         
       
            // instantiate and use the dompdf class
          
            $orderId = $_POST['order_number_rqa'];
            $html = $orderId;
            $pdf_maker = iss_rqaq_get_pdf_maker($html );
           $pdf = $pdf_maker->output();
           iss_rqaq_pdf_headers( 'somename.pdf', 'lnline', $pdf );

          return $pdf;
           die();
          }
        
         
        function add_meta_boxes(){
            	// create PDF buttons
		add_meta_box(
			'iss_rqaq_create_pdf',
			__( 'Create Quote PDF', 'woocommerce-PDF-invoices' ),
			array( $this, 'pdf_actions_meta_box' ),
			'shop_order',
			'side',
			'default'
		);
        }
        public function add_woocommerce_email_pdf( $emails) { 
            $emails['ISS_RQAQ_Send_Email_Request_Quote_PDF'] = include( ISS_RQAQ_PLUGIN_INC  . 'emails/class.iss_quote_email_pdf.php'  );
            return $emails;           
        }
       
            /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since 1.0
         */
        public function load_wc_mailer_pdf() {
            add_action( 'send_raq_mail_pdf', array( 'WC_Emails', 'send_transactional_email' ), 10, 10 );
        
        }  

        public function pdf_actions_meta_box( $post ) {
            global $post_id;
            ?>
         
            <ul class="iss_rqaq_pdf-actions">
              <li> <button href="#" data-order-number-rqaq="<?php echo $post_id?>"class="print_pdf">Create Pdf</button></li>
              <li class="response"></li>
            </ul>
            <?php
        }
    
        
        function add_quote_page(){
            function create_page_rqaq(){
                global $wpdb;

                $option_value = get_option( 'iss_page_id' );
                if ( $option_value > 0 && get_post( $option_value ) )
                return;

                $page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'request-quote-iss' LIMIT 1;" );
                if ( $page_found ) :
                    if ( ! $option_value )
                        update_option( 'iss_page_id', $page_found );
                    return;
                endif;

                $page_data = array(
                    'post_status' 		=> 'publish',
                    'post_type' 		=> 'page',
                    'post_author' 		=> 1,
                    'post_name' 		=> esc_sql( _x( 'request-quote-iss', 'page_slug', 'iss' ) ),
                    'post_title' 		=> 'Request a Quote',
                    'post_content' 		=> '[iss_rqaq_shortcode]',
                    'post_parent' 		=> 0,
                    'comment_status' 	=> 'closed'
                );
                wp_insert_post( $page_data );
                $page_id = wp_insert_post( $page_data );

                update_option( 'iss_page_id', $page_id );
               }
                     
               
            if( get_page_by_title( 'Request a Quote') == NULL){
                create_page_rqaq();
                
            }
         

          
                       
          
        }

    }
}

/** 
 *  unique acccess to instance of the ISS_RQAQ_Admin class
 * 
 * @return \ISS_RQAQ_Admin
 * 
*/

function ISS_RQAQ_Admin(){
    return ISS_RQAQ_Admin::get_instance();
}

ISS_RQAQ_Admin();