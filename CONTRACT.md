# Touch Grass v2 — Interface Contract

Shared between `theme/touchgrass/` and `plugins/touchgrass-core/`.
Neither side may invent new shared keys without updating this file.

## Customizer settings (theme-owned, editable in Appearance → Customize → Touch Grass)

Key panels: Announcement bar, Homepage hero, Honesty section, Shop section,
How it works, Reviews, Guarantees, Footer, Section visibility. Full list in
`docs/CUSTOMIZE.md`; `tg_brand_fields()` in `inc/customizer.php` is the
single source of truth for defaults. Notable keys:

| Key | Default | Used in |
|---|---|---|
| `tg_announcement_show` | true | header.php announcement bar |
| `tg_announcement_text` | `Fresh Cut Friday — <strong>20% off</strong> with code <code>GOOUTSIDE</code>, for the irony.` | header.php announcement bar |
| `tg_hero_image` | (none — theme default photo) | front-page.php hero |
| `tg_hero_cta_primary_url` | (none — WooCommerce shop page) | front-page.php hero |
| `tg_hero_cta_secondary_url` | `#how` | front-page.php hero |
| `tg_trust_rating_text` | (none — computed from product reviews) | front-page.php trust row |
| `tg_confession_price` | `$29` | front-page.php comparison table |
| `tg_section_*` | true | front-page.php section visibility |
| `tg_products_per_page` | 9 | shop loop (`loop_shop_per_page`) |
| `tg_guarantee_1..3_title/text` | 30-day regrow / Free shipping $50+ / No sunlight | homepage + product page |
| `tg_promo_code` | `GOOUTSIDE` | display only (announcement default text) |

## Custom post types (core plugin-owned)

| Slug | Title = | Content = | Meta |
|---|---|---|---|
| `tg_faq` | question | answer | `_tg_faq_order` (int, menu_order alt) |
| `tg_testimonial` | reviewer name | quote | `_tg_role` (text), `_tg_rating` (1–5) |
| `tg_subscriber` | email | — | `_tg_subscribed_at`, `_tg_source`; non-public, `show_ui` in admin only |

## Product meta (core plugin registers, theme reads)

| Key | Type | Meaning |
|---|---|---|
| `_tg_tagline` | text | one-line product tagline under the title |
| `_tg_badge` | select: `bestseller` / `lowstock` / `staffpick` / `` | corner badge on cards |

## Newsletter (Buttondown-backed)

- Form in the homepage newsletter section posts via AJAX action `tg_newsletter_subscribe` (nopriv allowed).
- Nonce: `tg_newsletter_nonce`. Validates email, honeypot, rate limit, then subscribes via Buttondown.
- Response JSON `{success, message}`. JS swaps form → success message; expired nonces auto-refresh and retry once.
- No fake success: failures (invalid email, dupe, server error) show real error text.
- Section hidden unless a Buttondown API key is configured.

## Products (demo importer, idempotent on SKU)

| SKU | Name | Price | Sale | Cats | Badge |
|---|---|---|---|---|---|
| TG-DAILY | The Daily Driver | 29 | — | plots | bestseller |
| TG-COMMUTER | The Commuter | 24 | — | plots | — |
| TG-STANDUP | The Standup | 34 | — | plots | — |
| TG-PROMAX | The Pro Max | 74 | 59 | plots | lowstock |
| TG-PAIR | Pair Programmer | 44 | — | plots | — |
| TG-STARTER | Seedling Starter Kit | 19 | — | plots | — |
| TG-NIGHT | The Night Shift | 39 | — | plots | staffpick |
| TG-GNOME | The Tiny Gnome | 12 | — | accessories | — |
| TG-MISTER | The Mister | 16 | — | accessories | — |

Categories: `plots` (Plots), `accessories` (Accessories). Coupon: `GOOUTSIDE` 20% off.

## Menus

- Location `primary`: Shop, Why grass? (/#how), Reviews (/#proof), FAQ (/#faq)
- Locations `foot_shop`, `foot_company`, `foot_support`: footer columns; brand defaults render until menus are assigned.

## Demo mode (plugin-owned)

- Option `tg_demo_mode` (bool, default false). `tg_demo_mode()` is the theme/plugin-wide check.
- When off: Demo Pay never registers, no demo notices, no demo coupon hints.
- When on: Demo Pay registers (its own toggle defaults to off), demo notices render on cart/checkout (classic hooks + `render_block` filter for the Cart/Checkout blocks).

## Demo Pay gateway

- ID `touchgrass_demo`. Only registered when demo mode is on. Classic: `WC_Payment_Gateway` subclass.
- Blocks: registers via `woocommerce_blocks_payment_method_type_registration`,
  JS at `assets/js/demo-pay-blocks.js` (vanilla, no build step), declares
  `supports: ['products']`, placeOrder → paymentMethodData.payment_method = id.
- Test mode: completes checkout, order goes to `processing` (documented, honest).

## Newsletter (Buttondown)

- Form posts via AJAX action `tg_newsletter_subscribe` (nopriv allowed).
- Nonce: `tg_newsletter_nonce` (action `tg_newsletter`); expired nonces return `code: expired_nonce`, refreshable via `tg_newsletter_nonce_refresh`.
- Honeypot field: `tg_company` (must be empty).
- Provider: Buttondown `POST https://api.buttondown.com/v1/subscribers`, `Authorization: Token <key>`, API key in option `tg_buttondown_api_key`.
- Theme hides the signup section unless `tg_newsletter_configured()`.
- Local private `tg_subscriber` record per confirmed signup: `_tg_subscribed_at`, `_tg_source`, `_tg_provider`, `_tg_external_id`.

## Admin setup page

- Location: top-level "Touch Grass" menu (Touch Grass Setup).
- Shows: WooCommerce active, store pages (exist & published), store visibility (info only), demo products (n/9), categories, coupon, FAQs, testimonials, menus assigned, payment gateways (enabled list), newsletter status, legacy-plugin warning.
- Settings: demo mode toggle, Buttondown API key (Settings API, `tg_settings` group).
- Buttons: "Import Demo Content" → AJAX → runs importer → re-renders checklist. "Reset Demo Products" (separate, confirmed) → restores demo values on managed products only.
- Importer: idempotent (products by SKU, terms by slug, media by slug, coupon by code, CPTs by slug, menu by name). Reruns refresh voice fields (tagline/badge/image/categories) on `_tg_demo_managed` products; commercial fields (name/description/prices) only change via explicit reset. Media is copied to temp files before sideload. Coming-soon and payment settings are never touched.
