<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$err = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';

// Upload the report PDF to the "progress" Storage bucket (or local fallback).
function saveProgressPdf(array $file): ?string
{
    return store_pdf(
        (string) ($file['tmp_name'] ?? ''),
        (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
        BUCKET_PROGRESS,
        'progress'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'delete' && $id) {
        $repo->remove('progress_reports', $id);
        $repo->audit('progress_delete', $id);
        $msg = 'Report deleted.';
        $action = 'list';
    } elseif (($_POST['_action'] ?? '') === 'save') {
        $title = trim($_POST['title'] ?? '');
        $year  = (int) ($_POST['year'] ?? date('Y'));
        $pdfUrl = saveProgressPdf($_FILES['pdf'] ?? []);

        if ($title === '') {
            $err = 'Please enter a title for the report.';
            $action = 'new';
        } elseif (!$pdfUrl) {
            $err = 'Please upload a valid PDF (max ' . (int) (MAX_PDF_BYTES / 1024 / 1024) . ' MB).';
            $action = 'new';
        } else {
            $row = $repo->create('progress_reports', [
                'title'   => $title,
                'year'    => $year,
                'pdf_url' => $pdfUrl,
            ]);
            $repo->audit('progress_create', $row['id'] ?? '');
            $msg = 'Report added.';
            $action = 'list';
        }
    }
}

$reports = $repo->progressReports();

$adminPage   = 'progress';
$pageHeading = $action === 'list' ? 'Progress of NAB Dahod' : 'Add Progress Report';
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

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;flex-wrap:wrap;gap:.6rem">
  <span class="muted"><?= count($reports) ?> report(s) published on the “Our Progress” page</span>
  <a href="?action=new" class="btn"><?= icon('document','w-4 h-4') ?> Add Report</a>
</div>

<?php if ($reports): ?>
<div class="clay" style="padding:.4rem 1.2rem">
  <table class="a-table">
    <thead>
      <tr><th>Title</th><th style="width:90px">Year</th><th style="width:110px">File</th><th style="width:90px;text-align:right">Action</th></tr>
    </thead>
    <tbody>
      <?php foreach ($reports as $r): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:.7rem">
            <span style="flex:none;display:grid;place-items:center;width:36px;height:36px;border-radius:10px;background:var(--brand-50)"><?= icon('document','w-5 h-5',['style'=>'color:var(--brand-600)']) ?></span>
            <span style="font-weight:600;color:var(--ink)"><?= e($r['title'] ?: 'Untitled') ?></span>
          </div>
        </td>
        <td><span class="badge"><?= e((string) ($r['year'] ?? '')) ?></span></td>
        <td>
          <a href="<?= attr($r['pdf_url']) ?>" target="_blank" rel="noopener" style="color:var(--brand-600);font-weight:600;font-size:.85rem;display:inline-flex;align-items:center;gap:.3rem">
            <?= icon('eye','w-4 h-4') ?> View
          </a>
        </td>
        <td style="text-align:right">
          <form method="POST" action="?id=<?= urlencode($r['id']) ?>" class="inline" onsubmit="return confirm('Delete this report?')">
            <?= csrf_field() ?>
            <input type="hidden" name="_action" value="delete">
            <button type="submit" style="color:#dc2626;font-weight:600;font-size:.82rem;background:none;border:none;cursor:pointer">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="clay" style="padding:2.5rem;text-align:center">
  <div style="display:inline-grid;place-items:center;width:56px;height:56px;border-radius:16px;background:var(--brand-50);margin-bottom:.8rem"><?= icon('document','w-7 h-7',['style'=>'color:var(--brand-400)']) ?></div>
  <p class="muted" style="margin:0">No reports yet. Add a progress report PDF to publish it on the “Our Progress” page.</p>
</div>
<?php endif ?>

<?php else: ?>

<div class="clay reveal" style="padding:1.8rem;max-width:600px">
  <form method="POST" enctype="multipart/form-data" onsubmit="return validateProgress()">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="save">
    <div class="stack">
      <div>
        <label class="label">Title *</label>
        <input class="field" type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" placeholder="e.g. Annual Progress Report" required>
      </div>
      <div>
        <label class="label">Year *</label>
        <input class="field" type="number" name="year" min="1990" max="2100" step="1" value="<?= e($_POST['year'] ?? date('Y')) ?>" required style="max-width:180px">
      </div>
      <div>
        <label class="label">PDF Document *</label>
        <label for="pdfInput" id="dropZone" style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.6rem 1rem;border:2px dashed var(--brand-300);border-radius:16px;background:var(--brand-50);cursor:pointer;transition:border-color .2s">
          <?= icon('document','w-8 h-8',['style'=>'color:var(--brand-400)']) ?>
          <span style="font-weight:600;color:var(--brand-700);font-size:.9rem">Click to upload or drag &amp; drop</span>
          <span class="muted" style="font-size:.78rem">PDF only — max <?= (int) (MAX_PDF_BYTES / 1024 / 1024) ?> MB</span>
        </label>
        <input id="pdfInput" type="file" name="pdf" accept="application/pdf" required style="display:none" onchange="showPdf(this)">
        <div id="pdfName" class="muted" style="margin-top:.7rem;font-size:.85rem"></div>
      </div>
      <div style="display:flex;gap:.8rem">
        <button type="submit" class="btn"><?= icon('check','w-4 h-4') ?> Save</button>
        <a href="?action=list" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
function showPdf(input) {
  const box = document.getElementById('pdfName');
  const f = input.files[0];
  box.textContent = f ? ('Selected: ' + f.name) : '';
}
function validateProgress() {
  if (document.getElementById('pdfInput').files.length === 0) {
    alert('Please select a PDF file.');
    document.getElementById('dropZone').style.borderColor = '#ef4444';
    return false;
  }
  return true;
}
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--brand-600)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--brand-300)'; });
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor = 'var(--brand-300)';
  document.getElementById('pdfInput').files = e.dataTransfer.files;
  showPdf(document.getElementById('pdfInput'));
});
</script>

<?php endif ?>
<?php require APP_DIR . '/layout/admin_footer.php'; ?>
