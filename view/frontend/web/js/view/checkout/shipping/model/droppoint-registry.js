/**
 *  droppoint-registry
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 */
define(
    [],
    function() {
        "use strict";
        var cache = [];
        return {
            get: function(addressKey) {
                if (cache[addressKey]) {
                    return cache[addressKey];
                }
                return false;
            },
            set: function(addressKey, data) {
                cache[addressKey] = data;
            }
        };
    }
);
