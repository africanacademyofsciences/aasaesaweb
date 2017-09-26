<?php

//	ini_set("display_errors", "yes");
//	error_reporting(E_ALL);


	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	

	$MAX_PALATES = 99;
	$palate_root=$_SERVER['DOCUMENT_ROOT']."/style";
	
	$action = read($_REQUEST,'action','');
	if (!$action) $action="load";
	
	// user feedback
	$feedback = read($_REQUEST,'feedback','error');
	
	$palate = read($_POST,'palate',''); // Page title
	$msv = read($_POST,'siteid',''); // Page title
	$data = read($_POST, 'data', '');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {// Form has been submitted
	
		if ($action=="Edit palate") {
			if (!$_POST['palate']>0) $message[]="No palate file number selected"; 
			else {
				$palate_file_name="/scheme/palate".$palate.".css";
				$palate_file=$palate_root.$palate_file_name;
			}
		}
		else if ($action == "Edit site scheme") {
			if (!$_POST['siteid']) $message[]="No microsite was selected";
			else {
				$palate_file_name="/microsite/".$_POST['siteid'].".css";
				$palate_file=$palate_root.$palate_file_name;
			}
		}
		else if ($action == "Save CSS") {
			$palate_file_name=$_POST['palate_file_name'];
			$palate_file=$palate_root.$palate_file_name;
			//$message[]="save data \n".$data." \n to $palate_file";
			
			if (file_exists($palate_file)) {
				$backup_palate_file=$palate_file.".bak";
				
				//print "copy($palate_file, $backup_palate_file)<br>\n";
				if (file_exists($backup_palate_file)) {
					unlink($backup_palate_file);
				}
				
				if (copy($palate_file, $backup_palate_file)) {
					//print "chmod($palate_file, 0775)<br>\n";
					chmod($palate_file, 0775);	
					//unlink($palate_file);
					//print "file_put_contents($palate_file, $data)<br>\n";
					if (file_put_contents($palate_file, $data)) {
						$feedback="success";
						$message[]="This palate has been updated.";
						$action="load";
						//$palate_file='';
						//$palate=0;
						//$msv=0;
							
					}
					else $message[]="Failed to write CSS data";
				}
				else $message[]="Failed to backup original file";
			}
			else $message[]="Palate file to update was not found?";
		}
	}
	
	// Collect a list of valid palate files.
	for ($i=2; $i<=$MAX_PALATES; $i++) {
		$pn=$i<10?"0".$i:$i;
		if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/scheme/palate".$pn.".css")) {
			$palate_file_list.='<option value="'.$pn.'"'.($i==($palate+0)?" selected":"").'>Palate #'.$i.'</option>';
		}
	}
	if ($palate_file_list) $palate_file_list='<option value="0">Select palate file</option>'.$palate_file_list;
	else $palate_file_list='<option value="0">No palates found</option>';

	// Collect a list of microsites
	$query="SELECT sv.msv, s.title, l.title as language_title FROM sites s
		LEFT JOIN sites_versions sv ON s.microsite=sv.microsite 
		LEFT JOIN languages l on sv.language=l.abbr
		order by title";
	//print "$query<br>\n";
	if ($results=$db->get_results($query)) {
		foreach($results as $result) {
			if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".$result->msv.".css")) {
				$microsite_option_list.='<option value="scheme'.$result->msv.'">'.$result->title.($site->config['setup_language']?"(".$result->language_title.")":"").'</option>';
			}
		}
	}
	if ($microsite_option_list) $microsite_option_list='<option value="0">Select microsite scheme</option>'.$microsite_option_list;
	else $microsite_option_list='<option value="0">No microsites CSS found</option>';
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables','events'); // all CSS needed by this page
	$extraCSS = '
	
form#palate-select {
	float:left;
}
form#palate-select fieldset#palate-default, fieldset#palate-microsite {
	float: left;
}
form#palate-select fieldset input.submit {
	float: right;
}

form fieldset textarea#palate-data {
	width: 600px;
	height: 400px;
}

'; // extra on page CSS
	
	$js = array(''); // all external JavaScript needed by this page
	$extraJS = '
'; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Colour schemes';
	$pageTitle = 'Colour schemes';
	
	$pageClass = 'colours';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
	
?>
    <div id="primarycontent">
        <div id="primary_inner">
        <?=drawFeedback($feedback,$message)?>

		<form method="post" action="" id="palate-select" >
        	<fieldset id="palate-default">
        	<select name="palate">
            	<?=$palate_file_list?>
            </select>
        	<input class="submit" type="submit" name="action" value="Edit palate" />
            </fieldset>
            
        	<fieldset id="palate-microsite">
        	<select name="siteid">
            	<?=$microsite_option_list?>
            </select>
        	<input class="submit" type="submit" name="action" value="Edit site scheme" />
            </fieldset>

        </form>

		<?php if (file_exists($palate_file)) { ?>
        <h2 style="clear:left" >Modify <?=$palate_file_name?></h2>
		<p>You can modify this palate directly below. Please ensure you know what you are doing before making changes to this file. The original palate data will be overwritten.</p>
		<form method="post" action="">
	       	<fieldset>
            <input type="hidden" name="palate_file_name" value="<?=$palate_file_name?>" />
        	<textarea id="palate-data" name="data"><?=file_get_contents($palate_file)?></textarea>
            <input class="submit" type="submit" name="action" value="Save CSS" />
            </fieldset>
        </form>
        <?php } ?>
        
        </div>
    </div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>