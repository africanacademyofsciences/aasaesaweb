<?

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/abuse.class.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/blogs.class.php");
	
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	$term = read($_REQUEST,'keywords','');
	$thispage = read($_GET,'p',1);

	$orderBy = read($_GET,'filter','date_added');
	$orderDir = read($_GET,'order','desc');	
	$tagFilter = read($_GET,'tag',false);
	
	$blogmode = read($_GET, "mode", "");

	$blogs = new Blog($_SESSION['member_id'], $term);
	$blogs->setPage($thispage);

	$feedback="error";
	
	if ($_SERVER['REQUEST_METHOD']=="POST") {
		
		$action=strtolower(read($_POST,"action",""));
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
	}
	else {
		if (isset($_GET['report'])) {

			if ($blogs->abuse->report($_GET['bid'], "blogs")) {
				$message[]="An abuse report has been logged against this blog and the site administrators have been informed.";
				$feedback="success";
			}
			else $message[]="Failed to create abuse report";
			$_GET['bid']='';
		}
	}	

	
	// Load a blog if we have one
	if ($_GET['bid']) {
		$blogs->loadBlog($_GET['bid']);

		if ($_SESSION['treeline_user_id'] && isset($_GET['admin'])) {
			$public=true; $mode="preview"; $showPreviewMsg=true;
		}
	}

	// Page specific options
	$pageClass = 'blogs'; // used for CSS usually
	
	$css = array('resources','page','contact','blog','forms'); // all attached stylesheets
	$extraCSS = '';
	
	// Set search panel title
	$searchTitle="Search blogs";
	if ($term || $_GET['bid']) $searchTitle="Search for another blog";

	// If we have a blog id need to load the blog and set the page title
	if (is_array($blogs->blog)) $pageTitle=$blogs->blog['title'];
	if (!$pageTitle) $pageTitle="Blogs listing page";
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours

	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
			
?>
<div id="midholder">
    
    <!--
    <div id="sidebar">
        <ul class="submemu">
        <?php //echo $menu->drawSecondaryByParent($page->getPrimary($pageGUID), $pageGUID); ?>
        </ul>
    </div>
    -->
    
    <h1 class="pagetitle"><?=$pageTitle?></h1>

	<?php if ($_SESSION['member_id']>0) { ?>
        <ul id="member-options">
        <?php if ($blogs->member_bloggable) { ?>
            <li><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?mode=blog"><?=($blogs->edit_id?"Edit current blog":"Add a new blog")?></a></li>
            <!-- <li><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?keywords=member&amp;id=<?=$_SESSION['member_id']?>">View my blogs</a></li> -->
        <?php } else { ?>
            <li>You do not have your own blog</li>
        <?php } ?>
        </ul>             
    <?php } ?>
    
    <ul id="filters">
    <?php
    if( $blogs->getTotal()<=0){
        $resulthtml='<p>There are no blogs ';
        if( $term > '') $resulthtml.='matching the term <strong>'.$term.'</strong> ';
        $resulthtml.='in this site.</p>';
    }
    else {
        $pageURL = substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'?'));

        // Title a-z
        if($orderBy=='title' && $orderDir=='asc') $formhtml.='<li>'.ucfirst($labels['title(a-z)']['txt']).'</li>';
        else $formhtml.='<li><a href="'.$pageURL.'?keywords='.$term.'&amp;filter=title&amp;order=asc">'.ucfirst($labels['title(a-z)']['txt']).'</a></li>';
        // Title z-a
        if($orderBy=='title' && $orderDir=='desc') $formhtml.='<li>'.ucfirst($labels['title(z-a)']['txt']).'</li>';
        else $formhtml.='<li><a href="'.$pageURL.'?keywords='.$term.'&amp;filter=title&amp;order=desc">'.ucfirst($labels['title(z-a)']['txt']).'</a></li>';

        // Date oldest
        if($orderBy=='date_added' && $orderDir=='asc') $formhtml.='<li>'.ucfirst($labels['date(oldest)']['txt']).'</li>';
        else $formhtml.='<li><a href="'.$pageURL.'?keywords='.$term.'&amp;filter=date_added&amp;order=asc">'.ucfirst($labels['date(oldest)']['txt']).'</a></li>';
        // Date newest
        if($orderBy=='date_added' && $orderDir=='desc') $formhtml.='<li class="last">'.ucfirst($labels['date(newest)']['txt']).'</li>';
        else $formhtml.='<li class="last"><a href="'.$pageURL.'?keywords='.$term.'&amp;filter=date_added&amp;order=desc">'.ucfirst($labels['date(newest)']['txt']).'</a></li>';

        echo $formhtml;
        $resulthtml = $blogs->drawBlogResults($thispage,$orderBy,$orderDir);
    }
    ?>
    </ul>       
    
    <div id="primarycontent">
    
        <?php if ($message) echo drawFeedback($feedback, $message); ?>

        
        <?php
		/// If we are in edit mode we need to show an edit page to add/modify current blog 
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
		// Do we need to show a set blog?
        else if (is_array($blogs->blog)) {
			?>
			<div id="blog-<?=$blogs->blog['id']?>" class="blogtext">
				<?=$blogs->blog['content']?>
				</div>
                <?php if ($blogs->blog['status']=="live") { ?>
				<p class="abuse-report"><a href="<?=$site->link?>blogs/?bid=<?=$blogs->blog['id']?>&amp;report">Report this blog as abuse</a></p>
                <?php } ?>
            <?php
        }
        // Show results listing.
        else {
			?>
            <p style="clear:left;"><?=$blogs->drawTotal()?></p>
            <?=$resulthtml?>
            <?php
        }
        ?>
    <!-- end content div (id="content") -->
	</div>


    <div id="secondarycontent">

        <div class="panel rounded">
            <h3><?=$searchTitle?></h3>
	        <form id="blog-search" method="get" action="<?= $_SERVER['REQUEST_URI'] ?>">
    	    <fieldset>
            	<input type="text" class="text" name="keywords" value="<?=$term?>" />
        	    <input type="submit" value="<?=ucfirst($labels['filter-btn']['txt'])?>" />
	        </fieldset>
    	    </form>		
        </div>

		<?php 
		// If we loaded a blog and its not owned by the currently logged in member 
		// show other blogs by that author
		if (is_array($blogs->blog) && $_SESSION['member_id']!=$blogs->blog['blogger_id']) { 
			$tmp_blog_list = $blogs->drawListByID($blogs->blog['id'], true);
			if ($tmp_blog_list) {
				?>
                <div class="panel rounded">
                    <h3>Other posts in this blog</h3>
                    <ul class="blog-list">
                    <?=$tmp_blog_list?>
                    </ul>
                </div>        	
        		<?php 
			} 
		}
        
		// If I am logged in then show my own posts too.
		if ($_SESSION['member_id']>0) { 
			$tmp_blog_list = $blogs->drawListByBlogger($_SESSION['member_id']);
			if ($tmp_blog_list) {
				?>
                <div class="panel rounded">
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

<?php 

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 

?>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_blogging.js"></script>
