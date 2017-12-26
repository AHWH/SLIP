<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 24/12/2017
 * Time: 10:58 PM
 */
require_once '../../data/DatabaseConnector.php';

include('../../log4php/Logger.php');

class PopularPlacesBase
{
    private $logger;

    /**
     * PopularPlacesBase constructor.
     */
    public function __construct()
    {
        Logger::configure('../log4php/config/config.xml');
        $this->logger = Logger::getLogger('main');
    }

    public function getPopularPlaces($searchDateTime) : array
    {
        $sql = 'SELECT semantic_place, COUNT(*) '
                . 'FROM (SELECT lt.mac_address AS mac_addr, MAX(user_timestamp) AS user_time '
                    . 'FROM location_history lt '
                    . 'WHERE user_timestamp BETWEEN ? AND ? '
                    . 'GROUP BY mac_address) as temp, location_history lh, location l '
                . 'WHERE temp.mac_addr = lh.mac_address '
                . 'AND temp.user_time = lh.user_timestamp '
                . 'AND lh.location_ID = l.location_ID '
                . 'GROUP BY semantic_place '
                . 'ORDER BY COUNT(*) desc';

        $dbConnectorInstance = DatabaseConnector::getInstance();
        $dbConnector = $dbConnectorInstance->getConnection();

        $results = NULL;
        try {
            $stmt = $dbConnector->prepare($sql);
            /**Time Manipulation
             * Note: PHP modifies them by reference! So they will always reference by memory.
             * Calling ->format after adjusting both the start and end time will make both the last modified time
             * */
            $startTime = $searchDateTime->sub(new DateInterval('PT15M'));
            $startTimeStr = $startTime->format('Y-m-d H:i:s');
            $endTime = $searchDateTime->add(new DateInterval('PT14M59S'));
            $endTimeStr = $endTime->format('Y-m-d H:i:s');

            $stmt->bindParam(1, $startTimeStr);
            $stmt->bindParam(2, $endTimeStr);
            $stmt->execute();

            $results = array();
            while(($row = $stmt->fetch())) {
                $semanticPlace = $row[0];
                $count = $row[1];

                $places = $results[$count] ?? array();
                $places[] = $semanticPlace;
                $results[$count] = $places;
            }

            krsort($results, SORT_NUMERIC);
        } catch (PDOException $pdoEx) {
            $this->logger->error('Error getting results. ', $pdoEx);
            $results = NULL;
        }

        return $results;
    }
}