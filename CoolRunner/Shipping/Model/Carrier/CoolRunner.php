<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;
/**
 * Class CoolRunner
 *
 * @package CoolRunner\Shipping
 */
class CoolRunner extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'coolrunner';
    /**
     * @var string
     */
    protected $_carrierTitle = "CoolRunner";
}
