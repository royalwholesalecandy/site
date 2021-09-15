/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery',
    'ko',
    'Magento_Checkout/js/model/checkout-data-resolver'
], function ($, ko, checkoutDataResolver) {
    'use strict';

    var shippingRates = ko.observableArray([]);

    return {
        isLoading: ko.observable(false),

        /**
         * Set shipping rates
         *
         * @param {*} ratesData
         */
        setShippingRates: function (ratesData) {
            shippingRates(ratesData);
            shippingRates.valueHasMutated();
            checkoutDataResolver.resolveShippingRates(ratesData);
			$(".table-checkout-shipping-method input[type=radio]").prop("disabled", false);
			$("#checkout textarea").prop("disabled", false);
        },

        /**
         * Get shipping rates
         *
         * @returns {*}
         */
        getShippingRates: function () {
            return shippingRates;
        }
    };
});
