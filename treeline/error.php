<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	$action = read($_REQUEST,'action','');
	//if (!$action) header("Location: /treeline/"); // only for action pages
	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','error');
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Error';
	$pageTitle = 'Error';
	
	$pageClass = 'error';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div class="container">

	<div id="primarycontent">

		<div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
          	<?php if($_GET['error']){
				switch($_GET['error']){
					default:
					case 404:
			?>
    		<p>The page you have requested, <em>http://<?=$site->properties['site_name']?><?=$_SERVER['REQUEST_URI']?></em>, is missing. </p>
			<h3>Why has this happened?</h3>
			<ul>
              <li>you may have mistyped the web address</li>
			  <li> a search engine may be listing an old web address</li>
			  <li>there may be an error on our part </li>
		  </ul>
			<?php
					break;
					case 500:
			?>
				<p>Our website has encountered an error and is not allowing you to view this page. This error has been reported and our technical team will be try to fix it as quickly as possible.</p>
			<?php		
					break;
					case 401:
			?>
				<p>Your are not authorised to view the page you have requested. </p>
			<?php			
					break;
					case 403:
			?>
				<p>403</p>
			<?php			
					break;
				}
			?>
				<script type="text/javascript">
					//urchinTracker('<?=$_GET['error'].$_SERVER['REQUEST_URI']?>');
				</script>
			<?php }  ?>
        </div>
      </div>
 
</div>

      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>