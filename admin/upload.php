<?php
// upload.php - handles adding a new industry
include 'db_connect.php';

function redirect_with_status($url, $status, $msg = ''){
    $params = ['status'=>$status];
    if($msg) $params['msg'] = urlencode($msg);
    header('Location: ' . $url . '?' . http_build_query($params));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($name === '') {
        redirect_with_status('add_item.php', 'error', 'Name is required');
    }

    // Handle file upload
    $allowed = ['jpg','jpeg','png','webp','avif','gif'];
    $target_dir = __DIR__ . '/../images/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        // No image provided
        $imageFilename = null;
    } else {
        $origName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            redirect_with_status('add_item.php', 'error', 'Invalid image type');
        }
        // Create a unique filename
        $imageFilename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
        $target_file = $target_dir . $imageFilename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            redirect_with_status('add_item.php', 'error', 'Error uploading image');
        }
    }

    // Insert into `industry` table (assumes columns: id, name, description, image, createdAt)
    $stmt = $conn->prepare("INSERT INTO industry (`name`, `description`, `image`, `createdAt`) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        redirect_with_status('add_item.php', 'error', 'DB prepare failed: ' . $conn->error);
    }
    $imgParam = $imageFilename ?: null;
    $stmt->bind_param('sss', $name, $description, $imgParam);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        redirect_with_status('add_item.php', 'success', 'Industry added successfully');
    } else {
        $err = $stmt->error;
        $stmt->close();
        $conn->close();
        redirect_with_status('add_item.php', 'error', 'DB insert failed: ' . $err);
    }
}
// Not a POST request
header('Location: add_item.php');
exit;
?>
