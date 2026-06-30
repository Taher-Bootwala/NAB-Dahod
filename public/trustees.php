<?php
require __DIR__ . '/../app/bootstrap.php';
$trustees = $repo->trustees();
$positions = array_values(array_unique(array_filter(array_map(fn($t) => trim($t['position'] ?? ''), $trustees))));
sort($positions);
$page = ['title' => 'Trustees', 'nav' => 'trustees', 'desc' => 'Meet the trustees and leadership guiding National Association for the Blind Dahod.'];
require APP_DIR . '/layout/header.php';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('users','w-4 h-4') ?> Leadership</span>
      <h1 style="margin-top:1rem">The people behind <span class="grad-text">the mission</span></h1>
      <p class="lead" style="margin-inline:auto">A dedicated board of trustees who give their time, expertise and hearts so that every child can learn.</p>
    </div>

    <!-- Search + position filter -->
    <div class="clay reveal" style="padding:1rem 1.2rem;margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;justify-content:space-between">
      <label style="flex:1;min-width:220px;display:flex;align-items:center;gap:.6rem;background:var(--bg-soft);border-radius:12px;padding:.2rem .9rem">
        <?= icon('search','w-5 h-5',['style'=>'color:var(--ink-mute)']) ?>
        <span class="sr-only">Search trustees</span>
        <input id="trSearch" type="search" placeholder="Search by name or position…" class="field" style="border:none;background:transparent;padding:.7rem 0">
      </label>
      <div id="trFilters" style="display:flex;gap:.4rem;flex-wrap:wrap" role="group" aria-label="Filter by position">
        <button class="amount-chip active" data-pos="All" style="padding:.5rem 1rem;font-size:.9rem">All</button>
        <?php foreach ($positions as $p): ?>
          <button class="amount-chip" data-pos="<?= e($p) ?>" style="padding:.5rem 1rem;font-size:.9rem"><?= e($p) ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div id="trGrid" class="auto-grid" data-stagger style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
      <?php foreach ($trustees as $t): ?>
        <article class="reveal tr-card" data-tilt data-name="<?= attr(mb_strtolower($t['name'] ?? '')) ?>" data-position="<?= attr($t['position'] ?? '') ?>" style="perspective:900px">
          <div class="glass tilt" style="padding:1.6rem;text-align:center;height:100%">
            <div class="tilt-lift" style="width:120px;height:120px;border-radius:30px;overflow:hidden;margin:0 auto 1rem;box-shadow:var(--shadow-card);background:var(--bg-soft)">
              <?php if (!empty($t['photo'])): ?>
                <img src="<?= attr($t['photo']) ?>" alt="Portrait of <?= attr($t['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
              <?php else: ?>
                <div style="width:100%;height:100%;display:grid;place-items:center;background:linear-gradient(150deg,var(--brand-500),var(--brand-700))"><?= icon('users','w-10 h-10',['style'=>'color:#fff']) ?></div>
              <?php endif; ?>
            </div>
            <h3 class="tilt-lift" style="font-size:1.25rem;margin-bottom:.2rem"><?= e($t['name']) ?></h3>
            <p class="badge tilt-lift" style="margin-bottom:.8rem"><?= e($t['position']) ?></p>
            <p style="font-size:.95rem"><?= e($t['description']) ?></p>
            <?php if (!empty($t['contact'])): ?>
              <a href="mailto:<?= e($t['contact']) ?>" class="muted" style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.8rem;font-size:.88rem"><?= icon('mail','w-4 h-4') ?> <?= e($t['contact']) ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p id="trEmpty" class="center muted" style="display:none;padding:3rem">No trustees match your search.</p>
  </div>
</section>

<script>
(() => {
  const grid = document.getElementById('trGrid');
  const empty = document.getElementById('trEmpty');
  const search = document.getElementById('trSearch');
  const filters = document.getElementById('trFilters');
  const cards = Array.from(document.querySelectorAll('.tr-card'));
  let pos = 'All', q = '';
  function apply() {
    let shown = 0;
    cards.forEach(c => {
      const okPos = pos === 'All' || c.dataset.position === pos;
      const okQ = !q || c.dataset.name.includes(q) || c.dataset.position.toLowerCase().includes(q);
      const show = okPos && okQ;
      c.style.display = show ? '' : 'none';
      if (show) shown++;
    });
    empty.style.display = shown ? 'none' : 'block';
  }
  filters.addEventListener('click', e => { const b = e.target.closest('[data-pos]'); if (!b) return;
    filters.querySelectorAll('[data-pos]').forEach(x => x.classList.remove('active')); b.classList.add('active'); pos = b.dataset.pos; apply(); });
  search.addEventListener('input', () => { q = search.value.trim().toLowerCase(); apply(); });
})();
</script>

<section class="section" style="padding-top:0">
  <div class="container center reveal">
    <div class="clay" style="padding:2.4rem">
      <h2>Want to join our cause?</h2>
      <p class="lead" style="margin-inline:auto">We welcome volunteers, educators and well-wishers who share our vision.</p>
      <a href="/contact.php" class="btn btn-lg" style="margin-top:1.2rem"><?= icon('hand','w-5 h-5') ?> Get in touch</a>
    </div>
  </div>
</section>

<?php require APP_DIR . '/layout/footer.php'; ?>
