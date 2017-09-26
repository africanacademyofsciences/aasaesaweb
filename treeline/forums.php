<?php

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/abuse.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/forum.class.php");
	
	
	global $db;
	$tags = new Tags();// TAGS
	$tags->setMode('edit');
	$forum = new Forum();// TAGS
	$abuse = new Abuse();
	
	if ($_SESSION['treeline_user_group']=="Author" || !$site->getConfig("setup_forum")) redirect("/treeline");
	
	
	//print "Post(".print_r($_POST, true).") action(".$_POST['post_action'].")<br>\n";
	if ($_POST['post_action']) $action = $_POST['post_action'];
	if (!$action) $action = strtolower(read($_REQUEST,'action',''));
	$section = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'section','cat');

	$currentPage = read($_REQUEST,'p','');
	
	$page = new Page;
	$thispage = read($_REQUEST,'p',1);
	$page->setPage($thispage);
	$currentPage = read($_GET,'page',1);
	$currentPage = ($currentPage != 1) ? ($currentPage) : read($_GET, 'p', 1);
	$sortBy = read($_GET,'sort',1);
	$clauses = read($_GET,'clauses',1);

	$member_type = 0; 		// Default to all members.
	
	$id = read($_REQUEST,'guid',NULL);
	$guid = read($_REQUEST, 'id', '');
	if (!$guid){
		$guid = read($_REQUEST, 'guid', '');
	}

	(int)$cat_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'cat_id', NULL);
	(int)$topic_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'topic_id', NULL);	
	(int)$post_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'post_id', NULL);
	//print "cat($cat_id) top($topic_id) post($post_id) action($action)<br>\n";

	$userid = read($_SESSION, 'userid', NULL);	

	$feedback="error";
	$nextsteps='';

	//print "section($section) action($action) cat($cat_id) topic($topic_id) post_id($post_id) guid($guid)<br>";
	
	// THIS SECTION JUST COLLECTS DATA FOR EDIT CAT/TOPIC/POST
	if($section == 'cat'){ // Get Category Data to fill in Edit boxes
		
		if ($action == "edit" && $post_id && $topic_id && $cat_id) {
			$posts = $forum->getPostAdminList($topic_id, $currentPage, $post_id);
			if ($posts){
				foreach ($posts as $post){
					$post_title = $post->title;
					$post_msg = $post->message;
					$suspended = $post->suspended==-1?1:0;
				}
			}
		}
		
		else if(
			($cat_id && $action=="add" && !$topic_id) || 
			($cat_id && $action == 'edit' && $topic_id)
			){
			if ($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['topic_title'])) {
				$topic_title = $_POST['topic_title'];
				$topic_desc = stripslashes($_POST['topic_desc']);
				$suspended = $_POST['suspended']==1?1:0;
				// print "set topic title to ".$topic_title." desc(".$topic_desc.") ss($suspended)<br>\n";
			}
			else if ($action=="edit") {
				$topics = $forum->getTopicAdminList($cat_id, $currentPage, $topic_id); //getData
				if($topics){
					foreach($topics as $topic){ // put data into variables (referenced in forms below)		
						$topic_title = $topic->title;
						$topic_desc = $topic->message;
						$suspended = $topic->suspended;
					}
				}
			}
		}
		
		// Collect category data
		else if(
			(!$cat_id && $action=="add") || 
			($cat_id && $action=="edit" && !$topic_id)
			){
			if ($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['cat_title'])) {
				$cat_title = $_POST['cat_title'];
				$cat_desc = $_POST['cat_desc'];
				$suspended = $_POST['suspended']==1?1:0;
				$member_type = $_POST['member_type'];
			}
			else if ($action=="edit"){
				$categories = $forum->getCategoryAdminList($cat_id, $currentPage); //getData
				if($categories){
					foreach($categories as $category){ // put data into variables (referenced in forms below)							
						//print_r($category);
						$cat_title = $category->title;
						$cat_desc = $category->message;
						$suspended = $category->suspended;
						$member_type = $category->member_type;
					}
				}
			}
		}
			
	} 
	// -------------------------------------------------------------
	
	
	// PERFORM ANY ACTIONS REQUIRED
	// print "got section($section) action($action)<br>\n";
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {	
	
		if ($section == 'cat' && $_POST['submitted']==1) {
	
			// Add a new category.
			if ($action == 'add') {
				if (!$cat_id) $success = $forum->createCategory();
				else $success=$forum->createTopic($cat_id);
				if ($success) {
					$feedback="success";
					$message[]= $page->drawGeneric($cat_id?"topic":"category", 1)." ".$page->drawLabel("tl_cat_add_success", "created successfully");
					$action="";
				}
				else $message = $forum->err_msg;
			} 
			// Edit a forum item.			
			else if ($action == 'edit') {
				if ($cat_id && $topic_id && $post_id) $success = $forum->editPost($topic_id, $cat_id, $post_id);
				else if ($cat_id && $topic_id) $success = $forum->editTopic($topic_id, $cat_id);
				else if ($cat_id) $success = $forum->editCategory($cat_id);

				if ($success) {
					$feedback="success";
					$message[] = $page->drawLabel("tl_foro_edit_success", "Update successful");
					$action="";
					$_POST['submitted']=0;	// Pretend not in form submitted mode anymore
				}
				else $message = $forum->err_msg;
			} 

			else if ($action == 'delete') {
				$message = $forum->deleteCategory($guid);
			}		
		}
		// Other actions
		else {
		}
		
		
	}
	else {
	
		// Unsuspend a forum post (this can also be done from the edit post page
		if ($action == "unsuspend") {
			if ($forum->suspendPost($post_id, false) || !$forum->getSuspended($post_id)) {
				$abuse->update($post_id, "forum", "ADMIN-UNSUSPEND");
			}
			$action="";
		}
		// Suspend a set post/topic
		// usually genereated by an abuse report on the website
		else if ($action == "suspend") {
			if ($forum->suspendPost($post_id) || $forum->getSuspended($post_id)) {
				// Need to clear any abuse reports affecting this post
				$abuse->update($post_id, "forum", "ADMIN-SUSPEND");
				$feedback="success";
			}
			else $message="Failed to suspend post";
		}
		
		// Delete a post either from treeline list or abuse reporter
		else if ($action=="abuse-delete" || $action=="delete") {
			
			if ($forum->deletePost($post_id)) {
				// If there is an abuse report logged regarding the post we are deleting we need
				// to update the history table so everyone knows this item has been actioned
				$abuse_status="ADMIN-DELETED";
				if ($action=="abuse-delete") $abuse_status="DELETED";
				// Need to update history and remove all references to this post
				$abuse->update($post_id, "forum", $abuse_status);
			}
			else $message[]="Failed to delete this post";
			$action="";
		}
		// Ignore an abuse report
		else if ($action == "abuse-restore") {
			if ($post_id>0) {
				if ($forum->suspendPost($post_id, false) || !$forum->getSuspended($post_id)) {
					$abuse->update($post_id, "forum", "RESTORED");
				}
				else $message[]="Failed to unsuspend post";
			}	
			else $message[]="No post ID passed";
			$action="";
		}
		// Create a new forum for this site.
		else if ($action=="generate") {
			$new_forum=new Page();
			$new_forum->setParent($site->id);
			$new_forum->setTitle('Forum');
			if (!$new_forum->generateName()) {
				$message[]="A page already exists called forum";
			}
			else {
				$new_forum->setHidden(0);
				$new_forum->setLocked(0);	
				$new_forum->setStyle(2);
				$new_forum->setSiteID($site->id);
				$new_forum->setSortOrder(4);					
				$new_forum->setTemplate(21);
				$new_forum->setMetaDescription('Site forum index');
				$new_forum->setMetaKeywords('Forum');

				$new_forum->create(1);

				// Need to force publish
				$db->query("UPDATE pages SET date_published=NOW() WHERE guid = '".$new_forum->getGUID()."'");

				$nextsteps.= '<li><a href="/treeline/forums/">Manage the new forum</a></li>';
				$nextsteps.= '<li><a href="/treeline/forums/?section=cat&action=add">Add categories to my new forum</a></li>';
				$action = '';
			}
		}
	}
	
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables', 'forum'); // all CSS needed by this page
	$extraCSS = '
	
	table#forum-posts {
		
	}
		table#forum-posts tr td span.suspended {
			float: none;
		}
	
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '
	
	function setFormAction(form, a) {
		var f = document.getElementById(form);
		if (f) {
			//alert("set post act("+a+")");
			f.post_action.value=a;
			f.submit();
			return true;		
		}
		return false;
	}
	
	
	'; // extra on page JavaScript
	
	// Page title	
	//$pageTitleH2 = ($action) ? 'Forums : '.ucwords($action) : 'Forums';
	//$pageTitle = ($action) ? 'Forums : '.ucwords($action) : 'Forums';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("forums", $action);
	$pageClass = 'forums';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">

