<?php
/**
 *  DataProvider
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    21:55
 */
namespace CoolRunner\Shipping\Model\Rate;

use CoolRunner\Shipping\Model\ResourceModel\Rate\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 *
 * @package CoolRunner\Shipping\Model\Rate
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider {
    /**
     * @var DataPersistorInterface
     */
    protected $_dataPersistor;
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * DataProvider constructor.
     *
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param CollectionFactory      $rateCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array                  $meta
     * @param array                  $data
     * @param PoolInterface|null     $pool
     */
    public function __construct($name,
                                   $primaryFieldName,
                                   $requestFieldName,
                                   CollectionFactory $rateCollectionFactory,
                                   DataPersistorInterface $dataPersistor,
                                   array $meta = [],
                                   array $data = [],
                                   PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->_dataPersistor = $dataPersistor;
        $this->collection = $rateCollectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData() {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        /** @var \Magento\Cms\Model\Block $block */
        foreach ($items as $rate) {
            $this->loadedData[$rate->getId()] = $rate->getData();
        }

        $data = $this->_dataPersistor->get('coolrunner_shipping_rate');
        if (!empty($data)) {
            $rate = $this->collection->getNewEmptyItem();
            $rate->setData($data);
            $this->loadedData[$rate->getId()] = $rate->getData();
            $this->_dataPersistor->clear('coolrunner_shipping_rate');
        }

        return $this->loadedData;
    }
}
