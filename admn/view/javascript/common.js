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
	
// On August 17 2021, Internet Explorer 11 (IE11) will no longer be supported by Microsoft's 365 applications and services.
function isIE() {
    if (!!window.ActiveXObject || "ActiveXObject" in window) return true;
}

// Header
$(document).ready(function () {
    // Header
    $('#header-notification [data-bs-toggle=\'modal\']').on('click', function (e) {
        e.preventDefault();

        var element = this;

        $('#modal-notification').remove();

        $.ajax({
            url: $(element).attr('href'),
            dataType: 'html',
            success: function (html) {
                $('body').append(html);

                $('#modal-notification').modal('show');
            }
        });
    });
});

// Menu
$(document).ready(function () {
    $('#button-menu').on('click', function (e) {
        e.preventDefault();

        $('#column-left').toggleClass('active');
    });

    // Set last page opened on the menu
    $('#menu a[href]').on('click', function () {
        sessionStorage.setItem('menu', $(this).attr('href'));
    });

    if (!sessionStorage.getItem('menu')) {
        $('#menu #menu-dashboard').addClass('active');
    } else {
        // Sets active and open to selected page in the left column menu.
        $('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parent().addClass('active');
    }

    $('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li').children('a').removeClass('collapsed');

    $('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('ul').addClass('show');

    $('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li').addClass('active');
});

$(document).ready(function () {
    // Tooltip
    var oc_tooltip = function () {
        // Get tooltip instance
        tooltip = bootstrap.Tooltip.getOrCreateInstance(this);

        if (!tooltip) {
            // Apply to current element
            tooltip.show();
        }
    }

    $(document).on('mouseenter', '[data-bs-toggle=\'tooltip\']', oc_tooltip);

    $(document).on('click', 'button', function () {
        $('.tooltip').remove();
    });

    // Date
    var oc_datetimepicker = function () {
        $(this).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function (start, end) {
            $(this.element).val(start.format('YYYY-MM-DD'));
        });
    }

    $(document).on('focus', '.date', oc_datetimepicker);

    // Time
    var oc_datetimepicker = function () {
        $(this).daterangepicker({
            singleDatePicker: true,
            datePicker: false,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'HH:mm'
            }
        }, function (start, end) {
            $(this.element).val(start.format('HH:mm'));
        }).on('show.daterangepicker', function (ev, picker) {
            picker.container.find('.calendar-table').hide();
        });
    }

    $(document).on('focus', '.time', oc_datetimepicker);

    // Date Time
    var oc_datetimepicker = function () {
        $('.datetime').daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'YYYY-MM-DD HH:mm'
            }
        }, function (start, end) {
            $(this.element).val(start.format('YYYY-MM-DD HH:mm'));
        });
    }

    $(document).on('focus', '.datetime', oc_datetimepicker);

    // Alert Fade
    var by_alert = function () {
        window.setTimeout(function () {
            $('.alert-dismissible').fadeTo(1000, 0, function () {
                //$(this).remove();
                $('#alerter').modal('hide');
            });
        }, 6000);
    }

    $(document).on('click', 'button', by_alert);
    
    $(document).on('change','.word-check > input',function(){
    	var W =$(this).val();
    	var L = $(this).attr('lang');
    	var K = $(this).attr('key');
    	var Obj=$(this);
    	var itemN =$('.item_id').attr('name');
    	var itemID =$('.item_id').val();
    	$.ajax({
	        url: 'index.php?route=bytao/common.wordcheck&user_token=' + getURLVar('user_token')+"&"+itemN+"="+itemID,
	        type: 'post',
	        data: {word:W,language_id:L,key:K},
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
	});
	    
    
});


  // ajax content
$(document).on('click', '[data-oc-toggle=\'ajaxpaginate\']', function (e) {
    e.preventDefault();
    $( $(this).parents('[data-oc-toggle=\'ajaxpaginate\']').attr('data-target') ).load( $(this).attr('href'), function() {});
	
	return false;
}); 

  // ajax content
$(document).on('click', 'a[data-by-toggle=\'ajax\']', function (e) {
    e.preventDefault();
    var action = $(this).attr('href');
    $.ajax({
        url: action.replaceAll('&amp;', '&'),
        type: 'post',
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded',
        success: function (json) {
            if (json['refresh']) {
            	location = location;
            }
            if (json['success']) {
                alertDivided(json['success'],'success');
            }
        }
    });
	return false;
});  

