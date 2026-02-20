$(document).ready(function() {


		$("#moreImage").on("click",  function(e)
			{

				t=$(this).attr('type');
				l=$(this).attr('last');
				g=$(this).attr('galeri');
				URL = $(this).attr('href');
				
				$.ajax(
					{
						url: URL+t+'&l='+l+'&g='+g,
						type: 'post',
						dataType: 'json',
						data: 23,
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function()
						{

						},
						complete: function()
						{

						},
						success: function(json)
						{
							if (json['error'])
							{
								$('#err-img p').html(json['error']);
								$('#err-img').toggleClass(' w3-show');
							}

							if (json['view'])
							{
								var n = $( ".bytao-gallery > div" ).length;
								for(col = 1; col <(n+1)  ; col++)
								{
									$('#colG'+col).append(json['view'][col]);
								}
								$('#moreImage').attr('last',json['last'])
							}

						},
						error: function(xhr, ajaxOptions, thrownError)
						{
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
			});

		$("#moreBlog").on("click", function(e)
			{
				l=$(this).attr('last');
				t=$(this).attr('type');
				URL = $(this).attr('href');

				$.ajax(
					{
						url: URL+l+'&t='+t,
						type: 'post',
						dataType: 'json',
						data: 23,
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function()
						{
						},
						complete: function()
						{
						},
						success: function(json)
						{
							if (json['error'])
							{
								$('#err-blog p').html(json['error']);
								if (!$('#err-blog').hasClass('w3-show'))
								{
									$('#err-blog').toggleClass(' w3-show');
								}

							}

							if (json['view'])
							{
								
								var n = $( "#oykuler > div" ).length;
								for(col = 1; col <(n+1)  ; col++)
								{

									$('#oykuler #colG'+col).append(json['view'][col]);
								}
								$('#moreBlog').attr('last',json['last'])
							}

						},
						error: function(xhr, ajaxOptions, thrownError)
						{
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
			});

		$("#contactForm").on("submit", function(e)
			{
				e.preventDefault();
				var form = $(e.target);
				$.post( form.attr("action"), form.serialize(), function(res)
					{
						if(res.success)
						{
							alert('Mesajýnýz tarafýmýza ulaþtý \nEn kýsa sürede geri dönüþ yapýlacaktýr.\nÝlginiz için teþekkür ederiz.');
							$("#contactForm").trigger('reset');
						}

					});
			});

	});