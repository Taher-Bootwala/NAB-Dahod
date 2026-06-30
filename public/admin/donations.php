<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
require_csrf();

$msg = '';
$err = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $err = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Change UPI ID
        if ($action === 'upi') {
        $upi = trim($_POST['upi_id'] ?? '');
        $payee = trim($_POST['payee_name'] ?? '');
        if ($upi !== '') {
            $repo->saveSetting('upi_id', $upi);
            if ($payee !== '') $repo->saveSetting('upi_payee_name', $payee);
            $repo->audit('upi_update', 'UPI ID changed to ' . $upi);
            $msg = 'UPI ID updated successfully.';
        } else {
            $err = 'UPI ID cannot be empty.';
        }
    }

    // Upload custom QR image
    if ($action === 'qr_upload') {
        $file = $_FILES['qr_image'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // Check file size (5 MB max)
            if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
                $err = 'File too large. Maximum size is ' . round(MAX_UPLOAD_BYTES / 1024 / 1024, 1) . ' MB.';
            } else {
                $mime = mime_content_type($file['tmp_name']);
                if (!in_array($mime, ['image/png','image/jpeg','image/webp','image/gif'], true)) {
                    $err = 'Only PNG, JPG, or WebP images allowed.';
                } else {
                    // Verify it's a valid image
                    if (@getimagesize($file['tmp_name']) === false) {
                        $err = 'Invalid image file.';
                    } else {
                        $ext = match($mime) { 'image/jpeg' => 'jpg', 'image/webp' => 'webp', default => 'png' };
                        $dest = PUBLIC_DIR . '/assets/img/payment_qr.' . $ext;
                        // Remove old QR files
                        foreach (glob(PUBLIC_DIR . '/assets/img/payment_qr.*') as $old) @unlink($old);
                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            $repo->saveSetting('donation_qr', '/assets/img/payment_qr.' . $ext);
                            $repo->audit('qr_upload', 'QR image updated');
                            $msg = 'QR image updated successfully.';
                        } else {
                            $err = 'Failed to save image. Check folder permissions.';
                        }
                    }
                }
            }
        } else {
            $err = 'No file uploaded or upload error.';
        }
    }
    }
}

// Current values (from settings override, else .env constants)
$currentUpi   = $repo->setting('upi_id',   UPI_ID);
$currentPayee = $repo->setting('upi_payee_name', UPI_PAYEE_NAME);
$currentQr    = $repo->setting('donation_qr', '/assets/img/payment_qr.png');

$adminPage  = 'donations';
$pageHeading = 'Donations & Payment Settings';
require APP_DIR . '/layout/admin_header.php';
?>

<?php if ($msg): ?>
<div class="glass" style="padding:.9rem 1.2rem;margin-bottom:1.2rem;border-radius:14px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-weight:600;display:flex;gap:.6rem;align-items:center">
  <?= icon('check','w-5 h-5') ?> <?= e($msg) ?>
</div>
<?php endif ?>
<?php if ($err): ?>
<div class="glass" style="padding:.9rem 1.2rem;margin-bottom:1.2rem;border-radius:14px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-weight:600;display:flex;gap:.6rem;align-items:center">
  <?= icon('close','w-5 h-5') ?> <?= e($err) ?>
</div>
<?php endif ?>

<!-- Payment Settings -->
<div style="display:grid;gap:1.4rem;grid-template-columns:1fr 1fr;margin-bottom:2rem">

  <!-- Change UPI ID -->
  <div class="clay reveal" style="padding:1.6rem">
    <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
      <?= icon('settings','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> UPI Payment ID
    </h2>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="upi">
      <div style="margin-bottom:1rem">
        <label class="label">UPI ID</label>
        <input class="field" type="text" name="upi_id"
          value="<?= e($currentUpi) ?>"
          placeholder="example@upi" required>
      </div>
      <div style="margin-bottom:1.2rem">
        <label class="label">Payee Name</label>
        <input class="field" type="text" name="payee_name"
          value="<?= e($currentPayee) ?>"
          placeholder="Organisation name">
      </div>
      <button type="submit" class="btn" style="width:100%">
        <?= icon('check','w-4 h-4') ?> Save UPI ID
      </button>
    </form>
    <div style="margin-top:1rem;padding:.8rem;background:var(--brand-50);border-radius:12px">
      <p style="margin:0;font-size:.82rem;color:var(--ink-soft)">Current: <strong><?= e($currentUpi) ?></strong></p>
    </div>
  </div>

  <!-- Upload QR Image -->
  <div class="clay reveal" style="padding:1.6rem;transition-delay:.1s">
    <h2 style="font-size:1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem">
      <?= icon('image','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> Donation QR Code
    </h2>

    <!-- Current QR preview -->
    <div style="text-align:center;margin-bottom:1.2rem">
      <div style="background:#fff;padding:10px;border-radius:16px;display:inline-block;box-shadow:var(--shadow-clay)">
        <img src="<?= e($currentQr) ?>?v=<?= time() ?>"
          alt="Current QR" width="160" height="160"
          style="display:block;border-radius:8px;object-fit:contain"
          onerror="this.src='/assets/img/payment_qr.png'">
      </div>
      <p class="muted" style="font-size:.78rem;margin-top:.5rem">Current QR</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="qr_upload">
      <div style="margin-bottom:1.2rem">
        <label class="label">Upload New QR Image</label>
        <input type="file" name="qr_image" accept="image/png,image/jpeg,image/webp" required
          style="width:100%;padding:.8rem;border:1.5px dashed var(--brand-300);border-radius:14px;background:var(--brand-50);color:var(--ink);cursor:pointer;font:inherit">
        <p class="muted" style="font-size:.78rem;margin-top:.4rem">PNG, JPG or WebP. Max 5MB.</p>
      </div>
      <button type="submit" class="btn" style="width:100%">
        <?= icon('image','w-4 h-4') ?> Upload QR Image
      </button>
    </form>
  </div>

</div>



<?php require APP_DIR . '/layout/admin_footer.php'; ?>
