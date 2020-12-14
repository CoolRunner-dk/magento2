<?php
/**
 *  InlineEdit
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    28.08.2020
 * Time:    16:57
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use CoolRunner\Shipping\Model\RateRepository;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class InlineEdit
 *
 * @package CoolRunner\Shipping
 */
class InlineEdit extends BackendAppAction {

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
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * InlineEdit constructor.
     *
     * @param BackendAppAction\Context $context
     * @param RateRepository           $repository
     * @param JsonFactory              $jsonFactory
     */
    public function __construct(
        BackendAppAction\Context $context,
        RateRepository $repository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->_repository = $repository;
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $rateId) {
                    /** @var \CoolRunner\Shipping\Model\Rate $model */
                    $model = $this->_repository->getById($rateId);
                    try {
                        /** convert store ids in string */
                        foreach ($postItems[$rateId] as $key => $value) {
                            /*
                            if (!in_array($key, RateInterface::ATTRIBUTES, true)) {
                                unset($postItems[$rateId][$key]);
                                continue;
                            }
                            */
                            if (is_array($value)) {
                                $postItems[$rateId][$key] = implode(',', $value);
                            }
                        }

                        $model->setData(array_merge($model->getData(), $postItems[$rateId]));
                        /** Set is_autoloaded flag to 0. To avoid deleting method when truncate autoloaded rates. */
                        $model->setIsAutoloaded(0);

                        $this->_eventManager->dispatch(
                            'coolrunner_shipping_rate_prepare_inlinesave',
                            ['rate' => $model, 'request' => $this->getRequest()]
                        );

                        $this->_repository->save($model);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithId(
                            $model,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add block title to error message
     *
     * @param \CoolRunner\Shipping\Model\Rate $model
     * @param string                          $errorText
     *
     * @return string
     */
    protected function getErrorWithId($model, $errorText) {
        return '[ID: ' . $model->getId() . '] ' . $errorText;
    }
}
