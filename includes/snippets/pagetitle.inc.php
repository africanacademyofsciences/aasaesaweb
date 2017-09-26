<!--	Page title
====================================== 
<div class="container-fluid page-title">
	<div class="row">
		<div class="container">
			<div class="col-xs-12 col-sm-8">
				<div class="title-container" id="pagetitle">
					<h1><?=$pagetitle?></h1>
				</div>
			</div>
			<div class="col-sm-4 hidden-xs">
				<div class="sharing-buttons">
					<span class="st_facebook_large" displayText="Facebook"></span>
					<span class="st_twitter_large" displayText="Tweet"></span>
					<span class="st_linkedin_large" displayText="LinkedIn"></span>
					<span class="st_googleplus_large" displayText="Google +"></span>
				</div>
			
			</div>
		</div>
	</div>
</div>
-->
<?php
$pdfHTML .= '<h1>'.$pagetitle.'</h1>'."\n";
?>


<div class="container-fluid page-title">
 <div class="row">
  <div class="container">
   <div class="col-xs-12 col-md-8">
    <div class="title-container" id="pagetitle">
     <h1><?=$pagetitle?></h1>
	 <?php
		if ($site->id == 18)
		{
			//include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
		}
	 ?>
    </div>
   </div>
   <div class="col-md-4 hidden-xs hidden-sm sharing-button-holder">
   <?php
    if ($site->id == 18)
    {
	?>
	<div>
	<?php
    }
    else
    {
	?>
		<div class="sharing-buttons">
	<?php
    }
   ?>
     <span class="st_facebook_large" displayText="Facebook"></span>
     <span class="st_twitter_large" displayText="Tweet"></span>
     <span class="st_linkedin_large" displayText="LinkedIn"></span>
     <span class="st_googleplus_large" displayText="Google +"></span>
    </div>
   </div>
  </div>
 </div>
</div>