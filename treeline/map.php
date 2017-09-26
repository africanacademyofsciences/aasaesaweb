<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	$css = array('forms','tables','worldmap'); // all CSS needed by this page
	$extraCSS = '
	
div.feedback {
	clear:both;
}

'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? ucwords($adminItemType).' : '.ucwords($action) : ucwords($adminItemType);
	$pageTitle = ($action) ? ucwords($adminItemType).' : '.ucwords($action) : ucwords($adminItemType);
	
	$pageClass = "map";

// Pretty last resorty but for now
//  back up the xml file every time the page is loaded until we can find the problem
$backup=$_SERVER['DOCUMENT_ROOT']."/_xml/backup/GetTree.".date("dmYhis", time()).".xml";
$source=$_SERVER['DOCUMENT_ROOT']."/_xml/GetTree.xml";
if (file_exists($source)) {
	//print "copy file to($source, $backup)<br>";
	copy ($source, $backup);
}
//else print "Source($source) does not exist<br>";

	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>
      <div id="primarycontent">
        <div id="primary_inner">

            <h3>World Map Manager</h3>
            <div id="worldmapmanager">
                <? include('./worldmap.php') ?>
            </div>
		
        </div>
      </div>
      
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>
