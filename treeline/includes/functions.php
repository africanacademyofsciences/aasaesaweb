<?php
	/*
		Treeline Common Functions
		
		These functions are used often throughout Treeline
		
		
		Contents
		
		read()
		redirect()
		drawRSSFeed()
		Date-related:
			getUFDateTime()
			getDDMMYYYY()
			getUFDate()
			getUFTime()
			addDays()
			removeDays()
			howManyDays()
		cleanString()
		drawPagination()
		getQueryLimits()
		drawFeedback()
		createFeedbackURL()
		niceError()
		limitWords()
		highlightSearchTerms()
		createCache()
		clearCache()
		formatFilesize()
		getBackgroundImage()
		validateContent()
		
	*/

	/* 
		Read():
		check for variable: if it doesn't exist return it's user set default value
	*/
	function read($object,$variable,$default='') {
		if (!isset($object[$variable])) {
			return $default;
		}
		return $object[$variable];
	}
	
	/* 
		Redirect()
		Send a user to a new place
	*/
	function redirect($link, $code=0) {
		global $DEBUG;
		if ($DEBUG) {
			echo '<p style="font-family:Tahoma, Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; padding: 10px; border: 1px solid #c00; background-color: #ff9;">Redirect: <a href="'.$link.'" style="text-decoration: none; color: #c00">click here</a></p>';
		}
		else {
			if ($code==301) header ('HTTP/1.1 301 Moved Permanently'); // 301 redirect: SEO friendly
			header("Location: $link\n\n");
			exit();
		}
	}
	/*
	function redirect($link) {
		global $DEBUG;
		if ($DEBUG) {
			echo '<p style="font-family:Tahoma, Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; padding: 10px; border: 1px solid #c00; background-color: #ff9;">Redirect: <a href="'.$link.'" style="text-decoration: none; color: #c00">click here</a></p>';
		}
		else {
			//header ('HTTP/1.1 301 Moved Permanently'); // 301 redirect: SEO friendly
			header("Location: $link\n\n");
			exit();
		}
	}
	*/


	function setClass($c) {
		switch ($c) {
			case "contact-details": return "contact-us"; break;
			default: return $c; break;
		}
	}


	function testinject($str) {
		// prevents email injection attacks
		$safe = (preg_replace(array("/\r/", "/\n/", "/%0a/", "/%0d/", "/Content-Type:/i", "/bcc:/i","/to:/i","/cc:/i" ), "", $str ) );
		if ($safe != $str) {
			$filename = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/ti-".date("dmyhis", time()).".txt";
			mail('phil@treelinesoftware.com','Mail injection attempt','On ' . $_SERVER['HTTP_HOST'] . ' in ' . $_SERVER['PHP_SELF'] . ' at line '. __LINE__.' from ' . $_SERVER['REMOTE_ADDR'] .'. String [when cleaned] was '. $safe. "\n\nOrginal string written to: ".$filename);
			file_put_contents($filename, $str);					
		}
		return $safe;
	}

	/* swaps array key/values round so the value becomes the key */
	function array_switch($array=false){
		// switch values/keys round...
		if( isset($array) && is_array($array) && count($array)>0 ){
			$tmp = array();
			foreach( $array as $key => $value ){
				$tmp[$value] = $key;
			}
			return $tmp;
		}
		return false;
	}



	/* 
		drawRSSFeed()
		Convert RSS feed into unordered list
	*/
	
	function drawRSSFeed($link, $showDate = false, $total = 5) {
		//print "dRSSF($link, $showDate, $total)<br>\n";
		$feedXml = @simplexml_load_file($link); 
		$i = 1;
		$html='';
		if ($feedXml) {
			//print_r($feedXml);
			foreach ($feedXml->channel->item as $article){
				if($i <= $total){
					$html .= '<li>';
					if($showDate === true){
						$html .= '<em class="date">'.getDDMMYYYY($article->pubDate).'</em> ';
					}
					//print "got title(".$article->title.")<br>\n";
					$html .= '<a href="' . (string)$article->link . '" target="_blank">' . (string)(str_replace("�", "", $article->title)) . '</a>';
					$html .= '</li>'."\n";
				   	$i++; 
				}
			} 
		}
		if ($html) $html = '<ul class="feed">'.$html.'</ul>';
		return $html;
	}

	/*
	The date function can't really use a timestamp straight from the data,
	so run it through this to get a useable version....
	*/
	function getDateFromTimestamp( $timestamp ){
		$timestamp = strtotime( $timestamp );
		//echo date('H',$timestamp) .', '. date('i',$timestamp) .', '. date('s',$timestamp) .', '. date('m',$timestamp) .', '. date('d',$timestamp) .', '. date('Y',$timestamp) .'<br />';
		return mktime(date('H',$timestamp), date('i',$timestamp), date('s',$timestamp), date('m',$timestamp) , date('d',$timestamp) , date('Y',$timestamp) );
	}

	
	/*
		getUFDateTime()
		get date and time in this format 3rd Nov 2006 at 15:33
	*/
	function getUFDateTime($date){
		if($date != '0000-00-00 00:00:00' && $date != NULL){
			$date = strtotime($date);
			$date = date('jS F Y \a\t H:i',$date);
		}
		else{
			$date = '';
		}
		return $date;
	}
	
	/*
		getDDMMYYYY()
		get date in this format dd/mm/yyyyy
	*/
	function getDDMMYYYY($date){
		if($date != '0000-00-00 00:00:00' && $date != NULL){
			$date = strtotime($date);
			$date = date('d/m/Y',$date);
		}
		else{
			$date = '';
		}
		return $date;
	}


	/*
		getUFDate()
		get date in this format 3rd November 2006
	*/
	function getUFDate($date){
		$date = strtotime($date);
		$date = date('jS F Y',$date);
		return $date;
	}


	/*
		getUFTime()
		get time in this format 15:33
	*/
	function getUFTime($time){
		$time = strtotime($time);
		$time = date('H:i',$time);
		return $time;
	}

	// Really do we have to?
	function smartTruncate($str, $maxLen){
		if(strlen($str) < $maxLen) return($str);
		else return substr($str, 0, $maxLen - 4) . " ...";
	}


	// 15/12/2008 Comment
	// Adds a record to the history table which tracks 
	// all required Treeline actions.
	function addHistory($user_id, $action, $guid='', $info='', $table='') {
		global $db, $site;
		//print "aH($user_id, $action, $guid, $info, $table) for site(".$site->id.")<br>\n";
		
		$msv = is_object($site)?$site->id:$_SESSION['treeline_user_site_id'];
		
		if (!$action && !$guid && !$info) {
			$msg="called with no data??? \n";
			$msg.="ah($user_id, $action, $guid, $info, $table)<br>\n";
			$msg.="ref(".$_SERVER['HTTP_REFERER'].")\n";
			mail("phil.redclift@ichameleon.com". $site->name." add history", $msg);
			return false;
		}
		
		switch(strtolower($action)) {
			case "publish" :
				global $page;
				$page->releaseLock($user_id);
				break;
		}
		
		$query = "INSERT INTO history (date_added, user_id, action, guid, info, `table`, msv)	
			VALUES (NOW(), ".($user_id+0).", '".strtolower($action)."', '$guid', '".$db->escape($info)."', '$table', ".($msv+0).")";
		//print "$query<br>\n";
		
		if ($db->query($query)) {
			return $db->insert_id;
		}
		return false;
	}
		

	/*
		addDays()
		add a set number of days to a given date and return the new date
	*/
	function addDays($date, $daysAdded){
		
		$totalSeconds = 86400*$daysAdded;// 86400 seconds in a day
		$date = strtotime($date);
		$newDate = $date+$totalSeconds;
		
		$newDate = date('Y\-m\-d \0\0\:\0\0\:\0\0',$newDate);
		
		return $newDate;
	}
	
	
	/*
		removeDays()
		subtract a set number of days to a given date and return the new date
	*/
	function removeDays($date, $daysRemoved){
		
		$totalSeconds = 86400*$daysRemoved; // 86400 seconds in a day
		$date = strtotime($date);
		$newDate = $date-$totalSeconds;
		
		$newDate = date('Y\-m\-d \0\0\:\0\0\:\0\0',$newDate);
		
		return $newDate;
	}


	/*
		howManyDays()
		returns the number of days between 2 given dates
	*/
	function howManyDays($firstDate, $secondDate = NULL){
		
		if($secondDate == NULL){ // if no second date present
			$secondDate = date('Y-m-d H:i:s'); // use today as the 2nd date
		}
	
		// convert format
		$firstDate = strtotime($firstDate);
		$secondDate = strtotime($secondDate);
	
		
		$offset = $secondDate-$firstDate;
		$offsetInSeconds = $offset/86400; // 86400 seconds in a day
		
		$newDate = round($offsetInSeconds).' days';
	
		return $newDate;
	}
	
	
	/*

		cleanString()
		CREATE Treeline's famous URL-friendly strings
	*/
	function cleanString($string){
		// Strip everything but letters, numbers and spaces from the title
		$string = preg_replace("/[^A-Za-z0-9 ]/", "", $string);
		// Replace spaces with dashes
		$string = str_replace(" ",'-',$string);
		$string = strtolower($string);
		return $string;
	}
	

	function removeAccents($str)
	{
		//print "<!-- rA($str) -->\n";
		$newstr = strtr($str,
			"���������������������������������������������������������������������",
			"SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy"
			);
		//print "<!-- New($newstr) -->\n";
		return $newstr;
	}
	


	/*
		drawPagination()
		create links as unordered list to all pages in a set e.g. search results
	*/
	
	
	/*
		drawPagination()
		create links as unordered list to all pages in a set e.g. search results
	*/
	function drawNewPagination($totalResults, $perPage, $currentPage=1, $currentURL = NULL){
	
	//print "dNP($totalResults, $perPage, $currentPage, $currentURL)<br>";
	$currentURL = (!$currentURL) ? $_SERVER['PHP_SELF'] : $currentURL;

	
		$totalpages = ceil($totalResults / $perPage);
		//print "got total pages($totalpages)<br>\n";
		if(!$currentPage || $currentPage==0){
			$currentPage = 1;
		}

		for( $i=0; $i<strlen($currentURL); $i++ ){
			$tmp[] = $currentURL[$i];
		}
	
		if( (!in_array('?',$tmp) && !in_array('&',$tmp)) ){
			$currentURL .= '?';
		}else{
			$currentURL .= '&amp;';
		}
		
		$html = '<ul class="pagination">'."\n";
		
		if ($totalpages == 1) {
			return $html;
		}
		// First link
		if($currentPage > 2) $html .= '<li class="bookend"><a href="'.$currentURL.'">First</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		else $html .= '<li class="bookend inactive">First</li>'."\n";

		// Previous link
		if ($currentPage > 1) $html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage-1).'">Previous</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		else $html .= '<li class="bookend inactive">Previous</li>'."\n";
		
		if($totalpages<=10){
			$pagestart=1;
			$pageend = $totalpages;
		}
		else if($currentPage<($totalpages-5)){
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		else if( ($currentPage>($totalpages-5)) && ($currentPage<=$totalpages) ){
			//print "curr($currentPage) > ($totalpages - 5) && ($currentPage <= $totalpages)<br>\n";
			$pagestart = $currentPage-(9-($totalpages-$currentPage));
			$pageend = ($currentPage+($totalpages-$currentPage));
			//print "Page end = $pageend<br>\n";
		}
		else{
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		
		// for debugging...
		//echo $pagestart.' > '.$pageend.' - ['.$page.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			//// We don't want to show all pages, just a few either side of the page we're on.
			//// If we keep the page we're on centrally (position 5) then when we get to position 6
			//// we'll need to cycle the whole lot down...
			$class = ($i==$pageend) ? ' bookend' : '';
			if ($i != $currentPage) {
				$html .= '<li class="page'. $class .'"><a href="'. $currentURL.'p='. $i.'">'.$i.'</a></li>'."\n"; 
			} 
			else {
				$html .= '<li class="page selected'. $class .'"><strong>'.$i.'</strong></li>'."\n";
			}

		}

		// Next page		
		if ($currentPage < $totalpages) $html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage+1).'">Next</a></li>'."\n";
		else $html .= '<li class="bookend inactive">Next</li>';

		// Last link
		if($currentPage < ($totalpages-1)) $html .= '<li class="bookend"><a href="'. $currentURL.'p='. $totalpages.'">Last</a></li>'."\n";
		else $html .= '<li class="bookend inactive">Last</li>'."\n";
		
		$html .= '</ul>'."\n";
			
		return $html;

}	


	function drawPagination($totalResults, $perPage=10, $currentPage=1, $currentURL='') {
		global $page;
		//print "drawPagination($totalResults, $perPage, $currentPage, $currentURL)<br>";
		$totalpages = ceil($totalResults / $perPage);

		if(!$currentPage || $currentPage==0){
			$currentPage = 1;
		}

		$arrow = array();
		$arrow['first'] = "&laquo ";
		$arrow['previous'] = "&laquo ";
		$arrow['next']= " &raquo";
		$arrow['last']= " &raquo";
		$arrow = array();	// Don't use arrows.

		$tmp=array();
		for( $i=0; $i<strlen($currentURL); $i++ ){
			$tmp[] = $currentURL[$i];
		}
	
		if(!in_array('?',$tmp) && !in_array('&',$tmp) ){
			$currentURL .= '?';
		}else{
			$currentURL .= '&amp;';
		}
		
		
		if ($totalpages == 1) return $html.'';
		
		// First page
		//'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		$label_first = $page->drawGeneric("first", 1);
		if($currentPage > 1) $html .= '<li class="bookend first"><a href="'.$currentURL.'page=1">'.$arrow['first'].$label_first.'</a></li>'."\n"; 
		else $html .= '<li class="bookend disabled first"><span>'.$arrow['first'].$label_first.'</span></li>'."\n";
		
		// Previous page
		//'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		$label_previous = $page->drawGeneric("previous", 1);
		if ($currentPage > 1) $html .= '<li class="bookend"><a href="'. $currentURL.'page='. ($currentPage-1).'">'.$arrow['previous'].$label_previous.'</a></li>'."\n"; 
		else $html .= '<li class="bookend disabled"><span>'.$arrow['previous'].$label_previous.'</span></li>'."\n";
		
		//print "got total($totalpages) current($currentPage)<br>\n";
		if($totalpages<=10){
			$pagestart=1;
			$pageend = $totalpages;
		}
		else if($currentPage<($totalpages-5)){
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		else if( ($currentPage>($totalpages-5)) && ($currentPage<=$totalpages) ){
			$pagestart = $currentPage-(9-($totalpages-$currentPage));
			$pageend = ($currentPage+($totalpages-$currentPage));
		}
		else{
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		//print "paginate from $pagestart to $pageend<br>\n";
		
		// for debugging...
		//echo $pagestart.' > '.$pageend.' - ['.$page.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			//// We don't want to show all pages, just a few either side of the page we're on.
			//// If we keep the page we're on centrally (position 5) then when we get to position 6
			//// we'll need to cycle the whole lot down...
			$class = ($i==$pageend) ? ' bookend':'';
			if ($i != $currentPage) {
				$html .= '<li class="page'.$class.'"><a href="'. $currentURL.'page='. $i.'">'.$i.'</a></li>'."\n"; 
			} else {
				$html .= '<li class="page'.$class.' selected disabled"><span>'.$i.'</span></li>'."\n";
			}

		}
		
		// Next link
		$label_next = $page->drawGeneric("next", 1);
		if ($currentPage < $totalpages) $html .= '<li class="bookend"><a href="'. $currentURL.'page='. ($currentPage+1).'">'.$label_next.$arrow['next'].'</a></li>'."\n";
		else $html .= '<li class="bookend disabled"><span>'.$label_next.$arrow['next'].'</span></li>';
			
		// Last page
		$label_last = $page->drawGeneric("last", 1);
		if($currentPage < ($totalpages-1)) $html .= '<li class="bookend last"><a href="'. $currentURL.'page='. $totalpages.'">'.$label_last.$arrow['last'].'</a></li>'."\n";
		else $html .= '<li class="bookend disabled last"><span>'.$label_last.$arrow['last'].'</span></li>'."\n";
		
		$html = '<ul class="pagination">
	'.$html.'
</ul>
';
			
		return $html;
	}	
	
	function drawFLPagination($totalResults, $perPage=10, $currentPage=1, $currentURL='') {
		global $page;
		$totalpages = ceil($totalResults / $perPage);
		//print "drawPagination($totalResults, $perPage, $currentPage, $currentURL) totalpages($totalpages)<br>";
		if(!$currentPage || $currentPage==0){
			$currentPage = 1;
		}

		$arrow = array();
		$arrow['first'] = "&laquo ";
		$arrow['previous'] = "&laquo ";
		$arrow['next']= " &raquo";
		$arrow['last']= " &raquo";
		$arrow = array();	// Don't use arrows.

		$tmp=array();
		for( $i=0; $i<strlen($currentURL); $i++ ){
			$tmp[] = $currentURL[$i];
		}
	
		if(!in_array('?',$tmp) && !in_array('&',$tmp) ){
			$currentURL .= '?';
		}else{
			$currentURL .= '&amp;';
		}
		
		
		if ($totalpages == 1) return $html.'';
		
		
		// Previous page
		//'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		$label_previous = $page->drawGeneric("previous", 1);
		if ($currentPage > 1) $html .= '<li class="bookend prev-open"></li><li class="bookend"><a href="'. $currentURL.'page='. ($currentPage-1).'">Previous</a></li>'."\n"; 
		else $html .= '<li class="inactive prev-open"></li><li class="bookend inactive">Previous</li>'."\n";

		// First page
		//'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		$label_first = $page->drawGeneric("first", 1);
		if($currentPage > 3 && $totalpages>4) {
			$html .= '<li class=""><a href="'.$currentURL.'page=1">1</a></li>'."\n"; 
			$html .= '<li class="">...</li>'."\n"; 
		}
		
		
		//print "got total($totalpages) current($currentPage)<br>\n";
		$pagestart = $currentPage - 1;
		if ($pagestart<1) {$pagestart++; }
		$pageend = $pagestart + 3;
		
		if ($currentPage+2 > $totalpages) {
			$pageend -= ($currentPage+2) - $totalpages;
			$pagestart = $pageend - 3;
			if ($pagestart<1) $pagestart = 1;
		}
		//print "paginate from $pagestart to $pageend<br>\n";

		//echo $pagestart.' > '.$pageend.' - ['.$page.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			if ($i != $currentPage) {
				$html .= '<li class="page'.$class.'"><a href="'. $currentURL.'page='. $i.'">'.$i.'</a></li>'."\n"; 
			} else {
				$html .= '<li class="page'.$class.' selected inactive"><span>'.$i.'</span></li>'."\n";
			}

		}
		
		// Last page
		if($pageend < $totalpages) {	
			if($pageend < $totalpages-1) $html .= '<li>...</li>'."\n";
			$html .= '<li><a href="'.$currentURL.'page='. $totalpages.'">'.$totalpages.'</a></li>'."\n";
		}

		// Next link
		$label_next = $page->drawGeneric("next", 1);
		if ($currentPage < $totalpages) $html .= '<li class="bookend last"><a href="'. $currentURL.'page='. ($currentPage+1).'">Next</a></li><li class="bookend next-close"></li>'."\n";
		else $html .= '<li class="last inactive">Next</li><li class="next-close inactive"></li>'."\n";
			
		
		$html = '<ul class="pagination">
	'.$html.'
