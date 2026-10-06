#!/bin/bash
# HTTP-level tests for Touch Grass production-quality work.
# Assumes playground running at http://localhost:8080.
set -u
WP="$HOME/workspace/tools/php-static/php $HOME/workspace/playground/wp-cli.phar --path=$HOME/workspace/playground/www --allow-root"
BASE="http://localhost:8080"
PASS=0; FAIL=0
ok()   { PASS=$((PASS+1)); echo "PASS: $1"; }
no()   { FAIL=$((FAIL+1)); echo "FAIL: $1 -- $2"; }

# H1: homepage renders, no fatal, no demo notice, no newsletter (no API key)
HOME_HTML=$(curl -s --max-time 20 "$BASE/")
echo "$HOME_HTML" | grep -qi "fatal error\|call to undefined" && no "H1 homepage no fatal" "fatal text found" || ok "H1 homepage no fatal"
echo "$HOME_HTML" | grep -q "tg-demo-note" && no "H1 no demo notice when demo off" "notice found" || ok "H1 no demo notice when demo off"
echo "$HOME_HTML" | grep -q 'id="newsForm"' && no "H1 newsletter hidden when unconfigured" "form found" || ok "H1 newsletter hidden when unconfigured"
echo "$HOME_HTML" | grep -q 'class="skip-link"' && ok "H1 skip link present" || no "H1 skip link present" "missing"

# nonce for newsletter tests
NONCE=$(echo "$HOME_HTML" | grep -o 'name="tg_newsletter_nonce" value="[a-f0-9]*"' | head -1 | grep -o '[a-f0-9]*$')
[ -z "$NONCE" ] && NONCE=$(curl -s --max-time 20 "$BASE/?tg_nonce_probe=1" >/dev/null; $WP eval "echo wp_create_nonce('tg_newsletter');" 2>/dev/null | tail -1)

# H5a: invalid email rejected
R=$(curl -s --max-time 20 -X POST "$BASE/wp-admin/admin-ajax.php" --data-urlencode "action=tg_newsletter_subscribe" --data-urlencode "tg_newsletter_nonce=$NONCE" --data-urlencode "email=not-an-email")
echo "$R" | grep -q '"success":false' && ok "H5a invalid email rejected" || no "H5a invalid email rejected" "$R"
# H5b: honeypot filled -> fake success, no subscriber created
BEFORE=$($WP post list --post_type=tg_subscriber --format=count 2>/dev/null | tail -1)
R=$(curl -s --max-time 20 -X POST "$BASE/wp-admin/admin-ajax.php" --data-urlencode "action=tg_newsletter_subscribe" --data-urlencode "tg_newsletter_nonce=$NONCE" --data-urlencode "email=bot@example.com" --data-urlencode "tg_company=spamco")
AFTER=$($WP post list --post_type=tg_subscriber --format=count 2>/dev/null | tail -1)
echo "$R" | grep -q '"success":true' && [ "$BEFORE" = "$AFTER" ] && ok "H5b honeypot fake-success, nothing stored" || no "H5b honeypot" "$R before=$BEFORE after=$AFTER"
# H7: expired nonce -> code expired_nonce; refresh endpoint works
R=$(curl -s --max-time 20 -X POST "$BASE/wp-admin/admin-ajax.php" --data-urlencode "action=tg_newsletter_subscribe" --data-urlencode "tg_newsletter_nonce=deadbeefdeadbeef" --data-urlencode "email=a@example.com")
echo "$R" | grep -q 'expired_nonce' && ok "H7 expired nonce returns expired_nonce code" || no "H7 expired nonce" "$R"
R=$(curl -s --max-time 20 -X POST "$BASE/wp-admin/admin-ajax.php" --data-urlencode "action=tg_newsletter_nonce_refresh")
echo "$R" | grep -q '"success":true' && echo "$R" | grep -q '"nonce"' && ok "H7 nonce refresh endpoint works" || no "H7 nonce refresh" "$R"
# H4: rate limit — 6 rapid valid-format attempts, 6th must be limited (do last: burns the hourly budget)
for i in 1 2 3 4 5 6; do
  R=$(curl -s --max-time 20 -X POST "$BASE/wp-admin/admin-ajax.php" --data-urlencode "action=tg_newsletter_subscribe" --data-urlencode "tg_newsletter_nonce=$NONCE" --data-urlencode "email=ratelimit$i@example.com")
  [ $i -eq 6 ] && LAST="$R"
