/**
 *  droppoint-service
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define(
    [
        'CoolRunner_Shipping/js/view/checkout/shipping/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'CoolRunner_Shipping/js/view/checkout/shipping/model/droppoint-registry',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, customer, storage, shippingService, droppointRegistry, errorProcessor) {
        'use strict';

        return {
            /**
             * Get nearest droppoints list for specified address
             * @param {Object} quote
             */
            getDroppointList: function (quote, form) {
                shippingService.isLoading(true);
                var cacheKey = quote.shippingAddress().getCacheKey()+(quote.shippingMethod()?quote.shippingMethod().carrier_code:''),
                    cache = droppointRegistry.get(cacheKey),
                    serviceUrl = resourceUrlManager.getUrlForDroppointList(quote);
                //console.log(cacheKey);
                if (cache) {
                    form.setDroppointList(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.get(
                        serviceUrl, false
                    ).done(
                        function (result) {
                            droppointRegistry.set(cacheKey, result);
                            form.setDroppointList(result);
                        }
                    ).fail(
                        function (response) {
                            form.setDroppointList([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
