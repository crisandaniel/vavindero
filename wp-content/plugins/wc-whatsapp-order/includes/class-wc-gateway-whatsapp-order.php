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

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( 'on-hold', 'Comandă plasată prin WhatsApp, în așteptarea confirmării vânzătorului.' );
		wc_reduce_stock_levels( $order_id );

		WC()->cart->empty_cart();

		/*
		 * Returning the full wa.me URL (with the multi-line message)
		 * directly here doesn't survive intact: WooCommerce's block-based
		 * Checkout (Store API) serializes this "redirect" value through a
		 * schema-validated JSON response, which strips %0a/%0d sequences
		 * from it - confirmed by testing wa.me's own redirect directly
		 * (it preserves %0a perfectly) versus calling process_payment()
		 * through the real checkout flow (the %0a vanishes with no
		 * trace). So instead we redirect to our own small handler below,
		 * which does a raw wp_redirect() - a plain HTTP Location header,
		 * not a JSON response field - straight to wa.me. That one extra,
		 * instant hop is what actually reaches WhatsApp with the message
		 * intact.
		 */
		$order->update_meta_data( '_whatsapp_order_message', $this->build_order_message( $order ) );
		$order->save();

		return array(
			'result'   => 'success',
			'redirect' => add_query_arg(
				array(
					'wc_whatsapp_order' => $order_id,
					'key'               => $order->get_order_key(),
				),
				home_url( '/' )
			),
		);
	}

	private function build_order_message( $order ) {
		$lines   = array();
		$lines[] = '===============';
		$lines[] = '*Comandă nouă #' . $order->get_order_number() . '*';
		$lines[] = '';
		$lines[] = 'Client: ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$lines[] = 'Telefon: ' . $order->get_billing_phone();

		$address_1 = $order->get_shipping_address_1() ?: $order->get_billing_address_1();
		$city      = $order->get_shipping_city() ?: $order->get_billing_city();
		$postcode  = $order->get_shipping_postcode() ?: $order->get_billing_postcode();
		$lines[]   = 'Adresă livrare: ' . trim( $address_1 . ', ' . $city . ', ' . $postcode, ', ' );
		$lines[]   = '';
		$lines[]   = 'Produse:';

		foreach ( $order->get_items() as $item ) {
			$lines[] = '- ' . $item->get_quantity() . ' x ' . $item->get_name() . ' — ' . wc_format_decimal( $item->get_total(), 2 ) . ' RON';
		}

		$lines[] = '';
		$lines[] = 'Total: ' . wc_format_decimal( $order->get_total(), 2 ) . ' RON';
		$lines[] = '===============';

		return implode( "\n", $lines );
	}
}
