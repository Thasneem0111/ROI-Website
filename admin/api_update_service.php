<?php
// CORS for local dev
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

define('API_MODE', true);
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : null;
if (!$id || $name === '') { echo json_encode(['success'=>false,'message'=>'Invalid input']); exit; }

$allowed = ['jpg','jpeg','png','webp','avif','gif','jfif'];
$target_dir = __DIR__ . '/../images/';
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

$newImage = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $origName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Invalid image type']); exit; }
    $newImage = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $target_file = $target_dir . $newImage;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Error uploading image']); exit; }
}

// detect existing columns
$hasCategory = false;
try {
    $r = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='services' AND COLUMN_NAME='category'");
    if ($r) { $hasCategory = (bool)$r->num_rows; if (method_exists($r,'free')) $r->free(); }
} catch (Throwable $e) {}

if ($newImage) {
    if ($hasCategory && $category !== null && $category !== '') {
        $stmt = $conn->prepare("UPDATE services SET `title` = ?, `description` = ?, `image` = ?, `category` = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('ssssi', $name, $description, $newImage, $category, $id);
    } else {
        $stmt = $conn->prepare("UPDATE services SET `title` = ?, `description` = ?, `image` = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('sssi', $name, $description, $newImage, $id);
    }
} else {
    if ($hasCategory && $category !== null && $category !== '') {
        $stmt = $conn->prepare("UPDATE services SET `title` = ?, `description` = ?, `category` = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('sssi', $name, $description, $category, $id);
    } else {
        $stmt = $conn->prepare("UPDATE services SET `title` = ?, `description` = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('ssi', $name, $description, $id);
    }
}

if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed']); exit; }
if ($stmt->execute()) {
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>true,'id'=>$id,'name'=>$name,'description'=>$description,'category'=> ($hasCategory ? $category : null),'image'=>$newImage ? ('/images/'.$newImage) : null]);
} else {
    $err = $stmt->error;
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'DB update failed: '.$err]);
}
$conn->close();
exit;
?>
