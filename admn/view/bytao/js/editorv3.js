  var $summernote;
  var sender='';
  var partType=0;
  var epID=0;
  var langID=0;
  var row_col_id=0;
  var ctrl;
  
 $(function()
	{
		$('.summernote').summernote('destroy');
		epID = $('#parentId').val();	
		langID =$('#language li.active').data('lid');
		rname = getVar('route').split("/")
		ctrl = rname[1];
		appendToHTML();
	
	$(document).on('click',".by_control-set-column", function(e) 
	{
		e.preventDefault();
		
		if(confirm("Are you sure?")) {
			$(this).parent().find(".by_control-set-column").removeClass("by_active");
			$(this).addClass("by_active");
			changeForm($(this).attr("data-cells"),$(this).parent().parent().parent())
			
		}
	});

	$(document).on('click',".by_row-add", function(e)
	 {
		e.preventDefault();
		
		langID = $('#language> li.active').data('lid');
		
		sort_order= $('#text-editor'+langID+' > .by_row').length + 1;
		
		$.ajax({
			url: 'index.php?route=bytao/editor/addrow&token='+getVar('token')+'&ctrl='+ctrl+'&id='+epID+'&language_id='+langID+'&sort_order='+sort_order,
			dataType: 'json',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					addRow2Editor(json['row'],json['col']);
				}
			}
		});
		
		
			
			
	});
	
	$(document).on('click',".by_col-row-add", function(e)
	 {
		e.preventDefault();
		
		langID = $('#language> li.active').data('lid');
		sort_order = $('#text-editor'+langID+' > .by_row').length + 1;
		parentId = $(this).data('cid');
		
		$.ajax({
			url: 'index.php?route=bytao/editor/addrow&token='+getVar('token')+'&ctrl='+ctrl+'&id='+epID+'&language_id='+langID+'&sort_order='+sort_order+'&parent_id='+parentId,
			dataType: 'json',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					addColRow2Editor(json['parent_id'],json['row'],json['col']);
				}
			}
		});
	});
	
	$(document).on('click',".by_column-clone", function(e) 
	{
			e.preventDefault();
			cont = $(document).find('.by_row').length;
			clone=$( this).parent().parent().parent().clone();
			conte = $(document).find('.content').length;
			
			$(clone).find(".content").each(function( index ) {
				conte++;
				$( this ).attr('id','content'+conte);
				
			});
			
			$(clone).find(".collDiv").each(function( index ) {
				$( this ).attr('id','collapse'+cont);
			});
			
			$(clone).find(".by_column-toggle").each(function( index ) {
				$( this ).attr('href','#collapse'+cont);
			});
			
			$(clone).prependTo( ".text-editor" );
			updateData();
			
	});
	
	$(document).on('click',".by_column-delete", function(e)
	 {
			e.preventDefault();
			rowID=$(this).data("rid");
			
			if(confirm("Are you sure?")) {
				$.ajax({
					url: 'index.php?route=bytao/editor/delrow&token='+getVar('token')+'&ctrl='+ctrl+'&row_id='+rowID,
					dataType: 'json',
					success: function(json) {
						if(json['error']){
							alert()
						}
						if(json['success']){
							$('#row-'+json['row']).remove();
							
						}
						
						if(json['parent_id']){
							if(!$('#content'+json['parent_id']+' > .row-container > li').length){
								$('#col-'+json['parent_id']+'> .by_controls').find('.in-content').removeClass('hidden');
							
								obj = $('#col-'+json['parent_id']+' > .by_controls [name="col_type"]');
								col_type_val = obj.val();
								var colID = json['parent_id'];
								$.ajax({
										url: 'index.php?route=bytao/editor/updatecol&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+colID,
										dataType: 'json',
										type: 'post',
										data:{col_type:col_type_val},
										success: function(json) {
											if(json['error']){
												alert()
											}
											if(json['success']){
												
											}
										}
								});
								
								switch(col_type_val){
									case "4":// Simple Text
									case "0":// Reach text
										$('#wbannert').val(col_type_val);
										obj.parent().find('.by_column-pict').addClass('hidden');
										obj.parent().find('.by_column-propert').addClass('hidden');
										obj.parent().find('.column_edit').removeClass('hidden');
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										obj.parent().parent().find(".content").html('');
										break;
									case "1"://Master slider
										$('#media-group').addClass('hidden');
										$('#slider-group').removeClass('hidden');
										$('#wbannert').val(col_type_val);
										obj.parent().find('.by_column-pict').addClass('hidden');
										obj.parent().find('.by_column-propert').removeClass('hidden');
										obj.parent().find('.by_column-edit').addClass('hidden');
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										obj.parent().parent().find(".content").html('');
										break;
									
									case "2":// Carousel
										$('#slider-group').addClass('hidden');
										$('#media-group').removeClass('hidden');
										$('#wbannert').val(col_type_val)
										obj.parent().find('.by_column-pict').addClass('hidden');
										obj.parent().find('.by_column-propert').removeClass('hidden');
										obj.parent().find('.by_column-edit').addClass('hidden');
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										obj.parent().parent().find(".content").html('');
										break;
									case "3"://Revolution Slider
										$('#slider-group').addClass('hidden');
										$('#media-group').removeClass('hidden');
										$('#wbannert').val(col_type_val)
										obj.parent().find('.by_column-pict').addClass('hidden');
										obj.parent().find('.by_column-propert').removeClass('hidden');
										obj.parent().find('.by_column-edit').addClass('hidden');
										
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										obj.parent().parent().find(".content").html('');
										break;
									case "5"://Image collections
										obj.parent().find('.column_edit').addClass('hidden');
										obj.parent().find('.by_column-pict').removeClass('hidden');
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										$("#content"+colID).html('');
										$("#simg > li").remove();
										$("#image-collection").prependTo("#content"+colID);
										sImages.sortable( "refresh" );
										break;
									
									
									case "6"://You tube
									case "7":// Vimeo
									case "8":// Modul Content
										$('#media-group').addClass('hidden');
										$('#slider-group').removeClass('hidden');
										$('#wbannert').val(col_type_val);
										obj.parent().find('.by_column-pict').addClass('hidden');
										obj.parent().find('.by_column-propert').removeClass('hidden');
										obj.parent().find('.by_column-edit').addClass('hidden');
										obj.parent().parent().find('.colpro').removeAttr('col-type');
										obj.parent().parent().find('.colpro').html(' , , , - , , , ');
										obj.parent().parent().find(".content").html('');
										break;
									
									default:
										
										
								}
								$('#wbannert').prop('disabled', true);
								$('#col-'+json['parent_id']+' > .by_controls [name="col_type"]').attr('old-value',col_type_val);
								partType = col_type_val;
							}
							
						}
					}
				});
				
			}
			
			
	});
	
	$(document).on('click',".column_save", function(e) 
	{
		e.preventDefault();
		colID=$(this).data('cid');
		content = $(this).parent().parent().find(".content");
 		$('#'+$(content).attr('id')).summernote('destroy');
 		contentText=$('#'+$(content).attr('id')).html();
 		$.ajax({
			url: 'index.php?route=bytao/editor/updatecol&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+colID,
			dataType: 'json',
			data:{col_content:contentText},
			type: 'post',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					
				}
			}
		});
 		$(this).parent().find(".column_edit").show(); 
 		updateData();
 		$(this).remove();
	});
	
	$(document).on('click',".by_column-cog", function(e) 
	{
		e.preventDefault();
		rID = $(this).data('rid');
		$('#settingModal').attr('data-crid',$(this).data('rid'))	
			$.ajax({
					url: 'index.php?route=bytao/editor/getpropert&token='+getVar('token')+'&ctrl='+ctrl+'&row_id='+rID,
					dataType: 'json',
					success: function(json) {
						if(json['error']){
							alert()
						}
						if(json['success']){
							if(json['row_class']){
								$('#row_class').html(json['row_class'])
							}else{
								$('#row_class').html('')
							}
							if(json['row_tag_id']){
								$('#row_tag_id').val(json['row_tag_id'])
							}else{
								$('#row_tag_id').val('')
							}
							$('#settingModal').modal('show')
						}
					}
			});
			
		
		
	});
	
	$(document).on('click',".by_column-img", function(e) 
	{
		e.preventDefault();
		row_id = $(this).data('rid');
		$.ajax({
			url: 'index.php?route=common/filemanager/multi&token=' + getURLVar('token')+'&toCall=backim(IIIM,IIID)&toCallId='+row_id,
			dataType: 'html',
			beforeSend: function() {
				$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
				$('#button-image').prop('disabled', true);
			},
			complete: function() {
				$('#button-image i').replaceWith('<i class="fa fa-pencil"></i>');
				$('#button-image').prop('disabled', false);
			},
			success: function(html) {
				
				$('#dir-image').html(html);
				$('#modal-image').modal('show');
			}
		});
	});
