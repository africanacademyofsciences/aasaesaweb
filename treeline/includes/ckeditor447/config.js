/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.stylesSet.add( 'my_styles',
[
    // Block-level styles
	// { name : 'None', element : 'p', styles : { 'class' : 'none' } },
    { name : 'None', element : 'p', attributes : { 'class' : 'none' } },
    { name : 'Highlight', element : 'p', attributes : { 'class' : 'highlight' } },
    { name : 'Larger font', element : 'p', attributes : { 'class' : 'larger' } },
	//{ name : 'Box', element : 'p', attributes : { 'class' : 'box' } },
    { name : 'Quote', element : 'p', attributes : { 'class' : 'quote' } }
    // Inline styles
	//{ name : 'Box', element : 'span', attributes : { 'class' : 'box' } }
]);

CKEDITOR.stylesSet.add( 'user_styles',
[
    // Block-level styles
	// { name : 'None', element : 'p', styles : { 'class' : 'none' } },
    { name : 'None', element : 'p', attributes : { 'class' : 'none' } },
    { name : 'Larger font', element : 'p', attributes : { 'class' : 'larger' } },
    { name : 'Quote', element : 'p', attributes : { 'class' : 'quote' } }
]);


CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.contentsCss = ['/style/global.css', '/treeline/includes/ckeditor/contents.css', '/style/font-awesome.min.css', '/style/bootstrap.css'];	
	config.format_tags = 'p;h3;h4';
	config.extraPlugins = 'linkpicker,MediaEmbed,widget,lineutils,glyphicons,fontawesome';
        config.allowedContent = true; 
        
        config.stylesSet = 'my_styles';
	config.height = 500;  
	config.protectedSource.push(/<i[^>]*><\/i>/g);	
        config.templates_files = [ '/behaviour/cke-templates.js' ];	
        
	config.removePlugins='autogrow';
	
	config.toolbar = 'pcustom';
	config.toolbar_pcustom =
	[
		{ name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
			'HiddenField' ] },
		'/',
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
		'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	];

	config.toolbar = 'contentStandard';
	config.toolbar_contentStandard =
	[
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','HorizontalRule','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format' ] },
		{ name: 'links', items : [ 'Linkpicker', 'Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Flash','FontAwesome','Glyphicons','Table','Iframe','Templates' ] },
		{ name: 'document', items : [ 'Source' ] }
	];

	config.toolbar = 'contentMinimal';
	config.toolbar_contentMinimal =
	[
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format' ] },
		{ name: 'links', items : [ 'Linkpicker','Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Flash','Table','Iframe' ] },
		{ name: 'document', items : [ 'Source' ] }
	];

	config.toolbar = 'contentPanel';
	config.toolbar_contentPanel =
	[
		{ name: 'basicstyles', items : [ 'Bold','Italic' ] },
		{ name: 'paragraph', items : [ 'BulletedList','HorizontalRule','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		'/',
		{ name: 'document', items : [ 'Styles','Format' ] },
		'/',
		{ name: 'links', items : [ 'Linkpicker','Unlink','Anchor','Source' ] }
	];

	config.toolbar = 'contentImageLink';
	config.toolbar_contentImageLink =
	[
		{ name: 'links', items : [ 'Linkpicker','Unlink','Anchor','Source' ] }
	];
	
	config.toolbar = 'contentImageOnly';
	config.toolbar_contentImageOnly =
	[
		{ name: 'links', items : [ 'Linkpicker','Unlink','Source' ] }
	];

	config.toolbar = 'contentUser';
	config.toolbar_contentUser =
	[
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
		'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		'/',
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','SpecialChar' ] }
	];


	/*
	// FULL MENU 
	config.toolbar = 'Pcustom';
	config.toolbar_Pcustom =
	[
		{ name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
			'HiddenField' ] },
		'/',
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
		'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	];
	*/


};

