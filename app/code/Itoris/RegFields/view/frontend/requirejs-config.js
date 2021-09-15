/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
var config = {
    map: {
        '*': {
            //'itoris_regfields_form' :'Itoris_RegFields/js/form'
        }
    }
    ,
    shim: {
        //"Itoris_RegFields/js/form":["prototype"],
        'prototype/validation':["prototype"],
        'varien/form':["prototype"],
        "Itoris_RegFields/js/main":["prototype"]
    }

};

require(['jquery'], function ($) {
    $.noConflict();
});