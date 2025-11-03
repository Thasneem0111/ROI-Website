<?php
// admin/api_send_reply.php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'message'=>'Unauthorized']);
    exit;
}
require_once 'db_connect.php';
// load optional mail config (returning array) if present
$mailConfig = [];
if (file_exists(__DIR__ . '/mail_config.php')) {
    try { $mailConfig = include __DIR__ . '/mail_config.php'; } catch(Throwable $e) { $mailConfig = []; }
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid request']); exit; }
$to = trim($data['to'] ?? '');
$subject = trim($data['subject'] ?? '');
$body = trim($data['body'] ?? '');
if (!filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Missing or invalid fields']);
    exit;
}
// fetch admin from DB to use as From
$fromEmail = 'noreply@roi.com.qa';
$fromName = 'ROI Admin';
try {
    $r = $conn->query("SELECT firstName, lastName, email FROM `admin` LIMIT 1");
    if ($r) {
        $row = $r->fetch_assoc();
        if (!empty($row['email'])) $fromEmail = $row['email'];
        $fn = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));
        if ($fn !== '') $fromName = $fn;
        if (method_exists($r,'free')) $r->free();
    }
} catch (Throwable $e) { }
// Try to use PHPMailer if Composer autoload is present and mail_config has host set
$sent = false;
$errorMsg = '';
$usePHPMailer = false;
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php'
];
$autoload = null;
foreach ($autoloadPaths as $p) { if (file_exists($p)) { $autoload = $p; break; } }
if ($autoload && !empty($mailConfig['host'])) {
    try {
        require_once $autoload;
        // Use PHPMailer
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $mailConfig['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $mailConfig['username'] ?? '';
        $mailer->Password = $mailConfig['password'] ?? '';
        $mailer->Port = $mailConfig['port'] ?? 587;
        $secure = strtolower(trim($mailConfig['secure'] ?? ''));
        if ($secure === 'ssl') $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        elseif ($secure === 'tls') $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        // optional SMTP options
        if (!empty($mailConfig['smtp_options']) && is_array($mailConfig['smtp_options'])) {
            $mailer->SMTPOptions = $mailConfig['smtp_options'];
        }
        $fromAddr = !empty($mailConfig['from_email']) ? $mailConfig['from_email'] : $fromEmail;
        $fromNameCfg = !empty($mailConfig['from_name']) ? $mailConfig['from_name'] : $fromName;
        $mailer->setFrom($fromAddr, $fromNameCfg);
        $mailer->addReplyTo($fromAddr, $fromNameCfg);
        $mailer->addAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = strip_tags($body);
        $mailer->send();
        $sent = true;
        $usePHPMailer = true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        $sent = false; $errorMsg = $e->getMessage();
        error_log('PHPMailer error: ' . $errorMsg);
    } catch (Throwable $e) {
        $sent = false; $errorMsg = $e->getMessage();
        error_log('PHPMailer throwable: ' . $errorMsg);
    }
}

// Fallback to mail()
if (!$sent) {
    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    try {
        $sent = mail($to, $subject, $body, implode("\r\n", $headers));
    } catch (Throwable $e) {
        $sent = false; $errorMsg = $e->getMessage();
        error_log('mail() send error: ' . $errorMsg);
    }
}

// Log the attempt to admin/sent_replies.json
$log = [
    'to' => $to,
    'subject' => $subject,
    'body' => $body,
    'from' => ($mailConfig['from_email'] ?? $fromEmail),
    'method' => $usePHPMailer ? 'phpmailer' : 'mail',
    'ok' => $sent ? true : false,
    'error' => $sent ? '' : $errorMsg,
    'ts' => round(microtime(true) * 1000)
];
try {
    $logPath = __DIR__ . DIRECTORY_SEPARATOR . 'sent_replies.json';
    $arr = [];
    if (file_exists($logPath)) {
        $raw = file_get_contents($logPath);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $arr = $decoded;
    }
    $arr[] = $log;
    file_put_contents($logPath . '.tmp', json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    rename($logPath . '.tmp', $logPath);
} catch (Throwable $e) { error_log('Failed to write sent_replies.json: ' . $e->getMessage()); }

if ($sent) {
    echo json_encode(['ok'=>true,'message'=>'Sent']);
} else {
    http_response_code(500);
    echo json_encode(['ok'=>false,'message'=>'Failed to send email (server).','error'=>$errorMsg]);
}

?>