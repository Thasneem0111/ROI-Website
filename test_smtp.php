<?php
require __DIR__ . '/vendor/autoload.php';
$mailConfig = [];
if (file_exists(__DIR__ . '/admin/mail_config.php')) {
    $mailConfig = include __DIR__ . '/admin/mail_config.php';
}

echo "Using mail config: " . json_encode($mailConfig) . "\n";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION; // show connection/debug
    $mail->Debugoutput = function($str, $level) { echo "DEBUG: $str\n"; };
    $mail->Host = $mailConfig['host'] ?? '';
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'] ?? '';
    $mail->Password = $mailConfig['password'] ?? '';
    $mail->Port = $mailConfig['port'] ?? 587;
    $secure = strtolower(trim($mailConfig['secure'] ?? ''));
    if ($secure === 'ssl') $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    elseif ($secure === 'tls') $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    if (!empty($mailConfig['smtp_options']) && is_array($mailConfig['smtp_options'])) {
        $mail->SMTPOptions = $mailConfig['smtp_options'];
    }
    $fromAddr = $mailConfig['from_email'] ?? 'noreply@roi.com.qa';
    $fromName = $mailConfig['from_name'] ?? 'ROI Test';
    $mail->setFrom($fromAddr, $fromName);
    $mail->addAddress('test@example.com');
    $mail->Subject = 'SMTP Test';
    $mail->Body = 'This is a test from PHPMailer';
    $mail->send();
    echo "Mail sent OK\n";
} catch (Exception $e) {
    echo "PHPMailer Exception: " . $e->getMessage() . "\n";
} catch (Throwable $t) {
    echo "Throwable: " . $t->getMessage() . "\n";
}
