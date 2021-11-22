<?php

use JetBrains\PhpStorm\Pure;

class Validation
{
    private array $unwanted_array;

    public function __construct()
    {
        $this->unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
    }

    public function validate_last_name(string $last_name): bool|string
    {
        // Lower firstname and lastname, and put first word in upper case
        $lastname = strtolower($last_name);
        $lastname = ucwords($lastname);

        // Clear the accentuation
        $lastname = strtr($lastname, $this->unwanted_array);

        if (filter_var($lastname, FILTER_SANITIZE_STRING)) {
            return preg_replace('/\d+/u', '', $lastname);
        } else {
            return false;
        }
    }

    public function validate_first_name(string $first_name): bool|string
    {
        $firstname = strtolower($first_name);
        $firstname = ucwords($firstname);

        // Clear the accentuation
        $firstname = strtr($firstname, $this->unwanted_array);

        if (filter_var($firstname, FILTER_SANITIZE_STRING)) {
            return preg_replace('/\d+/u', '', $firstname);
        } else {
            return false;
        }
    }

    #[Pure] public function validate_email(string $email): bool|string
    {
        $email = strtolower($email);

        if (filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL)) {
            return $email;
        } else {
            return false;
        }
    }

    #[Pure] public function validate_category(string $category): bool|string
    {
        $category = strtoupper($category);

        if ($category == "C1/D1" || $category == "A/A1/A35" || $category == "B" || $category == "C" || $category == "D" || $category == "BE" || $category == "CE" || $category == "OACP" || $category == "TPP121/122") {
            return $category;
        } else {
            return false;
        }
    }

    public function validate_insurance(string $insurance): bool|string
    {
        if ($insurance == "Allianz Suisse" || $insurance == "AXA" || $insurance == "Bâloise" || $insurance == "click2drive.ch" || $insurance == "ELVIA" || $insurance == "Generali" || $insurance == "Helvetia" || $insurance == "La Mobilière" || $insurance == "PostFinance" || $insurance == "Simpego" || $insurance == "smile.direct" || $insurance == "AXA" || $insurance == "Sympany" || $insurance == "TCS" || $insurance == "Vaudoise" || $insurance == "Zurich") {
            return $insurance;
        } else {
            return false;
        }
    }

    public function validate_address(string $address): bool|string
    {
        $address = str_replace(',', '', $address);
        $address = strtolower($address);
        $address = ucwords($address);

        // Validate address
        if (preg_match('/[A-Za-z0-9\-,.]+/', $address)) {
            return $address;
        } else {
            return false;
        }
    }

    public function validate_postal_code(string $postal_code): bool|string|int
    {
        if (filter_var((int)$postal_code, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1000, "max_range" => 9999)))) {
            return $postal_code;
        } else {
            return false;
        }
    }

    public function validate_city(string $city): array|string|null
    {
        $city = strtolower($city);
        $city = ucwords($city);

        if (filter_var($city, FILTER_SANITIZE_STRING)) {
            return preg_replace('/\d+/u', '', $city);
        } else {
            return false;
        }
    }

    public function validate_phone_number(string $phone_number): bool|string
    {
        if (preg_match("/(\b(0041|0)|\B\+41)(\s?\(0\))?(\s)?[1-9]{2}(\s)?[0-9]{3}(\s)?[0-9]{2}(\s)?[0-9]{2}\b/", $phone_number) || preg_match("/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/", $phone_number)) {
            return $phone_number;
        } else {
            return false;
        }
    }

    #[Pure] public function validate_student_license_number(string $student_license_number): bool|string
    {
        // Validate student license number
        if (strlen($student_license_number) == 7) {
            return $student_license_number;
        } else {
            return false;
        }
    }

    public function validate_validity_date(string $validity_date): bool|string
    {
        // Validate validity date
        $new_validity = date("Y-m-d", strtotime($validity_date));
        $new_validity = explode("-", $new_validity);

        if ($new_validity[0] < date('Y')) {
            return false;
        } else {
            return $new_validity[0] . "-" . $new_validity[1] . "-" . $new_validity[2];
        }
    }

    public function validate_date_of_birth(string $date_of_birth): bool|string
    {
        // Validate date of birth
        $new_date_of_birth = date("Y-m-d", strtotime($date_of_birth));
        $new_date_of_birth = explode("-", $new_date_of_birth);

        if ($new_date_of_birth[0] > date('Y') - 16) {
            return false;
        } else {
            return $new_date_of_birth[0] . "-" . $new_date_of_birth[1] . "-" . $new_date_of_birth[2];
        }
    }

    public function check_passwords(string $password, string $password_conf): string
    {
        // Compare the two strings
        if ($password == $password_conf) {
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);
            //$specialChars = preg_match('@[^\w]@', $password);

            if ($uppercase && $lowercase && $number && strlen($password) >= 8) {
                return $password;
            } else {
                return "password_not_meeting_requirements";
            }

        } else {
            // If password isn't the same as the confirmation, delete the array and print error
            return "passwords_do_not_match";
        }
    }

    public function hash_password(string $password): bool|string|null
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}