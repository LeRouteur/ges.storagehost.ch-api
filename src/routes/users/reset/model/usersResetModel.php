<?php

/**
 * This file contains the required functions to insert valid form data in the database.
 * @author Cyril Buchs
 * @version 1.7
 */

namespace Users;

use PDO;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class usersResetModel
{
    private PDO $pdo;
    private string $token;
    private string $email;
    private string $password;
    private string $password_conf;

    public function __construct(PDO $pdo, string $token, string $email, string $password, string $password_conf)
    {
        $this->pdo = $pdo;
        $this->token = $token;
        $this->email = $email;
        $this->password = $password;
        $this->password_conf = $password_conf;
    }

    /**
     * Method to check if mail given by the user already exists in the database. If so, it will return an error message.
     * @return array|bool
     */
    public function check_email_existence(): bool|array
    {
        try {
            $req = $this->pdo->prepare('SELECT email FROM ges_storagehost_ch.users WHERE email = :email');
            $req->bindParam(':email', $this->email);
            $req->execute();

            // Check if request rows are higher than 0. If yes, it means that the email exists
            if ($req->rowCount() > 0) {
                return true;
            } else {
                // Email does not exist
                return false;
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

    /**
     * Method used to add a password reset token in the DB
     * @return array|bool
     */
    public function add_token(): bool|array
    {
        $reset_token = md5(microtime(TRUE) * 100000);
        try {
            $req = $this->pdo->prepare('UPDATE ges_storagehost_ch.users SET password_reset_token = :password_reset_token WHERE ges_storagehost_ch.users.email = :email');
            $req->execute(
                array(
                    ':password_reset_token' => $reset_token,
                    ':email' => $this->email
                )
            );
            if ($req) {
                $this->sendMail($reset_token);
                $payload = [];
                array_push($payload, array(
                    "status" => "success",
                    "data" => array(
                        'email' => $this->email,
                        'token' => $reset_token
                    )
                ));

                return $payload;
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'token_update_failed',
                    'date' => time()
                );
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

    public function verify_token()
    {
        try {
            $req = $this->pdo->prepare('SELECT ges_storagehost_ch.users.password_reset_token FROM ges_storagehost_ch.users WHERE ges_storagehost_ch.users.email = :email');
            $req->execute(
                array(
                    ':email' => $this->email
                )
            );

            if ($req->fetch()['password_reset_token'] == $this->token) {
                return "ok";
            } else {
                return "token_is_invalid";
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

    public function update_password(): string|array
    {
        // check the passwords
        $password = $this->check_password();

        if ($password == "password_not_meeting_requirements" || $password == "passwords_do_not_match") {
            return $password;
        } else {
            try {
                $req = $this->pdo->prepare('UPDATE ges_storagehost_ch.users SET ges_storagehost_ch.users.password = :password WHERE ges_storagehost_ch.users.email = :email');
                $req->execute(
                    array(
                        ':password' => password_hash($this->password, PASSWORD_DEFAULT),
                        ':email' => $this->email
                    )
                );
                if ($req) {
                    return "ok";
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

    private function check_password(): bool|string|null
    {
        // Compare the two strings
        if ($this->password == $this->password_conf) {
            $uppercase = preg_match('@[A-Z]@', $this->password);
            $lowercase = preg_match('@[a-z]@', $this->password);
            $number = preg_match('@[0-9]@', $this->password);
            //$specialChars = preg_match('@[^\w]@', $password);

            if ($uppercase && $lowercase && $number && strlen($this->password) >= 8) {

                // Add hashed password in the array
                return password_hash($this->password, PASSWORD_DEFAULT);
            } else {
                return "password_not_meeting_requirements";
            }

        } else {
            // If password isn't the same as the confirmation, delete the array and print error
            return "passwords_do_not_match";
        }
    }

    /**
     * Method that will send an email to user when the insert pass is successful.
     * @param string $token
     * @return array
     */
    private
    function sendMail(string $token)
    {
        // Set subject and body
        $subject = "STORAGEHOST - réinitialisation du mot de passe";
        $message = "Bonjour,<br/>
        Nous avons reçu une demande de réinitialisation de mot de passe sur le site Web de gestion des élèves de Christophe Buchs.<br/><br/>
        <b>Si vous n'êtes pas à l'origine de cette demande, merci de ne pas tenir compte de cet email.</b><br/><br/>
	    Pour modifier votre mot de passe, merci de bien vouloir cliquer sur ce lien ou de le copier/coller dans un navigateur afin de l'activer :
	    <br/><br/>
        " . SITE_URL . "reset_password.php?email=" . urlencode($this->email) . "&token=" . urlencode($token) . "
        <br/>
        <br/>       
        ---------------<br/>
        Cet e-mail est généré automatiquement, merci de ne pas y répondre.<br/>
        En cas de problème, merci de contacter l'administrateur en créant un ticket sur https://helpdesk.storagehost.ch.";

        // Create new PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Define server settings
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = EMAIL_SERVER;
            $mail->SMTPAuth = true;
            $mail->Username = EMAIL_ADDRESS;
            $mail->Password = EMAIL_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = EMAIL_SERVER_PORT;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Define sender and recipients settings
            $mail->setFrom(EMAIL_ADDRESS, 'STORAGEHOST - Hosting Services');
            $mail->addAddress($this->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

}