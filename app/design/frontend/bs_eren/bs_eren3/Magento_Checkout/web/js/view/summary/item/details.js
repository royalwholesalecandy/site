/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';
    var quoteItemData = window.checkoutConfig.quoteItemData;
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },
        quoteItemData: quoteItemData,
        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        getDescriptionIsspecial: function(quoteItem){
            var item = this.getItem(quoteItem.item_id);
            if(item.isspecial){
                return item.isspecial;
            }

        },
        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if(element.item_id == item_id) {
                    itemElement = element;
                }
            })
            return itemElement;
        }
    });
});
