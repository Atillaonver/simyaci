var vWidth = screen.width;
var vHeight = screen.height;
var iH = window.innerHeight;
var stcky=0;

var opened="";
var prevScrollpos = window.pageYOffset;

$("#scriptDiv").before('<div id="mesMod" class="w3-modal"><div class="w3-modal-content"><div class="modal-head w3-container"><span class="modal-close-button w3-display-topright"><i class="w3-xlarge fa fa-times-circle"></i></span><h2 class="modal-title">Message</h2></div><div class="modal-body w3-container"></div><div id="modal-error" class="w3-container" style="display:none;"></div><div class="modal-footer w3-container w3-theme-action"></div></div></div>');

$("#scriptDiv").before('<div id="inner-cart" class="w3-modal" ><div class="w3-modal-content"><div class="modal-head w3-container"><span class="modal-close-button w3-display-topright"><i class="w3-xlarge fa fa-times-circle"></i></span><h2>Your Cart</h2></div><div id="modal-body" class="modal-body w3-container"></div><div id="modal-error" class="w3-container" style="display:none;"></div><div class="modal-footer w3-container"><p>What is next?</p></div></div></div>');

var popUp = {
	'create':function()
	{
		ppHtml = '<div id="pop-up" data-w="' + screen.width + '"  data-h="' + screen.height + '" data-l=""><div class="s-head"><img src="'+ $('.logo-pozitive').attr('src') + '" alt="' + $('.logo-pozitive').attr('alt') + '" class="w3-image" /><span class="popup-close-button"><i class="w3-xlarge fa fa-times-circle"></i></span></div><div id="pop-up-body" class="s-body popup-content scroll"></div></div>';

		$('#page-loader').before(ppHtml);



		$(window).resize(function()
			{
				$('#pop-up').attr({'data-h':screen.height,'data-w':screen.width});
			});

	},
	'open': function(URL)
	{
		l=$('#pop-up').attr('data-l');

		if(l==URL)
		{
			$('#pop-up').show();
			$('#pop-up').animate({top: 0},500, 'easeOutBounce');
		}else
		{
			$('#pop-up').attr('data-l',URL);
			$('#pop-up-body').load(URL,function()
				{
					$('body,html').css('overflow','hidden');
					$('#pop-up').show();
					$('#pop-up').animate({top: 0},500, 'easeOutBounce');
				});
		}

	},

	'close':function()
	{
		h=$('#pop-up').data('h');
		$('#pop-up').animate(
			{
				top:h
			},500, 'easeOutBounce',function()
			{
				$('body,html').css('overflow','unset');
				$('#pop-up').hide();
			});
	},

}

var loader = {
	'loading': function(Ind)
	{
		$('#page-loader').height($(document).height()+'px');
		opened +=','+Ind;
		$('#page-loader').show();
		if($('#page-loader .spiner i').length==0)
		{
			$('#page-loader .spiner').html('<img src="image/tools/star-gif-4-frames.gif" alt="loader"/>');
		}

		$('[type="button"]').prop('disabled', true);
	},
	'loaded': function(Ind)
	{
		var n = opened.indexOf(','+Ind);
		if(n!== -1)
		{
			opened = opened.replace(','+Ind, "");
			if(opened=="")
			{
				$('#page-loader').hide();
				$('#page-loader').removeClass('loader-opt');
				$('[type="button"]').prop('disabled', false);
			}
		}else
		{
			$('#page-loader').hide();
			$('#page-loader').removeClass('loader-opt');
			$('[type="button"]').prop('disabled', false);
		}
	},
	'process':function()
	{
	}
}

