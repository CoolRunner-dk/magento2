<?php
/**
 *  Run
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    12:55
 */
namespace CoolRunner\Shipping\Block\Adminhtml\Rate\Edit\LoadForm;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;
/**
 * Class Run
 *
 * @package CoolRunner\Shipping
 */
class RunButton implements ButtonProviderInterface {

    /**
     * @return array
     */
    public function getButtonData(): array {
        return [
            'label' => __('Run'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90
        ];
    }
}
