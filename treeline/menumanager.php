<?php

	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
	
	// Make sure no direct/unauthorised access to this page
	if ($_SESSION['treeline_user_group']=="Author" && !$guid) redirect("/treeline/");
	
	//$siteID = $_SESSION['siteid'];
	$guid = read($_REQUEST,'guid','');
	$parent = read($_REQUEST,'parent',false);
		
	$message = array();
	$feedback = read($_GET,'feedback','error');
	
	$datum = array();
	$data = array();
	
	$mode = read($_REQUEST,'mode','');
	$referer = read($_REQUEST,'referer','')	;
	
	$pagename = read($_GET,'pagename',false);
	$btntxt = ($pagename)? ' &amp; Edit Content':'';

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
		$content = (isset($_POST['mm_content']))?$_POST['mm_content']:'';
		//echo "content - $content<br>\n";

		if ($_POST['submitted']==1) {
			if ($content) {
			
				// Collect the most recently saved version of the menu for this microsite?
				$saved_menu=file_get_contents($_SERVER['DOCUMENT_ROOT']."/silo/tmp/menu-".$site->id.".txt");
				$children = unserialize($saved_menu);
				// This is horrific but it appears that jquery does not correctly
				// create the serialized string when passed as a post parameter.
				if ($children['m']) {
					if (substr($children['m'],0,9)=="mm[0][id]") {
						$children['mm'][0]['id']=substr($children['m'],10);
					}
					else {
						// This should never happen but lets keep an eye on it for now.
						print "MMMMMM - ".print_r($children['m'], true)."<be>\n";
					}
				}
				$treeline->saveMenu($parent, $children['mm']);
				$message[]= $page->drawLabel("tl_menu_err_updated", 'Menu updated');			
				$feedback = "success";
			} 
			// The menu was not changed but we may still need to 
			else $message[] = $page->drawLabel("tl_menu_err_unchanged", 'The menu was unchanged');

			// Wether or not the menu was updated we still need to forward the user to edit
			// content if they are in edit mode.
			if ($mode=='edit' && $guid) {
			
				clearCache(array('footer.inc', 'menu,inc', 'sitemap.inc'));
				
				$newPage = new Page;
				$newPage->loadByGUID($guid);
				// Dont edit image galleries.
				if (
					$newPage->template_id==68 ||		// new gallery page
					$newPage->template_id==16			// new resources page
					) {
					$newPage->publish();
					redirect("/treeline/pages/?action=edit");
				}
				// Forward to the website in edit mode
				else {
					redirect($newPage->drawLinkByGUID($guid)."?mode=edit&referer=".$referer);
				}
			}
		}
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','menumanager','tables'); // all CSS needed by this page
	$extraCSS = '
	
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
	}


	//div.menu-bg1 { background-color:#F6FAFA; }
	//div.menu-bg2 { background-color:#EDF1F1; }

	form fieldset input.submit {
		margin-left: 10px;
		margin-right: 10px;
	}
	form fieldset input.submit,
	form fieldset input.cancel {
		float: right;
	}	
'; // extra on page CSS
	
	// all external JavaScript needed by this page
	$js = array ('interface-1.2', 'inestedsortable');
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $pageTitle = $page->drawLabel("tl_menu_title", 'Menu manager');
	
	$pageClass = 'edit-structure';
	$mm_index=0;

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>
<div id="primarycontent">
	<div id="primary_inner">

	<?php
   	echo drawFeedback($feedback,$message);
    
	echo '<h2 class="pagetitle rounded">'.($guid?$page->drawLabel("tl_generic_step", "Step")." 3 of 4: ":"").$page->drawLabel("tl_menu_header", "Arrange the pages").'</h2>';
        
	// We have chosen a section or have one already selected
	if ($parent) {
			
		if ($user->drawGroup()=="Author" && !$pagename) { 
			$page_html='<p>'.$page->drawLabel("tl_menu_err_noaccess", "You do not have sufficient access to edit the menu").'</p>';
		} 
		else {
    		$page_html='<form id="mm_form" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post" id="mmForm">
        	<fieldset>
				<input type="hidden" id="mm_content" name="mm_content" value="" />
				<input type="hidden" id="guid" name="guid" value="'.$guid.'" />
				<input type="hidden" id="parent" name="parent" value="'.$parent.'" />
				<input type="hidden" id="mode" name="mode" value="'.$mode.'" />
				<input type="hidden" id="referer" name="referer" value="'.$referer.'" />
				<input type="hidden" id="pagename" name="pagename" value="'.$pagename.'" />
				<p>'.$page->drawLabel("tl_menu_msg_info1", "Click and drag the pages into the order you prefer. Drag right or left to demote or promote the page. Then click save changes").'</p>
				'.($guid?"<p>".$page->drawLabel("tl_menu_msg_info2", 'Note: you can only position the newly created page in the menu')."</p>":"").'
				'.$treeline->drawMenuManagerByParent($parent, $guid, $pagename?1:0, 0).'
				<fieldset class="buttons">
					<input type="hidden" name="submitted" value="1" />
					<input type="submit" class="submit" name="action" value="'.$page->drawLabel("tl_generic_save_change", "Save changes").$btntxt.'" />
				</fieldset>
			</fieldset>
        	</form>		
			';
		}
		echo treelineBox($page_html, $page->drawLabel("tl_menu_title", "Arrange the menu within")." : ".$db->get_var("select title from pages where guid='$parent'")." ".$page->drawLabel("tl_generic_section", "section"), "blue");
	} 

	// We are currently editting a particular page so no need to show the section chooser
	if(!$pagename){
		$page_html='
        <form action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="get" id="section_choice">
            <fieldset>
                <label for="title">'.ucfirst($page->drawLabel("tl_generic_section", "Section")).':</label>
                <select name="parent" xonchange="mySubmit(this)" onchange="document.getElementById(\'section_choice\').submit()">
                    <option value="0">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).'</option>
                    '.$treeline->drawSelectPagesByParent($site->id,$parent,$site->id,array(4,11,75)).'
                </select>
                <!--[if IE]>'.(0?'<fieldset class="buttons"><input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_select", "Select").'" /></fieldset>':'').'<![endif]-->
            </fieldset>
        </form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_menu_title1", "Select section to edit"), $parent?"grey":"blue");
	} 
	
	?>

	</div>
</div>


<script type="text/javascript">
jQuery( function($) {

$('#mm').NestedSortable(
	{
		accept: 'page-item1',
		noNestingClass: "no-nesting",
		nestingPxSpace: 20,
		opacity: 0.8,
		helperclass: 'helper',
		onChange: function(serialized) {
			//$('#left-to-right-ser').html("This can be passed as parameter to a GET or POST request: <br/>" + serialized[0].hash);
			// We still need this so the form knows the menu has been changed.			
			document.getElementById("mm_form").mm_content.value=serialized[0].hash;
			$.post("/behaviour/ajax/save_menu.php", 'msv=<?=$site->id?>&m='+serialized[0].hash);
		},
		autoScroll: true,
		handle: '.sort-handle'
	}
);

$('#spans-divs').NestedSortable(
	{
	accept: 'page-item3',
	opacity: 0.8,
	helperclass: 'helper',
	nestingPxSpace: 20,
	currentNestingClass: 'current-nesting',
	fx:400,
	revert: true,
	autoScroll: false
	}
);

$('#spans-divs-regular').Sortable(
	{
	accept: 'page-item4',
	opacity: 0.8,
	helperclass: 'helper'
	}
);
	
});
</script>

<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>