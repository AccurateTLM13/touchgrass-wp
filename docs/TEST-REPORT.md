# Test report — Touch Grass production-quality pass (2026-10-03)

**Versions tested:** WordPress 7.1.2, PHP 8.4.10, WooCommerce 11.1.2 (local playground).

## Automated

- `tests/integration.php` (WP-CLI, run against the playground install): **21/21 PASS**.
  Covers: theme-only fatal check, plugin deactivation safety (theme functions all
  guarded), demo mode default OFF, Demo Pay not registered when demo off,
  demo notice on/off behavior, importer idempotency (two runs, no duplicates),
  importer preserves coming-soon `yes` and `no`, merchant edits survive rerun,
  explicit reset restores demo values, FAQ order field honored, newsletter
  paths (invalid email, honeypot, expired nonce, unconfigured hidden).
- `tests/http-tests.sh`: **17/17 PASS**. Homepage, shop, product pages 200;
  no Demo Pay / no demo notices with demo off; notices appear with demo on;
  newsletter endpoints return correct error shapes.
- `php -l`: all theme/plugin PHP files pass. `node --check`: all JS passes.
- CI (`.github/workflows/ci.yml`): PHP lint, JS syntax, reproducible ZIP build.

## Manual / runtime

- **Demo mode isolation:** fresh install starts with demo mode OFF — Demo Pay
  never registered, no demo notices, no demo coupon hints. Verified on a clean
  second WordPress install (`tg_clean` DB, port 8081) using the built ZIPs.
- **Demo import on clean install:** 2 categories, 9 media, 9 products, 1 coupon,
  7 FAQs, 3 testimonials, 4 menu items, 0 failures. Bundled plugin assets
  survive import (temp-copy verified). Homepage 200 with all content.
- **Checkout — demo mode:** Store API checkout with Demo Pay → order #78,
  status `processing`, total $47.20, coupon GOOUTSIDE applied; refund #79
  processed → order `refunded`.
- **Checkout — real gateway:** WooCommerce Cash on Delivery → order #84,
  status `processing`, total $34.00; refund #85 → order `refunded`.
  Both block Cart/Checkout and classic flows verified for notices and totals.
- **Variable product:** attributes/selectors render; price updates per
  variation (screenshot verified).
- **Account pages:** render without fatal; login/password-reset/order-history
  are WooCommerce core and unmodified.
- **Customizer:** branding, hero, guarantees, announcement, section toggles all
  preview and persist.
- **Visual QA (wget-mirror + CDP screenshots):** desktop homepage, shop,
  single product, variable product, checkout notice (block + classic cart),
  mobile 390px (hamburger opens/closes), tablet 768px, block-sampler page
  (paragraph/list/table/quote), no-JS render (content visible).

## What was NOT exercised

- **Live newsletter subscribe:** no Buttondown API key was available. All code
  paths except the real `POST /v1/subscribers` were tested (validation,
  honeypot, rate limit, nonce refresh, duplicate/invalid/failure handling).
  Owner must add their key and send one test signup.
- **Real payment gateway credentials (Stripe/PayPal):** none available.
  End-to-end checkout + refund proven with demo mode and with WooCommerce's
  built-in Cash on Delivery. Owner must configure their gateway and place a
  live test order.
- **Email delivery:** order/customer emails were not sent to a real inbox in
  the playground.
