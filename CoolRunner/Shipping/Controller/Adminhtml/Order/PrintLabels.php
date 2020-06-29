<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class PrintLabels extends Action
{
    protected $collectionFactory;
    protected $filter;
    protected $orderRepository;
    protected $redirectUrl = '*/*/';
    protected $_objectManager;
    protected $_helper;

    public function __construct(Context $context, Filter $filter, CollectionFactory $orderCollectionFactory, OrderRepositoryInterface $orderRepository)
    {
        parent::__construct($context);
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $this->_objectManager->create('CoolRunner\Shipping\Helper\Data');
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $allLabels = [];

            // Send request to controller to get shipments and get label from CoolRunner
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection->getItems() as $order) {
                if ($this->_helper->getAgreementConfig('cr_type', $order->getStoreId()) == 'normal') {
                    $labels = $this->_helper->getShippingLabels($order->getId(), $order->getStoreId());

                    if (!empty($labels)) {
                        $allLabels[] = $labels;
                    }
                }
            }

            // Handle print of pdf (merge)
            $checked = 0;
            $finalArray = [];
            while ($checked < count($allLabels)) {
                foreach ($allLabels[$checked] as $toAdd) {
                    $finalArray[] = $toAdd;
                }
                $checked++;
            }
            $pdfMerged = new \Zend_Pdf();

            foreach ($finalArray as $singleLabel) {
                $base64_decode = base64_decode($singleLabel);
                $pdf = \Zend_Pdf::parse($base64_decode);
                foreach ($pdf->pages as $page) {
                    $clonedPage = clone $page;
                    $pdfMerged->pages[] = $clonedPage;
                }
                unset($clonedPage);
            }

            // Shows the merged label
            header('Content-type: application/pdf');
            echo $pdfMerged->render();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
