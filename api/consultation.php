<?php
// consultation.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://www.roi.com.qa');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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

// Send email
$sent = mail($to, $subject, $message, implode("\r\n", $headers));

if ($sent) {
    error_log("SUCCESS: Consultation form submitted - Name: $name, Email: $email");
    echo json_encode([
        'ok' => true, 
        'message' => 'Thank you! Your consultation request has been sent successfully.'
    ]);
} else {
    error_log("FAILED: Could not send consultation email for: $email");
    http_response_code(500);
    echo json_encode([
        'ok' => false, 
        'message' => 'Failed to send email. Please try again later.'
    ]);
}
?>