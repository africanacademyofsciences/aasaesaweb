<?php

	//ini_set("display_errors", 1);

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	//$mode = read($_REQUEST,'mode','');

	// Find out how many panels there are in the database and keep an idea about which panels are valid for this page.
	$query="select * from content where parent='{$page->getGUID()}' 
		and revision_id".(($mode=="edit" || $mode=="preview")?">":"")."=0 and placeholder like 'landing%' 
		group by guid 
		order by cast(substr(placeholder,8) as unsigned), revision_id desc";
	//print "$query<br>";
	$no_panels=0;$last_panel=0;
	if ($results=$db->get_results($query)) {
		foreach ($results as $result) {
			$apanels[$no_panels]['id']=$result->placeholder;
			$no_panels++;
			$last_panel=substr($result->placeholder,7); // Strip landing from the start of the string
			//print "this was panel ($last_panel)<br>";
		}
	}
	//print "got $no_panels panels last($last_panel)<br>";

	// Content
	/*
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);
	*/
	$landing1 = new HTMLPlaceholder();
	$landing1->load($page->getGUID(), 'infopanel1');
	$landing1->setMode($page->getMode());
	/*
	$landing2 = new HTMLPlaceholder();
	$landing2->load($page->getGUID(), 'infopanel2');
	$landing2->setMode($page->getMode());
	*/
	
	// Tags
	//$tags = new Tags();
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());
	
	// BLOCK 1 - Blocks 1 - 4 are the small panels at the base of the screen
	//print "load panels (0 -> $no_panels)<br>";
	for ($i=0; $i<$no_panels; $i++) {
		$apanels[$i]['panel'] = new HTMLPlaceholder();
		$apanels[$i]['panel']->load($pageGUID, $apanels[$i]['id']);
		$apanels[$i]['panel']->setMode($page->getMode());
	}
	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		//print "POST(".print_r($_POST, true).")<br>\n";
		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		if ($_POST['post_action']) $action = $_POST['post_action'];
	
		if ($action == 'Save changes') {
			//$content->save();
			$landing1->save();
			$page->save($_POST['f_expanels']>0?false:true);

			//print "about to save panels(0 -> $no_panels)<br>";
			for ($i=0; $i<$no_panels; $i++) {
				//print "saving panel {$apanels[$i]['id']}<br>";
				$apanels[$i]['panel']->save();
			}
			
			
			// Do we need to create some new blank panels?
			if ($_POST['f_expanels']>0) {
				//print "create another (".$_POST['f_expanels'].") panels from ($no_panels) to (".($no_panels+$_POST['f_expanels']).")<br>";
				for ($i=0; $i<($_POST['f_expanels']); $i++) {
					$panel_no=$no_panels+$i;
					$next_panel_id=$last_panel+$i+1;
					//print "create new panel at pos(".$panel_no.") landing".($next_panel_id)."<br>";
					$apanels[$panel_no]['id']="landing".$next_panel_id;
					$apanels[$panel_no]['panel'] = new HTMLPlaceholder();
					$apanels[$panel_no]['panel']->load($pageGUID, $apanels[$panel_no]['id']);
					$apanels[$panel_no]['panel']->setMode($page->getMode());
					$apanels[$panel_no]['panel']->save();	// explicit save here as may be loading previous content??
				}
				$no_panels+=$_POST['f_expanels'];
				$last_panel+=$_POST['f_expanels'];
				//print "we now have $no_panels panels?<br>";
			} 
			// If not delete any blank ones.
			else {
				for ($i=($no_panels-1); $i>=0; $i--) {
					// If this panel has no content remove it compeletely
					//print "check if panel($i) has any content<br>";
					if  (
						$_POST['treeline_'.$apanels[$i]['id']]=='' ||
						$_POST['treeline_'.$apanels[$i]['id']]=='delete' ||
						$_POST['treeline_'.$apanels[$i]['id']]=='<p>delete</p>' || 
						!$_POST['treeline_'.$apanels[$i]['id']]
						) {
						//print "deleting panel {$apanels[$i]['id']}<br>";
						$apanels[$i]['panel']->delete();
						$no_panels--;
					}
					else {
						//break;
					}
				}

				// Content is saved so redirect the user
				$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
				
				$referer .= $feedback;
				$referer .= '&action=edit';
				
				$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
				//$publish_redirect .= '&'.$feedback;
				
				include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
				if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
					redirect($publish_redirect); // show them the publish option
					//print "would redirect to($publish_redirect)<br>";
				} else{
					redirect($referer); // otherwise take the user back to the edit pages page
				}

			}
		}

		// Delete a panel from the panel list
		else if ($action=="Delete") {
			if (is_object($panels)) $page->deletePanel($panels, $_POST['treeline_panels'], $_POST['delete_panel']);
		}

		else if ($action == 'Discard changes') {			
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$referer .= 'action='.$page->getMode().'&'.createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}

		// Login to members area
		else if ($action=="login") {
			include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/login.class.php');
			$login = new MemberLogin();
			$message = $login->logIn();
		}
		
	}
	
	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	// Extra preview mode stuff needed for landing pages.
	if ($mode=="preview" || strtolower($action)=="preview") {

		$landing1->setMode($mode);
		$landing1->setContent(str_replace('\"', '"', $landing1->draw()));
		//$landing2->setMode($mode);
		//$landing2->setContent(str_replace('\"', '"', $landing2->draw()));
		// Need to set the mode of all the little panels too ??
		for ($i=($no_panels-1); $i>=0; $i--) {
			$apanels[$i]['panel']->setMode($mode);
		}
	}
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page','forms','landing', "contact"); // all attached stylesheets
	if($page->style != NULL){
		//$css[] = $page->style;
	}
	
	$jsBottom = array("landing_equalheightblocks"); // all atatched JS behaviours
	if ($mode=="edit") {
		$toolmode="landing";
		$js[] = 'styleSwitcher';
	}

	if ($mode!="edit") $extraJSbottom = '
	
