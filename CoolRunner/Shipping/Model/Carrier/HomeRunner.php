<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;
/**
 * Class HomeRunner
 *
 * @package CoolRunner\Shipping
 */
class HomeRunner extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'homerunner';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR Homerunner";
}
