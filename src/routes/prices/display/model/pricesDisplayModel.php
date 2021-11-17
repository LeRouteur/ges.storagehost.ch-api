<?php


namespace Prices;

use PDO;
use PDOException;

class pricesDisplayModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_prices(): array
    {
        try {
            $req = $this->pdo->prepare('SELECT * FROM ges_storagehost_ch.prices');
            $req->execute();
            return array(
                'status' => 'success',
                'data' => $req->fetchAll(),
                'date' => time()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}