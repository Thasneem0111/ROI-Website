<?php
// Simple redirect so visiting /admin/ opens the extensionless dashboard route.
// This is a safe, low-risk fallback while we ensure Apache honors .htaccess.
// It preserves any query string when present.
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: dashboard' . $qs, true, 302);
exit;
?>
