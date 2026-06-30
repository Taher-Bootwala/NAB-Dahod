<?php
require __DIR__ . '/../app/bootstrap.php';
$cats = $repo->galleryCategories();
$photos = $repo->gallery();
$page = ['title' => 'Gallery', 'nav' => 'gallery', 'desc' => 'Photo gallery of school activities, annual events, classroom learning, sports and festivals at National Association for the Blind Dahod.'];
require APP_DIR . '/layout/header.php';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('image','w-4 h-4') ?> Moments</span>
      <h1 style="margin-top:1rem">Photo <span class="grad-text">Gallery</span></h1>
      <p class="lead" style="margin-inline:auto">A window into everyday life, learning and celebration at our school.</p>
    </div>
    <label class="reveal" style="max-width:460px;margin:2rem auto 0;display:flex;align-items:center;gap:.6rem;background:var(--bg-soft);border-radius:12px;padding:.2rem .9rem">
      <?= icon('search','w-5 h-5',['style'=>'color:var(--ink-mute)']) ?>
      <span class="sr-only">Search photos</span>
      <input id="galSearch" type="search" placeholder="Search photos…" class="field" style="border:none;background:transparent;padding:.7rem 0">
    </label>
    <div id="galFilters" class="reveal" style="display:flex;gap:.4rem;flex-wrap:wrap;justify-content:center;margin-top:1rem" role="group" aria-label="Filter photos by category">
      <button class="amount-chip active" data-cat="All" style="padding:.5rem 1rem;font-size:.9rem">All</button>
      <?php foreach ($cats as $c): ?>
        <button class="amount-chip" data-cat="<?= e($c) ?>" style="padding:.5rem 1rem;font-size:.9rem"><?= e($c) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div id="galGrid" class="masonry" aria-live="polite">
      <?php foreach ($photos as $g): ?>
        <figure class="reveal card-hover gal-item" style="margin:0;position:relative;border-radius:18px;overflow:hidden;cursor:zoom-in;box-shadow:var(--shadow-card)"
                data-lightbox="<?= attr($g['image_url']) ?>" data-caption="<?= attr($g['title']) ?> — <?= attr(fmt_date($g['date'])) ?>">
          <img src="<?= attr($g['image_url']) ?>" alt="<?= attr($g['title']) ?>" loading="lazy" style="width:100%;display:block">
          <figcaption style="position:absolute;inset:auto 0 0 0;padding:1rem;background:linear-gradient(transparent,rgba(8,14,28,.8));color:#fff">
            <strong style="display:block"><?= e($g['title']) ?></strong>
            <span style="font-size:.82rem;opacity:.85"><?= e($g['category']) ?> · <?= e(fmt_date($g['date'])) ?></span>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <p id="galEmpty" class="center muted" style="display:none;padding:3rem">No photos match your search.</p>
  </div>
</section>

<script>
(() => {
  const grid = document.getElementById('galGrid');
  const empty = document.getElementById('galEmpty');
  const filters = document.getElementById('galFilters');
  const search = document.getElementById('galSearch');
  let cat='All', q='', t;
  function skel(n=8){ grid.innerHTML=''; for(let i=0;i<n;i++){ const d=document.createElement('div'); d.className='skeleton'; d.style.cssText='height:'+(160+((i*37)%120))+'px;border-radius:18px'; grid.appendChild(d);} }
  function item(g){
    const d = new Date(g.date).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
    const f=document.createElement('figure'); f.className='reveal is-visible card-hover gal-item';
    f.style.cssText='margin:0;position:relative;border-radius:18px;overflow:hidden;cursor:zoom-in;box-shadow:var(--shadow-card)';
    f.dataset.lightbox=g.image_url; f.dataset.caption=`${g.title} — ${d}`;
    f.innerHTML=`<img src="${g.image_url}" alt="${g.title}" loading="lazy" style="width:100%;display:block">
      <figcaption style="position:absolute;inset:auto 0 0 0;padding:1rem;background:linear-gradient(transparent,rgba(8,14,28,.8));color:#fff"><strong style="display:block">${g.title}</strong><span style="font-size:.82rem;opacity:.85">${g.category} · ${d}</span></figcaption>`;
    return f;
  }
  async function load(){ skel();
    try{ const r=await fetch('/api/content.php?type=gallery&category='+encodeURIComponent(cat)); const data=await r.json();
      let items=data.items||[];
      if(q){ const ql=q.toLowerCase(); items=items.filter(g=>(g.title||'').toLowerCase().includes(ql)||(g.category||'').toLowerCase().includes(ql)); }
      grid.innerHTML=''; if(!items.length){empty.style.display='block';return;} empty.style.display='none';
      items.forEach(g=>grid.appendChild(item(g)));
    }catch{ grid.innerHTML='<p class="center muted">Could not load gallery.</p>'; }
  }
  filters.addEventListener('click',e=>{const b=e.target.closest('[data-cat]');if(!b)return;
    filters.querySelectorAll('[data-cat]').forEach(x=>x.classList.remove('active'));b.classList.add('active');cat=b.dataset.cat;load();});
  search.addEventListener('input',()=>{ clearTimeout(t); t=setTimeout(()=>{ q=search.value.trim(); load(); },300); });
})();
</script>

<?php require APP_DIR . '/layout/footer.php'; ?>
