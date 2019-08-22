<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
$app = new \Slim\App([
    'settings' =>[
        'displayErrorDetails'=>true
    ]
]);

/*
    endpoint: createuser
    parameters: email, password, name, school
    method: POST
 */

 //USER CREATED
$app->post('/createuser',function(Request $request, Response $response, $argc){
    if(!haveEmptyParameters(array('email','password','name','school'), $response)){
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $password = $request_data['password'];
        $name = $request_data['name'];
        $school = $request_data['school'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createUser($email, $hash_password, $name, $school);

        if($result == USER_CREATE){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Kullanıcı oluşturma başarılı!';

            $response->write(json_encode($message));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(201);

        }else if($result == USER_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred / Bazı Hatalar Oluştu.';

            $response->write(json_encode($message));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(422);
        }else if($result == USER_EXISTS){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists / Kullanıcı zaten var.';

            $response->write(json_encode($message));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-Type','application/json')
        ->withStatus(422);
});

//USER LOGIN
$app->post('/userlogin' , function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email','password'), $response)){
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->userLogin($email, $password);
        if($result == USER_AUTHENTICATED){

            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Giriş Başarılı. ! / Login Succsessful';
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(200);

        }else if($result == USER_NOT_FOUND){

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Kullanıcı Bulunamadı / Users not exist';

            $response->write(json_encode($response_data));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(200);

        }else if($result == USER_PASSWORD_DO_NOT_MATCH){

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Şifre Yanlış / Wrong Password';

            $response->write(json_encode($response_data));
            return $response
                    ->withHeader('Content-Type','application/json')
                    ->withStatus(404);
        }
    }else{

    }
    return $response
        ->withHeader('Content-Type','application/json')
        ->withStatus(422);

});

$app->get('/allusers', function(Request $request, Response $response){

    $db = new DbOperations;

    $users = $db->getAllUsers();

    $response_data = array();

    $response_data['error'] = false;
    $response_data['users']=$users;

    $response->write(json_encode($response_data));

    return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);

});

function haveEmptyParameters($required_params,$response){
    $error=false;
    $error_params='';
    $request_params=$_REQUEST;

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param]) <=0){
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . 'are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;

}



$app->run();