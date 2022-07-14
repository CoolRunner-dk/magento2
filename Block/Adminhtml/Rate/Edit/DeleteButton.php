<?php
/**
 *  DeleteButton
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    26.08.2020
 * Time:    21:47
 */
namespace CoolRunner\Shipping\Block\Adminhtml\Rate\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
/**
 * Class DeleteButton
 *
 * @package CoolRunner\Shipping
 */
class DeleteButton implements ButtonProviderInterface{
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
     * Return rate ID
     *
     * @return int|null
     */
    public function getRateId() {
        try {
            return $this->context->getRequest()->getParam('id');
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * @return array
     */
    public function getButtonData() {
        $data = [];
        if ($this->getRateId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * URL to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', ['id' => $this->getRateId()]);
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
