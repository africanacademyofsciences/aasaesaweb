<h3><?=$page->drawLabel("blog_pan_other_title", "Other posts in this blog")?></h3>
<?php
// If we loaded a blog and its not owned by the currently logged in member 
// show other blogs by that author
global $blogs, $mode;

if ($mode=="preview") {
	?>
    <p>You cannot view this panel in preview mode, it can only be used when a blog is being viewed.</p>
    <?php
}
else if ($tmp_blog_list = $blogs->drawListByID($blogs->blog['id'], true)) {
	?>
	<ul class="blog-list">
	<?=$tmp_blog_list?>
	</ul>
	<?php 
}
else {
	?>
    <p>There are no other posts in this blog yet.</p>
    <?php
}
?>