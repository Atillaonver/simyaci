( function($) {
  'use strict';



  	/*-------------------------------------------------------------------------------
	  Detect mobile device 
	-------------------------------------------------------------------------------*/



	var mobileDevice = false; 

	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
	  	$('html').addClass('mobile');
	  	mobileDevice = true;
	}

	else{
		$('html').addClass('no-mobile');
		mobileDevice = false;
	}



    /*-------------------------------------------------------------------------------
	  Window load
	-------------------------------------------------------------------------------*/



	$(window).load(function(){

		$('.loader').fadeOut(300);

	});

	var wow = new WOW({
	    offset: 150,          
	    mobile: false
	  }
	);
	
	wow.init();

	var navbarDesctop=$('.navbar-desctop');
	var navbarMobile=$('.navbar-mobile');



	/*-------------------------------------------------------------------------------
	  Affix
	-------------------------------------------------------------------------------*/



	navbarDesctop.affix({
	  offset: {
	    top: 200
	  }
	});


	navbarDesctop.on('affix.bs.affix', function() {
		if (!navbarDesctop.hasClass('affix')){
			navbarDesctop.addClass('animated slideInDown');
		}
	});

	navbarDesctop.on('affix-top.bs.affix', function() {
	  	navbarDesctop.removeClass('animated slideInDown');
	  	$('.navbar-collapse').collapse('hide');
	});

	
	$('.parallax-window').parallax();

	/*-------------------------------------------------------------------------------
	  Navbar Mobile
	-------------------------------------------------------------------------------*/



	navbarMobile.affix({
	  offset: {
	    top: 1
	  }
	});

	navbarMobile.on('affix.bs.affix', function() {
		if (!navbarMobile.hasClass('affix')){
			navbarMobile.addClass('animated slideInDown');
		}
	});

	navbarMobile.on('affixed-top.bs.affix', function() {
	  	navbarMobile.removeClass('animated slideInDown');
	  	
	});
	
	$('a.no-link').bind('click', function(){
			return false;
		});
	$('.navbar-nav-mobile li a[href="#"]').on('click',function(){
		$(this).closest('li').toggleClass('current');
		$(this).closest('li').find('ul').slideToggle(200);
		return false;
	});



	/*-------------------------------------------------------------------------------
	 Navbar collapse
	-------------------------------------------------------------------------------*/



	$('.navbar-collapse').on('show.bs.collapse', function () {
		navbarMobile.addClass('affix');
	});

	$('.navbar-collapse').on('hidden.bs.collapse', function () {
		if (navbarMobile.hasClass('affix-top')){
			navbarMobile.removeClass('affix');
		}
	});

	navbarMobile.on('affixed-top.bs.affix', function() {
		if ($('.navbar-collapse').hasClass('in')){
			navbarMobile.addClass('affix');
		}	
	});



	/*-------------------------------------------------------------------------------
	  Smooth scroll to anchor
	-------------------------------------------------------------------------------*/



    $('.js-target-scroll').on('click', function() {
        var target = $(this.hash);
        if (target.length) {
            $('html,body').animate({
                scrollTop: (target.offset().top -100)
            }, 1000);
            return false;
        }
    });




    /*-------------------------------------------------------------------------------
	  Object Map
	-------------------------------------------------------------------------------*/



	$('.object-label').on('click', function(){
		$('.object-label').not(this).find($('.object-info')).fadeOut(200);
		$(this).find('.object-info').fadeToggle(200);
	});



    /*-------------------------------------------------------------------------------
	  Parallax
	-------------------------------------------------------------------------------*/



	$(window).stellar({
	  	responsive: true,
	  	horizontalScrolling: false,
	  	hideDistantElements: false,
	  	horizontalOffset: 0,
	  	verticalOffset: 0,
	});


	/*-------------------------------------------------------------------------------
	  Ajax Form
	-------------------------------------------------------------------------------*/
	
$( document ).ready(function() {
		
		
		var id = 'newsletter_n';
		$('.'+id+' .box-heading').bind('click', function(){
			$('.'+id).toggleClass('active');
		});

		$('#formNewLestter').on('submit', function() {
			var email = $('.inputNew').val();
			$(".success_inline, .warning_inline, .error").remove();
			
			if(!isValidEmailAddress(email)) {				
			$('.valid').html("<div class=\"error alert alert-danger\">Error</div></div>");
			$('.inputNew').focus();
			return false;
		}
		var url = "index.php?rout=module/bytaonewsletter/subscribe";
		$.ajax({
			type: "post",
			url: url,
			data: $("#formNewLestter").serialize(),
			dataType: 'json',
			success: function(json)
			{
				$(".success_inline, .warning_inline, .error").remove();
				if (json['error']) {
					$('.valid').html("<div class=\"warning_inline alert alert-danger\">"+json['error']+"</div>");
				}
				if (json['success']) {
					$('.valid').html("<div class=\"success_inline alert alert-success\">"+json['success']+"</div>");
				}
			}
		});
		return false;
	});
});

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}


	if ($('.js-ajax-form').length) {
		
		$('.js-ajax-form').each(function(){
			sendUrl=$(this).data('action');
			$(this).validate({
				errorClass: 'error wobble-error',
			    submitHandler: function(form){
		        	$.ajax({
			            type: "POST",
			            url:sendUrl,
			            data: $(form).serialize(),
			            success: function() {
		                	$('.col-message, .success-message').show();
		                },

		                error: function(){
			                $('.col-message, .error-message').show();
			            }
			        });
			    }
			});
		});
	}
})(jQuery);