$.ajaxSetup(
	{
		beforeSend: function(jqXHR)
		{
			loader.loading(jqXHR.requestId);
		},
		complete: function(jqXHR)
		{
			loader.loaded(jqXHR.requestId);
		},
		xhr: function ()
		{
			var xhr = new window.XMLHttpRequest();
			//Download progress
			xhr.addEventListener("progress", function (evt)
				{
					//console.log([evt.lengthComputable, evt.loaded, evt.total]);
					if (evt.lengthComputable)
					{
						var percentComplete = evt.loaded / evt.total;
						//$('#percent').html(Math.round(percentComplete * 100) + "%");
					}
				}, false);
			return xhr;
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			msg ='';
			if (xhr.status === 0)
			{
				msg = 'Not connect.\n Verify Network.';
			} else if (xhr.status == 404)
			{
				msg = 'Requested page not found. [404]';
			} else if (xhr.status == 500)
			{
				msg = 'Internal Server Error [500].';
			} else if (thrownError === 'parsererror')
			{
				msg = 'Requested JSON parse failed.';
			} else if (thrownError === 'timeout')
			{
				msg = 'Time out error.';
			} else if (thrownError === 'abort')
			{
				msg = 'Ajax request aborted.';
			} else
			{
				msg = 'Uncaught Error.\n' + xhr.responseText;
			}
			if(msg)
			{
				alert(msg);
			}
			//if(xhr.responseText){
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			//}

			return false;
		}
	});

$.ajaxPrefilter(function( options, originalOptions, jqXHR )
	{
		jqXHR.requestId = Math.floor( (Math.random() * 9999999999) + 1 )
	})

[].forEach.call(document.querySelectorAll('img[data-src]'),    function(img) {
  img.setAttribute('src', img.getAttribute('data-src'));
  img.onload = function() {
    img.removeAttribute('data-src');
  };
});



function getURLVar(key)
{
	var value = [];

	var query = String(document.location).split('?');

	if (query[1])
	{
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++)
		{
			var data = part[i].split('=');

			if (data[0] && data[1])
			{
				value[data[0]] = data[1];
			}
		}

		if (value[key])
		{
			return value[key];
		} else
		{
			return '';
		}
	}
}

function moveFrom()
{
	//if($('.pSlides').length>1){
	//	$('#prod-list-images').css('height',$('.pSlides').eq(0).innerHeight());
	//}else{
	//	$('#prod-list-images').css('height',$('.pSlides').innerHeight());
	//}
	$('#prod-list-images').css('height',$('.heiRef').innerHeight());
	
	//$('.mobile #prod-list-images').css('height','auto');
	
	$('.p-name').insertBefore($('#col-image'));
	$('.product-options').insertBefore($('.purchase'));
	$('.p-model').insertAfter($('.purchase'));
	$('.p-l-i-ctrl').data('sel',0);
	$('.p-l-i-ctrl').show();
	
	$('.product-box').each(function(){
		iHe=$(this).width();
		$(this).css({height:'initial'})
	})
	$('.banner-boxed').each(function(){
		iHe=$(this).width();
		$(this).css({height:'initial'})
	})
	
	$('#header-logo').css('width','100%');
}

function checkWindowWidth() {
		//check window width (scrollbar included)
		var e = window,
			a = 'inner';
		if (!('innerWidth' in window )) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		if ( e[ a+'Width' ] >= MqL ) {
			return true;
		} else {
			return false;
		}
	}
	
function moveTo()
{
	
	$('.p-name').insertAfter($('#i-ref'));
	$('.p-model').insertAfter($('.p-name'));
	$('.purchase').insertAfter($('.p-model'));
	$('.p-l-i-ctrl').data('sel',0);
	$('.p-l-i-ctrl').hide();
	$('.pSlides').removeClass('act-color');
	$('.product-box').each(function(){
		iHe=$(this).width();
		$(this).css({height:iHe})
	})
	$('.banner-boxed').each(function(){
		iHe=$(this).width();
		$(this).css({height:iHe})
	})
	iHe=0;
	$('.row-3-c2.t11 > div').each(function(){
		iHe = parseInt($(this).height())+iHe;
	})
	
	
	$('.row-3-c1.t11 > .product-box').css({height:iHe});
	$('.row-3-c2.t12 > .product-box').css({height:iHe});
	
	$('.mask').each(function(){
		iHe = parseInt($(this).parents('.product-box').height());
		iWd = parseInt($(this).parents('.product-box').width());
		$(this).css({height:iHe,width:iWd})
	})
	$('#header-logo').css('width','100%');
}

