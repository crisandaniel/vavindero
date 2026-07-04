# WhatsApp Order Checkout — Design

## Context

Subproject 4 of the [[vavinde-saas-pivot]] plan: replace real payment processing
with an order flow that sends the order to the shop owner's WhatsApp number
instead. Each site in the Multisite network is an independent shop and can
have a different WhatsApp number.

This document covers only the payment/checkout mechanism itself — not
self-service signup (subproject 2) or automatic site provisioning
(subproject 3), which remain unbuilt.

## Goal

Add a custom WooCommerce payment method, "Comandă pe WhatsApp", that:
- Appears in the normal Payment methods list at checkout, like any other
  gateway (Stripe, PayPal, Cash on Delivery).
- Lets each site's shop owner configure their own WhatsApp number via the
  gateway's own settings screen (WooCommerce → Settings → Payments).
- On placing an order, creates the order with status "On hold" (no real
  payment was taken — it's awaiting the seller's manual confirmation via
  the WhatsApp conversation that follows).
- Immediately redirects the customer to a `wa.me` link (opens WhatsApp
  app/web) pre-filled with a message containing the full order details.

## Non-goals

- No real payment processing of any kind — this method never charges
  anyone.
- No automated/API-based WhatsApp sending (WhatsApp Business API) — the
  customer manually presses Send in their own WhatsApp client. This was a
  deliberate choice (zero cost, zero business account/approval needed) over
  the alternative (automated but requires Meta business verification and
  per-message cost).
- No email notification to the seller — deferred; the order already exists
  in WooCommerce (`wp-admin → WooCommerce → Orders`) regardless of whether
  the WhatsApp message is ever sent, which is the safety net for now.
- No handling of "seller has no WhatsApp" — out of scope; every seller in
  this launch is expected to have WhatsApp, since it's the whole point of
  the feature.

## Design

### Component: a small custom plugin

A new plugin, `wc-whatsapp-order`, added under `wp-content/plugins/`,
following the same pattern as any other plugin already in this repo (code
lives in git, baked into the Docker image at build time — see
[[vavinde-infra-migration]]).

The plugin registers one class extending `WC_Payment_Gateway`:

- **id**: `whatsapp_order`
- **title / description**: configurable via the gateway's own settings form
  (defaults: "Comandă pe WhatsApp" / "Trimite comanda direct pe WhatsApp
  vânzătorului").
- **Settings fields** (`init_form_fields()`):
  - `enabled` (WooCommerce's standard toggle)
  - `title`, `description` (standard gateway fields, shown to the customer
    at checkout)
  - `whatsapp_number` — the shop's WhatsApp number, in international format
    without `+` or leading `0` (e.g. `40712345678` for a Romanian number).
    A short instruction string next to the field states the exact expected
    format, since `wa.me` links require it and get silently ignored if
    malformed.
- **process_payment( $order_id )**:
  1. Load the `WC_Order` object.
  2. Set order status to `on-hold` (`$order->update_status('on-hold', ...)`),
     with an order note explaining it's awaiting WhatsApp confirmation.
  3. Reduce stock levels as WooCommerce normally does when an order is
     placed (standard behavior via `wc_reduce_stock_levels()` — not
     something this gateway needs to implement itself; letting
     `process_payment` fall through to WooCommerce's normal stock-reduction
     hook on status change).
  4. Empty the cart (`WC()->cart->empty_cart()`), matching every other
     gateway's convention.
  5. Build the WhatsApp message (see below) and construct the `wa.me` URL:
     `https://wa.me/{whatsapp_number}?text={urlencoded message}`.
  6. Return `array('result' => 'success', 'redirect' => $wa_me_url)` —
     WooCommerce's checkout JS follows this redirect automatically, taking
     the customer straight to WhatsApp. No intermediate "thank you" page
     step.

### Message content

Built from the `WC_Order` object's own data (everything the customer
entered at checkout), formatted as plain text with line breaks
(URL-encoded for the `wa.me` link):

```
Comandă nouă #{order_number}

Client: {billing_first_name} {billing_last_name}
Telefon: {billing_phone}
Adresă livrare: {shipping_address_1}, {shipping_city}, {shipping_postcode}

Produse:
- {quantity} x {product_name} — {line_total} RON
- ...

Total: {order_total} RON
```

If shipping address fields are empty (e.g. digital-only order), fall back
to the billing address fields WooCommerce already collects — every
WooCommerce checkout captures at least a billing address, so there's always
something to include.

### Why a custom gateway, not a customized "Cash on Delivery"

WooCommerce ships a built-in "Cash on Delivery" gateway with similar
UX (no payment fields, just confirm-and-place-order). Repurposing it was
considered but rejected: its class and settings are semantically
"cash on delivery", not WhatsApp-specific, and hardcoding a redirect
override into core/plugin code would mean patching a bundled WooCommerce
class rather than owning a small, purpose-built one. A dedicated gateway
class is the standard, supported WooCommerce extension point for exactly
this kind of custom checkout flow.

## Verification

- Local: place a test order on `lvh.me` using the "Comandă pe WhatsApp"
  method, confirm:
  - Order appears in `wp-admin → WooCommerce → Orders` with status "On hold".
  - Browser redirects to a `wa.me` URL with a correctly formatted,
    URL-decoded message matching the order's actual data.
  - Stock levels decrease for the ordered product(s).
- Confirm the gateway's settings screen lets the phone number be changed,
  and the resulting `wa.me` link uses the newly configured number.
- Confirm a second site in the network (e.g. `test1.lvh.me`) can configure
  a *different* WhatsApp number independently, with no cross-site leakage.
