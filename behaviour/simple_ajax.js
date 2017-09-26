// JavaScript Document

function getXMLHttp()
{
  var xmlHttp

  try
  {
    //Firefox, Opera 8.0+, Safari
    xmlHttp = new XMLHttpRequest();
  }
  catch(e)
  {
    //Internet Explorer
    try
    {
      xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch(e)
    {
      try
      {
        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch(e)
      {
        alert("Your browser does not support AJAX!")
        return false;
      }
    }
  }
  return xmlHttp;
}

function GetContent(guid, rnd)
{
	//alert("GC("+guid+")");
	var xmlHttp = getXMLHttp();
	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			GetContentResponse(xmlHttp.responseText, "panel-content-"+guid, guid);
		}
	}
	xmlHttp.open("GET", "/behaviour/ajax/getPanelContent.php?guid="+guid+"&rnd="+rnd, true);
	xmlHttp.send(null);
}

function GetContentResponse(response, div, guid)
{
	//alert("hr("+div+") guid("+guid+")");
	document.getElementById(div).innerHTML = response;
	
	/*
	tinyMCE.init({
	mode : "textareas",			 
	elements : "",
	height : "250px",
	editor_selector: "mcePanelEditor",
	theme : "advanced",
	plugins : "safari,style,inlinepopups,new_link,new_image,media",
	relative_urls : false,
	remove_script_host : false,
	theme_advanced_buttons1 : "bold,italic,separator,justifyleft,justifycenter,justifyright",
	theme_advanced_buttons2 : "new_link,unlink,new_image,media",
	theme_advanced_buttons3 : "styleselect,formatselect,code",
	theme_advanced_styles : "",
	theme_advanced_blockformats : "p,h4",
	content_css : "/treeline/style/wysiwyg.css",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_path_location : "",
	extended_valid_elements : "a[id|name|href|title|target],img[id|class|src|border|alt=|title|width|height|align|style],hr[class|width],span[class|align|style]",
	paste_auto_cleanup_on_paste : true,
	spellchecker_languages : "+English=en"
	});
	*/
	
	CKEDITOR.replace('treeline_panelcontent-'+guid, { toolbar : 'contentPanel', height: '200px' });
	
}