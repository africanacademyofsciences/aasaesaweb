<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	$action = read($_REQUEST,'action','');
	//if (!$action) header("Location: /treeline/"); // only for action pages
	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Section : '.ucwords($action) : 'Section';
	$pageTitle = ($action) ? 'Section : '.ucwords($action) : 'Section';
	
	$pageClass = 'section';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>