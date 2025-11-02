<?php
// get_blogs.php
// Return JSON list of blogs for admin

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
$sql = "SELECT * FROM blog ORDER BY id DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $img = null;
        if (!empty($row['image'])) {
            $img = '/images/' . ltrim($row['image'], '/');
        }
        // choose topic column
        $topic = null;
        if (isset($row['blog_topic'])) $topic = $row['blog_topic'];
        elseif (isset($row['title'])) $topic = $row['title'];

        $createdAt = null;
        if (isset($row['createdAt'])) $createdAt = $row['createdAt'];
        elseif (isset($row['created_at'])) $createdAt = $row['created_at'];
        elseif (isset($row['created'])) $createdAt = $row['created'];

        $out[] = [
            'id' => isset($row['id']) ? (int)$row['id'] : null,
            'topic' => $topic,
            'description' => isset($row['description']) ? $row['description'] : null,
            'image' => $img,
            'main_heading' => isset($row['main_heading']) ? $row['main_heading'] : null,
            'sub_heading' => isset($row['sub_heading']) ? $row['sub_heading'] : null,
            'normal_heading' => isset($row['normal_heading']) ? $row['normal_heading'] : null,
            'paragraph' => isset($row['paragraph']) ? $row['paragraph'] : null,
            'list' => isset($row['list']) ? $row['list'] : null,
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
