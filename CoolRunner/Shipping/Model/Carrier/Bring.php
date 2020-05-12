<?php
namespace CoolRunner\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Bring extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'bring';

    /**
     * @inheritDoc
     */
    public function collectRates(RateRequest $request)
    {
        // TODO: Implement collectRates() method.
        if (!$this->getConfigFlag('active')) {
            return false;
        }

    }

    /**
     * @inheritDoc
     */
    public function getAllowedMethods()
    {
        // TODO: Implement getAllowedMethods() method.
    }
}