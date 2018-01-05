<?php
/**
 * Created by PhpStorm.
 * User: Wei Hong
 * Date: 5/1/2018
 * Time: 8:28 PM
 */
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
use IS203\login\LoginBase;
use IS203\topk\popularplaces\PopularPlacesBase;

//Object-Mapping
$jsonResponseArr = array();
//Pure array
$jsonErrors = array();

//Checks token first
if(!isset($_POST['token'])) {
    $jsonErrors[] = "missing token";
    onError();
}

$token = $_POST['token'];
if(empty($token)) {
    $jsonErrors[] = 'blank token';
    onError();
} else {
    $status = LoginBase::validateToken($token, 'ABCDEFGH12345678');
    if($status === 0 || $status === -1 || strcmp($status, 'admin') !== 0) {
        $jsonErrors[] = 'invalid token';
        onError();
    }
}

//Checks k
$validNonTokenVars = true;
$k = 0;
if(!isset($_REQUEST['k']) || empty($_REQUEST['k'])) {
    $k = 3;
} else {
    $k = $_REQUEST['k'];
    if(is_numeric($k)) {
        $k = (int) $k;
        if($k < 1 || $k > 10) {
            $jsonErrors[] = 'invalid k';
            $validNonTokenVars = false;
        }
    } else {
        $jsonErrors[] = 'invalid k';
        $validNonTokenVars = false;
    }
}

//Checks date
$dateTime = null;
if(!isset($_REQUEST['date'])) {
    $jsonErrors[] = 'missing date';
    $validNonTokenVars = false;
    onError();
} else {
    $date = $_REQUEST['date'];
    if(empty($date)) {
        $jsonErrors[] = 'blank date';
        $validNonTokenVars = false;
        onError();
    }

    $datePattern = '%[0-9]\d{1,4}-(0[0-9]|1[0-2])-[0-3][0-9]T([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]%';
    if(!preg_match($datePattern, $date)) {
        $jsonErrors[] = 'invalid date';
        $validNonTokenVars = false;
        onError();
    } else {
        try {
            $dateTime = new DateTime($date);
        } catch (Exception $ex) {
            $jsonErrors[] = 'invalid date';
            $validNonTokenVars = false;
            onError();
        }
    }
}

if(!$validNonTokenVars) {
    onError();
}

$popularPlacesBase = new PopularPlacesBase();
$results = $popularPlacesBase->getPopularPlaces($dateTime);

$rank = 1;
$resultsArr = array();
foreach ($results as $result => $places) {
    ksort($places);
    foreach ($places as $place) {
        $resultsArr[] = array(
            'rank' => $rank,
            'semantic-place' => $place,
            'count' => $result
        );
    }

    if(++$rank > $k) {
        break;
    }
}

$jsonResponseArr['status'] = 'success';
$jsonResponseArr['results'] = $resultsArr;
printJSON();

function onError() {
    global $jsonResponseArr, $jsonErrors;

    $jsonResponseArr['status'] = 'error';
    $jsonResponseArr['errors'] = $jsonErrors;
    printJSON();
}

function printJSON() {
    header('Content-type: text/json');

    global $jsonResponseArr;
    print json_encode($jsonResponseArr, JSON_PRETTY_PRINT);
    die();
}
