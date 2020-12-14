<?php
/**
 *
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    12.08.2020
 * Time:    10:54
 */
namespace CoolRunner\Shipping\Api\Data;
/**
 * Interface DroppointInterface
 *
 * @method $this setId(int $id)
 * @method $this setName(string $name)
 * @method $this setDistance(int $distance)
 * @method $this setAddress(array $address)
 * @method $this setCoordinates(array $coordinates)
 * @method $this setOpeningHours(array $hours)
 * @method int getDistance()
 * @method array getAddress() Associative array with keys "street", "zip_code", "city", "country_code"
 *
 * @package CoolRunner\Shipping
 */
interface DroppointInterface {

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * Get concatenated string of location Name + Street + Post Code + City (Distance)
     *
     * @return string
     */
    public function getLocation();

    /**
     * Get concatenated string of address Street + Post Code + City (Distance)
     *
     * @return string
     */
    public function getAddressLocation();

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @return string
     */
    public function getZipCode();

    /**
     * @return string
     */
    public function getCity();
    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @return float
     */
    public function getLatitude();

    /**
     * @return float
     */
    public function getLongitude();

    /**
     * Associative array with keys "latitude","longitude"
     *
     * @return array
     */
    public function getCoordinates();

    /**
     * Associative array with keys "monday","tuesday",... Sub level with keys "from","to"
     * @return array
     */
    public function getOpeningHours();

    /**
     * @return string
     */
    public function getFormattedOpeningHours();
}
