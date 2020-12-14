/**
 *  resource-url-manager
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define(
    [
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ],
    function(customer, quote, urlBuilder, utils) {
        "use strict";
        return {
            getUrlForDroppointList: function(quote, limit) {
                var params = {
                    carrier: quote.shippingMethod()['carrier_code']?quote.shippingMethod()['carrier_code']:'',
                    countryCode: quote.shippingAddress().countryId?quote.shippingAddress().countryId:'none',
                    postCode: quote.shippingAddress().postcode?quote.shippingAddress().postcode:'none',
                    city: quote.shippingAddress().city?quote.shippingAddress().city:'none',
                    street: quote.shippingAddress().street?quote.shippingAddress().street:'none'
                };
                var urls = {
                    'default': '/module/get-droppoint-list/:carrier/:countryCode/:postCode/:city/:street'
                };
                return this.getUrl(urls, params);
            },

            /** Get url for service */
            getUrl: function(urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }
                return urlBuilder.createUrl(url, urlParams);
            },

            getCheckoutMethod: function() {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            }
        };
    }
);
