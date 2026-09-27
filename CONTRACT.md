# Touch Grass v2 — Interface Contract

Shared between `theme/touchgrass/` and `plugins/touchgrass-core/`.
Neither side may invent new shared keys without updating this file.

## Customizer settings (theme-owned, editable in Appearance → Customize)

| Key | Default | Used in |
|---|---|---|
| `tg_announcement_text` | `Fresh Cut Friday — <strong>20% off</strong> with code <code>GOOUTSIDE</code>, for the irony.` | header.php announcement bar |
| `tg_hero_eyebrow` | `Cultivated indoors. Like you.` | front-page.php hero |
| `tg_hero_headline` | `Go ahead. Touch grass.` | front-page.php hero |
| `tg_hero_sub` | `Hand-grown plots of real grass, shipped to your desk. Going outside is free. This is $29. You do the math.` | front-page.php hero |
| `tg_hero_cta_primary` | `Shop the grass` → `/shop/` | front-page.php hero |
| `tg_hero_cta_secondary` | `How it works` → `/#how` | front-page.php hero |
| `tg_confession_title` | `Going outside is free.` | front-page.php confession |
| `tg_confession_copy` | `Yes. And yet here you are. We're not judging. We're invoicing.` | front-page.php confession |
| `tg_guarantees` | JSON list of 3: title+text (30-day regrow / Free shipping $50+ / No sunlight required) | front-page.php |
| `tg_footer_tagline` | `Premium plots of real grass for people who live indoors.` | footer.php |
| `tg_promo_code` | `GOOUTSIDE` | header announcement + checkout hint (display only) |

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

## Newsletter (real backend)

- Form in footer posts via AJAX action `tg_newsletter_subscribe` (nopriv allowed).
- Nonce: `tg_newsletter_nonce`. Validates email, checks duplicate by title, creates `tg_subscriber`.
- Response JSON `{success, message}`. JS swaps form → success message.
- No fake success: failures (invalid email, dupe, server error) show real error text.

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
- Location `footer`: theme renders footer columns from `footer` menu if set, else brand defaults.

## Demo Pay gateway

- ID `touchgrass_demo`. Title "Demo Pay". Classic: `WC_Payment_Gateway` subclass.
- Blocks: registers via `woocommerce_blocks_payment_method_type_registration`,
  JS at `assets/js/demo-pay-blocks.js` (vanilla, no build step), declares
  `supports: ['products']`, placeOrder → paymentMethodData.payment_method = id.
- Test mode: completes checkout, order goes to `processing` (documented, honest).

## Admin setup page

- Location: Settings → Touch Grass (or top-level "Touch Grass" menu).
- Shows: WooCommerce active ✓/✗, store pages ✓/✗, demo products (n/9), categories, coupon, FAQs, testimonials, menus assigned, Demo Pay enabled.
- Button: "Import Demo Content" → AJAX → runs importer → re-renders checklist.
- Importer is idempotent: products by SKU, terms by slug, media by slug, coupon by code, CPTs by title, menu by name.
