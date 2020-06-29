<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Curl;

use CoolRunner\Shipping\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;

class Coolrunner
{
    protected $_publicActions = ['coolrunner'];
    protected $_curl;
    protected $_objectManager;

    /**
     * @var Data
     * */
    protected $_helper;
    private $products;

    public function __construct($storeId = 0)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $this->_objectManager->create('CoolRunner\Shipping\Helper\Data');

        // Handle curl and authentication
        $userName = $this->_helper->getCredentialsConfig('cr_username', $storeId);
        $password = $this->_helper->getCredentialsConfig('cr_token', $storeId);

        $this->_curl = new Curl();
        $this->_curl->setCredentials($userName, $password);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // Do nothing
    }

    private function addProducts($carrier, $products)
    {
        if (!empty($products)) {
            // Add products to DB in table called coolrunner_products
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('coolrunner_products');

            $sql = "INSERT INTO " . $tableName . " (carrier, products) Values ('{$carrier}', '{$products}')";
            $connection->query($sql);
        }
    }

    private function getProducts($carrier)
    {
        // Get products from DB in table called coolrunner_products
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('coolrunner_products');

        $sql = "SELECT * FROM " . $tableName . " WHERE carrier = '{$carrier}'";

        return $connection->fetchAll($sql);
    }

    public function getProductsByCarrier($carrier)
    {
        // Checks if products is saved in DB and if not get these from Coolrunner and saves to DB
        if (empty($this->getProducts($carrier))) {
            if (!isset($this->products) or $this->products == '') {
                $this->_curl->get('https://api.coolrunner.dk/v3/products/dk');
                $this->products = json_decode($this->_curl->getBody());

                if (isset($this->products->DK->$carrier)) {
                    $this->products = json_encode($this->products->DK->$carrier);
                    $this->addProducts($carrier, $this->products);
                } else {
                    $this->addProducts($carrier, json_encode([]));
                }
            }
        }

        $this->products = json_decode($this->getProducts($carrier)[0]['products']);
        $formattedProducts = [];

        foreach ($this->products as $productType => $services) {
            foreach ($services as $service) {
                if (strtolower($productType) != 'dummy') {
                    if (strtoupper($service->services[0]->code) != '') {
                        $label = '_' . strtoupper($service->services[0]->code);
                    } else {
                        $label = '';
                    }

                    $formattedProducts[] = [
                        'value' => strtoupper($carrier) . '_' . strtoupper($productType) . $label,
                        'label' => strtoupper($carrier) . '_' . strtoupper($productType) . $label
                    ];
                }
            }
        }

        return $formattedProducts;
    }

    public function getNearestDroppoints($carrier, $countryCode, $street, $zipCode, $city)
    {
        $curlUrl = 'https://api.coolrunner.dk/v3/servicepoints/' . $carrier . '?country_code=' . $countryCode . '&street=' . str_replace(' ', '+', $street) . '&zip_code=' . $zipCode . '&city=' . $city;
        $this->_curl->get($curlUrl);

        return json_decode($this->_curl->getBody());
    }

    public function createShipment($shipmentData, $agreementType, $storeId, $orderId)
    {
        try {
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('coolrunner_shipping_labels');
            $addShipment = false;

            if ($agreementType == 'normal') {
                // Handle all shipments with normal agreement
                $curlUrl = 'https://api.coolrunner.dk/v3/shipments';

                $this->_curl->post($curlUrl, $shipmentData);
                $responseData = json_decode($this->_curl->getBody());

                // Store labels to DB
                if (isset($responseData->package_number)) {
                    // Add products to DB in table called coolrunner_shipping_labels
                    $sql = "INSERT INTO " . $tableName . " (ordernumber, package_number, link_self, link_label, link_tracking, unique_id, price_incl_tax, price_excl_tax, carrier, product, service) Values ('{$orderId}', '{$responseData->package_number}', '{$responseData->_links->self}', '{$responseData->_links->label}', '{$responseData->_links->tracking}', '', '{$responseData->price->incl_tax}', '{$responseData->price->excl_tax}', '{$responseData->carrier}', '{$responseData->carrier_product}', '{$responseData->carrier_service}')";
                    $connection->query($sql);
                    $addShipment = true;
                }
            } elseif ($agreementType == 'pcn') {
                // Handle all shipments using PCN
                $curlUrl = 'https://api.coolrunner.dk/pcn/order/create';
                $this->_curl->post($curlUrl, json_encode($shipmentData));
                $responseData = json_decode($this->_curl->getBody());

                // Store labels to DB
                if (isset($responseData->shipment_id)) {
                    // Add products to DB in table called coolrunner_shipping_labels
                    $sql = "INSERT INTO " . $tableName . " (ordernumber, package_number, link_self, link_label, link_tracking, unique_id, price_incl_tax, price_excl_tax, carrier, product, service) Values ('{$orderId}', '{$responseData->package_number}', 'pcn', 'pcn', 'pcn', '{$responseData->unique_id}', 0, 0, '{$shipmentData['carrier']}', '{$shipmentData['carrier_product']}', '{$shipmentData['carrier_service']}')";
                    $connection->query($sql);
                    $addShipment = true;
                }
            }

            if ($addShipment) {
                // Load the order increment ID
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

                // Check if order can be shipped or has already shipped
                if (!$order->canShip()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an shipment.')
                    );
                }

                // Initialize the order shipment object
                $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order);

                // Loop through order items
                foreach ($order->getAllItems() as $orderItem) {
                    // Check if order item has qty to ship or is virtual
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qtyShipped = $orderItem->getQtyToShip();

                    // Create shipment item with qty
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                    // Add shipment item to shipment
                    $shipment->addItem($shipmentItem);
                }

                // Register shipment
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);

                try {
                    // Save created shipment and order
                    $shipment->save();
                    $shipment->getOrder()->save();

                    // Send email
                    $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                        ->notify($shipment);

                    $shipment->save();
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($e->getMessage())
                    );
                }
            }
        } catch (\Exception $exception) {
            print_r($exception);
        }
    }

    public function getShippingLabels($orderId, $storeId)
    {
        // Get products from DB in table called coolrunner_products
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('coolrunner_shipping_labels');

        $sql = "SELECT * FROM " . $tableName . " WHERE ordernumber = '{$orderId}'";
        $labels = [];
        foreach ($connection->fetchAll($sql) as $label) {
            if ($label['link_self'] != 'pcn') {
                $this->_curl->get($label['link_label']);

                $labels[] = base64_encode($this->_curl->getBody());
            }
        }

        return $labels;
    }
}
