
function appendToBody(type){
	var modalId;
	var html  = '';
	switch(type){
		case '1':// ALRTR
			{	modalId="#alerter";
				html  ='<div id="alerter" class="modal fade" tabindex="-1">';
				html +='  <div id="modal-alerter" class="modal-dialog modal-sm modal-dialog-centered">';
				html +='		<div class="modal-icon d-flex align-items-left"></div>';
				html +='		<div class="modal-content">';
				html +='			<div class="modal-header text-center"></div>';
				html +='			<div class="modal-body text-center"></div>';
				html +='		</div>';
				html +='  </div>';
				html +='</div>';
			}	
			break;
		case '3':// Yes No
			{	modalId="#staticBackdrop";
				html  ='<div id="staticBackdrop" class="modal fade" tabindex="-1">';
				html +='  <div id="modal-staticBackdrop" class="modal-dialog modal-sm modal-dialog-centered">';
				html +='		<div class="modal-icon d-flex align-items-left"></div>';
				html +='		<div class="modal-content">';
				html +='			<div class="modal-header text-center"></div>';
				html +='			<div class="modal-body text-center"></div>';
				html +='			<div class="modal-footer">';
				html +='	    		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i></button>';
				html +='        		<button type="button" class="btn btn-primary" data-bs-remover=""><i class="fa-solid fa-circle-check"></i></button>';
				html +='      		</div>';
				html +='		</div>';
				html +='  </div>';
				html +='</div>';
			}	
			break;
		case '2'://MODALER
			{	modalId="#by-modaler";
				html  ='<div id="by-modaler" class="modal fade">';
				html +='  <div id="modal-modaler" class="modal-dialog modal-lg modal-dialog-centered">';
				html +='		<div class="modal-content">';
				html +='			<div class="modal-body"></div>';
				html +='			<div class="modal-footer"></div>';
				html +='		</div>';
				html +='	</div>';
				html +='</div>';
			}
			break;
		default:
			{	modalId="#byLoader";
				html  ='<div id="byLoader" class="modal fade">';
				html += '	<div class="modal-dialog modal-dialog-centered">';
				html +='		<div class="spinner-grow" style="width: 3rem; height: 3rem;" role="status">';
				html +='			<span class="visually-hidden">Prossesing...</span>';
				html +='		</div>';
				html +='	</div>';
				html +='</div>';
			}	
			break;	
	}
	
	if($(modalId).length==0){
		$('body').append(html);
	}
}

function Backdrop(TXT,TYP,OBJ){
	if($('#staticBackdrop').length==0){ appendToBody('3');}else{
		$('#staticBackdrop .modal-content').children('div').each(function(){
			//$(this).html('');
		});
	}
	Icn = '<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:"><use xlink:href="#question-circle-fill"/></svg>';
	Iin = '<div class="alert alert-info" role="alert">';
			
	$('#staticBackdrop .modal-icon').html(Icn);
	$('#staticBackdrop .modal-header').html('CONFIRM');
	
	Arr = TXT.split(": ");
	Arr1 = TXT.split("<br/>");
	if(!Arr[1] && Arr1[1] ){
		$('#staticBackdrop .modal-header').html(Arr1[0]);
		$('#staticBackdrop .modal-body').html(Iin + Arr1[1] + '</div>');
	}else if( Arr[1] && !Arr1[1] ){
		$('#staticBackdrop .modal-header').html(Arr[0]);
		$('#staticBackdrop .modal-body').html(Iin + Arr[1] + '</div>');
	}else{
		$('#staticBackdrop .modal-body').html(Iin + Arr[0] + '</div>');
	}
	options = {
	  keyboard: false,
	  backdrop: "static",	
	}
	element = document.querySelector('#staticBackdrop');
	modal = new bootstrap.Modal(element,options);
	modal.show();
	$('#staticBackdrop').on('click','button.btn-primary',function(){
		$(OBJ).remove();
		modal.hide();
	});  
	
	element.addEventListener('show.bs.modal', event => {
  		/*const button = event.relatedTarget
	  	const recipient = button.getAttribute('data-bs-remover')
  
  		const modalTitle = exampleModal.querySelector('.modal-title')
  		const modalBodyInput = exampleModal.querySelector('.modal-body input')

  		modalTitle.textContent = `New message to ${recipient}`
  		modalBodyInput.value = recipient*/
	});
	
	          	
}

