<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';

// Upload each selected image to the "activities" Storage bucket (or local fallback).
function saveActivityImages(array $files): array
{
    $urls = [];
    foreach ($files['tmp_name'] as $i => $tmp) {
        $url = store_image((string) $tmp, (int) $files['error'][$i], BUCKET_ACTIVITIES, 'act');
        if ($url) $urls[] = $url;
    }
    return $urls;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['_action'] === 'delete' && $id) {
        $repo->remove('activities', $id);
        $repo->audit('activity_delete', $id);
        $msg = 'Activity deleted.';
        $action = 'list';
    } elseif ($_POST['_action'] === 'save') {
        // Determine category (custom or selected)
        $category = trim($_POST['new_category'] ?? '') ?: trim($_POST['category'] ?? '');

        // Handle image uploads
        $uploaded = [];
        if (!empty($_FILES['images']['tmp_name'][0])) {
            $uploaded = saveActivityImages($_FILES['images']);
        }
        // Keep existing images if editing
        $existing = json_decode($_POST['existing_images'] ?? '[]', true) ?: [];
        $images   = array_merge($existing, $uploaded);

        $data = [
            'title'       => trim($_POST['title']),
            'slug'        => slugify($_POST['title']),
            'description' => trim($_POST['description']),
            'category'    => $category,
            'date'        => $_POST['date'],
            'images'      => $images,
        ];
        if ($id) {
            $repo->update('activities', $id, $data);
            $repo->audit('activity_update', $id);
            $msg = 'Activity updated.';
        } else {
            $row = $repo->create('activities', $data);
            $repo->audit('activity_create', $row['id'] ?? '');
            $msg = 'Activity created.';
        }
        $action = 'list';
    }
}

$categories = $repo->activityCategories();
$activities = $repo->activities();

// List filters (search + category + date)
$q    = trim($_GET['q'] ?? '');
$cat  = trim($_GET['cat'] ?? '');
$date = trim($_GET['date'] ?? '');
$totalCount = count($activities);
if ($cat !== '' && $cat !== 'All') {
    $activities = array_values(array_filter($activities, fn($a) => ($a['category'] ?? '') === $cat));
}
if ($date !== '') {
    $activities = array_values(array_filter($activities, fn($a) => substr((string)($a['date'] ?? ''), 0, 10) === $date));
}
if ($q !== '') {
    $ql = mb_strtolower($q);
    $activities = array_values(array_filter($activities, fn($a) =>
        str_contains(mb_strtolower($a['title'] ?? ''), $ql) ||
        str_contains(mb_strtolower($a['description'] ?? ''), $ql) ||
        str_contains(mb_strtolower($a['category'] ?? ''), $ql)
    ));
}
$filtered = $q !== '' || ($cat !== '' && $cat !== 'All') || $date !== '';

$edit = ($action === 'edit' && $id) ? $repo->activity($id) : null;

$adminPage   = 'activities';
$pageHeading = $action === 'list' ? 'Activities' : ($edit ? 'Edit Activity' : 'New Activity');
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<?php if ($action === 'list'): ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.6rem">
  <span class="muted"><?= $filtered ? count($activities) . ' of ' . $totalCount : $totalCount ?> activities</span>
  <a href="?action=new" class="btn"><?= icon('sparkle','w-4 h-4') ?> New Activity</a>
</div>

<form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1.2rem">
  <div style="position:relative;flex:1;min-width:200px">
    <span style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--ink-mute);pointer-events:none"><?= icon('search','w-4 h-4') ?></span>
    <input class="field" type="search" name="q" value="<?= e($q) ?>" placeholder="Search by title or description…" style="padding-left:2.4rem">
  </div>
  <select class="field" name="cat" style="max-width:200px" onchange="this.form.submit()">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
    <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach ?>
  </select>
  <label style="display:flex;align-items:center;gap:.4rem;color:var(--ink-mute);font-size:.85rem;font-weight:600">Date
    <input class="field" type="date" name="date" value="<?= e($date) ?>" style="max-width:170px" onchange="this.form.submit()">
  </label>
  <button type="submit" class="btn"><?= icon('search','w-4 h-4') ?> Search</button>
  <?php if ($filtered): ?><a href="?" class="btn btn-ghost">Clear</a><?php endif ?>
</form>

<div class="clay reveal" style="overflow:hidden">
  <div style="overflow-x:auto">
  <table class="a-table">
    <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Images</th><th style="text-align:right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($activities as $a): ?>
    <tr>
      <td style="font-weight:600;color:var(--ink)"><?= e($a['title']) ?></td>
      <td><span class="badge"><?= e($a['category']) ?></span></td>
      <td class="muted" style="font-size:.88rem;white-space:nowrap"><?= date('M j, Y', strtotime($a['date'])) ?></td>
      <td class="muted"><?= count($a['images'] ?? []) ?></td>
      <td style="text-align:right;white-space:nowrap">
        <a href="?action=edit&id=<?= urlencode($a['id']) ?>" style="color:var(--brand-600);font-weight:600;font-size:.88rem;margin-right:.8rem">Edit</a>
        <form method="POST" action="?id=<?= urlencode($a['id']) ?>" class="inline" onsubmit="return confirm('Delete this activity?')">
          <?= csrf_field() ?>
          <input type="hidden" name="_action" value="delete">
          <button type="submit" style="color:#dc2626;font-weight:600;font-size:.88rem;background:none;border:none;cursor:pointer">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach ?>
    <?php if (!$activities): ?><tr><td colspan="5" class="muted" style="text-align:center;padding:2rem"><?= $filtered ? 'No activities match your search.' : 'No activities yet.' ?></td></tr><?php endif ?>
    </tbody>
  </table>
  </div>
