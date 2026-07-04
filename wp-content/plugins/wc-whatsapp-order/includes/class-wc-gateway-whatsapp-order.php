<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_WhatsApp_Order extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'whatsapp_order';
		$this->method_title       = 'Comandă pe WhatsApp';
		$this->method_description = 'Trimite comanda direct pe WhatsApp vânzătorului, fără procesare de plată.';
		$this->has_fields         = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'         => array(
				'title'   => 'Activează/Dezactivează',
				'type'    => 'checkbox',
				'label'   => 'Activează Comandă pe WhatsApp',
				'default' => 'no',
			),
			'title'           => array(
				'title'       => 'Titlu',
				'type'        => 'text',
				'description' => 'Titlul pe care îl vede clientul la checkout.',
				'default'     => 'Comandă pe WhatsApp',
				'desc_tip'    => true,
			),
			'description'     => array(
				'title'       => 'Descriere',
				'type'        => 'textarea',
				'description' => 'Descrierea pe care o vede clientul la checkout.',
				'default'     => 'Trimite comanda direct pe WhatsApp vânzătorului.',
				'desc_tip'    => true,
			),
			'whatsapp_number' => array(
				'title'       => 'Număr WhatsApp',
				'type'        => 'text',
				'description' => 'Numărul WhatsApp al magazinului, format internațional, fără "+" și fără 0 la început (ex: 40712345678 pentru un număr românesc).',
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}
}