done
echo "$LAST" | grep -qi "too many attempts" && ok "H4 rate limit triggers on 6th attempt" || no "H4 rate limit" "$LAST"

# H2: plugin deactivated — homepage, shop, product render without fatal
$WP plugin deactivate touchgrass-core --quiet 2>/dev/null
for path in "" "/shop/" "$($WP post list --post_type=product --posts_per_page=1 --field=post_name 2>/dev/null | head -1 | sed 's|^|/?product=|')"; do
  CODE=$(curl -s -o /tmp/h2.html -w "%{http_code}" --max-time 20 "$BASE$path")
  if [ "$CODE" = "200" ] && ! grep -qi "fatal error" /tmp/h2.html; then ok "H2 plugin-off renders $path ($CODE)"; else no "H2 plugin-off renders $path" "code=$CODE"; fi
done
grep -q ">Bestseller<\|>Low stock<\|>Staff pick<" /tmp/h2.html && no "H2 shop works without plugin badge fn" "badge label leaked" || ok "H2 no badge label without plugin"
$WP plugin activate touchgrass-core --quiet 2>/dev/null

# H3: WooCommerce deactivated — homepage + page render, cart button hidden
$WP plugin deactivate woocommerce --quiet 2>/dev/null
CODE=$(curl -s -o /tmp/h3.html -w "%{http_code}" --max-time 20 "$BASE/")
[ "$CODE" = "200" ] && ! grep -qi "fatal error" /tmp/h3.html && ok "H3 WC-off homepage 200, no fatal" || no "H3 WC-off homepage" "code=$CODE"
grep -q "cart-btn" /tmp/h3.html && no "H3 cart button hidden without WC" "found" || ok "H3 cart button hidden without WC"
$WP plugin activate woocommerce --quiet 2>/dev/null

# H6: demo mode ON — demo notice on cart page
$WP option update tg_demo_mode 1 --quiet 2>/dev/null
CART_URL=$($WP eval "echo wc_get_cart_url();" 2>/dev/null | tail -1)
CART_HTML=$(curl -s --max-time 20 "$CART_URL")
echo "$CART_HTML" | grep -q "tg-demo-note" && ok "H6 demo notice on cart when demo on" || no "H6 demo notice on cart" "missing"
$WP option update tg_demo_mode 0 --quiet 2>/dev/null
CART_HTML=$(curl -s --max-time 20 "$CART_URL")
echo "$CART_HTML" | grep -q "tg-demo-note" && no "H6 demo notice gone when demo off" "still present" || ok "H6 demo notice gone when demo off"

