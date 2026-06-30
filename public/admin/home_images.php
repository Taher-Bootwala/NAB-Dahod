<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';

// Upload each selected image to the "home" Storage bucket (or local fallback).
function saveHomeImages(array $files): array
{
    $urls = [];
    foreach ($files['tmp_name'] as $i => $tmp) {
        $url = store_image((string) $tmp, (int) $files['error'][$i], BUCKET_HOME, 'home');
        if ($url) $urls[] = $url;
    }
    return $urls;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'delete' && $id) {
        $repo->remove('home_images', $id);
        $repo->audit('home_image_delete', $id);
        $msg = 'Image deleted.';
        $action = 'list';
    } elseif (($_POST['_action'] ?? '') === 'save') {
        $uploaded = [];
        if (!empty($_FILES['images']['tmp_name'][0])) {
            $uploaded = saveHomeImages($_FILES['images']);
        }
        $title = trim($_POST['title'] ?? '');
        foreach ($uploaded as $url) {
            $row = $repo->create('home_images', ['image_url' => $url, 'title' => $title]);
            $repo->audit('home_image_create', $row['id'] ?? '');
        }
        $msg = $uploaded ? (count($uploaded) . ' image(s) uploaded.') : 'No valid images selected.';
        $action = 'list';
    }
}

$images = $repo->homeImages();

$adminPage   = 'home_images';
$pageHeading = $action === 'list' ? 'Display Images — Home Page' : 'Upload Images';
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.8rem 1.2rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;display:flex;gap:.5rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>

<?php if ($action === 'list'): ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;flex-wrap:wrap;gap:.6rem">
  <span class="muted"><?= count($images) ?> image(s) shown on the home page</span>
  <a href="?action=new" class="btn"><?= icon('image','w-4 h-4') ?> Upload Images</a>
</div>

<div style="display:grid;gap:1.1rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
  <?php foreach ($images as $g): ?>
  <div class="clay img-zoom" style="overflow:hidden">
    <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['title'] ?? '') ?>"
      style="width:100%;height:160px;object-fit:cover;display:block"
      onerror="this.style.background='var(--bg-soft)';this.src=''">
    <div style="padding:.9rem;display:flex;justify-content:space-between;align-items:center;gap:.6rem">
      <span style="font-weight:600;color:var(--ink);font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($g['title'] ?: 'Untitled') ?></span>
      <form method="POST" action="?id=<?= urlencode($g['id']) ?>" class="inline" onsubmit="return confirm('Delete this image?')">
        <?= csrf_field() ?>
        <input type="hidden" name="_action" value="delete">
        <button type="submit" style="color:#dc2626;font-weight:600;font-size:.82rem;background:none;border:none;cursor:pointer">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach ?>
  <?php if (!$images): ?>
  <div class="clay" style="grid-column:1/-1;padding:2.5rem;text-align:center">
    <div style="display:inline-grid;place-items:center;width:56px;height:56px;border-radius:16px;background:var(--brand-50);margin-bottom:.8rem"><?= icon('image','w-7 h-7',['style'=>'color:var(--brand-400)']) ?></div>
    <p class="muted" style="margin:0">No images yet. Upload images to display them on the home page.</p>
  </div>
  <?php endif ?>
</div>

<?php else: ?>

<div class="clay reveal" style="padding:1.8rem;max-width:680px">
  <form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">
    <div class="stack">
      <div>
        <label class="label">Caption (optional)</label>
        <input class="field" type="text" name="title" placeholder="e.g. Annual Day celebration">
      </div>

      <div>
        <label class="label">Images *</label>
        <label for="imgInput" id="dropZone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.6rem 1rem;border:2px dashed var(--brand-300);border-radius:16px;background:var(--brand-50);cursor:pointer;transition:border-color .2s">
          <?= icon('image','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
          <span style="font-weight:600;color:var(--brand-700);font-size:.9rem">Click to select or drag &amp; drop</span>
          <span class="muted" style="font-size:.78rem">PNG, JPG, WebP — multiple allowed</span>
        </label>
        <input id="imgInput" type="file" name="images[]" accept="image/*" multiple required style="display:none" onchange="previewImgs(this)">
        <div id="newPreviews" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.7rem"></div>
      </div>

      <div style="display:flex;gap:.8rem">
        <button type="submit" class="btn"><?= icon('check','w-4 h-4') ?> Upload</button>
        <a href="?action=list" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
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
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--brand-600)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--brand-300)'; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor = 'var(--brand-300)';
  document.getElementById('imgInput').files = e.dataTransfer.files;
  previewImgs(document.getElementById('imgInput'));
});
</script>

<?php endif ?>
<?php require APP_DIR . '/layout/admin_footer.php'; ?>
