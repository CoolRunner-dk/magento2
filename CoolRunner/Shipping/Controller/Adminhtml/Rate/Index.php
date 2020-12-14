<?php
 /**
 * Index
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:53
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
/**
 * Class Index
 *
 * @package CoolRunner\Shipping
 */
class Index extends Action implements HttpGetActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor = null
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor ?: ObjectManager::getInstance()->get(DataPersistorInterface::class);
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CoolRunner_Shipping::rates');
        $resultPage->addBreadcrumb(__('CoolRunner'), __('CoolRunner'));
        $resultPage->addBreadcrumb(__('Shipping Rates'), __('Shipping Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('CoolRunner').' - '.__('Shipping Rates'));

		//$this->dataPersistor->clear('YOURMODELPREFIX');
        return $resultPage;
    }
}
