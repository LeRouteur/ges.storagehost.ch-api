<?php

namespace Invoices;

use PDO;

require_once __DIR__ . "/model/invoicesModifyModel.php";

class invoicesModify
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function modify_invoice_by_id(array $data): array
    {
        return (new invoicesModifyModel($this->pdo))->modify_invoice_by_id($data);
    }
}