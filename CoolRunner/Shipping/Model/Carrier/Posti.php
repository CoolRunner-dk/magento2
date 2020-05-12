<?php
namespace CoolRunner\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Posti extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'posti';

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
