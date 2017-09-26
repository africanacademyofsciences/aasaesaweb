<?php

if( is_object($site) ){

	if($site->isLoaded){

		if($_POST){
			$access = $site->preview;
			//print "Posted u(".$_POST['preview_username'].") p() should b u(".$access['username'].") p()<br>\n";
			if( ($_POST['preview_username'] == $access['username']) && ($_POST['preview_password'] == $access['password']) ){
				$_SESSION['treeline_preview'] = $site->id;
					//print "would redirect(".$site->link.")<br>";
					redirect( $site->link );
			}
			else $message[] = 'Your username and password were not recognised';
		}
		
	}
	else $message[] = 'Site could not be loaded<br />';
}
else $message[] = "There was a problem loading this site.";

	
	$action = read($_REQUEST,'action','');
	//if (!$action) header("Location: /treeline/"); // only for action pages
	$guid = read($_REQUEST,'guid','');
		
	if (!$message) $message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	// PAGE specific HTML settings
	
	$css = array('forms','login'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Microsite Preview';
	$pageTitle = 'Microsite Preview';
	
	$pageClass = 'section';
	
	$noMenu = true;
	$noUserInfo = true;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">
<?php 
	echo drawFeedback($feedback,$message);
	
	?>	
    <h2>This site is not currently viewable to the public</h2>
    <form action="" method="post">
        <fieldset>
            <legend>Log-in to preview this site</legend>
            <label for="preview_username">Username:</label>
            <input name="preview_username" type="text" size="12" value="<?=$_POST['preview_username']?>" /><br />
            <label for="preview_password">Password:</label>
            <input name="preview_password" type="password" size="12" /><br />
            <input type="hidden" name="guid" value="<?= $siteroot ?>" />
            <input type="submit" class="submit" value="Log-in" style="float: right;" />	
        </fieldset>
    </form>

</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>