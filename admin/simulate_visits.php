<?php
// simulate_visits.php
// Helper for QA: inserts sample visitors into `visitors` so you can verify dashboard counts.

require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');
$now = new DateTime();

// ensure visitors table exists (same schema as track_visit.php)
$create = "CREATE TABLE IF NOT EXISTS `visitors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `visitor_key` VARCHAR(128) DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT,
    `path` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
@$conn->query($create);

$now = new DateTime();
$thisWeekStart = (clone $now)->modify('monday this week')->setTime(0,0,0);
$lastWeekStart = (clone $thisWeekStart)->modify('-7 days');

$inserted = [];

// prepare insert
$stmt = $conn->prepare('INSERT INTO visitors (visitor_key, ip, user_agent, path, created_at) VALUES (?, ?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$conn->error]); exit;
}

// create 5 unique visitors in this week
for ($i=0;$i<5;$i++) {
    $vk = bin2hex(random_bytes(8));
    $ip = '127.0.0.'.($i+1);
    $ua = 'SimAgent/1.0';
    $path = '/test-sim';
    $created = (clone $thisWeekStart)->modify('+'.($i).' days')->format('Y-m-d H:i:s');
    $stmt->bind_param('sssss', $vk, $ip, $ua, $path, $created);
    $stmt->execute();
    $inserted[] = ['visitor_key'=>$vk,'created_at'=>$created];
}

// create 3 unique visitors last week
for ($i=0;$i<3;$i++) {
    $vk = bin2hex(random_bytes(8));
    $ip = '127.0.1.'.($i+1);
    $ua = 'SimAgent/1.0';
    $path = '/old-sim';
    $created = (clone $lastWeekStart)->modify('+'.($i).' days')->format('Y-m-d H:i:s');
    $stmt->bind_param('sssss', $vk, $ip, $ua, $path, $created);
    $stmt->execute();
    $inserted[] = ['visitor_key'=>$vk,'created_at'=>$created];
}

$stmt->close();

// return current counts
$out = [];
$res = $conn->query("SELECT COUNT(DISTINCT visitor_key) AS total FROM visitors");
if ($res) { $r=$res->fetch_assoc(); $out['total_users']=intval($r['total']); $res->free(); }
$res = $conn->query("SELECT COUNT(DISTINCT visitor_key) AS weekly FROM visitors WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
if ($res) { $r=$res->fetch_assoc(); $out['new_users_week']=intval($r['weekly']); $res->free(); }

echo json_encode(['success'=>true,'inserted'=>$inserted,'counts'=>$out], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
exit;
