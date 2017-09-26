<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/help.class.php");
	$help = new Help();
	
	// variables needed for the page
	$section = read($_REQUEST,'section','');
	$orderBy = read($_REQUEST,'sort','title'); 
	$currentpage = read($_REQUEST,'page',1); 	
	//$perPage = read($_REQUEST,'show','');
	
	// set up page title
	
	// PAGE specific HTML settings
	
	$css = array(); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($section) ? 'Help and support : '.ucwords($section) : 'Help and support';
	$pageTitle = ($section) ? 'Help and support : '.ucwords($section) : 'Help and support';
	
	$pageClass = 'help';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>
      <div id="primarycontent" class="column">
        <div id="primary_inner">
          <p>Email: <a href="mailto:russell.jones@ichameleon.com">russell.jones@ichameleon.com</a></p>
          <hr />
            <h2>Common terms</h2>
            <?=$help->drawCommonTerms($orderBy, $currentPage)?>

            <h2>Common questions</h2>
            <?=$help->drawCommonQuestions($orderBy, $currentPage)?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>