/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiComponent',
    'mage/url',
    'jquery',
    'jquery/ui',
    'MageWorx_OrdersGrid/js/grid/controls',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/grid/filters/filters',
    'Magento_Ui/js/grid/filters/range',
    'Magento_Ui/js/grid/filters/chips',
    'Magento_Ui/js/grid/dnd',
    'Magento_Ui/js/grid/paging/sizes',
    'Magento_Ui/js/grid/controls/bookmarks/storage',
    'Magento_Ui/js/lib/validation/utils',
    'Magento_Ui/js/lib/validation/rules',
    'MageWorx_OrdersGrid/js/grid/cells/comaSeparated',
    'MageWorx_OrdersGrid/js/grid/cells/thumbnails'
], function (_, registry, utils, uiComponent, url, $) {
    'use strict';

    return uiComponent.extend({

        /**
         * Initializes model instance.
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super();
            this.printInvoices();

            return this;
        },

        printInvoices: function () {
            var self = this;
            if (window.location.pathname.search(/\/print_invoices\/1\//i) !== -1) {
                $.ajax(this.print_url, {
                    data: {'check': true},
                    method: 'POST',
                    success: function (data) {
                        if (typeof data.success != 'undefined' && data.success) {
                            var link = document.createElement('a');
                            link.href = self.print_url;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    },
                    error: function (error) {
                        console.log('Error:');
                        console.log(error);
                    }
                });
            }
        }
    });
});
