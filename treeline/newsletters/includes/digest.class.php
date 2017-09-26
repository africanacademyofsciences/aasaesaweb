<?php

//require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
//include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");

	/*
	
	  Digest Class
	  
	  last edited: 14/08/2007 
	  last edited by: by Chris hardy chris.hardy@ichameleon.com
	  changes made: Created the digest clas
	  
	  
	  Table of contents
	  

	  # getPastLength
	  # getFutureLength
	  
	  # getNewsItems
	  # getOppItems
	  # getEventItems
	  
	  #drawTitle
	  #drawNews
	  #drawOpp
	  #drawEvents
	  
	  #createSummary
	  #limitWords
	
	*/
	
function getPastLength($pastLength){
	switch ($pastLength) {
		case "1week":
		    //echo "Make newsletter only show this weeks news.";
			$timeValue = strtotime("-1 week");
		    break;
		case "2weeks":
		    //echo "Make newsletter only show this 2weeks of news.";
			$timeValue = strtotime("-2 weeks");
		    break;
		case "3weeks":
		    //echo "Make newsletter only show this 3weeks of news.";
		    $timeValue = strtotime("-3 weeks");
		    break;
		case "1month":
		    //echo "Make newsletter only show this 3weeks of news.";
		    $timeValue = strtotime("-1 month");
		    break;
		case "2months":
			//echo "";
			$timeValue = strtotime("-2 months");
			break;
		case "3months":
			//echo "";
			$timeValue = strtotime("-3 months");
			break;
		case "4months":
			//echo "";
			$timeValue = strtotime("-4 months");
			break;
	}
		
	return $timeValue;
}


function getFutureLength($futureLength){
	switch ($futureLength) {
		case "1week":
		    //echo "Make newsletter only show this weeks news.";
			$futureLength = strtotime("+1 week");
		    break;
		case "2weeks":
		    //echo "Make newsletter only show this 2weeks of news.";
			$futureLength = strtotime("+2 weeks");
		    break;
		case "3weeks":
		    //echo "Make newsletter only show this 3weeks of news.";
		    $futureLength = strtotime("+3 weeks");
		    break;
		case "1month":
		    //echo "Make newsletter only show this 3weeks of news.";
		    $futureLength = strtotime("+1 month");
		    break;
		case "2months":
			//echo "";
			$futureLength = strtotime("+2 months");
			break;
		case "3months":
			//echo "";
			$futureLength = strtotime("+3 months");
			break;
		case "4months":
			//echo "";
			$futureLength = strtotime("+4 months");
			break;
	}
		
	return $futureLength;
}


function getNewsItems($timeValue){
		global $db, $siteID;
		
		$query = "SELECT p.guid, FROM_UNIXTIME($timeValue), p.title, p.meta_description, ".
		
				 "c.content, UNIX_TIMESTAMP(p.date_published) AS date FROM pages p ".

				 "LEFT JOIN pages p2 on p.parent = p2.guid ".

				 "LEFT JOIN content c on p.guid = c.parent ".

				 "WHERE p2.template = 4 ".

				 "AND c.placeholder = 'content' ".
				 
				 "AND p.date_published >= FROM_UNIXTIME($timeValue) ".
				 
				 "AND p.msv = $siteID ".

				 "ORDER BY p.date_published desc";
				 
		$data = $db->get_results($query);
		
		return $data;
}


function getOppItems($timeValue){
		global $db, $siteID;
	
		$query = "SELECT FROM_UNIXTIME($timeValue), p.title, c.content, UNIX_TIMESTAMP(p.date_published) AS date FROM pages p ".

				 "LEFT JOIN pages p2 on p.parent = p2.guid ".

				 "LEFT JOIN content c on p.guid = c.parent ".

				 "WHERE p2.name = 'opportunities' ".

				 "AND c.placeholder = 'content' ".
				 
				 "AND p.date_published >= FROM_UNIXTIME($timeValue) ".
				 
				 "AND p.msv = $siteID ".

				 "ORDER BY p.date_published desc";
		
		$data = $db->get_results($query);
		
		return $data;	
}


function getEventItems($timeValue){
		global $db, $siteID;
		
		$now = strtotime("now");
		
		$query = "SELECT title, description, venue, UNIX_TIMESTAMP(start_date) AS start_date, ".
				 
				 "UNIX_TIMESTAMP(end_date) AS end_date FROM `events` e ".
				 
				 "WHERE e.start_date <= FROM_UNIXTIME($timeValue) ".
				 
				 "AND e.start_date > FROM_UNIXTIME($now) ".
				 
				 "AND e.status = 1 ".
				 
				 "AND e.site_id = $siteID ".

				 "ORDER BY e.start_date desc";
				 
		$data = $db->get_results($query);
		
		return $data;	
}


function drawTitle($title){
		$title = ($title) ? $title : $_POST['action']." Digest";
		$text = "<h1>{$title}</h1>";
		$text .= "<hr />";
	return $text;
}


function drawNews($data, $title){
	
	$now = strtotime("now");
	$text = drawTitle($title);
	
	foreach ($data as $digestdata){
		$page = new Page();
		$text .= "<h2>";
		$text .= $digestdata->title;
		$text .= "</h2> <p>Posted on ".date("d/m/Y", $digestdata->date)."</p>";
		$text .= createSummary($digestdata->content, $digestdata->meta_description);
		$text .= '<p class="readmore"><a href="'.$page->drawLinkByGUID($digestdata->guid).'?utm_source=digest'.date("HMY", $now).'&utm_medium=email" title="Continue reading '.$digestdata->title.'">Find out more</a></p>'."\n\t";
		$text .= "<hr />";
	}
	
	return $text;
}


function drawOpp($data, $title){
	
	$text = drawTitle($title);
	
	foreach ($data as $digestdata){
		$text .= "<h2>";
				$text .= $digestdata->title;
				$text .= "</h2>";
				$text .= $digestdata->content;
				$text .= "<hr />";
	}
	
	return $text;
}


function drawEvents($data, $title){
	
	$text = drawTitle($title);
	
	foreach ($data as $digestdata){
		$text .= "<h2>";
		if ($digestdata->venue){
			$text .= $digestdata->title." at ".$digestdata->venue;
		} else {
			$text .= $digestdata->title;
		}
		$text .= "</h2>";
		$text .= "<p>Starts: ".date("d/m/Y, H:i", $digestdata->start_date);
		$text .= "<br />Ends: ".date("d/m/Y, H:i", $digestdata->end_date)."</p>";
		$text .= "<p>".nl2br($digestdata->description)."</p>";
		$text .= "<hr />";
	}
	
	return $text;
}


function createSummary($content,$meta_desc=''){
			// create a sumamry beased on a subsection of the content or the meta tag
			global $db;
			
			if($meta_desc){
				$html = $meta_desc;
			}
			else{
				$html = limitWords($content, 50);
			}
			
			return $html;
			
}
		
?>