<?php
declare(strict_types=1);

namespace CoolRunner\Shipping\Block\Adminhtml\Form\Field;

use CoolRunner\Shipping\Controller\Adminhtml\Curl\Coolrunner;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class MethodColumn extends Select
{
    protected $_logger;
    protected $_storeManager;

    public function __construct(Context $context, LoggerInterface $logger, StoreManagerInterface $storeManager)
    {
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        // Get name for carrier by replacing rubbish
        $nameExploded = explode('[', str_replace(['groups', ']', 'cr_', '[<%- _id %>[method'], '', $this->getName()));

        // Return carrier to source options
        $this->setOptions($this->getSourceOptions($nameExploded[1]));

        return parent::_toHtml();
    }

    private function getSourceOptions($carrier)
    {
        // Instantiate CoolRunner model
        $storeId = $this->_storeManager->getStore()->getId();
        $crCurl = new Coolrunner($storeId);

        // Get products by carrier and authentication
        $products = $crCurl->getProductsByCarrier($carrier);

        // Instantiate ararys and handle data for array structure
        $optionArray = [];
        $alreadyAdded = [];

        foreach ($products as $product) {
            if (!isset($alreadyAdded[$product['value']])) {
                $optionArray[] = ['value' => $product['value'], 'label' => $product['label']];
                $alreadyAdded[$product['value']] = 1;
            }
        }

        return $optionArray;
    }
}
