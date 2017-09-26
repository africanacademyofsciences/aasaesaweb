<?php


	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/pledge.class.php');

	//print "SERVER(".print_r($_SERVER, true).")<br>\n";
	//print "ENV(".print_r($_ENV, true).")<br>\n";
	//print "SESSION(".print_r($_SESSION, true).")<br>\n";
	//print "REQ(".print_r($_REQUEST, true).")<br>\n";

	//print "hit page.php(".time().")<br>\n";
	//$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	ini_set("display_errors", true);

	// Content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);

	// Tags
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);

	$feedback="error";
	$message = array();
	
	$show = read($_POST,'shownews',false);
	$show = ($show>'') ? 1 : false ;
	
	$global_currency = "&pound;";
	$global_currency = "$";
	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	
	$pledge = new Pledge($page->getGUID());
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		//$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		$redirect = true;
		
		if ($_POST['post_action']) $action = $_POST['post_action'];
		//print "<!-- got post action ($action) -->\n";
		if ($action == 'Save changes' || $action=="Save") {

			//print "post(".print_r($_POST, true).")<br>\n";
			$content->save();
			$page->save(true);
			$panels->save();
			
			if ($_POST['target'] || $_POST['researcher']) {
				$prjtarget = $_POST['target']+0;
				$prjresearcher = $_POST['researcher']+0;
				$query = "UPDATE pages SET target=$prjtarget, member_id = $prjresearcher WHERE guid = '".$page->getGUID()."'";
				$db->query($query);
			}
			
			
			// Intelligent link panels
			//$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
			
			// Content is saved so redirect the user
			$feedback = 'feedback=success&message='.urlencode($page->getLabel("tl_pedit_msg_saved", true));
			
			//$author_redirect = '/treeline/pages/?action=edit&'.$feedback;
			$author_redirect = "/treeline/pages/?action=edit";
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			//$publish_redirect .= '&'.$feedback;

			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");

			// For users with authorisation go to the publish option				
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				//print "would go to $publish_redirect<br>";
				$redirectURL = $publish_redirect; // show them the publish option
			}
			// Just go back to the page edit listing. 
			else $redirectURL = $author_redirect; 
			
			if ($redirect && $action=='Save changes') redirect($redirectURL);
				
		}


		// Discard changes was pressed
		else if ($action == 'Discard changes') {
			// We have to manually release the page here as we are not saving the page.
			$page->releaseLock($_SESSION['treeline_user_id']);			
			if ($redirect) redirect ('/treeline/pages/?action=edit&feedback=notice&message='.urlencode($page->getLabel("tl_pedit_err_nosave", true)));
		}
		
		// Delete a panel from the panel list
		else if ($action=="Delete") {
			if (is_object($panels)) $page->deletePanel($panels, $_POST['treeline_panels'], $_POST['delete_panel']);
		}
		
		// Login to members area
		else if ($action=="login") {
			include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/login.class.php');
			$login = new MemberLogin();
			$message = $login->logIn();
		}

		else if ($action == "process-form") {
			//$message[]="Form processing ...";
			$form = new Form($_POST['fid']);
			$data_id = $form->processData($_POST, $_POST['data_id'], $_POST['member_id']);
			if (count($form->errormsg)) {
				foreach ($form->errormsg as $tmp) {
					$message[]=$tmp;
				}
			}
			else {
				$hide_submit_button_just_this_once = true;
				$message[]=($form->successmsg?$form->successmsg:"Your information has been saved");
				//$message[]="Hide submit(".$hide_submit_button_just_this_once.")";
				$feedback="success";
				$form->sendData($data_id);
				// We are removing the post data instead
				$hide_submit_button_just_this_once = false;
			}
			unset($form);
		}
		
		else if ($_POST['type_id']>0) {
			
			if ($_POST['type_id']==1) {
				// Pledge money
				$amount = number_format($_POST['pledge']+0, 2, ".", "");
				if ($amount>0) {
					if ($pledge->add($_SESSION['member_id'], $page->member_id, $amount)) {
						$message[] = "Thank you for you generous pledge of $global_currency".$amount." to this project";
						$feedback = "success";
						$_POST['pledge'] = 0;
					}
					else $message[] = "Failed to add pledge";
				}
				else $message[] = "You must enter an amount to make a financial pledge";
			}
			else {
				// Pledge something else
				//$message[] = "This is not a financial pledge [".$_POST['type_id']."]";
				if ($pledge->add($_SESSION['member_id'], $page->member_id)) {
					$message[] = "Thank you for you generous offer of support for this project";
					$feedback = "success";
				}
				else $message[] = "Failed to add pledge";
			}
			
		}
	}
	else {
		
		
	}
	
	$member = new Member();
	$member->loadById($page->member_id);
	

	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page'); // all attached stylesheets
	if($page->style != NULL && $mode=="edit") $css[] = $page->style;
	//print "Style(".$page->style.") <br>\n";
	$primarycols = 8;
	if ($page->style=="1col") $primarycols = 12;
	


	// Are comments allowed on this page?
	$commentHTML = '';
	$comment = new Comment($page->getGUID());
	if($page->getComment() && $site->getConfig("setup_comments")) {
		$css[]="comment";
		$commentHTML = $comment->draw($_GET['commentid']); 
	}

	$extraCSS = '';
	
	$js = array("swipe"); // all atatched JS behaviours
	if($mode == 'edit'){
		//$js[] = 'showHideDetails';
		$toolmode="";
		$jsBottom[] = 'styleSwitcher';

		//$extraJSbottom .= '	CKEDITOR.replace(\'treeline_news1\', { toolbar : \'contentPanel\', height: \'60px\' });	';

		$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_content\', { toolbar : \'contentStandard\' });
			CKEDITOR.replace(\'treeline_content1\', { toolbar : \'contentPanel\', height: \'250px\' });
			CKEDITOR.replace(\'treeline_content2\', { toolbar : \'contentPanel\', height: \'250px\' });
			CKEDITOR.replace(\'treeline_content3\', { toolbar : \'contentPanel\', height: \'250px\' });
            CKEDITOR.replace(\'treeline_jumbo\', { toolbar : \'contentStandard\' });
		';

	}
	$extraJS = ' '; // etxra page specific  JS behaviours

	$pagetitle = $page->getTitle();

	//$mceFiles = array("content", "headerimage");
	
	$pdfHTML = '';
	ob_start();
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

?>
<div class="main-content">
    <div class="container">
		<div class="col-xs-12 col-sm-<?=$primarycols?> col-lg-<?=$primarycols?>" id="primarycontent">

			<?php 
            echo drawFeedback($feedback, $message);
            
            //print "private(".$page->private.") mem type(".$_SESSION['member_type'].")<br>\n";
            if ($page->private && $page->private!=($_SESSION['member_type']+0) && $mode!="edit" && $mode!="preview") {
                ?>
                <p>This page is only available to logged in members. To access this information please log in below.</p>
                <?php
                echo drawFeedback("notice", $message);
                include $_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php";
            }
            else {

				if ($mode=="edit" || $mode=="preview") {
					?>
                    <h3>Project pledges</h3>
                    <table class="table" id="pledge-table">
                    	<tr>
                        	<td>Organisation</td>
                            <td>Date</td>
                            <td align="right">Amount</td>
                        </tr>
                        <?php
						foreach ($pledge->pledges as $item) {
							?>
                            <tr>
                            <td><?=$item['org']?></td>
                            <td><?=$item['date']?></td>
                            <td align="right"><?=$pledge->currency?><?=$item['amount']?></td>
                            </tr>
                            <?php
						}
						?>
                        <tr>
                        <td colspan="2"><strong>Total pledges</strong></td>
                        <td align="right"><strong><?=$pledge->currency?><?=number_format($pledge->total, 2, ".", "")?></strong></td>
                        </tr>
                    </table>
                    <?php
				}
				if ($mode=="edit") {
					$query = "SELECT CONCAT(firstname, ' ', surname) AS name, m.member_id 
						FROM members m INNER JOIN member_access ma on ma.member_id = m.member_id
						WHERE ma.`status`='A' AND ma.type_id = 5
						";
					if ($results = $db->get_results($query)) {
						foreach ($results as $result) {
							//print "Page(".$page->member_id.") id(".$result->member_id.")<br>\n";
							$selected = $page->member_id == $result->member_id?' selected="selected"':"";
							$researcherList .= '<option value="'.$result->member_id.'"'.$selected.'>'.$result->name.'</option>'."\n";
						}
					}
					?>
                    <h3>Project data</h3>
                    <div class="form-group">
                    <label for="f_target">Target</label>
                    <input class="form-control" type="text" name="target" id="f_target" value="<?=number_format($page->target, 2, ".", "")?>" />
                    </div>
                    <div class="form-group">
                    <label for="f_member">Researcher</label>
                    <select name="researcher" class="form-control">
                    	<option value="0">Select researcher</option>
                        <?=$researcherList?>
                    </select>
                    </div>
                    <h3>Proposal</h3>
                    <?php
				}
                $contentHTML =  highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
				echo $contentHTML;
				$pdfHTML .= $contentHTML;

            }
            ?>
            
        </div>

		<div class="sidebar col-xs-12 col-sm-4 col-md-3 col-md-offset-1" id="secondarycontent">
        
            <!--PANELS-->
            <?php
            echo $panels->draw(array(), array(13));
			?>
            
			<!-- Project details -->
			<?php
			// Add project details info
			ob_start();
			$global_panelmode="pdf";
			include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/panels/panel.researcher-profile.php");
			$pdfHTML .= ob_get_contents();
			ob_end_clean();
			
            // INTELLIGENT LINKS PANEL
            /*
            $tag_mode = $tags->getMode();	// Save current tag mode
            $tags->setMode("view");
            echo $tags->drawRelatedContentLinks($pageGUID);
            $tags->setMode($tag_mode);
            */
            ?>
                    
        </div>
        
	</div>    
</div>

<?php
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');
	
	$html = ob_get_contents();
	ob_end_clean();
	if (!isset($_GET['pdf'])) echo $html;
	else {
		$pdfHTML = '<html>
<head>
</head>
<body>
	'.$pdfHTML.'
</body>
</html>
';	
		//print "Gen PDF<br>\n";
		$pdffile = "pdftest.pdf";
		$filetype ="PDF";
		$filename=$_SERVER['DOCUMENT_ROOT']."/silo/files/".$pdffile;
		generatePDF($pdfHTML, $pdffile);

		// required for IE, otherwise Content-disposition is ignored
		if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
		
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers 
		header("Content-Type: ".$filetype);
		// change, added quotes to allow spaces in filenames, by Rajkumar Singh
		header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($filename));
		readfile("$filename");
		//file_get_contents($_SERVER['DOCUMENT_ROOT']."/silo/pdf/pdftest.pdf");
	}
?>