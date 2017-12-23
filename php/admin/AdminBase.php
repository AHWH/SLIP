<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 20/12/2017
 * Time: 9:58 PM
 */
require_once '../data/DatabaseConnector.php';
require_once '../util/Validator.php';
require_once '../data/model/FileRowError.php';
require_once '../data/model/LocationHistory.php';

include('../log4php/Logger.php');

class AdminBase
{
    private $logger;

    /**
     * AdminBase constructor.
     */
    public function __construct()
    {
        Logger::configure('../log4php/config/config.xml');
        $this->logger = Logger::getLogger('main');
    }


    /**
     * Wipes the entire database
     * @return bool returns TRUE if successfully completed the Bootstrap process. Else returns false
     */
    public function bootstrap() : bool {
        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();
        $dbConnector->beginTransaction();

        try {
            $sql = 'SET FOREIGN_KEY_CHECKS = 0';
            $stmt = $dbConnector->prepare($sql);
            if(!$stmt->execute()) {
                $dbConnector->rollBack();
                return false;
            }

            $sql = 'TRUNCATE TABLE location_history';
            $stmt = $dbConnector->prepare($sql);
            if(!$stmt->execute()) {
                $dbConnector->rollBack();
                return false;
            }

            $sql = 'TRUNCATE TABLE location';
            $stmt = $dbConnector->prepare($sql);
            if(!$stmt->execute()) {
                $dbConnector->rollBack();
                return false;
            }

            $sql = 'TRUNCATE TABLE user';
            $stmt = $dbConnector->prepare($sql);
            if(!$stmt->execute()) {
                $dbConnector->rollBack();
                return false;
            }

            $sql = 'SET FOREIGN_KEY_CHECKS = 1';
            $stmt = $dbConnector->prepare($sql);
            if(!$stmt->execute()) {
                $dbConnector->rollBack();
                return false;
            }

            $dbConnector->commit();
            return true;
        } catch (PDOException $pdoEx) {
            $this->logger->error("SQL Error", $pdoEx);
            return false;
        }
    }


    /**
     * Attempt to validate and insert validated Location-lookup.csv data into database
     * @param $file - Location-lookup.csv file path
     * @param $locationArr - Array to hold all the locationIDs for later use with Location.csv
     * @return array - Returns all the errors from the validations. Returns null when failed to insert data to database
     */
    public function insertLocation($file, &$locationArr) : array {
        $fileStream = fopen($file, 'r');
        ini_set('auto_detect_line_endings', TRUE);
        //Skip the header
        fgetcsv($fileStream);
        //Gets the parent folder of passed in $file
        $verifiedFile = dirname($file) . '\LocationVerified.csv';
        $fileErrors = array();
        $count = 1;
        while(($data = fgetcsv($fileStream)) !== FALSE) {
            $count++;
            $errors = Validator::validateLocation($data);
            if(empty($errors)) {
                //No error write to verfied file
                $locationArr[] = $data[0];
                $newFile = fopen($verifiedFile, 'a');
                fputcsv($newFile, $data);
                fclose($newFile);
            } else {
                $fileErrors[] = new FileRowError($count, $errors);
            }
        }
        fclose($fileStream);
        $_SESSION['location-lookup.csv'] = $count - 1;

        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();
        $sql = 'LOAD DATA LOCAL INFILE ? INTO TABLE location FIELDS TERMINATED BY \',\' LINES TERMINATED BY \'\n\'';


        try {
            $stmt = $dbConnector->prepare($sql);
            $stmt->bindParam(1, $verifiedFile);
            $stmt->execute();
        } catch (PDOException $pdoEx) {
            $this->logger->error('Error loading data from local file. ', $pdoEx);
            $fileErrors = NULL;
        }

        //Delete verified file upon completion of uploading
        unlink($verifiedFile);

        return $fileErrors;
    }


    /**
     * Attempt to validate and insert validated Demographics.csv data into database
     * @param $file - Demographics.csv file path
     * @return array - returns all the errors from the validations. Returns null when failed to insert data to database
     */
    public function insertUsers($file) : array {
        $fileStream = fopen($file, 'r');
        ini_set('auto_detect_line_endings', TRUE);
        //Skips the header
        fgetcsv($fileStream);
        //Gets the parent folder of passed in $file
        $verifiedFile = dirname($file) . '\UserVerified.csv';
        $fileErrors = array();
        $count = 1;
        while(($data = fgetcsv($fileStream)) !== FALSE) {
            $count++;
            $errors = Validator::validateUser($data);

            if(empty($errors)) {
                //No error write to verified file
                $newFile = fopen($verifiedFile, 'a');
                fwrite($newFile, implode(',', $data) . "\n");
                fclose($newFile);
            } else {
                $fileErrors[] = new FileRowError($count, $errors);
            }
        }
        fclose($fileStream);
        $_SESSION['demographics.csv'] = $count - 1;

        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();
        $sql = 'LOAD DATA LOCAL INFILE ? INTO TABLE user FIELDS TERMINATED BY \',\' LINES TERMINATED BY \'\n\'';

        try {
            $stmt = $dbConnector->prepare($sql);
            $stmt->bindParam(1, $verifiedFile);
            $stmt->execute();
        } catch (PDOException $pdoEx) {
            $this->logger->error('Error loading data from local file. ', $pdoEx);
            $fileErrors = NULL;
        }


        //Delete verified file upon completion of uploading
        unlink($verifiedFile);

        return $fileErrors;
    }


