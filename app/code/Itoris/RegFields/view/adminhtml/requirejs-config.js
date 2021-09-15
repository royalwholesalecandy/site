/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */

var config = {
    map: {
        '*': {
            'itoris_regfields_settings'  : 'Itoris_RegFields/js/settings',
            'itoris_regfields_manager'  : 'Itoris_RegFields/js/field_manager'
        }
    }
    ,
    shim: {

        "Itoris_RegFields/js/main":["prototype"]

    }

};