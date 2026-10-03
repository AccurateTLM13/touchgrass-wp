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

echo ""; echo "$PASS passed, $FAIL failed"