function handleScrollTop()
{

	function totop_button(a)
	{
		var b = $("#up-btn"),
		f = $(".cart-float-right");

		if (a == "on")
		{
			f.addClass("on fadeInRight ").removeClass("off fadeOutRight");
			b.addClass("on fadeInRight ").removeClass("off fadeOutRight");
		} else
		{
			f.addClass("off fadeOutRight animated").removeClass("on fadeInRight");
			b.addClass("off fadeOutRight animated").removeClass("on fadeInRight");
		}
	}

	function upm_scrl(drm)
	{
		if(drm==1)
		{
			if(!$('.top-container').hasClass('scrolled'))
			{
				$('.top-container').addClass('scrolled');
				$('.ontop').hide();
				$('#header-logo').animate(
					{
						width: 20+'%'
					}, 100,function()
					{

					});
			}
		}else
		{
			if($('.top-container').hasClass('scrolled'))
			{
				$('.top-container').removeClass('scrolled');
				$('.ontop').show();
				$('#header-logo').animate(
					{
						width: 100+'%'
					}, 100,function()
					{

					});
			}
		}


	}

	$(window).scroll(function()
		{
			vWidth = screen.width;
			vHeight = screen.height;
			var b = $(this).scrollTop();
			var c = $(this).height();

			if(vWidth>1024)
			{
				if(b > 100)
				{
					upm_scrl(1);
				}else
				{
					upm_scrl(0);
				}
			}

			if (b > 0)
			{
				var d = b + c / 2;
			}
			else
			{
				var d = 1 ;
			}

			//console.log(iH+' ->b:'+b+' ->d:'+d+' ->c:'+c);

			if (d < 1e3 && d < c)
			{
				totop_button("off");

			}
			else
			{
				totop_button("on");
			}

			if(vWidth>1024)
			{
				stcky = $(".sticky-referance").innerHeight();
				$('#col-info').attr('data-ref',stcky)
			}


		});

	$("#up-btn").click(function (e)
		{
			e.preventDefault();
			$('body,html').animate({scrollTop:0},800,'swing');
		});


}

handleScrollTop();

function reScr()
{
	vWidth = screen.width;
	vHeight = screen.height;
	
	var inH = $('.heiRef').innerHeight(); 
	//var inH = $('.prod-list-image').eq(0).find('.pSlides').innerHeight(); 
	
	//$('.pSlides').eq(0).innerHeight();
	//console.log('Height '+inH);
	if (vWidth < 1024 ){
		inH = 	parseInt(screen.width) - 40 ;
		$('#prod-list-images').css('height',inH)
		moveFrom();
	}else{
		$('#prod-list-images').removeAttr('style');
		moveTo();
	}
	
	if($('.prod-list-image').length < 2){
		if(!$('.p-l-i-ctrl').hasClass('w3-hide')){ $('.p-l-i-ctrl').addClass('w3-hide');}
	}else{
		$('.p-l-i-ctrl').removeClass('w3-hide');
	}
	
		
		//bugLog(inH);
}


function myFunction()
{
	var navbar = document.getElementById("myNavbar");
	if (document.body.scrollTop > 60 || document.documentElement.scrollTop > 60)
	{
		navbar.className = "w3-bar" + " w3-card" + " w3-white";
	} else
	{
		navbar.className = navbar.className.replace(" w3-card w3-white", "");
	}
	scrollFunction()
}

function openLink(evt, animName)
{
	var i, x, tablinks;
	x = document.getElementsByClassName("size-div");
	for (i = 0; i < x.length; i++)
	{
		x[i].style.display = "none";
	}
	tablinks = document.getElementsByClassName("tablink");
	for (i = 0; i < x.length; i++)
	{
		tablinks[i].className = tablinks[i].className.replace(" w3-black", "");
	}
	document.getElementById(animName).style.display = "block";
	evt.currentTarget.className += " w3-black";
}

function clickHref(URL)
{
	location.href = URL;
}

function topFunction()
{
	$('html, body').animate(
		{
			scrollTop: 0
		}, 500);
}

