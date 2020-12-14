<?php
/**
 *  Curl
 *
 * @copyright Copyright © 2020 HeadWayIt https =>//headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date =>    06.08.2020
 * Time =>    14 =>35
 */
namespace CoolRunner\Shipping\Helper;

use CoolRunner\Shipping\Model\ResourceModel\Labels\Collection as LabelsCollection;
use Magento\Backend\Model\UrlInterface as BackendUrlBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl as Curl;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory as ShipmentTrackModelFactory;

/**
 * Class Curl
 * @see https =>//docs.coolrunner.dk/#create
 *
 * @package CoolRunner\Shipping\Helper
 */
class CurlData extends Data
{
    /**
     * @var ShipOrderInterface
     */
    protected $_shipOrderModel;
    /**
     * @var ShipmentTrackModelFactory
     */
    protected $_shipmentTrackModelFactory;
    /**
     * @var Curl|null
     */
    protected $_curl = null;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    private $smarttoken;

    /**
     * Data constructor.
     *
     * @param BackendUrlBuilder                     $backendUrlBuilder
     * @param ShipOrderInterface                    $shipOrderModel
     * @param ShipmentTrackModelFactory             $shipmentTrackModelFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param Context                               $context
     */
    public function __construct(
        BackendUrlBuilder $backendUrlBuilder,
        ShipOrderInterface $shipOrderModel,
        ShipmentTrackModelFactory $shipmentTrackModelFactory,
        \Magento\Framework\App\CacheInterface $cache,
        Context $context
    ) {
        parent::__construct($backendUrlBuilder, $context);
        $this->username = $this->getCredentialsConfig('cr_username');
        $this->password = $this->getCredentialsConfig('cr_token');
        $this->smarttoken = $this->getCredentialsConfig('cr_smartcheckouttoken');
        $this->_shipOrderModel = $shipOrderModel;
        $this->_shipmentTrackModelFactory = $shipmentTrackModelFactory;
        $this->_cache = $cache;
    }

    /**
     * @param string $data
     * @param string $identifier
     * @param array $tags1
     * @param null  $lifeTime
     *
     * @return bool
     */
    protected function _saveRequestToCache($data, $identifier, $tags = [], $lifeTime = null)
    {
        return $this->_cache->save($data, $identifier, $tags = [], $lifeTime = null);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected function _loadRequestFromCache($identifier)
    {
        return $this->_cache->load($identifier);
    }

    /**
     * Prepare Curl object
     *
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    protected function _prepareCurlObject()
    {
        if ($this->_curl === null) {
            $this->_curl = new Curl();
            $this->_curl->setCredentials($this->username, $this->password);
            $this->_curl->addHeader("X-Developer-Id", "Magento2-v1");
        }
        return $this->_curl;
    }

    /**
     * @param array $shipmentData
     *
     * @return array
     */
    public function generateNormalShipment($shipmentData)
    {
        $responseData = [];
        if (is_array($shipmentData) && !empty($shipmentData)) {
            $curl = $this->_prepareCurlObject();
            // Handle all shipments with own stock
            $curlUrl = 'https://api.coolrunner.dk/v3/shipments';
            $curl->post($curlUrl, $shipmentData);
            $responseData = json_decode($curl->getBody(), true);
        }

        return $responseData;
    }

    /**
     * @param array $shipmentData
     *
     * @return array
     */
    public function generatePcnShipment($shipmentData)
    {
        $responseData = [];
        if (is_array($shipmentData) && !empty($shipmentData)) {
            $curl = $this->_prepareCurlObject();
            $curlUrl = 'https://api.coolrunner.dk/pcn/order/create';
            $curl->post($curlUrl, json_encode($shipmentData));
            $responseData = json_decode($curl->getBody(), true);
        }

        return $responseData;
    }

    /**
     * @param LabelsCollection $labelsCollection
     *
     * @return array
     */
    public function getShippingLabels($labelsCollection)
    {
        /** @var LabelsCollection $labelsCollection */
        $labels = [];
        if ($labelsCollection->getSize()>0) {
            foreach ($labelsCollection as $_label) {
                if ($_label->getPackageNumber() != '') {
                    $labels[] = $this->getShippingLabelContent($_label->getPackageNumber());
                }
            }
        }
        return $labels;
    }

    /**
     * @param $packageNumber
     *
     * @return string
     */
    public function getShippingLabelContent($packageNumber)
    {
        $packageNumber = trim($packageNumber);

        $cacheKey    = self::COOLRUNNER_SERVICE_PREFIX . 'label_content_' . $packageNumber;
        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $curl->get(sprintf('https://api.coolrunner.dk/v3/shipments/%s/label', $packageNumber));
            $response = $curl->getBody();
            $this->_saveRequestToCache($response, $cacheKey);
        }
        return $response;
    }

