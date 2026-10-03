# Installing Touch Grass

Requires: **WordPress 6.5+**, **PHP 8.1+**, **WooCommerce 9.0+** (tested on WP 7.1.2, PHP 8.4, WC 11.1.2).

## What to install

| File | What it is | Required? |
|---|---|---|
| `touchgrass-theme-2.1.0.zip` | The Touch Grass theme (presentation) | Yes |
| `touchgrass-core-2.1.0.zip` | Touch Grass — Core (functionality: FAQs, testimonials, product fields, demo importer, newsletter, Demo Pay) | Recommended |

WooCommerce powers the shop, cart, checkout, orders, and payments. The theme
works without it (pages and posts render; commerce UI hides itself), but there
is no store without it.

## Steps (no SSH, no PHP edits)

1. In wp-admin, go to **Plugins → Add New → Upload Plugin** and install
   `touchgrass-core-2.1.0.zip`, then **Activate**.
2. Go to **Appearance → Themes → Add New → Upload Theme** and install
   `touchgrass-theme-2.1.0.zip`, then **Activate**.
3. Install and activate **WooCommerce** (Plugins → Add New, search
   "WooCommerce") and run its setup wizard. Make sure the **Shop, Cart,
   Checkout, and My account** pages exist.
4. Go to **Touch Grass → Touch Grass Setup** in the admin menu. The status
   checklist tells you exactly what's missing.
5. Optional: click **Import Demo Content** to install the demo catalogue
   (9 products, images, coupon, FAQs, testimonials, menu). Rerunning it never
   duplicates and never overwrites your product names, descriptions, or
   prices. See "Demo content" below.
6. Configure payments: **WooCommerce → Settings → Payments**. Enable a real
   gateway (Stripe, PayPal, etc.). **Demo mode is OFF by default** — the
   Demo Pay mock gateway only appears when you explicitly enable demo mode
   on the Touch Grass Setup screen. See `docs/PAYMENTS.md`.
7. Customize branding: **Appearance → Customize → Touch Grass**. Every string,
   image, link, stat, guarantee, and section toggle lives there.
   See `docs/CUSTOMIZE.md`.
8. Newsletter (optional): add a Buttondown API key on the Touch Grass Setup
   screen. Until you do, the signup section stays hidden. See
   `docs/NEWSLETTER.md`.
9. Launch: when the catalogue is ready, take the store live from
   **WooCommerce → Settings** (the "coming soon" switch). The demo importer
   never changes this for you.

## Demo content

- **Import** is optional, admin-only, and idempotent: run it twice and the
  second run only refreshes the importer's own display fields (taglines,
  badges, images, categories). Your edits to names, descriptions, and prices
  survive.
- **Reset Demo Products** (separate button, asks for confirmation) is the
  *only* action that overwrites commercial fields — and only on products the
  importer manages. Your own products are never touched.
- Imported FAQs/testimonials are flagged as demo material in the admin.
- The importer never publishes your store and never touches payment settings.

## Updating

1. Download the new ZIPs, then **Appearance → Themes** (or **Plugins**):
   upload the new ZIP — WordPress will offer to replace the existing theme/
   plugin with the newer version. Your content, settings, and Customizer
   values are stored in the database and survive the update.
2. After updating, visit **Touch Grass → Touch Grass Setup** and confirm the
   checklist is green.

## Uninstall

- Deactivating **Touch Grass — Core** leaves your products, FAQs,
  testimonials, menu, and coupon in place. The theme degrades gracefully
  (no fatals; commerce UI hides).
- Deleting the plugin removes its settings (`tg_demo_mode`,
  `tg_buttondown_api_key`, gateway settings). Content is kept — delete it
  manually if you want a clean slate. Subscriber records (`tg_subscriber`
  posts under Touch Grass → Subscribers) are kept too; delete them there if
  your retention policy requires it.
- The legacy standalone **Touch Grass — Demo Pay** plugin (v1) is not part
  of the supported installation. If it's still installed, the core plugin
  prevents it from loading (it would crash the site) and the dashboard tells
  you to deactivate and delete it.
