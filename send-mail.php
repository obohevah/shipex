<?php
/**
 * ==============================================================================
 * Contact & Quote Form Mail Handler
 * s-Heerenberg Logistics B.V. - Tank Storage & Logistics
 * ==============================================================================
 * Dual transactional dispatch:
 * 1. Admin notification to webmail (info@s-heerenberglogistic.nl)
 * 2. Instant confirmation reply to the sender / inquirer
 *
 * Configured with strict RFC & Anti-Spam standards:
 * - Proper envelope Return-Path alignment (SPF/DMARC)
 * - Domain Message-ID header alignment
 * - Full Multipart/alternative MIME (HTML + plain text AltBody)
 * - Clean Display Name & Subject to avoid bulk/spam triggers
 * ==============================================================================
 */

// Load centralized mail configuration
require_once __DIR__ . '/mail-config.php';

// Suppress PHP notices/warnings so output buffer remains 100% clean
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ob_start();

// Helper to log mail events/errors to mail-errors.log for troubleshooting
function writeMailLog(string $label, string $details): void {
    $entry = '[' . date('Y-m-d H:i:s') . "] [$label] $details" . PHP_EOL;
    @file_put_contents(__DIR__ . '/mail-errors.log', $entry, FILE_APPEND);
}

// Check if request is AJAX
$isAjax = (!empty($_POST['doing-via-ajax']) && $_POST['doing-via-ajax'] === 'yes')
    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

function sendResponse(bool $success, string $message, string $htmlAlert, int $code = 200): void {
    ob_end_clean();
    http_response_code($code);

    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'status'  => $success ? 'success' : 'error',
            'message' => $message,
            'html'    => $htmlAlert
        ]);
        exit;
    }

    // Default HTML snippet response for AJAX form handler
    header('Content-Type: text/html; charset=UTF-8');
    echo $htmlAlert;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.', '<div class="alert alert-danger mt-3">Invalid request method.</div>', 405);
}

// Load PHPMailer classes
try {
    require_once __DIR__ . '/mailer/Exception.php';
    require_once __DIR__ . '/mailer/PHPMailer.php';
    require_once __DIR__ . '/mailer/SMTP.php';
} catch (\Throwable $e) {
    writeMailLog('PHPMailer Load Error', $e->getMessage());
    sendResponse(false, 'Mail service unavailable.', '<div class="alert alert-danger mt-3">Mail service is temporarily unavailable. Please email us directly at ' . htmlspecialchars(ADMIN_EMAIL) . '</div>', 500);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sanitize inputs
function sanitizeInput($val): string {
    $val = trim($val ?? '');
    $val = str_replace(["\r", "\n", "%0a", "%0d"], '', $val);
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

$name = sanitizeInput($_POST['name'] ?? '');
if ($name === '') {
    $fName = sanitizeInput($_POST['firstName'] ?? '');
    $lName = sanitizeInput($_POST['lastName'] ?? '');
    $name  = trim($fName . ' ' . $lName);
}

$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = sanitizeInput($_POST['phone'] ?? '');
$subject = sanitizeInput($_POST['subject'] ?? ($_POST['therapy'] ?? ($_POST['service'] ?? 'General Inquiry')));
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];
if ($name === '') {
    $errors[] = 'Please enter your full name.';
}
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}
if ($message === '') {
    $errors[] = 'Please write a message detailing your inquiry.';
}

if (!empty($errors)) {
    $errList = '<ul class="mb-0 ps-3">';
    foreach ($errors as $err) {
        $errList .= '<li>' . $err . '</li>';
    }
    $errList .= '</ul>';
    $errHtml = '<div class="alert alert-danger mt-3" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:14px 18px;border-radius:8px;">' . $errList . '</div>';
    sendResponse(false, implode(' ', $errors), $errHtml, 422);
}

// Clean Display Name (avoiding leading quotes/apostrophes that trigger RFC quoting issues in spam scanners)
$brandDisplayName = "s-Heerenberg Logistics B.V.";

/**
 * Factory function creating a fully RFC-compliant PHPMailer instance
 * with high-reputation deliverability settings.
 */
function getMailerInstance(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = (strtolower(SMTP_SECURE) === 'ssl') ? 'ssl' : 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 20;

    // Strict Deliverability & Header Alignment
    $mail->Sender     = SMTP_USERNAME;             // Envelope Return-Path matches authenticated sender
    $mail->Hostname   = 's-heerenberglogistic.nl'; // Generates RFC Message-ID: <...@s-heerenberglogistic.nl>
    $mail->XMailer    = ' ';                       // Clears fingerprint header

    // SSL compatibility for local XAMPP and cPanel relays
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];

    if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
        $mail->SMTPDebug   = 2;
        $mail->Debugoutput = function ($str) {
            writeMailLog('SMTP Debug', $str);
        };
    }
    return $mail;
}

