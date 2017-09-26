<?

	//ini_set("display_errors", 1);
	
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/abuse.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/blogs.class.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');
	
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	$panels = new PanelsPlaceholder();
	$panels->guid = $page->getGUID();
	$panels->revision_id = 0;
	$panels->setMode("view");
	$panellist=array();
	
	$term = read($_REQUEST,'keywords','');
	//print "<!-- term($term) -->\n";
	if ($term) $global_searchresult	= true;
	$thispage = read($_GET,'page',1);

	$orderBy = read($_GET,'filter','date_added');
	$orderDir = read($_GET,'order','desc');	
	$tagFilter = read($_GET,'tag',false);
	
	$blogmode = read($_GET, "mode", "");

	$blogs = new Blog($_SESSION['member_id'], $term);
	$blogs->setPage($thispage);

	$bid = read($_POST?$_POST:$_GET, "bid", 0);

	$member = new Member();

	$feedback="error";
	

	$action=strtolower(read($_POST?$_POST:$_GET,"action",""));
	
	if ($_SERVER['REQUEST_METHOD']=="POST") {
		
		//print "got action($action)<br>\n";
		
		if ($action=="save blog" || $action=="post blog") {

			$message = $blogs->update();
			if (!$message) {
				unset($message);
				$blogmode='';
			
				// If posting then publish the blog too.....
				if ($action=="post blog") {
					if (!$blogs->publish()) $message[]="You do not have a currently publishable blog";
					else {
						$message[]="Your blog has been posted, it should now be visible to all users of the websites";
						$feedback="success";
					}
				}
				else {
					$message[]="You have saved a copy of your blog. You can work on this blog anytime you are logged in but it will not appear on the website until you press the 'Post blog' button";
					$feedback="notice";
				}
			}
		}
		else if ($action == "comment") {
			if ($_POST['bid']>0) {
				$blogs->loadBlog($_POST['bid']);
				if (!$blogs->addComment()) $message = $blogs->errmsg;
				else {
					$feedback="success";
					$message[]="Your comment has been added and the blog creator informed. Your comment will only appear on this site once it has been approved by the blogger";
				}
			}
			else $message[]="No blog ID passed to comment on";
		}
		else if ($action == "save-comments") {
			foreach ($_POST as $k=>$v) {
				//print "k($k) = v($v)<br>\n";
				if (substr($k,0,4)=="stat") {
					$comment_id = substr($k,4);
					if ($comment_id>0) {
						$query = "UPDATE blogs_comments SET `status`=".($v+0)." WHERE id=".$comment_id;
						$db->query($query);
						//print "$query<br>\n";
					}
				}
			}
		}
		
		else if ($action == "report") {		
			$email = $_POST['email'];
			if (!$email || !is_email($email)) $message[]="You have not entered a valid email address";
			else if (!$captcha->valid) $message = $captcha->errmsg;
			else if ($blogs->abuse->report($_POST['bid'], "blogs", $email)) {
				$message[]="An email has been sent to you containing details on how to complete your abuse report.";
				$feedback="success";
				$action = "";
			}
			else $message = $blogs->abuse->errmsg;
		}
		
	}
	else {
		// I don't know what this is for????
		if (isset($_GET['report']) && isset($_GET['confirm'])) {

			if ($blogs->abuse->report($_GET['bid'], "blogs")) {
				$message[]="An abuse report has been logged against this blog and the site administrators have been informed.";
				$feedback="success";
			}
			else $message[]="Failed to create abuse report";
			$_GET['bid']='';
		}
		// Abuse report has been confirmed
		// Actually submit an abuse report to owner.
		else if ($action=="report" && isset($_GET['id'])) {
			if ($blogs->abuse->suspendFromHistory($_GET['id'])) {
				$message[]="An abuse report has been logged against this blog and the site administrators have been informed.";
				$feedback="success";
			}
			else {
				$message = $blogs->abuse->errmsg;
				if (!count($message)) $message[]="Failed to create abuse report";
			}
		}


		// Figure out location style parameters
		$len = count($location);
		if ($location[$len-1]!="blogs") {
			$param2 = $location[$len-1];
			if ($location[$len-2]!="blogs") $param1 = $location[$len-2];
			//print "got param1($param1) param2($param2)<br>\n";
			if ($param1) {
				$author = $param1;
				$title = $param2;
			}
			else if ($param2>0) $bid = $param2;
			else $author = $param2;
		}
		//print "got title($title) author($author) id($bid)<br>\n";
		// Collect actual blog if we have enough information 
		if ($title && $author && $title!="en.js") {
			$query = "SELECT b.id FROM blogs b
				LEFT JOIN member_access ma ON ma.member_id=b.member_id
				LEFT JOIN member_profile mp ON mp.access_id = ma.id
				WHERE b.name='$title' AND mp.blog_name='$author'
				AND b.suspended = 0
				LIMIT 1
				";
			//print "get blog ID($query)<br>\n";
			$bid = $db->get_var($query);
			// We failed to get the blog ID from blog-title/post-title try member-name/post-title
			if (!$bid) {
				$query = "SELECT b.id FROM blogs b
					LEFT JOIN members m ON m.member_id = b.member_id
					WHERE b.name='$title' AND concat(m.firstname, '-', m.surname) = '$author'
					AND b.suspended = 0
					LIMIT 1
					";
				//print "get blog ID($query)<br>\n";
				$bid = $db->get_var($query);
				if (!$bid) {
					$msg = getcwd()."Title($title) author($author) \n\n";
					$msg .= print_r($_SERVER, 1);
					//mail("phil.redclift@ichameleon.com", "Blog system failed to get a blog", $msg);
				}
			}
		}
		else if ($author && $author!="langs") {
			//print "Get author ID from ($author)<br>\n";
			$query = "SELECT m.member_id FROM members m
				LEFT JOIN member_access ma ON ma.member_id=m.member_id
				LEFT JOIN member_profile mp ON mp.access_id = ma.id
				WHERE mp.blog_name='$author'
				AND ma.msv=".$site->id."
				LIMIT 1
				";
			//print "get author ID($query)<br>\n";
			$aid = $db->get_var($query);
			// We failed to get the blog ID from blog-title/post-title try member-name/post-title
			if (!$aid) {
				$query = "SELECT b.member_id FROM blogs b
					LEFT JOIN members m ON m.member_id = b.member_id
					WHERE concat(m.firstname, '-', m.surname) = '$author'
					LIMIT 1
					";
				//print "get author ID from name($query)<br>\n";
				$aid = $db->get_var($query);
				if (!$aid) {
					//mail("phil.redclift@ichameleon.com", "Blog system failed to get an author", getcwd()."Author($author)");
				}
			}
			if ($aid) $action="memberlist";
		}
	
	}	
	

	
	// Load a blog if we have one
	if ($bid) {
		$blogs->loadBlog($bid);
		// Is this a preview request from Treeline?
		if ($_SESSION['treeline_user_id'] && isset($_GET['admin'])) {
			$public=true; $mode="preview"; $showPreviewMsg=true;
		}
	}

	// Page specific options
	$pageClass = 'blogs'; // used for CSS usually
	
	$css = array('page','blog'); // all attached stylesheets
	$extraCSS = '';
	
	// If we have a blog id need to load the blog and set the page title
	if (is_array($blogs->blog)) $pageTitle=$blogs->blog['title'];
	else if ($blogmode=="blog" && $_SESSION['member_id']>0) $pageTitle = "Create a new blog post";
	else if ($action=="memberlist"  && $aid) {
		$pageTitle = $db->get_var("SELECT CONCAT(firstname, ' ', surname) FROM members WHERE member_id = ".$aid);
	}
	if (!$pageTitle) $pageTitle="Blogs";
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours

	$extraJSbottom .= '
		CKEDITOR.replace(\'blogtext\', { toolbar : \'contentUser\' });
	';


	if (!$_SESSION['member_id']>0) $panellist[]="log-in-or-register";
	$panellist[]="search-blogs";
	if ($bid>0 && is_array($blogs->blog)) $panellist[]="blogger-profile";
	if (is_array($blogs->blog) && $_SESSION['member_id']!=$blogs->blog['blogger_id']) $panellist[]="other-blogs";
	if ($_SESSION['member_id']>0) $panellist[]="my-blogs";
	
	// Should know what panels we want by now so get the guids
	foreach ($panellist as $tmp) {
		$query = "SELECT guid FROM pages WHERE name='$tmp' AND template IN (6, 24) AND msv=".$site->id;
		//print "$query<br>\n";
		$panels->panels[]=$db->get_var($query);
	}

	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	

	$pagetitle = $pageTitle;
	
	include($_SERVER["DOCUMENT_ROOT"]."/includes/templates/breadcrumb.inc.php");
	
	?>
	
	<div class="container">
	
		<?php include($_SERVER["DOCUMENT_ROOT"]."/includes/templates/pagetitle.inc.php"); ?>
			
		<!-- Columns
		================================================== -->
		<div class="row">
			<div id="primarycontent" class="col-xs-12 col-md-8">


				<p class="visible-sm visible-xs"><a href="#sidebar"><span class="glyphicon el-icon-chevron-down rgap"></span>Quick link to further information</a></p>
			
				<?php 
                // Show the blog maanagement menu
                if ($_SESSION['member_id']>0) { 
                    ?>
                    <ul id="member-options">
                    <?php 
                    if ($blogs->member_bloggable) { 
                        ?>
                        <li><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?mode=blog"><?=($blogs->edit_id?"Edit current blog":"Add a new blog")?></a></li>
                        <!-- <li><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?keywords=member&amp;id=<?=$_SESSION['member_id']?>">View my blogs</a></li> -->
                        <li><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?mode=vet">Manage my blog comments</a></li>
                        <li><a href="<?=$site->link?>member-login/">My account</a></li>
                        <?php 
                    } 
                    else { 
                        ?>
                        <li>You do not have your own blog</li>
                        <?php 
                        } 
                    ?>
                    </ul>             
                    <?php 
                } 


				if ($message) echo drawFeedback($feedback, $message); 
			
				// If we are in edit mode we need to show an edit page to add/modify current blog 
				if ($blogmode=="blog" && $_SESSION['member_id']>0) {
					?>
					<p>Please add your blog entry below.<br />
					You can save your blog as often as you like while you are preparing it.<br />
					Once you have completed your blog you should use the post button it which will enable it to appear on the website.<br />
					You can create multiple blogs but you cannot change blogs after they have been posted.</p>
					<form id="blog-form" method="post">
					<fieldset>
						<label for="blogtitle"><strong>Blog title</strong></label>
						<input type="text" id="blogtitle" name="blogtitle" value="<?=($_POST?$_POST['blogtitle']:$blogs->edit_title)?>">
					</fieldset>
					<fieldset>
					<textarea name="blogtext" id="blogtext"><?=($_POST?$_POST['blogtext']:$blogs->edit_text)?></textarea>
					</fieldset>
					<fieldset>
						<input type="submit" class="submit" name="action" value="Save blog" />
						<input type="submit" class="submit" name="action" value="Post blog" />
					</fieldset>
					</form>
					<?php
				}
				
			
				// --------------------------------------------------------------	
				// Manage my blog comments
				else if ($mode=="vet" && $_SESSION['member_id']>0) {
					$blogs->vetComments($_SESSION['member_id']);
				}
				// --------------------------------------------------------------	
			
			
				// --------------------------------------------------------------	
				// Do we need to show a set blog?
				else if (is_array($blogs->blog)) {
					
					if ($blogs->blog['status']=="suspended" && !isset($_GET['admin'])) {
						?>
						<p>This post has been suspended</p>
						<?php
					}
					else {
						if ($action=="report") {
							include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/abuse.report.inc.php";
						}
			
						if (isset($_GET['comment'])) {
							include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/blog_comments.php");
						}
                        //echo print_r($blogs->blog, 1)
						?>
						
                        <div class="blog-post-summary">
                        	<?php
							if ($blogs->blog['blogger_image']) {
								?>
								<div class="post-image post1" style="background-image: url('<?=$blogs->blog['blogger_image']?>');"></div>
								<?php
							}
							?>
                            <div class="date-box"><?=$blogs->blog['blog_day']?>
                                <div class="date-box-month"><?=$blogs->blog['blog_month']?></div>
                            </div>
                            <h3><a href="<?=$blogs->drawLink($blog->blog['blog_title'], $blogs->blog['blogger_name'], $blogs->blog['title'])?>"><?=$blogs->blog['title']?></a></h3>   
                            <div class="post-meta">
                                <span class="extras"><span class="glyphicon el-icon-user rgap"></span><a href="<?=$blogs->drawAuthorLink($blogs->blog['blog_title'], $blogs->blog['blogger_name'])?>"><?=$blogs->blog['blogger_name']?></a> </span>
                                <?php
                                if ($blogs->blog['blog_comments']>0) {
                                    ?>
                                    <span class="extras"><span class="glyphicon el-icon-comment rgap"></span><a href="#comments"><?=($blogs->blog['blog_comments'])?> Comment<?=($blogs->blog['blog_comments']>1?"s":"")?></a></span>
                                    <?php
                                }
                                ?>
                            </div> 
                            <div class="clearfix"></div>      
                            <div class="post-content">
								<?php echo $blogs->blog['content']?highlightSearchTerms($blogs->blog['content'], $blogs->getTerm(), 'strong', 'keywords'):"<p>This blog has not been started yet</p>"; ?>
                        	</div>
                            
                            <!-- Next and previous posts
                            ================================================== -->
                            <ul class="pager">
                                <li class="previous <?=($blogs->blog['prev']?'':'disabled')?>"><a href="<?=$blogs->blog['prev']?>"><span class="glyphicon el-icon-chevron-left rgap"></span>Previous post</a></li>
                                <li class="next <?=($blogs->blog['next']?'':'disabled')?>"><a href="<?=$blogs->blog['next']?>">Next post<span class="glyphicon el-icon-chevron-right lgap"></span></a></li>
                            </ul>
                        
                        </div>
						
						<?php 
						if ($blogs->blog['status']=="live") { 
							?>
							<h3>
							<?php
							if ($blogs->blog['allow_comments']) {
								?>
								<a href="<?=$site->link?>blogs/?bid=<?=$blogs->blog['id']?>&amp;comment">Comment on this blog</a> 
								<?php
							
							}
							/*
							 <a href="<?=$site->link?>blogs/?bid=<?=$blogs->blog['id']?>&amp;action=report">Report this blog as abuse</a></p>
							 */
							?>
							</h3>
							<?php 
						} 
			
						if ($blogs->blog['allow_comments']) {
							echo $blogs->drawComments();
						}
					}
				}
				// --------------------------------------------------------------	
			
				else if ($aid>0) {
					$blogs->setMemberID($aid);
					$resulthtml = $blogs->drawMemberList($thispage, $orderBy, $orderDir);
					?>
					<!-- <p id="result-total"><?=$blogs->drawTotal()?></p> -->
					<?=$resulthtml?>
					<?php
				}
				
				// --------------------------------------------------------------	
				// Default to a list of blogs.
				else {
					// Draw blogs by author
					//echo $blogs->drawBlogResultsByAuthor($thispage, $orderBy, $orderDir, true);
					echo $blogs->drawBlogResults($thispage, $orderBy, $orderDir);
				}
				?>
					
			<!-- end of primary content -->
			</div>

            <div id="sidebar" class="col-xs-12 col-md-4">
                <div id="secondarycontent" class="sidebar-container">
    
                    <?php
                    echo $panels->draw();
            
                    // If I am logged in then show my own posts too.
                    if ($_SESSION['member_id']>0 && 0) { 
                        $tmp_blog_list = $blogs->drawListByBlogger($_SESSION['member_id']);
                        if ($tmp_blog_list) {
                            ?>
                            <div class="panel ">
                                <h3>My blogs</h3>
                                <ul class="blog-list">
                                <?=$tmp_blog_list?>
                                </ul>
                            </div>        	
                            <?php 
                        } 
                    }
                    ?>
    
                </div>
            </div>
            <!-- END OF SECONDARY CONTENT -->
    
    
        </div>
    </div>

	<?php 
    include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
    ?>

<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_blogging.js"></script>
