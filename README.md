# Touch Grass — WordPress theme + companion plugin

A production-quality, installable WordPress theme with an optional companion
plugin for the Touch Grass store. Straight-faced luxury on the product pages,
mercenary honesty at checkout.

- **Theme** (`theme/touchgrass`): presentation only. Works with or without
  WooCommerce and without the companion plugin.
- **Plugin** (`plugins/touchgrass-core`): functionality — FAQ/testimonial/
  subscriber content types, product fields, demo importer, newsletter signup
  (Buttondown), Demo Pay mock gateway (demo mode only), and the setup
  dashboard.

Requires **WordPress 6.5+**, **PHP 8.1+**; WooCommerce 9.0+ for commerce.

## Install

See [`docs/INSTALL.md`](docs/INSTALL.md) — upload two ZIPs, activate, follow
the Touch Grass Setup checklist. No SSH, no PHP edits.

Build the ZIPs reproducibly with `./build.sh` (outputs to `dist/`).

## Docs

- `docs/INSTALL.md` — installation, demo content, updates, uninstall
- `docs/PAYMENTS.md` — production gateway setup and demo mode
- `docs/NEWSLETTER.md` — Buttondown setup, unsubscribe, data retention
- `docs/CUSTOMIZE.md` — every Customizer control and content type

## Development

- Interface contract between theme and plugin: [`CONTRACT.md`](CONTRACT.md).
- Integration tests (run against a test install with WP-CLI):
  `wp eval-file tests/integration.php`
- HTTP-level tests: `tests/http-tests.sh` (playground-oriented).
- CI (`.github/workflows/ci.yml`): PHP lint, JS syntax check, ZIP build.

## Brand voice

Product presentation is straight-faced luxury; transaction copy lets the
mercenary mask slip. "Going outside is free." "We're not judging. We're
invoicing." "Your money is safe. For now."
