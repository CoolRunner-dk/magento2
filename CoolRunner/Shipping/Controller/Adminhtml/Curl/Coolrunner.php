<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Curl;

use CoolRunner\Shipping\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;

class Coolrunner
{
    protected $_publicActions = ['coolrunner'];
    protected $_objectManager;
    protected $username;
    protected $password;

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
        $this->username = $this->_helper->getCredentialsConfig('cr_username', $storeId);
        $this->password = $this->_helper->getCredentialsConfig('cr_token', $storeId);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // Do nothing
    }

    private function addProducts($carrier, $country, $type, $products)
    {
        if (!empty($products)) {
            // Add products to DB in table called coolrunner_products
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('coolrunner_products');

            $sql = "INSERT INTO " . $tableName . " (carrier, country, type, products) Values ('{$carrier}', '{$country}', '{$type}', '{$products}')";
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
                $curl = new Curl();
                $curl->setCredentials($this->username, $this->password);
                $curl->get('https://api.coolrunner.dk/v3/products/dk');
                $this->products = json_decode($curl->getBody());

                foreach ($this->products as $country => $products) {
                    foreach ($products as $productCarrier => $productTypes) {
                        foreach ($productTypes as $type => $productData) {
                            if (isset($productData) and $productCarrier == $carrier) {
                                $this->products = json_encode($productData);
                                $this->addProducts($productCarrier, $country, $type, $this->products);
                            } else {
                                $this->addProducts($carrier, $country, $type, json_encode([]));
                            }
                        }
                    }
                }
            }
        }

        $this->products = $this->getProducts($carrier);
        $formattedProducts = [];
        $alreadyAdded = [];

        foreach ($this->products as $product) {
            $carrier = $product['carrier'];
            $carrier_product = $product['type'];

            foreach (json_decode($product['products']) as $singleProduct) {
                $carrier_service = $singleProduct->services[0]->code ?: '';

                if (!in_array(strtoupper($carrier) . '_' . strtoupper($carrier_product) . '_' . strtoupper($carrier_service), $alreadyAdded)) {
                    $formattedProducts[] = [
                        'value' => strtoupper($carrier) . '_' . strtoupper($carrier_product) . '_' . strtoupper($carrier_service),
                        'label' => strtoupper($carrier) . '_' . strtoupper($carrier_product) . '_' . strtoupper($carrier_service),
                    ];

                    $alreadyAdded[] = strtoupper($carrier) . '_' . strtoupper($carrier_product) . '_' . strtoupper($carrier_service);
                }
            }
        }

        return $formattedProducts;
    }

    public function getNearestDroppoints($carrier, $countryCode, $street, $zipCode, $city)
    {
        $curl = new Curl();
        $curl->setCredentials($this->username, $this->password);
        $curl->addHeader("X-Developer-Id", "Magento2-v2");

        $curlUrl = 'https://api.coolrunner.dk/v3/servicepoints/' . $carrier . '?country_code=' . $countryCode . '&street=' . str_replace(' ', '+', $street) . '&zip_code=' . $zipCode . '&city=' . $city;
        $curl->get($curlUrl);

        return json_decode($curl->getBody());
    }

    public function findDroppointById($carrier, $droppointId)
    {
        $curl = new Curl();
        $curl->setCredentials($this->username, $this->password);
        $curl->addHeader("X-Developer-Id", "Magento2-v2");

        $curlUrl = 'https://api.coolrunner.dk/v3/servicepoints/' . $carrier . '/' . $droppointId;
        $curl->get($curlUrl);

        return json_decode($curl->getBody());
    }

    public function createShipment($shipmentData, $agreementType, $orderId, $makeShipment)
    {
        try {
            $curl = new Curl();
            $curl->setCredentials($this->username, $this->password);
            $curl->addHeader("X-Developer-Id", "Magento2-v2");

            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('coolrunner_shipping_labels');
            $addShipment = false;

            if ($agreementType == 'normal') {
                // Handle all shipments with own stock
                $curlUrl = 'https://api.coolrunner.dk/v3/shipments';

                $curl->post($curlUrl, $shipmentData);
                $responseData = json_decode($curl->getBody());

                // Store labels to DB
                if (isset($responseData->package_number)) {
                    $sql = "INSERT INTO " . $tableName . " (ordernumber, package_number, link_self, link_label, link_tracking, unique_id, price_incl_tax, price_excl_tax, carrier, product, service) Values ('{$orderId}', '{$responseData->package_number}', '{$responseData->_links->self}', '{$responseData->_links->label}', 'https://tracking.coolrunner.dk/?shipment={$responseData->package_number}', '', '{$responseData->price->incl_tax}', '{$responseData->price->excl_tax}', '{$responseData->carrier}', '{$responseData->carrier_product}', '{$responseData->carrier_service}')";
                    $connection->query($sql);
                    $addShipment = true;
                }
            } elseif ($agreementType == 'pcn') {
                // Handle all shipments using PCN
                $curlUrl = 'https://api.coolrunner.dk/pcn/order/create';
                $curl->post($curlUrl, json_encode($shipmentData));
                $responseData = json_decode($curl->getBody());

                // Store labels to DB
                if (isset($responseData->shipment_id)) {
                    // Add products to DB in table called coolrunner_shipping_labels
                    $sql = "INSERT INTO " . $tableName . " (ordernumber, package_number, link_self, link_label, link_tracking, unique_id, price_incl_tax, price_excl_tax, carrier, product, service) Values ('{$orderId}', '{$responseData->package_number}', 'pcn', 'pcn', 'pcn', '{$responseData->unique_id}', 0, 0, '{$shipmentData['carrier']}', '{$shipmentData['carrier_product']}', '{$shipmentData['carrier_service']}')";
                    $connection->query($sql);
                    $addShipment = true;
                }
            }

            if ($addShipment and $makeShipment) {
                // Heavy memory?
                $this->createMagentoShipment($orderId);
            }
            unset($curl);
        } catch (\Exception $exception) {
            print_r($exception);
        }
    }

    public function createMagentoShipment($orderId)
    {
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
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
        unset($order);
        unset($shipment);
    }

    public function getShippingLabels($orderId)
    {
        // Get shipping labels from DB in table called coolrunner_shipping_labels
        $curl = new Curl();
        $curl->setCredentials($this->username, $this->password);
        $curl->addHeader("X-Developer-Id", "Magento2-v2");

        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('coolrunner_shipping_labels');

        $sql = "SELECT * FROM " . $tableName . " WHERE ordernumber = '{$orderId}'";
        $labels = [];
        foreach ($connection->fetchAll($sql) as $label) {
            if ($label['link_self'] != 'pcn') {
                $curl->get($label['link_label']);

                $labels[] = base64_encode($curl->getBody());
            }
        }

        return $labels;
    }
}
