<?php
namespace CoolRunner\Shipping\Model\ResourceModel;
/**
 * Class Labels
 *
 * @package CoolRunner\Shipping
 */
class Labels extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     *
     */
    protected function _construct() {
        $this->_init('coolrunner_shipping_labels', 'post_id');
    }
}
