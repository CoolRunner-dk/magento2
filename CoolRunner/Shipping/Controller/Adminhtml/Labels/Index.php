<?php
/**
 * Index
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    05.08.2020
 * Time:    10:07
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Labels;

use Magento\Backend\App\Action;
/**
 * Class Index
 *
 * @package CoolRunner\Shipping
 */
class Index extends Action {

    /**
     * @var bool|\Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Index constructor.
     *
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CoolRunner_Shipping::labels');
        $resultPage->addBreadcrumb(__('CoolRunner'), __('CoolRunner'));
        $resultPage->addBreadcrumb(__('Pakkelabels'), __('Pakkelabels'));
        $resultPage->getConfig()->getTitle()->prepend((__('CoolRunner').' - '.__('Pakkelabels')));

        return $resultPage;
    }
}
