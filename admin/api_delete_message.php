<?php
// admin/api_delete_message.php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || empty($data['createdAt'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid request']);
    exit;
}

$targets = $data['createdAt'];
if (!is_array($targets)) $targets = [$targets];
$targets = array_map('intval', $targets);

$store = __DIR__ . '/../api/messages.json';
if (!file_exists($store)) {
    echo json_encode(['ok' => true, 'deleted' => 0]);
    exit;
}

$raw = file_get_contents($store);
$items = json_decode($raw, true);
if (!is_array($items)) $items = [];

$before = count($items);
$filtered = array_filter($items, function($it) use ($targets) {
    $ts = isset($it['createdAt']) ? (int)$it['createdAt'] : 0;
    return !in_array($ts, $targets, true);
});
$after = count($filtered);
$deleted = $before - $after;

// Reindex array and write back
$filtered = array_values($filtered);
try {
    $tmp = $store . '.tmp';
    file_put_contents($tmp, json_encode($filtered, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    rename($tmp, $store);
} catch (Throwable $e) {
    error_log('Failed to write messages.json in delete API: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not delete messages']);
    exit;
}

echo json_encode(['ok' => true, 'deleted' => $deleted]);
exit;

