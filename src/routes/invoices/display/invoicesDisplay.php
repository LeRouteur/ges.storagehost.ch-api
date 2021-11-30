<?php

namespace Invoices;

use PDO;

require_once __DIR__ . "/model/invoicesDisplayModel.php";

class invoicesDisplay
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_invoices()
    {
        return (new invoicesDisplayModel($this->pdo))->get_invoices();
    }

    public function get_invoice_by_id(int $id)
    {
        return (new invoicesDisplayModel($this->pdo))->get_invoice_by_id($id);
    }
}