//*	
	$(document).on('click',".by_column-pict", function(e) {
		e.preventDefault();
		
		var colID= $(this).data('cid');
		if( $(this).hasClass('color-green')|| $(this).hasClass('color-red')){
			
			$("#image-collection").prependTo("#image-collection-container");
			$("#image-collection").attr('data-cid','');
			$("#simg > li").each(function(i, item) {
				siID= $(this).data('imageId');
				dSrc = $(this).find('img').attr('src');
				dImg = $(this).find('img').attr('data-src');
				
				$('#content'+colID).append('<img src="'+dSrc+'" alt="'+siID+'" data-src="'+dImg+'" data-id="'+siID+'" class="col-images"/>');
			});
			$('#simg > li').remove();
			$(this).removeClass('color-green color-red');
			
		}else{
			
			$("#image-collection").attr('data-cid',colID);
			$("#simg > li").remove();
			$('#content'+colID+' img').each(function(i, item) {
				siID=$(this).data('id');
				dSrc=$(this).attr('src');
				dImg=$(this).attr('data-src');
				$('#simg').append('<li id="si-'+siID+'" data-imageId="'+siID+'"  class="img-box"><div class="li-box"><div class="li-controls"><span class="fanzyLi" data-image="'+siID+'"></span><span class="removeLi remove-col-image" data-imageId="'+siID+'"><i class="fa fa-trash-o"></i></span></div><div class="li-ima"><img src="'+dSrc+'" alt="" title="" data-placeholder="" data-src="" /></div></div></li>');
				
			});
			$('#content'+colID+' img').remove();
			$(this).addClass('color-green');
			$("#image-collection").prependTo("#content"+colID);
			sImages.sortable( "refresh" );
		}
		
					
	});
	
	$(document).on('click',".column_edit", function(e) 
	{
			e.preventDefault();
			cid=$(this).data('cid');
			$(this).before('<a class="by_control column_save color-green by_column-edit " data-vc-control="edit" data-cid="'+cid+'" href="#" title="Save Changes"><i class="fa fa-floppy-o"></i></a>');
			
			colID = $(this).data('cid');
			rowID = $(this).parents('.by_row').data("id");
			$(this).hide();
			
			
			//$('#modalInner').val($(content).html());
			$('#sender').val("content"+colID);
			sender = "content"+colID;
	   /* */
	   contentName ="content"+colID;
	   $('#'+contentName).summernote({
			height: 400,
			focus:true,
			lang:'TR',
			popover: {
			      image: [

			        // This is a Custom Button in a new Toolbar Area
			        ['custom', ['examplePlugin']],
			        ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
			        ['float', ['floatLeft', 'floatRight', 'floatNone']],
			        ['remove', ['removeMedia']]
			      ]
			},
			toolbar: [
				['font', ['style','bold','italic', 'underline', 'clear']],
				['fontname', ['fontname','fontsize','color','height']],
				['para', ['ul', 'ol', 'paragraph']],
				['table', ['table']],
				['insert', ['link', 'image', 'video']],
				['view', ['fullscreen', 'codeview','computed']]
			],
			buttons: {
    			image: function() {
					var ui = $.summernote.ui;
					
					// create button
					var button = ui.button({
						contents: '<i class="fa fa-image" />',
						tooltip: $.summernote.lang[$.summernote.options.lang].image.image,
						click: function () {
							
							//$('#modal-image').remove();
						
							$.ajax({
								url: 'index.php?route=common/filemanager&token=' + getURLVar('token'),
								dataType: 'html',
								beforeSend: function() {
									$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
									$('#button-image').prop('disabled', true);
								},
								complete: function() {
									$('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
									$('#button-image').prop('disabled', false);
								},
								success: function(html) {
									
									$('#dir-image').html(html);
									$('#modal-image').modal('show');
									
									$(document).on('click','#dir-image a.thumbnail', function(e) {
										e.preventDefault();
										
										 $('#'+contentName).summernote('insertImage', $(this).attr('href'),function ($image) {
												$image.removeClass('img-responsive');
												$image.addClass('w3-image');
											  //$image.css('width', $image.width() / 3).attr('data-filename', 'puppies');
											});
																	
										$('#modal-image').modal('hide');
										
										e.stopImmediatePropagation();
										
									});
								}
							});						
						}
					});
				
					return button.render();
				},
  				
  				computed: function() {
					var ui = $.summernote.ui;
					
					// create button
					var button = ui.button({
						contents: '<i class="fa fa-sliders" />',
						tooltip: $.summernote.lang[$.summernote.options.lang].options.computed,
						click: function () {
							$('#activeContent').val(contentName);
							var mpad = propert.html().split("-");
							if(mpad[1]){
								var margins = mpad[0].split(",");
								var padings = mpad[1].split(",");
								$('#mod-p-t').val(padings[0]);
								$('#mod-p-b').val(padings[1]);
								$('#mod-p-l').val(padings[2]);
								$('#mod-p-r').val(padings[3]);
								
								$('#mod-m-t').val(margins[0]);
								$('#mod-m-b').val(margins[1]);
								$('#mod-m-l').val(margins[2]);
								$('#mod-m-r').val(margins[3]);
							}else{
								$('#mod-p-t').val(0);
								$('#mod-p-b').val(0);
								$('#mod-p-l').val(0);
								$('#mod-p-r').val(0);
								
								$('#mod-m-t').val(0);
								$('#mod-m-b').val(0);
								$('#mod-m-l').val(0);
								$('#mod-m-r').val(0);
							}
							$('#spacesModal').modal('show');
												
						}
					});
				
					return button.render();
				}
  			
  			
  			},
			callbacks: {
			    onFocus: function() {
			      //console.log('Editable area is focused');
			    },
			    onChange: function(contents, $editable) {
			    	$(this).parent().find('.column_save').removeClass('color-green');
			    	$(this).parent().find('.column_save').addClass('color-red');
			    	
			    }
			}
		});
      	
      	//$('#modalInner').summernote('code', $(content).html());
      	
      	
      	
      	
			
	});
	
	$('img').load(function (Img) 
	{
	    //console.log('Editable area is focused'+Img.src);
	});
	
	$(document).on('click',".by_column-propert", function(e)
	 {
			e.preventDefault();
			
			$('#widget-modal').data('cid',$(this).data('cid'));
			cId = $(this).data('cid');
			cType = $(this).parent().find('.col_type').val();
			partType = parseInt(cType);
			switch(cType){
				case 0: // Rich Text
					break;
				case '1':
	
					break;
				case 2:
					$('#m-modul,#m-type').addClass('hidden');
					$('#m-banner,#m-width,#m-carousel').removeClass('hidden');
					break;
				case 3:
					$('#m-modul,#m-type').addClass('hidden');
					$('#m-banner,#m-width,#m-carousel').removeClass('hidden');
					break;
				case 4:// Simple Text
					break;
				case 5:
					break;
				case 6:
					break;
				case 7:
					break;
				case '8': // Modül
					
					$('#m-banner,#m-type,#m-carousel,#m-width').addClass('hidden');
					$('#m-modul').removeClass('hidden');
					break;
				
				default:
					break;
			}
			
			
			$('#wbannert').prop('disabled', false);
			$('#widget-modal').modal('show');
			
			
	});
	
	$(document).on('click',".by_page-property", function(e) 
	{
			e.preventDefault();
			$('#input-css' ).val($( "#page-style" ).val());
			$('#pageModal').modal('show');
			
	});
	
	$(document).on('focus','.col_type', function (e)
	 { 
		$(this).attr('old-value',$(this).val())
		partType = $(this).val();
	});
	
	$(document).on('change','.col_type', function (e)
	 { 
		obj = $(this);
		col_type_val = obj.val();
		
		if(confirm("Are you sure?")) 
		{
			var colID = $(obj).parents('.by_col').data('id');
			
			$.ajax({
					url: 'index.php?route=bytao/editor/updatecol&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+colID,
					dataType: 'json',
					type: 'post',
					data:{col_type:col_type_val},
					success: function(json) {
						if(json['error']){
							alert()
						}
						if(json['success']){
							
						}
					}
			});
			$('#wbannert').prop('disabled', false);
			
			switch(col_type_val){
				case "4":// Simple Text
				case "0":// Reach text
					$('#wbannert').val(col_type_val);
					obj.parent().find('.by_column-propert').addClass('hidden');
					obj.parent().find('.column_edit').removeClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					obj.parent().parent().find(".content").html('');
					break;
				case "1"://Master slider
					$('#media-group').addClass('hidden');
					$('#slider-group').removeClass('hidden');
					$('#wbannert').val(col_type_val);
					obj.parent().find('.by_column-propert').removeClass('hidden');
					obj.parent().find('.by_column-edit').addClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					obj.parent().parent().find(".content").html('');
					break;
				
				case "2":// Carousel
					$('#slider-group').addClass('hidden');
					$('#media-group').removeClass('hidden');
					$('#wbannert').val(col_type_val)
					obj.parent().find('.by_column-propert').removeClass('hidden');
					obj.parent().find('.by_column-edit').addClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					obj.parent().parent().find(".content").html('');
					break;
				case "3"://Revolution Slider
					$('#slider-group').addClass('hidden');
					$('#media-group').removeClass('hidden');
					$('#wbannert').val(col_type_val)
					obj.parent().find('.by_column-propert').removeClass('hidden');
					obj.parent().find('.by_column-edit').addClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					obj.parent().parent().find(".content").html('');
					break;
				case "5"://Image collections
					obj.parent().find('.column_edit').addClass('hidden');
					obj.parent().find('.by_column-pict').removeClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					$("#content"+colID).html('');
					$("#simg > li").remove();
					$("#image-collection").prependTo("#content"+colID);
					sImages.sortable( "refresh" );
					break;
				
				
				case "6"://You tube
				case "7":// Vimeo
				case "8":// Modul Content
					$('#media-group').addClass('hidden');
					$('#slider-group').removeClass('hidden');
					$('#wbannert').val(col_type_val);
					obj.parent().find('.by_column-propert').removeClass('hidden');
					obj.parent().find('.by_column-edit').addClass('hidden');
					obj.parent().parent().find('.colpro').removeAttr('col-type');
					obj.parent().parent().find('.colpro').html(' , , , - , , , ');
					obj.parent().parent().find(".content").html('');
					break;
				
				default:
					
					
			}
			$('#wbannert').prop('disabled', true);
			$(this).attr('old-value',col_type_val);
			partType = col_type_val;
			
			
		}
		else
		{
			$(this).val($(this).attr('old-value'));
		}	
		
	});
	
	$(document).on('click','#updatem',function()
	{
		spaces =$('#mod-m-t').val() + ',' + $('#mod-m-b').val() + ',' + $('#mod-m-l').val() + ',' + $('#mod-m-r').val() + '-' + $('#mod-p-t').val() + ',' + $('#mod-p-b').val() + ',' + $('#mod-p-l').val() + ',' + $('#mod-p-r').val();
		rowClass=$('#row_class').val();
		rowTagId=$('#row_tag_id').val();
		rowID=$('#settingModal').attr('data-crid');
		$.ajax({
					url: 'index.php?route=bytao/editor/updaterow&token='+getVar('token')+'&ctrl='+ctrl+'&row_id='+rowID,
					dataType: 'json',
					type: 'post',
					data:{space:spaces,row_class:rowClass,row_tag_id:rowTagId},
					success: function(json) {
						if(json['error']){
							alert()
						}
						if(json['success']){
							
						}
					}
		});
		
		
		
		oldSpaces = $('#' + $('#activeContent').val()).parent().find('.colpro').html();
		$('#' + $('#activeContent').val()).parent().find('.colpro').html(spaces);
		updateData();
		if(spaces!=oldSpaces){
			$('#' + $('#activeContent').val()).parent().find('.column_save').removeClass('color-green');
			$('#' + $('#activeContent').val()).parent().find('.column_save').addClass('color-red');
		}
		
		
		$('#settingModal').modal('hide');
	});
	
	$(document).on('click','#updatew',function(e)
	{
		var colContent;
		var colContentID;
		switch(partType)
		{
			case 1:	nTitle="MASTER SLIDER:";break;
			case 2: nTitle="CAROUSEL:";break;
			case 3: nTitle="REVOLUTION SLIDER:";
					colContentID=$('#wbannert').val() + '-' + $('#wmedia').val() + '-' + $('#wbannerw').val();
					colContent=' <i class="fa fa-picture-o"></i> <i class="fa fa-picture-o"></i> <i class="fa fa-picture-o"></i> <b> '+ nTitle + $('#wmedia option:selected').text() + ' </b>';
					break;
			case 4:
			case 5:
			case 6:
			case 7:
			case 8: nTitle = "MODÜL:" ;
					if($('#mGroupId').val()!=''){
						colContentID = $('#mModule').val()+'-'+$('#mGroupId').val();
						colContent = ' <i class="fa fa-cog"></i> </i> <b> '+ nTitle + $('#mModule option:selected').text()+' - ' + $('#mGroupId  option:selected').text() + ' </b>';
					}else{
						colContentID = $('#mModule').val();
						colContent = ' <i class="fa fa-cog"></i> </i> <b> '+ nTitle + $('#mModule option:selected').text() + ' </b>';
					}
					
					
					break;
			default:
			 nTitle="";
		}
		
		colID = $('#widget-modal').data('cid');
		
		$('#content'+colID).html(colContent);
		
		$.ajax({
			url: 'index.php?route=bytao/editor/updatecol&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+colID,
			dataType: 'json',
			type: 'post',
			data:{col_content:colContent,col_content_id:colContentID},
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					
				}
			}
		});
			
		$('#widget-modal').modal('hide');
		
	});
	
	$(document).on('change','#mModule',function(e){
		e.preventDefault();
		if($(this).val()=='pages'){
			$('#m-group').removeClass('hidden')
		}else{
			if(! $('#m-group').hasClass('hidden')){
				$('#m-group').addClass('hidden')
			}
			
		}
	});
	
	$(document).on('click','#updatep',function(e)
	{
		
		$('#' + $('#sender').val()).html(' <i class="fa fa-picture-o"></i> <i class="fa fa-picture-o"></i> <i class="fa fa-picture-o"></i> <b> SLIDER: ' + $('#wbanner').text() + ' </b>');
		
		$('#' + $('#sender').val()).parent().find('.colpro').attr('col-type',$('#wbannert').val()+'-'+$('#wbanner').val());
		
		
		$('#page-modal').modal('hide');
		updateData();
	});
	
	$(document).on('click','#updatepage',function(e)
	{
		$('#pageModal').modal('hide');
		updateData();
	});
	
	
	properties = {
	  group: 'limited_drop_targets',
	  isValidTarget: function  (item, container) {
	    if(item.is(".highlight"))
	      return true
	    else {
	      return item.parent("ol")[0] == container.el[0]
	    }
	  },
	  update: function (item, container, _super) {

	  	var boxes = [];
	  	var Olchild=$('#simg li');
	  	serials='';
	  	
	  	$(Olchild).each(function(){
	  		serials += $(this).attr('data-imageid')+'_'
	  		
	    });
		cID=$('#image-collection').data('cid');
	    $.ajax({
				url: 'index.php?route=bytao/editor/sortimg&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+cID,
				dataType: 'json',
				type:'post',
				data:{serial:serials},	
				success: function(json) {
				}
			});
	    /**/

	  },
	  serialize: function (parent, children, isContainer) {
	    return isContainer ? children.join() : parent.attr('id');
	  },
	  tolerance: 6,
	  distance: 10,
	  activate: function( event, ui ) { 
	  
	  }
	};
	
	var sImages = $("#simg").sortable(properties);
	
	$(document).on('click','#button-sellect', function(e) { 
		row_col_id = $('#image-collection').data('cid');
		
		$.ajax({
			url: 'index.php?route=bytao/editor/addcolimg&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+row_col_id,
			type: 'post',
			dataType: 'json',
			data: $('input[name^=\'path\']:checked'),
			beforeSend: function() {
				
			},
			complete: function() {
				
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['images']) {
					$.each(json['images'], function(i, item) {
					    
					    $('#simg').append('<li id="si-'+item['image_id']+'" data-imageId="'+item['image_id']+'"  class="img-box"><div class="li-box"><div class="li-controls"><span class="fanzyLi" data-image="'+item['image_id']+'"></span><span class="removeLi" data-imageId="'+item['image_id']+'"><i class="fa fa-trash-o"></i></span></div><div class="li-ima"><img src="'+item['thumb']+'" alt="" title="" data-placeholder="" /></div></div></li>');
					    
				
					})
					
					sImages.sortable( "refresh" );
				}
				
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
		
			
	
		$('.popover').remove();
		$('#modal-image').modal('hide');
	 });
	
	$(document).on('click','a.thumbnail',  function(e) {

		if($(this).data('path') && ( $('.by_column-pict').hasClass('color-green') || $('.by_column-pict').hasClass('color-red'))){
			
			e.preventDefault();
			row_col_id = $('#image-collection').data('cid');
		
			$.ajax({
				url: 'index.php?route=bytao/editor/addcolimg&token='+getVar('token')+'&ctrl='+ctrl+'&row_col_id='+row_col_id,
				type: 'post',
				dataType: 'json',
				data: {path:$(this).data('path')},
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}

					if (json['images']) {
						$.each(json['images'], function(i, item) {
						    
						    $('#simg').append('<li id="si-'+item['image_id']+'" data-imageId="'+item['image_id']+'"  class="img-box"><div class="li-box"><div class="li-controls"><span class="fanzyLi" data-image="'+item['image_id']+'"></span><span class="removeLi" data-imageId="'+item['image_id']+'"><i class="fa fa-trash-o"></i></span></div><div class="li-ima"><img src="'+item['thumb']+'" alt="" title="" data-placeholder="" /></div></div></li>');
						    
					
						})
						
						sImages.sortable( "refresh" );
						
					}
					
				}
			});
		
			
			
		}
		$('.popover').remove();
		$('#modal-image').modal('hide');
		
		return false;
});
	
	$(document).on('click','.remove-col-image', function(e) {
		
		col_image_id = $(this).data('imageid');
		$.ajax({
			url: 'index.php?route=bytao/editor/delcolimg&token='+getVar('token')+'&ctrl='+ctrl+'&image_id='+col_image_id,
			dataType: 'json',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					$('#si-'+json['image_id']).remove();
				}
			}
		});
		
	});
	
	$(document).on('click','.img-box-add', function(e) {
		
		row_col_id = $('#image-collection').data('cid');
		$.ajax({
			url: 'index.php?route=common/filemanager/multi&token=' + getURLVar('token'),
			dataType: 'html',
			beforeSend: function() {
				$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
				$('#button-image').prop('disabled', true);
			},
			complete: function() {
				$('#button-image i').replaceWith('<i class="fa fa-pencil"></i>');
				$('#button-image').prop('disabled', false);
			},
			success: function(html) {
				
				$('#dir-image').html(html);
				$('#modal-image').modal('show');
			}
		});
	});

	var group = $( ".text-editor" ).sortable({
		  handle: ".by_column-move",
		  containment: "parent",
		  axis: "y",
		  helper: "clone",
			update:function () {
			  
			  var dataN = group.sortable("serialize")
			  
			  selInd=$( "#language li.active" ).index()
				  $.ajax({
						url: 'index.php?route=bytao/editor/sortroworder&token='+getVar('token')+'&ctrl='+ctrl+'&sel='+selInd,
						type: 'post',
						dataType: 'json',
						data: dataN,
						success: function(json) {
								
						}
					});
			}
		 
	});
	
	var subgroups = $( ".row-container" ).sortable({
		  handle: ".by_sub-column-move",
		  containment: "parent",
		  axis: "y",
		  helper: "clone",
			update:function (event,ui) {
			 
			  //console.log($(ui.item[0]).parent().index())
			  var dataN = $(ui.item[0]).parent().sortable("serialize")
			  
			  selInd=$( "#language li.active" ).index()
				  $.ajax({
						url: 'index.php?route=bytao/editor/sortroworder&token='+getVar('token')+'&ctrl='+ctrl+'&sel='+selInd,
						type: 'post',
						dataType: 'json',
						data: dataN,
						success: function(json) {
								
						}
					});
			}
		 
	});
	

});

