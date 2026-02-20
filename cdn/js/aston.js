!function(e,n,t)
{
	function o(e)
	{
		var n=p.className,t=Modernizr._config.classPrefix||"";if(h&&(n=n.baseVal),Modernizr._config.enableJSClass)
		{
			var o=new RegExp("(^|\\s)"+t+"no-js(\\s|$)");n=n.replace(o,"$1"+t+"js$2")
		}Modernizr._config.enableClasses&&(n+=" "+t+e.join(" "+t),h?p.className.baseVal=n:p.className=n)
	}function i(e,n)
	{
		return typeof e===n
	}function s()
	{
		var e,n,t,o,s,a,r;for(var l in d)if(d.hasOwnProperty(l))
		{
			if(e=[],n=d[l],n.name&&(e.push(n.name.toLowerCase()),n.options&&n.options.aliases&&n.options.aliases.length))for(t=0;t<n.options.aliases.length;t++)e.push(n.options.aliases[t].toLowerCase());for(o=i(n.fn,"function")?n.fn():n.fn,s=0;s<e.length;s++)a=e[s],r=a.split("."),1===r.length?Modernizr[r[0]]=o:(!Modernizr[r[0]]||Modernizr[r[0]]instanceof Boolean||(Modernizr[r[0]]=new Boolean(Modernizr[r[0]])),Modernizr[r[0]][r[1]]=o),u.push((o?"":"no-")+r.join("-"))
		}
	}function a()
	{
		return"function"!=typeof n.createElement?n.createElement(arguments[0]):h?n.createElementNS.call(n,"http://www.w3.org/2000/svg",arguments[0]):n.createElement.apply(n,arguments)
	}function r()
	{
		var e=n.body;return e||(e=a(h?"svg":"body"),e.fake=!0),e
	}function l(e,t,o,i)
	{
		var s,l,f,u,d="modernizr",c=a("div"),h=r();if(parseInt(o,10))for(;o--;)f=a("div"),f.id=i?i[o]:d+(o+1),c.appendChild(f);return s=a("style"),s.type="text/css",s.id="s"+d,(h.fake?h:c).appendChild(s),h.appendChild(c),s.styleSheet?s.styleSheet.cssText=e:s.appendChild(n.createTextNode(e)),c.id=d,h.fake&&(h.style.background="",h.style.overflow="hidden",u=p.style.overflow,p.style.overflow="hidden",p.appendChild(h)),l=t(c,e),h.fake?(h.parentNode.removeChild(h),p.style.overflow=u,p.offsetHeight):c.parentNode.removeChild(c),!!l
	}function f(e,n)
	{
		if("object"==typeof e)for(var t in e)v(e,t)&&f(t,e[t]);else
		{
			e=e.toLowerCase();var i=e.split("."),s=Modernizr[i[0]];if(2==i.length&&(s=s[i[1]]),"undefined"!=typeof s)return Modernizr;n="function"==typeof n?n():n,1==i.length?Modernizr[i[0]]=n:(!Modernizr[i[0]]||Modernizr[i[0]]instanceof Boolean||(Modernizr[i[0]]=new Boolean(Modernizr[i[0]])),Modernizr[i[0]][i[1]]=n),o([(n&&0!=n?"":"no-")+i.join("-")]),Modernizr._trigger(e,n)
		}return Modernizr
	}var u=[],d=[],c=
	{
		_version:"3.6.0",_config:
		{
			classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0
		},_q:[],on:function(e,n)
		{
			var t=this;setTimeout(function(){n(t[e])},0)
		},addTest:function(e,n,t)
		{
			d.push({name:e,fn:n,options:t})
		},addAsyncTest:function(e)
		{
			d.push({name:null,fn:e})
		}
	},Modernizr=function()
	{
	};Modernizr.prototype=c,Modernizr=new Modernizr;var p=n.documentElement,h="svg"===p.nodeName.toLowerCase(),m=function()
	{
		var n=e.matchMedia||e.msMatchMedia;return n?function(e)
		{
			var t=n(e);return t&&t.matches||!1
		}:function(n)
		{
			var t=!1;return l("@media "+n+" { #modernizr { position: absolute; } }",function(n){t="absolute"==(e.getComputedStyle?e.getComputedStyle(n,null):n.currentStyle).position}),t
		}
	}();c.mq=m;var v;!function()
	{
		var e=
		{
		}.hasOwnProperty;v=i(e,"undefined")||i(e.call,"undefined")?function(e,n)
		{
			return n in e&&i(e.constructor.prototype[n],"undefined")
		}:function(n,t)
		{
			return e.call(n,t)
		}
	}(),c._l=
	{
	},c.on=function(e,n)
	{
		this._l[e]||(this._l[e]=[]),this._l[e].push(n),Modernizr.hasOwnProperty(e)&&setTimeout(function(){Modernizr._trigger(e,Modernizr[e])},0)
	},c._trigger=function(e,n)
	{
		if(this._l[e])
		{
			var t=this._l[e];setTimeout(function(){var e,o;for(e=0;e<t.length;e++)(o=t[e])(n)},0),delete this._l[e]
		}
	},Modernizr._q.push(function(){c.addTest=f}),Modernizr.addTest("hovermq",m("(hover)")),s(),o(u),delete c.addTest,delete c.addAsyncTest;for(var g=0;g<Modernizr._q.length;g++)Modernizr._q[g]();e.Modernizr=Modernizr
}(window,document);

