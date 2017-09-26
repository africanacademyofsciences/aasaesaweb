
<div class="main-content">
    <div class="container">


		<?php
        $jumboHTML = validateContent($jumbo->draw());
        //print "Got jumbo($jumboHTML)<br>\n";
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
    
		<div class="col-xs-12 col-sm-8" id="primarycontent">

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

				$commentHTML = $comment->draw($_GET['commentid']); 
				?>
				<div class="blog-container">
					<div class="summary">
						<div class="info">
							<p><?=$page->blog_date?></p>
						</div>
                        
                        <?php
						if ($comment->getCount()) {
							?>
                            <div class="info">
                                <p><?=$comment->getCount()?><span> Comment<?=($comment->getCount()==1?"":"s")?></span></p>
                            </div>
                            <?php
						}
						
                        include_once($_SERVER['DOCUMENT_ROOT']."/includes/snippets/blog-tags.inc.php");
                        ?>
					</div>
					
					<div class="content">
						
                        <?php
						if ($mode=="edit") {
							?>
                            <p class="instructions">This section is meant to allow you to include a top image on your blogs. It can be left blank if this image is not required.</p>
                            <?php
						}
						?>
                        <?=$content1->draw()?>
                        
                        <?php
						//print "page(".print_r($page, 1).")<br>\n";
						$query = "SELECT * FROM users WHERE id = ".$page->user_created_id;
						//print "$query<br>\n";
						$row = $db->get_row($query);
						//print "author(".print_r($row, 1).")<br>\n";
						if ($row->job || $row->organisation) {
							$author_job_org = '<br />';
							if ($row->job) {
								$author_job_org.= $row->job;
								if ($row->organisation) $author_job_org .= ", ";
							}
							if ($row->organisation) $author_job_org.= $row->organisation;
						}
						if ($row->portrait) {
							$author_image = pullimage($row->portrait);
							$author_image = '<div class="author-image valign" style="background-image:url(\''.$author_image.'\');"></div>'."\n";
						}
						?>
						<div class="author-info">
                        	<?=$author_image?>
							<p class="valign">by <strong><?=$page->user_created?></strong><?=$author_job_org?></p>
						</div>

						<?php
						echo $pagerHTML;
						echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
			
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
						echo $pagerHTML;
						
						// Add a comment				
						if ($page->getMode()!="edit" && $page->getComment() && $site->getConfig("setup_comments")==1) {
							include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/formAddComment.php"; 
						}
						echo $commentHTML; 
						?>
                   </div>
				</div>	
                <?php
            }
            ?>
            
        </div>
        
		<div class="sidebar col-xs-12 col-sm-4 col-md-3 col-md-offset-1" id="secondarycontent">
        	
            <?php 
			if ($mode=="edit") {
				?>
                <p>The blog search, blog calendar and blog tags panels will automatically be added to the top of this section</p>
                <?php
			}
			?>
            
            <!--PANELS-->
            <?=$panels->draw(array(), array(13))?>
        
        </div>
	</div>    
</div>