$adminSent = false;
$userSent  = false;
$errorMsg  = '';

try {
    // ==========================================================
    // 1. Email notification to Admin (Webmail)
    // ==========================================================
    $adminMail = getMailerInstance();
    $adminMail->setFrom(SMTP_USERNAME, $brandDisplayName);
    $adminMail->addAddress(ADMIN_EMAIL);
    $adminMail->addReplyTo($email, $name);
    $adminMail->isHTML(true);
    $adminMail->Subject = "New Website Inquiry from {$name} - {$brandDisplayName}";

    $adminMail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#0b1329;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b1329;padding:35px 15px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 15px 40px rgba(0,0,0,0.35);">
          <tr>
            <td style="background-color:#00112e;padding:28px 32px;border-bottom:3px solid #d4a024;">
              <table width="100%">
                <tr>
                  <td>
                    <h2 style="color:#d4a024;margin:0 0 4px 0;font-size:22px;letter-spacing:0.5px;">' . htmlspecialchars($brandDisplayName) . '</h2>
                    <span style="color:#94a3b8;font-size:13px;text-transform:uppercase;letter-spacing:1px;">New Web Inquiry Received</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h3 style="color:#00112e;margin:0 0 16px 0;font-size:18px;">Contact Request Details</h3>
              <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                  <td width="30%" style="color:#64748b;font-size:13px;font-weight:bold;">Sender Name</td>
                  <td style="color:#00112e;font-size:14px;font-weight:600;">' . htmlspecialchars($name) . '</td>
                </tr>
                <tr style="border-bottom:1px solid #e2e8f0;">
                  <td style="color:#64748b;font-size:13px;font-weight:bold;">Email Address</td>
                  <td style="color:#00112e;font-size:14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#d4a024;text-decoration:none;font-weight:600;">' . htmlspecialchars($email) . '</a></td>
                </tr>
                ' . ($phone !== '' ? '
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                  <td style="color:#64748b;font-size:13px;font-weight:bold;">Phone Number</td>
                  <td style="color:#00112e;font-size:14px;"><a href="tel:' . htmlspecialchars($phone) . '" style="color:#00112e;text-decoration:none;">' . htmlspecialchars($phone) . '</a></td>
                </tr>' : '') . '
                <tr style="border-bottom:1px solid #e2e8f0;">
                  <td style="color:#64748b;font-size:13px;font-weight:bold;">Subject / Service</td>
                  <td style="color:#00112e;font-size:14px;font-weight:600;">' . htmlspecialchars($subject) . '</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="color:#64748b;font-size:13px;font-weight:bold;">Submission Time</td>
                  <td style="color:#64748b;font-size:13px;">' . date('Y-m-d H:i:s T') . '</td>
                </tr>
              </table>

              <div style="background-color:#f8fafc;border-left:4px solid #d4a024;padding:18px 20px;border-radius:0 8px 8px 0;margin-bottom:28px;">
                <p style="margin:0 0 8px 0;color:#00112e;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px;">Message Content:</p>
                <div style="color:#334155;font-size:14px;line-height:1.6;white-space:pre-wrap;">' . nl2br(htmlspecialchars($message)) . '</div>
              </div>

              <div style="text-align:center;">
                <a href="mailto:' . htmlspecialchars($email) . '?subject=' . urlencode('Re: ' . $subject) . '" style="background:#d4a024;color:#00112e;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:bold;font-size:14px;display:inline-block;">Reply Directly via Email</a>
              </div>
            </td>
          </tr>
          <tr>
            <td style="background-color:#f1f5f9;padding:16px 32px;text-align:center;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
              Secure automated notification generated by ' . htmlspecialchars($brandDisplayName) . ' &bull; Amsterdam, Netherlands
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    $adminMail->AltBody = "New Inquiry from {$name}\nEmail: {$email}\nPhone: {$phone}\nSubject: {$subject}\nDate: " . date('Y-m-d H:i:s') . "\n\nMessage:\n{$message}";
    $adminSent = $adminMail->send();
    writeMailLog('Admin Mail Sent', "Dispatched successfully to " . ADMIN_EMAIL);

    // ==========================================================
    // 2. Confirmation Email to Client / Sender
    // ==========================================================
    try {
        $userMail = getMailerInstance();
        $userMail->setFrom(SMTP_USERNAME, $brandDisplayName);
        $userMail->addAddress($email, $name);
        $userMail->addReplyTo(COMPANY_EMAIL, $brandDisplayName);
        $userMail->isHTML(true);
        $userMail->Subject = "Thank you for your message - {$brandDisplayName}";

        $userMail->Body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Thank you for contacting ' . htmlspecialchars($brandDisplayName) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#334155;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:30px 15px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
          <tr>
            <td style="background-color:#00112e;padding:24px 30px;border-bottom:3px solid #d4a024;">
              <h2 style="color:#d4a024;margin:0;font-size:20px;font-weight:bold;">' . htmlspecialchars($brandDisplayName) . '</h2>
              <span style="color:#94a3b8;font-size:12px;letter-spacing:0.5px;">Tank Storage &amp; Port Logistics</span>
            </td>
          </tr>
          <tr>
            <td style="padding:28px 30px;">
              <p style="font-size:15px;color:#00112e;margin-top:0;">Dear ' . htmlspecialchars($name) . ',</p>
              <p style="font-size:14px;line-height:1.6;color:#475569;">
                Thank you for contacting ' . htmlspecialchars($brandDisplayName) . '. We have received your inquiry and our operations and chartering desk will review your requirements and follow up with you shortly.
              </p>
              
              <table width="100%" cellpadding="12" cellspacing="0" style="background-color:#f8fafc;border-left:4px solid #d4a024;margin:20px 0;border-radius:4px;">
                <tr>
                  <td style="font-size:13px;color:#334155;line-height:1.6;">
                    <strong>Subject:</strong> ' . htmlspecialchars($subject) . '<br>
                    ' . ($phone !== '' ? '<strong>Phone:</strong> ' . htmlspecialchars($phone) . '<br>' : '') . '
                    <strong>Your Message:</strong><br>
                    ' . nl2br(htmlspecialchars($message)) . '
                  </td>
                </tr>
              </table>

              <p style="font-size:13px;line-height:1.6;color:#475569;">
                For immediate terminal assistance or urgent vessel scheduling, feel free to reach our operations desk directly at ' . htmlspecialchars(COMPANY_PHONE) . '.
              </p>

              <p style="font-size:14px;color:#00112e;margin-bottom:0;line-height:1.5;">
                Sincerely,<br>
                <strong>' . htmlspecialchars($brandDisplayName) . '</strong><br>
                <span style="font-size:12px;color:#64748b;">Amsterdam, Netherlands</span>
              </p>
            </td>
          </tr>
          <tr>
            <td style="background-color:#f1f5f9;padding:14px 30px;text-align:center;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
              ' . htmlspecialchars(COMPANY_ADDR) . ' &bull; <a href="mailto:' . htmlspecialchars(COMPANY_EMAIL) . '" style="color:#64748b;text-decoration:none;">' . htmlspecialchars(COMPANY_EMAIL) . '</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

        // Plain Text Multipart Fallback (Mirrors HTML naturally)
        $userMail->AltBody = "Dear {$name},\n\n"
            . "Thank you for contacting {$brandDisplayName}.\n\n"
            . "We have received your message regarding: {$subject}\n"
            . "Our operations and chartering team will review your specifications and contact you shortly.\n\n"
            . "Summary of your submission:\n"
            . "Subject: {$subject}\n"
            . ($phone !== '' ? "Phone: {$phone}\n" : "")
            . "Message:\n{$message}\n\n"
            . "For immediate terminal assistance, please call " . COMPANY_PHONE . ".\n\n"
            . "Sincerely,\n"
            . "{$brandDisplayName}\n"
            . COMPANY_ADDR;

        $userSent = $userMail->send();
        writeMailLog('User Mail Sent', "Confirmation delivered to {$email}");
    } catch (\Throwable $userEx) {
        writeMailLog('User Confirmation Exception', $userEx->getMessage());
    }

} catch (\Throwable $e) {
    $errorMsg = $e->getMessage();
    writeMailLog('Mailer Send Exception', $errorMsg);
}