function validate(obj)
{
	objVal= $(obj).val();

	$(obj).parent().removeClass('valid invalid');

	if($(obj).hasClass( "vmail" ))
	{
		reg = new RegExp(/^[a-z]{1}[\d\w\.-]+@[\d\w-]{3,}\.[\w]{2,3}(\.\w{2})?$/);
		if(reg.test(objVal))
		{
			$(obj).addClass('valid');
		}else
		{
			$(obj).addClass('invalid');
		}
	}

	if($(obj).hasClass( "vtext" ))
	{
		if (objVal.length > 2)
		{
			$(obj).addClass('valid');
		}else
		{
			$(obj).addClass('invalid');
		}
	}

	if($(obj).hasClass( "vnum" ))
	{
		if ($.isNumeric(objVal))
		{
			$(obj).addClass('valid');
		}else
		{
			$(obj).addClass('invalid');
		}
	}
	if($(obj).hasClass( "vsel" ))
	{
		if (objVal!='')
		{
			$(obj).addClass('valid');
		}else
		{
			$(obj).addClass('invalid');
		}
	}

}

function openModal(mId)
{
	/*if ($('#'+mId).hasClass('hide')){
	$('#'+mId).removeClass('hide').fadeIn( "slow" );
	}else{*/
	$('#'+mId).fadeIn( "slow");
	//}
}

var menu = {
	'open':function()
	{
		if($('#m-menu').data('status')=='0')
		{
			$('#m-menu').data('status','1');
			$('#m-menu').css('margin-left','-81%');
			$('#mobile-btn').addClass('on');
			$('#m-menu').show();
			$('#m-menu').animate(
				{
					marginLeft: 0
				},1000, 'easeOutBounce',function()
				{

				});


		}else
		{
			$('#m-menu').data('status','0');
			$('#mobile-btn').removeClass('on');
			$('#m-menu').animate(
				{
					marginLeft: '-81%'
				},1000, 'easeOutBounce',function()
				{
					$('#m-menu').hide();

				});
		}


	},
	'close':function()
	{
		$('#m-menu').data('status','0');
		$('#m-menu').animate(
			{
				marginLeft: '-81%'
			},1000, 'easeOutBounce',function()
			{

				$('#m-menu').hide();
			});
	}
}
// Cart add remove functions
var cart = {
	'add': function(product_id, quantity)
	{
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
				dataType: 'json',
				beforeSend: function()
				{
					$('#cart > button').button('loading');
				},
				complete: function()
				{
					$('#cart > button').button('reset');
				},
				success: function(json)
				{
					$('.alert, .text-danger').remove();

					if (json['redirect'])
					{
						location = json['redirect'];
					}

					if (json['success'])
					{
						location='shopping-cart';
						/*$('#content').parent().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

						// Need to set timeout otherwise it wont update the total
						setTimeout(function () {
						$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
						}, 100);

						$('html, body').animate({ scrollTop: 0 }, 'slow');

						$('#cart > ul').load('index.php?route=common/cart/info ul li');*/
					}
				}
			});
	},
	'update': function(key, quantity)
	{
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/edit',
				type: 'post',
				data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
				dataType: 'json',
				beforeSend: function()
				{
					$('#cart > button').button('loading');
				},
				complete: function()
				{
					$('#cart > button').button('reset');
				},
				success: function(json)
				{
					// Need to set timeout otherwise it wont update the total
					setTimeout(function ()
						{
							$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
						}, 100);

					if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout')
					{
						location = 'index.php?route=checkout/cart';
					} else
					{
						$('#cart > ul').load('index.php?route=common/cart/info ul li');
					}
				}
			});
	},
	'remove': function(key,obj)
	{
		buttonHtml = $(obj).html();
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + key,
				dataType: 'json',
				beforeSend: function()
				{
					$(obj).html('<i class="fa fa-refresh fa-spin"></i>');
				},
				complete: function()
				{
					$(obj).html(buttonHtml);
				},
				success: function(json)
				{
					// Need to set timeout otherwise it wont update the total
					setTimeout(function ()
						{
							$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
						}, 100);

					if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout')
					{
						location = 'index.php?route=checkout/cart';
					} else
					{
						$('#cart > ul').load('index.php?route=common/cart/info ul li');
					}
				}
			});
	},
	'cremove': function(key,obj)
	{
		buttonHtml = $(obj).html();
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + key,
				dataType: 'json',
				beforeSend: function()
				{
					$(obj).html('<i class="fa fa-refresh fa-spin"></i>');
				},
				complete: function()
				{
					$(obj).html(buttonHtml);
				},
				success: function(json)
				{
					
					location = 'index.php?route=checkout/cart';
				}
			});
	},
	'pRemove': function(key,obj)
	{
		buttonHtml = $(obj).html();
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + key,
				dataType: 'json',
				beforeSend: function()
				{
					$(obj).html('<i class="fa fa-refresh fa-spin"></i>');
				},
				complete: function()
				{
					$(obj).html(buttonHtml);
				},
				success: function(json)
				{

					$('#cart > ul').load('index.php?route=common/cart/info ul li');

					$('#modal-body').load('index.php?route=checkout/cart/inner',function( response, status, xhr )
						{

							if ( status == "error" )
							{
								var msg = "Sorry but there was an error: ";
								$( "#error" ).html( msg + xhr.status + " " + xhr.statusText );
							}

							if(response.length <2)
							{
								$( "#modal-error" ).show();
								$( "#modal-error" ).html( $( "#cart-empty" ).html() );
								setTimeout(function ()
									{
										$( "#modal-error" ).hide();
										//document.getElementById('inner-cart').style.display='none';
									}, 100);
							}
						});



				}
			});
	}

}

