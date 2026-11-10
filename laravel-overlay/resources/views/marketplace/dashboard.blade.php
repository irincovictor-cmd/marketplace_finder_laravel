<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Marketplace Finder | Discover smarter</title>
<style>
:root{color-scheme:dark;--bg:#0a0e1b;--panel:#151e30;--panel2:#101829;--line:#2c3850;--text:#f3f6ff;--muted:#9aa8c0;--accent:#ffae42;--green:#67d8a8;--red:#ff929b;font:15px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--text)}a{color:inherit}button,input,select{font:inherit}button{cursor:pointer}
.shell{min-height:100vh;display:grid;grid-template-columns:220px minmax(0,1fr)}
.sidebar{background:#0e1422;border-right:1px solid var(--line);padding:28px 17px;display:flex;flex-direction:column}
.brand{font-size:20px;letter-spacing:-.7px;font-weight:850}.brand b{color:var(--accent)}.sub{color:var(--muted);font-size:12px;margin:2px 0 35px}
.nav{display:grid;gap:7px}.nav a{padding:12px 13px;text-decoration:none;border-radius:12px;color:var(--muted)}.nav a.active,.nav a:hover{background:#282537;color:var(--accent)}.bottom{margin-top:auto;padding:20px 7px;color:var(--muted);font-size:12px}
main{max-width:1220px;width:100%;margin:0 auto;padding:34px 40px 80px}.eyebrow{color:var(--accent);text-transform:uppercase;font-size:12px;font-weight:800;letter-spacing:1.6px}h1{font-size:clamp(30px,4vw,47px);letter-spacing:-1.5px;line-height:1.12;margin:8px 0 8px}p{color:var(--muted)}.intro{display:flex;justify-content:space-between;gap:20px;align-items:start}.flag{border:1px solid #365b50;border-radius:50px;background:#16312b;color:var(--green);padding:9px 12px;font-size:12px;white-space:nowrap}
.hero{display:flex;align-items:center;justify-content:space-between;gap:20px;border:1px solid #36425d;border-radius:20px;background:linear-gradient(115deg,#222d49,#28243a);padding:24px;margin:24px 0}.hero h2{margin:0;font-size:21px}.hero p{margin:6px 0 0}.btn{background:var(--accent);border:0;color:#23180b;font-weight:800;border-radius:11px;padding:12px 16px;text-decoration:none;display:inline-block}.btn:hover{filter:brightness(1.08)}.btn.secondary{background:#26334a;color:var(--text);border:1px solid #46536b}.filters{display:grid;grid-template-columns:minmax(0,2fr) repeat(2,minmax(0,1fr)) auto;gap:10px;background:var(--panel2);padding:16px;border:1px solid var(--line);border-radius:16px}input,select{width:100%;background:#1a253a;border:1px solid #39465d;color:var(--text);padding:11px 12px;border-radius:10px}input:focus,select:focus,button:focus-visible,a:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
.header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:27px 0 13px}.header h2{font-size:21px;margin:0}.muted{font-size:12px;color:var(--muted)}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.card{background:var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden}.picture{height:140px;background:radial-gradient(circle at 55% 110%,#475a7b,#1b2c43 65%);display:grid;place-items:center;font-size:53px}.content{padding:16px}.tag{color:var(--green);font-size:11px;font-weight:800;text-transform:uppercase}.content h3{font-size:16px;margin:8px 0 4px}.content p{font-size:12px;margin:0}.price{font-size:23px;letter-spacing:-.5px;font-weight:850;margin:12px 0}.cardfoot{border-top:1px solid var(--line);padding-top:10px;color:var(--muted);font-size:12px}.split{display:grid;grid-template-columns:1fr 1fr;gap:16px}.box{background:var(--panel2);border:1px solid var(--line);border-radius:16px;padding:20px}.fields{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:14px 0}.fields label{color:var(--muted);font-size:12px}.fields input{margin-top:6px}.result{display:flex;gap:30px;align-items:center;flex-wrap:wrap;margin-top:14px}.metric strong{display:block;font-size:28px}.metric span{color:var(--muted);font-size:12px}.error{color:var(--red);font-size:13px;min-height:22px}.note{color:var(--muted);font-size:12px}.empty{border:1px dashed var(--line);padding:30px;border-radius:12px;color:var(--muted);grid-column:1/-1;text-align:center}
@media(max-width:1050px){.shell{display:block}.sidebar{border-right:0;border-bottom:1px solid var(--line);padding:13px 22px;display:block}.sub,.bottom{display:none}.nav{display:flex;overflow:auto;margin-top:10px}.nav a{white-space:nowrap;padding:8px 12px}main{padding:24px}.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:650px){.intro,.hero{display:block}.flag{display:inline-block;margin-top:10px}.hero .btn{margin-top:15px}.filters{grid-template-columns:1fr 1fr}.filters input,.filters button{grid-column:1/-1}.grid,.split{grid-template-columns:1fr}.fields{grid-template-columns:1fr}.header{align-items:start;flex-direction:column;gap:6px}.picture{height:120px}}
</style>
</head>
<body>
<div class="shell">
<aside class="sidebar">
<div class="brand">marketplace<b>finder.</b></div><div class="sub">Find deals. Flip smarter.</div>
<nav class="nav" aria-label="Main navigation">
<a class="active" href="#discover">⌕ &nbsp; Discover</a>
<a href="#calculator">▤ &nbsp; Profit calculator</a>
<a href="/flipradar/index.html">☆ &nbsp; Legacy watchlist</a>
<a href="/flipradar/index.html">♡ &nbsp; Legacy saved items</a>
</nav>
<div class="bottom">Laravel migration · Phase 2<br>Demo listings, no paid scanning</div>
</aside>
<main>
<header class="intro"><div><div class="eyebrow">A smarter way to find value</div><h1>Discover your next<br>great deal.</h1><p>Explore gadgets, compare budgets, and estimate resale profit.</p></div><span class="flag">● Laravel + PHP is live</span></header>
<section class="hero"><div><h2>Same FlipRadar mission. Better workspace.</h2><p>This is the new Laravel dashboard. The existing full scanner remains available during migration.</p></div><a class="btn" href="/flipradar/index.html">Open original scanner ↗</a></section>
<section id="discover" aria-labelledby="discover-heading">
<form id="filterForm" class="filters"><input id="query" aria-label="Search demo listings" placeholder="Search phones, laptops, consoles…"><select id="category" aria-label="Category"><option value="">All categories</option><option>Phones</option><option>Laptops</option><option>Consoles</option></select><select id="budget" aria-label="Maximum budget"><option value="100000000">Any budget</option><option value="5000">Below ₱5,000</option><option value="10000">Below ₱10,000</option><option value="20000">Below ₱20,000</option></select><button class="btn" type="submit">Filter</button></form>
<div class="header"><h2 id="discover-heading">Explore examples</h2><span class="muted" id="count">Demo data · not live marketplace results</span></div><div class="grid" id="listingGrid" aria-live="polite"></div>
</section>
<div class="header" id="calculator"><h2>Profit calculator</h2><span class="muted">Calculated by Laravel PHP · no API charges</span></div>
<div class="split">
<div class="box"><form id="calculatorForm">
<div class="fields">
<label>Buying price (₱)<input name="buying_price" type="number" inputmode="decimal" min=".01" step=".01" value="8000" required></label>
<label>Selling price (₱)<input name="selling_price" type="number" inputmode="decimal" min="0" step=".01" value="10000" required></label>
<label>Extra costs (₱)<input name="extra_costs" type="number" inputmode="decimal" min="0" step=".01" value="500" required></label>
</div><button class="btn" type="submit">Calculate using PHP</button><p id="calcError" role="alert" class="error"></p>
</form></div>
<div class="box" aria-live="polite"><div class="muted">Estimated profit</div><div class="result"><div class="metric"><strong id="profit">—</strong><span>Net return</span></div><div class="metric"><strong id="roi">—</strong><span>Return on investment</span></div></div><p class="note">Based only on your entered numbers. Not a verified resale price.</p></div>
</div>
<p class="note" style="margin-top:22px">Migration notice: Sample listing cards are illustrative only. Saved items, watchlist, live Apify scanning, and location filtering still run in the original FlipRadar interface. Their Laravel conversion is in progress.</p>
</main></div>
<script>
const demos=[
 {title:'iPhone 13 128GB',category:'Phones',price:12500,city:'Tagbilaran',icon:'📱'},
 {title:'POCO F7 256GB',category:'Phones',price:14800,city:'Dauis',icon:'📱'},
 {title:'Lenovo ThinkPad T480',category:'Laptops',price:11500,city:'Panglao',icon:'💻'},
 {title:'PlayStation 4 Slim',category:'Consoles',price:8500,city:'Tubigon',icon:'🎮'},
 {title:'Samsung Galaxy A54',category:'Phones',price:7200,city:'Tagbilaran',icon:'📱'},
 {title:'Acer Aspire 5',category:'Laptops',price:19500,city:'Talibon',icon:'💻'}
];
const money=n=>new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(n);
function renderListings(){
 const q=document.getElementById('query').value.toLowerCase().trim();
 const cat=document.getElementById('category').value, budget=Number(document.getElementById('budget').value);
 const items=demos.filter(i=>(!q||i.title.toLowerCase().includes(q)||i.city.toLowerCase().includes(q))&&(!cat||cat===i.category)&&i.price<=budget);
 const grid=document.getElementById('listingGrid');grid.replaceChildren();
 document.getElementById('count').textContent=items.length+' illustrative results · not live';
 if(!items.length){const empty=document.createElement('div');empty.className='empty';empty.textContent='No example listings match these filters.';grid.append(empty);return;}
 for(const item of items){
  const card=document.createElement('article');card.className='card';
  const picture=document.createElement('div');picture.className='picture';picture.textContent=item.icon;
  const content=document.createElement('div');content.className='content';
  const tag=document.createElement('div');tag.className='tag';tag.textContent='Sample · '+item.category;
  const name=document.createElement('h3');name.textContent=item.title;
  const location=document.createElement('p');location.textContent=item.city+', Bohol · example only';
  const price=document.createElement('div');price.className='price';price.textContent=money(item.price);
  const footer=document.createElement('div');footer.className='cardfoot';footer.textContent='No verified seller, link or market value';
  content.append(tag,name,location,price,footer);card.append(picture,content);grid.append(card);
 }
}
document.getElementById('filterForm').addEventListener('submit',e=>{e.preventDefault();renderListings()});
document.getElementById('query').addEventListener('input',renderListings);
document.getElementById('category').addEventListener('change',renderListings);
document.getElementById('budget').addEventListener('change',renderListings);
document.getElementById('calculatorForm').addEventListener('submit',async e=>{
 e.preventDefault();const form=e.currentTarget, error=document.getElementById('calcError');error.textContent='';
 const data=Object.fromEntries(new FormData(form));
 try{
 const response=await fetch('{{ route('marketplace.calculator') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(data)});
 if(!response.ok){const detail=await response.json().catch(()=>({}));throw Error(detail.message||'Calculation could not be completed');}
 const result=await response.json();
 document.getElementById('profit').textContent=money(result.profit);
 document.getElementById('roi').textContent=result.roi_percent.toFixed(2)+'%';
 }catch(err){error.textContent=err.message||'Request failed';document.getElementById('profit').textContent='—';document.getElementById('roi').textContent='—';}
});
renderListings();
</script>
</body></html>