function alertDivided(TXT,TYP){
	if($('#alerter').length==0){ appendToBody('1');}else{
		$('#alerter .modal-content').children('div').each(function(){
			$(this).html('');
		});
	}
	
	Icn = '';
	
	switch(TYP){
		case 'danger':
			Icn = '<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>';
			Iin = '<div class="alert alert-danger" role="alert">';
			break;
			
		case 'warning':
			Icn = '<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Warning:"><use xlink:href="#exclamation-triangle-fill"/></svg>';
			Iin = '<div class="alert alert-warning" role="alert">';
			break;
		case 'success':
			Icn = '<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>';
			Iin = '<div class="alert alert-success" role="alert">';
			break;
		default:
			Icn = '<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>';
			Iin = '<div class="alert alert-info" role="alert">';
			
	}
	
	$('#alerter .modal-icon').html(Icn)
	
	Arr = TXT.split(": ");
	Arr1 = TXT.split("<br/>");
	if(!Arr[1] && Arr1[1] ){
		$('#alerter .modal-header').html(Arr1[0]);
		$('#alerter .modal-body').html(Iin + Arr1[1] + '</div>');
	}else if( Arr[1] && !Arr1[1] ){
		$('#alerter .modal-header').html(Arr[0]);
		$('#alerter .modal-body').html(Iin + Arr[1] + '</div>');
	}else{
		$('#alerter .modal-body').html(Iin + Arr[0] + '</div>');
	}
	
	element = document.querySelector('#alerter');
	modal = new bootstrap.Modal(element);
	modal.show();
	            	
}


function ajaxControl(html){
	var n = html.search("<html");
	if(n>-1) location.reload();
}

var opened;
var loader = {
	'create': function() {
		appendToBody('0');
	},
	'loading': function(Ind) {
		opened += ',' + Ind;
		//elem = document.querySelector('#byLoader');
		//loadM = new bootstrap.Modal(elem);
		/*loadM.show();
		if($('#byLoader .spiner i').length==0){
			$('#byLoader .spiner').html('<i class="fa fa-refresh fa-spin"></i><span>Lütfen Bekleyin!</span>');
		}
		*/
		//$('[type="button"]').prop('disabled', true);
	},
	'loaded': function(Ind) {
		/*
		if(opened){
			var n = opened.indexOf(','+Ind);
			if(n!== -1){
				opened = opened.replace(','+Ind, "");
				if(opened == ""){
					$('#byLoader').hide();
				}
			}
		}
		$('#byLoader').hide();
		*/
	},
	'process':function() {
	}
}
	

$.ajaxSetup({
	  	beforeSend: function(jqXHR) {
	  		loader.loading(jqXHR.requestId);
	  		$('body').addClass('wait');
	  	},
		complete: function(jqXHR) {
			loader.loaded(jqXHR.requestId);
			$('body').removeClass('wait')
		},
		xhr: function () {
	        var xhr = new window.XMLHttpRequest();
	        xhr.addEventListener("progress", function (evt) {
	            if (evt.lengthComputable) {
	                var percentComplete = evt.loaded / evt.total;
	            }
	        }, false);
	        return xhr;
	    },
		error: function(xhr, ajaxOptions, thrownError) {
			$('body').removeClass('wait')
			if(xhr.responseText){
				alert(thrownError + "\r\n ****************** \r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
			//location.reload();
		    return false;
		}
});

$.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
  	jqXHR.requestId = Math.floor( (Math.random() * 9999999999) + 1 )
});

$(function() {
	loader.create();
});

$(document).ajaxStop(function() {
	$('body').addClass('stoped')
	// ajax Stop
});	

$(document).ready(function(){
	svg  = '<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">';
	
  	svg += '<symbol id="check-circle-fill" viewBox="0 0 16 16">';
  	svg += '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>';
  	svg += '</symbol>';
  	
  	svg += '<symbol id="info-fill" viewBox="0 0 16 16">';
  	svg += '<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>';
  	svg += '</symbol>';
  	
  	svg += '<symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">';
  	svg += '<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>';
  	svg += '</symbol>';
  	
  	svg += '<symbol id="emoji-heart-eyes" viewBox="0 0 16 16">';
	svg += '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M11.315 10.014a.5.5 0 0 1 .548.736A4.498 4.498 0 0 1 7.965 13a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .548-.736h.005l.017.005.067.015.252.055c.215.046.515.108.857.169.693.124 1.522.242 2.152.242.63 0 1.46-.118 2.152-.242a26.58 26.58 0 0 0 1.109-.224l.067-.015.017-.004.005-.002zM4.756 4.566c.763-1.424 4.02-.12.952 3.434-4.496-1.596-2.35-4.298-.952-3.434zm6.488 0c1.398-.864 3.544 1.838-.952 3.434-3.067-3.554.19-4.858.952-3.434z"/>';
  	svg += '</symbol>';
  	
  	svg += '<symbol id="question-circle-fill" viewBox="0 0 16 16">';
  	svg += '<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247m2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/>';
  	svg += '</symbol>';
  	svg += '</svg>';
	$('body').append(svg);	
	
	//loader.loaded();
	
	$(".page-ctrl-controls,.ctrl-controls").each(function(e) {
		Cont = $(this);
		var tWidth = 0;
		$(Cont).children().each(function(){
			tWidth = tWidth + $(this).outerWidth();
		});
		tWidth = tWidth + 40
		$(Cont).width(tWidth);
		$(Cont).data('iwidth',tWidth)
		$(Cont).data('owidth',tWidth+28 );
		$(Cont).addClass('fascinated')
		$(".page-ctrl-controls").trigger('mouseleave')
		$(".ctrl-controls").trigger('mouseleave')
	});	
	
	$(".page-ctrl-controls,.ctrl-controls").mouseenter(function() {
		if($(this).hasClass('smalled')){
			$(this).animate({marginRight:'0px'},100,function(){
				$(this).addClass('fascinated').removeClass('smalled');
			})
		}
	});

	$(".page-ctrl-controls,.ctrl-controls").mouseleave(function() {
		if($(this).hasClass('fascinated')){
			$(this).animate({marginRight:'-' + $(this).data('owidth') + 'px'},100,function(){
				$(this).addClass('smalled').removeClass('fascinated');
			})
		}
		
	});
	
	$(".page-ctrl-controls,.ctrl-controls").trigger('mouseleave');
	
});