# H8: v2.2.0 gag patterns — homepage sections, PDP spine, trust block
PDP_URL=$($WP eval "echo get_permalink(wc_get_product_id_by_sku('TG-DAILY'));" 2>/dev/null | tail -1)
PDP_HTML=$(curl -s --max-time 20 "$PDP_URL")
echo "$PDP_HTML" | grep -qi "fatal error\|call to undefined" && no "H8 PDP no fatal" "fatal text found" || ok "H8 PDP no fatal"
echo "$PDP_HTML" | grep -q "The Deed" && ok "H8 PDP has Deed tab" || no "H8 PDP has Deed tab" "missing"
echo "$PDP_HTML" | grep -q "Complete the Ritual" && ok "H8 PDP has ritual cross-sells" || no "H8 PDP has ritual cross-sells" "missing"
echo "$PDP_HTML" | grep -q "tg-sticky-atc" && ok "H8 PDP has sticky ATC" || no "H8 PDP has sticky ATC" "missing"
echo "$PDP_HTML" | grep -q "per blade" && ok "H8 PDP has per-blade line" || no "H8 PDP has per-blade line" "missing"
echo "$PDP_HTML" | grep -q "Batch No. 7" && ok "H8 PDP shows batch" || no "H8 PDP shows batch" "missing"
echo "$PDP_HTML" | grep -q "How It Ships" && ok "H8 PDP has How It Ships accordion" || no "H8 PDP has How It Ships accordion" "missing"
echo "$PDP_HTML" | grep -q "30-Day Photosynthesis Promise" && ok "H8 PDP has trust block" || no "H8 PDP has trust block" "missing"
echo "$PDP_HTML" | grep -q "Screen Time Weekly" && ok "H8 PDP press strip fictional" || no "H8 PDP press strip fictional" "missing"
echo "$PDP_HTML" | grep -q "Claim Your Plot" && ok "H8 PDP ATC uses microcopy" || no "H8 PDP ATC uses microcopy" "missing"
NEWS_CFG=$($WP eval "echo function_exists('tg_newsletter_configured') && tg_newsletter_configured() ? 'yes' : 'no';" 2>/dev/null | tail -1)
if [ "$NEWS_CFG" = "yes" ]; then echo "$HOME_HTML" | grep -q "The Grass Club" && ok "H8 homepage has Grass Club" || no "H8 homepage has Grass Club" "missing"; else ok "H8 Grass Club skipped (newsletter unconfigured)"; fi
echo "$HOME_HTML" | grep -q "Every plot ships with The Deed" && ok "H8 homepage has Deed teaser" || no "H8 homepage has Deed teaser" "missing"
echo "$HOME_HTML" | grep -q "application/ld+json" && ok "H8 homepage has FAQ JSON-LD" || no "H8 homepage has FAQ JSON-LD" "missing"
# Note: the playground cart page uses WooCommerce blocks (JS-rendered), so the
# PHP gettext filter applies to the classic cart path. Verify the mechanism.
COUPON_LABEL=$($WP eval "echo apply_filters('gettext', 'Coupon code', 'Coupon code', 'woocommerce');" 2>/dev/null | tail -1)
[ "$COUPON_LABEL" = "Abatement code" ] && ok "H8 coupon label from microcopy map" || no "H8 coupon label from microcopy map" "got: $COUPON_LABEL"

# H9: v2.3.0 Grassworks Institution — deed hierarchy, club tiers, conveyance
echo "$PDP_HTML" | grep -q "Surrey Grassworks" && ok "H9 PDP shows corporation" || no "H9 PDP shows corporation" "missing"
echo "$PDP_HTML" | grep -q "North Greenhouse Division" && ok "H9 PDP shows division" || no "H9 PDP shows division" "missing"
echo "$PDP_HTML" | grep -q "Indoor Recreation Commodity Program" && ok "H9 PDP shows program" || no "H9 PDP shows program" "missing"
echo "$PDP_HTML" | grep -q "Plot 184-C" && ok "H9 PDP shows plot number" || no "H9 PDP shows plot number" "missing"
echo "$PDP_HTML" | grep -q "Executive Desk Grade" && ok "H9 PDP shows classification" || no "H9 PDP shows classification" "missing"
echo "$PDP_HTML" | grep -q "Deed of Grass" && ok "H9 PDP deed certificate masthead" || no "H9 PDP deed certificate masthead" "missing"
# Club section is gated on newsletter configuration: enable a dummy key for the render check, then remove it.
$WP option update tg_buttondown_api_key tg_test_dummy --quiet 2>/dev/null
HOME_CLUB_HTML=$(curl -s --max-time 20 "$BASE/")
$WP option delete tg_buttondown_api_key --quiet 2>/dev/null
echo "$HOME_CLUB_HTML" | grep -q "The grass keeps score" && ok "H9 homepage club tagline" || no "H9 homepage club tagline" "missing"
for tier in Prospect Seedling Sod Estate; do
  echo "$HOME_CLUB_HTML" | grep -q ">$tier<" && ok "H9 homepage shows $tier tier" || no "H9 homepage shows $tier tier" "missing"
