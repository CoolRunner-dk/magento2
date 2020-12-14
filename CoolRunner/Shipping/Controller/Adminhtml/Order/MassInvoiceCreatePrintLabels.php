<?php
/**
 * MassInvoiceCreatePrintLabels
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    10.08.2020
 * Time:    14:33
 */
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;
use CoolRunner\Shipping\Model\LabelRepository as LabelRepository;
use Magento\Backend\App\Action as BackendAppAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Shipment\NotifierInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassInvoiceCreatePrintLabels
 *
 * @package CoolRunner\Shipping
 */
class MassInvoiceCreatePrintLabels extends BackendAppAction {

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
     * @var InvoiceOrderInterface
     */
    protected $invoiceOrder;
    /**
     * @var LabelRepository
     */
    protected $_labelRepository;
    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;
    /**
     * @var ShipOrderInterface
     */
    protected $_shipOrderModel;
    /**
     * @var ShipmentRepositoryInterface
     */
    protected $_shipmentRepository;
    /**
     * @var NotifierInterface
     */
    protected $_notifierInterface;
    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * MassPrintLabels constructor.
     *
     * @param CoolRunnerHelper            $helper
     * @param ShipOrderInterface          $shipOrderModel
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param NotifierInterface           $notifierInterface
     * @param LabelGenerator              $labelGenerator
     * @param CarrierFactory              $carrierFactory
     * @param Filter                      $filter
     * @param CollectionFactory           $orderCollectionFactory
     * @param OrderRepositoryInterface    $orderRepository
     * @param InvoiceOrderInterface       $invoiceOrder
     * @param LabelRepository             $labelRepository
     * @param DateTime                    $dateTime
     * @param FileFactory                 $fileFactory
     * @param Context                     $context
     */
    public function __construct(CoolRunnerHelper $helper,
                                ShipOrderInterface $shipOrderModel,
                                ShipmentRepositoryInterface $shipmentRepository,
                                NotifierInterface $notifierInterface,
                                LabelGenerator $labelGenerator,
                                CarrierFactory $carrierFactory,
                                Filter $filter,
                                CollectionFactory $orderCollectionFactory,
                                OrderRepositoryInterface $orderRepository,
                                InvoiceOrderInterface $invoiceOrder,
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
        $this->invoiceOrder = $invoiceOrder;
        $this->_labelRepository = $labelRepository;
        $this->_labelGenerator = $labelGenerator;
        $this->_shipOrderModel = $shipOrderModel;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_notifierInterface = $notifierInterface;
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function execute() {

        $countInvoiceOrder      = 0;
        $countCreatedLabels     = 0;
        $capture                = false;
        $notifyInvoice          = false;
//        $notifyShipment = false;
        $items = $this->filter->getCollection($this->collectionFactory->create())->getItems();


        /** @var \Magento\Sales\Model\Order $order */
        foreach ($items as $order) {
            try {
                if(!$this->_helper->isOrderCoolRunner($order)){
                    throw new LocalizedException(__("Invoice Document Validation Error(s):\n" . __('It is not CoolRunner order.')));
                }
                /**
                 * create invoice
                 */
                $capture        = $this->_helper->getAgreementConfig('cr_invoicecapture',$order->getStoreId());
                $notifyInvoice  = $this->_helper->getAgreementConfig('cr_invoicenotify',$order->getStoreId());
                $this->invoiceOrder->execute($order->getId(),$capture,[],$notifyInvoice);
                $countInvoiceOrder++;

                /**
                 * Create shipment
                 */
                /** get a setting notify customer about created shipment*/
                $notifyShipment = $this->_helper->getAgreementConfig('cr_shipmentnotify',$order->getStoreId());

                /** if no shipment create a new */
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                if(!$order->hasShipments()){
                    /** do shipment without notification. We will notify customer after create a label.*/
                    $shipmentId = $this->_shipOrderModel->execute($order->getId());
                    $shipment = $this->_shipmentRepository->get($shipmentId);
                } else {
                    $shipment = $order->getShipmentsCollection()->getLastItem();
                };

                $carrierModel = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode(),$order->getStoreId());

                if(method_exists($carrierModel,'prepareRequestOfDefaultPackage')){
                    $packageRequest = $carrierModel->prepareRequestOfDefaultPackage($order);
                } else {
                    continue;
                }

                $this->_labelGenerator->create($shipment, $packageRequest);
                $this->_shipmentRepository->save($shipment);

                /** notify customer about created shipment */
                if($notifyShipment){
                    $this->_notifierInterface->notify( $order,$shipment);
                }
                $countCreatedLabels++;

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Order #%1. %2',$order->getIncrementId(),$e->getMessage()));
            }
        }

        /** Generate pdf */
        try {
            $labelsContent = [];
            /** Get array of available labels*/
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($items as $order) {
                $labelsCollection = $this->_labelRepository->getCollectionObject();
                $labelsCollection->addFilterByOrderId($order->getId())->addFilterPrintLabels();
                $labelsContent = array_merge($labelsContent,$this->_helper->getShippingLabels($labelsCollection));
            }
            if (!empty($labelsContent)) {
                $outputPdf = $this->_labelGenerator->combineLabelsPdf($labelsContent);
                return $this->_fileFactory->create(
                    sprintf('ShippingLabelsCoolrunner_%s.pdf', $this->_dateTime->date('Y-m-d_H-i-s')),
                    $outputPdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** final summary for customer */
        if($countCreatedLabels>0 || $countInvoiceOrder>0){
            $this->messageManager->addSuccessMessage(__('Invoices: %1. Created Labels: %2',$countInvoiceOrder,$countCreatedLabels));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
	}
}
