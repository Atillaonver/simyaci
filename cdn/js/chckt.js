/*
class Chain {
    constructor() {
        this.start = false;
        this.data = [];
    }

    attach(call) {
        this.data.push(call);

        if (!this.start) {
            this.execute();
        }
    }

    execute() {
        if (this.data.length) {
            this.start = true;

            var call = this.data.shift();

            var jqxhr = call();

            jqxhr.done(function() {
                chain.execute();
            });
        } else {
            this.start = false;
        }
    }
}

var chain = new Chain();
*/

var sMethods= function() {
		var language = $('#clanguage').val();
		
		//chain.attach(function(){
        //	return 
        $.ajax({
            url: 'index.php?route=checkout/shipping_method.quote&language='+language,
            dataType: 'json',
            success: function(json) {
            	html = ''; 
            	$('#input-shipping-method').removeClass('is-invalid');
                $('#error-shipping-method').removeClass('d-block');

                if (json['error']) {
                    $('#input-shipping-method').addClass('is-invalid');
                    $('#error-shipping-method').html(json['error']).addClass('d-block');
                }
                var firstJ = 0;
                for (i in json['shipping_methods']) {
                	if (!json['shipping_methods'][i]['error']) {
                			//var CODE = $('#input-shipping-code').val()
                			for (j in json['shipping_methods'][i]['quote']) {
		                        html += '<div class="form-check">';
		                        var code = i + '-' + j.replaceAll('_', '-');
		                        html += '<input type="radio" name="shipping_method" value="' + json['shipping_methods'][i]['quote'][j]['code'] + '" id="input-shipping-method-' + code + '"';
								if(firstJ == 0){
									firstJ ++;
									html += ' checked';
								}
								
		                        html += '/>';
		                        html += '  <label for="input-shipping-method-' + code + '">' + json['shipping_methods'][i]['quote'][j]['name'] + ' - ' + json['shipping_methods'][i]['quote'][j]['text'] + '</label>';
		                        html += '</div>';
		                    }
	                } else {
	                    html += '<div class="alert alert-danger">' + json['shipping_methods'][i]['error'] + '</div>';
	                }
	            }
            	
            	$('#methods-shipping').html(html);
            	$('[name="shipping_method"]').trigger("change");
            	$('#checkout-shipping-method').removeClass('hidden');
             }
			});
		//});
}
	
var pMethods = function() {
		var language = $('#clanguage').val();
		
		//chain.attach(function() {
        //	return 
        $.ajax({
		            url: 'index.php?route=checkout/payment_method.getMethods&language='+language,
		            dataType: 'json',
		            success: function(json) {
		            	 html = ''; 
		            	$('#input-payment-method').removeClass('is-invalid');
		                $('#error-payment-method').removeClass('d-block');

		                if (json['error']) {
		                    $('#input-payment-method').addClass('is-invalid');
		                    $('#error-payment-method').html(json['error']).addClass('d-block');
		                }
		               
		                var firstJ=0;
		                for (i in json['payment_methods']) {
		                    if (!json['payment_methods'][i]['error']) {
		                    	//var CODE = $('#input-payment-code').val()
                				
		                        for (j in json['payment_methods'][i]['option']) {
		                            html += '<div class="form-check">';
		                            var code = i + '-' + j.replaceAll('_', '-');

		                            html += '<input type="radio" name="payment_method" value="' + json['payment_methods'][i]['option'][j]['code'] + '" id="input-payment-method-' + code + '"';
									if(firstJ == 0){
										firstJ = 1;
										 html += ' checked';
									}
		                            html += '/>';
		                            html += '  <label for="input-payment-method-' + code + '">' + json['payment_methods'][i]['option'][j]['name'] + '</label>';
		                            html += '</div>';
		                        }
		                    } else {
		                        html += '<div class="alert alert-danger">' + json['payment_methods'][i]['error'] + '</div>';
		                    }
		                }
		             	
		             	$('#methods-payment').html(html)
						$('[name="payment_method"]').trigger("change");
						$('#checkout-payment-method').removeClass('hidden');
		             }
				});
		//});
}
	
var confirm =  function() {
	var language = $('#clanguage').val();
	$('#checkout-confirm').load('index.php?route=checkout/confirm.confirm&language='+language);
}

function scrllDiv(parentid, id){
	TOP = parseInt($("#"+id).position().top)
	HEIGHT = parseInt($("#"+parentid).height())
	
	if( TOP > HEIGHT ){
		$("#"+parentid).animate({scrollTop: $("#"+id).position().top}, 800, 'swing');
	}
    
}

// Existing Address
function adrChange(elem){
    var language = $('#clanguage').val();
    var element = $('#input-'+elem+'-existing');
    elem = elem ? elem : 'register';
    
    //chain.attach(function() {
    //  	return 
    $.ajax({
            url: 'index.php?route=checkout/' + elem + '_address.address&language=' + language + '&address_id=' + $(element).val(),
            dataType: 'json',
            success: function(json) {
                
                $('#input-'+elem+'-address').removeClass('is-invalid');
                $('#error-'+elem+'-address').removeClass('d-block');

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['error']) {
                    $('#input-'+elem+'-address').addClass('is-invalid');
                    $('#error-'+elem+'-address').html(json['error']).addClass('d-block');
                }

                if (json['success']) {
                    $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-circle-check"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    
                    switch(elem){
                    	case 'shipping':
                    		sMethods();
                    		break;
                    	case 'payment':
                    		sMethods();
                    	default:
                    		confirm();	
                    }
                   	
                }
            }
        });
   // });
}