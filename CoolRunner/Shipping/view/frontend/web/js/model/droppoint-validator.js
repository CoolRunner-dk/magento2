/**
 *  droppoint-validator
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define([
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote',
], function ($t, messageList,quote) {
    'use strict';
    return {
        validate: function () {
            let isValid = true; //by default is valid
            let method = quote.shippingMethod();

            if(method != null){
                if(this._isShippingMethodCoolRunner(method.carrier_code) && this._isShippingMethodDroppoint(method.method_code)){

                    if (quote.shippingAddress().extension_attributes == undefined) {
                        /** if extensionAttributes is empty so shipping_coolrunner_pickup_id wasn't saved */
                        isValid = false;
                    }else if(quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id == undefined ){
                        isValid = false
                    }
                    if( quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id != undefined
                        && quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id > 0 ){
                        isValid = true
                    }else{
                        isValid = false;
                    }
                    if (!isValid) {
                        //Scroll to top
                        window.scrollTo({top: 0, behavior: 'smooth'});
                        messageList.addErrorMessage({ message: $t('Please provide correct country, city, postcode and street to see nearest ParcelShop') });
                    }
                }
            } else {
                messageList.addErrorMessage({ message: $t('Please choose a shipping method.') });
                isValid = false;
            }

            return isValid;
        },

        /**
         * Check is shipping methos is CoolRunner
         * @param carrierCode string
         * @returns {boolean}
         * @private
         */
        _isShippingMethodCoolRunner: function(carrierCode){
            return (carrierCode.indexOf('coolrunner') === 0);
        },

        /**
         * Check is shipping method is droppoint
         * @param methodCode
         * @returns {boolean}
         * @private
         */
        _isShippingMethodDroppoint: function(methodCode){
            return ((methodCode.indexOf('droppoint') > 0) || (methodCode.indexOf('servicepoint') > 0))
        }
    }
});
