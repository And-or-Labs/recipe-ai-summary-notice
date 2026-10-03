# Verification record

Completed October 2, 2026.

## Environment

WordPress Studio desktop and standalone CLI installed from Automattic's official downloads. Local site runs WordPress 7.1.2, PHP 8.4, SQLite, Twenty Twenty-Five, and WP Recipe Maker 10.8.5.

## Results

- 19 browser scenarios on each of installed Google Chrome and Playwright Firefox: 38 combinations verified.
- 26 WordPress runtime assertions passed, covering plain-text sanitization, malformed input, defaults, shortcode/block hints, manual overrides, nonce enforcement, and capabilities.
- Settings integration passed: saving, disabling without loading frontend assets, edited copy rendered, injected HTML unable to execute, and original settings restored.
- Packaged ZIP installed and activated through WordPress's installer.
- PHP syntax and JavaScript syntax checks passed.
- Automated accessibility audit: zero serious or critical violations in the notice on both engines.
- Rendered mobile notice, desktop recipe, and native WordPress settings screenshots inspected.

The initial browser run passed 35 of 38 combinations. It exposed WordPress localization converting the expiry duration to a string and native dialog tabbing reaching browser chrome. The duration now converts explicitly to a number; Tab boundaries wrap explicitly. Eight focused checks passed after the fixes, followed by two strict keyboard checks. No unresolved failures remain.

Mobile device identity and viewport are emulated on desktop engines. Physical Android/iOS devices and Firefox's summary generation were not part of this test. Tests verify the warning plugin, not summary accuracy or prevention.

## Browser coverage

- Android Firefox and iOS Firefox trigger only on recipes.
- Desktop Firefox, Android Chrome, and iOS Safari identities do not trigger.
- JSON-LD recipe, manually marked post, and a real WP Recipe Maker card trigger.
- Ordinary and password-protected pages do not trigger.
- Seven-day confirmation survives reload and expires correctly.
- Continue and Escape dismiss only the current visit and restore content focus.
- Clipboard success, denial, and selectable fallback.
- Unavailable local storage and malformed JSON-LD remain usable.
- Keyboard order, forward/reverse wrap, and inert page background.
- 320px width, 44px minimum controls, and accessible modal semantics.

## UI review

The Impeccable command package was not installed. The requested review sequence was applied directly to the two page types rather than claiming those commands ran.

| Pass | Reader notice | WordPress settings |
| --- | --- | --- |
| Normalize | Active theme color/font tokens; native dialog | Native Settings API and WordPress classes |
| Adapt | 320px and 390px layouts; scrollable short viewport | Native responsive WordPress form layout |
| Polish | Balanced heading, consistent spacing, visible focus | Associated labels, standard control sizing |
| Clarify | Explicit continue versus remembered confirmation | Accurate seven-day explanation and scope |
| Harden | Clipboard/storage fallback, safe text, keyboard wrap | Sanitization, nonces, capability enforcement |
| Delight | Original dry warning copy, subtle reduced-motion-aware press feedback | Don Marti attribution, no extra dashboard UI |

## Changes from review

| Before | After |
| --- | --- |
| WordPress TTL arrived as text | Numeric conversion before expiry arithmetic |
| Native modal allowed a browser-chrome Tab stop | Explicit forward/reverse focus wrapping |
| Full-page capture extended beyond the modal backdrop | Mobile screenshot captures the actual viewport |
| Settings implied every dismissal persisted | Only disabled-summary confirmation persists |

## Reproduce

See README.md for setup, fixture generation, and test commands. `node tests/admin-check.cjs` tests settings on the disposable local site. `python3 scripts/package.py` creates the upload archive reproducibly.
