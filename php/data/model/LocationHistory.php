<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 20/12/2017
 * Time: 11:55 PM
 */
namespace IS203\data\model;

class LocationHistory
{
    private $timestamp;
    private $macAddress;
    private $locationID;

    private $row;

    /**
     * LocationHistory constructor.
     * @param $timestamp
     * @param $macAddress
     * @param $locationID
     * @param $row
     */
    public function __construct($timestamp, $macAddress, $locationID, $row)
    {
        $this->timestamp = $timestamp;
        $this->macAddress = $macAddress;
        $this->locationID = $locationID;
        $this->row = $row;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getMacAddress()
    {
        return $this->macAddress;
    }

    /**
     * @param mixed $macAddress
     */
    public function setMacAddress($macAddress): void
    {
        $this->macAddress = $macAddress;
    }

    /**
     * @return mixed
     */
    public function getLocationID()
    {
        return $this->locationID;
    }

    /**
     * @param mixed $locationID
     */
    public function setLocationID($locationID): void
    {
        $this->locationID = $locationID;
    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param mixed $row
     */
    public function setRow($row): void
    {
        $this->row = $row;
    }




}