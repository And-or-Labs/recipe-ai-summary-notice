const { test, expect } = require('./browser-fixtures');
const fs = require('node:fs');
const path = require('node:path');
const fixtures = JSON.parse(fs.readFileSync(path.join(__dirname, 'fixtures.json'), 'utf8'));
const pluginScript = fs.readFileSync(path.join(__dirname, '../plugin/recipe-ai-summary-notice/assets/recipe-warning.js'), 'utf8');
const warning = page => page.locator('#recipe-warning');
const url = (name = 'recipe') => `/?p=${fixtures[name]}`;
const proceed = page => page.getByRole('button', { name: 'Continue to the original recipe' });
async function injectHead(page, markup) {
  await page.route('**/*', async route => {
    if (route.request().resourceType() !== 'document') return route.continue();
    const response = await route.fetch();
    await route.fulfill({ response, body: (await response.text()).replace('</head>', `${markup}</head>`) });
  });
}
function jsonld(text) { return `<script type="application/ld+json">${text}</script>`; }
async function measureInitialization(page) {
  // Measure plugin work against the loaded stress DOM, excluding Studio/network
  // latency and browser startup. Reinitialize only this page's isolated fixture.
  return page.evaluate(source => {
    document.getElementById('recipe-warning')?.remove();
    delete window.__recipeWarningLoaded;
    const start = performance.now();
    new Function(source)();
    return performance.now() - start;
  }, pluginScript);
}
function collectErrors(page) {
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  return errors;
}

test('large and malformed unrelated metadata does not hide a later recipe or freeze the page', async ({ page }) => {
  const errors = collectErrors(page);
  const large = JSON.stringify({ '@type': 'Article', description: 'x'.repeat(2 * 1024 * 1024) });
  await injectHead(page, jsonld('{broken') + jsonld(large) + jsonld('{"@type":"https://schema.org/Recipe"}'));
  await page.goto(url('plain'));
  expect(await measureInitialization(page)).toBeLessThan(1000);
  await expect(warning(page)).toBeVisible();
  await proceed(page).click();
  expect(errors).toEqual([]);
});

test('20,000 nested metadata levels fail safely and allow a later recipe', async ({ page }) => {
  const errors = collectErrors(page);
  const deep = '{"child":'.repeat(20000) + 'null' + '}'.repeat(20000);
  await injectHead(page, jsonld(deep) + jsonld('{"@graph":[{"@type":["Thing","Recipe"]}]}'));
  await page.goto(url('plain'));
  expect(await measureInitialization(page)).toBeLessThan(1000);
  await expect(warning(page)).toBeVisible();
  await proceed(page).click();
  expect(errors).toEqual([]);
});

test('many unrelated metadata nodes keep controls responsive', async ({ page }) => {
  const data = { '@graph': Array.from({ length: 25000 }, (_, i) => ({ '@type': 'Thing', name: `Item ${i}` })) };
  await injectHead(page, jsonld(JSON.stringify(data)) + jsonld('{"@type":"Recipe"}'));
  await page.goto(url('plain'));
  expect(await measureInitialization(page)).toBeLessThan(1000);
  await expect(warning(page)).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(warning(page)).toHaveCount(0);
});

for (const value of ['NaN', 'Infinity', '{broken', '-1', '999999999999999']) {
  test(`corrupted or unreasonable remembered expiry (${value}) does not bypass`, async ({ page }) => {
    await page.addInitScript(value => {
      Storage.prototype.getItem = () => value;
    }, value);
    await page.goto(url());
    await expect(warning(page)).toBeVisible();
  });
}

for (const mode of ['missing', 'throws']) {
  test(`dialog API ${mode} leaves the original article readable without errors`, async ({ page }) => {
    const errors = collectErrors(page);
    await page.addInitScript(mode => {
      Object.defineProperty(HTMLDialogElement.prototype, 'showModal', {
        configurable: true,
        value: mode === 'missing' ? undefined : function () { throw new DOMException('Dialog unavailable', 'InvalidStateError'); },
      });
    }, mode);
    await page.goto(url());
    await expect(warning(page)).toHaveCount(0);
    await expect(page.getByText('Combine and simmer for 5 minutes.', { exact: true })).toBeVisible();
    expect(errors).toEqual([]);
  });
}

test('duplicate script delivery creates one modal with working dismissal', async ({ page }) => {
  const errors = collectErrors(page);
  await page.goto(url());
  await expect(warning(page)).toBeVisible();
  await page.addScriptTag({ content: pluginScript });
  await page.addScriptTag({ content: pluginScript });
  await expect(warning(page)).toHaveCount(1);
  await proceed(page).click();
  await expect(warning(page)).toHaveCount(0);
  expect(errors).toEqual([]);
});

