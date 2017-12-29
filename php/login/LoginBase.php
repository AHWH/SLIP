<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 26/12/2017
 * Time: 8:15 PM
 */
require_once '../data/DatabaseConnector.php';
include '../log4php/Logger.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class LoginBase
{
    private $settings;
    private $logger;

    /**
     * LoginBase constructor.
     * @param string $settingsFile
     */
    public function __construct($settingsFile = 'admin.ini')
    {
        $this->settings = parse_ini_file($settingsFile, true);

        $loggerConfig = $_SERVER['SERVER_NAME'] . 'php/log4php/config/config.xml';
        Logger::configure($loggerConfig);
        $this->logger = Logger::getLogger('main');
    }

    public function checkAdmin($username, $password) : bool
    {
        if(strcmp($this->settings['admin']['username'], $username) === 0 && strcmp($this->settings['admin']['password'], $password) === 0) {
            return true;
        }
        return false;
    }

    public function checkUser($username, $password) : bool
    {
        $databaseConnectorInstance = DatabaseConnector::getInstance();
        $databaseConnector = $databaseConnectorInstance->getConnection();

        $sql = 'SELECT email FROM user WHERE loginID LIKE ? AND user_password LIKE BINARY ?';

        try {
            $stmt = $databaseConnector->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $password);
            $stmt->execute();

            while(($row = $stmt->fetch())) {
                return true;
            }
        } catch (PDOException $pdoEx) {
            $this->logger->error('Having problem checking user in database.', $pdoEx);
            return false;
        }

        return false;
    }

    /**
     * @param string $username The login ID
     * @param string $sharedSecret The self-created shared secret
     * @return mixed Returns the generated JWT token with HS256 encryption. Returns false if exception is encountered during the generation
     */
    public function generateToken($username, $sharedSecret)
    {
        //Please sign with a proper key during actual deployment!
        $token = array(
            'sub' => $username,
            'iat' => microtime(true),
            'exp' => microtime(true) + 3600000
        );

        $jwt = null;
        try {
            $jwt = JWT::encode($token, $sharedSecret, 'HS256');
        } catch (Exception $ex) {
            $this->logger->error('Having problem generating token.', $ex);
            return false;
        }

        return $jwt;
    }

    /**
     * @param string $jwt The given JWT Spec's token
     * @param string $sharedSecret The shared secret porvided
     * @return mixed Returns the decoded username if no exceptions are thrown during the validation. Else returns -1 if token has expired or 0 if token is invalid for other reasons.
     */
    public function validateToken($jwt, $sharedSecret)
    {
        try {
            //JWT::decode will return the payload portion of the JWT Token as an Object. Casting it to array is necessary to access it
            $decoded = JWT::decode($jwt, $sharedSecret, array('HS256'));
            $decodedArr = (array) $decoded;

            return $decodedArr['sub'];
        } catch (ExpiredException $expEx) {
            return -1;
        } catch (Exception $ex) {
            $this->logger->error('Having problem validating token.', $ex);
            return 0;
        }

    }
}