// Create an object of Modal class with an alert message as content

var sideWidth;
var opened="";
var prevScrollpos = window.pageYOffset;
var mobileDevice = false;
var touchDevice = false;
var tabletDevice = false;
var DPR = 1;

function getDevicePixelRatio() {
    var mediaQuery;
    var is_firefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    if (window.devicePixelRatio !== undefined && !is_firefox) {
    	$("body").addClass("R"+window.devicePixelRatio);
        return window.devicePixelRatio;
    } else if (window.matchMedia) {
        mediaQuery = "(-webkit-min-device-pixel-ratio: 1.5),\
          (min--moz-device-pixel-ratio: 1.5),\
          (-o-min-device-pixel-ratio: 3/2),\
          (min-resolution: 1.5dppx)";
        if (window.matchMedia(mediaQuery).matches) {
        	$("body").addClass("R1-5");
            return 1.5;
        }
        mediaQuery = "(-webkit-min-device-pixel-ratio: 2),\
          (min--moz-device-pixel-ratio: 2),\
          (-o-min-device-pixel-ratio: 2/1),\
          (min-resolution: 2dppx)";
        if (window.matchMedia(mediaQuery).matches) {
        	$("body").addClass("R2");
            return 2;
        }
        mediaQuery = "(-webkit-min-device-pixel-ratio: 0.75),\
          (min--moz-device-pixel-ratio: 0.75),\
          (-o-min-device-pixel-ratio: 3/4),\
          (min-resolution: 0.75dppx)";
        if (window.matchMedia(mediaQuery).matches) {
        	$("body").addClass("R0-7");
            return 0.7;
        }
    } else {
    	$("body").addClass("R1");
        return 1;
    }
}



if (
	navigator.userAgent.match(/Phone/i) ||
	navigator.userAgent.match(/DROID/i) ||
	navigator.userAgent.match(/Android/i) ||
	navigator.userAgent.match(/webOS/i) ||
	navigator.userAgent.match(/iPhone/i) ||
	navigator.userAgent.match(/iPod/i) ||
	navigator.userAgent.match(/BlackBerry/) ||
	navigator.userAgent.match(/Windows Phone/i) ||
	navigator.userAgent.match(/ZuneWP7/i) ||
	navigator.userAgent.match(/IEMobile/i)||
	navigator.userAgent.match(/Mobile/i)
)
{
	var mobileDevice = true; var touchDevice = true;
}

//touch/tablet detection
if (
	navigator.userAgent.match(/Tablet/i) ||
	navigator.userAgent.match(/iPad/i) ||
	navigator.userAgent.match(/Kindle/i) ||
	navigator.userAgent.match(/Playbook/i) ||
	navigator.userAgent.match(/Nexus/i) ||
	navigator.userAgent.match(/Xoom/i) ||
	navigator.userAgent.match(/SM-N900T/i) || //Samsung Note 3
	navigator.userAgent.match(/GT-N7100/i) || //Samsung Note 2
	navigator.userAgent.match(/SAMSUNG-SGH-I717/i) || //Samsung Note
	navigator.userAgent.match(/SM-T330NU/i) //Samsung Tab 4

)
{
	var tabletDevice = true; var touchDevice = true;
}



//get ready
$("document").ready( function()
	{

		if(mobileDevice)
		{
			$("html").addClass("mobile");
		}

		if(tabletDevice)
		{
			$("html").addClass("tablet");
		}

		if(typeof navigator.vendor!='undefined')
		{
			$("html").addClass("undef");
		}else if(!mobileDevice && !tabletDevice )
		{
			$("html").addClass("screen");
		}
		
		DPR = getDevicePixelRatio();
	});


