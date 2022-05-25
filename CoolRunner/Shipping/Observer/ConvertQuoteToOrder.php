<?php
 /**
 * ConvertQuoteToOrder
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    13.08.2020
 * Time:    20:46
 */
namespace CoolRunner\Shipping\Observer;
use CoolRunner\Shipping\Api\DroppointManagementInterface;
use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface as OrderInterface;

/**
 * Class ConvertQuoteToOrder
 *
 * @package CoolRunner\Shipping
 */
class ConvertQuoteToOrder implements ObserverInterface {
    /**
     * @var Helper
     */
    protected $_helper;
    /**
     * @var DroppointManagementInterface
     */
    protected $_droppointManagement;

    /**
     * ConvertQuoteToOrder constructor.
     *
     * @param Helper                       $helper
     * @param DroppointManagementInterface $droppointManagement
     */
    public function __construct(Helper $helper, DroppointManagementInterface $droppointManagement){
        $this->_helper = $helper;
        $this->_droppointManagement = $droppointManagement;
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

        /** Save shipping_coolrunner_pickup_id to order shipping address*/
        $shippingCoolRunnerPickupId = $quote->getShippingAddress()->getShippingCoolrunnerPickupId();
        $order->getShippingAddress()->setShippingCoolrunnerPickupId($shippingCoolRunnerPickupId);

        if($this->_helper->isShippingMethodCoolRunnerDroppoint($order->getShippingMethod())){
            $droppoint = $this->_droppointManagement->fetchDroppointById($order->getShippingMethod(true)->getCarrierCode(),$shippingCoolRunnerPickupId);
            if($droppoint->getId()>0){
                $order->getShippingAddress()->setCompany($droppoint->getName());
                $order->getShippingAddress()->setStreet($droppoint->getStreet());
                $order->getShippingAddress()->setPostcode($droppoint->getZipCode());
                $order->getShippingAddress()->setCity($droppoint->getCity());
                $order->getShippingAddress()->setCountryId($droppoint->getCountryCode());
            }
        }
    }
}
