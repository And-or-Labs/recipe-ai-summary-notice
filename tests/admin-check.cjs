const { chromium } = require('@playwright/test');
const { strict: assert } = require('node:assert');
(async () => {
 const browser = await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const context = await browser.newContext();
 const page=await context.newPage();
 const admin='http://localhost:8881/wp-admin/options-general.php?page=recipe-warning';
 const mobile=await browser.newContext({userAgent:'Mozilla/5.0 (Android 14; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0'});
 const reader=await mobile.newPage();
 let original;
 try {
  await page.goto('http://localhost:8881/studio-auto-login?redirect_to=%2Fwp-admin%2Foptions-general.php%3Fpage%3Drecipe-warning');
  await page.waitForURL(/options-general/);
  original={title:await page.locator('#recipe-warning-title').inputValue(),message:await page.locator('#recipe-warning-message').inputValue()};
  await page.getByLabel('Enable on recipe pages').uncheck();
  await page.getByRole('button',{name:'Save Changes',exact:true}).click();
  await page.waitForLoadState('load');
  await reader.goto('http://localhost:8881/?p=12');
  assert.equal(await reader.locator('#recipe-warning').count(),0,'Disabled plugin must not show dialog');
  assert.equal(await reader.locator('script#recipe-warning-js').count(),0,'Disabled plugin must not load frontend script');
  await page.getByLabel('Enable on recipe pages').check();
  await page.locator('#recipe-warning-title').fill('Reviewed recipe warning');
  await page.locator('#recipe-warning-message').fill('<script>window.rwInjected=true</script>Recipe text remains plain.');
  await page.getByRole('button',{name:'Save Changes',exact:true}).click();
  await page.waitForLoadState('load');
  await reader.reload();
  await reader.locator('#recipe-warning').waitFor();
  assert.equal(await reader.locator('#rw-title').innerText(),'Reviewed recipe warning','Edited title must render');
  assert.equal(await reader.evaluate(()=>window.rwInjected),undefined,'Saved message must not execute script');
  assert.equal(await reader.locator('#rw-message script').count(),0,'Message must be text');
  console.log('PASS: settings save, disable/no assets, editable copy, safe text rendering');
 } finally {
  if(original){
   await page.goto(admin);
   await page.getByLabel('Enable on recipe pages').check();
   await page.locator('#recipe-warning-title').fill(original.title);
   await page.locator('#recipe-warning-message').fill(original.message);
   await page.getByRole('button',{name:'Save Changes',exact:true}).click();
   await page.waitForLoadState('load');
   await page.screenshot({path:'artifacts/settings.png',fullPage:true});
  }
  await browser.close();
 }
})().catch(e=>{console.error(e);process.exit(1)});
