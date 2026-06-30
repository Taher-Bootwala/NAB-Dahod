<?php
/** POST /api/donate.php — record a donation intent and return receipt details. */
require __DIR__ . '/../../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$in = request_body();

// CSRF check
if (!csrf_check($in['csrf'] ?? null)) {
    json_out(['ok' => false, 'message' => 'Invalid request. Please reload the page.'], 403);
}

if (!empty($in['website'])) {
    json_out(['ok' => true, 'message' => 'Thank you!']);
}
if (!rate_limit('donate', 12, 600)) {
    json_out(['ok' => false, 'message' => 'Too many requests. Please slow down.'], 429);
}

$amount = (float)($in['amount'] ?? 0);
$name = trim((string)($in['donor_name'] ?? 'Anonymous'));
$email = trim((string)($in['email'] ?? ''));
$message = trim((string)($in['message'] ?? ''));
// Always record as pending - only admin can mark as success after verification
$status = 'pending';

if ($amount < 1) {
    json_out(['ok' => false, 'message' => 'Please enter a valid amount.'], 422);
}
if ($amount > 1000000) {
    json_out(['ok' => false, 'message' => 'For donations above ₹10,00,000 please contact us directly.'], 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['ok' => false, 'message' => 'Please enter a valid email for your receipt.'], 422);
}

$row = $repo->recordDonation([
    'donor_name' => $name ?: 'Anonymous',
    'email' => $email,
    'amount' => $amount,
    'message' => $message,
    'payment_status' => $status,
]);

json_out([
    'ok' => true,
    'message' => 'Donation recorded. Thank you for your generosity!',
    'receipt_no' => $row['receipt_no'] ?? null,
    'transaction_id' => $row['transaction_id'] ?? null,
    'amount' => $amount,
    'upi' => UPI_ID,
]);