$(window).load(function()
{
	getHeights();
	
});

'; // etxra page specific  JS behaviours

	if ($mode=="edit") {
		if ($site->id != 18)
		{
			$extraJSbottom .= 'CKEDITOR.replace(\'treeline_infopanel1\', { toolbar : \'contentStandard\' });';
		}
		$extraJSbottom .= '
			CKEDITOR.replaceAll( function(textarea,config) {
				if (textarea.className!="MCElandingPanel") return false; //for only assign a class
				config.toolbar = \'contentPanel\';
				config.height = "250px";
			});	
		';
	}
	
	$pagetitle = $page->getTitle();

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

	
	if ($mode=="edit") {
		echo '<input type="hidden" name="no_panels" value="'.$no_panels.'" />';
	}

?>
<div class="main-content">

	<?php
	if ($site->id != 18)
	{
	?>
    
    <section class="landing-intro-section">
        <div class="container">

			<!--
            <i class="ion-ios-flame-outline circle bigger"></i>
            <h1>This is a landing page, ideal for <strong>programs</strong> or for other <strong>major intitiatives</strong>. Think of it as a "homepage" for the project.</h1>
			-->
            
            <?php 
            if ($page->private && !$_SESSION['member_id'] && $mode!="edit") ; 
			else if ($mode=="edit") {
				?>
                <h3 class="instructions">You can use this area to add an icon, a large H1 heading and a link or just add any content you choose</h3>
                <?php
				echo $landing1->draw();
			}
            else { 
                $landingHTML = $landing1->draw();
				$landingHTML = validateContent($landingHTML);
				if (preg_match("/i class=\"(.*?)\"(.*?)<h1>(.*?)<\/h1>(.*?)<a(.*?)href=\"(.*?)\"(.*?)>(.*?)<\/a>(.*)/ms", $landingHTML, $reg)) {
					//print "Matched (".print_r($reg, 1).") <br>\n";
					$landingHTML = '
					<i class="'.$reg[1].' circle bigger"></i>
					<h1>'.$reg[3].'</h1>
					';
					$getHTML = '
					<a class="get-involved" href="'.$reg[6].'">
						<div class="container">
							<h3>'.$reg[8].' <i class="ion-arrow-right-c hidden-xs"></i></h3>
						</div>
					</a>
					';
				}
                echo $landingHTML;
            } 
            ?>
        </div>
    </section>
    
	<?php
	}
	?>
	<?=$getHTML?>

    <div class="container">
        <div class="row" id="primarycontent">

            <?php if ($mode=="edit") { ?>
                <div id="expanels" style="margin-top:10px;float:left;clear:both;">
                    <p>Need more panels? Just enter then number of panels require here and save changes. Any empty panels will be removed when you save the page.</p> 
                    <p>
                    <label for="f_expanels">Add</label>
                    <input type="text" name="f_expanels" id="f_expanels" style="width:40px;margin-right:10px;" /><span>extra panels?</span>
                    </p>
                </div>
            <?php } ?>
    
            <?php 
            if ($page->private && !$_SESSION['member_id'] && $mode!="edit") {
                ?>
                <p>This page is only available to logged in members. To access this information please log in below.</p>
                <?php
                echo drawFeedback("notice", $message);
                include $_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php";
            } 
            else { 
                $level=0; // Added to avoid first lighter grey panel
                //print "got $no_panels panels to draw<br>";
                for($i=0; $i<$no_panels; $i++) {
                    //print "draw panel($i) id- ".$apanels[$i]['id']."<br>";
                    if ($i%3==0) {
                        $level++;
                        if ($i>0) $landing_html.='</div>';
                        $landing_html.='<div '.$exstyle.' id="landing-'.$level.'" class="landing-level">';
						
						if ($site->id == 18)
						{
							$landing_html.='<div class="col-xs-12 col-md-4"><div class="col-xs-12 landing-panel panel0">'.$apanels[$i]['panel']->draw("MCElandingPanel")."</div></div>";
						}
						else
						{
							$landing_html.='<div class="col-xs-12 col-md-4 landing-panel panel0">'.$apanels[$i]['panel']->draw("MCElandingPanel")."</div>";
						}
                    }
                    else 
					{
						if ($site->id == 18)
						{	
							$landing_html.='<div class="col-xs-12 col-md-4"><div class="col-xs-12 landing-panel panel'.($i%3).'">'.$apanels[$i]['panel']->draw("MCElandingPanel")."</div></div>";
						}
						else
						{
							$landing_html.='<div class="col-xs-12 col-md-4 landing-panel panel'.($i%3).'">'.$apanels[$i]['panel']->draw("MCElandingPanel")."</div>";
						}
					}
                }
                if ($landing_html) $landing_html.='</div>';
                echo $landing_html;
            }
            ?>
    
        </div>
	</div>     
    
</div>
   
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>

