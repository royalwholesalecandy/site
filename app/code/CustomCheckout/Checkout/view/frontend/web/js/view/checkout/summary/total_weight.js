define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'CustomCheckout_Checkout/checkout/summary/total_weight'
            },


            isDisplayed: function() {
                //return this.isFullMode() && this.getPureValue() !== 0;
                return true;
            },

            getValue: function() {
                
                var total_weight = 0;
                var Items = quote.getItems();

                 this.result = ko.computed(function () {

                    for(var key in Items) {
                    if (!isNaN(Items[key].row_weight)) {
                           total_weight = parseFloat(Items[key].row_weight) + parseFloat(total_weight);
                        }
                    }

                });

                 return Math.ceil(total_weight);
                 ko.applyBindings();

            }
        });
    }
);
