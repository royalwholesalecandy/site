/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/adminhtml/grid'
], function (jQuery) {
    'use strict';

    return function (config) {
        var selectedProducts = config.selectedProducts,
            selectedProductsPrice = config.selectedProductsPrice,
            products = $H(selectedProducts),
            productsPrice = $H(selectedProductsPrice),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000,
            fieldId = config.fieldId,
            fieldPriceId = config.fieldPriceId;

        $(fieldId).value = Object.toJSON(products);
        $(fieldPriceId).value = Object.toJSON(productsPrice);

        /**
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerProducts(grid, element, checked) {
            if (checked) {
                if (element.value !== 'on') {
                    products.set(element.value, element.value);
                    productsPrice.set(
                        element.value, {
                            'price': jQuery('input[name="custom_price-' + element.value + '"]').val(),
                            'special_price': jQuery('input[name="custom_special_price-' + element.value + '"]').val()
                        });
                }
            } else {
                if (element.value !== 'on') {
                    products.unset(element.value);
                    productsPrice.unset(element.value);
                }
            }
            $(fieldId).value = Object.toJSON(products);
            $(fieldPriceId).value = Object.toJSON(productsPrice);
            var selectedIds = [];
            var selectedCounter = 0;
            products.each(function (item) {
                if (typeof item.value !== 'function') {
                    selectedIds.push(item.key);
                    selectedCounter += 1;
                }
            });

            jQuery('#customerprices_grid_product_price_massaction-count')
                .find(("strong[data-role='counter']"))
                .html(selectedCounter);
            grid.reloadParams = {
                'select_products[]': selectedIds
            };
        }

        /**
         *
         * @param {Object} grid
         * @param {String} event
         */
        function productRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        gridJsObject.checkboxCheckCallback = registerProducts;
        gridJsObject.initRowCallback = productsRowInit;
        gridJsObject.rowClickCallback = productRowClick;

        /**
         *
         * @param {Object} grid
         * @param {String} row
         */
        function productsRowInit(grid, row) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                position = $(row).getElementsByClassName('input-text')[0];

            jQuery(".custom_price").on('change', function () {
                if (checkbox.checked && checkbox.value !== 'on') {
                    productsPrice.set(
                        checkbox.value, {
                            'price': jQuery('input[name="custom_price-' + checkbox.value + '"]').val(),
                            'special_price': jQuery('input[name="custom_special_price-' + checkbox.value + '"]').val()
                        });

                    $(fieldPriceId).value = Object.toJSON(productsPrice);
                }
            });

            jQuery(".custom_special_price").on('change', function () {
                if (checkbox.checked && checkbox.value !== 'on') {
                    productsPrice.set(
                        checkbox.value, {
                            'price': jQuery('input[name="custom_price-' + checkbox.value + '"]').val(),
                            'special_price': jQuery('input[name="custom_special_price-' + checkbox.value + '"]').val()
                        });

                    $(fieldPriceId).value = Object.toJSON(productsPrice);
                }

            });

            if (checkbox && position) {
                checkbox.positionElement = position;
                position.checkboxElement = checkbox;
                position.tabIndex = tabIndex++;
            }
        }

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                productsRowInit(gridJsObject, row);
            });
        }
    };
});
