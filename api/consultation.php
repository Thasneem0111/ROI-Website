<?php
// consultation.php
header('Content-Type: application/json; charset=utf-8');

// Dynamic CORS: allow same-site (any host serving this file) and selected origins (non-prod dev)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? trim($_SERVER['HTTP_ORIGIN']) : '';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/^https?:\/\//i','', $_SERVER['HTTP_HOST']) : '';
$allowedOrigins = [
    'https://roi.com.qa',
    'https://www.roi.com.qa',
    'http://localhost',
    'http://localhost:3000',
    'http://127.0.0.1',
    'http://127.0.0.1:5500'
];

// If the request comes from the same host (same-origin), echo it back; else allow if whitelisted
if ($origin) {
    $sameSiteOrigin = '';
    // Build canonical same-site origin based on request scheme + host
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $sameSiteOrigin = $scheme . '://' . $host;
    if ($origin === $sameSiteOrigin || in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
}
// Methods / headers for preflight and actual requests
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content for preflight
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get and validate JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$name  = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$businessName = trim($data['businessName'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($phone)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Name, email, and phone are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid email format']);
    exit();
}

// Sanitize
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$businessName = htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8');

// Email configuration
$to = 'royalorbitinnovations@gmail.com';
$subject = "New Consultation Request: $name";

$message = "
NEW CONSULTATION REQUEST FROM ROI WEBSITE

CONTACT INFORMATION:
Name: $name
Email: $email
Phone: $phone
Business: " . ($businessName ?: 'Not provided') . "

TECHNICAL DETAILS:
Submitted: " . date('Y-m-d H:i:s') . "
IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "

Please respond within 24 hours.
";

// Email headers
$headers = [
    'From: noreply@roi.com.qa',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0'
];

// Send email: try PHPMailer (SMTP) if configured, else fallback to mail()
$sent = false;
$mailError = '';
// load mail config if present (admin/mail_config.php or api/mail_config.php)
$mailConfig = [];
if (file_exists(__DIR__ . '/../admin/mail_config.php')) {
    try { $mailConfig = include __DIR__ . '/../admin/mail_config.php'; } catch(Throwable $e) { $mailConfig = []; }
} elseif (file_exists(__DIR__ . '/mail_config.php')) {
    try { $mailConfig = include __DIR__ . '/mail_config.php'; } catch(Throwable $e) { $mailConfig = []; }
}

// Try PHPMailer if autoload and SMTP host provided
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
        if (!empty($mailConfig['smtp_options']) && is_array($mailConfig['smtp_options'])) {
            $mailer->SMTPOptions = $mailConfig['smtp_options'];
        }
        $fromAddr = !empty($mailConfig['from_email']) ? $mailConfig['from_email'] : ($mailConfig['from_email'] ?? 'noreply@roi.com.qa');
        $fromNameCfg = !empty($mailConfig['from_name']) ? $mailConfig['from_name'] : 'ROI Website';
        $mailer->setFrom($fromAddr, $fromNameCfg);
        $mailer->addReplyTo($email, $name);
        $mailer->addAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        $mailer->AltBody = strip_tags($message);
        $mailer->send();
        $sent = true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        $sent = false;
        $mailError = $e->getMessage();
        error_log('PHPMailer error in consultation API: ' . $mailError);
    } catch (Throwable $e) {
        $sent = false;
        $mailError = $e->getMessage();
        error_log('PHPMailer throwable in consultation API: ' . $mailError);
    }
}

// Fallback to mail()
if (!$sent) {
    try {
        // suppress PHP warning output from mail() to avoid breaking JSON responses when SMTP is not available
        $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
        if (!$sent) {
            $mailError = 'mail() returned false';
            error_log("FAILED: Could not send consultation email for: $email");
        }
    } catch (Throwable $e) {
        $sent = false;
        $mailError = $e->getMessage();
        error_log('Mail exception in consultation API: ' . $mailError);
    }
}

// Persist a copy to messages.json so the admin panel can show it (always persist even if mail failed)
try {
    $storePath = __DIR__ . DIRECTORY_SEPARATOR . 'messages.json';
    $current = [];
    if (file_exists($storePath)) {
        $rawJson = file_get_contents($storePath);
        $decoded = json_decode($rawJson, true);
        if (is_array($decoded)) { $current = $decoded; }
    }
    $entry = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'businessName' => $businessName,
        'source' => 'php',
        'createdAt' => round(microtime(true) * 1000),
        'email_sent' => $sent ? true : false,
        'email_error' => $mailError
    ];
    $current[] = $entry;
    // Write atomically
    $tmpPath = $storePath . '.tmp';
    file_put_contents($tmpPath, json_encode($current, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    rename($tmpPath, $storePath);
} catch (Exception $e) {
    error_log('WARN: Failed to persist messages.json: ' . $e->getMessage());
}

// If mail failed, also log into a dedicated log for easier debugging
if (!$sent) {
    try {
        $logPath = __DIR__ . DIRECTORY_SEPARATOR . 'consultation_errors.log';
        $logLine = date('c') . " | Email send failed for: $email | error: " . ($mailError ?: 'unknown') . "\n";
        file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
    } catch (Throwable $__) { /* ignore logging errors */ }
}

// Respond to client: always return ok:true so UI shows success; include email_sent flag
echo json_encode([
    'ok' => true,
    'email_sent' => $sent ? true : false,
    'message' => $sent ? 'Thank you! Your consultation request has been sent successfully.' : 'Your request was received and saved; email delivery failed on server.'
]);
?>