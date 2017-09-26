<?

	ini_set("display_errors", 1);
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	$guid = read($_REQUEST,'guid','');

	$message = array();
	$feedback = read($_GET,'feedback','notice');
	//$config = array();

	if ($action == "legacy") $lid = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "lid", 0);

	$image = new Image();
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		
		if ($action == 'create') {

		} 
		
		// if we find 'config_' in the name of the variable, then set it here...
		else if ($action == 'edit') {
			foreach($_POST as $name => $value ){
				//echo $name .' -> '. $value .'<br />';
				if( preg_match('/config_/', $name) ){
					$name = str_replace('config_','',$name);
					//$config[$name] = $value;
					//$treeline->config = $config;
					if( $name=='title') {
						if ($value && $value!=$_SESSION['treeline_user_site_title']){
							$query = "UPDATE sites SET title = '".$db->escape($value)."' WHERE microsite=".$_SESSION['treeline_user_microsite_id'];
							//print "update site title to ($value) q($query)<br>\n";
							if ($db->query($query)) {
								$_SESSION['treeline_user_site_title'] = $value;
							}
						}
					}
					else {
						if($name=='name'){
							$value = strtolower(preg_replace('\W','',$value));
							$config_name = $value;
						}
						// what we need to do here is replace the variables in the treeline->config array then save it.
						$treeline->setConfig($name,$value);
					}
				}
			}
			
			if($treeline->saveConfig()){ // Changes saved
				$message[] = $page->drawGeneric("changes_saved", 1);
				$feedback="success";
			}
			
			
		} 
		else if ($action == 'delete') {
		
			// 
		}
		
		else if ($action == "legacy") {
			$tmpnew = $db->escape($_POST['new_url']);
			$tmpold = $db->escape($_POST['old_url']);
			$query = "";
			if ($lid>0) {
				if ($_POST['delete_url']==1) $query = "DELETE FROM legacy_url WHERE id=$lid";
				else $query = "UPDATE legacy_url SET old_url='".$tmpold."', new_url='".$tmpnew."' WHERE id=$lid";
			}
			else if ($tmpold) {
				if (!$tmpnew) $tmpnew = "/";
				$query = "INSERT INTO legacy_url (old_url, new_url) VALUES ('$tmpold', '$tmpnew')";
			}
			else $message[]="You must specify and old URL a new URL match, no new URL will be considered a link to the homepage";
			
			if ($query) {
				$db->query($query);
				if ($db->last_error) $message[]="An error occurred while trying to update this URL, please try again and contact Chameleon Interactive if this error persists($query)";
				else {
					$lid = 0;
					$_POST['old_url']=$_POST['new_url']='';
					if ($_POST['delete_url']==1) {
						$message="That URL has been deleted";
						$feedback="success";
					}
				}
			}
		}
		 
		else if ($action == "images") {
		
			$feedback="error";
		
			// Go through post data once to create the full list of sizes
			foreach ($_POST as $k=>$v) {
				//print "got $k=>$v<br>\n";
				if (substr($k,0,5)=="imgsz") {
					//print "found a size<br>\n";
					$img_list.=substr($k,6).",";
				}
			}
			//print "stage 1 ".$img_list."<br>\n";
			
			// Go through post data again to remove any items from the list that have been marked for deletion
			foreach ($_POST as $k=>$v) {
				if (substr($k,0,6)=="imgdel") {
					//print "found a size to remove<br>\n";
					$img_list = str_replace(substr($k,7).",", "", $img_list);
				}
			}
			if ($img_list) $img_list=substr($img_list, 0, -1);
			//print "stage 2 ".$img_list."<br>\n";
			
			// Write a new image sizes list.
			$aList=explode(",",$img_list);
			foreach ($aList as $index) {
				if ($_POST['imgsz-'.$index]) 
					$real_list.=$_POST['imgsz-'.$index].":".$_POST['imgdsc-'.$index].",";
			}

			// Add a new item to the list if one has been added
			$new_size_ok = $image->sizeFormatOk($_POST['config_new_width'], $_POST['config_new_height'], $_POST['config_new_desc'], $_POST['config_new_crop']);
			if ($new_size_ok==1) {
				$new_size=($_POST['config_new_width']+0)."x".($_POST['config_new_crop']==1?"c":"").($_POST['config_new_height']+0);
				//print "got new size($new_size) crop(".$_POST['config_new_crop'].")<br>\n";
				$real_list.=$new_size.":".htmlentities($_POST['config_new_desc'], ENT_QUOTES, $site->properties['encoding']).",";
			}
			else if ($new_size_ok != '') $message[]=$new_size_ok;
			
			//print "got real_list($real_list) message count(".count($message).") m(".print_r($message, true).") m($message)<br>\n";
			if ($real_list && !count($message)) {
				if ($db->escape(substr($real_list,0,-1)) != $db->get_var("select value from config where name='image_sizes'")) {
					$query = "update config set value='".$db->escape(substr($real_list,0,-1))."' where name='image_sizes'";
					//print "$query<br>";
					if (!$db->query($query)) {
						$message[]="Could not update images sizes";
					}
					else {
						$redirectURL = '/treeline/settings/?action=images&feedback=success&message='.urlencode("your changes have been saved");
						//print "would redirect ($redirectURL)<br>\n";
						redirect($redirectURL);
					}
				}
				else $message[]=$page->drawLabel("tl_sett_err_nochange", "No changes where made");
			}
		}				
	}

	// PAGE specific HTML settings
	
	$css = array('forms'); // all CSS needed by this page
	$extraCSS = '
		div#primarycontent form fieldset span#siteURL{
			float:left;
			margin-top:7px;
		}
		
		div#primarycontent form fieldset input#config_name {
			width:10em;
		}

		form#legacy-form {
		}
			form#legacy-form input.checkbox {
				clear: none;
				margin-left: 0;
			}
		table#legacy-table {
			border-collapse: collapse;
			width: 680px;
			border-color: #ccc;
		}
			table#legacy-table th,
			table#legacy-table td {
				padding: 2px 4px;
				text-align: left;
			}
			table#legacy-table *.hitcount {
				text-align: center;
			}
		
	'; // extra on page CSS
	
	$js = array('palate_preview'); // all external JavaScript needed by this page
	$extraJS = '