<?php

echo drawFeedback($feedback, $message);
if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

$section = ($section) ? $section : 'cat'; 
//print "action($action) section($section) action($action) cat($cat_id) topic($topic_id) post_id($post_id) guid($guid)<br>";


// -------------------------------------------------------
// Edit a post
if ($action == 'edit' && $cat_id && $topic_id && $post_id) { 

	$page_html = '	
	<form id="editPost" method="post" action="?section=cat">
	<fieldset>
		<input type="hidden" name="submitted" value="1" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="cat_id" value="'.$cat_id.'" />
		<input type="hidden" name="topic_id" value="'.$topic_id.'" />
		<input type="hidden" name="post_id" value="'.$post_id.'" />

		<!--
		<label for="post_title">'.$page->drawGeneric("title", 1).':</label>
		<input type="text" class="text" name="post_title" id="post_title" value="'.$post_title.'" readonly="readonly" />
		-->
		
		<label for="post_msg">'.$page->drawGeneric("message", 1).':</label>
		<textarea type="text" class="text" name="post_msg" id="post_msg">'.trim($post_msg).'</textarea>

		<label for="f_suspended">'.$page->drawGeneric("suspended", 1).'</label>
		<input type="checkbox" id="f_suspended" name="suspended" value="1" '.($suspended==1?'checked="checked"':"").' style="clear:none;margin-left:0px;width:auto;" />

		<label for="form-sumbit" style="visibility:hidden;" >Submit</label>
		<input type="submit" class="submit" value="'.$page->drawGeneric("edit", 1).'" />
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_foro_epost_title", "Edit this post"), "blue");
} 

