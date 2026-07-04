<?php
/**
 * Plugin Name: WooCommerce WhatsApp Order
 * Description: Adds a "Comandă pe WhatsApp" payment method that sends order details to the shop's WhatsApp number instead of processing payment.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'wc_whatsapp_order_init', 11 );

function wc_whatsapp_order_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	require_once __DIR__ . '/includes/class-wc-gateway-whatsapp-order.php';
}

add_filter( 'woocommerce_payment_gateways', 'wc_whatsapp_order_add_gateway' );

function wc_whatsapp_order_add_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_WhatsApp_Order';
	return $gateways;
}

/*
 * The classic WC_Payment_Gateway registration above is enough for the
 * legacy [woocommerce_checkout] shortcode, but not for the block-based
 * Checkout (wp:woocommerce/checkout) that WooCommerce uses by default on
 * newer sites. This registers the same gateway with the Blocks payment
 * method API so it actually renders as a selectable option there too.
 */
add_action( 'woocommerce_blocks_payment_method_type_registration', 'wc_whatsapp_order_register_blocks_support' );

function wc_whatsapp_order_register_blocks_support( $payment_method_registry ) {
	if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}
	require_once __DIR__ . '/includes/class-wc-gateway-whatsapp-order-blocks.php';
	$payment_method_registry->register( new WC_Gateway_WhatsApp_Order_Blocks() );
}
