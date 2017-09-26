
<div class="main-content">

	<?php
	$c1HTML = $content1->draw();
	$c2HTML = $content2->draw();
	$c3HTML = $content3->draw();
	//print "c1 content(".$content1->content.") drawn(".$content1->draw().")<br>\n";
	if ($c1HTML || $c2HTML || $c3HTML || $mode=="edit") {
		$altdisplaymode = $conent1->content || $conent2->content || $conent3->content;
		?>	
        <section class="page-intro">
            <div class="container">
                <?php 
                if ($mode=="edit") {
                    ?>
                    <div class="show-altcontent">
                        <p><a href="javascript:toggleAltcontent()">Show/Hide top content area</a></p>
                    </div>
                    <?php
                }
                ?>
                <div class="row" id="alt-content" style="display:<?=($altdisplaymode?"block":"none")?>;">
                    <div class="col-xs-12 col-sm-4 intro-box">
                        <?=$c1HTML?>
                    </div>
                    <div class="col-xs-12 col-sm-4 intro-box">
                        <?=$c2HTML?>
                    </div>
                    <div class="col-xs-12 col-sm-4 intro-box">
                        <?=$c3HTML?>
                    </div>
                </div>
            </div>
        </section>
		<?php
	}
	?>

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
                
                // Store receipt
                if ($name=="store-receipt") include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/store/store.receipt.php"; 
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
			
			<!--<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>-->
			<script>
			
					//Initially hide all elements
					for (i = 0; i <= 50; i++) {
						
						$('#colbox'+i).hide();
					}
					
					current = 0;
					function changeBox(id)
					{
						//Show
						if (current == 0)
						{
							//$('#short'+id).hide(100);
							$('#colbox'+id).show(1000);
							$('#button' + id).text('Hide');
							current = 1;
								
								
								
						}
						//Hide
						else
						{
							$('#colbox'+id).hide(500);
							//$('#short'+id).show(1000);
							$('#button' + id).text('Show');
							
							current = 0;
						}
					}
						
			 </script>          
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