$(document).on('submit', 'form[data-oc-toggle=\'ajax\']', function(e)
	{
		e.preventDefault();
		
		var element = this;

		var form = e.target;
		
		var action = $(form).attr('action');

		if (e.originalEvent !== undefined && e.originalEvent.submitter !== undefined)
		{
			var button = e.originalEvent.submitter;
		} else
		{
			var button = '';
		}

		var formaction = $(button).attr('formaction');

		if (formaction !== undefined)
		{
			action = formaction;
		}

		var method = $(form).attr('method');

		if (method === undefined)
		{
			method = 'post';
		}

		var enctype = $(element).attr('enctype');

		if (enctype === undefined)
		{
			enctype = 'application/x-www-form-urlencoded';
		}

		var html = $(button).html();
		var width = $(button).width();

		// https://github.com/opencart/opencart/issues/9690
		if (typeof CKEDITOR != 'undefined')
		{
			for (instance in CKEDITOR.instances)
			{
				CKEDITOR.instances[instance].updateElement();
			}
		}

		$.ajax(
			{
				url: action,
				type: method,
				data: $(form).serialize(),
				dataType: 'json',
				cache: false,
				contentType: enctype,
				processData: false,
				success: function(json)
				{
					$('.alert-dismissible').remove();
					$(form).find('.is-invalid').removeClass('is-invalid');
					$(form).find('.invalid-feedback').removeClass('d-block');

					console.log(json);

					if (json['redirect'])
					{
						location = json['redirect'].replaceAll('&amp;', '&');
					}

					if (typeof json['error'] == 'string')
					{
						$('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
						$('.btn-close').trigger('click');
						alert(json['error']);
					}

					if (typeof json['error'] == 'object')
					{
						if (json['error']['warning'])
						{
							$('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error']['warning'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
							alert(json['error']['warning'])
						}

						for (var key in json['error'])
						{
							$('#input-' + key.replaceAll('_', '-')).addClass('is-invalid').find('.form-control, .form-select, .form-check-input, .form-check-label').addClass('is-invalid');
							$('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');
						}
						
						if (!json['error']['warning']){
							firstInv = $('.is-invalid').first().attr('id');
							goToByScroll('#'+firstInv);
						}
						
						
						$('.btn-close').trigger('click');
					}

					if (json['success'])
					{
						$('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
						
						// Refresh
						var url = $(form).attr('data-oc-load');
						var target = $(form).attr('data-oc-target');

						if (url !== undefined && target !== undefined)
						{
							$(target).load(url);
						}
					}

					// Replace any form values that correspond to form names.
					for (var key in json)
					{
						$(form).find('[name=\'' + key + '\']').val(json[key]);
					}
				}
			});
	});

var loader =
{
	'loading': function(Ind)
	{
		$('#page-loader').height($(document).height()+'px');
		opened +=','+Ind;
		$('#page-loader').show();
		if($('#page-loader .spiner i').length==0)
		{
			$('#page-loader .spiner').html('<i class="fa fa-refresh fa-spin"></i><span>Please WAIT!</span>');
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
			if(xhr.responseText)
			{
				
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText)
			}

			return false;
		}
	});

$.ajaxPrefilter(function( options, originalOptions, jqXHR )
	{
		jqXHR.requestId = Math.floor( (Math.random() * 9999999999) + 1 )
	})

function goToByScroll(id)
{
	if (id !== undefined ){
		$('html,body').animate({
			scrollTop: $(id).offset().top - 87
		}, 'slow');
	}
	
}

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


$(document).ready(function() {
    // Tooltip
    /*
    var oc_tooltip = function() {
        // Get tooltip instance
        tooltip = bootstrap.Tooltip.getInstance(this);
        if (!tooltip) {
            // Apply to current element
            tooltip = bootstrap.Tooltip.getOrCreateInstance(this);
            tooltip.show();
        }
    }
    $(document).on('mouseenter', '[data-bs-toggle=\'tooltip\']', oc_tooltip);
	*/
    
    $(document).on('click', 'button', function() {
        $('.tooltip').remove();
    });

    // Date
    var oc_datetimepicker = function() {
        $(this).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function(start, end) {
            $(this.element).val(start.format('YYYY-MM-DD'));
        });
    }

    $(document).on('focus', '.date', oc_datetimepicker);

    // Time
    var oc_datetimepicker = function() {
        $(this).daterangepicker({
            singleDatePicker: true,
            datePicker: false,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'HH:mm'
            }
        }, function(start, end) {
            $(this.element).val(start.format('HH:mm'));
        }).on('show.daterangepicker', function(ev, picker) {
            picker.container.find('.calendar-table').hide();
        });
    }

    $(document).on('focus', '.time', oc_datetimepicker);

    // Date Time
    var oc_datetimepicker = function() {
        $('.datetime').daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'YYYY-MM-DD HH:mm'
            }
        }, function(start, end) {
            $(this.element).val(start.format('YYYY-MM-DD HH:mm'));
        });
    }

    $(document).on('focus', '.datetime', oc_datetimepicker);

    // Alert Fade
    var oc_alert = function() {
        window.setTimeout(function() {
            $('.alert-dismissible').fadeTo(3000, 0, function() {
                $(this).remove();
            });
        }, 3000);
    }

    $(document).on('click', 'button', oc_alert);
    $(document).on('click', 'change', oc_alert);
});

