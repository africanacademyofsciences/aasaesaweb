<?

	ini_set("display_errors", true);
	error_reporting(E_ALL ^ E_NOTICE);
	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = read($_REQUEST,'feedback','error');

	$title = read($_POST,'title','');

	$phrase = read($_POST,'phrase',false);
	$ssearch = read($_POST, 'ssearch', '');
	$translang = read($_POST,'translang',false);

	$query = "select encoding from languages where abbr='".$_SESSION['treeline_user_language']."'";
	$encoding=$db->get_var($query);
	if (!$encoding) $encoding="utf-8";
	
	$transphrase = read($_POST,'transphrase','');
	//print "trans ($transphrase) to ($encoding) <br>";
	$transphrase = htmlentities($transphrase, ENT_QUOTES, $encoding);

	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
		//// ADD a new language to this site...
		if ($action == 'create') {

			$newLanguage=$_POST['language'];
			// Add all required pages - maybe dump this out to a function somewhere in sites?
			if ($newLanguage) {
				$site->setLanguage($newLanguage);
				$site->setTagline($page->drawLabel("tl_config_welcome", "Welcome to the")." ".$newLanguage." ".$page->drawLabel("tl_generic_site", "site"));
				$newVersion=$site->saveVersion($site->properties['microsite']);
				if ($newVersion) {
					if ($site->addPages($newVersion, $site->properties['site_title'], $site->properties['description'], $site->properties['keywords'])) {
						$feedback="success";
						$message[]=$page->drawLabel("tl_lang_new_version", "New language version")."[<strong>".$newLanguage."</strong>] ".$page->drawLabel("tl_lang_created", "created successfully");
					}
					else {
						$message[]=$page->drawLabel("tl_lang_err_pages", "Failed to add pages to your new version.");
						$site->deleteVersion($newVersion);
					}
				}
				else $message[]=$page->drawLabel("tl_lang_err_version", "Failed to create new language version");
			}
			else $message[]=$page->drawLabel("tl_lang_err_nosel", "No language selected");

		}
		else if ($action == 'delete') {

			$site->deleteVersion($_POST['msv']);
			$message[]=$page->drawLabel("tl_lang_deleted", "Microsite version has been deleted");
			$feedback="success";
			$_POST['msv']=0;
			$action="remove";
		}
		else if ($action=="switch") {
			$msv=$_POST['msv'];
			if ($msv>0) {
				$query="select l.title, l.abbr, l.encoding from languages l left join sites_versions sv on l.abbr = sv.language where sv.msv=$msv";
				$row=$db->get_row($query);
				//print "$query<br>";
				$_SESSION['treeline_user_site_id']=$msv;
				$_SESSION['treeline_user_language']=$row->abbr;
				$_SESSION['treeline_user_encoding']=$row->encoding;
				$_SESSION['treeline_user_language_title']=$row->title;
				$_SESSION['treeline_preview']=$msv;
				header("Location: /treeline/");
			}
		}
		else if($action=='translations' && !$ssearch){
			$intl = $_POST['admin'];
			//print "got transphrase($transphrase) tl($intl) search($ssearch)<br>";
			if ($intl) {
				if ($_POST['delete_phrase']==1) {
					//$message[]="Delete a phrase(".$_POST['phrase'].")<br>\n";
					$label_id = $_POST['phrase'];
					if ($label_id>0) {
						$message[]="This phrase has been deleted";
						$query = "DELETE FROM labels_translations WHERE label_id = $label_id";
						$db->query($query);
						//print "$query<br>\n";
						$query = "DELETE FROM labels_default WHERE label_id = $label_id";
						//print "$query<br>\n";
						$db->query($query);
						$query = "DELETE FROM labels WHERE id = $label_id";
						//print "$query<br>\n";
						$db->query($query);
						$_POST['phrase']=0;
					}
				}
				else if ($transphrase) {
					if (addDefaultTranslation($phrase, $_SESSION['treeline_language'], $transphrase, true)) $message[]=$page->drawLabel("tl_lang_save_success", "Your translation has been saved");
					else $message[]=$page->drawLabel("tl_lang_save_err", "Could not save translation");				
				}
			}
			else if ($transphrase) {
				if (addTranslation($phrase, $transphrase, $siteID, $_SESSION['treeline_user_language'])==-1) $message[]=$page->drawLabel("tl_lang_save_err", "Could not save translation");
				else $message[]=$page->drawLabel("tl_lang_save_success", "Your translation has been saved");
			}
		}				
		else if($action=='translate_all'){
			$success=$i=0;
			foreach($_POST as $k=>$v) { 
				if (substr($k, 0, strlen("transphrase_id_"))=="transphrase_id_") {
					$id=substr($k, strlen("transphrase_id_"));
					//print "addTranslation($v, ".$_POST['transphrase_'.$id].", $siteID, ".$_SESSION['treeline_user_language'].")<br>";
					switch (addTranslation($v, $_POST['transphrase_'.$id], $siteID, $_SESSION['treeline_user_language'])) {
						case 1: $success++; $i++; break;
						case -1: $i++; break;
					}
				}
			}
			if ($i) $message[]=$page->drawLabel("tl_generic_saved", "Saved").' '.$success.' '.$page->drawLabel("tl_generic_of", "of").' '.$i.' '.$page->drawLabel("tl_lang_translations", "translations");
			if ($success<$i) $message[]=($i-$success).' '.$page->drawLabel("tl_lang_saveall_err", "translations failed to save");
		}
		else if($action=='systranslations'){

			$success=$i=0;
			foreach($_POST as $k=>$v) { 
				if (substr($k, 0, strlen("transphrase_id_"))=="transphrase_id_") {
					$id=substr($k, strlen("transphrase_id_"));
					//print "addSysTranslation($v, ".$_POST['transphrase_'.$id].", ".$_POST['syslang'].")<br>";
					$i++;
					if(addSysTranslation($v, $_POST['transphrase_'.$id], $_POST['syslang'], $_POST['encoding'])) {
						$success++;
					}
				}
			}
			if ($i) $message[]=$page->drawLabel("tl_generic_saved", "Saved").' '.$success.' '.$page->drawLabel("tl_generic_of", "of").' '.$i.' '.$page->drawLabel("tl_lang_translations", "translations");
			if ($success<$i) $message[]=($i-$success).' '.$page->drawLabel("tl_lang_saveall_err", "translations failed to save");
			//if ($i) $message[]="Saved $success of $i translations";
			//if ($success<$i) $message[]=($i-$success)." translation(s) failed to save";
		}

	}


	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

