var pass=false;
jQuery.fn.extend({
	panIm: function(big){
		
		//$(".panWrapper").fadeIn("slow"); 
		$(document).scrollTop();
		$("#product-zoomed-image").show();
        
        $("#product-zoomed-image img.i").css("top","0px");
        $("#product-zoomed-image img.i").attr("src",big);
        $("#product-zoomed-image img.i").css("width",parseInt( parseInt(vWidth)));
		panInit(); 
		$("body").addClass("zoomed");
		$(".zoom-loader").hide();
		
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
	},
	
	panOn: function(){
		if($("body").hasClass('noscroll')){
			$("body").removeClass('noscroll');
		}else{
			$("body").addClass('noscroll');
		}
	},
	
	crt:function(){
		vWidth = $(document).width();
		vHeight = $(document).height();
		
		var panWrapper = document.createElement('div');
		$(panWrapper).attr("id","product-zoomed-image");
		
		var panLoader=document.createElement('div');
	
		$(panLoader).html('<div class="lds-ripple"><div></div><div></div></div>')
		$(panLoader).addClass("zoom-loader loader-opt");
		
		var panZoomOut=document.createElement('div');
	
		$(panZoomOut).html('<span class="zoom-out"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 36 36" style="enable-background:new 0 0 36 36; width: 36px; height: 36px;" xml:space="preserve"><polyline points="16,8 13,11 8,6 6,8 11,13 8,16 16,16 "></polyline><polyline points="28,16 25,13 30,8 28,6 23,11 20,8 20,16 "></polyline><polyline points="20,28 23,25 28,30 30,28 25,23 28,20 20,20 "></polyline><polyline points="8,20 11,23 6,28 8,30 13,25 16,28 16,20 "></polyline></svg></span>')
		$(panZoomOut).addClass("product-zoom expand");
	
		var panImg=document.createElement('img');
		$(panImg).addClass("i").css("position","absolute");
		
		
		
		$(panWrapper).append(panImg);
		//$(panImg).hide();
		$(panWrapper).append(panZoomOut);
		$(panWrapper).append(panLoader);
		$("body").append(panWrapper);
		//$(".pan").panOn();
		
		$('.zoom-out').on('click',function(){
			$(panWrapper).hide();
			//$(".pan").panOn();
			$("body").removeClass("zoomed noscroll");	
		});
		
		$( ".i" ).draggable({ 
			//containment: [0,0, vWidth, vHeight],
			distance:5,
			//dynamic: true,
			axis: "y",
			//helper:"clone",
			//refreshPositions: true,
			drag: function( event, ui ) {
				var panImg=$("img.i");
				var ph = parseInt($('#product-zoomed-image').height());
				var ih = parseInt($(panImg).height());
				
				var ut = parseInt(ui.position.top);
		        var mt =(ph - ih);
		        
		        //var y = parseInt(panImg.css("top"));
		        
				
				if(ut < mt){
					ui.position.top = mt;
				}else if(ut>0){
					ui.position.top = 0;
				}
			},
			stop: function( event, ui ) {
				//panInitl(ui)
			} 
		});
		
		
		
		function panInitl(ui) 
		{
		
			var panImg=$("img.i");
			var panWrapper=$(".panWrapper");
			
			var ph = parseInt($('#product-zoomed-image').height());
			var ih = parseInt($(panImg).height());
			
			var ut = parseInt(ui.position.top);
			var y = parseInt(panImg.css("top"));
	        var mt =(ph - ih);
	        
	        
			if(y>0){
				panImg.css("top", "0px");
			}else if(ut < mt){
				ui.position.top = mt;
				panImg.css("top", mt +"px");
			}
			
	    }
		
		
		return true;	
	},

	sizer:function(){
		
		var panImg=$("img.i");
		var hContainer = screen.height;
		var wContainer = screen.width;
		var Cls ='';
		pH= parseInt($('#pzHolder').height()) ;
		pW= parseInt($('#pzHolder').width()) ;
			
		if(pW < pH){
			Cls='HW';
		}else if(zHw > zHh){
			Cls='WH';
		}else{
			Cls='HH';
		}
		
		$('.pSlides').each(function(){
				var ww = 0;
				
				tH = parseInt($(this).prop('naturalHeight'));
				tW = parseInt($(this).prop('naturalWidth'));
						
				if(! $(this).hasClass(Cls)){
					$(this).removeClass('HH WH HW').addClass(Cls)
				}
				
				switch(Cls){
					case 'HW':
						hh=0;
						h=pH;
						w=(h*tW)/tH;
						ww = (pW - w)/2 ;
						$(this).css({'width':w+'px','height':h+'px','margin-top':'0px','margin-left':ww+'px'});
						//$(this).animate({'width':w,'height':h,'margin-top':ww,'margin-left':hh},800,'swing');
						break;
					case 'WH':
						ww=0;
						w=zHw;
						h=(w*tH)/tW;
						hh = (pH - h)/2 ;	
						$(this).css({'width':w+'px','height':h+'px','margin-top':hh+'px','margin-left':'0px'});
						//$(this).animate({'width':w,'height':h,'margin-top':ww,'margin-left':hh},800,'swing');
						break;
					case 'HH':
					
						if(pW<pH){
							w=zHw;
							h=(w*tH)/tW;
							hh = (pH - h)/2 ;
						}else if(pW>pH){
							h=pH;
							w=(h*tW)/tH;
							ww = (pW - w)/2 ;
						}else{
							h=w=pW;
						}
						$(this).css({'width':w+'px','height':h+'px','margin-top':ww+'px','margin-left':hh+'0px'});
						//$(this).animate({'width':w,'height':h,'margin-top':ww,'margin-left':hh},800,'swing');
						break;
				}
				//$('.w-h').html('pw:'+pW+' ph:'+pH+'w:'+w+' h:'+h+' hh:'+hh+' ww:'+ww)
				//console.log('pw:'+pW+' ph:'+pH+'w:'+w+' h:'+h+' hh:'+hh+' ww:'+ww);
			});
		
		
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


