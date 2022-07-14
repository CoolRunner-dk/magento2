<?php
/**
 *  AbstractCoolRunnerOnline
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    20.08.2020
 * Time:    16:16
 */
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Api\Data\RateInterface;
use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;
use CoolRunner\Shipping\Model\LabelRepository as LabelRepository;
use CoolRunner\Shipping\Model\ResourceModel\Rate\Collection as RateCollection;
use CoolRunner\Shipping\Model\ResourceModel\Rate\CollectionFactory as RateCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Xml\Security;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\SalesRule\Model\Data\Rule as RuleData;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class AbstractCoolRunnerOnline
 *
 * @package CoolRunner\Shipping
 */
abstract class AbstractCoolRunnerOnline extends AbstractCarrierOnline implements CarrierInterface
{
    const FREE_SHIPPING_RATES       = 'cr_freeshipping_rates';
    const FREE_SHIPPING_RATES_FIELD = 'cr_specific_shipping_method';
    /**
     * @var CoolRunnerHelper
     */
    protected $_helper;
    /**
     * @var LabelRepository
     */
    protected $_labelRepository;
    /**
     * @var RequestInterface
     */
    protected $_labelGenerationRequest;
    /**
     * @var RateCollection
     */
    protected $_rateCollection;
    /**
     * @var CartRepositoryInterface
     */
    protected $_quoteRepository;
    /**
     * @var RuleCollectionFactory
     */
    protected $_ruleCollectionFactory;

    protected $_smartRates;

    /**
     * AbstractCoolRunnerOnline constructor.
     *
     * @param CoolRunnerHelper                                            $helper
     * @param RateCollectionFactory                                       $rateCollectionFactory
     * @param RequestInterface                                            $labelGenerationRequest
     * @param LabelRepository                                             $labelRepository
     * @param CartRepositoryInterface                                     $quoteRepository
     * @param RuleCollectionFactory                                       $ruleCollectionFactory
     * @param ScopeConfigInterface                                        $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param Security                                                    $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory            $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory              $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory        $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory       $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory                      $regionFactory
     * @param \Magento\Directory\Model\CountryFactory                     $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory                    $currencyFactory
     * @param \Magento\Directory\Helper\Data                              $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface        $stockRegistry
     * @param array                                                       $data
     */
    public function __construct(
        CoolRunnerHelper $helper,
        RateCollectionFactory $rateCollectionFactory,
        RequestInterface $labelGenerationRequest,
        LabelRepository $labelRepository,
        CartRepositoryInterface $quoteRepository,
        RuleCollectionFactory $ruleCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $xmlSecurity, $xmlElFactory, $rateFactory, $rateMethodFactory, $trackFactory, $trackErrorFactory, $trackStatusFactory, $regionFactory, $countryFactory, $currencyFactory, $directoryData, $stockRegistry, $data);
        $this->_labelRepository = $labelRepository;
        $this->_labelGenerationRequest = $labelGenerationRequest;
        $this->_rateCollection = $rateCollectionFactory->create();
        $this->_quoteRepository = $quoteRepository;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Lower case without spaces
     *
     * @param RateInterface $rate
     *
     * @return string
     */
    public function _prepareMethodCode($rate)
    {
        $code = $rate->getMethod();
        if ($rate->getService()) {
            $code .= '_' . $rate->getService();
        }
        return strtolower(str_replace(' ', '', $code));
    }

    /**
     * @return string
     */
    public function getCarrierTitle()
    {
        return $this->_carrierTitle;
    }

    /**
     * @return string
     */
    protected function _getCarrierCodeWithoutPrefix()
    {
        return $this->_helper->getCarrierWithoutPrefix($this->_code);
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
        $rates = $this->_rateCollection->addCarrierFilter($this->_getCarrierCodeWithoutPrefix());
        /** @var RateInterface $rate */
        foreach ($rates as $rate) {
            if (!isset($result[$this->_prepareMethodCode($rate)])) {
                $result[$this->_prepareMethodCode($rate)] = sprintf('%s %s', ucfirst($rate->getMethod()), ucfirst($rate->getService()));
            }
        }
        return $result;
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|\Magento\Framework\DataObject|null
     * @throws NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->canCollectRates()) {
            return false;
        }
        $result = $this->_rateFactory->create();

        $rates = $this->_rateCollection
            ->addCarrierFilter($this->_getCarrierCodeWithoutPrefix())
            ->addFilterByRequest($request);

