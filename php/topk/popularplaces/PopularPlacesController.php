<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 24/12/2017
 * Time: 10:08 PM
 */
require_once 'PopularPlacesBase.php';

$dateTime = NULL;

try {
    $dateTime = new DateTime($_POST['dateTime']);
} catch (Exception $ex) {
    onError('Error parsing DateTime');
}

$popularPlacesBase = new PopularPlacesBase();
$results = $popularPlacesBase->getPopularPlaces($dateTime);

$_SESSION['results'] = $results;
$_SESSION['searchTime'] = $dateTime;
$_SESSION['k'] = $_POST['k'];
$_SESSION['processed'] = TRUE;
header('Location: ../../../topk/popularplace.php');
die();

function onError($msg) {
    $_SESSION['error'] = $msg;
    $_SESSION['processed'] = TRUE;
    header('Location: ../../../topk/popularplace.php');
    die();
}

?>