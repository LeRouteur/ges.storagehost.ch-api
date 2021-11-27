<?php

namespace Invoices;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../../../../config/Config.php";

class invoicesSendModel
{
    public function __construct()
    {
    }

    public function send_mail_with_pdf(string $link_to_pdf, string $invoice_name, string $email, int $id)
    {
        // Set subject and body
        $subject = "STORAGEHOST - facture " . $id;
        $message = "Bonjour,<br/>
        Voici le lien vers votre facture " . $invoice_name . "<br/><br/>
        <a href='$link_to_pdf'>" . $link_to_pdf . "</a>
        <br/>
        <br/>       
        ---------------<br/>
        Cet e-mail est généré automatiquement, merci de ne pas y répondre.<br/>
        En cas de problème, merci de contacter l'administrateur en créant un ticket sur https://helpdesk.storagehost.ch.";

        // Create new PHPMailer
        $mail = new PHPMailer(true);

        $binary_content = file_get_contents($link_to_pdf);

        if ($binary_content == false) {
            throw new \Exception('Could not fetch content.');
        }

        try {
            // Define server settings
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = EMAIL_SERVER;
            $mail->SMTPAuth = true;
            $mail->Username = EMAIL_ADDRESS;
            $mail->Password = EMAIL_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = EMAIL_SERVER_PORT;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Define sender and recipients settings
            $mail->setFrom(EMAIL_ADDRESS, 'STORAGEHOST - Hosting Services');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->addStringAttachment($binary_content, $invoice_name, $encoding = 'base64', $type = 'application/pdf');
            $mail->send();

            return array(
                'status' => 'success',
                'message' => 'mail_sent',
                'date' => time()
            );
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}