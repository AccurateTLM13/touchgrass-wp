# Installing Touch Grass (WordPress Site Package v2)

Time: about 5 minutes. No SSH, no WP-CLI, no code.

## What you need first

- A WordPress site (6.5 or newer, PHP 8.1 or newer).
- WooCommerce installed, activated, and its setup wizard completed.

## Steps

1. **Unzip** `touchgrass-wordpress-package-v2.zip` on your computer. Inside you'll find `touchgrass-theme.zip` and `touchgrass-core.zip`.
2. **Install the theme:** WordPress admin → Appearance → Themes → Add New → Upload Theme → choose `touchgrass-theme.zip` → Install Now → Activate.
3. **Install the plugin:** Plugins → Add New → Upload Plugin → choose `touchgrass-core.zip` → Install Now → Activate.
4. **Import the demo:** Settings (or the admin sidebar) → **Touch Grass** → click **Import Demo Content**. Wait for the checklist to turn green.
5. **Visit your site.** Nine products, categories, images, the GOOUTSIDE coupon, FAQs, testimonials, and menus are all there.

Run the importer again any time — it updates existing content instead of duplicating it.

## Making it yours

- **Words:** Appearance → Customize → Touch Grass (hero, announcement, guarantees, footer…).
- **Menus:** Appearance → Menus.
- **FAQs / Testimonials:** the FAQ and Testimonials sections in the admin sidebar.
- **Newsletter signups:** stored privately under Touch Grass → Subscribers.
- **Products & prices:** standard WooCommerce.

## Taking real payments

The included **Demo Pay** gateway works in both the classic and block checkouts, but it's a mock — orders land in *processing* and no money moves. When you're ready for real orders, install Stripe or PayPal for WooCommerce and disable Demo Pay.
