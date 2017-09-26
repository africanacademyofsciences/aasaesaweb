<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/forms.class.php");

	// Chuck out barred access attempts to this function
	//print "Forms site(".$site->id.") setup(".$site->getConfig("setup_forms").") site(".$site->getConfig("site_forms").")<br>\n"; exit();
	
	if ($_SESSION['treeline_user_group']=="Superuser" && $site->getConfig("setup_forms")) {
		if ($site->id==1 || $site->getConfig("site_forms")) ;
		else {
			redirect("/treeline/");
		}
	}
	else {
		redirect("/treeline/");
	}
	
	$form_id = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'fid','');
	//$member_id = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'mid','');

	$form = new Form($form_id, $_GET['msv']==1?1:$site->id);

	$action = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'action','');
		
	$title = read($_POST,'title','');
	$description = read($_POST,'description','');

	$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'page',1);
	$keywords = read($_REQUEST,'keywords', '');

	//print "got action($action) fid($form_id) f->id(".$form->id.")<br>\n";
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($action == "create") {
			if ($form_id = $form->create($_POST)) {
				$nextsteps='
<li><a href="/treeline/forms/">View forms</a></li>
<li><a href="/treeline/forms/?fid='.$form_id.'&action=editform">Add blocks to your form</a></li>
';
			}
			else if (count($form->errormsg)) {
				foreach ($form->errormsg as $tmp_msg) {
					$message[]=$tmp_msg;
				}
			}
			else {
				$message[]="Failed to create new form";
			}
		}

		else if ($action=="duplicate") {

			$new_form_id = $form->duplicate($_POST['fid'], $_POST);
			foreach ($form->errormsg as $tmp_msg) {
				$message[]=$tmp_msg;
			}
			
			if ($new_form_id) {
				// Add the new form id to the event
				if ($_POST['event-guid']) {
					$query = "UPDATE events SET form_id=".$new_form_id." WHERE guid='".$_POST['event-guid']."'";
					print "$query<br>\n";
					if ($db->query($query)) {
						$redirectURL = "/treeline/events/?guid=".$_POST['event-guid']."&message=The event form has been added&feedback=success";
						redirect($redirectURL);
						//$message[]="Should we redirect again($redirectURL)";
					}
					else $message[]="Failed to update event data";
				}
				else {
					$message[]="Form duplicated ok";
					$feedback="success";
					$action='';
				}
			}
		}

		else if ($action == "edit") {
			if ($form_id = $form->update($_POST)) {
				$feedback="success";
				$message[]="Your form has been updated";
				$action='';
				$form_id=0;
			}
			else if (count($form->errormsg)) {
				foreach ($form->errormsg as $tmp_msg) {
					$messgae[]=$tmp_msg;
				}
			}
			else {
				$message[]="Failed to update form data";
			}
		}
		
		else if ($action=="delete") {
			if ($form->delete()) {
				$message[]="That form has been deleted";
				$feedback="success";
				$action="list";
				$form_id=0;
			}
			else {
				foreach($form->errormsg as $tmp_msg) {
					$message[]=$tmp_msg;
				}
			}
		}

		// Any building functions to process?		
		if ($action == "editform") {
		
			// Add a new block to the form.
			if (isset($_POST['addblock'])) {
				if (!$_POST['title']) $message[]="You must enter a title for your new block";
				else if (!$form->addBlock($_POST)) {
					$message[]="Failed to add block";
				}	
			}

			else if (isset($_POST['editblock']) && $_POST['bid']>0) {
				if (!$_POST['title']) $message[]="You must enter a title for this block";
				else if (!$form->updateBlock($_POST['bid'], $_POST)) {
					$message[]="Failed to edit block";
				}
			}
			
			else if (isset($_POST['deleteblock']) && $_POST['bid']>0) {
				if (!$form->deleteBlock($_POST['bid'])) {
					$message[]="Failed to delete block";
				}
			}
			
			// Save the blocks menu for this form.
			else if (isset($_POST['saveblocks'])) {
				//$message[]="Save blocks order as ....";		
				$content = (isset($_POST['mm_content']))?$_POST['mm_content']:'';
				if ($content) {
					//$message[] = "content($content)";
					if (preg_match_all("/mm\[\d*\]\[id\]=mm_(.*?)&/", $content."&", $reg)) {
						foreach ($reg[1] as $block_id) {
							$query = "UPDATE forms_blocks SET sort_order=".(++$i+0)." WHERE id=".$block_id;
							//print "$query<br>\n";
							$db->query($query);
						}
						$message[]="The block order has been saved.";
						$feedback="success";
					}
				}
			}

			// Add a new field to a block
			if (isset($_POST['addfield'])) {
				if (!$_POST['label']) $message[]="You must enter a label for your new field";
				if (!$_POST['name']) $message[]="You must enter a name for your new field";
				else if (!$form->field->checkName($form_id, $_POST['name'])) $message[]="This name has already been used for a field in this form";
				// If no errors then try to add the field.
				if (!count($message)) {
					if (!$form->addField($_POST['bid'], $_POST)) {
						$message[]="Failed to add field";
					}
				}	
			}

			// Save update to field data
			else if (isset($_POST['editfield'])) {
				if (!$_POST['label']) $message[]="You must enter a label for your new field";
				else if (!$form->updateField($_POST['ffid'], $_POST)) {
					$message[]="Failed to update field";
				}
			}

			// actally delete a field
			else if (isset($_POST['deletefield']) && $_POST['ffid']>0) {
				if (!$form->deleteField($_POST['ffid'])) {
					$message[]="Failed to delete field";
				}
			}
			
			// Save the blocks menu for this form.
			else if (isset($_POST['savefields'])) {
				$content = (isset($_POST['mm_content']))?$_POST['mm_content']:'';
				if ($content) {
					//$message[] = "content($content)";
					if (preg_match_all("/ff\[\d*\]\[id\]=mm_(.*?)&/", $content."&", $reg)) {
						foreach ($reg[1] as $field_id) {
							$query = "UPDATE forms_fields SET sort_order=".(++$i+0)." WHERE id=".$field_id;
							$db->query($query);
						}
						$message[]="The field order has been saved.";
						$feedback="success";
					}
				}
			}
			
			// Save field options
			else if (isset($_POST['fieldoptions'])) {
				foreach ($_POST as $k=>$v) {
					if (substr($k, 0, 5)=="title") {
						$tmp_block_id = substr($k, 6);
						//$message[]= "got title(".$_POST['title-'.($tmp_block_id+0)].") value(".$_POST['value-'.($tmp_block_id+0)].") val($tmp_value)<br>\n";
						$query = '';
						// Update a current option
						if ($tmp_block_id>0) {
							// Update option
							if ($_POST['title-'.($tmp_block_id+0)]) {
								$query = "UPDATE sites_options SET 
									value='".($_POST['value-'.$tmp_block_id]+0)."', 
									title='".$db->escape($_POST['title-'.($tmp_block_id+0)])."'
									WHERE id=".$tmp_block_id;
							}
							// Delete option
							else {
								$query = "DELETE FROM sites_options WHERE id=".$tmp_block_id;
							}
						}
						// Create a new options
						else if ($_POST['title-'.($tmp_block_id+0)]) {
							$query = "INSERT INTO sites_options 
								(name, value, title)
								VALUES 
								('field-".$_POST['ffid']."', 
								'".($_POST['value-'.$tmp_block_id]+0)."', 
								'".$db->escape($_POST['title-'.($tmp_block_id+0)])."')
								";
							$_POST['title-0']=$_POST['value-0']='';
						}
						if ($query) $db->query($query);
					}
					if (!count($message)) {
						$message[]="Field options updated";
					}
				}
			}

		}
		
	}
	
	else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
		if ($action == 'suspend') {
		
		}
		
		// Re-use a form
		else if ($action == "duplicate") {
		}

	}


	$css = array('forms','tables','../../style/stdform'); // all CSS needed by this page
	$extraCSS = '
	
	';

	if ($action=="editform") {
		$css[]="menumanager";
		$js = array ('interface-1.2', 'inestedsortable');
	}
	$css[]="builder";


	// Page title	
	$pageTitleH2 = ($action) ? 'Forms : '.ucwords(str_replace("-", " ", $action)) : 'Forms';
	$pageTitle =  $pageTitleH2;
	$pageClass = 'forms';


	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');

