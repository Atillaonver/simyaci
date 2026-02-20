(function($){
	$(document).ready(function(){
        if ($.fn.colorpicker) {
            $('.input-colorpicker').each(function() {
                var $this = $(this),
                    $input = $this.find('input.form-control[type=text]');

                $input.colorpicker();

                $this.find('.input-group-text > i').on('click', function(e) {
                    $input.colorpicker('show');
                });
                $input.on('colorpickerChange', function(e) {
                    $this.find('.input-group-text > i').css('background-color', e.color.toString());
                });
            });
        }

        if ($.fn.ckeditor) {
		    var initCKeditor = function ($self) {
                $self.ckeditor({
                    enterMode: CKEDITOR.ENTER_BR,
                    autoParagraph: false,
                    extraAllowedContent: 'div{*}(*); table{*}(*); td[height,width,bgcolor]{*}(*); span{*}(*); p{*}(*);',
                    //extraPlugins: 'bootstrapCollapse,widget,lineutils'
                });
            }

            $('textarea[data-toggle=\'ckeditor\']').each(function(){
                var $self = $(this);

                if ($self.is(':hidden') && $self.parents('.collapse.show').length) {
                    $self.parents('.collapse.show').on('shown.bs.collapse', function() {
                        initCKeditor($self);
                    });
                } else {
                    initCKeditor($self);
                }
            });
        }
	});
})(jQuery);