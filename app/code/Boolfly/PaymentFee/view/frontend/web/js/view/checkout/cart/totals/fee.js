define(
    [
        'Boolfly_PaymentFee/js/view/checkout/summary/fee',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component,quote,totals) {
        'use strict';

        return Component.extend({
            totals: quote.getTotals(),

            /**
             * @override
             */
            isDisplayed: function () {
                if(totals.getSegment('fee_amount').value > 0 ){
                    return true;
                }
            }
        });
    }
);