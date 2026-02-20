var fancyboxIsOpen= false;
var counter=0;
var nStart=0;
var defaults = {
			buttons : [
				'slideShow',
				'fullScreen',
				'thumbs',
				'share',
				//'download',
				'zoom',
				'close'
			],
			lang : lan,
			jumpTo: nStart,
			i18n :
			{
				'en' :
				{
					CLOSE       : 'Close',
					NEXT        : 'Next',
					PREV        : 'Previous',
					ERROR       : 'The requested content cannot be loaded. <br/> Please try again later.',
					PLAY_START  : 'Start slideshow',
					PLAY_STOP   : 'Pause slideshow',
					FULL_SCREEN : 'Full screen',
					THUMBS      : 'Thumbnails',
					DOWNLOAD    : 'Download',
					SHARE       : 'Share',
					ZOOM        : 'Zoom'
				},
				'tr' :
				{
					CLOSE       : 'Kapat',
					NEXT        : 'Sonraki',
					PREV        : 'Önceki',
					ERROR       : 'İstediğiniz içerik çağrılamadı <br/> Lütfen daha sonra deneyiniz',
					PLAY_START  : 'Gösteriyi Başlat',
					PLAY_STOP   : 'Gösteriyi Durdur',
					FULL_SCREEN : 'Tam Ekran',
					THUMBS      : 'Tırnak Resim',
					DOWNLOAD    : 'İndir',
					SHARE       : 'Paylaş',
					ZOOM        : 'Yakınlaştır'
				}
			},
			afterClose: function() {
	            fancyboxIsOpen=false;
	        },
			afterShow : function( instance, slide ) {
				
			}
		};
		


$( document ).ready(function()
	{
		$( '[data-fancybox="images"]' ).fancybox(defaults);
		$(document).ajaxStart(function()
			{
				$("#wait").css("display", "block");
			});
		$(document).ajaxComplete(function()
			{
				$("#wait").css("display", "none");
			});
	});



