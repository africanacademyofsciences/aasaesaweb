<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
	// PAGE specific HTML settings
	
	$css = array(); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Terms and conditions';
	$pageTitle = 'Terms and conditions';
	
	$pageClass = 'terms';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
    <div id="primary_inner">
    	<?php
		$page_html = '
        <p>Treeline CMS is owned by Treeline Software Ltd.</p>
        <p>This version of Treeline CMS has been adapted for this specific website. Your purchase of this version of Treeline is valid for this website only. You are not permitted to use the source files of Treeline for independent projects without the express permission of Treeline Software Ltd.</p>
		';
		echo treelineBox($page_html, "Conditions of use", "blue");
		?>
    </div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>