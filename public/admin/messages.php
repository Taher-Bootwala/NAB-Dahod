<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $repo->remove('contact_messages', $_POST['id'] ?? '');
    $repo->audit('message_delete', $_POST['id'] ?? '');
    $msg = 'Message deleted.';
}

$contacts = $repo->contactMessages();
$adminPage   = 'messages';
$pageHeading = 'Contact Messages';
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<div style="margin-bottom:1rem"><span class="muted"><?= count($contacts) ?> messages</span></div>

<?php if ($contacts): ?>
<div style="display:flex;flex-direction:column;gap:1rem">
  <?php foreach ($contacts as $c): ?>
  <div class="clay reveal" style="padding:1.2rem 1.4rem">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:.6rem">
      <div style="display:flex;align-items:center;gap:.7rem">
        <div class="avatar"><?= e(mb_strtoupper(mb_substr($c['name'] ?? 'A', 0, 1))) ?></div>
        <div>
          <div style="font-weight:700;color:var(--ink)"><?= e($c['name']) ?></div>
          <div style="font-size:.82rem;color:var(--ink-mute)">
            <?= e($c['email']) ?>
            <?php if (!empty($c['phone'])): ?> · <?= e($c['phone']) ?><?php endif ?>
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:1rem">
        <span class="muted" style="font-size:.82rem;white-space:nowrap"><?= date('M j, Y', strtotime($c['created_at'])) ?></span>
        <?php if (($c['status'] ?? 'new') === 'new'): ?>
          <span class="badge">New</span>
        <?php endif ?>
        <form method="POST" onsubmit="return confirm('Delete this message?')">
          <?= csrf_field() ?>
          <input type="hidden" name="_action" value="delete">
          <input type="hidden" name="id" value="<?= e($c['id']) ?>">
          <button type="submit" style="color:#dc2626;font-weight:600;font-size:.85rem;background:none;border:none;cursor:pointer">Delete</button>
        </form>
      </div>
    </div>
    <p style="margin:0;color:var(--ink-soft);font-size:.92rem;line-height:1.6"><?= nl2br(e($c['message'])) ?></p>
  </div>
  <?php endforeach ?>
</div>
<?php else: ?>
<div class="clay" style="padding:3rem;text-align:center">
  <?= icon('inbox','w-10 h-10',['style'=>'color:var(--ink-mute);margin:0 auto 1rem']) ?>
  <p class="muted">No messages yet.</p>
</div>
<?php endif ?>

<?php require APP_DIR . '/layout/admin_footer.php'; ?>