var voucher = {
	'add': function()
	{

	},
	'remove': function(key)
	{
		$.ajax(
			{
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + key,
				dataType: 'json',
				beforeSend: function()
				{
					$('#cart > button').button('loading');
				},
				complete: function()
				{
					$('#cart > button').button('reset');
				},
				success: function(json)
				{
					// Need to set timeout otherwise it wont update the total
					setTimeout(function ()
						{
							$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
						}, 100);

					if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout')
					{
						location = 'index.php?route=checkout/cart';
					} else
					{
						$('#cart > ul').load('index.php?route=common/cart/info ul li');
					}
				}
			});
	}
}

var wishlist = {
	'add': function(product_id)
	{
		$.ajax(
			{
				url: 'index.php?route=account/wishlist/add',
				type: 'post',
				data: 'product_id=' + product_id,
				dataType: 'json',
				success: function(json)
				{


					if (json['success'])
					{
						$('#mesMod .modal-body').html('<i class="fa fa-check-circle"></i> ' + json['success']);
					}

					if (json['info'])
					{
						$('#mesMod .modal-body').html('<i class="fa fa-info-circle"></i> ' + json['info']);
					}

					$('#wishlist-total').html(json['total']);
					openModal('mesMod');

				}
			});
	},
	'remove': function()
	{

	}
}

var compare = {
	'add': function(product_id)
	{
		$.ajax(
			{
				url: 'index.php?route=product/compare/add',
				type: 'post',
				data: 'product_id=' + product_id,
				dataType: 'json',
				success: function(json)
				{
					$('.alert').remove();

					if (json['success'])
					{
						$('#mesMod .message-body').html('<i class="fa fa-check-circle"></i> ' + json['success']);

						$('#compare-total').html(json['total']);

						$('html, body').animate({ scrollTop: 0 }, 'slow');
					}
				}
			});
	},
	'remove': function()
	{

	}
}



function bugLog(bug){
	if($('#debug').length>0){
		$('#debug').html($('#debug').html()+'<br/>'+bug)
	}else{
		$("#scriptDiv").before('<div id="debug" style="position: fixed; right:0px; width:150px;height:80px;overflow-y: scroll;border:solid 2px #ccc;background-color: #fff;z-index:99999999;">' + bug + '</div>');
	}
}

$(window).resize(function()
{
		reScr();
});

    
$(document).ready(function ()
	{
		//reScr();
		$('#cart > ul').load('index.php?route=common/cart/info ul li');
		$( "#page-container" ).prepend($('#ribbon'));
		$( "body" ).addClass('hs-rbbn');
		//bugLog('loaded');
		
		$('.menu-item > a.active').parents('.top-item').addClass('active_parent');
		$('.menu-item > a.active').parent().addClass('active_up');
		
		
		
	});
	