function backim(imgPath,rid){
	var rowID = rid;
	$.ajax({
			url: 'index.php?route=bytao/editor/updaterow&token='+getVar('token')+'&ctrl='+ctrl+'&nrow_id='+rowID,
			dataType: 'json',
			type: 'post',
			data:{path:imgPath},
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					if(json['thumb']){
						$('#row-'+json['row']).find('.by_column-img').html('<img src="'+json['thumb']+'" alt="min" class="min-thumb" />');
					}
				}
			}
		});
	
}

function getVar(key) {
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

function changeForm(resform,objF){
	
	var Divs = resform.split(/_/);
	rowID=$(objF).data('id');
	$.ajax({
			url: 'index.php?route=bytao/editor/deladdrowcol&token='+getVar('token')+'&ctrl='+ctrl+'&row_id='+rowID+'&divs='+resform,
			dataType: 'json',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					$('#collapse'+json['row']).find('.by_col').remove();
					$('#collapse'+json['row']).append(json['content']);
					
				}
			}
		});
}
 
function updateData(){
	$("#page-style" ).val($( '#input-css' ).val());
	
	$(".text-editor" ).each(function( index ) {
		
			langData=$( this ).attr('data-lang');
			genData='';
			$( this ).find(".by_row").each(function( index ) {
				genData += '<by_row>';
				genData += '<by_divs>' + $( this ).find(".by_active").attr('data-cells') + '</by_divs>';
				
				$( this ).find(".by_col").each(function( index ) {
					
					col_type1 = $( this ).find('.colpro').attr('col-type');
					
					if ((typeof col_type1 === "undefined") || (col_type1 == 0)) {
						ct ='';	
					}else{
						ct = ':' + col_type1;
					}

					genData += '<by_col>';
					genData += '<by_cell>' + $( this ).find('.content').attr('data-cell') + '</by_cell>';
					genData += '<by_colp>' + $( this ).find('.colpro').html() + ct + '</by_colp>';
					genData += '<by_content>' + $( this ).find('.content').html() + '</by_content>';
					genData += '</by_col>'
				});
				genData += '</by_row>'
			});
			
			$('#input-description'+langData).html(genData);
			
	});
	
}
 
