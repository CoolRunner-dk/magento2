<?php
 /**
 * SalesConvertOrderToQuote
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    14.08.2020
 * Time:    15:26
 */
namespace CoolRunner\Shipping\Observer;
use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface as OrderInterface;

/**
 * Class SalesConvertOrderToQuote
 *
 * @package CoolRunner\Shipping
 */
class SalesConvertOrderToQuote implements ObserverInterface {
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * SalesConvertOrderToQuote constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper){
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** Save shipping_coolrunner_pickup_id to quote shipping address*/
        if($this->_helper->isShippingMethodCoolRunnerDroppoint($order->getShippingMethod())){
            $shippingCoolRunnerPickupId = $order->getShippingAddress()->getShippingCoolrunnerPickupId();
            $quote->getShippingAddress()->setShippingCoolrunnerPickupId($shippingCoolRunnerPickupId);
        }
    }
}
