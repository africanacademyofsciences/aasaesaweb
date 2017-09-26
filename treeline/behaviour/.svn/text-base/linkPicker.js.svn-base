	
	function insertLink() {
		var url = String(window.location);
		var source;
		if (url.indexOf('source=field') > -1) {
			//alert ("updating field");
			source = 'field';
		}
		else if (url.indexOf('source=wysiwyg') > -1) {
			//alert ("updating wysiwyg");
			source = 'wysiwyg';			
		}
		if (window.opener) {
			var href = document.forms[0].href.value;
			var target = document.forms[0].target.options[document.forms[0].target.selectedIndex].value;
			// not presently using the next three
			var title = '';// document.forms[0].linktitle.value;
			var style_class = '';//document.forms[0].styleSelect.value;
			var dummy;
			var type = document.getElementById('type').value;
			if (type == 'mailto') {
				href = 'mailto:' + href;
			}
			// new approach to this: checking whether we're updating a field, or the wysiwyg
			if (source == 'field') {
				window.opener.updateHref(href,target);
			}		
			else if (source == 'wysiwyg') {
				o = window.opener.location.href;
				if ((o.indexOf('newsletters') > -1) && (type == 'page' || type == 'file')) {
					// if we're adding a page or file link to the newsletter, make it an absolute link:
					href = 'http://www.vanilla.com' + href;
				}
				alert(href);
				window.opener.tinyMCE.insertLink(href, target, title, dummy, style_class);
			}			
			else if (window.opener.updateHref && 
					(window.opener.document.getElementById('paneltype').value == 2
					|| window.opener.document.getElementById('paneltype').value == 3
					|| window.opener.document.getElementById('paneltype').value == 7
					)) {
				// Basically -- this clause is only activated if we've opened linkpicker.php from panel.php
				// and if we're choosing a link for an "image only" panel -- rather than a "WYSIWYG" panel.
				// This is because the former just needs an <input> field populating, whereas the latter
				// needs a WYSIWYG editor updating. If we add more panel types, this will need changing.
				// This is pretty inelegant. I think the Panel editor is too complex. Once you've indicated the type
				// of panel you're editing, the page should refresh -- rather than one page trying to do everything.
				// Note -- this has been further complicated by adding the tickermanager, which ALSO lacks a WYSIWYG
				// so I've had to add a dummy "paneltype" field. This will all be tidied up when I get chance.
				window.opener.updateHref(href,target);
			}		
			else if (window.opener.tinyMCE) {
				o = window.opener.location.href;
				if ((o.indexOf('newsletters') > -1) && (type == 'page' || type == 'file')) {
					// if we're adding a page or file link to the newsletter, make it an absolute link:
					href = 'http://www.vanilla.com' + href;
				}
				window.opener.tinyMCE.insertLink(href, target, title, dummy, style_class);
			}
//			tinyMCE.closeDialog(); // Do we need to reinstate this? Check the right-click feature of TinyMCE.
			window.close();
		}
	}	
	
	function cancelAction() {
//		tinyMCE.closeDialog();
		window.close(); // hmmm -- this might not work if we've right-cicked a link. I need to get my head around how tinyMCE works here.
	}
	function linkLibrary() {
		window.open('/cms/linklibrary.php','linklib','width=380,height=460');
	}	
	function fileLibrary() {
		window.open('/cms/filelibrary.php','filelib','width=456,height=440,scrolling=yes');
	}		
	function updateHref(h) {
		document.getElementById('href').value = h;
	}
	function myPick() {
		var type = document.getElementById('type').value;
		if (type == 'page') {
			linkLibrary();
		}
		else if (type == 'file') {
			fileLibrary();
		}
		else if (type == 'external') {
			alert ('Please enter the full URL you wish to link to');
		}
		else if (type == 'mailto') {
			alert ('Please enter the email address you wish to link to');
		}
		else {
			alert ('Please select a link type');
		}
	}
	function update(t) {
		if (t == 'mailto') {
			document.getElementById('href').disabled = false;
			document.getElementById('target').disabled = true;
		}
		else if (t == 'external') {
			document.getElementById('href').disabled = false;
			document.getElementById('target').disabled = false;
		}
		else if (t == 'file') {
			document.getElementById('target').selectedIndex = 1; // Force target="_blank" for files
		}		
		else {
			document.getElementById('href').disabled = true;
			document.getElementById('target').disabled = false;
		}
	}
