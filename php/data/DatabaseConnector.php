<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 16/12/2017
 * Time: 11:19 PM
 */

class DatabaseConnector {
    private static $instance = NULL;
    private $pdo;

    private function __construct($settingsFile = 'database_settings.ini') {
        //Read from a ini file
        $settings = parse_ini_file($settingsFile, true);

        //If the ini file is valid, proceed to create the PDO
        if($settings) {
            $dsn = $settings['database']['driver'] . ':host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['schema'];
            $this->pdo = new PDO($dsn, $settings['database']['user'], $settings['database']['password'], array(PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE));
        }
    }

    public static function getInstance() {
        if(self::$instance == NULL) {
            self::$instance = new DatabaseConnector;
        }

        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
?>