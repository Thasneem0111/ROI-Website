<?php
// admin/track_visit.php
// Simple visitor tracker: sets a visitor cookie and logs visits to `visitors` table.

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
ob_start();

include 'db_connect.php';

// Create visitors table if missing
$create = "CREATE TABLE IF NOT EXISTS `visitors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `visitor_key` VARCHAR(128) DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `path` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($create);

// get or set visitor key cookie
$cookieName = 'roi_visitor';
$visitorKey = null;
if (!empty($_COOKIE[$cookieName])) {
    $visitorKey = $_COOKIE[$cookieName];
} else {
    try { $visitorKey = bin2hex(random_bytes(16)); } catch (Exception $e) { $visitorKey = uniqid('v', true); }
    // set cookie for 1 year
    setcookie($cookieName, $visitorKey, time() + 31536000, '/');
}

// gather data
$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : ($_SERVER['REMOTE_ADDR'] ?? '');
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$path = '';
if (isset($_POST['path'])) $path = substr($_POST['path'], 0, 255);
elseif (isset($_SERVER['HTTP_REFERER'])) $path = substr(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) ?? '', 0, 255);
else $path = '/';

// insert row
$stmt = $conn->prepare('INSERT INTO `visitors` (`visitor_key`,`ip`,`user_agent`,`path`) VALUES (?,?,?,?)');
if ($stmt) {
    $stmt->bind_param('ssss', $visitorKey, $ip, $ua, $path);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        echo json_encode(['success'=>true,'visitor'=>$visitorKey]);
        exit;
    }
}

// fallback
echo json_encode(['success'=>false,'message'=>'Unable to record visit']);
exit;