function appendToHTML(){
	
	htmlE ='<input type="hidden" id="sender" value="">';
	htmlE += '<div class="modal fade" id="settingModal" role="dialog" data-crid="0">';
  	htmlE += '<div class="modal-dialog  modal-lg">';
    htmlE += '<div class="modal-content"><input type="hidden" id="activeContent" value="" />';
    htmlE += '  <div class="modal-header">';
    htmlE += '    <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
    htmlE += '      <span aria-hidden="true">&times;</span>';
    htmlE += '	  </button>';
    htmlE += '    <button type="button" class="process"><i class="fa fa-spinner"></i></button>';
    htmlE += '    <h4 class="modal-title">Row</h4>';
    htmlE += '  </div>';
    htmlE += '  <div class="modal-body" id="modal-body">';
    htmlE += '  <div class="row">';
    htmlE += '        <div class="col-md-6">';
    htmlE += '<table class="modal-padding-table">';
	htmlE += '	<tr><td style="color:#ccc;">Margin</td><td><input type="text" name="mod-m-t" id="mod-m-t" title="Top" value=""/></td>';
	htmlE += '	<td></td></tr>';
	htmlE += '<tr><td><input type="text" title="Left"  name="mod-m-l" id="mod-m-l" value=""/></td>';
	htmlE += '<td><table class="modal-padding-table">';
	htmlE += '	<tr><td>Padding</td><td><input type="text" name="mod-p-t" id="mod-p-t" title="Top" value=""/></td><td></td></tr>';
	htmlE += '	<tr><td><input type="text" name="mod-p-l" id="mod-p-l" title="Left" value=""/></td><td><div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam sollicitudin finibus libero eget suscipit.</div></td><td><input type="text" name="mod-p-r" id="mod-p-r" title="Right" value=""/></td></tr>';
	htmlE += '	<tr><td></td><td><input type="text" name="mod-p-b" id="mod-p-b" title="bottom"  value=""/></td><td></td></tr></table>';
	htmlE += '</td><td><input type="text" name="mod-m-r" id="mod-m-r" title="Right" value=""/></td></tr>';
	htmlE += '<tr><td></td><td><input type="text" name="mod-m-b"  id="mod-m-b" title="bottom"  value=""/></td><td></td></tr></table>';
    htmlE += '        </div>';
    htmlE += '        <div class="col-md-6">';
    htmlE +='<label>Tag ID </label> <input type="text" id="row_tag_id" name="row_tag_id" value="" style="width:100%;"/>';
    htmlE +='<label>Class </label> <textarea id="row_class" rows="10" name="row_class" style="width:100%;"></textarea>';
    htmlE += '        </div>';
    htmlE += '      </div>';
    
    htmlE += '  </div>';
    htmlE += '<div class="modal-footer">';
    htmlE += '    <button type="button" id="updatem" class="btn btn-primary" title="Save changes"><i class="fa fa-floppy-o"></i></button>';
    htmlE += '  </div>';
    htmlE += '</div>';
  	htmlE += '</div>';
	htmlE += '</div>';
	
	htmlE += '<div class="modal fade" id="pageModal" role="dialog">';
  	htmlE += '<div class="modal-dialog  modal-lg">';
    htmlE += '<div class="modal-content">';
    htmlE += '  <div class="modal-header">';
    htmlE += '    <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
    htmlE += '      <span aria-hidden="true">&times;</span>';
    htmlE += '	  </button>';
    htmlE += '    <button type="button" class="process"><i class="fa fa-spinner"></i></button>';
    htmlE += '    <h4 class="modal-title">CSS</h4>';
    htmlE += '  </div>';
    htmlE += '  <div class="modal-body">';
    htmlE += '  	<div id="div-css">';
    htmlE += '  	<textarea id="input-css" style="width:100%;height:500px;"></textarea>';
    htmlE += '  	</div>';
    htmlE += '  </div>';
    htmlE += '<div class="modal-footer">';
    htmlE += '    <button type="button" id="updatepage" class="btn btn-primary" title="Save changes"><i class="fa fa-floppy-o"></i></button>';
    htmlE += '  </div>';
    htmlE += '</div>';
  	htmlE += '</div>';
	htmlE += '</div>';
	
	htmlE += '<div id="image-collection-container">';
	htmlE += '<div class="img-platform" id="image-collection" data-cid="">';
    htmlE += '<ul>';
    htmlE += '<li>';
    htmlE += '<ol class="collection-sortible"  id="simg">';
    htmlE += '</ol>';
    htmlE += '</li>';
	htmlE += '<li class="img-box-add">';
	htmlE += '<i class="fa fa-plus" ></i>';
	htmlE += '</li>';
	htmlE += '</ul>';
	htmlE += '</div>';
	htmlE += '</div>';

	$('body').append(htmlE);
}

