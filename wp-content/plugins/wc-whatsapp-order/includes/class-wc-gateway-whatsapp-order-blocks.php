<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Makes the WhatsApp Order gateway appear in the block-based Checkout
 * (wp:woocommerce/checkout). The classic WC_Payment_Gateway registration
 * alone is not enough for the Checkout block — it needs this separate
 * integration plus a JS-side registerPaymentMethod() call.
 */
class WC_Gateway_WhatsApp_Order_Blocks extends AbstractPaymentMethodType {

	protected $name = 'whatsapp_order';

	private $gateway;

	public function initialize() {
		$this->settings = get_option( 'woocommerce_whatsapp_order_settings', array() );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	public function is_active() {
		return $this->gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-whatsapp-order-blocks',
			plugins_url( 'assets/js/checkout-block.js', dirname( __DIR__ ) . '/wc-whatsapp-order.php' ),
			array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n' ),
			'1.0.0',
			true
		);

		return array( 'wc-whatsapp-order-blocks' );
	}

	public function get_payment_method_data() {
		return array(
			'title'       => $this->gateway->title,
			'description' => $this->gateway->description,
		);
	}
}
