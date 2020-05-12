<?php

namespace CoolRunner\Shipping\Model\Config\Source;

class AgreementTypes
{
    public function toOptionArray()
    {
        return [
            ['value' => 'normal', 'label' => 'Eget lager'],
            ['value' => 'pcn', 'label' => 'PakkecenterNORD']
        ];
    }
}

