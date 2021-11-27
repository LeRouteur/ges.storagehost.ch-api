<?php


namespace Users;

use PDO;

require_once __DIR__ . "/model/usersDisplayModel.php";

class usersDisplay
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_user_email(int $id): array
    {
        return (new usersDisplayModel($this->pdo))->get_user_email($id);
    }

    public function get_user_info(int $id): array
    {
        return (new usersDisplayModel($this->pdo))->get_user_info($id);
    }
}