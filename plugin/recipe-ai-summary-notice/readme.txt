=== Recipe AI Summary Notice for Firefox ===
Contributors: eclecticv, vj1987
Tags: recipes, ai summaries, firefox
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Give mobile Firefox readers a choice to use the original recipe, with a dismissible notice about AI summaries.

== Description ==

Recipe AI Summary Notice for Firefox adds a reader notice to recipe pages when a visitor's browser identifies itself as Firefox on Android or iOS. Readers can continue to the original recipe, copy its link for another browser, or confirm that they have disabled page summaries. The original recipe and its structured data stay intact.

[Source code and issue tracker](https://github.com/And-or-Labs/recipe-ai-summary-notice)

**Reader controls**

* Continue or press Escape to dismiss the current notice.
* Confirm summaries are disabled to remember that preference for seven days on this site in this browser.
* Copy the page link. If clipboard access is unavailable, select and copy the displayed URL.

**Publisher controls**

* Edit the title and message under Settings > Recipe AI Summary Notice for Firefox.
* Recognize Recipe JSON-LD, Recipe microdata, WP Recipe Maker, and supported recipe blocks and shortcodes.
* Mark other recipe formats with the editor's "Treat this as a recipe page" checkbox.
* Review the Context and About & privacy tabs, including suggested text for WordPress's Privacy Policy Guide.

The plugin uses browser-side detection so shared page caches do not store a different warning page for each browser. There are no external services, AI APIs, remote assets, analytics, or paid dependencies. All executable source is included in the plugin.

**News context**

On October 1, 2026, Mozilla described its use of recipe-specific prompts and webpage structured data for Firefox recipe summaries. Mozilla reports improvements in completeness and accuracy from those changes. The plugin does not claim that all summaries are wrong. It gives publishers a way to distinguish generated summaries from their original pages.

[Mozilla's engineering article](https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/)

Page-summary availability depends on browser version, device, and rollout. See Mozilla's current [Android documentation](https://support.mozilla.org/en-US/kb/summarize-pages-android) and [iOS documentation](https://support.mozilla.org/en-US/kb/summarize-pages-ios). Disabling only the shake gesture may leave summaries available through other controls.

**About and attribution**

Developed by And/or Labs Inc. Credit for the original idea goes to Don Marti, whose proposal prompted this plugin. That acknowledgement does not imply that he developed, reviewed, or endorsed this release. [Source discussion](https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/)

Recipe AI Summary Notice for Firefox is independent and is not affiliated with, sponsored by, or endorsed by Mozilla or the WordPress project. Firefox and Mozilla are trademarks of the Mozilla Foundation in the United States and other countries. Other product names identify their respective products and owners. No third-party logos are included.

**Privacy**

The plugin checks browser identification locally. It does not transmit it to another service, set cookies, track visitors, or call an AI service.

Only an explicit disabled-summary confirmation saves an expiry timestamp in this site's localStorage under `recipe-warning-bypass-v1`. The timestamp suppresses the notice for seven days. Expired or invalid entries are removed the next time the warning checks storage; they may remain until that visit. Clearing this site's browser storage removes the preference. Continue and Escape store no preference. Copy writes the current URL to the clipboard only when selected.

Settings and manual recipe flags are stored in the WordPress database and retained on uninstall. The plugin does not add personal information to those records. Other parts of the site, its hosting, and the browser have separate data practices.

**Limitations and license**

This is a notice, not a blocker. It does not prevent scraping or summarization, remove recipe data, verify browser settings, or determine whether a particular visitor has the summary feature. Browser and recipe detection are approximate. Recipes inserted after page readiness, unusual formats, or metadata exceeding scanning limits should use the manual checkbox.

The plugin does not assess recipes, allergens, nutrition, food safety, or generated summaries. It makes no claim that a site's recipes were tested. It provides technical information, not medical, food-safety, or legal advice. It does not guarantee revenue, search rankings, copyright protection, accessibility conformance, or legal compliance. Publishers control their copy and remain responsible for their content.

Licensed under GPL version 2 or any later version. Provided without warranty to the extent permitted by applicable law, including implied warranties of merchantability or fitness for a particular purpose. Liability limitations are governed by the license and applicable law. Nothing here excludes rights or liabilities that cannot lawfully be excluded. See LICENSE.txt for the full license. A notice or disclaimer does not create legal immunity.

== Installation ==

Updating from the GitHub release named Recipe Warning: deactivate the old plugin before activating this renamed plugin. Existing settings and recipe flags are retained. Do not activate both copies together.

1. Upload recipe-ai-summary-notice-1.3.0.zip under Plugins > Add New Plugin > Upload Plugin.
2. Activate Recipe AI Summary Notice for Firefox.
3. Review the wording under Settings > Recipe AI Summary Notice for Firefox and test on a staging site.
4. For a recipe without recognized markup, check "Treat this as a recipe page" in its editor.

== Frequently Asked Questions ==

= Does this disable Firefox summaries? =
No. Browser settings remain under the reader's control. The confirmation button records a reader's statement; it does not verify or change settings.

= Does it change recipes or search metadata? =
No. Original content and structured data are retained. Visitors can always continue.

= What if JavaScript, storage, or modal dialogs are blocked? =
Without JavaScript or a working modal-dialog API, the original page remains available. If storage fails, visitors can still dismiss the current notice.

= What detection limits keep the page responsive? =
The plugin inspects up to 64 JSON-LD blocks, skips blocks over 262,144 characters, reads up to 1,048,576 characters total, and scans at most 10,000 metadata nodes per block. Use the manual checkbox for larger or dynamically injected recipes.

= Does the plugin support translations? =
Yes. Interface strings use the recipe-ai-summary-notice text domain. Publisher-saved copy is displayed as entered.

= What happens when updating from 1.0.0? =
The exact original default message is replaced with neutral wording. Custom messages and enabled settings are preserved. Title and message lengths are limited to 180 and 4,000 characters respectively.

= Does uninstalling remove data? =
Settings and manual recipe flags are retained. Recipes are never changed by the plugin. Saved browser preferences can be removed by clearing this site's browser storage.

== Changelog ==

= 1.3.0 =
* Added WordPress component-based settings with a live notice preview and native form fallback.
* Refined reader notice typography, spacing, surfaces, and focus styling.

= 1.2.0 =
* Renamed to Recipe AI Summary Notice for Firefox with matching directory slug and translation domain.
* Existing settings, manual recipe flags, and browser preferences retain their original storage keys.

= 1.1.0 =
* Added sourced context, About, privacy, trademark, scope, and license disclosures.
* Replaced unsupported default claims with neutral wording and preserved custom messages.
* Added translation support and typed script configuration.
* Bounded metadata scanning and hardened browser API, storage, duplicate loading, text overflow, and theme contrast handling.

= 1.0.0 =
* Initial release with mobile Firefox targeting, recipe detection, accessible notice, clipboard fallback, and optional seven-day confirmation.
