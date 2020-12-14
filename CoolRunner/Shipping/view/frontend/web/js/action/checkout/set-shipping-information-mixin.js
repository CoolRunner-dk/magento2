/**
 *  set-shipping-information-mixin
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define([
    'mage/utils/wrapper',
    'CoolRunner_Shipping/js/model/droppoint-validator',
], function (wrapper, coolRunnerDroppointValidator) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if (!coolRunnerDroppointValidator.validate()) {
                return [];
            } else {
                return originalAction();
            }
        });
    };
});
