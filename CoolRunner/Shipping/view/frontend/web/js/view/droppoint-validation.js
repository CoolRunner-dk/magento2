/**
 *  droppoint-validation
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'CoolRunner_Shipping/js/model/droppoint-validator'
    ],
    function (Component, additionalValidators, droppointValidator) {
        'use strict';
        additionalValidators.registerValidator(droppointValidator);
        return Component.extend({});
    }
);
