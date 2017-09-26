<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
	// PAGE specific HTML settings
	
	$css = array(); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'About Treeline';
	$pageTitle = 'About Treeline';
	
	$pageClass = 'about';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
    <div id="primary_inner">
    	<?php
		$page_html = '
        <p>Treeline is a content management system created by Chameleon Interactive</abbr> for the express purpose of providing an easy-to-use yet affordable website management system for organisations.</p>
        <h3>Treeline updates</h3>
        <p>This version of Treeline has been customised specifically for your website but it is safe to say that this is Treeline version '.($CUR_TL_VER?$CUR_TL_VER:"3.0").'</p>
        <p>To find out when general updates are made to Treeline and when new versions are released subscribe to Chameleon Interactive\'s Treeline e-newsletter.</p>
        <h3>Previous versions of Treeline</h3>
        <p>Treeline 1.0 debuted in 2005. The first website to make use of Treeline was <a href="http://www.maginternational.org/">MAG (UK)</a> and that site ran Treeline 1.0 up to early 2008. Treeline is a very robust piece of software which allowed MAG to keep version 1.0 for all that time with no problems.</p>
        <p>Treeline 2.0 debuted in 2007.</p>
        <p>Treeline 3.0 came into use in 2008.</p>
		';
		echo treelineBox($page_html, "Treeline V".($CUR_TL_VER?$CUR_TL_VER:"3.0"), "blue");
		?>
    </div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>