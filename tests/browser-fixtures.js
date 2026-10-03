const { test: base, expect } = require('@playwright/test');

const test = base.extend({
  page: async ({ page, browserName }, use) => {
    if (browserName === 'webkit') {
      // WordPress core's emoji OffscreenCanvas worker intermittently throws in
      // Playwright WebKit. Reuse its normal capability cache, not an error filter.
      // Recipe Warning does not read this key or sessionStorage.
      await page.addInitScript(() => {
        try {
          sessionStorage.setItem('wpEmojiSettingsSupports', JSON.stringify({
            timestamp: Date.now(),
            supportTests: { flag: true, emoji: true },
          }));
        } catch (_) { /* A storage-denial test may intentionally prevent this. */ }
      });
    }
    await use(page);
  },
});

module.exports = { test, expect };
