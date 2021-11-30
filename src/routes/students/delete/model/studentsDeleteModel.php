<?php


namespace Students;


use PDO;
use PDOException;

class studentsDeleteModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete_student_by_id(int $id): bool|array
    {
        if ($this->check_if_student_exist_by_id($id)) {
            try {
                $req = $this->pdo->prepare('DELETE FROM ges_storagehost_ch.invoices WHERE student_id = :id');
                $req->execute(array(
                    ':id' => $id
                ));

                $req1 = $this->pdo->prepare('DELETE FROM ges_storagehost_ch.lesson_details WHERE student_id = :id');
                $req1->execute(array(
                    ':id' => $id
                ));

                $req2 = $this->pdo->prepare('DELETE FROM ges_storagehost_ch.exam_dates WHERE student_id = :id');
                $req2->execute(array(
                    ':id' => $id
                ));

                $req3 = $this->pdo->prepare('DELETE FROM ges_storagehost_ch.students WHERE id = :id');
                $req3->execute(array(
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
            // student does not exist, print a 404
            return false;
        }
    }

    private function check_if_student_exist_by_id(int $id): bool|array
    {
        try {
            $req = $this->pdo->prepare('SELECT id FROM ges_storagehost_ch.students WHERE ges_storagehost_ch.students.id = :id');
            $req->execute(array(
                ':id' => $id
            ));
            $result = $req->fetch();

            if (!$result) {
                // student does not exist
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