span#engphrase {
		padding-left:120px;
		font-family:Arial, Helvetica;
		font-size:80%;
	}

fieldset {
	border:1px solid #ccc;
	width:450px;
	margin:20px 0;
}
	fieldset legend {
		font-family:Arial, Helvetica;
		font-size:90%;
	}
	
	
div.field {
	float: left;
	clear: left;
}

fieldset.translate_all {
	width:500px;
}
	fieldset.translate_all label {
		width:200px;
	}


div#trans-phrase-admin {
}	
	div#trans-phrase-admin select {
	}	
		div#trans-phrase-admin select option {
		}
			div#trans-phrase-admin select option.default {
				color: #aaa;
			}
';

	// Page title	
	$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_languages", 'Languages'));
	$pageTitleH2 .= ($action)?' : '.ucfirst($page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action)))):'';
	$pageTitle = $pageTitleH2;

	$pageClass = 'language';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');



function addTranslation($id, $translation, $msv, $lang) {
	global $db;
	//print "addT($id, $translation, $msv, $lang)<br>";
	if ($translation) {
		// Get language encoding
		//$coded_translation=htmlentities(mysql_real_escape_string($translation), ENT_QUOTES, $encoding);
		$translation = mysql_real_escape_string($translation);
		$query = "REPLACE INTO labels_translations (label_id,msv,longname) VALUES ('$id', $msv, '$translation')";
		//print "$query<br>";
		if( $db->query( $query ) ){
			$message[] = "Translation saved.";
			addDefaultTranslation($id, $lang, $translation);
			return 1;	
		}
	}
	else {
		if ($db->get_var("select count(*) from labels_translations where msv=$msv and label_id=$id")>0) {
			$query="delete from labels_translations where msv=$msv and label_id=$id";
			$db->query($query);
		}
		return 0;
	}
	return -1;
}	