$(document).on('click','a.no-link', function (e) {
	e.preventDefault();
	e.stopPropagation();
				
});

$(document).on('click','.check-img', function (e) {
	html=$('.check-img:checked').length+ 'item/s Selected';
	$('#fdesc').html(html);
});

$(document).on('click', 'a[data-oc-toggle=\'ajax_json\']', function (e) {
	    e.preventDefault();
	    e.stopPropagation();
	   	tUrl = $(this).attr('href');
		 $.ajax({
	            url: tUrl,
	            dataType: 'json',
	            success: function (json) {
	                if(json['pid']){
	                	if(json['type']=='status'){
	                		$('#p'+json['pid']+'.disabled').addClass('enabled').removeClass('disabled')
	                	}else{
	                		$('#p'+json['pid']+' .'+json['type']).addClass('active')
	                	}
	                }
	            }
	        });

	});  

$(document).on('click', 'a[data-oc-toggle=\'ajax_html\']', function (e) {
	    e.preventDefault();
	    e.stopPropagation();
	    
	   	tUrl = $(this).attr('href');
	   	target = $(this).attr('data-oc-target');
	   	$('#'+target).load(tUrl,function(){
	   		
	   	});

	});  

$(document).on('change focusout blur', '.input-seo-url', function (e) {
   thisURL =$(this).val();
   $(this).val(toSeoUrl(thisURL));
});

$(document).on('click','.top-store-div', function (e) {
	if(!$(this).hasClass('active')){
		var StoreId = $(this).data('storeid');
		$.ajax({
			url: 'index.php?route=bytao/common.clickstore&user_token=' + getURLVar('user_token') + '&store_id=' + encodeURIComponent(StoreId),
			dataType: 'json',
			contentType: 'application/json; charset=utf-8',
			success: function(JSON) {
				if(JSON['storeId']){
					location.reload();
				}
			}
		});
	}
});

$(document).on('click', '#modal-image #button-folder', function (e) {
  		e.preventDefault();
  		$('#modal-folder').slideToggle();
});

toSeoUrl = function(text) {
    var trMap = {
        'çÇ':'c',
        'ğĞ':'g',
        'şŞ':'s',
        'üÜ':'u',
        'ıİ':'i',
        'öÖ':'o'
    };
    for(var key in trMap) {
        text = text.replace(new RegExp('['+key+']','g'), trMap[key]);
    }
    
    var WW =  text.replace(/[^-a-zA-Z0-9\s]+/ig, '') // remove non-alphanumeric chars
                .replace(/\s/gi, "-") // convert spaces to dashes
                .replace(/[-]+/gi, "-") // trim repeated dashes
                .toLowerCase();
                
    var itemN =$('.itemId').attr('name');
    var itemID =$('.itemId').val(); 
     
    if($('.itemId').length){
		$.ajax({
	        url: 'index.php?route=bytao/common.wordcheck&user_token=' + getURLVar('user_token')+"&"+itemN+"="+itemID,
	        type: 'post',
	        data: {word:WW,language_id:L},
	        dataType: 'json',
	        contentType: 'application/x-www-form-urlencoded',
	        success: function (json) {
	        	$(Obj).find('i').removeClass('fa-xmark fa-check')
	        	if(json['confirm']){
					$(Obj).find('i').addClass('fa-check');	
				}else{
					$(Obj).find('i').addClass('fa-xmark');	
				}
	        }
	    });	
	}          
	return WW;	    
}