done
echo "$HOME_CLUB_HTML" | grep -q "Annual inspection waived" && ok "H9 homepage estate benefit" || no "H9 homepage estate benefit" "missing"
# Conveyance block: render the thankyou action for a scratch order via WP-CLI.
CONV_HTML=$($WP eval "
\$o = wc_create_order();
\$o->set_billing_first_name('Holly');
\$o->set_billing_last_name('Hock');
\$o->add_product(wc_get_product(wc_get_product_id_by_sku('TG-DAILY')), 1);
\$o->calculate_totals(); \$o->save();
\$id = \$o->get_id();
ob_start(); do_action('woocommerce_thankyou', \$id); \$h = ob_get_clean();
echo \$h;
wp_delete_post(\$id, true);
" 2>/dev/null | tail -5)
echo "$CONV_HTML" | grep -q "Official Notice of Conveyance" && ok "H9 conveyance block renders" || no "H9 conveyance block renders" "missing"
echo "$CONV_HTML" | grep -q "TG-00" && ok "H9 conveyance has registry number" || no "H9 conveyance has registry number" "missing"
echo "$CONV_HTML" | grep -q "Holly Hock" && ok "H9 conveyance names owner" || no "H9 conveyance names owner" "missing"
REG_FMT=$($WP eval "echo tg_plot_registry_number(42);" 2>/dev/null | tail -1)
[ "$REG_FMT" = "TG-00042" ] && ok "H9 registry number format" || no "H9 registry number format" "got: $REG_FMT"

# H10: v2.4.0 SEO + social + speed
echo "$HOME_HTML" | grep -q 'property="og:image"' && ok "H10 homepage has og:image" || no "H10 homepage has og:image" "missing"
echo "$HOME_HTML" | grep -q 'name="twitter:image"' && ok "H10 homepage has twitter:image" || no "H10 homepage has twitter:image" "missing"
echo "$HOME_HTML" | grep -q 'name="description"' && ok "H10 homepage has meta description" || no "H10 homepage has meta description" "missing"
echo "$HOME_HTML" | grep -q 'property="og:description"' && ok "H10 homepage has og:description" || no "H10 homepage has og:description" "missing"
echo "$HOME_HTML" | grep -q 'og-default.jpg' && ok "H10 homepage og:image is theme default" || no "H10 homepage og:image default" "missing"
echo "$HOME_HTML" | grep -q '"@type":"Organization"' && ok "H10 homepage has Organization schema" || no "H10 homepage Organization schema" "missing"
echo "$PDP_HTML" | grep -q 'property="og:image"' && ok "H10 PDP has og:image" || no "H10 PDP og:image" "missing"
echo "$PDP_HTML" | grep -q 'og-default.jpg' && no "H10 PDP og:image overrides default" "still default" || ok "H10 PDP og:image overrides default"
echo "$PDP_HTML" | grep -q '"@type":"Product"' && ok "H10 PDP has Product schema" || no "H10 PDP Product schema" "missing"
echo "$PDP_HTML" | grep -q '"price"' && ok "H10 PDP schema has price" || no "H10 PDP schema price" "missing"
echo "$HOME_HTML" | grep -q 'fetchpriority="high"' && ok "H10 hero image has fetchpriority" || no "H10 hero fetchpriority" "missing"
echo "$HOME_HTML" | grep -o 'loading="lazy"' | wc -l | grep -q '[1-9]' && ok "H10 below-fold images lazy" || no "H10 lazy images" "none found"

echo ""; echo "$PASS passed, $FAIL failed"
