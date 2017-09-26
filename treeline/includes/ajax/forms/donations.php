<?php
// if edit mode
if($action == 'view'){
	// Edit mode should have a page's details so display that instead of the generic (so we can use just 1 form)

	print "generate csv summary<br>";
	
}

define (TODAY, time());
define (THIRTY_DAYS, TODAY - (60*60*24*30));

$start_day=read($_POST,'donate_start_day', date("d", THIRTY_DAYS));
$start_month=read($_POST,'donate_start_month', date("m", THIRTY_DAYS));
$start_year=read($_POST, 'donate_start_year', date("Y", THIRTY_DAYS));
$end_day=read($_POST, 'donate_end_day', date("d", TODAY));
$end_month=read($_POST, 'donate_end_month', date("m", TODAY));
$end_year=read($_POST, 'donate_end_year', date("Y", TODAY));


$donate_range=read($_POST, 'donate_range', 0);
$donate_source=$_POST['donate_source'];
$donate_type=$_POST['donate_type'];




function showDateSelect($name, $year_range, $selected) {
	$html='';	
	$html.=daySelect($name."_day", $selected[0]);
	$html.=monthSelect($name."_month", $selected[1]);
	$html.=yearSelect($name."_year", $selected[2], $year_range);
	return $html;
}
function daySelect($name, $curday) {
	for($i=1;$i<=31;$i++) {
		$html.='<option value="'.$i.'"'.(($i==$curday)?" selected":"").'>'.$i.'</option>';
	}
	return '<select name="'.$name.'" id="'.$name.'_id" style="width:50px;">'.$html.'</select>';
}
function monthSelect($name, $curmonth) {

	$months=array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$i=1;
	foreach ($months as $month) {
		$html.='<option value="'.$i.'"'.(($i==$curmonth)?" selected":"").'>'.$month.'</option>';
		$i++;
	}
	return '<select name="'.$name.'" id="'.$name.'_id" style="width:100px;">'.$html.'</select>';
}
function yearSelect($name, $curyear, $year_range=5) {
	$inc=1;
	$startyear=$year=date("Y", time()); 
	$endyear=$curyear+$year_range;
	//print "show years from $startyear to $endyear<br>";
	if ($year_range<0) $inc=-$inc;
	while ($year!=$startyear+$year_range) {
		$html.='<option value="'.$year.'"'.(($year==$curyear)?" selected":"").'>'.$year.'</option>';;
		$year+=$inc;	
	}
	return '<select name="'.$name.'" id="'.$name.'_id" style="width:100px;">'.$html.'</select>';
}



if ($_SERVER['REQUEST_METHOD']=="POST") {

	$file='';
	// Process CSV request
	
	// 1 - Generate the query
	if ($donate_range)  $where.="date_created > NOW() - INTERVAL $donate_range AND ";
	else $where.="date_created>'{$start_year}-{$start_month}-{$start_day} 00:00:00' AND date_created<'{$end_year}-{$end_month}-{$end_day} 23:59:59' AND ";
	if ($donate_source) { $where.="source='$donate_source' AND "; $file.=substr($donate_source, 0, 1); }
	else $file.="a";
	if ($donate_type) $where.="donation_type=$donate_type AND ";
	$file.=($donate_type+0);
	
	// 2 - Create CSV filename and open the file for writing.
	$file="/silo/reports/rpt-".$file."-".date("YmdHis", time()).".csv";
	$filepath = $_SERVER['DOCUMENT_ROOT'].$file;
	
	if (!($fp=fopen($filepath, "wt"))) {
		echo drawFeedback("error", "Failed to create file($file)");
	}
	else {
		$count=0; $total=0;
		//print "file($file)<br>";
		$select="date_format(dut.date_created, '%d %M %Y') as created, dut.order_id, dut.source, dut.donation_type, dut.amount,
			dt.title,
		 	concat(du.first_name,' ',du.initial,' ',du.last_name) as name, du.email";
		$query="select $select from donation_user_types dut 
			left join donation_types dt on dut.donation_type=dt.donation_id
			left join donation_users du on dut.donor_id=du.donor_id";
		if ($where) $query.=" WHERE ".substr($where, 0, -5). "
			and du.donor_id not in (0,1,2,3,246,247,248,249,250,251,356,357,414,776)
			order by dut.date_created";
		//print "$query<br>";
		
		// Doh whats next?
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$count++;
				$csv=$result->created.",".$result->name.",".$result->amount.",".$result->title.",".$result->source.",".$result->email."\n";
				//print "$csv<br>";
				$total+=$result->amount;
				fputs($fp, $csv);
			}
			if ($count>0) {
				echo drawFeedback("success", $count.' donation'.(($count>1)?"s":"").' total £'.number_format($total, 2).', <a href="'.$file.'" target="_blank">click here for CSV result file</a>');
				echo '<p>You can click the link above to open the spreadsheet using your browser or you can right click the link and select "save link/target as" to copy the file to you disk.</p>';
			}
		}
		fclose($fp);		
		
	}
}


?>

<form id="treeline" action="<?=$_SERVER['REQUEST_URI']?><? if ($DEBUG) echo '?debug'?>" method="post">
    <fieldset>
        <legend>Show donations</legend>
        <input type="hidden" name="action" value="<?=$action?>" />
        <p class="instructions">To view donations select from the filters below:</p>
        <fieldset>
            <legend>Donations</legend>
            <div>
                <label for="donate_source">Source :</label>
                <select name="donate_source" id="donate_source">
                  <option value="">All</option>
                  <option value="amref"<?=(($donate_source=='amref')?" selected":"")?>>Amref</option>
                  <option value="guardian"<?=(($donate_source=='guardian')?" selected":"")?>>Guardian</option>
                </select><br />
            </div>
            <div>
                <label for="donate_type">Type :</label>
                <select name="donate_type" id="donate_type">
                <option value="">All</option>
                <option value="2"<?=(($donate_type==2)?" selected":"")?>>Direct debit</option>
                <option value="1"<?=(($donate_type==1)?" selected":"")?>>One off</option>
                <option value="4"<?=(($donate_type==4)?" selected":"")?>>Christmas card(Amref only)</option>
                </select><br />
            </div>
        </fieldset>
        <fieldset>
	        <legend>Date range</legend>
        	<label for="donate_start_day_id">From date :</label>
            <?=showDateSelect("donate_start", -5, array($start_day, $start_month, $start_year))?>
            <br />
			<label for="donate_end_day_id" style="clear:both;">Upto date :</label>
            <?=showDateSelect("donate_end", -5, array($end_day, $end_month, $end_year))?>
            <br />
            <label for="donate_range">From the : </label>
            <select name="donate_range" id="donate_range" onchange="javascript:toggleRange();" >
            <option value=""<?=(($donate_range==0 || !$donate_range || $donate_range=="0")?" selected":"")?>>Set date range</option>
            <option value="1 MONTH"<?=(($donate_range=="1 MONTH")?" selected":"")?>>Past month</option>
            <option value="3 MONTH"<?=(($donate_range=="3 MONTH")?" selected":"")?>>Past 3 months</option>
            <option value="6 MONTH"<?=(($donate_range=="6 MONTH")?" selected":"")?>>Past 6 monhts</option>
            <option value="1 YEAR"<?=(($donate_range=="1 YEAR")?" selected":"")?>>Past year</option>
            <option value="1000 YEAR"<?=(($donate_range=="1000 YEAR")?" selected":"")?>>Show all</option>
            </select>
        </fieldset>
        <fieldset class="buttons">
        	<button type="submit" class="submit">Generate summary</button>
        </fieldset>
    </fieldset>
</form>