</ul>
';
			
		return $html;
	}	
	
	


function pl($msg) {
	$site="csipu";
	$t=date("d/m/y H:i:s", time());
	$msg=$cur.$t." ".$msg."\n";
	$f=$_SERVER['DOCUMENT_ROOT']."/silo/tmp/$site-".date("dmy", time()).".log";
	if ($fp=fopen($f, "at")) {
		fputs($fp, $msg."\n");
		fclose($fp);
	}
	else if ($_SERVER['SERVER_NAME']!=$site) {
		$subject="DbgMsg from $site";
		mail("phil.redclift@ichameleon.com", $subject, $msg);	
	}
	else print "$msg<br>\n";
}


	/*
		getQueryLimits()
		 returns the limits for the query based on the current page and the total to be shown per page
	*/
	function getQueryLimits($perPage, $currentPage){
		if($currentPage == 0 || $currentPage == 1 || !$currentPage){ // current page is 1 or 0 or is missing
			$limit = '0,'.$perPage;
		} 
		else{
			$limit = ($currentPage-1) * $perPage.','.$perPage; // work out limits
		}

		return $limit;
	}
	
	// returns the limits for the query based on the current page and the total to be shown per page
	function getShowingXofX($perPage, $currentPage, $pageTotal, $grandTotal){
		global $page;
		//print "gSXoX($perPage, $currentPage, $pageTotal, $grandTotal)<br>\n";
		if($currentPage == 0 || $currentPage == 1 || !$currentPage){ // current page is 1 or 0 or is missing
			$perPage = ($pageTotal < $perPage) ? $pageTotal : $perPage;
			$limit =  $page->drawGeneric('showing', 1).' 1 '.$page->drawGeneric("to").' '.$perPage.' '.$page->drawGeneric("of").' '.$grandTotal; // work out limits
		} 
		else $limit = $page->drawGeneric('Showing', 1).' '.(($currentPage-1)*$perPage).' '.$page->drawGeneric("to").' '.((($currentPage-1) * $perPage)+$pageTotal).' '.$page->drawGeneric("of").' '.$grandTotal; 
		return $limit;
	}
	
	

	/*
		drawFeedback()
		// create user feedback
	*/
	function drawFeedback($type = 'feedback', $content ='', $inline_style=''){
		global $page;
		//print "dF($type, content, is)<br>\n";
		//print "Treeline(".$_SERVER['HTTP_REFERER'].")<br>\n";
		$label_prefix = preg_match("|/treeline/|", $_SERVER['HTTP_REFERER'])?"tl_":"";
		//print "Labels(".print_r($labels, true).")<br>\n";
		if (!$content) return;
		//print "content ($content)<br>\n";
		
		if (!$type) $type="feedback";
		if ($content) {
			switch ($type) {
				case 'info':
				case 'notice' :
					$title = $page->drawLabel($label_prefix."generic_notice", "Information");
					$typeClass=" notice";
					break;
				case 'error':
				case "warning" :
					$title = $page->drawLabel($label_prefix."generic_warning", "Warning");
					if (!$title) $title = "There was a problem";
					$typeClass = ' error';
					break;
				case 'welcome' : 
					// We dont need to worry about labels here as this will only ever
					// happen in english
					$title = "Welcome ".($_SESSION['treeline_user_logins']>1?"back ":"")."to Treeline";	
					$title = $page->drawLabel($label_prefix."feed_welcome", $title);
					$typeClass = ' success';
					break;
				case 'success':
					$title = $page->drawLabel($label_prefix."generic_success", "Success");
					$typeClass = ' '.$type;
					break;
					
				default:
					$title = $page->drawLabel($label_prefix."generic_feedback", "Feedback");
					$typeClass = '';
					break;
			}
			
			if (!$title) $title[0] = mb_strtoupper($title[0]); 
			$html ='<div class="feedback'.$typeClass.'" '.($inline_style?'style="'.$inline_style.'"':"").'>'."\n";
			if ($label_prefix=="tl_" && $type=="error") $html.='<p class="reportBug"><a href="/treeline/bugs/?action=create&bug=bug">'.$page->drawLabel($label_prefix."fb_report_bug", "Report this as a bug").'</a></p>';
			if ($label_prefix=="tl_") $html .= '<p class="hideFeedback"><a href="#">'.$page->drawLabel($label_prefix."help_hide", "Hide this message").'</a></p>';
			$html .= '<h3>'.$title.'</h3>'."\n";
			
			if(is_array($content) && sizeof($content) > 1){ // more than one message in feedback so loop
				$html .= '<ul>'."\n";
				foreach($content as $item){
					$html .= '<li>'.stripslashes($item).'</li>'."\n";
				}
				$html .= '</ul>'."\n";
			} 
			else if(is_array($content) && sizeof($content) == 1){ // an arry with only only one item so show it
				$html .= '<p>'.stripslashes($content[0]).'</p>'."\n";
			}
			else{ // only one item so show it
				$html .= '<p>'.stripslashes($content).'</p>'."\n";
			}
			$html .= '</div>'."\n";
		} 
		 else{
			$html  = '';
		}
		
		return $html;
	}
	
	// 22/12/2008 Phil Redclift
	// Draw a treelineBox with a list in it.
	function treelineList($content, $title='', $bg='', $ulclass='', $width=735, $height=0, $helpid=0, $id='', $xclass='') {
		if (!$ulclass) $ulclass="submenu";
		if (is_array($content)) {
			foreach ($content as $item) $html.='<li>'.$item.'</li>'."\n";
		}
		else $html=$content;
		$html='<ul class="'.$ulclass.'">'.$html.'</ul>'."\n";
		return treelineBox($html, $title, $bg, $width, $height, $helpid, $id, $xclass);
	}

	// 18/12/2008 Phil Redclift
	// Generate a pretty box in Treeline.
	// Boxes may be any height or width but default to the page width
	// Optionally the box can be assigned an id and additional classes
	
	// Useful xclass variables : 
	// tl-box-left - adds a 20px margin to the right of the box
	// tl-box-right - adds a 20px margin to the left of the box
	function treelineBox($content, $title='', $bg='', $width=735, $height=0, $helpid=0, $id='', $xclass='') {
		global $help, $page;
		//print "<!-- tB(content, $title, $bg, $width, $height, $helpid, $id, $xclass) -->\n";
		$html='';
		
		if (!$content) return false;
		
		if ($width < 200) $width=735;
		if ($width > 735) $width=735;
		if ($height < 100) $height=0;
		
		$headwidth = 'width:'.($width-($helpid>0?154:24)).'px;';
		$footwidth = 'width:'.($width).'px;';
		
		// Generate basic box html
		$html ='
<div class="tl-box'.($xclass?' '.$xclass:'').'" '.($id?'id="'.$id.'"':'').' style="'.($width?'width:'.($width+0).'px;':'').($height?'height:'.($height+0).'px;':'').($xclass?$xclass:"").'" >
	<div class="tl-head'.($title?' tl-head-'.$bg:'').'">
		<span class="tl-head-left"></span>
		<h2 class="tl-head-right" style="width:'.($width-24).'px;">
			<span style="'.$headwidth.'">'.$title.'</span>
			'.($helpid>0?'<a href="javascript:openhelp(\''.$help->helpLinkByID($helpid).'\')" class="tl-help-link">'.$page->drawLabel("tl_help_get_help", "Get help with this").'</a>':"").'
		</h2>
	</div>
	<div class="tl-content" style="'.($width!=730?'width:'.($width-46).'px;':'').($height?'height:'.($height-76).'px;':'').'" >
		'.$content.'
	</div>
	<div class="tl-footer" style="'.$footwidth.'" >
		<span class="tl-footer-left"></span>
		<span class="tl-footer-right"></span>
	</div>
</div>
';
		return $html;
	
	}

	/*
		createFeedbackURL()
		convert user feedback into URL friendly variables
	*/
	
	function createFeedbackURL($feedbackType,$message){
		if(is_array($message)){
			$messageURL = '';
			foreach($message as $msg){
				$messageURL .= '&message[]='.urlencode($msg);
			}
		}
		else{
			$messageURL = '&message='.urlencode($message);
		}	
			$newURL = 'feedback='.$feedbackType.$messageURL;
		return $newURL;
	}
	
	/*
		niceError()
		draws a nice looking so users aren't distressed
	*/
	
	function niceError($content){
		
		$html ='<div class="feedback error">'."\n";
		$html .= '<h3>Technical error</h3>'."\n";
		$html .= '<p>An error has occurred on this website. Please ignore this message as our technical team has been made aware of this error.</p>'."\n";
		if(is_array($content)){ // more than one message in feedback so loop
			$html .= '<p><pre>'."\n";
			$html .= print_r($content)."\n";
			$html .= '</pre></p>'."\n";
		} 
		else{ // only one item so show it
			$html .= '<p>'.$content.'</p>'."\n";
		}
		$html .= '</div>'."\n";
		
		echo $html;
	}
	
	/*
		limitWords()
		truncates content to shwoa set number of words
	*/
	
	function limitWords($content,$cutoff){
		///strip tags...
		$content = strip_tags(nl2br(html_entity_decode($content))); //this prevents line breaks, images, etc from being counted...
		$wordcount = str_word_count($content);
		$wordindex = str_word_count($content, 1,'.,-\'"\\/?&!�$%^*()_-+=#~{[]}:;|1234567890');
		$wordlimit = ($wordcount<$cutoff) ? $wordcount : $cutoff-1;
		
		if($wordcount > $wordlimit){
			$wordindex = array_slice($wordindex,0,$wordlimit);
			$content = implode(' ',$wordindex).'...';
		}
		return $content;	
	}
	
	
	/*
		highlightSearchTerm()
		add tag+plus wrapped aroudn instance of search keyword e.g <em class="search">monkey juice</em>
	*/
	function _highlightSearchTerms($content, $searchTerm, $tag = 'span', $class = 'keywords'){
		return str_ireplace($searchTerm,'<'.$tag.' class="'.$class.'">'.$searchTerm.'</'.$tag.'>', $content);
	}


	function highlightSearchTerms($content, $searchTerm, $tag = 'span', $class = 'keywords'){
		//print "hST($content, $searchTerm, $tag, $class)<br>\n";
		if (!$searchTerm) return $content;
		$content=replace_selected_tags($content, "<li><a><h1><h2><h3><h4><p><td><strong>", $searchTerm, $tag, $class);
		return $content;
	}

	function replace_selected_tags($str, $tags="", $search, $replace_tag, $class) {

		$tagstack=array();
        preg_match_all("/<([^>]+)>/i",$tags,$allTags,PREG_PATTERN_ORDER);
		
		//foreach($allTags[1] as $tag) print "got tag($tag)<br>";		
		
		$slen=strlen($str);
//		if ($slen>1000) $slen=1000;
		for ($i=0;$i<$slen;$i++) {
			$c=$str[$i];
			if ($c=="<") { 
//				print "last word($word)<br>"; 
				$fIntag=true; 
				$tag=''; 
				$word='';
			}
			else if ($c==">") { 
				$fIntag=false; 
//				print "last tag($tag)<br>";

				// get the actual last tag
				if (substr($tag, -1)!="/") {	// Totally ignore tags ending in / rely on tinyMCE to get it right
					$lastTag=substr($tag, 0, strpos($tag, " ")?strpos($tag, " "):strlen($tag));
					if (substr($tag, 0, 1)=="/") {
						array_pop($tagstack);
						$currenttag=$tagstack[count($tagstack)-1];
						$fReplace=in_array($currenttag, $allTags[1]);
//						print "back to tag($currenttag) check if replacing($fReplace)<br>";
					}
					else {
						array_push($tagstack, $tag);
						$fReplace=in_array($lastTag, $allTags[1]);
//						print "actual last tag($lastTag) next stuff we are replacing($fReplace)<br>";				
					}
				}
				
			}
			else if ($fIntag) { $tag.=$c; }
			else if (!$fIntag) { $word.=$c; }
			// Add this digit...
			$r.=$c;
			// Next if we are in a word and replacing check if the last x digits are insensitively the same as our search string
			if (!$fIntag && $fReplace) {
				if (strtolower(substr($r, -(strlen($search)))) == strtolower($search)) {
					$r=substr($r, 0, strlen($r)-strlen($search)).'<'.$replace_tag.' class="'.$class.'">'.substr($r, -(strlen($search))).'</'.$replace_tag.'>';
				}
			}
		}
		return $r;
	}

	
	/*
				
	createCache()
					
	Use a chaced flat HTML snippet file instead of 
	running queries all day long.
	
	If the cache file doesn't exist,
	create it  then include it

	*/
	function createCache($filename, $contents, $inc=true){
		//print "cc($filename, ".strlen($contents).", $inc)<br>\n";
		$filePath = $_SERVER['DOCUMENT_ROOT'].'/cache/';
		$cacheFile = $filePath.$filename;
		$handle = @fopen($cacheFile, "w");
		if ($handle) {
			//print "write to ($cacheFile)<br>\n";
			fwrite($handle, $contents);
			fclose($handle);
		} 
		//else print "Failed to open file for writing<br>\n";
		
		// Do we need to show the file too?
		if ($inc) {
			// Inclde the new file
			if(file_exists($cacheFile)) include($cacheFile);
			// Or display contents
			else echo $contents;
		}	
	}
	/*
	function _createCache($filename, $contents){

			$filePath = $_SERVER['DOCUMENT_ROOT'].'/cache/';
			$handle = @fopen($filePath.$filename, "w");
			if ($handle) {
				fwrite($handle, $contents);
				fclose($handle);
			} 
			
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename)){
				// Now inclde the new file 
				include($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename);
			} 
			else{
				echo $contents;
			}
	}
	*/
		
	/*
	validCache
	checks if a cache file exists and is less then a set time old
	*/
	function validCache($filename, $age=86400) {
		//print "vC($filename, $age)<br>\n";
		if (file_exists($filename)) {
			if (!$age) return true;						// We don't care how old it is
			$timediff = time()-filemtime($filename);	// Use modified time as create process overwrites
			//print "File is $timediff seconds old<br>\n";
			if ($timediff < $age) return true;			// This file is still valid
		}
		// Cache is not there or no longer valid
		// assume it will be rebuilt by the calling process.
		return false;									
	}
	
	

	/*
	clearCache()
	remove listed files from cache directory
	*/
	function clearCache($filenames="all"){
		global $site;
		//print "cC(".(is_array($filenames)?"array":$filenames).")<br>\n";
		if ($filenames=="all") {
			$filenames=array(
				'footer'.($site->id>1?"_":"").$site->id.'.inc', 
				'menu'.($site->id>1?"_":"").$site->id.'.inc', 
				'sitemap'.($site->id>1?"_":"").$site->id.'.inc'
				);
		}
	
		if(is_array($filenames)){
			foreach($filenames as $filename){
				if(file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename)){
					@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename);
				}
			}
		}
		else if(file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filenames)){
			@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filenames);
		}
	}



