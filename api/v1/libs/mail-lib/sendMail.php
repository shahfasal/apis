<?php

function sendMail($info) {
    require 'PHPMailerAutoload.php';

    $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'abhishek.ramkrishna002@gmail.com';                 // SMTP username
    $mail->Password = '1010010110';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    $mail->From = $info['from_email'];
    $mail->FromName = $info['from_name'];
    $mail->addAddress($info['to_email'],$info['to_name']);     // Add a recipient
    //$mail->addAddress('abhishek.ramkrishna002@gmail.com');               // Name is optional
    //$mail->addReplyTo('abhishek.ramkrishna002@gmail.com', 'Information');
    //$mail->addCC('abhishek.ramkrishna002@gmail.com');
    $mail->addBCC('fassha08@gmail.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $info['subject'];
    $mail->Body = $info['body'];
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if (!$mail->send()) {
        //echo 'Message could not be sent.';
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
        return false;
    } else {
        //echo 'Message has been sent';
        return true;
    }
}
