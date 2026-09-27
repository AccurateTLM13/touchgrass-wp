# Touch Grass — WordPress Install Package

Everything you need to run the Touch Grass WooCommerce store on your own WordPress.

## What's in the box

| File | What it is |
|---|---|
| `touchgrass-theme.zip` | The custom Touch Grass theme — upload via Appearance → Themes → Add New → Upload |
| `touchgrass-demo-pay.zip` | Demo Pay gateway (mock checkout, no real charges) — upload via Plugins → Add New → Upload |
| `product-images/` | 9 product photos (`.webp`) used by the setup script |
| `touchgrass-content-setup.sh` | One-shot script: creates categories, imports images, creates all 9 products, creates the `GOOUTSIDE` coupon |

## Install steps

### 1. The basics (on your server or local machine)

- PHP 8.1+, MySQL/MariaDB, Apache or Nginx
- A fresh WordPress install
- [WP-CLI](https://wp-cli.org/) installed (needed for step 4)

### 2. Install WooCommerce

In WP admin: Plugins → Add New → search **WooCommerce** → Install → Activate.
Run through the WooCommerce setup wizard (store address, currency — pick anything, it's a demo).

### 3. Install the Touch Grass theme + Demo Pay

- Appearance → Themes → Add New → **Upload Theme** → choose `touchgrass-theme.zip` → Activate
- Plugins → Add New → **Upload Plugin** → choose `touchgrass-demo-pay.zip` → Activate
- In WooCommerce → Settings → Payments, enable **Demo Pay** (it approves every order instantly — no real money moves)

### 4. Load the products, images & coupon

From a terminal on the server, with the `product-images/` folder next to the script:

```bash
chmod +x touchgrass-content-setup.sh
./touchgrass-content-setup.sh /path/to/wordpress
```

This creates:
- Categories: **Plots** (7 products), **Accessories** (2 products)
- All 9 products with prices, descriptions, badges, and photos
- Coupon **`GOOUTSIDE`** — 20% off, because irony

The script is idempotent — safe to run twice, it won't duplicate anything.

### 5. Pretty permalinks

Settings → Permalinks → choose **Post name** → Save. (The shop, cart, and checkout pages need this.)

## The catalog

| Product | Price |
|---|---|
| The Daily Driver (Bestseller) | $29 |
| The Commuter | $24 |
| The Standup | $34 |
| The Pro Max (Low stock, on sale) | ~~$74~~ $59 |
| Pair Programmer | $44 |
| Seedling Starter Kit | $19 |
| The Night Shift (Staff pick) | $39 |
| The Tiny Gnome | $12 |
| The Mister | $16 |

## Brand notes

- Product copy is straight-faced luxury. Transaction copy lets the mask slip.
- "Going outside is free." / "We're not judging. We're invoicing." / "Your money is safe. For now."
- Promo bar code: `GOOUTSIDE`

## Troubleshooting

- **Products don't show in the shop?** The setup script registers each product through WooCommerce's data store (`wp wc product update`). If you add products by hand later, create them via Products → Add New in WP admin, not raw post inserts.
- **Images 403?** Make sure the web server user owns `wp-content/uploads` (e.g. `chown -R www-data:www-data wp-content/uploads`).
- **Checkout 404s?** Re-save permalinks (step 5) and confirm WooCommerce's Shop/Cart/Checkout pages exist under Pages.
