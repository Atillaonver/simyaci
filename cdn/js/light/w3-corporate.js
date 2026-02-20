
$(document).scroll(function(){
   scrlFunction();
});

$(document).resize(function(){
   resized();
});
resized();
	
$(document).ready(function(){
	
    scrlFunction();
    moveItems= $("div[data-move-before]");
    $(moveItems).each(function(e){
    	$(this).insertBefore('#'+$(this).data('move-before')) ;
    });
    
    upItems= $("div[data-parent-class]");
    $(upItems).each(function(e){
    	$('#'+$(this).data('parent-id')).addClass($(this).data('parent-class'));
    });
    
});

function scrlFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    	$('#toTop').show();
    } else {
        $('#toTop').hide();
    }
	
	$('body').attr('scroll2',window.pageYOffset );	
  
  	if (window.pageYOffset >= 50) {
	    $('.accordion').parent().addClass("sticky")
	} else {
	    $('.accordion').parent().removeClass("sticky");
	}
    
}

function clickHref(URL) { location.href = URL;}

function resized(){
	if ($( window ).width() < 600) {
	    $('video').hide()
	}
}
