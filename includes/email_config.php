<?php
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . '/../vendor/autoload.php';

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'si.mala.laundry@gmail.com');
define('SMTP_PASSWORD', 'qqre rezr npnr pddt');
define('EMAIL_PENGIRIM', 'si.mala.laundry@gmail.com');
define('NAMA_PENGIRIM', 'SI-MALA Klin-Klin Laundry');

function buatMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 30;
    $mail->setFrom(EMAIL_PENGIRIM, NAMA_PENGIRIM);
    return $mail;
}