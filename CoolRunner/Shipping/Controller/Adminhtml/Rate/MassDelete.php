<?php
 /**
 * MassDelete
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:53
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Rate;

use CoolRunner\Shipping\Model\RateRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Ui\Component\MassAction\Filter;
/**
 * Class MassDelete
 *
 * @package CoolRunner\Shipping
 */
class MassDelete extends BackendAppAction implements HttpPostActionInterface {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var RateRepository
     */
    protected $_repository;

    /**
     * @param BackendAppAction\Context $context
     * @param Filter                   $filter
     * @param RateRepository           $repository
     */
    public function __construct(BackendAppAction\Context $context,
                                Filter $filter,
                                RateRepository $repository
    ) {

        $this->filter = $filter;
        parent::__construct($context);
        $this->_repository = $repository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute() {

        $collection = $this->filter->getCollection($this->_repository->getCollectionObject());
        $collectionSize = $collection->getSize();

        foreach ($collection as $model) {
            $this->_repository->delete($model);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('coolrunner_shipping/rate/index');
    }
}
