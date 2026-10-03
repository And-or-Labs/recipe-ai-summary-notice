const {test,expect}=require('./browser-fixtures');
const AxeBuilder=require('@axe-core/playwright').default;
const fixtures=require('./fixtures.json');
async function required(page){
 await page.route('**/*',async route=>{
  if(route.request().resourceType()!=='document')return route.continue();
  const response=await route.fetch();
  const body=(await response.text()).replace(/"dismissible":true/g,'"dismissible":false');
  await route.fulfill({response,body});
 });
 await page.goto('/?p='+fixtures.recipe);
 await expect(page.locator('#recipe-warning')).toBeVisible();
}
test('non-dismissible blocks Escape, close, Continue and backdrop; confirmation persists',async({page})=>{
 await required(page);
 await expect(page.getByRole('button',{name:'Continue to the original recipe'})).toHaveCount(0);
 await expect(page.getByRole('button',{name:'Close notice'})).toHaveCount(0);
 await page.keyboard.press('Escape');await expect(page.locator('#recipe-warning')).toBeVisible();
 await page.mouse.click(2,2);await expect(page.locator('#recipe-warning')).toBeVisible();
 const copy=page.getByRole('button',{name:'Copy link for another browser'});
 const bypass=page.getByRole('button',{name:'I’ve disabled summaries'});
 await copy.focus();await page.keyboard.press('Tab');await expect(bypass).toBeFocused();
 await page.keyboard.press('Tab');await expect(copy).toBeFocused();
 await bypass.click();await expect(page.locator('#recipe-warning')).toHaveCount(0);
 await page.reload();await expect(page.locator('#recipe-warning')).toHaveCount(0);
});
test('non-dismissible storage and clipboard denial still allow confirmation',async({page})=>{
 await page.addInitScript(()=>{
  Object.defineProperty(navigator,'clipboard',{value:undefined,configurable:true});
  Object.defineProperty(window,'localStorage',{get(){throw new Error('Storage denied')}});
 });
 await required(page);await page.getByRole('button',{name:'Copy link for another browser'}).click();
 await expect(page.getByRole('textbox',{name:'Recipe link to copy'})).toBeFocused();
 await page.getByRole('button',{name:'I’ve disabled summaries'}).click();
 await expect(page.locator('#recipe-warning')).toHaveCount(0);
 await page.reload();await expect(page.locator('#recipe-warning')).toBeVisible();
});
test('dismissible close is keyboard accessible and does not remember a preference',async({page})=>{
 await page.goto('/?p='+fixtures.recipe);
 const close=page.getByRole('button',{name:'Close notice'});await expect(close).toBeVisible();
 await close.focus();await page.keyboard.press('Enter');await expect(page.locator('#recipe-warning')).toHaveCount(0);
 expect(await page.evaluate(()=>localStorage.getItem('recipe-warning-bypass-v1'))).toBeNull();
 await page.reload();await expect(page.locator('#recipe-warning')).toBeVisible();
});
test('non-dismissible narrow layout and accessibility',async({page})=>{
 await page.setViewportSize({width:320,height:568});await required(page);
 expect(await page.locator('#recipe-warning').evaluate(el=>el.scrollWidth<=el.clientWidth+1)).toBe(true);
 const audit=await new AxeBuilder({page}).include('#recipe-warning').analyze();
 expect(audit.violations.filter(v=>['serious','critical'].includes(v.impact))).toEqual([]);
});
