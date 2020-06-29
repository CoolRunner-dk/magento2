<?php
namespace CoolRunner\Shipping\Model;

class Labels extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'coolrunner_shipping_labels';

    protected $_cacheTag = 'coolrunner_shipping_labels';

    protected $_eventPrefix = 'coolrunner_shipping_labels';

    protected function _construct()
    {
        $this->_init('CoolRunner\Shipping\Model\ResourceModel\Labels');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
