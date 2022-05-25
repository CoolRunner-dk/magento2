<?php
 /**
 * Save
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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends BackendAppAction implements HttpPostActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var RateRepository
     */
    protected $_repository;
    /**
     * @param BackendAppAction\Context $context
     * @param DataPersistorInterface   $dataPersistor
     * @param RateRepository           $repository
     */
    public function __construct(
        BackendAppAction\Context $context,
        DataPersistorInterface $dataPersistor,
        RateRepository $repository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_repository = $repository;
        parent::__construct($context);
    }


    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /*
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = \CoolRunner\Shipping\Model\Rate::STATUS_ENABLED;
            }
			*/
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            $id = $this->getRequest()->getParam('entity_id');

            try {
                /** @var \CoolRunner\Shipping\Model\Rate $model */
                $model = $this->_repository->getById($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This Rate no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            /** convert store ids in string */
            foreach ($data as $key => $value) {
                /*
                if (!in_array($key, RateInterface::ATTRIBUTES, true)) {
                    unset($data[$key]);
                    continue;
                }
                */
                if (is_array($value)) {
                    $data[$key] = implode(',', $value);
                }
            }
            $model->setData($data);
            /** Set is_autoloaded flag to 0. To avoid deleting method when truncate autoloaded rates. */
            $model->setIsAutoloaded(0);

            try {

                $this->_eventManager->dispatch(
                    'coolrunner_shipping_rate_prepare_save',
                    ['rate' => $model, 'request' => $this->getRequest()]
                );

                $this->_repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the Rate.'));
                return $this->_processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Rate.'));
            }

            $this->dataPersistor->set('coolrunner_shipping_rate', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');

    }

    /**
     * Process result redirect
     *
     * @param \CoolRunner\Shipping\Model\Rate $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    protected function _processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newItem = $this->_repository->getModelObject();
            $newItem->setData($data);
            $newItem->setId(null);
            /** Set is_autoloaded flag to 0. To avoid deleting method when truncate autoloaded rates. */
            $model->setIsAutoloaded(0);
            //$newPage->setIsActive(false);
			$this->_repository->save($newItem);
            $this->messageManager->addSuccessMessage(__('You duplicated the Rate.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id' => $newItem->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('coolrunner_shipping_rate');
        if ($this->getRequest()->getParam('back') === 'close') {
            return $resultRedirect->setPath('*/*/', ['id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/edit');
    }
}
