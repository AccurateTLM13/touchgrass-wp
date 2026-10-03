# Customizing Touch Grass

Everything below lives in **Appearance → Customize → Touch Grass** (a panel
with one section per area). Nothing requires editing PHP. Changes preview
live and survive updates.

## Branding

- **Site title & logo:** Appearance → Customize → **Site Identity**. Set the
  site title and upload a logo — the header and footer use them
  automatically. With no logo, the site title renders as the wordmark.
- **Announcement bar:** show/hide, editable text (links allowed), and the
  display-only promo code. Keep the text consistent with a real coupon in
  WooCommerce → Coupons.

## Homepage

- **Hero:** eyebrow, headline, sub-copy, primary/secondary button labels *and*
  link destinations (primary defaults to your WooCommerce shop page), hero
  image (defaults to the theme photo).
- **Trust row:** rating text is computed live from product reviews when left
  empty, and hidden until the first review exists.
- **Honesty section:** headline, copy, and the Touch Grass price in the
  comparison table.
- **Shop section:** heading, subheading, products per shop page.
- **How it works:** image plus all three step titles and texts.
- **Reviews:** heading, subheading, three editable stats (value + label).
- **Guarantees:** three title/text pairs — the *same* fields render on the
  homepage trust row and on every product page.
- **Section visibility:** each homepage section (honesty, shop, how it works,
  reviews, FAQ, newsletter) can be hidden independently.

## Content (no code)

- **FAQs:** Touch Grass → FAQs. Each has a **Display order** field (lower
  shows first). The homepage accordion follows it.
- **Testimonials:** Touch Grass → Testimonials, with reviewer role and 1–5
  rating. Deleting all of them hides the reviews section — fictional quotes
  never come back on their own.
- **Products:** standard WooCommerce products, plus a **Touch Grass** box on
  each product for the tagline and badge (Bestseller / Low stock / Staff
  pick / none).
- **Menus:** Appearance → Menus. The theme has Primary + three footer
  locations; sensible fallbacks render until you assign menus.
- **Pages & posts:** standard WordPress content, including blocks, renders in
  the theme's page/post templates.

## Commerce display rules

- Product cards show the real average rating and review count — and show **no**
  stars at all until a product has reviews.
- The shop toolbar keeps WooCommerce's sorting dropdown, result counts,
  category filters, and pagination.
- Sale prices, stock states, and quantity selectors are WooCommerce's own —
  the theme only styles them.
