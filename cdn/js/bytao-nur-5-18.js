window.onscroll = function() {myFunction()};
/*window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
*/
function myFunction() {
    var navbar = document.getElementById("myNavbar");
    if (document.body.scrollTop > 60 || document.documentElement.scrollTop > 60) {
        navbar.className = "w3-bar" + " w3-card" + " w3-animate-top" + " w3-white";
    } else {
        navbar.className = navbar.className.replace(" w3-card w3-animate-top w3-white", "");
    }
    scrollFunction()
}


function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        $("#myBtn").show();
    } else {
        $("#myBtn").hide();
    }
}

function clickHref(URL) {
	location.href = URL;
	}
	
function mMenu(x) {
    x.classList.toggle("change");
    toggleFunction()
}

function closeModal(){
	var Modal = document.getElementById("modal01");
	Modal.style.display='none'
}

function topFunction() {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}
// Used to toggle the menu on small screens when clicking on the menu button

function toggleFunction() {
    if ($("#navDemo").hasClass("w3-show")) {
        $("#navDemo").removeClass("w3-show");
    } else {
       $("#navDemo").addClass(" w3-show");
    }
}

function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}

$(document).on('click','.btn-lang',function(){
  	var element = this;
  	//$('body').prepend('<form enctype="multipart/form-data" id="form-language" method="post" style="display: none;"><input type="file" name="file" value=""/></form>');
  	location.href = $(element).data('href');
});

$(document).ready(function ()
	{
		$('.search-button').on('click',function(){
			
			document.documentElement.scrollTop = 0;
			$('.nav-search').addClass('open');
			
		})
		$('#closebtn').on('click',function(){
			$('.nav-search').removeClass('open');
			mMenu(document.getElementById("topMenuClose"));
		})
		// Search
		$('#insearchbtn').on('click', function()
			{
				url = $('#searchsection').data('href');
				var value = $('#search input[name=\'search\']').val();
				if (value)
				{
					url += '?search=' + encodeURIComponent(value);
				}
				location = url;
			});
			
		$('#search input[name=\'search\']').on('keydown', function(e)
			{
				if (e.keyCode == 13)
				{
					
					$('#insearchbtn').trigger('click');
				}
			});
	});

$(document).on('submit', 'form[data-oc-toggle=\'ajax\']', function(e) {
    e.preventDefault();

    var element = this;

    var form = e.target;

    var action = $(form).attr('action');

    if (e.originalEvent !== undefined && e.originalEvent.submitter !== undefined) {
        var button = e.originalEvent.submitter;
    } else {
        var button = '';
    }

    var formaction = $(button).attr('formaction');

    if (formaction !== undefined) {
        action = formaction;
    }

    var method = $(form).attr('method');

    if (method === undefined) {
        method = 'post';
    }

    var enctype = $(element).attr('enctype');

    if (enctype === undefined) {
        enctype = 'application/x-www-form-urlencoded';
    }

    var html = $(button).html();
    var width = $(button).width();

    // https://github.com/opencart/opencart/issues/9690
    if (typeof CKEDITOR != 'undefined') {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }

    
    $.ajax({
        url: action,
        type: method,
        data: $(form).serialize(),
        dataType: 'json',
        cache: false,
        contentType: enctype,
        processData: false,
        beforeSend: function() {
            //$(button).button('loading');
        },
        complete: function() {
            //$(button).button('reset');
        },
        success: function(json) {
            $('.alert-dismissible').remove();
            $(form).find('.is-invalid').removeClass('is-invalid');
            $(form).find('.invalid-feedback').removeClass('d-block');

            console.log(json);

            if (json['redirect']) {
                //location = json['redirect'].replaceAll('&', '&');
            }

            if (typeof json['error'] == 'string') {
                $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }

            if (typeof json['error'] == 'object') {
                if (json['error']['warning']) {
                    $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error']['warning'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                }

                for (var key in json['error']) {
                    $('#input-' + key.replaceAll('_', '-')).addClass('is-invalid').find('.form-control, .form-select, .form-check-input, .form-check-label').addClass('is-invalid');
                    $('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');
                }
            }

            if (json['success']) {
                $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');

                // Refresh
                var url = $(form).attr('data-oc-load');
                var target = $(form).attr('data-oc-target');

                if (url !== undefined && target !== undefined) {
                    $(target).load(url);
                }
            }

            // Replace any form values that correspond to form names.
            for (var key in json) {
                $(form).find('[name=\'' + key + '\']').val(json[key]);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});