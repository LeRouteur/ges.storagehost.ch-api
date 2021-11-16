<?php

/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.0
 */

namespace Students;

include __DIR__ . "/model/studentsRegisterModel.php";

use PDO;

class studentsRegister
{

    private PDO $pdo;
    private array $form_data;
    private array $valid_form_data;

    /**
     * Register constructor.
     * @param array $form_data
     * @param PDO $pdo
     */
    public function __construct(array $form_data, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->form_data = $form_data;
        $this->valid_form_data = array();
    }

    /**
     * Method used to receive the data and validate it.
     * @return array|string
     */
    public function get_form_data()
    {
        if (!empty($this->form_data) && !empty($this->form_data['student_license_number']) && !empty($this->form_data['validity']) && !empty($this->form_data['last_name']) && !empty($this->form_data['first_name']) && !empty($this->form_data['email']) && !empty($this->form_data['date_of_birth']) && !empty($this->form_data['job']) && !empty($this->form_data['address']) && !empty($this->form_data['postal_code']) && !empty($this->form_data['city']) && !empty($this->form_data['phone']) && !empty($this->form_data['category']) && !empty($this->form_data['categories_holder']) && !empty($this->form_data['insurance']) && !empty($this->form_data['exam_dates']) && is_array($this->form_data['exam_dates'])) {
            $result = $this->trim();

            if (is_array($result)) {
                $result_validation = $this->validate_form_data($result);
                if (is_array($result_validation)) {
                    // No error occurred during validation, proceeding by calling the model
                    $model = new studentsRegisterModel($this->pdo, $this->valid_form_data);
                    if ($model->check_email_existence()) {
                        // User does not exist in the database, proceeding
                        $result_user_creation = $model->create_student();
                        if (is_array($result_user_creation)) {
                            return $result_user_creation;
                        } else {
                            return "user_creation_error";
                        }
                    } else {
                        return "user_already_exists";
                    }

                } else {
                    return $result_validation;
                }
            } else {
                return $result;
            }
        } else {
            return "bad_post";
        }
    }

    private function trim(): array
    {
        // Give an array of unwanted chars
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

        // Create a new array who will store the validated values
        $caseFormData = array();

        // Lower firstname and lastname, and put first word in upper case
        $lastname = strtolower($this->form_data['last_name']);
        $lastname = ucwords($lastname);

        // Clear the accentuation
        $lastname = strtr($lastname, $unwanted_array);
        $caseFormData['last_name'] = $lastname;

        $firstname = strtolower($this->form_data['first_name']);
        $firstname = ucwords($firstname);

        // Clear the accentuation
        $firstname = strtr($firstname, $unwanted_array);
        $caseFormData['first_name'] = $firstname;

        // Lower email address (email cannot have any upper case letter)
        $email = $this->form_data['email'];
        $email = strtolower($email);
        $caseFormData['email'] = $email;

        // Uppercase the category
        $caseFormData['category'] = strtoupper($this->form_data['category']);

        // Lower complete address (without ZIP)
        // Also clear comma(s) in address and city
        $address = $this->form_data['address'];
        $address = str_replace(',', '', $address);
        $address = strtolower($address);
        $address = ucwords($address);
        $caseFormData['address'] = $address;

        // Add ZIP code in the array
        $caseFormData['postal_code'] = $this->form_data['postal_code'];

        return $caseFormData;
    }

