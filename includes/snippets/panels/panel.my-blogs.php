<h3>My blogs</h3>
<?php
global $blogs, $mode;

if ($mode=="preview") {
	?>
    <p>You cannot view this panel in preview mode, it can only be used when a blog is being viewed.</p>
    <?php
}
else if ($tmp_blog_list = $blogs->drawListByBlogger($_SESSION['member_id'])) {
	?>
    <ul class="blog-list">
    <?=$tmp_blog_list?>
    </ul>
	<?php 
}
?>