<?php
// app/services/mailer.php

require_once BASE_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_password_reset_email(string $to_email, string $to_name, string $reset_url): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);

        $mail->setFrom(
            $_ENV['SMTP_FROM'],
            $_ENV['SMTP_NAME'] ?? 'Cafeteria POS'
        );
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request (Cafeteria POS)';
        $mail->Body    = build_reset_email_html($to_name, $reset_url);
        $mail->AltBody = build_reset_email_plain($to_name, $reset_url);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

function build_reset_email_html(string $name, string $url): string
{
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $safe_url  = htmlspecialchars($url, ENT_QUOTES);
    $year      = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset – Cafeteria POS</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8; padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;">

          <!-- TOP ACCENT BAR -->
          <tr>
            <td style="background-color:#ff6b4a; height:4px; border-radius:4px 4px 0 0; font-size:0; line-height:0;">&nbsp;</td>
          </tr>

          <!-- HEADER -->
          <tr>
            <td style="background-color:#0f4c5c; padding:32px 40px; text-align:center;">
              <p style="margin:0 0 6px; font-size:11px; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.55);">Management System</p>
              <h1 style="margin:0; font-size:26px; font-weight:700; color:#ffffff; letter-spacing:-0.02em;">Cafeteria POS</h1>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background-color:#ffffff; padding:40px 40px 32px;">

              <!-- Icon badge -->
              <div style="text-align:center; margin-bottom:28px;">
                <div style="display:inline-block; background-color:#fff5f3; border:2px solid #ffe0d9; border-radius:50%; width:64px; height:64px; line-height:64px; text-align:center; font-size:28px;">&#128273;</div>
              </div>

              <!-- Greeting -->
              <h2 style="margin:0 0 8px; font-size:22px; font-weight:700; color:#0f2d3a; letter-spacing:-0.01em;">Password Reset Request</h2>
              <p style="margin:0 0 24px; font-size:15px; color:#5a7a8a; line-height:1.6;">
                Hi <strong style="color:#0f2d3a;">{$safe_name}</strong>, we received a request to reset the password for your Cafeteria POS account.
              </p>

              <!-- Divider -->
              <div style="border-top:1px solid #edf2f7; margin-bottom:24px;"></div>

              <!-- Expiry notice -->
              <p style="margin:0 0 8px; font-size:15px; color:#3d5a6a; line-height:1.7;">
                Click the button below to set a new password. For your security, this link will expire in:
              </p>
              <div style="margin:16px 0 28px; text-align:center;">
                <span style="display:inline-block; background-color:#f0f9ff; border:1px solid #bae6fd; border-radius:20px; padding:8px 20px; font-size:13px; font-weight:600; color:#0f4c5c; letter-spacing:0.03em;">
                  &#9200;&nbsp; 1 Hour from now
                </span>
              </div>

              <!-- CTA Button -->
              <div style="text-align:center; margin-bottom:32px;">
                <a href="{$safe_url}"
                   style="display:inline-block; background-color:#ff6b4a; color:#ffffff; text-decoration:none;
                          font-size:15px; font-weight:700; letter-spacing:0.02em;
                          padding:14px 36px; border-radius:10px;">
                  Reset My Password &rarr;
                </a>
              </div>

              <!-- Fallback link box -->
              <div style="background-color:#f8fafc; border:1px solid #e8eef3; border-radius:8px; padding:16px 20px; margin-bottom:28px;">
                <p style="margin:0 0 6px; font-size:12px; font-weight:600; color:#8a9baa; letter-spacing:0.07em; text-transform:uppercase;">Button not working? Copy this link:</p>
                <p style="margin:0; font-size:12px; color:#0f4c5c; word-break:break-all; line-height:1.6;">
                  <a href="{$safe_url}" style="color:#0f4c5c; text-decoration:none;">{$safe_url}</a>
                </p>
              </div>

              <!-- Divider -->
              <div style="border-top:1px solid #edf2f7; margin-bottom:20px;"></div>

              <!-- Safety note -->
              <p style="margin:0; font-size:13px; color:#8a9baa; line-height:1.6;">
                <strong style="color:#5a7a8a;">Didn't request this?</strong>
                You can safely ignore this email — your password will remain unchanged and this link will expire automatically.
              </p>

            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background-color:#f8fafc; border-top:1px solid #edf2f7; padding:24px 40px; border-radius:0 0 12px 12px; text-align:center;">
              <p style="margin:0 0 6px; font-size:13px; font-weight:600; color:#0f4c5c;">Cafeteria POS</p>
              <p style="margin:0; font-size:12px; color:#a0b4be; line-height:1.6;">
                This is an automated message &mdash; please do not reply.<br>
                &copy; {$year} Cafeteria POS Management System. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
HTML;
}

function build_reset_email_plain(string $name, string $url): string
{
    $year = date('Y');

    return implode("\n", [
        "PASSWORD RESET REQUEST - Cafeteria POS",
        str_repeat("-", 40),
        "",
        "Hi {$name},",
        "",
        "We received a request to reset your password.",
        "Use the link below to set a new one (expires in 1 hour):",
        "",
        $url,
        "",
        str_repeat("-", 40),
        "Didn't request this? Ignore this email — your password won't change.",
        "",
        "(c) {$year} Cafeteria POS Management System",
        "This is an automated message. Please do not reply.",
    ]);
}