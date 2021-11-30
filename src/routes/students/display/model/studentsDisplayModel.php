<?php


namespace Students;


use PDO;
use PDOException;

class studentsDisplayModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_all_students(): array
    {
        try {
            $req = $this->pdo->prepare('SELECT id, student_license_number, validity, last_name, first_name, email, address, postal_code, city, date_of_birth, job, phone, category, categories_holder, `1st_date`, `2nd_date`, `3rd_date` FROM ges_storagehost_ch.students INNER JOIN exam_dates ed on students.id = ed.student_id');
            $req->execute();
            $students = $req->fetchAll();

            if (count($students) > 0) {
                return array(
                    'status' => 'success',
                    'data' => $students,
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'success',
                    'message' => 'no_students',
                    'date' => time()
                );
            }

        } catch (PDOException $exception) {
            return array(
                'status' => 'error',
                'message' => $exception->getMessage(),
                'date' => time()
            );
        }
    }

    public function get_student_by_id(int $id)
    {
        try {
            $req = $this->pdo->prepare('SELECT id, student_license_number, validity, last_name, first_name, email, address, postal_code, city, date_of_birth, job, phone, category, categories_holder, `1st_date`, `2nd_date`, `3rd_date` FROM ges_storagehost_ch.students INNER JOIN exam_dates ed on students.id = ed.student_id WHERE ges_storagehost_ch.students.id = :id');
            $req->bindParam(':id', $id) && $req->execute();
            $student = $req->fetch();

            if (!empty($student)) {
                return array(
                    'status' => 'success',
                    'data' => $student,
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'success',
                    'message' => 'no_student_found',
                    'date' => time()
                );
            }

        } catch (PDOException $exception) {
            return array(
                'status' => 'error',
                'message' => $exception->getMessage(),
                'date' => time()
            );
        }
    }

    public function get_student_lessons_by_id(int $id)
    {
        try {
            $req = $this->pdo->prepare('SELECT * FROM ges_storagehost_ch.lesson_details WHERE ges_storagehost_ch.lesson_details.student_id = :id');
            $req->bindParam(':id', $id) && $req->execute();
            $student = $req->fetchAll();

            if (!empty($student)) {
                return array(
                    'status' => 'success',
                    'data' => $student,
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'success',
                    'message' => 'no_lessons',
                    'date' => time()
                );
            }

        } catch (PDOException $exception) {
            return array(
                'status' => 'error',
                'message' => $exception->getMessage(),
                'date' => time()
            );
        }
    }
}