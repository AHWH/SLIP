<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 29/12/2017
 * Time: 9:25 PM
 */
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
use IS203\login\LoginBase;

//Object-Mapping
$jsonResponseArr = array();
//Pure array
$jsonErrors = array();

//Checks if mandatory variables are passed in
if(!isset($_POST['username']) || !isset($_POST['password'])) {
    $jsonErrors[] = 'missing username/password';
    onError();
}

$username = $_POST['username'];
$password = $_POST['password'];

//Checks if mandatory variables are empty
if(empty($username) || empty($password)) {
    $jsonErrors[] = 'blank username/password';
    onError();
} else {
    $loginBase = new LoginBase();
    if($loginBase->checkAdmin($username, $password) || $loginBase->checkUser($username, $password)) {
        $jwt = $loginBase->generateToken($username, 'ABCDEFGH12345678');
        $jsonResponseArr['status'] = 'success';
        $jsonResponseArr['token'] = $jwt;

        printJSON();
    } else {
        $jsonErrors[] = 'invalid username/password';
        onError();
    }
}


function onError() {
    global $jsonResponseArr, $jsonErrors;

    $jsonResponseArr ['status'] = 'error';
    $jsonResponseArr ['errors'] = $jsonErrors;
    printJSON();
}

function printJSON() {
    header('Content-type: text/json');

    global $jsonResponseArr;
    print json_encode($jsonResponseArr, JSON_PRETTY_PRINT);
    die();
}
?>