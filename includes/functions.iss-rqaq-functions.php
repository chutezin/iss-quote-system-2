<?php
if ( !function_exists( 'iss_rqaq_notice_count' ) ) {
	/****** NOTICES *****/
	/**
	 * Get the count of notices added, either for all notices (default) or for one
	 * particular notice type specified by $notice_type.
	 *
	 * @since 2.1
	 *
	 * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
	 *
	 * @return int
	 */
	function iss_rqaq_notice_count( $notice_type = '' ) {
		$session      =ISS_RQAQ_Request()->session_class;
		$notice_count = 0;
		$all_notices  = $session->get( 'iss_rqaq_notices', array() );

		if ( isset( $all_notices[ $notice_type ] ) ) {
			$notice_count = absint( sizeof( $all_notices[ $notice_type ] ) );
		} elseif ( empty( $notice_type ) ) {
			$notice_count += absint( sizeof( $all_notices ) );
		}

		return $notice_count;
	}
}

// if( !function_exists('iss_rqaq_add_notice') ){
/**
	 * Add and store a notice
	 *
	 * @since 2.1
	 *
	 * @param string $message The text to display in the notice.
	 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
	 */
// 	function iss_rqaq_add_notice( $message, $notice_type = 'success' ) {

// 		$session =ISS_RQAQ_Request()->session_class;
// 		$notices = $session->get( 'iss_rqaq_notices', array() );

// 		// Backward compatibility
// 		if ( 'success' === $notice_type ) {
// 			$message = apply_filters( 'iss_rqaq_add_message', $message );
// 		}

// 		$notices[ $notice_type ][] = apply_filters( 'iss_rqaq_add' . $notice_type, $message );

// 		$session->set( 'iss_iss_rqaq_notices', $notices );

// 	}
// }
  

if ( !function_exists( 'iss_rqaq_print_notices' ) ) {
	/**
	 * Prints messages and errors which are stored in the session, then clears them.
	 *
	 * @since 2.1
	 */
	function iss_rqaq_print_notices() {

		if ( get_option( 'iss_rqaq_activate_thank_you_page' ) == 'yes' ) {
			return '';
		}

		$session      =ISS_RQAQ_Request()->session_class;
		$all_notices  = $session->get( 'iss_rqaq_notices', array() );
		$notice_types = apply_filters( 'iss_rqaq_notice_types', array( 'error', 'success', 'notice' ) );

		foreach ( $notice_types as $notice_type ) {
			if ( iss_rqaq_notice_count( $notice_type ) > 0 ) {
				if ( count( $all_notices ) > 0 && $all_notices[ $notice_type ] ) {
					wc_get_template( "notices/{$notice_type}.php", array(
						'messages' => $all_notices[ $notice_type ]
					) );
				}
			}
		}

		iss_rqaq_clear_notices();
	}
}

if ( !function_exists( 'iss_rqaq_clear_notices' ) ) {
	/**
	 * Unset all notices
	 *
	 * @since 2.1
	 */
	function iss_rqaq_clear_notices() {
		$session = ISS_RQAQ_Request()->session_class;
		$session->set( 'iss_rqaq_notices', null );
    }
    
}

// if( !function_exists('iss_rqaq_get_pdf_maker') ){
	
// 	function iss_rqaq_get_pdf_maker( $html, $settings = array() ) {
// 		if ( ! class_exists( 'ISS_RQAQ_PDF_Maker' ) ) {
// 			include_once( ISS_RQAQ_PLUGIN_DIR .  '/includes/class.iss_quote-pdf_render.php' );
// 		}
// 		$class = 'ISS_RQAQ_PDF_Maker';
// 		return new $class(  $html, $settings = array()  );
// 	}


// }

// if(!function_exists('iss_rqaq_pdf_headers')){
	
