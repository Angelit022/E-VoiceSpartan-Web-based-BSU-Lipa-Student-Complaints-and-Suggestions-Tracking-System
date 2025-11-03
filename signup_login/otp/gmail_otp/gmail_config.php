<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../phpmailer/vendor/autoload.php';

function sendMailOTP($toEmail, $otp_code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '23-36439@g.batstate-u.edu.ph';   
        $mail->Password   = 'heksaeymhadbrwyu';               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;     
        $mail->Port       = 465;                          
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('23-36439@g.batstate-u.edu.ph', 'EVoiceSpartan');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your EVoiceSpartan OTP Code';
        $mail->Body = "
            <p>Hello!</p>
            <p>Your One-Time Password (OTP) is: <b style='font-size:18px;'>$otp_code</b></p>
            <p>This code is valid for 5 minutes. Please do not share it with anyone.</p>
            <br>
            <p>– EVoiceSpartan System</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Gmail OTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
