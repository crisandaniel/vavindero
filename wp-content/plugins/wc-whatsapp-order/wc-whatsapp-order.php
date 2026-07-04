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

/*
 * process_payment() (class-wc-gateway-whatsapp-order.php) redirects here
 * instead of straight to wa.me, so the message text never passes through
 * the block Checkout's JSON response (which strips line breaks from it).
 * This does a raw HTTP redirect - a plain Location header, not a JSON
 * field - straight to WhatsApp with the message intact.
 */
add_action( 'template_redirect', 'wc_whatsapp_order_handle_redirect' );

function wc_whatsapp_order_handle_redirect() {
	if ( empty( $_GET['wc_whatsapp_order'] ) || empty( $_GET['key'] ) ) {
		return;
	}

	$order = wc_get_order( absint( $_GET['wc_whatsapp_order'] ) );

	if ( ! $order || ! hash_equals( $order->get_order_key(), wp_unslash( $_GET['key'] ) ) ) {
		return;
	}

	$message = $order->get_meta( '_whatsapp_order_message' );
	if ( ! $message ) {
		return;
	}

	$gateways = WC()->payment_gateways()->payment_gateways();
	if ( ! isset( $gateways['whatsapp_order'] ) ) {
		return;
	}

	$whatsapp_number = preg_replace( '/[^0-9]/', '', $gateways['whatsapp_order']->get_option( 'whatsapp_number' ) );

	/*
	 * wp_redirect() runs the URL through wp_sanitize_redirect(), which
	 * strips %0d/%0a sequences unconditionally (an HTTP header-injection
	 * safeguard - confirmed by testing wp_sanitize_redirect() directly:
	 * "Linia1%0ALinia2" becomes "Linia1Linia2"). That protection targets
	 * *raw* CR/LF bytes ending up in the header; rawurlencode() below
	 * already means the message only ever contributes the literal, safe
	 * ASCII text "%0A" to the header value, never an actual newline byte,
	 * so a plain PHP header() call (which WordPress doesn't filter) is
	 * safe here and preserves it.
	 */
	header( 'Location: https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message ) );
	exit;
}
