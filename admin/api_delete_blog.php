<?php
// api_delete_blog.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@error_reporting(0);
ob_start();

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

// fetch record to get image
$stmt = $conn->prepare('SELECT image FROM blog WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row && !empty($row['image'])) {
    $file = __DIR__ . '/../images/' . $row['image'];
    if (is_file($file)) @unlink($file);
}

$stmt = $conn->prepare('DELETE FROM blog WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>true]);
} else {
    $err = $stmt->error;
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'DB delete failed: '.$err]);
}
$conn->close();
exit;
?>