function addDefaultTranslation($id, $lang, $translation, $update=false) {
	global $db;
	// Check if we already have a default translation for this term?
	//print "aDT($id, $lang, $translation, $update)<br>\n";
	if ($_POST['comment']) {
		$query = "UPDATE labels SET comment='".$db->escape($_POST['comment'])."' WHERE id=$id";
		//print "$query<br>\n";
		$db->query($query);
		if ($db->last_error) print "Failed to update comment text<br>\n";
	}
	
	$query="select count(*) from labels_default where label_id=$id AND lang='$lang'";
	if ($db->get_var($query)==0) {
		$query="INSERT INTO labels_default (label_id, lang, longname) values ($id, '$lang', '$translation')";
		//print "$query<br>";
		if (!$db->query($query)) print "Failed to add label($query)<br>\n";
		else return true;
	}
	else if ($update) {
		$query = "UPDATE labels_default SET longname = '$translation' WHERE label_id=$id AND lang='$lang'";
		//print "$query<br>";
		$db->query($query);
		if ($db->last_error) print "Failed to update label($query)<br>\n";
		else return true;
	}
	return false;
}


function addSysTranslation($id, $translation, $lang, $encoding) {
	global $db;
	//print "addSysTranslation($id, $translation, $lang, $encoding)<br>";
	if (!$id) return true;

	if ($encoding) {
		$coded_translation=htmlentities(mysql_real_escape_string($translation), ENT_QUOTES, $encoding);
		//$translation = mysql_real_escape_string($translation);
		$query="REPLACE INTO labels_default (label_id, lang, longname) values ($id, '$lang', '$coded_translation')";
		//print "$query<br>";
		return $db->query($query);
	}
	return false;
}	

?>


<div id="primarycontent">
<div id="primary_inner">

<?=drawFeedback($feedback,$message)?>	

<? 
if ($action == 'create') { 
	$page_html = '
	<p class="instructions">'.$page->drawLabel("tl_lang_add_msg", "To add another language to your site, please select it from the menu below.").'</p>
    <p>'.$page->drawLabel("tl_lang_add_msg2", "This process creates a homepage and functional pages such as news and sitemap and allows you to make sections and pages in your chosen language.").'</p>
    <form id="treeline" action="'.$_SERVER['PHP_SELF'].'" method="post">
    <input type="hidden" name="action" value="'.$action.'" />
    <fieldset>
        <div class="field" style="padding-top:10px;">
            <label for="lang">'.$page->drawLabel("tl_lang_select", "Select language").':</label>
            '.$page->drawSelectLanguages('language', '', $page->getCurrentLanguageVersions()).'
        </div>	
        <div class="field">
            <label for="submit" style="visibility:hidden">Submit:</label>
            <input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_submit", "submit").'" />
        </div>
    </fieldset>
    </form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_lang_add_title", "Add another language"), "blue");
}
else if ($action == 'delete' || $action=="remove") {

	if(!$message || $feedback=="success"){
		if (!$_POST['msv']) { 

			$exclude=array($site->id, $_SESSION['treeline_user_default_site_id']);

			$lang_list=$page->drawAvailableLanguages('msv', $exclude); 
			$lang_msg=($lang_list?$lang_list:'<p style="float:left;width: 310px;">'.$page->drawLabel("tl_lang_none_msg", "No language versions available to delete. <strong>Note:</strong> You cannot delete the version of a site you are currently editing.").'</p>');
			
			$page_title = $page->drawLabel("tl_lang_del_title", 'Delete a language version');
			$page_html = '
			<p>'.$page->drawLabel("tl_lang_del_msg", "To delete a language version of this microsite, please select it from the list below below:").'</p>
            <form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
            <input type="hidden" name="action" value="'.$action.'" />
            <fieldset>
                <div class="field" style="padding-top:10px;">
                    <label for="lang" style="padding-bottom:20px;">'.$page->drawLabel("tl_lang_select", "Select language").':</label>
                    '.$lang_msg.'
                </div>	
				';
			if ($lang_list) $page_html.='
                <div class="field">
                    <label for="submit" style="visibility:hidden">Submit:</label>
                    <input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_delete", "Delete").'" />
                </div>
				';
			$page_html.='
            </fieldset>
            </form>
			';
		} 
		else {
			$language_title=$page->getLanguageTitle('', $_POST['msv']);
			$page_title = $page->drawLabel("tl_generic_delete", 'Delete').' '.$language_title.' '.$page->drawLabel("tl_lang_version", "language version");
            $page_html = '
            <p>'.$page->drawLabel("tl_lang_del_confirm", "You are about to delete a version of your site. This will remove and sections and pages from this microsite").' '.$page->drawLabel("tl_lang_pub_in", "published in").' <strong>'.$language_title.'</strong></p>
            <p><strong><em>'.$page->drawLabel("tl_lang_confirm2", "Are you sure?").'</em></strong></p>
            
            <form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="msv" value="'.$_POST['msv'].'" />
            <fieldset>
                <div class="field">
                    <label for="submit" style="visibility:hidden">Submit:</label>
                    <input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_delete", "Delete").'" />
                </div>
            </fieldset>
           	</form>
			';
	 	} 
		echo treelineBox($page_html, $page_title, "blue");
 	} 
	else {
		?> 
		<p>Please go back and try again.</p>
		<? 
	} 

} 