// Added 4th Novemeber 2009 PMR
// Compare content to a list of bad words in the admin database.
function censor($string, $all=false) {
	//print "c($string, $all)<br>\n";
	global $db_admin, $site;
	
	$words = Array();
	$replace = Array();

	// Does this site want to use the filter?
	$level = ($site->getConfig('bad_word_filter_level')+0);	
	if (!$level) return $string;
	if ($level>5) $level = 5;
	
	// Get the standard bad word list from the admin database.
	$query = "SELECT word, `replace` FROM bad_words WHERE level >=".($level+0);
	if ($results = $db_admin->get_results($query)) {
		foreach ($results as $result) {
			$words[] = $result->word;
			$replace[] = $result->replace;
		}
	}
	
	// Join with the site specific badwords string.
	// Maybe code this in one day if we decide we ever actually need it.
	
	if (empty($words)) return $string;

	// This foreach loops through each censor word and checks it has a valid replacement
	$rex = count($words);
	for ($i=0; $i<$rex; $i++) {
		if (!$replace[$i]) {
			if (!$all) $replace[$i] = substr($words[$i],0,1).str_repeat('*', strlen($words[$i])-2).substr($words[$i],-1);
			else $replace[$i] = str_repeat('*', strlen($words[$i]));
		}
	}
	
	//print_r($words);
	//print_r($replace);

	// Str_replace replaces all the words with their censors
	return str_ireplace($words, $replace, $string);
}





	function formatFilesize( $filesize, $round=2 ){
		if($filesize){
	
			if( $filesize > 999999999 ){
				return round($filesize/100000000,$round).'GB';	
			}else if( $filesize > 999999 ){
				return round($filesize/1000000,$round).'MB';	
			}else if( $filesize > 999 ){
				return round($filesize/1000,$round).'KB';
			}else{
				return $filesize.'b';
			}
			
		}else{
			return false;
		}
	}
	

