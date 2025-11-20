<?php
/**
 * smtp_test.php
 * Test SMTP sending using PHPMailer (if available) or show helpful diagnostics.
 * Usage: http://localhost/xanhorganic/smtp_test.php?to=you@example.com
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "SMTP/PHPMailer support removed\n";
echo "Composer/vendor autoload and PHPMailer were removed per request.\n";
echo "This script will only attempt PHP's mail() as a local fallback.\n\n";

$to = isset($_GET['to']) && filter_var($_GET['to'], FILTER_VALIDATE_EMAIL) ? $_GET['to'] : (defined('SMTP_USER') && SMTP_USER ? SMTP_USER : null);
echo "Site URL: " . SITE_URL . "\n";
echo "Using MAIL_FROM: " . MAIL_FROM . "\n";
if (!$to) {
    echo "No recipient specified. Provide ?to=you@example.com\n";
    exit(1);
}

// Try PHP mail() only
echo "Attempting PHP mail() to: $to\n";
$subject = 'SMTP test from Xanh Organic (mail fallback)';
$message = "This is a test message from smtp_test.php on " . SITE_URL;
$headers = 'From: ' . MAIL_FROM . "\r\n" . 'Reply-To: ' . MAIL_FROM . "\r\n" . 'X-Mailer: PHP/' . phpversion();

$ok = @mail($to, $subject, $message, $headers);
if ($ok) {
    echo "PHP mail() returned TRUE. Mail may have been accepted by local MTA. Check recipient inbox.\n";
} else {
    echo "PHP mail() returned FALSE. Local mail sending failed. Check PHP mail configuration (sendmail_path) or use SMTP.\n";
}

echo "Done.\n";
