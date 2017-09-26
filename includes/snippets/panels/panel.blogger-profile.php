<?php
global $blogs, $member;
if (is_array($blogs->blog)) {
    $profileimg = $member->getMemberImage($blogs->blog['blogger_access_id']);
    $profiletext = $blogs->blog['blogger_profile'];
    if ($profiletext || $profileimg) {
        ?>
        <div class="panel panel-orange">
            <?php
            echo ($profileimg?'<p><img src="'.$profileimg.'" /></p>':"");
            echo ($profiletext?'<p><strong>About: <a href="'.$blogs->drawAuthorLink('', $blogs->blog['blogger_name']).'">'.$blogs->blog['blogger_name'].'</a></strong></p><p>'.$profiletext.'</p>':"");
            ?>
        </div>
        <?php
    }
}
