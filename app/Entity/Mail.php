<?php

namespace App\Entity;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public static function send($to_email = 'stan4lod@yandex.ru', $subject = 'Test Email', $text_html = '<h1>Test Text</h1>')
    {
        $mail = new PHPMailer(false);

        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'mail.nic.ru';                          //Set the SMTP server to send through
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = 'noreply@re-aktiv.ru';                  //SMTP username
        $mail->Password = 'HG!_@H#*&!^!HwJSDJ2Wsqgq';             //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
        $mail->Port = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('noreply@re-aktiv.ru');
        $mail->addAddress($to_email);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $text_html;

        $resp = $mail->send();

        return $resp;

    }

    public static function clear_phone($phone)
    {
        $remove_symbols = [
            '(',
            ')',
            '-',
            ' ',
            '+'
        ];
        return str_replace($remove_symbols, '', $phone);
    }
}