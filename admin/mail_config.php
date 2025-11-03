<?php
// admin/mail_config.php
// Copy this file to set SMTP credentials for PHPMailer and reliable sending.
// Leave values empty to keep using PHP mail() fallback.

return [
    // SMTP host (e.g. smtp.gmail.com)
    // Use the domain SMTP host shown in cPanel (Mail Client settings)
    'host' => 'roi.com.qa',
    // SMTP username
    // Use the exact mailbox username from cPanel (full email address)
    'username' => 'royalorbitinnovations@roi.com.qa',
    // SMTP password
    'password' => 'royalorbit@0109',
    // SMTP port (e.g. 587 or 465)
    'port' => 465,
    // encryption: '', 'tls', or 'ssl'
    'secure' => 'ssl',
    // default From email and name to use when sending replies
    'from_email' => 'royalorbitinnovations@roi.com.qa',
    'from_name' => 'ROI Admin',
    // Optional: allow self-signed certs (dev only)
    'smtp_options' => [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ],
];
