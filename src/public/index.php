<?php

require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../config/SQLConnection.php";

/**
 * Users
 */
require __DIR__ . "/../routes/users/register/usersRegister.php";
require __DIR__ . "/../routes/users/login/usersLogin.php";
require __DIR__ . "/../routes/users/reset/usersReset.php";
//require __DIR__ . "/../routes/users/password/Password.php";

/**
 * Students
 */
require __DIR__ . "/../routes/students/register/studentsRegister.php";
require __DIR__ . "/../routes/students/display/studentsDisplay.php";
require __DIR__ . "/../routes/students/modify/studentsModify.php";
require __DIR__ . "/../routes/students/delete/studentsDelete.php";

/**
 * Invoices
 */
require __DIR__ . "/../routes/invoices/create/invoicesCreate.php";

/**
 * Auth
 */
require __DIR__ . "/../config/Auth.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Container;
use Students\studentsDelete;
use Students\studentsDisplay;
use Students\studentsModify;
use Students\studentsRegister;
use Users\Auth;
use Users\usersLogin;
use Users\usersReset;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new Container($configuration);

$app = new App($c);

$container = $app->getContainer();

$container['pdo'] = function () {
    return (new SQLConnection())->connect();
};

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * AUTHENTICATED ACTIONS
 * ---------------------------------------------------------------------------------------------------------------------
 */

/**
 * -----------------------------------------------------------------------
 * STUDENTS SECTION
 * -----------------------------------------------------------------------
 */

// Register a student
$app->post('/api/students', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $body = $request->getParsedBody();

        if (isset($body) && !empty($body)) {
            $result = (new studentsRegister((array)$body, $this->pdo))->get_form_data();
            if (is_array($result)) {
                return $response->withStatus(201)->withJson($result);
            } else {
                return $response->withStatus(400)->withJson(array(
                    'status' => 'error',
                    'message' => $result,
                    'date' => time()
                ));
            }
        } else {
            return $response->withStatus(400)->withJson(array(
                'status' => 'error',
                'message' => 'missing_body',
                'date' => time()
            ));
        }
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Display all students
$app->get('/api/students', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        return $response->withStatus(200)->withJson((new studentsDisplay($this->pdo))->display_students());
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Display info of a specific student
$app->get('/api/student/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $id = $args['id'];
        echo $id;
        return $response->withStatus(200)->withJson((new studentsDisplay($this->pdo))->display_student($id));
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Modify info of a specific student
$app->put('/api/student/{id}', function (Request $request, Response $response, $args) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
        $headers = getallheaders();
        $auth = new Auth($this->pdo, $headers);
        $body = $request->getParsedBody();

        if ($auth->isAuth()) {
            if (isset($args['id'])) {
                if (!empty($body)) {
                    $result = (new studentsModify($body, $args['id'], $this->pdo))->modify_student();
                    if (is_array($result)) {
                        return $response->withStatus(200)->withJson($result);
                    } else {
                        return $response->withStatus(400)->withJson(array(
                            'status' => 'error',
                            'message' => $result,
                            'date' => time()));
                    }
                } else {
                    return $response->withStatus(400)->withJson(array(
                        'status' => 'error',
                        'message' => 'missing_body',
                        'date' => time()
                    ));
                }
            } else {
                return $response->withStatus(400)->withJson(array(
                    'status' => 'error',
                    'message' => 'missing_required_parameter_id',
                    'date' => time()
                ));
            }
        } else {
            return $response->withStatus(401)->withJson(array(
                'status' => 'error',
                'message' => 'unauthorized',
                'date' => time()
            ));
        }
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_required_header_content_type',
            'date' => time()
        ));
    }
});

// Delete a student
$app->delete('/api/student/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        if (isset($args['id'])) {
            $result = (new studentsDelete($args['id'], $this->pdo))->delete_student();
            if (is_array($result)) {
                return $result;
            } elseif ($result) {
                return $response->withStatus(204);
            } else {
                return $response->withStatus(404)->withJson(array(
                    'status' => 'error',
                    'message' => 'student_does_not_exist',
                    'date' => time()
                ));
            }
        } else {
            return $response->withStatus(400)->withJson(array(
                'status' => 'error',
                'message' => 'missing_required_parameter_id',
                'date' => time()
            ));
        }
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

/**
 * -----------------------------------------------------------------------
 * INVOICES SECTION
 * -----------------------------------------------------------------------
 */

// Create an invoice
$app->post('/api/invoices', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $user_data = $auth->isAuth();
        $vms = (new Order(array(), $user_data, $this->pdo))->getOrders();
        return $response->withStatus(200)->withJson($vms);
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Get an invoice by its ID
$app->get('/api/invoices', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $user_data = $auth->isAuth();
        $body = $request->getParsedBody();

        if (isset($body) && !empty($body)) {
            $result = (new Order((array)$body, (array)$user_data, $this->pdo))->validateData();
            if (is_array($result)) {
                return $response->withStatus(201)->withJson($result);
            } else {
                return $response->withStatus(400)->withJson(array(
                    'status' => 'error',
                    'message' => $result,
                    'date' => time()
                ));
            }
        } else {
            return $response->withStatus(400)->withJson(array(
                'status' => 'error',
                'message' => 'missing_body',
                'date' => time()
            ));
        }
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

/**
 * -----------------------------------------------------------------------
 * USERS SECTION
 * -----------------------------------------------------------------------
 */

// Login the user
$app->post('/api/users/login', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (isset($body) && !empty($body)) {
        $result = (new usersLogin((array)$body, $this->pdo))->get_form_data();

        if (is_array($result) && in_array('success', $result)) {
            return $response->withStatus(200)->withJson($result);
        } else {
            return $response->withStatus(400)->withJson($result);
        }
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_body',
            'date' => time()
        ));
    }
});

// Register a new user
$app->post('/api/users', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    if (isset($body) && !empty($body)) {
        $result = (new Users\usersRegister((array)$body, $this->pdo))->get_form_data();
        if (is_array($result)) {
            return $response->withStatus(201)->withJson($result);
        } else {
            return $response->withStatus(400)->withJson(array(
                'status' => 'error',
                'message' => $result,
                'date' => time()
            ));
        }
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_body',
            'date' => time()
        ));
    }
});

// Send email to user
$app->post('/api/users/password', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (isset($body) && isset($body['email']) && !empty($body)) {
        $result = (new usersReset($this->pdo, "", $body['email'], "", ""))->send_email();

        if ($result) {
            return $response->withStatus(204);
        } else {
            return $response->withStatus(400)->withJson(
                array(
                    'status' => 'error',
                    'message' => "bad_request",
                    'date' => time()
                ));
        }
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_body',
            'date' => time()
        ));
    }
});

// Update user password
$app->post('/api/users/password/reset', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (isset($body) && isset($body['email']) && isset($body['token']) && isset($body['password']) && isset($body['password_conf']) && !empty($body)) {
        $result = (new usersReset($this->pdo, $body['token'], $body['email'], $body['password'], $body['password_conf']))->update_user();

        var_dump($result);

        if ($result == "ok") {
            return $response->withStatus(204);
        } else {
            return $response->withStatus(400)->withJson(
                array(
                    'status' => 'error',
                    'message' => $result,
                    'date' => time()
                ));
        }
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_body',
            'date' => time()
        ));
    }
});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 */

try {
    $app->run();
} catch (Throwable $e) {
    echo "Cannot run the app! " . $e->getMessage();
}