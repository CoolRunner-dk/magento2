<?php

namespace CoolRunner\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_CRSETTINGS = 'cr_settings/';
    const XML_PATH_SHOPINFO = 'general/';
    protected $_objectManager;

    public function __construct(Context $context)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getStoreInformation($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SHOPINFO . 'store_information/' . $code, $storeId);
    }

    public function getCredentialsConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'credentials/' . $code, $storeId);
    }

    public function getAgreementConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'agreement/' . $code, $storeId);
    }

    public function getActivationConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'activation/' . $code, $storeId);
    }

    public function createShipment($shipmentData, $agreementType, $orderId, $makeShipment)
    {
        $coolRunner = $this->_objectManager->get('CoolRunner\Shipping\Controller\Adminhtml\Curl\CoolRunner');
        $coolRunner->createShipment($shipmentData, $agreementType, $orderId, $makeShipment);
    }

    public function getShippingLabels($orderId)
    {
        $coolRunner = $this->_objectManager->get('CoolRunner\Shipping\Controller\Adminhtml\Curl\CoolRunner');
        return $coolRunner->getShippingLabels($orderId);
    }

    public function findClosestDroppoints($carrier, $countryCode, $street, $zipCode, $city)
    {
        $coolRunner = $this->_objectManager->get('CoolRunner\Shipping\Controller\Adminhtml\Curl\CoolRunner');
        return $coolRunner->getNearestDroppoints($carrier, $countryCode, $street, $zipCode, $city);
    }
}
