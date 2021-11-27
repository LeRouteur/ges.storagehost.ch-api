<?php

namespace Invoices;

use Invoices\invoicesDeleteModel;
use PDO;

require_once __DIR__ . "/model/invoicesDeleteModel.php";

class invoicesDelete
{
    private int $id;
    private PDO $pdo;

    public function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;
    }

    public function delete_invoice(): bool|array
    {
        return (new invoicesDeleteModel($this->pdo))->delete_invoice_by_id($this->id);
    }
}