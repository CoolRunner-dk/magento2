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

    public function __construct($storeId)
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
}