// Autocomplete
+function($) {
    $.fn.autocomplete = function(option) {
        return this.each(function() {
            var element = this;
            var $dropdown = $('#' + $(element).attr('data-oc-target'));

            this.timer = null;
            this.items = [];

            $.extend(this, option);

            // Focus in
            $(element).on('focusin', function() {
                element.request();
            });

            // Focus out
            $(element).on('focusout', function(e) {
                if (!e.relatedTarget || !$(e.relatedTarget).hasClass('dropdown-item')) {
                    $dropdown.removeClass('show');
                }
            });

            // Input
            $(element).on('input', function(e) {
                element.request();
            });

            // Click
            $dropdown.on('click', 'a', function(e) {
                e.preventDefault();

                var value = $(this).attr('href');

                if (element.items[value] !== undefined) {
                    element.select(element.items[value]);

                    $dropdown.removeClass('show');
                }
            });

            // Request
            this.request = function() {
                clearTimeout(this.timer);

                $('#autocomplete-loading').remove();

                $dropdown.prepend('<li id="autocomplete-loading"><span class="dropdown-item text-center disabled"><i class="fa-solid fa-circle-notch fa-spin"></i></span></li>');
                $dropdown.addClass('show');

                this.timer = setTimeout(function(object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 50, this);
            }

            // Response
            this.response = function(json) {
                var html = '';
                var category = {};
                var name;
                var i = 0, j = 0;

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        // update element items
                        this.items[json[i]['value']] = json[i];

                        if (!json[i]['category']) {
                            // ungrouped items
                            html += '<li><a href="' + json[i]['value'] + '" class="dropdown-item">' + json[i]['label'] + '</a></li>';
                        } else {
                            // grouped items
                            name = json[i]['category'];

                            if (!category[name]) {
                                category[name] = [];
                            }

                            category[name].push(json[i]);
                        }
                    }

                    for (name in category) {
                        html += '<li><h6 class="dropdown-header">' + name + '</h6></li>';

                        for (j = 0; j < category[name].length; j++) {
                            html += '<li><a href="' + category[name][j]['value'] + '" class="dropdown-item">' + category[name][j]['label'] + '</a></li>';
                        }
                    }
                }

                $dropdown.html(html);
            }
        });
    }
}(jQuery);


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

$(window).scroll(function()
	{

		var b = $(this).scrollTop();
		var c = $(this).height();

		if (b > 0)
		{
			var d = b + c / 2;
		}
		else
		{
			var d = 1 ;
		}
		if (d < 1e3 && d < c)
		{
			totop_button("off");
		}
		else
		{
			totop_button("on");
		}
		vHeight = $(document).height();

		var currentScrollPos = window.pageYOffset;
		//if (Modernizr.csscalc) {

		//}else{
		if ((prevScrollpos > currentScrollPos)||(currentScrollPos==0))
		{
			$("#navbar").removeClass('upnav');
			if (currentScrollPos==0)
			{
				$("#col-image-container").removeClass('upnav');
			}
		} else
		{
			$("#navbar").addClass('upnav');
			$("#col-image-container").addClass('upnav');
		}
		//}

		prevScrollpos = currentScrollPos;
		if(currentScrollPos > 250)
		{
			//$(".animate-cc").addClass('out-with-cc');
		}else
		{
			//$(".animate-cc").removeClass('out-with-cc');
		}



		jview.reSc(b);
	});

$(window).load(function(){$(window).trigger('resize')});

$(window).resize(function(){
		$('#page-loader').height($(document).height()+'px');
		$('#view-back').height($(document).height()+'px');
	});

$(document).on( "mouseover","li.amenu", function()
	{
		$(".woman-menu,.man-menu,.accessories-menu").removeClass('tagled');
	});

