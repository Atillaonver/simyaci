var onOrder=0;


var ode = {
	'login' : function (){
		$.ajax({
        url: 'index.php?route=checkout/login',
        dataType: 'html',
        success: function(html) {
           $('#collapse-checkout-option .panel-body').html(html);
			$('#collapse-checkout-option').parent().find('.panel-heading .panel-title').html('<?php echo $text_checkout_option; ?>');
			
        }
    });
	},
	
	'register' : function (status){
		
		 $.ajax({
	        url: 'index.php?route=checkout/register/save',
	        type: 'post',
	        data: $('#collapse-payment-address input[type=\'text\'], #collapse-payment-address input[type=\'date\'], #collapse-payment-address input[type=\'datetime-local\'], #collapse-payment-address input[type=\'time\'], #collapse-payment-address input[type=\'password\'], #collapse-payment-address input[type=\'hidden\'], #collapse-payment-address input[type=\'checkbox\']:checked, #collapse-payment-address input[type=\'radio\']:checked, #collapse-payment-address textarea, #collapse-payment-address select'),
	        dataType: 'json',
	        success: function(json) {
	            $('.alert, .text-danger').remove();
	            $('.form-group').removeClass('has-error');

	            if (json['redirect']) {
	                location = json['redirect'];
	            } else if (json['error']) {
	                if (json['error']['warning']) {
	                    $('#collapse-payment-address .panel-body').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	                }

					for (i in json['error']) {
						var element = $('#input-payment-' + i.replace('_', '-'));

						if ($(element).parent().hasClass('input-group')) {
							$(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
						} else {
							$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
						}
					}

					// Highlight any found errors
					$('.text-danger').parent().addClass('has-error');
	            } else {
				ode.payment(0);
	            
	               
	            }
	        }
	    });
	},
	
	'guest' : function(status){
		if(status){
			
			 $.ajax({
		        url: 'index.php?route=checkout/guest/save',
		        type: 'post',
		        data: $('#collapse-payment-address input[type=\'text\'], #collapse-payment-address input[type=\'date\'], #collapse-payment-address input[type=\'datetime-local\'], #collapse-payment-address input[type=\'time\'], #collapse-payment-address input[type=\'checkbox\']:checked, #collapse-payment-address input[type=\'radio\']:checked, #collapse-payment-address input[type=\'hidden\'], #collapse-payment-address textarea, #collapse-payment-address select'),
		        dataType: 'json',
		        success: function(json) {
		            $('.alert, .text-danger').remove();

		            if (json['redirect']) 
		            {
		                location = json['redirect'];
		            } 
		            else if (json['error']) 
		            {
		                if (json['error']['warning']) {
		                    $('#collapse-payment-address .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		                }

						for (i in json['error']) {
							
							var element = $('#input-payment-' + i.replace('_', '-'));

							if ($(element).parent().hasClass('input-group')) {
								$(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
							} else {
								$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
							}
						}
						move2();
						// Highlight any found errors
						$('.text-danger').parent().addClass('has-error');
		            } 
		            else 
		            {
		            	//var shipping_address = $('#collapse-payment-address input[name=\'shipping_address\']').val();
		            	var shipping_address = $('#collapse-payment-address input[name=\'shipping_address\']:checked').prop('value');
						$('.has-error').removeClass('has-error');
                		
                		if (shipping_address) 
                		{
		                    $.ajax({
		                        url: 'index.php?route=checkout/shipping_method',
		                        dataType: 'html',
		                        success: function(html){
									// Add the shipping address
		                            $.ajax({
		                                url: 'index.php?route=checkout/guest_shipping',
		                                dataType: 'html',
		                                success: function(xhtml) {
		                                    //$('#collapse-shipping-address .panel-body').html(xhtml);
											$('#collapse-shipping-address').parent().find('.panel-heading .panel-title').html(txtSame);
											$('#collapse-shipping-address .panel-body').html('');
											 ode.paymentMethod(0);
		                                }
		                            });

								    $('#collapse-shipping-method .panel-body').html(html);

	                    		}
		                    });
		                    disabledOptions(1);
		                    //ode.doOrder(4);
		                    $('#guest-shipping-entry-view').hide();
		                    $('#shipping-panel-control').hide();
		                    
		                } 
		                else 
		                {
		                	$('#collapse-shipping-address').parent().find('.panel-heading .panel-title').html(txtShipping);
		                 	 $.ajax({
		                        url: 'index.php?route=checkout/guest_shipping',
		                        dataType: 'html',
		                        success: function(html) {
		                            $('#collapse-shipping-address .panel-body').html(html);
		                            
		                            ode.paymentMethod(0);
		                        }
		                    });
		                    //ode.doOrder(4);
		                   
						}
						
		               	guestView();
		              	$('#payment-panel-control').show();
		                
		            }
		        
		        	$('#collapse-payment-address').parent().find('.panel-control').show();
		        }
		    });
		}
	},

	'guestShipping': function(status) {
		if(status)
		{
			$.ajax({
		        url: 'index.php?route=checkout/guest_shipping/save',
		        type: 'post',
		        data: $('#collapse-shipping-address input[type=\'text\'], #collapse-shipping-address input[type=\'date\'], #collapse-shipping-address input[type=\'datetime-local\'], #collapse-shipping-address input[type=\'time\'], #collapse-shipping-address input[type=\'password\'], #collapse-shipping-address input[type=\'checkbox\']:checked, #collapse-shipping-address input[type=\'radio\']:checked, #collapse-shipping-address textarea, #collapse-shipping-address select,#comment'),
		        dataType: 'json',
		        success: function(json) {
		            $('.alert, .text-danger').remove();

		            if (json['redirect']) {
		                location = json['redirect'];
		            } 
		            else if (json['error']) 
		            {
		                if (json['error']['warning']) {
		                    $('#collapse-shipping-address .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		                }

						for (i in json['error']) {
							var element = $('#input-shipping-' + i.replace('_', '-'));

							if ($(element).parent().hasClass('input-group')) {
								$(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
							} else {
								$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
							}
						}
						$('#shipping-panel-control').hide();
						move2();
						// Highlight any found errors
						$('#guest-shipping-entry-view').addClass('has-error');
		            } 
		            else 
		            {
		            	$('.has-error').removeClass('has-error');
		            	
		               	disabledOptions(1);
		            }
		        	ode.doOrder(2);
		        	guestShippingView();
		        }
		    });
			
		}
		else //load
		{
			$.ajax({
                        url: 'index.php?route=checkout/guest_shipping',
                        dataType: 'html',
                        success: function(html) {
                            $('#collapse-shipping-address .panel-body').html(html);	
                        }
                    });
		}
	},
	
	'payment': function(status) {
		if(status)
		{
			//save
			$.ajax({
        		url: 'index.php?route=checkout/payment_address/save',
        		type: 'post',
        		data: $('#collapse-payment-address input[type=\'text\'], #collapse-payment-address input[type=\'date\'], #collapse-payment-address input[type=\'datetime-local\'], #collapse-payment-address input[type=\'time\'], #collapse-payment-address input[type=\'password\'], #collapse-payment-address input[type=\'checkbox\']:checked, #collapse-payment-address input[type=\'radio\']:checked, #collapse-payment-address input[type=\'hidden\'], #collapse-payment-address textarea, #collapse-payment-address select,#comment'),
        		cache: false, 
        		dataType: 'json',
        		success: function(json) {
            		$('.alert, .text-danger').remove();

		            if (json['redirect']) {
		                location = json['redirect'];
		            } 
		            else if (json['error']) 
		            {
		                if (json['error']['warning']) {
		                    $('#collapse-payment-address .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		                }

						for (i in json['error']) {
							var element = $('#input-payment-' + i.replace('_', '-'));

							if ($(element).parent().hasClass('input-group')) {
								$(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
							} else {
								$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
							}
						}
						move2();
						// Highlight any found errors
						$('.text-danger').parent().parent().addClass('has-error');
						
						return 0;
            		} else{
            			ode.paymentMethod(0);
						return 1;
					}
		        }
		    });
		}
		else
		{
			//load
			$.ajax({
		        url: 'index.php?route=checkout/payment_address',
		        dataType: 'html',
		        success: function(html) {
		            $('#collapse-payment-address .panel-body').html(html);
		        },
		        error: function(xhr, ajaxOptions, thrownError) {
		            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		            return false;
		        }
	    	});
	    	return true;
		}
	},
	
	'paymentMethod': function(status) {
		if(status)
		{//1
			console.log('paymentmethod:1');
			$.ajax({
		        url: 'index.php?route=checkout/payment_method/save',
		        type: 'post',
		        data: $('#collapse-payment-method input[type=\'radio\']:checked, #collapse-payment-method input[type=\'checkbox\']:checked, #collapse-payment-method textarea, #comment'),
		        cache: false, 
		        dataType: 'json',
		        success: function(json) {
		            $('.alert, .text-danger').remove();

		            if (json['redirect']) {
		               // location = json['redirect'];
		            } else if (json['error']) {
		                if (json['error']['warning']) {
		                    $('#collapse-payment-method .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		                }
		                return false;
		            } else {
		            	ode.confirm();
		                return true;
		            }
		        }
		    });
		}
		else
		{
			//load
			console.log('paymentmethod:0');
			$.ajax({
                url: 'index.php?route=checkout/payment_method',
                dataType: 'html',
                success: function(html) {
                    $('#collapse-payment-method .panel-body').html(html);
                    
                    return true;
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    return false;
                }
            });
		}
		
	},
	
	'shipping': function(status) {
		if(status)
		{
			//save
			 $.ajax({
		        url: 'index.php?route=checkout/shipping_address/save',
		        type: 'post',
		        data: $('#collapse-shipping-address input[type=\'text\'],#collapse-shipping-address input[type=\'hidden\'], #collapse-shipping-address input[type=\'date\'], #collapse-shipping-address input[type=\'datetime-local\'], #collapse-shipping-address input[type=\'time\'], #collapse-shipping-address input[type=\'password\'], #collapse-shipping-address input[type=\'checkbox\']:checked, #collapse-shipping-address input[type=\'radio\']:checked, #collapse-shipping-address textarea, #collapse-shipping-address select'),
		        cache: false, 
		        dataType: 'json',
		        success: function(json) {
					$('.alert, .text-danger').remove();
		            if (json['redirect']) {
		                location = json['redirect'];
		            } else if (json['error']) {
		                if (json['error']['warning']) {
		                    $('#collapse-shipping-address .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		                }
						for (i in json['error']) {
							var element = $('#input-shipping-' + i.replace('_', '-'));

							if ($(element).parent().hasClass('input-group')) {
								$(element).parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
							} else {
								$(element).after('<div class="text-danger">' + json['error'][i] + '</div>');
							}
						}
						move2();
						// Highlight any found errors
						$('.text-danger').parent().parent().addClass('has-error');
						return false;
		            } else {
		            	ode.shippingMethod(0);
		              return true;
		            }
		        }
		    });
		}
		else
		{
			//load
			$.ajax({
	            url: 'index.php?route=checkout/shipping_address',
	            dataType: 'html',
	            success: function(html) {
	                $('#collapse-shipping-address .panel-body').html(html);
	                return true;
	            },
	            error: function(xhr, ajaxOptions, thrownError) {
	                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	                return false;
	            }
	        });
		}
	},
	
	'shippingMethod': function(status) {
		if(status)
		{
			 $.ajax({
		        url: 'index.php?route=checkout/shipping_method/save',
		        type: 'post',
		        data: $('#collapse-shipping-method input[type=\'radio\']:checked, #comment'),
		        cache: false, 
		        dataType: 'json',
		        success: function(json) {
		            $('.alert, .text-danger').remove();

		            if (json['redirect']) {
		                location = json['redirect'];
		            } else if (json['error']) {
		            	
		                if (json['error']['warning']) {
		                    $('#collapse-shipping-method .panel-body').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button class="close" data-dismiss="alert">&times;</button></div>');
		                }
		                return false;
		            } else {
		            	$.ajax({
			                url: 'index.php?route=checkout/confirm/totals',
			                type: 'post',
			                dataType: 'html',
			                data: $('#comment'),
			                success: function(html) {
			                    $('#confirm-total').html(html);
			                    
			                    onOrder=0;
								return true;
							}
			            });
		               return true;
		            }
		        }
		    });
			
		}
		else //load
		{
			$.ajax({
                url: 'index.php?route=checkout/shipping_method',
                dataType: 'html',
                success: function(html) {
                    $('#collapse-shipping-method .panel-body').html(html);
                    
                    return true;
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    return false;
                }
            });
		}
	},
	
	'confirm': function() {
		if($('.has-error').length==0 && $('.gate-opened').length==0){
			$.ajax({
                url: 'index.php?route=checkout/confirm/totals',
                type: 'post',
                dataType: 'html',
                data: $('#comment'),
                success: function(html) {
                    $('#confirm-total').html(html);
                    loader.loaded();
					return true;
				}
            });
		}
		else
		{
			move2();
		}
	},
	
	'lastConfirm': function() {
		//if($('.has-error').length==0 && $('.gate-opened').length==0)
		//{
			$.ajax({
		        url: 'index.php?route=checkout/confirm/payment',
		        dataType: 'html',
		        success: function(html) {
		        	$("html, body").animate({scrollTop:0}, 500);
		        	if($( "input[name=\'payment_method\']" ).prop( "checked")){
		        		
		        		$('#last-confirm .confirm-container').removeClass($('#last-confirm .confirm-container').data('class'));
		        		$('#last-confirm .confirm-container').addClass($("input[name=\'payment_method\']").val());
		        		$('#last-confirm .confirm-container').data('class',$("input[name=\'payment_method\']").val())
		        	}

		           $('#last-confirm .container-body').html(html);
		           $('#last-confirm').show()
		        }
		    });	
		//}
		//else
		//{
		//	move2();
		//}
		
	},
	
	'cart' : function (){
		$.ajax({
	        url: 'index.php?route=common/cart/infoy',
	        dataType: 'html',
	        success: function(html) {
	           $('#collapse-checkout-confirm .panel-body').html(html);
	        }
	    });
	},
	
	'coupon':function (){
		$.ajax({
                url: 'index.php?route=checkout/payment_method',
                dataType: 'html',
                success: function(html) {
                    $('#collapse-payment-method .panel-body').html(html);
                    
                    return true;
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    return false;
                }
            });
	},
	
	'voucher':function (){
		location = location;
	},
	
	'reload':function (){
		
	},
	
	'doOrder':function(job){
		
		switch(job){
			case 1:
				onOrder=1;
				$.ajax({
                    url: 'index.php?route=checkout/shipping_method',
                    dataType: 'html',
                    success: function(html) {
                        $('#collapse-shipping-method .panel-body').html(html);
						$.ajax({
			                url: 'index.php?route=checkout/payment_method',
			                dataType: 'html',
			                success: function(html) {
			                    $('#collapse-payment-method .panel-body').html(html);
			                    $.ajax({
					                url: 'index.php?route=checkout/confirm/totals',
					                type: 'post',
					                dataType: 'html',
					                data: $('#comment'),
					                success: function(html) {
					                    $('#confirm-total').html(html);
					                    onOrder=0;
										return true;
									}
					            });
					            
			                    return true;
			                }
			            });
			    	}
				});
	            
				break;
			case 2:
				 $.ajax({
                    url: 'index.php?route=checkout/shipping_method',
                    dataType: 'html',
                    success: function(html) {
                        $('#collapse-shipping-method .panel-body').html(html);
                        ode.shippingMethod(1);
                        $.ajax({
			                url: 'index.php?route=checkout/confirm/totals',
			                type: 'post',
			                dataType: 'html',
			                data: $('#comment'),
			                success: function(html) {
			                    $('#confirm-total').html(html);
			                    
			                    onOrder=0;
								return true;
							}
			            });
                    }
                });
				break;
			case 3:
				$.ajax({
	                url: 'index.php?route=checkout/confirm/',
	                type: 'post',
	                dataType: 'html',
	                data: $('#comment'),
	                success: function(html) {
	                    $('#collapse-checkout-confirm .panel-body').html(html);
	                    onOrder=0;
						return true;
					}
	            });
				break;
			case 4:
				/*
				$.ajax({
	                url: 'index.php?route=checkout/confirm/',
	                type: 'post',
	                dataType: 'html',
	                data: $('#comment'),
	                success: function(html) {
	                    $('#collapse-checkout-confirm .panel-body').html(html);
	                    onOrder=0;
						return true;
					}
	            });*/
				break;
			case 5:
				$.ajax({
	                url: 'index.php?route=checkout/confirm/totals',
	                type: 'post',
	                dataType: 'html',
	                data: $('#comment'),
	                success: function(html) {
	                    $('#confirm-total').html(html);
	                    onOrder=0;
						return true;
					}
	            });
				break;
			case 6:
				$.ajax({
	                url: 'index.php?route=checkout/confirm/cart',
	                type: 'post',
	                dataType: 'html',
	                data: $('#comment'),
	                success: function(html) {
	                    $('#confirm-cart').html(html);
	                    onOrder=0;
						return true;
					}
	            });
				break;
		}
		
	}
	

}

function move2(){
	if($('.text-danger').length>0){
		topPoz = $('.text-danger:first').offset().top;
		topPoz -= 180;
		$("html, body").animate({scrollTop:topPoz+'px' }, 500);
	}
}

function guestView(){
 	$("#guest-view .add-name").html($('#input-payment-firstname').val()+' '+$('#input-payment-lastname').val());
	$("#guest-view .addr-name").html(' Addr:' + $('#input-payment-address-1').val()+' Zip Code:'+$('#input-payment-postcode').val()+' '+$('#input-payment-city').val()+' <strong>'+$('#input-payment-zone :selected').text()+' '+$('#input-payment-country :selected').text()+'</strong><br/>');
	
	
	if($('#guest-entry-view').hasClass('has-error')){
		$('#guest-view').hide();
		$('#payment-panel-control').hide();
		$('#guest-entry-view').show();
		$('#guest-view').addClass('gate-opened');
		
	}else{
		
		$('#guest-view').show();
		$('#payment-panel-control').show();
		$('#guest-view').removeClass('gate-opened');
		$('#guest-entry-view').hide();
		$("html, body").animate({scrollTop: $('#side-a').offset().top }, 500);
	}
	
	
 }
 
function guestShippingView(){
 	$("#guest-shipping-view .add-name").html($('#input-shipping-firstname').val()+' '+$('#input-shipping-lastname').val());
	$("#guest-shipping-view .addr-name").html(' Addr:'+$('#input-shipping-address-1').val()+' '+$('#input-shipping-postcode').val()+' '+$('#input-shipping-city').val()+' <strong>'+$('#input-shipping-zone :selected').text()+' / '+$('#input-shipping-country :selected').text()+'</strong> ');
	
	if($('#guest-shipping-entry-view').hasClass('has-error')){
		$('#guest-shipping-view').hide();
		$('#shipping-panel-control').hide();
		$('#guest-shipping-entry-view').show();
		$('#guest-shipping-view').addClass('gate-opened');
		move2();
	}else{
		$('#guest-shipping-view').show();
		$('#shipping-panel-control').show();
		$('#guest-shipping-entry-view').hide();
		$("html, body").animate({scrollTop: $('#side-a').offset().top }, 500);
		
	}
	
	
 }

function disabledOptions(sts){
	
		if(sts){
			$('#collapse-shipping-method').removeClass('hide-div');
			$('#collapse-payment-method').removeClass('hide-div');
		}else{
			$('#collapse-shipping-method').addClass('hide-div');
			$('#collapse-payment-method').addClass('hide-div');
		}
	
}

$(document).ready(function (){
	
	
	$('select').each(function () {

		    // Cache the number of options
		    var $this = $(this),
		        numberOfOptions = $(this).children('option').length;

		    // Hides the select element
		    $this.addClass('s-hidden');

		    // Wrap the select element in a div
		    $this.wrap('<div class="select"></div>');

		    // Insert a styled div to sit over the top of the hidden select element
		    $this.after('<div class="styledSelect"></div>');

		    // Cache the styled div
		    var $styledSelect = $this.next('div.styledSelect');

		    // Show the first select option in the styled div
		    $styledSelect.text($this.children('option').eq(0).text());

		    // Insert an unordered list after the styled div and also cache the list
		    var $list = $('<ul />', {
		        'class': 'options'
		    }).insertAfter($styledSelect);

		    // Insert a list item into the unordered list for each select option
		    for (var i = 0; i < numberOfOptions; i++) {
		        $('<li />', {
		            text: $this.children('option').eq(i).text(),
		            rel: $this.children('option').eq(i).val()
		        }).appendTo($list);
		    }

		    // Cache the list items
		    var $listItems = $list.children('li');

		    // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
		    $styledSelect.click(function (e) {
		        e.stopPropagation();
		        $('div.styledSelect.active').each(function () {
		            $(this).removeClass('active').next('ul.options').hide();
		        });
		        $(this).toggleClass('active').next('ul.options').toggle();
		    });

		    // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
		    // Updates the select element to have the value of the equivalent option
		    $listItems.click(function (e) {
		        e.stopPropagation();
		        $styledSelect.text($(this).text()).removeClass('active');
		        $this.val($(this).attr('rel'));
		        $list.hide();
		        /* alert($this.val()); Uncomment this for demonstration! */
		    });

		    // Hides the unordered list when clicking outside of it
		    $(document).click(function () {
		        $styledSelect.removeClass('active');
		        $list.hide();
		    });

		});

	$(document).delegate(".edit-address-item", "click", function(e){
		e.preventDefault();
		$(this).parent().hide();
		if($(this).parents('.panel').find('#collapse-payment-address').length>0){
			$("#guest-view").hide();
			$('#guest-entry-view').show();
			
			$("#guest-view").data("on","1");
			disabledOptions(0);
			//$('#guest-shipping-view').addClass('gate-opened');
		}else{
			
			$("#guest-shipping-view").hide();
			$("#guest-shipping-entry-view").show();
			$("#guest-shipping-view").data("on","1");
			disabledOptions(0);
			//$('#guest-shipping-view').addClass('gate-opened');
			
		}
		return false;
	});

});



	
