<?php
namespace CoolRunner\Shipping\Model\Config\Source;
/**
 * Class PrintTypes
 *
 * @package CoolRunner\Shipping
 */
class PrintTypes implements \Magento\Framework\Data\OptionSourceInterface {
    /**
     * @return array
     */
    public function toOptionArray() {
        return [
            ['value' => 'LabelPrint', 'label' => 'LabelPrint'],
            ['value' => 'A4', 'label' => 'A4']
        ];
    }
}

