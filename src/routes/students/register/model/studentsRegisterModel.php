<?php

namespace Students;

use PDO;
use PDOException;

class studentsRegisterModel
{
    protected PDO $pdo;
    protected array $form_data;

    public function __construct(PDO $pdo, array $valid_form_data)
    {
        $this->pdo = $pdo;
        $this->form_data = $valid_form_data;
    }

    /**
     * Method to check if mail given by the user already exists in the database. If so, it will return an error message.
     * @return bool|null
     */
    public function check_email_existence(): ?bool
    {
        var_dump($this->form_data['validity']);
        $email = $this->form_data['email'];
        try {
            $req = $this->pdo->prepare('SELECT email FROM ges_storagehost_ch.students WHERE email = :email');
            $req->bindParam(':email', $email);
            $req->execute();

            // Check if request rows are higher than 0. If yes, it means that the email exists
            if ($req->rowCount() > 0) {
                return false;
            } else {
                // Email does not exist, so account creating is OK
                return true;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return null;
    }

    /**
     * Method used to insert data in the DB.
     * @return array|bool
     */
    public function create_student()
    {
        var_dump($this->form_data);
        try {
            $req = $this->pdo->prepare('INSERT INTO ges_storagehost_ch.students(student_license_number, validity, last_name, first_name, email, address, postal_code, city, date_of_birth, phone, category, insurance, categories_holder, exam_dates) VALUES (:student_license_number, :validity, :last_name, :first_name, :email, :address, :postal_code, :city, :date_of_birth, :phone, :category, :insurance, :categories_holder, :exam_dates)');
            $req->execute(
                array(
                    ':student_license_number' => $this->form_data['student_license_number'],
                    ':validity' => $this->form_data['validity'],
                    ':last_name' => $this->form_data['last_name'],
                    ':first_name' => $this->form_data['first_name'],
                    ':email' => $this->form_data['email'],
                    ':address' => $this->form_data['address'],
                    ':postal_code' => $this->form_data['postal_code'],
                    ':city' => $this->form_data['city'],
                    ':date_of_birth' => $this->form_data['date_of_birth'],
                    ':phone' => $this->form_data['phone'],
                    ':category' => $this->form_data['category'],
                    ':insurance' => $this->form_data['insurance'],
                    ':categories_holder' => $this->form_data['categories_holder'],
                    ':exam_dates' => 0
                )
            );
            if ($req) {
                // add entries to exam_dates table
                $last_id = $this->pdo->lastInsertId();
                $first_exam = $second_exam = $third_exam = "0000-00-00";

                if (!empty($this->form_data['1st_exam'])) {
                    $first_exam = $this->form_data['1st_exam'];
                }

                if (!empty($this->form_data['2nd_exam'])) {
                    $second_exam = $this->form_data['2nd_exam'];
                }

                if (!empty($this->form_data['3rd_exam'])) {
                    $third_exam = $this->form_data['3rd_exam'];
                }

                $req1 = $this->pdo->prepare('INSERT INTO ges_storagehost_ch.exam_dates(student_id, `1st_date`, `2nd_date`, `3rd_date`) VALUES (:id, :1st_date, :2nd_date, :3rd_date)');
                $req1->execute(array(
                    ':id' => $last_id,
                    ':1st_date' => $first_exam,
                    ':2nd_date' => $second_exam,
                    ':3rd_date' => $third_exam
                ));

                $req2 = $this->pdo->prepare('UPDATE ges_storagehost_ch.students SET exam_dates = :exam_dates WHERE email = :email');
                $req2->execute(array(
                    ':exam_dates' => $last_id,
                    ':email' => $this->form_data['email']
                ));

                if ($req1) {
                    $payload = [];
                    array_push($payload, array(
                        "status" => "success",
                        "data" => array(
                            'user_id' => $last_id,
                            'last_name' => $this->form_data['last_name'],
                            'first_name' => $this->form_data['first_name'],
                            'email' => $this->form_data['email'],
                            'date_of_birth' => $this->form_data['date_of_birth'],
                            'address' => $this->form_data['address'],
                            'postal_code' => $this->form_data['postal_code'],
                            'city' => $this->form_data['city'],
                            'phone' => $this->form_data['phone'],
                            'category' => $this->form_data['category'],
                            'categories_holder' => $this->form_data['categories_holder'],
                            'insurance' => $this->form_data['insurance'],
                            'exam_dates' => array(
                                'first_date' => $first_exam,
                                'second_date' => $second_exam,
                                'third_date' => $third_exam
                            )
                        )
                    ));
                } else {
                    return false;
                }

                return $payload;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
        return null;
    }
}