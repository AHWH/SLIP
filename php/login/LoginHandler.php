<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 17/12/2017
 * Time: 4:57 PM
 */
require_once '../data/DatabaseConnector.php';
require_once 'LoginBase.php';

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