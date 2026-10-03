const { test, expect } = require('./browser-fixtures');
const AxeBuilder = require('@axe-core/playwright').default;
const fs = require('node:fs');
const path = require('node:path');

function fixtureURL(name) {
  const fixtures = JSON.parse(fs.readFileSync(path.join(__dirname, 'fixtures.json'), 'utf8'));
  if (!fixtures[name]) throw new Error(`Missing WordPress fixture: ${name}`);
  return `/?p=${fixtures[name]}`;
}
const dialog = page => page.locator('#recipe-warning');
const copy = page => page.getByRole('button', { name: 'Copy link for another browser' });
const bypass = page => page.getByRole('button', { name: 'I’ve disabled summaries' });
const proceed = page => page.getByRole('button', { name: 'Continue to the original recipe' });
async function visit(page, name = 'recipe') {
  const response = await page.goto(fixtureURL(name));
  expect(response.status()).toBe(200);
  await page.waitForLoadState('load');
}

const browsers = [
  ['Android Firefox', 'Mozilla/5.0 (Android 14; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0', true],
  ['iOS Firefox', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/143.0 Mobile/15E148 Safari/605.1.15', true],
  ['desktop Firefox', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0', false],
  ['Android Chrome', 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', false],
  ['iOS Safari', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1', false],
];
for (const [name, userAgent, warns] of browsers) {
  test.describe(name, () => {
    test.use({ userAgent });
    test(`recipe warning ${warns ? 'appears' : 'stays absent'}`, async ({ page }, testInfo) => {
      if (name === 'desktop Firefox') await page.setViewportSize({ width: 1440, height: 1000 });
      await visit(page);
      if (warns) await expect(dialog(page)).toBeVisible();
      else await expect(dialog(page)).toHaveCount(0);
      if (name === 'Android Firefox' && testInfo.project.name === 'chromium') {
        await page.screenshot({ path: 'artifacts/mobile-warning.png', fullPage: false });
      }
      if (name === 'desktop Firefox' && testInfo.project.name === 'chromium') {
        await page.screenshot({ path: 'artifacts/desktop-recipe.png', fullPage: true });
      }
    });
  });
}

for (const [name, warns] of [['plain', false], ['password', false], ['manual', true], ['wprm', true]]) {
  test(`${name} page ${warns ? 'shows' : 'does not show'} the warning`, async ({ page }) => {
    await visit(page, name);
    if (warns) await expect(dialog(page)).toBeVisible();
    else await expect(dialog(page)).toHaveCount(0);
    if (name === 'wprm') await expect(page.locator('.wprm-recipe-container').first()).toBeVisible();
    if (name === 'password') await expect(page.locator('input[name="post_password"]')).toBeVisible();
  });
}

test('confirmation lasts seven days, survives reload, and expires', async ({ page }) => {
  await visit(page);
  await bypass(page).click();
  await expect(dialog(page)).toHaveCount(0);
  const remaining = await page.evaluate(() => Number(localStorage.getItem(rwConfig.storageKey)) - Date.now());
  expect(remaining).toBeGreaterThan(7 * 86400000 - 10000);
  expect(remaining).toBeLessThanOrEqual(7 * 86400000);
  await page.reload();
  await expect(dialog(page)).toHaveCount(0);
  await page.evaluate(() => localStorage.setItem(rwConfig.storageKey, String(Date.now() - 1)));
  await page.reload();
  await expect(dialog(page)).toBeVisible();
});

for (const method of ['continue', 'Escape']) {
  test(`${method} dismisses only the current page and restores readable focus`, async ({ page }) => {
    await visit(page);
    if (method === 'continue') await proceed(page).click();
    else await page.keyboard.press('Escape');
    await expect(dialog(page)).toHaveCount(0);
    expect(await page.evaluate(() => localStorage.getItem(rwConfig.storageKey))).toBeNull();
    expect(await page.evaluate(() => document.activeElement.matches('main h1, .entry-title, main'))).toBe(true);
    await page.reload();
    await expect(dialog(page)).toBeVisible();
  });
}

test('copy link reports successful clipboard write', async ({ page }) => {
  await page.addInitScript(() => Object.defineProperty(navigator, 'clipboard', {
    configurable: true, value: { writeText: async value => { window.copiedRecipeURL = value; } },
  }));
  await visit(page);
  await copy(page).click();
  await expect(page.getByRole('status')).toHaveText('Link copied. Paste it into another browser.');
  expect(await page.evaluate(() => window.copiedRecipeURL)).toBe(page.url());
});

test('denied clipboard exposes a focused, selected copy fallback', async ({ page }) => {
  await page.addInitScript(() => Object.defineProperty(navigator, 'clipboard', {
    configurable: true, value: { writeText: async () => { throw new Error('Permission denied'); } },
  }));
  await visit(page);
  await copy(page).click();
  const field = page.getByRole('textbox', { name: 'Recipe link to copy' });
  await expect(field).toBeFocused();
  await expect(field).toHaveValue(page.url());
  expect(await field.evaluate(input => input.value.substring(input.selectionStart, input.selectionEnd))).toBe(page.url());
  await expect(page.getByRole('status')).toContainText('Copy the selected link');
});

test('unavailable storage still allows reading and never crashes', async ({ page }) => {
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.addInitScript(() => {
    Storage.prototype.getItem = () => { throw new Error('Storage unavailable'); };
    Storage.prototype.setItem = () => { throw new Error('Storage unavailable'); };
  });
  await visit(page);
  await bypass(page).click();
  await expect(dialog(page)).toHaveCount(0);
  await page.reload();
  await expect(dialog(page)).toBeVisible();
  expect(errors).toEqual([]);
});

test('malformed JSON-LD is ignored while valid recipe metadata still works', async ({ page }) => {
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.route('**/*', async route => {
    if (route.request().resourceType() !== 'document') return route.continue();
    const response = await route.fetch();
    const body = (await response.text()).replace('</head>', '<script type="application/ld+json">{invalid json</script></head>');
    await route.fulfill({ response, body });
  });
  await visit(page, 'plain');
  await expect(dialog(page)).toHaveCount(0);
  await visit(page, 'recipe');
  await expect(dialog(page)).toBeVisible();
  expect(errors).toEqual([]);
});

test('native modal traps keyboard focus and supports keyboard dismissal', async ({ page }) => {
  await visit(page);
  await expect(copy(page)).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(bypass(page)).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(proceed(page)).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(page.getByRole('button', { name: 'Close notice' })).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(copy(page)).toBeFocused();
  const outsideFocusBlocked = await page.evaluate(() => {
    const outside = document.querySelector('main a, header a');
    outside.focus();
    return document.activeElement !== outside;
  });
  expect(outsideFocusBlocked).toBe(true);
  await page.keyboard.press('Shift+Tab');
  await expect(page.getByRole('button', { name: 'Close notice' })).toBeFocused();
  await page.keyboard.press('Shift+Tab');
  await expect(proceed(page)).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(dialog(page)).toHaveCount(0);
});

test('320px layout has no horizontal overflow and usable controls', async ({ page }) => {
  await page.setViewportSize({ width: 320, height: 568 });
  await visit(page);
  await expect(dialog(page)).toBeVisible();
  const measurements = await dialog(page).evaluate(element => {
    const bounds = element.getBoundingClientRect();
    return { left: bounds.left, right: bounds.right, width: innerWidth, scroll: element.scrollWidth, client: element.clientWidth,
      heights: Array.from(element.querySelectorAll('button'), button => button.getBoundingClientRect().height) };
  });
  expect(measurements.left).toBeGreaterThanOrEqual(0);
  expect(measurements.right).toBeLessThanOrEqual(measurements.width);
  expect(measurements.scroll).toBeLessThanOrEqual(measurements.client + 1);
  for (const height of measurements.heights) expect(height).toBeGreaterThanOrEqual(44);
  await proceed(page).click();
  await expect(dialog(page)).toHaveCount(0);
});

test('warning has no serious or critical automated accessibility violations', async ({ page }) => {
  await visit(page);
  await expect(dialog(page)).toBeVisible();
  const results = await new AxeBuilder({ page }).include('#recipe-warning').analyze();
  expect(results.violations.filter(violation => ['serious', 'critical'].includes(violation.impact))).toEqual([]);
});
