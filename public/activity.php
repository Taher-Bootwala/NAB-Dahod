<?php
require __DIR__ . '/../app/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$a = $repo->activity($slug);
if (!$a) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$more = array_values(array_filter($repo->activities(['limit' => 4]), fn($x) => ($x['id'] ?? '') !== ($a['id'] ?? '')));
$more = array_slice($more, 0, 3);
$page = ['title' => $a['title'], 'nav' => 'activities', 'desc' => excerpt($a['description'], 150), 'og' => $a['images'][0] ?? null];
require APP_DIR . '/layout/header.php';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container" style="max-width:900px">
    <a href="/activities.php" class="muted" style="display:inline-flex;align-items:center;gap:.4rem;font-weight:600"><?= icon('chevron-right','w-4 h-4',['style'=>'transform:rotate(180deg)']) ?> All activities</a>
    <div class="reveal" style="margin-top:1rem">
      <div style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
        <span class="badge"><?= e($a['category']) ?></span>
        <span class="muted" style="display:inline-flex;align-items:center;gap:.4rem"><?= icon('calendar','w-4 h-4') ?> <?= e(fmt_date($a['date'], 'j F Y')) ?></span>
      </div>
      <h1 style="margin-top:.8rem"><?= e($a['title']) ?></h1>
      <button data-speak class="btn btn-ghost" type="button" style="margin-top:.6rem;padding:.6rem 1.2rem"><?= icon('volume','w-5 h-5') ?> <span class="speak-label">Listen</span></button>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container" style="max-width:900px">
    <?php $imgs = $a['images'] ?? []; if ($imgs): ?>
      <div class="reveal" data-reveal="zoom" style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-card)">
        <img src="<?= attr($imgs[0]) ?>" alt="<?= attr($a['title']) ?>" loading="eager" style="width:100%;display:block;max-height:480px;object-fit:cover" data-lightbox="<?= attr($imgs[0]) ?>" data-caption="<?= attr($a['title']) ?>">
      </div>
    <?php endif; ?>

    <div class="reveal" style="margin-top:2rem;font-size:1.1rem;line-height:1.8;color:var(--ink-soft)">
      <?= nl2br(e($a['description'])) ?>
    </div>

    <?php if (count($imgs) > 1): ?>
      <h3 style="margin-top:2.5rem">Gallery</h3>
      <div class="masonry" style="margin-top:1rem">
        <?php foreach (array_slice($imgs, 1) as $img): ?>
          <img src="<?= attr($img) ?>" alt="<?= attr($a['title']) ?>" loading="lazy" class="card-hover" style="width:100%;border-radius:16px;cursor:zoom-in" data-lightbox="<?= attr($img) ?>" data-caption="<?= attr($a['title']) ?>">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="glass" style="margin-top:2.5rem;padding:1.8rem;border-radius:var(--radius);display:flex;gap:1.2rem;align-items:center;flex-wrap:wrap;justify-content:space-between">
      <div><h3 style="margin:0">Inspired by what you see?</h3><p style="margin:.3rem 0 0">Help us keep these programs running for every child.</p></div>
      <a href="/donate.php" class="btn btn-lg"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
    </div>
  </div>
</section>

<?php if ($more): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <h2 style="text-align:center">More activities</h2>
    <div class="auto-grid" data-stagger style="margin-top:1.6rem">
      <?php foreach ($more as $m): ?>
        <a href="/activity/<?= e($m['slug'] ?? $m['id']) ?>" class="clay card-hover reveal" style="overflow:hidden;text-decoration:none;color:inherit">
          <div style="aspect-ratio:16/10;overflow:hidden;background:var(--bg-soft)"><img src="<?= attr($m['images'][0] ?? '') ?>" alt="<?= attr($m['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover"></div>
          <div style="padding:1.1rem"><span class="badge"><?= e($m['category']) ?></span><h3 style="margin:.5rem 0 0;font-size:1.1rem"><?= e($m['title']) ?></h3></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require APP_DIR . '/layout/footer.php'; ?>
