<?php

/**
 * Allow including of Gutenberg Template
 *
 * @since 2.0.1
 */
class Tribe__Tickets__NYP__Template extends Tribe__Tickets_Plus__Template {

	/**
	 * Building of the Class template configuration
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->set_template_origin( WC_NYP_Tickets::instance() );
		$this->set_template_folder( 'views' );

		// Configures this templating class to extract variables.
		$this->set_template_context_extract( true );

		// Uses the public folders.
		$this->set_template_folder_lookup( true );
	}

}

