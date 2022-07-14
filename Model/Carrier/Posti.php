<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class Posti
 *
 * @package CoolRunner\Shipping
 */
class Posti extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'posti';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR Posti";
}
