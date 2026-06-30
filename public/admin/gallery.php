<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$err = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';

// Upload to the "gallery" Storage bucket (or local fallback).
function saveGalleryImage(array $file): ?string
{
    return store_image(
        (string) ($file['tmp_name'] ?? ''),
        (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
        BUCKET_GALLERY,
        'gallery'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'delete' && $id) {
        $repo->remove('gallery', $id);
        $repo->audit('gallery_delete', $id);
        $msg = 'Photo deleted.';
        $action = 'list';
    } elseif (($_POST['_action'] ?? '') === 'save') {
        $imageUrl = trim($_POST['existing_image'] ?? '');
        $uploaded = saveGalleryImage($_FILES['image'] ?? []);
        if ($uploaded) $imageUrl = $uploaded;

        // Category: custom "add new" value takes precedence over the dropdown
        $category = trim($_POST['new_category'] ?? '') ?: trim($_POST['category'] ?? '');

        if ($imageUrl === '') {
            // Image is mandatory — do not save with a blank image
            $err = 'Please upload an image. The image field cannot be left blank.';
            $action = $id ? 'edit' : 'new';
        } else {
            $data = ['image_url' => $imageUrl, 'title' => trim($_POST['title']),
                     'description' => trim($_POST['description']), 'category' => $category, 'date' => $_POST['date']];
            if ($id) { $repo->update('gallery', $id, $data); $repo->audit('gallery_update', $id); $msg = 'Photo updated.'; }
            else     { $row = $repo->create('gallery', $data); $repo->audit('gallery_create', $row['id'] ?? ''); $msg = 'Photo added.'; }
            $action = 'list';
        }
    }
}

$categories = $repo->galleryCategories();
$allGallery = $repo->gallery();
$edit = ($action === 'edit' && $id) ? array_values(array_filter($allGallery, fn($g) => $g['id'] === $id))[0] ?? null : null;

// List filters (search + category)
$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$totalCount = count($allGallery);
$gallery = $allGallery;
if ($cat !== '' && $cat !== 'All') {
    $gallery = array_values(array_filter($gallery, fn($g) => ($g['category'] ?? '') === $cat));
}
if ($q !== '') {
    $ql = mb_strtolower($q);
    $gallery = array_values(array_filter($gallery, fn($g) =>
        str_contains(mb_strtolower($g['title'] ?? ''), $ql) ||
        str_contains(mb_strtolower($g['description'] ?? ''), $ql) ||
        str_contains(mb_strtolower($g['category'] ?? ''), $ql)
    ));
}
$filtered = $q !== '' || ($cat !== '' && $cat !== 'All');

$adminPage   = 'gallery';
$pageHeading = $action === 'list' ? 'Gallery' : ($edit ? 'Edit Photo' : 'Add Photo');
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<?php if ($err): ?>
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('close','w-5 h-5') ?> <?= e($err) ?>
</div>
<?php endif ?>

<?php if ($action === 'list'): ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.6rem">
  <span class="muted"><?= $filtered ? count($gallery) . ' of ' . $totalCount : $totalCount ?> photos</span>
  <a href="?action=new" class="btn"><?= icon('image','w-4 h-4') ?> Add Photo</a>
</div>

<form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1.2rem">
  <div style="position:relative;flex:1;min-width:200px">
    <span style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--ink-mute);pointer-events:none"><?= icon('search','w-4 h-4') ?></span>
    <input class="field" type="search" name="q" value="<?= e($q) ?>" placeholder="Search by title or description…" style="padding-left:2.4rem">
  </div>
  <select class="field" name="cat" style="max-width:220px" onchange="this.form.submit()">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
    <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach ?>
  </select>
  <button type="submit" class="btn"><?= icon('search','w-4 h-4') ?> Search</button>
  <?php if ($filtered): ?><a href="?" class="btn btn-ghost">Clear</a><?php endif ?>
</form>

<div style="display:grid;gap:1.1rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
  <?php foreach ($gallery as $g): ?>
  <div class="clay img-zoom" style="overflow:hidden">
    <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['title']) ?>"
      style="width:100%;height:160px;object-fit:cover;display:block"
      onerror="this.style.background='var(--bg-soft)';this.src=''">
    <div style="padding:.9rem">
      <div style="font-weight:700;color:var(--ink);font-size:.92rem;margin-bottom:.2rem"><?= e($g['title']) ?></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.6rem">
        <span class="badge" style="font-size:.72rem"><?= e($g['category']) ?></span>
        <div style="display:flex;gap:.7rem">
          <a href="?action=edit&id=<?= urlencode($g['id']) ?>" style="color:var(--brand-600);font-weight:600;font-size:.82rem">Edit</a>
          <form method="POST" action="?id=<?= urlencode($g['id']) ?>" class="inline" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <input type="hidden" name="_action" value="delete">
            <button type="submit" style="color:#dc2626;font-weight:600;font-size:.82rem;background:none;border:none;cursor:pointer">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach ?>
  <?php if (!$gallery): ?>
  <p class="muted" style="grid-column:1/-1"><?= $filtered ? 'No photos match your search.' : 'No photos yet.' ?></p>
  <?php endif ?>
</div>

<?php else: ?>

