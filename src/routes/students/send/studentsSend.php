<?php


namespace Invoices;

require_once __DIR__ . "/model/studentsSendModel.php";


class studentsSend
{
    public function __construct()
    {
    }

    public function send_email_with_pdf(array $body)
    {
        if (isset($body['link']) && isset($body['detail_sheet_name']) && isset($body['email']) && isset($body['student_id'])) {
            return (new studentsSendModel())->send_mail_with_pdf($body['link'], $body['detail_sheet_name'], $body['email'], (int)$body['student_id']);
        } else {
            return array(
                'status' => 'error',
                'message' => 'missing_body',
                'date' => time()
            );
        }
    }
}