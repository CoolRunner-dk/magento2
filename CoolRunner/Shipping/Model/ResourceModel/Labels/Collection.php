<?php
namespace CoolRunner\Shipping\Model\ResourceModel\Labels;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'post_id';
    protected $_eventPrefix = 'coolrunner_shipping_labels_collection';
    protected $_eventObject = 'labels_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CoolRunner\Shipping\Model\Labels', 'CoolRunner\Shipping\Model\ResourceModel\Labels');
    }

}
