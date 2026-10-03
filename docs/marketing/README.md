# Recipe AI Summary Notice for Firefox: sharing kit

## Product summary

Recipe AI Summary Notice for Firefox is a WordPress plugin by And/or Labs Inc. It adds a dismissible notice to recognized recipe pages for readers whose browser identifies itself as Firefox on Android or iOS. Readers can continue to the original recipe, copy its link, or confirm that they have disabled page summaries. That confirmation suppresses the notice for seven days on the site in that browser.

**Original idea: Don Marti.** His [discussion about recipe sites and Firefox summaries](https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/) inspired the project. And/or Labs built the implementation. The credit does not imply that Don developed, reviewed, or endorsed the release.

## Short share copy

> Keep the original recipe. We built a WordPress notice for recipe readers using Firefox on Android or iOS. Readers can continue or copy the original link. Publishers choose the wording. The original recipe and its structured data stay intact.
>
> Built by And/or Labs Inc., from an idea by Don Marti.

[Get the plugin](https://github.com/And-or-Labs/recipe-ai-summary-notice/releases/latest) · [Source and installation](https://github.com/And-or-Labs/recipe-ai-summary-notice)

## Share copy with news context

> Mozilla described recipe-specific prompts and structured-data handling for Firefox summaries on October 1, 2026, reporting improvements in completeness and accuracy.
>
> Recipe AI Summary Notice for Firefox gives WordPress publishers a way to remind readers about the original recipe. It adds a dismissible notice with options to continue, copy the link, or confirm that page summaries are disabled. It leaves the recipe and its metadata intact.
>
> Original idea: Don Marti. Implementation: And/or Labs Inc.

Include these links with the copy:

- [Mozilla’s engineering article](https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/)
- [Don Marti’s original discussion](https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/)
- [Download the latest plugin release](https://github.com/And-or-Labs/recipe-ai-summary-notice/releases/latest)

## Screenshots and captions

| Asset | Caption | Alt text |
| --- | --- | --- |
| [Mobile notice](../../artifacts/mobile-warning.png) | Keep the original recipe. Readers can continue or copy the link. | Mobile recipe page showing the dismissible Firefox AI summary notice and reader controls. |
| [Publisher settings](../../artifacts/settings.png) | Your site, your notice wording. | WordPress settings for editing the recipe notice title and message. |

These are actual interface captures. Use them without adding claims that the plugin disables summaries, verifies settings, or tests recipes.

## Installation CTA

Download the plugin ZIP from the [latest release](https://github.com/And-or-Labs/recipe-ai-summary-notice/releases/latest). In WordPress, choose **Plugins > Add New Plugin > Upload Plugin**, upload the ZIP, and activate it. Review the notice under **Settings > Recipe AI Summary Notice for Firefox**.

## Copy boundaries

- Summary availability varies by Firefox version, device, and rollout. [Android guidance](https://support.mozilla.org/en-US/kb/summarize-pages-android) and [iOS guidance](https://support.mozilla.org/en-US/kb/summarize-pages-ios) describe the browser controls.
- The plugin displays a notice. It cannot prevent scraping or summarization, change browser settings, or verify that summaries are disabled.
- It adds no analytics, tracking cookies, external services, remote assets, or AI calls. An explicit disabled-summary confirmation stores an expiry timestamp locally for seven days.
- The project is independent of Mozilla and WordPress. Attribution does not imply affiliation or endorsement.
- Do not promise legal protection, food safety, universal compatibility, or that every AI summary is wrong. See the [full disclosures](../CONTEXT-AND-DISCLOSURES.md) and [verification record](../../TESTING.md).
