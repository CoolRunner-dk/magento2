<?php
namespace CoolRunner\Shipping\Model\ResourceModel\Labels;

/**
 * Class Collection
 *
 * @package CoolRunner\Shipping
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'coolrunner_shipping_labels_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'labels_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('CoolRunner\Shipping\Model\Labels', 'CoolRunner\Shipping\Model\ResourceModel\Labels');
    }

    /**
     * @param int $orderId
     *
     * @return Collection
     */
    public function addFilterByOrderId($orderId){
        return $this->addFieldToFilter('order_id',array('eq' => $orderId));
    }

    /**
     * @return Collection
     */
    public function addFilterPrintLabels(){
//        return $this->addFieldToFilter('unique_id',array('eq' => ''));
        return $this->addFieldToFilter('unique_id',array('null' => true));
    }

}
