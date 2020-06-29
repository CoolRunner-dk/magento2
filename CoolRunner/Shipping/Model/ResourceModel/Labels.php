<?php
namespace CoolRunner\Shipping\Model\ResourceModel;

class Labels extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('coolrunner_shipping_labels', 'post_id');
    }
}
