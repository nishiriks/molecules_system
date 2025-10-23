<?php
require_once __DIR__ . '/../../../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USERNAME;
            $this->mail->Password = SMTP_PASSWORD;
            $this->mail->SMTPSecure = SMTP_ENCRYPTION;
            $this->mail->Port = SMTP_PORT;

            // Recipients
            $this->mail->setFrom(FROM_EMAIL, FROM_NAME);
            $this->mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }

    public function sendVerificationEmail($toEmail, $toName, $verificationCode) {
        try {
            $this->mail->addAddress($toEmail, $toName);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Email - Molecules CEU';
            
            $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/molecules_system/verify-email.php?code=" . $verificationCode;
            
            $this->mail->Body = $this->getVerificationEmailTemplate($toName, $verificationLink, $verificationCode);
            $this->mail->AltBody = "Hello $toName,\n\nPlease verify your email by clicking this link: $verificationLink\n\nOr use this verification code: $verificationCode\n\nThank you!";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    private function getVerificationEmailTemplate($name, $link, $code) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .code { font-size: 24px; font-weight: bold; text-align: center; padding: 10px; background: #eee; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Molecules CEU</h1>
                    <h2>Email Verification</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>Thank you for registering with Molecules CEU. Please verify your email address to activate your account.</p>
                    
                    <p><strong>Verification Code:</strong></p>
                    <div class='code'>$code</div>
                    
                    <p>Or click the button below to verify your email:</p>
                    <a href='$link' class='button'>Verify Email Address</a>
                    
                    <p>If the button doesn't work, copy and paste this link in your browser:</p>
                    <p><a href='$link'>$link</a></p>
                    
                    <p>This verification code will expire in 24 hours.</p>
                    <p>If you didn't create an account, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>