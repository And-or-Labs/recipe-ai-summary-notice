# Recipe AI Summary Notice for Firefox 1.4.0

A redesigned reader notice with a document header, separate confirmation card, primary Continue action and accessible close control. Publisher settings now include **Allow readers to dismiss the notice**, enabled by default.

- Dismissible: Continue, close and Escape dismiss the current visit without remembering a preference.
- Non-dismissible: Continue and close are hidden; Escape and backdrop clicks do not dismiss. Copy link and disabled-summary confirmation remain available. Confirmation closes the notice and stores the existing seven-day preference.
- Stock copy and the settings preview adapt to the selected mode. Custom publisher copy is preserved.

This is a notice preference, not anti-scraping or AI-summary enforcement. The plugin cannot verify a reader’s browser settings; missing JavaScript or dialog support leaves the original page available.

Download `recipe-ai-summary-notice-1.4.0.zip` and upload through WordPress Plugins > Add New Plugin. Update the existing plugin when WordPress prompts. Users of the old Recipe Warning directory should deactivate that copy first. Existing saved settings default to dismissible mode.

Built by And/or Labs Inc. Original idea credited to [Don Marti](https://www.linkedin.com/posts/dmarti_should-recipe-sites-start-blocking-firefox-share-7511824622989008896-pGED/). Updated screenshots, sourced context, marketing copy and verification records are in the repository.

Verification: 138 browser/scenario combinations passed across Chromium, Firefox and WebKit through the full and recovery runs; 69 PHP assertions passed on both tested WordPress versions. Settings integration checks cover saving both modes, accessible controls, mode-aware copy and the no-JavaScript form. WordPress Plugin Check reported no errors. See TESTING.md for the local-server restart and other test boundaries.