    /**
     * @param $carrier
     * @param $countryCode
     * @param $street
     * @param $zipCode
     * @param $city
     *
     * @return array
     */
    public function findClosestDroppoints($carrier, $countryCode, $street, $zipCode, $city)
    {
        $carrier     = str_replace(self::COOLRUNNER_SERVICE_PREFIX, '', $carrier);
        $countryCode = trim($countryCode);
        $street      = str_replace(' ', '+', $street);
        $zipCode     = trim($zipCode);
        $city        = trim($city);

        $cacheKey    = self::COOLRUNNER_SERVICE_PREFIX . 'droppoints_' . $carrier . $countryCode . $zipCode . $city . $street;
        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $curlUrl = sprintf('https://api.coolrunner.dk/v3/servicepoints/%s?country_code=%s&street=%s&zip_code=%s&city=%s', $carrier, $countryCode, $street, $zipCode, $city);
            $curl->get($curlUrl);
            $response = $curl->getBody();
            $this->_saveRequestToCache($response, $cacheKey);
        }
        return json_decode($response, true);
    }

    /**
     * @param string $carrier
     * @param string $droppointId
     *
     * @return mixed
     */
    public function findDroppointById($carrier, $droppointId)
    {
        $carrier     = str_replace(self::COOLRUNNER_SERVICE_PREFIX, '', $carrier);
        $droppointId = trim($droppointId);

        $cacheKey    = self::COOLRUNNER_SERVICE_PREFIX . 'droppoint_' . $carrier . '_' . $droppointId;
        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $curlUrl = sprintf('https://api.coolrunner.dk/v3/servicepoints/%s/%s', $carrier, $droppointId);
            $curl->get($curlUrl);
            $response = $curl->getBody();
            $this->_saveRequestToCache($response, $cacheKey);
        }
        return json_decode($response, true);
    }

    /**
     * @param string $packageNumber
     *
     * @return array
     */
    public function getTracking($packageNumber)
    {
        $packageNumber = trim($packageNumber);

        $cacheKey =  self::COOLRUNNER_SERVICE_PREFIX . 'tracking_' . $packageNumber;
        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $curlUrl = sprintf('https://api.coolrunner.dk/v3/shipments/%s/tracking', $packageNumber);
            $curl->get($curlUrl);
            $response = $curl->getBody();
            $this->_saveRequestToCache($response, $cacheKey, [], 480); /** set life time 8 minutes (480s) to avoid flush requests  */
        }
        return json_decode($response, true);
    }

    /**
     * @param string $countryId
     *
     * @return array
     */
    public function getRates($countryId)
    {
        $countryId = trim($countryId);

        $cacheKey =  self::COOLRUNNER_SERVICE_PREFIX . 'rates_' . $countryId;
        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $curl->get(sprintf('https://api.coolrunner.dk/v3/products/%s', $countryId));
            $response = $curl->getBody();
            $this->_saveRequestToCache($response, $cacheKey);
        }
        return json_decode($response, true);
    }

    public function getSmartcheckoutCredentials($installData)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion(); //will return the magento version

        $params = [
            "activation_code" => $installData['install_token'],
            "name" => $installData['install_storename'],
            "platform" => "Magento 2",
            "version" => $version,
            //"shop_url" => $installData['install_storeurl'],
            //"pingback_url" => ""
        ];

        $curl = $this->_prepareCurlObject();

        $curl->setHeaders([
            'Content-Type' => 'application/json'
        ]);

        $curl->post("https://core.coolrunner.dk/smartcheckout/install", json_encode($params));
        $response = json_decode($curl->getBody());

        if (isset($response->shop_info)) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\Config\Storage\WriterInterface::class)->save('cr_settings/credentials/cr_username', $response->shop_info->integration_email);
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\Config\Storage\WriterInterface::class)->save('cr_settings/credentials/cr_token', $response->shop_info->integration_token);
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\Config\Storage\WriterInterface::class)->save('cr_settings/credentials/cr_smartcheckouttoken', $response->shop_info->shop_token);
        }

        return ['response' => $response];
    }

    public function getSmartcheckoutRates(RateRequest $request)
    {
        $cacheKey =  self::COOLRUNNER_SERVICE_PREFIX . 'smartcheckout_' . strtolower(str_replace(' ', '', explode(PHP_EOL, $request->getDestStreet())[0] . $request->getDestPostcode() . $request->getDestCity()));

        if ($this->_loadRequestFromCache($cacheKey)) {
            $response = $this->_loadRequestFromCache($cacheKey);
        } else {
            $curl = $this->_prepareCurlObject();
            $url = "https://sv0ruqhnd4.execute-api.eu-central-1.amazonaws.com/default/smartcheckout?shop_token=" . $this->smarttoken;
            $params = [
                "receiver_name" => "",
                "receiver_address1" => explode(PHP_EOL, $request->getDestStreet())[0],
                "receiver_address2" => explode(PHP_EOL, $request->getDestStreet())[1] ?? "",
                "receiver_country" => $request->getDestCountryId(),
                "receiver_city" => $request->getDestCity(),
                "receiver_zipcode" => $request->getDestPostcode(),
                "receiver_phone" => "",
                "receiver_email" => "",
                "receiver_company" => "",
                "cart_date" => strtotime(date('d-m-Y')),
                "cart_time" => date('H:i:s'),
                "cart_day" => date("l"),
                "cart_amount" => $request->getPackageQty(),
                "cart_weight" => $request->getPackageWeight()*1000,
                "cart_currency" => $request->getDataByKey('base_subtotal_incl_tax'),
                "cart_subtotal" =>  $request->getDataByKey('base_currency')[0] ?? "DKK",
                "cart_items" => [
                    [
                        "item_name" => "",
                        "item_sku" => "",
                        "item_id" => "",
                        "item_qty" => "",
                        "item_price" => "",
                        "item_weight" => ""
                    ]
                ]
            ];

            $curl->setHeaders([
                'Content-Type' => 'application/json'
            ]);

            $curl->post($url, json_encode($params));
            $response = $curl->getBody();

            $this->_saveRequestToCache($response, $cacheKey);
        }

        return $response;
    }
}