function addImage(iID){
	htmlE  = '<li id="si-'+iID+'" data-imageId="'+iID+'"  class="img-box">';
	htmlE += '<div class="li-box">';
	htmlE += '<div class="li-controls">';
	htmlE += '<span class="fanzyLi" data-image="'+iID+'"></span>';
	htmlE += '<span class="removeLi remove-col-image" data-imageId="'+iID+'"><i class="fa fa-trash-o"></i></span>';
	htmlE += '</div>';
	htmlE += '<div class="li-ima">';
	htmlE += '<img src="" alt="" title="" data-placeholder="" />';
	htmlE += '</div>';
	htmlE += '</div>';
	htmlE += '</li>';
}

function addRow2Editor(rowID,colID){
		html  = '<li class="row by_row ui-state-default" data-id="'+rowID+'">';
		html += '<div class="by_controls-row controls controls_row by_clearfix">'; 
  		html += '<a class="by_control column_move by_column-move" href="#" title="Drag row to reorder" data-vc-control="move"><i class="by_icon"></i></a>';
  		html += '<span class="by_row_name by_control">ROW-' + rowID + '</span>';
  		html += '<span class="by_row_layouts by_control">';
  		html += '<a class="by_control-set-column set_columns l_11 by_active" data-cells="11" data-cells-mask="12" title="1/1"></a> ';
  		html += '<a class="by_control-set-column set_columns l_12_12" data-cells="12_12" data-cells-mask="26" title="1/2 + 1/2"></a>'; 
  		html += '<a class="by_control-set-column set_columns l_23_13" data-cells="23_13" data-cells-mask="29" title="2/3 + 1/3"></a>';
  		html += '<a class="by_control-set-column set_columns l_13_13_13" data-cells="13_13_13" data-cells-mask="312" title="1/3 + 1/3 + 1/3"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_14_14_14" data-cells="14_14_14_14" data-cells-mask="420" title="1/4 + 1/4 + 1/4 + 1/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_34" data-cells="14_34" data-cells-mask="212" title="1/4 + 3/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_12_14" data-cells="14_12_14" data-cells-mask="313" title="1/4 + 1/2 + 1/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_56_16" data-cells="56_16" data-cells-mask="218" title="5/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_16_16_16_16_16" data-cells="16_16_16_16_16_16" data-cells-mask="642" title="1/6 + 1/6 + 1/6 + 1/6 + 1/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_46_16" data-cells="16_23_16" data-cells-mask="319" title="1/6 + 4/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_16_16_12" data-cells="16_16_16_12" data-cells-mask="424" title="1/6 + 1/6 + 1/6 + 1/2"></a>';
  		html += '</span>';
  		html += '<span class="by_row_edit_clone_delete">';
  		html += '<a class="by_control column_delete by_column-delete" href="#" title="Delete this row" data-vc-control="delete" data-rid="' + rowID + '"><i class="by_icon"></i></a>';
  		html += '<a class="by_control column_clone by_column-clone" href="#" title="Clone this row" data-vc-control="clone" data-rid="' + rowID + '"><i class="by_icon"></i></a>';
  		html += '<a class="by_control column_clone by_column-cog" href="#" title="Properties" data-vc-control="properties" data-rid="' + rowID + '"><i class="fa fa-cog"></i></a>';
  		html += '<a class="by_control column_clone by_column-img" href="#" title="Properties" data-vc-control="Back Image" data-rid="' + rowID + '"><i class="fa fa-picture-o"></i></a>';
  		
  		//html += '<a class="by_control column_edit by_column-edit" href="#" title="Edit this row" data-vc-control="edit"><i class="by_icon"></i></a>';
  		html += '<a class="by_control column_toggle by_column-toggle" data-toggle="collapse" href="#collapse' + rowID + '" aria-expanded="false" aria-controls="collapse' + rowID + '"><i class="by_icon"></i></a>';
  		html += '</span>';
  		html += '</div>';
		html += '<div class="collDiv collapse in" id="collapse' + rowID + '">';
		html += '<div class="col-sm-12 by_col" id="col-'+colID+'" data-id="'+colID+'">';
		html += '<div class="by_controls by_control-column by_controls-visible controls"> ';
		
		html += '<a class="by_control column_edit in-content by_column-edit" data-vc-control="edit" data-cid="'+colID+'" href="#" title="Edit this column"><i class="by_icon"></i></a> ';
		html += '<a class="by_control by_column-pict in-content hidden" data-cid="'+colID+'" href="#" title="widget"><i class="fa fa-picture-o"></i></a> ';
		html += '<a class="by_control by_column-propert in-content hidden"  data-cid="'+colID+'" href="#" title="widget"><i class="fa fa-tasks"></i></a> ';
		html += '<select name="col_type" class="col_type in-content"><option value="0" selected="selected">Text-image-movie(zengin editor)</option><option value="4">Simple Tex</option><option value="5">Simple Image</option><option value="6">Youtube Video</option><option value="7">Vimeo Video</option><option value="1">Master Slider</option><option value="2">Carousel</option><option value="3">Revolution Slider</option><option value="8">Modul Content</option></select>';
		html += '<a class="by_control by_col-row-add" data-cid="'+colID+'" href="#" title="Add Row In column"><i class="fa fa-plus-circle"></i></a> ';
		html += '</div>';
		
		html += '<div id="collpro'+colID+'" class="hidden colpro">0,0,0,0-0,0,0,0</div>';
		html += '<div id="content'+colID+'" datac="'+colID+'" data-cell="11" class="content">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</li>';
		langID =$('#language li.active').data('lid');
		$(html).appendTo($('#text-editor'+langID));
}

