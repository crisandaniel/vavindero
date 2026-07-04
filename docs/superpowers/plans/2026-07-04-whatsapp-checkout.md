# WhatsApp Order Checkout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a custom WooCommerce payment method ("Comandă pe WhatsApp") that creates an on-hold order and redirects the customer straight to a pre-filled WhatsApp message to the shop's own WhatsApp number, with no real payment processing.

**Architecture:** A small, self-contained plugin (`wc-whatsapp-order`) registering one `WC_Payment_Gateway` subclass. WooCommerce's existing gateway architecture handles checkout-list placement, per-site settings storage (each Multisite site gets its own independent gateway configuration for free), and cart/stock lifecycle hooks.

**Tech Stack:** PHP, WordPress plugin API, WooCommerce `WC_Payment_Gateway` class, WP-CLI (for verification), Docker Compose (local dev — see `docker-compose.override.yml` for the live bind-mount that makes plugin edits show up immediately).

## Global Constraints

- No real payment processing of any kind — this gateway never charges anyone. (spec: Non-goals)
- No WhatsApp Business API / automated sending — customer manually presses Send in their own WhatsApp client. (spec: Non-goals)
- Each Multisite site must be able to configure a different WhatsApp number independently, with no cross-site leakage. (spec: Verification)
- Order status after checkout is `on-hold`, not `processing` or `pending`. (spec: Design → process_payment)
- WhatsApp number is stored/used in international format without `+` or leading `0` (e.g. `40712345678`). (spec: Design → Settings fields)
- The redirect after placing the order goes directly to the `wa.me` URL — no intermediate "thank you page with a button" step. (spec: Design → process_payment step 6)

---

### Task 1: Register the gateway with settings

**Files:**
- Create: `wp-content/plugins/wc-whatsapp-order/wc-whatsapp-order.php`
- Create: `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`

**Interfaces:**
- Consumes: WooCommerce's `WC_Payment_Gateway` base class and `woocommerce_payment_gateways` filter (both provided by the already-installed `woocommerce` plugin — no version pinning needed, it's already active in this repo).
- Produces: a registered gateway with id `whatsapp_order`, settings fields `enabled`, `title`, `description`, `whatsapp_number` — accessible via `$this->get_option('whatsapp_number')` etc. Task 2 reads `whatsapp_number` via this exact method call.

- [ ] **Step 1: Create the plugin bootstrap file**

Create `wp-content/plugins/wc-whatsapp-order/wc-whatsapp-order.php`:

```php
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
```

- [ ] **Step 2: Create the gateway class with settings fields**

Create `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`:

```php
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
```

- [ ] **Step 3: Activate the plugin and verify it registers**

Run:
```bash
docker compose exec -T wpcli wp plugin activate wc-whatsapp-order --url=http://lvh.me --allow-root
```
Expected: `Plugin 'wc-whatsapp-order' activated.` with no PHP warnings/errors.

- [ ] **Step 4: Verify the gateway appears in the payments settings list**

Run:
```bash
docker compose exec -T wpcli wp eval '
$gateways = WC()->payment_gateways()->payment_gateways();
var_dump( isset( $gateways["whatsapp_order"] ) );
var_dump( $gateways["whatsapp_order"]->method_title );
' --url=http://lvh.me --allow-root
```
Expected:
```
bool(true)
string(21) "Comandă pe WhatsApp"
```
(the exact byte length may differ due to UTF-8 diacritics — what matters is `bool(true)` and the title text is correct, not the exact `string(N)` byte count)

- [ ] **Step 5: Set the WhatsApp number and verify it persists**

Run:
```bash
docker compose exec -T wpcli wp eval '
$gateways = WC()->payment_gateways()->payment_gateways();
$gateway  = $gateways["whatsapp_order"];
$gateway->update_option( "enabled", "yes" );
$gateway->update_option( "whatsapp_number", "40712345678" );
echo $gateway->get_option( "whatsapp_number" ) . "\n";
' --url=http://lvh.me --allow-root
```
Expected: `40712345678`

- [ ] **Step 6: Commit**