// Generates a "friendly" name from $title
// checking that there are no existing records with the same name and parent
function generateName($title, $table="", $parent='', $check_title=false, $field="guid") {
	global $db, $site;
	$msg="gN($title, $table) enc(".$site->properties['encoding'].") \n";
	$name = '';
	
	// Check we have inuf information to continue
	if (!$title || !$table) return '';

	// Naff but some tables still do not use the fieldname msv
	$site_field="msv";
	if ($table=="files") $site_field="site_id"; 
	
	// 1 - Check the title is not duplicated
	$title = $db->escape($title);
	if ($check_title) {
		$query="SELECT $field FROM $table WHERE $site_field=".$site->id." AND title = '$title' "; 
		if ($parent) $query.="AND parent = '$parent' ";
		$query.="LIMIT 1 ";
		$msg.="genName check title($query) \n";
		if ($db->get_var($query)) {
			$msg.="check title fail.. (".$db->num_rows.")\n";
			$title = ''; 
		}
	}
	
	// 2 - Title is OK, create new name and check ok/force ok
	if ($title) {

		// Strip everything but letters, numbers and spaces from the title
		$tmp = $title;
		if ($_SESSION['treeline_user_language']=="ar") $tmp = UTF_to_Unicode($tmp);
		else if ($_SESSION['treeline_user_language']=="ja") $tmp = UTF_to_Unicode($tmp);
		// else tmp = htmlentities($title,ENT_QUOTES,$site->properties['encoding']);
		
		$tmpname = _generateName($tmp);
		$msg.="created name(".$tmpname.") from title($tmp) \n";

		// Check pagename is ok.
		if ($tmpname && str_replace("-", "", $tmpname)!="") {	
			$loop=0;
			$tmpnamelen=strlen($tmpname);
			while (!$name && $loop<100) {
				$query = "SELECT $field FROM $table WHERE $site_field = ".$site->id." AND name='$tmpname' ";
				if ($parent) $query.="AND parent = '$parent' ";
				$query .= "LIMIT 1";
				$msg.="Name q($query)\n";
				if ($db->get_var($query)) $tmpname = substr($tmpname, 0, $tmpnamelen)."-".(++$loop);
				else $name = $tmpname;
			}
		}
		else $msg.="returning false zero length name generated \n";
	}
	unset($msg);
	if ($msg) mail("phil.redclift@ichameleon.com", $site->name." arabic generate name", $msg);
	// if ($msg) print nl2br($msg);
	return $name;
}

