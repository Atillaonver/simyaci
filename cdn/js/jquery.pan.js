jQuery.fn.extend({
	panat: function(big){
		
		//$(".panWrapper").fadeIn("slow"); 
		$(document).scrollTop();
		$(".panWrapper").show();
        
        $(".panWrapper img.i").css("top","0px");
        $(".panWrapper img.i").attr("src",big);
        $(".panWrapper img.i").css("width",parseInt( parseInt(vWidth)));
		panInit(); 
		
		
		
		function panInit() {
		
		var panImg=$(".panWrapper img.i");
		var panWrapper=$(".panWrapper");
		
        var w = parseInt(panImg.css("width"));
        var h = parseInt(panImg.css("height"));
        var x = parseInt(panImg.css("left"));
        var y = parseInt(panImg.css("top"));

        var ml = 0 - (w - $(panWrapper).width());
        var mt = 0 - (h - $(panWrapper).height());

        var nl = parseInt((ml * parseInt(event.pageX)) / parseInt($(panWrapper).width()));
        var nt = parseInt((mt * parseInt(event.pageY)) / parseInt($(panWrapper).height()));
		
		if( parseInt($(panWrapper).width())>w && parseInt($(panWrapper).height())>h) {
			panImg.css("left", ((parseInt($(panWrapper).width()) - w)/2));
			panImg.css("top", ((parseInt($(panWrapper).height()) - h)/2));
		}
		else if(parseInt($(panWrapper).width())>w){
			panImg.css("left", ((parseInt($(panWrapper).width()) - w)/2));
			panImg.css("top", nt);
		}
		else if(parseInt($(panWrapper).height())>h){
			panImg.css("left", nl);
			panImg.css("top", ((parseInt($(panWrapper).height()) - h)/2));
		}
		else {
			panImg.css("left", nl);
			panImg.css("top", nt);
		}
    }
	}
});

jQuery.fn.extend({

    pan: function () {
	
	var panWrapper = document.createElement('div');
	$(panWrapper).addClass("panWrapper");
	
	var panLoader=document.createElement('div');
	
	$(panLoader).html('<div class="lds-ripple"><div></div><div></div></div>')
	$(panLoader).addClass("zoom-loader loader-opt");
	
	var panImg=document.createElement('img');
	$(panImg).addClass("i").css("position","absolute");
	
	var zi=document.createElement('a');
	$(zi).addClass("controls in");
	$(panWrapper).append(zi);
	
	var zo=document.createElement('a');
	$(zo).addClass("controls out");
	$(panWrapper).append(zo);
	
	var ne=document.createElement('a');
	$(ne).addClass("controls next");
	$(panWrapper).append(ne);
	
	var be=document.createElement('a');
	$(be).addClass("controls before");
	$(panWrapper).append(be);
	
	var close=document.createElement('a');
	$(close).addClass("controls close");
	$(panWrapper).append(close);
	
	$(panWrapper).append(panImg);
	$(panImg).hide();
	$(panWrapper).append(panLoader);
	$("body").append(panWrapper);
	
	
	$(this).click(function(e){
		e.preventDefault();
		$('html, body').css({overflow: 'hidden'});

		var t=$(this);
        var big = t.attr("data-big");
        nClss=$(this).attr('class');
        res=nClss.split(" ");
        var tSelector='';
        for(var i = 0; i < res.length; i++){
        	tSelector += "."+res[i];
        }
       
       	$(".panWrapper").data('class',tSelector);
       	$(".panWrapper").data('index',0);
       	
        
		$(".panWrapper").show();
        $(".panWrapper img.i").attr("src",big);
        $(".panWrapper img.i").css("top","0px");
        $(".panWrapper img.i").css("width",parseInt( parseInt(vWidth)));
		panInit(); 
		
		return false;
	});
	
	
	$(zi).click(function(e){
		var panImg=$(".panWrapper img.i");
		panImg.css("width",parseInt( parseInt(panImg.css("width"))*1.2));
		panInit(); 
	});
	
	$(zo).click(function(e){
		var panImg=$(".panWrapper img.i");
		panImg.css("width",parseInt( parseInt(panImg.css("width"))/1.2)+1);
		panInit(); 
	});
	
	$(ne).click(function(e){
       	var lInd = $(".panWrapper").data('index')+1;
       	nCls = $(".panWrapper").data('class');
       	console.log(nCls+' i:'+lInd);
       	All= $(nCls);
       	if(All.length > lInd){
       		$(".zoom-loader").show();
       		$(".panWrapper").data('index',lInd);
			nextImg = All[lInd];
	       	$(".panWrapper img.i").attr("src",$(nextImg).data('big'));
	        $(".panWrapper img.i").css("top","0px");
	        $(".panWrapper img.i").css("width",parseInt( parseInt(vWidth)));
			panInit();
		}
	});
	
	$(be).click(function(e){
		var lInd = $(".panWrapper").data('index')-1;
       	nCls = $(".panWrapper").data('class');
       	console.log(nCls+' i:'+lInd);
       	All= $(nCls);
       	if(lInd>=0){
       		$(".panWrapper").data('index',lInd);
			nextImg = All[lInd];
			$(".zoom-loader").show();
	       	$(".panWrapper img.i").attr("src",$(nextImg).data('big'));
	        $(".panWrapper img.i").css("top","0px");
	        $(".panWrapper img.i").css("width",parseInt( parseInt(vWidth)));
			panInit();
		}
	});
	
	$(".panWrapper img.i").on('load',function (e) { 
		$(panImg).show();
		 $(".zoom-loader").hide();
		 $(this).css("top","0px");
	});
	
	$(".panWrapper img.i").click(function (e) { 
		e.preventDefault();
		panInit(); 
		
	});
	
	$(close).click(function (e) { 
		$(".panWrapper").fadeOut("slow"); 
		$('html, body').attr('style','');
	});

	
    $(panWrapper).mousemove(function (e) { 
		panInit(); 
		console.log('mmove')
	});
	
	
	$(panWrapper).on('touchstart', function(e) {  
	    panInit();
	});
	
	$(panWrapper).mousewheel(function (evt) { 
		if(evt.deltaY>0)
			$(zo).click();
		else
			$(zi).click();
		panInit();
	});
	
	$("body").keydown(function (e) {
        if (e.keyCode == 27) {
            $(close).click();
        }
    });
    
	
	
	
	
	function panInit() {
		
		var panImg=$(".panWrapper img.i");
		var panWrapper=$(".panWrapper");
		
        var w = parseInt(panImg.css("width"));
        var h = parseInt(panImg.css("height"));
        var x = parseInt(panImg.css("left"));
        var y = parseInt(panImg.css("top"));

        var ml = 0 - (w - $(panWrapper).width());
        var mt = 0 - (h - $(panWrapper).height());

        var nl = parseInt((ml * parseInt(event.pageX)) / parseInt($(panWrapper).width()));
        var nt = parseInt((mt * parseInt(event.pageY)) / parseInt($(panWrapper).height()));
		
		if( parseInt($(panWrapper).width())>w && parseInt($(panWrapper).height())>h) {
			panImg.css("left", ((parseInt($(panWrapper).width()) - w)/2));
			panImg.css("top", ((parseInt($(panWrapper).height()) - h)/2));
		}
		else if(parseInt($(panWrapper).width())>w){
			panImg.css("left", ((parseInt($(panWrapper).width()) - w)/2));
			panImg.css("top", nt);
		}
		else if(parseInt($(panWrapper).height())>h){
			panImg.css("left", nl);
			panImg.css("top", ((parseInt($(panWrapper).height()) - h)/2));
		}
		else {
			panImg.css("left", nl);
			panImg.css("top", nt);
		}
		
		
    }
	
    }

});

