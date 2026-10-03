import { performance } from 'node:perf_hooks';
import { readFile, writeFile } from 'node:fs/promises';
import assert from 'node:assert/strict';
const base = process.env.RW_SITE_URL || 'http://localhost:8881';
const fixtures = JSON.parse(await readFile(new URL('./fixtures.json', import.meta.url)));
const agents = ['Mozilla/5.0 (Android 14; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0'];
const times=[];
const failures=[];
let next=0;
const started=performance.now();
await Promise.all(Array.from({length:4},async()=>{
 while(next<200){
  const index=next++;
  const t=performance.now();
  try {
   const response=await fetch(`${base}/?p=${index%2?fixtures.wprm:fixtures.recipe}`,{headers:{'user-agent':agents[index%2]},signal:AbortSignal.timeout(15000)});
   assert.equal(response.status,200);
   const html=await response.text();
   assert(html.includes('recipe-warning.js?ver=1.1.0'),'Plugin script missing');
   assert(html.includes('window.rwConfig = '),'Typed config missing');
   assert(!/Fatal error:|Warning: Undefined/.test(html),'PHP error in response');
   times.push(performance.now()-t);
  }catch(error){failures.push({index,error:error.message});}
 }
}));
const configs=[];
for(const agent of agents){
 const html=await(await fetch(`${base}/?p=${fixtures.recipe}`,{headers:{'user-agent':agent}})).text();
 configs.push(html.match(/window\.rwConfig = (\{.*?\});/)?.[1]);
}
assert(configs[0] && configs[0]===configs[1],'User-agent-dependent plugin config risks shared caching');
times.sort((a,b)=>a-b);
const result={requests:200,concurrency:4,passed:times.length,failures,elapsedMs:Math.round(performance.now()-started),medianMs:Math.round(times[Math.floor(times.length*.5)]),p95Ms:Math.round(times[Math.min(times.length-1,Math.floor(times.length*.95))]),maxMs:Math.round(times.at(-1)),cacheConfigIdentical:true};
await writeFile(new URL('../artifacts/load-results.json',import.meta.url),JSON.stringify(result,null,2)+'\n');
console.log(JSON.stringify(result,null,2));
assert.equal(failures.length,0);