function addColRow2Editor(PID,rowID,colID){
	
		html  = '<li class="row by_row ui-state-default" data-id="'+rowID+'" id="row-'+rowID+'">';
		html += '<div class="by_controls-row controls controls_row by_clearfix">'; 
  		html += '<a class="by_control column_move by_column-move" href="#" title="Drag row to reorder" data-vc-control="move"><i class="by_icon"></i></a>';
  		html += '<span class="by_row_name by_control">ROW-' + rowID + '</span>';
  		html += '<span class="by_row_layouts by_control">';
  		html += '<a class="by_control-set-column set_columns l_11 by_active" data-cells="11" data-cells-mask="12" title="1/1"></a> ';
  		html += '<a class="by_control-set-column set_columns l_12_12" data-cells="12_12" data-cells-mask="26" title="1/2 + 1/2"></a>'; 
  		html += '<a class="by_control-set-column set_columns l_23_13" data-cells="23_13" data-cells-mask="29" title="2/3 + 1/3"></a>';
  		html += '<a class="by_control-set-column set_columns l_13_13_13" data-cells="13_13_13" data-cells-mask="312" title="1/3 + 1/3 + 1/3"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_14_14_14" data-cells="14_14_14_14" data-cells-mask="420" title="1/4 + 1/4 + 1/4 + 1/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_34" data-cells="14_34" data-cells-mask="212" title="1/4 + 3/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_14_12_14" data-cells="14_12_14" data-cells-mask="313" title="1/4 + 1/2 + 1/4"></a>';
  		html += '<a class="by_control-set-column set_columns l_56_16" data-cells="56_16" data-cells-mask="218" title="5/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_16_16_16_16_16" data-cells="16_16_16_16_16_16" data-cells-mask="642" title="1/6 + 1/6 + 1/6 + 1/6 + 1/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_46_16" data-cells="16_23_16" data-cells-mask="319" title="1/6 + 4/6 + 1/6"></a>';
  		html += '<a class="by_control-set-column set_columns l_16_16_16_12" data-cells="16_16_16_12" data-cells-mask="424" title="1/6 + 1/6 + 1/6 + 1/2"></a>';
  		html += '</span>';
  		html += '<span class="by_row_edit_clone_delete">';
  		html += '<a class="by_control column_delete by_column-delete" href="#" title="Delete this row" data-vc-control="delete" data-rid="' + rowID + '"><i class="by_icon"></i></a>';
  		html += '<a class="by_control column_clone by_column-clone" href="#" title="Clone this row" data-vc-control="clone" data-rid="' + rowID + '"><i class="by_icon"></i></a>';
  		html += '<a class="by_control column_clone by_column-cog" href="#" title="Properties" data-vc-control="properties" data-rid="' + rowID + '"><i class="fa fa-cog"></i></a>';
  		html += '<a class="by_control column_clone by_column-img" href="#" title="Properties" data-vc-control="Back Image" data-rid="' + rowID + '"><i class="fa fa-picture-o"></i></a>';
  		html += '<a class="by_control column_toggle by_column-toggle" data-toggle="collapse" href="#collapse' + rowID + '" aria-expanded="false" aria-controls="collapse' + rowID + '"><i class="by_icon"></i></a>';
  		html += '</span>';
  		html += '</div>';
		html += '<div class="collDiv collapse in" id="collapse' + rowID + '">';
		html += '<div class="col-sm-12 by_col" id="col-'+colID+'" data-id="'+colID+'">';
		html += '<div class="by_controls by_control-column by_controls-visible controls"> ';
		
		html += '<a class="by_control column_edit in-content by_column-edit" data-vc-control="edit" data-cid="'+colID+'" href="#" title="Edit this column"><i class="by_icon"></i></a> ';
		html += '<a class="by_control by_column-pict in-content hidden" data-cid="'+colID+'" href="#" title="widget"><i class="fa fa-picture-o"></i></a> ';
		html += '<a class="by_control by_column-propert in-content hidden"  data-cid="'+colID+'" href="#" title="widget"><i class="fa fa-tasks"></i></a> ';
		html += '<select name="col_type" class="col_type in-content"><option value="0" selected="selected">Text-image-movie(zengin editor)</option><option value="4">Simple Tex</option><option value="5">Simple Image</option><option value="6">Youtube Video</option><option value="7">Vimeo Video</option><option value="1">Master Slider</option><option value="2">Carousel</option><option value="3">Revolution Slider</option><option value="8">Modul Content</option></select>';
		html += '<a class="by_control by_col-row-add" data-cid="'+colID+'" href="#" title="Add Row In column"><i class="fa fa-plus-circle"></i></a> ';
		html += '</div>';
		
		html += '<div id="collpro'+colID+'" class="hidden colpro">0,0,0,0-0,0,0,0</div>';
		html += '<div id="content'+colID+'" datac="'+colID+'" data-cell="11" class="content">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</li>';
		
		if($('#rc-'+PID).length){
			$(html).appendTo($('#content'+PID+' > .row-container'));
		}
		else
		{
			$('#col-'+PID+'> .by_controls').find('.in-content').addClass('hidden');
			$('<ul class="row-container" id="rc-'+PID+'"></ul>').appendTo($('#content'+PID));
			$(html).appendTo($('#content'+PID+' > .row-container'));	
		}
		
		move2($('#row-'+rowID));
}

function move2(elm){
	var position = elm.position();
	$("html, body").animate({scrollTop:position.top}, 500);
}





/**
* New
* 
* @return
*/
function getAjax(){
	var Divs = resform.split(/_/);
	rowID=$(objF).data('id');
	$.ajax({
			url: 'index.php?route=bytao/editor/getwidget&token='+getVar('token')+'&ctrl='+ctrl+'&row_id='+rowID+'&divs='+resform,
			dataType: 'json',
			success: function(json) {
				if(json['error']){
					alert()
				}
				if(json['success']){
					$('#collapse'+json['row']).find('.by_col').remove();
					$('#collapse'+json['row']).append(json['content']);
					
				}
			}
		});
}