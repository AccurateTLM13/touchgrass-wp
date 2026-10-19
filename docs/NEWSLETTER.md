# Newsletter: local capture with optional Buttondown delivery

Every signup is captured as a private `tg_subscriber` record on this server —
the signup section is always visible and no provider account is required.
The **Touch Grass → Touch Grass Setup** dashboard has a **Subscribers**
section with a one-click **Download CSV** export (email, subscribed_at,
source, provider).

Optionally, signups can ALSO be delivered to a
[Buttondown](https://buttondown.com) audience via its API: paste a key into
**Buttondown API key** in the settings. Buttondown handles double
opt-in/consent according to your newsletter's own settings, and its emails
carry working unsubscribe links. If the Buttondown call ever fails, the
signup is still captured locally and the error is logged — a provider
outage never loses an email.

Tags are *not* sent by default: Buttondown rejects unknown tags on plans
without tag support (e.g. free), so forcing one would break delivery for
free accounts. If your plan supports tags and you want them, hook
`tg_newsletter_tags` and return your tag list.

## How signups behave

- **Success**: the form swaps to the confirmation message once the signup
  is captured (and delivered to Buttondown when a key is saved).
- **Duplicates** (already captured here, or already in Buttondown) get the
  "already on the list" message — no error, no duplicate record.
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
