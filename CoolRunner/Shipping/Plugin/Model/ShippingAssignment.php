<?php
 /**
 * ShippingAssignment
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    13.08.2020
 * Time:    17:48
 */
namespace CoolRunner\Shipping\Plugin\Model;

use CoolRunner\Shipping\Helper\Data as Helper;

/**
 * Class ShippingAssignment
 *
 * @package CoolRunner\Shipping
 */
class ShippingAssignment {
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * ShippingAssignment constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper) {
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAssignment   $subject
     * @param \Magento\Quote\Api\Data\ShippingInterface $value
     *
     * @return array
     */
	public function beforeSetShipping(\Magento\Quote\Model\ShippingAssignment $subject, \Magento\Quote\Api\Data\ShippingInterface $value) {

	    $method = $value->getMethod();
        /** @var AddressInterface $address */
        $address = $value->getAddress();

        if ($this->_helper->isShippingMethodCoolRunnerDroppoint($method)
            && $address->getExtensionAttributes()
            && $address->getExtensionAttributes()->getShippingCoolrunnerPickupId()
        ) {
            $address->setShippingCoolrunnerPickupId($address->getExtensionAttributes()->getShippingCoolrunnerPickupId());
        }
        /** important! set null only if there is method, but not coolrunner */
        if(!empty($method) && !$this->_helper->isShippingMethodCoolRunnerDroppoint($method)) {
            $address->setShippingCoolrunnerPickupId(null);
        }
        return [$value];
	}
}
