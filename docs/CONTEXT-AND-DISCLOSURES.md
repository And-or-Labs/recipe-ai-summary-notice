# Recipe Warning: context and disclosures

Source review: October 2, 2026. The sections below record the sourced publication disclosures. Implementation-specific privacy and retention statements describe the reviewed plugin; keep them synchronized with releases.

## Context

On October 1, 2026, Mozilla described how the Firefox browser uses recipe-specific prompts and webpage structured data to produce recipe summaries. Mozilla reports improvements in completeness and accuracy from its prompt changes. [Mozilla's engineering article](https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/)

Page summaries are available in the Firefox browser on Android and iOS, with availability varying by rollout and device. Mozilla advises readers to check important details against the original page because generated summaries can contain errors. Recipe Warning gives publishers a way to make that reminder visible on recipe pages. [Android help](https://support.mozilla.org/en-US/kb/summarize-pages-android), [iOS help](https://support.mozilla.org/en-US/kb/summarize-pages-ios)

## About

Recipe Warning is an independent WordPress plugin that displays a dismissible notice to visitors whose browser identifies itself as Firefox on Android or iOS. Readers can continue to the original recipe, copy its link, or confirm that they have disabled page summaries. The plugin leaves the recipe and its structured data intact.

The project grew out of a discussion with Don Marti about helping readers support recipe creators. That acknowledgement does not imply that he developed, reviewed, or endorsed this release.

## Suggested default notice

Title: Keep the original recipe

Message: AI summaries can contain errors. Check ingredients, quantities, timings, and instructions against the original recipe. You can continue to the recipe below. This notice does not check or change your browser settings.

Optional settings help: In the Firefox browser, open Settings > Page Summaries and turn off Summarize Pages. Turning off only Shake to Summarize can leave other ways to request summaries available. Menu names and availability may change. [Android instructions](https://support.mozilla.org/en-US/kb/summarize-pages-android), [iOS instructions](https://support.mozilla.org/en-US/kb/summarize-pages-ios)

## Privacy

Recipe Warning runs on this website and in your browser. It adds no analytics, tracking cookies, external assets, or calls to AI services. Browser identification is checked locally using the browser's user-agent string and is not sent elsewhere by the plugin.

If you confirm that page summaries are disabled, the plugin stores an expiry timestamp in this website's local browser storage. It uses that timestamp to suppress the notice for seven days. Clearing this website's browser storage removes the preference. Expired or invalid timestamps are removed when the warning next checks storage; browser storage may retain them until that visit. Continuing to the recipe or pressing Escape dismisses the current notice without saving a preference. The copy-link button writes the current page URL to your clipboard only when selected, or shows a selectable URL if clipboard access is unavailable.

Plugin settings and manual recipe-page flags are stored in the WordPress database and retained when the plugin is removed. Other plugins, this website, your browser, and your hosting provider may have separate data practices.

## Limitations

The notice does not block scraping or AI summaries, remove structured data, verify browser settings, or establish whether summaries are available to a particular visitor. Browser identification and recipe detection can miss pages or visitors. The original page remains available when JavaScript or the required browser features are unavailable.

Recipe Warning does not evaluate recipes, food safety, allergens, nutrition, or generated summaries. It makes no claim that a site's recipes have been tested. It does not guarantee search rankings, publisher revenue, copyright protection, accessibility conformance, or legal compliance. Site owners control their notice wording and remain responsible for their published content.

## License and no warranty

Recipe Warning is free software licensed under the GNU General Public License, version 2 or any later version. It is provided without warranty to the extent permitted by applicable law, including implied warranties of merchantability or fitness for a particular purpose. Liability limitations are governed by the license and applicable law. Nothing in these notices excludes rights or liabilities that cannot lawfully be excluded. See the distributed license for the full terms. [GPLv2, sections 11 and 12](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

This plugin and its documentation provide technical information, not legal advice. Installing the plugin or displaying a notice does not itself establish legal compliance or immunity from claims.

## Trademarks and independence

Firefox and Mozilla are trademarks of the Mozilla Foundation in the United States and other countries. Recipe Warning is an independent project and is not affiliated with, sponsored by, or endorsed by Mozilla. Other product names identify the products discussed and belong to their respective owners. [Mozilla trademark guidelines](https://www.mozilla.org/en-US/foundation/trademarks/policy/)

## Publication decisions

- Use the neutral default above for new installations. Version 1.0.0 asserted that every publisher tested its recipe and called summaries “slop.” Version 1.1.0 migrates only that exact stock wording and preserves edited messages.
- Keep the independent Recipe Warning name and slug. Do not use Mozilla logos or imply official affiliation.
- Put source links, provenance, and disclosures in the readme and settings page. Do not add public credits or external links by default. WordPress requires an explicit opt-in for those displays.
- Include the complete GPLv2 license in the distributable. The GNU website failed to load during this review; the license and its warranty sections were verified against [WordPress's official distributed license](https://raw.githubusercontent.com/WordPress/WordPress/master/license.txt).
- Describe tested configurations precisely. Emulated mobile browser identity is not a test of the native mobile summarization feature. Do not claim universal compatibility or a legal shield.

These decisions follow the requirements on licensing, privacy, honest claims, optional public credits, and trademarks in the [WordPress Plugin Directory guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/). WordPress also explicitly rejects promises of legal compliance; a disclaimer does not cure contradictory marketing claims. See its [compliance disclaimer guidance](https://developer.wordpress.org/plugins/wordpress-org/compliance-disclaimers/).
