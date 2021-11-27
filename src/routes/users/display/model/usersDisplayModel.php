<?php


namespace Users;


use PDO;
use PDOException;

class usersDisplayModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_user_email(int $id): array
    {
        try {
            $req = $this->pdo->prepare('SELECT email FROM ges_storagehost_ch.users WHERE id = :id');
            $req->bindParam(':id', $id);
            $req->execute();

            if ($req->rowCount() > 0) {
                return array(
                    'status' => 'success',
                    'data' => $req->fetch(),
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'user_not_found',
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

    public function get_user_info(int $id): array
    {
        try {
            $req = $this->pdo->prepare('SELECT last_name, first_name FROM ges_storagehost_ch.users WHERE id = :id');
            $req->bindParam(':id', $id);
            $req->execute();

            if ($req->rowCount() > 0) {
                return array(
                    'status' => 'success',
                    'data' => $req->fetch(),
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'user_not_found',
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