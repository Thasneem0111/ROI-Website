<?php
// api_update_blog.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

header('Content-Type: application/json; charset=utf-8');
// Enable error reporting during debugging so we can return JSON on fatal errors
@ini_set('display_errors', 1);
@error_reporting(E_ALL);
ob_start();

// Register shutdown function to catch fatal errors and return JSON instead of HTML
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        @header('Content-Type: application/json; charset=utf-8');
        // clear any previous output
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Fatal error', 'error' => $err]);
        exit;
    }
});

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); exit; }

$blog_topic = isset($_POST['blog_topic']) ? trim($_POST['blog_topic']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : null;
$main_heading = isset($_POST['main_heading']) ? trim($_POST['main_heading']) : null;
$sub_heading = isset($_POST['sub_heading']) ? trim($_POST['sub_heading']) : null;
$normal_heading = isset($_POST['normal_heading']) ? trim($_POST['normal_heading']) : null;
$paragraph = isset($_POST['paragraph']) ? trim($_POST['paragraph']) : null;
$list = isset($_POST['list']) ? trim($_POST['list']) : null;

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

// fetch existing to remove old image if replaced
$oldImage = null;
$stmt = $conn->prepare('SELECT image FROM blog WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res) { $r = $res->fetch_assoc(); if ($r && !empty($r['image'])) $oldImage = $r['image']; }
$stmt->close();

// Build dynamic update only for columns that exist
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

$sets = [];
$values = [];
$types = '';

if (column_exists($conn,'blog','blog_topic') && $blog_topic !== null) { $sets[]='`blog_topic` = ?'; $values[]=$blog_topic; $types.='s'; }
if (column_exists($conn,'blog','description') && $description !== null) { $sets[]='`description` = ?'; $values[]=$description; $types.='s'; }
if (column_exists($conn,'blog','main_heading') && $main_heading !== null) { $sets[]='`main_heading` = ?'; $values[]=$main_heading; $types.='s'; }
if (column_exists($conn,'blog','sub_heading') && $sub_heading !== null) { $sets[]='`sub_heading` = ?'; $values[]=$sub_heading; $types.='s'; }
if (column_exists($conn,'blog','normal_heading') && $normal_heading !== null) { $sets[]='`normal_heading` = ?'; $values[]=$normal_heading; $types.='s'; }
if (column_exists($conn,'blog','paragraph') && $paragraph !== null) { $sets[]='`paragraph` = ?'; $values[]=$paragraph; $types.='s'; }
if (column_exists($conn,'blog','list') && $list !== null) { $sets[]='`list` = ?'; $values[]=$list; $types.='s'; }
if ($newImage !== null && column_exists($conn,'blog','image')) { $sets[]='`image` = ?'; $values[]=$newImage; $types.='s'; }

if (count($sets) === 0) { ob_clean(); echo json_encode(['success'=>false,'message'=>'No updatable columns']); exit; }

$sql = "UPDATE blog SET " . implode(', ', $sets) . " WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error]); exit; }

// bind params safely: build an array of references for call_user_func_array
$typesAll = $types . 'i'; // include the trailing 'i' for the id
$params = array();
$params[] = & $typesAll;
// ensure $values entries are variables so we can take references
for ($i = 0; $i < count($values); $i++) {
    $params[] = & $values[$i];
}
$params[] = & $id;
// call bind_param with references
call_user_func_array(array($stmt, 'bind_param'), $params);

if ($stmt->execute()) {
    $stmt->close();
    // remove old image if replaced
    if ($newImage && $oldImage) {
        $oldFile = __DIR__ . '/../images/' . $oldImage;
        if (is_file($oldFile)) @unlink($oldFile);
    }
    ob_clean();
    echo json_encode(['success'=>true,'id'=>$id,'blog_topic'=>$blog_topic,'description'=>$description,'image'=>$newImage?('/images/'.$newImage):null]);
} else {
    $err = $stmt->error;
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'DB update failed: '.$err]);
}

$conn->close();
exit;
?>
