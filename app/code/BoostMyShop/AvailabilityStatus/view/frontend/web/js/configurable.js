
define([
    'jquery',
    "prototype",
    "configurable"
], function (jQuery) {
    'use strict';

    window.AvailabilityConfigurable = new Class.create();

    AvailabilityConfigurable.prototype = {

        initialize: function () {

        },

        init: function (availabilities) {
            this.availabilities = availabilities;
            this.simpleProductHidden = jQuery('[name="selected_configurable_option"]');

            jQuery( ".price-box" ).on( "updatePrice", function() {
                var productId = objAvailabilityConfigurable.simpleProductHidden.val();
                if (productId)
                    jQuery('#availability-configurable')[0].innerHTML = objAvailabilityConfigurable.availabilities[productId].message;
            });

        }

    }

});
