<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 17/12/2017
 * Time: 4:57 PM
 */
require_once '../data/DatabaseConnector.php';

$username = $_POST['username'];
$password = $_POST['password'];

if($username == 'admin') {
    session_start();
    $_SESSION['username'] = $username;
    header('Location: ../../dashboard.php');
    die();
} else {
    echo 'You are not supposed to be here';
}
?>