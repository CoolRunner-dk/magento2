<?php
/**
 *  IncrementId
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    06.08.2020
 * Time:    13:42
 */
namespace CoolRunner\Shipping\Ui\Component\Listing\Column;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class IncrementId
 *
 * @package CoolRunner\Shipping
 */
class IncrementId extends Column {

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Tracking constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Helper             $helper
     * @param array              $components
     * @param array              $data
     */
    public function __construct(ContextInterface $context,
                                UiComponentFactory $uiComponentFactory,
                                Helper $helper,
                                array $components = [],
                                array $data = [])
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_helper = $helper;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if($item[$this->getData('name')] != '' && $item['order_id'] != '') {
                    $item[$this->getData('name')] = sprintf('<a href="%s">%s</a>', $this->_helper->getOrderViewUrl($item['order_id']), $item[$this->getData('name')]);
                }
            }
        }

        return $dataSource;
    }
}