// -------------------------------------------------------
// Add or edit a topic
else if ( 
	($action=="add" && $cat_id) || 
	($action == 'edit' && $cat_id && $topic_id) 
	) { 
	$page_html='
	<form id="'.($action == 'add'?'add':'edit').'Topic" method="post" action="?section=cat">
	<fieldset>
		<label for="topic_title">'.$page->drawGeneric("title", 1).':</label>
		<input type="text" class="text" name="topic_title" id="topic_title" value="'.$topic_title.'" />
		<label for="topic_msg">'.$page->drawGeneric("message", 1).':</label>
		<textarea type="text" class="text" name="topic_desc" id="topic_msg" />'.$topic_desc.'</textarea>
	';
	if ($action!="add") $page_html.='
		<label for="suspended">'.$page->drawGeneric("suspended", 1).'</label>
		<input type="checkbox" id="suspended" name="suspended" value="1" '.($suspended==1?' checked="checked"':"").' style="clear:none;margin-left:0px;width:auto;" />
	';
	$page_html.='
		<input type="hidden" name="submitted" value="1" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="section" value="'.$section.'" />
		<input type="hidden" name="cat_id" value="'.$cat_id.'" />
		<input type="hidden" name="topic_id" value="'.$topic_id.'" />
		<label for="form-sumbit" style="visibility:hidden;" >Submit</label>
		<input type="submit" class="submit" value="'.$page->drawGeneric($action=='add'?'add':'edit', 1).'" />
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_foro_but_".($action=="add"?"add":"edit"), ($action=='add'?'Add':'Edit')." topic"), "blue");

}

