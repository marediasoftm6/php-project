<?php
// Include composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Authentication configuration
// Set to false to store passwords in plain text (not recommended)
define('AUTH_HASH_ENABLED', false);

// Mail configuration (PHPMailer)
// MAIL_DRIVER: 'smtp' to use SMTP, 'mail' to use PHP's mail(), 'display' to skip sending and only display links
if (!defined('MAIL_DRIVER')) define('MAIL_DRIVER', 'smtp');
if (!defined('MAIL_FROM_EMAIL')) define('MAIL_FROM_EMAIL', 'muhammadshahzaibkhan2k20@gmail.com');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'Quesiono');
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'muhammadshahzaibkhan2k20@gmail.com');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'hhnpbjoihuqqdlhq');
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

/**
 * Sanitizes HTML content using HTMLPurifier.
 * This is used for rich text content from Quill editor.
 */
function sanitize_html($html) {
    if (!class_exists('HTMLPurifier_Config')) {
        // Fallback if HTMLPurifier is not available
        return strip_tags($html, '<p><br><strong><em><u><s><h1><h2><h3><h4><h5><h6><blockquote><pre><ol><ul><li><sub><sup><span><a><img><video><div><table><thead><tbody><tr><th><td>');
    }
    
    $config = HTMLPurifier_Config::createDefault();
    // Allow Quill specific attributes and tags
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
    $config->set('Attr.AllowedFrameTargets', array('_blank'));
    
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}

function send_email($to, $subject, $html)
{
    $onWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    $useSMTP = (MAIL_DRIVER === 'smtp' && SMTP_HOST !== '');
    $useDisplay = (MAIL_DRIVER === 'display') || (MAIL_DRIVER === 'smtp' && SMTP_HOST === '') || (MAIL_DRIVER === 'mail' && $onWindows);

    // PHPMailer should be available via autoloader
    $phpmailerAvailable = class_exists('\\PHPMailer\\PHPMailer\\PHPMailer');

    if ($useDisplay) {
        $_SESSION['last_email_preview'] = ['to' => $to, 'subject' => $subject, 'html' => $html];
        return true;
    }

    if ($phpmailerAvailable) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            if ($useSMTP) {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->Port = SMTP_PORT;
                $mail->SMTPAuth = (SMTP_USERNAME !== '' || SMTP_PASSWORD !== '');
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = (SMTP_SECURE === 'ssl' ? 'ssl' : (SMTP_SECURE === 'tls' ? 'tls' : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS));
            } else {
                $mail->isMail();
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return false;
        }
    }

    // Fallback: PHP's mail()
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">";
    $headersStr = implode("\r\n", $headers);
    return mail($to, $subject, $html, $headersStr);
}

function generate_token($length = 64)
{
    $bytes = random_bytes((int)($length / 2));
    return bin2hex($bytes);
}

function base_url()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $root = '/Quesiono';
    return $scheme . '://' . $host . $root;
}
