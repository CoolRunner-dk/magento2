<?php
 /**
 * Product
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  ilya.kush@gmail.com
 * Date:    07.08.2020
 * Time:    14:17
 */
namespace CoolRunner\Shipping\Api\Data;

/**
 * Interface ProductInterface
 * @method string getCarrier() Return carrier code
 * @method string getProducts() Get available methods of carrier. Return json string
 * @method $this setProducts(string $json)
 * @method $this setCarrier(string $code)
 *
 * @package CoolRunner\Shipping
 */
interface ProductInterface {

}
