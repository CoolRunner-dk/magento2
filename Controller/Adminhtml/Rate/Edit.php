<?php
 /**
 * Edit
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    15:56
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use CoolRunner\Shipping\Model\RateRepository;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class Edit extends BackendAppAction implements HttpGetActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var RateRepository
     */
    protected $_repository;

    /**
     * @param RateRepository                             $repository
     * @param BackendAppAction\Context                   $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        RateRepository $repository,
        BackendAppAction\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_repository = $repository;
        parent::__construct($context);
    }


    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction() {

		// load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
         $resultPage->setActiveMenu('CoolRunner_Shipping::rates')
            ->addBreadcrumb(__('CoolRunner'), __('CoolRunner'))
            ->addBreadcrumb(__('MANAGE Rate'), __('MANAGE Rate'));
        return $resultPage;
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
		/** @var \CoolRunner\Shipping\Model\Rate $model */
        $model = $this->_repository->getModelObject();

        // 2. Initial checking
        if ($id) {
            $model = $this->_repository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Rate no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        //$this->_dataPersistor->set('coolrunner_shipping_rate',$model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Rate') : __('New Rate'),
            $id ? __('Edit Rate') : __('New Rate')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rate'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Edit Rate: %1 - %2',$model->getCarrier(),$model->getTitle())   : __('New Rate'));

        return $resultPage;
    }
}
