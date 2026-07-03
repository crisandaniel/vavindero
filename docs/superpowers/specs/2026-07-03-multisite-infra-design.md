# Multisite Infrastructure (Subproject 1 of 4) — Design

## Context

Long-term goal: turn vavinde.ro into a mini-SaaS. Visitors register on the main
site, each registration provisions a subdomain site with a WooCommerce catalog,
and checkout is replaced by a "Send order" button that messages the order to
the registrant's WhatsApp number instead of taking payment.

That goal decomposes into four independent subprojects:

1. **Multisite infrastructure** (this spec) — network exists, subdomains route
   correctly, current site becomes the main site.
2. Self-service signup (collects WhatsApp number, provisions a subdomain site).
3. Automatic provisioning template (new sites get WooCommerce + a starter
   catalog out of the box).
4. WhatsApp checkout (replace the pay button with a "send order" flow).

This document covers subproject 1 only.

## Goal

Convert the current single-site WordPress install (local Docker dev copy of
vavinde.ro) to a subdomain-based Multisite network, with the existing site
preserved as the network's main site, and prove subdomain routing works by
manually creating one test site.

## Non-goals

- No self-service registration UI (subproject 2).
- No automatic WooCommerce/catalog setup for new sites (subproject 3).
- No WhatsApp checkout changes (subproject 4).
- No production changes — this is local-only, for evaluation before deciding
  whether to convert vavinde.ro's live site.

## Design

### Network type

Subdomain-based Multisite (`SUBDOMAIN_INSTALL = true`), since the product
requirement is "each user gets a subdomain."

### Port change

WordPress Multisite with subdomains has known issues on non-standard ports.
`WP_PORT` moves from `8080` to `80` in `.env`, so `DOMAIN_CURRENT_SITE` can be
`localhost` with no port suffix. Docker Desktop on macOS does not require
elevated privileges to bind port 80.

`phpmyadmin` keeps its own port (`PMA_PORT`, currently 8081) — unaffected.

### wp-config.php changes

1. Add `define('WP_ALLOW_MULTISITE', true);` before running Network Setup.
2. After running Network Setup (Tools → Network Setup, or
   `wp core multisite-convert` via the `wpcli` container), add the constants
   WordPress generates:
   - `define('MULTISITE', true);`
   - `define('SUBDOMAIN_INSTALL', true);`
   - `define('DOMAIN_CURRENT_SITE', 'localhost');`
   - `define('PATH_CURRENT_SITE', '/');`
   - `define('SITE_ID_CURRENT_SITE', 1);`
   - `define('BLOG_ID_CURRENT_SITE', 1);`

### .htaccess

Replace the current single-site rewrite rules with the multisite subdomain
rewrite rules WordPress generates during Network Setup.

### Existing site data

The current site (real production content mirrored via `db/init.sql`, plus
WooCommerce/Elementor/Hostinger plugins) becomes site ID 1 — the main site of
the network. No data migration needed; `wp core multisite-convert` preserves
the existing site in place.

Plugins currently active stay active only on the main site. Network-activation
for new sites is out of scope here (subproject 3).

### Docker / Apache

Assumption: the official `wordpress:php8.2-apache` image's default Apache
vhost has no `ServerName` restriction, so it accepts any `Host` header —
`*.localhost` requests should reach WordPress without Apache config changes.
This is validated empirically in the verification step below, not assumed
blindly; if subdomain requests don't reach the container correctly, the vhost
config will need a wildcard `ServerAlias`.

Assumption: modern browsers resolve `*.localhost` to `127.0.0.1` automatically
(no `/etc/hosts` edits needed). Also validated empirically.

## Implementation steps

1. Snapshot current DB state is already reproducible from `db/init.sql` — no
   extra backup step needed beyond confirming that file is current.
2. Update `.env`: `WP_PORT=80`.
3. Update `docker-compose.yml` port mapping for the `wordpress` service.
4. Add `WP_ALLOW_MULTISITE` constant to `wp-config.php`.
5. Recreate the `wordpress` container, confirm site still loads on
   `http://localhost/`.
6. Run Network Setup (subdomain option) via wp-admin or WP-CLI.
7. Add the generated multisite constants to `wp-config.php`.
8. Replace `.htaccess` with the generated multisite rules.
9. Recreate containers, confirm main site still loads at `http://localhost/`.
10. Create a test site (`test1.localhost`) via Network Admin → Sites → Add New.
11. Verify `http://test1.localhost/` loads the new site's own (empty)
    content, separate from the main site, and its wp-admin is reachable.

## Verification / success criteria

- `http://localhost/` loads the main site exactly as before (no regressions
  to the existing content/plugins).
- `http://localhost/wp-admin/network/` loads the Network Admin dashboard,
  showing 1 site initially.
- After creating `test1.localhost`: it resolves, shows a fresh/empty site
  (not the main site's content), and its own `/wp-admin/` is reachable and
  logs into that site's context.

## Rollback

Entirely local Docker state. If conversion goes wrong, `docker compose down -v`
and re-provision from `db/init.sql` restores the pre-multisite state.