for (const size of [{ width: 280, height: 360 }, { width: 640, height: 280 }]) {
  test(`small ${size.width}x${size.height} viewport at 200% text remains usable`, async ({ page }) => {
    await page.setViewportSize(size);
    await injectHead(page, '<style>html { font-size: 200% !important; }</style>');
    await page.goto(url());
    await expect(warning(page)).toBeVisible();
    const bounds = await warning(page).evaluate(el => ({ left: el.getBoundingClientRect().left, right: el.getBoundingClientRect().right,
      scroll: el.scrollWidth, client: el.clientWidth, width: innerWidth }));
    expect(bounds.left).toBeGreaterThanOrEqual(0);
    expect(bounds.right).toBeLessThanOrEqual(bounds.width);
    expect(bounds.scroll).toBeLessThanOrEqual(bounds.client + 1);
    await proceed(page).click();
    await expect(warning(page)).toHaveCount(0);
  });
}

test('reduced motion preference disables control transition', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto(url());
  await expect(warning(page)).toBeVisible();
  const durations = await warning(page).locator('button').evaluateAll(buttons => buttons.map(button => getComputedStyle(button).transitionDuration));
  for (const duration of durations) expect(duration.split(',').every(value => parseFloat(value) === 0)).toBe(true);
});

test('clipboard fallback participates in forward and reverse keyboard focus order', async ({ page }) => {
  await page.addInitScript(() => Object.defineProperty(navigator, 'clipboard', { configurable: true, value: undefined }));
  await page.goto(url());
  const copy = page.getByRole('button', { name: 'Copy link for another browser' });
  await copy.click();
  const field = page.getByRole('textbox', { name: 'Recipe link to copy' });
  await expect(field).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(page.getByRole('button', { name: 'I’ve disabled summaries' })).toBeFocused();
  await page.keyboard.press('Shift+Tab');
  await expect(field).toBeFocused();
  await page.keyboard.press('Shift+Tab');
  await expect(copy).toBeFocused();
  await page.keyboard.press('Shift+Tab');
  await expect(page.getByRole('button', { name: 'Close notice' })).toBeFocused();
  await page.keyboard.press('Shift+Tab');
  await expect(proceed(page)).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(warning(page)).toHaveCount(0);
});

test('remembered bypass survives navigation back and forward', async ({ page }) => {
  await page.goto(url());
  await page.getByRole('button', { name: 'I’ve disabled summaries' }).click();
  await page.goto(url('plain'));
  await page.goBack();
  await expect(warning(page)).toHaveCount(0);
  await page.goForward();
  await expect(warning(page)).toHaveCount(0);
  await page.goto(url('manual'));
  await expect(warning(page)).toHaveCount(0);
});

test.describe('JavaScript disabled', () => {
  test.use({ javaScriptEnabled: false });
  test('recipe remains readable with no blocking warning', async ({ page }) => {
    await page.goto(url());
    await expect(warning(page)).toHaveCount(0);
    await expect(page.getByText('Combine and simmer for 5 minutes.', { exact: true })).toBeVisible();
  });
});

test.describe('Firefox tablet user agent', () => {
  test.use({ userAgent: 'Mozilla/5.0 (Android 14; Tablet; rv:143.0) Gecko/143.0 Firefox/143.0' });
  test('Android tablets receive the same dismissible warning', async ({ page }) => {
    await page.goto(url());
    await expect(warning(page)).toBeVisible();
    await proceed(page).click();
  });
});

test('archives, feeds and embedded posts do not load warning assets', async ({ request }) => {
  for (const endpoint of ['/', '/?feed=rss2', `/?p=${fixtures.recipe}&embed=true`]) {
    const response = await request.get(endpoint);
    expect(response.ok()).toBe(true);
    expect(await response.text()).not.toMatch(/recipe-ai-summary-notice\/assets\/recipe-warning\.(?:js|css)/);
  }
});

test('root array recipe metadata is recognized', async ({ page }) => {
  await injectHead(page, jsonld('[{"@type":"Article"},{"@type":"Recipe"}]'));
  await page.goto(url('plain'));
  await expect(warning(page)).toBeVisible();
});

for (const [name, paper, ink] of [['dark', '#161616', '#fafafa'], ['low contrast', '#ffffff', '#eeeeee']]) {
  test(`${name} theme colors leave readable warning text and primary controls`, async ({ page }) => {
    await injectHead(page, `<style>:root { --wp--preset--color--base:${paper}; --wp--preset--color--contrast:${ink}; }</style>`);
    await page.goto(url());
    await expect(warning(page)).toBeVisible();
    const ratios = await warning(page).evaluate(el => {
      function luminance(color) {
        const values = color.match(/[\d.]+/g).slice(0, 3).map(Number).map(n => n / 255).map(n => n <= 0.04045 ? n / 12.92 : ((n + 0.055) / 1.055) ** 2.4);
        return values[0] * 0.2126 + values[1] * 0.7152 + values[2] * 0.0722;
      }
      return [el, el.querySelector('.rw-primary')].map(node => {
        const style = getComputedStyle(node);
        const values = [luminance(style.color), luminance(style.backgroundColor)].sort((a, b) => a - b);
        return (values[1] + 0.05) / (values[0] + 0.05);
      });
    });
    for (const ratio of ratios) expect(ratio).toBeGreaterThanOrEqual(4.5);
  });
}

test('late duplicate delivery does not reopen a dismissed warning', async ({ page }) => {
  await page.goto(url());
  await proceed(page).click();
  await page.addScriptTag({ content: pluginScript });
  await expect(warning(page)).toHaveCount(0);
});