```bash
git add wp-content/plugins/wc-whatsapp-order/
git commit -m "Register WhatsApp Order payment gateway with settings"
```

---

### Task 2: Handle checkout — create order, redirect to WhatsApp

**Files:**
- Modify: `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`

**Interfaces:**
- Consumes: `$this->get_option('whatsapp_number')` from Task 1's settings fields.
- Produces: `process_payment( $order_id )` method returning `array('result' => 'success', 'redirect' => $wa_me_url)`. Task 3 replaces the placeholder message text this task uses with the real `build_order_message()` method — the return array shape and the `wa.me` URL construction stay the same.

- [ ] **Step 1: Add process_payment() with a placeholder message**

In `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`, add this method inside the `WC_Gateway_WhatsApp_Order` class, after `init_form_fields()`:

```php
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( 'on-hold', 'Comandă plasată prin WhatsApp, în așteptarea confirmării vânzătorului.' );
		wc_reduce_stock_levels( $order_id );

		WC()->cart->empty_cart();

		$whatsapp_number = preg_replace( '/[^0-9]/', '', $this->get_option( 'whatsapp_number' ) );
		$message         = 'Comandă nouă #' . $order->get_order_number();
		$wa_me_url       = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message );

		return array(
			'result'   => 'success',
			'redirect' => $wa_me_url,
		);
	}
```

- [ ] **Step 2: Place a test order via WP-CLI and verify the redirect**

First, get a real product ID to order:
```bash
docker compose exec -T wpcli wp post list --post_type=product --field=ID --posts_per_page=1 --url=http://lvh.me --allow-root
```
Note the returned ID (call it `PRODUCT_ID` below).

Then simulate the checkout by creating an order directly and calling `process_payment` on it (this exercises the same code path the real checkout form calls):
```bash
docker compose exec -T wpcli wp eval '
$order = wc_create_order();
$order->add_product( wc_get_product( PRODUCT_ID ), 1 );
$order->set_billing_first_name( "Test" );
$order->set_billing_last_name( "Buyer" );
$order->set_billing_phone( "40799999999" );
$order->calculate_totals();
$order->save();

$gateways = WC()->payment_gateways()->payment_gateways();
$gateway  = $gateways["whatsapp_order"];
$result   = $gateway->process_payment( $order->get_id() );

echo $result["result"] . "\n";
echo $result["redirect"] . "\n";

$order = wc_get_order( $order->get_id() );
echo $order->get_status() . "\n";
' --url=http://lvh.me --allow-root
```
(replace `PRODUCT_ID` with the actual numeric ID from the previous command)

