<?php

namespace CoolRunner\Shipping\Model\Config\Source;

class PrintTypes
{
    public function toOptionArray()
    {
        return [
            ['value' => 'LabelPrint', 'label' => 'LabelPrint'],
            ['value' => 'A4', 'label' => 'A4']
        ];
    }
}

