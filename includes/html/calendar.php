
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
                echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
    
                if ($mode=="edit") {
                    ?>
                    <h2>Jumbotron</h2>
                    <p>Add content here that you would like to appear centered in a larger font at the top of the page</p>
                    <?=$jumboHTML?>
                    <?php
                }
				
				//echo $calendar->drawMonth();
				echo $calendar->drawNews($eventpageguid);
				
				                
				/*
				?>
				<a name="comments"></a>
                <?php
    
				include_once($_SERVER['DOCUMENT_ROOT']."/includes/snippets/tags.inc.php");

				// Add a comment				
				if ($page->getMode()!="edit" && $page->getComment() && $site->getConfig("setup_comments")==1) {
					include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/formAddComment.php"; 
				}
				echo $commentHTML;
				*/
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
