<?php

namespace Prices;

use PDO;
use PDOException;

class pricesModifyModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function update_prices(array $data)
    {
        var_dump($data);
        try {
            $req = $this->pdo->prepare('UPDATE ges_storagehost_ch.prices SET casco = :casco, lesson = :lesson, 
                                     exam = :exam, oacp = :oacp, 
                                     theorical_lesson = :theorical_lesson WHERE category = :category');
            $req->execute(array(
                ':casco' => $data['casco'],
                ':lesson' => $data['lesson'],
                ':exam' => $data['exam'],
                ':oacp' => $data['oacp'],
                ':theorical_lesson' => $data['theorical_lesson'],
                ':category' => $data['category']
            ));
            return array(
                'status' => 'success',
                'data' => $data,
                'date' => time()
            );
        } catch (PDOException $exception) {
            return array(
                'status' => 'error',
                'message' => $exception->getMessage(),
                'date' => time()
            );
        }
    }
}