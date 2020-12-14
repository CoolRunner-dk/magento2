<?php
/**
 *  CalculatorFactoryPlugin
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    27.08.2020
 * Time:    22:09
 */
namespace CoolRunner\Shipping\Plugin\SalesRule\Model\Rule\Action\Discount;

use CoolRunner\Shipping\Model\Carrier\AbstractCoolRunnerOnline as CarrierModel;
use CoolRunner\Shipping\Model\Model\Rule\Action\Discount\FreeshippingRates as RuleActionDiscountModel;
/**
 * Class CalculatorFactoryPlugin
 *
 * @package CoolRunner\Shipping
 */
class CalculatorFactoryPlugin {
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * CalculatorFactoryPlugin constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager) {

        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
     * @param callable                                                        $proceed
     * @param                                                                 $type
     *
     * @return RuleActionDiscountModel|mixed
     */
    public function aroundCreate(\Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject, callable $proceed, $type) {

        if($type == CarrierModel::FREE_SHIPPING_RATES) {
            $result = $this->_objectManager->create(RuleActionDiscountModel::class);
        } else {
            $result = $proceed($type);
        }

        return $result;
    }
}
