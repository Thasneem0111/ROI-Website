<?php
// get_services.php - Return JSON list of services for admin/public

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

include 'db_connect.php';

$out = [];
$sql = "SELECT * FROM services ORDER BY id DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $img = null;
        if (!empty($row['image'])) {
            $img = '/images/' . ltrim($row['image'], '/');
        }
        $name = null;
        if (isset($row['name'])) $name = $row['name'];
        elseif (isset($row['title'])) $name = $row['title'];

        $createdAt = null;
        if (isset($row['createdAt'])) $createdAt = $row['createdAt'];
        elseif (isset($row['created_at'])) $createdAt = $row['created_at'];

        $out[] = [
            'id' => isset($row['id']) ? (int)$row['id'] : null,
            'name' => $name,
            'description' => isset($row['description']) ? $row['description'] : null,
            'image' => $img,
            'createdAt' => $createdAt
        ];
    }
    if (method_exists($res, 'free')) $res->free();
}

$conn->close();
ob_clean();
echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
?>
