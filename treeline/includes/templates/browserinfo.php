<?php

include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/browserdetect.php");

$browser['name']=browser_detection("browser");
$browser['number']=browser_detection("number");
$browser['os']=browser_detection("os");
$browser['os_version']=browser_detection("os_number");
$browser['supported']=true;
print "<!-- browser[] ".print_r($browser, 1)." -->\n";

if ($browser['os'] && $browser['os_version'] && $browser['name'] && $browser['number']) {
	$query = "SELECT o.id as os_id,
		ov.title as opsys,
		obv.title as browser,
		obv.number as browser_version
		FROM os o
		LEFT JOIN os_version ov ON o.id=ov.os_id
		LEFT JOIN os_browser ob ON o.id=ob.os_id
		LEFT JOIN os_browser_version obv on ob.id = obv.browser_id
		WHERE o.name='".$browser['os']."' 
		AND (ov.osv = '".$browser['os_version']."' OR ov.osv IS NULL) 
		AND ob.name='".$browser['name']."'
		AND (obv.number = '".$browser['number']."' OR obv.number IS NULL)
		ORDER BY ov.osv DESC, obv.number DESC
		LIMIT 1
		";
	if ($row = $db_admin->get_row($query)) {
	
		//print "got os[".$row->title."] id(".$row->id.")<br>\n";
	
		$browser_name = fixname($row->browser, $row->browser_number);
		$opsys_name = fixname($row->opsys, $row->os_id);
		
		$browserhtml='<table border="0" cellpadding="10" cellspacing="0" id="browserinfo">
	<tr>
		<td class="opsys top">'.$page->drawGeneric("browser", 1).'</td>
		<td class="top single">'.$page->drawLabel("tl_browse_using", "You are using").' <strong>'.$browser_name.'</strong></td>
	</tr><tr>
		<td class="opsys">'.$page->drawGeneric("computer", 1).'</td>
		<td class="single">'.$page->drawLabel("tl_browse_using", "You are using").' <strong>'.$opsys_name.'</strong></td>
	</tr>
	</table>
	';
	}
	else {
		$msg="Unknown OS configuration accessing Treeline \n";
		$msg.="$query \n";
	
		$browser['supported']=false;
	
		$broswerhtml='';
		//$browserhtml.= '<p>Your browser may not be supported</p>';
		$browserhtml.= '<table border="0" cellpadding="10" cellspacing="0" id="browserinfo">
	<tr>
		<td class="opsys top">'.$page->drawGeneric("windows", 1).'</td>
		<td class="top">Mozill Firefox 1.5 ('.$page->drawLabel("tl_browse_higher", "or higher").')<br />Microsoft Internet Explorer 6 ('.$page->drawLabel("tl_browse_higher", "or higher").')</td>
	</tr><tr>
		<td class="opsys">'.$page->drawLabel("tl_browse_apple", "Apple Mac").'</td>
		<td>Mozilla Firefox 1.5 ('.$page->drawLabel("tl_browse_higher", "or higher").')<br />Safari 2 ('.$page->drawLabel("tl_browse_higher", "or higher").')<br />Opera 8 (or higher)</td>
	</tr>
	</table>
	';
	}
	
	if ($msg) {
		$msg = getcwd()."\n\n".$msg;
		$msg.="\n\n---------------\n".print_r($browser, true)."\n\n---------------\n";
		foreach ($_SERVER as $k => $v) {
			$msg.=$k." => ".$v."\n";
		}
		mail("phil.redclift@ichameleon.com", $_SERVER['SERVER_NAME']." tl-login", $msg);
	}
}

function fixname($name, $number) {
	return str_replace("NNN", $number, $name);
}
print "<!-- browser ($browserhtml) -->\n";
?>