function _generateName($title) {
	$tmpname = strtr($title, "��", "ae");
	$tmpname = preg_replace("/[^A-Za-z0-9 ]/", "", $tmpname);
	$tmpname = str_replace(" ",'-',$tmpname);
	//print "_gN($title => $tmpname)<br>\n";
	return $tmpname;
}

function generatePDF($html, $filename='', $paper="letter", $orientation="portrait") {
	global $message, $site;
	//print "genPDF($html, $filename, $paper, $orientation)<br>\n";
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/dompdf-0.5.1/dompdf_config.inc.php");
	
	if ( get_magic_quotes_gpc() ) {
    	$html = stripslashes($html);
	}
	$old_limit = ini_set("memory_limit", "32M");
	
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->set_paper($paper, $orientation);
	$dompdf->render();

	// If we have a filename we need to create the file and return success/failure
	if ($filename) {
		$filepath=$_SERVER['DOCUMENT_ROOT']."/silo/pdf/".$filename;
		if (!file_exists($filepath)) {
			if ($fp=fopen($filepath, "wt")) {
				fputs($fp, $dompdf->output());
				fclose($fp);
				return true;
			}
			else $message[]="Failed to open output file($filepath)";
		}
		else $message[]="Output file already exists.";
	}
	// No filename passed so just stream the PDF to the browser.
	else {
		$dompdf->stream($site->name."-page.pdf");	
		return true;
	}
	return false;
}


	/*
		getBackgroundImage()
		take the string of an image, find it's src value (image source) and make it the background image of an item
		
	*/
	function getBackgroundImage($element,$bg_img){
		$img =  explode('src="',$bg_img);
		$new_img = explode('"',$img[1]);
		$bg_img = $new_img[0];
		$css = ($bg_img) ? $element."{\n\tbackground-image: url('$bg_img');\n}\n" : '';	
		return $css;	
	}
	
