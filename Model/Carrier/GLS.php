<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;
/**
 * Class GLS
 *
 * @package CoolRunner\Shipping
 */
class GLS extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'gls';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR GLS";
}
