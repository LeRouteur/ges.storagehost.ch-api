<?php

namespace Users;

require __DIR__."/model/usersLoginModel.php";

/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.6
 */

use PDO;

class usersLogin
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

        if (!empty($this->form_data) && !empty($this->form_data['email']) && !empty($this->form_data['password'])) {
            return ($this->validate_data());

        } else {
            return array(
                'status' => 'error',
                'message' => 'bad_post',
                'date' => time()
            );
        }

    }

    /**
     * Method used to validate the form data received through the Web interface.
     */
    private function validate_data(): array
    {
        // Validate email
        if (filter_var($this->form_data['email'], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL)) {
            $this->valid_form_data['email'] = $this->form_data['email'];

            // Add password
            $this->valid_form_data['password'] = $this->form_data['password'];

            return (new usersLoginModel($this->valid_form_data, $this->pdo))->authenticate_user();
        } else {
            return array(
                'status' => 'error',
                'message' => 'bad_email',
                'date' => time()
            );
        }
    }
}