// function iss_rqaq_pdf_headers( $filename, $mode = 'inline', $pdf = null ) {
// 	switch ($mode) {
// 		case 'download':
// 			header('Content-Description: File Transfer');
// 			header('Content-Type: application/pdf');
// 			header('Content-Disposition: attachment; filename="'.$filename.'"'); 
// 			header('Content-Transfer-Encoding: binary');
// 			header('Connection: Keep-Alive');
// 			header('Expires: 0');
// 			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
// 			header('Pragma: public');
// 			break;
// 		case 'inline':
// 		default:
// 			header('Content-type: application/pdf');
// 			header('Content-Disposition: inline; filename="'.$filename.'"');
// 			break;
// 	}
// }

// }

if ( !function_exists( 'iss_rqaq_get_product_meta' ) ) {
	/**
	 * Return the product meta in a varion product
	 *
	 * @param array $raq
	 * @param bool  $echo
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function iss_rqaq_get_product_meta( $raq, $echo = true, $show_price = true ) {

		$item_data = array();

		// Variation data
		if ( !empty( $raq['variation_id'] ) && is_array( $raq['variations'] ) ) {

			foreach ( $raq['variations'] as $name => $value ) {

				if ( '' === $value ) {
					continue;
				}

				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				// If this is a term slug, get the term's nice name
				if ( taxonomy_exists( $taxonomy ) ) {
					$term = get_term_by( 'slug', $value, $taxonomy );
					if ( !is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );

				}else {
					if( strpos($name, 'attribute_') !== false ) {
						$custom_att = str_replace( 'attribute_', '', $name );
						if ( $custom_att != '' ) {
							$label = wc_attribute_label( $custom_att );
						}
						else {
							$label = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $name ), $name );
							// $label = $name;
						}
					}
				}

				$item_data[] = array(
					'key'   => $label,
					'value' => $value
				);


			}
		}

		$item_data = apply_filters( 'ywraq_item_data', $item_data, $raq, $show_price );

		$carrets = apply_filters('ywraq_meta_data_carret', "\n" );

		$out = $echo ? $carrets : "";

		// Output flat or in list format
		if ( sizeof( $item_data ) > 0 ) {
			foreach ( $item_data as $data ) {
				if ( $echo ) {
					$out .= esc_html(  $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . $carrets;
				}
				else {
					$out .= ' - ' . esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['value'] ) . ' ';
				}
			}
		}

		if ( $echo ) {
			echo $out;
		}else{
			return $out;
		}

		return '';
	}


}

function yith_ywraq_email_custom_tags( $text, $tag, $html){

    if( $tag == 'yith-request-a-quote-list' ){
        return yith_ywraq_get_email_template($html);
    }
}

function yith_ywraq_get_email_template( $html ) {
    $raq_data['raq_content'] = ISS_RQAQ_Request()->get_raq_return();
    ob_start();
    if ( $html ) {
        wc_get_template( 'emails/request-quote-table.php', array(
            'raq_data' => $raq_data
        ) );
    }
    else {
        wc_get_template( 'emails/plain/request-quote-table.php', array(
            'raq_data' => $raq_data
        ) );
    }
    return ob_get_clean();
}


if ( !function_exists( 'iss_rqaq_locate_template' ) ) {
	/**
	 * Locate the templates and return the path of the file found
	 *
	 * @param string $path
	 * @param array  $var
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function iss_rqaq_locate_template( $path, $var = NULL ) {

		if ( function_exists( 'WC' ) ) {
			$woocommerce_base = WC()->template_path();
		}
		elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
			$woocommerce_base = WC_TEMPLATE_PATH;
		}
		else {
			$woocommerce_base = WC()->plugin_path() . '/templates/';
		}

		$template_woocommerce_path = $woocommerce_base . $path;
		$template_path             = '/' . $path;
		$plugin_path               = ISS_RQAQ_PLUGIN_DIR . 'templates/' . $path;

		$located = locate_template( array(
			$template_woocommerce_path, // Search in <theme>/woocommerce/
			$template_path,             // Search in <theme>/
			$plugin_path                // Search in <plugin>/templates/
		) );

		if ( !$located && file_exists( $plugin_path ) ) {
			return apply_filters( 'iss_rqaq_locate_template', $plugin_path, $path );
		}

		return apply_filters( 'iss_rqaq_locate_template', $located, $path );
	}
}