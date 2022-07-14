<?php
/**
 *  Rule
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    27.08.2020
 * Time:    22:21
 */
namespace CoolRunner\Shipping\Plugin\SalesRule\Model;

use Magento\SalesRule\Model\Rule as SalesRuleModel;
use CoolRunner\Shipping\Model\Carrier\AbstractCoolRunnerOnline as CarrierModel;
/**
 * Class Rule
 *
 * @package CoolRunner\Shipping
 */
class Rule {

    /**
     * @param SalesRuleModel $subject
     * @param array          $data
     *
     * @return array
     */
    public function beforeLoadPost(SalesRuleModel $subject, array $data)  {
        if(empty($data[CarrierModel::FREE_SHIPPING_RATES_FIELD])){
            unset($data[CarrierModel::FREE_SHIPPING_RATES_FIELD]);
        } elseif(is_array($data[CarrierModel::FREE_SHIPPING_RATES_FIELD])){
            $data[CarrierModel::FREE_SHIPPING_RATES_FIELD] = implode(',',$data[CarrierModel::FREE_SHIPPING_RATES_FIELD]);
        }
        return [$data];
    }
}
