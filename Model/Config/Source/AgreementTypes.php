<?php
namespace CoolRunner\Shipping\Model\Config\Source;
/**
 * Class AgreementTypes
 *
 * @package CoolRunner\Shipping
 */
class AgreementTypes implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     * @return array
     */
    public function toOptionArray() {
        return [
            ['value' => 'normal', 'label' => __('Eget lager')],
            ['value' => 'pcn', 'label' => __('PakkecenterNORD')]
        ];
    }
}

