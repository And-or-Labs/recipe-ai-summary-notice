const {chromium,expect}=require('@playwright/test');
const fs=require('node:fs');
(async()=>{
 const fixtures=JSON.parse(fs.readFileSync('artifacts/minimum-fixtures.json','utf8'));
 const browser=await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const page=await browser.newPage({userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 FxiOS/143.0 Mobile/15E148 Safari/605.1.15',viewport:{width:390,height:844}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 for(const [name,warns] of [['recipe',true],['manual',true],['plain',false],['password',false]]){
  const response=await page.goto('http://localhost:8882/?p='+fixtures[name]);expect(response.status()).toBe(200);
  if(warns){await expect(page.locator('#recipe-warning')).toBeVisible();await page.getByRole('button',{name:'Continue to the original recipe'}).click();await expect(page.locator('#recipe-warning')).toHaveCount(0)}
  else await expect(page.locator('#recipe-warning')).toHaveCount(0);
 }
 expect(errors).toEqual([]);
 await browser.close();console.log('PASS: WordPress 6.4.12 / PHP 8.2 recipe/manual/plain/password behavior');
})().catch(e=>{console.error(e);process.exit(1)});
