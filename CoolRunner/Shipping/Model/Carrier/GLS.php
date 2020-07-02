<?php

namespace CoolRunner\Shipping\Model\Carrier;

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

class GLS extends AbstractCarrier implements CarrierInterface
{

    /**

     * Carrier's code
     *
     * @var string
     */

    protected $_code = 'cr_gls';
    protected $_carrierTitle = "GLS";

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

    protected $_logger;

    protected $_rateMethodFactory;
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
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
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
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
        /**
         * Make sure that Shipping method is enabled
         */

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $crMethods = json_decode($this->getConfigData('methods'));

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        foreach ($crMethods as $crMethod) {
            $shippingPrice = $crMethod->price;

            // Handle pricerules from settings
            if (strpos($this->getConfigData('pricerules'), $crMethod->method) !== false) {
                foreach (json_decode($this->getConfigData('pricerules')) as $priceRule) {
                    if ($priceRule->method == $crMethod->method) {
                        $priceChanged = false;

                        // Handle price condition
                        if ($priceRule->condition == 'price') {
                            if ($request->getData()['base_subtotal_incl_tax'] >= $priceRule->condition_from and $request->getData()['base_subtotal_incl_tax'] <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle weight condition
                        if ($priceRule->condition == 'weight') {
                            if (($request->getData()['package_weight'] * 1000) >= $priceRule->condition_from and ($request->getData()['package_weight'] * 1000) <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle postcode condition
                        if ($priceRule->condition == 'postcode') {
                            if (isset($request->getData()['dest_postcode']) and $request->getData()['dest_postcode'] >= $priceRule->condition_from and $request->getData()['dest_postcode'] <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle product amount condition
                        if ($priceRule->condition == 'productamount') {
                            if ($request->getData()['package_qty'] >= $priceRule->condition_from and $request->getData()['package_qty'] <= $priceRule->condition_to and !$priceChanged) {
                                $shippingPrice = $priceRule->price;
                                $priceChanged = true;
                            }
                        }

                        // Handle free shopping from magento
                        if ($request->getData()['free_shipping']) {
                            $shippingPrice = 0;
                        }
                    }
                }
            }

            $explodedMethod = explode('_', strtolower(str_replace(' ', '', $crMethod->method)));

            if (isset($explodedMethod) and $explodedMethod[2] == 'droppoint' or $explodedMethod[2] == 'servicepoint') {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helper = $objectManager->create('CoolRunner\Shipping\Helper\Data');
                $droppoints = $helper->findClosestDroppoints($explodedMethod[0], $request->getDestCountryId(), $request->getDestStreet()[0], $request->getDestPostcode(), $request->getDestCity());
                $droppoint = json_encode($droppoints->servicepoints);
                $shown = 0;
                $maxShow = 3;
                foreach ($droppoints->servicepoints as $servicepoint) {
                    if ($shown >= $maxShow) {
                        break;
                    }

                    $method = $this->_rateMethodFactory->create();

                    $method->setCarrier($this->getCarrierCode());
                    $method->setCarrierTitle($this->_carrierTitle);

                    $method->setMethod($this->getCarrierCode() . '-' . strtolower(str_replace(' ', '', $crMethod->method)) . '_' . $servicepoint->id);
                    $method->setMethodTitle('Pakkeshop: ' . $servicepoint->name . ' (Afstand: ' . $servicepoint->distance . 'm)');
                    $method->setPrice($shippingPrice);
                    $method->setCost($shippingPrice);
                    $result->append($method);

                    $shown++;
                }
            } else {
                $method = $this->_rateMethodFactory->create();
                /**
                 * Set carrier's method data
                 */
                $method->setCarrier($this->getCarrierCode());
                $method->setCarrierTitle($this->_carrierTitle);
                /**
                 * Displayed as shipping method under Carrier
                 */

                $method->setMethod($this->getCarrierCode() . '-' . strtolower(str_replace(' ', '', $crMethod->method)));
                $method->setMethodTitle($droppoint ?? $crMethod->methodname);
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                $result->append($method);
            }
        }

        return $result;
    }
}
