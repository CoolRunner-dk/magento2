<?php
/**
 *  Stores
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    27.08.2020
 * Time:    11:24
 */
namespace CoolRunner\Shipping\Model\Rate\Source;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;
/**
 * Class Stores
 *
 * @package CoolRunner\Shipping
 */
class Stores  extends StoreOptions {

    /**
     * @return array
     */
    public function toOptionArray(): array {
        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = 0;
        return parent::toOptionArray();
    }
}
