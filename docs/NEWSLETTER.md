# Newsletter: Buttondown setup

Signups on the site are delivered to a [Buttondown](https://buttondown.com)
audience via its API. Buttondown handles double opt-in/consent according to
your newsletter's own settings, and its emails carry working unsubscribe
links.

## Setup (5 minutes, no code)

1. Create a free Buttondown account and a newsletter (publication).
2. Go to **Buttondown → Settings → API** and create an API key.
3. In wp-admin, go to **Touch Grass → Touch Grass Setup**, paste the key into
   **Buttondown API key**, and save.
4. The signup section appears on the homepage. Test it with your own address
   and confirm you land in **Buttondown → Subscribers**.

   Tags are *not* sent by default: Buttondown rejects unknown tags on plans
   without tag support (e.g. free), so forcing one would break signups for
   free accounts. If your plan supports tags and you want them, hook
   `tg_newsletter_tags` and return your tag list.

**Until a key is saved, the signup section is hidden** — visitors never see a
form that goes nowhere.

## How signups behave

- **Success** is shown only after Buttondown confirms the subscription
  (HTTP 201). The form then swaps to the confirmation message.
- **Duplicates** (already subscribed, here or in Buttondown) get the "already
  on the list" message — no error, no duplicate record.
- **Invalid emails** are rejected before anything is sent.
- **Abuse protection:** a honeypot field (bots get a fake success and nothing
  is stored), per-IP rate limiting (5 attempts/hour), and nonce verification.
  If a cached page carries an expired nonce, the form silently fetches a
  fresh one and retries once.
- **Failures** (network issues, bad key) show a human error message and are
  logged when `WP_DEBUG` is on. The local record is only created on confirmed
  success, so the store owner can trust it.

## Unsubscribe & data

- **Unsubscribe:** every Buttondown email includes an unsubscribe link, which
  is the supported path. Unsubscribing there does not delete the local record
  (see below).
- **Local records:** each confirmed signup is also stored as a private
  `tg_subscriber` post (Touch Grass → Subscribers) with the signup timestamp,
  source, and Buttondown subscriber ID — your own consent records. They are
  never public.
- **Retention/removal:** delete subscriber posts from Touch Grass →
  Subscribers at any time (bulk-select → Trash → Empty Trash). Deleting the
  plugin does not delete them; remove them manually if your policy requires
  it. To remove someone from the audience itself, use Buttondown →
  Subscribers.
- **Permissions:** subscriber posts are visible only to users who can
  `manage_options` (administrators) via the Touch Grass menu.
