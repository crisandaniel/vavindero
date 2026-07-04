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