else if ($action=="switch") { 

	$exclude=array($site->id);
	$lang_list = $page->drawAvailableLanguages('msv', $exclude);

    $page_html = '
	<p>'.$page->drawLabel("tl_lang_switch_msg", "To modify a different language version of this microsite, please select it from the list below below").'</p>
	<form id="treeline" action="'.$_SERVER['PHP_SELF'].'" method="post">
	<input type="hidden" name="action" value="'.$action.'" />
	<fieldset>
		<div class="field" style="padding-top:10px;">
			<label for="lang">'.$page->drawLabel("tl_lang_switch_select", "Select language").'</label>
			'.($lang_list?$lang_list:'<p style="float:left;width: 310px;">'.$page->drawLabel("tl_lang_switch_nolang", "No languages available to switch to").'</p>').'
		</div>	
		';
	if ($lang_list) $page_html.='
		<div class="field">
			<label for="submit" style="visibility:hidden">Submit:</label>
			<input class="submit" type="submit" value="'.$page->drawLabel("tl_lang_switch_switch", "Switch").'" />
		</div>
		';
	$page_html.='		
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_lang_switch_title", "Switch to editing a different language version"), "blue");
} 

// -------------------------------------------------------------------------
// Translate a single phrase
// -------------------------------------------------------------------------
else if ($action == 'translations') { 

	$intl = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "admin");
	if ($intl) {
		$language_title = ucfirst(strtolower($_SESSION['treeline_language_title']));
		$page_html = '
		<p>'.$page->drawLabel("tl_lang_trad_msg", "In this section you can alter the translations of words & phrases used around Treeline").'</p>
		<form id="treeline" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="phrase" value="'.$phrase.'" />
		<input type="hidden" name="admin" value="1" />
		<fieldset>
		';
	}
	else {
		$language_title = ucfirst(strtolower($_SESSION['treeline_user_language_title']));
		$page_html = '
		<p>'.$page->drawLabel("tl_lang_trad_msg1", "In this section you can alter the translations of words & phrases used around the site. The English versions will always stay the same.").'</p>
		';
		if ($user->id==1) {
			$page_html .= '
			<p>'.$page->drawLabel("tl_lang_trad_msg2", "You can use the form below to select specific translations").'</p>
			';
			$page_html.='
			<p>Or you can <a href="/treeline/languages/?action=translate_all">'.$page->drawLabel("tl_lang_trad_msg3", "translate all labels at once.").'</a></p>
			';
		}
		$page_html.='
		<form id="treeline" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="phrase" value="'.$phrase.'" />
		<input type="hidden" name="admin" value="0" />
		<fieldset>
		';
		/*
		$page_html = '
		<p>'.$page->drawLabel("tl_lang_trad_msg1", "In this section you can alter the translations of words & phrases used around the site. The English versions will always stay the same.").'</p>
		<p>'.$page->drawLabel("tl_lang_trad_msg2", "You can use the form below to select specific translations or").' <a href="/treeline/languages/?action=translate_all">'.$page->drawLabel("tl_lang_trad_msg3", "translate all labels at once.").'</a></p>
		<form id="treeline" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="phrase" value="'.$phrase.'" />
		<input type="hidden" name="admin" value="0" />
		<fieldset>
		';
		*/
	}
	$page_title = $page->drawLabel("tl_lang_".$language_title, 'Amend '.$language_title.' translations');
	$label_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "phrase", 0);
		
	$query = "SELECT l.id, l.shortname, l.longname, l.`comment`
		".($intl?",ld.longname AS `default`":"")."
		FROM labels l
		".($intl?"LEFT OUTER JOIN labels_default ld ON ld.label_id = l.id AND ld.lang='".$_SESSION['treeline_language']."'":"")."
		WHERE system=".($intl?2:0)." 
		ORDER BY l.longname";
	//print "$query<br>\n";
	//$query = "SELECT id, shortname, longname FROM labels WHERE system="" ODER BY longname";
	//print "$query<br>\n";
	$page_html.='
		<legend>'.$page->drawLabel("tl_lang_select_title", "Select phrase").'</legend>
		<div class="field" id="trans-phrase-'.($intl?"admin":"").'">
			<label for="phrase">'.$page->drawLabel("tl_lang_lab_default", "Default phrase").'</label>
			<select name="phrase" id="phrase" onchange="document.location=\'/treeline/languages/?action='.$action.'&amp;admin='.($intl?1:0).'&amp;phrase=\'+this.value;">
            <option value="0">'.$page->drawLabel("tl_lang_seltext", "Select label text").'</option>
	';
	//print "phrase(".$_REQUEST['phrase'].") $query<br>";
	
	if( $results = $db->get_results( $query ) ){
		foreach( $results as $item ){ 
			if ($item->id==$label_id) {
				$comment=$item->comment;
				$label_text=$item->longname;
				$default = $item->default;
				$shortname = $item->shortname;
			}
			$selected='';
			if($label_id==$item->id) $selected = ' selected="selected"'; 
			//$page_html.='<option class="'.($item->default?"default":"").'" value="'.$item->id.'"'.$selected.'>'.(substr($item->longname,0,40)).'</option>';
			$tmp = (substr($item->longname,0,40));
			//$tmp = (substr($page->drawLabel($item->shortname, $item->longname),0,40));
			$page_html.='<option class="'.($item->default?"default":"").'" value="'.$item->id.'"'.$selected.'>'.$tmp.'</option>';
		}
	}
	
	$page_html.='
			</select>
		</div>
		';
	if ($ssearch) {
		$label_id = 0;	// Never show a result here only possible phrases
		$html = '';
		// Search defaults if we are in Treeline
		//$query = "SELECT * FROM labels_default WHERE longname like '%$ssearch%' AND lang='".($intl?$_SESSION['treeline_language']:$_SESSION['treeline_user_language'])."' ORDER BY longname";
		$query = "SELECT label_id, longname FROM labels_default WHERE longname like '%$ssearch%' AND lang='".($intl?$_SESSION['treeline_language']:$_SESSION['treeline_user_language'])."' ";
		$query.= "UNION SELECT id AS label_id, longname FROM labels WHERE longname like '%$ssearch%' ";
		$query .= "AND system=".($intl?"2":"0")." ";
		$query.= "ORDER BY longname ";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$html.='<li><a href="/treeline/languages/?action=translations&amp;admin='.($intl?1:0).'&amp;phrase='.$result->label_id.'">'.$result->longname.'</a></li>';
			}
		}
		if ($html) {
			$listhtml='<h3>'.$page->drawLabel("tl_lang_srch_msg1", "Default translations for this search").'</h3><ul>'.$html.'</ul>';
			$html = '';
		}
		
		// If we are not in Treeline we should also check for matching site translations
		if (!$intl) {
			$query = "SELECT * FROM labels_translations WHERE longname LIKE '%$ssearch%' AND msv=".$site->id." ORDER BY longname";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					$html.='<li><a href="/treeline/languages/?action=translations&amp;admin=0&amp;phrase='.$result->label_id.'">'.$result->longname.'</a></li>';
				}
			}
			if ($html) $listhtml=$listhtml.'<h3>'.$page->drawLabel("tl_lang_srch_msg2", "Site translations for this search").'</h3><ul>'.$html.'</ul>';
		}		
	}

	$page_html.='
		<label for="f_search">'.$page->drawLabel("tl_lang_field_search", "Or search for").'</label>
		<input type="text" name="ssearch" id="f_search" value="'.$ssearch.'">
		<fieldset class="buttons">
			<input type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'" />
		</fieldset>
		'.$listhtml;
	$page_html.='</fieldset>';
	
	if ($label_id>0) { 
	
		$page_html.='<fieldset style="margin-top:20px;"><legend>'.$page->drawGeneric("translation", 1).'</legend>';
		
		if ($comment) {
			$page_html.='<div class="field">';
			$page_html.='<label for="usagetext">'.$page->drawLabel("tl_lang_lab_usage", "Label usage").'</label>';
			if ($user->id==1) $page_html.='<input type="text" name="comment" value="'.$comment.'" />';
			else $page_html.='<p style="float: left;">'.$comment.'</p>';
			$page_html.='</div>';
		}
		$page_html.=(($label_text)?'<div class="field"><label for="labeltext">'.$page->drawLabel("tl_lang_lab_text", "Label text").'</label><p style="float: left;">'.$label_text.'</p></div>':"");

		if ($intl) $thisphrase = $default;
		else {
			// Find out current translation or default translation
			$query = "SELECT lt.longname FROM labels_translations lt WHERE lt.msv=".$site->id." and lt.label_id=".$label_id;
			$thisphrase = $db->get_var($query);
			if (!$thisphrase) {
				$query = "SELECT longname FROM labels_default WHERE label_id=$label_id AND lang='".$_SESSION['treeline_user_language']."'";
				$thisphrase = $db->get_var($query);
			}
		}
		//print "got trans (".$thisphrase->longname.") decoded(".$thisphrase->longname.") ".utf8_decode($thisphrase->longname)."<br>";
		//ucfirst(strtolower($_SESSION['treeline_user_language_title']))
		$page_html .= '
		<div class="field">
			<label for="transphrase">'.$page->drawLabel("tl_lang_lab_".$language_title, $language_title.' phrase').'</label>
			<textarea type="text" name="transphrase" id="transphrase">'.html_entity_decode($thisphrase).'</textarea>
		</div>
	
		<fieldset class="buttons">
			<input type="submit" class="submit" value="'.$page->drawGeneric("update", 1).'" />
		</fieldset>
		';

		if ($intl) {
			$page_html .='
			<div class="field">
				<label for="f_del_phrase">'.$page->drawLabel("tl_lang_del_label", 'Delete phrase').'</label>
				<input type="checkbox" style="width:auto;" name="delete_phrase" id="f_del_phrase" value="1" />
			</div>
			';
		}
		$page_html.='</fieldset>';
	} 
	$page_html.='
	</form>
	';
	echo treelineBox($page_html, $page_title, "blue");
	
} 
// -------------------------------------------------------------------------



