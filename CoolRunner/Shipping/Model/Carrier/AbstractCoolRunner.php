<?php
/**
 *  AbstractCarrier
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    04.08.2020
 * Time:    10:46
 */
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractCoolRunner
 *
 * @package CoolRunner\Shipping
 */
abstract class AbstractCoolRunner extends AbstractCarrier implements CarrierInterface
{
    const METHOD_TYPE_CODE_DROPPOINT    = 'droppoint';
    const METHOD_TYPE_CODE_SERVICEPOINT = 'servicepoint';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;
    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;
    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;
    /**
     * @var CoolRunnerHelper
     */
    protected $_helper;

    /**
     * @param CoolRunnerHelper     $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory         $rateErrorFactory
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param array                $data
     */

    public function __construct(
        CoolRunnerHelper $helper,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Lower case without spaces
     *
     * @param string $method
     *
     * @return string
     */
    public function _prepareMethodCode($method)
    {
        return strtolower(str_replace(' ', '', $method));
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        $result = [];
        $coolRunnerMethods = json_decode($this->getConfigData('methods'), true);
        if (is_array($coolRunnerMethods) && !empty($coolRunnerMethods)) {
            foreach ($coolRunnerMethods as $coolRunnerMethod) {
                $result[$this->_prepareMethodCode($coolRunnerMethod['method'])] = $coolRunnerMethod['methodname'];
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getCarrierTitle()
    {
        return $this->_carrierTitle;
    }

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        $this->_logger->debug('test collectrates CoolRunner');
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->isActive()) {
            return false;
        }

        $coolRunnerMethods = json_decode($this->getConfigData('methods'));

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        foreach ($coolRunnerMethods as $coolRunnerMethod) {
            $shippingPrice = $coolRunnerMethod->price;

            // Handle pricerules from settings
            if (strpos($this->getConfigData('pricerules'), $coolRunnerMethod->method) !== false) {
                foreach (json_decode($this->getConfigData('pricerules')) as $priceRule) {
                    if ($priceRule->method == $coolRunnerMethod->method) {
                        $priceChanged = false;

                        // Handle price condition
                        if ($priceRule->condition == 'price') {
                            /** need to use getPackageValue(). base_subtotal_incl_tax is not suitable. will be trouble if base currency and current currency is different  */
                            if ($request->getPackageValue() >= $priceRule->condition_from and $request->getPackageValue() <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle weight condition
                        if ($priceRule->condition == 'weight') {
                            /** better use function getPackageWeight() and convert package weight depends on a system WeightUnit */
                            if ($this->_helper->convertWeightToGrams($request->getPackageWeight()) >= $priceRule->condition_from and $this->_helper->convertWeightToGrams($request->getPackageWeight()) <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle postcode condition
                        /** better use function getDestPostcode() */
                        if ($priceRule->condition == 'postcode') {
                            if (!empty($request->getDestPostcode()) and $request->getDestPostcode() >= $priceRule->condition_from and $request->getDestPostcode() <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle product amount condition
                        /** better use function getPackageQty() */
                        if ($priceRule->condition == 'productamount') {
                            if ($request->getPackageQty() >= $priceRule->condition_from and $request->getPackageQty() <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle free shopping from magento
                        /** better use function getFreeShipping() */
                        if ($request->getFreeShipping()) {
                            $shippingPrice = 0;
                        }
                    }
                }
            }

            $method = $this->_rateMethodFactory->create();
            /**
             * Set carrier's method data
             */
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            /**
             * Displayed as shipping method under Carrier
             */
            $method->setMethod($this->_prepareMethodCode($coolRunnerMethod->method));
            $method->setMethodTitle($coolRunnerMethod->methodname);
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            /** don't add rate with negative price. */
            if ($shippingPrice >= 0) {
                $result->append($method);
            }
        }

        return $result;
    }
}
