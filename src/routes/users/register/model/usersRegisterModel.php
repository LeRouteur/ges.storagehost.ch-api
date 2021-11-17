<?php

/**
 * This file contains the required functions to insert valid form data in the database.
 * @author Cyril Buchs
 * @version 1.7
 */

namespace Users;

use PDO;
use PDOException;

include __DIR__ . "/../../../../config/Config.php";

class usersRegisterModel
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
        $email = $this->form_data['email'];
        try {
            $req = $this->pdo->prepare('SELECT email FROM ges_storagehost_ch.users WHERE email = :email');
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
    public function create_user()
    {
        try {
            $req = $this->pdo->prepare('INSERT INTO ges_storagehost_ch.users(last_name, first_name, email, password, role, password_reset_token) VALUES (:last_name, :first_name, :email, :password, :role, :password_reset_token)');
            $req->execute(
                array(
                    ':last_name' => $this->form_data['last_name'],
                    ':first_name' => $this->form_data['first_name'],
                    ':email' => $this->form_data['email'],
                    ':password' => $this->form_data['password'],
                    ':role' => 0,

                    // Set a default password reset token at 0, which means no token was issued
                    ':password_reset_token' => 0
                )
            );
            if ($req) {
                $payload = [];
                array_push($payload, array(
                    "status" => "success",
                    "data" => array(
                        'user_id' => $this->pdo->lastInsertId(),
                        'last_name' => $this->form_data['last_name'],
                        'first_name' => $this->form_data['first_name'],
                        'email' => $this->form_data['email'],
                        'role' => 0
                    )
                ));

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