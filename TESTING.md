# Verification record

Release 1.1.0, October 2, 2026.

## Environments

WordPress Studio runs WordPress 7.1.2 / PHP 8.4 / SQLite / Twenty Twenty-Five with WP Recipe Maker 10.8.5 at http://localhost:8881. A second site at http://localhost:8882 runs the declared minimum WordPress 6.4.12 with PHP 8.2 and Twenty Twenty-Four.

## Completed verification

- 42 scenarios across installed Google Chrome, Playwright Firefox, and WebKit: 126 combinations verified through the full runs and focused reruns after fixes. Chrome and Firefox initially passed all 84; WebKit passed 38 of 42 initially. Keyboard fixes and focused reruns resolved the remaining plugin cases.
- Nine metadata stress combinations passed across all three engines. Actual plugin initialization completed below 1,000 ms on every stress fixture, including oversized, malformed, and deeply nested metadata.
- 26 PHP runtime assertions passed on both WordPress versions. 57 release regression checks passed on both versions and cover malformed saved options, stock-copy migration, Unicode limits, safe text, typed configuration, and translated strings.
- Settings integration passed: save, disable without frontend assets, edited copy, injection resistance, and restoration.
- Context and About tabs passed source-link, active-navigation, script-error, and overflow checks at 1280px and 390px.
- Minimum-version browser checks passed for recipe, manual override, ordinary content, and password-protected content.
- 200 local HTTP requests at four concurrent requests passed. Median response 634 ms, p95 1,535 ms, maximum 2,392 ms. Desktop and mobile receive identical cached configuration. This measures the local Studio environment, not production capacity.
- WordPress Plugin Check 2.1.0 reported no errors. PHP and JavaScript syntax checks passed.
- Accessibility checks cover modal semantics, visible focus, keyboard order and wrapping, return focus, minimum targets, narrow and short viewports, 200% text, reduced motion, and low-contrast theme fallback. Automated notice audits reported no serious or critical violations.

## Failure paths

Coverage includes clipboard denial, unavailable storage, corrupt and expired storage, unreasonable future expiration, missing or throwing native dialog support, duplicate script execution, disabled JavaScript, malformed JSON-LD, bounded oversized scans, root arrays, schema URLs, supported recipe containers, and HTTP/HTTPS microdata. Archives, feeds, embeds, protected pages, and unsupported browser identities do not show the notice.

The final stock-copy regression covers Windows-style line endings and trailing form whitespace. Only the recognized original stock copy migrates to the neutral default; custom publisher copy remains intact.

## Test boundaries

Mobile identities and viewports are emulated. Physical Android/iOS devices and Firefox summary generation were not tested. The plugin neither validates summaries nor prevents their generation.

WebKit's unrelated WordPress emoji worker intermittently failed on canvas access. Its normal capability cache is seeded only in WebKit tests; page-error assertions remain unfiltered. See [environment evidence](docs/WEBKIT-TEST-ENVIRONMENT.md). Stress timing measures plugin execution over loaded metadata, excluding browser startup and WordPress network latency.

## UI review

The Impeccable command package was unavailable. Its requested review sequence was applied directly, without claiming the commands ran: normalize against theme tokens and native WordPress components; adapt to mobile and short viewports; polish spacing and focus; clarify dismissal versus seven-day confirmation; harden text, storage, clipboard, metadata, and permissions; retain restrained press feedback with reduced-motion support. Context and About use the same native admin navigation and responsive layout. Rendered screenshots are in `artifacts/`.

## Reproduction

See README.md for site setup. Run `npm test`, the PHP checks through `studio wp eval-file`, `node tests/admin-check.cjs`, `node tests/content-check.cjs`, `node tests/load.mjs`, and `node tests/minimum-check.cjs`. `python3 scripts/package.py` creates the reproducible upload archive. Full and focused browser logs are preserved in `artifacts/`.
