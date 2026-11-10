<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Marketplace Finder — Find better deals</title>
<style>
:root{font:15px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color-scheme:dark;--bg:#090f1c;--side:#0d1627;--panel:#121e31;--panel2:#19263a;--line:#2b3b52;--text:#f5f7fd;--muted:#a5b2c5;--orange:#ffb54d;--mint:#88e5c1;--red:#ff96a2}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text)}button,input,select{font:inherit}button{cursor:pointer}a{color:inherit}button:disabled{opacity:.6;cursor:wait}button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,summary:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
.app{display:grid;grid-template-columns:230px minmax(0,1fr);min-height:100vh}.aside{background:var(--side);padding:26px 14px;border-right:1px solid var(--line);display:flex;flex-direction:column;gap:28px}.logo{font-size:20px;font-weight:850;letter-spacing:-.8px;padding:0 10px}.logo b{color:var(--orange)}.tagline{padding:0 10px;color:var(--muted);font-size:12px}.menu{display:grid;gap:6px}.menu button{width:100%;border:0;text-align:left;background:transparent;color:var(--muted);padding:12px 15px;border-radius:12px}.menu button[aria-current=page],.menu button:hover{background:#283044;color:var(--orange)}.asidefoot{margin-top:auto;color:var(--muted);font-size:12px;padding:10px}.asidefoot a{color:var(--orange)}
main{width:100%;max-width:1190px;margin:0 auto;padding:37px 40px 75px}.view[hidden]{display:none!important}.eyebrow{text-transform:uppercase;letter-spacing:1.5px;font-weight:800;font-size:12px;color:var(--orange)}h1{font-size:clamp(30px,4vw,43px);line-height:1.15;letter-spacing:-1.2px;margin:10px 0}h2{font-size:22px;margin:0}h3{font-size:17px;margin:0 0 7px}p{color:var(--muted);margin:8px 0 18px}.intro{margin-bottom:26px}.intro p{font-size:15px}.status{font-size:12px;color:var(--mint);background:#15332f;border:1px solid #285e51;border-radius:50px;padding:7px 12px;display:inline-block}.headrow{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}
.btn{border:0;border-radius:12px;background:var(--orange);color:#201508;padding:11px 18px;font-weight:800;white-space:nowrap}.btn.alt{background:#283951;color:var(--text);border:1px solid #40516b}.btn.subtle{background:transparent;color:var(--muted);border:1px solid var(--line)}.btn.sm{font-size:12px;padding:8px 12px}.btn:hover:not(:disabled){filter:brightness(1.07)}
.panel{background:var(--panel);border:1px solid var(--line);border-radius:19px;padding:22px}.searchbox{padding:19px;background:linear-gradient(120deg,#1b2c46,#172238);border:1px solid #384963;border-radius:19px}.searchrow{display:grid;grid-template-columns:minmax(0,2fr) minmax(130px,1fr) minmax(145px,1fr) auto;gap:11px;align-items:end}.filterrow{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;margin-top:13px}.filterrow label{min-width:0}.filter-summary{padding:10px 1px 0;color:var(--muted);font-size:12px}.filter-toggle{border:0;background:transparent;padding:9px 3px;color:var(--orange);font-weight:700;font-size:13px}.extra-filters[hidden]{display:none}.searchbox .chip{margin-bottom:2px}label{display:block;color:var(--muted);font-size:12px;font-weight:600}input,select{display:block;width:100%;min-width:0;border-radius:11px;border:1px solid #42526b;background:#101c2e;color:var(--text);padding:11px 12px;margin-top:6px}input::placeholder{color:#8190a9}.quicklinks{display:flex;gap:8px;flex-wrap:wrap;margin:15px 0 0}.chip{border:1px solid #40516a;border-radius:50px;padding:8px 13px;background:#1e2b42;color:#d0d9e9;font-size:12px}.chip[aria-pressed=true]{background:#3b3228;color:var(--orange);border-color:#b77d35}.muted{color:var(--muted);font-size:12px}.sectiontop{margin:27px 0 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}.stack{display:grid;gap:13px}.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.listing{background:var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}.listing-art{height:99px;display:grid;place-items:center;background:linear-gradient(130deg,#263b57,#1b263c);font-size:32px}.listing-info{padding:15px;display:flex;flex-direction:column;flex:1}.listing h3{font-size:16px;margin:7px 0}.listing-price{font-size:24px;font-weight:850;letter-spacing:-.5px;margin:8px 0}.listing-actions{display:flex;gap:8px;margin-top:auto;padding-top:12px}.pill{font-size:11px;color:var(--mint);font-weight:700}.empty{padding:30px 24px;border:1px dashed #44536b;border-radius:16px;text-align:center;background:var(--panel)}.empty strong{font-size:17px}.empty p{max-width:470px;margin:7px auto 16px}.hint{margin-top:14px;padding:13px 15px;border-radius:12px;background:#1e2a3f;border:1px solid #3b4c63;color:#c1cede;font-size:13px}
details.settings{margin-top:15px;border:1px solid var(--line);border-radius:14px;padding:15px;background:var(--side)}summary{cursor:pointer;color:#f4dfbd;font-weight:700}details.settings p{font-size:13px}.grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.formrow{display:grid;grid-template-columns:2fr 1fr auto;gap:10px;align-items:end}.storageitem{display:flex;align-items:center;justify-content:space-between;gap:14px;border-radius:12px;background:#17263b;border:1px solid var(--line);padding:13px;margin-top:10px}.storageitem a{color:var(--orange);overflow-wrap:anywhere}.storageitem small{display:block;color:var(--muted);font-size:12px}.error{color:var(--red)}.success{color:var(--mint)}.out{font-size:29px;font-weight:850}.checkboxrow{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:var(--muted);margin:17px 0}.checkboxrow input{width:18px;height:18px;margin:1px 0}.summarygrid{display:flex;gap:45px;flex-wrap:wrap}.notice{border-top:1px solid var(--line);padding-top:18px;margin-top:24px;color:var(--muted);font-size:12px}
@media(max-width:950px){.app{display:block}.aside{padding:12px 16px;gap:8px;border-right:0;border-bottom:1px solid var(--line)}.menu{display:flex;overflow:auto;gap:6px}.menu button{width:auto;white-space:nowrap}.asidefoot{display:none}main{padding:25px 19px}.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.searchrow,.filterrow,.grid2,.grid3,.formrow,.cards{grid-template-columns:1fr}.panel{padding:16px}.listing-art{height:80px}.headrow{align-items:flex-start}.storageitem{align-items:flex-start;flex-direction:column}.btn{width:100%}.listing-actions .btn{width:auto}.menu button{padding:10px 12px}}
</style>
</head>
<body>
<div class="app">
<aside class="aside">
<div><div class="logo">marketplace<b>finder.</b></div><div class="tagline">Find deals. Buy smarter.</div></div>
<nav class="menu" aria-label="Main menu">
<button type="button" data-view="discover" aria-current="page">⌕ &nbsp; Find deals</button>
<button type="button" data-view="saved">♡ &nbsp; Saved items</button>
<button type="button" data-view="watchlist">☆ &nbsp; Watchlist</button>
<button type="button" data-view="profit">₱ &nbsp; Profit calculator</button>
</nav>
<div class="asidefoot">Need the original tools?<br><a href="/flipradar/index.html">Open classic FlipRadar ↗</a></div>
</aside>
<main>
<section class="view" id="view-discover">
<div class="intro"><div class="eyebrow">Discover / Bohol</div><h1>Find your next great deal.</h1><p>Browse Bohol listings found in earlier scans, compare prices, and save promising deals.</p><span class="status">● Your workspace is ready</span></div>
<div class="searchbox">
<form id="browseForm">
<div class="searchrow">
<label>Search term (optional)<input id="search" name="q" placeholder="Leave blank to browse all models" autocomplete="off"></label>
<label>Maximum price (₱)<input id="budget" name="max_price" type="number" step="1" min="0" placeholder="Any price"></label>
<label>Sort by<select id="sort" name="sort"><option value="newest">Recently found</option><option value="price_low">Price: lowest first</option><option value="price_high">Price: highest first</option><option value="first_seen">First discovered</option></select></label>
<button class="btn" type="submit" id="historySearchButton">Apply filters</button>
</div>
<div class="quicklinks" role="group" aria-label="Browse categories">
<button class="chip" data-category="" aria-pressed="true" type="button">All</button>
<button class="chip" data-category="Electronics" aria-pressed="false" type="button">Electronics</button>
<button class="chip" data-category="Phones" aria-pressed="false" type="button">Phones</button>
<button class="chip" data-category="Laptops" aria-pressed="false" type="button">Laptops</button>
<button class="chip" data-category="Cameras" aria-pressed="false" type="button">Cameras</button>
<button class="chip" data-category="PC Parts" aria-pressed="false" type="button">PC Parts</button>
<button class="chip" data-category="Consoles" aria-pressed="false" type="button">Consoles</button>
</div>
<button id="extraFiltersToggle" type="button" class="filter-toggle" aria-expanded="false" aria-controls="extraFilters">More filters ▾</button>
<div id="extraFilters" class="extra-filters" hidden>
<div class="filterrow">
<label>Minimum price (₱)<input id="minPrice" type="number" min="0" step="1" placeholder="No minimum"></label>
<label>First discovered<select id="foundWithinDays"><option value="">Any time</option><option value="1">Within 24 hours</option><option value="7">Within 7 days</option><option value="30">Within 30 days</option></select></label>
<label>Search area<input disabled value="Bohol only (location verified by metadata)" aria-label="Search area"></label>
</div>
</div>
</form>
<div class="filter-summary" id="filterSummary" role="status">Default: All categories · any price · recently found · Bohol only.</div>
<button class="filter-toggle" id="resetFilters" type="button">Reset to defaults</button>
</div>
<p class="muted" style="margin:10px 4px 0">This searches results already collected on this PC. To look for new Facebook Marketplace listings, choose “Get fresh results” below.</p>
<div class="sectiontop"><h2>Bohol listings</h2><span class="muted">Results from earlier scans, not live Facebook ranking</span></div>
<p id="searchFeedback" role="status" class="muted" style="margin:0 0 12px">Ready to search previously collected listings.</p>
<div id="listingResults" class="cards" aria-live="polite"><div class="empty">Loading your discovered listings…</div></div>
<div class="hint">Want new Facebook Marketplace offers? <button class="btn sm alt" type="button" id="openFreshScan">Get fresh results</button> Live scans use Apify and may cost money; you will always be asked before one runs.</div>
<details class="settings" id="scanSettings">
<summary>Fresh Marketplace search — setup and cost approval</summary>
<p>Scanning uses a third-party Apify actor and may incur charges. It is disabled until you configure your private server token and explicitly enable it. One scan requires confirmation; the actor's $0.10 parameter is not a guaranteed billing limit.</p>
<form id="scanForm">
<div class="grid3">
<label>Search keyword (optional)<input name="keyword" placeholder="Blank = broad browse" maxlength="80"></label>
<label>Area radius<select name="radius_km"><option value="20">20 km</option><option value="40" selected>40 km</option><option value="60">60 km</option></select></label>
<label>Order from provider<select name="sort_by"><option value="creation_time_descend">Newest</option><option value="price_ascend">Lowest price</option><option value="price_descend">Highest price</option><option value="distance_ascend">Nearest</option></select></label>
<label>Minimum price (₱)<input name="min_price" type="number" min="0" step="1" placeholder="Any minimum"></label>
<label>Maximum price (₱)<input name="max_price" type="number" min="0" step="1" placeholder="Any maximum"></label>
<label>Category for saved results<select name="category"><option value="">All</option><option value="Electronics">Electronics</option><option value="Phones">Phones</option><option value="Laptops">Laptops</option><option value="Cameras">Cameras</option><option value="PC Parts">PC Parts</option><option value="Consoles">Consoles</option></select></label>
</div>
<p class="muted">Category selection filters our archived results after collection; Apify's filter-based actor input does not provide the same category selection as Facebook. A broad scan may return unrelated items or miss some models. We keep the Bohol-only evidence check.</p>
<label class="checkboxrow"><input id="paidScan" type="checkbox" required><span>I approve one potentially billable scan. Results, location accuracy and category coverage are not guaranteed.</span></label>
<button class="btn" id="scanButton" type="submit">Run one approved scan</button>
</form>
<p id="scanStatus" role="status" class="muted">Scanning is off by default. Set APIFY_TOKEN and MARKETPLACE_SCAN_ENABLED in the private Laravel environment file to enable it.</p>
</details>
</section>
<section class="view" id="view-saved" hidden>
<div class="intro"><div class="eyebrow">Your collection</div><h1>Saved items</h1><p>Keep interesting Marketplace links in one place, ready to compare later.</p></div>
<div class="panel">
<h3>Save a listing</h3>
<form id="saveForm" class="formrow">
<label>Listing name<input name="title" placeholder="e.g. Realme C55" required maxlength="250"></label>
<label>Facebook item link<input name="listing_url" type="url" placeholder="https://www.facebook.com/marketplace/item/…" required></label>
<button class="btn" type="submit">Save item</button>
</form>
<p class="muted" id="saveStatus" role="status"></p>
<div id="savedItems" aria-live="polite"></div>
</div>
</section>
<section class="view" id="view-watchlist" hidden>
<div class="intro"><div class="eyebrow">Keep an eye on prices</div><h1>Your watchlist</h1><p>List devices and your ideal buying prices. Nothing scans automatically or spends credits.</p></div>
<div class="panel"><h3>Add a device to watch</h3>
<form id="watchForm" class="formrow">
<label>Device name<input name="keyword" placeholder="e.g. iPhone 13" minlength="2" maxlength="80" required></label>
<label>Maximum budget (₱)<input name="max_price" type="number" step=".01" min="0" placeholder="Optional"></label>
<button class="btn" type="submit">Add device</button>
</form>
<p class="muted" id="watchStatus" role="status"></p>
<div id="watchItems" aria-live="polite"></div>
</div>
</section>
<section class="view" id="view-profit" hidden>
<div class="intro"><div class="eyebrow">Know your numbers</div><h1>Profit calculator</h1><p>Estimate how much you could make after buying and selling a device.</p></div>
<div class="grid2">
<div class="panel"><h3>Enter your prices</h3><form id="calcForm">
<div class="grid3">
<label>Buying price (₱)<input name="buying_price" type="number" min=".01" step=".01" value="8000" required></label>
<label>Selling price (₱)<input name="selling_price" type="number" min="0" step=".01" value="10000" required></label>
<label>Extra expenses (₱)<input name="extra_costs" type="number" min="0" step=".01" value="500" required></label>
</div><p class="muted">Include delivery, repairs and other expenses.</p><button class="btn" type="submit">Calculate profit</button></form><p id="calcStatus" role="alert" class="error"></p></div>
<div class="panel"><h3>Estimated result</h3><div class="summarygrid"><div><div class="muted">Net profit</div><div id="calcProfit" class="out">—</div></div><div><div class="muted">ROI</div><div id="calcRoi" class="out">—</div></div></div><p class="muted">A manual estimate, not a verified market resale price.</p></div>
</div>
</section>
<div class="notice">Marketplace Finder · Laravel + SQLite · Single-user local development. Original FlipRadar data is kept separate until imported. <a href="/flipradar/index.html">Classic app</a></div>
</main></div>
<script>
'use strict';
const csrf=document.querySelector('meta[name="csrf-token"]').content;
const money=n=>new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(Number(n||0));
const filterKey='marketplace_finder_filters_v1';
const defaults={q:'',category:'',min_price:'',max_price:'',sort:'newest',found_within_days:''};
const categories=['','Electronics','Phones','Laptops','Cameras','PC Parts','Consoles'];
let category='';
function readFilters(){
 try{const saved=JSON.parse(localStorage.getItem(filterKey)||'{}');return {...defaults,...saved}}catch(e){return {...defaults}}
}
function rememberFilters(){
 const state={q:document.getElementById('search').value.trim(),category,
  min_price:document.getElementById('minPrice').value,
  max_price:document.getElementById('budget').value,
  sort:document.getElementById('sort').value,
  found_within_days:document.getElementById('foundWithinDays').value};
 try{localStorage.setItem(filterKey,JSON.stringify(state))}catch(e){}
 const cat=category||'All';
 document.getElementById('filterSummary').textContent=cat+' · '+(state.q||'all models')+' · '+
 (state.min_price?'from ₱'+Number(state.min_price).toLocaleString('en-PH'):'no minimum')+
 ' · '+(state.max_price?'to ₱'+Number(state.max_price).toLocaleString('en-PH'):'any maximum')+
 ' · '+document.getElementById('sort').selectedOptions[0].text+' · Bohol only';
}
function restoreFilters(){
 const saved=readFilters();
 category=categories.includes(saved.category)?saved.category:'';
 for(const [id,key] of [['search','q'],['minPrice','min_price'],['budget','max_price'],['sort','sort'],['foundWithinDays','found_within_days']]){
  const input=document.getElementById(id);
  const val=String(saved[key]??'');
  if(input.tagName==='SELECT'&&!Array.from(input.options).some(o=>o.value===val))continue;
  input.value=val;
 }
 document.querySelectorAll('[data-category]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category===category)));
 if(saved.min_price||saved.found_within_days)toggleExtraFilters(true);
 rememberFilters();
}
function toggleExtraFilters(open){
 const extra=document.getElementById('extraFilters');
 extra.hidden=!open;
 document.getElementById('extraFiltersToggle').setAttribute('aria-expanded',String(open));
 document.getElementById('extraFiltersToggle').textContent=open?'Fewer filters ▴':'More filters ▾';
}

const views=['discover','saved','watchlist','profit'];
function navigate(view){
 if(!views.includes(view))return;
 views.forEach(v=>{document.getElementById('view-'+v).hidden=v!==view});
 document.querySelectorAll('[data-view]').forEach(b=>b.setAttribute('aria-current',b.dataset.view===view?'page':'false'));
 history.replaceState(null,'','#'+view);
 if(view==='saved')loadSaved();
 if(view==='watchlist')loadWatch();
 window.scrollTo({top:0,behavior:'instant'});
}
document.querySelectorAll('[data-view]').forEach(b=>b.addEventListener('click',()=>navigate(b.dataset.view)));
if(views.includes(location.hash.slice(1)))navigate(location.hash.slice(1));
async function request(path,method='GET',body){
 const options={method,headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}};
 if(body!==undefined){options.headers['Content-Type']='application/json';options.body=JSON.stringify(body)}
 const response=await fetch(path,options);
 const value=await response.json().catch(()=>({}));
 if(!response.ok)throw Error(value.errors?Object.values(value.errors).flat().join(' '):value.message||'Request failed');
 return value;
}
const label=(text,cls)=>{const e=document.createElement('span');e.className=cls||'';e.textContent=text;return e};
const button=(title,callback)=>{const e=document.createElement('button');e.className='btn sm alt';e.type='button';e.textContent=title;e.addEventListener('click',callback);return e};
async function loadListings(){
 const box=document.getElementById('listingResults');
 const feedback=document.getElementById('searchFeedback');
 const searchButton=document.getElementById('historySearchButton');
 searchButton.disabled=true;
 searchButton.textContent='Searching…';
 feedback.textContent='Checking your stored listings…';
 const p=new URLSearchParams();const search=document.getElementById('search').value.trim();
 const budget=document.getElementById('budget').value;
 const minimum=document.getElementById('minPrice').value;
 const date=document.getElementById('foundWithinDays').value;
 if(minimum!==''&&budget!==''&&Number(minimum)>Number(budget)){
  feedback.textContent='Minimum price cannot exceed maximum price.';
  searchButton.disabled=false;searchButton.textContent='Apply filters';return;
 }
 rememberFilters();
 if(search)p.set('q',search);if(budget)p.set('max_price',budget);
 if(minimum)p.set('min_price',minimum);if(date)p.set('found_within_days',date);
 if(category)p.set('category',category);
 p.set('sort',document.getElementById('sort').value);
 try{
 const data=await request('/listings?'+p.toString());
 box.replaceChildren();
 if(!data.items.length){
 feedback.textContent='No Bohol-verified matches for '+(search?'“'+search+'”':'these filters')+'. '+(data.excluded_unverified||0)+' archived listings with missing/out-of-area location evidence are hidden.';
 box.className='stack';const empty=document.createElement('div');empty.className='empty';const heading=document.createElement('strong');heading.textContent='No matching listings stored';const msg=document.createElement('p');msg.textContent='This search checked your local results only. It did not contact Facebook or charge your Apify account.';
 const external=document.createElement('a');
 external.href='https://www.facebook.com/marketplace/search/?query='+encodeURIComponent(search||'gadgets');
 external.target='_blank';external.rel='noopener noreferrer';
 external.className='btn sm alt';external.textContent='Search on Facebook ↗';
 empty.append(heading,msg,button('Set up a fresh search',showScanSettings),document.createTextNode(' '),external,document.createTextNode(' '),button('Clear filters',()=>{document.getElementById('search').value='';clearFilters();loadListings()}));box.append(empty);return}
 box.className='cards';feedback.textContent=data.items.length+' matching Bohol listings (not a fresh Facebook search). '+(data.excluded_unverified||0)+' unverified/outside listings hidden.';
 for(const item of data.items){
 const card=document.createElement('article');card.className='listing';
 const art=document.createElement('div');art.className='listing-art';art.textContent=({'Phones':'📱','Laptops':'💻','Cameras':'📷','PC Parts':'🖥️','Consoles':'🎮'})[item.category]||'📦';
 const body=document.createElement('div');body.className='listing-info';
 const badge=label(item.category+' · Saved in discovery history','pill');
 const h=document.createElement('h3');h.textContent=item.title;
 const price=label(money(item.price),'listing-price');
 const city=label((item.city||'Bohol province')+' · Bohol location inferred from listing data','muted');
 const actions=document.createElement('div');actions.className='listing-actions';
 if(item.url){
  const a=document.createElement('a');a.href=item.url;a.target='_blank';a.rel='noopener noreferrer';a.className='btn sm alt';a.textContent='View listing';
  actions.append(a);
  actions.append(button('♡ Save',async()=>{try{await request('/collections/saved','POST',{title:item.title,listing_url:item.url,price:item.price,city:item.city});alert('Saved to your collection')}catch(e){alert(e.message)}}));
 }else{actions.append(label('Listing link unavailable','muted'))}
 body.append(badge,h,price,city,actions);card.append(art,body);box.append(card);
 }
 }catch(e){feedback.textContent='Search failed';box.textContent='Could not load listings: '+e.message}
 finally{searchButton.disabled=false;searchButton.textContent='Apply filters'}
}
function showScanSettings(){
 const details=document.getElementById('scanSettings');
 const query=document.getElementById('search').value.trim();
 const scanKeyword=document.querySelector('#scanForm [name="keyword"]');
 scanKeyword.value=query;
 const scanForm=document.getElementById('scanForm');
 scanForm.elements.namedItem('category').value=category;
 scanForm.elements.namedItem('min_price').value=document.getElementById('minPrice').value;
 scanForm.elements.namedItem('max_price').value=document.getElementById('budget').value;
 const currentSort=document.getElementById('sort').value;
 scanForm.elements.namedItem('sort_by').value=currentSort==='price_low'?'price_ascend':currentSort==='price_high'?'price_descend':'creation_time_descend';
 details.open=true;
 details.scrollIntoView({behavior:'smooth',block:'center'});
 scanKeyword.focus({preventScroll:true});
}
document.getElementById('openFreshScan').addEventListener('click',showScanSettings);
document.getElementById('browseForm').addEventListener('submit',async e=>{
 e.preventDefault();
 await loadListings();
 if(document.getElementById('listingResults').querySelector('.empty')){
   showScanSettings();
   document.getElementById('searchFeedback').textContent='No matching listings stored. Fresh scan options are now open below. Nothing has been charged.';
 }
});
document.getElementById('sort').addEventListener('change',loadListings);
document.getElementById('foundWithinDays').addEventListener('change',loadListings);
document.getElementById('extraFiltersToggle').addEventListener('click',()=>toggleExtraFilters(document.getElementById('extraFilters').hidden));
function clearFilters(){
 category='';
 for(const [id,value] of [['search',''],['budget',''],['minPrice',''],['foundWithinDays',''],['sort','newest']])document.getElementById(id).value=value;
 document.querySelectorAll('[data-category]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category==='')));
 toggleExtraFilters(false);rememberFilters();
}
document.getElementById('resetFilters').addEventListener('click',()=>{clearFilters();loadListings()});
document.querySelectorAll('[data-category]').forEach(b=>b.addEventListener('click',()=>{
 category=b.dataset.category;
 document.querySelectorAll('[data-category]').forEach(x=>x.setAttribute('aria-pressed',String(x===b)));
 loadListings();
}));
async function loadSaved(){
 const box=document.getElementById('savedItems');box.replaceChildren();
 try{
 const data=await request('/collections/saved');
 if(!data.items.length){box.append(label('No saved items yet. Save one from Find deals or paste a Facebook listing link.','muted'));return}
 for(const item of data.items){
  const row=document.createElement('div');row.className='storageitem';const info=document.createElement('div');
  const a=document.createElement('a');a.href=item.listing_url;a.target='_blank';a.rel='noopener noreferrer';a.textContent=item.title;
  info.append(a,document.createElement('br'),label(item.price!==null?money(item.price):'Price not recorded','muted'));
  row.append(info,button('Remove',async()=>{try{await request('/collections/saved/'+item.id,'DELETE');loadSaved()}catch(e){document.getElementById('saveStatus').textContent=e.message}}));box.append(row);
 }
 }catch(e){box.textContent='Unable to load saved items: '+e.message}
}
async function loadWatch(){
 const box=document.getElementById('watchItems');box.replaceChildren();
 try{
 const data=await request('/collections/watchlist');
 if(!data.items.length){box.append(label('Your watchlist is empty. Add a device you want to find.','muted'));return}
 for(const item of data.items){
  const row=document.createElement('div');row.className='storageitem';const info=document.createElement('div');
  info.append(label(item.keyword,'pill'),document.createElement('br'),label((item.max_price!==null?'Up to '+money(item.max_price):'Any price')+' · '+(item.enabled?'Enabled':'Paused'),'muted'));
  row.append(info,button('Remove',async()=>{try{await request('/collections/watchlist/'+item.id,'DELETE');loadWatch()}catch(e){document.getElementById('watchStatus').textContent=e.message}}));box.append(row);
 }
 }catch(e){box.textContent='Unable to load watchlist: '+e.message}
}
document.getElementById('saveForm').addEventListener('submit',async e=>{e.preventDefault();try{await request('/collections/saved','POST',Object.fromEntries(new FormData(e.currentTarget)));e.currentTarget.reset();document.getElementById('saveStatus').textContent='Item saved.';loadSaved()}catch(error){document.getElementById('saveStatus').textContent=error.message}});
document.getElementById('watchForm').addEventListener('submit',async e=>{e.preventDefault();const data=Object.fromEntries(new FormData(e.currentTarget));data.enabled=true;if(!data.max_price)data.max_price=null;try{await request('/collections/watchlist','POST',data);e.currentTarget.reset();document.getElementById('watchStatus').textContent='Device added.';loadWatch()}catch(error){document.getElementById('watchStatus').textContent=error.message}});
document.getElementById('calcForm').addEventListener('submit',async e=>{e.preventDefault();const status=document.getElementById('calcStatus');status.textContent='';try{const data=await request('/calculator/estimate','POST',Object.fromEntries(new FormData(e.currentTarget)));document.getElementById('calcProfit').textContent=money(data.profit);document.getElementById('calcRoi').textContent=Number(data.roi_percent).toFixed(2)+'%'}catch(error){status.textContent=error.message}});
document.getElementById('scanForm').addEventListener('submit',async e=>{
 e.preventDefault();if(!document.getElementById('paidScan').checked)return;
 if(!confirm('This can charge your Apify account. Start one scan?'))return;
 const btn=document.getElementById('scanButton'),status=document.getElementById('scanStatus');btn.disabled=true;status.textContent='Scanning…';
 try{
 const data=Object.fromEntries(new FormData(e.currentTarget));
 if(!data.keyword)delete data.keyword;
 if(!data.min_price)delete data.min_price;
 else data.min_price=Number(data.min_price);
 if(!data.max_price)delete data.max_price;
 else data.max_price=Number(data.max_price);
 if(!data.category)delete data.category;
 data.radius_km=Number(data.radius_km);data.latitude=9.6477;data.longitude=123.8547;data.confirm_paid_scan=true;
 const outcome=await request('/marketplace/scan','POST',data);
 status.textContent=outcome.archived_count+' identifiable listings archived. Only records with Bohol location evidence display; category coverage is not guaranteed.';
 if(data.category){category=data.category;document.querySelectorAll('[data-category]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category===category)))}
 await loadListings();
 document.getElementById('scanSettings').open=false;
 }catch(error){status.textContent=error.message}
 finally{btn.disabled=false;document.getElementById('paidScan').checked=false}
});
restoreFilters();
loadListings();
</script>
</body></html>