$(document).ready(function ()
	{
		popUp.create();
		$('.w3-top').animate({top: 0},1000, 'easeOutBounce');
		$('#page-container').animate({opacity: 1},1000);
		//reScr();


		
		
		$(document).delegate('a.nolink','click',function(e){
			e.preventDefault();
			e.stopPropagation();
			
			return false;
		});		
		$('.opsub > a').on('click',function(e){
			e.preventDefault();
			if($(this).parent().hasClass('clicked')){
				$(this).parent().removeClass('clicked');
			}else{
				$('.opsub').removeClass('clicked')
				$(this).parent().addClass('clicked');	
			}
			return false;
		});
		/**/
		$('#top-search').on('focus',function(e){
			$(this).attr('placeholder','Search Text');
		});
		$('#top-search').on('focusout',function(e){
			$(this).attr('placeholder','Click here for Search Text');
		});
		
		$('a.inline-linker').on('click',function(e)
			{
				e.preventDefault();
				popUp.open($(this).attr('href'));
				return false;
			});

		$('.popup-close-button').on('click',function(e)
			{
				popUp.close();
			});
		
		$('.close-danger').on('click',function(e)
			{
				$(this).parents('.alert-danger').remove();
			});

		$('#menu .w3-dropdown-content').each(function(i)
			{
				$(this).width($(this).parent().width())
			});

		/* wishlist */
		$('i.wishlist-btn').on('click', function(e)
			{
				e.preventDefault();
			});

		/* Search */
		$('.modal-close-button').on('click', function(e)
			{
				e.preventDefault();
				$(this).parents('.w3-modal').addClass('hide').fadeOut( "slow" );;
			});

		$('.w3-modal').on('click', function(e)
			{
				if (e.target === this)
				{
					$('.w3-modal').addClass('hide').fadeOut( "slow" );;
				}
			});

		$('.qty-down').on('click', function(e)
			{
				//e.preventDefault();
				e.stopPropagation();
				//if (e.target == this){

				var qtyElem = $('#quantity');
				var Qty = $(qtyElem).val();

				Qty=parseInt(Qty)-1;

				if(Qty>0)
				{

					$("#quantity").val(Qty);
				}
				//console.log(Qty)
				//}


			});

		$('.qty-up').on('click', function(e)
			{
				e.stopPropagation();
				//if (e.target == this){

				var qtyElem =$('#quantity');
				var Qty = $(qtyElem).val();
				Qty=parseInt(Qty)+1;

				$("#quantity").val(Qty);
				//}
				//console.log(Qty)

			});

		$('#search-btn').on('click', function(e)
			{
				if($('#search').hasClass('opened'))
				{
					$('#search').animate(
						{
							marginTop: '-200px'
						}, 100,'swing', function()
						{
							$(this).removeClass('opened');
						});
				}else
				{
					$('#search').animate(
						{
							marginTop: '0px'
						}, 100,'swing', function()
						{
							$(this).addClass('opened');
						});
				}

			});
			//top-search

		
		$('#search-submit').on('click', function()
			{
				url = $('base').attr('href') + 'search';
				var value = $('#search input[name=\'search\']').val();
				if (value)
				{
					url += '&search=' + encodeURIComponent(value);
				}
				$('#search-form').submit();
			});

		$('#search input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('#search-submit').trigger('click');
				}
			});
			
		$('#top-search').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('input[name=\'search\']').val($('#top-search').val())
					$('#search-submit').trigger('click');
				}
			});
			
		$('#search-btn').on('click', function()
		{
			$('input[name=\'search\']').val($('#top-search').val())
			$('#search-submit').trigger('click');

		});


		/* Language */
		$('#language a').on('click', function(e)
			{
				e.preventDefault();

				$('#language input[name=\'code\']').attr('value', $(this).attr('href'));

				$('#language').submit();
			});


		$(".mini-shop-cart-link").mouseenter(function()
			{
				$('.mini-cart-wrapper').css('visibility','visible')
			});

		$(document).on('click',function(e)
			{

				$('.mini-cart-wrapper').css('visibility','hidden')

			});

		$(window).on( "orientationchange", function( event )
			{

			});

		$(window).scroll();

		var $sticky = $('.sticky');
		var $stickyContainer = $('.sticky-container');
		allImg = $('.prod-list-image').length;
		allHImg = $('.prod-list-image.hidden').length;
		
		
		if (($sticky.length>0) && ((allImg-allHImg) > 1 ) && ($(window).width()> 1024))
		{
			// make sure ".sticky" element exists

			var generalSidebarHeight = $('.sticky-referance').outerHeight();
			var stickyContainerHeight = $('.sticky-container').innerHeight();

			var stickyTop = $sticky.offset().top;
			var stickOffset = 0;
			//var stickyStopperPosition = $stickyrStopper.offset().top;
			//var stopPoint = stickyStopperPosition - generalSidebarHeight - stickOffset;
			// var diff = stopPoint + stickOffset;
			referance = $(".sticky-referance").innerHeight();
			//$('#col-image').css('min-height',stickyContainerHeight+"px"); 
			
			
			$('.mobile #col-image').css('min-height',"initial"); 
			
			
			
			
			
			$sticky.attr('data-ref',referance);
			$sticky.css({ height: referance});


			$(window).scroll(function()
				{
					generalSidebarHeight = $('.sticky-referance').innerHeight();
					//console.log( generalSidebarHeight+'  '+stickyContainerHeight+' top  '+stickyTop + $stickyContainer.css('top'));

					var windowTop = $(window).scrollTop();
					switch(true)
					{
						case (windowTop < stickyTop):
						MT=0;
						$stickyContainer.css({marginTop:MT });
						break;
						case (windowTop > stickyTop && windowTop < stickyContainerHeight + stickyTop):
						MT = parseInt(windowTop - stickyTop);
						$stickyContainer.css({marginTop:MT });
						break;
						case (windowTop > stickyContainerHeight + stickyTop && windowTop < (generalSidebarHeight - stickyContainerHeight) + stickyTop):
						MT = parseInt(windowTop - stickyTop);
						$stickyContainer.css({marginTop:MT });
						break;
						case (windowTop > (generalSidebarHeight - stickyContainerHeight) + stickyTop):
						MT = parseInt((generalSidebarHeight - stickyContainerHeight) - stickyTop);
						$stickyContainer.css({marginTop:MT });
						break;
						default:
						break;
					}
				});

		}
		else
		{
			$stickyContainer.css({top: 'unset',bottom:'unset',marginTop:0});
			
		}

		$(window).resize();

		$('#search input[name=\'search\']').focusout(function()
			{
				//$('#search').animate({top: -50}, 400);
			});

		$('#search input[name=\'search\']').parent().find('button').on('click', function()
			{
				url = $('base').attr('href') + 'search';

				var value = $('input[name=\'search\']').val();

				if (value)
				{
					url += '&search=' + encodeURIComponent(value);
				}

				location = url;
			});

		$('#search input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('input[name=\'search\']').parent().find('button').trigger('click');
				}
			});

		$('#page-container').animate(
			{
				top: 0
			},500, 'easeOutBounce');

		loader.loaded();


	});
	
	(function() {

if(!('ontouchend' in document)) return;
var pageX, pageY, newX, newY, linked;

jQuery('.rev_slider').on('touchstart', function(event) {

    newX = newY = false;

    var target = jQuery(event.target),
    clas = target.attr('class');
    event = event.originalEvent;

    if(event.touches) event = event.touches[0];
    pageX = event.pageX;
    pageY = event.pageY;

    if(target.is('a') || target.closest('a').length) linked = target;

}).on('touchmove', function(event) {

    event = event.originalEvent;
    if(event.touches) event = event.touches[0];

    newX = event.pageX;
    newY = event.pageY;
    if(Math.abs(pageX - newX) > 10) event.preventDefault();

}).on('touchend', function(event) {

    if(newX !== false && Math.abs(pageX - newX) > 30) {

        eval('revapi' + jQuery(this).closest('.rev_slider_wrapper').attr('id').split('rev_slider_')[1].split('_')[0])[pageX > newX ? 'revnext' : 'revprev']();

    }
    else if((linked && newY === false) || (linked && Math.abs(pageY - newY) < 10)) {

        linked = linked.is('a') ? linked : linked.closest('a');
        if(linked.length) {

            if(linked.attr('target') === '_blank') {    
                window.open(linked.attr('href'));
            }
            else {
                window.location = linked.attr('href');
            }

        }

    }

    linked = newX = false;

});})();