        /** @var RateInterface $rate */
        foreach ($rates as $rate) {
            $shippingPrice = $rate->getPrice();
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            /**
             * Set carrier's method data
             */
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            /**
             * Displayed as shipping method under Carrierv
             */
            $method->setMethod($this->_prepareMethodCode($rate));
            $method->setMethodTitle($rate->getTitle());
            $method->setPrice($shippingPrice);
            $method->setCost($rate->getCost());

            /** don't add rate with negative price. */
            if ($shippingPrice >= 0) {
                $result->append($method);
            }
        }
        return $this->_applyFreeShippingSalesRules($request, $result);
    }

    /**
     * @param RateRequest                         $request
     * @param \Magento\Shipping\Model\Rate\Result $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _applyFreeShippingSalesRules($request, $result)
    {
        $quoteItems = $request->getAllItems();
        if (is_array($quoteItems)) {
            $quoteId = $quoteItems[0]->getQuoteId();
            if ($quoteId) {
                try {
                    $quote = $this->_quoteRepository->get($quoteId);
                } catch (NoSuchEntityException $e) {
                    return $result;
                }

                $appliedRuleIds = $quote->getAppliedRuleIds();
                if ($appliedRuleIds) {
                    $salesRulesCollection = $this->_ruleCollectionFactory->create();
                    $rules                = $salesRulesCollection
                        ->addFieldToFilter(RuleData::KEY_RULE_ID, ['in' => $appliedRuleIds])
                        ->addFieldToFilter(RuleData::KEY_SIMPLE_ACTION, ['eq' => self::FREE_SHIPPING_RATES])
                        ->addFieldToFilter(self::FREE_SHIPPING_RATES_FIELD, ['notnull' => true]);

                    if ($rules->count()) {
                        $discountAmountsArray = [];
                        foreach ($rules as $salesRule) {
                            if (isset($salesRule[self::FREE_SHIPPING_RATES_FIELD]) && $salesRule[self::FREE_SHIPPING_RATES_FIELD]) {
                                $appliedRateCodes = explode(",", $salesRule[self::FREE_SHIPPING_RATES_FIELD]);
                                foreach ($appliedRateCodes as $rate_code) {
                                    if (isset($salesRule[RuleData::KEY_DISCOUNT_AMOUNT]) && $salesRule[RuleData::KEY_DISCOUNT_AMOUNT]) {
                                        if (isset($discountAmountsArray[$rate_code])) {
                                            if ($discountAmountsArray[$rate_code] >= $salesRule[RuleData::KEY_DISCOUNT_AMOUNT]) {
                                                $discountAmountsArray[$rate_code] = $salesRule[RuleData::KEY_DISCOUNT_AMOUNT];
                                            }
                                        } else {
                                            $discountAmountsArray[$rate_code] = $salesRule[RuleData::KEY_DISCOUNT_AMOUNT];
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($result->getAllRates() as $method) {
                            if (in_array($this->__prepareRateToCompare($method), array_keys($discountAmountsArray))) {
                                if (isset($discountAmountsArray[$this->__prepareRateToCompare($method)]) && $discountAmountsArray[$this->__prepareRateToCompare($method)] > 0) {
                                    $method->setPrice(max($method->getPrice()-$discountAmountsArray[$this->__prepareRateToCompare($method)], 0));
                                } else {
                                    $method->setPrice(0);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $result = new \Magento\Framework\DataObject();
        $request->setCarrierCode($this->getCarrierCode());

        try {
            if ($this->_helper->getTypeOfService($request->getStoreId()) == 'pcn') {
                $shipmantData = $this->_labelRepository->prepareShipmentDataByRequest($request, 'pcn');
                $label = $this->_labelRepository->generatePcnLabelByData($shipmantData);
            } else {
                $shipmantData = $this->_labelRepository->prepareShipmentDataByRequest($request, 'normal');
                $label = $this->_labelRepository->generateNormalLabelByData($shipmantData);
            }

            $result->setTrackingNumber($label->getPackageNumber());
            $result->setShippingLabelContent($label->getLabelContent());
        } catch (LocalizedException $e) {
            $result->setErrors($e->getMessage());
        }
        return $result;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        /** @var \Magento\Shipping\Model\Tracking\Result $result */
        $result = $this->_trackFactory->create();

        foreach ($trackings as $tracking) {
            $response = $this->_helper->getTracking($tracking);
            //var_dump($response);

            if (isset($response['package_number'])) {
                /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */
                $tracking = $this->_trackStatusFactory->create();
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getCarrierTitle());
                $tracking->setTracking($response['package_number']);
                $tracking->setUrl($this->_helper->getTrackingUrl($response['package_number']));

                if (isset($response['events']) && is_array($response['events'])) {
                    $summary = '';
                    foreach ($response['events'] as $_event) {
                        $summary .= sprintf(
                            '%s - %s / ',
                            isset($_event['timestamp']) ? $_event['timestamp'] : '',
                            isset($_event['title']) ? $_event['title'] : ''
                        );
                    }
                    $summary .= __('Track:') . ' ' . sprintf('<a target="_blank" href="%s">%s</a>', $tracking->getUrl(), $tracking->getUrl());
                    $tracking->setTrackSummary($summary);
                }
                $result->append($tracking);
            } else {
                /** @var \Magento\Shipping\Model\Tracking\Result\Error $error */
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getCarrierTitle());
                $error->setTracking($tracking);
                $error->setErrorMessage(__('Tracking info is not available at the moment.'));
                $result->append($error);
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return RequestInterface
     */
    public function prepareRequestOfDefaultPackage($order)
    {
        $items = [];
        $packageCustomsValue = 0;
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getWeight()>0) {
                $items[$orderItem->getItemId()] = [
                    'qty'           => $orderItem->getQtyOrdered(),
                    'price'         => $orderItem->getPrice(),
                    'customs_value' => $packageCustomsValue =+ $orderItem->getRowTotal(),
                    'name'          => $orderItem->getName(),
                    'weight'        => $orderItem->getRowWeight(),
                    'product_id'    => $orderItem->getProductId(),
                    'order_item_id' => $orderItem->getItemId(),
                ];
            }
        }

        $package =[
            'params' => [
                'container'          => '',
                'weight'             => $order->getWeight(),
                'customs_value'      => $packageCustomsValue,
                'length'             => $this->getPackageConfig('length'),
                'width'              => $this->getPackageConfig('width'),
                'height'             => $this->getPackageConfig('height'),
                'content_type'       => '',
                'content_type_other' => '',
                'weight_units'       => $this->getPackageConfig('weight_units'),
                'dimension_units'    => $this->getPackageConfig('dimension_units')
            ],
            'items' => $items
        ];

        $requestParams = [
            'packages' => [
                1 => $package,
            ]
        ];
        return $this->_labelGenerationRequest->setParams($requestParams); // <--need function to generate packages
    }

    /**
     * @param Method $method
     *
     * @return string
     */
    protected function __prepareRateToCompare(Method $method)
    {
        return sprintf('%s_%s', $method->getCarrier(), $method->getMethod());
    }
}
