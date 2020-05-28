<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
require_once('/Applications/MAMP/htdocs/Project1/PHPMailer/PHPMailerAutoload.php');
class Mail
{
    public static function sendMail($subject, $body, $address)
    {
        $mail= new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth=true;
        $mail->SMTPSecure='ssl';
        $mail->Host='smtp.gmail.com';
        $mail->Port='465';
        $mail->isHTML();
        $mail->Username='someone@gmail.com';
        $mail->Password='xxxxxxxx';
        $mail->SetFrom('no-reply@gmail.com');
        $mail->Subject=$subject;
        $mail->Body=$body;
        $mail->AddAddress($address);
        $mail->Send();
    }
}
    
?>

