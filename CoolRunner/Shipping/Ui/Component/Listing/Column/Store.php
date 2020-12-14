<?php
/**
 *  Store
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    13:20
 */
namespace CoolRunner\Shipping\Ui\Component\Listing\Column;

/**
 * Class Store
 *
 * @package CoolRunner\Shipping
 */
class Store extends \Magento\Store\Ui\Component\Listing\Column\Store {
    /**
     * @param array $item
     *
     * @return \Magento\Framework\Phrase|string
     */
    protected function prepareItem(array $item) {
        $origStores = '';
        if ($item[$this->storeKey] === '0' || !empty($item[$this->storeKey])) {
            $origStores = $item[$this->storeKey];
        }
        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }
        if (in_array('0', $origStores, true) && count($origStores) === 1) {
            return __('All Store Views');
        }

        return parent::prepareItem($item);
    }
}
