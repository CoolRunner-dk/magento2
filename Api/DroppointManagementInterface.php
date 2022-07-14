<?php
/**
 *
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    12.08.2020
 * Time:    10:49
 */
namespace CoolRunner\Shipping\Api;
/**
 * Interface DroppointManagementInterface
 *
 * @package CoolRunner\Shipping
 */
interface DroppointManagementInterface {

    /**
     * Find droppoints
     *
     * @param string $carrier
     * @param string $countryCode
     * @param string $postCode
     * @param string $city
     * @param string $street
     *
     * @return \CoolRunner\Shipping\Api\Data\DroppointInterface[]
     */
    public function fetchDroppoints($carrier, $countryCode, $postCode, $city, $street);

    /**
     * @param string $carrier
     * @param string $droppointId
     *
     * @return \CoolRunner\Shipping\Api\Data\DroppointInterface
     */
    public function fetchDroppointById($carrier,$droppointId);
}
