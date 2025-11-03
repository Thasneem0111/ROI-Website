<?php
// Handles updating an existing industry
include 'db_connect.php';

function redirect_with_status($url, $status, $msg = ''){
    // Normalize URL to remove .php extension from path portion so redirects are extensionless
    $parts = explode('?', $url, 2);
    $path = preg_replace('/\.php$/i', '', $parts[0]);
    $extraQuery = isset($parts[1]) ? $parts[1] : '';
    $params = ['status'=>$status];
    if($msg) $params['msg'] = urlencode($msg);
    $query = http_build_query($params);
    if ($extraQuery !== '') {
        $final = $path . '?' . $extraQuery . '&' . $query;
    } else {
        $final = $path . '?' . $query;
    }
    header('Location: ' . $final);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: update_item'); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (!$id || $name === '') {
    redirect_with_status('update_item?id=' . $id, 'error', 'Invalid input');
}

// Image replacement handling
$allowed = ['jpg','jpeg','png','webp','avif','gif'];
$target_dir = __DIR__ . '/../images/';
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

$newImage = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $origName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        redirect_with_status('update_item?id=' . $id, 'error', 'Invalid image type');
    }
    $newImage = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $target_file = $target_dir . $newImage;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        redirect_with_status('update_item?id=' . $id, 'error', 'Error uploading image');
    }
}

// Build update query
if ($newImage) {
    $stmt = $conn->prepare("UPDATE industry SET `name` = ?, `description` = ?, `image` = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param('sssi', $name, $description, $newImage, $id);
} else {
    $stmt = $conn->prepare("UPDATE industry SET `name` = ?, `description` = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param('ssi', $name, $description, $id);
}

if (!$stmt) {
    redirect_with_status('update_item?id=' . $id, 'error', 'DB prepare failed: ' . $conn->error);
}

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    redirect_with_status('update_item?id=' . $id, 'success', 'Industry updated successfully');
} else {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    redirect_with_status('update_item?id=' . $id, 'error', 'DB update failed: ' . $err);
}

?>
