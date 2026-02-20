
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

function toggleFullScreen() {
  if (!document.fullscreenElement &&    // alternative standard method
      !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) {  // current working methods
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
      //$('#btn-full').hide();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
      //$('#btn-full').show();
    }
  }
}


	
function online(){
	onl = setInterval(function() {
	    $.ajax({
			url: 'index.php?route=bytao/trivia|online&user_token='+ getURLVar('user_token')+ '&trivia_id='+getURLVar('trivia_id')+'&qq='+ QQ,
			dataType: 'json',
			success: function(json) {
				if(json['all']){
					if(json['all']=='1'){
						$('.by-btn').show();
					}else{
						$('.by-btn').hide();
					}
				}
				
				if(json['participants']){
					$('#firstboard').html(json['participants'])
				}
			}
		});	
	}, 1000);
}

$( window ).unload(function(){
	$.ajax({
			url: 'index.php?route=bytao/trivia|eventClose&user_token='+ getURLVar('user_token')+ '&trivia_id='+getURLVar('trivia_id'),
			dataType: 'json',
			success: function(json) {}
	});	
});

$(document).on('click', '[data-by-toggle=\'ajax\']', function (e) {
	
    var url = $(this).attr('data-by-href');
    var BTN = $(this);
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded',
        beforeSend: function () {
       		$(BTN).addClass('clicked');
        },
        complete: function () {
       		$(BTN).removeClass('clicked');
        },
        success: function (json) {
			if(json['quest']){
				QQ = json['quest'];
			}
			if(json['atc']){
				ACT = json['act'];
			}
			
			if(json['content']){
				
				if(json['content']['title']){
					$('.title').html(json['content']['title']);
				}
				
				if(json['content']['finish']){
					clearInterval(tt);
					clearInterval(strt);
					clearInterval(onl);
					$('#page-content').html(json['content']['finish']);
				}
				
				if(json['content']['board']){
					if(json['content']['online']){online();}
					$('#page-content').html(json['content']['board']);
				}
				
				if(json['content']['question']){
					$('#page-content').html(json['content']['question']);
					//clearInterval(onl);
					$('.by-btn').hide();
					$('#page-content').animate({opacity: 1,}, 1000, function() {
						if(json['time']){
							timer = json['time'];
							tt = setInterval(function() {
	    						timer--;
	    						$('#count').html(timer);
	    						if(timer<1){
	    							$('.by-btn').show();
	    							clearInterval(tt);
	    							$.ajax({
										url: 'index.php?route=bytao/trivia|addevent&user_token='+ getURLVar('user_token')+ '&trivia_id='+getURLVar('trivia_id')+'&act='+ACT+'&quest='+QQ,
										dataType: 'json',
										success: function(json) {
											
										}
									});
	    							
	    						}
							}, 1000);
						}
					});
					QQ = json['content']['order'];
				}	
				
				if(json['content']['ansver']){
					clearInterval(tt);
					clearInterval(strt);
					$('#page-content').html(json['content']['ansver']);	
				}	
				
				
				if(json['content']['startX']){
					
					$('#page-content').html(json['content']);
					
					$('.counter').removeClass('op0')
					$('.body.body-content').html(json['qview']);
					
					
					$('#page-content').animate({opacity: 1,}, 1000, function() {
						if(json['time']){
							timer = json['time'];
							tt = setInterval(function() {
	    						timer--;
	    						$('#count').html(timer);
	    						if(timer<1){
	    							$('.by-btn').show();
	    							clearInterval(tt);
	    							
	    							$.ajax({
										url: 'index.php?route=bytao/trivia|addevent&user_token='+ getURLVar('user_token')+ '&trivia_id='+getURLVar('trivia_id')+'&act='+act+'&quest='+quest,
										dataType: 'json',
										success: function(json) {
											
										}
									});
	    							
	    						}
							}, 1000);
						}
					})
				}
							
			}
			
			
			
			
			
		}
	});		
	
	return false;
}); 
