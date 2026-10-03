const {chromium,expect}=require('@playwright/test');
const AxeBuilder=require('@axe-core/playwright').default;
(async()=>{
 const browser=await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const context=await browser.newContext(); const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const origin=process.env.RW_SITE_URL||'http://localhost:8881';
 await page.goto(origin+'/studio-auto-login?redirect_to=%2Fwp-admin%2Foptions-general.php%3Fpage%3Drecipe-warning');
 await page.waitForURL(/options-general/);
 await expect(page.locator('#rw-admin-editor[data-ready="true"]')).toBeVisible();
 await expect(page.locator('#rw-admin-fallback')).toHaveCount(0);
 const title=page.locator('#recipe-warning-title');const old=await title.inputValue();
 await title.fill('Live preview check');await expect(page.locator('.rw-preview-card h3')).toHaveText('Live preview check');await title.fill(old);
 await title.blur();
 for(const width of [1280,390]){
  await page.setViewportSize({width,height:900});
  expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1)).toBe(true);
  const audit=await new AxeBuilder({page}).include('.rw-admin').analyze();
  expect(audit.violations.filter(v=>['serious','critical'].includes(v.impact))).toEqual([]);
  await page.screenshot({path:'artifacts/'+(process.env.RW_SITE_URL?'minimum-':'')+(width===1280?'settings.png':'settings-mobile.png'),fullPage:true});
 }
 const state=await context.storageState();
 const noJS=await browser.newContext({javaScriptEnabled:false,storageState:state});const fallback=await noJS.newPage();
 await fallback.goto(origin+'/wp-admin/options-general.php?page=recipe-warning');
 await expect(fallback.locator('#rw-admin-fallback')).toBeVisible();await expect(fallback.getByRole('button',{name:'Save Changes',exact:true})).toBeVisible();
 expect(errors).toEqual([]);await browser.close();console.log('PASS: native components, live preview, desktop/mobile layout, accessibility, no-JS form fallback');
})().catch(e=>{console.error(e);process.exit(1)});