Expected output (three lines):
```
success
https://wa.me/40712345678?text=Comand%C4%83%20nou%C4%83%20%23...
on-hold
```
The exact `%XX`-encoded text after `?text=` will vary (it's URL-encoding of "Comandă nouă #<order number>") — what matters is: `result` is `success`, the `redirect` starts with `https://wa.me/40712345678?text=`, and the order status is `on-hold`.

- [ ] **Step 3: Verify stock was reduced**

Run (replace `PRODUCT_ID` with the same ID used above):
```bash
docker compose exec -T wpcli wp post meta get PRODUCT_ID _stock --url=http://lvh.me --allow-root
```
Compare against the product's stock level before Step 2 — it should be exactly 1 lower (skip this check if the product has stock management disabled; note that in the report instead of treating it as a failure).

- [ ] **Step 4: Commit**

```bash
git add wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php
git commit -m "Add process_payment: create on-hold order, redirect to wa.me"
```

---

### Task 3: Build the full order message

**Files:**
- Modify: `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`

**Interfaces:**
- Consumes: the `WC_Order` object already loaded in `process_payment()` (Task 2).
- Produces: a private `build_order_message( WC_Order $order )` method returning a plain-text string (not yet URL-encoded — `process_payment` applies `rawurlencode()` when building the final URL).

- [ ] **Step 1: Replace the placeholder message with the real builder**

In `wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php`, replace this line inside `process_payment()`:

```php
		$message         = 'Comandă nouă #' . $order->get_order_number();
```

with:

```php
		$message         = $this->build_order_message( $order );
```

Then add this new method to the class, after `process_payment()`:

```php
	private function build_order_message( $order ) {
		$lines   = array();
		$lines[] = 'Comandă nouă #' . $order->get_order_number();
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

		return implode( "\n", $lines );
	}
```

- [ ] **Step 2: Place a test order with full billing/shipping data and verify the decoded message**

Get a product ID the same way as Task 2, Step 2, then:

```bash
docker compose exec -T wpcli wp eval '
$order = wc_create_order();
$order->add_product( wc_get_product( PRODUCT_ID ), 2 );
$order->set_billing_first_name( "Ana" );
$order->set_billing_last_name( "Popescu" );
$order->set_billing_phone( "40799999999" );
$order->set_shipping_address_1( "Str. Exemplu 10" );
$order->set_shipping_city( "Cluj-Napoca" );
$order->set_shipping_postcode( "400000" );
$order->calculate_totals();
$order->save();

$gateways = WC()->payment_gateways()->payment_gateways();
$gateway  = $gateways["whatsapp_order"];
$result   = $gateway->process_payment( $order->get_id() );

$parsed = parse_url( $result["redirect"] );
parse_str( $parsed["query"], $query );
echo $query["text"] . "\n";
' --url=http://lvh.me --allow-root
```

Expected output (decoded message, exact order number and product name will vary based on what's in the local DB, but the structure must match):
```
Comandă nouă #<some number>

Client: Ana Popescu
Telefon: 40799999999
Adresă livrare: Str. Exemplu 10, Cluj-Napoca, 400000

Produse:
- 2 x <product name> — <line total> RON

Total: <order total> RON
```

- [ ] **Step 3: Commit**

```bash
git add wp-content/plugins/wc-whatsapp-order/includes/class-wc-gateway-whatsapp-order.php
git commit -m "Build full order details into the WhatsApp message"
```

---

### Task 4: End-to-end verification across two Multisite sites

**Files:**
- None modified — this is a verification-only task confirming the spec's Multisite-independence requirement.

**Interfaces:**
- Consumes: the completed gateway from Tasks 1–3, and the existing `test1.lvh.me` site already present in the local Multisite network (see [[vavinde-infra-migration]] for how this site was created).

- [ ] **Step 1: Activate and configure the gateway on the second site**

```bash
docker compose exec -T wpcli wp plugin activate wc-whatsapp-order --url=http://test1.lvh.me --allow-root
docker compose exec -T wpcli wp eval '
$gateways = WC()->payment_gateways()->payment_gateways();
$gateway  = $gateways["whatsapp_order"];
$gateway->update_option( "enabled", "yes" );
$gateway->update_option( "whatsapp_number", "40788888888" );
' --url=http://test1.lvh.me --allow-root
```

- [ ] **Step 2: Confirm the two sites have independent, non-leaking settings**

```bash
docker compose exec -T wpcli wp eval '
$gateways = WC()->payment_gateways()->payment_gateways();
echo $gateways["whatsapp_order"]->get_option( "whatsapp_number" ) . "\n";
' --url=http://lvh.me --allow-root

docker compose exec -T wpcli wp eval '
$gateways = WC()->payment_gateways()->payment_gateways();
echo $gateways["whatsapp_order"]->get_option( "whatsapp_number" ) . "\n";
' --url=http://test1.lvh.me --allow-root
```
Expected: first command prints `40712345678` (set in Task 1, Step 5), second prints `40788888888` — different values, confirming no cross-site leakage.

- [ ] **Step 3: Confirm the gateway shows at checkout in the browser**

Open `http://lvh.me/?page_id=` (or navigate to the shop, add a product to cart, and go to checkout) in a browser, and confirm "Comandă pe WhatsApp" appears as a selectable payment method alongside any others. Place a real test order through the browser UI, confirm the browser actually navigates to `web.whatsapp.com` or opens the WhatsApp app with the message pre-filled.

- [ ] **Step 4: Record completion**

No file changes to commit. Confirm all three prior commits from this plan are present:
```bash
git log --oneline -3
```
