<?php
 /**
 * Delete
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    15:56
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use CoolRunner\Shipping\Model\RateRepository;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
/**
 * Class Delete
 *
 * @package CoolRunner\Shipping
 */
class Delete extends BackendAppAction implements HttpPostActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';
    /**
     * @var RateRepository
     */
    protected $_repository;

    /**
     * Delete constructor.
     *
     * @param BackendAppAction\Context $context
     * @param RateRepository           $repository
     */
    public function __construct(BackendAppAction\Context $context, RateRepository $repository) {
        parent::__construct($context);
        $this->_repository = $repository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
				/** @var \CoolRunner\Shipping\Model\Rate $model */
                $this->_repository->deleteById($id);

                // display success message
                $this->messageManager->addSuccessMessage(__('The Rate has been deleted.'));

                // go to grid
                $this->_eventManager->dispatch('adminhtml_coolrunner_shipping_rate_on_delete', [
                    'id' => $id,
                    'status' => 'success'
                ]);
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_coolrunner_shipping_rate_on_delete',
                    ['id' => $id, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Rate to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