function warndel(chk) {
	if (chk) {
		alert("'.$page->drawLabel("tl_sett_err_delperm", "When you save changes this image size will be deleted permenantly").'\n'.$page->drawLabel("tl_sett_err_uncheck", "Please uncheck this box if you do not want this size to be removed").'");	
	}
}
	
'; // extra on page JavaScript
	
	//// Page title	
	//$pageTitleH2 = ($action) ? 'Settings : '.ucwords($action) : 'Settings';
	//$pageTitle = ($action) ? 'Settings : '.ucwords($action) : 'Settings';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("setting", $action);
	$pageClass = 'settings';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php 
	if($user->drawGroup() == 'Superuser') { 
	
		echo drawFeedback($feedback,$message);

		if ($action == 'create') { //Create settings
    
		} 
		// -------------------------------------------------------------
		// Edit settings 
        else if ($action == 'edit') { 
			$page_html = '
            <form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
                <fieldset>
                    <input type="hidden" name="action" value="'.$action.'" />
                    <input type="hidden" name="guid" value="'.$guid.'" />
                    <p class="instructions">'.$page->drawLabel("tl_sett_conf_message", "This section allows for the editing of some of the core configuration options. Warning: Any changes made will affect the live website").'</p>
                    '.$treeline->drawEditableConfig().'
                    <fieldset class="buttons">
                        <input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
                     </fieldset>
                </fieldset>
            </form>
			';
			echo treelineBox($page_html, $page->drawLabel("tl_sett_conf_title", "Website configuration settings"), "blue");
		} 
		
		// Set up legacy URLs
		else if ($action == "legacy") {
			$tmpold = $_POST['old_url'];
			$tmpnew = $_POST['new_url'];
			if ($lid>0 && $_SERVER['REQUEST_METHOD']!="POST") {
				$query = "SELECT * FROM legacy_url WHERE id=$lid";
				if ($row = $db->get_row($query)) {
					$tmpold = $row->old_url;
					$tmpnew = $row->new_url;
				}
			}
			$page_html = '
                <form method="post" id="legacy-form">
                <fieldset class="field">
					<p>The old URL should be relative to the site root i.e. if the old URL was http://www.example.com/about-us/page-name/ you would enter about-us/page-name here.</p>
					<!-- <p>The old URL should be the full URL of the page on your previous website</p> -->
					<input type="hidden" name="lid" value="'.($lid+0).'" />
                	<label for="f_old">Old URL</label>
					<input type="text" name="old_url" class="text" value="'.$tmpold.'" />
                </fieldset>
                <fieldset class="field">
					<p>The new URL should be the full URL of the page you would like displayed</p>
					<!-- <p>The new URL should be relative to the site root i.e. /about-us/page-name/</p> -->
                	<label for="f_new">New URL</label>
					<input type="text" name="new_url" class="text" value="'.$tmpnew.'" />
                </fieldset>
				';
			if ($lid>0) {
				$page_html.='
                <fieldset class="field">
                	<label for="f_delete">Delete URL</label>
					<input type="checkbox" name="delete_url" class="checkbox" value="1" />
                </fieldset>
				';
			}
			$page_html.='
				<fieldset class="field">
					<label for="f_submit" style="visibility:hidden;">Submit</label>
					<input type="submit" name="submit" id="f_submit" class="submit" value="'.($lid>0?"Post":"Create").' URL" />
				</fieldset>
                </form>
				';
			echo treelineBox($page_html, "Create/Modify URL", "blue");
			
			// List all Legacy URLs
			$query = "SELECT * FROM legacy_url ORDER BY old_url";
			if ($results = $db->get_results($query)) {
				$page_html='
				
				';
				foreach ($results as $result) {
					$page_html.='<tr>
<td><a href="/treeline/settings/?action=legacy&lid='.$result->id.'">'.$result->old_url.'</a></td>
<td>'.$result->new_url.'</td>
<td class="hitcount">'.$result->count.'</td></tr>';
				}
				if ($page_html) $page_html = '<table id="legacy-table" border="1" cellpadding="0" cellspacing="0">
<tr><th>Old URL</th><th>New URL</th><th class="hitcount">Hit count</th></tr>
'.$page_html.'
</table>';
				else $page_html = '<p>There was a problem</p>';
			}
			else $page_html = '<p>There are no legacy URLs set up at the moment</p>';
			echo treelineBox($page_html, "Current URLs");
		}


		// -------------------------------------------------------------
		// Edit library image sizes
        else if ($action == 'images') { 
            if ($user->drawGroup() == "Superuser") {
				$page_html = '
				<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
				<fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="guid" value="'.$guid.'" />
					<p class="instructions">
						'.$page->drawLabel("tl_sett_img_msg1", "This section allows for the editing of the images sizes available").'.  
						'.$page->drawLabel("tl_sett_img_upto", "You can have up to").' '.$image->upload_max_sizes.' '.$page->drawLabel("tl_sett_img_width", "image sizes and any image may be up to").' '.$image->upload_max_width.' '.$page->drawLabel("tl_sett_img_wide", "pixels wide or").' '.$image->upload_max_height.' '.$page->drawLabel("tl_sett_img_tall", "pixels tall").'
					</p>
					<p>'.$page->drawLabel("tl_sett_img_msg2", "Changes made here will ONLY affect images uploaded after the settings have been saved").'</p>
					<p><strong>'.$page->drawGeneric("warning", 1).':</strong> '.$page->drawLabel("tl_sett_img_msg3", "Large images reduced to very small images may lose some details as pixels must be removed. Small images enlarged to large images may look blotchy as pixels must be added. Stretching or squashing an image to fixed dimensions may distort the image").'</p>
				';
				if (count($site->config['size']) < $image->upload_max_sizes) {
					$page_html.='
						<fieldset style="margin-top:20px;">
							<legend>'.$page->drawLabel("tl_sett_new_legend", "Add a new size").'</legend>
							<p>'.$page->drawLabel("tl_sett_img_newmsg1", "You can also enter some brief descriptive text for each image size to remind yourself of its purpose on the website. If you check the crop box your image will not be stretched/squashed to fit but a section with the required dimensions will be removed from the middle area of the image").'</p>
							
							<label for="config_new_width">'.$page->drawLabel("tl_sett_imgf_width", "Image width").':</label>
							<input type="text" name="config_new_width" id="config_new_width" value="'.$_POST['config_new_width'].'" maxlength="5" /><br />
							<label for="config_new_height">'.$page->drawLabel("tl_sett_imgf_height", "Image height").':</label>
							<input type="text" name="config_new_height" id="config_new_height" value="'.$_POST['config_new_height'].'" maxlength="5" /><br />
							<label for="config_new_desc">'.$page->drawLabel("tl_sett_imgf_desc", "Size description").':</label>
							<input type="text" name="config_new_desc" id="config_new_desc" value="'.$_POST['config_new_desc'].'" maxlength="30" /><br />
							<label for="config_new_crop">'.$page->drawLabel("tl_sett_imgf_crop", "Crop image").':</label>
							<input type="checkbox" class="right" name="config_new_crop" id="config_new_crop" '.($_POST['config_new_crop']==1?'checked="checked"':"").' value="1" /><br />
						</fieldset>
					';
				}
				else $page_html.='<h1>'.$page->drawLabel("tl_sett_img_upmax", "You have uploaded the maximum number of images sizes").'</h1>';

				$page_html.='				
					<fieldset style="margin-top:20px;">
						<legend>'.$page->drawLabel("tl_sett_img_confleg", "Configured sizes").'</legend>
						<p style="clear:left;">'.$page->drawLabel("tl_sett_iconf_msg1", "Below you can update the description of any of the image sizes or you can tick the checkbox to remove them from the list. Removing an image size will not remove any actual images just the ability to create images at that size").'</p>
						'.$image->drawConfigSizes().'
						<fieldset class="buttons">
						<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
						</fieldset>
					</fieldset>					 
				</fieldset>
				</form>
				';
			}
			else $page_html.='<p>Changing image sizes is only available to superusers</p>';
			echo treelineBox($page_html, $page->drawLabel("tl_sett_img_title", "Image library sizes"), "blue");		
		} 
	}
	 
	else{
		$page_html='
        <div class="feedback error">
            <h3>'.$page->drawGeneric("warning", 1).'</h3>
            <p>'.$page->drawLabel("tl_sett_err_super", "Only superusers can edit these configuration settings").'</p>
        </div>
		';
	} 

	?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>