<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

use CoolRunner\Shipping\Model\LabelRepository as LabelRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Shipment\NotifierInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Ui\Component\MassAction\Filter;
use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;

/**
 * Class CreateLabels
 *
 * @package CoolRunner\Shipping
 */
class MassCreateLabels extends Action {

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
     * @var LabelRepository
     */
    protected $_labelRepository;
    /**
     * @var ShipOrderInterface
     */
    protected $_shipOrderModel;
    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;
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
     * CreateLabels constructor.
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
     * @param LabelRepository             $labelRepository
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
                                LabelRepository $labelRepository,
                                Context $context)  {
        parent::__construct($context);
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->_helper = $helper;
        $this->_labelRepository = $labelRepository;
        $this->_shipOrderModel = $shipOrderModel;
        $this->_labelGenerator = $labelGenerator;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_notifierInterface = $notifierInterface;
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute() {
        $items = $this->filter->getCollection($this->collectionFactory->create())->getItems();
        $countCreatedLabels = 0;

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($items as $order) {
            try {
                /** check if order is Coolrunner */
                if(!$this->_helper->isOrderCoolRunner($order)){
                    throw new LocalizedException(__('It is not CoolRunner order.'));
                }
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

        if($countCreatedLabels>0){
            $this->messageManager->addSuccessMessage(__('Gennemført oprettelse af label på de valgte ordre.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
    }
}
