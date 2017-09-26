<?php

if (!is_object($news)) global $news;

// Need to find the blog index page (or news???)

// We should already know the page template but we don't 

$row = $db->get_row("SELECT parent, template FROM pages WHERE guid='$pageGUID'");
if ($row->template==4) $indexguid = $pageGUID;
else $indexguid = $row->parent;

?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

	<!-- news calendar -->
	<?=$news->drawCalendar($indexguid, '6 MONTH')?>
	<!-- // news calendar -->
    
    <!--
    <div class="panel panel-info">
		<div class="panel-heading" role="tab" id="headingTwo">
	        <h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">Date 2</a></h4>
        </div>
		<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
        	<div class="panel-body">
            blogs in this month
            </div>
        </div>
    </div>
    
    <div class="panel panel-info">
		<div class="panel-heading" role="tab" id="headingThree">
	        <h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="true" aria-controls="collapseThree">Date 3</a></h4>
        </div>
		<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
        	<div class="panel-body">
            blogs in this month
            </div>
        </div>
    </div>
	-->

</div>