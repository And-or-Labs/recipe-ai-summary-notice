const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  testMatch: 'warning.spec.js',
  fullyParallel: true,
  workers: 2,
  timeout: 30_000,
  expect: { timeout: 7000 },
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'artifacts/playwright-report' }]],
  outputDir: 'artifacts/test-results',
  use: {
    baseURL: process.env.RW_SITE_URL || 'http://localhost:8881',
    viewport: { width: 390, height: 844 },
    userAgent: 'Mozilla/5.0 (Android 14; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'chromium', use: { browserName: 'chromium', launchOptions: { executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' } } },
    { name: 'firefox', use: { browserName: 'firefox' } },
  ],
});
