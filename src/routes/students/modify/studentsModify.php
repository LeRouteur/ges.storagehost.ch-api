<?php

namespace Students;

use PDO;

require_once "model/studentsModifyModel.php";

class studentsModify
{
    private array $data;
    private int $id;
    private PDO $pdo;

    public function __construct(array $data, int $id, PDO $pdo)
    {
        $this->data = $data;
        $this->id = $id;
        $this->pdo = $pdo;
    }

    public function modify_student()
    {
        return (new studentsModifyModel($this->data, $this->pdo))->modify_student_by_id($this->id);
    }
}