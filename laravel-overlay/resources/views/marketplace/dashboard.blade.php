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
.panel{background:var(--panel);border:1px solid var(--line);border-radius:19px;padding:22px}.searchbox{padding:19px;background:linear-gradient(120deg,#1b2c46,#172238);border:1px solid #384963;border-radius:19px}.searchrow{display:grid;grid-template-columns:minmax(0,2fr) minmax(130px,1fr) minmax(145px,1fr) auto;gap:11px;align-items:end}.filterrow{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;margin-top:13px}.filterrow label{min-width:0}.filter-summary{padding:10px 1px 0;color:var(--muted);font-size:12px}.filter-toggle{border:0;background:transparent;padding:9px 3px;color:var(--orange);font-weight:700;font-size:13px}.extra-filters[hidden]{display:none}.searchbox .chip{margin-bottom:2px}label{display:block;color:var(--muted);font-size:12px;font-weight:600}input,select{display:block;width:100%;min-width:0;border-radius:11px;border:1px solid #42526b;background:#101c2e;color:var(--text);padding:11px 12px;margin-top:6px}input::placeholder{color:#8190a9}.quicklinks{display:flex;gap:8px;flex-wrap:wrap;margin:15px 0 0}.chip{border:1px solid #40516a;border-radius:50px;padding:8px 13px;background:#1e2b42;color:#d0d9e9;font-size:12px}.chip[aria-pressed=true]{background:#3b3228;color:var(--orange);border-color:#b77d35}.muted{color:var(--muted);font-size:12px}.sectiontop{margin:27px 0 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}.stack{display:grid;gap:13px}.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.listing{background:var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}.listing-art{height:190px;display:grid;place-items:center;background:linear-gradient(130deg,#263b57,#1b263c);font-size:32px;overflow:hidden;position:relative}.listing-art img{width:100%;height:100%;object-fit:cover;display:block}.listing-art .photo-fallback{font-size:35px}.listing-art .photo-label{position:absolute;bottom:7px;right:9px;background:#101a2ad9;color:#b9c6d8;border-radius:6px;padding:2px 6px;font-size:10px}.listing-info{padding:15px;display:flex;flex-direction:column;flex:1}.listing h3{font-size:16px;margin:7px 0}.listing-price{font-size:24px;font-weight:850;letter-spacing:-.5px;margin:8px 0}.listing-actions{display:flex;gap:8px;margin-top:auto;padding-top:12px}.pill{font-size:11px;color:var(--mint);font-weight:700}.empty{padding:30px 24px;border:1px dashed #44536b;border-radius:16px;text-align:center;background:var(--panel)}.empty strong{font-size:17px}.empty p{max-width:470px;margin:7px auto 16px}.hint{margin-top:14px;padding:13px 15px;border-radius:12px;background:#1e2a3f;border:1px solid #3b4c63;color:#c1cede;font-size:13px}
details.settings{margin-top:15px;border:1px solid var(--line);border-radius:14px;padding:15px;background:var(--side)}summary{cursor:pointer;color:#f4dfbd;font-weight:700}details.settings p{font-size:13px}.grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.formrow{display:grid;grid-template-columns:2fr 1fr auto;gap:10px;align-items:end}.storageitem{display:flex;align-items:center;justify-content:space-between;gap:14px;border-radius:12px;background:#17263b;border:1px solid var(--line);padding:13px;margin-top:10px}.storageitem a{color:var(--orange);overflow-wrap:anywhere}.storageitem small{display:block;color:var(--muted);font-size:12px}.error{color:var(--red)}.success{color:var(--mint)}.out{font-size:29px;font-weight:850}.checkboxrow{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:var(--muted);margin:17px 0}.checkboxrow input{width:18px;height:18px;margin:1px 0}.summarygrid{display:flex;gap:45px;flex-wrap:wrap}.log-actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}.log-actions .btn{width:auto}.log-entry{padding:12px 14px;background:#162238;border:1px solid var(--line);border-radius:12px;margin-top:9px}.log-entry strong{font-size:13px}.log-entry small{display:block;color:var(--muted);font-size:11px}.log-entry pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:200px;overflow:auto;color:#bdcde0;font:12px/1.5 ui-monospace,monospace;margin:8px 0 0}.log-badge{font-weight:700;color:var(--mint)}.log-badge.error{color:var(--red)}.notice{border-top:1px solid var(--line);padding-top:18px;margin-top:24px;color:var(--muted);font-size:12px}
@media(max-width:950px){.app{display:block}.aside{padding:12px 16px;gap:8px;border-right:0;border-bottom:1px solid var(--line)}.menu{display:flex;overflow:auto;gap:6px}.menu button{width:auto;white-space:nowrap}.asidefoot{display:none}main{padding:25px 19px}.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.searchrow,.filterrow,.grid2,.grid3,.formrow,.cards{grid-template-columns:1fr}.panel{padding:16px}.listing-art{height:175px}.headrow{align-items:flex-start}.storageitem{align-items:flex-start;flex-direction:column}.btn{width:100%}.listing-actions .btn{width:auto}.menu button{padding:10px 12px}}
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
<button type="button" data-view="activity">▤ &nbsp; Activity logs</button>
<button type="button" data-view="rejected">⊘ &nbsp; Rejected listings</button>
<button type="button" data-view="status">✓ &nbsp; Availability history</button>
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
<div class="sectiontop"><h2 id="listingHeading">Bohol listings</h2><button type="button" id="showArchive" class="btn sm alt" hidden>Show all stored listings</button><span class="muted">Only Bohol-eligible listings</span></div>
<p id="searchFeedback" role="status" class="muted" style="margin:0 0 12px">Ready to search previously collected listings.</p>
<div id="listingResults" class="cards" aria-live="polite"><div class="empty">Loading your discovered listings…</div></div>
<div class="hint">Missing product photos from earlier scans? <button class="btn sm alt" type="button" id="restoreLegacyImages">Restore photos from classic FlipRadar</button> <span id="photoRestoreStatus" role="status" class="muted">Uses existing browser data only. No paid scan.</span></div>
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
<p class="muted">Blank keyword uses a small category-based keyword plan like classic FlipRadar. This is not a complete Facebook category feed.</p><div id="scanPlanPreview" class="hint" aria-live="polite">Checking scan plan…</div>
<label class="checkboxrow"><input id="paidScan" type="checkbox" required><span>I approve the potentially billable requests shown in the scan plan. Results and Bohol location accuracy are not guaranteed.</span></label>
<button class="btn" id="scanButton" type="submit">Run approved searches</button>
</form>
<p id="scanStatus" role="status" class="muted">Scanning is off by default. Set APIFY_TOKEN and MARKETPLACE_SCAN_ENABLED in the private Laravel environment file to enable it.</p>
</details>

</section>
<section class="view" id="view-activity" hidden>
<div class="intro">
<div class="eyebrow">Diagnostics / Activity</div>
<h1>Activity logs</h1>
<p>Check what happened when you searched, applied filters, or requested a Marketplace scan.</p>
</div>
<div class="panel" id="activityPanel">
<div class="headrow"><h2>Search &amp; scan history</h2><span id="logCount" class="muted"></span></div>
<p class="muted">The latest 100 events stay in this browser. No Apify token is intentionally stored. Logs may contain search terms and budget settings; review them before sharing.</p>
<div class="log-actions">
<button id="logAudit" type="button" class="btn sm alt">Record current filters</button>
<button id="logExport" type="button" class="btn sm alt">Export JSON</button>
<button id="logCopy" type="button" class="btn sm alt">Copy logs</button>
<button id="logClear" type="button" class="btn sm subtle">Clear logs</button>
</div>
<p id="logFeedback" class="muted" role="status"></p>
<div id="logEntries" aria-live="polite"></div>
<div class="hint">Want to inspect everything the filters excluded? <button class="btn sm alt" type="button" id="openRejectedFromLogs">Open rejected listings</button></div>
</div>
</section>
<section class="view" id="view-rejected" hidden>
<div class="intro"><div class="eyebrow">Diagnostics / Filter inspector</div>
<h1>Rejected listings</h1>
<p>Review every archived listing excluded by the current Find deals rules. A listing can have multiple rejection reasons. Read-only, with no Apify requests.</p></div>
<div class="panel">
<div class="headrow"><h2>Filter decisions</h2><button type="button" class="btn sm alt" id="rejectedRefresh">Refresh</button></div>
<div class="filterrow">
<label>Which listings?
<select id="rejectedScope">
<option value="current">Current Find deals filters</option>
<option value="archive">Entire archive (Bohol and sale rules)</option>
<option value="batch">Latest scan (if available)</option>
</select></label>
<label>Reason
<select id="rejectedReason"><option value="">All reasons</option></select></label>
<label>Listings per page
<select id="rejectedPageSize"><option value="20">20</option><option value="40" selected>40</option><option value="100">100</option></select></label>
</div>
<p id="rejectedFilterSummary" class="muted"></p>
<p id="rejectedStatus" role="status" class="muted">Open this view to inspect archived exclusions.</p>
<div id="rejectedReasonStats" class="quicklinks"></div>
<div id="rejectedResults" class="stack" aria-live="polite"></div>
<div class="log-actions">
<button type="button" class="btn sm alt" id="rejectedPrev">Previous page</button>
<span id="rejectedPageInfo" class="muted" style="align-self:center"></span>
<button type="button" class="btn sm alt" id="rejectedNext">Next page</button>
</div>
<p class="muted">A location marked “unverified” means the archived metadata is insufficient—not proof the seller is outside Bohol. The inspector does not change or delete listings.</p>
</div>
</section>
<section class="view" id="view-status" hidden>
<div class="intro"><div class="eyebrow">Your archive / Manual checks</div>
<h1>Availability history</h1>
<p>Items you manually tagged after visiting Facebook. These are your observations, not verified Facebook statuses. You can undo a tag at any time without losing its discovery history.</p></div>
<div class="panel">
<div class="headrow"><h2>Tagged listings</h2><button id="refreshStatusHistory" type="button" class="btn sm alt">Refresh</button></div>
<p id="manualHistoryFeedback" role="status" class="muted"></p>
<div id="manualHistoryEntries" class="stack"></div>
<div class="log-actions"><button id="statusPrev" class="btn sm alt" type="button">Previous</button>
<span id="statusPage" class="muted" style="align-self:center"></span><button id="statusNext" type="button" class="btn sm alt">Next</button></div>
</div>
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
let lastScanBatch=null;
const money=n=>new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(Number(n||0));
const logKey='marketplace_finder_activity_log_v1';
function redactLog(value){
 const serialized=JSON.stringify(value??{});
 if(!serialized)return {};
 const safe=serialized.replace(/apify_api_[a-z0-9_-]+/gi,'[REDACTED]')
  .replace(/(token|authorization|api_key|secret|password)(["']?\\s*[:=]\\s*["']?)[^,}\\s"']+/gi,'$1$2[REDACTED]')
  .replace(/Bearer\\s+[^\\s"']+/gi,'Bearer [REDACTED]');
 try{return JSON.parse(safe)}catch(e){return {message:'Details redacted'}}
}
function activityLogs(){
 try{const items=JSON.parse(localStorage.getItem(logKey)||'[]');return Array.isArray(items)?items.slice(0,100):[]}catch(e){return []}
}
function logActivity(type,details={}){
 const log=[{time:new Date().toISOString(),type,details:redactLog(details)},...activityLogs()].slice(0,100);
 try{localStorage.setItem(logKey,JSON.stringify(log))}catch(e){}
 renderActivityLogs();
}
function diagnosticsExport(){
 return JSON.stringify({app:'Marketplace Finder Laravel',version:1,exportedAt:new Date().toISOString(),
  note:'Logs may contain search words, budget and category settings. Review before sharing. API tokens are not deliberately stored.',
  events:activityLogs()},null,2);
}
function renderActivityLogs(){
 const entries=document.getElementById('logEntries');if(!entries)return;
 const data=activityLogs();entries.replaceChildren();
 document.getElementById('logCount').textContent='· '+data.length+' events';
 if(!data.length){entries.textContent='No events recorded yet. Apply filters or run a scan to see what happens.';return}
 for(const item of data.slice(0,40)){
  const row=document.createElement('div');row.className='log-entry';
  const title=document.createElement('strong');title.className='log-badge'+(String(item.type).includes('failed')?' error':'');title.textContent=item.type;
  const time=document.createElement('small');time.textContent=item.time;
  const extra=document.createElement('pre');extra.textContent=JSON.stringify(item.details,null,2);
  row.append(title,time,extra);entries.append(row);
 }
}
function downloadActivityLogs(){
 const blob=new Blob([diagnosticsExport()],{type:'application/json'});
 const url=URL.createObjectURL(blob);
 const a=document.createElement('a');a.href=url;
 a.download='marketplace-finder-logs-'+new Date().toISOString().slice(0,10)+'.json';
 document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
}
function openActivityLogs(){navigate('activity')}
document.getElementById('logAudit').addEventListener('click',()=>{
 logActivity('filter_audit',{category:category||'All',keyword:document.getElementById('search').value.trim(),
  minPrice:document.getElementById('minPrice').value||null,maxPrice:document.getElementById('budget').value||null,
  sort:document.getElementById('sort').value,discoveredWithin:document.getElementById('foundWithinDays').value||null,area:'Bohol'});
 document.getElementById('logFeedback').textContent='Current filters recorded.';
});
document.getElementById('logExport').addEventListener('click',downloadActivityLogs);
document.getElementById('logCopy').addEventListener('click',async()=>{
 try{await navigator.clipboard.writeText(diagnosticsExport());document.getElementById('logFeedback').textContent='Activity log copied.'}
 catch(e){document.getElementById('logFeedback').textContent='Clipboard unavailable. Use Export JSON instead.'}
});
document.getElementById('openRejectedFromLogs').addEventListener('click',()=>navigate('rejected'));
let rejectedPage=1;
let rejectedLastPage=1;
let rejectedBusy=false;
function rejectedSearchParams(){
 const mode=document.getElementById('rejectedScope').value;
 const p=new URLSearchParams();
 if(mode==='current'){
  const q=document.getElementById('search').value.trim();
  if(q)p.set('q',q);
  if(category)p.set('category',category);
  const min=document.getElementById('minPrice').value;
  const max=document.getElementById('budget').value;
  const days=document.getElementById('foundWithinDays').value;
  if(min!=='')p.set('min_price',min);
  if(max!=='')p.set('max_price',max);
  if(days)p.set('found_within_days',days);
  if(lastScanBatch)p.set('batch',lastScanBatch);
 }else if(mode==='batch'&&lastScanBatch){
  p.set('batch',lastScanBatch);
 }
 p.set('page',String(rejectedPage));
 p.set('per_page',document.getElementById('rejectedPageSize').value);
 const reason=document.getElementById('rejectedReason').value;
 if(reason)p.set('reason',reason);
 document.getElementById('rejectedFilterSummary').textContent=
  mode==='current'?'Using Find deals filters: '+(p.get('q')||'all keywords')+' · '+(p.get('category')||'all categories')+
   ' · '+(p.get('min_price')||'0')+' to '+(p.get('max_price')||'unlimited')+' PHP'+
   (p.get('batch')?' · most recent scan':' · all archived scans')
  :mode==='batch'?(lastScanBatch?'Latest scan only; Bohol and sale rules':'No latest scan ID in this page session; showing archived records')
   :'All archived listings; Bohol location and sale-intent rules only';
 return p;
}
function safeMarketplaceLink(raw){
 try{const u=new URL(raw);return u.protocol==='https:'&&(u.hostname==='facebook.com'||u.hostname.endsWith('.facebook.com'))?u.href:null}catch(e){return null}
}
function safeMarketplacePhoto(raw){
 try{const u=new URL(raw);return u.protocol==='https:'&&(u.hostname==='fbcdn.net'||u.hostname.endsWith('.fbcdn.net')||u.hostname==='fbsbx.com'||u.hostname.endsWith('.fbsbx.com'))?u.href:null}catch(e){return null}
}
async function loadRejectedListings(){
 if(rejectedBusy)return;
 const host=document.getElementById('rejectedResults'),status=document.getElementById('rejectedStatus');
 const params=rejectedSearchParams();
 const low=params.get('min_price'),high=params.get('max_price');
 if(low!==null&&high!==null&&Number(low)>Number(high)){
  status.textContent='Minimum price cannot exceed maximum price. Correct the Find deals filters before inspecting them.';
  host.replaceChildren();return;
 }
 rejectedBusy=true;
 status.textContent='Inspecting archived filter decisions…';host.replaceChildren();
 try{
  const data=await request('/listings/rejected?'+params.toString());
  rejectedLastPage=data.pages;
  status.textContent=data.checked_count+' archived records checked · '+data.accepted_count+' accepted · '+data.rejected_count+
   ' rejected by at least one rule · '+data.matched_reason_count+' matching the selected rejection reason.';
  const reasonSelect=document.getElementById('rejectedReason'),chosen=reasonSelect.value;
  reasonSelect.replaceChildren();
  const all=document.createElement('option');all.value='';all.textContent='All reasons';reasonSelect.append(all);
  const stats=document.getElementById('rejectedReasonStats');stats.replaceChildren();
  for(const [key,label] of Object.entries(data.reason_labels)){
   const n=data.reason_counts[key]||0;
   const opt=document.createElement('option');opt.value=key;opt.textContent=label+' ('+n+')';reasonSelect.append(opt);
   if(n){
    const chip=document.createElement('button');chip.type='button';chip.className='chip';
    chip.textContent=label+': '+n;
    chip.addEventListener('click',()=>{reasonSelect.value=key;rejectedPage=1;loadRejectedListings()});
    stats.append(chip);
   }
  }
  reasonSelect.value=chosen;
  for(const item of data.items){
   const row=document.createElement('article');row.className='log-entry';
   const line=document.createElement('div');line.className='headrow';
   const title=document.createElement('strong');title.textContent=item.title;
   const price=document.createElement('strong');price.textContent=money(item.price);
   line.append(title,price);row.append(line);
   const photo=safeMarketplacePhoto(item.image_url);
   if(photo){
    const img=document.createElement('img');img.src=photo;img.loading='lazy';img.alt='Listing photo';
    img.referrerPolicy='no-referrer';img.style.cssText='width:115px;height:90px;object-fit:cover;border-radius:8px;margin-top:8px';
    row.append(img);
   }
   const meta=document.createElement('small');meta.textContent=
    'Category: '+(item.category||'Other')+' · Town: '+(item.city||'unknown')+
    ' · State: '+(item.province||'unknown')+' · Location: '+item.location_status+
    ' · Intent: '+item.listing_intent;row.append(meta);
   if(item.location_text){
    const loc=document.createElement('small');loc.textContent='Original location text: '+item.location_text;row.append(loc);
   }
   if(item.latitude!==null&&item.longitude!==null){
    const coords=document.createElement('small');coords.textContent='Coordinates: '+item.latitude+', '+item.longitude;row.append(coords);
   }
   const reasons=document.createElement('p');
   reasons.textContent='Excluded because: '+item.reason_labels.join(' · ');
   reasons.style.color='var(--orange)';row.append(reasons);
   if(item.reasons.includes('outside_bohol')||item.reasons.includes('unverified_location')){
    const detail=document.createElement('small');detail.textContent='Location decision: '+item.location_reason;row.append(detail);
   }
   if(item.reasons.some(x=>['accessory','repair','wanted','other_intent'].includes(x))){
    const detail=document.createElement('small');detail.textContent='Sale classification: '+item.intent_reason;row.append(detail);
   }
   const url=safeMarketplaceLink(item.url);
   if(url){const a=document.createElement('a');a.href=url;a.target='_blank';a.rel='noopener noreferrer';
    a.textContent='View original Marketplace listing';row.append(a)}
   host.append(row);
  }
  if(!data.items.length){
   const note=document.createElement('p');note.textContent='No rejected listings on this page for these settings.';host.append(note);
  }
  document.getElementById('rejectedPageInfo').textContent='Page '+data.page+' of '+data.pages;
  document.getElementById('rejectedPrev').disabled=data.page<=1;
  document.getElementById('rejectedNext').disabled=data.page>=data.pages;
 }catch(error){status.textContent='Could not inspect listings: '+error.message}
 finally{rejectedBusy=false}
}
document.getElementById('rejectedRefresh').addEventListener('click',()=>{rejectedPage=1;loadRejectedListings()});
for(const name of ['rejectedScope','rejectedReason','rejectedPageSize']){
 document.getElementById(name).addEventListener('change',()=>{rejectedPage=1;loadRejectedListings()});
}
document.getElementById('rejectedPrev').addEventListener('click',()=>{if(rejectedPage>1){rejectedPage--;loadRejectedListings()}});
document.getElementById('rejectedNext').addEventListener('click',()=>{if(rejectedPage<rejectedLastPage){rejectedPage++;loadRejectedListings()}});

document.getElementById('logClear').addEventListener('click',()=>{
 if(!confirm('Clear all locally stored Marketplace Finder logs?'))return;
 try{localStorage.removeItem(logKey)}catch(e){}renderActivityLogs();
 document.getElementById('logFeedback').textContent='Activity log cleared.';
});
renderActivityLogs();

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

let manualHistoryPage=1;
let manualHistoryLastPage=1;
const views=['discover','saved','watchlist','profit','activity','rejected','status'];
function navigate(view){
 if(!views.includes(view))return;
 views.forEach(v=>{document.getElementById('view-'+v).hidden=v!==view});
 document.querySelectorAll('[data-view]').forEach(b=>b.setAttribute('aria-current',b.dataset.view===view?'page':'false'));
 history.replaceState(null,'','#'+view);
 if(view==='saved')loadSaved();
 if(view==='watchlist')loadWatch();
 if(view==='activity')renderActivityLogs();
 if(view==='rejected')loadRejectedListings();
 if(view==='status')loadManualStatusHistory();
 window.scrollTo({top:0,behavior:'instant'});
}
document.querySelectorAll('[data-view]').forEach(b=>b.addEventListener('click',()=>navigate(b.dataset.view)));
if(views.includes(location.hash.slice(1)))navigate(location.hash.slice(1));
async function request(path,method='GET',body){
 const options={method,headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}};
 if(body!==undefined){options.headers['Content-Type']='application/json';options.body=JSON.stringify(body)}
 const response=await fetch(path,options);
 const value=await response.json().catch(()=>({}));
 if(!response.ok){
  if(response.status===429){
   const retryHeader=Number(response.headers.get('Retry-After'));
   const retrySeconds=Number.isFinite(retryHeader)&&retryHeader>0?Math.ceil(retryHeader):60;
   const error=new Error('A provider or infrastructure rate limit was reached. Retry after approximately '+retrySeconds+' seconds.');
   error.status=429;error.retryAfter=retrySeconds;
   throw error;
  }
  const error=new Error(value.errors?Object.values(value.errors).flat().join(' '):value.message||'Request failed');
  error.status=response.status;
  throw error;
 }
 return value;
}
const label=(text,cls)=>{const e=document.createElement('span');e.className=cls||'';e.textContent=text;return e};
const button=(title,callback)=>{const e=document.createElement('button');e.className='btn sm alt';e.type='button';e.textContent=title;e.addEventListener('click',callback);return e};
const manualStatusNames={unverified:'Not verified',available:'Available (user tagged)',pending:'Pending / reserved',sold:'Sold',unavailable:'Unavailable / removed'};
const hiddenManualStatuses=new Set(['pending','sold','unavailable']);
function makeManualStatusControl(item,onChanged){
 const box=document.createElement('div');box.style.cssText='margin-top:12px;border-top:1px solid var(--line);padding-top:10px';
 const title=document.createElement('label');title.textContent='Your availability tag (not Facebook-verified)';
 const select=document.createElement('select');select.setAttribute('aria-label','Availability for '+item.title);
 for(const [value,name] of Object.entries(manualStatusNames)){
  const opt=document.createElement('option');opt.value=value;opt.textContent=name;select.append(opt);
 }
 select.value=item.manual_status||'unverified';
 const info=document.createElement('small');info.className='muted';
 info.textContent=item.manual_status_updated_at?'Last tagged '+new Date(item.manual_status_updated_at).toLocaleString():'No manual verification yet';
 select.addEventListener('change',async()=>{
  const old=item.manual_status||'unverified',value=select.value;
  select.disabled=true;info.textContent='Saving your tag…';
  try{
   const result=await request('/listings/'+encodeURIComponent(item.id)+'/manual-status','PATCH',{status:value});
   item.manual_status=result.manual_status;
   item.manual_status_updated_at=result.manual_status_updated_at;
   info.textContent='Saved as '+manualStatusNames[value]+'. This is your report.';
   logActivity('manual_listing_status_changed',{listingId:item.id,from:old,to:value});
   if(onChanged)await onChanged();
  }catch(error){select.value=old;info.textContent='Could not save: '+error.message}
  finally{select.disabled=false}
 });
 box.append(title,select,info);
 return box;
}
async function loadManualStatusHistory(){
 const host=document.getElementById('manualHistoryEntries');
 const feedback=document.getElementById('manualHistoryFeedback');
 feedback.textContent='Loading manually tagged listings…';host.replaceChildren();
 try{
  const data=await request('/listings/manual-status-history?page='+manualHistoryPage);
  manualHistoryLastPage=data.pages;
  feedback.textContent=data.total+' tagged listing(s) preserved in SQLite. Tags are user-reported and cost no Apify credits.';
  for(const item of data.items){
   const row=document.createElement('article');row.className='log-entry';
   const heading=document.createElement('strong');heading.textContent=item.title+' · '+money(item.price);
   const meta=document.createElement('small');meta.textContent=(item.city||'Location unknown')+' · '+manualStatusNames[item.manual_status];
   row.append(heading,meta);
   if(item.listing_url){
    const link=document.createElement('a');
    try{const url=new URL(item.listing_url);if(url.protocol==='https:'&&(url.hostname==='facebook.com'||url.hostname.endsWith('.facebook.com'))){
     link.href=url.href;link.target='_blank';link.rel='noopener noreferrer';link.textContent='View original Marketplace listing';row.append(link)
    }}catch(e){}
   }
   row.append(makeManualStatusControl(item,loadManualStatusHistory));host.append(row);
  }
  if(!data.items.length){const empty=document.createElement('p');empty.textContent='No manually tagged listings on this page.';host.append(empty)}
  document.getElementById('statusPage').textContent='Page '+data.page+' of '+data.pages;
  document.getElementById('statusPrev').disabled=data.page<=1;
  document.getElementById('statusNext').disabled=data.page>=data.pages;
 }catch(e){feedback.textContent='Failed to load availability history: '+e.message}
}
document.getElementById('refreshStatusHistory').addEventListener('click',loadManualStatusHistory);
document.getElementById('statusPrev').addEventListener('click',()=>{if(manualHistoryPage>1){manualHistoryPage--;loadManualStatusHistory()}});
document.getElementById('statusNext').addEventListener('click',()=>{if(manualHistoryPage<manualHistoryLastPage){manualHistoryPage++;loadManualStatusHistory()}});

async function loadListings(){
 const box=document.getElementById('listingResults');
 const feedback=document.getElementById('searchFeedback');
 const searchButton=document.getElementById('historySearchButton');
 searchButton.disabled=true;
 searchButton.textContent='Searching…';
 feedback.textContent='Checking your stored listings…';
 const p=new URLSearchParams();const search=document.getElementById('search').value.trim();
 if(lastScanBatch)p.set('batch',lastScanBatch);
 document.getElementById('listingHeading').textContent=lastScanBatch?'Latest scan — Bohol listings':'Bohol listings';
 document.getElementById('showArchive').hidden=!lastScanBatch;
 const budget=document.getElementById('budget').value;
 const minimum=document.getElementById('minPrice').value;
 const date=document.getElementById('foundWithinDays').value;
 if(minimum!==''&&budget!==''&&Number(minimum)>Number(budget)){
  feedback.textContent='Minimum price cannot exceed maximum price. Previous results have been cleared; correct the price range to search.';
  box.replaceChildren();
  box.className='stack';
  const warning=document.createElement('div');
  warning.className='empty';warning.textContent='No search performed: minimum price exceeds maximum price.';
  box.append(warning);
  logActivity('local_search_invalid_filters',{reason:'minimum_exceeds_maximum',category:category||'All',minimum,maximum:budget});
  searchButton.disabled=false;searchButton.textContent='Apply filters';return;
 }
 rememberFilters();
 if(search)p.set('q',search);if(budget)p.set('max_price',budget);
 if(minimum)p.set('min_price',minimum);if(date)p.set('found_within_days',date);
 if(category)p.set('category',category);
 p.set('sort',document.getElementById('sort').value);
 const safeFilters={search:search||'(blank - all models)',batch:lastScanBatch||'all_archive',category:category||'All',minPrice:minimum||null,maxPrice:budget||null,sort:document.getElementById('sort').value,date:date||null,source:'local_sqlite'};
 logActivity('local_search_started',safeFilters);
 try{
 const data=await request('/listings?'+p.toString());
 box.replaceChildren();
 const d=data.diagnostics||{};
 logActivity('local_search_completed',{...safeFilters,shown:data.items.length,totalArchived:d.stored_total||0,boholArchived:d.bohol_total||0,matchingFilters:d.matching_filters||0,excluded:d.hidden_outside_or_unknown||0});
 if(!data.items.length){
 const zeroStored=(d.stored_total||0)===0;
 const noBohol=(d.bohol_total||0)===0;
 const reason=zeroStored?'No scans have archived any listings yet.':noBohol?'Archived listings exist, but none currently have enough Bohol location evidence.':'There are Bohol listings in the archive, but your selected filters excluded all of them.';
 feedback.textContent=reason+' '+(d.hidden_outside_or_unknown||0)+' out-of-area/unverified records hidden.';
 box.className='stack';const empty=document.createElement('div');empty.className='empty';const heading=document.createElement('strong');heading.textContent=zeroStored?'No scanned listings yet':noBohol?'No Bohol listings confirmed from metadata':'No matches with the current filters';const msg=document.createElement('p');msg.textContent=reason+' A blank search term means all models in the selected category; it does not trigger a paid scan.';
 const external=document.createElement('a');
 external.href='https://www.facebook.com/marketplace/search/?query='+encodeURIComponent(search||'gadgets');
 external.target='_blank';external.rel='noopener noreferrer';
 external.className='btn sm alt';external.textContent='Search on Facebook ↗';
 empty.append(heading,msg,button('Set up a fresh search',showScanSettings),document.createTextNode(' '),external,document.createTextNode(' '),button('Clear filters',()=>{document.getElementById('search').value='';clearFilters();loadListings()}));box.append(empty);return}
 box.className='cards';feedback.textContent=data.items.length+(lastScanBatch?' Bohol-eligible listing(s) from your latest scan.':' Bohol-eligible listing(s) from your saved archive.')+' '+(d.hidden_non_sale||0)+' non-sale/accessory ads in the archive are filtered out. '+(data.excluded_unverified||0)+' outside/unverified locations excluded.';
 for(const item of data.items){
 const card=document.createElement('article');card.className='listing';
 const art=document.createElement('div');art.className='listing-art';
 const fallback=document.createElement('span');fallback.className='photo-fallback';fallback.textContent=({'Phones':'📱','Laptops':'💻','Cameras':'📷','PC Parts':'🖥️','Consoles':'🎮'})[item.category]||'📦';
 if(item.image_url && typeof item.image_url==='string'){
   const img=document.createElement('img');
   img.src=item.image_url;
   img.alt='Photo of '+item.title;
   img.loading='lazy';img.decoding='async';img.referrerPolicy='no-referrer';
   img.addEventListener('error',()=>{img.remove();art.prepend(fallback);photoLabel.textContent='Photo unavailable';});
   art.append(img);
 }else{art.append(fallback)}
 const photoLabel=document.createElement('span');photoLabel.className='photo-label';
 photoLabel.textContent=item.image_url?'Marketplace photo':'Photo unavailable';art.append(photoLabel);
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
 const availability=makeManualStatusControl(item,()=>loadListings());
 body.append(badge,h,price,city,actions,availability);card.append(art,body);box.append(card);
 }
 }catch(e){feedback.textContent='Search failed';box.textContent='Could not load listings: '+e.message;logActivity('local_search_failed',{...safeFilters,error:String(e.message||'Request failed').slice(0,180)})}
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
 lastScanBatch=null;
 await loadListings();
 // Never confuse a local archive filter with a paid provider scan.
 // The user may open Fresh Marketplace search manually if desired.
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
document.getElementById('resetFilters').addEventListener('click',()=>{clearFilters();lastScanBatch=null;loadListings()});
document.getElementById('showArchive').addEventListener('click',()=>{lastScanBatch=null;loadListings()});
document.querySelectorAll('[data-category]').forEach(b=>b.addEventListener('click',()=>{
 category=b.dataset.category;
 document.querySelectorAll('[data-category]').forEach(x=>x.setAttribute('aria-pressed',String(x===b)));
 loadListings();
}));
function restoreLegacyImagesFromBrowser(){
 const status=document.getElementById('photoRestoreStatus');
 let records;
 try{records=JSON.parse(localStorage.getItem('fr_live')||'[]')}catch(e){records=[]}
 if(!Array.isArray(records)||!records.length){
  status.textContent='No classic FlipRadar listing cache found in this browser and site origin.';
  logActivity('photo_restore_skipped',{reason:'legacy_cache_empty'});
  return;
 }
 const seen=new Set(),images=[];
 for(const entry of records){
  if(!entry||typeof entry!=='object'||typeof entry.u!=='string'||typeof entry.i!=='string')continue;
  if(!/^https:\/\/(?:[a-z0-9-]+\.)?facebook\.com\/marketplace\/item\/[0-9]+\/?(?:\?.*)?$/i.test(entry.u))continue;
  if(!/^https:\/\/[a-z0-9.-]+\.(?:fbcdn\.net|fbsbx\.com)\//i.test(entry.i))continue;
  const id=entry.u.match(/\/marketplace\/item\/([0-9]+)/)?.[1];
  if(!id||seen.has(id))continue;
  seen.add(id);images.push({listing_url:entry.u,image_url:entry.i});
  if(images.length>=100)break;
 }
 if(!images.length){
  status.textContent='The classic cache has no matching Marketplace item photos.';
  logActivity('photo_restore_skipped',{reason:'no_usable_photo_links'});
  return;
 }
 status.textContent='Checking '+images.length+' cached photo links…';
 request('/listings/restore-images','POST',{images}).then(async(result)=>{
  status.textContent=result.updated+' existing listing photo(s) restored from the classic cache. Missing photos need a new approved scan.';
  logActivity('photo_restore_completed',{submitted:images.length,restored:result.updated});
  await loadListings();
 }).catch(err=>{
  status.textContent='Photo restore failed: '+err.message;
  logActivity('photo_restore_failed',{message:String(err.message).slice(0,160)});
 });
}
document.getElementById('restoreLegacyImages').addEventListener('click',restoreLegacyImagesFromBrowser);
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
document.getElementById('saveForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const form=e.currentTarget;
 const payload=Object.fromEntries(new FormData(form));
 try{
  await request('/collections/saved','POST',payload);
  form.reset();
  document.getElementById('saveStatus').textContent='Item saved.';
  loadSaved();
 }catch(error){document.getElementById('saveStatus').textContent=error.message}
});
document.getElementById('watchForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const form=e.currentTarget;
 const data=Object.fromEntries(new FormData(form));
 data.enabled=true;
 if(!data.max_price)data.max_price=null;
 try{
  await request('/collections/watchlist','POST',data);
  form.reset();
  document.getElementById('watchStatus').textContent='Device added.';
  loadWatch();
 }catch(error){document.getElementById('watchStatus').textContent=error.message}
});
document.getElementById('calcForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const form=e.currentTarget;
 const payload=Object.fromEntries(new FormData(form));
 const status=document.getElementById('calcStatus');
 status.textContent='';
 try{
  const data=await request('/calculator/estimate','POST',payload);
  document.getElementById('calcProfit').textContent=money(data.profit);
  document.getElementById('calcRoi').textContent=Number(data.roi_percent).toFixed(2)+'%';
 }catch(error){status.textContent=error.message}
});
let activeScanPlan=null;
async function refreshScanPlan(){
 const form=document.getElementById('scanForm');
 const params=new URLSearchParams();
 const keyword=form.elements.namedItem('keyword').value.trim();
 const selected=form.elements.namedItem('category').value||'All';
 if(keyword)params.set('keyword',keyword);
 params.set('category',selected);
 const node=document.getElementById('scanPlanPreview');
 activeScanPlan=null;
 try{
  const plan=await request('/marketplace/scan-plan?'+params.toString());
  activeScanPlan=plan;
  node.textContent='Planned '+plan.requests+' potentially billable Apify request(s): '+plan.terms.join(', ')+'. '+plan.coverage_note;
 }catch(error){node.textContent='Unable to preview scan plan: '+error.message}
}
document.querySelectorAll('#scanForm input[name="keyword"], #scanForm select[name="category"]').forEach(input=>input.addEventListener('change',refreshScanPlan));
document.getElementById('scanSettings').addEventListener('toggle',()=>{if(document.getElementById('scanSettings').open)refreshScanPlan()});
let scanInFlight=false;
document.getElementById('scanForm').addEventListener('submit',async e=>{
 e.preventDefault();
 if(scanInFlight)return;
 // Capture the HTML form synchronously. e.currentTarget becomes null after await.
 const scanForm=e.currentTarget;
 const approvedData=Object.fromEntries(new FormData(scanForm));
 if(!document.getElementById('paidScan').checked){logActivity('scan_not_started',{reason:'approval_checkbox_unchecked'});return}
 await refreshScanPlan();
 if(!activeScanPlan){document.getElementById('scanStatus').textContent='Unable to verify scan plan. Nothing was charged.';return}
 if(!confirm('Approve '+activeScanPlan.requests+' potentially billable Apify run(s) for: '+activeScanPlan.terms.join(', ')+'? The actor charge parameter is not a guaranteed billing ceiling.')){logActivity('scan_cancelled',{reason:'user_cancelled_confirmation'});return}
 const btn=document.getElementById('scanButton'),status=document.getElementById('scanStatus');
 scanInFlight=true;btn.disabled=true;btn.textContent='Scanning…';status.textContent='Scanning…';
 try{
 const data=approvedData;
 if(!data.keyword)delete data.keyword;
 if(!data.min_price)delete data.min_price;
 else data.min_price=Number(data.min_price);
 if(!data.max_price)delete data.max_price;
 else data.max_price=Number(data.max_price);
 if(!data.category)delete data.category;
 data.radius_km=Number(data.radius_km);data.latitude=9.6477;data.longitude=123.8547;data.confirm_paid_scan=true;
 logActivity('scan_submission_attempted',{terms:activeScanPlan.terms,requests:activeScanPlan.requests,keyword:data.keyword||'(blank)',category:data.category||'All',minPrice:data.min_price??null,maxPrice:data.max_price??null,radiusKm:data.radius_km,sort:data.sort_by,area:'Bohol'});
 const outcome=await request('/marketplace/scan','POST',data);
 logActivity('scan_completed',{keyword:data.keyword||'(blank)',rawCount:outcome.diagnostics?.raw_count??outcome.raw_count,
  normalized:outcome.diagnostics?.normalized_count??outcome.returned,
  archived:outcome.diagnostics?.archived_count??outcome.archived_count,
  new:outcome.new_count,previouslySeen:outcome.previously_seen_count,terms:outcome.planned_terms,failedTerms:outcome.failed_terms,
  bohol:outcome.diagnostics?.bohol_count??null,
  outside:outcome.diagnostics?.outside_count??null,unverified:outcome.diagnostics?.unverified_count??null});
 status.textContent=outcome.completed_requests+' keyword request(s) completed: '+outcome.raw_count+' raw items, '+outcome.returned+' unique normalized listings, '+(outcome.diagnostics?.bohol_count??'?')+' with Bohol location evidence ('+outcome.new_count+' newly archived, '+outcome.previously_seen_count+' previously known). '+(outcome.failed_terms.length?'Failed terms: '+outcome.failed_terms.join(', ')+'. ':'')+'The cards show only the matching local sale listings, not every raw provider item.';
 // Keep visible filters aligned with the scan just requested, not older
 // browser preferences that could hide every result from the new batch.
 category=data.category||'';
 document.getElementById('search').value=data.keyword||'';
 document.getElementById('minPrice').value=data.min_price??'';
 document.getElementById('budget').value=data.max_price??'';
 document.getElementById('foundWithinDays').value='';
 document.getElementById('sort').value=data.sort_by==='price_ascend'?'price_low':data.sort_by==='price_descend'?'price_high':'newest';
 document.querySelectorAll('[data-category]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category===category)));
 lastScanBatch=outcome.scan_batch;
 await loadListings();
 document.getElementById('listingHeading').scrollIntoView({behavior:'smooth',block:'start'});
 }catch(error){
  status.textContent=error.message;
  if(error.status===429){
   logActivity('scan_rate_limited',{reason:'Provider or infrastructure rate limit',retryAfterSeconds:error.retryAfter||60});
  }else{
   logActivity('scan_failed',{message:String(error.message||'Unknown error').slice(0,180)});
  }
  // Keep the user on Find deals; logs remain available from the sidebar.
  // Revealing the failure next to the scan form avoids an unexpected page switch.
  document.getElementById('scanSettings').open=true;
  status.setAttribute('role','alert');
  status.scrollIntoView({behavior:'smooth',block:'nearest'});
 }
 finally{scanInFlight=false;btn.disabled=false;btn.textContent='Run approved searches';document.getElementById('paidScan').checked=false}
});
restoreFilters();
loadListings();
</script>
</body></html>
