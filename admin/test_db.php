<?php
// Simple DB connection tester. It reads admin/db_config.php and attempts connects to localhost and 127.0.0.1
@ini_set('display_errors', 1);
@error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Load config
if (!is_file(__DIR__ . '/db_config.php')) {
    echo json_encode(['ok'=>false,'message'=>'db_config.php not found']);
    exit;
}
include __DIR__ . '/db_config.php';

$host = $DB['host'] ?? ($DB_HOST ?? 'localhost');
$user = $DB['user'] ?? ($DB_USER ?? '');
$pass = $DB['pass'] ?? ($DB_PASS ?? '');
$name = $DB['name'] ?? ($DB_NAME ?? '');

function tryConn($h,$u,$p,$n){
    mysqli_report(MYSQLI_REPORT_OFF);
    $res = ['host'=>$h,'user'=>$u,'db'=>$n];
    $conn = @new mysqli($h,$u,$p,$n);
    if ($conn && !$conn->connect_error) {
        $res['ok'] = true;
        $conn->close();
    } else {
        $res['ok'] = false;
        $res['error'] = $conn?->connect_error ?: mysqli_connect_error();
    }
    return $res;
}

$out = [];
$out[] = tryConn($host,$user,$pass,$name);
if ($host !== '127.0.0.1') { $out[] = tryConn('127.0.0.1',$user,$pass,$name); }

echo json_encode($out, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
