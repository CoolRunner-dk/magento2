<?php

namespace CoolRunner\Shipping\Controller\Index;

use CoolRunner\Shipping\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Config extends Action
{
    protected $helperData;

    public function __construct(Context $context, Data $helperData)
    {
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    public function execute()
    {
        // Might be used later?
    }
}
