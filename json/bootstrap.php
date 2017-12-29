<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 29/12/2017
 * Time: 10:50 PM
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
if(!isset($_FILES['file'])) {
    $jsonErrors[] = 'missing bootstrap file';
    onError();
} else {
    //Wiki states to assume the file given is never empty and always valid
    $fileTmpName = $_FILES['file']['tmp_name'];
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
    $locationFile = $tempFolder . '\location-lookup.csv';
    $locationHistoryFile = $tempFolder . '\location.csv';

    //Bootstrap process
    if(!$adminBase->bootstrap()) {
        $jsonErrors[] = 'Bootstrap process error. Having difficulty with wiping database';
    }

    session_start();

    $userErrors = $adminBase->insertUsers($userFile);
    if($userErrors === NULL) {
        $jsonErrors[] = 'Bootstrap process error. Having difficulty uploading Demographics data to database';
    } else {
        $rowsInserted['demographics.csv'] = $_SESSION['demographics.csv'];
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


    $locationArr = array();
    $locationErrors = $adminBase->insertLocation($locationFile, $locationArr);
    if($locationErrors === NULL) {
        $jsonErrors = array();
        $jsonErrors[] = 'Bootstrap process error. Having difficulty uploading Location data to database';
        partialComplete();
    } else {
        $rowsInserted['location-lookup.csv'] = $_SESSION['location-lookup.csv'];
        foreach ($locationErrors as $locationError) {
            $errorsArr = array();
            foreach ($locationError as $reason => $cause) {
                $errorsArr[] = $reason;
            }

            $jsonErrors[] = array(
                'file' => 'location-lookup.csv',
                'line' => $locationError->getLineNo(),
                'messages' => $errorsArr
            );
        }
    }

    unlink($locationFile);


    $locHistErrors = $adminBase->insertLocationHistory($locationHistoryFile, $locationArr, 'Bootstrap');
    if($locHistErrors === NULL) {
        $jsonErrors = array();
        $jsonErrors[] = 'Bootstrap process error. Having difficulty uploading Location Histories data to database';
        partialComplete();
    } else {
        $_SESSION['locHistErrors'] = $locHistErrors;
        $rowsInserted['location.csv'] = $_SESSION['location.csv'];
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
}



function onError() {
    $jsonResponseArr['status'] = 'error';
    $this->jsonResponseArr['errors'] = $this->jsonErrors;
    printJSON();
}

function partialComplete() {
    $this->jsonResponseArr['status'] = 'error';
    $this->jsonResponseArr['num-record-loaded'] = $this->rowsInserted;
    $this->jsonResponseArr['errors'] = $this->jsonErrors;
    printJSON();
}

function printJSON() {
    header('Content-type: text/json');
    print json_encode($this->jsonResponseArr, JSON_PRETTY_PRINT);
    die();
}
?>