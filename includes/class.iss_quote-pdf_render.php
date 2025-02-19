<?php
use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists('ISS_RQAQ_PDF_Maker' ) ) :

class ISS_RQAQ_PDF_Maker {
	public $html;
	public $settings;

	public function __construct( $html, $settings = array() ) {
		$this->html = $html;

		$default_settings = array(
			'paper_size'		=> 'A4',
			'paper_orientation'	=> 'portrait',
			'font_subsetting'	=> false,
		);
		$this->settings = $settings + $default_settings;
	}

	public function output() {
		if ( empty( $this->html ) ) {
			return;
		}
		
		require ISS_RQAQ_PLUGIN_DIR .  '/vendor/autoload.php';

		// set options
		$options = new Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'defaultFont'				=> 'helvetica',
					
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> true,
		) ) );

		// instantiate and use the dompdf class
		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html );

		return $dompdf->output();
	}
}
endif; // class_exists