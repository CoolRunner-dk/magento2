<?php
/**
 *  DataProvider
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    10:39
 */
namespace CoolRunner\Shipping\Model\Rate\LoadForm;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
/**
 * Class DataProvider
 *
 * @package CoolRunner\Shipping
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider {
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * DataProvider constructor.
     *
     * @param string             $name
     * @param string             $primaryFieldName
     * @param string             $requestFieldName
     * @param Helper             $helper
     * @param array              $meta
     * @param array              $data
     * @param PoolInterface|null $pool
     */
    public function __construct($name,
                                $primaryFieldName,
                                $requestFieldName,
                                Helper $helper,
                                array $meta = [],
                                array $data = [],
                                PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->_helper = $helper;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(){
        return [ '' => [
            'country_id' => $this->_helper->getConfigValue('general/country/default')
        ]];
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter) {

    }
}