// -------------------------------------------------------
// Edit a category or add a category.
else if ($action == 'add' || ($action == 'edit' && $cat_id)) { 

	// Get allowed member types.
	// If there is only 1 type then add as a hidden field
	$member_type_html = '';
	$query = "SELECT id, title FROM member_types ORDER BY sort_order, title";
	//print "$query<br>\n";
	if ($results = $db->get_results($query)) {
		foreach($results as $result) {
			$member_type_html.='<option value="'.$result->id.'"'.($result->id==$member_type?' selected="selected"':"").'>'.$page->drawLabel("tl_memt_".$result->title, $result->title).'</option>';
		}
	}
	if (!$member_type_html) $member_type_html = '<input type="hidden" name="member_type" value="1" />';
	else $member_type_html = '
	<label for="f_member_type">'.$page->drawLabel("tl_foro_add_mtype", "Membership type").':</label>
	<select name="member_type" id="f_member_type">
		<option value="0">'.$page->drawLabel("tl_foro_add_allmem", "All members").'</option>
		'.$member_type_html.'
	</select>
	';	
	
	$page_html='
	<form id="'.($action == 'add'?'add':'edit').'Category" method="post" action="?section=cat">
	<fieldset>
		<p>'.$page->drawLabel("tl_foro_add_msg1", "The forum is split into categories into which users can add threads and posts. The category need only have a descriptive title and some supporting text").'</p>
		<label for="cat_title">'.$page->drawGeneric("Title", 1).':</label>
		<input type="text" class="text" name="cat_title" id="cat_title" value="'.$cat_title.'" /><br />
		<label for="cat_desc">'.$page->drawGeneric("Description", 1).':</label>
		<input type="text" class="text" name="cat_desc" id="cat_desc" value="'.$cat_desc.'" /><br />
	';
	$page_html .= $member_type_html;
	if ($action=="add") $page_html.='<input type="hidden" name="suspend" value="0" />';
	else $page_html.='
		<label for="suspended">'.$page->drawGeneric("Suspended", 1).'</label>
		<input type="checkbox" id="suspended" name="suspended" value="1" '.($suspended==1?'checked="checked"':"").' style="clear:none;margin-left:0px;width:auto;" /><br />
		';
	$page_html.='
		<input type="hidden" name="submitted" value="1" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="section" value="'. $section .'" />
		<input type="hidden" name="cat_id" value="'. $cat_id .'" />
		<input type="hidden" name="userid" value="'. $userid .'" />
		<label for="form-sumbit" style="visibility:hidden;" >Submit</label>
		<input type="submit" class="submit" value="'.($action == 'add'?'Add':'Edit').'" />
	</fieldset>
	</form>
	';
	
	echo treelineBox($page_html, $page->drawLabel("tl_foro_".($action=="add"?'add_title':'edit_title'), ($action=='add'?'Add a new':'Edit this')." category"), "blue");
	
} 

		
// -------------------------------------------------------
// Delete a category 
else if ($section == 'cat' && ($action == 'delete' && $guid)){ 
	?>	
	<form id="deleteCategory" method="post" action="?section=cat">
		<fieldset>
			<legend>Delete category</legend>
			<p class="instructions">Are you sure you want to delete this category?</p>
			<input type="hidden" name="guid" value="<?=$guid?>" />
			<input type="hidden" name="section" value="<?=$section?>" />
			<input type="hidden" name="action" value="<?=$action?>" />
			<input type="submit" class="button" value="Yes delete it" />
		</fieldset>
	</form>
	<? 
}

