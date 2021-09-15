var config = {
    map: {
        '*': {
			magnificPopup : 'Magewares_MWQuickOrder/js/mp.min',
            mwQuickorder : 'Magewares_MWQuickOrder/js/mwquickorder'
        }
    },
    shim: {
        mwQuickorder: {
            deps: ['jquery']
        }
    }
};