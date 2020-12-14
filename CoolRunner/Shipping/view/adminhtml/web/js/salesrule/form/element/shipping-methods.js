/**
 *  shipping-methods
 *
 * @copyright Copyright © 2020 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/multiselect',
    'domReady'
], function ($, MultiSelect,domReady) {
    'use strict';

    return MultiSelect.extend({
        defaults: {
            actionCodeTrigger: 'cr_freeshipping_rates',
            imports: {
                simpleAction: '${ $.provider }:data.simple_action:value',
            }
        },

        /**
         *
         */
        initialize: function () {
            //initialize parent Component
            this._super();
            this._checkMode(this.simpleAction);
            this.on('simpleAction', this.onSimpleActionUpdate.bind(this));
        },

        /**
         *
         * @returns {exports}
         */
        onSimpleActionUpdate: function () {
            this._checkMode(this.simpleAction);
            return this;
        },

        /**
         *
         * @param simpleAction
         * @private
         */
        _checkMode: function (simpleAction) {
            if(simpleAction == this.actionCodeTrigger){
                this.show();
            } else {
                this.hide();
            }
        },
    })
});
