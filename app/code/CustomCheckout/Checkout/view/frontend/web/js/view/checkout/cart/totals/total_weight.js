define(
    [
        'CustomCheckout_Checkout/js/view/checkout/summary/total_weight'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);
