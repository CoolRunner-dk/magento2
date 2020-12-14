<?php
/**
 * Collection
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:36
 */
namespace CoolRunner\Shipping\Model\ResourceModel\Rate;

use CoolRunner\Shipping\Model\ResourceModel\Rate;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Quote\Model\Quote\Address\RateRequest;
use CoolRunner\Shipping\Helper\CurlData as CoolRunnerHelper;

/**
 * Class Collection
 *
 * @package CoolRunner\Shipping
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'coolrunner_shipping_rate_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'coolrunner_shipping_rate_collection';

    /**
     * @var array
     */
    protected $_conditions = [];

    /**
     * @var CoolRunnerHelper
     */
    protected $_helper;

    public function __construct(
        CoolRunnerHelper $helper,
        \CoolRunner\Shipping\Model\Rate\Source\Condition $rateConditions,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_conditions = $rateConditions->toArray();
        $this->_helper = $helper;
    }

    /**
     * @param \CoolRunner\Shipping\Model\Rate\Source\Condition $rateConditions
     */
    protected function _construct()
    {
        $this->_init('CoolRunner\Shipping\Model\Rate', 'CoolRunner\Shipping\Model\ResourceModel\Rate');
    }

    /**
     * @param string $carrierCode
     *
     * @return $this
     */
    public function addCarrierFilter($carrierCode = '')
    {
        $this->addFilter('carrier', $carrierCode);
        return $this;
    }

    /**
     * @param string $countryId
     *
     * @return $this
     */
    public function addCountryFilter($countryId = 'DK')
    {
        $this->addFieldToFilter('dest_country_id', ['finset' => $countryId]);
        return $this;
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->addFieldToFilter('is_active', ['eq' => 1]);
        return $this;
    }

    /**
     * @param RateRequest $request
     *
     * @return Collection
     */
    public function addFilterByRequest(RateRequest $request)
    {
        $this->addFieldToFilter(['store_id', 'store_id'], [['eq' => '0'], ['finset' => $request->getStoreId()]])
            ->addCountryFilter($request->getDestCountryId())
            ->addFieldToFilter('dest_region_id', ['in' => [$request->getDestRegionId(), '', '0']])
            ->addFieldToFilter('dest_zip', ['in' => [$request->getDestPostcode(), '', '*']])
            ->addActiveFilter()
            ->addConditions($this->_conditions,$request);
        return $this;
    }


    public function addFilterForSmartcheckout(RateRequest $request)
    {
        return $this->_helper->getSmartcheckoutRates($request);
    }
    /**
     * @param array       $conditions
     * @param RateRequest $request
     *
     * @return $this
     */
    public function addConditions(array $conditions, RateRequest $request)
    {
        $rateConditions = [];
        $rateSecondConditions = [];

        /** It is a bit confused condition. Just believe.*/
        foreach ($conditions as $conditionName) {
            $value = abs($request->getData($conditionName));
            $rateConditions[] = "(`condition_name` = '" . $conditionName . "' AND `condition_from` <= '" . $value . "' AND `condition_to` > '" . $value . "')";
            $rateSecondConditions[] = "(`second_condition_name` = '" . $conditionName . "' AND `second_condition_from` <= '" . $value . "' AND `second_condition_to` > '" . $value . "')";
        }
        if (!empty($rateConditions)) {
            $this->getSelect()->where(implode(' OR ', $rateConditions));
        }

        if (!empty($rateSecondConditions)) {
            $this->getSelect()->where(implode(' OR ', $rateSecondConditions));
        }

        return $this;
    }
}
