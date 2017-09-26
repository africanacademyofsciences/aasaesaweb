<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/preference.class.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");
//include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");

$curPage = "digestedit";

// Choices for action: "create" or "edit".


//print "got sub name(".$subscriber->fullname.")<br>";

	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	$action = read($_REQUEST,'action','');
	//$action = "nowt";
	
	// PAGE specific HTML settings
		
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
	table select{width: auto;} table.mceEditor{width: 500px;}
	';
	
	$js = array('tiny_mce/tiny_mce_digests'); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Newsletter Digest: '.ucwords($action) : 'Newsletter Digest';
	$pageTitle = ($action) ? 'Newsletter Digest : '.ucwords($action) : 'Newsletter Digest';
	
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
  <div id="primary_inner">
    <?php if (isset($_GET['test']) && $_GET['test'] != 0){
			$feedback = 'success';
			$message = ($_GET['test']+0)." message(s) were sent";
	} else if (isset($_GET['success'])){
			$feedback = 'success';
			$message = "The digest will now be sent";
	} else {
			$feedback = 'error';
			$message = $_GET['message'];
	} 
	echo drawFeedback($feedback,$message);

	?>
	<?php if(( $action == "Events" || $action == "News"  || $action == "Opportunities" || $action == "All")){ ?>
	    <p><strong>We would recommend that you send a test digest email first.</strong> If you choose to send a test digest it will be emailed immediately.</p>
    <p>To send a test digest now, enter up to 10 email addresses in the box below, seperating them with either 
      semi-colons ";" or carriage-returns (don't use live subscriber email addresses for this). </p>
    <p>Alternatively, to send your newsletter digest now, click "send now".</p>
    <p>Newsletter digests are mailed out regularly during the day at the rate of approximately 500 every 5 minutes.</p>
    <form id="frmTestSend" method="post" action="/treeline/newsletters/digestproc.php">
      <fieldset>
	  <legend>Send/test <?= ($action == "All") ? '' : strtolower($action); ?> digest</legend>
      <input type="hidden" name="id" value="<?php echo $newsletter->id; ?>" />
      <label for="test_emails">Email list for Send a Test</label>
      <textarea name="nl_test_send_emails" id="nl_test_send_emails" rows="3" cols="5"><?php echo ($_POST['nl_test_send_emails']) ? $_POST['nl_test_send_emails'] : ''; ?></textarea><br />
      <?php if ($action != "All"){ ?>      
      <label for="digestContent">Custom Digest Text:</label>
	  <textarea id="copy" class="mceEditor required" name="digestContent" rows="20" cols="20"></textarea><br />
	  <label for="timeLength">Mail <?=strtolower($action)?> from the last:</label>
	  <select id="timeLength" name="timeLength">
		  <option value="1week">Week</option>
		  <option value="2weeks">2 Weeks</option>
		  <option value="3weeks">3 Weeks</option>
		  <option value="1month">Month</option>
		  <option value="2months">2 Months</option>
		  <option value="3months">3 Months</option>
		  <option value="4months">4 Months</option>
	  </select><br />
	  <?php } else if ($action == "All"){ //Send digest for all options ?> 
	  
	  <label for="digestContent">Custom Digest Text:</label>
	  <textarea id="copy" class="mceEditor required" name="digestContent" rows="20" cols="20"></textarea><br />
	  <label for="newsLength">Mail news from the last:</label>
	  <select id="newsLength" name="newsLength">
		  <option value="1week">Week</option>
		  <option value="2weeks">2 Weeks</option>
		  <option value="3weeks">3 Weeks</option>
		  <option value="1month">Month</option>
		  <option value="2months">2 Months</option>
		  <option value="3months">3 Months</option>
		  <option value="4months">4 Months</option>
	  </select><br />
	  
	  <label for="opportunityLength">Mail opportunities from the last:</label>
	  <select id="opportunityLength" name="opportunityLength">
		  <option value="1week">Week</option>
		  <option value="2weeks">2 Weeks</option>
		  <option value="3weeks">3 Weeks</option>
		  <option value="1month">Month</option>
		  <option value="2months">2 Months</option>
		  <option value="3months">3 Months</option>
		  <option value="4months">4 Months</option>
	  </select><br />
	    
	  <label for="eventLength">Mail events coming up in the next:</label>
	  <select id="eventLength" name="eventLength">
		  <option value="1week">Week</option>
		  <option value="2weeks">2 Weeks</option>
		  <option value="3weeks">3 Weeks</option>
		  <option value="1month">Month</option>
		  <option value="2months">2 Months</option>
		  <option value="3months">3 Months</option>
		  <option value="4months">4 Months</option>
	  </select><br />
	
	  <?php } ?>
	  
	  
	  <input type="hidden" name="action" value="<?php echo $action; ?>" />
      <input type="hidden" name="validate_nl_test_send_emails_optional" value="false" />
      <input type="hidden" name="validate_nl_test_send_emails_nicename" value="Email list for Send a Test" />
      <fieldset class="buttons">
      	<button type="submit" class="cancel" name="test_send" value="true">send a test</button>
      	<?php	if ($user->drawGroup()=="Superuser") {  ?>
      	<button type="submit" class="submit" name="send_now" value="true">send now</button>
      	<?php } ?>
      </fieldset>
      </fieldset>
    </form>
		<?php
	} else {
	?>
	
		<p>Digests can be used to send newsletter subscribers a summary of news or events 
		over a set amount of time. This will allow them to keep up to date with things without
		having to actually visit the website.</p>
		<p>Send a digest with:</p>
		<li><a href="?action=News">News</a></li>
		<li><a href="?action=Opportunities">Opportunities</a></li>
		<li><a href="?action=Events">Events</a></li>
        <li><a href="?action=All">All</a></li>
	<?php
		} 
	?>
  </div>
</div>
<script type="text/javascript" src="/treeline/includes/tiny_mce/tiny_mce.js"></script>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>