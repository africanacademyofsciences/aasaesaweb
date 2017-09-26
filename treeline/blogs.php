<?
	//ini_set("display_errors", 1);
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/abuse.class.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/blogs.class.php");

	include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	if ($_SESSION['treeline_user_group']=="Author") redirect("/treeline/");
	
	$abuse = new Abuse();

	$action = read($_REQUEST,'action','');
	$guid = read($_REQUEST,'guid','');
	$blog_id = read($_REQUEST,'bid','');
		
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');

	$title = read($_POST,'title','');

	$keywords = read($_REQUEST,'keywords',false);
	$category = read($_REQUEST,'category','xx');
	$thispage = read($_REQUEST,'page',1);

	$blogs = new Blog();
	$blogs->setPerPage(10);
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($action=="edit") {
			$blog_id = $_POST['id'];
			if ($blog_id>0) {
				$blog_title = $db->escape($_POST['blogtitle']);
				$blog_text = $db->escape($_POST['blogtext']);
				//$blog_author = $_POST['blogauthor'];
				$blog_date = is_sql_date($db->escape($_POST['blogdate']));
				
				if (!$blog_title) $message[]=$page->drawLabel("tl_blog_err_title", "You must enter a title for your blog");
				else if (!$blog_date) $message[]=$page->drawLabel("tl_blog_err_date", "Blog date format is incorrect must be YYYY-MM-DD hh:mm");
				else {
					$query = "UPDATE blogs SET 
						title = '$blog_title',
						name = '"._generateName($blog_title)."',
						text = '$blog_text',
						date_added = '$blog_date'
						WHERE id=$blog_id
						";
					//print "$query<Br>\n";
					$db->query($query);
					if (!$db->last_error) {
						$action="list";
					}
					else $message[]=$page->drawLabel("tl_blog_err_update", "Failed to update blog");
				}
			}
		}
		else if ($action=="delete") {
			if ($blogs->delete($blog_id)) {
				$message[]=$page->drawLabel("tl_blog_dele_success", "Blog has been deleted");
				$feedback="success";
			}
			$action="list";
		}

	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
		//if ($action) print "acction($action)<br>\n";
		
		if ($action == 'suspend') {
		
			$blogs->suspend($blog_id, true);

			// Need to clear any abuse reports affecting this post
			$abuse->update($blog_id, "blogs", "ADMIN-SUSPEND");

			$message[]="This blog has been suspended";
			$feedback="success";
			
			$action = "list";
		}
		
		// Delete a blog either from treeline list or abuse reporter
		else if ($action=="abuse-delete") {
			if ($blogs->delete($blog_id)) {
				$abuse->update($blog_id, "blogs", "DELETED");
			}
			else $message[]=$page->drawLabel("tl_blog_err_faildel", "Failed to delete this post");
			$action="";
		}
		
		// Ignore an abuse report
		else if ($action == "abuse-restore" || $action=="unsuspend") {
			if ($blog_id>0) {
				$blogs->suspend($blog_id, false);
				$abuse->update($blog_id, "blogs", ($action=="abuse-restore"?"RESTORED":"ADMIN-UNSUSPEND"));
			}
			else $message[]="No blog ID passed";
			$action="";
			$actoin="list";
		}

		// Blogs page not yet created for this site.
		else if ($action=="generate") {
			$forum=new Page();
			$forum->setParent($site->id);
			$forum->setTitle('Blogs');
			if (!$forum->generateName()) {
				$message[]="A page already exists called blogs";
			}
			else {
				$forum->setHidden(0);
				$forum->setLocked(0);	
				$forum->setSiteID($site->id);
				$forum->setSortOrder(4);					
				$forum->setTemplate(29);
				$forum->setMetaDescription('Main blogs index page');
				$forum->setMetaKeywords('Blogs');
				
				$forum->create(1);
				
				// Need to force publish
				$db->query("UPDATE pages SET date_published=NOW() WHERE guid = '".$forum->getGUID()."'");
				
				//$nextsteps.= '<li><a href="/treeline/blogs/">Manage blogs</a></li>';
				$message[]="A new blogs page has been created for this site.";
				$message[]='You can access your blogs page at <a href="'.$site->link.'blogs" target="_blank">'.$site->name.'/blogs</a> or you can <a href="/treeline/pages/?action=edit&amp;guid='.$forum->getGUID().'">edit the blogs page attributes</a> and select a section of the site for blogs to appear in';
				$action = '';
			}
		}
		
	}


	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

	
';

	// Page title	
	//$pageTitleH2 = ($action) ? 'Blogs : '.ucwords(str_replace("-", " ", $action)) : 'Personal pages';
	$pageTitle =  $pageTitleH2 = $page->drawPageTitle("blogs", $action);
	$pageClass = 'blogs';



	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');




?>


<div id="primarycontent">
<div id="primary_inner">
<?php