<div class="clay reveal" style="padding:1.8rem;max-width:600px">
  <form method="POST" enctype="multipart/form-data" onsubmit="return validateGallery()">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">
    <input type="hidden" name="existing_image" value="<?= e($edit['image_url'] ?? '') ?>">
    <div class="stack">
      <div>
        <label class="label">Image <?= $edit ? '' : '*' ?></label>
        <?php if (!empty($edit['image_url'])): ?>
          <div id="currentImg" style="margin-bottom:.7rem;position:relative;display:inline-block">
            <img src="<?= e($edit['image_url']) ?>" alt="Current image" style="width:150px;height:95px;border-radius:12px;object-fit:cover;box-shadow:var(--shadow-card)" onerror="this.style.display='none'">
            <button type="button" onclick="removeCurrentImg()" title="Remove this image" aria-label="Remove this image"
              style="position:absolute;top:-7px;right:-7px;width:22px;height:22px;border-radius:50%;background:#ef4444;color:#fff;border:none;cursor:pointer;font-size:.85rem;line-height:1;display:grid;place-items:center;box-shadow:var(--shadow-card)">×</button>
          </div>
        <?php endif ?>
        <label for="imgInput" id="dropZone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.6rem 1rem;border:2px dashed var(--brand-300);border-radius:16px;background:var(--brand-50);cursor:pointer;transition:border-color .2s">
          <?= icon('image','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
          <span style="font-weight:600;color:var(--brand-700);font-size:.9rem">Click to upload or drag &amp; drop</span>
          <span class="muted" style="font-size:.78rem">PNG, JPG, WebP<?= $edit ? ' — leave empty to keep current' : '' ?></span>
        </label>
        <input id="imgInput" type="file" name="image" accept="image/*" <?= $edit ? '' : 'required' ?> style="display:none" onchange="previewImg(this)">
        <div id="imgPreview" style="margin-top:.7rem"></div>
      </div>
      <div>
        <label class="label">Title *</label>
        <input class="field" type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required>
      </div>
      <div>
        <label class="label">Description</label>
        <textarea class="field" name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea>
      </div>
      <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr">
        <div>
          <label class="label">Category *</label>
          <?php
          $allCats = array_values(array_unique(array_merge(
              ['School Activities','Annual Events','Classroom Learning','Sports','Festivals'],
              $categories
          )));
          sort($allCats);
          $editCat = $edit['category'] ?? '';
          ?>
          <select class="field" name="category" id="catSelect" onchange="toggleNewCat(this.value)">
            <?php foreach ($allCats as $cat): ?>
            <option value="<?= e($cat) ?>" <?= $editCat === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach ?>
            <option value="__new__" <?= ($editCat && !in_array($editCat, $allCats, true)) ? 'selected' : '' ?>>＋ Add category…</option>
          </select>
        </div>
        <div>
          <label class="label">Date *</label>
          <input class="field" type="date" name="date" value="<?= e($edit['date'] ?? date('Y-m-d')) ?>" required>
        </div>
      </div>

      <!-- New category input (hidden unless "Add category…" is selected) -->
      <div id="newCatWrap" style="display:none">
        <label class="label">New Category Name *</label>
        <input class="field" type="text" name="new_category" id="newCatInput" value="<?= e(($editCat && !in_array($editCat, $allCats, true)) ? $editCat : '') ?>" placeholder="e.g. Music, Art, Field Trip…">
      </div>
      <div style="display:flex;gap:.8rem">
        <button type="submit" class="btn"><?= icon('check','w-4 h-4') ?> Save</button>
        <a href="?action=list" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
function toggleNewCat(val) {
  const wrap = document.getElementById('newCatWrap');
  const inp  = document.getElementById('newCatInput');
  const show = val === '__new__';
  wrap.style.display = show ? '' : 'none';
  inp.required = show;
}
toggleNewCat(document.getElementById('catSelect').value);

function removeCurrentImg() {
  document.querySelector('input[name="existing_image"]').value = '';
  const cur = document.getElementById('currentImg');
  if (cur) cur.remove();
}

function validateGallery() {
  const existing = document.querySelector('input[name="existing_image"]').value.trim();
  const hasFile  = document.getElementById('imgInput').files.length > 0;
  if (!existing && !hasFile) {
    alert('Please upload an image. The image field cannot be left blank.');
    document.getElementById('dropZone').style.borderColor = '#ef4444';
    return false;
  }
  return true;
}

function previewImg(input) {
  const box = document.getElementById('imgPreview');
  box.innerHTML = '';
  const file = input.files[0];
  if (!file) return;
  const img = document.createElement('img');
  img.src = URL.createObjectURL(file);
  img.style.cssText = 'width:150px;height:95px;object-fit:cover;border-radius:12px;box-shadow:var(--shadow-card)';
  box.appendChild(img);
  const cur = document.getElementById('currentImg');
  if (cur) cur.style.display = 'none';
}
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--brand-600)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--brand-300)'; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor = 'var(--brand-300)';
  document.getElementById('imgInput').files = e.dataTransfer.files;
  previewImg(document.getElementById('imgInput'));
});
</script>

<?php endif ?>
<?php require APP_DIR . '/layout/admin_footer.php'; ?>
