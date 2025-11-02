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
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$work_category = isset($_POST['work_category']) ? trim($_POST['work_category']) : '';
$new_clients = isset($_POST['new_clients']) ? intval($_POST['new_clients']) : null;
$conversation_rate = isset($_POST['conversation_rate']) ? floatval($_POST['conversation_rate']) : null;
$seo_rank = isset($_POST['seo_rank']) ? intval($_POST['seo_rank']) : null;
if (!$id || $name === '') { echo json_encode(['success'=>false,'message'=>'Invalid input']); exit; }

$allowed = ['jpg','jpeg','png','webp','avif','gif','jfif'];
$target_dir = __DIR__ . '/../images/';
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

// preserve and optionally replace image
$newImage = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $origName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Invalid image type']); exit; }
    $newImage = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $target_file = $target_dir . $newImage;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) { ob_clean(); echo json_encode(['success'=>false,'message'=>'Error uploading image']); exit; }
}
// build dynamic update set based on columns present
function column_exists_local($conn, $table, $col) {
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

// title/name
if (column_exists_local($conn, 'clients', 'title')) { $sets[] = '`title` = ?'; $values[] = $name; $types .= 's'; }
elseif (column_exists_local($conn, 'clients', 'name')) { $sets[] = '`name` = ?'; $values[] = $name; $types .= 's'; }

if (column_exists_local($conn, 'clients', 'description')) { $sets[] = '`description` = ?'; $values[] = $description; $types .= 's'; }

if ($newImage && column_exists_local($conn, 'clients', 'image')) { 
    // fetch old image to delete
    $oldRes = $conn->query("SELECT image FROM clients WHERE id = " . intval($id) . " LIMIT 1");
    $oldImg = null;
    if ($oldRes && $oldRes->num_rows) { $oldImg = $oldRes->fetch_assoc()['image']; }
    $sets[] = '`image` = ?'; $values[] = $newImage; $types .= 's';
}

if (column_exists_local($conn, 'clients', 'category')) { $sets[] = '`category` = ?'; $values[] = $category; $types .= 's'; }
if (column_exists_local($conn, 'clients', 'work_category')) { $sets[] = '`work_category` = ?'; $values[] = $work_category; $types .= 's'; }
if (column_exists_local($conn, 'clients', 'new_clients')) { $sets[] = '`new_clients` = ?'; $values[] = $new_clients !== null ? $new_clients : 0; $types .= 'i'; }
if (column_exists_local($conn, 'clients', 'conversation_rate')) { $sets[] = '`conversation_rate` = ?'; $values[] = $conversation_rate !== null ? $conversation_rate : 0.0; $types .= 'd'; }
if (column_exists_local($conn, 'clients', 'seo_rank')) { $sets[] = '`seo_rank` = ?'; $values[] = $seo_rank !== null ? $seo_rank : 0; $types .= 'i'; }

if (count($sets) === 0) { ob_clean(); echo json_encode(['success'=>false,'message'=>'No updatable columns found']); exit; }

$sql = "UPDATE clients SET " . implode(', ', $sets) . " WHERE id = ? LIMIT 1";
$values[] = $id; $types .= 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) { ob_clean(); echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error, 'sql'=>$sql]); exit; }

$bind_names = [];
if ($types !== '') {
    $bind_names[] = & $types;
    for ($i=0;$i<count($values);$i++) { $bind_names[] = & $values[$i]; }
    call_user_func_array(array($stmt,'bind_param'), $bind_names);
}

if ($stmt->execute()) {
    $stmt->close();
    // delete old image file if replaced
    if (isset($oldImg) && $oldImg && $newImage) {
        $path = __DIR__ . '/../images/' . basename($oldImg);
        if (file_exists($path)) @unlink($path);
    }
    ob_clean();
    echo json_encode(['success'=>true,'id'=>$id,'name'=>$name,'description'=>$description,'image'=>$newImage ? ('/images/'.$newImage) : null]);
} else {
    $err = $stmt->error;
    $stmt->close();
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'DB update failed: '.$err]);
}
$conn->close();
exit;
?>
