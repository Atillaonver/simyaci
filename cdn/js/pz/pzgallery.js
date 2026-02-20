(function(window, $)
{
	$(function()
	{
		var pzItems = $("[data-elem='pzItem']"),
			tempItem = $("[data-elem='tempItem']"),
			thumbScrollerElem = $("div[data-elem='thumbScroller']").eq(0),
			thumbHolder = $("div[data-elem='thumbHolder']").eq(0),
			fullscreenThumbHolder = $("div[data-elem='fullscreenThumbHolder']").eq(0),
			thumbScroller = null,
			pz = new PinchZoomer(tempItem),
			_index = -1,
			oldIndex = -1,
			blankThumbUrl = "../../assets/blank.jpg",
			defaultOptions = $.extend({}, pz.vars()),
			initObj = {};
		
		pzItems.detach();
		
		setupThumbs();
		
		setIndex(0);	
		
		pz.on(PinchZoomer.FULLSCREEN_TOGGLE, onFullscreenToggle);
		pz.on(PinchZoomer.LOAD_COMPLETE, initZoom);
		

		onFullscreenToggle();
        
		function onTipHandler(e)
		{
			if(e.type == "press")
			{
				$(e.target).tooltipster('open');
			}
			else
			{
				$(e.target).tooltipster('close');
			}
		}
		
		function onFullscreenToggle()
		{
			if(thumbScroller != null && fullscreenThumbHolder.length > 0)
			{
				
				if(pz.fullscreen())
				{
                    TweenLite.set(fullscreenThumbHolder, { autoAlpha:1 });
					fullscreenThumbHolder.append(thumbScrollerElem);
					thumbScroller.resetElem(true);
				}
				else
				{
					initZoom()
					setIndex(selIndex)
					TweenLite.set(fullscreenThumbHolder, { autoAlpha:0 });
					thumbHolder.append(thumbScrollerElem);
					thumbScroller.resetElem(true);
					$('.controlHolder').hide();
					
				}
			}
		}
		
		function setIndex(val)
		{
			if(val !== _index && val >= 0 && val < pzItems.length)
			{
				index = val;
				
				var pzItem = pzItems.eq(index);
				
				pz.vars($.extend({}, PinchZoomer.defaultVars, {adjustHeight:-fullscreenThumbHolder.height()}, defaultOptions));
				pz.elem(pzItem, false, false, true);
				
				if(thumbScroller != null)
				{
					thumbScroller.index(index)	
				}
			}
		}
		
		function initZoom()
		{
			pz.zoom(1, 0);
		}
		
		function setupThumbs()
		{
			if(thumbScrollerElem.length > 0)
			{
				var pzLen = pzItems.length,
					thumbs = [];
				for(var i = 0; i < pzLen; i++)
				{
					var pzItem = pzItems.eq(i);
					pzItem.data("parsed", false)
					//pzItem.data("scaleMode", widthOnly)
					//pzItem.data("adjustHolderSize", true)
					
					pzItem.data("obj", {})
					//thumbs.push({url:pzItem.data("thumb") || blankThumbUrl});
					
					var thumbElem = $.parseHTML("<img src='#' data-src='" + (pzItem.data("thumb") || blankThumbUrl) + "' class='" + pzItem.data("color")+"'/>");
					thumbs.push($(thumbElem));
				}
				var thumbVars = $.extend({initShow:true}, Utils.stringToObject("{" + thumbScrollerElem.data("options") + "}") );
				
				thumbScroller = new ThumbScroller(thumbScrollerElem, thumbs, thumbVars);
				thumbScroller.on(ThumbScroller.INDEX_CHANGE, onIndexChange);
			}
		}
		
		function onIndexChange()
		{
			var i = thumbScroller.index();
			
			setIndex(i);
		}
	
		function fullscreenToggle(){
			pz.fullscreen(true);
			pz.zoom(2, 0);
		}
		window.setIndex = setIndex;
		window.FullscreenToggle = fullscreenToggle;
	});
	
}(window, jQuery)); 