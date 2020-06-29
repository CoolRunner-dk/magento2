<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

use CoolRunner\Shipping\Controller\Adminhtml\Curl\Coolrunner;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class CreateLabels extends Action
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

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection->getItems() as $order) {
                $explodedMethod = explode('_', $order->getShippingMethod());
                $carrier = $explodedMethod[1];
                $product = $explodedMethod[4];
                $service = $explodedMethod[5];
                $orderLines = [];
                $shipmentData = [];
                $totalWeight = 0;

                // Find closest droppoint
                if ($service == 'droppoint' or $service == 'servicepoint') {
                    $closestDroppoints = $this->_helper->findClosestDroppoints(
                        $carrier,
                        $order->getShippingAddress()->getCountryId(),
                        $order->getShippingAddress()->getStreet()[0],
                        $order->getShippingAddress()->getPostcode(),
                        $order->getShippingAddress()->getCity()
                    );

                    $closest = $closestDroppoints->servicepoints[0];
                }

                // Handle orderlines for PCN and to get weight
                foreach ($order->getAllItems() as $orderItem) {
                    $orderLines[] = ['item_number' => $orderItem->getSku(), 'qty' => number_format($orderItem->getQtyOrdered(), 0)];
                    $totalWeight += ($orderItem->getWeight()*1000);
                }

                if ($this->_helper->getAgreementConfig('cr_type', $order->getStoreId()) == 'normal') {
                    $shipmentData = [
                        "sender" => [
                            "name" => $order->getStore()->getFrontendName(),
                            "attention" => "",
                            "street1" => $this->_helper->getStoreInformation('street_line1', $order->getStoreId()),
                            "street2" => $this->_helper->getStoreInformation('street_line2', $order->getStoreId()) ?? "",
                            "zip_code" => $this->_helper->getStoreInformation('postcode', $order->getStoreId()),
                            "city" => $this->_helper->getStoreInformation('city', $order->getStoreId()),
                            "country" => $this->_helper->getStoreInformation('country_id', $order->getStoreId()),
                            "phone" => $this->_helper->getStoreInformation('phone', $order->getStoreId()),
                            "email" => ""
                        ],
                        "receiver" => [
                            "name" => $order->getCustomerName(),
                            "attention" => "",
                            "street1" => $order->getShippingAddress()->getStreet()[0],
                            "street2" => $order->getShippingAddress()->getStreet()[1] ?? "",
                            "zip_code" => $order->getShippingAddress()->getPostcode(),
                            "city" => $order->getShippingAddress()->getCity(),
                            "country" => $order->getShippingAddress()->getCountryId(),
                            "phone" => $order->getShippingAddress()->getTelephone(),
                            "email" => $order->getShippingAddress()->getEmail(),
                            "notify_sms" => $order->getShippingAddress()->getTelephone(),
                            "notify_email" => $order->getShippingAddress()->getEmail()
                        ],
                        "length" => "15",
                        "width" => "15",
                        "height" => "6",
                        "weight" => $totalWeight,
                        "carrier" => $carrier,
                        "carrier_product" => $product,
                        "carrier_service" => $service ?? "",
                        "reference" => $order->getRealOrderId(),
                        "description" => "",
                        "comment" => "",
                        "label_format" => $this->_helper->getAgreementConfig('cr_printformat', $order->getStoreId()),
                        "servicepoint_id" => $closest->id ?? 0
                    ];
                } elseif ($this->_helper->getAgreementConfig('cr_type', $order->getStoreId()) == 'pcn') {
                    $shipmentData = [
                        "order_number" => $order->getRealOrderId(),
                        "receiver_name" => $order->getShippingAddress()->getName(),
                        "receiver_attention" => "",
                        "receiver_street1" => $order->getShippingAddress()->getStreet()[0],
                        "receiver_street2" => $order->getShippingAddress()->getStreet()[1] ?? "",
                        "receiver_zipcode" => $order->getShippingAddress()->getPostcode(),
                        "receiver_city" => $order->getShippingAddress()->getCity(),
                        "receiver_country" => $order->getShippingAddress()->getCountryId(),
                        "receiver_phone" => $order->getShippingAddress()->getTelephone(),
                        "receiver_email" => $order->getShippingAddress()->getEmail(),
                        "receiver_notify_sms" => $order->getShippingAddress()->getTelephone(),
                        "receiver_notify_email" => $order->getShippingAddress()->getEmail(),
                        "droppoint_id" => $closest->id ?? 0,
                        "droppoint_name" => $closest->name ?? "",
                        "droppoint_street1" => $closest->address->street ?? "",
                        "droppoint_zipcode" => $closest->address->zip_code ?? "",
                        "droppoint_city" => $closest->address->city ?? "",
                        "droppoint_country" => $closest->address->country_code ?? "",
                        "carrier" => $carrier,
                        "carrier_product" => $product,
                        "carrier_service" => $service ?? "",
                        "reference" => $order->getRealOrderId(),
                        "description" => "",
                        "comment" => "",
                        "order_lines" => $orderLines
                    ];
                }

                $this->_helper->createShipment($shipmentData, $this->_helper->getAgreementConfig('cr_type', $order->getStoreId()), $order->getStoreId(), $order->getRealOrderId());
            }
            $this->messageManager->addSuccessMessage('Gennemført oprettelse af label på de valgte ordre.');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
