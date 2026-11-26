<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if($testerMode ?? false)
<meta name="robots" content="noindex,nofollow">
@endif
<script>
// Canonical browser address: old 127.0.0.1 bookmarks are moved to localhost.
// Keep the original path, query and hash; the dashboard later converts #views
// to proper Laravel page routes. No provider calls or local data changes.
if (location.hostname === '127.0.0.1' && location.port === '8000') {
  location.replace('http://localhost:8000' + location.pathname + location.search + location.hash);
}
</script>
<title>Marketplace Finder — Find better deals</title>
<style>
:root{font:15px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color-scheme:dark;--bg:#090f1c;--side:#0d1627;--panel:#121e31;--panel2:#19263a;--line:#2b3b52;--text:#f5f7fd;--muted:#a5b2c5;--orange:#ffb54d;--mint:#88e5c1;--red:#ff96a2}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text)}button,input,select{font:inherit}button{cursor:pointer}a{color:inherit}button:disabled{opacity:.6;cursor:wait}button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,summary:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
.app{display:grid;grid-template-columns:230px minmax(0,1fr);min-height:100vh}.aside{background:var(--side);padding:26px 14px;border-right:1px solid var(--line);display:flex;flex-direction:column;gap:28px}.logo{font-size:20px;font-weight:850;letter-spacing:-.8px;padding:0 10px}.logo b{color:var(--orange)}.tagline{padding:0 10px;color:var(--muted);font-size:12px}.menu{display:grid;gap:6px}.menu a{display:block;width:100%;border:0;text-align:left;text-decoration:none;background:transparent;color:var(--muted);padding:12px 15px;border-radius:12px}.menu a[aria-current=page],.menu a:hover{background:#283044;color:var(--orange)}.asidefoot{margin-top:auto;color:var(--muted);font-size:12px;padding:10px}.asidefoot a{color:var(--orange)}
main{width:100%;max-width:1190px;margin:0 auto;padding:37px 40px 75px}.view[hidden]{display:none!important}.eyebrow{text-transform:uppercase;letter-spacing:1.5px;font-weight:800;font-size:12px;color:var(--orange)}h1{font-size:clamp(30px,4vw,43px);line-height:1.15;letter-spacing:-1.2px;margin:10px 0}h2{font-size:22px;margin:0}h3{font-size:17px;margin:0 0 7px}p{color:var(--muted);margin:8px 0 18px}.intro{margin-bottom:26px}.intro p{font-size:15px}.status{font-size:12px;color:var(--mint);background:#15332f;border:1px solid #285e51;border-radius:50px;padding:7px 12px;display:inline-block}.headrow{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}
.btn{border:0;border-radius:12px;background:var(--orange);color:#201508;padding:11px 18px;font-weight:800;white-space:nowrap}.btn.alt{background:#283951;color:var(--text);border:1px solid #40516b}.btn.subtle{background:transparent;color:var(--muted);border:1px solid var(--line)}.btn.sm{font-size:12px;padding:8px 12px}.btn:hover:not(:disabled){filter:brightness(1.07)}
.panel{background:var(--panel);border:1px solid var(--line);border-radius:19px;padding:22px}.searchbox{padding:19px;background:linear-gradient(120deg,#1b2c46,#172238);border:1px solid #384963;border-radius:19px}.searchrow{display:grid;grid-template-columns:minmax(0,2fr) minmax(130px,1fr) minmax(145px,1fr) auto;gap:11px;align-items:end}.filterrow{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;margin-top:13px}.filterrow label{min-width:0}.filter-summary{padding:10px 1px 0;color:var(--muted);font-size:12px}.filter-toggle{border:0;background:transparent;padding:9px 3px;color:var(--orange);font-weight:700;font-size:13px}.extra-filters[hidden]{display:none}.searchbox .chip{margin-bottom:2px}label{display:block;color:var(--muted);font-size:12px;font-weight:600}input,select{display:block;width:100%;min-width:0;border-radius:11px;border:1px solid #42526b;background:#101c2e;color:var(--text);padding:11px 12px;margin-top:6px}input::placeholder{color:#8190a9}.quicklinks{display:flex;gap:8px;flex-wrap:wrap;margin:15px 0 0}.chip{border:1px solid #40516a;border-radius:50px;padding:8px 13px;background:#1e2b42;color:#d0d9e9;font-size:12px}.chip[aria-pressed=true]{background:#3b3228;color:var(--orange);border-color:#b77d35}.muted{color:var(--muted);font-size:12px}.sectiontop{margin:27px 0 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}.stack{display:grid;gap:13px}.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.listing{background:var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}.listing-art{height:190px;display:grid;place-items:center;background:linear-gradient(130deg,#263b57,#1b263c);font-size:32px;overflow:hidden;position:relative}.listing-art img{width:100%;height:100%;object-fit:cover;display:block}.listing-art .photo-fallback{font-size:35px}.listing-art .photo-label{position:absolute;bottom:7px;right:9px;background:#101a2ad9;color:#b9c6d8;border-radius:6px;padding:2px 6px;font-size:10px}.listing-info{padding:15px;display:flex;flex-direction:column;flex:1}.listing h3{font-size:16px;margin:7px 0}.listing-price{font-size:24px;font-weight:850;letter-spacing:-.5px;margin:8px 0}.listing-actions{display:flex;gap:8px;margin-top:auto;padding-top:12px}.pill{font-size:11px;color:var(--mint);font-weight:700}.empty{padding:30px 24px;border:1px dashed #44536b;border-radius:16px;text-align:center;background:var(--panel)}
/* NOTE: This is a truncated version for the tool call. The full polished file is in the sandbox. */
</style>
</head>
<body>
<p>This is a temporary truncated version. Please wait for the full file push.</p>
</body>
</html>