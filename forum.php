<?php 

	if (!$site->getConfig('setup_forum')) redirect("/?msg=the forum is not enabled");

	include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/abuse.class.php";
	include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/thread.class.php";
	//$user->checkLogin(); // if user isn't logged in : send them away

	$feedback="";
	$message=array();

	if ($mode!="preview") $mode = "view";					// Force forum into view mode (this page is not editable)

	$tags = new Tags($site->id, 1);
	$tags->setMode($mode);
	
	// Set to false to block even viewing threads to the public.
	$public = true; // Usually forums are public but you must log in to post.
	
	$logged_in = ($_SESSION['member_id']>0 && $_SESSION['member_site_id']==$site->id);
	
	$action = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "action", "");
	if ($_SERVER['REQUEST_METHOD']=="GET" && isset($_GET['report'])) $action="report";
		
	// page parameters
	$filter = read($_GET,'ftype','date_created');			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue','asc');				// value of the filter (can be asc/desc or a specific value)
	$perpage = 5;											// results to show per page - could be set by user
	$thispage = read($_GET,'page',1);						// page as passed by the pagination code
	$current_page = $thispage-1;							// current page

	$addpost = (isset($_GET['newpost']) && $_SERVER['REQUEST_METHOD']=="GET");
	
	$post_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'post', 0);	// Post
	$parent = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'parent', 0); 	// Posts parent
	if ($post_id>0 && !$parent>0) $parent = $db->get_var("SELECT parent_id FROM forum_posts WHERE post_id=".$post_id);

	$admin = ($_SERVER['REQUEST_METHOD']=="GET" && isset($_GET['admin']) && isset($_SESSION['treeline_user_id']));

	$feedback = "error";
	
	//print "parent($parent) post($post_id) action($action)<br>";
	
	// Is this admin preview?
	//print "post($post_id) parent($parent) admin($admin)<br>\n";
	if ($post_id && $admin) {
		$public=true; 
		$mode="preview"; 
		$showPreviewMsg=true;
	}	
	
	$thread = new Thread();
	//print "load thread($post_id, $parent, $filter, $filtervalue, ($perpage * $current_page), $perpage) \n";
	$thread->load($post_id, $parent, $filter, $filtervalue, ($perpage * $current_page), $perpage);	

	
	// Form processing...
	if( $_POST ){
		//print "process action($action)<br>\n";
		switch($action){
		
			case 'add':
				//echo '<pre>'. print_r($_POST,true) .'</pre>';
				//print "content(".$_POST['forum_message'].")<br>\n";
				foreach( $_POST as $key => $value ){

					${$key} = stripslashes( cleanField( read($_POST,"$key",0), 1, '<p><strong><em><h3><a><span><img>' ) );
					
					if ($key=="forum_message") ${$key} = censor(${$key});
					if( substr_count($key,'forum_')>0 && substr_count($key,'forum_tag_')<=0 ){
						$pKey = substr($key, 6);
						$properties[$pKey] = ${$key};
					}
				}
				//print "add(".print_r($properties, true)."<br>\n";
				$new_post_id = $thread->add($properties);
				if ($new_post_id>0) {
				
					$feedback="success";
					$message[]='Your post has been added';
					$action='';
					$thispage = ceil(($totalCount+1) / $perpage);
					$current_page = $thispage-1;
					unset($_POST);
					$parent = $post_id;
					$post_id = $new_post_id;
				}
				else{
					$message[]='Your post could not be added';
				}
				break;
			
			/*	
			case 'edit':
			
				//echo '<pre>'. print_r($_POST,true) .'</pre>';
				//exit();
				
				foreach( $_POST as $key => $value ){
					${$key} = stripslashes( cleanField( read($_POST,"$key",false) ) );
					if( substr_count($key,'forum_')>0 && substr_count($key,'forum_tag_')<=0 ){
						$pKey = substr($key, 6);
						$properties[$pKey] = ${$key};
					}
				}
			
				if($term=='topic'){
					for( $i=0; $i<(NUM_TAG_FIELDS); $i++){
						$tmp = $_POST['forum_tag_'.$i];
						$taglist[($i-1)]['title'] = $tmp;
					}
				}
				
				if( $thread->update( $properties ) ){
				
					$thread->addTags( $taglist );
				
					$feedback="success";
					$message[]='Your '. $term .' has been updated';
					$action=false;
					unset($forum_title,$forum_message);
					$thispage = ceil(($totalCount+1) / $perpage);
					$current_page = $thispage-1;
					unset($_POST);
					// after adding, work out which page we need to view...
				}else{
					$message='Your '. $term .' could not be updated';
				}
			
				break;
				
				
			case 'delete':
				//echo '<pre>'. print_r($_POST,true) .'</pre>';
				if( $postID>0 ){
					if( $thread->delete( $postID ) ){
						$feedback="success";
						$message[]='Your '. $term .' has been deleted';
						$action=false;
						$postID=false;
						$id=$parent;
						$parent=false;
					}else{
						$message='Your '. $term .' could not be deleted';
					}
				}
				break;


			// Login to members area
			case "login": 
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/login.class.php');
				$login = new MemberLogin();
				$message = $login->logIn();
				$action="";
				$logged_in = $_SESSION['member_id']>0 && $_SESSION['member_site_id']==$site->id;
				break;
			*/
			
			case "report" :

				$email = $_POST['email'];
				if (!$captcha->valid) $message = $captcha->errmsg;
				else if ($thread->abuse->report($post_id, "forum", $email)) {
					$message[]="An email has been sent to you containing details on how to complete your abuse report.";
					$feedback="success";
					// Reload posts
					$query = "SELECT parent_id FROM forum_posts WHERE post_id=".($parent+0);
					//print "$query<br>\n";
					$grandparent = $db->get_var($query);
					if ($grandparent>0) { 
						// Skip up one level
						$post_id = $parent; 
						$parent = $grandparent; 
					}
					//print "got post($post_id) parent($parent)<br>\n";
					$thread->load($post_id, $parent, $filter, $filtervalue, ($perpage * $current_page), $perpage);
					$action = '';
				}
				else $message = $thread->abuse->errmsg;
				break;
		}
		
		//print "load thread($post_id, $parent, $filter, $filtervalue, ($perpage * $current_page), $perpage) \n";
		$thread->load($post_id, $parent, $filter, $filtervalue, ($perpage * $current_page), $perpage);
	}
	else {
	
		if ($action == "report") {
			
			$id = $_GET['id'];
			if ($id>0) {
				//print "find out about report $id and suspend if valid;";
				$action = "";
				
				if ($thread->abuse->suspendFromHistory($id)) {
					$message=$thread->abuse->errmsg;
					$message[]="This post has been suspended";
					$feedback="success";
				}
				else $message=$thread->abuse->errmsg;
			}
		}
	}

	//print "parent($parent) post($post_id) action($action)<br>";
		
	// show forums
	if(!$post_id){			
		//$perpage=20;			// If we are showing forums we need to allow them all (or limit to say 20)
		$term = 'category';
		$termPl = 'categories'; //plural
	}
	// show topics
	else if($post_id && !$parent){	
		$term = 'thread';
		$termPl = 'threads'; //plural
	}
	// show posts
	else{
		$term = 'post';
		$termPl = 'posts'; //plural
	}
	//print "term($term) pl($termPL)<br>";		


	$totalCount = $thread->count;
	$postCount = count($thread->posts);
	//print "got total($totalCount) posts($postCount)<br>\n";
	
	$result_start = ($current_page*$perpage)+1;
	$result_end = ($result_start + $postCount)-1;

	// breadcrumb
	if( $breadcrumb = $thread->getBreadcrumb($post_id) ){		// get breadcrumb array
		$tmp_breadcrumb = ( count($breadcrumb)>0) ? array_reverse($breadcrumb,false) : '';
	}

	
	// Forums
	if(!$post_id){			
		$pageTitle .= ($action) ? ucfirst($action) .'Category' : 'View Categories' ;
	}
	// Threads
	else if($post_id>0 && !$parent){	
		$pageTitle = (is_array($tmp_breadcrumb))?$tmp_breadcrumb[0]['title'] : '';
		$forum_options = array("Start a new conversation"=>"post=$post_id&amp;parent=$parent&amp;newpost");
	}
	// Posts/responses.
	else{
		$pageTitle = (is_array($tmp_breadcrumb))?$tmp_breadcrumb[0]['title'] : '';
		$formTitle = 'Reply to this thread';
		if ($action!="report") $forum_options = array("Post a reply"=>"post=$post_id&amp;parent=$parent&amp;newpost");
	}
	
	// get the current folder's title		
	if(!is_array($breadcrumb) || sizeof($breadcrumb)==0) $showTitle = $breadcrumb[0]['title'];

	//print_r($breadcrumb);
	//print "pageTitle($pageTitle) showTitle($showTitle)<br>\n";
	
	// CSS: Look and feel/Appearance
	$css = array('page','forum'); // all attached stylesheets

	$extraCSS = ''; // on page CSS
	
	$extraJS = ''; // on page JS
	
	// SEO & Accessibility
	$pageClass = 'forum';
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
?>
    
    <!-- 
    <div id="sidebar">
        <ul class="submemu">
        <?php //echo $menu->drawSecondaryByParent($page->getPrimary($pageGUID), $pageGUID); ?>
        </ul>
    </div>
    -->

    <h1 class="pagetitle">Forum <?=($pageTitle?" | ".$pageTitle:"")?></h1>

	<?php if ($post_id) { ?>
    <ul id="forum-options">
    	<li class="first"><a href="<?=$site->link?>forum/">Back to forum homepage</a></li>
        <?php
		if ($logged_in) {
			if(is_array($forum_options)) {
				foreach($forum_options as $option=>$link) {
					//print "got option($option) link($link)<br>\n";
					?><li><a href="<?=$site->link?>forum/?<?=$link?>"><?=$option?></a></li><?php
				}
			}
		}
		?>
    </ul>
    <?php } ?>

    <div id="primarycontent">

		<?php
		echo drawFeedback($feedback, $message);

        print "action($action) total($totalCount) term($term) logged in($logged_in) id(".$_SESSION['member_id'].") public($public)<br> \n";
        if(!$action && $totalCount>0 && 
            ($logged_in || $public) )
			{
           	//print "switch($term)\n";
            if($term == 'category') {
                    
				echo $thread->topForumPosts(5);
				?>
				<table id="forumCategory" class="treeline">
				<caption class="title">Showing <?= ($totalCount>1) ? $termPl : '<strong>1</strong> '.$term  ?> <? if( $totalCount>1 ){ ?><strong><?= $result_start?></strong> to <strong><?= $result_end ?></strong><? } ?><?= $showTitle ?></caption>
					<thead>
						<tr class="category">
						  <th scope="col" id="col_title">Title</th>
						  <th scope="col" id="col_content">Content</th>
						</tr>
					</thead>
				   <tbody>
					<?	
					$i =0;
					foreach($thread->posts as $post){ 
						if ($post->title) {
							$class = (($i%2)!=0)?'even':'odd';
							$class .= ($post->suspended<0?' suspended':'');
							?>
							<tr class="category <?= $class ?>">
							  <td class="title">
								<? 
								if( $parent==0 && $post->suspended==0 ){ 
									?><a href="<?=$site->link?>forum/?post=<?=$post->post_id?>&amp;parent=0"><?=$post->title?></a><? 
								} 
								else echo $post->title;
								?>
							  </td>
							  <td class="cat-desc">
								<?= ($post->suspended<0 ? '<span class="suspended">This category has been suspended.</span><br />' : '') ?>
								<?= $post->message ?>
							  </td>
							</tr>
							<? 
							$i++; 	
						}
					} 
					?>
					<td colspan="2" class="forum-bottom"></td>
					</tbody>
				</table>
				<?
				//print "dP($totalCount, $perpage, $thispage)<br>\n";
				echo drawPagination($totalCount, $perpage, $thispage, '?fvalue='.$filtervalue); 
			}
			// POSTS - do we want to show the post list or allow new posts to be submitted?
			else if (!$addpost) {
				?>
                <h2>Showing <?= ($totalCount>1) ? $termPl : '<strong>1</strong> '.$term  ?> <? if( $totalCount>1 ){ ?><strong><?= $result_start?></strong> to <strong><?= $result_end ?></strong><? } ?><?= $showTitle ?></h2>
				<?php
				$pag_html='';
						
				//print "got total($totalCount) per($perpage)<br>\n";
				if( $totalCount>$perpage ){
					$pag_html = drawPagination($totalCount, $perpage, $thispage, $site->link.'forum/?post='.$post_id.'&amp;parent='. $parent.'&amp;fvalue='.$filtervalue); 
					//echo $pag_html;
				}
				
				foreach($thread->posts as $post){ 

					$highlight = ( $parent>0 && (($post_id==$post->post_id) && ($parent==$post->parent_id)) ) ? ' highlight' : '';
					$class = (($i%2)!=0) ? ' class="even'. ($post->suspended<0 ? ' suspended' : '') .'"' : ' class="odd'. ($post->suspended<0 ? ' suspended' : '') .'"';
					$format_date = getDateFromTimestamp($post->date_created);
					?>
					<table id="post_<?= $post->post_id ?>" class="treeline forumPost<?= $highlight?>">
					<tbody>
					<tr>
						<td class="title" colspan="3">
							<p>
							<?php
							if($parent==0) echo 'Conversation | ';
							if ($post->suspended<0 || $preview || $parent>0) echo $post->title;
							else {
								?><a href="<?=$site->link?>forum/?post=<?= $post->post_id ?>&amp;parent=<?=$post->parent_id?>"><?= $post->title ?></a><?php
							}
							?>
							</p>
						</td>
					</tr>
					<tr<?= $class ?>>
						<td class="message<?=(($post->suspended<0 && $mode!="preview")?" suspended":"")?>" valign="top">
							<?php
							if(($post->suspended<0 && $mode!="preview")){ 
								?>
								<p class="suspended"><strong>This post has been suspended</strong></p>
								<p>The message is temporarily suspended while we investigate a problem. It may be restored, or may be permanently deleted. Other messages in this conversation can still be seen.</p>
								<?php
							}
							else {
								?>
								<p><strong><?= date('d/m/Y', $format_date)?></strong> at <strong><?=date('H:i', $format_date) ?></strong>
								<?=($post->user_created_name?"<strong>".$post->user_created_name."</strong> says:":"")?>
								</p>
								<div class="forum-msg"><?= $post->message ?></div>
								<?php
							} 
							?>
						</td>
						<td class="reply-links" valign="top">
							<?php
							if ($post->suspended<0) {
								?>&nbsp;<?php
							}
							else {
								// If this is the first item, i.e the thread post
								if($parent==0 || $highlight == " highlight"){
									// Show number of posts in this tread
									if ($parent==0) {
										?><p><?=($post->posts+0)?> post<?=($post->posts==1)?'':'s'?></p><?php
									}
									// Only allow replies to logged in members.
									if ($logged_in) {
										?><p><a href="<?=$site->link?>forum/?post=<?=$post->post_id?>&amp;newpost">Post a reply</a></p><?php
									}
									// Anyone can reorder the list
									?><p><a href="<?=$site->link?>forum/?post=<?=$post_id?>&amp;fvalue=<?=($filtervalue=="asc"?"desc":"asc")?>">View <?=($filtervalue=="asc"?"most recent":"oldest")?> reply</a></p><?php
								}
								// Anyone can report abuse too (long as they do it properly)
								?><p><a href="<?=$site->link?>forum/?post=<?=$post->post_id?>&amp;report">Report as abuse</a></p><?php
							}
							?>
						</td>
					</tr>
					<td colspan="2" class="forum-bottom"></td>
					</tbody>
					</table>
					<? 	
				} 
						
				echo $pag_html;
            } // END OF SWITCH


            // allow posting new posts and responses.
            if( $totalCount>0 && $term!='category' && $mode!="preview" && $addpost){
                $action='add';
                $formTitle = 'Add a new '. ucfirst($term);
                include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/forum.edit.php");
            }


        }

        // No action and no posts
        else if( $totalCount<=0 && !$action && 
            ($logged_in || $public) ){ 
            ?>
            <h3>No <?= $termPl ?> found</h3>
            <?php 
            // Dont allow categories to be created on site.
            if ($term!="category") { 
                ?>
                <p>Please enter a new topic to start off a new conversation.</p>
                <?php
                $action='add';
                $formTitle = 'Add a new '. ucfirst($term);
                include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/forum.edit.php");       
            }
        }
        
        // Show the abuse report form.
		else if ($action == "report") {
			include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/abuse.report.inc.php";
		}
		
		else if (!$logged_in && !$public) {
            ?>
            <p>Only registered users can log in to access this site's forums. From here you can post messages, respond to other people's posts, and share ideas and information.</p>
            <p>If you are not a registered member, and would like to use these forums, please complete the <a href="<?=$site->link?>contact-details/">Contact us</a> form using the link at the top of the page and choose Become a Member in the Contact about selection.</p>
            <?php
        }
        ?>
        
    </div>
    
    <div id="secondarycontent">
    
        <div class="panel panel-grey">
		<?php
        // If we are not logged in then show the log box on the right.
        if (!$logged_in) {
	        ?>
            <h3><span>Login</span></h3>
            <div class="panelheadbottom"></div>
            <?php 
			include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/memberLogin.php'); 
        }
		else {
			?>
            <h3 class="name"><?=$_SESSION['member_name']?></h3>
            <ul>
                <li class="item-1"><a href="<?=$siteLink?>member-login/">Edit your account details</a></li>
                <li class="item-2"><a href="<?=$siteLink?>member-login/?action=subscriptions">Change the emails we send you</a></li>
                <li class="item-3"><a href="<?=$siteLink?>contact-details/">Contact us</a></li>
                <li class="item-4"><a href="<?=$siteLink?>member-login/?action=logout">Sign out</a></li>
            </ul>
            <?php
		}
    	?>
        </div>
	</div>
        

