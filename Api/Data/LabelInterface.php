<?php
/**
 *  LabelInterface
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    07.08.2020
 * Time:    16:56
 */
namespace CoolRunner\Shipping\Api\Data;

/**
 * Interface LabelInterface
 * @method $this setOrderId(int $orderId)
 * @method $this setOrderIncrementId(string $incrementId)
 * @method $this setPackageNumber(string $packageNumber)
 * @method $this setPriceExclTax(float $price)
 * @method $this setPriceInclTax(float $price)
 * @method $this setUniqueId(string $pcnId)
 * @method $this setCarrier(string $code)
 * @method $this setProduct(string $code)
 * @method $this setService(string $code)
 * @method int getOrderId()
 * @method string getPackageNumber()
 * @method string getCreatedAt()
 * @method string getCarrier()
 * @method string getProduct()
 * @method string getService()
 * @method float getPriceExclTax()
 * @method float getPriceInclTax()
 *
 * @package CoolRunner\Shipping
 */
interface LabelInterface {

    /**
     * @return string
     */
    public function getLabelContent();
}
