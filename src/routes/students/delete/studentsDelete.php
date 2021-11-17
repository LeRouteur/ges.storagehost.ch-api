<?php

namespace Students;

use PDO;

require_once "model/studentsDeleteModel.php";

class studentsDelete
{
    private int $id;
    private PDO $pdo;

    public function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;
    }

    public function delete_student(): bool|array
    {
        return (new studentsDeleteModel($this->pdo))->delete_student_by_id($this->id);
    }
}