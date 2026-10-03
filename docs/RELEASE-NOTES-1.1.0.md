Recipe Warning gives mobile Firefox readers a dismissible notice about AI recipe summaries while keeping the original recipe accessible.

Developed by And/or Labs Inc. Original idea credited to [Don Marti](https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/). WordPress.org contributor: vj1987.

Mozilla’s [October 1 engineering article](https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/) describes recipe-specific prompts and structured-data routing to improve summaries. This plugin lets publishers explain the distinction between their recipe and a browser-generated summary. It does not disable summarization or verify browser settings.

## Install

Download `recipe-warning-1.1.0.zip` below. In WordPress, choose Plugins > Add New Plugin > Upload Plugin, activate, and review Settings > Recipe Warning. Use the installable ZIP rather than GitHub’s automatically generated source archives.

Requires WordPress 6.4 or newer and PHP 7.4 or newer. WordPress.org directory submission is pending authentication; this GitHub release can be installed directly.

## Included

- Android/iOS Firefox detection and recipe markup detection, with a manual editor override.
- Continue, copy-link fallback, and optional seven-day confirmation.
- Sourced Context and About/privacy tabs, explicit limitations, and GPL-2.0-or-later licensing.
- Bounded metadata scanning, safe editable text, storage failure handling, keyboard focus, and contrast fallback.

## Verification

126 browser/scenario combinations verified through full runs and focused reruns across Chromium, Firefox, and WebKit. 57 release assertions and 26 runtime assertions passed on both tested WordPress versions. All 200 local load-test requests passed, and WordPress Plugin Check reported no errors. See [TESTING.md](https://github.com/And-or-Labs/recipe-warning/blob/main/TESTING.md) for the test boundaries, including emulated mobile devices and the WebKit emoji-worker fixture.