    private function validate_form_data(array $data)
    {
        // Check if vars are empty
        if (empty($data) || empty($this->form_data['student_license_number']) || empty($this->form_data['validity'])
            || empty($data['last_name']) || empty($data['first_name'])
            || empty($data['email']) || empty($this->form_data['date_of_birth'])
            || empty($this->form_data['job']) || empty($data['address'])
            || empty($data['postal_code']) || empty($this->form_data['city'])
            || empty($this->form_data['phone']) || empty($data['category'])
            || empty($this->form_data['categories_holder']) || empty($this->form_data['insurance'])
            || empty($this->form_data['exam_dates'])) return "error";


        // Validate student license number
        if (strlen($this->form_data['student_license_number']) == 7) {
            $this->valid_form_data['student_license_number'] = $this->form_data['student_license_number'];
        } else {
            return "bad_student_license_number";
        }

        // Validate validity date
        $validity = $this->form_data['validity'];
        $new_validity = date("Y-m-d", strtotime($validity));
        $new_validity = explode("-", $new_validity);

        if ($new_validity[0] < date('Y')) {
            // Not possible
            return "bad_validity_date";
        } else {
            $this->valid_form_data['validity'] = $new_validity[0] . "-" . $new_validity[1] . "-" . $new_validity[2];
        }

        // Validate last name
        if (filter_var($data['last_name'], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['last_name'] = preg_replace('/\d+/u', '', $data['last_name']);
        } else {
            return "bad_last_name";
        }

        // Validate first name
        if (filter_var($data['first_name'], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['first_name'] = preg_replace('/\d+/u', '', $data['first_name']);
        } else {
            return "bad_first_name";
        }

        // Validate email
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL)) {
            $this->valid_form_data['email'] = $data['email'];
        } else {
            return "bad_email";
        }

        // Validate date of birth
        $date_of_birth = $this->form_data['date_of_birth'];
        $new_date_of_birth = date("Y-m-d", strtotime($date_of_birth));
        $new_date_of_birth = explode("-", $new_date_of_birth);

        if ($new_date_of_birth[0] > date('Y') - 16) {
            // Not possible
            return "bad_date_of_birth";
        } else {
            $this->valid_form_data['date_of_birth'] = $new_date_of_birth[0] . "-" . $new_date_of_birth[1] . "-" . $new_date_of_birth[2];
        }

        // Validate address
        if (preg_match('/[A-Za-z0-9\-,.]+/', $data['address'])) {
            $this->valid_form_data['address'] = $data['address'];
        } else {
            return "bad_address";
        }

        // Validate postal code
        if (filter_var((int)$this->form_data['postal_code'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1000, "max_range" => 9999)))) {
            $this->valid_form_data['postal_code'] = $this->form_data['postal_code'];
        } else {
            return "bad_zip";
        }

        // Validate city
        $validCity = $this->form_data['city'];
        if (filter_var($validCity, FILTER_SANITIZE_STRING)) {
            $validCity = preg_replace('/\d+/u', '', $validCity);
            $this->valid_form_data['city'] = $validCity;
        } else {
            return "bad_city";
        }

        // Validate phone number
        $valid_phone_number = $this->form_data['phone'];
        if (preg_match("/(\b(0041|0)|\B\+41)(\s?\(0\))?(\s)?[1-9]{2}(\s)?[0-9]{3}(\s)?[0-9]{2}(\s)?[0-9]{2}\b/", $valid_phone_number) || preg_match("/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/", $valid_phone_number)) {
            $this->valid_form_data['phone'] = $valid_phone_number;
        } else {
            return "bad_phone";
        }

        // Validate category
        $category = $data['category'];
        if ($category == "A1" || $category == "A35" || $category == "A" || $category == "B" || $category == "C1" || $category == "D1" || $category == "C" || $category == "D" || $category == "BE" || $category == "CE" || $category == "OACP" || $category == "TPP121") {
            $this->valid_form_data['category'] = $category;
        } else {
            return "bad_category";
        }

        // Add categories holder
        $this->valid_form_data['categories_holder'] = $this->form_data['categories_holder'];

        // Validate insurance
        $insurance = $this->form_data['insurance'];
        if ($insurance == "Allianz Suisse" || $insurance == "AXA" || $insurance == "Bâloise" || $insurance == "click2drive.ch" || $insurance == "ELVIA" || $insurance == "Generali" || $insurance == "Helvetia" || $insurance == "La Mobilière" || $insurance == "PostFinance" || $insurance == "Simpego" || $insurance == "smile.direct" || $insurance == "AXA" || $insurance == "Sympany" || $insurance == "TCS" || $insurance == "Vaudoise" || $insurance == "Zurich") {
            $this->valid_form_data['insurance'] = $insurance;
        } else {
            return "bad_insurance";
        }

        // Validate exam dates
        $exam_dates = $this->form_data['exam_dates'];
        $first_exam = $second_exam = $third_exam = "";

        if (!empty($exam_dates['1st'])) {
            $first_exam = date("Y-m-d", strtotime($exam_dates['1st']));
            $this->valid_form_data['1st_exam'] = $first_exam;
        }

        if (!empty($exam_dates['2nd'])) {
            $second_exam = date("Y-m-d", strtotime($exam_dates['2nd']));
            $this->valid_form_data['2nd_exam'] = $second_exam;
        }

        if (!empty($exam_dates['3rd'])) {
            $third_exam = date("Y-m-d", strtotime($exam_dates['3rd']));
            $this->valid_form_data['3rd_exam'] = $third_exam;
        }

        //var_dump($this->valid_form_data);
        return $this->valid_form_data;
    }
}