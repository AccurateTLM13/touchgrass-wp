#!/bin/bash
# Touch Grass — content setup (products, images, coupon).
# Run AFTER: WordPress is installed, WooCommerce is installed + activated,
# the Touch Grass theme is installed + activated, and Demo Pay is activated.
#
# Usage: ./touchgrass-content-setup.sh /path/to/wordpress
#   (product-images/ folder must sit next to this script)
set -euo pipefail
WP="${1:?Usage: $0 /path/to/wordpress}"
IMG_SRC="$(cd "$(dirname "$0")/product-images" && pwd)"
cd "$WP"

echo "=== categories ==="
PLOTS=$(wp term create product_cat Plots --slug=plots --porcelain 2>/dev/null || wp term list product_cat --slug=plots --field=term_id)
ACC=$(wp term create product_cat Accessories --slug=accessories --porcelain 2>/dev/null || wp term list product_cat --slug=accessories --field=term_id)
echo "plots=$PLOTS accessories=$ACC"

echo "=== media import (idempotent: skips images already in the library) ==="
declare -A IMGMAP
for f in daily commuter standup promax pair starter night gnome mister; do
  existing=$(wp post list --post_type=attachment --name="$f" --field=ID 2>/dev/null | head -1)
  if [ -n "$existing" ]; then
    IMGMAP[$f]=$existing
    echo "$f -> $existing (already imported)"
  else
    id=$(wp media import "$IMG_SRC/$f.webp" --porcelain --title="$f")
    IMGMAP[$f]=$id
    echo "$f -> $id"
  fi
done

echo "=== products ==="
add_product() { # slug name price sale tagline badge cat img desc
  local slug="$1" name="$2" price="$3" sale="$4" tagline="$5" badge="$6" cat="$7" img="$8" desc="$9"
  local pid
  pid=$(wp post list --post_type=product --name="$slug" --field=ID 2>/dev/null | head -1)
  if [ -z "$pid" ]; then
    pid=$(wp post create --post_type=product --post_title="$name" --post_name="$slug" \
      --post_content="$desc" --post_excerpt="$tagline" --post_status=publish --porcelain)
    echo "created $name ($pid)"
  else
    wp post update "$pid" --post_content="$desc" --post_excerpt="$tagline" >/dev/null
    echo "updated $name ($pid)"
  fi
  wp post meta update "$pid" _price "$price" >/dev/null
  wp post meta update "$pid" _regular_price "$price" >/dev/null
  if [ -n "$sale" ]; then
    wp post meta update "$pid" _sale_price "$sale" >/dev/null
    wp post meta update "$pid" _price "$sale" >/dev/null
  else
    wp post meta delete "$pid" _sale_price >/dev/null 2>&1 || true
  fi
  wp post meta update "$pid" _visibility visible >/dev/null
  wp post meta update "$pid" _stock_status instock >/dev/null
  wp post meta update "$pid" _tax_status taxable >/dev/null
  wp post meta update "$pid" _tg_tagline "$tagline" >/dev/null
  wp post meta update "$pid" _tg_badge "$badge" >/dev/null
  wp post term set "$pid" product_cat "$cat" --by=id >/dev/null
  wp post meta update "$pid" _thumbnail_id "${IMGMAP[$img]}" >/dev/null
  # Register through WooCommerce's data store so products appear in the shop.
  wp wc product update "$pid" --type=simple >/dev/null 2>&1 || true
}

D1="The original. A hand-grown rectangle of real grass in a mint tray, sized for the space between your keyboard and your excuses.

Cut to order in our greenhouse and shipped within 48 hours. Mist lightly every two to three days. Touch daily — that is the entire program."
D2="Grass that travels better than you do. A pocket-sized plot in a travel tin, built for hotels, hackathons, and long layovers.

TSA has questions. We have answers. The tin keeps it alive for up to a week without attention, which is longer than most houseplants manage."
D3="A taller cut for standing desks and taller ambitions. Eye-level grass for eye-level meetings.

The Standup arrives pre-trimmed to a confident height and keeps growing, unlike your backlog. Your standups are about to get 40 percent calmer."
D4="Our largest plot. An XL desk lawn on a blush mat, for people whose commitment issues end at grass.

When you want the whole meadow minus the weather, the bugs, and the other people — this is the one. Low stock because the greenhouse can only grow so much ambition."
D5="Two plots, one per monitor. Rubber-duck debugging works better with photosynthesis nearby.

Symmetrical, suspiciously calming, and the only pair-programming partner who never comments on your code. Named variables not included."
D6="Soil, seed, and a tiny sense of responsibility. Grow your own plot and take full credit for nature.

Sprouts in 7 to 10 days with a light mist every two to three days. Instructions included; patience not included."
D7="A meadow that glows after hours, moon lamp included. For deploys that run past midnight and ideas that only arrive at 2am.

The lamp runs on USB and casts a soft lunar glow across the grass. Your nightstand has never looked this intentional."
D8="4cm of silent judgment. He watches you skip standup. He has seen things.

Not grass, strictly speaking — but every plot needs a supervisor. Hand-painted, unbothered, and weirdly supportive."
D9="A brass mister for the morning ritual. The grass expects punctuality.

A light mist every two to three days keeps the meadow smug and the ritual intact. Solid brass, satisfying heft, zero batteries."

add_product "the-daily-driver"     "The Daily Driver"     29 ""   "Classic desk plot · mint tray"   "Bestseller" "$PLOTS" "daily"    "$D1"
add_product "the-commuter"        "The Commuter"         24 ""   "Portable plot · travel tin"      ""           "$PLOTS" "commuter" "$D2"
add_product "the-standup"         "The Standup"          34 ""   "Tall plot · standing desks"      ""           "$PLOTS" "standup"  "$D3"
add_product "the-pro-max"         "The Pro Max"          74 59   "XL desk lawn · blush mat"        "Low stock"  "$PLOTS" "promax"   "$D4"
add_product "pair-programmer"     "Pair Programmer"      44 ""   "Two plots · one per monitor"     ""           "$PLOTS" "pair"     "$D5"
add_product "seedling-starter-kit" "Seedling Starter Kit" 19 "" "Grow your own · soil included"   ""           "$PLOTS" "starter"  "$D6"
add_product "the-night-shift"     "The Night Shift"      39 ""   "Glow meadow · moon lamp included" "Staff pick" "$PLOTS" "night"   "$D7"
add_product "the-tiny-gnome"      "The Tiny Gnome"       12 ""   "Companion · 4cm of wisdom"       ""           "$ACC"   "gnome"    "$D8"
add_product "the-mister"          "The Mister"           16 ""   "Brass mister · for the ritual"   ""           "$ACC"   "mister"   "$D9"

echo "=== coupon GOOUTSIDE (20% off) ==="
if ! wp wc shop_coupon list --code=GOOUTSIDE --field=id 2>/dev/null | grep -q .; then
  wp wc shop_coupon create --code=GOOUTSIDE --discount_type=percent --amount=20 \
    --description="Fresh Cut Friday — 20% off, for the irony" --quiet
  echo "coupon created"
else
  echo "coupon exists"
fi

echo "=== homepage ==="
wp option update show_on_front posts >/dev/null
wp rewrite flush >/dev/null 2>&1 || true

echo "=== DONE ==="
echo "Visit your site — the Touch Grass storefront should be live."
