<?php
/**
 *  Carrier
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    14:28
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class Carrier
 *
 * @package CoolRunner\Shipping
 */
class Carrier implements OptionSourceInterface {
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Carrier constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper) {
        $this->_helper = $helper;
    }

    /**
     * @param bool $addCarrierPrefix
     * @param null $store
     *
     * @return array
     */
    public function toArray($addCarrierPrefix = false, $store = null){
        $carriers = [];
        $config = $this->_helper->getConfigValue('carriers', $store);
        foreach (array_keys($config) as $carrierCode) {
            if ($carrierCode && $this->_helper->isShippingMethodCoolRunner($carrierCode)) {
                $carriers[$addCarrierPrefix?$this->_helper->getCarrierWithPrefix($carrierCode):$this->_helper->getCarrierWithoutPrefix($carrierCode)] = $this->_helper->getConfigValue('carriers/'.$carrierCode.'/title', $store);;
            }
        }
        return $carriers;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array {
        $options = [];
        foreach ($this->toArray() as $index => $value) {
            $options[] = ['value' => $index, 'label' => __($value)];
        }
        return $options;
    }
}