?>
<div id="primarycontent">
<div id="primary_inner">
<?php

	echo drawFeedback($feedback,$message);
	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

	//print "got action($action)<Br>\n";
	
	if (!$action) $action="list";	

	if ($action=="list" || $action=="preview" || $action=="download" || $action=="view") {

		$page_html='
		<p><a href="/treeline/forms/?action=create">Create a new form</a></p>
		<form id="treeline" action="/treeline/forms/'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<label for="keywords">Search for: </label>
			<input type="text" name="keywords" id="keywords" value="'.$keywords.'" /><br />
			<fieldset class="buttons">
				<input name="filter" type="submit" class="submit" value="Search" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		if ($form_id) $page_html.=$form->drawFormsList(1, $keywords, $form_id);
		
		echo treelineBox($page_html, "Find existing forms to manage", "blue");
		
		if (!$form_id) {
			$form_list = $form->drawFormsList($thispage, $keywords);
			if ($form_list) echo $form_list;
			else {
				echo '<p>No forms have been created yet.</p>';
			}
		}
		else if ($action == "preview") {
			?><p><strong>NOTE :</strong> In preview mode the submit button has been removed</p><?php
			$form->loadByID($form->id);
			$form_mode="PREVIEW";
			include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/form_display.inc.php";
			echo $replace;
		}
		else if ($action=="download") {
			$table = '';
			if ($_GET['refresh']==1) include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/form_download.inc.php";
			else $table = $_GET['table'];
			if ($table) {
				echo $form->drawDataList($table, $thispage);
			}
		}
		else if ($action=="view") {
			echo $form->drawData($_GET['table'], $_GET['did']);
		}

	}
	
	// Build the form
	else if ($action=="editform") {
		include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/editFormBlocks.php");
	}

	// Create or edit master form data
	else if ($action=="create" || $action=="edit" || $action=="duplicate") {
		include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditForm.php");
	}
	
	else if ($action=="delete") { 

		$page_html = '
			<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="fid" value="'.$form_id.'" />
				<input type="hidden" name="page" value="'.$thispage.'" />
				</legend>
				<p class="instructions">You are about to delete this form. <strong>Are you sure?</strong></p>
				<p class="instructions">If this form is used on any page within this site you will no longer be able to capture data via those pages.</p>
				<fieldset class="buttons">
					<button type="submit" class="submit">Delete</button>
				</fieldset>
			</fieldset>
			</form>
			';
		$page_title = 'Delete form: '.$form->title;
		echo treelineBox($page_html, $page_title, "blue");
	}
	
	

?>
</div>
</div>

<?php if ($action =="editform") { ?>
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
			$.post("/behaviour/ajax/save_blocks.php", 'msv=<?=$site->id?>&m='+serialized[0].hash);
		},
		autoScroll: true
	}
);
$('#ff').NestedSortable(
	{
		accept: 'page-item1',
		noNestingClass: 'no-nest',
		nestingPxSpace: 20,
		opacity: 0.8,
		helperclass: 'helper',
		onChange: function(serialized) {
			//$('#left-to-right-ser').html("This can be passed as parameter to a GET or POST request: <br/>" + serialized[0].hash);
			// We still need this so the form knows the menu has been changed.			
			document.getElementById("form-fields").mm_content.value=serialized[0].hash;
			//$.post("/behaviour/ajax/save.php", 'msv=<?=$site->id?>&m='+serialized[0].hash);
		},
		autoScroll: true
	}
);


});
</script>
<?php } ?>


<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>