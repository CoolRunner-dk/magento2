<?php
namespace CoolRunner\Shipping\Controller\Adminhtml\Curl;

use Magento\Framework\HTTP\Client\Curl;

class Coolrunner
{
    protected $_publicActions = ['coolrunner'];
    protected $_curl;
    protected $_objectManager;
    private $products;

    public function __construct()
    {
        // TODO: Get username from settings
        $userName = "kq+firma@tric.dk";

        // TODO: Get password from settings
        $password = "ni238ebe1zz3qgbmra3lfreozit5h6qu";

        $this->_curl = new Curl();
        $this->_curl->setCredentials($userName, $password);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
    }

    private function addProducts($carrier, $products)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('coolrunner_products'); //gives table name with prefix

        $sql = "INSERT INTO " . $tableName . " (carrier, products) Values ('{$carrier}', '{$products}')";
        $connection->query($sql);
    }

    private function getProducts($carrier)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('coolrunner_products');

        $sql = "SELECT * FROM " . $tableName . " WHERE carrier = '{$carrier}'";

        return $connection->fetchAll($sql);
    }

    public function getProductsByCarrier($carrier)
    {
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
        $formatedProducts = [];

        foreach ($this->products as $productType => $services) {
            foreach ($services as $service) {
                if (strtolower($productType) != 'dummy') {
                    if (strtoupper($service->services[0]->code) != '') {
                        $label = '_' . strtoupper($service->services[0]->code);
                    } else {
                        $label = '';
                    }
                    $formatedProducts[] = ['value' => strtoupper($carrier) . '_' . strtoupper($productType) . '_' . strtoupper($service->services[0]->code), 'label' => strtoupper($carrier) . '_' . strtoupper($productType) . $label];
                }
            }
        }

        return $formatedProducts;
    }
}
