<?php
/**
 * Rate
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:36
 */
namespace CoolRunner\Shipping\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
/**
 *
 *
 * @method \CoolRunner\Shipping\Model\ResourceModel\Rate getResource()
 * @method \CoolRunner\Shipping\Model\ResourceModel\Rate\Collection getCollection()
 */
class Rate extends AbstractModel implements \CoolRunner\Shipping\Api\Data\RateInterface, IdentityInterface {

	const CACHE_TAG = 'coolrunner_shipping_rate';
	protected $_cacheTag = 'coolrunner_shipping_rate';
	protected $_eventPrefix = 'coolrunner_shipping_rate';

    /**
     *
     */
	protected function _construct() {
		$this->_init('CoolRunner\Shipping\Model\ResourceModel\Rate');
	}

	/**
	 * @return array|string[]
	 */
	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
}
