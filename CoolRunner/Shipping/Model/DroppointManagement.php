<?php
/**
 *  DroppointManagement
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    12.08.2020
 * Time:    11:11
 */
namespace CoolRunner\Shipping\Model;

use CoolRunner\Shipping\Api\DroppointManagementInterface;
use CoolRunner\Shipping\Api\Data\DroppointInterface;
use CoolRunner\Shipping\Api\Data\DroppointInterfaceFactory;
use CoolRunner\Shipping\Helper\CurlData;

/**
 * Class DroppointManagement
 *
 * @package CoolRunner\Shipping
 */
class DroppointManagement implements DroppointManagementInterface{
    /**
     * @var DroppointInterfaceFactory
     */
    protected $_droppointFactory;
    /**
     * @var CurlData
     */
    protected $_helper;

    /**
     * DroppointManagement constructor.
     *
     * @param DroppointInterfaceFactory $droppointFactory
     * @param CurlData                  $helper
     */
    public function __construct(DroppointInterfaceFactory $droppointFactory, CurlData $helper) {
        $this->_droppointFactory = $droppointFactory;
        $this->_helper = $helper;
    }

    /**
     * @param string $carrier
     * @param string $countryCode
     * @param string $postCode
     * @param string $city
     * @param string $street
     *
     * @return DroppointInterface[]|void
     */
    public function fetchDroppoints($carrier, $countryCode, $postCode, $city, $street) {
        $result      = [];
        $carrier     = str_replace(CurlData:: COOLRUNNER_SERVICE_PREFIX,'',$carrier);
        $countryCode = trim($countryCode);
        $street      = str_replace(' ', '+', $street);
        $postCode    = trim($postCode);
        $city        = trim($city);

        /** @var array $response */
        $response = $this->_helper->findClosestDroppoints($carrier,$countryCode,$street,$postCode,$city);
        if(isset($response['servicepoints'])){
            foreach ($response['servicepoints'] as $_servicepoint) {
                if(isset($_servicepoint['id'])){
                    /** @var DroppointInterface $droppoint */
                    $droppoint = $this->_droppointFactory->create();
                    $droppoint->setId($_servicepoint['id'])
                            ->setName($_servicepoint['name'])
                            ->setDistance($_servicepoint['distance'])
                            ->setCoordinates($_servicepoint['coordinates'])
                            ->setAddress($_servicepoint['address'])
                            ->setOpeningHours($_servicepoint['opening_hours'])
                    ;
                    $result[] = $droppoint;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $carrier
     * @param string $droppointId
     *
     * @return DroppointInterface|void
     */
    public function fetchDroppointById($carrier, $droppointId) {
        $carrier     = str_replace(CurlData:: COOLRUNNER_SERVICE_PREFIX,'',$carrier);
        $droppointId = trim($droppointId);

        /** @var array $response */
        $response = $this->_helper->findDroppointById($carrier,$droppointId);
        /** @var DroppointInterface $droppoint */
        $droppoint = $this->_droppointFactory->create();

        if(isset($response['id'])){
            $droppoint->setId($response['id'])
                ->setName($response['name'])
                ->setDistance('')
                ->setCoordinates($response['coordinates'])
                ->setAddress($response['address'])
                ->setOpeningHours($response['opening_hours'])
            ;
            return $droppoint;
        }
        return $droppoint;
    }

}
