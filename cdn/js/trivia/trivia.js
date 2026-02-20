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

function tTmr() {
  tCount++;
  if(tCount>60){
  	clearInterval(onAir);
  }
}
	
function online_(){
		onl = setInterval(function() {
			
		    $.ajax({
				url: 'index.php?route=bytao/trivia|online&cid='+cId+'&sid='+sid+'&trivia_id='+tvId,
				dataType: 'json',
				success: function(json) {
					if(json['wait']){
						if(!$('.tri-body').hasClass('wait')){
							$('.tri-body').addClass('wait');
							$('.opt.active').removeClass('active')
							clearInterval(tCnt);
						}
					}

					if(json['quest']){
						if(q != json['quest'] && ansQ != json['quest']){
							tCnt = setInterval(tTmr, 1000);
							q = json['quest'];
							$('.tri-body').removeClass('wait');
							$('#ans > span > span').html(q);
						}
					}
					
					if(json['stop']){
						clearInterval(onl);
					}
					
					if(json['so']){
						$('#so').html(json['so']);
						alert(json['so'])
						if(json['so'] == 0) {
							clearInterval(onl);
							alert()
						}
					}
					if(json['finish']){
						clearInterval(onl);
						$('.tri-body').html(json['finish'])
					}
				}
			});	
	  	}, 750);
	}

function online(){
	onl = setInterval(function() {
		$.ajax({
			url: 'index.php?route=bytao/trivia|online&cid='+cId+'&sm='+sM+'&sid='+sId+'&trivia_id='+tvId,
			dataType: 'json',
			success: function(json) {
				
				if(json['wait']){
					if(!$('.tri-body').hasClass('wait')){
						$('.tri-body').addClass('wait');
						$('.opt.active').removeClass('active')
						clearInterval(onAir);
					}
				}

				if(json['quest']){
					if(q != json['quest']  && ansQ != json['quest']){
						tCount=0;
						onAir = setInterval(tTmr, 1000);
						q = json['quest'];
						qTime = json['time'];;
						$('.tri-body').removeClass('wait');
					}
					$('#ans > span > span').html(q);
				}
					
				if(json['so']){
					if(json['so'] == 0) {
						$('#so').html('');
						clearInterval(onl);
					}else{
						$('#so').html(json['so']);
					}
				}
					
				if(json['until']){
					clearInterval(onl);
				}
					
				if(json['message']){
					alert(json['message']['content'])
				}
					
				if(json['stop']){
					clearInterval(onl);
				}
				
				if(json['redirect']){
					clearInterval(onl);
					location = json['redirect'];
				}
					
				if(json['finish']){
					clearInterval(onl);
					$('.tri-body').html(json['finish'])
				}
			}
		});	
	}, onAirTime);
}
	
	
$(document).on('click', '[data-by-toggle=\'ajax\']', function (e) {
    var url = $(this).attr('data-by-href');
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded',
        beforeSend: function () {
       
        },
        complete: function () {
       
        },
        success: function (json) {}
	});	
});	
