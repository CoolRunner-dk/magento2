<?php
/**
 *  Condition
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    13:32
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class Condition
 *
 * @package CoolRunner\Shipping
 */
class Condition implements OptionSourceInterface {

    /**
     *
     */
    protected const CONDITIONS = [
        'package_value'  => 'Value',
        'package_weight' => 'Weight',
        'package_qty'    => 'QTY',
    ];

    /**
     * @return array
     */
    public function toArray(){
        return array_keys(Self::CONDITIONS);
    }

    /**
     * @return array
     */
    public function toOptionArray(): array {
        $options = [];
        foreach (self::CONDITIONS as $index => $value) {
            $options[] = ['value' => $index, 'label' => __($value)];
        }

        return $options;
    }
}
