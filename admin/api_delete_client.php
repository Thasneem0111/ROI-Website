<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@error_reporting(0);
ob_start();

session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

include 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

// find image to delete
$row = null;
$res = $conn->query("SELECT image FROM clients WHERE id = " . $id . " LIMIT 1");
if ($res && $res->num_rows) { $row = $res->fetch_assoc(); }

$stmt = $conn->prepare("DELETE FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed']); exit; }
if ($stmt->execute()) {
    $stmt->close();
    // delete image file if exists
    if ($row && !empty($row['image'])) {
        $path = __DIR__ . '/../images/' . basename($row['image']);
        if (file_exists($path)) @unlink($path);
    }
    ob_clean(); echo json_encode(['success'=>true,'id'=>$id]);
} else {
    $err = $stmt->error;
    $stmt->close();
    ob_clean(); echo json_encode(['success'=>false,'message'=>'DB delete failed: '.$err]);
}
$conn->close();
exit;
?>
