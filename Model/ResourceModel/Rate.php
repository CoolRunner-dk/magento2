<?php
/**
 * Rate
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:36
 */
namespace CoolRunner\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Class Rate
 *
 * @package CoolRunner\Shipping
 */
class Rate extends AbstractDb {

    /**
     *
     */
	protected function _construct() {
		$this->_init('coolrunner_shipping_rates', 'entity_id');
	}
}
