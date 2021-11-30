<?php

require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../config/SQLConnection.php";

/**
 * Users
 */
require __DIR__ . "/../routes/users/register/usersRegister.php";
require __DIR__ . "/../routes/users/login/usersLogin.php";
require __DIR__ . "/../routes/users/reset/usersReset.php";
require __DIR__ . "/../routes/users/display/usersDisplay.php";

/**
 * Students
 */
require __DIR__ . "/../routes/students/register/studentsRegister.php";
require __DIR__ . "/../routes/students/display/studentsDisplay.php";
require __DIR__ . "/../routes/students/modify/studentsModify.php";
require __DIR__ . "/../routes/students/delete/studentsDelete.php";
require __DIR__ . "/../routes/students/send/studentsSend.php";

/**
 * Invoices
 */
require __DIR__ . "/../routes/invoices/create/invoicesCreate.php";
require __DIR__ . "/../routes/invoices/display/invoicesDisplay.php";
require __DIR__ . "/../routes/invoices/modify/invoicesModify.php";
require __DIR__ . "/../routes/invoices/delete/invoicesDelete.php";
require __DIR__ . "/../routes/invoices/send/invoicesSend.php";

/**
 * Prices
 */
require __DIR__ . "/../routes/prices/display/pricesDisplay.php";
require __DIR__ . "/../routes/prices/modify/pricesModify.php";

/**
 * Auth
 */
require __DIR__ . "/../config/Auth.php";

use Invoices\invoicesCreate;
use Invoices\invoicesDelete;
use Invoices\invoicesDisplay;
use Invoices\invoicesModify;
use Invoices\invoicesSend;
use Invoices\studentsSend;
use Prices\pricesDisplay;
use Prices\pricesModify;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Container;
use Students\studentsDelete;
use Students\studentsDisplay;
use Students\studentsModify;
use Students\studentsRegister;
use Users\Auth;
use Users\usersDisplay;
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
 * PRICES SECTION
 * -----------------------------------------------------------------------
 */
// Get the prices
$app->get('/api/prices', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        return $response->withStatus(200)->withJson((new pricesDisplay($this->pdo))->get_prices());
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Update prices
$app->put('/api/prices', function (Request $request, Response $response) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
        $headers = getallheaders();
        $auth = new Auth($this->pdo, $headers);
        $body = $request->getParsedBody();

        if ($auth->isAuth()) {
            if (!empty($body)) {
                $result = (new pricesModify($this->pdo, $body))->update_prices();
                if ($result['status'] == 'success') {
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

/**
 * -----------------------------------------------------------------------
 * STUDENTS SECTION
 * -----------------------------------------------------------------------
 */

// Register a student
$app->post('/api/students', function (Request $request, Response $response) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
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
    } else {
        return $response->withStatus(400)->withJson(array(
            'status' => 'error',
            'message' => 'missing_required_header_content_type',
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
        return $response->withStatus(200)->withJson((new studentsDisplay($this->pdo))->display_student($id));
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Display lessons of a specific student
$app->get('/api/student/lessons/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $id = $args['id'];
        return $response->withStatus(200)->withJson((new studentsDisplay($this->pdo))->display_student_lessons($id));
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
            var_dump($result);
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

// Modify lessons by id
$app->put('/api/lesson/modify', function (Request $request, Response $response) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
        $headers = getallheaders();
        $auth = new Auth($this->pdo, $headers);
        $body = $request->getParsedBody();

        if ($auth->isAuth()) {
            if (!empty($body)) {
                $result = (new studentsModify($body, 0, $this->pdo))->modify_student_lessons_by_id();
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

// Add a new lesson to an user
$app->post('/api/lessons', function (Request $request, Response $response) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
        $headers = getallheaders();
        $auth = new Auth($this->pdo, $headers);
        $body = $request->getParsedBody();

        if ($auth->isAuth()) {
            if (!empty($body)) {
                $result = (new studentsModify($body, 0, $this->pdo))->add_new_lesson_to_student();
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

// Send detail sheet by email
$app->post('/api/lessons/send', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);
    $body = $request->getParsedBody();

    if ($auth->isAuth()) {
        if (!empty($body)) {
            $result = (new studentsSend())->send_email_with_pdf($body);
            if (is_array($result)) {
                return $response->withStatus(204);
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
        $body = $request->getParsedBody();
        if (isset($body) && !empty($body)) {
            $invoice = (new invoicesCreate($this->pdo, $body))->create_invoice();
            if ($invoice['status'] == 'error') {
                return $response->withStatus(400)->withJson($invoice);
            } else {
                return $response->withStatus(200)->withJson($invoice);
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

// Get all invoices
$app->get('/api/invoices', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $result = (new invoicesDisplay($this->pdo))->get_invoices();
        if (is_array($result) && $result['status'] == 'success') {
            return $response->withStatus(200)->withJson($result);
        } else {
            return $response->withStatus(404)->withJson($result);
        }
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
            'date' => time()
        ));
    }
});

// Get an invoice by its ID
$app->get('/api/invoice/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {

        if (!empty($args)) {
            $result = (new invoicesDisplay($this->pdo))->get_invoice_by_id((int)$args['id']);
            if (is_array($result) && $result['status'] == 'success') {
                return $response->withStatus(200)->withJson($result);
            } else {
                return $response->withStatus(404)->withJson($result);
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

// Modify info of a specific invoice
$app->put('/api/invoice/{id}', function (Request $request, Response $response, $args) {
    if ($request->getHeader('Content-Type')[0] == 'application/json') {
        $headers = getallheaders();
        $auth = new Auth($this->pdo, $headers);
        $body = $request->getParsedBody();

        if ($auth->isAuth()) {
            if (isset($args['id'])) {
                if (!empty($body)) {
                    $body['id'] = $args['id'];
                    $result = (new invoicesModify($this->pdo))->modify_invoice_by_id($body);
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

// Delete an invoice
$app->delete('/api/invoice/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        if (isset($args['id'])) {
            $result = (new invoicesDelete($args['id'], $this->pdo))->delete_invoice();
            if (is_array($result)) {
                return $result;
            } elseif ($result) {
                return $response->withStatus(204);
            } else {
                return $response->withStatus(404)->withJson(array(
                    'status' => 'error',
                    'message' => 'invoice_does_not_exist',
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

// Send invoice by email
$app->post('/api/invoice/send', function (Request $request, Response $response) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        $body = $request->getParsedBody();
        $result = (new invoicesSend())->send_email_with_pdf($body);

        if (is_array($result) && $result['status'] == 'success') {
            return $response->withStatus(204);
        } else {
            return $response->withStatus(404)->withJson(array(
                'status' => 'error',
                'message' => 'invoice_does_not_exist',
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

$app->get('/api/user/email/{id}', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        if (isset($args['id'])) {
            $result = (new usersDisplay($this->pdo))->get_user_email($args['id']);
            if (is_array($result)) {
                return $result;
            } elseif ($result) {
                return $response->withStatus(204);
            } else {
                return $response->withStatus(404)->withJson(array(
                    'status' => 'error',
                    'message' => 'invoice_does_not_exist',
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

// Get the basic infos of a user
$app->get('/api/user/me', function (Request $request, Response $response, $args) {
    $headers = getallheaders();
    $auth = new Auth($this->pdo, $headers);

    if ($auth->isAuth()) {
        return $response->withStatus(200)->withJson(array(
            'status' => 'success',
            'data' => $auth->isAuth()['data'],
            'date' => time()
        ));
    } else {
        return $response->withStatus(401)->withJson(array(
            'status' => 'error',
            'message' => 'unauthorized',
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