// ----------------------------------------------------------------------------------
else if ($action == 'translate_all') { 

	$language_title = ucfirst(strtolower($_SESSION['treeline_user_language_title']));
	$page_title = $page->drawLabel("tl_lang_amend_title", 'Amend '.$language_title.' translations');

	$page_html = '
	<p>'.$page->drawLabel("tl_lang_all_msg", "You can use the form below to change all the label translations in one go.").'</p>
    <form id="treeline" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
	<input type="hidden" name="action" value="'.$action.'" />
	<input type="hidden" name="phrase" value="'.$phrase.'" />
	<fieldset class="translate_all">
		<legend>'.$page->drawLabel("tl_lang_transall_title", "Enter translations").'</legend>
        <div class="field">
		';
	$l=$page->getTranslations($_SESSION['treeline_user_site_id'], $_SESSION['treeline_user_language'], 0);
	$i=0;
	foreach ($l as $label) {
		$label_text = $label['site'];
		if (!$label_text) $label_text = $label['default'];
		if (!$label_text) $label_text = $label['label'];
		//print_r($label);
		$page_html.='
		<label for="transphrase_'.$i.'">'.$label['eng'].'</label>
		<input type="hidden" name="transphrase_id_'.$i.'" value="'.$label['id'].'" />
		<textarea type="text" name="transphrase_'.$i.'" id="transphrase_'.$i.'">'.($_POST['transphrase_'.$i]?$_POST['transphrase_'.$i]:$label_text).'</textarea>
		';
		$i++;
	}
    $page_html.='
        </div>
    
        <fieldset class="buttons">
            <button class="submit" value="update">'.$page->drawLabel("tl_generic_update", "Update").'</button>
        </fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page_title, "blue");
} 
// ----------------------------------------------------------------------------------

