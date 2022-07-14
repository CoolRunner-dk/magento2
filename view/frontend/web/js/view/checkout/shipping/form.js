/**
 *  form
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'CoolRunner_Shipping/js/view/checkout/shipping/droppoint-service',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Magento_Ui/js/modal/modal'
], function ($, ko, Component, quote, shippingService, droppointService, $t,shippingSaveProcessor,modal) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'CoolRunner_Shipping/checkout/shipping/form'
        },

        initialize: function (config) {
            this.allowMap = window.coolrunnerMapActivated;
            this.carriersIcon = this.allowMap?window.carriersIcon:undefined;
            this.droppoints = ko.observableArray();
            this.selectedDroppoint = ko.observable();
            this.droppointsModal = undefined;
            this._super();
            if(this.allowMap){
                require(['googlemaps']);
            }
        },

        /**
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super();

            this.isShippingMethodDroppoint = ko.computed(function() {
                let method = quote.shippingMethod();
                //shippingMethod.hasOwnProperty('carrier_code')
                return method != null? (this._isShippingMethodCoolRunner(method.carrier_code) && this._isShippingMethodDroppoint(method.method_code)) : false
            }, this);

            this.showDroppointSelection = ko.computed(function() {
                return this.droppoints().length != 0
            }, this);

            this.showDroppointsMap = ko.computed(function() {
                return this.allowMap;
            }, this);

            quote.shippingMethod.subscribe(function(method) {
                if(method){
                    if(this._isShippingMethodCoolRunner(method.carrier_code) && this._isShippingMethodDroppoint(method.method_code)){
                        this.reloadDroppoints();
                    }
                }
            }, this);

            this.selectedDroppoint.subscribe(function(droppointId) {
                //console.log(quote.shippingAddress().extension_attributes);
                if (quote.shippingAddress().extension_attributes == undefined) {
                    quote.shippingAddress().extension_attributes = {};
                }
                quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id = droppointId;

                /** trigger to reload shipping method and save shipping data to db
                 * Amasty checkout trick
                 *
                 * */
                if(quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id != undefined
                    && quote.shippingAddress().extension_attributes.shipping_coolrunner_pickup_id > 0)
                {
                    // console.log('trigger works');
                    shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType())
                }
                // console.log(quote.shippingAddress().extension_attributes);
            });
            return this;
        },

        /**
         *2
         * @param list
         */
        setDroppointList: function(list) {
            this.droppoints(list);
        },

        /**
         *
         */
        reloadDroppoints: function() {
            droppointService.getDroppointList(quote, this);
            let defaultDroppoint = this.droppoints()[0];
            if (defaultDroppoint) {
                this.selectedDroppoint(defaultDroppoint);
            }
        },

        /**
         *
         * @returns {*}
         */
        getDroppoint: function() {
            var droppoint;
            if (this.selectedDroppoint()) {
                for (var i in this.droppoints()) {
                    var m = this.droppoints()[i];
                    if (m.id == this.selectedDroppoint()) {
                        droppoint = m;
                    }
                }
            }
            else {
                droppoint = this.droppoints()[0];
            }
            return droppoint;
        },

        initSelector: function() {
            let startDroppoint = this.getDroppoint();
        },

        /**
         * Show droppoints on google map
         */
        showOnMap: function () {

            if(!this.allowMap) return false;
            let self = this;
            let initDroppoint = self.getDroppoint();

            let mapOptions = {
                zoom: 13,
                center: new google.maps.LatLng(initDroppoint.latitude, initDroppoint.longitude),
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                draggable: true,
                zoomControl: true,
                scrollwheel: true,
                disableDoubleClickZoom: true,
                keyboardShortcuts: false,
                streetViewControl: false
            };
            let modalPopUpOptions = {
                autoOpen: true,
                type: 'popup',
                modalClass: 'coolrunner-droppoints-modal',
                responsive: true,
                innerScroll: true,
                title: $t('Choose ParcelShop'),
                buttons: [{
                    text: $t('Continue'),
                    class: '',
                    // /**
                    //  * Default action on button click
                    //  */
                    // click: function (event) {
                    //     this.closeModal(event);
                    // }
                }]
            };

            let map = new google.maps.Map($('#coolrunner-droppoints-map')[0], mapOptions);
            let geocoder = new google.maps.Geocoder();

            if(self.droppointsModal == undefined){
                self.droppointsModal = modal(modalPopUpOptions, $('#coolrunner-droppoints-popup-map'));
                $('#coolrunner-droppoints-map').css('min-height', '400px');
            } else {
                self.droppointsModal.openModal();
            }

            if (self.droppoints().length > 1) {
                let markers = [];
                for (let i in self.droppoints()) {
                    let shop = self.droppoints()[i];
                    if (shop.coordinates) {
                        let marker = self.placeMarkers(shop, map, markers);
                        markers.push(marker);
                        if(shop.id == initDroppoint.id){
                            self.toggleMarkerBounce(markers, marker, map);
                        }
                    } else {
                        self.reverseGeocode(shop, geocoder, map, markers);
                    }
                }
            }

            self.showActiveMarkerInfo(initDroppoint);
        },

        /**
         *
         * @param shippingMethod
         * @returns {string|{scaledSize: google.maps.Size, origin: (Ext.lib.Point|Ext.lib.Point), anchor: (Ext.lib.Point|Ext.lib.Point), url: *}}
         * @private
         */
        _getMarkerIcon: function(shippingMethod) {
            if (shippingMethod.carrier_code == undefined || self.carriersIcon[shippingMethod.carrier_code] == undefined ){
                return ''
            }
            return {
                url: self.carriersIcon[shippingMethod.carrier_code],
                scaledSize: new google.maps.Size(48, 48),
                origin: new google.maps.Point(0,0),
                anchor: new google.maps.Point(16,16),
            };
        },

        /**
         *
         * @param droppoint
         * @param resultsMap
         * @param markers
         * @returns {google.maps.Marker}
         */
        placeMarkers: function (droppoint, resultsMap, markers) {

            let id = droppoint.id,
                latitude = droppoint.latitude,
                longitude = droppoint.longitude,
                shopname = droppoint.name,
                address = droppoint.address_location;

            let self = this;
            let marker = new google.maps.Marker({
                map: resultsMap,
                position: new google.maps.LatLng(latitude, longitude),
                title:         shopname,
                droppoint_id:  id,
                opening_hours: droppoint.opening_hours,
                address:       address,
                icon:          self._getMarkerIcon(quote.shippingMethod()),
            });
            let contentString = '<div id="content"><strong>' + shopname + '</strong><br/>' + address + '</div>';
            let infowindow = new google.maps.InfoWindow({
                content: contentString
            });
            marker.infowindow = infowindow;
            marker.addListener('click', function () {
                infowindow.open(resultsMap, marker);
                self.toggleMarkerBounce(markers, this, resultsMap);
                self.showActiveMarkerInfo(droppoint);
                self.changeSelect(id);
            });
            return marker;
        },

        /**
         *
         * @param droppoint
         * @param geocoder
         * @param resultsMap
         * @param markers
         */
        reverseGeocode: function (droppoint, geocoder, resultsMap, markers) {
            let self = this,
                id = droppoint.id,
                address = droppoint.street + ',' + droppoint.zip_code + ',' + droppoint.city,
                shopname = droppoint.name;
            geocoder.geocode({
                'address': address
            }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    resultsMap.setCenter(results[0].geometry.location);
                    let marker = new google.maps.Marker({
                        map: resultsMap,
                        title: shopname,
                        icon:  self._getMarkerIcon(quote.shippingMethod()),
                        position: results[0].geometry.location
                    });
                    markers.push(marker);
                    let contentString = '<div id="content"><strong>' + shopname + '</strong><br/>' + address + '</div>';
                    let infowindow = new google.maps.InfoWindow({
                        content: contentString
                    });
                    marker.addListener('click', function () {
                        infowindow.open(resultsMap, marker);
                        self.toggleMarkerBounce(markers, this, resultsMap);
                        self.showActiveMarkerInfo(droppoint);
                        self.changeSelect(id);
                    });
                }
            });
        },

        /**
         *
         * @param resultsMap
         * @param markers
         */
        mapUpdateZoom: function (resultsMap, markers) {
            let bounds = new google.maps.LatLngBounds();
            for (let i = 0; i < markers.length; i++) {
                bounds.extend(markers[i].getPosition());
            }
            resultsMap.fitBounds(bounds);
        },

        /**
         *
         * @param markers
         * @param marker
         * @param map
         */
        toggleMarkerBounce: function (markers, marker, map) {
            let x = 0;
            while (x < markers.length) {
                markers[x].setAnimation(null);
                markers[x].infowindow.close();
                x++;
            }
            marker.setAnimation(google.maps.Animation.BOUNCE);
            marker.infowindow.open(map, marker);
        },

        /**
         *
         * @param droppoint
         */
        showActiveMarkerInfo: function (droppoint) {
            let modalFooter = $('.coolrunner-droppoints-modal.modal-popup.modal-slide .modal-footer');
            if (!modalFooter.find('.droppoint-info').length) {
                modalFooter.prepend('<div class="droppoint-info"></div>');
            }
            let droppointInfo = $('.coolrunner-droppoints-modal.modal-popup.modal-slide .modal-footer .droppoint-info');
            droppointInfo.empty();
            droppointInfo.append('<p><strong>' + $t('Choosen ParcelShop') + ':</strong> <span class="name">' + droppoint.name + '</span> - <span class="street">' + droppoint.address_location + '</span></p>');
            droppointInfo.append(droppoint.formatted_opening_hours);
        },

        /**
         *
         * @param id
         */
        changeSelect: function (id) {
            this.selectedDroppoint(id);
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
        },
    });
});
