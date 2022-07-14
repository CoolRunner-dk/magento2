<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;
/**
 * Class DHL
 *
 * @package CoolRunner\Shipping
 */
class DHL extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'dhl';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR DHL";
}
