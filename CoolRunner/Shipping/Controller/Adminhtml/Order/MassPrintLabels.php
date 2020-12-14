<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;
use CoolRunner\Shipping\Model\LabelRepository as LabelRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Ui\Component\MassAction\Filter;
/**
 * Class MassPrintLabels
 *
 * @package CoolRunner\Shipping
 */
class MassPrintLabels extends Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CoolRunner_Shipping::shipping';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var CoolRunnerHelper
     */
    protected $_helper;
    /**
     * @var DateTime
     */
    protected $_dateTime;
    /**
     * @var FileFactory
     */
    protected $_fileFactory;
    /**
     * @var LabelRepository
     */
    protected $_labelRepository;
    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;

    /**
     * MassPrintLabels constructor.
     *
     * @param CoolRunnerHelper         $helper
     * @param LabelGenerator           $labelGenerator
     * @param Filter                   $filter
     * @param CollectionFactory        $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param LabelRepository          $labelRepository
     * @param DateTime                 $dateTime
     * @param FileFactory              $fileFactory
     * @param Context                  $context
     */
    public function __construct(CoolRunnerHelper $helper,
                                LabelGenerator $labelGenerator,
                                Filter $filter,
                                CollectionFactory $orderCollectionFactory,
                                OrderRepositoryInterface $orderRepository,
                                LabelRepository $labelRepository,
                                DateTime $dateTime,
                                FileFactory $fileFactory,
                                Context $context)  {
        parent::__construct($context);
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_fileFactory = $fileFactory;
        $this->_labelRepository = $labelRepository;
        $this->_labelGenerator = $labelGenerator;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute() {

        $labelsContent = [];
        $items = $this->filter->getCollection($this->collectionFactory->create())->getItems();
        // Send request to controller to get shipments and get label from CoolRunner
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($items as $order) {
            try {
                $labelsCollection = $this->_labelRepository->getCollectionObject();
                $labelsCollection->addFilterByOrderId($order->getId())->addFilterPrintLabels();
                $labelsContent = array_merge($labelsContent,$this->_helper->getShippingLabels($labelsCollection));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Order #%1. %2',$order->getIncrementId(),$e->getMessage()));
            }
        }

        try {
            if (!empty($labelsContent)) {
                $outputPdf = $this->_labelGenerator->combineLabelsPdf($labelsContent);
                return $this->_fileFactory->create(
                    sprintf('ShippingLabelsCoolrunner_%s.pdf', $this->_dateTime->date('Y-m-d_H-i-s')),
                    $outputPdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
            throw new LocalizedException(__('There are no Package Labels.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
    }
}
