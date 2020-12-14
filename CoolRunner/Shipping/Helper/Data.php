<?php
namespace CoolRunner\Shipping\Helper;

use CoolRunner\Shipping\Model\Carrier\AbstractCoolRunner as CarrierModel;
use Magento\Backend\Model\UrlInterface as BackendUrlBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 *
 * @package CoolRunner\Shipping
 */
class Data extends AbstractHelper {

    const COOLRUNNER_SERVICE_PREFIX = 'coolrunner';

    const XML_PATH_CRSETTINGS = 'cr_settings/';
    const XML_PATH_SHOPINFO = 'general/';

    /**
     * @var BackendUrlBiulder
     */
    protected $_backendUrlBuilder;

    /**
     * Data constructor.
     *
     * @param BackendUrlBuilder $backendUrlBuilder
     * @param Context           $context
     */
    public function __construct(
        BackendUrlBuilder $backendUrlBuilder,
        Context $context) {
        parent::__construct($context);
        $this->_backendUrlBuilder = $backendUrlBuilder;
    }

    /**
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null) {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param string $code
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getStoreInformation($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH_SHOPINFO . 'store_information/' . $code, $storeId);
    }

    /**
     * country_id, region_id, city, postcode, street_line1, street_line2
     *
     * @param string $code
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getShippingOriginInformation($code, $storeId = null) {
        return $this->getConfigValue('shipping/origin/' . $code, $storeId);
    }

    /**
     * @param string $code
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getCredentialsConfig($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'credentials/' . $code, $storeId);
    }

    /**
     * @param string $code
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getAgreementConfig($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'agreement/' . $code, $storeId);
    }

    /**
     * @param string $code
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getPackageConfig($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH_CRSETTINGS . 'package/' . $code, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getPrintFormat($storeId = null){
        return $this->getAgreementConfig('cr_printformat',$storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getTypeOfService($storeId = null){
        return $this->getAgreementConfig('cr_type',$storeId);
    }

    /**
     * @param float $weight
     *
     * @return float
     */
    public function convertWeightToGrams($weight){
        switch ($this->getConfigValue(\Magento\Directory\Helper\Data::XML_PATH_WEIGHT_UNIT)){
            case 'g': return $weight;
            case 'kgs': return $weight * 1000;
            case 'lbs': return $weight * 453.592;
        }
        return $weight;
    }

    /**
     * @param string $packageNumber
     *
     * @return string
     */
    public function getTrackingUrl($packageNumber){
        return sprintf('https://tracking.coolrunner.dk/?shipment=%s',trim($packageNumber));
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public function getOrderViewUrl($orderId){
        return $this->_backendUrlBuilder->getUrl('sales/order/view',['order_id' => $orderId]);
    }

    /**
     * @param OrderModel $order
     *
     * @return bool
     */
    public function isOrderCoolRunner($order){
        return $this->isShippingMethodCoolRunner($order->getShippingMethod());
    }

    /**
     * @param string $shippingMethod
     *
     * @return bool
     */
    public function isShippingMethodCoolRunner($shippingMethod){
        if(strpos($shippingMethod,self::COOLRUNNER_SERVICE_PREFIX) === 0){
            return true;
        }
        return false;
    }

    /**
     * @param string $shippingMethod
     *
     * @return bool
     */
    public function isShippingMethodCoolRunnerDroppoint($shippingMethod){
        if($this->isShippingMethodCoolRunner($shippingMethod)
            && (
            	strpos($shippingMethod,CarrierModel::METHOD_TYPE_CODE_DROPPOINT) >= 0
	            || strpos($shippingMethod,CarrierModel::METHOD_TYPE_CODE_SERVICEPOINT) >= 0
	        )
        ){
            return true;
        }
        return false;
    }

    /**
     * Return Associative array or value if second parameter is given
     *
     * @param string $shippingMethod
     * @param string $code
     *
     * @return array|string
     */
    public function explodeShippingMethod($shippingMethod,$code = ''){
        $result = [
            'prefix' => '',
            'carrier'=> '',
            'service'=>'',
            'product'=>'',
            //'droppoint_id' => ''
        ];

        if($this->isShippingMethodCoolRunner($shippingMethod)){
            $explodedMethod = explode('_', $shippingMethod);
            $result['prefix']  = self::COOLRUNNER_SERVICE_PREFIX;
            $result['carrier'] = str_replace(self::COOLRUNNER_SERVICE_PREFIX,'',$explodedMethod[0]);
            $result['service'] = $explodedMethod[2];
            $result['product'] = $explodedMethod[1];
            //$result['droppoint_id'] = isset($explodedMethod[4])?$explodedMethod[4]:'';
        }

        if($code != '') {
            if(isset($result[$code])){
                return $result[$code];
            } else {
                return '';
            }
        }
        return $result;
    }

    /**
     * @param string $carrierCode
     *
     * @return string
     */
    public function getCarrierWithoutPrefix($carrierCode){
        return str_replace(Self::COOLRUNNER_SERVICE_PREFIX,'',$carrierCode);
    }

    /**
     * @param string $carrierCode
     *
     * @return string
     */
    public function getCarrierWithPrefix($carrierCode){
        if(strpos($carrierCode,self::COOLRUNNER_SERVICE_PREFIX) === false){
            return self::COOLRUNNER_SERVICE_PREFIX.$carrierCode;
        }
        return $carrierCode;
    }

}
