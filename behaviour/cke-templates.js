/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// Register a template definition set named "default".
CKEDITOR.addTemplates( 'default',
{
	// The name of the subfolder that contains the preview images of the templates.
	imagesPath : CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + 'templates/images/' ),
 
	// Template definitions.
	templates :
		[
			{
				title: 'My Template 1',
				image: 'template1.gif',
				description: 'Description of My Template 1.',
				html:
					'<h2>Template 1</h2>' +
					'<p><img src="/logo.png" style="float:left" />Type your text here.</p>'
			},
			{
				title: 'Image block left',
                                image: 'imageblockleft.gif',
				html:
                                        '<div class="img-block">' +
					'<img src="" />' +
					'<p class="credit">Credit</p>' +
                                        '<p class="caption">Caption</p>' +
                                        '</div>'
			}
		]
});