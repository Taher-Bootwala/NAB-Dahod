<?php
require __DIR__ . '/../app/bootstrap.php';
$cats = $repo->activityCategories();
$activities = $repo->activities();
$page = ['title' => 'Activities', 'nav' => 'activities', 'desc' => 'Sports, Braille learning, cultural events, computer education and skill workshops at National Association for the Blind Dahod.'];
require APP_DIR . '/layout/header.php';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('sparkle','w-4 h-4') ?> Life at our school</span>
      <h1 style="margin-top:1rem">Activities & <span class="grad-text">Programs</span></h1>
      <p class="lead" style="margin-inline:auto">From beep-ball cricket to coding by ear — explore what makes every day here extraordinary.</p>
    </div>

    <!-- Search + filters -->
    <div class="clay reveal" style="padding:1rem 1.2rem;margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;justify-content:space-between">
      <label style="flex:1;min-width:220px;display:flex;align-items:center;gap:.6rem;background:var(--bg-soft);border-radius:12px;padding:.2rem .9rem">
        <?= icon('search','w-5 h-5',['style'=>'color:var(--ink-mute)']) ?>
        <span class="sr-only">Search activities</span>
        <input id="actSearch" type="search" placeholder="Search activities…" class="field" style="border:none;background:transparent;padding:.7rem 0">
      </label>
      <div id="actFilters" style="display:flex;gap:.4rem;flex-wrap:wrap" role="group" aria-label="Filter by category">
        <button class="amount-chip active" data-cat="All" style="padding:.5rem 1rem;font-size:.9rem">All</button>
        <?php foreach ($cats as $c): ?>
          <button class="amount-chip" data-cat="<?= e($c) ?>" style="padding:.5rem 1rem;font-size:.9rem"><?= e($c) ?></button>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center" role="group" aria-label="Filter by date">
        <label style="display:flex;align-items:center;gap:.4rem;color:var(--ink-mute);font-size:.85rem;font-weight:600">Date
          <input id="actDate" type="date" class="field" style="padding:.5rem .7rem" aria-label="Filter by date">
        </label>
        <button id="actDateClear" type="button" class="btn btn-ghost" style="padding:.5rem .9rem;font-size:.85rem">Clear date</button>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div id="actGrid" class="auto-grid" data-stagger aria-live="polite">
      <?php foreach ($activities as $a): ?>
        <a href="/activity/<?= e($a['slug'] ?? $a['id']) ?>" class="clay card-hover reveal act-card" style="overflow:hidden;text-decoration:none;color:inherit">
          <div style="aspect-ratio:16/10;overflow:hidden;background:var(--bg-soft)">
            <img src="<?= attr($a['images'][0] ?? '') ?>" alt="<?= attr($a['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div style="padding:1.3rem">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
              <span class="badge"><?= e($a['category']) ?></span>
              <span class="muted" style="font-size:.82rem"><?= e(fmt_date($a['date'])) ?></span>
            </div>
            <h3 style="margin:.6rem 0 .4rem;font-size:1.2rem"><?= e($a['title']) ?></h3>
            <p style="font-size:.95rem"><?= e(excerpt($a['description'], 100)) ?></p>
            <span style="display:inline-flex;align-items:center;gap:.3rem;color:var(--brand-700);font-weight:700;margin-top:.6rem;font-size:.92rem">Read more <?= icon('arrow-right','w-4 h-4') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <p id="actEmpty" class="center muted" style="display:none;padding:3rem">No activities match your search.</p>
  </div>
</section>

<template id="skelCard">
  <div class="clay" style="overflow:hidden">
    <div class="skeleton" style="aspect-ratio:16/10"></div>
    <div style="padding:1.3rem"><div class="skeleton" style="height:14px;width:40%;margin-bottom:.8rem"></div><div class="skeleton" style="height:20px;width:80%;margin-bottom:.6rem"></div><div class="skeleton" style="height:40px;width:100%"></div></div>
  </div>
</template>

<script>
(() => {
  const grid = document.getElementById('actGrid');
  const empty = document.getElementById('actEmpty');
  const search = document.getElementById('actSearch');
  const filters = document.getElementById('actFilters');
  const dateInp = document.getElementById('actDate');
  const dateClear = document.getElementById('actDateClear');
  let cat = 'All', q = '', date = '', t;

  function skeletons(n=6){ const tpl=document.getElementById('skelCard'); grid.innerHTML='';
    for(let i=0;i<n;i++) grid.appendChild(tpl.content.cloneNode(true)); }

  function card(a){
    const img = (a.images && a.images[0]) || '';
    const d = new Date(a.date).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
    const ex = (a.description||'').replace(/<[^>]*>/g,'').slice(0,100);
    const el = document.createElement('a');
    el.href = '/activity/' + (a.slug || a.id);
    el.className = 'clay card-hover reveal is-visible';
    el.style.cssText = 'overflow:hidden;text-decoration:none;color:inherit';
    el.innerHTML = `<div style="aspect-ratio:16/10;overflow:hidden;background:var(--bg-soft)"><img src="${img}" alt="${a.title}" loading="lazy" style="width:100%;height:100%;object-fit:cover"></div>
      <div style="padding:1.3rem"><div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem"><span class="badge">${a.category}</span><span class="muted" style="font-size:.82rem">${d}</span></div>
      <h3 style="margin:.6rem 0 .4rem;font-size:1.2rem">${a.title}</h3><p style="font-size:.95rem">${ex}…</p></div>`;
    return el;
  }

  async function load(){
    skeletons();
    try{
      const url = `/api/content.php?type=activities&category=${encodeURIComponent(cat)}&search=${encodeURIComponent(q)}`;
      const r = await fetch(url); const data = await r.json();
      let items = data.items || [];
      if (date) items = items.filter(a => (a.date||'').slice(0,10) === date);
      grid.innerHTML='';
      if(!items.length){ empty.style.display='block'; return; }
      empty.style.display='none';
      items.forEach(a => grid.appendChild(card(a)));
    }catch{ grid.innerHTML='<p class="center muted">Could not load activities.</p>'; }
  }

  filters.addEventListener('click', e=>{ const b=e.target.closest('[data-cat]'); if(!b)return;
    filters.querySelectorAll('[data-cat]').forEach(x=>x.classList.remove('active')); b.classList.add('active'); cat=b.dataset.cat; load(); });
  search.addEventListener('input', ()=>{ clearTimeout(t); t=setTimeout(()=>{ q=search.value.trim(); load(); }, 300); });
  dateInp.addEventListener('change', ()=>{ date=dateInp.value; load(); });
  dateClear.addEventListener('click', ()=>{ date=''; dateInp.value=''; load(); });
})();
</script>

<?php require APP_DIR . '/layout/footer.php'; ?>
