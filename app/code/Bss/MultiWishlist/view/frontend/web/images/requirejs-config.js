/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
	map: {
        '*': {
            'wishlist':'Bss_MultiWishlist/js/overwirte_core_wishlist'
        }
    },
    paths: {
        'bss_fancybox': 'Bss_MultiWishlist/js/jquery.bssfancybox'
    },
    shim: {
        'bss_fancybox': {
            deps: ['jquery']
        }
    }
};
require.config(config);
