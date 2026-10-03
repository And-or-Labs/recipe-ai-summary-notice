const {chromium,expect}=require('@playwright/test');
(async()=>{
 const browser=await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const page=await browser.newPage();
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://localhost:8881/studio-auto-login?redirect_to=%2Fwp-admin%2Foptions-general.php%3Fpage%3Drecipe-warning');
 await page.waitForURL(/options-general/);
 for(const width of [1280,390]){
  await page.setViewportSize({width,height:900});
  for(const tab of ['context','about']){
   await page.goto('http://localhost:8881/wp-admin/options-general.php?page=recipe-warning&tab='+tab);
   await expect(page.locator('nav[aria-label="Recipe AI Summary Notice for Firefox settings"] a[aria-current="page"]')).toHaveCount(1);
   if(tab==='context'){
    await expect(page.getByRole('heading',{name:'Why this plugin exists'})).toBeVisible();
    await expect(page.locator('.wrap')).toContainText('October 1, 2026');
    await expect(page.getByRole('link',{name:'Mozilla’s Android summary settings'})).toHaveAttribute('href','https://support.mozilla.org/en-US/kb/summarize-pages-android');
   }else{
    await expect(page.getByRole('heading',{name:'Privacy and storage',exact:true})).toBeVisible();
    await expect(page.locator('.wrap')).toContainText('not an endorsement');
    await expect(page.locator('.wrap')).toContainText('without warranty');
   }
   const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1);
   expect(overflow).toBe(false);
   await page.screenshot({path:`artifacts/${tab}-${width}.png`,fullPage:true});
  }
 }
 expect(errors).toEqual([]);
 await browser.close();
 console.log('PASS: Context/About sourced content, active navigation, desktop/mobile layout, no script errors');
})().catch(e=>{console.error(e);process.exit(1)});
