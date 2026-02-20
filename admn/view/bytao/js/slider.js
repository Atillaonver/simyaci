var byA = 0;
var state=false;

var byS = {
	ajaxURL:'index.php?route=bytao/layerslider/ajaxget&user_token=' + getURLVar('user_token'),
	langID:0
	data : null, 
	currentLayer : null,
	currentSlide:0,
	countItem : 0,
	delayTime : 9000,
	siteURL : '',
	'setop':function(OPT){
		if(OPT['langID']) this.langID=OPT['langID'];
		if(OPT['siteURL']) this.langID=OPT['siteURL'];
		
	},
	'evt':function(){
		$('body').append('<div class="hidden" id="arr"></div>')
		
		$(document).delegate('.layer-style-item a','click',function(e){
			e.preventDefault();
			tClass = $(this).attr('by-class');
			$('#input-layer-class-'+byA).val(tClass);
			 byS.currentLayer.addClass( tClass );
			$(this).parents('.btn-group').removeClass('open');
			byS.storeCurrentLayerData(); 
			return false;
		});
		
		$(document).delegate('.btn-create','click',function(e){
			//byS.createLayer($(this).data('action'));
			var layer = byS.createLayer( $(this).attr("data-action"), null, ++byS.countItem );
		})
		
		$(document).delegate('.btn-delete','click',function(e){
			$('#tab-lang-'+byA+' .layer-active').remove();
			
		});
		
		$(document).delegate('.slider-top-clone','click',function(e){
			e.preventDefault();
			
			if($('#group-sliders-'+byA+' .slider-item.active').length>0){
				layerId = byS.currentSlide;
				$.ajax({
					url: 'index.php?route=bytao/layerslider/lyclone&layer_id='+layerId+'&lang='+byA+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(resp) {
						if(resp['error']){
							alert(resp['error']);
						}
						if( resp['newlayer'] ){
			 		  	$('#group-sliders-'+byA+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
			 		  	byS.newSlide();
			 		  }
					}	
				});
			}
			else
			{
				alert(noselected_text)
			}
			
		})
		
		//slayt background image change
		$(document).delegate('.btn-update-slider','click',function(e){
			e.preventDefault();
			lId = $('#languagetabs > .active').data('lid')
			
			$('#modal-image').remove();
			$.ajax({
				//url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&toData=slider_image-'+byA+ '&toThumb=simage-'+byA,
				//url: 'index.php?route=bytao/layerslider/filemanager&user_token=' + getURLVar('user_token') + '&target=slider_image-'+byA,
				url: 'index.php?route=bytao/layerslider/filemanager&user_token=' + getURLVar('user_token') + '&target=slider_image-'+lId+ '&thumb=simage-'+lId,
				dataType: 'html',
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
					//$('#dir-image').html(html);
					$('#modal-image').modal('show');
				}	
			});
		})
		
		//image layer change image
		$(document).delegate('.btn-change-img','click',function(e){
			byS.showDialogImage($(this).parent().find('img').attr('id'));
		})
		
		$(document).delegate('.btn-save-slider','click',function(e){
			byS.submitForm();
		});
		
		$(document).delegate('.btn-preview-slider','click',function(e){});
		
		$(document).delegate('.slider-item .image','click',function(e){
			e.preventDefault();
			byS.getSlide($(this).data('layer_id'));
		});
		
		$(document).delegate('.slider-clone','click',function(e){
			e.preventDefault();
			if($('#group-sliders-'+byA+' .slider-item.active').length>0){
				layerId=$(this).data('layer_id');
				$.ajax({
					url: 'index.php?route=bytao/layerslider/lyclone&layer_id='+layerId+'&lang='+byA+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(resp) {
						if(resp['error']){
							alert(resp['error']);
						}
						if( resp['newlayer'] ){
			 		  	$('#group-sliders-'+byA+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
			 		  	byS.newSlide();
			 		  }
					}	
				});
			}
			else
			{
				alert(noselected_text)
			}

		});
		
		$(document).delegate('.slider-new','click',function(e){
			e.preventDefault();
			$(this).parent().addClass('active');
			byS.newSlide();
		});
			
		$(document).delegate('.slider-status','click',function(e){
			e.preventDefault();
		});

		$(document).delegate('[name="layer_link_status"]','click',function(e){
			if($( this ).prop( "checked" ))
			{
				$('#layer_link_group').show();
			}else{
				$('#layer_link_group').hide();
			}
		});
		
		$(document).delegate('.slider-delete','click',function(e){
			e.preventDefault();
			var layerId=$(this).data('layer_id');
			if(confirm(confirm_delete)){
				$.ajax({
					url: 'index.php?route=bytao/layerslider|lydelete&layer_id='+layerId+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(RES) {
						if(RES['success']){
							if($('#slider-item-'+RES['success']).hasClass('active')){
								$('#slider-item-'+RES['success']).remove();
								byS.newSlide();
							}else{
								$('#slider-item-'+RES['success']).remove();
							}
							
							
						}
					}	
				});
			}
		});
		
		$(document).delegate('.layer-active .slider-timing','slidechange', function(e){
			byS.storeCurrentLayerData(); 
		}); 
		
		$(document).delegate(".btn-clear-pos",'click', function(){
			if(byS.currentLayer){
				byS.currentLayer.removeClass('pos'+$('.layer-align-table .active').data('h')+$('.layer-align-table .active').data('v'))
				$('#slider-form-'+ byA +' [name="layer_pos"]' ).val( '');
				$('.layer-align-table .active').removeClass('active');
				byS.currentLayer.css( 'left','1px');
				byS.currentLayer.css( 'top','1px');
				$( '[name="layer_top"]','#slider-form-'+byA ).val(  1 );
				$( '[name="layer_left"]','#slider-form-'+byA ).val(  1 );
				byS.storeCurrentLayerData();
			}
		});
		
		$(document).delegate("[ng-click]",'click', function(){
			if(!$(this).hasClass('active')){
				$(this).parents('.layer-align-table').find('.active').removeClass('active');
				$(this).addClass('active');
				eval($(this).attr('ng-click'));
				byS.setPosition(1,1);
 				byS.storeCurrentLayerData(); 
			}
				
 		});
		
		$(document).delegate(".btn-clear-style",'click', function(){	
			$('#input-layer-class-'+byA).val('');
			byS.storeCurrentLayerData(); 
		});	
		
		$(document).delegate(".layer-index .status",'click', function(){	
			if($(this).find('i').hasClass('fa-eye')){
				$(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
				byS.currentLayer.hide();
			}else{
				$(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
				byS.currentLayer.show();
			}
		});	
		
		$("#slider-editor-" + byA).attr('data-icount',$("#slider-editor-" + byA+".draggable-item").length);
		
		$('#timeline-'+ byA).parent().find('.t-end').html(byS.delayTime+'ms');
		
		$('#timeline-'+ byA).slider( {range: true,min: 0,max: byS.delayTime,values: [ 0 , byS.delayTime   ],	
										      slide: function( event, ui ) {
										      	$('#layer_start_time').val(ui.values[ 0 ])
										      	$('#layer_end_time').val(ui.values[ 1 ])
										      }
	 									} ); 
	}
	,
	'getSlide': function(layer_id){
		$.ajax({
			url: this.ajaxURL,
			type: 'post',
			data: 'layer_id=' + layer_id + '&t=s&a=g&lang=' + byA ,
			dataType: 'json',
			beforeSend: function() {},
			complete: function() {},			
			success: function(j) {
				var list  = j[''] ;
				if( list ) {
					byS.deleteCurrentLayer();
		 			$.paramseach( list, function(i, sValue ){
		 			 	switch(i){
							case 'slider_status':
							case 'slider_enable_link':
							case 'slider_videoplay':
								if(sValue=='1'){
									$( 'input[type="checkbox"][name="'+i+'"]' ).prop( "checked", true );
								}else{
									$( 'input[type="checkbox"][name="'+i+'"]' ).prop( "checked", false );
								}
								break;
							case 'slider_thumbnail':
								$('#'+i+'-'+byA).val(sValue);
								if(sValue!=''){
									$('#'+i+'-'+byA).parents('.image').find('img').attr('src',img_url+sValue)
								}else{
									$('#'+i+'-'+byA).parents('.image').find('img').attr('src',placeholder);
								}
								break;
							case 'slider_image':
								$('#'+i+'-'+byA).val(sValue);
								if(sValue!=''){
									$('#slider_image_src-'+byA).attr('src',img_url+sValue)
								}
								break;
							default:
								$('#'+i+'-'+byA).val(sValue);	
						}

		 			 });
	 			}
	 			$('#collapse-a').html(h_edit+': ' + $('[name="slider_title"]').val());
	 			byS.createList(j['layers'])
	 			byS.currentSlide = layer_id;
	 			byS.getCurrentSlideCount(layer_id);
			    /**/
				$('#tab-lang-'+byA+' .slider-top-clone ').removeClass('hidden');
				$('#group-sliders-'+byA+' > div.active ').removeClass('active');
				$('#group-sliders-'+byA+' > #slider-item-'+layer_id).addClass('active');
			}
		});
	}
	,
	'newSlide':function(){
		
		if(!$('#tab-lang-'+byA+' .slider-top-clone ').hasClass('hidden'))
		{
			$('#tab-lang-'+byA+' .slider-top-clone ').addClass('hidden');
			}
		$('[data-def]').each(function(){
			$(this).val($(this).data('def'));
		});
		$('[data-def-src]').each(function(){
			$(this).attr('src',$(this).data('def-src'));
		});
		$('#collapse-a').html(h_creat);
		$('#slider-editor-'+byA).empty();
		$('#layer-collection-'+byA).empty();
		$('#layer-form-'+ byA).hide();
		$('#group-sliders-'+byA+' .slider-item.active').removeClass('active');
		$('#slider_image_src-'+byA).attr('src',sSrc);
		byS.countItem=0;
		byS.createDefaultLayerData();
	}
	,
	'submitForm' : function(){
		
				 var data =[];
				 var i = 0;
				 var params = "id="+$("#slider_id-"+ byA).val()+"&"+$("#slider-editor-form-"+ byA).serialize()+"&";
				 var times = '';
				 $("#slider-editor-"+ byA +" .draggable-item" ).each( function(){
		 			var param = '';
		 			$.each( $(this).data("data-form"), function(_,e ) {
							if( $(this).attr('name').indexOf('layer_time') ==-1 ){
								if( e.name == 'layer_caption' ){
									 e.value = e.value.replace(/\&/,'_ASM_');
								}  
							param += 'layers['+i+']['+e.name+']='+e.value+'&';
							}
		 			}  );
		 			params += 	param+"&";
				 	i++
				 } );
				 
				 $(".input-time input", $("#slider-form-"+ byA) ).each( function(i,e){
				 	params +=$(e).attr('name')+"="+$(e).val()+"&";
				 	
				 	 
				 } ); 
				 params +="th="+tHeight;
				 $.ajax( {url:$("#slider-form-"+ byA).attr('action'),  dataType:"JSON",type: "POST", 'data':params}  ).done( function(resp){
			 		  if( resp['error'] ){
			 		  	alert(resp['error']);
			 		  }
			 		  
			 		  if(resp['newlayer'])
			 		  {
			 		  	$('#group-sliders-'+byA+' .slider-item.active').removeClass('active');
			 		  	$('#group-sliders-'+byA+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
			 		  	uL=resp['newlayer'] ;
			 		  	if(resp['slider_status']==0 ){
							$('#slider-item-'+uL+' .slider-status').addClass('slider-status-off');	
						}else{
							$('#slider-item-'+uL+' .slider-status').removeClass('slider-status-off');	
						}
						byS.newSlide();
			 		  }
			 		  
			 		  if(resp['updatelayer'])
			 		  {
			 		  	uL=resp['updatelayer'] ;
			 		  	$('#slider-item-'+uL).data('id',uL);
			 		  	$('#slider-item-'+uL+' [data-id]').data('id',uL);
			 		  	$('#slider-item-'+uL+' img').attr('src',resp['thumbnail']);
			 		  	$('#slider-item-'+uL+' div').html(resp['slider_title']);
			 		  	if(resp['slider_status']==0 ){
							$('#slider-item-'+uL+' .slider-status').addClass('slider-status-off');	
						}else{
							$('#slider-item-'+uL+' .slider-status').removeClass('slider-status-off');	
						}
						byS.newSlide();
			 		  }
				 } );
				
	}
	,
	'createList' : function( layers  ){
		state=false;
		var layer = '';
		if( layers ) {
 			 $.each( layers, function(i, jslayer ){
 			 	
 			 	var type = jslayer['layer_type']?'add-'+jslayer['layer_type']:'add-text';

		 		layer = byS.createLayer( type, jslayer , jslayer['layer_id'] );

		 		byS.countItem++;
 			 });
		}
		state = true;
 	}
 	,
 	'addLayerText' : function( layer, ilayer , caption ){  
		layer.addClass('layer-text');
		$(".caption-layer",layer ).html( caption );
		$("#slider-editor-" + byA ).append( layer );
		$("#layer_type-"+byA).val('text');
		$("#layer-collection-" + byA ).append( ilayer );
		$(".layer-index-caption", ilayer).html( caption );
	}
	,
	'addLayerVideo' : function( layer, ilayer , caption ){
		layer.addClass('layer-content');
		$(".caption-layer",layer ).html( caption );
		$("#slider-editor-" + byA ).append( layer );

		$("#layer-collection-" + byA ).append( ilayer ); $(".layer-index-caption", ilayer).html( caption );
		
		$("#layer_type-"+byA).val('video');
		layer.append( '<div class="layer_video" id="'+'video-'+layer.attr('id')+'"><div class="content-sample"></div></div><div class="btn-change-video">Chang Video</div>' );

	}
	,
	'addLayerImage' : function( layer, ilayer , caption ){
		layer.addClass('layer-content');
		$(".caption-layer",layer ).html( caption );
		layer.append( '<div class="layer_image" id="'+'img-'+layer.attr('id')+'"><div class="content-sample"></div></div><div class="btn-change-img">Change Image</div>' );

		$("#slider-editor-" + byA ).append( layer );
		$("#layer-collection-" + byA ).append( ilayer ); $(".layer-index-caption", ilayer).html( caption );
		
		$("#layer_type-"+byA).val('image');
		$("#layer_content-"+byA).val('');
		// show input form
		
	}
	,	
	'createLayer' : function( type, data, slayerID ){

 			var layer = $('<div class="draggable-item ms-caption"><div class="caption-layer"></div></div>');
	 		layer.attr('id','slayerID'+ slayerID ); 
	 		var ilayer = $('<div class="layer-index"></div>').attr("id","i-"+layer.attr("id"));
	 		ilayer.append( '<span class="status"><i class="fa fa-fw fa-eye"></i></span>' );
	 		ilayer.append( '<span class="i-no">'+($(".draggable-item",$("#slider-editor-" + byA)).length+1)+'</span>' );
	 		ilayer.append( '<span class="layer-index-caption"></span>' );
	 		ilayer.append( '<div class="hidden layerform"><input type="hidden" name="" value=""/></div>' );
	 		

	 		switch( type ){
	 			case 'add-text': 
	 				byS.addLayerText( layer , ilayer, "Your Caption Here " + slayerID );
	 				break;
	 			case 'add-video': 
	 				byS.addLayerVideo( layer , ilayer, "Your Video Here " + slayerID  );
	 				break;
	 			case 'add-image': 
	 				byS.addLayerImage(layer , ilayer,  "Your Image Here " + slayerID );
	 				break;	
	 			
	 		}
	 	 
	 		$("#layer_id-"+byA).val( slayerID );
	 		
	 		$('#timeline-'+byA).slider( 'values', 400*byS.countItem,byS.delayTime - (400*byS.countItem)  );
	 		
	 		byS.setCurrentLayerActive( layer );	
	 		//auto bind the drag and drap for this 
	 		
	 		$(layer).draggable({ containment: ".slider-editor-"+ byA,
	 							 drag:function(){
	 							 	byS.setCurrentLayerActive( layer );
	 							 	byS.updatePosition( layer.css('left'), layer.css("top") );
	 							 },
	 							 create:function(){
	 							 	byS.createDefaultLayerData( layer, data );
	 							 }
	 		});
	    	
	    	$('input,select,textarea', '#slider-form-'+ byA ).change( function(){  
				if( $(this).attr('name') =='layer_top' || $(this).attr('name') == 'layer_left' ) {  
					byS.currentLayer.css( { top:$('#layer-form-'+ byA+' [name="layer_top"]','#slider-form-'+ byA ).val()+"px",			
					 						  left:$('#layer-form-'+ byA+' [name="layer_left"]','#slider-form-'+ byA ).val()+"px"				
					 						});	
				}
				if( $(this).attr('name') =='layer_start_time') {  
					$('#timeline-'+byA).slider( 'values',0,$(this).val() );
				}
				if( $(this).attr('name') =='layer_end_time') {  
					$('#timeline-'+byA).slider( 'values',1,$(this).val() );
				}
					
				
				byS.storeCurrentLayerData();  
				
			});
			
			$('input,select,textarea', '#slider-form-'+ byA ).on('focusout', function(){  
				if( $(this).attr('name') =='layer_top' || $(this).attr('name') == 'layer_left' ) {  
					byS.currentLayer.css( { top:$('#layer-form-'+ byA+' [name="layer_top"]','#slider-form-'+ byA ).val()+"px",			
					 						  left:$('#layer-form-'+ byA+' [name="layer_left"]','#slider-form-'+ byA ).val()+"px"				
					 						});	
				}
				if( $(this).attr('name') =='layer_start_time') {  
					$('#timeline-'+byA).slider( 'values',0,$(this).val() );
				}
				if( $(this).attr('name') =='layer_end_time') {  
					$('#timeline-'+byA).slider( 'values',1,$(this).val() );
				}
					
				
				byS.storeCurrentLayerData();  
				
			});
			
	    	$('#input-slider-caption-'+ byA, '#slider-form-' + byA ).keypress( function(){  
				 
				 setTimeout(function ()
				 { 
				    $(".caption-layer",byS.currentLayer).html( $('#input-slider-caption-'+ byA).val()  );
				    
				 	$('.layer-index-caption',"#i-"+byS.currentLayer.attr("id") ).text( $(".caption-layer",byS.currentLayer).text() );	
				 }, 6);
				
			});


	 	    layer.click( function() { 
	 			byS.setCurrentLayerActive( layer );	 
	 		});
	 		
	 		$("#i-"+layer.attr("id") ).click( function(){
	 		  if( byS.currentLayer != null ){
	 		  	byS.storeCurrentLayerData();
	 		  }
	 		  byS.setCurrentLayerActive(layer); 
	 		} );
	 		
	 		if(type=="add-image"){
	 			if(state){
					byS.showDialogImage('img-'+layer.attr('id'));
				}
				
			}	
	 		
	}
	,		
	'updatePosition' : function( left, top ){
		_left = parseInt(left);
		_top = parseInt(top);
		
		ew=$('#slider-editor-'+ byA).width();
		eh=$('#slider-editor-'+ byA).height();
		
		if($('.layer-active','#slider-editor-'+byA).hasClass('poslefttop')){
			
		}
		if($('.layer-active').hasClass('poscentertop')){
			_left=parseInt(ew/2)-parseInt(left);
			
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('posrighttop')){
			_left = -parseInt(left);
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('posleftcenter')){
			_left=0;
			_top=parseInt(eh/2)-parseInt(top);
			
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('poscentercenter')){
			_left=parseInt(ew/2)-parseInt(left);
			_top=parseInt(eh/2)-parseInt(top);
			
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('posrightcenter')){
			_left = 0;
			_top = parseInt(eh/2);
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('posleftbottom')){
			_left = 0;
			_top = -parseInt(top);
			
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('poscenterbottom')){
			_left = parseInt(ew/2)-parseInt(left);
			_top = 0-parseInt(top);
			
		}
		if($('.layer-active','#slider-editor-'+byA).hasClass('posrightbottom')){
			_left = 0-parseInt(left);
			_top = 0-parseInt(top);
			
		}

		$( '[name="layer_top"]','#slider-form-'+ byA ).val(_top );
		$( '[name="layer_left"]','#slider-form-'+ byA ).val( _left );

		byS.storeCurrentLayerData();
	}
	,
	'setPosition' : function(_left,_top){
			
			
			ew=$('#slider-editor-'+ byA).width();
			eh=$('#slider-editor-'+ byA).height();
			
			if($('.layer-active','#slider-editor-'+byA).hasClass('poslefttop')){
				$('.layer-active','#slider-editor-'+byA).css({"left": _left, "top": _top});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('poscentertop')){
				_left=parseInt(ew/2)+parseInt(_left);
				$('.layer-active','#slider-editor-'+byA).css({"left": _left, "top": _top});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('posrighttop')){
				$('.layer-active','#slider-editor-'+byA).css({"left": "auto", "top": _top,'right':0});
			}
			if($('.layer-active').hasClass('posleftcenter')){
				//_left=0;
				_top=parseInt(eh/2)+parseInt(_top);
				$('.layer-active','#slider-editor-'+byA).css({"left": _left, "top": _top,'right':'auto'});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('poscentercenter')){
				_left = parseInt(ew/2)+parseInt(_left);
				_top = parseInt(eh/2)+parseInt(_top);
				console.log(_left + ' - '+_top);
				
				//$('.layer-active','#slider-editor-'+byA).css({"left": _left, "top": _top,'right':'auto','bottom':'auto'});
				byS.currentLayer.css({"left": _left, "top": _top,'right':'auto','bottom':'auto'});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('posrightcenter')){
				//_left = 0;
				_top = parseInt(eh/2)+parseInt(_top);
				$('.layer-active','#slider-editor-'+byA).css({"left":'auto' , "top": _top,'right': _left ,'bottom':'auto'});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('posleftbottom')){
				//_left = 0;
				//_top = 0;
				$('.layer-active','#slider-editor-'+byA).css({"left": _left , "top":'auto','right': 'auto','bottom': _top});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('poscenterbottom')){
				//_left = parseInt(ew/2);
				//_top = 0;
				$('.layer-active').css({"left":_left  , "top":'auto','right': 'auto','bottom': _top});
			}
			if($('.layer-active','#slider-editor-'+byA).hasClass('posrightbottom')){
				//_left = 0;
				//_top = 0;
				$('.layer-active','#slider-editor-'+byA).css({"left": 'auto' , "top":'auto','right': _left,'bottom': _top});
			}
			
			//byS.setPosition(0,0);
		}
	,
	'deleteCurrentLayer': function(){
			
			if( byS.currentLayer ){
				$( "#i-"+byS.currentLayer.attr("id") ).remove();
				//byS.currentLayer.remove();	
				byS.currentLayer.data( "data-form", null );
				$(".draggable-item",$('#slider-editor-'+byA)).remove();
				$(".layer-index",$('#layer-collection-'+byA)).remove();
				
				//byS.currentLayer = null;
				if( $(".draggable-item",$('#slider-editor-'+byA)).length <= 0 ) {
					$( "#layer-form-" + byA).hide();
					$('#dialog').remove();
					$('#dialog-video').hide();
				}
			}
	}
	,
	'showDialogImage' : function( thumb ){
		
			$('#modal-image').remove();
			$.ajax({
				//url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&toData=layer_image-' + byA+'&toDIMG=' + thumb+ '&toThumb=' + thumb+ '&toDo=byS.storeCurrentLayerData()',
				url: 'index.php?route=bytao/layerslider|filemanager&user_token=' + getURLVar('user_token') + '&toData=layer_image-' + byA+'&toDIMG=' + thumb+ '&toThumb=' + thumb+ '&toDo=byS.storeCurrentLayerData()',
				dataType: 'html',
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
					//$('#dir-image').html(html);
					$('#modal-image').modal('show');
				}
			});
	}
	,
	'setCurrentLayerActive' : function ( layer ){
		
			$("#slider-editor-"+byA+" .draggable-item").removeClass("layer-active");
	 		$( layer ).addClass("layer-active");
	 	 	
	 	 	$(".layer-index","#layer-collection-"+byA).removeClass("layer-active");
	 	 	
	 	 	$('#i-'+layer.attr("id") ).addClass("layer-active");
	 	 	
	 	 	byS.currentLayer = layer;
	 	 	byS.showLayerForm( layer );	
	}
	,
	'showLayerForm': function( layer ){
		 	 // restore data form for
		 	 var $currentLayer = byS.currentLayer;

			 if( $currentLayer.data("data-form") ){ 
			 	$.each( $currentLayer.data("data-form"), function(_, kv) {
			 		switch(kv.name){
			 			case 'layer_link_status':
			 				if(kv.value=='1'){
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", true );
									$('#layer_link_group').show();
								}else{
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", false );
									$('#layer_link_group').hide();
								}
			 				break;
						case 'layer_pos':
							$('#slider-form-'+ byA +' [name="'+kv.name+'"]' ).val( kv.value );
							if(kv.value){
								
								$('.layer-align-table .active').removeClass('active');
								var rst = kv.value.split(',');
								$('[data-h="'+rst[0]+'"][data-v="'+rst[1]+'"]').addClass('active');
								if($('.layer-active',layer).hasClass('pos'+rst[0]+rst[1])){
									$('.layer-active',layer).addClass('pos'+rst[0]+rst[1]);
								}
								
							}else{
								$('.layer-align-table .active').removeClass('active');
							}
							break;
						case 'layer_image':
							if($('.ms-caption.layer-active').find('.caption-layer').length>0){
								iId=$('.ms-caption.layer-active').find('.caption-layer').attr('id');
								$('#img-'+iId).replaceWith('<img src="' +img_url+kv.value+ '" alt="" id="img-'+iId+'" />');
							}
							break;
						default:
							$('#slider-form-'+ byA +' [name="'+kv.name+'"]' ).val( kv.value );
					}
					
				} ); 
			 }
 
			 $('#timeline-'+ byA).slider( {  min: 0, max: byS.delayTime,values: [ $('#layer_start_time').val() , $('#layer_end_time').val()  ]} ); 
			 
			 $('#layer-form-'+ byA).show();
	}
	,
	'createDefaultLayerData' : function( layer, data ){

 		if( data !=null && data ) { 
	 		$.each( data , function(key, valu){	
	 			switch(key){
	 				case 'layer_top':
	 					$( '[name="'+key+'"]','#slider-form-'+byA ).val(  valu );
	 					byS.currentLayer.css( 'top', valu+'px');	
	 					break;	
	 					
	 				case 'layer_left':
	 					$( '[name="'+key+'"]','#slider-form-'+byA ).val(  valu );
	 					byS.currentLayer.css( 'left', valu+'px');		
	 					break;	
	 					
	 				case 'layer_caption':
	 					valu = valu.replace( /_ASM_/,'&' );
	 					$( '[name="'+key+'"]','#slider-form-'+byA ).val(  valu );
	 					break;
	 					
	 				case 'layer_link_status':
			 				if(valu=='1'){
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", true );
									$('#layer_link_group').show();
								}else{
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", false );
									$('#layer_link_group').hide();
								}
			 				break;
			 				
			 		default:
			 			$( '[name="'+key+'"]','#slider-form-'+byA ).val(  valu );
				}
				
		 	} ); 

	 		if(  data['layer_type'] == 'image' ){
				var thumb = 'img-'+byS.currentLayer.attr('id');
				var src = img_url+data['layer_image'];
				$('#' + thumb).replaceWith('<img src="' + src + '" alt="" id="' + thumb + '" />');
				// this.siteURL 	
			}
			if(  data['layer_type'] == 'video' ){
				var thumb = 'video-'+byS.currentLayer.attr('id');
				var src = data['layer_video_thumb'];
				$(".content-sample",byS.currentLayer).html( '<img height="'+data['layer_video_height']+'" width="'+data['layer_video_width']+'" src="'+src+'"/>');
				// this.siteURL 	
			}
			if(  data['layer_type'] == 'text' ){
				 byS.currentLayer.addClass(  data['layer_class'] );
			}
			
			if(data['layer_pos'] != '' ){
				
				var rst = data['layer_pos'].split(',');
				if(!$('.layer-active','#slider-editor-'+byA).hasClass('pos'+rst[0]+rst[1])){
					$('.layer-active','#slider-editor-'+byA).addClass('pos'+rst[0]+rst[1]);
				}
				$('[data-h="'+rst[0]+'"][data-v="'+rst[1]+'"]').addClass('active');
				 byS.setPosition( data['layer_left'], data['layer_top']);
			}
			
			data['layer_caption'] = data['layer_caption'].replace(/_ASM_/,'&');
		 
			$(".caption-layer",byS.currentLayer).html( data['layer_caption'] );
			$(".layer-index-caption", '#i-slayerID'+data['layer_id']).text( $(".caption-layer",byS.currentLayer).text()  );

 			$("#timeline-1"+byA).slider( 'values', data['layer_start_time'],data['layer_end_time'] );

		 	byS.currentLayer = layer;
		 	
 			
 		}else {

			$('#layer-form-'+byA+' [name="layer_caption"]').val(  $(".caption-layer",layer).html() );
			$('#layer-form-'+byA+' [name="layer_left"]').val(  0 );
			$('#layer-form-'+byA+' [name="layer_top"]' ).val(  0 );
			$('#layer-form-'+byA+' [name="layer_class"]').val(  '' );
			$('#layer-form-'+byA+' [name="layer_start_speed"]' ).val(  350 );
			$('#layer-form-'+byA+' [name="layer_start_time"]' ).val(  0 );
			$('#layer-form-'+byA+' [name="layer_end_time"]' ).val(  byS.delayTime );
			$('#layer-form-'+byA+' [name="layer_end_speed"]').val(  300 );
			$('#layer-form-'+byA+' [name="layer_end_animation"]').val(  'auto' );
			$('#layer-form-'+byA+' [name="layer_end_easing"]' ).val(  'nothing' );
			$('#layer-form-'+byA+' [name="layer_content"]').val(  'no_image.png' );
	 	}
	 	
	 	byS.storeCurrentLayerData();
	  
	}
	,
	'storeCurrentLayerData':function(){

	 		 byS.state = false; 
	 		 byS.currentLayer.data("data-form", $('#slider-form-'+ byA ).serializeArray() );
	 		 
	}
	,
	'getCurrentSlideCount':function(SlideID){
		if($('#arr .A'+byA).find('.'+SlideID).length>0){
			byS.countItem = $('#arr .A'+byA).find('.'+SlideID).data('countItem');	
			
		}else{
			if($('#arr .A'+byA).length>0){
				$('#arr .A'+byA).append('<div class="'+SlideID+'"></div>');
			}else{
				$('#arr').append('<div class="A'+byA+'"><div class="'+SlideID+'"></div></div>');
			}
			$('#arr .A'+byA).find('.'+SlideID).data('countItem',0);
			byS.countItem =0;
			
		}
	}
	
}
