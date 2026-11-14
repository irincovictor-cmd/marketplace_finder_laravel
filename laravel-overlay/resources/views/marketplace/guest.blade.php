<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Marketplace Finder — Bohol deals</title>
<style>
:root{font:15px/1.5 system-ui,Segoe UI,Arial,sans-serif;color-scheme:dark;--bg:#09111f;--panel:#16243a;--border:#35465e;--text:#f6f7fa;--muted:#bac5d5;--accent:#ffb84d}
*{box-sizing:border-box}body{background:var(--bg);color:var(--text);margin:0}main{max-width:1140px;margin:auto;padding:24px 17px 65px}
.top{display:flex;justify-content:space-between;gap:16px;align-items:center}.brand{font-size:20px;font-weight:850}.brand span{color:var(--accent)}.label{color:#99dcc9;font-size:12px}
h1{font-size:clamp(27px,5vw,43px);margin:36px 0 6px;line-height:1.12}p{color:var(--muted)}
.panel{background:var(--panel);border:1px solid var(--border);padding:18px;border-radius:16px}
form{display:grid;grid-template-columns:2fr repeat(3,minmax(110px,1fr)) auto;gap:11px;align-items:end}
label{font-size:12px;color:var(--muted)}input,select,button{font:inherit;min-width:0}
input,select{display:block;background:#101b2d;border:1px solid #41516b;color:white;width:100%;border-radius:10px;padding:11px;margin-top:5px}
button{border:0;border-radius:10px;padding:12px 16px;background:var(--accent);font-weight:750;color:#251805;cursor:pointer}
button:disabled{opacity:.5;cursor:default}
#status{font-size:13px;min-height:25px;margin:17px 0}
.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
.card{overflow:hidden;border:1px solid var(--border);background:var(--panel);border-radius:15px;display:flex;flex-direction:column}
.photo{width:100%;aspect-ratio:4/3;object-fit:cover;background:#27344c}
.fallback{display:grid;place-items:center;font-size:12px;color:var(--muted)}
.body{padding:14px;display:flex;flex:1;flex-direction:column;gap:8px}
.body h2{font-size:16px;line-height:1.3;margin:0;overflow-wrap:anywhere}
.price{font-size:20px;font-weight:850}.meta{font-size:12px;color:var(--muted)}
.view{display:inline-block;text-decoration:none;background:#283d57;color:#fff;padding:8px 12px;border-radius:9px;font-size:13px;font-weight:700;align-self:flex-start;margin-top:auto}
.pager{display:flex;justify-content:center;gap:12px;align-items:center;padding:24px}
.foot{margin-top:20px;font-size:12px;color:var(--muted)}
@media(max-width:820px){form{grid-template-columns:repeat(2,minmax(0,1fr))}form label:first-child{grid-column:1/-1}.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){main{padding:18px 12px 40px}.cards{grid-template-columns:1fr}form{grid-template-columns:1fr}form label:first-child{grid-column:auto}.top{align-items:flex-start;flex-direction:column}}
/* Phone experience: readable controls, touch targets and reliable visibility */
[hidden]{display:none!important}
body{overflow-x:hidden}
main,.panel,.card,.body{min-width:0}
input,select,button,.view{min-height:44px;touch-action:manipulation}
input,select{font-size:16px}
.view{display:inline-flex;align-items:center;justify-content:center;text-align:center}
#status{line-height:1.5}
@media(max-width:820px){
 form{grid-template-columns:repeat(2,minmax(0,1fr))}
 form label:first-child{grid-column:1/-1}
 form button{grid-column:1/-1}
 .cards{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:520px){
 main{padding:17px 12px 42px}
 h1{margin-top:25px}
 .panel{padding:14px}
 form{grid-template-columns:minmax(0,1fr)}
 form label:first-child,form button{grid-column:auto}
 .cards{grid-template-columns:minmax(0,1fr);gap:12px}
 .photo{aspect-ratio:16/10}
 .body{padding:14px}
 .view{width:100%}
 .pager{gap:7px;flex-wrap:wrap}
 .pager button{flex:1 1 110px}
 #pageText{order:-1;width:100%;text-align:center}
 .top{align-items:flex-start;flex-direction:column;gap:4px}
}
</style>
</head>
<body>
<main>
<header class="top"><div class="brand">marketplace<span>finder.</span></div><div class="label">Bohol deals · Guest browsing</div></header>
<h1>Discover Bohol deals</h1>
<p>Browse collected Facebook Marketplace listings. This public view is read-only; availability and prices are not guaranteed.</p>
<section class="panel">
<form id="filterForm">
<label>Search by model or keyword<input id="keyword" maxlength="100" placeholder="e.g. Redmi, laptop, camera"></label>
<label>Category<select id="category">
<option value="">All</option><option value="Electronics">Electronics</option><option>Phones</option><option>Laptops</option><option>Cameras</option><option>PC Parts</option><option>Consoles</option><option>Other</option>
</select></label>
<label>Minimum price (₱)<input id="min" inputmode="numeric" placeholder="0"></label>
<label>Maximum price (₱)<input id="max" inputmode="numeric" placeholder="Any"></label>
<button type="submit">Search deals</button>
</form>
</section>
<p id="status" role="status">Loading listings…</p>
<section id="cards" class="cards" aria-live="polite"></section>
<div class="pager" id="pager" hidden>
<button id="previous" type="button">Previous</button><span id="pageText"></span><button id="next" type="button">Next</button>
</div>
<p class="foot">Listings are archived from earlier searches. This page cannot run Apify scans, edit saved items, or access API-key settings. Verify each item directly with its seller.</p>
</main>
<script>
const q=id=>document.getElementById(id);
let page=1,pages=1;
const cash=value=>'₱'+Number(value||0).toLocaleString('en-PH',{minimumFractionDigits:0,maximumFractionDigits:2});
const safeUrl=raw=>{try{const u=new URL(raw);return u.protocol==='https:'&&(u.hostname==='facebook.com'||u.hostname.endsWith('.facebook.com'))?u.href:null}catch{return null}};
const safePhoto=raw=>{try{const u=new URL(raw);return u.protocol==='https:'&&(u.hostname==='fbcdn.net'||u.hostname.endsWith('.fbcdn.net')||u.hostname==='fbsbx.com'||u.hostname.endsWith('.fbsbx.com'))?u.href:null}catch{return null}};
function el(name,text,className){const n=document.createElement(name);if(text!==undefined)n.textContent=text;if(className)n.className=className;return n}
function renderItem(item){
 const card=el('article',undefined,'card');
 const photo=safePhoto(item.image_url);
 if(photo){const img=el('img');img.src=photo;img.alt='Marketplace listing photo';img.loading='lazy';img.referrerPolicy='no-referrer';img.className='photo';card.append(img)}
 else card.append(el('div','Listing photo unavailable','photo fallback'));
 const body=el('div',undefined,'body');body.append(el('div',item.category||'Other','meta'),el('h2',item.title),el('div',cash(item.price),'price'),el('div',(item.city||'Location unspecified')+' · '+(item.manual_location?'Location user-reviewed':'Bohol listing metadata'),'meta'));
 const link=safeUrl(item.url);
 if(link){const a=el('a','View on Facebook','view');a.href=link;a.target='_blank';a.rel='noopener noreferrer';body.append(a)}
 card.append(body);return card
}
async function load(){
 const search=new URLSearchParams({page:String(page),per_page:'24',sort:'newest'});
 const keyword=q('keyword').value.trim();if(keyword)search.set('q',keyword);
 const category=q('category').value;if(category)search.set('category',category);
 for(const [field,input] of [['min_price','min'],['max_price','max']]){const val=q(input).value.replace(/,/g,'').trim();if(val)search.set(field,val)}
 q('status').textContent='Loading saved Bohol listings…';q('cards').replaceChildren();q('pager').hidden=true;
 try{
  const res=await fetch('/listings?'+search,{headers:{'Accept':'application/json'},credentials:'omit',cache:'no-store'});
  if(!res.ok)throw Error('Unable to load listings ('+res.status+').');
  const data=await res.json();pages=data.pages||1;
  q('status').textContent=data.total+' matching Bohol listing(s) in the archive. No fresh Facebook scan was made.';
  for(const item of data.items)q('cards').append(renderItem(item));
  if(!data.items.length)q('cards').append(el('p','No listings match these filters. Try a broader category or price range.'));
  q('pager').hidden=pages<=1;q('pageText').textContent='Page '+data.page+' of '+pages;
  q('previous').disabled=page<=1;q('next').disabled=page>=pages;
 }catch(e){q('status').textContent=e.message}
}
q('filterForm').addEventListener('submit',e=>{e.preventDefault();page=1;load()});
q('previous').addEventListener('click',()=>{if(page>1){page--;load()}});
q('next').addEventListener('click',()=>{if(page<pages){page++;load()}});
load();
</script>
</body></html>