// JavaScript Document

CKEDITOR.plugins.add( 'linkpicker',
{
	requires: ['iframedialog'],
	init: function( editor )
	{
		//Plugin logic goes here.
		var pluginName = 'plinkpicker';
		var mypath = this.path;
		
		CKEDITOR.dialog.addIframe( pluginName, 'Treeline Library', this.path+'linkpicker.php', 700, 450, false);
		
		editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
		
		editor.ui.addButton('Linkpicker',
		{
			label: 'Treeline library',
			command: pluginName,
			icon: this.path + 'images/linkpicker.png'
		});
	}

} );