$(document).ready(function ()
	{
		$(".bytao_box img").dblclick(function(e)
			{
				e.preventDefault();
				var query = Modernizr.mq('(orientation: portrait)');
				if (query)
				{

				}else
				{
					//alert('none (-webkit-min-device-pixel-ratio: 2)');
				}
				return false
			});

		ItemCount = $('.top-cart > .cart-holder').data('id');
		
		if(ItemCount>'0')
		{
			$('.top-cart').css('display','inline-block');
		}
		cart.load();

		$(".mobil-search-button a").click(function(e)
			{
				SEARCH.open();
				return false
			});

		$("a.toScroll").click(function(e)
			{
				e.preventDefault();
				goToByScroll($(this).attr('href'));
				return false
			});

		$(".iframe-link").click(function (e)
			{
				e.preventDefault();
				loadUrl = $(this).attr('href');
				$.ajax(
					{
						url: loadUrl,
						dataType: 'json',
						beforeSend: function()
						{
							loader.loading();
						},
						complete: function()
						{
							loader.loaded();
						},
						success: function(json)
						{
							if (json['title'])
							{
								$('#info-content-header-title').html(json['title']);
							}
							if (json['view'])
							{
								$('#info-side-items').html(json['view']);
								info.open();
							}


						}
					});

				return false;
			});


		$("#trackit").click(function (e)
			{
				e.preventDefault();
				if(typeof navigator.platform!='undefined'){Platform = navigator.platform;}else{Platform ='undefined';}
		
				vWidth = screen.width;
				vHeight = screen.height;
				wHeight = $(window).height();
				wWidth = $(window).width();
				userAgent = navigator.userAgent;
				
				var cVals = { wHeight : wHeight, wWidth : wWidth, vWidth : vWidth, vHeight : vHeight,Platform:Platform,userAgent:userAgent,DPR : DPR }

				$.ajax({
						url: 'index.php?route=bytao/home.trackit',
						type: 'post',
						dataType: 'json',
						data: cVals,
						success: function(json)
						{
							
						}
					});
			});
			
		$("#up-btn").click(function (e)
			{
				e.preventDefault();
				$('body,html').animate({scrollTop:0},800,'swing');
			});

		$(".qua_top_menu").each(function()
			{
				$(this).parent().addClass('has-sub uper-item');
			});

		$("#search").parents('.uper-item').removeClass('uper-item');



		$("a.no-link, .top-search a").on('click', function(e)
			{
				e.preventDefault();
				return false;
			});

		$(".woman-menu > a,.man-menu > a,.accessories-menu > a").click(function (e)
			{
				e.preventDefault();
				if($(this).parent().hasClass('tagled'))
				{
					$(this).parent().removeClass('tagled');
				}else
				{
					$('.tagled').removeClass('tagled');
					$(this).parent().addClass('tagled');
				}
				return false;
			});

		
		$("a.woman-up").click(function (e)
			{
				e.preventDefault();
				if(!$(".woman-menu").hasClass('tagled'))
				{
					$('.tagled').removeClass('tagled');
					$(".woman-menu").addClass('tagled');
				}
				return false;
		});
		
		$("a.trig-woman-up").click(function (e){
			e.preventDefault();
			$('.woman-menu > a').trigger('click');
			if($('html').hasClass('mobile')|| $('html').hasClass('tablet')){
				menu.open();
			}
		})
		
		$("a.user-menu").click(function (e)
			{
				e.preventDefault();
				if(!$("a.user-menu").parent().hasClass('tagled'))
				{
					$('.tagled').removeClass('tagled');
					$("a.user-menu").parent().addClass('tagled');
				}else
				{
					$('.tagled').removeClass('tagled');
				}
				return false;
			});



		$("a.man-up").click(function (e){
				e.preventDefault();
				if(!$(".man-menu").hasClass('tagled'))
				{
					$('.tagled').removeClass('tagled');
					$(".man-menu").addClass('tagled');
				}
				return false;
		});
		
		$("a.trig-man-up").click(function (e){
			e.preventDefault();
			$('.man-menu  > a').trigger('click');
			if($('html').hasClass('mobile')|| $('html').hasClass('tablet')){
				menu.open();
			}
			
		});
		
		$("a.accessories-up").click(function (e)
			{
				e.preventDefault();
				if(!$(".accessories-menu").hasClass('tagled'))
				{
					$('.tagled').removeClass('tagled');
					$(".accessories-menu").addClass('tagled');
				}
				return false;
			});


		$(".no-hovermq a.man-up,.no-hovermq a.woman-up,.no-hovermq a.accessories-up").click(function (e)
			{
				menu.open();
				return false;
			});



		$("#view-back").on('click', function()
			{
				$('.tagled').removeClass('tagled');
			});

		$('.qua_top_menu a.active').each(function()
			{
				upper = $(this).parents('.top-item').children('a')
				if($(upper).hasClass("animated-text-decoration"))
				{
					$(upper).addClass('upper-parent');
				}

			});

		$("#menu-side-items .no-link > a").on('click', function(e)
			{
				e.preventDefault();

				if(! $(this).parent().children('.qua_top_menu').is(':visible'))
				{
					if($(this).parent().parents('.qua_top_menu').length==0)
					{
						$('.qua_top_menu').removeClass('active');
					}else
					{
						$(this).parent().parents('.qua_top_menu').find('.qua_top_menu').removeClass('active');
					}

					$(this).parent().children('.qua_top_menu').addClass('active');
					$(this).parent().parents('.qua_top_menu').addClass('active');

					$(this).parent().parent().find('.this-opened').removeClass('this-opened');
					$(this).parent().addClass('this-opened');
				}else
				{
					$(this).parent().children('.qua_top_menu').removeClass('active');
					$(this).parent().removeClass('this-opened');
				}


				return false;
			});

		$(document).mousemove(function( event )
			{
				if($(".qua_top_menu").is(":visible"))
				{
					$("#view-back").show();
				}else
				{
					$("#view-back").hide();
				}

			});


		$("nav").mouseout(function()
			{
				$(".w3-dropdown-content").slideToggle(300);
			});

		$(".cart-holder").mouseenter(function()
			{
				$(".cart-menu .count").css('animation-name','scroll-in')
			});

		$(".cart-menu").mouseout(function()
			{
				$(".cart-menu .count").css('animation-name','scroll-out')
			});

		/* Search */
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

		$('#search-side-items .search-submit').on('click', function()
			{
				url = $('base').attr('href') + 'index.php?route=product/search';

				var value = $('#search-side-items input[name=\'search\']').val();

				if (value)
				{
					url += '&search=' + encodeURIComponent(value);
				}
				location = url;
			});

		$('#search-side-items input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('#search-submit').trigger('click');
				}
			});

		$('#search-side-items input[name=\'search\']').focusout(function()
			{
				//$('#search').animate({top: -50}, 400);
			});

		$('#search-side-items input[name=\'search\']').parent().find('button').on('click', function()
			{
				url = $('base').attr('href') + 'index.php?route=product/search';

				var value = $('#search-side-items input[name=\'search\']').val();

				if (value)
				{
					url += '&search=' + encodeURIComponent(value);
				}

				location = url;
			});

		$('#search-side-items input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('#search-side-items input[name=\'search\']').parent().find('button').trigger('click');
				}
			});

		$('.top-search input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					$('.top-search input[name=\'search\']').parent().find('button').trigger('click');
				}
			});

		$('.top-search .search-submit').on('click', function()
			{
				url = $('base').attr('href') + 'index.php?route=product/search';

				var value = $('.top-search input[name=\'search\']').val();

				if (value)
				{
					url += '&search=' + encodeURIComponent(value);
				}
				location = url;
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

		$(document).on('click',function()
			{
				$('.mini-cart-wrapper').css('visibility','hidden')
			});

		$(document).on('click','.cart-item .remove',function(e){
				return false;
			});

		$(document ).on("click",".close",function(){$('.alert, .text-danger').remove();});
		$(document ).on("click","[data-bs-dismiss=\'modal\']",function(){$(this).parents('.w3-modal').remove();});
		

		loader.loaded();
		$('body').removeClass('lazyLoad');
	});

