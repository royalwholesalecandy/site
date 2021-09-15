/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    deps: [
            "js/custom",
          ],
    map: {
        '*': {
            owlcarousel:       'js/owl.carousel',
    	    jstree:      	   'js/jstree.min',
            flexslider:        'js/tm_jquery.flexslider.min',
    	    fancybox:          'js/jquery.fancybox.pack',
            bxslider:          'js/jquery.bxslider.min'
        }
    },
	shim: {
            'flexslider': {
                deps: ['jquery']
            },
        'owlcarousel': {
                deps: ['jquery']
            },
        'bxslider': {
                deps: ['jquery']
            },
	    'fancybox': {
                deps: ['jquery']
            }
    }
};
