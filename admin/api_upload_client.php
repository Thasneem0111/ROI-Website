<?php
// admin/api_upload_client.php
// Accepts FormData (name/title, description, image) and inserts into `clients` table.

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

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$work_category = isset($_POST['work_category']) ? trim($_POST['work_category']) : '';
$new_clients = isset($_POST['new_clients']) ? intval($_POST['new_clients']) : null;
$conversation_rate = isset($_POST['conversation_rate']) ? floatval($_POST['conversation_rate']) : null;
$seo_rank = isset($_POST['seo_rank']) ? intval($_POST['seo_rank']) : null;
if ($name === '') { ob_clean(); echo json_encode(['success'=>false,'message'=>'Name required']); exit; }

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

$useName = column_exists($conn, 'clients', 'name');
$useTitle = !$useName && column_exists($conn, 'clients', 'title');

// Build insert dynamically depending on available columns
$cols = [];
$placeholders = [];
$values = [];
$types = '';

// title/name
if ($useName) { $cols[] = '`name`'; $placeholders[] = '?'; $values[] = $name; $types .= 's'; }
elseif ($useTitle) { $cols[] = '`title`'; $placeholders[] = '?'; $values[] = $name; $types .= 's'; }

// description
if (column_exists($conn, 'clients', 'description')) { $cols[] = '`description`'; $placeholders[] = '?'; $values[] = $description; $types .= 's'; }

// image
if (column_exists($conn, 'clients', 'image')) { $cols[] = '`image`'; $placeholders[] = '?'; $values[] = $imageFilename ? $imageFilename : null; $types .= 's'; }

// category
if (column_exists($conn, 'clients', 'category')) { $cols[] = '`category`'; $placeholders[] = '?'; $values[] = $category; $types .= 's'; }

// work_category
if (column_exists($conn, 'clients', 'work_category')) { $cols[] = '`work_category`'; $placeholders[] = '?'; $values[] = $work_category; $types .= 's'; }

// new_clients
if (column_exists($conn, 'clients', 'new_clients')) { $cols[] = '`new_clients`'; $placeholders[] = '?'; $values[] = $new_clients !== null ? $new_clients : 0; $types .= 'i'; }

// conversation_rate
if (column_exists($conn, 'clients', 'conversation_rate')) { $cols[] = '`conversation_rate`'; $placeholders[] = '?'; $values[] = $conversation_rate !== null ? $conversation_rate : 0.0; $types .= 'd'; }

// seo_rank
if (column_exists($conn, 'clients', 'seo_rank')) { $cols[] = '`seo_rank`'; $placeholders[] = '?'; $values[] = $seo_rank !== null ? $seo_rank : 0; $types .= 'i'; }

// created_at default handled by DB; if column exists but requires explicit NOW, skip — DB default will set it

if (count($cols) === 0) { ob_clean(); echo json_encode(['success'=>false,'message'=>'No columns available to insert']); exit; }

$sql = "INSERT INTO clients (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error, 'sql'=>$sql]); exit; }

// bind params dynamically
$bind_names = [];
if ($types !== '') {
    $bind_names[] = & $types;
    for ($i=0;$i<count($values);$i++) {
        $bind_names[] = & $values[$i];
    }
    call_user_func_array(array($stmt,'bind_param'), $bind_names);
}

if ($stmt->execute()) {
    $insertId = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    ob_clean();
    $resp = ['success'=>true,'id'=>$insertId,'name'=>$name,'description'=>$description,'image'=> $imageFilename ? ('/images/'.$imageFilename) : null, 'createdAt'=>date('Y-m-d H:i:s')];
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
