<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

$live  = $repo->live();
$limit = SUPABASE_STORAGE_LIMIT;
$usage = null;
$err   = '';

if ($live) {
    $usage = $supabase->storageUsage();
    if (empty($usage['buckets']) && $usage['total'] === 0) {
        // Could be a genuinely empty project, or the list call failed.
        $err = 'No buckets found, or Storage could not be read with the current service key.';
    }
} else {
    // Offline/demo: report what the local uploads folder is holding instead.
    $uploadsDir = PUBLIC_DIR . '/uploads';
    $buckets = [];
    $total = 0;
    if (is_dir($uploadsDir)) {
        foreach (scandir($uploadsDir) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $path = $uploadsDir . '/' . $entry;
            if (!is_dir($path)) continue;
            $size = 0; $count = 0;
            foreach (scandir($path) as $f) {
                $fp = $path . '/' . $f;
                if (is_file($fp)) { $size += (int) filesize($fp); $count++; }
            }
            $buckets[] = ['name' => $entry, 'size' => $size, 'count' => $count, 'public' => true];
            $total += $size;
        }
    }
    $usage = ['total' => $total, 'buckets' => $buckets];
}

$used      = (int) ($usage['total'] ?? 0);
$remaining = max(0, $limit - $used);
$percent   = $limit > 0 ? min(100, round($used / $limit * 100, 1)) : 0;
$barColor  = $percent >= 90 ? '#dc2626' : ($percent >= 70 ? '#d97706' : 'var(--brand-600)');

$adminPage   = 'storage';
$pageHeading = 'Storage';
require APP_DIR . '/layout/admin_header.php';
?>

<?php if (!$live): ?>
<div style="background:#fffbeb;border:1px solid #fde68a;color:#b45309;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.4rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('database','w-5 h-5') ?> Supabase isn’t configured — showing local <code>uploads/</code> usage instead.
</div>
<?php elseif ($err): ?>
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.4rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('close','w-5 h-5') ?> <?= e($err) ?>
</div>
<?php endif ?>

<!-- Summary cards -->
<div style="display:grid;gap:1.2rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:1.6rem">
  <div class="stat-card reveal">
    <div class="stat-icon" style="background:var(--brand-50)"><?= icon('database','w-6 h-6',['style'=>'color:var(--brand-600)']) ?></div>
    <div style="font-size:1.8rem;font-weight:900;color:var(--ink);line-height:1"><?= e(format_bytes($used)) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Used</div>
  </div>
  <div class="stat-card reveal" style="transition-delay:.08s">
    <div class="stat-icon" style="background:#f0fdf4"><?= icon('check-circle','w-6 h-6',['style'=>'color:#16a34a']) ?></div>
    <div style="font-size:1.8rem;font-weight:900;color:var(--ink);line-height:1"><?= e(format_bytes($remaining)) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Remaining</div>
  </div>
  <div class="stat-card reveal" style="transition-delay:.16s">
    <div class="stat-icon" style="background:#eef2ff"><?= icon('chart','w-6 h-6',['style'=>'color:#4f46e5']) ?></div>
    <div style="font-size:1.8rem;font-weight:900;color:var(--ink);line-height:1"><?= e(format_bytes($limit)) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Total quota</div>
  </div>
</div>

<!-- Usage bar -->
<div class="clay reveal" style="padding:1.6rem;margin-bottom:1.6rem">
  <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.7rem">
    <h2 style="font-size:1rem;margin:0"><?= icon('database','w-4 h-4',['style'=>'display:inline;color:var(--brand-600)']) ?> Storage used</h2>
    <span style="font-weight:800;color:<?= $barColor ?>"><?= e((string) $percent) ?>%</span>
  </div>
  <div style="height:14px;border-radius:999px;background:var(--brand-50);overflow:hidden;box-shadow:inset 0 1px 3px rgba(26,60,116,.12)">
    <div style="height:100%;width:<?= e((string) $percent) ?>%;background:<?= $barColor ?>;border-radius:999px;transition:width .6s cubic-bezier(.2,.8,.2,1)"></div>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:.6rem;font-size:.85rem;color:var(--ink-mute)">
    <span><?= e(format_bytes($used)) ?> used</span>
    <span><?= e(format_bytes($limit)) ?> total</span>
  </div>
</div>

<!-- Per-bucket breakdown -->
<div class="clay reveal" style="padding:1.4rem">
  <h2 style="font-size:1rem;margin:0 0 1rem"><?= icon('image','w-4 h-4',['style'=>'display:inline;color:var(--brand-600)']) ?> Breakdown by bucket</h2>
  <?php if (!empty($usage['buckets'])): ?>
  <table class="a-table">
    <thead>
      <tr><th>Bucket</th><th style="width:120px">Files</th><th style="width:140px;text-align:right">Size</th></tr>
    </thead>
    <tbody>
      <?php foreach ($usage['buckets'] as $b): ?>
      <tr>
        <td>
          <span style="font-weight:600;color:var(--ink)"><?= e($b['name']) ?></span>
          <?php if (!empty($b['public'])): ?><span class="badge" style="margin-left:.5rem;font-size:.7rem">public</span><?php endif ?>
        </td>
        <td><?= e((string) ($b['count'] ?? 0)) ?></td>
        <td style="text-align:right;font-weight:600"><?= e(format_bytes((float) ($b['size'] ?? 0))) ?></td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  <?php else: ?>
  <p class="muted" style="margin:0">No stored files found.</p>
  <?php endif ?>
</div>

<p class="muted" style="font-size:.8rem;margin-top:1rem">
  Quota is configurable via <code>SUPABASE_STORAGE_LIMIT</code> in <code>.env</code> (defaults to 1&nbsp;GB — the Supabase free-tier limit). Sizes are summed from each bucket’s files.
</p>

<?php require APP_DIR . '/layout/admin_footer.php'; ?>
