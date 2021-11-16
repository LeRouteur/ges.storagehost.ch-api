<?php

namespace Students;

use JetBrains\PhpStorm\ArrayShape;
use PDO;
use PDOException;

class studentsModifyModel
{
    private array $data;
    private PDO $pdo;

    public function __construct(array $data, PDO $pdo)
    {
        $this->data = $data;
        $this->pdo = $pdo;
    }

    #[ArrayShape(['status' => "string", 'message' => "string", 'date' => "int"])] public function modify_student_by_id(int $id): array
    {
        var_dump($this->data);
        try {
            $req = $this->pdo->prepare("UPDATE ges_storagehost_ch.students SET student_license_number = :student_license_number, validity = :validity, last_name = :last_name, first_name = :first_name, email = :email, address = :address, postal_code = :postal_code, city = :city, date_of_birth = :date_of_birth, phone = :phone, category = :category, insurance = :insurance, categories_holder = :categories_holder WHERE id = :id");
            $req->execute(array(
                ':student_license_number' => $this->data['student_license_number'],
                ':validity' => $this->data['validity'],
                ':last_name' => $this->data['last_name'],
                ':first_name' => $this->data['first_name'],
                ':email' => $this->data['email'],
                ':address' => $this->data['address'],
                ':postal_code' => $this->data['postal_code'],
                ':city' => $this->data['city'],
                ':date_of_birth' => $this->data['date_of_birth'],
                ':phone' => $this->data['phone'],
                ':category' => $this->data['category'],
                ':insurance' => $this->data['insurance'],
                ':categories_holder' => $this->data['categories_holder'],
                ':id' => $id

            ));
            if ($req) {
                // modify exam dates (force the rewrite each time, can be optimized)
                $req1 = $this->pdo->prepare('UPDATE ges_storagehost_ch.exam_dates SET `1st_date` = :1st_date, `2nd_date` = :2nd_date, `3rd_date` = :3rd_date WHERE student_id = :student_id');
                $req1->execute(array(
                    ':1st_date' => $this->data['exam_dates'][0]['1st'],
                    ':2nd_date' => $this->data['exam_dates'][0]['2nd'],
                    ':3rd_date' => $this->data['exam_dates'][0]['3rd'],
                    ':student_id' => $id
                ));

                return array(
                    'status' => 'success',
                    'message' => 'user_' . $id . '_modified',
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'failed_to_modify_user',
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