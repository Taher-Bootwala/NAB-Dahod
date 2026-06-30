<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (str_starts_with($key, 'setting_')) {
            $repo->saveSetting(substr($key, 8), trim($value));
        }
    }
    $repo->audit('settings_update', 'Updated site content');
    $msg = 'Settings saved successfully.';
}

$settings    = $repo->settings();
$adminPage   = 'content';
$pageHeading = 'Site Content';
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.4rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<form method="POST">
  <?= csrf_field() ?>
  <div class="stack">

    <div class="clay reveal" style="padding:1.6rem">
      <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
        <?= icon('sparkle','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> Hero Section
      </h2>
      <div class="stack">
        <div><label class="label">Hero Title</label><input class="field" type="text" name="setting_hero_title" value="<?= e($settings['hero_title'] ?? '') ?>"></div>
        <div><label class="label">Hero Subtitle</label><textarea class="field" name="setting_hero_subtitle" rows="3"><?= e($settings['hero_subtitle'] ?? '') ?></textarea></div>
      </div>
    </div>

    <div class="clay reveal" style="padding:1.6rem;transition-delay:.08s">
      <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
        <?= icon('dashboard','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> Statistics
      </h2>
      <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr">
        <div><label class="label">Years of Service</label><input class="field" type="text" name="setting_stat_years" value="<?= e($settings['stat_years'] ?? '') ?>"></div>
        <div><label class="label">Volunteers</label><input class="field" type="text" name="setting_stat_volunteers" value="<?= e($settings['stat_volunteers'] ?? '') ?>"></div>
      </div>
    </div>

    <div class="clay reveal" style="padding:1.6rem;transition-delay:.16s">
      <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
        <?= icon('shield','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> About Page
      </h2>
      <div class="stack">
        <div><label class="label">Mission Statement</label><textarea class="field" name="setting_mission_statement" rows="3"><?= e($settings['mission_statement'] ?? '') ?></textarea></div>
        <div><label class="label">School History</label><textarea class="field" name="setting_about_history" rows="4"><?= e($settings['about_history'] ?? '') ?></textarea></div>
        <div><label class="label">Principal Message</label><textarea class="field" name="setting_principal_message" rows="4"><?= e($settings['principal_message'] ?? '') ?></textarea></div>
      </div>
    </div>

    <div class="clay reveal" style="padding:1.6rem;transition-delay:.24s">
      <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
        <?= icon('globe','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> Footer
      </h2>
      <div><label class="label">About Text</label><textarea class="field" name="setting_footer_about" rows="3"><?= e($settings['footer_about'] ?? '') ?></textarea></div>
    </div>

    <div style="display:flex;gap:.8rem">
      <button type="submit" class="btn btn-lg"><?= icon('check','w-5 h-5') ?> Save All Settings</button>
      <a href="/admin/" class="btn btn-ghost btn-lg">Cancel</a>
    </div>

  </div>
</form>

<?php require APP_DIR . '/layout/admin_footer.php'; ?>
