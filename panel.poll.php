<?php
	include_once($_SERVER['DOCUMENT_ROOT'] .'/treeline/includes/poll.class.php');
	
	//$content = new HTMLPlaceholder();
	//$content->load($page->getGUID(), 'panelcontent');

	$poll = new Poll($page->getGUID());
	$polldata = $poll->structure;
	
	$referer = read($_REQUEST,'referer','/treeline/');
	
	if ($page->getMode() == 'inline') {
		// If we've put the panel in 'list' mode -- ie, we've included the panel in a page we're editing -- we don't want to actively edit the content:
		//$content->setMode('view');
	} else {
		//$content->setMode($page->getMode());
	}
	
	// START DATABASE ATTRACTION
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if (strpos($referer,'?') > 0) {
			// If we've already got a querystring in the referring URL, we need to append the message onto it
			$referer .= '&';
		}
		else {
			// Otherwise, create a new querystring
			$referer .= '?';
		}
	
		if (read($_POST,'treeline','') == 'Save changes') {
			//$content->save();
			$page->save();
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to panel '<strong>". $page->drawTitle() ."</strong>'");
			$referer .= $feedback;		
			$publish_redirect = '/treeline/panels/?action=saved&guid='.$page->getGUID();
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		} 
		else if (read($_POST,'treeline','') == 'Discard changes') {
			$referer .= createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		} 
		else if (read($_POST,'poll_vote', 0) == $page->getGUID()){
		
			// RECORD A VOTE FOR THIS POLL
			//echo '<pre>'. print_r($_POST,true) .'</pre>';
			foreach( $_POST as $key => $value ){
				if( substr_count($key,'poll')>0 ){
					${$key} = $value;
				}
			}

			$totalVotes = (${'poll_totalvotes_'.$poll_answer}+1);
			
			if( $poll->addVote($poll_guid, $poll_answer, $totalVotes) ){
				
				//echo 'Thanks for voting!';
				$poll->loadByGUID($page->getGUID());
				$polldata = $poll->structure;
				$_SESSION['voted_poll_'.$page->getGUID()] = 1;
			}
		}
	}
	
	// END DATABASE INTERACTION
	
	// START PREVIEW/EDIT/VIEW MODE
	if ($page->getMode() != 'view' && $mode=="preview"){ // PREVIEW MODE: SHOW PROPER HTML
		//$page->loadByGUID($pageGUID);
		$pageClass = 'panel'; // used for CSS usually
		//$panelStyle = $page->getStyle();
	
		$css = array('page','3col','editMode'); // all attached stylesheets

		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Panel - <?=$page->drawTitle()?></title>
<style type="text/css">
    @import url("/style/reset.css");
    @import url("/style/yui_fonts.css");
    @import url("/style/global.css");
    @import url("/style/page.css");
    @import url("/style/3col.css");
    @import url("/style/scheme/palate01.css");
	<?php
	if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".$site->id.".css") && !$_SESSION['palate']) echo '@import url("/style/microsite/scheme'.$site->id.'.css");';
	else if ($palateNumber>1) echo '@import url("/style/scheme/palate'.(($palateNumber<10?"0":"").$palateNumber).'.css");';		
	if ($_GET['palate']>0) echo '@import url("/style/scheme/palate'.(($_GET['palate']<10?"0":"").$_GET['palate']).'.css");';
	?>
    body{background: #FFF;}
</style>
<?php //include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/commonCSS.inc.php'); ?>
<script type="text/javascript" src="/behaviour/jquery.js"></script>
<script type="text/javascript" src="/behaviour/jquery.corner.js"></script>
<script type="text/javascript">
$(window).load(function()
{
	// -------------------- Keep large images within the design --------------------
	
	$("#secondarycontent div.rounded").each(function()
	{	
		$(this).corner("7px");
	});

});
</script>
</head>
        
<body>	
<div id="breadcrumb">Breadcrumb trail</div>
<?php
	}
		
	if ($page->getMode() == 'edit') { // EDIT MODE: BASIC HTML
		
		?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Panel - <?=$page->drawTitle()?></title>
        <style type="text/css">
            @import url("/style/global.css");
            @import url("/treeline/style/editMode.css");
            body{background: #FFF;}
        </style>
        </head>
        
        <body>
        <?php
		
		// EDIT MODE: SHOW EDIT FORM
		
		// Should this be part of the treeline class?
		echo $page->drawPanelTinyMCE();

		// Now add the 'edit' form -- is there a neater way to do this, rathr than sandwiching the panel in form tags like this?
		// $dbqs = ($DEBUG)?'?debug':'';
		$dbqs = '';
		$action = $_SERVER['REQUEST_URI'].$dbqs;
		//$referer = read($_SERVER,'HTTP_REFERER','');
		// If we're debugging, apend ?debug to the form post
		echo '<form id="treeline_edit" action="'.$action.'" method="post">'."\n";
	
		// Add the panel toolbar:	
		echo $page->drawPanelToolbar();
		
		echo '<input type="hidden" name="action" value="save" />'."\n";
		echo '<input type="hidden" name="mode" value="'.$page->getMode().'" />'."\n";
		echo '<input type="hidden" name="referer" value="'.$referer.'" />'."\n";
		echo '<input type="hidden" name="title" value="'.$page->drawTitle().'" />'."\n";
		
		echo '<div style="width: 300px; margin:0 auto;">'."\n"; /* This should inherit from a stylesheet somewhere, I think */

	}	
	
	// PREVIEW MODE: SHOW DUMMY CONTENT 
	if ($page->getMode() != 'view' && $mode == 'preview'){  
	
		?>
        <div id="sidebar">
            <ul class="submemu">
                <li><a class="level-1" href="#">Sub-section</a></li>
                <li><a class="level-1" href="#">Sub-section</a></li>
                <li class="subon"><a class="level-2" href="#">Sub-section</a>
                <li><a class="level-1" href="#">Sub-section</a></li>
                <li><a class="level-1" href="#">Sub-section</a></li>
                <li><a class="level-1" href="#">Sub-section</a></li>
                <li><a class="level-1" href="#">Sub-section</a></li>
            </ul>
        </div>

        <div id="contentholder">
            <h1 class="pagetitle">Dummy content</h1>
            
            <div id="primarycontent">
                <p>This page doesn't have any content, its purpose is to highlight what the panel (left panels also appear on right - just for preview) will look like in context.</p>
                <p>By seeing this so-called 'Dummy content' next to your new panel you should get a better feeling as to how the panel will look on your website.</p>
            </div>
    
		    <div id="secondarycontent">
		<?php 
	} 


?>
<!-- SHOW ACTUAL PANEL CONTENT -->
<div class="panel rounded <?=$panelStyle?>">
    <h3><?= $page->drawTitle()?></h3>
    <?php 	
	if ($mode=="edit") {
		?>
        <p>Poll form disabled in edit mode</p>
        <?php
	}
	else if( 
		($_POST['poll_vote']!=$polldata['guid'] && $_SESSION['voted_poll_'.$page->getGUID()]!=1) ||
		$mode=="preview") 
		{ 
		?>
    	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="pollpanel" name="poll">
        <fieldset>
        	<input type="hidden" name="poll_vote" value="<?=$polldata['guid']?>" />
            <input type="hidden" name="poll_guid" value="<?=$polldata['guid']?>" />
        	
            <p class="pollquestion"><?= $polldata['question'] ?></p>

			<?php
			if (is_array($polldata['answers'])) {
				foreach( $polldata['answers'] as $id => $answer ){ 
				?>
                <div class="pollanswer">
                    <input type="radio" name="poll_answer" class="radio" id="poll_<?=$page->getGUID()?>_answer<?= $id ?>" value="<?= $id ?>" />
                    <label for="poll_<?=$page->getGUID()?>_answer<?= $id ?>"><?= $answer['text'] ?></label><br />
                </div>
                <input type="hidden" name="poll_totalvotes_<?= $id ?>" value="<?= $answer['votes']?>" />
                <?php
				}
            }
			?>
            <input type="submit" id="pollsubmit" value="Vote" <?=($mode=="preview"?'disabled="disabled"':"")?> />
        </fieldset>
        </form>
    	<? 
	} 
	else { 
		?>
        <p class="pollquestion"><?= $polldata['response'] ?></p>
        <ul class="pollresults">
        <?php
		if (is_array($polldata['answers'])) {
			foreach( $polldata['answers'] as $id => $answer ){ 
			?>
            <li<?= ( $answer['default']==1 ? ' class="poll_correct_answer"' : '' ) ?>>
                <span class="poll_answer"><?= $answer['text'] ?></span>
                <span class="poll_votes"><?= $answer['votes'] ?></span>
                <span class="poll_percentage"><?= $answer['percentage'] ?>%</span>
                <div class="poll_result_bar">
                    <span class="colourBar" style="width: <?=$answer['percentage']?>% !important"></span>
                    <span class="whiteBar" style="width: <?=(100 - $answer['percentage'])?>% !important"></span>
                </div>
            </li>
            <?php
			}
        }
		?>
        </ul>
		<? 
	} 
	?>

</div>
<?php
	if ($page->mode == 'edit') {
		// EDIT MDOE: CLOSE FORM AND BASIC HTML
		// Close the form [and containing div] we opened above:
	?>
        </div>
       </form>
      </body>
     </html>
<?php
}
	if ($page->mode == 'preview') {
		// PREVIEW MODE: CLOSE PROPER HTML
		// Close the form [and containing div] we opened above:
	?>
    </div>
        
<?php	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');	
	}
?>