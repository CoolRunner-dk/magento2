<?php
namespace CoolRunner\Shipping\Model\Carrier;

use CoolRunner\Shipping\Helper\Data as CoolRunnerHelper;
use Magento\Shipping\Model\Carrier\CarrierInterface;
/**
 * Class PostNord
 *
 * @package CoolRunner\Shipping
 */
class PostNord extends AbstractCoolRunnerOnline implements CarrierInterface {

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = CoolRunnerHelper::COOLRUNNER_SERVICE_PREFIX.'postnord';
    /**
     * @var string
     */
    protected $_carrierTitle = "CR PostNord";
}
