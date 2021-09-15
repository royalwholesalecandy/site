define([
    'jquery',
    'magnificPopup'
], function ($, magnificPopup) {
    "use strict";
    return {
		searchContent: function(searchUrl, currentlyActiveInput, reqLength) {
            if (!searchUrl.length) {
                return false;
            }
            // get the product list according to search charcter
			var Length = $('.prod-name-'+currentlyActiveInput).val().length;
			var string = $('.prod-name-'+currentlyActiveInput).val();
			if(Length>=reqLength){
				$.ajax({
                    url: searchUrl,
					showLoader: true,
                    type: "POST",
					data : 'querystring='+string,
					success: function(res) {
						$('.product-suggestion-'+currentlyActiveInput).css('display','block');
						$('.product-suggestion-'+currentlyActiveInput).empty().html(res.result);
                    }
                });
			}else{
			$('.product-suggestion-'+currentlyActiveInput).css('display','none');	
			}
		},
		productDetails: function(producturl, pid){
			if (!pid.length) {
                return false;
            }
			$.ajax({
				url: producturl,
                type: "POST",
				showLoader: true,
				data : 'pid='+pid,
                success: function(res) {
					$('.product-suggestion-'+currentlyActiveInput).css('display','none');
					$('.product-item-'+currentlyActiveInput).removeClass('active');
					$('.productid-'+currentlyActiveInput).val(res.result.id);
					$('.prod-name-'+currentlyActiveInput).val(res.result.name);
					$('.prod-price-'+currentlyActiveInput).html(res.result.price);
					$('.prod-sku-'+currentlyActiveInput).html(res.result.sku);
					$('.prod-qty-'+currentlyActiveInput).val(res.result.qty);
					var total=res.result.qty*res.result.price;
					$('.prod-total-price-'+currentlyActiveInput).html(total.toFixed(4));
					$('.prod-addtocart-'+currentlyActiveInput).attr('data-url',res.result.addtocartUrl);
					$('.prod-formkey-'+currentlyActiveInput).val(res.result.formkey);
					$('.prod-addtocart-'+currentlyActiveInput).prop("disabled", false);
				}
			}); 
		},
		showpopup: function(productViewUrl){
			if (!productViewUrl.length) {
                return false;
            }
			$.magnificPopup.open({
				items: {
					src: productViewUrl
				},
				type: 'iframe',
				closeOnBgClick: false,
				preloader: true,
				tLoading: '',
				callbacks: {
					open: function() {
						$('.mfp-preloader').css('display', 'block');
					},
					close: function() {
						$('.mfp-preloader').css('display', 'none');
					}
				}
			});
		},
		
		addToCartIndividual: function(cartAddUrl,indexUsed,formDataArr){
			if($('.prod-addtocart-'+indexUsed).attr('data-url') == '') {
				var qty = $('.prod-qty-'+indexUsed).val();
				$.ajax({
					url: cartAddUrl,
					type: "POST",
					showLoader: true,
					data : formDataArr[indexUsed]+'&qty='+qty,
					success: function(res) {
						if(!res.backUrl){
							$('.product-add-message').empty();
							$('.product-add-message').append('Product is added to the cart!!!');
							$('.product-add-message').css('display','block');
							$('.prod-name-'+indexUsed).attr("readonly","true");
							$('.prod-qty-'+indexUsed).attr("readonly","true");
							$('.prod-addtocart-'+indexUsed).css('display','none');
							$('.prod-clear-'+indexUsed).css('display','none');
							$('.prod-remove-'+indexUsed).css('display','block');
							$('.error-message').css('display','none');	
						}else{
							$('.product-add-message').css('display','none');
							$('.product-remove-message').css('display','none');	
							$('.error-message').empty();
							$('.error-message').append('There is issue while adding this product!!');
							$('.error-message').css('display','block');	
						}
					}
				});
			}else{
				var pid = $('.productid-'+indexUsed).val();
				var qty = $('.prod-qty-'+indexUsed).val();
				var configOption=''; var related='';
				var formKey=$('.prod-formkey-'+indexUsed).val();						
				$.ajax({
					url: cartAddUrl,
					type: "POST",
					showLoader: true,
					data : 'product='+pid+'&qty='+qty+'&selected_configurable_option='+configOption+'&related_product='+related+'&form_key='+formKey,
					success: function(res) {
						if(!res.backUrl){
							$('.product-add-message').empty();
							$('.product-add-message').append('Product is added to the cart!!!');
							$('.product-add-message').css('display','block');
							$('.prod-name-'+indexUsed).attr("readonly",true);
							$('.prod-qty-'+indexUsed).attr("readonly",true);
							$('.prod-addtocart-'+indexUsed).css('display','none');
							$('.prod-clear-'+indexUsed).css('display','none');
							$('.prod-remove-'+indexUsed).css('display','block');
							$('.error-message').css('display','none');	
						}else{
							$('.product-add-message').css('display','none');
							$('.product-remove-message').css('display','none');	
							$('.error-message').empty();
							$('.error-message').append('There is issue while adding this product!!');
							$('.error-message').css('display','block');	
						}
					}
				});
			}
		},
		removeProductIndividual:function(productremoveurl,indexUsed){
			var pid = $('.productid-'+indexUsed).val();
			var qty = $('.prod-qty-'+indexUsed).val();
			$.ajax({
				url: productremoveurl,
                type: "POST",
				showLoader: true,
				data : 'pid='+pid+'&qty='+qty,
                success: function(res) {
					if (res.messages) {
						$('[data-placeholder="messages"]').html(res.messages);
					}
					if (res.minicart) {
						$('[data-block="minicart"]').replaceWith(res.minicart);
						$('[data-block="minicart"]').trigger('contentUpdated');
					}
					$('.product-remove-message').empty();
					$('.product-remove-message').append('Product is removed from the cart!!!');
					$('.product-remove-message').css('display','block');
					$('.product-add-message').css('display','none');
					$('.productid-'+indexUsed).val('');
					$('.prod-name-'+indexUsed).val('');
					$('.prod-price-'+indexUsed).html('');
					$('.prod-sku-'+indexUsed).html('');
					$('.prod-qty-'+indexUsed).val('');
					$('.prod-total-price-'+indexUsed).html('');
					$('.bundle-options-'+indexUsed).html('');
					$('.bundle-options-items-qty-'+indexUsed).html('');
					$('.bundle-options-items-'+indexUsed).html('');
					$('.prod-name-'+indexUsed).attr("readonly",false);
					$('.prod-qty-'+indexUsed).attr("readonly",false);
					$('.prod-addtocart-'+indexUsed).css('display','block');
					$('.prod-addtocart-'+indexUsed).attr('disabled','true');
					$('.prod-clear-'+indexUsed).css('display','block');
					$('.prod-remove-'+indexUsed).css('display','none');
					
				}
			}); 
		},
		addAll: function(cartAddUrl,formDataArr){
			$('.product-items-wrapper .product-item').each(function(index){
				if($('.product-item-'+index).length != 0 && $('.productid-'+index).val() != ''){
					if($('.prod-addtocart-'+index).attr('data-url') == '') {
						if($('.prod-qty-'+index).val() != 'undefined'){
							var qty = $('.prod-qty-'+index).val();
							$.ajax({
								url: cartAddUrl,
								type: "POST",
								showLoader: true,
								data : formDataArr[index]+'&qty='+qty,
								showLoader: true,
								success: function(res) {
									if(!res.backUrl){
										$('.product-add-message').empty();
										$('.product-add-message').append('All Products are added to the cart!!');
										$('.product-add-message').css('display','block');
										$('#add-all-items').css('display','none');
										$('#remove-all-items').css('display','block');
										$('.prod-addtocart-'+index).css('display','none');
										$('.prod-clear-'+index).css('display','none');
										$('.prod-remove-'+index).css('display','block');
										$('.prod-name-'+index).attr("readonly",true);
										$('.prod-qty-'+index).attr("readonly",true);
										$('.error-message').css('display','none');	
									}else{
										$('.product-add-message').css('display','none');
										$('.product-remove-message').css('display','none');	
										$('.error-message').empty();
										$('.error-message').append('There is issue while adding this product!!');
										$('.error-message').css('display','block');	
									}
								}
							});
						}
					}else if($('.productid-'+index).val() != ''){
							var pid = $('.productid-'+index).val();
							var qty = $('.prod-qty-'+index).val();
							var configOption=''; var related='';
							var formKey=$('.prod-formkey-'+index).val();						
							$.ajax({
								url: cartAddUrl,
								type: "POST",
								showLoader: true,
								data : 'product='+pid+'&qty='+qty+'&selected_configurable_option='+configOption+'&related_product='+related+'&form_key='+formKey,
								success: function(res) {
									if(!res.backUrl){
										$('.product-add-message').empty();
										$('.product-add-message').append('All Products are added to the cart!!');
										$('.product-add-message').css('display','block');
										$('.product-remove-message').css('display','none');
										$('#add-all-items').css('display','none');
										$('#remove-all-items').css('display','block');
										$('.prod-addtocart-'+index).css('display','none');
										$('.prod-clear-'+index).css('display','none');
										$('.prod-remove-'+index).css('display','block');
										$('.prod-name-'+index).attr("readonly",true);
										$('.prod-qty-'+index).attr("readonly",true);
										$('.error-message').css('display','none');	
									}else{
										$('.product-add-message').css('display','none');
										$('.product-remove-message').css('display','none');	
										$('.error-message').empty();
										$('.error-message').append('There is issue while adding this product!!');
										$('.error-message').css('display','block');	
									}
								}
							});
					}else{
						$('.product-add-message').css('display','none');
						$('.product-remove-message').css('display','none');	
						$('.error-message').empty();
						$('.error-message').append('There is no product for add to cart!!');
						$('.error-message').css('display','block');
					}
				}else{
						$('.product-add-message').css('display','none');
						$('.product-remove-message').css('display','none');	
						$('.error-message').empty();
						$('.error-message').append('There is no product for add to cart!!');
						$('.error-message').css('display','block');
				}
			});	
		},
		removeAll: function(productremoveurl,formDataArr){
			$('.product-item').each(function(index){
				if($('.product-item-'+index).length != 0){
					if($('.productid-'+index).val() != 'undefined'){
						var product = $('.productid-'+index).val();
						var qty = $('.prod-qty-'+index).val();
					}
					$.ajax({
						url: productremoveurl,
						type: "POST",
						showLoader: true,
						data : 'pid='+product+'&qty='+qty,
						success: function(res) {
							if (res.messages) {
								$('[data-placeholder="messages"]').html(res.messages);
							}
							if (res.minicart) {
								$('[data-block="minicart"]').replaceWith(res.minicart);
								$('[data-block="minicart"]').trigger('contentUpdated');
							}
							$('.product-remove-message').empty();
							$('.product-remove-message').append('All Products are removed from the cart!!');
							$('.product-add-message').css('display','none');
							$('.product-remove-message').css('display','block');
							$('.productid-'+index).val('');
							$('.prod-name-'+index).val('');
							$('.prod-price-'+index).html('');
							$('.prod-sku-'+index).html('');
							$('.prod-qty-'+index).val('');
							$('.prod-total-price-'+index).html('');
							$('.bundle-options-'+index).html('');
							$('.bundle-options-items-qty-'+index).html('');
							$('.bundle-options-items-'+index).html('');
							$('.prod-addtocart-'+index).css('display','block');
							$('.prod-addtocart-'+index).attr('disabled','true');
							$('.prod-clear-'+index).css('display','block');
							$('.prod-remove-'+index).css('display','none');
							$('#add-all-items').css('display','block');
							$('#remove-all-items').css('display','none');
							$('.prod-name-'+index).attr("readonly",false);
							$('.prod-qty-'+index).attr("readonly",false);
						}
					});
				}
			});
		},
		getConfigdetails: function(configurl,formDataArr,iframeObject,currentlyActiveInput){
			var optionSelected=[];
			var attrCode=[];
			var attrId=[];
			var loop=0;
			var id = $(iframeObject).contents().find('.price-box').attr('data-product-id');
			if($(iframeObject).contents().find('#product-options-wrapper .swatch-attribute').length){
				$(iframeObject).contents().find('#product-options-wrapper .swatch-attribute').each(function(index){
					loop=loop+1;
					if($(this).attr('option-selected')){
						attrCode.push($(this).attr('attribute-code'));
						attrId.push($(this).attr('attribute-id'));
						optionSelected.push($(this).attr('option-selected'));
					}
				});
				var formData=$(iframeObject).contents().find('#product_addtocart_form').serialize();
				formDataArr[currentlyActiveInput]=formData;
				if((optionSelected.length) != loop){
					$(iframeObject).contents().find('.mwquickorder-error-message').css('color','red');
					$(iframeObject).contents().find('.mwquickorder-error-message').html('Please Specify your options');
				}else{
					$.ajax({
						url: configurl,
						type: "POST",
						data : 'pid='+id+'&attrcode='+attrCode+'&attrId='+attrId+'&optionSelected='+optionSelected+'&type=configurable',
						showLoader: true,
						success: function(res) {
							$('.mfp-close').click();
							$('.product-suggestion-'+currentlyActiveInput).css('display','none');
							$('.productid-'+currentlyActiveInput).val(res.result.id);
							$('.prod-name-'+currentlyActiveInput).val(res.result.name);
							$('.prod-price-'+currentlyActiveInput).html(res.result.price);
							$('.prod-sku-'+currentlyActiveInput).html(res.result.sku);
							$('.prod-qty-'+currentlyActiveInput).val(res.result.qty);
							var total=res.result.qty*res.result.price;
							$('.prod-total-price-'+currentlyActiveInput).html(total.toFixed(4));
							$('.prod-addtocart-'+currentlyActiveInput).prop("disabled", false);
						}
					});
				}
			}else{
			var childSelected = $(iframeObject).contents().find('#product_addtocart_form input[name=selected_configurable_option]').val();
			var formData=$(iframeObject).contents().find('#product_addtocart_form').serialize();
			formDataArr[currentlyActiveInput]=formData;
			if(childSelected == ''){
				$(iframeObject).contents().find('.mwquickorder-error-message').css('color','red');
				$(iframeObject).contents().find('.mwquickorder-error-message').html('Please Specify your options');
			}else{
				$.ajax({
					url: configurl,
					type: "POST",
					data : 'pid='+id+'&childSelected='+childSelected+'&type=configurable',
					showLoader: true,
					success: function(res) {
						$('.mfp-close').click();
						$('.product-suggestion-'+currentlyActiveInput).css('display','none');
						$('.productid-'+currentlyActiveInput).val(res.result.id);
						$('.prod-name-'+currentlyActiveInput).val(res.result.name);
						$('.prod-price-'+currentlyActiveInput).html(res.result.price);
						$('.prod-sku-'+currentlyActiveInput).html(res.result.sku);
						$('.prod-qty-'+currentlyActiveInput).val(res.result.qty);
						var total=res.result.qty*res.result.price;
						$('.prod-total-price-'+currentlyActiveInput).html(total.toFixed(4));
						$('.prod-addtocart-'+currentlyActiveInput).prop("disabled", false);
					}
				});
			}
			}

		},
		getBundledetails: function(configurl,formDataArr,iframeObject,currentlyActiveInput){
			//code for bundle product
			var options=[]; var selection=[]; var items=[]; var qty=[];
			var pid=''; var totalPrice=''; var sku=''; var name='';
			pid = $(iframeObject).contents().find('#bundleSummary .bundle-info .product-details div.price-box').attr('data-product-id');
			totalPrice = $(iframeObject).contents().find('#bundleSummary .bundle-info .product-details span.price-container span.price-wrapper span.price').html().replace(/\$/g, '');
			$(iframeObject).contents().find('#bundleSummary .bundle-summary #bundle-summary .bundle.items li').each(function(index){
				items.push($(this).find('div div').html());
			});
			var formData=$(iframeObject).contents().find('#product_addtocart_form').serialize();
			formDataArr[currentlyActiveInput]=formData;
			if(items == ''){
				$(iframeObject).contents().find('.mwquickorder-error-message').css('color','red');
				$(iframeObject).contents().find('.mwquickorder-error-message').html('Please Specify the qty of products');
			}else{
				$.ajax({
					url: configurl,
					type: "POST",
					showLoader: true,
					data : 'pid='+pid+'&items='+items+'&bundleprice='+totalPrice+'&type=bundle',
					success: function(res) {
						$('.mfp-close').click();
						$('.product-suggestion-'+currentlyActiveInput).css('display','none');
						$('.productid-'+currentlyActiveInput).val(res.result.id);
						$('.prod-name-'+currentlyActiveInput).val(res.result.name);
						$('.prod-price-'+currentlyActiveInput).html(res.result.price);
						$('.prod-sku-'+currentlyActiveInput).html(res.result.sku);
						$('.prod-qty-'+currentlyActiveInput).val(res.result.qty);
						var total=res.result.qty*res.result.price;
						$('.prod-total-price-'+currentlyActiveInput).html(total.toFixed(4));
						$('.bundle-options-'+currentlyActiveInput).css('display','block');
						$('.bundle-options-items-'+currentlyActiveInput).html(res.result.selection);
						$('.bundle-options-items-qty-'+currentlyActiveInput).html(res.result.qtyArr);
						$('.bundle-options-'+currentlyActiveInput).html(res.result.items);
						$('.prod-addtocart-'+currentlyActiveInput).addClass('bundleproduct');
						$('.prod-addtocart-'+currentlyActiveInput).prop("disabled", false);
					} 
				});
			}
		},
		getGroupeddetails: function(configurl,formDataArr,iframeObject,currentlyActiveInput){
			var self = this;
			var products=[];var qty=[];
			//code for grouped product
			if($(iframeObject).contents().find('.grouped #super-product-table')){ 
				$(iframeObject).contents().find('.grouped #super-product-table>tbody').each(function(index){
					if($(this).find('div.control .input-text.qty').val() > 0){
						products.push($(this).find('div.price-box').attr('data-product-id'));
						qty.push($(this).find('div.control .input-text.qty').val());
					}
				});
				var formData=$(iframeObject).contents().find('#product_addtocart_form').serialize();
				formDataArr[currentlyActiveInput]=formData;
				if(products == ''){
					$(iframeObject).contents().find('.mwquickorder-error-message').css('color','red');
					$(iframeObject).contents().find('.mwquickorder-error-message').html('Please Specify the qty of products');
				}else{
					$.ajax({
						url: configurl,
						type: "POST",
						showLoader: true,
						data : 'products='+products+'&qty='+qty+'&type=grouped',
						success: function(res) {
							$('.mfp-close').click();
							$('.product-suggestion-'+currentlyActiveInput).css('display','none');
							$.each(res.result, function(index){
								if($('.product-item-'+currentlyActiveInput).length == 0){
									console.log('in grouped');
									var row = currentlyActiveInput-1;
									self.addNewRow(row); 
								}
								$('.productid-'+currentlyActiveInput).val(res.result[index].id);
								$('.prod-name-'+currentlyActiveInput).val(res.result[index].name);
								$('.prod-price-'+currentlyActiveInput).html(res.result[index].price);
								$('.prod-sku-'+currentlyActiveInput).html(res.result[index].sku);
								$('.prod-qty-'+currentlyActiveInput).val(res.result[index].qty);
								var total=res.result[index].qty*res.result[index].price;
								$('.prod-total-price-'+currentlyActiveInput).html(total.toFixed(4));
								$('.prod-addtocart-'+currentlyActiveInput).attr('data-url',res.result[index].addtocartUrl);
								$('.prod-formkey-'+currentlyActiveInput).val(res.result[index].formkey);
								$('.prod-addtocart-'+currentlyActiveInput).prop("disabled", false);
								
								++currentlyActiveInput;								
							}); 				
						}
					});
				}
			}
		},
		addNewRow: function(totalRows){
			$('.product-items-wrapper .product-item').each(function(index){
				if($(this).hasClass('last')){
					if($(this).find('.productid').val() != '' ){
						$('.error-message').css('display','none');
						$('.product-add-message').css('display','none');
						$('.product-remove-message').css('display','none');
						$('.product-item.row').each(function(index){
							$('.product-item-'+index).removeClass('last');
						});
						++totalRows;
						$('.product-items-wrapper').append('<div class="product-item-'+totalRows+' product-item last row"><input type="hidden" name="productid" class="productid productid-'+totalRows+' " value=""><div class="mwquickorder-name content"><input type="text" name="pname"  class="pname product-items prod-name-'+totalRows+'" data-id = "'+totalRows+'" /><div style="display:none;" class="product-suggestion-'+totalRows+' product-suggestion"></div></div><div class="mwquickorder-sku content product-items prod-sku-'+totalRows+'"></div><div class="mwquickorder-qty content"><input type="text" name="pqty"  class="product-items qty prod-qty-'+totalRows+'"/></div><div class="mwquickorder-price content product-items prod-price-'+totalRows+'"></div><div class="mwquickorder-total-price content product-items prod-total-price-'+totalRows+'"></div><div class="mwquickorder-action content mw-action-'+totalRows+'"><button type="button" name="remove" class="product-actions mwremove prod-remove-'+totalRows+'" style="display:none;">Remove From Cart</button><input type="hidden" name="formkey" class="formkey prod-formkey-'+totalRows+'"/><button type="button" data-url=""  name="addtocart" class="product-actions mwaddtocart prod-addtocart-'+totalRows+'" disabled="disabled">Add To Cart</button>&nbsp;&nbsp;<button type="button" name="remove" class="product-actions mwclear prod-clear-'+totalRows+'">Clear</button> </div><div class="bundle-options-items-qty-'+totalRows+' " style="display:none;"></div><div class="bundle-options-items-'+totalRows+' " style="display:none;"></div><div class="bundle-options-'+totalRows+'" style="display:none;"></div></div>');
					}else{
						$('.product-add-message').css('display','none');
						$('.product-remove-message').css('display','none');
						$('.error-message').empty();
						$('.error-message').append('Row is empty!!');
						$('.error-message').css('display','block');
					}
				}
			});
		}
		
    }		
});