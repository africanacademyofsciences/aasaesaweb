<?php


function pplog($s) {
	global $db, $testing, $usedemo, $member_id, $reference;
	$desc = $db->escape($s);
	if ($testing && $usedemo) $desc = "TEST: ".$desc;
	$query = "INSERT INTO pesapal_history 
		(member_id, reference, pid, added, description) 
		VALUES
		(".($member_id+0).", '$reference', '".getmypid()."', NOW(), '".$desc."')
		";
	//if ($testing) print "$query<br>\n";
	if ($db->query($query)) return $db->insert_id;
	return 0;
}


function pp_format_years($y) {
	return $y." years";
}


function formatrenewal($total, $current, $missed, $future) {
	global $site;
	print "<!-- fr($total, $current, $missed, $future) -->\n";

	$html = '';
	$cur = $site->getConfig("pesapal_currency");

	if (!$missed && !$future) return '<p>The membership renewal fee is '.($cur.number_format($amount, 2)).'</p>'."\n";
	
	if ($missed) $html .= '<tr><td>'.($missed/$current).' missed years</td><td class="right">'.($cur.number_format($missed, 2)).'</td></tr>'."\n";
	$html .= '<tr><td>Renewal</td><td class="right">'.($cur.number_format($current, 2)).'</td></tr>'."\n";
	if ($future) $html .= '<tr><td>'.($future/$current).' future years</td><td class="right">'.($cur.number_format($future, 2)).'</td></tr>'."\n";
	$html .= '<tr class="bold"><td>Total</td><td class="right">'.($cur.number_format($total, 2)).'</td></tr>'."\n";
	
	return '
		<table class="pesapal-due">
			'.$html.'
		</table>
		';
}

?>