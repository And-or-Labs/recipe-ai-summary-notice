# WebKit test environment

The browser suites use a shared fixture in `tests/browser-fixtures.js`. Only for Playwright WebKit, it seeds the existing WordPress `sessionStorage` key `wpEmojiSettingsSupports` with a current timestamp and supported emoji/flag results. WordPress uses this cache in its normal emoji initialization path. This prevents an unrelated emoji worker from running during plugin tests. It does not change plugin storage, product code, site options, or browser error reporting.

## Evidence

The initial WebKit run passed 38 of 42 cases. Two metadata stress cases reported `InvalidStateError: The object is in an invalid state.` even though their recipe warning displayed and dismissed successfully. The other two failures concerned keyboard focus and were investigated separately.

The trace at `artifacts/test-results/edge-large-and-malformed-u-f3402-r-recipe-or-freeze-the-page-webkit/trace.zip`, `1-trace.trace`, recorded the error at a `blob:http://localhost:8881/` URL, line 0, column 920, with an empty error stack. Its worker source, trace resource `507a7ee14317efd5df343aa16db3275b9cd0d061.js`, contains WordPress's `wpTestEmojiSupports` code. The reported position belongs to its canvas image-data comparison. The worker creates an `OffscreenCanvas` and calls `getImageData()` while checking emoji support. Recipe Warning neither creates workers nor uses canvas.

Two standalone WebKit reproductions with the same page and injected recipe metadata produced no error, including the 2 MiB metadata payload. The failure is intermittent.

## Scope

All `pageerror` assertions remain unfiltered. This fixture avoids the identified WordPress worker by supplying its normal capability cache; it does not suppress generic errors or allow plugin failures. Storage-denial tests may deliberately prevent WordPress from reading its cache and retain strict error assertions. Chromium and Firefox do not receive this setup. Emoji rendering itself is outside the plugin's test scope. WebKit tests use the Playwright engine with a mobile Firefox user agent and do not replace physical-device Firefox iOS testing.

## Stress timing

Stress cases measure plugin initialization with `performance.now()` against the already-loaded metadata fixture and require completion within 1,000 ms. They remove the current dialog and reset its single-page initialization guard before executing the actual plugin source again. This measures parsing, bounded scanning, and modal creation while excluding WordPress response time and browser startup. Separate assertions still require the real page warning to display, dismiss correctly, and produce no page errors. The ordinary navigation/test timeout remains in force.
