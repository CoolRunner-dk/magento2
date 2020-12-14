<?php
/**
 *  ValueProviderPlugin
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    27.08.2020
 * Time:    22:26
 */
namespace CoolRunner\Shipping\Plugin\SalesRule\Model\Rule\Metadata;

use CoolRunner\Shipping\Model\Carrier\AbstractCoolRunnerOnline as CarrierModel;
use Magento\Shipping\Model\CarrierFactory;
/**
 * Class ValueProviderPlugin
 *
 * @package CoolRunner\Shipping
 */
class ValueProviderPlugin {
    /**
     * @var \CoolRunner\Shipping\Model\Rate\Source\Carrier
     */
    protected $_carrierList;
    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * ValueProviderPlugin constructor.
     *
     * @param \CoolRunner\Shipping\Model\Rate\Source\Carrier $carrierList
     * @param CarrierFactory                                 $carrierFactory
     */
    public function __construct(
        \CoolRunner\Shipping\Model\Rate\Source\Carrier $carrierList,
        CarrierFactory $carrierFactory
    ) {
        $this->_carrierList = $carrierList;
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param                                                      $result
     *
     * @return mixed
     */
    public function afterGetMetadataValues(\Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject, $result) {
        /* adds extra action option */
        $carrierOptions = [['label' => __('Free shipping on specific CoolRunner freight rates'), 'value' => CarrierModel::FREE_SHIPPING_RATES]];
        $actionsOptions = $result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'] = array_merge($actionsOptions, $carrierOptions);

        /*adds rates like options*/
        //$result['actions']['children'][CarrierModel::FREE_SHIPPING_RATES_FIELD]['arguments']['data']['config']['options'] = $this->carrierModel->getAllowedMethods(true);
        $ratesOptionsArray = [];
        foreach ($this->_carrierList->toArray(true) as $_carrierCode => $_carrierTitle) {
            $carrierMethods = $this->_carrierFactory->create($_carrierCode)->getAllowedMethods();
            $carrierMethodsOptions = [];
            if(!empty($carrierMethods) && is_array($carrierMethods)){
                $_collectMethods = [];
                foreach ($carrierMethods as $carrierMethod => $carrierMethodTitle){
                    $_collectMethods[] = [
                        'label' => $carrierMethodTitle,
                        'value' => $_carrierCode.'_'.$carrierMethod
                    ];
                }
                $carrierMethodsOptions[] = [
                    'label' => $_carrierTitle,
                    'value' => $_collectMethods
                ];
            }
            $ratesOptionsArray = array_merge($ratesOptionsArray,$carrierMethodsOptions);
        }
        $result['actions']['children'][CarrierModel::FREE_SHIPPING_RATES_FIELD]['arguments']['data']['config']['options'] = $ratesOptionsArray;
        return $result;
    }
}