function santise_content($content, $encoding=''){
	if ($encoding) $content = htmlentities(strip_tags($content), ENT_QUOTES, $encoding);
	else $content = htmlentities(strip_tags($content));
	return jsspecialchars($content);
}
// Escapes strings to be included in javascript
function jsspecialchars($s) {
   return preg_replace('/([^ !#$%@()*+,-.\x30-\x5b\x5d-\x7e])/e',
       "'\\x'.(ord('\\1')<16? '0': '').dechex(ord('\\1'))",$s);
}


	// 1st Feb 2009 - Phil Redclift
	// Removes unwanted data from input. Taken from the intranet functions
	function cleanField($content,$level=1,$allowed_tags=''){
		if($content){
			switch($level){
				case 1:
					// Basic
					$tmp = strip_tags( trim($content), $allowed_tags );
					return $tmp;
					break;
				case 2:
					// Name formatting[include spaces, hyphens and apostrophes
					$tmp = strip_tags( trim($content) );
					$tmp = preg_replace('/^[a-z-\'\s]+$/i',$tmp);
					return $tmp;
					break;
			}
		}
	}


// check if an email address is valid.
// There are quite a few of these lurking around in Treeline
// from php.net
function is_email($email, $validate=true, $debug=false){
	if ($debug) print "<!-- is_email($email, $validate, $debug) -->\n";
	$x = '\d\w!\#\$%&\'*+\-/=?\^_`{|}~';    //just for clarity
	$oemail = $email = strtolower($email);	
	$formatok = count($email = explode('@', $email, 3)) == 2
       && strlen($email[0]) < 65
       && strlen($email[1]) < 256
       && preg_match("#^[$x]+(\.?([$x]+\.)*[$x]+)?$#", $email[0])
       && preg_match('#^(([a-z0-9]+-*)?[a-z0-9]+\.)+[a-z]{2,6}.?$#', $email[1]);
	if ($formatok) {
		if ($debug) print "<!-- email format is ok -->\n";
		if ($validate) return validateEmail($oemail, $debug);
		return true;
	}
	return false;
}

function validateEmail($email, $dbg=false) {	
	if (!$email) {
		if ($dbg) print "<!-- No email address passed -->\n";
		return false;
	}
	if ($dbg) print "<!-- validateEmail($email) -->\n";
	
	//print "Start validator<br>\n";
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/smtp_validateEmail.class.php");

	$sender = 'test@treelinesoftware.com';
	$SMTP_Validator = new SMTP_validateEmail();
	$SMTP_Validator->debug = $debug;
	$results = $SMTP_Validator->validate(array($email), $sender);
	if ($dbg) echo '<!-- '.$email.' is '.($results[$email] ? 'valid' : 'invalid')." -->\n";
	if (!$results[$email]) if ($dbg) print "<!-- email($email) is not valid -->\n";
	return ($results[$email]?true:false);
}
function validateEmails($emails, $debug=false) {	
	if (!count($emails) || !is_array($emails)) {
		if ($debug) print "No email array passed<br>\n";
		return false;
	}
	
	//print "Start validator<br>\n";
	include_once("./includes/smtp_validateEmail.class.php");
	$sender = 'test@treelinesoftware.com';
	$SMTP_Validator = new SMTP_validateEmail();
	$SMTP_Validator->debug = $debug;
	$results = $SMTP_Validator->validate($emails, $sender);
	if ($debug) {
		foreach($results as $email=>$result) {
			echo 'The email address '. $email.' is '.($result?'':'not ').'valid<br>'."\n";
		}
	}
	return $results;
}




