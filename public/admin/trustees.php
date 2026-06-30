<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';

// Upload to the "trustees" Storage bucket (or local fallback).
function saveTrusteePhoto(array $file): ?string
{
    return store_image(
        (string) ($file['tmp_name'] ?? ''),
        (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
        BUCKET_TRUSTEES,
        'trustee'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'delete' && $id) {
        $repo->remove('trustees', $id);
        $repo->audit('trustee_delete', $id);
        $msg = 'Trustee deleted.';
        $action = 'list';
    } elseif (($_POST['_action'] ?? '') === 'save') {
        $photo = trim($_POST['existing_photo'] ?? '');
        $uploaded = saveTrusteePhoto($_FILES['photo'] ?? []);
        if ($uploaded) $photo = $uploaded;
        // No photo provided — fall back to the default avatar (stored on Supabase).
        if ($photo === '') $photo = default_trustee_photo();

        $data = ['name' => trim($_POST['name']), 'position' => trim($_POST['position']),
                 'photo' => $photo, 'address' => trim($_POST['address'])];
        if ($id) { $repo->update('trustees', $id, $data); $repo->audit('trustee_update', $id); $msg = 'Trustee updated.'; }
        else     { $row = $repo->create('trustees', $data); $repo->audit('trustee_create', $row['id'] ?? ''); $msg = 'Trustee added.'; }
        $action = 'list';
    }
}

$allTrustees = $repo->trustees();
$edit = ($action === 'edit' && $id) ? array_values(array_filter($allTrustees, fn($t) => $t['id'] === $id))[0] ?? null : null;

// Distinct positions act as the "category" filter for trustees
$positions = array_values(array_unique(array_filter(array_map(fn($t) => trim($t['position'] ?? ''), $allTrustees))));
sort($positions);

// List filters (search + position)
$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$totalCount = count($allTrustees);
$trustees = $allTrustees;
if ($cat !== '' && $cat !== 'All') {
    $trustees = array_values(array_filter($trustees, fn($t) => ($t['position'] ?? '') === $cat));
}
if ($q !== '') {
    $ql = mb_strtolower($q);
    $trustees = array_values(array_filter($trustees, fn($t) =>
        str_contains(mb_strtolower($t['name'] ?? ''), $ql) ||
        str_contains(mb_strtolower($t['position'] ?? ''), $ql) ||
        str_contains(mb_strtolower($t['address'] ?? ''), $ql)
    ));
}
$filtered = $q !== '' || ($cat !== '' && $cat !== 'All');

$adminPage   = 'trustees';
$pageHeading = $action === 'list' ? 'Trustees' : ($edit ? 'Edit Trustee' : 'Add Trustee');
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<?php if ($action === 'list'): ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.6rem">
  <span class="muted"><?= $filtered ? count($trustees) . ' of ' . $totalCount : $totalCount ?> trustees</span>
  <a href="?action=new" class="btn"><?= icon('users','w-4 h-4') ?> Add Trustee</a>
</div>

<form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1.2rem">
  <div style="position:relative;flex:1;min-width:200px">
    <span style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--ink-mute);pointer-events:none"><?= icon('search','w-4 h-4') ?></span>
    <input class="field" type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name, position or address…" style="padding-left:2.4rem">
  </div>
  <select class="field" name="cat" style="max-width:220px" onchange="this.form.submit()">
    <option value="">All positions</option>
    <?php foreach ($positions as $p): ?>
    <option value="<?= e($p) ?>" <?= $cat === $p ? 'selected' : '' ?>><?= e($p) ?></option>
    <?php endforeach ?>
  </select>
  <button type="submit" class="btn"><?= icon('search','w-4 h-4') ?> Search</button>
  <?php if ($filtered): ?><a href="?" class="btn btn-ghost">Clear</a><?php endif ?>
</form>

