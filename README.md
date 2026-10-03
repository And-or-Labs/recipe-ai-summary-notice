# Recipe Warning

WordPress plugin inspired by Don Marti's proposal: give mobile Firefox readers a choice to use the original recipe instead of an untested AI summary.

## Local environment

- Project: `/Users/transl8r/Developer/recipe-warning`
- WordPress Studio desktop: `/Applications/Studio.app`
- Studio CLI: `/Users/transl8r/.local/bin/studio`
- Test site: http://localhost:8881/
- Recipe: http://localhost:8881/?p=12
- WP Recipe Maker integration: http://localhost:8881/?p=17
- Local admin: http://localhost:8881/studio-auto-login?redirect_to=%2Fwp-admin%2Foptions-general.php%3Fpage%3Drecipe-warning

The desktop browser normally shows the recipe. The warning targets Firefox on Android and iOS. Automated screenshots show the mobile state without changing your browser.

## Development

Source lives in `plugin/recipe-warning/`. The local Studio site is excluded from version control. Copy source into `site/wp-content/plugins/recipe-warning/` after edits.

```sh
npm ci
npx playwright install firefox webkit
studio site start --path ./site --skip-browser
npm test
```

Tests use installed Google Chrome, Playwright Firefox, and WebKit. Mobile identity and viewport are emulated; this is not physical-device testing of Firefox's summarization feature.

Create or refresh fixture pages:

```sh
cp tests/seed.php site/recipe-warning-seed.php
studio wp --path ./site eval-file "$PWD/site/recipe-warning-seed.php" > tests/fixtures.json
cp tests/php-checks.php site/recipe-warning-checks.php
studio wp --path ./site eval-file "$PWD/site/recipe-warning-checks.php"
```

## Behavior

- Enqueues only on singular, non-password-protected content, outside feeds and embeds.
- Browser-side targeting prevents user-agent-specific HTML from entering shared page caches.
- Recognizes schema.org Recipe JSON-LD/microdata, known recipe containers, supported shortcodes and blocks, and a manual post checkbox.
- Uses a native modal dialog with keyboard containment, Escape, focus restoration, and a permanent continue option.
- Stores only the expiry of an explicit disabled-summary confirmation, for seven days in localStorage on the current origin.
- Clipboard denial exposes a selectable URL. Storage failures do not prevent dismissal.
- Copy is plain text, sanitized on save and rendered using textContent. Settings and metadata writes use WordPress permissions and nonces.
- Inherits the active theme's base/contrast color and heading typography tokens. No remote assets or external requests.

## Scope

This is a warning plugin. It does not withhold recipe data or prevent Firefox from summarizing it. It cannot confirm browser settings, distinguish enabled/disabled summarization, or identify a spoofed user agent. Dynamic recipes injected after initial page readiness should use the editor checkbox.

Developed by And/or Labs Inc. Inspired by a discussion with Don Marti; attribution does not imply endorsement. Source conversation: https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/

## Release 1.1.0

Adds sourced news context, About/privacy/license disclosures, neutral defaults, translation support, bounded metadata scanning, contrast validation, and failure handling. The exact original stock warning is migrated; edited publisher messages are preserved.

Release archive: `artifacts/recipe-warning-1.1.0.zip`.

Admin context: http://localhost:8881/wp-admin/options-general.php?page=recipe-warning&tab=context

Admin About/privacy: http://localhost:8881/wp-admin/options-general.php?page=recipe-warning&tab=about

Additional checks:

```sh
node tests/content-check.cjs
node tests/load.mjs
node tests/minimum-check.cjs
```

The load test sends 200 requests to the local test site with four concurrent requests. It does not target production. The minimum-version check uses the separate Studio site at http://localhost:8882 and its generated fixtures.

See `docs/CONTEXT-AND-DISCLOSURES.md` for source-backed disclosures. These are technical publication materials, not a legal opinion or a guarantee against claims.
