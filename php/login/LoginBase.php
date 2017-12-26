<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 26/12/2017
 * Time: 8:15 PM
 */
require_once '../data/DatabaseConnector.php';
include '../log4php/Logger.php';
use Lcobucci\JWT\Builder;

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

    public function generateToken($username) : string
    {
        //Please sign with a proper key during actual deployment!
        $token = (new Builder())->setIssuer('SE') // Configures the issuer (iss claim)
        ->setAudience('SLIP') // Configures the audience (aud claim)
        ->setId($username, true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
        ->setNotBefore(time() + 300) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + 3600) // Configures the expiration time of the token (exp claim)
        ->set('uid', 1) // Configures a new claim, called "uid"
        ->getToken(); // Retrieves the generated token

        return $token;
    }
}