<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_forum.js"></script>


<?php 

include($_SERVER['DOCUMENT_ROOT']."/includes/templates/footer.inc.php"); 


function drawBreadcrumb( $breadcrumb = false ){
	global $id,$parent;
	
	if($breadcrumb) {
		$tmp = '';
		foreach($breadcrumb as $item){
			//echo $item['id'] .' - '. $item['parent'] .'<br />';
			if( ($id==$item['id'] && $parent==$item['parent']) || ( $id==$item['id'] && $item['parent']<='' ) ){
				$tmp .= ' / <strong>'. $item['title'] .'</strong>';
			}else{
				$URLstring = '/forum/'. $item['id'] .'/'. ($item['parent'] ? $item['parent'] : 0 ) .'/';
				$tmp .= ' / <a href="'.$URLstring.'" class="breadcrumb_item">'. $item['title'] .'</a>';
			}
		}
		return $tmp;
	}else{
		return false;
	}
}



function getIDs( $postID=false ){
	global $db;
	
	if( $postID ){
		$query = "SELECT p.post_id, p.parent_id, p2.parent_id as gparent_id FROM forum_posts p
					LEFT JOIN forum_posts p2 ON p.parent_id=p2.post_id WHERE p.post_id=". $postID;
		//echo $query .'<br />';
		if( $data = $db->get_row($query) ){
			return $data;
		}else{
			return false;
		}
	}else{
		return false;
	}
}



?>