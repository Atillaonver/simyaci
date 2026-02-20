
function mMenu(mID) {
    var x = document.getElementById(mID);
    if (x.className.indexOf("w3-show") == -1) {  
        x.className += " w3-show";
    } else { 
        x.className = x.className.replace(" w3-show", "");
    }
}

function viewProp(){
	vWidth = $( window ).width() ;
	vHeight = $( window ).height() ;
	
	if( $('#container-nav > button').is(':visible')){
		if($( "#navmenu" ).hasClass("animated")){
			closeNav();
		}else{
			openNav();
		}
	}
	
}

$( window ).on("orientationchange", function(event) {
	orientation = window.orientation;
 	viewProp();
});
 
$(window).resize(function() {viewProp();});

if (!(/iphone|ipad/gi).test(navigator.appVersion)) {
	
}


function openNav(){
	var newW = $('#navmenu').width() - 47;
	
	$("#navmenu").animate({left: -newW},function() {
		$('#search').show();
		$('#pagin').show();
		$('#header-actions > li').show();
		
		
	   $('#container-nav > button').html('<i class="fa fa-bars"></i>')
	  });
}

function closeNav(){
		$("#navmenu").animate({left: "0"},function() {
			$('#header-actions > li').hide();
			$('#search').hide();
			$('#pagin').hide();
			
		   $('#container-nav > button').html('<i class="fa fa-times"></i>')
		  });
}

function w3_open() {
	if($( "#navmenu" ).hasClass("animated")){
		openNav();
		$("#navmenu" ).removeClass("animated");
	}else{
		$("#navmenu").addClass("animated");
		closeNav();
	}
}
