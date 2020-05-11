<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Order;

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

    public function __construct(Context $context, Filter $filter, CollectionFactory $orderCollectionFactory, OrderRepositoryInterface $orderRepository)
    {
        parent::__construct($context);
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        try {
            echo 'CreateLabels on following orders: ';
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection->getItems() as $order) {
                echo $order->getId() . ', ';

                if (!empty($order->getShippingAddress())) {
                    print_r(['street' => $order->getShippingAddress()->getStreet(), 'postcode' => $order->getShippingAddress()->getPostcode(), 'city' => $order->getShippingAddress()->getCity()]);
                } else {
                    print_r(['street' => $order->getBillingAddress()->getStreet(), 'postcode' => $order->getBillingAddress()->getPostcode(), 'city' => $order->getBillingAddress()->getCity()]);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
