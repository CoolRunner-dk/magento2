<?php
 /**
 * Rate
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:36
 */
namespace CoolRunner\Shipping\Api\Data;

/**
 * Interface RateInterface
 * @method $this setSortOrder(int $order)
 * @method $this setTitle(string $title)
 * @method $this setCarrier(string $carrier)
 * @method $this setMethod(string $method)
 * @method $this setService(string $service)
 * @method $this setDestCountryId(string $countryId)
 * @method $this setIsActive(bool $status)
 * @method $this setIsAutoloaded(bool $status)
 * @method $this setPrice(float $price)
 * @method $this setCost(float $price)
 * @method $this setConditionName(string $conditionName)
 * @method $this setConditionFrom(float $from)
 * @method $this setConditionTo(float $to)
 * @method $this setSecondConditionName(string $conditionName)
 * @method $this setSecondConditionFrom(float $from)
 * @method $this setSecondConditionTo(float $to)
 * @method $this setMaxWidth(float $width)
 * @method $this setMaxHeight(float $height)
 * @method $this setMaxLength(float $length)
 * @method string getCarrier()
 * @method string getTitle()
 * @method string getMethod()
 * @method string getService()
 * @method float getPrice()
 * @method float getCost()
 *
 * @package CoolRunner\Shipping
 */
interface RateInterface {

}
