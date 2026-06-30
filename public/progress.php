<?php
require __DIR__ . '/../app/bootstrap.php';

$reports = $repo->progressReports();

// Group reports by year (newest year first).
$byYear = [];
foreach ($reports as $r) {
    $year = (int) ($r['year'] ?? 0);
    $byYear[$year][] = $r;
}
krsort($byYear);

$page = [
    'title' => 'Our Progress',
    'nav'   => 'progress',
    'desc'  => 'Year-by-year progress reports of National Association for the Blind, Dahod — view and download our published annual reports as PDF documents.',
];
require APP_DIR . '/layout/header.php';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('document','w-4 h-4') ?> Reports</span>
      <h1 style="margin-top:1rem">Our <span class="grad-text">Progress</span></h1>
      <p class="lead" style="margin-inline:auto">A transparent, year-by-year record of our journey. Open or download any annual report below.</p>
    </div>

    <?php if ($byYear): ?>
    <!-- Search + year filter -->
    <div class="clay reveal" style="padding:1rem 1.2rem;margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;justify-content:space-between">
      <label style="flex:1;min-width:220px;display:flex;align-items:center;gap:.6rem;background:var(--bg-soft);border-radius:12px;padding:.2rem .9rem">
        <?= icon('search','w-5 h-5',['style'=>'color:var(--ink-mute)']) ?>
        <span class="sr-only">Search reports</span>
        <input id="dlSearch" type="search" placeholder="Search reports by title…" class="field" style="border:none;background:transparent;padding:.7rem 0">
      </label>
      <div style="display:flex;align-items:center;gap:.5rem">
        <label for="dlYear" style="color:var(--ink-mute);font-size:.85rem;font-weight:600">Year</label>
        <select id="dlYear" class="field" style="max-width:180px">
          <option value="All">All years</option>
          <?php foreach (array_keys($byYear) as $yr): ?>
          <option value="<?= e((string) $yr) ?>"><?= e((string) $yr) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <?php if ($byYear): ?>
      <?php foreach ($byYear as $year => $items): ?>
      <div class="reveal dl-year-group" data-year="<?= e((string) $year) ?>" style="margin-bottom:2.4rem">
        <div style="display:flex;align-items:center;gap:.9rem;margin-bottom:1.1rem">
          <h2 style="margin:0;font-size:1.5rem"><?= e((string) $year) ?></h2>
          <span style="flex:1;height:1px;background:var(--line)"></span>
          <span class="badge"><?= count($items) ?> report<?= count($items) === 1 ? '' : 's' ?></span>
        </div>

        <div style="display:grid;gap:1.1rem;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
          <?php foreach ($items as $r): ?>
          <article class="clay card-hover dl-card" data-title="<?= attr(mb_strtolower($r['title'] ?? '')) ?>" data-year="<?= e((string) $year) ?>" style="padding:1.3rem;display:flex;gap:1rem;align-items:flex-start">
            <span style="flex:none;display:grid;place-items:center;width:52px;height:52px;border-radius:14px;background:var(--brand-50)">
              <?= icon('document','w-7 h-7',['style'=>'color:var(--brand-600)']) ?>
            </span>
            <div style="min-width:0;flex:1">
              <h3 style="margin:0 0 .15rem;font-size:1.05rem;line-height:1.3"><?= e($r['title'] ?: 'Progress Report ' . $year) ?></h3>
              <p class="muted" style="margin:0 0 .9rem;font-size:.85rem"><?= e((string) $year) ?> · PDF document</p>
              <div style="display:flex;gap:.6rem;flex-wrap:wrap">
                <a href="<?= attr($r['pdf_url']) ?>" target="_blank" rel="noopener" class="btn" style="padding:.55rem 1rem;font-size:.85rem">
                  <?= icon('eye','w-4 h-4') ?> View
                </a>
                <a href="<?= attr($r['pdf_url']) ?>" download class="btn btn-ghost" style="padding:.55rem 1rem;font-size:.85rem">
                  <?= icon('download','w-4 h-4') ?> Download
                </a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <p id="dlEmpty" class="center muted" style="display:none;padding:3rem">No reports match your search.</p>
    <?php else: ?>
      <div class="clay center" style="padding:3.5rem 1.5rem;max-width:560px;margin-inline:auto">
        <div style="display:inline-grid;place-items:center;width:60px;height:60px;border-radius:18px;background:var(--brand-50);margin-bottom:1rem">
          <?= icon('document','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
        </div>
        <h2 style="margin:0 0 .4rem;font-size:1.2rem">Reports coming soon</h2>
        <p class="muted" style="margin:0">Our progress reports will be published here shortly. Please check back later.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($byYear): ?>
<script>
(() => {
  const search = document.getElementById('dlSearch');
  const yearSel = document.getElementById('dlYear');
  const empty = document.getElementById('dlEmpty');
  const groups = Array.from(document.querySelectorAll('.dl-year-group'));
  const cards = Array.from(document.querySelectorAll('.dl-card'));
  let q = '', year = 'All';
  function apply() {
    let shown = 0;
    const groupVisible = {};
    cards.forEach(c => {
      const okYear = year === 'All' || c.dataset.year === year;
      const okQ = !q || c.dataset.title.includes(q);
      const show = okYear && okQ;
      c.style.display = show ? '' : 'none';
      if (show) { shown++; groupVisible[c.dataset.year] = true; }
    });
    // Hide a year group when none of its cards are visible
    groups.forEach(g => { g.style.display = groupVisible[g.dataset.year] ? '' : 'none'; });
    empty.style.display = shown ? 'none' : 'block';
  }
  search.addEventListener('input', () => { q = search.value.trim().toLowerCase(); apply(); });
  yearSel.addEventListener('change', () => { year = yearSel.value; apply(); });
})();
</script>
<?php endif; ?>

<?php require APP_DIR . '/layout/footer.php'; ?>
