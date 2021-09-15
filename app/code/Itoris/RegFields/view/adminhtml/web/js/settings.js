/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */

define([
    'jquery',
    'prototype'
], function (jQuery) {
    if (!window.ItorisHelper) {
        window.ItorisHelper = {};
    }

    window.ItorisHelper.toogleFieldEditMode = function(id, container) {
        $(container).disabled = $(id).checked;
    };

});