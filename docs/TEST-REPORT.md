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

## RC2 correction pass (2026-10-03, v2.1.1)

An independent source review of the branch found five release-candidate
defects; all were fixed and covered by new regression tests.

1. **Customizer image controls stored URLs, code expected IDs.** Both image
   settings (`tg_hero_image`, `tg_how_image`) used `WP_Customize_Image_Control`
   (stores a URL) with an `absint` sanitizer (destroyed the URL → always 0).
   Switched to `WP_Customize_Media_Control` (`mime_type=image`), which stores
   the attachment ID the theme already expects. New integration tests assert
   the registered control class for both settings, and a half-star rating
   sanitizer (`tg_sanitize_rating`, 0–5 in 0.5 steps) was added with unit
   coverage.
2. **Forced Buttondown tag broke free-plan signups.** The subscribe payload
   always sent `tags: ['touch-grass-site']`; Buttondown rejects unknown tags
   with 403 on plans without tag support. Tags are now opt-in via the
   `tg_newsletter_tags` filter (default: none). NEWSLETTER.md documents this.
3. **Trust row always rendered five stars.** The live aggregate now returns
   `{avg, count}` (`tg_live_rating_data()`); the trust row renders
   `tg_stars($avg, $count)` for live data, or a new merchant-set
   `tg_trust_rating_stars` value for manual override text.
4. **Fictional review stats as production defaults.** `tg_stat_1_value`
   (`8,600+`) and `tg_stat_2_value` (`4.9`) now default to empty and are
   hidden when empty; the importer fills them as flagged demo material
   (`tg_demo_stats_set` option) only when the merchant hasn't customized
   them — reruns preserve merchant edits.
5. **CI workflow missing from the branch.** `.github/workflows/ci.yml` existed
   in the source tree but never reached the branch — and the API token in
   use cannot create files under `.github/workflows/` (GitHub requires the
   `workflow` scope for that path; both the git-data and contents APIs
   return 404 without it). The workflow file ships in the repo; it needs
   one manual step: add `.github/workflows/ci.yml` via the GitHub web UI
   (branch `production-quality` → Add file → copy the file contents), or
   push it from a checkout authenticated with `workflow` scope. Until then,
   the honest description is: test suite exists and runs green locally;
   CI is designed but not yet installed.

**Results after RC2:** `tests/integration.php` **36/36 PASS** (21 original +
15 new), `tests/http-tests.sh` **17/17 PASS**, all PHP files pass `php -l`.

## v2.2.0 — Gag-commerce patterns pass (2026-10-04)

All 10 "professional gag commerce" patterns implemented (theme = presentation,
plugin = functionality). Demo mode stays OFF; importer stays idempotent,
admin-only, non-publishing, merchant-edit-preserving.

### Automated

- `tests/integration.php`: **57/57 PASS** (36 pre-existing + 21 new).
  New coverage: microcopy defaults (12 keys) + merchant override + empty-override
  fallback; checkout button and processing-email heading wired to the map;
  "The Deed" tab registered with 5 spec rows and house defaults; per-blade
  line computes from live price; importer converges to exactly 8 FAQs across
  reruns (stale demo FAQs retired, merchant FAQs untouched); `_tg_batch`
  set on plots (empty on accessories); Mister + Gnome cross-sells on every plot.
- `tests/http-tests.sh`: **31/31 PASS** (17 pre-existing + 14 new).
  New coverage: PDP renders with no fatal (plugin on and off); Deed tab,
  "Complete the Ritual" cross-sells, sticky ATC markup, per-blade line, batch
  line, "How It Ships" accordion, trust block, fictional press strip, and
  microcopy ATC text all present; homepage has Deed teaser + FAQ JSON-LD;
  coupon label resolves to "Bribe code" through the map.
- `php -l`: every changed PHP file passes. `node --check`: main.js passes.
- `.pot` files regenerated for both text domains (new strings included).

### Manual / runtime (screenshot-verified)

- **Homepage (1440px + 390px):** announcement → hero → estates grid (9 products)
  → trust strip (guarantee card + fictional press + "Certified 100% Real Grass*
  *grass") → confession → "The Deed" teaser (dark) → how-it-works → reviews →
  FAQ (8 items) → footer. Grass Club + newsletter sections correctly hidden
  (newsletter unconfigured in playground); both gated on `tg_section_club` /
  `tg_section_news` + `tg_newsletter_configured()`.
- **PDP (1440px + 390px):** badge → H1 → "Harvest Batch No. 7 — cut this
  morning, invoiced this afternoon." → tagline → $29.00 + "≈ $0.001 per blade"
  → "Claim Your Plot" ATC → Complete the Ritual (Mister + Gnome) →
  accordions (Description / The Deed / Shipping & Returns / How It Ships) →
  reviews → trust block (compact).
- **Sticky ATC (390px, scrolled):** appears after scrolling past the main form;
  thumbnail + title + price + "Claim Your Plot" button (triggers the real form).
  Hidden on desktop via media query; `aria-hidden` toggles with visibility.
- **Cart:** empty message reads "Nothing here. Like your step count." (classic
  filter + block-cart `render_block` swap of the default text only); trust block
  (compact) appended via `the_content` so it renders for both cart types.
- **Checkout:** empty cart redirects to cart (standard WooCommerce behavior);
  "Complete Invoice" button text verified through the
  `woocommerce_order_button_text` filter.

### Known limitations

- **Block-based checkout button:** `woocommerce_order_button_text` covers the
  classic checkout; the block checkout renders its button label via JS. Same
  applies to the block cart's coupon placeholder (the PHP gettext filter covers
  the classic cart; the map value was verified through the filter directly).
- **Live newsletter subscribe / real gateway / email delivery:** still not
  exercised (no API keys) — unchanged from v2.1.1.
- **`.github/workflows/ci.yml`:** still needs the one manual web-UI add
  (API token lacks `workflow` scope).
