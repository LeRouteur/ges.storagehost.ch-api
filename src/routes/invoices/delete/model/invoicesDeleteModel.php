<?php


namespace Invoices;


use PDO;
use PDOException;

class invoicesDeleteModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete_invoice_by_id(int $id): bool|array
    {
        if ($this->check_if_invoice_exist_by_id($id)) {
            try {
                $req = $this->pdo->prepare('DELETE FROM ges_storagehost_ch.invoices WHERE invoice_id = :id');
                $req->execute(array(
                    ':id' => $id
                ));
                return true;
            } catch (PDOException $e) {
                return array(
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'date' => time()
                );
            }
        } else {
            // invoice does not exist, print a 404
            return false;
        }
    }

    private function check_if_invoice_exist_by_id(int $id): bool|array
    {
        try {
            $req = $this->pdo->prepare('SELECT invoice_id FROM ges_storagehost_ch.invoices WHERE ges_storagehost_ch.invoices.invoice_id = :id');
            $req->execute(array(
                ':id' => $id
            ));
            $result = $req->fetch();

            if (!$result) {
                // invoice does not exist
                return false;
            } else {
                return true;
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