<?php


namespace Invoices;


use PDO;
use PDOException;

class invoicesModifyModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function modify_invoice_by_id(array $data): array
    {
        try {
            $req = $this->pdo->prepare('UPDATE ges_storagehost_ch.invoices SET paid = :paid, paid_by = :paid_by, total = :total WHERE invoice_id = :id');
            $req->execute(array(
                ':id' => $data['id'],
                ':paid' => $data['paid'],
                ':paid_by' => $data['paid_by'],
                ':total' => $data['total']
            ));

            if ($req) {
                return array(
                    'status' => 'success',
                    'data' => $data,
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'invoice_not_found',
                    'date' => time()
                );
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}