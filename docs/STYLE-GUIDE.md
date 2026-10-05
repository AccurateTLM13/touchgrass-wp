# Touch Grass — Style Guide (v2.2.0)

The visual religion. Everything the brand ships must obey this document.
When in doubt, remove decoration before adding it.

## Palette

| Token        | Hex       | Use                                              |
|--------------|-----------|--------------------------------------------------|
| Paper        | `#FAF7F2` | Page background. Everything sits on paper.       |
| Surface      | `#FFFFFF` | Cards, panels, form fields.                     |
| Ink          | `#16211A` | Text, footer, dark sections, primary buttons.   |
| Muted        | `#5F6A61` | Secondary text.                                 |
| Faint        | `#8A938B` | Tertiary text, captions, footnotes.             |
| Line         | `#E7E0D2` | Borders, dividers. Never pure gray.             |
| Green        | `#2E5B3E` | Primary actions, eyebrows, links-on-paper.      |
| Deep green   | `#234830` | Hover states, dark-green accents.               |
| Green tint   | `#EAF0E9` | Badges, invoice banners, club panels.           |
| Gold         | `#C9A227` | Stars, eyebrows on dark sections. Sparingly.    |
| Error        | `#B3402E` | Form errors, low-stock warnings.                |

Rules:

- No new colors. If a design needs a color not in this table, the design is wrong.
- Dark sections (deed teaser, footer) use Ink backgrounds with `#EDE8DB` text and Gold eyebrows.
- Gold is for stars and ceremony only — never body text, never buttons.

## Typography

- **Display:** Fraunces (serif), weights 400–600. Headlines, prices on PDP, deed spec values, press quotes, guarantee titles. Italic is allowed for taglines, captions, and the batch line — nowhere else.
- **Sans:** Inter, weights 400–700. Body, buttons, labels, navigation, forms.
- **Mono:** system mono stack. Promo codes and announcement-bar codes only.

Rules:

- Eyebrows: 12px, 600 weight, 0.24em letter-spacing, uppercase, Green (Gold on dark).
- H1 (product): Fraunces 600. H2 sections: Fraunces 600, `clamp()`-scaled.
- Never set body text in Fraunces. Never set a headline in Inter.
- `text-wrap: balance` on headlines is encouraged; never on paragraphs.

## Voice registers (copy)

Two registers, never mixed on the same element:

1. **Product register** — straight-faced luxury. "Provenance: Plot 7, Surrey Grassworks." The product is always presented with total seriousness.
2. **Transaction register** — mercenary. "Complete Invoice." "We're not judging. We're invoicing." Lives in the microcopy map (Touch Grass → Settings → Microcopy in wp-admin) and is merchant-editable. Never hardcode transaction strings in templates — always `tg_microcopy( $key )`.

## Photography

Two series, never deviated from:

1. **Studio still-life (the Patek ad):** product on seamless paper, soft directional light, generous negative space. Hero, product cards, PDP gallery. The grass is always the hero; props are minimal and premium (brass mister, ceramic).
2. **Absurd lifestyle:** the product in a deadpan wrong context — boardroom, server rack, nightstand at 3am. Confession/how sections, social.

Rules:

- Never stock photos of actual lawns, gardens, or people touching grass outside. The joke is that nobody goes outside.
- Alt text describes the photo plainly; the joke lives in the caption (`figcaption`), not the alt.
- Product images: square-ish, consistent background tone across the lineup.

## Components

- **Buttons:** 10px radius, 600 weight. Primary = Green fill, white text, soft shadow; hover = Deep green, −1px lift. Secondary = transparent, Ink text, `#CFC7B4` border.
- **Cards:** Surface background, 1px Line border, 12px radius, soft shadow. Generous padding (2–2.5rem).
- **Trust block:** guarantee card → (reviews) → press strip → certification line. Press outlets are always obviously fictional. Never real publications.
- **Accordions:** native `<details>`/`<summary>`, Fraunces 20px titles, `+` marker rotating to `×`.
- **Deed spec rows:** label (sans, uppercase, 14px) / value (Fraunces, 18px) grid.
- **Announcement bar:** Ink background, 13px, centered. Promo codes in mono chips.

## Layout

- Max width 1180px, 2rem gutters (1.25rem under 600px).
- Sections: 4.5–5rem vertical padding. One focal point per section.
- Whitespace before decoration. When in doubt, more space, not less.
- Reveal-on-scroll is JS-gated: `.js .reveal` starts hidden; no-JS still shows content.
