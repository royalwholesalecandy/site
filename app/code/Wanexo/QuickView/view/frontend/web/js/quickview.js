define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($){
	"use strict";
	$.widget('wxo.WanexoQuickView', {
		options: {
			modalOpenClass: 'modal-view',
			modalId: 'quickview'
		},
		_create: function(){
			 this.openModal(this.options);
		 },
		
		openModal: function(opt){
			var quickviewModel = $('#'+opt.modalId);
			quickviewModel.modal({
                buttons: [],
				trigger: '.'+opt.modalOpenClass,
				wrapperClass: 'quick_view_wrapper',
				closed: function(){
					quickviewModel.find('.quickview-content').html('');
				},
				opened: function(){
                    setTimeout(function() {
                        var modalLoader = quickviewModel.find('.quickview-wrapper');
                        var Modalcontent = quickviewModel.find('.quickview-content');
                        modalLoader.show();
                        Modalcontent.hide();
                        var url = $('.products .item').find('a.active').data('href');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            cache:false,
                            success: function(res){
                                Modalcontent.html(res);
                                Modalcontent.show();
                                Modalcontent.trigger('contentUpdated');
                                if(Modalcontent.find('#bundle-slide').length > 0){
                                    var buttonBundleProduct = Modalcontent.find('#bundle-slide');
                                    var btnLink = $('#tab-label-quickview-product-bundle-title');
                                    setTimeout(function(){
                                        buttonBundleProduct.unbind('click').click(function(e){
                                            e.preventDefault();
                                            btnLink.parent().show();
                                            btnLink.click();
                                            return false;
                                        });
                                    },500);
                                }
                            }
                        }).always(function(){modalLoader.hide();});
                    },100);
				}
			});
		}
	});
	return $.wxo.WanexoQuickView;
});
/*
function addMark(id){
    var getButton = document.getElementsByClassName('modal-view');
    for (var i = 0, len = getButton.length; i < len; i++) {
        getButton[i].classList.remove("active");
    }
    var d = document.getElementById(id);
    d.className += " active";
}
*/