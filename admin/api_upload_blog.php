<?php
// admin/api_upload_blog.php
// Accepts FormData to insert a blog into `blog` table.

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

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

$blog_topic = isset($_POST['blog_topic']) ? trim($_POST['blog_topic']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$main_heading = isset($_POST['main_heading']) ? trim($_POST['main_heading']) : '';
$sub_heading = isset($_POST['sub_heading']) ? trim($_POST['sub_heading']) : '';
$normal_heading = isset($_POST['normal_heading']) ? trim($_POST['normal_heading']) : '';
$paragraph = isset($_POST['paragraph']) ? trim($_POST['paragraph']) : '';
$list = isset($_POST['list']) ? trim($_POST['list']) : '';

if ($blog_topic === '') { ob_clean(); echo json_encode(['success'=>false,'message'=>'Blog topic required']); exit; }

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

function column_exists_blog($conn, $table, $col) {
    $db = $conn->real_escape_string($conn->query('SELECT DATABASE()')->fetch_row()[0]);
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($col);
    $q = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$db."' AND TABLE_NAME='".$t."' AND COLUMN_NAME='".$c."'";
    $r = $conn->query($q);
    if (!$r) return false;
    $row = $r->fetch_row();
    return intval($row[0]) > 0;
}

$cols = [];
$placeholders = [];
$values = [];
$types = '';

if (column_exists_blog($conn, 'blog', 'blog_topic')) { $cols[]='`blog_topic`'; $placeholders[]='?'; $values[]=$blog_topic; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'description')) { $cols[]='`description`'; $placeholders[]='?'; $values[]=$description; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'image')) { $cols[]='`image`'; $placeholders[]='?'; $values[]=$imageFilename ? $imageFilename : null; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'main_heading')) { $cols[]='`main_heading`'; $placeholders[]='?'; $values[]=$main_heading; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'sub_heading')) { $cols[]='`sub_heading`'; $placeholders[]='?'; $values[]=$sub_heading; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'normal_heading')) { $cols[]='`normal_heading`'; $placeholders[]='?'; $values[]=$normal_heading; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'paragraph')) { $cols[]='`paragraph`'; $placeholders[]='?'; $values[]=$paragraph; $types.='s'; }
if (column_exists_blog($conn, 'blog', 'list')) { $cols[]='`list`'; $placeholders[]='?'; $values[]=$list; $types.='s'; }

if (count($cols) === 0) { ob_clean(); echo json_encode(['success'=>false,'message'=>'No columns available to insert']); exit; }

$sql = "INSERT INTO blog (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error, 'sql'=>$sql]); exit; }

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
    $resp = ['success'=>true,'id'=>$insertId,'blog_topic'=>$blog_topic,'description'=>$description,'image'=> $imageFilename ? ('/images/'.$imageFilename) : null];
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
