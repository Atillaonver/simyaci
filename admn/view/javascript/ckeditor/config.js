/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	config.language = $(this).attr('data-lang');
	config.filebrowserWindowWidth = '800';
	config.filebrowserWindowHeight = '500';
	config.resize_enabled = true;
	config.resize_dir = 'vertical';
	config.htmlEncodeOutput = true;
	config.entities = false;
	config.extraPlugins = 'opencart,embed,codemirror,forms'; //
	config.codemirror_theme = 'monokai';
	config.toolbar = 'Custom';
	config.allowedContent = true; // Tüm içerik türlerine izin verir
    config.extraAllowedContent = 'form[*]{*}(*);input[*]{*}(*);select[*]{*}(*);option[*]{*}(*);textarea[*]{*}(*);button[*]{*}(*);fieldset[*]{*}(*);legend[*]{*}(*);';
	

	config.toolbar_Custom = [
		['Source'],
		['Maximize'],
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['SpecialChar'],
		'/',
		['Undo','Redo'],
		['Font','FontSize'],
		['TextColor','BGColor'],
		['Link','Unlink','Anchor'],
		['Image','OpenCart','Table','HorizontalRule']
	];
};