function is_sql_date($date){
	if(preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}$/', $date)) return $date;
	if(preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $date)) return $date;
	if(preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $date)) return $date;
	return false;
}


	
if (!function_exists("lcfirst")) {
	//print "function does not exist<br>\n";
	function lcfirst($s) {
		$s{0} = strtolower($s{0});
		return $s;
	}
}	
function UTF_to_Unicode($input, $array=False) {

 $bit1  = pow(64, 0);
 $bit2  = pow(64, 1);
 $bit3  = pow(64, 2);
 $bit4  = pow(64, 3);
 $bit5  = pow(64, 4);
 $bit6  = pow(64, 5);
 
 $value = '';
 $val   = array();
 
 for($i=0; $i< strlen( $input ); $i++){
 
     $ints = ord ( $input[$i] );
    
     $z     = ord ( $input[$i] );
     $y     = ord ( $input[$i+1] ) - 128;
     $x     = ord ( $input[$i+2] ) - 128;
     $w     = ord ( $input[$i+3] ) - 128;
     $v     = ord ( $input[$i+4] ) - 128;
     $u     = ord ( $input[$i+5] ) - 128;

     if( $ints >= 0 && $ints <= 127 ){
        // 1 bit
        $value .= '&#'.($z * $bit1).';';
        $val[]  = $value;
     }
     if( $ints >= 192 && $ints <= 223 ){
        // 2 bit
        $value .= '&#'.(($z-192) * $bit2 + $y * $bit1).';';
        $val[]  = $value;
     }   
     if( $ints >= 224 && $ints <= 239 ){
        // 3 bit
        $value .= '&#'.(($z-224) * $bit3 + $y * $bit2 + $x * $bit1).';';
        $val[]  = $value;
     }    
     if( $ints >= 240 && $ints <= 247 ){
        // 4 bit
        $value .= '&#'.(($z-240) * $bit4 + $y * $bit3 +
$x * $bit2 + $w * $bit1).';';
        $val[]  = $value;       
     }    
     if( $ints >= 248 && $ints <= 251 ){
        // 5 bit
        $value .= '&#'.(($z-248) * $bit5 + $y * $bit4
+ $x * $bit3 + $w * $bit2 + $v * $bit1).';';
        $val[]  = $value;  
     }
     if( $ints == 252 && $ints == 253 ){
        // 6 bit
        $value .= '&#'.(($z-252) * $bit6 + $y * $bit5
+ $x * $bit4 + $w * $bit3 + $v * $bit2 + $u * $bit1).';';
        $val[]  = $value;
     }
     if( $ints == 254 || $ints == 255 ){
       return "";
     }
    
 }
 
 if( $array === False ){
    return $unicode = $value;
 }
 if($array === True ){
     $val     = str_replace('&#', '', $value);
     $val     = explode(';', $val);
     $len = count($val);
     unset($val[$len-1]);
    
     return $unicode = $val;
 }
 
}
	
	/*
		validateContent()
		clean up content: if no </p> is present: 
		we assume there's no HTML, add <p>'s and convert line breaks yto <br />s
	*/
	
	function validateContent($html){
		global $db, $page, $mode;
		//print "<!-- VC($html) -->\n";
		if ($mode!="edit") {
			$valid_html = (stripos($html, '</p>') === false) ? '<p>'.nl2br($html).'</p>' : $html;
		}
		else $valid_html = $html;
		$valid_html = str_replace('<p>&nbsp;</p>','',$valid_html);
		$valid_html = str_replace('&amp;amp;#','&#',$valid_html);

		$loop=0;

		if ($page->getMode()!="edit") {
			
			if (preg_match("/<p>@@(\S*)@@<\/p>/", $valid_html, $reg) ) 
				$valid_html = str_replace("<p>@@".$reg[1]."@@</p>", "@@".$reg[1]."@@", $valid_html);
			
			
			while (preg_match("/@@(\S*)@@/", $valid_html, $reg) && ++$loop<20) {
				$reppd = addCode($reg[1]);
				//print "<!-- Got code($reppd) -->\n";
				$valid_html = str_replace("@@".$reg[1]."@@", $reppd, $valid_html);
				
				// If its a gallery we have to replace a whole load more goo
				if (substr($reg[1], 0, 13)=="LINK-GALLERY-") {
					$gallery_id = substr($reg[1], 13);
					// Get this galleries style 
					$gallery_pattern = '<a href=\"(.*?)@@LINK-GALLERY-'.$gallery_id.'@@(.*?)\"(.*?)>(.*?)<\/a>';
					//print "check for pattern($gallery_pattern) site($siteID)<br>\n";
					if (preg_match('/(.*)'.$gallery_pattern.'(.*)/', $valid_html, $reg2)) {
						//print "matched(".print_r($reg2, true).")<br>\n";
						$replace = '';
						$linktext = $reg2[5];
						include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/embeddedgallery.inc.php");
						// Replace the entire image tag with the new gallery code
						$valid_html = str_replace('<a href="'.$reg2[2].'@@LINK-GALLERY-'.$gallery_id.'@@'.$reg2[3].'"'.$reg2[4].'>'.$reg2[5].'</a>', $replace, $valid_html);
					}
					// Collect slideshow html
				}
				// If its a gallery we have to replace a whole load more goo
				else if (substr($reg[1], 0, 15)=="MOBILE-GALLERY-") {
					$gallery_id = substr($reg[1], 15);
					// Get this galleries style 
					$gallery_pattern = '<a href=\"(.*?)@@MOBILE-GALLERY-'.$gallery_id.'@@(.*?)\"(.*?)>(.*?)<\/a>';
					//print "check for pattern($gallery_pattern) site($siteID)<br>\n";
					if (preg_match('/(.*)'.$gallery_pattern.'(.*)/', $valid_html, $reg2)) {
						//print "matched(".print_r($reg2, true).")<br>\n";
						$replace = '';
						$linktext = $reg2[5];
						include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/embeddedmobilegallery.inc.php");
						// Replace the entire image tag with the new gallery code
						$valid_html = str_replace('<a href="'.$reg2[2].'@@MOBILE-GALLERY-'.$gallery_id.'@@'.$reg2[3].'"'.$reg2[4].'>'.$reg2[5].'</a>', $replace, $valid_html);
					}
					// Collect slideshow html
				}
				
			}
			
			//print "ducked out after($loop) loops<br>\n";
			
			// 3 Replace any conertinas
			include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/concertina_replace.inc.php");
		}
		//print "<!-- DONE VC($valid_html) END VC-->\n";
		return $valid_html;
	}

	// Bit of a fix but only real way to avoid having to put 
	function addCode($code) {
		global $db, $page, $site;
		$replace='';

		//print "ac($code)<br>\n";
		//$replace="<p>replace($code)</p>";
		if (substr($code, 0, 7)=="GALLERY") return '';	// Blank out old image style gallery system

		//if (substr($code, 0, 8)=="GALLERY-") return $code;
		if (substr($code, 0, 12)=="LINK-GALLERY") {
			return "@@".$code."@@";
		}
		else if (substr($code, 0, 14)=="MOBILE-GALLERY") {
			return "@@".$code."@@";
		}
		// Are we trying to embed a form?
		else if (substr($code, 0, 5)=="FORM_") {
			$formname=substr($code, 5);
			$code="FORM-INCLUDE";
		}
		else if (substr($code, 0, 9)=="CHILDREN(") {
			$parentGUID = substr($code, 9, -1);
			$code = "PAGE_CHILD_LIST";
		}
		else if (substr($code, 0, 8)=="LISTING-") {
			$tag = substr($code, 8);
			$code = "TAG-LISTING";
		}
		else if (substr($code, 0, 10) == "PANEL-LIST") {
			$parentGUID = substr($code, 11);
			$code = "PANEL_CHILD_LIST";
		}
		else if (substr($code, 0, 12) == "MOSAIC-EMBED") {
			$mid = substr($code, 13);
			$code = "MOSAIC-EMBED";
		}
		else if (substr($code, 0, 4)=="RSS(") {
			$feed = substr($code, 4, -1);
			$code = "RSS-FEED-INJECT";
		}
		else if (substr($code, 0, 9)=="GOOGLEMAP") {
			$googleGUID = substr($code, 10);
			$code = "GOOGLEMAP";
		}
		else if (substr($code, 0, 13)=="FILE/download") {
			$fileGUID = substr($code, 14, -1);
			$code = "FILE-DOWNLOAD";
		}
		else if (substr($code, 0, 6)=="MEDIA-") {
			$media_guid = substr($code, 6);
			$code = "MEDIA-BLOCK";
		}
		else if (substr($code, 0, 6)=="FANCY-") {
			$fancy_guid = substr($code, 6);
			$code = "FANCY-BLOCK";
		}

		switch($code) {
			case "SEND2FRIEND" :
				//include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/worldmap.php");
				break;
			case "FORM-INCLUDE":
				$form = new Form();
				$form->loadByName($formname);
				include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/form_display.inc.php");
				break;
			case "RSS-FEED-INJECT":
				//print "get data from feed($feed)<br>\n";
				$feeddata = drawRSSFeed($feed, true, 100, true);
				$replace = $feeddata;
				break;
			case "AMMAP-MAP": 
				include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/ammap.inc.php");
				break;
			case "GOOGLEMAP": 
				if (!$mapcounter) $mapcounter = 1;
				//print "<!-- map counter($mapcounter) -->\n";
				include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/googlemap.inc.php");
				break;
			case "MOSAIC-EMBED":
                include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/mosaic-embed.php");
				break;				
			case "FILE-DOWNLOAD":
                include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/file-download.php");
				break;				
			case "MOSAIC-INCLUDE":
				include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/mosaic.php");
				break;
			case "FANCY-BLOCK":
				include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/fancy-block.inc.php");
				break;					
			case "MEDIA-BLOCK":
				//print "get data from feed($feed)<br>\n";
				$query = "SELECT code, responsive FROM media WHERE guid='$media_guid'";
				if ($row = $db->get_row($query)) {
					$replace = '';
					if ($row->responsive) $replace .= '<div class="iframe-rwd">';
					$replace .= html_entity_decode($row->code);
					if ($row->responsive) $replace .= '</div>';
				}
				break;
			case "TAG-LISTING": 
				break;				
			case "PAGE_CHILD_LIST":
				if ($results = $page->getChildrenByParent($parentGUID)) {
					foreach ($results as $result) {
						$replace.='<p><a href="'.$page->drawLinkByGUID($result->guid).'">'.$result->title.'</a></p>';
					}
				}
				break;
			case "PANEL_CHILD_LIST":
				//print "p->gCBP($parentGUID)<br>\n";
				if ($results = $page->getChildrenByParent($parentGUID)) {
					foreach ($results as $result) {
						$replace.='<p><a href="'.$page->drawLinkByGUID($result->guid).'">'.$result->title.'</a></p>';
					}
				}
				break;
			case "readmore": 
				$replace = '<p><a href="#read">Read more</a></p>';
				break;
			case "readless": 
				$replace = '<p><a href="#read">Read less</a></p>';
				break;
			case "calls-btn":
			if ($page->guid == '')
			{
				
			}
				$replace = '
				<div class="col-xs-12">
					<div class="col-xs-6">
						<div class="col-xs-12 btn btn-primary '.($page->guid =='57f25589c6b66'? 'active':'').'" onclick="window.location=\'http://aasciences.ac.ke/aesa/programmes/grand-challenges-africa/funding-opportunities/\';">
							Current calls
						</div>
					</div>
					<!--<div class="col-xs-4">
						<div class="col-xs-12 btn btn-primary '.($page->guid =='580a6a885391c'? 'active':'').'" onclick="window.location=\'http://aasciences.ac.ke/aesa/programmes/grand-challenges-africa/funding-opportunities/previous-calls/\';">
							Previous calls
						</div>
					</div>-->
					<div class="col-xs-6">
						<div class="col-xs-12 btn btn-primary '.($page->guid =='580bb6e65ec38'? 'active':'').'" onclick="window.location=\'http://aasciences.ac.ke/aesa/programmes/grand-challenges-africa/funding-opportunities/calls-from-partners/\';">
							Calls from our partners
						</div>
					</div>
				</div>
				';
				break;
		}
		//print "<!-- replace($code) rep($replace) END REP FUNC -->\n";
		return $replace;
	}
	
	function make_seed()
	{
	  list($usec, $sec) = explode(' ', microtime());
	  return (float) $sec + ((float) $usec * 100000);
	}
	
	
	// Another nice fix.
	// Take some object html find its width and height and change them :o)
	function setObjectHW($content, $w=0, $h=0) {
		//print "sOHW($content, $w, $h)<br>\n";
		if (($w+$h)==0) return $content;
		if (preg_match("/\swidth=\"?(\d*)/", $content, $rw)) {
			$width=$rw[1];
			if (preg_match("/\sheight=\"?(\d*)/", $content, $rh)) {
				$height=$rh[1];
			}
			if ($width>0 && $height>0) {
				if ($w>0 && $h>0) { $newwidth=$w; $newheight=$h; }
				else if ($w>0) { $newwidth=$w; $newheight=$height*($newwidth/$width); }
				else if ($h>0) { $newheight=$h; $newwidth=$width*($newheight/$height); }
			}
			//print "got w($width) new($newwidth) h($height) new($newheight)<br>\n";
			$content=preg_replace("/\swidth=\"?\d*\"?/", ' width="'.floor($newwidth).'" ', $content, 2);
			$content=preg_replace("/\sheight=\"?\d*\"?/", ' height="'.floor($newheight).'" ', $content, 2);
			
		}
		//print "set to($content)<br>\n";
		return $content;
	}

	//LOCAL TESTING FUNCTION
	function tp($s, $die=0, $file=false) {
		global $msg;
		$s=date("H:i:s", time())." - $s"."<br>\n";
		if ($file) {
			if ($fp=fopen($logfile, "at")) {
				$logfile=$_SERVER['DOCUMENT_ROOT']."/treeline/tp-".date("dmY", time())."-log.txt";
				fputs($fp, $s);
				fclose($fp);
			}
		}
		if (isset($msg)) $msg.=$s;		
		if ($die) exit();
	}
	
	function href_replace ($content) {
		global $site;	
		if (!$content) return;	
		
		$matches = array("/http:\/\/www\.its-services.org\.uk/", 
			);
		for ($i=0; $i<count($matches); $i++) $replacements[]="";		
			
		$new_content = preg_replace($matches, $replacements, $content);
		
		$msg = "HREF_REPLACE 

Send
-----
".$content."

Created
-------
".$new_content."

";
		//mail("phil.redclift@ichameleon.com", $site->title." href_replace", $msg);
		return $new_content;
	}
			
			
