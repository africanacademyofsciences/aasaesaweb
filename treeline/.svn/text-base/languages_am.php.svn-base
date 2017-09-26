<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	//include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/file.class.php");	

	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: ./treeline");
	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$language = read($_POST,'language',false);
	
	$site_properties = $site->properties;
	$languages = $site->getSiteLanguages($siteID);
	
	$sitelang = $site_properties['language'];
	
	
	$phrase = read($_POST,'phrase',false);
	$translang = read($_POST,'translang',false);
	$transphrase = read($_POST,'transphrase','');
	$transphrase = htmlentities( $transphrase , ENT_QUOTES, $site->properties['encoding']);
	//$transphrase = read($_POST,'transphrase',false);

	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		if($action=='translations'){

			//echo $translang .' '. $phrase.'<br />';
			/*
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';	
			*/
			if( $transphrase ){
				$query = "INSERT INTO labels_translations (label_id,lang,longname) VALUES ('". $phrase ."', '". $translang ."', '". mysql_real_escape_string(stripslashes($transphrase)) ."')";
				//echo $query.'<br />';
				if( @$db->query( $query ) ){
					$feedback = "success";
					$message = "Translation saved.";
				}else{
					$query = "UPDATE labels_translations SET longname='". mysql_real_escape_string(stripslashes($transphrase)) ."' WHERE label_id='". $phrase ."' AND lang='". $translang ."'";
					//echo $query.'<br />';
					$db->query( $query );
					if( $db->affected_rows>=0 ){
						$feedback = "success";
						$message = "Translation saved.";
					}else{
						$feedback = "error";
						$message = "Your translation could not be saved.";
					}
				}
			}
		}				
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
	span#engphrase {
			padding-left:120px;
			font-family:Arial, Helvetica;
			font-size:80%;
		}
	
	input#transphrase {
		width:300px;
	}
	
/*	fieldset {
		border:1px solid #ccc;
		width:450px;
		margin:20px 0;
	}
		fieldset legend {
			font-family:Arial, Helvetica;
			font-size:90%;
		}*/
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Translations';
	$pageTitle = 'Translations';
	
	$pageClass = 'translations';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>
      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>	


<? if ($action == 'translations') { ?>

	<h2>Amend Translations</h2>
	<p>In this section you can alter the translations of words & phrases used around the site.
	The English versions will always stay the same.</p>
    <form id="treeline" action="/treeline/languages/?action=translations<?php if ($DEBUG) echo '&debug'?>" method="post">
	<fieldset>
		<legend>Select phrase &amp; language</legend>
			<label for="phrase">Phrase:</label>
			<select name="phrase" id="phrase">
				<? 
				$query = "SELECT label_id id,longname FROM labels_translations WHERE lang='en' ORDER BY longname";
				if( $results = $db->get_results( $query ) ){
					foreach( $results as $item ){ ?>
						<option value="<?= $item->id ?>"<? if($_POST['phrase']==$item->id){ echo ' selected="selected"'; } ?>><?= $item->longname ?></option>
					<? }
				}
				?>
			</select><br />
			<label for="translang">Language</label>
			<?= $page->drawSelectLanguages('translang',$_POST['translang'],true,'all',array('en')) ?><br />	

			<fieldset class="buttons">
				<button type="submit" class="submit">Submit</button>
            </fieldset>
	</fieldset>
	</form>
	<? if( $phrase>'' && $translang>'' ){ ?>
	<form id="treeline" action="/treeline/languages/?action=translations<? if ($DEBUG) echo '&debug'?>" method="post">
    	<fieldset>
		<input type="hidden" name="action" value="<?=$action?>" />
		<input type="hidden" name="phrase" value="<?=$phrase?>" />
		<input type="hidden" name="translang" value="<?=$translang?>" />
		
			<legend>Amend Translation</legend>
			<? 
			$query = "SELECT * FROM labels_translations WHERE label_id='".$phrase."' AND (lang='en' OR lang='".$translang."') ORDER BY longname";

			if( $thisphrase = $db->get_results($query) ){ 
				foreach($thisphrase as $item){
					if($item->lang=='en'){
						$engphrase = $item->longname;
					}else{
						$transphrase_old = $item->longname;
					} ?>
			<? } }
			
			//if($db->num_rows==1){ ?>
            	<label id="engphrase">English:</label>
                <input type="text" disabled="disabled" value="<?= $engphrase ?>" /><br />
				<label for="transphrase">Translated phrase:</label>
				<input type="text" name="transphrase" id="transphrase" value="<? echo (!$transphrase) ? $transphrase_old : stripslashes($transphrase) ?>" /><br />
			<? //} ?>
				<fieldset class="buttons">
					<button type="submit" class="submit">Submit</button>
           		</fieldset>
		</fieldset>
    </form>
	<? } ?>
	

<? } ?>



        </div>
      </div>
    <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>