<div class="clay reveal" style="overflow:hidden">
  <div style="overflow-x:auto">
  <table class="a-table">
    <thead><tr><th>Photo</th><th>Name</th><th>Position</th><th style="text-align:right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($trustees as $t): ?>
    <tr>
      <td><img src="<?= e($t['photo']) ?>" alt="<?= e($t['name']) ?>"
        style="width:44px;height:44px;border-radius:50%;object-fit:cover;box-shadow:var(--shadow-card)"
        onerror="this.style.display='none'"></td>
      <td style="font-weight:700;color:var(--ink)"><?= e($t['name']) ?></td>
      <td class="muted" style="font-size:.9rem"><?= e($t['position']) ?></td>
      <td style="text-align:right;white-space:nowrap">
        <a href="?action=edit&id=<?= urlencode($t['id']) ?>" style="color:var(--brand-600);font-weight:600;font-size:.88rem;margin-right:.8rem">Edit</a>
        <form method="POST" action="?id=<?= urlencode($t['id']) ?>" class="inline" onsubmit="return confirm('Delete this trustee?')">
          <?= csrf_field() ?>
          <input type="hidden" name="_action" value="delete">
          <button type="submit" style="color:#dc2626;font-weight:600;font-size:.88rem;background:none;border:none;cursor:pointer">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach ?>
    <?php if (!$trustees): ?><tr><td colspan="4" class="muted" style="text-align:center;padding:2rem"><?= $filtered ? 'No trustees match your search.' : 'No trustees yet.' ?></td></tr><?php endif ?>
    </tbody>
  </table>
  </div>
</div>

<?php else: ?>

<div class="clay reveal" style="padding:1.8rem;max-width:560px">
  <form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">
    <input type="hidden" name="existing_photo" value="<?= e($edit['photo'] ?? '') ?>">
    <div class="stack">
      <div>
        <label class="label">Photo</label>
        <?php if (!empty($edit['photo'])): ?>
          <div id="currentPhoto" style="margin-bottom:.7rem;position:relative;display:inline-block">
            <img src="<?= e($edit['photo']) ?>" alt="Current photo" style="width:90px;height:90px;border-radius:14px;object-fit:cover;box-shadow:var(--shadow-card)" onerror="this.style.display='none'">
            <button type="button" onclick="removeCurrentPhoto()" title="Remove this photo" aria-label="Remove this photo"
              style="position:absolute;top:-7px;right:-7px;width:22px;height:22px;border-radius:50%;background:#ef4444;color:#fff;border:none;cursor:pointer;font-size:.85rem;line-height:1;display:grid;place-items:center;box-shadow:var(--shadow-card)">×</button>
          </div>
        <?php endif ?>
        <label for="photoInput" id="dropZone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.6rem 1rem;border:2px dashed var(--brand-300);border-radius:16px;background:var(--brand-50);cursor:pointer;transition:border-color .2s">
          <?= icon('image','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
          <span style="font-weight:600;color:var(--brand-700);font-size:.9rem">Click to upload or drag &amp; drop</span>
          <span class="muted" style="font-size:.78rem">PNG, JPG, WebP<?= $edit ? ' — leave empty to keep current' : ' — optional, a default avatar is used if left empty' ?></span>
        </label>
        <input id="photoInput" type="file" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
        <div id="photoPreview" style="margin-top:.7rem"></div>
      </div>
      <div>
        <label class="label">Name *</label>
        <input class="field" type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required>
      </div>
      <div>
        <label class="label">Position *</label>
        <input class="field" type="text" name="position" value="<?= e($edit['position'] ?? '') ?>" required placeholder="e.g. President, Secretary">
      </div>
      <div>
        <label class="label">Address</label>
        <textarea class="field" name="address" rows="4"><?= e($edit['address'] ?? '') ?></textarea>
      </div>
      <div style="display:flex;gap:.8rem">
        <button type="submit" class="btn"><?= icon('check','w-4 h-4') ?> Save</button>
        <a href="?action=list" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
function removeCurrentPhoto() {
  document.querySelector('input[name="existing_photo"]').value = '';
  const cur = document.getElementById('currentPhoto');
  if (cur) cur.remove();
}

function previewPhoto(input) {
  const box = document.getElementById('photoPreview');
  box.innerHTML = '';
  const file = input.files[0];
  if (!file) return;
  const img = document.createElement('img');
  img.src = URL.createObjectURL(file);
  img.style.cssText = 'width:90px;height:90px;object-fit:cover;border-radius:14px;box-shadow:var(--shadow-card)';
  box.appendChild(img);
  const cur = document.getElementById('currentPhoto');
  if (cur) cur.style.display = 'none';
}
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--brand-600)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--brand-300)'; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor = 'var(--brand-300)';
  document.getElementById('photoInput').files = e.dataTransfer.files;
  previewPhoto(document.getElementById('photoInput'));
});
</script>

<?php endif ?>
<?php require APP_DIR . '/layout/admin_footer.php'; ?>
