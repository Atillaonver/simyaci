;!function(e){e.fn.classes=function(t){var n=[];e.each(this,function(e,t){var r=t.className.split(/\s+/);for(var i in r){var s=r[i];if(-1===n.indexOf(s)){n.push(s)}}});if("function"===typeof t){for(var r in n){t(n[r])}}return n}}(jQuery);

function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

var byS = {
	ajaxURL:'index.php?route=bytao/layerslider/ajaxget&user_token=' + getURLVar('user_token'),
	lId:0, // language_id,
	state:false,
	data : null,
	Layers : null, 
	currentLayer : null,
	currentSlide:0,
	activeLayer:0,
	maxId:0,
	countItem : 0,
	delayTime : 9000,
	siteURL : '',
	'setOP':function(OPT){
		if(OPT['lId']) byS.lId = OPT['lId'];
		
		$('body').append('<div class="hidden" id="arr"></div>')
	
		
		$(document).on('click','.layer-style-item a',function(e){
			e.preventDefault();
			tClass = $(this).attr('by-class');
			$('#input-layer-class-'+byS.lId).val(tClass);
			 byS.currentLayer.addClass( tClass );
			$(this).parents('.btn-group').removeClass('open');
			byS.storeCurrentLayerData(); 
			return false;
		});
		
		$(document).on('click','.btn-create',function(e){
			 byS.maxId ++ ;
			var layer = byS.createLayer( $(this).attr("data-action"), null, byS.maxId );
		})
		
		$(document).on('click','.btn-delete',function(e){
			//$(this).data('action')
			byS.deleteCurrentLayer();
			
		});
		
		$(document).on('click','.layer-delete',function(e){
			byS.deleteCurrentLayer();
		});
		
		$(document).on('click','.slider-top-clone',function(e){
			e.preventDefault();
			
			if($('#group-sliders-'+byS.lId+' .slider-item.active').length>0){
				layerId = byS.currentSlide;
				$.ajax({
					url: 'index.php?route=bytao/layerslider/lyclone&layer_id='+layerId+'&lang='+byS.lId+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(resp) {
						if(resp['error']){
							alert(resp['error']);
						}
						if( resp['newlayer'] ){
			 		  	$('#group-sliders-'+byS.lId+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
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
		$(document).on('click','.btn-update-slider',function(e){
			e.preventDefault();

			$('#modal-image').remove();
			$.ajax({
				url: 'index.php?route=bytao/layerslider/filemanager&user_token=' + getURLVar('user_token') + '&target=slider_image-'+byS.lId+ '&image=simage-'+byS.lId,
				dataType: 'html',
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
					$('#modal-image').modal('show');
				}	
			});
		})
		
		//image layer change image
		$(document).on('click','.btn-change-img',function(e){
			byS.showDialogImage($(this).parent().find('img').attr('id'));
		})
		
		$(document).on('click','.btn-save-slider',function(e){
			byS.submitForm();
		});
		
		$(document).on('click','.btn-preview-slider',function(e){
			
			$('#previewModal .modal-dialog').css('width',1170);
			var a = $( '<span class="glyphicon glyphicon-refresh"></span><iframe frameborder="0" scrolling="no" src="'+$(this).attr('href')+'" style="width:100%;height:500px; display:none"/>'  );
			$('#previewModal .modal-body').html( a );
				
			$('#previewModal').modal('show');
			$('#previewModal').attr('rel', $(this).attr('rel') );
			$(a).load( function(){  
				$('#previewModal .modal-body .glyphicon-refresh').hide();
		 		$('#previewModal .modal-body iframe').show();
			});
			return false;
		});
		
		$(document).on('click','.slider-item .image',function(e){
			e.preventDefault();
			byS.getSlide($(this).data('layer_id'));
			
		});
		
		$(document).on('click','.slider-clone',function(e){
			e.preventDefault();
			if($('#group-sliders-'+byS.lId+' .slider-item.active').length>0){
				layerId=$(this).data('layer_id');
				$.ajax({
					url: 'index.php?route=bytao/layerslider/lyclone&layer_id='+layerId+'&lang='+byS.lId+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(resp) {
						if(resp['error']){
							alert(resp['error']);
						}
						if( resp['newlayer'] ){
			 		  	$('#group-sliders-'+byS.lId+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
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
		
		$(document).on('click','.slider-new',function(e){
			e.preventDefault();
			$(this).parent().addClass('active');
			byS.newSlide();
		});
			
		$(document).on('click','.slider-status',function(e){
			e.preventDefault();
			var status=0;
			if($(this).hasClass('slider-status-off')){
				$(this).removeClass('slider-status-off');
				status=1;
			}else{
				$(this).addClass('slider-status-off');
			}
			var layerID = $(this).parent().data('id');
			$.ajax({
					url: 'index.php?route=bytao/layerslider/savestatus&layer_id='+layerID+'&status='+status+'&user_token=' + getURLVar('user_token'),
					dataType: 'json',
					success: function(RES) {
						if(layerID == byS.currentSlide){
							$('slider_status-'+byS.lId).val(status);
						}
						
					}	
				});
		});

		$(document).on('click','.bytao-switch-btn input[type=checkbox]',function(e){
			if($( this ).prop( "checked" )){
				$('#'+$(this).attr('by-elem')).show();
			}else{
				$('#'+$(this).attr('by-elem')).hide();
			}
		});
		
		$(document).on('click','.slider-delete',function(e){
			e.preventDefault();
			var layerId=$(this).data('layer_id');
			
			if(confirm(confirm_delete)){
				
				$.ajax({
					url: 'index.php?route=bytao/layerslider/lydelete&layer_id='+layerId+'&user_token=' + getURLVar('user_token'),
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
		
		$(document).on('slidechange','.layer-active .slider-timing', function(e){
			byS.storeCurrentLayerData(); 
		}); 
		
		$(document).on('click', ".btn-clear-pos",function(){
			if(byS.currentLayer){
				byS.currentLayer.removeClass('pos'+$('.layer-align-table .active').data('h')+$('.layer-align-table .active').data('v'))
				$('#layer-form-'+ byS.lId +' [name="layer_pos"]' ).val( '');
				$('.layer-align-table .active').removeClass('active');
				byS.currentLayer.css( 'left','1px');
				byS.currentLayer.css( 'top','1px');
				$( '[name="layer_top"]','#layer-form-'+byS.lId ).val(  1 );
				$( '[name="layer_left"]','#layer-form-'+byS.lId ).val(  1 );
				byS.storeCurrentLayerData();
			}
		});
		
		$(document).on('click',"[ng-click]", function(){
			if(!$(this).hasClass('active')){
				$(this).parents('.layer-align-table').find('.active').removeClass('active');
				$(this).addClass('active');
				eval($(this).attr('ng-click'));
				byS.setPosition(1,1);
 				byS.storeCurrentLayerData(); 
			}
				
 		});
		
		$(document).on('click',".btn-clear-style", function(){	
			$("#layer-editor-" + byS.lId+" .draggable-item.layer-active").removeClass($('#input-layer-class-'+byS.lId).val());
			$('#input-layer-class-'+byS.lId).val('');
			//$("#layer-editor-" + byS.lId+".draggable-item.layer-active").classes(function(c) {});
			
			byS.storeCurrentLayerData(); 
		});	
		
		$(document).on('click',".layer-index .status", function(){	
			if($(this).find('i').hasClass('fa-eye')){
				$(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
				byS.currentLayer.hide();
			}else{
				$(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
				byS.currentLayer.show();
			}
		});	
		
		$(document).on('change keyup paste',".layer_caption", function(){
			dataFor=$(this).attr('data-for');	
			$( '[name="layer_caption"]','#layer-form-'+ byS.lId ).val($(this).val());
			$("#layer-editor-" + byS.lId+" .layer-active  ."+dataFor).html($(this).val());
			
			byS.storeCurrentLayerData();
		});	

		$(document).on('shown.bs.tab','#languagetabs a[data-toggle="tab"]', function (e) {
			byS.lId = $(e.target).parent().attr('data-lid');
		});
		
		$("#layer-editor-" + byS.lId).attr('data-icount',$("#layer-editor-" + byS.lId+".draggable-item").length);
		
		$('#timeline-'+ byS.lId).parent().find('.t-end').html(byS.delayTime+'ms');
		
		$('#timeline-'+ byS.lId).slider( {range: true,min: 0,max: byS.delayTime,values: [ 0 , byS.delayTime   ],	
										      slide: function( event, ui ) {
										      	$('#layer_start_time').val(ui.values[ 0 ])
										      	$('#layer_end_time').val(ui.values[ 1 ])
										      }
	 									} ); 
	}
	,
	'getSlide': function(layerslider_id){
		
		$.ajax({
			url: byS.ajaxURL,
			type: 'post',
			data: 'layerslider_id=' + layerslider_id + '&t=s&a=g&lang=' + byS.lId ,
			dataType: 'json',
			beforeSend: function() {},
			complete: function() {},			
			success: function(j) {
				byS.currentSlide = layerslider_id;
	 			byS.getCurrentSlideCount(layerslider_id);
	 			
				
				var list  = j['params'] ;
				if( list ) {
					byS.deleteLayers();
					$.each(list, function (key, sValue) {
					   
					    switch(key){
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
								$('#'+key+'-'+byS.lId).val(sValue);
								if(sValue!=''){
									$('#'+i+'-'+byS.lId).parents('.image').find('img').attr('src',img_url+sValue)
								}else{
									$('#'+i+'-'+byS.lId).parents('.image').find('img').attr('src',placeholder);
								}
								break;
							case 'slider_image':
								$('#'+key+'-'+byS.lId).val(sValue);
								if(sValue!=''){
									$('#slider_image_src-'+byS.lId).attr('src',img_url+sValue)
								}
								break;
							default:
								$('#'+key+'-'+byS.lId).val(sValue);
								 
						}
					   
					});
	 			}
	 			$('.bytao-switch-btn input[type=checkbox]').each(function(e){
					if($( this ).prop( "checked" )){
						$('#'+$(this).attr('by-elem')).show();
					}else{
						$('#'+$(this).attr('by-elem')).hide();
					}
				});
				$('#tab-lang-' + byS.lId + ' .slider-top-clone ').removeClass('hidden');
				$('#group-sliders-' + byS.lId + ' > div.active ').removeClass('active');
				$('#group-sliders-' + byS.lId + ' > #slider-item-'+layerslider_id).addClass('active');
				byS.createList(j['layers'])
			}
		});
	}
	,
	'newSlide':function(){
		
		if(!$('#tab-lang-'+byS.lId+' .slider-top-clone ').hasClass('hidden')){
			$('#tab-lang-'+byS.lId+' .slider-top-clone ').addClass('hidden');
		}
		byS.deleteLayers();
		
		$('[data-def]').each(function(){
			$(this).val($(this).data('def'));
		});
		
		$('[data-def-src]').each(function(){
			$(this).attr('src',$(this).data('def-src'));
		});
		
		
		$('#group-sliders-'+byS.lId+' .slider-item.active').removeClass('active');
		$('#slider_image_src-'+byS.lId).attr('src',sSrc);
		sliderSort();
		
		byS.countItem=0;
		byS.createDefaultLayerData();
	}
	,
	'createList' : function( layers  ){
		byS.state=false;
		var layer = '';
		if( layers ) {
 			 $.each( layers, function(i, jslayer ){
 			 	byS.maxId++;
 			 	var type = jslayer['layer_type']?'add-'+jslayer['layer_type']:'add-text';
 			 	layer = byS.createLayer( type, jslayer , 	byS.maxId );
		 		
 			 });
		}
		
		byS.state = true;
 	}
 	,
 	'createLayer' : function( type, data, slayerID ){

 			var layer = $('<div class="draggable-item tp-caption" data-form=""><div class="caption-layer"></div></div>');
	 		layer.attr('id','slayerID'+ slayerID ); 
	 		layer.attr('lid',slayerID ); 
	 		
	 		
	 		var ilayer = $('<div class="layer-index"></div>').attr("id","i-"+layer.attr("id"));
		 		ilayer.append( '<span class="status"><i class="fa fa-fw fa-eye"></i></span>' );
		 		ilayer.append( '<span class="i-no">'+($(".draggable-item",$("#layer-editor-" + byS.lId)).length+1)+'</span>' );
		 		ilayer.append( '<span class="layer-index-caption"></span>' );
		 		ilayer.append( '<div class="item-ranger"><div class="item-range" data-str="0" data-stp="'+byS.delayTime+'"></div></div>' );

	 		switch( type ){
	 			case 'add-text': 
	 				byS.addLayerText( layer , ilayer, "Yazı " + slayerID );
	 				break;
	 			case 'add-video': 
	 				byS.addLayerVideo( layer , ilayer, "Video " + slayerID  );
	 				break;
	 			case 'add-image': 
	 				byS.addLayerImage(layer , ilayer,  "Resim " + slayerID );
	 				break;	
	 		}


	 	 
	 		$("#layer_id-"+byS.lId).val( slayerID );
	 			 		
	 		byS.setCurrentLayerActive( layer );	
	 		
	 		$(layer).draggable({ containment: ".layer-editor-"+ byS.lId,
	 							 //handle: ".handle",
	 							 drag:function(){
	 							 	byS.setCurrentLayerActive( layer );
	 							 	byS.updatePosition( layer.css('left'), layer.css("top") );
	 							 },
	 							 create:function(){
	 							 	byS.createDefaultLayerData( layer, data );
	 							 	
	 							 }
	 		});
	    	
	    	$('input,select,textarea', '#layer-form-'+ byS.lId ).change( function(){  
				if( $(this).attr('name') =='layer_top' || $(this).attr('name') == 'layer_left' ) {  
					byS.currentLayer.css( { top:$('#layer-form-'+ byS.lId+' [name="layer_top"]','#layer-form-'+ byS.lId ).val()+"px",			
					 						  left:$('#layer-form-'+ byS.lId+' [name="layer_left"]','#layer-form-'+ byS.lId ).val()+"px"				
					 						});	
				}
				if( $(this).attr('name') =='layer_start_time') {  
					$('#timeline-'+byS.lId).slider( 'values',0,$(this).val() );
					$('#layer-collection-'+byS.lId+' .layer-active .item-range').data('str',$(this).val());
					byS.rangeSlider()
				}
				if( $(this).attr('name') =='layer_end_time') {  
					$('#timeline-'+byS.lId).slider( 'values',1,$(this).val() );
					$('#layer-collection-'+byS.lId+' .layer-active .item-range').data('stp',$(this).val());
					byS.rangeSlider()
				}

				byS.storeCurrentLayerData();  
				
			});
			
	    	$('#input-layer-caption-'+ byS.lId, '#layer-form-' + byS.lId ).keypress( function(){  
				 
				 setTimeout(function ()
				 { 
				    $(".caption-layer",byS.currentLayer).html( $('#input-slider-caption-'+ byS.lId).val()  );
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
	 		});
	 		
	 		if(type == "add-image" && data==null){
	 			
				byS.showDialogImage('img-slayerID'+layer.attr('lid'));
			}	
	 		
	}
	,
	'createDefaultLayerData' : function( layer, data ){

 		if( data != null && data ) { 
	 		$.each( data , function(key, valu){	
	 			switch(key){
	 				case 'layer_top':
	 					$( '[name="'+key+'"]','#layer-form-'+byS.lId ).val(  valu );
	 					byS.currentLayer.css( 'top', valu+'px');	
	 					break;	
	 					
	 				case 'layer_left':
	 					$( '[name="'+key+'"]','#layer-form-'+byS.lId ).val(  valu );
	 					byS.currentLayer.css( 'left', valu+'px');		
	 					break;	
	 					
	 				case 'layer_caption':
	 					valu = valu.replace( /_ASM_/,'&' );
	 					$( '[name="'+key+'"]','#layer-form-'+byS.lId ).val(  valu );
	 					break;
	 					
	 				case 'layer_link_status':
			 				if(valu=='1'){
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", true );
								}else{
									$( 'input[type="checkbox"][name="layer_link_status"]' ).prop( "checked", false );
								}
			 				break;
			 		default:
			 			$( '[name="'+key+'"]','#layer-form-'+byS.lId ).val(  valu );
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
				if(!$('.layer-active','#layer-editor-'+byS.lId).hasClass('pos'+rst[0]+rst[1])){
					$('.layer-active','#layer-editor-'+byS.lId).addClass('pos'+rst[0]+rst[1]);
				}
				$('[data-h="'+rst[0]+'"][data-v="'+rst[1]+'"]').addClass('active');
				 byS.setPosition( data['layer_left'], data['layer_top']);
			}
			
			data['layer_caption'] = data['layer_caption'].replace(/_ASM_/,'&');
		 
			$(".caption-layer",byS.currentLayer).html( data['layer_caption'] );
			$(".layer-index-caption", '#i-slayerID'+data['layer_id']).text( $(".caption-layer",byS.currentLayer).text()  );

 			
 			$('#timeline-'+ byS.lId).slider( "values", 0, data['layer_start_time'])
			$('#timeline-'+ byS.lId).slider( "values", 1, data['layer_end_time'] )

		 	byS.currentLayer = layer;
		 	
 			
 		}else {

			$('#layer-form-'+byS.lId+' [name="layer_caption"]').val(  $(".caption-layer",layer).html() );
			$('#layer-form-'+byS.lId+' [name="layer_left"]').val(  0 );
			$('#layer-form-'+byS.lId+' [name="layer_top"]' ).val(  0 );
			$('#layer-form-'+byS.lId+' [name="layer_class"]').val(  '' );
			$('#layer-form-'+byS.lId+' [name="layer_start_speed"]' ).val(  350 );
			$('#layer-form-'+byS.lId+' [name="layer_start_time"]' ).val(  0 );
			$('#layer-form-'+byS.lId+' [name="layer_end_time"]' ).val(  byS.delayTime );
			$('#layer-form-'+byS.lId+' [name="layer_end_speed"]').val(  300 );
			$('#layer-form-'+byS.lId+' [name="layer_end_animation"]').val(  'auto' );
			$('#layer-form-'+byS.lId+' [name="layer_end_easing"]' ).val(  'nothing' );
			$('#layer-form-'+byS.lId+' [name="layer_content"]').val(  'no_image.png' );
			$('#timeline-'+ byS.lId).slider( "values", 0,0)
			$('#timeline-'+ byS.lId).slider( "values", 1, byS.delayTime )
			
	 	}
	 	
	 	$('.bytao-switch-btn input[type=checkbox]').each(function(e){
			if($( this ).prop( "checked" )){
				$('#'+$(this).attr('by-elem')).show();
			}else{
				$('#'+$(this).attr('by-elem')).hide();
			}
		});
	 	
	 	byS.storeCurrentLayerData();
	  	byS.rangeSlider();
	}
	,		
 	'addLayerText' : function( layer, ilayer , caption ){  
		layer.addClass('layer-text');
		$(".caption-layer",layer ).html( caption );
		$("#layer-editor-" + byS.lId ).append( layer );
		$("#layer_type-"+byS.lId).val('text');
		$("#layer-collection-" + byS.lId ).append( ilayer );
		$(".layer-index-caption", ilayer).html( caption );
	}
	,
	'addLayerVideo' : function( layer, ilayer , caption ){
		layer.addClass('layer-content');
		$(".caption-layer",layer ).html( caption );
		$("#layer-editor-" + byS.lId ).append( layer );

		$("#layer-collection-" + byS.lId ).append( ilayer ); $(".layer-index-caption", ilayer).html( caption );
		
		$("#layer_type-"+byS.lId).val('video');
		layer.append( '<div class="layer_video" id="'+'video-'+layer.attr('id')+'"><div class="content-sample"></div></div><div class="btn-change-video">Chang Video</div>' );

	}
	,
	'addLayerImage' : function( layer, ilayer , caption ){
		layer.addClass('layer-content');
		$(".caption-layer",layer ).html( caption );
		layer.append( '<div class="layer_image" id="'+'img-'+layer.attr('id')+'"><div class="content-sample"></div></div><div class="btn-change-img">Change Image</div>' );

		$("#layer-editor-" + byS.lId ).append( layer );
		$("#layer-collection-" + byS.lId ).append( ilayer ); $(".layer-index-caption", ilayer).html( caption );
		
		$("#layer_type-"+byS.lId).val('image');
		$("#layer_content-"+byS.lId).val('');
		// show input form
		
	}
	,	
	'updatePosition' : function( left, top ){
		_left = parseInt(left);
		_top = parseInt(top);
		
		ew=$('#layer-editor-'+ byS.lId).width();
		eh=$('#layer-editor-'+ byS.lId).height();
		
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poslefttop')){
			
		}
		if($('.layer-active').hasClass('poscentertop')){
			_left=parseInt(ew/2)-parseInt(left);
			
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrighttop')){
			_left = -parseInt(left);
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posleftcenter')){
			_left=0;
			_top=parseInt(eh/2)-parseInt(top);
			
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poscentercenter')){
			_left=parseInt(ew/2)-parseInt(left);
			_top=parseInt(eh/2)-parseInt(top);
			
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrightcenter')){
			_left = 0;
			_top = parseInt(eh/2);
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posleftbottom')){
			_left = 0;
			_top = -parseInt(top);
			
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poscenterbottom')){
			_left = parseInt(ew/2)-parseInt(left);
			_top = 0-parseInt(top);
			
		}
		if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrightbottom')){
			_left = 0-parseInt(left);
			_top = 0-parseInt(top);
			
		}

		$( '[name="layer_top"]','#layer-form-'+ byS.lId ).val(_top );
		$( '[name="layer_left"]','#layer-form-'+ byS.lId ).val( _left );

		byS.storeCurrentLayerData();
	}
	,
	'setPosition' : function(_left,_top){
			
			
			ew=$('#layer-editor-'+ byS.lId).width();
			eh=$('#layer-editor-'+ byS.lId).height();
			
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poslefttop')){
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": _left, "top": _top});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poscentertop')){
				_left=parseInt(ew/2)+parseInt(_left);
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": _left, "top": _top});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrighttop')){
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": "auto", "top": _top,'right':0});
			}
			if($('.layer-active').hasClass('posleftcenter')){
				//_left=0;
				_top=parseInt(eh/2)+parseInt(_top);
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": _left, "top": _top,'right':'auto'});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poscentercenter')){
				_left = parseInt(ew/2)+parseInt(_left);
				_top = parseInt(eh/2)+parseInt(_top);
				
				
				//$('.layer-active','#layer-editor-'+byS.lId).css({"left": _left, "top": _top,'right':'auto','bottom':'auto'});
				byS.currentLayer.css({"left": _left, "top": _top,'right':'auto','bottom':'auto'});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrightcenter')){
				//_left = 0;
				_top = parseInt(eh/2)+parseInt(_top);
				$('.layer-active','#layer-editor-'+byS.lId).css({"left":'auto' , "top": _top,'right': _left ,'bottom':'auto'});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posleftbottom')){
				//_left = 0;
				//_top = 0;
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": _left , "top":'auto','right': 'auto','bottom': _top});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('poscenterbottom')){
				//_left = parseInt(ew/2);
				//_top = 0;
				$('.layer-active').css({"left":_left  , "top":'auto','right': 'auto','bottom': _top});
			}
			if($('.layer-active','#layer-editor-'+byS.lId).hasClass('posrightbottom')){
				//_left = 0;
				//_top = 0;
				$('.layer-active','#layer-editor-'+byS.lId).css({"left": 'auto' , "top":'auto','right': _left,'bottom': _top});
			}
			
			//byS.setPosition(0,0);
		}
	,
	'deleteAction': function(action){
		switch(action){
			case 'delete-layer':
				byS.deleteCurrentLayer();
			break;
			case 'delete-slider':
				byS.deleteCurrentSlider();
			break;
			
		}
	}
	,
	'deleteCurrentSlider': function(){
			
			if( byS.currentLayer ){
				byS.currentLayer.data( "form", null );
				$( "#i-"+byS.currentLayer.attr("id") ).remove();
				$(".draggable-item",$('#layer-editor-'+byS.lId)).remove();
				$(".layer-index",$('#layer-collection-'+byS.lId)).remove();
				byS.newSlide();
			}
	}
	,
	'deleteCurrentLayer': function(){
			
			if( byS.currentLayer ){
				byS.currentLayer.data( "form", null );
				$( "#i-"+byS.currentLayer.attr("id") ).remove();
				$(".draggable-item.layer-active",$('#layer-editor-'+byS.lId)).remove();
				$(".layer-index.layer-active",$('#layer-collection-'+byS.lId)).remove();
				
				if( $('.draggable-item',$('#layer-editor-'+byS.lId)).length <= 0 ) {
					$('#dialog').remove();
					$('#dialog-video').hide();
				}
			}
	}
	,
	'deleteLayers': function(){
		
		$('#layer-editor-'+byS.lId).empty();
		$('#layer-collection-'+byS.lId).empty();
		if( byS.currentLayer ) byS.currentLayer.data( "form", null );	
	}
	,
	'showDialogImage' : function( thumb ){
			byS.currentLayer
			$('#modal-image').remove();
			$.ajax({
				url: 'index.php?route=bytao/layerslider/filemanager&user_token=' + getURLVar('user_token') + '&target=layer_image-' + byS.lId +'&thumb='+thumb ,
				dataType: 'html',
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
					$('#modal-image').modal('show');
				}
			});
	}
	,
	'setCurrentLayerActive' : function ( layer ){
		
		if(byS.activeLayer != $(layer).attr('lid')){
			if(byS.state) byS.updateLayerDataForm();
			$("#layer-editor-"+byS.lId+" .draggable-item").removeClass("layer-active");
	 	 	$(".layer-index","#layer-collection-"+byS.lId).removeClass("layer-active");
	 	 	$('#i-'+layer.attr("id") ).addClass("layer-active");
	 	 	$( layer ).addClass("layer-active");
	 	 	
	 	 	byS.activeLayer = $(layer).attr('lid');
	 	 	byS.currentLayer = layer;
	 	 	byS.showLayerForm( layer );	
	 	 	byS.rangeSlider();
		}
			
	}
	,
	'showLayerForm': function( layer ){
		 	 
		 	 var $currentLayer = byS.currentLayer;
			 if( $currentLayer.data("form") ){ 
			 	
			 	$.each( $currentLayer.data("form"), function(_, kv) {
			 		
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
							$('#layer-form-'+ byS.lId +' [name="'+kv.name+'"]' ).val( kv.value );
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
							if($('.tp-caption.layer-active').find('.caption-layer').length){
								iId = $('.tp-caption.layer-active').find('.caption-layer').attr('id');
								$('#img-'+iId).replaceWith('<img src="' +img_url+kv.value+ '" alt="" id="img-'+iId+'" />');
							}
							$('#layer-form-'+ byS.lId +' [name="'+kv.name+'"]' ).val( kv.value );
							break;
						default:
							$('#layer-form-'+ byS.lId +' [name="'+kv.name+'"]' ).val( kv.value );
					}
					
				} ); 
			 }
			 
			$('#timeline-'+ byS.lId).slider( "values", 0,$('#layer_start_time-'+ byS.lId).val() )
			$('#timeline-'+ byS.lId).slider( "values", 1,$('#layer_end_time-'+ byS.lId).val() )
			$('.bytao-switch-btn input[type=checkbox]').each(function(e){
				if($( this ).prop( "checked" )){
					$('#'+$(this).attr('by-elem')).show();
				}else{
					$('#'+$(this).attr('by-elem')).hide();
				}
			});
			$('.layer-form').show();
	}
	,
	'storeCurrentLayerData':function(){
	 		 byS.state = false; 
	 		 byS.currentLayer.data("form", $('#layer-form-'+ byS.lId ).serializeArray() );
	 		
	}
	,
	'rangeSlider':function(){
			var crpn = byS.delayTime / 154;
			var iStart =  parseInt($('#timeline-'+ byS.lId).slider( "values", 0 ));
			var iStop =  parseInt($('#timeline-'+ byS.lId).slider( "values", 1 ));
			var iWidth =parseInt((iStop - iStart)*154/ byS.delayTime) ; 
			
			$('#layer-collection-'+byS.lId+' .layer-active .item-range').css({'left':parseInt(iStart*154/byS.delayTime)+'px','width':+iWidth+'px'});
	}
	,
	'updateLayerDataForm':function(){
 			if($("#layer-editor-"+byS.lId+" .draggable-item.layer-active").length){
				byS.currentLayer.data("form", $('#layer-form-'+ byS.lId ).serializeArray() );
			}
	}
	,
	'getCurrentSlideCount':function(SlideID){
		if($('#arr .A'+byS.lId).find('.'+SlideID).length>0){
			byS.countItem = $('#arr .A'+byS.lId).find('.'+SlideID).data('countItem');	
			
		}else{
			if($('#arr .A'+byS.lId).length>0){
				$('#arr .A'+byS.lId).append('<div class="'+SlideID+'"></div>');
			}else{
				$('#arr').append('<div class="A'+byS.lId+'"><div class="'+SlideID+'"></div></div>');
			}
			$('#arr .A'+byS.lId).find('.'+SlideID).data('countItem',0);
			byS.countItem =0;
			
		}
	}
	,
	'submitForm' : function(){
			layerSliderId = $("#slider_id-"+ byS.lId).val();
			if(!layerSliderId){	layerSliderId=0;}
			var data =[];
			var i = 0;
			var params = "slider_id="+layerSliderId+"&"+$("#slayt-form-"+ byS.lId).serialize()+"&";
			var times = '';
			 
			$("#layer-editor-"+ byS.lId +" .draggable-item" ).each( function(){
	 			var param = '';
	 			$.each( $(this).data("form"), function(_,e ) {
						if( $(this).attr('name').indexOf('layer_time') ==-1 ){
							if( e.name == 'layer_caption' ){
								 e.value = e.value.replace(/\&/,'_ASM_');
							}  
						param += 'layers['+i+']['+e.name+']='+e.value+'&';
						}
	 			});
	 			params += 	param+"&";
			 	i++
			 });
			 
			 $(".input-time input", $("#slayt-form-"+ byS.lId) ).each( function(i,e){
				 	params +=$(e).attr('name')+"="+$(e).val()+"&";
				 }); 
			 	params +="th="+tHeight;
				$.ajax({
					url: $("#layer-form-"+ byS.lId).attr('action'),
					dataType: 'JSON',
					data:params,
					type: 'POST',
					success: function(resp) {
							if( resp['error'] ){
				 		  		alert(resp['error']);
				 		  	}
				 		  
							if(resp['newlayer'])
							{
								$('#group-sliders-'+byS.lId+' .slider-item.active').removeClass('active');
								$('#group-sliders-'+byS.lId+' .new-slider-item').after('<div class="slider-item" data-id="'+resp['newlayer'] +'" id="slider-item-'+resp['newlayer'] + '" > <a class="image" href="#" data-layer_id="'+resp['newlayer'] +'"><img class="img-responsive" src="'+resp['thumbnail'] +'" height="86"/></a><a  title="'+clone_this+'" class="slider-clone" href="#" data-layer_id="'+resp['slider_id'] +'"><span>'+clone_text+'</span></a><a  title="'+delete_this+'" class="slider-delete" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+delete_text+'</span></a><a class="slider-status" href="#" data-layer_id="'+resp['newlayer'] +'"><span>'+status_text+'</span></a><div>'+resp['slider_title']+'</div></div>');
								uL=resp['newlayer'] ;
								if(resp['slider_status']==0 ){
										$('#slider-item-'+uL+' .slider-status').addClass('slider-status-off');	
									}else{
										$('#slider-item-'+uL+' .slider-status').removeClass('slider-status-off');	
									}
								alert(resp['text_added_slider']);
							}
				 		  
							if(resp['updatelayer'])
							{
								uL = resp['slider_id'] ;
								$('#slider-item-'+uL).data('id',uL);
								$('#slider-item-'+uL+' [data-id]').data('id',uL);
								$('#slider-item-'+uL+' img').attr('src',resp['thumbnail']);
								$('#slider-item-'+uL+' div').html(resp['slider_title']);
								
								if(resp['slider_status']==0 ){
										$('#slider-item-'+uL+' .slider-status').addClass('slider-status-off');	
									}else{
										$('#slider-item-'+uL+' .slider-status').removeClass('slider-status-off');	
									}
									alert(resp['text_updated_slider']);
								}
							}
				});
				
	}
		
}

