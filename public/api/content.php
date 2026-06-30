<?php
/** GET /api/content.php?type=activities|gallery&category=&search= — JSON feed for live filtering. */
require __DIR__ . '/../../app/bootstrap.php';

// CSRF for POST operations (if needed in future)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check($_POST['csrf'] ?? null)) {
    json_out(['ok' => false, 'message' => 'Invalid request.'], 403);
}

$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

switch ($type) {
    case 'activities':
        json_out(['ok' => true, 'items' => $repo->activities(['category' => $category, 'search' => $search])]);
    case 'gallery':
        json_out(['ok' => true, 'items' => $repo->gallery($category)]);
    case 'donations':
        json_out(['ok' => true, 'stats' => $repo->donationStats(), 'recent' => $repo->recentDonations(8)]);
    default:
        json_out(['ok' => false, 'message' => 'Unknown content type'], 400);
}
