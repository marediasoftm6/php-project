<?php
// Authentication configuration
// Set to false to store passwords in plain text (not recommended)
define('AUTH_HASH_ENABLED', false);

// Mail configuration (PHPMailer)
// MAIL_DRIVER: 'smtp' to use SMTP, 'mail' to use PHP's mail(), 'display' to skip sending and only display links
if (!defined('MAIL_DRIVER')) define('MAIL_DRIVER', 'smtp');
if (!defined('MAIL_FROM_EMAIL')) define('MAIL_FROM_EMAIL', 'no-reply@localhost');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'Quesiono');
if (!defined('SMTP_HOST')) define('SMTP_HOST', '');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', '');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', '');
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

function send_email($to, $subject, $html)
{
    $onWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    $useSMTP = (MAIL_DRIVER === 'smtp' && SMTP_HOST !== '');
    $useDisplay = (MAIL_DRIVER === 'display') || (MAIL_DRIVER === 'smtp' && SMTP_HOST === '') || (MAIL_DRIVER === 'mail' && $onWindows);

    // Try PHPMailer if available
    $phpmailerAvailable = false;
    $paths = [
        __DIR__ . '/vendor/phpmailer/phpmailer/src',
    ];
    foreach ($paths as $vendorBase) {
        if (is_file($vendorBase . '/PHPMailer.php') && is_file($vendorBase . '/SMTP.php') && is_file($vendorBase . '/Exception.php')) {
            require_once $vendorBase . '/Exception.php';
            require_once $vendorBase . '/PHPMailer.php';
            require_once $vendorBase . '/SMTP.php';
            $phpmailerAvailable = class_exists('\\PHPMailer\\PHPMailer\\PHPMailer');
            if ($phpmailerAvailable) {
                break;
            }
        }
    }

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
