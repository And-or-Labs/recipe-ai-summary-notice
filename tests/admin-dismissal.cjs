const {chromium,expect}=require('@playwright/test');
const fixtures=require(process.env.RW_SITE_URL?'../artifacts/minimum-fixtures.json':'./fixtures.json');
(async()=>{
 const browser=await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const context=await browser.newContext();const page=await context.newPage();
 const origin=process.env.RW_SITE_URL||'http://localhost:8881';
 const mobile=await browser.newContext({userAgent:'Mozilla/5.0 (Android 14; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0',viewport:{width:390,height:844}});const reader=await mobile.newPage();
 const errors=[];page.on('pageerror',e=>errors.push(e.message));reader.on('pageerror',e=>errors.push(e.message));
 let original;
 const mode=()=>page.getByLabel('Allow readers to dismiss the notice');
 async function save(){await page.getByRole('button',{name:'Save Changes',exact:true}).click();await page.waitForLoadState('load');await expect(page.locator('#rw-admin-editor[data-ready="true"]')).toBeVisible()}
 try{
  await page.goto(origin+'/studio-auto-login?redirect_to=%2Fwp-admin%2Foptions-general.php%3Fpage%3Drecipe-warning');await page.waitForURL(/options-general/);
  await expect(page.locator('#rw-admin-editor[data-ready="true"]')).toBeVisible();original=await mode().isChecked();
  await mode().uncheck();await save();await expect(mode()).not.toBeChecked();
  await reader.goto(origin+'/?p='+fixtures.recipe);await expect(reader.locator('#recipe-warning')).toBeVisible();
  await expect(reader.getByRole('button',{name:'Continue to the original recipe'})).toHaveCount(0);
  await expect(reader.getByRole('button',{name:'Close notice'})).toHaveCount(0);
  await expect(reader.locator('#rw-message')).not.toContainText('You can continue');
  await reader.keyboard.press('Escape');await expect(reader.locator('#recipe-warning')).toBeVisible();
  await reader.screenshot({path:process.env.RW_SITE_URL?'artifacts/minimum-mobile-required.png':'artifacts/mobile-required.png'});
  await mode().check();await save();await expect(mode()).toBeChecked();
  await reader.reload();await expect(reader.getByRole('button',{name:'Continue to the original recipe'})).toBeVisible();
  await reader.getByRole('button',{name:'Close notice'}).click();await expect(reader.locator('#recipe-warning')).toHaveCount(0);
  const noJS=await browser.newContext({javaScriptEnabled:false,storageState:await context.storageState()});
  const fallback=await noJS.newPage();await fallback.goto(origin+'/wp-admin/options-general.php?page=recipe-warning');
  await fallback.getByLabel('Allow readers to dismiss the notice').uncheck();
  await fallback.getByRole('button',{name:'Save Changes',exact:true}).click();await fallback.waitForLoadState('load');
  await reader.reload();await expect(reader.getByRole('button',{name:'Close notice'})).toHaveCount(0);await expect(reader.locator('#recipe-warning')).toBeVisible();
  await page.reload();await expect(page.locator('#rw-admin-editor[data-ready="true"]')).toBeVisible();
  expect(errors).toEqual([]);console.log('PASS: dismissible setting saves both ways, mode-aware default copy, required Escape blocked, close action restored and no-JS setting saves');
 }finally{if(original!==undefined){await mode().setChecked(original);await save()}await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
