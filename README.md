# Touch Grass — WordPress Site Package v2

A complete, installable WordPress + WooCommerce storefront for Touch Grass.
Built to the **WordPress Site Package v1** contract: the theme is presentation
only, the core plugin owns functionality and demo content.

## What's inside

| File | What it is |
|---|---|
| `touchgrass-theme.zip` | The theme. Upload via Appearance → Themes → Add New → Upload. |
| `touchgrass-core.zip` | The core plugin. Upload via Plugins → Add New → Upload. Owns product meta, FAQ/testimonial/subscriber content types, the Demo Pay gateway, and the one-click demo importer. |
| `README.md` | This file. |
| `INSTALL.md` | Step-by-step install guide. |
| `package.json` | Machine-readable manifest. |

## The 5-minute path

1. Fresh WordPress + WooCommerce installed, with WooCommerce's setup wizard completed.
2. Upload and activate `touchgrass-theme.zip`.
3. Upload and activate `touchgrass-core.zip`.
4. Go to **Settings → Touch Grass** and click **Import Demo Content**.
5. Done. Nine products, categories, images, the GOOUTSIDE coupon, FAQs, testimonials, and menus — all created, none duplicated if you run it again.

## Editing the site (no PHP required)

- **Words**: Appearance → Customize → Touch Grass. Hero, announcement bar, confession, steps, guarantees, footer tagline — all editable.
- **Navigation**: Appearance → Menus. The importer builds a Primary menu; change it freely.
- **FAQs**: FAQ → All FAQs in the admin sidebar.
- **Testimonials**: Testimonials → All Testimonials.
- **Newsletter signups**: stored as Subscribers (private, visible in admin only).
- **Products, prices, images**: standard WooCommerce.

## Demo Pay

The mock gateway works in **both** the classic checkout and the block checkout.
Orders land in `processing` — that's honest, it's a demo. Swap in a real gateway
(Stripe, PayPal) when you're ready to take actual money.
