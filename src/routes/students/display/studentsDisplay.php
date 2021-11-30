<?php

namespace Students;

use PDO;

require_once "model/studentsDisplayModel.php";

class studentsDisplay
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function display_students(): array
    {
        return (new studentsDisplayModel($this->pdo))->get_all_students();
    }

    public function display_student(int $id): array
    {
        return (new studentsDisplayModel($this->pdo))->get_student_by_id($id);
    }

    public function display_student_lessons(int $id)
    {
        return (new studentsDisplayModel($this->pdo))->get_student_lessons_by_id($id);
    }
}