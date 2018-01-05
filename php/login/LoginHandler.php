<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 17/12/2017
 * Time: 4:57 PM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use IS203\data\DatabaseConnector;
use IS203\login\LoginBase;

$username = $_POST['username'];
$password = $_POST['password'];

$loginBase = new LoginBase();
session_start();
if($loginBase->checkAdmin($username, $password)) {
    $_SESSION['username'] = $username;
    $_SESSION['isAdmin'] = TRUE;

    header('Location: ../../dashboard.php');
} else if($loginBase->checkUser($username, $password)) {
    $_SESSION['username'] = $username;
    $_SESSION['isAdmin'] = FALSE;

    header('Location: ../../dashboard.php');
} else  {
    $_SESSION['invalidLogin'] = TRUE;

    header('Location: ../../index.php');
}
die();
?>