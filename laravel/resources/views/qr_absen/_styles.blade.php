<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#F2F5FA; --surface:#FFFFFF; --ink:#16223A; --muted:#5D6B85; --line:#DDE3EE;
  --brand:#2456C8; --brand-ink:#FFFFFF;
  --ok:#17805A; --ok-soft:#E1F4EC; --warn:#8A5A00; --warn-soft:#FDF0D5;
  --err:#B3261E; --err-soft:#FCE4E2; --pencil:#F5B82E;
}
@media (prefers-color-scheme: dark){
  :root{
    --bg:#F2F5FA; --surface:#FFFFFF; --ink:#16223A; --muted:#5D6B85; --line:#DDE3EE;
    --brand:#6C93F2; --brand-ink:#0F1626;
    --ok:#4CC79A; --ok-soft:#173A31; --warn:#F2C25B; --warn-soft:#3B2F14;
    --err:#FF8A80; --err-soft:#3E1D1B;
  }
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;font-size:15px;line-height:1.5}
button,input,select{font:inherit;color:inherit}
a{color:var(--brand)}
:focus-visible{outline:3px solid var(--brand);outline-offset:2px}
.wrap{max-width:1120px;margin:0 auto;padding:28px 20px 56px}
.crumb{font-size:13px;color:var(--muted);margin-bottom:6px}
h1{margin:0;font-size:26px;font-weight:800;letter-spacing:-.01em}
.lead{margin:4px 0 22px;color:var(--muted);max-width:56ch}
.panel{background:var(--surface);border:1px solid var(--line);border-radius:14px}
.sub{font-size:13px;color:var(--muted)}
.nm{font-weight:600}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--line);background:var(--surface);border-radius:10px;padding:9px 14px;font-weight:600;cursor:pointer;min-height:40px;text-decoration:none;color:var(--ink)}
.btn:hover{border-color:var(--brand)}
.btn.primary{background:var(--brand);color:var(--brand-ink);border-color:var(--brand)}
.btn.warn{background:var(--warn-soft);color:var(--warn);border-color:transparent}
.btn.small{padding:6px 12px;min-height:34px;font-size:14px}
.btn[disabled]{opacity:.5;cursor:not-allowed}

.flash{padding:12px 16px;border-radius:12px;margin-bottom:14px;font-weight:600}
.flash.ok{background:var(--ok-soft);color:var(--ok)}
.flash.err{background:var(--err-soft);color:var(--err)}

.progress{padding:18px 20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:16px}
.progress .txt{flex:1;min-width:220px}
.bar{height:8px;background:var(--line);border-radius:99px;overflow:hidden;margin-top:10px}
.bar > i{display:block;height:100%;background:var(--brand);border-radius:99px}

.tools{display:flex;gap:10px;flex-wrap:wrap;padding:14px 16px;border-bottom:1px solid var(--line)}
.tools input,.tools select{background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:8px 12px;min-height:40px}
.tools input{flex:1;min-width:160px}
.tablewrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:560px}
th{text-align:left;font-size:13px;font-weight:600;color:var(--muted);padding:10px 16px;border-bottom:1px solid var(--line)}
td{padding:12px 16px;border-bottom:1px solid var(--line);vertical-align:middle}
tr:last-child td{border-bottom:0}
td.act{text-align:right;white-space:nowrap}
td.act form{display:inline}
.thumb{width:48px;height:48px;background:#fff;border:1px solid var(--line);border-radius:8px;padding:3px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.thumb svg{width:100%;height:100%}
.pill{display:inline-block;font-size:13px;font-weight:600;border-radius:99px;padding:3px 10px;background:var(--warn-soft);color:var(--warn)}
.empty{padding:28px 16px;text-align:center;color:var(--muted)}
.pagination{display:flex;gap:6px;list-style:none;padding:14px 16px;margin:0;flex-wrap:wrap}
.pagination li a,.pagination li span{display:inline-block;padding:6px 12px;border:1px solid var(--line);border-radius:8px;text-decoration:none;font-size:14px}
.pagination li.active span{background:var(--brand);color:var(--brand-ink);border-color:var(--brand)}
.pagination li.disabled span{opacity:.45}
</style>
