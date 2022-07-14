<?php
/**
 * Created by CoolRunner.
 * Developer: Kevin Hansen
 */

namespace CoolRunner\Shipping\Controller\Adminhtml\System\Config;

use CoolRunner\Shipping\Helper\CurlData;
use CoolRunner\Shipping\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class SmartCheckout extends Action
{
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     * @param CurlData $curlData
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $installData = [
            'install_token' => $this->getRequest()->getParam('installToken'),
            'install_storename' => $this->getRequest()->getParam('storeName'),
            'install_storeurl' => $this->getRequest()->getParam('storeUrl')
        ];
        $installResponse = \Magento\Framework\App\ObjectManager::getInstance()->get(\CoolRunner\Shipping\Helper\CurlData::class)->getSmartcheckoutCredentials($installData);

        $resultData = ['success' => true, 'install_data' => $installData, 'install_repsonse' => $installResponse];

        return $result->setData($resultData);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CoolRunner_Shipping::config');
    }
}
