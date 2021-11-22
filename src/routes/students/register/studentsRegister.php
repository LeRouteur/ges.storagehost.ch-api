<?php

/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.0
 */

namespace Students;

include __DIR__ . "/model/studentsRegisterModel.php";
require_once __DIR__ . "/../../../service/validation/Validation.php";

use Exception;
use PDO;
use Validation;

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
        if (empty($this->form_data['student_license_number']) || empty($this->form_data['validity'])
            || empty($this->form_data['last_name']) || empty($this->form_data['first_name'])
            || empty($this->form_data['date_of_birth']) || empty($this->form_data['address'])
            || empty($this->form_data['postal_code']) || empty($this->form_data['city'])
            || empty($this->form_data['phone']) || empty($this->form_data['category'])) return "bad_post";

        $result = $this->validate_data();
        if ($result) {
            // an error occurred, return it to the router
            return $result;
        } else {
            // no error occurred during validation, proceed
            $model = new studentsRegisterModel($this->pdo, $this->valid_form_data);
            if ($model->check_sln_existence()) {
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
        }
    }

    private
    function validate_data()
    {
        // validate the data from the form data array
        $validation = new Validation();

        $sln = $validation->validate_student_license_number($this->form_data['student_license_number']);
        if ($sln) {
            $this->valid_form_data['student_license_number'] = $sln;
        } else {
            return "bad_student_license_number";
        }

        $validity = $validation->validate_validity_date($this->form_data['validity']);
        if ($validity) {
            $this->valid_form_data['validity'] = $validity;
        } else {
            return "bad_validity_date";
        }

        $last_name = $validation->validate_last_name($this->form_data['last_name']);
        if ($last_name) {
            $this->valid_form_data['last_name'] = $last_name;
        } else {
            return "bad_last_name";
        }

        $first_name = $validation->validate_first_name($this->form_data['first_name']);
        if ($first_name) {
            $this->valid_form_data['first_name'] = $first_name;
        } else {
            return "bad_first_name";
        }

        if (isset($this->form_data['email'])) {
            $email = $validation->validate_email($this->form_data['email']);
            if ($email) {
                $this->valid_form_data['email'] = $email;
            } else {
                return "bad_email";
            }
        } else {
            $this->valid_form_data['email'] = null;
        }

        $dob = $validation->validate_date_of_birth($this->form_data['date_of_birth']);
        if ($dob) {
            $this->valid_form_data['date_of_birth'] = $dob;
        } else {
            return "bad_date_of_birth";
        }

        try {
            $this->valid_form_data['job'] = ucwords($this->form_data['job']);
        } catch (Exception $e) {
            $this->valid_form_data['job'] = null;
        }

        $address = $validation->validate_address($this->form_data['address']);
        if ($address) {
            $this->valid_form_data['address'] = $address;
        } else {
            return "bad_address";
        }

        $zip = $validation->validate_postal_code($this->form_data['postal_code']);
        if ($zip) {
            $this->valid_form_data['postal_code'] = $zip;
        } else {
            return "bad_postal_code";
        }

        $city = $validation->validate_city($this->form_data['city']);
        if ($city) {
            $this->valid_form_data['city'] = $city;
        } else {
            return "bad_city";
        }

        $phone = $validation->validate_phone_number($this->form_data['phone']);
        if ($phone) {
            $this->valid_form_data['phone'] = $phone;
        } else {
            return "bad_phone_number";
        }

        $category = $validation->validate_category($this->form_data['category']);
        if ($category) {
            $this->valid_form_data['category'] = $category;
        } else {
            return "bad_category";
        }

        if (isset($this->form_data['categories_holder'])) {
            $this->valid_form_data['categories_holder'] = $this->form_data['categories_holder'];
        } else {
            $this->valid_form_data['categories_holder'] = null;
        }

        // Validate exam dates
        $exam_dates = $this->form_data['exam_dates'];
        $first_exam = $second_exam = $third_exam = "";

        if (!empty($exam_dates[0])) {
            $first_exam = date("Y-m-d", strtotime($exam_dates[0]));
            $this->valid_form_data['1st_exam'] = $first_exam;
        }

        if (!empty($exam_dates[1])) {
            $second_exam = date("Y-m-d", strtotime($exam_dates[1]));
            $this->valid_form_data['2nd_exam'] = $second_exam;
        }

        if (!empty($exam_dates[2])) {
            $third_exam = date("Y-m-d", strtotime($exam_dates[2]));
            $this->valid_form_data['3rd_exam'] = $third_exam;
        }
    }
}