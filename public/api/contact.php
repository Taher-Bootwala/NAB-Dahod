<?php
/** POST /api/contact.php — store a contact inquiry. */
require __DIR__ . '/../../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$in = request_body();

// CSRF check
if (!csrf_check($in['csrf'] ?? null)) {
    json_out(['ok' => false, 'message' => 'Invalid request. Please reload the page.'], 403);
}

// Honeypot (bots fill hidden "website" field)
if (!empty($in['website'])) {
    json_out(['ok' => true, 'message' => 'Thank you!']); // silently accept, do nothing
}

// Rate limit
if (!rate_limit('contact', 5, 600)) {
    json_out(['ok' => false, 'message' => 'Too many messages. Please try again in a few minutes.'], 429);
}

$name = trim((string)($in['name'] ?? ''));
$email = trim((string)($in['email'] ?? ''));
$phone = trim((string)($in['phone'] ?? ''));
$message = trim((string)($in['message'] ?? ''));

$errors = [];
if (mb_strlen($name) < 2) $errors[] = 'Please enter your name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
if (mb_strlen($message) < 5) $errors[] = 'Please enter a message.';
if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) $errors[] = 'Phone number looks invalid.';

if ($errors) {
    json_out(['ok' => false, 'message' => implode(' ', $errors)], 422);
}

$saved = $repo->saveContactMessage([
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'message' => $message,
]);

// Best-effort confirmation email (works if PHP mail() is configured; ignored otherwise).
@mail(
    $email,
    'We received your message — ' . SITE_NAME,
    "Dear {$name},\n\nThank you for reaching out to " . SITE_NAME . ". We have received your message and will reply shortly.\n\nYour message:\n{$message}\n\nWarm regards,\n" . SITE_NAME,
    'From: ' . ORG_EMAIL
);

json_out(['ok' => true, 'message' => 'Thank you! We have received your message and will reply soon.']);