</div>

<?php else: ?>

<div class="clay reveal" style="padding:1.8rem;max-width:740px">
  <form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">
    <input type="hidden" name="existing_images" value="<?= e(json_encode($edit['images'] ?? [])) ?>">

    <div class="stack">
      <!-- Title -->
      <div>
        <label class="label">Title *</label>
        <input class="field" type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required>
      </div>

      <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr">
        <!-- Category -->
        <div>
          <label class="label">Category *</label>
          <select class="field" name="category" id="catSelect" onchange="toggleNewCat(this.value)">
            <?php
            $allCats = array_unique(array_merge(
                ['Sports','Education','Technology','Cultural','Skill Development'],
                $categories
            ));
            sort($allCats);
            $editCat = $edit['category'] ?? '';
            foreach ($allCats as $cat): ?>
            <option value="<?= e($cat) ?>" <?= $editCat === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach ?>
            <option value="__new__" <?= ($editCat && !in_array($editCat, $allCats)) ? 'selected' : '' ?>>＋ Add new category…</option>
          </select>
        </div>
        <!-- Date -->
        <div>
          <label class="label">Date *</label>
          <input class="field" type="date" name="date" value="<?= e($edit['date'] ?? date('Y-m-d')) ?>" required>
        </div>
      </div>

      <!-- New category input (hidden by default) -->
      <div id="newCatWrap" style="display:none">
        <label class="label">New Category Name *</label>
        <input class="field" type="text" name="new_category" id="newCatInput" placeholder="e.g. Music, Art, Field Trip…">
      </div>

      <!-- Description -->
      <div>
        <label class="label">Description *</label>
        <textarea class="field" name="description" rows="6" required><?= e($edit['description'] ?? '') ?></textarea>
      </div>

      <!-- Image Upload -->
      <div>
        <label class="label">Upload Images</label>

        <!-- Existing images preview -->
        <?php if (!empty($edit['images'])): ?>
        <div id="existingImgs" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:.8rem">
          <?php foreach ($edit['images'] as $img): ?>
          <div style="position:relative">
            <img src="<?= e($img) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:10px;box-shadow:var(--shadow-card)">
            <button type="button" onclick="removeExisting(this,'<?= e($img) ?>')"
              style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#ef4444;color:#fff;border:none;cursor:pointer;font-size:.75rem;line-height:1;display:grid;place-items:center">×</button>
          </div>
          <?php endforeach ?>
        </div>
        <?php endif ?>

        <!-- Drop zone -->
        <label for="imgInput" id="dropZone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.6rem 1rem;border:2px dashed var(--brand-300);border-radius:16px;background:var(--brand-50);cursor:pointer;transition:border-color .2s">
          <?= icon('image','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
          <span style="font-weight:600;color:var(--brand-700);font-size:.9rem">Click to select or drag &amp; drop</span>
          <span class="muted" style="font-size:.78rem">PNG, JPG, WebP — multiple allowed</span>
        </label>
        <input id="imgInput" type="file" name="images[]" accept="image/*" multiple style="display:none" onchange="previewImgs(this)">

        <!-- New image previews -->
        <div id="newPreviews" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.7rem"></div>
      </div>

      <div style="display:flex;gap:.8rem">
        <button type="submit" class="btn"><?= icon('check','w-4 h-4') ?> Save Activity</button>
        <a href="?action=list" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
function toggleNewCat(val) {
  const wrap = document.getElementById('newCatWrap');
  const inp  = document.getElementById('newCatInput');
  wrap.style.display = val === '__new__' ? '' : 'none';
  inp.required = val === '__new__';
}
// Init on page load
toggleNewCat(document.getElementById('catSelect').value);

function previewImgs(input) {
  const box = document.getElementById('newPreviews');
  box.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const url = URL.createObjectURL(file);
    const img = document.createElement('img');
    img.src = url;
    img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:10px;box-shadow:var(--shadow-card)';
    box.appendChild(img);
  });
}

// Drag-over style
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--brand-600)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--brand-300)'; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor = 'var(--brand-300)';
  document.getElementById('imgInput').files = e.dataTransfer.files;
  previewImgs(document.getElementById('imgInput'));
});

// Remove existing image from hidden input
function removeExisting(btn, url) {
  btn.parentNode.remove();
  const inp = document.querySelector('input[name="existing_images"]');
  const arr = JSON.parse(inp.value).filter(u => u !== url);
  inp.value = JSON.stringify(arr);
}
</script>

<?php endif ?>
<?php require APP_DIR . '/layout/admin_footer.php'; ?>
