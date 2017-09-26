<?php

session_start();
if (!$_SESSION['treeline_user_id']) {
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ajax/notloggedin.inc.php");
	exit;
}

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

$tl_box_width = 415;	// Width to draw the Treeline Box 
$tags = new Tags();

$guid=read($_REQUEST, "guid", "");

// If we are not on a 3 col layout disable the panel manager.
$valid = $db->get_var("SELECT IF(style=3,1,0) FROM pages WHERE guid='$guid'");

$feedback="error";

	// arrangement stuff?
	$datum = array();
	$data = array();


if ($_GET['list']) $_SESSION['panellist']=$_GET['list'];
$_SESSION['panellist'] = str_replace("undefined", "", $_SESSION['panellist']); 

$action = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'action', '');
$paneltype = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'type', '');
$title = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'title', '');

$style = read($_POST, 'style', 8);


// If we have just arrived we need to clear
// the panel list stored in the session
if (!$action) {
	$action="intro";
	unset($_SESSION['panellist']);
}
//print "got action($action) panellist(".$_SESSION['panellist'].")<br>\n";
//print "got action ($action)<br>\n";

if (!$guid) $message[]="No master page ID passed !!!";
if (!$action) $message[]="No action, nothing to do";

if ($_SERVER['REQUEST_METHOD']=="POST") {

	$action=strtolower(read($_POST, "action", ''));
	if ($action=="arrange") {
	
		// 1 - Get the menu listing and re-order the panellist
		$content = (isset($_POST['mm_content']))?$_POST['mm_content']:'';
		if ($content) {
			// Collect the most recently saved version of the menu for this microsite?
			//print "content($content)<br>\n";
			if (preg_match_all("/mm\[\d*\]\[id\]=mm_(.*?)&/", $content."&", $reg)) {
				//print_r($reg);
				unset($_SESSION['panellist']);
				foreach ($reg[1] as $panel_guid) {
					$_SESSION['panellist'].=($_SESSION['panellist']?",":"").$panel_guid;
				}
				$message[]="The panel order has been saved. You will still need to use the Save &amp; redraw option to update your page";
				$feedback="success";
			}
		}
		// Otherwise the order was not changed and we dont have to do anything.
				
		// 2 - Check if we have an intelligent panel to delete/save.
		$tags->updateIntelligentLinkPanelDetails($guid, $_POST['accuracy'], $_POST['maxlinks'], $_POST['delete-related']?0:1);
		
	}
	
	else if ($action=="create") {

		// If we are posting the form then make sure we have all the info we need.
		if (isset($_POST['title'])) {

			// All panels must have a title
			switch ($paneltype) {
			
				case "custom" :
				case "library" :

					if (!$_POST['title']) $message[]="You must enter a title for your new panel";

					$panel_template_id=$paneltype=="library"?6:23;

					$panel = new Page;
					// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
					$panel->setParent($siteID);
					$panel->setTitle($title);
					$panel->setStyle($style);
					// Generate a unique name for the panel. Is this necessary?
					$name = $panel->generateName();
					if (!$name) $message[] = 'A panel with that name already exists';
					else {		
						$panel->setHidden('0');
						$panel->setSortOrder();					
						$panel->setTemplate($panel_template_id);
						$panel->setMetaDescription('A new panel');
						if ($panel->create(2)) {
							$new_panel_guid=$panel->getGUID();
							// Custom panels can go straight to arrange
							// As these panels are part of the page
							//if ($paneltype=="custom") {
								$_SESSION['panellist']=$new_panel_guid.(($_SESSION['panellist']!="empty" && $_SESSION['panellist'])?",".$_SESSION['panellist']:"");
								$action="arrange";
							//}
							// Otherwise we have to jump into panel edit mode.
							//else $action="edit";
						}
						else $message[]="Failed to create panel";
					}
					break;
					
				case "rss" : 
					$message[]= "Check RSS link & save RSS panel details";
					$action = "arrange";
					break;

				case "intelligent" : 
					// Check if we have an intelligent panel to delete/save.
					$tags->updateIntelligentLinkPanelDetails($guid, $_POST['accuracy'], $_POST['maxlinks'], $_POST['delete-related']?0:1, $_POST['title']);
					$message[]="Your new intelligent panel has been saved";
					$feedback="success";
					$action = "arrange";
					break;
			}
		}
	}
	
	else if ($action == "save") {
	
		$content = new HTMLPlaceholder();
		$content->setMode("inline-edit");
		$content->load($_POST['panelguid'], 'panelcontent');
		$content->save();
		
		$panel = new Page();
		$panel->loadByGUID($_POST['panelguid']);
		$panel->save(true);
		
		$message[]="Changes to this panel have been saved.";
		$message[]="These changes will have to be published before they will appear on the live websites.";
		$feedback="success";
		
		$action = "arrange";
		
	}
	
	else if ($action == "add") {
	
		if ($_POST['new_panel_guid']>'') {

			$new_panel_guid=$_POST['new_panel_guid'];
			$_SESSION['panellist']=$new_panel_guid.",".$_SESSION['panellist'];
			$action="arrange";
			
		}
		else $message[]="No panel was selected";
	}
	
	
}
// _GET actions
else {

	if ($action=="publish") {
		$i=0;
		$pubpos = $_GET['pos']+0;
		$action = "arrange";
		$apanels = explode(",", tidyList($_SESSION['panellist']));
		foreach ($apanels as $thispanel) {
			//print "i($i) del($delpos)<br>\n";
			if ($i==$pubpos) {
				if ($db->get_var("SELECT publishable FROM get_page_properties WHERE guid='$thispanel'")==1) { 
					$panel = new Page();
					$panel->loadByGUID($thispanel);
					$panel->publish();
					$message[]="Panel <strong>".$panel->getTitle()."</strong> has been published";
					$feedback="success";
				}
				else $message[]="That panel is not publishable";
			}
			$i++;
		}	
	}
	
	// Remove a set index from the panellist stored in the session
	if ($action=="delete") {
		$i=0;
		$delpos = $_GET['pos']+0;
		$action = "arrange";
		$apanels = explode(",", tidyList($_SESSION['panellist']));
		foreach ($apanels as $thispanel) {
			//print "i($i) del($delpos)<br>\n";
			if ($i==$delpos) {
				// Check if this is a custom panel and delete page/content data if it is
				$query = "SELECT template_title FROM pages_templates pt 
					LEFT JOIN pages p ON pt.template_id = p.template
					WHERE p.guid='$thispanel'";
				//$message[]="$query";
				$panel_type = $db->get_var($query);
				if ($panel_type =="Custom panel" && $thispanel) {
					
					if ($_GET['confirm']=="true") {
						//$message[]="DELETE FROM PAGES AND CONTENT TOO...";
						$panel = new Page();
						$panel->loadByGUID($thispanel);
						$panel->delete();
						
						$message[]="Your custom panel has been deleted";
						$message[]="<strong>Please note</strong>: If you close this window without using the Save &amp; Redraw option your page will continue to refer to this panel even though it no longer exists in the database.";
					}
					else {
						$message[]="Deleting this panel will also remove it from the database completely. You will no longer be able to access this content";
						$message[]='Do you wish to <a href="/treeline/panelManager/?guid='.$guid.'&amp;action=delete&amp;pos='.$delpos.'&amp;confirm=true">continue with delete</a>';
						$newlist.=($newlist?",":"").$thispanel;
					}
				}
				if (!count($message)) {
					$message[]="Your ".strtolower($panel_type)." has been removed from this page";
				}
				$feedback="success";
			}
			else if ($thispanel) {
				$newlist.=($newlist?",":"").$thispanel;
			}
			$i++;
		}
		//print "created new list($newlist)<br>\n";
		if (!$newlist) $newlist="empty";
		$_SESSION['panellist']=$newlist;
	}


}
$css = array('forms','panelmanager','tables'); // all CSS needed by this page
$extraCSS = '';

