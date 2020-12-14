<?php
namespace CoolRunner\Shipping\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;
use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
/**
 * Class Bring
 *
 * @package CoolRunner\Shipping
 */
//class Bring extends AbstractCoolRunner implements CarrierInterface {
class Bring extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code =  CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'bring';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR Bring";
}
