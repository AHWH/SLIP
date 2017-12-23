<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 23/12/2017
 * Time: 11:07 PM
 */
session_start();
if(isset($_SESSION['username'])) {
    header('Location: dashboard.php');
} else {
    header('Location: index.html');
}
die();