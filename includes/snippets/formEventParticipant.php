<?php
	$MAX_ITEMS=3;
	// Do we need to run any searchings?
	if ($_SERVER['REQUEST_METHOD']=="POST") {
	
		$ev_name=$_POST['name'];
		$ev_title=$_POST['event'];
	
		if ($ev_name) $where.="CONCAT(m.firstname,' ',m.surname) LIKE '%$ev_name%' AND ";
		if ($ev_title) $where.="p.title LIKE '%$ev_title%' AND ";
		if (!$where) {
			$err_msg[].="You did not enter a name or event title";
			$err_msg[].="You can enter part of someones name or part of the event they are participating in if you are unsure exactly what to search for";
		}			
		else {
			$where.=" NOW()<e.cutoff_date AND p.date_published is not null ";
			$from="FROM members m
				INNER JOIN event_entry ee ON m.member_id=ee.member_id
				LEFT JOIN events e on ee.event_guid=e.guid
				LEFT JOIN pages p on ee.event_guid=p.guid
				WHERE ";
			if ($ev_name) $orderby.="m.firstname, m.surname, e.start_date DESC";
			else if ($ev_title) $orderby="e.start_date DESC, e.title, m.firstname, m.surname";
			$query="SELECT p.title, concat(m.firstname,' ',m.surname) as name, 
				date_format(e.start_date, '%D %M %Y') as ev_date,
				ee.event_guid, ee.pp_guid ".$from.$where." ORDER BY ".$orderby;
			//print "q($query)<br>";
			if ($results=$db->get_results($query)) {
				foreach ($results as $result) {
					// Are we showing events or people
					if ($ev_name) {
						if ($current_name != $result->name) {
							$current_name=$result->name;
							if (count($currentList)) {
								foreach($currentList as $currentItem) $html.=$currentItem;
								$currentList=array();
							}
							$listcount++;
							if ($itemcount<$MAX_ITEMS) $stylehtml.='<style type="text/css">div#ev-list ul li span#toggle-'.($listcount-1).'{display:none;}div#ev-list ul#ev-list-'.($listcount-1).'{display:block;}</style>'."\n";
							$itemcount=0;
							$html.='</ul></li><li><h2>'.$result->name.'</h2> <span class="toggle" id="toggle-'.$listcount.'"><a href="javascript:togglelist(\'ev-list-'.$listcount.'\','.$listcount.');">show/hide registered events</a></span></li>'."\n".'<li><ul class="ev-list" id="ev-list-'.$listcount.'">';
						}
						$itemcount++;
						$currentList[]='<li><a href="'.$page->drawLinkByGUID($result->pp_guid).'">'.$result->title.'</a> ('.$result->ev_date.')</li>';
					}
					else if ($ev_title) {
						if ($current_title != $result->title) {
							$current_title=$result->title;
							if (count($currentList)) {
								foreach($currentList as $currentItem) $html.=$currentItem;
								$currentList=array();
							}
							$listcount++;
							if ($itemcount<$MAX_ITEMS) $stylehtml.='<style type="text/css">div#ev-list ul li span#toggle-'.($listcount-1).'{display:none;}div#ev-list ul#ev-list-'.($listcount-1).'{display:block;}</style>'."\n";
							$itemcount=0;
							$html.='</ul></li><li><h2>'.$result->title.'('.$result->ev_date.')</h2> <span class="toggle" id="toggle-'.$listcount.'"><a href="javascript:togglelist(\'ev-list-'.$listcount.'\','.$listcount.');">show/hide registered participants</a></span></li>'."\n".'<li><ul class="ev-list" id="ev-list-'.$listcount.'">';
						}
						$itemcount++;
						$currentList[]='<li><a href="'.$page->drawLinkByGUID($result->pp_guid).'">'.$result->name.'</a></li>';
					}						
				}
				if ($html) {
					//print "got listcount($listcount) items($itemcount)<br>";
					if ($listcount==1) $stylehtml='<style type="text/css">div#ev-list ul li span.toggle{display:none;}div#ev-list ul.ev-list{display:block;}</style>'."\n";
					else if ($itemcount<$MAX_ITEMS) $stylehtml.='<style type="text/css">div#ev-list ul li span#toggle-'.($listcount).'{display:none;}div#ev-list ul#ev-list-'.($listcount).'{display:block;}</style>'."\n";
					$html='<p style="padding-top:10px;padding-bottom:0px;margin:0;">Found '.$listcount.' match'.(($listcount==1)?"":"es").'</p>
<ul>
	'.substr($html, 10).'
	'.implode($currentList).'
</ul>
</li></ul>';
				}
			}
			else $err_msg[]="No matches found for this search";
		}
	}
	
?>

<?php
	if ($err_msg) {
		echo drawFeedback("error", $err_msg);
	}
?>

<script type="text/javascript">
	var liststatus=new Array();
	function togglelist(name, id) {
		//alert("toggle - "+name+" id - "+id);
		if (!liststatus[id]) {
			liststatus[id]=1;
			document.getElementById(name).style.display="block";
		}
		else {
			liststatus[id]=0;
			document.getElementById(name).style.display="none";
		}
	}
</script>

<form id="event-search-form" action="<?=$page->drawLinkByGUID($page->getGUID())?>" class="contact" method="post">
<fieldset class="border">

    <legend>Find an individual to sponsor</legend>
	<div class="ie-fix">						
        <label for="f_name">Persons name:</label>
        <input type="text" name="name" id="f_name" class="text" value="<?=$_POST['name']?>" />
        <label for="f_event">Event title:</label>
        <input type="text" name="event" id="f_event" class="text" value="<?=$_POST['event']?>" />
        <div class="ie-fix2">
	        <label for="f_submit" style="visibility:hidden;">Search</label>
    	    <input type="submit" class="submit" value="Search" />
        </div>
	</div>
</fieldset>
</form>

<?php 
	if (!$err_msg && $html) {
		?>
        <div id="ev-list">
        <?=$stylehtml?>
		<?=$html?>
        </div>
        <?php
	}
?>
