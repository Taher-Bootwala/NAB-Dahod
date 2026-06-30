<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

$activities = $repo->activities(['limit' => 5]);
$contacts   = $repo->contactMessages(['limit' => 5]);
$gallery    = $repo->gallery();

$adminPage    = 'index';
$pageHeading  = 'Dashboard';
require APP_DIR . '/layout/admin_header.php';
?>

<!-- Stats row (no total donations) -->
<div style="display:grid;gap:1.2rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:1.8rem">

  <div class="stat-card reveal">
    <div class="stat-icon" style="background:var(--brand-50)"><?= icon('sparkle','w-6 h-6',['style'=>'color:var(--brand-600)']) ?></div>
    <div style="font-size:2rem;font-weight:900;color:var(--ink);line-height:1"><?= count($repo->activities()) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Activities</div>
  </div>

  <div class="stat-card reveal" style="transition-delay:.08s">
    <div class="stat-icon" style="background:#f0fdf4"><?= icon('image','w-6 h-6',['style'=>'color:#16a34a']) ?></div>
    <div style="font-size:2rem;font-weight:900;color:var(--ink);line-height:1"><?= count($gallery) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Gallery Photos</div>
  </div>

  <div class="stat-card reveal" style="transition-delay:.16s">
    <div class="stat-icon" style="background:#fef3c7"><?= icon('inbox','w-6 h-6',['style'=>'color:#d97706']) ?></div>
    <div style="font-size:2rem;font-weight:900;color:var(--ink);line-height:1"><?= count($contacts) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Messages</div>
  </div>

  <div class="stat-card reveal" style="transition-delay:.24s">
    <div class="stat-icon" style="background:#fdf2f8"><?= icon('users','w-6 h-6',['style'=>'color:#9333ea']) ?></div>
    <div style="font-size:2rem;font-weight:900;color:var(--ink);line-height:1"><?= count($repo->trustees()) ?></div>
    <div style="font-size:.88rem;color:var(--ink-mute);margin-top:.3rem;font-weight:600">Trustees</div>
  </div>

</div>

<!-- Recent Activities (full width) -->
<div class="clay reveal" style="padding:1.4rem;margin-bottom:1.4rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <h2 style="font-size:1rem;margin:0"><?= icon('sparkle','w-4 h-4',['style'=>'display:inline;color:var(--brand-600)']) ?> Recent Activities</h2>
      <a href="/admin/activities.php" class="muted" style="font-size:.82rem;font-weight:600">View all →</a>
    </div>
    <?php if ($activities): ?>
    <table class="a-table">
      <?php foreach ($activities as $a): ?>
      <tr>
        <td style="padding:.7rem 0">
          <div style="font-weight:600;color:var(--ink)"><?= e($a['title']) ?></div>
          <div style="font-size:.8rem;color:var(--ink-mute)"><?= date('M j, Y', strtotime($a['date'])) ?></div>
        </td>
        <td style="text-align:right;padding:.7rem 0">
          <span class="badge"><?= e($a['category']) ?></span>
        </td>
      </tr>
      <?php endforeach ?>
    </table>
    <?php else: ?>
      <p class="muted" style="font-size:.9rem">No activities yet.</p>
    <?php endif ?>
  </div>

<!-- Contact Messages -->
<div class="clay reveal" style="padding:1.4rem">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h2 style="font-size:1rem;margin:0"><?= icon('inbox','w-4 h-4',['style'=>'display:inline;color:var(--brand-600)']) ?> Recent Messages</h2>
    <a href="/admin/messages.php" class="muted" style="font-size:.82rem;font-weight:600">View all →</a>
  </div>
  <?php if ($contacts): ?>
  <table class="a-table">
    <?php foreach ($contacts as $c): ?>
    <tr>
      <td style="padding:.75rem 0;width:30%">
        <div style="font-weight:600;color:var(--ink)"><?= e($c['name']) ?></div>
        <div style="font-size:.78rem;color:var(--ink-mute)"><?= e($c['email']) ?></div>
      </td>
      <td style="padding:.75rem 1rem;color:var(--ink-soft);font-size:.9rem">
        <?= e(mb_substr($c['message'], 0, 90)) ?><?= mb_strlen($c['message']) > 90 ? '…' : '' ?>
      </td>
      <td style="text-align:right;padding:.75rem 0;white-space:nowrap">
        <span class="muted" style="font-size:.78rem"><?= date('M j, Y', strtotime($c['created_at'])) ?></span>
      </td>
    </tr>
    <?php endforeach ?>
  </table>
  <?php else: ?>
    <p class="muted" style="font-size:.9rem">No messages yet.</p>
  <?php endif ?>
</div>

<?php require APP_DIR . '/layout/admin_footer.php'; ?>