var jview =
{
	'reV': function()
	{
		vWidth = screen.width;
		vHeight = screen.height;
		wHeight = $(window).height();
		$('body').removeClass('dView tView mView');

		switch (true)
		{
			case (vWidth >= 0 && vWidth < mDeviceWidth ): cView='mView';break;
			case (vWidth >= mDeviceWidth && vWidth < 1024 ):  cView='tView';break;
			default:cView='dView';break;
		}
		$('body').addClass(cView);
		cc = $('.hovermq .product-cell:eq( 0 )').height();
		$('#products-sub #category-content').css('height',cc +'px');
		dd = $('#category-content').next().height();
		$('.landscape #products #category-content').css('height',dd +'px');
		$('.portrait #products #category-content').css('height',cc +'px');


		$('#page-loader').height($(document).height())

		$('.right-content').each(function()
			{
				thisHeight = $(this).outerHeight();
				parentHeight = $(this).parent().height();
				if(isMobile!='1')
				{
					if(parentHeight>thisHeight)
					{
						marginTop = ( parentHeight-thisHeight ) /2;
						$(this).css({'margin-top':marginTop+'px','margin-bottom':'120px'});
					}else
					{
						$(this).css({'margin-top':'80px','margin-bottom':'80px'});

					}
				}else
				{
					$(this).css({'margin-top':'80px','margin-bottom':'80px'});
				}
			});

		//$('#col-image').css({'min-height':vHeight+'px'});
		//$('#col-image').height($('#col-info').height());

		//zhP=$('.mView #pzHolder').innerHeight();
		//$('.mView .zhBtn').css('top',(parseInt(zhP)/2)+'px');

		if(vWidth < 1024)
		{
			sideWidth = vWidth - 20;
		}else
		{
			sideWidth = (vWidth/2) - 20;
		}

		$('.side-width').css('width',sideWidth+'px');

		var newHeight = $('#category-content').innerWidth();

		$('.product-cell-img').each(function()
			{
				$(this).height($(this).width());
				//$(this).height(sideWidth);
			})

		cc = $('.hovermq .product-cell:eq( 0 )').height();
		$('#products-sub #category-content').css('height',cc +'px');
		dd = $('#category-content').next().height();
		$('.landscape #products #category-content').css('height',dd +'px');
		$('.portrait #products #category-content').css('height',cc +'px');

		if (Modernizr.csscalc)
		{
			// supported


		} else
		{

			//$('.product-cell').css('height',((parseInt(wHeight)-88)/2)+'px');
			$('.hovermq .product-cell').css('height',((parseInt(wHeight)-88)/2)+'px');
			$('.hovermq .product-cell').css('padding','1px 1px 1px 1px');

			$('.hovermq .product-cell-img').css('height',((parseInt(wHeight)-87)/2)+'px');

			$('#category-content').css('height',((parseInt(wHeight)-88)/2)+'px');

			$('.product-product #category-content').css('height',(parseInt(wHeight)-26)+'px');
			$('.site-box-container').css('height',parseInt(wHeight)+'px');
			$('.hovermq #pzHolder').css('height',parseInt(wHeight)+'px');
			$('.hovermq #product-container').css('position','relative');
			//$('.hovermq #col-image').css({'position':'absolute','top':'auto','bottom':'0px'});

			$('.no-hovermq #pzHolder').css('height',parseInt(wHeight)+'px');
			$('.no-hovermq #product-container').css('position','relative');
			//$('.no-hovermq #col-image').css({'top':'auto','bottom':'auto'});
			//$('.no-hovermq #col-image').css({'top':'auto','bottom':'0px'});

			//$('#col-info').css('height',((parseInt(wHeight)-88)/2)+'px');

			$('.site-box-content').css('min-height',parseInt(wHeight)+'px');
			$('#col-title').css('min-height',(parseInt(wHeight)-87)+'px');
			$('.content-height').css('min-height',(parseInt(wHeight)-87)+'px');
		}


	}
	,
	'reSc':function(b)
	{

		if (Modernizr.csscalc)
		{
			// supported
		} else
		{
			wHeight = parseInt($(window).height());
			if(b>87)
			{
				$('.hovermq #col-image').css(
					{
						'top':'auto',
						'bottom':'0px'
					});
			}else
			{
				/*$('.no-hovermq #col-image').css({
				'top':'0px',
				'bottom':'auto'
				});
				*/
			}
		}
	}
	,
	'sizer':function()
	{

		var panImg=$("img.i");
		var hContainer = screen.height;
		var wContainer = screen.width;
		var Cls ='';
		pH= parseInt($('#pzHolder').height()) ;
		pW= parseInt($('#pzHolder').width()) ;

		if(pW < pH)
		{
			Cls='HW';
		}else if(pW > pH)
		{
			Cls='WH';
		}else
		{
			Cls='HH';
		}


		$('.pSlides').each(function()
			{
				if(! $(this).hasClass(Cls))
				{
					$(this).removeClass('HH WH HW').addClass(Cls)
				}

				var w = 0;
				var h = 0;
				var ww = 0;
				var hh = 0;

				tH = parseInt($(this).prop('naturalHeight'));
				tW = parseInt($(this).prop('naturalWidth'));

				switch(Cls)
				{
					case 'HW':
					mtop=0;
					h=pH;
					w=(h*tW)/tH;
					mleft = (pW - w)/2 ;
					//alert('HW width'+w+'px height'+h+'px margin-top'+mtop+'px margin-left'+mleft+'px')
					$(this).css({'width':w+'px','height':h+'px','margin-top':mtop+'px','margin-left':mleft+'px'});
					break;
					case 'WH':
					mleft=0;
					w=pW;
					h=(w*tH)/tW;
					mtop = (pH - h)/2 ;
					//alert('WH width'+w+'px height'+h+'px margin-top'+mtop+'px margin-left'+mleft+'px')
					$(this).css({'width':w+'px','height':h+'px','margin-top':mtop+'px','margin-left':mleft+'px'});
					break;
					case 'HH':
					if(pW<pH)
					{
						w=zHw;
						h=(w*tH)/tW;
						hh = (pH - h)/2 ;
					}else if(pW>pH)
					{
						h=pH;
						w=(h*tW)/tH;
						ww = (pW - w)/2 ;
					}else
					{
						h=w=pW;
					}
					$(this).css({'width':w+'px','height':h+'px','margin-top':mtop+'px','margin-left':mleft+'px'});
					break;
				}
				//$(this).animate({'width':w,'height':h,'margin-top':ww,'margin-left':hh},800,'swing');

				//	console.log('width'+w+'px'+'height'+h+'px'+'margin-top'+ww+'px'+'margin-left'+hh+'0px');
			});


	}
}