// -------------------------------------------------------
// List categories
else if ($section == 'cat'){ 

	$results = $forum->getCategoryAdminList(null,1);
	if($results){ 
		
		foreach($results as $result){
			$cat_html.='<option value="'.$result->post_id.'" '.($cat_id == $result->post_id?"selected":"").'>'.$result->title.($result->suspended?" (".$page->drawGeneric("suspended").")":"").'</option>';
		}
		$cat_html='
		<p><a href="?section=cat&action=add">'.$page->drawLabel("tl_foro_cat_add", "Add a new category").'</a></p>
		<form id="categoryPicker" method="post" action="">
		<fieldset>
			<input type="hidden" name="post_action" value="" />
			<input type="hidden" name="section" value="'.$section.'" />
			<select name="cat_id" id="cat_id">
			<option value="">'.$page->drawGeneric("select", 1).'</option>
			'.$cat_html.'
			</select>
			<input type="submit" class="submit" name="action" onclick="setFormAction(\'categoryPicker\', \'topics\');" value="'.$page->drawLabel("tl_foro_cat_viewtop", "View topics").'" />
			<input type="submit" class="submit" name="action" onclick="setFormAction(\'categoryPicker\', \'edit\');" value="'.$page->drawGeneric("edit", 1).'" />
		</fieldset>
		</form>
		';
		echo treelineBox($cat_html, $page->drawGeneric("categories", 1), "blue");

		// -------------------------------------------------------
		// List topics in this category
		if ($cat_id>0 && !$_POST['submitted']){ 
			$results = $forum->getTopicAdminList($cat_id,1);
			if($results){ 
				foreach($results as $result){
					$page_html.='<option value="'.$result->post_id.'" '.($topic_id == $result->post_id? "SELECTED":"").' >'.$result->title.($result->suspended?" (suspended)":"").'</option>';
				}
				$page_html='
				<p><a href="?section=cat&amp;action=add&amp;cat_id='.$cat_id.'">'.$page->drawLabel("tl_foro_topic_add", "Add a new topic").'</a></p>
				<form id="topicPicker" method="post" action="">
				<fieldset>
					<input type="hidden" name="post_action" value="" />
					<input type="hidden" name="section" value="'.$section.'" />
					<input type="hidden" name="cat_id" value="'.$cat_id.'" />
					<select name="topic_id" id="topic_id">
					<option value="">'.$page->drawGeneric("select", 1).'</option>
					'.$page_html.'
					</select>
					<input type="submit" class="submit" onclick="setFormAction(\'topicPicker\', \'posts\');" name="action" value="'.$page->drawLabel("tl_foro_cat_viewpost", "View posts in this topic").'" />
					<input type="submit" class="submit" onclick="setFormAction(\'topicPicker\', \'edit\');" name="action" value="'.$page->drawGeneric("edit", 1).'" />
				</fieldset>
				</form>
				';
				echo treelineBox($page_html, $page->drawLabel("tl_foro_topic_title", "Topics"), "blue");
				
				// -------------------------------------------------------
				// LEVEL 2
				// DISPLAY LIST OF POST IN A TOPIC
				if ($topic_id>0){ 
					echo $forum->drawPostAdminList($cat_id, $topic_id, $currentPage);
				}
				
				
			} 
			else {
				$page_html='<p>'.$page->drawLabel("tl_foro_cat_none", "This category has no topics").'</p>';
				$page_html.='<p><a href="?section=cat&amp;action=add&amp;cat_id='.$cat_id.'">'.$page->drawGeneric("press here", 1).'</a> '.$page->drawLabel("tl_foro_topic_msg1", "to add a new topic to this category. Please note that users of the website can add topics so you are not required to add any here").'</p>';
				$page_title = $page->drawLabel("tl_foro_topic_notitle", 'No topics exist in this category');
				echo treelineBox($page_html, $page_title);
			}
		}
		// Stage 1 - Only showing category list nothing selected so
		// 			 show the abuse reporting system too
		else {
			echo treelineBox($abuse->manage("forum"), "Manage abuse reports");
		} 

	} 
	// Failed to get any categories, 
	// Lets check there is really a forum?
	else {
	
		if (!$db->get_var("SELECT guid FROM pages where template=21 AND msv=".$site->id)) {
			$page_html='<p>This site does not have a forum.</p>';
			if ($_SESSION['treeline_user_group']=="Superuser") $page_html.='<p>Press <a href="/treeline/forums/?action=generate">here</a> if you would like to create a forum for this site.</p>';
			$page_title = 'No forum exists';
		}	
		// There are no categories.........
		else {
			$page_html='<p>This forum has no categories</p>';
			$page_html.='<p>Press <a href="?section=cat&action=add">here</a> to add a new category</p>';
			$page_title = 'No categories exist';
		}
		echo treelineBox($page_html, $page_title, "blue");
	}

} 
	
?>

</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>
