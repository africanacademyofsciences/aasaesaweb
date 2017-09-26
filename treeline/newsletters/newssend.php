<?php

	ini_set("display_errors", 1);
	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");

	include "includes/email/htmlMimeMail.php";

	$def_admin_email = "phil.redclift@ichameleon.com";
	
	$nid = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "id", 0)+0;
	$mode = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "mode", 0);

	$newsletter = new newsletter($nid);
	
	$action = read($_REQUEST,'action','');
	$mode = read($_REQUEST, "mode", "");
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = "error";
	
	// PAGE specific HTML settings
	
	// Posting options
	if ($_SERVER['REQUEST_METHOD']=="POST") {
	
		$action = $_POST['action'];
		//print "post(".print_r($_POST, true).") got action($action)<br>\n";
		
		// Send a test
		if($action == "send-a-test"){
		
			$page=new Page();
			//$newsletter = new newsletter($nid);
			$newsletter->labels=$page->getTranslations($_SESSION['treeline_user_site_id'], $_SESSION['treeline_user_language']);
		
			if ($newsletter->validate()) {
				
				// Prep Email texts
				$strHTMLEmail = $newsletter->getHTMLEmail();
				$strHTMLPlain = $newsletter->getPlainEmail();
		
				// Strip junk, leaving just semi-colon seperated email addys
				$strEmailList = str_replace(" ", "", $_POST["nl_test_send_emails"]);
				$strEmailList = str_replace("\r", "", $strEmailList);
				$strEmailList = str_replace("\n", ";", $strEmailList);
		
				$emails = explode(";", $strEmailList);
				$sent = 0;
		
				$mail = new htmlMimeMail();
		
				for($n = 0; ($n < count($emails)) && ($sent < 10); $n++){
		
					$e = $emails[$n];
		
					if(trim($e) != ""){
					
						if($newsletter->validateEmail($e)){
							//$strHTMLEmail = $newsletter->setUnsubscribe($strHTMLEmail, $e);
		
							// Email checks out, send the test mail
							$newsletter_from = "testnews@".$site->name.".com";
							$mail->setFrom($newsletter_from);
							$mail->setReturnPath($newsletter_from);
							$mail->setSubject('Test: '.$newsletter->subject);
			
							$mail->setHtml($strHTMLEmail, $strHTMLPlain, null);
							$mail->is_built = false;
							$result = $mail->send(array($e));
							//print "got mail result($result)<br>"; exit();
							$sent++;
							
							// Should log this from/to/nid/date/TEST somewhere???
							$message[]=$page->drawLabel("tl_nl_test_sendto", "Test sent to")." - $e";
						}
					}
				}
			}
			else $message[]=$page->drawLabel("tl_nl_test_inv", "Newsletter appears to be invalid");
		}
				
		else if($_POST["send_now"]){

			$message[]="Setting up live run...";
			
			$newsletter = new newsletter($nid);
		
			if($newsletter->validate()){
		
		
				// Take all email addresses from the mailing list and add them to the outbox table
				// First add all preference related member ids....
				$strSQL = "INSERT INTO newsletter_outbox (newsletter_id, member_id)
					SELECT '".$newsletter->id."', m.member_id 
					FROM newsletter_user_preferences nup
					LEFT JOIN members m ON m.member_id = nup.member_id 
					WHERE nup.preference_id in ( 
						SELECT preference_id
						FROM newsletter_send_preferences nsp
						WHERE newsletter_id = '".$newsletter->id."'
						)
					GROUP BY m.email ";
				//print "$strSQL<br>";
				$db->query($strSQL);
		
				// Next we need to add any event subscribers if there are any.
				$query = "INSERT INTO newsletter_outbox(newsletter_id, email)
					SELECT ".$newsletter->id.", email
					FROM event_entry ee 
					LEFT JOIN event_entry_data eed ON ee.id=eed.entry_id
					WHERE ee.registered>0 AND eed.email IS NOT NULL 
					AND ee.event_guid in (
						SELECT event_id 
						FROM newsletter_send_preferences nsp
						WHERE newsletter_id=".($newsletter->id+0)."
						AND event_id IS NOT NULL
						)
					GROUP by eed.email ";
				$message[]=$query;
				$db->query($query);
		
				// Update the Newsletter record so it no longer shows as sendable
				$query =  "UPDATE newsletter " .
					"SET done = 1, " .
					"send_date = '" .date("Y/m/d H:i:s", time()). "' " .
					"WHERE id = " . $newsletter->id;
				$message[]=$query;
				$db->query($query);
				
				$message[]=$page->drawLabel("tl_nl_send_sent", "Newsletter has been scheduled to send");
			}
			else $message[]=$page->drawLabel("tl_nl_test_inv", "Newsletter appears to be invalid");
		}
	
	}
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $pagetitle = ucfirst($mode).' Newsletter';
	$pageClass = 'newsletters';
	
	//print "got action($action) mode($mode) <br>\n";
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
  <div id="primary_inner">
	<?php 
	
	echo drawFeedback($feedback, $message);
	 	
	if($newsletter->id){

		// Actually send a real newsletter
		if ($user->drawGroup()=="Superuser" && $mode=="send" && $newsletter->status!="S") {		
			$page_html.='
				<p>Newsletters are mailed out regularly during the day at the rate of approximately 500 every 5 minutes.</p>
				<p>If you do not want to send your newsletter now, you can come back and send this newsletter any time you prefer.</p>
				<p>We highly recommend you thoroughly test your newsletter before you send it</p>

				<form id="frmTestSend" method="post">
				<fieldset>
					<input type="hidden" name="mode" value="'.$mode.'" />
					<input type="hidden" name="id" value="'.$newsletter->id.'" />
					<input type="submit" class="submit" value="Send now" />
					<input type="hidden" name="action" value="send-now" />
				</fieldset>
				</form>
			';
		}
		// Testing send  process
		else {
		
			if ($newsletter->status=="S") $page_title = $page->drawLabel("tl_nl_test_ftitle", 'Follow up emails'); 
			else {
		
				$query= "SELECT m.member_id, 0 as entry_id
				  FROM newsletter_user_preferences nup 
				  LEFT JOIN members m ON m.member_id = nup.member_id 
				  WHERE nup.preference_id in ( 
					  SELECT preference_id 
					  FROM newsletter_send_preferences nsp 
					  WHERE newsletter_id = '".$newsletter->id."'
					  ) 
				  GROUP BY m.email ";
				 $query.="UNION 
					SELECT ee.member_id, ee.id AS entry_id FROM event_entry ee
					LEFT JOIN event_entry_data eed ON ee.id=eed.entry_id
					WHERE eed.email IS NOT NULL 
					AND ee.registered>0 AND ee.event_guid in (
						SELECT event_id 
						FROM newsletter_send_preferences nsp
						WHERE newsletter_id=".($newsletter->id+0)."
						AND event_id IS NOT NULL
						)
					GROUP by eed.email";
				//print "$query<br>";
				$db->get_var($query);
				$n_count=$db->num_rows;
	
				$page_title = $page->drawLabel("tl_nl_test_woodsend", 'This newsletter would be sent to ').' <b>'.($n_count+0).'</b> '.$page->drawGeneric("subscribers");
			}
        
			$page_html = '
			<p>'.$page->drawLabel("tl_nl_test_msg1", "We recommend that you test your email to as many different mail clients as possible. Test messages are sent straight away but can take a few minutes to appear in your inbox").'</p>
			<p>'.$page->drawLabel("tl_nl_test_msg2", "To send a test mailing now, enter up to 10 email addresses in the box below, seperate them with either a ; or a new line").'</p>
			';	
	    
		
			$page_html .= '
			<form id="frmTestSend" method="post">
			<fieldset>
				<input type="hidden" name="mode" value="'.$mode.'" />
				<input type="hidden" name="id" value="'.$newsletter->id.'" />
				<label for="test_emails">'.$page->drawLabel("tl_nl_test_list", "Email list to test").'</label>
				<textarea name="nl_test_send_emails" id="nl_test_send_emails" rows="3" cols="5">'.($_POST['nl_test_send_emails']?$_POST['nl_test_send_emails']:$def_admin_email).'</textarea>
				<br />
				<input type="hidden" name="validate_nl_test_send_emails_optional" value="false" />
				<input type="hidden" name="validate_nl_test_send_emails_nicename" value="Email list for Send a Test" />
				';
			
			if ($newsletter->status!="S") {
				$page_html.='
					<fieldset class="buttons">
						<a class="button" href="'.$site->link.'newsletter/?id='.$newsletter->id.'&amp;mode=preview;" target="_blank" title="'.$page->drawLabel("tl_nl_test_opennew", "Opens in a new window").'">'.$page->drawLabel("tl_nl_test_prehtml", "Preview email as html").'</a>
					</fieldset>
					<fieldset class="buttons">
						<a class="button" href="'.$site->link.'newsletter/?id='.$newsletter->id.'&amp;s=plain&amp;mode=preview" target="_blank" title="'.$page->drawLabel("tl_nl_test_opennew", "Opens in a new window").'">'.$page->drawLabel("tl_nl_test_preplain", "Preview email as text").'</a>
					</fieldset>
					';
			}

			$page_html.='
				<fieldset class="buttons">
					<input type="submit" class="cancel" value="'.$page->drawLabel("tl_nl_test_send", "Send a test").'" />
					<input type="hidden" name="action" value="send-a-test" />
				</fieldset>
			</form>
			';
		}
		
		echo treelineBox($page_html, $page_title, "blue");
    } 
	?>
    
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>
