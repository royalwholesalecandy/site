/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Boolfly_PaymentFee/js/action/checkout/cart/totals'
    ],
    function($, ko ,quote, totals) {
        'use strict';
        var isLoading = ko.observable(false);

        return function (paymentMethod) {
            setTimeout(function(){ 
                
                $(".payment-method-text").remove();
                if(paymentMethod.method == 'pmclain_authorizenetcim'){
                    $(".payment-method-content").prepend('<span class="payment-method-text" style="color: #ba0400; margin-bottom: 10px; display: block;">You have selected Credit Card as payment method, there will be a 2.5% Merchant Fee assessed to your order.</span>');
                }
                quote.paymentMethod(paymentMethod);
                totals(isLoading, paymentMethod['method']);
        
            }, 3000);
        }
    }
);