if ($adminSent) {
    $successHtml = '<div class="alert alert-success mt-3" style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:16px 20px;border-radius:8px;font-size:15px;line-height:1.5;">'
        . '<strong><i class="fa fa-check-circle me-1" style="color:#16a34a;"></i> Inquiry Sent Successfully!</strong>'
        . '<div class="mt-1">Thank you, <strong>' . htmlspecialchars($name) . '</strong>. Your message has been received by our operations desk, and a confirmation email has been sent to <strong>' . htmlspecialchars($email) . '</strong>.</div>'
        . '</div>';
    sendResponse(true, "Thank you {$name}! Your inquiry has been sent successfully.", $successHtml);
} else {
    $failHtml = '<div class="alert alert-danger mt-3" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:16px 20px;border-radius:8px;font-size:15px;">'
        . '<strong><i class="fa fa-exclamation-triangle me-1"></i> Message could not be dispatched via mail server.</strong>'
        . '<div class="mt-1">Please reach out to us directly at <a href="mailto:' . htmlspecialchars(ADMIN_EMAIL) . '" style="color:#991b1b;font-weight:bold;text-decoration:underline;">' . htmlspecialchars(ADMIN_EMAIL) . '</a> or call ' . htmlspecialchars(COMPANY_PHONE) . '.</div>'
        . '</div>';
    sendResponse(false, 'Unable to send email: ' . $errorMsg, $failHtml, 500);
}
