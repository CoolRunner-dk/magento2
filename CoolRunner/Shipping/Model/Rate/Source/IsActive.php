<?php
/**
 *  IsActive
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    12:42
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface {

    protected const ENABLED = 1;
    protected const DISABLED = 0;

    /**
     * @return array
     */
    protected static function getAvailableStatuses(): array {
        return [
            self::DISABLED => 'Disabled',
            self::ENABLED => 'Enabled'
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach (self::getAvailableStatuses() as $index => $value) {
            $options[] = ['value' => $index, 'label' => __($value)];
        }

        return $options;
    }

}
