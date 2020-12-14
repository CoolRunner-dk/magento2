<?php
/**
 *  Service
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    16:33
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class Service
 *
 * @package CoolRunner\Shipping
 */
class Service implements OptionSourceInterface{
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
     * @return array
     */
    public function toOptionArray(): array {
        $options = [];
        $services = explode(',',$this->_helper->getConfigValue('cr_settings/carriers/services'));
        foreach ($services as $value) {
            $options[] = ['value' => $value, 'label' => __(ucfirst($value))];
        }
        return $options;
    }
}