if ($action == "arrange") $css[]="menumanager";
else if ($action == "edit") {

	$extraCSS.='
	div#secondarycontent {
		margin: 30px auto 0;
		width: 224px;
	}
	';
}
else if ($action == "preview") {

	if (!$palateNumber) $palateNumber=$site->config['palate'];
	if (!$palateNumber || $palateNumber>99) $palateNumber=1;
	if (!$fontNumber || $fontNumber>2) $fontNumber=1;

	$css[]="../../style/panel";
	$css[]='../../style/scheme/font0'.$fontNumber;
	$css[]='../../style/scheme/palate01';

	if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".$site->id.".css") && !$_SESSION['palate']) $css[]="../../style/microsite/scheme".$site->id;
	else if ($palateNumber>1) $css[]="../../style/scheme/palate".(($palateNumber<10?"0":"").$palateNumber);		

	$extraCSS.='
	body {
		background: #FFF;
	}
	div#secondarycontent {
		margin: 30px auto 0;
		width: 224px;
		font-size: 110%;
	}
	';
}

// Set up CSS for the arranger box thingy
if ($action=="arrange") $extraCSS .= '
	
	.page-list {
		list-style: none;
		margin: 0;
		padding: 0;
		display: block;
	}
	.clear-element {
		clear: both;
	}
	
	.page-item1 > div,
	.page-item2 > div,
	.page-item3 > div,
	.page-item4 > div {
		margin: 0;
	}
	.page-item1 > div p {
		padding-left:10px;
	}

	.sort-handle {
		cursor:move;
	}

	.helper {
		border:2px dashed #777777;
		height: 21px;
		width: 363px !important;
	}