$(document).on('click', 'a[data-oc-toggle=\'ajax\']', function (e) {
    e.preventDefault();
    $('ul.toolbar active').removeClass('active');
    $(this).parent().addClass('active');
    $($(this).attr('data-target')).load( $(this).attr('href'), function() {});
	
	return false;
});  

  // ajax Modal
 
$(document).on('click', 'a[data-oc-toggle=\'ajax-modal\']', function (e) {
    e.preventDefault();

    var action = $(this).attr('href'); 
    if($(this).find('.fa-envelope-circle-check').length){
		action = action+'&first=2'
	}else{
		action = action+'&first=1'
	}
    
    $('#ajax-modal .modal-body').load(action, function() {
    	 $('.tooltip').remove();
    	$('#modal-ajax-comtent').modal('show');
    });
	return false;
}); 
  
// Forms
$(document).on('submit', 'form[data-oc-toggle=\'ajax\']', function (e) {
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

	// https://github.com/opencart/opencart/issues/9690
	if (typeof CKEDITOR != 'undefined') {
		for (instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
	}

	$.ajax({
		url: action.replaceAll('&amp;', '&'),
		type: method,
		data: $(form).serialize(),
		dataType: 'json',
		contentType: 'application/x-www-form-urlencoded',
		beforeSend: function () {
			$(button).button('loading');
		},
		complete: function () {
			$(button).button('reset');
		},
		success: function (json) {
			$('.taberr').remove();
			$('.alert-dismissible').remove();
			$(element).find('.is-invalid').removeClass('is-invalid');
			$(element).find('.invalid-feedback').removeClass('d-block');
			
			if (json['redirect']) {
				location = json['redirect'];
			}


			if (typeof json['error'] == 'string') {
				if (json['error']['warning']) {
					alertDivided(json['error']['warning'],'warning');
				}
			}

			if (typeof json['error'] == 'object') {
				if (json['error']['warning']) {
					alertDivided(json['error']['warning'],'warning');
				}

				for (key in json['error']) {
					$('#input-' + key.replaceAll('_', '-')).addClass('is-invalid').find('.form-control, .form-select, .form-check-input, .form-check-label').addClass('is-invalid');
					$('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');

					tabN = $('#input-' + key.replaceAll('_', '-')).closest('.tab-pane').attr('id');

					if (!$("a[href='#"+tabN+"']").find('.taberr').length) {
						$("a[href='#"+tabN+"']").css('position','relative').append('<span class="taberr">!</span>');
					}
				}
			}

			if (json['refresh']) {
				//location = location;
			}

			if (json['success']) {
				alertDivided(json['success'],'success');
				// Refresh
				var url = $(form).attr('data-oc-load');
				var target = $(form).attr('data-oc-target');

				if (url !== undefined && target !== undefined) {
					$('#'+target).load(url, function() {
						var goTO = $(form).attr('data-oc-go');
						if (goTO !== undefined) {
							$('html, body').animate({
								scrollTop: $("#"+goTO).offset().top
							}, 1000);
						}
					});
				}
			}

			if (json['incontent']) {
				var target = $(form).attr('data-oc-target');
				if (target !== undefined) {
					$('#'+target).html(json['incontent']);
				}
				var goTO = $(form).attr('data-oc-go');
				if (goTO !== undefined) {
					$('html, body').animate({
						scrollTop: $("#"+goTO).offset().top
					}, 1000);
				}
			}

			if (json['set']) {
				for (key in json['set']) {
					$('#'+key).val(json['set'][key])
				}
			}

			// Replace any form values that correspond to form names.
			for (key in json) {
				$(element).find('[name=\'' + key + '\']').val(json[key]);
			}
		}
	});
});



$(document).on('shown.bs.modal','#alerter', function (e) {
  	setTimeout(function(){$('#alerter').modal('hide');} , 1000000);
})

// Upload
$(document).on('click', '[data-oc-toggle=\'upload\']', function () {
    var element = this;

    if (!$(element).prop('disabled')) {
        $('#form-upload').remove();

        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" value=""/></form>');

        $('#form-upload input[name=\'file\']').trigger('click');

        $('#form-upload input[name=\'file\']').on('change', function (e) {
            if ((this.files[0].size / 1024) > $(element).attr('data-oc-size-max')) {
                alert($(element).attr('data-oc-size-error'));
				
                $(this).val('');
            }
        });

        if (typeof timer != 'undefined') {
            clearInterval(timer);
        }

        var timer = setInterval(function () {
            if ($('#form-upload input[name=\'file\']').val() != '') {
                clearInterval(timer);

                $.ajax({
                    url: $(element).attr('data-oc-url'),
                    type: 'post',
                    data: new FormData($('#form-upload')[0]),
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(element).button('loading');
                    },
                    complete: function () {
                        $(element).button('reset');
                    },
                    success: function (json) {
                        console.log(json);

                        if (json['error']) {
                            alertDivided(json['error'],'danger');
                        }

                        if (json['success']) {
                            alertDivided(json['success'],'success');
                        }

                        if (json['code']) {
                            $($(element).attr('data-oc-target')).val(json['code']);

                            $(element).parent().find('[data-oc-toggle=\'download\'], [data-oc-toggle=\'clear\']').prop('disabled', false);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        }, 500);
    }
});

$(document).on('click', '[data-oc-toggle=\'download\']', function (e) {
    var element = this;

    var value = $($(element).attr('data-oc-target')).val();

    if (value != '') {
        location = 'index.php?route=tool/upload.download&user_token=' + getURLVar('user_token') + '&code=' + value;
    }
});

$(document).on('click', '[data-oc-toggle=\'clear\']', function () {
    var element = this;

    // Images
    var thumb = $(this).attr('data-oc-thumb');

    if (thumb !== undefined) {
        $(thumb).attr('src', $(thumb).attr('data-oc-placeholder'));
    }

    // Custom fields
    var download = $(element).parent().find('[data-oc-toggle=\'download\']');

    if (download.length) {
        $(element).parent().find('[data-oc-toggle=\'download\'], [data-oc-toggle=\'clear\']').prop('disabled', true);
    }

    $($(this).attr('data-oc-target')).val('');
});

$(document).on('click', '[data-check-toggle]', function () {
    var element = this;
    var Clss = $(this).attr('data-check-toggle');
    $('.'+Clss).prop("checked",$( element ).prop( "checked" ));
});

// Image Manager

$(document).on('click', '[data-oc-toggle=\'image\']', function (e) {
    var element = this;
	li=$(this).data('li'),
    
    $('#modal-image').remove();
    
	if(li){
		URL = 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token');
	}else{
		URL = 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&target=' + encodeURIComponent($(element).attr('data-oc-target')) + '&thumb=' + encodeURIComponent($(element).attr('data-oc-thumb'))
	}
	
    $.ajax({
        url: URL ,
        dataType: 'html',
        beforeSend: function () {
            $(element).button('loading');
        },
        complete: function () {
            $(element).button('reset');
        },
        success: function (html) {
            $('body').append(html);
            var element = document.querySelector('#modal-image');
            var modal = new bootstrap.Modal(element);
            modal.show();
        }
    });
});

// Chain ajax calls.
class Chain {
    constructor() {
        this.start = false;
        this.data = [];
    }

    attach(call) {
        this.data.push(call);

        if (!this.start) {
            this.execute();
        }
    }

    execute() {
        if (this.data.length) {
            this.start = true;

            var call = this.data.shift();

            var jqxhr = call();

            jqxhr.done(function () {
                chain.execute();
            });
        } else {
            this.start = false;
        }
    }
}

var chain = new Chain();

// Autocomplete
+function ($) 
{
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            var element = this;
            var $dropdown = $('#' + $(element).attr('data-oc-target'));

            this.timer = null;
            this.items = [];

            $.extend(this, option);

            // Focus in
            $(element).on('focusin', function () {
                element.request();
            });

            // Focus out
            $(element).on('focusout', function (e) {
                if (!e.relatedTarget || !$(e.relatedTarget).hasClass('dropdown-item')) {
                    $dropdown.removeClass('show');
                }
            });

            // Input
            $(element).on('input', function (e) {
                element.request();
            });

            // Click
            $dropdown.on('click', 'a', function (e) {
                e.preventDefault();

                var value = $(this).attr('href');
                var label = $(this).html();
				if (element.items[value] !== undefined) {
					element.select(element.items[value]);
                    $dropdown.removeClass('show');
                }
            });

            // Request
            this.request = function () {
                clearTimeout(this.timer);

                $('#autocomplete-loading').remove();

                $dropdown.prepend('<li id="autocomplete-loading"><span class="dropdown-item text-center disabled"><i class="fa-solid fa-circle-notch fa-spin"></i></span></li>');
                $dropdown.addClass('show');

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 50, this);
            }

            // Response
            this.response = function (json) {
                var html = '';
                var category = {};
                var name;
                var i = 0, j = 0;

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        // update element items
                        this.items[json[i]['value']] = json[i];

                        if (!json[i]['category']) {
                            // ungrouped items
                            html += '<li><a href="' + json[i]['value'] + '" class="dropdown-item">' + json[i]['label'] + '</a></li>';
                        } else {
                            // grouped items
                            name = json[i]['category'];

                            if (!category[name]) {
                                category[name] = [];
                            }

                            category[name].push(json[i]);
                        }
                    }

                    for (name in category) {
                        html += '<li><h6 class="dropdown-header">' + name + '</h6></li>';

                        for (j = 0; j < category[name].length; j++) {
                            html += '<li><a href="' + category[name][j]['value'] + '" class="dropdown-item">' + category[name][j]['label'] + '</a></li>';
                        }
                    }
                }
                $dropdown.html(html);
            }
        });
    }
}(jQuery);

