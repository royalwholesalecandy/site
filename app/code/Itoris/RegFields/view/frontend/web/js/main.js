/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */

if (!window.ItorisHelper) {
    window.ItorisHelper = {};
}

window.ItorisHelper.reloadCaptcha = function(id, captchaType){
	$(id).src = $(id).getAttribute('url') + 'captcha/'+captchaType+'?' + Math.random();
};

window.ItorisHelper.validateForm = function(formId) {
	var validator = new Validation(formId);
	if (validator.validate() && !jQuery('.mage-error:visible')[0]) {
		$(formId).submit();
	}
};

window.ItorisHelper.validateElements = function(elms, valid) {
	var elmValid = true;
	for (var i = 0; i < elms.length; i++) {
		if (elms[i]) {
			elmValid = Validation.validate(elms[i]);
			valid = !valid ? valid : elmValid;
		}
	}
	return valid;
};

window.ItorisHelper.showFileInput = function(inputId, confirmMessage, isRequired) {
	if (confirm(confirmMessage)) {
		var siblings = $('itoris_file_' + inputId).previousSiblings();
		for (var i = 0; i < siblings.length; i++) {
			if (siblings[i] && (siblings[i].hasClassName('link-file') || siblings[i].hasClassName('link-wishlist'))) {
				siblings[i].remove();
			}
		}
		if (isRequired && !$('itoris_file_' + inputId).hasClassName('required-file')) {
			$('itoris_file_' + inputId).addClassName('required-file');
		}
		$('itoris_file_value_' + inputId).value = 'null';
		$('itoris_file_' + inputId).show();
	}
};

window.ItorisHelper.updateZip = function() {
    window.ItorisHelper._updateZip('');
}

window.ItorisHelper._updateZip = function(prefix) {
	if ($(prefix + 'postcode')) {
		if (window.countriesWithOptionalZip.indexOf($(prefix + 'country_id').value) != -1) {
			if ($(prefix + 'postcode').hasClassName('required-entry')) {
				//$(prefix + 'postcode').removeClassName('required-entry');
			}
			if ($$('#' + prefix + 'postcode_box label em')[0]) {
				$$('#' + prefix + 'postcode_box label em')[0].hide();
			}
		} else {
			if (!$(prefix + 'postcode').hasClassName('required-entry')) {
				//$(prefix + 'postcode').addClassName('required-entry');
			}
			if ($$('#' + prefix + 'postcode_box label em')[0]) {
				//$$('#' + prefix + 'postcode_box label em')[0].show();
			}
		}
		if ($(prefix + 'country_id').value == 'US') {
			if (!$(prefix + 'postcode').hasClassName('validate-zip')) {
				$(prefix + 'postcode').addClassName('validate-zip');
			}
		} else {
			if ($(prefix + 'postcode').hasClassName('validate-zip')) {
				$(prefix + 'postcode').removeClassName('validate-zip');
			}
		}
	}
}
Validation.add('validate-name', 'Enter valid name. For example O\'Brien.', function (v) {
	return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z-\s']+$/.test(v)
});

Validation.add('validate-money', 'Please enter a valid money format. For example 0.84 or 1,234,567.89.', function (v) {
	return Validation.get('IsEmpty').test(v) ||  /^([0-9]*|([0-9]{0,3},[0-9]{3})*)(\.[0-9]{2})?$/.test(v)
});

Validation.add('validate-phone-number', 'Please enter a valid phone number. Please use numbers (0-9) in this field. Spaces and .-+() characters are allowed.', function (v) {
	return Validation.get('IsEmpty').test(v) ||  /^[0-9-\s\.\(\)\/\+]+$/.test(v)
});