';

$js = array ('interface-1.2', 'inestedsortable');
$extraJS = ''; // extra on page JavaScript


if ($action=="preview") {

	$js[]="../../behaviour/jquery";
	$js[]="../..//behaviour/jquery.corner";
	$extraJS= '
$(window).load(function()
{
	// -------------------- Keep large images within the design --------------------
	
	$("#secondarycontent div.rounded").each(function()
	{	
		$(this).corner("7px");
	});

});
	';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=($_SESSION['treeline_user_encoding']?$_SESSION['treeline_user_encoding']:"iso-8859-1")?>" />
<meta name="robots" content="noindex,nofollow" />
<title>&nbsp;</title>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonCSS.inc.php"); ?>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonJS.inc.php"); ?>
<script type="text/javascript">
</script>
</head>

<body onload="javascript:window.focus();">

<h1 class="notes-header" id="notes-header">
	<span>Panel manager</span>
	<a href="javascript:openhelp('<?=$help->helpLinkByID($helpid)?>')" class="tl-help-link">Get help with this</a>
</h1>

<div id="panel-manager">
    <ul id="panel-menu">
    <li class="first"><a class="<?=($action==""?" selected":"")?>" href="javascript:loadPage('<?=$guid?>', 'intro');">Introduction</a></li>
    <li><a class="<?=($action=="create"?" selected":"")?>" href="javascript:loadPage('<?=$guid?>', 'create');">Create a new panel</a></li>
    <li><a class="<?=($action=="add"?"selected":"")?>" href="javascript:loadPage('<?=$guid?>', 'add');">Add a panel to this page</a></li>
    <li><a class="<?=($action=="arrange"?"selected":"")?>" href="javascript:loadPage('<?=$guid?>', 'arrange');">Arrange panels</a></li>
 
    <li class="first"><a href="javascript:openhelp('<?=$help->helpLinkByID($helpid)?>')" class="<?=($action=="help"?"selected":"")?>">Panels help</a></li>
    <li class="savenclose"><a class="savenclose" href="javascript:saveandclose('<?=$_SESSION['panellist']?>');">Save Changes</a></li>
    <!-- <li class="first"><a href="javascript:saveandclose('exit');">Close without saving</a></li> -->
    </ul>

	<div id="contentholder">
    <?php
    if (count($message)) {
        foreach ($message as $tmp) {
            $tmp_msg .= '<li>'.$tmp.'</li>';	
        }
		echo '<ul class="message '.($feedback=="success"?"":"error").'">'.$tmp_msg.'</ul>';
    }
	
	
	if ($action=="create") {
	
		$page_html.='
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
		';

		if (!$paneltype) {
			$page_html.='
			<p class="instructions">Please select a panel type from the list below:</p>
			<label for="type">Panel Type:</label>
			<select name="type" id="type">
				<option value="custom">Custom Panel</option>
				<option value="library">Library Panel</option>
				<!-- <option value="rss">RSS Feed Panel</option> -->
				'.($db->get_var("SELECT guid FROM tags_intelligent_link_panels WHERE guid='$guid'")?'':'<option value="intelligent">Intelligent Panel</option>').'
			</select>
			<input type="submit" class="submit" value="Submit" />
			';
			echo treelineBox($page_html, "Select panel type", "blue", $tl_box_width);
		}
		else if (!$title || ($title && count($message))) { 

			$page_html .= '<input type="hidden" name="type" value="'.$paneltype.'" />';
			
			// Create a library panel or a custom panel
			// NOTE - Library panels cannot be created here.
			if ($paneltype=="library" || $paneltype=="custom") {
				if ($paneltype=="library") $instructions = "This panel will be available for use in other pages";
				if ($paneltype=="custom") {	
					$instructions = "This panel will appear on this page only. Please add panel content via the page editor.";
					//$button_text = "Save &amp; Organise";
				}
				include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addPanels.php");
			}
			// Show form for creating an intelligent panel
			else if ($paneltype=="intelligent") {
				$tags->setMode("edit");
				$page_html .= '<p class="instructions">Intelligent links panels always appear at the bottom of the panel list</p>';
				$page_html .= $tags->drawRelatedContentLinks($guid);
				$page_html .= '<input type="submit" class="submit" value="Submit" />';
			}
			echo treelineBox($page_html, "Create ".$paneltype." panel", "blue", $tl_box_width);
		}
		else {
			print "We are not dealing with library panels so we never have to do this :o))";
		}
		$page_html.='
			</fieldset>
			</form>
		';
	}
	
	// Inline panel editor 
	else if ($action == "edit") {

		$i=0;
		$editpos = $_GET['pos']+0;
		$action = "arrange";
		$apanels = explode(",", tidyList($_SESSION['panellist']));
		foreach ($apanels as $thispanel) {
			if ($i==$editpos) {
				$page = new Page();
				if ($page->loadByGUID($thispanel)) {
					$page->setMode('inline-edit'); 
					$pageGUID = $thispanel;
					?><div id="secondarycontent"><?php
					include $_SERVER['DOCUMENT_ROOT']."/".$page->getTemplate();
					?></div><?php
				}
			}
			$i++;
		}
	
	}

	// Inline panel editor 
	else if ($action == "preview") {

		$i=0;
		$editpos = $_GET['pos']+0;
		$action = "arrange";
		$apanels = explode(",", tidyList($_SESSION['panellist']));
		foreach ($apanels as $thispanel) {
			if ($i==$editpos) {
				$page = new Page();
				if ($page->loadByGUID($thispanel)) {
					$page->setMode('inline-preview'); 
					?><div id="secondarycontent"><?php
					include $_SERVER['DOCUMENT_ROOT']."/".$page->getTemplate();
					?></div><?php
				}
			}
			$i++;
		}
	
	}

	
	// Add panels to the page
	// By default all panels are added to the top of the list and can be reordered later	
	else if ($action=="add") {
		
		if (!$new_panel_guid) {
			$page_html = '
			<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">Select a panel to add to the page:</p>
			<label for="f_new_panel">Panel:</label>
			<select name="new_panel_guid" id="f_new_panel">
			'.PanelsPlaceholder::drawSelectablePanels().'
			</select>
			<input type="submit" class="submit" value="Submit" />
			</fieldset>
			</form>
			';
			echo treelineBox($page_html, "Select panel to add", "blue", $tl_box_width);
		}
	}
	

	// Organise panels with a nice pretty jquery thingy	
	else if ($action == "arrange") {
	
		$query = $html = "";
		$i = $j = 0;
		$apanels = $bpanels = array();
		
		//print "show (".tidyList($_SESSION['panellist']).")<br>\n";
		if (tidyList($_SESSION['panellist'])!="empty") {
			$panel_list = explode(",", $_SESSION['panellist']);
			foreach ($panel_list as $thispanel) {
				if ($thispanel) {
					$apanels[$i]['guid']=$thispanel;
					$query.="'$thispanel',";
					$i++;
				}
			}
		}
		
		//print_r($apanels);
		if (count($apanels)) {
			$query = "SELECT guid, name, title, publishable FROM get_page_properties WHERE guid IN (".substr($query,0,-1).")";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				for($i=0; $i<count($apanels); $i++) {
					foreach($results as $result) {
						//print "i($i) test if ".$result->guid." == ".$apanels[$i]['guid']."<br>\n";
						if ($result->guid == $apanels[$i]['guid']) {
							//print "Match setting apanels[$i]['index']=$j<br>\n";
							$bpanels[$j]['guid']=$result->guid;
							$bpanels[$j]['title']=$result->title;
							$bpanels[$j]['publishable']=$result->publishable;
						}
					}
					$j++;
				}
			}
			//print_r($bpanels);
			
			// Go through the sorted list and produce html
			//print "list (".count($apanels).") panels";
			for($i=0; $i<count($apanels); $i++) {

				$no_link='<span class="no-action"></span>';
				$previewlink = $editlink = $publishlink = $deletelink = $no_link;
				
				if ($bpanels[$i]['title']) $previewlink='<a '.$help->drawInfoPopup("Preview this panel").' class="preview" href="/treeline/panelManager/?guid='.$guid.'&action=preview&pos='.$i.'"></a>';
				if ($bpanels[$i]['title']) $editlink = '<a '.$help->drawInfoPopup("Edit this panel").' class="edit" href="/treeline/panelManager/?guid='.$guid.'&action=edit&pos='.$i.'"></a>';
				if ($bpanels[$i]['title']) {
					//print "$query<br>\n";
					if ($_SESSION['treeline_user_group']!="Author" && $bpanels[$i]['publishable']) {
						$publishlink = '<a '.$help->drawInfoPopup("Publish this panel").' class="publish" href="/treeline/panelManager/?guid='.$guid.'&action=publish&pos='.$i.'"></a>';
					}
				}
				$deletelink = '<a class="delete" href="/treeline/panelManager/?guid='.$guid.'&action=delete&pos='.$i.'"></a>';

				$html.='<li id="mm_'.$bpanels[$i]['guid'].'" class="page-item1 no-nest sort-handle">
<table border="0" cellpadding="0" cellspacing="0" class="tl_list"><tr>
	<td class="pm-right action">
		'.$previewlink.$editlink.$publishlink.$deletelink.'
	</td>
	<td>
		<span '.$help->drawInfoPopup("Click and drag this item to move it").' class="title">'.($bpanels[$i]['title']?$bpanels[$i]['title']:"FAILED TO LOAD PANEL").'</span>
	</td>
	</tr>
</table>
</li>';
				//$html.='<li id="mm_'.$bpanels[$i]['guid'].'" class="page-item1 sort-handle">ele-'.$i.'</li>'."\n";
			}
			if ($html) $html='
	<input type="hidden" id="mm_content" name="mm_content" value="" />
	<p class="instructions">You must press submit on this page to save any changes</p>
	<div class="menu-wrap-wrap">
	<div class="menu-wrap">
		<ul id="mm" class="page-list">
		'.$html.'
		</ul>
	</div>
	</div>
	<fieldset class="buttons">
		<input type="submit" class="submit" value="Submit" />
	</fieldset>
	';

		}
		else $html = '<p class="instructions">There are no panels on this page</p>';
		
		if ($tags->showRelatedContent($guid)) {
			$tags->setMode("edit");
			$html .= $tags->drawRelatedContentLinks($guid);
		}
		
		$html = '
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			'.$html.'
		</fieldset>
		</form>
		';
		echo treelineBox($html, "Arrange panels", "blue", $tl_box_width);
	}
	
	// Show some instructions for using the panel manager.
	else {
	
		echo treelineBox('
        <p>Use the panel manager to create new library panels or add custom panels to this page only.</p>
        <p>Add panels to this page only or the panel library and the use the arrange panels function to put them in the order required</p>
        <p>Once you have finished updating panels you must use the "Save Changes" button on the left or your changes may be lost. When you save changes this window will close and the main window will refresh. Please allow time for the main window to reload.</p>
		', 'Manage panels on this page', "blue", $tl_box_width);
	
	}
    ?>
    
    </div>
    
</div>

<?php if ($action =="arrange") { ?>
<script type="text/javascript">
jQuery( function($) {

$('#mm').NestedSortable(
	{
		accept: 'page-item1',
		noNestingClass: 'no-nest',
		nestingPxSpace: 20,
		opacity: 0.8,
		helperclass: 'helper',
		onChange: function(serialized) {
			//$('#left-to-right-ser').html("This can be passed as parameter to a GET or POST request: <br/>" + serialized[0].hash);
			// We still need this so the form knows the menu has been changed.			
			document.getElementById("treeline").mm_content.value=serialized[0].hash;
			$.post("/behaviour/ajax/save_panels.php", 'msv=<?=$site->id?>&m='+serialized[0].hash);
		},
		autoScroll: true
	}
);

});
</script>
<?php } ?>


<script type="text/javascript" src="/treeline/behaviour/helpPopup.js"></script>
<script type="text/javascript">

	/*
	function openhelp(lnk) {
		var settings="menubar=no,top=100,left=100,width=600,height=600";
		var helpwindow = window.open(lnk, "helpwin", settings)
		if (window.focus) { helpwindow.focus(); }	
	}
	*/

	// Panel processing functions
	function loadPage(guid, act) {
		var new_location = "/treeline/panelManager/?guid="+guid+"&action="+act;
		if(panels_valid) {
			<?php if (!$_SESSION['panellist']) echo "new_location = new_location+'&list='+panellist;"; ?>
			window.location = new_location;
		}
		else alert("This panel manager is only functional for 3 column layouts");
	}
	function saveandclose(panellist) {
		//alert ("set main val to "+panellist);
		if (panellist=="exit") {
			if (!confirm("Are you sure you wish to exit without saving?\n\nAny changes you have made to the menu will not show on your page.\n\nIf you have created any custom panels that you do not want to appear on the page\nyou should delete them before you close this window.")) return;
			panellist='';
		}
		if (panellist) {
			if (panellist=="empty") panellist='';
			pageform.treeline_panels.value = panellist;
			pageform.submit();
		}
		self.close()
	}
	var pageform = window.opener.document.forms[0];
	var panellist = '<?=($_SESSION['panellist']?$_SESSION['panellist']:'')?>';
	var panels_valid = pageform.style[pageform.style.selectedIndex].value==3;
	if (!panellist) panellist = pageform.treeline_panels.value;
	//alert ("panel list "+panellist);
	
	// Set the height of the menu
	var windowHeight = 500 	// Should be able to get this in js really
	var contentHeight = windowHeight - document.getElementById("notes-header").offsetHeight;
	document.getElementById("panel-menu").style.height = (contentHeight-1)+"px";
	//document.getElementById("panel-form").style.height = contentHeight+"px";

</script>  

<div id="nfoholder" style="display: none; visibility: hidden;">
<div id="nfotopline"></div>
<div id="nfotext">
<p class="bodytext"><span id="nfo">Welcome to Treeline</span></p></div>
</div>

<script type="text/javascript" src="/treeline/behaviour/nfoPopup.js"></script>


</body>
</html>

<?php

function tidyList($list) {
	if (substr($list, 0, 1)==",") return substr($list, 1);
	else return $list;
}

?>