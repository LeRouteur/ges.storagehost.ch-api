<?php
/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.6
 */

namespace Users;

include __DIR__ . "/model/usersResetModel.php";

use PDO;

class usersReset
{
    private PDO $pdo;
    private string $token;
    private string $email;
    private string $password;
    private string $password_conf;

    /**
     * Password constructor.
     * @param PDO $pdo
     * @param string $token
     * @param string $email
     * @param string $password
     * @param string $password_conf
     */
    public function __construct(PDO $pdo, string $token, string $email, string $password, string $password_conf)
    {
        $this->pdo = $pdo;
        $this->token = $token;
        $this->email = $email;
        $this->password = $password;
        $this->password_conf = $password_conf;
    }

    public function send_email(): array|bool
    {
        $model = new usersResetModel($this->pdo, $this->token, $this->email, $this->password, $this->password_conf);
        if ($model->check_email_existence()) {
            return $model->add_token();
        } else {
            return false;
        }
    }

    public function update_user()
    {
        $model = new usersResetModel($this->pdo, $this->token, $this->email, $this->password, $this->password_conf);
        $result = $model->verify_token();

        if ($result == "ok") {
            return $model->update_password();
        } else {
            return $result;
        }
    }
}