=== Recipe Warning ===
Tags: recipes, firefox, accessibility
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A mobile Firefox warning that helps readers choose the original recipe instead of an untested AI summary.

== Description ==

Recipe Warning displays an accessible notice on recipe pages for Firefox on Android and iOS. Desktop Firefox and other browsers retain the normal page.

Readers can copy the page link for another browser, confirm that summaries are disabled, or continue to the original recipe. Confirmations are remembered in this site's local browser storage for seven days. Continuing or pressing Escape dismisses only the current notice.

The plugin recognizes Recipe JSON-LD, Recipe microdata, WP Recipe Maker, supported recipe blocks and shortcodes. An editor checkbox marks other recipe formats manually. Warning copy is editable under Settings > Recipe Warning.

Browser detection happens locally, so shared page caches do not store different versions for different browsers. No external services, tracking, AI APIs, or paid dependencies are used.

This is a reader notice, not an anti-scraping or AI-blocking system. It does not remove recipe markup, prevent summarization, or verify whether a browser setting has been disabled. Browser identification relies on the user agent.

Idea and original warning wording: Don Marti.

== Installation ==

1. Upload recipe-warning.zip under Plugins > Add New Plugin > Upload Plugin.
2. Activate Recipe Warning.
3. Review the wording under Settings > Recipe Warning.
4. For a recipe without recognized markup, check "Treat this as a recipe page" in its editor.

== Frequently Asked Questions ==

= Does this disable Firefox summaries? =
No. It informs readers and offers a choice. Firefox settings remain under the reader's control.

= Does it change recipes or search metadata? =
No. Original content and structured data are retained. Visitors can always continue.

= What if JavaScript or storage is blocked? =
Without JavaScript the original page remains available. If browser storage is unavailable, visitors can still dismiss the notice for the current page.

= Does uninstalling remove data? =
Plugin settings and manual recipe flags are retained. Recipes are never changed by the plugin.

== Changelog ==

= 1.0.0 =
* Initial release with mobile Firefox targeting, recipe detection, accessible notice, clipboard fallback, and optional seven-day confirmation.
