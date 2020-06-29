<?php
namespace CoolRunner\Shipping\Block\Adminhtml\Order\View;

class CustomOrderView extends \Magento\Backend\Block\Template
{
    public $labelsForOrder = "";

    public function getOrderLabelsFromDB($orderId)
    {
        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $this->labelsForOrder = $connection->fetchAll("SELECT * FROM coolrunner_shipping_labels WHERE ordernumber = '{$orderId}'");
    }

    public function getOrderLabels($orderId)
    {
        $this->getOrderLabelsFromDB($orderId);
        return $this->labelsForOrder;
    }
}
