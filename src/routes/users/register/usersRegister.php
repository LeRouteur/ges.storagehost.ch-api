<?php

/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.0
 */

namespace Users;

include __DIR__ . "/model/usersRegisterModel.php";

use PDO;

class usersRegister
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
        if (!empty($this->form_data) && !empty($this->form_data['last_name']) && !empty($this->form_data['first_name']) && !empty($this->form_data['email']) && !empty($this->form_data['password']) && !empty($this->form_data['password_conf'])) {
            //var_dump($this->form_data);

            $result = $this->check_password();
            if (is_null($result)) {
                // No error occurred during password treatment, proceeding
                $result = $this->trim();
                //var_dump($result);
                if (is_array($result)) {
                    // No error occurred during trim, proceeding
                    $result_validation = $this->validate_form_data($result);
                    if (is_array($result_validation)) {
                        // No error occurred during validation, proceeding by calling the model
                        $model = new usersRegisterModel($this->pdo, $this->valid_form_data);
                        if ($model->check_email_existence()) {
                            // User does not exist in the database, proceeding
                            $result_user_creation = $model->create_user();
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
                return $result;
            }
        } else {
            return "bad_post";
        }
    }

    private function check_password()
    {
        // Compare the two strings
        if ($this->form_data['password'] == $this->form_data['password_conf']) {
            $final_password = $this->form_data['password'];

            $uppercase = preg_match('@[A-Z]@', $final_password);
            $lowercase = preg_match('@[a-z]@', $final_password);
            $number = preg_match('@[0-9]@', $final_password);
            //$specialChars = preg_match('@[^\w]@', $password);

            if ($uppercase && $lowercase && $number && strlen($final_password) >= 8) {

                // Add hashed password in the array
                $this->valid_form_data['password'] = password_hash($final_password, PASSWORD_DEFAULT);
            } else {
                return "password_not_meeting_requirements";
            }

        } else {
            // If password isn't the same as the confirmation, delete the array and print error
            return "passwords_do_not_match";
        }
        return null;
    }

    private function trim()
    {
        // Give an array of unwanted chars
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

        // Create a new array who will store the validated values
        $caseFormData = array();

        //var_dump($trimedFormData);

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

        //var_dump($caseFormData);
        return $caseFormData;
    }

    private function validate_form_data(array $caseFormData)
    {
        // Check if vars are empty
        if (empty($caseFormData) || empty($caseFormData['last_name']) || empty($caseFormData['first_name']) || empty($caseFormData['email'])) return "error";


        if (filter_var($caseFormData['last_name'], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['last_name'] = preg_replace('/\d+/u', '', $caseFormData['last_name']);
        } else {
            return "bad_last_name";
        }

        if (filter_var($caseFormData['first_name'], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['first_name'] = preg_replace('/\d+/u', '', $caseFormData['first_name']);
        } else {
            return "bad_first_name";
        }

        // Validate email
        if (filter_var($caseFormData['email'], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL)) {
            $this->valid_form_data['email'] = $caseFormData['email'];
        } else {
            return "bad_email";
        }

        //var_dump($this->valid_form_data);

        return $this->valid_form_data;
    }
}