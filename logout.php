<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 23/12/2017
 * Time: 10:55 PM
 */
session_start();
session_unset();
session_destroy();
header('Location: index.php');
die();
?>