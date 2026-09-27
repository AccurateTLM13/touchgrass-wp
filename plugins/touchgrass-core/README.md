# Touch Grass — Core

The functionality half of the Touch Grass WordPress Site Package. The theme
handles presentation; this plugin owns everything that should survive a
theme change.

## What it does

- **Content types** — `tg_faq` (FAQ entries with display order), `tg_testimonial`
  (reviews with role + 1–5 rating), `tg_subscriber` (newsletter list, private,
  visible in WP admin only).
- **Product fields** — `_tg_tagline` and `_tg_badge` (Bestseller / Low stock /
  Staff pick) on WooCommerce products, editable in a "Touch Grass" box on the
  product editor. The theme reads them; it never defines them.
- **Demo Pay gateway** — mock checkout for the demo store. Works in both the
  classic checkout and the Cart/Checkout Blocks (vanilla-JS block registration,
  no build step). Completing checkout marks the order **processing** —
  WooCommerce's honest status for "paid, awaiting fulfillment". No card is
  charged, nothing is shipped. That's the point of a demo.
- **One-click demo importer** — Touch Grass → **Import Demo Content** builds the
  whole store: 2 categories, 9 product images, 9 products (by SKU), the
  `GOOUTSIDE` 20%-off coupon, 7 FAQs, 3 testimonials, and the Primary menu.
  It is idempotent: run it twice and the second run only updates, creating
  zero duplicates.
- **Setup dashboard** — a checklist (WooCommerce, store pages, products,
  coupon, menus, Demo Pay…) so you can see what's missing at a glance.
- **Newsletter backend** — the footer's "Join the touched" form posts to
  `tg_newsletter_subscribe` and stores real subscribers. Invalid emails and
  duplicates get real error messages, not fake success.

## Requirements

- WordPress 6.5+, PHP 8.1+
- WooCommerce 9.0+ (the plugin activates without it but the importer, gateway,
  and product fields wait politely until it's there)

## Theme integration note

The theme's newsletter form posts directly to `admin-ajax.php` with
`action=tg_newsletter_subscribe`, the `email` field, an optional `source`
field, and the nonce in field `tg_newsletter_nonce` (nonce action
`tg_newsletter`, rendered via `wp_nonce_field()`). No script localization
is needed.
