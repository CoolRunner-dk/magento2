/**
 *  requirejs-config
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'CoolRunner_Shipping/js/action/checkout/set-shipping-information-mixin': true
            }
        }
    },
};