else if ($action == 'systranslations') { 

	$syslang=read($_REQUEST, 'syslang', 'en');
	$page_title = $page->drawLabel("tl_lang_master_title", "Amend default translations");
	$page_html = '
    <form style="background:none;border:none;" method="post" id="f_setsyslang" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
	<input type="hidden" name="action" value="'.$action.'" />
	<h3>'.$page->drawLabel("tl_lang_sys_master", "Default").' <select style="margin-bottom:3px;" onchange="document.getElementById(\'f_setsyslang\').submit.click();" name="syslang">'.$page->drawSelectLanguages("syslang", $syslang, array(), false).'</select> '.$page->drawLabel("tl_lang_translations", "translations").'</h3>
    <input name="submit" type="submit" class="hide" />
    </form>
	';
	$page_html.='
	<hr />
	<p>'.$page->drawLabel("tl_lang_sys_msg", "Use this form to configure the default labels that will be used for all newly created sites.").'</p>
	';

	// Get language encoding
	$query = "select encoding from languages where abbr='$syslang'";
	//print "$query<br>";
	$encoding=$db->get_var($query);
	
	$page_html.='    
    <form id="treeline" action="'.($_SERVER['PHP_SELF'].($DEBUG?'?debug':'')).'" method="post">
	<input type="hidden" name="action" value="'.$action.'" />
	<input type="hidden" name="syslang" value="'.$syslang.'" />
	<input type="hidden" name="encoding" value="'.$encoding.'" />
	<fieldset class="translate_all">
		<legend>'.$page->drawLabel("tl_lang_transsys_title", "Enter translations").'</legend>
        <div class="field">
		';
	$l=$page->getTranslations($_SESSION['treeline_user_site_id'], $syslang);
	foreach ($l as $label) {
		if ($label['id']) {
			$i++;
			$label_text = $label['default'];
			//print_r($label);
			$page_html.='
			<label for="transphrase_'.$i.'">'.$label['eng'].'</label>
			<input type="hidden" name="transphrase_id_'.$i.'" value="'.$label['id'].'" />
			<input type="text" name="transphrase_'.$i.'" id="transphrase_'.$i.'" value="'.(($_POST['transphrase_'.$i])?$_POST['transphrase_'.$i]:$label_text).'" /> 
			';
		}
	}
    $page_html.='
        </div>
    
        <fieldset class="buttons">
            <input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_update", "Update").'" />
        </fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page_title, "blue");
	
} 


