<?php
/** GET /api/qr.php?amount=500 — redirect to a scannable UPI QR image for the amount. */
require __DIR__ . '/../../app/bootstrap.php';

$amount = (float)($_GET['amount'] ?? 0);
$size = min(600, max(160, (int)($_GET['size'] ?? 280)));

// If admin uploaded a custom static QR, prefer it.
$custom = $repo->setting('donation_qr', '');
if ($custom !== '') {
    redirect($custom);
}

header('Cache-Control: public, max-age=86400');
// Static merchant QR shipped with the site.
redirect(upi_qr_url($amount > 0 ? $amount : null, $size));
