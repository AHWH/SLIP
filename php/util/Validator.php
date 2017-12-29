<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 18/12/2017
 * Time: 5:17 PM
 */
namespace IS203\util;

class Validator
{
    static function validateLocation(&$data) : array {
        $errors = array();

        $data[0] = trim($data[0]);
        $locationID = $data[0];
        if(empty($locationID)) {
            $errors['blank location id'] = "";
        } else {
            if(is_numeric($locationID)) {
                if(intval($locationID, 10) < 0) {
                    echo "Hello";
                    $errors['invalid location id'] = "{$locationID} is negative";
                }
            } else {
                $errors['invalid location id'] = "{$locationID} contains non-numeric character(s)";
            }
        }

        $data[1] = trim($data[1]);
        $semanticPlace = $data[1];
        if(empty($semanticPlace)) {
            $errors['blank semantic place'] = "";
        } else {
            if(strpos($semanticPlace, 'SMUSISL') !== 0 & strpos($semanticPlace, 'SMUSISB') !== 0) {
                $errors['invalid semantic place'] = "{$semanticPlace} is not a valid SIS Building location";
                return $errors;
            }

            $semanticPlaceLvl = (int) substr($semanticPlace, 7,1 );
            if($semanticPlaceLvl < 0 | $semanticPlaceLvl > 5) {
                $errors['invalid semantic place'] = "{$semanticPlaceLvl} is not a valid level";
                return $errors;
            }

            $semanticPlaceName = substr($semanticPlace, 8);
            if(empty($semanticPlaceName)) {
                $errors['invalid semantic place'] = "{$semanticPlaceName} is not a valid place";
                return $errors;
            }
        }

        return $errors;
    }


    static function validateUser(&$data) : array {
        $errors = array();
        $data[0] = trim($data[0]);
        $macAddress = $data[0];
        if (empty($macAddress)) {
            $errors['blank mac-address'] = '';
        } else {
            if (($len = strlen($macAddress)) !== 40) {
                $errors['invalid mac address'] = "{$len} characters long";
            } else if(preg_match('%[^a-fA-f0-9]+%', $macAddress, $matches)){
                //Retrieving all the illegal characters via Regex
                $illegalChar = array();
                foreach ($matches as $match) {
                    $illegalChar[] = $match[0];
                }

                $errors['invalid mac-address'] = 'char ' . implode(',', $illegalChar) . ' inside';
            }
        }

        $data[1] = trim($data[1]);
        $name = $data[1];
        if (empty($name)) {
            $errors['blank name'] = '';
        }

        $data[2] = trim($data[2]);
        $password = $data[2];
        if (empty($password)) {
            $errors['blank password'] = '';
        } else {
            if (($len = strlen($password)) < 8) {
                $errors['invalid password'] = "{$len} characters only";
            } else if (strpos($password, " ")){
                $errors['invalid password'] = 'password contains space';
            }
        }

        $data[3] = trim($data[3]);
        $email = $data[3];
        if (empty($email)) {
            $errors['blank email'] = '';
        } else if(strpos($email, ' ')){
            $errors['invalid email'] = 'Email contains space';
        } else {
            $emailErrors = array();
            $emailArr = explode('@', $email);

            if (count($emailArr) !== 2) {
                $errors['invalid email'] = 'Email does not contains @';
            } else{
                $userPartOfEmail = $emailArr[0];
                $domainPartOfEmail = $emailArr[1];
                $indexOfUserDot = strripos($userPartOfEmail, '.');
                if (!$indexOfUserDot) {
                    $errors['invalid email'] = 'Missing year';
                } else {
                    $id = substr($userPartOfEmail,0, $indexOfUserDot);
                    $year = substr($userPartOfEmail, $indexOfUserDot + 1);
                    if(preg_match('%[^A-z0-9.]+%', $id, $matches)) {
                        $idErrors = array();
                        foreach ($matches as $match) {
                            $idErrors[] = $match;
                        }
                        $emailErrors[] = 'char ' . implode(', ', $idErrors) . ' inside';
                    }

                    $data[] = $userPartOfEmail;
                    $data[] = $year;

                    if(!is_numeric($year)) {
                        $emailErrors[] = "year ${year}";
                    } else {
                        $yearInt = (int) $year;
                        if($yearInt < 2013 || $yearInt > 2017) {
                            $emailErrors[] = "year ${year}";
                        }
                    }
                }

                $indexOfSchoolDot = strpos($domainPartOfEmail, '.');
                if (!$indexOfSchoolDot) {
                    $emailErrors[] = 'invalid domain';
                } else {
                    $school = substr($domainPartOfEmail, 0, $indexOfSchoolDot);
                    $domain = substr($domainPartOfEmail, $indexOfSchoolDot + 1, strlen($domainPartOfEmail));
                    $data[] = $school;
                    $schoolArr = array("business", "accountancy", "sis", "economics", "law", "socsc");
                    if (!in_array($school, $schoolArr)) {
                        $emailErrors[] = "invalid school: {$school}";
                    }

                    if (strcmp($domain, 'smu.edu.sg') !== 0) {
                        $emailErrors[] = "invalid domain: {$domain}";
                    }
                }
            }

            if(!empty($emailErrors)) {
                $errors['invalid email'] = implode(',', $emailErrors);
            }
        }

        $data[4] = trim($data[4]);
        $genderStr = $data[4];
        if (empty($genderStr)) {
            $errors['blank gender'] = "";
        } else {
            if (stripos($genderStr, 'm') === FALSE & stripos($genderStr, 'f') === FALSE) {
                $errors['invalid gender'] = "{$genderStr} is not a valid gender";
            }
        }
        return $errors;
    }

    static function validateLocationHistory(&$data) : array {
        $errors = array();

        $data[0] = trim($data[0]);
        $timestamp = $data[0];
        if (empty($timestamp)) {
            $errors['blank timestamp'] = "";
        } else {
            $pattern = '%[0-9]\d{1,4}-(0[0-9]|1[0-2])-[0-3][0-9] ([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]%';
            if(!preg_match($pattern, $timestamp)) {
                $errors['invalid timestamp'] = $timestamp;
            } else {
                try {
                    new DateTime($timestamp);
                } catch (Exception $ex) {
                    $errors['invalid timestamp'] = $timestamp;
                }
            }
        }

        $data[1] = trim($data[1]);
        $macAddress = $data[1];
        if (empty($macAddress)) {
            $errors['blank mac-address'] = '';
        } else {
            if (($len = strlen($macAddress)) !== 40) {
                $errors['invalid mac address'] = "{$len} characters long";
            } else if(preg_match('%[^a-fA-f0-9]+%', $macAddress, $matches)){
                //Retrieving all the illegal characters via Regex
                $illegalChar = array();
                foreach ($matches as $match) {
                    $illegalChar[] = $match[0];
                }

                $errors['invalid mac-address'] = 'char ' . implode(',', $illegalChar) . ' inside';
            }
        }

        $data[2] = trim($data[2]);
        $locationID = $data[2];
        if(empty($locationID)) {
            $errors['blank location id'] = "";
        } else {
            if(is_numeric($locationID)) {
                if(intval($locationID, 10) < 0) {
                    $errors['invalid location id'] = "{$locationID} is negative";
                }
            } else {
                $errors['invalid location id'] = "{$locationID} contains non-numeric character(s)";
            }
        }

        //Still left with validate locationID with location-lookup.csv and duplicate row(s)
        //Check by caller
        return $errors;
    }
}