<?php


namespace Invoices;

require_once __DIR__ . "/model/invoicesSendModel.php";


class invoicesSend
{
    public function __construct()
    {
    }

    public function send_email_with_pdf(array $body)
    {
        if (isset($body['link']) && isset($body['invoice_name']) && isset($body['email']) && isset($body['id'])) {
            return (new studentsSendModel())->send_mail_with_pdf($body['link'], $body['invoice_name'], $body['email'], (int)$body['id']);
        } else {
            return array(
                'status' => 'error',
                'message' => 'missing_body',
                'date' => time()
            );
        }
    }
}