    /**
     * Attempt to validate and insert validated Location.csv data into database. If user choose upload, locationIDs and existing location histories will be retrieved from database
     * @param $file - Location.csv file path
     * @param $locationArr - Validated LocationIDs from insertLocation(). Valid only for Bootstrap
     * @param $type - The type of process user selected. Bootstrap/Upload
     * @return array - returns all the errors from the validations. Returns null when failed to insert data to database or retrieved existing data (for upload only)
     */
    public function insertLocationHistory($file, $locationArr, $type) : array {
        $existingLocationHistories = NULL;
        if($type === 'Upload') {
            $locationArr = $this->getLocationIDsFromDatabase();
            if(empty($locationArr)) {
                return NULL;
            } else {
                $existingLocationHistories = $this->getExistingLocationHistories();
            }
            if(empty($existingLocationHistories)) {
                return NULL;
            }
        }

        $fileStream = fopen($file, 'r');
        ini_set('auto_detect_line_endings', TRUE);
        //Skips the header
        fgetcsv($fileStream);
        $fileErrors = array();
        $count = 1;
        $currentLocationHistories = array();
        while(($data = fgetcsv($fileStream)) !== FALSE) {
            $count++;
            $errors = Validator::validateLocationHistory($data);
            $key = $data[0] . ',' . $data[1];
            if(empty($errors)) {
                //Checks if location ID is in database
                if(in_array($data[2], $locationArr)) {
                    //Valid locationID
                    if($type == 'Upload') {
                        if(in_array($key, $existingLocationHistories)) {
                            //If user choose upload, check against existing location history
                            //If its a duplicate, discard the one in the uploaded file
                            $fileErrors[] = new FileRowError($count, 'duplicate row', $count);
                            continue;
                        }
                    }

                    if(in_array($key, $currentLocationHistories)) {
                        $locationHistory = $currentLocationHistories[$key];
                        $fileErrors[] = new FileRowError($count, 'duplicate row', $locationHistory->getRow());
                        $locationHistory->setRow($count);
                        $locationHistory->setLocationID($data[2]);
                    } else {
                        $currentLocationHistories[$key] = new LocationHistory($data[0], $data[1], $data[2], $count);
                    }
                } else {
                    $fileErrors[] = new FileRowError($count, 'invalid location id', $data[2]);
                }
            } else {
                $fileErrors[] = new FileRowError($count, $errors);
            }
        }
        fclose($fileStream);

        //Gets the parent folder of passed in $file
        $verifiedFile = dirname($file) . '\LocHistVerified.csv';
        //Writes to CSV file
        $count = 0;
        foreach ($currentLocationHistories as $locationHistory) {
            $newFile = fopen($verifiedFile, 'a');
            fwrite($newFile, $locationHistory->getTimeStamp() . ',' . $locationHistory->getMacAddress() . ',' . $locationHistory->getLocationID() . "\n");
            fclose($newFile);
            $count++;
        }
        $_SESSION['location.csv'] = $count;

        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();
        $sql = 'LOAD DATA LOCAL INFILE ? INTO TABLE location_history FIELDS TERMINATED BY \',\' LINES TERMINATED BY \'\n\'';

        try {
            $stmt = $dbConnector->prepare($sql);
            $stmt->bindParam(1, $verifiedFile);
            $stmt->execute();
        } catch (PDOException $pdoEx) {
            $this->logger->error('Error loading data from local file. ', $pdoEx);
            $fileErrors = NULL;
        }

        //Delete verified file upon completion of uploading
        unlink($verifiedFile);

        return $fileErrors;
    }

    private function getLocationIDsFromDatabase() : array {
        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();

        $sql = 'SELECT location_ID FROM location';
        $stmt = $dbConnector->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getExistingLocationHistories() : array {
        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();

        $sql = 'SELECT user_timestamp, mac_address FROM location_history';
        $stmt = $dbConnector->prepare($sql);
        $stmt->execute();
        $existingLocationHistories = array();
        while(($row = $stmt->fetch())) {
            $timestamp = $row[0];
            $key = substr($timestamp, 0, strlen($timestamp) - 2);
            $existingLocationHistories[] = $key;

        }

        return $existingLocationHistories;
    }
}