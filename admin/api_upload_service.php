<?php
// admin/api_upload_service.php
// Accepts FormData (name/title, description, image) and inserts into `services` table.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@error_reporting(0);
ob_start();

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

define('API_MODE', true);
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
if ($name === '') { ob_clean(); echo json_encode(['success'=>false,'message'=>'Name required']); exit; }
if ($category === '') { ob_clean(); echo json_encode(['success'=>false,'message'=>'Category required']); exit; }

$allowed = ['jpg','jpeg','png','webp','avif','gif','jfif'];
$target_dir = __DIR__ . '/../images/';
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

$imageFilename = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $origName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Invalid image type']); exit; }
    $imageFilename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $target_file = $target_dir . $imageFilename;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Error uploading image']); exit; }
}

if (!$conn) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB connection failed']); exit; }

function column_exists($conn, $table, $col) {
    $db = $conn->real_escape_string($conn->query('SELECT DATABASE()')->fetch_row()[0]);
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($col);
    $q = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$db."' AND TABLE_NAME='".$t."' AND COLUMN_NAME='".$c."'";
    $r = $conn->query($q);
    if (!$r) return false;
    $row = $r->fetch_row();
    return intval($row[0]) > 0;
}

$useName = column_exists($conn, 'services', 'name');
$useTitle = !$useName && column_exists($conn, 'services', 'title');
$hasCategory = column_exists($conn, 'services', 'category');

if ($useName && $hasCategory) {
    $insertSql = "INSERT INTO services (`name`, `description`, `image`, `category`, `createdAt`) VALUES (?, ?, ?, ?, NOW())";
} elseif ($useName) {
    $insertSql = "INSERT INTO services (`name`, `description`, `image`, `createdAt`) VALUES (?, ?, ?, NOW())";
} elseif ($useTitle && $hasCategory) {
    $insertSql = "INSERT INTO services (`title`, `description`, `image`, `category`, `created_at`) VALUES (?, ?, ?, ?, NOW())";
} elseif ($useTitle) {
    $insertSql = "INSERT INTO services (`title`, `description`, `image`, `created_at`) VALUES (?, ?, ?, NOW())";
} else {
    // Fallback insert when neither `name` nor `title` exists
    $insertSql = $hasCategory
        ? "INSERT INTO services (`description`, `image`, `category`) VALUES (?, ?, ?)"
        : "INSERT INTO services (`description`, `image`) VALUES (?, ?)";
}

$stmt = $conn->prepare($insertSql);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error]); exit; }

$imgParam = $imageFilename ?: null;
if ($useName && $hasCategory) {
    $stmt->bind_param('ssss', $name, $description, $imgParam, $category);
} elseif ($useName) {
    $stmt->bind_param('sss', $name, $description, $imgParam);
} elseif ($useTitle && $hasCategory) {
    $stmt->bind_param('ssss', $name, $description, $imgParam, $category);
} elseif ($useTitle) {
    $stmt->bind_param('sss', $name, $description, $imgParam);
} else {
    if ($hasCategory) {
        $stmt->bind_param('sss', $description, $imgParam, $category);
    } else {
        $stmt->bind_param('ss', $description, $imgParam);
    }
}
if ($stmt->execute()) {
    $insertId = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    ob_clean();
    $resp = ['success'=>true,'id'=>$insertId,'name'=>$name,'description'=>$description,'category'=>$hasCategory?$category:null,'image'=> $imageFilename ? ('/images/'.$imageFilename) : null, 'createdAt'=>date('Y-m-d H:i:s')];
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
} else {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'DB insert failed: '.$err]);
    exit;
}

?>
