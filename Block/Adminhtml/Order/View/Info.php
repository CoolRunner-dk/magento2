<?php
namespace CoolRunner\Shipping\Block\Adminhtml\Order\View;

use CoolRunner\Shipping\Api\DroppointManagementInterface;
use CoolRunner\Shipping\Api\Data\DroppointInterface;
use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;
use CoolRunner\Shipping\Model\ResourceModel\Labels\CollectionFactory as LabelCollectionFactory;
use CoolRunner\Shipping\Model\ResourceModel\Labels\Collection as LabelCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

/**
 * Class CustomOrderView
 *
 * @package CoolRunner\Shipping
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder {
    /**
     * @var CoolRunnerHelper
     */
    protected $_helper;
    /**
     * @var LabelCollectionFactory
     */
    protected $_labelCollectionFactory;
    /**
     * @var DroppointManagementInterface
     */
    protected $_droppointManagement;

    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Sales\Helper\Admin             $adminHelper
     * @param LabelCollectionFactory                  $labelCollectionFactory
     * @param DroppointManagementInterface            $droppointManagement
     * @param CoolRunnerHelper                        $helper
     * @param array                                   $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                   \Magento\Framework\Registry $registry,
                                   \Magento\Sales\Helper\Admin $adminHelper,
                                   LabelCollectionFactory $labelCollectionFactory,
                                   DroppointManagementInterface $droppointManagement,
                                   CoolRunnerHelper $helper,
                                   array $data = []) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->_helper = $helper;
        $this->_labelCollectionFactory = $labelCollectionFactory;
        $this->_droppointManagement = $droppointManagement;
    }

    /**
     * @return LabelCollection
     * @throws LocalizedException
     */
    public function getOrderLabels() {

        /** @var LabelCollection $labelsCollection */
        $labelsCollection = $this->_labelCollectionFactory->create();
        $labelsCollection->addFilterByOrderId($this->getOrder()->getId());
        return $labelsCollection;
    }

    /**
     * @return bool
     */
    public function isOrderCoolRunner(){
        try {
            return $this->_helper->isOrderCoolRunner($this->getOrder());
        }
        catch (LocalizedException $e){
            return false;
        }
    }

    /**
     * @return string
     */
    public function _toHtml() {
        if($this->isOrderCoolRunner()){
            return parent::_toHtml();
        }
        return  '';
    }

    /**
     * @param string $code
     *
     * @return array
     * @throws LocalizedException
     */
    public function getShippingMethodDetails($code = ''){
        return $this->_helper->explodeShippingMethod($this->getOrder()->getShippingMethod(),$code);
    }

    /**
     * @param $trackNumber
     *
     * @return string
     */
    public function getTrackingUrl($trackNumber){
        return $this->_helper->getTrackingUrl($trackNumber);
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isDropppoint(){
        return $this->_helper->isShippingMethodCoolRunnerDroppoint($this->getOrder()->getShippingMethod());
    }

    /**
     * @return DroppointInterface
     * @throws LocalizedException
     */
    public function getDroppoint(){
        $droppointId = $this->getOrder()->getShippingAddress()->getShippingCoolrunnerPickupId();
        if($droppointId > 0){
            /** @var DroppointInterface $droppoint */
            $droppoint = $this->_droppointManagement->fetchDroppointById($this->getShippingMethodDetails('carrier'),$droppointId);
            return $droppoint;
        }
    }
}
