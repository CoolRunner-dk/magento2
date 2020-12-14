<?php
/**
 *  Price
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    06.08.2020
 * Time:    12:22
 */
namespace CoolRunner\Shipping\Ui\Component\Listing\Column;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;

/**
 * Class Price
 *
 * @package CoolRunner\Shipping
 */
class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * Constructor
     *
     * @param ContextInterface       $context
     * @param UiComponentFactory     $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param Helper                 $helper
     * @param array                  $components
     * @param array                  $data
     * @param Currency               $currency
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        Helper $helper,
        array $components = [],
        array $data = [],
        Currency $currency = null
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->currency = $currency ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(Currency::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['base_currency_code']) ? $item['base_currency_code'] : $this->_helper->getConfigValue('currency/options/default');
                $basePurchaseCurrency = $this->currency->load($currencyCode);
                $item[$this->getData('name')] = $basePurchaseCurrency
                    ->format($item[$this->getData('name')], [], false);
            }
        }

        return $dataSource;
    }
}