// Remove the first image from a content string
// assume image is formatted as
// <img src="...." />  - OR -
// &lt;img src=&quot...&quot /&gt;
function pullImage($content, $use_entities=true, $strip_site_name=true) {
	global $site;
	if ($use_entities) $content = html_entity_decode($content);
	//print "get img tag from ($content)<br>\n";
	if (preg_match('/<img(.*?)src="(.*?)"(.*)\/>/', $content, $reg)) {
		if (!$strip_site_name) return $reg[2];
		else {
			// Need to remove the site link if its there 
			$sl = $site->link;
			$img = $reg[2];
			//print "<!-- strip(".$sl.") from(".$img.") -->\n";
			if (substr($sl, 0, 7) == "http://") $sl = substr($sl, 7);
			if (substr($img, 0, 7) == "http://") $img = substr($img, 7);
			//print "<!-- strip(".$sl.") from(".$img.") -->\n";
			if (substr($sl, 0, 4) == "www.") $sl = substr($sl, 4);
			if (substr($img, 0, 4) == "www.") $img = substr($img, 4);
			//print "<!-- strip(".$sl.") from(".$img.") -->\n";
			return str_replace ($sl, "", $img);
		}
		//else return str_replace ($site->link, "", $reg[2]);
	}
	return false;
}
// ----------------------------------------
			
?>
