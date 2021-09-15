define(
    [
        'uiComponent'
    ],
    function(Component, totals) {
        'use strict';
        return Component.extend({
            hasDealer: function(){
                return window.checkoutConfig.amasty && window.checkoutConfig.amasty.perm ? true : false;
            },
            getDealerDescription: function(){
                return this.hasDealer() ? window.checkoutConfig.amasty.perm.dealerDescription : '';
            }
        });
    }
);
