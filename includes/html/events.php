
<?php
//print "<!-- events($mode) -->\n";
//ini_set("display_errors", true);

?>

<div class="main-content">

    <div class="container">


		<?php
        $jumboHTML = validateContent($jumbo->draw());
        //print "Got jumbo($jumboHTML) mode($mode)<br>\n";
        if ($mode!="edit" && $jumboHTML && $jumboHTML!='<p></p>') {
            ?>
            <div class="col-xs-12">
                <div class="jumbotron wow fadeInUp" data-wow-delay="0.2s">
                    <?=$jumboHTML?>
                </div>
            </div>
            <?php
        }
        ?>
    
		<div class="col-xs-12 col-sm-<?=$primarycols?> col-lg-<?=$primarycols?> mtop20" id="primarycontent">

			<?php 
            echo drawFeedback($feedback, $message);
            
            if ($page->private && !$_SESSION['member_id'] && $mode!="edit" && $mode!="preview") {
                ?>
                <p>This page is only available to logged in members. To access this information please log in below.</p>
                <?php
                include $_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php";
            }
            else if ($register_now) { 
                include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_form.php");
            }
            else if (isset($_GET['showcomments']) && $site->getConfig('setup_comments')==1 && ($comment->count>0 || ($mode=="preview" && $_GET['commentid']>0)))	{ 
                echo $comment->draw($_GET['commentid']); 
            }
            else {
                echo $event->drawEventInfo();
                echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
    			//echo $event->drawBookingButton();
				
                if ($mode=="edit") {
                    ?>
                    <h2>Jumbotron</h2>
                    <p>Add content here that you would like to appear centered in a larger font at the top of the page</p>
                    <?=$jumboHTML?>
                    <?php
                }

				?>
				<a name="comments"></a>
                <?php
    
				include_once($_SERVER['DOCUMENT_ROOT']."/includes/snippets/tags.inc.php");

				// Add a comment				
				if ($page->getMode()!="edit" && $page->getComment() && $site->getConfig("setup_comments")==1) {
					include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/formAddComment.php"; 
				}
				echo $commentHTML;
            }

			
            ?>
            
        </div>

		<div class="sidebar col-xs-12 col-sm-4 col-md-3 col-md-offset-1" id="secondarycontent">
        
            <!--PANELS-->
            <?=$panels->draw(array(), array(13))?>
        
            <!--INTELLIGENT LINKS PANEL -->
            <?php
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
