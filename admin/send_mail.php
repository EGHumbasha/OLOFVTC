<?php
/**
 * admin/send_mail.php — Robust email sender with 3-tier fallback
 * Tier 1: Composer autoload (multiple search paths)
 * Tier 2: Direct PHPMailer file requires (5 known cPanel paths)
 * Tier 3: PHP native mail() — never causes a 500 error
 */
if (!defined('DB_HOST')) require_once __DIR__ . '/config.php';

$phpmailerLoaded = false;

$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];
foreach ($autoloadPaths as $al) {
    if (file_exists($al)) {
        require_once $al;
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) { $phpmailerLoaded = true; break; }
    }
}

if (!$phpmailerLoaded) {
    $directPaths = [
        [__DIR__.'/../phpmailer/src/Exception.php', __DIR__.'/../phpmailer/src/PHPMailer.php', __DIR__.'/../phpmailer/src/SMTP.php'],
        [__DIR__.'/../PHPMailer/src/Exception.php', __DIR__.'/../PHPMailer/src/PHPMailer.php', __DIR__.'/../PHPMailer/src/SMTP.php'],
        [__DIR__.'/phpmailer/src/Exception.php',    __DIR__.'/phpmailer/src/PHPMailer.php',    __DIR__.'/phpmailer/src/SMTP.php'],
    ];
    foreach ($directPaths as $group) {
        $allExist = true;
        foreach ($group as $f) { if (!file_exists($f)) { $allExist = false; break; } }
        if ($allExist) {
            foreach ($group as $f) require_once $f;
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) { $phpmailerLoaded = true; break; }
        }
    }
}

function sendEmail(string $to, string $subject, string $body, ?string $replyTo = null): bool {
    global $phpmailerLoaded;
    if ($phpmailerLoaded && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(SMTP_FROM, SMTP_NAME);
            $mail->addAddress($to);
            if ($replyTo) $mail->addReplyTo($replyTo);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<br />','</p>','</div>'], ["\n","\n","\n","\n\n","\n"], $body));
            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer error to '.$to.': '.$mail->ErrorInfo);
        }
    }
    // Native mail() fallback
    $boundary = '----=_Part_'.md5(uniqid('',true));
    $plain    = strip_tags(str_replace(['<br>','<br/>','<br />','</p>','</div>'], ["\n","\n","\n","\n\n","\n"], $body));
    $headers  = "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $headers .= "From: ".SMTP_NAME." <".SMTP_FROM.">\r\nReply-To: ".($replyTo??SMTP_FROM)."\r\n";
    $message  = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n{$plain}\r\n\r\n";
    $message .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n{$body}\r\n\r\n--{$boundary}--";
    $result = @mail($to, $subject, $message, $headers);
    if (!$result) error_log('native mail() failed to '.$to);
    return (bool)$result;
}