echo drawFeedback($feedback,$message);
if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");


	if (!$action) {
		
		// Lets check there is really a forum?
		if (!$db->get_var("SELECT guid FROM pages where template=29 AND msv=".$site->id)) {
			$page_html='<p>This site does not have a blogs page.</p>';
			if ($_SESSION['treeline_user_group']=="Superuser") $page_html.='<p>Press <a href="/treeline/blogs/?action=generate">here</a> if you would like to create a blogs index for this site.</p>';
			$page_title = 'Blogs page does not exist';
			echo treelineBox($page_html, $page_title, "blue");
		}
		else $action="list";	
	}

	if ($action=="list") {

		$page_html='
		<form id="treeline" action="/treeline/blogs/'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<label for="keywords">'.$page->drawLabel("tl_blog_search_search", "Search for").': </label>
			<input type="text" name="keywords" id="keywords" value="'.$keywords.'" /><br />
			<label for="category">'.$page->drawLabel("tl_blog_search_by", "Search by").':</label>
			<select name="category" id="category">
				<option value="xx">'.$page->drawGeneric("select",1).'</option>
				<option value="title" '.($category=='title'?'selected="selected"':"").'>'.$page->drawGeneric("title", 1).'</option>
				<option value="author" '.($category=='author'?'selected="selected"':"").'>'.$page->drawGeneric("author", 1).'</option>
				<!-- <option value="abuse" '.($category=='abuse'?'selected="selected"':"").'>Reported as abuse</option> -->
			</select><br />
			<input type="hidden" name="findcat" value="1" />
			<fieldset class="buttons">
				<input name="filter" type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_blog_search_title", "Find existing blogs to manage"), "blue");
		
		$category = ($category == 'xx') ? '' : $category;
		
		if(!$category && $keywords) $category="title";
		else if(!$keywords && $category ) {
			$feedback = 'error';
			$message = $page->drawLabel("tl_blog_err_keyword", 'You need to specify keywords to search with');
			echo drawFeedback("notice",$message);
		}
		
		if( (!$category && !$keywords) || ($category && $keywords) ) {
			
			if (!$category && !$keywords) {
				echo treelineBox($abuse->manage("blogs"), $page->drawLabel("tl_blog_abuse_title", "Manage abuse reports"));
			}

			$blog_list = $blogs->drawBlogsList($thispage,$category,$keywords);
			if ($blog_list) echo $blog_list;
			else {
				echo '<p>'.$page->drawLabel("tl_blog_err_noblogs", "No blogs found by this search").'</p>';
			}
		}
		

	}
	
	// --------------------------------------------------------------
	// Edit a blog in Treeline
	else if ($action=="edit") {
		$blog_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "id", 0);
		$blogs->loadBlog($blog_id);
		
		// Do we want to allow them to move a blog from one author to another?
		/*
		$query = "SELECT m.member_id, CONCAT(firstname, ' ', surname) AS name FROM members m
			LEFT JOIN member_access ma ON ma.member_id = m.member_id
			WHERE ma.`status`='A'
			AND ma.msv=".$site->id."
			AND ma.blog_allowed=1
			";
		//print "$query<br>\n";
		$blogauthorlist='';
		if ($results = $db->get_results($query)) {
			foreach($results as $result) {
				$blogauthorlist.='<option value="'.$result->member_id.'"'.(($_POST?$_POST['blogauthor']:$blogs->edit_author)==$result->member_id?' selected="selected"':"").'>'.$result->name.'</option>';
			}
		}
		*/
		//print_r($blogs->blog);
		$page_html = '
<form id="blog-form" method="post">
<fieldset>
	<input type="hidden" name="action" value="'.$action.'" />
	<input type="hidden" name="id" value="'.$blog_id.'" />
	<label for="blogtitle"><strong>'.$page->drawGeneric("title", 1).'</strong></label>
	<input type="text" id="blogtitle" name="blogtitle" value="'.($_POST?$_POST['blogtitle']:$blogs->blog['title']).'">
</fieldset>
<!--
<fieldset>
	<label for="f_author"><strong>Blog author</strong></label>
	<select name="blogauthor" id="f_author">
		'.$blogauthorlist.'
	</select>		
</fieldset>
-->
<fieldset>
	<label for="f_added"><strong>'.$page->drawGeneric("date", 1).'</strong></label>
	<input type="text" id="f_added" name="blogdate" value="'.($_POST?$_POST['blogdate']:$blogs->blog['date']).'" maxlength="20" />
</fieldset>
<fieldset>
	<label for="blogtitle"><strong>'.$page->drawGeneric("content", 1).'</strong></label>
	<div style="float: left;">
	<textarea name="blogtext" id="blogtext" style="">'.($_POST?$_POST['blogtext']:$blogs->blog['content']).'</textarea>
	</div>
</fieldset>
<fieldset>
	<label for="f_submit" style="visibility:hidden;">Submit</label>
	<input type="submit" id="f_submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
</fieldset>
</form>
		';
		$page_title = $page->drawLabel("tl_blog_edit_title", "Edit blog")." : ".($_POST?$_POST['blogtitle']:$blogs->blog['title']);
		echo treelineBox($page_html, $page_title, "blue");
	}
	// --------------------------------------------------------------

	
	else if ($action=="delete") { 

		$page_html = '
			<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
				<fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="bid" value="'.$blog_id.'" />
					</legend>
					<p class="instructions">'.$page->drawLabel("tl_blog_dele_message1", "You are about to delete this blog, are you sure?").'</strong></p>
					<p class="instructions">'.$page->drawLabel("tl_blog_dele_message2", "To preview this blog first").', <a href="'.$site->link.'blogs/?bid='.$blog_id.'&amp;mode=preview" target="_blank">'.$page->drawGeneric("click_here").'</a>.</p>
					<fieldset class="buttons">
						<input type="submit" class="submit" value="'.$page->drawGeneric("delete", 1).'" />
					</fieldset>
				</fieldset>
			</form>
			';
		$page_title = $page->drawLabel("tl_blog_dele_title", 'Delete blog').' : '.$db->get_var("SELECT title FROM blogs where id=".$blog_id);
		echo treelineBox($page_html, $page_title, "blue");
	}
	

?>
</div>
</div>
<?php
if ($action=="edit") {
	/*
	?>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_blogging_admin.js"></script>
	<?php
	*/
	?>
	<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
    <script type="text/javascript">
    CKEDITOR.replace('blogtext', { toolbar : 'contentStandard', height: '300px', width: '500px' });
    </script>
	<?php
}
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>