(function(){
	var prefix = "", _addEventListener, onwheel, support;
	
    if ( window.addEventListener ) {
        _addEventListener = "addEventListener";
    } else {
        _addEventListener = "attachEvent";
        prefix = "on";
    }
 
    if ( document.onmousewheel !== undefined ) {
        support = "mousewheel";
    }
    
    try {
        WheelEvent("wheel");
        support = "wheel";
    } catch (e) {}
    if ( !support ) {
        support = "DOMMouseScroll";
    }
 
    window.addWheelListener = function( elem, callback, useCapture ) {
        _addWheelListener( elem, support, callback, useCapture );

        if( support == "DOMMouseScroll" ) {
            _addWheelListener( elem, "MozMousePixelScroll", callback, useCapture );
        }
    };
 
function _addWheelListener( elem, eventName, callback, useCapture ) {
        elem[ _addEventListener ]( prefix + eventName, support == "wheel" ? callback : function( originalEvent ) {
            !originalEvent && ( originalEvent = window.event );
			
            var event = {
                originalEvent: originalEvent,
                target: originalEvent.target || originalEvent.srcElement,
                type: "wheel",
                deltaMode: originalEvent.type == "MozMousePixelScroll" ? 0 : 1,
                deltaX: 0,
                delatZ: 0,
                preventDefault: function() {
                    originalEvent.preventDefault ?
                        originalEvent.preventDefault() :
                        originalEvent.returnValue = false;
                }
            };
             
            if ( support == "mousewheel" ) {
                event.deltaY = - 1/40 * originalEvent.wheelDelta;
                originalEvent.wheelDeltaX && ( event.deltaX = - 1/40 * originalEvent.wheelDeltaX );
            } else {
                event.deltaY = originalEvent.detail;
            }
 
            return callback( event );
 
        }, useCapture || false );
    }

	$.fn.mousewheel = function(handler) {
		return this.each(function() {
			window.addWheelListener(this, handler, true);
		});
	};
 })(jQuery);
 