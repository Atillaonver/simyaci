function ALRTR(){
	html  ='<div id="alerter" class="modal">';
	html +='  <div id="modal-alerter" class="modal-dialog modal-lg modal-dialog-centered">';
	html +='	<div class="modal-dialog modal-sm">';
	html +='		<div class="modal-content">';
	html +='		<div class="modal-body"></div>';
	html +='	</div>';
	html +='	</div>';
	html +='	</div>';
	html +='</div>';
	$('body').append(html);
}

function MODALER(){
	html  ='<div id="by-modaler" class="modal">';
	html +='  <div id="modal-modaler" class="modal-dialog modal-lg modal-dialog-centered">';
	html +='	<div class="modal-dialog modal-sm">';
	html +='		<div class="modal-content">';
	html +='		<div class="modal-body"></div>';
	html +='		<div class="modal-footer"></div>';
	html +='	</div>';
	html +='	</div>';
	html +='	</div>';
	html +='</div>';
	$('body').append(html);
}

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

$(document).on('change', '.input-seo-url', function (e) {
   thisURL =$(this).val();
   $(this).val(toSeoUrl(thisURL));
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
    return  text.replace(/[^-a-zA-Z0-9\s]+/ig, '') // remove non-alphanumeric chars
                .replace(/\s/gi, "-") // convert spaces to dashes
                .replace(/[-]+/gi, "-") // trim repeated dashes
                .toLowerCase();

}


	
	