else if ($action == 'treelinetranslations') { 
	/*
	$syslang=read($_REQUEST, 'syslang', 'en');
	?>

    <form style="background:none;border:none;" method="GET" id="f_setsyslang" action="<?=$_SERVER['PHP_SELF']?><?php if ($DEBUG) echo '?debug'?>" method="post">
	<input type="hidden" name="action" value="<?=$action?>" />
	<h3>Treeline <select style="margin-bottom:3px;" onchange="document.getElementById('f_setsyslang').submit.click();" name="syslang"><?=$page->drawSelectLanguages("syslang", $syslang, array(), false)?></select> Translations</h3>
    <input name="submit" type="submit" class="hide" />
    </form>
    
	<hr />
	<p>You can use the form below to change all Treeline translations in one go.</p>
    
    <form id="treeline" action="<?=$_SERVER['PHP_SELF']?><?php if ($DEBUG) echo '?debug'?>" method="post">
	<input type="hidden" name="action" value="<?=$action?>" />
	<input type="hidden" name="syslang" value="<?=$syslang?>" />
	<fieldset class="translate_all">
		<legend>Enter translations</legend>
        <div class="field">
            <? 
			$l=$page->getTranslations($_SESSION['treeline_user_site_id'], $syslang, 2);
            foreach ($l as $label) {
				$i++;
				//print_r($label);
				?>
				<label for="transphrase_<?=$i?>"><?=$label['eng']?></label>
				<input type="hidden" name="transphrase_id_<?=$i?>" value="<?=$label['id']?>" />
				<input type="text" name="transphrase_<?=$i?>" id="transphrase_<?=$i?>" value="<?=(($_POST['transphrase_'.$i])?$_POST['transphrase_'.$i]:$label['txt'])?>" /> 
				<?php
            }
            ?>
        </div>
    
        <fieldset class="buttons">
            <button class="submit" value="update">Update</button>
        </fieldset>
	</fieldset>
	</form>
	<? 
	*/
} 


?>

</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>