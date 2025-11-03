<?php
// Minimal autoloader that tries to load PHPMailer if you've placed its src/ under admin/vendor/phpmailer/src
// Usage: download PHPMailer src files into admin/vendor/phpmailer/src and this autoload will include them.

$base = __DIR__;
// The autoloader should look for admin/vendor/phpmailer/src relative to the project root.
// vendor/autoload.php is at PROJECT_ROOT/vendor/autoload.php so go up one dir then into admin/vendor/phpmailer/src
$srcDir = $base . '/../admin/vendor/phpmailer/src';
$files = [
    $srcDir . '/PHPMailer.php',
    $srcDir . '/SMTP.php',
    $srcDir . '/Exception.php'
];
$found = false;
foreach ($files as $f) {
    if (file_exists($f)) {
        require_once $f;
        $found = true;
    }
}
if (!$found) {
    // no-op; PHPMailer not present. This file is intentionally silent so code can fallback to mail().
}
return;