// Cart add remove functions
var cart =
{
	'open':function()
	{
		$("#side-content").show();
		$('html, body').css({overflow: 'hidden'});
		$("#side-cart-content").animate({
				marginRight: "0px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function(){});
	},

	'close':function()
	{
		var aRi= parseInt($("#side-cart-content").width());
		$("#side-cart-content").animate({
				marginRight: "-"+aRi+"px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},function(){
				$('html, body').attr('style','');
				$("#side-content").hide();
			});
	},

	'load':function()
	{
		$.ajax(
			{
				url: 'index.php?route=bytao/cart.getcart',
				type: 'post',
				dataType: 'json',
				success: function(json)
				{
					if(json['itemcount']==0){
						$('.top-cart').hide();
					}else{
						$('.top-cart').show();		
					}
					
					$('.count-holder .count').html(json['itemcount']);
					
					if(json['cart']){
						$('#cart-side-items').html(json['cart'])
					}
				}
			});
	},
	
	'loadOpen':function()
	{
		cart.load();
		$('html, body').animate({scrollTop: 0},function(){cart.open();});
	}
}

var voucher =
{
	'add': function()
	{

	},
	'remove': function(key)
	{
		$.ajax(
			{
				url: 'index.php?route=checkout/cart.remove',
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

var wishlist =
{
	'add': function(product_id)
	{
		$.ajax(
			{
				url: 'index.php?route=account/wishlist.add',
				type: 'post',
				data: 'product_id=' + product_id,
				dataType: 'json',
				success: function(json)
				{
					$('.alert').remove();
					
					if (json['success'])
					{
					
						$('#info-side-items').html('<div class="alert alert-success"> ' + json['success'] + '</div>');
						wishlist.open();
					}

					if (json['info'])
					{
						$('#content').parent().before('<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' + json['info'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					}

					$('#wishlist-total').html(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			});
	},
	'remove': function()
	{

	},
	'open':function()
	{
		$("#info-content").show();
		$('html, body').css({overflow: 'hidden'});
		$("#side-info-content").animate({
				marginRight: "0px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function(){});
	},
}

var menu =
{
	'open':function()
	{
		$("#menu-content").show();
		$('html, body').css({overflow: 'hidden'});
		$('.tree-side-menu-container .qua_top_menu a.active').parents('.top-item').addClass('this-opened');
		$('.this-opened > .qua_top_menu').addClass('active');
		$("#side-menu-content").animate(
			{
				marginRight: "0px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{

			});
	},
	
	'close':function()
	{
		var aRi= parseInt($("#side-menu-content").width());
		$("#side-menu-content").animate(
			{
				marginRight: "-"+aRi+"px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{
				$('html, body').attr('style','');
				$("#menu-content").hide();
				$("#view-back").hide();
				$('#side-menu-content .qua_top_menu').removeClass('active');
			});
	}
}

var SEARCH =
{
	'open':function()
	{
		$("#search-content").show();
		//$("#side-cart-content").width(480);
		$('html, body').css({overflow: 'hidden'});
		$("#side-search-content").animate(
			{
				marginLeft: "0px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{

			});
	},
	'close':function()
	{
		var aRi= parseInt($("#side-search-content").width());
		$("#side-search-content").animate(
			{
				marginLeft: "-"+aRi+"px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{
				$('html, body').attr('style','');
				$("#search-content").hide();
			});
	}
}

var info =
{
	'open':function()
	{
		$("#info-content").show();
		$('html, body').css({overflow: 'hidden'});
		$("#side-info-content").animate(
			{
				marginRight: "0px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{

			});
	},
	'close':function()
	{
		var aRi= parseInt($("#side-info-content").width());
		$("#side-info-content").animate(
			{
				marginRight: "-"+aRi+"px" ,
				duration: 1000,
				easing:'swing',
				queue: false
			},
			function()
			{
				$('html, body').attr('style','');
				$("#info-content").hide();
			});
	}
}