<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 29/12/2017
 * Time: 11:49 PM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
use IS203\admin\AdminBase;
use IS203\login\LoginBase;
use IS203\data\model\FileRowError;

//Object-Mapping
$jsonResponseArr = array();
//Pure array
$jsonErrors = array();
//Object-Map array
$rowsInserted = array();

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


//Checks file variable
if(!isset($_FILES['upload-file'])) {
    $jsonErrors[] = 'missing upload file';
    onError();
} else {
    //Wiki states to assume the file given is never empty and always valid
    $fileTmpName = $_FILES['upload-file']['tmp_name'];
    $adminBase = new AdminBase();

    /*Folder Management*/
    $tempFolder = sys_get_temp_dir() . '\upload';
    if(!$adminBase->createUploadFolder($tempFolder)) {
        $jsonErrors[] = 'failed to create upload folder';
        onError();
    }

    //Zip file management
    if(($status = $adminBase->unzipFile($fileTmpName, $tempFolder)) !== TRUE) {
        $jsonErrors[] = "Failed to open zip file. ZipArchive's Error Code: {$status}";
        onError();
    }

    $userFile = $tempFolder . '\demographics.csv';
    $locationHistoryFile = $tempFolder . '\location.csv';

    //Upload process
    session_start();

    $userErrors = $adminBase->insertUsers($userFile);
    if($userErrors === NULL) {
        $jsonErrors[] = 'Bootstrap process error. Having difficulty uploading Demographics data to database';
    } else {
        $rowsInsertedInnerArr = array();
        $rowsInsertedInnerArr['demographics.csv'] = $_SESSION['demographics.csv'];
        $rowsInserted[] = $rowsInsertedInnerArr;
        foreach ($userErrors as $userError) {
            $errorsArr = array();
            foreach ($userError as $reason => $cause) {
                $errorsArr[] = $reason;
            }

            $jsonErrors[] = array(
                'file' => 'demographics.csv',
                'line' => $userError->getLineNo(),
                'messages' => $errorsArr
            );
        }
    }

    unlink($userFile);


    $locHistErrors = $adminBase->insertLocationHistory($locationHistoryFile, NULL, 'Upload');
    if($locHistErrors === NULL) {
        $jsonErrors = array();
        $jsonErrors[] = 'Bootstrap process error. Having difficulty uploading Location Histories data to database';
        partialComplete();
    } else {
        $rowsInsertedInnerArr = array();
        $rowsInsertedInnerArr['location.csv'] = $_SESSION['location.csv'];
        $rowsInserted[] = $rowsInsertedInnerArr;
        foreach ($locHistErrors as $locHistError) {
            $errorsArr = array();
            foreach ($locHistError as $reason => $cause) {
                $errorsArr[] = $reason;
            }

            $jsonErrors[] = array(
                'file' => 'location-lookup.csv',
                'line' => $locHistError->getLineNo(),
                'messages' => $errorsArr
            );
        }
    }

    unlink($locationHistoryFile);


    if(empty($jsonErrors)) {
        $jsonResponseArr['status'] = 'success';
    } else {
        $jsonResponseArr['status'] = 'error';
    }
    $jsonResponseArr['num-record-loaded'] = $rowsInserted;
    $jsonResponseArr['errors'] = $jsonErrors;
    printJSON();

    rmdir($tempFolder);
}



function onError() {
    global $jsonResponseArr, $jsonErrors;

    $jsonResponseArr['status'] = 'error';
    $jsonResponseArr['errors'] = $jsonErrors;
    printJSON();
}

function partialComplete() {
    global $jsonResponseArr, $jsonErrors, $rowsInserted;

    $jsonResponseArr['status'] = 'error';
    $jsonResponseArr['num-record-loaded'] = $rowsInserted;
    $jsonResponseArr['errors'] = $jsonErrors;
    printJSON();
}

function printJSON() {
    header('Content-type: text/json');

    global $jsonResponseArr;
    print json_encode($jsonResponseArr, JSON_PRETTY_PRINT);
    die();
}
?>