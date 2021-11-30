<?php

namespace Students;

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

    public function modify_student_by_id(int $id): array
    {
        // check if student exists
        if ($this->check_if_student_exist_by_id($id)) {
            try {
                $req = $this->pdo->prepare("UPDATE ges_storagehost_ch.students SET student_license_number = :student_license_number, validity = :validity, last_name = :last_name, first_name = :first_name, email = :email, date_of_birth = :date_of_birth, job = :job, address = :address, postal_code = :postal_code, city = :city, phone = :phone, category = :category, categories_holder = :categories_holder WHERE id = :id");
                $req->execute(array(
                    ':student_license_number' => $this->data['student_license_number'],
                    ':validity' => $this->data['validity'],
                    ':last_name' => $this->data['last_name'],
                    ':first_name' => $this->data['first_name'],
                    ':email' => $this->data['email'],
                    ':date_of_birth' => $this->data['date_of_birth'],
                    ':job' => $this->data['job'],
                    ':address' => $this->data['address'],
                    ':postal_code' => $this->data['postal_code'],
                    ':city' => $this->data['city'],
                    ':phone' => $this->data['phone'],
                    ':category' => $this->data['category'],
                    ':categories_holder' => $this->data['categories_holder'],
                    ':id' => $id

                ));
                if ($req) {
                    // modify exam dates (force the rewrite each time, can be optimized)
                    $req1 = $this->pdo->prepare('UPDATE ges_storagehost_ch.exam_dates SET `1st_date` = :1st_date, `2nd_date` = :2nd_date, `3rd_date` = :3rd_date WHERE student_id = :student_id');
                    $req1->execute(array(
                        ':1st_date' => $this->data['1st_exam'],
                        ':2nd_date' => $this->data['2nd_exam'],
                        ':3rd_date' => $this->data['3rd_exam'],
                        ':student_id' => $id
                    ));

                    return array(
                        'status' => 'success',
                        'data' => array(
                            'student_id' => $id,
                            'last_name' => $this->data['last_name'],
                            'first_name' => $this->data['first_name'],
                            'email' => $this->data['email'],
                            'date_of_birth' => $this->data['date_of_birth'],
                            'job' => $this->data['job'],
                            'address' => $this->data['address'],
                            'postal_code' => $this->data['postal_code'],
                            'city' => $this->data['city'],
                            'phone' => $this->data['phone'],
                            'category' => $this->data['category'],
                            'categories_holder' => $this->data['categories_holder'],
                            'exam_dates' => array(
                                'first_date' => $this->data['1st_exam'],
                                'second_date' => $this->data['2nd_exam'],
                                'third_date' => $this->data['3rd_exam']
                            )
                        ),
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
        } else {
            return array(
                'status' => 'error',
                'message' => 'student_does_not_exist',
                'date' => time()
            );
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

    // update already existing student lessons
    public function modify_student_lessons_by_id(): array
    {
        try {
            foreach ($this->data as $datum) {
                $req = $this->pdo->prepare('UPDATE lesson_details SET date = :date, duration = :duration, student_comment = :student_comment, teacher_comment = :teacher_comment WHERE lesson_id = :lesson_id');
                $req->execute(array(
                    ':date' => $datum['date'],
                    ':duration' => $datum['lesson_duration'],
                    ':student_comment' => $datum['student_comment'],
                    ':teacher_comment' => $datum['teacher_comment'],
                    ':lesson_id' => $datum['lesson_id']
                ));
            }
            return array(
                'status' => 'success',
                'data' => $this->data,
                'date' => time()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

    // add lesson to student
    public function add_student_lessons(): array
    {
        $student_id = $this->data['student_id'];

        // remove student_id from the array
        array_splice($this->data, 0, 1);

        try {
            $data = array();

            foreach ($this->data as $datum) {
                $req = $this->pdo->prepare('INSERT INTO ges_storagehost_ch.lesson_details(student_id, date, duration, student_comment, teacher_comment) VALUES (:student_id, :date, :duration, :student_comment, :teacher_comment)');
                $req->execute(array(
                    ':date' => $datum['date'],
                    ':duration' => $datum['lesson_duration'],
                    ':student_comment' => $datum['student_comment'],
                    ':teacher_comment' => $datum['teacher_comment'],
                    ':student_id' => $student_id
                ));

                $array = array(
                    'lesson_id' => $this->pdo->lastInsertId(),
                    'student_id' => $student_id,
                    'date' => $datum['date'],
                    'duration' => $datum['lesson_duration'],
                    'student_comment' => $datum['student_comment'],
                    'teacher_comment' => $datum['teacher_comment']
                );

                array_push($data, $array);
            }

            return array(
                'status' => 'success',
                'data' => $data,
                'date' => time()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}