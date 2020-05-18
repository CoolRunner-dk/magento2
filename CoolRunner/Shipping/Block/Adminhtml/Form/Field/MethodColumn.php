<?php
declare(strict_types=1);

namespace CoolRunner\Shipping\Block\Adminhtml\Form\Field;

use CoolRunner\Shipping\Controller\Adminhtml\Curl\Coolrunner;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Psr\Log\LoggerInterface;

class MethodColumn extends Select
{
    protected $_curl;
    protected $_logger;
    protected $_crCurl;
    protected $carrierInputName;

    public function __construct(Context $context, LoggerInterface $logger)
    {
        $this->_logger = $logger;

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
        $nameExploded = explode('[', str_replace(['groups', ']', 'cr_', '[<%- _id %>[method'], '', $this->getName()));
        $this->setOptions($this->getSourceOptions($nameExploded[1]));

        return parent::_toHtml();
    }

    private function getSourceOptions($carrier)
    {
        $crCurl = new Coolrunner();
        $products = $crCurl->getProductsByCarrier($carrier);
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
