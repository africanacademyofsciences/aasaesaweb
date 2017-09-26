<!-- News index(22/2/16) -->

<div class="main-content">
    <div class="container">

        <div class="col-xs-12 col-sm-8" id="primarycontent">

			<?php
			print "<!-- page(".print_r($page, 1).") -->\n";
            print "<!-- private(".$page->private.") mem type(".$_SESSION['member_type'].") -->\n";
            if ($page->private && $page->private!=($_SESSION['member_type']+0) && $mode!="edit" && $mode!="preview") {
                ?>
                <p>This page is only available to logged in members. To access this information please log in below.</p>
                <?php
                echo drawFeedback("notice", $message);
                include $_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php";
            }
            else {
				echo $newsHTML;
			}
			?>
        </div>
        
		<div style="" class="sidebar col-xs-12 col-sm-4 col-md-3 col-md-offset-1" id="secondarycontent">
        
            <!--PANELS-->
            <?php
	
			if ($site->id != 18)
			{
				print $panels->draw(array(), array(13));
			}
			else
			{
				//if ($page->guid != '57f62558045b0')
				//{
					include $_SERVER['DOCUMENT_ROOT']. '/includes/snippets/panels/panel.also-in-this-section.php';
					print $panels->draw(array(15, 11), array(13));
				//}
			}
			?>
        </div>

    </div>
    
</div>