// Button
$(document).ready(function() {
	
    +function($) {
        $.fn.button = function(state) {
            return this.each(function() {
                var element = this;

                if (state == 'loading') {
                    this.html = $(element).html();
                    this.state = $(element).prop('disabled');

                    $(element).prop('disabled', true).width($(element).width()).html('<i class="fa-solid fa-circle-notch fa-spin text-light"></i>');
                }

                if (state == 'reset') {
                    $(element).prop('disabled', this.state).width('').html(this.html);
                }
            });
        }
    }(jQuery);
});
/*
(function() {

	const MAX_CHUNK = 200000; // Her parçanın maksimum boyutu

	// Chunked gönderim fonksiyonu
	window.sendChunkedForm = function($form, callback) {
		if (!$form || !$form.length)
			return;
		var formEl = $form[0];
		if (!(formEl instanceof HTMLFormElement))
			return;

		// FormData to JSON
		const formData = new FormData(formEl);
		let json = {};
		formData.forEach((v, k) => { json[k] = v; });
		const raw = JSON.stringify(json);

		// Küçük form → callback çağır
		if (raw.length <= MAX_CHUNK) {
			if (typeof callback === 'function')
				callback();
			return;
		}

		// Büyük form → chunklara ayır
		let chunks = [];
		for (let i = 0; i < raw.length; i += MAX_CHUNK) {
			chunks.push(raw.substring(i, i + MAX_CHUNK));
		}

		let index = 0;

		function sendNextChunk()
		{
			fetch('index.php?route=tool/chunk/save&user_token=' + oc.token, {
				method: 'POST',
				body: JSON.stringify({
					index,
					total: chunks.length,
					data: chunks[index]
				}),
				headers: { 'Content-Type': 'application/json' }
			})
			.then(r => r.json())
			.then(j => {
				if (j.status === 'part') {
					index++;
					sendNextChunk();
				} else if (j.status === 'done') {
					if (typeof callback === 'function')
						callback();
				}
			})
			.catch(err => console.error('Chunk send error:', err));
		}

		sendNextChunk();
	};

	// Form submit override
	$(document).ready(function() {
		$(document).on('submit', 'form[data-oc-toggle="ajax"]', function(e) {
			var $form = $(this);

			// Tek seferlik chunk kontrolü
			if ($form.data('chunked-processed'))
				return;
			$form.data('chunked-processed', true);

			// Büyük form mu kontrol et
			const formData = new FormData($form[0]);
			let json = {};
			formData.forEach((v,k)=>{ json[k]=v; });
			const raw = JSON.stringify(json);

			if (raw.length > MAX_CHUNK) {
				e.preventDefault(); // normal submit engellendi

				sendChunkedForm($form, function() {
					// session’a chunks kaydedildi → OpenCart AJAX submit normal çalışacak
					// Önemli: tekrar submit yok, çifte tetiklenmez
				});
			}
			// Küçük formlar için hiçbir değişiklik yok
		});
	});

})();

*/
