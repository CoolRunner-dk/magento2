<?php

namespace CoolRunner\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_CRSETTINGS = 'cr_settings/';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCredentialsConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'credentials/' . $code, $storeId);
    }

    public function getActivationConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'activation/' . $code, $storeId);
    }
}
