<?php
/**
 *  BackButton
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    12:50
 */
namespace CoolRunner\Shipping\Block\Adminhtml\Rate\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 *
 * @package CoolRunner\Shipping
 */
class BackButton implements ButtonProviderInterface {
    /**
     * @var Context
     */
    protected $context;

    /**
     * BackButton constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context){
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData() {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl() {
        return $this->getUrl('*/*/');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = []) {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
