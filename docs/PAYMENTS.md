# Payments: production setup & demo mode

## Production: take real payments

1. Go to **WooCommerce → Settings → Payments**.
2. Enable and configure at least one real gateway (Stripe, PayPal, etc.).
   Follow that gateway's own setup — API keys, webhooks, and test mode live
   in the gateway's settings, not in the Touch Grass plugin.
3. Place a test order yourself (most gateways offer a sandbox/test mode —
   use it, then switch to live).
4. Confirm the order lands in **WooCommerce → Orders** with the right status,
   and that the customer email arrives.

**Demo mode is OFF by default.** With demo mode off, the Demo Pay mock
gateway is never registered — it cannot appear at checkout, be selected by
accident, or process a fake payment. What your customers see is exactly what
you enabled under WooCommerce → Settings → Payments. The Touch Grass Setup
checklist shows the currently enabled gateways so you can verify at a glance.

### Keep promises consistent

Anything the storefront *advertises* about money must match your WooCommerce
settings:

- **Shipping promises** (e.g. "Free shipping over $50") are editable in
  **Appearance → Customize → Touch Grass → Guarantees**. The matching rule
  must exist in **WooCommerce → Settings → Shipping** (e.g. a free-shipping
  method for orders over $50).
- **Coupons mentioned on the site** (e.g. `GOOUTSIDE`) must exist in
  **WooCommerce → Coupons** with the advertised terms. The announcement bar
  text is editable in the Customizer — if you delete the coupon, update or
  hide the announcement too.

## Demo mode: for demos only

On the **Touch Grass Setup** screen, ticking **Enable demo mode** turns the
installation into a demo store:

- The **Demo Pay** mock gateway registers with WooCommerce (classic checkout
  and Cart/Checkout Blocks). Its own toggle still defaults to off — enable it
  under WooCommerce → Settings → Payments if you want it offered.
- A **"Demo store" notice** appears on the cart and checkout pages (classic
  and block versions).
- The cart's coupon field hints at the demo coupon while demo mode is on.

Demo Pay marks orders **processing** without charging anything — the order
note says so explicitly. **Do not take real orders with demo mode on.**
Switching demo mode back off also force-disables the gateway's own toggle,
so re-enabling demo mode later starts from a known-safe state.
