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
        $data = [
            'cr_username' => $this->helperData->getCredentialsConfig('cr_username', 1),
            'cr_token' => $this->helperData->getCredentialsConfig('cr_token', 1),
            'cr_activation' => $this->helperData->getActivationConfig('enable', 1)
        ];

        print_r($data);
        exit();
    }
}
