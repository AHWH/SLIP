<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 17/12/2017
 * Time: 5:59 PM
 */
require_once 'AdminBase.php';
require_once '../data/model/FileRowError.php';

/*Retrieved files from form*/
$fileName = $_FILES['file']['name'];
$fileType = $_FILES['file']['type'];
$fileSize = $_FILES['file']['size'];
$fileTmpName = $_FILES['file']['tmp_name'];
echo "{$fileName} is a {$fileSize} bytes {$fileType} file stored as {$fileTmpName}.";

/*Folder Management*/
$tempFolder = sys_get_temp_dir() . '\upload';
if(!is_dir($tempFolder)) {
    //Checks for existence of upload folder in temp folder. Creates one if it does not exists
    mkdir($tempFolder);
    //Todo: Delete the upload folder later
}

/*Zip Management*/
$zip = new ZipArchive;
$zipSuccess = $zip->open($fileTmpName);
if($zipSuccess === TRUE) {
    //If a proper zip file, extract to temp upload folder
    $zip->extractTo($tempFolder);
    $zip->close();
    echo "It works";
} else {
    echo "Something went wrong! Error code: {$zipSuccess}";
}


$userFile = $tempFolder . '\demographics.csv';
$locationFile = $tempFolder . '\location-lookup.csv';
$locationHistoryFile = $tempFolder . '\location.csv';

$processType = $_POST['submit'];
if($processType === 'Bootstrap') {
    echo "<br//> Initiating end of world Destruction";
    $adminBase = new AdminBase();
    $adminBase->bootstrap();

    $locationArr = array();
    if(is_file($locationFile)) {
        $adminBase->insertLocation($locationFile, $locationArr);
        unlink($locationFile);
    }

    if(is_file($userFile)) {
        $adminBase->insertUsers($userFile);
        unlink($userFile);
    }

    if(is_file($locationHistoryFile)) {
        $errors = $adminBase->insertLocationHistory($locationHistoryFile, $locationArr, $processType);
        unlink($locationHistoryFile);

        foreach ($errors as $error) {
            print '<br/>' . $error->getLineNo() . ': ' . $error->getErrors();
        }
    }

    rmdir($tempFolder);
} else {
    echo "<br//> Nothing happen";
    $adminBase = new AdminBase();
    if(is_file($userFile)) {
        $adminBase->insertUsers($userFile);
        unlink($userFile);
    }

    if(is_file($locationHistoryFile)) {
        $errors = $adminBase->insertLocationHistory($locationHistoryFile, NULL, $processType);
        unlink($locationHistoryFile);

        foreach ($errors as $error) {
            print '<br/>' . $error->getLineNo() . ': ' . $error->getErrors();
        }
    }

    rmdir($tempFolder);
}

?>