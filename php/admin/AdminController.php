<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 17/12/2017
 * Time: 5:59 PM
 */
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
use IS203\admin\AdminBase;

/*Retrieved files from form*/
$fileName = $_FILES['file']['name'];
$fileType = $_FILES['file']['type'];
$fileSize = $_FILES['file']['size'];
$fileTmpName = $_FILES['file']['tmp_name'];

session_start();
$adminBase = new AdminBase();

/*Folder Management*/
$tempFolder = sys_get_temp_dir() . '\upload';
if(!$adminBase->createUploadFolder($tempFolder)) {
    onError('Failed to create upload folder');
}


/*Zip Management*/
if(($status = $adminBase->unzipFile($fileTmpName, $tempFolder)) !== TRUE) {
    onError("Failed to open zip file. ZipArchive's Error Code: {$status}");
}


$userFile = $tempFolder . '\demographics.csv';
$locationFile = $tempFolder . '\location-lookup.csv';
$locationHistoryFile = $tempFolder . '\location.csv';


/*Based on type chosen, process the files*/
$processType = $_POST['submit'];
if($processType === 'Bootstrap') {
    if(!$adminBase->bootstrap()) {
        onError('Bootstrap process error. Having difficulty with wiping database');
    }

    $locationArr = array();
    if(is_file($locationFile)) {
        $locationErrors = $adminBase->insertLocation($locationFile, $locationArr);
        if($locationErrors === NULL) {
            onError('Bootstrap process error. Having difficulty uploading Location data to database');
        } else {
            $_SESSION['locationErrors'] = $locationErrors;
        }

        unlink($locationFile);
    }


    if(is_file($userFile)) {
        $userErrors = $adminBase->insertUsers($userFile);
        if($userErrors === NULL) {
            onError('Bootstrap process error. Having difficulty uploading Demographics data to database');
        } else {
            $_SESSION['userErrors'] = $userErrors;
        }

        unlink($userFile);
    }

    if(is_file($locationHistoryFile)) {
        $locHistErrors = $adminBase->insertLocationHistory($locationHistoryFile, $locationArr, $processType);
        if($locHistErrors === NULL) {
            onError('Bootstrap process error. Having difficulty uploading Location Histories data to database');
        } else {
            $_SESSION['locHistErrors'] = $locHistErrors;
        }
        unlink($locationHistoryFile);
    }
} else {
    if(is_file($userFile)) {
        $userErrors = $adminBase->insertUsers($userFile);
        if($userErrors === NULL) {
            onError('Upload process error. Having difficulty uploading Demographics data to database');
        } else {
            $_SESSION['userErrors'] = $userErrors;
        }

        unlink($userFile);
    }

    if(is_file($locationHistoryFile)) {
        $locHistErrors = $adminBase->insertLocationHistory($locationHistoryFile, NULL, $processType);
        if($locHistErrors === NULL) {
            onError('Upload process error. Having difficulty uploading Location Histories data to database');
        } else {
            $_SESSION['locHistErrors'] = $locHistErrors;
        }
        unlink($locationHistoryFile);
    }
}

if(!rmdir($tempFolder)) {
    //Wipes the whole temp folder first if $tempfolder for some reason is not empty
    array_map('unlink', glob($tempFolder . '/*'));
    rmdir($tempFolder);
}

$_SESSION['processAdmin'] = TRUE;
header('Location: ../../admin.php');
die();

function onError($msg) {
    $_SESSION['error'] = $msg;
    $_SESSION['processAdmin'] = TRUE;
    header('Location: ../../admin.php');
    die();
}
?>