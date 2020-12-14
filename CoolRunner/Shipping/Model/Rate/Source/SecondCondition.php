<?php
/**
 *  SecondCondition
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    28.08.2020
 * Time:    18:11
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

/**
 * Class SecondCondition
 *
 * @package CoolRunner\Shipping
 */
class SecondCondition extends Condition {

    /**
     * @return array
     */
    public function toOptionArray(): array {
        $result = [['value' => '', 'label' => __('None')]];
        return array_merge($result,parent::toOptionArray());
    }
}
