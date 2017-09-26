<?php

// Calendar class 
// Written by Phil Redclift 13th November


class Calendar {


	public $day, $month, $year;
	public $t_day, $t_month, $t_year;

	private $months = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	public function Calendar($day=-1, $month=-1, $year=-1) {
		print "C::($day, $month, $year)<br>\n";
		$time = time();
		$this->t_day=date("d", $time);
		$this->t_month=date("m", $time);
		$this->t_year=date("Y", $time);
		print "created calendar for ".$this->t_day."/".$this->t_month."/".$this->t_year.")<br>\n";

		$this->setDate($day, $month, $year);
		
		
		return;
	}
	
	// Set the internal date and ensure all parameters are valid.
	public function setDate($day=-1, $month=-1, $year=-1) {	
		//print "sD($day, $month, $year)<br>\n";
		if (strlen($year)<4 && $year>=0 && $year<=999) $year=substr(date("Y",time()), 0, (4-strlen($year))).($year?($year+0):'');

		if ($day == -1 || !$day) $this->day = $this->t_day;
		else $this->day = $day;
		if ($month == -1 || !$month) $this->month = $this->t_month;
		else $this->month = $month;
		if ($year==-1) $this->year = $this->t_year;
		else $this->year = $year;

		print "set Date ".$this->day."/".$this->month."/".$this->year.")<br>\n";
		return ;
	}

	public function drawMonth() {
		global $db, $site;
		$oneday = 60*60*24;
		
		$start_time = mktime(12,0,0,$this->month, 1, $this->year);	// Current time on the first of the month
		$last_day=date("d", ($start_time-$oneday));			// Date of the last day of last month
		$first_day=date("w", $start_time);					// Weekday to start on 
		$no_spare_days = !$first_day?6:($first_day-1);		// Number of weekdays till the first of this month
		//print "start on ".date("d/m/Y H:i", $start_time)."first day-$first_day ($no_spare_days spare) last day of last month was the $last_day<br>\n";
		
		for ($i=($last_day-$no_spare_days+1); $i<=$last_day; $i++) {
			$month_html.='<td class="inactive w'.($i-($last_day-$no_spare_days)).'"><p>'.$i.'</p></td>';
		}
		if ($month_html) $month_html='<tr>'.$month_html;
		
		// Grab a list of events happening this month....
		$events=array();
		$start_of_month = $this->year."-".(($this->month<10?"0":"").($this->month+0))."-01";
		$query="SELECT
			IF (date_format(e.start_date, '%m')=".$this->month.",date_format(e.start_date, '%d'),1) as start_day,
			IF (date_format(e.end_date, '%m')=".$this->month.",date_format(e.end_date, '%d'),31) as end_day,
			e.*
			FROM events e
			INNER JOIN pages p on e.guid=p.guid
			WHERE p.msv=".$site->id."
			AND p.date_published is not null 
			AND p.date_published <> '0000-00-00 00:00:00'
			AND e.end_date >= '$start_of_month'
			AND e.start_date <= '$start_of_month' + INTERVAL 1 MONTH ";
		//print "$query<br>\n";
		if ($results=$db->get_results($query)) {
			foreach ($results as $result) {
				for($i=$result->start_day; $i<=$result->end_day; $i++) {
					$events[($i+0)]=1;
				}				
			}
		}
		//print "events (".print_r($events, true).")<br>\n";
		
		// Find out what day the first of the month is and draw some blank boxes....
		$cur_time = $start_time;		
		do {
			//print "do day ".date("d/m/Y", $cur_time)." w(".date("w",$cur_time).")<br>\n";
			$cur_day = date("d", $cur_time);
			$cur_wday = date("w", $cur_time);
			$cur_month = date("m", $cur_time);
			
			// If we are still in this month then add a box for this day....
			if ($cur_month==$this->month) {
				$tmp = ($cur_day+0);
				// If something happens today just add a link....
				if ($events[$tmp]==1) $tmp='<a href="'.$site->link.'search/?ed='.$this->year.'-'.($this->month<10?"0":"").($this->month+0).'-'.($tmp<10?"0":"").($tmp+0).'">'.$tmp.'</a>';
				
				$class="";
				//print "c($cur_day) t(".$this->t_day.") m($cur_month==".$this->t_month.") y(".$this->year."==".$this->t_year.")<br>\n";
				if ($cur_day == $this->t_day && $cur_month == $this->t_month && $this->year == $this->t_year) $class="today";
				if ($cur_wday==1) $month_html.='<tr>';
				$month_html.='<td class="active w'.$cur_wday.'"><p class="'.$class.'">'.$tmp.'</p></td>';
				if ($cur_wday==0) $month_html.='</tr>';
			}
			
			$cur_time+=$oneday;
		} while ($cur_month == $this->month);
		
		// If the month does not end on a sunday then throw in a few blank days more.....
		//print "month finished on a $cur_wday<br>\n";
		if ($cur_wday!=1) {
			if ($cur_wday!=0) {
				for ($i=$cur_wday; $i<=6; $i++) {
					$month_html.='<td class="inactive w'.$i.'"><p>'.($cur_day+0).'</p></td>';
					$cur_day++;
				}
			}
			// Always add the last sunday....
			$month_html.='<td class="inactive w0"><p>'.($cur_day+0).'</p></td>';
			$month_html.='</tr>';
		}
			
		if ($month_html) {
			$month_html='<table id="calendar" border="0" cellpadding="0" cellspacing="0">'.$this->drawHeaderTable().$month_html.'</table>';		
		}
		
		return $month_html;
	}
	
	private function drawHeader() {
	
		$next_month = $this->month+1;
		$prev_month = $this->month-1;
		$next_year = $prev_year = $this->year;

		if ($next_month>12) {
			$next_month=1; 
			$next_year=$this->year+1;
		}
		if ($prev_month<1) {
			$prev_month=12;
			$prev_year=$this->year-1;
		}
		//print "draw header for month(".$this->month.") year(".$this->year.")<br>\n";			
		$html = '<ul id="calendar-header">
<li class="header-link-left"><a href="'.$PHP_SELF.'?c_month='.$prev_month.'&amp;c_year='.$prev_year.'">Prev</a></li>
<li class="header-month">'.$this->months[($this->month+0)]." ".$this->year.'</li>
<li class="header-link-right"><a href="'.$PHP_SELF.'?c_month='.$next_month.'&amp;c_year='.$next_year.'">Next</a></li>
</ul>
';
		return $html;
	}


	private function drawHeaderTable() {
	
		$next_month = $this->month+1;
		$prev_month = $this->month-1;
		$next_year = $prev_year = $this->year;

		if ($next_month>12) {
			$next_month=1; 
			$next_year=$this->year+1;
		}
		if ($prev_month<1) {
			$prev_month=12;
			$prev_year=$this->year-1;
		}
		//print "draw header for month(".$this->month.") year(".$this->year.")<br>\n";			
		$html = '<tr><td colspan="7" class="header-cell">
<table id="calendar-header-table" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="header-link-left"><a href="'.$PHP_SELF.'?c_month='.$prev_month.'&amp;c_year='.$prev_year.'">Prev</a></td>
<td class="header-month">'.$this->months[($this->month+0)]." ".$this->year.'</td>
<td class="header-link-right"><a href="'.$PHP_SELF.'?c_month='.$next_month.'&amp;c_year='.$next_year.'">Next</a></td>
</tr>
</table>
</td>
</tr>
';
		return $html;
	}
}


?>